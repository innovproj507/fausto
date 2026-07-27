<?php 
$title = 'Inventario';
ob_start();
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-secondary">Control de Inventario</h2>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b-2 border-gray-200">
            <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Producto</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">SKU</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Categoría</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Stock</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Estado</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <?php 
                    $stock = $product['stock'] ?? 0;
                    $lowStock = $stock < 10;
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-semibold"><?= sanitize($product['name']) ?></td>
                        <td class="px-6 py-4 font-mono text-sm text-gray-600"><?= sanitize($product['sku']) ?></td>
                        <td class="px-6 py-4 text-gray-600"><?= sanitize($product['category_name'] ?? 'N/A') ?></td>
                        <td class="px-6 py-4">
                            <span class="text-2xl font-bold <?= $lowStock ? 'text-red-600' : 'text-green-600' ?>">
                                <?= $stock ?>
                            </span>
                            <?php if ($lowStock): ?>
                                <span class="ml-2 px-2 py-1 bg-red-100 text-red-800 rounded text-xs font-semibold">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>Bajo
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <?= $stock > 0 ? 
                                '<span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Disponible</span>' :
                                '<span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">Agotado</span>'
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <i class="fas fa-warehouse text-6xl text-gray-300 mb-4 block"></i>
                        <p class="text-gray-500 text-lg">No hay productos en inventario</p>
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
