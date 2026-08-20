<?php
/**
 * SIGES - Sistema de Gestión de Empeños
 * Configuración general de la aplicación
 *
 * Fase 1 - Configuración inicial
 */

// ============================================================
// INFORMACIÓN GENERAL DEL SISTEMA
// ============================================================
define('APP_NAME', 'SIGES');
define('APP_FULL_NAME', 'Sistema de Gestión de Empeños');
define('APP_VERSION', '1.0.0');

// ============================================================
// URL BASE DE LA APLICACIÓN
// ============================================================
// Detección automática del protocolo (HTTP/HTTPS)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    ? 'https'
    : 'http';

// URL base de la aplicación (sin barra final)
define('BASE_URL', $protocol . '://' . $_SERVER['HTTP_HOST'] . '/siges');

// ============================================================
// ZONA HORARIA Y LOCALE
// ============================================================
date_default_timezone_set('America/La_Paz');
setlocale(LC_TIME, 'es_BO.UTF-8', 'es_ES.UTF-8', 'Spanish');

// ============================================================
// CONFIGURACIÓN DE SESIÓN
// ============================================================
define('SESSION_NAME', 'siges_session');
define('SESSION_LIFETIME', 7200); // 2 horas en segundos

// ============================================================
// CONFIGURACIÓN DE SEGURIDAD
// ============================================================
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_MINUTES', 15);

// ============================================================
// CABECERAS DE SEGURIDAD HTTP
// ============================================================
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-XSS-Protection: 1; mode=block');


