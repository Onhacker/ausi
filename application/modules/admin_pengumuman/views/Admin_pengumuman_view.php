<link href="<?= base_url('assets/admin/datatables/css/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet" type="text/css"/>
<link href="<?= base_url('assets/admin/summernote/summernote-bs4.min.css'); ?>" rel="stylesheet" type="text/css"/>

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
          <th>Judul</th>
          <th width="12%">Tanggal</th>
          <th width="14%">Pembuat</th>
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
          <h4 class="mymodal-title">Tambah Pengumuman</h4>
          <button type="button" class="close" onclick="close_modal()" aria-hidden="true">×</button>
        </div>
        <div class="modal-body">
          <form id="form_app" method="post">
            <input type="hidden" name="id" id="id">
            <div class="form-group mb-2">
              <label class="text-primary">Judul</label>
              <input type="text" class="form-control" name="judul" id="judul" autocomplete="off" required>
            </div>
            <div class="form-group mb-2">
              <label class="text-primary">Tanggal</label>
              <input type="date" class="form-control" name="tanggal" id="tanggal" required>
            </div>
            <div class="form-group mb-0">
              <label class="text-primary">Isi</label>
              <textarea name="isi" id="isi" class="form-control"></textarea>
              <small class="text-muted">Konten dapat berformat (bold, list, link, dsb.).</small>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary waves-effect" onclick="close_modal()">Batal</button>
          <button type="button" onclick="simpan()" class="btn btn-primary waves-effect waves-light">Simpan</button>
        </div>
      </div>
    </div>
  </div>

  <?php
    $this->load->view("backend/global_css");
    $this->load->view($controller."_js");
  ?>
</div>
