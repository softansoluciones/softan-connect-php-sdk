#!/usr/bin/env php
<?php
/**
 * flows.php — CLI runner for Softan Connect quick flows.
 *
 * Usage:
 *   php bin/flows.php token-create  --user-id=42
 *   php bin/flows.php token-status  --user-id=42 --token=abc123
 *   php bin/flows.php otp-request   --user-id=42
 *   php bin/flows.php otp-validate  --user-id=42 --otp-code=123456
 *   php bin/flows.php validate-credentials
 *
 * Options:
 *   --insecure   Disable TLS verification (development only)
 *   --env=dev    Override active environment
 */

$autoloadCandidates = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php',
    getcwd() . '/vendor/autoload.php',
];

$loaded = false;
foreach ($autoloadCandidates as $candidate) {
    if (is_file($candidate)) {
        require $candidate;
        $loaded = true;
        break;
    }
}

if (!$loaded || !class_exists('SoftanConnect\\SDK')) {
    fwrite(STDERR, "Could not find the Composer autoloader.\n");
    exit(1);
}

use SoftanConnect\SDK;
use SoftanConnect\Services;

SDK::init();

// ----------------------------------------------------------------
// Parse arguments
// ----------------------------------------------------------------
$args    = array_slice($argv, 1);
$command = null;
$opts    = [];

foreach ($args as $arg) {
    if (str_starts_with($arg, '--')) {
        $part = substr($arg, 2);
        if (str_contains($part, '=')) {
            [$k, $v] = explode('=', $part, 2);
            $opts[$k] = $v;
        } else {
            $opts[$part] = true;
        }
    } elseif ($command === null) {
        $command = $arg;
    }
}

$verifyTLS = !isset($opts['insecure']);

// Override active environment if --env is provided
if (isset($opts['env'])) {
    SDK::$CONFIG['active_environment'] = $opts['env'];
}

function printJson(array $data): void
{
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
}

function requireOpt(array $opts, string $key): string
{
    if (!isset($opts[$key])) {
        fwrite(STDERR, "Missing required option: --{$key}\n");
        exit(1);
    }
    return (string) $opts[$key];
}

// ----------------------------------------------------------------
// Commands
// ----------------------------------------------------------------
$commands = [
    'token-create'         => 'Create a session token for a user (--user-id)',
    'token-status'         => 'Check token status (--user-id, --token)',
    'otp-request'          => 'Request / send OTP to a user (--user-id)',
    'otp-validate'         => 'Validate an OTP code (--user-id, --otp-code)',
    'validate-credentials' => 'Validate SDK credentials against the API',
];

if ($command === null || $command === 'help' || isset($opts['help'])) {
    echo "Softan Connect PHP SDK — flows CLI (v" . (SDK::$META['sdk_version'] ?? '?') . ")\n\n";
    echo "Available commands:\n";
    foreach ($commands as $cmd => $desc) {
        printf("  %-26s %s\n", $cmd, $desc);
    }
    echo "\nOptions:\n";
    echo "  --insecure   Disable TLS verification\n";
    echo "  --env=<env>  Override active environment (dev|stg|prod)\n";
    exit(0);
}

switch ($command) {

    case 'token-create':
        $result = Services::requestToken(
            ['user_id' => (int) requireOpt($opts, 'user-id')],
            null,
            $verifyTLS
        );
        printJson($result);
        break;

    case 'token-status':
        $result = Services::tokenStatus([
            'user_id' => (int) requireOpt($opts, 'user-id'),
            'token'   => requireOpt($opts, 'token'),
        ], null, $verifyTLS);
        printJson($result);
        break;

    case 'otp-request':
        $result = Services::requestOtp(
            ['user_id' => (int) requireOpt($opts, 'user-id')],
            null,
            $verifyTLS
        );
        printJson($result);
        break;

    case 'otp-validate':
        $result = Services::validateOtp([
            'user_id'  => (int) requireOpt($opts, 'user-id'),
            'otp_code' => (int) requireOpt($opts, 'otp-code'),
        ], null, $verifyTLS);
        printJson($result);
        break;

    case 'validate-credentials':
        $result = Services::validateCredentials(null, $verifyTLS);
        printJson($result);
        break;

    default:
        fwrite(STDERR, "Unknown command: '{$command}'. Run 'php bin/flows.php help' for usage.\n");
        exit(1);
}
