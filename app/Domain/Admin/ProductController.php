<?php

namespace App\Domain\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database\Connection;

class ProductController
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * List all products
     */
    public function index(Request $request): Response
    {
        $search = $request->get('search', '');
        $category = $request->get('category', '');
        $status = $request->get('status', '');
        $page = (int) $request->get('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        // Build query
        $where = [];
        $params = [];

        if ($search) {
            $where[] = '(p.name LIKE ? OR p.sku LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($category) {
            $where[] = 'p.category_id = ?';
            $params[] = $category;
        }

        if ($status) {
            $where[] = 'p.status = ?';
            $params[] = $status;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // Get products
        $products = $this->db->fetchAll(
            "SELECT p.*, c.name as category_name 
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             $whereClause
             ORDER BY p.created_at DESC
             LIMIT $perPage OFFSET $offset",
            $params
        );

        // Get total count
        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM products p $whereClause",
            $params
        )['count'];

        // Get categories for filter
        $categories = $this->db->fetchAll(
            'SELECT id, name FROM categories WHERE status = "active" ORDER BY name'
        );

        return Response::view('admin.products.index', [
            'products' => $products,
            'categories' => $categories,
            'search' => $search,
            'category' => $category,
            'status' => $status,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage)
            ]
        ]);
    }

    /**
     * Show create form
     */
    public function create(Request $request): Response
    {
        $categories = $this->db->fetchAll(
            'SELECT id, name FROM categories WHERE status = "active" ORDER BY name'
        );

        return Response::view('admin.products.create', [
            'categories' => $categories
        ]);
    }

    /**
     * Store new product
     */
    public function store(Request $request): Response
    {
        $data = $request->only([
            'name', 'sku', 'description', 'price', 'compare_price',
            'category_id', 'is_featured', 'status', 'meta_title', 'meta_description', 'stock'
        ]);

        // Validate
        $errors = $this->validateProduct($data);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            return Response::redirect('/manager/products/create');
        }

        // Generate slug
        $data['slug'] = $this->generateUniqueSlug($data['name']);

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $this->uploadImage($request->file('image'));
            if ($image) {
                $data['featured_image'] = $image;
            }
        }

        // Convert checkbox
        $data['is_featured'] = $request->input('is_featured') ? 1 : 0;
        
        // Convert empty strings to NULL for optional numeric fields
        if (empty($data['compare_price'])) $data['compare_price'] = null;
        if (empty($data['stock'])) $data['stock'] = 0;

        // Insert
        $this->db->insert('products', $data);

        $_SESSION['success'] = 'Producto creado exitosamente';
        return Response::redirect('/manager/products');
    }

    /**
     * Show edit form
     */
    public function edit(Request $request, $id): Response
    {
        $product = $this->db->fetchOne(
            'SELECT * FROM products WHERE id = ?',
            [$id]
        );

        if (!$product) {
            $_SESSION['error'] = 'Producto no encontrado';
            return Response::redirect('/manager/products');
        }

        $categories = $this->db->fetchAll(
            'SELECT id, name FROM categories WHERE status = "active" ORDER BY name'
        );

        return Response::view('admin.products.edit', [
            'product' => $product,
            'categories' => $categories
        ]);
    }

    /**
     * Update product
     */
    public function update(Request $request, $id): Response
    {
        $product = $this->db->fetchOne('SELECT * FROM products WHERE id = ?', [$id]);
        
        if (!$product) {
            $_SESSION['error'] = 'Producto no encontrado';
            return Response::redirect('/manager/products');
        }

        $data = $request->only([
            'name', 'sku', 'description', 'price', 'compare_price',
            'category_id', 'is_featured', 'status', 'meta_title', 'meta_description', 'stock'
        ]);

        // Validate
        $errors = $this->validateProduct($data, $id);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            return Response::redirect('/manager/products/' . $id . '/edit');
        }

        // Update slug if name changed
        if ($data['name'] !== $product['name']) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $id);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $this->uploadImage($request->file('image'));
            if ($image) {
                // Delete old image
                if ($product['featured_image']) {
                    @unlink(__DIR__ . '/../../../public/uploads/products/' . $product['featured_image']);
                }
                $data['featured_image'] = $image;
            }
        }

        // Convert checkbox
        $data['is_featured'] = $request->input('is_featured') ? 1 : 0;
        
        // Convert empty strings to NULL for optional numeric fields
        if (empty($data['compare_price'])) $data['compare_price'] = null;
        if (empty($data['stock'])) $data['stock'] = 0;

        // Update
        $this->db->update('products', $data, 'id = ?', [$id]);

        $_SESSION['success'] = 'Producto actualizado exitosamente';
        return Response::redirect('/manager/products');
    }

    /**
     * Delete product
     */
    public function destroy(Request $request, $id): Response
    {
        $product = $this->db->fetchOne('SELECT * FROM products WHERE id = ?', [$id]);
        
        if (!$product) {
            $_SESSION['error'] = 'Producto no encontrado';
            return Response::redirect('/manager/products');
        }

        // Delete image
        if ($product['featured_image']) {
            @unlink(__DIR__ . '/../../../public/uploads/products/' . $product['featured_image']);
        }

        // Delete product
        $this->db->delete('products', 'id = ?', [$id]);

        $_SESSION['success'] = 'Producto eliminado exitosamente';
        return Response::redirect('/manager/products');
    }

    /**
     * Validate product data
     */
    private function validateProduct(array $data, $id = null): array
    {
        $errors = [];

        if (empty($data['name']) || strlen($data['name']) < 3) {
            $errors[] = 'El nombre debe tener al menos 3 caracteres';
        }

        if (empty($data['sku'])) {
            $errors[] = 'El SKU es requerido';
        } else {
            // Check unique SKU
            $existing = $this->db->fetchOne(
                'SELECT id FROM products WHERE sku = ?' . ($id ? ' AND id != ?' : ''),
                $id ? [$data['sku'], $id] : [$data['sku']]
            );
            if ($existing) {
                $errors[] = 'El SKU ya existe';
            }
        }

        if (empty($data['price']) || $data['price'] <= 0) {
            $errors[] = 'El precio debe ser mayor a 0';
        }

        if (empty($data['category_id'])) {
            $errors[] = 'Selecciona una categoría';
        }

        return $errors;
    }

    /**
     * Generate unique slug
     */
    private function generateUniqueSlug(string $name, $excludeId = null): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $existing = $this->db->fetchOne(
                'SELECT id FROM products WHERE slug = ?' . ($excludeId ? ' AND id != ?' : ''),
                $excludeId ? [$slug, $excludeId] : [$slug]
            );

            if (!$existing) {
                break;
            }

            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Upload product image
     */
    private function uploadImage(array $file): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Validate
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }

        if ($file['size'] > 5 * 1024 * 1024) { // 5MB
            return null;
        }

        // Create directory if not exists
        $uploadDir = __DIR__ . '/../../../public/uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = time() . '_' . uniqid() . '.' . $extension;

        // Move file
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            return $filename;
        }

        return null;
    }
}
