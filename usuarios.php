<?php
/**
 * SIGES - Sistema de Gestión de Empeños
 * Panel de gestión de empleados (solo OWNER)
 *
 * Sprint 1 - Autenticación y Seguridad (SGS-34)
 * Permite al OWNER registrar, listar, editar y desactivar EMPLOYEE.
 */

// Configuración y helpers
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/security.php';
require_once __DIR__ . '/helpers/auth.php';

// Asegurar sesión
ensure_session();

// Proteger la página: requiere autenticación y rol OWNER
if (!is_logged_in()) {
    redirect(BASE_URL . '/login.php');
}

if (!has_role('OWNER')) {
    // Registrar intento de acceso no autorizado
    log_audit('ACCESO_NO_AUTORIZADO', 'Intento de acceso al panel de usuarios sin rol OWNER.');
    redirect(BASE_URL . '/dashboard.php');
}

$errors = [];
$success = '';
$form_data = [
    'id'    => '',
    'name'  => '',
    'email' => '',
    'phone' => '',
];

// ============================================================
// PROCESAR ACCIONES
// ============================================================

// Acción: Desactivar/Activar empleado
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $user_id = (int)$_GET['id'];

    if ($action === 'toggle' && $user_id > 0) {
        try {
            $pdo = getDBConnection();

            // Verificar que el usuario existe y es EMPLOYEE
            $stmt = $pdo->prepare(
                'SELECT u.id, u.is_active, r.name AS role_name
                 FROM users u
                 INNER JOIN roles r ON u.role_id = r.id
                 WHERE u.id = :id AND r.name = "EMPLOYEE"
                 LIMIT 1'
            );
            $stmt->execute([':id' => $user_id]);
            $user = $stmt->fetch();

            if ($user) {
                $new_status = $user['is_active'] ? 0 : 1;
                $stmt = $pdo->prepare('UPDATE users SET is_active = :status WHERE id = :id');
                $stmt->execute([':status' => $new_status, ':id' => $user_id]);

                log_audit(
                    $new_status ? 'ACTIVAR_EMPLEADO' : 'DESACTIVAR_EMPLEADO',
                    'Empleado ID ' . $user_id . ($new_status ? ' activado' : ' desactivado')
                );

                $success = $new_status
                    ? 'El empleado ha sido activado correctamente.'
                    : 'El empleado ha sido desactivado correctamente.';
            } else {
                $errors[] = 'El usuario no existe o no es un empleado.';
            }
        } catch (PDOException $e) {
            error_log('Error al cambiar estado de empleado: ' . $e->getMessage());
            $errors[] = 'Error interno del sistema. Por favor, intente nuevamente.';
        }
    }
}

// Acción: Editar empleado (cargar datos en el formulario)
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            'SELECT u.id, u.name, u.email, u.phone, u.is_active
             FROM users u
             INNER JOIN roles r ON u.role_id = r.id
             WHERE u.id = :id AND r.name = "EMPLOYEE"
             LIMIT 1'
        );
        $stmt->execute([':id' => $user_id]);
        $user = $stmt->fetch();

        if ($user) {
            $form_data = [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
            ];
        } else {
            $errors[] = 'El usuario no existe o no es un empleado.';
        }
    } catch (PDOException $e) {
        error_log('Error al cargar empleado: ' . $e->getMessage());
        $errors[] = 'Error interno del sistema. Por favor, intente nuevamente.';
    }
}

