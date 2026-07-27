<?php

namespace App\Contracts;

/**
 * Interface that all ERP adapters must implement
 */
interface ERPAdapterInterface
{
    /**
     * Configure adapter with credentials and settings
     */
    public function configure(array $config): void;

    /**
     * Test the ERP connection
     */
    public function testConnection(): bool;

    // ==================== PRODUCTS ====================

    /**
     * Get all products from ERP
     * @return array Array of products in standard format
     */
    public function getProducts(array $filters = []): array;

    /**
     * Get single product by SKU
     */
    public function getProduct(string $sku): ?array;

    /**
     * Create product in ERP
     */
    public function createProduct(array $data): bool;

    /**
     * Update product in ERP
     */
    public function updateProduct(string $sku, array $data): bool;

    /**
     * Delete product from ERP
     */
    public function deleteProduct(string $sku): bool;

    // ==================== INVENTORY ====================

    /**
     * Get inventory level for a product
     */
    public function getInventory(string $sku): ?int;

    /**
     * Update inventory level
     */
    public function updateInventory(string $sku, int $quantity): bool;

    // ==================== ORDERS ====================

    /**
     * Create order in ERP
     * @return string|null ERP Order ID if successful
     */
    public function createOrder(array $orderData): ?string;

    /**
     * Get order status from ERP
     */
    public function getOrderStatus(string $erpOrderId): ?string;

    /**
     * Cancel order in ERP
     */
    public function cancelOrder(string $erpOrderId): bool;

    // ==================== CUSTOMERS ====================

    /**
     * Create customer in ERP
     * @return string|null ERP Customer ID if successful
     */
    public function createCustomer(array $customerData): ?string;

    /**
     * Get customer from ERP
     */
    public function getCustomer(string $erpCustomerId): ?array;
}
