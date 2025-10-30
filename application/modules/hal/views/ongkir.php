<!-- ================== ONGKIR MAP + LOKASI SAYA (LEAFLET) — SIAP TEMPEL ================== -->

<style>
/* Ikon lokasi saya: bulat + efek pulse */
.pulse-marker { position: relative; }
.pulse-marker .pulse-dot{
  position:absolute; left:0; top:0;
  width:14px; height:14px; border-radius:999px;
  background:#3b82f6; border:2px solid #fff;
  box-shadow:0 0 0 0 rgba(59,130,246,0.6);
  animation:pulse 2s infinite;
}
@keyframes pulse{
  0%{ box-shadow:0 0 0 0 rgba(59,130,246,0.6); }
  70%{ box-shadow:0 0 0 14px rgba(59,130,246,0); }
  100%{ box-shadow:0 0 0 0 rgba(59,130,246,0); }
}
/* Tombol locate di map */
.leaflet-control-locate button{
  width:34px;height:34px; line-height:34px;
  border:none; background:#fff; cursor:pointer;
  font-size:18px;
}
.leaflet-control-locate button:hover{ background:#f3f4f6; }
</style>
<!-- ============ INPUT FORM DETEKSI LOKASI (SIAP TEMPEL) ============ -->

<!-- Input tampilan (yang terlihat user) -->
<div class="input-group mb-2">
  <input type="text" id="lokasi_user" class="form-control"
         placeholder="Klik 📍 untuk deteksi lokasi saya"
         readonly required>
  <button type="button" id="btnDetect" class="btn btn-outline-primary" aria-label="Deteksi lokasi">
    📍
  </button>
</div>
<small id="lokasiStatus" class="text-muted d-block mb-2"></small>

<script>
// Pastikan ini dieksekusi SETELAH peta & fungsi setDest/useMyLocation terdefinisi
document.addEventListener('DOMContentLoaded', function(){
  const inp   = document.getElementById('lokasi_user');
  const btn   = document.getElementById('btnDetect');
  const stat  = document.getElementById('lokasiStatus');

  function setStatus(msg, ok=false){
    if (!stat) return;
    stat.textContent = msg || '';
    stat.classList.toggle('text-success', !!ok);
    stat.classList.toggle('text-danger', !ok && !!msg);
  }

  function fillFromGeo(lat, lng, acc){
    // Update input tampilan
    if (inp){
      const accTxt = (typeof acc === 'number') ? ` (±${Math.round(acc)} m)` : '';
      inp.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}${accTxt}`;
      inp.setCustomValidity(''); // lulus required
    }

    // Panggil alur kamu: tampilkan marker tujuan + hitung ongkir
    if (typeof useMyLocation === 'function'){
      useMyLocation(lat, lng, acc);
    } else if (typeof setDest === 'function'){
      setDest(lat, lng);
    }

    setStatus('Lokasi terdeteksi.', true);
  }

  function explainError(err){
    let msg = 'Gagal deteksi lokasi.';
    if (err && typeof err.code !== 'undefined'){
      if (err.code === 1) msg = 'Izin lokasi ditolak. Izinkan lokasi untuk situs ini.';
      else if (err.code === 2) msg = 'Lokasi tidak tersedia. Aktifkan GPS/Wi-Fi atau pindah tempat.';
      else if (err.code === 3) msg = 'Waktu habis (timeout). Coba lagi.';
    }
    if (!window.isSecureContext && location.hostname !== 'localhost'){
      msg += ' (Butuh HTTPS atau jalankan via localhost).';
    }
    if (window.top !== window.self){
      msg += ' (Jika di dalam iframe, butuh allow="geolocation").';
    }
    setStatus(msg, false);
    alert(msg);
  }

  function detectNow(){
    if (!('geolocation' in navigator)){
      setStatus('Browser tidak mendukung Geolocation.', false);
      alert('Browser tidak mendukung Geolocation.');
      return;
    }
    setStatus('Mendeteksi lokasi…');
    navigator.geolocation.getCurrentPosition(
      pos => {
        const { latitude, longitude, accuracy } = pos.coords;
        fillFromGeo(latitude, longitude, accuracy);
      },
      explainError,
      { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
    );
  }

  if (btn){
    btn.addEventListener('click', (e)=>{
      e.preventDefault();
      detectNow();
    });
  }

  // (Opsional) tekan Enter di input untuk re-detect
  if (inp){
    inp.addEventListener('keydown', (e)=>{
      if (e.key === 'Enter'){ e.preventDefault(); detectNow(); }
    });
  }
});
</script>

<!-- Hidden fields untuk dikirim ke server -->
<input type="hidden" name="dest_lat" id="dest_lat">
<input type="hidden" name="dest_lng" id="dest_lng">
<input type="hidden" name="distance_m" id="distance_m">
<input type="hidden" name="ongkir" id="ongkir">

<!-- Peta -->
<div id="mapPick" style="height:300px; border-radius:12px;"></div>
<small id="ongkirInfo" class="text-muted d-block mt-1"></small>

<!-- Leaflet CDN (boleh diganti file lokal) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
// Koordinat toko (ambil dari DB/Identitas)
const STORE = { lat: -3.7156933057722576, lng: 120.40755839999998 };

// Skema tarif: base 5k untuk <=2 km, lalu 1k per 0.5 km berikutnya
function hitungOngkir(distance_m){
  const km = distance_m / 1000;
  const baseKm = 2;
  const baseFee = 5000;
  const perKm = 1000;
  if (km <= baseKm) return baseFee;
  const extraKm = Math.ceil((km - baseKm) * 2) / 2; // pembulatan 0.5km ke atas
  return baseFee + Math.max(0, extraKm) * perKm;
}

// Haversine (meter)
function haversine(lat1, lon1, lat2, lon2){
  const R = 6371000; // m
  const toRad = d => d * Math.PI/180;
  const dLat = toRad(lat2-lat1);
  const dLon = toRad(lon2-lon1);
  const a = Math.sin(dLat/2)**2 + Math.cos(toRad(lat1))*Math.cos(toRad(lat2))*Math.sin(dLon/2)**2;
  return 2 * R * Math.asin(Math.sqrt(a));
}

// Formatter
function fmtRp(n){ return 'Rp' + (n||0).toLocaleString('id-ID'); }
function fmtKm(m){ return (m/1000).toFixed(2) + ' km'; }

document.addEventListener('DOMContentLoaded', () => {
  // Inisialisasi peta
  const m = L.map('mapPick').setView([STORE.lat, STORE.lng], 14);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19}).addTo(m);

  // Marker toko
  L.marker([STORE.lat, STORE.lng]).addTo(m).bindPopup('Toko');

  // ====== Marker tujuan (draggable) + perhitungan ongkir ======
  let destMarker = null;
  function setDest(lat, lng){
    if (!destMarker){
      destMarker = L.marker([lat, lng], {draggable:true}).addTo(m);
      destMarker.on('dragend', e => {
        const p = e.target.getLatLng();
        setDest(p.lat, p.lng);
      });
    } else {
      destMarker.setLatLng([lat, lng]);
    }
    // Hitung jarak & ongkir
    const d = haversine(STORE.lat, STORE.lng, lat, lng);
    const ong = hitungOngkir(d);

    // Set hidden + info
    document.getElementById('distance_m').value = Math.round(d);
    document.getElementById('ongkir').value = ong;
    document.getElementById('dest_lat').value = lat;
    document.getElementById('dest_lng').value = lng;

    document.getElementById('ongkirInfo').innerHTML =
      `Jarak: <b>${fmtKm(d)}</b> &nbsp; | &nbsp; Estimasi ongkir: <b>${fmtRp(ong)}</b>`;
  }

  // Klik peta untuk pilih tujuan
  m.on('click', e => setDest(e.latlng.lat, e.latlng.lng));

  // ====== Lokasi Saya (ikon bulat + akurasi) ======
  let myLocMarker = null;   // marker bulat
  let myLocCircle = null;   // lingkar akurasi (meter)

  function showMyLocation(lat, lng, acc){
    const icon = L.divIcon({
      className: 'pulse-marker',
      html: '<span class="pulse-dot"></span>',
      iconSize: [14,14],
      iconAnchor: [7,7]
    });

    if (!myLocMarker){
      myLocMarker = L.marker([lat, lng], { icon }).addTo(m).bindPopup('Lokasi saya');
    } else {
      myLocMarker.setLatLng([lat, lng]);
    }

    const radius = (typeof acc === 'number' && acc > 0) ? acc : 30;
    if (!myLocCircle){
      myLocCircle = L.circle([lat, lng], {
        radius, weight:1, color:'#3b82f6', fillColor:'#3b82f6', fillOpacity:0.08
      }).addTo(m);
    } else {
      myLocCircle.setLatLng([lat, lng]).setRadius(radius);
    }
  }

  function useMyLocation(lat, lng, acc){
    showMyLocation(lat, lng, acc);
    setDest(lat, lng); // hitung ongkir dari lokasi saya
    m.flyTo([lat, lng], 16, { duration: 0.6 });
  }

  // ====== Tombol 📍 (geolokasi on-demand) ======
  const LocateControl = L.Control.extend({
    onAdd: function(){
      const wrap = L.DomUtil.create('div', 'leaflet-control-locate leaflet-bar');
      const btn = L.DomUtil.create('button', '', wrap);
      btn.type = 'button';
      btn.title = 'Deteksi lokasi saya';
      btn.textContent = '📍';

      L.DomEvent.on(btn, 'click', (e) => {
        L.DomEvent.stopPropagation(e);
        L.DomEvent.preventDefault(e);

        if (!navigator.geolocation){
          alert('Browser tidak mendukung Geolocation.');
          return;
        }
        navigator.geolocation.getCurrentPosition(
          pos => {
            const { latitude, longitude, accuracy } = pos.coords;
            useMyLocation(latitude, longitude, accuracy);
          },
          err => {
            let msg = 'Gagal deteksi lokasi: ' + err.message;
            if (!window.isSecureContext && location.hostname !== 'localhost'){
              msg += '\n(Butuh HTTPS atau jalankan via localhost).';
            }
            alert(msg);
          },
          { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
        );
      });

      return wrap;
    },
    onRemove: function(){}
  });
  m.addControl(new LocateControl({ position: 'topleft' }));

  // ====== Auto-coba geolokasi saat load (silent) ======
  if (navigator.geolocation){
    navigator.geolocation.getCurrentPosition(
      pos => {
        const { latitude, longitude, accuracy } = pos.coords;
        useMyLocation(latitude, longitude, accuracy);
      },
      () => {/* diam kalau user menolak izin */},
      { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 }
    );
  }
});
</script>
