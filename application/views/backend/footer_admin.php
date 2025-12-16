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
    /*height: 65px;*/
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

    <?php $web = $this->om->web_me();
    if (!function_exists('user_can_mod')) $this->load->helper('menu');

    $can_scan   = function_exists('user_can_mod') ? user_can_mod(['admin_pos','scan','checkin/checkout']) : false;
    $target_url = $can_scan ? base_url('admin_pos') : base_url('booking');
    $center_on  = uri_is($uri, $can_scan ? ['admin_pos'] : ['booking']);
    ?>
  


    <?php
      // Semua halaman yang “mewakili” Menu → bikin aktif ikon Menu
      $menu_patterns = [
        'admin_permohonan','admin_profil/detail_profil','booking',
        'admin_dashboard/monitor','admin_pos','admin_user','hal/kontak',
        'admin_setting_web*','admin_unit_tujuan*','admin_unit_lain*','Admin_pengumuman*',
        'admin_voucher_cafe*','admin_voucher_kursi_pijat*','admin_ps*','admin_voucher_ps*',
        'admin_monitor*', // NEW: Monitor TV Billiard
        'admin_voucher_billiard*', // <-- ADD INI
      ];


    ?>
    <div class="nav-item">
      <a href="#" class="<?= nav_class($uri, $menu_patterns) ?>" data-toggle="modal" data-target="#kontakModal">
        <i class="fas fa-bars d-block mb-1"></i>
        <span class="small"></span>
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
        <style>
  /* biar emoji rapi sejajar teks */
  .menu-item .emoji{
    font-size: 1.25rem;
    width: 1.6em;
    display: inline-block;
    text-align: center;
    margin-right: 8px;
    transform: translateY(-1px);
  }
</style>

