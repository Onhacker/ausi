<script type="text/javascript">
let table;
let saveUrl = "<?= site_url(strtolower($controller).'/add'); ?>";
let SETTING = { harga_satuan: 0, durasi_unit: 1, free_main_threshold: 10 };
let isSaving = false; // <<< FLAG ANTI DOBEL KLIK

/* ==== Util Rupiah ==== */
function formatRupiahNumber(n){ n=parseInt(n||0,10); return n.toLocaleString('id-ID'); }
function maskToRupiah(strDigits){
  const n = String(strDigits).replace(/[^\d]/g,'');
  if(n === '') return '';
  return 'Rp ' + formatRupiahNumber(n);
}
function unmaskRupiah(str){ const n = String(str||'').replace(/[^\d]/g,''); return n===''?0:parseInt(n,10); }
function formatRupiah(n){ return 'Rp ' + formatRupiahNumber(n); }

/* ==== Sesi options ==== */
function buildSesiOptions(selectedMinutes = null){
  const unit = Math.max(1, parseInt(SETTING.durasi_unit,10));
  const $sel = $('#durasi_menit');
  $sel.empty();

  // bikin 1–10 sesi standar
  for(let i = 1; i <= 10; i++){
    const menit = i * unit;
    $sel.append(`<option value="${menit}">${i} sesi (${menit} menit)</option>`);
  }

  // tentukan nilai yang dipilih
  let targetMenit = unit; // default: 1 sesi

  if (selectedMinutes){
    // bulatkan durasi lama ke sesi terdekat (dibulatkan ke atas)
    let sesi = Math.ceil(selectedMinutes / unit);
    if (sesi < 1) sesi = 1;
    if (sesi > 10) sesi = 10;
    targetMenit = sesi * unit;
  }

  $sel.val(String(targetMenit));
}

/* ==== Estimator ==== */
function updateEstimator(){
  const unit = Math.max(1, parseInt(SETTING.durasi_unit,10));
  const hs   = Math.max(0, parseInt(SETTING.harga_satuan,10));
  const menit = parseInt($('#durasi_menit').val()||'0',10);
  if(menit>0){
    const sesi = Math.ceil(menit/unit);
    const total = sesi * hs;
    $('#estimator').html(`Total: <b>${sesi}x</b> sesi × ${formatRupiah(hs)} = <b>${formatRupiah(total)}</b>`);
  } else {
    $('#estimator').empty();
  }
}

/* ==== SweetAlert helpers ==== */
function swalToast(icon='success', title='Berhasil', text='') {
  return Swal.fire({ icon, title, text, toast:true, position:'center', showConfirmButton:false, timer:2500, timerProgressBar:true });
}
function swalErr(msg, title='Gagal'){ return Swal.fire({icon:'error',   title, html: msg}); }
function swalWarn(msg, title='Perhatian'){ return Swal.fire({icon:'warning', title, html: msg}); }

function refresh(){ table.ajax.reload(null,false); }

