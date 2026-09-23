<?php
declare(strict_types=1);

const APP_NAME = 'Focal-Shift';
define('APP_ENV', getenv('FOCAL_APP_ENV') ?: 'development');
const MAX_UPLOAD_BYTES = 5 * 1024 * 1024;

// Valeurs compatibles avec l'installation WampServer la plus courante.
// En production, utiliser des variables d'environnement et un compte SQL dédié.
define('DB_HOST', getenv('FOCAL_DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('FOCAL_DB_PORT') ?: '3306');
define('DB_NAME', getenv('FOCAL_DB_NAME') ?: 'focal_shift');
define('DB_USER', getenv('FOCAL_DB_USER') ?: 'root');
define('DB_PASS', getenv('FOCAL_DB_PASS') !== false ? getenv('FOCAL_DB_PASS') : '');

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$basePath = preg_replace('#/(api/[^/]+|[^/]+\.php)$#', '', $scriptName) ?: '';
define('BASE_URL', rtrim($basePath, '/'));

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'path' => '/',
    ]);
    session_start();
}
