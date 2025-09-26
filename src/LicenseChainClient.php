<?php

namespace LicenseChain;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use LicenseChain\Exceptions\LicenseChainException;
use LicenseChain\Exceptions\AuthenticationException;
use LicenseChain\Exceptions\ValidationException;
use LicenseChain\Exceptions\NotFoundException;
use LicenseChain\Exceptions\RateLimitException;
use LicenseChain\Exceptions\ServerException;
use LicenseChain\Exceptions\NetworkException;

/**
 * LicenseChain PHP SDK Client
 * 
 * Main client for interacting with the LicenseChain API.
 */
class LicenseChainClient
{
    private Client $httpClient;
    private array $config;

    public function __construct(array $config)
    {
        if (empty($config['api_key'])) {
            throw new LicenseChainException('API key is required');
        }

        $this->config = array_merge([
            'base_url' => 'https://api.licensechain.app',
            'timeout' => 30,
            'retry_attempts' => 3,
            'retry_delay' => 1.0,
        ], $config);

        $this->httpClient = new Client([
            'base_uri' => $this->config['base_url'],
            'timeout' => $this->config['timeout'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->config['api_key'],
                'User-Agent' => 'LicenseChain-PHP-SDK/1.0.0',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    // Authentication Methods

    /**
     * Register a new user
     */
    public function registerUser(array $userData): array
    {
        return $this->post('/auth/register', $userData);
    }

    /**
     * Login with email and password
     */
    public function login(array $credentials): array
    {
        return $this->post('/auth/login', $credentials);
    }

    /**
     * Logout the current user
     */
    public function logout(): void
    {
        $this->post('/auth/logout');
    }

    /**
     * Refresh authentication token
     */
    public function refreshToken(string $refreshToken): array
    {
        return $this->post('/auth/refresh', ['refresh_token' => $refreshToken]);
    }

    /**
     * Get current user profile
     */
    public function getUserProfile(): array
    {
        return $this->get('/auth/me');
    }

    /**
     * Update user profile
     */
    public function updateUserProfile(array $attributes): array
    {
        return $this->patch('/auth/me', $attributes);
    }

    /**
     * Change user password
     */
    public function changePassword(array $data): void
    {
        $this->patch('/auth/password', $data);
    }

    /**
     * Request password reset
     */
    public function requestPasswordReset(string $email): void
    {
        $this->post('/auth/forgot-password', ['email' => $email]);
    }

    /**
     * Reset password with token
     */
    public function resetPassword(array $data): void
    {
        $this->post('/auth/reset-password', $data);
    }

    // Application Management

    /**
     * Create a new application
     */
    public function createApplication(array $data): array
    {
        return $this->post('/apps', $data);
    }

    /**
     * List applications with pagination
     */
    public function listApplications(array $options = []): array
    {
        $params = $this->buildListParams($options);
        return $this->get('/apps', $params);
    }

    /**
     * Get application details
     */
    public function getApplication(string $appId): array
    {
        return $this->get("/apps/{$appId}");
    }

    /**
     * Update application
     */
    public function updateApplication(string $appId, array $attributes): array
    {
        return $this->patch("/apps/{$appId}", $attributes);
    }

    /**
     * Delete application
     */
    public function deleteApplication(string $appId): void
    {
        $this->delete("/apps/{$appId}");
    }

    /**
     * Regenerate API key for application
     */
    public function regenerateApiKey(string $appId): array
    {
        return $this->post("/apps/{$appId}/regenerate-key");
    }

    // License Management

    /**
     * Create a new license
     */
    public function createLicense(array $data): array
    {
        return $this->post('/licenses', $data);
    }

    /**
     * List licenses with filters
     */
    public function listLicenses(array $options = []): array
    {
        $params = $this->buildListParams($options);
        return $this->get('/licenses', $params);
    }

    /**
     * Get license details
     */
    public function getLicense(string $licenseId): array
    {
        return $this->get("/licenses/{$licenseId}");
    }

    /**
     * Update license
     */
    public function updateLicense(string $licenseId, array $attributes): array
    {
        return $this->patch("/licenses/{$licenseId}", $attributes);
    }

    /**
     * Delete license
     */
    public function deleteLicense(string $licenseId): void
    {
        $this->delete("/licenses/{$licenseId}");
    }

    /**
     * Validate a license key
     */
    public function validateLicense(string $licenseKey, ?string $appId = null): array
    {
        $data = ['license_key' => $licenseKey];
        if ($appId) {
            $data['app_id'] = $appId;
        }
        return $this->post('/licenses/validate', $data);
    }

    /**
     * Revoke a license
     */
    public function revokeLicense(string $licenseId, ?string $reason = null): void
    {
        $data = [];
        if ($reason) {
            $data['reason'] = $reason;
        }
        $this->patch("/licenses/{$licenseId}/revoke", $data);
    }

    /**
     * Activate a license
     */
    public function activateLicense(string $licenseId): void
    {
        $this->patch("/licenses/{$licenseId}/activate");
    }

    /**
     * Extend license expiration
     */
    public function extendLicense(string $licenseId, string $expiresAt): void
    {
        $this->patch("/licenses/{$licenseId}/extend", ['expires_at' => $expiresAt]);
    }

    // Webhook Management

    /**
     * Create a webhook
     */
    public function createWebhook(array $data): array
    {
        return $this->post('/webhooks', $data);
    }

    /**
     * List webhooks
     */
    public function listWebhooks(array $options = []): array
    {
        $params = $this->buildListParams($options);
        return $this->get('/webhooks', $params);
    }

    /**
     * Get webhook details
     */
    public function getWebhook(string $webhookId): array
    {
        return $this->get("/webhooks/{$webhookId}");
    }

    /**
     * Update webhook
     */
    public function updateWebhook(string $webhookId, array $attributes): array
    {
        return $this->patch("/webhooks/{$webhookId}", $attributes);
    }

    /**
     * Delete webhook
     */
    public function deleteWebhook(string $webhookId): void
    {
        $this->delete("/webhooks/{$webhookId}");
    }

    /**
     * Test webhook
     */
    public function testWebhook(string $webhookId): void
    {
        $this->post("/webhooks/{$webhookId}/test");
    }

    // Analytics

    /**
     * Get analytics data
     */
    public function getAnalytics(array $options = []): array
    {
        $params = $this->buildAnalyticsParams($options);
        return $this->get('/analytics', $params);
    }

    /**
     * Get license analytics
     */
    public function getLicenseAnalytics(string $licenseId): array
    {
        return $this->get("/licenses/{$licenseId}/analytics");
    }

    /**
     * Get usage statistics
     */
    public function getUsageStats(array $options = []): array
    {
        $params = $this->buildUsageStatsParams($options);
        return $this->get('/analytics/usage', $params);
    }

    // System Status

    /**
     * Get system status
     */
    public function getSystemStatus(): array
    {
        return $this->get('/status');
    }

    /**
     * Get health check
     */
    public function getHealthCheck(): array
    {
        return $this->get('/health');
    }

    // HTTP Methods

    /**
     * Make a GET request
     */
    public function get(string $path, array $params = []): array
    {
        $options = [];
        if (!empty($params)) {
            $options['query'] = $params;
        }

        return $this->makeRequest('GET', $path, $options);
    }

    /**
     * Make a POST request
     */
    public function post(string $path, array $data = []): array
    {
        $options = [];
        if (!empty($data)) {
            $options['json'] = $data;
        }

        return $this->makeRequest('POST', $path, $options);
    }

    /**
     * Make a PATCH request
     */
    public function patch(string $path, array $data = []): array
    {
        $options = [];
        if (!empty($data)) {
            $options['json'] = $data;
        }

        return $this->makeRequest('PATCH', $path, $options);
    }

    /**
     * Make a DELETE request
     */
    public function delete(string $path): array
    {
        return $this->makeRequest('DELETE', $path);
    }

    /**
     * Make HTTP request with retry logic
     */
    private function makeRequest(string $method, string $path, array $options = []): array
    {
        $attempts = 0;
        $maxAttempts = $this->config['retry_attempts'];

        while ($attempts < $maxAttempts) {
            try {
                $response = $this->httpClient->request($method, $path, $options);
                $body = $response->getBody()->getContents();
                return json_decode($body, true) ?? [];
            } catch (RequestException $e) {
                $attempts++;
                
                if ($e->hasResponse()) {
                    $statusCode = $e->getResponse()->getStatusCode();
                    $body = $e->getResponse()->getBody()->getContents();
                    $data = json_decode($body, true) ?? [];
                    
                    $errorMessage = $data['error'] ?? $e->getMessage();
                    
                    // Don't retry on client errors (4xx)
                    if ($statusCode >= 400 && $statusCode < 500) {
                        throw $this->createExceptionFromStatusCode($statusCode, $errorMessage, $data);
                    }
                    
                    // Retry on server errors (5xx) and rate limits
                    if ($attempts < $maxAttempts && ($statusCode >= 500 || $statusCode === 429)) {
                        sleep($this->config['retry_delay'] * pow(2, $attempts - 1));
                        continue;
                    }
                    
                    throw $this->createExceptionFromStatusCode($statusCode, $errorMessage, $data);
                } else {
                    // Network error
                    if ($attempts < $maxAttempts) {
                        sleep($this->config['retry_delay'] * pow(2, $attempts - 1));
                        continue;
                    }
                    
                    throw new NetworkException($e->getMessage());
                }
            } catch (GuzzleException $e) {
                throw new NetworkException($e->getMessage());
            }
        }

        throw new NetworkException('Maximum retry attempts exceeded');
    }

    /**
     * Create appropriate exception from HTTP status code
     */
    private function createExceptionFromStatusCode(int $statusCode, string $message, array $data = []): LicenseChainException
    {
        switch ($statusCode) {
            case 400:
                return new ValidationException($message, $data['code'] ?? null, $statusCode, $data);
            case 401:
            case 403:
                return new AuthenticationException($message, $data['code'] ?? null, $statusCode, $data);
            case 404:
                return new NotFoundException($message, $data['code'] ?? null, $statusCode, $data);
            case 429:
                return new RateLimitException(
                    $message,
                    $data['code'] ?? null,
                    $statusCode,
                    $data,
                    $data['retry_after'] ?? null,
                    $data['limit'] ?? null,
                    $data['remaining'] ?? null,
                    $data['reset'] ?? null
                );
            case 500:
            case 502:
            case 503:
            case 504:
                return new ServerException($message, $data['code'] ?? null, $statusCode, $data);
            default:
                if ($statusCode >= 400 && $statusCode < 500) {
                    return new ValidationException($message, $data['code'] ?? null, $statusCode, $data);
                } elseif ($statusCode >= 500) {
                    return new ServerException($message, $data['code'] ?? null, $statusCode, $data);
                } else {
                    return new LicenseChainException($message, $data['code'] ?? null, $statusCode, $data);
                }
        }
    }

    // Utility Methods

    /**
     * Build list parameters
     */
    private function buildListParams(array $options): array
    {
        $params = [
            'page' => $options['page'] ?? 1,
            'limit' => $options['limit'] ?? 20,
        ];

        if (isset($options['sort_by'])) {
            $params['sort_by'] = $options['sort_by'];
        }

        if (isset($options['sort_order'])) {
            $params['sort_order'] = $options['sort_order'];
        }

        if (isset($options['filter'])) {
            $params = array_merge($params, $options['filter']);
        }

        return $params;
    }

    /**
     * Build analytics parameters
     */
    private function buildAnalyticsParams(array $options): array
    {
        $params = [];

        if (isset($options['app_id'])) {
            $params['app_id'] = $options['app_id'];
        }

        if (isset($options['start_date'])) {
            $params['start_date'] = $options['start_date'];
        }

        if (isset($options['end_date'])) {
            $params['end_date'] = $options['end_date'];
        }

        if (isset($options['metric'])) {
            $params['metric'] = $options['metric'];
        }

        if (isset($options['period'])) {
            $params['period'] = $options['period'];
        }

        return $params;
    }

    /**
     * Build usage stats parameters
     */
    private function buildUsageStatsParams(array $options): array
    {
        $params = [
            'period' => $options['period'] ?? '30d',
        ];

        if (isset($options['app_id'])) {
            $params['app_id'] = $options['app_id'];
        }

        if (isset($options['granularity'])) {
            $params['granularity'] = $options['granularity'];
        }

        return $params;
    }

    /**
     * Update configuration
     */
    public function updateConfig(array $newConfig): void
    {
        $this->config = array_merge($this->config, $newConfig);
        
        if (isset($newConfig['api_key'])) {
            $this->httpClient = new Client([
                'base_uri' => $this->config['base_url'],
                'timeout' => $this->config['timeout'],
                'headers' => [
                    'Authorization' => 'Bearer ' . $newConfig['api_key'],
                    'User-Agent' => 'LicenseChain-PHP-SDK/1.0.0',
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);
        }
    }

    /**
     * Get current configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}
