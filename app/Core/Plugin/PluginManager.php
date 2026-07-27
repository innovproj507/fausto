<?php

namespace App\Core\Plugin;

use App\Core\Container;
use App\Core\Database\Connection;

/**
 * Plugin Manager
 * Gestiona descubrimiento, carga y activación de plugins
 */
class PluginManager
{
    private Container $container;
    private Connection $db;
    private EventDispatcher $dispatcher;
    private string $pluginsPath;
    private array $loadedPlugins = [];

    public function __construct(
        Container $container,
        Connection $db,
        EventDispatcher $dispatcher,
        string $pluginsPath = 'plugins'
    ) {
        $this->container = $container;
        $this->db = $db;
        $this->dispatcher = $dispatcher;
        $this->pluginsPath = $pluginsPath;
    }

    /**
     * Discover all available plugins
     */
    public function discover(): array
    {
        $plugins = [];
        $directories = glob($this->pluginsPath . '/*/*', GLOB_ONLYDIR);

        foreach ($directories as $dir) {
            $manifestPath = $dir . '/plugin.json';
            
            if (file_exists($manifestPath)) {
                $manifest = json_decode(file_get_contents($manifestPath), true);
                $plugins[] = $manifest;
            }
        }

        return $plugins;
    }

    /**
     * Load all active plugins
     */
    public function loadActivePlugins(): void
    {
        $activePlugins = $this->db->fetchAll(
            'SELECT * FROM plugins WHERE is_active = 1 ORDER BY name'
        );

        foreach ($activePlugins as $pluginData) {
            $this->loadPlugin($pluginData['name']);
        }
    }

    /**
     * Load a specific plugin
     */
    public function loadPlugin(string $name): bool
    {
        if (isset($this->loadedPlugins[$name])) {
            return true; // Already loaded
        }

        // Get plugin data from database
        $pluginData = $this->db->fetchOne(
            'SELECT * FROM plugins WHERE name = ?',
            [$name]
        );

        if (!$pluginData) {
            return false;
        }

        // Construct plugin class name
        $className = $pluginData['main_class'];

        if (!class_exists($className)) {
            return false;
        }

        // Instantiate plugin
        $plugin = new $className($this->container, $pluginData['config']);

        if (!$plugin instanceof PluginInterface) {
            throw new \Exception("Plugin {$name} must implement PluginInterface");
        }

        // Boot the plugin
        $plugin->boot();

        // Register hooks
        $plugin->registerHooks($this->dispatcher);

        // Store loaded plugin
        $this->loadedPlugins[$name] = $plugin;

        return true;
    }

    /**
     * Install a plugin
     */
    public function install(string $name): bool
    {
        // Check if already installed
        $existing = $this->db->fetchOne(
            'SELECT id FROM plugins WHERE name = ?',
            [$name]
        );

        if ($existing) {
            return false; // Already installed
        }

        // Find plugin manifest
        $manifest = $this->findPluginManifest($name);

        if (!$manifest) {
            return false;
        }

        // Insert into database
        $this->db->insert('plugins', [
            'name' => $manifest['name'],
            'display_name' => $manifest['display_name'],
            'description' => $manifest['description'] ?? null,
            'version' => $manifest['version'],
            'author' => $manifest['author'] ?? null,
            'author_url' => $manifest['author_url'] ?? null,
            'namespace' => $manifest['namespace'],
            'main_class' => $manifest['main_class'],
            'is_installed' => 1,
            'is_active' => 0,
            'config' => json_encode($manifest['config'] ?? []),
            'dependencies' => json_encode($manifest['dependencies'] ?? []),
            'installed_at' => date('Y-m-d H:i:s')
        ]);

        // Run plugin install method
        $className = $manifest['main_class'];
        $plugin = new $className($this->container, $manifest['config'] ?? []);
        
        if ($plugin instanceof PluginInterface) {
            $plugin->install();
        }

        return true;
    }

    /**
     * Activate a plugin
     */
    public function activate(string $name): bool
    {
        $updated = $this->db->update(
            'plugins',
            [
                'is_active' => 1,
                'activated_at' => date('Y-m-d H:i:s')
            ],
            'name = ?',
            [$name]
        );

        if ($updated) {
            $this->loadPlugin($name);
            
            if (isset($this->loadedPlugins[$name])) {
                $this->loadedPlugins[$name]->activate();
            }
        }

        return $updated > 0;
    }

    /**
     * Deactivate a plugin
     */
    public function deactivate(string $name): bool
    {
        if (isset($this->loadedPlugins[$name])) {
            $this->loadedPlugins[$name]->deactivate();
            unset($this->loadedPlugins[$name]);
        }

        $updated = $this->db->update(
            'plugins',
            ['is_active' => 0],
            'name = ?',
            [$name]
        );

        return $updated > 0;
    }

    /**
     * Uninstall a plugin
     */
    public function uninstall(string $name): bool
    {
        // Deactivate first
        $this->deactivate($name);

        // Get plugin data
        $pluginData = $this->db->fetchOne(
            'SELECT * FROM plugins WHERE name = ?',
            [$name]
        );

        if (!$pluginData) {
            return false;
        }

        // Run plugin uninstall method
        $className = $pluginData['main_class'];
        if (class_exists($className)) {
            $plugin = new $className($this->container, json_decode($pluginData['config'], true));
            
            if ($plugin instanceof PluginInterface) {
                $plugin->uninstall();
            }
        }

        // Remove from database
        $deleted = $this->db->delete('plugins', 'name = ?', [$name]);

        return $deleted > 0;
    }

    /**
     * Find plugin manifest file
     */
    private function findPluginManifest(string $name): ?array
    {
        $directories = glob($this->pluginsPath . '/*/*', GLOB_ONLYDIR);

        foreach ($directories as $dir) {
            $manifestPath = $dir . '/plugin.json';
            
            if (file_exists($manifestPath)) {
                $manifest = json_decode(file_get_contents($manifestPath), true);
                
                if ($manifest['name'] === $name) {
                    return $manifest;
                }
            }
        }

        return null;
    }

    /**
     * Get all loaded plugins
     */
    public function getLoadedPlugins(): array
    {
        return $this->loadedPlugins;
    }

    /**
     * Get event dispatcher
     */
    public function getDispatcher(): EventDispatcher
    {
        return $this->dispatcher;
    }
}
