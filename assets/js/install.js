/* assets/js/install.js */
(() => {
  'use strict';

  // --- Deteksi platform ---
  function isIOSUA(){
    const ua = navigator.userAgent || navigator.vendor || '';
    return /iPad|iPhone|iPod/i.test(ua) || (ua.includes('Macintosh') && 'ontouchend' in document);
  }
  function isAndroidUA(){
    const ua = navigator.userAgent || navigator.vendor || '';
    return /Android/i.test(ua);
  }

  // --- Panduan iOS (Add to Home Screen) ---
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

  // --- Utilitas kecil untuk SweetAlert fallback ---
  function whenSwalReady(run, timeout=2500){
    const t0 = Date.now();
    (function tick(){
      if (window.Swal && typeof Swal.fire === 'function') return run(false);
      if (Date.now()-t0 > timeout) return run(true);
      setTimeout(tick, 50);
    })();
  }

  // --- Tampilkan sesuai platform ---
  document.addEventListener('DOMContentLoaded', () => {
    const playBtn    = document.getElementById('playStoreButton');
    const installBtn = document.getElementById('installButton');

    if (isAndroidUA()){
      // Android => hanya Play
      if (playBtn)   playBtn.style.display   = 'inline-flex';
      if (installBtn) installBtn.style.display = 'none';
    } else if (isIOSUA()){
      // iOS => hanya PWA
      if (playBtn)   playBtn.style.display   = 'none';
      if (installBtn) installBtn.style.display = 'inline-flex';

      // klik → tampilkan panduan A2HS
      if (installBtn){
        installBtn.addEventListener('click', (e)=>{
          e.preventDefault();
          window.showIOSInstallGuide(e);
        });
      }
    } else {
      // Platform lain => default tampilkan Play
      if (playBtn)   playBtn.style.display   = 'inline-flex';
      if (installBtn) installBtn.style.display = 'none';
    }
  });

  // (Opsional) info jika sudah standalone di iOS (hanya tombol PWA yg tampil)
  function isStandalone(){
    return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
  }
  window.addEventListener('load', ()=>{
    if (!isIOSUA() || !isStandalone()) return;
    whenSwalReady((fallback)=>{
      if (!fallback) Swal.fire('Sudah Terpasang','Aplikasi berjalan dalam mode mandiri.','info');
    });
  });
})();
