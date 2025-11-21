<?php $this->load->view("front_end/head.php"); ?>

<div class="container-fluid">
  <!-- Hero -->
  <div class="hero-title ausi-hero-center" role="banner" aria-label="Judul halaman">
    <i class="  ti-arrow-left ausi-btn-back" onclick="ausiBack()"></i>
    
    <style type="text/css">
      .ausi-hero-center{
        position: relative;
        text-align: center !important;   /* pastikan title/subtitle center */
        padding: 24px 0 14px;
      }
      .ausi-btn-back{
        position: absolute;
        left: 0px;                            
        width: 30px; height: 30px;
        display: inline-flex; align-items: center; justify-content: center;
        color: #fff;
        font-weight: 700;
        font-size: 18px;
      }
    </style>
    <script>
      function ausiBack(){
        window.location.href = "<?= site_url("produk") ?>";
      }
    </script>
    <h1 class="text mb-1">Keranjang</h1>
    <span class="accent" aria-hidden="true"></span>
  </div>

  <!-- Mode badge -->
  <?php $this->load->view("judul_mode") ?>

  <div class="card card-body">
    <?php if (empty($items)): ?>
      <div class="text-center py-5">
        <div style="font-size:2.5rem;line-height:1">🧺</div>
        <h5 class="mt-2">Belum ada apa-apa di sini, bestie~</h5>
        <a href="<?= site_url('produk') ?>" class="btn btn-primary mt-3">
          <i class="mdi mdi-arrow-left"></i> Kembali ke Menu
        </a>
      </div>
    <?php else: ?>

      <!-- Tabel Desktop -->
      <div class="table-responsive d-none d-md-block">
        <table class="table table-centered table-striped mb-0 cart-table">
          <thead class="thead-light">
            <tr>
              <th style="width:80px">Gambar</th>
              <th>Produk</th>
              <th class="text-right" style="width:140px">Harga</th>
              <th class="text-center" style="width:200px">Qty</th>
              <th class="text-right" style="width:140px">Subtotal</th>
              <th class="text-center" style="width:80px">Hapus</th>
            </tr>
          </thead>
          <tbody id="cart-tbody">
            <?php foreach($items as $it):
              $img   = $it->gambar ? base_url($it->gambar) : base_url('assets/images/icon_app.png');
              $harga = (int)$it->harga;
              $qty   = (int)$it->qty;
              $sub   = $harga * $qty;
            ?>
            <tr data-id="<?= (int)$it->produk_id ?>" class="align-middle">
              <td class="no-label" data-label="Gambar">
                <img src="<?= $img ?>" class="img-fluid avatar-md rounded shadow-sm" style="width:64px;height:64px;object-fit:cover" alt="">
              </td>
              <td data-label="Produk">
                <div class="font-weight-600 mb-1"><?= html_escape($it->nama ?? '') ?></div>
                <?php if (!empty($it->slug)): ?>
                  <small><a href="javascript:void(0)" class="link-detail" data-slug="<?= html_escape($it->slug) ?>">Lihat detail</a></small>
                <?php endif; ?>
              </td>
              <td class="text-right" data-label="Harga">
                <span class="harga" data-harga="<?= $harga ?>">Rp <?= number_format($harga,0,',','.') ?></span>
              </td>
              <td class="text-center" data-label="Qty">
                <div class="input-group input-group-sm justify-content-center" style="max-width:200px">
                  <div class="input-group-prepend">
                    <button class="btn btn-light btn-dec" type="button" aria-label="Kurangi">
                      <i class="mdi mdi-minus"></i>
                    </button>
                  </div>
                  <input type="number" min="0" step="1" class="form-control text-center qty-input" value="<?= $qty ?>" style="max-width:90px">
                  <div class="input-group-append">
                    <button class="btn btn-light btn-inc" type="button" aria-label="Tambah">
                      <i class="mdi mdi-plus"></i>
                    </button>
                  </div>
                </div>
              </td>
              <td class="text-right subtotal" data-label="Subtotal">Rp <?= number_format($sub,0,',','.') ?></td>
              <td class="text-center no-label" data-label="Hapus">
                <button class="btn btn-sm btn-outline-danger btn-remove" type="button">
                  <i class="mdi mdi-trash-can-outline"></i> Hapus
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="4" class="text-right">Total</th>
              <th class="text-right" id="cart-total">Rp <?= number_format((int)$total,0,',','.') ?></th>
              <th></th>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Kartu Mobile -->
      <div class="d-md-none">
        <div id="cart-tbody-mobile" class="row no-gutters">
          <?php foreach($items as $it):
            $img   = $it->gambar ? base_url($it->gambar) : base_url('assets/images/icon_app.png');
            $harga = (int)$it->harga;
            $qty   = (int)$it->qty;
            $sub   = $harga * $qty;
          ?>
          <div class="col-12 mb-2">
            <div class="p-2 border rounded cart-card" data-id="<?= (int)$it->produk_id ?>">
              <div class="d-flex">
                <img src="<?= $img ?>" class="rounded mr-2" style="width:64px;height:64px;object-fit:cover" alt="">
                <div class="flex-grow-1">
                  <div class="font-weight-600 d-flex align-items-start justify-content-between">
                    <span><?= html_escape($it->nama ?? '') ?></span>
                    <button class="btn btn-unstyled p-0 ml-2 btn-remove text-danger" type="button" aria-label="Hapus">
                      <i class="mdi mdi-trash-can-outline"></i>
                    </button>
                  </div>
                  <?php if (!empty($it->slug)): ?>
                    <small><a href="javascript:void(0)" class="link-detail" data-slug="<?= html_escape($it->slug) ?>">Lihat detail</a></small>
                  <?php endif; ?>
                  <div class="d-flex align-items-center justify-content-between mt-1">
                    <div class="small text-muted">
                      <span class="harga" data-harga="<?= $harga ?>">Rp <?= number_format($harga,0,',','.') ?></span>
                    </div>
                    <div class="input-group input-group-sm" style="width:124px">
                      <div class="input-group-prepend">
                        <button class="btn btn-light btn-dec" type="button"><i class="mdi mdi-minus"></i></button>
                      </div>
                      <input type="number" min="0" step="1" class="form-control text-center qty-input" value="<?= $qty ?>">
                      <div class="input-group-append">
                        <button class="btn btn-light btn-inc" type="button"><i class="mdi mdi-plus"></i></button>
                      </div>
                    </div>
                  </div>
                  <div class="d-flex align-items-center justify-content-between mt-1">
                    <small class="text-muted">Subtotal</small>
                    <div class="subtotal font-weight-600">Rp <?= number_format($sub,0,',','.') ?></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Total ringkas mobile -->
        <div class="d-flex justify-content-between align-items-center border-top pt-2">
          <div class="font-weight-600"><strong>Total</strong></div>
          <div class="font-weight-700" id="cart-total"><strong>Rp <?= number_format((int)$total,0,',','.') ?></strong></div>
        </div>
      </div>

      <!-- Footer actions -->
      <!-- <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 sticky-actions"> -->
        <!-- <a href="<?= site_url('produk') ?>" class="btn btn-outline-secondary">
          <i class="mdi mdi-arrow-left"></i> Kembali
        </a> -->
        <div class="d-flex align-items-center mt-2">
          <!-- <div class="mr-3 d-none d-md-block">
            <small class="text-muted">Total</small>
            <div class="h5 mb-0" id="cart-total-desktop">Rp <?= number_format((int)$total,0,',','.') ?></div>
          </div> -->
          <style>
          /* gaya dasar tombol kamu, asumsi sudah ada .btn, .btn-blue, .btn-block */

          /* state loading */
          .btn-loading {
            pointer-events: none;
            opacity: .7;
            position: relative;
          }

          /* spinner mini */
          .spinner-border {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: .15rem solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spin .6s linear infinite;
            vertical-align: -0.2em;
            margin-left: .5rem;
          }

          @keyframes spin {
            to { transform: rotate(360deg); }
          }
        </style>

        <a
          href="<?= site_url('produk/order') ?>"
          class="btn btn-blue btn-block js-go-order"
        >
          <span class="btn-text">Lanjutkan Pesan</span>
          <i class="mdi mdi-arrow-right ml-1 btn-icon"></i>
        </a>

        <script>
        (function(){
          const link = document.querySelector('.js-go-order');
          if (!link) return;

          link.addEventListener('click', function(e){
            // kalau sudah loading, jangan eksekusi lagi
            if (link.classList.contains('btn-loading')) {
              e.preventDefault();
              return;
            }

            // ubah tampilan ke loading
            link.classList.add('btn-loading');

            // ganti isi icon jadi spinner
            const iconEl = link.querySelector('.btn-icon');
            if (iconEl) {
              iconEl.outerHTML = '<span class="spinner-border" aria-hidden="true"></span>';
            }

            // optional: ganti teks juga kalau mau
            const txtEl = link.querySelector('.btn-text');
            if (txtEl) {
              txtEl.textContent = 'Mohon tunggu...';
            }

            // biarkan browser lanjut ke href normal.
            // TIDAK preventDefault => langsung navigate.
            // (Kalau kamu pakai ajax single-page, baru kita preventDefault.)
          }, {passive:true});
        })();
        </script>

        </div>
      <!-- </div> -->
    <?php endif; ?>
  </div>
