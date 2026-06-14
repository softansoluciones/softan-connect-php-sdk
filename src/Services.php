<?php
namespace SoftanConnect;

final class Services
{
    // ----------------------------------------------------------------
    // Tokens
    // ----------------------------------------------------------------

    /**
     * POST /tokens — Create a session token for a user.
     *
     * Required payload fields:
     *   - user_id (int)
     */
    public static function requestToken(array $payload, ?array $headers = null, bool $verifyTLS = true): array
    {
        SDK::init();
        self::require($payload, 'user_id', 'scalar');

        $headers  = $headers ?: Headers::buildRuntimeHeaders();
        $endpoint = Config::resolveEndpoint('tokens', 'create');
        return Client::request($endpoint, 'POST', $headers, $payload, null, $verifyTLS);
    }

    /**
     * POST /tokens/status — Check the status of a session token.
     *
     * Required payload fields:
     *   - user_id (int)
     *   - token   (string)
     */
    public static function tokenStatus(array $payload, ?array $headers = null, bool $verifyTLS = true): array
    {
        SDK::init();
        self::require($payload, 'user_id', 'scalar');
        self::require($payload, 'token',   'string');

        $headers  = $headers ?: Headers::buildRuntimeHeaders();
        $endpoint = Config::resolveEndpoint('tokens', 'status');
        return Client::request($endpoint, 'POST', $headers, $payload, null, $verifyTLS);
    }

    // ----------------------------------------------------------------
    // OTP
    // ----------------------------------------------------------------

    /**
     * POST /otp — Request / send an OTP to a user.
     *
     * Required payload fields:
     *   - user_id (int)
     */
    public static function requestOtp(array $payload, ?array $headers = null, bool $verifyTLS = true): array
    {
        SDK::init();
        self::require($payload, 'user_id', 'scalar');

        $headers  = $headers ?: Headers::buildRuntimeHeaders();
        $endpoint = Config::resolveEndpoint('otp', 'create');
        return Client::request($endpoint, 'POST', $headers, $payload, null, $verifyTLS);
    }

    /**
     * POST /otp/validate — Validate an OTP code.
     *
     * Required payload fields:
     *   - user_id  (int)
     *   - otp_code (int)
     */
    public static function validateOtp(array $payload, ?array $headers = null, bool $verifyTLS = true): array
    {
        SDK::init();
        self::require($payload, 'user_id',  'int');
        self::require($payload, 'otp_code', 'int');

        $headers  = $headers ?: Headers::buildRuntimeHeaders();
        $endpoint = Config::resolveEndpoint('otp', 'validate');
        return Client::request($endpoint, 'POST', $headers, $payload, null, $verifyTLS);
    }

    // ----------------------------------------------------------------
    // Developers / Credentials
    // ----------------------------------------------------------------

    /**
     * POST /developers/validate — Validate the SDK credentials.
     */
    public static function validateCredentials(
        ?array  $headers         = null,
        bool    $verifyTLS       = true,
        ?string $baseUrlOverride = null
    ): array {
        SDK::init();
        $headers  = $headers ?: Headers::buildRuntimeHeaders();
        $endpoint = Config::resolveEndpoint('developers', 'validate');
        return Client::request($endpoint, 'POST', $headers, [], $baseUrlOverride, $verifyTLS);
    }

    // ----------------------------------------------------------------
    // Internal helpers
    // ----------------------------------------------------------------

    /**
     * Assert that a required field exists in the payload with the correct type.
     *
     * @throws \InvalidArgumentException
     */
    private static function require(array $data, string $field, string $type): void
    {
        if (!array_key_exists($field, $data)) {
            throw new \InvalidArgumentException("Missing required field: '{$field}'.");
        }

        $value = $data[$field];

        $valid = match ($type) {
            'int'    => is_int($value),
            'string' => is_string($value) && $value !== '',
            'scalar' => is_scalar($value) && trim((string) $value) !== '',
            'bool'   => is_bool($value),
            'array'  => is_array($value),
            default  => true,
        };

        if (!$valid) {
            throw new \InvalidArgumentException(
                "Field '{$field}' must be of type {$type}, got " . gettype($value) . '.'
            );
        }
    }
}
