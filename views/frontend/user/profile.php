<?php
ob_start();
$title = 'Mi Cuenta - Fausto Salazar, S.A.';
?>

<div class="bg-secondary py-10">
    <div class="container mx-auto px-4">
        <h1 class="font-display text-4xl font-bold text-white uppercase tracking-wide">
            <i class="fas fa-user text-primary mr-3"></i>Mi Cuenta
        </h1>
    </div>
</div>

<div class="container mx-auto px-4 py-10">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-4xl">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-md border border-gray-100 p-6">
            <h3 class="font-display text-xl font-bold text-secondary uppercase tracking-wide mb-4">Información Personal</h3>
            <div class="space-y-3">
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600">Nombre</span>
                    <strong class="text-secondary"><?= sanitize($user['first_name'] . ' ' . $user['last_name']) ?></strong>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600">Correo</span>
                    <strong class="text-secondary"><?= sanitize($user['email']) ?></strong>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600">Teléfono</span>
                    <strong class="text-secondary"><?= sanitize($user['phone'] ?? 'No registrado') ?></strong>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-gray-600">Cliente desde</span>
                    <strong class="text-secondary"><?= date('d/m/Y', strtotime($user['created_at'])) ?></strong>
                </div>
            </div>
        </div>

        <div>
            <a href="<?= url('/account/orders') ?>"
               class="block w-full text-center bg-primary text-white py-3 rounded-lg hover:bg-primary-dark transition font-semibold shadow-md mb-3">
                <i class="fas fa-box mr-2"></i>Mis Pedidos
            </a>
            <form action="<?= url('/account/logout') ?>" method="POST">
                <?= csrf_field() ?>
                <button type="submit"
                        class="block w-full text-center bg-gray-100 text-secondary py-3 rounded-lg hover:bg-gray-200 transition font-medium">
                    <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
                </button>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main_tailwind.php';
?>
