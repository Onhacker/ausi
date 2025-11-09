<link rel="stylesheet" href="<?= base_url('assets/admin/datatables/css/dataTables.bootstrap4.min.css'); ?>">

<div class="container-fluid">
  <div class="row"><div class="col-12">
    <div class="page-title-box">
      <div class="page-title-right">
        <ol class="breadcrumb m-0">
          <li class="breadcrumb-item active"><?= $subtitle; ?></li>
        </ol>
      </div>
      <h4 class="page-title"><?= $title; ?></h4>
      <!-- <div class="text-muted small">Grafik harian Cafe, Billiard, Pengeluaran, dan Laba</div> -->
    </div>
  </div></div>

  <!-- HEADER FILTER STATUS + BUTTON -->
  <style>
    .chart-header-card .mini-label {
      font-size:11px;
      line-height:1.4;
      color:#6c757d;
    }
    .chart-header-card h6 {
      font-size:13px;
      font-weight:600;
      color:#222;
      margin-bottom:.25rem;
    }
  </style>

  <div class="card mb-3 chart-header-card">
    <div class="card-body">
      <div class="d-flex flex-wrap justify-content-between align-items-start">
        <div class="mb-2" style="min-width:230px;max-width:100%;">
          <h6 class="mb-1">Filter Aktif</h6>
          <div class="mini-label text-dark" id="infoRange">-</div>
          <div class="mini-label text-dark" id="infoTotal">-</div>
        </div>

        <div class="btn-wrap text-right">
          <button type="button" class="btn btn-blue btn-sm mb-1" id="btn-open-filter">
            <i class="fe-settings"></i> Setel Waktu
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ================== CHART AREA ================== -->
  <div class="row" id="chart-area">
    <div class="col-12 mb-4">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title mb-1">Pendapatan Harian</h5>
          <div class="text-muted small mb-2">Cafe vs Billiard (Rp)</div>
          <div id="chartPendapatan" style="width:100%; height:360px;"></div>
        </div>
      </div>
    </div>

    <div class="col-lg-6 mb-4">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title mb-1">Pengeluaran Harian</h5>
          <div class="text-muted small mb-2">Total biaya keluar per hari (Rp)</div>
          <div id="chartPengeluaran" style="width:100%; height:320px;"></div>
        </div>
      </div>
    </div>

    <div class="col-lg-6 mb-4">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title mb-1">Laba Harian</h5>
          <div class="text-muted small mb-2">(Cafe + Billiard - Pengeluaran)</div>
          <div id="chartLaba" style="width:100%; height:320px;"></div>
        </div>
      </div>
    </div>
  </div>
</div><!-- /.container-fluid -->


<!-- ================== MODAL FILTER ================== -->
<div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h4 class="modal-title">Setel Filter Grafik</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <!-- Kita pakai row sama seperti versi sebelumnya tapi SEKARANG DI MODAL -->
        <div class="form-row">

          <div class="form-group col-md-4">
            <label class="mb-1">Periode</label>
            <select id="preset" class="form-control form-control-sm">
              <option value="today">Hari ini</option>
              <option value="yesterday">Kemarin</option>
              <option value="this_week">Minggu ini</option>
              <option value="this_month" selected>Bulan ini</option> <!-- default -->
              <option value="range">Rentang Tanggal</option>
            </select>

            <small class="text-muted" style="font-size:11px">
              Pilih preset cepat atau pakai Rentang Tanggal.
            </small>
          </div>

          <div class="form-group col-md-4">
            <label class="mb-1" for="dt_from">Dari (Tanggal & Jam)</label>
            <input type="datetime-local" id="dt_from" class="form-control form-control-sm">
            <small class="text-muted" style="font-size:11px">
              Contoh: 2025-10-31 19:00
            </small>
          </div>

          <div class="form-group col-md-4">
            <label class="mb-1" for="dt_to">Sampai (Tanggal & Jam)</label>
            <input type="datetime-local" id="dt_to" class="form-control form-control-sm">
            <small class="text-muted" style="font-size:11px">
              Contoh: 2025-11-01 03:00
            </small>
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

        </div><!-- /.form-row -->
      </div><!-- /.modal-body -->

      <div class="modal-footer d-flex flex-wrap justify-content-between">
        <div class="mb-2">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
            Batal
          </button>
        </div>
        <div class="btn-wrap text-right">
          <button type="button" class="btn btn-warning btn-sm" id="btn-reset">
            <i class="fe-rotate-ccw"></i> Reset
          </button>
          <button type="button" class="btn btn-primary btn-sm ml-2" id="btn-apply">
            <span class="btn-label"><i class="fe-filter"></i></span> Terapkan
          </button>
        </div>
      </div>

    </div>
  </div>
