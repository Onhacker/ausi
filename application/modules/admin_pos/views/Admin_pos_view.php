<link href="<?= base_url('assets/admin/datatables/css/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet" type="text/css"/>

<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="page-title-box">
        <div class="page-title-right">
          <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active"><?= $subtitle; ?></li>
          </ol>
        </div>
        <h4 class="page-title"><?= $subtitle; ?></h4>
      </div>
    </div>
  </div>

  <div class="row"><div class="col-12">
    <div class="card"><div class="card-body">

     <?php
  $uname   = strtolower((string)$this->session->userdata('admin_username'));
  $isKB    = in_array($uname, ['kitchen','bar'], true); // kitchen / bar
?>
<style type="text/css">
  .table td .badge.border {
  border-color: rgba(0,0,0,.12) !important;
  background: #f9fafb !important;
  color: #111827 !important;
  font-weight: 600;
}

</style>


<div class="button-list mb-2 d-flex align-items-center flex-wrap">
  <!-- Kiri: tombol umum -->
  <button type="button" onclick="reload_table('user')" class="btn btn-warning btn-rounded btn-sm waves-effect waves-light mr-2">
    <span class="btn-label"><i class="fe-refresh-ccw"></i></span>Refresh
  </button>
<button type="button" id="btn-enable-sound" class="btn btn-outline-secondary btn-rounded btn-sm">
    <span class="btn-label"><i class="fe-volume-2"></i></span>Aktifkan Suara
  </button>
  <!-- Kanan: filter status -->
  <?php if (!$isKB): ?>
    <select id="filter-status" class="form-control form-control-sm ml-auto" style="width:220px">
      <option value="all" selected>Semua status</option>
      <option value="paid">Paid (Lunas)</option>
      <option value="pending">Pending</option>
      <option value="verifikasi">Verifikasi</option>
      <option value="canceled">Canceled</option>
      <option value="failed">Failed</option>
    </select>
  <?php endif; ?>
</div>


</table> 
<table id="datable_pos" class="table table-striped table-bordered w-100">
  <thead>
    <tr>
      <th width="6%">No.</th>
      <th>Mode</th>
      <th>Meja / Nama</th>

      <?php if ($isKB): ?>
        <th>Pesanan</th>
      <?php endif; ?>

      <th>Waktu</th>
      <th>Durasi</th>
      <th class="th-price">Jumlah</th>
      <th>Status</th>
      <th class="th-method">Metode Pembayaran</th>

      <?php if (!$isKB): ?>
        <th width="14%">Aksi</th>
      <?php endif; ?>
    </tr>
  </thead>
</table>


    </div></div>
  </div></div>

  <!-- Modal Detail -->
  <div id="pos-detail-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="mymodal-title">Detail Order</h4>
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        </div>
        <div class="modal-body" id="detail-body">
          <div class="text-center text-muted py-5">Memuat…</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Tambah Order (POS) -->
  <div id="pos-create-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="mymodal-title">Buat Order (Kasir)</h4>
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        </div>
        <div class="modal-body">
          <form id="form-pos" onsubmit="return false">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group mb-2">
                  <label>Mode</label>
                  <select class="form-control" name="mode" id="pos-mode">
                    <option value="walkin">Walk-in</option>
                    <option value="dinein">Dine-in</option>
                    <option value="delivery">Delivery</option>
                  </select>
                </div>
                <div class="form-group mb-2 dinein-only" style="display:none;">
                  <label>Meja (kode/nama)</label>
                  <input type="text" class="form-control" name="meja_kode" id="pos-meja-kode" placeholder="K-01">
                  <small class="text-muted">Opsional: isi kode atau nama meja</small>
                  <input type="text" class="form-control mt-1" name="meja_nama" id="pos-meja-nama" placeholder="Meja Tamu 1">
                </div>
                <div class="form-group mb-2">
                  <label>Nama Pelanggan</label>
                  <input type="text" class="form-control" name="nama" id="pos-nama" placeholder="Customer">
                </div>
                <div class="form-group mb-2">
                  <label>Catatan</label>
                  <textarea class="form-control" name="catatan" id="pos-catatan" rows="2" maxlength="120" placeholder="Tanpa gula, pedas, dll."></textarea>
                </div>
                <div class="form-group mb-2">
                  <label>Metode Pembayaran</label>
                  <div class="btn-group d-flex" role="group">
                    <button type="button" class="btn btn-outline-secondary w-100 pay-btn" data-pay="cash">Cash</button>
                    <button type="button" class="btn btn-outline-secondary w-100 pay-btn" data-pay="qris">QRIS</button>
                    <button type="button" class="btn btn-outline-secondary w-100 pay-btn" data-pay="transfer">Transfer</button>
                  </div>
                  <input type="hidden" name="pay_method" id="pos-pay" value="cash">
                </div>
              </div>

              <div class="col-md-8">
                <div class="form-group mb-2">
                  <label>Cari Produk</label>
                  <input type="search" class="form-control" id="pos-search" placeholder="Ketik nama / SKU…">
                </div>
                <div id="pos-search-result" class="border rounded p-2" style="max-height:210px; overflow:auto;">
                  <div class="text-muted small">Mulai ketik untuk mencari…</div>
                </div>

                <div class="table-responsive mt-2">
                  <table class="table table-sm table-bordered mb-0" id="pos-items">
                    <thead>
                      <tr>
                        <th>Produk</th>
                        <th style="width:120px">Harga</th>
                        <th style="width:120px">Qty</th>
                        <th style="width:140px">Subtotal</th>
                        <th style="width:60px">#</th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                      <tr>
                        <th colspan="3" class="text-right">Total</th>
                        <th id="pos-total" class="text-right">Rp 0</th>
                        <th></th>
                      </tr>
                    </tfoot>
                  </table>
                </div>

              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button class="btn btn-primary" id="pos-submit">Buat Order</button>
        </div>
      </div>
    </div>
  </div>
<script>
  const IS_KB = <?= $isKB ? 'true' : 'false' ?>;
</script>
  <?php
    $this->load->view("backend/global_css");
    $this->load->view($controller."_js");
  ?>
</div>
