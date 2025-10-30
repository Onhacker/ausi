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
      sSearch:"<i class='ti-search'></i> Cari Kategori :",
      sZeroRecords:"Maaf Data Tidak Ditemukan",
      sLengthMenu:"Tampil _MENU_ Data",
      sEmptyTable:"Data Tidak Ada",
      sInfo:"Menampilkan _START_ - _END_ dari _TOTAL_ Total Data",
      sInfoEmpty:"Tidak ada data ditampilkan",
      sInfoFiltered:"(Filter dari _MAX_ total Data)",
      oPaginate:{ sNext:"<i class='fe-chevrons-right'></i>", sPrevious:"<i class='fe-chevrons-left'></i>"}
    },
    processing:true, serverSide:true, scrollX:true,
    ajax:{ url:"<?= site_url('admin_kategori_produk/get_dataa') ?>", type:"POST" },
    columns:[
      {data:"cek", orderable:false},
      {data:"no",  orderable:false},
      {data:"nama"},
      {data:"slug"},
      {data:"aktif", orderable:false},
      {data:"aksi", orderable:false}
    ],
    order: [],
    rowCallback:function(row, data, iDisplayIndex){
      var info = this.fnPagingInfo();
      var idx  = info.iPage * info.iLength + (iDisplayIndex + 1);
      $('td:eq(1)', row).html(idx);
    }
  });

  $("#check-all").on('click', function(){ $(".data-check").prop('checked', $(this).prop('checked')); });
});

function add(){
  save_method = 'add';
  $('#form_app')[0].reset();
  $('#id').val('');
  $('.mymodal-title').text('Tambah Kategori');
  $('#full-width-modal').modal('show');
}

function edit(id=null){
  let targetId = id;
  if (!targetId){
    const list_id = [];
    $(".data-check:checked").each(function(){ list_id.push(this.value); });
    if (list_id.length !== 1){ Swal.fire("Info","Pilih satu data untuk diedit.","warning"); return; }
    targetId = list_id[0];
  }
  save_method='update';
  loader();
  $.getJSON("<?= site_url('admin_kategori_produk/get_one/') ?>"+targetId)
    .done(function(r){
      close_loader();
      if (!r.success){ Swal.fire(r.title||'Gagal', r.pesan||'Tidak bisa mengambil data', 'error'); return; }
      const d = r.data;
      $('#id').val(d.id);
      $('#nama').val(d.nama);
      $('#deskripsi').val(d.deskripsi);
      $('#is_active').prop('checked', d.is_active == 1);
      $('.mymodal-title').html('Edit Kategori <code>#'+d.id+'</code>');
      $('#full-width-modal').modal('show');
    }).fail(function(){ close_loader(); Swal.fire("Error","Gagal mengambil data","error"); });
}

function simpan(){
  const url = (save_method === 'add') ? "<?= site_url('admin_kategori_produk/add') ?>" : "<?= site_url('admin_kategori_produk/update') ?>";
  const fd = new FormData(document.getElementById('form_app'));
  // checkbox
  fd.set('is_active', $('#is_active').is(':checked') ? '1' : '0');
  loader();
  $.ajax({ url:url, type:'POST', data:fd, processData:false, contentType:false, dataType:'json' })
    .done(function(r){
      close_loader();
      if (!r.success){ Swal.fire(r.title||'Gagal', r.pesan||'Terjadi kesalahan', 'error'); return; }
      Swal.fire(r.title, r.pesan, 'success');
      $('#full-width-modal').modal('hide');
      reload_table();
    }).fail(function(){ close_loader(); Swal.fire('Gagal','Tidak dapat mengirim data','error'); });
}

function hapus_data(){
  const list_id = [];
  $(".data-check:checked").each(function(){ list_id.push(this.value); });
  if (list_id.length === 0){ Swal.fire("Info","Pilih minimal satu data","warning"); return; }
  Swal.fire({ title:"Hapus "+list_id.length+" data?", icon:"warning", showCancelButton:true, confirmButtonColor:"#d33", cancelButtonColor:"#3085d6", confirmButtonText:"Ya, Hapus", cancelButtonText:"Batal", allowOutsideClick:false })
    .then((res)=>{
      if (!res.isConfirmed) return;
      loader();
      $.ajax({ url:"<?= site_url('admin_kategori_produk/hapus_data') ?>", type:"POST", data:{id:list_id}, dataType:"json" })
        .done(function(r){ close_loader(); if (!r.success){ Swal.fire(r.title||'Gagal', r.pesan||'Sebagian gagal dihapus', 'error'); } else { Swal.fire(r.title, r.pesan, 'success'); reload_table(); } })
        .fail(function(){ close_loader(); Swal.fire("Gagal","Koneksi bermasalah","error"); });
    });
}

