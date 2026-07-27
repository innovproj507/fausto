<?php

namespace App\Api\V1;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database\Connection;
use App\Core\Security\LoginThrottle;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * API Authentication: issues/refreshes JWT access tokens.
 * Tokens are stateless (no server-side blacklist table exists yet), so
 * logout is client-side only - see logout() for why.
 */
class AuthController extends ApiController
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function login(Request $request): Response
    {
        $data = $this->input($request);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $ip = $request->ip();

        $throttle = new LoginThrottle($this->db);
        if ($throttle->tooManyAttempts($email, $ip)) {
            return $this->error(
                'Too many failed attempts. Try again in ' . $throttle->minutesUntilRetry($email, $ip) . ' minute(s)',
                429
            );
        }

        if (!$email || !$password) {
            return $this->error('Email and password are required', 422);
        }

        $user = $this->db->fetchOne(
            'SELECT * FROM users WHERE email = ? AND status = "active"',
            [$email]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            $throttle->recordFailure($email, $ip);
            return $this->error('Invalid credentials', 401);
        }

        return Response::json([
            'access_token' => $this->issueToken($user, 'access', (int) env('JWT_EXPIRATION', 3600)),
            'refresh_token' => $this->issueToken($user, 'refresh', (int) env('JWT_REFRESH_EXPIRATION', 604800)),
            'token_type' => 'Bearer',
            'expires_in' => (int) env('JWT_EXPIRATION', 3600),
        ]);
    }

    public function refresh(Request $request): Response
    {
        $data = $this->input($request);
        $refreshToken = $data['refresh_token'] ?? '';

        if (!$refreshToken) {
            return $this->error('refresh_token is required', 422);
        }

        try {
            $decoded = JWT::decode($refreshToken, new Key(env('JWT_SECRET'), 'HS256'));
        } catch (\Throwable $e) {
            return $this->error('Invalid or expired refresh token', 401);
        }

        if (($decoded->type ?? null) !== 'refresh') {
            return $this->error('Token is not a refresh token', 401);
        }

        $user = $this->db->fetchOne(
            'SELECT * FROM users WHERE id = ? AND status = "active"',
            [$decoded->sub]
        );

        if (!$user) {
            return $this->error('User no longer active', 401);
        }

        return Response::json([
            'access_token' => $this->issueToken($user, 'access', (int) env('JWT_EXPIRATION', 3600)),
            'token_type' => 'Bearer',
            'expires_in' => (int) env('JWT_EXPIRATION', 3600),
        ]);
    }

    public function logout(Request $request): Response
    {
        // Access tokens are stateless JWTs with no server-side revocation
        // table, so there's nothing to invalidate here - the client is
        // expected to discard the token. This just acknowledges the intent.
        return Response::json(['message' => 'Logged out. Discard the token client-side.']);
    }

    private function issueToken(array $user, string $type, int $ttl): string
    {
        $now = time();
        $payload = [
            'iss' => env('APP_URL'),
            'iat' => $now,
            'exp' => $now + $ttl,
            'sub' => $user['id'],
            'email' => $user['email'],
            'role_id' => $user['role_id'],
            'type' => $type,
        ];

        return JWT::encode($payload, env('JWT_SECRET'), 'HS256');
    }
}
