<link href="<?= base_url('assets/admin/datatables/css/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet" type="text/css"/>

<div class="container-fluid">
  <div class="row">
   <!--  <div class="col-12">
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
          <th width="12%">Kode</th>
          <th>Nama</th>
          <th width="13%">No. HP</th>
          <th width="9%">Tipe</th>
          <th width="10%">Nilai</th>
          <th width="18%">Periode</th>
          <th width="10%">Klaim</th>
          <th width="9%">Status</th>
          <th width="11%">Aksi</th>
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
          <h4 class="mymodal-title">Tambah Voucher Cafe</h4>
          <button type="button" class="close" onclick="close_modal()" aria-hidden="true">×</button>
        </div>
        <div class="modal-body">
          <form id="form_app" method="post">
            <input type="hidden" name="id" id="id">

            <div class="form-group mb-2">
              <label class="text-primary">Nama Pelanggan</label>
              <input type="text" class="form-control" name="nama" id="nama" autocomplete="off" required>
            </div>

            <div class="form-group mb-2">
              <label class="text-primary">No. HP</label>
              <input type="tel" class="form-control" name="no_hp" id="no_hp" autocomplete="off" required>
            </div>

            <div class="form-row">
              <div class="form-group mb-2 col-md-4">
                <label class="text-primary">Tipe Voucher</label>
                <select name="tipe" id="tipe" class="form-control" required>
                  <option value="nominal">Nominal (Rp)</option>
                  <option value="persen">Persen (%)</option>
                </select>
              </div>
              <div class="form-group mb-2 col-md-4">
                <label class="text-primary">Nilai</label>
                <input type="number" min="1" class="form-control" name="nilai" id="nilai" required>
                <small class="text-muted">
                  Jika persen: 1-100. Jika nominal: isi dalam rupiah.
                </small>
              </div>
              <div class="form-group mb-2 col-md-4">
                <label class="text-primary">Minimal Belanja (Rp)</label>
                <input type="number" min="0" class="form-control" name="minimal_belanja" id="minimal_belanja">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group mb-2 col-md-4">
                <label class="text-primary">Max Potongan (Rp)</label>
                <input type="number" min="0" class="form-control" name="max_potongan" id="max_potongan">
                <small class="text-muted">
                  Opsional, biasanya hanya untuk tipe persen.
                </small>
              </div>
              <div class="form-group mb-2 col-md-4">
                <label class="text-primary">Tanggal Mulai</label>
                <input type="date" class="form-control" name="tgl_mulai" id="tgl_mulai" required>
              </div>
              <div class="form-group mb-2 col-md-4">
                <label class="text-primary">Tanggal Selesai</label>
                <input type="date" class="form-control" name="tgl_selesai" id="tgl_selesai" required>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group mb-2 col-md-4">
                <label class="text-primary">Kuota Klaim</label>
                <input type="number" min="1" class="form-control" name="kuota_klaim" id="kuota_klaim" required>
                <small class="text-muted">
                  Berapa kali voucher boleh dipakai.
                </small>
              </div>
              <div class="form-group mb-2 col-md-4">
                <label class="text-primary">Status</label>
                <select name="status" id="status_voucher" class="form-control">
                  <option value="1">Aktif</option>
                  <option value="0">Nonaktif</option>
                </select>
              </div>
            </div>

            <div class="form-group mb-0">
              <label class="text-primary">Keterangan</label>
              <textarea name="keterangan" id="keterangan" rows="3" class="form-control"></textarea>
              <small class="text-muted">
                Misal: khusus menu tertentu, hanya berlaku di AUSI Cafe, dll.
              </small>
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
