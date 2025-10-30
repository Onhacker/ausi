
  <style>
    /* kamera + guide box */
    #cameraWrap{position:relative}
    #cameraWrap .guide{position:absolute;inset:0;display:grid;place-items:center;pointer-events:none}
    #cameraWrap .guide .box{width:40%;aspect-ratio:1/1;border:2px solid rgba(255,255,255,.7);border-radius:12px}

    /* Fullscreen fallback + proper fill */
    body.scan-lock { overflow: hidden; }
    #cameraWrap.fullscreen-scan{
      position: fixed !important;
      inset: 0 !important;
      z-index: 1060 !important; /* di atas modal biasa */
      background:#000;
      margin:0 !important;
      border-radius:0 !important;
      padding: env(safe-area-inset-top) env(safe-area-inset-right)
               env(safe-area-inset-bottom) env(safe-area-inset-left);
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
    }

    /* Saat Fullscreen API aktif */
    #cameraWrap:fullscreen,
    #cameraWrap:-webkit-full-screen { background:#000; }
    #cameraWrap:fullscreen video,
    #cameraWrap:-webkit-full-screen video,
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
    }

    /* Saat helper .is-fs menandai mode fullscreen */
    #cameraWrap.is-fs video{
      position:absolute; inset:0;
      width:100vw !important;
      height:100svh !important;
      height:100dvh !important;
      height:100vh !important;
      object-fit:cover !important;
      border-radius:0 !important;
      aspect-ratio:auto !important;
    }

    /* Tombol Exit (×) saat fullscreen */
    .fs-exit-btn{
      position:absolute; top:10px; right:10px;
      z-index: 1070;
      width:42px; height:42px; border:0; border-radius:999px;
      display:flex; align-items:center; justify-content:center;
      background:rgba(0,0,0,.6); color:#fff;
    }
    #cameraWrap.fullscreen-scan .fs-exit-btn{ display:flex !important; }

    /* (opsional) rapikan select */
    #cameraSelect{min-width:220px}
  </style>
</head>
<body>

<div class="container-fluid">
  <!-- start page title -->
  <div class="row">
    <div class="col-12">
      <div class="page-title-box">
        <div class="page-title-right">
          <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><?= htmlspecialchars($title) ?></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($subtitle) ?></li>
          </ol>
        </div>
        <h4 class="page-title"><?= htmlspecialchars($subtitle) ?></h4>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- KIRI: KAMERA -->
    <div class="col-lg-7">
      <div class="card shadow-sm">
        <div class="card-body">

          <!-- Area kamera (ikut fullscreen) -->
          <div id="cameraWrap" class="mb-2">
            <video id="preview" autoplay muted playsinline style="width:100%;border-radius:12px;background:#000;aspect-ratio:16/9;"></video>
            <div class="guide"><div class="box"></div></div>
            <!-- Tombol tutup fullscreen -->
            <button id="btnExitFs" type="button" class="fs-exit-btn d-none" aria-label="Tutup layar penuh">
              <i class="mdi mdi-close"></i>
            </button>
          </div>
          <div class="form-inline mb-2">
              <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="modeCheckin" name="scanMode" value="checkin" class="custom-control-input" checked>
                <label class="custom-control-label" for="modeCheckin">Check-in</label>
              </div>
              <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="modeCheckout" name="scanMode" value="checkout" class="custom-control-input">
                <label class="custom-control-label" for="modeCheckout">Checkout</label>
              </div>
            </div>
          <div class="d-flex flex-wrap align-items-center" style="gap:.5rem;">
            <select id="cameraSelect" class="form-control" style="max-width:320px"></select>

            <button id="btnStart" class="btn btn-primary">
              <i class="mdi mdi-play"></i> Mulai
            </button>

            <button id="btnStop"  class="btn btn-outline-secondary" disabled>
              <i class="mdi mdi-stop"></i> Stop
            </button>

            <button id="btnFlip"  class="btn btn-outline-info">
              <i class="mdi mdi-camera-switch"></i> Flip
            </button>

            <button id="btnTorch" class="btn btn-outline-warning" disabled>
              <i class="mdi mdi-flashlight"></i> Senter
            </button>

            <!-- Tampil setelah kamera nyala -->
            <button id="btnFull" class="btn btn-outline-dark d-none">
              <i class="mdi mdi-arrow-expand-all"></i> Layar Penuh
            </button>

            <!-- Mode Scan (radio) -->
            
          </div>
           <!-- <div class="card shadow-sm mt-3"> -->
        <!-- <div class="card-body"> -->
          <h6 class="mb-2"><i class="mdi mdi-keyboard-outline"></i> Input Manual / Barcode Gun</h6>
          <div class="input-group">
            <input type="text" id="kodeManual" class="form-control" placeholder="Tempel atau ketik kode booking lalu Enter">
            <div class="input-group-append">
              <button class="btn btn-outline-primary" id="btnManual">Kirim</button>
            </div>
          </div>
          <small class="text-muted d-block mt-1">
            Format harus sama dengan pada QR (contoh: <em>20250827-U1-opd-12-123</em>).
          </small>
        <!-- </div> -->
      <!-- </div> -->

          <small class="text-muted d-block mt-2">
            Tips: akses via HTTPS/localhost, gunakan kamera belakang untuk akurasi & senter, double-click video / tekan <b>F</b> untuk toggle layar penuh.
          </small>
        </div>
      </div>
    </div>

    <!-- KANAN: TIPS + INPUT MANUAL -->
    <div class="col-lg-5 mt-3 mt-lg-0">
      <div class="card shadow-sm">
        <div class="card-body">
          <h6 class="mb-2"><i class="mdi mdi-shield-check-outline"></i> Info / Tips</h6>
          <ul class="mb-0 pl-3">
            <li>Check-in hanya dapat dilakukan pada <b>tanggal yang tertera di booking</b>.</li>
            <li>Toleransi keterlambatan maksimal <b>1 jam setelah jam jadwal</b>.</li>
            <li>Pastikan pencahayaan cukup; aktifkan <b>senter</b> bila tersedia.</li>
            <li>Jarak kamera ± <b>15–25 cm</b>; sejajarkan QR di dalam bingkai.</li>
            <li>Untuk <i>barcode gun</i>, fokuskan kursor pada kolom input (di bawah) lalu scan.</li>
          </ul>
        </div>
      </div>

      <!-- Fallback manual / barcode gun -->
     
    </div>
  </div>
