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
<?php $this->load->view("partials/form_rating") ?>