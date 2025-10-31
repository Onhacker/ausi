let deferredPrompt = null;

function isAppInstalled() {
  return window.matchMedia('(display-mode: standalone)').matches
      || window.navigator.standalone === true; // iOS Safari
}

function isIOSUA() {
  const ua = navigator.userAgent || navigator.vendor || '';
  return /iPad|iPhone|iPod/i.test(ua) || (ua.includes('Macintosh') && 'ontouchend' in document);
}

/* Pastikan panduan iOS tersedia */
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

/* Tunggu SweetAlert siap sebelum menampilkan popup (maks 3 detik) */
function whenSwalReady(run, timeout=3000){
  const t0 = Date.now();
  (function tick(){
    if (window.Swal && typeof Swal.fire === 'function') return run(false);
    if (Date.now()-t0 > timeout) return run(true); // fallback
    setTimeout(tick, 50);
  })();
}

/* Opsional: kasih info 1x bahwa user sudah buka sebagai app mandiri */
function showStandaloneNoticeOnce(){
  if (!isAppInstalled()) return;

  const KEY = 'shownStandaloneNotice'; // pakai localStorage biar persist lintas sesi

  try {
    if (localStorage.getItem(KEY)) return;
  } catch (e) {
    if (window.__shownStandaloneNotice) return;
    window.__shownStandaloneNotice = true;
  }

  whenSwalReady((fallback)=>{
    const markDone = () => {
      try { localStorage.setItem(KEY, '1'); } catch (e) {}
    };

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

/* Tangkap PWA prompt — JANGAN auto-show */
window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault();
  deferredPrompt = e;
  console.log('✅ beforeinstallprompt siap.');
});

/* Jalankan setelah semua resource termuat (lebih aman di Android PWA) */
window.addEventListener('load', showStandaloneNoticeOnce);


/* ===== ICON SVG ===== */

const ICON_ANDROID = `
<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <g fill="#fff">
    <!-- body + head + legs + antenna -->
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

    <!-- arms -->
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

/* fallback generic kalau suatu saat mau dipakai lagi */
const ICON_APP = `
<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <path fill="#fff" d="M4 3h16c.6 0 1 .4 1 1v16c0 .6-.4 1-1 1H4c-.6 0-1-.4-1-1V4c0-.6.4-1 1-1zm1 2v14h14V5H5zm3 2h8c.6 0 1 .4 1 1v8c0 .6-.4 1-1 1H8c-.6 0-1-.4-1-1V8c0-.6.4-1 1-1z"/>
</svg>`;

/* Update tampilan tombol sesuai device */
function setupInstallButtonUI(){
  const btn    = document.getElementById('installButton');
  if (!btn) return;

  const iconEl = btn.querySelector('.install-icon');
  const textEl = btn.querySelector('.install-text');

  // sudah terinstal? langsung hide tombol
  if (isAppInstalled()){
    btn.style.display = 'none';
    return;
  }

  // iOS
  if (isIOSUA()){
    if (iconEl) iconEl.innerHTML = ICON_IOS;
    if (textEl) textEl.textContent = 'Install on iOS';
    btn.setAttribute('aria-label','Install on iOS');
    return;
  }

  // default = Android / Chrome
  if (iconEl) iconEl.innerHTML = ICON_ANDROID;
  if (textEl) textEl.textContent = 'Install on Android';
  btn.setAttribute('aria-label','Install on Android');
}


/* Klik badge → jalanin panduan / prompt */
document.addEventListener('DOMContentLoaded', () => {
  setupInstallButtonUI();

  const installButton = document.getElementById('installButton');
  if (!installButton) return;

  installButton.addEventListener('click', async (e) => {
    e.preventDefault();

    // iOS → tampilkan instruksi Add to Home Screen
    if (isIOSUA()) {
      return window.showIOSInstallGuide(e);
    }

    // Android / Chrome → panggil beforeinstallprompt
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

/* Setelah sukses install (Android/Chrome biasanya trigger ini) */
window.addEventListener('appinstalled', () => {
  console.log('✅ App installed');

  const btn = document.getElementById('installButton');
  if (btn) {
    btn.style.display = 'none';
  }

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

/* dummy biar onclick="openPlayStore(event)" ga error di tempat lain */
function openPlayStore(e){ return true; }
