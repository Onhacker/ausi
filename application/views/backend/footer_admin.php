
<?php $uri = $this->uri->uri_string(); ?>
<?php
// --- ACTIVE HELPER ---
$uri = trim($this->uri->uri_string(), '/'); // normalize

if (!function_exists('uri_is')) {
  function uri_is(string $uri, $patterns): bool {
    foreach ((array)$patterns as $p) {
      $p = trim((string)$p, '/');
      if ($p === '') { if ($uri === '') return true; continue; }
      // wildcard '*' di akhir = prefix match
      if (substr($p, -1) === '*') {
        $prefix = rtrim($p, '*');
        if ($uri === $prefix || strpos($uri, $prefix) === 0) return true;
      } elseif ($uri === $p) {
        return true;
      }
    }
    return false;
  }
}

if (!function_exists('nav_class')) {
  function nav_class(string $uri, $patterns, string $on='text-active', string $off='text-dark'): string {
    return uri_is($uri, $patterns) ? $on : $off;
  }
}
?>

<style>
  .navbar-bottom {
    height: 65px;
    border-top: 1px solid #dee2e6;
    background-color: #fff;
    box-shadow: 0 -1px 5px rgba(0,0,0,0.05);
    z-index: 1030;
  }

  .navbar-bottom a {
    font-size: 12px;
    color: #666;
    text-decoration: none;
  }

  .navbar-bottom i {
    font-size: 18px;
  }

  .center-button {
    position: absolute;
    top: -25px;
    left: 50%;
    transform: translateX(-50%);
    /*background-image: linear-gradient(to right, #00c6ff 0%, #0072ff 100%) !important;*/
    background-image: linear-gradient(to right, #1e3c72 0%, #2a5298 100%) !important;

    width: 65px;
    height: 65px;
    border-radius: 50%;
    border: 1px solid #dee2e6;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    animation: footerAni 1s infinite;
    z-index: 10;
  }

  .center-button .icon-center {
    width: 30px;
    height: 30px;
    object-fit: contain;
  }

  .nav-item {
    flex: 1;
    text-align: center;
  }

  .space-left, .space-right {
    flex: 0.5;
  }

  @keyframes footerAni {
    0% {
      transform: scale(1,1) translateX(-50%)
    }

    50% {
      transform: scale(1.05,1.05) translateX(-48%)
    }
  }
  .navbar-bottom .nav-item a:hover {
    color: #4a81d4 !important; 
  }

  .navbar-bottom .nav-item a:hover i,
  .navbar-bottom .nav-item a:hover span {
    color: #4a81d4 !important;
  }
  .text-active {
    color: #4a81d4 !important
  }
</style>

<nav class="navbar fixed-bottom navbar-light bg-white shadow-sm d-lg-none navbar-bottom px-0">
  <div class="w-100 d-flex justify-content-between text-center position-relative mx-0 px-0">

    <div class="nav-item">
      <a href="<?= base_url() ?>" class="<?= nav_class($uri, ['', 'home']) ?>">
        <i class="fas fa-home d-block mb-1"></i>
        <span class="small">Beranda</span>
      </a>
    </div>

    <div class="nav-item">
      <a href="<?= base_url('hal/jadwal') ?>" class="<?= nav_class($uri, ['hal/jadwal*']) ?>">
        <i class="far fa-calendar-alt d-block mb-1"></i>
        <span class="small">Jadwal</span>
      </a>
    </div>

    <div class="space-left"></div>

    <?php $web = $this->om->web_me();
    if (!function_exists('user_can_mod')) $this->load->helper('menu');

    $can_scan   = function_exists('user_can_mod') ? user_can_mod(['admin_scan','scan','checkin/checkout']) : false;
    $target_url = $can_scan ? base_url('admin_scan') : base_url('booking');
    $center_on  = uri_is($uri, $can_scan ? ['admin_scan'] : ['booking']);
    ?>
    <a href="<?= $target_url ?>"
       class="center-button <?= $center_on ? 'text-white' : '' ?>"
       style="text-align:center; <?= $center_on ? 'background-image:none;background-color:#2a5298;' : '' ?>"
       aria-label="<?= $can_scan ? 'Scan (Check-in/Out)' : 'Booking' ?>">
      <div>
        <img src="<?= base_url('assets/images/') . $web->gambar ?>"
             alt="<?= $can_scan ? 'Scan' : 'Booking' ?>"
             style="width:50px;height:50px;object-fit:contain;margin-top:0;">
      </div>
    </a>

    <div class="space-right"></div>

    <div class="nav-item">
      <a href="<?= base_url('hal/struktur') ?>" class="<?= nav_class($uri, ['hal/struktur*']) ?>">
        <i class="fas fa-sitemap d-block mb-1"></i>
        <span class="small">Struktur</span>
      </a>
    </div>

    <?php
      // Semua halaman yang “mewakili” Menu → bikin aktif ikon Menu
      $menu_patterns = [
        'admin_permohonan','admin_profil/detail_profil','booking',
        'admin_dashboard/monitor','admin_scan','admin_user','hal/kontak',
        'admin_setting_web*','admin_unit_tujuan*','admin_unit_lain*','Admin_pengumuman*'
      ];
    ?>
    <div class="nav-item">
      <a href="#" class="<?= nav_class($uri, $menu_patterns) ?>" data-toggle="modal" data-target="#kontakModal">
        <i class="fas fa-bars d-block mb-1"></i>
        <span class="small">Menu</span>
      </a>
    </div>

  </div>
</nav>

<style type="text/css">

  .modal-dialog.modal-bottom {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    width: 100%;
    transform: translateY(100%); 
    transition: transform 0.3s ease-out; 
    margin: 0;
  }

  .modal.fade.show .modal-dialog.modal-bottom {
    transform: translateY(0); 
  }
  .modal-dialog-full {
    max-width: 100%;
  }

  .modal-content-full {
    height: 100%;
    border-radius: 0;
  }


  .modal-content {
    border-radius: 0; 
    width: 100%; 
    margin: 0;
  }

</style>
<!-- Modal Menu (scrollable + icon rapi) -->
<div class="modal" id="kontakModal" tabindex="-1" aria-labelledby="menumoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-bottom fadeInUp animated modal-dialog-full" style="animation-duration: .5s;">
    <div class="modal-content">
      <div class="modal-header bg-blue text-white">
        <h5 class="modal-title d-flex align-items-center text-white" id="menumoLabel">
          <i class="fas fa-concierge-bell mr-2"></i> Menu
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body p-0">
        <div class="menu-list">

  <!-- Profil (public) -->
  <a href="<?= base_url('admin_profil/detail_profil') ?>" class="menu-item">
    <i class="mdi mdi-account-circle-outline"></i><span>Profil</span>
  </a>


  <!-- Produk -->
  <?php if (user_can_mod(['admin_produk'])): ?>
    <a id="quick-produk-link" href="<?= site_url('admin_produk') ?>" class="menu-item">
      <i class="mdi mdi-package-variant-closed"></i><span>Produk</span>
    </a>
  <?php endif; ?>

  <!-- POS Caffe -->
  <?php if (user_can_mod(['admin_pos'])): ?>
    <a id="quick-pos-link" href="<?= site_url('admin_pos') ?>" class="menu-item">
      <i class="mdi mdi-coffee-outline"></i><span>POS Caffe</span>
    </a>
  <?php endif; ?>

  <!-- POS Billiard -->
  <?php if (user_can_mod(['admin_billiard'])): ?>
    <a id="quick-billiard-link" href="<?= site_url('admin_billiard') ?>" class="menu-item">
      <i class="mdi mdi-billiards"></i><span>POS Billiard</span>
    </a>
  <?php endif; ?>

  <!-- Pengeluaran -->
  <?php if (user_can_mod(['admin_pengeluaran'])): ?>
    <a id="quick-pengeluaran-link" href="<?= site_url('admin_pengeluaran') ?>" class="menu-item">
      <i class="mdi mdi-cash-minus"></i><span>Pengeluaran</span>
    </a>
  <?php endif; ?>

  <!-- Riwayat Caffe -->
  <?php if (user_can_mod(['admin_pos_riwayat'])): ?>
    <a id="quick-riwayat-caffe-link" href="<?= site_url('admin_pos_riwayat') ?>" class="menu-item">
      <i class="mdi mdi-history"></i><span>Riwayat Caffe</span>
    </a>
  <?php endif; ?>

  <!-- Riwayat Billiard -->
  <?php if (user_can_mod(['admin_riwayat_billiard'])): ?>
    <a id="quick-riwayat-billiard-link" href="<?= site_url('admin_riwayat_billiard') ?>" class="menu-item">
      <i class="mdi mdi-history"></i><span>Riwayat Billiard</span>
    </a>
  <?php endif; ?>

  <!-- Laporan -->
  <?php if (user_can_mod(['admin_laporan','admin_laporan/index'])): ?>
    <a id="quick-laporan-link" href="<?= site_url('admin_laporan') ?>" class="menu-item">
      <i class="mdi mdi-file-chart"></i><span>Laporan</span>
    </a>
  <?php endif; ?>

  <!-- Manajemen User -->
  <?php if (user_can_mod(['admin_user'])): ?>
    <a id="quick-user-link" href="<?= site_url('admin_user') ?>" class="menu-item">
      <i class="mdi mdi-account-cog"></i><span>Manajemen User</span>
    </a>
  <?php endif; ?>

  <!-- Pengaturan Sistem -->
  <?php if (user_can_mod(['admin_setting_web'])): ?>
    <a id="quick-setting-link" href="<?= site_url('admin_setting_web') ?>" class="menu-item">
      <i class="mdi mdi-cog-outline"></i><span>Pengaturan Sistem</span>
    </a>
  <?php endif; ?>

  <!-- Kategori Produk -->
  <?php if (user_can_mod(['admin_kategori_produk'])): ?>
    <a id="quick-kategori-link" href="<?= site_url('admin_kategori_produk') ?>" class="menu-item">
      <i class="mdi mdi-tag-multiple-outline"></i><span>Kategori Produk</span>
    </a>
  <?php endif; ?>


  <!-- Unit Lain -->
  <?php if (user_can_mod(['admin_unit_lain'])): ?>
    <a id="quick-unit-lain-link" href="<?= site_url('admin_unit_lain') ?>" class="menu-item">
      <i class="mdi mdi-domain-plus"></i><span>Unit Lain</span>
    </a>
  <?php endif; ?>

 

  <!-- Pengumuman -->
  <?php if (user_can_mod(['admin_pengumuman'])): ?>
    <a id="quick-pengumuman-link" href="<?= site_url('admin_pengumuman') ?>" class="menu-item">
      <i class="mdi mdi-bullhorn-outline"></i><span>Pengumuman</span>
    </a>
  <?php endif; ?>

  <!-- Meja -->
  <?php if (user_can_mod(['admin_meja'])): ?>
    <a id="quick-meja-link" href="<?= site_url('admin_meja') ?>" class="menu-item">
      <i class="mdi mdi-table-chair"></i><span>Meja</span>
    </a>
  <?php endif; ?>

</div>

      </div>
    </div>
  </div>
</div>

<!-- Style khusus modal menu -->
<style>
  /*.bg-blue{ background:#1f6feb !important; }*/

  #kontakModal .menu-list{
    max-height: 70vh;        /* bikin body modal bisa discroll */
    overflow-y: auto;
    padding: 12px;
  }
  #kontakModal .menu-item{
    display:flex;
    align-items:center;
    gap:10px;
    padding:12px 14px;
    margin:10px 12px;
    border-radius:12px;
    background:#c7d5ff;
    font-weight:600;
    color:#111 !important;
    text-decoration:none !important;
    transition: background .2s ease, transform .1s ease;
  }
  #kontakModal .menu-item:hover{ background:#b9c9ff; }
  #kontakModal .menu-item:active{ transform: translateY(1px); }
  #kontakModal .menu-item i{ width:22px; text-align:center; }
  /* optional: rapihin scrollbar */
  #kontakModal .menu-list::-webkit-scrollbar{ width:8px; }
  #kontakModal .menu-list::-webkit-scrollbar-thumb{ background:#1D41D1; border-radius:8px; }
