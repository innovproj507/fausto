<?php

namespace App\Api\V1;

use App\Core\Request;
use App\Core\Response;

/**
 * Shared helpers for JSON API controllers: reading the JWT-authenticated
 * user (set by App\Core\Security\JwtAuth) and reading JSON/form input.
 */
abstract class ApiController
{
    protected function currentUser(): array
    {
        $container = app()->getContainer();
        return $container->has('api.current_user') ? $container->make('api.current_user') : [];
    }

    protected function currentUserId(): ?int
    {
        return $this->currentUser()['id'] ?? null;
    }

    protected function input(Request $request): array
    {
        if ($request->isJson()) {
            return $request->json() ?? [];
        }

        return $request->all();
    }

    protected function error(string $message, int $status = 400): Response
    {
        return Response::json(['error' => true, 'message' => $message], $status);
    }
}
