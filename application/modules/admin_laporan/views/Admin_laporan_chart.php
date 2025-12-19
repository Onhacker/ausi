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

  /* ==== Style tabel data Highcharts (untuk "View data table") ==== */
  .highcharts-data-table table {
    font-family: Verdana, sans-serif;
    border-collapse: collapse;
    border: 1px solid var(--highcharts-neutral-color-10, #e6e6e6);
    margin: 10px auto;
    text-align: center;
    width: 100%;
    max-width: 500px;
  }

  .highcharts-data-table caption {
    padding: 1em 0;
    font-size: 1.2em;
    color: var(--highcharts-neutral-color-60, #666);
  }

  .highcharts-data-table th {
    font-weight: 600;
    padding: 0.5em;
  }

  .highcharts-data-table td,
  .highcharts-data-table th,
  .highcharts-data-table caption {
    padding: 0.5em;
  }

  .highcharts-data-table thead tr,
  .highcharts-data-table tbody tr:nth-child(even) {
    background: var(--highcharts-neutral-color-3, #f7f7f7);
  }
</style>

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
          <div class="text-muted small mb-2">(Cafe + Billiard + Kursi Pijat + PS - Pengeluaran)</div>

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
<script>
  // ==== Plugin animasi garis + sumbu ala demo Highcharts ====
  (function (H) {
    const animateSVGPath = (svgElem, animation, callback = void 0) => {
      if (!svgElem || !svgElem.element || !svgElem.element.getTotalLength) {
        return;
      }
      const length = svgElem.element.getTotalLength();
      svgElem.attr({
        'stroke-dasharray': length,
        'stroke-dashoffset': length,
        opacity: 1
      });
      svgElem.animate({
        'stroke-dashoffset': 0
      }, animation, callback);
    };

    // Override animasi untuk series line (dan turunan)
    if (H.seriesTypes.line) {
      const protoLine = H.seriesTypes.line.prototype;
      const baseAnimate = protoLine.animate;

      protoLine.animate = function (init) {
        const series = this;
        const animation = H.animObject(
          series.options.animation || series.chart.renderer.globalAnimation
        );

        if (!init && series.graph) {
          // animasi path garis
          animateSVGPath(series.graph, animation);
        } else if (baseAnimate) {
          baseAnimate.apply(series, arguments);
        }
      };
    }

    // Pastikan spline juga pakai animasi yang sama
    if (H.seriesTypes.spline && H.seriesTypes.line) {
      H.seriesTypes.spline.prototype.animate = H.seriesTypes.line.prototype.animate;
    }

    // Animasi axis + label axis + plotLines
    H.addEvent(H.Axis, 'afterRender', function () {
      const axis = this;
      const chart = axis.chart;
      const animation = H.animObject(chart.renderer.globalAnimation);

      if (axis.axisGroup) {
        axis.axisGroup
          .attr({ opacity: 0, rotation: -3, scaleY: 0.9 })
          .animate({ opacity: 1, rotation: 0, scaleY: 1 }, animation);
      }

      if (axis.labelGroup) {
        if (axis.horiz) {
          axis.labelGroup
            .attr({ opacity: 0, rotation: 3, scaleY: 0.5 })
            .animate({ opacity: 1, rotation: 0, scaleY: 1 }, animation);
        } else {
          axis.labelGroup
            .attr({ opacity: 0, rotation: 3, scaleX: -0.5 })
            .animate({ opacity: 1, rotation: 0, scaleX: 1 }, animation);
        }
      }

      if (axis.plotLinesAndBands) {
        axis.plotLinesAndBands.forEach(function (plotLine) {
          if (!plotLine.svgElem || !plotLine.label) {
            return;
          }

          const plAnim = H.animObject(
            (plotLine.options && plotLine.options.animation) || animation
          );

          // label muncul setelah garis selesai di-draw
          plotLine.label.attr({ opacity: 0 });

          animateSVGPath(
            plotLine.svgElem,
            plAnim,
            function () {
              plotLine.label.animate({ opacity: 1 }, plAnim);
            }
          );
        });
      }
    });
  });
</script>
<script src="<?= base_url('/assets/admin/chart/highcharts.js'); ?>"></script>
<script src="<?= base_url('/assets/admin/chart/exporting.js'); ?>"></script>
<script src="<?= base_url('/assets/admin/chart/export-data.js'); ?>"></script>
<script src="<?= base_url('/assets/admin/chart/accessibility.js'); ?>"></script>
<script src="https://code.highcharts.com/themes/adaptive.js"></script>

<script>
// ✅ pakai waktu lokal (WITA dari browser), supaya plotLine "sekarang" pas
Highcharts.setOptions({
  time: { useUTC: false }
});

// ---- helper untuk forecast look ----
function ymdToLocalTs(ymd){
  const m = String(ymd || '').match(/^(\d{4})-(\d{1,2})-(\d{1,2})/);
  if (!m) return NaN;
  return new Date(+m[1], (+m[2]-1), +m[3], 0,0,0,0).getTime(); // local midnight
}
function toPoints(categories, values){
  const cats = categories || [];
  const vals = values || [];
  return cats.map((d,i)=> [ymdToLocalTs(d), Number(vals[i] ?? 0)]);
}
function gradLine(base){
  const c0 = Highcharts.color(base).brighten(-0.15).get('rgb');
  const c1 = Highcharts.color(base).brighten( 0.25).get('rgb');
  return {
    linearGradient: { x1:0, y1:0, x2:0, y2:1 },
    stops: [[0, c0],[1, c1]]
  };
}
function gradFill(base){
  const c0 = Highcharts.color(base).setOpacity(0.28).get('rgba');
  const c1 = Highcharts.color(base).setOpacity(0.00).get('rgba');
  return {
    linearGradient: { x1:0, y1:0, x2:0, y2:1 },
    stops: [[0, c0],[1, c1]]
  };
}
function forecastPlotLine(nowTs){
  return [{
    color: '#4840d6',
    width: 2,
    value: nowTs,
    zIndex: 5,
    dashStyle: 'Dash',
    label: {
      text: '● Waktu sekarang',          // ✅ bullet
      align: 'right',                   // ✅ di pucuk kanan
      verticalAlign: 'top',             // ✅ di pucuk
      y: 10,
      x: -6,
      rotation: 0,
      style: { color:'#333', fontSize:'11px', fontWeight:'600' }
    }
  }];
}

function baseForecastOptions(nowTs, tickPos){
  const hasTickPos = Array.isArray(tickPos) && tickPos.length;

  return {
    chart: { spacingTop: 22 },          // ✅ kasih ruang utk “bullet/legend” di pucuk
    credits: { enabled:false },
    exporting: { enabled:true },
    subtitle: { text: 'Garis titik-titik menandakan data setelah waktu sekarang' },

    // ✅ “bullet list” di pucuk = legend style bulat
    legend: {
      align: 'center',
      verticalAlign: 'top',
      layout: 'horizontal',
      symbolRadius: 6,
      symbolWidth: 12,
      symbolHeight: 12,
      itemStyle: { fontSize:'11px', fontWeight:'600' }
    },

    xAxis: {
      type: 'datetime',
      plotLines: forecastPlotLine(nowTs),

      // ✅ INI KUNCI: paksa semua tanggal tampil (tidak auto-skip)
      tickPositions: hasTickPos ? tickPos : undefined,
      tickInterval: hasTickPos ? undefined : 24 * 3600 * 1000,

      labels: {
        format: '{value:%d-%m-%Y}',     // ✅ lengkap
        rotation: -45,
        step: 1,
        allowOverlap: true
      },
      crosshair: true
    },

    tooltip: {
      shared: true,
      xDateFormat: '%d-%m-%Y',
      valueDecimals: 0,
      valuePrefix: 'Rp '
    },

    plotOptions: {
      series: {
        zoneAxis: 'x',
        zones: [{ value: nowTs }, { dashStyle: 'Dot' }],
        lineWidth: 4,
        marker: {
          enabled: false,
          lineWidth: 2,
          fillColor: '#fff',
          states: { hover: { enabled:true, radius:4 } }
        },
        states: { inactive: { opacity: 1 } },
        animation: { duration: 900 }
      }
    },

    // kalau layar kecil, biar nggak “patah-patah” parah
    responsive: {
      rules: [{
        condition: { maxWidth: 575 },
        chartOptions: { xAxis: { labels: { step: 2 } } }
      }]
    }
  };
}

</script>


<script>
  // Nama bulan Indonesia
/**
 * Ubah string tanggal → "dd-mm-yyyy"
 * Menerima:
 *  - "YYYY-MM-DD"
 *  - "YYYY-MM-DD HH:MM[:SS]"
 *  - "YYYY-MM-DDTHH:MM[:SS]"
 */
function fmtTanggalIndo(input){
  if (!input) return '-';
  const str = String(input).trim();

  // ambil 3 komponen pertama "YYYY-MM-DD" di depan
  const m = str.match(/^(\d{4})-(\d{1,2})-(\d{1,2})/);
  if (!m) return str; // fallback apa adanya kalau tidak cocok

  const y  = m[1];
  const mo = ('0' + m[2]).slice(-2);
  const d  = ('0' + m[3]).slice(-2);

  return d + '-' + mo + '-' + y; // dd-mm-yyyy
}

/**
 * Gabung tanggal + jam → "dd-mm-yyyy HH:MM"
 * - dateStr boleh "YYYY-MM-DD" / "YYYY-MM-DD HH:MM"
 * - timeStr boleh "HH:MM" / "HH:MM:SS" (ambil HH:MM)
 */
function fmtTanggalWaktuIndo(dateStr, timeStr){
  if (!dateStr) return '-';

  const tgl = fmtTanggalIndo(dateStr); // sudah dd-mm-yyyy

  if (!timeStr) return tgl;

  const t = String(timeStr).trim();
  const m = t.match(/^(\d{2}):(\d{2})/);
  const jamMenit = m ? (m[1] + ':' + m[2]) : t;

  return tgl + ' ' + jamMenit;
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
  // ===== info header (punya kamu, tetap) =====
  if (res.filter){
    const dfID = fmtTanggalWaktuIndo(res.filter.date_from, res.filter.time_from);
    const dtID = fmtTanggalWaktuIndo(res.filter.date_to,   res.filter.time_to);

    infoRangeEl.textContent =
      'Rentang: ' + dfID +
      ' s/d '     + dtID +
      ' | Mode: '   + (res.filter.mode   || '-') +
      ' | Metode: ' + (res.filter.metode || '-') +
      ' | Status: ' + (res.filter.status || '-');
  }

  if (res.total_rekap){
    infoTotalEl.textContent =
      'Total Cafe: '+rupiah(res.total_rekap.cafe||0)+
      ' · Billiard: '+rupiah(res.total_rekap.billiard||0)+
      ' · Kursi Pijat: '+rupiah(res.total_rekap.kursi_pijat||0)+
      ' · PS: '+rupiah(res.total_rekap.ps||0)+
      ' · Pengeluaran: '+rupiah(res.total_rekap.pengeluaran||0)+
      ' · Laba: '+rupiah(res.total_rekap.laba||0);
  }

  // ===== data (punya kamu, tetap) =====
  const categories      = res.categories    || [];
  const tickPos = (categories || []).map(ymdToLocalTs).filter(x => !isNaN(x));

  const cafeData        = res.cafe          || [];
  const billiardData    = res.billiard      || [];
  const pengeluaranData = res.pengeluaran   || [];
  const labaData        = res.laba          || [];
  const kpData          = res.kursi_pijat   || [];
  const psData          = res.ps            || [];

  // ===== ubah ke format forecast: [timestamp, value] =====
  const nowTs = Date.now();
  const colors = Highcharts.getOptions().colors || [];

  const ptsCafe     = toPoints(categories, cafeData);
  const ptsBilliard = toPoints(categories, billiardData);
  const ptsKP       = toPoints(categories, kpData);
  const ptsPS       = toPoints(categories, psData);
  const ptsOut      = toPoints(categories, pengeluaranData);
  const ptsProfit   = toPoints(categories, labaData);

  // Total pendapatan per hari (pakai timestamp yg sama)
  const ptsTotalPendapatan = categories.map((d,i)=>{
    const ts = ymdToLocalTs(d);
    const v  = Number(cafeData[i] ?? 0)
             + Number(billiardData[i] ?? 0)
             + Number(kpData[i] ?? 0)
             + Number(psData[i] ?? 0);
    return [ts, v];
  });

  // ===== Chart 1: Pendapatan (forecast style) =====
  Highcharts.chart('chartPendapatan', Highcharts.merge(
    baseForecastOptions(nowTs, tickPos)

    {
      chart: { type: 'spline' },
      title: { text: null },
      yAxis: { min: 0, title: { text: 'Rupiah (Rp)' } },
      legend: { enabled: true },
      series: [
        {
          name: 'Cafe / POS',
          data: ptsCafe,
          color: gradLine(colors[0] || '#4e79a7'),
          marker: { lineColor: (colors[0] || '#4e79a7') }
        },
        {
          name: 'Billiard',
          data: ptsBilliard,
          color: gradLine(colors[1] || '#f28e2b'),
          marker: { lineColor: (colors[1] || '#f28e2b') }
        },
        {
          name: 'Kursi Pijat',
          data: ptsKP,
          color: gradLine(colors[2] || '#e15759'),
          marker: { lineColor: (colors[2] || '#e15759') }
        },
        {
          name: 'PlayStation (PS)',
          data: ptsPS,
          color: gradLine(colors[3] || '#76b7b2'),
          marker: { lineColor: (colors[3] || '#76b7b2') }
        },
        {
          name: 'Total Pendapatan (Cafe+Billiard+KP+PS)',
          data: ptsTotalPendapatan,
          lineWidth: 5,
          color: gradLine(colors[4] || '#59a14f'),
          marker: { lineColor: (colors[4] || '#59a14f') }
        }
      ]
    }
  ));

  // ===== Chart 2: Pengeluaran (dibuat forecast line biar seragam) =====
  Highcharts.chart('chartPengeluaran', Highcharts.merge(
    baseForecastOptions(nowTs, tickPos)

    {
      chart: { type: 'spline' }, // <— sebelumnya column, sekarang forecast style
      title: { text: null },
      yAxis: { min: 0, title: { text: 'Rupiah (Rp)' } },
      legend: { enabled: false },
      series: [{
        name: 'Pengeluaran',
        data: ptsOut,
        color: gradLine(colors[5] || '#edc948'),
        marker: { lineColor: (colors[5] || '#edc948') }
      }]
    }
  ));

  // ===== Chart 3: Laba (area forecast style) =====
  const baseProfit = (colors[6] || '#b07aa1');

  Highcharts.chart('chartLaba', Highcharts.merge(
    baseForecastOptions(nowTs, tickPos)

    {
      chart: { type: 'area' },
      title: { text: null },
      yAxis: { title: { text: 'Rupiah (Rp)' } },
      legend: { enabled: false },
      plotOptions: {
        area: {
          fillColor: gradFill(baseProfit),
          lineColor: baseProfit
        }
      },
      series: [{
        name: 'Laba (Cafe + Billiard + KP + PS - Pengeluaran)',
        data: ptsProfit,
        color: gradLine(baseProfit),
        marker: { lineColor: baseProfit }
      }]
    }
  ));
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