/* ==== Init ==== */
$(document).ready(function(){
  // init setting & hint
  $.getJSON("<?= site_url(strtolower($controller).'/get_setting'); ?>", function(res){
    if(res && res.success){
      SETTING = res.data || SETTING;

      let voucherHint = (SETTING.free_main_threshold > 0)
        ? `Program voucher: <b>${SETTING.free_main_threshold}x main</b> (berdasarkan nomor HP) dapat 1x FREE.`
        : `Program voucher: <b>tidak aktif</b> (Jumlah main untuk dapat FREE = 0).`;

      $('#price_hint').html(
        `Tarif aktif sewa PS: <b>${formatRupiah(SETTING.harga_satuan)}</b> per ${SETTING.durasi_unit} menit.`+
        `<br>${voucherHint}`
      );

      buildSesiOptions();
      updateEstimator();
    }
  });

  // open setting modal
  $('#btnOpenSetting').on('click', function(){
    $('#harga_satuan_rp').val(maskToRupiah(SETTING.harga_satuan));
    $('#harga_satuan').val(SETTING.harga_satuan);
    $('#durasi_unit').val(SETTING.durasi_unit);
    $('#free_main_threshold').val(SETTING.free_main_threshold);
    $('#setting-modal').modal('show');
    setTimeout(()=>$('#harga_satuan_rp').trigger('focus'),100);
  });

  // mask rupiah
  $('#harga_satuan_rp').on('input', function(){
    const raw = unmaskRupiah(this.value);
    const masked = maskToRupiah(raw);
    this.value = masked;
    $('#harga_satuan').val(raw);
    this.setSelectionRange(masked.length, masked.length);
  });

  // save setting
  $('#btnSaveSetting').on('click', function(){
    const raw = unmaskRupiah($('#harga_satuan_rp').val());
    $('#harga_satuan').val(raw);
    $.ajax({
      url: "<?= site_url(strtolower($controller).'/save_setting'); ?>",
      type: "POST", dataType: "json", data: $('#form_setting').serialize(),
      success: function(res){
        if(res.success){
          $('#setting-modal').modal('hide');
          swalToast('success','Berhasil',res.pesan||'Pengaturan tersimpan');
          $.getJSON("<?= site_url(strtolower($controller).'/get_setting'); ?>", function(r){
            if(r && r.success){
              SETTING = r.data || SETTING;

              let voucherHint = (SETTING.free_main_threshold > 0)
                ? `Program voucher: <b>${SETTING.free_main_threshold}x main</b> (berdasarkan nomor HP) dapat 1x FREE.`
                : `Program voucher: <b>tidak aktif</b> (Jumlah main untuk dapat FREE = 0).`;

              $('#price_hint').html(
                `Tarif aktif sewa PS: <b>${formatRupiah(SETTING.harga_satuan)}</b> per ${SETTING.durasi_unit} menit.`+
                `<br>${voucherHint}`
              );

              buildSesiOptions($('#durasi_menit').val());
              updateEstimator();
              refresh();
            }
          });

        } else { swalErr(res.pesan||'Gagal menyimpan pengaturan'); }
      },
      error: function(){ swalErr('Terjadi kesalahan koneksi'); }
    });
  });

  // datatable
  table = $('#datable_1').DataTable({
    processing: true, serverSide: true, responsive: true,
    ajax: { url: "<?= site_url(strtolower($controller).'/get_data'); ?>", type: "POST" },
    columns: [
      { data: 'cek', orderable: false, searchable: false, className:'text-center' },
      { data: 'no',  orderable: false, searchable: false },
      { data: 'nama' },
      { data: 'no_hp', className:'text-nowrap' },
      { data: 'tanggal', className:'text-nowrap' },
      { data: 'durasi', className:'text-nowrap' },
      { data: 'sesi', className:'text-nowrap' },
      { data: 'total', className:'text-nowrap' },
      { data: 'status', orderable:false, searchable:false },
      { data: 'aksi', orderable:false, searchable:false, className:'text-center' },
    ],
    order: [[4,'desc']],
    rowCallback: function(row, data, displayIndex) {
      const info = this.api().page.info();
      $('td:eq(1)', row).html(info.start + displayIndex + 1);
    }
  });

  // check-all
  $('#check-all').on('click', function(){ $('.data-check').prop('checked', this.checked); });

  // estimator realtime
  $('#durasi_menit').on('change', updateEstimator);
});

