<?php

use PHPUnit\Framework\TestCase;
use SoftanConnect\Crypto;

final class CryptoTest extends TestCase
{
    public function testBase64UrlRoundtrip(): void
    {
        $input = random_bytes(32);
        $b64 = Crypto::base64url_encode($input);
        $out = Crypto::base64url_decode($b64);
        $this->assertSame($input, $out);
        $this->assertStringNotContainsString('+', $b64);
        $this->assertStringNotContainsString('/', $b64);
        $this->assertStringNotContainsString('=', $b64);
    }

    public function testGenerateSdkHeaderHmacStructureAndSignature(): void
    {
        $apiKey = 'test_api_key_123';
        $appId  = 'com.example.app';
        $meta   = ['sdk_version' => '0.1.0', 'sdk_type' => 'php'];

        $token = Crypto::generateSdkHeaderHmac($apiKey, $appId, $meta);
        $parts = explode('.', $token);
        $this->assertCount(2, $parts, 'Expected two-part HMAC token');

        [$payloadB64, $sigB64] = $parts;

        // Verify signature
        $expectedRaw = hash_hmac('sha256', $payloadB64, $apiKey, true);
        $expectedB64 = Crypto::base64url_encode($expectedRaw);
        $this->assertSame($expectedB64, $sigB64, 'HMAC signature must match');

        // Verify payload JSON
        $payloadJson = Crypto::base64url_decode($payloadB64);
        $payload = json_decode($payloadJson, true);
        $this->assertIsArray($payload);
        $this->assertSame('0.1.0', $payload['sdk_version'] ?? null);
        $this->assertSame('php', $payload['sdk_type'] ?? null);
        $this->assertSame($appId, $payload['app_identifier'] ?? null);
        $this->assertArrayHasKey('timestamp', $payload);
    }

    public function testGenerateSdkHeaderGcmEncryptDecrypt(): void
    {
        if (!function_exists('openssl_encrypt')) {
            $this->markTestSkipped('OpenSSL not available');
        }

        $apiKey = 'test_api_key_456';
        $appId  = 'com.example.app2';
        $meta   = ['sdk_version' => '0.2.0', 'sdk_type' => 'php'];

        $token = Crypto::generateSdkHeaderGcm($apiKey, $appId, $meta);
        $parts = explode('.', $token);
        $this->assertCount(3, $parts, 'Expected three-part GCM token');

        [$ivB64, $cipherB64, $tagB64] = $parts;
        $key = hash('sha256', $apiKey, true);

        $iv     = Crypto::base64url_decode($ivB64);
        $cipher = Crypto::base64url_decode($cipherB64);
        $tag    = Crypto::base64url_decode($tagB64);

        $plain = openssl_decrypt($cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        $this->assertNotFalse($plain, 'Decryption should succeed');

        $payload = json_decode($plain, true);
        $this->assertIsArray($payload);
        $this->assertSame('0.2.0', $payload['sdk_version'] ?? null);
        $this->assertSame('php', $payload['sdk_type'] ?? null);
        $this->assertSame($appId, $payload['app_identifier'] ?? null);
        $this->assertArrayHasKey('timestamp', $payload);
    }
}

