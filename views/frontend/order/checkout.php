<?php
ob_start();
$title = 'Checkout - Fausto Salazar, S.A.';
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);
?>

<div class="bg-secondary py-10">
    <div class="container mx-auto px-4">
        <h1 class="font-display text-4xl font-bold text-white uppercase tracking-wide">
            <i class="fas fa-lock text-primary mr-3"></i>Checkout
        </h1>
    </div>
</div>

<div class="container mx-auto px-4 py-10">
    <?php if (isset($_SESSION['errors'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 max-w-4xl">
            <ul class="list-disc list-inside">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><?= sanitize($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <form action="<?= url('/checkout/process') ?>" method="POST" id="checkout-form">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <!-- Billing -->
                <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
                    <h3 class="font-display text-xl font-bold text-secondary uppercase tracking-wide mb-4">
                        Dirección de Facturación
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre *</label>
                            <input type="text" name="billing_first_name" required value="<?= sanitize($old['billing_first_name'] ?? '') ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Apellido *</label>
                            <input type="text" name="billing_last_name" required value="<?= sanitize($old['billing_last_name'] ?? '') ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Dirección *</label>
                            <input type="text" name="billing_address_line1" required value="<?= sanitize($old['billing_address_line1'] ?? '') ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Dirección 2 (opcional)</label>
                            <input type="text" name="billing_address_line2" value="<?= sanitize($old['billing_address_line2'] ?? '') ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Ciudad *</label>
                            <input type="text" name="billing_city" required value="<?= sanitize($old['billing_city'] ?? '') ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Provincia/Estado</label>
                            <input type="text" name="billing_state" value="<?= sanitize($old['billing_state'] ?? '') ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Código Postal *</label>
                            <input type="text" name="billing_postal_code" required value="<?= sanitize($old['billing_postal_code'] ?? '') ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">País * (código de 2 letras)</label>
                            <input type="text" name="billing_country" required maxlength="2" placeholder="PA"
                                   value="<?= sanitize($old['billing_country'] ?? 'PA') ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary uppercase">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Teléfono de contacto *</label>
                            <input type="text" name="customer_phone" required value="<?= sanitize($old['customer_phone'] ?? '') ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        </div>
                    </div>
                </div>

                <!-- Shipping -->
                <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-display text-xl font-bold text-secondary uppercase tracking-wide">
                            Dirección de Envío
                        </h3>
                        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                            <input type="checkbox" id="ship_same_as_billing" name="ship_same_as_billing" value="1" checked
                                   class="w-4 h-4 accent-primary">
                            Igual a facturación
                        </label>
                    </div>
                    <div id="shipping-fields" class="grid grid-cols-1 sm:grid-cols-2 gap-4 hidden">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre *</label>
                            <input type="text" name="shipping_first_name" value="<?= sanitize($old['shipping_first_name'] ?? '') ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Apellido *</label>
                            <input type="text" name="shipping_last_name" value="<?= sanitize($old['shipping_last_name'] ?? '') ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Dirección *</label>
                            <input type="text" name="shipping_address_line1" value="<?= sanitize($old['shipping_address_line1'] ?? '') ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Dirección 2 (opcional)</label>
                            <input type="text" name="shipping_address_line2" value="<?= sanitize($old['shipping_address_line2'] ?? '') ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Ciudad *</label>
                            <input type="text" name="shipping_city" value="<?= sanitize($old['shipping_city'] ?? '') ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Provincia/Estado</label>
                            <input type="text" name="shipping_state" value="<?= sanitize($old['shipping_state'] ?? '') ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Código Postal *</label>
                            <input type="text" name="shipping_postal_code" value="<?= sanitize($old['shipping_postal_code'] ?? '') ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">País * (código de 2 letras)</label>
                            <input type="text" name="shipping_country" maxlength="2" placeholder="PA"
                                   value="<?= sanitize($old['shipping_country'] ?? 'PA') ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary uppercase">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Teléfono</label>
                            <input type="text" name="shipping_phone" value="<?= sanitize($old['shipping_phone'] ?? '') ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        </div>
                    </div>
                </div>

                <!-- Payment + notes -->
                <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
                    <h3 class="font-display text-xl font-bold text-secondary uppercase tracking-wide mb-4">
                        Pago y Notas
                    </h3>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Método de pago</label>
                        <select name="payment_method" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            <option value="cash_on_delivery">Efectivo contra entrega</option>
                            <option value="bank_transfer">Transferencia bancaria</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Notas (opcional)</label>
                        <textarea name="customer_notes" rows="3"
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary"><?= sanitize($old['customer_notes'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div>
                <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6 sticky top-24">
                    <h3 class="font-display text-xl font-bold text-secondary uppercase tracking-wide mb-4">Resumen del Pedido</h3>

                    <div class="max-h-64 overflow-y-auto divide-y divide-gray-100 mb-4">
                        <?php foreach ($items as $item): ?>
                            <div class="flex justify-between py-2 text-sm">
                                <span class="text-gray-600"><?= sanitize($item['name']) ?> × <?= (int) $item['quantity'] ?></span>
                                <span class="font-semibold text-secondary">$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="flex justify-between py-2 border-t border-gray-100 text-gray-600">
                        <span>Subtotal</span>
                        <span class="font-semibold text-secondary">$<?= number_format($subtotal, 2) ?></span>
                    </div>
                    <div class="flex justify-between py-2 text-gray-600">
                        <span>Envío</span>
                        <span class="font-semibold text-secondary"><?= $shipping > 0 ? '$' . number_format($shipping, 2) : 'Gratis' ?></span>
                    </div>
                    <div class="flex justify-between py-4 border-t border-gray-100 text-lg font-bold">
                        <span class="text-secondary">Total</span>
                        <span class="text-primary">$<?= number_format($subtotal + $shipping, 2) ?></span>
                    </div>

                    <button type="submit"
                            class="block w-full text-center bg-primary text-white py-3 rounded-lg hover:bg-primary-dark transition font-semibold shadow-md hover:shadow-lg">
                        <i class="fas fa-check-circle mr-2"></i>Confirmar Pedido
                    </button>
                    <a href="<?= url('/cart') ?>"
                       class="block w-full text-center bg-gray-100 text-secondary py-3 rounded-lg hover:bg-gray-200 transition font-medium mt-3">
                        Volver al Carrito
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // Hide the shipping address fields while "same as billing" is checked,
    // and drop their `required` attributes so the browser doesn't block submit.
    (function () {
        var checkbox = document.getElementById('ship_same_as_billing');
        var fields = document.getElementById('shipping-fields');
        var inputs = fields.querySelectorAll('input[name="shipping_first_name"], input[name="shipping_last_name"], input[name="shipping_address_line1"], input[name="shipping_city"], input[name="shipping_postal_code"], input[name="shipping_country"]');

        function sync() {
            var same = checkbox.checked;
            fields.classList.toggle('hidden', same);
            inputs.forEach(function (input) { input.required = !same; });
        }

        checkbox.addEventListener('change', sync);
        sync();
    })();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main_tailwind.php';
?>