</div>


<!-- Highcharts assets -->
<script src="<?= base_url('/assets/admin/chart/highcharts.js'); ?>"></script>
<script src="<?= base_url('/assets/admin/chart/exporting.js'); ?>"></script>
<script src="<?= base_url('/assets/admin/chart/export-data.js'); ?>"></script>
<script src="<?= base_url('/assets/admin/chart/accessibility.js'); ?>"></script>

<script>
  // Nama bulan Indonesia
const NAMA_BULAN_ID = [
  'Januari','Februari','Maret','April','Mei','Juni',
  'Juli','Agustus','September','Oktober','November','Desember'
];

// Ubah 'YYYY-MM-DD' -> 'D <Nama Bulan> YYYY', mis. '2025-11-09' -> '9 November 2025'
function fmtTanggalIndo(ymd){
  if (!ymd) return '-';
  const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(ymd);
  if (!m) return ymd; // fallback kalau format tak sesuai
  const y = +m[1], mo = +m[2], d = +m[3];
  return d + ' ' + NAMA_BULAN_ID[mo-1] + ' ' + y;
}

// (Opsional, jika mau tampilkan jam juga)
// gabung 'YYYY-MM-DD' + 'HH:MM' -> '9 November 2025 19:00'
function fmtTanggalWaktuIndo(ymd, hhmm){
  const tgl = fmtTanggalIndo(ymd);
  return hhmm ? (tgl + ' ' + hhmm) : tgl;
}

