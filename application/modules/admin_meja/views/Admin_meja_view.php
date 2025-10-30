<link href="<?= base_url('assets/admin/datatables/css/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet" type="text/css"/>

<div class="container-fluid">
  <div class="row"><div class="col-12">
    <div class="page-title-box">
      <div class="page-title-right"><ol class="breadcrumb m-0"><li class="breadcrumb-item active"><?= $subtitle; ?></li></ol></div>
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
          <th class="text-center" width="5%"><div class="checkbox checkbox-primary checkbox-single"><input id="check-all" type="checkbox"><label></label></div></th>
          <th width="5%">No.</th>
          <th>Nama</th>
          <th width="12%">Kode</th>
          <th width="10%">Kapasitas</th>
          <th>Area</th>
          <th width="10%">Status</th>
          <th width="6%">QR</th>
          <th width="16%">Aksi</th>
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
          <h4 class="mymodal-title">Tambah Meja</h4>
          <button type="button" class="close" onclick="close_modal()" aria-hidden="true">×</button>
        </div>
        <div class="modal-body">
          <form id="form_app" method="post">
            <input type="hidden" name="id" id="id">
            <div class="form-group mb-2">
              <label class="text-primary">Nama Meja</label>
              <input type="text" class="form-control" name="nama" id="nama" required autocomplete="off" placeholder="Contoh: Meja 1">
            </div>
            <div class="form-group mb-2">
              <label class="text-primary">Kapasitas</label>
              <input type="number" class="form-control" name="kapasitas" id="kapasitas" min="1" placeholder="Contoh: 4">
            </div>
            <div class="form-group mb-2">
              <label class="text-primary">Area/Ruangan</label>
              <input type="text" class="form-control" name="area" id="area" placeholder="Contoh: Indoor / Outdoor / Lantai 2">
            </div>
            <div class="form-group mb-2">
              <label class="text-primary">Status</label>
              <select class="custom-select" name="status" id="status">
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
              </select>
            </div>

            <div class="form-group mb-0">
              <label class="text-primary d-flex align-items-center">QR Code
                <small class="text-muted ml-2">Dipakai untuk scan ke halaman produk</small>
              </label>
              <div id="qr-preview" class="pt-2">
                <div class="text-muted small">QR akan dibuat otomatis setelah disimpan.</div>
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
    // $this->load->view($controller."_js"); // langsung inline di bawah
  ?>
</div>

<script>
var table, save_method = 'add';

function loader(){
  if (window.Swal) {
    Swal.fire({title:"Proses...", html:"Jangan tutup halaman ini", allowOutsideClick:false, didOpen:()=>Swal.showLoading()});
  }
}
function close_loader(){ if (window.Swal) Swal.close(); }
function reload_table(){ table.ajax.reload(null,false); }

