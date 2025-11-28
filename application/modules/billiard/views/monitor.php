<!DOCTYPE html>
<html lang="id">
<head>
  <!-- ========== META DASAR ========== -->
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, viewport-fit=cover">

 <meta name="robots" content="noindex, nofollow, noarchive">
  <meta name="googlebot" content="noindex, nofollow, nosnippet">

  <meta name="google" content="notranslate">
  <meta name="author" content="Onhacker.net">

  <title><?= ucfirst(strtolower($rec->nama_website)).' - '.$title; ?></title>
  <meta name="google-site-verification" content="yoI3KrMVtbFyU9SfHWnE2d57nrTE3pS-Uu_Edrt6v7E" />
  <!-- ========== THEME COLOR (LIGHT/DARK) ========== -->
  <meta name="theme-color" media="(prefers-color-scheme: light)" content="#0F172A">
  <meta name="theme-color" media="(prefers-color-scheme: dark)"  content="#000000">

  <!-- ========== SEO / OPEN GRAPH / TWITTER ========== -->
  <meta name="description" content="<?= htmlspecialchars($deskripsi, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="keywords" content="<?= htmlspecialchars($rec->meta_keyword, ENT_QUOTES, 'UTF-8') ?>">

  <meta property="og:title" content="<?= htmlspecialchars($rec->nama_website.' - '.$title, ENT_QUOTES, 'UTF-8') ?>" />
  <meta property="og:description" content="<?= htmlspecialchars($deskripsi, ENT_QUOTES, 'UTF-8') ?>" />
  <meta property="og:image" content="<?= $prev ?>" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="630" />
  <meta property="og:url" content="<?= current_url() ?>" />
  <meta property="og:type" content="website" />
  <meta name="twitter:card" content="summary_large_image" />

  <?php $canon = preg_replace('#^http:#','https:', current_url()); ?>
  <link rel="canonical" href="<?= htmlspecialchars($canon, ENT_QUOTES, 'UTF-8') ?>">

  <!-- ========== PWA / ICONS ========== -->
  <link rel="manifest" href="<?= site_url('developer/manifest') ?>?v=1">
  <link rel="icon" href="<?= base_url('assets/images/favicon.ico') ?>" type="image/x-icon" />
  <link rel="shortcut icon" href="<?= base_url('assets/images/favicon.ico') ?>" type="image/x-icon" />

  <!-- ========== JSON-LD ORGANIZATION ========== -->
  <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "<?= addslashes($rec->nama_website) ?>",
      "url": "<?= site_url() ?>",
      "logo": "<?= base_url('assets/images/logo.png'); ?>"
    }
  </script>

  <!-- ========== CSS VENDOR ========== -->
  <link href="<?= base_url('assets/admin/css/bootstrap.min.css'); ?>" rel="stylesheet" />
  <link href="<?= base_url('assets/min/monitor.min.css'); ?>" rel="stylesheet" />

  <!-- ========== CSS KUSTOM UTAMA ========== -->

<style type="text/css">
  /* PAKSA JAM DI BAWAH KANAN */
body .live-clock{
  position: fixed;
  right: 16px;
  top: auto !important;
  bottom: calc(var(--ticker-h) + var(--safe-bottom) + env(safe-area-inset-bottom, 0px)) !important;
}

</style>
</head>

