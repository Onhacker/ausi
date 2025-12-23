/* ===== Service Worker (AUSI) — no-504-from-SW ===== */

const CACHE_NAME  = 'ausi-73';                 // ⬅️ bump saat deploy
const OFFLINE_URL = '/assets/offline.html';
const SUPPRESS_5XX = true;                     // true = jangan teruskan 5xx asli ke klien

/* HTML publik yang boleh dicache (path tanpa query) */
const HTML_CACHE_WHITELIST = new Set([
  '/', '/home', '/hal', '/hal/kontak', '/hal/privacy_policy',
  '/hal/jadwal', '/scan', '/produk', '/pijat', '/ps4', '/review',
  '/hal/review', '/hal/pengumuman'
]);

/* Precaches (URL asli, termasuk query). */
const urlsToCache = [
  '/', '/home', '/hal', '/hal/kontak', '/hal/privacy_policy',
  '/hal/jadwal', '/scan', '/hal/pengumuman', '/produk', '/review',
  '/hal/review', '/pijat','/ps4',

  '/developer/manifest?v=1',
  OFFLINE_URL,

  '/assets/admin/js/jquery-3.1.1.min.js',
  '/assets/admin/js/vendor.min.js',
  '/assets/admin/js/app.min.js',
  '/assets/admin/css/bootstrap.min.css',
  '/assets/admin/css/aos.min.css',
  '/assets/admin/css/icons.min.css',
  '/assets/admin/css/app.min.css',
  '/assets/admin/libs/animate/animate.min.css',
  '/assets/admin/datatables/dataTables.bootstrap4.css',
  '/assets/admin/datatables/jquery.dataTables.min.js',
  '/assets/admin/datatables/dataTables.bootstrap4.js',
  '/assets/admin/js/jquery.easyui.min.js',
  '/assets/admin/libs/flatpickr/flatpickr.min.css',
  '/assets/admin/libs/flatpickr/flatpickr.min.js',
  '/assets/admin/libs/dropify/dropify_peng.js',
  '/assets/admin/libs/dropify/dropify.min.css',
  '/assets/admin/libs/sweetalert2/sweetalert2.min.js',
  '/assets/admin/libs/tippy-js/tippy.all.min.js',
  '/assets/admin/libs/select2/select2.min.js',
  '/assets/admin/libs/select2/select2.min.css',
  '/assets/admin/libs/jquery-toast/jquery.toast.min.js',
  '/assets/admin/js/sw.min.js',
  '/assets/js/install.js',
  '/assets/admin/fonts/fa-brands-400.woff2',
  '/assets/admin/fonts/fa-brands-400.woff',
  '/assets/admin/fonts/fa-brands-400.ttf',
  '/assets/admin/SliderCaptcha-master/src/disk/longbow.slidercaptcha.js',
  '/assets/admin/SliderCaptcha-master/src/disk/slidercaptcha.css',
  '/assets/js/zxing-browser.min.js',
  '/assets/admin/chart/highcharts.js',
  '/assets/admin/chart/exporting.js',
  '/assets/admin/chart/export-data.js',
  '/assets/admin/chart/accessibility.js',
  '/assets/min/home.min.css',
  '/assets/min/home.min.js',
  '/assets/min/footer.min.js',
  '/assets/min/head.min.css',
  '/assets/min/peta.min.css',
  '/assets/min/peta.min.js',
  '/assets/min/gmail.min.js',
  '/assets/min/sound.min.js',
  '/assets/min/thead.min.js',
  '/assets/min/gmai.min.css',
  '/assets/sound/notif_b.wav',
  '/assets/sound/notif.wav',
  '/assets/front/produk.min.css',
  '/assets/front/produk.min.js',
  '/assets/js/canva.js',
  '/assets/front/produk_detail_modal_partial.min.css',

];

/* === Helper === */
const pathKey = (reqOrUrl) => {
  const u = new URL(typeof reqOrUrl === 'string' ? reqOrUrl : reqOrUrl.url, self.location.origin);
  return (u.pathname.replace(/\/+$/, '') || '/');
};

const API_DENYLIST = [
  /^\/home\/chart(?:_?data)?(?:\/.*)?$/i,  // /home/chartdata atau /home/chart_data
  /^\/api\/?/i,                            // API umum
  /^\/admin(?:\/.*)?$/i,                   // semua admin (dashboard, dll.)
  /^\/login(?:\/.*)?$/i,                   // login
  /^\/admin_permohonan\/(export_excel|cetak_pdf)(?:\/.*)?$/i
];
const QRIS_BYPASS = [
  /^\/uploads\/qris\/.*$/i,            // file png qris (kalau kamu pakai ini)
  /^\/produk\/qris_png(?:\/.*)?$/i,    // endpoint gambar qris dari controller
  /^\/produk\/pay_qris(?:\/.*)?$/i     // halaman bayar qris
];

