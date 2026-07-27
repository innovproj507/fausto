<?php

namespace App\Domain\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database\Connection;
use App\Services\ERP\ERPManager;

class ERPController
{
    private Connection $db;
    private ERPManager $erp;

    public function __construct(Connection $db)
    {
        $this->db = $db;
        $this->erp = new ERPManager($db);
    }

    /**
     * ERP Dashboard - Overview and manual sync
     */
    public function dashboard(Request $request): Response
    {
        // Get recent sync logs
        $logs = $this->db->fetchAll(
            'SELECT * FROM erp_sync_logs ORDER BY created_at DESC LIMIT 20'
        );

        // Get sync stats
        $stats = [
            'total_syncs' => $this->db->fetchOne('SELECT COUNT(*) as count FROM erp_sync_logs')['count'],
            'successful' => $this->db->fetchOne('SELECT COUNT(*) as count FROM erp_sync_logs WHERE status = "success"')['count'],
            'failed' => $this->db->fetchOne('SELECT COUNT(*) as count FROM erp_sync_logs WHERE status = "failed"')['count'],
            'last_sync' => $this->db->fetchOne('SELECT created_at FROM erp_sync_logs ORDER BY created_at DESC LIMIT 1')['created_at'] ?? null,
        ];

        return Response::view('admin.erp.dashboard', [
            'logs' => $logs,
            'stats' => $stats,
            'erp_enabled' => $this->erp->isEnabled(),
            'erp_type' => env('ERP_TYPE', 'none'),
        ]);
    }

    /**
     * Sync products from ERP
     */
    public function syncProducts(Request $request): Response
    {
        if (!$this->erp->isEnabled()) {
            $_SESSION['error'] = 'ERP no está configurado';
            return Response::redirect('/manager/erp/dashboard');
        }

        $result = $this->erp->syncProductsFromERP();

        if (isset($result['error'])) {
            $_SESSION['error'] = 'Error en sincronización: ' . $result['error'];
        } else {
            $_SESSION['success'] = "Sincronizados {$result['synced']} productos";
            if (!empty($result['errors'])) {
                $_SESSION['warning'] = count($result['errors']) . ' productos con errores';
            }
        }

        return Response::redirect('/manager/erp/dashboard');
    }

    /**
     * Sync inventory from ERP
     */
    public function syncInventory(Request $request): Response
    {
        if (!$this->erp->isEnabled()) {
            $_SESSION['error'] = 'ERP no está configurado';
            return Response::redirect('/manager/erp/dashboard');
        }

        $result = $this->erp->syncInventoryFromERP();

        if (isset($result['error'])) {
            $_SESSION['error'] = 'Error en sincronización: ' . $result['error'];
        } else {
            $_SESSION['success'] = "Actualizado inventario de {$result['updated']} productos";
        }

        return Response::redirect('/manager/erp/dashboard');
    }

    /**
     * Test ERP connection
     */
    public function testConnection(Request $request): Response
    {
        if (!$this->erp->isEnabled()) {
            return Response::json(['success' => false, 'message' => 'ERP no configurado']);
        }

        $connected = $this->erp->testConnection();

        return Response::json([
            'success' => $connected,
            'message' => $connected ? 'Conexión exitosa' : 'Error de conexión'
        ]);
    }

    /**
     * Configuration page
     */
    public function config(Request $request): Response
    {
        $currentConfig = [
            'erp_type' => env('ERP_TYPE', 'none'),
            'erp_enabled' => env('ERP_ENABLED', 'false'),
            'erp_url' => env('ERP_API_URL', ''),
            'erp_key' => env('ERP_API_KEY', ''),
        ];

        return Response::view('admin.erp.config', [
            'config' => $currentConfig
        ]);
    }

    /**
     * Save configuration
     */
    public function saveConfig(Request $request): Response
    {
        $erpType = $request->input('erp_type');
        $enabled = $request->input('erp_enabled') ? 'true' : 'false';
        $url = $request->input('erp_url');
        $key = $request->input('erp_key');

        // Update .env file
        $this->updateEnvFile([
            'ERP_TYPE' => $erpType,
            'ERP_ENABLED' => $enabled,
            'ERP_API_URL' => $url,
            'ERP_API_KEY' => $key,
        ]);

        $_SESSION['success'] = 'Configuración guardada exitosamente';
        return Response::redirect('/manager/erp/config');
    }

    /**
     * Update .env file with new values
     */
    private function updateEnvFile(array $data): void
    {
        $envFile = __DIR__ . '/../../../.env';
        
        if (!file_exists($envFile)) {
            return;
        }

        $envContent = file_get_contents($envFile);

        foreach ($data as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}={$value}";

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
        }

        file_put_contents($envFile, $envContent);
    }
}
