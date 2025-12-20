<!-- ================== (OPSIONAL) CSS DASHBOARDS (boleh taruh di <head>) ================== -->
<link rel="stylesheet" href="https://code.highcharts.com/css/highcharts.css">
<link rel="stylesheet" href="https://code.highcharts.com/dashboards/css/dashboards.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@highcharts/grid-pro/css/grid-pro.css">

<style>
  /* Colors */
  :root{
    --highcharts-dashboards-green:#20d17e;
    --highcharts-dashboards-orange:#feaa61;
    --highcharts-dashboards-green-transparent:#20d17e30;
  }

  /* Wrapper */
  .highcharts-dashboards,
  .highcharts-dashboards-wrapper{
    background-color: var(--highcharts-neutral-color-3);
  }

  /* Override warna series (yang lain tetap dari highcharts.css) */
  .highcharts-color-0{ stroke: var(--highcharts-dashboards-green); fill: var(--highcharts-dashboards-green); }
  .highcharts-color-1{ stroke: var(--highcharts-dashboards-orange); fill: var(--highcharts-dashboards-orange); }

  /* Rows & cells (kartu) */
  .highcharts-dashboards-row#row-1{
    border-radius:20px;
    padding:10px;
  }
  .highcharts-dashboards-cell > .highcharts-dashboards-component{
    background-color: var(--highcharts-background-color);
    border-radius:20px;
    padding:10px;
    text-align:left;
  }

  /* Title/subtitle/value KPI */
  .highcharts-dashboards-component-title{
    padding-left:10px;
    font-size:.8rem;
    font-weight:100;
  }
  .highcharts-dashboards-component-subtitle{
    font-size:.8rem;
    font-weight:100;
    padding-left:20px;
    color: var(--highcharts-dashboards-green);
  }
  .highcharts-dashboards-component-kpi-value{
    padding-left:20px;
    font-weight:bold;
  }

  /* Grid theme */
  .hcg-custom-theme{
    --hcg-padding:10px;
    --hcg-row-border-width:1px;
    --hcg-header-background:transparent;
  }

  /* HTML cell (filter) */
  #btn-open-filter{
    border:none;
    border-radius:10px;
    padding:10px 14px;
    background-color: var(--highcharts-dashboards-orange);
    cursor:pointer;
    color:#1f1f1f;
    font-weight:600;
  }
  .dh-mini{
    font-size:12px;
    line-height:1.4;
    color: var(--highcharts-neutral-color-80);
    padding-left: 10px;
    margin: 2px 0;
    word-break: break-word;
  }
  .dh-title{
    padding-left:10px;
    font-size: 14px;
    font-weight: 700;
    margin: 0 0 6px 0;
  }

  /* Gradient area (laba) */
  #gradient-0 stop{ stop-color: var(--highcharts-dashboards-green); }
  #gradient-0 stop[offset="0"]{ stop-opacity:.75; }
  #gradient-0 stop[offset="1"]{ stop-opacity:0; }

  /* Heights & responsive cells */
  #dashboard-row-1-cell-1,
  #dashboard-row-1-cell-2,
  #dashboard-row-1-cell-3{ height:160px; }

  #dashboard-row-2-cell-1{ height:360px; }

  #dashboard-row-3-cell-1,
  #dashboard-row-3-cell-2,
  #dashboard-row-3-cell-3{ height:260px; }

  #dashboard-row-3-cell-1,
  #dashboard-row-3-cell-2{ flex:1 1 20%; }
  #dashboard-row-3-cell-3{ flex:1 1 60%; }

  /* LARGE */
  @media (max-width:1200px){
    #dashboard-row-1-cell-1,
    #dashboard-row-1-cell-2,
    #dashboard-row-1-cell-3{ flex:1 1 33.333%; }

    #dashboard-row-3-cell-1,
    #dashboard-row-3-cell-2{ flex:1 1 20%; }
    #dashboard-row-3-cell-3{ flex:1 1 60%; }
  }
  /* MEDIUM */
  @media (max-width:992px){
    #dashboard-row-1-cell-1,
    #dashboard-row-1-cell-2,
    #dashboard-row-1-cell-3{ flex:1 1 50%; }

    #dashboard-row-3-cell-1,
    #dashboard-row-3-cell-2{ flex:1 1 50%; }
    #dashboard-row-3-cell-3{ flex:1 1 100%; }
  }
  /* SMALL */
  @media (max-width:576px){
    #dashboard-row-1-cell-1,
    #dashboard-row-1-cell-2,
    #dashboard-row-1-cell-3{ flex:1 1 100%; }

    #dashboard-row-3-cell-1,
    #dashboard-row-3-cell-2,
    #dashboard-row-3-cell-3{ flex:1 1 100%; }
  }
