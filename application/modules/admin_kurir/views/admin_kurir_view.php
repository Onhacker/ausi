<link href="<?= base_url('assets/admin/datatables/css/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet" type="text/css"/>

<div class="container-fluid">
  <div class="row"><div class="col-12">
    <div class="page-title-box">
      <div class="page-title-right">
        <ol class="breadcrumb m-0"><li class="breadcrumb-item active"><?= $subtitle; ?></li></ol>
      </div>
      <h4 class="page-title"><?= $subtitle; ?></h4>
    </div>
  </div></div>

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
          <th>Nama Kurir</th>
          <th>Kontak</th>
          <th>Kendaraan / Plat</th>
          <th>Status</th>
          <th>On Trip</th>
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
          <h4 class="mymodal-title">Tambah Kurir</h4>
          <button type="button" class="close" onclick="close_modal()" aria-hidden="true">×</button>
        </div>

        <div class="modal-body">
          <form id="form_app" method="post">
            <input type="hidden" name="id" id="id">

            <div class="row">
              <div class="col-md-4">
                <div class="form-group mb-2">
                  <label class="text-primary">Nama Kurir</label>
                  <input type="text" class="form-control" name="nama" id="nama" required autocomplete="off" placeholder="Nama">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group mb-2">
                  <label class="text-primary">No. HP / WA</label>
                  <input type="text" class="form-control" name="phone" id="phone" placeholder="08xxxxx">
                  <small class="text-muted">Gunakan nomor aktif</small>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group mb-2">
                  <label class="text-primary">Status</label>
                  <select class="custom-select" name="status" id="status">
                    <option value="available">available</option>
                    <option value="ontask">ontask</option>
                    <option value="off">off</option>
                  </select>
                </div>
              </div>
            </div><!-- row 1 -->

            <div class="row">
              <div class="col-md-4">
                <div class="form-group mb-2">
                  <label class="text-primary">Kendaraan</label>
                  <input type="text" class="form-control" name="vehicle" id="vehicle" placeholder="Motor / Mobil / Becak">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group mb-2">
                  <label class="text-primary">Plat Nomor</label>
                  <input type="text" class="form-control" name="plate" id="plate" placeholder="DD 1234 XX">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group mb-2">
                  <label class="text-primary">On Trip Count</label>
                  <input type="number" class="form-control" name="on_trip_count" id="on_trip_count" min="0" step="1" placeholder="0">
                  <small class="text-muted">Berapa order yang lagi jalan</small>
                </div>
              </div>
            </div><!-- row 2 -->

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
      sSearch:"<i class='ti-search'></i> Cari Kurir :",
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
      url:"<?= site_url('admin_kurir/get_dataa') ?>",
      type:"POST"
    },
    columns:[
      {data:"cek", orderable:false},
      {data:"no",  orderable:false},
      {data:"nama"},
      {data:"kontak"},
      {data:"kendaraan"},
      {data:"status"},
      {data:"on_trip_count"},
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

}); // end ready


function add(){
  save_method = 'add';
  $('#form_app')[0].reset();
  $('#id').val('');
  $('#status').val('off');
  $('#on_trip_count').val('0');

  $('.mymodal-title').text('Tambah Kurir');
  $('#full-width-modal').modal('show');
}

function edit(id=null){
  let targetId = id;
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

  $.getJSON("<?= site_url('admin_kurir/get_one/') ?>"+targetId)
    .done(function(r){
      close_loader();

      if (!r.success){
        Swal.fire(r.title||'Gagal', r.pesan||'Tidak bisa mengambil data', 'error');
        return;
      }

      const d = r.data;
      $('#id').val(d.id);
      $('#nama').val(d.nama);
      $('#phone').val(d.phone);
      $('#vehicle').val(d.vehicle);
      $('#plate').val(d.plate);
      $('#status').val(d.status);
      $('#on_trip_count').val(d.on_trip_count);

      $('.mymodal-title').html('Edit Kurir <code>#'+d.id+'</code>');
      $('#full-width-modal').modal('show');
    })
    .fail(function(){
      close_loader();
      Swal.fire("Error","Gagal mengambil data","error");
    });
}

function simpan(){
  const url = (save_method === 'add')
    ? "<?= site_url('admin_kurir/add') ?>"
    : "<?= site_url('admin_kurir/update') ?>";

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
      url:"<?= site_url('admin_kurir/hapus_data') ?>",
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
