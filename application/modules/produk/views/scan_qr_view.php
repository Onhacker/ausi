<?php $this->load->view("front_end/head.php") ?>

<?php
// --- Dapetin mode aktif dari session (fallback kalau controller belum ngoper $mode)
$meja_kode    = $this->session->userdata('guest_meja_kode');
$explicitMode = $this->session->userdata('cart__mode');

// Tentukan mode tanpa ternary bersarang
if (isset($mode) && $mode) {
    $__mode = $mode;
} elseif ($meja_kode) {
    $__mode = 'dinein';
} elseif ($explicitMode === 'delivery') {
    $__mode = 'delivery';
} else {
    $__mode = 'walkin';
}

// Label & emoji tanpa ternary bersarang
$labels = [
    'dinein'   => 'Dine-in',
    'delivery' => 'Antar',
    'walkin'   => 'Bawa Pulang',
];
$emojis = [
    'dinein'   => '🍽️',
    'delivery' => '🚚',
    'walkin'   => '🛍️',
];

$__mode_label = $labels[$__mode] ?? 'Bawa Pulang';
$__mode_emoji = $emojis[$__mode] ?? '🛍️';
?>

<div class="container-fluid">
  <div class="hero-title" role="banner" aria-label="Judul situs">
    <h1 class="text"><?php echo $title ?></h1>
    <!-- teks panduan dipindah ke bawah (dekat tombol & kamera) -->
    <span class="accent" aria-hidden="true"></span>
  </div>
<?php $this->load->view("judul_mode") ?>
  <div class="row">
    <div class="col-lg-12">
      <div class="card-box">
             <?php if ($__mode === 'dinein'): ?>
    <!-- Dine-in -->
    <div class="alert alert-warning mt-3 mb-0" role="alert">
      Kamu lagi <b>mode Dine-in / makan di tempat</b> 🍽️. <b>Mau tambah pesanan</b>? atau <b>pindah meja</b>? 
      <b>Scan</b> QR di meja baru pakai kamera di bawah, ya. 🙌
    </div>

  <?php elseif ($__mode === 'delivery'): ?>
    <!-- Delivery -->
    <div class="alert alert-info mt-3 mb-0" role="alert">
      Kamu lagi <b>mode Delivery / Kirim / Antar</b> 🚚. Kalau pengin <b>makan di tempat</b>,
      tinggal <b>scan barcode</b> yang ada di meja pakai kamera di bawah. 🍽️
    </div>

  <?php elseif ($__mode === 'walkin'): ?>
    <!-- Walk-in / Takeaway -->
    <div class="alert alert-secondary mt-3 mb-0" role="alert">
      Kamu lagi <b>mode Bawa Pulang / Takeaway</b> 🛍️. Kalau berubah pikiran dan mau <b>makan di tempat</b>,
      cukup <b>scan QR</b> di meja pakai kamera di bawah. 😉
    </div>
  <?php endif; ?>
