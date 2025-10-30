<script type="text/javascript">
let table;
let saveUrl = "<?= site_url(strtolower($controller).'/add'); ?>";

$(document).ready(function(){
  table = $('#datable_1').DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    ajax: {
      url: "<?= site_url(strtolower($controller).'/get_data'); ?>",
      type: "POST"
    },
    columns: [
      { data: 'cek', orderable: false, searchable: false, className:'text-center' },
      { data: 'no',  orderable: false, searchable: false },
      { data: 'tugas' },
      { data: 'aksi', orderable: false, searchable: false, className:'text-center' },
    ],
    order: [[2,'asc']],
    rowCallback: function(row, data, displayIndex) {
      const info = this.api().page.info();
      $('td:eq(1)', row).html(info.start + displayIndex + 1);
    }
  });

  // check-all
  $('#check-all').on('click', function(){
    $('.data-check').prop('checked', this.checked);
  });
});

/* ==== SweetAlert helpers ==== */
function swalToast(icon='success', title='Berhasil', text='') {
  return Swal.fire({
    icon, title, text,
    toast: true, position: 'center',
    showConfirmButton: false, timer: 2500, timerProgressBar: true
  });
}
function swalOk(msg, title='Berhasil'){ return Swal.fire({icon:'success', title, html: msg}); }
function swalErr(msg, title='Gagal'){   return Swal.fire({icon:'error',   title, html: msg}); }
function swalWarn(msg, title='Perhatian'){ return Swal.fire({icon:'warning', title, html: msg}); }

function refresh(){ table.ajax.reload(null,false); }

function add(){
  saveUrl = "<?= site_url(strtolower($controller).'/add'); ?>";
  $('#form_app')[0].reset();
  $('#id_unit_lain').val('');
  $('.mymodal-title').text('Tambah');
  $('#full-width-modal').modal('show');
}

function edit(id){
  $('#form_app')[0].reset();
  $.getJSON("<?= site_url(strtolower($controller).'/get_one/'); ?>"+id, function(res){
    if(res.success){
      const d = res.data;
      $('#id_unit_lain').val(d.id_unit_lain);
      $('#tugas').val(d.tugas);
      saveUrl = "<?= site_url(strtolower($controller).'/update'); ?>";
      $('.mymodal-title').text('Edit');
      $('#full-width-modal').modal('show');
    } else {
      swalErr(res.pesan || 'Data tidak ditemukan');
    }
  }).fail(function(){
    swalErr('Terjadi kesalahan koneksi');
  });
}

function close_modal(){ $('#full-width-modal').modal('hide'); }

function simpan(){
  const form = $('#form_app');
  $.ajax({
    url: saveUrl,
    type: "POST",
    dataType: "json",
    data: form.serialize(),
    success: function(res){
      if(res.success){
        close_modal();
        swalToast('success','Berhasil',res.pesan || 'Data tersimpan');
        refresh();
      } else {
        swalErr(res.pesan || 'Gagal memproses');
      }
    },
    error: function(){
      swalErr('Terjadi kesalahan koneksi');
    }
  });
}

function hapus_data(){
  const ids = [];
  $('.data-check:checked').each(function(){ ids.push($(this).val()); });
  if(ids.length === 0){ swalWarn('Tidak ada data dipilih'); return; }

  Swal.fire({
    title: 'Hapus data terpilih?',
    text: 'Tindakan ini tidak bisa dibatalkan.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, hapus',
    cancelButtonText: 'Batal'
  }).then(function(result){
    if(!result.isConfirmed) return;

    $.ajax({
      url: "<?= site_url(strtolower($controller).'/hapus_data'); ?>",
      type: "POST",
      dataType: "json",
      data: {id: ids},
      traditional: true,
      success: function(res){
        if(res.success){
          swalToast('success','Berhasil',res.pesan || 'Data terhapus');
          refresh();
        } else {
          swalErr(res.pesan || 'Gagal menghapus');
        }
      },
      error: function(){
        swalErr('Terjadi kesalahan koneksi');
      }
    });
  });
}
</script>
