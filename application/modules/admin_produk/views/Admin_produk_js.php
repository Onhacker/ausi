<script>
/* =======================
   Global vars & helpers
======================= */
var table, save_method = 'add';

function loader(){
  if (window.Swal) {
    Swal.fire({
      title: "Proses...",
      html: "Jangan tutup halaman ini",
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });
  }
}
function close_loader(){ if (window.Swal) Swal.close(); }
function reload_table(){ if (table) table.ajax.reload(null,false); }

/* ==========================================
   Subkategori helpers (dibuat GLOBAL)
========================================== */
window.resetSubkategori = function(disable=true){
  var $sub = $('#sub_kategori_id');
  if (!$sub.length) return;
  $sub.html('<option value="">— Pilih Sub Kategori —</option>');
  $sub.prop('disabled', disable);
};

window.loadSubkategori = function(kategoriId, selectedId){
  var $sub = $('#sub_kategori_id');
  if (!$sub.length) return;
  if (!kategoriId){
    resetSubkategori(true);
    return;
  }
  $sub.prop('disabled', true).html('<option>Memuat...</option>');
  $.getJSON("<?= site_url('admin_produk/get_subkategori/') ?>"+kategoriId)
    .done(function(r){
      $sub.empty().append('<option value="">— Pilih Sub Kategori —</option>');
      if (r && r.success && r.data && r.data.length){
        r.data.forEach(function(it){
          var opt = $('<option/>', { value: it.id, text: it.nama });
          if (selectedId && String(selectedId) === String(it.id)) opt.attr('selected','selected');
          $sub.append(opt);
        });
        $sub.prop('disabled', false);
      } else {
        $sub.prop('disabled', true);
      }
    })
    .fail(function(){
      resetSubkategori(true);
    });
};

