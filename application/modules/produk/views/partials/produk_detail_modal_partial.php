<?php
$img = $product->gambar
  ? (strpos($product->gambar, 'http') === 0 ? $product->gambar : base_url($product->gambar))
  : base_url('assets/images/products/no-image.png');

$stok    = (int)($product->stok ?? 0);
$satuan  = $product->satuan ?: 'pcs';
$katNama = $product->kategori_nama ?: '';
$harga   = (float)($product->harga ?? 0);
$slug    = $product->link_seo ?? '';

// ===== Aggregate rating (fallback ke 0 bila null) =====
$ratingAvg   = isset($product->rating_avg)   ? (float)$product->rating_avg   : 0.0;
$ratingCount = isset($product->rating_count) ? (int)$product->rating_count   : 0;

// Hitung icon full/half/empty untuk tampilan
$rounded = round(max(0,min(5,$ratingAvg))*2)/2; // ke 0.5
$full  = (int)floor($rounded);
$half  = ((float)$rounded - $full) === 0.5;
$empty = 5 - $full - ($half ? 1 : 0);

// ===== Ambil 3 review terbaru (punya teks) =====
$reviews = $this->db->select('nama, stars, review, COALESCE(review_at, created_at) AS ts', false)
  ->from('produk_rating')
  ->where('produk_id', (int)$product->id)
  ->where("review IS NOT NULL AND TRIM(review) <> ''", null, false)
  ->order_by('COALESCE(review_at, created_at)', 'DESC', false)
  ->limit(3)
  ->get()->result();


function _rv_date($ts){
  if (!$ts) return '';
  $t = is_numeric($ts) ? (int)$ts : strtotime($ts);
  return date('d M Y', $t);
}

function _rv_initials($name){
  $name = trim((string)$name);
  if ($name==='') return '?';
  $parts = preg_split('/\s+/u', $name);
  $a = mb_strtoupper(mb_substr($parts[0],0,1));
  $b = isset($parts[1]) ? mb_strtoupper(mb_substr($parts[1],0,1)) : '';
  return $a.$b;
}


function _rv_mask_name($name){
  $name = trim((string)$name);
  if ($name === '') return 'Anonim';
  $parts = preg_split('/\s+/u', $name);
  $masked = [];
  foreach ($parts as $p){
    $first = mb_strtoupper(mb_substr($p, 0, 1));
    $masked[] = $first . '***'; // selalu 3 bintang, rapi & konsisten
  }
  return implode(' ', $masked);
}



?>
  <link href="<?= base_url('assets/front/produk_detail_modal_partial.min.css'); ?>" rel="stylesheet" />

<style type="text/css">
  /* ===== KOMPOSISI / DESKRIPSI DI MODAL PRODUK ===== */
.desc-title{
    margin-top: 1rem;
    margin-bottom: .25rem;
    font-size: .78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #6b7280; /* abu-abu halus */
}

.desc-box{
    padding: .75rem .9rem;
    background: #f9fafb;
    border-radius: .75rem;
    border: 1px dashed #e5e7eb;
    font-size: .9rem;
    line-height: 1.5;
    color: #111827;
    max-height: 190px;          /* biar ga kepanjangan turun ke bawah */
    overflow-y: auto;           /* scroll kalau isi panjang */
    word-break: break-word;     /* teks panjang ga nembus keluar */
}

/* Rapikan elemen HTML di dalam deskripsi */
.desc-box p{
    margin-bottom: .4rem;
}
.desc-box p:last-child{
    margin-bottom: 0;
}
.desc-box ul,
.desc-box ol{
    margin: 0 0 .4rem;
    padding-left: 1.2rem;
}
.desc-box li{
    margin-bottom: .15rem;
}

