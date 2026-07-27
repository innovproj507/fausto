<?php 
$title = 'Configuración';
ob_start();
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-secondary">Configuración del Sistema</h2>
</div>

<div class="bg-white rounded-lg shadow p-6 max-w-3xl">
    <form action="<?= url('/manager/settings/update') ?>" method="POST">
        <?= csrf_field() ?>
        
        <div class="space-y-6">
            <!-- General -->
            <div>
                <h3 class="text-lg font-bold mb-4 pb-2 border-b">Información General</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre de la Tienda</label>
                        <input type="text" name="app_name"
                               value="<?= sanitize($settings['app_name'] ?? env('APP_NAME', 'Fausto Salazar, S.A.')) ?>"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Email de Contacto</label>
                            <input type="email" name="contact_email"
                                   value="<?= sanitize($settings['contact_email'] ?? 'ventas@fausto.com') ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Teléfono</label>
                            <input type="text" name="contact_phone"
                                   value="<?= sanitize($settings['contact_phone'] ?? '+507 0000-0000') ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO -->
            <div>
                <h3 class="text-lg font-bold mb-4 pb-2 border-b">SEO</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Meta Description</label>
                        <textarea name="meta_description" rows="3"
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                                  placeholder="Descripción de la tienda para motores de búsqueda"><?= sanitize($settings['meta_description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Social Media -->
            <div>
                <h3 class="text-lg font-bold mb-4 pb-2 border-b">Redes Sociales</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fab fa-facebook text-blue-600 mr-2"></i>Facebook
                        </label>
                        <input type="url" name="facebook_url"
                               value="<?= sanitize($settings['facebook_url'] ?? '') ?>"
                               placeholder="https://facebook.com/..."
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fab fa-instagram text-pink-600 mr-2"></i>Instagram
                        </label>
                        <input type="url" name="instagram_url"
                               value="<?= sanitize($settings['instagram_url'] ?? '') ?>"
                               placeholder="https://instagram.com/..."
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fab fa-whatsapp text-green-600 mr-2"></i>WhatsApp
                        </label>
                        <input type="text" name="whatsapp"
                               value="<?= sanitize($settings['whatsapp'] ?? '') ?>"
                               placeholder="+507 0000-0000"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                    </div>
                </div>
            </div>

            <!-- Maintenance Mode -->
            <div>
                <h3 class="text-lg font-bold mb-4 pb-2 border-b">Modo Mantenimiento</h3>
                <div class="space-y-4">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="maintenance_mode" value="1"
                               <?= !empty($settings['maintenance_mode']) ? 'checked' : '' ?>
                               class="w-5 h-5 accent-primary">
                        <span class="text-sm font-semibold text-gray-700">
                            Activar modo mantenimiento (bloquea la tienda a los visitantes; los administradores con sesión iniciada siguen viendo el sitio con normalidad)
                        </span>
                    </label>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Mensaje para los visitantes</label>
                        <textarea name="maintenance_message" rows="3"
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                                  placeholder="Estamos realizando tareas de mantenimiento. Vuelve pronto."><?= sanitize($settings['maintenance_message'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-8 pt-6 border-t">
            <button type="submit" 
                    class="px-8 py-3 bg-primary text-white rounded-lg hover:bg-red-700 transition font-semibold">
                <i class="fas fa-save mr-2"></i>Guardar Cambios
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
