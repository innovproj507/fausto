<?php 
$title = 'Categorías';
ob_start();
?>

<div class="mb-6 flex justify-between items-center">
    <h2 class="text-2xl font-bold text-secondary">Categorías</h2>
    <a href="<?= url('/manager/categories/create') ?>" 
       class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-red-700 transition font-semibold">
        <i class="fas fa-plus mr-2"></i>Crear Categoría
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b-2 border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Nombre</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Slug</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Icono</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Estado</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-semibold text-gray-900"><?= sanitize($category['name']) ?></td>
                            <td class="px-6 py-4 text-gray-600 font-mono text-sm"><?= sanitize($category['slug']) ?></td>
                            <td class="px-6 py-4">
                                <i class="<?= $category['icon_class'] ?? 'fas fa-box' ?> text-2xl text-primary"></i>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($category['status'] == 'active'): ?>
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Activo</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <a href="<?= url('/manager/categories/' . $category['id'] . '/edit') ?>" 
                                       class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-edit text-lg"></i>
                                    </a>
                                    <form action="<?= url('/manager/categories/' . $category['id'] . '/delete') ?>" 
                                          method="POST" class="inline"
                                          onsubmit="return confirm('¿Eliminar esta categoría?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash text-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <i class="fas fa-tags text-6xl text-gray-300 mb-4 block"></i>
                            <p class="text-gray-500 text-lg">No hay categorías</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
