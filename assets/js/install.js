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
    if (!window.Swal) { alert('Buka menu Bagikan → Tambahkan ke Layar Utama'); return false; }
    if (!isSafari) {
      Swal.fire({title:'Buka di Safari', html:'Buka halaman ini di <b>Safari</b> untuk menginstal PWA.<br><br>'+htmlSafari, icon:'info'});
      return false;
    }
    Swal.fire({title:'Instal ke iOS', html:htmlSafari, icon:'info'});
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

/* Tampilkan info “sudah terinstal” sekali saja (persist lintas sesi) */
function showStandaloneNoticeOnce(){
  if (!isAppInstalled()) return;

  const KEY = 'shownStandaloneNotice'; // ganti dari sessionStorage -> localStorage

  // Cek flag persist
  try {
    if (localStorage.getItem(KEY)) return;
  } catch (e) {
    // Jika storage diblok/galat, pakai in-memory fallback biar tidak looping dalam 1 run
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
  // SVG icon putih untuk Android dan iOS
// const ICON_ANDROID = '<svg viewBox="0 0 24 24"><path d="M17.6 9c-.3 0-.6.3-.6.6v5.8c0 .9-.7 1.6-1.6 1.6-.9 0-1.6-.7-1.6-1.6V9.6h-2.8v5.8c0 .9-.7 1.6-1.6 1.6-.9 0-1.6-.7-1.6-1.6V9.6c0-.3-.3-.6-.6-.6s-.6.3-.6.6v2.5c0 .3-.3.6-.6.6s-.6-.3-.6-.6V9.4c0-1.4 1.1-2.5 2.5-2.5h7.2c1.4 0 2.5 1.1 2.5 2.5v2.7c0 .3-.3.6-.6.6s-.6-.3-.6-.6V9.6c0-.3-.3-.6-.6-.6zM9.5 6.4l-.9-1.7c-.1-.3 0-.6.3-.7.3-.1.6 0 .7.3l1 1.9c.5-.2 1-.3 1.5-.3s1 .1 1.5.3l1-1.9c.1-.3.5-.4.7-.3.3.1.4.5.3.7l-.9 1.7c1 .5 1.7 1.5 1.7 2.6H7.8c0-1.2.7-2.1 1.7-2.6zM10 8.5c.3 0 .6-.3.6-.6s-.3-.6-.6-.6-.6.3-.6.6.3.6.6.6zm4 0c.3 0 .6-.3.6-.6s-.3-.6-.6-.6-.6.3-.6.6.3.6.6.6z"/></svg>';
const ICON_ANDROID = `
<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <g fill="#fff">
    <!-- badan + kepala + kaki + antena -->
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

    <!-- tangan kiri -->
    <rect x="3" y="10" width="2" height="7" rx="1" ry="1"/>

    <!-- tangan kanan -->
    <rect x="19" y="10" width="2" height="7" rx="1" ry="1"/>
  </g>
</svg>`;


const ICON_IOS = `
<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
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

// const ICON_IOS = '<svg viewBox="0 0 24 24"><path d="M16.4 1.5c0 1.2-.5 2.3-1.3 3.1-.8.8-2.1 1.5-3.2 1.4-.1-1.1.4-2.3 1.2-3.1.8-.9 2.2-1.5 3.2-1.6.1.1.1.2.1.2zM20.7 17.3c-.7 1.5-1 2.2-1.9 3.5-1.2 1.9-3 4.2-5.1 4.2-1.9 0-2.4-1.2-5-1.2s-3.1 1.2-5.1 1.2c-2.1 0-3.8-2.1-5-4C-2.2 17.6-.9 12.6.9 9.9 2.1 8.1 3.9 7 5.6 7c2 0 3.2 1.2 4.9 1.2S13.2 7 15.4 7c1.5 0 3.1.8 4.3 2.3-3.8 2.1-3.2 7.5-1 9z"/></svg>';

// fallback generic (misal desktop)
const ICON_APP = '<svg viewBox="0 0 24 24"><path d="M4 3h16c.6 0 1 .4 1 1v16c0 .6-.4 1-1 1H4c-.6 0-1-.4-1-1V4c0-.6.4-1 1-1zm1 2v14h14V5H5zm3 2h8c.6 0 1 .4 1 1v8c0 .6-.4 1-1 1H8c-.6 0-1-.4-1-1V8c0-.6.4-1 1-1z"/></svg>';

// fungsi untuk update tampilan tombol sesuai device
function setupInstallButtonUI(){
  const btn   = document.getElementById('installButton');
  if (!btn) return;
  const iconEl  = btn.querySelector('.install-icon');
  const textEl  = btn.querySelector('.install-text');

  // kalau sudah terpasang → kamu boleh ubah text jadi "Open App" biar gak misleading
  if (isAppInstalled()){
    if (iconEl) iconEl.innerHTML = ICON_APP;
    if (textEl) textEl.textContent = 'Open App';
    btn.setAttribute('aria-label','Open App');
    return;
  }

  // deteksi iOS
  if (isIOSUA()){
    if (iconEl) iconEl.innerHTML = ICON_IOS;
    if (textEl) textEl.textContent = 'Install on iOS';
    btn.setAttribute('aria-label','Install on iOS');
    return;
  }

  // default anggap Android / Chrome / PWA capable
  if (iconEl) iconEl.innerHTML = ICON_ANDROID;
  if (textEl) textEl.textContent = 'Install on Android';
  btn.setAttribute('aria-label','Install on Android');
}

/* Klik badge iOS/Android → baru tampilkan prompt/panduan */
document.addEventListener('DOMContentLoaded', () => {
  setupInstallButtonUI();

  const installButton = document.getElementById('installButton');
  if (!installButton) return;

  installButton.addEventListener('click', async (e) => {
    e.preventDefault();

    // Jika sudah standalone (mis. WebAPK) -> beri info (jangan sembunyikan tombol)
    if (isAppInstalled()) {
      return whenSwalReady((fallback)=>{
        if (!fallback) Swal.fire('Aplikasi Sudah Terinstal','Aplikasi sedang berjalan dalam mode mandiri.','info');
        else alert('Aplikasi sudah terinstal (standalone).');
      });
    }

    // iOS: panduan A2HS
    if (isIOSUA()) return window.showIOSInstallGuide(e);

    // Android/Chrome: gunakan prompt jika ada
    if (!deferredPrompt) {
      return whenSwalReady((fallback)=>{
        if (!fallback) Swal.fire('Belum Siap','Aplikasi belum memenuhi syarat PWA untuk ditawarkan instal.','warning');
        else alert('Instal belum siap.');
      });
    }

    deferredPrompt.prompt();
    const choice = await deferredPrompt.userChoice;
    if (choice && choice.outcome === 'accepted') {
      whenSwalReady((fallback)=>{
        if (!fallback) Swal.fire('Berhasil!','Aplikasi sedang diinstal.','success');
      });
    } else {
      whenSwalReady((fallback)=>{
        if (!fallback) Swal.fire('Dibatalkan','Anda membatalkan instalasi.','info');
      });
    }
    deferredPrompt = null;
  });
});

/* Event sukses instal */
window.addEventListener('appinstalled', () => {
  console.log('✅ App installed');
  whenSwalReady((fallback)=>{
    if (!fallback) Swal.fire('Terpasang','Aplikasi berhasil diinstal. Icon akan tampil di menu Hp Anda','success');
  });
});

/* Agar onclick="openPlayStore(event)" aman meski kamu override di tempat lain */
function openPlayStore(e){ return true; }
