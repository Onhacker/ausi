<?php
// application/views/live_wallboard_combined.php
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Live Wallboard (Dashboard + Monitor)</title>

<style>
/* ===== Sembunyikan layout di fullscreen (jaga-jaga untuk template admin) ===== */
:root:fullscreen .navbar,
:root:fullscreen .navbar-custom,
:root:fullscreen .topnav,
:root:fullscreen #topnav,
:root:fullscreen .left-side-menu,
:root:fullscreen .logo-box,
:root:fullscreen header,
body.fs-active .navbar,
body.fs-active .navbar-custom,
body.fs-active .topnav,
body.fs-active #topnav,
body.fs-active .left-side-menu,
body.fs-active .logo-box,
body.fs-active header { display:none !important; }

:root:fullscreen .content-page,
body.fs-active .content-page { margin-top:0 !important; }

:root{--bg:#0b1220;--text:#e5e7eb;--muted:#94a3b8;--accent:#22c55e}

/* Wrapper utama yang akan di-fullscreen */
#wallWrap{display:flex;flex-direction:column;min-height:100vh;background:var(--bg);color:var(--text)}
#wallWrap:fullscreen{background:var(--bg) !important;color:var(--text)} /* tetap gelap saat fullscreen */

.topbar{display:flex;align-items:center;justify-content:space-between;padding:.6rem 1rem;border-bottom:1px solid rgba(255,255,255,.08);background:rgba(15,23,42,.95);color:var(--text)}
.brand{display:flex;align-items:center;gap:.6rem}
.brand .dot{width:10px;height:10px;border-radius:50%;background:var(--accent);box-shadow:0 0 0 0 rgba(34,197,94,.6);animation:ping 1.6s infinite}
@keyframes ping{0%{box-shadow:0 0 0 0 rgba(34,197,94,.6)}80%{box-shadow:0 0 0 12px rgba(34,197,94,0)}100%{box-shadow:0 0 0 0 rgba(34,197,94,0)}}
.brand h1{font-size:1rem;margin:0}
.meta{display:flex;align-items:center;gap:1rem;color:var(--muted);font-size:.9rem}
.meta b{color:var(--text)}
.controls button{background:transparent;border:1px solid rgba(255,255,255,.2);color:var(--text);padding:.35rem .6rem;border-radius:.5rem;cursor:pointer}
.controls button:hover{border-color:#fff}

/* panggung untuk panel */
.stage{position:relative;flex:1;overflow:hidden;padding:10px}
.tab-panel{position:absolute;inset:10px;overflow:auto;background:#ffffff;color:#0f172a;border-radius:12px;opacity:0;pointer-events:none;transition:opacity .35s ease}
.tab-panel.show{opacity:1;pointer-events:auto}

/* tabs switcher kecil di bawah */
.tabs{position:absolute;left:50%;bottom:22px;transform:translateX(-50%);display:flex;gap:.35rem;background:rgba(15,23,42,.6);backdrop-filter:blur(6px);border:1px solid rgba(255,255,255,.1);padding:.25rem .35rem;border-radius:999px;z-index:10}
.tag{color:var(--muted);font-weight:600;font-size:.8rem;padding:.15rem .6rem;border-radius:999px;cursor:pointer;user-select:none}
.tag.active{background:#1f2937;color:#fff}

/* === gaya dashboard & monitor (ringkas) === */
.dash-card,.board-card{border:1px solid #eef0f3;border-radius:14px}
.dash-head,.board-head{border-bottom:1px dashed #e5e7eb}
.kpi{display:flex;align-items:center;gap:.8rem}
.kpi .num{font-size:1.6rem;font-weight:800;line-height:1}
.kpi .lbl{color:#64748b}
.btn-period .btn{min-width:88px}

.hc-wrap{min-height:300px}        /* kontainer grafik highcharts */
.hc{width:100%;height:320px}      /* tinggi default grafik */

.list-table{width:100%;border-collapse:separate;border-spacing:0 6px}
.list-table th{font-size:.9rem;color:#6b7280;font-weight:600}
.list-table td{background:#fff;box-shadow:0 1px 0 rgba(0,0,0,.03);padding:10px 12px;border-top:1px solid #eef0f3;border-bottom:1px solid #eef0f3}
.row-pill{border-radius:10px;transition:background .15s}
.row-link{cursor:pointer}
.row-link:hover{background:#f8fafc}
.empty{color:#94a3b8}
.count{font-size:1.6rem;font-weight:800}
.server-time{font-size:1.8rem;font-weight:800;color:#0f172a;letter-spacing:.5px;font-variant-numeric:tabular-nums}
.scope-text{font-size:1.6rem;font-weight:800;color:#0f172a}
</style>
</head>
<body>

<div id="wallWrap">
  <!-- Top Bar -->
  <div class="topbar">
    <div class="brand"><span class="dot"></span><h1>Live Wallboard</h1></div>
    <div class="meta">
      <span id="tabTitle">—</span><span>•</span><span id="clock">--:--:--</span><span>•</span>
      <span>Rotasi <b id="durText">45</b> dtk</span>
    </div>
    <div class="controls">
      <button id="btnPrev" title="Sebelumnya">« Prev</button>
      <button id="btnNext" title="Berikutnya">Next »</button>
      <button id="btnPause" title="Jeda / Lanjut">⏸︎</button>
      <button id="btnFS" title="Fullscreen">⛶</button>
    </div>
  </div>

  <div class="stage">
    <div class="tabs" id="tabsWrap"></div>

    <!-- ================== DASHBOARD ================== -->
    <section id="tab-dashboard" class="tab-panel show" data-title="Dashboard">
      <div class="container-fluid" id="dashFS">
        <div class="row">
          <div class="col-12">
            <div class="page-title-box">
              <div class="page-title-right">
                <ol class="breadcrumb m-0">
                  <li class="breadcrumb-item">Dashboard Kunjungan</li>
                  <li class="breadcrumb-item active">Harian • Mingguan • Bulanan</li>
                </ol>
              </div>
              <h4 class="page-title d-flex align-items-center" style="gap:.5rem;">Dashboard Kunjungan</h4>
            </div>
          </div>
        </div>

        <!-- Controls -->
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
                  <input type="date" class="form-control dash-date" value="<?= date('Y-m-d') ?>">
                  <button class="btn btn-outline-primary ml-2 dash-reload">
                    <i class="mdi mdi-refresh"></i> Muat
                  </button>
                </div>
              </div>

              <!-- KPI -->
              <div class="card-body">
                <div class="row text-center">
                  <div class="col-sm-4">
                    <div class="kpi justify-content-center">
                      <div class="num dash-kpi-visitors">0</div>
                      <div class="lbl">Tamu (check-in)</div>
                    </div>
                    <div class="small text-muted dash-range">-</div>
                  </div>
                  <div class="col-sm-4">
                    <div class="kpi justify-content-center">
                      <div class="num dash-kpi-instansi">0</div>
                      <div class="lbl">Instansi unik</div>
                    </div>
                    <div class="small text-muted">Sumber kedatangan</div>
                  </div>
                  <div class="col-sm-4">
                    <div class="kpi justify-content-center">
                      <div class="num dash-kpi-topunit">-</div>
                      <div class="lbl">Unit teramai</div>
                    </div>
                    <div class="small text-muted dash-kpi-topunit-cnt">-</div>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>

        <!-- Charts (Highcharts) -->
        <div class="row">
          <div class="col-xl-4">
            <div class="card dash-card shadow-sm">
              <div class="card-body">
                <h5 class="mb-2">Tren Kunjungan</h5>
                <div class="hc-wrap"><div id="hcTrend" class="hc"></div></div>
              </div>
            </div>
          </div>

          <div class="col-xl-4">
            <div class="card dash-card shadow-sm">
              <div class="card-body">
                <h5 class="mb-2">Asal Instansi (Top 5)</h5>
                <div class="hc-wrap"><div id="hcInstansi" class="hc"></div></div>
              </div>
            </div>
          </div>

          <div class="col-xl-4">
            <div class="card dash-card shadow-sm">
              <div class="card-body">
                <h5 class="mb-2">Unit Teramai (Top 5)</h5>
                <div class="hc-wrap"><div id="hcUnit" class="hc"></div></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ================== MONITOR ================== -->
    <section id="tab-monitor" class="tab-panel" data-title="Monitor">
      <div class="container-fluid" id="fsContainer">
        <div class="row">
          <div class="col-12">
            <div class="page-title-box">
              <div class="page-title-right">
                <ol class="breadcrumb m-0">
                  <li class="breadcrumb-item">Layar Utama</li>
                  <li class="breadcrumb-item active">Daftar Booking & Sedang Berkunjung</li>
                </ol>
              </div>
              <h4 class="page-title d-flex align-items-center" style="gap:.5rem;">Daftar Booking & Sedang Berkunjung</h4>
            </div>
          </div>
        </div>

        <!-- Info bar -->
        <div class="row">
          <div class="col-12">
            <div class="card board-card shadow-sm mb-3">
              <div class="card-body d-flex flex-wrap justify-content-between align-items-center board-head pb-2">
                <div class="d-flex align-items-center" style="gap:1rem;">
                  <div>
                    <div class="text-muted small mb-1">Ruang Lingkup</div>
                    <div><span class="scope-text">Lapas Kelas I Makassar</span></div>
                  </div>
                  <div>
                    <div class="text-muted small mb-1">Server Time</div>
                    <div><span class="mon-time server-time">--:--:--</span></div>
                  </div>
                  <div class="card-body pt-3">
                    <span class="badge badge-danger px-3 py-2 badge-live mon-badge"><strong>Live</strong></span>
                  </div>
                </div>

                <div class="d-flex align-items-center" style="gap:1rem;">
                  <div class="text-center">
                    <div class="text-muted small">Jumlah Sudah Booking</div>
                    <div class="count mon-count-booked">0</div>
                  </div>
                  <div class="text-center">
                    <div class="text-muted small">Sedang Berkunjung</div>
                    <div class="count mon-count-visit">0</div>
                  </div>
                  <button class="btn btn-outline-primary ml-2 mon-reload">
                    <i class="mdi mdi-refresh"></i> Muat Ulang
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Dua kolom -->
        <div class="row">
          <!-- Kiri -->
          <div class="col-xl-6">
            <div class="card board-card shadow-sm">
              <div class="card-body">
                <h5 class="header-title">Akan Datang (Belum Check-in)</h5>

                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap" style="gap:.75rem;">
                  <div class="input-group" style="max-width:460px;">
                    <input type="text" class="form-control mon-q" placeholder="Cari nama / kode / unit / instansi / keperluan...">
                    <div class="input-group-append">
                      <button class="btn btn-outline-primary mon-search" title="Cari"><i class="mdi mdi-magnify"></i></button>
                      <button class="btn btn-outline-secondary mon-clear" title="Bersihkan"><i class="mdi mdi-close"></i></button>
                    </div>
                  </div>
                  <div class="form-inline">
                    <label class="mr-2 small text-muted">Baris</label>
                    <select class="form-control form-control-sm mon-perpage">
                      <option value="10">10</option>
                      <option value="15" selected>15</option>
                      <option value="25">25</option>
                      <option value="50">50</option>
                    </select>
                  </div>
                </div>

                <div class="table-responsive">
                  <table class="list-table">
                    <thead>
                      <tr>
                        <th style="width:26%">Tanggal / Jam</th>
                        <th>Nama</th>
                        <th style="width:24%">Asal</th>
                        <th style="width:26%">Unit & Petugas</th>
                        <th style="width:9%;text-align:center">Pendamping</th>
                      </tr>
                    </thead>
                    <tbody class="mon-booked">
                      <tr><td colspan="5" class="empty text-center py-3">Memuat...</td></tr>
                    </tbody>
                  </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div class="small text-muted mon-pageinfo">Hal. 1/1</div>
                  <div>
                    <button class="btn btn-sm btn-outline-secondary mon-prev" disabled>« Sebelumnya</button>
                    <button class="btn btn-sm btn-outline-secondary mon-next" disabled>Berikutnya »</button>
                  </div>
                </div>

              </div>
            </div>
          </div>

          <!-- Kanan -->
          <div class="col-xl-6">
            <div class="card board-card shadow-sm">
              <div class="card-body">
                <h5 class="header-title">Sedang Berkunjung (Check-in)</h5>
                <div class="table-responsive">
                  <table class="list-table">
                    <thead>
                      <tr>
                        <th style="width:18%">Check-in</th>
                        <th>Nama / Instansi</th>
                        <th style="width:28%">Unit & Petugas</th>
                        <th style="width:14%">Durasi</th>
                        <th style="width:12%;text-align:center">Pendamping</th>
                      </tr>
                    </thead>
                    <tbody class="mon-visit">
                      <tr><td colspan="5" class="empty text-center py-3">Belum ada pengunjung aktif.</td></tr>
                    </tbody>
                  </table>
                </div>
                <div class="small text-muted mt-2">Durasi diperbarui otomatis.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

  </div>
</div>

<!-- Highcharts -->
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>

<script>
(function(){
  const qs  = new URLSearchParams(location.search);
  const SEC = Math.max(5, parseInt(qs.get('sec')||'45',10));
  const AUTOFS = (qs.get('auto') === '1');

  document.getElementById('durText').textContent = SEC;

  // Jam bar atas
  setInterval(()=>{
    const d=new Date(); const p=n=>(n<10?'0':'')+n;
    document.getElementById('clock').textContent = p(d.getHours())+':'+p(d.getMinutes())+':'+p(d.getSeconds());
  },1000);

  /* ==================== DASHBOARD (Highcharts) ==================== */
  function createDashboardModule(root){
    const periodBtns = root.querySelectorAll('.btn-period .btn');
    const datePick   = root.querySelector('.dash-date');
    const btnReload  = root.querySelector('.dash-reload');

    const kpiVisitors= root.querySelector('.dash-kpi-visitors');
    const kpiInstansi= root.querySelector('.dash-kpi-instansi');
    const kpiTopUnit = root.querySelector('.dash-kpi-topunit');
    const kpiTopUnitC= root.querySelector('.dash-kpi-topunit-cnt');
    const rangeText  = root.querySelector('.dash-range');

    // Highcharts instances
    let hcTrend=null, hcInstansi=null, hcUnit=null, refreshTimer=null;

    function ensureTrend(){
      if (hcTrend) return hcTrend;
      hcTrend = Highcharts.chart('hcTrend', {
        chart: { type:'line' },
        title: { text: null },
        xAxis: { categories: [] },
        yAxis: { title: { text: null }, min: 0, allowDecimals:false },
        legend: { enabled: false },
        series: [{ name: 'Kunjungan', data: [] }],
        credits: { enabled:false },
        exporting: { enabled:false }
      });
      return hcTrend;
    }
    function ensureInstansi(){
      if (hcInstansi) return hcInstansi;
      hcInstansi = Highcharts.chart('hcInstansi', {
        chart: { type:'pie' },
        title: { text: null },
        plotOptions: {
          pie: { innerSize: '55%', dataLabels: { enabled: true, format: '{point.name}<br><b>{point.y}</b>', distance: 10 } }
        },
        series: [{ name: 'Jumlah', data: [] }],
        credits: { enabled:false },
        exporting: { enabled:false },
        legend: { enabled: false }
      });
      return hcInstansi;
    }
    function ensureUnit(){
      if (hcUnit) return hcUnit;
      hcUnit = Highcharts.chart('hcUnit', {
        chart: { type:'column' },
        title: { text: null },
        xAxis: { categories: [] },
        yAxis: { title: { text: null }, min: 0, allowDecimals:false },
        legend: { enabled: false },
        series: [{ name: 'Kunjungan', data: [] }],
        credits: { enabled:false },
        exporting: { enabled:false }
      });
      return hcUnit;
    }

    function setRangeText(start,end){
      const s = new Date(String(start).replace(' ','T'));
      const e = new Date(String(end).replace(' ','T'));
      const pad=n=> (n<10?'0':'')+n;
      const fmt=(d)=> `${pad(d.getDate())}-${pad(d.getMonth()+1)}-${d.getFullYear()}`;
      rangeText.textContent = `${fmt(s)} s/d ${fmt(e)}`;
    }

    let state = {
      period: (['day','week','month'].includes((new URLSearchParams(location.search)).get('period')||'')) ? (new URLSearchParams(location.search)).get('period') : 'month',
      date: datePick.value
    };
    function markActive(p){ periodBtns.forEach(x=> x.classList.toggle('active', x.getAttribute('data-period') === p)); }
    markActive(state.period);

    periodBtns.forEach(b=>{
      b.addEventListener('click', ()=>{ state.period=b.getAttribute('data-period'); markActive(state.period); load(); });
    });
    btnReload.addEventListener('click', ()=>{ state.date = datePick.value || '<?= date('Y-m-d') ?>'; load(); });

    async function load(){
      const params = new URLSearchParams({ period: state.period, date: state.date });
      const url = '<?= site_url('admin_dashboard/dashboard_data') ?>?'+params.toString();
      const r = await fetch(url, { credentials:'same-origin' });
      const j = await r.json();
      if (!j || !j.ok) return;

      kpiVisitors.textContent = j.totals?.visitors ?? 0;
      kpiInstansi.textContent = j.totals?.unique_instansi ?? 0;
      const topU = (j.top_units||[])[0];
      kpiTopUnit.textContent  = topU ? topU.label : '-';
      kpiTopUnitC.textContent = topU ? (`${topU.qty} kunjungan`) : '—';
      setRangeText(j.start, j.end);

      const c1 = ensureTrend();
      c1.xAxis[0].setCategories(j.trend?.labels || [], false);
      c1.series[0].setData(j.trend?.data || [], true);

      const c2 = ensureInstansi();
      c2.series[0].setData((j.top_instansi||[]).map(x=>({ name:x.label, y:x.qty })), true);

      const c3 = ensureUnit();
      c3.xAxis[0].setCategories((j.top_units||[]).map(x=>x.label), false);
      c3.series[0].setData((j.top_units||[]).map(x=>x.qty), true);
    }

    function reflow(){
      setTimeout(()=>{
        hcTrend && hcTrend.reflow();
        hcInstansi && hcInstansi.reflow();
        hcUnit && hcUnit.reflow();
      }, 100);
    }

    return {
      start(){ load(); reflow(); if (refreshTimer) clearInterval(refreshTimer); refreshTimer = setInterval(()=>{ load(); reflow(); }, 20000); },
      stop(){ if (refreshTimer){ clearInterval(refreshTimer); refreshTimer=null; } },
      reflow
    };
  }

  /* ==================== MONITOR ==================== */
  function createMonitorModule(root){
    const elTime   = root.querySelector('.mon-time');
    const elBadge  = root.querySelector('.mon-badge');
    const elBooked = root.querySelector('.mon-booked');
    const elVisit  = root.querySelector('.mon-visit');
    const elCB     = root.querySelector('.mon-count-booked');
    const elCV     = root.querySelector('.mon-count-visit');

    const qInput   = root.querySelector('.mon-q');
    const btnSearch= root.querySelector('.mon-search');
    const btnClear = root.querySelector('.mon-clear');
    const perSel   = root.querySelector('.mon-perpage');
    const btnPrev  = root.querySelector('.mon-prev');
    const btnNext  = root.querySelector('.mon-next');
    const pageInfo = root.querySelector('.mon-pageinfo');
    const btnReload= root.querySelector('.mon-reload');

    let refreshTimer=null, tickerTimer=null, clockTimer=null, serverOffsetMs=0, lastTotalPages=1;
    const state = { q:'', page:1, per_page: parseInt(perSel.value,10)||15 };

    // utils
    function pad(n){ return (n<10?'0':'')+n; }
    function fmtTimeFull(d){ return pad(d.getDate())+'-'+pad(d.getMonth()+1)+'-'+d.getFullYear()+' '+pad(d.getHours())+':'+pad(d.getMinutes())+':'+pad(d.getSeconds()); }
    function fmtTime(dt){ const d=new Date(dt); if(isNaN(d))return '-'; return pad(d.getHours())+':'+pad(d.getMinutes()); }
    function fmtDateID(s){ if(!s||s.length<10) return '-'; const y=s.slice(0,4), m=s.slice(5,7), d=s.slice(8,10); return `${d}-${m}-${y}`; }
    function hariID(s){ if(!s||s.length<10) return ''; const y=+s.slice(0,4), m=+s.slice(5,7)-1, d=+s.slice(8,10); const idx = new Date(y,m,d).getDay(); return ['Min','Sen','Sel','Rab','Kam','Jum','Sab'][idx]||''; }
    function parseDateLoose(s){ if(!s) return NaN; const fast=Date.parse(s); if(!isNaN(fast)) return fast;
      const m=String(s).match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?$/);
      if(!m) return NaN; const Y=+m[1], M=+m[2]-1, D=+m[3], h=+m[4], i=+m[5], sec=+(m[6]||0);
      return new Date(Y,M,D,h,i,sec).getTime();
    }
    function fmtDurFromMs(startMs){
      if(!startMs) return '-';
      const now = Date.now() + serverOffsetMs;
      let sec = Math.max(0, Math.floor((now - startMs)/1000));
      const days = Math.floor(sec / 86400); sec %= 86400;
      const h = Math.floor(sec/3600); sec%=3600;
      const m = Math.floor(sec/60);
      const s = sec%60;
      const hh = pad(h), mm = pad(m), ss = pad(s);
      return (days>0 ? (days+' hari ') : '') + `${hh}:${mm}:${ss}`;
    }

    function renderBooked(list){
      if (!list || !list.length){
        elBooked.innerHTML = `<tr><td colspan="5" class="empty text-center py-3">Tidak ada data.</td></tr>`;
        return;
      }
      elBooked.innerHTML = list.map(r=>{
        const petugas = (r.nama_petugas_instansi||r.petugas_unit||r.petugas||'').trim();
        return `
        <tr class="row-pill row-link" data-url="${r.detail_url}">
          <td class="tgljam">
            <div class="jam">${r.jam ? r.jam : '-'}</div>
            <div class="small text-black tgl"><span class="day">${hariID(r.tanggal)}</span>${fmtDateID(r.tanggal)}</div>
          </td>
          <td class="nama">${(r.nama||'')}</td>
          <td class="asal" style="color:black">${(r.instansi||'-')}</td>
          <td class="unit" style="color:black">
            ${(r.unit||'-')} ${petugas? `<div class="subline"><i class="mdi mdi-account-badge-outline"></i> ${petugas}</div>` : ''}
          </td>
          <td class="pendamping"><span class="pill"><i class="mdi mdi-account-multiple-outline ico"></i>${(r.jumlah_pendamping||0)}</span></td>
        </tr>`;
      }).join('');
    }
    function renderVisit(list){
      if (!list || !list.length){
        elVisit.innerHTML = `<tr><td colspan="5" class="empty text-center py-3">Belum ada pengunjung aktif.</td></tr>`;
        return;
      }
      elVisit.innerHTML = list.map(r=>{
        const startIso = r.checkin_at || '';
        const startMs  = parseDateLoose(startIso);
        const petugas  = (r.nama_petugas_instansi||r.petugas_unit||r.petugas||'').trim();
        return `
        <tr class="row-pill row-link" data-url="${r.detail_url}">
          <td class="jam">${r.checkin_at ? fmtTime(startIso) : '-'}</td>
          <td class="nama">${(r.nama||'')}<div class="small text-black">${(r.instansi||'-')}</div></td>
          <td class="unit" style="color:black">
            ${(r.unit||'-')} ${petugas? `<div class="subline"><i class="mdi mdi-account-badge-outline"></i> ${petugas}</div>` : ''}
          </td>
          <td class="durasi text-primary" data-startms="${!isNaN(startMs)? startMs : ''}">${!isNaN(startMs)? fmtDurFromMs(startMs) : '-'}</td>
          <td class="pendamping"><span class="pill"><i class="mdi mdi-account-multiple-outline ico"></i>${(r.jumlah_pendamping||0)}</span></td>
        </tr>`;
      }).join('');
    }
    function tickDurations(){
      root.querySelectorAll('.mon-visit .durasi').forEach(el=>{
        const startMs = parseInt(el.getAttribute('data-startms'),10);
        el.textContent = startMs ? fmtDurFromMs(startMs) : '-';
      });
    }
    function startClock(){
      if (clockTimer) clearInterval(clockTimer);
      clockTimer = setInterval(()=>{
        const now = new Date(Date.now()+serverOffsetMs);
        elTime.textContent = fmtTimeFull(now);
      },1000);
    }

    async function loadData(){
      const params = new URLSearchParams();
      if (state.q) params.set('q', state.q);
      params.set('page', state.page);
      params.set('per_page', state.per_page);
      const url = `<?= site_url('admin_dashboard/monitor_data') ?>?`+params.toString();

      elBadge.textContent = 'Memuat...'; elBadge.classList.add('blink');

      try{
        const t0=Date.now();
        const r = await fetch(url,{credentials:'same-origin'});
        const j = await r.json();
        const t1=Date.now();

        if (!j || !j.ok) throw new Error('Gagal ambil data');

        if (j.server_time){
          const serverMs = Date.parse(j.server_time);
          const rttHalf  = Math.round((t1 - t0)/2);
          serverOffsetMs = (serverMs + rttHalf) - Date.now();
          startClock();
        }

        if (j.booked_pages > 0 && state.page > j.booked_pages){
          state.page = j.booked_pages;
          return loadData();
        }

        renderBooked(j.booked||[]);
        renderVisit(j.in_visit||[]);
        elCB.textContent = j.count_booked||0;
        elCV.textContent = j.count_visit||0;

        const totalPages = j.booked_pages||0;
        const nowPage    = j.page||1;
        lastTotalPages   = totalPages;
        pageInfo.textContent = `Hal. ${nowPage}/${totalPages||1}`;
        btnPrev.disabled = !(nowPage > 1);
        btnNext.disabled = !(totalPages && nowPage < totalPages);

        elBadge.textContent='Live'; elBadge.classList.remove('blink');
        tickDurations();
      }catch(e){
        elBadge.textContent='Gagal memuat';
        console.error(e);
      }
    }

    // events
    root.querySelector('.mon-booked').addEventListener('click',(e)=>{
      const tr=e.target.closest('tr[data-url]'); if(!tr) return;
      location.href=tr.getAttribute('data-url');
    });
    root.querySelector('.mon-visit').addEventListener('click',(e)=>{
      const tr=e.target.closest('tr[data-url]'); if(!tr) return;
      location.href=tr.getAttribute('data-url');
    });
    btnReload.addEventListener('click', loadData);
    btnSearch.addEventListener('click', ()=>{ state.q=qInput.value.trim(); state.page=1; loadData(); });
    qInput.addEventListener('keydown',(e)=>{ if(e.key==='Enter'){ state.q=qInput.value.trim(); state.page=1; loadData(); }});
    btnClear.addEventListener('click', ()=>{ qInput.value=''; state.q=''; state.page=1; loadData(); });
    perSel.addEventListener('change', ()=>{ state.per_page=parseInt(perSel.value,10)||15; state.page=1; loadData(); });
    btnPrev.addEventListener('click', ()=>{ if (state.page>1){ state.page--; loadData(); }});
    btnNext.addEventListener('click', ()=>{ if (lastTotalPages && state.page<lastTotalPages){ state.page++; loadData(); }});

    return {
      start(){
        loadData();
        if (refreshTimer) clearInterval(refreshTimer);
        refreshTimer = setInterval(loadData, 20000);
        if (tickerTimer) clearInterval(tickerTimer);
        tickerTimer = setInterval(tickDurations, 1000);
      },
      stop(){
        if (refreshTimer){ clearInterval(refreshTimer); refreshTimer=null; }
        if (tickerTimer){ clearInterval(tickerTimer);  tickerTimer=null; }
        if (clockTimer){  clearInterval(clockTimer);   clockTimer=null; }
      }
    };
  }

  /* ==================== SWITCHER & FULLSCREEN ==================== */
  const tabs  = [
    { id:'tab-dashboard', title: document.getElementById('tab-dashboard').dataset.title },
    { id:'tab-monitor'  , title: document.getElementById('tab-monitor').dataset.title  },
  ];

  const dashMod = createDashboardModule(document.getElementById('tab-dashboard'));
  const monMod  = createMonitorModule(document.getElementById('tab-monitor'));
  const modules = [dashMod, monMod];

  // tombol switcher
  const tabsWrap = document.getElementById('tabsWrap');
  tabs.forEach((t,i)=>{
    const sp=document.createElement('span');
    sp.className='tag'+(i===0?' active':'');
    sp.textContent=t.title;
    sp.addEventListener('click', ()=>{ swapTo(i); if(!paused) start(); });
    tabsWrap.appendChild(sp);
  });
  function setActiveTag(n){ [...tabsWrap.children].forEach((el,i)=> el.classList.toggle('active', i===n)); }

  let idx=0, timer=null, paused=false;
  const btnNext=document.getElementById('btnNext');
  const btnPrev=document.getElementById('btnPrev');
  const btnPause=document.getElementById('btnPause');
  const btnFS=document.getElementById('btnFS');
  const tabTitle=document.getElementById('tabTitle');

  function swapTo(n){
    const next = (n + tabs.length) % tabs.length;
    const curPanel  = document.getElementById(tabs[idx].id);
    const nextPanel = document.getElementById(tabs[next].id);

    modules[idx].stop();
    idx = next;
    setActiveTag(idx);
    tabTitle.textContent = tabs[idx].title;

    nextPanel.classList.add('show');
    curPanel.classList.remove('show');

    modules[idx].start();
    // reflow Highcharts jika ke dashboard
    if (idx===0) { dashMod.reflow && dashMod.reflow(); }
    // NOTE: fullscreen tidak ditutup saat transisi karena targetnya #wallWrap (stabil)
  }
  function start(){ stop(); timer=setInterval(()=> swapTo(idx+1), SEC*1000); }
  function stop(){ if(timer){ clearInterval(timer); timer=null; } }

  btnNext.addEventListener('click', ()=>{ swapTo(idx+1); if(!paused) start(); });
  btnPrev.addEventListener('click', ()=>{ swapTo(idx-1); if(!paused) start(); });
  btnPause.addEventListener('click', ()=>{ paused=!paused; btnPause.textContent = paused ? '▶︎' : '⏸︎'; if (paused) stop(); else start(); });

  // Fullscreen target TETAP ke wrapper utama → tidak keluar saat tab ganti
  const FS_TARGET = document.getElementById('wallWrap');
  btnFS.addEventListener('click', async ()=>{
    if (!document.fullscreenElement){
      if (FS_TARGET.requestFullscreen) await FS_TARGET.requestFullscreen({navigationUI:'hide'});
      else if (FS_TARGET.webkitRequestFullscreen) FS_TARGET.webkitRequestFullscreen();
    } else {
      try { await document.exitFullscreen(); }catch(e){}
    }
  });

  document.addEventListener('fullscreenchange', ()=>{
    if (document.fullscreenElement){ document.body.classList.add('fs-active'); }
    else                           { document.body.classList.remove('fs-active'); }
  });

  document.addEventListener('keydown', function(e){
    const k=(e.key||'').toLowerCase();
    if (k==='n'){ e.preventDefault(); btnNext.click(); }
    else if (k==='p'){ e.preventDefault(); btnPrev.click(); }
    else if (k===' '){ e.preventDefault(); btnPause.click(); }
    else if (k==='f'){ e.preventDefault(); btnFS.click(); }
  });

  if (AUTOFS){ setTimeout(()=>{ try{ btnFS.click(); }catch(e){} }, 600); }

  // Mulai
  modules[0].start();
  start();
})();
</script>
</body>
</html>
