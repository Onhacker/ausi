<link href="<?= base_url('assets/admin/datatables/css/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet" type="text/css"/>
<style type="text/css">
  /* pastikan tabel ambil lebar penuh saat tab dibuka */
#datatable_sub.dataTable { width:100% !important; }
/* header dan isi tidak membungkus aneh saat scrollX */
#datatable_sub thead th { white-space: nowrap; }
#datable_1.dataTable,
#datatable_sub.dataTable { width:100% !important; }
#datable_1 thead th,
#datatable_sub thead th { white-space:nowrap; }

</style>
<div class="container-fluid">
  <div class="row">
    <!-- <div class="col-12">
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
       <h4 class="header-title"><?= $subtitle; ?></h4>

  <!--    <ul class="nav nav-tabs mb-3" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" data-toggle="tab" href="#tab-kategori" role="tab">Kategori</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" data-toggle="tab" href="#tab-subkategori" role="tab">Subkategori</a>
  </li>
</ul>
 -->
<ul class="nav nav-pills navtab-bg nav-justified" role="tablist">

  <li class="nav-item">
    <a class="nav-link active" data-toggle="tab" href="#tab-kategori" role="tab">Kategori</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" data-toggle="tab" href="#tab-subkategori" role="tab">Subkategori</a>
  </li>
</ul>

<div class="tab-content">
  <!-- ========== TAB KATEGORI (ASLI ANDA) ========== -->
  <div class="tab-pane show active" id="tab-kategori" role="tabpanel">
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
          <th>Nama</th>
          <th>Slug</th>
          <th width="10%">Aktif</th>
          <th width="12%">Aksi</th>
        </tr>
      </thead>
    </table>
  </div>

  <!-- ========== TAB SUBKATEGORI (BARU) ========== -->
  <div class="tab-pane" id="tab-subkategori" role="tabpanel">
    <div class="button-list mb-2">
      <div class="d-flex flex-wrap align-items-center">
        <button type="button" onclick="sub_add()" class="btn btn-success btn-rounded btn-sm mr-2 waves-effect waves-light">
          <span class="btn-label"><i class="fe-plus-circle"></i></span>Tambah
        </button>
        <button type="button" onclick="sub_reload()" class="btn btn-info btn-rounded btn-sm mr-2 waves-effect waves-light">
          <span class="btn-label"><i class="fe-refresh-ccw"></i></span>Refresh
        </button>
        <button type="button" onclick="sub_hapus()" class="btn btn-danger btn-rounded btn-sm waves-effect waves-light">
          <span class="btn-label"><i class="fa fa-trash"></i></span>Hapus
        </button>

        <div class="ml-auto d-flex align-items-center">
          <label class="mb-0 mr-2">Filter Kategori:</label>
          <select id="sub-filter-kategori" class="form-control form-control-sm" style="min-width:220px"></select>
        </div>
      </div>
    </div>

    <table id="datatable_sub" class="table table-striped table-bordered w-100">
      <thead>
        <tr>
          <th class="text-center" width="5%">
            <div class="checkbox checkbox-primary checkbox-single">
              <input id="sub-check-all" type="checkbox"><label></label>
            </div>
          </th>
          <th width="5%">No.</th>
          <th>Kategori</th>
          <th>Nama</th>
          <th>Slug</th>
          <th width="10%">Aktif</th>
          <th width="12%">Aksi</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<div id="modal-sub" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="mymodal-sub-title">Tambah Subkategori</h4>
        <button type="button" class="close" onclick="sub_close()" aria-hidden="true">×</button>
      </div>
      <div class="modal-body">
        <form id="form_sub" method="post">
          <input type="hidden" name="id" id="sub_id">
          <div class="form-group mb-2">
            <label class="text-primary">Kategori</label>
            <select class="form-control" name="kategori_id" id="sub_kategori_id" required></select>
          </div>
          <div class="form-group mb-2">
            <label class="text-primary">Nama</label>
            <input type="text" class="form-control" name="nama" id="sub_nama" autocomplete="off" required>
          </div>
          <div class="form-group mb-2">
            <label class="text-primary">Deskripsi</label>
            <input type="text" class="form-control" name="deskripsi" id="sub_deskripsi">
          </div>
          <div class="form-group mb-0">
            <div class="checkbox checkbox-primary">
              <input id="sub_is_active" type="checkbox" name="is_active" value="1" checked>
              <label for="sub_is_active">Aktif</label>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary waves-effect" onclick="sub_close()">Batal</button>
        <button type="button" onclick="sub_simpan()" class="btn btn-primary waves-effect waves-light">Simpan</button>
      </div>
    </div>
  </div>
</div>


  <!-- Modal -->
  <div id="full-width-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="mymodal-title">Tambah Kategori</h4>
          <button type="button" class="close" onclick="close_modal()" aria-hidden="true">×</button>
        </div>
        <div class="modal-body">
          <form id="form_app" method="post">
            <input type="hidden" name="id" id="id">
            <div class="form-group mb-2">
              <label class="text-primary">Nama</label>
              <input type="text" class="form-control" name="nama" id="nama" autocomplete="off" required>
            </div>
            <div class="form-group mb-2">
              <label class="text-primary">Deskripsi</label>
              <input type="text" class="form-control" name="deskripsi" id="deskripsi">
            </div>
            <div class="form-group mb-0">
              <div class="checkbox checkbox-primary">
                <input id="is_active" type="checkbox" name="is_active" value="1" checked>
                <label for="is_active">Aktif</label>
              </div>
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
