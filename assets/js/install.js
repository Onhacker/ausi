let deferredPrompt = null;

/* ===== DETEKSI STATE ===== */

// Apakah tab ini SEDANG BERJALAN sebagai PWA mandiri (ikon home screen / standalone)?
function isRunningStandaloneNow() {
  const mm = m => window.matchMedia(m).matches;
  const displayStandalone =
    mm('(display-mode: standalone)') ||
    mm('(display-mode: fullscreen)') ||
    mm('(display-mode: minimal-ui)') ||
    mm('(display-mode: window-controls-overlay)');
  const iosStandalone = (window.navigator.standalone === true);
  return displayStandalone || iosStandalone;
}

// Apakah device INI sudah punya app kita terinstall sebelumnya?
// Kita pakai flag localStorage (di-set saat app pernah dibuka standalone)
function hasInstalledApp() {
  if (isRunningStandaloneNow()) return true;
  try {
    return !!localStorage.getItem('shownStandaloneNotice');
  } catch (e) {
    return !!window.__shownStandaloneNotice; // fallback in-memory
  }
}

// deteksi iOS user agent
function isIOSUA() {
  const ua = navigator.userAgent || navigator.vendor || '';
  return /iPad|iPhone|iPod/i.test(ua) || (ua.includes('Macintosh') && 'ontouchend' in document);
}


/* ===== SweetAlert helper ===== */

(function ensureIOSGuide(){
  if (typeof window.showIOSInstallGuide === 'function') return;
  window.showIOSInstallGuide = function(e){
    if (e) e.preventDefault();

    const ua = navigator.userAgent || navigator.vendor || '';
    const isSafari = /^((?!chrome|android|crios|fxios).)*safari/i.test(ua);

    const htmlSafari =
      '<ol style="text-align:left;max-width:520px;margin:0 auto">' +
      '<li>Ketuk ikon <b>Bagikan</b> (kotak dengan panah ke atas).</li>' +
      '<li>Pilih <b>Tambahkan ke Layar Utama</b>.</li>' +
      '<li>Ketuk <b>Tambahkan</b>.</li>' +
      '</ol>';

    if (!window.Swal) {
      alert('Buka menu Bagikan → Tambahkan ke Layar Utama');
      return false;
    }

    if (!isSafari) {
      Swal.fire({
        title:'Buka di Safari',
        html:'Buka halaman ini di <b>Safari</b> untuk menginstal PWA.<br><br>'+htmlSafari,
        icon:'info'
      });
      return false;
    }

    Swal.fire({
      title:'Instal ke iOS',
      html:htmlSafari,
      icon:'info'
    });
    return false;
  };
})();

function whenSwalReady(run, timeout=3000){
  const t0 = Date.now();
  (function tick(){
    if (window.Swal && typeof Swal.fire === 'function') return run(false);
    if (Date.now()-t0 > timeout) return run(true); // fallback
    setTimeout(tick, 50);
  })();
}


/* ===== Info "sudah terinstal" sekali aja ===== */

function showStandaloneNoticeOnce(){
  if (!isRunningStandaloneNow()) return;

  const KEY = 'shownStandaloneNotice';

  // simpan flag kalau belum ada
  const markDone = () => {
    try { localStorage.setItem(KEY, '1'); } catch (e) {
      window.__shownStandaloneNotice = true;
    }
  };

  // kalau sudah pernah tandai -> gak usah pop up lagi
  try {
    if (localStorage.getItem(KEY)) return;
  } catch (e) {
    if (window.__shownStandaloneNotice) return;
  }

  whenSwalReady((fallback)=>{
    if (!fallback && window.Swal?.fire) {
      Swal.fire(
        'Aplikasi Sudah Terinstal',
        'Anda menjalankan aplikasi dalam mode mandiri (standalone).',
        'info'
      ).then(markDone, markDone);
    } else {
      alert('Aplikasi berjalan dalam mode mandiri (standalone).');
      markDone();
    }
  });
}


/* ===== SVG ICONS ===== */

