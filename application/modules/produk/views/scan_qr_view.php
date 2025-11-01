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
   BASE / HELPERS
   =========================================== */
.ctrl-btn-inner{
  display:flex;
  align-items:center;
  justify-content:center;
  gap:6px;
  font-weight:600;
  line-height:1.2;
  font-size:15px;
}
.ctrl-btn-inner .ico{
  font-size:16px;
  line-height:1;
  display:inline-block;
}

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
  z-index:20;
}
.video-placeholder .bubble{
  backdrop-filter: blur(6px);
  background:rgba(15,23,42,.55);
  border:1px solid rgba(255,255,255,.12);
  border-radius:12px;
  padding:14px 16px;
  text-align:center;
}
.video-placeholder .qr{
  width:42px;
  height:42px;
  margin:0 auto 8px auto;
  opacity:.9;
}

/* garis laser merah animasi */
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
  z-index:30;
}
@keyframes scan{
  0%   { transform:translateY(0); }
  50%  { transform:translateY(72vh); }
  100% { transform:translateY(0); }
}

/* kotak tengah (guide box) */
.scan-box{
  position:absolute;
  left:50%; top:50%;
  transform:translate(-50%,-50%);
  width:60vw;
  max-width:320px;
  aspect-ratio:1/1;
  border:2px solid rgba(255,255,255,.75);
  border-radius:12px;
  box-shadow:
    0 0 30px rgba(0,0,0,.6),
    0 0 8px rgba(255,255,255,.3) inset;
  z-index:40;
  pointer-events:none;
}

/* setelah kamera jalan → hide placeholder */
#cameraWrap.live .video-placeholder{
  display:none;
}

/* ===========================================
   FULLSCREEN STYLE
   =========================================== */

body.scan-lock { overflow:hidden; }

#cameraWrap.fullscreen-scan{
  position: fixed !important;
  inset: 0 !important;
  z-index:1060 !important;
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
  box-shadow:0 4px 12px rgba(0,0,0,.6);
}
#cameraWrap.fullscreen-scan .fs-exit-btn{
  display:flex !important;
}

/* hint instruksi saat fullscreen */
.fs-hint{
  position:absolute;
  left:0;
  right:0;
  top:calc(env(safe-area-inset-top) + 56px);
  max-width:90%;
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
  box-shadow:0 8px 24px rgba(0,0,0,.7);
}
/* tampilkan hint kalau fullscreen aktif + kamera aktif */
#cameraWrap.is-fs.scan-active .fs-hint{
  display:block;
}

/* tombol torch besar di fullscreen (🔦) */
.fs-torch-btn{
  position:absolute;
  left:50%;
  transform:translateX(-50%);
  bottom:calc(env(safe-area-inset-bottom) + 20px);
  z-index:1080;

  min-width:64px;
  min-height:64px;
  border-radius:999px;
  border:2px solid rgba(255,255,255,.4);
  background:rgba(0,0,0,.6);
  color:#fff;

  font-size:13px;
  line-height:1.2;
  font-weight:600;
  text-align:center;

  padding:10px 12px;
  flex-direction:column;
  align-items:center;
  justify-content:center;

  box-shadow:0 8px 24px rgba(0,0,0,.7);

  /* default: HILANG total kalau bukan fullscreen */
  display:none;
  opacity:.4;
  pointer-events:none;
}
.fs-torch-btn .icon{
  font-size:20px;
  line-height:1;
}
.fs-torch-btn .lbl{
  font-size:12px;
  font-weight:600;
  line-height:1.2;
  margin-top:4px;
  color:#fff;
  text-shadow:0 1px 2px rgba(0,0,0,.9);
}

/* fullscreen aktif + kamera aktif → tampilkan tombol torch (tapi masih "mati") */
#cameraWrap.is-fs.scan-active .fs-torch-btn{
  display:flex;
}

/* device support torch → aktifkan pointer + terang */
#cameraWrap.is-fs.scan-active.torch-ready .fs-torch-btn{
  opacity:1;
  pointer-events:auto;
  border-color:rgba(255,255,255,.8);
  background:rgba(0,0,0,.7);
}

