<?php

namespace App\Domain\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database\Connection;

class OrderController
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function index(Request $request): Response
    {
        $status = $request->get('status', '');

        $where = $status ? 'WHERE o.status = ?' : '';
        $params = $status ? [$status] : [];

        $orders = $this->db->fetchAll(
            "SELECT o.*, u.first_name, u.last_name, u.email 
             FROM orders o
             LEFT JOIN users u ON o.user_id = u.id
             $where
             ORDER BY o.created_at DESC",
            $params
        );

        return Response::view('admin.orders.index', [
            'orders' => $orders,
            'status' => $status
        ]);
    }

    public function show(Request $request, $id): Response
    {
        $order = $this->db->fetchOne(
            'SELECT o.*, u.first_name, u.last_name, u.email, u.phone 
             FROM orders o
             LEFT JOIN users u ON o.user_id = u.id
             WHERE o.id = ?',
            [$id]
        );

        if (!$order) {
            $_SESSION['error'] = 'Pedido no encontrado';
            return Response::redirect('/manager/orders');
        }

        $items = $this->db->fetchAll(
            'SELECT oi.*, p.name as product_name, p.sku 
             FROM order_items oi
             LEFT JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?',
            [$id]
        );

        return Response::view('admin.orders.show', [
            'order' => $order,
            'items' => $items
        ]);
    }

    public function updateStatus(Request $request, $id): Response
    {
        $status = $request->input('status');
        
        $this->db->update('orders', ['status' => $status], 'id = ?', [$id]);

        $_SESSION['success'] = 'Estado del pedido actualizado';
        return Response::redirect('/manager/orders/' . $id);
    }
}
