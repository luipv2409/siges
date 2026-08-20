<?php
/**
 * SIGES - Sistema de Gestión de Empeños
 * Funciones de sesión y autorización
 *
 * Fase 2 - Utilidades y estilos
 */

/**
 * Inicia la sesión de forma segura si aún no está iniciada.
 *
 * @return void
 */
function ensure_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        // Configuración segura de la cookie de sesión
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'secure'   => false, // Cambiar a true en producción con HTTPS
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

/**
 * Verifica si el usuario está autenticado en el sistema.
 *
 * @return bool True si hay una sesión activa, False en caso contrario
 */
function is_logged_in(): bool
{
    ensure_session();
    return !empty($_SESSION['user_id']);
}

/**
 * Verifica si el usuario autenticado tiene uno de los roles permitidos.
 *
 * @param array|string $roles Rol o lista de roles permitidos (ej: 'OWNER' o ['OWNER', 'EMPLOYEE'])
 * @return bool True si el usuario tiene el rol requerido, False en caso contrario
 */
function has_role($roles): bool
{
    if (!is_logged_in()) {
        return false;
    }

    // Normalizar a array
    $allowed_roles = is_array($roles) ? $roles : [$roles];

    // El rol del usuario se guarda en sesión al iniciar sesión
    $user_role = $_SESSION['user_role'] ?? null;

    return in_array($user_role, $allowed_roles, true);
}

/**
 * Requiere que el usuario esté autenticado y tenga uno de los roles permitidos.
 * Si no cumple los requisitos, redirige a la página correspondiente.
 *
 * @param array|string $roles Rol o lista de roles permitidos
 * @return void
 */
function require_role($roles): void
{
    if (!is_logged_in()) {
        // No autenticado: redirigir al login
        redirect(BASE_URL . '/login.php');
    }

    if (!has_role($roles)) {
        // Registrar intento de acceso no autorizado en audit_log
        log_audit('ACCESO_NO_AUTORIZADO', 'Intento de acceso a módulo sin permisos. Rol: ' . ($_SESSION['user_role'] ?? 'N/A'));

        // Autenticado pero sin permisos: redirigir al dashboard según su rol
        $user_role = $_SESSION['user_role'] ?? '';

        switch ($user_role) {
            case 'OWNER':
                redirect(BASE_URL . '/dashboard.php');
                break;
            case 'EMPLOYEE':
                redirect(BASE_URL . '/dashboard.php');
                break;
            case 'CLIENT':
                redirect(BASE_URL . '/dashboard.php');
                break;
            default:
                redirect(BASE_URL . '/login.php');
        }
    }
}

/**
 * Registra una acción en la tabla audit_log.
 *
 * @param string $action Tipo de acción (ej: 'LOGIN', 'ACCESO_NO_AUTORIZADO', 'CREAR_EMPLEADO')
 * @param string|null $description Descripción adicional de la acción
 * @return void
 */
function log_audit(string $action, ?string $description = null): void
{
    try {
        require_once __DIR__ . '/../config/database.php';
        $pdo = getDBConnection();

        $user_id = $_SESSION['user_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : null;

        $stmt = $pdo->prepare(
            'INSERT INTO audit_log (user_id, action, description, ip_address, user_agent)
             VALUES (:user_id, :action, :description, :ip_address, :user_agent)'
        );
        $stmt->execute([
            ':user_id'     => $user_id,
            ':action'      => $action,
            ':description' => $description,
            ':ip_address'  => $ip,
            ':user_agent'  => $user_agent,
        ]);
    } catch (PDOException $e) {
        // No bloquear la operación principal si falla el registro de auditoría
        error_log('Error registrando auditoría: ' . $e->getMessage());
    }
}

/**
 * ============================================================
 * CLASE Auth - Middleware de autenticación y autorización
 * ============================================================
 * Wrappers compatibles con la API descrita en siges.csv:
 *   Auth::check()          -> Verifica sesión activa
 *   Auth::requireLogin()   -> Requiere autenticación
 *   Auth::authorize($roles)-> Verifica rol permitido
 *
 * Estas funciones son wrappers de las funciones existentes
 * para mantener compatibilidad sin romper el código actual.
 * ============================================================
 */
class Auth
{
    /**
     * Verifica si hay una sesión activa.
     *
     * @return bool True si el usuario está autenticado
     */
    public static function check(): bool
    {
        return is_logged_in();
    }

    /**
     * Requiere que el usuario esté autenticado.
     * Redirige a /login si no está autenticado.
     *
     * @return void
     */
    public static function requireLogin(): void
    {
        if (!is_logged_in()) {
            redirect(BASE_URL . '/login.php');
        }
    }

    /**
     * Verifica si el usuario tiene uno de los roles permitidos.
     * Responde HTTP 403 si no tiene permisos.
     *
     * @param array|string $roles Rol o lista de roles permitidos
     * @return void
     */
    public static function authorize($roles): void
    {
        if (!is_logged_in()) {
            redirect(BASE_URL . '/login.php');
        }

        if (!has_role($roles)) {
            // Registrar intento de acceso no autorizado
            log_audit('ACCESO_NO_AUTORIZADO', 'Intento de acceso a módulo sin permisos. Rol: ' . ($_SESSION['user_role'] ?? 'N/A'));

            // Responder HTTP 403
            http_response_code(403);
            die('403 - Acceso Prohibido. No tiene permisos para acceder a este módulo.');
        }
    }

    /**
     * Obtiene el ID del usuario autenticado.
     *
     * @return int|null ID del usuario o null si no está autenticado
     */
    public static function id(): ?int
    {
        return is_logged_in() ? (int)$_SESSION['user_id'] : null;
    }

    /**
     * Obtiene el rol del usuario autenticado.
     *
     * @return string|null Rol del usuario o null si no está autenticado
     */
    public static function role(): ?string
    {
        return is_logged_in() ? ($_SESSION['user_role'] ?? null) : null;
    }
}
