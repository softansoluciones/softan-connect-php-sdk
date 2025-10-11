<?php
namespace SoftanConnect;

final class Client
{
    public static function request(string $endpoint, string $method = 'GET', array $headers = [], ?array $data = null, ?string $baseUrlOverride = null, bool $verifyTLS = true): array
    {
        $url = ($baseUrlOverride ?: Config::getBaseUrl()) . ltrim($endpoint, '/');

        $ch = curl_init();
        $httpHeaders = [];
        foreach ($headers as $k => $v) { $httpHeaders[] = $k . ': ' . $v; }

        $opts = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
            CURLOPT_HTTPHEADER     => $httpHeaders,
            CURLOPT_TIMEOUT        => 20,
        ];

        if ($data !== null) {
            $opts[CURLOPT_POSTFIELDS] = json_encode($data, JSON_UNESCAPED_SLASHES);
        }

        if (!$verifyTLS) {
            $opts[CURLOPT_SSL_VERIFYPEER] = false;
            $opts[CURLOPT_SSL_VERIFYHOST] = false;
        }

        curl_setopt_array($ch, $opts);
        $raw  = curl_exec($ch);
        $err  = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE) ?: 0;
        curl_close($ch);

        if ($raw === false) {
            return ['error' => 'network_error', 'detail' => $err];
        }

        $json = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            return $json;
        }
        return ['status_code' => $code, 'raw' => $raw];
    }
}
