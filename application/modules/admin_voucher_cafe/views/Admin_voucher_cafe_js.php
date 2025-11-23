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

function close_loader(){
  if (window.Swal) Swal.close();
}

function reload_table(){
  table.ajax.reload(null,false);
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
      url:"<?= site_url('admin_voucher_cafe/get_dataa') ?>",
      type:"POST"
    },
    columns:[
      {data:"cek", orderable:false},
      {data:"no",  orderable:false},
      {data:"kode_voucher"},
      {data:"nama"},
      {data:"no_hp"},
      {data:"tipe"},
      {data:"nilai"},
      {data:"periode"},
      {data:"klaim"},
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
});

function reset_form(){
  $('#form_app')[0].reset();
  $('#id').val('');
}

function add(){
  save_method = 'add';
  reset_form();

  // default: hari ini s/d +7 hari
  const today  = new Date();
  const pad    = (n)=> (n<10?'0':'')+n;
  const tMulai = today.toISOString().slice(0,10);

  const nextWeek = new Date();
  nextWeek.setDate(nextWeek.getDate()+7);
  const tSelesai = nextWeek.toISOString().slice(0,10);

  $('#tgl_mulai').val(tMulai);
  $('#tgl_selesai').val(tSelesai);
  $('#status_voucher').val('1');
  $('#tipe').val('nominal');
  $('#kuota_klaim').val('1');
  $('#keterangan').val('');

  $('.mymodal-title').text('Tambah Voucher Cafe');
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
  $.getJSON("<?= site_url('admin_voucher_cafe/get_one/') ?>"+targetId)
    .done(function(r){
      close_loader();
      if (!r.success){
        Swal.fire(r.title||'Gagal', r.pesan||'Tidak bisa mengambil data', 'error');
        return;
      }
      const d = r.data;
      $('#id').val(d.id);
      $('#nama').val(d.nama);
      $('#no_hp').val(d.no_hp);
      $('#tipe').val(d.tipe);
      $('#nilai').val(d.nilai);
      $('#minimal_belanja').val(d.minimal_belanja);
      $('#max_potongan').val(d.max_potongan);
      $('#tgl_mulai').val(d.tgl_mulai);
      $('#tgl_selesai').val(d.tgl_selesai);
      $('#kuota_klaim').val(d.kuota_klaim);
      $('#status_voucher').val(d.status);
      $('#keterangan').val(d.keterangan);

      $('.mymodal-title').html('Edit Voucher Cafe <code>#'+d.kode_voucher+'</code>');
      $('#full-width-modal').modal('show');
    })
    .fail(function(){
      close_loader();
      Swal.fire("Error","Gagal mengambil data","error");
    });
}

