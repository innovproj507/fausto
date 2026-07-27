<?php 
$title = 'Dashboard';
ob_start();
?>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Total Products -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 font-semibold">Total Productos</p>
                <p class="text-3xl font-bold text-primary mt-2"><?= $stats['total_products'] ?? 0 ?></p>
            </div>
            <div class="bg-red-100 p-4 rounded-full">
                <i class="fas fa-box text-3xl text-primary"></i>
            </div>
        </div>
    </div>

    <!-- Total Users -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 font-semibold">Total Usuarios</p>
                <p class="text-3xl font-bold text-primary mt-2"><?= $stats['total_users'] ?? 0 ?></p>
            </div>
            <div class="bg-blue-100 p-4 rounded-full">
                <i class="fas fa-users text-3xl text-blue-600"></i>
            </div>
        </div>
    </div>

    <!-- Total Orders -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 font-semibold">Total Pedidos</p>
                <p class="text-3xl font-bold text-primary mt-2"><?= $stats['total_orders'] ?? 0 ?></p>
            </div>
            <div class="bg-green-100 p-4 rounded-full">
                <i class="fas fa-shopping-cart text-3xl text-green-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Recent Products -->
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-bold text-secondary">Productos Recientes</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Producto</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Categoría</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Precio</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (!empty($recent_products)): ?>
                    <?php foreach ($recent_products as $product): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <?php if ($product['featured_image']): ?>
                                        <img src="<?= url('uploads/products/' . $product['featured_image']) ?>" 
                                             alt="<?= sanitize($product['name']) ?>"
                                             class="w-10 h-10 rounded object-cover mr-3">
                                    <?php else: ?>
                                        <div class="w-10 h-10 bg-gray-200 rounded mr-3 flex items-center justify-center">
                                            <i class="fas fa-image text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                    <span class="font-semibold text-gray-900"><?= sanitize($product['name']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600"><?= sanitize($product['category_name'] ?? 'Sin categoría') ?></td>
                            <td class="px-6 py-4 font-semibold text-primary">$<?= number_format($product['price'], 2) ?></td>
                            <td class="px-6 py-4">
                                <?php if ($product['status'] == 'active'): ?>
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Activo</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">Inactivo</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2 block"></i>
                            No hay productos recientes
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="px-6 py-4 bg-gray-50 border-t">
        <a href="<?= url('/manager/products') ?>" class="text-primary hover:text-red-700 font-semibold">
            Ver todos los productos →
        </a>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/main.php';
?>
