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

/* Klik badge iOS/Android → baru tampilkan prompt/panduan */
document.addEventListener('DOMContentLoaded', () => {
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
    if (!fallback) Swal.fire('Terpasang','Aplikasi berhasil diinstal.','success');
  });
});

/* Agar onclick="openPlayStore(event)" aman meski kamu override di tempat lain */
function openPlayStore(e){ return true; }