function close_modal(){
  Swal.fire({ title:"Tutup formulir?", text:"Perubahan yang belum disimpan akan hilang.", icon:"warning", showCancelButton:true, confirmButtonText:"Tutup", cancelButtonText:"Batal" })
    .then(r=>{ if(r.value) $('#full-width-modal').modal('hide'); });
}
/* ==================== SUBKATEGORI ==================== */
var tableSub, sub_save_method='add';

function sub_loader(){ loader(); }
function sub_close_loader(){ close_loader(); }
function sub_reload(){ tableSub.ajax.reload(null,false); }

function isi_dropdown_kategori(targetSel, withAll){
  $.getJSON("<?= site_url('admin_kategori_produk/list_kategori') ?>")
    .done(function(rows){
      var $el = $(targetSel); $el.empty();
      if (withAll) $el.append('<option value="">Semua</option>');
      rows.forEach(function(r){ $el.append('<option value="'+r.id+'">'+r.nama+'</option>'); });
    });
}

$(document).ready(function(){
  // init dropdown filter + dropdown modal
  isi_dropdown_kategori('#sub-filter-kategori', true);
  isi_dropdown_kategori('#sub_kategori_id', false);

  // DataTables Subkategori
  $.fn.dataTableExt.oApi.fnPagingInfo = $.fn.dataTableExt.oApi.fnPagingInfo || function(o){/* sudah didefinisikan di atas */};

  tableSub = $('#datatable_sub').DataTable({
    lengthMenu: [[10,25,50,100,-1],[10,25,50,100,'All']],
    autoWidth: false,
    scrollCollapse: true,
    oLanguage:{
      sProcessing:"Memuat Data...",
      sSearch:"<i class='ti-search'></i> Cari Subkategori :",
      sZeroRecords:"Maaf Data Tidak Ditemukan",
      sLengthMenu:"Tampil _MENU_ Data",
      sEmptyTable:"Data Tidak Ada",
      sInfo:"Menampilkan _START_ - _END_ dari _TOTAL_ Total Data",
      sInfoEmpty:"Tidak ada data ditampilkan",
      sInfoFiltered:"(Filter dari _MAX_ total Data)",
      oPaginate:{ sNext:"<i class='fe-chevrons-right'></i>", sPrevious:"<i class='fe-chevrons-left'></i>"}
    },
    processing:true, serverSide:true, scrollX:true,
    ajax:{
      url:"<?= site_url('admin_kategori_produk/sub_get_data') ?>",
      type:"POST",
      data:function(d){ d.kategori_id = $('#sub-filter-kategori').val(); }
    },
    columns:[
      {data:"cek", orderable:false},
      {data:"no",  orderable:false},
      {data:"kategori"},
      {data:"nama"},
      {data:"slug"},
      {data:"aktif", orderable:false},
      {data:"aksi", orderable:false}
    ],
    order: [],
    rowCallback:function(row, data, iDisplayIndex){
      var info = this.fnPagingInfo();
      var idx  = info.iPage * info.iLength + (iDisplayIndex + 1);
      $('td:eq(1)', row).html(idx);
    }
  });

  $("#sub-check-all").on('click', function(){ $(".sub-check").prop('checked', $(this).prop('checked')); });
  $("#sub-filter-kategori").on('change', function(){ sub_reload(); });
});

function sub_add(){
  sub_save_method='add';
  $('#form_sub')[0].reset();
  $('#sub_id').val('');
  $('#sub_is_active').prop('checked', true);
  $('.mymodal-sub-title').text('Tambah Subkategori');
  isi_dropdown_kategori('#sub_kategori_id', false);
  $('#modal-sub').modal('show');
}