</style>

<div class="container-fluid">
  <div class="row">
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
  </div>

  <!-- DASHBOARDS CONTAINER -->
  <div id="dash_container"></div>
</div>

<!-- ================== MODAL FILTER (tetap kamu pakai) ================== -->
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
        <div class="form-row">

          <div class="form-group col-md-4">
            <label class="mb-1">Periode</label>
            <select id="preset" class="form-control form-control-sm">
              <option value="today">Hari ini</option>
              <option value="yesterday">Kemarin</option>
              <option value="this_week">Minggu ini</option>
              <option value="this_month" selected>Bulan ini</option>
              <option value="range">Rentang Tanggal</option>
            </select>
            <small class="text-muted" style="font-size:11px">Pilih preset cepat atau pakai Rentang Tanggal.</small>
          </div>

          <div class="form-group col-md-4">
            <label class="mb-1" for="dt_from">Dari (Tanggal & Jam)</label>
            <input type="datetime-local" id="dt_from" class="form-control form-control-sm">
            <small class="text-muted" style="font-size:11px">Contoh: 2025-10-31 19:00</small>
          </div>

          <div class="form-group col-md-4">
            <label class="mb-1" for="dt_to">Sampai (Tanggal & Jam)</label>
            <input type="datetime-local" id="dt_to" class="form-control form-control-sm">
            <small class="text-muted" style="font-size:11px">Contoh: 2025-11-01 03:00</small>
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

        </div>
      </div>

      <div class="modal-footer d-flex flex-wrap justify-content-between">
        <div class="mb-2">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
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

<!-- ================== SCRIPTS (sesuai yang kamu minta) ================== -->
<script src="https://cdn.jsdelivr.net/npm/@highcharts/grid-pro/grid-pro.js"></script>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<script src="https://code.highcharts.com/dashboards/dashboards.js"></script>
<script src="https://code.highcharts.com/dashboards/modules/layout.js"></script>

<script>
/* ================== UTIL FORMAT TANGGAL ================== */
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
  const m = str.match(/^(\d{4})-(\d{1,2})-(\d{1,2})/);
  if (!m) return str;
  const y  = m[1];
  const mo = ('0' + m[2]).slice(-2);
  const d  = ('0' + m[3]).slice(-2);
  return d + '-' + mo + '-' + y;
}

/**
 * Gabung tanggal + jam → "dd-mm-yyyy HH:MM"
 */
function fmtTanggalWaktuIndo(dateStr, timeStr){
  if (!dateStr) return '-';
  const tgl = fmtTanggalIndo(dateStr);
  if (!timeStr) return tgl;
  const t = String(timeStr).trim();
  const m = t.match(/^(\d{2}):(\d{2})/);
  const jamMenit = m ? (m[1] + ':' + m[2]) : t;
  return tgl + ' ' + jamMenit;
}

