<!-- HAPUS baris ini jika jQuery sudah ada di halaman -->
<script src="<?php echo base_url("assets/admin") ?>/js/jquery-3.1.1.min.js"></script>

<?php
$rec = $this->fm->web_me();
$store_lat     = $rec->store_lat;
$store_lng     = $rec->store_lng;
$base_km       = $rec->base_km;
$base_fee      = $rec->base_fee;
$per_km        = $rec->per_km;
$max_radius_m  = $rec->max_radius_m;
$batas_free    = (int)($rec->batas_free_ongkir ?? 0);
?>

<?php if ((int)$total >= $batas_free): ?>
  <!-- MODE: GRATIS ONGKIR -->
  <input type="hidden" name="free_ongkir" value="1">
<?php endif; ?>

<!-- Hidden input yang dipakai JS -->
<input type="hidden" name="ongkir"        id="ongkirInput">
<input type="hidden" name="dest_lat"      id="dest_lat">
<input type="hidden" name="dest_lng"      id="dest_lng">
<input type="hidden" name="distance_m"    id="distance_m">
<input type="hidden" name="ongkir_token"  id="ongkir_token">

<!-- =========================
     FORM ALAMAT + TOMBOL MAP/GPS
     ========================= -->
<div class="form-group col-md-6">
  <label for="alamatKirim" class="mb-1">
    Alamat Antar <span class="text-muted">(lengkap ya!)</span>
  </label>

  <textarea
    id="alamatKirim"
    class="form-control shadow-sm"
    name="alamat"
    rows="3"
    required
    minlength="15"
    maxlength="300"
    aria-describedby="alamatTips alamatCount"></textarea>

  <small id="alamatTips" class="form-text text-muted mt-1 d-flex">
    <i class="mdi mdi-lightbulb-on-outline mr-1" aria-hidden="true"></i>
    Tulis: jalan/no, gang, patokan (masjid/sekolah), akses (lewat sungai), dan shareloc.
  </small>

  <?php if ((int)$total >= $batas_free): ?>
    <!-- Banner info gratis ongkir -->
    <div class="mt-2">
      <div class="alert alert-success d-flex align-items-start mb-2" role="alert">
        <i class="mdi mdi-truck-fast mr-2" aria-hidden="true"></i>
        <div>
          <strong>Gratis Ongkir!</strong><br>
          Belanja Anda sudah mencapai minimal
          Rp<?= number_format($batas_free, 0, ',', '.') ?>.<br>
          Silakan pilih titik pengantaran.
        </div>
      </div>
    </div>
  <?php endif; ?>

  <style>.delivery-actions{display:flex;flex-wrap:wrap;margin-top:.75rem;gap:.5rem}.delivery-btn{flex:1 1 calc(50% - .5rem);min-width:calc(50% - .5rem);display:flex;align-items:center;justify-content:center;font-weight:600;border:0;border-radius:.75rem;color:#fff;padding:.6rem .75rem;line-height:1.2;box-shadow:0 .5rem 1rem rgb(0 0 0 / .15);text-shadow:0 1px 2px rgb(0 0 0 / .4)}.delivery-btn .icon{font-size:1rem;margin-right:.5rem;line-height:0;display:inline-flex;align-items:center;justify-content:center}.btn-map-grad{background-image:linear-gradient(135deg,#2563eb 0%,#1d4ed8 50%,#0f2e8a 100%)}.btn-gps-grad{background-image:linear-gradient(135deg,#facc15 0%,#eab308 40%,#b45309 100%);color:#1f1f1f;text-shadow:0 1px 2px rgb(255 255 255 / .4);box-shadow:0 .5rem 1rem rgb(180 83 9 / .25)}@media (max-width:360px){.delivery-btn{flex:1 1 100%;min-width:100%}}</style>

	<div class="delivery-actions">
	  <button
	    type="button"
	    id="btnMapOngkir"
	    class="delivery-btn btn-map-grad"
	  >
	    <span class="icon">
	      <i class="mdi mdi-map-marker-outline" aria-hidden="true"></i>
	    </span>
	    <span>Pilih Lokasi</span>
	  </button>

	  <button
	    type="button"
	    id="btnUseMyLocation"
	    class="delivery-btn btn-gps-grad"
	  >
	    <span class="icon">
	      <i class="mdi mdi-crosshairs-gps" aria-hidden="true"></i>
	    </span>
	    <span>Posisi saya</span>
	  </button>
	</div>

</div>

<span id="ongkirHint" class="text-dark mb-2"></span>

  <!-- ====== MODAL (HANYA SATU — WAJIB ADA .modal-dialog & .modal-content) ====== -->
  <div class="modal fade " id="modalOngkirMap"
       tabindex="-1" role="dialog" aria-hidden="true"
       data-backdrop="static" data-keyboard="false"
       data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg  modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <!-- <h5 class="modal-title mb-0">Hitung Ongkir via Peta</h5> -->
          <button type="button" class="btn btn-info btn-sm mr-2" id="btnReloadMap">
            <span class="spinner-border spinner-border-sm d-none" id="reloadSpin" role="status" aria-hidden="true"></span>
            <span class="lbl">Reload Peta</span>
          </button>
          <button type="button" class="close btn-close"
                  data-dismiss="modal" data-bs-dismiss="modal"
                  aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
        </div>

        <div class="modal-body p-0">
          <div id="modalOngkirLoader" class="p-4 text-center">
            <div class="spinner-border" role="status" aria-hidden="true"></div>
            <div class="mt-2">Memuat peta…</div>
          </div>
          <div id="modalOngkirBody"></div> <!-- HTML fragmen peta via AJAX -->
        </div>
      </div>
    </div>
  </div>

  <style>
    /* Modal di paling depan */
    #modalOngkirMap { z-index: 221000 !important; }

    /* Backdrop khusus tanpa blur */
    .modal-backdrop.ongkir-backdrop{
      z-index: 220990 !important;
      position: fixed !important;
      background: rgba(0,0,0,.55) !important;
      backdrop-filter: none !important;
      -webkit-backdrop-filter: none !important;
      filter: none !important;
    }
    .btn .spinner-border.btn-spin { display:inline-block; vertical-align: -0.125em; }

    /* Nonaktifkan blur global saat modal ini terbuka */
    body.noblur-backdrop .modal-backdrop{
      backdrop-filter:none !important; -webkit-backdrop-filter:none !important; filter:none !important;
    }
    body.noblur-backdrop .content,
    body.noblur-backdrop .page-wrapper,
    body.noblur-backdrop .wrapper,
    body.noblur-backdrop main,
    body.noblur-backdrop #app{
      filter:none !important; -webkit-filter:none !important; transform:none !important;
    }
  </style>

  <!-- =========================
       SEMUA SCRIPT DI BAWAH INI ADALAH KODEMU SENDIRI (TIDAK DIUBAH)
       ========================= -->
       <script>
  const ROAD_ONLY = true; // Wajib rute jalan
