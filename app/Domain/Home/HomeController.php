<?php

namespace App\Domain\Home;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database\Connection;

class HomeController
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function index(Request $request): Response
    {
        // Get active categories for the browse row
        $categories = $this->db->fetchAll(
            'SELECT id, name, slug, icon_class
             FROM categories
             WHERE status = "active"
             ORDER BY sort_order ASC, name ASC'
        );

        // Get featured products (active, with a real image; is_featured products
        // ranked first, but not required - most currently have no image assigned)
        $featured_products = $this->db->fetchAll(
            'SELECT p.*, c.name as category_name
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.status = "active" AND p.featured_image IS NOT NULL AND p.featured_image <> ""
             ORDER BY p.is_featured DESC, p.created_at DESC
             LIMIT 8'
        );

        return Response::view('frontend.home', [
            'categories' => $categories,
            'featured_products' => $featured_products
        ]);
    }
}