const ICON_ANDROID = `
<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <g fill="#fff">
    <path d="
      M6 18
      c0 1.1.9 2 2 2h1v3h2v-3h2v3h2v-3h1
      c1.1 0 2-.9 2-2V9H6v9z

      M15.53 4.18
      l1.3-1.3-.78-.78-1.48 1.48
      C14.38 3.17 13.23 3 12 3
      s-2.38.17-2.93.48L7.59 2
      6.81 2.88l1.3 1.3
      C7.61 5.24 7 6.48 7 8h10
      c0-1.52-.61-2.76-1.47-3.82z

      M10 6
      c-.55 0-1 .45-1 1s.45 1 1 1
      1-.45 1-1-.45-1-1-1zm4 0
      c-.55 0-1 .45-1 1s.45 1 1 1
      1-.45 1-1-.45-1-1-1z
    "/>
    <rect x="3"  y="10" width="2" height="7" rx="1" ry="1"/>
    <rect x="19" y="10" width="2" height="7" rx="1" ry="1"/>
  </g>
</svg>`;

const ICON_IOS = `
<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <path fill="#fff" d="
    M19.67 16.34
    c-.41.94-.6 1.32-1.13 2.13
    -.88 1.4-2.12 3.14-3.63 3.14
    -1.36 0-1.72-.89-3.55-.89
    s-2.25.89-3.6.89
    c-1.51 0-2.73-1.61-3.61-3.01
    C2.46 15.6 2.1 11.07 4.07 8.29
    c.97-1.37 2.52-2.22 4.12-2.24
    1.62-.02 2.64.97 3.55.97
    .9 0 2.45-1.2 4.13-1.02
    .7.03 2.67.28 3.94 2.11
    -3.47 1.89-2.91 6.4-.14 8.23
    z
    M14.6 4.8
    c.62-.75 1.1-1.8 1-2.85
    -1 .04-2.2.68-2.92 1.5
    -.64.72-1.16 1.78-1.02 2.81
    1.1.08 2.22-.56 2.94-1.46
    z
  "/>
</svg>`;

const ICON_APP = `
<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <path fill="#fff" d="M4 3h16c.6 0 1 .4 1 1v16c0 .6-.4 1-1 1H4c-.6 0-1-.4-1-1V4c0-.6.4-1 1-1zm1 2v14h14V5H5zm3 2h8c.6 0 1 .4 1 1v8c0 .6-.4 1-1 1H8c-.6 0-1-.4-1-1V8c0-.6.4-1 1-1z"/>
</svg>`;


/* ===== RENDER / STATE BUTTON =====
   State:
   - standalone running now  -> hide completely
   - browser + installed     -> show "Open App"
   - browser + not installed -> show "Install on X"
*/
function renderInstallButtonState(){
  const btn = document.getElementById('installButton');
  if (!btn) return;

  const iconEl = btn.querySelector('.install-icon');
  const textEl = btn.querySelector('.install-text');

  // 1. kalau lagi running standalone → sembunyikan total
  if (isRunningStandaloneNow()){
    btn.style.display = 'none';
    btn.dataset.mode = '';
    return;
  }

  // 2. kalau di browser biasa dan SUDAH terinstal → tampilkan "Open App"
  if (hasInstalledApp()){
    btn.style.display = 'inline-flex';
    if (iconEl) iconEl.innerHTML = ICON_APP;
    if (textEl) textEl.textContent = 'Open App';
    btn.setAttribute('aria-label','Open App');
    btn.dataset.mode = 'open';
    return;
  }

  // 3. di browser biasa dan BELUM terinstal → tampilkan "Install ..."
  btn.style.display = 'inline-flex';
  btn.dataset.mode = 'install';

  if (isIOSUA()){
    if (iconEl) iconEl.innerHTML = ICON_IOS;
    if (textEl) textEl.textContent = 'Install on iOS';
    btn.setAttribute('aria-label','Install on iOS');
  } else {
    if (iconEl) iconEl.innerHTML = ICON_ANDROID;
    if (textEl) textEl.textContent = 'Install on Android';
    btn.setAttribute('aria-label','Install on Android');
  }
}


