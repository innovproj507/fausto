<?php 
$title = 'Pedidos';
ob_start();
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-secondary">Gestión de Pedidos</h2>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form method="GET" class="flex gap-4">
        <select name="status" class="px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
            <option value="">Todos los estados</option>
            <option value="pending" <?= ($status ?? '') == 'pending' ? 'selected' : '' ?>>Pendiente</option>
            <option value="processing" <?= ($status ?? '') == 'processing' ? 'selected' : '' ?>>Procesando</option>
            <option value="shipped" <?= ($status ?? '') == 'shipped' ? 'selected' : '' ?>>Enviado</option>
            <option value="delivered" <?= ($status ?? '') == 'delivered' ? 'selected' : '' ?>>Entregado</option>
            <option value="cancelled" <?= ($status ?? '') == 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
        </select>
        <button type="submit" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-red-700 transition font-semibold">
            <i class="fas fa-filter mr-2"></i>Filtrar
        </button>
    </form>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b-2 border-gray-200">
            <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">#Pedido</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Cliente</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Total</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Estado</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Fecha</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-mono font-semibold">#<?= $order['id'] ?></td>
                        <td class="px-6 py-4"><?= sanitize($order['first_name'] . ' ' . $order['last_name']) ?></td>
                        <td class="px-6 py-4 font-bold text-primary">$<?= number_format($order['total_amount'], 2) ?></td>
                        <td class="px-6 py-4">
                            <?php
                            $badges = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'processing' => 'bg-blue-100 text-blue-800',
                                'shipped' => 'bg-purple-100 text-purple-800',
                                'delivered' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800'
                            ];
                            $badgeClass = $badges[$order['status']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $badgeClass ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-600"><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                        <td class="px-6 py-4">
                            <a href="<?= url('/manager/orders/' . $order['id']) ?>" 
                               class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-eye text-lg"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <i class="fas fa-shopping-cart text-6xl text-gray-300 mb-4 block"></i>
                        <p class="text-gray-500 text-lg">No hay pedidos</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
