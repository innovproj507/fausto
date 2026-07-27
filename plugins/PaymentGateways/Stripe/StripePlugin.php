<?php

namespace Plugins\PaymentGateways\Stripe;

use App\Core\Container;
use App\Core\Plugin\PluginInterface;
use App\Core\Plugin\EventDispatcher;
use App\Domain\Payment\PaymentGateway;

/**
 * Stripe Payment Gateway Plugin
 * Implementa la interfaz PluginInterface y PaymentGateway
 */
class StripePlugin implements PluginInterface, PaymentGateway
{
    private Container $container;
    private array $config;

    public function __construct(Container $container, array $config = [])
    {
        $this->container = $container;
        $this->config = $config;
    }

    public function getName(): string
    {
        return 'stripe_payment';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getDescription(): string
    {
        return 'Integración con Stripe para pagos con tarjeta';
    }

    public function boot(): void
    {
        // Inicializar Stripe SDK si está disponible
        if (class_exists('\Stripe\Stripe')) {
            \Stripe\Stripe::setApiKey($this->config['secret_key']);
        }
    }

    public function registerHooks(EventDispatcher $dispatcher): void
    {
        // Escuchar evento de pago
        $dispatcher->listen('payment.process', function ($data) {
            if ($data['payment_method'] === 'stripe') {
                return $this->processPayment($data);
            }
            return $data;
        }, 20);

        // Escuchar webhook de Stripe
        $dispatcher->listen('webhook.stripe', function ($payload) {
            return $this->handleWebhook($payload);
        }, 10);
    }

    public function install(): bool
    {
        // Crear tabla de transacciones si es necesario
        // O cualquier setup inicial
        return true;
    }

    public function uninstall(): bool
    {
        // Limpieza al desinstalar
        return true;
    }

    public function activate(): void
    {
        // Acciones al activar el plugin
    }

    public function deactivate(): void
    {
        // Acciones al desactivar el plugin
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    // ============================================
    // Payment Gateway Interface Implementation
    // ============================================

    public function processPayment(array $paymentData): array
    {
        try {
            // Crear PaymentIntent en Stripe
            $intent = \Stripe\PaymentIntent::create([
                'amount' => $paymentData['amount'] * 100, // Stripe usa centavos
                'currency' => $this->config['currency'],
                'payment_method' => $paymentData['payment_method_id'],
                'confirm' => true,
                'metadata' => [
                    'order_id' => $paymentData['order_id']
                ]
            ]);

            return [
                'success' => true,
                'transaction_id' => $intent->id,
                'status' => $intent->status,
                'amount' => $paymentData['amount'],
                'response' => $intent
            ];
        } catch (\Stripe\Exception\CardException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function refund(string $transactionId, float $amount): array
    {
        try {
            $refund = \Stripe\Refund::create([
                'payment_intent' => $transactionId,
                'amount' => $amount * 100
            ]);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'status' => $refund->status
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function handleWebhook(array $payload): bool
    {
        $event = null;

        try {
            // Verificar firma del webhook
            $event = \Stripe\Webhook::constructEvent(
                $payload['raw_body'],
                $payload['signature'],
                $this->config['webhook_secret']
            );
        } catch (\Exception $e) {
            return false;
        }

        // Procesar el evento
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentSuccess($event->data->object);
                break;

            case 'payment_intent.payment_failed':
                $this->handlePaymentFailed($event->data->object);
                break;

            case 'charge.refunded':
                $this->handleRefund($event->data->object);
                break;
        }

        return true;
    }

    private function handlePaymentSuccess($paymentIntent): void
    {
        // Actualizar estado del pedido en la base de datos
        $orderId = $paymentIntent->metadata->order_id ?? null;
        
        if ($orderId) {
            $db = $this->container->make(\App\Core\Database\Connection::class);
            $db->update('orders', [
                'payment_status' => 'paid',
                'status' => 'confirmed'
            ], 'id = ?', [$orderId]);

            $db->insert('payments', [
                'order_id' => $orderId,
                'transaction_id' => $paymentIntent->id,
                'payment_method' => 'stripe',
                'amount' => $paymentIntent->amount / 100,
                'currency' => strtoupper($paymentIntent->currency),
                'status' => 'completed',
                'gateway_response' => json_encode($paymentIntent),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    private function handlePaymentFailed($paymentIntent): void
    {
        // Marcar pago como fallido
        $orderId = $paymentIntent->metadata->order_id ?? null;
        
        if ($orderId) {
            $db = $this->container->make(\App\Core\Database\Connection::class);
            $db->update('orders', [
                'payment_status' => 'failed'
            ], 'id = ?', [$orderId]);
        }
    }

    private function handleRefund($charge): void
    {
        // Procesar reembolso
        // Actualizar registros en la base de datos
    }

    public function getName(): string
    {
        return 'Stripe';
    }

    public function isAvailable(): bool
    {
        return !empty($this->config['secret_key']);
    }
}
