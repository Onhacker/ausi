

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

  // Summernote (Editor)
(function(){
  $(function(){
    $('#isi').summernote({
      height: 260,
      placeholder: 'Tulis pengumuman di sini...',
      toolbar: [
        ['style', ['style']],
        ['font', ['bold','italic','underline','clear']],
        ['para', ['ul','ol','paragraph']],
        ['insert', ['link','picture','table']],
        ['view', ['codeview','help']]
      ]
    });
  });
})();
  // DataTables
  $.fn.dataTableExt.oApi.fnPagingInfo = function(o){
    return {iStart:o._iDisplayStart, iEnd:o.fnDisplayEnd(), iLength:o._iDisplayLength, iTotal:o.fnRecordsTotal(), iFilteredTotal:o.fnRecordsDisplay(), iPage:Math.ceil(o._iDisplayStart/o._iDisplayLength), iTotalPages:Math.ceil(o.fnRecordsDisplay()/o._iDisplayLength)};
  };

  table = $('#datable_1').DataTable({
    lengthMenu: [[10,25,50,100,-1],[10,25,50,100,'All']],
    oLanguage:{
      sProcessing:"Memuat Data...",
      sSearch:"<i class='ti-search'></i> Cari Pengumuman :",
      sZeroRecords:"Maaf Data Tidak Ditemukan",
      sLengthMenu:"Tampil _MENU_ Data",
      sEmptyTable:"Data Tidak Ada",
      sInfo:"Menampilkan _START_ - _END_ dari _TOTAL_ Total Data",
      sInfoEmpty:"Tidak ada data ditampilkan",
      sInfoFiltered:"(Filter dari _MAX_ total Data)",
      oPaginate:{ sNext:"<i class='fe-chevrons-right'></i>", sPrevious:"<i class='fe-chevrons-left'></i>"}
    },
    processing:true,
    serverSide:true,
    scrollX:true,
    ajax:{ url:"<?= site_url('admin_pengumuman/get_dataa') ?>", type:"POST" },
    columns:[
      {data:"cek", orderable:false},
      {data:"no",  orderable:false},  // diisi rowCallback
      {data:"judul"},
      {data:"tanggal"},
      {data:"username"},
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
  // $('#isi').summernote('reset');
  $('#isi').summernote('code','');
  // default tanggal = hari ini
  const today = new Date().toISOString().slice(0,10);
  $('#tanggal').val(today);
  $('.mymodal-title').text('Tambah Pengumuman');
  $('#full-width-modal').modal('show');
}

function edit(id=null){
  let targetId = id;
  if (!targetId){
    const list_id = [];
    $(".data-check:checked").each(function(){ list_id.push(this.value); });
    if (list_id.length !== 1){
      Swal.fire("Info","Pilih satu data untuk diedit.","warning"); return;
    }
    targetId = list_id[0];
  }

  save_method='update';
  loader();
  $.getJSON("<?= site_url('admin_pengumuman/get_one/') ?>"+targetId)
    .done(function(r){
      close_loader();
      if (!r.success){ Swal.fire(r.title||'Gagal', r.pesan||'Tidak bisa mengambil data', 'error'); return; }
      const d = r.data;
      $('#id').val(d.id);
      $('#judul').val(d.judul);
      $('#tanggal').val(d.tanggal);
      $('#isi').summernote('code', d.isi || '');

      $('.mymodal-title').html('Edit Pengumuman <code>#'+d.id+'</code>');
      $('#full-width-modal').modal('show');
    })
    .fail(function(){ close_loader(); Swal.fire("Error","Gagal mengambil data","error"); });
}

function simpan(){
  const url = (save_method === 'add')
    ? "<?= site_url('admin_pengumuman/add') ?>"
    : "<?= site_url('admin_pengumuman/update') ?>";

  // Ambil HTML dari editor
  const htmlIsi = $('#isi').summernote('code');
  // Pakai FormData agar lebih aman untuk HTML
  const fd = new FormData(document.getElementById('form_app'));
  fd.set('isi', htmlIsi);

  loader();
  $.ajax({
    url: url,
    type: 'POST',
    data: fd,
    processData: false,
    contentType: false,
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
      url:"<?= site_url('admin_pengumuman/hapus_data') ?>",
      type:"POST",
      data:{id:list_id},
      dataType:"json"
    }).done(function(r){
      close_loader();
      if (!r.success){ Swal.fire(r.title||'Gagal', r.pesan||'Sebagian gagal dihapus', 'error'); }
      else { Swal.fire(r.title, r.pesan, 'success'); reload_table(); }
    }).fail(function(){
      close_loader(); Swal.fire("Gagal","Koneksi bermasalah","error");
    });
  });
}

function close_modal(){
  Swal.fire({
    title:"Tutup formulir?",
    text:"Perubahan yang belum disimpan akan hilang.",
    icon:"warning", showCancelButton:true, confirmButtonText:"Tutup", cancelButtonText:"Batal"
  }).then(r=>{ if(r.value) $('#full-width-modal').modal('hide'); });
}
</script>