// Procesar formulario (crear o actualizar empleado)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF
    $csrf_token = $_POST['csrf_token'] ?? null;
    if (!verify_csrf_token($csrf_token)) {
        $errors[] = 'Token de seguridad inválido. Por favor, recargue la página e intente nuevamente.';
    } else {
        $form_data['id']    = (int)($_POST['id'] ?? 0);
        $form_data['name']  = sanitize($_POST['name'] ?? '');
        $form_data['email'] = sanitize($_POST['email'] ?? '');
        $form_data['phone'] = sanitize($_POST['phone'] ?? '');
        $password           = $_POST['password'] ?? '';
        $password_confirm   = $_POST['password_confirm'] ?? '';

        // ============================================================
        // VALIDACIONES
        // ============================================================

        // Nombre
        if (empty($form_data['name'])) {
            $errors[] = 'El campo Nombre es obligatorio.';
        } elseif (strlen($form_data['name']) < 3) {
            $errors[] = 'El Nombre debe tener al menos 3 caracteres.';
        }

        // Email
        if (empty($form_data['email'])) {
            $errors[] = 'El campo Email es obligatorio.';
        } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El formato del Email no es válido.';
        }

        // Teléfono (opcional)
        if (!empty($form_data['phone']) && !preg_match('/^[0-9]{7,10}$/', $form_data['phone'])) {
            $errors[] = 'El Teléfono debe contener entre 7 y 10 dígitos numéricos.';
        }

        // Contraseña (solo requerida al crear)
        if ($form_data['id'] === 0) {
            if (empty($password)) {
                $errors[] = 'El campo Contraseña es obligatorio.';
            } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
                $errors[] = 'La Contraseña debe tener al menos ' . PASSWORD_MIN_LENGTH . ' caracteres.';
            }

            if ($password !== $password_confirm) {
                $errors[] = 'Las contraseñas no coinciden.';
            }
        }

        // ============================================================
        // VERIFICAR DUPLICADOS EN BD
        // ============================================================
        if (empty($errors)) {
            try {
                $pdo = getDBConnection();

                // Verificar email duplicado (excluyendo el usuario actual si se edita)
                $stmt = $pdo->prepare(
                    'SELECT id FROM users WHERE email = :email AND id != :current_id LIMIT 1'
                );
                $stmt->execute([
                    ':email'      => $form_data['email'],
                    ':current_id' => $form_data['id'],
                ]);
                if ($stmt->fetch()) {
                    $errors[] = 'El Email ingresado ya está registrado en el sistema.';
                }
            } catch (PDOException $e) {
                error_log('Error verificando duplicados: ' . $e->getMessage());
                $errors[] = 'Error interno del sistema. Por favor, intente nuevamente.';
            }
        }

        // ============================================================
        // INSERTAR O ACTUALIZAR EMPLEADO
        // ============================================================
        if (empty($errors)) {
            try {
                $pdo = getDBConnection();

                if ($form_data['id'] === 0) {
                    // CREAR NUEVO EMPLEADO
                    $pdo->beginTransaction();

                    // Obtener el rol EMPLOYEE
                    $stmt = $pdo->prepare('SELECT id FROM roles WHERE name = "EMPLOYEE" LIMIT 1');
                    $stmt->execute();
                    $employee_role = $stmt->fetch();

                    if (!$employee_role) {
                        throw new Exception('El rol EMPLOYEE no existe en la base de datos.');
                    }

                    // Hash de la contraseña temporal
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);

                    // Insertar en tabla users
                    $stmt = $pdo->prepare(
                        'INSERT INTO users (role_id, name, email, password_hash, phone, is_active)
                         VALUES (:role_id, :name, :email, :password_hash, :phone, 1)'
                    );
                    $stmt->execute([
                        ':role_id'       => $employee_role['id'],
                        ':name'          => $form_data['name'],
                        ':email'         => $form_data['email'],
                        ':password_hash' => $password_hash,
                        ':phone'         => $form_data['phone'] ?: null,
                    ]);
                    $new_user_id = (int)$pdo->lastInsertId();

                    $pdo->commit();

                    log_audit('CREAR_EMPLEADO', 'Nuevo empleado creado: ' . $form_data['email'] . ' (ID: ' . $new_user_id . ')');

                    $success = 'Empleado registrado correctamente.';
                } else {
                    // ACTUALIZAR EMPLEADO EXISTENTE
                    $pdo->beginTransaction();

                    $stmt = $pdo->prepare(
                        'UPDATE users SET name = :name, email = :email, phone = :phone WHERE id = :id'
                    );
                    $stmt->execute([
                        ':name'  => $form_data['name'],
                        ':email' => $form_data['email'],
                        ':phone' => $form_data['phone'] ?: null,
                        ':id'    => $form_data['id'],
                    ]);

                    // Si se proporcionó nueva contraseña, actualizarla
                    if (!empty($password)) {
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
                        $stmt->execute([':hash' => $password_hash, ':id' => $form_data['id']]);
                    }

                    $pdo->commit();

                    log_audit('EDITAR_EMPLEADO', 'Empleado actualizado: ' . $form_data['email'] . ' (ID: ' . $form_data['id'] . ')');

                    $success = 'Empleado actualizado correctamente.';
                }

                // Limpiar formulario después de guardar
                $form_data = ['id' => '', 'name' => '', 'email' => '', 'phone' => ''];

            } catch (Exception $e) {
                if (isset($pdo) && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log('Error al guardar empleado: ' . $e->getMessage());
                $errors[] = 'Error interno del sistema. Por favor, intente nuevamente.';
            }
        }
    }
}

