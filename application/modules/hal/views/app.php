<script>
(function () {
  // === KONFIGURASI TUJUAN ===
  var URL_ANDROID = 'https://play.google.com/store/apps/details?id=id.co.ausi.twa';
  var URL_IOS     = 'https://ausi.co.id';

  // === OPSIONAL: bypass manual jika perlu (?no-redirect=1) ===
  if (location.search.indexOf('no-redirect=1') !== -1) return;

  // === JANGAN redirect kalau sedang di mode app (PWA/TWA/Standalone) ===
  var isStandalone = (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches)
                  || (window.navigator.standalone === true); // iOS PWA
  var isFromTWA = document.referrer && document.referrer.indexOf('android-app://') === 0;
  if (isStandalone || isFromTWA) return;

  // === DETEKSI OS ===
  var ua  = (navigator.userAgent || '').toLowerCase();
  var plt = (navigator.userAgentData && navigator.userAgentData.platform) || navigator.platform || '';

  // iOS termasuk iPadOS (kadang mengaku 'Mac' tapi touch-enabled)
  var isIOS = /iphone|ipad|ipod/.test(ua)
           || /iPhone|iPad|iPod|iOS/.test(plt)
           || (ua.indexOf('mac') > -1 && 'ontouchend' in document);

  var isAndroid = /android/.test(ua) || /android/i.test(plt);

  // === CEGAH LOOP: jangan redirect kalau sudah di target yang sama ===
  var here = location.href.replace(/\/$/, '');
  if (isAndroid && here.indexOf(URL_ANDROID) !== 0) {
    location.replace(URL_ANDROID);     // replace agar tombol Back tidak balik ke redirector
  } else if (isIOS && here.indexOf(URL_IOS) !== 0) {
    location.replace(URL_IOS);
  }
})();
</script>
