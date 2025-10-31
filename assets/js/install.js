let deferredPrompt = null;

function isAppInstalled() {
  // Chrome PWA bisa pakai standalone / fullscreen / minimal-ui / window-controls-overlay
  const mm = (m) => window.matchMedia(m).matches;
  const displayInstalled =
    mm('(display-mode: standalone)') ||
    mm('(display-mode: fullscreen)') ||
    mm('(display-mode: minimal-ui)') ||
    mm('(display-mode: window-controls-overlay)');

  // iOS Safari A2HS expose navigator.standalone === true
  const iosStandalone = window.navigator.standalone === true;

  return displayInstalled || iosStandalone;
}

function isIOSUA() {
  const ua = navigator.userAgent || navigator.vendor || '';
  return /iPad|iPhone|iPod/i.test(ua) || (ua.includes('Macintosh') && 'ontouchend' in document);
}

/* SweetAlert helper, dll (biarkan punyamu yg lama) ... */

// --- ICONS tetap sama punyamu ---
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
    <rect x="3" y="10" width="2" height="7" rx="1" ry="1"/>
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

/* ini yang ngatur tampilan dan HIDE tombol */
function setupInstallButtonUI(){
  const btn    = document.getElementById('installButton');
  if (!btn) return;

  // kalau udah jadi app → sembunyiin tombol
  if (isAppInstalled()){
    btn.style.display = 'none';
    return;
  }

  // belum app → pastikan tombol visible (kalau sebelumnya disembunyikan)
  btn.style.display = 'inline-flex';

  const iconEl = btn.querySelector('.install-icon');
  const textEl = btn.querySelector('.install-text');

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

/* --- listener klik install --- */
document.addEventListener('DOMContentLoaded', () => {
  setupInstallButtonUI(); // pertama kali

  const installButton = document.getElementById('installButton');
  if (!installButton) return;

  installButton.addEventListener('click', async (e) => {
    e.preventDefault();

    // iOS → tunjukin langkah Add to Home Screen
    if (isIOSUA()) {
      return window.showIOSInstallGuide(e);
    }

    // Android/Chrome → pakai deferredPrompt
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
  });
});

/* kita tangkap beforeinstallprompt -> simpan event */
window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault();
  deferredPrompt = e;
  console.log('✅ beforeinstallprompt siap.');
});

/* setelah semua resource selesai load
   -> cek lagi status (kadang baru ketauan standalone di sini) */
window.addEventListener('load', () => {
  setupInstallButtonUI(); // re-check
  showStandaloneNoticeOnce(); // popup info sekali aja (kode lama kamu)
});

/* kalau user pasang PWA (Android) */
window.addEventListener('appinstalled', () => {
  console.log('✅ App installed');
  setupInstallButtonUI(); // ini bakal hide tombol

  whenSwalReady((fallback)=>{
    if (!fallback) {
      Swal.fire(
        'Terpasang',
        'Aplikasi berhasil diinstal. Icon akan tampil di menu HP Anda.',
        'success'
      );
    }
  });
});

/* bonus: kalau pindah fokus / balik lagi ke app,
   kadang browser update media query → kita cek lagi */
document.addEventListener('visibilitychange', () => {
  if (!document.hidden) {
    setupInstallButtonUI();
  }
});