// ============================================================
// OBTENER LISTA DE EMPLEADOS
// ============================================================
$employees = [];
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare(
        'SELECT u.id, u.name, u.email, u.phone, u.is_active, u.last_login_at, u.created_at
         FROM users u
         INNER JOIN roles r ON u.role_id = r.id
         WHERE r.name = "EMPLOYEE"
         ORDER BY u.created_at DESC'
    );
    $stmt->execute();
    $employees = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Error obteniendo empleados: ' . $e->getMessage());
    $errors[] = 'Error interno del sistema. Por favor, intente nuevamente.';
}

// Generar token CSRF para el formulario
$csrf_token = generate_csrf_token();
$page_title = 'Gestión de Empleados';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Gestión de empleados en SIGES - Sistema de Gestión de Empeños">
    <meta name="theme-color" content="#0B192C">
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/icons/icon-32x32.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css">
    <title><?= $page_title ?> | <?= APP_NAME ?></title>
</head>
<body>

<!-- Incluir header con navbar dinámico -->
<?php include __DIR__ . '/includes/header.php'; ?>

<!-- Encabezado de la página -->
<div class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-1">
                    <i class="bi bi-people me-2"></i>Gestión de Empleados
                </h1>
                <p class="mb-0 text-white-50">
                    Registre, edite y administre los empleados del sistema
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <span class="badge badge-gold fs-6">
                    <i class="bi bi-person-badge me-1"></i>OWNER
                </span>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <!-- Mensajes de éxito -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Mensajes de error -->
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

    <div class="row">
        <!-- Formulario de registro/edición -->
        <div class="col-lg-4 mb-4">
            <div class="card card-siges">
                <div class="card-header bg-navy text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-plus me-2"></i>
                        <?= $form_data['id'] ? 'Editar Empleado' : 'Registrar Empleado' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/usuarios.php" novalidate>
                        <!-- Token CSRF -->
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="id" value="<?= (int)$form_data['id'] ?>">

                        <!-- Nombre -->
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="bi bi-person me-1"></i>Nombre Completo *
                            </label>
                            <input type="text" class="form-control" id="name" name="name"
                                   value="<?= htmlspecialchars($form_data['name']) ?>"
                                   placeholder="Ej: Juan Pérez" required>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope me-1"></i>Email *
                            </label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= htmlspecialchars($form_data['email']) ?>"
                                   placeholder="empleado@ejemplo.com" required>
                        </div>

                        <!-- Teléfono -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">
                                <i class="bi bi-telephone me-1"></i>Teléfono
                            </label>
                            <input type="tel" class="form-control" id="phone" name="phone"
                                   value="<?= htmlspecialchars($form_data['phone']) ?>"
                                   placeholder="Ej: 71234567" maxlength="10">
                        </div>

                        <?php if ($form_data['id']): ?>
                            <!-- Al editar, la contraseña es opcional -->
                            <div class="alert alert-info py-2 small">
                                <i class="bi bi-info-circle me-1"></i>
                                Deje la contraseña en blanco para mantener la actual.
                            </div>
                        <?php endif; ?>

                        <!-- Contraseña -->
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock me-1"></i>
                                <?= $form_data['id'] ? 'Nueva Contraseña' : 'Contraseña Temporal *' ?>
                            </label>
                            <input type="password" class="form-control" id="password" name="password"
                                   placeholder="<?= $form_data['id'] ? 'Dejar en blanco para no cambiar' : 'Mínimo ' . PASSWORD_MIN_LENGTH . ' caracteres' ?>"
                                   <?= $form_data['id'] ? '' : 'required' ?>>
                        </div>

                        <!-- Confirmar Contraseña -->
                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">
                                <i class="bi bi-lock-fill me-1"></i>Confirmar Contraseña
                            </label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                                   placeholder="Repita la contraseña"
                                   <?= $form_data['id'] ? '' : 'required' ?>>
                        </div>

                        <!-- Botones -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-gold">
                                <i class="bi bi-check-lg me-2"></i>
                                <?= $form_data['id'] ? 'Actualizar Empleado' : 'Registrar Empleado' ?>
                            </button>
                            <?php if ($form_data['id']): ?>
                                <a href="<?= BASE_URL ?>/usuarios.php" class="btn btn-outline-navy">
                                    <i class="bi bi-x-lg me-2"></i>Cancelar Edición
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Lista de empleados -->
        <div class="col-lg-8">
            <div class="card card-siges">
                <div class="card-header bg-navy text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul me-2"></i>Empleados Registrados
                        <span class="badge bg-gold text-navy ms-2"><?= count($employees) ?></span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($employees)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-people fs-1 text-muted d-block mb-3"></i>
                            <p class="text-muted mb-0">No hay empleados registrados todavía.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Empleado</th>
                                        <th>Contacto</th>
                                        <th>Estado</th>
                                        <th>Último Acceso</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($employees as $emp): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-person-circle fs-4 text-navy me-2"></i>
                                                    <div>
                                                        <div class="fw-bold"><?= htmlspecialchars($emp['name']) ?></div>
                                                        <small class="text-muted">ID: <?= $emp['id'] ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <div><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($emp['email']) ?></div>
                                                    <?php if ($emp['phone']): ?>
                                                        <div><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($emp['phone']) ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($emp['is_active']): ?>
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle me-1"></i>Activo
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">
                                                        <i class="bi bi-x-circle me-1"></i>Inactivo
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="small text-muted">
                                                <?= $emp['last_login_at'] ? date('d/m/Y H:i', strtotime($emp['last_login_at'])) : 'Nunca' ?>
                                            </td>
                                            <td class="text-end">
                                                <a href="<?= BASE_URL ?>/usuarios.php?action=edit&id=<?= $emp['id'] ?>"
                                                   class="btn btn-sm btn-outline-navy me-1" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="<?= BASE_URL ?>/usuarios.php?action=toggle&id=<?= $emp['id'] ?>"
                                                   class="btn btn-sm <?= $emp['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?>"
                                                   title="<?= $emp['is_active'] ? 'Desactivar' : 'Activar' ?>"
                                                   onclick="return confirm('¿Está seguro de <?= $emp['is_active'] ? 'desactivar' : 'activar' ?> a este empleado?');">
                                                    <i class="bi <?= $emp['is_active'] ? 'bi-x-circle' : 'bi-check-circle' ?>"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incluir footer con scripts -->
<?php include __DIR__ . '/includes/footer.php'; ?>