$(document).ready(function(){
  $.fn.dataTableExt.oApi.fnPagingInfo = function(o){
    return {iStart:o._iDisplayStart, iEnd:o.fnDisplayEnd(), iLength:o._iDisplayLength, iTotal:o.fnRecordsTotal(), iFilteredTotal:o.fnRecordsDisplay(), iPage:Math.ceil(o._iDisplayStart/o._iDisplayLength), iTotalPages:Math.ceil(o.fnRecordsDisplay()/o._iDisplayLength)};
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
      oPaginate:{ sNext:"<i class='fe-chevrons-right'></i>", sPrevious:"<i class='fe-chevrons-left'></i>"}
    },
    processing:true, serverSide:true, scrollX:true,
    ajax:{ url:"<?= site_url('admin_meja/get_dataa') ?>", type:"POST" },
    columns:[
      {data:"cek", orderable:false},
      {data:"no",  orderable:false},
      {data:"nama"},
      {data:"kode"},
      {data:"kapasitas"},
      {data:"area"},
      {data:"status"},
      {data:"qr", orderable:false},
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
});

function add(){
  save_method = 'add';
  $('#form_app')[0].reset();
  $('#id').val('');
  $('#qr-preview').html('<div class="text-muted small">QR akan dibuat otomatis setelah disimpan.</div>');
  $('.mymodal-title').text('Tambah Meja');
  $('#full-width-modal').modal('show');
}

function edit(id=null){
  let targetId = id;
  if (!targetId){
    const list_id = []; $(".data-check:checked").each(function(){ list_id.push(this.value); });
    if (list_id.length !== 1){ Swal.fire("Info","Pilih satu data untuk diedit.","warning"); return; }
    targetId = list_id[0];
  }
  save_method='update';
  loader();
  $.getJSON("<?= site_url('admin_meja/get_one/') ?>"+targetId)
    .done(function(r){
      close_loader();
      if (!r.success){ Swal.fire(r.title||'Gagal', r.pesan||'Tidak bisa mengambil data', 'error'); return; }
      const d = r.data;
      $('#id').val(d.id);
      $('#nama').val(d.nama);
      $('#kapasitas').val(d.kapasitas);
      $('#area').val(d.area);
      $('#status').val(d.status);
      if (d.qrcode){
        const img = '<div class="p-2 rounded border"><img src="<?= base_url(); ?>'+d.qrcode+'" alt="QR" style="max-width:180px"></div>'
                 + '<div class="small mt-2"><a class="btn btn-sm btn-primary" target="_blank" href="<?= site_url('admin_meja/print_qr/') ?>'+d.id+'"><i class="fe-printer"></i> Cetak</a></div>';
        $('#qr-preview').html(img);
      } else {
        $('#qr-preview').html('<div class="text-muted small">QR akan dibuat otomatis setelah disimpan.</div>');
      }
      $('.mymodal-title').html('Edit Meja <code>#'+d.id+'</code>');
      $('#full-width-modal').modal('show');
    })
    .fail(function(){ close_loader(); Swal.fire("Error","Gagal mengambil data","error"); });
}

function simpan(){
  const url = (save_method === 'add')
    ? "<?= site_url('admin_meja/add') ?>"
    : "<?= site_url('admin_meja/update') ?>";

  loader();
  $.ajax({
    url: url,
    type: 'POST',
    data: $('#form_app').serialize(),
    dataType: 'json'
  }).done(function(r){
    close_loader();
    if (!r.success){ Swal.fire(r.title||'Gagal', r.pesan||'Terjadi kesalahan', 'error'); return; }
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
  if (list_id.length === 0) { Swal.fire("Info","Pilih minimal satu data","warning"); return; }

  Swal.fire({
    title:"Yakin ingin menghapus "+list_id.length+" data?",
    icon:"warning", showCancelButton:true, confirmButtonColor:"#d33", cancelButtonColor:"#3085d6",
    confirmButtonText:"Ya, Hapus", cancelButtonText:"Batal", allowOutsideClick:false
  }).then((res)=>{
    if (!res.isConfirmed) return;
    loader();
    $.ajax({
      url:"<?= site_url('admin_meja/hapus_data') ?>",
      type:"POST",
      data:{id:list_id},
      dataType:"json"
    }).done(function(r){
      close_loader();
      if (!r.success){ Swal.fire(r.title||'Gagal', r.pesan||'Sebagian gagal dihapus', 'error'); }
      else { Swal.fire(r.title, r.pesan, 'success'); reload_table(); }
    }).fail(function(){ close_loader(); Swal.fire("Gagal","Koneksi bermasalah","error"); });
  });
}

function close_modal(){
  Swal.fire({
    title:"Tutup formulir?", text:"Perubahan yang belum disimpan akan hilang.",
    icon:"warning", showCancelButton:true, confirmButtonText:"Tutup", cancelButtonText:"Batal"
  }).then(r=>{ if(r.value) $('#full-width-modal').modal('hide'); });
}

function print_qr(id){
  window.open("<?= site_url('admin_meja/print_qr/') ?>"+id, "_blank");
}
</script>
