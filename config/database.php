<?php
/**
 * SIGES - Sistema de Gestión de Empeños
 * Configuración de conexión a la base de datos MySQL mediante PDO
 *
 * Fase 1 - Configuración inicial
 */

// ============================================================
// CONSTANTES DE CONEXIÓN
// ============================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'siges');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ============================================================
// OPCIONES PDO
// ============================================================
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones en errores
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve arrays asociativos
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Usa prepared statements reales
    PDO::ATTR_PERSISTENT         => false,                  // Sin conexiones persistentes
]);

/**
 * Obtiene una instancia única de conexión PDO (patrón Singleton).
 *
 * @return PDO Instancia de conexión a la base de datos.
 * @throws PDOException Si falla la conexión.
 */
function getDBConnection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, DB_OPTIONS);
        } catch (PDOException $e) {
            // En producción, registrar el error en un log en lugar de mostrarlo.
            error_log('Error de conexión a la base de datos: ' . $e->getMessage());
            die('Error de conexión a la base de datos. Por favor, contacte al administrador del sistema.');
        }
    }

    return $pdo;
}
