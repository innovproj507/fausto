<?php

namespace App\Api\V1;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database\Connection;
use App\Services\ERP\ERPManager;

/**
 * Thin wrapper around ERPManager (same one App\Domain\Admin\ERPController
 * already uses). Note: ERPManager reads/writes products.stock,
 * products.erp_product_id and products.last_synced_at - the latter two are
 * added by database/erp_integration.sql, but `stock` is not a real column
 * anywhere in the schema (stock lives in the `inventory` table instead).
 * That mismatch is pre-existing and out of scope here; sync will only be
 * exercised at all if ERP_ENABLED=true, which is not the default.
 */
class ErpController extends ApiController
{
    private Connection $db;
    private ERPManager $erp;

    public function __construct(Connection $db)
    {
        $this->db = $db;
        $this->erp = new ERPManager($db);
    }

    public function importProducts(Request $request): Response
    {
        if (!$this->isAdmin()) {
            return $this->error('Forbidden: admin role required', 403);
        }

        $result = $this->erp->syncProductsFromERP();
        if (isset($result['error'])) {
            return $this->error($result['error'], 422);
        }

        return Response::json(['data' => $result]);
    }

    public function syncInventory(Request $request): Response
    {
        if (!$this->isAdmin()) {
            return $this->error('Forbidden: admin role required', 403);
        }

        $result = $this->erp->syncInventoryFromERP();
        if (isset($result['error'])) {
            return $this->error($result['error'], 422);
        }

        return Response::json(['data' => $result]);
    }

    public function exportOrders(Request $request): Response
    {
        if (!$this->isAdmin()) {
            return $this->error('Forbidden: admin role required', 403);
        }

        $data = $this->input($request);
        $orderIds = $data['order_ids'] ?? [];
        if (!is_array($orderIds) || empty($orderIds)) {
            return $this->error('order_ids[] is required', 422);
        }

        $results = [];
        foreach ($orderIds as $orderId) {
            $results[$orderId] = $this->erp->sendOrderToERP((int) $orderId);
        }

        return Response::json(['data' => $results]);
    }

    public function syncStatus(Request $request): Response
    {
        if (!$this->isAdmin()) {
            return $this->error('Forbidden: admin role required', 403);
        }

        $logs = $this->db->fetchAll(
            'SELECT * FROM erp_sync_logs ORDER BY created_at DESC LIMIT 50'
        );

        return Response::json([
            'data' => $logs,
            'meta' => ['erp_enabled' => $this->erp->isEnabled()],
        ]);
    }

    private function isAdmin(): bool
    {
        return ($this->currentUser()['role_id'] ?? null) == 1;
    }
}