(function(){
  /* ================== SETUP GLOBAL HIGHCHARTS (styled mode) ================== */
  Highcharts.setOptions({
    chart: { styledMode: true }
  });

  const pad2 = n => String(n).padStart(2,'0');
  function rupiahNum(n){
    return 'Rp ' + (parseInt(n||0,10)).toLocaleString('id-ID');
  }
  function sumArr(a){
    if (!Array.isArray(a)) return 0;
    return a.reduce((s,v)=>s+(parseFloat(v)||0),0);
  }

  function fmtDTLocal(d){
    return d.getFullYear() + '-' + pad2(d.getMonth()+1) + '-' + pad2(d.getDate())
      + 'T' + pad2(d.getHours()) + ':' + pad2(d.getMinutes());
  }
  function startOfDay(d){ return new Date(d.getFullYear(), d.getMonth(), d.getDate(), 0,0,0); }
  function endOfDay(d){   return new Date(d.getFullYear(), d.getMonth(), d.getDate(), 23,59,0); }
  function addDays(d, n){ const x = new Date(d); x.setDate(x.getDate()+n); return x; }

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

    // 'range'
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

  function setRangeInputsDT(range, enable){
    const $df = document.getElementById('dt_from');
    const $dt = document.getElementById('dt_to');
    $df.value = fmtDTLocal(range.from);
    $dt.value = fmtDTLocal(range.to);
    $df.disabled = !enable;
    $dt.disabled = !enable;
  }

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

  function getParams(){
    const presetSel = document.getElementById('preset').value;
    const r = computeRangeDT(presetSel);

    const pickedFrom = (presetSel==='range') ? new Date(document.getElementById('dt_from').value) : r.from;
    const pickedTo   = (presetSel==='range') ? new Date(document.getElementById('dt_to').value)   : r.to;

    let F = isNaN(pickedFrom) ? r.from : pickedFrom;
    let T = isNaN(pickedTo)   ? r.to   : pickedTo;
    if (T < F) T = new Date(F);

    return {
      preset    : presetSel,
      date_from : F.getFullYear() + '-' + pad2(F.getMonth()+1) + '-' + pad2(F.getDate()),
      date_to   : T.getFullYear() + '-' + pad2(T.getMonth()+1) + '-' + pad2(T.getDate()),
      time_from : pad2(F.getHours()) + ':' + pad2(F.getMinutes()),
      time_to   : pad2(T.getHours()) + ':' + pad2(T.getMinutes()),
      metode    : document.getElementById('metode').value || 'all',
      mode      : document.getElementById('mode').value   || 'all'
    };
  }

  /* ================== DASHBOARD BUILDER ================== */
  let __board = null;

  function buildRekapRows(res){
    const cat  = res.categories || [];
    const cafe = res.cafe || [];
    const bil  = res.billiard || [];
    const kp   = res.kursi_pijat || [];
    const ps   = res.ps || [];
    const peng = res.pengeluaran || [];
    const laba = res.laba || [];

    const rows = [];
    for (let i=0; i<cat.length; i++){
      const c = parseFloat(cafe[i]) || 0;
      const b = parseFloat(bil[i])  || 0;
      const k = parseFloat(kp[i])   || 0;
      const p = parseFloat(ps[i])   || 0;
      const e = parseFloat(peng[i]) || 0;
      const l = parseFloat(laba[i]) || 0;
      rows.push([
        fmtTanggalIndo(cat[i]),
        c, b, k, p, e, l,
        (c+b+k+p)
      ]);
    }
    return rows;
  }

  function computeTotals(res){
    // kalau backend sudah kirim total_rekap, pakai itu biar konsisten
    const t = res.total_rekap || {};
    const out = {
      cafe: parseFloat(t.cafe)||sumArr(res.cafe),
      billiard: parseFloat(t.billiard)||sumArr(res.billiard),
      kursi_pijat: parseFloat(t.kursi_pijat)||sumArr(res.kursi_pijat),
      ps: parseFloat(t.ps)||sumArr(res.ps),
      pengeluaran: parseFloat(t.pengeluaran)||sumArr(res.pengeluaran),
      laba: parseFloat(t.laba)||sumArr(res.laba)
    };
    out.total_pendapatan = out.cafe + out.billiard + out.kursi_pijat + out.ps;
    return out;
  }

  function infoText(res){
    const f = res.filter || {};
    const dfID = fmtTanggalWaktuIndo(f.date_from, f.time_from);
    const dtID = fmtTanggalWaktuIndo(f.date_to,   f.time_to);
    return {
      range: 'Rentang: ' + dfID + ' s/d ' + dtID +
             ' | Mode: ' + (f.mode||'-') +
             ' | Metode: ' + (f.metode||'-') +
             ' | Status: ' + (f.status||'-'),
    };
  }

  async function createDashboard(res){
    const totals = computeTotals(res);

    const categories = (res.categories || []).map(fmtTanggalIndo);
    const cafeData = (res.cafe||[]).map(v=>parseFloat(v)||0);
    const bilData  = (res.billiard||[]).map(v=>parseFloat(v)||0);
    const kpData   = (res.kursi_pijat||[]).map(v=>parseFloat(v)||0);
    const psData   = (res.ps||[]).map(v=>parseFloat(v)||0);
    const pengData = (res.pengeluaran||[]).map(v=>parseFloat(v)||0);
    const labaData = (res.laba||[]).map(v=>parseFloat(v)||0);
    const totalPend = cafeData.map((v,i)=>v+(bilData[i]||0)+(kpData[i]||0)+(psData[i]||0));

    const it = infoText(res);

    __board = await Dashboards.board('dash_container', {
      dataPool: {
        connectors: [{
          id: 'rekap',
          type: 'JSON',
          firstRowAsNames: false,
          columnIds: ['Tanggal','Cafe','Billiard','KursiPijat','PS','Pengeluaran','Laba','TotalPendapatan'],
          data: buildRekapRows(res)
        }]
      },
      gui: {
        layouts: [{
          rows: [{
            id: 'row-1',
            cells: [
              { id: 'dashboard-row-1-cell-1' },
              { id: 'dashboard-row-1-cell-2' },
              { id: 'dashboard-row-1-cell-3' }
            ]
          }, {
            cells: [{ id: 'dashboard-row-2-cell-1' }]
          }, {
            cells: [
              { id: 'dashboard-row-3-cell-1' },
              { id: 'dashboard-row-3-cell-2' },
              { id: 'dashboard-row-3-cell-3' }
            ]
          }]
        }]
      },
      components: [
        /* KPI #1: Total Cafe */
        {
          id: 'kpi_cafe',
          type: 'KPI',
          renderTo: 'dashboard-row-1-cell-1',
          title: 'Total Cafe',
          value: totals.cafe,
          subtitle: 'POS Cafe',
          linkedValueTo: { enabled:false },
          valueFormatter: function(){ return rupiahNum(this.options.value); },
          chartOptions: {
            chart: { styledMode:true },
            series: [{
              type:'spline',
              enableMouseTracking:false,
              dataLabels:{ enabled:false },
              marker:{ enabled:false },
              data: cafeData
            }]
          }
        },

        /* KPI #2: Total Billiard */
        {
          id: 'kpi_billiard',
          type: 'KPI',
          renderTo: 'dashboard-row-1-cell-2',
          title: 'Total Billiard',
          value: totals.billiard,
          subtitle: 'Billiard',
          linkedValueTo: { enabled:false },
          valueFormatter: function(){ return rupiahNum(this.options.value); },
          chartOptions: {
            chart: { styledMode:true },
            series: [{
              type:'spline',
              enableMouseTracking:false,
              dataLabels:{ enabled:false },
              marker:{ enabled:false },
              data: bilData
            }]
          }
        },

        /* HTML #3: Filter & tombol */
        {
          id: 'html_filter',
          type: 'HTML',
          renderTo: 'dashboard-row-1-cell-3',
          elements: [{
            tagName: 'div',
            children: [
              { tagName:'div', textContent:'Filter Aktif', attributes:{ class:'dh-title' } },
              { tagName:'div', textContent: it.range, attributes:{ class:'dh-mini', id:'dh_infoRange' } },
              { tagName:'div', textContent:
                ('Total Cafe: '+rupiahNum(totals.cafe)+
                 ' · Billiard: '+rupiahNum(totals.billiard)+
                 ' · KP: '+rupiahNum(totals.kursi_pijat)+
                 ' · PS: '+rupiahNum(totals.ps)+
                 ' · Pengeluaran: '+rupiahNum(totals.pengeluaran)+
                 ' · Laba: '+rupiahNum(totals.laba)),
                attributes:{ class:'dh-mini', id:'dh_infoTotal' }
              },
              { tagName:'div', attributes:{ style:'padding-left:10px;margin-top:10px;' }, children:[
                { tagName:'button', textContent:'Setel Waktu', attributes:{ id:'btn-open-filter' } }
              ]}
            ]
          }]
        },

        /* Chart besar: Pendapatan Harian */
        {
          id: 'chart_pendapatan',
          type: 'Highcharts',
          renderTo: 'dashboard-row-2-cell-1',
          title: 'Pendapatan Harian',
          chartOptions: {
            chart: { styledMode:true, type:'spline', marginTop: 55 },
            credits:{ enabled:false },
            title:{ text:'' },
            xAxis:{ categories, crosshair:true },
            yAxis:{ min:0, title:{ text:'Rupiah (Rp)' } },
            tooltip:{ shared:true, valuePrefix:'Rp ' },
            plotOptions:{ series:{ marker:{ enabled:false } } },
            series: [
              { name:'Cafe / POS', type:'spline', data: cafeData },
              { name:'Billiard', type:'spline', data: bilData },
              { name:'Kursi Pijat', type:'spline', data: kpData },
              { name:'PlayStation (PS)', type:'spline', data: psData },
              { name:'Total Pendapatan', type:'spline', data: totalPend }
            ]
          }
        },

        /* KPI: Pengeluaran (spark column) */
        {
          id: 'kpi_pengeluaran',
          type: 'KPI',
          renderTo: 'dashboard-row-3-cell-1',
          title: 'Pengeluaran',
          value: totals.pengeluaran,
          subtitle: 'Total biaya keluar',
          linkedValueTo: { enabled:false },
          valueFormatter: function(){ return rupiahNum(this.options.value); },
          chartOptions: {
            chart: { styledMode:true },
            series: [{
              type:'column',
              enableMouseTracking:false,
              dataLabels:{ enabled:false },
              data: pengData
            }]
          }
        },

        /* KPI: Laba (spark area) */
        {
          id: 'kpi_laba',
          type: 'KPI',
          renderTo: 'dashboard-row-3-cell-2',
          title: 'Laba',
          value: totals.laba,
          subtitle: '(Cafe+Billiard+KP+PS-Pengeluaran)',
          linkedValueTo: { enabled:false },
          valueFormatter: function(){ return rupiahNum(this.options.value); },
          chartOptions: {
            chart: { styledMode:true },
            defs: {
              gradient0: {
                tagName: 'linearGradient',
                id: 'gradient-0',
                x1: 0, y1: 0, x2: 0, y2: 1,
                children: [
                  { tagName:'stop', offset: 0 },
                  { tagName:'stop', offset: 1 }
                ]
              }
            },
            plotOptions: {
              series: { marker:{ enabled:false } },
              areaspline: { fillColor: 'url(#gradient-0)' }
            },
            series: [{
              type:'areaspline',
              enableMouseTracking:false,
              dataLabels:{ enabled:false },
              data: labaData
            }]
          }
        },

        /* Grid: Rekap Harian */
        {
          id: 'grid_rekap',
          renderTo: 'dashboard-row-3-cell-3',
          connector: { id: 'rekap' },
          title: 'Rekap Harian',
          type: 'Grid',
          gridOptions: {
            credits: { enabled:false },
            rendering: { theme: 'hcg-custom-theme' }
          }
        }
      ]
    }, true);

    // simpan global
    window.__AUSI_DASH_BOARD__ = __board;
  }

  function updateDashboard(res){
    if (!__board) return;

    const totals = computeTotals(res);
    const it = infoText(res);

    // update HTML info
    const elR = document.getElementById('dh_infoRange');
    const elT = document.getElementById('dh_infoTotal');
    if (elR) elR.textContent = it.range;
    if (elT) elT.textContent =
      ('Total Cafe: '+rupiahNum(totals.cafe)+
       ' · Billiard: '+rupiahNum(totals.billiard)+
       ' · KP: '+rupiahNum(totals.kursi_pijat)+
       ' · PS: '+rupiahNum(totals.ps)+
       ' · Pengeluaran: '+rupiahNum(totals.pengeluaran)+
       ' · Laba: '+rupiahNum(totals.laba));

    const categories = (res.categories || []).map(fmtTanggalIndo);
    const cafeData = (res.cafe||[]).map(v=>parseFloat(v)||0);
    const bilData  = (res.billiard||[]).map(v=>parseFloat(v)||0);
    const kpData   = (res.kursi_pijat||[]).map(v=>parseFloat(v)||0);
    const psData   = (res.ps||[]).map(v=>parseFloat(v)||0);
    const pengData = (res.pengeluaran||[]).map(v=>parseFloat(v)||0);
    const labaData = (res.laba||[]).map(v=>parseFloat(v)||0);
    const totalPend = cafeData.map((v,i)=>v+(bilData[i]||0)+(kpData[i]||0)+(psData[i]||0));

    // KPI update (value + sparkline)
    const kpiCafe = __board.getComponentById('kpi_cafe');
    if (kpiCafe){
      kpiCafe.update({ value: totals.cafe }, true);
      if (kpiCafe.chart && kpiCafe.chart.series[0]){
        kpiCafe.chart.series[0].setData(cafeData, true, false, false);
      }
    }

    const kpiBil = __board.getComponentById('kpi_billiard');
    if (kpiBil){
      kpiBil.update({ value: totals.billiard }, true);
      if (kpiBil.chart && kpiBil.chart.series[0]){
        kpiBil.chart.series[0].setData(bilData, true, false, false);
      }
    }

    const kpiPeng = __board.getComponentById('kpi_pengeluaran');
    if (kpiPeng){
      kpiPeng.update({ value: totals.pengeluaran }, true);
      if (kpiPeng.chart && kpiPeng.chart.series[0]){
        kpiPeng.chart.series[0].setData(pengData, true, false, false);
      }
    }

    const kpiLaba = __board.getComponentById('kpi_laba');
    if (kpiLaba){
      kpiLaba.update({ value: totals.laba }, true);
      if (kpiLaba.chart && kpiLaba.chart.series[0]){
        kpiLaba.chart.series[0].setData(labaData, true, false, false);
      }
    }

    // Chart pendapatan update (xAxis + 5 series)
    const chPend = __board.getComponentById('chart_pendapatan');
    if (chPend && chPend.chart){
      const c = chPend.chart;
      c.xAxis[0].setCategories(categories, false);

      // urutan series harus sama seperti createDashboard()
      if (c.series[0]) c.series[0].setData(cafeData, false, false, false);
      if (c.series[1]) c.series[1].setData(bilData,  false, false, false);
      if (c.series[2]) c.series[2].setData(kpData,   false, false, false);
      if (c.series[3]) c.series[3].setData(psData,   false, false, false);
      if (c.series[4]) c.series[4].setData(totalPend,false, false, false);

      c.redraw();
    }

    // Grid rekap update via connector.load() (lebih aman daripada reload component)
    __board.dataPool.getConnector('rekap').then(function(connector){
      connector.options.data = buildRekapRows(res);
      return connector.load();
    }).catch(function(e){
      console.error('Gagal update grid connector', e);
    });
  }

  function loadChartsAndDashboard(cbAfter){
    const params = getParams();

    fetch("<?= site_url('admin_laporan/chart_data'); ?>", {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
      body:(new URLSearchParams(params)).toString(),
      credentials:'same-origin'
    })
    .then(r=>r.json())
    .then(async res=>{
      if (!res || !res.success){
        console.error('chart_data invalid',res);
        return;
      }
      if (!__board){
        await createDashboard(res);
      } else {
        updateDashboard(res);
      }
      if (typeof cbAfter === 'function') cbAfter();
    })
    .catch(err=>console.error('Gagal load chart_data()', err));
  }

  /* ================== EVENT MODAL ================== */
  // tombol "Setel Waktu" ada di HTML component dashboard => pakai delegasi
  document.addEventListener('click', function(e){
    const btn = e.target.closest('#btn-open-filter');
    if (!btn) return;
    $('#filterModal').modal('show');
  });

  document.getElementById('preset').addEventListener('change', function(){
    const isRange = (this.value === 'range');
    const range = computeRangeDT(this.value);
    setRangeInputsDT(range, isRange);
    normalizeDTInputs();
  });

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

  document.getElementById('btn-reset').addEventListener('click', function(){
    document.getElementById('preset').value = 'this_month';
    document.getElementById('metode').value = 'all';
    document.getElementById('mode').value   = 'all';

    const r = computeRangeDT('this_month');
    setRangeInputsDT(r, false);
    normalizeDTInputs();
  });

  document.getElementById('btn-apply').addEventListener('click', function(){
    normalizeDTInputs();
    loadChartsAndDashboard(function(){
      $('#filterModal').modal('hide');
      const target = document.getElementById('dash_container');
      if (target){
        target.scrollIntoView({ behavior:'smooth', block:'start' });
      }
    });
  });

  /* ================== INIT ================== */
  document.addEventListener('DOMContentLoaded', function(){
    const presetSel = document.getElementById('preset');
    presetSel.value = 'this_month';

    const initRange = computeRangeDT('this_month');
    setRangeInputsDT(initRange, false);
    normalizeDTInputs();

    loadChartsAndDashboard();
  });

})();
</script>
