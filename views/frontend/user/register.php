<?php 
ob_start(); 
$title = 'Registro - Fausto Salazar, S.A.';
?>

<div class="container mx-auto px-4 py-12">
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user-plus text-3xl text-white"></i>
                </div>
                <h2 class="text-3xl font-bold text-secondary">Crear Cuenta</h2>
                <p class="text-gray-600 mt-2">Únete a Fausto Salazar</p>
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
            
            <form action="<?= url('/account/register') ?>" method="POST">
                <?= csrf_field() ?>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="first_name" class="block text-sm font-semibold text-gray-700 mb-2">
                            Nombre *
                        </label>
                        <input type="text" id="first_name" name="first_name" required 
                               value="<?= old('first_name') ?>"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                    </div>
                    
                    <div>
                        <label for="last_name" class="block text-sm font-semibold text-gray-700 mb-2">
                            Apellido
                        </label>
                        <input type="text" id="last_name" name="last_name" 
                               value="<?= old('last_name') ?>"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        Correo Electrónico *
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-400">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input type="email" id="email" name="email" required 
                               value="<?= old('email') ?>"
                               placeholder="tu@email.com"
                               class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        Contraseña * (mínimo 8 caracteres)
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-400">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" id="password" name="password" required 
                               minlength="8"
                               placeholder="••••••••"
                               class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                        Confirmar Contraseña *
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-400">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" id="password_confirmation" name="password_confirmation" required 
                               minlength="8"
                               placeholder="••••••••"
                               class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                    </div>
                </div>
                
                <button type="submit" 
                        class="w-full bg-primary text-white py-3 rounded-lg hover:bg-primary-dark transition font-semibold text-lg mb-4">
                    <i class="fas fa-user-check mr-2"></i>Crear Cuenta
                </button>
            </form>
            
            <div class="text-center pt-4 border-t">
                <p class="text-gray-600">
                    ¿Ya tienes cuenta? 
                    <a href="<?= url('/account/login') ?>" class="text-primary hover:underline font-semibold">
                        Inicia sesión aquí
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main_tailwind.php';
?>
