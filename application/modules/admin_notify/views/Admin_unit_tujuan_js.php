<script>
var table, save_method;

function loader(){
  Swal.fire({title:"Proses...", html:"Jangan tutup halaman ini", allowOutsideClick:false, didOpen:()=>Swal.showLoading()});
}

$(document).ready(function(){
  // Select2 parent (AJAX)
  if ($.fn.select2){
    $('#parent_id').select2({
      width: '100%',
      allowClear: true,
      placeholder: $(this).data('placeholder') || '— Root (tanpa parent) —',
      ajax: {
        delay: 150,
        url: "<?= site_url('admin_unit_tujuan/parent_options') ?>",
        data: params => ({ q: params.term || '' }),
        processResults: data => ({ results: data })
      }
    });
  }

  // DataTables
  $.fn.dataTableExt.oApi.fnPagingInfo = function(o){
    return {iStart:o._iDisplayStart, iEnd:o.fnDisplayEnd(), iLength:o._iDisplayLength, iTotal:o.fnRecordsTotal(), iFilteredTotal:o.fnRecordsDisplay(), iPage:Math.ceil(o._iDisplayStart/o._iDisplayLength), iTotalPages:Math.ceil(o.fnRecordsDisplay()/o._iDisplayLength)};
  };

  table = $('#datable_1').DataTable({
    lengthMenu: [[10,25,50,100,-1],[10,25,50,100,'All']],
    oLanguage:{
      sProcessing:"Memuat Data...",
      sSearch:"<i class='ti-search'></i> Cari <?= $subtitle ?> :",
      sZeroRecords:"Maaf Data Tidak Ditemukan",
      sLengthMenu:"Tampil _MENU_ Data",
      sEmptyTable:"Data Tidak Ada",
      sInfo:"Menampilkan _START_ - _END_ dari _TOTAL_ Total Data",
      sInfoEmpty:"Tidak ada data ditampilkan",
      sInfoFiltered:"(Filter dari _MAX_ total Data)",
      oPaginate:{ sNext:"<i class='fe-chevrons-right'></i>", sPrevious:"<i class='fe-chevrons-left'></i>"}
    },
    processing:true, serverSide:true, scrollX:true,
    ajax:{ url:"<?= site_url('admin_unit_tujuan/get_data') ?>", type:"POST" },
    columns:[
      {data:"cek", orderable:false},
      {data:"id",  orderable:false},      // nanti ditimpa rowCallback jadi nomor urut
      {data:"id"},
      {data:"nama_unit"},
      {data:"parent_nama"},
      {data:"nama_pejabat"},
      {data:"no_hp"},
      {data:"kuota"},
      {data:"pendamping"},
      {data:"updated"}
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

function reload_table(){ table.ajax.reload(null,false); }

function add(){
  $('#form_app')[0].reset();
  if ($('#parent_id').data('select2')) $('#parent_id').val(null).trigger('change');
  $('#id').val('');
  save_method='add';
  $('.mymodal-title').text('Tambah Unit');
  $('#full-width-modal').modal('show');
}

function edit(){
  var list_id = [];
  $(".data-check:checked").each(function(){ list_id.push(this.value); });

  if (list_id.length === 1){
    loader();
    save_method='update';
    $.getJSON("<?= site_url('admin_unit_tujuan/edit/') ?>"+list_id[0])
      .done(function(d){
        Swal.close();
        $('#id').val(d.id);
        $('#nama_unit').val(d.nama_unit);
        $('#nama_pejabat').val(d.nama_pejabat);
        $('#no_hp').val(d.no_hp);
        $('#kuota_harian').val(d.kuota_harian);
        $('#jumlah_pendamping').val(d.jumlah_pendamping);

        // set select2 parent (ajax)
        if ($('#parent_id').data('select2')){
          if (d.parent_id){
            var opt = new Option('[#'+d.parent_id+'] '+(d.parent_nama || ''), d.parent_id, true, true);
            $('#parent_id').append(opt).trigger('change');
          } else {
            $('#parent_id').val(null).trigger('change');
          }
        } else {
          $('#parent_id').val(d.parent_id || '');
        }

        $('.mymodal-title').html('Edit Unit <code>#'+d.id+'</code>');
        $('#full-width-modal').modal('show');
      })
      .fail(function(){ Swal.close(); Swal.fire("Error","Gagal mengambil data","error"); });
  } else if (list_id.length > 1){
    Swal.fire("Info","Tidak dapat mengedit "+list_id.length+" data sekaligus, pilih satu saja.","warning");
  } else {
    Swal.fire("Info","Pilih satu data.","warning");
  }
}

function simpan(){
  var url = (save_method==='add')
    ? "<?= site_url('admin_unit_tujuan/add') ?>"
    : "<?= site_url('admin_unit_tujuan/update') ?>";

  $('#form_app').form('submit',{
    url: url,
    onSubmit:function(){ loader(); return $(this).form('validate'); },
    dataType:'json',
    success:function(res){
      try{ var obj = (typeof res === 'string') ? JSON.parse(res) : res; }catch(e){ obj={success:false,title:'Gagal',pesan:'Respon tidak valid'}; }
      Swal.close();
      if (!obj.success){
        Swal.fire(obj.title, obj.pesan, 'error');
        return;
      }
      Swal.fire(obj.title, obj.pesan, 'success');
      $('#full-width-modal').modal('hide');
      reload_table();
    },
    error:function(){ Swal.close(); Swal.fire('Gagal','Tidak dapat mengirim data','error'); }
  });
}

function hapus_data(){
  var list_id = [];
  $(".data-check:checked").each(function(){ list_id.push(this.value); });
  if (list_id.length === 0) { Swal.fire("Info","Pilih minimal satu data","warning"); return; }

  Swal.fire({
    title:"Yakin ingin menghapus "+list_id.length+" data?",
    text:"Data yang punya anak tidak bisa dihapus.",
    icon:"warning",
    showCancelButton:true,
    confirmButtonColor:"#d33",
    cancelButtonColor:"#3085d6",
    confirmButtonText:"Ya, Hapus",
    cancelButtonText:"Batal",
    allowOutsideClick:false
  }).then((res)=>{
    if (!res.value) return;
    loader();
    $.ajax({
      url:"<?= site_url('admin_unit_tujuan/hapus_data') ?>",
      type:"POST",
      data:{id:list_id},
      dataType:"json"
    }).done(function(r){
      Swal.close();
      if (!r.success){ Swal.fire(r.title, r.pesan, 'error'); }
      else { Swal.fire(r.title, r.pesan, 'success'); reload_table(); }
    }).fail(function(){
      Swal.close(); Swal.fire("Gagal","Koneksi bermasalah","error");
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
