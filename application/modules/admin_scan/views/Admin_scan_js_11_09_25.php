

<script>
  // tombol salin
  (function(){
    function copyToClipboard(text){
      if (navigator.clipboard) return navigator.clipboard.writeText(text);
      const ta=document.createElement('textarea'); ta.value=text; document.body.appendChild(ta);
      ta.select(); document.execCommand('copy'); document.body.removeChild(ta);
      return Promise.resolve();
    }
    document.querySelectorAll('.btn-copy').forEach(btn=>{
      btn.addEventListener('click', function(){
        const txt = this.getAttribute('data-clip') || '';
        copyToClipboard(txt).then(()=>{
          this.innerHTML = '<i class="mdi mdi-check"></i> Disalin';
          this.classList.add('btn-success');
          setTimeout(()=>{ this.innerHTML = '<i class="mdi mdi-content-copy"></i> Salin'; this.classList.remove('btn-success'); }, 1500);
        });
      });
    });
  })();

  // Foto Lampiran (modal & refresh)
  function setLampiranModal(url){
    const img = document.getElementById('lampiranFotoModalImg');
    const dl  = document.getElementById('lampiranFotoDownload');
    if (img) img.src = url || '';
    if (dl)  dl.href = url || '#';
  }
  function renderLampiranFoto(url) {
    const box = document.getElementById('lampiranFotoBox');
    if (!box) return;
    if (!url) {
      box.innerHTML = '<div class="text-muted">Belum ada foto lampiran.</div>';
      setLampiranModal('');
      return;
    }
    box.innerHTML = `
      <img src="${url}" alt="Foto Lampiran"
           class="img-thumbnail js-lampiran-foto"
           style="max-height:120px;object-fit:cover;cursor:zoom-in;display:block"
           data-full="${url}">
      <a class="btn btn-sm btn-outline-secondary mt-2" href="${url}" download>
        <i class="mdi mdi-download"></i> Unduh Foto
      </a>
      <button type="button" class="btn btn-sm btn-outline-primary mt-2 ml-1"
              data-toggle="modal" data-target="#modalFotoLampiran">
        <i class="mdi mdi-magnify-plus"></i> Perbesar
      </button>
    `;
    setLampiranModal(url);
  }
  document.addEventListener('click', function(e){
    const el = e.target.closest('.js-lampiran-foto');
    if (!el) return;
    const full = el.getAttribute('data-full') || el.getAttribute('src') || '';
    setLampiranModal(full);
    if (window.jQuery){ $('#modalFotoLampiran').modal('show'); }
    else { document.querySelector('[data-target="#modalFotoLampiran"]')?.click(); }
  });
</script>

