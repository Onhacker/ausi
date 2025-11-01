/* ===== Service Worker ===== */

const CACHE_NAME  = 'ausi-9';                 // ⬅️ bump saat deploy
const OFFLINE_URL = '/assets/offline.html';

/* HTML publik yang boleh dicache (path tanpa query) */
const HTML_CACHE_WHITELIST = new Set([
  '/',
   '/home',
   '/hal',
   '/hal/kontak',
   '/hal/privacy_policy',
   '/hal/jadwal',
  
   '/produk',
   '/hal/pengumuman'
])


/* Precaches (URL asli,
 termasuk query). JANGAN masukkan endpoint dinamis seperti /home/chart_data */
const urlsToCache = [
  '/',
   '/home',
   '/hal',
    '/hal/kontak',
    '/hal/privacy_policy',
    '/hal/jadwal',

   '/hal/pengumuman',
   '/produk',

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
  '/assets/sound/notif_b.wav',
  '/assets/sound/notif.wav',
];

/* === Helper === */
const pathKey = (reqOrUrl) => {
  const u = new URL(typeof reqOrUrl === 'string' ? reqOrUrl : reqOrUrl.url, self.location.origin);
  return (u.pathname.replace(/\/+$/, '') || '/');
};

/* Route yang TIDAK BOLEH di-cache (network-only) */
const API_DENYLIST = [
  /^\/home\/chart(?:_?data)?(?:\/.*)?$/i,  // /home/chartdata atau /home/chart_data
  /^\/api\/?/i,                            // API umum
  /^\/admin(?:\/.*)?$/i,                   // semua admin (dashboard, dll.)
  /^\/login(?:\/.*)?$/i,                 // login
  /^\/admin_permohonan\/(export_excel|cetak_pdf)(?:\/.*)?$/i
  // /^\/booking\/(booked|print_pdf)(?:\/.*)?$/i // halaman sensitif token
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
          if (res && res.ok) await cache.put(url, res.clone()); // simpan dgn query
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

/* ===== ACTIVATE ===== */
self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const names = await caches.keys();
    await Promise.all(names.map((n) => (n === CACHE_NAME ? null : caches.delete(n))));
    await self.clients.claim();
  })());
});

/* ===== FETCH ===== */
self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;

  const url = new URL(req.url);
  const accept = req.headers.get('accept') || '';
  const sameOrigin = url.origin === self.location.origin;

  if (RUNTIME_BYPASS.some(rx => rx.test(url.pathname))) {
    event.respondWith(fetch(event.request)); // network only
    return;
  }
  /* 0) Network-only untuk route sensitif (tidak pernah di-cache) */
  if (sameOrigin && API_DENYLIST.some(rx => rx.test(url.pathname))) {
    event.respondWith(
      fetch(req, { cache: 'no-store', credentials: 'include' })
        .catch(() => new Response('Offline', { status: 503, headers: { 'Content-Type': 'text/plain' } }))
    );
    return;
  }

  /* 1) HTML / navigasi → network-first; cache hanya whitelist & bukan login */
  if (req.mode === 'navigate' || accept.includes('text/html')) {
    event.respondWith((async () => {
      try {
        const fresh = await fetch(req, { cache: 'no-store', credentials: 'include' });

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
               (await caches.match('/')) ||
               (await caches.match(OFFLINE_URL)) ||
               new Response('Offline', { status: 503, headers: { 'Content-Type': 'text/plain' } });
      }
    })());
    return;
  }

  /* 2) Aset same-origin → stale-while-revalidate (preserve query) */
  if (sameOrigin && isStaticAsset(req)) {
    event.respondWith((async () => {
      const c = await caches.open(CACHE_NAME);
      const cached = await c.match(req); // match dgn Request asli (ada query)
      const updating = fetch(req, { credentials: 'include' })
        .then(res => { if (res && res.ok) c.put(req, res.clone()); return res; })
        .catch(() => null);
      return cached || (await updating) || new Response('', { status: 504 });
    })());
    return;
  }

  /* 3) Lainnya (XHR same-origin yg bukan denylist & seluruh cross-origin) → network-first */
  event.respondWith(
    fetch(req, { cache: 'no-store', credentials: 'include' })
      .catch(() => caches.match(req) || new Response('', { status: 504 }))
  );
});

/* ===== MESSAGE ===== */
self.addEventListener('message', (event) => {
  const data = event.data || {};
  if (data.type === 'SKIP_WAITING') self.skipWaiting();
  if (data.type === 'CLEAR_ALL_CACHES') {
    event.waitUntil(caches.keys().then(keys => Promise.all(keys.map(k => caches.delete(k)))));
  }
});
