<?php

namespace LicenseChain;

use LicenseChain\Exceptions\LicenseChainException;

/**
 * License validator for easy license validation
 */
class LicenseValidator
{
    private LicenseChainClient $client;

    public function __construct(array $config)
    {
        $this->client = new LicenseChainClient($config);
    }

    /**
     * Validate a license key
     */
    public function validateLicense(string $licenseKey, ?string $appId = null): array
    {
        try {
            return $this->client->validateLicense($licenseKey, $appId);
        } catch (LicenseChainException $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check if license is valid (quick check)
     */
    public function isValid(string $licenseKey, ?string $appId = null): bool
    {
        try {
            $result = $this->validateLicense($licenseKey, $appId);
            return $result['valid'] ?? false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get license information
     */
    public function getLicenseInfo(string $licenseKey, ?string $appId = null): ?array
    {
        try {
            $result = $this->validateLicense($licenseKey, $appId);
            return $result['valid'] ? $result : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if license is expired
     */
    public function isExpired(string $licenseKey, ?string $appId = null): bool
    {
        try {
            $result = $this->validateLicense($licenseKey, $appId);
            if (!$result['valid'] || !isset($result['license'])) {
                return true;
            }

            $expiresAt = $result['license']['expires_at'] ?? null;
            if (!$expiresAt) {
                return false;
            }

            return strtotime($expiresAt) < time();
        } catch (\Exception $e) {
            return true;
        }
    }

    /**
     * Get days until expiration
     */
    public function getDaysUntilExpiration(string $licenseKey, ?string $appId = null): ?int
    {
        try {
            $result = $this->validateLicense($licenseKey, $appId);
            if (!$result['valid'] || !isset($result['license'])) {
                return null;
            }

            $expiresAt = $result['license']['expires_at'] ?? null;
            if (!$expiresAt) {
                return null;
            }

            $expDate = strtotime($expiresAt);
            $now = time();
            $diffDays = ceil(($expDate - $now) / (24 * 60 * 60));

            return max(0, $diffDays);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Validate multiple licenses
     */
    public function validateLicenses(array $licenseKeys, ?string $appId = null): array
    {
        $results = [];
        foreach ($licenseKeys as $key) {
            $results[] = $this->validateLicense($key, $appId);
        }
        return $results;
    }

    /**
     * Validate with custom validation rules
     */
    public function validateWithRules(string $licenseKey, array $rules, ?string $appId = null): array
    {
        $result = $this->validateLicense($licenseKey, $appId);

        if (!$result['valid'] || empty($rules)) {
            return $result;
        }

        // Apply custom validation rules
        if (isset($rules['max_usage']) && isset($result['license']['usage_count'])) {
            if ($result['license']['usage_count'] > $rules['max_usage']) {
                return array_merge($result, [
                    'valid' => false,
                    'error' => 'Usage limit exceeded',
                ]);
            }
        }

        if (isset($rules['allowed_features']) && isset($result['license']['features'])) {
            $invalidFeatures = array_diff(
                $result['license']['features'],
                $rules['allowed_features']
            );
            if (!empty($invalidFeatures)) {
                return array_merge($result, [
                    'valid' => false,
                    'error' => 'Invalid features: ' . implode(', ', $invalidFeatures),
                ]);
            }
        }

        if (isset($rules['required_features']) && isset($result['license']['features'])) {
            $missingFeatures = array_diff(
                $rules['required_features'],
                $result['license']['features']
            );
            if (!empty($missingFeatures)) {
                return array_merge($result, [
                    'valid' => false,
                    'error' => 'Missing required features: ' . implode(', ', $missingFeatures),
                ]);
            }
        }

        if (isset($rules['allowed_domains']) && isset($result['license']['metadata']['domain'])) {
            $domain = $result['license']['metadata']['domain'];
            if (!in_array($domain, $rules['allowed_domains'])) {
                return array_merge($result, [
                    'valid' => false,
                    'error' => "Domain not allowed: {$domain}",
                ]);
            }
        }

        if (isset($rules['allowed_ips']) && isset($result['license']['metadata']['ip_address'])) {
            $ipAddress = $result['license']['metadata']['ip_address'];
            if (!in_array($ipAddress, $rules['allowed_ips'])) {
                return array_merge($result, [
                    'valid' => false,
                    'error' => "IP address not allowed: {$ipAddress}",
                ]);
            }
        }

        return $result;
    }

    /**
     * Check if license has specific feature
     */
    public function hasFeature(string $licenseKey, string $feature, ?string $appId = null): bool
    {
        try {
            $result = $this->validateLicense($licenseKey, $appId);
            return $result['valid'] && 
                   isset($result['license']['features']) && 
                   in_array($feature, $result['license']['features']);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get license usage count
     */
    public function getUsageCount(string $licenseKey, ?string $appId = null): ?int
    {
        try {
            $result = $this->validateLicense($licenseKey, $appId);
            return $result['valid'] ? ($result['license']['usage_count'] ?? 0) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get license metadata
     */
    public function getMetadata(string $licenseKey, ?string $appId = null): ?array
    {
        try {
            $result = $this->validateLicense($licenseKey, $appId);
            return $result['valid'] ? ($result['license']['metadata'] ?? []) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get license features
     */
    public function getFeatures(string $licenseKey, ?string $appId = null): array
    {
        try {
            $result = $this->validateLicense($licenseKey, $appId);
            return $result['valid'] ? ($result['license']['features'] ?? []) : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get user information from license
     */
    public function getUserInfo(string $licenseKey, ?string $appId = null): ?array
    {
        try {
            $result = $this->validateLicense($licenseKey, $appId);
            if (!$result['valid']) {
                return null;
            }

            return [
                'email' => $result['user']['email'] ?? null,
                'name' => $result['user']['name'] ?? null,
                'id' => $result['user']['id'] ?? null,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get application information from license
     */
    public function getAppInfo(string $licenseKey, ?string $appId = null): ?array
    {
        try {
            $result = $this->validateLicense($licenseKey, $appId);
            if (!$result['valid']) {
                return null;
            }

            return [
                'name' => $result['app']['name'] ?? null,
                'id' => $result['app']['id'] ?? null,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get client instance for advanced operations
     */
    public function getClient(): LicenseChainClient
    {
        return $this->client;
    }

    /**
     * Update configuration
     */
    public function updateConfig(array $config): void
    {
        $this->client->updateConfig($config);
    }
}
