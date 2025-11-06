<link href="<?= base_url('assets/admin/datatables/css/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet" type="text/css"/>

<div class="container-fluid">
  <div class="row">
   <!--  <div class="col-12">
    <div class="page-title-box">
      <div class="page-title-right">
        <ol class="breadcrumb m-0"><li class="breadcrumb-item active"><?= $subtitle; ?></li></ol>
      </div>
      <h4 class="page-title"><?= $subtitle; ?></h4>
    </div>
  </div> -->
</div>

  <div class="row"><div class="col-12">
    <div class="card"><div class="card-body">
       <h4 class="header-title"><?= $subtitle; ?></h4>

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
          <th>Nama Meja</th>
          <th width="10%">Kategori</th>
          <th width="15%">Harga Default</th>
          <th width="10%">Status</th>
          <th width="18%">Update Terakhir</th>
          <th width="15%">Aksi</th>
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
          <h4 class="mymodal-title">Tambah Meja Billiard</h4>
          <button type="button" class="close" onclick="close_modal()" aria-hidden="true">×</button>
        </div>

        <div class="modal-body">
          <form id="form_app" method="post">
            <input type="hidden" name="id_meja" id="id_meja">

            <div class="row">
              <div class="col-md-6">
                <div class="form-group mb-2">
                  <label class="text-primary">Nama Meja</label>
                  <input type="text" class="form-control" name="nama_meja" id="nama_meja" required autocomplete="off" placeholder="Contoh: Meja 1 - Regular / VIP Room">
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group mb-2">
                  <label class="text-primary">Kategori</label>
                  <select class="custom-select" name="kategori" id="kategori" required>
                    <option value="reguler">Reguler</option>
                    <option value="vip">VIP</option>
                  </select>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group mb-2">
                  <label class="text-primary">Harga/Jam Default</label>
                  <input type="number" class="form-control" name="harga_per_jam" id="harga_per_jam" min="0" step="1000" placeholder="35000">
                  <small class="text-muted">Dipakai sebagai fallback</small>
                </div>
              </div>
            </div><!-- row 1 -->

            <div class="row">
              <div class="col-md-3">
                <div class="form-group mb-2">
                  <label class="text-primary">Status</label>
                  <select class="custom-select" name="aktif" id="aktif">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                  </select>
                </div>
              </div>
            </div>

            <hr class="my-3">
            <h5 class="text-primary mb-2">Jam Operasional</h5>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group mb-2">
                  <label>Jam Buka</label>
                  <input type="time" class="form-control" name="jam_buka" id="jam_buka" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group mb-2">
                  <label>Jam Tutup</label>
                  <input type="time" class="form-control" name="jam_tutup" id="jam_tutup" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group mb-2">
                  <label>Jam Tutup Voucher</label>
                  <input type="time" class="form-control" name="jam_tutup_voucer" id="jam_tutup_voucer" required>
                  <small class="text-muted d-block">Set sampai jam berapa voucher masih bisa dipakai</small>
                </div>
              </div>
            </div>

            <hr class="my-3">
            <h5 class="text-primary mb-2">Tarif Weekday (Senin - Jumat)</h5>
            <div class="row">
              <div class="col-md-2">
                <div class="form-group mb-2">
                  <label>Day Start</label>
                  <input type="time" class="form-control" name="wk_day_start" id="wk_day_start" required>
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group mb-2">
                  <label>Day End</label>
                  <input type="time" class="form-control" name="wk_day_end" id="wk_day_end" required>
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group mb-2">
                  <label>Day Rate</label>
                  <input type="number" class="form-control" name="wk_day_rate" id="wk_day_rate" min="0" step="1000" placeholder="35000">
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-group mb-2">
                  <label>Night Start</label>
                  <input type="time" class="form-control" name="wk_night_start" id="wk_night_start" required>
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group mb-2">
                  <label>Night End</label>
                  <input type="time" class="form-control" name="wk_night_end" id="wk_night_end" required>
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group mb-2">
                  <label>Night Rate</label>
                  <input type="number" class="form-control" name="wk_night_rate" id="wk_night_rate" min="0" step="1000" placeholder="40000">
                </div>
              </div>
            </div><!-- weekday row -->

            <hr class="my-3">
            <h5 class="text-primary mb-2">Tarif Weekend (Sabtu - Minggu / Libur)</h5>
            <div class="row">
              <div class="col-md-2">
                <div class="form-group mb-2">
                  <label>Day Start</label>
                  <input type="time" class="form-control" name="we_day_start" id="we_day_start" required>
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group mb-2">
                  <label>Day End</label>
                  <input type="time" class="form-control" name="we_day_end" id="we_day_end" required>
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group mb-2">
                  <label>Day Rate</label>
                  <input type="number" class="form-control" name="we_day_rate" id="we_day_rate" min="0" step="1000" placeholder="40000">
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-group mb-2">
                  <label>Night Start</label>
                  <input type="time" class="form-control" name="we_night_start" id="we_night_start" required>
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group mb-2">
                  <label>Night End</label>
                  <input type="time" class="form-control" name="we_night_end" id="we_night_end" required>
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group mb-2">
                  <label>Night Rate</label>
                  <input type="number" class="form-control" name="we_night_rate" id="we_night_rate" min="0" step="1000" placeholder="50000">
                </div>
              </div>
            </div><!-- weekend row -->

            <hr class="my-3">
            <div class="form-group mb-2">
              <label class="text-primary">Catatan / Fasilitas</label>
              <textarea class="form-control" name="catatan" id="catatan" rows="3" placeholder="Contoh: VIP room + karaoke + 1 pitcher free drink"></textarea>
            </div>

          </form>
        </div><!-- modal-body -->

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary waves-effect" onclick="close_modal()">Batal</button>
          <button type="button" onclick="simpan()" class="btn btn-primary waves-effect waves-light">Simpan</button>
        </div>
      </div>
    </div>
  </div>

  <?php
    $this->load->view("backend/global_css");
    // $this->load->view($controller."_js"); // kita inline JS di bawah
  ?>
