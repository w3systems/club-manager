<?php
// app/views/member/payment_methods.php
 require_once VIEW_PATH . '/member/layouts/member.php';  use App\Helpers\functions as Helpers; ?>

<?php Helpers\start_section('content'); ?>

<h1 class="text-2xl font-semibold text-gray-900 mb-6">Your Payment Methods</h1>

<?php Helpers\displayFlashMessages();  Helpers\displayErrors(); ?>

<div class="mb-8">
    <h2 class="text-xl font-medium text-gray-900 mb-4">Saved Payment Methods</h2>
    <?php if (!empty($paymentMethods)): ?>
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <ul role="list" class="divide-y divide-gray-200">
                <?php foreach ($paymentMethods as $pm): ?>
                    <li class="px-4 py-5 sm:px-6">
                        <div class="flex items-center justify-between">
                            <p class="text-lg font-medium text-gray-900">
                                <span class="uppercase"><?= Helpers\esc($pm['card_brand']) ?></span> ending in **** **** **** <?= Helpers\esc($pm['last_four']) ?>
                            </p>
                            <?php if ($pm['is_default']): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Default
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2 text-sm text-gray-500">
                            Expires: <?= Helpers\esc(str_pad($pm['exp_month'], 2, '0', STR_PAD_LEFT)) ?>/<?= Helpers\esc($pm['exp_year']) ?>
                        </div>
                        <div class="mt-4 flex space-x-3">
                            <?php if (!$pm['is_default']): ?>
                                <form action="/payment-methods/set-default" method="POST">
                                    <input type="hidden" name="payment_method_id" value="<?= Helpers\esc($pm['id']) ?>">
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Set as Default
                                    </button>
                                </form>
                            <?php endif; ?>
                            <form action="/payment-methods/delete" method="POST" onsubmit="return confirm('Are you sure you want to delete this payment method? This cannot be undone.');">
                                <input type="hidden" name="payment_method_id" value="<?= Helpers\esc($pm['id']) ?>">
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>
        <p class="text-gray-500">You do not have any saved payment methods.</p>
    <?php endif; ?>
</div>

<div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg p-6">
    <h2 class="text-xl font-medium text-gray-900 mb-4">Add New Payment Method</h2>

    <form id="add-payment-method-form" action="/payment-methods/add" method="POST" class="space-y-6">
        <div id="card-element" class="border border-gray-300 rounded-md p-3">
            </div>
        <div id="card-errors" role="alert" class="text-red-500 text-sm mt-2"></div>

        <input type="hidden" name="stripeToken" id="stripeToken">
        <input type="hidden" name="last_four" id="last_four">
        <input type="hidden" name="card_brand" id="card_brand">

        <button type="submit" id="add-card-button"
                class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Add Card
        </button>
    </form>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripe = Stripe('<?= Helpers\esc($stripePublishableKey) ?>');
    const elements = stripe.elements();
    const cardElement = elements.create('card');

    cardElement.mount('#card-element');

    const form = document.getElementById('add-payment-method-form');
    const addButton = document.getElementById('add-card-button');
    const cardErrors = document.getElementById('card-errors');
    const stripeTokenInput = document.getElementById('stripeToken');
    const lastFourInput = document.getElementById('last_four');
    const cardBrandInput = document.getElementById('card_brand');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        addButton.disabled = true;

        const { token, error } = await stripe.createToken(cardElement);

        if (error) {
            cardErrors.textContent = error.message;
            addButton.disabled = false;
        } else {
            stripeTokenInput.value = token.id;
            lastFourInput.value = token.card.last4;
            cardBrandInput.value = token.card.brand;
            form.submit();
        }
    });
</script>

<?php Helpers\end_section(); ?>
