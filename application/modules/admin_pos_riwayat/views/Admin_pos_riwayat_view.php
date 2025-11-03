<link href="<?= base_url('assets/admin/datatables/css/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet" type="text/css"/>

<div class="container-fluid">
  <div class="row"><div class="col-12">
    <div class="page-title-box">
      <div class="page-title-right">
        <ol class="breadcrumb m-0">
          <li class="breadcrumb-item active"><?= $subtitle; ?></li>
        </ol>
      </div>
      <h4 class="page-title"><?= $title; ?></h4>
    </div>
  </div></div>

  <div class="mb-2 d-flex align-items-center flex-wrap justify-content-start">
    <button type="button" onclick="reload_pos_paid('user')" class="btn btn-warning btn-sm mr-2">
      <span class="btn-label"><i class="fe-refresh-ccw"></i></span>Refresh
    </button>

    <select id="filter-metode" class="form-control form-control-sm mr-2" style="width:180px">
      <option value="all" selected>Semua Metode</option>
      <option value="cash">Cash</option>
      <option value="qris">QRIS</option>
      <option value="transfer">Transfer</option>
    </select>

    <!-- <select id="filter-mode" class="form-control form-control-sm" style="width:180px">
      <option value="all" selected>Semua Mode</option>
      <option value="walkin">Bungkus</option>
      <option value="dinein">Makan di Tempat</option>
      <option value="delivery">Antar/Kirim</option>
    </select> -->
  </div>

  <div class="card"><div class="card-body">
    <table id="table_pos_paid" class="table table-striped table-bordered w-100">
      <thead>
        <tr>
          <th width="6%">No.</th>
          <th>Nomor</th>
          <th>Mode</th>
          <th>Meja / Nama</th>
          <th>Pembayaran Diterima</th>
          <th>Subtotal</th>
          <th>Grand Total</th>
          <th>Metode</th>
        </tr>
      </thead>
    </table>
  </div></div>
</div>

<!-- Modal Detail -->
<div id="pos-paid-detail-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="mymodal-title">Detail Riwayat</h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      </div>
      <div class="modal-body" id="detail-body"><div class="text-center text-muted py-5">Memuat…</div></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
var table=null, isReloading=false;
function loader(){ if (window.Swal) Swal.fire({title:"Proses...", allowOutsideClick:false, didOpen:()=>Swal.showLoading()}); }
function close_loader(){ if (window.Swal) Swal.close(); }

function reload_pos_paid(reason='user'){
  if (!table || isReloading) return;
  isReloading = true;
  table.ajax.reload(function(){ isReloading=false; }, false);
}

$(document).ready(function(){
  table = $('#table_pos_paid').DataTable({
    pageLength: 10,
    lengthMenu: [[10,25,100],[10,25,100]],
    oLanguage:{
      sProcessing:"Memuat Data...",
      sSearch:"<i class='ti-search'></i> Cari :",
      sZeroRecords:"Data tidak ditemukan",
      sLengthMenu:"Tampil _MENU_ data",
      sEmptyTable:"Belum ada data",
      sInfo:"Menampilkan _START_ - _END_ dari _TOTAL_ data",
      sInfoEmpty:"Tidak ada data",
      sInfoFiltered:"(disaring dari _MAX_ total)",
      oPaginate:{ sNext:"<i class='fe-chevrons-right'></i>", sPrevious:"<i class='fe-chevrons-left'></i>"}
    },
    processing:true, serverSide:true, scrollX:true,
    ajax:{
      url:"<?= site_url('admin_pos_riwayat/get_data') ?>",
      type:"POST",
      data:function(d){
        d.metode = $('#filter-metode').val() || 'all';
        d.mode   = $('#filter-mode').val()   || 'all';
      }
    },
    columns:[
      {data:"no",        orderable:false},
      {data:"nomor"},
      {data:"mode"},
      {data:"meja"},
      {data:"archived_at"},
      {data:"subtotal"},
      {data:"grand"},
      {data:"metode"}
    ],
    order: [],
    rowCallback:function(row, data, displayIndex){
      var api  = this.api();
      var info = api.page.info();
      var idx  = info.start + displayIndex + 1;
      $('td:eq(0)', row).html(idx);
    },
    createdRow:function(row,data){
      if (data && data.id){
        $(row).attr('data-id', data.id).addClass('row-link').css('cursor','pointer');
      }
    }
  });

  // klik baris => detail
  $('#table_pos_paid tbody').on('click','tr', function(e){
    // tidak ada tombol aksi sekarang, tapi tetap guard kalau ada link lain
    if ($(e.target).closest('a,i,button').length) return;
    const id = parseInt($(this).attr('data-id')||'0',10);
    if (id>0){ show_detail(id); }
  });

  $('#filter-metode,#filter-mode').on('change', function(){ reload_pos_paid('user'); });
});

/* ========= Detail ========= */
function show_detail(id){
  loader();
  $.getJSON("<?= site_url('admin_pos_riwayat/detail/') ?>"+id)
    .done(function(r){
      close_loader();
      if (!r.success){ Swal.fire(r.title||'Gagal', r.pesan||'Tidak bisa memuat detail', 'error'); return; }
      $('.mymodal-title').text(r.title || 'Detail');
      $('#detail-body').html(r.html || '-');
      $('#pos-paid-detail-modal').modal('show');
    })
    .fail(function(){ close_loader(); Swal.fire("Error","Gagal mengambil detail","error"); });
}
</script>