<div class="menu-list">

  <?php
    // helper visibilitas grup
    $showCaffe     =
    user_can_mod(['admin_pos']) ||
    user_can_mod(['admin_voucher_cafe']) ||
    user_can_mod(['admin_pos_riwayat']) ||
    user_can_mod(['admin_meja']) ||
    user_can_mod(['admin_poin']); // <- biar grup Caffe muncul kalau cuma punya akses poin

    $showBilliard  =
    user_can_mod(['admin_billiard']) ||
    user_can_mod(['admin_riwayat_billiard']) ||
    user_can_mod(['admin_meja_billiard']) ||
    user_can_mod(['admin_voucher_billiard']); // <-- ADD INI

    $showPijat     =
    user_can_mod(['admin_kursi_pijat']) ||
    user_can_mod(['admin_voucher_kursi_pijat']);

    $showPS        =
    user_can_mod(['admin_ps']) ||
    user_can_mod(['admin_voucher_ps']);

   $showKeuLap =
    user_can_mod(['admin_pengeluaran'])      ||
    user_can_mod(['admin_laporan'])         ||
    user_can_mod(['admin_laporan/index'])   ||
    user_can_mod(['admin_rating'])          ||
    user_can_mod(['admin_monitor']);  // NEW: kalau punya akses admin_monitor, grup ini muncul


    $showMaster    =
    user_can_mod(['admin_produk']) ||
    user_can_mod(['admin_kategori_produk']) ||
    user_can_mod(['admin_kurir']) ||
    user_can_mod(['admin_unit_lain']) ||
    user_can_mod(['admin_voucher_cafe']); // <-- tambah ini

    $showAdmin     = user_can_mod(['admin_user']) || user_can_mod(['admin_setting_web']) || user_can_mod(['admin_pengumuman']);
  ?>

  <!-- ========== Akun & Ringkasan ========== -->
  <div class="menu-group" role="group" aria-label="Akun & Ringkasan">
    <div class="menu-title">Akun &amp; Ringkasan</div>

    <!-- Profil (public) -->
    <a href="<?= base_url('admin_profil/detail_profil') ?>" class="menu-item">
      <span class="emoji" aria-hidden="true">👤</span><span>Profil</span>
    </a>

    <!-- Statistik -->
    
  </div>

  <!-- ========== Caffe ========== -->
  <?php if ($showCaffe): ?>
  <div class="menu-group" role="group" aria-label="Caffe">
    <div class="menu-title">Caffe</div>

    <?php if (user_can_mod(['admin_pos'])): ?>
      <a id="quick-pos-link" href="<?= site_url('admin_pos') ?>" class="menu-item">
        <span class="emoji" aria-hidden="true">☕️</span><span>POS Caffe</span>
      </a>
    <?php endif; ?>

    <?php if (user_can_mod(['admin_poin'])): ?>
      <a id="quick-poin-link" href="<?= site_url('admin_poin') ?>" class="menu-item">
        <span class="emoji" aria-hidden="true">⭐</span><span>Cek Poin</span>
      </a>
    <?php endif; ?>
    <?php if (user_can_mod(['admin_voucher_cafe'])): ?>
      <a id="quick-voucher-cafe-link" href="<?= site_url('admin_voucher_cafe') ?>" class="menu-item">
        <span class="emoji" aria-hidden="true">🎁</span><span>Voucher Cafe</span>
      </a>
    <?php endif; ?>
    <?php if (user_can_mod(['admin_pos_riwayat'])): ?>
      <a id="quick-riwayat-caffe-link" href="<?= site_url('admin_pos_riwayat') ?>" class="menu-item">
        <span class="emoji" aria-hidden="true">🧾</span><span>Riwayat Caffe</span>
      </a>
    <?php endif; ?>

    <?php if (user_can_mod(['admin_meja'])): ?>
      <a id="quick-meja-link" href="<?= site_url('admin_meja') ?>" class="menu-item">
        <span class="emoji" aria-hidden="true">🪑</span><span>Meja</span>
      </a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- ========== Billiard ========== -->
  <?php if ($showBilliard): ?>
  <div class="menu-group" role="group" aria-label="Billiard">
    <div class="menu-title">Billiard</div>

    <?php if (user_can_mod(['admin_billiard'])): ?>
      <a id="quick-billiard-link" href="<?= site_url('admin_billiard') ?>" class="menu-item">
        <span class="emoji" aria-hidden="true">🎱</span><span>POS Billiard</span>
      </a>
    <?php endif; ?>
    <?php if (user_can_mod(['admin_voucher_billiard'])): ?>
      <a id="quick-voucher-billiard-link" href="<?= site_url('admin_voucher_billiard') ?>" class="menu-item">
        <span class="emoji" aria-hidden="true">🎟️</span><span>Voucher Billiard</span>
      </a>
    <?php endif; ?>

    <?php if (user_can_mod(['admin_riwayat_billiard'])): ?>
      <a id="quick-riwayat-billiard-link" href="<?= site_url('admin_riwayat_billiard') ?>" class="menu-item">
        <span class="emoji" aria-hidden="true">🧾</span><span>Riwayat Billiard</span>
      </a>
    <?php endif; ?>

    <?php if (user_can_mod(['admin_meja_billiard'])): ?>
      <a id="quick-meja-billiard-link" href="<?= site_url('admin_meja_billiard') ?>" class="menu-item">
        <span class="emoji" aria-hidden="true">🎱</span><span>Meja Billiard</span>
      </a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- ========== Kursi Pijat ========== -->
   <!-- ========== Kursi Pijat ========== -->
  <?php if ($showPijat): ?>
  <div class="menu-group" role="group" aria-label="Kursi Pijat">
    <div class="menu-title">Kursi Pijat</div>

    <?php if (user_can_mod(['admin_kursi_pijat'])): ?>
      <a id="quick-kursi-pijat-link" href="<?= site_url('admin_kursi_pijat') ?>" class="menu-item">
        <span class="emoji" aria-hidden="true">💺</span><span>POS Kursi Pijat</span>
      </a>
    <?php endif; ?>

    <?php if (user_can_mod(['admin_voucher_kursi_pijat'])): ?>
      <a id="quick-voucher-kursi-pijat-link" href="<?= site_url('admin_voucher_kursi_pijat') ?>" class="menu-item">
        <span class="emoji" aria-hidden="true">🎟️</span><span>Voucher Kursi Pijat</span>
      </a>
    <?php endif; ?>

  </div>
  <?php endif; ?>

  <!-- ========== PlayStation ========== -->
  <?php if ($showPS): ?>
  <div class="menu-group" role="group" aria-label="PlayStation">
    <div class="menu-title">PlayStation</div>

    <?php if (user_can_mod(['admin_ps'])): ?>
      <a id="quick-ps-link" href="<?= site_url('admin_ps') ?>" class="menu-item">
        <span class="emoji" aria-hidden="true">🎮</span><span>POS PS</span>
      </a>
    <?php endif; ?>

    <?php if (user_can_mod(['admin_voucher_ps'])): ?>
      <a id="quick-voucher-ps-link" href="<?= site_url('admin_voucher_ps') ?>" class="menu-item">
        <span class="emoji" aria-hidden="true">🎟️</span><span>Voucher PS</span>
      </a>
    <?php endif; ?>

  </div>
  <?php endif; ?>


  <!-- ========== Keuangan & Laporan ========== -->
  <?php if ($showKeuLap): ?>
  <div class="menu-group" role="group" aria-label="Keuangan & Laporan">
    <div class="menu-title">Keuangan &amp; Laporan</div>

    <?php if (user_can_mod(['admin_pengeluaran'])): ?>
      <a id="quick-pengeluaran-link" href="<?= site_url('admin_pengeluaran') ?>" class="menu-item">
        <span class="emoji" aria-hidden="true">💸</span><span>Pengeluaran</span>
      </a>
    <?php endif; ?>
      <?php
        $uname = strtolower((string)$this->session->userdata('admin_username'));
        if (
          in_array($uname, ['admin','kasir'], true)
          && user_can_mod(['admin_laporan','admin_laporan/index'])
        ):
      ?>
        <a id="quick-laporan-link"
           href="<?= site_url('admin_laporan') ?>"
           class="menu-item">
          <span class="emoji" aria-hidden="true">📊</span>
          <span>Laporan</span>
        </a>
      <?php endif; ?>
      <?php if (user_can_mod(['admin_monitor'])): ?>
        <a id="quick-monitor-link" href="<?= site_url('admin_monitor') ?>" class="menu-item">
          <span class="emoji" aria-hidden="true">📺</span><span>Status Monitor Billiard</span>
        </a>
      <?php endif; ?>
    <?php if (user_can_mod(['admin_laporan/chart','dashboard'])): ?>
      <a id="quick-statistik-link" href="<?= site_url('admin_laporan/chart') ?>" class="menu-item">
        <span class="emoji" aria-hidden="true">📈</span><span>Statistik</span>
      </a>
    <?php endif; ?>
    <?php if (user_can_mod(['admin_rating'])): ?>
      <a id="quick-rating-link" href="<?= site_url('admin_rating') ?>" class="menu-item">
        <span class="emoji" aria-hidden="true">⭐</span><span>Rating</span>
      </a>
    <?php endif; ?>

  </div>
  <?php endif; ?>

  <!-- ========== Master Data ========== -->
  <?php if ($showMaster): ?>
  <div class="menu-group" role="group" aria-label="Master Data">
    <div class="menu-title">Master Data</div>

    <?php if (user_can_mod(['admin_produk'])): ?>
      <a id="quick-produk-link" href="<?= site_url('admin_produk') ?>" class="menu-item">
        <span class="emoji" aria-hidden="true">📦</span><span>Produk</span>
      </a>
    <?php endif; ?>

    <?php if (user_can_mod(['admin_kategori_produk'])): ?>
      <a id="quick-kategori-link" href="<?= site_url('admin_kategori_produk') ?>" class="menu-item">
        <span class="emoji" aria-hidden="true">🏷️</span><span>Kategori Produk</span>
      </a>
    <?php endif; ?>

    


    <?php if (user_can_mod(['admin_kurir'])): ?>
      <a id="quick-kurir-link" href="<?= site_url('admin_kurir') ?>" class="menu-item">
        <span class="emoji" data-anim="cart" aria-hidden="true">🛵</span><span>Kurir</span>
      </a>
    <?php endif; ?>

    <?php if (user_can_mod(['admin_unit_lain'])): ?>
      <a id="quick-unit-lain-link" href="<?= site_url('admin_unit_lain') ?>" class="menu-item">
        <span class="emoji" aria-hidden="true">🧩</span><span>Unit Lain</span>
      </a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- ========== Pengaturan & Admin ========== -->
  <?php if ($showAdmin): ?>
  <div class="menu-group" role="group" aria-label="Pengaturan & Admin">
    <div class="menu-title">Pengaturan &amp; Admin</div>

    <?php if (user_can_mod(['admin_user'])): ?>
      <a id="quick-user-link" href="<?= site_url('admin_user') ?>" class="menu-item">
        <span class="emoji" aria-hidden="true">👥</span><span>Manajemen User</span>
      </a>
    <?php endif; ?>

    <?php if (user_can_mod(['admin_setting_web'])): ?>
      <a id="quick-setting-link" href="<?= site_url('admin_setting_web') ?>" class="menu-item">
        <span class="emoji" aria-hidden="true">⚙️</span><span>Pengaturan Sistem</span>
      </a>
    <?php endif; ?>

    <?php if (user_can_mod(['admin_pengumuman'])): ?>
      <a id="quick-pengumuman-link" href="<?= site_url('admin_pengumuman') ?>" class="menu-item">
        <span class="emoji" aria-hidden="true">📣</span><span>Pengumuman</span>
      </a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

