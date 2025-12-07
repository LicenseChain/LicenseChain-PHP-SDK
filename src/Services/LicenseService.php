<?php

namespace LicenseChain\Services;

use LicenseChain\{
    ApiClient,
    Utils,
    Exceptions\ValidationException
};

class LicenseService
{
    private ApiClient $client;

    public function __construct(ApiClient $client)
    {
        $this->client = $client;
    }

    public function create(string $userId, string $productId, ?array $metadata = null): array
    {
        $this->validateRequiredParams($userId, $productId);
        
        $data = [
            'user_id' => $userId,
            'product_id' => $productId,
            'metadata' => Utils::sanitizeMetadata($metadata ?? [])
        ];
        
        $response = $this->client->post('/licenses', $data);
        return $response['data'];
    }

    public function get(string $licenseId): array
    {
        $this->validateUuid($licenseId, 'license_id');
        
        $response = $this->client->get("/licenses/{$licenseId}");
        return $response['data'];
    }

    public function update(string $licenseId, array $updates): array
    {
        $this->validateUuid($licenseId, 'license_id');
        
        $response = $this->client->put("/licenses/{$licenseId}", Utils::sanitizeMetadata($updates));
        return $response['data'];
    }

    public function revoke(string $licenseId): bool
    {
        $this->validateUuid($licenseId, 'license_id');
        
        $this->client->delete("/licenses/{$licenseId}");
        return true;
    }

    public function validate(string $licenseKey): bool
    {
        Utils::validateNotEmpty($licenseKey, 'license_key');
        
        $response = $this->client->post('/licenses/validate', ['license_key' => $licenseKey]);
        return $response['valid'] ?? false;
    }

    public function listUserLicenses(string $userId, ?int $page = null, ?int $limit = null): array
    {
        $this->validateUuid($userId, 'user_id');
        [$page, $limit] = Utils::validatePagination($page, $limit);
        
        $response = $this->client->get('/licenses', [
            'user_id' => $userId,
            'page' => $page,
            'limit' => $limit
        ]);
        
        return [
            'data' => $response['data'],
            'total' => $response['total'],
            'page' => $response['page'],
            'limit' => $response['limit']
        ];
    }

    public function stats(): array
    {
        $response = $this->client->get('/licenses/stats');
        return $response['data'];
    }

    private function validateRequiredParams(string $userId, string $productId): void
    {
        Utils::validateNotEmpty($userId, 'user_id');
        Utils::validateNotEmpty($productId, 'product_id');
    }

    private function validateUuid(string $id, string $fieldName): void
    {
        Utils::validateNotEmpty($id, $fieldName);
        if (!Utils::validateUuid($id)) {
            throw new ValidationException("Invalid {$fieldName} format");
        }
    }
}
