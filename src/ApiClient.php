<?php

namespace LicenseChain;

use LicenseChain\Exceptions\{
    NetworkException,
    ServerException,
    ValidationException,
    AuthenticationException,
    NotFoundException,
    RateLimitException,
    LicenseChainException
};

class ApiClient
{
    private Configuration $config;
    private ?\Psr\Log\LoggerInterface $logger;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
        $this->logger = $config->getLogger();
    }

    public function get(string $endpoint, array $params = []): array
    {
        return $this->makeRequest('GET', $endpoint, null, $params);
    }

    public function post(string $endpoint, ?array $data = null): array
    {
        return $this->makeRequest('POST', $endpoint, $data);
    }

    public function put(string $endpoint, ?array $data = null): array
    {
        return $this->makeRequest('PUT', $endpoint, $data);
    }

    public function delete(string $endpoint, ?array $data = null): array
    {
        return $this->makeRequest('DELETE', $endpoint, $data);
    }

    private function makeRequest(string $method, string $endpoint, ?array $data = null, array $params = []): array
    {
        $url = $this->buildUrl($endpoint, $params);
        $options = $this->buildOptions($method, $data);
        
        return Utils::retryWithBackoff(function () use ($url, $options) {
            return $this->sendRequest($url, $options);
        }, $this->config->getRetries());
    }

    private function buildUrl(string $endpoint, array $params = []): string
    {
        // Ensure endpoint starts with /v1 prefix
        if (!str_starts_with($endpoint, '/v1/')) {
            if (str_starts_with($endpoint, '/')) {
                $endpoint = '/v1' . $endpoint;
            } else {
                $endpoint = '/v1/' . $endpoint;
            }
        }
        
        $url = $this->config->getBaseUrl() . '/' . ltrim($endpoint, '/');
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }

    private function buildOptions(string $method, ?array $data = null): array
    {
        $options = [
            'http' => [
                'method' => $method,
                'header' => [
                    'Authorization: Bearer ' . $this->config->getApiKey(),
                    'Content-Type: application/json',
                    'X-API-Version: 1.0',
                    'X-Platform: php-sdk',
                    'User-Agent: LicenseChain-PHP-SDK/1.0.0'
                ],
                'timeout' => $this->config->getTimeout()
            ]
        ];

        if ($data !== null) {
            $options['http']['content'] = Utils::jsonSerialize($data);
        }

        return $options;
    }

    private function sendRequest(string $url, array $options): array
    {
        $context = stream_context_create($options);
        
        try {
            $response = file_get_contents($url, false, $context);
            
            if ($response === false) {
                throw new NetworkException('Failed to make HTTP request');
            }
            
            $httpCode = $this->getHttpCode($http_response_header ?? []);
            $data = Utils::jsonDeserialize($response);
            
            return $this->handleResponse($httpCode, $data);
            
        } catch (\Exception $e) {
            if ($e instanceof LicenseChainException) {
                throw $e;
            }
            throw new NetworkException('Request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    private function getHttpCode(array $headers): int
    {
        foreach ($headers as $header) {
            if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $header, $matches)) {
                return (int)$matches[1];
            }
        }
        return 200;
    }

    private function handleResponse(int $httpCode, array $data): array
    {
        if ($httpCode >= 200 && $httpCode < 300) {
            return $data;
        }
        
        $errorMessage = $data['error'] ?? $data['message'] ?? 'Unknown error';
        
        switch ($httpCode) {
            case 400:
                throw new ValidationException("Bad Request: {$errorMessage}");
            case 401:
                throw new AuthenticationException("Unauthorized: {$errorMessage}");
            case 403:
                throw new AuthenticationException("Forbidden: {$errorMessage}");
            case 404:
                throw new NotFoundException("Not Found: {$errorMessage}");
            case 429:
                throw new RateLimitException("Rate Limited: {$errorMessage}");
            case 500:
            case 502:
            case 503:
            case 504:
                throw new ServerException("Server Error: {$errorMessage}");
            default:
                throw new LicenseChainException("Unexpected response: {$httpCode} {$errorMessage}");
        }
    }

    public function ping(): array
    {
        return $this->get('/ping');
    }

    public function health(): array
    {
        return $this->get('/health');
    }
}
