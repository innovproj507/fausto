<?php

/**
 * API Entry Point
 * RESTful API para integraciones externas y ERP
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;
use App\Core\Response;
use App\Core\Security\JwtAuth;
use App\Core\Security\RateLimiter;

// Create and bootstrap application
$app = new Application(dirname(__DIR__));
$app->bootstrap();

// Get router
$router = $app->getRouter();

// Set JSON response headers
header('Content-Type: application/json');

// ============================================
// API ROUTES v1
// ============================================

$router->group(['prefix' => 'api/v1'], function ($router) {
    
    // Authentication
    $router->post('/auth/login', 'App\Api\V1\AuthController@login');
    $router->post('/auth/refresh', 'App\Api\V1\AuthController@refresh');
    $router->post('/auth/logout', 'App\Api\V1\AuthController@logout');
    
    // Protected API routes (require JWT token)
    $router->group(['middleware' => [RateLimiter::class, JwtAuth::class]], function ($router) {
        
        // Products API
        $router->get('/products', 'App\Api\V1\ProductController@index');
        $router->get('/products/{id}', 'App\Api\V1\ProductController@show');
        $router->post('/products', 'App\Api\V1\ProductController@store');
        $router->put('/products/{id}', 'App\Api\V1\ProductController@update');
        $router->delete('/products/{id}', 'App\Api\V1\ProductController@destroy');
        
        // Inventory API
        $router->get('/inventory', 'App\Api\V1\InventoryController@index');
        $router->get('/inventory/{id}', 'App\Api\V1\InventoryController@show');
        $router->put('/inventory/{id}', 'App\Api\V1\InventoryController@update');
        $router->post('/inventory/sync', 'App\Api\V1\InventoryController@sync');
        
        // Orders API
        $router->get('/orders', 'App\Api\V1\OrderController@index');
        $router->get('/orders/{id}', 'App\Api\V1\OrderController@show');
        $router->post('/orders', 'App\Api\V1\OrderController@store');
        $router->put('/orders/{id}/status', 'App\Api\V1\OrderController@updateStatus');
        
        // Customers API
        $router->get('/customers', 'App\Api\V1\CustomerController@index');
        $router->get('/customers/{id}', 'App\Api\V1\CustomerController@show');
        $router->post('/customers', 'App\Api\V1\CustomerController@store');
        $router->put('/customers/{id}', 'App\Api\V1\CustomerController@update');
        
        // Categories API
        $router->get('/categories', 'App\Api\V1\CategoryController@index');
        
        // ERP Sync endpoints
        $router->post('/erp/products/import', 'App\Api\V1\ErpController@importProducts');
        $router->post('/erp/inventory/sync', 'App\Api\V1\ErpController@syncInventory');
        $router->post('/erp/orders/export', 'App\Api\V1\ErpController@exportOrders');
        $router->get('/erp/sync/status', 'App\Api\V1\ErpController@syncStatus');
    });
    
    // Webhooks (no authentication required, but signature verification)
    $router->post('/webhooks/stripe', 'App\Api\V1\WebhookController@stripe');
    $router->post('/webhooks/paypal', 'App\Api\V1\WebhookController@paypal');
    $router->post('/webhooks/erp', 'App\Api\V1\WebhookController@erp');
});

// Run the application
try {
    $app->run();
} catch (\Throwable $e) {
    $response = Response::json([
        'error' => true,
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ], 500);
    $response->send();
}
