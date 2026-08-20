<?php
/**
 * SIGES - Sistema de Gestión de Empeños
 * Recuperación de contraseña (flujo simulado)
 *
 * Sprint 1 - Autenticación y Seguridad
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
$email = '';

// Procesar formulario de recuperación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF
    $csrf_token = $_POST['csrf_token'] ?? null;
    if (!verify_csrf_token($csrf_token)) {
        $message = 'Token de seguridad inválido. Por favor, recargue la página e intente nuevamente.';
        $message_type = 'danger';
    } else {
        $email = sanitize($_POST['email'] ?? '');

        if (empty($email)) {
            $message = 'Por favor, ingrese su email.';
            $message_type = 'danger';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'El formato del email no es válido.';
            $message_type = 'danger';
        } else {
            try {
                $pdo = getDBConnection();

                // Buscar si el email existe en users o customers
                $stmt = $pdo->prepare(
                    'SELECT id, email FROM users WHERE email = :email
                     UNION
                     SELECT id, email FROM customers WHERE email = :email
                     LIMIT 1'
                );
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch();

                // FLUJO SIMULADO:
                // En un sistema real, aquí se enviaría un email con un enlace de recuperación.
                // Por seguridad, siempre mostramos el mismo mensaje (éxito o no).
                if ($user) {
                    // Generar token de recuperación
                    $reset_token = bin2hex(random_bytes(32));
                    $token_hash = hash('sha256', $reset_token);
                    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

                    // Guardar token en tabla password_resets
                    $stmt = $pdo->prepare(
                        'INSERT INTO password_resets (user_id, token_hash, expires_at)
                         VALUES (:user_id, :token_hash, :expires_at)'
                    );
                    $stmt->execute([
                        ':user_id'    => $user['id'],
                        ':token_hash' => $token_hash,
                        ':expires_at' => $expires_at,
                    ]);

                    $reset_link = BASE_URL . '/reset_password.php?token=' . $reset_token;

                    // Registrar en log (simulación)
                    error_log('[RECOVER] Enlace de recuperación para ' . $email . ': ' . $reset_link);

                    $message = 'Se ha enviado un enlace de recuperación a su email. '
                             . 'Por favor, revise su bandeja de entrada. (Simulado)';
                    $message_type = 'success';
                } else {
                    // No revelar si el email existe o no (seguridad)
                    $message = 'Si el email está registrado, recibirá un enlace de recuperación. '
                             . 'Por favor, revise su bandeja de entrada.';
                    $message_type = 'success';
                }
            } catch (PDOException $e) {
                error_log('Error en recuperación de contraseña: ' . $e->getMessage());
                $message = 'Error interno del sistema. Por favor, intente nuevamente.';
                $message_type = 'danger';
            }

        }
    }
}

// Generar token CSRF para el formulario
$csrf_token = generate_csrf_token();
$page_title = 'Recuperar Contraseña';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Recuperar contraseña en SIGES - Sistema de Gestión de Empeños">
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
                <i class="bi bi-key"></i>
            </div>
            <h1>Recuperar Contraseña</h1>
            <p>Le enviaremos un enlace para restablecerla</p>
        </div>

        <!-- Cuerpo de la tarjeta -->
        <div class="login-card-body">
            <?php if ($message): ?>
                <div class="alert alert-<?= $message_type ?> d-flex align-items-center" role="alert">
                    <i class="bi <?= $message_type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> me-2"></i>
                    <div><?= htmlspecialchars($message) ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/recover.php" novalidate>
                <!-- Token CSRF -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope me-1"></i>Email
                    </label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= htmlspecialchars($email) ?>"
                           placeholder="usuario@ejemplo.com" required autofocus>
                </div>

                <!-- Botón de envío -->
                <button type="submit" class="btn btn-gold w-100">
                    <i class="bi bi-envelope-arrow-up me-2"></i>Enviar Enlace
                </button>
            </form>

            <hr class="my-4">

            <div class="text-center">
                <a href="<?= BASE_URL ?>/login.php" class="text-navy text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i>Volver al inicio de sesión
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
