<link href="<?= base_url('assets/admin/datatables/css/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet" type="text/css"/>

<div class="container-fluid">
  <div class="row">
  <!--   <div class="col-12">
      <div class="page-title-box">
        <div class="page-title-right">
          <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active"><?= $subtitle; ?></li>
          </ol>
        </div>
        <h4 class="page-title"><?= $subtitle; ?></h4>
      </div>
    </div> -->
  </div>

  <!-- Bar tombol -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <h4 class="header-title"><?= $subtitle; ?></h4>

          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div class="button-list mb-1">
              <button type="button" onclick="add()" class="btn btn-success btn-rounded btn-sm waves-effect waves-light">
                <span class="btn-label"><i class="fe-plus-circle"></i></span>Tambah
              </button>
              <button type="button" onclick="refresh()" class="btn btn-info btn-rounded btn-sm waves-effect waves-light">
                <span class="btn-label"><i class="fe-refresh-ccw"></i></span>Refresh
              </button>
              <?php if ($this->session->userdata("admin_username") == "admin") {?>
              <button type="button" onclick="hapus_data()" class="btn btn-danger btn-rounded btn-sm waves-effect waves-light">
                <span class="btn-label"><i class="fa fa-trash"></i></span>Hapus
              </button>
            <?php } ?>
            </div>

            <div class="d-flex align-items-center gap-2 ms-auto">
              <button type="button" id="btnOpenSetting" class="btn btn-primary btn-rounded btn-sm waves-effect waves-light">
                <i class="fe-settings me-1"></i> Pengaturan Tarif
              </button>
            </div>
          </div>
          <small id="price_hint" class="text-dark"></small>
          <hr>
            <div class="table-responsive">
          <!-- <table id="datable_1" class="table table-striped table-bordered w-100"> -->
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
          <h4 class="mymodal-title">Tambah</h4>
          <button type="button" class="close" onclick="close_modal()" aria-hidden="true">×</button>
        </div>
        <div class="modal-body">
          <form id="form_app" method="post">
            <input type="hidden" name="id_transaksi" id="id_transaksi">

            <div class="form-group mb-3">
              <label class="text-primary">Nama</label>
              <input type="text" class="form-control" name="nama" id="nama" autocomplete="off" required>
            </div>

            <!-- Sesi -> dropdown; value = menit (sesi * durasi_unit) -->
            <div class="form-group mb-3">
              <label class="text-primary">Sesi</label>
              <select class="form-control" name="durasi_menit" id="durasi_menit" required></select>
              <small class="text-muted d-block">Nilai opsi = <em>sesi × durasi per unit</em> (menit).</small>
              <div id="estimator" class="mt-2 small text-info"></div>
            </div>

            <!-- Tambahan: Status -->
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
          <button type="button" onclick="simpan()" class="btn btn-primary waves-effect waves-light">Simpan</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Pengaturan Tarif -->
  <div id="setting-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="m-0">Pengaturan Tarif Kursi Pijat</h4>
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        </div>
        <div class="modal-body">
          <form id="form_setting" class="row g-2">
            <div class="col-12 mb-3">
              <label class="text-primary">Harga Satuan (Rp / unit)</label>
              <input type="text" class="form-control" id="harga_satuan_rp" autocomplete="off" inputmode="numeric">
              <input type="hidden" name="harga_satuan" id="harga_satuan">
              <small class="text-muted">Contoh: Rp 20.000</small>
            </div>
            <div class="col-12 mb-1">
              <label class="text-primary">Durasi per Unit (menit)</label>
              <input type="number" min="1" step="1" class="form-control" name="durasi_unit" id="durasi_unit" required>
              <small class="text-muted">Contoh: 15</small>
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
