# Changelog

Todas las notas de versión del SDK PHP de Softan Connect.

## v0.1.0 – 2025-10-11
- Publicación inicial del SDK.
- Endpoints: tokens (crear/estado), otp (crear/validar), developers/validate.
- Helpers de headers y crypto (HMAC y AES-256-GCM para `X-Connect-SDK`).
- CLI de instalación y manejo de entornos (`bin/install.php`, `bin/add-env.php`, `bin/switch-env.php`).
- PHPUnit configurado con pruebas básicas de Crypto, Headers y SDK JSON IO.
- CI en GitHub Actions para PHP 8.1/8.2/8.3.
