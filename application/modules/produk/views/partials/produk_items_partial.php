<?php if (empty($products)): ?>
  <div class="col-12 px-0 grid-span-all">
    <style>
      .grid-span-all{ width:100%; }
      @supports (display: grid){
        .grid-span-all{ grid-column: 1 / -1 !important; }
      }
      .empty-state{
        border: 1px dashed #b9c3d6;
        background: #f8fbff;
        padding: 0;
        border-radius: 0;
      }
      .empty-state__inner{ padding: 16px; }
      .empty-state .emoji{ font-size:42px; line-height:1; margin-bottom:8px; }
      .empty-state h5{ margin:6px 0 4px; font-weight:700; }
      .empty-state p{ margin:0 0 12px; }
      @media (min-width: 768px){
        .empty-state{ border-radius: 10px; }
        .empty-state__inner{ padding: 32px; }
      }
    </style>


    <div class="empty-state my-3">
      <div class="empty-state__inner text-center">
        <div class="emoji">🛒</div>
        <h5>Belum nemu yang pas 😅</h5>
        <p class="text-muted">Coba ganti kata kunci, ubah filter, atau liat semua menu lagi.</p>

        <div class="d-flex flex-wrap justify-content-center">
          <button type="button" class="btn btn-outline-secondary btn-sm mr-2 btn-reload">Muat ulang</button>
          <button type="button" class="btn btn-primary btn-sm btn-reset-filter">Reset filter</button>
        </div>
      </div>
    </div>

    <script>
      (function(){
        var reloadBtn = document.querySelector('.btn-reload');
        var resetBtn  = document.querySelector('.btn-reset-filter');
        if (reloadBtn){ reloadBtn.addEventListener('click', function(){ location.reload(); }); }
        if (resetBtn){ resetBtn.addEventListener('click', function(){ location.href = location.pathname; }); }
      })();
    </script>
  </div>
