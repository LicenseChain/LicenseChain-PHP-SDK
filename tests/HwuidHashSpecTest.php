<?php

declare(strict_types=1);

namespace LicenseChain\Tests;

use LicenseChain\ApiClient;
use LicenseChain\Services\LicenseService;
use PHPUnit\Framework\TestCase;

final class HwuidHashSpecTest extends TestCase
{
    public function testValidateUsesDeterministicDefaultHwuidHashSpec(): void
    {
        $capturedBody = null;

        $client = $this->createMock(ApiClient::class);
        $client->method('post')
            ->willReturnCallback(function (string $endpoint, array $data) use (&$capturedBody): array {
                if ($endpoint === '/licenses/verify') {
                    $capturedBody = $data;
                    return ['valid' => true];
                }
                return ['valid' => false];
            });

        $service = new LicenseService($client);

        $this->assertTrue($service->validate('test-license-key'));
        $this->assertNotNull($capturedBody);
        $this->assertArrayHasKey('hwuid', $capturedBody);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', (string)$capturedBody['hwuid']);
    }
}
