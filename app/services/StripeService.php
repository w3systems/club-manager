<?php
// app/services/StripeService.php
namespace App\Services;

use Stripe\StripeClient;
use Stripe\Customer;
use Stripe\PaymentMethod;
use Stripe\Subscription;
use Stripe\Charge;
use Exception;

class StripeService
{
    private $stripe;

    public function __construct()
    {
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
        $this->stripe = new StripeClient(STRIPE_SECRET_KEY);
    }

    public function createCustomer($memberId, $email, $name = null)
    {
        try {
            $customer = $this->stripe->customers->create([
                'email' => $email,
                'name' => $name,
                'metadata' => ['member_id' => $memberId],
            ]);
            return $customer->id;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new Exception("Stripe Customer Creation Error: " . $e->getMessage());
        }
    }

    public function createPaymentMethodFromToken($token)
    {
        try {
            // A token represents a card or other payment source collected by Stripe.js
            // You create a PaymentMethod directly from the token.
            $paymentMethod = $this->stripe->paymentMethods->create([
                'type' => 'card',
                'card' => ['token' => $token],
            ]);
            return $paymentMethod->id;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new Exception("Stripe Payment Method Creation Error: " . $e->getMessage());
        }
    }

    public function attachPaymentMethodToCustomer($paymentMethodId, $customerId)
    {
        try {
            $this->stripe->paymentMethods->attach(
                $paymentMethodId,
                ['customer' => $customerId]
            );
            return true;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new Exception("Stripe Payment Method Attachment Error: " . $e->getMessage());
        }
    }

    public function updateCustomerDefaultPaymentMethod($customerId, $paymentMethodId)
    {
        try {
            $this->stripe->customers->update(
                $customerId,
                ['invoice_settings' => ['default_payment_method' => $paymentMethodId]]
            );
            return true;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new Exception("Stripe Default Payment Method Update Error: " . $e->getMessage());
        }
    }

    public function createStripeSubscription($customerId, $priceId, $paymentMethodId = null, $trialEnd = null)
    {
        try {
            $params = [
                'customer' => $customerId,
                'items' => [
                    ['price' => $priceId],
                ],
                'expand' => ['latest_invoice.payment_intent'],
            ];

            if ($paymentMethodId) {
                $params['default_payment_method'] = $paymentMethodId;
            }

            if ($trialEnd) {
                $params['trial_end'] = $trialEnd; // Unix timestamp for trial end
                $params['proration_behavior'] = 'none'; // No proration during trial
            }

            // You might add collection_method, billing_cycle_anchor, proration_behavior for complex scenarios
            // Based on your spec "recurring subscriptions will can be set to either run from a date in a month with a term length or from the date of joining."
            // This suggests billing_cycle_anchor might be needed. For now, using default Stripe behavior.

            $subscription = $this->stripe->subscriptions->create($params);
            return $subscription;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new Exception("Stripe Subscription Creation Error: " . $e->getMessage());
        }
    }

    public function cancelStripeSubscription($stripeSubscriptionId)
    {
        try {
            // Cancel at period end is generally safer for user experience
            $this->stripe->subscriptions->cancel($stripeSubscriptionId, ['prorate' => true]);
            // Or immediately: $this->stripe->subscriptions->cancel($stripeSubscriptionId);
            return true;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new Exception("Stripe Subscription Cancellation Error: " . $e->getMessage());
        }
    }

    public function resumeStripeSubscription($stripeSubscriptionId)
    {
        try {
            // Resumes a paused/canceled subscription. Stripe typically tries to collect any past due amount.
            // If the subscription was canceled, you might need to create a new one, depending on the status.
            // For simplicity, assuming it's still in a state that allows resumption (e.g., paused by Stripe due to failed payment).
            $this->stripe->subscriptions->update($stripeSubscriptionId, ['cancel_at_period_end' => false]);
            return true;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new Exception("Stripe Subscription Resume Error: " . $e->getMessage());
        }
    }

    public function createOneTimeCharge($customerId, $paymentMethodId, $amount, $description)
    {
        try {
            $charge = $this->stripe->charges->create([
                'amount' => $amount * 100, // Amount in cents
                'currency' => 'gbp', // Or retrieve from settings
                'customer' => $customerId,
                'payment_method' => $paymentMethodId,
                'off_session' => true, // Charge without requiring customer to be online
                'confirm' => true,
                'description' => $description,
            ]);
            return $charge;
        } catch (\Stripe\Exception\CardException $e) {
            // Card declined
            throw new Exception("Card was declined: " . $e->getMessage() . " Code: " . $e->getDeclineCode());
        } catch (\Stripe\Exception\RateLimitException $e) {
            // Too many requests made to the API too quickly
            throw new Exception("Stripe Rate Limit Error: " . $e->getMessage());
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Invalid parameters were supplied to Stripe's API
            throw new Exception("Stripe Invalid Request Error: " . $e->getMessage());
        } catch (\Stripe\Exception\AuthenticationException $e) {
            // Authentication with Stripe's API failed (maybe you changed API keys)
            throw new Exception("Stripe Authentication Error: " . $e->getMessage());
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            // Network communication with Stripe failed
            throw new Exception("Stripe API Connection Error: " . $e->getMessage());
        } catch (\Stripe\Exception\Exception $e) {
            // Display a very generic error to the user, and send yourself an email
            throw new Exception("Stripe Generic Error: " . $e->getMessage());
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            throw new Exception("Application Error: " . $e->getMessage());
        }
    }

    public function detachPaymentMethod($paymentMethodId)
    {
        try {
            $this->stripe->paymentMethods->detach($paymentMethodId);
            return true;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new Exception("Stripe Payment Method Detach Error: " . $e->getMessage());
        }
    }
}