</div>
<style>
  .menu-group + .menu-group { margin-top: 12px; }
  .menu-title { font-weight:600; font-size:.95rem; padding:6px 8px; opacity:.8; }
</style>

</div></div>
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
  admin_voucher_cafe:    { a: document.getElementById("quick-voucher-cafe-link") }, // <-- TAMBAHAN
  admin_meja:            { a: document.getElementById("quick-meja-link") },

  // ===== POS / Transaksi =====
  admin_pos:             { a: document.getElementById("quick-pos-link") },
  admin_billiard:        { a: document.getElementById("quick-billiard-link") },
  admin_pengeluaran:     { a: document.getElementById("quick-pengeluaran-link") },

  // ===== Riwayat & Laporan =====
  admin_pos_riwayat:       { a: document.getElementById("quick-riwayat-caffe-link") },
  admin_riwayat_billiard:  { a: document.getElementById("quick-riwayat-billiard-link") },
  admin_laporan:           { a: document.getElementById("quick-laporan-link") },
  admin_laporan_chart:     { a: document.getElementById("quick-statistik-link") }, // Statistik
  admin_rating:            { a: document.getElementById("quick-rating-link") },    // <-- NEW

  // ===== PlayStation =====
  admin_ps:                { a: document.getElementById("quick-ps-link") },
  admin_voucher_ps:        { a: document.getElementById("quick-voucher-ps-link") },

  // ===== Manajemen & Pengaturan =====
  admin_user:          { a: document.getElementById("quick-user-link") },
  admin_setting_web:   { a: document.getElementById("quick-setting-link") },
  admin_unit_lain:     { a: document.getElementById("quick-unit-lain-link") },
  admin_pengumuman:    { a: document.getElementById("quick-pengumuman-link") },
  admin_monitor:           { a: document.getElementById("quick-monitor-link") },   // NEW: Monitor TV
  admin_kursi_pijat:         { a: document.getElementById("quick-kursi-pijat-link") },          // <-- NEW
  admin_voucher_kursi_pijat: { a: document.getElementById("quick-voucher-kursi-pijat-link") }, // <-- NEW
  admin_voucher_billiard: { a: document.getElementById("quick-voucher-billiard-link") }, // <-- ADD

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

