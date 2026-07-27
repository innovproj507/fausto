<?php

namespace App\Services\ERP;

use App\Contracts\ERPAdapterInterface;
use App\Core\Database\Connection;

/**
 * Central ERP Manager - Facade for ERP operations
 */
class ERPManager
{
    private ?ERPAdapterInterface $adapter = null;
    private Connection $db;
    private bool $enabled = false;

    public function __construct(Connection $db)
    {
        $this->db = $db;
        $this->loadAdapter();
    }

    /**
     * Load the configured ERP adapter
     */
    private function loadAdapter(): void
    {
        $erpType = env('ERP_TYPE', 'none');

        if ($erpType === 'none' || env('ERP_ENABLED') !== 'true') {
            return;
        }

        try {
            $adapterClass = $this->getAdapterClass($erpType);

            if (!class_exists($adapterClass)) {
                $this->logError("ERP Adapter class not found: {$adapterClass}");
                return;
            }

            $this->adapter = new $adapterClass();

            // Configure with environment variables
            $this->adapter->configure([
                'url' => env('ERP_API_URL'),
                'key' => env('ERP_API_KEY'),
                'secret' => env('ERP_API_SECRET'),
                'company_id' => env('ERP_COMPANY_ID'),
            ]);

            $this->enabled = true;
        } catch (\Exception $e) {
            $this->logError("Failed to load ERP adapter: " . $e->getMessage());
        }
    }

    /**
     * Get adapter class name from type
     */
    private function getAdapterClass(string $type): string
    {
        $adapters = [
            'custom' => 'App\\Services\\ERP\\Adapters\\CustomAdapter',
            'sap' => 'App\\Services\\ERP\\Adapters\\SAPAdapter',
            'odoo' => 'App\\Services\\ERP\\Adapters\\OdooAdapter',
        ];

        if (!isset($adapters[$type])) {
            throw new \Exception("Unknown ERP type: {$type}");
        }

        return $adapters[$type];
    }

    /**
     * Check if ERP is enabled and configured
     */
    public function isEnabled(): bool
    {
        return $this->enabled && $this->adapter !== null;
    }