<script>
(function(){
  const CAN_CAPTURE  = <?= $can_capture ? 'true' : 'false' ?>;
  const STATUS_LABEL = <?= json_encode($status_label) ?>;
  const KODE    = <?= json_encode($booking->kode_booking ?? '') ?>;

  const wrap    = document.getElementById('docCamWrap');   // pastikan ada <div id="docCamWrap">
  const video   = document.getElementById('camPreview');
  const canvas  = document.getElementById('camCanvas');
  const imgPrev = document.getElementById('docPreview');
  const msg     = document.getElementById('docMsg');

  const ddlCam  = document.getElementById('camSelect');
  const bStart  = document.getElementById('btnCamStart');
  const bStop   = document.getElementById('btnCamStop');
  const bShot   = document.getElementById('btnCamCapture');
  const bSave   = document.getElementById('btnDocSave');
  const bFull   = document.getElementById('btnDocFull');      // jika ada tombol fullscreen normal
  const bExitFs = document.getElementById('btnDocExitFs');    // jika ada tombol exit fullscreen
  const bFsShut = document.getElementById('btnFsShutter');    // jika ada tombol shutter fullscreen
  const bTorch  = document.getElementById('btnCamTorch');     // ← tombol senter normal
  const bFsTorch= document.getElementById('btnFsTorch');      // ← tombol senter fullscreen
  const fFile   = document.getElementById('fileFallback');

  const safeRenderLampiran = (window.renderLampiranFoto || function(){}).bind(window);

  let stream = null;
  let currentDeviceId = null;

  // torch
  let torchTrack = null;
  let torchOn    = false;
  let torchCap   = false;

  function setMsg(t, ok=false){ if(!msg) return; msg.textContent = t; msg.className = ok ? 'small text-success' : 'small text-muted'; }
  const isFsActive = () => document.fullscreenElement === wrap || wrap?.classList.contains('fullscreen-scan') || wrap?.classList.contains('is-fs');

  async function ensureLabels(){ try { const t=await navigator.mediaDevices.getUserMedia({video:true,audio:false}); t.getTracks().forEach(x=>x.stop()); } catch(e){} }
  async function listCams(){ const devs = await navigator.mediaDevices.enumerateDevices(); return devs.filter(d=>d.kind==='videoinput'); }
  function pickDefault(cams){
    if (!cams.length) return null;
    const by = re => cams.find(d => re.test(d.label||''));
    return by(/usb|external|logitech|webcam|brio|hd pro/i) || by(/back|rear|environment/i) || cams[0];
  }
  async function fillCamSelect(selectedId=null){
    if (!ddlCam) return [];
    const cams = await listCams();
    ddlCam.innerHTML = '';
    cams.forEach((c,i)=>{
      const opt = document.createElement('option');
      opt.value = c.deviceId || '';
      opt.textContent = c.label || `Kamera ${i+1}`;
      ddlCam.appendChild(opt);
    });
    const def = selectedId && cams.some(c=>c.deviceId===selectedId)
      ? selectedId : (pickDefault(cams)?.deviceId || (cams[0]?.deviceId || ''));
    if (def) ddlCam.value = def;
    return cams;
  }

  async function startWithDevice(deviceId){
    if (stream){ stream.getTracks().forEach(t=>t.stop()); stream=null; }
    const constraints = deviceId
      ? { video:{ deviceId:{ exact: deviceId }, width:{ideal:1280}, height:{ideal:720} }, audio:false }
      : { video:{ facingMode:{ ideal:'environment' }, width:{ideal:1280}, height:{ideal:720} }, audio:false };

    stream = await navigator.mediaDevices.getUserMedia(constraints);
    video.srcObject = stream;
    await video.play().catch(()=>{});
    currentDeviceId = stream.getVideoTracks()[0]?.getSettings()?.deviceId || deviceId || null;

    if (bShot) bShot.disabled = false;
    if (bStop) bStop.disabled = false;
    if (bFull) bFull.classList?.remove('d-none');

    await setupTorch();       // ← cek & aktifkan kemampuan senter
    setMsg('Kamera aktif.', true);
  }

  async function startCam(){
    if (!CAN_CAPTURE){ setMsg('Kamera dinonaktifkan (status: '+STATUS_LABEL+').'); return; }
    try{
      await ensureLabels();
      const cams = await fillCamSelect(currentDeviceId || localStorage.getItem('doc.camId'));
      if (!cams.length){ setMsg('Tidak ada kamera terdeteksi.'); return; }
      const chosenId = ddlCam?.value || pickDefault(cams)?.deviceId || null;
      await startWithDevice(chosenId);
      try { localStorage.setItem('doc.camId', chosenId || ''); } catch(e){}
    }catch(e){
      setMsg('Tidak bisa mengakses kamera: '+(e?.message || e));
    }
  }

  function stopCam(){
    turnOffTorch();   // ← matikan senter saat berhenti
    if (stream){ stream.getTracks().forEach(t=>t.stop()); stream=null; }
    if (video) video.srcObject = null;
    if (bShot) bShot.disabled = true;
    if (bStop) bStop.disabled = true;
    if (bFull) bFull.classList?.add('d-none');
    disableTorchUI();
    hideFsUi();
    exitFullscreen();
    setMsg('Kamera dimatikan.');
  }

  function capture(){
    if (!video || !video.videoWidth) return;
    const w = video.videoWidth, h = video.videoHeight;
    const maxW = 1280, scale = Math.min(1, maxW / w);
    canvas.width  = Math.round(w*scale);
    canvas.height = Math.round(h*scale);
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
    imgPrev.src = dataUrl;
    imgPrev.classList.remove('d-none');
    if (bSave) bSave.disabled = false;
    setMsg('Pratinjau siap. Klik "Simpan Foto".', true);
  }

  function captureAndExit(){
    capture();
    if (isFsActive()){
      setTimeout(()=> exitFullscreen(), 80);
    }
  }

  function readFile(file){
    const r = new FileReader();
    r.onload = ()=>{
      imgPrev.src = r.result;
      imgPrev.classList.remove('d-none');
      if (bSave) bSave.disabled = false;
      setMsg('Pratinjau siap (unggah). Klik "Simpan Foto".', true);
    };
    r.readAsDataURL(file);
  }

  async function saveDoc(){
    const dataUrl = imgPrev.src || '';
    if (!/^data:image\//.test(dataUrl)) return;

    const params = new URLSearchParams();
    params.set('kode', KODE);
    params.set('image', dataUrl);
    <?php if (config_item('csrf_protection')): ?>
      params.set('<?= $CI->security->get_csrf_token_name() ?>','<?= $CI->security->get_csrf_hash() ?>');
    <?php endif; ?>

    if (bSave) bSave.disabled = true;
    setMsg('Menyimpan...', false);

    try{
      const r = await fetch('<?= site_url('admin_scan/upload_doc_photo') ?>', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'},
        body: params.toString(),
        credentials: 'same-origin'
      });
      const j = await r.json();
      if (j.ok){
        setMsg('Tersimpan: '+ (j.file || ''), true);
        if (j.url){
          imgPrev.src = j.url;
          safeRenderLampiran(j.url);
        }
      } else {
        setMsg('Gagal: '+(j.msg||'')); if (bSave) bSave.disabled = false;
      }
    }catch(e){
      setMsg('Error: '+(e?.message || e)); if (bSave) bSave.disabled = false;
    }
  }

  // ====== SENTER ======
  function updateTorchUI(){
    // normal button
    if (bTorch){
      bTorch.disabled = !torchCap;
      bTorch.classList.toggle('btn-warning', torchOn);
      bTorch.classList.toggle('btn-outline-warning', !torchOn);
      bTorch.innerHTML = `<i class="mdi ${torchOn?'mdi-flashlight-off':'mdi-flashlight'}"></i> ${torchOn?'Matikan':'Senter'}`;
    }
    // fullscreen button
    if (bFsTorch){
      if (isFsActive() && torchCap) bFsTorch.classList.remove('d-none');
      else bFsTorch.classList.add('d-none');
      bFsTorch.classList.toggle('active', torchOn);
      bFsTorch.innerHTML = `<i class="mdi ${torchOn?'mdi-flashlight-off':'mdi-flashlight'}"></i><span class="d-none d-sm-inline"> ${torchOn?'Matikan':'Senter'}</span>`;
    }
  }
  function disableTorchUI(){
    torchTrack = null; torchOn = false; torchCap = false;
    updateTorchUI();
  }
  async function setupTorch(){
    disableTorchUI();
    const stream = video.srcObject;
    if (!stream) return;
    const track = stream.getVideoTracks()[0];
    if (!track) return;
    const caps = (track.getCapabilities && track.getCapabilities()) || {};
    if (caps.torch){
      torchTrack = track;
      torchCap   = true;
      torchOn    = (track.getSettings && !!track.getSettings().torch) || false;
    }
    updateTorchUI();
  }
  async function toggleTorch(){
    if (!torchTrack) return;
    try{
      await torchTrack.applyConstraints({ advanced:[{ torch: !torchOn }] });
      torchOn = !torchOn;
      updateTorchUI();
    }catch(e){
      setMsg('Senter tidak didukung/ditolak di perangkat ini.', false);
      // fallback: disable tombol agar tidak menipu user
      disableTorchUI();
    }
  }
  function turnOffTorch(){
    if (!torchTrack || !torchOn) return;
    try { torchTrack.applyConstraints({ advanced:[{ torch:false }] }); } catch(e){}
    torchOn=false; updateTorchUI();
  }

  // ===== Fullscreen (API + fallback) =====
  async function enterFullscreen(){
    try{
      if (wrap && wrap.requestFullscreen) {
        await wrap.requestFullscreen();
        wrap.classList.add('is-fs');
        showFsUi();
        return;
      }
      if (video && video.webkitEnterFullscreen) {
        video.webkitEnterFullscreen();
        wrap.classList.add('is-fs');
        showFsUi();
        return;
      }
    }catch(e){ /* fallback */ }
    wrap?.classList.add('fullscreen-scan','is-fs');
    document.body.classList.add('scan-lock');
    showFsUi();
  }
  function exitFullscreen(){
    if (document.fullscreenElement && document.exitFullscreen) {
      document.exitFullscreen();
    }
    wrap?.classList.remove('fullscreen-scan','is-fs');
    document.body.classList.remove('scan-lock');
    hideFsUi();
  }
  function showFsUi(){
    bExitFs?.classList.remove('d-none');
    bFsShut?.classList.remove('d-none');
    updateTorchUI(); // tampilkan btn senter FS jika bisa
  }
  function hideFsUi(){
    bExitFs?.classList.add('d-none');
    bFsShut?.classList.add('d-none');
    bFsTorch?.classList.add('d-none');
  }
  document.addEventListener('fullscreenchange', ()=>{
    if (!document.fullscreenElement) {
      wrap?.classList.remove('fullscreen-scan','is-fs');
      document.body.classList.remove('scan-lock');
      hideFsUi();
    } else {
      wrap?.classList.add('is-fs');
      showFsUi();
    }
  });

  // ===== Events =====
  bStart && bStart.addEventListener('click', startCam);
  bStop  && bStop .addEventListener('click', stopCam);

  bShot  && bShot .addEventListener('click', capture);
  bFsShut&& bFsShut?.addEventListener('click', captureAndExit);
  video  && video .addEventListener('click', ()=>{ if (isFsActive()) captureAndExit(); });

  bSave  && bSave .addEventListener('click', saveDoc);
  fFile  && fFile .addEventListener('change', (e)=>{ if (e.target.files && e.target.files[0]) readFile(e.target.files[0]); });

  ddlCam && ddlCam.addEventListener('change', async (e)=>{
    const id = e.target.value || null;
    try {
      await startWithDevice(id);
      try { localStorage.setItem('doc.camId', id || ''); } catch(_){}
    } catch(err){
      setMsg('Gagal beralih kamera: '+(err?.message || err));
    }
  });

  bFull  && bFull .addEventListener('click', async ()=>{ if (stream){ await enterFullscreen(); } });
  bExitFs&& bExitFs?.addEventListener('click', exitFullscreen);

  // tombol senter
  bTorch && bTorch.addEventListener('click', toggleTorch);
  bFsTorch && bFsTorch.addEventListener('click', toggleTorch);

  // ESC untuk keluar (desktop)
  document.addEventListener('keydown', (e)=>{ if (e.key === 'Escape') exitFullscreen(); });

  // Hot-swap saat device colok/cabut
  navigator.mediaDevices?.addEventListener?.('devicechange', async ()=>{
    const cams = await fillCamSelect(currentDeviceId);
    const stillThere = cams.some(c=>c.deviceId === currentDeviceId);
    if (!stillThere){
      const next = pickDefault(cams);
      if (next && stream){
        setMsg('Perangkat berubah—beralih kamera…');
        await startWithDevice(next.deviceId);
        if (ddlCam) ddlCam.value = next.deviceId;
      } else if (stream){
        stopCam();
        setMsg('Semua kamera tidak tersedia.');
      }
    }
  });

  // init dropdown
  (async ()=>{
    if (!CAN_CAPTURE){
      bStart?.setAttribute('disabled','disabled');
      bShot ?.setAttribute('disabled','disabled');
      bStop ?.setAttribute('disabled','disabled');
      fFile ?.setAttribute('disabled','disabled');
      ddlCam ?.setAttribute('disabled','disabled');
      setMsg('Kamera dinonaktifkan (status: '+STATUS_LABEL+').');
      return;
    }
    await ensureLabels();
    await fillCamSelect(localStorage.getItem('doc.camId'));
  })();

  window.addEventListener('beforeunload', stopCam);
})();
</script>
<script>
(function(){
  const KODE        = <?= json_encode($booking->kode_booking ?? '') ?>;
  const INIT_SURAT  = <?= json_encode($surat_url ?? '') ?>;

  const fileInput   = document.getElementById('fileSuratTugas');
  const btnUpload   = document.getElementById('btnUploadSurat');
  const progWrap    = document.getElementById('suratProg');
  const progBar     = progWrap ? progWrap.querySelector('.progress-bar') : null;
  const msgEl       = document.getElementById('suratMsg');

  const box         = document.getElementById('suratTugasBox');
  const modalView   = document.getElementById('suratTugasView');
  const modalDl     = document.getElementById('suratTugasDownloadModal');

  function setMsgSurat(t, ok){ if(!msgEl) return; msgEl.textContent=t; msgEl.className = 'small ' + (ok?'text-success':'text-muted'); }

  function showProgress(show){ if(!progWrap) return; progWrap.classList.toggle('d-none', !show); if (show && progBar) progBar.style.width='0%'; }
  function setProgress(pct){ if(progBar) progBar.style.width = Math.max(0, Math.min(100, pct)) + '%'; }

  function isPdf(url){ return /\.pdf(?:\?|#|$)/i.test(url||''); }
  function renderSuratModal(url){
    if (!modalView) return;
    modalView.innerHTML = '';
    if (!url){ modalView.innerHTML = '<div class="p-3 text-white-50">Tidak ada berkas.</div>'; return; }
    if (isPdf(url)){
      const obj = document.createElement('object');
      obj.setAttribute('data', url);
      obj.setAttribute('type', 'application/pdf');
      obj.style.width='100%'; obj.style.height='70vh'; obj.style.border='0';
      modalView.appendChild(obj);
    } else {
      const img = document.createElement('img');
      img.src = url; img.alt='Surat Tugas'; img.style.maxWidth='100%'; img.style.maxHeight='80vh'; img.style.objectFit='contain'; img.style.display='block'; img.style.margin='0 auto';
      modalView.appendChild(img);
    }
    if (modalDl) modalDl.href = url;
  }

  function renderSuratBox(url){
    if (!box) return;
    if (!url){
      box.innerHTML = '<span class="text-muted">Belum ada surat tugas.</span>';
      renderSuratModal('');
      return;
    }
    box.innerHTML = `
      <div class="btn-group btn-group-sm" role="group">
        <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#modalSuratTugas">
          <i class="mdi mdi-eye"></i> Lihat
        </button>
        <a id="suratTugasUnduh" class="btn btn-outline-secondary ml-1" href="${url}" download>
          <i class="mdi mdi-download"></i> Unduh
        </a>
      </div>
    `;
    renderSuratModal(url);
  }

  // init (render awal)
  renderSuratBox(INIT_SURAT);

  // Upload via XHR (agar bisa progress)
  function uploadSurat(file){
    return new Promise((resolve, reject)=>{
      const fd = new FormData();
      fd.append('kode', KODE);
      fd.append('surat_tugas', file);
      <?php if (config_item('csrf_protection')): ?>
        fd.append('<?= $CI->security->get_csrf_token_name() ?>', '<?= $CI->security->get_csrf_hash() ?>');
      <?php endif; ?>

      const xhr = new XMLHttpRequest();
      xhr.open('POST', '<?= site_url('admin_scan/upload_surat_tugas') ?>', true);
      xhr.responseType = 'json';

      xhr.upload.onprogress = (e)=>{ if (e.lengthComputable){ setProgress(Math.round((e.loaded/e.total)*100)); } };
      xhr.onload = ()=> {
        const j = xhr.response || {};
        if (xhr.status>=200 && xhr.status<300 && j.ok){
          resolve(j);
        } else {
          reject(j && j.msg ? j.msg : ('Gagal upload ('+xhr.status+')'));
        }
      };
      xhr.onerror = ()=> reject('Jaringan bermasalah.');
      xhr.send(fd);
    });
  }

  function validateFile(f){
    if (!f) return 'Pilih berkas terlebih dahulu.';
    const okType = /^(application\/pdf|image\/jpeg|image\/png)$/i.test(f.type) || /\.(pdf|jpe?g|png)$/i.test(f.name);
    if (!okType) return 'Format harus PDF/JPG/PNG.';
    if (f.size > 2*1024*1024) return 'Ukuran melebihi 2MB.';
    return '';
  }

  btnUpload && btnUpload.addEventListener('click', async ()=>{
    const f = fileInput?.files?.[0];
    const err = validateFile(f);
    if (err){ setMsgSurat(err, false); return; }

    btnUpload.disabled = true; showProgress(true); setMsgSurat('Mengunggah...', false);
    try{
      const res = await uploadSurat(f);
      const url = res.url || '';
      setMsgSurat('Surat tugas tersimpan.', true);
      renderSuratBox(url);
      // bersihkan input
      if (fileInput) fileInput.value='';
    }catch(e){
      setMsgSurat(String(e||'Upload gagal'), false);
    }finally{
      setProgress(100);
      setTimeout(()=> showProgress(false), 600);
      btnUpload.disabled = false;
    }
  });

  // Loader saat klik "Unduh"
  document.addEventListener('click', function(e){
    const a = e.target.closest('#suratTugasUnduh, #lampiranFotoDownload');
    if (!a) return;
    a.classList.add('btn-loading'); setTimeout(()=> a.classList.remove('btn-loading'), 2000);
  });
})();
</script>

<script>
(function(){
  const fileInput = document.getElementById('fileSuratTugas');
  const dropZone  = document.getElementById('suDropZone');
  const pill      = document.getElementById('suFilePill');
  const fName     = document.getElementById('suFileName');
  const fMeta     = document.getElementById('suFileMeta');
  const btnClear  = document.getElementById('suClear');

  if (!fileInput || !dropZone) return;

  function fmtBytes(b){
    if (!b && b!==0) return '';
    if (b < 1024) return b+' B';
    if (b < 1024*1024) return (b/1024).toFixed(1)+' KB';
    return (b/1024/1024).toFixed(2)+' MB';
  }
  function extOf(name){
    const m = String(name||'').match(/\.([a-z0-9]+)$/i); return m? m[1].toUpperCase() : '';
  }
  function showFileInfo(file){
    if (!file){ pill.classList.add('d-none'); return; }
    fName.textContent = file.name;
    const type = (file.type || extOf(file.name) || '').replace('image/','IMG ').replace('application/','');
    fMeta.textContent = (type ? type+' • ' : '') + fmtBytes(file.size);
    pill.classList.remove('d-none');
  }
  function clearFile(){
    // kosongkan input (dan pill)
    try{
      const dt = new DataTransfer();
      fileInput.files = dt.files;
    }catch(_){ fileInput.value = ''; }
    pill.classList.add('d-none');
  }

  // pilih via dialog
  fileInput.addEventListener('change', ()=> showFileInfo(fileInput.files[0]));

  // drag & drop
  ['dragenter','dragover'].forEach(ev=>{
    dropZone.addEventListener(ev, e=>{ e.preventDefault(); e.stopPropagation(); dropZone.classList.add('is-drag'); });
  });
  ['dragleave','drop'].forEach(ev=>{
    dropZone.addEventListener(ev, e=>{ e.preventDefault(); e.stopPropagation(); dropZone.classList.remove('is-drag'); });
  });
  dropZone.addEventListener('drop', e=>{
    const file = (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) || null;
    if (!file) return;
    // masukkan ke input agar proses upload JS yang ada tetap bekerja
    try{
      const dt = new DataTransfer(); dt.items.add(file); fileInput.files = dt.files;
    }catch(_){ /* fallback: tidak fatal */ }
    showFileInfo(file);
  });

  // tombol clear
  btnClear && btnClear.addEventListener('click', clearFile);
})();
</script>
