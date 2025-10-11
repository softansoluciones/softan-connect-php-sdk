#!/usr/bin/env php
<?php
// Autoload resiliente: repo, vendor o proyecto raíz
$autoloadCandidates = [
    __DIR__ . '/../vendor/autoload.php',        // ejecutado desde el repo del SDK
    __DIR__ . '/../../../autoload.php',         // ejecutado desde vendor/softan/connect-php-sdk/bin (sin symlink)
    getcwd() . '/vendor/autoload.php',          // ejecutado desde el proyecto raíz (symlink repos)
];
$loaded = false;
foreach ($autoloadCandidates as $candidate) {
    if (is_file($candidate)) { require $candidate; $loaded = true; break; }
}
if (!$loaded) {
    fwrite(STDERR, "No se encontró el autoloader de Composer.\nBusqué en:\n - " . implode("\n - ", $autoloadCandidates) . "\n");
    exit(1);
}
if (!class_exists('SoftanConnect\\SDK')) {
    fwrite(STDERR, "No se encontró el autoloader de Composer.\n");
    exit(1);
}

use SoftanConnect\SDK;
use SoftanConnect\Config;
use SoftanConnect\Crypto;
use SoftanConnect\Headers;
use SoftanConnect\Services;

SDK::init();

function prompt(string $label, bool $hidden = false): string {
    if ($hidden && strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        echo $label;
        system('stty -echo');
        $val = rtrim(fgets(STDIN));
        system('stty echo');
        echo "\n";
        return $val;
    }
    echo $label;
    return rtrim(fgets(STDIN));
}

$developer = prompt('Developer e-mail: ');
$apiKey    = prompt('Requester API Key (X-API-KEY): ', true);
$xcInfo    = prompt('X-Connect-Info (encrypted string): ', true);
$appId     = prompt('App Identifier: ');

// Generar X-Connect-SDK (HMAC por defecto)
$sdkHash = Crypto::generateSdkHeaderHmac($apiKey, $appId, SDK::$META ?: []);

$cfg = SDK::$CONFIG ?: [
  'developer_email'    => $developer,
  'app_identifier'     => $appId,
  'active_environment' => SDK::$META['default_environment'] ?? 'stg',
  'environments'       => []
];
$env = $cfg['active_environment'];
$cfg['environments'][$env] = [
  'api_key'      => $apiKey,
  'connect_info' => $xcInfo,
  'connect_sdk'  => $sdkHash
];

// Validación directa contra STG (meta default) para evitar mala config
$stgUrl = Config::getMeta('base_urls', [])['stg'] ?? '';
$headers = Headers::buildValidationHeaders($apiKey, $xcInfo, $sdkHash);
$res = Services::validateCredentials($headers, true, $stgUrl);

if (!empty($res['Response']['api-key']) && !empty($res['Response']['connect-info'])) {
    SDK::saveJson(SDK::CONFIG_PATH, $cfg);
    echo "\nOK ✅ Configuración guardada en sdk_config.json\n";
    exit(0);
}

fwrite(STDERR, "\nERROR ❌ No fue posible validar credenciales. Respuesta:\n" . json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
exit(1);
