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

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="button-list">
            <button type="button" onclick="add()" class="btn btn-success btn-rounded btn-sm waves-effect waves-light">
              <span class="btn-label"><i class="fe-plus-circle"></i></span>Tambah
            </button>
            <button type="button" onclick="refresh()" class="btn btn-info btn-rounded btn-sm waves-effect waves-light">
              <span class="btn-label"><i class="fe-refresh-ccw"></i></span>Refresh
            </button>
            <button type="button" onclick="hapus_data()" class="btn btn-danger btn-rounded btn-sm waves-effect waves-light">
              <span class="btn-label"><i class="fa fa-trash"></i></span>Hapus
            </button>
          </div>
          <hr>
          <table id="datable_1" class="table table-striped table-bordered w-100">
            <thead>
            <tr>
              <th class="text-center" width="5%">
                <div class="checkbox checkbox-primary checkbox-single">
                  <input id="check-all" type="checkbox"><label></label>
                </div>
              </th>
              <th width="5%">No.</th>
              <th>Tugas</th>
              <th width="12%">Aksi</th>
            </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <div id="full-width-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="mymodal-title">Tambah</h4>
          <button type="button" class="close" onclick="close_modal()" aria-hidden="true">×</button>
        </div>
        <div class="modal-body">
          <form id="form_app" method="post">
            <input type="hidden" name="id_unit_lain" id="id_unit_lain">
            <div class="form-group mb-3">
              <label class="text-primary">Tugas</label>
              <input type="text" class="form-control" name="tugas" id="tugas" autocomplete="off" required>
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
