<?php 
$title = 'Gestión de Productos';
ob_start();
?>

<div class="mb-6 flex justify-between items-center">
    <h2 class="text-2xl font-bold text-secondary">Productos</h2>
    <a href="<?= url('/manager/products/create') ?>" 
       class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-red-700 transition font-semibold">
        <i class="fas fa-plus mr-2"></i>Crear Producto
    </a>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <input type="text" name="search" placeholder="Buscar por nombre o SKU..." 
                   value="<?= sanitize($search ?? '') ?>"
                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
        </div>
        
        <div>
            <select name="category" 
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                <option value="">Todas las categorías</option>
                <?php foreach ($categories ?? [] as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($category ?? '') == $cat['id'] ? 'selected' : '' ?>>
                        <?= sanitize($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <select name="status" 
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                <option value="">Todos los estados</option>
                <option value="active" <?= ($status ?? '') == 'active' ? 'selected' : '' ?>>Activo</option>
                <option value="inactive" <?= ($status ?? '') == 'inactive' ? 'selected' : '' ?>>Inactivo</option>
            </select>
        </div>
        
        <div>
            <button type="submit" 
                    class="w-full px-6 py-3 bg-primary text-white rounded-lg hover:bg-red-700 transition font-semibold">
                <i class="fas fa-search mr-2"></i>Filtrar
            </button>
        </div>
    </form>
</div>

<!-- Products Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b-2 border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Imagen</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Nombre</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">SKU</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Categoría</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Precio</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Stock</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Estado</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Destacado</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <?php if ($product['featured_image']): ?>
                                    <img src="<?= url('uploads/products/' . $product['featured_image']) ?>" 
                                         alt="<?= sanitize($product['name']) ?>"
                                         class="w-16 h-16 object-cover rounded-lg">
                                <?php else: ?>
                                    <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-image text-2xl text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-gray-900"><?= sanitize($product['name']) ?></span>
                            </td>
                            <td class="px-6 py-4 text-gray-600 font-mono text-sm"><?= sanitize($product['sku']) ?></td>
                            <td class="px-6 py-4 text-gray-600"><?= sanitize($product['category_name'] ?? 'Sin categoría') ?></td>
                            <td class="px-6 py-4">
                            <span class="font-bold text-primary">$<?= number_format($product['price'], 2) ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <?php 
                            $stock = $product['stock'] ?? 0;
                            $stockClass = $stock == 0 ? 'text-red-600' : ($stock < 10 ? 'text-yellow-600' : 'text-green-600');
                            ?>
                            <span class="font-bold text-lg <?= $stockClass ?>">
                                <?= $stock ?>
                            </span>
                            <?php if ($stock < 10 && $stock > 0): ?>
                                <span class="ml-2 px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-semibold">Bajo</span>
                            <?php elseif ($stock == 0): ?>
                                <span class="ml-2 px-2 py-1 bg-red-100 text-red-800 rounded text-xs font-semibold">Agotado</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                                <?php if ($product['status'] == 'active'): ?>
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                        <i class="fas fa-check-circle mr-1"></i>Activo
                                    </span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">
                                        <i class="fas fa-pause-circle mr-1"></i>Inactivo
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($product['is_featured']): ?>
                                    <i class="fas fa-star text-yellow-500 text-xl"></i>
                                <?php else: ?>
                                    <i class="far fa-star text-gray-300 text-xl"></i>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <a href="<?= url('/manager/products/' . $product['id'] . '/edit') ?>" 
                                       class="text-blue-600 hover:text-blue-800 transition" 
                                       title="Editar">
                                        <i class="fas fa-edit text-lg"></i>
                                    </a>
                                    <form action="<?= url('/manager/products/' . $product['id'] . '/delete') ?>" 
                                          method="POST" class="inline"
                                          onsubmit="return confirm('¿Estás seguro de eliminar este producto?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-800 transition" 
                                                title="Eliminar">
                                            <i class="fas fa-trash text-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <i class="fas fa-box-open text-6xl text-gray-300 mb-4 block"></i>
                            <p class="text-gray-500 text-lg">No hay productos</p>
                            <a href="<?= url('/manager/products/create') ?>" 
                               class="inline-block mt-4 text-primary hover:text-red-700 font-semibold">
                                <i class="fas fa-plus mr-2"></i>Crear el primer producto
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if (isset($pagination) && $pagination['last_page'] > 1): ?>
    <?php
        $currentPage = $pagination['page'];
        $lastPage = $pagination['last_page'];

        $pageUrl = function (int $i) use ($search, $category, $status) {
            $params = http_build_query(array_filter([
                'page' => $i,
                'search' => $search ?? '',
                'category' => $category ?? '',
                'status' => $status ?? ''
            ]));
            return url('/manager/products?' . $params);
        };

        // Build a windowed list of page numbers: first, last, and a few around the current page
        $window = 2;
        $pages = array_unique(array_filter(array_merge(
            [1, $lastPage],
            range(max(1, $currentPage - $window), min($lastPage, $currentPage + $window))
        ), fn($p) => $p >= 1 && $p <= $lastPage));
        sort($pages);
    ?>
    <div class="flex justify-center items-center gap-2 mt-6 flex-wrap">
        <?php if ($currentPage > 1): ?>
            <a href="<?= $pageUrl($currentPage - 1) ?>"
               class="px-4 py-2 bg-white border-2 border-gray-300 rounded-lg hover:border-primary hover:text-primary transition font-semibold">
                <i class="fas fa-chevron-left"></i>
            </a>
        <?php endif; ?>

        <?php $previous = null; ?>
        <?php foreach ($pages as $i): ?>
            <?php if ($previous !== null && $i - $previous > 1): ?>
                <span class="px-2 text-gray-400">&hellip;</span>
            <?php endif; ?>

            <?php if ($i == $currentPage): ?>
                <span class="px-4 py-2 bg-primary text-white rounded-lg font-semibold"><?= $i ?></span>
            <?php else: ?>
                <a href="<?= $pageUrl($i) ?>"
                   class="px-4 py-2 bg-white border-2 border-gray-300 rounded-lg hover:border-primary hover:text-primary transition font-semibold">
                    <?= $i ?>
                </a>
            <?php endif; ?>
            <?php $previous = $i; ?>
        <?php endforeach; ?>

        <?php if ($currentPage < $lastPage): ?>
            <a href="<?= $pageUrl($currentPage + 1) ?>"
               class="px-4 py-2 bg-white border-2 border-gray-300 rounded-lg hover:border-primary hover:text-primary transition font-semibold">
                <i class="fas fa-chevron-right"></i>
            </a>
        <?php endif; ?>
    </div>
    <p class="text-center text-sm text-gray-500 mt-3">
        Página <?= $currentPage ?> de <?= $lastPage ?> (<?= number_format($pagination['total']) ?> productos)
    </p>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