const SITEMAP_BYPASS = [
  /^\/sitemap\.xml$/i,
  /^\/sitemap-static\.xml$/i,
  /^\/sitemap-products-\d+\.xml$/i,
  /^\/robots\.txt$/i
];

/* Hanya cache aset statik (bukan XHR). Tambah ekstensi bila perlu */
const isStaticAsset = (req) => {
  if (req.destination) {
    return ['style','script','image','font','audio','video','track','manifest'].includes(req.destination);
  }
  const p = new URL(req.url).pathname;
  return /\.(?:css|js|mjs|png|jpe?g|gif|webp|svg|ico|woff2?|ttf|otf|map|wasm)$/i.test(p);
};

const RUNTIME_BYPASS = [/\/api\/status$/];

/* ===== INSTALL ===== */
self.addEventListener('install', (event) => {
  self.skipWaiting();
  const SKIP_BIG = /\.(mp4|mov|webm|zip|pdf)$/i;

  event.waitUntil((async () => {
    const cache = await caches.open(CACHE_NAME);
    await Promise.allSettled(
      urlsToCache.map(async (url) => {
        try {
          if (SKIP_BIG.test(url)) return;
          const res = await fetch(url, { cache: 'reload' });
          if (res && res.ok) {
            await cache.put(url, res.clone());
            const u = new URL(url, self.location.origin);
            if (u.searchParams.has('v')) {
              await cache.put(u.origin + u.pathname, res.clone());
            }
          }
        } catch (err) {
          console.warn('[SW] Precache fail', url, err);
        }
      })
    );
    try {
      const off = await fetch(OFFLINE_URL, { cache: 'reload' });
      if (off.ok) await cache.put(OFFLINE_URL, off.clone());
    } catch {}
  })());
});
const purgeQrisFromCache = async () => {
  const cache = await caches.open(CACHE_NAME);
  const keys = await cache.keys();
  await Promise.all(keys.map((r) => {
    const u = new URL(r.url);
    if (QRIS_BYPASS.some(rx => rx.test(u.pathname))) {
      return cache.delete(r);
    }
  }));
};

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const names = await caches.keys();
    await Promise.all(names.map((n) => (n === CACHE_NAME ? null : caches.delete(n))));
    await purgeQrisFromCache();
    await self.clients.claim();
    console.log('[SW] active', CACHE_NAME);
  })());
});

/* ===== ACTIVATE ===== */
self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const names = await caches.keys();
    await Promise.all(names.map((n) => (n === CACHE_NAME ? null : caches.delete(n))));
    await self.clients.claim();
    console.log('[SW] active', CACHE_NAME);
  })());
});

/* ===== FETCH (no 504) ===== */
self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;

  const url = new URL(req.url);
  const accept = req.headers.get('accept') || '';
  const sameOrigin = url.origin === self.location.origin;

  // BYPASS manual: tambahkan ?sw-bypass=1 pada URL saat debug
  if (url.searchParams.has('sw-bypass')) return;

  if (RUNTIME_BYPASS.some(rx => rx.test(url.pathname))) {
    event.respondWith(fetch(req));
    return;
  }

  // BYPASS sitemap/robots → network-only & no-store
  if (sameOrigin && SITEMAP_BYPASS.some(rx => rx.test(url.pathname))) {
    event.respondWith(
      fetch(req, { cache: 'no-store', credentials: 'include' })
        .catch(() => new Response('Unavailable', { status: 503, headers: { 'Content-Type': 'text/plain' } }))
    );
    return;
  }

  // Network-only untuk route sensitif (tidak pernah di-cache)
  if (sameOrigin && API_DENYLIST.some(rx => rx.test(url.pathname))) {
    event.respondWith(
      fetch(req, { cache: 'no-store', credentials: 'include' })
        .catch(() => new Response('Offline', { status: 503, headers: { 'Content-Type': 'text/plain' } }))
    );
    return;
  }

  // ✅ BYPASS QRIS: selalu network-only (jangan pernah cache)
