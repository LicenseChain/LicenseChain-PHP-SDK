<?php

namespace LicenseChain\Services;

use LicenseChain\{
    ApiClient,
    Utils,
    Exceptions\ValidationException
};

class WebhookService
{
    private ApiClient $client;

    public function __construct(ApiClient $client)
    {
        $this->client = $client;
    }

    public function create(string $url, array $events, ?string $secret = null): array
    {
        $this->validateWebhookParams($url, $events);
        
        $data = [
            'url' => $url,
            'events' => $events,
            'secret' => $secret
        ];
        
        $response = $this->client->post('/webhooks', $data);
        return $this->normalizeWebhookPayload($response['data'] ?? $response);
    }

    public function get(string $webhookId): array
    {
        $this->validateUuid($webhookId, 'webhook_id');
        
        $response = $this->client->get("/webhooks/{$webhookId}");
        return $this->normalizeWebhookPayload($response['data'] ?? $response);
    }

    public function update(string $webhookId, array $updates): array
    {
        $this->validateUuid($webhookId, 'webhook_id');
        
        $response = $this->client->put("/webhooks/{$webhookId}", Utils::sanitizeMetadata($updates));
        return $this->normalizeWebhookPayload($response['data'] ?? $response);
    }

    public function delete(string $webhookId): bool
    {
        $this->validateUuid($webhookId, 'webhook_id');
        
        $this->client->delete("/webhooks/{$webhookId}");
        return true;
    }

    public function list(): array
    {
        $response = $this->client->get('/webhooks');
        $items = $response['data'] ?? [];
        return array_map(fn($webhook) => $this->normalizeWebhookPayload($webhook), $items);
    }

    private function validateWebhookParams(string $url, array $events): void
    {
        Utils::validateNotEmpty($url, 'url');
        if (empty($events)) {
            throw new ValidationException('Events cannot be empty');
        }
    }

    private function validateUuid(string $id, string $fieldName): void
    {
        Utils::validateNotEmpty($id, $fieldName);
        if (!Utils::validateUuid($id)) {
            throw new ValidationException("Invalid {$fieldName} format");
        }
    }

    private function normalizeWebhookPayload(array $payload): array
    {
        return [
            'id' => $payload['id'] ?? null,
            'app_id' => $payload['app_id'] ?? '',
            'url' => $payload['url'] ?? '',
            'events' => $payload['events'] ?? [],
            'secret' => $payload['secret'] ?? null,
            'status' => (($payload['active'] ?? true) ? 'active' : 'inactive'),
            'created_at' => $payload['created_at'] ?? ($payload['createdAt'] ?? null),
            'updated_at' => $payload['updated_at'] ?? ($payload['updatedAt'] ?? null),
        ];
    }
}
