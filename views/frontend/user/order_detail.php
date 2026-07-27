<?php
ob_start();
$title = 'Pedido ' . $order['order_number'] . ' - Fausto Salazar, S.A.';

$labels = [
    'pending' => 'Pendiente',
    'confirmed' => 'Confirmado',
    'processing' => 'Procesando',
    'shipped' => 'Enviado',
    'delivered' => 'Entregado',
    'cancelled' => 'Cancelado',
    'refunded' => 'Reembolsado',
];
?>

<div class="bg-secondary py-10">
    <div class="container mx-auto px-4">
        <h1 class="font-display text-4xl font-bold text-white uppercase tracking-wide">
            <i class="fas fa-receipt text-primary mr-3"></i>Pedido <?= sanitize($order['order_number']) ?>
        </h1>
    </div>
</div>

<div class="container mx-auto px-4 py-10">
    <a href="<?= url('/account/orders') ?>" class="text-primary hover:underline font-semibold mb-6 inline-block">
        <i class="fas fa-arrow-left mr-2"></i>Volver a Mis Pedidos
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
            <?php foreach ($items as $item): ?>
                <div class="flex justify-between items-center p-6 border-b border-gray-100 last:border-b-0">
                    <div>
                        <h3 class="font-semibold text-secondary"><?= sanitize($item['product_name']) ?></h3>
                        <p class="text-sm text-gray-500">SKU: <?= sanitize($item['sku']) ?> · Cantidad: <?= (int) $item['quantity'] ?></p>
                    </div>
                    <div class="font-bold text-secondary">$<?= number_format($item['total'], 2) ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
                <h3 class="font-display text-lg font-bold text-secondary uppercase tracking-wide mb-4">Resumen</h3>
                <div class="flex justify-between py-2 text-gray-600">
                    <span>Estado</span>
                    <span class="font-semibold text-secondary"><?= $labels[$order['status']] ?? ucfirst($order['status']) ?></span>
                </div>
                <div class="flex justify-between py-2 text-gray-600">
                    <span>Subtotal</span>
                    <span class="font-semibold text-secondary">$<?= number_format($order['subtotal'], 2) ?></span>
                </div>
                <div class="flex justify-between py-2 text-gray-600">
                    <span>Envío</span>
                    <span class="font-semibold text-secondary">$<?= number_format($order['shipping_amount'], 2) ?></span>
                </div>
                <div class="flex justify-between py-3 border-t border-gray-100 text-lg font-bold">
                    <span class="text-secondary">Total</span>
                    <span class="text-primary">$<?= number_format($order['total_amount'], 2) ?></span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
                <h3 class="font-display text-lg font-bold text-secondary uppercase tracking-wide mb-4">Dirección de Envío</h3>
                <p class="text-gray-600 leading-relaxed">
                    <?= sanitize($order['shipping_first_name'] . ' ' . $order['shipping_last_name']) ?><br>
                    <?= sanitize($order['shipping_address_line1']) ?><br>
                    <?php if ($order['shipping_address_line2']): ?><?= sanitize($order['shipping_address_line2']) ?><br><?php endif; ?>
                    <?= sanitize($order['shipping_city']) ?><?= $order['shipping_state'] ? ', ' . sanitize($order['shipping_state']) : '' ?><br>
                    <?= sanitize($order['shipping_postal_code']) ?>, <?= sanitize($order['shipping_country']) ?>
                </p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main_tailwind.php';
?>
