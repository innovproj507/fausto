<?php 
$title = 'Detalle del Pedido #' . $order['id'];
ob_start();
?>

<div class="mb-6">
    <a href="<?= url('/manager/orders') ?>" class="text-primary hover:text-red-700 mb-2 inline-block">
        <i class="fas fa-arrow-left mr-2"></i>Volver a Pedidos
    </a>
    <h2 class="text-2xl font-bold text-secondary">Pedido #<?= $order['id'] ?></h2>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Order Details -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Items -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-bold">Productos</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Producto</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Cantidad</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Precio</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="px-6 py-4"><?= sanitize($item['product_name']) ?></td>
                                <td class="px-6 py-4"><?= $item['quantity'] ?></td>
                                <td class="px-6 py-4">$<?= number_format($item['price'], 2) ?></td>
                                <td class="px-6 py-4 font-semibold">$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right font-bold">Total:</td>
                            <td class="px-6 py-4 font-bold text-primary text-xl">$<?= number_format($order['total'], 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Customer Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold mb-4">Cliente</h3>
            <div class="space-y-2">
                <p><strong>Nombre:</strong> <?= sanitize($order['first_name'] . ' ' . $order['last_name']) ?></p>
                <p><strong>Email:</strong> <?= sanitize($order['email']) ?></p>
                <?php if ($order['phone']): ?>
                    <p><strong>Teléfono:</strong> <?= sanitize($order['phone']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Status Update -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold mb-4">Estado del Pedido</h3>
            <form action="<?= url('/manager/orders/' . $order['id'] . '/status') ?>" method="POST">
                <?= csrf_field() ?>
                <select name="status" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary mb-4">
                    <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Procesando</option>
                    <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>Enviado</option>
                    <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Entregado</option>
                    <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
                </select>
                <button type="submit" class="w-full px-4 py-3 bg-primary text-white rounded-lg hover:bg-red-700 transition font-semibold">
                    Actualizar Estado
                </button>
            </form>
        </div>

        <!-- Order Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold mb-4">Información</h3>
            <div class="space-y-2 text-sm">
                <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                <p><strong>Método de Pago:</strong> <?= sanitize($order['payment_method'] ?? 'N/A') ?></p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
