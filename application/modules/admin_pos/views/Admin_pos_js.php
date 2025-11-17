<script>
/* ==========================================================
 *  POS Admin JS — siap tempel
 *  - Idempotent (hindari "Identifier ... already declared")
 *  - Reload DataTables aman & tidak dobel
 *  - Perbaikan dialog Update Ongkir (parse res.order)
 *  - Kolom dinamis Kitchen/Bar vs Kasir
 *  - Stopwatch "Durasi" jalan live, berhenti saat closed
 * ========================================================== */
(function(){
  // ==== cegah inisialisasi ganda
  if (window.__POS_INIT__) { return; }
  window.__POS_INIT__ = true;

  // ==== state global aman
  // sinkronkan flag IS_KB dari PHP -> window.IS_KB
  try { window.IS_KB = (typeof IS_KB !== 'undefined') ? !!IS_KB : !!window.IS_KB; }
  catch(_) { window.IS_KB = !!window.IS_KB; }

  window.table = window.table || null;
  window.isReloading = !!window.isReloading;
  window.__posReloadReason = window.__posReloadReason || 'init';

  // DataTables jangan munculkan alert default
  if (window.jQuery && $.fn && $.fn.dataTable && $.fn.dataTable.ext) {
    $.fn.dataTable.ext.errMode = 'none';
  }

  // ===== Loader kecil (SweetAlert2)
  function loader(){ if (window.Swal) Swal.fire({title:"Proses...", allowOutsideClick:false, didOpen:()=>Swal.showLoading()}); }
  function close_loader(){ if (window.Swal) Swal.close(); }

  // ===== Reload table dengan reason (hindari spam reload)
  function reload_table(reason='user'){
    window.__posReloadReason = reason;
    if (!window.table || window.isReloading) return false;
    window.isReloading = true;
    try{
      window.table.ajax.reload(function(){
        window.isReloading = false;
      }, false);
    }catch(e){
      window.isReloading = false;
      console.error('reload_table error:', e);
    }
    return true;
  }
  window.reload_table = reload_table;

  // ===== Helpers ringan
  function escapeHtml(s){ return (s||'').replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' }[m])); }
  function escapeAttr(s){ return (s||'').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
  function formatRpInt(n){ n=parseInt(n||0,10); return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g,'.'); }
  function digitsOnly(s){ return (s||'').toString().replace(/[^\d]/g,''); }

 
  // ====== DataTables init di DOM ready
  // GANTI baris: $(function(){
(function initPOSWhenReady(){
  if (!window.jQuery || !(jQuery.fn && (jQuery.fn.DataTable || jQuery.fn.dataTable))) {
    return setTimeout(initPOSWhenReady, 80); // tunggu jQuery + DataTables
  }
  jQuery(function(){

    // util paging info
    $.fn.dataTableExt.oApi.fnPagingInfo = function(o){
      return {
        iStart:o._iDisplayStart, iEnd:o.fnDisplayEnd(),
        iLength:o._iDisplayLength, iTotal:o.fnRecordsTotal(),
        iFilteredTotal:o.fnRecordsDisplay(),
        iPage:Math.ceil(o._iDisplayStart/o._iDisplayLength),
        iTotalPages:Math.ceil(o.fnRecordsDisplay()/o._iDisplayLength)
      };
    };

    // susun kolom dinamis (tanpa checkbox)
    var columns = [
      { data: "no", orderable: false, className: "text-center" },   // rata tengah
      { data: "mode",              className: "text-center" },      // rata tengah
      { data: "meja"}       // rata tengah
    ];

    if (window.IS_KB) {
      columns.push({ data: "pesanan", orderable: false });
    }

    columns.push(
      { data: "waktu",             className: "text-center" },      // rata tengah
      { data: "lama", orderable:false, className: "text-center" },  // rata tengah
      { data: "jumlah",            className: "text-right" },       // rata kanan (Bootstrap 4)
      { data: "status", orderable:true, className: "text-center" },                           // biar tetap pakai styling badge dari server
      { data: "metode", orderable:true,           className: "text-center" }       // rata tengah
    );

    if (!window.IS_KB){
      columns.push({
        data: "aksi",
        orderable: false,
        className: "text-center"                                   // rata tengah
      });
    }

    // bikin <span class="elapsed"> di kolom "Lama" kalau belum ada
    // Buat <span class="elapsed"> di kolom "Lama" + cari start time yang masuk akal
function ensureElapsedSpans(api){
  // index kolom tergantung KB atau bukan
  var idxWaktu = window.IS_KB ? 4 : 3;
  var idxLama  = window.IS_KB ? 5 : 4;

  // parser tanggal yang fleksibel (ISO, dd/mm/yyyy, yyyy-mm-dd)
  function parseDateLoose(s){
    if (!s) return 0;
    s = (s+'').trim();
    // coba ISO-ish
    var t = Date.parse(s.replace(' ', 'T'));
    if (!isNaN(t)) return (t/1000)|0;

    // dd/mm/yyyy HH:mm[:ss]
    var m = s.match(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})\s+(\d{1,2}):(\d{2})(?::(\d{2}))?/);
    if (m){
      var d=+m[1], mo=(+m[2])-1, y=+m[3]; if (y<100) y+=2000;
      var H=+m[4]||0, Mi=+m[5]||0, S=+m[6]||0;
      return (new Date(y,mo,d,H,Mi,S).getTime()/1000)|0;
    }

    // yyyy-mm-dd HH:mm[:ss]
    var m2 = s.match(/(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})(?:[^\d]+(\d{1,2}):(\d{2})(?::(\d{2}))?)?/);
    if (m2){
      var y2=+m2[1], mo2=(+m2[2])-1, d2=+m2[3];
      var H2=+m2[4]||0, Mi2=+m2[5]||0, S2=+m2[6]||0;
      return (new Date(y2,mo2,d2,H2,Mi2,S2).getTime()/1000)|0;
    }
    return 0;
  }

  api.rows({page:'current'}).every(function(){
    var d = this.data() || {};
    var row = this.node();

    // sel "Lama"
    var tdLama  = row && row.cells ? row.cells[idxLama]  : null;
    if (!tdLama) return;

    // kalau sudah ada span.elapsed → skip
    if (tdLama.querySelector('span.elapsed')) return;

    // 1) coba baca dari data JSON (beberapa kemungkinan nama field)
    var start = parseInt(
      (d.start_ts ?? d.start ?? d.start_time ?? d.waktu_unix ?? 0),
      10
    ) || 0;
    var dur   = parseInt(
      (d.dur ?? d.duration ?? d.elapsed ?? d.elapsed_sec ?? 0),
      10
    ) || 0;

    // 2) kalau belum dapat, baca dari sel "Waktu"
    if (!start){
      var tdWaktu = row && row.cells ? row.cells[idxWaktu] : null;
      var waktuTxt = tdWaktu ? tdWaktu.textContent.trim() : '';
      start = parseDateLoose(waktuTxt);
    }

    if (start > 1e12) start = (start/1000)|0; // kalau ms → detik

    // 3) sisipkan span sesuai data yang ada
    if (dur > 0){
      tdLama.innerHTML = '<span class="elapsed" data-dur="'+dur+'"></span>';
    } else if (start > 0){
      tdLama.innerHTML = '<span class="elapsed" data-start="'+start+'"></span>';
    } else {
      // tidak punya info apapun → biarkan isi lama apa adanya
      // (tidak disisipkan apa-apa)
    }
  });
}


    // init
    window.table = $('#datable_pos').DataTable({
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
      processing:true,
      serverSide:true,
      scrollX:true,
      ajax:{
        url:"<?= site_url('admin_pos/get_dataa') ?>",
        type:"POST",
        data:function(d){
          var $fs = $('#filter-status');
          d.status = ($fs.length ? $fs.val() : '') || 'all';
        }
      },
      columns: columns,
      order: [],
      rowCallback:function(row, data, iDisplayIndex){
        var info = this.fnPagingInfo();
        var idx  = info.iPage * info.iLength + (iDisplayIndex + 1);
        $('td:eq(0)', row).html(idx); // kolom No. = index 0
      },
      createdRow: function(row, data){
        if (data && data.id){
          $(row).attr('data-id', data.id).addClass('row-link').css('cursor','pointer');
        }
      },
      drawCallback: function(){
        var api = this.api();
        ensureElapsedSpans(api); // pastikan ada <span.elapsed>
        setTimeout(function(){ if (window.POS_tickOnce) window.POS_tickOnce(); }, 0);
      },
      initComplete: function(){
        var api = this.api();
        ensureElapsedSpans(api); // tick pertama juga disiapkan
        if (window.POS_tickOnce) window.POS_tickOnce();
      },


    });
  

    // pastikan flag reload reset di semua outcome
    window.table.on('xhr.dt error.dt', function(){ window.isReloading = false; });

    // sembunyikan kolom Jumlah & Metode utk Kitchen/Bar (ikut json.hide_price_payment)
    window.table.on('xhr.dt', function (e, settings, json) {
      if (json && json.server_now){
    // server_now (detik) - now browser (detik)
    window.__POS_TDIFF__ = (parseInt(json.server_now,10)||0) - ((Date.now()/1000)|0);
  }
      var hide = !!(json && json.hide_price_payment);
      // Non-KB: [no,mode,meja,waktu,lama,jumlah,status,metode,aksi]
      // KB    : [no,mode,meja,pesanan,waktu,lama,jumlah,status,metode]
      var idxJumlah = window.IS_KB ? 6 : 5;
      var idxMetode = window.IS_KB ? 8 : 7;

      try{
        window.table.column(idxJumlah).visible(!hide);
        window.table.column(idxMetode).visible(!hide);
        const $thead = $('#datable_pos thead');
        $thead.find('th.th-price').css('display', hide ? 'none' : '');
        $thead.find('th.th-method').css('display', hide ? 'none' : '');
      }catch(_){}
    });

    // klik baris -> buka detail
    $('#datable_pos tbody').on('click', 'tr', function(e){
      if ($(e.target).closest('input,button,a,label,.checkbox').length) return;
      const id = parseInt($(this).attr('data-id')||'0', 10);
      if (id > 0){ show_detail(id); }
    });

    // filter status berubah -> reload
    $('#filter-status').on('change', function(){ reload_table('user'); });

    // timer "Durasi" live
    // setInterval(function(){
    //   var now = Math.floor(Date.now()/1000);
    //   $('#datable_pos tbody span.elapsed').each(function(){
    //     var durAttr = this.getAttribute('data-dur');
    //     if (durAttr !== null && durAttr !== ''){
    //       var dur = parseInt(durAttr,10) || 0;
    //       this.textContent = humanizeDuration(dur);
    //       this.classList.add('text-muted');
    //       return;
    //     }
    //     var start = parseInt(this.getAttribute('data-start')||'0',10);
    //     if (start>0){
    //       this.textContent = humanizeDuration(now - start);
    //     }
    //   });
    // }, 1000);
    }); // akhir jQuery DOM ready
})(); // akhir initPOSWhenReady
// === Formatter durasi (global, tanpa jQuery) ===

