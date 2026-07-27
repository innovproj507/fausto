<?php

namespace App\Core;

/**
 * HTTP Response Handler
 * Maneja las respuestas HTTP
 */
class Response
{
    private $content;
    private int $statusCode;
    private array $headers = [];

    public function __construct($content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    /**
     * Set response content
     */
    public function setContent($content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Set status code
     */
    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Add a header
     */
    public function header(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * Send JSON response
     */
    public static function json($data, int $status = 200): self
    {
        $response = new self(json_encode($data), $status);
        $response->header('Content-Type', 'application/json');
        return $response;
    }

    /**
     * Send view response
     */
    public static function view(string $view, array $data = [], int $status = 200): self
    {
        $content = self::renderView($view, $data);
        return new self($content, $status);
    }

    /**
     * Redirect response
     */
    public static function redirect(string $url, int $status = 302): self
    {
        $response = new self('', $status);
        $response->header('Location', $url);
        return $response;
    }

    /**
     * Render a view file
     */
    private static function renderView(string $view, array $data = []): string
    {
        $viewPath = __DIR__ . '/../../views/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View file not found: {$view}");
        }

        extract($data);
        
        ob_start();
        include $viewPath;
        return ob_get_clean();
    }

    /**
     * Send the response
     */
    public function send(): void
    {
        // Send status code
        http_response_code($this->statusCode);

        // Send headers
        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
        }

        // Send content
        if (is_array($this->content) || is_object($this->content)) {
            echo json_encode($this->content);
        } else {
            echo $this->content;
        }
    }

    /**
     * Get the content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get headers
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
