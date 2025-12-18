<?php

namespace LicenseChain;

class Configuration
{
    private string $apiKey;
    private string $baseUrl;
    private int $timeout;
    private int $retries;
    private ?\Psr\Log\LoggerInterface $logger;

    public function __construct(
        string $apiKey,
        string $baseUrl = 'https://api.licensechain.app',
        int $timeout = 30,
        int $retries = 3,
        ?\Psr\Log\LoggerInterface $logger = null
    ) {
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;
        $this->retries = $retries;
        $this->logger = $logger;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function getRetries(): int
    {
        return $this->retries;
    }

    public function getLogger(): ?\Psr\Log\LoggerInterface
    {
        return $this->logger;
    }

    public function isValid(): bool
    {
        return !empty($this->apiKey);
    }

    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        return $this;
    }

    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function setRetries(int $retries): self
    {
        $this->retries = $retries;
        return $this;
    }

    public function setLogger(?\Psr\Log\LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'apiKey' => $this->apiKey,
            'baseUrl' => $this->baseUrl,
            'timeout' => $this->timeout,
            'retries' => $this->retries,
            'logger' => $this->logger
        ];
    }

    public static function fromArray(array $config): self
    {
        return new self(
            $config['apiKey'] ?? '',
            $config['baseUrl'] ?? 'https://api.licensechain.app',
            $config['timeout'] ?? 30,
            $config['retries'] ?? 3,
            $config['logger'] ?? null
        );
    }

    public static function fromEnvironment(): self
    {
        return new self(
            $_ENV['LICENSECHAIN_API_KEY'] ?? '',
            $_ENV['LICENSECHAIN_BASE_URL'] ?? 'https://api.licensechain.app',
            (int)($_ENV['LICENSECHAIN_TIMEOUT'] ?? 30),
            (int)($_ENV['LICENSECHAIN_RETRIES'] ?? 3)
        );
    }
}
