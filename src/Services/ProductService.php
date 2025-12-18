<?php

namespace LicenseChain\Services;

use LicenseChain\{
    ApiClient,
    Utils,
    Exceptions\ValidationException
};

class ProductService
{
    private ApiClient $client;

    public function __construct(ApiClient $client)
    {
        $this->client = $client;
    }

    public function create(
        string $name,
        ?string $description = null,
        ?float $price = null,
        string $currency = 'USD',
        ?array $metadata = null
    ): array {
        $this->validateRequiredParams($name, $price, $currency);
        
        $data = [
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'currency' => $currency,
            'metadata' => Utils::sanitizeMetadata($metadata ?? [])
        ];
        
        $response = $this->client->post('/products', $data);
        return $response['data'];
    }

    public function get(string $productId): array
    {
        $this->validateUuid($productId, 'product_id');
        
        $response = $this->client->get("/products/{$productId}");
        return $response['data'];
    }

    public function update(string $productId, array $updates): array
    {
        $this->validateUuid($productId, 'product_id');
        
        $response = $this->client->put("/products/{$productId}", Utils::sanitizeMetadata($updates));
        return $response['data'];
    }

    public function delete(string $productId): bool
    {
        $this->validateUuid($productId, 'product_id');
        
        $this->client->delete("/products/{$productId}");
        return true;
    }

    public function list(?int $page = null, ?int $limit = null): array
    {
        [$page, $limit] = Utils::validatePagination($page, $limit);
        
        $response = $this->client->get('/products', [
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
        $response = $this->client->get('/products/stats');
        return $response['data'];
    }

    private function validateRequiredParams(string $name, ?float $price, string $currency): void
    {
        Utils::validateNotEmpty($name, 'name');
        if ($price !== null) {
            Utils::validatePositive($price, 'price');
        }
        if (!Utils::validateCurrency($currency)) {
            throw new ValidationException('Invalid currency');
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
