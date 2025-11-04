/* assets/js/install.js */
(() => {
  'use strict';

  // ====== PUNYAMU (dipertahankan) ======
  let deferredPrompt = null;

  function isAppInstalled() {
    return window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true; // iOS Safari
  }
  function isIOSUA() {
    const ua = navigator.userAgent || navigator.vendor || '';
    return /iPad|iPhone|iPod/i.test(ua) || (ua.includes('Macintosh') && 'ontouchend' in document);
  }

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

  function whenSwalReady(run, timeout=3000){
    const t0 = Date.now();
    (function tick(){
      if (window.Swal && typeof Swal.fire === 'function') return run(false);
      if (Date.now()-t0 > timeout) return run(true);
      setTimeout(tick, 50);
    })();
  }

  function showStandaloneNoticeOnce(){
    if (!isAppInstalled()) return;

    const KEY = 'shownStandaloneNotice';
    try { if (localStorage.getItem(KEY)) return; }
    catch (e) { if (window.__shownStandaloneNotice) return; window.__shownStandaloneNotice = true; }

    whenSwalReady((fallback)=>{
      const markDone = ()=>{ try { localStorage.setItem(KEY,'1'); } catch(e){} };
      if (!fallback && window.Swal?.fire) {
        Swal.fire('Aplikasi Sudah Terinstal','Anda menjalankan aplikasi dalam mode mandiri (standalone).','info').then(markDone, markDone);
      } else {
        alert('Aplikasi berjalan dalam mode mandiri (standalone).'); markDone();
      }
    });
  }

  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault(); deferredPrompt = e; console.log('✅ beforeinstallprompt siap.');
  });
  window.addEventListener('load', showStandaloneNoticeOnce);

  // Ikon kamu
  const ICON_ANDROID = `
<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><g fill="#fff">
  <path d="M6 18c0 1.1.9 2 2 2h1v3h2v-3h2v3h2v-3h1c1.1 0 2-.9 2-2V9H6v9zM15.53 4.18l1.3-1.3-.78-.78-1.48 1.48C14.38 3.17 13.23 3 12 3s-2.38.17-2.93.48L7.59 2 6.81 2.88l1.3 1.3C7.61 5.24 7 6.48 7 8h10c0-1.52-.61-2.76-1.47-3.82zM10 6c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1zm4 0c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1z"/>
  <rect x="3" y="10" width="2" height="7" rx="1" ry="1"/><rect x="19" y="10" width="2" height="7" rx="1" ry="1"/>
</g></svg>`.trim();

  const ICON_IOS = `
<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="#fff" d="M19.67 16.34c-.41.94-.6 1.32-1.13 2.13-.88 1.4-2.12 3.14-3.63 3.14-1.36 0-1.72-.89-3.55-.89s-2.25.89-3.6.89c-1.51 0-2.73-1.61-3.61-3.01C2.46 15.6 2.1 11.07 4.07 8.29c.97-1.37 2.52-2.22 4.12-2.24 1.62-.02 2.64.97 3.55.97.9 0 2.45-1.2 4.13-1.02.7.03 2.67.28 3.94 2.11-3.47 1.89-2.91 6.4-.14 8.23zM14.6 4.8c.62-.75 1.1-1.8 1-2.85-1 .04-2.2.68-2.92 1.5-.64.72-1.16 1.78-1.02 2.81 1.1.08 2.22-.56 2.94-1.46z"/></svg>`.trim();

  const ICON_APP = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 10h9a3 3 0 0 1 3 3v1a5 5 0 0 1-5 5H10a5 5 0 0 1-5-5v-1a3 3 0 0 1 3-3z"/><path d="M17 11h1a2 2 0 0 1 0 4h-1"/><path d="M4 20h14"/><path d="M9 4c0 .8-.5 1.2-.5 2s.5 1.2.5 2"/><path d="M12 4c0 .8-.5 1.2-.5 2s.5 1.2.5 2"/><path d="M15 5c0 .8-.5 1.2-.5 2s.5 1.2.5 2"/></svg>';

  // ====== Tambahan minimal untuk Google Play & visibilitas ======
  function isAndroidUA(){
    const ua = navigator.userAgent || navigator.vendor || '';
    return /Android/i.test(ua);
  }

  const ICON_PLAY = `
  <svg class="kOqhQd" aria-hidden="true" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"><path fill="none" d="M0,0h40v40H0V0z"></path><g><path d="M19.7,19.2L4.3,35.3c0,0,0,0,0,0c0.5,1.7,2.1,3,4,3c0.8,0,1.5-0.2,2.1-0.6l0,0l17.4-9.9L19.7,19.2z" fill="#EA4335"></path><path d="M35.3,16.4L35.3,16.4l-7.5-4.3l-8.4,7.4l8.5,8.3l7.5-4.2c1.3-0.7,2.2-2.1,2.2-3.6C37.5,18.5,36.6,17.1,35.3,16.4z" fill="#FBBC04"></path><path d="M4.3,4.7C4.2,5,4.2,5.4,4.2,5.8v28.5c0,0.4,0,0.7,0.1,1.1l16-15.7L4.3,4.7z" fill="#4285F4"></path><path d="M19.8,20l8-7.9L10.5,2.3C9.9,1.9,9.1,1.7,8.3,1.7c-1.9,0-3.6,1.3-4,3c0,0,0,0,0,0L19.8,20z" fill="#34A853"></path></g></svg>`.trim();

  function getPlayUrl(){
    const a = document.getElementById('playStoreButton');
    return a ? (a.dataset.playUrl || a.getAttribute('href') || '') : '';
  }

  // Punyamu: atur ikon/teks tombol install (termasuk “Ngopi Yuk”)
  function setupInstallButtonUI(){
    const btn   = document.getElementById('installButton');
    if (!btn) return;
    const iconEl  = btn.querySelector('.install-icon');
    const textEl  = btn.querySelector('.install-text');

    if (isAppInstalled()){
      if (iconEl) iconEl.innerHTML = ICON_APP;
      if (textEl) textEl.textContent = 'Ngopi Yuk';
      btn.setAttribute('aria-label','Ngopi Yuk');
      return;
    }
    if (isIOSUA()){
      if (iconEl) iconEl.innerHTML = ICON_IOS;
      if (textEl) textEl.textContent = 'Install on iOS';
      btn.setAttribute('aria-label','Install on iOS');
      return;
    }
    if (iconEl) iconEl.innerHTML = ICON_ANDROID;
    if (textEl) textEl.textContent = 'Install on Android';
    btn.setAttribute('aria-label','Install on Android');
  }

  // Baru: tampilkan mana yang perlu
  function setupBadgesVisibility(){
    const playBtn    = document.getElementById('playStoreButton');
    const installBtn = document.getElementById('installButton');

    if (isAppInstalled()){
      if (playBtn)    playBtn.style.display = 'none';
      if (installBtn) installBtn.style.display = 'inline-flex'; // akan jadi "Ngopi Yuk" oleh setupInstallButtonUI()
      return;
    }

    if (isIOSUA()){
      if (playBtn)    playBtn.style.display = 'none';
      if (installBtn) installBtn.style.display = 'inline-flex';
    } else {
      // Android & platform lain → tampilkan Play
      if (playBtn){
        const ic = playBtn.querySelector('.play-icon');
        if (ic && !ic.innerHTML) ic.innerHTML = ICON_PLAY;
        playBtn.style.display = 'inline-flex';
        // href Play sudah di HTML → tidak perlu handler khusus
      }
      if (installBtn) installBtn.style.display = 'none';
    }
  }

  // Klik install → tetap pakai logika kamu
  document.addEventListener('DOMContentLoaded', () => {
    setupInstallButtonUI();
    setupBadgesVisibility();

    const installButton = document.getElementById('installButton');
    if (!installButton) return;

    installButton.addEventListener('click', async (e) => {
      e.preventDefault();

      if (isAppInstalled()) {
        return whenSwalReady((fallback)=>{
          if (!fallback) Swal.fire('Yuk','Kesini aja ngopi bareng.','info');
          else alert('Kesini aja ngopi bareng');
        });
      }

      if (isIOSUA()) { return window.showIOSInstallGuide(e); }

      if (!deferredPrompt) {
        return whenSwalReady((fallback)=>{
          if (!fallback) Swal.fire('Installed','Aplikasi sudah terinstal, cek di home HP anda.','warning');
          else alert('Instal belum siap.');
        });
      }

      deferredPrompt.prompt();
      const choice = await deferredPrompt.userChoice;
      if (choice && choice.outcome === 'accepted') {
        whenSwalReady((fallback)=>{ if (!fallback) Swal.fire('Berhasil!','Aplikasi sedang diinstal.','success'); });
      } else {
        whenSwalReady((fallback)=>{ if (!fallback) Swal.fire('Dibatalkan','Anda membatalkan instalasi.','info'); });
      }
      deferredPrompt = null;
    });
  });

  window.addEventListener('appinstalled', () => {
    console.log('✅ App installed');
    whenSwalReady((fallback)=>{ if (!fallback) Swal.fire('Terpasang','Aplikasi berhasil diinstal.','success'); });
  });

  // Tetap ada agar kompatibel dengan onclick="openPlayStore(event)" di tempat lain
  function openPlayStore(e){ return true; }
})();
