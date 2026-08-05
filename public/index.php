<?php

/**
 * Frontend Entry Point
 * Punto de entrada para el sitio web público
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;
use App\Core\Response;
use App\Core\Security\CsrfProtection;
use App\Core\Security\AuthMiddleware;

// Create and bootstrap application
$app = new Application(dirname(__DIR__));
$app->bootstrap();

// Maintenance mode: block the storefront for regular visitors, but let a
// logged-in admin (same session used by /manager) keep browsing normally.
if (setting('maintenance_mode', false) && !isset($_SESSION['admin_user'])) {
    http_response_code(503);
    header('Retry-After: 3600');
    require __DIR__ . '/../views/frontend/maintenance.php';
    exit;
}

// Get router
$router = $app->getRouter();

// ============================================
// FRONTEND ROUTES
// ============================================

// All routes below get CsrfProtection; the middleware itself is a no-op for
// GET requests, so this only actually checks the token on POST/PUT/DELETE/PATCH.
$router->group(['middleware' => [CsrfProtection::class]], function ($router) {

    // Home page
    $router->get('/', 'App\Domain\Home\HomeController@index');

    // SEO
    $router->get('/sitemap.xml', 'App\Domain\Seo\SitemapController@index');

    // Product routes
    $router->get('/products', 'App\Domain\Product\ProductController@index');
    $router->get('/products/{slug}', 'App\Domain\Product\ProductController@show');

    // Category routes
    $router->get('/category/{slug}', 'App\Domain\Product\ProductController@category');

    // Cart routes
    $router->get('/cart', 'App\Domain\Cart\CartController@index');
    $router->post('/cart/add', 'App\Domain\Cart\CartController@add');
    $router->post('/cart/update', 'App\Domain\Cart\CartController@update');
    $router->post('/cart/remove', 'App\Domain\Cart\CartController@remove');

    // Checkout routes: orders.user_id is NOT NULL, so checkout requires a logged-in customer
    $router->group(['middleware' => [AuthMiddleware::class]], function ($router) {
        $router->get('/checkout', 'App\Domain\Order\CheckoutController@index');
        $router->post('/checkout/process', 'App\Domain\Order\CheckoutController@process');
    });

    // User routes
    $router->group(['prefix' => 'account'], function ($router) {
        $router->get('/login', 'App\Domain\User\AuthController@showLogin');
        $router->post('/login', 'App\Domain\User\AuthController@login');
        $router->post('/logout', 'App\Domain\User\AuthController@logout');
        $router->get('/register', 'App\Domain\User\AuthController@showRegister');
        $router->post('/register', 'App\Domain\User\AuthController@register');

        // Protected routes: require a logged-in customer session
        $router->group(['middleware' => [AuthMiddleware::class]], function ($router) {
            $router->get('/profile', 'App\Domain\User\UserController@profile');
            $router->get('/orders', 'App\Domain\User\UserController@orders');
            $router->get('/orders/{id}', 'App\Domain\User\UserController@orderDetail');
        });
    });

    // Institutional Pages
    $router->get('/guias/{slug}', 'App\Domain\Pages\PagesController@guia');
    $router->get('/sucursales', 'App\Domain\Pages\PagesController@sucursales');
    $router->get('/nosotros', 'App\Domain\Pages\PagesController@nosotros');
    $router->get('/contacto', 'App\Domain\Pages\PagesController@contacto');
    $router->post('/contacto/submit', 'App\Domain\Pages\PagesController@contactoSubmit');
});

// Run the application
$app->run();
