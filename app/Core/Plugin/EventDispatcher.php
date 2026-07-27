<?php

namespace App\Core\Plugin;

/**
 * Event Dispatcher
 * Sistema de eventos para hooks y plugins
 */
class EventDispatcher
{
    /**
     * Registered event listeners
     */
    private array $listeners = [];

    /**
     * Register an event listener
     */
    public function listen(string $event, callable $callback, int $priority = 10): void
    {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        $this->listeners[$event][] = [
            'callback' => $callback,
            'priority' => $priority
        ];

        // Sort by priority (higher first)
        usort($this->listeners[$event], function ($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });
    }

    /**
     * Dispatch an event
     */
    public function dispatch(string $event, $payload = null)
    {
        if (!isset($this->listeners[$event])) {
            return $payload;
        }

        foreach ($this->listeners[$event] as $listener) {
            $payload = call_user_func($listener['callback'], $payload);
        }

        return $payload;
    }

    /**
     * Check if an event has listeners
     */
    public function hasListeners(string $event): bool
    {
        return isset($this->listeners[$event]) && count($this->listeners[$event]) > 0;
    }

    /**
     * Get all listeners for an event
     */
    public function getListeners(string $event): array
    {
        return $this->listeners[$event] ?? [];
    }

    /**
     * Remove all listeners for an event
     */
    public function forget(string $event): void
    {
        unset($this->listeners[$event]);
    }

    /**
     * Remove all listeners
     */
    public function flush(): void
    {
        $this->listeners = [];
    }
}
