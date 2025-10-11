# Softan Connect SDK (PHP)

## Descripción
- SDK oficial para integrar aplicaciones con Softan Connect en PHP.
- Expone métodos de alto nivel para crear y validar tokens y OTP.
- Incluye utilidades CLI para configurar credenciales y administrar entornos (stg/prod).

### Comandos CLI (referencia rápida)
| Comando                         | Propósito                                   |
|---------------------------------|---------------------------------------------|
| `vendor/bin/install.php`        | Crear configuración inicial (stg)           |
| `vendor/bin/add-env.php`        | Agregar/actualizar credenciales de `prod`   |
| `vendor/bin/switch-env.php`     | Cambiar entorno activo (stg/prod)           |

Nota Windows: si no se ejecutan directamente, usa `php vendor/bin/install.php` (igual para los demás).

## Requisitos
- PHP 8.0 o superior
- Composer 2

## Instalación
Desde GitHub (origen único soportado por ahora).

1) Agrega el repositorio VCS en tu `composer.json` del proyecto (copiar y pegar):
```json
{
  "repositories": [
    { "type": "vcs", "url": "https://github.com/softansoluciones/softan-connect-php-sdk" }
  ]
}
```

2) Requiere el paquete del SDK PHP (rama `main`) — copiar y pegar:
```bash
composer require softan/connect-php-sdk:dev-main
```

### Versionado
- Primer tag recomendado: `v0.1.0` (cuando esté publicado).
- Instalación directa con versión fija (copiar y pegar):
```bash
composer require softan/connect-php-sdk:^0.1
```

### Instalación rápida (sin editar composer.json a mano)
- Windows (PowerShell):
```powershell
composer config repositories.softan-sdk vcs https://github.com/softansoluciones/softan-connect-php-sdk
composer require softan/connect-php-sdk:dev-main
php vendor/bin/install.php
```
- macOS/Linux (bash):
```bash
composer config repositories.softan-sdk vcs https://github.com/softansoluciones/softan-connect-php-sdk \
  && composer require softan/connect-php-sdk:dev-main \
  && vendor/bin/install.php
```
- Notas:
  - Si tu proyecto no tiene Composer aún, `composer require` creará `composer.json` automáticamente.
  - Requiere Git instalado para resolver el repositorio VCS.

### Cuando esté en Packagist (flujo recomendado)
- Instalación con un solo comando (sin repositorios VCS):
```bash
composer require softan/connect-php-sdk:^0.1
```
  - Con esto Composer resolverá el paquete desde Packagist y añadirá la dependencia automáticamente.

## Quickstart (3 pasos)
1) Instalar el SDK desde GitHub
```bash
composer require softan/connect-php-sdk:dev-main
```
2) Crear configuración base (stg por defecto)
```bash
vendor/bin/install.php
# En Windows si es necesario: php vendor/bin/install.php
```
3) Usar en código (crear y consultar estado de token)
```php
<?php
require __DIR__ . '/vendor/autoload.php';

use SoftanConnect\Services;

$userId = 12345;
$resCreate = Services::requestToken(["user_id" => $userId]);

$token = null;
if (is_array($resCreate) && isset($resCreate['Response']) && is_array($resCreate['Response'])) {
    $token = $resCreate['Response']['token'] ?? null;
}

if ($token) {
    $resStatus = Services::tokenStatus(["token" => $token, "user_id" => $userId]);
    var_dump($resStatus);
} else {
    echo "No se obtuvo token en la creación. Revisa credenciales y entorno."; 
}
```

## Primeros pasos (CLI)
- Instalar y crear la configuración (stg por defecto):
```bash
vendor/bin/install.php
```
- Agregar/actualizar credenciales de producción:
```bash
vendor/bin/add-env.php
```
- Cambiar entorno activo (stg/prod):
```bash
vendor/bin/switch-env.php
```

## Uso en código
Importa los métodos de alto nivel desde `SoftanConnect\Services` y realiza llamadas según tu caso de uso.

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

// Validar credenciales del desarrollador (útil para verificar setup)
$resCreds = Services::validateCredentials();
```

## Configuración
- Transparente para el usuario final: la configuración se gestiona mediante los comandos CLI.
- No es necesario (ni recomendado) editar ni versionar `sdk_config.json` manualmente.

## Entornos
- Desarrollo: `stg` es el entorno por defecto.
- Producción: agrega credenciales con `add-env.php` y usa `switch-env.php` para activarlo.
- Todas las llamadas usan automáticamente el entorno activo para base URL y headers.

## Notas importantes
- TLS: la verificación está habilitada por defecto; si tu entorno stg presenta certificados no confiables, puedes pasar `verifyTLS=false` a los métodos de alto nivel por llamada (solo para desarrollo):
  - `Services::requestToken($data, null, false)`
  - `Services::tokenStatus($data, null, false)`
  - `Services::requestOtp($data, null, false)`
  - `Services::validateOtp($data, null, false)`
- No incluyas `/public` en tus rutas; el SDK se encarga de la base URL.

## Solución de problemas
- 403 al llamar a tokens/otp: verifica que configuraste credenciales (CLI) y que el entorno activo corresponde.
- 0 o 404: puede indicar datos inexistentes/inactivos o `user_id` inválido.

## Comandos disponibles
- `vendor/bin/install.php` → Instala y configura el entorno `stg`.
- `vendor/bin/add-env.php` → Agrega o actualiza credenciales de `prod`.
- `vendor/bin/switch-env.php` → Cambia el entorno activo (stg/prod).

## Compatibilidad
- PHP: 8.0+
- Sistemas: Windows, Linux, macOS

## Desarrollo
- Ejecutar pruebas (requiere deps de desarrollo):
```bash
composer install
composer test
```
- CI: el workflow en `.github/workflows/ci.yml` valida composer e integra PHPUnit en PHP 8.1/8.2/8.3.

## Releases
- Consulta `RELEASE.md` para el proceso de publicación y `CHANGELOG.md` para notas de versión.

## Licencia
- MIT (ver `composer.json`).
