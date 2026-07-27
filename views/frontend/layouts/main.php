<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Ecommerce' ?> - <?= env('APP_NAME') ?></title>
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <?= $styles ?? '' ?>
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="<?= url('/') ?>">
                        <h1><?= env('APP_NAME') ?></h1>
                    </a>
                </div>
                
                <nav class="main-nav">
                    <ul>
                        <li><a href="<?= url('/') ?>">Inicio</a></li>
                        <li><a href="<?= url('/products') ?>">Productos</a></li>
                        <li><a href="<?= url('/cart') ?>">Carrito</a></li>
                        <?php if (is_logged_in()): ?>
                            <li><a href="<?= url('/account/profile') ?>">Mi Cuenta</a></li>
                            <li>
                                <form action="<?= url('/account/logout') ?>" method="POST" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit">Salir</button>
                                </form>
                            </li>
                        <?php else: ?>
                            <li><a href="<?= url('/account/login') ?>">Iniciar Sesión</a></li>
                            <li><a href="<?= url('/account/register') ?>">Registrarse</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    <?php if ($successMsg = get_flash('success')): ?>
        <div class="alert alert-success"><?= sanitize($successMsg) ?></div>
    <?php endif; ?>
    
    <?php if ($errorMsg = get_flash('error')): ?>
        <div class="alert alert-error"><?= sanitize($errorMsg) ?></div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="site-content">
        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= env('APP_NAME') ?>. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="<?= asset('js/main.js') ?>"></script>
    <?= $scripts ?? '' ?>
</body>
</html>
