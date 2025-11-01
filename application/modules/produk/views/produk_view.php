<?php $this->load->view("front_end/head.php"); ?>

<!-- custom style untuk halaman menu -->
<link rel="stylesheet"
      href="<?= base_url('assets/front/produk.min.css') ?>?v=<?= filemtime(FCPATH.'assets/front/produk.min.css'); ?>">
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
?>

<div class="container-fluid">
  <div class="mt-2">
    <?php $this->load->view("judul_mode") ?>

    <div id="mode-info"
         data-mode="<?= html_escape($mode ?? '') ?>"
         data-meja="<?= html_escape($meja_info ?? '') ?>">
    </div>
  </div>

  <form id="filter-form" class="mb-0">
    <input type="hidden" id="kategori" name="kategori" value="<?= html_escape($kategori); ?>">
    <input type="hidden" id="sort" name="sort" value="<?= html_escape($sort ?: 'random'); ?>">

    <div class="filter-toolbar">
      <div class="filter-search">
        <div class="input-group">
          <input type="search"
                 class="form-control filter-input"
                 id="q"
                 name="q"
                 value="<?= html_escape($q); ?>"
                 placeholder="Cari produk…"
                 aria-label="Cari menu"
                 autocomplete="off">
          <div class="input-group-append">
            <button type="button" id="btn-reset" class="btn btn-danger">
              <i class="fa fa-times"></i>
            </button>
          </div>
        </div>
      </div>

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
              <i class="fas fa-thumbs-up me-2"></i> For You
            </a>
            <a class="dropdown-item sort-opt" data-sort="new" href="javascript:void(0)">
              <i class="fas fa-clock me-2"></i> Terbaru
            </a>
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
    <div class="quickmenu-wrap position-relative mb-2" id="grandong">
      <div id="quickmenu" class="quickmenu-scroll d-flex text-center" tabindex="0" aria-label="Kategori">

        <div class="quickmenu-item" data-kategori="">
          <div class="qcard">
            <div class="menu-circle" style="background:#6f42c1;">
              <span class="emoji-icon" data-anim="all">🗂️</span>
            </div>
            <small class="menu-label">Semua</small>
          </div>
        </div>

        <div class="quickmenu-item" data-kategori="<?= html_escape($kat_makanan_id); ?>">
          <div class="qcard">
            <div class="menu-circle" style="background:#e67e22;">
              <span class="emoji-icon" data-anim="food">🍽️</span>
            </div>
            <small class="menu-label">Makanan</small>
          </div>
        </div>

        <div class="quickmenu-item" data-kategori="<?= html_escape($kat_minuman_id); ?>">
          <div class="qcard">
            <div class="menu-circle" style="background:#17a2b8;">
              <span class="emoji-icon" data-anim="drink">🥤</span>
            </div>
            <small class="menu-label">Minuman</small>
          </div>
        </div>

        <div class="quickmenu-item" data-action="cart">
          <a class="qcard d-block text-decoration-none"
             href="<?= site_url('produk/cart') ?>"
             aria-label="Buka keranjang">
            <div class="menu-circle" style="background:#ef4444;">
              <i class="mdi mdi-cart-outline" data-anim="cart" style="font-size:26px;position:relative;"></i>
              <span class="q-badge" id="cart-count">0</span>
            </div>
            <small class="menu-label" style="color:#ef4444;">Keranjang</small>
          </a>
        </div>

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

<?php $this->load->view("front_end/footer.php") ?>
<?php $this->load->view("modal_produk.php") ?>
