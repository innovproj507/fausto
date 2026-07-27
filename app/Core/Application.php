<?php

namespace App\Core;

use App\Core\Database\Connection;
use App\Core\Plugin\PluginManager;
use App\Core\Plugin\EventDispatcher;
use Dotenv\Dotenv;

/**
 * Main Application Class
 * Bootstrap y gestión del ciclo de vida de la aplicación
 */
class Application
{
    private Container $container;
    private ?Router $router = null;
    private ?PluginManager $pluginManager = null;
    private array $config = [];

    public function __construct(string $basePath)
    {
        $this->loadEnvironment($basePath);
        $this->container = new Container();
        $this->registerCoreServices();
        $this->loadConfiguration($basePath);
    }

    /**
     * Load environment variables
     */
    private function loadEnvironment(string $basePath): void
    {
        if (file_exists($basePath . '/.env')) {
            $dotenv = Dotenv::createImmutable($basePath);
            $dotenv->load();
        }
    }

    /**
     * Load configuration files
     */
    private function loadConfiguration(string $basePath): void
    {
        $configPath = $basePath . '/config';
        
        if (is_dir($configPath)) {
            foreach (glob($configPath . '/*.php') as $file) {
                $key = basename($file, '.php');
                $this->config[$key] = require $file;
            }
        }
    }

    /**
     * Register core services in the container
     */
    private function registerCoreServices(): void
    {
        // Singleton: Database Connection
        $this->container->singleton(Connection::class, function ($container) {
            return Connection::getInstance([
                'host' => $_ENV['DB_HOST'] ?? 'localhost',
                'port' => $_ENV['DB_PORT'] ?? 3306,
                'database' => $_ENV['DB_DATABASE'] ?? 'ecommerce',
                'username' => $_ENV['DB_USERNAME'] ?? 'root',
                'password' => $_ENV['DB_PASSWORD'] ?? '',
                'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4'
            ]);
        });

        // Singleton: Event Dispatcher
        $this->container->singleton(EventDispatcher::class, function () {
            return new EventDispatcher();
        });

        // Singleton: Plugin Manager
        $this->container->singleton(PluginManager::class, function ($container) {
            return new PluginManager(
                $container,
                $container->make(Connection::class),
                $container->make(EventDispatcher::class),
                $_ENV['PLUGINS_PATH'] ?? 'plugins'
            );
        });

        // Router
        $this->container->singleton(Router::class, function ($container) {
            return new Router($container);
        });

        // Request
        $this->container->bind(Request::class, function () {
            return Request::capture();
        });

        // Store container instance
        $this->container->instance(Container::class, $this->container);
    }

    /**
     * Bootstrap the application
     */
    public function bootstrap(): self
    {
        // Load plugins if enabled
        if (($_ENV['PLUGINS_ENABLED'] ?? true)) {
            $this->pluginManager = $this->container->make(PluginManager::class);
            $this->pluginManager->loadActivePlugins();
        }

        // Set error handling
        $this->setupErrorHandling();

        // Start session
        $this->startSession();

        return $this;
    }

    /**
     * Setup error handling
     */
    private function setupErrorHandling(): void
    {
        $debug = $_ENV['APP_DEBUG'] ?? false;

        if ($debug) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }

        set_exception_handler(function ($exception) use ($debug) {
            if ($debug) {
                echo "<h1>Error</h1>";
                echo "<pre>" . $exception->getMessage() . "</pre>";
                echo "<pre>" . $exception->getTraceAsString() . "</pre>";
            } else {
                echo "An error occurred. Please try again later.";
            }
        });
    }

    /**
     * Start session
     */
    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Get the router
     */
    public function getRouter(): Router
    {
        if (!$this->router) {
            $this->router = $this->container->make(Router::class);
        }
        return $this->router;
    }

    /**
     * Run the application
     */
    public function run(): void
    {
        $request = $this->container->make(Request::class);
        $response = $this->router->dispatch($request);
        $response->send();
    }

    /**
     * Get the container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Get configuration value
     */
    public function config(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Get plugin manager
     */
    public function getPluginManager(): ?PluginManager
    {
        return $this->pluginManager;
    }

    /**
     * Dispatch an event
     */
    public function event(string $event, $payload = null)
    {
        $dispatcher = $this->container->make(EventDispatcher::class);
        return $dispatcher->dispatch($event, $payload);
    }
}
