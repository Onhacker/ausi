<!-- opsional: sedikit teks biar tidak benar-benar blank -->
<div id="handoff" style="font:16px system-ui; padding:16px; text-align:center;">
  Mengarahkan ke Play Store… <br>Jika tidak pindah otomatis, <a id="manual" href="#">tap di sini</a>.
</div>

<script>
(function () {
  var PKG = 'id.co.ausi.twa';
  var PLAY_WEB = 'https://play.google.com/store/apps/details?id=' + PKG;
  var PLAY_INTENT = 'intent://details?id=' + PKG +
    '#Intent;scheme=market;package=com.android.vending;' +
    'S.browser_fallback_url=' + encodeURIComponent(PLAY_WEB) + ';end';
  var URL_IOS = 'https://ausi.co.id/';

  // bypass manual: /hal/app?no-redirect=1
  if (location.search.indexOf('no-redirect=1') !== -1) return;

  // jangan redirect jika sedang dalam PWA/TWA
  var isStandalone = (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches)
                  || (navigator.standalone === true);
  var isFromTWA = document.referrer && document.referrer.indexOf('android-app://') === 0;
  if (isStandalone || isFromTWA) return;

  // deteksi OS
  var ua  = (navigator.userAgent || '').toLowerCase();
  var plt = (navigator.userAgentData && navigator.userAgentData.platform) || navigator.platform || '';
  var isIOS = /iphone|ipad|ipod/.test(ua)
           || /iPhone|iPad|iPod|iOS/.test(plt)
           || (ua.indexOf('mac') > -1 && 'ontouchend' in document);
  var isAndroid = /android/.test(ua) || /android/i.test(plt);

  // tombol manual fallback (kalau user tap)
  var manual = document.getElementById('manual');
  if (manual) manual.addEventListener('click', function(e){
    e.preventDefault();
    goAndroid();
  });

  function goAndroid(){
    // 1) coba buka Play app via intent://
    location.href = PLAY_INTENT;
    // 2) safety net: kalau intent ditolak (in-app browser), arahkan ke web Play
    setTimeout(function(){ location.href = PLAY_WEB; }, 1200);
  }

  if (isAndroid) {
    // kasih kesempatan 1 frame supaya teks "Mengarahkan…" sempat terlihat
    requestAnimationFrame(function(){ setTimeout(goAndroid, 200); });
  } else if (isIOS) {
    // iOS → root site
    try {
      var dest = new URL(URL_IOS, location.href);
      if (location.href !== dest.href) location.replace(dest.href);
    } catch (e) {
      location.href = URL_IOS;
    }
  }
})();
</script>
