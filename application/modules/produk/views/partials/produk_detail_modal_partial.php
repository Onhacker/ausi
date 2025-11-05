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
            <span class="chip" title="Stok tersedia"><span class="dot on"></span> Instock</span>
          <?php else: ?>
            <span class="chip" title="Stok habis"><span class="dot off"></span> Sold Out</span>
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
          <span class="icon-default mr-1"><i class="mdi mdi-cart"></i></span>
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
  if (window.__RV_MODAL_SYNC__) return;
  window.__RV_MODAL_SYNC__ = true;

  var PROD_ID = <?= (int)$product->id ?>;
  var REV_URL = "<?= site_url('produk/review_list'); ?>";

  // CSRF mengikuti konfigurasi CI
  var CSRF = <?php
    if ($this->config->item('csrf_protection')) {
      echo json_encode([
        'name' => $this->security->get_csrf_token_name(),
        'hash' => $this->security->get_csrf_hash()
      ]);
    } else { echo 'null'; }
  ?>;

  /* ===== Helpers ===== */
  function esc(s){ return String(s||'').replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m])); }
  function nl2br(s){ return esc(s).replace(/\r?\n/g,'<br>'); }
  function initials(name){
    name=(name||'').trim(); if(!name) return '?';
    var p=name.split(/\s+/);
    return (p[0]&&p[0][0]?p[0][0]:'?') + ((p[1]&&p[1][0])?p[1][0]:'');
  }
  function mask(name){
    name=(name||'').trim(); if(!name) return 'Anonim';
    return name.split(/\s+/).map(p => (p[0]||'?').toUpperCase()+'***').join(' ');
  }
  function starHtml(n){
    n=parseInt(n||0,10); var h='';
    for (var i=1;i<=5;i++) h += '<i class="mdi '+(i<=n?'mdi-star full':'mdi-star-outline')+'"></i>';
    return h;
  }
  function findBox(){
    // utamakan modal yang sedang tampil
    return document.querySelector('.modal.show .modal-product .reviews-box')
        || document.querySelector('.modal-product .reviews-box')
        || document.querySelector('.reviews-box');
  }

  /* ===== Render (wipe & replace) ===== */
  var LAST_TOKEN = 0;
  function renderReviews(rows, total, token){
    if (token !== LAST_TOKEN) return; // abaikan hasil lama
    var BOX = findBox(); if (!BOX) return;

    var footer = BOX.querySelector('.reviews-footer');

    // hapus semua selain footer → cegah dobel
    Array.from(BOX.childNodes).forEach(function(n){
      if (!footer || n !== footer) BOX.removeChild(n);
    });

    var anchor = footer || null;

    if (!rows || !rows.length){
      var empty = document.createElement('div');
      empty.className = 'text-muted';
      empty.textContent = 'Belum ada ulasan.';
      BOX.insertBefore(empty, anchor);
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
      BOX.insertBefore(frag, anchor);
    }

    // update label jumlah ulasan (di area modal saja)
    var scope = BOX.closest('.modal-product') || document;
    scope.querySelectorAll('.count-label').forEach(function(el){
      el.textContent = parseInt(total||0,10);
    });
  }

  /* ===== Single-flight refresh (maks 1 request aktif/terjadwal) ===== */
  var TMR = null, INFLIGHT = false, SCHEDULED = false;
  var LAST_DONE_AT = 0;  // cap waktu fetch terakhir selesai

  function refreshNow(){
    if (INFLIGHT){ return; }          // jangan jalankan fetch kedua
    INFLIGHT = true; SCHEDULED = false;

    var token = ++LAST_TOKEN;

    var fd = new FormData();
    fd.append('id', PROD_ID);
    fd.append('offset', 0);
    fd.append('limit', 3);
    if (CSRF){ fd.append(CSRF.name, CSRF.hash); }

    fetch(REV_URL, {
      method:'POST',
      headers:{'X-Requested-With':'XMLHttpRequest'},
      body: fd
    })
    .then(function(r){ return r.json(); })
    .then(function(res){
      if (!res || !res.success) return;
      if (res.csrf && CSRF){ CSRF.name=res.csrf.name; CSRF.hash=res.csrf.hash; }
      renderReviews(res.rows||[], res.total||0, token);
    })
    .catch(function(){ /* diam */ })
    .finally(function(){
      INFLIGHT = false;
      LAST_DONE_AT = Date.now();
    });
  }

  function refreshOnce(){
    if (SCHEDULED || INFLIGHT) return;
    // throttle: cegah fetch kedua beruntun setelah fetch baru saja selesai
    if (Date.now() - LAST_DONE_AT < 300) return;
    SCHEDULED = true;
    clearTimeout(TMR);
    TMR = setTimeout(refreshNow, 150);
  }

  /* ===== Trigger dari form rating/ulasan ===== */
  ['rv-prepended','reviews:refresh','rating:success','rating:saved','review:success','review:saved','ulasan:updated']
    .forEach(function(ev){
      document.addEventListener(ev, function(e){
        var id = e && e.detail && (e.detail.produk_id || e.detail.produkId || e.detail.id);
        if (!id || parseInt(id,10) === PROD_ID) refreshOnce();
      });
    });

  /* ===== Intercept POST penyimpanan rating/ulasan (bukan review_list) ===== */
  (function(){
    if (window.__RV_FETCH_WRAP_DONE__) return;
    window.__RV_FETCH_WRAP_DONE__ = true;

    // pastikan "review_list" tidak ikut match
    var SAVE_RE = /\/produk\/(?![^?#]*review_list)[^?#]*(rating|review(?!_list)|ulasan|rate|nilai|kirim|save)/i;

    function shouldHook(url, method){
      return method && method.toUpperCase()==='POST' && SAVE_RE.test(String(url||''));
    }

    if (window.fetch){
      var _fetch = window.fetch;
      window.fetch = function(input, init){
        var url = (typeof input==='string') ? input : (input && input.url) || '';
        var method = (init && init.method) || (typeof input!=='string' && input && input.method) || 'GET';
        var hook = shouldHook(url, method);
        return _fetch(input, init).then(function(res){
          if (hook && res && res.ok) refreshOnce();
          return res;
        });
      };
    }

    if (window.XMLHttpRequest){
      var _open = XMLHttpRequest.prototype.open, _send = XMLHttpRequest.prototype.send;
      XMLHttpRequest.prototype.open = function(m,u){ this.__rv_hook = shouldHook(u, m); return _open.apply(this, arguments); };
      XMLHttpRequest.prototype.send = function(b){
        if (this.__rv_hook){
          this.addEventListener('load', function(){
            if (this.status >= 200 && this.status < 300) refreshOnce();
          });
        }
        return _send.apply(this, arguments);
      };
    }
  })();

  /* ===== Saat modal tampil / node baru muncul ===== */
 if (window.jQuery){
  $(document).on('shown.bs.modal', '.modal', function(){
    // Hanya refresh jika modal ini memang punya kotak ulasan
    var box = this.querySelector('.modal-product .reviews-box');
    if (box && !box.__rv_inited){
      box.__rv_inited = true;
      refreshOnce();
    }
  });
}

  try{
    var mo = new MutationObserver(function(){
      var box = findBox();
      if (box && !box.__rv_inited){
        box.__rv_inited = true;
        refreshOnce();
      }
    });
    mo.observe(document.documentElement, {childList:true, subtree:true});
  }catch(_){}

  // initial satu kali saja (kalau modal sudah terpasang di DOM)
  if (findBox()) refreshOnce();
})();
</script>

