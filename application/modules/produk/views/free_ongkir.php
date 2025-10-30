<!-- =========================
       CABANG: GRATIS ONGKIR (SELECT-ONLY MAP)
       ========================= -->
<?php
$rec = $this->fm->web_me();
    $store_lat = $rec->store_lat;
    $store_lng = $rec->store_lng;
    $base_km   = $rec->base_km;
    $base_fee  = $rec->base_fee;
    $per_km    = $rec->per_km;
    $max_radius_m = $rec->max_radius_m;
    $batas_free = (int)($rec->batas_free_ongkir ?? 0);
?>
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

    <div class="mt-2">
      <div class="alert alert-success d-flex align-items-start mb-2" role="alert">
        <i class="mdi mdi-truck-fast mr-2" aria-hidden="true"></i>
        <div>
          <strong>Gratis Ongkir!</strong><br>
          Belanja Anda sudah mencapai minimal
          Rp<?= number_format($batas_free, 0, ',', '.') ?>.<br>
          Silakan pilih titik pengantaran (tanpa estimasi ongkir).
        </div>
      </div>
    </div>

    <div class="d-flex flex-wrap align-items-center mt-2">
      <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center mr-1" id="btnMapOngkir">
        <i class="mdi mdi-map-marker-outline mr-1" aria-hidden="true"></i> Pilih Lokasi
      </button>
      <button type="button" class="btn btn-warning btn-sm d-inline-flex align-items-center" id="btnUseMyLocation">
        <i class="mdi mdi-crosshairs-gps mr-1" aria-hidden="true"></i> Gunakan Lokasi Saya
      </button>
    </div>
  </div>

  <small id="ongkirHint" class="text-dark mb-2"></small>

  <!-- Hidden input: ongkir dipaksa 0, tetap kirim koordinat -->
  <input type="hidden" name="ongkir" id="ongkirInput" value="0">
  <input type="hidden" name="free_ongkir" value="1">
  <input type="hidden" name="dest_lat" id="dest_lat">
  <input type="hidden" name="dest_lng" id="dest_lng">
  <input type="hidden" name="distance_m" id="distance_m">
  <input type="hidden" name="ongkir_token" id="ongkir_token">

  <!-- ====== MODAL SELECT-ONLY MAP (KHUSUS GRATIS ONGKIR) ====== -->
  <div class="modal fade" id="modalSelectOnlyMap"
       tabindex="-1" role="dialog" aria-hidden="true"
       data-backdrop="static" data-keyboard="false"
       data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <div class="d-flex align-items-center">
            <i class="mdi mdi-map-outline mr-2"></i>
            <div class="font-weight-semibold">Pilih Titik Pengantaran</div>
          </div>
          <button type="button" class="close btn-close"
                  data-dismiss="modal" data-bs-dismiss="modal"
                  aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body p-0">
          <div id="selectOnlyLoader" class="p-4 text-center">
            <div class="spinner-border" role="status" aria-hidden="true"></div>
            <div class="mt-2">Menyiapkan peta…</div>
          </div>
          <div id="selectOnlyBody" class="d-none">
            <div id="selectOnlyMap" style="min-height: 360px; height: 420px; width:100%;"></div>
            <div id="selectOnlyInfo" class="p-2 small"></div>
            <div class="p-2 d-flex justify-content-between">
              <button type="button" class="btn btn-warning btn-sm" id="btnMyLocSelect">
                <i class="mdi mdi-crosshairs-gps mr-1"></i> Gunakan Lokasi Saya
              </button>
              <button type="button" class="btn btn-primary btn-sm" id="btnUseSelectLoc" disabled>
                <span class="lbl">Pilih Lokasi Ini</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ==== Anti-blur & z-index fix (sama seperti cabang ongkir) ==== -->
  <style>
    #modalSelectOnlyMap { z-index: 221000 !important; }
    .modal-backdrop.ongkir-backdrop{
      z-index:220990 !important; position:fixed !important; background:rgba(0,0,0,.55) !important;
      backdrop-filter:none !important; -webkit-backdrop-filter:none !important; filter:none !important;
    }
    body.noblur-backdrop .modal-backdrop{ backdrop-filter:none !important; -webkit-backdrop-filter:none !important; filter:none !important; }
    body.noblur-backdrop .content, body.noblur-backdrop .page-wrapper, body.noblur-backdrop .wrapper, body.noblur-backdrop main, body.noblur-backdrop #app{
      filter:none !important; -webkit-filter:none !important; transform:none !important;
    }
  </style>

  <script>
  (function(){
    /* ====== Ensure SweetAlert2 (CDN) jika belum ada ====== */
    // (function ensureSwal(){
    //   if (window.Swal) return;
    //   var s=document.createElement('script');
    //   s.src='https://cdn.jsdelivr.net/npm/sweetalert2@11'; s.defer=true;
    //   document.head.appendChild(s);
    // })();

    // ===== Helper modal (BS4/BS5) =====
    function openSelectModal(el){
      if (window.jQuery && jQuery.fn && jQuery.fn.modal) jQuery(el).modal('show');
      else if (window.bootstrap && bootstrap.Modal) bootstrap.Modal.getOrCreateInstance(el).show();
      else (window.Swal?Swal.fire({icon:'error',title:'Modal tidak tersedia',text:'Bootstrap modal plugin tidak ditemukan.'}):alert('Bootstrap modal plugin tidak ditemukan.'));
    }
    function closeSelectModal(el){
      if (window.jQuery && jQuery.fn && jQuery.fn.modal) jQuery(el).modal('hide');
      else if (window.bootstrap && bootstrap.Modal) bootstrap.Modal.getOrCreateInstance(el).hide();
    }

    // ===== Update CSRF dari respons lock_ongkir() =====
    function updateCsrf(csrf){
      if (!csrf || !csrf.name || !csrf.hash) return;
      var mn = document.querySelector('meta[name="csrf-name"]');
      var mh = document.querySelector('meta[name="csrf-hash"]');
      if (!mn){ mn = document.createElement('meta'); mn.setAttribute('name','csrf-name'); document.head.appendChild(mn); }
      if (!mh){ mh = document.createElement('meta'); mh.setAttribute('name','csrf-hash'); document.head.appendChild(mh); }
      mn.setAttribute('content', csrf.name);
      mh.setAttribute('content', csrf.hash);
      // Kalau ada hidden input CSRF di form, ikut perbarui
      var hid = document.querySelector('input[name="'+csrf.name+'"]');
      if (hid) hid.value = csrf.hash;
    }

    // ===== Loader Leaflet khusus (tidak ganggu ensureLeaflet lain) =====
    function ensureLeafletSelectOnly(cb){
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

    // ===== Haversine (meter) =====
    function haversineM(lat1, lon1, lat2, lon2){
      var R = 6371000, toRad = d=>d*Math.PI/180;
      var dLat = toRad(lat2-lat1), dLon = toRad(lon2-lon1);
      var a = Math.sin(dLat/2)**2 + Math.cos(toRad(lat1))*Math.cos(toRad(lat2))*Math.sin(dLon/2)**2;
      return 2*R*Math.asin(Math.sqrt(a));
    }

    // ===== Init peta select-only =====
    function initSelectOnlyMap(){
      var loader = document.getElementById('selectOnlyLoader');
      var body   = document.getElementById('selectOnlyBody');
      var mapEl  = document.getElementById('selectOnlyMap');
      if (!mapEl) return;

      ensureLeafletSelectOnly(function(){
        try { if (window.__leafletSelectOnly && window.__leafletSelectOnly.remove) window.__leafletSelectOnly.remove(); } catch(e){}
        var storeLat = <?= json_encode((float)$store_lat) ?>;
        var storeLng = <?= json_encode((float)$store_lng) ?>;
        var MAX_RADIUS_M  = <?= json_encode((int)$max_radius_m) ?>;
        var MAX_RADIUS_KM = MAX_RADIUS_M/1000;

        var m = L.map(mapEl).setView([storeLat, storeLng], 14);
        window.__leafletSelectOnly = m;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom:19}).addTo(m);

        var storeIcon = L.icon({ iconUrl: '<?= base_url("assets/images/mark.webp") ?>',  iconSize: [50, 50], iconAnchor: [16, 28], popupAnchor: [0, -24] });
        var destIcon  = L.icon({ iconUrl: '<?= base_url("assets/images/maker2.webp") ?>', iconSize: [28, 40], iconAnchor: [14, 39], popupAnchor: [0, -32] });

        L.marker([storeLat, storeLng], {icon:storeIcon}).addTo(m).bindPopup('Ausi Billiard & Cafe');
        L.circle([storeLat, storeLng], { radius: MAX_RADIUS_M, fill:false, weight:2, dashArray:'6,6', color:'#0d6efd' }).addTo(m);

        var destMarker = null;
        var infoEl = document.getElementById('selectOnlyInfo');
        var btnUse = document.getElementById('btnUseSelectLoc');
        var btnMy  = document.getElementById('btnMyLocSelect');
        var lastRadiusWarnAt = 0;

        function showSwal(opts, fallbackMsg){
          if (window.Swal) Swal.fire(opts);
          else if (fallbackMsg) alert(fallbackMsg);
        }

        function updateInfo(lat, lng, distM, allowed){
          var km = (distM/1000).toFixed(2);
          infoEl.innerHTML =
            '<div>Tujuan: <b>'+ lat.toFixed(6) + ', ' + lng.toFixed(6) +
            '</b></div><div>Jarak: <b>'+ km +' km</b>' +
            (allowed ? '' : ' <span class="text-danger">(di luar radius '+MAX_RADIUS_KM+' km)</span>') +
            '</div>';
          btnUse.disabled = !allowed;

          if (!allowed){
            var now = Date.now();
            if (now - lastRadiusWarnAt > 4000){
              lastRadiusWarnAt = now;
              showSwal({
                icon: 'warning',
                title: 'Di luar jangkauan',
                html: 'Jarak kamu <b>'+ km +' km</b> (maks '+ MAX_RADIUS_KM +' km).<br>Pilih titik lebih dekat atau pilih <b>Ambil di Cafe</b> ya.',
                confirmButtonText: 'Mengerti'
              }, 'Di luar jangkauan — jarak '+km+' km (maks '+MAX_RADIUS_KM+' km).');
            }
          }
        }

        function setDest(lat, lng){
          if (!destMarker){
            destMarker = L.marker([lat, lng], {draggable:true, icon:destIcon}).addTo(m);
            destMarker.on('dragend', function(e){
              var p = e.target.getLatLng();
              var d = haversineM(storeLat, storeLng, p.lat, p.lng);
              updateInfo(p.lat, p.lng, d, d <= MAX_RADIUS_M + 1e-6);
            });
          } else {
            destMarker.setLatLng([lat,lng]);
          }
          var d = haversineM(storeLat, storeLng, lat, lng);
          updateInfo(lat, lng, d, d <= MAX_RADIUS_M + 1e-6);
        }

        m.on('click', function(e){ setDest(e.latlng.lat, e.latlng.lng); });

        if (btnMy && navigator.geolocation){
          btnMy.addEventListener('click', function(){
            navigator.geolocation.getCurrentPosition(function(pos){
              var lat = pos.coords.latitude, lng = pos.coords.longitude;
              setDest(lat, lng);
              m.flyTo([lat, lng], 16, {duration:0.4});
            }, function(err){
              var geoErr = {1:'Izin lokasi ditolak', 2:'Lokasi tidak ditemukan', 3:'Timeout'};
              var msg = geoErr[err && err.code] || (err && err.message) || 'Gagal mengambil lokasi.';
              showSwal({
                icon:'warning',
                title: 'Lokasi nggak keambil 😅',
                html: '<div class="text-left">Kendala: <b>'+msg+
                      '</b><ul class="mt-2 mb-0"><li>Nyalakan GPS & beri izin lokasi.</li><li>Pastikan internet stabil.</li><li>Atau pilih titik manual di peta.</li></ul></div>',
                confirmButtonText:'Oke'
              }, 'Gagal mengambil lokasi: '+msg);
            }, {enableHighAccuracy:true, timeout:12000, maximumAge:0});
          });
        }

        if (btnUse){
  btnUse.onclick = function(){
    if (!destMarker){
      return showSwal(
        {icon:'info', title:'Pilih titik dulu', text:'Klik peta untuk memilih titik pengantaran terlebih dahulu.'},
        'Pilih titik di peta terlebih dahulu.'
      );
    }
    var p = destMarker.getLatLng();
    var d = haversineM(storeLat, storeLng, p.lat, p.lng);

    // Validasi radius
    var allowed = d <= MAX_RADIUS_M + 1e-6;
    if (!allowed){
      var km = (d/1000).toFixed(2);
      return showSwal(
        {icon:'warning', title:'Di luar jangkauan', html:'Jarak kamu <b>'+ km +' km</b> (maks '+ (MAX_RADIUS_M/1000) +' km).'},
        'Di luar jangkauan'
      );
    }

    // Isi hidden input (koordinat + jarak), ongkir biarkan 0 (gratis)
    document.getElementById('dest_lat').value    = +p.lat.toFixed(6);
    document.getElementById('dest_lng').value    = +p.lng.toFixed(6);
    document.getElementById('distance_m').value  = Math.round(d);
    var tok = document.getElementById('ongkir_token'); if (tok) tok.value = ''; // tidak pakai token di gratis ongkir

    // Tampilkan hint sederhana (koordinat & jarak — tanpa ongkir)
    var hint = document.getElementById('ongkirHint');
    if (hint){
      var km = (d/1000).toFixed(2);
      hint.innerHTML =
        '<div class="p-2">'
        + '<div><b>'+ (+p.lat).toFixed(6)+', '+(+p.lng).toFixed(6) +'</b></div>'
        + '<div><span class="text-muted">Jarak:</span> <b>'+ km +' km</b></div>'
        + '<div class="text-success">Lokasi tersimpan. Gratis ongkir.</div>'
        + '</div>';
    }

    closeSelectModal(document.getElementById('modalSelectOnlyMap'));
  };
}


        // tampilkan body
        if (loader) loader.classList.add('d-none');
        if (body)   body.classList.remove('d-none');
        setTimeout(function(){ try{ m.invalidateSize(); }catch(e){} }, 120);
      });
    }

    // ===== Bind tombol luar ke modal select-only =====
    (function(){
      var btn = document.getElementById('btnMapOngkir');
      var modalEl = document.getElementById('modalSelectOnlyMap');
      if (!btn || !modalEl) return;

      function onShown(){
        initSelectOnlyMap();
        if (window.jQuery && jQuery.fn && jQuery.fn.modal){
          jQuery(modalEl).off('shown.bs.modal', onShown);
        } else {
          modalEl.removeEventListener('shown.bs.modal', onShown);
          modalEl.removeEventListener('shown', onShown);
        }
      }

      btn.addEventListener('click', function(){
        if (window.jQuery && jQuery.fn && jQuery.fn.modal){
          jQuery(modalEl).one('shown.bs.modal', onShown);
        } else {
          modalEl.addEventListener('shown-bs.modal', onShown, {once:true});
          modalEl.addEventListener('shown', onShown, {once:true});
        }
        openSelectModal(modalEl);
      });
    })();

    // ===== Anti-blur + pindah modal ke body =====
    (function(){
      var el = document.getElementById('modalSelectOnlyMap');
      if (!el) return;

      if (el.parentNode !== document.body){
        try { document.body.appendChild(el); } catch(e){}
      }

      function applyNoBlur(){
        document.body.classList.add('noblur-backdrop');
        var backs = document.querySelectorAll('.modal-backdrop');
        var bd = backs[backs.length - 1];
        if (bd){
          bd.classList.add('ongkir-backdrop');
          Object.assign(bd.style, { background:'rgba(0,0,0,.55)', zIndex:220990, position:'fixed' });
        }
      }
      function clearNoBlur(){
        document.body.classList.remove('noblur-backdrop');
        document.querySelectorAll('.modal-backdrop.ongkir-backdrop').forEach(function(bd){
          bd.classList.remove('ongkir-backdrop'); bd.removeAttribute('style');
        });
      }

      if (window.jQuery && jQuery.fn && jQuery.fn.modal){
        var $el = jQuery(el);
        $el.on('shown.bs.modal', applyNoBlur);
        $el.on('hidden.bs.modal', clearNoBlur);
      } else {
        el.addEventListener('shown.bs.modal', applyNoBlur);
        el.addEventListener('hidden.bs.modal', clearNoBlur);
        el.addEventListener('shown', applyNoBlur);
        el.addEventListener('hidden', clearNoBlur);
      }
    })();
  })();
  // ===== Handler tombol luar: "Gunakan Lokasi Saya" (gratis ongkir → simpan koordinat saja) =====
