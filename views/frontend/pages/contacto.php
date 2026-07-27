<?php 
$title = 'Contáctanos';
ob_start();
?>

<!-- Hero Section -->
<div class="bg-gradient-to-r from-primary to-red-700 text-white py-16">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Contáctanos</h1>
        <p class="text-xl opacity-90">Estamos aquí para ayudarte</p>
    </div>
</div>

<!-- Contact Section -->
<div class="container mx-auto px-4 py-12">
    <div class="max-w-6xl mx-auto">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-3 text-2xl"></i>
                    <p><?= $_SESSION['success'] ?></p>
                </div>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['errors'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                <ul class="list-disc list-inside">
                    <?php foreach ($_SESSION['errors'] as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php unset($_SESSION['errors']); ?>
        <?php endif; ?>

        <div class="grid md:grid-cols-2 gap-12">
            <!-- Contact Form -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-2xl font-bold text-secondary mb-6">Envíanos un Mensaje</h2>
                
                <form action="<?= url('/contacto/submit') ?>" method="POST" class="space-y-4">
                    <?= csrf_field() ?>
                    
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                            Nombre Completo *
                        </label>
                        <input type="text" id="name" name="name" required
                               value="<?= old('name') ?>"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                            Email *
                        </label>
                        <input type="email" id="email" name="email" required
                               value="<?= old('email') ?>"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                            Teléfono
                        </label>
                        <input type="tel" id="phone" name="phone"
                               value="<?= old('phone') ?>"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                    </div>
                    
                    <div>
                        <label for="subject" class="block text-sm font-semibold text-gray-700 mb-2">
                            Asunto
                        </label>
                        <input type="text" id="subject" name="subject"
                               value="<?= old('subject') ?>"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                    </div>
                    
                    <div>
                        <label for="message" class="block text-sm font-semibold text-gray-700 mb-2">
                            Mensaje *
                        </label>
                        <textarea id="message" name="message" rows="6" required
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary"><?= old('message') ?></textarea>
                    </div>
                    
                    <button type="submit" 
                            class="w-full px-6 py-4 bg-primary text-white rounded-lg font-bold hover:bg-primary-dark transition text-lg">
                        <i class="fas fa-paper-plane mr-2"></i>Enviar Mensaje
                    </button>
                </form>
            </div>

            <!-- Contact Info -->
            <div>
                <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
                    <h2 class="text-2xl font-bold text-secondary mb-6">Información de Contacto</h2>
                    
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-primary rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg mb-1">Dirección</h3>
                                <p class="text-gray-600">Casa Matriz: Calle 65 Oeste<br>Ciudad de Panamá, Panamá</p>
                                <a href="<?= url('/sucursales') ?>" class="text-primary text-sm font-semibold hover:underline">Ver las 5 sucursales →</a>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-primary rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-phone text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg mb-1">Teléfono</h3>
                                <p class="text-gray-600">6566-1892</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-primary rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-envelope text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg mb-1">Email</h3>
                                <p class="text-gray-600">ventas@fausto.com</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-primary rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-clock text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg mb-1">Horario</h3>
                                <p class="text-gray-600">
                                    Lun - Vie: 8:00 AM - 6:00 PM<br>
                                    Sábado: 9:00 AM - 2:00 PM
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Media -->
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h3 class="text-xl font-bold text-secondary mb-4">Síguenos en Redes Sociales</h3>
                    <div class="flex gap-4">
                        <a href="#" class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white hover:bg-blue-700 transition">
                            <i class="fab fa-facebook-f text-xl"></i>
                        </a>
                        <a href="#" class="w-12 h-12 bg-pink-600 rounded-full flex items-center justify-center text-white hover:bg-pink-700 transition">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="#" class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center text-white hover:bg-green-700 transition">
                            <i class="fab fa-whatsapp text-xl"></i>
                        </a>
                        <a href="#" class="w-12 h-12 bg-blue-400 rounded-full flex items-center justify-center text-white hover:bg-blue-500 transition">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main_tailwind.php';
?>
