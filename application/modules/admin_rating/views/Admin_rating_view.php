<link href="<?= base_url('assets/admin/datatables/css/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet" type="text/css"/>

<div class="container-fluid">
  <div class="row">
    <!-- <div class="col-12">
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

  <!-- ===== FILTER ===== -->
  <div class="card mb-3">
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
          <label class="mb-1">Bintang</label>
          <select id="stars" class="form-control form-control-sm">
            <option value="all">Semua</option>
            <option value="5">5 ⭐</option>
            <option value="4">4 ⭐</option>
            <option value="3">3 ⭐</option>
            <option value="2">2 ⭐</option>
            <option value="1">1 ⭐</option>
            <option value="0">0 ⭐</option>
          </select>
        </div>

        <div class="form-group col-md-2">
          <label class="mb-1">Status Ulasan</label>
          <select id="has_review" class="form-control form-control-sm">
            <option value="all">Semua</option>
            <option value="with">Hanya yang ada ulasan</option>
            <option value="without">Tanpa ulasan</option>
          </select>
        </div>

        <div class="form-group col-12 d-flex flex-wrap align-items-center justify-content-end btn-wrap">
          <button type="button" class="btn btn-primary btn-sm mb-2 mr-2" id="btn-apply">
            <span class="btn-label"><i class="fe-filter"></i></span> Lihat
          </button>
          <button type="button" class="btn btn-secondary btn-sm mb-2 mr-2" id="btn-reset">
            <i class="fe-rotate-ccw"></i> Reset Form
          </button>
          <button type="button" onclick="reload_rating('user')" class="btn btn-warning btn-sm mb-2">
            <span class="btn-label"><i class="fe-refresh-ccw"></i></span> Refresh Data
          </button>
        </div>
      </div>
      <div class="text-muted small">Catatan: Periode menggunakan <code>review_at</code> jika ada, jika kosong akan memakai <code>created_at</code>.</div>
    </div>
  </div>
  <!-- ===== /FILTER ===== -->

  <div class="card"><div class="card-body">
    <table id="table_rating" class="table table-striped table-bordered w-100">
      <thead>
        <tr>
          <th width="6%">No.</th>
          <th>Produk</th>
          <th>Bintang</th>
          <th>Nama / Token</th>
          <th>Ulasan</th>
          <th>Ditulis</th>
          <th>Dibuat</th>
          <th width="110">Aksi</th>
        </tr>
      </thead>
    </table>
  </div></div>
</div>

<!-- Modal Edit -->
<div id="rating-edit-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="mymodal-title">Edit Rating</h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      </div>
      <div class="modal-body">
        <form id="form-edit">
          <input type="hidden" name="id" id="f_id">
          <div class="form-group">
            <label>Produk</label>
            <input type="text" class="form-control" id="f_produk" disabled>
          </div>
          <div class="form-group">
            <label>Nama (opsional)</label>
            <input type="text" class="form-control" name="nama" id="f_nama" maxlength="60" placeholder="Nama pelanggan (maks 60)">
          </div>
          <div class="form-group">
            <label>Bintang</label>
            <select class="form-control" name="stars" id="f_stars">
              <option value="5">5</option><option value="4">4</option>
              <option value="3">3</option><option value="2">2</option>
              <option value="1">1</option><option value="0">0</option>
            </select>
          </div>
          <div class="form-group">
            <label>Ulasan (opsional)</label>
            <textarea class="form-control" name="review" id="f_review" rows="4" placeholder="Teks ulasan"></textarea>
          </div>
          <div class="form-group">
            <label>Waktu Ditulis (review_at) — kosongkan bila ingin null</label>
            <input type="text" class="form-control" name="review_at" id="f_review_at" placeholder="YYYY-MM-DD HH:MM:SS">
            <small class="text-muted">Jika dikosongkan, sistem akan pakai <code>NULL</code> dan periode akan fallback ke <code>created_at</code>.</small>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btn-save-edit">
          <i class="fe-save"></i> Simpan
        </button>
      </div>
    </div>
  </div>
