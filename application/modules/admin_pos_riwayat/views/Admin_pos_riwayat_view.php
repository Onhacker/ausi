<link href="<?= base_url('assets/admin/datatables/css/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet" type="text/css"/>

<div class="container-fluid">
  <div class="row">
 <!--    <div class="col-12">
    <div class="page-title-box">
      <div class="page-title-right">
        <ol class="breadcrumb m-0">
          <li class="breadcrumb-item active"><?= $subtitle; ?></li>
        </ol>
      </div>
      <h4 class="page-title"><?= $title; ?></h4>
    </div>
  </div> -->
</div>

  <!-- ===== FILTER BARU ===== -->
  <div class="card">
    <div class="card-body">
       <h4 class="header-title"><?= $title; ?></h4>
      <div class="form-row">
        <div class="form-group col-md-2">
          <label class="mb-1">Periode</label>
          <select id="preset" class="form-control form-control-sm">
            <option value="today">Hari ini</option>
            <option value="yesterday">Kemarin</option>
            <option value="this_week">Minggu ini</option>
            <option value="this_month">Bulan ini</option>
            <option value="range">Rentang Tanggal</option>
          </select>
        </div>

        <div class="form-group col-md-3">
          <label class="mb-1" for="dt_from">Dari (Tanggal & Jam)</label>
          <input type="datetime-local" id="dt_from" class="form-control form-control-sm">
        </div>

        <div class="form-group col-md-3">
          <label class="mb-1" for="dt_to">Sampai (Tanggal & Jam)</label>
          <input type="datetime-local" id="dt_to" class="form-control form-control-sm">
        </div>

        <div class="form-group col-md-2">
          <label class="mb-1">Metode Pembayaran</label>
          <select id="metode" class="form-control form-control-sm">
            <option value="all">Semua</option>
            <option value="cash">Cash</option>
            <option value="qris">QRIS</option>
            <option value="transfer">Transfer</option>
          </select>
        </div>

        <div class="form-group col-md-2">
          <label class="mb-1">Mode</label>
          <select id="mode" class="form-control form-control-sm">
            <option value="all">Semua</option>
            <option value="walkin">Walk-in</option>
            <option value="dinein">Dine-in</option>
            <option value="delivery">Delivery</option>
          </select>
        </div>
<div class="form-group col-12 d-flex flex-wrap align-items-center justify-content-end btn-wrap">
  <button type="button" class="btn btn-primary btn-sm mb-2 mr-2" id="btn-apply">
    <span class="btn-label"><i class="fe-filter"></i></span> Lihat
  </button>

  <button type="button" class="btn btn-secondary btn-sm mb-2 mr-2" id="btn-reset">
    <i class="fe-rotate-ccw"></i> Reset Form
  </button>

  <button type="button" onclick="reload_pos_paid('user')" class="btn btn-warning mb-2 btn-sm">
    <span class="btn-label"><i class="fe-refresh-ccw"></i></span> Refresh Data
  </button>
</div>

      </div>
    </div>
  </div>
  <!-- ===== /FILTER BARU ===== -->

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

// ==== util tanggal lokal → input datetime-local & SQL ====
function pad(n){ return (n<10?'0':'')+n; }
function toInputValue(d){ // Date -> "YYYY-MM-DDTHH:mm"
  return d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate())+'T'+pad(d.getHours())+':'+pad(d.getMinutes());
}
function toSql(val, isEnd){ // "YYYY-MM-DDTHH:mm" -> "YYYY-MM-DD HH:mm:ss"
  if(!val) return '';
  var t = val.replace('T',' ');
  return t + (t.length===16 ? (isEnd ? ':59' : ':00') : '');
}
function startOfToday(){ var n=new Date(); return new Date(n.getFullYear(), n.getMonth(), n.getDate(), 0,0,0); }
function endOfToday(){ var n=new Date(); return new Date(n.getFullYear(), n.getMonth(), n.getDate(), 23,59,59); }
function startOfYesterday(){ var n=new Date(); n.setDate(n.getDate()-1); return new Date(n.getFullYear(), n.getMonth(), n.getDate(), 0,0,0); }
function endOfYesterday(){ var n=new Date(); n.setDate(n.getDate()-1); return new Date(n.getFullYear(), n.getMonth(), n.getDate(), 23,59,59); }
function startOfWeekMon(){ // minggu ini (mulai Senin)
  var n=new Date(); var day=n.getDay(); var diff = (day===0?-6:(1-day));
  var s=new Date(n.getFullYear(), n.getMonth(), n.getDate()+diff, 0,0,0);
  return s;
}
function startOfMonth(){ var n=new Date(); return new Date(n.getFullYear(), n.getMonth(), 1, 0,0,0); }

function applyPreset(){
  var p = $('#preset').val();
  var from, to;
  if (p==='today'){ from=startOfToday(); to=endOfToday(); }
  else if (p==='yesterday'){ from=startOfYesterday(); to=endOfYesterday(); }
  else if (p==='this_week'){ from=startOfWeekMon(); to=endOfToday(); }
  else if (p==='this_month'){ from=startOfMonth(); to=endOfToday(); }
  else { // 'range' → jangan sentuh nilai user
    return;
  }
  $('#dt_from').val(toInputValue(from));
  $('#dt_to').val(toInputValue(to));
}

$(document).ready(function(){
  // set default: Hari ini
  $('#preset').val('today');
  applyPreset();

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
        d.metode  = $('#metode').val() || 'all';
        d.mode    = $('#mode').val()   || 'all';
        d.dt_from = toSql($('#dt_from').val(), false);
        d.dt_to   = toSql($('#dt_to').val(), true);
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
    order: [], // biarkan model pakai default (archived_at DESC)
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
    if ($(e.target).closest('a,i,button').length) return;
    const id = parseInt($(this).attr('data-id')||'0',10);
    if (id>0){ show_detail(id); }
  });

  // events filter
  $('#preset').on('change', applyPreset);
  $('#btn-apply').on('click', function(){ reload_pos_paid('apply'); });
  $('#btn-reset').on('click', function(){
    $('#preset').val('today');
    applyPreset();
    $('#metode').val('all');
    $('#mode').val('all');
    reload_pos_paid('reset');
  });
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
