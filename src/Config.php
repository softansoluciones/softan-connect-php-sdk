<?php
namespace SoftanConnect;


final class Config
{
    public static function getMeta(string $key, $default = null)
    {
        return SDK::$META[$key] ?? $default;
    }


    public static function getConfig(string $key, $default = null)
    {
        return SDK::$CONFIG[$key] ?? $default;
    }


    public static function getActiveEnvironment(): string
    {
        $active = self::getConfig('active_environment');
        if (!$active) {
            $active = self::getMeta('default_environment', 'stg');
        }
        return $active;
    }


    public static function getEnvironmentConfig(?string $env = null): array
    {
        $env = $env ?: self::getActiveEnvironment();
        $all = self::getConfig('environments', []);
        return $all[$env] ?? [];
    }


    public static function getBaseUrl(?string $env = null): string
    {
        $env = $env ?: self::getActiveEnvironment();
        $base = self::getMeta('base_urls', [])[$env] ?? '';
        if ($base && !str_ends_with($base, '/')) $base .= '/';
        return $base;
    }


    public static function getEndpoints(): array
    {
        return self::getMeta('endpoints', []);
    }
}