<!-- sound area -->
<script>
  window.PING_POS_BIL_CFG = {
    pingPosUrl: "<?= site_url('admin_pos/ping'); ?>",
    pingBilUrl: "<?= site_url('admin_billiard/ping'); ?>",
    soundPosUrl:"<?= base_url('assets/sound/notif.wav'); ?>",
    soundBilUrl:"<?= base_url('assets/sound/notif_b.wav'); ?>",
    lsKey: "admin_sound_enabled"
  };
</script>
<script src="<?= base_url('assets/min/sound.min.js?v='.filemtime(FCPATH.'assets/min/sound.min.js')); ?>"></script>
<!-- end of sound area -->

<!-- GMAIL AREA -->
<?php
  $uname = strtolower((string)$this->session->userdata('admin_username'));
  $isKB  = in_array($uname, ['kitchen','bar'], true);
?>
<?php if (!$isKB): ?>


<!-- ===== MODAL: INBOX GMAIL (PRETTY) ===== -->
<div id="gmail-inbox-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content gmail-modal">
      <div class="modal-header gmail-modal__header">
        <div class="d-flex align-items-center" style="gap:.6rem;">
          <div class="gmail-icon">
            <i class="mdi mdi-email-outline"></i>
          </div>
          <div>
            <div class="gmail-title">Inbox Gmail</div>
            <div class="gmail-subtitle" id="gmail-subtitle">Tarik email terbaru & cari cepat</div>
          </div>
        </div>

        <button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">×</button>
      </div>

      <div class="modal-body">
        <!-- Toolbar -->
        <div class="gmail-toolbar">
          <div class="gmail-search">
            <i class="mdi mdi-magnify"></i>
            <input type="search" id="gmail-q" class="form-control" placeholder="Cari subject / from / snippet…">
            <button type="button" class="btn btn-light btn-sm gmail-clear" id="gmail-clear" title="Bersihkan">
              <i class="mdi mdi-close"></i>
            </button>
          </div>

         <!--  <button type="button" class="btn btn-primary btn-sm gmail-sync" id="gmail-sync-btn">
            <i class="fe-refresh-ccw mr-1"></i> Refresh
          </button> -->
          <button type="button" class="btn btn-danger btn-sm gmail-sync" id="gmail-sync-btn">
            <i class="fe-refresh-ccw mr-1"></i><span class="btn-text"> Cek Email</span>
          </button>

        </div>

        <!-- Loading (shimmer) -->
        <div id="gmail-loading" class="gmail-loading" style="display:none">
          <div class="gmail-skel">
            <div class="skel-avatar"></div>
            <div class="skel-lines">
              <div class="skel-line w60"></div>
              <div class="skel-line w90"></div>
              <div class="skel-line w40"></div>
            </div>
          </div>
          <div class="gmail-skel">
            <div class="skel-avatar"></div>
            <div class="skel-lines">
              <div class="skel-line w50"></div>
              <div class="skel-line w80"></div>
              <div class="skel-line w35"></div>
            </div>
          </div>
          <div class="gmail-skel">
            <div class="skel-avatar"></div>
            <div class="skel-lines">
              <div class="skel-line w70"></div>
              <div class="skel-line w95"></div>
              <div class="skel-line w45"></div>
            </div>
          </div>
          <div class="gmail-loading-text">
            <span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
            Memuat email…
          </div>
        </div>

        <!-- Empty State -->
        <div id="gmail-empty" class="gmail-empty" style="display:none">
          <div class="gmail-empty__icon">
            <i class="mdi mdi-inbox-arrow-down-outline"></i>
          </div>
          <div class="gmail-empty__title">Belum ada email</div>
          <div class="gmail-empty__desc">Klik <b>Refresh</b> untuk mengambil email terbaru.</div>
        </div>

        <!-- List -->
        <div id="gmail-list" class="list-group gmail-list"></div>
      </div>

      <div class="modal-footer gmail-modal__footer">
        <div class="d-flex align-items-center" style="gap:.5rem;">
          <span class="badge badge-pill badge-light border" id="gmail-count">0 email</span>
          <small class="text-muted" id="gmail-hint">Klik item untuk detail.</small>
        </div>
        <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>