</script>

  <script>
  (function(){
    if (window.__ONGKIR_MAP_INIT__) return;  // anti duplikat init
    window.__ONGKIR_MAP_INIT__ = true;

    // ====== Endpoint fragmen peta (kirim param agar konsisten) ======
    var LOAD_URL = "<?= site_url('produk/load_map') ?>"
    // make LOAD_URL accessible globally
    window.__ONGKIR_LOAD_URL = "<?= site_url('produk/load_map') ?>";

    // ===== ICON FACTORY (aman sebelum Leaflet siap) =====
    var __MAP_ICONS__ = null;
    function getMapIcons(){
      if (__MAP_ICONS__) return __MAP_ICONS__;
      if (!(window.L && L.icon)) return null; // Leaflet belum siap

      __MAP_ICONS__ = {
        store: L.icon({
          iconUrl: '<?= base_url("assets/images/mark.webp") ?>',
          iconRetinaUrl: '<?= base_url("assets/images/mark.webp") ?>',
          iconSize: [50, 50],
          iconAnchor: [16, 28],
          popupAnchor: [0, -24],
          shadowSize: [41, 41],
          shadowAnchor: [13, 41]
        }),
        destOk: L.icon({
          iconUrl: '<?= base_url("assets/images/maker2.webp") ?>',
          iconSize: [28, 40],
          iconAnchor: [14, 39],
          popupAnchor: [0, -32],
          shadowSize: [41, 41],
          shadowAnchor: [13, 41]
        }),
        destBad: L.icon({
          iconUrl: '<?= base_url("assets/images/marker2.webp") ?>',
          iconSize: [28, 40],
          iconAnchor: [14, 39],
          popupAnchor: [0, -32],
          shadowSize: [41, 41],
          shadowAnchor: [13, 41]
        })
      };
      return __MAP_ICONS__;
    }

    // ====== Helper open/close modal: BS4 (jQuery) & BS5 (native) ======
    function openModal(el){
      if (window.jQuery && jQuery.fn && jQuery.fn.modal) jQuery(el).modal('show');
      else if (window.bootstrap && bootstrap.Modal) bootstrap.Modal.getOrCreateInstance(el).show();
      else alert('Bootstrap modal plugin tidak ditemukan.');
    }
    function closeModal(el){
      if (window.jQuery && jQuery.fn && jQuery.fn.modal) jQuery(el).modal('hide');
      else if (window.bootstrap && bootstrap.Modal) bootstrap.Modal.getOrCreateInstance(el).hide();
    }

    // ====== API dipanggil dari fragmen peta ======
    /* Sinkron dengan tampilan di panel peta (updateInfo) */
window.__applyOngkirFromMap = function () {
  var fee, lat, lng, dist, label = '', isFree = false;

  // --- dukung object atau positional ---
  if (arguments.length === 1 && typeof arguments[0] === 'object') {
    var o = arguments[0] || {};
    fee   = +((o.fee ?? o.ongkir) || 0);
    lat   = +o.lat;
    lng   = +o.lng;
    dist  = +((o.distance_m ?? o.distance) || 0);
    label = String(o.labelText ?? o.label ?? '');
    isFree = !!o.free;
  } else {
    var a0 = +arguments[0], a1 = +arguments[1], a2 = +arguments[2], a3 = +arguments[3];
    label  = String(arguments[4] || '');
    // deteksi urutan argumen
    if (Math.abs(a0) <= 90 && Math.abs(a1) <= 180) { lat = a0; lng = a1; fee = +a2; dist = +a3; }
    else { fee = +a0; lat = a1; lng = a2; dist = +a3; }
  }

  // --- gratis? deteksi dari hidden atau flag ---
  if (document.querySelector('input[name="free_ongkir"]')) isFree = true;

  // --- pembulatan ribuan untuk mode normal ---
  var finalFeeDisplay = Math.ceil((+fee || 0) / 1000) * 1000;

  // === SENTINEL untuk gratis ongkir ===
  var FREE_SENTINEL = 1;                 // <- sesuai permintaan: "ongkirnya 1"
  var feeToField = isFree ? FREE_SENTINEL : finalFeeDisplay; // ke hidden input

  // --- isi hidden field ---
  var $fee = document.getElementById('ongkirInput');
  if ($fee) $fee.value = feeToField;

  var $lat = document.getElementById('dest_lat');
  var $lng = document.getElementById('dest_lng');
  var $dst = document.getElementById('distance_m');
  if ($lat) $lat.value = +lat || 0;
  if ($lng) $lng.value = +lng || 0;
  if ($dst) $dst.value = Math.round(+dist || 0);

  // --- render hint (UI tetap nol jika gratis) ---
  function esc(s){return String(s||'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
  var km = ((+dist||0)/1000).toFixed(2);
  var tujuanText = label ? esc(label) : ((+lat).toFixed(6)+', '+(+lng).toFixed(6));

  var $hint = document.getElementById('ongkirHint');
  if ($hint){
    $hint.innerHTML = isFree
      ? (
        '<div class="p-2">'
        + 'Posisi Anda : <b>'+ tujuanText +'</b><br>'
        + 'Jarak : <b>'+ km +' km</b><br>'
        + '<span class="text-success">🎉 Gratis ongkir diterapkan.</span>'
        + '</div>'
      )
      : (
        '<div class="p-2">'
        + 'Tujuan Anda : <b>'+ tujuanText +'</b><br>'
        + 'Ongkir: <b>Rp'+ finalFeeDisplay.toLocaleString('id-ID') +'</b>'
        + '<br>Jarak: <b>'+ km +' km</b>'
        + '</div>'
      );
  }

  // --- tutup modal jika ada ---
  try{
    var modal = document.getElementById('modalOngkirMap') || document.getElementById('modalSelectOnlyMap');
    if (modal){
      if (window.jQuery && jQuery.fn && jQuery.fn.modal) jQuery(modal).modal('hide');
      else if (window.bootstrap && bootstrap.Modal) bootstrap.Modal.getOrCreateInstance(modal).hide();
    }
  }catch(e){}
};

    // ====== Leaflet lazy loader ======
    function ensureLeaflet(cb){
      if (window.L && typeof L.map === 'function') return cb();
      var cssId = 'leaflet-css', jsId = 'leaflet-js';
      if (!document.getElementById(cssId)){
        var l = document.createElement('link');
        l.id = cssId; l.rel='stylesheet';
        l.href='<?= base_url("assets/min/peta.min.css") ?>';
        document.head.appendChild(l);
      }
      var s = document.getElementById(jsId);
      if (!s){
        s = document.createElement('script');
        s.id = jsId; s.src = '<?= base_url("assets/min/peta.min.js") ?>';
        s.onload = cb;
        document.body.appendChild(s);
      } else {
        if (s.readyState === 'loaded' || s.readyState === 'complete') cb();
        else s.addEventListener('load', cb, {once:true});
      }
    }

    /* ============== Leaflet + OSRM + Nominatim (alamat otomatis) ============== */
    (function(){
      function esc(s){ var d = document.createElement('div'); d.textContent = String(s||''); return d.innerHTML; }
      function formatOsmAddress(addr, display_name){
        if (!addr) return display_name || '';
        var line1 = [];
        if (addr.road) line1.push(addr.road + (addr.house_number ? ' ' + addr.house_number : ''));
        var area = addr.neighbourhood || addr.hamlet || addr.suburb || addr.village || addr.district;
        if (area) line1.push(area);
        var city = addr.city || addr.town || addr.municipality || addr.county || addr.regency || addr.state_district;
        var line2 = [];
        if (city) line2.push(city);
        if (addr.state) line2.push(addr.state);
        if (addr.postcode) line2.push(addr.postcode);
        var shortStr = (line1.join(', ') + (line2.length ? ' — ' + line2.join(', ') : '')).trim();
        if (shortStr.replace(/[\s—,]/g,'') === '') shortStr = display_name || '';
        return shortStr;
      }

      window.initOngkirMapFromHtml = function(){
        var wrap = document.getElementById('ongkirMapWrap');
        if (!wrap) return;

        var storeLat = parseFloat(wrap.getAttribute('data-store-lat')) || 0;
        var storeLng = parseFloat(wrap.getAttribute('data-store-lng')) || 0;
        var baseKm   = parseFloat(wrap.getAttribute('data-base-km'))  || 1;
        var baseFee  = parseInt  (wrap.getAttribute('data-base-fee'), 10) || 5000;
        var perKm    = parseInt  (wrap.getAttribute('data-per-km'),  10) || 1000;

        var mapEl    = document.getElementById('mapInModal');
        var infoEl   = document.getElementById('mapInfo');
        var btnUse   = document.getElementById('btnUseOngkir');
        var btnMyLoc = document.getElementById('btnUseMyLoc');
        // var btnMyLoc = document.getElementById('btnUseMyLoc');

			// helper loading state utk tombol "Posisi Saya"
			function startLocLoading(){
			  if (!btnMyLoc) return;
			  btnMyLoc.setAttribute('aria-busy','true');
			  btnMyLoc.disabled = true;

			  var spin = btnMyLoc.querySelector('.spin');
			  if (spin) spin.classList.remove('d-none');

			  var ico = btnMyLoc.querySelector('.icon');
			  if (ico) ico.classList.add('d-none');

			  var txt = btnMyLoc.querySelector('.txt');
			  if (txt) txt.textContent = 'Mencari lokasi...';
			}

			function stopLocLoading(){
			  if (!btnMyLoc) return;
			  btnMyLoc.setAttribute('aria-busy','false');
			  btnMyLoc.disabled = false;

			  var spin = btnMyLoc.querySelector('.spin');
			  if (spin) spin.classList.add('d-none');

			  var ico = btnMyLoc.querySelector('.icon');
			  if (ico) ico.classList.remove('d-none');

			  var txt = btnMyLoc.querySelector('.txt');
			  if (txt) txt.textContent = 'Posisi Saya';
			}


        var radius = <?= json_encode((int)($rec->max_radius_m ?? 0)) ?>;
        var MAX_RADIUS_KM = radius /1000;
        var MAX_RADIUS_M  = radius;
        function withinService(distM){ return distM <= MAX_RADIUS_M + 1e-6; }

        ensureLeaflet(function(){
          var m = L.map(mapEl).setView([storeLat, storeLng], 14);
          try { m.invalidateSize(); } catch(e){}
          try { requestAnimationFrame(function(){ try{ m.invalidateSize(); }catch(e){} }); } catch(e){}
          setTimeout(function(){ try{ m.invalidateSize(); }catch(e){} }, 300);

          window.__leafletMapInModal = m;

          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom:19}).addTo(m);

          var icons = getMapIcons();
          L.marker([storeLat, storeLng], { icon: icons ? icons.store : undefined }).addTo(m).bindPopup('Ausi Billiard & Cafe');

          var radiusCircle = L.circle([storeLat, storeLng], {
            radius: MAX_RADIUS_M, fill:false, weight:2, dashArray:'6,6', color:'#0d6efd'
          }).addTo(m);

          var destMarker = null;
          function refreshDestIcon(){
            var _icons = getMapIcons();
            if (!destMarker || !_icons) return;
            if (current.addrPending){
              destMarker.setIcon(_icons.destOk);
            } else {
              destMarker.setIcon(current.allowed ? _icons.destOk : _icons.destBad);
            }
          }

          var current = { lat:null, lng:null, dist:0, ongkir:0, isRoad:false, address:'', addressShort:'', allowed:false, addrPending:false };

          function haversine(lat1, lon1, lat2, lon2){
            var R = 6371000, toRad = d=>d*Math.PI/180;
            var dLat = toRad(lat2-lat1), dLon = toRad(lon2-lon1);
            var a = Math.sin(dLat/2)**2 + Math.cos(toRad(lat1))*Math.cos(toRad(lat2))*Math.sin(dLon/2)**2;
            return 2*R*Math.asin(Math.sqrt(a));
          }

          var btnUse = document.getElementById('btnUseOngkir');

          if (btnUse && !btnUse.dataset.bound_lock){
            btnUse.onclick = null;
            btnUse.addEventListener('click', function(){
              if (current.lat == null || !current.allowed || current.addrPending){
                alert(current.addrPending ? 'Tunggu alamat selesai ditentukan…' : 'Maaf, di luar radius layanan (maks '+MAX_RADIUS_KM+' km).');
                return;
              }
              setBtnLoading(btnUse, true);
              var formData = new URLSearchParams();
              formData.append('lat', current.lat);
              formData.append('lng', current.lng);

              var metaName = document.querySelector('meta[name="csrf-name"]');
              var metaHash = document.querySelector('meta[name="csrf-hash"]');
              if (metaName && metaHash){
                formData.append(metaName.getAttribute('content'), metaHash.getAttribute('content'));
              }

              fetch("<?= site_url('produk/lock_ongkir') ?>", {
                method: 'POST',
                headers: { 'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8' },
                body: formData.toString(),
                credentials: 'same-origin'
              })
              .then(function(r){ return r.json(); })
              .then(function(j){
                setBtnLoading(btnUse, false);
                if (!j || !j.ok){
                  alert((j && j.msg) ? j.msg : 'Gagal mengunci ongkir.');
                  return;
                }
                var el = document.querySelector('input[name="ongkir_token"]');
                if (!el){
                  el = document.createElement('input');
                  el.type = 'hidden'; el.name = 'ongkir_token'; el.id = 'ongkir_token';
                  var f = document.querySelector('form'); if (f) f.appendChild(el);
                }
                el.value = j.token;

                var label = current.addressShort
                  ? current.addressShort
                  : ('Koordinat: ' + current.lat.toFixed(6) + ', ' + current.lng.toFixed(6)
                    + (current.isRoad ? ' (jarak jalan)' : ' (jarak lurus)'));

                window.__applyOngkirFromMap(j.fee, current.lat, current.lng, j.distance_m, label);
              })
              .catch(function(){
                setBtnLoading(btnUse, false);
                alert('Gagal terhubung ke server.');
              });
            });
            btnUse.dataset.bound_lock = '1';
          }

          function getDrivingDistance(lat1, lng1, lat2, lng2){
            var url = 'https://router.project-osrm.org/route/v1/driving/'
                      + [lng1,lat1].join(',') + ';' + [lng2,lat2].join(',')
                      + '?overview=false&alternatives=false&steps=false';
            var ctrl = new AbortController();
            var tmo = setTimeout(function(){ try{ctrl.abort();}catch(e){} }, 9000);
            return fetch(url, {signal: ctrl.signal, mode:'cors'})
              .then(r => r.ok ? r.json() : Promise.reject(new Error('HTTP '+r.status)))
              .then(j => { clearTimeout(tmo);
                if (!j || j.code!=='Ok' || !j.routes || !j.routes[0]) throw new Error('No route');
                return j.routes[0].distance || 0;
              })
              .catch(err => { clearTimeout(tmo); throw err; });
          }

          function hitungOngkir(distance_m){
            var km = distance_m/1000;
            if (km <= baseKm) return baseFee;
            var extraKm = Math.ceil((km - baseKm) * 2) / 2;
            return baseFee + Math.max(0, extraKm) * perKm;
          }

          var geocodeTimer = null, geocodeToken = 0;
          function scheduleReverseGeocode(lat, lng){
            if (geocodeTimer) clearTimeout(geocodeTimer);
            current.addrPending = true;
            updateInfo(true);
            refreshDestIcon();
            geocodeTimer = setTimeout(function(){ reverseGeocode(lat, lng); }, 500);
          }

          function reverseGeocode(lat, lng){
            var my = ++geocodeToken;
            current.addressShort = '';
            updateInfo(true);
            refreshDestIcon();

            var url = "<?= site_url('produk/reverse_geocode') ?>"
                    + "?lat=" + encodeURIComponent(lat)
                    + "&lon=" + encodeURIComponent(lng);

            var ctrl = new AbortController();
            var tmo = setTimeout(function(){ try{ctrl.abort();}catch(e){} }, 9000);

            fetch(url, { signal: ctrl.signal, headers: { 'Accept':'application/json' } })
              .then(r => r.ok ? r.json() : Promise.reject(new Error('HTTP '+r.status)))
              .then(j => {
                clearTimeout(tmo);
                if (my !== geocodeToken) return;
                var shortAddr = formatOsmAddress(j.address, j.display_name);
                current.address      = j.display_name || '';
                current.addressShort = shortAddr || '';
                current.addrPending  = false;
                updateInfo();
              })
              .catch(function(){
                clearTimeout(tmo);
                if (my !== geocodeToken) return;
                current.address = '';
                current.addressShort = '';
                current.addrPending  = false;
                updateInfo();
              });
          }

          function updateInfo(pendingAddr){
  if (!infoEl) return;

  // cek apakah ini mode GRATIS ONGKIR
  // (akan true kalau di form ada <input name="free_ongkir" ...>)
  var IS_FREE = !!document.querySelector('input[name="free_ongkir"]');

  // kalau kita wajib pakai jarak via jalan tapi rute jalan belum tersedia
  if (ROAD_ONLY && current.lat != null && !current.isRoad){
    infoEl.innerHTML =
      '<div class="p-2 text-danger">Menunggu jarak <b>via jalan</b>. Rute belum tersedia.</div>';
    if (btnUse) btnUse.disabled = true;
    return;
  }

  // belum ada titik tujuan sama sekali
  if (current.lat == null){
    infoEl.innerHTML =
      '<div class="p-2">Klik peta untuk pilih titik anda. Klik 📍 Posisi saya untuk deteksi otomatis lokasi anda.</div>';
    btnUse && (btnUse.disabled = true);
    return;
  }

  var km   = (current.dist/1000).toFixed(2);
  var fee  = Math.ceil(current.ongkir/1000)*1000;
  var label = current.isRoad
    ? ('Jarak jalanan' + (current.roadProvider ? ' · ' + current.roadProvider : ''))
    : 'Jarak lurus';

  var tujuanText = current.addressShort
    ? esc(current.addressShort)
    : (
        pendingAddr
        ? 'Menunggu respon satelite onhacker… ('+current.lat.toFixed(6)+', '+current.lng.toFixed(6)+')'
        : current.lat.toFixed(6)+', '+current.lng.toFixed(6)
      );

  var warn = '';
  if (!current.allowed) {
    if (!window.__lastRadiusWarnAt || Date.now() - window.__lastRadiusWarnAt > 4000) {
      window.__lastRadiusWarnAt = Date.now();
      if (window.Swal && Swal.fire) {
        Swal.fire({
          icon: 'warning',
          title: 'Di luar jangkauan',
          html: `Jarak kamu <b>${km} km</b> (maks ${MAX_RADIUS_KM} km).<br>Lokasinya kejauhan.`,
          confirmButtonText: 'Oke'
        });
      } else {
        alert('Di luar jangkauan — jarak kamu ' + km + ' km (maks ' + MAX_RADIUS_KM + ' km).');
      }
    }
  }

  // RENDER INFO:
  // - jika free ongkir: JANGAN tampilkan "Estimasi Ongkir"
  // - jika normal: tampilkan estimasi ongkir
  if (IS_FREE){
    infoEl.innerHTML =
      '<div class="p-2">'
      + 'Tujuan: <b>' + tujuanText + '</b><br>'
      + label + ': <b>' + km + ' km</b><br>'
      + '<span class="text-success">🎉 Gratis ongkir berlaku.</span>'
      + '</div>'
      + warn;
  } else {
    infoEl.innerHTML =
      '<div class="p-2">'
      + 'Tujuan: <b>' + tujuanText + '</b><br>'
      + label + ': <b>' + km + ' km</b>'
      + ' · Estimasi Ongkir: <b>Rp' + fee.toLocaleString('id-ID') + '</b>'
      + '</div>'
      + warn;
  }

  var isPending = !!pendingAddr || !!current.addrPending;
  if (btnUse) btnUse.disabled = (!current.allowed || isPending);
}


          var routeToken = 0;
          var routeTimer = null;

          function calcRoute(lat, lng){
            var myToken = ++routeToken;
            if (infoEl){
              infoEl.innerHTML = '<div class="p-2">Sedangan Mengukur jarak Posisi anda <b>via jalanan</b>…</div>';
              btnUse && (btnUse.disabled = true);
            }
            if (routeTimer) clearTimeout(routeTimer);

            routeTimer = setTimeout(function(){
              getDrivingDistanceMulti(storeLat, storeLng, lat, lng)
                .then(function(res){
                  if (myToken !== routeToken) return;
                  current.lat = lat; current.lng = lng;
                  current.dist = res.distance;
                  current.ongkir = hitungOngkir(res.distance);
                  current.isRoad = true;
                  current.roadProvider = res.provider || 'osrm';
                  current.allowed = withinService(res.distance);
                  updateInfo(true);
                  refreshDestIcon();
                  scheduleReverseGeocode(lat, lng);
                  stopLocLoading();
                })
                .catch(function(err){
				  // TIDAK ADA fallback ke haversine
				  current.lat = lat; 
				  current.lng = lng;
				  current.dist = 0;
				  current.ongkir = 0;
				  current.isRoad = false;
				  current.roadProvider = null;
				  current.allowed = false;

				  updateInfo();        // render status gagal rute
				  refreshDestIcon();   // ikon merah

				  if (window.Swal && Swal.fire){
				    Swal.fire({
				      icon: 'error',
				      title: 'Rute jalan tidak ditemukan',
				      html: 'Coba geser pin sedikit, pastikan ada jalan mobil/motor, lalu coba lagi.',
				      timer: 4000
				    });
				  } else {
				    alert('Rute jalan tidak ditemukan. Coba geser pin lalu ulangi.');
				  }
				    stopLocLoading();
				});

            }, 250);
          }

          function setDest(lat, lng){
            if (!destMarker){
              var _icons = getMapIcons();
              destMarker = L.marker([lat, lng], {
                draggable:true,
                icon: _icons ? _icons.destOk : undefined
              }).addTo(m);
              destMarker.on('drag',  function(e){ var p=e.target.getLatLng(); calcRoute(p.lat, p.lng); });
              destMarker.on('dragend',function(e){ var p=e.target.getLatLng(); calcRoute(p.lat, p.lng); });
            } else {
              destMarker.setLatLng([lat, lng]);
            }
            calcRoute(lat, lng);
          }

          m.on('click', function(e){ setDest(e.latlng.lat, e.latlng.lng); });

          if (btnMyLoc && navigator.geolocation){
            btnMyLoc.addEventListener('click', function(){
              startLocLoading();

              navigator.geolocation.getCurrentPosition(
                function (pos) {
                  // ==== SUKSES ====
                  var lat = pos.coords.latitude;
                  var lng = pos.coords.longitude;

                  // taruh pin tujuan + hitung ongkir dll
                  setDest(lat, lng);

                  // fokuskan peta ke posisi user
                  try {
                    m.flyTo([lat, lng], 16, { duration: 0.5 });
                  } catch(e){}

                  // hentikan loading di tombol
                  stopLocLoading();

                  // kasih feedback manis ke user (boleh kamu hapus nanti)
                  if (window.Swal && Swal.fire) {
                    Swal.fire({
                      icon: 'success',
                      title: 'Lokasi OK',
                      html:
                        'lat=' + lat.toFixed(6) +
                        '<br>lng=' + lng.toFixed(6),
                      timer: 2000,
                      showConfirmButton: false
                    });
                  }
                },
                function (err) {
                  // ==== GAGAL ====
                  stopLocLoading();

                  const geoErr = {
                    1: 'Izin lokasi ditolak',
                    2: 'Lokasi tidak bisa didapat',
                    3: 'Timeout GPS'
                  };
                  const detail = geoErr[err.code] || (err.message || ('error ' + err.code));

                  // note tambahan kalau bukan https / bukan localhost
                  const httpsNote = (!window.isSecureContext && location.hostname !== 'localhost')
                    ? '<br><small class="text-muted">Butuh koneksi <b>HTTPS</b> atau <code>localhost</code>.</small>'
                    : '';

                  if (window.Swal && Swal.fire) {
                    Swal.fire({
                      icon: 'warning',
                      title: 'Lokasi nggak keambil 😅',
                      html: detail + httpsNote,
                      confirmButtonText: 'Sip'
                    });
                  } else {
                    alert('Ups, lokasi nggak keambil: ' + detail);
                  }
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
              );
            });

          }

          updateInfo();
        });
      };
    })();

    // Pindahkan modal ke body
    (function moveModalToBody(){
      var el = document.getElementById('modalOngkirMap');
      if (!el) return;
      if (el.parentNode !== document.body){
        try { document.body.appendChild(el); } catch(e){}
      }
    })();

    // Load fragmen peta saat modal shown
    (function(){
      var btn = document.getElementById('btnMapOngkir');
      var modalEl = document.getElementById('modalOngkirMap');
      if (!btn || !modalEl) return;

      btn.addEventListener('click', function(){
        var loadOnce = function(){
          var body = document.getElementById('modalOngkirBody');
          if (!window.__leafletMapInModal && body && body.children.length === 0){
            window.__loadOngkirMapFragment();
          }
          if (window.jQuery && jQuery.fn && jQuery.fn.modal) {
            jQuery(modalEl).off('shown.bs.modal', loadOnce);
          } else {
            modalEl.removeEventListener('shown.bs.modal', loadOnce);
            modalEl.removeEventListener('shown', loadOnce);
          }
        };

        if (window.jQuery && jQuery.fn && jQuery.fn.modal){
          jQuery(modalEl).one('shown.bs.modal', loadOnce);
        } else {
          modalEl.addEventListener('shown.bs.modal', loadOnce, {once:true});
          modalEl.addEventListener('shown', loadOnce, {once:true});
        }

        openModal(modalEl);
      });
    })();

    // Non-blur backdrop & reflow Leaflet
    (function bindModalEvents(){
      var el = document.getElementById('modalOngkirMap');
      if (!el) return;

      if (window.jQuery && jQuery.fn && jQuery.fn.modal){
        var $el = jQuery(el);
        $el.on('shown.bs.modal', function(){
          document.body.classList.add('noblur-backdrop');
          var bd = document.querySelectorAll('.modal-backdrop');
          bd = bd[bd.length - 1];
          if (bd){
            bd.classList.add('ongkir-backdrop');
            Object.assign(bd.style, {
              backdropFilter:'none', WebkitBackdropFilter:'none', filter:'none',
              background:'rgba(0,0,0,.55)', zIndex:220990, position:'fixed'
            });
          }
          setTimeout(function(){
            if (window.__leafletMapInModal && window.__leafletMapInModal.invalidateSize) {
              window.__leafletMapInModal.invalidateSize();
            }
          }, 120);
        });
        $el.on('hidden.bs.modal', function(){
          document.body.classList.remove('noblur-backdrop');
          document.querySelectorAll('.modal-backdrop.ongkir-backdrop').forEach(function(bd){
            bd.classList.remove('ongkir-backdrop');
            bd.removeAttribute('style');
          });
        });
        return;
      }

      el.addEventListener('shown.bs.modal', function(){
        document.body.classList.add('noblur-backdrop');
        var backs = document.querySelectorAll('.modal-backdrop');
        var bd = backs[backs.length - 1];
        if (bd){
          bd.classList.add('ongkir-backdrop');
          Object.assign(bd.style, {
            backdropFilter:'none', WebkitBackdropFilter:'none', filter:'none',
            background:'rgba(0,0,0,.55)', zIndex:220990, position:'fixed'
          });
        }
        setTimeout(function(){
          if (window.__leafletMapInModal && window.__leafletMapInModal.invalidateSize) {
            window.__leafletMapInModal.invalidateSize();
          }
        }, 120);
      });
      el.addEventListener('hidden.bs.modal', function(){
        document.body.classList.remove('noblur-backdrop');
        document.querySelectorAll('.modal-backdrop.ongkir-backdrop').forEach(function(bd){
          bd.classList.remove('ongkir-backdrop');
          bd.removeAttribute('style');
        });
      });
    })();

  })();
  </script>

  <script>
  (function(){
    if (window.__ONGKIR_MAP_PATCH_V2__) return;
    window.__ONGKIR_MAP_PATCH_V2__ = true;

    var MODAL_ID = 'modalOngkirMap';
    var BODY_ID  = 'modalOngkirBody';

    function queueInvalidate(){
      var m = window.__leafletMapInModal;
      if (!m || !m.invalidateSize) return;
      try { m.invalidateSize(); } catch(e){}
      try { requestAnimationFrame(function(){ try{ m.invalidateSize(); }catch(e){} }); } catch(e){}
      setTimeout(function(){ try{ m.invalidateSize(); }catch(e){} }, 120);
      setTimeout(function(){ try{ m.invalidateSize(); }catch(e){} }, 360);
      setTimeout(function(){ try{ m.invalidateSize(); }catch(e){} }, 800);
    }

    function bindModalEvents(){
      var el = document.getElementById(MODAL_ID);
      if (!el) return;

      if (window.jQuery && jQuery.fn && jQuery.fn.modal){
        var $el = jQuery(el);
        $el.on('shown.bs.modal', function(){
          queueInvalidate();
          var dlg = el.querySelector('.modal-dialog');
          if (dlg){ dlg.addEventListener('transitionend', queueInvalidate, {once:true}); }
          setTimeout(queueInvalidate, 1000);
        });
        $el.on('hidden.bs.modal', function(){
          try { if (window.__leafletMapInModal && window.__leafletMapInModal.remove) {
            window.__leafletMapInModal.remove();
          }} catch(e){}
          window.__leafletMapInModal = null;
          var body = document.getElementById(BODY_ID);
          if (body) body.innerHTML = '';
        });
        return;
      }

      el.addEventListener('shown.bs.modal', function(){
        queueInvalidate();
        var dlg = el.querySelector('.modal-dialog');
        if (dlg){ dlg.addEventListener('transitionend', queueInvalidate, {once:true}); }
        setTimeout(queueInvalidate, 1000);
      });
      el.addEventListener('hidden.bs.modal', function(){
        try { if (window.__leafletMapInModal && window.__leafletMapInModal.remove) {
          window.__leafletMapInModal.remove();
        }} catch(e){}
        window.__leafletMapInModal = null;
        var body = document.getElementById(BODY_ID);
        if (body) body.innerHTML = '';
      });
    }

    window.__ongkirMap_afterAjaxLoaded = function(){ queueInvalidate(); };

    var css = document.createElement('style');
    css.innerHTML =
      '#'+MODAL_ID+' #mapInModal{min-height:320px;height:380px;width:100%;}' +
      '#'+MODAL_ID+' .modal-body{padding:0;}';
    document.head.appendChild(css);

    bindModalEvents();
  })();
  </script>

  <script>
  (function($){
    var $ta = $('#alamatKirim');
    var $count = $('#alamatCount');
    var $hint = $('#ongkirHint');
    var radius = <?= json_encode((int)($rec->max_radius_m ?? 0)) ?>;

    var STORE_LAT = <?= $store_lat ?>;
    var STORE_LNG = <?= $store_lng ?>;
    var MAX_RADIUS_M = radius;
    var MAX_RADIUS_KM = MAX_RADIUS_M / 1000;

    function haversineM(lat1, lon1, lat2, lon2){
      var R = 6371000, toRad = d=>d*Math.PI/180;
      var dLat = toRad(lat2-lat1), dLon = toRad(lon2-lon1);
      var a = Math.sin(dLat/2)**2 + Math.cos(toRad(lat1))*Math.cos(toRad(lat2))*Math.sin(dLon/2)**2;
      return 2*R*Math.asin(Math.sqrt(a));
    }

    function syncCount(){
      var val = $ta.val();
      $count.text(val.length + '/300');
      $ta[0].style.height = 'auto';
      $ta[0].style.height = ($ta[0].scrollHeight + 2) + 'px';
    }
    $ta.on('input', syncCount);
    syncCount();

    $('#btnUseMyLocation').on('click', function(){
  var self = this;

  // fungsi yang benar-benar minta GPS
  function ambilLokasiSetelahIzin() {
    if (!navigator.geolocation){
      $hint.addClass('text-danger')
           .text('Perangkat tidak mendukung geolokasi / GPS.');
      return;
    }

    if (window.setBtnLoading) setBtnLoading(self, true);
    $hint.removeClass('text-danger')
         .text('Meminta lokasi GPS…');

    navigator.geolocation.getCurrentPosition(function(pos){
      // ====== SUKSES DAPAT KOORDINAT ======
      var lat = +pos.coords.latitude.toFixed(6);
      var lng = +pos.coords.longitude.toFixed(6);

      var approx = haversineM(STORE_LAT, STORE_LNG, lat, lng);
      if (approx > MAX_RADIUS_M){
        if (window.setBtnLoading) setBtnLoading(self, false);
        $hint.addClass('text-danger')
             .text('Maaf, lokasi Anda berada di luar radius layanan (maks '+MAX_RADIUS_KM+' km dari toko).');
        return;
      }

      // simpan ke hidden input
      if ($('#dest_lat').length) $('#dest_lat').val(lat);
      if ($('#dest_lng').length) $('#dest_lng').val(lng);

      // auto tempel koordinat ke textarea alamat (Shareloc)
      var share = ' (Shareloc: ' + lat + ', ' + lng + ')';
      if ($ta.val().indexOf('Shareloc:') === -1) {
        $ta.val(($ta.val().trim() + share).trim());
        if ($count && $count.length) $count.text($ta.val().length + '/300');
      }

      if (window.setBtnLoading) setBtnLoading(self, false);
      $hint.removeClass('text-danger')
           .text('Koordinat tersimpan ✅. Silakan lanjut atau buka peta untuk cek rute.');

    }, function(err){
      // ====== GAGAL AMBIL KOORDINAT ======
      var geoErr = {
        1:'Izin lokasi ditolak',
        2:'Lokasi tidak ditemukan',
        3:'Timeout saat ambil lokasi'
      };
      var msg = geoErr[err.code] || ('Gagal ambil lokasi ('+err.code+')');

      if (window.setBtnLoading) setBtnLoading(self, false);

      // tambahan info HTTPS (Chrome tidak kasih geolocation kalau bukan https / localhost)
      var httpsNote = (!window.isSecureContext && location.hostname !== 'localhost')
        ? ' Situs harus diakses lewat HTTPS agar lokasi bisa diambil.'
        : '';

      $hint.addClass('text-danger')
           .text(msg + '. ' + 'Aktifkan GPS & izinkan lokasi.' + httpsNote);
    }, {
      enableHighAccuracy: true,
      timeout: 10000,
      maximumAge: 0
    });
  }

  // sebelum benar-benar minta lokasi, jelaskan dulu ke user
  if (window.Swal && Swal.fire){
    Swal.fire({
      title: 'Izinkan lokasi?',
      html: `
        Kami pakai lokasimu untuk:
        <ul style="text-align:left;margin:0;padding-left:1.2em;font-size:.9em;line-height:1.4em">
          <li>Hitung jarak ke Ausi Billiard & Café</li>
          <li>Estimasi ongkir antar</li>
          <li>Cek apakah alamat kamu masih dalam jangkauan</li>
        </ul>
        Lokasi tidak dipakai untuk iklan.
      `,
      showCancelButton: true,
      confirmButtonText: 'Izinkan',
      cancelButtonText: 'Batal'
    }).then(function(res){
      if (res.isConfirmed){
        ambilLokasiSetelahIzin();
      } else {
        // user batal → jangan panggil GPS
        $hint.addClass('text-danger')
             .text('Lokasi tidak diambil. Kamu bisa tulis alamat manual.');
      }
    });
  } else {
    // fallback kalau Swal tidak ada
    var ok = confirm(
      'Kami pakai lokasi buat hitung jarak & ongkir kurir, tidak untuk iklan.\n' +
      'Izinkan ambil lokasi sekarang?'
    );
    if (ok) {
      ambilLokasiSetelahIzin();
    } else {
      $hint.addClass('text-danger')
           .text('Lokasi tidak diambil. Kamu bisa tulis alamat manual.');
    }
  }
});

  })(jQuery);

  // Muat ulang fragmen peta (AJAX) + inisialisasi ulang Leaflet
  window.__loadOngkirMapFragment = function(){
    var body   = document.getElementById('modalOngkirBody');
    var loader = document.getElementById('modalOngkirLoader');

    try { if (window.__leafletMapInModal?.remove) window.__leafletMapInModal.remove(); } catch(e){}
    window.__leafletMapInModal = null;

    if (body)   body.innerHTML = '';
    if (loader) loader.style.display = 'block';

    var url   = window.__ONGKIR_LOAD_URL || "<?= site_url('produk/load_map') ?>";
    var data  = new URLSearchParams({
      store_lat: "<?= $store_lat ?>",
      store_lng: "<?= $store_lng ?>",
      base_km:   "<?= (int)$base_km ?>",
      base_fee:  "<?= (int)$base_fee ?>",
      per_km:    "<?= (int)$per_km ?>",
      max_radius_m: "<?= (int)$max_radius_m ?>",
      _:         String(Date.now())
    });

    var xhr = new XMLHttpRequest();
    xhr.open('POST', url, true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');

    xhr.onreadystatechange = function(){
      if (xhr.readyState !== 4) return;

      var fail = function(msg){
        if (loader) loader.innerHTML =
          '<div class="p-4 text-danger">'+ msg +
          ' <button type="button" class="btn btn-link p-0" id="retryLoadLink">Coba lagi</button></div>';
        setTimeout(function(){
          document.getElementById('retryLoadLink')?.addEventListener('click', function(){
            window.__loadOngkirMapFragment();
          });
        }, 0);
        document.dispatchEvent(new CustomEvent('ongkirMap:error', {detail:{status:xhr.status}}));
      };

      if (xhr.status >= 200 && xhr.status < 300){
        var text = xhr.responseText || '';
        if (text.indexOf('The URI you submitted has disallowed characters') !== -1){
          fail('Server menolak URI (disallowed characters).'); return;
        }
        if (loader) loader.style.display = 'none';
        if (body)   body.innerHTML = text;

        try { initOngkirMapFromHtml(); } catch(e){ console.error(e); }
        if (window.__ongkirMap_afterAjaxLoaded) window.__ongkirMap_afterAjaxLoaded();
        document.dispatchEvent(new CustomEvent('ongkirMap:loaded'));
      } else {
        fail('Gagal memuat peta.');
      }
    };
    xhr.send(data.toString());
  };

  (function(){
    var btn  = document.getElementById('btnReloadMap');
    var spin = document.getElementById('reloadSpin');
    if (!btn) return;

    function setBusy(b){
      if (!btn) return;
      btn.disabled = !!b;
      if (spin) spin.classList.toggle('d-none', !b);
    }

    btn.addEventListener('click', function(){
      try {
        if (window.__leafletMapInModal && window.__leafletMapInModal.invalidateSize){
          window.__leafletMapInModal.invalidateSize();
          setTimeout(function(){
            var el = document.getElementById('mapInModal');
            if (!el || el.clientHeight < 50) {
              setBusy(true);
              window.__loadOngkirMapFragment();
            }
          }, 200);
          return;
        }
      } catch(e){}
      setBusy(true);
      window.__loadOngkirMapFragment();
    });

    document.addEventListener('ongkirMap:loaded', function(){ setBusy(false); });
    document.addEventListener('ongkirMap:error',  function(){ setBusy(false); });
  })();
  function getDrivingDistanceMulti(lat1, lng1, lat2, lng2){
  const u = "<?= site_url('produk/route_multi') ?>"
          + `?lat1=${encodeURIComponent(lat1)}&lng1=${encodeURIComponent(lng1)}`
          + `&lat2=${encodeURIComponent(lat2)}&lng2=${encodeURIComponent(lng2)}`;

  const ctrl = new AbortController();
  const t = setTimeout(()=>{ try{ctrl.abort();}catch(_){} }, 12000);

  return fetch(u, {
    signal: ctrl.signal,
    credentials: 'same-origin',   // aman karena same-origin
    cache: 'no-store',
    headers: { 'Accept':'application/json' }
  })
  .then(r => { if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
  .then(j => {
    if (!j.ok || !j.distance) throw new Error('No route');
    return { distance: j.distance|0, provider: j.provider || 'osrm' };
  })
  .finally(()=>clearTimeout(t));
}

  // function getDrivingDistanceMulti(lat1, lng1, lat2, lng2){
  //   const providers = [
  //     { name: 'osrm',  url: `https://router.project-osrm.org/route/v1/driving/${lng1},${lat1};${lng2},${lat2}?overview=false&alternatives=false&steps=false` },
  //     { name: 'osmde', url: `https://routing.openstreetmap.de/routed-car/route/v1/driving/${lng1},${lat1};${lng2},${lat2}?overview=false&alternatives=false&steps=false` }
  //   ];
  //   let lastErr = null;
  //   const PER_ATTEMPT_TIMEOUT = 12000;

  //   return providers.reduce((chain, p) => {
  //     return chain.catch(() => {
  //       const ctrl = new AbortController();
  //       const timer = setTimeout(() => { try{ctrl.abort();}catch(e){} }, PER_ATTEMPT_TIMEOUT);

  //       return fetch(p.url, {
  //       	signal: ctrl.signal, 
  //       	mode:'cors',
  //       	credentials: 'omit', 
  //       	cache: 'no-store'
  //       })
  //         .then(r => { if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
  //         .then(j => {
  //           clearTimeout(timer);
  //           if (j && j.code === 'Ok' && j.routes && j.routes[0] && typeof j.routes[0].distance === 'number'){
  //             const dist = Math.max(0, j.routes[0].distance|0);
  //             if (dist > 0){
  //               return { distance: dist, provider: p.name };
  //             }
  //           }
  //           throw new Error('No route');
  //         })
  //         .catch(err => { clearTimeout(timer); lastErr = err; throw err; });
  //     });
  //   }, Promise.reject(new Error('init')));
  // }
  </script>

  <script>
  (function(){
    window.setBtnLoading = function(btn, busy){
      if (!btn) return;
      var spin = btn.querySelector('.btn-spin');
      if (!spin){
        spin = document.createElement('span');
        spin.className = 'spinner-border spinner-border-sm btn-spin d-none';
        spin.setAttribute('role','status');
        spin.setAttribute('aria-hidden','true');
        spin.style.marginRight = '.5rem';
        btn.insertBefore(spin, btn.firstChild);
      }
      btn.setAttribute('aria-busy', busy ? 'true' : 'false');
      btn.disabled = !!busy;
      spin.classList.toggle('d-none', !busy);
    };
  })();
  </script>


