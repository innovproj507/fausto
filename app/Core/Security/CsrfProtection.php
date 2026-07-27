<?php

namespace App\Core\Security;

use App\Core\Request;
use App\Core\Response;

/**
 * CSRF Protection Middleware
 * Protege contra ataques Cross-Site Request Forgery
 */
class CsrfProtection
{
    /**
     * Handle the request
     */
    public function handle(Request $request): ?Response
    {
        // Solo verificar en métodos que modifican datos
        if (!in_array($request->getMethod(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            return null;
        }

        // Obtener token del request
        $token = $request->input('_token') ?? $request->header('X-CSRF-TOKEN');

        // Verificar token
        if (!$this->tokensMatch($token)) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::json([
                    'error' => 'CSRF token mismatch',
                    'message' => 'Your session has expired. Please refresh and try again.'
                ], 419);
            }

            // Set both flash conventions in use across the codebase (admin views
            // read $_SESSION['error'] directly, frontend views read get_flash('error'))
            $_SESSION['error'] = 'Tu sesión expiró. Por favor intenta de nuevo.';
            flash('error', 'Tu sesión expiró. Por favor intenta de nuevo.');
            return Response::redirect($request->header('Referer') ?? '/')->setStatusCode(303);
        }

        return null; // Continue
    }

    /**
     * Check if tokens match
     */
    private function tokensMatch(?string $token): bool
    {
        $sessionToken = $_SESSION['csrf_token'] ?? null;
        
        if (!$sessionToken || !$token) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    /**
     * Generate new CSRF token
     */
    public static function generateToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