</style>


<script>
  document.addEventListener("DOMContentLoaded", function () {

  // Peta id menu (DOM <a>) <-> slug izin/module
  const QUICK = {
    // ===== Master Data / Operasional =====
    admin_produk:          { a: document.getElementById("quick-produk-link") },
    admin_kategori_produk: { a: document.getElementById("quick-kategori-link") },
    admin_meja:            { a: document.getElementById("quick-meja-link") },

    // ===== POS / Transaksi =====
    admin_pos:             { a: document.getElementById("quick-pos-link") },
    admin_billiard:        { a: document.getElementById("quick-billiard-link") },
    admin_pengeluaran:     { a: document.getElementById("quick-pengeluaran-link") },

    // ===== Riwayat & Laporan =====
    admin_pos_riwayat:       { a: document.getElementById("quick-riwayat-caffe-link") },
    admin_riwayat_billiard:  { a: document.getElementById("quick-riwayat-billiard-link") },
    admin_laporan:           { a: document.getElementById("quick-laporan-link") },

    // ===== Manajemen & Pengaturan =====
    admin_user:          { a: document.getElementById("quick-user-link") },
    admin_setting_web:   { a: document.getElementById("quick-setting-link") },
    admin_unit_lain:     { a: document.getElementById("quick-unit-lain-link") },
    admin_pengumuman:    { a: document.getElementById("quick-pengumuman-link") }

    // (opsional, kalau nanti mau ditambah ke modal)
    // admin_unit_tujuan:   { a: document.getElementById("quick-unit-tujuan-link") },
    // admin_instansi_ref:  { a: document.getElementById("quick-instansi-ref-link") },
    // admin_permohonan:    { a: document.getElementById("quick-data-link") },
    // admin_scan:          { a: document.getElementById("quick-scan-link") },
    // 'admin_dashboard': { a: document.getElementById("quick-dashboard-link") },
    // 'admin_dashboard/monitor': { a: document.getElementById("quick-dashboard-monitor-link") },
  };

  // helper untuk show/hide 1 item
  function setVis(moduleId, show){
    const q = QUICK[moduleId];
    if (!q || !q.a) return; // kalau gak ada tombolnya di modal, skip aja
    q.a.style.display = show ? "" : "none";
  }

  // fetch izin terbaru dari server
  fetch("<?= site_url('api/get_menu_mobile') ?>?v=1", {
    credentials: 'same-origin', // penting biar session kebawa
    cache: 'no-store'
  })
  .then(async (r) => {
    // dukung ETag 304 → artinya "tidak berubah"
    if (r.status === 304) {
      return null;
    }
    if (!r.ok) throw new Error("HTTP " + r.status);
    const etag = r.headers.get("ETag"); // kalau mau disimpan di localStorage, bisa
    const data = await r.json();
    return { etag, data };
  })
  .then((res) => {
    if (!res || !res.data || !res.data.success) return;

    const actions = res.data.actions || [];
    const allowed = new Set(actions.map(a => a.id));

    // 1. Sync visibility semua menu di QUICK
    Object.keys(QUICK).forEach(id => {
      setVis(id, allowed.has(id));
    });

    // 2. (opsional) Sync URL kalau server mau override link
    actions.forEach(a => {
      const q = QUICK[a.id];
      if (q && q.a && a.url) {
        q.a.href = a.url;
      }
    });
  })
  .catch((err) => {
    console.warn("get_menu_mobile failed:", err);
    // kalau gagal fetch, kita biarkan tampilan server-side aja
  });

});

</script>


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

