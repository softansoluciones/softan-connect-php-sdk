# Changelog

Todas las notas de versión del SDK PHP de Softan Connect.

## v0.0.2 – 2025-10-11
- Cache local de tokens y estado (TTL por defecto 1 hora) para reducir llamadas repetitivas; soporta múltiples usuarios/tokens simultáneos.
- Instalador `bin/install.php` mejorado con mensajes claros y soporte de flags (validación online siempre requerida).
- README actualizado para instalación desde Packagist; badges de Packagist añadidos.
- Workflows: notificación opcional a Packagist en release; guía de release y publicación.

## v0.0.1 – 2025-10-11
- Publicación inicial del SDK.
- Endpoints: tokens (crear/estado), otp (crear/validar), developers/validate.
- Helpers de headers y crypto (HMAC y AES-256-GCM para `X-Connect-SDK`).
- CLI de instalación y manejo de entornos (`bin/install.php`, `bin/add-env.php`, `bin/switch-env.php`).
- PHPUnit configurado con pruebas básicas de Crypto, Headers y SDK JSON IO.
- CI en GitHub Actions para PHP 8.1/8.2/8.3.


