<?php
/**
 * SIGES - Sistema de Gestión de Empeños
 * Cierre de sesión
 *
 * Sprint 1 - Autenticación y Seguridad
 */

// Configuración y helpers
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/helpers/security.php';
require_once __DIR__ . '/helpers/auth.php';

// Asegurar que la sesión esté iniciada
ensure_session();

// Destruir la sesión de forma segura
$_SESSION = [];

// Eliminar la cookie de sesión
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destruir la sesión
session_destroy();

// Redirigir al login
redirect(BASE_URL . '/login.php');
