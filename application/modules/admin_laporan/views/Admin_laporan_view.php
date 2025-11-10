<link href="<?= base_url('assets/admin/datatables/css/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet" type="text/css"/>

<div class="container-fluid">
  <div class="row"><div class="col-12">
    <div class="page-title-box">
      <div class="page-title-right">
        <ol class="breadcrumb m-0">
          <li class="breadcrumb-item active"><?= $subtitle; ?></li>
        </ol>
      </div>
      <h4 class="page-title"><?= $title; ?></h4>
    </div>
  </div></div>

<!-- ========== FILTER BAR (rapi + reset + cetak di bawah) ========== -->
<style>
  /* jarak tombol biar tidak mepet, aman untuk Bootstrap 4 */
  .btn-wrap .btn { margin-right:.5rem; margin-bottom:.5rem; }
  .btn-wrap .btn:last-child { margin-right:0; }
</style>

<div class="card mb-3">
  <div class="card-body">
    <div class="form-row">
      <div class="form-group col-md-4">
        <label class="mb-1">Periode</label>
        <select id="preset" class="form-control form-control-sm">
          <option value="today">Hari ini</option>
          <option value="yesterday">Kemarin</option>
         
          <option value="this_week">Minggu ini</option>
          <option value="this_month">Bulan ini</option>
          <option value="range">Rentang Tanggal</option>
         
        </select>
      </div>

      <!-- GANTI: input tanggal lama -->
      <div class="form-group col-md-4">
        <label class="mb-1" for="dt_from">Dari (Tanggal & Jam)</label>
        <input type="datetime-local" id="dt_from" class="form-control form-control-sm">
      </div>

      <div class="form-group col-md-4">
        <label class="mb-1" for="dt_to">Sampai (Tanggal & Jam)</label>
        <input type="datetime-local" id="dt_to" class="form-control form-control-sm">
      </div>



      <div class="form-group col-md-6">
        <label class="mb-1">Metode Pembayaran</label>
        <select id="metode" class="form-control form-control-sm">
          <option value="all">Semua</option>
          <option value="cash">Cash</option>
          <option value="qris">QRIS</option>
          <option value="transfer">Transfer</option>
        </select>
      </div>

      <div class="form-group col-md-6">
        <label class="mb-1">Mode (POS Cafe)</label>
        <select id="mode" class="form-control form-control-sm">
          <option value="all">Semua</option>
          <option value="walkin">Walk-in</option>
          <option value="dinein">Dine-in</option>
          <option value="delivery">Delivery</option>
        </select>
      </div>

      <div class="form-group col-12 d-flex flex-wrap align-items-center btn-wrap">
        <button type="button" class="btn btn-primary btn-sm" id="btn-apply">
          <span class="btn-label"><i class="fe-filter"></i></span> Terapkan
        </button>
        <button type="button" class="btn btn-secondary btn-sm" id="btn-reset">
          <i class="fe-rotate-ccw"></i> Reset
        </button>
      </div>
    </div>

    <hr class="my-3">

    <!-- Tombol Cetak di bawah, rapi & tidak mepet -->
    <div class="d-flex align-items-center justify-content-between flex-wrap">
      <div class="text-muted mb-2">Cetak Laporan</div>
      <div class="btn-wrap">
        <button class="btn btn-sm btn-primary" id="btn-print-pos"><i class="fe-printer"></i> Cetak Cafee</button>
        <button class="btn btn-sm btn-blue" id="btn-print-bil"><i class="fe-printer"></i> Cetak Billiard</button>
        <button class="btn btn-sm btn-danger" id="btn-print-kursi">
  <i class="fe-printer"></i> Cetak Kursi Pijat
</button>

        <button class="btn btn-sm btn-dark" id="btn-print-kurir">
          <i class="fe-truck"></i> Cetak Lap. Kurir
        </button>

        <button class="btn btn-sm btn-warning" id="btn-print-peng"><i class="fe-printer"></i> Cetak Pengeluaran</button>
        <button class="btn btn-sm btn-success" id="btn-print-laba"><i class="fe-dollar-sign"></i> Cetak Laba</button>
      </div>
    </div>
  </div>
</div>


  <!-- RINGKASAN (Widget Rounded) -->
