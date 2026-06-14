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
    echo $label;
    return rtrim(fgets(STDIN));
}

// El único entorno que se puede agregar vía este script es producción.
// La base URL se lee directamente de sdk_meta.json.
$env  = 'prod';
$base = Config::getBaseUrl($env);

if (!$base) {
    fwrite(STDERR, "ERROR ❌ No hay base_url para el entorno '{$env}' en sdk_meta.json.\n");
    exit(1);
}

$api   = prompt('Requester API Key: ');
$info  = prompt('X-Connect-Info: ');
$appId = prompt('App Identifier: ');
$sdk   = Crypto::generateSdkHeaderHmac($api, $appId, SDK::$META ?: []);

// Validar credenciales contra la URL de producción
$headers = Headers::buildValidationHeaders($api, $info, $sdk);
$res = Services::validateCredentials($headers, true, $base);

if (!empty($res['success']) && !empty($res['data'])) {
    $cfg = SDK::$CONFIG ?: ['environments' => []];
    $cfg['environments'][$env] = [
        'api_key'      => $api,
        'connect_info' => $info,
        'connect_sdk'  => $sdk,
    ];
    SDK::saveJson(SDK::CONFIG_PATH, $cfg);
    echo "OK ✅ Entorno '{$env}' configurado correctamente.\n";
    exit(0);
}

fwrite(STDERR, "ERROR ❌ No se pudo validar contra {$base}\nRespuesta:\n" . json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
exit(1);