/* Kalau isinya plain text (tanpa <p>), tetap enak dibaca */
.desc-box{
    white-space: normal;
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

    <!-- ===== Rating row (klik bintang/teks untuk memberi rating) ===== -->

      <div class="rate-row" data-rate-box data-id="<?= (int)$product->id; ?>" data-name="<?= html_escape($product->nama); ?>">

      <div class="star-meter" aria-label="Rating: <?= number_format($ratingAvg,1,',','.'); ?>/5" title="Beri rating">
        <?php for ($i=0; $i<$full; $i++): ?><i class="mdi mdi-star full"></i><?php endfor; ?>
        <?php if ($half): ?><i class="mdi mdi-star-half-full full"></i><?php endif; ?>
        <?php for ($i=0; $i<$empty; $i++): ?><i class="mdi mdi-star-outline empty"></i><?php endfor; ?>
      </div>
      <div class="small text-muted">
        <span class="avg-label"><?= number_format($ratingAvg,1,',','.'); ?></span>/5 ·
        <span class="count-label"><?= (int)$ratingCount; ?></span> ulasan
      </div>
      <a class="rate-link">Tulis Ulasan</a>
    </div>

    <!-- Meta + Qty + CTA -->
    <div class="row align-items-center no-gutters mb-2">
      <!-- Meta stok -->
      <div class="col-4">
        <div class="meta-line mb-0">
          <?php if ($stok > 0): ?>
            <span class="chip" title="Stok tersedia"><span class="dot on"></span> Tersedia</span>
          <?php else: ?>
            <span class="chip" title="Stok habis"><span class="dot off"></span> Habis</span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Qty -->
      <div class="col-4 px-1">
        <div class="input-group qty-group input-group-sm">
          <div class="input-group-prepend d-none d-md-flex"><span class="input-group-text">Qty</span></div>
          <div class="input-group-prepend">
            <button class="btn btn-outline-secondary" type="button" id="qty-dec" <?= $stok <= 0 ? 'disabled' : ''; ?>>−</button>
          </div>
          <input type="number" class="form-control" id="qty-modal" value="1" inputmode="numeric" pattern="[0-9]*"
                 aria-label="Jumlah" <?= $stok > 0 ? 'min="1" max="'.(int)$stok.'"' : 'disabled'; ?>>
          <div class="input-group-append">
            <button class="btn btn-outline-secondary" type="button" id="qty-inc" <?= $stok <= 0 ? 'disabled' : ''; ?>>+</button>
          </div>
        </div>
      </div>

      <!-- CTA Add to Cart -->
      <div class="col-4 text-right">
        <button type="button" class="btn btn-danger w-100" id="btn-add-cart-modal" data-id="<?= (int)$product->id; ?>" <?= $stok <= 0 ? 'disabled' : ''; ?>>
          <span class="icon-default mr-1"><i class="mdi mdi-cart"></i> Order</span>
          <span class="spinner-border spinner-border-sm mr-1 d-none" role="status" aria-hidden="true"></span>
          <span class="btn-text d-none d-sm-inline">Tambah ke Keranjang</span>
        </button>
      </div>
    </div>

    <!-- Deskripsi -->
    <div class="desc-title">Komposisi / Deskripsi</div>
    <div class="desc-box">
      <?= $product->deskripsi ?: '<p class="text-muted m-0">Belum ada deskripsi.</p>'; ?>
    </div>

    <!-- Reviews (maks 3) -->
    <div class="desc-title">Ulasan Terbaru</div>
    <div class="reviews-box">
      <?php if ($reviews): foreach($reviews as $rv): ?>
  <div class="review-item">
    <div class="review-avatar"><?= html_escape(_rv_initials($rv->nama ?? '')) ?></div>
    <div class="flex-fill">
      <div class="review-head">
        <span class="review-name"><?= html_escape(_rv_mask_name($rv->nama ?? '')) ?></span>

        <span class="review-meta"><?= _rv_date($rv->ts); ?></span>
      </div>
      <div class="review-stars">
        <?php for ($i=1;$i<=5;$i++): ?>
          <i class="mdi <?= $i <= (int)$rv->stars ? 'mdi-star full' : 'mdi-star-outline'; ?>"></i>
        <?php endfor; ?>
      </div>
      <div class="review-text"><?= nl2br(html_escape($rv->review)); ?></div>
    </div>
  </div>
<?php endforeach; else: ?>
        <div class="text-muted">Belum ada ulasan.</div>
      <?php endif; ?>

      <div class="reviews-footer">
        <a href="<?= site_url('produk/detail/'.rawurlencode($slug)).'#ulasan'; ?>" class="text-blue">Lihat ulasan selengkapnya → </a>
      </div>
    </div>
  </div>
</div>


<script>
/* kontrol Qty + Enter submit (existing) */
(function(){
  const input = document.getElementById('qty-modal');
  const inc   = document.getElementById('qty-inc');
  const dec   = document.getElementById('qty-dec');
  const btn   = document.getElementById('btn-add-cart-modal');

  if (!input) return;

  const maxAttr = parseInt(input.getAttribute('max'), 10);
  const hasMax  = Number.isFinite(maxAttr) && maxAttr > 0;
  const toInt = (v)=>{ const n=parseInt(String(v).replace(/\D+/g,''),10); return Number.isFinite(n)?n:1; };

  function syncButtons(v){ if(!inc||!dec) return; dec.disabled=(v<=1)||input.disabled; inc.disabled=(hasMax&&v>=maxAttr)||input.disabled; }
  function clamp(){ let v=toInt(input.value); if(v<1)v=1; if(hasMax&&v>maxAttr)v=maxAttr; input.value=v; syncButtons(v); return v; }

  inc && inc.addEventListener('click', function(){ let v=clamp(); if(!hasMax||v<maxAttr) input.value=++v; syncButtons(v); });
  dec && dec.addEventListener('click', function(){ let v=clamp(); if(v>1) input.value=--v; syncButtons(v); });
  input.addEventListener('input', clamp);
  input.addEventListener('blur', clamp);
  input.addEventListener('keydown', function(e){
    if (e.key === 'Enter'){
      e.preventDefault();
      const $btn = $('#btn-add-cart-modal');
      if ($btn.length && !$btn.hasClass('btn-loading') && !$btn.is(':disabled')) $btn.click();
    }
  });
  clamp();
})();
</script>
<script>
(function(){
  // ====== CSRF (CodeIgniter) ======
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

  // Helper kecil
  function esc(s){ return String(s||'').replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m])); }
  function nl2br(s){ return esc(s).replace(/\r?\n/g,'<br>'); }
  function initials(name){ name=(name||'').trim(); if(!name) return '?'; var p=name.split(/\s+/); return (p[0]?.[0]||'?') + (p[1]?.[0]||''); }
  function mask(name){ name=(name||'').trim(); if(!name) return 'Anonim'; return name.split(/\s+/).map(p => (p[0]||'?').toUpperCase()+'***').join(' '); }
  function starHtml(n){ n=parseInt(n||0,10); var h=''; for (var i=1;i<=5;i++) h += '<i class="mdi '+(i<=n?'mdi-star full':'mdi-star-outline')+'"></i>'; return h; }

  // ====== Init khusus per modal ======
  function initReviewsForModal(modal){
    if (!modal || modal.__rvInited) return;
    var box = modal.querySelector('.modal-product .reviews-box');
    var rate = modal.querySelector('.modal-product [data-rate-box]');
    if (!box || !rate) return;

    modal.__rvInited = true;

    var PROD_ID = parseInt(rate.getAttribute('data-id'),10) || 0;
    var REV_URL = "<?= site_url('produk/review_list'); ?>";

    var LAST_TOKEN = 0, INFLIGHT = false, LAST_DONE_AT = 0, TMR = null;

    function render(rows, total, token){
      if (token !== LAST_TOKEN) return;
      var footer = box.querySelector('.reviews-footer');

      if (!Array.isArray(rows)) return;

      var tot = parseInt(total||0,10);
      if (rows.length === 0 && tot > 0) return; // ada data tapi salah param? jangan wipe UI

      // wipe & replace aman (hanya anak element, bukan node teks)
      Array.from(box.children).forEach(function(n){
        if (!footer || n !== footer) n.remove();
      });

      var anchor = footer || null;

      if (rows.length === 0){
        var empty = document.createElement('div');
        empty.className = 'text-muted';
        empty.textContent = 'Belum ada ulasan.';
        box.insertBefore(empty, anchor);
      } else {
        var frag = document.createDocumentFragment();
        rows.slice(0,3).forEach(function(r){
          var rawName = (r && (r.nama||r.name||r.customer_name||r.user_name||'')) || '';
          var when    = (r && (r.ts_fmt||r.created_fmt||r.created_at_fmt||r.created_at||r.ts||'')) || '';
          var wrap = document.createElement('div');
          wrap.className = 'review-item';
          wrap.innerHTML =
            '<div class="review-avatar">'+esc(initials(rawName))+'</div>'+
            '<div class="flex-fill">'+
              '<div class="review-head">'+
                '<span class="review-name">'+esc(mask(rawName))+'</span>'+
                '<span class="review-meta">'+esc(when)+'</span>'+
              '</div>'+
              '<div class="review-stars">'+starHtml(r && r.stars)+'</div>'+
              (r && r.review ? '<div class="review-text">'+nl2br(r.review)+'</div>' : '')+
            '</div>';
          frag.appendChild(wrap);
        });
        box.insertBefore(frag, anchor);
      }

      if (!isNaN(tot)){
        modal.querySelectorAll('.count-label').forEach(function(el){ el.textContent = tot; });
      }
    }

    function refreshNow(){
      if (INFLIGHT || !PROD_ID) return;
      INFLIGHT = true;

      var token = ++LAST_TOKEN;
      var fd = new FormData();
      fd.append('produk_id', PROD_ID); // kunci utama
      fd.append('id', PROD_ID);        // kompat
      fd.append('offset', 0);
      fd.append('limit', 3);
      if (CSRF){ fd.append(CSRF.name, CSRF.hash); }

      fetch(REV_URL, {
        method: 'POST',
        headers: { 'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json' },
        body: fd
      })
      .then(r => r.json())
      .then(res => {
        if (!res || !res.success) return;
        if (res.csrf && CSRF){ CSRF.name=res.csrf.name; CSRF.hash=res.csrf.hash; }
        render(res.rows || [], res.total || 0, token);
      })
      .catch(function(){ /* diam */ })
      .finally(function(){ INFLIGHT=false; LAST_DONE_AT = Date.now(); });
    }

    function refreshOnce(){
      if (INFLIGHT) return;
      if (Date.now() - LAST_DONE_AT < 300) return;
      clearTimeout(TMR);
      TMR = setTimeout(refreshNow, 150);
    }

    // Event lokal (kalau ada aksi simpan rating/ulasan)
    modal.addEventListener('reviews:refresh', function(e){
      var id = e && e.detail && (e.detail.produk_id || e.detail.id);
      if (!id || parseInt(id,10) === PROD_ID) refreshOnce();
    });

    // Kickoff untuk modal aktif
    refreshOnce();

    // Cleanup saat modal ditutup (optional)
    modal.addEventListener('hidden.bs.modal', function(){
      modal.__rvInited = false;
    });
  }

  // Bootstrap/jQuery: init ketika modal tampil
  if (window.jQuery){
    $(document).on('shown.bs.modal', '.modal', function(){
      if (this.querySelector('.modal-product')) initReviewsForModal(this);
    });
  } else {
    // Fallback tanpa jQuery: observer sederhana
    try{
      var mo = new MutationObserver(function(){
        document.querySelectorAll('.modal.show').forEach(initReviewsForModal);
      });
      mo.observe(document.documentElement, {childList:true, subtree:true});
    }catch(_){}
  }

  // Kalau markup modal sudah ada & sudah "show" saat injected
  document.querySelectorAll('.modal.show').forEach(initReviewsForModal);

})();
</script>

