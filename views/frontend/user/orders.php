<?php
ob_start();
$title = 'Mis Pedidos - Fausto Salazar, S.A.';

$badges = [
    'pending' => 'bg-yellow-100 text-yellow-800',
    'confirmed' => 'bg-blue-100 text-blue-800',
    'processing' => 'bg-blue-100 text-blue-800',
    'shipped' => 'bg-purple-100 text-purple-800',
    'delivered' => 'bg-green-100 text-green-800',
    'cancelled' => 'bg-red-100 text-red-800',
    'refunded' => 'bg-gray-100 text-gray-800',
];
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
            <i class="fas fa-box text-primary mr-3"></i>Mis Pedidos
        </h1>
    </div>
</div>

<div class="container mx-auto px-4 py-10">
    <?php if (!empty($orders)): ?>
        <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b-2 border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">#Pedido</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Total</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Estado</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Fecha</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($orders as $order): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-mono text-sm"><?= sanitize($order['order_number']) ?></td>
                            <td class="px-6 py-4 font-bold text-primary">$<?= number_format($order['total_amount'], 2) ?></td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $badges[$order['status']] ?? 'bg-gray-100 text-gray-800' ?>">
                                    <?= $labels[$order['status']] ?? ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600"><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                            <td class="px-6 py-4">
                                <a href="<?= url('/account/orders/' . $order['id']) ?>" class="text-primary hover:underline font-semibold">
                                    Ver detalle
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-20">
            <i class="fas fa-box-open text-7xl text-gray-200 mb-6"></i>
            <p class="text-xl text-gray-600 mb-6">Aún no tienes pedidos</p>
            <a href="<?= url('/products') ?>"
               class="inline-flex items-center gap-2 bg-primary text-white px-8 py-3 rounded-lg hover:bg-primary-dark transition font-semibold">
                <i class="fas fa-th-large"></i>Ver Productos
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main_tailwind.php';
?>
