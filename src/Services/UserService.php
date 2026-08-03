<?php

namespace LicenseChain\Services;

use LicenseChain\{
    ApiClient,
    Utils,
    Exceptions\ValidationException
};

class UserService
{
    private ApiClient $client;

    public function __construct(ApiClient $client)
    {
        $this->client = $client;
    }

    public function create(string $email, ?string $name = null, ?array $metadata = null): array
    {
        $this->validateEmail($email);
        
        $data = [
            'email' => $email,
            'name' => $name,
            'password' => 'ChangeMe123!',
            'metadata' => Utils::sanitizeMetadata($metadata ?? [])
        ];
        
        $response = $this->client->post('/auth/register', $data);
        return $response['user'] ?? ($response['data'] ?? $response);
    }

    public function get(string $userId): array
    {
        Utils::validateNotEmpty($userId, 'user_id');
        $response = $this->client->get('/auth/me');
        return $response['data'] ?? $response;
    }

    public function update(string $userId, array $updates): array
    {
        throw new ValidationException('User update endpoint is not available in API v1');
    }

    public function delete(string $userId): bool
    {
        throw new ValidationException('User delete endpoint is not available in API v1');
    }

    public function list(?int $page = null, ?int $limit = null): array
    {
        [$page, $limit] = Utils::validatePagination($page, $limit);

        return [
            'data' => [],
            'total' => 0,
            'page' => $page,
            'limit' => $limit
        ];
    }

    public function stats(): array
    {
        return [
            'total' => 0,
            'active' => 0,
            'inactive' => 0
        ];
    }

    private function validateEmail(string $email): void
    {
        Utils::validateNotEmpty($email, 'email');
        if (!Utils::validateEmail($email)) {
            throw new ValidationException('Invalid email format');
        }
    }

    private function validateUuid(string $id, string $fieldName): void
    {
        Utils::validateNotEmpty($id, $fieldName);
        if (!Utils::validateUuid($id)) {
            throw new ValidationException("Invalid {$fieldName} format");
        }
    }
}
