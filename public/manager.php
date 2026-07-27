<?php

/**
 * Admin Panel Entry Point
 * Punto de entrada para el panel de administración
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;
use App\Core\Response;
use App\Core\Security\CsrfProtection;
use App\Core\Security\LoginThrottle;

// Create and bootstrap application
$app = new Application(dirname(__DIR__));
$app->bootstrap();

// Manejar logout
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_user']);
    header('Location: /manager');
    exit;
}

// Manejar login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['_token'] ?? '')) {
        $login_error = 'Tu sesión expiró. Por favor intenta de nuevo.';
    } else {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $ip = \App\Core\Request::capture()->ip();

        $db = \App\Core\Database\Connection::getInstance([
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? 3306,
            'database' => $_ENV['DB_DATABASE'] ?? 'ecommerce',
            'username' => $_ENV['DB_USERNAME'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4'
        ]);

        $throttle = new LoginThrottle($db);

        if ($throttle->tooManyAttempts($email, $ip)) {
            $minutes = $throttle->minutesUntilRetry($email, $ip);
            $login_error = "Demasiados intentos fallidos. Intenta de nuevo en {$minutes} minuto(s).";
        } else {
            $user = $db->fetchOne(
                'SELECT * FROM users WHERE email = ? AND role_id = 1 AND status = "active"',
                [$email]
            );

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['admin_user'] = [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                ];
                header('Location: /manager');
                exit;
            } else {
                $throttle->recordFailure($email, $ip);
                $login_error = 'Credenciales inválidas o no tienes permisos de administrador';
            }
        }
    }
}

// Si NO está logueado como admin, mostrar formulario de login
if (!isset($_SESSION['admin_user'])) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - <?= env('APP_NAME') ?></title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: linear-gradient(135deg, #ed1c24 0%, #c41820 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .login-container {
                background: white;
                padding: 3rem;
                border-radius: 12px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                max-width: 400px;
                width: 100%;
            }
            .logo {
                text-align: center;
                margin-bottom: 2rem;
            }
            .logo-icon {
                width: 80px;
                height: 80px;
                background: linear-gradient(135deg, #ed1c24 0%, #c41820 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 1rem;
                font-size: 2rem;
                color: white;
            }
            h1 {
                color: #1f2937;
                font-size: 1.75rem;
                margin-bottom: 0.5rem;
                text-align: center;
            }
            p {
                color: #6b7280;
                text-align: center;
                margin-bottom: 2rem;
            }
            .form-group {
                margin-bottom: 1.5rem;
            }
            label {
                display: block;
                margin-bottom: 0.5rem;
                color: #374151;
                font-weight: 500;
            }
            input {
                width: 100%;
                padding: 0.75rem;
                border: 2px solid #e5e7eb;
                border-radius: 8px;
                font-size: 1rem;
                transition: border-color 0.3s;
            }
            input:focus {
                outline: none;
                border-color: #ed1c24;
            }
            button {
                width: 100%;
                padding: 0.875rem;
                background: linear-gradient(135deg, #ed1c24 0%, #c41820 100%);
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 1rem;
                font-weight: 600;
                cursor: pointer;
                transition: transform 0.2s;
            }
            button:hover {
                transform: translateY(-2px);
            }
            .alert {
                padding: 1rem;
                background: #fee2e2;
                border: 1px solid #fca5a5;
                border-radius: 8px;
                color: #991b1b;
                margin-bottom: 1.5rem;
            }
            .footer-link {
                text-align: center;
                margin-top: 1.5rem;
                color: #6b7280;
                font-size: 0.875rem;
            }
            .footer-link a {
                color: #ed1c24;
                text-decoration: none;
            }
            .footer-link a:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="logo">
                <div class="logo-icon">🔐</div>
                <h1>Panel de Administración</h1>
                <p>Ingresa tus credenciales para continuar</p>
            </div>
            
            <?php if (isset($login_error)): ?>
                <div class="alert">
                    <?= htmlspecialchars($login_error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" required autofocus 
                           placeholder="admin@example.com">
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="••••••••">
                </div>
                
                <input type="hidden" name="admin_login" value="1">
                <?= csrf_field() ?>

                <button type="submit">Iniciar Sesión</button>
            </form>
            
            <div class="footer-link">
                <a href="/">← Volver al sitio web</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// USUARIO ADMIN YA LOGUEADO - Mostrar dashboard
// Get router
$router = $app->getRouter();

// All routes below get CsrfProtection; the middleware itself is a no-op for
// GET requests, so this only actually checks the token on POST/PUT/DELETE/PATCH.
$router->group(['middleware' => [CsrfProtection::class]], function ($router) {

    // Dashboard
    $router->get('/', 'App\Domain\Admin\DashboardController@index');
    $router->get('/dashboard', 'App\Domain\Admin\DashboardController@index');

    // Products Management
    $router->get('/products', 'App\Domain\Admin\ProductController@index');
    $router->get('/products/create', 'App\Domain\Admin\ProductController@create');
    $router->post('/products/store', 'App\Domain\Admin\ProductController@store');
    $router->get('/products/{id}/edit', 'App\Domain\Admin\ProductController@edit');
    $router->post('/products/{id}/update', 'App\Domain\Admin\ProductController@update');
    $router->post('/products/{id}/delete', 'App\Domain\Admin\ProductController@destroy');

    // Categories Management
    $router->get('/categories', 'App\Domain\Admin\CategoryController@index');
    $router->get('/categories/create', 'App\Domain\Admin\CategoryController@create');
    $router->post('/categories/store', 'App\Domain\Admin\CategoryController@store');
    $router->get('/categories/{id}/edit', 'App\Domain\Admin\CategoryController@edit');
    $router->post('/categories/{id}/update', 'App\Domain\Admin\CategoryController@update');
    $router->post('/categories/{id}/delete', 'App\Domain\Admin\CategoryController@destroy');

    // Orders Management
    $router->get('/orders', 'App\Domain\Admin\OrderController@index');
    $router->get('/orders/{id}', 'App\Domain\Admin\OrderController@show');
    $router->post('/orders/{id}/status', 'App\Domain\Admin\OrderController@updateStatus');

    // Users Management
    $router->get('/users', 'App\Domain\Admin\UserController@index');
    $router->get('/users/{id}', 'App\Domain\Admin\UserController@show');
    $router->post('/users/{id}/status', 'App\Domain\Admin\UserController@updateStatus');

    // Inventory
    $router->get('/inventory', 'App\Domain\Admin\InventoryController@index');

    // Settings routes
    $router->get('/settings', 'App\Domain\Admin\SettingsController@index');
    $router->post('/settings/update', 'App\Domain\Admin\SettingsController@update');

    // ERP routes
    $router->get('/erp/dashboard', 'App\Domain\Admin\ERPController@dashboard');
    $router->get('/erp/config', 'App\Domain\Admin\ERPController@config');
    $router->post('/erp/config/save', 'App\Domain\Admin\ERPController@saveConfig');
    $router->post('/erp/sync-products', 'App\Domain\Admin\ERPController@syncProducts');
    $router->post('/erp/sync-inventory', 'App\Domain\Admin\ERPController@syncInventory');
    $router->get('/erp/test-connection', 'App\Domain\Admin\ERPController@testConnection');
});

// Run the application
$app->run();
