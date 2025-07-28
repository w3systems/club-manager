<?php
// app/controllers/StripeController.php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\StripeService;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Member; // Using Member model now for finding by Stripe customer ID
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeController extends Controller
{
    public function webhook()
    {
        // Webhooks don't require authentication; Stripe sends them
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = Webhook::constructEvent(
                $payload, $sig_header, STRIPE_WEBHOOK_SECRET
            );
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            http_response_code(400);
            exit();
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                // For one-time payments or initial subscription setup where checkout is used
                $session = $event->data->object;
                error_log('Checkout session completed: ' . $session->id);
                // In your signup flow, you might pass member_id in metadata
                // $memberId = $session->metadata->member_id;
                // Payment::recordPayment for one-time payment.
                break;
            case 'invoice.payment_succeeded':
                $invoice = $event->data->object;
                $stripeSubscriptionId = $invoice->subscription;
                $customerId = $invoice->customer;
                $amountPaid = $invoice->amount_paid; // In cents
                $currency = $invoice->currency;
                $chargeId = $invoice->charge; // The ID of the charge created for this invoice

                error_log("Invoice payment succeeded for customer {$customerId}, subscription {$stripeSubscriptionId}");

                // Update payment status in your database
                // Find the member by Stripe customer ID
                $member = Member::findByStripeCustomerId($customerId);
                if ($member) {
                    $memberSubscription = Subscription::getMemberSubscriptionByStripeSubscriptionId($stripeSubscriptionId);
                    Payment::recordPayment([
                        'member_id' => $member['id'],
                        'member_subscription_id' => $memberSubscription['id'] ?? null,
                        'amount' => $amountPaid / 100, // Convert cents to dollars
                        'currency' => strtoupper($currency),
                        'payment_date' => date('Y-m-d H:i:s', $invoice->status_transitions->paid_at),
                        'status' => 'succeeded',
                        'payment_gateway' => 'stripe',
                        'transaction_id' => $chargeId,
                        'invoice_id' => $invoice->id,
                        'description' => 'Subscription renewal'
                    ]);
                    // Auto-book classes for the new period if recurring subscription
                    if ($memberSubscription) {
                        Subscription::autoBookClassesForRecurring($member['id'], $memberSubscription['subscription_id']);
                    }
                    // Send notification
                    \App\Services\NotificationService::sendPaymentReceivedNotification($member['id'], $amountPaid / 100, 'Subscription renewal');
                }
                break;
            case 'invoice.payment_failed':
                $invoice = $event->data->object;
                $customerId = $invoice->customer;
                $stripeSubscriptionId = $invoice->subscription; // Get subscription ID
                error_log("Invoice payment failed for customer {$customerId}");

                $member = Member::findByStripeCustomerId($customerId);
                if ($member) {
                    $memberSubscription = Subscription::getMemberSubscriptionByStripeSubscriptionId($stripeSubscriptionId);
                    Payment::recordPayment([
                        'member_id' => $member['id'],
                        'member_subscription_id' => $memberSubscription['id'] ?? null,
                        'amount' => $invoice->amount_due / 100,
                        'currency' => strtoupper($invoice->currency),
                        'payment_date' => date('Y-m-d H:i:s'), // Or date of failure
                        'status' => 'failed',
                        'payment_gateway' => 'stripe',
                        'transaction_id' => $invoice->charge ?? null, // May not have a charge if failed before
                        'invoice_id' => $invoice->id,
                        'description' => 'Subscription payment failed'
                    ]);
                    // Notify admin and member about failed payment
                    \App\Services\NotificationService::sendPaymentFailedNotification($member['id'], $invoice->amount_due / 100);
                }
                break;
            case 'customer.subscription.updated':
                $stripeSubscription = $event->data->object;
                $memberSubscription = Subscription::getMemberSubscriptionByStripeSubscriptionId($stripeSubscription->id);

                if ($memberSubscription) {
                    $newStatus = $stripeSubscription->status;
                    // Map Stripe status to your internal status if needed
                    $internalStatus = 'active'; // Default
                    if (in_array($newStatus, ['canceled', 'unpaid', 'incomplete', 'past_due'])) {
                        $internalStatus = 'cancelled'; // Or 'suspended', depends on your desired mapping
                    } elseif ($newStatus === 'active') {
                        $internalStatus = 'active';
                    } elseif ($newStatus === 'trialing') {
                        $internalStatus = 'trial';
                    }

                    Subscription::updateMemberSubscriptionStatus($memberSubscription['id'], $internalStatus);
                    error_log("Customer subscription updated: {$stripeSubscription->id} Status: {$newStatus} (Internal: {$internalStatus})");
                }
                break;
            case 'customer.subscription.deleted':
                $stripeSubscription = $event->data->object;
                // Handle subscription deletion (e.g., member cancelled or admin cancelled via Stripe dashboard)
                error_log("Customer subscription deleted: " . $stripeSubscription->id);
                $memberSubscription = Subscription::getMemberSubscriptionByStripeSubscriptionId($stripeSubscription->id);
                if ($memberSubscription) {
                    // Mark as cancelled or ended in your DB
                    Subscription::updateMemberSubscriptionStatus($memberSubscription['id'], 'ended'); // Use 'ended' or 'cancelled'
                }
                break;
            case 'payment_method.card_automatically_updated':
                $paymentMethod = $event->data->object;
                // Handle card updates (e.g., expired card automatically updated by network)
                error_log("Payment method automatically updated: " . $paymentMethod->id);
                // Update your local payment method details (last4, expiry, brand)
                \App\Models\PaymentMethod::updateCardDetails($paymentMethod->id, $paymentMethod->card->last4, $paymentMethod->card->brand, $paymentMethod->card->exp_month, $paymentMethod->card->exp_year);
                break;
            case 'payment_method.detached':
                $paymentMethod = $event->data->object;
                // Handle when a payment method is detached (e.g., deleted by customer or admin)
                error_log("Payment method detached: " . $paymentMethod->id);
                \App\Models\PaymentMethod::markAsInactive($paymentMethod->id);
                break;
            // ... handle other event types
            default:
                error_log('Received unknown event type ' . $event->type);
        }

        http_response_code(200);
    }
}
