<?php 
$title = 'Configuración ERP';
ob_start();
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-secondary">Configuración de ERP</h2>
</div>

<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <form action="<?= url('/manager/erp/config/save') ?>" method="POST">
        <?= csrf_field() ?>
        
        <div class="space-y-6">
            <!-- Enable/Disable -->
            <div class="flex items-center">
                <input type="checkbox" id="erp_enabled" name="erp_enabled" value="1"
                       <?= $config['erp_enabled'] === 'true' ? 'checked' : '' ?>
                       class="w-5 h-5 text-primary border-gray-300 rounded focus:ring-primary">
                <label for="erp_enabled" class="ml-3 text-sm font-semibold text-gray-700">
                    Habilitar Integración ERP
                </label>
            </div>

            <!-- ERP Type -->
            <div>
                <label for="erp_type" class="block text-sm font-semibold text-gray-700 mb-2">
                    Tipo de ERP *
                </label>
                <select id="erp_type" name="erp_type" required
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                    <option value="none" <?= $config['erp_type'] === 'none' ? 'selected' : '' ?>>Ninguno</option>
                    <option value="custom" <?= $config['erp_type'] === 'custom' ? 'selected' : '' ?>>Custom REST API</option>
                    <option value="sap" <?= $config['erp_type'] === 'sap' ? 'selected' : '' ?>>SAP Business One</option>
                    <option value="odoo" <?= $config['erp_type'] === 'odoo' ? 'selected' : '' ?>>Odoo</option>
                    <option value="dynamics" <?= $config['erp_type'] === 'dynamics' ? 'selected' : '' ?>>Microsoft Dynamics</option>
                </select>
                <p class="text-sm text-gray-500 mt-1">Selecciona el tipo de ERP que utilizas</p>
            </div>

            <!-- API URL -->
            <div>
                <label for="erp_url" class="block text-sm font-semibold text-gray-700 mb-2">
                    URL de la API *
                </label>
                <input type="url" id="erp_url" name="erp_url" 
                       value="<?= sanitize($config['erp_url'] ?? '') ?>"
                       placeholder="https://erp.example.com/api"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                <p class="text-sm text-gray-500 mt-1">URL base de la API de tu ERP</p>
            </div>

            <!-- API Key -->
            <div>
                <label for="erp_key" class="block text-sm font-semibold text-gray-700 mb-2">
                    API Key / Token
                </label>
                <input type="text" id="erp_key" name="erp_key" 
                       value="<?= sanitize($config['erp_key'] ?? '') ?>"
                       placeholder="tu-api-key-secreta"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                <p class="text-sm text-gray-500 mt-1">Clave de autenticación de la API</p>
            </div>

            <!-- Info Box -->
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-semibold text-blue-800 mb-2">Información Importante</h3>
                        <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
                            <li>Los cambios se guardan en el archivo <code>.env</code></li>
                            <li>Después de guardar, prueba la conexión desde el dashboard</li>
                            <li>Para APIs personalizadas, usa el tipo "Custom REST API"</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-4 mt-8 pt-6 border-t">
            <button type="submit" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-red-700 transition font-semibold">
                <i class="fas fa-save mr-2"></i>Guardar Configuración
            </button>
            <a href="<?= url('/manager/erp/dashboard') ?>" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                Cancelar
            </a>
        </div>
    </form>
</div>

<!-- API Documentation -->
<div class="bg-white rounded-lg shadow p-6 max-w-2xl mt-6">
    <h3 class="text-lg font-bold mb-4">
        <i class="fas fa-book text-primary mr-2"></i>
        Documentación de Endpoints Esperados
    </h3>
    
    <div class="space-y-4">
        <div class="border-l-4 border-blue-500 pl-4">
            <p class="font-semibold text-sm">GET /products</p>
            <p class="text-sm text-gray-600">Obtener lista de productos</p>
        </div>
        
        <div class="border-l-4 border-green-500 pl-4">
            <p class="font-semibold text-sm">POST /products</p>
            <p class="text-sm text-gray-600">Crear nuevo producto</p>
        </div>
        
        <div class="border-l-4 border-yellow-500 pl-4">
            <p class="font-semibold text-sm">GET /inventory/{sku}</p>
            <p class="text-sm text-gray-600">Obtener stock de un producto</p>
        </div>
        
        <div class="border-l-4 border-purple-500 pl-4">
            <p class="font-semibold text-sm">POST /orders</p>
            <p class="text-sm text-gray-600">Crear orden en el ERP</p>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
