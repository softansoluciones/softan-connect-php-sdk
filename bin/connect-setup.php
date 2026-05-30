#!/usr/bin/env php
<?php
// Autoload (repo, vendor o proyecto raiz)
$autoloadCandidates = [
    __DIR__ . '/../vendor/autoload.php',        // ejecutado desde el repo del SDK
    __DIR__ . '/../../../autoload.php',         // ejecutado desde vendor/softan/connect-php-sdk/bin
    getcwd() . '/vendor/autoload.php',          // ejecutado desde el proyecto raiz
];
$loaded = false;
foreach ($autoloadCandidates as $candidate) {
    if (is_file($candidate)) { require $candidate; $loaded = true; break; }
}
if (!$loaded || !class_exists('SoftanConnect\\SDK')) {
    fwrite(STDERR, "No se encontro el autoloader de Composer.\nBusque en:\n - " . implode("\n - ", $autoloadCandidates) . "\n");
    exit(1);
}

use SoftanConnect\SDK;
use SoftanConnect\Config;
use SoftanConnect\Crypto;
use SoftanConnect\Headers;
use SoftanConnect\Services;

SDK::init();

echo "Softan Connect PHP SDK - Instalador\n\n";

function prompt(string $label, bool $hidden = false): string {
    if ($hidden && strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        echo $label;
        @system('stty -echo');
        $val = rtrim(fgets(STDIN));
        @system('stty echo');
        echo "\n";
        return $val;
    }
    echo $label;
    return rtrim(fgets(STDIN));
}

// Permite uso no interactivo con flags
$opts = getopt('', [
    'email::', 'api-key::', 'connect-info::', 'app-id::', 'env::', 'base-url::', 'verify-tls::'
]);

echo "Introduce los datos requeridos:\n";
$developer = $opts['email']        ?? prompt('Developer e-mail: ');
$apiKey    = $opts['api-key']      ?? prompt('Requester API Key (X-API-KEY): ', true);
$xcInfo    = $opts['connect-info'] ?? prompt('X-Connect-Info (encrypted string): ', true);
$appId     = $opts['app-id']       ?? prompt('App Identifier: ');

$activeEnv = $opts['env'] ?? (SDK::$CONFIG['active_environment'] ?? (SDK::$META['default_environment'] ?? 'stg'));

// Generar X-Connect-SDK (HMAC por defecto)
$sdkHash = Crypto::generateSdkHeaderHmac($apiKey, $appId, SDK::$META ?: []);

$cfg = SDK::$CONFIG ?: [
  'developer_email'    => $developer,
  'app_identifier'     => $appId,
  'active_environment' => $activeEnv,
  'environments'       => []
];
$cfg['developer_email'] = $developer;
$cfg['app_identifier'] = $appId;
$cfg['active_environment'] = $activeEnv;
$cfg['environments'][$activeEnv] = [
  'api_key'      => $apiKey,
  'connect_info' => $xcInfo,
  'connect_sdk'  => $sdkHash
];

$baseUrl   = $opts['base-url'] ?? (Config::getMeta('base_urls', [])[$activeEnv] ?? '');
$verifyTls = !isset($opts['verify-tls']) ? true : (filter_var($opts['verify-tls'], FILTER_VALIDATE_BOOLEAN));

echo "Validando credenciales contra $activeEnv...\n";
$headers = Headers::buildValidationHeaders($apiKey, $xcInfo, $sdkHash);
$res = Services::validateCredentials($headers, $verifyTls, $baseUrl);

if (($res['success'] ?? false) && !empty($res['data']['credentials']['x_api_key'])) {
    if (SDK::saveJson(SDK::CONFIG_PATH, $cfg)) { echo "\nOK - Configuracion guardada en sdk_config.json\n"; exit(0);}
    fwrite(STDERR, "\nERROR - No se pudo escribir sdk_config.json\n"); exit(1);
}

fwrite(STDERR, "\nERROR - No fue posible validar credenciales. Respuesta:\n" . json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
exit(1);
