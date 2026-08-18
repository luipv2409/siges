<?php
/**
 * SIGES - Sistema de Gestión de Empeños
 * Página de inicio de sesión
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

$error = '';
$email = '';

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF
    $csrf_token = $_POST['csrf_token'] ?? null;
    if (!verify_csrf_token($csrf_token)) {
        $error = 'Token de seguridad inválido. Por favor, recargue la página e intente nuevamente.';
    } else {
        // Sanear entradas
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validar campos vacíos
        if (empty($email) || empty($password)) {
            $error = 'Por favor, complete todos los campos.';
        } else {
            try {
                $pdo = getDBConnection();

                // Buscar usuario por email
                $stmt = $pdo->prepare(
                    'SELECT u.id, u.name, u.email, u.password_hash, u.is_active, r.name AS role_name
                     FROM users u
                     INNER JOIN roles r ON u.role_id = r.id
                     WHERE u.email = :email
                     LIMIT 1'
                );
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch();

                // Verificar usuario existe y está activo
                if (!$user) {
                    $error = 'Credenciales incorrectas. Verifique su email y contraseña.';
                } elseif ((int)$user['is_active'] !== 1) {
                    $error = 'Su cuenta está desactivada. Contacte al administrador del sistema.';
                } elseif (!password_verify($password, $user['password_hash'])) {
                    $error = 'Credenciales incorrectas. Verifique su email y contraseña.';
                } else {
                    // Autenticación exitosa
                    session_regenerate_id(true); // Prevenir session fixation

                    $_SESSION['user_id'] = (int)$user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role_name'];

                    // Actualizar último login
                    $updateStmt = $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
                    $updateStmt->execute([':id' => $user['id']]);

                    // Redirigir al dashboard
                    redirect(BASE_URL . '/dashboard.php');
                }
            } catch (PDOException $e) {
                error_log('Error en login: ' . $e->getMessage());
                $error = 'Error interno del sistema. Por favor, intente nuevamente.';
            }
        }
    }
}

// Generar token CSRF para el formulario
$csrf_token = generate_csrf_token();
$page_title = 'Iniciar Sesión';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Iniciar sesión en SIGES - Sistema de Gestión de Empeños">
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
                <i class="bi bi-bank"></i>
            </div>
            <h1><?= APP_NAME ?></h1>
            <p><?= APP_FULL_NAME ?></p>
        </div>

        <!-- Cuerpo de la tarjeta -->
        <div class="login-card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/login.php" novalidate>
                <!-- Token CSRF -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope me-1"></i>Email
                    </label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= htmlspecialchars($email) ?>" placeholder="usuario@ejemplo.com"
                           required autofocus>
                </div>

                <!-- Contraseña -->
                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock me-1"></i>Contraseña
                    </label>
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="Ingrese su contraseña" required>
                </div>

                <!-- Recordar / Recuperar -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label small" for="remember">Recordarme</label>
                    </div>
                    <a href="<?= BASE_URL ?>/recover.php" class="small text-navy text-decoration-none">
                        ¿Olvidó su contraseña?
                    </a>
                </div>

                <!-- Botón de envío -->
                <button type="submit" class="btn btn-gold w-100">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                </button>
            </form>
        </div>

        <!-- Pie de la tarjeta -->
        <div class="login-card-footer">
            ¿No tiene una cuenta?
            <a href="<?= BASE_URL ?>/register.php" class="text-navy fw-bold text-decoration-none">
                Regístrese aquí
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
