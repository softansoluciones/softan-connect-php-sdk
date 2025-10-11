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

SDK::init();
$cfg = SDK::$CONFIG;
$envs = array_keys($cfg['environments'] ?? []);
if (!$envs) { fwrite(STDERR, "No hay entornos configurados.\n"); exit(1);} 

echo "Entornos: ".implode(', ', $envs)."\n";
echo "Actual: ".($cfg['active_environment'] ?? '(none)')."\n";
echo "¿A cuál cambiar?: ";
$target = rtrim(fgets(STDIN));
if (!in_array($target, $envs, true)) { fwrite(STDERR, "Entorno inválido.\n"); exit(1);} 
$cfg['active_environment'] = $target;
if (SDK::saveJson(SDK::CONFIG_PATH, $cfg)) { echo "OK ✅ Cambiado a $target\n"; exit(0);} 
exit(1);