if (sameOrigin && QRIS_BYPASS.some(rx => rx.test(url.pathname))) {
  event.respondWith(
    fetch(req, { cache: 'no-store', credentials: 'include' })
      .catch(() => new Response('Offline', {
        status: 503,
        headers: { 'Content-Type': 'text/plain' }
      }))
  );
  return;
}


  // 1) HTML / navigasi → network-first; cache hanya whitelist & bukan login
  if (req.mode === 'navigate' || accept.includes('text/html')) {
    event.respondWith((async () => {
      try {
        const fresh = await fetch(req, { cache: 'no-store', credentials: 'include' });

        // MASK 5xx dari server agar tidak terlihat 504 di klien
       // MASK 5xx dari server agar tidak terlihat 504 di klien
        if (SUPPRESS_5XX && fresh.status >= 500) {
          const key = pathKey(req);

          // 1) kalau ada cache untuk path sekarang, pakai itu
          const cached = await caches.match(key) || await caches.match(OFFLINE_URL);
          if (cached) return cached;

          // 2) last resort: coba fetch offline.html langsung
          try {
            const off = await fetch(OFFLINE_URL, { cache: 'reload' });
            if (off.ok) return off;
          } catch {}

          // 3) kalau semua gagal, minimal kirim teks Offline
          return new Response('Offline', {
            status: 200,
            headers: { 'Content-Type': 'text/html' }
          });
        }


        const cc = fresh.headers.get('cache-control') || '';
        const isNoStore = /no-store|private/i.test(cc) || fresh.headers.get('x-auth-logged-in') === '1';

        const key = pathKey(req); // cache-by-path utk HTML
        if (!isNoStore && HTML_CACHE_WHITELIST.has(key) && fresh.ok) {
          const c = await caches.open(CACHE_NAME);
          await c.put(key, fresh.clone());
        }
        return fresh;
      } catch {
          const key = pathKey(req);
          return (await caches.match(key)) ||
                 (await caches.match(OFFLINE_URL)) ||
                 new Response('Offline', {
                   status: 200,
                   headers: { 'Content-Type': 'text/html' }
                 });
        }

    })());
    return;
  }

  // 2) Aset same-origin → stale-while-revalidate + fallback (tanpa 504)
  if (sameOrigin && isStaticAsset(req)) {
    event.respondWith((async () => {
      const c = await caches.open(CACHE_NAME);
      const u = new URL(req.url);
      const hasV  = u.searchParams.has('v');
      const clean = hasV ? (u.origin + u.pathname) : null;

      // Exact match (dengan query)
      let cached = await c.match(req);
      // Jika tidak ada dan ini aset versi (?v=...), coba tanpa query
      if (!cached && hasV) cached = await c.match(clean);

      // Revalidate di belakang layar; simpan hanya res.ok
      const updating = fetch(req, { cache: 'no-store', credentials: 'include' })
        .then(res => {
          if (res && res.ok) {
            c.put(req, res.clone());
            if (hasV) c.put(clean, res.clone());
          }
          return res;
        })
        .catch(() => null);

      // Prioritaskan cache; jika tidak ada, coba network; jika gagal → offline
      return cached ||
             (await updating) ||
             (await caches.match(OFFLINE_URL)) ||
             new Response('Sementara tidak tersedia', { status: 503, headers: { 'Content-Type': 'text/plain' } });
    })());
    return;
  }

  // 3) Lainnya (XHR same-origin non-denylist & seluruh cross-origin)
  event.respondWith((async () => {
    try {
      const res = await fetch(req, { cache: 'no-store', credentials: 'include' });

      // MASK 5xx untuk API/asset non-HTML
      if (SUPPRESS_5XX && res.status >= 500) {
        const isJson = (req.headers.get('accept') || '').includes('application/json')
                    || (req.headers.get('content-type') || '').includes('application/json');
        if (isJson) {
          return new Response(JSON.stringify({ ok:false, server:true, code: res.status }), {
            status: 503,
            headers: { 'Content-Type': 'application/json' }
          });
        }
        return (await caches.match(OFFLINE_URL)) ||
               new Response('Sementara tidak tersedia', { status: 503, headers: { 'Content-Type': 'text/plain' } });
      }
      return res;
    } catch {
      const isJson = (req.headers.get('accept') || '').includes('application/json')
                  || (req.headers.get('content-type') || '').includes('application/json');
      if (isJson) {
        return new Response(JSON.stringify({ ok:false, offline:true }), {
          status: 503,
          headers: { 'Content-Type': 'application/json' }
        });
      }
      return (await caches.match(OFFLINE_URL)) ||
             new Response('Offline', { status: 503, headers: { 'Content-Type': 'text/plain' } });
    }
  })());
});

/* ===== MESSAGE ===== */
self.addEventListener('message', (event) => {
  const data = event.data || {};
  if (data.type === 'SKIP_WAITING') self.skipWaiting();
  if (data.type === 'CLEAR_ALL_CACHES') {
    event.waitUntil(caches.keys().then(keys => Promise.all(keys.map(k => caches.delete(k)))));
  }
});