function simpan(){
  const url = (save_method === 'add')
    ? "<?= site_url('admin_voucher_cafe/add') ?>"
    : "<?= site_url('admin_voucher_cafe/update') ?>";

  const fd = new FormData(document.getElementById('form_app'));

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
    Swal.fire({
      title:r.title,
      html:r.pesan,
      icon:'success'
    });
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
      url:"<?= site_url('admin_voucher_cafe/hapus_data') ?>",
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
  $.getJSON("<?= site_url('admin_voucher_cafe/get_one/') ?>"+targetId)
    .done(function(r){
      close_loader();
      if (!r.success){
        Swal.fire(r.title||'Gagal', r.pesan||'Tidak bisa mengambil data', 'error');
        return;
      }

      const d   = r.data || {};
      const esc = function(s){
        return String(s || '').replace(/[&<>"'`]/g, function(c){
          return {
            '&':'&amp;','<':'&lt;','>':'&gt;',
            '"':'&quot;',"'":'&#39;','`':'&#x60;'
          }[c];
        });
      };
      const toInt = (v)=> parseInt(v,10) || 0;
      const fmtRp = (v)=>{
        const n = toInt(v);
        return 'Rp ' + n.toLocaleString('id-ID');
      };

      // === TANGGAL INDO (dd-mm-yyyy) ===
      const fmtDateId = (s)=>{
        if (!s) return '-';
        const parts = String(s).split('-'); // asumsi: YYYY-MM-DD
        if (parts.length === 3){
          return parts[2] + '-' + parts[1] + '-' + parts[0]; // dd-mm-yyyy
        }
        return s;
      };

      const tipeLabel  = d.tipe === 'persen' ? 'Persen (%)' : 'Nominal (Rp)';
      const nilaiLabel = d.tipe === 'persen'
        ? (toInt(d.nilai)) + ' %'
        : fmtRp(d.nilai);

      const minBelanja = d.minimal_belanja ? fmtRp(d.minimal_belanja) : '-';
      const maxPot     = d.max_potongan    ? fmtRp(d.max_potongan)    : '-';

      // pakai tanggal Indonesia di periode
      const periode = fmtDateId(d.tgl_mulai) + ' s/d ' + fmtDateId(d.tgl_selesai);

      const used       = toInt(d.klaim_terpakai);
      const quota      = toInt(d.kuota_klaim);
      const kuotaText  = used + ' / ' + quota;

      const todayStr = (new Date()).toISOString().slice(0,10);
      let statusLabel = 'Aktif';
      let statusClass = 'badge-success';

      if (quota > 0 && used >= quota){
        statusLabel = 'Habis Terpakai';
        statusClass = 'badge-danger';
      } else if (String(d.status) === '0'){
        statusLabel = 'Nonaktif';
        statusClass = 'badge-secondary';
      } else if (d.tgl_selesai && d.tgl_selesai < todayStr){
        statusLabel = 'Expired';
        statusClass = 'badge-warning';
      }

      const jenisVoucher = d.jenis_voucher
        ? d.jenis_voucher.replace(/_/g,' ')
        : '-';

      const html =
        '<div class="table-responsive text-left">'+
          '<table class="table table-sm table-bordered mb-0">'+
            '<tbody>'+
              '<tr>'+
                '<th style="width:38%;white-space:nowrap;">Kode Voucher</th>'+
                '<td><code>'+esc(d.kode_voucher)+'</code></td>'+
              '</tr>'+
              '<tr>'+
                '<th>Nama</th>'+
                '<td>'+esc(d.nama)+'</td>'+
              '</tr>'+
              '<tr>'+
                '<th>No. HP</th>'+
                '<td>'+esc(d.no_hp)+'</td>'+
              '</tr>'+
              '<tr>'+
                '<th>Jenis Voucher</th>'+
                '<td>'+esc(jenisVoucher)+'</td>'+
              '</tr>'+
              '<tr>'+
                '<th>Tipe &amp; Nilai</th>'+
                '<td>'+tipeLabel+' &mdash; <strong>'+nilaiLabel+'</strong></td>'+
              '</tr>'+
              '<tr>'+
                '<th>Minimal Belanja</th>'+
                '<td>'+minBelanja+'</td>'+
              '</tr>'+
              '<tr>'+
                '<th>Max Potongan</th>'+
                '<td>'+maxPot+'</td>'+
              '</tr>'+
              '<tr>'+
                '<th>Periode</th>'+
                '<td>'+periode+'</td>'+
              '</tr>'+
              '<tr>'+
                '<th>Klaim</th>'+
                '<td>'+kuotaText+'</td>'+
              '</tr>'+
              '<tr>'+
                '<th>Status</th>'+
                '<td><span class="badge '+statusClass+'">'+statusLabel+'</span></td>'+
              '</tr>'+
              '<tr>'+
                '<th>Keterangan</th>'+
                '<td>'+(d.keterangan ? esc(d.keterangan) : '<span class="text-muted">-</span>')+'</td>'+
              '</tr>'+
            '</tbody>'+
          '</table>'+
        '</div>';

      Swal.fire({
        title: 'Detail Voucher Cafe',
        html: html,
        width: 650,
        showCloseButton: true,
        focusConfirm: false,
        confirmButtonText: 'Tutup'
      });
    })
    .fail(function(){
      close_loader();
      Swal.fire("Error","Gagal mengambil data","error");
    });
}


</script>
