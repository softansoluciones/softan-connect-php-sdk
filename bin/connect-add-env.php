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
use SoftanConnect\Crypto;
use SoftanConnect\Headers;
use SoftanConnect\Services;

SDK::init();

function prompt(string $label, bool $hidden=false){ echo $label; return rtrim(fgets(STDIN)); }
$env   = prompt('Nombre del entorno a agregar (prod): ');
$base  = prompt('Base URL (termina con /public/): ');
$api   = prompt('Requester API Key: ');
$info  = prompt('X-Connect-Info: ');
$appId = prompt('App Identifier: ');
$sdk   = Crypto::generateSdkHeaderHmac($api, $appId, SDK::$META ?: []);

// Validar contra la URL indicada
$headers = Headers::buildValidationHeaders($api, $info, $sdk);
$res = Services::validateCredentials($headers, true, $base);

if (!empty($res['Response']['api-key']) && !empty($res['Response']['connect-info'])) {
    $meta = SDK::$META; $meta['base_urls'][$env] = $base;
    SDK::saveJson(SDK::META_PATH, $meta);

    $cfg = SDK::$CONFIG ?: ['environments' => []];
    $cfg['environments'][$env] = [
        'api_key' => $api,
        'connect_info' => $info,
        'connect_sdk' => $sdk
    ];
    SDK::saveJson(SDK::CONFIG_PATH, $cfg);
    echo "OK ✅ Entorno agregado: $env\n"; exit(0);
}

fwrite(STDERR, "ERROR ❌ No se pudo validar contra $base\n");
exit(1);
