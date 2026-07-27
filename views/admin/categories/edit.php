<?php 
$title = 'Editar Categoría';
ob_start();
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-secondary">Editar Categoría</h2>
</div>

<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <form action="<?= url('/manager/categories/' . $category['id'] . '/update') ?>" method="POST">
        <?= csrf_field() ?>
        
        <div class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nombre *</label>
                <input type="text" id="name" name="name" required 
                       value="<?= sanitize(old('name', $category['name'] ?? '')) ?>"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
            </div>
            
            <div>
                <label for="icon_class" class="block text-sm font-semibold text-gray-700 mb-2">Clase de Icono</label>
                <input type="text" id="icon_class" name="icon_class" 
                       value="<?= sanitize(old('icon_class', $category['icon_class'] ?? '')) ?>"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
            </div>
            
            <div>
                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Descripción</label>
                <textarea id="description" name="description" rows="4"
                          class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary"><?= sanitize(old('description', $category['description'] ?? '')) ?></textarea>
            </div>
            
            <div>
                <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">Estado</label>
                <select id="status" name="status"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                    <option value="active" <?= old('status', $category['status']) == 'active' ? 'selected' : '' ?>>Activo</option>
                    <option value="inactive" <?= old('status', $category['status']) == 'inactive' ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
        </div>
        
        <div class="flex gap-4 mt-6 pt-6 border-t">
            <button type="submit" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-red-700 transition font-semibold">
                <i class="fas fa-save mr-2"></i>Guardar Cambios
            </button>
            <a href="<?= url('/manager/categories') ?>" 
               class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                Cancelar
            </a>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
