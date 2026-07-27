<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin Panel' ?> - Fausto Salazar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#ed1c24',
                        secondary: '#1F2937'
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-secondary text-white flex-shrink-0">
            <div class="p-6">
                <h2 class="text-2xl font-bold text-primary">Fausto Salazar</h2>
                <p class="text-sm text-gray-400 mt-1">Panel Admin</p>
            </div>
            
            <nav class="px-4 pb-4">
                <ul class="space-y-1">
                    <li>
                        <a href="<?= url('/manager') ?>" 
                           class="flex items-center px-4 py-3 rounded-lg hover:bg-primary transition <?= (!isset($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] == '/manager' || $_SERVER['REQUEST_URI'] == '/manager/dashboard') ? 'bg-primary' : '' ?>">
                            <i class="fas fa-chart-line w-5"></i>
                            <span class="ml-3">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('/manager/products') ?>" 
                           class="flex items-center px-4 py-3 rounded-lg hover:bg-primary transition <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/products') !== false) ? 'bg-primary' : '' ?>">
                            <i class="fas fa-box w-5"></i>
                            <span class="ml-3">Productos</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('/manager/categories') ?>" 
                           class="flex items-center px-4 py-3 rounded-lg hover:bg-primary transition <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/categories') !== false) ? 'bg-primary' : '' ?>">
                            <i class="fas fa-tags w-5"></i>
                            <span class="ml-3">Categorías</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('/manager/orders') ?>" 
                           class="flex items-center px-4 py-3 rounded-lg hover:bg-primary transition <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/orders') !== false) ? 'bg-primary' : '' ?>">
                            <i class="fas fa-shopping-cart w-5"></i>
                            <span class="ml-3">Pedidos</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('/manager/users') ?>" 
                           class="flex items-center px-4 py-3 rounded-lg hover:bg-primary transition <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/users') !== false) ? 'bg-primary' : '' ?>">
                            <i class="fas fa-users w-5"></i>
                            <span class="ml-3">Usuarios</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('/manager/inventory') ?>" 
                           class="flex items-center px-4 py-3 rounded-lg hover:bg-primary transition <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/inventory') !== false) ? 'bg-primary' : '' ?>">
                            <i class="fas fa-warehouse w-5"></i>
                            <span class="ml-3">Inventario</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('/manager/erp/dashboard') ?>" 
                           class="flex items-center px-4 py-3 rounded-lg hover:bg-primary transition <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/erp') !== false) ? 'bg-primary' : '' ?>">
                            <i class="fas fa-plug w-5"></i>
                            <span class="ml-3">Integración ERP</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('/manager/settings') ?>" 
                           class="flex items-center px-4 py-3 rounded-lg hover:bg-primary transition <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/settings') !== false) ? 'bg-primary' : '' ?>">
                            <i class="fas fa-cog w-5"></i>
                            <span class="ml-3">Configuración</span>
                        </a>
                    </li>
                    <li class="pt-4 border-t border-gray-700 mt-4">
                        <a href="<?= url('/') ?>" target="_blank"
                           class="flex items-center px-4 py-3 rounded-lg hover:bg-primary transition">
                            <i class="fas fa-external-link-alt w-5"></i>
                            <span class="ml-3">Ver Sitio</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <header class="bg-white shadow-sm">
                <div class="px-6 py-4 flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-secondary"><?= $title ?? 'Dashboard' ?></h1>
                    <div class="flex items-center gap-4">
                        <span class="text-gray-600">Hola, <strong><?= $_SESSION['admin_user']['first_name'] ?? 'Admin' ?></strong></span>
                        <a href="<?= url('/manager?logout=1') ?>" 
                           class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                            <i class="fas fa-sign-out-alt mr-2"></i>Salir
                        </a>
                    </div>
                </div>
            </header>

            <!-- Flash Messages -->
            <?php if ($successMsg = $_SESSION['success'] ?? null): ?>
                <div class="mx-6 mt-4">
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative" role="alert">
                        <span class="block sm:inline"><?= sanitize($successMsg) ?></span>
                    </div>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if ($errorMsg = $_SESSION['error'] ?? null): ?>
                <div class="mx-6 mt-4">
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative" role="alert">
                        <span class="block sm:inline"><?= sanitize($errorMsg) ?></span>
                    </div>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Page Content -->
            <main class="flex-1 p-6">
                <?= $content ?? '' ?>
            </main>
        </div>
    </div>

    <script>
        // Auto-hide flash messages
        setTimeout(() => {
            document.querySelectorAll('[role="alert"]').forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>
