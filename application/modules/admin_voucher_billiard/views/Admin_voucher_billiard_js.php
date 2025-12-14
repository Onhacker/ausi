<script>
var table, save_method = 'add', isSaving = false;

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

function reset_form(){
  $('#form_app')[0].reset();
  $('#id_voucher').val('');
}

function toggleJenis(){
  const jenis = String($('#jenis').val() || 'FREE_MAIN').toUpperCase();
  $('.jenis-free, .jenis-diskon, .jenis-persen').hide();

  if (jenis === 'FREE_MAIN'){
    $('.jenis-free').show();
    $('#jam_voucher').prop('disabled', false);
    $('#nilai').prop('disabled', true).val(0);
    $('#max_potongan').prop('disabled', true).val(0);
  } else if (jenis === 'NOMINAL'){
    $('.jenis-diskon').show();
    $('#jam_voucher').prop('disabled', true).val(0);
    $('#nilai').prop('disabled', false);
    $('#max_potongan').prop('disabled', true).val(0);
  } else { // PERSEN
    $('.jenis-diskon').show();
    $('.jenis-persen').show();
    $('#jam_voucher').prop('disabled', true).val(0);
    $('#nilai').prop('disabled', false);
    $('#max_potongan').prop('disabled', false);
  }
}

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
      sSearch:"<i class='ti-search'></i> Cari Voucher :",
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
      url:"<?= site_url('admin_voucher_billiard/get_dataa') ?>",
      type:"POST"
    },
    columns:[
      {data:"cek", orderable:false},
      {data:"no",  orderable:false},
      {data:"kode_voucher"},
      {data:"nama"},
      {data:"no_hp"},
      {data:"benefit"},
      {data:"dibuat"},
      {data:"dipakai"},
      {data:"status"},
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

  $('#jenis').on('change', toggleJenis);
  toggleJenis();
});

function add(){
  save_method = 'add';
  reset_form();

  // default periode: hari ini s/d +7
  const today  = new Date();
  const tMulai = today.toISOString().slice(0,10);
  const next7  = new Date(); next7.setDate(next7.getDate()+7);
  const tSeles = next7.toISOString().slice(0,10);

  $('#jenis').val('FREE_MAIN');
  $('#jam_voucher').val('1');
  $('#nilai').val('0');
  $('#max_potongan').val('0');
  $('#minimal_subtotal').val('0');
  $('#tgl_mulai').val(tMulai);
  $('#tgl_selesai').val(tSeles);
  $('#notes').val('');

  toggleJenis();

  $('.mymodal-title').text('Tambah Voucher Billiard');
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
  $.getJSON("<?= site_url('admin_voucher_billiard/get_one/') ?>"+targetId)
    .done(function(r){
      close_loader();
      if (!r.success){
        Swal.fire(r.title||'Gagal', r.pesan||'Tidak bisa mengambil data', 'error');
        return;
      }
      const d = r.data;

      $('#id_voucher').val(d.id_voucher);
      $('#nama').val(d.nama);
      $('#no_hp').val(d.no_hp);
      $('#jenis').val(String(d.jenis || 'FREE_MAIN').toUpperCase());
      $('#jam_voucher').val(d.jam_voucher);
      $('#nilai').val(d.nilai);
      $('#max_potongan').val(d.max_potongan);
      $('#minimal_subtotal').val(d.minimal_subtotal);
      $('#tgl_mulai').val(d.tgl_mulai);
      $('#tgl_selesai').val(d.tgl_selesai);
      $('#notes').val(d.notes);

      toggleJenis();

      $('.mymodal-title').html('Edit Voucher Billiard <code>#'+(d.kode_voucher||'')+'</code>');
      $('#full-width-modal').modal('show');
    })
    .fail(function(){
      close_loader();
      Swal.fire("Error","Gagal mengambil data","error");
    });
}