function sub_edit(id=null){
  let targetId = id;
  if (!targetId){
    const list_id = [];
    $(".sub-check:checked").each(function(){ list_id.push(this.value); });
    if (list_id.length !== 1){ Swal.fire("Info","Pilih satu data untuk diedit.","warning"); return; }
    targetId = list_id[0];
  }
  sub_save_method='update';
  sub_loader();
  $.getJSON("<?= site_url('admin_kategori_produk/sub_get_one/') ?>"+targetId)
    .done(function(r){
      sub_close_loader();
      if (!r.success){ Swal.fire(r.title||'Gagal', r.pesan||'Tidak bisa mengambil data', 'error'); return; }
      const d = r.data;
      $('#sub_id').val(d.id);
      isi_dropdown_kategori('#sub_kategori_id', false);
      setTimeout(function(){ $('#sub_kategori_id').val(d.kategori_id); }, 50);
      $('#sub_nama').val(d.nama);
      $('#sub_deskripsi').val(d.deskripsi);
      $('#sub_is_active').prop('checked', d.is_active == 1);
      $('.mymodal-sub-title').html('Edit Subkategori <code>#'+d.id+'</code>');
      $('#modal-sub').modal('show');
    }).fail(function(){ sub_close_loader(); Swal.fire("Error","Gagal mengambil data","error"); });
}

function sub_simpan(){
  const url = (sub_save_method === 'add') ? "<?= site_url('admin_kategori_produk/sub_add') ?>" : "<?= site_url('admin_kategori_produk/sub_update') ?>";
  const fd = new FormData(document.getElementById('form_sub'));
  fd.set('is_active', $('#sub_is_active').is(':checked') ? '1' : '0');
  sub_loader();
  $.ajax({ url:url, type:'POST', data:fd, processData:false, contentType:false, dataType:'json' })
    .done(function(r){
      sub_close_loader();
      if (!r.success){ Swal.fire(r.title||'Gagal', r.pesan||'Terjadi kesalahan', 'error'); return; }
      Swal.fire(r.title, r.pesan, 'success');
      $('#modal-sub').modal('hide');
      sub_reload();
    }).fail(function(){ sub_close_loader(); Swal.fire('Gagal','Tidak dapat mengirim data','error'); });
}

function sub_hapus(){
  const list_id = [];
  $(".sub-check:checked").each(function(){ list_id.push(this.value); });
  if (list_id.length === 0){ Swal.fire("Info","Pilih minimal satu data","warning"); return; }
  Swal.fire({ title:"Hapus "+list_id.length+" subkategori?", icon:"warning", showCancelButton:true, confirmButtonColor:"#d33", cancelButtonColor:"#3085d6", confirmButtonText:"Ya, Hapus", cancelButtonText:"Batal", allowOutsideClick:false })
    .then((res)=>{
      if (!res.isConfirmed) return;
      sub_loader();
      $.ajax({ url:"<?= site_url('admin_kategori_produk/sub_hapus') ?>", type:"POST", data:{id:list_id}, dataType:"json" })
        .done(function(r){ sub_close_loader(); if (!r.success){ Swal.fire(r.title||'Gagal', r.pesan||'Sebagian gagal dihapus', 'error'); } else { Swal.fire(r.title, r.pesan, 'success'); sub_reload(); } })
        .fail(function(){ sub_close_loader(); Swal.fire("Gagal","Koneksi bermasalah","error"); });
    });
}

function sub_close(){
  Swal.fire({ title:"Tutup formulir?", text:"Perubahan yang belum disimpan akan hilang.", icon:"warning", showCancelButton:true, confirmButtonText:"Tutup", cancelButtonText:"Batal" })
    .then(r=>{ if(r.value) $('#modal-sub').modal('hide'); });
}

// Ketika pindah tab, rapikan lebar kolom semua DataTable yang terlihat
$('a[data-toggle="tab"]').on('shown.bs.tab', function () {
  $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
});

// Pastikan juga setelah pertama kali menampilkan tab subkategori
$('a[href="#tab-subkategori"]').on('shown.bs.tab', function(){
  setTimeout(function(){
    if (tableSub) tableSub.columns.adjust().draw(false);
  }, 50);
});

// Saat window di-resize
$(window).on('resize', function(){
  clearTimeout(window.__dtAdj);
  window.__dtAdj = setTimeout(function(){
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
  }, 80);
});
$('a[data-toggle="tab"]').on('shown.bs.tab', function () {
  $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().draw(false);
});

$(window).on('load resize', function(){
  clearTimeout(window.__dtAdj);
  window.__dtAdj = setTimeout(function(){
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().draw(false);
  }, 80);
});

</script>
