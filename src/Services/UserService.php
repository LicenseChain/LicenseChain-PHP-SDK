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
            'metadata' => Utils::sanitizeMetadata($metadata ?? [])
        ];
        
        $response = $this->client->post('/users', $data);
        return $response['data'];
    }

    public function get(string $userId): array
    {
        $this->validateUuid($userId, 'user_id');
        
        $response = $this->client->get("/users/{$userId}");
        return $response['data'];
    }

    public function update(string $userId, array $updates): array
    {
        $this->validateUuid($userId, 'user_id');
        
        $response = $this->client->put("/users/{$userId}", Utils::sanitizeMetadata($updates));
        return $response['data'];
    }

    public function delete(string $userId): bool
    {
        $this->validateUuid($userId, 'user_id');
        
        $this->client->delete("/users/{$userId}");
        return true;
    }

    public function list(?int $page = null, ?int $limit = null): array
    {
        [$page, $limit] = Utils::validatePagination($page, $limit);
        
        $response = $this->client->get('/users', [
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
        $response = $this->client->get('/users/stats');
        return $response['data'];
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
