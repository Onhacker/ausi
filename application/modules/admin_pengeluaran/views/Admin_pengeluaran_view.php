<link href="<?= base_url('assets/admin/datatables/css/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet" type="text/css"/>

<div class="container-fluid">
 <!--  <div class="row">
    <div class="col-12">
    <div class="page-title-box">
      <div class="page-title-right">
        <ol class="breadcrumb m-0">
          <li class="breadcrumb-item active"><?= $subtitle; ?></li>
        </ol>
      </div>
      <h4 class="page-title"><?= $title; ?></h4>
    </div>
  </div>
</div> -->
 <div class="card"><div class="card-body">
   <h4 class="header-title"><?= $subtitle; ?></h4>
  <div class="mb-2 d-flex align-items-center flex-wrap">
    <button type="button" class="btn btn-blue btn-sm mb-2 mr-2" onclick="open_form()">
      <span class="btn-label"><i class="fe-plus-circle"></i></span>Tambah
    </button>

    <button type="button" onclick="reload_pengeluaran('user')" class="btn btn-warning btn-sm mb-2 mr-2">
      <span class="btn-label"><i class="fe-refresh-ccw"></i></span>Refresh
    </button>

    <input type="date" id="filter-from" class="form-control form-control-sm mb-2 mr-2" style="width:160px">
    <input type="date" id="filter-to"   class="form-control form-control-sm mb-2 mr-2" style="width:160px">

    <select id="filter-kategori" class="form-control form-control-sm mr-2 mb-2" style="width:180px">
      <option value="all" selected>Semua Kategori</option>
      <option value="Umum">Umum</option>
      <option value="Bahan Baku">Bahan Baku</option>
      <option value="Operasional">Operasional</option>
      <option value="Gaji">Gaji</option>
      <option value="Lain-lain">Lain-lain</option>
    </select>

    <select id="filter-metode" class="form-control form-control-sm mb-2" style="width:140px">
      <option value="all" selected>Semua Metode</option>
      <option value="cash">Cash</option>
      <option value="qris">QRIS</option>
      <option value="transfer">Transfer</option>
    </select>
  </div>

 
    <table id="table_pengeluaran" class="table table-striped table-bordered w-100">
      <thead>
        <tr>
          <th width="6%">No.</th>
          <th>Tanggal</th>
          <th>Kategori</th>
          <th>Nomor / Ket</th>
          <th>Jumlah</th>
          <th>Metode</th>
          <th>Dibuat</th>
          <th width="14%">Aksi</th>
        </tr>
      </thead>
    </table>
  </div></div>
</div>

<!-- Modal Detail -->
<div id="peng-detail-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="mymodal-title">Detail Pengeluaran</h4>
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

function loader(){ if (window.Swal) Swal.fire({title:"Proses...", allowOutsideClick:false, didOpen:()=>Swal.showLoading()}); }
function close_loader(){ if (window.Swal) Swal.close(); }

function reload_pengeluaran(reason='user'){
  if (!table || isReloading) return;
  isReloading = true;
  table.ajax.reload(function(){ isReloading=false; }, false);
}

$(document).ready(function(){
  table = $('#table_pengeluaran').DataTable({
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
      url:"<?= site_url('admin_pengeluaran/get_data') ?>",
      type:"POST",
      data:function(d){
        d.kategori  = $('#filter-kategori').val() || 'all';
        d.metode    = $('#filter-metode').val() || 'all';
        d.date_from = $('#filter-from').val() || '';
        d.date_to   = $('#filter-to').val()   || '';
      }
    },
    columns:[
      {data:"no",       orderable:false},
      {data:"tanggal"},
      {data:"kategori"},
      {data:"uraian"},
      {data:"jumlah"},
      {data:"metode"},
      {data:"dibuat"},
      {data:"aksi",     orderable:false}
    ],
    order: [],
    rowCallback:function(row, data, displayIndex){
      var api  = this.api();
      var info = api.page.info();
      var idx  = info.start + displayIndex + 1;
      $('td:eq(0)', row).html(idx);
    },
    createdRow: function(row, data){
      if (data && data.id){
        $(row).attr('data-id', data.id).addClass('row-link').css('cursor','pointer');
      }
    }
  });

  // klik baris → detail (kecuali tombol)
  $('#table_pengeluaran tbody').on('click','tr', function(e){
    if ($(e.target).closest('button, a, i').length) return;
    const id = parseInt($(this).attr('data-id')||'0', 10);
    if (id > 0){ show_detail(id); }
  });

  // filter → reload
  $('#filter-kategori, #filter-metode, #filter-from, #filter-to').on('change', function(){
    reload_pengeluaran('user');
  });
});

