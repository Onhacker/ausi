<link href="<?= base_url('assets/admin/datatables/css/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet" type="text/css"/>

<div class="container-fluid">
  <div class="row"></div>

  <!-- Bar tombol -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <h4 class="header-title"><?= $subtitle; ?></h4>
          <?php
            $uname_ps = strtolower((string)$this->session->userdata('admin_username'));
          ?>
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div class="button-list mb-1">
              <button type="button" onclick="add()" class="btn btn-success btn-rounded btn-sm waves-effect waves-light">
                <span class="btn-label"><i class="fe-plus-circle"></i></span>Tambah
              </button>
              <button type="button" onclick="refresh()" class="btn btn-info btn-rounded btn-sm waves-effect waves-light">
                <span class="btn-label"><i class="fe-refresh-ccw"></i></span>Refresh
              </button>
              <?php if ($uname_ps === 'admin'): ?>
              <button type="button" onclick="hapus_data()" class="btn btn-danger btn-rounded btn-sm waves-effect waves-light">
                <span class="btn-label"><i class="fa fa-trash"></i></span>Hapus
              </button>
              <?php endif; ?>
            </div>

            <?php if ($uname_ps === 'admin'): ?>
              <div class="d-flex align-items-center gap-2 ms-auto">
                <button type="button" id="btnOpenSetting" class="btn btn-primary btn-rounded btn-sm waves-effect waves-light">
                  <i class="fe-settings me-1"></i> Pengaturan Tarif PS & Voucher
                </button>
              </div>
            <?php endif; ?>

          </div>
          <small id="price_hint" class="text-dark"></small>
          <hr>
          <div class="table-responsive">
            <table id="datable_1" class="table table-sm table-striped table-bordered w-100">
              <thead>
                <tr>
                  <th class="text-center" width="5%">
                    <div class="checkbox checkbox-primary checkbox-single">
                      <input id="check-all" type="checkbox"><label></label>
                    </div>
                  </th>
                  <th width="5%">No.</th>
                  <th>Nama</th>
                  <th>No. HP</th>
                  <th>Tanggal</th>
                  <th>Durasi</th>
                  <th>Sesi</th>
                  <th>Total</th>
                  <th>Status</th>
                  <th width="12%">Aksi</th>
                </tr>
              </thead>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- Modal Form Transaksi -->
  <div id="full-width-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="mymodal-title">Tambah Transaksi PS</h4>
          <button type="button" class="close" onclick="close_modal()" aria-hidden="true">×</button>
        </div>
        <div class="modal-body">
          <form id="form_app" method="post">
            <input type="hidden" name="id_transaksi" id="id_transaksi">

            <div class="form-group mb-3">
              <label class="text-primary">Nama</label>
              <input type="text" class="form-control" name="nama" id="nama" autocomplete="off" required>
            </div>

            <div class="form-group mb-3">
              <label class="text-primary">No. HP (untuk voucher)</label>
              <input type="text" class="form-control" name="no_hp" id="no_hp" autocomplete="off">
              <small class="text-muted">Opsional, tapi wajib diisi jika ingin mengumpulkan voucher.</small>
            </div>

            <!-- Sesi -> dropdown; value = menit (sesi * durasi_unit) -->
            <div class="form-group mb-3">
              <label class="text-primary">Sesi</label>
              <select class="form-control" name="durasi_menit" id="durasi_menit" required></select>
              <small class="text-muted d-block">Nilai opsi = <em>sesi × durasi per unit</em> (menit).</small>
              <div id="estimator" class="mt-2 small text-info"></div>
            </div>

            <!-- Status (radio) -->
            <div class="form-group mb-3">
              <label class="text-primary d-block">Status</label>

              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="status" id="st_baru" value="baru" checked>
                <label class="form-check-label" for="st_baru">Belum Bayar</label>
              </div>

              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="status" id="st_selesai" value="selesai">
                <label class="form-check-label" for="st_selesai">Lunas</label>
              </div>

              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="status" id="st_batal" value="batal">
                <label class="form-check-label" for="st_batal">Batal</label>
              </div>
            </div>

            <div class="form-group mb-1">
              <label class="text-primary">Catatan (opsional)</label>
              <textarea class="form-control" name="catatan" id="catatan" rows="2"></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <div class="text-start me-auto"></div>
          <button type="button" class="btn btn-secondary waves-effect" onclick="close_modal()">Batal</button>
          <!-- DITAMBAH id="btnSimpan" -->
          <button type="button" id="btnSimpan" onclick="simpan()" class="btn btn-primary waves-effect waves-light">Simpan</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Pengaturan Tarif & Voucher -->
  <div id="setting-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="m-0">Pengaturan Sewa PlayStation & Program Voucher</h4>
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        </div>
        <div class="modal-body">
          <form id="form_setting" class="row g-2">
            <div class="col-12 mb-3">
              <label class="text-primary">Harga Satuan (Rp / unit)</label>
              <input type="text" class="form-control" id="harga_satuan_rp" autocomplete="off" inputmode="numeric">
              <input type="hidden" name="harga_satuan" id="harga_satuan">
              <small class="text-muted">Contoh: Rp 15.000</small>
            </div>
            <div class="col-12 mb-3">
              <label class="text-primary">Durasi per Unit (menit)</label>
              <input type="number" min="1" step="1" class="form-control" name="durasi_unit" id="durasi_unit" required>
              <small class="text-muted">Contoh: 60</small>
            </div>
            <div class="col-12 mb-1">
              <label class="text-primary">Jumlah main untuk dapat FREE</label>
              <input type="number" min="0" step="1" class="form-control" name="free_main_threshold" id="free_main_threshold" required>
              <small class="text-muted">
                Contoh: 10 (artinya setiap 10x main PS, dapat 1x gratis). Isi <b>0</b> jika tidak menggunakan voucher.
              </small>

            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
          <button type="button" id="btnSaveSetting" class="btn btn-primary">Simpan Pengaturan</button>
        </div>
      </div>
    </div>
  </div>

  <?php
    $this->load->view("backend/global_css");
    $this->load->view($controller."_js");
  ?>
</div>