function simpan(){
  if (isSaving) return;
  isSaving = true;

  const url = (save_method === 'add')
    ? "<?= site_url('admin_voucher_billiard/add') ?>"
    : "<?= site_url('admin_voucher_billiard/update') ?>";

  const fd   = new FormData(document.getElementById('form_app'));
  const $btn = $('#btnSimpan');
  const oldHtml = $btn.html();

  $btn.prop('disabled', true).html('Menyimpan...');
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
    if (!r.success){
      Swal.fire(r.title||'Gagal', r.pesan||'Terjadi kesalahan', 'error');
      return;
    }
    Swal.fire({ title:r.title, html:r.pesan, icon:'success' });
    $('#full-width-modal').modal('hide');
    reload_table();
  }).fail(function(){
    close_loader();
    Swal.fire('Gagal','Tidak dapat mengirim data','error');
  }).always(function(){
    isSaving = false;
    $btn.prop('disabled', false).html(oldHtml);
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
      url:"<?= site_url('admin_voucher_billiard/hapus_data') ?>",
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

function detailVoucher(id = null){
  let targetId = id;
  if (!targetId){
    const list_id = [];
    $(".data-check:checked").each(function(){ list_id.push(this.value); });
    if (list_id.length !== 1){
      Swal.fire("Info","Pilih satu data untuk dilihat detailnya.","warning");
      return;
    }
    targetId = list_id[0];
  }

  loader();
  $.getJSON("<?= site_url('admin_voucher_billiard/get_one/') ?>"+targetId)
    .done(function(r){
      close_loader();
      if (!r.success){
        Swal.fire(r.title||'Gagal', r.pesan||'Tidak bisa mengambil data', 'error');
        return;
      }

      const d = r.data || {};
      const esc = s => String(s||'').replace(/[&<>"'`]/g, c => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','`':'&#x60;'
      }[c]));

      const fmtRp = v => 'Rp ' + (parseInt(v,10)||0).toLocaleString('id-ID');
      const jenis = String(d.jenis || 'FREE_MAIN').toUpperCase();

      let benefit = '-';
      if (jenis === 'FREE_MAIN'){
        benefit = 'Gratis ' + (parseInt(d.jam_voucher,10)||0) + ' Jam';
      } else if (jenis === 'NOMINAL'){
        benefit = fmtRp(d.nilai);
      } else {
        benefit = (parseInt(d.nilai,10)||0) + '%';
        const mp = parseInt(d.max_potongan,10)||0;
        if (mp>0) benefit += ' (max '+fmtRp(mp)+')';
      }

      const periode = (d.tgl_mulai || '-') + ' s/d ' + (d.tgl_selesai || '-');

      const st = String(d.status||'').toLowerCase();
      const claimed = String(d.is_claimed||'0') === '1';
      let statusLabel = claimed || st==='accept' ? 'Dipakai' : (st==='batal' ? 'Batal' : 'Baru');
      let statusClass = claimed || st==='accept' ? 'badge-success' : (st==='batal' ? 'badge-danger' : 'badge-primary');

      const html =
        '<div class="table-responsive text-left">'+
          '<table class="table table-sm table-bordered mb-0">'+
            '<tbody>'+
              '<tr><th style="width:38%">Kode</th><td><code>'+esc(d.kode_voucher)+'</code></td></tr>'+
              '<tr><th>Nama</th><td>'+esc(d.nama)+'</td></tr>'+
              '<tr><th>No. HP</th><td>'+esc(d.no_hp)+'</td></tr>'+
              '<tr><th>Jenis</th><td>'+esc(jenis)+'</td></tr>'+
              '<tr><th>Benefit</th><td><strong>'+esc(benefit)+'</strong></td></tr>'+
              '<tr><th>Minimal Subtotal</th><td>'+(parseInt(d.minimal_subtotal,10)>0 ? fmtRp(d.minimal_subtotal) : '-')+'</td></tr>'+
              '<tr><th>Periode</th><td>'+esc(periode)+'</td></tr>'+
              '<tr><th>Issued From Count</th><td>'+(parseInt(d.issued_from_count,10)||0)+'</td></tr>'+
              '<tr><th>Status</th><td><span class="badge '+statusClass+'">'+statusLabel+'</span></td></tr>'+
              '<tr><th>Created At</th><td>'+esc(d.created_at)+'</td></tr>'+
              '<tr><th>Claimed At</th><td>'+(d.claimed_at ? esc(d.claimed_at) : '-')+'</td></tr>'+
              '<tr><th>Notes</th><td>'+(d.notes ? esc(d.notes) : '<span class="text-muted">-</span>')+'</td></tr>'+
            '</tbody>'+
          '</table>'+
        '</div>';

      Swal.fire({
        title: 'Detail Voucher Billiard',
        html: html,
        width: 750,
        showCloseButton: true,
        confirmButtonText: 'Tutup'
      });
    })
    .fail(function(){
      close_loader();
      Swal.fire("Error","Gagal mengambil data","error");
    });
}
</script>
