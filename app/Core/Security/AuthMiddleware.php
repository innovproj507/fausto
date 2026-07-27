<?php

namespace App\Core\Security;

use App\Core\Request;
use App\Core\Response;

/**
 * Authentication Middleware
 * Verifica que el usuario esté autenticado
 */
class AuthMiddleware
{
    /**
     * Handle the request
     */
    public function handle(Request $request): ?Response
    {
        // Verificar si hay sesión de usuario
        if (!isset($_SESSION['user']) || !$_SESSION['user']) {
            // Si es una petición AJAX, devolver JSON
            if ($request->isAjax()) {
                return Response::json([
                    'error' => 'Unauthenticated',
                    'message' => 'Please login to continue'
                ], 401);
            }

            // Guardar URL intentada para redireccionar después del login
            $_SESSION['intended_url'] = $request->getUri();

            // Redireccionar a login
            return Response::redirect('/account/login');
        }

        return null; // Continue
    }
}