(function(){
  var btn = document.getElementById('btnUseMyLocationss');
  if (!btn || !navigator.geolocation) return;

  // Konstanta dari PHP untuk cek radius di luar modal
  var STORE_LAT = <?= json_encode((float)$store_lat) ?>;
  var STORE_LNG = <?= json_encode((float)$store_lng) ?>;
  var MAX_RADIUS_M = <?= json_encode((int)$max_radius_m) ?>;

  btn.addEventListener('click', function(){
    var hint = document.getElementById('ongkirHint');
    if (hint) { hint.classList.remove('text-danger'); hint.textContent = 'Mencari lokasi Anda...'; }

    navigator.geolocation.getCurrentPosition(function(pos){
      var lat = +pos.coords.latitude.toFixed(6);
      var lng = +pos.coords.longitude.toFixed(6);
      var d = haversineM(STORE_LAT, STORE_LNG, lat, lng);

      if (d > MAX_RADIUS_M + 1e-6){
        if (hint){
          hint.classList.add('text-danger');
          hint.textContent = 'Lokasi di luar jangkauan layanan (maks '+ (MAX_RADIUS_M/1000) +' km).';
        } else {
          alert('Lokasi di luar jangkauan.');
        }
        return;
      }

      // Simpan koordinat + jarak, ongkir tetap 0 (gratis)
      document.getElementById('dest_lat').value    = lat;
      document.getElementById('dest_lng').value    = lng;
      document.getElementById('distance_m').value  = Math.round(d);
      // token dibiarkan kosong di mode gratis
      var tok = document.getElementById('ongkir_token'); if (tok) tok.value = '';

      // Tampilkan hint sederhana
      if (hint){
        hint.classList.remove('text-danger');
        hint.innerHTML =
          '<div class="p-2">'
          + '<div><b>'+ lat.toFixed(6) +', '+ lng.toFixed(6) +'</b></div>'
          + '<div><span class="text-muted">Jarak:</span> <b>'+ (d/1000).toFixed(2) +' km</b></div>'
          + '<div class="text-success">Lokasi tersimpan. Gratis ongkir.</div>'
          + '</div>';
      }
    }, function(err){
      // var geoErr = {1:'Izin lokasi ditolak', 2:'Lokasi tidak ditemukan', 3:'Timeout'};
      var geoErr = {1:'izin lokasi ditolak', 2:'lokasi nggak ketemu', 3:'timeout'};
        var msg = 'lokasi nggak keambil (' + (geoErr[err.code] || 'error ' + err.code) + '). Nyalain GPS & izinin lokasi, atau pilih lokasi manual ya.';

      // var msg = geoErr[err && err.code] || (err && err.message) || 'Gagal mengambil lokasi.';
      if (hint){
        hint.classList.add('text-danger'); hint.textContent = 'Ups: ' + msg;
      } else {
        alert('Ups: ' + msg);
      }
    }, { enableHighAccuracy:true, timeout:12000, maximumAge:0 });
  });
})();

