<?php

/**
 * Helper Functions
 * Funciones globales útiles para todo el sistema
 */

if (!function_exists('env')) {
    /**
     * Get environment variable
     */
    function env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        // Parse boolean values
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }
        
        return $value;
    }
}

if (!function_exists('config')) {
    /**
     * Get configuration value
     */
    function config(string $key, $default = null)
    {
        global $app;
        return $app ? $app->config($key, $default) : $default;
    }
}

if (!function_exists('url')) {
    /**
     * Generate URL
     */
    function url(string $path = ''): string
    {
        $baseUrl = rtrim(env('APP_URL', 'http://localhost'), '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /**
     * Generate asset URL
     */
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('redirect')) {
    /**
     * Create redirect response
     */
    function redirect(string $url)
    {
        return \App\Core\Response::redirect($url);
    }
}

if (!function_exists('view')) {
    /**
     * Render a view
     */
    function view(string $view, array $data = [])
    {
        return \App\Core\Response::view($view, $data);
    }
}

if (!function_exists('json')) {
    /**
     * Create JSON response
     */
    function json($data, int $status = 200)
    {
        return \App\Core\Response::json($data, $status);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Generate CSRF token
     */
    function csrf_token(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate CSRF hidden input field
     */
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('old')) {
    /**
     * Get old input value
     */
    function old(string $key, $default = '')
    {
        return $_SESSION['old_input'][$key] ?? $default;
    }
}

if (!function_exists('session')) {
    /**
     * Get/set session value
     */
    function session(string $key = null, $value = null)
    {
        if ($key === null) {
            return $_SESSION;
        }
        
        if ($value === null) {
            return $_SESSION[$key] ?? null;
        }
        
        $_SESSION[$key] = $value;
        return $value;
    }
}

if (!function_exists('flash')) {
    /**
     * Set flash message
     */
    function flash(string $key, $message): void
    {
        $_SESSION['flash'][$key] = $message;
    }
}

if (!function_exists('get_flash')) {
    /**
     * Get and clear flash message
     */
    function get_flash(string $key)
    {
        $message = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $message;
    }
}

if (!function_exists('auth')) {
    /**
     * Get authenticated user
     */
    function auth()
    {
        return $_SESSION['user'] ?? null;
    }
}

if (!function_exists('is_logged_in')) {
    /**
     * Check if user is logged in
     */
    function is_logged_in(): bool
    {
        return isset($_SESSION['user']);
    }
}

if (!function_exists('has_permission')) {
    /**
     * Check if user has permission
     */
    function has_permission(string $permission): bool
    {
        $user = auth();
        if (!$user) {
            return false;
        }
        
        // Check permissions from user's role
        // This would query the database in real implementation
        return true; // Placeholder
    }
}

if (!function_exists('money_format')) {
    /**
     * Format money
     */
    function money_format(float $amount, string $currency = 'USD'): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'MXN' => '$',
            'COP' => '$'
        ];
        
        $symbol = $symbols[$currency] ?? '$';
        return $symbol . number_format($amount, 2);
    }
}

if (!function_exists('str_slug')) {
    /**
     * Generate URL slug
     */
    function str_slug(string $string): string
    {
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9-]/', '-', $string);
        $string = preg_replace('/-+/', '-', $string);
        return trim($string, '-');
    }
}

if (!function_exists('truncate')) {
    /**
     * Truncate string
     */
    function truncate(string $string, int $length = 100, string $append = '...'): string
    {
        if (mb_strlen($string) <= $length) {
            return $string;
        }
        
        return mb_substr($string, 0, $length) . $append;
    }
}

if (!function_exists('sanitize')) {
    /**
     * Sanitize string for output (XSS protection)
     */
    function sanitize(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die (debug)
     */
    function dd(...$vars): void
    {
        echo '<pre>';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
        die();
    }
}

if (!function_exists('app')) {
    /**
     * Get application instance
     */
    function app()
    {
        global $app;
        return $app;
    }
}

if (!function_exists('setting')) {
    /**
     * Read a value from the `settings` table (cached for the request)
     */
    function setting(string $key, $default = null)
    {
        static $cache = null;

        if ($cache === null) {
            $cache = [];
            try {
                $db = app()?->getContainer()->make(\App\Core\Database\Connection::class);
                if ($db) {
                    foreach ($db->fetchAll('SELECT `key`, `value`, `type` FROM settings') as $row) {
                        $cache[$row['key']] = $row['type'] === 'boolean'
                            ? filter_var($row['value'], FILTER_VALIDATE_BOOLEAN)
                            : $row['value'];
                    }
                }
            } catch (\Throwable $e) {
                // settings table not reachable (e.g. not migrated yet) - fall back to defaults
            }
        }

        return $cache[$key] ?? $default;
    }
}

if (!function_exists('event')) {
    /**
     * Dispatch an event
     */
    function event(string $event, $payload = null)
    {
        return app()->event($event, $payload);
    }
}
