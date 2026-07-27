<?php 
$title = 'Integración ERP';
ob_start();
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-secondary">Panel de Integración ERP</h2>
</div>

<!-- ERP Status -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Estado</p>
                <p class="text-2xl font-bold mt-1">
                    <?php if ($erp_enabled): ?>
                        <span class="text-green-600">Activo</span>
                    <?php else: ?>
                        <span class="text-gray-400">Inactivo</span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="w-12 h-12 bg-<?= $erp_enabled ? 'green' : 'gray' ?>-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-plug text-2xl text-<?= $erp_enabled ? 'green' : 'gray' ?>-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Tipo de ERP</p>
                <p class="text-2xl font-bold mt-1 capitalize"><?= $erp_type ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-server text-2xl text-blue-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Sincronizaciones</p>
                <p class="text-2xl font-bold mt-1"><?= $stats['total_syncs'] ?></p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-sync text-2xl text-purple-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Exitosas / Fallidas</p>
                <p class="text-xl font-bold mt-1">
                    <span class="text-green-600"><?= $stats['successful'] ?></span>
                    <span class="text-gray-400">/</span>
                    <span class="text-red-600"><?= $stats['failed'] ?></span>
                </p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-chart-bar text-2xl text-yellow-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Manual Sync Actions -->
<?php if ($erp_enabled): ?>
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-bold mb-4">Sincronización Manual</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <form action="<?= url('/manager/erp/sync-products') ?>" method="POST">
                <?= csrf_field() ?>
                <button type="submit" class="w-full px-6 py-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-box mr-2"></i>
                    <span class="font-semibold">Sincronizar Productos</span>
                    <p class="text-sm mt-1 opacity-90">Traer productos desde el ERP</p>
                </button>
            </form>

            <form action="<?= url('/manager/erp/sync-inventory') ?>" method="POST">
                <?= csrf_field() ?>
                <button type="submit" class="w-full px-6 py-4 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-warehouse mr-2"></i>
                    <span class="font-semibold">Actualizar Inventario</span>
                    <p class="text-sm mt-1 opacity-90">Sincronizar stock desde ERP</p>
                </button>
            </form>

            <button onclick="testConnection()" class="w-full px-6 py-4 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                <i class="fas fa-heartbeat mr-2"></i>
                <span class="font-semibold">Probar Conexión</span>
                <p class="text-sm mt-1 opacity-90">Verificar estado del ERP</p>
            </button>
        </div>
    </div>
<?php else: ?>
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    ERP no configurado. 
                    <a href="<?= url('/manager/erp/config') ?>" class="font-semibold underline">Configurar ahora</a>
                </p>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Sync Logs -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-bold">Historial de Sincronización</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Dirección</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Registros</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Fecha</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Error</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if (!empty($logs)): ?>
                    <?php foreach ($logs as $log): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 capitalize"><?= $log['sync_type'] ?></td>
                            <td class="px-6 py-4">
                                <?php if ($log['direction'] == 'from_erp'): ?>
                                    <span class="text-blue-600"><i class="fas fa-arrow-down mr-1"></i>Desde ERP</span>
                                <?php else: ?>
                                    <span class="text-green-600"><i class="fas fa-arrow-up mr-1"></i>Hacia ERP</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($log['status'] == 'success'): ?>
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-semibold">Exitoso</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs font-semibold">Fallido</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 font-semibold"><?= $log['records_count'] ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                            <td class="px-6 py-4 text-sm text-red-600 max-w-xs truncate">
                                <?= $log['error_message'] ? sanitize($log['error_message']) : '-' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-history text-4xl mb-2 block text-gray-300"></i>
                            No hay registros de sincronización
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function testConnection() {
    fetch('<?= url('/manager/erp/test-connection') ?>')
        .then(r => r.json())
        .then(data => {
            alert(data.message);
        })
        .catch(e => {
            alert('Error al probar conexión');
        });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
