<?php $this->load->view("front_end/head.php"); ?>
<div class="container-fluid">

  <!-- page title -->
  <div class="row">
    <div class="col-12">
      <div class="page-title-box">
        <div class="page-title-right">
          <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="<?= base_url(); ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= site_url('produk'); ?>">Products</a></li>
            <li class="breadcrumb-item active">Product Detail</li>
          </ol>
        </div>
        <h4 class="page-title">Product Detail</h4>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card-box">
        <div class="row">
          <div class="col-xl-5">
            <?php $img = $product->gambar ? ( (strpos($product->gambar,'http')===0) ? $product->gambar : base_url($product->gambar) ) : base_url('assets/images/products/no-image.png'); ?>
            <div class="tab-content pt-0">
              <div class="tab-pane active show" id="product-1-item">
                <img src="<?= $img; ?>" alt="<?= html_escape($product->nama); ?>" class="img-fluid mx-auto d-block rounded">
              </div>
            </div>
            <ul class="nav nav-pills nav-justified">
              <li class="nav-item">
                <a href="#product-1-item" data-toggle="tab" aria-expanded="false" class="nav-link product-thumb active show">
                  <img src="<?= $img; ?>" alt="<?= html_escape($product->nama); ?>" class="img-fluid mx-auto d-block rounded">
                </a>
              </li>
            </ul>
          </div>

          <div class="col-xl-7">
            <div class="pl-xl-3 mt-3 mt-xl-0">
              <a href="<?= site_url('produk').'?kategori='.$product->kategori_id; ?>" class="text-primary">
                <?= html_escape($product->kategori_nama ?: ''); ?>
              </a>
              <h4 class="mb-3"><?= html_escape($product->nama); ?></h4>

              <h4 class="mb-4">Harga : <b>Rp <?= number_format((float)$product->harga,0,',','.'); ?></b></h4>

              <h4>
                <?php if ((int)$product->stok > 0): ?>
                  <span class="badge bg-soft-success text-success mb-4">Instock</span>
                <?php else: ?>
                  <span class="badge bg-soft-danger text-danger mb-4">Sold Out</span>
                <?php endif; ?>
              </h4>

              <div class="text-muted mb-3">
                <span class="mr-3">SKU: <code><?= html_escape($product->sku); ?></code></span>
                <span>Stok: <?= (int)$product->stok; ?> <?= html_escape($product->satuan ?: 'pcs'); ?></span>
              </div>

              <div class="mb-4">
                <?= $product->deskripsi ?: '<p class="text-muted">Belum ada deskripsi.</p>'; ?>
              </div>

              <div class="form-inline mb-3">
                <label class="my-1 mr-2" for="qty">Qty</label>
                <select class="custom-select my-1 mr-sm-3" id="qty">
                  <?php for($i=1;$i<=10;$i++): ?>
                    <option value="<?= $i; ?>"><?= $i; ?></option>
                  <?php endfor; ?>
                </select>
              </div>

              <div>
                <a href="<?= site_url('produk'); ?>" class="btn btn-light mr-2">Kembali</a>
                <button type="button" class="btn btn-success" id="btn-add-cart-detail">
                  <span class="btn-label"><i class="mdi mdi-cart"></i></span>Keranjang
                </button>
              </div>
            </div>
          </div>
        </div>

      </div> <!-- end card-->
    </div>
  </div>
</div>
<script src="<?php echo base_url('assets/admin') ?>/js/vendor.min.js"></script>
<script src="<?php echo base_url('assets/admin') ?>/js/app.min.js"></script>
<script src="<?php echo base_url('assets/admin') ?>/js/sw.min.js"></script>
<?php $this->load->view("front_end/footer.php") ?>
<script>
$(function(){
  // update cart badge
  function refreshCartCount(){
    $.getJSON("<?= site_url('produk/cart/count'); ?>").done(function(r){
      if (r && r.success){
        // kalau halaman ini dipakai sendiri, tambahkan badge kalau ada elemen global
        const $badge = $('#cart-count'); if ($badge.length) $badge.text(r.count);
      }
    });
  }
  $('#btn-add-cart-detail').on('click', function(){
    const qty = parseInt($('#qty').val() || 1);
    $.post("<?= site_url('produk/cart/add'); ?>", { id: <?= (int)$product->id; ?>, qty: qty }, function(r){
     if (!r.success){ notifyError(r.pesan || 'Gagal menambahkan'); return; }
      notifySuccess('Ditambahkan ke keranjang');

      refreshCartCount();
    }, 'json').fail(function(){ alert('Gagal terhubung ke server'); });
  });
  refreshCartCount();
});
</script>
<script>
  // Helper notifikasi
  function notifySuccess(msg){
    if (window.Swal) {
      Swal.fire({ icon:'success', title:'Berhasil', text: msg, timer:1400, showConfirmButton:false });
    } else { alert(msg); }
  }
  function notifyError(msg){
    if (window.Swal) {
      Swal.fire({ icon:'error', title:'Gagal', text: msg });
    } else { alert(msg); }
  }
</script>

