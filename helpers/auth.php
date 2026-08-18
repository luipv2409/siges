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
        // Autenticado pero sin permisos: redirigir al dashboard según su rol
        $user_role = $_SESSION['user_role'] ?? '';

        switch ($user_role) {
            case 'OWNER':
                redirect(BASE_URL . '/dashboard_owner.php');
                break;
            case 'EMPLOYEE':
                redirect(BASE_URL . '/dashboard_employee.php');
                break;
            case 'CLIENT':
                redirect(BASE_URL . '/dashboard_client.php');
                break;
            default:
                redirect(BASE_URL . '/login.php');
        }
    }
}
