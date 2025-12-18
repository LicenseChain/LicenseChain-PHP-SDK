<?php

namespace LicenseChain;

use LicenseChain\Exceptions\ValidationException;

/**
 * Webhook verifier for secure webhook handling
 */
class WebhookVerifier
{
    private string $secret;

    public function __construct(string $secret)
    {
        if (empty($secret)) {
            throw new \InvalidArgumentException('Webhook secret is required');
        }
        $this->secret = $secret;
    }

    /**
     * Verify webhook signature
     */
    public function verifySignature(string $payload, string $signature, string $algorithm = 'sha256'): bool
    {
        try {
            $expectedSignature = $this->generateSignature($payload, $algorithm);
            return $this->secureCompare($signature, $expectedSignature);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Parse and verify webhook payload
     */
    public function parsePayload(string $payload, string $signature, string $algorithm = 'sha256'): array
    {
        if (!$this->verifySignature($payload, $signature, $algorithm)) {
            throw new ValidationException('Invalid webhook signature');
        }

        $data = json_decode($payload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ValidationException('Invalid JSON payload: ' . json_last_error_msg());
        }

        return $this->extractEventData($data);
    }

    /**
     * Generate signature for testing
     */
    public function generateSignature(string $payload, string $algorithm = 'sha256'): string
    {
        $hash = hash_hmac($algorithm, $payload, $this->secret);
        return "{$algorithm}={$hash}";
    }

    /**
     * Verify webhook event type
     */
    public function verifyEventType(array $payload, string $expectedType): bool
    {
        $eventType = $payload['type'] ?? $payload['event'] ?? null;
        return $eventType === $expectedType;
    }

    /**
     * Extract event data from webhook payload
     */
    public function extractEventData(array $payload): array
    {
        return [
            'id' => $payload['id'] ?? null,
            'type' => $payload['type'] ?? $payload['event'] ?? null,
            'created_at' => $payload['created_at'] ?? $payload['createdAt'] ?? null,
            'data' => $payload['data'] ?? $payload['object'] ?? [],
        ];
    }

    /**
     * Verify webhook timestamp (prevent replay attacks)
     */
    public function verifyTimestamp(array $payload, int $tolerance = 300): bool
    {
        $timestamp = $payload['created_at'] ?? $payload['createdAt'] ?? null;
        if (!$timestamp) {
            return true;
        }

        try {
            $eventTime = strtotime($timestamp);
            $currentTime = time();
            return abs($currentTime - $eventTime) <= $tolerance;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Securely compare two strings
     */
    private function secureCompare(string $a, string $b): bool
    {
        if (strlen($a) !== strlen($b)) {
            return false;
        }

        $result = 0;
        for ($i = 0; $i < strlen($a); $i++) {
            $result |= ord($a[$i]) ^ ord($b[$i]);
        }
        return $result === 0;
    }
}

/**
 * Webhook event handler
 */
class WebhookHandler
{
    private WebhookVerifier $verifier;
    private array $handlers = [];

    public function __construct(string $secret)
    {
        $this->verifier = new WebhookVerifier($secret);
        $this->setupDefaultHandlers();
    }

    /**
     * Handle webhook payload
     */
    public function handle(string $payload, string $signature, string $algorithm = 'sha256'): array
    {
        try {
            // Parse and verify the payload
            $event = $this->verifier->parsePayload($payload, $signature, $algorithm);

            // Verify timestamp to prevent replay attacks
            if (!$this->verifier->verifyTimestamp($event)) {
                throw new ValidationException('Webhook timestamp is too old');
            }

            // Route to appropriate handler
            return $this->handleEvent($event);
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Route event to appropriate handler
     */
    private function handleEvent(array $event): array
    {
        $eventType = $event['type'] ?? 'unknown';
        $handler = $this->handlers[$eventType] ?? [$this, 'handleUnknownEvent'];
        
        if (is_callable($handler)) {
            return call_user_func($handler, $event);
        }
        
        return $this->handleUnknownEvent($event);
    }

    /**
     * Register event handler
     */
    public function on(string $eventType, callable $handler): void
    {
        $this->handlers[$eventType] = $handler;
    }

    /**
     * Remove event handler
     */
    public function off(string $eventType): void
    {
        unset($this->handlers[$eventType]);
    }

    /**
     * Setup default handlers
     */
    private function setupDefaultHandlers(): void
    {
        $this->handlers = [
            'license.created' => [$this, 'handleLicenseCreated'],
            'license.updated' => [$this, 'handleLicenseUpdated'],
            'license.revoked' => [$this, 'handleLicenseRevoked'],
            'license.expired' => [$this, 'handleLicenseExpired'],
            'license.validated' => [$this, 'handleLicenseValidated'],
            'app.created' => [$this, 'handleAppCreated'],
            'app.updated' => [$this, 'handleAppUpdated'],
            'app.deleted' => [$this, 'handleAppDeleted'],
            'user.created' => [$this, 'handleUserCreated'],
            'user.updated' => [$this, 'handleUserUpdated'],
        ];
    }

    // Default event handlers - override in subclasses or register custom handlers

    public function handleLicenseCreated(array $event): array
    {
        return ['status' => 'processed', 'event' => 'license.created'];
    }

    public function handleLicenseUpdated(array $event): array
    {
        return ['status' => 'processed', 'event' => 'license.updated'];
    }

    public function handleLicenseRevoked(array $event): array
    {
        return ['status' => 'processed', 'event' => 'license.revoked'];
    }

    public function handleLicenseExpired(array $event): array
    {
        return ['status' => 'processed', 'event' => 'license.expired'];
    }

    public function handleLicenseValidated(array $event): array
    {
        return ['status' => 'processed', 'event' => 'license.validated'];
    }

    public function handleAppCreated(array $event): array
    {
        return ['status' => 'processed', 'event' => 'app.created'];
    }

    public function handleAppUpdated(array $event): array
    {
        return ['status' => 'processed', 'event' => 'app.updated'];
    }

    public function handleAppDeleted(array $event): array
    {
        return ['status' => 'processed', 'event' => 'app.deleted'];
    }

    public function handleUserCreated(array $event): array
    {
        return ['status' => 'processed', 'event' => 'user.created'];
    }

    public function handleUserUpdated(array $event): array
    {
        return ['status' => 'processed', 'event' => 'user.updated'];
    }

    public function handleUnknownEvent(array $event): array
    {
        return ['status' => 'ignored', 'event' => $event['type'] ?? 'unknown'];
    }

    /**
     * Get verifier instance
     */
    public function getVerifier(): WebhookVerifier
    {
        return $this->verifier;
    }

    /**
     * Get registered handlers
     */
    public function getHandlers(): array
    {
        return array_keys($this->handlers);
    }

    /**
     * Clear all handlers
     */
    public function clearHandlers(): void
    {
        $this->handlers = [];
    }
}
