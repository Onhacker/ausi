<link href="<?= base_url('assets/admin/datatables/css/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet" type="text/css"/>
<link href="<?= base_url('assets/min/thead.min.css'); ?>" rel="stylesheet" type="text/css"/>

<div class="container-fluid">
 


  <div class="card"><div class="card-body">
    <h4 class="header-title">POS Billiard</h4>
    <!-- Toolbar -->
          <div class="row mb-2">
            <div class="col-12">
              <form class="form-inline">
                <!-- Refresh -->
             <a href="<?= site_url('billiard') ?>" class="btn btn-blue btn-sm mb-2 mr-2">
              <span class="btn-label"><i class="fe-plus-circle"></i></span>Booking
            </a>
            <button type="button" onclick="reload_billiard_table('user')" class="btn btn-warning mb-2 btn-sm mr-2">
              <span class="btn-label"><i class="fe-refresh-ccw"></i></span>Refresh
            </button>
           
            <!-- <small id="rs-selected" class="text-muted mb-2">ID terpilih: - (klik baris tabel untuk memilih)</small> -->

                  <!-- Filter status (custom-select bawaan template) -->
                  <div class="form-group mb-2 mr-2">
                    <label for="filter-status" class="sr-only">Status</label>
                    <select id="filter-status" class="form-control form-control-sm" style="width:240px">
                      <option value="all" selected>Semua status</option>
                      <option value="draft">Draft</option>
                      <option value="menunggu_bayar">Menunggu Bayar</option>
                      <option value="verifikasi">Verifikasi</option>
                      <option value="terkonfirmasi">Terkonfirmasi</option>
                      <option value="batal">Batal</option>
                      <option value="free">Free</option>
                    </select>
                  </div>
           
              </form>
            </div>
          </div>
          <!-- /Toolbar -->
    <table id="table_billiard" class="table table-striped table-bordered w-100 dt-head-flow">
      <thead>
        <tr>
          <th width="3%">No.</th>
          <th>Kode Book</th>
          <th>Meja / Nama</th>
          <th>Waktu Booking</th>
          <th>Waktu Main</th>
          <!-- <th>Durasi</th> -->
          <th>Harga/Jam</th>
          <th>Grand Total</th>
          <th>Status</th>
          <th>Metode</th>
          <th width="14%">Aksi</th>
        </tr>
      </thead>
    </table>
  </div></div>
</div>

<!-- Modal Detail -->
<div id="bil-detail-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="mymodal-title">Detail Booking</h4>
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
var table;
var isReloading = false;
window.selectedId = 0; // id booking yang terpilih via klik baris

function loader(){ if (window.Swal) Swal.fire({title:"Proses...", allowOutsideClick:false, didOpen:()=>Swal.showLoading()}); }
function close_loader(){ if (window.Swal) Swal.close(); }

let reloadReason = 'init';
function reload_billiard_table(reason='user'){
  reloadReason = reason;
  if (!table || isReloading) return;
  isReloading = true;
  table.ajax.reload(function(){ isReloading = false; }, false);
}


$(document).ready(function(){
  table = $('#table_billiard').DataTable({
    pageLength: 10,
    lengthMenu: [[10,25,100], [10,25,100]],
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
      url:"<?= site_url('admin_billiard/get_data') ?>",
      type:"POST",
      data:function(d){ d.status = $('#filter-status').val() || 'all'; }
    },
    columns:[
      {data:"no",     orderable:false},
      {data:"kode"},
      {data:"meja"},
      
      {data:"durasi"},
      {data:"waktu"},
      {data:"harga"},
      {data:"grand"},
      {data:"status", orderable:true},
      {data:"metode"},
      {data:"aksi",   orderable:false}
    ],
    order: [],
    rowCallback:function(row, data, displayIndex){
      // API modern (works on DataTables 1.10+ dan 2.x)
      var api  = this.api();
      var info = api.page.info();            // { start, end, length, page, pages, recordsTotal, recordsDisplay }
      var idx  = info.start + displayIndex + 1;
      $('td:eq(0)', row).html(idx);
    },

    createdRow: function(row, data){
      if (data && data.id){
        $(row).attr('data-id', data.id).addClass('row-link').css('cursor','pointer');
      }
    }
  });

  // Row click → detail (kecuali klik tombol)
$('#table_billiard').on('click', 'tbody tr', function(e){
  if ($(e.target).closest('button, a, i').length) return; // jangan ganggu tombol
  const id = parseInt($(this).attr('data-id')||'0', 10);
  if (id > 0){ show_detail(id); }
});

  // Filter berubah → reload
  $('#filter-status').on('change', function(){ reload_billiard_table('user'); });

  // Mulai ping 3 detik
  // setInterval(pingServerForChanges, 3000);
});

