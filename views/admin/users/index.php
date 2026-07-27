<?php 
$title = 'Usuarios';
ob_start();
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-secondary">Gestión de Usuarios</h2>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form method="GET" class="flex gap-4">
        <input type="text" name="search" placeholder="Buscar por nombre o email..." 
               value="<?= sanitize($search ?? '') ?>"
               class="flex-1 px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
        <select name="role" class="px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
            <option value="">Todos los roles</option>
            <option value="1" <?= ($role ?? '') == '1' ? 'selected' : '' ?>>Admin</option>
            <option value="2" <?= ($role ?? '') == '2' ? 'selected' : '' ?>>Cliente</option>
        </select>
        <button type="submit" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-red-700 transition font-semibold">
            <i class="fas fa-search mr-2"></i>Filtrar
        </button>
    </form>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b-2 border-gray-200">
            <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Usuario</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Email</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Rol</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Estado</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Registro</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-semibold"><?= sanitize($user['first_name'] . ' ' . $user['last_name']) ?></td>
                        <td class="px-6 py-4 text-gray-600"><?= sanitize($user['email']) ?></td>
                        <td class="px-6 py-4">
                            <?php if ($user['role_id'] == 1): ?>
                                <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-semibold">
                                    <i class="fas fa-crown mr-1"></i>Admin
                                </span>
                            <?php else: ?>
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">
                                    <i class="fas fa-user mr-1"></i>Cliente
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($user['status'] == 'active'): ?>
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Activo</span>
                            <?php else: ?>
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-semibold">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-gray-600"><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                        <td class="px-6 py-4">
                            <a href="<?= url('/manager/users/' . $user['id']) ?>" 
                               class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-eye text-lg"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <i class="fas fa-users text-6xl text-gray-300 mb-4 block"></i>
                        <p class="text-gray-500 text-lg">No hay usuarios</p>
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