/* ==== Form transaksi ==== */
function add(){
  saveUrl = "<?= site_url(strtolower($controller).'/add'); ?>";
  $('#form_app')[0].reset();
  buildSesiOptions();
  updateEstimator();
  $('#id_transaksi').val('');
  $('input[name="status"][value="baru"]').prop('checked', true);
  $('.mymodal-title').text('Tambah Transaksi PS');
  $('#full-width-modal').modal('show');
}
function edit(id){
  $('#form_app')[0].reset();
  $.getJSON("<?= site_url(strtolower($controller).'/get_one/'); ?>"+id, function(res){
    if(res.success){
      const d = res.data;
      $('#id_transaksi').val(d.id_transaksi);
      $('#nama').val(d.nama);
      $('#no_hp').val(d.no_hp || '');
      $('#catatan').val(d.catatan || '');
      buildSesiOptions(parseInt(d.durasi_menit,10));
      const st = (d.status || 'baru');
      $(`input[name="status"][value="${st}"]`).prop('checked', true);
      updateEstimator();
      saveUrl = "<?= site_url(strtolower($controller).'/update'); ?>";
      $('.mymodal-title').text('Edit Transaksi PS');
      $('#full-width-modal').modal('show');
    } else { swalErr(res.pesan || 'Data tidak ditemukan'); }
  }).fail(function(){ swalErr('Terjadi kesalahan koneksi'); });
}

function close_modal(){ $('#full-width-modal').modal('hide'); }

function simpan(){
  if (isSaving) return; // cegah dobel klik
  isSaving = true;

  const $btn = $('#btnSimpan');
  const oldHtml = $btn.html();
  $btn.prop('disabled', true).html('Menyimpan...');

  $.ajax({
    url: saveUrl,
    type: "POST",
    dataType: "json",
    data: $('#form_app').serialize(),
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
    },
    complete: function(){
      isSaving = false;
      $btn.prop('disabled', false).html(oldHtml);
    }
  });
}

/* ==== Bayar (set lunas) ==== */
function bayar(id){
  Swal.fire({
    title: 'Tandai Lunas?',
    text: 'Status akan diubah menjadi Lunas.',
    icon: 'question', showCancelButton: true,
    confirmButtonText: 'Ya, Lunas', cancelButtonText: 'Batal'
  }).then(function(r){
    if(!r.isConfirmed) return;
    $.ajax({
      url: "<?= site_url(strtolower($controller).'/set_lunas'); ?>",
      type: "POST", dataType: "json", data: {id},
      success: function(res){
        if(res.success){ swalToast('success','Berhasil',res.pesan || 'Transaksi Lunas'); refresh(); }
        else{ swalErr(res.pesan || 'Gagal mengubah status'); }
      },
      error: function(){ swalErr('Terjadi kesalahan koneksi'); }
    });
  });
}

function hapus_data(){
  const ids = []; $('.data-check:checked').each(function(){ ids.push($(this).val()); });
  if(ids.length === 0){ swalWarn('Tidak ada data dipilih'); return; }

  Swal.fire({
    title: 'Hapus data terpilih?',
    text: 'Tindakan ini tidak bisa dibatalkan.',
    icon: 'warning', showCancelButton: true,
    confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal'
  }).then(function(result){
    if(!result.isConfirmed) return;
    $.ajax({
      url: "<?= site_url(strtolower($controller).'/hapus_data'); ?>",
      type: "POST", dataType: "json",
      data: {id: ids}, traditional: true,
      success: function(res){
        if(res.success){ swalToast('success','Berhasil',res.pesan || 'Data terhapus'); refresh(); }
        else{ swalErr(res.pesan || 'Gagal menghapus'); }
      },
      error: function(){ swalErr('Terjadi kesalahan koneksi'); }
    });
  });
}

function batal(id){
  Swal.fire({
    title: 'Batalkan transaksi?',
    text: 'Status akan diubah menjadi Batal & poin voucher PS akan dihapus.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, Batalkan',
    cancelButtonText: 'Tutup'
  }).then(function(r){
    if(!r.isConfirmed) return;
    $.ajax({
      url: "<?= site_url(strtolower($controller).'/set_batal'); ?>",
      type: "POST",
      dataType: "json",
      data: {id},
      success: function(res){
        if(res.success){
          swalToast('success','Berhasil',res.pesan || 'Transaksi dibatalkan');
          refresh();
        }else{
          swalErr(res.pesan || 'Gagal mengubah status');
        }
      },
      error: function(){ swalErr('Terjadi kesalahan koneksi'); }
    });
  });
}
</script>