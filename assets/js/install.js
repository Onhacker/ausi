// ganti nama key di localStorage supaya "versi baru"
const INSTALL_FLAG_KEY = 'pwaInstalled_v2';

let deferredPrompt = null;
let canInstallPrompt = false; // penting: true kalau browser siap install (belum terpasang)

/* ========== DETEKSI STATE ========== */

// Apakah tab ini SEKARANG jalan sebagai PWA mandiri (ikon home screen)?
function isRunningStandaloneNow() {
  const mm = m => window.matchMedia(m).matches;
  const displayStandalone =
    mm('(display-mode: standalone)') ||
    mm('(display-mode: fullscreen)') ||
    mm('(display-mode: minimal-ui)') ||
    mm('(display-mode: window-controls-overlay)');

  const iosStandalone = (window.navigator.standalone === true);

  // DETEKSI VIA PARAM URL (?pwa=1) → ini kunci fix
  const urlStandalone = /[?&]pwa=1(?:&|$)/.test(window.location.search);

  return displayStandalone || iosStandalone || urlStandalone;
}

// Pernah dibuka sebagai standalone sebelum ini? (→ berarti user SUDAH install di device ini)
function hadStandaloneBefore() {
  // true kalau sekarang benar2 standalone
  if (isRunningStandaloneNow()) return true;

  // atau kalau pernah tandai dengan key baru
  try {
    return !!localStorage.getItem(INSTALL_FLAG_KEY);
  } catch (e) {
    return !!window.__pwaInstalledFlag_v2; // fallback in-memory
  }
}


// iOS UA check
function isIOSUA() {
  const ua = navigator.userAgent || navigator.vendor || '';
  return /iPad|iPhone|iPod/i.test(ua) || (ua.includes('Macintosh') && 'ontouchend' in document);
}


/* ========== SweetAlert helper & iOS guide ========== */

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
    if (Date.now()-t0 > timeout) return run(true);
    setTimeout(tick, 50);
  })();
}


/* ========== Tandai & pop up sekali saat app benar² jalan standalone ========== */

function showStandaloneNoticeOnce(){
  if (!isRunningStandaloneNow()) return;

  // SET FLAG INSTAL SUPAYA hadStandaloneBefore() = true KE DEPAN
  try {
    localStorage.setItem(INSTALL_FLAG_KEY, '1');
  } catch(e){
    window.__pwaInstalledFlag_v2 = true;
  }

  // kalau sudah ditandai dengan key baru, jangan pop up lagi
  try {
    if (localStorage.getItem(INSTALL_FLAG_KEY)) return;
  } catch (e) {
    if (window.__pwaInstalledFlag_v2) return;
  }

  const markDone = () => {
    try {
      localStorage.setItem(INSTALL_FLAG_KEY, '1');
    } catch(e){
      window.__pwaInstalledFlag_v2 = true;
    }
  };

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




/* ========== SVG ICONS ========== */

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


/* ========== RENDER STATE BUTTON ========== */
/*
State final:
- if runningStandaloneNow() === true:
    -> hide tombol
- else (browser normal):
    - if (hadStandaloneBefore() === true AND !canInstallPrompt) -> mode "open app"
    - else -> mode "install"
        - iOS => "Install on iOS"
        - Android/Chrome => "Install on Android"
*/

function renderInstallButtonState(){
  const btn = document.getElementById('installButton');
  if (!btn) return;

  const iconEl = btn.querySelector('.install-icon');
  const textEl = btn.querySelector('.install-text');

  // CASE 1: Lagi jalan sebagai standalone → sembunyikan tombol
  if (isRunningStandaloneNow()){
    btn.style.display = 'none';
    btn.dataset.mode = '';
    return;
  }

  // CASE 2: Browser normal & sudah terinstall → "Open App"
  // syarat kita: hadStandaloneBefore() === true dan !canInstallPrompt
  if (hadStandaloneBefore() && !canInstallPrompt) {
    btn.style.display = 'inline-flex';
    btn.dataset.mode = 'open';

    if (iconEl) iconEl.innerHTML = ICON_APP;
    if (textEl) textEl.textContent = 'Open App';
    btn.setAttribute('aria-label','Open App');
    return;
  }

  // CASE 3: Browser normal & belum terinstall → "Install…"
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



/* ========== EVENT LISTENERS ========== */

// browser bilang "app bisa di-install"
window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault();
  deferredPrompt = e;
  canInstallPrompt = true; // penting!
  console.log('✅ beforeinstallprompt siap.');

  // setelah dapat sinyal bisa install, render ulang → harusnya jadi "Install on Android"
  renderInstallButtonState();
});

// setelah semua resource -> tandai standalone (untuk localStorage), lalu render
window.addEventListener('load', () => {
  showStandaloneNoticeOnce();
  renderInstallButtonState();
});

// pas DOM siap: render awal dan pasang click handler
document.addEventListener('DOMContentLoaded', () => {
  renderInstallButtonState();

  const btn = document.getElementById('installButton');
  if (!btn) return;

  btn.addEventListener('click', async (e) => {
    e.preventDefault();
    const mode = btn.dataset.mode || '';

    // MODE "open": user sudah punya app, kita cuma arahkan dia buka via shortcut
    if (mode === 'open') {
      // Chrome WebAPK kadang akan switch sendiri kalau kita buka scope root.
      try {
        window.location.href = window.location.origin + '/';
      } catch(e){}

      whenSwalReady((fallback)=>{
        if (!fallback) {
          Swal.fire(
            'Buka Aplikasi',
            'Kalau tidak otomatis terbuka sebagai aplikasi, buka dari ikon yang sudah ada di Home Screen / menu HP Anda.',
            'info'
          );
        } else {
          alert('Silakan buka dari ikon aplikasi di Home Screen / menu HP Anda.');
        }
      });
      return;
    }

    // MODE "install": user belum pasang
    if (mode === 'install') {

      // iOS → jelaskan Add to Home Screen
      if (isIOSUA()) {
        return window.showIOSInstallGuide(e);
      }

      // Android / Chrome → gunakan prompt native
      if (!deferredPrompt) {
        // fallback: kemungkinan browser gak support PWA / gak memenuhi syarat
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
      canInstallPrompt = false;
      renderInstallButtonState(); // refresh state setelah prompt
      return;
    }

    console.warn('installButton: mode kosong / tidak dikenal');
  });
});

// ketika PWA benar2 terpasang di Android/Chrome
window.addEventListener('appinstalled', () => {
  console.log('✅ App installed');

  // tandai dengan key baru
  try {
    localStorage.setItem(INSTALL_FLAG_KEY, '1');
  } catch(e){
    window.__pwaInstalledFlag_v2 = true;
  }

  whenSwalReady((fallback)=>{
    if (!fallback) {
      Swal.fire(
        'Terpasang',
        'Aplikasi berhasil diinstal. Icon akan tampil di Home Screen / menu HP Anda.',
        'success'
      );
    }
  });

  // Chrome: setelah terpasang biasanya gak kirim beforeinstallprompt lagi
  canInstallPrompt = false;

  renderInstallButtonState();
});


// kalau user balik fokus ke tab browser sesudah pasang app,
// kita re-render lagi biar state tombol update
document.addEventListener('visibilitychange', () => {
  if (!document.hidden) {
    renderInstallButtonState();
  }
});
