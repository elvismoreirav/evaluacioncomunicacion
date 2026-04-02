<?php
/**
 * SISTEMA DE DIAGNOSTICO DE COMUNICACION - UECR
 * Configuracion principal
 */

if (!defined('EVALCOM_APP')) {
    define('EVALCOM_APP', true);
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

date_default_timezone_set('America/Guayaquil');

$localConfig = __DIR__ . '/local.php';
if (file_exists($localConfig)) {
    require_once $localConfig;
}

$detectBasePath = static function(): string {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    if ($scriptName !== '') {
        foreach (['/admin/', '/public/', '/api/'] as $segment) {
            $position = strpos($scriptName, $segment);
            if ($position !== false) {
                $base = substr($scriptName, 0, $position);
                return $base === '' ? '/' : rtrim($base, '/');
            }
        }

        $base = dirname($scriptName);
        return $base === '.' ? '/' : rtrim($base, '/');
    }

    return '/evaluacioncomunicacion';
};

$basePath = $detectBasePath();
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$detectedUrl = $scheme . '://' . $host . ($basePath === '/' ? '' : $basePath);

if (!function_exists('config_value')) {
    function config_value(string $constantName, string $envName, string $default = ''): string
    {
        if (defined($constantName)) {
            return (string) constant($constantName);
        }

        $value = $_ENV[$envName] ?? $_SERVER[$envName] ?? getenv($envName);
        if ($value === false || $value === null) {
            return $default;
        }

        return (string) $value;
    }
}

define('DB_HOST', config_value('DB_HOST', 'EVALCOM_DB_HOST', 'localhost'));
define('DB_NAME', config_value('DB_NAME', 'EVALCOM_DB_NAME', 'admision2627'));
define('DB_USER', config_value('DB_USER', 'EVALCOM_DB_USER', 'root'));
define('DB_PASS', config_value('DB_PASS', 'EVALCOM_DB_PASS', '12345678'));
define('DB_CHARSET', config_value('DB_CHARSET', 'EVALCOM_DB_CHARSET', 'utf8mb4'));

define('APP_NAME', 'Sistema de Diagnostico de Comunicacion');
define('APP_SHORT_NAME', 'Diagnostico Comunicacion UECR');
define('APP_VERSION', '1.0.0');
define('APP_BASE_PATH', $basePath);
define('APP_URL', getenv('EVALCOM_APP_URL') ?: $detectedUrl);
define('INSTITUTION_NAME', 'Unidad Educativa Particular Cristo Rey');
define('INSTITUTION_LOGO', 'https://academico.cristorey.edu.ec/v2.0/procesomatricula/uecr%20redondo.png');

define('PRIMARY_COLOR', '#003399');
define('ACCENT_COLOR', '#ffcc00');
define('NEUTRAL_COLOR', '#ffffff');

define('SESSION_NAME', 'evalcom_uecr_session');
define('SESSION_LIFETIME', 7200);

define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'admin123');
define('ADMIN_DISPLAY', 'Administrador Diagnostico Comunicacion');

define('MAIL_FROM', config_value('MAIL_FROM', 'EVALCOM_MAIL_FROM', 'notificacionesbecas@cristorey.edu.ec'));
define('MAIL_FROM_NAME', config_value('MAIL_FROM_NAME', 'EVALCOM_MAIL_FROM_NAME', 'Unidad Educativa Particular Cristo Rey'));
define('MAIL_SMTP_HOST', config_value('MAIL_SMTP_HOST', 'EVALCOM_MAIL_SMTP_HOST', 'smtp.gmail.com'));
define('MAIL_SMTP_PORT', config_value('MAIL_SMTP_PORT', 'EVALCOM_MAIL_SMTP_PORT', '465'));
define('MAIL_SMTP_SECURE', config_value('MAIL_SMTP_SECURE', 'EVALCOM_MAIL_SMTP_SECURE', 'ssl'));
define('OAUTH_CLIENT_ID', config_value('OAUTH_CLIENT_ID', 'EVALCOM_OAUTH_CLIENT_ID', ''));
define('OAUTH_CLIENT_SECRET', config_value('OAUTH_CLIENT_SECRET', 'EVALCOM_OAUTH_CLIENT_SECRET', ''));
define('OAUTH_REFRESH_TOKEN', config_value('OAUTH_REFRESH_TOKEN', 'EVALCOM_OAUTH_REFRESH_TOKEN', ''));

define('PASSWORD_COST', 12);

if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