<body class="menubar-gradient gradient-topbar topbar-dark compact">

  <!-- Jam live (dipin ke kanan bawah di atas ticker) -->
  <div class="live-clock" id="liveClock" role="timer" aria-live="polite" aria-label="Jam lokal WITA">
    <div class="lc-badge">Waktu Siwa</div>
    <div class="lc-time" id="lcTime">00:00:00</div>
    <div class="lc-date" id="lcDate">Senin, 01 Januari 1970</div>
  </div>

  <div class="rotate-guard" id="rotateGuard" role="dialog" aria-live="polite" aria-label="Mohon putar perangkat ke mode landscape">
    <div>
      <span class="ico" aria-hidden="true">🔁</span>
      <h3>Putar ke Mode Landscape</h3>
      <p>Layar ini didesain untuk posisi mendatar agar teks besar & rapi di TV.</p>
    </div>
  </div>

  <!-- WRAPPER HALAMAN -->
  <div class="wrapper curved" style="--curve-h: 330px;" id="app-scroll">
    <div class="container-fluid mt-2" aria-live="polite">
      <div class="hero-title" role="banner" aria-label="Judul situs">
        <span id="liveDot" style="width:14px;height:14px;border-radius:50%;display:inline-block;background:#aaa;margin-right:20px;"></span>
        <h1 class="text" id="liveText">Menghubungkan…</h1>
        <span class="accent" aria-hidden="true"></span>
      </div>

      <!-- ===== FIXED TV TICKER di bawah layar ===== -->
      <div id="fixedTicker" class="notice-ticker notice-ticker--fixed" role="region" aria-label="Pengumuman berjalan">
        <span class="sr-only">
          Pengumuman: Harap bermain sportif, santai, dan saling menghargai. Jika belum menang, jangan baper—tetap seru dan happy bareng; tepati waktu mulai dan selesai bermain; jangan lupa berdoa sebelum bermain; arahkan stik ke bola, bukan ke teman.
        </span>
        <div class="ticker-track" aria-hidden="true">
          <span class="t-item">🎱 Harap bermain sportif, santai, dan saling menghargai. Jika belum menang, jangan baper—yang penting tetap seru dan happy bareng!</span>
          <span class="t-sep">•</span>
          <span class="t-item">🕒 Tepati waktu mulai & selesai bermain.</span>
          <span class="t-sep">•</span>
          <span class="t-item">🙏 Jangan lupa berdoa sebelum bermain.</span>
          <span class="t-sep">•</span>
          <span class="t-item">🎱 Arahkan stik ke bola—bukan ke teman ya 😉</span>
        </div>
      </div>

      <!-- KONTEN LIST BOOKING -->
      <div class="row" id="cardsRow">
        <div class="col-12">
          <div class="card-box"><p class="mb-0">Memuat data…</p></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Jam live WITA -->
  <!-- Jam live WITA + identitas monitor -->
<script>
  // ===== JAM LIVE WITA =====
  (function(){
    var tz = 'Asia/Makassar';
    var timeEl = document.getElementById('lcTime');
    var dateEl = document.getElementById('lcDate');
    if(!timeEl || !dateEl) return;

    var fmtTime = new Intl.DateTimeFormat('id-ID', {
      hour:'2-digit', minute:'2-digit', second:'2-digit',
      hour12:false, timeZone:tz
    });
    var fmtDate = new Intl.DateTimeFormat('id-ID', {
      weekday:'long', day:'2-digit', month:'long', year:'numeric',
      timeZone:tz
    });

    function updateClock(){
      var now = new Date();
      timeEl.textContent = fmtTime.format(now);
      dateEl.textContent = fmtDate.format(now);
    }
    updateClock();
    setInterval(updateClock, 1000);
  })();

  // ===== IDENTITAS MONITOR (TV) + HELPER buildUrl =====
  (function(){
    let MONITOR_ID = localStorage.getItem('ausi_monitor_id');
    if (!MONITOR_ID){
      MONITOR_ID = 'mon-' + Math.random().toString(36).slice(2) + '-' + Date.now();
      localStorage.setItem('ausi_monitor_id', MONITOR_ID);
    }

    function buildUrl(url){
      try {
        const u = new URL(url, window.location.origin);
        u.searchParams.set('monitor_id', MONITOR_ID);
        return u.toString();
      } catch(e){
        return url + (url.indexOf('?') === -1 ? '?' : '&')
             + 'monitor_id=' + encodeURIComponent(MONITOR_ID);
      }
    }

    // EXPOSE ke global, supaya script lain bisa pakai
    window.MONITOR_ID = MONITOR_ID;
    window.buildUrl   = buildUrl;
  })();
