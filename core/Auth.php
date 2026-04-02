<?php
/**
 * Autenticacion de administradores y personal interno
 */

class Auth
{
    public static function isAdminLoggedIn(): bool
    {
        return isset($_SESSION['evalcom_admin']) && array_key_exists('id', $_SESSION['evalcom_admin']);
    }

    public static function isEmployeeLoggedIn(): bool
    {
        return !empty($_SESSION['evalcom_employee']['serial_epl']);
    }

    public static function requireAdmin(): void
    {
        if (!self::isAdminLoggedIn()) {
            if (is_ajax()) {
                json_response(['success' => false, 'message' => 'No autorizado'], 401);
            }

            redirect('login.php');
        }
    }

    public static function requireEmployee(): void
    {
        if (!self::isEmployeeLoggedIn()) {
            if (is_ajax()) {
                json_response(['success' => false, 'message' => 'Sesión expirada'], 401);
            }

            redirect('interno.php');
        }
    }

    public static function loginAdmin(string $username, string $password): bool
    {
        $username = trim($username);
        $password = trim($password);

        if ($username === ADMIN_USER && $password === ADMIN_PASS) {
            $_SESSION['evalcom_admin'] = [
                'id' => 0,
                'username' => $username,
                'name' => ADMIN_DISPLAY,
                'role' => 'SUPERADMIN',
            ];
            return true;
        }

        $admin = Database::fetchOne(
            "SELECT *
             FROM evalcom_admins
             WHERE usuario = ?
               AND activo = 'SI'
             LIMIT 1",
            [$username]
        );

        if (!$admin || !password_verify($password, (string) $admin['password'])) {
            return false;
        }

        $_SESSION['evalcom_admin'] = [
            'id' => (int) $admin['serial_admin'],
            'username' => $admin['usuario'],
            'name' => $admin['nombre_completo'],
            'role' => $admin['rol'],
            'email' => $admin['email'],
        ];

        Database::update(
            'evalcom_admins',
            ['ultimo_acceso' => date('Y-m-d H:i:s')],
            'serial_admin = ?',
            [(int) $admin['serial_admin']]
        );

        return true;
    }

    public static function loginEmployee(string $cedula, string $password): ?array
    {
        $cedula = digits_only($cedula);
        $password = digits_only($password);

        if ($cedula === '' || $password === '' || $cedula !== $password) {
            return null;
        }

        $employee = Database::fetchOne(
            "SELECT e.SERIAL_EPL,
                    e.DOCUMENTOIDENTIDAD_EPL,
                    e.NOMBRE_EPL,
                    e.APELLIDO_EPL,
                    e.EMAIL_EPL,
                    e.mailPersonal_epl,
                    e.emailpersonal_epl,
                    e.CELULAR_EPL,
                    e.ESTADO_EPL,
                    e.ESTADOEMPLEADO_EPL
             FROM empleado e
             WHERE e.ESTADOEMPLEADO_EPL = 'ACTIVO'
               AND e.DOCUMENTOIDENTIDAD_EPL = ?
             LIMIT 1",
            [$cedula]
        );

        if (!$employee) {
            return null;
        }

        $_SESSION['evalcom_employee'] = [
            'serial_epl' => (int) $employee['SERIAL_EPL'],
            'cedula' => $employee['DOCUMENTOIDENTIDAD_EPL'],
            'name' => employee_full_name($employee),
            'email' => $employee['EMAIL_EPL'] ?: ($employee['mailPersonal_epl'] ?: $employee['emailpersonal_epl']),
        ];

        return $employee;
    }

    public static function logoutAdmin(): void
    {
        unset($_SESSION['evalcom_admin']);
        if (!headers_sent()) {
            session_regenerate_id(true);
        }
    }

    public static function logoutEmployee(): void
    {
        unset($_SESSION['evalcom_employee']);
        if (!headers_sent()) {
            session_regenerate_id(true);
        }
    }

    public static function getAdminUser(): ?array
    {
        return $_SESSION['evalcom_admin'] ?? null;
    }

    public static function getEmployeeUser(): ?array
    {
        return $_SESSION['evalcom_employee'] ?? null;
    }

    public static function hasPermission(string $permission): bool
    {
        if (!self::isAdminLoggedIn()) {
            return false;
        }

        $role = $_SESSION['evalcom_admin']['role'] ?? '';
        $permissions = [
            'SUPERADMIN' => ['all'],
            'ADMIN' => ['view', 'manage', 'config', 'results'],
            'REVISOR' => ['view', 'results'],
        ];

        return in_array('all', $permissions[$role] ?? [], true)
            || in_array($permission, $permissions[$role] ?? [], true);
    }

    public static function createAdmin(array $data): int
    {
        return Database::insert('evalcom_admins', [
            'nombre_completo' => trim((string) ($data['nombre'] ?? '')),
            'usuario' => trim((string) ($data['usuario'] ?? '')),
            'email' => trim((string) ($data['email'] ?? '')),
            'password' => password_hash((string) ($data['password'] ?? ''), PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]),
            'rol' => $data['rol'] ?? 'ADMIN',
            'activo' => $data['activo'] ?? 'SI',
        ]);
    }
}
