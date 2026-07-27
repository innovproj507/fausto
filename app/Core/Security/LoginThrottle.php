<?php

namespace App\Core\Security;

use App\Core\Database\Connection;

/**
 * Brute-force guard for password login endpoints (storefront, admin panel,
 * API). Backed by the existing `audit_logs` table (no dedicated table for
 * this) - counts recent 'login_failed' rows for either the IP or the
 * attempted identifier (email) within a decay window.
 */
class LoginThrottle
{
    private Connection $db;
    private int $maxAttempts;
    private int $decayMinutes;

    public function __construct(Connection $db, int $maxAttempts = 5, int $decayMinutes = 15)
    {
        $this->db = $db;
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
    }

    public function tooManyAttempts(string $identifier, string $ip): bool
    {
        return $this->attemptCount($identifier, $ip) >= $this->maxAttempts;
    }

    public function recordFailure(string $identifier, string $ip): void
    {
        $this->db->insert('audit_logs', [
            'action' => 'login_failed',
            'entity_type' => 'auth',
            'new_values' => json_encode(['identifier' => $identifier]),
            'ip_address' => $ip,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Minutes until the oldest attempt in the current window ages out.
     * Used to give the user a "try again in N minutes" message.
     */
    public function minutesUntilRetry(string $identifier, string $ip): int
    {
        $row = $this->db->fetchOne(
            "SELECT MIN(created_at) as oldest FROM audit_logs
             WHERE action = 'login_failed'
               AND (ip_address = ? OR new_values->>'$.identifier' = ?)
               AND created_at > (NOW() - INTERVAL {$this->decayMinutes} MINUTE)",
            [$ip, $identifier]
        );

        if (empty($row['oldest'])) {
            return $this->decayMinutes;
        }

        $elapsedMinutes = (time() - strtotime($row['oldest'])) / 60;
        return max(1, (int) ceil($this->decayMinutes - $elapsedMinutes));
    }

    private function attemptCount(string $identifier, string $ip): int
    {
        $row = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM audit_logs
             WHERE action = 'login_failed'
               AND (ip_address = ? OR new_values->>'$.identifier' = ?)
               AND created_at > (NOW() - INTERVAL {$this->decayMinutes} MINUTE)",
            [$ip, $identifier]
        );

        return (int) ($row['count'] ?? 0);
    }
}
