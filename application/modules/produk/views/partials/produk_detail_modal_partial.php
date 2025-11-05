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
$reviews = $this->db->select('stars, review, COALESCE(review_at, created_at) AS ts', false)
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
?>
<style>
  :root{
    --ink:#0f172a; --muted:#6b7280; --line:rgba(15,23,42,.08);
    --chip-bg:#f8fafc; --ok:#10b981; --bad:#ef4444; --brand:#1e88e5;
    --card:#fff; --shadow:0 4px 14px rgba(2,6,23,.07);
    --radius:10px;
  }
  .modal-product{ --gap:10px; }

  .modal-product .img-wrap{ border-radius:var(--radius); overflow:hidden; background:#f1f5f9; box-shadow:var(--shadow); }
  .modal-product .img-fluid{ display:block; width:100%; height:auto; object-fit:cover; }

  .category{ color:var(--brand); font-weight:700; font-size:.78rem; letter-spacing:.15px; margin-bottom:.15rem; }

  .header-line{ display:flex; align-items:flex-start; justify-content:space-between; gap:.5rem; flex-wrap:wrap; margin:.1rem 0 .2rem; }
  .product-title{ margin:0; font-weight:800; line-height:1.15; color:var(--ink); font-size:1rem; }
  .price-tag{ font-size:1.05rem; font-weight:900; color:var(--ink); white-space:nowrap; }
  .price-tag small{ font-weight:600; color:var(--muted); font-size:.8rem; margin-left:.25rem; }

  .meta-line{ display:flex; align-items:center; gap:.35rem; flex-wrap:wrap; margin:.15rem 0 .35rem; }
  .chip{ display:inline-flex; align-items:center; gap:.32rem; padding:.18rem .45rem; border-radius:999px; font-size:.74rem; font-weight:600; border:1px solid var(--line); background:var(--chip-bg); color:#111827; }
  .dot{ width:.44rem; height:.44rem; border-radius:50%; display:inline-block; }
  .dot.on{ background:var(--ok); } .dot.off{ background:var(--bad); }

  .desc-title{ font-size:.8rem; font-weight:700; color:#334155; margin:.1rem 0 .25rem; }
  .desc-box{ max-height:140px; /*overflow:auto;*/ border:1px solid var(--line); border-radius:9px; padding:.6rem; background:var(--card); margin-bottom:.6rem; box-shadow:0 1px 6px rgba(2,6,23,.04); }
  .desc-box p{ margin-bottom:.4rem; }
  .desc-box::-webkit-scrollbar{ width:6px; }
  .desc-box::-webkit-scrollbar-thumb{ background:rgba(0,0,0,.12); border-radius:999px; }

  .qty-group{ border-radius:10px; overflow:hidden; max-width:520px; box-shadow:0 2px 10px rgba(2,6,23,.05); }
  .qty-group .input-group-text{ border:none; background:#f8fafc; font-weight:700; font-size:.78rem; padding:.35rem .5rem; }
  .qty-group .btn{ min-width:36px; font-weight:800; padding:.35rem .5rem; font-size:.86rem; }
  .qty-group input[type="number"]{ text-align:center; font-weight:800; border-left:none; border-right:none; padding:.35rem .25rem; height:34px; font-size:.9rem; }

  .spinner-border.spinner-border-sm{ width:1rem; height:1rem; border-width:.15em; }

  .mb-1{ margin-bottom:.2rem!important; } .mb-2{ margin-bottom:.4rem!important; }

  /* ===== Rating area ===== */
  .rate-row{ display:flex; align-items:center; gap:.5rem; margin:.25rem 0 .35rem; }
  .star-meter{ display:flex; align-items:center; gap:2px; cursor:pointer; }
  .star-meter .mdi{ font-size:16px; line-height:1; vertical-align:middle; }
  .star-meter .full{ color:#f59e0b; } .star-meter .empty{ color:#cbd5e1; }
  .rate-link{ font-weight:600; cursor:pointer; color:#2563eb; text-decoration:underline; }
  .rate-link:hover{ text-decoration:none; }

  /* Reviews box */
  .reviews-box{ border:1px solid var(--line); border-radius:9px; padding:.6rem; background:#fff; box-shadow:0 1px 6px rgba(2,6,23,.04); }
  .review-item{ display:flex; gap:.6rem; padding:.4rem 0; border-bottom:1px dashed var(--line); }
  .review-item:last-child{ border-bottom:none; }
  .review-stars .mdi{ font-size:14px; } .review-stars .full{ color:#f59e0b; }
  .review-meta{ font-size:.74rem; color:#64748b; }
  .review-text{ margin:.15rem 0 0; font-size:.88rem; }
  .reviews-footer{ text-align:right; margin-top:.4rem; }
  .reviews-footer a{ font-weight:600; font-size:.85rem; }

  @media (max-width: 991.98px){
    .product-title{ font-size:.98rem; }
    .price-tag{ font-size:1rem; }
  }
  @media (max-width: 767.98px){
    .modal-product{ --gap:8px; }
    .desc-box{ max-height:120px; }
    .qty-group .btn{ min-width:34px; padding:.32rem .45rem; font-size:.84rem; }
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
    <div class="rate-row" data-rate-box-modal data-id="<?= (int)$product->id; ?>" data-name="<?= html_escape($product->nama); ?>">
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
          <div class="review-stars">
            <?php for ($i=1;$i<=5;$i++): ?>
              <i class="mdi <?= $i <= (int)$rv->stars ? 'mdi-star full' : 'mdi-star-outline'; ?>"></i>
            <?php endfor; ?>
          </div>
          <div class="flex-fill">
            <div class="review-meta"><?= _rv_date($rv->ts); ?></div>
            <div class="review-text"><?= nl2br(html_escape($rv->review)); ?></div>
          </div>
        </div>
      <?php endforeach; else: ?>
        <div class="text-muted">Belum ada ulasan.</div>
      <?php endif; ?>

      <div class="reviews-footer">
        <a href="<?= site_url('produk/detail/'.rawurlencode($slug)).'#ulasan'; ?>" class="text-blue">Lihat ulasan selengkapnya →</a>
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

/* ====== Rating + Review (SweetAlert) ====== */
(function(){
  // jangan double-init kalau partial dipanggil beberapa kali
  if (window.__MODAL_RATE_INIT__) return; window.__MODAL_RATE_INIT__ = true;

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
      var html=''; for(var i=0;i<full;i++) html+='<i class="mdi mdi-star full"></i>';
      if(half) html+='<i class="mdi mdi-star-half-full full"></i>';
      for(var j=0;j<empty;j++) html+='<i class="mdi mdi-star-outline empty"></i>';
      meter.innerHTML = html;
    }
  }

  function esc(s){ return String(s||'').replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#039;'}[m])); }

  function openRate(prodId, prodName, box){
  // fallback sederhana jika SweetAlert belum dimuat
  if (!window.Swal){
    var stars = parseInt(prompt('Beri rating (1–5) untuk: '+prodName, 5) || '0', 10);
    if (!stars || stars < 1 || stars > 5) return;
    var rev = prompt('Tulis review (opsional):','') || '';
    postJSON('<?= site_url('produk/rate') ?>', { id:prodId, stars:stars, review:rev })
      .then(function(resp){
        if (!resp || !resp.success){ alert((resp && resp.pesan) || 'Gagal menyimpan rating'); return; }
        renderAvg(box, resp.avg, resp.count);
        alert('Terima kasih!');
      });
    return;
  }

  var chosen = 0, maxLen = 1000;
  var esc = function(s){ return String(s||'').replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#039;'}[m])); };

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
    returnFocus: false,   // penting: jangan rebut fokus kembali
    didOpen: function(m){
      // 🔧 Matikan focus trap Bootstrap agar textarea bisa diketik
      if (window.jQuery) { $(document).off('focusin.bs.modal'); }
      // (opsional) izinkan fokus ke elemen dalam swal meski ada modal
      document.addEventListener('focusin', function onFI(e){
        if (e.target && e.target.closest && e.target.closest('.swal2-container')) {
          e.stopImmediatePropagation();
        }
      }, {capture:true, once:true});

      var wrap = m.querySelector('.swal-rate-wrap'),
          hint = m.querySelector('#swal-rate-hint'),
          ta   = m.querySelector('#swal-review'),
          cnt  = m.querySelector('#swal-count');

      function draw(){
        wrap.querySelectorAll('.rate-star').forEach(function(el){
          var n = parseInt(el.getAttribute('data-n')||'0',10);
          el.className = 'mdi rate-star ' + (n<=chosen ? 'mdi-star' : 'mdi-star-outline');
          el.style.color = (n<=chosen ? '#f59e0b' : '');
        });
        hint.textContent = chosen ? (chosen+'/5') : 'Pilih 1–5 bintang';
      }

      wrap.addEventListener('click', function(e){
        var ic = e.target.closest('.rate-star'); if(!ic) return;
        chosen = parseInt(ic.getAttribute('data-n')||'0',10);
        draw();
      });

      ta.addEventListener('input', function(){
        if (this.value.length > maxLen) this.value = this.value.slice(0, maxLen);
        cnt.textContent = this.value.length + '/' + maxLen;
      });

      draw();
      setTimeout(function(){ ta && ta.focus(); }, 50);
    },
    preConfirm: function(){
      var r = (document.getElementById('swal-review')||{}).value || '';
      if (!chosen){
        Swal.showValidationMessage('Pilih minimal 1 bintang');
        return false;
      }
      return { stars: chosen, review: r.trim() };
    }
  }).then(function(res){
    if (!res.isConfirmed || !res.value) return;
    postJSON('<?= site_url('produk/rate') ?>', {
      id: prodId,
      stars: res.value.stars,
      review: res.value.review
    })
    .then(function(resp){
      if (!resp || !resp.success){
        Swal.fire('Gagal', (resp && resp.pesan) || 'Gagal menyimpan rating', 'error');
        return;
      }
      renderAvg(box, resp.avg, resp.count);
      Swal.fire({ icon:'success', title:'Terima kasih!', text:'Rating & review tersimpan.', timer:1300, showConfirmButton:false });
    })
    .catch(function(){
      Swal.fire('Gagal', 'Kesalahan jaringan.', 'error');
    });
  });
}


  // delegasi klik untuk bintang & "Beri rating"
  document.addEventListener('click', function(e){
    var star = e.target.closest('[data-rate-box-modal] .star-meter');
    var link = e.target.closest('[data-rate-box-modal] .rate-link');
    if (!star && !link) return;
    var box = (star||link).closest('[data-rate-box-modal]');
    var id  = parseInt(box.getAttribute('data-id')||'0',10);
    var nm  = box.getAttribute('data-name')||'Produk';
    if (!id) return;
    openRate(id, nm, box);
  }, {passive:true});
})();
</script>