</div>

<!-- SFX nada sederhana -->
<script>
let audioCtx;
function initAudio(){
  if (!audioCtx) {
    const AC = window.AudioContext || window.webkitAudioContext;
    if (AC) audioCtx = new AC();
  }
}
function playTone(freq=880, durMs=180){
  if (!audioCtx) return;
  const ctx = audioCtx;
  const osc = ctx.createOscillator();
  const gain = ctx.createGain();
  osc.type = 'sine';
  osc.frequency.setValueAtTime(freq, ctx.currentTime);
  gain.gain.setValueAtTime(0.0001, ctx.currentTime);
  osc.connect(gain).connect(ctx.destination);
  osc.start();
  gain.gain.exponentialRampToValueAtTime(0.22, ctx.currentTime + 0.02);
  gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + durMs/1000);
  osc.stop(ctx.currentTime + durMs/1000 + 0.02);
}
function sfx(kind){
  if (kind === 'checkin')       playTone(880, 180);
  else if (kind === 'checkout') playTone(523.25, 180);
  else if (kind === 'error')    playTone(200, 220);
}
</script>

<!-- ZXing -->
<script src="<?php echo base_url(); ?>assets/js/zxing-browser.min.js"></script>

<script>
(function(){
  const { BrowserMultiFormatReader, BrowserCodeReader } = window.ZXingBrowser || {};
  if (!BrowserMultiFormatReader) {
    console.error('ZXingBrowser tidak ditemukan. Pastikan zxing-browser.min.js termuat.');
    return;
  }

  // Elemen
  const video     = document.getElementById('preview');
  const wrap      = document.getElementById('cameraWrap');
  const sel       = document.getElementById('cameraSelect');
  const btnStart  = document.getElementById('btnStart');
  const btnStop   = document.getElementById('btnStop');
  const btnFlip   = document.getElementById('btnFlip');
  const btnTorch  = document.getElementById('btnTorch');
  const btnFull   = document.getElementById('btnFull');
  const btnExitFs = document.getElementById('btnExitFs');
  const kodeManual= document.getElementById('kodeManual');
  const btnManual = document.getElementById('btnManual');

  // State
  const reader    = new BrowserMultiFormatReader();
  let controls    = null;
  let facing      = 'environment';
  let torchTrack  = null;
  let currentStream = null;

  // Helpers
  function getScanMode(){
    const el = document.querySelector('input[name="scanMode"]:checked');
    return (el ? el.value : 'checkin');
  }
  function isSecureOk(){
    return window.isSecureContext || ['localhost','127.0.0.1'].includes(location.hostname);
  }
  function setMirror(isFront){
    video.style.transform = isFront ? 'scaleX(-1)' : 'none';
  }
  function sanitizeKode(raw){
    const m = (raw||'').match(/[A-Za-z0-9_\-]+/g);
    return (m ? m.join('') : '');
  }

  // Fullscreen
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
    }catch(e){}
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
  document.addEventListener('fullscreenchange', ()=>{
    if (!document.fullscreenElement) {
      wrap.classList.remove('fullscreen-scan','is-fs');
      document.body.classList.remove('scan-lock');
      btnExitFs.classList.add('d-none');
    } else {
      wrap.classList.add('is-fs');
      btnExitFs.classList.remove('d-none');
    }
  });

  // Toggle via double-click & hotkey F
  video.addEventListener('dblclick', ()=>{
    if (document.fullscreenElement || wrap.classList.contains('fullscreen-scan')) exitFullscreen();
    else enterFullscreen();
  });
  document.addEventListener('keydown', (e)=>{
    if (e.key === 'Escape') exitFullscreen();
    if (e.key && e.key.toLowerCase() === 'f'){
      if (document.fullscreenElement || wrap.classList.contains('fullscreen-scan')) exitFullscreen();
      else enterFullscreen();
    }
  });

  // ZXing scan lifecycle
  function stopScan(){
    btnStop.disabled = true;
    if (controls && controls.stop) controls.stop();
    controls = null;

    if (video.srcObject){
      video.srcObject.getTracks().forEach(t=>t.stop());
      video.srcObject = null;
    }
    if (currentStream){
      currentStream.getTracks().forEach(t=>t.stop());
      currentStream = null;
    }
    // reset torch
    btnTorch.disabled = true;
    torchTrack = null;

    btnFull.classList.add('d-none');
    exitFullscreen();
  }

  async function ensureLabels(){
    try { await navigator.mediaDevices.getUserMedia({ video: true, audio: false }); }
    catch(e){}
  }

  async function listCameras(){
    await ensureLabels();
    let devices = [];
    if (BrowserCodeReader?.listVideoInputDevices) {
      devices = await BrowserCodeReader.listVideoInputDevices();
    } else if (navigator.mediaDevices?.enumerateDevices) {
      devices = (await navigator.mediaDevices.enumerateDevices()).filter(d=>d.kind==='videoinput');
    }
    sel.innerHTML = '';
    devices.forEach((d, i)=>{
      const opt = document.createElement('option');
      opt.value = d.deviceId || '';
      opt.textContent = d.label || `Kamera ${i+1}`;
      sel.appendChild(opt);
    });

    const last = localStorage.getItem('scan.camId');
    if (last && [...sel.options].some(o=>o.value===last)) sel.value = last;
    else localStorage.removeItem('scan.camId');

    return devices;
  }

  async function setupTorch(){
    // tunggu stream attach
    for (let i=0;i<25;i++){
      if (video.srcObject) break;
      await new Promise(r=>setTimeout(r,120));
    }
    const stream = video.srcObject;
    if (!stream) { btnTorch.disabled = true; return; }
    torchTrack = stream.getVideoTracks()[0];
    const caps = (torchTrack.getCapabilities && torchTrack.getCapabilities()) || {};
    btnTorch.disabled = !caps.torch;
  }

  function buildBaseConstraints({ deviceId, prefFacing } = {}){
    if (deviceId) {
      return { audio:false, video:{ deviceId:{ exact: deviceId }, width:{ ideal:1280 }, height:{ ideal:720 } } };
    }
    return {
      audio:false,
      video:{
        width:  { ideal:1280 },
        height: { ideal:720  },
        frameRate: { ideal:24, max:30 },
        facingMode: { ideal: (prefFacing || facing) }
      }
    };
  }

  async function startWithConstraints(cons){
    const c = await reader.decodeFromConstraints(cons, video, (res, err)=>{
      if (res && res.text){
        const code = (res.text || '').trim();
        stopScan();            // hentikan supaya tidak multi-trigger
        handleCode(code);      // proses kode
      }
    });
    currentStream = video.srcObject || null;
    controls = c;
    btnStop.disabled = false;
    await listCameras();
    await setupTorch();
    try { await video.play(); } catch(e){}

    btnFull.classList.remove('d-none'); // tampilkan tombol fullscreen
    btnExitFs.classList.add('d-none');
  }

  async function startScan(deviceId){
    stopScan();
    if (!isSecureOk()){
      Swal.fire({
        icon: 'error',
        title: 'Kamera diblokir',
        html: 'Akses kamera hanya diizinkan pada <b>HTTPS</b> atau <code>localhost</code>.',
      });
      return;
    }

    const baseByDevice = deviceId ? buildBaseConstraints({ deviceId }) : buildBaseConstraints({ prefFacing: facing });
    const tries = [
      baseByDevice,
      (deviceId ? buildBaseConstraints({ prefFacing: facing }) : null),
      { audio:false, video:{ width:{ ideal:640 }, height:{ ideal:480 }, facingMode:{ ideal:facing } } },
      { audio:false, video:{ width:{ ideal:640 }, height:{ ideal:480 }, facingMode:{ ideal:'user' } } },
      { audio:false, video:true }
    ].filter(Boolean);

    let lastErr = null;

    for (let i=0;i<tries.length;i++){
      try {
        setMirror(tries[i]?.video?.facingMode?.ideal === 'user' && !deviceId);
        await startWithConstraints(tries[i]);
        return;
      } catch (err) {
        console.warn('✗ getUserMedia/decode gagal:', err.name, err.message, 'constraint:', err.constraint, tries[i]);
        lastErr = err;

        if (err.name === 'OverconstrainedError' && tries[i].video?.deviceId){
          try {
            const alt = buildBaseConstraints({ prefFacing: facing });
            await startWithConstraints(alt);
            return;
          } catch (e2) { lastErr = e2; }
        }
        if (err.name === 'OverconstrainedError' && err.constraint === 'facingMode'){
          try {
            const alt = { audio:false, video:{ width:{ ideal:640 }, height:{ ideal:480 }, facingMode:{ ideal:'user' } } };
            setMirror(true);
            await startWithConstraints(alt);
            return;
          } catch (e3) { lastErr = e3; }
        }
      }
    }

    Swal.fire({
      icon: 'error',
      title: 'Gagal akses kamera',
      html: `${lastErr?.name || 'Unknown'} — ${lastErr?.message || ''}${lastErr?.constraint ? ('<br><small>Constraint: <code>'+lastErr.constraint+'</code></small>') : ''}`
    });
  }

  function toggleTorch(){
    try{
      if (!torchTrack) return;
      const cur = torchTrack.getSettings && torchTrack.getSettings().torch;
      torchTrack.applyConstraints({ advanced:[{torch: !cur}] });
    }catch(e){}
  }

  // Proses kode → kirim ke endpoint & SweetAlert hasil
  function handleCode(raw){
    initAudio();
    const kode = sanitizeKode(raw);
    if (!kode){
      sfx('error');
      Swal.fire({ icon:'error', title:'Kode tidak valid', text:'Tidak ada isi yang dapat diproses.' })
        .then(()=> startScan(sel.value || null));
      return;
    }

    const mode = getScanMode();
    const endpoint = mode === 'checkout'
      ? '<?= site_url('admin_scan/checkout_api') ?>'
      : '<?= site_url('admin_scan/checkin_api') ?>';

    const params = new URLSearchParams();
    params.set('kode', kode);
    <?php if (config_item('csrf_protection')): ?>
      params.set('<?= $CI->security->get_csrf_token_name() ?>','<?= $CI->security->get_csrf_hash() ?>');
    <?php endif; ?>

    Swal.fire({
      title: 'Memproses…',
      html: `<div class="text-left small">
               <div>Mode: <b>${mode}</b></div>
               <div>Kode: <code>${kode}</code></div>
             </div>`,
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });

    fetch(endpoint, {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'},
      body: params.toString(),
      credentials:'same-origin'
    })
    .then(r=>r.json())
    .then(j=>{
      Swal.close();
      if (j.ok){
        const d          = j.data || {};
        const isCheckout = (mode === 'checkout');
        const icon       = isCheckout ? 'success' : 'success';
        sfx(isCheckout ? 'checkout' : 'checkin');

        const html = `
          <div class="text-left">
            <div><b>${isCheckout ? 'Checkout' : 'Check-in'} berhasil</b></div>
            <div>Kode: <code>${d.kode || '-'}</code></div>
            <div>Nama: <b>${d.nama || '-'}</b></div>
            <div>Waktu: <b>${(d.checkin_at || d.checkout_at || '-')}</b></div>
            <div>Petugas: <b>${isCheckout ? (d.petugas_checkout || '-') : (d.petugas_checkin || '-')}</b></div>
            ${d.status ? `<div>Status: <b>${d.status}</b></div>` : ''}
            ${j.already ? '<div class="text-warning mt-1"><small>Aksi ini sudah pernah dilakukan.</small></div>' : ''}
          </div>
        `;

        Swal.fire({
            icon,
            title: isCheckout ? 'Checkout Berhasil' : 'Check-in Berhasil',
            html,
            showCancelButton: true,
            confirmButtonText: (j.detail_url ? 'Buka Detail Booking' : 'Tutup'),
            cancelButtonText: 'Scan lagi',
            reverseButtons: true,
            allowOutsideClick: false
          }).then((res) => {
            if (res.isConfirmed && j.detail_url) {
              // buka di tab yang sama (tanpa _blank)
              window.location.href = j.detail_url; // atau: location.assign(j.detail_url)
              return; // hentikan agar tidak lanjut startScan()
            }
            // lanjut scan lagi
            startScan(sel.value || null);
          });


      } else {
        sfx('error');
        Swal.fire({
          icon:'error',
          title:'Gagal',
          html: j.msg ? `<div class="text-left">${j.msg}</div>` : 'Tidak diketahui',
          showCancelButton: true,
          confirmButtonText: 'Coba lagi',
          cancelButtonText: 'Tutup',
          reverseButtons: true
        }).then((res)=>{
          if (res.isConfirmed){
            startScan(sel.value || null);
          }
        });
      }
    })
    .catch(err=>{
      sfx('error');
      Swal.fire({
        icon:'error',
        title:'Error jaringan',
        text: err && err.message ? err.message : String(err),
        confirmButtonText:'Coba lagi'
      }).then(()=> startScan(sel.value || null));
    });
  }

  // UI events
  btnStart.addEventListener('click', ()=> { initAudio(); startScan(sel.value || null); });
  btnStop .addEventListener('click', stopScan);
  btnFlip .addEventListener('click', ()=>{ initAudio(); facing = (facing==='environment' ? 'user' : 'environment'); setMirror(facing === 'user'); startScan(null); });
  btnTorch.addEventListener('click', toggleTorch);

  btnManual.addEventListener('click', ()=> { initAudio(); handleCode(kodeManual.value); });
  kodeManual.addEventListener('keydown', (e)=>{ if (e.key==='Enter'){ initAudio(); handleCode(kodeManual.value); }});

  sel.addEventListener('change', ()=>{
    localStorage.setItem('scan.camId', sel.value);
    setMirror(false);
    startScan(sel.value);
  });

  btnFull.addEventListener('click', async ()=>{
    initAudio();
    if (document.fullscreenElement || wrap.classList.contains('fullscreen-scan')) {
      exitFullscreen();
    } else {
      await enterFullscreen();
    }
  });
  btnExitFs.addEventListener('click', ()=> exitFullscreen());

  // Init daftar kamera & auto refresh saat kembali ke tab
  (async ()=>{
    await listCameras();
    document.addEventListener('visibilitychange', async ()=>{
      if (!document.hidden && !video.srcObject && !controls){
        await listCameras();
      }
    });
  })();

  // Torch update setelah stream mulai
  async function afterStreamAttached(){
    await setupTorch();
  }

  // Hook setelah decodeFromConstraints attach → sudah dipanggil dalam startWithConstraints()
})();
</script>