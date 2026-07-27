<?php

namespace App\Core;

/**
 * HTTP Router
 * Sistema de enrutamiento con soporte para grupos, middleware y parámetros
 */
class Router
{
    private array $routes = [];
    private array $groupStack = [];
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Add a GET route
     */
    public function get(string $uri, $action): void
    {
        $this->addRoute('GET', $uri, $action);
    }

    /**
     * Add a POST route
     */
    public function post(string $uri, $action): void
    {
        $this->addRoute('POST', $uri, $action);
    }

    /**
     * Add a PUT route
     */
    public function put(string $uri, $action): void
    {
        $this->addRoute('PUT', $uri, $action);
    }

    /**
     * Add a DELETE route
     */
    public function delete(string $uri, $action): void
    {
        $this->addRoute('DELETE', $uri, $action);
    }

    /**
     * Add a route for any method
     */
    public function any(string $uri, $action): void
    {
        foreach (['GET', 'POST', 'PUT', 'DELETE', 'PATCH'] as $method) {
            $this->addRoute($method, $uri, $action);
        }
    }

    /**
     * Create a route group
     */
    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    /**
     * Add a route to the collection
     */
    private function addRoute(string $method, string $uri, $action): void
    {
        $uri = $this->prefix($uri);
        $middleware = $this->gatherMiddleware();
        
        $this->routes[$method][$uri] = [
            'action' => $action,
            'middleware' => $middleware
        ];
    }

    /**
     * Get prefix from group stack
     */
    private function prefix(string $uri): string
    {
        $prefix = '';
        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
        }
        
        return '/' . trim($prefix . '/' . trim($uri, '/'), '/');
    }

    /**
     * Gather middleware from group stack
     */
    private function gatherMiddleware(): array
    {
        $middleware = [];
        foreach ($this->groupStack as $group) {
            if (isset($group['middleware'])) {
                $middleware = array_merge(
                    $middleware, 
                    (array) $group['middleware']
                );
            }
        }
        return $middleware;
    }

    /**
     * Dispatch the request to a route
     */
    public function dispatch(Request $request): Response
    {
        $method = $request->getMethod();
        $uri = $request->getUri();

        // Find matching route
        $route = $this->findRoute($method, $uri);

        if ($route === null) {
            return new Response('404 Not Found', 404);
        }

        // Execute middleware
        foreach ($route['middleware'] as $middlewareClass) {
            $middleware = $this->container->make($middlewareClass);
            $response = $middleware->handle($request);
            
            if ($response !== null) {
                return $response;
            }
        }

        // Execute action
        return $this->executeAction($route['action'], $route['params'] ?? []);
    }

    /**
     * Find a matching route
     */
    private function findRoute(string $method, string $uri): ?array
    {
        if (!isset($this->routes[$method])) {
            return null;
        }

        foreach ($this->routes[$method] as $routeUri => $route) {
            $pattern = $this->convertToRegex($routeUri);
            
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match
                $route['params'] = $matches;
                return $route;
            }
        }

        return null;
    }

    /**
     * Convert route URI to regex pattern
     */
    private function convertToRegex(string $uri): string
    {
        // Convert {id} to named capture groups
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $uri);
        return '#^' . $pattern . '$#';
    }

    /**
     * Execute the route action
     */
    private function executeAction($action, array $params = []): Response
    {
        if (is_callable($action)) {
            $result = $this->container->call($action, $params);
        } elseif (is_string($action)) {
            // Parse "Controller@method" syntax
            [$controller, $method] = explode('@', $action);
            $controllerInstance = $this->container->make($controller);
            $result = $this->container->call([$controllerInstance, $method], $params);
        } else {
            throw new \Exception('Invalid route action');
        }

        // Convert to Response if needed
        if (!$result instanceof Response) {
            $result = new Response($result);
        }

        return $result;
    }

    /**
     * Get all registered routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
