<?php
/**
 * Helpers compartidos
 */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function sanitize(?string $value): string
{
    return htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function is_ajax(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function digits_only(?string $value): string
{
    return preg_replace('/\D+/', '', (string) $value) ?? '';
}

function get_client_ip(): string
{
    $keys = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_CLIENT_IP',
        'REMOTE_ADDR',
    ];

    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            return trim(explode(',', (string) $_SERVER[$key])[0]);
        }
    }

    return '0.0.0.0';
}

function app_url(string $path = ''): string
{
    $base = rtrim(APP_URL, '/');
    if ($path === '') {
        return $base;
    }

    return $base . '/' . ltrim($path, '/');
}

function public_url(string $path = ''): string
{
    return app_url('public/' . ltrim($path, '/'));
}

function admin_url(string $path = ''): string
{
    return app_url('admin/' . ltrim($path, '/'));
}

function api_url(string $path = ''): string
{
    return app_url('api/' . ltrim($path, '/'));
}

function employee_full_name(array $employee): string
{
    $lastName = trim((string) ($employee['APELLIDO_EPL'] ?? $employee['apellido_epl'] ?? ''));
    $firstName = trim((string) ($employee['NOMBRE_EPL'] ?? $employee['nombre_epl'] ?? ''));
    return trim($lastName . ' ' . $firstName);
}

function employee_short_name(array $employee): string
{
    $name = employee_full_name($employee);
    if ($name !== '') {
        return $name;
    }

    return trim((string) ($employee['nombre_completo'] ?? ''));
}

function employee_initials(array $employee): string
{
    $name = preg_split('/\s+/', employee_short_name($employee)) ?: [];
    $initials = '';
    foreach (array_slice($name, 0, 2) as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }

    return $initials !== '' ? $initials : 'UE';
}

function format_date(?string $value, string $format = 'd/m/Y'): string
{
    if (!$value) {
        return '';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return '';
    }

    return date($format, $timestamp);
}

function format_datetime(?string $value): string
{
    return format_date($value, 'd/m/Y H:i');
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function pull_flash(): ?array
{
    if (!isset($_SESSION['flash_message'])) {
        return null;
    }

    $flash = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);

    return $flash;
}

function request_method_is(string $method): bool
{
    return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === strtoupper($method);
}

function performance_thresholds(): array
{
    return [
        'sobresaliente' => 83.334,
        'satisfactorio' => 66.667,
        'mejora' => 50.000,
    ];
}

function score_label(?float $normalizedScore): string
{
    if ($normalizedScore === null) {
        return 'Pendiente';
    }

    $thresholds = performance_thresholds();
    if ($normalizedScore >= $thresholds['sobresaliente']) {
        return 'Desempeño sobresaliente';
    }
    if ($normalizedScore >= $thresholds['satisfactorio']) {
        return 'Desempeño satisfactorio';
    }
    if ($normalizedScore >= $thresholds['mejora']) {
        return 'En proceso de mejora';
    }

    return 'Desempeño insuficiente';
}

function score_badge_class(?float $normalizedScore): string
{
    if ($normalizedScore === null) {
        return 'bg-gray-100 text-gray-600';
    }

    $thresholds = performance_thresholds();
    if ($normalizedScore >= $thresholds['sobresaliente']) {
        return 'bg-emerald-100 text-emerald-700';
    }
    if ($normalizedScore >= $thresholds['satisfactorio']) {
        return 'bg-blue-100 text-blue-700';
    }
    if ($normalizedScore >= $thresholds['mejora']) {
        return 'bg-amber-100 text-amber-700';
    }

    return 'bg-red-100 text-red-700';
}

function format_score(?float $score, int $decimals = 3): string
{
    if ($score === null) {
        return '--';
    }

    return number_format($score, $decimals, '.', '');
}

function same_numeric_value($left, $right, int $decimals = 4): bool
{
    if ($left === null || $right === null) {
        return false;
    }

    if (!is_numeric((string) $left) || !is_numeric((string) $right)) {
        return false;
    }

    return number_format((float) $left, $decimals, '.', '') === number_format((float) $right, $decimals, '.', '');
}

function format_percentage(?float $value, int $decimals = 1): string
{
    if ($value === null) {
        return '--';
    }

    return number_format($value, $decimals, '.', '') . '%';
}
