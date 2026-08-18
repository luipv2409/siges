<?php
/**
 * SIGES - Sistema de Gestión de Empeños
 * Pie de página responsive con scripts y registro de Service Worker
 *
 * Fase 3 - Estructura PWA y componentes de interfaz
 */

// Incluir configuración para BASE_URL
require_once __DIR__ . '/../config/app.php';
?>
</main><!-- /main -->

<!-- ============================================================
     FOOTER
     ============================================================ -->
<footer class="bg-navy-dark text-white py-4 mt-auto">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                <h5 class="text-gold mb-1">
                    <i class="bi bi-bank me-1"></i><?= APP_NAME ?>
                </h5>
                <p class="small text-white-50 mb-0">
                    <?= APP_FULL_NAME ?> &copy; <?= date('Y') ?> - Todos los derechos reservados.
                </p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <p class="small text-white-50 mb-2">Versión <?= APP_VERSION ?></p>
                <div class="d-flex justify-content-center justify-content-md-end gap-2">
                    <a href="<?= BASE_URL ?>/index.php" class="text-white-50 text-decoration-none small me-3">
                        <i class="bi bi-house-door me-1"></i>Inicio
                    </a>
                    <a href="<?= BASE_URL ?>/subastas.php" class="text-white-50 text-decoration-none small me-3">
                        <i class="bi bi-gavel me-1"></i>Subastas
                    </a>
                    <a href="<?= BASE_URL ?>/contacto.php" class="text-white-50 text-decoration-none small">
                        <i class="bi bi-envelope me-1"></i>Contacto
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- ============================================================
     SCRIPTS
     ============================================================ -->

<!-- Bootstrap 5 JS Bundle (incluye Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Registro del Service Worker para PWA -->
<script>
    // Registrar el Service Worker solo si el navegador lo soporta
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('<?= BASE_URL ?>/sw.js')
                .then(function (registration) {
                    console.log('[PWA] Service Worker registrado con éxito:', registration.scope);
                })
                .catch(function (error) {
                    console.error('[PWA] Error al registrar el Service Worker:', error);
                });
        });
    }

    // Detectar instalación de la PWA
    let deferredPrompt;
    window.addEventListener('beforeinstallprompt', function (event) {
        // Prevenir que el navegador muestre el prompt automáticamente
        event.preventDefault();
        deferredPrompt = event;

        // Mostrar un botón de instalación si existe en la página
        const installBtn = document.getElementById('install-app-btn');
        if (installBtn) {
            installBtn.style.display = 'inline-block';
            installBtn.addEventListener('click', function () {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(function (choiceResult) {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('[PWA] Usuario aceptó la instalación');
                    } else {
                        console.log('[PWA] Usuario rechazó la instalación');
                    }
                    deferredPrompt = null;
                    installBtn.style.display = 'none';
                });
            });
        }
    });

    // Detectar cuando la PWA está instalada
    window.addEventListener('appinstalled', function () {
        console.log('[PWA] Aplicación instalada correctamente');
        const installBtn = document.getElementById('install-app-btn');
        if (installBtn) {
            installBtn.style.display = 'none';
        }
    });
</script>

</body>
</html>