</div>

<script>
var table=null, isReloading=false;

/* ==== CSRF util (ikut pola kamu) ==== */
var CSRF = <?php
  if ($this->config->item('csrf_protection')) {
    echo json_encode([
      'name' => $this->security->get_csrf_token_name(),
      'hash' => $this->security->get_csrf_hash()
    ]);
  } else {
    echo 'null';
  }
?>;

function postJSON(url, data){
  return fetch(url, {
    method: 'POST',
    headers: {'X-Requested-With': 'XMLHttpRequest'},
    body: (function(){
      var fd = new FormData();
      for (var k in data){ fd.append(k, data[k]); }
      if (CSRF) fd.append(CSRF.name, CSRF.hash);
      return fd;
    })()
  }).then(function(r){ return r.json(); });
}

function loader(){ if (window.Swal) Swal.fire({title:"Proses...", allowOutsideClick:false, didOpen:()=>Swal.showLoading()}); }
function close_loader(){ if (window.Swal) Swal.close(); }

function reload_rating(reason='user'){
  if (!table || isReloading) return;
  isReloading = true;
  table.ajax.reload(function(){ isReloading=false; }, false);
}

// ==== tanggal helper (sama pola) ====
function pad(n){ return (n<10?'0':'')+n; }
function toInputValue(d){ return d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate())+'T'+pad(d.getHours())+':'+pad(d.getMinutes()); }
function toSql(val, isEnd){ if(!val) return ''; var t = val.replace('T',' '); return t + (t.length===16 ? (isEnd ? ':59' : ':00') : ''); }
function startOfToday(){ var n=new Date(); return new Date(n.getFullYear(), n.getMonth(), n.getDate(), 0,0,0); }
function endOfToday(){ var n=new Date(); return new Date(n.getFullYear(), n.getMonth(), n.getDate(), 23,59,59); }
function startOfYesterday(){ var n=new Date(); n.setDate(n.getDate()-1); return new Date(n.getFullYear(), n.getMonth(), n.getDate(), 0,0,0); }
function endOfYesterday(){ var n=new Date(); n.setDate(n.getDate()-1); return new Date(n.getFullYear(), n.getMonth(), n.getDate(), 23,59,59); }
function startOfWeekMon(){ var n=new Date(); var day=n.getDay(); var diff=(day===0?-6:(1-day)); return new Date(n.getFullYear(), n.getMonth(), n.getDate()+diff, 0,0,0); }
function startOfMonth(){ var n=new Date(); return new Date(n.getFullYear(), n.getMonth(), 1, 0,0,0); }

function applyPreset(){
  var p = $('#preset').val();
  var from, to;
  if (p==='today'){ from=startOfToday(); to=endOfToday(); }
  else if (p==='yesterday'){ from=startOfYesterday(); to=endOfYesterday(); }
  else if (p==='this_week'){ from=startOfWeekMon(); to=endOfToday(); }
  else if (p==='this_month'){ from=startOfMonth(); to=endOfToday(); }
  else if (p==='range'){
    $('#dt_from').val('');
    $('#dt_to').val('');
    return;
  } else { return; }
  $('#dt_from').val(toInputValue(from));
  $('#dt_to').val(toInputValue(to));
}

