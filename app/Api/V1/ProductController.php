<?php

namespace App\Api\V1;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database\Connection;

class ProductController extends ApiController
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->get('page', 1));
        $perPage = min(100, max(1, (int) $request->get('per_page', 20)));
        $offset = ($page - 1) * $perPage;

        $status = $request->get('status');
        $where = $status ? 'WHERE p.status = ?' : '';
        $params = $status ? [$status] : [];

        $total = $this->db->fetchOne("SELECT COUNT(*) as count FROM products p $where", $params)['count'];

        $products = $this->db->fetchAll(
            "SELECT p.*, c.name as category_name
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             $where
             ORDER BY p.created_at DESC
             LIMIT $perPage OFFSET $offset",
            $params
        );

        return Response::json([
            'data' => $products,
            'meta' => ['page' => $page, 'per_page' => $perPage, 'total' => (int) $total],
        ]);
    }

    public function show(Request $request, $id): Response
    {
        $product = $this->db->fetchOne(
            'SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?',
            [$id]
        );

        if (!$product) {
            return $this->error('Product not found', 404);
        }

        return Response::json(['data' => $product]);
    }

    public function store(Request $request): Response
    {
        if (($this->currentUser()['role_id'] ?? null) != 1) {
            return $this->error('Forbidden: admin role required', 403);
        }

        $data = $this->input($request);
        foreach (['sku', 'name', 'price'] as $field) {
            if (empty($data[$field])) {
                return $this->error("Field '{$field}' is required", 422);
            }
        }

        $productId = $this->db->insert('products', [
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'sku' => $data['sku'],
            'category_id' => $data['category_id'] ?? null,
            'name' => $data['name'],
            'slug' => str_slug($data['name']),
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'status' => $data['status'] ?? 'draft',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        event('product.after_create', ['id' => $productId]);

        $product = $this->db->fetchOne('SELECT * FROM products WHERE id = ?', [$productId]);
        return Response::json(['data' => $product], 201);
    }

    public function update(Request $request, $id): Response
    {
        if (($this->currentUser()['role_id'] ?? null) != 1) {
            return $this->error('Forbidden: admin role required', 403);
        }

        $product = $this->db->fetchOne('SELECT * FROM products WHERE id = ?', [$id]);
        if (!$product) {
            return $this->error('Product not found', 404);
        }

        $data = $this->input($request);
        $allowed = ['sku', 'category_id', 'name', 'short_description', 'description', 'price', 'compare_price', 'status', 'is_featured'];
        $update = array_intersect_key($data, array_flip($allowed));

        if (isset($update['name'])) {
            $update['slug'] = str_slug($update['name']);
        }

        if (!empty($update)) {
            $this->db->update('products', $update, 'id = ?', [$id]);
        }

        $product = $this->db->fetchOne('SELECT * FROM products WHERE id = ?', [$id]);
        return Response::json(['data' => $product]);
    }

    public function destroy(Request $request, $id): Response
    {
        if (($this->currentUser()['role_id'] ?? null) != 1) {
            return $this->error('Forbidden: admin role required', 403);
        }

        $product = $this->db->fetchOne('SELECT id FROM products WHERE id = ?', [$id]);
        if (!$product) {
            return $this->error('Product not found', 404);
        }

        $this->db->delete('products', 'id = ?', [$id]);
        return Response::json(['message' => 'Product deleted']);
    }
}
