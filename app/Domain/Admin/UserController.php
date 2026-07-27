<?php

namespace App\Domain\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database\Connection;

class UserController
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function index(Request $request): Response
    {
        $search = $request->get('search', '');
        $role = $request->get('role', '');

        $where = [];
        $params = [];

        if ($search) {
            $where[] = '(first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($role) {
            $where[] = 'role_id = ?';
            $params[] = $role;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $users = $this->db->fetchAll(
            "SELECT u.*, r.name as role_name 
             FROM users u
             LEFT JOIN roles r ON u.role_id = r.id
             $whereClause
             ORDER BY u.created_at DESC",
            $params
        );

        return Response::view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'role' => $role
        ]);
    }

    public function show(Request $request, $id): Response
    {
        $user = $this->db->fetchOne(
            'SELECT u.*, r.name as role_name 
             FROM users u
             LEFT JOIN roles r ON u.role_id = r.id
             WHERE u.id = ?',
            [$id]
        );

        if (!$user) {
            $_SESSION['error'] = 'Usuario no encontrado';
            return Response::redirect('/manager/users');
        }

        // Get user orders
        $orders = $this->db->fetchAll(
            'SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 10',
            [$id]
        );

        return Response::view('admin.users.show', [
            'user' => $user,
            'orders' => $orders
        ]);
    }

    public function updateStatus(Request $request, $id): Response
    {
        $status = $request->input('status');
        
        $this->db->update('users', ['status' => $status], 'id = ?', [$id]);

        $_SESSION['success'] = 'Estado actualizado exitosamente';
        return Response::redirect('/manager/users');
    }
}