/* Tombol Senter kecil (bawah kamera, mode normal) */
#btnTorch{
  visibility:hidden; /* default hidden sampai device support torch */
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

              <!-- garis laser -->
              <div class="scan-laser"></div>

              <!-- kotak tengah -->
              <div class="scan-box" aria-hidden="true"></div>

              <!-- overlay hint saat fullscreen aktif -->
              <div class="fs-hint">
                Arahkan QR ke kotak tengah
              </div>

              <!-- tombol keluar fullscreen (X) -->
              <button
                id="btnExitFs"
                type="button"
                class="fs-exit-btn d-none"
                aria-label="Tutup layar penuh">
                ✕
              </button>

              <!-- tombol torch fullscreen (muncul HANYA saat fullscreen) -->
              <button
                id="btnTorchFS"
                type="button"
                class="fs-torch-btn"
                aria-label="Senter">
                <div class="icon">🔦</div>
                <div class="lbl">Senter</div>
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
                  <span class="ctrl-btn-inner">
                    <span class="ico">📷</span>
                    <span>Buka Kamera</span>
                  </span>
                </button>
              </div>
              <div class="col-6 pl-1 mb-2">
                <button id="btnStop" class="btn btn-outline-secondary btn-block" disabled>
                  <span class="ctrl-btn-inner">
                    <span class="ico">✕</span>
                    <span>Tutup</span>
                  </span>
                </button>
              </div>
            </div>

            <!-- row 2: fullscreen / senter kecil -->
            <div class="row no-gutters">
              <div class="col-6 pr-1 mb-2">
                <button id="btnFull" class="btn btn-outline-dark btn-block d-none">
                  <span class="ctrl-btn-inner">
                    <span class="ico">⛶</span>
                    <span>Layar Penuh</span>
                  </span>
                </button>
              </div>
              <div class="col-6 pl-1 mb-2">
                <button id="btnTorch" class="btn btn-outline-warning btn-block" disabled>
                  <span class="ctrl-btn-inner">
                    <span class="ico">🔦</span>
                    <span>Senter</span>
                  </span>
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

  const { BrowserMultiFormatReader, BrowserCodeReader } = window.ZXingBrowser || {};
  if (!BrowserMultiFormatReader) {
    console.error('ZXingBrowser tidak ditemukan');
    return;
  }

  /* =========================
     ELEMEN DOM
  ========================== */
  const video        = document.getElementById('video');
  const wrap         = document.getElementById('cameraWrap');
  const btnExitFs    = document.getElementById('btnExitFs');

  const btnTorchFS   = document.getElementById('btnTorchFS'); // tombol torch fullscreen bawah

  const resultBox    = document.getElementById('resultBox');
  const resultText   = document.getElementById('resultText');

  const btnStart     = document.getElementById('btnStart');
  const btnStop      = document.getElementById('btnStop');
  const btnFull      = document.getElementById('btnFull');
  const btnTorch     = document.getElementById('btnTorch');
  const cameraSelect = document.getElementById('cameraSelect');

  // fallback TAG_BASE kalau variabel view tidak ada
  const TAG_BASE = <?php echo json_encode(isset($tag_base) ? $tag_base : site_url('produk/tag/')); ?>;

  /* =========================
     STATE
  ========================== */
  const reader       = new BrowserMultiFormatReader();
  let running        = false;
  let controlsObj    = null;
  let currentStream  = null;
  let torchTrack     = null;
  let facing         = 'environment'; // prefer kamera belakang

  /* =========================
     HELPERS
  ========================== */
  function showResult(s){
    resultText.textContent = s;
    resultBox.classList.remove('d-none');
  }

  function isUrl(s){
    try { const u = new URL(s); return /^https?:$/.test(u.protocol); }
    catch(e){ return false; }
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

  function setMirror(isFront){
    // depan => mirror biar natural
    video.style.transform = isFront ? 'scaleX(-1)' : 'none';
  }

  function disableTorchUI(){
    wrap.classList.remove('torch-ready');

    // tombol kecil
    btnTorch.disabled = true;
    btnTorch.style.visibility = 'hidden';

    // tombol fullscreen
    btnTorchFS.style.opacity = '0.4';
    btnTorchFS.style.pointerEvents = 'none';

    torchTrack = null;
  }

  function setupTorchUI(){
    const stream = video.srcObject;
    if (!stream){ disableTorchUI(); return; }

    const track = stream.getVideoTracks()[0];
    if (!track){ disableTorchUI(); return; }

    const caps = (track.getCapabilities && track.getCapabilities()) || {};
    let supported = false;

    if (caps.torch){
      supported = true;
    } else if (caps.fillLightMode && Array.isArray(caps.fillLightMode)){
      if (caps.fillLightMode.includes('flash') || caps.fillLightMode.includes('torch')){
        supported = true;
      }
    }

    if (supported){
      torchTrack = track;
      wrap.classList.add('torch-ready');

      // tombol kecil (mode normal)
      btnTorch.disabled = false;
      btnTorch.style.visibility = 'visible';

      // tombol fullscreen (akan kelihatan hanya saat fullscreen)
      btnTorchFS.style.opacity = '1';
      btnTorchFS.style.pointerEvents = 'auto';
    } else {
      disableTorchUI();
    }

    // coba autofocus continuous (beberapa Samsung nurut)
    try {
      track.applyConstraints({ advanced: [{ focusMode: "continuous" }] });
    } catch(e){
      // aman kalau gagal
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

  /* =========================
     CAMERA ENUM & CONSTRAINTS
  ========================== */
  async function ensureLabels(){
    // minta izin sekali supaya label kamera kebuka di Android
    try {
      await navigator.mediaDevices.getUserMedia({video:true, audio:false});
    } catch(e){}
  }

  async function listCameras(){
    await ensureLabels();

    let devices = [];
    if (BrowserCodeReader?.listVideoInputDevices){
      devices = await BrowserCodeReader.listVideoInputDevices();
    } else if (navigator.mediaDevices?.enumerateDevices){
      devices = (await navigator.mediaDevices.enumerateDevices()).filter(d=>d.kind==='videoinput');
    }

    cameraSelect.innerHTML = '';
    devices.forEach((d,i)=>{
      const opt = document.createElement('option');
      opt.value = d.deviceId || '';
      opt.textContent = d.label || `Kamera ${i+1}`;
      cameraSelect.appendChild(opt);
    });

    // pilih kamera belakang default kalau labelnya ada "back"/"environment"
    const back = devices.find(d => /back|rear|environment/i.test(d.label||''));
    if (back){
      cameraSelect.value = back.deviceId || '';
    }

    return devices;
  }

  function buildBaseConstraints({ deviceId, prefFacing } = {}){
    // high-res attempt 1080p
    const highRes = {
      audio:false,
      video:{
        width:{ ideal:1920 },
        height:{ ideal:1080 },
        frameRate:{ ideal:30, max:60 }
      }
    };

    if (deviceId){
      highRes.video.deviceId = { exact: deviceId };
    } else {
      highRes.video.facingMode = { ideal: (prefFacing || facing) };
    }

    return highRes;
  }

  async function startWithConstraints(cons){
    // Jalankan decoding pakai constraints kita (bukan default ZXing 480p)
    controlsObj = await reader.decodeFromConstraints(cons, video, (res, err)=>{
      if (res && res.text){
        const codeText = (res.text || '').trim();
        stopScan();        // stop supaya ga spam scan
        handleDecoded(codeText);
      }
    });

    currentStream = video.srcObject || null;

    wrap.classList.add('live');
    wrap.classList.add('scan-active');

    btnStop.disabled  = false;
    btnStart.disabled = true;

    btnFull.classList.remove('d-none');
    btnExitFs.classList.add('d-none');

    // mirror kalau front cam
    const isFrontNow =
      (cons.video && cons.video.facingMode && /user/i.test(cons.video.facingMode.ideal||'')) ||
      (facing === 'user');
    setMirror(isFrontNow);

    // torch / autofocus setup
    setupTorchUI();
  }

  async function startScan(deviceId){
    running = true;

    // beberapa browser nolak resolusi tinggi → kita coba bertahap
    const tries = [];

    // 1. high-res dengan kamera yg dipilih user
    if (deviceId){
      tries.push(buildBaseConstraints({ deviceId }));
    }

    // 2. high-res pakai 'environment'
    tries.push(buildBaseConstraints({ prefFacing:'environment' }));

    // 3. fallback 1280x720
    tries.push({
      audio:false,
      video:{
        width:{ ideal:1280 },
        height:{ ideal:720 },
        frameRate:{ ideal:30, max:60 },
        facingMode:{ ideal:'environment' }
      }
    });

    // 4. fallback 640x480
    tries.push({
      audio:false,
      video:{
        width:{ ideal:640 },
        height:{ ideal:480 },
        facingMode:{ ideal:'environment' }
      }
    });

    // 5. ultimate true
    tries.push({ audio:false, video:true });

    let lastErr = null;
    for (let i=0;i<tries.length;i++){
      try {
        await startWithConstraints(tries[i]);
        return;
      } catch(err){
        console.warn('getUserMedia/decode gagal:', err.name, err.message, 'constraint:', err.constraint, tries[i]);
        lastErr = err;
      }
    }

    // semua gagal
    running = false;
    btnStart.disabled = false;
    btnStop.disabled  = true;
    exitFullscreen();

    const name = lastErr && lastErr.name;
    const msg  = lastErr && lastErr.message;
    showResult(
      (name==='NotAllowedError')
        ? 'Akses kamera ditolak. Cek izin situs di address bar ya.'
        : (name==='NotFoundError')
          ? 'Kamera nggak ketemu di perangkat ini.'
          : (name==='NotReadableError')
            ? 'Kamera lagi dipakai aplikasi lain. Tutup dulu aplikasi kamera/meeting-nya.'
            : (name==='OverconstrainedError')
              ? 'Kamera yang diminta nggak tersedia. Coba pilih kamera lain.'
              : (name==='SecurityError')
                ? 'Butuh HTTPS atau halaman tidak dibatasi iframe/policy.'
                : ('Gagal buka kamera: ' + (msg || name || 'tidak diketahui'))
    );
  }

  function stopScan(){
    btnStop.disabled  = true;
    btnStart.disabled = false;

    running = false;

    if (controlsObj && controlsObj.stop){
      try { controlsObj.stop(); } catch(_){}
    }
    controlsObj = null;

    // matikan semua track
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

    wrap.classList.remove('live','scan-active','torch-ready');

    btnFull.classList.add('d-none');
    disableTorchUI();

    exitFullscreen();
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
      // fallback if native fullscreen gagal
    }

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

  // sync kalau user tekan ESC native fullscreen
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

  video.addEventListener('dblclick', toggleFullscreen);
  btnExitFs.addEventListener('click', exitFullscreen);

  document.addEventListener('keydown', (e)=>{
    if (e.key === 'Escape'){ exitFullscreen(); }
    if (e.key && e.key.toLowerCase() === 'f'){ toggleFullscreen(); }
  });

  /* =========================
     EVENTS
  ========================== */

  btnStart.addEventListener('click', async ()=>{
    // keamanan basic: kamera high-res butuh HTTPS / localhost
    if (!(window.isSecureContext || ['localhost','127.0.0.1'].includes(location.hostname))){
      showResult('Akses kamera butuh HTTPS atau localhost.');
      return;
    }

    await listCameras(); // refresh list & pilih kamera belakang kalau ada
    const devId = cameraSelect.value || null;

    stopScan(); // bersihkan sesi lama kalau ada
    await startScan(devId);
  });

  btnStop.addEventListener('click', stopScan);
  btnFull.addEventListener('click', toggleFullscreen);

  btnTorch.addEventListener('click', toggleTorch);
  btnTorchFS.addEventListener('click', toggleTorch);

  cameraSelect.addEventListener('change', ()=>{
    // user ganti kamera → kalau lagi jalan kita restart ke kamera itu
    if (running){
      const devId = cameraSelect.value || null;
      stopScan();
      startScan(devId);
    }
  });

  // init awal: isi dropdown kamera biar ga kosong
  (async ()=>{
    try { await listCameras(); } catch(_){}
  })();

})();
</script>

<?php $this->load->view("front_end/footer.php") ?>