<div class="row mb-3">
  <!-- Omzet POS -->
  <div class="col-md-6 col-xl-3">
    <div class="widget-rounded-circle card-box">
      <div class="row">
        <div class="col-6">
          <div class="avatar-lg rounded bg-soft-primary">
            <i class="dripicons-wallet font-24 avatar-title text-primary"></i>
          </div>
        </div>
        <div class="col-6">
          <div class="text-right">
            <h3 class="text-dark mt-1"><span id="sum-pos">Rp 0</span></h3>
            <p class="text-muted mb-1 text-truncate">
              Omzet Cafe <small>(<span id="cnt-pos">0</span> trx)</small>
            </p>
          </div>
        </div>
      </div> <!-- end row-->
    </div> <!-- end widget-rounded-circle-->
  </div> <!-- end col-->

  <!-- Omzet Billiard -->
  <div class="col-md-6 col-xl-3">
    <div class="widget-rounded-circle card-box">
      <div class="row">
        <div class="col-6">
          <div class="avatar-lg rounded bg-soft-info">
            <i class="dripicons-store font-24 avatar-title text-info"></i>
          </div>
        </div>
        <div class="col-6">
          <div class="text-right">
            <h3 class="text-dark mt-1"><span  id="sum-bil">Rp 0</span></h3>
            <p class="text-muted mb-1 text-truncate">
              Omzet Billiard <small>(<span id="cnt-bil">0</span> trx)</small>
            </p>
          </div>
        </div>
      </div> <!-- end row-->
    </div> <!-- end widget-rounded-circle-->
  </div> <!-- end col-->

 <!-- Omzet Kursi Pijat -->
<div class="col-md-6 col-xl-3">
  <div class="widget-rounded-circle card-box">
    <div class="row">
      <div class="col-6">
        <div class="avatar-lg rounded bg-soft-pink">
          <i class="dripicons-rocket font-24 avatar-title text-danger"></i>
        </div>
      </div>
      <div class="col-6">
        <div class="text-right">
          <h3 class="text-dark mt-1"><span id="sum-kp">Rp 0</span></h3>
          <p class="text-muted mb-1 text-truncate">
            Kursi Pijat <small>(<span id="cnt-kp">0</span> trx)</small>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>


  <!-- Pengeluaran -->
  <div class="col-md-6 col-xl-3">
    <div class="widget-rounded-circle card-box">
      <div class="row">
        <div class="col-6">
          <div class="avatar-lg rounded bg-soft-warning">
            <i class="dripicons-basket font-24 avatar-title text-warning"></i>
          </div>
        </div>
        <div class="col-6">
          <div class="text-right">
            <h3 class="text-dark mt-1"><span id="sum-peng">Rp 0</span></h3>
            <p class="text-muted mb-1 text-truncate">
              Pengeluaran <small>(<span id="cnt-peng">0</span> trx)</small>
            </p>
          </div>
        </div>
      </div> <!-- end row-->
    </div> <!-- end widget-rounded-circle-->
  </div> <!-- end col-->

  <!-- Laba Bersih -->
  <div class="col-md-6 col-xl-3">
    <div class="widget-rounded-circle card-box">
      <div class="row">
        <div class="col-6">
          <div class="avatar-lg rounded bg-soft-success">
            <i class="dripicons-user-group font-24 avatar-title text-success"></i>
          </div>
        </div>
        <div class="col-6">
          <div class="text-right">
            <h3 class="text-dark mt-1"><span id="sum-laba">Rp 0</span></h3>
            <p class="text-muted mb-1 text-truncate">Laba Bersih</p>
          </div>
        </div>
      </div> <!-- end row-->
    </div> <!-- end widget-rounded-circle-->
  </div> <!-- end col-->


   <!-- Ongkir Delivery -->
<div class="col-md-6 col-xl-3">
  <div class="widget-rounded-circle card-box">
    <div class="row">
      <div class="col-6">
        <div class="avatar-lg rounded bg-soft-secondary">
          <i class="dripicons-location font-24 avatar-title text-secondary"></i>
        </div>
      </div>
      <div class="col-6">
        <div class="text-right">
          <h3 class="text-dark mt-1"><span id="sum-kurir">Rp 0</span></h3>
          <p class="text-muted mb-1 text-truncate">
            Ongkir Delivery <small>(<span id="cnt-kurir">0</span> trx)</small>
          </p>
          <!-- <small id="kurir-method-mini" class="text-muted d-block"></small> -->
          <small id="kurir-note" class="text-muted d-block"></small> 

        </div>
      </div>
    </div>
  </div>
