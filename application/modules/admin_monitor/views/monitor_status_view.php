<style type="text/css">
  #widgetTvMonitor .tv-dot{
  display:inline-block;
  width:10px;
  height:10px;
  border-radius:50%;
  background:#6c757d;
  box-shadow:0 0 0 0 rgba(40,167,69,0); /* hijau transparan default */
  transition:background-color .2s ease, box-shadow .3s ease;
}
#widgetTvMonitor .tv-dot.online{
  background:#28a745; /* hijau */
  box-shadow:0 0 0 4px rgba(40,167,69,0.25);
}
#widgetTvMonitor .tv-dot.offline{
  background:#6c757d; /* abu */
  box-shadow:none;
}
#widgetTvMonitor .badge-online{
  background-color:#28a745;
  color:#fff;
}
#widgetTvMonitor .badge-offline{
  background-color:#6c757d;
  color:#fff;
}
.tv-monitor-card{
  border:0;
  border-radius:.9rem;
  background: linear-gradient(135deg, #0f172a, #1e293b);
  color:#e5e7eb;
  box-shadow:0 12px 30px rgba(15,23,42,0.45);
}
.tv-monitor-card .card-body{
  padding:1.25rem 1.5rem;
}
.tv-monitor-card .text-muted{
  color:rgba(148,163,184,0.9) !important;
}
.tv-dot-wrapper{
  position:relative;
}
.tv-dot{
  display:inline-block;
  width:18px;
  height:18px;
  border-radius:999px;
  background:#6b7280;
  box-shadow:0 0 0 0 rgba(34,197,94,0);
  position:relative;
  transition:background-color .25s ease, box-shadow .3s ease;
}
.tv-dot::after{
  content:'';
  position:absolute;
  inset:0;
  border-radius:999px;
  border:2px solid rgba(148,163,184,0.8);
}
.tv-dot.online{
  background:#22c55e;
  box-shadow:0 0 0 6px rgba(34,197,94,0.35);
}
.tv-dot.offline{
  background:#6b7280;
  box-shadow:none;
}
.badge-online{
  background-color:#22c55e;
  color:#022c22;
}
.badge-offline{
  background-color:#4b5563;
  color:#e5e7eb;
}
.border-right-md{
  border-right:0;
}
@media (min-width:768px){
  .border-right-md{
    border-right:1px solid rgba(148,163,184,0.25);
  }
}

</style>
<style type="text/css">
/* ===== KARTU DASAR ===== */
.tv-card{
  border:0;
  border-radius:.9rem;
  color:#e5e7eb;
  box-shadow:0 10px 24px rgba(15,23,42,0.38);
  overflow:hidden;
}
.tv-card .card-body{
  padding:1rem 1.25rem;
}
.tv-card .text-muted{
  color:rgba(226,232,240,0.85) !important;
}
.tv-card h4,
.tv-card .font-weight-medium{
  color:#f9fafb;
}

/* ===== VARIAN WARNA ===== */
.tv-card--status{
  background: linear-gradient(135deg, #0f172a, #1e293b); /* biru gelap */
}
.tv-card--ip{
  background: linear-gradient(135deg, #064e3b, #047857); /* hijau */
}
.tv-card--browser{
  background: linear-gradient(135deg, #312e81, #6d28d9); /* ungu */
}
.tv-card--last{
  background: linear-gradient(135deg, #78350f, #d97706); /* oranye */
}

/* ===== DOT STATUS ===== */
.tv-dot-wrapper{
  position:relative;
}
.tv-dot{
  display:inline-block;
  width:18px;
  height:18px;
  border-radius:999px;
  background:#6b7280;
  box-shadow:0 0 0 0 rgba(34,197,94,0);
  position:relative;
  transition:background-color .25s ease, box-shadow .3s ease;
}
.tv-dot::after{
  content:'';
  position:absolute;
  inset:0;
  border-radius:999px;
  border:2px solid rgba(148,163,184,0.9);
}
.tv-dot.online{
  background:#22c55e;
  box-shadow:0 0 0 6px rgba(34,197,94,0.35);
}
.tv-dot.offline{
  background:#6b7280;
  box-shadow:none;
}

/* ===== BADGE STATUS ===== */
.badge-online{
  background-color:#bbf7d0;
  color:#064e3b;
}
.badge-offline{
  background-color:rgba(15,23,42,0.35);
  color:#e5e7eb;
}
/* Widget Nyala Monitor */
.tv-widget-session{
  border:0;
  border-radius:.9rem;
  background:linear-gradient(135deg,#0f172a,#1d4ed8);
  color:#e5e7eb;
  box-shadow:0 12px 30px rgba(15,23,42,0.5);
}
.tv-widget-session .card-body{
  padding:1.25rem 1.5rem;
}
.tv-widget-session .badge-session{
  background:rgba(15,23,42,0.92);
  color:#e5e7eb;
  font-size:.75rem;
}

</style>
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
<div class="row mt-2">

  <!-- KOLOM KIRI: STATUS + IP + BROWSER -->
  <div class="col-md-6 mb-3">
    <div class="card tv-card tv-card--status" id="widgetTvLeft">
      <div class="card-body">
        <!-- STATUS MONITOR -->
        <div class="text-muted text-uppercase mb-1">Status Monitor</div>
        <div class="d-flex align-items-center mb-3">
          <div class="tv-dot-wrapper mr-3">
            <span class="tv-dot offline" id="tvDot"></span>
          </div>
          <div>
            <h4 class="mb-1" id="tvTitle">TV Billiard</h4>
            <span id="tvBadge" class="badge badge-pill badge-offline">Offline</span>
          </div>
        </div>
        <div class="small text-muted mb-3" id="tvSubtitle">
          Memeriksa status…
        </div>

        <hr class="my-2">

        <!-- IP MONITOR -->
        <div class="mb-3">
          <div class="text-muted text-uppercase small mb-1">IP Monitor (TV)</div>
          <div id="tvIp" class="font-weight-medium">-</div>
          <!--
          <div id="tvLocation" class="small text-muted mt-1">Lokasi: -</div>
          -->
        </div>

        <hr class="my-2">

        <!-- BROWSER -->
        <div>
          <div class="text-muted text-uppercase small mb-1">Browser</div>
          <div id="tvBrowser" class="font-weight-medium">-</div>
          <div class="small text-muted mt-1">
            Diambil dari user agent monitor (TV).
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- KOLOM KANAN: NYALA MONITOR + TERAKHIR AKTIF -->
  <div class="col-md-6 mb-3">
    <div class="card tv-widget tv-widget-session" id="widgetTvRight">
      <div class="card-body py-3">

        <!-- NYALA MONITOR -->
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div>
            <div class="text-uppercase small text-muted">Nyala Monitor</div>
            <h5 class="mb-0" style="color: white">Riwayat &amp; Sesi Aktif</h5>
          </div>
          <span class="badge badge-pill badge-session" id="tvSessionBadge">Sesi aktif</span>
        </div>

        <div class="small text-muted mb-1">Pertama kali terdeteksi</div>
        <div class="font-weight-medium mb-2" id="tvFirstSeen">-</div>

        <div class="small text-muted mb-1">Sesi online saat ini</div>
        <div class="font-weight-medium" id="tvSessionStart">-</div>
        <div class="small text-muted mt-1 mb-2">
          Durasi sesi:
          <span class="font-weight-semibold" id="tvSessionDuration">-</span>
        </div>

        <hr class="my-3">

        <!-- TERAKHIR AKTIF -->
        <div>
          <div class="text-muted text-uppercase small mb-1">Terakhir Aktif</div>
          <div id="tvLastSeen" class="font-weight-medium">-</div>
          <div class="small text-muted mt-1">
            Data dari ping terakhir halaman <em>Live Billiard</em> di TV.
          </div>
        </div>

      </div>
    </div>
  </div>

</div>

</div>
<script>
(function(){
  var ENDPOINT = "<?= site_url('admin_monitor/status_json'); ?>";

  var dot        = document.getElementById('tvDot');
  var badge      = document.getElementById('tvBadge');
  var subtitle   = document.getElementById('tvSubtitle');
  var titleEl    = document.getElementById('tvTitle');
  var ipEl       = document.getElementById('tvIp');
  var locEl      = document.getElementById('tvLocation');
  var browserEl  = document.getElementById('tvBrowser');
  var lastSeenEl = document.getElementById('tvLastSeen');

  // elemen tambahan untuk widget NYALA MONITOR
  var firstSeenEl       = document.getElementById('tvFirstSeen');
  var sessionStartEl    = document.getElementById('tvSessionStart');
  var sessionDurationEl = document.getElementById('tvSessionDuration');
  var sessionBadgeEl    = document.getElementById('tvSessionBadge');

  if (!dot || !badge) return;

  // ====== STATE UNTUK LIVE TIMER ======
  var lastSeenDate     = null;
  var sessionStartDate = null;
  var firstSeenDate    = null;
  var liveTimer        = null;

  // ====== FORMAT RELATIF: detik / menit / jam lalu ======
  function fmtRelative(sec){
    sec = parseInt(sec||0,10);
    if (sec < 0) sec = 0;

    if (sec < 60){
      return sec + ' detik lalu';
    }

    var m = Math.floor(sec/60);
    if (m < 60){
      return '±' + m + ' menit lalu';
    }

    var h = Math.floor(m/60);
    var sisaMenit = m % 60;
    if (sisaMenit > 0){
      return '±' + h + ' jam ' + sisaMenit + ' menit lalu';
    }
    return '±' + h + ' jam lalu';
  }

  // ====== FORMAT DURASI (untuk lama sesi) ======
  function fmtDuration(sec){
    sec = parseInt(sec||0,10);
    if (sec < 0) sec = 0;

    var d = Math.floor(sec/86400); sec %= 86400;
    var h = Math.floor(sec/3600);  sec %= 3600;
    var m = Math.floor(sec/60);
    var s = sec % 60;

    var parts = [];
    if (d) parts.push(d + ' hari');
    if (h) parts.push(h + ' jam');
    if (m) parts.push(m + ' menit');
    parts.push(s + ' detik');
    return parts.join(' ');
  }

  // ====== FORMAT TANGGAL INDO + JAM ======
  var fmtTanggalWaktu = new Intl.DateTimeFormat('id-ID', {
    weekday:'long',
    day:'2-digit',
    month:'long',
    year:'numeric',
    hour:'2-digit',
    minute:'2-digit',
    second:'2-digit'
  });

  function formatTanggalIndo(date){
    try {
      return fmtTanggalWaktu.format(date);
    } catch(e){
      return '-';
    }
  }

  // ====== UPDATE LABEL TERAKHIR AKTIF SECARA LIVE ======
  function updateLastSeenLabel(){
    if (!lastSeenEl){
      return;
    }
    if (!lastSeenDate){
      lastSeenEl.textContent = '-';
      return;
    }

    var now    = new Date();
    var diffMs = now - lastSeenDate;
    var diffSec = Math.max(0, Math.floor(diffMs / 1000));

    var rel  = fmtRelative(diffSec);
    var indo = formatTanggalIndo(lastSeenDate);

    // contoh: "5 detik lalu — Kamis, 27 November 2025 22.31.05"
    lastSeenEl.textContent = rel + ' — ' + indo;
  }

  // ====== UPDATE LABEL NYALA MONITOR (FIRST & SESSION) SECARA LIVE ======
  function updateSessionLabels(){
    // Pertama kali terdeteksi
    if (firstSeenEl){
      if (firstSeenDate){
        firstSeenEl.textContent = formatTanggalIndo(firstSeenDate);
      } else {
        firstSeenEl.textContent = '-';
      }
    }

    // Sesi saat ini + durasi
    if (sessionStartEl && sessionDurationEl){
      if (sessionStartDate){
        sessionStartEl.textContent = formatTanggalIndo(sessionStartDate);

        var now    = new Date();
        var diffMs = now - sessionStartDate;
        var diffSec = Math.max(0, Math.floor(diffMs / 1000));

        sessionDurationEl.textContent = fmtDuration(diffSec);

        if (sessionBadgeEl){
          sessionBadgeEl.textContent = 'Sesi aktif';
        }
      } else {
        sessionStartEl.textContent    = '-';
        sessionDurationEl.textContent = '-';
        if (sessionBadgeEl){
          sessionBadgeEl.textContent = 'Tidak aktif';
        }
      }
    }
  }

  // ====== SATU TIMER UNTUK SEMUA LABEL LIVE ======
  function tickLive(){
    updateLastSeenLabel();
    updateSessionLabels();
  }

  // ====== AMBIL DATA DARI SERVER ======
  function refreshWidget(){
    fetch(ENDPOINT, { cache:'no-store', credentials:'same-origin' })
      .then(function(r){
        if (!r.ok) throw new Error('HTTP '+r.status);
        return r.json();
      })
      .then(function(j){
        if (!j || !j.ok) throw new Error('Respon tidak valid');

        if (j.nama && titleEl) {
          titleEl.textContent = j.nama;
        }

        var isOnline = !!j.is_online;

        dot.classList.remove('online','offline');
        if (isOnline){
          dot.classList.add('online');
          badge.classList.remove('badge-offline');
          badge.classList.add('badge-online');
          badge.textContent = 'Online';
          subtitle.textContent = 'Status halaman Live Billiard di TV (berdasarkan ping monitor).';
        } else {
          dot.classList.add('offline');
          badge.classList.remove('badge-online');
          badge.classList.add('badge-offline');
          badge.textContent = 'Offline';
          subtitle.textContent = j.has_data
            ? 'Tidak ada ping terbaru (TV mungkin mati atau browser tertutup).'
            : 'Belum pernah ada ping dari monitor.';
        }

        // IP
        if (ipEl){
          ipEl.textContent = j.last_ip ? j.last_ip : '-';
        }

        // Lokasi
        // if (locEl){
        //   if (j.ip_location) {
        //     locEl.textContent = 'Lokasi: ' + j.ip_location;
        //   } else {
        //     locEl.textContent = 'Lokasi: tidak diketahui';
        //   }
        // }

        // Browser + OS
        if (browserEl){
          var browserStr = '-';
          if (j.ua_browser || j.ua_platform){
            browserStr = (j.ua_browser || 'Tidak diketahui')
                       + (j.ua_platform ? (' di ' + j.ua_platform) : '');
          } else if (j.ua_raw){
            browserStr = j.ua_raw;
          }
          browserEl.textContent = browserStr;
        }

        // ---- SIMPAN WAKTU TERAKHIR AKTIF ----
        if (j.last_seen){
          var isoLast = j.last_seen.replace(' ', 'T');
          var dLast   = new Date(isoLast);
          lastSeenDate = isNaN(dLast.getTime()) ? null : dLast;
        } else {
          lastSeenDate = null;
        }

        // ---- FIRST SEEN ----
        if (j.first_seen){
          var isoFirst = j.first_seen.replace(' ', 'T');
          var dFirst   = new Date(isoFirst);
          firstSeenDate = isNaN(dFirst.getTime()) ? null : dFirst;
        } else {
          firstSeenDate = null;
        }

        // ---- SESSION START ----
        if (j.session_start){
          var isoSess = j.session_start.replace(' ', 'T');
          var dSess   = new Date(isoSess);
          sessionStartDate = isNaN(dSess.getTime()) ? null : dSess;
        } else {
          sessionStartDate = null;
        }

        // update sekali sekarang
        tickLive();

        // kalau interval belum jalan, start 1 detik sekali
        if (!liveTimer){
          liveTimer = setInterval(tickLive, 1000);
        }
      })
      .catch(function(){
        dot.classList.remove('online');
        dot.classList.add('offline');
        badge.classList.remove('badge-online');
        badge.classList.add('badge-offline');
        badge.textContent = 'Gangguan';
        if (subtitle)   subtitle.textContent   = 'Gagal memuat status monitor.';
        if (ipEl)       ipEl.textContent       = '-';
        // if (locEl)      locEl.textContent      = 'Lokasi: -';
        if (browserEl)  browserEl.textContent  = '-';
        if (lastSeenEl) lastSeenEl.textContent = '-';

        if (firstSeenEl)       firstSeenEl.textContent       = '-';
        if (sessionStartEl)    sessionStartEl.textContent    = '-';
        if (sessionDurationEl) sessionDurationEl.textContent = '-';
        if (sessionBadgeEl)    sessionBadgeEl.textContent    = 'Tidak aktif';

        lastSeenDate     = null;
        firstSeenDate    = null;
        sessionStartDate = null;
      });
  }

  // pertama kali
  refreshWidget();
  // polling data baru tiap 30 detik
  setInterval(refreshWidget, 30000);
})();
</script>

