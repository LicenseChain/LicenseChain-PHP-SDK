<?php

namespace LicenseChain;

use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use GuzzleHttp\Client;

/**
 * RS256 license_token verification via JWKS (parity with Node verifyLicenseAssertionJwt).
 */
final class LicenseAssertion
{
    /** @var string Must match Core API LICENSE_TOKEN_USE_CLAIM */
    public const LICENSE_TOKEN_USE_CLAIM = 'licensechain_license_v1';

    /**
     * @param array{expectedAppId?: string, issuer?: string} $options
     * @return object Decoded JWT payload (stdClass)
     */
    public static function verifyLicenseAssertionJwt(string $token, string $jwksUrl, array $options = []): object
    {
        $token = trim($token);
        if ($token === '') {
            throw new \InvalidArgumentException('empty token');
        }
        $jwksUrl = trim($jwksUrl);
        if ($jwksUrl === '') {
            throw new \InvalidArgumentException('empty jwksUrl');
        }

        $client = new Client(['timeout' => 20]);
        $jwksJson = (string) $client->get($jwksUrl)->getBody();
        $jwks = json_decode($jwksJson, true);
        if (!is_array($jwks)) {
            throw new \RuntimeException('invalid JWKS JSON');
        }

        $keys = JWK::parseKeySet($jwks);
        $decoded = JWT::decode($token, $keys);

        $issuer = isset($options['issuer']) ? (string) $options['issuer'] : '';
        if ($issuer !== '' && (!isset($decoded->iss) || (string) $decoded->iss !== $issuer)) {
            throw new \RuntimeException('issuer mismatch');
        }

        $tu = isset($decoded->token_use) ? (string) $decoded->token_use : '';
        if ($tu !== self::LICENSE_TOKEN_USE_CLAIM) {
            throw new \RuntimeException('Invalid license token: expected token_use "' . self::LICENSE_TOKEN_USE_CLAIM . '"');
        }

        $expectedAppId = isset($options['expectedAppId']) ? trim((string) $options['expectedAppId']) : '';
        if ($expectedAppId !== '') {
            $aud = $decoded->aud ?? null;
            $ok = false;
            if (is_string($aud) && $aud === $expectedAppId) {
                $ok = true;
            }
            if (is_array($aud) && in_array($expectedAppId, $aud, true)) {
                $ok = true;
            }
            if (!$ok) {
                throw new \RuntimeException('Invalid license token: aud does not match expected app id');
            }
        }

        return $decoded;
    }
}
