<?php

namespace LicenseChain;

class Utils
{
    /**
     * Validate email format
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate license key format
     */
    public static function validateLicenseKey(string $licenseKey): bool
    {
        return strlen($licenseKey) === 32 && preg_match('/^[A-Z0-9]+$/', $licenseKey);
    }

    /**
     * Validate UUID format
     */
    public static function validateUuid(string $uuid): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid);
    }

    /**
     * Validate amount is positive
     */
    public static function validateAmount($amount): bool
    {
        return is_numeric($amount) && $amount > 0 && is_finite($amount);
    }

    /**
     * Validate currency code
     */
    public static function validateCurrency(string $currency): bool
    {
        $validCurrencies = ['USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'CNY'];
        return in_array(strtoupper($currency), $validCurrencies);
    }

    /**
     * Validate status against allowed values
     */
    public static function validateStatus(string $status, array $allowedStatuses): bool
    {
        return in_array($status, $allowedStatuses);
    }

    /**
     * Sanitize input by escaping HTML characters
     */
    public static function sanitizeInput(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize metadata array
     */
    public static function sanitizeMetadata(array $metadata): array
    {
        $sanitized = [];
        foreach ($metadata as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = self::sanitizeInput($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = self::sanitizeMetadata($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    /**
     * Generate a random license key
     */
    public static function generateLicenseKey(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $result = '';
        for ($i = 0; $i < 32; $i++) {
            $result .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $result;
    }

    /**
     * Generate a random UUID v4
     */
    public static function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Format timestamp to ISO 8601 string
     */
    public static function formatTimestamp(int $timestamp): string
    {
        return date('c', $timestamp);
    }

    /**
     * Parse ISO 8601 timestamp string
     */
    public static function parseTimestamp(string $timestamp): int
    {
        $date = new \DateTime($timestamp);
        return $date->getTimestamp();
    }

    /**
     * Validate pagination parameters
     */
    public static function validatePagination(?int $page, ?int $limit): array
    {
        $page = max($page ?? 1, 1);
        $limit = min(max($limit ?? 10, 1), 100);
        return [$page, $limit];
    }

    /**
     * Validate date range
     */
    public static function validateDateRange(string $startDate, string $endDate): void
    {
        $startTime = self::parseTimestamp($startDate);
        $endTime = self::parseTimestamp($endDate);
        
        if ($startTime > $endTime) {
            throw new ValidationException('Start date must be before or equal to end date');
        }
    }

    /**
     * Create webhook signature
     */
    public static function createWebhookSignature(string $payload, string $secret): string
    {
        return hash_hmac('sha256', $payload, $secret);
    }

    /**
     * Verify webhook signature
     */
    public static function verifyWebhookSignature(string $payload, string $signature, string $secret): bool
    {
        $expectedSignature = self::createWebhookSignature($payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Retry with exponential backoff
     */
    public static function retryWithBackoff(callable $callback, int $maxRetries = 3, float $initialDelay = 1.0)
    {
        $delay = $initialDelay;
        
        for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
            try {
                return $callback();
            } catch (\Exception $e) {
                if ($attempt === $maxRetries) {
                    throw $e;
                }
                
                usleep($delay * 1000000); // Convert to microseconds
                $delay *= 2;
            }
        }
    }

    /**
     * Format bytes to human readable format
     */
    public static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $threshold = 1024;
        
        if ($bytes < $threshold) {
            return $bytes . ' B';
        }
        
        $size = $bytes;
        $unitIndex = 0;
        
        while ($size >= $threshold && $unitIndex < count($units) - 1) {
            $size /= $threshold;
            $unitIndex++;
        }
        
        return round($size, 1) . ' ' . $units[$unitIndex];
    }

    /**
     * Format duration to human readable format
     */
    public static function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . 's';
        } elseif ($seconds < 3600) {
            $minutes = intval($seconds / 60);
            $remainingSeconds = $seconds % 60;
            return $minutes . 'm ' . $remainingSeconds . 's';
        } elseif ($seconds < 86400) {
            $hours = intval($seconds / 3600);
            $minutes = intval(($seconds % 3600) / 60);
            return $hours . 'h ' . $minutes . 'm';
        } else {
            $days = intval($seconds / 86400);
            $hours = intval(($seconds % 86400) / 3600);
            return $days . 'd ' . $hours . 'h';
        }
    }

    /**
     * Capitalize the first letter of a string
     */
    public static function capitalizeFirst(string $text): string
    {
        if (empty($text)) {
            return $text;
        }
        return ucfirst(strtolower($text));
    }

    /**
     * Convert string to snake_case
     */
    public static function toSnakeCase(string $text): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $text));
    }

    /**
     * Convert string to PascalCase
     */
    public static function toPascalCase(string $text): string
    {
        return str_replace('_', '', ucwords($text, '_'));
    }

    /**
     * Truncate string to maximum length
     */
    public static function truncateString(string $text, int $maxLength): string
    {
        if (strlen($text) <= $maxLength) {
            return $text;
        }
        return substr($text, 0, $maxLength - 3) . '...';
    }

    /**
     * Remove special characters from string
     */
    public static function removeSpecialChars(string $text): string
    {
        return preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
    }

    /**
     * Create a slug from string
     */
    public static function slugify(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/\s+/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }

    /**
     * Validate that string is not empty
     */
    public static function validateNotEmpty(string $value, string $fieldName): void
    {
        if (empty(trim($value))) {
            throw new ValidationException("{$fieldName} cannot be empty");
        }
    }

    /**
     * Validate that number is positive
     */
    public static function validatePositive($number, string $fieldName): void
    {
        if (!is_numeric($number) || $number <= 0) {
            throw new ValidationException("{$fieldName} must be positive");
        }
    }

    /**
     * Validate that number is within range
     */
    public static function validateRange($number, $min, $max, string $fieldName): void
    {
        if ($number < $min || $number > $max) {
            throw new ValidationException("{$fieldName} must be between {$min} and {$max}");
        }
    }

    /**
     * Serialize object to JSON string
     */
    public static function jsonSerialize($obj): string
    {
        return json_encode($obj, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Deserialize JSON string to object
     */
    public static function jsonDeserialize(string $json)
    {
        return json_decode($json, true);
    }

    /**
     * Deep merge two arrays
     */
    public static function deepMerge(array $array1, array $array2): array
    {
        $result = $array1;
        
        foreach ($array2 as $key => $value) {
            if (isset($result[$key]) && is_array($result[$key]) && is_array($value)) {
                $result[$key] = self::deepMerge($result[$key], $value);
            } else {
                $result[$key] = $value;
            }
        }
        
        return $result;
    }

    /**
     * Split array into chunks
     */
    public static function chunkArray(array $array, int $chunkSize): array
    {
        return array_chunk($array, $chunkSize);
    }

    /**
     * Flatten nested array
     */
    public static function flattenArray(array $array, string $separator = '.'): array
    {
        $result = [];
        
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $flattened = self::flattenArray($value, $separator);
                foreach ($flattened as $subKey => $subValue) {
                    $result[$key . $separator . $subKey] = $subValue;
                }
            } else {
                $result[$key] = $value;
            }
        }
        
        return $result;
    }

    /**
     * Unflatten array with nested keys
     */
    public static function unflattenArray(array $array, string $separator = '.'): array
    {
        $result = [];
        
        foreach ($array as $key => $value) {
            $keys = explode($separator, $key);
            $current = &$result;
            
            foreach ($keys as $k) {
                if (!isset($current[$k])) {
                    $current[$k] = [];
                }
                $current = &$current[$k];
            }
            
            $current = $value;
        }
        
        return $result;
    }

    /**
     * Generate random string
     */
    public static function generateRandomString(int $length, string $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'): string
    {
        $result = '';
        $charactersLength = strlen($characters);
        
        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[random_int(0, $charactersLength - 1)];
        }
        
        return $result;
    }

    /**
     * Generate random bytes
     */
    public static function generateRandomBytes(int $length): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Hash string with SHA-256
     */
    public static function sha256(string $data): string
    {
        return hash('sha256', $data);
    }

    /**
     * Hash string with SHA-1
     */
    public static function sha1(string $data): string
    {
        return hash('sha1', $data);
    }

    /**
     * Hash string with MD5
     */
    public static function md5(string $data): string
    {
        return hash('md5', $data);
    }

    /**
     * Base64 encode
     */
    public static function base64Encode(string $data): string
    {
        return base64_encode($data);
    }

    /**
     * Base64 decode
     */
    public static function base64Decode(string $data): string
    {
        return base64_decode($data);
    }

    /**
     * URL encode
     */
    public static function urlEncode(string $data): string
    {
        return urlencode($data);
    }

    /**
     * URL decode
     */
    public static function urlDecode(string $data): string
    {
        return urldecode($data);
    }

    /**
     * Check if string is valid JSON
     */
    public static function isValidJson(string $json): bool
    {
        json_decode($json);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Check if string is valid URL
     */
    public static function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Check if string is valid IP address
     */
    public static function isValidIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Check if string is valid email
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Get current timestamp
     */
    public static function getCurrentTimestamp(): int
    {
        return time();
    }

    /**
     * Get current date in ISO format
     */
    public static function getCurrentDate(): string
    {
        return date('c');
    }

    /**
     * Get current date in specified format
     */
    public static function getCurrentDateFormatted(string $format): string
    {
        return date($format);
    }
}
