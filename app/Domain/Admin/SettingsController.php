<?php

namespace App\Domain\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database\Connection;

class SettingsController
{
    /**
     * Settings this form manages, and how each value should be typed/read back
     */
    private const FIELDS = [
        'app_name' => 'string',
        'contact_email' => 'string',
        'contact_phone' => 'string',
        'meta_description' => 'string',
        'facebook_url' => 'string',
        'instagram_url' => 'string',
        'whatsapp' => 'string',
        'maintenance_mode' => 'boolean',
        'maintenance_message' => 'string',
    ];

    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function index(Request $request): Response
    {
        return Response::view('admin.settings.index', [
            'settings' => $this->allSettings(),
        ]);
    }

    public function update(Request $request): Response
    {
        foreach (self::FIELDS as $key => $type) {
            $value = $type === 'boolean'
                ? ($request->post($key) ? '1' : '0')
                : (string) $request->post($key, '');

            $this->db->query(
                'INSERT INTO settings (`key`, `value`, `type`) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE value = VALUES(value), type = VALUES(type)',
                [$key, $value, $type]
            );
        }

        $_SESSION['success'] = 'Configuración actualizada exitosamente';
        return Response::redirect('/manager/settings');
    }

    private function allSettings(): array
    {
        $rows = $this->db->fetchAll('SELECT `key`, `value`, `type` FROM settings');

        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['key']] = $row['type'] === 'boolean'
                ? filter_var($row['value'], FILTER_VALIDATE_BOOLEAN)
                : $row['value'];
        }

        return $settings;
    }
}