(function(){

  /* =========================================================
   * UTIL TANGGAL/JAM
   * ========================================================= */

  const pad2 = n => String(n).padStart(2,'0');

  function fmtDTLocal(d){
    return d.getFullYear() + '-' + pad2(d.getMonth()+1) + '-' + pad2(d.getDate())
         + 'T' + pad2(d.getHours()) + ':' + pad2(d.getMinutes());
  }

  function startOfDay(d){ return new Date(d.getFullYear(), d.getMonth(), d.getDate(), 0,0,0); }
  function endOfDay(d){   return new Date(d.getFullYear(), d.getMonth(), d.getDate(), 23,59,0); }
  function addDays(d, n){ const x = new Date(d); x.setDate(x.getDate()+n); return x; }

  // Hitung rentang default dari pilihan preset
  function computeRangeDT(preset){
    const now = new Date();
    const today0 = startOfDay(now);

    if (preset === 'today'){
      return { from:startOfDay(today0), to:endOfDay(today0) };
    }
    if (preset === 'yesterday'){
      const y = addDays(today0,-1);
      return { from:startOfDay(y), to:endOfDay(y) };
    }
    if (preset === 'this_week'){
      // cari senin (hari ke-1)
      const dow = today0.getDay(); // 0=Min..6=Sab
      const senin = addDays(today0, -((dow + 6) % 7));
      const minggu = addDays(senin,6);
      return { from:startOfDay(senin), to:endOfDay(minggu) };
    }
    if (preset === 'this_month'){
      const awal  = new Date(today0.getFullYear(), today0.getMonth(), 1, 0,0,0);
      const akhir = new Date(today0.getFullYear(), today0.getMonth()+1, 0, 23,59,0);
      return { from:awal, to:akhir };
    }

    // 'range' → gunakan nilai input kalau valid, fallback hari ini.
    const $df = document.getElementById('dt_from');
    const $dt = document.getElementById('dt_to');
    if ($df.value && $dt.value){
      const f = new Date($df.value);
      const t = new Date($dt.value);
      if (!isNaN(f) && !isNaN(t)){
        return { from:f, to:t };
      }
    }
    return { from:startOfDay(today0), to:endOfDay(today0) };
  }

  // pasang nilai ke input datetime-local + enable/disable kalau preset bukan range
  function setRangeInputsDT(range, enable){
    const $df = document.getElementById('dt_from');
    const $dt = document.getElementById('dt_to');
    $df.value = fmtDTLocal(range.from);
    $dt.value = fmtDTLocal(range.to);
    $df.disabled = !enable;
    $dt.disabled = !enable;
  }

  // jaga supaya dt_to tidak < dt_from
  function normalizeDTInputs(){
    const $df = document.getElementById('dt_from');
    const $dt = document.getElementById('dt_to');
    if ($df.value && $dt.value){
      const vf = new Date($df.value);
      const vt = new Date($dt.value);
      if (!isNaN(vf) && !isNaN(vt) && vt < vf){
        $dt.value = $df.value;
      }
    }
  }

  // KONVERSI nilai UI (preset + dt_from/dt_to) ke param lama (_parse_filter() friendly)
  function getParams(){
    const presetSel = document.getElementById('preset').value;
    const r = computeRangeDT(presetSel);

    // kalau preset==range, pakai persis dt_from/dt_to yg user isi
    const pickedFrom = (presetSel==='range') ? new Date(document.getElementById('dt_from').value) : r.from;
    const pickedTo   = (presetSel==='range') ? new Date(document.getElementById('dt_to').value)   : r.to;

    let F = isNaN(pickedFrom) ? r.from : pickedFrom;
    let T = isNaN(pickedTo)   ? r.to   : pickedTo;
    if (T < F) T = new Date(F); // backup

    const date_from = F.getFullYear() + '-' + pad2(F.getMonth()+1) + '-' + pad2(F.getDate());
    const date_to   = T.getFullYear() + '-' + pad2(T.getMonth()+1) + '-' + pad2(T.getDate());
    const time_from = pad2(F.getHours()) + ':' + pad2(F.getMinutes());
    const time_to   = pad2(T.getHours()) + ':' + pad2(T.getMinutes());

    return {
      preset    : presetSel,
      date_from : date_from,
      date_to   : date_to,
      time_from : time_from,
      time_to   : time_to,
      metode    : document.getElementById('metode').value || 'all',
      mode      : document.getElementById('mode').value   || 'all'
      // status tidak kita expose di modal chart → backend akan pakai 'all'
    };
  }

  /* =========================================================
   * RENDER CHARTS
   * ========================================================= */

  const infoRangeEl = document.getElementById('infoRange');
  const infoTotalEl = document.getElementById('infoTotal');

  function rupiah(n){
    return 'Rp ' + (parseInt(n||0,10)).toLocaleString('id-ID');
  }

  function renderCharts(res){
    // info filter aktif di header kartu atas
    if (res.filter){
       const dfID = fmtTanggalIndo(res.filter.date_from);
  const dtID = fmtTanggalIndo(res.filter.date_to);

  infoRangeEl.textContent =
    'Rentang: ' + dfID +
    ' s/d '   + dtID +
    ' | Mode: '   + (res.filter.mode   || '-') +
    ' | Metode: ' + (res.filter.metode || '-') +
    ' | Status: ' + (res.filter.status || '-');
    }

    if (res.total_rekap){
      infoTotalEl.textContent =
        'Total Cafe: '+rupiah(res.total_rekap.cafe||0)+
        ' · Billiard: '+rupiah(res.total_rekap.billiard||0)+
        ' · Pengeluaran: '+rupiah(res.total_rekap.pengeluaran||0)+
        ' · Laba: '+rupiah(res.total_rekap.laba||0);
    }

    const categories      = res.categories    || [];
    const cafeData        = res.cafe          || [];
    const billiardData    = res.billiard      || [];
    const pengeluaranData = res.pengeluaran   || [];
    const labaData        = res.laba          || [];

    const kpData = res.kursi_pijat || []; // NEW

    const totalPendapatan = cafeData.map(function(v,i){
      const vb = (typeof billiardData[i] !== 'undefined') ? billiardData[i] : 0;
  const vk = (typeof kpData[i] !== 'undefined') ? kpData[i] : 0; // NEW
  return v + vb + vk; // NEW: total termasuk KP
});


    Highcharts.chart('chartPendapatan', {
  title:{ text:null },
  xAxis:{ categories:categories, crosshair:true },
  yAxis:{ min:0, title:{ text:'Rupiah (Rp)' } },
  tooltip:{ shared:true, valueDecimals:0, valuePrefix:'Rp ' },
  credits:{ enabled:false },
  exporting:{ enabled:true },
  series:[
    { name:'Cafe / POS', data:cafeData },
    { name:'Billiard', data:billiardData },
    { name:'Kursi Pijat', data:kpData }, // NEW
    { name:'Total Pendapatan (Cafe+Billiard+KP)', data:totalPendapatan }
  ]
});


    Highcharts.chart('chartPengeluaran', {
      chart:{ type:'column' },
      title:{ text:null },
      xAxis:{ categories:categories, crosshair:true },
      yAxis:{ min:0, title:{ text:'Rupiah (Rp)' } },
      tooltip:{
        shared:true,
        valueDecimals:0,
        valuePrefix:'Rp '
      },
      credits:{ enabled:false },
      exporting:{ enabled:true },
      series:[
        { name:'Pengeluaran', data:pengeluaranData }
      ]
    });

    Highcharts.chart('chartLaba', {
      chart:{ type:'area' },
      title:{ text:null },
      xAxis:{ categories:categories, crosshair:true },
      yAxis:{ title:{ text:'Rupiah (Rp)' } },
      tooltip:{
        shared:true,
        valueDecimals:0,
        valuePrefix:'Rp '
      },
      credits:{ enabled:false },
      exporting:{ enabled:true },
      series:[
        { name:'Laba (Cafe+Billiard-Pengeluaran)', data:labaData }
      ]
    });
  }

  function loadCharts(cbAfter){
    const params = getParams();

    fetch("<?= site_url('admin_laporan/chart_data'); ?>", {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
      body:(new URLSearchParams(params)).toString(),
      credentials:'same-origin'
    })
    .then(r=>r.json())
    .then(res=>{
      if (!res || !res.success){
        console.error('chart_data invalid',res);
        return;
      }
      renderCharts(res);

      // callback setelah render (misal: scroll ke chart)
      if (typeof cbAfter === 'function'){
        cbAfter();
      }
    })
    .catch(err=>{
      console.error('Gagal load chart_data()', err);
    });
  }

  /* =========================================================
   * INTERAKSI MODAL
   * ========================================================= */

  // Buka modal filter
  document.getElementById('btn-open-filter').addEventListener('click', function(){
    $('#filterModal').modal('show');
  });

  // Saat preset diubah di modal → isi dt_from/dt_to dan lock/unlock
  document.getElementById('preset').addEventListener('change', function(){
    const isRange = (this.value === 'range');
    const range = computeRangeDT(this.value);
    setRangeInputsDT(range, isRange);
    normalizeDTInputs();
  });

  // Kalau user edit dt_from/dt_to manual → switch preset jadi 'range'
  document.getElementById('dt_from').addEventListener('change', function(){
    const presetSel = document.getElementById('preset');
    if (presetSel.value !== 'range'){
      presetSel.value = 'range';
      const r = computeRangeDT('range');
      setRangeInputsDT(r, true);
    }
    normalizeDTInputs();
  });
  document.getElementById('dt_to').addEventListener('change', function(){
    const presetSel = document.getElementById('preset');
    if (presetSel.value !== 'range'){
      presetSel.value = 'range';
      const r = computeRangeDT('range');
      setRangeInputsDT(r, true);
    }
    normalizeDTInputs();
  });

  // Reset dari modal → balik "today", mode=all, metode=all
document.getElementById('btn-reset').addEventListener('click', function(){
  document.getElementById('preset').value = 'this_month';
  document.getElementById('metode').value = 'all';
  document.getElementById('mode').value   = 'all';

  const r = computeRangeDT('this_month');
  setRangeInputsDT(r, false);
  normalizeDTInputs();
});


  // Terapkan dari modal:
  // - normalisasi input
  // - loadCharts()
  // - tutup modal
  // - scroll halus ke chart
  document.getElementById('btn-apply').addEventListener('click', function(){
    normalizeDTInputs();

    loadCharts(function(){
      // tutup modal
      $('#filterModal').modal('hide');

      // scroll ke chart area
      const target = document.getElementById('chart-area');
      if (target){
        target.scrollIntoView({
          behavior:'smooth',
          block:'start'
        });
      }
    });
  });

  // INIT pertama kali halaman dibuka:
  // preset default: today
 document.addEventListener('DOMContentLoaded', function(){
  const presetSel = document.getElementById('preset');
  presetSel.value = 'this_month'; // default = bulan ini

  const initRange = computeRangeDT('this_month');
  setRangeInputsDT(initRange, false);
  normalizeDTInputs();

  // load chart awal pakai Bulan ini
  loadCharts();
});


})();
</script>
