<?php

namespace App\Domain\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database\Connection;

/**
 * Dashboard Controller
 */
class DashboardController
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function index(Request $request): Response
    {
        // Get statistics
        $stats = [
            'total_products' => $this->db->fetchOne('SELECT COUNT(*) as count FROM products')['count'] ?? 0,
            'active_products' => $this->db->fetchOne('SELECT COUNT(*) as count FROM products WHERE status = "active"')['count'] ?? 0,
            'total_orders' => $this->db->fetchOne('SELECT COUNT(*) as count FROM orders')['count'] ?? 0,
            'total_users' => $this->db->fetchOne('SELECT COUNT(*) as count FROM users')['count'] ?? 0,
        ];

        // Get recent products
        $recent_products = $this->db->fetchAll(
            'SELECT p.*, c.name as category_name 
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             ORDER BY p.created_at DESC
             LIMIT 10'
        );

        return Response::view('admin.dashboard', [
            'stats' => $stats,
            'recent_products' => $recent_products
        ]);
    }
}
