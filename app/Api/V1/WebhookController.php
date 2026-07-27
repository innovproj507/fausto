<?php

namespace App\Api\V1;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database\Connection;

/**
 * Inbound webhooks. Deliberately outside the JwtAuth/RateLimiter group in
 * public/api.php - these are authenticated by provider-specific signatures,
 * not bearer tokens.
 */
class WebhookController extends ApiController
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function stripe(Request $request): Response
    {
        $payload = [
            'raw_body' => file_get_contents('php://input'),
            'signature' => $request->header('Stripe-Signature'),
        ];

        // StripePlugin (if installed+active) listens on 'webhook.stripe' and
        // verifies the signature itself, returning true/false. With no
        // active listener, dispatch() just hands the payload back unchanged.
        $handled = event('webhook.stripe', $payload);

        if ($handled === false) {
            return $this->error('Webhook signature verification failed', 400);
        }

        if ($handled === true) {
            return Response::json(['received' => true]);
        }

        return Response::json(['received' => true, 'note' => 'No active Stripe plugin handled this webhook'], 202);
    }

    public function paypal(Request $request): Response
    {
        $payload = $this->input($request);
        event('webhook.paypal', $payload);

        return Response::json(['received' => true, 'note' => 'No PayPal integration is implemented yet; payload was only dispatched as an event'], 202);
    }

    public function erp(Request $request): Response
    {
        $payload = $this->input($request);
        event('webhook.erp', $payload);

        $this->db->insert('erp_sync_logs', [
            'sync_type' => 'webhook',
            'direction' => 'from_erp',
            'status' => 'success',
            'records_count' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return Response::json(['received' => true]);
    }
}
