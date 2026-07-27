<?php

namespace App\Domain\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database\Connection;

class CategoryController
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function index(Request $request): Response
    {
        $categories = $this->db->fetchAll(
            'SELECT * FROM categories ORDER BY sort_order ASC, name ASC'
        );

        return Response::view('admin.categories.index', [
            'categories' => $categories
        ]);
    }

    public function create(Request $request): Response
    {
        return Response::view('admin.categories.create');
    }

    public function store(Request $request): Response
    {
        $data = $request->only(['name', 'description', 'icon_class', 'status']);
        
        // Validate
        $errors = [];
        if (empty($data['name'])) {
            $errors[] = 'El nombre es requerido';
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            return Response::redirect('/manager/categories/create');
        }

        // Generate slug
        $data['slug'] = $this->generateUniqueSlug($data['name']);
        $data['status'] = $data['status'] ?? 'active';

        $this->db->insert('categories', $data);

        $_SESSION['success'] = 'Categoría creada exitosamente';
        return Response::redirect('/manager/categories');
    }

    public function edit(Request $request, $id): Response
    {
        $category = $this->db->fetchOne('SELECT * FROM categories WHERE id = ?', [$id]);

        if (!$category) {
            $_SESSION['error'] = 'Categoría no encontrada';
            return Response::redirect('/manager/categories');
        }

        return Response::view('admin.categories.edit', ['category' => $category]);
    }

    public function update(Request $request, $id): Response
    {
        $category = $this->db->fetchOne('SELECT * FROM categories WHERE id = ?', [$id]);
        
        if (!$category) {
            $_SESSION['error'] = 'Categoría no encontrada';
            return Response::redirect('/manager/categories');
        }

        $data = $request->only(['name', 'description', 'icon_class', 'status']);

        // Update slug if name changed
        if ($data['name'] !== $category['name']) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $id);
        }

        $this->db->update('categories', $data, 'id = ?', [$id]);

        $_SESSION['success'] = 'Categoría actualizada exitosamente';
        return Response::redirect('/manager/categories');
    }

    public function destroy(Request $request, $id): Response
    {
        // Check if category has products
        $count = $this->db->fetchOne(
            'SELECT COUNT(*) as count FROM products WHERE category_id = ?',
            [$id]
        )['count'];

        if ($count > 0) {
            $_SESSION['error'] = 'No se puede eliminar una categoría con productos';
            return Response::redirect('/manager/categories');
        }

        $this->db->delete('categories', 'id = ?', [$id]);

        $_SESSION['success'] = 'Categoría eliminada exitosamente';
        return Response::redirect('/manager/categories');
    }

    private function generateUniqueSlug(string $name, $excludeId = null): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $existing = $this->db->fetchOne(
                'SELECT id FROM categories WHERE slug = ?' . ($excludeId ? ' AND id != ?' : ''),
                $excludeId ? [$slug, $excludeId] : [$slug]
            );

            if (!$existing) break;

            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
