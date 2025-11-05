<?php $this->load->view("front_end/head.php"); ?>

<?php
$img = $product->gambar
  ? ((strpos($product->gambar,'http')===0) ? $product->gambar : base_url($product->gambar))
  : base_url('assets/images/products/no-image.png');

$stok    = (int)($product->stok ?? 0);
$satuan  = $product->satuan ?: 'pcs';
$katNama = $product->kategori_nama ?: '';
$harga   = (float)($product->harga ?? 0);
$slug    = $product->link_seo ?? '';

// Agregat rating dari controller
$ratingAvg   = isset($product->rating_avg)   ? (float)$product->rating_avg   : 0.0;
$ratingCount = isset($product->rating_count) ? (int)$product->rating_count   : 0;

// Hitung ikon penuh/half/kosong
$_rounded = round(max(0,min(5,$ratingAvg))*2)/2;
$_full  = (int)floor($_rounded);
$_half  = (($_rounded - $_full) == 0.5);
$_empty = 5 - $_full - ($_half ? 1 : 0);
?>

<style>
  :root{
    --ink:#0f172a; --muted:#64748b; --line:rgba(15,23,42,.08);
    --brand:#1e88e5; --ok:#10b981; --bad:#ef4444;
  }
  .pd-wrap .page-title{ font-weight:800; letter-spacing:.2px; }
  .pd-card{ border-radius:12px; }
  .pd-img{ border-radius:12px; overflow:hidden; background:#f1f5f9; }
  .pd-meta{ color:var(--muted); }
  .pd-price{ font-weight:900; font-size:1.25rem; color:#111827; }
  .pd-price small{ font-weight:600; color:#6b7280; }

  /* Rating row */
  .rate-row{ display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; margin:.25rem 0 .75rem; }
  .star-meter{ display:flex; align-items:center; gap:2px; cursor:pointer; }
  .star-meter .mdi{ font-size:18px; line-height:1; vertical-align:middle; }
  .star-meter .full{ color:#f59e0b; } .star-meter .empty{ color:#cbd5e1; }
  .rate-label{ font-size:.9rem; color:#111827; font-weight:700; }
  .rate-info{ font-size:.85rem; color:var(--muted); }
  .rate-link{ font-weight:700; color:#2563eb; cursor:pointer; text-decoration:underline; }
  .rate-link:hover{ text-decoration:none; }

  /* Qty + CTA */
  .btn .btn-text{ display:inline; }
  @media (max-width: 575.98px){
    /* icon-only di mobile */
    .btn .btn-text{ display:none !important; }
  }

  /* Qty group (− input +) */
  .qty-group{
    border-radius:10px;
    overflow:hidden;
    max-width:220px;
    box-shadow:0 2px 10px rgba(2,6,23,.05);
  }
  .qty-group .btn{
    min-width:36px;
    font-weight:800;
    padding:.35rem .5rem;
    font-size:.86rem;
    border:none;
    background:#f8fafc;
  }
  .qty-group input[type="number"]{
    text-align:center;
    font-weight:800;
    border-left:none;
    border-right:none;
    padding:.35rem .25rem;
    height:34px;
    font-size:.9rem;
  }

  /* Reviews */
  #ulasan{ scroll-margin-top: 80px; }
  .rv-list{ border:1px solid var(--line); border-radius:10px; padding:.75rem; }
  .rv-item{ display:flex; gap:.6rem; padding:.5rem 0; border-bottom:1px dashed var(--line); }
  .rv-item:last-child{ border-bottom:none; }
  .rv-stars .mdi{ font-size:14px; } .rv-stars .full{ color:#f59e0b; }
  .rv-meta{ font-size:.76rem; color:var(--muted); }
  .rv-text{ margin:.15rem 0 0; font-size:.92rem; color:#111827; white-space:pre-line; }

  /* === Cart FAB === */
  .cart-fab{
    position: fixed;
    right: 16px;
    bottom: calc(70px + env(safe-area-inset-bottom));
    width: 56px; height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
    color:#fff; text-decoration:none;
    display:inline-flex; align-items:center; justify-content:center;
    box-shadow: 0 12px 28px rgba(0,0,0,.18), 0 6px 12px rgba(0,0,0,.12);
    z-index:1060;
  }
  .cart-fab .mdi{ font-size:24px; line-height:1; }
  .cart-fab .fab-badge{
    position:absolute; top:-6px; right:-6px;
    min-width:20px; height:20px; padding:0 6px;
    border-radius:999px; background:#111827; color:#fff;
    font-size:12px; font-weight:700;
    display:flex; align-items:center; justify-content:center;
    box-shadow:0 2px 6px rgba(0,0,0,.25);
  }
  .cart-fab.bump{ animation: fab-bump .4s ease; }
  @keyframes fab-bump { 0%{transform:scale(1)} 35%{transform:scale(1.12)} 100%{transform:scale(1)} }
  @media (min-width: 992px){
    .cart-fab{ width:52px; height:52px; }
  }

  .spinner-border{
  display:inline-block;width:1rem;height:1rem;
  border:.15rem solid currentColor;border-right-color:transparent;
  border-radius:50%;animation:spin .6s linear infinite;vertical-align:-0.2em;
  margin-right:.4rem;
}
@keyframes spin{to{transform:rotate(360deg)}}
#btn-add-cart-detail.is-loading{ pointer-events:none; opacity:.9; }
/* Avatar & nama di daftar ulasan (detail) */
.rv-item{ display:flex; gap:.6rem; padding:.5rem 0; border-bottom:1px dashed var(--line); }
.rv-item:last-child{ border-bottom:none; }
.rv-avatar{
  width:36px;height:36px;border-radius:50%;
  background:#e2e8f0;color:#0f172a;font-weight:800;
  display:flex;align-items:center;justify-content:center;flex:0 0 36px;
}
.rv-head{ display:flex;justify-content:space-between;align-items:center;gap:.5rem; }
.rv-name{ font-weight:700;color:#0f172a;font-size:.85rem; }

</style>

<div class="container-fluid pd-wrap">

  <!-- page title -->
  <div class="hero-title" role="banner" aria-label="Judul situs">
    <?php $this->load->view("front_end/back") ?>
    <h1 class="text"><?php echo $title ?></h1>
    <span class="accent" aria-hidden="true"></span>
  </div>

  <!-- content -->
  <div class="row">
    <div class="col-12">
      <div class="card-box pd-card">
        <div class="row">
          <!-- Left: image -->
          <div class="col-xl-5">
            <div class="pd-img mb-2">
              <img src="<?= $img; ?>" alt="<?= html_escape($product->nama); ?>" class="img-fluid mx-auto d-block">
            </div>
          </div>

          <!-- Right: info -->
          <div class="col-xl-7">
            <div class="pl-xl-3 mt-3 mt-xl-0">

              <!-- kategori -->
              <?php if ($katNama): ?>
                <a href="<?= site_url('produk').'?kategori='.$product->kategori_id; ?>" class="text-blue font-weight-bold">
                  <?= html_escape($katNama); ?>
                </a>
              <?php endif; ?>

              <!-- nama -->
              <h3 class="mb-2 mt-1"><?= html_escape($product->nama); ?></h3>

              <!-- rating row (klik bintang/teks untuk beri rating) -->
              <div class="rate-row" data-rate-box-detail data-id="<?= (int)$product->id; ?>" data-name="<?= html_escape($product->nama); ?>">
                <div class="star-meter" aria-label="Rating: <?= number_format($ratingAvg,1,',','.'); ?>/5" title="Beri rating">
                  <?php for ($i=0; $i<$_full; $i++): ?><i class="mdi mdi-star full"></i><?php endfor; ?>
                  <?php if ($_half): ?><i class="mdi mdi-star-half-full full"></i><?php endif; ?>
                  <?php for ($i=0; $i<$_empty; $i++): ?><i class="mdi mdi-star-outline empty"></i><?php endfor; ?>
                </div>
                <div class="rate-info">
                  <span class="avg-label"><?= number_format($ratingAvg,1,',','.'); ?></span>/5 ·
                  <span class="count-label"><?= (int)$ratingCount; ?></span> ulasan
                </div>
                <span class="rate-link"><i class="mdi mdi-fountain-pen-tip"></i>Beri rating</span>
              </div>

              <!-- harga -->
              <div class="pd-price mb-2">
                Rp <?= number_format($harga, 0, ',', '.'); ?>
                <small>/ <?= html_escape($satuan); ?></small>
              </div>

              <!-- stok -->
              <div class="mb-1">
                <?php if ($stok > 0): ?>
                  <span class="badge bg-soft-success text-success">Instock</span>
                <?php else: ?>
                  <span class="badge bg-soft-danger text-danger">Sold Out</span>
                <?php endif; ?>
              </div>

              <!-- deskripsi -->
              <div class="mb-3">
                <?= $product->deskripsi ?: '<p class="text-muted">Belum ada deskripsi.</p>'; ?>
              </div>

              <!-- Qty (− input +) + CTA -->
              <div class="d-flex align-items-center flex-wrap qty-inline mb-3">
                <label class="my-1 mr-2" for="qty-detail">Qty</label>

                <div class="input-group qty-group my-1 mr-3">
                  <div class="input-group-prepend">
                    <button class="btn btn-outline-secondary" type="button" id="qty-dec" <?= $stok <= 0 ? 'disabled' : ''; ?>>−</button>
                  </div>
                  <input
                    type="number"
                    class="form-control"
                    id="qty-detail"
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

              <!--   <a href="<?= site_url('produk'); ?>" class="btn btn-light mr-2 my-1">
                  <span ><i class="mdi mdi-arrow-left"></i></span>
                  <span class="btn-text">Kembali</span>
                </a>
              -->
              <button type="button"
              class="btn btn-block btn-danger my-1"
              id="btn-add-cart-detail"
              data-loading-label="Menambahkan…"
              <?= $stok <= 0 ? 'disabled' : ''; ?>>
              <span class="spinner-border d-none" aria-hidden="true"></span>
              <i class="mdi mdi-cart mr-1 icon-default" aria-hidden="true"></i>+ Keranjang
              <span class="btn-text">+ Keranjang</span>
            </button>

              </div>


              <!-- Ulasan -->
              <h5 id="ulasan" class="mt-3 mb-2">Ulasan</h5>

              <div class="rv-list" id="rv-list">
                <div id="rv-skel" class="text-muted">Memuat ulasan…</div>
              </div>

              <div class="text-center mt-2">
                <button class="btn btn-outline-secondary btn-sm" id="btn-more-rev">
                  Muat lebih banyak
                </button>
              </div>


            </div>
          </div>
        </div><!-- /row -->
      </div><!-- /card -->
    </div>
  </div>
</div>

<?php
// Product + AggregateRating + Review (JSON-LD)
$ld = [
  "@context" => "https://schema.org",
  "@type"    => "Product",
  "name"     => (string)$product->nama,
  "image"    => [$img],
  "sku"      => (string)($product->sku ?? ""),
  "description" => trim(strip_tags($product->deskripsi ?: $product->nama)),
  "brand" => [
    "@type" => "Brand",
    "name"  => (string)($product->merek ?? $product->kategori_nama ?? "Brand")
  ],
  "offers" => [
    "@type"         => "Offer",
    "priceCurrency" => "IDR",
    "price"         => number_format((float)$product->harga, 0, '.', ''),
    "availability"  => ((int)$product->stok > 0) ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
    "url"           => current_url()
  ]
];
$ratingAvg   = isset($product->rating_avg)   ? (float)$product->rating_avg   : 0.0;
$ratingCount = isset($product->rating_count) ? (int)$product->rating_count   : 0;
if ($ratingCount > 0) {
  $ld["aggregateRating"] = [
    "@type"       => "AggregateRating",
    "ratingValue" => round($ratingAvg, 1),
    "reviewCount" => $ratingCount,
    "bestRating"  => 5,
    "worstRating" => 1
  ];
}
$revLD = [];
if (!empty($reviews)) {
  $i = 0;
  foreach ($reviews as $rv) {
    $txt = trim((string)($rv->review ?? ''));
    if ($txt === '') continue;
    $revLD[] = [
      "@type" => "Review",
      "reviewBody" => $txt,
      "reviewRating" => [
        "@type" => "Rating",
        "ratingValue" => (int)$rv->stars,
        "bestRating"  => 5,
        "worstRating" => 1
      ],
      "author" => [ "@type" => "Person", "name" => "Pengguna" ],
      "datePublished" => date('c', is_numeric($rv->ts) ? (int)$rv->ts : strtotime($rv->ts))
    ];
    if (++$i >= 10) break;
  }
}
if ($revLD) { $ld["review"] = $revLD; }
?>
<script type="application/ld+json">
<?= json_encode($ld, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); ?>
</script>

<?php
$bread = [
  "@context" => "https://schema.org",
  "@type"    => "BreadcrumbList",
  "itemListElement" => [
    [
      "@type"    => "ListItem",
      "position" => 1,
      "name"     => "Products",
      "item"     => site_url('produk')
    ],
    [
      "@type"    => "ListItem",
      "position" => 2,
      "name"     => (string)$product->nama,
      "item"     => current_url()
    ]
  ]
];
?>
<script type="application/ld+json">
<?= json_encode($bread, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); ?>
</script>

<!-- Floating Cart Button -->
<a href="<?= site_url('produk/cart') ?>"
   class="cart-fab" id="cart-fab"
   aria-label="Buka keranjang">
  <span class="spinner-border d-none" aria-hidden="true"></span>
  <i class="mdi mdi-cart-outline icon-default" aria-hidden="true"></i>
  <span class="fab-badge" id="cart-count-fab">0</span>
</a>

<script src="<?php echo base_url('assets/admin') ?>/js/vendor.min.js"></script>
<script src="<?php echo base_url('assets/admin') ?>/js/app.min.js"></script>
<script src="<?php echo base_url('assets/admin') ?>/js/sw.min.js"></script>

<?php $this->load->view("front_end/footer.php") ?>

<script>
(function(){
  // ==== CSRF (jika aktif di config) ====
  var CSRF = <?php
    if ($this->config->item('csrf_protection')) {
      echo json_encode([
        'name' => $this->security->get_csrf_token_name(),
        'hash' => $this->security->get_csrf_hash()
      ]);
    } else { echo 'null'; }
  ?>;

  // ==== Endpoint ====
  var CART_ADD_URL   = "<?= site_url('produk/add_to_cart'); ?>";
  var CART_COUNT_URLS = [
    "<?= site_url('produk/cart_count'); ?>",
    "<?= site_url('produk/cart/count'); ?>"
  ];

  // ==== Utils ====
  function notifySuccess(msg){
    if (window.Swal) Swal.fire({ icon:'success', title:'Berhasil', text: msg, timer:1400, showConfirmButton:false });
    else alert(msg);
  }
  function notifyError(msg){
    if (window.Swal) Swal.fire({ icon:'error', title:'Gagal', text: msg });
    else alert(msg);
  }
  function setBtnLoading($btn, on){
    var btn = $btn instanceof HTMLElement ? $btn : $btn[0];
    if (!btn) return;
    var spin = btn.querySelector('.spinner-border');
    var ico  = btn.querySelector('.icon-default');
    if (on){
      btn.classList.add('btn-loading');
      btn.setAttribute('disabled','disabled');
      if (spin) spin.classList.remove('d-none');
      if (ico)  ico.classList.add('d-none');
    } else {
      btn.classList.remove('btn-loading');
      btn.removeAttribute('disabled');
      if (spin) spin.classList.add('d-none');
      if (ico)  ico.classList.remove('d-none');
    }
  }
  function postForm(url, data){
    var fd = new FormData();
    for (var k in data){ fd.append(k, data[k]); }
    if (CSRF){ fd.append(CSRF.name, CSRF.hash); }
    return fetch(url, { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:fd })
      .then(function(r){ return r.json(); });
  }
  function getJSON(url){
    return fetch(url, { headers:{'X-Requested-With':'XMLHttpRequest'} })
      .then(function(r){ if (!r.ok) throw new Error('HTTP '+r.status); return r.json(); });
  }

  // ==== Qty (− / +) controller ====
  var input = document.getElementById('qty-detail');
  var inc   = document.getElementById('qty-inc');
  var dec   = document.getElementById('qty-dec');

  var maxAttr = input ? parseInt(input.getAttribute('max'),10) : 0;
  var hasMax  = Number.isFinite(maxAttr) && maxAttr > 0;

  function toInt(v){
    var n = parseInt(String(v).replace(/\D+/g,''),10);
    return Number.isFinite(n) ? n : 1;
  }
  function syncButtons(v){
    if (!inc || !dec || !input) return;
    dec.disabled = (v <= 1) || input.disabled;
    inc.disabled = (hasMax && v >= maxAttr) || input.disabled;
  }
  function clamp(){
    if (!input) return 1;
    var v = toInt(input.value);
    if (v < 1) v = 1;
    if (hasMax && v > maxAttr) v = maxAttr;
    input.value = v;
    syncButtons(v);
    return v;
  }
  if (inc) inc.addEventListener('click', function(){
    var v = clamp();
    if (!hasMax || v < maxAttr) input.value = ++v;
    syncButtons(v);
  });
  if (dec) dec.addEventListener('click', function(){
    var v = clamp();
    if (v > 1) input.value = --v;
    syncButtons(v);
  });
  if (input){
    input.addEventListener('input', clamp);
    input.addEventListener('blur', clamp);
    input.addEventListener('keydown', function(e){
      if (e.key === 'Enter'){
        e.preventDefault();
        var btn = document.getElementById('btn-add-cart-detail');
        if (btn && !btn.disabled){ btn.click(); }
      }
    });
    clamp(); // init
  }

  // Update badge cart; coba 2 URL (standar & fallback)
  function refreshCartCount(){
    function applyCount(n){
      var head = document.getElementById('cart-count');
      if (head) head.textContent = n;
      var fab  = document.getElementById('cart-count-fab');
      if (fab)  fab.textContent = n;
    }
    return getJSON(CART_COUNT_URLS[0]).then(function(r){
      if (r && r.success){ applyCount(r.count); }
    }).catch(function(){
      return getJSON(CART_COUNT_URLS[1]).then(function(r){
        if (r && r.success){ applyCount(r.count); }
      }).catch(function(){ /* diamkan */ });
    });
  }

  // ==== Handler tombol tambah ke keranjang (detail) ====
  document.addEventListener('click', function(e){
    var btn = e.target.closest('#btn-add-cart-detail');
    if (!btn) return;

    var qtyEl = document.getElementById('qty-detail');
    var qty = parseInt((qtyEl && qtyEl.value) ? qtyEl.value : '1', 10);
    if (!Number.isFinite(qty) || qty < 1) qty = 1;

    setBtnLoading(btn, true);
    postForm(CART_ADD_URL, { id: <?= (int)$product->id; ?>, qty: qty })
      .then(function(res){
        if (!res || res.success !== true){
          notifyError((res && res.pesan) || 'Gagal menambahkan ke keranjang.');
          return;
        }
        // Berhasil
        notifySuccess('Ditambahkan ke keranjang' + (qty>1 ? (' (x'+qty+')') : ''));

        // Update count cepat jika API mengembalikan count
        if (typeof res.count === 'number'){
          var head = document.getElementById('cart-count');
          if (head) head.textContent = res.count;
          var fabBadge = document.getElementById('cart-count-fab');
          if (fabBadge) fabBadge.textContent = res.count;
        } else {
          refreshCartCount();
        }

        // Trigger FAB bump
        document.dispatchEvent(new CustomEvent('cart:add:success', {
          detail: { count: (typeof res.count === 'number' ? res.count : null) }
        }));
      })
      .catch(function(){
        notifyError('Gagal terhubung ke server.');
      })
      .finally(function(){
        setBtnLoading(btn, false);
      });
  });

  // Init pertama
  refreshCartCount();
})();
</script>
<?php $this->load->view("partials/form_rating") ?>

<script>
(function(){
  var CART_COUNT_URLS = [
    "<?= site_url('produk/cart_count'); ?>",
    "<?= site_url('produk/cart/count'); ?>"
  ];

  function setFabCount(n){
    var el = document.getElementById('cart-count-fab');
    if (el) el.textContent = n;
  }

  function fetchCartCountAndRender(){
    return fetch(CART_COUNT_URLS[0], {headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(function(r){ return r.json(); })
      .then(function(res){
        if (res && res.success){
          setFabCount(res.count);
          var headBadge = document.getElementById('cart-count');
          if (headBadge) headBadge.textContent = res.count;
        }
      })
      .catch(function(){
        return fetch(CART_COUNT_URLS[1], {headers:{'X-Requested-With':'XMLHttpRequest'}})
          .then(function(r){ return r.json(); })
          .then(function(res){
            if (res && res.success){
              setFabCount(res.count);
              var headBadge = document.getElementById('cart-count');
              if (headBadge) headBadge.textContent = res.count;
            }
          })
          .catch(function(){ /* diamkan */});
      });
  }

  function bumpFab(){
    var fab = document.getElementById('cart-fab');
    if (!fab) return;
    fab.classList.add('bump');
    setTimeout(function(){ fab.classList.remove('bump'); }, 400);
  }

  document.addEventListener('cart:add:success', function(){
    fetchCartCountAndRender().then(bumpFab);
  });

  fetchCartCountAndRender();
})();
</script>
<script>
(function(){
  if (window.__RV_LOADER__) return;
  window.__RV_LOADER__ = true;

  var PROD_ID = <?= (int)$product->id ?>;
  var list    = document.getElementById('rv-list');
  var btnMore = document.getElementById('btn-more-rev');

  // skeleton awal
  var skel = document.createElement('div');
  skel.id = 'rv-skel';
  skel.className = 'text-muted';
  skel.textContent = 'Memuat ulasan…';
  if (list && !document.getElementById('rv-skel')) list.appendChild(skel);

  var OFF=0, LIM=6, TOT=0, BUSY=false, DONE=false;

  // CSRF (kalau aktif)
  var CSRF = <?php
    if ($this->config->item('csrf_protection')) {
      echo json_encode([
        'name' => $this->security->get_csrf_token_name(),
        'hash' => $this->security->get_csrf_hash()
      ]);
    } else { echo 'null'; }
  ?>;

  function escapeHtml(s){
    return String(s||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  }
  function fmtDate(ts){
    var d = new Date(ts); var m=['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    return String(d.getDate()).padStart(2,'0')+' '+m[d.getMonth()]+' '+d.getFullYear();
  }
  function initials(name){
    name = (name||'').trim(); if(!name) return '?';
    var p = name.split(/\s+/); return (p[0][0]||'?') + ((p[1]||'')[0]||'');
  }
  function maskName(name){
    name = (name||'').trim(); if(!name) return 'Anonim';
    return name.split(/\s+/).map(p => (p[0]||'?').toUpperCase()+'***').join(' ');
  }
  function starHtml(n){
    var h=''; for (var i=1;i<=5;i++) h += '<i class="mdi '+(i<=n?'mdi-star full':'mdi-star-outline')+'"></i>';
    return h;
  }
  function rowHtml(r){
    return (
      '<div class="rv-item">'+
        '<div class="rv-avatar">'+escapeHtml(initials(r.nama||''))+'</div>'+
        '<div class="flex-fill">'+
          '<div class="rv-head">'+
            '<span class="rv-name">'+escapeHtml(maskName(r.nama||''))+'</span>'+
            '<span class="rv-meta">'+escapeHtml(r.ts_fmt||fmtDate(new Date()))+'</span>'+
          '</div>'+
          '<div class="rv-stars">'+starHtml(parseInt(r.stars||0,10))+'</div>'+
          (r.review ? '<div class="rv-text">'+escapeHtml(r.review)+'</div>' : '')+
        '</div>'+
      '</div>'
    );
  }

  function setBtnBusy(b){
    if (!btnMore) return;
    btnMore.disabled = !!b;
    btnMore.innerHTML = b
      ? '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memuat…'
      : 'Muat lebih banyak';
  }

  function fetchPage(){
    if (BUSY || DONE) return;
    BUSY = true; setBtnBusy(true);

    var fd = new FormData();
    fd.append('id', <?= (int)$product->id ?>);
    fd.append('offset', OFF);
    fd.append('limit', LIM);
    if (CSRF){ fd.append(CSRF.name, CSRF.hash); }

    fetch('<?= site_url('produk/review_list') ?>', {
      method: 'POST',
      headers: {'X-Requested-With':'XMLHttpRequest'},
      body: fd
    })
    .then(r => r.json())
    .then(res => {
      if (!res || !res.success){ throw new Error('bad'); }

      // update CSRF bila dikirim balik
      if (res.csrf && CSRF){ CSRF.name=res.csrf.name; CSRF.hash=res.csrf.hash; }

      TOT = parseInt(res.total||0,10);
      var rows = res.rows||[];

      if (skel){ skel.remove(); skel=null; }
      if (!rows.length && OFF===0){
        list.innerHTML = '<div class="text-muted">Belum ada ulasan.</div>';
        DONE = true; if(btnMore) btnMore.style.display='none';
        document.querySelectorAll('.count-label').forEach(function(el){ el.textContent = 0; });
        return;
      }

      var frag = document.createDocumentFragment();
      rows.forEach(function(r){
        var wrap = document.createElement('div');
        wrap.className = 'rv-item';
        wrap.innerHTML = rowHtml(r);
        frag.appendChild(wrap);
      });
      list.appendChild(frag);

      OFF += rows.length;
      document.querySelectorAll('.count-label').forEach(function(el){ el.textContent = TOT; });

      if (OFF >= TOT || rows.length === 0){
        DONE = true;
        if (btnMore) btnMore.style.display='none';
      } else {
        setBtnBusy(false);
      }
    })
    .catch(function(){
      if (OFF===0){ list.innerHTML = '<div class="text-danger">Gagal memuat ulasan.</div>'; }
    })
    .finally(function(){ BUSY=false; setBtnBusy(false); });
  }

  // ⬇️ fungsi publik untuk refresh total
  function refreshAll(){
    OFF=0; LIM=6; TOT=0; BUSY=false; DONE=false;
    list.innerHTML = '';
    skel = document.createElement('div');
    skel.id = 'rv-skel';
    skel.className = 'text-muted';
    skel.textContent = 'Memuat ulasan…';
    list.appendChild(skel);
    if (btnMore){ btnMore.style.display=''; btnMore.disabled=false; btnMore.textContent='Muat lebih banyak'; }
    fetchPage();
  }
  window.refreshReviews = refreshAll;

  // dipanggil setelah submit rating sukses
  document.addEventListener('reviews:refresh', function(e){
    var pid = e.detail && e.detail.produkId;
    if (!pid || pid === PROD_ID) refreshAll();
  });

  // inisialisasi pertama
  btnMore && btnMore.addEventListener('click', fetchPage, {passive:true});
  fetchPage();
})();
</script>

<script>
(function(){
  var btn = document.getElementById('btn-add-cart-detail');
  if(!btn) return;

  function setBtnLoading(on){
    var sp  = btn.querySelector('.spinner-border');
    var txt = btn.querySelector('.btn-text');
    if(on){
      if(btn.hasAttribute('disabled')) return; // sudah nonaktif
      btn.classList.add('is-loading','disabled');
      btn.setAttribute('aria-disabled','true');
      sp.classList.remove('d-none');
      if(!btn.dataset.originalText){ btn.dataset.originalText = txt ? (txt.textContent||'').trim() : ''; }
      if(txt) txt.textContent = btn.dataset.loadingLabel || 'Menambahkan…';
    }else{
      btn.classList.remove('is-loading','disabled');
      btn.removeAttribute('aria-disabled');
      sp.classList.add('d-none');
      if(txt && btn.dataset.originalText){ txt.textContent = btn.dataset.originalText; }
    }
  }
  // Ekspos bila ingin dipanggil dari AJAX-mu:
  window.setCartDetailLoading = setBtnLoading;

  // Klik: tampilkan loading; matikan sendiri dari AJAX-mu ketika selesai
  btn.addEventListener('click', function(){
    if (btn.hasAttribute('disabled') || btn.classList.contains('disabled')) return;
    setBtnLoading(true);

    // --- Integrasikan di kode add-to-cart kamu ---
    // Contoh pola umum:
    // postJSON('<?= site_url('cart/add') ?>', {id: PRODUCT_ID, qty: 1})
    //   .then(function(res){ /* sukses */ })
    //   .catch(function(){ /* gagal */ })
    //   .finally(function(){ setBtnLoading(false); });
  });

  // Optional: reset saat modal ditutup
  if (window.jQuery){
    $('#modalProduk').on('hidden.bs.modal', function(){ setBtnLoading(false); });
  }
})();
</script>
