<?php

namespace App\Domain\Product;

use App\Core\Database\Connection;

/**
 * Product Repository
 * Maneja el acceso a datos de productos
 */
class ProductRepository
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Find product by ID
     */
    public function find(int $id): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM products WHERE id = ? AND status = "active"',
            [$id]
        );
    }

    /**
     * Find product by slug
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->db->fetchOne(
            'SELECT p.*, c.name as category_name
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.slug = ? AND p.status = "active"',
            [$slug]
        );
    }

    /**
     * Get all products with pagination
     */
    public function paginate(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        $where = ['p.status = "active"'];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[] = 'p.category_id = ?';
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)';
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereClause = implode(' AND ', $where);

        // Get total count
        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM products p WHERE {$whereClause}",
            $params
        )['count'];

        // Get products
        $products = $this->db->fetchAll(
            "SELECT p.*, c.name as category_name
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE {$whereClause}
             ORDER BY p.created_at DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        return [
            'data' => $products,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => ceil($total / $perPage)
        ];
    }

    /**
     * Get active categories (for filters/navigation)
     */
    public function getCategories(): array
    {
        return $this->db->fetchAll(
            'SELECT id, name, slug, icon_class FROM categories WHERE status = "active" ORDER BY name'
        );
    }

    /**
     * Find an active category by slug
     */
    public function findCategoryBySlug(string $slug): ?array
    {
        return $this->db->fetchOne(
            'SELECT id, name, slug, description FROM categories WHERE slug = ? AND status = "active"',
            [$slug]
        );
    }

    /**
     * Get featured products
     */
    public function getFeatured(int $limit = 10): array
    {
        return $this->db->fetchAll(
            'SELECT p.*, c.name as category_name
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.status = "active" AND p.is_featured = 1
             ORDER BY p.sort_order DESC, p.created_at DESC
             LIMIT ?',
            [$limit]
        );
    }

    /**
     * Create a product
     */
    public function create(array $data): int
    {
        return $this->db->insert('products', $data);
    }

    /**
     * Update a product
     */
    public function update(int $id, array $data): bool
    {
        $updated = $this->db->update('products', $data, 'id = ?', [$id]);
        return $updated > 0;
    }

    /**
     * Delete a product (soft delete)
     */
    public function delete(int $id): bool
    {
        return $this->update($id, ['status' => 'inactive']);
    }

    /**
     * Get product with inventory
     */
    public function getWithInventory(int $id): ?array
    {
        return $this->db->fetchOne(
            'SELECT p.*, 
                    COALESCE(SUM(i.available_quantity), 0) as stock
             FROM products p
             LEFT JOIN inventory i ON p.id = i.product_id
             WHERE p.id = ?
             GROUP BY p.id',
            [$id]
        );
    }
}
