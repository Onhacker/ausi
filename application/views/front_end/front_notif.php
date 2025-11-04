<?php if ($this->session->userdata("admin_username") == "admin" or $this->session->userdata("admin_username") == "bar" or $this->session->userdata("admin_username") == "kitcher") {?>



<style>
  .g-center-toast { padding: 12px 16px; border-radius: 12px; }
  .g-center-toast .swal2-title { font-size: 14px; font-weight: 600; }
</style>

 <script>
/* ==========================================================
 * Ping + Notif + Auto Reload (POS & Billiard) — v3.3
 * - Bunyi HANYA saat penambahan data (total↑ atau max_id↑)
 * - Auto unlock audio pada gesture pertama (klik/tap/keydown)
 * - Banner fallback + WebAudio beep jika <audio> diblokir
 * - Cooldown 5s/kanal agar tidak spam
 * - Default suara ON (kecuali user pernah OFF)
 * ========================================================== */
(function(){
  if (window.__POS_BILL_PINGER_V33__) return;
  window.__POS_BILL_PINGER_V33__ = true;

  /* ===== Endpoint ===== */
  const PING_POS_URL = "<?= site_url('admin_pos/ping'); ?>";
  const PING_BIL_URL = "<?= site_url('admin_billiard/ping'); ?>";

  /* ===== Sumber Suara ===== */
  if (typeof window.SOUND_POS_URL === 'undefined')
    var SOUND_POS_URL = "<?= base_url('assets/sound/notif.wav'); ?>";
  if (typeof window.SOUND_BIL_URL === 'undefined')
    var SOUND_BIL_URL = "<?= base_url('assets/sound/notif_b.wav'); ?>";

  const POS_SOURCES = [SOUND_POS_URL, SOUND_POS_URL.replace(/\.wav(\?.*)?$/i, '.mp3$1')];
  const BIL_SOURCES = [SOUND_BIL_URL, SOUND_BIL_URL.replace(/\.wav(\?.*)?$/i, '.mp3$1')];

  /* ===== Util kecil ===== */
  const LOG = (msg, ...rest) => (window.PING_DEBUG ? console.log('[PING]', msg, ...rest) : void 0);
  const now = () => Date.now();
  const isNum = (v)=> Number.isFinite(v);

  /* ===== Audio elements + fallback ===== */
  function createAudioWithFallback(urls){
    const a = new Audio();
    a.preload = 'auto';
    a.muted = false;
    a.volume = 1.0;
    a.crossOrigin = 'anonymous';
    let i = 0;
    function setSrc(idx){ a.src = urls[idx]; try{ a.load(); }catch(_){ } }
    a.addEventListener('error', function(){
      if (i + 1 < urls.length){ i++; setSrc(i); }
    });
    setSrc(i);
    return a;
  }
  const audioPos = window.__audioPos__ || createAudioWithFallback(POS_SOURCES);
  const audioBil = window.__audioBil__ || createAudioWithFallback(BIL_SOURCES);
  window.__audioPos__ = audioPos;
  window.__audioBil__ = audioBil;

  // WebAudio fallback (beep)
  let audioCtx = null;
  function ensureAudioCtx(){
    if (audioCtx) return audioCtx;
    const Ctx = window.AudioContext || window.webkitAudioContext;
    if (!Ctx) return null;
    audioCtx = new Ctx();
    return audioCtx;
  }
  async function webAudioBeep(durationMs=250, freq=880){
    const ctx = ensureAudioCtx();
    if (!ctx) return;
    if (ctx.state === 'suspended') {
      try { await ctx.resume(); } catch(_){}
    }
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.type = 'sine';
    osc.frequency.setValueAtTime(freq, ctx.currentTime);
    gain.gain.setValueAtTime(0.001, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.2, ctx.currentTime + 0.02);
    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + durationMs/1000);
    osc.connect(gain); gain.connect(ctx.destination);
    osc.start();
    osc.stop(ctx.currentTime + durationMs/1000 + 0.02);
  }

  /* ===== Sound state ===== */
  const LS_KEY = 'admin_sound_enabled';
  let soundEnabled = (localStorage.getItem(LS_KEY) !== '0'); // default ON
  let soundUnlocked = !!window.__soundUnlocked__; // true setelah user gesture/klik
  /* ===== Inline sound button (tampil hanya saat belum aktif) ===== */
function isAudioActive(){ return !!soundEnabled && !!soundUnlocked; }

function updateSoundButtonInline(){
  const btn = document.getElementById('btn-enable-sound-inline');
  if (!btn) return;
  btn.style.display = isAudioActive() ? 'none' : '';
}

function bindInlineSoundButton(){
  const btn = document.getElementById('btn-enable-sound-inline');
  if (!btn) return;
  btn.addEventListener('click', async ()=>{
    await tryUnlockAudio();              // minta izin audio
    soundEnabled = true;                 // paksa ON
    localStorage.setItem(LS_KEY,'1');
    const ok = await playDirect('pos');  // bunyikan tes singkat
    if (ok) {
      updateSoundButtonInline();         // sembunyikan tombol bila sukses
    } else {
      // kalau masih gagal (kebijakan browser), tombol tetap tampil
      updateSoundButtonInline();
    }
  });
}

  /* === Kontrol visibilitas tombol/bannner "Aktifkan suara" === */
  function isAudioActive(){ return !!soundEnabled && !!soundUnlocked; }
  function updateUnlockBannerVisibility(){
    if (isAudioActive()) hideUnlockBanner();
    else showUnlockBanner();
  }

  async function tryUnlockAudio(){
    if (soundUnlocked) return true;
    // trik: play muted lalu pause untuk “mendaftarkan” gesture
    try {
      audioPos.muted = true; await audioPos.play().catch(()=>{}); audioPos.pause(); audioPos.muted = false;
      audioBil.muted = true; await audioBil.play().catch(()=>{}); audioBil.pause(); audioBil.muted = false;
    } catch(_){}
    const ctx = ensureAudioCtx();
    if (ctx && ctx.state !== 'running'){
      try { await ctx.resume(); } catch(_){}
    }
    soundUnlocked = true;
    window.__soundUnlocked__ = true;
    LOG('Audio unlocked');
    return true;
  }

  // 🔓 Auto-unlock pada gesture pertama user di mana saja
  function autoUnlockOnFirstGesture(){
    if (window.__autoUnlockBound__) return;
    window.__autoUnlockBound__ = true;
    const events = ['pointerdown','click','touchstart','keydown'];
    const handler = async ()=>{
      await tryUnlockAudio();
      if (soundEnabled) localStorage.setItem(LS_KEY, '1');
      hideUnlockBanner();
      updateUnlockBannerVisibility();

      // lepas listener biar efisien
      events.forEach(ev => window.removeEventListener(ev, handler, true));
    };
    events.forEach(ev => window.addEventListener(ev, handler, { once:true, capture:true, passive:true }));
  }

  /* ===== Banner “Aktifkan suara” (fallback) ===== */
  function ensureUnlockBanner(){
    // if (document.getElementById('sound-unlock-banner')) return;
    // const div = document.createElement('div');
    // div.id = 'sound-unlock-banner';
    // div.style.cssText = 'position:fixed;right:12px;bottom:12px;z-index:99999;background:#111827;color:#fff;padding:10px 12px;border-radius:10px;box-shadow:0 6px 20px rgba(0,0,0,.25);display:none';
    // div.innerHTML = '<span style="margin-right:8px">🔔 Aktifkan suara notifikasi</span><button id="sound-unlock-btn" class="btn btn-sm btn-success" style="padding:3px 10px;border-radius:8px">Aktifkan</button>';
    // document.body.appendChild(div);
    // document.getElementById('sound-unlock-btn').addEventListener('click', async ()=>{
    //   await tryUnlockAudio();
    //   soundEnabled = true;
    //   localStorage.setItem(LS_KEY,'1');
    //   hideUnlockBanner();
    //   // bunyikan test sebagai konfirmasi (opsional)
    //   // playDirect('pos').catch(()=>{});
    // });
  }
  function showUnlockBanner(){
    // ensureUnlockBanner();
    // const el = document.getElementById('sound-unlock-banner');
    // if (el) el.style.display = 'block';
  }
  function hideUnlockBanner(){
    // const el = document.getElementById('sound-unlock-banner');
    // if (el) el.style.display = 'none';
  }

  // Integrasi tombol yang sudah ada (opsional)
  function bindSoundButton(){
    const btn = document.getElementById('btn-enable-sound');
    if (!btn) return;
    const mark = ()=>{
      if (soundEnabled){
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-success');
        btn.innerHTML = `<span class="btn-label"><i class="fe-volume-2"></i></span>Suara ON`;
      }else{
        btn.classList.add('btn-outline-secondary');
        btn.classList.remove('btn-success');
        btn.innerHTML = `<span class="btn-label"><i class="fe-volume-x"></i></span>Suara OFF`;
      }
    };
    btn.addEventListener('click', async ()=>{
      await tryUnlockAudio();
      soundEnabled = !soundEnabled;
      localStorage.setItem(LS_KEY, soundEnabled ? '1' : '0');
      if (soundEnabled) { playDirect('pos').catch(()=>{}); hideUnlockBanner(); }
      mark();
    });
    mark();
  }

  if (document.readyState === 'complete' || document.readyState === 'interactive') {
  bindSoundButton();           // boleh tetap ada (opsional)
  bindInlineSoundButton();     // <-- penting
  autoUnlockOnFirstGesture();  // biar kebuka juga saat ada gesture umum
  updateSoundButtonInline();   // set visibilitas awal
} else {
  document.addEventListener('DOMContentLoaded', ()=>{
    bindSoundButton();
    bindInlineSoundButton();
    autoUnlockOnFirstGesture();
    updateSoundButtonInline();
  }, { once:true });
}



  /* ===== Play dengan fallback ===== */
  async function playDirect(kind){ // tanpa cooldown/throttle
    const el = (kind === 'pos') ? audioPos : audioBil;
    try {
      el.currentTime = 0; await el.play();
      return true;
    } catch(err) {
      LOG('HTMLAudio play blocked:', err && err.name);
      // coba WebAudio beep
      await webAudioBeep(250, kind==='pos'?880:660);
      // kalau masih gagal, munculkan banner
      // showUnlockBanner();
      // update visibilitas tombol sesuai status
updateSoundButtonInline();  // biar tombol inline tetap terlihat kalau belum aktif


      return false;
    }
  }

 const SOUND_COOLDOWN = 5000; // ms
let lastSoundAt = { pos: 0, bil: 0 };

async function playSound(kind){
  if (!soundEnabled) return;
  const t = now();
  if (t - lastSoundAt[kind] < SOUND_COOLDOWN) return;
  lastSoundAt[kind] = t;

  // update tombol inline sesuai status
  updateSoundButtonInline();

  await tryUnlockAudio();
  const ok = await playDirect(kind);

  if (ok && isAudioActive()){
    updateSoundButtonInline(); // sembunyikan jika sudah aktif
  }

  const label = (kind === 'pos') ? 'POS' : 'Billiard';
  showToast(`${label}: ada yang baru tuh`, 'success', { sticky: true }); // center + sticky (punyamu)
}




  /* ===== Ping / deteksi perubahan ===== */
  const last = {
    pos: { total: null, max_id: null, last_ts: null },
    bil: { total: null, max_id: null, last_ts: null }
  };
  const BASE_INTERVAL = 10000, HIDDEN_INTERVAL = 20000;
  let errorStreak = 0, ticking = false;

  // ✅ Bunyi hanya saat penambahan
  function isAdded(oldSnap, snap){
    const tOld = Number(oldSnap.total), tNew = Number(snap.total);
    const idOld = Number(oldSnap.max_id), idNew = Number(snap.max_id);
    const totalUp = Number.isFinite(tOld) && Number.isFinite(tNew) && tNew > tOld;
    const idUp    = Number.isFinite(idOld) && Number.isFinite(idNew) && idNew > idOld;
    return totalUp || idUp;
  }

  // 🔄 Reload untuk perubahan apa pun (naik/turun/ubah timestamp)
  function isChanged(oldSnap, snap){
    const tOld = Number(oldSnap.total), tNew = Number(snap.total);
    const idOld = Number(oldSnap.max_id), idNew = Number(snap.max_id);
    if (Number.isFinite(tOld) && Number.isFinite(tNew) && tNew !== tOld) return true;
    if (Number.isFinite(idOld) && Number.isFinite(idNew) && idNew !== idOld) return true;
    if (oldSnap.last_ts && snap.last_ts && String(snap.last_ts) !== String(oldSnap.last_ts)) return true;
    return false;
  }

  async function safeFetch(url){
    const r = await fetch(url, { cache:'no-store', credentials:'same-origin' });
    if (!r.ok) throw new Error('HTTP '+r.status);
    return r.json();
  }

  function triggerReload(kind){
    if (kind === 'pos'){
      if (typeof window.reload_table === 'function') window.reload_table(kind+'-ping');
    } else {
      if (typeof window.reload_billiard_table === 'function') window.reload_billiard_table(kind+'-ping');
    }
  }

  async function handleChannel(kind, url, key){
    try{
      const j = await safeFetch(url);
      if (j && j.success){
        const snap = { total:Number(j.total||0), max_id:Number(j.max_id||0), last_ts:j.last_ts?String(j.last_ts):null };
        if (last[key].total === null) {
          last[key] = snap; LOG(key.toUpperCase(),'baseline', snap);
        } else {
          const added   = isAdded(last[key], snap);
          const changed = isChanged(last[key], snap);
          LOG(key.toUpperCase(), {added, changed, prev:last[key], next:snap});
          last[key] = snap;                   // update snapshot dulu
          if (changed) triggerReload(key);    // reload untuk perubahan apa pun
          if (added)   playSound(key);        // 🔔 bunyi hanya saat penambahan
        }
      }
    }catch(e){
      LOG(key.toUpperCase(),'ping error', e && e.message);
      throw e;
    }
  }

  async function tick(){
    if (ticking) return;
    ticking = true;

    const visible = !document.hidden;
    const interval = (visible ? BASE_INTERVAL : HIDDEN_INTERVAL) * Math.min(4, (1 + errorStreak*0.5));

    try{
      await Promise.all([
        handleChannel('pos', PING_POS_URL, 'pos'),
        handleChannel('bil', PING_BIL_URL, 'bil')
      ]);
      errorStreak = 0;
    }catch(_){
      errorStreak = Math.min(6, errorStreak + 1);
    }finally{
      ticking = false;
      setTimeout(tick, interval);
    }
  }

  // start
  tick();
  document.addEventListener('visibilitychange', function(){
    if (!document.hidden) setTimeout(tick, 200);
  });

  /* ===== Helper test manual (ketik di console) ===== */
  window.test_pos_sound = ()=> playDirect('pos');
  window.test_bil_sound = ()=> playDirect('bil');
  window.PING_DEBUG = window.PING_DEBUG || false; // set true utk lihat log

})();

  /* ===== Toast kanan-atas (Swal kalau ada, fallback vanilla) ===== */
  /* ===== Toast kanan-atas (Swal kalau ada, fallback vanilla) ===== */