/* ================== AKSI PER-BARIS ================== */
function show_detail(id){
  loader();
  $.getJSON("<?= site_url('admin_billiard/detail/') ?>"+id)
    .done(function(r){
      close_loader();
      if (!r.success){ Swal.fire(r.title||'Gagal', r.pesan||'Tidak bisa memuat detail', 'error'); return; }
      $('.mymodal-title').text(r.title || 'Detail');
      $('#detail-body').html(r.html || '-');
      $('#bil-detail-modal').modal('show');
    })
    .fail(function(){ close_loader(); Swal.fire("Error","Gagal mengambil detail","error"); });
}
$(document).on('click', '.btn-apply-voucher', function(){
  const id = parseInt($(this).data('id')||0, 10);
  const code = String($(this).closest('.card-body').find('.dv-voucher-code').val()||'')
                .trim().toUpperCase();

  if (!id) return;
  if (!code){ Swal.fire('Validasi','Kode voucher wajib diisi.','warning'); return; }

  loader();
  $.post("<?= site_url('admin_billiard/apply_voucher') ?>", { id:id, voucher:code })
    .done(function(resp){
      close_loader();
      let r=resp; if (typeof resp==='string'){ try{ r=JSON.parse(resp);}catch(e){} }
      if (!r || r.success!==true){ Swal.fire(r?.title||'Gagal', r?.pesan||'Voucher ditolak.', 'error'); return; }
      Swal.fire(r.title||'Berhasil', r.pesan||'Voucher diterapkan.', 'success');
      reload_billiard_table('user');
      show_detail(id); // refresh modal biar angka ikut update
    })
    .fail(function(){ close_loader(); Swal.fire('Error','Koneksi bermasalah / error 500','error'); });
});

function mark_paid_one(el){
  const id   = parseInt($(el).data('id')||0,10);
  const nama = ($(el).data('nama')||'-');
  const meja = ($(el).data('meja')||'-');
  Swal.fire({
    title: 'Lunasi pembayaran',
    text: `${nama} — ${meja} (#${id})`,
    icon: 'question',
    showCancelButton: true
  }).then(res=>{
    if (!res.isConfirmed) return;
    loader();
    $.post("<?= site_url('admin_billiard/mark_paid') ?>", {id:[id]})
      .done(function(resp){
        close_loader();
        let r=resp; if (typeof resp==='string'){ try{ r=JSON.parse(resp);}catch(e){} }
        if (!r.success){ Swal.fire(r.title||'Gagal', r.pesan||'Sebagian gagal', 'error'); }
        else { Swal.fire(r.title, r.pesan, 'success'); reload_billiard_table('user'); }
      }).fail(function(){ close_loader(); Swal.fire('Error','Koneksi bermasalah / error 500','error'); });
  });
}

