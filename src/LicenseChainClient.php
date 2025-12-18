<?php

namespace LicenseChain;

use LicenseChain\Services\{
    LicenseService,
    UserService,
    ProductService,
    WebhookService
};
use LicenseChain\Exceptions\ConfigurationException;

class LicenseChainClient
{
    private Configuration $config;
    private ApiClient $apiClient;
    private LicenseService $licenses;
    private UserService $users;
    private ProductService $products;
    private WebhookService $webhooks;

    public function __construct(Configuration $config)
    {
        if (!$config->isValid()) {
            throw new ConfigurationException('API key is required');
        }
        
        $this->config = $config;
        $this->apiClient = new ApiClient($config);
        
        // Initialize services
        $this->licenses = new LicenseService($this->apiClient);
        $this->users = new UserService($this->apiClient);
        $this->products = new ProductService($this->apiClient);
        $this->webhooks = new WebhookService($this->apiClient);
    }

    public function getConfiguration(): Configuration
    {
        return $this->config;
    }

    public function getLicenses(): LicenseService
    {
        return $this->licenses;
    }

    public function getUsers(): UserService
    {
        return $this->users;
    }

    public function getProducts(): ProductService
    {
        return $this->products;
    }

    public function getWebhooks(): WebhookService
    {
        return $this->webhooks;
    }

    public function ping(): array
    {
        return $this->apiClient->ping();
    }

    public function health(): array
    {
        return $this->apiClient->health();
    }

    public static function create(string $apiKey, string $baseUrl = 'https://api.licensechain.app'): self
    {
        $config = new Configuration($apiKey, $baseUrl);
        return new self($config);
    }

    public static function fromEnvironment(): self
    {
        $config = Configuration::fromEnvironment();
        return new self($config);
    }
}