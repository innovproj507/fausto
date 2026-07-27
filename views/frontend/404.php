<?php
ob_start();
$title = '404 - Página no encontrada';
?>

<div class="container mx-auto px-4 py-24 text-center">
    <i class="fas fa-tools text-7xl text-primary/20 mb-6"></i>
    <h1 class="font-display text-8xl font-bold text-secondary leading-none">404</h1>
    <h2 class="font-display text-2xl font-bold text-secondary uppercase tracking-wide mt-4 mb-2">Página no encontrada</h2>
    <p class="text-gray-600 mb-10">Lo sentimos, la página que buscas no existe o fue movida.</p>

    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="<?= url('/') ?>"
           class="inline-flex items-center justify-center gap-2 bg-primary text-white px-8 py-3 rounded-lg hover:bg-primary-dark transition font-semibold shadow-md">
            <i class="fas fa-home"></i>Volver al Inicio
        </a>
        <a href="<?= url('/products') ?>"
           class="inline-flex items-center justify-center gap-2 bg-secondary text-white px-8 py-3 rounded-lg hover:bg-gray-700 transition font-semibold">
            <i class="fas fa-th-large"></i>Ver Productos
        </a>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/main_tailwind.php';
?>
