<?php $this->load->view("front_end/head.php") ?>

<?php
// --- Ambil mode aktif dari session
$meja_kode    = $this->session->userdata('guest_meja_kode');
$explicitMode = $this->session->userdata('cart__mode');

if (isset($mode) && $mode) {
    $__mode = $mode;
} elseif ($meja_kode) {
    $__mode = 'dinein';
} elseif ($explicitMode === 'delivery') {
    $__mode = 'delivery';
} else {
    $__mode = 'walkin';
}

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

<style>
/* ===========================================
   FRAME KAMERA NORMAL
   =========================================== */

#cameraWrap{
  position:relative;
  overflow:hidden;
  border-radius:10px;
  background:radial-gradient(120% 120% at 0% 0%, #1f2937 0%, #0b1220 60%);
}

/* video kamera */
#cameraWrap video{
  display:block;
  width:100%;
  aspect-ratio:16/9;
  background:#000;
  border-radius:10px;
  position:relative;
  z-index:10;
}

/* placeholder sebelum kamera nyala */
.video-placeholder{
  position:absolute;
  inset:0;
  display:grid;
  place-items:center;
  color:#cbd5e1;
  text-align:center;
  padding:16px;
  z-index:20; /* di atas video hitam awal */
}
.video-placeholder .bubble{
  backdrop-filter: blur(6px);
  background:rgba(15,23,42,.55);
  border:1px solid rgba(255,255,255,.12);
  border-radius:12px;
  padding:14px 16px;
}
.video-placeholder .qr{
  width:42px;
  height:42px;
  margin:0 auto 8px auto;
  opacity:.9;
}

/* garis laser merah animasi (area scan) */
.scan-laser{
  position:absolute;
  left:16%;
  right:16%;
  top:12%;
  height:2px;
  background:rgba(255,0,0,.9);
  animation:scan 2s linear infinite;
  box-shadow:0 0 12px rgba(255,0,0,.7);
  pointer-events:none;
  z-index:30; /* di atas video */
}
@keyframes scan{
  0%   { transform:translateY(0); }
  50%  { transform:translateY(72vh); }
  100% { transform:translateY(0); }
}

/* setelah kamera jalan → hide placeholder */
#cameraWrap.live .video-placeholder{
  display:none;
}

/* ===========================================
   FULLSCREEN STYLE (nyamain pola admin)
   =========================================== */

body.scan-lock { overflow:hidden; }

#cameraWrap.fullscreen-scan{
  position: fixed !important;
  inset: 0 !important;
  z-index:1060 !important; /* di atas navbar/menu */
  background:#000;
  margin:0 !important;
  border-radius:0 !important;
  padding:
    env(safe-area-inset-top)
    env(safe-area-inset-right)
    env(safe-area-inset-bottom)
    env(safe-area-inset-left);
}

#cameraWrap.fullscreen-scan video{
  position:absolute; inset:0;
  width:100vw !important;
  height:100svh !important;
  height:100dvh !important;
  height:100vh !important;
  object-fit:cover !important;
  border-radius:0 !important;
  aspect-ratio:auto !important;
  background:#000;
}

/* Native Fullscreen API */
#cameraWrap:fullscreen,
#cameraWrap:-webkit-full-screen {
  background:#000;
}
#cameraWrap:fullscreen video,
#cameraWrap:-webkit-full-screen video,
#cameraWrap.is-fs video,
video:fullscreen,
video:-webkit-full-screen{
  position:absolute; inset:0;
  width:100vw !important;
  height:100svh !important;
  height:100dvh !important;
  height:100vh !important;
  object-fit:cover !important;
  border-radius:0 !important;
  aspect-ratio:auto !important;
  background:#000;
}

/* tombol exit fullscreen (X) */
.fs-exit-btn{
  position:absolute;
  top:10px;
  right:10px;
  z-index:1070;
  width:42px;
  height:42px;
  border:0;
  border-radius:999px;
  display:flex;
  align-items:center;
  justify-content:center;
  background:rgba(0,0,0,.6);
  color:#fff;
  line-height:1;
  font-size:20px;
  font-weight:600;
}
#cameraWrap.fullscreen-scan .fs-exit-btn{
  display:flex !important;
}

