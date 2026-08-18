/**
 * SIGES - Sistema de Gestión de Empeños
 * Service Worker para PWA
 *
 * Almacena en caché los assets estáticos para funcionamiento offline.
 */

const CACHE_NAME = 'siges-cache-v1';

// Assets estáticos a precachear
const STATIC_ASSETS = [
    './',
    './index.php',
    './login.php',
    './assets/css/custom.css',
    './manifest.json',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
];

// Evento: Instalación del Service Worker
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[SW] Cacheando assets estáticos...');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => self.skipWaiting())
    );
});

// Evento: Activación del Service Worker
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => {
                        // Eliminar cachés antiguas
                        if (cacheName !== CACHE_NAME) {
                            console.log('[SW] Eliminando caché antigua:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => self.clients.claim())
    );
});

// Evento: Intercepción de peticiones fetch
self.addEventListener('fetch', (event) => {
    // Solo manejar peticiones GET
    if (event.request.method !== 'GET') {
        return;
    }

    // Estrategia: Cache First con actualización en segundo plano (stale-while-revalidate)
    event.respondWith(
        caches.match(event.request)
            .then((cachedResponse) => {
                // Si hay respuesta en caché, devolverla y actualizar en segundo plano
                if (cachedResponse) {
                    // Actualizar la caché en segundo plano
                    fetch(event.request)
                        .then((networkResponse) => {
                            if (networkResponse && networkResponse.status === 200) {
                                const responseClone = networkResponse.clone();
                                caches.open(CACHE_NAME)
                                    .then((cache) => cache.put(event.request, responseClone));
                            }
                        })
                        .catch(() => {
                            // Si falla la red, usar la caché (offline)
                        });

                    return cachedResponse;
                }

                // Si no hay en caché, hacer la petición a la red
                return fetch(event.request)
                    .then((networkResponse) => {
                        // Solo cachear respuestas válidas
                        if (networkResponse && networkResponse.status === 200) {
                            const responseClone = networkResponse.clone();
                            caches.open(CACHE_NAME)
                                .then((cache) => cache.put(event.request, responseClone));
                        }
                        return networkResponse;
                    })
                    .catch(() => {
                        // Fallback offline para navegaciones
                        if (event.request.mode === 'navigate') {
                            return caches.match('./index.php');
                        }
                        return new Response('Sin conexión a internet', {
                            status: 503,
                            statusText: 'Service Unavailable',
                            headers: { 'Content-Type': 'text/plain' }
                        });
                    });
            })
    );
});

// Evento: Sincronización en segundo plano (para pujas y operaciones offline)
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-pujas') {
        event.waitUntil(syncPujas());
    }
});

/**
 * Sincroniza las pujas pendientes cuando hay conexión.
 * (Función de ejemplo - se implementará en fases posteriores)
 */
async function syncPujas() {
    try {
        const db = await openDB();
        const pending = await db.getAll('pending-pujas');
        for (const puja of pending) {
            await fetch('./api/pujas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(puja)
            });
            await db.delete('pending-pujas', puja.id);
        }
    } catch (error) {
        console.error('[SW] Error sincronizando pujas:', error);
    }
}

/**
 * Abre la base de datos IndexedDB para almacenamiento offline.
 * (Función de ejemplo - se implementará en fases posteriores)
 */
function openDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('siges-offline', 1);
        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('pending-pujas')) {
                db.createObjectStore('pending-pujas', { keyPath: 'id', autoIncrement: true });
            }
        };
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}