    /**
     * Test ERP connection
     */
    public function testConnection(): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        try {
            return $this->adapter->testConnection();
        } catch (\Exception $e) {
            $this->logError("Connection test failed: " . $e->getMessage());
            return false;
        }
    }

    // ==================== PRODUCT SYNC ====================

    /**
     * Sync all products from ERP to e-commerce
     */
    public function syncProductsFromERP(): array
    {
        if (!$this->isEnabled()) {
            return ['error' => 'ERP not configured'];
        }

        try {
            $erpProducts = $this->adapter->getProducts();
            $synced = 0;
            $errors = [];

            foreach ($erpProducts as $erpProduct) {
                try {
                    $this->syncSingleProduct($erpProduct);
                    $synced++;
                } catch (\Exception $e) {
                    $errors[] = "SKU {$erpProduct['sku']}: " . $e->getMessage();
                }
            }

            $this->logSync('product', 'from_erp', 'success', $synced);

            return [
                'success' => true,
                'synced' => $synced,
                'errors' => $errors
            ];
        } catch (\Exception $e) {
            $this->logSync('product', 'from_erp', 'failed', 0, $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Sync single product from ERP data
     */
    private function syncSingleProduct(array $erpProduct): void
    {
        $existing = $this->db->fetchOne(
            'SELECT id FROM products WHERE sku = ?',
            [$erpProduct['sku']]
        );

        $data = [
            'name' => $erpProduct['name'],
            'sku' => $erpProduct['sku'],
            'price' => $erpProduct['price'] ?? 0,
            'stock' => $erpProduct['stock'] ?? 0,
            'description' => $erpProduct['description'] ?? '',
            'erp_product_id' => $erpProduct['id'] ?? null,
            'last_synced_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $this->db->update('products', $data, 'id = ?', [$existing['id']]);
        } else {
            // Set defaults for new products
            $data['status'] = 'active';
            $data['slug'] = $this->generateSlug($data['name']);
            $this->db->insert('products', $data);
        }
    }

    /**
     * Send product to ERP
     */
    public function sendProductToERP(int $productId): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $product = $this->db->fetchOne('SELECT * FROM products WHERE id = ?', [$productId]);

        if (!$product) {
            return false;
        }

        try {
            $result = $this->adapter->createProduct([
                'sku' => $product['sku'],
                'name' => $product['name'],
                'price' => $product['price'],
                'stock' => $product['stock'],
                'description' => $product['description'],
            ]);

            if ($result) {
                $this->logSync('product', 'to_erp', 'success', 1);
            }

            return $result;
        } catch (\Exception $e) {
            $this->logSync('product', 'to_erp', 'failed', 0, $e->getMessage());
            return false;
        }
    }

    // ==================== ORDER SYNC ====================

    /**
     * Send order to ERP
     */
    public function sendOrderToERP(int $orderId): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        try {
            $orderData = $this->getOrderDataForERP($orderId);

            if (!$orderData) {
                return false;
            }

            $erpOrderId = $this->adapter->createOrder($orderData);

            if ($erpOrderId) {
                // Save ERP reference
                $this->db->update('orders', [
                    'erp_order_id' => $erpOrderId
                ], 'id = ?', [$orderId]);

                $this->logSync('order', 'to_erp', 'success', 1);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->logSync('order', 'to_erp', 'failed', 0, $e->getMessage());
            return false;
        }
    }

    /**
     * Get order data formatted for ERP
     */
    private function getOrderDataForERP(int $orderId): ?array
    {
        $order = $this->db->fetchOne(
            'SELECT o.*, u.first_name, u.last_name, u.email, u.phone
             FROM orders o
             LEFT JOIN users u ON o.user_id = u.id
             WHERE o.id = ?',
            [$orderId]
        );

        if (!$order) {
            return null;
        }

        $items = $this->db->fetchAll(
            'SELECT oi.*, p.sku
             FROM order_items oi
             LEFT JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?',
            [$orderId]
        );

        return [
            'order_number' => $order['id'],
            'customer' => [
                'name' => $order['first_name'] . ' ' . $order['last_name'],
                'email' => $order['email'],
                'phone' => $order['phone'] ?? '',
            ],
            'items' => array_map(function($item) {
                return [
                    'sku' => $item['sku'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ];
            }, $items),
            'total' => $order['total'],
            'date' => $order['created_at'],
        ];
    }

    // ==================== INVENTORY SYNC ====================

    /**
     * Update inventory from ERP
     */
    public function syncInventoryFromERP(): array
    {
        if (!$this->isEnabled()) {
            return ['error' => 'ERP not configured'];
        }

        try {
            $products = $this->db->fetchAll('SELECT id, sku FROM products WHERE sku IS NOT NULL');
            $updated = 0;

            foreach ($products as $product) {
                $stock = $this->adapter->getInventory($product['sku']);

                if ($stock !== null) {
                    $this->db->update('products', [
                        'stock' => $stock,
                        'last_synced_at' => date('Y-m-d H:i:s')
                    ], 'id = ?', [$product['id']]);
                    $updated++;
                }
            }

            $this->logSync('inventory', 'from_erp', 'success', $updated);

            return ['success' => true, 'updated' => $updated];
        } catch (\Exception $e) {
            $this->logSync('inventory', 'from_erp', 'failed', 0, $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    // ==================== LOGGING ====================

    /**
     * Log sync operation
     */
    private function logSync(string $type, string $direction, string $status, int $count, string $error = null): void
    {
        $this->db->insert('erp_sync_logs', [
            'sync_type' => $type,
            'direction' => $direction,
            'status' => $status,
            'records_count' => $count,
            'error_message' => $error,
        ]);
    }

    /**
     * Log error
     */
    private function logError(string $message): void
    {
        error_log("ERP Error: " . $message);
    }

    /**
     * Generate URL-friendly slug
     */
    private function generateSlug(string $name): string
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    }
}