function mark_canceled_one(el){
  const id   = parseInt($(el).data('id')||0,10);
  const nama = ($(el).data('nama')||'-');
  const meja = ($(el).data('meja')||'-');
  Swal.fire({
    title: 'Batalkan booking?',
    text: `${nama} — ${meja} (#${id})`,
    icon: 'warning', showCancelButton:true, confirmButtonColor:"#d33"
  }).then(res=>{
    if (!res.isConfirmed) return;
    loader();
    $.post("<?= site_url('admin_billiard/mark_canceled') ?>", {id:[id]})
      .done(function(resp){
        close_loader();
        let r=resp; if (typeof resp==='string'){ try{ r=JSON.parse(resp);}catch(e){} }
        if (!r.success){ Swal.fire(r.title||'Gagal', r.pesan||'Sebagian gagal', 'error'); }
        else { Swal.fire(r.title, r.pesan, 'success'); reload_billiard_table('user'); }
      }).fail(function(){ close_loader(); Swal.fire('Error','Koneksi bermasalah / error 500','error'); });
  });
}

function hapus_data_one(el){
  const id   = parseInt($(el).data('id')||0,10);
  const nama = ($(el).data('nama')||'-');
  const meja = ($(el).data('meja')||'-');
  Swal.fire({
    title: 'Hapus data?',
    text: `${nama} — ${meja} (#${id})`,
    icon: 'warning', showCancelButton:true, confirmButtonColor:"#d33"
  }).then(res=>{
    if (!res.isConfirmed) return;
    loader();
    $.post("<?= site_url('admin_billiard/hapus_data') ?>", {id:[id]})
      .done(function(resp){
        close_loader();
        let r=resp; if (typeof resp==='string'){ try{ r=JSON.parse(resp);}catch(e){} }
        if (!r.success){ Swal.fire(r.title||'Gagal', r.pesan||'Sebagian gagal', 'error'); }
        else { Swal.fire(r.title, r.pesan, 'success'); reload_billiard_table('user'); }
      }).fail(function(){ close_loader(); Swal.fire('Error','Koneksi bermasalah / error 500','error'); });
  });
}

$('#table_billiard').on('click', '.btn-reschedule', function(e){
  e.stopPropagation();
  const $b   = $(this);
  const id   = parseInt($b.data('id')||0,10);
  const tgl  = String($b.data('tanggal')||'').slice(0,10);
  const jam  = String($b.data('jam_mulai')||'').slice(0,5);
  const nama = ($b.data('nama')||'-');
  const meja = ($b.data('meja')||'-');

  // kecil-kecilan: escape untuk jaga-jaga
  const esc = s => String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));

  Swal.fire({
    title: 'Reschedule',
    // Pindahkan info ke HTML supaya tampil bareng input
    html:
      '<div class="mb-2"><b>'+esc(nama)+'</b> — '+esc(meja)+' <span class="text-muted">#'+id+'</span></div>' +
      '<div class="d-flex align-items-center justify-content-center" style="gap:8px;flex-wrap:wrap;margin-top:8px">' +
        '<input type="date" id="rsw-tgl" class="swal2-input" style="width:auto" value="'+(tgl||'')+'">' +
        '<input type="time" id="rsw-jam" class="swal2-input" style="width:auto" step="300" value="'+(jam||'')+'">' +
      '</div>',
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: 'Simpan',
    cancelButtonText: 'Batal',
    preConfirm: ()=>{
      const ntgl = ($('#rsw-tgl').val()||'').trim();
      const njam = ($('#rsw-jam').val()||'').trim();
      if (!ntgl || !njam){
        Swal.showValidationMessage('Tanggal & Jam mulai wajib diisi');
        return false;
      }
      return {tanggal: ntgl, jam: njam};
    }
  }).then(res=>{
    if (!res.isConfirmed) return;
    loader();
    $.post("<?= site_url('admin_billiard/reschedule') ?>", {
      id: id, tanggal: res.value.tanggal, jam_mulai: res.value.jam
    })
    .done(function(resp){
      close_loader();
      let r=resp; if (typeof resp==='string'){ try{ r=JSON.parse(resp);}catch(e){} }
      if (!r || r.success!==true){
        Swal.fire(r?.title||'Gagal', r?.pesan||'Reschedule ditolak.', 'error');
        return;
      }
      Swal.fire(r.title, r.pesan, 'success');
      reload_billiard_table('user');
    })
    .fail(function(){
      close_loader();
      Swal.fire('Error','Koneksi bermasalah / error 500','error');
    });
  });
});

</script>