<!-- CSS: taruh di <head> atau sebelum penutup </body> -->
<style>
  /* bungkus video */
  .video-wrap{
    position:relative; overflow:hidden; border-radius:10px;
    background:
      radial-gradient(120% 120% at 0% 0%, #1f2937 0%, #0b1220 60%) /* fallback */;
    /* atau ganti dengan gambar pattern: */
    /* background: #0b1220 url("<?php echo base_url('assets/images/qr_pattern.svg'); ?>") center/160px auto repeat; */
  }
  /* placeholder layer saat kamera belum aktif */
 /* .video-wrap::before{
    content:""; position:absolute; inset:0; pointer-events:none;
    background:
      repeating-linear-gradient( 90deg, rgba(255,255,255,.06) 0 2px, transparent 2px 8px),
      repeating-linear-gradient(180deg, rgba(255,255,255,.05) 0 2px, transparent 2px 8px);
    opacity:.7;
  }*/
  .video-placeholder{
    position:absolute; inset:0; display:grid; place-items:center; color:#cbd5e1;
    text-align:center; padding:16px;
  }
  .video-placeholder .bubble{
    backdrop-filter: blur(6px);
    background:rgba(15,23,42,.55);
    border:1px solid rgba(255,255,255,.12);
    border-radius:12px; padding:14px 16px;
  }
  .video-placeholder .qr{
    width:42px; height:42px; margin:0 auto 8px auto; opacity:.9;
  }
  .scan-corners{
    position:absolute; inset:10% 15%;
    pointer-events:none;
  }
  .scan-corners span{
    position:absolute; width:22px; height:22px; border:3px solid #fff; opacity:.85;
  }

  .scan-corners .tl{top:0; left:0; border-right:none; border-bottom:none; border-radius:8px 0 0 0;}
  .scan-corners .tr{top:0; right:0; border-left:none; border-bottom:none; border-radius:0 8px 0 0;}
  .scan-corners .bl{bottom:0; left:0; border-right:none; border-top:none; border-radius:0 0 0 8px;}
  .scan-corners .br{bottom:0; right:0; border-left:none; border-top:none; border-radius:0 0 8px 0;}
  /* state ketika live: sembunyikan placeholder & corners halus */
  .video-wrap.live .video-placeholder,
  .video-wrap.live .scan-corners{ display:none; }
  .scan-corners { display:none !important; }
/* garis laser merah yg naik-turun */
.scan-laser{
  position:absolute;
  left:16%;                /* kiri-kanan agak masuk biar pas dengan frame */
  right:16%;
  top:12%;                 /* titik start (harus cocok dengan tinggi frame) */
  height:2px;
  background:rgba(255,0,0,.9);
  animation:scan 2s linear infinite;
  box-shadow:0 0 12px rgba(255,0,0,.7);
  pointer-events:none;
  z-index:3;               /* di atas video */
}
@keyframes scan{
  0%   { transform:translateY(0); }
  50%  { transform:translateY(72vh); } /* jarak turun; sesuaikan tinggi area video */
  100% { transform:translateY(0); }
}

/* kalau pakai wrapper .video-wrap seperti contoh sebelumnya, pastikan urutan layer */
.video-wrap{ position:relative; overflow:hidden; border-radius:10px; }
.video-wrap video{ display:block; width:100%; border-radius:10px; }

.scan-corners span{ width:16px; height:16px; border-width:2px; opacity:.6; }
.scan-corners span{ border-color:#22c55e; }

</style>

        <!-- ====== FRAME KAMERA SCAN ====== -->
        <section id="scan-wrap" class="p-2">
      
         
          
          

          <div class="position-relative mx-auto" style="max-width:520px">
            <!-- HTML: ganti blok video lama -->
<div class="position-relative mx-auto" style="max-width:520px">
  <div id="videoWrap" class="video-wrap">
    <video id="video" playsinline autoplay muted style="width:100%; aspect-ratio:16/9; display:block; background:transparent; border-radius:10px"></video>

    <!-- hiasan sudut kotak scan -->
   <!--  <div class="scan-corners">
      <span class="tl"></span><span class="tr"></span>
      <span class="bl"></span><span class="br"></span>
    </div> -->
  <div class="scan-laser"></div>
    <!-- placeholder sampai kamera nyala -->
    <div class="video-placeholder">
      <div class="bubble">
        <svg class="qr" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <rect x="3" y="3" width="8" height="8" rx="1"></rect>
          <rect x="13" y="3" width="8" height="8" rx="1"></rect>
          <rect x="3" y="13" width="8" height="8" rx="1"></rect>
          <path d="M13 13h4v4h-4zM19 13h2v2h-2zM17 19h-4v2h6v-4h-2z"></path>
        </svg>

        <div class="small">klik <b>Start</b> buat nyalain kamera 👇</div>
      </div>
    </div>
  </div>
</div>

            <!-- overlay frame (opsional) -->
            <!--
            <div id="overlay" aria-hidden="true"
                 style="position:absolute; inset:0; pointer-events:none; display:grid; place-items:center;">
              <div style="width:70%; aspect-ratio:1/1; border:3px solid rgba(255,255,255,.85); border-radius:12px;
                          box-shadow:0 0 0 9999px rgba(0,0,0,.25);"></div>
            </div>
            -->
          </div>
          <!-- SELECT DI ATAS -->
          <div class="mb-2" style="max-width:520px">
            <label for="cameraSelect" class="small text-muted d-block mb-1">Pilih kamera</label>
            <select id="cameraSelect" class="form-control"></select>
          </div>

          <!-- TOMBOL DI BAWAH TERBAGI 2 -->
          <div class="row no-gutters" style="max-width:520px">
            <div class="col-6 pr-1 mb-2">
              <button id="btnStart" class="btn btn-blue btn-block">
                Buka Kamera
              </button>
            </div>
            <div class="col-6 pl-1 mb-2">
              <button id="btnStop" class="btn btn-outline-secondary btn-block" disabled>
                Tutup
              </button>
            </div>
          </div>


          <!-- hasil -->
          <div id="resultBox" class="alert alert-info d-none mt-3" role="alert" style="max-width:520px">
            <div class="fw-bold mb-1">Hasil:</div>
            <div id="resultText" class="text-break"></div>
          </div>
           <div class="alert alert-warning  mb-0" role="alert">
    Silakan <b>scan QR/barcode</b> yang nempel di meja pakai kamera di atas.
    Kalau kameranya ngambek, cobain pakai <i>app</i> scanner bawaan HP kamu juga boleh. ✨
  </div>
        </section>
        <!-- ====== END FRAME KAMERA ====== -->
       
      </div>
    </div>
  </div>

  <!-- ZXing -->
  <script src="<?php echo base_url('assets/js/zxing-browser.min.js'); ?>"></script>
</div>

<script>
(function(){
  const video        = document.getElementById('video');
  const resultBox    = document.getElementById('resultBox');
  const resultText   = document.getElementById('resultText');
  const btnStart     = document.getElementById('btnStart');
  const btnStop      = document.getElementById('btnStop');
  const cameraSelect = document.getElementById('cameraSelect');

  // fallback TAG_BASE kalau variabel view tidak ada
  const TAG_BASE = <?php echo json_encode(isset($tag_base) ? $tag_base : site_url('produk/tag/')); ?>;

  let codeReader = null;
  let currentDeviceId = null;
  let running = false;
  let warmedStream = null; // stream pemanasan untuk memicu izin

  function showResult(s){
    resultText.textContent = s;
    resultBox.classList.remove('d-none');
  }
  function isUrl(s){
    try { const u = new URL(s); return /^https?:$/.test(u.protocol); } catch(e){ return false; }
  }
  function handleDecoded(text){
    showResult(text);
    if (isUrl(text)) { window.location.href = text; return; }
    const code = text.replace(/^MEJA[:\- ]/i,'').trim();
    window.location.href = TAG_BASE + encodeURIComponent(code);
  }

  async function listCameras(){
    cameraSelect.innerHTML = '';
    const devices = await ZXingBrowser.BrowserCodeReader.listVideoInputDevices();
    devices.forEach((d,i)=>{
      const opt = document.createElement('option');
      opt.value = d.deviceId;
      opt.textContent = d.label || `Kamera ${i+1}`;
      cameraSelect.appendChild(opt);
    });
    // pilih kamera belakang kalau ketemu
    const back = devices.find(d => /back|rear|environment/i.test(d.label));
    currentDeviceId = back ? back.deviceId : (devices[0] ? devices[0].deviceId : null);
    if (currentDeviceId) cameraSelect.value = currentDeviceId;
  }

  // "Pemanasan" untuk memicu prompt izin kamera lebih cepat
  async function warmPermission(){
    const constraints = {
      video: currentDeviceId ? { deviceId: { exact: currentDeviceId } } : { facingMode: { ideal: 'environment' } },
      audio: false
    };
    const s = await navigator.mediaDevices.getUserMedia(constraints);
    return s;
  }

  async function startZXing(){
    codeReader = new ZXingBrowser.BrowserMultiFormatReader();
    running = true;
    btnStart.disabled = true; btnStop.disabled = false;

    await codeReader.decodeFromVideoDevice(
      currentDeviceId || undefined, // null/undefined = otomatis pilih
      video,
      (result, err, controls) => {
        if (result && running){
          running = false;
          try { controls.stop(); } catch(_){}
          try { codeReader.reset(); } catch(_){}
          handleDecoded(result.getText());
        }
        // error frame diabaikan
      }
    );
  }

  async function start(){
    if (running) return;

    // cegah di dalam iframe ketat
    if (window.top !== window.self){
      // kasih notice halus di result box
      showResult('Halaman dibuka di dalam iframe; akses kamera bisa keblok. Coba buka langsung halaman ini ya 🙏');
      return;
    }

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia){
      showResult('Browser kamu belum dukung kamera. Coba Chrome/Safari versi terbaru ya.');
      return;
    }

    try{
      // 1) kalau belum tahu label device (belum izin), kita daftar device dulu
      try { await listCameras(); } catch(_){}

      // 2) WARM-UP: paksa muncul prompt izin kamera lewat getUserMedia
      warmedStream = await warmPermission();
      video.srcObject = warmedStream;
      await video.play();

      // 3) Setelah izin, device labels biasanya akan muncul → pilih kamera belakang lagi
      try {
        await listCameras();
        if (warmedStream){ warmedStream.getTracks().forEach(t=>t.stop()); warmedStream=null; }
      } catch(_){}

      // 4) Mulai ZXing pakai deviceId yang terpilih
      await startZXing();

    }catch(e){
      running = false;
      btnStart.disabled = false; btnStop.disabled = true;

      const name = e && e.name;
      const map = {
        NotAllowedError:  'Akses kamera ditolak. Cek izin situs di address bar ya.',
        NotFoundError:    'Kamera nggak ketemu di perangkat ini.',
        NotReadableError: 'Kamera lagi dipakai aplikasi lain. Tutup dulu aplikasi kamera/meeting-nya.',
        OverconstrainedError: 'Kamera yang diminta nggak tersedia. Coba pilih kamera lain.',
        SecurityError:    'Butuh HTTPS atau halaman tidak dibatasi iframe/policy.',
      };
      showResult(map[name] || ('Gagal buka kamera: ' + (e && e.message ? e.message : e)));
    }
  }

  function stop(){
    running = false;
    btnStart.disabled = false; btnStop.disabled = true;
    try{ if (codeReader) codeReader.reset(); }catch(_){}
    try{
      const s = video.srcObject;
      if (s && s.getTracks) s.getTracks().forEach(t => t.stop());
    }catch(_){}
    video.srcObject = null;
     if (window._scan_onStopDecor) window._scan_onStopDecor();
  }

  // events
  cameraSelect.addEventListener('change', ()=>{
    currentDeviceId = cameraSelect.value || null;
    if (running){ stop(); start(); }
  });
  btnStart.addEventListener('click', start);
  btnStop.addEventListener('click', stop);

  // init
  (async ()=>{
    try { await listCameras(); } catch(_){}
  })();
})();
// JS: panggil ini di script kamu (pakai id yang sama).
(function(){
  const video = document.getElementById('video');
  const wrap  = document.getElementById('videoWrap');

  // ketika stream mulai play → tandai live (sembunyikan placeholder)
  video.addEventListener('playing', ()=> wrap.classList.add('live'));

  // pastikan juga waktu stop memunculkan lagi placeholder
  function onStop(){
    wrap.classList.remove('live');
  }
  // contoh integrasi: panggil onStop() di fungsi stop() kamu
  window._scan_onStopDecor = onStop; // optional hook global
})();

</script>

<?php $this->load->view("front_end/footer.php") ?>
