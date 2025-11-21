<?php $this->load->view("front_end/head.php"); ?>

<!-- custom style untuk halaman menu -->
<link rel="stylesheet"
href="<?= base_url('assets/front/produk.min.css') ?>?v=<?= filemtime(FCPATH.'assets/front/produk.min.css'); ?>">
<style>
  /* ===== Tulisan Berjalan: Batas Last Order ===== */
  .lastorder-ticker{
    position: relative;
    overflow: hidden;
    background: linear-gradient(90deg, #dc3545, #ff6b6b);
    color: #fff;
    border-radius: 999px;
    padding: 4px 0;
    margin-top: .5rem;
    margin-bottom: .75rem;
    box-shadow: 0 6px 16px rgba(220,53,69,.35);
    display: none;              /* default: sembunyikan */
  }
  .lastorder-ticker.is-active{
    display: block;             /* muncul kalau sudah 1 jam sebelumnya */
  }
  .lastorder-track{
    display: inline-block;
    white-space: nowrap;
    will-change: transform;
    animation: lastorder-scroll 22s linear infinite;
    padding-left: 100%; /* mulai dari luar kanan */
  }
  .lastorder-ticker:hover .lastorder-track{
    animation-play-state: paused; /* bisa dihentikan kalau di-hover */
  }
  .lastorder-label{
    display: inline-flex;
    align-items: center;
    font-weight: 600;
    text-transform: uppercase;
    font-size: .75rem;
    padding: .15rem .75rem;
    background: rgba(0,0,0,.25);
    border-radius: 999px;
    margin-right: .75rem;
    letter-spacing: .04em;
  }
  .lastorder-label i{
    font-size: .9rem;
    margin-right: .25rem;
  }
  .lastorder-text{
    font-size: .8rem;
  }
  .lastorder-text strong{
    font-weight: 700;
  }
  @media (min-width: 768px){
    .lastorder-text{ font-size: .9rem; }
  }
  @keyframes lastorder-scroll{
    0%   { transform: translateX(0); }
    100% { transform: translateX(-100%); }
  }
</style>


<?php
// Cari ID kategori "Makanan" & "Minuman"
$kat_makanan_id = '';
$kat_minuman_id = '';
if (!empty($kategoris)) {
  foreach ($kategoris as $k) {
    $nm = strtolower(trim($k->nama));
    if ($kat_makanan_id === '' && strpos($nm, 'makanan') !== false) {
      $kat_makanan_id = (string)$k->id;
    }
    if ($kat_minuman_id === '' && strpos($nm, 'minuman') !== false) {
      $kat_minuman_id = (string)$k->id;
    }
  }
}

/**
 * Jam LAST ORDER = jam tutup hari ini dari profil identitas ($rec)
 * - pakai op_mon_close / op_tue_close / ... / op_sun_close
 * - kalau hari ini di-set closed / jam kosong -> ticker tidak muncul
 */
$last_order_label = '';

if (isset($rec) && is_object($rec)) {
    $dow = (int) date('N'); // 1 = Senin ... 7 = Minggu

    $closeFieldMap  = [
        1 => 'op_mon_close',
        2 => 'op_tue_close',
        3 => 'op_wed_close',
        4 => 'op_thu_close',
        5 => 'op_fri_close',
        6 => 'op_sat_close',
        7 => 'op_sun_close',
    ];
    $closedFieldMap = [
        1 => 'op_mon_closed',
        2 => 'op_tue_closed',
        3 => 'op_wed_closed',
        4 => 'op_thu_closed',
        5 => 'op_fri_closed',
        6 => 'op_sat_closed',
        7 => 'op_sun_closed',
    ];

    $closeField  = $closeFieldMap[$dow]  ?? null;
    $closedField = $closedFieldMap[$dow] ?? null;

    $isClosedDay = false;
    if ($closedField && property_exists($rec, $closedField)) {
        $isClosedDay = ((int)$rec->{$closedField} === 1);
    }

    if (!$isClosedDay && $closeField && property_exists($rec, $closeField)) {
        $jam = trim((string)$rec->{$closeField});
        if (preg_match('/^\d{2}:\d{2}$/', $jam)) {
            $last_order_label = $jam; // contoh: "23:59" atau "01:00"
        }
    }
}
?>

<div class="container-fluid">
  <div class="mt-2">
    <?php $this->load->view("judul_mode") ?>

    <div id="mode-info"
    data-mode="<?= html_escape($mode ?? '') ?>"
    data-meja="<?= html_escape($meja_info ?? '') ?>">
  </div>
</div>
 <!-- Tulisan berjalan batas last order -->
  <div class="lastorder-ticker"
       role="status"
       aria-label="Peringatan batas last order"
       data-last-order="<?= html_escape($last_order_label); ?>">
    <div class="lastorder-track">
      <span class="lastorder-label">
        <i class="mdi mdi-alert" aria-hidden="true"></i> INFO
      </span>
      <span class="lastorder-text">
        Batas <strong>JAM TUTUP ORDER</strong> Makanan/minuman sampai pukul
        <strong><?= html_escape($last_order_label); ?> Waktu Siwa Hari ini</strong>.
        Siahkan lakukan order sebelum jam tutup order. Terima kasih. 💛
      </span>
    </div>
  </div>

<form id="filter-form" class="mb-0">
  <input type="hidden" id="kategori" name="kategori" value="<?= html_escape($kategori); ?>">
  <input type="hidden" id="sort" name="sort" value="<?= html_escape($sort ?: 'random'); ?>">
  <input type="hidden" id="recommended" name="recommended" value="0">

  <div class="filter-toolbar">
    <?php $this->load->view("form_cari") ?>

    <div class="filter-sort">
      <div class="dropdown">
        <button class="btn btn-danger dropdown-toggle d-flex align-items-center"
        type="button"
        id="dropdownSortBtn"
        data-toggle="dropdown"
        aria-haspopup="true"
        aria-expanded="false">
        <span id="sortBtnLabel">Urutkan</span>&nbsp;<i class="mdi mdi-chevron-down"></i>
      </button>

      <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownSortBtn">
        <a class="dropdown-item sort-opt" data-sort="random" href="javascript:void(0)">
          <i class="fas fa-thumbs-up me-2"></i> Untukmu
        </a>
        <a class="dropdown-item sort-opt" data-sort="new" href="javascript:void(0)">
          <i class="fas fa-clock me-2"></i> Terbaru
        </a>

        <!-- ⬇️ Tambahan: Trending -->
        <div class="dropdown-divider"></div>
        <a class="dropdown-item sort-opt" data-sort="trending" data-trend="today" href="javascript:void(0)">
          <i class="fas fa-bolt me-2"></i> Favorit • Hari ini
        </a>
        <a class="dropdown-item sort-opt" data-sort="trending" data-trend="week" href="javascript:void(0)">
          <i class="fas fa-bolt me-2"></i> Favorit • 7 hari
        </a>
        <a class="dropdown-item sort-opt" data-sort="trending" data-trend="month" href="javascript:void(0)">
          <i class="fas fa-bolt me-2"></i> Favorit • 30 hari
        </a>
        <div class="dropdown-divider"></div>

        <a class="dropdown-item sort-opt" data-sort="bestseller" href="javascript:void(0)">
          <i class="fas fa-star me-2"></i> Terlaris
        </a>
        <a class="dropdown-item sort-opt" data-sort="price_low" href="javascript:void(0)">
          <i class="fas fa-arrow-down me-2"></i> Harga Rendah
        </a>
        <a class="dropdown-item sort-opt" data-sort="price_high" href="javascript:void(0)">
          <i class="fas fa-arrow-up me-2"></i> Harga Tinggi
        </a>
        <a class="dropdown-item sort-opt" data-sort="sold_out" href="javascript:void(0)">
          <i class="fas fa-ban me-2"></i> Habis
        </a>
      </div>

    </div>
  </div>
</div>

<!-- QUICKMENU -->
<div class="quickmenu-wrap position-relative mb-1" id="grandong">
  <div id="quickmenu" class="quickmenu-scroll d-flex text-center" tabindex="0" aria-label="Kategori">

    <div class="quickmenu-item" data-kategori="">
      <div class="qcard">
        <div class="menu-circle" >
          <span class="emoji-icon" data-anim="all">🍽️</span>
        </div>
        <small class="menu-label">Semua</small>
      </div>
    </div>

    <div class="quickmenu-item" data-kategori="<?= html_escape($kat_makanan_id); ?>">
      <div class="qcard">
        <div class="menu-circle" >
          <span class="emoji-icon" data-anim="food">🍝</span>
        </div>
        <small class="menu-label">Makanan</small>
      </div>
    </div>

    <div class="quickmenu-item" data-kategori="<?= html_escape($kat_minuman_id); ?>">
      <div class="qcard">
        <div class="menu-circle" >
          <span class="emoji-icon" data-anim="drink">☕</span>
        </div>
        <small class="menu-label">Minuman</small>
      </div>
    </div>
    <div class="quickmenu-item" data-recommended="1">
      <div class="qcard">
        <div class="menu-circle">
          <span class="emoji-icon" data-anim="all">🔥</span>
        </div>
        <small class="menu-label">Andalang</small>
      </div>
    </div>



       <!--  <div class="quickmenu-item" data-action="cart">
          <a class="qcard d-block text-decoration-none"
             href="<?= site_url('produk/cart') ?>"
             aria-label="Buka keranjang">
            <div class="menu-circle" >
              🍱<span class="q-badge" data-anim="cart" id="cart-count">0</span>
            </div>
            <small class="menu-label">Keranjang</small>
          </a>
        </div>
      -->
    </div>
  </div>
</form>

<div class="row mt-1" id="grid-products"></div>
<div class="row"><div class="col-12" id="pagination-wrap"></div></div>
</div>

<!-- FAB Cart -->
<a href="<?= site_url('produk/cart') ?>"
 id="fab-cart"
 class="fab-cart"
 aria-label="Buka keranjang">
 <span class="spinner-border d-none" aria-hidden="true"></span>
 <i class="mdi mdi-cart-outline icon-default" aria-hidden="true"></i>

 <?php if (!empty($meja_info)): ?>
  <span class="fab-label d-none d-sm-inline">Meja <?= html_escape($meja_info) ?></span>
<?php endif; ?>

<span class="fab-badge" id="fab-count">0</span>
</a>

<!-- TOUR TOOLTIP KERANJANG -->
<div id="fab-cart-tooltip" class="cart-tour-tooltip" role="dialog" aria-live="polite">
  <div class="cart-tour-card">
    <!-- Badge bulat di pojok -->
    <div class="cart-tour-step">
      <i class="mdi mdi-cart-outline" aria-hidden="true"></i>
    </div>

    <div class="cart-tour-content">
      <div class="cart-tour-title">Ini keranjang Anda</div>
      <div class="cart-tour-text">
        Di sini tersimpan pesanan Anda. Klik untuk melihat &amp; menyelesaikan orderan.
      </div>

      <div class="cart-tour-actions">
        <button type="button"
        id="fab-cart-tooltip-ok"
        class="cart-tour-btn-primary">
        OK, saya paham
      </button>
    </div>
  </div>
</div>
</div>
<script>
(function(){
  var ticker = document.querySelector('.lastorder-ticker');
  if (!ticker) return;

  var now = new Date();

  // Target last order: 23:59 (diasumsikan waktu lokal pengunjung = WITA)
  var target = new Date(
    now.getFullYear(),
    now.getMonth(),
    now.getDate(),
    23, 59, 0, 0
  );

  var diffMs      = target - now;
  var diffMinutes = diffMs / 60000;

  // Aktif kalau sekarang berada di rentang 0–60 menit sebelum 23:59
  if (diffMinutes <= 60 && diffMinutes >= 0) {
    ticker.classList.add('is-active');
  }
})();
</script>

<!-- Vendor JS (harus duluan, biar jQuery/SweetAlert dsb sudah ada) -->
<script src="<?= base_url('assets/admin/js/vendor.min.js'); ?>"></script>
<script src="<?= base_url('assets/admin/js/app.min.js'); ?>"></script>
<script src="<?= base_url('assets/admin/js/sw.min.js'); ?>"></script>
<script>
  window.AUSI_CFG = {
    sub_api     : "<?= site_url('produk/subkategori/'); ?>",
    list_ajax   : "<?= site_url('produk/list_ajax'); ?>",
    add_to_cart : "<?= site_url('produk/add_to_cart'); ?>",
    cart_count  : "<?= site_url('produk/cart_count'); ?>"
  };
</script>

<!-- Custom logic kita -->
<script src="<?= base_url('assets/front/produk.min.js') ?>?v=<?= filemtime(FCPATH.'assets/front/produk.min.js'); ?>"></script>

<?php
// Asumsi timezone sudah di-set di config (Asia/Makassar)
// 1 = Senin, 2 = Selasa, ..., 5 = Jumat, 7 = Minggu
$today = (int) date('N');

if ($today === 5) {
    // Hari Jumat
  $this->load->view("promo_jumat_berkah");
} else {
    // Selain Jumat
  $this->load->view("promo_mingguan");
}
?>

<?php $this->load->view("front_end/footer.php") ?>

<?php $this->load->view("modal_produk") ?>