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
        throw new ValidationException('Product endpoints are not available in API v1');
    }

    public function get(string $productId): array
    {
        throw new ValidationException('Product endpoints are not available in API v1');
    }

    public function update(string $productId, array $updates): array
    {
        throw new ValidationException('Product endpoints are not available in API v1');
    }

    public function delete(string $productId): bool
    {
        throw new ValidationException('Product endpoints are not available in API v1');
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
            'revenue' => 0
        ];
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
