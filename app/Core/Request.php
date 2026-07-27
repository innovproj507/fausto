<?php

namespace App\Core;

/**
 * HTTP Request Handler
 * Maneja datos de la petición HTTP
 */
class Request
{
    private array $get;
    private array $post;
    private array $server;
    private array $cookies;
    private array $files;
    private ?array $json = null;

    public function __construct(
        array $get = null,
        array $post = null,
        array $server = null,
        array $cookies = null,
        array $files = null
    ) {
        $this->get = $get ?? $_GET;
        $this->post = $post ?? $_POST;
        $this->server = $server ?? $_SERVER;
        $this->cookies = $cookies ?? $_COOKIE;
        $this->files = $files ?? $_FILES;
    }

    /**
     * Create from globals
     */
    public static function capture(): self
    {
        return new self();
    }

    /**
     * Get HTTP method
     */
    public function getMethod(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Get request URI
     */
    public function getUri(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        
        // Remove query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        
        // Remove .php file name (for direct file access like index.php, api.php)
        $uri = preg_replace('/\/(index|api)\.php/', '', $uri);

        // Strip the "manager" clean-URL prefix (rewritten to manager.php by .htaccess)
        $uri = preg_replace('/^\/manager(\.php)?/', '', $uri);
        
        // If empty, set to root
        if (empty($uri) || $uri === '') {
            $uri = '/';
        }
        
        return $uri;
    }

    /**
     * Get query parameter
     */
    public function get(string $key, $default = null)
    {
        return $this->get[$key] ?? $default;
    }

    /**
     * Get POST parameter
     */
    public function post(string $key, $default = null)
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * Get input from GET or POST
     */
    public function input(string $key, $default = null)
    {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    /**
     * Get all input data
     */
    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    /**
     * Get only specified keys
     */
    public function only(array $keys): array
    {
        $data = [];
        $all = $this->all();
        
        foreach ($keys as $key) {
            if (isset($all[$key])) {
                $data[$key] = $all[$key];
            }
        }
        
        return $data;
    }

    /**
     * Get all except specified keys
     */
    public function except(array $keys): array
    {
        $all = $this->all();
        
        foreach ($keys as $key) {
            unset($all[$key]);
        }
        
        return $all;
    }

    /**
     * Check if request has a key
     */
    public function has(string $key): bool
    {
        return isset($this->get[$key]) || isset($this->post[$key]);
    }

    /**
     * Get JSON payload
     */
    public function json(string $key = null, $default = null)
    {
        if ($this->json === null) {
            $content = file_get_contents('php://input');
            $this->json = json_decode($content, true) ?? [];
        }

        if ($key === null) {
            return $this->json;
        }

        return $this->json[$key] ?? $default;
    }

    /**
     * Check if request is JSON
     */
    public function isJson(): bool
    {
        return str_contains(
            $this->header('Content-Type', ''),
            'application/json'
        );
    }

    /**
     * Check if request is AJAX
     */
    public function isAjax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * Get request header
     */
    public function header(string $key, $default = null): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $this->server[$key] ?? $default;
    }

    /**
     * Get bearer token from Authorization header
     */
    public function bearerToken(): ?string
    {
        $header = $this->header('Authorization');
        
        if ($header && str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }
        
        return null;
    }

    /**
     * Get client IP address
     */
    public function ip(): string
    {
        if (!empty($this->server['HTTP_CLIENT_IP'])) {
            return $this->server['HTTP_CLIENT_IP'];
        } elseif (!empty($this->server['HTTP_X_FORWARDED_FOR'])) {
            return $this->server['HTTP_X_FORWARDED_FOR'];
        }
        
        return $this->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Get user agent
     */
    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * Get uploaded file
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Check if request has file
     */
    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }

    /**
     * Get cookie value
     */
    public function cookie(string $key, $default = null)
    {
        return $this->cookies[$key] ?? $default;
    }

    /**
     * Get server variable
     */
    public function server(string $key, $default = null)
    {
        return $this->server[$key] ?? $default;
    }
}