/* hint text saat fullscreen */
.fs-hint{
  position:absolute;
  left:0;
  right:0;
  top:10px;
  max-width:80%;
  margin:0 auto;
  z-index:1080;
  display:none;

  text-align:center;
  font-size:14px;
  line-height:1.4;
  font-weight:500;
  color:#fff;
  text-shadow:0 2px 4px rgba(0,0,0,.8);

  background:rgba(0,0,0,.35);
  border-radius:8px;
  padding:6px 10px;
}

/* munculkan hint hanya kalau:
   - fullscreen aktif (.is-fs)
   - kamera lagi jalan (.scan-active)
*/
#cameraWrap.is-fs.scan-active .fs-hint{
  display:block;
}

/* dropdown kamera */
#cameraSelect{min-width:220px}
</style>

<div class="container-fluid">
  <div class="hero-title" role="banner" aria-label="Judul situs">
    <h1 class="text"><?php echo $title ?></h1>
    <span class="accent" aria-hidden="true"></span>
  </div>

  <?php $this->load->view("judul_mode") ?>

  <div class="row">
    <div class="col-lg-12">
      <div class="card-box">

        <?php if ($__mode === 'dinein'): ?>
          <div class="alert alert-warning mt-3 mb-0" role="alert">
            Kamu lagi <b>mode Dine-in / makan di tempat</b> 🍽️. <b>Mau tambah pesanan</b>? atau <b>pindah meja</b>? 
            <b>Scan</b> QR di meja baru pakai kamera di bawah, ya. 🙌
          </div>
        <?php elseif ($__mode === 'delivery'): ?>
          <div class="alert alert-info mt-3 mb-0" role="alert">
            Kamu lagi <b>mode Delivery / Kirim / Antar</b> 🚚. Kalau pengin <b>makan di tempat</b>,
            tinggal <b>scan barcode</b> yang ada di meja pakai kamera di bawah. 🍽️
          </div>
        <?php elseif ($__mode === 'walkin'): ?>
          <div class="alert alert-secondary mt-3 mb-0" role="alert">
            Kamu lagi <b>mode Bawa Pulang / Takeaway</b> 🛍️. Kalau berubah pikiran dan mau <b>makan di tempat</b>,
            cukup <b>scan QR</b> di meja pakai kamera di bawah. 😉
          </div>
        <?php endif; ?>

        <section id="scan-wrap" class="p-2">

          <!-- KAMERA WRAP -->
          <div class="position-relative mx-auto mb-2" style="max-width:520px">
            <div id="cameraWrap">

              <video id="video" playsinline autoplay muted></video>

              <div class="scan-laser"></div>

              <!-- overlay hint saat fullscreen aktif -->
              <div class="fs-hint">
                Arahkan QR ke kotak tengah
              </div>

              <!-- tombol keluar fullscreen (X), hidden default -->
              <button
                id="btnExitFs"
                type="button"
                class="fs-exit-btn d-none"
                aria-label="Tutup layar penuh">
                ✕
              </button>

              <!-- placeholder sebelum kamera nyala -->
              <div class="video-placeholder">
                <div class="bubble">
                  <svg class="qr" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="display:block">
                    <rect x="3" y="3" width="8" height="8" rx="1"></rect>
                    <rect x="13" y="3" width="8" height="8" rx="1"></rect>
                    <rect x="3" y="13" width="8" height="8" rx="1"></rect>
                    <path d="M13 13h4v4h-4zM19 13h2v2h-2zM17 19h-4v2h6v-4h-2z"></path>
                  </svg>

                  <div class="small">
                    klik <b>Buka Kamera</b> buat nyalain kamera 👇
                  </div>
                </div>
              </div>

            </div>
          </div>

          <!-- pilih kamera -->
          <div class="mb-2" style="max-width:520px">
            <label for="cameraSelect" class="small text-muted d-block mb-1">Pilih kamera</label>
            <select id="cameraSelect" class="form-control"></select>
          </div>

          <!-- tombol kontrol -->
          <div style="max-width:520px">
            <!-- row 1: start / stop -->
            <div class="row no-gutters">
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

            <!-- row 2: fullscreen / senter -->
            <div class="row no-gutters">
              <div class="col-6 pr-1 mb-2">
                <button id="btnFull" class="btn btn-outline-dark btn-block d-none">
                  Layar Penuh
                </button>
              </div>
              <div class="col-6 pl-1 mb-2">
                <button id="btnTorch" class="btn btn-outline-warning btn-block d-none" disabled>
                  Senter
                </button>
              </div>
            </div>
          </div>

          <!-- hasil scan -->
          <div id="resultBox" class="alert alert-info d-none mt-3" role="alert" style="max-width:520px">
            <div class="fw-bold mb-1">Hasil:</div>
            <div id="resultText" class="text-break"></div>
          </div>

          <!-- note -->
          <div class="alert alert-warning mb-0" role="alert">
            Silakan <b>scan QR/barcode</b> yang nempel di meja pakai kamera di atas.
            Kalau kameranya ngambek, cobain pakai <i>app</i> scanner bawaan HP kamu juga boleh. ✨
          </div>

        </section>

      </div><!-- ./card-box -->
    </div>
  </div>