<?php else: ?>
  <style>
    .product-img-bg{ position:relative; overflow:hidden; border-radius:10px; }

    .corner-ribbon{
      --h:22px; --w:160px;
      position:absolute; z-index:5; top:12px; left:-42px;
      width:var(--w); line-height:var(--h);
      transform: rotate(-45deg);
      text-align:center; color:#fff; font-weight:700; font-size:12px;
      letter-spacing:.2px; box-shadow:0 6px 14px rgba(0,0,0,.12); pointer-events:none;
    }
    .corner-ribbon.hot{ background:#ab0808; }
    .corner-ribbon.success{ background:#16a34a; }
    @media (max-width: 767.98px){
      .corner-ribbon{ --h:20px; --w:120px; top:10px; left:-36px; font-size:11px; }
    }

    .star-meter{ display:flex; align-items:center; gap:2px; margin:.1rem 0 .15rem; cursor:pointer; }
    .star-meter .mdi{ font-size:14px; line-height:1; vertical-align:middle; }
    .star-meter .full{ color:#f59e0b; }
    .star-meter .empty{ color:#cbd5e1; }

    .rate-link{ font-size:.8rem; margin-left:.35rem; color:#2563eb; text-decoration:underline; cursor:pointer; }
    .rate-link:hover{ text-decoration:none; }

    .sold-label{ font-size:.72rem; color:#64748b; margin:0 0 .25rem; }
    .card-box { margin-bottom: 0px !important; border-radius: .60rem !important; }

    .product-price-tag{
      display:inline-block; padding:.15rem .6rem .15rem .5rem;
      border-radius:1.5rem !important; background:#795548 !important; color:#ffffff;
      font-weight:700; font-size:.95rem !important; white-space:nowrap;
      height:30px !important; line-height:30px !important;
    }
    .product-price-tag .cur{
      position:relative; top:-0.55em; font-size:.65em; margin-right:.15rem;
      letter-spacing:.3px; opacity:.95; display:inline-block;
    }
    .spinner-border { display:inline-block;width:.9rem;height:.9rem;border:.15rem solid currentColor;border-right-color:transparent;border-radius:50%;animation:spin .6s linear infinite;vertical-align:-0.2em;margin-right:.4rem; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .d-none{display:none!important}
  </style>
<style>
/* Default: teks tetap muncul di desktop */
.btn-icon-only-sm .btn-text{ display:inline; }

/* Mobile: icon-only (≤ 768px) */
@media (max-width: 767.98px){
  .btn-icon-only-sm{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:.48rem .56rem;           /* lebih ringkas di mobile */
    min-width:44px; min-height:38px; /* area sentuh nyaman */
  }
  .btn-icon-only-sm .btn-text{ display:none !important; }
  .btn-icon-only-sm i{
    margin:0 !important;
    font-size:18px;
    line-height:1;
  }
  /* kalau ada label tambahan */
  .btn-icon-only-sm .btn-label{ margin-right:0 !important; }
}
</style>

  <?php
    $BESTSELLER_MIN = 1;
    $NEW_DAYS       = 3;
    $nowTs          = time();
  ?>

  <?php foreach ($products as $p):
    $img     = $p->gambar ? (strpos($p->gambar,'http')===0 ? $p->gambar : base_url($p->gambar)) : base_url('assets/images/products/no-image.png');
    $stok    = (int)($p->stok ?? 0);
    $slug    = $p->link_seo;
    $soldout = ($stok === 0);

    $soldAll   = isset($p->sold_all)   ? (int)$p->sold_all   : null;
    $sold30    = isset($p->sold)       ? (int)$p->sold       : null;
    $soldMonth = isset($p->sold_month) ? (int)$p->sold_month : null;

    $createdAt = !empty($p->created_at) ? strtotime($p->created_at) : null;
    $isNew     = $createdAt ? (($nowTs - $createdAt) <= ($NEW_DAYS * 86400)) : false;

    $basisLaris = ($soldAll !== null) ? $soldAll : (($soldMonth !== null) ? $soldMonth : (($sold30 !== null) ? $sold30 : null));
    $isHot      = ($basisLaris !== null && $basisLaris >= $BESTSELLER_MIN);

    $ribbon = null;
    if (!$soldout) {
      if ($isHot)       $ribbon = ['class'=>'hot', 'text'=>'Terlaris'];
      elseif ($isNew)   $ribbon = ['class'=>'success', 'text'=>'Terbaru'];
    }

    $ratingAvg   = isset($p->rating_avg)   ? (float)$p->rating_avg   : null;
    $ratingCount = isset($p->rating_count) ? (int)$p->rating_count   : 0;
    $rating      = ($ratingAvg !== null) ? max(0,min(5,$ratingAvg)) : null;
    $ratingRounded = ($rating !== null) ? (round($rating * 2) / 2) : null;

    $full = $half = $empty = 0;
    if ($ratingRounded !== null) {
      $full  = (int)floor($ratingRounded);
      $half  = ((float)$ratingRounded - $full) === 0.5;
      $empty = 5 - $full - ($half ? 1 : 0);
    }
  ?>
    <div class="col-md-6 col-xl-3">
      <div class="card-box product-box">
        <div class="product-img-bg">
          <a href="javascript:void(0)" class="btn-detail d-block" data-slug="<?= html_escape($slug); ?>">
            <img src="<?= $img; ?>" alt="<?= html_escape($p->nama); ?>" class="img-fluid">
          </a>

          <?php if ($soldout): ?>
            <span class="badge badge-danger" style="position:absolute;top:10px;left:10px">Habis</span>
          <?php endif; ?>

          <?php if ($ribbon): ?>
            <span class="corner-ribbon <?= $ribbon['class']; ?>">
              <?= $ribbon['text']; ?>
            </span>
          <?php endif; ?>
        </div>

        <div class="product-info">
          <h5 class="font-14 mt-2 sp-line-1 mb-1">
            <a href="javascript:void(0)" class="text-dark btn-detail" data-slug="<?= html_escape($slug); ?>">
              <?= html_escape(ucwords($p->nama)); ?>
            </a>
          </h5>

          <!-- AREA RATING (klik bintang atau teks "Beri rating") -->
          <div class="d-flex align-items-center"
               data-rate-box
               data-id="<?= (int)$p->id; ?>"
               data-name="<?= html_escape($p->nama); ?>">
            <div class="star-meter" aria-label="Rating: <?= $rating ? number_format($rating,1,',','.') : '0.0'; ?>/5">
              <?php if ($ratingRounded !== null): ?>
                <?php for ($i=0; $i<$full; $i++): ?><i class="mdi mdi-star full"></i><?php endfor; ?>
                <?php if ($half): ?><i class="mdi mdi-star-half-full full"></i><?php endif; ?>
                <?php for ($i=0; $i<$empty; $i++): ?><i class="mdi mdi-star-outline empty"></i><?php endfor; ?>
              <?php else: ?>
                <?php for ($i=0; $i<5; $i++): ?><i class="mdi mdi-star-outline empty"></i><?php endfor; ?>
              <?php endif; ?>
            </div>
            <a class="rate-link"><i class="mdi mdi-fountain-pen-tip"></i> Ulas</a>
          </div>

          <div class="sold-label">
            <?= $rating ? number_format($rating,1,',','.') : '0.0' ?>/5
            <?= $ratingCount ? ' · '.$ratingCount.' ulasan' : '' ?>
            <?php if ($basisLaris !== null): ?> · <?= number_format($basisLaris,0,',','.'); ?> terjual<?php endif; ?>
          </div>

          <style type="text/css">
            .product-price-tag { height: 30px !important; line-height: 30px !important; }
          </style>

          <div class="text-right mt-1">
            <div class="product-price-tag">
              <span class="cur">Rp</span><?= number_format((float)$p->harga, 0, ',', '.'); ?>
            </div>
          </div>

          <div class="mt-2 d-flex align-items-center justify-content-between">
            <button type="button"
                    class="btn btn-sm btn-blue btn-detail btn-icon-only-sm"
                    data-slug="<?= html_escape($slug); ?>"
                    aria-label="Detail">
              <span class="spinner-border d-none" aria-hidden="true"></span>
              <i class="mdi mdi-eye-outline icon-default" aria-hidden="true"></i>
              <span class="btn-text">Detail</span>
            </button>

            <button type="button"
                    class="btn btn-sm btn-danger waves-effect waves-light btn-add-cart btn-icon-only-sm"
                    data-id="<?= (int)$p->id; ?>"
                    data-qty="1"
                    <?= $soldout ? 'disabled' : ''; ?>
                    aria-label="Tambah ke keranjang">
              <span class="spinner-border d-none" aria-hidden="true"></span>
              <i class="mdi mdi-cart icon-default" aria-hidden="true"></i>
              <span class="btn-text">+ keranjang</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<script>
(function(){
  if (window.__RATING_SWEETALERT_INIT__) return;
  window.__RATING_SWEETALERT_INIT__ = true;

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

  function roundHalf(v){ var rr=Math.round(v*2)/2; return Math.max(0,Math.min(5,rr)); }

  function applyAvgToCard(card, avg, count){
    var soldLbl = card.querySelector('.sold-label');
    if (soldLbl){
      var txtTerjual = (soldLbl.textContent.match(/·\s[\d\.]+\s+terjual/i)||[''])[0];
      soldLbl.textContent = avg.toFixed(1).replace('.',',') + '/5'
        + (count ? ' · '+count+' ulasan' : '')
        + (txtTerjual ? ' ' + txtTerjual : '');
    }
    var meter = card.querySelector('.star-meter');
    if (meter){
      var r=roundHalf(avg), full=Math.floor(r), half=(r-full)===0.5, empty=5-full-(half?1:0);
      var html=''; for(var i=0;i<full;i++) html+='<i class="mdi mdi-star full"></i>';
      if(half) html+='<i class="mdi mdi-star-half-full full"></i>';
      for(var j=0;j<empty;j++) html+='<i class="mdi mdi-star-outline empty"></i>';
      meter.innerHTML=html;
    }
  }

  function escapeHtml(str){
    return String(str||'').replace(/[&<>"']/g, function(m){
      return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]);
    });
  }

  function openRatingModal(prodId, prodName){
    if (!window.Swal){
      var val = prompt('Beri rating (1–5) untuk: '+prodName, 5);
      var stars = parseInt(val||'0',10);
      if (!stars||stars<1||stars>5) return;
      var rev = prompt('Tulis review (opsional):','');
      submitRating(prodId, stars, rev||'');
      return;
    }

    var selected = 0;
    var maxLen   = 1000;

    Swal.fire({
    title: 'Kasih Rating',
    html:
      '<div style="margin-top:.25rem; font-weight:600;">'+escapeHtml(prodName)+'</div>'+
      '<div class="swal-rate-wrap" style="margin:.75rem 0 .5rem; display:flex; justify-content:center; gap:8px;">' +
        [1,2,3,4,5].map(function(n){
          return '<i class="mdi mdi-star-outline rate-star" data-n="'+n+'" '+
                 'style="font-size:28px; cursor:pointer;"></i>';
        }).join('') +
      '</div>'+
      '<div class="small text-muted" id="swal-rate-hint" style="margin-bottom:.5rem;">Pilih 1–5 bintang, gaskeun!</div>'+
      '<textarea id="swal-review" class="form-control" rows="3" '+
        'placeholder="Tulis review singkat (boleh kosong)" style="resize:vertical;"></textarea>'+
      '<div class="small text-muted mt-1" id="swal-count">0/'+maxLen+'</div>',
      showCancelButton: true,
      confirmButtonText: 'Kirim',
      cancelButtonText: 'Nanti',
      focusConfirm: false,
      didOpen: function(modal){
        var wrap = modal.querySelector('.swal-rate-wrap');
        var hint = modal.querySelector('#swal-rate-hint');
        var ta   = modal.querySelector('#swal-review');
        var cnt  = modal.querySelector('#swal-count');

        function render(){
          var stars = wrap.querySelectorAll('.rate-star');
          stars.forEach(function(el){
            var n = parseInt(el.getAttribute('data-n')||'0',10);
            el.className = 'mdi rate-star ' + (n<=selected ? 'mdi-star' : 'mdi-star-outline');
            el.style.color = (n<=selected ? '#f59e0b' : '');
          });
          hint.textContent = selected ? (selected+'/5') : 'Pilih 1–5 bintang';
        }
        wrap.addEventListener('click', function(e){
          var icon = e.target.closest('.rate-star');
          if (!icon) return;
          selected = parseInt(icon.getAttribute('data-n')||'0',10);
          render();
        });

        ta.addEventListener('input', function(){
          if (this.value.length > maxLen) this.value = this.value.slice(0, maxLen);
          cnt.textContent = this.value.length + '/' + maxLen;
        });

        render();
      },
      preConfirm: function(){
        var ta = document.getElementById('swal-review');
        var review = ta ? ta.value.trim() : '';
        if (!selected){
          Swal.showValidationMessage('Pilih minimal 1 bintang');
          return false;
        }
        return {stars:selected, review:review};
      }
    }).then(function(res){
      if (res.isConfirmed && res.value){
        submitRating(prodId, res.value.stars, res.value.review||'');
      }
    });
  }

  function submitRating(prodId, stars, review){
    var box  = document.querySelector('[data-rate-box][data-id="'+prodId+'"]');
    var card = box ? (box.closest('.product-box') || document) : document;

    postJSON('<?= site_url('produk/rate') ?>', { id: prodId, stars: stars, review: review })
      .then(function(res){
        if (!res || !res.success){
          if (window.Swal) Swal.fire('Gagal', (res&&res.pesan)||'Gagal menyimpan rating', 'error');
          else alert((res&&res.pesan)||'Gagal menyimpan rating');
          return;
        }
        applyAvgToCard(card, parseFloat(res.avg||0), parseInt(res.count||0,10));
        if (window.Swal) {
          Swal.fire({
            icon: 'success',
            title: 'Thankyou! ✨',
            text: 'Rating & review-nya keren banget~',
            timer: 1300,
            showConfirmButton: false
          });

        }
      })
      .catch(function(){
        if (window.Swal) Swal.fire('Gagal', 'Terjadi kesalahan jaringan.', 'error');
        else alert('Terjadi kesalahan jaringan.');
      });
  }

  // Klik bintang/teks "Beri rating" => buka modal rating + review
  document.addEventListener('click', function(e){
    var star = e.target.closest('[data-rate-box] .star-meter');
    var link = e.target.closest('[data-rate-box] .rate-link');
    var box  = star ? star.closest('[data-rate-box]') : (link ? link.closest('[data-rate-box]') : null);
    if (!box) return;
    var id   = parseInt(box.getAttribute('data-id')||'0',10);
    var name = box.getAttribute('data-name') || 'Produk';
    if (!id) return;
    openRatingModal(id, name);
  }, {passive:true});
})();
</script>
