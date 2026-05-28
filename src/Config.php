<?php
namespace SoftanConnect;

final class Config
{
    public static function getMeta(string $key, mixed $default = null): mixed
    {
        return SDK::$META[$key] ?? $default;
    }

    public static function getConfig(string $key, mixed $default = null): mixed
    {
        return SDK::$CONFIG[$key] ?? $default;
    }

    public static function getActiveEnvironment(): string
    {
        $active = self::getConfig('active_environment');
        if (!$active) {
            $active = self::getMeta('default_environment', 'stg');
        }
        return (string) $active;
    }

    public static function getEnvironmentConfig(?string $env = null): array
    {
        $env = $env ?: self::getActiveEnvironment();
        $all = self::getConfig('environments', []);
        return (array) ($all[$env] ?? []);
    }

    public static function getBaseUrl(?string $env = null): string
    {
        $env  = $env ?: self::getActiveEnvironment();
        $base = (string) (self::getMeta('base_urls', [])[$env] ?? '');
        if ($base && !str_ends_with($base, '/')) {
            $base .= '/';
        }
        return $base;
    }

    /**
     * Resolve an endpoint path from the nested endpoints map in sdk_meta.json.
     *
     * Usage: Config::resolveEndpoint('tokens', 'create') → 'tokens'
     *        Config::resolveEndpoint('otp', 'validate')  → 'otp/validate'
     *
     * @throws \InvalidArgumentException if the path is not found or not a string leaf.
     */
    public static function resolveEndpoint(string ...$keys): string
    {
        $map = self::getMeta('endpoints', []);

        foreach ($keys as $key) {
            if (!is_array($map) || !array_key_exists($key, $map)) {
                $path = implode('.', $keys);
                throw new \InvalidArgumentException(
                    "Endpoint path not configured in sdk_meta.json for '{$path}'."
                );
            }
            $map = $map[$key];
        }

        if (!is_string($map)) {
            $path = implode('.', $keys);
            throw new \InvalidArgumentException(
                "Endpoint leaf for '{$path}' is not a string."
            );
        }

        return $map;
    }

    /**
     * @deprecated Use resolveEndpoint() instead. Kept for backward compatibility.
     */
    public static function getEndpoints(): array
    {
        return (array) self::getMeta('endpoints', []);
    }
}
