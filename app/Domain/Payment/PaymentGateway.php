<?php

namespace App\Domain\Payment;

/**
 * Payment Gateway Interface
 * Todas las pasarelas de pago deben implementar esta interfaz
 */
interface PaymentGateway
{
    /**
     * Process a payment
     * 
     * @param array $paymentData [amount, order_id, payment_method_id, etc.]
     * @return array [success, transaction_id, status, error]
     */
    public function processPayment(array $paymentData): array;

    /**
     * Refund a payment
     */
    public function refund(string $transactionId, float $amount): array;

    /**
     * Handle webhook from payment gateway
     */
    public function handleWebhook(array $payload): bool;

    /**
     * Get gateway name
     */
    public function getName(): string;

    /**
     * Check if gateway is available/configured
     */
    public function isAvailable(): bool;
}
