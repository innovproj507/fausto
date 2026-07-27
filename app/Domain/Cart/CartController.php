<?php

namespace App\Domain\Cart;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database\Connection;

/**
 * Cart Controller
 * Maneja el carrito de compras
 */
class CartController
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Show cart
     */
    public function index(): Response
    {
        $cart = $this->getOrCreateCart();
        $items = $this->getCartItems($cart['id']);

        return view('frontend.cart.index', [
            'cart' => $cart,
            'items' => $items,
            'total' => $this->calculateTotal($items)
        ]);
    }

    /**
     * Add item to cart
     */
    public function add(Request $request): Response
    {
        $productId = $request->post('product_id');
        $quantity = (int) $request->post('quantity', 1);
        $variantId = $request->post('variant_id');

        if (!$productId || $quantity < 1) {
            return json(['error' => 'Invalid product or quantity'], 400);
        }

        // Get product
        $product = $this->db->fetchOne(
            'SELECT * FROM products WHERE id = ? AND status = "active"',
            [$productId]
        );

        if (!$product) {
            return json(['error' => 'Product not found'], 404);
        }

        // Get or create cart
        $cart = $this->getOrCreateCart();

        // Check if item already in cart
        $existingItem = $this->db->fetchOne(
            'SELECT * FROM cart_items 
             WHERE cart_id = ? AND product_id = ? AND variant_id <=> ?',
            [$cart['id'], $productId, $variantId]
        );

        if ($existingItem) {
            // Update quantity
            $this->db->update('cart_items', [
                'quantity' => $existingItem['quantity'] + $quantity
            ], 'id = ?', [$existingItem['id']]);
        } else {
            // Add new item
            $this->db->insert('cart_items', [
                'cart_id' => $cart['id'],
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity,
                'price' => $product['price']
            ]);
        }

        // Dispatch event
        event('cart.item_added', [
            'product_id' => $productId,
            'quantity' => $quantity
        ]);

        flash('success', 'Product added to cart');

        if ($request->isAjax()) {
            return json([
                'success' => true,
                'message' => 'Product added to cart'
            ]);
        }

        return redirect('/cart');
    }

    /**
     * Update cart item
     */
    public function update(Request $request): Response
    {
        $itemId = $request->post('item_id');
        $quantity = (int) $request->post('quantity');

        if ($quantity < 1) {
            return $this->remove($request);
        }

        $this->db->update('cart_items', [
            'quantity' => $quantity
        ], 'id = ?', [$itemId]);

        flash('success', 'Cart updated');
        return redirect('/cart');
    }

    /**
     * Remove item from cart
     */
    public function remove(Request $request): Response
    {
        $itemId = $request->post('item_id');

        $this->db->delete('cart_items', 'id = ?', [$itemId]);

        event('cart.item_removed', ['item_id' => $itemId]);

        flash('success', 'Item removed from cart');
        return redirect('/cart');
    }

    /**
     * Get or create cart for current session/user
     */
    private function getOrCreateCart(): array
    {
        $userId = $_SESSION['user']['id'] ?? null;
        $sessionId = session_id();

        if ($userId) {
            $cart = $this->db->fetchOne(
                'SELECT * FROM carts WHERE user_id = ?',
                [$userId]
            );
        } else {
            $cart = $this->db->fetchOne(
                'SELECT * FROM carts WHERE session_id = ?',
                [$sessionId]
            );
        }

        if (!$cart) {
            $cartId = $this->db->insert('carts', [
                'user_id' => $userId,
                'session_id' => $sessionId,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $cart = ['id' => $cartId];
        }

        return $cart;
    }

    /**
     * Get cart items with product details
     */
    private function getCartItems(int $cartId): array
    {
        return $this->db->fetchAll(
            'SELECT ci.*, p.name, p.slug, p.featured_image
             FROM cart_items ci
             JOIN products p ON ci.product_id = p.id
             WHERE ci.cart_id = ?',
            [$cartId]
        );
    }

    /**
     * Calculate cart total
     */
    private function calculateTotal(array $items): float
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }
}