$(document).ready(function(){
  // default preset
  $('#preset').val('range'); applyPreset(); // akan mengosongkan dt_from/dt_to


  table = $('#table_rating').DataTable({
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
      url:"<?= site_url('admin_rating/get_data') ?>",
      type:"POST",
      data:function(d){
        d.stars      = $('#stars').val() || 'all';
        d.has_review = $('#has_review').val() || 'all';
        d.dt_from    = toSql($('#dt_from').val(), false);
        d.dt_to      = toSql($('#dt_to').val(), true);
        if (CSRF){ d[CSRF.name] = CSRF.hash; } // jaga2 untuk DataTables POST
      }
    },
    columns:[
      {data:"no", orderable:false},
      {data:"produk"},
      {data:"stars"},
      {data:"nama"},
      {data:"review"},
      {data:"review_at"},
      {data:"created_at"},
      {data:"aksi", orderable:false}
    ],
    order: [], // default dari model
    rowCallback:function(row, data, displayIndex){
      var api  = this.api();
      var info = api.page.info();
      var idx  = info.start + displayIndex + 1;
      $('td:eq(0)', row).html(idx);
    },
    createdRow:function(row,data){
      if (data && data.id){
        $(row).attr('data-id', data.id).addClass('row-link');
      }
    }
  });

  // events filter
  $('#preset').on('change', applyPreset);
  $('#btn-apply').on('click', function(){ reload_rating('apply'); });
  $('#btn-reset').on('click', function(){
  $('#preset').val('range'); applyPreset(); // kosongkan tanggal
  $('#stars').val('all');
  $('#has_review').val('all');
  reload_rating('reset');
});


  // simpan edit
  $('#btn-save-edit').on('click', save_edit);
});

/* ========= Edit / Delete ========= */
function open_edit(id){
  if (!id || isNaN(id)) { Swal.fire("Error","ID tidak valid","error"); return; }
  loader();
  $.getJSON("<?= site_url('admin_rating/get_one/') ?>"+id)
    .done(function(r){
      close_loader();
      if (!r.success){ Swal.fire(r.title||'Gagal', r.pesan||'Tidak bisa memuat data', 'error'); return; }
      var d = r.data || {};
      $('#f_id').val(d.id||'');
      $('#f_produk').val((d.produk_nama||('#'+d.produk_id)) + ' (ID: '+d.produk_id+')');
      $('#f_nama').val(d.nama||'');
      $('#f_stars').val(String(d.stars||0));
      $('#f_review').val(d.review||'');
      $('#f_review_at').val(d.review_at||'');
      $('.mymodal-title').text('Edit Rating #'+(d.id||''));
      $('#rating-edit-modal').modal('show');
    })
    .fail(function(){ close_loader(); Swal.fire("Error","Gagal mengambil data","error"); });
}

function save_edit(){
  var data = {
    id: $('#f_id').val(),
    nama: $('#f_nama').val(),
    stars: $('#f_stars').val(),
    review: $('#f_review').val(),
    review_at: $('#f_review_at').val()
  };
  if (CSRF){ data[CSRF.name] = CSRF.hash; }

  loader();
  postJSON("<?= site_url('admin_rating/save') ?>", data)
    .then(function(r){
      close_loader();
      if (!r || !r.success){ Swal.fire(r.title||'Gagal', r.pesan||'Tidak dapat menyimpan', 'error'); return; }
      Swal.fire(r.title||'Berhasil', r.pesan||'Tersimpan', 'success');
      $('#rating-edit-modal').modal('hide');
      reload_rating('save');
    })
    .catch(function(){ close_loader(); Swal.fire("Error","Gagal menyimpan","error"); });
}

function do_delete(id){
  Swal.fire({
    title: 'Hapus rating ini?',
    text: 'Tindakan ini tidak dapat dibatalkan.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, hapus',
    cancelButtonText: 'Batal'
  }).then(function(res){
    if (!res.isConfirmed) return;
    loader();
    postJSON("<?= site_url('admin_rating/delete/') ?>"+id, {})
      .then(function(r){
        close_loader();
        if (!r || !r.success){ Swal.fire(r.title||'Gagal', r.pesan||'Tidak dapat menghapus', 'error'); return; }
        Swal.fire(r.title||'Terhapus', r.pesan||'Berhasil dihapus','success');
        reload_rating('delete');
      })
      .catch(function(){ close_loader(); Swal.fire("Error","Gagal menghapus","error"); });
  });
}
</script>