/* ==== EVENT LISTENERS ==== */

/* Simpan event PWA install prompt (Android/Chrome) */
window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault();
  deferredPrompt = e;
  console.log('✅ beforeinstallprompt siap.');
});

/* Setelah semua resource load:
   - tandai kalau standalone (localStorage)
   - render tombol sesuai state terbaru
*/
window.addEventListener('load', () => {
  showStandaloneNoticeOnce();
  renderInstallButtonState();
});

/* Saat DOM siap:
   - render awal
   - pasang click handler
*/
document.addEventListener('DOMContentLoaded', () => {
  renderInstallButtonState();

  const btn = document.getElementById('installButton');
  if (!btn) return;

  btn.addEventListener('click', async (e) => {
    e.preventDefault();

    const mode = btn.dataset.mode || '';

    // MODE: open → user sudah install, lagi di browser
    if (mode === 'open') {
      // upaya "buka app"
      // Secara teknis browser sering gak bisa langsung switch ke standalone PWA.
      // Kita arahkan ke URL root app. Di Android WebAPK kadang ini otomatis open app.
      try {
        window.location.href = window.location.origin + '/';
      } catch (err) {}

      whenSwalReady((fallback)=>{
        if (!fallback) {
          Swal.fire(
            'Buka Aplikasi',
            'Kalau tidak otomatis terbuka sebagai aplikasi, silakan buka via ikon yang sudah ada di Home Screen / menu HP Anda.',
            'info'
          );
        } else {
          alert('Buka dari ikon aplikasi di Home Screen / menu HP Anda ya.');
        }
      });

      return;
    }

    // MODE: install → belum terpasang
    if (mode === 'install') {

      // iOS: tampilkan panduan Add to Home Screen
      if (isIOSUA()) {
        return window.showIOSInstallGuide(e);
      }

      // Android / Chrome: pakai deferredPrompt
      if (!deferredPrompt) {
        return whenSwalReady((fallback)=>{
          if (!fallback) {
            Swal.fire(
              'Belum Siap',
              'Aplikasi belum memenuhi syarat PWA untuk ditawarkan instal.',
              'warning'
            );
          } else {
            alert('Instal belum siap.');
          }
        });
      }

      deferredPrompt.prompt();
      const choice = await deferredPrompt.userChoice;

      if (choice && choice.outcome === 'accepted') {
        whenSwalReady((fallback)=>{
          if (!fallback) {
            Swal.fire(
              'Berhasil!',
              'Aplikasi sedang diinstal.',
              'success'
            );
          }
        });
      } else {
        whenSwalReady((fallback)=>{
          if (!fallback) {
            Swal.fire(
              'Dibatalkan',
              'Anda membatalkan instalasi.',
              'info'
            );
          }
        });
      }

      deferredPrompt = null;
      return;
    }

    // fallback kalau entah kenapa mode kosong
    console.warn('installButton: mode tidak dikenal');
  });
});

/* Ketika PWA selesai di-install (Chrome/Android trigger event ini) */
window.addEventListener('appinstalled', () => {
  console.log('✅ App installed');

  // tandai lokal supaya next time hasInstalledApp() = true,
  // walau belum sempat buka standalone
  try { localStorage.setItem('shownStandaloneNotice','1'); }
  catch(e){ window.__shownStandaloneNotice = true; }

  whenSwalReady((fallback)=>{
    if (!fallback) {
      Swal.fire(
        'Terpasang',
        'Aplikasi berhasil diinstal. Icon akan tampil di Home Screen / menu HP Anda.',
        'success'
      );
    }
  });

  // refresh state tombol
  renderInstallButtonState();
});

/* Saat tab balik fokus, cek ulang state (misal user baru saja buka app standalone lalu kembali ke browser) */
document.addEventListener('visibilitychange', () => {
  if (!document.hidden) {
    renderInstallButtonState();
  }
});
