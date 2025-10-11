<?php
namespace SoftanConnect;

final class Crypto
{
    public static function base64url_encode(string $bin): string
    {
        return rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
    }


    public static function base64url_decode(string $str): string
    {
        $remainder = strlen($str) % 4;
        if ($remainder) $str .= str_repeat('=', 4 - $remainder);
        return base64_decode(strtr($str, '-_', '+/')) ?: '';
    }


    public static function generateSdkHeaderHmac(string $apiKey, string $appIdentifier, array $meta): string
    {
        $payload = json_encode([
            'sdk_version' => $meta['sdk_version'] ?? '0.0.0',
            'sdk_type' => $meta['sdk_type'] ?? 'php',
            'app_identifier' => $appIdentifier,
            'timestamp' => time()
        ], JSON_UNESCAPED_SLASHES);


        $payloadB64 = self::base64url_encode($payload);
        $sigRaw = hash_hmac('sha256', $payloadB64, $apiKey, true);
        $sigB64 = self::base64url_encode($sigRaw);
        return $payloadB64 . '.' . $sigB64; // X-Connect-SDK
    }


    public static function generateSdkHeaderGcm(string $apiKey, string $appIdentifier, array $meta): string
    {
        $payload = json_encode([
            'sdk_version' => $meta['sdk_version'] ?? '0.0.0',
            'sdk_type' => $meta['sdk_type'] ?? 'php',
            'app_identifier' => $appIdentifier,
            'timestamp' => time()
        ], JSON_UNESCAPED_SLASHES);


        $key = hash('sha256', $apiKey, true);
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($payload, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        if ($cipher === false) {
            throw new \RuntimeException('Encryption failed');
        }
        return self::base64url_encode($iv) . '.' . self::base64url_encode($cipher) . '.' . self::base64url_encode($tag);
    }
}