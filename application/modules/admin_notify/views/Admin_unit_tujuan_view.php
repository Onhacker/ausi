<link href="<?php echo base_url(); ?>assets/admin/datatables/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
<div class="container-fluid">
  <div class="row"><div class="col-12">
    <div class="page-title-box">
      <div class="page-title-right">
        <ol class="breadcrumb m-0">
          <li class="breadcrumb-item"><a href="javascript:void(0);"><?= $title ?></a></li>
          <li class="breadcrumb-item active"><?= $subtitle ?></li>
        </ol>
      </div>
      <h4 class="page-title"><?= $subtitle ?></h4>
    </div>
  </div></div>

  <div class="row"><div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="button-list mb-2">
          <button type="button" onclick="add()" class="btn btn-success btn-rounded waves-effect waves-light">
            <span class="btn-label"><i class="fa fa-plus"></i></span>Tambah
          </button>
          <button type="button" onclick="edit()" class="btn btn-info btn-rounded waves-effect waves-light">
            <span class="btn-label"><i class="fa fa-edit"></i></span>Edit
          </button>
          <button type="button" onclick="hapus_data()" class="btn btn-danger btn-rounded waves-effect waves-light">
            <span class="btn-label"><i class="fa fa-trash"></i></span>Hapus
          </button>
        </div>

        <table id="datable_1" class="table table-striped table-bordered w-100">
          <thead>
            <tr>
              <th width="5%">
                <div class="checkbox checkbox-primary checkbox-single">
                  <input id="check-all" type="checkbox"><label></label>
                </div>
              </th>
              <th width="5%">No.</th>
              <th>ID</th>
              <th>Nama Unit</th>
              <th>Parent</th>
              <th>Nama Pejabat</th>
              <th>No. HP</th>
              <th>Kuota/Hari</th>
              <th>Pendamping Maks</th>
              <th>Update</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div></div>

  <!-- Modal -->
  <div id="full-width-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="mymodal-title">Tambah Data</h4>
          <button type="button" class="close" onclick="close_modal()" aria-hidden="true">×</button>
        </div>
        <div class="modal-body">
          <form id="form_app" method="post">
            <input type="hidden" name="id" id="id">

            <div class="form-group mb-3">
              <label class="text-primary mb-1">Parent</label>
              <!-- Bisa pakai Select2 ajax ke endpoint parent_options -->
              <select name="parent_id" id="parent_id" class="form-control" data-toggle="select2" data-placeholder="— Root (tanpa parent) —">
                <option value="">— Root (tanpa parent) —</option>
                <?php
                // jika ingin render statis:
                // foreach($this->dm->arr_parent() as $k=>$v){ echo '<option value="'.$k.'">'.$v.'</option>'; }
                ?>
              </select>
              <small class="text-muted">Pilih induk. Kosongkan bila unit level atas.</small>
            </div>

            <div class="form-group mb-3">
              <label class="text-primary mb-1">Nama Unit</label>
              <input type="text" class="form-control" name="nama_unit" id="nama_unit" autocomplete="off" required>
            </div>

            <div class="form-group mb-3">
              <label class="text-primary mb-1">Nama Pejabat</label>
              <input type="text" class="form-control" name="nama_pejabat" id="nama_pejabat" autocomplete="off">
            </div>

            <div class="form-row">
              <div class="form-group col-md-6">
                <label class="text-primary mb-1">No. HP</label>
                <input type="text" class="form-control" name="no_hp" id="no_hp" autocomplete="off" placeholder="62xxxxxxxxxxx">
              </div>
              <div class="form-group col-md-6">
                <label class="text-primary mb-1">Kuota Harian</label>
                <input type="number" min="0" class="form-control" name="kuota_harian" id="kuota_harian" required>
              </div>
            </div>

            <div class="form-group mb-0">
              <label class="text-primary mb-1">Jumlah Pendamping Maks (opsional)</label>
              <input type="number" min="0" class="form-control" name="jumlah_pendamping" id="jumlah_pendamping" placeholder="Kosongkan = tidak dibatasi">
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

  <?php $this->load->view(($controller)."_js"); ?>
</div>