/* ==========================================
   Document Ready
========================================== */
$(document).ready(function(){

  // Summernote Deskripsi (guard)
  if ($('#deskripsi').length){
    $('#deskripsi').summernote({
      height: 220,
      placeholder: 'Tuliskan deskripsi produk...',
      toolbar: [
        ['style', ['style']],
        ['font', ['bold','italic','underline','clear']],
        ['para', ['ul','ol','paragraph']],
        ['insert', ['link','picture','table']],
        ['view', ['codeview','help']]
      ]
    });
  }

  // Preview image + label file + kosongkan path (guard)
  if ($('#gambar_file').length){
    $('#gambar_file').on('change', function(){
      var file = this.files ? this.files[0] : null;
      // update label bootstrap custom-file
      $(this).next('.custom-file-label').text(file ? file.name : 'Choose file');

      if (!file) return;
      var reader = new FileReader();
      reader.onload = function(e){
        var $preview = $('#preview');
        if ($preview.length) $preview.attr('src', e.target.result).show();
      };
      reader.readAsDataURL(file);

      // kosongkan path lama agar backend tidak menggunakan nilai teks 'gambar'
      $('#gambar').val('');
    });
  }

  // chained select: on change kategori → muat sub
  if ($('#kategori_id').length){
    $('#kategori_id').on('change', function(){
      var kat = $(this).val();
      if (typeof loadSubkategori === 'function') loadSubkategori(kat, null);
    });
  }

  // DataTables (guard)
  if ($.fn.dataTable && $('#datable_1').length){

    // paging info helper
    $.fn.dataTableExt = $.fn.dataTableExt || { oApi: {} };
    $.fn.dataTableExt.oApi.fnPagingInfo = function(o){
      return {
        iStart: o._iDisplayStart,
        iEnd: o.fnDisplayEnd(),
        iLength: o._iDisplayLength,
        iTotal: o.fnRecordsTotal(),
        iFilteredTotal: o.fnRecordsDisplay(),
        iPage: Math.ceil(o._iDisplayStart / o._iDisplayLength),
        iTotalPages: Math.ceil(o.fnRecordsDisplay() / o._iDisplayLength)
      };
    };

    table = $('#datable_1').DataTable({
      lengthMenu: [[10,25,50,100,-1],[10,25,50,100,'All']],
      oLanguage:{
        sProcessing:"Memuat Data...",
        sSearch:"<i class='ti-search'></i> Cari Produk :",
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
      ajax:{
        url:"<?= site_url('admin_produk/get_dataa') ?>",
        type:"POST",
        data: function(d){
          d.kategori_id = $('#filter_kategori').val() || '';
        }
      },
      columns:[
        {data:"cek", orderable:false},
        {data:"no",  orderable:false},
        {data:"produk"},
        {data:"kategori"},
        // {data:"sub_kategori"},   // pastikan controller mengirim field ini
        {data:"sku"},
        {data:"harga"},
        {data:"stok"},
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

    // Check all
    $("#check-all").on('click', function(){
      $(".data-check").prop('checked', $(this).prop('checked'));
    });

    // Ganti kategori → reload tabel
    $('#filter_kategori').on('change', function(){ reload_table(); });

// Tombol reset
$('#btn-clear-kat').on('click', function(){
  $('#filter_kategori').val('');
  reload_table();
});

  }
});

/* ==========================================
   Actions: add / edit / simpan / hapus / close
========================================== */

function add(){
  save_method = 'add';

  if (typeof resetSubkategori === 'function') resetSubkategori(true);

  var $form = $('#form_app');
  if ($form.length && $form[0]) $form[0].reset();

  $('#id').val('');
  $('#sku').val('').attr('placeholder','(auto)');
  $('#is_active').prop('checked', true);

  if ($('#deskripsi').length) $('#deskripsi').summernote('code','');

  var $preview = $('#preview');
  if ($preview.length) $preview.hide().attr('src','');
  // reset harga/hpp (kosong terformat)
  setRupiahValue($('#harga'), '');
  setRupiahValue($('#hpp'), '');

// reset recomended
$('#recomended').prop('checked', false);

  $('#gambar').val('');
  $('#gambar_file').val('');
  $('.mymodal-title').text('Tambah Produk');

  var $modal = $('#full-width-modal');
  if ($modal.length) $modal.modal('show');
}

function edit(id){
  var targetId = id;
  if (!targetId){
    var list_id = [];
    $(".data-check:checked").each(function(){ list_id.push(this.value); });
    if (list_id.length !== 1){
      if (window.Swal) Swal.fire("Info","Pilih satu data untuk diedit.","warning");
      return;
    }
    targetId = list_id[0];
  }

  save_method='update';
  loader();

  $.getJSON("<?= site_url('admin_produk/get_one/') ?>"+targetId)
    .done(function(r){
      close_loader();
      if (!r || !r.success){
        if (window.Swal) Swal.fire(r.title||'Gagal', r.pesan||'Tidak bisa mengambil data', 'error');
        return;
      }
      var d = r.data || {};

      // kosongkan input file & preview dulu
      $('#gambar_file').val('');
      var $preview = $('#preview');
      if ($preview.length) $preview.hide().attr('src','');

      $('#id').val(d.id);
      $('#kategori_id').val(d.kategori_id);

      // muat subkategori + pilih yang sesuai
      if (typeof loadSubkategori === 'function'){
        loadSubkategori(d.kategori_id, d.sub_kategori_id || '');
      }

      $('#nama').val(d.nama);
      $('#kata_kunci').val(d.kata_kunci);
      $('#sku').val(d.sku);

// ⬇️ GANTI 2 baris ini:
// $('#harga').val(d.harga);
// $('#hpp').val(d.hpp);

// ⬆️ MENJADI:
      setRupiahValue($('#harga'), d.harga);
      setRupiahValue($('#hpp'), d.hpp);

      // sisanya tetap
      $('#stok').val(d.stok);
      $('#satuan').val(d.satuan);
      $('#gambar').val(d.gambar);

      (function(){
        var rec = d.recomended;
        var isAndalan = (rec === 1) || (rec === '1') || (rec === true) || (String(rec).toLowerCase() === 'true');
  $('#recomended').prop('checked', isAndalan).val('1'); // nilai '1' saat checked
})();

      // Preview gambar lama (cache buster)
      if (d.gambar){
        var base = (typeof d.gambar === 'string' && d.gambar.indexOf('http') === 0)
          ? d.gambar
          : "<?= base_url(); ?>"+d.gambar;
        var bust = (d.updated_at || Date.now());
        if ($preview.length) $preview.attr('src', base + (base.indexOf('?')>=0 ? '&' : '?') + 'v=' + bust).show();
      } else {
        if ($preview.length) $preview.hide();
      }

      if ($('#deskripsi').length) $('#deskripsi').summernote('code', d.deskripsi || '');
      $('#is_active').prop('checked', String(d.is_active) === '1');

      $('.mymodal-title').html('Edit Produk <code>#'+d.id+'</code>');
      var $modal = $('#full-width-modal');
      if ($modal.length) $modal.modal('show');
    })
    .fail(function(){
      close_loader();
      if (window.Swal) Swal.fire("Error","Gagal mengambil data","error");
    });
}

function simpan(){
  var url = (save_method === 'add') ? "<?= site_url('admin_produk/add') ?>" : "<?= site_url('admin_produk/update') ?>";
  var formEl = document.getElementById('form_app');
  if (!formEl){
    if (window.Swal) Swal.fire('Gagal','Form tidak ditemukan','error');
    return;
  }
  var fd = new FormData(formEl);

  // sinkronisasi field non-standar
  if ($('#deskripsi').length) fd.set('deskripsi', $('#deskripsi').summernote('code'));
  fd.set('is_active', $('#is_active').is(':checked') ? '1' : '0');

  // hanya kirim file jika benar-benar dipilih
  var gf = $('#gambar_file')[0];
  if (!(gf && gf.files && gf.files.length)){
    fd.delete('gambar_file');
  }
  // Kirim angka murni untuk harga/hpp
  var hargaRaw = extractDigits($('#harga').val());
  var hppRaw   = extractDigits($('#hpp').val());

fd.set('harga', hargaRaw);                 // required → biarkan empty memicu validasi server kalau kosong
fd.set('hpp',   hppRaw ? hppRaw : '');     // optional → kosongkan bila tidak diisi

// Kirim status Andalan
fd.set('recomended', $('#recomended').is(':checked') ? '1' : '0');

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
    if (!r || !r.success){
      if (window.Swal) Swal.fire(r.title||'Gagal', r.pesan||'Terjadi kesalahan', 'error');
      return;
    }
    if (window.Swal) Swal.fire(r.title||'Berhasil', r.pesan||'Data disimpan', 'success');
    $('#full-width-modal').modal('hide');
    reload_table();
  }).fail(function(){
    close_loader();
    if (window.Swal) Swal.fire('Gagal','Tidak dapat mengirim data','error');
  });
}

function hapus_data(){
  var list_id = [];
  $(".data-check:checked").each(function(){ list_id.push(this.value); });
  if (list_id.length === 0){
    if (window.Swal) Swal.fire("Info","Pilih minimal satu data","warning");
    return;
  }

  if (!window.Swal){
    // fallback tanpa Swal
    if (!confirm("Yakin ingin menghapus "+list_id.length+" data?")) return;
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
  }).then(function(res){
    if (!res.isConfirmed) return;
    loader();
    $.ajax({
      url:"<?= site_url('admin_produk/hapus_data') ?>",
      type:"POST",
      data:{id:list_id},
      dataType:"json"
    }).done(function(r){
      close_loader();
      if (!r || !r.success){
        if (window.Swal) Swal.fire(r.title||'Gagal', r.pesan||'Sebagian gagal dihapus', 'error');
      } else {
        if (window.Swal) Swal.fire(r.title||'Berhasil', r.pesan||'Data dihapus', 'success');
        reload_table();
      }
    }).fail(function(){
      close_loader();
      if (window.Swal) Swal.fire("Gagal","Koneksi bermasalah","error");
    });
  });
}

function close_modal(){
  if (!window.Swal){
    $('#full-width-modal').modal('hide');
    return;
  }
  Swal.fire({
    title:"Tutup formulir?",
    text:"Perubahan yang belum disimpan akan hilang.",
    icon:"warning",
    showCancelButton:true,
    confirmButtonText:"Tutup",
    cancelButtonText:"Batal"
  }).then(function(r){
    if (r.value) $('#full-width-modal').modal('hide');
  });
}
function set_andalan(){
  var list_id = [];
  $(".data-check:checked").each(function(){ list_id.push(this.value); });

  if (list_id.length === 0){
    if (window.Swal) Swal.fire("Info","Pilih minimal satu data","warning");
    return;
  }

  // Konfirmasi
  if (!window.Swal){
    if (!confirm("Tandai "+list_id.length+" produk sebagai Andalan?")) return;
  }

  Swal.fire({
    title: "Tandai sebagai Andalan?",
    text: "Akan mengatur andalang pada "+list_id.length+" produk terpilih.",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Ya, Set Andalan",
    cancelButtonText: "Batal"
  }).then(function(res){
    if (!res.isConfirmed) return;

    loader();
    $.ajax({
      url: "<?= site_url('admin_produk/set_andalan') ?>",
      type: "POST",
      data: { id: list_id },
      dataType: "json"
    }).done(function(r){
      close_loader();
      if (!r || !r.success){
        if (window.Swal) Swal.fire(r.title||'Gagal', r.pesan||'Terjadi kesalahan', 'error');
      } else {
        if (window.Swal) Swal.fire(r.title||'Berhasil', r.pesan||'Produk ditandai andalan', 'success');
        reload_table();
      }
    }).fail(function(){
      close_loader();
      if (window.Swal) Swal.fire('Gagal','Koneksi bermasalah','error');
    });
  });
}

/* ===== Formatter Rupiah di input ===== */
/* ==== Formatter tampilan ==== */
function formatRupiahString(rawDigits){
  if (!rawDigits) return '';
  return 'Rp ' + String(rawDigits).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

/* ==== Extract untuk kirim ke server (dari input user) ==== */
function extractDigits(str){
  return (str || '').replace(/[^\d]/g, '');
}

/* ==== NEW: Normalisasi dari DB ke integer rupiah ==== */
/* Menangani "23000.00", "23.000,00", "23,000", dll */
function normalizeMoneyToInteger(v){
  let s = String(v == null ? '' : v).trim();
  if (!s) return '';

  // sisakan angka, titik, koma
  s = s.replace(/[^0-9.,]/g, '');

  if (s.includes('.') && s.includes(',')){
    // contoh "23.000,00" → hilangkan titik ribuan, koma jadi desimal
    s = s.replace(/\./g, '').replace(',', '.');
  } else if (s.includes(',')){
    // bisa ribuan "23,000" atau desimal "23000,00"
    if (/^\d{1,3}(,\d{3})+$/.test(s)) {
      s = s.replace(/,/g, '');
    } else {
      s = s.replace(',', '.');
    }
  } else if (s.includes('.')){
    // bisa ribuan "23.000" atau desimal "23000.00"
    if (/^\d{1,3}(\.\d{3})+$/.test(s)) {
      s = s.replace(/\./g, '');
    } // else biarkan titik sebagai desimal
  }

  let n = parseFloat(s);
  if (!isFinite(n)) n = 0;

  // integer rupiah (tanpa sen)
  return String(Math.round(n));
}

/* ==== Set nilai ke input (untuk EDIT) ==== */
function setRupiahValue($el, numberLike){
  var digits = normalizeMoneyToInteger(numberLike); // ← pakai normalizer baru
  $el.val(digits ? formatRupiahString(digits) : '');
}

/* ==== Binding saat user mengetik (tetap seperti sebelumnya) ==== */
$(document).on('input', '.rupiah', function(){
  var digits = extractDigits(this.value);
  this.value = formatRupiahString(digits);
});

</script>
