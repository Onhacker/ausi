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

<script>
/* ==== Rating + Review (SweetAlert) ==== */
(function(){
  var CSRF = <?php
    if ($this->config->item('csrf_protection')) {
      echo json_encode([
        'name' => $this->security->get_csrf_token_name(),
        'hash' => $this->security->get_csrf_hash()
      ]);
    } else {
      echo 'null';
    }
  ?>;

  function postJSON(url, data){
    return fetch(url, {
      method: 'POST',
      headers: {'X-Requested-With': 'XMLHttpRequest'},
      body: (function(){
        var fd = new FormData();
        for (var k in data){ fd.append(k, data[k]); }
        if (CSRF) fd.append(CSRF.name, CSRF.hash);
        return fd;
      })()
    }).then(function(r){ return r.json(); });
  }

  function renderAvg(box, avg, count){
    avg = parseFloat(avg||0); count = parseInt(count||0,10);
    var meter = box.querySelector('.star-meter');
    var avgLb = box.querySelector('.avg-label');
    var cntLb = box.querySelector('.count-label');

    if (avgLb) avgLb.textContent = avg.toFixed(1).replace('.', ',');
    if (cntLb) cntLb.textContent = count;

    if (meter){
      var r = Math.round(Math.max(0,Math.min(5,avg))*2)/2;
      var full = Math.floor(r), half = (r-full)===0.5, empty = 5-full-(half?1:0);
      var html='';
      for(var i=0;i<full;i++) html+='<i class="mdi mdi-star full"></i>';
      if(half) html+='<i class="mdi mdi-star-half-full full"></i>';
      for(var j=0;j<empty;j++) html+='<i class="mdi mdi-star-outline empty"></i>';
      meter.innerHTML = html;
    }
  }

  function esc(s){ return String(s||'').replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#039;'}[m])); }

  function openRate(prodId, prodName, box){
    if (!window.Swal){
      var val = prompt('Beri rating (1–5) untuk: '+prodName, 5);
      var stars = parseInt(val||'0',10); if (!stars||stars<1||stars>5) return;
      var rev = prompt('Tulis review (opsional):','')||'';
      postJSON('<?= site_url('produk/rate') ?>', { id:prodId, stars:stars, review:rev })
        .then(function(res){ if(res&&res.success){ renderAvg(box,res.avg,res.count); alert('Terima kasih!'); } else alert(res&&res.pesan||'Gagal'); });
      return;
    }

    var chosen = 0, maxLen = 1000;

    Swal.fire({
      title: 'Beri rating',
      html:
        '<div style="margin-top:.25rem;font-weight:600">'+esc(prodName)+'</div>'+
        '<div class="swal-rate-wrap" style="margin:.75rem 0 .5rem;display:flex;justify-content:center;gap:8px;">'+
          [1,2,3,4,5].map(n=>'<i class="mdi mdi-star-outline rate-star" data-n="'+n+'" style="font-size:28px;cursor:pointer;"></i>').join('')+
        '</div>'+
        '<div class="small text-muted" id="swal-rate-hint" style="margin-bottom:.5rem;">Pilih 1–5 bintang</div>'+
        '<textarea id="swal-review" class="form-control" rows="3" placeholder="Tulis review (opsional)" style="resize:vertical;"></textarea>'+
        '<div class="small text-muted mt-1" id="swal-count">0/'+maxLen+'</div>',
      showCancelButton: true,
      confirmButtonText: 'Simpan',
      cancelButtonText: 'Batal',
      focusConfirm: false,
      returnFocus: false,
      didOpen: function(m){
        // Matikan focus trap Bootstrap agar textarea bisa diketik
        if (window.jQuery) { $(document).off('focusin.bs.modal'); }
        document.addEventListener('focusin', function onFI(e){
          if (e.target && e.target.closest && e.target.closest('.swal2-container')) {
            e.stopImmediatePropagation();
          }
        }, {capture:true, once:true});

        var wrap=m.querySelector('.swal-rate-wrap'),
            hint=m.querySelector('#swal-rate-hint'),
            ta=m.querySelector('#swal-review'),
            cnt=m.querySelector('#swal-count');

        function draw(){
          wrap.querySelectorAll('.rate-star').forEach(function(el){
            var n=parseInt(el.getAttribute('data-n')||'0',10);
            el.className='mdi rate-star '+(n<=chosen?'mdi-star':'mdi-star-outline');
            el.style.color=(n<=chosen?'#f59e0b':'');
          });
          hint.textContent = chosen ? (chosen+'/5') : 'Pilih 1–5 bintang';
        }
        wrap.addEventListener('click', function(e){
          var ic=e.target.closest('.rate-star'); if(!ic) return;
          chosen=parseInt(ic.getAttribute('data-n')||'0',10);
          draw();
        });
        ta.addEventListener('input', function(){
          if(this.value.length>maxLen) this.value=this.value.slice(0,maxLen);
          cnt.textContent=this.value.length+'/'+maxLen;
        });
        draw();
        setTimeout(function(){ ta && ta.focus(); }, 50);
      },
      preConfirm: function(){
        var r=(document.getElementById('swal-review')||{}).value||'';
        if(!chosen){ Swal.showValidationMessage('Pilih minimal 1 bintang'); return false; }
        return {stars:chosen, review:r.trim()};
      }
    }).then(function(res){
      if (!res.isConfirmed || !res.value) return;
      postJSON('<?= site_url('produk/rate') ?>', { id:prodId, stars:res.value.stars, review:res.value.review })
        .then(function(resp){
          if (!resp || !resp.success){
            Swal.fire('Gagal', (resp&&resp.pesan)||'Gagal menyimpan rating', 'error'); return;
          }
          renderAvg(box, resp.avg, resp.count);
          Swal.fire({icon:'success', title:'Terima kasih!', text:'Rating & review tersimpan.', timer:1300, showConfirmButton:false});
        })
        .catch(function(){ Swal.fire('Gagal', 'Kesalahan jaringan.', 'error'); });
    });
  }

  // Delegasi klik pada bintang & “Beri rating”
  document.addEventListener('click', function(e){
    var star = e.target.closest('[data-rate-box-detail] .star-meter');
    var link = e.target.closest('[data-rate-box-detail] .rate-link');
    if (!star && !link) return;
    var box = (star||link).closest('[data-rate-box-detail]');
    var id  = parseInt(box.getAttribute('data-id')||'0',10);
    var nm  = box.getAttribute('data-name')||'Produk';
    if (!id) return;
    openRate(id, nm, box);
  }, {passive:true});
})();
</script>

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
  var CSRF = <?php
    if ($this->config->item('csrf_protection')) {
      echo json_encode([
        'name' => $this->security->get_csrf_token_name(),
        'hash' => $this->security->get_csrf_hash()
      ]);
    } else { echo 'null'; }
  ?>;

  var REV_URL = "<?= site_url('produk/review_list'); ?>";
  var prodId  = <?= (int)$product->id; ?>;
  var limit   = 5;
  var offset  = 0;
  var total   = 0;

  var wrap = document.getElementById('rv-list');
  var skel = document.getElementById('rv-skel');
  var moreBtn = document.getElementById('btn-more-rev');

  // kalau belum ada tombol, tambahkan
  if (!moreBtn){
    var holder = document.createElement('div');
    holder.className = 'text-center mt-2';
    holder.innerHTML = '<button class="btn btn-outline-secondary btn-sm" id="btn-more-rev">Muat lebih banyak</button>';
    wrap.parentNode.appendChild(holder);
    moreBtn = document.getElementById('btn-more-rev');
  }

  function starHTML(n){
    n = parseInt(n||0,10);
    var html = '';
    for (var i=1;i<=5;i++){
      html += '<i class="mdi '+(i<=n?'mdi-star full':'mdi-star-outline')+'"></i>';
    }
    return html;
  }
  function rowHTML(r){
    var txt = (r.review||'').replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#039;'}[m]));
    return (
      '<div class="rv-item">'+
        '<div class="rv-stars">'+ starHTML(r.stars) +'</div>'+
        '<div>'+
          '<div class="rv-meta">'+ (r.ts_fmt||'') +'</div>'+
          '<div class="rv-text">'+ (txt ? txt.replace(/\n/g,'<br>') : '') +'</div>'+
        '</div>'+
      '</div>'
    );
  }

  function loadMore(){
    if (!moreBtn) return;
    moreBtn.disabled = true;
    moreBtn.textContent = 'Memuat...';

    var fd = new FormData();
    fd.append('id', prodId);
    fd.append('offset', offset);
    fd.append('limit', limit);
    if (CSRF){ fd.append(CSRF.name, CSRF.hash); }
fetch(REV_URL, {
  method:'POST',
  headers:{'X-Requested-With':'XMLHttpRequest'},
  body: fd
}).then(function(r){
  var ct = r.headers.get('content-type')||'';
  if (r.status === 403) throw new Error('CSRF');
  if (!ct.includes('application/json')) throw new Error('NOT_JSON');
  return r.json();
}).then(function(res){
  // --- tambahkan ini: update CSRF untuk request berikutnya
  if (res && res.csrf && CSRF){
    CSRF.name = res.csrf.name;
    CSRF.hash = res.csrf.hash;
  }

  if (skel) { skel.remove(); skel = null; }
  if (!res || !res.success) throw new Error((res && res.pesan) || 'Gagal memuat');

  total  = parseInt(res.total||0,10);
  var rows = res.rows || [];

  if (offset === 0 && rows.length === 0){
    wrap.innerHTML = '<div class="text-muted">Belum ada ulasan.</div>';
    moreBtn.classList.add('d-none');
    return;
  }

  rows.forEach(function(row){ wrap.insertAdjacentHTML('beforeend', rowHTML(row)); });
  offset += rows.length;

  if (offset >= total || rows.length === 0){
    moreBtn.classList.add('d-none');
  } else {
    moreBtn.disabled = false;
    moreBtn.textContent = 'Muat lebih banyak';
  }
}).catch(function(err){
  if (skel) { skel.textContent = (String(err)==='Error: CSRF')
    ? 'Sesi kedaluwarsa. Muat ulang halaman.'
    : 'Gagal memuat ulasan. Coba lagi.'; }
  if (moreBtn){
    moreBtn.disabled = false;
    moreBtn.textContent = 'Coba lagi';
  }
});

  }

  // batch pertama
  loadMore();
  moreBtn && moreBtn.addEventListener('click', loadMore);
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
