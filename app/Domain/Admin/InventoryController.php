<?php

namespace App\Domain\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database\Connection;

class InventoryController
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function index(Request $request): Response
    {
        $products = $this->db->fetchAll(
            'SELECT p.*, c.name as category_name 
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.status = "active"
             ORDER BY p.stock ASC, p.name ASC'
        );

        return Response::view('admin.inventory.index', [
            'products' => $products
        ]);
    }
}