/* ================ CRUD handlers ================ */
function show_detail(id){
  loader();
  $.getJSON("<?= site_url('admin_pengeluaran/detail/') ?>"+id)
    .done(function(r){
      close_loader();
      if (!r.success){ Swal.fire(r.title||'Gagal', r.pesan||'Tidak bisa memuat detail', 'error'); return; }
      $('.mymodal-title').text(r.title || 'Detail');
      $('#detail-body').html(r.html || '-');
      $('#peng-detail-modal').modal('show');
    })
    .fail(function(){ close_loader(); Swal.fire("Error","Gagal mengambil detail","error"); });
}

function open_form(data){
  const isEdit = !!data;
  const id     = data?.id || '';
  const dt     = data?.tanggal ? data.tanggal.replace(' ','T').slice(0,16) : '';
  const optsKat = ['Umum','Bahan Baku','Operasional','Gaji','Lain-lain'];
  const optsPay = ['cash','qris','transfer'];

  let html = `
  <div class="text-left">
    ${isEdit ? `<input type="hidden" id="f-id" value="${id}">` : ``}
    <div class="form-group">
      <label>Tanggal</label>
      <input type="datetime-local" id="f-tanggal" class="form-control" value="${dt}">
    </div>
    <div class="form-group">
      <label>Kategori</label>
      <select id="f-kategori" class="form-control">
        ${optsKat.map(k=>`<option ${data?.kategori===k?'selected':''} value="${k}">${k}</option>`).join('')}
      </select>
    </div>
    <div class="form-group">
      <label>Metode</label>
      <select id="f-metode" class="form-control">
        ${optsPay.map(k=>`<option ${data?.metode_bayar===k?'selected':''} value="${k}">${k.toUpperCase()}</option>`).join('')}
      </select>
    </div>
    <div class="form-group">
      <label>Jumlah (Rp)</label>
      <input type="text"
       id="f-jumlah"
       class="form-control fmt-idr"
       data-decimals="0"
       placeholder="0" 
       value="${data?.jumlah||''}">
<small class="text-muted">Format otomatis: 1.234.567</small>

    </div>
    <div class="form-group">
      <label>Keterangan</label>
      <textarea id="f-ket" class="form-control" rows="3" placeholder="opsional">${data?.keterangan||''}</textarea>
    </div>
  </div>`;

  Swal.fire({
  title: isEdit ? 'Ubah Pengeluaran' : 'Tambah Pengeluaran',
  html: html,
  focusConfirm:false,
  showCancelButton:true,
  confirmButtonText: isEdit ? 'Simpan' : 'Tambah',
  cancelButtonText: 'Batal',

  // ⬇️ FORMATTER AKTIF SAAT MODAL TERBUKA
  didOpen: () => {
    if (window.IDRFormat) {
      IDRFormat.bindAll(Swal.getHtmlContainer());
    }
  },

  preConfirm: () => {
    const jumlahRaw = (window.IDRFormat
      ? IDRFormat.unformat($('#f-jumlah').val(), 0)
      : ($('#f-jumlah').val()||'').replace(/[^\d]/g,'') );

    const payload = {
      tanggal      : ($('#f-tanggal').val() || '').replace('T',' ')+':00',
      kategori     : $('#f-kategori').val(),
      metode_bayar : $('#f-metode').val(),
      jumlah       : jumlahRaw,          // ⬅️ kirim angka mentah
      keterangan   : $('#f-ket').val()
    };

    const url = isEdit ? "<?= site_url('admin_pengeluaran/update') ?>" 
                       : "<?= site_url('admin_pengeluaran/create') ?>";
    if (isEdit) payload.id = $('#f-id').val();

    return $.post(url, payload, null, 'json').then(res=>{
      if (!res || !res.success) throw new Error(res?.pesan || 'Gagal menyimpan');
      return res;
    }).catch(err=>{
      Swal.showValidationMessage(err.message || 'Gagal');
    });
  }
})
.then(r=>{
    if (!r.isConfirmed) return;
    Swal.fire(r.value.title||'OK', r.value.pesan||'Sukses', 'success');
    reload_pengeluaran('form');
  });
}

function edit_one(id){
  loader();
  $.getJSON("<?= site_url('admin_pengeluaran/detail/') ?>"+id)
    .done(function(r){
      close_loader();
      if (!r.success){ Swal.fire('Gagal', r.pesan||'Tidak bisa memuat data', 'error'); return; }
      // parse ringkas dari HTML? lebih aman panggil endpoint data mentah—biar cepat, ambil lewat get_row JSON kecil
      $.getJSON("<?= site_url('admin_pengeluaran/detail/') ?>"+id, {raw:1})
      .always(function(){ /* abaikan */ });
    })
    .always(function(){
      // agar tak buntu, panggil endpoint data khusus:
      $.ajax({
        url: "<?= site_url('admin_pengeluaran/get_data') ?>",
        type: "POST",
        data: { length:1, start:0, search:{value:id} }
      }).always(function(){
        // fallback: minta data single via API kecil:
        $.getJSON("<?= site_url('admin_pengeluaran_api/get_one/') ?>"+id)
          .done(function(o){ open_form(o); })
          .fail(function(){ Swal.fire('Info','Silakan klik Detail lalu edit manual isian yang sama','info'); });
      });
    });
}