</div>

<!-- Assets -->

<?php $this->load->view("front_end/footer.php"); ?>
<!-- Basic styles -->
<style>
  .cart-table thead th { white-space: nowrap; }
  .cart-card{ box-shadow:0 8px 20px rgba(16,24,40,.04); background:#fff; }
  .btn-unstyled{ background:none;border:0; }
  .sticky-actions{ position:sticky; bottom:0; padding:.5rem .25rem;
    background:linear-gradient(180deg, rgba(255,255,255,0) 0%, #fff 40%); z-index:5; }
  .input-group-sm .btn { min-width:32px; }
  @media (min-width: 768px){
    .cart-table tbody tr:hover { background:#fff; box-shadow: inset 0 0 0 999px rgba(16,24,40,.015); }
  }
  @media (max-width: 767.98px){
    .cart-table { border-collapse: separate; border-spacing: 0 12px; }
    .cart-table thead { display: none; }
    .cart-table tbody tr {
      display:block; background:#fff; border:1px solid #eef2f7; border-radius:12px;
      padding:.75rem .75rem .5rem; box-shadow:0 8px 20px rgba(16,24,40,.04);
    }
    .cart-table tbody td { display:flex; align-items:center; justify-content:space-between;
      width:100%; border:0 !important; padding:.375rem 0; text-align:right; }
    .cart-table tbody td::before { content:attr(data-label); font-weight:600; color:#6b7280; margin-right:.75rem; text-align:left; }
    .cart-table td.no-label::before { content:none; }
    .cart-table .img-fluid { width:64px; height:64px; object-fit:cover; }
    .cart-table tfoot, .cart-table tfoot tr, .cart-table tfoot th { display:block; width:100%; border:0 !important; padding:0; }
    .cart-table tfoot tr { margin-top:.25rem; background:#f9fafb; border-radius:12px; padding:.5rem .75rem; }
    #cart-total { display:inline-block; }
  }
</style>

<!-- ====== ANTI-BLUR & ALWAYS-ON-TOP (final) ====== -->
<style>
  
  /* Library overlay/maske lain */
  .mm-wrapper__blocker, .window-mask, .messager-mask, .datagrid-mask, .easyui-mask{
    z-index:0 !important; pointer-events:none !important; opacity:0 !important;
    filter:none !important; -webkit-filter:none !important;
    backdrop-filter:none !important; -webkit-backdrop-filter:none !important;
    transform:none !important;
  }

  .skel-thumb{ width:100%; aspect-ratio:4/3; border-radius:12px; margin-bottom:12px; background:#eee; position:relative; overflow:hidden; }
  .skel-line{ height:12px; border-radius:999px; background:#eee; margin-bottom:8px; position:relative; overflow:hidden; }
  .skel-line.w80{ width:80%; } .skel-line.w60{ width:60%; } .skel-line.w40{ width:40%; }
  .skel-shimmer::after{ content:""; position:absolute; inset:0;
    background:linear-gradient(90deg, transparent, rgba(255,255,255,.55), transparent);
    transform:translateX(-100%); animation:skel 1.2s infinite; }
  @keyframes skel { 100% { transform:translateX(100%); } }
  @media (prefers-reduced-motion: reduce){ .skel-shimmer::after{ display:none; } }
</style>



<!-- ===== Logic Keranjang + Modal ===== -->
<script>
(function(){
  const fmt = n => 'Rp ' + (n||0).toString().replace(/\B(?=(\d{3})+(?!\d))/g,'.');
  // function syncTotals(total){ $('#cart-total').text(fmt(total||0)); $('#cart-total-desktop').text(fmt(total||0)); }
  function syncTotals(total){
  const v = fmt(total || 0);
  // update total di desktop (tfoot)
  $('.d-none.d-md-block #cart-total').text(v);
  // update total di mobile (ringkas bawah)
  $('.d-md-none #cart-total').text(v);
  // label total di kanan bawah (desktop)
  $('#cart-total-desktop').text(v);
}
  function updateRowTotal($scope){
    const $tr = $scope.closest('[data-id]'); const harga = parseInt($tr.find('.harga').data('harga')||0);
    const qty = Math.max(0, parseInt($tr.find('.qty-input').val()||0));
    $tr.find('.subtotal').text(fmt(harga*qty));
  }
  const postUpdate = (produk_id, qty)=> $.post("<?= site_url('produk/cart_update'); ?>", {produk_id, qty}, null, 'json');
  const postRemove = (produk_id)=> $.post("<?= site_url('produk/cart_remove'); ?>", {produk_id}, null, 'json');

  // +1
  $(document).on('click', '.btn-inc', function(){
    const $tr = $(this).closest('[data-id]'); const id = parseInt($tr.data('id')); if (!id) return;
    let q = parseInt($tr.find('.qty-input').val()||0) + 1;
    $tr.find('.qty-input').val(q); updateRowTotal($(this));
    const $btn = $(this).prop('disabled', true);
    postUpdate(id, q).done(r=>{
      if (r && r.success){ syncTotals(r.total||0); $('#cart-count').text(r.count||0); }
      else { Swal.fire('Gagal', r?.pesan||'Tidak bisa update', 'error'); }
    }).fail(()=> Swal.fire('Error','Koneksi bermasalah','error')).always(()=> $btn.prop('disabled', false));
  });

  // -1
  $(document).on('click', '.btn-dec', function(){
    const $tr = $(this).closest('[data-id]'); const id = parseInt($tr.data('id')); if (!id) return;
    let q = Math.max(0, parseInt($tr.find('.qty-input').val()||0) - 1);
    $tr.find('.qty-input').val(q); updateRowTotal($(this));
    const $btn = $(this).prop('disabled', true);
    postUpdate(id, q).done(r=>{
      if (r && r.success){
        if (q <= 0) $tr.remove();
        syncTotals(r.total||0); $('#cart-count').text(r.count||0);
        if ((r.count||0) === 0) window.location.reload();
      } else { Swal.fire('Gagal', r?.pesan||'Tidak bisa update', 'error'); }
    }).fail(()=> Swal.fire('Error','Koneksi bermasalah','error')).always(()=> $btn.prop('disabled', false));
  });

  // input manual
  $(document).on('change', '.qty-input', function(){
    const $tr = $(this).closest('[data-id]'); const id = parseInt($tr.data('id')); if (!id) return;
    let q = Math.max(0, parseInt($(this).val()||0));
    $(this).val(q); updateRowTotal($(this));
    const $inp = $(this).prop('disabled', true);
    postUpdate(id, q).done(r=>{
      if (r && r.success){
        if (q <= 0) $tr.remove();
        syncTotals(r.total||0); $('#cart-count').text(r.count||0);
        if ((r.count||0) === 0) window.location.reload();
      } else { Swal.fire('Gagal', r?.pesan||'Tidak bisa update', 'error'); }
    }).fail(()=> Swal.fire('Error','Koneksi bermasalah','error')).always(()=> $inp.prop('disabled', false));
  });

  // hapus item
  $(document).on('click', '.btn-remove', function(){
    const $tr = $(this).closest('[data-id]'); const id = parseInt($tr.data('id')); if (!id) return;
    Swal.fire({title:'Hapus item?', icon:'warning', showCancelButton:true, confirmButtonText:'Ya, hapus', cancelButtonText:'Batal'})
      .then(res=>{
        if (!res.isConfirmed) return;
        const $btn = $(this).prop('disabled', true);
        postRemove(id).done(r=>{
          if (r && r.success){
            $tr.remove(); syncTotals(r.total||0); $('#cart-count').text(r.count||0);
            if ((r.count||0) === 0) window.location.reload();
          } else { Swal.fire('Gagal', r?.pesan||'Tidak bisa menghapus', 'error'); }
        }).fail(()=> Swal.fire('Error','Koneksi bermasalah','error')).always(()=> $btn.prop('disabled', false));
      });
  });

  // Open modal + shimmer + load isi
  $(document).on('click', '.link-detail, .btn-detail', function(e){
    e.preventDefault();
    var slug = $(this).data('slug');
    $('#modalProdukTitle').text('Detail Produk');
    $('#modalProdukBody').html(`
      <div class="px-2">
        <div class="skel-thumb skel-shimmer"></div>
        <div class="skel-line w80 skel-shimmer"></div>
        <div class="skel-line w60 skel-shimmer"></div>
        <div class="skel-line w80 skel-shimmer"></div>
        <div class="skel-line w40 skel-shimmer"></div>
      </div>
    `);
    $('#modalProduk').modal('show');

    $.getJSON("<?= site_url('produk/detail_modal'); ?>", { slug: slug })
      .done(function(r){
        if (!r || !r.success){
          $('#modalProdukBody').html('<div class="text-danger p-3">Gagal memuat detail.</div>');
          return;
        }
        if (r.title) $('#modalProdukTitle').text(r.title);
        $('#modalProdukBody').html(r.html);
      })
      .fail(function(){
        $('#modalProdukBody').html('<div class="text-danger p-3">Koneksi bermasalah.</div>');
      });
  });

  // Add to cart dari dalam modal
  $(document).on('click', '#btn-add-cart-modal', function(e){
    e.preventDefault();
    var $btn = $(this), id = $btn.data('id');
    var qtyEl = document.getElementById('qty-modal');
    var qty = parseInt(qtyEl && qtyEl.value ? qtyEl.value : 1, 10); if (!Number.isFinite(qty) || qty < 1) qty = 1;
    $btn.prop('disabled', true);
    $.ajax({
      url: "<?= site_url('produk/add_to_cart'); ?>",
      type: "POST", dataType: "json", data: { id, qty },
    }).done(function(r){
      if (!r || !r.success){
        if (window.Swal) Swal.fire({icon:'error',title:r?.title||'Oops!',text:r?.pesan||'Gagal menambahkan'});
        else alert((r?.title||'Oops!')+': '+(r?.pesan||'Gagal menambahkan'));
        return;
      }
      var nTop = document.getElementById('cart-count'); if (nTop) nTop.textContent = r.count||0;
      $('#modalProduk').one('hidden.bs.modal', function(){
        if (window.Swal) Swal.fire({icon:'success',title:r.title||'Mantap!',text:r.pesan||'Item masuk keranjang',timer:1500,showConfirmButton:false});
        else alert((r.title||'Mantap!')+': '+(r.pesan||'Item masuk keranjang'));
      });
      $('#modalProduk').modal('hide');
    }).fail(function(){
      if (window.Swal) Swal.fire({icon:'error',title:'Error',text:'Gagal terhubung ke server'});
      else alert('Error: Gagal terhubung ke server');
    }).always(function(){ $btn.prop('disabled', false); });
  });

})();

</script>
<script>
(function(){
  // helper qty (kalau belum ada)
  window.safeQty = window.safeQty || function(val){
    const n = Number(val);
    return Number.isFinite(n) && n > 0 ? n : 1;
  };

  // Hitung total hanya dari baris yang terlihat (desktop ATAU mobile)
  function sumFromDomVisible(){
    var sum = 0;
    $('[data-id]:visible').each(function(){
      var $row  = $(this);
      var harga = parseInt($row.find('.harga').data('harga') || 0, 10);
      var q     = parseInt($row.find('.qty-input').val() || 0, 10);
      if (!Number.isFinite(q)) q = 0;
      sum += harga * q;
    });
    return sum;
  }

  // Pastikan tidak double-bind
  $(document).off('click', '#btn-add-cart-modal').on('click', '#btn-add-cart-modal', function(e){
    e.preventDefault();
    var $btn  = $(this);
    var id    = $btn.data('id');
    var qtyEl = document.getElementById('qty-modal');
    var qty   = safeQty(qtyEl && qtyEl.value ? qtyEl.value : 1);

    $btn.prop('disabled', true);

    $.ajax({
      url: "<?= site_url('produk/add_to_cart'); ?>",
      type: "POST",
      dataType: "json",
      data: { id, qty },
    }).done(function(r){
      if (!r || !r.success){
        if (window.Swal) Swal.fire({icon:'error',title:r?.title||'Oops!',text:r?.pesan||'Gagal menambahkan'});
        else alert((r?.title||'Oops!')+': '+(r?.pesan||'Gagal menambahkan'));
        return;
      }

      // badge
      var nTop = document.getElementById('cart-count'); if (nTop) nTop.textContent = r.count || 0;
      var fab  = document.getElementById('fab-count');  if (fab)  fab.textContent  = r.count || 0;

      // sinkron baris + total di halaman cart
      (function syncCartAfterAdd(){
        var addQ  = qty;
        var $rows = $('#cart-tbody [data-id="'+id+'"], #cart-tbody-mobile [data-id="'+id+'"]');

        if ($rows.length){
          $rows.each(function(){
            var $row = $(this);
            var $inp = $row.find('.qty-input');
            var cur  = parseInt($inp.val() || 0, 10); if (!Number.isFinite(cur)) cur = 0;
            var next = cur + addQ;
            $inp.val(next);
            if (typeof updateRowTotal === 'function') updateRowTotal($inp);
          });

          // Pakai total dari server bila ada (coerce string->number), kalau tidak hitung dari DOM yang visible
          var newTotal = (r.total != null && r.total !== '')
            ? parseInt(r.total, 10)
            : sumFromDomVisible();

          if (typeof syncTotals === 'function') {
            syncTotals(newTotal);
          } else {
            var v = 'Rp ' + (newTotal||0).toString().replace(/\B(?=(\d{3})+(?!\d))/g,'.');
            // fallback update
            $('.d-none.d-md-block #cart-total').text(v);
            $('.d-md-none #cart-total').text(v);
            $('#cart-total-desktop').text(v);
          }
        } else {
          // item baru yang belum ada barisnya → reload supaya muncul
          window.location.reload();
          return;
        }
      })();

      $('#modalProduk').one('hidden.bs.modal', function(){
        if (window.Swal) Swal.fire({icon:'success',title:r.produk||'Mantap!',text:r.pesan||'Item masuk keranjang',timer:1500,showConfirmButton:false});
        else alert((r.title||'Mantap!')+': '+(r.pesan||'Item masuk keranjang'));
      });
      $('#modalProduk').modal('hide');

    }).fail(function(){
      if (window.Swal) Swal.fire({icon:'error',title:'Error',text:'Gagal terhubung ke server'});
      else alert('Error: Gagal terhubung ke server');
    }).always(function(){
      $btn.prop('disabled', false);
    });
  });
})();
</script>

<?php $this->load->view("modal_produk"); ?>

