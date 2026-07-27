<?php

namespace App\Core\Plugin;

/**
 * Plugin Interface
 * Todos los plugins deben implementar esta interfaz
 */
interface PluginInterface
{
    /**
     * Get plugin name
     */
    public function getName(): string;

    /**
     * Get plugin version
     */
    public function getVersion(): string;

    /**
     * Get plugin description
     */
    public function getDescription(): string;

    /**
     * Bootstrap the plugin
     * Se ejecuta cuando el plugin es cargado
     */
    public function boot(): void;

    /**
     * Register plugin hooks/events
     * Registra los eventos que el plugin escucha
     */
    public function registerHooks(EventDispatcher $dispatcher): void;

    /**
     * Install the plugin
     * Se ejecuta una sola vez al instalar
     */
    public function install(): bool;

    /**
     * Uninstall the plugin
     * Se ejecuta al desinstalar
     */
    public function uninstall(): bool;

    /**
     * Activate the plugin
     */
    public function activate(): void;

    /**
     * Deactivate the plugin
     */
    public function deactivate(): void;

    /**
     * Get plugin configuration
     */
    public function getConfig(): array;
}