// Versi simpel: langsung panggil modal input dengan payload minimal (untuk demo cukup panggil server detail → bentuk objek)
function edit_one(id){
  loader();
  $.getJSON("<?= site_url('admin_pengeluaran/detail/') ?>"+id)
    .done(function(r){
      close_loader();
      if (!r.success){ Swal.fire('Gagal', r.pesan||'Tidak bisa memuat data', 'error'); return; }
      // Ambil data mentah cepat:
      $.getJSON("<?= site_url('admin_pengeluaran/get_raw/') ?>"+id)
        .done(function(o){ open_form(o); })
        .fail(function(){ Swal.fire('Gagal','Endpoint get_raw belum dibuat. Untuk sekarang pakai Tambah saja.','warning'); });
    })
    .fail(function(){ close_loader(); Swal.fire('Error','Koneksi bermasalah','error'); });
}

function hapus_one(id){
  Swal.fire({title:"Hapus data #"+id+"?", icon:"warning", showCancelButton:true, confirmButtonColor:"#d33"})
  .then(res=>{
    if (!res.isConfirmed) return;
    loader();
    $.post("<?= site_url('admin_pengeluaran/delete') ?>", {id:id}, null, 'json')
      .done(function(r){
        close_loader();
        if (!r.success){ Swal.fire(r.title||'Gagal', r.pesan||'Sebagian gagal', 'error'); }
        else { Swal.fire(r.title, r.pesan, 'success'); reload_pengeluaran('user'); }
      })
      .fail(function(){ close_loader(); Swal.fire('Error','Koneksi bermasalah / error 500','error'); });
  });
}
</script>
<script>
/* ====== IDR live formatter (titik ribuan, koma desimal) ====== */
(function(){
  if (window.__IDR_FMT__) return; window.__IDR_FMT__ = true;

  function countDigits(s){ return (s.match(/\d/g) || []).length; }
  function setCaretByDigits(el, digitsLeft){
    digitsLeft = Math.max(0, digitsLeft|0);
    const v = el.value; let seen = 0, pos = v.length;
    for (let i=0;i<v.length;i++){ if (/\d/.test(v[i])) seen++; if (seen >= digitsLeft){ pos = i+1; break; } }
    try { el.setSelectionRange(pos, pos); } catch(_){}
  }
  function formatIndo(intStr, decStr, decimals){
    intStr = (intStr||'').replace(/\D/g,''); if (intStr === '') intStr = '0';
    intStr = intStr.replace(/^0+(?=\d)/,'').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    if (!decimals || decimals <= 0) return intStr;
    decStr = (decStr||'').replace(/\D/g,'').slice(0, decimals);
    return decStr ? (intStr + ',' + decStr) : intStr;
  }
  function normalize(val, decimals){
    val = (val||'').toString();
    let cleaned = val.replace(/[^\d,]/g,'');
    let [ints, decs=''] = cleaned.split(',');
    if (!decimals || decimals <= 0){ decs=''; }
    const formatted = formatIndo(ints, decs, decimals);
    let raw = formatted.replace(/\./g,'').replace(',', decimals>0 ? '.' : '');
    if (raw === '') raw = '0';
    return { formatted, raw };
  }
  function onInput(e){
    const el = e.target;
    const decimals = parseInt(el.dataset.decimals||'0',10) || 0;
    const oldVal = el.value;
    const caret  = el.selectionStart || 0;
    const digitsLeft = countDigits(oldVal.slice(0, caret));
    const { formatted } = normalize(oldVal, decimals);
    el.value = formatted;
    setCaretByDigits(el, digitsLeft);
    el.dataset.numeric = normalize(formatted, decimals).raw;
  }
  function onFocus(e){ const el = e.target; setTimeout(()=>{ try{ el.select(); }catch(_){}} , 0); }
  function onBlur(e){
    const el = e.target;
    const decimals = parseInt(el.dataset.decimals||'0',10) || 0;
    const { formatted, raw } = normalize(el.value, decimals);
    el.value = formatted; el.dataset.numeric = raw;
  }
  function bindOne(el){
    if (el.__idrBound) return; el.__idrBound = true;
    el.addEventListener('input', onInput);
    el.addEventListener('focus', onFocus);
    el.addEventListener('blur',  onBlur);
    onBlur({target: el});
  }
  function bindAll(ctx){
    (ctx || document).querySelectorAll('input.fmt-idr, textarea.fmt-idr').forEach(bindOne);
  }
  if (document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', ()=> bindAll(document));
  } else { bindAll(document); }

  window.IDRFormat = {
    unformat: function(str, decimals){ return normalize(str, decimals||0).raw; },
    format  : function(numberLike, decimals){ return normalize(String(numberLike ?? ''), decimals||0).formatted; },
    bindAll
  };
})();
</script>
