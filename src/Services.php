<?php
namespace SoftanConnect;

final class Services
{
    /**
     * POST /public/tokens
     */
    public static function requestToken(array $payload, ?array $headers = null, bool $verifyTLS = true): array
    {
        SDK::init();
        $headers = $headers ?: Headers::buildRuntimeHeaders();
        $endpoint = Config::getEndpoints()['tokens_create'] ?? 'tokens';
        return Client::request($endpoint, 'POST', $headers, $payload, null, $verifyTLS);
    }

    /**
     * POST /public/tokens/status
     */
    public static function tokenStatus(array $payload, ?array $headers = null, bool $verifyTLS = true): array
    {
        SDK::init();
        $headers = $headers ?: Headers::buildRuntimeHeaders();
        $endpoint = Config::getEndpoints()['tokens_status'] ?? 'tokens/status';
        return Client::request($endpoint, 'POST', $headers, $payload, null, $verifyTLS);
    }

    /**
     * POST /public/otp
     */
    public static function requestOtp(array $payload, ?array $headers = null, bool $verifyTLS = true): array
    {
        SDK::init();
        $headers = $headers ?: Headers::buildRuntimeHeaders();
        $endpoint = Config::getEndpoints()['otp_create'] ?? 'otp';
        return Client::request($endpoint, 'POST', $headers, $payload, null, $verifyTLS);
    }

    /**
     * POST /public/otp/validate
     */
    public static function validateOtp(array $payload, ?array $headers = null, bool $verifyTLS = true): array
    {
        SDK::init();
        $headers = $headers ?: Headers::buildRuntimeHeaders();
        $endpoint = Config::getEndpoints()['otp_validate'] ?? 'otp/validate';
        return Client::request($endpoint, 'POST', $headers, $payload, null, $verifyTLS);
    }

    /**
     * POST /public/developers/validate
     */
    public static function validateCredentials(?array $headers = null, bool $verifyTLS = true, ?string $baseUrlOverride = null): array
    {
        SDK::init();
        $headers = $headers ?: Headers::buildRuntimeHeaders();
        $endpoint = Config::getEndpoints()['developers_validate'] ?? 'developers/validate';
        return Client::request($endpoint, 'POST', $headers, [], $baseUrlOverride, $verifyTLS);
    }
}