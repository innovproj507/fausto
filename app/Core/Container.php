<?php

namespace App\Core;

use Closure;

/**
 * Dependency Injection Container
 * Gestiona la resolución de dependencias y singletons
 */
class Container
{
    /**
     * Registered bindings
     */
    private array $bindings = [];

    /**
     * Singleton instances
     */
    private array $instances = [];

    /**
     * Bind a service
     */
    public function bind(string $abstract, Closure|string $concrete = null, bool $shared = false): void
    {
        if ($concrete === null) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'shared' => $shared
        ];
    }

    /**
     * Bind a singleton
     */
    public function singleton(string $abstract, Closure|string $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Register an existing instance as singleton
     */
    public function instance(string $abstract, $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    /**
     * Resolve a service from the container
     */
    public function make(string $abstract, array $parameters = [])
    {
        // Check if we have a singleton instance
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Get the concrete implementation
        $concrete = $this->getConcrete($abstract);

        // Build the instance
        if ($concrete instanceof Closure) {
            $object = $concrete($this, $parameters);
        } else {
            $object = $this->build($concrete, $parameters);
        }

        // Store singleton if needed
        if (isset($this->bindings[$abstract]) && $this->bindings[$abstract]['shared']) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * Get the concrete implementation
     */
    private function getConcrete(string $abstract)
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]['concrete'];
        }

        return $abstract;
    }

    /**
     * Build an instance of the given class
     */
    private function build(string $concrete, array $parameters = [])
    {
        try {
            $reflector = new \ReflectionClass($concrete);
        } catch (\ReflectionException $e) {
            throw new \Exception("Target class [{$concrete}] does not exist.");
        }

        if (!$reflector->isInstantiable()) {
            throw new \Exception("Target [{$concrete}] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return new $concrete;
        }

        $dependencies = $this->resolveDependencies(
            $constructor->getParameters(),
            $parameters
        );

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Resolve constructor dependencies
     */
    private function resolveDependencies(array $dependencies, array $parameters = []): array
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            // Si hay un parámetro específico proporcionado
            if (isset($parameters[$dependency->getName()])) {
                $results[] = $parameters[$dependency->getName()];
                continue;
            }

            // Si tiene un type hint de clase
            $type = $dependency->getType();
            if ($type && !$type->isBuiltin()) {
                $results[] = $this->make($type->getName());
                continue;
            }

            // Si tiene un valor por defecto
            if ($dependency->isDefaultValueAvailable()) {
                $results[] = $dependency->getDefaultValue();
                continue;
            }

            throw new \Exception("Cannot resolve dependency [{$dependency->getName()}]");
        }

        return $results;
    }

    /**
     * Call a method with dependency injection
     */
    public function call(callable $callback, array $parameters = [])
    {
        if (is_array($callback)) {
            $reflector = new \ReflectionMethod($callback[0], $callback[1]);
        } else {
            $reflector = new \ReflectionFunction($callback);
        }

        $dependencies = $this->resolveDependencies(
            $reflector->getParameters(),
            $parameters
        );

        return call_user_func_array($callback, $dependencies);
    }

    /**
     * Check if a binding exists
     */
    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }
}