</script>


  <!-- SET CSS --clock-h sesuai tinggi jam -->
  <script>
  (function(){
    const el = document.getElementById('liveClock');
    if(!el) return;
    let t;
    function applyClockHeight(){
      const h = el.offsetHeight || 80;
      document.documentElement.style.setProperty('--clock-h', h + 'px');
    }
    applyClockHeight();
    window.addEventListener('resize', ()=>{ clearTimeout(t); t = setTimeout(applyClockHeight, 120); }, {passive:true});
    if ('ResizeObserver' in window){
      const ro = new ResizeObserver(applyClockHeight);
      ro.observe(el);
    }
  })();
  </script>

  <!-- Monitor loop + renderer -->
  <script>
  (function(){
    // ================== ENDPOINTS ==================
    var EP_DATA      = '<?= site_url("billiard/monitor_data"); ?>';
    var PING_BIL_URL = '<?= site_url("billiard/monitor_ping"); ?>';

    // ================== UI refs ==================
    var rowEl    = document.getElementById('cardsRow');
    var liveDot  = document.getElementById('liveDot');
    var liveText = document.getElementById('liveText');

    function setLive(status){
      if(!liveDot || !liveText) return;
      liveDot.classList.add('radar');
      liveDot.classList.remove('is-ok','is-idle','is-err','is-conn');

      if(status==='ok'){
        liveText.textContent = 'Live Billiard Ausi';
        liveDot.style.setProperty('--dot-color', '#10b981');
        liveDot.style.color = '#10b981';
        liveDot.classList.add('is-ok');
      } else if(status==='idle'){
        liveText.textContent = 'Menunggu perubahan…';
        liveDot.style.setProperty('--dot-color', '#6b7280');
        liveDot.style.color = '#6b7280';
        liveDot.classList.add('is-idle');
      } else if(status==='err'){
        liveText.textContent = 'Gangguan koneksi';
        liveDot.style.setProperty('--dot-color', '#ef4444');
        liveDot.style.color = '#ef4444';
        liveDot.classList.add('is-err');
      } else {
        liveText.textContent = 'Menghubungkan…';
        liveDot.style.setProperty('--dot-color', '#a3a3a3');
        liveDot.style.color = '#a3a3a3';
        liveDot.classList.add('is-idle');
      }
    }

    function esc(s){ return (s==null?'':String(s)).replace(/[&<>"']/g,function(c){return({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'})[c]}); }
    function truncateNama(s, max){
      s = (s == null ? '' : String(s));
      if (s.length <= max) return s;
  // kalau mau 10 karakter total termasuk titik-titik, pakai max-1
  return s.slice(0, max-1) + '…';
}
    function renderCards(cards){
      cards = (cards || []).slice().sort((a, b) => (b.meja_id - a.meja_id));

      function countBookings(list){
        const now = Date.now();
        let n = 0;
        (list||[]).forEach(c=>{
          (c.days||[]).forEach(d=>{
            (d.bookings||[]).forEach(b=>{
              const end = Number(b.end_ts||0);
              if (!end || end > now) n++;
            });
          });
        });
        return n;
      }

      var trulyEmpty = !Array.isArray(cards) || cards.length===0 || countBookings(cards)===0;

      if (trulyEmpty){
        var html = ''
        + '<div class="col-12">'
        + '  <div class="card-box empty-wrap">'
        + '    <div class="empty-grid">'
        + '      <div class="empty-left" role="status" aria-live="polite">'
        + '        <div class="empty-hero">'
        + '          <span class="emoji-8ball" aria-hidden="true">🎱</span>'
        + '          <h3 class="empty-title">Belum ada bookingan billiard mendatang</h3>'
        + '        </div>'
        + '        <div class="empty-sub">Jadwal akan muncul otomatis saat ada booking baru.</div>'
        + '        <div class="empty-divider" aria-hidden="true"></div>'
        + '      </div>'
        + '      <div class="empty-right">'
        + '        <div class="empty-video" aria-label="Video pemutar 9-ball (diputar berulang)">'
        + '          <div class="embed-16x9">'
        + '            <iframe'
        + '              id="ytLoop"'
        + '              src="https://www.youtube.com/embed/4_nZL5pDl5U?autoplay=1&mute=1&loop=1&playlist=4_nZL5pDl5U&rel=0&modestbranding=1&playsinline=1&origin=<?= site_url() ?>"'
        + '              title="EFREN REYES vs MICHAEL DEITCHMAN - 2022 Derby City Classic 9-Ball Division"'
        + '              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"'
        + '              referrerpolicy="strict-origin-when-cross-origin"'
        + '              allowfullscreen></iframe>'
        + '          </div>'
        + '        </div>'
        + '      </div>'
        + '    </div>'
        + '  </div>'
        + '</div>';

        rowEl.innerHTML = html;
        refreshCounters();
        setLive('idle');
        return;
      }

      var html = '';
      cards.forEach(function(c){
        var count = parseInt(c.booking_count||0,10);
        html += '<div class="col-xl-6 col-lg-12">';
        html +=   '<div class="card-box mt-3" style="position:relative; padding-top:46px; padding-bottom:64px;">';
        html +=     '<div class="meja-ribbon"><span>'+esc(c.nama_meja)+'</span></div>';
        html +=     '<div class="book-count">'+count+' booking</div>';

        (c.days||[]).forEach(function(d){
          html += '<div class="day-row">';
          html +=   '<div class="cal-ava" title="'+esc(d.tanggal_fmt)+'">';
          html +=     '<div class="cal-tile" aria-label="'+esc(d.tanggal_fmt)+'">';
          html +=       '<div class="cal-head">'+esc(d.mon)+'</div>';
          html +=       '<div class="cal-day">'+esc(d.daynum)+'</div>';
          html +=     '</div>';
          html +=     '<div class="cal-cap mt-1">'+esc(String(d.weekday||'').toUpperCase())+'</div>';
          html +=   '</div>';

          html +=   '<div class="day-content">';
          if(d.bookings && d.bookings.length){
            html +=   '<ul class="conversation-list" style="height:auto;max-height:none;overflow:visible;width:auto;">';
            d.bookings.forEach(function(b){
              html += '<li class="booking-item" data-start-ts="'+(b.start_ts||0)+'" data-end-ts="'+(b.end_ts||0)+'">';
              html +=   '<div class="conversation-text"><div class="ctext-wrap">';
              html +=     '<div class="ct-head">';
              html +=       '<div class="ct-left">'+esc(b.jam_mulai)+' – '+esc(b.jam_selesai)+' <span class="tz">WITA</span>  · '+parseInt(b.durasi_jam||0,10)+' jam </div>';
              html +=       '<div class="ct-right">';
              html +=         '<span class="status-pill"><span class="status-label">Menunggu</span></span>';
              html +=       '</div>';
              html +=     '</div>';
              html +=     '<div class="ct-row-bottom">';
              var namaFull  = b.nama || 'Booking';
var namaShort = truncateNama(namaFull, 10); // max 10 karakter

html +=       '<div class="ct-meta" title="'+esc(namaFull)+'">'
            +   esc(namaShort)
            +   (b.hp_masked ? (' · '+esc(b.hp_masked)) : '')
            + '</div>';

              html +=       '<div class="ct-time-right">';
              html +=         '<div class="time-pill" aria-live="polite">';
              html +=           '<span class="cd-caption">Menunggu</span>';
              html +=           '<div class="cd cd--mini" data-countdown>';

              // JAM
              html +=             '<div class="cd__pair">';
              html +=               '<div class="cd__digits">';
              html +=                 '<div class="cd__digit" data-col><div data-pos="next">-</div><div data-pos="prev">-</div></div>';
              html +=                 '<div class="cd__digit" data-col><div data-pos="next">-</div><div data-pos="prev">-</div></div>';
              html +=               '</div>';
              html +=               '<span class="cd__unit-inline">Jam</span>';
              html +=             '</div>';

              // MENIT
              html +=             '<div class="cd__pair">';
              html +=               '<div class="cd__digits">';
              html +=                 '<div class="cd__digit" data-col><div data-pos="next">-</div><div data-pos="prev">-</div></div>';
              html +=                 '<div class="cd__digit" data-col><div data-pos="next">-</div><div data-pos="prev">-</div></div>';
              html +=               '</div>';
              html +=               '<span class="cd__unit-inline">Menit</span>';
              html +=             '</div>';

              // DETIK
              html +=             '<div class="cd__pair">';
              html +=               '<div class="cd__digits">';
              html +=                 '<div class="cd__digit" data-col><div data-pos="next">-</div><div data-pos="prev">-</div></div>';
              html +=                 '<div class="cd__digit" data-col><div data-pos="next">-</div><div data-pos="prev">-</div></div>';
              html +=               '</div>';
              html +=               '<span class="cd__unit-inline">Detik</span>';
              html +=             '</div>';

              html +=           '</div>';   // .cd
              html +=         '</div>';     // .time-pill
              html +=       '</div>';       // .ct-time-right
              html +=     '</div>';         // .ct-row-bottom
              html +=   '</div></div>';
              html += '</li>';
            });
            html +=   '</ul>';
          } else {
            html +=   '<div class="text-muted small">Belum ada booking pada tanggal ini.</div>';
          }
          html +=   '</div>';
          html += '</div>';
          html += '<hr style="margin:10px 0">';
        });

        html +=   '</div>';
        html += '</div>';
      });
      rowEl.innerHTML = html;
      refreshCounters();
    }

    // ================== Countdown per-booking ==================
    (function(){
      function fmtWords(ms){
        if (ms <= 0) return '0 dtk';
        var s = Math.floor(ms / 1000);
        var d = Math.floor(s / 86400); s %= 86400;
        var h = Math.floor(s / 3600);  s %= 3600;
        var m = Math.floor(s / 60);    var sec = s % 60;

        var parts = [];
        if (d > 0)   parts.push(d + ' hari');
        if (h > 0)   parts.push(h + ' jam');
        if (m > 0)   parts.push(m + ' menit');
        if (sec > 0 || parts.length === 0) parts.push(sec + ' dtk');

        return parts.slice(0, 3).join(' ');
      }

      function splitHMS(ms){
        if (ms <= 0) return {h:0,m:0,s:0};
        var total = Math.floor(ms / 1000);
        var h = Math.floor(total / 3600);
        var m = Math.floor((total % 3600) / 60);
        var s = total % 60;
        return {h:h, m:m, s:s};
      }

      function updateCdElement(root, units, animate){
        if (!root) return;

        if (!Array.isArray(root._time)){
          root._time = ['-','-','-','-','-','-']; // 3 unit x 2 digit
        }

        var digits = [];
        ['h','m','s'].forEach(function(k){
          var v = units[k] || 0;
          var str = String(v);
          if (str.length < 2) str = '0' + str;
          digits.push(str.charAt(0), str.charAt(1));
        });

        var cols = root.querySelectorAll('[data-col]');
        cols.forEach(function(c, idx){
          var nextDigit = digits[idx] || '0';
          var prevDigit = root._time[idx] || '-';
          if (nextDigit !== prevDigit){
            var nextEl = c.querySelector('[data-pos="next"]');
            var prevEl = c.querySelector('[data-pos="prev"]');
            if (animate){
              c.classList.add('cd__digit--roll-in');
              if (nextEl) nextEl.classList.add('cd__next-digit-fade');
              if (prevEl) prevEl.classList.add('cd__prev-digit-fade');
            }
            if (nextEl) nextEl.textContent = nextDigit;
            if (prevEl) prevEl.textContent = prevDigit;
          }
        });

        root._time = digits;

        // === Sembunyikan JAM / MENIT saat 0 ===
        var pairs = root.querySelectorAll('.cd__pair');
        if (pairs.length === 3){
          var showHour = units.h > 0;
          var showMin  = (units.h > 0) || (units.m > 0); // kalau jam 0 tapi menit masih ada, tetap tampil menit
          pairs[0].style.display = showHour ? 'flex' : 'none'; // JAM
          pairs[1].style.display = showMin  ? 'flex' : 'none'; // MENIT
          pairs[2].style.display = 'flex';                     // DETIK selalu tampil
        }

        if (root._animTimeout) clearTimeout(root._animTimeout);
        root._animTimeout = setTimeout(function(){
          var cols = root.querySelectorAll('[data-col]');
          cols.forEach(function(c){
            c.classList.remove('cd__digit--roll-in');
          });
          var posEls = root.querySelectorAll('[data-pos]');
          posEls.forEach(function(p){
            p.classList.remove('cd__next-digit-fade','cd__prev-digit-fade');
          });
        }, 500);
      }

      const FIVE_MIN_MS = 5*60*1000;
      const ONE_MIN_MS  = 60*1000;

      function runCountdown(){
        var now = Date.now();
        document.querySelectorAll('.booking-item').forEach(function(item){
          var start = parseInt(item.getAttribute('data-start-ts'),10)||0;
          var end   = parseInt(item.getAttribute('data-end-ts'),10)||0;
          var pill  = item.querySelector('.status-pill');
          var label = item.querySelector('.status-label');
          var cdRoot= item.querySelector('.time-pill .cd[data-countdown]');
          var caption = item.querySelector('.time-pill .cd-caption');
          var timePill = item.querySelector('.time-pill');
          if(!pill||!label||!cdRoot||!caption||!timePill) return;

          pill.classList.remove('success','muted');
          item.classList.remove('soon','critical');

          /* reset mode warna time-pill */
          timePill.classList.remove('mode-lagi','mode-sisa','mode-selesai');


          if (start && now < start){
            // ===== Belum mulai (MENUNGGU) =====
            var ms = start - now;
            label.textContent = 'Menunggu ⌛';
            caption.textContent = 'Lagi';
            timePill.setAttribute('title','Lagi ' + fmtWords(ms));
            timePill.classList.add('mode-lagi');   // << warna khusus "Lagi"
            updateCdElement(cdRoot, splitHMS(ms), true);
          }

         else if (end && now <= end){
            // ===== Sedang bermain (SISA) =====
            var msLeft = end - now;
            label.textContent = 'Sedang bermain 🎱';
            caption.textContent = 'Sisa';
            timePill.setAttribute('title','Sisa ' + fmtWords(msLeft) + ' lagi');
            pill.classList.add('success');
            timePill.classList.add('mode-sisa');   // << warna khusus "Sisa"
            updateCdElement(cdRoot, splitHMS(msLeft), true);

            if (msLeft > 0 && msLeft <= FIVE_MIN_MS){
              item.classList.add('soon');
              if (msLeft <= ONE_MIN_MS) item.classList.add('critical');
            
              if (!item.dataset.beep5){
                try{ playSound(); }catch(e){}
                item.dataset.beep5 = '1';
              }
            } else {
              delete item.dataset.beep5;
            }
          }
          else{
            // ===== Selesai =====
            label.textContent = 'Selesai';
            caption.textContent = 'Selesai';
            timePill.setAttribute('title','Sesi selesai');
            pill.classList.add('muted');
            timePill.classList.add('mode-selesai');   // abu-abu
            delete item.dataset.beep5;
            updateCdElement(cdRoot, {h:0,m:0,s:0}, true);

            setTimeout(function(){
                          if (!item.dataset.removing){
                item.dataset.removing = '1';
                item.classList.add('vanish');
                setTimeout(function(){ item.remove(); refreshCounters(); }, 300);
              }
            }, 15000);
          }
        });
        refreshCounters();
      }

      setInterval(runCountdown, 1000);
    })();

    // ================== Doorbell beep ==================
    (function(){
      const AC = window.AudioContext || window.webkitAudioContext;
      let ctx = null, lastWall = 0;
      function ensureCtx(){ if(!ctx) ctx = new AC(); return ctx; }
      function hit(ac, f, t0, dur, vol, type){
        const now = ac.currentTime;
        const o = ac.createOscillator(); const g = ac.createGain();
        o.type = type || 'triangle'; o.frequency.setValueAtTime(f, now + t0);
        g.gain.setValueAtTime(0.0001, now + t0);
        g.gain.exponentialRampToValueAtTime(vol, now + t0 + 0.03);
        g.gain.exponentialRampToValueAtTime(0.0001, now + t0 + dur);
        o.connect(g); g.connect(ac.destination); o.start(now + t0); o.stop(now + t0 + dur + 0.05);
      }
      function _play(){ const ac = ensureCtx(); hit(ac, 880.00, 0.00, 0.22, 0.10, 'triangle'); hit(ac, 587.33, 0.16, 0.30, 0.11, 'sine'); }
      window.playSound = function(){
        const t = Date.now(); if (t - lastWall < 1200) return; lastWall = t;
        const ac = ensureCtx(); if (ac.state !== 'running'){ ac.resume().then(_play).catch(()=>{}); } else { _play(); }
      };
      document.addEventListener('pointerdown', function prime(){ const ac = ensureCtx(); if (ac.state !== 'running') ac.resume();
        const s = ac.createBufferSource(); s.buffer = ac.createBuffer(1, 1, ac.sampleRate); s.connect(ac.destination); s.start(0);
        document.removeEventListener('pointerdown', prime, {capture:false});
      }, { once:true });
    })();

    // ================== Ping loop ==================
    const last = { bil: { total:null, max_id:null, last_ts:null } };
    const BASE_INTERVAL = 10000, HIDDEN_INTERVAL = 20000;
    let errorStreak = 0, ticking = false;

        async function safeFetch(url){
          const r = await fetch(buildUrl(url), {
            cache:'no-store',
            credentials:'same-origin'
          });
          if(!r.ok) throw new Error('HTTP '+r.status);
          return r.json();
        }


    let reloadAborter = null, reloading = false;
    async function reload_billiard_table(reason){
      if (reloading) { try{ reloadAborter && reloadAborter.abort(); }catch(_){ } }
      reloadAborter = new AbortController();
      reloading = true;
      try{
        setLive('ok');
                const r = await fetch(buildUrl(EP_DATA), {
          headers:{'Accept':'application/json'},
          cache:'no-store',
          signal: reloadAborter.signal
        });

        if(!r.ok) throw new Error('HTTP '+r.status);
        const j = await r.json();
        if(!j || !j.ok) throw new Error('Respon tidak valid');
        renderCards(j.cards || []);
      }catch(e){
        if (e.name !== 'AbortError') setLive('err');
      }finally{
        reloading = false;
      }
    }

    function isAdded(oldSnap, snap){
      const tOld = Number(oldSnap.total), tNew = Number(snap.total);
      const idOld = Number(oldSnap.max_id), idNew = Number(snap.max_id);
      const totalUp = Number.isFinite(tOld) && Number.isFinite(tNew) && tNew > tOld;
      const idUp    = Number.isFinite(idOld) && Number.isFinite(idNew) && idNew > idOld;
      return totalUp || idUp;
    }
    function isChanged(oldSnap, snap){
      const tOld = Number(oldSnap.total), tNew = Number(snap.total);
      const idOld = Number(oldSnap.max_id), idNew = Number(snap.max_id);
      if (Number.isFinite(tOld) && Number.isFinite(tNew) && tNew !== tOld) return true;
      if (Number.isFinite(idOld) && Number.isFinite(idNew) && idNew !== idOld) return true;
      if (oldSnap.last_ts && snap.last_ts && String(snap.last_ts) !== String(oldSnap.last_ts)) return true;
      return false;
    }

    async function handlePing(){
      const j = await safeFetch(PING_BIL_URL);
      if (j && j.success){
        const snap = {
          total:  Number(j.total||0),
          max_id: Number(j.max_id||0),
          last_ts: j.last_ts ? String(j.last_ts) : null
        };
        if (last.bil.total === null){
          last.bil = snap;
          await reload_billiard_table('baseline');
        } else {
          const added   = isAdded(last.bil, snap);
          const changed = isChanged(last.bil, snap);
          last.bil = snap;
          if (changed) await reload_billiard_table('changed');
          if (added)   playSound();
        }
      }
    }

    async function loop(){
      if (ticking) return;
      ticking = true;

      const visible  = !document.hidden;
      const baseInt  = visible ? BASE_INTERVAL : HIDDEN_INTERVAL;
      const interval = baseInt * Math.min(4, (1 + errorStreak*0.5));

      try{
        await handlePing();
        errorStreak = 0;
        setLive('ok');
      }catch(_){
        errorStreak = Math.min(6, errorStreak + 1);
        setLive('err');
      }finally{
        ticking = false;
        setTimeout(loop, interval);
      }
    }

    loop();
    document.addEventListener('visibilitychange', function(){
      if (!document.hidden) setTimeout(loop, 200);
    });

    (function(){
      if (liveDot && !liveDot.querySelector('.sweep')){
        var s = document.createElement('i'); s.className = 'sweep'; liveDot.appendChild(s);
      }
    })();
  })();
  </script>

  <!-- Fullscreen toggle + WakeLock -->
  <script>
  (function(){
    const LS_KEY = 'ausi_fs_auto';
    const target = document.documentElement;

    function isFs(){ return !!(document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement); }
    function setBtnActive(on){ if (!btn) return; btn.classList.toggle('active', !!on); }

    async function enterFs(){
      try{
        if (target.requestFullscreen)      { await target.requestFullscreen({ navigationUI: 'hide' }); }
        else if (target.webkitRequestFullscreen){ target.webkitRequestFullscreen(); }
        else if (target.msRequestFullscreen){ target.msRequestFullscreen(); }
        localStorage.setItem(LS_KEY, '1');
        hideCta();
        setBtnActive(true);
      } catch(e){}
    }
    async function exitFs(){
      try{
        if (document.exitFullscreen) { await document.exitFullscreen(); }
        else if (document.webkitExitFullscreen) { document.webkitExitFullscreen(); }
        else if (document.msExitFullscreen) { document.msExitFullscreen(); }
        setBtnActive(false);
      } catch(e){}
    }

    let cta = null;
    function showCta(){
      if (cta || isFs()) return;
      cta = document.createElement('div');
      cta.className = 'fs-cta';
      cta.innerHTML = `
        <div class="fs-cta__inner">
          <div class="emoji">🖥️</div>
          <h3>Layar Penuh</h3>
          <p>Tap/klik di mana saja untuk masuk mode layar penuh.</p>
          <button class="btn-cta" type="button">Aktifkan</button>
          <div class="hint">Esc untuk keluar · tekan <b>F</b> untuk toggle</div>
        </div>`;
      document.body.appendChild(cta);
      cta.addEventListener('click', enterFs);
      cta.querySelector('.btn-cta').addEventListener('click', function(ev){ ev.stopPropagation(); enterFs(); });
    }
    function hideCta(){ if(!cta) return; cta.remove(); cta=null; }

    let btn = null;
    function buildBtn(){
      if (btn) return;
      btn = document.createElement('button');
      btn.className = 'fs-btn'; btn.type = 'button'; btn.title = 'Toggle layar penuh (F)';
      btn.innerHTML = '<span class="icon-enter" aria-hidden="true">⛶</span><span class="icon-exit" aria-hidden="true">⤫</span>';
      document.body.appendChild(btn);
      btn.addEventListener('click', ()=> isFs() ? exitFs() : enterFs());
    }

    document.addEventListener('keydown', function(ev){
      if ((ev.key||'').toLowerCase() === 'f'){ ev.preventDefault(); isFs() ? exitFs() : enterFs(); }
    });
    ['fullscreenchange','webkitfullscreenchange','msfullscreenchange'].forEach(evt=>{
      document.addEventListener(evt, ()=> setBtnActive(isFs()));
    });

    function init(){
      buildBtn();
      const consent = localStorage.getItem(LS_KEY) === '1';
      if (consent) enterFs(); else showCta();
      const once = ()=>{ enterFs(); window.removeEventListener('pointerdown', once, true); };
      window.addEventListener('pointerdown', once, { once:true, capture:true });
    }

    if (document.readyState !== 'loading') init();
    else document.addEventListener('DOMContentLoaded', init);

    let wakeLock = null;
    async function requestWakeLock(){
      try{
        if ('wakeLock' in navigator){
          wakeLock = await navigator.wakeLock.request('screen');
          wakeLock.addEventListener('release', ()=>{});
        }
      }catch(e){}
    }
    document.addEventListener('visibilitychange', ()=>{ if (!document.hidden) requestWakeLock(); });
    requestWakeLock();
  })();
  </script>

  <!-- Ticker rebuild + flag has-fixed-ticker -->
  <script>
  (function(){
    const el = document.getElementById('fixedTicker');
    if(!el) return;
    const track = el.querySelector('.ticker-track');
    if(!track) return;

    const originalHTML = track.innerHTML;

    function rebuild(){
      const vw = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
      track.innerHTML = originalHTML;
      while (track.scrollWidth < vw * 2.2){
        track.innerHTML += originalHTML;
      }
      const distance = track.scrollWidth + vw;
      const pps = parseFloat(getComputedStyle(el).getPropertyValue('--ticker-speed-pps')) || 95;
      el.style.setProperty('--ticker-duration', (distance/pps) + 's');
      el.style.setProperty('--ticker-translate', (-distance) + 'px');
    }

    document.body.classList.add('has-fixed-ticker');

    rebuild();
    let t;
    window.addEventListener('resize', ()=>{ clearTimeout(t); t = setTimeout(rebuild, 120); }, { passive:true });
  })();
  </script>

  <!-- Auto reload berkala -->
  <script>
  (function(){
    const HOUR  = 60 * 60 * 1000;
    const CHECK = 60 * 1000;
    function reloadOrWait(){
      if (!document.hidden) { location.reload(); return; }
      setTimeout(reloadOrWait, CHECK);
    }
    setTimeout(reloadOrWait, HOUR);
  })();
  </script>

  <!-- Hitung tinggi hero untuk batas video + refreshCounters -->
  <script>
  (function(){
    const hero = document.querySelector('.hero-title');
    if(!hero) return;
    function applyHeroH(){
      const h = hero.offsetHeight || 120;
      document.documentElement.style.setProperty('--hero-h', h + 'px');
    }
    applyHeroH();
    let t;
    window.addEventListener('resize', ()=>{ clearTimeout(t); t=setTimeout(applyHeroH,120); }, {passive:true});
    if('ResizeObserver' in window){ new ResizeObserver(applyHeroH).observe(hero); }
  })();

  function refreshCounters(){
    const now = Date.now();

    document.querySelectorAll('#cardsRow .card-box').forEach(function(card){
      let active = 0;
      card.querySelectorAll('.booking-item').forEach(function(li){
        const end = parseInt(li.getAttribute('data-end-ts'),10)||0;
        if (!end || now <= end) active++;
      });
      const bc = card.querySelector('.book-count');
      if (bc) bc.textContent = active + ' booking';

      card.querySelectorAll('.day-content').forEach(function(dc){
        const list = dc.querySelector('.conversation-list');
        const remain = list ? Array.from(list.querySelectorAll('.booking-item')).filter(function(li){
          const end = parseInt(li.getAttribute('data-end-ts'),10)||0;
          return !li.classList.contains('vanish') && (!end || now <= end);
        }).length : 0;

        let emptyNote = dc.querySelector('.empty-note');
        if (remain === 0){
          if (!emptyNote){
            emptyNote = document.createElement('div');
            emptyNote.className = 'empty-note text-muted small';
            emptyNote.textContent = 'Belum ada booking pada tanggal ini.';
            dc.appendChild(emptyNote);
          }
        } else {
          if (emptyNote) emptyNote.remove();
        }
      });
    });
  }
  </script>

  <!-- Lock orientation ke landscape -->
  <script>
  (function(){
    async function tryLockLandscape(){
      try{
        if (screen.orientation && screen.orientation.lock){
          await screen.orientation.lock('landscape');
        }
      }catch(e){}
    }

    ['fullscreenchange','webkitfullscreenchange','msfullscreenchange'].forEach(function(evt){
      document.addEventListener(evt, tryLockLandscape);
    });

    document.addEventListener('pointerdown', function once(){
      tryLockLandscape();
      document.removeEventListener('pointerdown', once, true);
    }, { once:true, capture:true });

    function isLandscape(){ return window.matchMedia('(orientation: landscape)').matches; }
    function applyGuard(){
      document.body.classList.toggle('force-landscape', !isLandscape());
    }
    applyGuard();
    window.addEventListener('orientationchange', applyGuard);
    window.addEventListener('resize', applyGuard);

    document.addEventListener('fullscreenchange', function(){
      setTimeout(tryLockLandscape, 300);
    });
  })();
  </script>

</body>
</html>
