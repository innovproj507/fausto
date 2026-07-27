<?php 
$title = 'Perfil de Usuario';
ob_start();
?>

<div class="mb-6">
    <a href="<?= url('/manager/users') ?>" class="text-primary hover:text-red-700 mb-2 inline-block">
        <i class="fas fa-arrow-left mr-2"></i>Volver a Usuarios
    </a>
    <h2 class="text-2xl font-bold text-secondary">Perfil de Usuario</h2>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- User Info -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-center mb-6">
            <div class="w-24 h-24 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user text-4xl text-white"></i>
            </div>
            <h3 class="text-xl font-bold"><?= sanitize($user['first_name'] . ' ' . $user['last_name']) ?></h3>
            <p class="text-gray-600"><?= sanitize($user['email']) ?></p>
        </div>
        
        <div class="space-y-3 pt-6 border-t">
            <div class="flex justify-between">
                <span class="text-gray-600">Rol:</span>
                <strong><?= $user['role_id'] == 1 ? 'Admin' : 'Cliente' ?></strong>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Estado:</span>
                <strong><?= ucfirst($user['status']) ?></strong>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Registro:</span>
                <strong><?= date('d/m/Y', strtotime($user['created_at'])) ?></strong>
            </div>
        </div>
    </div>

    <!-- Orders History -->
    <div class="lg:col-span-2 bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-bold">Pedidos Recientes</h3>
        </div>
        <div class="overflow-x-auto">
            <?php if (!empty($orders)): ?>
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">#Pedido</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="px-6 py-4 font-mono">#<?= $order['id'] ?></td>
                                <td class="px-6 py-4 font-bold text-primary">$<?= number_format($order['total_amount'], 2) ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600"><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="p-12 text-center text-gray-500">
                    <i class="fas fa-shopping-bag text-4xl mb-2 block"></i>
                    <p>Sin pedidos</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
