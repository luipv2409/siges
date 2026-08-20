<?php
/**
 * SIGES - Sistema de Gestión de Empeños
 * Funciones de seguridad y saneamiento de datos
 *
 * Fase 2 - Utilidades y estilos
 */

/**
 * Sanea un valor de entrada para prevenir XSS e inyección de código.
 *
 * @param mixed $value Valor a sanear (string, array, etc.)
 * @return mixed Valor saneado
 */
function sanitize($value)
{
    if (is_array($value)) {
        return array_map('sanitize', $value);
    }

    if (is_string($value)) {
        // Elimina etiquetas HTML y PHP, y convierte caracteres especiales
        $value = strip_tags($value);
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        return trim($value);
    }

    return $value;
}

/**
 * Genera un token CSRF único y lo almacena en la sesión.
 *
 * @return string Token CSRF generado
 */
function generate_csrf_token(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        // Configuración segura de la cookie de sesión
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'secure'   => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}


/**
 * Verifica que el token CSRF recibido coincida con el almacenado en sesión.
 *
 * @param string|null $token Token CSRF a verificar (normalmente $_POST['csrf_token'])
 * @return bool True si el token es válido, False en caso contrario
 */
function verify_csrf_token(?string $token): bool
{
    // Si no hay cookie de sesión, no hay token CSRF almacenado
    if (session_status() === PHP_SESSION_NONE && !isset($_COOKIE[session_name()])) {
        return false;
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }

    // Comparación segura contra timing attacks
    return hash_equals($_SESSION['csrf_token'], $token);
}


/**
 * Redirige a una URL específica y termina la ejecución del script.
 *
 * @param string $url URL de destino (puede ser relativa o absoluta)
 * @return void
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}
