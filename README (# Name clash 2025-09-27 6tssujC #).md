# LicenseChain PHP SDK

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.0+-blue.svg)](https://www.php.net/)
[![Packagist](https://img.shields.io/packagist/v/licensechain/sdk)](https://packagist.org/packages/licensechain/sdk)
[![Composer](https://img.shields.io/badge/Composer-2.0+-green.svg)](https://getcomposer.org/)

Official PHP SDK for LicenseChain - Secure license management for PHP applications.

## 🚀 Features

- **🔐 Secure Authentication** - User registration, login, and session management
- **📜 License Management** - Create, validate, update, and revoke licenses
- **🛡️ Hardware ID Validation** - Prevent license sharing and unauthorized access
- **🔔 Webhook Support** - Real-time license events and notifications
- **📊 Analytics Integration** - Track license usage and performance metrics
- **⚡ High Performance** - Optimized for production workloads
- **🔄 Async Operations** - Non-blocking HTTP requests and data processing
- **🛠️ Easy Integration** - Simple API with comprehensive documentation

## 📦 Installation

### Method 1: Composer (Recommended)

```bash
# Install via Composer
composer require licensechain/sdk

# Or add to composer.json
composer require licensechain/sdk:^1.0
```

### Method 2: Manual Installation

1. Download the latest release from [GitHub Releases](https://github.com/LicenseChain/LicenseChain-PHP-SDK/releases)
2. Extract to your project directory
3. Include the autoloader

### Method 3: Git Repository

```bash
# Add to composer.json
{
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/LicenseChain/LicenseChain-PHP-SDK.git"
        }
    ],
    "require": {
        "licensechain/sdk": "dev-main"
    }
}
```

## 🚀 Quick Start

### Basic Setup

```php
<?php
require_once 'vendor/autoload.php';

use LicenseChain\SDK\LicenseChainClient;
use LicenseChain\SDK\LicenseChainConfig;

// Initialize the client
$config = new LicenseChainConfig([
    'apiKey' => 'your-api-key',
    'appName' => 'your-app-name',
    'version' => '1.0.0'
]);

$client = new LicenseChainClient($config);

// Connect to LicenseChain
try {
    $client->connect();
    echo "Connected to LicenseChain successfully!\n";
} catch (Exception $e) {
    echo "Failed to connect: " . $e->getMessage() . "\n";
}
?>
```

### User Authentication

```php
// Register a new user
try {
    $user = $client->register('username', 'password', 'email@example.com');
    echo "User registered successfully!\n";
    echo "User ID: " . $user->getId() . "\n";
} catch (Exception $e) {
    echo "Registration failed: " . $e->getMessage() . "\n";
}

// Login existing user
try {
    $user = $client->login('username', 'password');
    echo "User logged in successfully!\n";
    echo "Session ID: " . $user->getSessionId() . "\n";
} catch (Exception $e) {
    echo "Login failed: " . $e->getMessage() . "\n";
}
```

### License Management

```php
// Validate a license
try {
    $license = $client->validateLicense('LICENSE-KEY-HERE');
    echo "License is valid!\n";
    echo "License Key: " . $license->getKey() . "\n";
    echo "Status: " . $license->getStatus() . "\n";
    echo "Expires: " . $license->getExpires() . "\n";
    echo "Features: " . implode(', ', $license->getFeatures()) . "\n";
    echo "User: " . $license->getUser() . "\n";
} catch (Exception $e) {
    echo "License validation failed: " . $e->getMessage() . "\n";
}

// Get user's licenses
try {
    $licenses = $client->getUserLicenses();
    echo "Found " . count($licenses) . " licenses:\n";
    foreach ($licenses as $index => $license) {
        echo "  " . ($index + 1) . ". " . $license->getKey() 
             . " - " . $license->getStatus() 
             . " (Expires: " . $license->getExpires() . ")\n";
    }
} catch (Exception $e) {
    echo "Failed to get licenses: " . $e->getMessage() . "\n";
}
```

### Hardware ID Validation

```php
// Get hardware ID (automatically generated)
$hardwareId = $client->getHardwareId();
echo "Hardware ID: " . $hardwareId . "\n";

// Validate hardware ID with license
try {
    $isValid = $client->validateHardwareId('LICENSE-KEY-HERE', $hardwareId);
    if ($isValid) {
        echo "Hardware ID is valid for this license!\n";
    } else {
        echo "Hardware ID is not valid for this license.\n";
    }
} catch (Exception $e) {
    echo "Hardware ID validation failed: " . $e->getMessage() . "\n";
}
```

### Webhook Integration

```php
// Set up webhook handler
$client->setWebhookHandler(function($event, $data) {
    echo "Webhook received: " . $event . "\n";
    
    switch ($event) {
        case 'license.created':
            echo "New license created: " . $data['licenseKey'] . "\n";
            break;
        case 'license.updated':
            echo "License updated: " . $data['licenseKey'] . "\n";
            break;
        case 'license.revoked':
            echo "License revoked: " . $data['licenseKey'] . "\n";
            break;
    }
});

// Start webhook listener
$client->startWebhookListener();
```

## 🌐 API Endpoints

This SDK connects to the LicenseChain API at `https://api.licensechain.app` using the `/v1` API version prefix.

### Base URL
```
https://api.licensechain.app
```

### Available Endpoints

All endpoints use the `/v1` prefix:

- **Health Check**: `GET /v1/health`
- **Authentication**:
  - `POST /v1/auth/register` - Register new user
  - `POST /v1/auth/login` - User login
  - `GET /v1/auth/me` - Get current user
  - `POST /v1/auth/logout` - User logout
- **Applications**:
  - `GET /v1/apps` - List all apps
  - `GET /v1/apps/:id` - Get app by ID
  - `POST /v1/apps` - Create new app
  - `PUT /v1/apps/:id` - Update app
  - `DELETE /v1/apps/:id` - Delete app
- **Licenses**:
  - `GET /v1/licenses` - List licenses
  - `GET /v1/licenses/:id` - Get license by ID
  - `POST /v1/licenses` - Create license
  - `POST /v1/licenses/verify` - Verify license key
  - `PUT /v1/licenses/:id` - Update license
  - `DELETE /v1/licenses/:id` - Delete license
- **Webhooks**:
  - `GET /v1/webhooks` - List webhooks
  - `POST /v1/webhooks` - Create webhook
  - `GET /v1/webhooks/:id` - Get webhook by ID
  - `PUT /v1/webhooks/:id` - Update webhook
  - `DELETE /v1/webhooks/:id` - Delete webhook
- **Analytics**:
  - `GET /v1/analytics` - Get analytics data

> **Note**: The SDK automatically prepends `/v1` to all endpoint calls. You don't need to include it manually.

## 📚 API Reference

### LicenseChainClient

#### Constructor

```php
$config = new LicenseChainConfig([
    'apiKey' => 'your-api-key',
    'appName' => 'your-app-name',
    'version' => '1.0.0',
    'baseUrl' => 'https://api.licensechain.app' // Optional
]);

$client = new LicenseChainClient($config);
```

#### Methods

##### Connection Management

```php
// Connect to LicenseChain
$client->connect();

// Disconnect from LicenseChain
$client->disconnect();

// Check connection status
$isConnected = $client->isConnected();
```

##### User Authentication

```php
// Register a new user
$user = $client->register($username, $password, $email);

// Login existing user
$user = $client->login($username, $password);

// Logout current user
$client->logout();

// Get current user info
$user = $client->getCurrentUser();
```

##### License Management

```php
// Validate a license
$license = $client->validateLicense($licenseKey);

// Get user's licenses
$licenses = $client->getUserLicenses();

// Create a new license
$license = $client->createLicense($userId, $features, $expires);

// Update a license
$license = $client->updateLicense($licenseKey, $updates);

// Revoke a license
$client->revokeLicense($licenseKey);

// Extend a license
$license = $client->extendLicense($licenseKey, $days);
```

##### Hardware ID Management

```php
// Get hardware ID
$hardwareId = $client->getHardwareId();

// Validate hardware ID
$isValid = $client->validateHardwareId($licenseKey, $hardwareId);

// Bind hardware ID to license
$client->bindHardwareId($licenseKey, $hardwareId);
```

##### Webhook Management

```php
// Set webhook handler
$client->setWebhookHandler($handler);

// Start webhook listener
$client->startWebhookListener();

// Stop webhook listener
$client->stopWebhookListener();
```

##### Analytics

```php
// Track event
$client->trackEvent($eventName, $properties);

// Get analytics data
$analytics = $client->getAnalytics($timeRange);
```

## 🔧 Configuration

### Environment Variables

Set these in your environment or through your build process:

```bash
# Required
export LICENSECHAIN_API_KEY=your-api-key
export LICENSECHAIN_APP_NAME=your-app-name
export LICENSECHAIN_APP_VERSION=1.0.0

# Optional
export LICENSECHAIN_BASE_URL=https://api.licensechain.app
export LICENSECHAIN_DEBUG=true
```

### Advanced Configuration

```php
$config = new LicenseChainConfig([
    'apiKey' => 'your-api-key',
    'appName' => 'your-app-name',
    'version' => '1.0.0',
    'baseUrl' => 'https://api.licensechain.app',
    'timeout' => 30,        // Request timeout in seconds
    'retries' => 3,         // Number of retry attempts
    'debug' => false,       // Enable debug logging
    'userAgent' => 'MyApp/1.0.0' // Custom user agent
]);
```

## 🛡️ Security Features

### Hardware ID Protection

The SDK automatically generates and manages hardware IDs to prevent license sharing:

```php
// Hardware ID is automatically generated and stored
$hardwareId = $client->getHardwareId();

// Validate against license
$isValid = $client->validateHardwareId($licenseKey, $hardwareId);
```

### Secure Communication

- All API requests use HTTPS
- API keys are securely stored and transmitted
- Session tokens are automatically managed
- Webhook signatures are verified

### License Validation

- Real-time license validation
- Hardware ID binding
- Expiration checking
- Feature-based access control

## 📊 Analytics and Monitoring

### Event Tracking

```php
// Track custom events
$client->trackEvent('app.started', [
    'level' => 1,
    'playerCount' => 10
]);

// Track license events
$client->trackEvent('license.validated', [
    'licenseKey' => 'LICENSE-KEY',
    'features' => 'premium,unlimited'
]);
```

### Performance Monitoring

```php
// Get performance metrics
$metrics = $client->getPerformanceMetrics();
echo "API Response Time: " . $metrics->getAverageResponseTime() . "ms\n";
echo "Success Rate: " . number_format($metrics->getSuccessRate() * 100, 2) . "%\n";
echo "Error Count: " . $metrics->getErrorCount() . "\n";
```

## 🔄 Error Handling

### Custom Exception Types

```php
try {
    $license = $client->validateLicense('invalid-key');
} catch (InvalidLicenseException $e) {
    echo "License key is invalid\n";
} catch (ExpiredLicenseException $e) {
    echo "License has expired\n";
} catch (NetworkException $e) {
    echo "Network connection failed\n";
} catch (LicenseChainException $e) {
    echo "LicenseChain error: " . $e->getMessage() . "\n";
}
```

### Retry Logic

```php
// Automatic retry for network errors
$config = new LicenseChainConfig([
    'apiKey' => 'your-api-key',
    'appName' => 'your-app-name',
    'version' => '1.0.0',
    'retries' => 3,        // Retry up to 3 times
    'timeout' => 30        // Wait 30 seconds for each request
]);
```

## 🧪 Testing

### Unit Tests

```bash
# Run tests
composer test

# Run tests with coverage
composer test:coverage

# Run specific test
vendor/bin/phpunit tests/ClientTest.php
```

### Integration Tests

```bash
# Test with real API
composer test:integration
```

## 📝 Examples

See the `examples/` directory for complete examples:

- `basic_usage.php` - Basic SDK usage
- `advanced_features.php` - Advanced features and configuration
- `webhook_integration.php` - Webhook handling

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Setup

1. Clone the repository
2. Install PHP 8.0 or later
3. Install Composer 2.0 or later
4. Install dependencies: `composer install`
5. Build: `composer build`
6. Test: `composer test`

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- **Documentation**: [https://docs.licensechain.app/php](https://docs.licensechain.app/php)
- **Issues**: [GitHub Issues](https://github.com/LicenseChain/LicenseChain-PHP-SDK/issues)
- **Discord**: [LicenseChain Discord](https://discord.gg/licensechain)
- **Email**: support@licensechain.app

## 🔗 Related Projects

- [LicenseChain JavaScript SDK](https://github.com/LicenseChain/LicenseChain-JavaScript-SDK)
- [LicenseChain Python SDK](https://github.com/LicenseChain/LicenseChain-Python-SDK)
- [LicenseChain Node.js SDK](https://github.com/LicenseChain/LicenseChain-NodeJS-SDK)
- [LicenseChain Customer Panel](https://github.com/LicenseChain/LicenseChain-Customer-Panel)

---

**Made with ❤️ for the PHP community**
