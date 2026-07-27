<?php

namespace App\Core\Security;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database\Connection;

/**
 * API Rate Limiter Middleware
 * Fixed-window limiter backed by the `api_rate_limits` table.
 * Keyed by client IP + endpoint, since the API has no issued api_keys yet.
 */
class RateLimiter
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function handle(Request $request): ?Response
    {
        $limit = (int) env('API_RATE_LIMIT', 1000);
        $window = (int) env('API_RATE_LIMIT_WINDOW', 3600);

        $ip = $request->ip();
        $endpoint = $request->getUri();
        $windowStart = date('Y-m-d H:i:s', intdiv(time(), $window) * $window);

        $this->db->query(
            'INSERT INTO api_rate_limits (api_key, ip_address, endpoint, requests_count, window_start)
             VALUES (?, ?, ?, 1, ?)
             ON DUPLICATE KEY UPDATE requests_count = requests_count + 1',
            [$ip, $ip, $endpoint, $windowStart]
        );

        $current = $this->db->fetchOne(
            'SELECT requests_count FROM api_rate_limits WHERE api_key = ? AND endpoint = ? AND window_start = ?',
            [$ip, $endpoint, $windowStart]
        );

        if ($current && $current['requests_count'] > $limit) {
            $retryAfter = $window - (time() % $window);
            return Response::json([
                'error' => 'Too Many Requests',
                'message' => "Rate limit of {$limit} requests per {$window}s exceeded"
            ], 429)->header('Retry-After', (string) $retryAfter);
        }

        return null; // Continue
    }
}
