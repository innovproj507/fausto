<?php

namespace App\Domain\User;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database\Connection;

/**
 * Customer account area: profile and order history
 * (routes are gated by App\Core\Security\AuthMiddleware)
 */
class UserController
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function profile(Request $request): Response
    {
        $user = $this->db->fetchOne(
            'SELECT id, email, first_name, last_name, phone, created_at FROM users WHERE id = ?',
            [$_SESSION['user']['id']]
        );

        return view('frontend.user.profile', ['user' => $user]);
    }

    public function orders(Request $request): Response
    {
        $orders = $this->db->fetchAll(
            'SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC',
            [$_SESSION['user']['id']]
        );

        return view('frontend.user.orders', ['orders' => $orders]);
    }

    public function orderDetail(Request $request, $id): Response
    {
        $order = $this->db->fetchOne(
            'SELECT * FROM orders WHERE id = ? AND user_id = ?',
            [$id, $_SESSION['user']['id']]
        );

        if (!$order) {
            flash('error', 'Pedido no encontrado');
            return redirect('/account/orders');
        }

        $items = $this->db->fetchAll(
            'SELECT * FROM order_items WHERE order_id = ?',
            [$id]
        );

        return view('frontend.user.order_detail', [
            'order' => $order,
            'items' => $items,
        ]);
    }
}
