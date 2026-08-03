<?php

declare(strict_types=1);

/**
 * JWKS-only: verify a `license_token` when you already have the token and JWKS URI
 * (no full client verify call). Matches JWKS_EXAMPLE_PRIORITY.md in the sdks umbrella repo.
 *
 *   export LICENSECHAIN_LICENSE_TOKEN="eyJ..."
 *   export LICENSECHAIN_LICENSE_JWKS_URI="https://api.licensechain.app/v1/licenses/jwks"
 *   # optional: LICENSECHAIN_EXPECTED_APP_ID=<uuid>
 *   php examples/jwks_only.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use LicenseChain\LicenseAssertion;

$token = getenv('LICENSECHAIN_LICENSE_TOKEN') ?: '';
$jwks = getenv('LICENSECHAIN_LICENSE_JWKS_URI') ?: '';
if ($token === '' || $jwks === '') {
    fwrite(STDERR, "Set LICENSECHAIN_LICENSE_TOKEN and LICENSECHAIN_LICENSE_JWKS_URI\n");
    exit(1);
}

$options = [];
$appId = getenv('LICENSECHAIN_EXPECTED_APP_ID');
if ($appId !== false && trim($appId) !== '') {
    $options['expectedAppId'] = trim($appId);
}

$claims = LicenseAssertion::verifyLicenseAssertionJwt($token, $jwks, $options);
echo json_encode($claims, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