</div>

<script>
var table, save_method = 'add';

function loader(){
  if (window.Swal) {
    Swal.fire({
      title:"Proses...",
      html:"Jangan tutup halaman ini",
      allowOutsideClick:false,
      didOpen:()=>Swal.showLoading()
    });
  }
}
function close_loader(){ if (window.Swal) Swal.close(); }
function reload_table(){ table.ajax.reload(null,false); }

$(document).ready(function(){

  $.fn.dataTableExt.oApi.fnPagingInfo = function(o){
    return {
      iStart:o._iDisplayStart,
      iEnd:o.fnDisplayEnd(),
      iLength:o._iDisplayLength,
      iTotal:o.fnRecordsTotal(),
      iFilteredTotal:o.fnRecordsDisplay(),
      iPage:Math.ceil(o._iDisplayStart/o._iDisplayLength),
      iTotalPages:Math.ceil(o.fnRecordsDisplay()/o._iDisplayLength)
    };
  };

  table = $('#datable_1').DataTable({
    lengthMenu: [[10,25,50,100,-1],[10,25,50,100,'All']],
    oLanguage:{
      sProcessing:"Memuat Data...",
      sSearch:"<i class='ti-search'></i> Cari Meja :",
      sZeroRecords:"Maaf Data Tidak Ditemukan",
      sLengthMenu:"Tampil _MENU_ Data",
      sEmptyTable:"Data Tidak Ada",
      sInfo:"Menampilkan _START_ - _END_ dari _TOTAL_ Total Data",
      sInfoEmpty:"Tidak ada data ditampilkan",
      sInfoFiltered:"(Filter dari _MAX_ total Data)",
      oPaginate:{
        sNext:"<i class='fe-chevrons-right'></i>",
        sPrevious:"<i class='fe-chevrons-left'></i>"
      }
    },
    processing:true,
    serverSide:true,
    scrollX:true,
    ajax:{
      url:"<?= site_url('admin_meja_billiard/get_dataa') ?>",
      type:"POST"
    },
    columns:[
      {data:"cek", orderable:false},
      {data:"no",  orderable:false},
      {data:"nama_meja"},
      {data:"kategori"},
      {data:"harga_per_jam"},
      {data:"aktif"},
      {data:"updated_at"},
      {data:"aksi", orderable:false}
    ],
    order: [],
    rowCallback:function(row, data, iDisplayIndex){
      var info = this.fnPagingInfo();
      var idx  = info.iPage * info.iLength + (iDisplayIndex + 1);
      $('td:eq(1)', row).html(idx);
    }
  });

  $("#check-all").on('click', function(){
    $(".data-check").prop('checked', $(this).prop('checked'));
  });

}); // end document.ready


function add(){
  save_method = 'add';
  $('#form_app')[0].reset();
  $('#id_meja').val('');
  $('#aktif').val('1');
  $('#kategori').val('reguler');

  $('.mymodal-title').text('Tambah Meja Billiard');
  $('#full-width-modal').modal('show');
}

