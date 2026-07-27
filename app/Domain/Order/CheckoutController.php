<?php

namespace App\Domain\Order;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database\Connection;

/**
 * Checkout Controller
 * Convierte el carrito del usuario autenticado en un pedido (orders/order_items)
 */
class CheckoutController
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function index(Request $request): Response
    {
        $cart = $this->getCart();
        $items = $cart ? $this->getCartItems($cart['id']) : [];

        if (empty($items)) {
            flash('error', 'Tu carrito está vacío');
            return redirect('/cart');
        }

        return view('frontend.order.checkout', [
            'items' => $items,
            'subtotal' => $this->calculateSubtotal($items),
            'shipping' => (float) setting('shipping_flat_rate', 0),
        ]);
    }

    public function process(Request $request): Response
    {
        $cart = $this->getCart();
        $items = $cart ? $this->getCartItems($cart['id']) : [];

        if (empty($items)) {
            flash('error', 'Tu carrito está vacío');
            return redirect('/cart');
        }

        $data = $request->only([
            'billing_first_name', 'billing_last_name', 'billing_address_line1', 'billing_address_line2',
            'billing_city', 'billing_state', 'billing_postal_code', 'billing_country', 'billing_phone',
            'shipping_first_name', 'shipping_last_name', 'shipping_address_line1', 'shipping_address_line2',
            'shipping_city', 'shipping_state', 'shipping_postal_code', 'shipping_country', 'shipping_phone',
            'customer_phone', 'customer_notes', 'payment_method',
        ]);
        $shipSameAsBilling = (bool) $request->post('ship_same_as_billing');

        if ($shipSameAsBilling) {
            foreach (['first_name', 'last_name', 'address_line1', 'address_line2', 'city', 'state', 'postal_code', 'country', 'phone'] as $field) {
                $data["shipping_{$field}"] = $data["billing_{$field}"] ?? null;
            }
        }

        $errors = $this->validate($data);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $data;
            return redirect('/checkout');
        }

        $user = $_SESSION['user'];
        $subtotal = $this->calculateSubtotal($items);
        $shippingAmount = (float) setting('shipping_flat_rate', 0);
        $taxAmount = 0.0;
        $discountAmount = 0.0;
        $totalAmount = $subtotal + $shippingAmount + $taxAmount - $discountAmount;
        $paymentMethod = in_array($data['payment_method'], ['cash_on_delivery', 'bank_transfer'], true)
            ? $data['payment_method']
            : 'cash_on_delivery';

        event('order.before_checkout', ['user_id' => $user['id'], 'items' => $items]);

        $this->db->beginTransaction();
        try {
            $orderId = $this->db->insert('orders', [
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $user['id'],
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $paymentMethod,
                'customer_email' => $user['email'],
                'customer_phone' => $data['customer_phone'] ?? '',
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
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shippingAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'customer_notes' => $data['customer_notes'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            foreach ($items as $item) {
                $lineTotal = $item['price'] * $item['quantity'];
                $this->db->insert('order_items', [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'sku' => $item['sku'] ?? '',
                    'product_name' => $item['name'],
                    'variant_name' => null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'tax_rate' => 0,
                    'tax_amount' => 0,
                    'subtotal' => $lineTotal,
                    'total' => $lineTotal,
                ]);
            }

            $this->db->insert('order_status_history', [
                'order_id' => $orderId,
                'old_status' => null,
                'new_status' => 'pending',
                'user_id' => $user['id'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $this->db->delete('cart_items', 'cart_id = ?', [$cart['id']]);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            flash('error', 'No se pudo procesar tu pedido. Intenta de nuevo.');
            return redirect('/checkout');
        }

        event('order.after_payment', ['order_id' => $orderId, 'total' => $totalAmount, 'payment_method' => $paymentMethod]);

        flash('success', '¡Pedido recibido! Tu número de orden es #' . $orderId);
        return redirect('/account/orders/' . $orderId);
    }

    private function validate(array $data): array
    {
        $errors = [];
        $required = [
            'billing_first_name' => 'El nombre de facturación es requerido',
            'billing_last_name' => 'El apellido de facturación es requerido',
            'billing_address_line1' => 'La dirección de facturación es requerida',
            'billing_city' => 'La ciudad de facturación es requerida',
            'billing_postal_code' => 'El código postal de facturación es requerido',
            'billing_country' => 'El país de facturación es requerido',
            'shipping_first_name' => 'El nombre de envío es requerido',
            'shipping_last_name' => 'El apellido de envío es requerido',
            'shipping_address_line1' => 'La dirección de envío es requerida',
            'shipping_city' => 'La ciudad de envío es requerida',
            'shipping_postal_code' => 'El código postal de envío es requerido',
            'shipping_country' => 'El país de envío es requerido',
            'customer_phone' => 'El teléfono de contacto es requerido',
        ];

        foreach ($required as $field => $message) {
            if (empty($data[$field])) {
                $errors[$field] = $message;
            }
        }

        return $errors;
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    }

    private function getCart(): ?array
    {
        $userId = $_SESSION['user']['id'] ?? null;

        return $this->db->fetchOne('SELECT * FROM carts WHERE user_id = ?', [$userId]);
    }

    private function getCartItems(int $cartId): array
    {
        return $this->db->fetchAll(
            'SELECT ci.*, p.name, p.slug, p.sku, p.featured_image
             FROM cart_items ci
             JOIN products p ON ci.product_id = p.id
             WHERE ci.cart_id = ?',
            [$cartId]
        );
    }

    private function calculateSubtotal(array $items): float
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }
}
