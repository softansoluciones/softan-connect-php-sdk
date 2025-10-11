# Softan Connect PHP SDK

SDK ligero para integrar con Softan Connect sin pelear con headers ni cifrados.

## Instalación

- Composer (desde GitHub, repo propio):

1) Agrega el repositorio VCS en tu `composer.json` del proyecto:
```json
{
  "repositories": [
    { "type": "vcs", "url": "https://github.com/softansoluciones/softan-connect-php-sdk" }
  ]
}
```

2) Requiere el paquete del SDK PHP (rama `main`):
```bash
composer require softan/connect-php-sdk:dev-main
```

- Si publicamos un tag (recomendado):
```bash
composer require softan/connect-php-sdk:^0.1
```

3) Ejecuta el asistente de configuración para generar `sdk_config.json`:
```bash
vendor/bin/install.php
```

## Uso rápido
```php
<?php
require __DIR__ . '/vendor/autoload.php';

use SoftanConnect\Services;

$res = Services::requestToken(["user_id" => 12345]);
var_dump($res);
```

## Estructura
- `sdk_meta.json`: metadatos (urls, endpoints, versión SDK)
- `sdk_config.json`: generado por `install.php` con tu `api_key`, `x-connect-info`, `x-connect-sdk`.

## Desarrollo

- Ejecutar pruebas (requiere deps de desarrollo):
```bash
composer install
composer test
```

- CI: el workflow en `.github/workflows/ci.yml` valida composer e integra PHPUnit en PHP 8.1/8.2/8.3.

## Releases
- Consulta `RELEASE.md` para el proceso de publicación y `CHANGELOG.md` para notas de versión.
