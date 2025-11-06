<script>
(function () {
  var URL_ANDROID = 'https://play.google.com/store/apps/details?id=id.co.ausi.twa';
  var URL_IOS     = 'https://ausi.co.id/';

  if (location.search.indexOf('no-redirect=1') !== -1) return;

  var isStandalone = (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches)
                  || (window.navigator.standalone === true);
  var isFromTWA = document.referrer && document.referrer.indexOf('android-app://') === 0;
  if (isStandalone || isFromTWA) return;

  var ua  = (navigator.userAgent || '').toLowerCase();
  var plt = (navigator.userAgentData && navigator.userAgentData.platform) || navigator.platform || '';
  var isIOS = /iphone|ipad|ipod/.test(ua)
           || /iPhone|iPad|iPod|iOS/.test(plt)
           || (ua.indexOf('mac') > -1 && 'ontouchend' in document);
  var isAndroid = /android/.test(ua) || /android/i.test(plt);

  var dest = isAndroid ? URL_ANDROID : isIOS ? URL_IOS : null;
  if (!dest) return;

  try {
    var hereURL = new URL(location.href);
    var destURL = new URL(dest, location.href); // normalisasi & trailing slash
    if (hereURL.href !== destURL.href) {
      location.replace(destURL.href);
    }
  } catch (e) {
    location.href = dest; // fallback
  }
})();
</script>
