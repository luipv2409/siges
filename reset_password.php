<?php
/**
 * SIGES - Sistema de Gestión de Empeños
 * Restablecimiento de contraseña (flujo simulado)
 *
 * Sprint 1 - Autenticación y Seguridad (SGS-36/37)
 * Valida el token de recuperación y permite cambiar la contraseña.
 */

// Configuración y helpers
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/security.php';
require_once __DIR__ . '/helpers/auth.php';

// Asegurar sesión
ensure_session();

// Si ya está autenticado, redirigir al dashboard
if (is_logged_in()) {
    redirect(BASE_URL . '/dashboard.php');
}

$message = '';
$message_type = '';
$token = $_GET['token'] ?? $_POST['token'] ?? '';

// Validar que se proporcione un token
if (empty($token)) {
    $message = 'Token de recuperación no proporcionado.';
    $message_type = 'danger';
}

// Procesar formulario de cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($token)) {
    // Verificar token CSRF
    $csrf_token = $_POST['csrf_token'] ?? null;
    if (!verify_csrf_token($csrf_token)) {
        $message = 'Token de seguridad inválido. Por favor, recargue la página e intente nuevamente.';
        $message_type = 'danger';
        // Devolver 400 Bad Request para token CSRF inválido
        http_response_code(400);
    } else {

        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        // Validar contraseña
        if (empty($password)) {
            $message = 'El campo Nueva Contraseña es obligatorio.';
            $message_type = 'danger';
        } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
            $message = 'La Contraseña debe tener al menos ' . PASSWORD_MIN_LENGTH . ' caracteres.';
            $message_type = 'danger';
        } elseif ($password !== $password_confirm) {
            $message = 'Las contraseñas no coinciden.';
            $message_type = 'danger';
        } else {
            try {
                $pdo = getDBConnection();

                // Hash del token para buscar en la BD
                $token_hash = hash('sha256', $token);

                // Buscar token válido (no usado y no expirado)
                $stmt = $pdo->prepare(
                    'SELECT pr.id, pr.user_id, pr.expires_at
                     FROM password_resets pr
                     WHERE pr.token_hash = :token_hash
                       AND pr.used_at IS NULL
                       AND pr.expires_at > NOW()
                     LIMIT 1'
                );
                $stmt->execute([':token_hash' => $token_hash]);
                $reset = $stmt->fetch();

                if (!$reset) {
                    $message = 'El enlace de recuperación es inválido o ha expirado. Por favor, solicite uno nuevo.';
                    $message_type = 'danger';
                    // Devolver 400 Bad Request para token inválido
                    http_response_code(400);
                } else {

                    // Actualizar contraseña del usuario
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);

                    $pdo->beginTransaction();

                    $stmt = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
                    $stmt->execute([
                        ':hash' => $password_hash,
                        ':id'   => $reset['user_id'],
                    ]);

                    // Marcar token como usado
                    $stmt = $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = :id');
                    $stmt->execute([':id' => $reset['id']]);

                    $pdo->commit();

                    log_audit('RESET_PASSWORD', 'Contraseña restablecida para el usuario ID ' . $reset['user_id']);

                    // Redirigir al login con mensaje de éxito
                    $_SESSION['flash_message'] = '¡Contraseña actualizada correctamente! Ya puede iniciar sesión con su nueva contraseña.';
                    $_SESSION['flash_type'] = 'success';
                    redirect(BASE_URL . '/login.php');
                }
            } catch (PDOException $e) {
                if (isset($pdo) && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log('Error en restablecimiento de contraseña: ' . $e->getMessage());
                $message = 'Error interno del sistema. Por favor, intente nuevamente.';
                $message_type = 'danger';
            }
        }
    }
}

// Generar token CSRF para el formulario
$csrf_token = generate_csrf_token();
$page_title = 'Restablecer Contraseña';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Restablecer contraseña en SIGES - Sistema de Gestión de Empeños">
    <meta name="theme-color" content="#0B192C">
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/icons/icon-32x32.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css">
    <title><?= $page_title ?> | <?= APP_NAME ?></title>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">
        <!-- Encabezado de la tarjeta -->
        <div class="login-card-header">
            <div class="brand-icon">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h1>Restablecer Contraseña</h1>
            <p>Ingrese su nueva contraseña</p>
        </div>

        <!-- Cuerpo de la tarjeta -->
        <div class="login-card-body">
            <?php if ($message): ?>
                <div class="alert alert-<?= $message_type ?> d-flex align-items-center" role="alert">
                    <i class="bi <?= $message_type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> me-2"></i>
                    <div><?= htmlspecialchars($message) ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($token) && $message_type !== 'danger'): ?>
                <form method="POST" action="<?= BASE_URL ?>/reset_password.php?token=<?= urlencode($token) ?>" novalidate>
                    <!-- Token CSRF -->
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                    <!-- Nueva Contraseña -->
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock me-1"></i>Nueva Contraseña *
                        </label>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Mínimo <?= PASSWORD_MIN_LENGTH ?> caracteres" required autofocus>
                    </div>

                    <!-- Confirmar Contraseña -->
                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">
                            <i class="bi bi-lock-fill me-1"></i>Confirmar Contraseña *
                        </label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                               placeholder="Repita la contraseña" required>
                    </div>

                    <!-- Botón de envío -->
                    <button type="submit" class="btn btn-gold w-100">
                        <i class="bi bi-check-circle me-2"></i>Actualizar Contraseña
                    </button>
                </form>

                <hr class="my-4">

                <div class="text-center">
                    <a href="<?= BASE_URL ?>/login.php" class="text-navy text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i>Volver al inicio de sesión
                    </a>
                </div>
            <?php else: ?>
                <div class="text-center">
                    <a href="<?= BASE_URL ?>/recover.php" class="btn btn-gold">
                        <i class="bi bi-envelope-arrow-up me-2"></i>Solicitar Nuevo Enlace
                    </a>
                    <div class="mt-3">
                        <a href="<?= BASE_URL ?>/login.php" class="text-navy text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i>Volver al inicio de sesión
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
