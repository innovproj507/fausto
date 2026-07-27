<?php
// Real cart preview (badge + sidebar) — pulled from DB, not session, since CartController persists to carts/cart_items
$cartPreviewItems = [];
$cartPreviewTotal = 0;
$cartCount = 0;
$headerCategories = [];
try {
    $db = app()->getContainer()->make(\App\Core\Database\Connection::class);

    $userId = $_SESSION['user']['id'] ?? null;
    $cartRow = $userId
        ? $db->fetchOne('SELECT id FROM carts WHERE user_id = ?', [$userId])
        : $db->fetchOne('SELECT id FROM carts WHERE session_id = ?', [session_id()]);

    if ($cartRow) {
        $cartPreviewItems = $db->fetchAll(
            'SELECT ci.*, p.name, p.slug, p.featured_image
             FROM cart_items ci JOIN products p ON ci.product_id = p.id
             WHERE ci.cart_id = ?',
            [$cartRow['id']]
        );
        foreach ($cartPreviewItems as $ci) {
            $cartCount += (int) $ci['quantity'];
            $cartPreviewTotal += $ci['price'] * $ci['quantity'];
        }
    }

    $headerCategories = $db->fetchAll(
        'SELECT id, name, slug, icon_class FROM categories WHERE status = "active" ORDER BY name'
    );
} catch (\Throwable $e) {
    // Layout must never fatal on a DB hiccup
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Fausto Salazar, S.A.' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#ed1c24',
                        'primary-dark': '#b8141a',
                        secondary: '#1F2937',
                        accent: '#f5a623'
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        display: ['"Barlow Condensed"', 'ui-sans-serif', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        ::selection { background: #ed1c24; color: #fff; }
        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 6px; }
        ::-webkit-scrollbar-thumb:hover { background: #ed1c24; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="bg-white text-gray-800">
    <!-- Top Bar -->
    <div class="bg-secondary text-white text-xs sm:text-sm">
        <div class="container mx-auto px-4 py-2 flex justify-between items-center">
            <span class="hidden sm:flex items-center gap-2 text-gray-300">
                <i class="fas fa-truck text-accent"></i>+9,000 productos en stock para tu proyecto
            </span>
            <span class="sm:hidden text-gray-300">Tu ferretería de confianza</span>
            <div class="flex items-center gap-3 sm:gap-5">
                <a href="<?= url('/sucursales') ?>" class="hover:text-accent transition flex items-center gap-1">
                    <i class="fas fa-map-marker-alt"></i><span class="hidden sm:inline">Sucursales</span>
                </a>
                <span class="hidden sm:inline text-gray-600">|</span>
                <a href="<?= url('/account/orders') ?>" class="hidden sm:flex hover:text-accent transition items-center gap-1">
                    <i class="fas fa-truck-loading"></i>Mis Pedidos
                </a>
                <span class="hidden sm:inline text-gray-600">|</span>
                <?php if (isset($_SESSION['user'])): ?>
                    <a href="<?= url('/account/profile') ?>" class="hover:text-accent transition flex items-center gap-1">
                        <i class="fas fa-user"></i><span class="hidden sm:inline">Mi Cuenta</span>
                    </a>
                <?php else: ?>
                    <a href="<?= url('/account/login') ?>" class="hover:text-accent transition flex items-center gap-1">
                        <i class="fas fa-user"></i><span class="hidden sm:inline">Ingresar</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center gap-4 md:gap-6">
                <!-- Logo -->
                <div class="flex items-center flex-shrink-0">
                    <a href="<?= url('/') ?>" class="flex items-center transition hover:scale-105">
                        <img src="<?= asset('logo.png') ?>" alt="Fausto Salazar" class="h-12 md:h-16">
                    </a>
                </div>

                <!-- Search Bar -->
                <div class="hidden md:flex flex-1 max-w-2xl">
                    <form action="<?= url('/products') ?>" method="GET" class="relative w-full flex">
                        <input type="text" name="search" placeholder="Buscar productos, SKU, marca..."
                            value="<?= sanitize($_GET['search'] ?? '') ?>"
                            class="w-full pl-5 pr-4 py-3 bg-gray-100 border-2 border-gray-100 rounded-l-lg focus:outline-none focus:border-primary focus:bg-white transition">
                        <button type="submit"
                            class="bg-primary text-white px-6 sm:px-8 rounded-r-lg hover:bg-primary-dark transition font-bold uppercase tracking-wide text-sm">
                            Buscar
                        </button>
                    </form>
                </div>

                <!-- Support + Cart -->
                <div class="flex items-center gap-4 md:gap-6 flex-shrink-0">
                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', env('CONTACT_PHONE', '+507 0000-0000')) ?>"
                       class="hidden lg:flex items-center gap-3 text-secondary">
                        <i class="fas fa-headset text-3xl text-primary"></i>
                        <span class="leading-tight">
                            <span class="block text-xs text-gray-500">Soporte / Ventas</span>
                            <span class="block font-bold"><?= env('CONTACT_PHONE', '+507 0000-0000') ?></span>
                        </span>
                    </a>

                    <button onclick="toggleCart()"
                        class="flex flex-col items-center text-secondary hover:text-primary transition relative">
                        <i class="fas fa-shopping-bag text-2xl"></i>
                        <span class="text-xs mt-1 font-medium hidden sm:inline">Carrito</span>
                        <span id="cartCount"
                            class="absolute -top-1.5 -right-2.5 bg-primary text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                            <?= $cartCount ?>
                        </span>
                    </button>
                </div>
            </div>

            <!-- Mobile Search -->
            <div class="md:hidden mt-4">
                <form action="<?= url('/products') ?>" method="GET" class="relative flex">
                    <input type="text" name="search" placeholder="Buscar productos..."
                        value="<?= sanitize($_GET['search'] ?? '') ?>"
                        class="w-full pl-4 pr-4 py-2.5 bg-gray-100 border-2 border-gray-100 rounded-l-lg focus:outline-none focus:border-primary">
                    <button type="submit" class="bg-primary text-white px-5 rounded-r-lg">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Category / Nav Bar -->
        <nav class="bg-secondary text-white border-t border-black/20">
            <div class="container mx-auto px-4 flex items-center justify-between">
                <div class="flex items-center">
                    <!-- Browse Categories Dropdown -->
                    <div class="relative">
                        <button onclick="toggleCategoryMenu()" id="categoryMenuBtn"
                            class="flex items-center gap-2 px-4 py-3 bg-primary hover:bg-primary-dark transition text-sm font-bold uppercase tracking-wide">
                            <i class="fas fa-bars"></i>Ver Categorías
                        </button>
                        <div id="categoryMenu"
                             class="hidden absolute left-0 top-full w-72 bg-white text-secondary shadow-2xl rounded-b-lg overflow-hidden z-50 max-h-96 overflow-y-auto">
                            <?php foreach ($headerCategories as $cat): ?>
                                <a href="<?= url('/category/' . $cat['slug']) ?>"
                                   class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 last:border-b-0 hover:bg-gray-50 hover:text-primary transition">
                                    <i class="<?= $cat['icon_class'] ?? 'fas fa-box' ?> w-5 text-primary"></i>
                                    <span class="text-sm font-medium"><?= sanitize($cat['name']) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Main Links -->
                    <ul class="flex flex-wrap">
                        <?php
                            $navLinks = [
                                '/' => 'Inicio',
                                '/products' => 'Productos',
                                '/sucursales' => 'Sucursales',
                                '/nosotros' => 'Nosotros',
                                '/contacto' => 'Contacto',
                            ];
                            $currentUri = $_SERVER['REQUEST_URI'] ?? '/';
                        ?>
                        <?php foreach ($navLinks as $href => $label): ?>
                            <?php $isActive = $href === '/' ? ($currentUri === '/' || $currentUri === '') : str_starts_with($currentUri, $href); ?>
                            <li>
                                <a href="<?= url($href) ?>"
                                   class="block px-4 py-3 text-sm font-semibold uppercase tracking-wide transition border-b-2 <?= $isActive ? 'border-accent text-accent' : 'border-transparent hover:text-accent' ?>">
                                    <?= $label ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Social -->
                <div class="hidden lg:flex items-center gap-4 text-gray-300">
                    <a href="#" class="hover:text-accent transition"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="hover:text-accent transition"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="hover:text-accent transition"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Flash Messages -->
    <?php if ($successMsg = get_flash('success')): ?>
        <div class="container mx-auto px-4 mt-4">
            <div class="bg-green-50 border-l-4 border-green-500 text-green-800 px-5 py-4 rounded-lg shadow-sm flex items-center" role="alert">
                <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                <span><?= sanitize($successMsg) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($errorMsg = get_flash('error')): ?>
        <div class="container mx-auto px-4 mt-4">
            <div class="bg-red-50 border-l-4 border-primary text-red-800 px-5 py-4 rounded-lg shadow-sm flex items-center" role="alert">
                <i class="fas fa-exclamation-circle text-primary text-xl mr-3"></i>
                <span><?= sanitize($errorMsg) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main>
        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <footer class="bg-secondary text-white mt-12">
        <!-- Newsletter -->
        <div class="border-b border-white/10">
            <div class="container mx-auto px-4 py-8 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <i class="fas fa-envelope-open-text text-4xl text-primary"></i>
                    <div>
                        <h3 class="font-display text-xl font-bold uppercase tracking-wide">Únete a Nuestro Boletín</h3>
                        <p class="text-gray-400 text-sm">Recibe novedades de productos y promociones</p>
                    </div>
                </div>
                <form class="flex w-full md:w-auto">
                    <input type="email" placeholder="Tu correo electrónico" required
                           class="px-4 py-3 rounded-l-lg text-secondary w-full md:w-72 focus:outline-none">
                    <button type="submit" class="bg-primary hover:bg-primary-dark transition px-6 py-3 rounded-r-lg font-bold uppercase tracking-wide text-sm">
                        Suscribirme
                    </button>
                </form>
            </div>
        </div>

        <div class="container mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <!-- Contact -->
                <div>
                    <h3 class="text-lg font-bold mb-4 font-display uppercase tracking-wide text-accent">Contáctanos</h3>
                    <ul class="space-y-3 text-gray-300">
                        <li class="flex items-start">
                            <i class="fas fa-map-marker-alt text-primary mt-1 mr-3 w-4"></i>
                            <span>Casa Matriz: Calle 65 Oeste, Panamá</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone text-primary mr-3 w-4"></i>
                            <span><?= env('CONTACT_PHONE', '6566-1892') ?></span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope text-primary mr-3 w-4"></i>
                            <span><?= env('CONTACT_EMAIL', 'ventas@fausto.com') ?></span>
                        </li>
                    </ul>
                    <div class="flex gap-3 mt-5">
                        <a href="#" class="w-9 h-9 bg-white/10 rounded-full flex items-center justify-center hover:bg-primary hover:scale-110 transition">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="w-9 h-9 bg-white/10 rounded-full flex items-center justify-center hover:bg-primary hover:scale-110 transition">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-9 h-9 bg-white/10 rounded-full flex items-center justify-center hover:bg-primary hover:scale-110 transition">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>

                <!-- Productos -->
                <div>
                    <h3 class="text-lg font-bold mb-4 font-display uppercase tracking-wide text-accent">Productos</h3>
                    <ul class="space-y-2">
                        <li><a href="<?= url('/products') ?>" class="text-gray-300 hover:text-primary hover:pl-1 transition-all">Catálogo Completo</a></li>
                        <li><a href="<?= url('/cart') ?>" class="text-gray-300 hover:text-primary hover:pl-1 transition-all">Carrito de Compras</a></li>
                        <li><a href="<?= url('/account/orders') ?>" class="text-gray-300 hover:text-primary hover:pl-1 transition-all">Mis Pedidos</a></li>
                    </ul>
                </div>

                <!-- Empresa -->
                <div>
                    <h3 class="text-lg font-bold mb-4 font-display uppercase tracking-wide text-accent">Nuestra Empresa</h3>
                    <ul class="space-y-2">
                        <li><a href="<?= url('/nosotros') ?>" class="text-gray-300 hover:text-primary hover:pl-1 transition-all">Sobre Nosotros</a></li>
                        <li><a href="<?= url('/sucursales') ?>" class="text-gray-300 hover:text-primary hover:pl-1 transition-all">Nuestras Sucursales</a></li>
                        <li><a href="<?= url('/contacto') ?>" class="text-gray-300 hover:text-primary hover:pl-1 transition-all">Contacto</a></li>
                    </ul>
                </div>

                <!-- Categorias -->
                <div>
                    <h3 class="text-lg font-bold mb-4 font-display uppercase tracking-wide text-accent">Categorías Populares</h3>
                    <ul class="space-y-2">
                        <?php foreach (array_slice($headerCategories, 0, 5) as $cat): ?>
                            <li><a href="<?= url('/category/' . $cat['slug']) ?>" class="text-gray-300 hover:text-primary hover:pl-1 transition-all"><?= sanitize($cat['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Bottom Footer -->
            <div class="border-t border-white/10 pt-6 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-gray-400 text-sm text-center sm:text-left">
                    &copy; <?= date('Y') ?> Fausto Salazar, S.A. Todos los derechos reservados.
                </p>
                <div class="flex items-center gap-3 text-2xl text-gray-400">
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                    <i class="fab fa-cc-amex"></i>
                    <i class="fas fa-money-bill-wave text-lg"></i>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to top -->
    <button id="backToTop" onclick="window.scrollTo({top:0,behavior:'smooth'})"
        class="hidden fixed bottom-6 right-6 w-12 h-12 bg-primary text-white rounded-full shadow-lg hover:bg-primary-dark transition items-center justify-center z-40">
        <i class="fas fa-chevron-up"></i>
    </button>

    <!-- Shopping Cart Sidebar -->
    <div id="cartSidebar"
        class="fixed top-0 right-0 h-full w-96 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 z-50 flex flex-col">
        <div class="p-6 border-b flex justify-between items-center bg-secondary text-white">
            <h3 class="text-xl font-bold font-display uppercase tracking-wide"><i class="fas fa-shopping-bag mr-2 text-primary"></i>Carrito de Compras</h3>
            <button onclick="toggleCart()" class="text-2xl hover:text-primary transition leading-none">&times;</button>
        </div>

        <div id="cartItems" class="flex-1 overflow-y-auto p-6">
            <?php if (!empty($cartPreviewItems)): ?>
                <?php foreach ($cartPreviewItems as $item): ?>
                    <div class="flex items-center gap-4 mb-4 pb-4 border-b">
                        <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0 overflow-hidden">
                            <?php if (!empty($item['featured_image'])): ?>
                                <img src="<?= url('uploads/' . $item['featured_image']) ?>" alt="" class="w-full h-full object-cover">
                            <?php else: ?>
                                <i class="fas fa-box text-2xl text-gray-400"></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-secondary text-sm line-clamp-2"><?= sanitize($item['name']) ?></h4>
                            <p class="text-sm text-gray-600">Cant: <?= $item['quantity'] ?></p>
                            <p class="text-primary font-bold">$<?= number_format($item['price'], 2) ?></p>
                        </div>
                        <form action="<?= url('/cart/remove') ?>" method="POST" class="inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                            <button type="submit" class="text-gray-400 hover:text-primary transition">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-shopping-basket text-5xl text-gray-200 mb-4"></i>
                    <p class="text-gray-500">Tu carrito está vacío</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="border-t p-6 bg-gray-50">
            <div class="flex justify-between mb-4 text-lg font-semibold">
                <span>Subtotal:</span>
                <span id="cartTotal" class="text-primary">$<?= number_format($cartPreviewTotal, 2) ?></span>
            </div>
            <a href="<?= url('/checkout') ?>"
                class="block w-full bg-primary text-white py-3 rounded-lg hover:bg-primary-dark transition font-semibold mb-2 text-center shadow-md hover:shadow-lg">
                Proceder al Pago
            </a>
            <button onclick="toggleCart()"
                class="w-full bg-secondary text-white py-3 rounded-lg hover:bg-gray-700 transition">
                Continuar Comprando
            </button>
        </div>
    </div>

    <!-- Cart Overlay -->
    <div id="cartOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden" onclick="toggleCart()"></div>

    <script>
        function toggleCart() {
            document.getElementById('cartSidebar').classList.toggle('translate-x-full');
            document.getElementById('cartOverlay').classList.toggle('hidden');
        }

        function toggleCategoryMenu() {
            document.getElementById('categoryMenu').classList.toggle('hidden');
        }
        document.addEventListener('click', function (e) {
            const menu = document.getElementById('categoryMenu');
            const btn = document.getElementById('categoryMenuBtn');
            if (menu && !menu.contains(e.target) && !btn.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });

        // Back to top visibility
        window.addEventListener('scroll', function () {
            document.getElementById('backToTop').classList.toggle('hidden', window.scrollY < 400);
            document.getElementById('backToTop').classList.toggle('flex', window.scrollY >= 400);
        });

        // Auto-hide flash messages
        setTimeout(() => {
            document.querySelectorAll('[role="alert"]').forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>
