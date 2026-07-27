<?php

namespace App\Api\V1;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database\Connection;
use App\Services\ERP\ERPManager;

/**
 * Inventory is tracked per product+warehouse in the `inventory` table
 * (not a `products.stock` column - that column doesn't exist), so reads
 * go through the pre-built `v_products_inventory` view.
 */
class InventoryController extends ApiController
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

        $rows = $this->db->fetchAll(
            "SELECT * FROM v_products_inventory ORDER BY total_available ASC LIMIT $perPage OFFSET $offset"
        );

        return Response::json(['data' => $rows]);
    }

    public function show(Request $request, $id): Response
    {
        $row = $this->db->fetchOne('SELECT * FROM v_products_inventory WHERE id = ?', [$id]);

        if (!$row) {
            return $this->error('Product not found', 404);
        }

        $warehouses = $this->db->fetchAll(
            'SELECT i.*, w.name as warehouse_name FROM inventory i JOIN warehouses w ON i.warehouse_id = w.id WHERE i.product_id = ?',
            [$id]
        );

        return Response::json(['data' => array_merge($row, ['warehouses' => $warehouses])]);
    }

    public function update(Request $request, $id): Response
    {
        if (($this->currentUser()['role_id'] ?? null) != 1) {
            return $this->error('Forbidden: admin role required', 403);
        }

        $data = $this->input($request);
        if (empty($data['warehouse_id']) || !isset($data['quantity'])) {
            return $this->error('warehouse_id and quantity are required', 422);
        }

        $inventory = $this->db->fetchOne(
            'SELECT * FROM inventory WHERE product_id = ? AND warehouse_id = ? AND variant_id IS NULL',
            [$id, $data['warehouse_id']]
        );

        $newQuantity = (int) $data['quantity'];

        if ($inventory) {
            $this->db->update('inventory', ['quantity' => $newQuantity], 'id = ?', [$inventory['id']]);
            $inventoryId = $inventory['id'];
            $previousQuantity = $inventory['quantity'];
        } else {
            $inventoryId = $this->db->insert('inventory', [
                'product_id' => $id,
                'warehouse_id' => $data['warehouse_id'],
                'quantity' => $newQuantity,
            ]);
            $previousQuantity = 0;
        }

        $this->db->insert('stock_movements', [
            'inventory_id' => $inventoryId,
            'type' => 'adjustment',
            'quantity' => $newQuantity - $previousQuantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $newQuantity,
            'reference_type' => 'api',
            'reason' => 'Manual adjustment via API',
            'user_id' => $this->currentUserId(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $row = $this->db->fetchOne('SELECT * FROM v_products_inventory WHERE id = ?', [$id]);
        return Response::json(['data' => $row]);
    }

    public function sync(Request $request): Response
    {
        if (($this->currentUser()['role_id'] ?? null) != 1) {
            return $this->error('Forbidden: admin role required', 403);
        }

        $erpManager = new ERPManager($this->db);
        $result = $erpManager->syncInventoryFromERP();

        if (isset($result['error'])) {
            return $this->error($result['error'], 422);
        }

        return Response::json(['data' => $result]);
    }
}