<!-- ===== MODAL: DETAIL EMAIL (PRETTY) ===== -->
<div id="gmail-detail-modal" class="modal fade gmail-detail-modal" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <div class="gmail-detail-top">
          <div class="gmail-icon">
            <i class="mdi mdi-email-open-outline"></i>
          </div>
          <div>
            <div class="gmail-detail-title" id="gmail-detail-title">Detail Email</div>
            <div class="gmail-detail-subtitle" id="gmail-detail-subtitle">Memuat isi email…</div>
          </div>
        </div>

        <button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">×</button>
      </div>

      <div class="modal-body">
        <div class="gmail-detail-card" id="gmail-detail-body">
          <div class="text-center text-muted py-5">Memuat…</div>
        </div>
      </div>

      <div class="modal-footer">
        <div class="gmail-detail-actions mr-auto">
          <span class="badge badge-pill badge-light border" id="gmail-detail-badge">—</span>
          <small class="text-muted" id="gmail-detail-hint">Klik tab Email/Text/RAW untuk melihat isi.</small>
        </div>
        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Tutup Detail</button>
      </div>

    </div>
  </div>
</div>


<style>
  .fab-gmail{
    position: fixed;
    right: 18px;
    top: 140px;
    z-index: 9999;
    border-radius: 999px;
    box-shadow: 0 14px 35px rgba(0,0,0,.22);
    padding: 10px 14px;
    display: inline-flex;
    align-items: center;
    gap: .5rem;
  }

  /* biar di hp cuma ikon aja */
  @media (max-width: 575.98px){
    .fab-gmail .fab-text{ display:none; }
    .fab-gmail{ padding: 12px; }
  }