function edit(id_meja=null){
  let targetId = id_meja;
  if (!targetId){
    const list_id = [];
    $(".data-check:checked").each(function(){ list_id.push(this.value); });
    if (list_id.length !== 1){
      Swal.fire("Info","Pilih satu data untuk diedit.","warning");
      return;
    }
    targetId = list_id[0];
  }

  save_method='update';
  loader();

  $.getJSON("<?= site_url('admin_meja_billiard/get_one/') ?>"+targetId)
    .done(function(r){
      close_loader();

      if (!r.success){
        Swal.fire(r.title||'Gagal', r.pesan||'Tidak bisa mengambil data', 'error');
        return;
      }

      const d = r.data;
      $('#id_meja').val(d.id_meja);
      $('#nama_meja').val(d.nama_meja);
      $('#kategori').val(d.kategori); // NEW
      $('#harga_per_jam').val(d.harga_per_jam);

      $('#jam_buka').val(d.jam_buka);
      $('#jam_tutup').val(d.jam_tutup);
      $('#jam_tutup_voucer').val(d.jam_tutup_voucer); // NEW

      $('#wk_day_start').val(d.wk_day_start);
      $('#wk_day_end').val(d.wk_day_end);
      $('#wk_day_rate').val(d.wk_day_rate);

      $('#wk_night_start').val(d.wk_night_start);
      $('#wk_night_end').val(d.wk_night_end);
      $('#wk_night_rate').val(d.wk_night_rate);

      $('#we_day_start').val(d.we_day_start);
      $('#we_day_end').val(d.we_day_end);
      $('#we_day_rate').val(d.we_day_rate);

      $('#we_night_start').val(d.we_night_start);
      $('#we_night_end').val(d.we_night_end);
      $('#we_night_rate').val(d.we_night_rate);

      $('#aktif').val(d.aktif);
      $('#catatan').val(d.catatan);

      $('.mymodal-title').html('Edit Meja Billiard <code>#'+d.id_meja+'</code>');
      $('#full-width-modal').modal('show');
    })
    .fail(function(){
      close_loader();
      Swal.fire("Error","Gagal mengambil data","error");
    });
}

function simpan(){
  const url = (save_method === 'add')
    ? "<?= site_url('admin_meja_billiard/add') ?>"
    : "<?= site_url('admin_meja_billiard/update') ?>";

  loader();
  $.ajax({
    url: url,
    type: 'POST',
    data: $('#form_app').serialize(),
    dataType: 'json'
  }).done(function(r){
    close_loader();

    if (!r.success){
        Swal.fire(r.title||'Gagal', r.pesan||'Terjadi kesalahan', 'error');
        return;
    }

    Swal.fire(r.title, r.pesan, 'success');
    $('#full-width-modal').modal('hide');
    reload_table();
  }).fail(function(){
    close_loader();
    Swal.fire('Gagal','Tidak dapat mengirim data','error');
  });
}

function hapus_data(){
  const list_id = [];
  $(".data-check:checked").each(function(){ list_id.push(this.value); });

  if (list_id.length === 0) {
    Swal.fire("Info","Pilih minimal satu data","warning");
    return;
  }

  Swal.fire({
    title:"Yakin ingin menghapus "+list_id.length+" data?",
    icon:"warning",
    showCancelButton:true,
    confirmButtonColor:"#d33",
    cancelButtonColor:"#3085d6",
    confirmButtonText:"Ya, Hapus",
    cancelButtonText:"Batal",
    allowOutsideClick:false
  }).then((res)=>{
    if (!res.isConfirmed) return;

    loader();

    $.ajax({
      url:"<?= site_url('admin_meja_billiard/hapus_data') ?>",
      type:"POST",
      data:{id:list_id},
      dataType:"json"
    }).done(function(r){
      close_loader();
      if (!r.success){
        Swal.fire(r.title||'Gagal', r.pesan||'Sebagian gagal dihapus', 'error');
      } else {
        Swal.fire(r.title, r.pesan, 'success');
        reload_table();
      }
    }).fail(function(){
      close_loader();
      Swal.fire("Gagal","Koneksi bermasalah","error");
    });
  });
}

function close_modal(){
  Swal.fire({
    title:"Tutup formulir?",
    text:"Perubahan yang belum disimpan akan hilang.",
    icon:"warning",
    showCancelButton:true,
    confirmButtonText:"Tutup",
    cancelButtonText:"Batal"
  }).then(r=>{
    if(r.value) $('#full-width-modal').modal('hide');
  });
}
</script>
