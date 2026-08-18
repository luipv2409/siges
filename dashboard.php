<?php
/**
 * SIGES - Sistema de Gestión de Empeños
 * Panel de control protegido
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

// Proteger la página: requiere autenticación
if (!is_logged_in()) {
    redirect(BASE_URL . '/login.php');
}

// Obtener datos del usuario desde la sesión
$user_id   = (int)$_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? '';
$user_role = $_SESSION['user_role'] ?? '';

// Obtener datos adicionales del usuario desde la BD
$user_data = null;
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare(
        'SELECT u.id, u.name, u.email, u.phone, u.last_login_at, r.name AS role_name, r.description AS role_description
         FROM users u
         INNER JOIN roles r ON u.role_id = r.id
         WHERE u.id = :id
         LIMIT 1'
    );
    $stmt->execute([':id' => $user_id]);
    $user_data = $stmt->fetch();
} catch (PDOException $e) {
    error_log('Error obteniendo datos del usuario en dashboard: ' . $e->getMessage());
}

// Definir enlaces según el rol
$menu_links = [];
switch ($user_role) {
    case 'OWNER':
        $menu_links = [
            ['url' => BASE_URL . '/empenos.php', 'icon' => 'bi-bank', 'title' => 'Empeños', 'desc' => 'Gestionar empeños y contratos'],
            ['url' => BASE_URL . '/subastas.php', 'icon' => 'bi-gavel', 'title' => 'Subastas', 'desc' => 'Ver y gestionar subastas'],
            ['url' => BASE_URL . '/inventario.php', 'icon' => 'bi-box-seam', 'title' => 'Inventario', 'desc' => 'Control de prendas y ubicaciones'],
            ['url' => BASE_URL . '/reportes.php', 'icon' => 'bi-graph-up', 'title' => 'Reportes', 'desc' => 'Indicadores financieros y KPIs'],
            ['url' => BASE_URL . '/configuracion.php', 'icon' => 'bi-gear', 'title' => 'Configuración', 'desc' => 'Tasas de interés y parámetros'],
        ];
        break;

    case 'EMPLOYEE':
        $menu_links = [
            ['url' => BASE_URL . '/empenos.php', 'icon' => 'bi-bank', 'title' => 'Empeños', 'desc' => 'Registrar y gestionar empeños'],
            ['url' => BASE_URL . '/valuacion.php', 'icon' => 'bi-tags', 'title' => 'Valuación', 'desc' => 'Asistente de valuación de prendas'],
            ['url' => BASE_URL . '/subastas.php', 'icon' => 'bi-gavel', 'title' => 'Subastas', 'desc' => 'Ver y gestionar subastas'],
            ['url' => BASE_URL . '/inventario.php', 'icon' => 'bi-box-seam', 'title' => 'Inventario', 'desc' => 'Control de prendas y ubicaciones'],
        ];
        break;

    case 'CLIENT':
        $menu_links = [
            ['url' => BASE_URL . '/mis_empenos.php', 'icon' => 'bi-bank', 'title' => 'Mis Empeños', 'desc' => 'Ver el estado de sus empeños'],
            ['url' => BASE_URL . '/subastas.php', 'icon' => 'bi-gavel', 'title' => 'Subastas', 'desc' => 'Participar en subastas activas'],
            ['url' => BASE_URL . '/perfil.php', 'icon' => 'bi-person', 'title' => 'Mi Perfil', 'desc' => 'Actualizar sus datos personales'],
        ];
        break;

    default:
        // Rol desconocido: cerrar sesión
        redirect(BASE_URL . '/logout.php');
}

$page_title = 'Panel de Control';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Panel de control de SIGES - Sistema de Gestión de Empeños">
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

<!-- Encabezado del dashboard -->
<div class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-1">
                    <i class="bi bi-speedometer2 me-2"></i>Panel de Control
                </h1>
                <p class="mb-0 text-white-50">
                    Bienvenido, <strong class="text-gold"><?= htmlspecialchars($user_name) ?></strong>
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <span class="badge badge-gold fs-6">
                    <i class="bi bi-person-badge me-1"></i><?= htmlspecialchars($user_role) ?>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <!-- Mensaje flash (si existe) -->
    <?php if (!empty($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'success' ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <!-- Información del usuario -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="stat-card">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-person-circle stat-icon me-3"></i>
                    <div>
                        <div class="stat-value"><?= htmlspecialchars($user_data['name'] ?? $user_name) ?></div>
                        <div class="stat-label">Usuario</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-envelope stat-icon me-3"></i>
                    <div>
                        <div class="stat-value text-truncate"><?= htmlspecialchars($user_data['email'] ?? '') ?></div>
                        <div class="stat-label">Email</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-clock-history stat-icon me-3"></i>
                    <div>
                        <div class="stat-value small">
                            <?= $user_data['last_login_at'] ? date('d/m/Y H:i', strtotime($user_data['last_login_at'])) : 'Primer acceso' ?>
                        </div>
                        <div class="stat-label">Último acceso</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Descripción del rol -->
    <?php if (!empty($user_data['role_description'])): ?>
        <div class="alert alert-gold mb-4" role="alert">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Rol <?= htmlspecialchars($user_role) ?>:</strong>
            <?= htmlspecialchars($user_data['role_description']) ?>
        </div>
    <?php endif; ?>

    <!-- Enlaces según rol -->
    <h2 class="h4 text-navy mb-3">
        <i class="bi bi-grid me-2"></i>Accesos Disponibles
    </h2>

    <div class="row">
        <?php foreach ($menu_links as $link): ?>
            <div class="col-md-6 col-lg-4 mb-3">
                <a href="<?= $link['url'] ?>" class="text-decoration-none">
                    <div class="card card-siges h-100">
                        <div class="card-body d-flex align-items-start">
                            <i class="bi <?= $link['icon'] ?> fs-2 text-gold me-3"></i>
                            <div>
                                <h5 class="card-title text-navy mb-1"><?= $link['title'] ?></h5>
                                <p class="card-text text-muted small mb-0"><?= $link['desc'] ?></p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Incluir footer con scripts -->
<?php include __DIR__ . '/includes/footer.php'; ?>
