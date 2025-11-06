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
 .pd-card,.pd-img{border-radius:12px}.rate-row,.star-meter{display:flex;align-items:center}#btn-add-cart-detail.is-loading,.cart-anim-outer,.cart-svg-wrap{pointer-events:none;pointer-events:none}:root{--ink:#0f172a;--muted:#64748b;--line:rgba(15,23,42,.08);--brand:#1e88e5;--ok:#10b981;--bad:#ef4444;--cart-red:#dc3545;--cart-wheel:#1e1e1e}.pd-wrap .page-title{font-weight:800;letter-spacing:.2px}.pd-img{overflow:hidden;background:#f1f5f9}.pd-meta{color:var(--muted)}.pd-price{font-weight:900;font-size:1.25rem;color:#111827}.pd-price small{font-weight:600;color:#6b7280}.rate-row{gap:.5rem;flex-wrap:wrap;margin:.25rem 0 .75rem}.star-meter{gap:2px;cursor:pointer}.star-meter .mdi{font-size:18px;line-height:1;vertical-align:middle}.rv-stars .full,.star-meter .full{color:#f59e0b}.star-meter .empty{color:#cbd5e1}.rate-label{font-size:.9rem;color:#111827;font-weight:700}.rate-info{font-size:.85rem;color:var(--muted)}.rate-link{font-weight:700;color:#2563eb;cursor:pointer;text-decoration:underline}.cart-fab,.rate-link:hover{text-decoration:none}.btn .btn-text{display:inline}@media (max-width:575.98px){.btn .btn-text{display:none!important}}.qty-group{border-radius:10px;overflow:hidden;max-width:220px;box-shadow:0 2px 10px rgba(2,6,23,.05)}.qty-group .btn{min-width:36px;font-weight:800;padding:.35rem .5rem;font-size:.86rem;border:none;background:#f8fafc}.qty-group input[type=number]{text-align:center;font-weight:800;border-left:none;border-right:none;padding:.35rem .25rem;height:34px;font-size:.9rem}#ulasan{scroll-margin-top:80px}.rv-list{border:1px solid var(--line);border-radius:10px;padding:.75rem}.rv-stars .mdi{font-size:14px}.rv-meta{font-size:.76rem;color:var(--muted)}.rv-text{margin:.15rem 0 0;font-size:.92rem;color:#111827;white-space:pre-line}.cart-fab{position:fixed;right:16px;bottom:calc(70px + env(safe-area-inset-bottom));width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#ef4444 0,#b91c1c 100%);color:#fff;display:inline-flex;align-items:center;justify-content:center;box-shadow:0 12px 28px rgba(0,0,0,.18),0 6px 12px rgba(0,0,0,.12);z-index:1060}.cart-fab .mdi{font-size:24px;line-height:1}.cart-fab .fab-badge{position:absolute;top:-6px;right:-6px;min-width:20px;height:20px;padding:0 6px;border-radius:999px;background:#111827;color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,.25)}.cart-fab.bump{animation:.4s fab-bump}@keyframes fab-bump{0%,100%{transform:scale(1)}35%{transform:scale(1.12)}}@media (min-width:992px){.cart-fab{width:52px;height:52px}}.spinner-border{display:inline-block;width:1rem;height:1rem;border:.15rem solid currentColor;border-right-color:transparent;border-radius:50%;animation:.6s linear infinite spin;vertical-align:-.2em;margin-right:.4rem}.cart-anim-outer,.rv-avatar,.rv-head,.rv-item{display:flex;display:flex}@keyframes spin{to{transform:rotate(360deg)}}#btn-add-cart-detail.is-loading{opacity:.9}.rv-item{gap:.6rem;padding:.5rem 0;border-bottom:1px dashed var(--line)}.rv-item:last-child{border-bottom:none}.rv-avatar{width:36px;height:36px;border-radius:50%;background:#e2e8f0;color:#0f172a;font-weight:800;align-items:center;justify-content:center;flex:0 0 36px}.rv-head{justify-content:space-between;align-items:center;gap:.5rem}.rv-name{font-weight:700;color:#0f172a;font-size:.85rem}.qm-anim-pulse{animation:.45s ease-out qm-pulse}.qm-anim-food{animation:.55s cubic-bezier(.2,.7,.3,1) qm-food-pop}.qm-anim-drink{animation:.55s cubic-bezier(.2,.7,.3,1) qm-drink-sip}.qm-anim-cart{animation:.55s cubic-bezier(.2,.7,.3,1) qm-cart-wiggle}.drop-plate{width:16px;height:16px;border-radius:50%;background:#fff;border:2px solid var(--cart-red);box-sizing:border-box;animation:.8s ease-out forwards plate-drop}.drop-drink{width:12px;height:16px;border-radius:2px;background:var(--cart-red);box-shadow:0 0 6px rgba(220,53,69,.5);animation:.9s ease-out .15s forwards drink-drop}.drop-drink:after{content:"";position:absolute;top:-5px;left:6px;width:2px;height:6px;background:#fff;border-radius:1px;transform:rotate(15deg);box-shadow:0 0 2px rgba(0,0,0,.15)}@keyframes plate-drop{0%{opacity:1;transform:translate(-50%,-28px) scale(1)}40%{opacity:1;transform:translate(-50%,4px) scale(1.1)}60%{opacity:1;transform:translate(-50%,2px) scale(.9)}80%{opacity:1;transform:translate(-50%,3px) scale(1.05)}100%{opacity:0;transform:translate(-50%,20px) scale(.4)}}@keyframes drink-drop{0%{opacity:1;transform:translate(-50%,-32px) scale(1)}40%{opacity:1;transform:translate(-50%,2px) scale(1.15)}60%{opacity:1;transform:translate(-50%,0) scale(.9)}80%{opacity:1;transform:translate(-50%,1px) scale(1.05)}100%{opacity:0;transform:translate(-50%,20px) scale(.4)}}.cart-wheel-shape{fill:var(--cart-wheel);stroke:var(--cart-wheel);stroke-width:3}.swal-cart-popup{overflow:hidden;padding-bottom:1rem}.swal2-icon.swal-cart-icon{display:block!important;width:auto!important;height:auto!important;min-height:120px;margin:0 auto .75rem!important;padding:0!important;border:none!important;background:0 0!important;line-height:0!important}.cart-anim-outer{align-items:flex-start;justify-content:center;width:72px;height:120px;margin:0 auto .1rem;position:relative;align-items:flex-start;justify-content:center}.cart-anim-wrapper{transform-origin:center top;position:relative;width:48px;height:48px;transform:scale(2.2) translateY(2px);transform-origin:center top}.cart-svg-wrap,.drop-item{position:absolute;left:50%;top:0}.cart-svg-wrap{transform:translateX(-50%);width:48px;height:48px;z-index:2}.drop-item{filter:drop-shadow(0 2px 2px rgba(0, 0, 0, .2));transform:translate(-50%,-24px) scale(1);opacity:0;animation-fill-mode:forwards;filter:drop-shadow(0 2px 2px rgba(0,0,0,.2));z-index:1}.swal-cart-popup .swal2-title{margin:.25rem 0!important}.swal-cart-popup .swal2-html-container{margin:0 0 .25rem!important}@media (max-width:480px){.swal2-icon.swal-cart-icon{min-height:108px;margin-bottom:.5rem!important}.cart-anim-wrapper{transform:scale(2) translateY(2px)}}
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
  // function notifySuccess(msg){
  //   if (window.Swal) Swal.fire({ icon:'success', title:'Berhasil', text: msg, timer:1400, showConfirmButton:false });
  //   else alert(msg);
  // }
   function notifySuccess(produk, text){
    if (window.Swal){
      Swal.fire({
        title: produk,
        text:  text  || "",
        timer: 1500,
        showConfirmButton: false,
        iconHtml:
          '<div class="cart-anim-outer"><div class="cart-anim-wrapper">'+
            '<div class="drop-item drop-plate"></div>'+
            '<div class="drop-item drop-drink"></div>'+
            '<div class="cart-svg-wrap">'+
              '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="48" height="48" fill="none" stroke="#dc3545" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">'+
                '<path d="M6 12h7.5a2 2 0 0 1 1.9 1.5l2.2 8.5" />'+
                '<path d="M17 22h22.5a2 2 0 0 1 1.9 2.6l-3 9a2 2 0 0 1-1.9 1.4H22.5a2 2 0 0 1-1.9-1.5L17 22Z" />'+
                '<path d="M20 26h18" />'+
                '<path d="M21.5 30h15" />'+
                '<circle class="cart-wheel-shape" cx="22" cy="38" r="3.5" />'+
                '<circle class="cart-wheel-shape" cx="36" cy="38" r="3.5" />'+
                '<path d="M28 14l8 -4" stroke="#dc3545" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>'+
              '</svg>'+
            '</div>'+
          '</div></div>',
        customClass:{popup:'swal-cart-popup',icon:'swal-cart-icon'}
      });
    } else {
      alert((produk ? produk + ": " : "") + (text || ""));
    }
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
        // notifySuccess(r.produk||"Mantap!", r.pesan||"Item masuk keranjang");
        // Berhasil
        // Berhasil
        var r = res || {};
        notifySuccess(r.produk || "Mantap!", r.pesan || "Item masuk keranjang");
        flashAdded(btn);


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
  window.setCartDetailLoading = function(on){
    var btn = document.getElementById('btn-add-cart-detail');
    if (!btn) return;
    var sp  = btn.querySelector('.spinner-border');
    if (on){
      btn.setAttribute('disabled','disabled');
      btn.setAttribute('aria-busy','true');
      if (sp) sp.classList.remove('d-none');
    } else {
      btn.removeAttribute('disabled');
      btn.removeAttribute('aria-busy');
      if (sp) sp.classList.add('d-none');
    }
  };
})();

function flashAdded(btn){
  // Ganti icon & teks sebentar
  var ico = btn.querySelector('.icon-default');
  var txt = btn.querySelector('.btn-text');

  var oldIcon = ico ? ico.className : '';
  var oldText = txt ? (txt.textContent || '') : '';

  if (ico){ ico.className = 'mdi mdi-check-circle-outline icon-default'; ico.classList.remove('d-none'); }
  if (txt){ txt.textContent = 'Ditambahkan!'; }

  setTimeout(function(){
    if (ico && oldIcon) ico.className = oldIcon;              // balik ke ikon keranjang
    if (txt) txt.textContent = oldText || '+ Keranjang';      // balik ke teks awal
  }, 900);
}

</script>