</div>




</div>
<script>
(function(){
  /* ========== COUNTER-UP helper (tanpa plugin eksternal) ========== */
  function easeOutCubic(t){ return 1 - Math.pow(1 - t, 3); }
  function animateNumber(target, to, formatter, duration){
    const $el = (target instanceof jQuery) ? target : $(target);
    const raw = ($el.text() || '').replace(/[^\d]/g,'');
    const fromDOM = raw ? parseInt(raw,10) : 0;
    const from = Number.isFinite($el.data('from')) ? $el.data('from') : fromDOM;
    const D = Math.max(0, duration || 900);
    const start = performance.now();
    function frame(now){
      const t = Math.min(1, (now - start) / D);
      const val = Math.round(from + (to - from) * easeOutCubic(t));
      $el.text(formatter(val));
      if (t < 1) requestAnimationFrame(frame); else $el.data('from', to);
    }
    requestAnimationFrame(frame);
  }

  const IDR = n => 'Rp ' + (parseInt(n||0,10)).toLocaleString('id-ID');
  const INT = n => (parseInt(n||0,10)).toLocaleString('id-ID');

  /* ========== Util tanggal/waktu ========== */
  const pad2 = n => String(n).padStart(2,'0');

  // Format untuk nilai input datetime-local
  function fmtDTLocal(d){
    return d.getFullYear() + '-' + pad2(d.getMonth()+1) + '-' + pad2(d.getDate())
         + 'T' + pad2(d.getHours()) + ':' + pad2(d.getMinutes());
  }
  // Ekstrak date & time parts dari nilai datetime-local (fallback ke default)
  function splitDTLocal(val, defDate, defTime){
    // val contoh: "2025-10-30T08:15"
    if (typeof val === 'string' && val.includes('T')){
      const [d, t] = val.split('T');
      const time = (t || '').slice(0,5); // HH:MM
      if (/^\d{4}-\d{2}-\d{2}$/.test(d) && /^([01]\d|2[0-3]):[0-5]\d$/.test(time)){
        return { date: d, time };
      }
    }
    return { date: defDate, time: defTime };
  }

  // Hitung range preset ke Date object lengkap
  function startOfDay(d){ return new Date(d.getFullYear(), d.getMonth(), d.getDate(), 0, 0, 0); }
  function endOfDay(d){   return new Date(d.getFullYear(), d.getMonth(), d.getDate(), 23, 59, 0); }
  function addDays(d, n){ const x = new Date(d); x.setDate(x.getDate()+n); return x; }

  function computeRangeDT(preset){
    const now = new Date();
    const today = startOfDay(now);

    if (preset === 'today'){
      return { from: startOfDay(today), to: endOfDay(today) };
    }
    if (preset === 'yesterday'){
      const y = addDays(today, -1);
      return { from: startOfDay(y), to: endOfDay(y) };
    }
    if (preset === 'this_week'){
      const dow = today.getDay(); // 0..6 (Min..Sab)
      const senin = addDays(today, -((dow + 6) % 7));
      const minggu = addDays(senin, 6);
      return { from: startOfDay(senin), to: endOfDay(minggu) };
    }
    if (preset === 'this_month'){
      const awal = new Date(today.getFullYear(), today.getMonth(), 1, 0, 0, 0);
      const akhir = new Date(today.getFullYear(), today.getMonth()+1, 0, 23, 59, 0);
      return { from: awal, to: akhir };
    }
    // 'range' → pakai nilai input kalau ada, default hari ini
    const def = { from: startOfDay(today), to: endOfDay(today) };
    const $df = $('#dt_from'), $dt = $('#dt_to');
    if ($df.val() && $dt.val()){
      // kembalikan Date dari nilai input
      const f = new Date($df.val());
      const t = new Date($dt.val());
      return { from: isNaN(f) ? def.from : f, to: isNaN(t) ? def.to : t };
    }
    return def;
  }

  function setRangeInputsDT(range, enable){
    $('#dt_from').val(fmtDTLocal(range.from)).prop('disabled', !enable);
    $('#dt_to').val(fmtDTLocal(range.to)).prop('disabled', !enable);
  }

  // menjaga dt_to >= dt_from
  function normalizeDTInputs(){
    const vf = $('#dt_from').val();
    const vt = $('#dt_to').val();
    if (vf && vt && new Date(vt) < new Date(vf)){
      $('#dt_to').val(vf);
    }
  }

  // querystring builder
  function qs(obj){
    const p = [];
    for (const k in obj){ if (obj[k]!==null && obj[k]!==undefined) p.push(encodeURIComponent(k)+'='+encodeURIComponent(obj[k])); }
    return p.join('&');
  }

  /* ========== Gabungkan params untuk request ========== */
  function getParams(){
    const preset = $('#preset').val();
    // Tampilkan dt sesuai preset, tapi kirim ke backend dalam 4 field lama:
    // date_from, date_to, time_from, time_to
    const r = computeRangeDT(preset);

    // ambil nilai final yang tampil di input (kalau preset=range) atau dari perhitungan (preset lain)
    const fromDT = (preset === 'range') ? new Date($('#dt_from').val()) : r.from;
    const toDT   = (preset === 'range') ? new Date($('#dt_to').val())   : r.to;

    // normalize jika invalid/terbalik
    let F = isNaN(fromDT) ? r.from : fromDT;
    let T = isNaN(toDT)   ? r.to   : toDT;
    if (T < F) T = new Date(F);

    // potong ke HH:MM untuk backend lama
    const date_from = F.getFullYear() + '-' + pad2(F.getMonth()+1) + '-' + pad2(F.getDate());
    const date_to   = T.getFullYear() + '-' + pad2(T.getMonth()+1) + '-' + pad2(T.getDate());
    const time_from = pad2(F.getHours()) + ':' + pad2(F.getMinutes());
    const time_to   = pad2(T.getHours()) + ':' + pad2(T.getMinutes());

    return {
      preset,
      date_from,   // YYYY-MM-DD
      date_to,     // YYYY-MM-DD
      time_from,   // HH:MM
      time_to,     // HH:MM
      metode: $('#metode').val() || 'all',
      mode:   $('#mode').val()   || 'all'
    };
  }

  /* ========== Summary (pakai COUNTER-UP) ========== */
  // Tambah helper kecil (di atas updateSummary)
// Helper mini rincian metode (top-3)
function miniByMethod(byMethod){
  if (!byMethod) return '';
  const arr = Object.entries(byMethod).map(([k,v]) => [String(k||'-').toUpperCase(), parseInt(v||0,10)]);
  arr.sort((a,b)=> b[1]-a[1]);
  return arr.slice(0,3).map(([k,v]) => `${k}: Rp ${v.toLocaleString('id-ID')}`).join(' · ');
}

function updateSummary(){
  const f = getParams();
  $.getJSON("<?= site_url('admin_laporan/summary_json') ?>", f)
    .done(function(r){
      if (!r || !r.success) return;

      // POS / Billiard / Pengeluaran / Laba (tetap)
      const posTotal  = parseInt((r.pos && r.pos.total) || 0, 10);
      const posCount  = parseInt((r.pos && r.pos.count) || 0, 10);
      const bilTotal  = parseInt((r.billiard && r.billiard.total) || 0, 10);
      const bilCount  = parseInt((r.billiard && r.billiard.count) || 0, 10);
      const pengTotal = parseInt((r.pengeluaran && r.pengeluaran.total) || 0, 10);
      const pengCount = parseInt((r.pengeluaran && r.pengeluaran.count) || 0, 10);
      const labaTotal = parseInt((r.laba && r.laba.total) || 0, 10);

      // Kurir = subset POS (informasi saja, tidak mempengaruhi laba)
      const kur      = r.kurir || {};
      const kurTotal = parseInt(kur.total_fee || 0, 10);
      const kurCount = parseInt(kur.count || 0, 10);
      const kurMini  = miniByMethod(kur.by_method || null);
      const isSubset = !!(r.meta && r.meta.kurir_subset_of_pos === true);

      const kp = r.kursi_pijat || {};
      const kpTotal = parseInt(kp.total || 0, 10);
      const kpCount = parseInt(kp.count || 0, 10);

      if ($('#sum-kp').length)  animateNumber('#sum-kp', kpTotal, IDR, 900);
      if ($('#cnt-kp').length)  animateNumber('#cnt-kp', kpCount, INT, 700);

      // ===== Animate angka (pakai guard elemen) =====
      if ($('#sum-pos').length)   animateNumber('#sum-pos',  posTotal,  IDR, 900);
      if ($('#sum-bil').length)   animateNumber('#sum-bil',  bilTotal,  IDR, 900);
      if ($('#sum-peng').length)  animateNumber('#sum-peng', pengTotal, IDR, 900);
      if ($('#sum-laba').length)  animateNumber('#sum-laba', labaTotal, IDR, 900);
      if ($('#sum-kurir').length) animateNumber('#sum-kurir', kurTotal, IDR, 900); // NEW

      if ($('#cnt-pos').length)   animateNumber('#cnt-pos',  posCount,  INT, 700);
      if ($('#cnt-bil').length)   animateNumber('#cnt-bil',  bilCount,  INT, 700);
      if ($('#cnt-peng').length)  animateNumber('#cnt-peng', pengCount, INT, 700);
      if ($('#cnt-kurir').length) animateNumber('#cnt-kurir', kurCount, INT, 700); // NEW

      // Mini rincian & catatan subset
      if ($('#kurir-method-mini').length) $('#kurir-method-mini').text(kurMini || '');
      if ($('#kurir-note').length)        $('#kurir-note').text(isSubset ? 'Termasuk di Omzet Cafe (tidak menambah laba bersih)' : '');
    })
    .fail(function(){
      // (opsional) fallback nol biar nggak blank saat error jaringan
      if ($('#sum-kurir').length) $('#sum-kurir').text('Rp 0');
      if ($('#cnt-kurir').length) $('#cnt-kurir').text('0');
      if ($('#kurir-method-mini').length) $('#kurir-method-mini').text('');
      if ($('#kurir-note').length) $('#kurir-note').text('');
    });
}


  /* ========== Auto-apply, Reset, Cetak ========== */
  let applyTimer = null;
  function autoApply(immediate=false){
    if (applyTimer) clearTimeout(applyTimer);
    applyTimer = setTimeout(updateSummary, immediate ? 0 : 250);
  }

  $(function(){
    // Init nilai dt_from/dt_to dari preset awal
    const initRange = computeRangeDT($('#preset').val());
    setRangeInputsDT(initRange, $('#preset').val()==='range');
    updateSummary();

    // Preset berubah → isi & lock/unlock input
    $('#preset').on('change', function(){
      const v = this.value;
      const r = computeRangeDT(v);
      setRangeInputsDT(r, v==='range');
      autoApply(true);
    });

    // Manual ubah datetime → paksa preset 'range' & normalisasi
    $('#dt_from, #dt_to').on('change input', function(){
      if ($('#preset').val() !== 'range'){
        $('#preset').val('range');
        setRangeInputsDT(computeRangeDT('range'), true);
      }
      normalizeDTInputs();
      autoApply();
    });

    // Dropdown lain
    $('#metode, #mode').on('change', function(){ autoApply(); });

    // Terapkan manual
    $('#btn-apply').on('click', updateSummary);

    // Reset → balik ke 'today' (00:00–23:59), kunci input
    $('#btn-reset').on('click', function(){
      $('#preset').val('today');
      $('#metode').val('all');
      $('#mode').val('all');
      const r = computeRangeDT('today');
      setRangeInputsDT(r, false);
      autoApply(true);
    });

    // Cetak (ikut param yang sama)
    $('#btn-print-pos').on('click', function(){
      window.open("<?= site_url('admin_laporan/print_pos') ?>?"+qs(getParams()), '_blank');
    });
    $('#btn-print-bil').on('click', function(){
      window.open("<?= site_url('admin_laporan/print_billiard') ?>?"+qs(getParams()), '_blank');
    });
    $('#btn-print-peng').on('click', function(){
      window.open("<?= site_url('admin_laporan/print_pengeluaran') ?>?"+qs(getParams()), '_blank');
    });
    $('#btn-print-laba').on('click', function(){
      window.open("<?= site_url('admin_laporan/print_laba') ?>?"+qs(getParams()), '_blank');
    });
    $('#btn-print-kurir').on('click', function(){
      window.open("<?= site_url('admin_laporan/print_kurir') ?>?" + qs(getParams()), '_blank');
    });
    $('#btn-print-kursi').on('click', function(){
  window.open("<?= site_url('admin_laporan/print_kursi_pijat') ?>?" + qs(getParams()), '_blank');
});


  });
})();
</script>
