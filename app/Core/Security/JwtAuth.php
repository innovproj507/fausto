<?php

namespace App\Core\Security;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * JWT Authentication Middleware
 * Verifica el Bearer token de la API y expone el usuario autenticado
 * al resto de la request via el Container (clave 'api.current_user').
 */
class JwtAuth
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function handle(Request $request): ?Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return Response::json([
                'error' => 'Unauthenticated',
                'message' => 'Missing Authorization: Bearer <token> header'
            ], 401);
        }

        try {
            $decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));
        } catch (\Throwable $e) {
            return Response::json([
                'error' => 'Unauthenticated',
                'message' => 'Invalid or expired token'
            ], 401);
        }

        if (($decoded->type ?? null) !== 'access') {
            return Response::json([
                'error' => 'Unauthenticated',
                'message' => 'Token is not an access token'
            ], 401);
        }

        $this->container->instance('api.current_user', [
            'id' => $decoded->sub,
            'email' => $decoded->email ?? null,
            'role_id' => $decoded->role_id ?? null,
        ]);

        return null; // Continue
    }
}
