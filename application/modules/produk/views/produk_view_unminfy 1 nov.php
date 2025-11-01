<?php $this->load->view("front_end/head.php"); ?>

<style>
  /* QUICKMENU */
  .quickmenu-wrap {
    position: relative;
    width: 100%;
  }

  .quickmenu-scroll {
    display: flex;
    align-items: stretch;
    gap: .75rem;
    overflow-x: auto;
    overflow-y: hidden;
    scroll-behavior: smooth;
    padding: .25rem .25rem;
    -webkit-overflow-scrolling: touch;
  }

  .quickmenu-scroll::-webkit-scrollbar {
    height: 8px;
  }

  .quickmenu-scroll::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, .15);
    border-radius: 999px;
  }

  .quickmenu-item {
    flex: 0 0 auto;
    width: 80px;
  }

  .qcard {
    border-radius: 20px;
    padding: .1rem .25rem;
    background: #fff;
    border: 1px solid rgba(0,0,0,.08);
    transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
    cursor: pointer;
    text-decoration: none;
  }

  .qcard:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 14px rgba(0,0,0,.07);
  }

  .menu-circle {
    width: 56px;
    height: 56px;
    border-radius: 999px;
    margin: 0 auto .35rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 26px;
    box-shadow:
      0 8px 18px rgba(0,0,0,.15) inset,
      0 6px 12px rgba(0,0,0,.08);
    position: relative;
  }

  .emoji-icon {
    display: block;
    line-height: 1;
    transform: translateY(2px);
  }

  .menu-label {
    display: block;
    color: #333;
    font-weight: 600;
  }

  .quickmenu-item.active .qcard {
    border-color: red;
    box-shadow: 0 0 0 2px rgba(30,136,229,.18) inset;
  }

  .quickmenu-item.active .menu-label {
    color: #1e88e5;
  }

  /* GRID: Mobile 2 kolom */
  @media (max-width: 767.98px) {
    #grid-products {
      display: grid !important;
      grid-template-columns: repeat(2, 1fr) !important;
      grid-gap: 10px !important;
      margin-left: 0 !important;
      margin-right: 0 !important;
    }

    #grid-products > [class^="col-"],
    #grid-products > [class*=" col-"] {
      width: auto !important;
      max-width: 100% !important;
      padding: 0 !important;
      float: none !important;
    }
  }

  /* FAB Cart (mobile only) */
  .fab-cart {
    position: fixed;
    right: 16px;
    bottom: calc(70px + env(safe-area-inset-bottom));
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    background: #ef4444;
    color: #fff;
    text-decoration: none;
    padding: .75rem .9rem;
    border-radius: 999px;
    box-shadow:
      0 12px 24px rgba(0,0,0,.18),
      0 2px 6px rgba(0,0,0,.12);
    z-index: 2147483000;
    font-weight: 700;
  }

  .fab-cart .mdi {
    font-size: 22px;
    line-height: 1;
  }

  .fab-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 22px;
    height: 22px;
    padding: 0 .45rem;
    background: #111827;
    color: #fff;
    border-radius: 999px;
    font-size: .85rem;
    box-shadow: 0 6px 14px rgba(0,0,0,.25) inset;
  }

  @media (min-width: 768px) {
    .fab-cart {
      display: none;
    }
  }

  .dropdown-menu {
    z-index: 200010 !important;
  }

  /* Badge kecil di icon keranjang quickmenu */
  .q-badge {
    position: absolute;
    top: -6px;
    right: -6px;
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    background: #111827;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 10px rgba(0,0,0,.25);
  }

  /* Skeleton */
  .skel-card {
    border: 1px solid rgba(0,0,0,.06);
    border-radius: 12px;
    background: #fff;
    padding: 10px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
    height: 100%;
  }

  .skel-thumb {
    width: 100%;
    aspect-ratio: 1 / 1;
    border-radius: 10px;
    margin-bottom: 10px;
    background: #eee;
    position: relative;
    overflow: hidden;
  }

  .skel-line,
  .skel-price,
  .skel-btn {
    height: 12px;
    border-radius: 999px;
    background: #eee;
    margin-bottom: 8px;
    position: relative;
    overflow: hidden;
  }

  .skel-line.w60 { width: 60%; }
  .skel-line.w80 { width: 80%; }
  .skel-line.w40 { width: 40%; }

  .skel-price {
    width: 40%;
    height: 14px;
  }

  .skel-btn {
    width: 60%;
    height: 34px;
    border-radius: 10px;
    margin-top: 6px;
  }

  .skel-shimmer::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.6), transparent);
    transform: translateX(-100%);
    animation: skel 1.2s infinite;
  }

  @keyframes skel {
    100% { transform: translateX(100%); }
  }

  @media (prefers-reduced-motion: reduce) {
    .skel-shimmer::after {
      display: none;
    }
  }

  /* Toolbar: Search + Sort sejajar */
  .filter-toolbar {
    display: flex;
    align-items: center;
    gap: .5rem;
    flex-wrap: nowrap;
    margin: .5rem 0 .75rem;
  }

  .filter-search {
    flex: 1 1 auto;
    min-width: 0;
  }

  .filter-search .input-group {
    width: 100%;
  }

  .filter-sort {
    flex: 0 0 auto;
  }

  .filter-sort .btn {
    white-space: nowrap;
  }

  @media (max-width: 360px) {
    .filter-toolbar {
      gap: .35rem;
    }
    .filter-sort .btn {
      padding-left: .5rem;
      padding-right: .5rem;
    }
  }

  /* Tombol biru util */
  .btn-blue {
    background: #1e88e5;
    border-color: #1e88e5;
    color: #fff;
  }

  .btn-blue:hover {
    filter: brightness(.95);
  }

  /* === Scroll hint untuk quickmenu (fade kiri/kanan) === */
  .quickmenu-wrap {
    position: relative;
    --qm-fade-w: 36px;   /* lebar fade */
    --qm-bg: #fff;       /* warna latar belakang area quickmenu */
  }

  .quickmenu-wrap::before,
  .quickmenu-wrap::after {
    content: "";
    position: absolute;
    top: 0;
    bottom: 0;
    width: var(--qm-fade-w);
    pointer-events: none;
    opacity: 0;
    transition: opacity .18s ease;
    z-index: 1;
  }

  .quickmenu-wrap::before {
    left: 0;
    background:
      linear-gradient(to right, var(--qm-bg) 30%, rgba(255,255,255,0));
    backdrop-filter: blur(0px); /* opsional efek blur tipis */
  }

  .quickmenu-wrap::after {
    right: 0;
    background:
      linear-gradient(to left, var(--qm-bg) 30%, rgba(255,255,255,0));
    backdrop-filter: blur(0px);
  }

  /* Saat ada konten tersembunyi di kiri/kanan, tampilkan fades */
  .quickmenu-wrap.show-left::before {
    opacity: 1;
  }

  .quickmenu-wrap.show-right::after {
    opacity: 1;
  }

  /* Kalau kamu pakai dark section, bisa override --qm-bg di wrapper induknya */
  .quickmenu-scroll {
    scrollbar-width: none; /* Firefox */
  }

  .quickmenu-scroll::-webkit-scrollbar {
    height: 0 !important; /* WebKit */
  }

  .badge-blue {
    background-color: #ff5722 !important;
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
?>

<div class="container-fluid">
  <!--
  <div class="hero-title ausi-hero-center" role="banner" aria-label="Judul halaman">
    <h1 class="text">Menu <?= html_escape($meja_info) ?></h1>
    <span class="accent" aria-hidden="true"></span>
  </div>
  -->

  <div class="mt-2">
    <?php $this->load->view("judul_mode") ?>

    <!-- inject info mode ke JS -->
    <div
      id="mode-info"
      data-mode="<?= html_escape($mode ?? '') ?>"
      data-meja="<?= html_escape($meja_info ?? '') ?>">
    </div>
  </div>

  <!-- ===== Filter toolbar (Search + Sort) ===== -->
  <form id="filter-form" class="mb-0">
    <input type="hidden" id="kategori" name="kategori" value="<?= html_escape($kategori); ?>">
    <input type="hidden" id="sort"     name="sort"     value="<?= html_escape($sort ?: 'random'); ?>">

    <div class="filter-toolbar">
      <!-- Search -->
      <div class="filter-search">
        <div class="input-group">
          <input
            type="search"
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

      <!-- Sort -->
      <div class="filter-sort">
        <div class="dropdown">
          <button
            class="btn btn-danger dropdown-toggle d-flex align-items-center"
            type="button"
            id="dropdownSortBtn"
            data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false">
            <span id="sortBtnLabel">Urutkan</span>&nbsp;
            <i class="mdi mdi-chevron-down"></i>
          </button>

          <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownSortBtn">
            <a class="dropdown-item sort-opt" data-sort="random"      href="javascript:void(0)" aria-label="For You">
              <i class="fas fa-thumbs-up me-2" aria-hidden="true"></i> For You
            </a>
            <a class="dropdown-item sort-opt" data-sort="new"         href="javascript:void(0)" aria-label="Terbaru">
              <i class="fas fa-clock me-2" aria-hidden="true"></i> Terbaru
            </a>
            <a class="dropdown-item sort-opt" data-sort="bestseller"  href="javascript:void(0)" aria-label="Terlaris">
              <i class="fas fa-star me-2" aria-hidden="true"></i> Terlaris
            </a>
            <a class="dropdown-item sort-opt" data-sort="price_low"   href="javascript:void(0)" aria-label="Harga Rendah">
              <i class="fas fa-arrow-down me-2" aria-hidden="true"></i> Harga Rendah
            </a>
            <a class="dropdown-item sort-opt" data-sort="price_high"  href="javascript:void(0)" aria-label="Harga Tinggi">
              <i class="fas fa-arrow-up me-2" aria-hidden="true"></i> Harga Tinggi
            </a>
            <a class="dropdown-item sort-opt" data-sort="sold_out"    href="javascript:void(0)" aria-label="Sold Out">
              <i class="fas fa-ban me-2" aria-hidden="true"></i> Habis
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- QUICKMENU kategori -->
    <div class="quickmenu-wrap position-relative mb-2" id="grandong">
      <div
        id="quickmenu"
        class="quickmenu-scroll d-flex text-center"
        tabindex="0"
        aria-label="Kategori">

        <!-- Semua -->
        <div class="quickmenu-item" data-kategori="">
          <div class="qcard">
            <div class="menu-circle" style="background:#6f42c1;">
              <span class="emoji-icon" data-anim="all">🗂️</span>
            </div>
            <small class="menu-label">Semua</small>
          </div>
        </div>

        <!-- Makanan -->
        <div class="quickmenu-item" data-kategori="<?= html_escape($kat_makanan_id); ?>">
          <div class="qcard">
            <div class="menu-circle" style="background:#e67e22;">
              <span class="emoji-icon" data-anim="food">🍽️</span>
            </div>
            <small class="menu-label">Makanan</small>
          </div>
        </div>

        <!-- Minuman -->
        <div class="quickmenu-item" data-kategori="<?= html_escape($kat_minuman_id); ?>">
          <div class="qcard">
            <div class="menu-circle" style="background:#17a2b8;">
              <span class="emoji-icon" data-anim="drink">🥤</span>
            </div>
            <small class="menu-label">Minuman</small>
          </div>
        </div>

        <!-- Keranjang -->
        <div class="quickmenu-item" data-action="cart">
          <a
            class="qcard d-block text-decoration-none"
            href="<?= site_url('produk/cart') ?>"
            aria-label="Buka keranjang">
            <div class="menu-circle" style="background:#ef4444;">
              <i
                class="mdi mdi-cart-outline"
                data-anim="cart"
                aria-hidden="true"
                style="font-size:26px; position:relative;"></i>

              <span class="q-badge" id="cart-count">0</span>
            </div>
            <small class="menu-label" style="color:#ef4444;">Keranjang</small>
          </a>
        </div>

      </div>
    </div>
  </form>

  <!-- ===== LIST PRODUK: DI LUAR CARD ===== -->
  <div class="row mt-1" id="grid-products"><!-- items injected via AJAX --></div>

  <div class="row">
    <div class="col-12" id="pagination-wrap"><!-- pagination injected via AJAX --></div>
  </div>
</div>


<!-- ========================= -->
<!--  QUICKMENU ICON ANIMATION -->
<!-- ========================= -->
<style type="text/css">
  /* Biar animasi smooth dan tidak merusak layout */
  .menu-circle {
    position: relative;
    width: 58px;
    height: 58px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color:#fff;
  }

  .menu-circle .emoji-icon,
  .menu-circle .mdi {
    display: inline-block;
    line-height: 1;
    will-change: transform;
    transform-origin: center center;
  }

  /* =======================
     KEYFRAMES
     ======================= */

  /* 1. ALL (Semua): pulse zoom in-out */
  @keyframes qm-pulse {
    0%   { transform: scale(1); }
    30%  { transform: scale(1.2) rotate(3deg); }
    60%  { transform: scale(0.9) rotate(-3deg); }
    100% { transform: scale(1); }
  }

  /* 2. FOOD (Makanan): efek "tutup piring kebuka"
     - sedikit miring, naik dikit, pop turun */
  @keyframes qm-food-pop {
    0%   { transform: translateY(0) rotate(0deg) scale(1); }
    20%  { transform: translateY(-6px) rotate(-12deg) scale(1.15); }
    50%  { transform: translateY(-10px) rotate(8deg) scale(1.2); }
    80%  { transform: translateY(2px) rotate(-4deg) scale(0.95); }
    100% { transform: translateY(0) rotate(0deg) scale(1); }
  }

  /* 3. DRINK (Minuman): "diseruput"
     - naik turun cepat + sedikit rotasi sedotan */
  @keyframes qm-drink-sip {
    0%   { transform: translateY(0) rotate(0deg) scale(1); }
    25%  { transform: translateY(-5px) rotate(-5deg) scale(1.1); }
    50%  { transform: translateY(0) rotate(4deg) scale(0.95); }
    75%  { transform: translateY(-3px) rotate(-3deg) scale(1.08); }
    100% { transform: translateY(0) rotate(0deg) scale(1); }
  }

  /* 4. CART (Keranjang): wiggle kiri-kanan */
  @keyframes qm-cart-wiggle {
    0%   { transform: rotate(0deg) scale(1); }
    15%  { transform: rotate(-10deg) scale(1.05); }
    30%  { transform: rotate(8deg) scale(1.05); }
    45%  { transform: rotate(-6deg) scale(1.05); }
    60%  { transform: rotate(4deg) scale(1.05); }
    75%  { transform: rotate(-2deg) scale(1.03); }
    100% { transform: rotate(0deg) scale(1); }
  }

  /* =======================
     CLASS TRIGGER
     ======================= */

  .qm-anim-pulse   { animation: qm-pulse       .45s ease-out; }
  .qm-anim-food    { animation: qm-food-pop    .55s cubic-bezier(.2,.7,.3,1); }
  .qm-anim-drink   { animation: qm-drink-sip   .55s cubic-bezier(.2,.7,.3,1); }
  .qm-anim-cart    { animation: qm-cart-wiggle .55s cubic-bezier(.2,.7,.3,1); }

  /* biar badge keranjang tetap di atas, ga ikut goyang terlalu jauh */
  .menu-circle .q-badge {
    position: absolute;
    top: 2px;
    right: 2px;
    background: #fff;
    color: #ef4444;
    font-size: 11px;
    font-weight: 700;
    line-height: 1;
    border-radius: 999px;
    min-width: 18px;
    min-height: 18px;
    padding: 2px 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #ef4444;
    box-shadow: 0 2px 4px rgba(0,0,0,.2);
    pointer-events: none;
  }
</style>

<script>
(function(){
  // mapping anim
  const animClassMap = {
    "all"   : "qm-anim-pulse",
    "food"  : "qm-anim-food",
    "drink" : "qm-anim-drink",
    "cart"  : "qm-anim-cart"
  };

  // fungsi untuk play anim di satu icon
  function playAnim(iconEl){
    if (!iconEl) return;
    const type = iconEl.getAttribute('data-anim');
    const cls  = animClassMap[type] || "qm-anim-pulse";

    // kalau masih animasi, reset dulu biar bisa retrigger cepat
    iconEl.classList.remove(cls);

    // force reflow → trik supaya anim bisa diulang walaupun class sama
    void iconEl.offsetWidth;

    iconEl.classList.add(cls);

    // lepas class saat anim selesai
    iconEl.addEventListener('animationend', function handler(){
      iconEl.classList.remove(cls);
      iconEl.removeEventListener('animationend', handler);
    });
  }

  // listener untuk setiap tile menu
  document.querySelectorAll('.quickmenu-item').forEach(function(item){
    item.addEventListener('click', function(e){
      // cari icon di dalam tile yg di-tap
      // prioritas: [data-anim] di bawah item yg diklik
      let iconEl = item.querySelector('[data-anim]');
      if (!iconEl && e.target && e.target.closest('[data-anim]')) {
        iconEl = e.target.closest('[data-anim]');
      }

      playAnim(iconEl);

      // NOTE:
      // - kalau item punya data-kategori (filter), jalankan filter mu di sini
      // - kalau item punya <a> (keranjang), biarkan default link jalan
      //   (biar anim masih main sebelum pindah halaman, kita tidak preventDefault)
      //   kalau kamu MAU cegah langsung redirect, uncomment preventDefault di bawah
      //
      // if (item.getAttribute('data-action') === 'cart') {
      //   e.preventDefault();
      //   setTimeout(()=>{ window.location = "<?= site_url('produk/cart') ?>"; }, 200);
      // }
    }, { passive: true });
  });
})();
</script>


<!-- FAB Cart (mobile) -->
<a
  href="<?= site_url('produk/cart') ?>"
  id="fab-cart"
  class="fab-cart"
  aria-label="Buka keranjang">

  <!-- spinner hidden by default -->
  <span class="spinner-border d-none" aria-hidden="true"></span>

  <!-- icon normal -->
  <i class="mdi mdi-cart-outline icon-default" aria-hidden="true"></i>

  <?php if (!empty($meja_info)): ?>
    <span class="fab-label d-none d-sm-inline">
      Meja <?= html_escape($meja_info) ?>
    </span>
  <?php endif; ?>

  <span class="fab-badge" id="fab-count">0</span>
</a>

<style>
  /* state saat loading */
  .fab-loading {
    pointer-events: none;
    opacity: .75;
  }

  /* spinner kecil */
  .spinner-border {
    display: inline-block;
    width: .9rem;
    height: .9rem;
    border: .15rem solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spin .6s linear infinite;
    vertical-align: -0.2em;
    margin-right: .4rem;
  }

  @keyframes spin {
    to { transform: rotate(360deg); }
  }

  /* util hide */
  .d-none {
    display: none !important;
  }
</style>

<script>
(function(){
  var fab = document.getElementById('fab-cart');
  if (!fab) return;

  fab.addEventListener('click', function(e){
    // kalau udah loading, jangan proses lagi
    if (fab.classList.contains('fab-loading')) {
      // cegah double tap super cepat (opsional)
      e.preventDefault();
      return;
    }

    // masuk mode loading visual
    fab.classList.add('fab-loading');

    var spinEl = fab.querySelector('.spinner-border');
    var iconEl = fab.querySelector('.icon-default');

    if (spinEl) spinEl.classList.remove('d-none');
    if (iconEl) iconEl.classList.add('d-none');

    // optional: ubah teks "Meja X" jadi "Memuat..." biar kelihatan banget
    var lbl = fab.querySelector('.fab-label');
    if (lbl && !lbl.dataset.orig) {
      lbl.dataset.orig = lbl.textContent;
      lbl.textContent = 'Memuat…';
    }

    // CATATAN:
    // kita TIDAK preventDefault di sini
    // supaya browser langsung lanjut ke href (/produk/cart)
    // jadi spinner kelihatan sebentar, lalu halaman cart kebuka
  }, {passive:true});
})();
</script>


<!-- vendor scripts -->
<script src="<?php echo base_url('assets/admin') ?>/js/vendor.min.js"></script>
<script src="<?php echo base_url('assets/admin') ?>/js/app.min.js"></script>
<script src="<?php echo base_url('assets/admin') ?>/js/sw.min.js"></script>

<?php $this->load->view("front_end/footer.php") ?>
<?php $this->load->view("modal_produk.php") ?>


<script>
(function(){
  // ==== KONFIG: endpoint subkategori ====
  const SUB_API = "<?= site_url('produk/subkategori/'); ?>";

  // ==== Cache elemen utama ====
  const $grid      = $('#grid-products');
  const $pagi      = $('#pagination-wrap');
  const $cartCount = $('#cart-count');
  const $fabCount  = $('#fab-count');

  // Pastikan hidden input sub_kategori ada di form
  if (!$('#sub_kategori').length){
    $('<input>', {
      type:'hidden',
      id:'sub_kategori',
      name:'sub_kategori',
      value:''
    }).appendTo('#filter-form');
  }

  // Buat container subkategori (badge2) tepat di bawah quickmenu
  let $subWrap = $('#subcat-wrap');
  if (!$subWrap.length){
    $subWrap = $('<div id="subcat-wrap" class="mb-2" role="navigation" aria-label="Subkategori"></div>');
    $('.quickmenu-wrap').first().after($subWrap);
  }
  $subWrap.hide().empty();

  // ========= UTIL / HELPER ==========

  // Skeleton placeholder saat loading produk
  function buildSkeleton(n){
    let html = '';
    for (let i=0; i<n; i++){
      html += `
        <div class="col-6 col-md-3 mb-3">
          <div class="skel-card">
            <div class="skel-thumb skel-shimmer"></div>
            <div class="skel-line w80 skel-shimmer"></div>
            <div class="skel-line w60 skel-shimmer"></div>
            <div class="skel-price skel-shimmer"></div>
            <div class="skel-btn skel-shimmer"></div>
          </div>
        </div>`;
    }
    return html;
  }

  function loading(on=true){
    if (on){
      $grid.html(buildSkeleton(8));
      $pagi.html('');
    }
  }

  function updateAllCartBadges(n){
    if ($cartCount && $cartCount.length) $cartCount.text(n);
    if ($fabCount && $fabCount.length)   $fabCount.text(n);
  }

  // Spinner/state di tombol "Tambah"
  function btnStartLoading($btn, loadingText){
    if (!$btn || !$btn.length) return;
    if ($btn.hasClass('btn-loading')) return;

    $btn.addClass('btn-loading');
    $btn.find('.spinner-border').removeClass('d-none'); // tampilkan spinner
    $btn.find('.icon-default').addClass('d-none');      // sembunyikan icon biasa

    const $txt = $btn.find('.btn-text');
    if ($txt.length){
      if (!$txt.data('orig')) {
        $txt.data('orig', $txt.text());
      }
      $txt.text(loadingText || 'Menambah...');
    }
  }

  function btnStopLoading($btn){
    if (!$btn || !$btn.length) return;
    if (!$btn.hasClass('btn-loading')) return;

    $btn.find('.spinner-border').addClass('d-none');
    $btn.find('.icon-default').removeClass('d-none');

    const $txt = $btn.find('.btn-text');
    if ($txt.length){
      const origText = $txt.data('orig');
      if (origText){
        $txt.text(origText);
      }
    }
    $btn.removeClass('btn-loading');
  }

  // Amanin qty minimal 1
  function safeQty(v){
    v = parseInt(v,10);
    return (isNaN(v) || v<1) ? 1 : v;
  }

  // Alert helper (pakai SweetAlert kalau ada)
  function notifySuccess(title, text){
    if (window.Swal){
      Swal.fire({
        icon:'success',
        title: title || 'Berhasil',
        text:  text  || '',
        timer:1500,
        showConfirmButton:false
      });
    } else {
      alert((title?title+': ':'')+(text||''));
    }
  }

  function notifyError(title, text){
    if (window.Swal){
      Swal.fire({
        icon:'error',
        title: title || 'Gagal',
        text:  text  || ''
      });
    } else {
      alert((title?title+': ':'')+(text||''));
    }
  }

  // Scroll sedikit ke area produk setelah user ganti kategori/halaman
  function scrollToGrid(){
    var el = document.getElementById('grandong');
    if (!el) return;
    var OFFSET = 70;
    var y = el.getBoundingClientRect().top + window.pageYOffset - OFFSET;
    window.scrollTo({ top: y, behavior: 'smooth' });
  }

  // Ambil nilai filter (q/kategori/sub/sort/page/seed) untuk AJAX
  function serializeFilters(page=1){
    const q            = $('#q').val() || '';
    const kategori     = $('#kategori').val() || '';
    const sub_kategori = $('#sub_kategori').val() || '';
    const sort         = $('#sort').val() || 'random';
    const per_page     = 12;

    // seed random untuk mode "For You"
    const url = new URL(window.location.href);
    let seed = url.searchParams.get('seed');
    if (!seed && sort === 'random'){
      seed = String(Math.floor(Math.random()*1e9));
      url.searchParams.set('seed', seed);
      history.replaceState({}, '', url.toString());
    }

    return { q, kategori, sub_kategori, sort, page, per_page, seed };
  }

  // Render daftar produk
  function loadProducts(page=1, pushUrl=true){
    loading(true);
    const params = serializeFilters(page);

    $.getJSON("<?= site_url('produk/list_ajax'); ?>", params)
      .done(function(r){
        if (!r || !r.success){
          $grid.html('<div class="col-12 alert alert-danger">Gagal memuat data.</div>');
          return;
        }

        $grid.html(r.items_html);
        $pagi.html(r.pagination_html);

        if (pushUrl){
          const url = new URL(window.location.href);
          url.searchParams.set('q',        params.q);
          url.searchParams.set('kategori', params.kategori);
          url.searchParams.set('sub',      params.sub_kategori);
          url.searchParams.set('sort',     params.sort);
          url.searchParams.set('page',     r.page);
          url.searchParams.set('seed',     params.seed);
          history.pushState(params, '', url.toString());
        }

        bindAddToCart();
        bindPagination();
        // kalau nanti punya modal detail produk:
        bindDetailModal();
      })
      .fail(function(){
        $grid.html('<div class="col-12 alert alert-danger">Koneksi bermasalah.</div>');
      });
  }

  // Klik pagination
  function bindPagination(){
    $('#pagination-wrap')
      .off('click', 'a[data-page]')
      .on('click', 'a[data-page]', function(e){
        e.preventDefault();
        const p = parseInt($(this).data('page') || 1, 10);
        loadProducts(p);
        scrollToGrid();
      });
  }

  // Tambah ke keranjang dari card produk (pakai spinner tombol)
  function bindAddToCart(){
    $('#grid-products')
      .off('click', '.btn-add-cart')
      .on('click', '.btn-add-cart', function(e){
        e.preventDefault();

        const $btn = $(this);

        // anti spam double click
        if ($btn.hasClass('btn-loading')) return;
        if ($btn.is(':disabled')) return;

        const id  = $btn.data('id');
        const qty = safeQty($btn.data('qty'));

        btnStartLoading($btn, 'Menambah...');

        $.ajax({
          url: "<?= site_url('produk/add_to_cart'); ?>",
          type: "POST",
          dataType: "json",
          data: { id, qty },
        })
        .done(function(r){
          if (!r || !r.success){
            notifyError(r?.title || 'Oops!', r?.pesan || 'Gagal menambahkan');
            return;
          }

          updateAllCartBadges(r.count);
          notifySuccess(
            r.title || 'Mantap!',
            r.pesan || 'Item masuk keranjang'
          );
        })
        .fail(function(){
          notifyError('Error', 'Gagal terhubung ke server');
        })
        .always(function(){
          btnStopLoading($btn);
        });
      });
  }

  // Hitung isi keranjang awal
  function loadCartCount(){
    $.getJSON("<?= site_url('produk/cart_count'); ?>")
      .done(function(r){
        if (r && r.success){
          updateAllCartBadges(r.count);
        }
      });
  }

  // Label tombol sort
  function setSortLabel(val){
    const map = {
      'random'      : 'For You',
      'new'         : 'Terbaru',
      'bestseller'  : 'Terlaris',
      'price_low'   : 'Harga Rendah',
      'price_high'  : 'Harga Tinggi',
      'sold_out'    : 'Habis'
    };
    $('#sortBtnLabel').text(map[val] || 'Urutkan');
  }

  // Highlight kategori aktif di quickmenu
  function markActiveKategori(){
    const val = String($('#kategori').val() || '');
    $('#quickmenu .quickmenu-item')
      .not('[data-action="cart"]')
      .removeClass('active')
      .filter(function(){
        return String($(this).data('kategori') || '') === val;
      })
      .addClass('active');
  }

  // ====== Subkategori badge logic ======
  function hideSubcats(){
    $subWrap.hide().empty();
  }

  function markActiveSub(subId){
    const sid = String(subId || '');

    // default style semua jadi badge-blue
    $subWrap.find('.subcat-badge')
      .removeClass('badge-dark text-white active')
      .addClass('badge-blue');

    if (sid === ''){
      $subWrap.find('.subcat-badge[data-sub=""]')
        .removeClass('badge-blue')
        .addClass('badge-dark text-white active');
    } else {
      $subWrap.find('.subcat-badge[data-sub="'+sid+'"]')
        .removeClass('badge-blue')
        .addClass('badge-dark text-white active');
    }
  }

  function renderSubBadges(list, selectedId){
    let html = '';

    // badge "Semua"
    html += `
      <a
        href="#"
        class="badge badge-pill subcat-badge badge-dark text-white mr-1"
        data-sub="">
        Semua
      </a>`;

    (list || []).forEach(it => {
      html += `
        <a
          href="#"
          class="badge badge-pill subcat-badge badge-blue mr-1"
          data-sub="${it.id}">
          ${it.nama}
        </a>`;
    });

    $subWrap.html(html).show();
    markActiveSub(selectedId);
  }

  function fetchAndRenderSubcats(kategoriId){
    // loader kecil sementara
    $subWrap.html(
      '<div class="d-inline-flex align-items-center rounded px-2 py-1 bg-light border small text-muted" style="line-height:1.2;">' +
      '  <span class="spinner-border spinner-border-sm mr-2" role="status" ' +
      '        style="width:0.9rem;height:0.9rem;border-width:0.15rem;border-right-color:transparent;"></span>' +
      '  <span>Memuat subkategori…</span>' +
      '</div>'
    ).show();

    $.getJSON(SUB_API + String(kategoriId))
      .done(function(r){
        const currentSelected = $('#sub_kategori').val() || '';
        if (r && r.success && Array.isArray(r.data) && r.data.length){
          renderSubBadges(r.data, currentSelected);
        } else {
          hideSubcats();
        }
      })
      .fail(function(){
        hideSubcats();
      });
  }

  // ====== EVENT HANDLERS ======

  // Ketik di search (debounce)
  let typingTimer = null;
  $('#q')
    .on('input', function(){
      clearTimeout(typingTimer);
      typingTimer = setTimeout(function(){
        loadProducts(1);
      }, 350);
    })
    .on('keydown', function(e){
      if(e.key === 'Enter'){
        e.preventDefault();
        clearTimeout(typingTimer);
        loadProducts(1);
      }
    });

  // Tombol manual cari (kalau ada #btn-search)
  $(document).on('click', '#btn-search', function(e){
    e.preventDefault();
    loadProducts(1);
  });

  // Tombol reset → bersihkan semua filter termasuk sub_kategori
  $(document).on('click', '#btn-reset', function(e){
    e.preventDefault();
    $('#q').val('');
    $('#kategori').val('');
    $('#sub_kategori').val('');
    $('#sort').val('random');
    setSortLabel('random');
    markActiveKategori();

    const url = new URL(window.location.href);
    url.searchParams.delete('seed');
    url.searchParams.delete('sub');
    history.replaceState({}, '', url.toString());

    hideSubcats();
    loadProducts(1);
  });

  // Klik opsi sort
  $(document).on('click', '.sort-opt', function(e){
    e.preventDefault();
    const val = $(this).data('sort');
    $('#sort').val(val);
    setSortLabel(val);

    if (val === 'random'){
      // hapus seed biar regeneration
      const url = new URL(window.location.href);
      url.searchParams.delete('seed');
      history.replaceState({}, '', url.toString());
    }

    loadProducts(1);
  });

  // Klik kategori di quickmenu
  $('#quickmenu').on('click', '.quickmenu-item', function(e){
    if ($(this).data('action') === 'cart') return; // biarkan cart link jalan normal
    e.preventDefault();

    const kat = String($(this).data('kategori') || '');
    $('#kategori').val(kat);
    $('#sub_kategori').val(''); // reset sub setiap ganti kategori

    markActiveKategori();
    loadProducts(1);

    if (kat){
      fetchAndRenderSubcats(kat);
    } else {
      hideSubcats();
    }

    scrollToGrid();
  });

  // Klik badge subkategori
  $(document).on('click', '.subcat-badge', function(e){
    e.preventDefault();
    const sid = String($(this).data('sub') || '');
    $('#sub_kategori').val(sid);
    markActiveSub(sid);
    loadProducts(1);
    scrollToGrid();
  });

  // Klik FAB cart → spinner kecil biar terasa responsif (punyamu sendiri, tetap dipakai)
  (function(){
    var fab = document.getElementById('fab-cart');
    if (!fab) return;

    fab.addEventListener('click', function(e){
      if (fab.classList.contains('fab-loading')){
        // udah loading → cegah spam double tap
        e.preventDefault();
        return;
      }

      fab.classList.add('fab-loading');

      var spinEl = fab.querySelector('.spinner-border');
      var iconEl = fab.querySelector('.icon-default');

      if (spinEl) spinEl.classList.remove('d-none');
      if (iconEl) iconEl.classList.add('d-none');

      var lbl = fab.querySelector('.fab-label');
      if (lbl && !lbl.dataset.orig){
        lbl.dataset.orig = lbl.textContent;
        lbl.textContent = 'Memuat…';
      }

      // nggak preventDefault → browser lanjut ke href cart
    }, {passive:true});
  })();

  // SweetAlert saat user klik "keluar dari Dine-in"
  $(document).on('click', '.js-leave-table', function(e){
    e.preventDefault();
    const url = this.href;

    if (window.Swal){
      Swal.fire({
        icon: 'warning',
        title: 'Keluar dari Meja?',
        html: `
          Santai, kamu bisa lanjut belanja dari rumah — pesanan bisa kami <b>antar</b> (Delivery) atau <b>dibungkus</b> (Takeaway). 😉<br><br>
          <small style="display:inline-block;margin-top:.25rem;color:#6b7280">
            Kalau masih mau makan di tempat, <b>scan ulang barcode di meja</b> ya. 🍽️📱
          </small>
        `,
        showCancelButton: true,
        confirmButtonText: 'Iya, keluar',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        focusCancel: true
      }).then((res)=>{
        if (res.isConfirmed){
          Swal.fire({
            icon:'success',
            title:'Keluar dari Dine-in',
            text:'Mode diubah. Lanjut belanja sebagai Delivery/Takeaway. 🙌',
            timer: 900,
            showConfirmButton: false
          });

          setTimeout(()=>{ window.location.href = url; }, 300);
        }
      });
    } else {
      if (confirm('Keluar dari mode Dine-in? Kalau masih mau makan di tempat, scan ulang barcode di meja ya.')){
        window.location.href = url;
      }
    }
  });

  // Add to cart dari dalam modalProduk (pakai qty input)
  $('#modalProduk')
    .off('click', '#btn-add-cart-modal')
    .on('click', '#btn-add-cart-modal', function(e){
      e.preventDefault();
      const $btn = $(this);
      const id   = $btn.data('id');
      const qty  = safeQty($('#qty-modal').val());

      $btn.prop('disabled', true);

      $.ajax({
        url: "<?= site_url('produk/add_to_cart'); ?>",
        type: "POST",
        dataType: "json",
        data: { id, qty },
      })
      .done(function(r){
        if (!r || !r.success){
          notifyError(r?.title || 'Oops!', r?.pesan || 'Gagal menambahkan');
          return;
        }

        const n = r.count || 0;
        if ($fabCount && $fabCount.length) $fabCount.text(n);
        if ($cartCount && $cartCount.length) $cartCount.text(n);

        $('#modalProduk').one('hidden.bs.modal', function(){
          notifySuccess(r.title || 'Mantap!', r.pesan || 'Item masuk keranjang');
        });

        $('#modalProduk').modal('hide');
      })
      .fail(function(){
        notifyError('Error', 'Gagal terhubung ke server');
      })
      .always(function(){
        $btn.prop('disabled', false);
      });
    });

  // ====== INIT HALAMAN ======
  $(function(){
    loadCartCount();
    markActiveKategori();
    $('#dropdownSortBtn').dropdown();

    // Tarik parameter dari URL (refresh / direct link)
    const url = new URL(window.location.href);

    if (url.searchParams.has('q'))        $('#q').val(url.searchParams.get('q'));
    if (url.searchParams.has('kategori')) $('#kategori').val(url.searchParams.get('kategori'));
    if (url.searchParams.has('sub'))      $('#sub_kategori').val(url.searchParams.get('sub'));
    if (url.searchParams.has('sort'))     $('#sort').val(url.searchParams.get('sort'));

    // Update label sort sesuai nilai awal
    setSortLabel($('#sort').val() || 'random');
    markActiveKategori();

    // Kalau kategori terpilih saat load → muat subkategorinya
    const katInit = $('#kategori').val();
    if (katInit){
      fetchAndRenderSubcats(katInit);
    } else {
      hideSubcats();
    }

    const firstPage = parseInt(url.searchParams.get('page') || '1', 10);
    loadProducts(firstPage, false);

    // === ALERT MODE (pakai localStorage agar gak muncul terus, tanpa sessionStorage) ===
    const $modeInfo   = $('#mode-info');
    const curModeRaw  = ($modeInfo.data('mode') || '').toString().toLowerCase();
    const mejaLabel   = ($modeInfo.data('meja') || '').toString();

    // 1. Mode cantik buat judul
    let modeNice = '';
    if (curModeRaw === 'dinein' || curModeRaw === 'dine-in'){
      modeNice = (mejaLabel !== ''
        ? 'Dine-in di ' + mejaLabel
        : 'Dine-in');
    } else if (curModeRaw === 'delivery'){
      modeNice = 'Delivery';
    } else if (curModeRaw === 'walkin'){
      modeNice = 'Takeaway/Bungkus';
    } else {
      modeNice = 'Belanja biasa';
    }

    // 2. Pesan HTML
    let htmlMsg = '';
    if (curModeRaw === 'dinein' || curModeRaw === 'dine-in'){
      htmlMsg = `
        Kamu saat ini <b>${modeNice}</b> 👋<br>
        Pesanan akan dicatat ke meja kamu.<br><br>
        <small style="color:#6b7280;display:inline-block;margin-top:.25rem;">
          Mau pindah jadi Delivery / Takeaway? Pakai tombol keluar di atas (ikon keluar meja).
        </small>
      `;
    } else if (curModeRaw === 'delivery'){
      htmlMsg = `
        Kamu saat ini mode <b>${modeNice}</b> 🚚<br>
        Kami bisa antar pesananmu ke alamat kamu.
      `;
    } else if (curModeRaw === 'walkin'){
      htmlMsg = `
        Kamu saat ini mode <b>${modeNice}</b> 👜<br>
        Pesananmu akan dibungkus untuk diambil.
      `;
    } else {
      htmlMsg = `
        Kamu belanja sebagai <b>${modeNice}</b> 🛍️
      `;
    }

    // 3. Ambil mode terakhir yang SUDAH PERNAH ditunjukkan ke user.
    //    Pakai localStorage biar bertahan antar reload/tab.
    //    (Bukan sessionStorage lagi)
    let lastShown = '';
    try {
      lastShown = localStorage.getItem('lastModeShown') || '';
    } catch (e) {
      // kalau browser aneh / private mode ketat, biarin lastShown = ''
    }

    // 4. Cegah spam di halaman yg sama:
    //    kita pakai flag runtime supaya Swal gak kebuka dua kali
    //    kalau script ini kebetulan dieksekusi dua kali.
    if (typeof window.__MODE_ALERT_SHOWN === 'undefined') {
      window.__MODE_ALERT_SHOWN = false;
    }

    // 5. Logika tampil:
    //    - hanya tampil kalau mode sekarang beda dari yg terakhir pernah ditampilkan
    //    - dan di halaman ini belum pernah ditampilkan
    const shouldShowAlert = (!window.__MODE_ALERT_SHOWN && curModeRaw !== lastShown);

    // 6. Tampilkan Swal sekali
    if (shouldShowAlert && window.Swal) {
      Swal.fire({
        icon: 'info',
        title: modeNice,
        html: htmlMsg,
        confirmButtonText: 'Oke',
        width: 320
      });

      // tandai bahwa kita sudah munculkan alert di halaman ini
      window.__MODE_ALERT_SHOWN = true;

      // simpan mode ini supaya reload berikutnya gak muncul lagi
      try {
        localStorage.setItem('lastModeShown', curModeRaw);
      } catch (e) {}
    }
    // === END ALERT MODE ===
  });

  // Restore state saat user pakai tombol Back/Forward browser
  window.addEventListener('popstate', function(e){
    const s = e.state || {};
    $('#q').val(s.q || '');
    $('#kategori').val(s.kategori || '');
    $('#sub_kategori').val(s.sub_kategori || '');
    $('#sort').val(s.sort || 'random');
    setSortLabel($('#sort').val());
    markActiveKategori();

    if (s.kategori){
      fetchAndRenderSubcats(s.kategori);
    } else {
      hideSubcats();
    }

    loadProducts(parseInt(s.page || 1,10), false);
  });

})(); // END big IIFE


/* ===== Scroll hint quickmenu: fade kiri/kanan ===== */
(function(){
  const q = document.getElementById('quickmenu');
  if (!q) return;
  const wrap = q.closest('.quickmenu-wrap');

  function updateQuickmenuShadows(){
    const maxScroll = q.scrollWidth - q.clientWidth;
    const x = Math.round(q.scrollLeft);
    wrap.classList.toggle('show-left',  x > 0);
    wrap.classList.toggle('show-right', x < (maxScroll - 1));
  }

  q.addEventListener('scroll', updateQuickmenuShadows, {passive:true});
  window.addEventListener('resize', updateQuickmenuShadows);

  document.addEventListener('DOMContentLoaded', updateQuickmenuShadows);
  setTimeout(updateQuickmenuShadows, 600);

  // "nudge" kecil biar user sadar bisa digeser
  let nudged = false;
  setTimeout(function(){
    const maxScroll = q.scrollWidth - q.clientWidth;
    if (maxScroll > 8 && !nudged){
      nudged = true;
      q.scrollBy({ left: 48, behavior: 'smooth' });
      setTimeout(()=> q.scrollBy({ left: -48, behavior: 'smooth' }), 350);
    }
  }, 800);
})();

/* (opsional) killer masker lama EasyUI, dll */
window.killMasks = function () {
  $('.window-mask, .messager-mask, .datagrid-mask, .easyui-mask, .mm-wrapper__blocker')
    .css('pointer-events','none')
    .hide();
};
</script>