</style>



  <button type="button"
          onclick="openGmailInbox()"
          class="btn btn-blue btn-sm waves-effect waves-light fab-gmail"
          title="Cek transaksi QRIS">
    <i class="mdi mdi-gmail"></i>
    <span class="fab-text">Cek transaksi QRIS</span>
  </button>

<script>
window.GMAIL_CFG = {
  URL_LIST:   <?= json_encode(site_url('admin_pos/gmail_inbox')) ?>,
  URL_SYNC:   <?= json_encode(site_url('admin_pos/gmail_sync')) ?>,
  URL_DETAIL: <?= json_encode(site_url('admin_pos/gmail_detail').'/') ?>
};

function loadCssOnce(href){
  if (document.querySelector('link[data-href="'+href+'"]')) return Promise.resolve();
  return new Promise(res=>{
    const l=document.createElement('link');
    l.rel='stylesheet'; l.href=href; l.setAttribute('data-href', href);
    l.onload=res; document.head.appendChild(l);
  });
}
function loadJsOnce(src){
  if (document.querySelector('script[data-src="'+src+'"]')) return Promise.resolve();
  return new Promise((res,rej)=>{
    const s=document.createElement('script');
    s.src=src; s.defer=true; s.setAttribute('data-src', src);
    s.onload=res; s.onerror=rej; document.body.appendChild(s);
  });
}

window.openGmailInbox = async function(){
  try{
    await loadCssOnce("<?= base_url('assets/min/gmai.min.css?v=3') ?>");
    await loadJsOnce("<?= base_url('assets/min/gmail.min.js?v=3') ?>");
    if (window.gmailInitAndOpen) window.gmailInitAndOpen(window.GMAIL_CFG);
    else $('#gmail-inbox-modal').modal('show');
  }catch(e){
    console.error(e);
    if (window.Swal) Swal.fire('Gagal', 'Gagal memuat modul Gmail (css/js).', 'error');
  }
};
</script>

<?php endif; ?>
<script>
(function(){
  if (!window.jQuery) return;
  $(document)
    .off('click.qrischeck', '.qris-check-btn')
    .on('click.qrischeck', '.qris-check-btn', function(e){
      e.preventDefault();
      e.stopPropagation();
      if (window.openGmailInbox) window.openGmailInbox();
    });
})();
</script>

<!-- END OF GMAIL AREA -->
