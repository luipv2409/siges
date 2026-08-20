<?php
/**
 * SIGES - Sistema de Gestión de Empeños
 * Encabezado HTML5 con Navbar dinámico según rol
 *
 * Fase 3 - Estructura PWA y componentes de interfaz
 */

// Incluir configuraciones y helpers necesarios
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/security.php';

// Asegurar que la sesión esté iniciada
ensure_session();

// Determinar si el usuario está autenticado y su rol

$is_logged = is_logged_in();
$user_role = $_SESSION['user_role'] ?? '';
$user_name = $_SESSION['user_name'] ?? '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Gestión de Empeños - SIGES">
    <meta name="theme-color" content="#0B192C">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SIGES">

    <!-- Manifest PWA -->
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/icons/icon-32x32.png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/icons/icon-192x192.png">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Estilos personalizados SIGES -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css">

    <title><?= isset($page_title) ? $page_title . ' | ' : '' ?><?= APP_NAME ?></title>
</head>
<body>

<!-- ============================================================
     NAVBAR RESPONSIVE DINÁMICO
     ============================================================ -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-siges sticky-top">
    <div class="container">
        <!-- Marca -->
        <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>/index.php">
            <i class="bi bi-bank me-2"></i>
            <span><?= APP_NAME ?></span>
        </a>

        <!-- Botón hamburguesa (móvil) -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSiges"
                aria-controls="navbarSiges" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Contenido del navbar -->
        <div class="collapse navbar-collapse" id="navbarSiges">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if (!$is_logged): ?>
                    <!-- Menú para visitantes no autenticados -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/index.php">
                            <i class="bi bi-house-door me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/subastas.php">
                            <i class="bi bi-gavel me-1"></i>Subastas
                        </a>
                    </li>
                <?php elseif ($user_role === 'OWNER'): ?>
                    <!-- Menú para DUEÑO (OWNER) -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/dashboard_owner.php">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/empenos.php">
                            <i class="bi bi-bank me-1"></i>Empeños
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/subastas.php">
                            <i class="bi bi-gavel me-1"></i>Subastas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/inventario.php">
                            <i class="bi bi-box-seam me-1"></i>Inventario
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/reportes.php">
                            <i class="bi bi-graph-up me-1"></i>Reportes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/configuracion.php">
                            <i class="bi bi-gear me-1"></i>Configuración
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/usuarios.php">
                            <i class="bi bi-people me-1"></i>Usuarios
                        </a>
                    </li>
                <?php elseif ($user_role === 'EMPLOYEE'): ?>

                    <!-- Menú para EMPLEADO (EMPLOYEE) -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/dashboard_employee.php">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/empenos.php">
                            <i class="bi bi-bank me-1"></i>Empeños
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/valuacion.php">
                            <i class="bi bi-tags me-1"></i>Valuación
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/subastas.php">
                            <i class="bi bi-gavel me-1"></i>Subastas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/inventario.php">
                            <i class="bi bi-box-seam me-1"></i>Inventario
                        </a>
                    </li>
                <?php elseif ($user_role === 'CLIENT'): ?>
                    <!-- Menú para CLIENTE (CLIENT) -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/dashboard_client.php">
                            <i class="bi bi-speedometer2 me-1"></i>Mi Panel
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/mis_empenos.php">
                            <i class="bi bi-bank me-1"></i>Mis Empeños
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/subastas.php">
                            <i class="bi bi-gavel me-1"></i>Subastas
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- Zona derecha del navbar -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <?php if ($is_logged): ?>
                    <!-- Usuario autenticado -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= htmlspecialchars($user_name) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <span class="dropdown-item-text small text-muted">
                                    Rol: <span class="badge badge-gold"><?= htmlspecialchars($user_role) ?></span>
                                </span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/perfil.php">
                                    <i class="bi bi-person me-2"></i>Mi Perfil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Visitante no autenticado -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/login.php">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Iniciar Sesión
                        </a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-gold btn-sm mt-1 mt-lg-0" href="<?= BASE_URL ?>/register.php">
                            <i class="bi bi-person-plus me-1"></i>Registrarse
                        </a>
                    </li>

                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Contenido principal -->
<main class="container py-4">
