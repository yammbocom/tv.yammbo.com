/*
 * Self-destructing service worker for tv.yammbo.com (root scope "/").
 *
 * WHY THIS EXISTS:
 * An old Stremio/Workbox build was once deployed at the web root and registered
 * a service worker at scope "/". That deploy was later moved to "/app/", but the
 * root-scope service worker stayed ACTIVE in users' browsers. Its script now 404s,
 * yet Chrome keeps it running and it intercepts every navigation to "/", serving a
 * stale precached Stremio app shell. Result: the Yammbo TV landing page is blank on
 * entry and only appears after a manual reload.
 *
 * WHAT THIS DOES:
 * The browser fetches this script during its automatic service-worker update check
 * (which bypasses the old SW). The new bytes differ from the old worker, so this
 * worker installs, deletes the orphaned root cache, unregisters itself, and reloads
 * any controlled tabs so they fetch the real landing from the network.
 *
 * It deliberately does NOT touch the legitimate "/app/" service worker or its caches.
 * Keep this file in place permanently so returning users with the stale SW get cleaned.
 */
self.addEventListener('install', function () {
  self.skipWaiting();
});

self.addEventListener('activate', function (event) {
  event.waitUntil(
    (async function () {
      // 1. Delete only the orphaned root caches; keep the "/app/" Stremio caches.
      try {
        var keys = await caches.keys();
        await Promise.all(
          keys
            .filter(function (k) { return k.indexOf('/app/') === -1; })
            .map(function (k) { return caches.delete(k); })
        );
      } catch (e) {}

      // 2. Unregister this worker so "/" is served straight from the network again.
      try {
        await self.registration.unregister();
      } catch (e) {}

      // 3. Reload controlled tabs to immediately drop the stale shell.
      try {
        var clients = await self.clients.matchAll({ type: 'window' });
        clients.forEach(function (client) {
          if ('navigate' in client) {
            client.navigate(client.url);
          }
        });
      } catch (e) {}
    })()
  );
});

// While briefly alive, never serve from cache — pass everything to the network.
self.addEventListener('fetch', function () {
  // No event.respondWith() -> default browser (network) handling.
});
