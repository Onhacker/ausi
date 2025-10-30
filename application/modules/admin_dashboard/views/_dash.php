<script>
(function(){
  var isEmbed = /[?&]embed=1/.test(location.search) || (window.top !== window.self);
  if (isEmbed){
    var s = document.createElement('style');
    s.textContent = `
      .page-title-box{display:none!important}
      .container-fluid{padding-top:8px;}
    `;
    document.head.appendChild(s);
  }
})();
</script>

<!-- Controls -->
<style>
  .dash-card{border:1px solid #eef0f3;border-radius:14px}
  .dash-head{border-bottom:1px dashed #e5e7eb}
  .kpi{display:flex;align-items:center;gap:.8rem}
  .kpi .num{font-size:1.6rem;font-weight:800;line-height:1}
  .kpi .lbl{color:#64748b}
  .btn-period .btn{min-width:88px}
  .chart-wrap{min-height:300px}
</style>
  <div class="row">
    <div class="col-12">
      <div class="card dash-card shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center dash-head pb-3">
          <div class="btn-group btn-period" role="group" aria-label="Period">
            <button class="btn btn-outline-primary" data-period="day">Harian</button>
            <button class="btn btn-outline-primary" data-period="week">Mingguan</button>
            <button class="btn btn-outline-primary" data-period="month">Bulanan</button>
          </div>

          <div class="form-inline">
            <label class="mr-2 text-muted small">Tanggal Acuan</label>
            <input type="date" id="datePick" class="form-control" value="<?= date('Y-m-d') ?>">
            <button id="btnReload" class="btn btn-outline-primary ml-2">
              <i class="mdi mdi-refresh"></i> Muat
            </button>
          </div>
        </div>

        <!-- KPIs -->
        <div class="card-body">
          <div class="row text-center">
            <div class="col-sm-4">
              <div class="kpi justify-content-center">
                <div class="num" id="kpiVisitors">0</div>
                <div class="lbl">Tamu (check-in)</div>
              </div>
              <div class="small text-muted" id="rangeText">-</div>
            </div>
            <div class="col-sm-4">
              <div class="kpi justify-content-center">
                <div class="num" id="kpiInstansi">0</div>
                <div class="lbl">Instansi unik</div>
              </div>
              <div class="small text-muted">Sumber kedatangan</div>
            </div>
            <div class="col-sm-4">
              <div class="kpi justify-content-center">
                <div class="num" id="kpiTopUnit">-</div>
                <div class="lbl">Unit teramai</div>
              </div>
              <div class="small text-muted" id="kpiTopUnitCount">-</div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Charts -->
  <div class="row">
    <div class="col-xl-4">
      <div class="card dash-card shadow-sm">
        <div class="card-body">
          <h5 class="mb-2">Tren Kunjungan</h5>
          <div class="chart-wrap" id="hcTrend"></div>
        </div>
      </div>
    </div>

    <div class="col-xl-4">
      <div class="card dash-card shadow-sm">
        <div class="card-body">
          <h5 class="mb-2">Asal Instansi (Top 5)</h5>
          <div class="chart-wrap" id="hcInstansi"></div>
        </div>
      </div>
    </div>

    <div class="col-xl-4 ">
      <div class="card dash-card shadow-sm">
        <div class="card-body">
          <h5 class="mb-2">Unit Teramai (Top 5)</h5>
          <div class="chart-wrap" id="hcUnit"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Highcharts -->
<script src="<?php echo base_url('assets/admin') ?>/chart/highcharts.js"></script>
<script src="<?php echo base_url('assets/admin') ?>/chart/exporting.js"></script>
<script src="<?php echo base_url('assets/admin') ?>/chart/export-data.js"></script>
<script src="<?php echo base_url('assets/admin') ?>/chart/accessibility.js"></script>

<script>
(function(){
  // ------- UI el
  const periodBtns = document.querySelectorAll('.btn-period .btn');
  const datePick   = document.getElementById('datePick');
  const btnReload  = document.getElementById('btnReload');

  const kpiVisitors= document.getElementById('kpiVisitors');
  const kpiInstansi= document.getElementById('kpiInstansi');
  const kpiTopUnit = document.getElementById('kpiTopUnit');
  const kpiTopUnitC= document.getElementById('kpiTopUnitCount');
  const rangeText  = document.getElementById('rangeText');

  // Fullscreen
  const btnFS  = document.getElementById('btnFullscreen');
  const fsEl   = document.getElementById('dashFS');
  btnFS.addEventListener('click', async ()=>{
    try{
      if (!document.fullscreenElement){
        await (fsEl.requestFullscreen ? fsEl.requestFullscreen({navigationUI:'hide'}) : document.documentElement.requestFullscreen());
        btnFS.innerHTML = '<i class="mdi mdi-fullscreen-exit"></i> Exit Fullscreen';
      } else {
        await document.exitFullscreen();
        btnFS.innerHTML = '<i class="mdi mdi-fullscreen"></i> Fullscreen';
      }
    }catch(e){}
  });
  document.addEventListener('fullscreenchange', ()=>{
    if (!document.fullscreenElement){
      btnFS.innerHTML = '<i class="mdi mdi-fullscreen"></i> Fullscreen';
    }
  });

  // ------- State
  const urlP = (new URLSearchParams(location.search)).get('period');
  const initialPeriod = (['day','week','month'].includes((urlP||'').toLowerCase())) ? urlP.toLowerCase() : 'month';
  let state = { period: initialPeriod, date: datePick.value };

  function markActive(p){
    periodBtns.forEach(x=> x.classList.toggle('active', x.getAttribute('data-period') === p));
  }
  periodBtns.forEach(b=>{
    b.addEventListener('click', ()=>{
      state.period = b.getAttribute('data-period');
      markActive(state.period);
      load();
    });
  });
  btnReload.addEventListener('click', ()=>{
    state.date = datePick.value || '<?= date('Y-m-d') ?>';
    load();
  });

  // ------- Highcharts theme (ringan)
  Highcharts.setOptions({
    chart: { style:{ fontFamily: 'system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial' } },
    colors: ['#2563eb','#22c55e','#eab308','#ef4444','#8b5cf6','#06b6d4','#f97316'],
    lang: { thousandsSep: '.', decimalPoint: ',' },
    credits: { enabled:false }
  });

  // ------- Init charts (kosong dulu agar tidak error)
  const trendChart = Highcharts.chart('hcTrend', {
    chart: { type:'line' },
    title: { text: null },
    xAxis: { categories: ['—'] },
    yAxis: { title:{ text:null }, allowDecimals:false },
    tooltip:{ shared:true },
    series: [{ name:'Kunjungan', data:[0] }],
    exporting: { enabled: true }
  });

  const instansiChart = Highcharts.chart('hcInstansi', {
    chart: { type:'pie' },
    title: { text: null },
    plotOptions: {
      pie: {
        innerSize: '60%',
        dataLabels: { enabled:true, format:'{point.name}: <b>{point.y}</b>' }
      }
    },
    tooltip:{ pointFormat:'<b>{point.y} kunjungan</b>' },
    series: [{ name:'Instansi', data:[{ name:'—', y:0 }] }]
  });

  const unitChart = Highcharts.chart('hcUnit', {
    chart: { type:'column' },
    title: { text: null },
    xAxis: { categories: ['—'], crosshair:true },
    yAxis: { title:{ text:null }, allowDecimals:false, min:0 },
    tooltip:{ shared:true },
    plotOptions:{ column:{ borderRadius:3, pointPadding:0.1, groupPadding:0.15 } },
    series: [{ name:'Kunjungan', data:[0] }]
  });

  // ------- Helpers
  function setRangeText(start,end){
    const s = new Date(start.replace(' ','T'));
    const e = new Date(end.replace(' ','T'));
    const pad=n=> (n<10?'0':'')+n;
    const fmt=(d)=> `${pad(d.getDate())}-${pad(d.getMonth()+1)}-${d.getFullYear()}`;
    rangeText.textContent = `${fmt(s)} s/d ${fmt(e)}`;
  }

  function safeCats(arr){
    return (Array.isArray(arr) && arr.length) ? arr : ['—'];
  }
  function safeNums(arr){
    if (!Array.isArray(arr) || !arr.length) return [0];
    return arr.map(x=> {
      const n = Number(x);
      return Number.isFinite(n) ? n : 0;
    });
  }

  // ------- Load + render
  async function load(){
    const params = new URLSearchParams({ period: state.period, date: state.date });
    const url = '<?= site_url('admin_dashboard/dashboard_data') ?>?'+params.toString();
    const r = await fetch(url, { credentials:'same-origin' });
    const j = await r.json();
    if (!j || !j.ok) return;

    // KPIs
    kpiVisitors.textContent = j.totals?.visitors ?? 0;
    kpiInstansi.textContent = j.totals?.unique_instansi ?? 0;

    const topU = (j.top_units||[])[0];
    kpiTopUnit.textContent  = topU ? (topU.label || '-') : '-';
    kpiTopUnitC.textContent = topU ? (`${topU.qty} kunjungan`) : '—';

    setRangeText(j.start, j.end);

    // Trend (line)
    const cats1 = safeCats(j.trend?.labels || []);
    const data1 = safeNums(j.trend?.data   || []);
    trendChart.xAxis[0].setCategories(cats1, false);
    trendChart.series[0].setData(data1, true);

    // Instansi (donut)
    const pieData = (j.top_instansi||[]).map(it=>({
      name: (it.label || '—'),
      y: Number(it.qty) || 0
    }));
    instansiChart.series[0].setData(pieData.length ? pieData : [{name:'—', y:0}], true);

    // Unit (column)
    const cats3 = safeCats((j.top_units||[]).map(x=> x.label || '—'));
    const data3 = safeNums((j.top_units||[]).map(x=> x.qty));
    unitChart.xAxis[0].setCategories(cats3, false);
    unitChart.series[0].setData(data3, true);
  }

  // init → paksa period BULANAN saat pertama kali
  markActive(state.period);
  load();
})();
</script>
