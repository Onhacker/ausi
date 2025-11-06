<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mengarahkan…</title>
<style>
  :root{
    --bg1:#0b3b6d; --bg2:#0e4a8a;
    --card:#ffffff; --text:#0f172a; --muted:#64748b;
    --accent:#2563eb; --accent-weak:#dbeafe; --ring:#93c5fd;
  }
  @media (prefers-color-scheme: dark){
    :root{ --card:#0b1220; --text:#e5e7eb; --muted:#9aa4b2; --accent-weak:#0b1c3a; --ring:#1e3a8a; }
  }
  html,body{height:100%;}
  body{
    margin:0; font:16px system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial,sans-serif;
    color:var(--text);
    background: radial-gradient(1200px 800px at 50% -100px, var(--bg2), var(--bg1));
    display:grid; place-items:center;
  }
  .wrap{width:min(520px,92%);}
  .card{
    background:var(--card);
    border-radius:18px; padding:22px 18px;
    box-shadow:0 10px 30px rgba(0,0,0,.25), 0 2px 8px rgba(0,0,0,.2);
    border:1px solid rgba(255,255,255,.06);
  }
  .head{
    display:flex; gap:14px; align-items:center; margin-bottom:6px;
  }
  .logo{
    width:46px; height:46px; display:grid; place-items:center;
    border-radius:12px; background:var(--accent-weak); border:1px solid var(--ring);
  }
  .title{font-size:18px; font-weight:700; line-height:1.2;}
  .desc{color:var(--muted); margin:.25rem 0 .75rem;}
  .progress{
    height:10px; background:rgba(0,0,0,.08); border-radius:999px; overflow:hidden;
    position:relative; outline:1px solid rgba(255,255,255,.04);
  }
  .bar{
    height:100%; width:0%; background:var(--accent);
    transition:width .1s linear;
  }
  .meta{display:flex; justify-content:space-between; font-size:13px; color:var(--muted); margin-top:6px;}
  .actions{display:flex; flex-wrap:wrap; gap:10px; margin-top:14px;}
  .btn{
    appearance:none; border:0; border-radius:12px; padding:10px 14px; font-weight:600;
    cursor:pointer; transition:transform .06s ease;
  }
  .btn:active{ transform:translateY(1px); }
  .btn-primary{ background:var(--accent); color:#fff; }
  .btn-ghost{ background:transparent; color:var(--text); border:1px solid rgba(0,0,0,.15); }
  .hint{font-size:12px; color:var(--muted); margin-top:8px}
  .hide{display:none!important;}
  .spinner{
    width:16px;height:16px;border-radius:999px;border:2px solid rgba(0,0,0,.15);
    border-top-color:var(--accent); display:inline-block; vertical-align:-3px; animation:s .8s linear infinite;
    margin-inline-start:6px;
  }
  @keyframes s{to{transform:rotate(1turn)}}
</style>
</head>
<body>
  <div class="wrap">
    <div class="card" role="status" aria-live="polite">
      <div class="head">
        <div class="logo" aria-hidden="true">
          <!-- ikon generik -->
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
            <path d="M3 5.5a2.5 2.5 0 0 1 2.5-2.5h13A2.5 2.5 0 0 1 21 5.5v13a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 18.5v-13Z" stroke="currentColor" opacity=".25" stroke-width="1.5"/>
            <path d="M10 8l6 4-6 4V8Z" fill="currentColor"/>
          </svg>
        </div>
        <div>
          <div id="title" class="title">Mengarahkan…</div>
          <div id="desc" class="desc">Sebentar ya, kami sedang menyiapkan tautan.</div>
        </div>
      </div>

      <div class="progress" aria-hidden="true"><div id="bar" class="bar"></div></div>
      <div class="meta"><span id="statusLabel">Mulai</span><span><span id="sec">3</span> detik</span></div>

      <div class="actions">
        <button id="btnNow" class="btn btn-primary">Buka sekarang <span class="spinner hide" id="spin"></span></button>
        <button id="btnCancel" class="btn btn-ghost">Batal</button>
        <a id="linkSite" class="btn btn-ghost" href="https://ausi.co.id/?no-redirect=1">Buka situs</a>
      </div>
      <div class="hint">Jika tidak pindah otomatis, tekan <strong>Buka sekarang</strong>.</div>
    </div>
  </div>

<script>
(function () {
  // ===== KONFIG =====
  var PKG       = 'id.co.ausi.twa';
  var PLAY_WEB  = 'https://play.google.com/store/apps/details?id=' + PKG;
  var PLAY_INTENT = 'intent://details?id=' + PKG +
    '#Intent;scheme=market;package=com.android.vending;' +
    'S.browser_fallback_url=' + encodeURIComponent(PLAY_WEB) + ';end';
  var URL_IOS = 'https://ausi.co.id/';

  var COUNTDOWN_SEC = 3; // ubah jika ingin 2/4/5 detik

  // ===== GUARD =====
  if (location.search.indexOf('no-redirect=1') !== -1) return;

  var isStandalone = (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches)
                  || (navigator.standalone === true);
  var isFromTWA = document.referrer && document.referrer.indexOf('android-app://') === 0;
  if (isStandalone || isFromTWA) return;

  // ===== DETEKSI OS =====
  var ua  = (navigator.userAgent || '').toLowerCase();
  var plt = (navigator.userAgentData && navigator.userAgentData.platform) || navigator.platform || '';
  var isIOS = /iphone|ipad|ipod/.test(ua)
           || /iPhone|iPad|iPod|iOS/.test(plt)
           || (ua.indexOf('mac') > -1 && 'ontouchend' in document);
  var isAndroid = /android/.test(ua) || /android/i.test(plt);

  // ===== UI nodes =====
  var elTitle = document.getElementById('title');
  var elDesc  = document.getElementById('desc');
  var elBar   = document.getElementById('bar');
  var elSec   = document.getElementById('sec');
  var elStatus= document.getElementById('statusLabel');
  var btnNow  = document.getElementById('btnNow');
  var btnCancel = document.getElementById('btnCancel');
  var spinNow = document.getElementById('spin');
  var linkSite= document.getElementById('linkSite');

  // ===== STATE =====
  var canceled = false, finished = false;

  function setUIForOS(){
    if (isAndroid) {
      elTitle.textContent = 'Membuka Ausi di Play Store';
      elDesc.textContent  = 'Kami akan mengarahkanmu ke Google Play Store untuk membuka aplikasi.';
      linkSite.classList.remove('hide'); // tetap tampil
    } else if (isIOS) {
      elTitle.textContent = 'Menuju Beranda Ausi';
      elDesc.textContent  = 'Kamu akan diarahkan ke halaman utama situs.';
      linkSite.classList.add('hide'); // iOS tujuannya memang situs
    } else {
      elTitle.textContent = 'Membuka Ausi';
      elDesc.textContent  = 'Perangkat tidak terdeteksi, membuka situs.';
    }
  }

  function goAndroid(){
    if (finished) return;
    finished = true;
    spinNow.classList.remove('hide');
    // 1) coba intent (membuka aplikasi Play Store)
    location.href = PLAY_INTENT;
    // 2) safety net: jika intent ditolak (in-app browser), paksa ke web Play
    setTimeout(function(){ location.href = PLAY_WEB; }, 1200);
  }

  function goIOS(){
    if (finished) return;
    finished = true;
    spinNow.classList.remove('hide');
    try {
      var dest = new URL(URL_IOS, location.href);
      if (location.href !== dest.href) location.replace(dest.href);
      else location.href = dest.href;
    } catch(e){ location.href = URL_IOS; }
  }

  function runCountdown(sec){
    var total = sec * 1000;
    var start = performance.now();
    elSec.textContent = sec;
    elStatus.textContent = 'Menunggu';

    function tick(t){
      if (canceled || finished) return;
      var elapsed = t - start;
      var remain  = Math.max(0, total - elapsed);
      var pct = Math.min(100, (elapsed/total)*100);
      elBar.style.width = pct.toFixed(1) + '%';

      var nextSec = Math.ceil(remain/1000);
      if (parseInt(elSec.textContent,10) !== nextSec){
        elSec.textContent = nextSec;
      }

      if (remain <= 0){
        elStatus.textContent = 'Mengalihkan';
        if (isAndroid) goAndroid(); else goIOS();
        return;
      }
      requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  }

  // ===== ACTIONS =====
  btnNow.addEventListener('click', function(){
    if (isAndroid) goAndroid(); else goIOS();
  });
  btnCancel.addEventListener('click', function(){
    canceled = true;
    elStatus.textContent = 'Dibatalkan';
    elDesc.textContent = 'Pengalihan dibatalkan. Kamu bisa membuka secara manual.';
    spinNow.classList.add('hide');
  });

  // ===== START =====
  setUIForOS();
  // kasih 1 frame agar UI sempat paint (menghindari “blank putih” di Android)
  requestAnimationFrame(function(){ setTimeout(function(){ runCountdown(COUNTDOWN_SEC); }, 180); });

})();
</script>
</body>
</html>
