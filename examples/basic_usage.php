<?php

require_once __DIR__ . '/../vendor/autoload.php';

use LicenseChain\{
    LicenseChainClient,
    Configuration,
    WebhookHandler,
    WebhookEvents,
    Utils
};

// Configure the SDK
$config = new Configuration(
    'your-api-key-here',
    'https://api.licensechain.app',
    30,
    3
);

// Initialize the client
$client = new LicenseChainClient($config);

echo "🚀 LicenseChain PHP SDK - Basic Usage Example\n\n";

try {
    // 1. License Management
    echo "🔑 License Management:\n";
    
    // Create a license
    $metadata = [
        'platform' => 'php',
        'version' => '1.0.0',
        'features' => ['validation', 'webhooks']
    ];
    
    $license = $client->getLicenses()->create('user123', 'product456', $metadata);
    echo "✅ License created: {$license['id']}\n";
    echo "   License Key: {$license['license_key']}\n";
    echo "   Status: {$license['status']}\n";
    
    // Validate a license
    $licenseKey = Utils::generateLicenseKey();
    echo "\n🔍 Validating license key: {$licenseKey}\n";
    
    $isValid = $client->getLicenses()->validate($licenseKey);
    if ($isValid) {
        echo "✅ License is valid\n";
    } else {
        echo "❌ License is invalid\n";
    }
    
    // Get license stats
    $stats = $client->getLicenses()->stats();
    echo "\n📊 License Statistics:\n";
    echo "   Total: {$stats['total']}\n";
    echo "   Active: {$stats['active']}\n";
    echo "   Expired: {$stats['expired']}\n";
    echo "   Revenue: \${$stats['revenue']}\n";
    
    // 2. User Management
    echo "\n👤 User Management:\n";
    
    // Create a user
    $userMetadata = [
        'source' => 'php-sdk',
        'plan' => 'premium'
    ];
    
    $user = $client->getUsers()->create('user@example.com', 'John Doe', $userMetadata);
    echo "✅ User created: {$user['id']}\n";
    echo "   Email: {$user['email']}\n";
    echo "   Name: {$user['name']}\n";
    
    // Get user stats
    $userStats = $client->getUsers()->stats();
    echo "\n📊 User Statistics:\n";
    echo "   Total: {$userStats['total']}\n";
    echo "   Active: {$userStats['active']}\n";
    echo "   Inactive: {$userStats['inactive']}\n";
    
    // 3. Product Management
    echo "\n📦 Product Management:\n";
    
    // Create a product
    $productMetadata = [
        'category' => 'software',
        'tags' => ['premium', 'enterprise']
    ];
    
    $product = $client->getProducts()->create(
        'My Software Product',
        'A great software product',
        99.99,
        'USD',
        $productMetadata
    );
    echo "✅ Product created: {$product['id']}\n";
    echo "   Name: {$product['name']}\n";
    echo "   Price: \${$product['price']} {$product['currency']}\n";
    
    // Get product stats
    $productStats = $client->getProducts()->stats();
    echo "\n📊 Product Statistics:\n";
    echo "   Total: {$productStats['total']}\n";
    echo "   Active: {$productStats['active']}\n";
    echo "   Revenue: \${$productStats['revenue']}\n";
    
    // 4. Webhook Management
    echo "\n🔗 Webhook Management:\n";
    
    // Create a webhook
    $events = [
        WebhookEvents::LICENSE_CREATED,
        WebhookEvents::LICENSE_UPDATED,
        WebhookEvents::USER_CREATED
    ];
    
    $webhook = $client->getWebhooks()->create('https://example.com/webhook', $events, 'webhook-secret');
    echo "✅ Webhook created: {$webhook['id']}\n";
    echo "   URL: {$webhook['url']}\n";
    echo "   Events: " . implode(', ', $webhook['events']) . "\n";
    
    // 5. Webhook Processing
    echo "\n🔄 Webhook Processing:\n";
    
    $webhookHandler = new WebhookHandler('webhook-secret');
    
    // Simulate a webhook event
    $webhookEvent = [
        'id' => 'evt_123',
        'type' => WebhookEvents::LICENSE_CREATED,
        'data' => [
            'id' => 'lic_123',
            'user_id' => 'user_123',
            'product_id' => 'prod_123',
            'license_key' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ012345',
            'status' => 'active',
            'created_at' => '2023-01-01T00:00:00Z'
        ],
        'timestamp' => '2023-01-01T00:00:00Z',
        'signature' => 'signature_here'
    ];
    
    $webhookHandler->processEvent($webhookEvent);
    echo "✅ Webhook event processed successfully\n";
    
    // 6. Utility Functions
    echo "\n🛠️ Utility Functions:\n";
    
    // Email validation
    $email = 'test@example.com';
    echo "Email '{$email}' is valid: " . (Utils::validateEmail($email) ? 'true' : 'false') . "\n";
    
    // License key validation
    $licenseKey = Utils::generateLicenseKey();
    echo "License key '{$licenseKey}' is valid: " . (Utils::validateLicenseKey($licenseKey) ? 'true' : 'false') . "\n";
    
    // Generate UUID
    $uuid = Utils::generateUuid();
    echo "Generated UUID: {$uuid}\n";
    
    // Format bytes
    $bytes = 1024 * 1024;
    echo "{$bytes} bytes = " . Utils::formatBytes($bytes) . "\n";
    
    // Format duration
    $seconds = 3661;
    echo "Duration: " . Utils::formatDuration($seconds) . "\n";
    
    // String utilities
    $text = 'Hello World';
    echo "Capitalize first: " . Utils::capitalizeFirst($text) . "\n";
    echo "To snake_case: " . Utils::toSnakeCase('HelloWorld') . "\n";
    echo "To PascalCase: " . Utils::toPascalCase('hello_world') . "\n";
    echo "Slugify: " . Utils::slugify('Hello World!') . "\n";
    
    // 7. Error Handling
    echo "\n🛡️ Error Handling:\n";
    
    try {
        $client->getLicenses()->get('invalid-id');
    } catch (Exception $e) {
        echo "✅ Caught expected error: " . $e->getMessage() . "\n";
    }
    
    try {
        $client->getUsers()->create('invalid-email', 'John Doe');
    } catch (Exception $e) {
        echo "✅ Caught expected error: " . $e->getMessage() . "\n";
    }
    
    // 8. API Health Check
    echo "\n🏥 API Health Check:\n";
    
    $ping = $client->ping();
    echo "Ping response: " . Utils::jsonSerialize($ping) . "\n";
    
    $health = $client->health();
    echo "Health response: " . Utils::jsonSerialize($health) . "\n";
    
    echo "\n✅ Basic usage example completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . get_class($e) . " - " . $e->getMessage() . "\n";
    if (getenv('DEBUG')) {
        echo $e->getTraceAsString() . "\n";
    }
}
