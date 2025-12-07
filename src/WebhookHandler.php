<?php

namespace LicenseChain;

use LicenseChain\Exceptions\ValidationException;
use LicenseChain\Exceptions\AuthenticationException;

class WebhookHandler
{
    private string $secret;
    private int $tolerance;

    public function __construct(string $secret, int $tolerance = 300)
    {
        $this->secret = $secret;
        $this->tolerance = $tolerance; // seconds
    }

    /**
     * Verify webhook signature
     */
    public function verifySignature(string $payload, string $signature): bool
    {
        return Utils::verifyWebhookSignature($payload, $signature, $this->secret);
    }

    /**
     * Verify webhook timestamp
     */
    public function verifyTimestamp(string $timestamp): void
    {
        try {
            $webhookTime = new \DateTime($timestamp);
            $currentTime = new \DateTime();
            $timeDiff = abs($currentTime->getTimestamp() - $webhookTime->getTimestamp());
            
            if ($timeDiff > $this->tolerance) {
                throw new ValidationException("Webhook timestamp too old: {$timeDiff} seconds");
            }
        } catch (\Exception $e) {
            throw new ValidationException("Invalid timestamp format: " . $e->getMessage());
        }
    }

    /**
     * Verify complete webhook
     */
    public function verifyWebhook(string $payload, string $signature, string $timestamp): void
    {
        $this->verifyTimestamp($timestamp);
        
        if (!$this->verifySignature($payload, $signature)) {
            throw new AuthenticationException('Invalid webhook signature');
        }
    }

    /**
     * Process webhook event
     */
    public function processEvent(array $eventData): void
    {
        $payload = Utils::jsonSerialize($eventData['data'] ?? []);
        $this->verifyWebhook($payload, $eventData['signature'], $eventData['timestamp']);
        
        $eventType = $eventData['type'] ?? '';
        
        switch ($eventType) {
            case 'license.created':
                $this->handleLicenseCreated($eventData);
                break;
            case 'license.updated':
                $this->handleLicenseUpdated($eventData);
                break;
            case 'license.revoked':
                $this->handleLicenseRevoked($eventData);
                break;
            case 'license.expired':
                $this->handleLicenseExpired($eventData);
                break;
            case 'user.created':
                $this->handleUserCreated($eventData);
                break;
            case 'user.updated':
                $this->handleUserUpdated($eventData);
                break;
            case 'user.deleted':
                $this->handleUserDeleted($eventData);
                break;
            case 'product.created':
                $this->handleProductCreated($eventData);
                break;
            case 'product.updated':
                $this->handleProductUpdated($eventData);
                break;
            case 'product.deleted':
                $this->handleProductDeleted($eventData);
                break;
            case 'payment.completed':
                $this->handlePaymentCompleted($eventData);
                break;
            case 'payment.failed':
                $this->handlePaymentFailed($eventData);
                break;
            case 'payment.refunded':
                $this->handlePaymentRefunded($eventData);
                break;
            default:
                echo "Unknown webhook event type: {$eventType}\n";
        }
    }

    private function handleLicenseCreated(array $eventData): void
    {
        echo "License created: {$eventData['id']}\n";
        // Add custom logic for license created event
    }

    private function handleLicenseUpdated(array $eventData): void
    {
        echo "License updated: {$eventData['id']}\n";
        // Add custom logic for license updated event
    }

    private function handleLicenseRevoked(array $eventData): void
    {
        echo "License revoked: {$eventData['id']}\n";
        // Add custom logic for license revoked event
    }

    private function handleLicenseExpired(array $eventData): void
    {
        echo "License expired: {$eventData['id']}\n";
        // Add custom logic for license expired event
    }

    private function handleUserCreated(array $eventData): void
    {
        echo "User created: {$eventData['id']}\n";
        // Add custom logic for user created event
    }

    private function handleUserUpdated(array $eventData): void
    {
        echo "User updated: {$eventData['id']}\n";
        // Add custom logic for user updated event
    }

    private function handleUserDeleted(array $eventData): void
    {
        echo "User deleted: {$eventData['id']}\n";
        // Add custom logic for user deleted event
    }

    private function handleProductCreated(array $eventData): void
    {
        echo "Product created: {$eventData['id']}\n";
        // Add custom logic for product created event
    }

    private function handleProductUpdated(array $eventData): void
    {
        echo "Product updated: {$eventData['id']}\n";
        // Add custom logic for product updated event
    }

    private function handleProductDeleted(array $eventData): void
    {
        echo "Product deleted: {$eventData['id']}\n";
        // Add custom logic for product deleted event
    }

    private function handlePaymentCompleted(array $eventData): void
    {
        echo "Payment completed: {$eventData['id']}\n";
        // Add custom logic for payment completed event
    }

    private function handlePaymentFailed(array $eventData): void
    {
        echo "Payment failed: {$eventData['id']}\n";
        // Add custom logic for payment failed event
    }

    private function handlePaymentRefunded(array $eventData): void
    {
        echo "Payment refunded: {$eventData['id']}\n";
        // Add custom logic for payment refunded event
    }
}

class WebhookEvents
{
    public const LICENSE_CREATED = 'license.created';
    public const LICENSE_UPDATED = 'license.updated';
    public const LICENSE_REVOKED = 'license.revoked';
    public const LICENSE_EXPIRED = 'license.expired';
    public const USER_CREATED = 'user.created';
    public const USER_UPDATED = 'user.updated';
    public const USER_DELETED = 'user.deleted';
    public const PRODUCT_CREATED = 'product.created';
    public const PRODUCT_UPDATED = 'product.updated';
    public const PRODUCT_DELETED = 'product.deleted';
    public const PAYMENT_COMPLETED = 'payment.completed';
    public const PAYMENT_FAILED = 'payment.failed';
    public const PAYMENT_REFUNDED = 'payment.refunded';
}
