<link href="<?= base_url('assets/admin/datatables/css/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet" type="text/css"/>

<div class="container-fluid">
  <div class="row"><div class="col-12">
    <div class="card"><div class="card-body">
      <h4 class="header-title"><?= $title; ?></h4>

      <div class="button-list mb-2">
        <button type="button" onclick="add()" class="btn btn-success btn-rounded btn-sm waves-effect waves-light">
          <span class="btn-label"><i class="fe-plus-circle"></i></span>Tambah
        </button>
        <button type="button" onclick="reload_table()" class="btn btn-info btn-rounded btn-sm waves-effect waves-light">
          <span class="btn-label"><i class="fe-refresh-ccw"></i></span>Refresh
        </button>
        <button type="button" onclick="hapus_data()" class="btn btn-danger btn-rounded btn-sm waves-effect waves-light">
          <span class="btn-label"><i class="fa fa-trash"></i></span>Hapus
        </button>
      </div>

      <table id="datable_1" class="table table-striped table-bordered w-100">
        <thead>
          <tr>
            <th class="text-center" width="5%">
              <div class="checkbox checkbox-primary checkbox-single">
                <input id="check-all" type="checkbox"><label></label>
              </div>
            </th>
            <th width="5%">No.</th>
            <th width="14%">Kode</th>
            <th>Nama</th>
            <th width="13%">No. HP</th>
            <th width="10%">Benefit</th>
            <th width="14%">Dibuat</th>
            <th width="14%">Dipakai</th>
            <th width="9%">Status</th>
            <th width="12%">Aksi</th>
          </tr>
        </thead>
      </table>

    </div></div>
  </div></div>

  <!-- Modal -->
  <div id="full-width-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="mymodal-title">Tambah Voucher Billiard</h4>
          <button type="button" class="close" onclick="close_modal()" aria-hidden="true">×</button>
        </div>
        <div class="modal-body">
          <form id="form_app" method="post">
            <input type="hidden" name="id_voucher" id="id_voucher">

            <div class="form-row">
              <div class="form-group mb-2 col-md-6">
                <label class="text-primary">Nama</label>
                <input type="text" class="form-control" name="nama" id="nama" autocomplete="off" required>
              </div>
              <div class="form-group mb-2 col-md-6">
                <label class="text-primary">No. HP</label>
                <input type="tel" class="form-control" name="no_hp" id="no_hp" autocomplete="off" required>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group mb-2 col-md-4">
                <label class="text-primary">Jenis Voucher</label>
                <select name="jenis" id="jenis" class="form-control" required>
                  <option value="FREE_MAIN">FREE_MAIN (Gratis Jam)</option>
                  <option value="NOMINAL">NOMINAL (Diskon Rp)</option>
                  <option value="PERSEN">PERSEN (Diskon %)</option>
                </select>
              </div>

              <div class="form-group mb-2 col-md-4 jenis-free">
                <label class="text-primary">Jam Voucher</label>
                <input type="number" min="1" class="form-control" name="jam_voucher" id="jam_voucher" value="1">
              </div>

              <div class="form-group mb-2 col-md-4 jenis-diskon">
                <label class="text-primary">Nilai (Rp / %)</label>
                <input type="number" min="0" class="form-control" name="nilai" id="nilai" value="0">
                <small class="text-muted">Jika PERSEN: 1-100</small>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group mb-2 col-md-4 jenis-persen">
                <label class="text-primary">Max Potongan (Rp)</label>
                <input type="number" min="0" class="form-control" name="max_potongan" id="max_potongan" value="0">
                <small class="text-muted">Opsional (hanya PERSEN)</small>
              </div>

              <div class="form-group mb-2 col-md-4">
                <label class="text-primary">Minimal Subtotal (Rp)</label>
                <input type="number" min="0" class="form-control" name="minimal_subtotal" id="minimal_subtotal" value="0">
                <small class="text-muted">Opsional</small>
              </div>

              <div class="form-group mb-2 col-md-4">
                <label class="text-primary">Periode</label>
                <div class="d-flex" style="gap:8px">
                  <input type="date" class="form-control" name="tgl_mulai" id="tgl_mulai">
                  <input type="date" class="form-control" name="tgl_selesai" id="tgl_selesai">
                </div>
                <small class="text-muted">Boleh kosong kalau tidak dibatasi</small>
              </div>
            </div>

            <div class="form-group mb-0">
              <label class="text-primary">Catatan</label>
              <textarea name="notes" id="notes" rows="3" class="form-control"></textarea>
            </div>

          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary waves-effect" onclick="close_modal()">Batal</button>
          <button type="button" id="btnSimpan" onclick="simpan()" class="btn btn-primary waves-effect waves-light">Simpan</button>
        </div>
      </div>
    </div>
  </div>

  <?php
    $this->load->view("backend/global_css");
    $this->load->view($controller."_js");
  ?>
</div>
