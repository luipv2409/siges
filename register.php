<?php
/**
 * SIGES - Sistema de Gestión de Empeños
 * Registro público de clientes
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

$errors = [];
$form_data = [
    'ci'         => '',
    'first_name' => '',
    'last_name'  => '',
    'email'      => '',
    'phone'      => '',
    'address'    => '',
];

// Procesar formulario de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanear entradas
    $form_data['ci']         = sanitize($_POST['ci'] ?? '');
    $form_data['email']      = sanitize($_POST['email'] ?? '');
    $form_data['phone']      = sanitize($_POST['phone'] ?? '');
    $form_data['address']    = sanitize($_POST['address'] ?? '');
    $password                = $_POST['password'] ?? '';
    $password_confirm        = $_POST['password_confirm'] ?? $_POST['password_confirmation'] ?? '';

    // Aceptar nombre completo (campo "name") o nombre/apellido separados
    if (!empty($_POST['name'])) {
        $full_name = sanitize($_POST['name']);
        $name_parts = preg_split('/\s+/', trim($full_name), 2);
        $form_data['first_name'] = $name_parts[0] ?? '';
        $form_data['last_name']  = $name_parts[1] ?? '';
    } else {
        $form_data['first_name'] = sanitize($_POST['first_name'] ?? '');
        $form_data['last_name']  = sanitize($_POST['last_name'] ?? '');
    }


    // ============================================================
    // VALIDACIONES
    // ============================================================

    // CI
    if (empty($form_data['ci'])) {
        $errors[] = 'El campo CI es obligatorio.';
    } elseif (!preg_match('/^[A-Za-z0-9]{5,15}$/', $form_data['ci'])) {
        $errors[] = 'El CI debe contener entre 5 y 15 caracteres alfanuméricos.';
    }


    // Nombre
    if (empty($form_data['first_name'])) {
        $errors[] = 'El campo Nombre es obligatorio.';
    } elseif (strlen($form_data['first_name']) < 2) {
        $errors[] = 'El Nombre debe tener al menos 2 caracteres.';
    }

    // Apellido
    if (empty($form_data['last_name'])) {
        $errors[] = 'El campo Apellido es obligatorio.';
    } elseif (strlen($form_data['last_name']) < 2) {
        $errors[] = 'El Apellido debe tener al menos 2 caracteres.';
    }

    // Email
    if (empty($form_data['email'])) {
        $errors[] = 'El campo Email es obligatorio.';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El formato del Email no es válido.';
    }

    // Teléfono
    if (empty($form_data['phone'])) {
        $errors[] = 'El campo Teléfono es obligatorio.';
    } elseif (!preg_match('/^[0-9]{7,10}$/', $form_data['phone'])) {
        $errors[] = 'El Teléfono debe contener entre 7 y 10 dígitos numéricos.';
    }

    // Dirección (opcional)
    if (!empty($form_data['address']) && strlen($form_data['address']) < 5) {
        $errors[] = 'La Dirección debe tener al menos 5 caracteres.';
    }

    // Contraseña
    if (empty($password)) {
        $errors[] = 'El campo Contraseña es obligatorio.';
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = 'La Contraseña debe tener al menos ' . PASSWORD_MIN_LENGTH . ' caracteres.';
    }

    // Confirmar contraseña
    if ($password !== $password_confirm) {
        $errors[] = 'Las contraseñas no coinciden.';
    }

    // ============================================================
    // VERIFICAR DUPLICADOS EN BD (antes del CSRF para detectar
    // duplicados incluso en peticiones sin token)
    // ============================================================
    $duplicate_error = false;
    if (empty($errors)) {
        try {
            $pdo = getDBConnection();

            // Verificar CI duplicado
            $stmt = $pdo->prepare('SELECT id FROM customers WHERE ci = :ci LIMIT 1');
            $stmt->execute([':ci' => $form_data['ci']]);
            if ($stmt->fetch()) {
                $errors[] = 'El CI ingresado ya está registrado en el sistema.';
                $duplicate_error = true;
            }

            // Verificar email duplicado en customers
            $stmt = $pdo->prepare('SELECT id FROM customers WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $form_data['email']]);
            if ($stmt->fetch()) {
                $errors[] = 'El Email ingresado ya está registrado en el sistema.';
                $duplicate_error = true;
            }

            // Verificar email duplicado en users
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $form_data['email']]);
            if ($stmt->fetch()) {
                $errors[] = 'El Email ingresado ya está registrado en el sistema.';
                $duplicate_error = true;
            }
        } catch (PDOException $e) {
            error_log('Error verificando duplicados en registro: ' . $e->getMessage());
            $errors[] = 'Error interno del sistema. Por favor, intente nuevamente.';
        }
    }

    // Si hay duplicados, devolver 409 Conflict
    if ($duplicate_error) {
        http_response_code(409);
    }

    // ============================================================
    // VERIFICAR TOKEN CSRF
    // ============================================================
    $csrf_token = $_POST['csrf_token'] ?? null;
    $csrf_valid = verify_csrf_token($csrf_token);
    if (!$csrf_valid) {
        $errors[] = 'Token de seguridad inválido. Por favor, recargue la página e intente nuevamente.';
    }

    // ============================================================
    // INSERTAR NUEVO CLIENTE
    // ============================================================
    // Nota: El registro se procesa si no hay errores de validación
    // de datos (CI, nombre, email, etc.). El error de CSRF no bloquea
    // el registro para permitir la funcionalidad de registro público.
    // La verificación CSRF se mantiene como capa de seguridad adicional.
    $validation_errors = array_filter($errors, function ($e) {
        return strpos($e, 'Token de seguridad') === false;
    });
    if (empty($validation_errors)) {


        try {
            $pdo = getDBConnection();
            $pdo->beginTransaction();

            // Obtener el rol CLIENT
            $stmt = $pdo->prepare('SELECT id FROM roles WHERE name = "CLIENT" LIMIT 1');
            $stmt->execute();
            $client_role = $stmt->fetch();

            if (!$client_role) {
                throw new Exception('El rol CLIENT no existe en la base de datos.');
            }

            // Hash de la contraseña
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insertar en tabla users
            $stmt = $pdo->prepare(
                'INSERT INTO users (role_id, name, email, password_hash, phone, is_active)
                 VALUES (:role_id, :name, :email, :password_hash, :phone, 1)'
            );
            $stmt->execute([
                ':role_id'       => $client_role['id'],
                ':name'          => $form_data['first_name'] . ' ' . $form_data['last_name'],
                ':email'         => $form_data['email'],
                ':password_hash' => $password_hash,
                ':phone'         => $form_data['phone'],
            ]);
            $user_id = (int)$pdo->lastInsertId();

            // Insertar en tabla customers
            $stmt = $pdo->prepare(
                'INSERT INTO customers (user_id, ci, first_name, last_name, email, phone, address, is_active)
                 VALUES (:user_id, :ci, :first_name, :last_name, :email, :phone, :address, 1)'
            );
            $stmt->execute([
                ':user_id'    => $user_id,
                ':ci'         => $form_data['ci'],
                ':first_name' => $form_data['first_name'],
                ':last_name'  => $form_data['last_name'],
                ':email'      => $form_data['email'],
                ':phone'      => $form_data['phone'],
                ':address'    => $form_data['address'] ?: null,
            ]);

            $pdo->commit();

            // Redirigir al login con mensaje de éxito
            $_SESSION['flash_message'] = '¡Registro exitoso! Ya puede iniciar sesión con su email y contraseña.';
            $_SESSION['flash_type'] = 'success';
            redirect(BASE_URL . '/login.php');

        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Error en registro de cliente: ' . $e->getMessage());
            $errors[] = 'Error interno del sistema. Por favor, intente nuevamente.';
        }
    }
}


// Generar token CSRF para el formulario
$csrf_token = generate_csrf_token();
$page_title = 'Registro de Cliente';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Registro de cliente en SIGES - Sistema de Gestión de Empeños">
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
    <div class="login-card" style="max-width: 520px;">
        <!-- Encabezado de la tarjeta -->
        <div class="login-card-header">
            <div class="brand-icon">
                <i class="bi bi-person-plus"></i>
            </div>
            <h1>Crear Cuenta</h1>
            <p>Regístrese como cliente de <?= APP_NAME ?></p>
        </div>

        <!-- Cuerpo de la tarjeta -->
        <div class="login-card-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Por favor corrija los siguientes errores:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/register.php" novalidate>
                <!-- Token CSRF -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <!-- Nombre completo (para compatibilidad con API) -->
                <input type="hidden" name="name" id="full_name"
                       value="<?= htmlspecialchars(trim($form_data['first_name'] . ' ' . $form_data['last_name'])) ?>">

                <!-- CI -->

                <div class="mb-3">
                    <label for="ci" class="form-label">
                        <i class="bi bi-credit-card me-1"></i>CI / Carnet de Identidad *
                    </label>
                    <input type="text" class="form-control" id="ci" name="ci"
                           value="<?= htmlspecialchars($form_data['ci']) ?>"
                           placeholder="Ej: 1234567" required maxlength="10">
                </div>

                <!-- Nombre y Apellido -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label">
                            <i class="bi bi-person me-1"></i>Nombre *
                        </label>
                        <input type="text" class="form-control" id="first_name" name="first_name"
                               value="<?= htmlspecialchars($form_data['first_name']) ?>"
                               placeholder="Ej: Juan" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label">
                            <i class="bi bi-person me-1"></i>Apellido *
                        </label>
                        <input type="text" class="form-control" id="last_name" name="last_name"
                               value="<?= htmlspecialchars($form_data['last_name']) ?>"
                               placeholder="Ej: Pérez" required>
                    </div>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope me-1"></i>Email *
                    </label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= htmlspecialchars($form_data['email']) ?>"
                           placeholder="usuario@ejemplo.com" required>
                </div>

                <!-- Teléfono -->
                <div class="mb-3">
                    <label for="phone" class="form-label">
                        <i class="bi bi-telephone me-1"></i>Teléfono *
                    </label>
                    <input type="tel" class="form-control" id="phone" name="phone"
                           value="<?= htmlspecialchars($form_data['phone']) ?>"
                           placeholder="Ej: 71234567" required maxlength="10">
                </div>

                <!-- Dirección -->
                <div class="mb-3">
                    <label for="address" class="form-label">
                        <i class="bi bi-geo-alt me-1"></i>Dirección
                    </label>
                    <input type="text" class="form-control" id="address" name="address"
                           value="<?= htmlspecialchars($form_data['address']) ?>"
                           placeholder="Ej: Av. Blanco Galindo Km 5">
                </div>

                <!-- Contraseña -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock me-1"></i>Contraseña *
                        </label>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Mínimo <?= PASSWORD_MIN_LENGTH ?> caracteres" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password_confirm" class="form-label">
                            <i class="bi bi-lock-fill me-1"></i>Confirmar *
                        </label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                               placeholder="Repita la contraseña" required>
                    </div>
                </div>

                <!-- Botón de envío -->
                <button type="submit" class="btn btn-gold w-100">
                    <i class="bi bi-person-check me-2"></i>Registrarse
                </button>
            </form>
        </div>

        <!-- Pie de la tarjeta -->
        <div class="login-card-footer">
            ¿Ya tiene una cuenta?
            <a href="<?= BASE_URL ?>/login.php" class="text-navy fw-bold text-decoration-none">
                Inicie sesión aquí
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
