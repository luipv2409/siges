<?php
/**
 * SIGES - Sistema de Gestión de Empeños
 * Página de inicio
 *
 * Sprint 1 - Autenticación y Seguridad
 */

// Configuración y helpers
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/helpers/security.php';
require_once __DIR__ . '/helpers/auth.php';

// Asegurar sesión
ensure_session();

$page_title = 'Inicio';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SIGES - Sistema de Gestión de Empeños">
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

<!-- Hero section -->
<div class="dashboard-header">
    <div class="container text-center py-4">
        <h1 class="display-4 mb-3">
            <i class="bi bi-bank me-2"></i><?= APP_NAME ?>
        </h1>
        <p class="lead text-white-50 mb-4">
            <?= APP_FULL_NAME ?>
        </p>
        <?php if (!is_logged_in()): ?>
            <div class="d-flex justify-content-center gap-3">
                <a href="<?= BASE_URL ?>/login.php" class="btn btn-gold btn-lg">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                </a>
                <a href="<?= BASE_URL ?>/register.php" class="btn btn-gold-outline btn-lg">
                    <i class="bi bi-person-plus me-2"></i>Registrarse
                </a>
            </div>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-gold btn-lg">
                <i class="bi bi-speedometer2 me-2"></i>Ir al Panel de Control
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Características -->
<div class="container py-5">
    <h2 class="text-center text-navy mb-4">¿Qué ofrece <?= APP_NAME ?>?</h2>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card card-siges h-100">
                <div class="card-body text-center">
                    <i class="bi bi-bank fs-1 text-gold mb-3 d-block"></i>
                    <h5 class="card-title text-navy">Gestión de Empeños</h5>
                    <p class="card-text text-muted">
                        Registre clientes y prendas, calcule intereses automáticamente y genere contratos digitales.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-siges h-100">
                <div class="card-body text-center">
                    <i class="bi bi-gavel fs-1 text-gold mb-3 d-block"></i>
                    <h5 class="card-title text-navy">Subastas en Tiempo Real</h5>
                    <p class="card-text text-muted">
                        Participe en subastas de prendas impagas con pujas en tiempo real y compra directa.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-siges h-100">
                <div class="card-body text-center">
                    <i class="bi bi-graph-up fs-1 text-gold mb-3 d-block"></i>
                    <h5 class="card-title text-navy">Reportes Financieros</h5>
                    <p class="card-text text-muted">
                        Visualice KPIs financieros, capital invertido e intereses generados con gráficos interactivos.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incluir footer con scripts -->
<?php include __DIR__ . '/includes/footer.php'; ?>
