<!-- ==== Embed Mode Trim ==== -->
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

<style>
  .board-card{border:1px solid #eef0f3;border-radius:14px}
  .board-head{border-bottom:1px dashed #e5e7eb}
  .stat{font-weight:700}
  .badge-soft{border:1px solid #e5e7eb;background:#f8fafc}
  .list-table{width:100%;border-collapse:separate;border-spacing:0 6px}
  .list-table th{font-size:.9rem;color:#6b7280;font-weight:600}
  .list-table td{background:#fff;box-shadow:0 1px 0 rgba(0,0,0,.03);padding:10px 12px;border-top:1px solid #eef0f3;border-bottom:1px solid #eef0f3}
  .row-pill{border-radius:10px;transition:background .15s}
  .row-link{cursor:pointer}
  .row-link:hover{background:#f8fafc}
  .kode{font-weight:700}
  .nama{font-weight:700}
  .unit,.asal,.petugas{color:#374151}
  .jam,.tgl{font-variant-numeric:tabular-nums}
  .tgl .day{display:inline-block;margin-right:.35rem;color:#64748b}
  .empty{color:#94a3b8}
  .count{font-size:1.6rem;font-weight:800}
  .blink{animation:pulse 1.6s infinite}
  .subline{font-size:.85rem;color:#64748b}
  .pill{display:inline-flex;align-items:center;gap:.35rem;background:#f8fafc;border:1px solid #e5e7eb;border-radius:999px;padding:.1rem .5rem;font-weight:600}
  .ico{width:1.15rem;display:inline-block;text-align:center}
  .pendamping{text-align:center}
  @keyframes pulse{0%{opacity:.45}50%{opacity:1}100%{opacity:.45}}

  /* === Enhancements: Server Time === */
  .server-time{font-size:1.8rem;font-weight:800;color:#0f172a;letter-spacing:.5px;font-variant-numeric:tabular-nums;text-shadow:0 1px 2px rgba(0,0,0,.15)}
  .server-time-wrap{
    display:inline-flex;align-items:center;gap:.6rem;
    background:linear-gradient(180deg,#ffffff,#f8fafc);
    border:1px solid #e5e7eb;border-radius:12px;padding:.35rem .7rem;
    box-shadow:0 2px 6px rgba(15,23,42,.06)
  }
  .server-time-icon{
    display:inline-flex;align-items:center;justify-content:center;
    width:28px;height:28px;border-radius:50%;
    background:#eef2ff;border:1px solid #e5e7eb;font-size:1rem;color:#374151
  }
  @media (max-width:768px){
    .server-time{font-size:1.4rem}
    .count{font-size:1.4rem}
  }
  #infoScope{
    font-size:1.6rem;
    font-weight:800;
    color:#0f172a;
    letter-spacing:.4px;
    text-shadow:0 1px 1px rgba(0,0,0,.08);
  }

  /* === Enhancements: Ruang Lingkup === */
  .scope-text{
    font-size:1.6rem;
    font-weight:800;
    color:#0f172a;
    letter-spacing:.4px;
    font-variant-numeric:tabular-nums;
    text-shadow:0 1px 1px rgba(0,0,0,.08);
  }
  .scope-wrap{
    display:inline-flex;align-items:center;gap:.6rem;
    background:linear-gradient(180deg,#ffffff,#f8fafc);
    border:1px solid #e5e7eb;border-radius:12px;padding:.35rem .7rem;
    box-shadow:0 2px 6px rgba(15,23,42,.06)
  }
  .scope-icon{
    display:inline-flex;align-items:center;justify-content:center;
    width:28px;height:28px;border-radius:50%;
    background:#F54927;border:1px solid #e5e7eb;font-size:1rem;color:#0ea5e9
  }
  @media (max-width:768px){ .scope-text{font-size:1.35rem} }
  .brand{display:flex;align-items:center;gap:.6rem}
  .brand .dot{width:10px;height:10px;border-radius:50%;background:var(--accent);box-shadow:0 0 0 0 rgba(34,197,94,.6);animation:ping 1.6s infinite}
  @keyframes ping{0%{box-shadow:0 0 0 0 rgba(34,197,94,.6)}80%{box-shadow:0 0 0 12px rgba(34,197,94,0)}100%{box-shadow:0 0 0 0 rgba(34,197,94,0)}}
  .brand h1{font-size:1rem;margin:0}
  /* .row-disabled{opacity:.6; cursor:not-allowed;} */
</style>

<?php $web = $this->om->web_me(); ?>
<!-- Info Bar -->
<div class="row">
  <div class="col-12">
    <div class="card board-card shadow-sm mb-3">
      <div class="card-body d-flex flex-wrap justify-content-between align-items-center board-head pb-2">
        <div class="d-flex align-items-center" style="gap:1rem;">
          <div>
            <div class="text-dark small mb-1">Ruang Lingkup</div>
            <div class="scope-wrap">
              <span class="scope-icon"><i class="mdi mdi-office-building-marker-outline"></i></span>
              <span id="infoScope" class="scope-text"><?php echo $web->type ?></span>
            </div>
          </div>

          <div>
            <div class="text-dark small mb-1">Waktu Lokasi <?php echo $web->waktu ?></div>
            <div class="server-time-wrap">
              <span class="server-time-icon"><i class="mdi mdi-clock-outline"></i></span>
              <span id="infoTime" class="server-time">--:--:--</span>
            </div>
            <button id="btnRefresh" class="btn btn-lg btn-rounded ml-2" style="background-color: black">
              <span class="badge-live" id="stateBadge"><strong>Live</strong></span>
            </button>
            <style>
              .badge-live{position:relative;display:inline-flex;align-items:center;gap:.80rem;transform-origin:center;animation:livePulse 1.25s ease-in-out infinite}
              .badge-live::before{content:"";width:8px;height:8px;border-radius:50%;background:#22c55e;box-shadow:0 0 0 0 rgba(34,197,94,.6);animation:livePing 1.25s cubic-bezier(0,0,.2,1) infinite}
              @keyframes livePulse{0%,100%{transform:scale(1)}50%{transform:scale(1.06)}}
              @keyframes livePing{0%{box-shadow:0 0 0 0 rgba(34,197,94,.55)}80%{box-shadow:0 0 0 10px rgba(34,197,94,0)}100%{box-shadow:0 0 0 0 rgba(34,197,94,0)}}
              @media (prefers-reduced-motion:reduce){.badge-live,.badge-live::before{animation:none!important}}
            </style>
          </div>
        </div>

        <div class="d-flex align-items-center" style="gap:1rem;">
          <div class="text-center">
            <div class="text-dark small">Jumlah Sudah Booking</div>
            <div id="countBooked" class="count">0</div>
          </div>
          <div class="text-center">
            <div class="text-dark small">Sedang Berkunjung</div>
            <div id="countVisit" class="count">0</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Dua kolom -->
<div class="row">
  <!-- Kiri: Akan Datang -->
  <div class="col-xl-6">
    <div class="card board-card shadow-sm">
      <div class="card-body">
        <h5 class="header-title">Akan Datang (Belum Check-in)</h5>

        <!-- Toolbar cari + per page -->
        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap" style="gap:.75rem;">
          <div class="input-group" style="max-width:460px;">
            <input type="text" id="q" class="form-control" placeholder="Cari nama / kode / unit / instansi / keperluan...">
            <div class="input-group-append">
              <button class="btn btn-outline-primary" id="btnSearch" title="Cari"><i class="mdi mdi-magnify"></i></button>
              <button class="btn btn-outline-secondary" id="btnClear" title="Bersihkan"><i class="mdi mdi-close"></i></button>
            </div>
          </div>
          <div class="form-inline">
            <label class="mr-2 small text-muted">Baris</label>
            <select id="perPage" class="form-control form-control-sm">
              <option value="5" selected>5</option>
              <option value="10">10</option>
              <option value="15">15</option>
              <option value="25">25</option>
              <option value="50">50</option>
            </select>
          </div>
        </div>

        <table class="" width="100%">
          <tbody id="tblBooked">
            <tr><td colspan="5" class="empty text-center py-3">Memuat...</td></tr>
          </tbody>
        </table>

        <!-- Pager -->
        <div class="d-flex justify-content-between align-items-center mt-2">
          <div class="small text-muted" id="pageInfo">Hal. 1/1</div>
          <div>
            <button class="btn btn-sm btn-outline-secondary mr-2" id="btnPrev" disabled>« Sebelumnya</button>
            <button class="btn btn-sm btn-outline-secondary" id="btnNext" disabled>Berikutnya »</button>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Kanan: Sedang Berkunjung -->
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
            <tbody id="tblVisit">
              <tr><td colspan="5" class="empty text-center py-3">Belum ada pengunjung aktif.</td></tr>
            </tbody>
          </table>
        </div>
        <div class="small text-muted mt-2">Durasi diperbarui otomatis.</div>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const elTime   = document.getElementById('infoTime');
  const elBadge  = document.getElementById('stateBadge');
  const elBooked = document.getElementById('tblBooked');
  const elVisit  = document.getElementById('tblVisit');
  const elCB     = document.getElementById('countBooked');
  const elCV     = document.getElementById('countVisit');
  const btnRef   = document.getElementById('btnRefresh');

  // Search & paging UI
  const qInput   = document.getElementById('q');
  const btnSearch= document.getElementById('btnSearch');
  const btnClear = document.getElementById('btnClear');
  const perSel   = document.getElementById('perPage');
  const btnPrev  = document.getElementById('btnPrev');
  const btnNext  = document.getElementById('btnNext');
  const pageInfo = document.getElementById('pageInfo');

  // Fullscreen (opsional, guard kalau tidak ada)
  const btnFS    = document.getElementById('btnFullscreen');
  const fsEl     = document.getElementById('fsContainer');

  let refreshTimer = null;
  let tickerTimer  = null;

  // ======== FORMATTER WAKTU (cache) ========
  window.serverTz = window.serverTz || 'Asia/Makassar';
  let _fmtServer = null, _fmtWeekday = null, _fmtTz = null;
  function initServerFormatter(){
    const tz = window.serverTz || 'Asia/Makassar';
    if (_fmtServer && _fmtWeekday && _fmtTz === tz) return;
    _fmtServer = new Intl.DateTimeFormat('id-ID', {
      timeZone: tz,
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit',
      hour12: false
    });
    _fmtWeekday = new Intl.DateTimeFormat('id-ID', {
      timeZone: tz, weekday: 'long'
    });
    _fmtTz = tz;
  }
  initServerFormatter();

  // --- utils waktu/format ---
  function pad(n){ return (n<10?'0':'')+n; }
  function fmtTime(dt){
    const d=new Date(dt);
    if(isNaN(d))return '-';
    return pad(d.getHours())+':'+pad(d.getMinutes());
  }
  function fmtDateID(s){
    if(!s||s.length<10) return '-';
    const y=s.slice(0,4), m=s.slice(5,7), d=s.slice(8,10);
    return `${d}-${m}-${y}`;
  }
  function hariID(s){
    if(!s||s.length<10) return '';
    const y=+s.slice(0,4), m=+s.slice(5,7)-1, d=+s.slice(8,10);
    const idx = new Date(y,m,d).getDay(); // 0=Min ... 6=Sab
    const map = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
    return map[idx] || '';
  }
  function parseDateLoose(s){
    if(!s) return NaN;
    const fast = Date.parse(s);
    if (!isNaN(fast)) return fast;
    const m = String(s).match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?$/);
    if (!m) return NaN;
    const Y=+m[1], M=+m[2]-1, D=+m[3], h=+m[4], i=+m[5], sec=+(m[6]||0);
    return new Date(Y, M, D, h, i, sec).getTime();
  }
  function fmtDurFromMs(startMs){
    if(!startMs) return '-';
    const now = Date.now() + (window.serverOffsetMs || 0);
    let sec = Math.max(0, Math.floor((now - startMs)/1000));
    const days = Math.floor(sec / 86400); sec %= 86400;
    const h = Math.floor(sec/3600); sec%=3600;
    const m = Math.floor(sec/60);
    const s = sec%60;
    return (days>0 ? (days+' hari ') : '') + `${pad(h)}:${pad(m)}:${pad(s)}`;
  }

  // --- CLOCK ---
  function fmtMsInServerTz(ms, { withDay = false } = {}) {
    initServerFormatter();
    const d = new Date(ms);
    const parts = _fmtServer.formatToParts(d);
    const map = Object.fromEntries(parts.map(p => [p.type, p.value]));
    const wd = withDay ? (_fmtWeekday.format(d).replace('.', '') + ', ') : '';
    return `${wd}${map.day}-${map.month}-${map.year} ${map.hour}:${map.minute}:${map.second}`;
  }
  function startClock(){
    if (!elTime) return;
    if (window.clockTimer) clearInterval(window.clockTimer);
    window.clockTimer = setInterval(()=>{
      const ms = Date.now() + (window.serverOffsetMs || 0);
      elTime.textContent = fmtMsInServerTz(ms, { withDay: true });
    }, 1000);
  }

  // --- escape util ---
  function safe(v){ return (v==null ? '' : String(v)); }
  function esc(s){ return safe(s).replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' }[m])); }

  // --- RENDERERS ---
  function renderBooked(list){
  const html = (!list || !list.length)
    ? `<tr><td colspan="5" class="empty text-center py-3">Tidak ada data.</td></tr>`
    : list.map(r=>{
        const petugas  = safe(r.nama_petugas_instansi || r.petugas_unit || r.petugas).trim();
        const clickable = !!(r.can_open && r.detail_url);
        const trClass = 'row-pill' + (clickable ? ' row-link' : ' row-disabled');
        const trAttr  = clickable ? ` data-url="${esc(r.detail_url)}" title="Buka detail"` : ` title="Akses tidak diizinkan"`;
        return `
<tr class="${trClass}" ${trAttr} role="button" tabindex="0" style="cursor:pointer">
  <td colspan="5" class="p-0">
    <div class="card-box mb-2">
      <div class="row align-items-center">
        <div class="col-sm-6">
          <div class="media">
            <img class="d-flex align-self-center mr-1"
                 src="${esc(r.logo_ins || '')}"
                 alt="${esc(r.instansi || r.nama || 'Instansi')}"
                 height="64" width="64"
                 loading="lazy" decoding="async"
                 onerror="this.style.display='none'">
            <div class="media-body">
              <h4 class="mt-0 mb-1 font-16">${esc(r.instansi || '-')}</h4>
              <p class="mb-0"><i class="mdi mdi-account-outline mr-1"></i>${esc(r.nama || '-')}</p>
              <p class="mb-0"><i class="mdi mdi-briefcase-outline mr-1"></i>${esc(r.jabatan || '-')}</p>
              <p class="mb-0">
                <span class="badge badge-light">
                  <i class="mdi mdi-account-multiple-outline mr-1"></i>${(r.jumlah_pendamping || 0)} pendamping
                </span>
              </p>
            </div>
          </div>
        </div>
        <div class="col-sm-2">
          <p class="mb-1 mt-3 mt-sm-0">
            <i class="mdi mdi-clock-outline mr-1"></i> ${r.jam ? esc(r.jam) : '-'}
          </p>
          <p class="mb-0 small text-black">
            <span class="day">${hariID(r.tanggal)}</span> ${fmtDateID(r.tanggal)}
          </p>
        </div>
        <div class="col-sm-4">
          <p class="mb-1 mt-3 mt-sm-0">
            <i class="mdi mdi-domain mr-1"></i> ${esc(r.unit || '-')}
          </p>
          ${petugas ? `
          <p class="mb-1 small text-muted">
            <i class="mdi mdi-account-badge-outline mr-1"></i> ${esc(petugas)}
          </p>` : ``}
        </div>
      </div>
    </div>
  </td>
</tr>`;
      }).join('');

  return replaceIfChanged(elBooked, html);
}

function renderVisit(list){
  const html = (!list || !list.length)
    ? `<tr><td colspan="5" class="empty text-center py-3">Belum ada pengunjung aktif.</td></tr>`
    : list.map(r=>{
        const startIso  = r.checkin_at || '';
        const startMs   = parseDateLoose(startIso);
        const durInit   = startIso ? fmtDurFromMs(startMs) : '-';
        const petugas   = safe(r.nama_petugas_instansi || r.petugas_unit || r.petugas).trim();
        const clickable = !!(r.can_open && r.detail_url);
        const trClass   = 'row-pill' + (clickable ? ' row-link' : ' row-disabled');
        const trAttr    = clickable ? ` data-url="${esc(r.detail_url)}" title="Buka detail"` : ` title="Akses tidak diizinkan"`;
        return `
<tr class="${trClass}"${trAttr}>
  <td class="jam">${r.checkin_at ? fmtTime(startIso) : '-'}</td>
  <td class="nama">
    ${esc(r.nama)}
    <div class="small text-black">${esc(r.instansi || '-')}</div>
  </td>
  <td class="unit" style="color:black">
    ${esc(r.unit || '-')}
    ${petugas ? `<div class="subline"><i class="mdi mdi-account-badge-outline"></i> ${esc(petugas)}</div>` : ''}
  </td>
  <td class="durasi text-primary" data-checkin="${esc(startIso)}" data-startms="${!isNaN(startMs)? startMs : ''}">${durInit}</td>
  <td class="pendamping">
    <span class="pill"><i class="mdi mdi-account-multiple-outline ico"></i>${(r.jumlah_pendamping||0)}</span>
  </td>
</tr>`;
      }).join('');

  const changed = replaceIfChanged(elVisit, html);
  if (changed) {            // DOM berubah → refresh cache node durasi
    cacheDurNodes();
    tickDurationsFast();
  }
  return changed;
}


  // ====== DURASI TICK (hemat DOM) ======
  let DUR_NODES = [];
  function cacheDurNodes(){
    DUR_NODES = Array.from(document.querySelectorAll('#tblVisit .durasi')).map(el=>{
      let ms = +el.getAttribute('data-startms');
      if (!Number.isFinite(ms)) {
        const iso = el.getAttribute('data-checkin') || '';
        const parsed = parseDateLoose(iso);
        if (Number.isFinite(parsed)) {
          ms = parsed;
          el.setAttribute('data-startms', String(parsed));
        }
      }
      el._startMs = ms || null;
      el._lastTxt = el.textContent;
      return el;
    });
  }
  function fmtDurFromMsFast(nowMs, startMs){
    let sec = Math.max(0, (nowMs - startMs) / 1000 | 0);
    const days = (sec / 86400) | 0; sec %= 86400;
    const h = (sec / 3600) | 0; sec %= 3600;
    const m = (sec / 60) | 0;
    const s = (sec % 60) | 0;
    const p = n => (n<10?'0':'')+n;
    return (days>0 ? (days+' hari ') : '') + `${p(h)}:${p(m)}:${p(s)}`;
  }
  function tickDurationsFast(){
    const now = Date.now() + (window.serverOffsetMs || 0);
    for (const el of DUR_NODES){
      const startMs = el._startMs;
      const txt = startMs ? fmtDurFromMsFast(now, startMs) : '-';
      if (txt !== el._lastTxt){
        el.textContent = txt;
        el._lastTxt = txt;
      }
    }
  }

  // ====== STATE ======
  const state = {
    q: '',
    page: 1,
    per_page: parseInt(perSel?.value,10) || 15,
  };
  let hasRenderedOnce = false;
  let lastPageRendered = 1;

  // ====== LOADER (stale-while-revalidate) ======
  let loadAbort = null;

  async function loadData(){
    // Saat memuat: JANGAN ubah tabel kalau sudah pernah render.
    // Tampilkan status hanya di badge.
    elBadge.textContent = 'Memuat...';
    elBadge.classList.add('blink');

    // Untuk load pertama (belum ada data lama), tampilkan placeholder sederhana.
    if (!hasRenderedOnce){
      elBooked.innerHTML = `<tr><td colspan="5" class="empty text-center py-3">Memuat...</td></tr>`;
      elVisit .innerHTML = `<tr><td colspan="5" class="empty text-center py-3">Memuat...</td></tr>`;
    }

    if (loadAbort) { loadAbort.abort(); }
    loadAbort = new AbortController();

    const requestedPage = state.page;

    const params = new URLSearchParams();
    if (state.q) params.set('q', state.q);
    params.set('page', state.page);
    params.set('per_page', state.per_page);
    const url = `<?= site_url('admin_dashboard/monitor_data') ?>?` + params.toString();

    try {
      const t0 = performance.now();
      const res = await fetch(url, {
        credentials: 'same-origin',
        signal: loadAbort.signal,
        cache: 'no-store',
        keepalive: false
      });
      const j = await res.json();
      if (!j || !j.ok) throw new Error('Gagal ambil data');

      // sinkron waktu server
      if (j.server_ms != null || j.server_time) {
        const rttHalf = Math.round((performance.now() - t0) / 2);
        const baseServerMs = Number.isFinite(+j.server_ms) ? +j.server_ms : Date.parse(j.server_time || '');
        if (Number.isFinite(baseServerMs)) {
          window.serverOffsetMs = (baseServerMs + rttHalf) - Date.now();
          if (j.server_tz && j.server_tz !== window.serverTz) {
            window.serverTz = j.server_tz;
            initServerFormatter();
          }
          startClock();
        }
      }

      // pagination guard
      if (j.booked_pages > 0 && state.page > j.booked_pages){
        state.page = j.booked_pages;
        return loadData();
      }

      // render baru (replace DOM)
      renderBooked(j.booked || []);
      renderVisit(j.in_visit || []);
      elCB.textContent = j.count_booked ?? 0;
      elCV.textContent = j.count_visit  ?? 0;

      const lastTotalPages = j.booked_pages ?? 0;
      const nowPage  = j.page ?? requestedPage;
      pageInfo.textContent = `Hal. ${nowPage}/${lastTotalPages || 1}`;
      btnPrev.disabled = !(nowPage > 1);
      btnNext.disabled = !(lastTotalPages && nowPage < lastTotalPages);

      hasRenderedOnce   = true;
      lastPageRendered  = nowPage;

      elBadge.textContent = 'Live';
      elBadge.classList.remove('blink');

      cacheDurNodes();
      tickDurationsFast();

    } catch (e) {
      if (e.name === 'AbortError') return; // di-cancel → diam (konten lama tetap)
      // Gagal: tampilkan status tapi JANGAN ubah konten lama
      elBadge.textContent = 'Gagal memuat';
      elBadge.classList.remove('blink');
      // Revert page jika perlu (agar UI konsisten dengan konten lama)
      state.page = lastPageRendered;
      console.error(e);
    }
  }

  // ====== TIMERS (adaptif & hemat) ======
  function startTimers(){
    if (refreshTimer) clearTimeout(refreshTimer);
    const refreshLoop = ()=> {
      if (document.hidden) { refreshTimer = setTimeout(refreshLoop, 10000); return; }
      loadData().finally(()=> { refreshTimer = setTimeout(refreshLoop, 20000); });
    };
    refreshLoop();

    if (tickerTimer) clearInterval(tickerTimer);
    tickerTimer = setInterval(()=>{ if (!document.hidden) tickDurationsFast(); }, 1000);
  }
  document.addEventListener('visibilitychange', ()=>{
    if (!document.hidden){ tickDurationsFast(); }
  });

  // --- delegasi klik (satu kali saja) ---
  ['tblBooked','tblVisit'].forEach(id=>{
    const el = document.getElementById(id);
    el.addEventListener('click', (e)=>{
      const tr = e.target.closest('tr[data-url]');
      if (!tr) return;
      const url = tr.getAttribute('data-url');
      if (url) location.href = url;
    });
  });
  document.addEventListener('keydown', (e)=>{
    if (e.key !== 'Enter') return;
    const row = document.activeElement?.closest?.('tr.row-link[data-url]');
    if (!row) return;
    const url = row.getAttribute('data-url');
    if (url) location.href = url;
  });

  // --- bind UI ---
  btnRef?.addEventListener('click', loadData);
  btnSearch?.addEventListener('click', ()=>{ state.q = qInput.value.trim(); state.page = 1; loadData(); });
  qInput?.addEventListener('keydown', (e)=>{ if (e.key==='Enter'){ state.q = qInput.value.trim(); state.page = 1; loadData(); }});
  btnClear?.addEventListener('click', ()=>{ qInput.value=''; state.q=''; state.page=1; loadData(); });
  perSel?.addEventListener('change', ()=>{ state.per_page = parseInt(perSel.value,10)||15; state.page=1; loadData(); });
  btnPrev?.addEventListener('click', ()=>{ if (state.page>1){ state.page--; loadData(); }});
  btnNext?.addEventListener('click', ()=>{ state.page++; loadData(); });

  // fullscreen guard
  if (btnFS && fsEl){
    btnFS.addEventListener('click', async ()=>{
      try{
        if (!document.fullscreenElement){
          await (fsEl.requestFullscreen ? fsEl.requestFullscreen({navigationUI:'hide'}) : document.documentElement.requestFullscreen());
          btnFS.innerHTML = '<i class="mdi mdi-fullscreen-exit"></i> Exit Fullscreen';
        }else{
          await document.exitFullscreen();
          btnFS.innerHTML = '<i class="mdi mdi-fullscreen"></i> Fullscreen';
        }
      }catch(e){ console.warn(e); }
    });
    document.addEventListener('fullscreenchange', ()=>{
      if (!document.fullscreenElement){
        btnFS.innerHTML = '<i class="mdi mdi-fullscreen"></i> Fullscreen';
      }
    });
  }

  // ==== GO ====
  loadData();
  startClock();
  startTimers();
})();

function replaceIfChanged(el, html){
  if (el.__lastHTML !== html){
    el.innerHTML = html;
    el.__lastHTML = html;
  }
}

</script>