</div>

<!-- ZXing -->
<script src="<?php echo base_url('assets/js/zxing-browser.min.js'); ?>"></script>

<script>
(function(){

  const video        = document.getElementById('video');
  const wrap         = document.getElementById('cameraWrap');
  const btnExitFs    = document.getElementById('btnExitFs');

  const resultBox    = document.getElementById('resultBox');
  const resultText   = document.getElementById('resultText');

  const btnStart     = document.getElementById('btnStart');
  const btnStop      = document.getElementById('btnStop');
  const btnFull      = document.getElementById('btnFull');
  const btnTorch     = document.getElementById('btnTorch');
  const cameraSelect = document.getElementById('cameraSelect');

  // fallback TAG_BASE kalau variabel view tidak ada
  const TAG_BASE = <?php echo json_encode(isset($tag_base) ? $tag_base : site_url('produk/tag/')); ?>;

  // state
  let codeReader     = null;
  let currentDeviceId= null;
  let running        = false;
  let warmedStream   = null;  // stream awal buat izin
  let controlsObj    = null;  // handler ZXing (buat stop)
  let currentStream  = null;  // simpan active stream
  let torchTrack     = null;  // track yg support torch

  /* =========================
     HELPER UI
  ========================== */
  function showResult(s){
    resultText.textContent = s;
    resultBox.classList.remove('d-none');
  }

  function isUrl(s){
    try {
      const u = new URL(s);
      return /^https?:$/.test(u.protocol);
    } catch(e){
      return false;
    }
  }

  function handleDecoded(text){
    showResult(text);

    if (isUrl(text)) {
      window.location.href = text;
      return;
    }

    const code = text.replace(/^MEJA[:\- ]/i,'').trim();
    window.location.href = TAG_BASE + encodeURIComponent(code);
  }

  async function listCameras(){
    cameraSelect.innerHTML = '';

    const devices = await ZXingBrowser.BrowserCodeReader.listVideoInputDevices();

    devices.forEach((d,i)=>{
      const opt = document.createElement('option');
      opt.value = d.deviceId;
      opt.textContent = d.label || ('Kamera ' + (i+1));
      cameraSelect.appendChild(opt);
    });

    // pilih kamera belakang kalau ada
    const back = devices.find(d => /back|rear|environment/i.test(d.label));
    currentDeviceId = back ? back.deviceId : (devices[0] ? devices[0].deviceId : null);

    if (currentDeviceId){
      cameraSelect.value = currentDeviceId;
    }

    return devices;
  }

  // minta izin kamera buat munculkan prompt
  async function warmPermission(){
    const constraints = {
      video: currentDeviceId
        ? { deviceId: { exact: currentDeviceId } }
        : { facingMode: { ideal: 'environment' } },
      audio: false
    };
    const s = await navigator.mediaDevices.getUserMedia(constraints);
    return s;
  }

  // cek support torch dan setup tombol senter
  function disableTorchUI(){
    btnTorch.disabled = true;
    btnTorch.classList.add('d-none');
    torchTrack = null;
  }
  function setupTorchUI(){
    const stream = video.srcObject;
    if (!stream) { disableTorchUI(); return; }

    const track = stream.getVideoTracks()[0];
    if (!track) { disableTorchUI(); return; }

    const caps = (track.getCapabilities && track.getCapabilities()) || {};
    if (caps.torch){
      torchTrack = track;
      btnTorch.disabled = false;
      btnTorch.classList.remove('d-none');
    } else {
      disableTorchUI();
    }
  }

  function toggleTorch(){
    try{
      if (!torchTrack) return;
      const cur = torchTrack.getSettings && torchTrack.getSettings().torch;
      torchTrack.applyConstraints({ advanced:[{torch: !cur}] });
    }catch(e){
      console.warn('toggleTorch gagal:', e);
    }
  }

  async function startZXing(){
    codeReader = new ZXingBrowser.BrowserMultiFormatReader();
    running = true;
    btnStart.disabled = true;
    btnStop.disabled  = false;

    controlsObj = await codeReader.decodeFromVideoDevice(
      currentDeviceId || undefined,
      video,
      (result, err, controls) => {
        if (result && running){
          running = false;
          try{ controls.stop(); }catch(_){}
          try{ codeReader.reset(); }catch(_){}
          handleDecoded(result.getText());
        }
      }
    );

    // simpan stream aktif
    currentStream = video.srcObject || null;

    // kamera nyala → sembunyikan placeholder
    wrap.classList.add('live');
    // tandai aktif scanning (buat fs-hint)
    wrap.classList.add('scan-active');

    // munculkan tombol fullscreen
    btnFull.classList.remove('d-none');
    // tombol keluar fullscreen disembunyiin dulu (nanti muncul pas masuk fs)
    btnExitFs.classList.add('d-none');

    // cek torch
    setupTorchUI();
  }

  /* =========================
     FULLSCREEN HANDLING
  ========================== */

  async function enterFullscreen(){
    try{
      if (wrap && wrap.requestFullscreen) {
        await wrap.requestFullscreen();
        wrap.classList.add('is-fs');
        btnExitFs.classList.remove('d-none');
        return;
      }
      if (video && video.webkitEnterFullscreen) {
        video.webkitEnterFullscreen();
        wrap.classList.add('is-fs');
        btnExitFs.classList.remove('d-none');
        return;
      }
    }catch(e){
      // kalau gagal, lanjut fallback css
    }

    // fallback CSS
    wrap.classList.add('fullscreen-scan','is-fs');
    document.body.classList.add('scan-lock');
    btnExitFs.classList.remove('d-none');
  }

  function exitFullscreen(){
    if (document.fullscreenElement && document.exitFullscreen) {
      document.exitFullscreen();
    }

    wrap.classList.remove('fullscreen-scan','is-fs');
    document.body.classList.remove('scan-lock');
    btnExitFs.classList.add('d-none');
  }

  // sync kalau user ESC fullscreen native
  document.addEventListener('fullscreenchange', ()=>{
    if (!document.fullscreenElement){
      wrap.classList.remove('fullscreen-scan','is-fs');
      document.body.classList.remove('scan-lock');
      btnExitFs.classList.add('d-none');
    } else {
      wrap.classList.add('is-fs');
      btnExitFs.classList.remove('d-none');
    }
  });

  function toggleFullscreen(){
    if (document.fullscreenElement || wrap.classList.contains('fullscreen-scan')){
      exitFullscreen();
    } else {
      enterFullscreen();
    }
  }

  video.addEventListener('dblclick', ()=>{
    toggleFullscreen();
  });

  btnExitFs.addEventListener('click', ()=>{
    exitFullscreen();
  });

  document.addEventListener('keydown', (e)=>{
    if (e.key === 'Escape'){
      exitFullscreen();
    }
    if (e.key && e.key.toLowerCase() === 'f'){
      toggleFullscreen();
    }
  });

  /* =========================
     START / STOP CAMERA
  ========================== */

  async function start(){
    if (running) return;

    // kalau halaman di-embed iframe strict
    if (window.top !== window.self){
      showResult('Halaman dibuka di dalam iframe; akses kamera bisa keblok. Coba buka langsung halaman ini ya 🙏');
      return;
    }

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia){
      showResult('Browser kamu belum dukung kamera. Coba Chrome/Safari versi terbaru ya.');
      return;
    }

    try{
      // isi dropdown awal
      try { await listCameras(); } catch(_){}

      // warm-up → munculkan prompt izin kamera
      warmedStream = await warmPermission();
      video.srcObject = warmedStream;
      await video.play();

      // setelah izin → refresh list (biar label kamera kebaca)
      try {
        await listCameras();
        if (warmedStream){
          warmedStream.getTracks().forEach(t=>t.stop());
          warmedStream=null;
        }
      } catch(_){}

      // mulai ZXing realtime
      await startZXing();

    }catch(e){
      running = false;
      btnStart.disabled = false;
      btnStop.disabled  = true;

      // keluar fullscreen kalau kebetulan aktif
      exitFullscreen();

      const name = e && e.name;
      const map = {
        NotAllowedError:     'Akses kamera ditolak. Cek izin situs di address bar ya.',
        NotFoundError:       'Kamera nggak ketemu di perangkat ini.',
        NotReadableError:    'Kamera lagi dipakai aplikasi lain. Tutup dulu aplikasi kamera/meeting-nya.',
        OverconstrainedError:'Kamera yang diminta nggak tersedia. Coba pilih kamera lain.',
        SecurityError:       'Butuh HTTPS atau halaman tidak dibatasi iframe/policy.',
      };
      showResult(map[name] || ('Gagal buka kamera: ' + (e && e.message ? e.message : e)));
    }
  }

  function hardStopStream(){
    try{
      const s = video.srcObject;
      if (s && s.getTracks){
        s.getTracks().forEach(t => t.stop());
      }
    }catch(_){}
    video.srcObject = null;

    if (currentStream && currentStream.getTracks){
      currentStream.getTracks().forEach(t=>t.stop());
    }
    currentStream = null;
  }

  function stop(){
    running = false;
    btnStart.disabled = false;
    btnStop.disabled  = true;

    // matikan ZXing
    try{
      if (codeReader) codeReader.reset();
    }catch(_){}
    if (controlsObj && controlsObj.stop){
      try{ controlsObj.stop(); }catch(_){}
    }
    controlsObj = null;

    hardStopStream();

    // balikin placeholder
    wrap.classList.remove('live');
    wrap.classList.remove('scan-active');

    // sembunyikan tombol fullscreen & senter
    btnFull.classList.add('d-none');
    disableTorchUI();

    // pastikan keluar fullscreen
    exitFullscreen();
  }

  /* =========================
     EVENTS
  ========================== */

  btnStart.addEventListener('click', start);
  btnStop .addEventListener('click', stop);
  btnFull .addEventListener('click', toggleFullscreen);
  btnTorch.addEventListener('click', toggleTorch);

  cameraSelect.addEventListener('change', ()=>{
    currentDeviceId = cameraSelect.value || null;
    if (running){
      // restart scanning dgn kamera baru
      stop();
      start();
    }
  });

  // init awal: isi dropdown kamera biar ga kosong
  (async ()=>{
    try { await listCameras(); } catch(_){}
  })();

})();
</script>

<?php $this->load->view("front_end/footer.php") ?>