// === Tick sekali (update semua span.elapsed) ===
// function POS_tickOnce(){
//   var now = Math.floor(Date.now()/1000);
//   var els = document.querySelectorAll('#datable_pos tbody span.elapsed');
//   for (var i=0; i<els.length; i++){
//     var el = els[i];
//     var durAttr = el.getAttribute('data-dur');
//     if (durAttr !== null && durAttr !== ''){
//       var dur = parseInt(durAttr,10) || 0;
//       el.textContent = window.POS_humanizeDuration(dur);
//       el.classList.add('text-muted');
//       continue;
//     }
//     var st = parseInt(el.getAttribute('data-start')||'0',10);
//     if (st > 1e12) st = Math.floor(st/1000); // kalau tak sengaja ms
//     if (st > 0){
//       el.textContent = window.POS_humanizeDuration(now - st);
//     }
//   }
// }

// === Loop 1 detik, tanpa tergantung jQuery ===


// === Re-tick setiap DataTables draw (kalau jQuery ada) ===


  /* ================== Toolbar & Detail ================== */
  function show_detail(id){
    if (!id){ return; }
    loader();
    $.getJSON("<?= site_url('admin_pos/detail/') ?>"+id)
      .done(function(r){
        close_loader();
        if (!r || !r.success){
          Swal.fire(r?.title||'Gagal', r?.pesan||'Tidak bisa memuat detail', 'error'); return;
        }
        $('.mymodal-title').text(r.title || 'Detail');
        $('#detail-body').html(r.html || '-');
        $('#pos-detail-modal').modal('show');
      })
      .fail(function(xhr){
        close_loader();
        Swal.fire("Error","Gagal mengambil detail","error");
        console.error(xhr?.responseText);
      });
  }
  window.show_detail = show_detail;

  function mark_paid_one(id){
    Swal.fire({title:"Tandai paid #"+id+"?", icon:"question", showCancelButton:true})
    .then(res=>{
      if (!res.isConfirmed) return;
      loader();
      $.post("<?= site_url('admin_pos/mark_paid') ?>", {id:[id]})
        .done(function(resp){
          close_loader();
          let r=resp; if (typeof resp==='string'){ try{ r=JSON.parse(resp);}catch(e){} }
          if (!r.success){ Swal.fire(r.title||'Gagal', r.pesan||'Sebagian gagal', 'error'); }
          else { Swal.fire(r.title, r.pesan, 'success'); reload_table('user'); }
        })
        .fail(function(){ close_loader(); Swal.fire('Error','Koneksi bermasalah / error 500','error'); });
    });
  }
  function mark_canceled_one(id){
    Swal.fire({title:"Batalkan #"+id+"?", icon:"warning", showCancelButton:true, confirmButtonColor:"#d33"})
    .then(res=>{
      if (!res.isConfirmed) return;
      loader();
      $.post("<?= site_url('admin_pos/mark_canceled') ?>", {id:[id]})
        .done(function(resp){
          close_loader();
          let r=resp; if (typeof resp==='string'){ try{ r=JSON.parse(resp);}catch(e){} }
          if (!r.success){ Swal.fire(r.title||'Gagal', r.pesan||'Sebagian gagal', 'error'); }
          else { Swal.fire(r.title, r.pesan, 'success'); reload_table('user'); }
        })
        .fail(function(){ close_loader(); Swal.fire('Error','Koneksi bermasalah / error 500','error'); });
    });
  }
  function hapus_data_one(id){
    Swal.fire({title:"Hapus #"+id+"?", icon:"warning", showCancelButton:true, confirmButtonColor:"#d33"})
    .then(res=>{
      if (!res.isConfirmed) return;
      loader();
      $.post("<?= site_url('admin_pos/hapus_data') ?>", {id:[id]})
        .done(function(resp){
          close_loader();
          let r=resp; if (typeof resp==='string'){ try{ r=JSON.parse(resp);}catch(e){} }
          if (!r.success){ Swal.fire(r.title||'Gagal', r.pesan||'Sebagian gagal', 'error'); }
          else { Swal.fire(r.title, r.pesan, 'success'); reload_table('user'); }
        })
        .fail(function(){ close_loader(); Swal.fire('Error','Koneksi bermasalah / error 500','error'); });
    });
  }
  window.mark_paid_one = mark_paid_one;
  window.mark_canceled_one = mark_canceled_one;
  window.hapus_data_one = hapus_data_one;

  /* ================== POS (modal kasir) ================== */
  function add_order(){
    document.getElementById('form-pos')?.reset();
    $('#pos-pay').val('cash');
    $('.pay-btn').removeClass('btn-primary').addClass('btn-outline-secondary');
    $('.pay-btn[data-pay="cash"]').addClass('btn-primary').removeClass('btn-outline-secondary');
    $('#pos-items tbody').empty();
    $('#pos-total').text('Rp 0');
    $('#pos-mode').trigger('change');
    $('#pos-create-modal').modal('show');
  }
  window.add_order = add_order;

  $(function(){
    $('#pos-mode').on('change', function(){
      if (this.value === 'dinein') $('.dinein-only').slideDown(120);
      else $('.dinein-only').slideUp(120);
    });
    $('.pay-btn').on('click', function(){
      $('.pay-btn').removeClass('btn-primary').addClass('btn-outline-secondary');
      $(this).addClass('btn-primary').removeClass('btn-outline-secondary');
      $('#pos-pay').val($(this).data('pay'));
    });

    // Cari produk (debounce)
    let tSearch=null;
    $('#pos-search').on('input', function(){
      clearTimeout(tSearch);
      const q = this.value.trim();
      tSearch = setTimeout(()=> doSearch(q), 300);
    });

    $('#pos-submit').on('click', submitPOS);
  });

  function doSearch(q){
    $('#pos-search-result').html('<div class="text-muted small">Mencari…</div>');
    $.getJSON("<?= site_url('admin_pos/search_products') ?>", {q:q})
      .done(function(r){
        if (!r || !r.success){
          $('#pos-search-result').html('<div class="text-danger small">Gagal memuat produk</div>'); return;
        }
        const arr = r.data || [];
        if (!arr.length){ $('#pos-search-result').html('<div class="text-muted small">Tidak ada produk</div>'); return; }
        let html = '';
        arr.forEach(it=>{
          const harga = parseInt(it.harga||0,10);
          html += `
            <div class="d-flex align-items-center justify-content-between py-1 border-bottom">
              <div>
                <div class="fw-600">${escapeHtml(it.nama||'-')}</div>
                <div class="text-muted small">Rp ${formatRpInt(harga)} · Stok: ${(it.stok??0)}</div>
              </div>
              <div>
                <button type="button" class="btn btn-sm btn-soft-primary" onclick="POS_addItem(${it.id}, '${escapeAttr(it.nama)}', ${harga})"><i class="fe-plus"></i></button>
              </div>
            </div>`;
        });
        $('#pos-search-result').html(html);
      })
      .fail(function(){
        $('#pos-search-result').html('<div class="text-danger small">Koneksi bermasalah</div>');
      });
  }
  window.POS_addItem = function(id,nama,harga){
    const tb = $('#pos-items tbody');
    const row = tb.find(`tr[data-id="${id}"]`);
    if (row.length){
      const $qty = row.find('.pos-qty');
      $qty.val( (parseInt($qty.val()||'1',10)||1)+1 ).trigger('input');
      return;
    }
    const tr = $(`
      <tr data-id="${id}">
        <td>${escapeHtml(nama)}</td>
        <td><input type="number" class="form-control form-control-sm pos-harga text-right" value="${harga}" min="0"></td>
        <td><input type="number" class="form-control form-control-sm pos-qty" value="1" min="1"></td>
        <td class="text-right pos-sub">Rp ${formatRpInt(harga)}</td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="POS_delItem(${id})"><i class="fe-trash-2"></i></button></td>
      </tr>
    `);
    tb.append(tr);
    bindRow(tr);
    recalc();
  };
  window.POS_delItem = function(id){
    $('#pos-items tbody').find(`tr[data-id="${id}"]`).remove();
    recalc();
  };
  function bindRow($tr){
    $tr.find('.pos-qty, .pos-harga').on('input', function(){
      const $r = $(this).closest('tr');
      const q = Math.max(1, parseInt($r.find('.pos-qty').val()||'1',10)||1);
      const h = Math.max(0, parseInt($r.find('.pos-harga').val()||'0',10)||0);
      $r.find('.pos-sub').text('Rp '+formatRpInt(q*h));
      recalc();
    });
  }
  function recalc(){
    let total=0;
    $('#pos-items tbody tr').each(function(){
      const q = Math.max(1, parseInt($(this).find('.pos-qty').val()||'1',10)||1);
      const h = Math.max(0, parseInt($(this).find('.pos-harga').val()||'0',10)||0);
      total += q*h;
    });
    $('#pos-total').text('Rp '+formatRpInt(total));
  }
  function submitPOS(){
    const items=[];
    $('#pos-items tbody tr').each(function(){
      const id = parseInt($(this).data('id'),10);
      const q  = Math.max(1, parseInt($(this).find('.pos-qty').val()||'1',10)||1);
      const h  = Math.max(0, parseInt($(this).find('.pos-harga').val()||'0',10)||0);
      const nm = $(this).find('td:first').text().trim();
      items.push({produk_id:id, qty:q, harga:h, nama:nm});
    });
    if (!items.length){ Swal.fire('Info','Pilih minimal 1 item','warning'); return; }

    const fd = new FormData(document.getElementById('form-pos'));
    fd.append('items', JSON.stringify(items));

    loader();
    $.ajax({
      url:"<?= site_url('admin_pos/create_order') ?>",
      type:"POST", data:fd, processData:false, contentType:false, dataType:'json'
    }).done(function(r){
      close_loader();
      if (!r || !r.success){
        Swal.fire(r?.title||'Gagal', r?.pesan||'Tidak bisa membuat order', 'error'); return;
      }
      if (r.redirect){ window.location = r.redirect; return; }
      Swal.fire(r.title, r.pesan, 'success');
      $('#pos-create-modal').modal('hide');
      reload_table('user');
    }).fail(function(xhr){
      close_loader();
      Swal.fire('Error','Koneksi bermasalah / error 500','error');
      console.error('create_order fail:', xhr?.responseText);
    });
  }

  /* ================== PRINT INLINE (iframe tunggal) ================== */
  (function(){
    let printFrame = null;
    function ensurePrintFrame(){
      if (printFrame && document.body.contains(printFrame)) return printFrame;
      printFrame = document.createElement('iframe');
      printFrame.id = 'print-frame';
      printFrame.style.position = 'fixed';
      printFrame.style.right = '0';
      printFrame.style.bottom = '0';
      printFrame.style.width = '0';
      printFrame.style.height = '0';
      printFrame.style.border = '0';
      printFrame.style.visibility = 'hidden';
      document.body.appendChild(printFrame);
      return printFrame;
    }
    function openAndPrint(url){
      const frame = ensurePrintFrame();
      frame.onload = null;
      frame.onload = function(){
        try{
          const cw = frame.contentWindow;
          cw.focus();
          setTimeout(function(){ cw.print(); }, 50);
        }catch(e){
          console.error('Gagal print frame:', e);
          window.location.href = url.replace('&embed=1','');
        }
      };
      frame.src = url;
    }

window.printStrukInline = function(orderId, paper, autoClose, useRawBT, opts){
  try{
    var p = (paper === '80') ? '80' : '58';
    if (typeof autoClose === 'undefined') autoClose = true;   // default: tutup otomatis
    if (typeof useRawBT  === 'undefined') useRawBT  = false;  // default: HTML print
    opts = opts || {};

    var base = "<?= site_url('admin_pos/print_struk_termal/') ?>" + orderId +
               "?paper=" + p + "&embed=1&_=" + Date.now();

    if (useRawBT){
      // === MODE RAWBT ===
      var url = base + "&rawbt=1" + (autoClose ? "&autoclose=1" : "");
      if (opts.cutn)    url += "&cutn=" + encodeURIComponent(opts.cutn);         // ex: 4..7
      if (opts.trail!=null) url += "&trail=" + encodeURIComponent(opts.trail);   // ex: 0..3
      if (opts.cutmode) url += "&cutmode=" + encodeURIComponent(opts.cutmode);   // 'partial'|'full'
      if (opts.extra)   url += "&" + opts.extra;                                 // passthrough optional

      var w1 = window.open(url, "print_"+orderId,
        "width=520,height=760,menubar=0,location=0,toolbar=0,status=0");
      if (!w1) window.location.href = url; // popup diblok → redirect
      return;
    }

    // === MODE HTML (browser print) ===
    var urlHtml = base + "&autoprint=1" + (autoClose ? "&autoclose=1" : "");
    if (typeof openAndPrint === 'function'){
      openAndPrint(urlHtml);
    } else {
      // fallback: autoprint manual saat ready
      var w2 = window.open(urlHtml, "print_"+orderId,
        "width=520,height=760,menubar=0,location=0,toolbar=0,status=0");
      if (!w2) { window.location.href = urlHtml; return; }
      var tried = false, iv = setInterval(function(){
        if (!w2 || w2.closed) { clearInterval(iv); return; }
        try{
          if (w2.document && w2.document.readyState === 'complete' && !tried){
            tried = true; clearInterval(iv);
            try { w2.focus(); w2.print(); if (autoClose) w2.close(); } catch(e){}
          }
        }catch(e){}
      }, 250);
    }
  }catch(err){
    console.error(err);
    var fallbackUrl = "<?= site_url('admin_pos/print_struk_termal/') ?>" + orderId + "?paper=" + ((paper==='80')?'80':'58');
    window.location.href = fallbackUrl;
  }
};


    window.printStrukInlinex = function(orderId, paper){
      try{
        paper = (paper === '80') ? '80' : '58';
        const url = "<?= site_url('admin_pos/print_struk_termalx/') ?>"
                    + orderId + "?paper=" + paper + "&embed=1&_=" + Date.now();
        openAndPrint(url);
      }catch(err){
        console.error(err);
        const fallbackUrl = "<?= site_url('admin_pos/print_struk_termalx/') ?>" + orderId + "?paper=" + paper;
        window.location.href = fallbackUrl;
      }
      reload_table('print');
    };
  })();

  /* ================== Update Ongkir (Swal) — FIXED ================== */
  (function(){
    const idrFmt = new Intl.NumberFormat('id-ID');

    function formatOnBlur(el){
      const d = digitsOnly(el.value);
      el.value = d ? idrFmt.format(parseInt(d,10)) : '';
    }
    function stripOnFocus(el){
      el.value = digitsOnly(el.value);
      try{ el.select(); }catch(_){}
    }
    function filterDigitsKeepCaret(e){
      const el = e.target;
      const before = el.value;
      const pos = el.selectionStart || 0;
      const left = before.slice(0,pos);
      const removedLeft = (left.match(/[^\d]/g) || []).length;
      const only = before.replace(/[^\d]/g,'');
      if (only !== before){
        el.value = only;
        const newPos = Math.max(0, pos - removedLeft);
        try{ el.setSelectionRange(newPos, newPos); }catch(_){}
      }
    }

    window.showSetOngkir = function(orderId, currentFeeStr){
      const startDigits    = digitsOnly(currentFeeStr || '0');
      const startFormatted = startDigits ? idrFmt.format(parseInt(startDigits,10)) : '';

      Swal.fire({
        title: 'Update Ongkir',
        input: 'text',
        inputLabel: 'Ongkir (Rp)',
        inputValue: startFormatted,
        inputAttributes: {
          inputmode: 'numeric',
          autocomplete: 'off',
          autocapitalize: 'off',
          spellcheck: 'false'
        },
        focusConfirm: false,
        returnFocusOnClose: false,
        showCancelButton: true,
        confirmButtonText: 'Simpan',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        showLoaderOnConfirm: true,

        didOpen: () => {
          const $in = Swal.getInput();
          $in.addEventListener('focus', () => stripOnFocus($in));
          $in.addEventListener('input',  filterDigitsKeepCaret);
          $in.addEventListener('blur',   () => formatOnBlur($in));
          setTimeout(()=>{ try{$in.focus(); $in.select();}catch(_){}} , 50);
        },

        preConfirm: () => {
          const digits = digitsOnly(Swal.getInput().value);
          if (!digits || isNaN(parseInt(digits,10))){
            Swal.showValidationMessage('Masukkan ongkir yang valid (angka saja)');
            return false;
          }
          return $.ajax({
            url: '<?= site_url('admin_pos/set_ongkir') ?>',
            method: 'POST',
            dataType: 'json',
            data: { id: orderId, fee: digits }
          }).then(res => {
            if (!res || !res.success){
              throw new Error((res && res.pesan) || 'Gagal memperbarui ongkir');
            }
            // ONLY return bagian order agar mudah dipakai di then()
            return res.order || res;
          }).catch(err => {
            Swal.showValidationMessage(err.message || 'Gagal memperbarui ongkir');
          });
        },

        allowOutsideClick: () => !Swal.isLoading()
      }).then(result => {
        if (!result.isConfirmed) return;

        const o = result.value || {};
        // siapkan link struk & WA
        const links = o.links || {};
        const struk_url = links.customer_receipt || links.admin_print_58 || links.admin_print_80 || '';
        const phoneDigits = digitsOnly(o.customer_phone||'');
        const wa_text = o.wa_preview || '';
        const wa_link = phoneDigits ? ('https://wa.me/'+phoneDigits+'?text='+encodeURIComponent(wa_text)) : null;

        // refresh table & modal detail (kalau sedang terbuka)
        if (typeof reload_table === 'function') reload_table('set-ongkir');
        if ($('#pos-detail-modal').hasClass('show') && typeof show_detail === 'function'){
          show_detail(orderId);
        }

        const idr = n => 'Rp ' + idrFmt.format(parseInt(n||0,10));
        const sumHTML = `
          <div style="text-align:left">
            ${o.nomor ? `<div><b>Order</b>: ${o.nomor}</div>` : ``}
            ${o.nama  ? `<div><b>Nama</b>: ${escapeHtml(o.nama)}</div>`   : ``}
            ${o.customer_phone ? `<div><b>HP</b>: ${escapeHtml(o.customer_phone)}</div>` : ``}
            ${o.alamat_kirim ? `<div style="margin-top:4px"><b>Alamat</b>:<br>${escapeHtml(o.alamat_kirim)}</div>`:``}
            <hr style="margin:8px 0">
            ${'subtotal' in o ? `<div>Subtotal <b class="float-right">${idr(o.subtotal)}</b></div>` : ``}
            ${'delivery_fee' in o ? `<div>Ongkir <b class="float-right">${idr(o.delivery_fee)}</b></div>` : ``}
            ${(o.kode_unik>0) ? `<div>Kode Unik <b class="float-right">${idr(o.kode_unik)}</b></div>` : ``}
            ${'grand_total' in o ? `<div style="margin-top:4px"><b>Total Bayar</b> <b class="float-right">${idr(o.grand_total)}</b></div>` : ``}
            <div style="display:flex;gap:8px;margin-top:10px;flex-wrap:wrap">
              ${struk_url ? `<a class="swal2-styled" style="background:#3b82f6" target="_blank" rel="noopener" href="${struk_url}">Lihat Struk</a>` : ``}
              ${wa_link   ? `<a class="swal2-styled" style="background:#22c55e" target="_blank" rel="noopener" href="${wa_link}">Kirim WA</a>` : ``}
            </div>
          </div>`;
        Swal.fire({ icon:'success', title:'Ongkir diperbarui', html: sumHTML, confirmButtonText:'Tutup' });
      });
    };
  })();

})(); // end IIFE
</script>
<script>
/* ===== Meja module (BS4/BS5-safe) — V3 ===== */
(function(){
  if (window.__MEJA_V3__) return; window.__MEJA_V3__ = true;

  var $modal = $('#meja-modal');
  var $list  = $('#meja-list');
  var $empty = $('#meja-empty');
  var $count = $('#meja-count');
  var $q     = $('#meja-q');
  var tDeb   = null;

  function esc(s){ return (s||'').toString().replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' }[m])); }
  function toast(msg){ if (window.Swal) Swal.fire({toast:true, position:'top', timer:1300, showConfirmButton:false, icon:'success', title:msg}); }

  function render(items){
    $list.empty();
    if (!items || !items.length){ $empty.show(); $count.text('0 meja'); return; }
    $empty.hide(); $count.text(items.length + ' meja');

    items.forEach(function(it){
      var kode = esc(it.kode), nama = esc(it.nama), area = esc(it.area||'');
      var kap  = parseInt(it.kapasitas||0,10)||0;
      var link = it.link || '#';
      var qr   = it.qrcode || '';

      var $col = $('<div class="col-md-6 col-lg-4 p-1"></div>');
      var $card = $(`
        <div class="meja-card">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="meja-kode">${nama}</div>
              <div class="meja-meta">${kode}</div>
            </div>
            <span class="badge badge-success">aktif</span>
          </div>
          <div class="meja-actions mt-2">
            <a href="${link}" target="_blank" rel="noopener" class="btn btn-sm btn-primary"><i class="fe-external-link"></i> Buka</a>
            ${qr ? `<button type="button" class="btn btn-sm btn-outline-info" data-qr="${esc(qr)}"><i class="fe-image"></i> QR</button>` : ''}
          </div>
        </div>
      `);
      $col.append($card); $list.append($col);
    });
  }

  function loadMeja(q){
    // tampilkan placeholder loading
    $list.html('<div class="col-12 text-center text-muted py-3">Memuat…</div>');
    $.getJSON("<?= site_url('admin_pos/list_meja') ?>", { q: q||'' })
      .done(function(r){
        if (!r || !r.success){ $list.html('<div class="col-12 text-center text-danger py-3">Gagal memuat data</div>'); return; }
        render(r.data||[]);
      })
      .fail(function(xhr){
        var msg = 'Koneksi bermasalah';
        if (xhr && xhr.status) msg += ' ('+xhr.status+')';
        $list.html('<div class="col-12 text-center text-danger py-3">'+msg+'</div>');
        console.error('list_meja error:', xhr && xhr.responseText);
      });
  }

  // === expose ke global utk dipanggil tombol toolbar ===
  window.openMejaModal = function(){
    // show modal (helper BS4/BS5)
    if (typeof showModalById === 'function') showModalById('meja-modal', {backdrop:'static', keyboard:true});
    else { // fallback minimal
      var el = document.getElementById('meja-modal');
      if (el){ el.style.display='block'; el.classList.add('show'); document.body.classList.add('modal-open'); }
    }
    // load list + focus input
    setTimeout(function(){
      loadMeja($q.val());
      try{ document.getElementById('meja-q').focus(); }catch(_){}
    }, 50);
  };

  // cari (debounce)
  $q.on('input', function(){
    clearTimeout(tDeb);
    var v = this.value.trim();
    tDeb = setTimeout(function(){ loadMeja(v); }, 250);
  });

  // aksi copy/QR (delegasi)
  $list.on('click', 'button[data-copy]', function(){
    var txt = $(this).data('copy')||'';
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(txt).then(function(){ toast('Link disalin'); });
    } else {
      var ta = document.createElement('textarea'); ta.value = txt; document.body.appendChild(ta);
      ta.select(); try{ document.execCommand('copy'); }catch(_){}
      document.body.removeChild(ta); toast('Link disalin');
    }
  });
  $list.on('click', 'button[data-qr]', function(){
    var src = $(this).data('qr')||'';
    if (!src) return;
    if (window.Swal){
      Swal.fire({title:'QR Code Meja', html:'<img src="'+esc(src)+'" alt="QR" style="max-width:100%">', confirmButtonText:'Tutup'});
    } else { window.open(src, '_blank'); }
  });

  // (opsional) muat ulang saat modal dibuka via event BS
  $modal.on('shown.bs.modal', function(){
    loadMeja($q.val());
    try{ document.getElementById('meja-q').focus(); }catch(_){}
  });

  // simpan loader ke global untuk debugging jika perlu
  window._meja_load = loadMeja;
})();
</script>
<script>
/* ===== Bootstrap modal helpers (BS4 & BS5 safe) ===== */
(function(){
  if (window.__BS_MODAL_HELPERS__) return; window.__BS_MODAL_HELPERS__ = true;

  function bsShow(el, options){
    if (!el) return false;

    // Bootstrap 5 (tanpa jQuery)
    if (window.bootstrap && window.bootstrap.Modal){
      var Modal = window.bootstrap.Modal;
      if (typeof Modal.getOrCreateInstance !== 'function'){
        Modal.getOrCreateInstance = function(element, opts){
          return (Modal.getInstance && Modal.getInstance(element)) || new Modal(element, opts || {});
        };
      }
      var inst = Modal.getOrCreateInstance(el, options || {backdrop:'static', keyboard:true});
      inst.show();
      return true;
    }

    // Bootstrap 4 (jQuery plugin)
    if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function'){
      jQuery(el).modal(Object.assign({backdrop:'static', keyboard:true, show:true}, options||{}));
      return true;
    }

    // Fallback (tanpa Bootstrap JS)
    el.style.display = 'block';
    el.classList.add('show');
    document.body.classList.add('modal-open');

    // bikin backdrop tipis biar UX enak
    if (!document.querySelector('.modal-backdrop')) {
      var bd = document.createElement('div');
      bd.className = 'modal-backdrop show';
      bd.style.background = 'rgba(0,0,0,.5)';
      document.body.appendChild(bd);
    }
    return true;
  }

  function bsHide(el){
    if (!el) return;

    if (window.bootstrap && window.bootstrap.Modal){
      var Modal = window.bootstrap.Modal;
      var inst = (Modal.getInstance && Modal.getInstance(el)) || null;
      if (inst && inst.hide){ inst.hide(); return; }
    }
    if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function'){
      jQuery(el).modal('hide'); return;
    }

    // Fallback
    el.style.display = 'none';
    el.classList.remove('show');
    document.body.classList.remove('modal-open');
    var bd = document.querySelector('.modal-backdrop');
    if (bd) bd.parentNode.removeChild(bd);
  }

  window.showModalById = function(id, options){
    var el = (typeof id === 'string') ? document.getElementById(id) : id;
    bsShow(el, options);
  };
  window.hideModalById = function(id){
    var el = (typeof id === 'string') ? document.getElementById(id) : id;
    bsHide(el);
  };
})();
</script>
<script>
// ===== POS ticker — single source of truth =====
(function(){
  if (window.__POS_TICK__) return; window.__POS_TICK__ = true;

  function H(sec){
    sec = sec|0; if (sec < 0) sec = 0;
    var d = (sec/86400)|0; sec -= d*86400;
    var h = (sec/3600)|0;  sec -= h*3600;
    var m = (sec/60)|0;    sec -= m*60;
    var out = [];
    if (d) out.push(d+'h');
    if (h) out.push(h+'j');
    if (m) out.push(m+'m');
    out.push(sec+'d');
    return out.join(' ');
  }
  window.POS_humanizeDuration = H;

  function tickOnce(){
    // var now = (Date.now()/1000)|0;
    var now = ((Date.now()/1000)|0) + (window.__POS_TDIFF__||0);
    var els = document.querySelectorAll('#datable_pos tbody span.elapsed');
    for (var i=0, n=els.length; i<n; i++){
      var el = els[i], txt;
      var durAttr = el.getAttribute('data-dur');
      if (durAttr !== null && durAttr !== '') {
        var dur = parseInt(durAttr,10) || 0;
        txt = H(dur);
        if (!el.dataset.muted){ el.classList.add('text-muted'); el.dataset.muted = '1'; }
      } else {
        var st = parseInt(el.getAttribute('data-start')||'0',10) || 0;
        if (st > 1e12) st = (st/1000)|0; // kalau server kirim ms
        txt = (st>0) ? H(now - st) : '—';
      }
      // paksa rewrite text
      if (el.textContent !== txt) el.textContent = txt;
    }
  }
  window.POS_tickOnce = tickOnce;

  // 1Hz (pause saat tab hidden)
  var last = 0;
  function loop(ts){
    if (document.visibilityState !== 'hidden' && ts - last >= 950){ tickOnce(); last = ts; }
    requestAnimationFrame(loop);
  }
  requestAnimationFrame(loop);

  // Re-tick setiap redraw/XHR DataTables
  if (window.jQuery){
    jQuery(document).on('draw.dt xhr.dt', '#datable_pos', tickOnce);
  }
})();
</script>


