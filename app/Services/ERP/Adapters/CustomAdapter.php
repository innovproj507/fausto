<?php

namespace App\Services\ERP\Adapters;

use App\Contracts\ERPAdapterInterface;

/**
 * Generic REST API Adapter for custom ERPs
 * Works with standard JSON REST APIs
 */
class CustomAdapter implements ERPAdapterInterface
{
    private array $config;
    private ?string $token = null;

    public function configure(array $config): void
    {
        $this->config = $config;
    }

    public function testConnection(): bool
    {
        try {
            $response = $this->request('GET', '/health');
            return $response['status'] === 200;
        } catch (\Exception $e) {
            return false;
        }
    }

    // ==================== PRODUCTS ====================

    public function getProducts(array $filters = []): array
    {
        $response = $this->request('GET', '/products', $filters);

        if ($response['status'] !== 200) {
            throw new \Exception('Failed to fetch products');
        }

        // Normalize to standard format
        return array_map(function($item) {
            return [
                'id' => $item['id'] ?? null,
                'sku' => $item['sku'] ?? $item['code'],
                'name' => $item['name'] ?? $item['title'],
                'price' => $item['price'] ?? 0,
                'stock' => $item['stock'] ?? $item['quantity'] ?? 0,
                'description' => $item['description'] ?? '',
            ];
        }, $response['data'] ?? []);
    }

    public function getProduct(string $sku): ?array
    {
        $response = $this->request('GET', "/products/{$sku}");

        if ($response['status'] !== 200) {
            return null;
        }

        $item = $response['data'];
        return [
            'id' => $item['id'] ?? null,
            'sku' => $item['sku'] ?? $item['code'],
            'name' => $item['name'] ?? $item['title'],
            'price' => $item['price'] ?? 0,
            'stock' => $item['stock'] ?? $item['quantity'] ?? 0,
            'description' => $item['description'] ?? '',
        ];
    }

    public function createProduct(array $data): bool
    {
        $response = $this->request('POST', '/products', $data);
        return $response['status'] === 201 || $response['status'] === 200;
    }

    public function updateProduct(string $sku, array $data): bool
    {
        $response = $this->request('PUT', "/products/{$sku}", $data);
        return $response['status'] === 200;
    }

    public function deleteProduct(string $sku): bool
    {
        $response = $this->request('DELETE', "/products/{$sku}");
        return $response['status'] === 200 || $response['status'] === 204;
    }

    // ==================== INVENTORY ====================

    public function getInventory(string $sku): ?int
    {
        try {
            $response = $this->request('GET', "/inventory/{$sku}");

            if ($response['status'] !== 200) {
                return null;
            }

            return $response['data']['quantity'] ?? $response['data']['stock'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function updateInventory(string $sku, int $quantity): bool
    {
        $response = $this->request('PUT', "/inventory/{$sku}", [
            'quantity' => $quantity
        ]);

        return $response['status'] === 200;
    }

    // ==================== ORDERS ====================

    public function createOrder(array $orderData): ?string
    {
        $response = $this->request('POST', '/orders', $orderData);

        if ($response['status'] === 201 || $response['status'] === 200) {
            return $response['data']['id'] ?? $response['data']['order_id'] ?? null;
        }

        return null;
    }

    public function getOrderStatus(string $erpOrderId): ?string
    {
        $response = $this->request('GET', "/orders/{$erpOrderId}");

        if ($response['status'] !== 200) {
            return null;
        }

        return $response['data']['status'] ?? null;
    }

    public function cancelOrder(string $erpOrderId): bool
    {
        $response = $this->request('POST', "/orders/{$erpOrderId}/cancel");
        return $response['status'] === 200;
    }

    // ==================== CUSTOMERS ====================

    public function createCustomer(array $customerData): ?string
    {
        $response = $this->request('POST', '/customers', $customerData);

        if ($response['status'] === 201 || $response['status'] === 200) {
            return $response['data']['id'] ?? $response['data']['customer_id'] ?? null;
        }

        return null;
    }

    public function getCustomer(string $erpCustomerId): ?array
    {
        $response = $this->request('GET', "/customers/{$erpCustomerId}");

        if ($response['status'] !== 200) {
            return null;
        }

        return $response['data'];
    }

    // ==================== HTTP CLIENT ====================

    /**
     * Make HTTP request to ERP API
     */
    private function request(string $method, string $endpoint, array $data = []): array
    {
        $url = rtrim($this->config['url'], '/') . $endpoint;

        $ch = curl_init($url);

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        // Add authentication
        if (!empty($this->config['key'])) {
            $headers[] = 'Authorization: Bearer ' . $this->config['key'];
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
        ]);

        if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif (!empty($data) && $method === 'GET') {
            $url .= '?' . http_build_query($data);
            curl_setopt($ch, CURLOPT_URL, $url);
        }

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("HTTP Error: {$error}");
        }

        return [
            'status' => $statusCode,
            'data' => json_decode($response, true) ?? []
        ];
    }
}
