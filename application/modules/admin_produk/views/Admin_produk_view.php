<link href="<?= base_url('assets/admin/datatables/css/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet" type="text/css"/>
<link href="<?= base_url('assets/admin/summernote/summernote-bs4.min.css'); ?>" rel="stylesheet" type="text/css"/>

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
       <h4 class="header-title"><?= $subtitle; ?></h4>

    <div class="d-flex align-items-center flex-column flex-md-row mb-2">
  <!-- Kiri: tombol-tombol -->
  <div class="button-list d-flex flex-wrap">
    <button type="button" onclick="add()" class="btn btn-success btn-rounded btn-sm waves-effect waves-light mr-1 mb-1">
      <span class="btn-label"><i class="fe-plus-circle"></i></span>Tambah
    </button>

    <button type="button" onclick="set_andalan()" class="btn btn-warning btn-rounded btn-sm waves-effect waves-light mr-1 mb-1">
      <span class="btn-label"><i class="fe-star"></i></span>Set Andalan
    </button>

    <button type="button" onclick="reload_table()" class="btn btn-info btn-rounded btn-sm waves-effect waves-light mr-1 mb-1">
      <span class="btn-label"><i class="fe-refresh-ccw"></i></span>Refresh
    </button>

    <button type="button" onclick="hapus_data()" class="btn btn-danger btn-rounded btn-sm waves-effect waves-light mr-1 mb-1">
      <span class="btn-label"><i class="fa fa-trash"></i></span>Hapus
    </button>
  </div>

  <!-- Kanan: filter kategori (di desktop terdorong ke kanan; di mobile turun & full width) -->
  <div class="ml-md-auto mt-2 mt-md-0" style="min-width:280px; max-width:420px; width:100%;">
    <div class="input-group input-group-sm">
      <div class="input-group-prepend">
        <span class="input-group-text"><i class="fe-layers"></i></span>
      </div>
      <select id="filter_kategori" class="form-control">
        <option value="">— Semua Kategori —</option>
        <?php
          $kats_filter = $this->db->order_by('nama','asc')->get('kategori_produk')->result();
          foreach($kats_filter as $kf){
            echo '<option value="'.(int)$kf->id.'">'.htmlspecialchars($kf->nama, ENT_QUOTES, 'UTF-8').'</option>';
          }
        ?>
      </select>
      <div class="input-group-append">
        <button class="btn btn-outline-secondary" type="button" id="btn-clear-kat" title="Reset">&times;</button>
      </div>
    </div>
  </div>
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
            <th>Produk</th>
            <th>Kategori</th>
            
            <th>SKU</th>
            <th width="12%">Harga</th>
            <th width="8%">Stok</th>
            <th width="10%">Aktif</th>
            <th width="12%">Aksi</th>
          </tr>
        </thead>
      </table>

    </div></div>
  </div></div>

  <!-- Modal -->
  <div id="full-width-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-full modal-dialog-scrollable modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="mymodal-title">Tambah Produk</h4>
          <button type="button" class="close" onclick="close_modal()" aria-hidden="true">×</button>
        </div>
        <div class="modal-body">
          <form id="form_app" method="post">
            <input type="hidden" name="id" id="id">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group mb-2">
                  <label class="text-primary">Kategori</label>
                  <select name="kategori_id" id="kategori_id" class="form-control" required>
                    <option value="">— Pilih Kategori —</option>
                    <?php
                      $kats = $this->db->order_by('nama','asc')->get('kategori_produk')->result();
                      foreach($kats as $k){
                        echo '<option value="'.(int)$k->id.'">'.htmlspecialchars($k->nama, ENT_QUOTES, 'UTF-8').'</option>';
                      }
                    ?>
                  </select>
                </div>
                <div class="form-group mb-2">
                  <label class="text-primary">Sub Kategori</label>
                  <select name="sub_kategori_id" id="sub_kategori_id" class="form-control" disabled>
                    <option value="">— Pilih Sub Kategori —</option>
                  </select>
                  <small class="text-muted">Pilih kategori terlebih dahulu.</small>
                </div>

                <div class="form-group mb-2">
                  <label class="text-primary">Nama Produk</label>
                  <input type="text" class="form-control" name="nama" id="nama" autocomplete="off" required>
                </div>
                <div class="form-group mb-2">
                  <label class="text-primary">Kata Kunci Pencarian</label>
                  <input type="text" class="form-control" name="kata_kunci" id="kata_kunci" autocomplete="off" required>
                </div>
                <div class="form-group mb-2">
                  <label class="text-primary">SKU (Auto)</label>
                  <input type="text" class="form-control" name="sku" id="sku" autocomplete="off" readonly>
                  <small class="text-muted">SKU digenerate otomatis saat simpan/update.</small>

                </div>
                <div class="form-group mb-2">
                  <label class="text-primary">Harga</label>
                  <input type="text" class="form-control rupiah" name="harga" id="harga"
                  inputmode="numeric" autocomplete="off" placeholder="Rp 0" required>
                </div>
                <div class="form-group mb-2">
                  <label class="text-primary">HPP</label>
                  <input type="text" class="form-control rupiah" name="hpp" id="hpp"
                  inputmode="numeric" autocomplete="off" placeholder="Rp 0">
                </div>
                <div class="form-group mb-2">
                  <label class="text-primary">Stok</label>
                  <input type="number" class="form-control" name="stok" id="stok" step="1" value="0">
                </div>
                <div class="form-group mb-2">
                  <label class="text-primary">Satuan</label>
                  <input type="text" class="form-control" name="satuan" id="satuan" placeholder="porsi/gelas/botol">
                </div>
              </div>
              <div class="col-md-8">
                <div class="form-group mb-2">
                  <label class="text-primary">Gambar Produk</label>
                  <div class="input-group">
                    <div class="custom-file">
                      <input type="file" class="custom-file-input" name="gambar_file" id="gambar_file" accept="image/*" id="inputGroupFile04">
                      <label class="custom-file-label" for="inputGroupFile04">Choose file</label>
                    </div>
                  </div>
                  <input type="hidden" name="gambar" id="gambar">
                  <small class="text-muted">Tipe: gif/jpg/jpeg/png/webp, maks 2MB.</small>
                  <div class="mt-2">
                    <img id="preview" src="" alt="" style="display:none;width:80px;height:80px;object-fit:cover;border-radius:6px">
                  </div>
                </div>
                <div class="form-group mb-2">
                  <label class="text-primary">Deskripsi</label>
                  <textarea name="deskripsi" id="deskripsi" class="form-control"></textarea>
                  <small class="text-muted">Deskripsi mendukung format (bold, list, gambar, dll.).</small>
                </div>
                <div class="form-group mb-0 mt-2">
                  <div class="checkbox checkbox-warning">
                    <input id="recomended" type="checkbox" name="recomended" value="1">
                    <label for="recomended">Jadikan Andalan</label>
                  </div>
                </div>
                <div class="form-group mb-0">
                  <div class="checkbox checkbox-primary">
                    <input id="is_active" type="checkbox" name="is_active" value="1" checked>
                    <label for="is_active">Aktif</label>
                  </div>
                </div>
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