$('#btnUseMyLocation').on('click', function(){
      var self = this;
      if (!navigator.geolocation){
        $hint.text('Peramban tidak mendukung geolokasi.').addClass('text-danger');
        return;
      }
      if (window.setBtnLoading) setBtnLoading(self, true);
      $hint.removeClass('text-danger').text('Mencari lokasi Anda...');

      navigator.geolocation.getCurrentPosition(function(pos){
        var lat = +pos.coords.latitude.toFixed(6);
        var lng = +pos.coords.longitude.toFixed(6);

        var approx = haversineM(STORE_LAT, STORE_LNG, lat, lng);
        if (approx > MAX_RADIUS_M){
          if (window.setBtnLoading) setBtnLoading(self, false);
          $hint.addClass('text-danger')
               .text('Maaf, lokasi Anda berada di luar radius layanan (maks '+MAX_RADIUS_KM+' km dari toko).');
          return;
        }

        if ($('#dest_lat').length) $('#dest_lat').val(lat);
        if ($('#dest_lng').length) $('#dest_lng').val(lng);

        var share = ' (Shareloc: ' + lat + ', ' + lng + ')';
        if ($ta.val().indexOf('Shareloc:') === -1) {
          $ta.val(($ta.val().trim() + share).trim());
          if ($count && $count.length) $count.text($ta.val().length + '/300');
        }
        if (window.setBtnLoading) setBtnLoading(self, false);
        $hint.removeClass('text-danger')
             .text('Koordinat tersimpan. Anda bisa buka peta untuk memastikan rute.');
      }, function(err){
        var geoErr = {1:'izin lokasi ditolak', 2:'lokasi nggak ketemu', 3:'timeout'};
        var msg = 'Ups, lokasi nggak keambil (' + (geoErr[err.code] || 'error ' + err.code) + '). Nyalain GPS & izinin lokasi, atau pilih lokasi manual ya.';
        $hint.addClass('text-danger').text(msg);
        if (window.setBtnLoading) setBtnLoading(self, false);
      }, { enableHighAccuracy:true, timeout:10000, maximumAge:0 });
    });

  </script>