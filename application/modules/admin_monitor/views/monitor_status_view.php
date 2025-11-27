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

  <!-- KIRI ATAS: STATUS MONITOR -->
  <div class="col-md-6 mb-3">
    <div class="card tv-card tv-card--status" id="widgetTvStatus">
      <div class="card-body">
        <div class="text-muted text-uppercase mb-1">Status Monitor</div>
        <div class="d-flex align-items-center">
          <div class="tv-dot-wrapper mr-3">
            <span class="tv-dot offline" id="tvDot"></span>
          </div>
          <div>
            <h4 class="mb-1" id="tvTitle">TV Billiard</h4>
            <span id="tvBadge" class="badge badge-pill badge-offline">Offline</span>
          </div>
        </div>
        <div class="small text-muted mt-2" id="tvSubtitle">
          Memeriksa status…
        </div>
      </div>
    </div>
  </div>

  <!-- KANAN ATAS: IP MONITOR -->
  <div class="col-md-6 mb-3">
    <div class="card tv-card tv-card--ip" id="widgetTvIp">
      <div class="card-body">
        <div class="text-muted text-uppercase mb-1">IP Monitor (TV)</div>
        <div id="tvIp" class="font-weight-medium">-</div>
        <div id="tvLocation" class="small text-muted mt-1">Lokasi: -</div>
      </div>
    </div>
  </div>

  <!-- KIRI BAWAH: BROWSER -->
  <div class="col-md-6 mb-3">
    <div class="card tv-card tv-card--browser" id="widgetTvBrowser">
      <div class="card-body">
        <div class="text-muted text-uppercase mb-1">Browser</div>
        <div id="tvBrowser" class="font-weight-medium">-</div>
        <div class="small text-muted mt-1">
          Diambil dari user agent monitor (TV).
        </div>
      </div>
    </div>
  </div>

  <!-- KANAN BAWAH: TERAKHIR AKTIF -->
  <div class="col-md-6 mb-3">
    <div class="card tv-card tv-card--last" id="widgetTvLast">
      <div class="card-body">
        <div class="text-muted text-uppercase mb-1">Terakhir Aktif</div>
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

  var dot       = document.getElementById('tvDot');
  var badge     = document.getElementById('tvBadge');
  var subtitle  = document.getElementById('tvSubtitle');
  var titleEl   = document.getElementById('tvTitle');
  var ipEl      = document.getElementById('tvIp');
  var locEl     = document.getElementById('tvLocation');
  var browserEl = document.getElementById('tvBrowser');
  var lastSeenEl= document.getElementById('tvLastSeen');

  if (!dot || !badge) return;

  // ====== STATE UNTUK LIVE TIMER ======
  var lastSeenDate = null;
  var liveTimer    = null;

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
        if (locEl){
          if (j.ip_location) {
            locEl.textContent = 'Lokasi: ' + j.ip_location;
          } else {
            locEl.textContent = 'Lokasi: tidak diketahui';
          }
        }

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

        // Simpan lastSeenDate untuk live timer
        if (j.last_seen){
          // asumsikan format "YYYY-MM-DD HH:MM:SS"
          var iso = j.last_seen.replace(' ', 'T');
          var d   = new Date(iso);
          if (!isNaN(d.getTime())){
            lastSeenDate = d;
          } else {
            lastSeenDate = null;
          }
        } else {
          lastSeenDate = null;
        }

        // update sekali sekarang
        updateLastSeenLabel();

        // kalau interval belum jalan, start 1 detik sekali
        if (!liveTimer){
          liveTimer = setInterval(updateLastSeenLabel, 1000);
        }
      })
      .catch(function(){
        dot.classList.remove('online');
        dot.classList.add('offline');
        badge.classList.remove('badge-online');
        badge.classList.add('badge-offline');
        badge.textContent = 'Gangguan';
        if (subtitle) subtitle.textContent = 'Gagal memuat status monitor.';
        if (ipEl)      ipEl.textContent  = '-';
        if (locEl)     locEl.textContent = 'Lokasi: -';
        if (browserEl) browserEl.textContent = '-';
        if (lastSeenEl)lastSeenEl.textContent = '-';
        lastSeenDate = null;
      });
  }

  // pertama kali
  refreshWidget();
  // polling data baru tiap 30 detik
  setInterval(refreshWidget, 30000);
})();
</script>

