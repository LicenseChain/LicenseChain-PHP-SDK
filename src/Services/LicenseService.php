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

    public function create(string $appId, string $userEmail, ?array $metadata = null): array
    {
        Utils::validateNotEmpty($appId, 'app_id');
        Utils::validateNotEmpty($userEmail, 'user_email');
        
        $data = [
            'appId' => $appId,
            'plan' => 'FREE',
            'issuedEmail' => $userEmail,
            'metadata' => Utils::sanitizeMetadata($metadata ?? [])
        ];
        
        $response = $this->client->post("/apps/{$appId}/licenses", $data);
        return $this->normalizeLicensePayload($response['data'] ?? $response);
    }

    public function get(string $licenseId): array
    {
        Utils::validateNotEmpty($licenseId, 'license_id');
        
        $response = $this->client->get("/licenses/{$licenseId}");
        return $this->normalizeLicensePayload($response['data'] ?? $response);
    }

    public function update(string $licenseId, array $updates): array
    {
        Utils::validateNotEmpty($licenseId, 'license_id');
        
        $response = $this->client->patch("/licenses/{$licenseId}", Utils::sanitizeMetadata($updates));
        return $this->normalizeLicensePayload($response['data'] ?? $response);
    }

    public function revoke(string $licenseId): bool
    {
        Utils::validateNotEmpty($licenseId, 'license_id');
        
        $this->client->delete("/licenses/{$licenseId}");
        return true;
    }

    public function validate(string $licenseKey, ?string $hwuid = null): bool
    {
        Utils::validateNotEmpty($licenseKey, 'license_key');
        $body = ['key' => $licenseKey];
        if ($hwuid !== null && trim($hwuid) !== '') {
            $body['hwuid'] = trim($hwuid);
        } else {
            $raw = sprintf('licensechain|php|%s|%s|%s', gethostname() ?: 'unknown', php_uname('s'), php_uname('m'));
            $body['hwuid'] = hash('sha256', $raw);
        }
        $response = $this->client->post('/licenses/verify', $body);
        return $response['valid'] ?? false;
    }

    /**
     * Full POST /licenses/verify response (valid, optional license_token, license_jwks_uri, etc.).
     */
    public function verifyWithDetails(string $licenseKey, ?string $hwuid = null): array
    {
        Utils::validateNotEmpty($licenseKey, 'license_key');
        $body = ['key' => $licenseKey];
        if ($hwuid !== null && trim($hwuid) !== '') {
            $body['hwuid'] = trim($hwuid);
        } else {
            $raw = sprintf('licensechain|php|%s|%s|%s', gethostname() ?: 'unknown', php_uname('s'), php_uname('m'));
            $body['hwuid'] = hash('sha256', $raw);
        }
        return $this->client->post('/licenses/verify', $body);
    }

    public function listUserLicenses(string $userId, ?int $page = null, ?int $limit = null): array
    {
        Utils::validateNotEmpty($userId, 'user_id');
        [$page, $limit] = Utils::validatePagination($page, $limit);
        
        $response = $this->client->get('/licenses', [
            'page' => $page,
            'limit' => $limit
        ]);

        $items = $response['data'] ?? $response['licenses'] ?? [];
        $filtered = array_values(array_filter($items, function ($license) use ($userId) {
            return (($license['issuedEmail'] ?? null) === $userId)
                || (($license['email'] ?? null) === $userId)
                || (($license['user_id'] ?? null) === $userId);
        }));
        
        return [
            'data' => array_map(fn($license) => $this->normalizeLicensePayload($license), $filtered),
            'total' => count($filtered),
            'page' => $page,
            'limit' => $limit
        ];
    }

    public function stats(): array
    {
        $response = $this->client->get('/licenses/stats');
        return $response['data'] ?? $response;
    }

    private function validateUuid(string $id, string $fieldName): void
    {
        Utils::validateNotEmpty($id, $fieldName);
        if (!Utils::validateUuid($id)) {
            throw new ValidationException("Invalid {$fieldName} format");
        }
    }

    private function normalizeLicensePayload(array $payload): array
    {
        return [
            'id' => $payload['id'] ?? null,
            'key' => $payload['key'] ?? ($payload['licenseKey'] ?? null),
            'app_id' => $payload['app_id'] ?? ($payload['appId'] ?? ''),
            'user_id' => $payload['user_id'] ?? null,
            'user_email' => $payload['user_email'] ?? ($payload['issuedEmail'] ?? ($payload['email'] ?? '')),
            'user_name' => $payload['user_name'] ?? ($payload['issuedTo'] ?? null),
            'status' => strtolower((string)($payload['status'] ?? 'active')),
            'expires_at' => $payload['expires_at'] ?? ($payload['expiresAt'] ?? null),
            'created_at' => $payload['created_at'] ?? ($payload['createdAt'] ?? null),
            'updated_at' => $payload['updated_at'] ?? ($payload['updatedAt'] ?? null),
            'metadata' => $payload['metadata'] ?? [],
            'features' => $payload['features'] ?? [],
            'usage_count' => $payload['usage_count'] ?? 0,
        ];
    }
}
