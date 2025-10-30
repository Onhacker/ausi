<?php
$img = $product->gambar
  ? (strpos($product->gambar, 'http') === 0 ? $product->gambar : base_url($product->gambar))
  : base_url('assets/images/products/no-image.png');

$stok    = (int)($product->stok ?? 0);
$satuan  = $product->satuan ?: 'pcs';
$katNama = $product->kategori_nama ?: '';
$harga   = (float)($product->harga ?? 0);
?>
<style>
  /* ===== Ultra-Compact, title wrap ===== */
  :root{
    --ink:#0f172a; --muted:#6b7280; --line:rgba(15,23,42,.08);
    --chip-bg:#f8fafc; --ok:#10b981; --bad:#ef4444; --brand:#1e88e5;
    --card:#fff; --shadow:0 4px 14px rgba(2,6,23,.07);
    --radius:10px;
  }
  .modal-product{ --gap:10px; }

  .modal-product .img-wrap{
    border-radius:var(--radius); overflow:hidden; background:#f1f5f9;
    box-shadow:var(--shadow);
  }
  .modal-product .img-fluid{ display:block; width:100%; height:auto; object-fit:cover; }

  .category{
    color:var(--brand); font-weight:700; font-size:.78rem; letter-spacing:.15px;
    margin-bottom:.15rem;
  }

  .header-line{
    display:flex; align-items:flex-start; justify-content:space-between; gap:.5rem; flex-wrap:wrap;
    margin:.1rem 0 .2rem;
  }
  .product-title{
    margin:0; font-weight:800; line-height:1.15; color:var(--ink);
    font-size:1rem;
    white-space:normal;
    overflow:visible; text-overflow:clip;
    word-break:break-word;
  }
  .price-tag{
    font-size:1.05rem; font-weight:900; color:var(--ink); white-space:nowrap;
  }
  .price-tag small{ font-weight:600; color:var(--muted); font-size:.8rem; margin-left:.25rem; }

  .meta-line{
    display:flex; align-items:center; gap:.35rem; flex-wrap:wrap; margin:.15rem 0 .35rem;
  }
  .chip{
    display:inline-flex; align-items:center; gap:.32rem;
    padding:.18rem .45rem; border-radius:999px; font-size:.74rem; font-weight:600;
    border:1px solid var(--line); background:var(--chip-bg); color:#111827;
  }
  .dot{ width:.44rem; height:.44rem; border-radius:50%; display:inline-block; }
  .dot.on{ background:var(--ok); } .dot.off{ background:var(--bad); }

  .desc-title{ font-size:.8rem; font-weight:700; color:#334155; margin:.1rem 0 .25rem; }
  .desc-box{
    max-height:140px; overflow:auto; border:1px solid var(--line);
    border-radius:9px; padding:.6rem; background:var(--card); margin-bottom:.6rem;
    box-shadow:0 1px 6px rgba(2,6,23,.04);
  }
  .desc-box p{ margin-bottom:.4rem; }
  .desc-box::-webkit-scrollbar{ width:6px; }
  .desc-box::-webkit-scrollbar-thumb{ background:rgba(0,0,0,.12); border-radius:999px; }

  /* Qty group super ringkas */
  .qty-group{
    border-radius:10px; overflow:hidden; max-width:520px; box-shadow:0 2px 10px rgba(2,6,23,.05);
  }
  .qty-group .input-group-text{ border:none; background:#f8fafc; font-weight:700; font-size:.78rem; padding:.35rem .5rem; }
  .qty-group .btn{ min-width:36px; font-weight:800; padding:.35rem .5rem; font-size:.86rem; }
  .qty-group input[type="number"]{
    text-align:center; font-weight:800; border-left:none; border-right:none;
    padding:.35rem .25rem; height:34px; font-size:.9rem;
  }
  .btn-blue{ background:var(--brand); border-color:var(--brand); color:#fff; font-weight:800; }
  .btn-blue:hover{ filter:brightness(.97); }

  /* mikro utilitas */
  .mb-1{ margin-bottom:.2rem!important; }
  .mb-2{ margin-bottom:.4rem!important; }

  /* Responsif: lebih padat di mobile */
  @media (max-width: 991.98px){
    .product-title{ font-size:.98rem; }
    .price-tag{ font-size:1rem; }
  }
  @media (max-width: 767.98px){
    .modal-product{ --gap:8px; }
    .desc-box{ max-height:120px; }
    .qty-group .btn{ min-width:34px; padding:.32rem .45rem; font-size:.84rem; }
    .btn-blue{ font-size:.86rem; padding:.38rem .6rem; }
  }
</style>

<div class="row modal-product align-items-start" style="row-gap: var(--gap); column-gap: var(--gap);">
  <div class="col-md-5 mb-1 mb-md-0">
    <div class="img-wrap">
      <img src="<?= $img; ?>" alt="<?= html_escape($product->nama); ?>" class="img-fluid" loading="lazy">
    </div>
  </div>

  <div class="col-md-12">
    <?php if ($katNama): ?>
      <div class="category"><?= html_escape($katNama); ?></div>
    <?php endif; ?>

    <!-- Header -->
    <div class="header-line">
      <h4 class="product-title"><?= html_escape(ucwords($product->nama)); ?></h4>
      <div class="price-tag">
        Rp <?= number_format($harga, 0, ',', '.'); ?>
        <small>/ <?= html_escape($satuan); ?></small>
      </div>
    </div>

    <!-- Meta + Qty + CTA sejajar (col-4 / col-4 / col-4) -->
    <div class="row align-items-center no-gutters mb-2">
      <!-- Meta (Instock/Sold Out) -->
      <div class="col-4">
        <div class="meta-line mb-0">
          <?php if ($stok > 0): ?>
            <span class="chip" title="Stok tersedia"><span class="dot on"></span> Instock</span>
          <?php else: ?>
            <span class="chip" title="Stok habis"><span class="dot off"></span> Sold Out</span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Qty group (ikon +/- dan input) -->
      <div class="col-4 px-1">
        <div class="input-group qty-group input-group-sm">
          <div class="input-group-prepend d-none d-md-flex">
            <span class="input-group-text">Qty</span>
          </div>

          <div class="input-group-prepend">
            <button class="btn btn-outline-secondary" type="button" id="qty-dec" <?= $stok <= 0 ? 'disabled' : ''; ?>>−</button>
          </div>

          <input
            type="number"
            class="form-control"
            id="qty-modal"
            value="1"
            inputmode="numeric"
            pattern="[0-9]*"
            aria-label="Jumlah"
            <?= $stok > 0 ? 'min="1" max="'.(int)$stok.'"' : 'disabled'; ?>
          >

          <div class="input-group-append">
            <button class="btn btn-outline-secondary" type="button" id="qty-inc" <?= $stok <= 0 ? 'disabled' : ''; ?>>+</button>
          </div>
        </div>
      </div>

      <!-- CTA (ikon saja di mobile) -->
      <div class="col-4 text-right">
        <button type="button"
                class="btn btn-blue btn-sm w-100"
                id="btn-add-cart-modal"
                data-id="<?= (int)$product->id; ?>"
                <?= $stok <= 0 ? 'disabled' : ''; ?>>
          <i class="mdi mdi-cart"></i>
          <span class="d-none d-sm-inline"> Tambah ke Keranjang</span>
        </button>
      </div>
    </div>

    <!-- Deskripsi -->
    <div class="desc-title">Komposisi / Deskripsi</div>
    <div class="desc-box">
      <?= $product->deskripsi ?: '<p class="text-muted m-0">Belum ada deskripsi.</p>'; ?>
    </div>
  </div>
</div>

<script>
/* JS tetap sama: tidak diubah */
</script>

<script>
(function(){
  const input = document.getElementById('qty-modal');
  const inc   = document.getElementById('qty-inc');
  const dec   = document.getElementById('qty-dec');
  if (!input) return;

  const maxAttr = parseInt(input.getAttribute('max'), 10);
  const hasMax  = Number.isFinite(maxAttr) && maxAttr > 0;

  const toInt = (v)=> {
    const n = parseInt(String(v).replace(/\D+/g, ''), 10);
    return Number.isFinite(n) ? n : 1;
  };

  function syncButtons(v){
    if (!inc || !dec) return;
    dec.disabled = (v <= 1) || input.disabled;
    inc.disabled = (hasMax && v >= maxAttr) || input.disabled;
  }

  function clamp(){
    let v = toInt(input.value);
    if (v < 1) v = 1;
    if (hasMax && v > maxAttr) v = maxAttr;
    input.value = v;
    syncButtons(v);
    return v;
  }

  inc && inc.addEventListener('click', function(){
    let v = clamp();
    if (!hasMax || v < maxAttr) input.value = ++v;
    syncButtons(v);
  });

  dec && dec.addEventListener('click', function(){
    let v = clamp();
    if (v > 1) input.value = --v;
    syncButtons(v);
  });

  input.addEventListener('input', clamp);
  input.addEventListener('blur', clamp);

  // Enter = trigger Tambah ke Keranjang
  input.addEventListener('keydown', function(e){
    if (e.key === 'Enter'){
      e.preventDefault();
      const btn = document.getElementById('btn-add-cart-modal');
      if (btn && !btn.disabled) btn.click();
    }
  });

  // Init
  clamp();
})();
</script>
