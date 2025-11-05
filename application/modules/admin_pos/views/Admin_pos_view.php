<link href="<?= base_url('assets/admin/datatables/css/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet" type="text/css"/>

<div class="container-fluid">
  <!--
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
  -->

  <?php
    $uname = strtolower((string)$this->session->userdata('admin_username'));
    $isKB  = in_array($uname, ['kitchen','bar'], true); // kitchen / bar
  ?>

  <style>
    /* Badge tabel */
    .table td .badge.border{
      border-color: rgba(0,0,0,.12) !important;
      background:#f9fafb !important;
      color:#111827 !important;
      font-weight:600;
    }
    /* Kartu meja */
    .meja-card{ border:1px solid rgba(0,0,0,.08); border-radius:.75rem; padding:.75rem; margin:.25rem; width:100%; }
    .meja-kode{ font-weight:700; font-size:1rem; }
    .meja-meta{ color:#6b7280; font-size:.85rem; }
    .meja-actions .btn{ margin-right:.3rem; margin-top:.35rem; }
  </style>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">

          <!-- Toolbar -->
          <div class="row mb-2">
            <div class="col-12">
              <form class="form-inline">
                <!-- Refresh -->
                <button type="button" onclick="reload_table('user')" class="btn btn-blue btn-sm waves-effect waves-light mb-2 mr-2">
                  <span class="btn-label"><i class="fe-refresh-ccw"></i></span>Refresh
                </button>

                <?php if (!$isKB): ?>
                  <!-- Order -->
                  <button type="button" class="btn btn-success btn-sm waves-effect waves-light mb-2 mr-2" onclick="openMejaModal()">
                    <span class="btn-label"><i class="fe-grid"></i></span>Order
                  </button>

                  <!-- Bungkus -->
                  <a href="<?= site_url('produk/walkin') ?>" class="btn btn-primary btn-sm waves-effect waves-light mb-2 mr-2">
                    <span class="btn-label"><i class="fe-shopping-bag"></i></span>Bungkus
                  </a>

                  <!-- Filter status (custom-select bawaan template) -->
                  <div class="form-group mb-2 mr-2">
                    <label for="filter-status" class="sr-only">Status</label>
                    <select id="filter-status" class="custom-select custom-select-sm">
                      <option value="all" selected>Semua status</option>
                      <option value="paid">Lunas</option>
                      <option value="pending">Menunggu Pembayaran</option>
                      <option value="verifikasi">Verifikasi</option>
                      <option value="canceled">Canceled</option>
                      <!-- <option value="failed">Failed</option> -->
                    </select>
                  </div>
                <?php endif; ?>
              </form>
            </div>
          </div>
          <!-- /Toolbar -->

          <!-- Tabel -->
          <table id="datable_pos" class="table table-sm table-striped table-bordered w-100">
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
                <th class="th-method">Metode</th>

                <?php if (!$isKB): ?>
                  <th width="14%">Aksi</th>
                <?php endif; ?>
              </tr>
            </thead>
          </table>
          <!-- /Tabel -->

        </div>
      </div>
    </div>
  </div>

  <!-- ===== MODAL: LIST MEJA ===== -->
  <div id="meja-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-light">
          <h4 class="mymodal-title">Pilih Meja</h4>
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        </div>
        <div class="modal-body">
          <div class="form-group mb-2">
            <input type="search" id="meja-q" class="form-control" placeholder="Cari meja (kode/nama/area)…">
          </div>

          <div id="meja-list" class="row no-gutters"></div>
          <div id="meja-empty" class="text-muted small py-3" style="display:none">Tidak ada data.</div>
        </div>
        <div class="modal-footer">
          <small class="text-muted mr-auto" id="meja-count"></small>
          <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ===== MODAL: DETAIL ORDER ===== -->
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

  <!-- ===== MODAL: BUAT ORDER (POS) ===== -->
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
