/* assets/js/install.js */
(() => {
  'use strict';

  // --- Deteksi platform ---
  function isAndroidUA(){
    const ua = navigator.userAgent || navigator.vendor || '';
    return /Android/i.test(ua);
  }
  function isIOSUA(){
    const ua = navigator.userAgent || navigator.vendor || '';
    // iPadOS modern kadang terdeteksi "Mac"; cek touch points
    return /iPad|iPhone|iPod/i.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
  }

  // --- Pop-up panduan iOS (Add to Home Screen) ---
  function ensureIOSGuide(){
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

      if (!window.Swal) { // fallback tanpa SweetAlert
        if (!isSafari) alert('Buka halaman ini di Safari, lalu Bagikan → Tambahkan ke Layar Utama');
        else alert('Bagikan → Tambahkan ke Layar Utama → Tambahkan');
        return false;
      }

      if (!isSafari) {
        Swal.fire({
          title:'Buka di Safari',
          html:'Untuk menginstal di iOS, buka halaman ini di <b>Safari</b>.<br><br>'+htmlSafari,
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
  }

  document.addEventListener('DOMContentLoaded', () => {
    const playBtn    = document.getElementById('playStoreButton');
    const installBtn = document.getElementById('installButton');

    ensureIOSGuide();

    if (isAndroidUA()){
      // Android → hanya Play
      if (playBtn)   playBtn.style.display   = 'inline-flex';
      if (installBtn) installBtn.style.display = 'none';
    } else if (isIOSUA()){
      // iOS → hanya PWA
      if (playBtn)   playBtn.style.display   = 'none';
      if (installBtn) installBtn.style.display = 'inline-flex';

      // Klik = tampilkan panduan A2HS
      if (installBtn){
        installBtn.addEventListener('click', (e)=>{
          e.preventDefault();
          window.showIOSInstallGuide(e);
        });
      }
    } else {
      // Default lain (desktop) → Google Play
      if (playBtn)   playBtn.style.display   = 'inline-flex';
      if (installBtn) installBtn.style.display = 'none';
    }
  });
})();
