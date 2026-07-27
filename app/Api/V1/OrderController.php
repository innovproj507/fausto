<?php

namespace App\Api\V1;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database\Connection;

class OrderController extends ApiController
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

        $isAdmin = ($this->currentUser()['role_id'] ?? null) == 1;
        $where = $isAdmin ? '' : 'WHERE user_id = ?';
        $params = $isAdmin ? [] : [$this->currentUserId()];

        $orders = $this->db->fetchAll(
            "SELECT * FROM orders $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset",
            $params
        );

        return Response::json(['data' => $orders, 'meta' => ['page' => $page, 'per_page' => $perPage]]);
    }

    public function show(Request $request, $id): Response
    {
        $order = $this->db->fetchOne('SELECT * FROM orders WHERE id = ?', [$id]);

        if (!$order) {
            return $this->error('Order not found', 404);
        }

        $isAdmin = ($this->currentUser()['role_id'] ?? null) == 1;
        if (!$isAdmin && $order['user_id'] != $this->currentUserId()) {
            return $this->error('Forbidden', 403);
        }

        $items = $this->db->fetchAll('SELECT * FROM order_items WHERE order_id = ?', [$id]);

        return Response::json(['data' => array_merge($order, ['items' => $items])]);
    }

    /**
     * Create an order directly from a line-item payload (for integrations
     * that don't go through the storefront's session-based cart), e.g.:
     * { "items": [{"product_id": 1, "quantity": 2}], "billing_*": ..., "shipping_*": ... }
     */
    public function store(Request $request): Response
    {
        $data = $this->input($request);
        $userId = $this->currentUserId();

        if (empty($data['items']) || !is_array($data['items'])) {
            return $this->error('items[] is required', 422);
        }

        $required = [
            'billing_first_name', 'billing_last_name', 'billing_address_line1', 'billing_city',
            'billing_postal_code', 'billing_country', 'shipping_first_name', 'shipping_last_name',
            'shipping_address_line1', 'shipping_city', 'shipping_postal_code', 'shipping_country',
            'customer_email', 'customer_phone',
        ];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return $this->error("Field '{$field}' is required", 422);
            }
        }

        $lineItems = [];
        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $product = $this->db->fetchOne('SELECT * FROM products WHERE id = ? AND status = "active"', [$item['product_id'] ?? 0]);
            if (!$product) {
                return $this->error("Product {$item['product_id']} not found", 422);
            }
            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $lineTotal = $product['price'] * $quantity;
            $subtotal += $lineTotal;
            $lineItems[] = [
                'product_id' => $product['id'],
                'sku' => $product['sku'],
                'product_name' => $product['name'],
                'quantity' => $quantity,
                'price' => $product['price'],
                'total' => $lineTotal,
            ];
        }

        $this->db->beginTransaction();
        try {
            $orderId = $this->db->insert('orders', [
                'order_number' => 'ORD-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6)),
                'user_id' => $userId,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $data['payment_method'] ?? 'cash_on_delivery',
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'],
                'billing_first_name' => $data['billing_first_name'],
                'billing_last_name' => $data['billing_last_name'],
                'billing_address_line1' => $data['billing_address_line1'],
                'billing_address_line2' => $data['billing_address_line2'] ?? null,
                'billing_city' => $data['billing_city'],
                'billing_state' => $data['billing_state'] ?? null,
                'billing_postal_code' => $data['billing_postal_code'],
                'billing_country' => $data['billing_country'],
                'billing_phone' => $data['billing_phone'] ?? null,
                'shipping_first_name' => $data['shipping_first_name'],
                'shipping_last_name' => $data['shipping_last_name'],
                'shipping_address_line1' => $data['shipping_address_line1'],
                'shipping_address_line2' => $data['shipping_address_line2'] ?? null,
                'shipping_city' => $data['shipping_city'],
                'shipping_state' => $data['shipping_state'] ?? null,
                'shipping_postal_code' => $data['shipping_postal_code'],
                'shipping_country' => $data['shipping_country'],
                'shipping_phone' => $data['shipping_phone'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                'shipping_amount' => $data['shipping_amount'] ?? 0,
                'discount_amount' => 0,
                'total_amount' => $subtotal + ($data['shipping_amount'] ?? 0),
                'customer_notes' => $data['customer_notes'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            foreach ($lineItems as $line) {
                $this->db->insert('order_items', array_merge($line, [
                    'order_id' => $orderId,
                    'variant_id' => null,
                    'tax_rate' => 0,
                    'tax_amount' => 0,
                    'subtotal' => $line['total'],
                ]));
            }

            $this->db->insert('order_status_history', [
                'order_id' => $orderId,
                'old_status' => null,
                'new_status' => 'pending',
                'user_id' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            return $this->error('Could not create order', 500);
        }

        event('order.after_payment', ['order_id' => $orderId]);

        $order = $this->db->fetchOne('SELECT * FROM orders WHERE id = ?', [$orderId]);
        return Response::json(['data' => $order], 201);
    }

    public function updateStatus(Request $request, $id): Response
    {
        if (($this->currentUser()['role_id'] ?? null) != 1) {
            return $this->error('Forbidden: admin role required', 403);
        }

        $order = $this->db->fetchOne('SELECT * FROM orders WHERE id = ?', [$id]);
        if (!$order) {
            return $this->error('Order not found', 404);
        }

        $data = $this->input($request);
        $newStatus = $data['status'] ?? null;
        $validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];

        if (!in_array($newStatus, $validStatuses, true)) {
            return $this->error('Invalid status', 422);
        }

        $this->db->update('orders', ['status' => $newStatus], 'id = ?', [$id]);
        $this->db->insert('order_status_history', [
            'order_id' => $id,
            'old_status' => $order['status'],
            'new_status' => $newStatus,
            'user_id' => $this->currentUserId(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        event('order.status_changed', ['order_id' => $id, 'old_status' => $order['status'], 'new_status' => $newStatus]);

        $order = $this->db->fetchOne('SELECT * FROM orders WHERE id = ?', [$id]);
        return Response::json(['data' => $order]);
    }
}
