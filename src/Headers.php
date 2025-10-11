<?php
namespace SoftanConnect;

final class Headers
{
    public static function buildValidationHeaders(string $apiKey, string $connectInfo, string $sdkHash): array
    {
        return [
            'Content-Type' => 'application/json',
            'X-API-KEY' => $apiKey,
            'X-Connect-Info' => $connectInfo,
            'X-Connect-SDK' => $sdkHash,
        ];
    }

    public static function buildRuntimeHeaders(?array $envCfg = null): array
    {
        $envCfg = $envCfg ?: Config::getEnvironmentConfig();
        $apiKey = $envCfg['api_key'] ?? '';
        $info = $envCfg['connect_info'] ?? '';
        $sdk = $envCfg['connect_sdk'] ?? '';
        return self::buildValidationHeaders($apiKey, $info, $sdk);
    }
}