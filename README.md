# Softan Connect SDK (PHP)

[![Latest Stable Version](https://img.shields.io/packagist/v/softan/connect-php-sdk.svg)](https://packagist.org/packages/softan/connect-php-sdk)
[![Total Downloads](https://img.shields.io/packagist/dt/softan/connect-php-sdk.svg)](https://packagist.org/packages/softan/connect-php-sdk)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](./LICENSE)

## Descripción

SDK oficial para integrar aplicaciones con Softan Connect en PHP. Expone métodos de alto nivel para crear y validar tokens y OTP. Incluye utilidades CLI para configurar credenciales y administrar entornos (stg/prod).

### Comandos CLI (referencia rápida)

| Comando | Propósito |
|---------|-----------|
| `php vendor/bin/connect-setup.php` | Configuración inicial — pide credenciales y valida contra la API |
| `php vendor/bin/connect-add-env.php` | Agrega o actualiza credenciales de un entorno adicional (ej: prod) |
| `php vendor/bin/connect-switch-env.php` | Cambia el entorno activo (stg/prod) |

## Requisitos

- PHP 8.0 o superior
- Composer 2

## Instalación

```bash
composer require softan/connect-php-sdk:^0.0.6
```

Luego inicializa la configuración con tus credenciales (stg por defecto):

```bash
php vendor/bin/connect-setup.php
```

### Alternativa: instalación desde GitHub (VCS)

```bash
composer config repositories.softan-sdk vcs https://github.com/softansoluciones/softan-connect-php-sdk
composer require softan/connect-php-sdk:dev-main
```

## Quickstart

1. Instalar el SDK:
```bash
composer require softan/connect-php-sdk:^0.0.6
```

2. Configurar credenciales:
```bash
php vendor/bin/connect-setup.php
```

3. Usar en código:
```php
<?php
require __DIR__ . '/vendor/autoload.php';

use SoftanConnect\Services;

$userId = 12345;
$resCreate = Services::requestToken(["user_id" => $userId]);

$token = $resCreate['data']['token'] ?? $resCreate['Response']['token'] ?? null;

if ($token) {
    $resStatus = Services::tokenStatus(["token" => $token, "user_id" => $userId]);
    var_dump($resStatus);
} else {
    echo "No se obtuvo token. Revisa credenciales y entorno.";
}
```

## Primeros pasos (CLI)

### Configuración inicial (stg)

```bash
php vendor/bin/connect-setup.php
```

Solicita: developer email, API Key (`X-API-KEY`), X-Connect-Info y App Identifier. Valida las credenciales contra la API antes de guardar.

**Modo no interactivo:**
```bash
php vendor/bin/connect-setup.php \
  --email="dev@example.com" \
  --api-key="<X-API-KEY>" \
  --connect-info="<X-Connect-Info>" \
  --app-id="SOM-XXXX" \
  --env=stg \
  --verify-tls=true
```

### Agregar entorno de producción

```bash
php vendor/bin/connect-add-env.php
```

Solicita: API Key, X-Connect-Info y App Identifier. Configura producción usando la base URL definida en `sdk_meta.json`, sin pisar la configuración existente.

### Cambiar entorno activo

```bash
php vendor/bin/connect-switch-env.php
```

Lista los entornos disponibles y permite seleccionar cuál usar como activo.

## Uso en código

```php
use SoftanConnect\Services;

// Token: crear
$resCreate = Services::requestToken(["user_id" => 12345]);

// Token: estado
$resStatus = Services::tokenStatus(["token" => "<TOKEN>", "user_id" => 12345]);

// OTP: crear
$resOtp = Services::requestOtp(["user_id" => 12345]);

// OTP: validar
$resVal = Services::validateOtp(["otp_code" => 123456, "user_id" => 12345]);

// Validar credenciales del desarrollador
$resCreds = Services::validateCredentials();
```

## Entornos

- El entorno por defecto es `stg`.
- Para agregar prod: `php vendor/bin/connect-add-env.php`
- Para cambiar el activo: `php vendor/bin/connect-switch-env.php`
- Todas las llamadas usan automáticamente el entorno activo para base URL y headers.

## Configuración

La configuración se gestiona en `sdk_config.json` (creado por los scripts CLI, **no versionar**). Contiene las credenciales por entorno y el entorno activo.

```json
{
  "developer_email": "dev@example.com",
  "app_identifier": "SOM-XXXX",
  "active_environment": "stg",
  "environments": {
    "stg": {
      "api_key": "...",
      "connect_info": "...",
      "connect_sdk": "..."
    }
  }
}
```

## TLS

La verificación TLS está habilitada por defecto. Para desactivarla por llamada (solo desarrollo):

```php
Services::requestToken($data, null, false);
Services::tokenStatus($data, null, false);
Services::requestOtp($data, null, false);
Services::validateOtp($data, null, false);
```

## Caché de tokens

El SDK cachea respuestas de tokens y OTP para reducir llamadas repetitivas:

- **TTL por defecto:** 3600 segundos (1 hora)
- **Directorio:** `cache/` junto a `sdk_config.json`

Para personalizar:
```json
{
  "cache": {
    "dir": "/ruta/absoluta/opcional",
    "ttl_seconds": 1800
  }
}
```

## Solución de problemas

- **403 en tokens/otp:** verifica que configuraste credenciales y que el entorno activo es correcto.
- **0 o 404:** puede indicar `user_id` inválido o inactivo.
- **Conflicto de binarios:** todos los scripts de Softan Connect usan el prefijo `connect-` para evitar conflictos con otros paquetes (`connect-setup.php`, `connect-add-env.php`, `connect-switch-env.php`).

## Compatibilidad

- PHP: 8.0+
- Sistemas: Windows, Linux, macOS

## Desarrollo

```bash
composer install
composer test
```

CI: el workflow en `.github/workflows/ci.yml` valida Composer e integra PHPUnit en PHP 8.1/8.2/8.3.

## Licencia

MIT (ver `composer.json`).