/* ===== Toast TENGAH layar (Swal kalau ada, fallback vanilla) ===== */
function showToast(text, icon = 'success', opts = {}){
  const sticky   = !!opts.sticky;                       // true = tidak auto close
  const duration = Number.isFinite(opts.duration) ? opts.duration : 2500;

  // normalisasi ikon agar cocok dengan SweetAlert2
  const iconMap = { danger:'error', warn:'warning' };
  const swalIcon = iconMap[icon] || icon;

  if (window.Swal){ // gunakan modal kecil di tengah (toast:false) agar benar-benar center
    Swal.fire({
      toast: false,
      icon: swalIcon,
      title: text,
      position: 'center',
      width: 360,
      showConfirmButton: false,
      showCloseButton: sticky,
      timer: sticky ? undefined : duration,
      timerProgressBar: !sticky,
      allowOutsideClick: sticky ? false : true,
      backdrop: false,                 // kalau ingin ada gelap transparan, ubah ke true
      customClass: { popup: 'g-center-toast' }
    });
    return;
  }

  // ===== Fallback vanilla (center overlay) =====
  const id = 'toast-center-overlay';
  let c = document.getElementById(id);
  if (!c){
    c = document.createElement('div');
    c.id = id;
    c.style.cssText = 'position:fixed;inset:0;z-index:100000;display:flex;align-items:center;justify-content:center;pointer-events:none';
    document.body.appendChild(c);
  }

  const card = document.createElement('div');
  card.setAttribute('role','status');
  card.style.cssText = 'pointer-events:auto;position:relative;background:#111827;color:#fff;padding:12px 44px 12px 14px;border-radius:12px;box-shadow:0 8px 28px rgba(0,0,0,.35);opacity:0;transform:scale(.96);transition:.15s';
  card.textContent = text;

  // tombol close
  const btn = document.createElement('button');
  btn.type = 'button';
  btn.setAttribute('aria-label','Close');
  btn.innerHTML = '&times;';
  btn.style.cssText = 'position:absolute;top:6px;right:10px;background:transparent;border:none;color:#fff;font-size:18px;line-height:1;cursor:pointer;opacity:.85';
  btn.addEventListener('click', ()=> card.remove());
  card.appendChild(btn);

  c.appendChild(card);
  requestAnimationFrame(()=>{
    card.style.opacity='1';
    card.style.transform='scale(1)';
  });

  if (!sticky){
    setTimeout(()=>{
      card.style.opacity='0';
      card.style.transform='scale(.96)';
      setTimeout(()=> card.remove(), 150);
    }, duration);
  }
}
</script>

<?php } ?>