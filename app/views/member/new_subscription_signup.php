<?php
// app/views/member/new_subscription_signup.php
 require_once VIEW_PATH . '/member/layouts/member.php';  use App\Helpers\functions as Helpers; ?>

<?php Helpers\start_section('content'); ?>

<h1 class="text-2xl font-semibold text-gray-900 mb-6">Sign Up for <?= Helpers\esc($subscription['name']) ?></h1>

<?php Helpers\displayFlashMessages();  Helpers\displayErrors(); ?>

<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Subscription Details</h3>
        <p class="mt-1 text-sm text-gray-500">Review the details before proceeding to payment.</p>
        <div class="mt-4 border-t border-gray-200 pt-4">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?= Helpers\esc($subscription['name']) ?></dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?= Helpers\esc($subscription['description']) ?></dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Price</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        &pound;<?= Helpers\esc(number_format($subscription['price'], 2)) ?>
                        <?php if ($subscription['type'] !== 'session_based'): ?>
                            / <?= Helpers\esc($subscription['term_unit']) ?>
                        <?php endif; ?>
                    </dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Type</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?= Helpers\esc(ucfirst(str_replace('_', ' ', $subscription['type']))) ?></dd>
                </div>
                <?php if ($subscription['prorata_enabled']): ?>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Pro-rata First Month</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            &pound;<?= Helpers\esc(number_format($subscription['prorata_price'] + $subscription['admin_fee'], 2)) ?> (includes &pound;<?= Helpers\esc(number_format($subscription['admin_fee'], 2)) ?> admin fee)
                        </dd>
                    </div>
                <?php endif; ?>
                <?php if ($subscription['free_trial_enabled']): ?>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Free Trial</dt>
                        <dd class="mt-1 text-sm text-gray-900">Yes</dd>
                    </div>
                <?php endif; ?>
                <?php if ($subscription['min_age'] || $subscription['max_age']): ?>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Age Range</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <?php if ($subscription['min_age'] && $subscription['max_age']): ?>
                                <?= Helpers\esc($subscription['min_age']) ?> - <?= Helpers\esc($subscription['max_age']) ?> years
                            <?php elseif ($subscription['min_age']): ?>
                                <?= Helpers\esc($subscription['min_age']) ?>+ years
                            <?php else: ?>
                                Up to <?= Helpers\esc($subscription['max_age']) ?> years
                            <?php endif; ?>
                        </dd>
                    </div>
                <?php endif; ?>
            </dl>
        </div>
    </div>
</div>

<div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg p-6">
    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Payment Information</h3>

    <form id="payment-form" action="/subscriptions/signup" method="POST" class="space-y-6">
        <input type="hidden" name="subscription_id" value="<?= Helpers\esc($subscription['id']) ?>">

        <?php
        // Fetch existing payment methods for the current member
        $paymentMethods = \App\Models\PaymentMethod::getMemberPaymentMethods(\App\Core\Auth::member()['id']);
        $stripePublishableKey = STRIPE_PUBLISHABLE_KEY;
        ?>

        <?php if (!empty($paymentMethods)): ?>
            <div class="space-y-4">
                <h4 class="text-md font-medium text-gray-700">Use Existing Payment Method:</h4>
                <?php foreach ($paymentMethods as $pm): ?>
                    <div class="flex items-center">
                        <input id="payment_method_<?= Helpers\esc($pm['id']) ?>" name="payment_method_id" type="radio" value="<?= Helpers\esc($pm['id']) ?>"
                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                               <?= $pm['is_default'] ? 'checked' : '' ?>>
                        <label for="payment_method_<?= Helpers\esc($pm['id']) ?>" class="ml-3 block text-sm text-gray-700">
                            **** **** **** <?= Helpers\esc($pm['last_four']) ?> (<?= Helpers\esc(ucfirst($pm['card_brand'])) ?>)
                            <?= $pm['is_default'] ? '<span class="text-xs text-gray-500">(Default)</span>' : '' ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="relative flex py-5 items-center">
                <div class="flex-grow border-t border-gray-300"></div>
                <span class="flex-shrink mx-4 text-gray-500 text-sm">OR</span>
                <div class="flex-grow border-t border-gray-300"></div>
            </div>
        <?php endif; ?>

        <div class="space-y-4">
            <h4 class="text-md font-medium text-gray-700">Add New Card:</h4>
            <div id="card-element" class="border border-gray-300 rounded-md p-3">
                </div>
            <div id="card-errors" role="alert" class="text-red-500 text-sm mt-2"></div>
        </div>

        <input type="hidden" name="stripeToken" id="stripeToken">
        <input type="hidden" name="last_four" id="last_four">
        <input type="hidden" name="card_brand" id="card_brand">

        <button type="submit" id="submit-button"
                class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Confirm and Pay
        </button>
    </form>
</div>

<script src="[https://js.stripe.com/v3/](https://js.stripe.com/v3/)"></script>
<script>
    const stripe = Stripe('<?= Helpers\esc($stripePublishableKey) ?>');
    const elements = stripe.elements();
    const cardElement = elements.create('card');

    cardElement.mount('#card-element');

    const form = document.getElementById('payment-form');
    const submitButton = document.getElementById('submit-button');
    const cardErrors = document.getElementById('card-errors');
    const stripeTokenInput = document.getElementById('stripeToken');
    const lastFourInput = document.getElementById('last_four');
    const cardBrandInput = document.getElementById('card_brand');

    form.addEventListener('submit', async (event) => {
        // Prevent default form submission
        event.preventDefault();

        // Check if an existing payment method is selected
        const existingMethodSelected = form.querySelector('input[name="payment_method_id"]:checked');
        if (existingMethodSelected) {
            // If an existing method is selected, allow form to submit normally
            // No Stripe token needed, server will use payment_method_id
            form.submit();
            return;
        }

        // Otherwise, process new card with Stripe
        submitButton.disabled = true;

        const { token, error } = await stripe.createToken(cardElement);

        if (error) {
            cardErrors.textContent = error.message;
            submitButton.disabled = false;
        } else {
            // Insert the token ID into the form so it gets submitted to the server
            stripeTokenInput.value = token.id;
            lastFourInput.value = token.card.last4;
            cardBrandInput.value = token.card.brand;
            form.submit();
        }
    });
</script>

<?php Helpers\end_section(); ?>

