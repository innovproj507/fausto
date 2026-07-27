<?php

namespace App\Api\V1;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database\Connection;

/**
 * "Customers" are rows in `users`. All endpoints here are admin-only.
 */
class CustomerController extends ApiController
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function index(Request $request): Response
    {
        if (!$this->isAdmin()) {
            return $this->error('Forbidden: admin role required', 403);
        }

        $page = max(1, (int) $request->get('page', 1));
        $perPage = min(100, max(1, (int) $request->get('per_page', 20)));
        $offset = ($page - 1) * $perPage;

        $customers = $this->db->fetchAll(
            "SELECT id, uuid, email, first_name, last_name, phone, status, created_at
             FROM users ORDER BY created_at DESC LIMIT $perPage OFFSET $offset"
        );

        return Response::json(['data' => $customers, 'meta' => ['page' => $page, 'per_page' => $perPage]]);
    }

    public function show(Request $request, $id): Response
    {
        if (!$this->isAdmin()) {
            return $this->error('Forbidden: admin role required', 403);
        }

        $customer = $this->db->fetchOne(
            'SELECT id, uuid, email, first_name, last_name, phone, status, created_at FROM users WHERE id = ?',
            [$id]
        );

        if (!$customer) {
            return $this->error('Customer not found', 404);
        }

        return Response::json(['data' => $customer]);
    }

    public function store(Request $request): Response
    {
        if (!$this->isAdmin()) {
            return $this->error('Forbidden: admin role required', 403);
        }

        $data = $this->input($request);
        foreach (['email', 'password', 'first_name'] as $field) {
            if (empty($data[$field])) {
                return $this->error("Field '{$field}' is required", 422);
            }
        }

        $existing = $this->db->fetchOne('SELECT id FROM users WHERE email = ?', [$data['email']]);
        if ($existing) {
            return $this->error('Email already registered', 422);
        }

        $roleId = $this->db->fetchOne('SELECT id FROM roles WHERE name = "customer"')['id'] ?? 2;

        $userId = $this->db->insert('users', [
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'role_id' => $roleId,
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? '',
            'phone' => $data['phone'] ?? null,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $customer = $this->db->fetchOne(
            'SELECT id, uuid, email, first_name, last_name, phone, status, created_at FROM users WHERE id = ?',
            [$userId]
        );

        return Response::json(['data' => $customer], 201);
    }

    public function update(Request $request, $id): Response
    {
        if (!$this->isAdmin()) {
            return $this->error('Forbidden: admin role required', 403);
        }

        $customer = $this->db->fetchOne('SELECT id FROM users WHERE id = ?', [$id]);
        if (!$customer) {
            return $this->error('Customer not found', 404);
        }

        $data = $this->input($request);
        $allowed = ['first_name', 'last_name', 'phone', 'status'];
        $update = array_intersect_key($data, array_flip($allowed));

        if (isset($data['password']) && $data['password'] !== '') {
            $update['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        if (!empty($update)) {
            $this->db->update('users', $update, 'id = ?', [$id]);
        }

        $customer = $this->db->fetchOne(
            'SELECT id, uuid, email, first_name, last_name, phone, status, created_at FROM users WHERE id = ?',
            [$id]
        );

        return Response::json(['data' => $customer]);
    }

    private function isAdmin(): bool
    {
        return ($this->currentUser()['role_id'] ?? null) == 1;
    }
}
