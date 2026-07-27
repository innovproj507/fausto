<?php 
$title = 'Editar Producto';
$content = function() use ($product, $categories) {
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-secondary">Editar Producto</h2>
</div>

<?php if (isset($_SESSION['errors'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
        <ul class="list-disc list-inside">
            <?php foreach ($_SESSION['errors'] as $error): ?>
                <li><?= sanitize($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php unset($_SESSION['errors']); ?>
<?php endif; ?>

<div class="bg-white rounded-lg shadow p-6">
    <form action="<?= url('/manager/products/' . $product['id'] . '/update') ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                        Nombre del Producto *
                    </label>
                    <input type="text" id="name" name="name" required 
                           value="<?= sanitize(old('name', $product['name'])) ?>"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                </div>
                
                <div>
                    <label for="sku" class="block text-sm font-semibold text-gray-700 mb-2">
                        SKU *
                    </label>
                    <input type="text" id="sku" name="sku" required 
                           value="<?= sanitize(old('sku', $product['sku'])) ?>"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                </div>
                
                <div>
                    <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-2">
                        Categoría *
                    </label>
                    <select id="category_id" name="category_id" required
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        <option value="">Selecciona una categoría</option>
                        <?php foreach ($categories ?? [] as $cat): ?>
                            <option value="<?= $cat['id'] ?>" 
                                    <?= old('category_id', $product['category_id']) == $cat['id'] ? 'selected' : '' ?>>
                                <?= sanitize($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="price" class="block text-sm font-semibold text-gray-700 mb-2">
                            Precio *
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-gray-600">$</span>
                            <input type="number" id="price" name="price" required step="0.01" min="0"
                                   value="<?= old('price', $product['price']) ?>"
                                   class="w-full pl-8 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        </div>
                    </div>
                    
                    <div>
                        <label for="compare_price" class="block text-sm font-semibold text-gray-700 mb-2">
                            Precio Comparación
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-gray-600">$</span>
                            <input type="number" id="compare_price" name="compare_price" step="0.01" min="0"
                                   value="<?= old('compare_price', $product['compare_price']) ?>"
                                   class="w-full pl-8 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        </div>
                    </div>
                </div>
                
                <div>
                    <label for="stock" class="block text-sm font-semibold text-gray-700 mb-2">
                        Stock Actual
                    </label>
                    <input type="number" id="stock" name="stock" min="0" step="1"
                           value="<?= old('stock', $product['stock'] ?? 0) ?>"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                    <p class="text-sm text-gray-500 mt-1">Cantidad disponible en inventario</p>
                </div>
                
                <div>
                    <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">
                        Estado
                    </label>
                    <select id="status" name="status"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        <option value="active" <?= old('status', $product['status']) == 'active' ? 'selected' : '' ?>>Activo</option>
                        <option value="inactive" <?= old('status', $product['status']) == 'inactive' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" id="is_featured" name="is_featured" value="1"
                           <?= old('is_featured', $product['is_featured']) ? 'checked' : '' ?>
                           class="w-5 h-5 text-primary border-gray-300 rounded focus:ring-primary">
                    <label for="is_featured" class="ml-2 text-sm font-semibold text-gray-700">
                        Producto Destacado
                    </label>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="space-y-4">
                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                        Descripción
                    </label>
                    <textarea id="description" name="description" rows="6"
                              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary"><?= sanitize(old('description', $product['description'] ?? '')) ?></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Imagen Actual
                    </label>
                    <?php if ($product['featured_image']): ?>
                        <div class="mb-3">
                            <img src="<?= url('uploads/products/' . $product['featured_image']) ?>" 
                                 alt="<?= sanitize($product['name']) ?>"
                                 class="w-32 h-32 object-cover rounded-lg border-2 border-gray-300">
                        </div>
                    <?php else: ?>
                        <div class="w-32 h-32 bg-gray-100 rounded-lg border-2 border-gray-300 flex items-center justify-center mb-3">
                            <i class="fas fa-image text-4xl text-gray-400"></i>
                        </div>
                    <?php endif; ?>
                    
                    <label for="image" class="block text-sm font-semibold text-gray-700 mb-2">
                        Cambiar Imagen
                    </label>
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                    <p class="text-sm text-gray-500 mt-1">Formatos: JPG, PNG, WebP. Máximo 5MB</p>
                </div>
                
                <div>
                    <label for="meta_title" class="block text-sm font-semibold text-gray-700 mb-2">
                        Meta Title (SEO)
                    </label>
                    <input type="text" id="meta_title" name="meta_title" 
                           value="<?= sanitize(old('meta_title', $product['meta_title'] ?? '')) ?>"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                </div>
                
                <div>
                    <label for="meta_description" class="block text-sm font-semibold text-gray-700 mb-2">
                        Meta Description (SEO)
                    </label>
                    <textarea id="meta_description" name="meta_description" rows="3"
                              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary"><?= sanitize(old('meta_description', $product['meta_description'] ?? '')) ?></textarea>
                </div>
            </div>
        </div>
        
        <div class="flex gap-4 mt-6 pt-6 border-t">
            <button type="submit" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-red-700 transition font-semibold">
                <i class="fas fa-save mr-2"></i>Guardar Cambios
            </button>
            <a href="<?= url('/manager/products') ?>" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                Cancelar
            </a>
        </div>
    </form>
</div>

<?php
};

ob_start();
$content();
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
