<?php $this->load->view("head_monitor") ?>

<div class="container-fluid mt-2" aria-live="polite">
  <div class="hero-title" role="banner" aria-label="Judul situs">
     <span id="liveDot" style="width:14px;height:14px;border-radius:50%;display:inline-block;background:#aaa;margin-right:20px;"></span><h1 class="text" id="liveText">Menghubungkan…</h1>
    <span class="accent" aria-hidden="true"></span>
  </div>

<!-- ===== FIXED TV TICKER: full-width di bawah layar ===== -->
<div id="fixedTicker" class="notice-ticker notice-ticker--fixed" role="region" aria-label="Pengumuman berjalan">
  <!-- Versi statis untuk screen reader agar tidak dibacakan berulang -->
 <!-- Versi statis untuk screen reader -->
<!-- Versi statis untuk screen reader -->
<span class="sr-only">
  Pengumuman: Harap bermain sportif, santai, dan saling menghargai. Jika belum menang, jangan baper—tetap seru dan happy bareng; tepati waktu mulai dan selesai bermain; jangan lupa berdoa sebelum bermain; arahkan stik ke bola, bukan ke teman.
</span>

<!-- Track bergerak (disembunyikan dari screen reader) -->
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


  <!-- indikator live -->
  <!-- <div class="d-flex align-items-center small text-muted">
    <span id="liveDot" style="width:8px;height:8px;border-radius:50%;display:inline-block;background:#aaa;margin-right:6px;"></span>
    <span id="liveText">Menghubungkan…</span>
  </div> -->

  <div class="row" id="cardsRow">
    <div class="col-12">
      <div class="card-box"><p class="mb-0">Memuat data…</p></div>
    </div>
  </div>

</div>

<script src="<?= base_url('assets/admin/js/vendor.min.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/app.min.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/sw.min.js') ?>"></script>

<!-- Jam live WITA (butuh #lcTime & #lcDate di head_monitor) -->
<script>
(function(){
  var tz = 'Asia/Makassar';
  var timeEl = document.getElementById('lcTime');
  var dateEl = document.getElementById('lcDate');
  if(!timeEl || !dateEl) return;

  var fmtTime = new Intl.DateTimeFormat('id-ID', { hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:false, timeZone:tz });
  var fmtDate = new Intl.DateTimeFormat('id-ID', { weekday:'long', day:'2-digit', month:'long', year:'numeric', timeZone:tz });

  function updateClock(){
    var now = new Date();
    timeEl.textContent = fmtTime.format(now);
    dateEl.textContent = fmtDate.format(now);
  }
  updateClock();
  setInterval(updateClock, 1000);
})();
</script>

<script>
(function(){
  // ================== ENDPOINTS ==================
  var EP_DATA      = '<?= site_url($controller."/monitor_data"); ?>';
  var PING_BIL_URL = '<?= site_url($controller."/monitor_ping"); ?>';

  // ================== UI refs ==================
  var rowEl    = document.getElementById('cardsRow');
  var liveDot  = document.getElementById('liveDot');
  var liveText = document.getElementById('liveText');

  function setLive(status){
    if(!liveDot || !liveText) return;

    // reset kelas status
    liveDot.classList.add('radar');
    liveDot.classList.remove('is-ok','is-idle','is-err','is-conn');

    if(status==='ok'){
      liveText.textContent = 'Live Billiard Ausi';
      liveDot.style.setProperty('--dot-color', '#10b981'); // hijau
      liveDot.style.color = '#10b981';
      liveDot.classList.add('is-ok');
    }
    else if(status==='idle'){
      liveText.textContent = 'Menunggu perubahan…';
      liveDot.style.setProperty('--dot-color', '#6b7280'); // abu-abu
      liveDot.style.color = '#6b7280';
      liveDot.classList.add('is-idle');
    }
    else if(status==='err'){
      liveText.textContent = 'Gangguan koneksi';
      liveDot.style.setProperty('--dot-color', '#ef4444'); // merah
      liveDot.style.color = '#ef4444';
      liveDot.classList.add('is-err');
    }
    else{
      liveText.textContent = 'Menghubungkan…';
      liveDot.style.setProperty('--dot-color', '#a3a3a3'); // default
      liveDot.style.color = '#a3a3a3';
      liveDot.classList.add('is-idle'); // kedip lambat saat connecting
    }
  }
  // ================== Renderer ==================
  function esc(s){ return (s==null?'':String(s)).replace(/[&<>"']/g,function(c){return({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'})[c]}); }

  function renderCards(cards){
    if(!Array.isArray(cards) || cards.length===0){
      rowEl.innerHTML = '<div class="col-12"><div class="card-box"><p class="mb-0">Belum ada bookingan mendatang.</p></div></div>';
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
            var showVeriCash = (String(b.status||'').toLowerCase()==='verifikasi'
                                && String(b.metode_bayar||'').toLowerCase()==='cash');
            html += '<li class="booking-item" data-start-ts="'+(b.start_ts||0)+'" data-end-ts="'+(b.end_ts||0)+'">';
            html +=   '<div class="conversation-text"><div class="ctext-wrap">';
            html +=     '<div class="ct-head">';
            html +=       '<div class="ct-left">'+esc(b.jam_mulai)+' – '+esc(b.jam_selesai)+' · '+parseInt(b.durasi_jam||0,10)+' jam <span class="tz">WITA</span></div>';
            html +=       '<div class="ct-right mb-1">';
            html +=         '<span class="status-pill"><span class="status-label">Mulai dalam</span> · <span class="cd">00:00:00</span></span>';
            // if(showVeriCash){
            //   html +=       ' <span class="verify-pill" title="Status: verifikasi, metode bayar cash" aria-label="Verifikasi (Cash)">Verifikasi Cash</span>';
            // }
            html +=       '</div>';
            html +=     '</div>';
            html +=     '<div class="ct-meta">'+esc(b.nama||'Booking')+(b.hp_masked?(' · '+esc(b.hp_masked)):'')+'</div>';
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
  }

  // ================== Countdown status ==================
  function pad(n){return (n<10?'0':'')+n;}
  function fmt(ms){
    if(ms<=0)return'00:00:00';
    var s=Math.floor(ms/1000);
    var d=Math.floor(s/86400); s%=86400;
    var h=Math.floor(s/3600);  s%=3600;
    var m=Math.floor(s/60);    var sec=s%60;
    var day=d>0?(d+' hari '):'';
    return day+pad(h)+':'+pad(m)+':'+pad(sec);
  }

  // ambang 5 menit & 1 menit
  const FIVE_MIN_MS = 5*60*1000;
  const ONE_MIN_MS  = 60*1000;

  
  function runCountdown(){
    var now = Date.now();
    document.querySelectorAll('.booking-item').forEach(function(item){
      var start = parseInt(item.getAttribute('data-start-ts'),10)||0;
      var end   = parseInt(item.getAttribute('data-end-ts'),10)||0;
      var pill  = item.querySelector('.status-pill');
      var label = item.querySelector('.status-label');
      var cd    = item.querySelector('.cd');
      if(!pill||!label||!cd) return;

      pill.classList.remove('success','muted');
      item.classList.remove('soon','critical');

      if(start && now < start){
        // Belum mulai
        label.textContent = 'Mulai dalam';
        cd.textContent    = fmt(start - now);
      }
      else if(end && now <= end){
        // Sedang bermain
        label.textContent = 'Sedang bermain';
        cd.textContent    = fmt(end - now);
        pill.classList.add('success');

        var left = end - now;
        if (left > 0 && left <= FIVE_MIN_MS){
          // 5 menit terakhir → berkedip
          item.classList.add('soon');
          if (left <= ONE_MIN_MS) item.classList.add('critical');

          // bunyi sekali saat baru masuk jendela 5 menit
          if (!item.dataset.beep5){
            try{ playSound(); }catch(e){}
            item.dataset.beep5 = '1';
          }
        } else {
          // reset flag bila sudah lewat dari 5 menit / selesai
          delete item.dataset.beep5;
        }
      }
      else{
  // Selesai
  label.textContent = 'Selesai';
  cd.textContent    = '00:00:00';
  pill.classList.add('muted');
  delete item.dataset.beep5;

  // ===== Langsung hilang (dengan animasi) =====
// tunggu 15000 ms (15 dtk), baru hilang
setTimeout(function(){
  if (!item.dataset.removing){
    item.dataset.removing = '1';
    item.classList.add('vanish');
    setTimeout(function(){ item.remove(); }, 300);
  }
}, 15000);

}

    });
  }
  // interval sudah ada di kode kamu: setInterval(runCountdown, 1000);

  setInterval(runCountdown, 1000);

  // ================== Doorbell "ting–tong" (autoplay-safe) ==================
  (function(){
    const AC = window.AudioContext || window.webkitAudioContext;
    let ctx = null;
    let lastWall = 0;  // throttle pakai wall clock

    function ensureCtx(){
      if (!ctx) ctx = new AC();
      return ctx;
    }
    function hit(ac, freq, t0, dur, vol, type){
      const now = ac.currentTime;
      const o = ac.createOscillator();
      const g = ac.createGain();
      o.type = type || 'triangle';
      o.frequency.setValueAtTime(freq, now + t0);
      g.gain.setValueAtTime(0.0001, now + t0);
      g.gain.exponentialRampToValueAtTime(vol, now + t0 + 0.03);
      g.gain.exponentialRampToValueAtTime(0.0001, now + t0 + dur);
      o.connect(g); g.connect(ac.destination);
      o.start(now + t0);
      o.stop(now + t0 + dur + 0.05);
    }
    function _play(){
      const ac = ensureCtx();
      hit(ac, 880.00, 0.00, 0.22, 0.10, 'triangle'); // "Ting" A5
      hit(ac, 587.33, 0.16, 0.30, 0.11, 'sine');     // "Tong" D5
    }
    window.playSound = function(){
      const t = Date.now();
      if (t - lastWall < 1200) return; // throttle 1.2s
      lastWall = t;
      const ac = ensureCtx();
      if (ac.state !== 'running'){ ac.resume().then(_play).catch(()=>{}); }
      else { _play(); }
    };
    // prime sekali pada gesture agar lolos autoplay policy
    function prime(){
      const ac = ensureCtx();
      if (ac.state !== 'running') ac.resume();
      const s = ac.createBufferSource();
      s.buffer = ac.createBuffer(1, 1, ac.sampleRate);
      s.connect(ac.destination); s.start(0);
    }
    document.addEventListener('pointerdown', prime, { once:true });
  })();

  // ================== Ping ringan ==================
  const last = { bil: { total:null, max_id:null, last_ts:null } };
  const BASE_INTERVAL = 10000, HIDDEN_INTERVAL = 20000;
  let errorStreak = 0, ticking = false;

  async function safeFetch(url){
    const r = await fetch(url, { cache:'no-store', credentials:'same-origin' });
    if(!r.ok) throw new Error('HTTP '+r.status);
    return r.json();
  }

  // Hindari overlap reload
  let reloadAborter = null, reloading = false;
  async function reload_billiard_table(reason){
    if (reloading) { try{ reloadAborter && reloadAborter.abort(); }catch(_){ } }
    reloadAborter = new AbortController();
    reloading = true;
    try{
      setLive('ok');
      const r = await fetch(EP_DATA, { headers:{'Accept':'application/json'}, cache:'no-store', signal: reloadAborter.signal });
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
        await reload_billiard_table('baseline'); // muat awal
      } else {
        const added   = isAdded(last.bil, snap);
        const changed = isChanged(last.bil, snap);
        last.bil = snap; // update snapshot dulu
        if (changed) await reload_billiard_table('changed'); // hanya saat berubah
        if (added)   playSound(); // ting–tong saat ada penambahan
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

  // kickoff
  loop();
  document.addEventListener('visibilitychange', function(){
    if (!document.hidden) setTimeout(loop, 200);
  });
})();
</script>
<script>
  // buat elemen sweep sekali agar bisa dianimasikan
  (function(){
    if (liveDot && !liveDot.querySelector('.sweep')){
      var s = document.createElement('i');
      s.className = 'sweep';
      liveDot.appendChild(s);
    }
  })();
</script>
<script>
(function(){
  const LS_KEY = 'ausi_fs_auto';
  const target = document.documentElement; // bisa diganti ke document.body jika mau

  function isFs(){
    return !!(document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement);
  }
  function setBtnActive(on){
    if (!btn) return;
    btn.classList.toggle('active', !!on);
  }

  async function enterFs(){
    try{
      if (target.requestFullscreen) {
        await target.requestFullscreen({ navigationUI: 'hide' });
      } else if (target.webkitRequestFullscreen) {
        target.webkitRequestFullscreen();
      } else if (target.msRequestFullscreen) {
        target.msRequestFullscreen();
      }
      localStorage.setItem(LS_KEY, '1');
      hideCta();
      setBtnActive(true);
    } catch(e){
      // diam: browser bisa blok tanpa gesture
    }
  }
  async function exitFs(){
    try{
      if (document.exitFullscreen) { await document.exitFullscreen(); }
      else if (document.webkitExitFullscreen) { document.webkitExitFullscreen(); }
      else if (document.msExitFullscreen) { document.msExitFullscreen(); }
      setBtnActive(false);
    } catch(e){}
  }

  // ===== Overlay CTA =====
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

  // ===== Floating button =====
  let btn = null;
  function buildBtn(){
    if (btn) return;
    btn = document.createElement('button');
    btn.className = 'fs-btn';
    btn.type = 'button';
    btn.title = 'Toggle layar penuh (F)';
    btn.innerHTML = '<span class="icon-enter" aria-hidden="true">⛶</span><span class="icon-exit" aria-hidden="true">⤫</span>';
    document.body.appendChild(btn);
    btn.addEventListener('click', ()=> isFs() ? exitFs() : enterFs());
  }

  // ===== Keyboard & event hooks =====
  document.addEventListener('keydown', function(ev){
    if (ev.key && ev.key.toLowerCase() === 'f'){
      ev.preventDefault();
      isFs() ? exitFs() : enterFs();
    }
  });
  ['fullscreenchange','webkitfullscreenchange','msfullscreenchange'].forEach(evt=>{
    document.addEventListener(evt, ()=> setBtnActive(isFs()));
  });

  // ===== Init =====
  function init(){
    buildBtn();

    const consent = localStorage.getItem(LS_KEY) === '1';
    if (consent) {
      // langsung coba (jika diblok, CTA tetap tampil di gesture pertama)
      enterFs();
    } else {
      showCta();
    }

    // upayakan auto pada gesture pertama (kebijakan browser mewajibkan gesture)
    const once = ()=>{ enterFs(); window.removeEventListener('pointerdown', once, true); };
    window.addEventListener('pointerdown', once, { once:true, capture:true });
  }

  if (document.readyState !== 'loading') init();
  else document.addEventListener('DOMContentLoaded', init);

  /* ============ OPSIONAL: kunci layar nyala (Wake Lock) ============
     Aktifkan jika perlu agar layar tidak mati saat monitor:
     (tidak disupport semua browser)
  */
  
  let wakeLock = null;
  async function requestWakeLock(){
    try{
      if ('wakeLock' in navigator){
        wakeLock = await navigator.wakeLock.request('screen');
        // wakeLock.addEventListener('release', ()=>{  noop  });
        wakeLock.addEventListener('release', ()=>{});
      }
    }catch(e){}
  }
  document.addEventListener('visibilitychange', ()=>{ if (!document.hidden) requestWakeLock(); });
  requestWakeLock();
  
})();
</script>
<script>
(function(){
  const el = document.getElementById('fixedTicker');
  if(!el) return;
  const track = el.querySelector('.ticker-track');
  if(!track) return;

  const originalHTML = track.innerHTML;

  function rebuild(){
    const vw = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
    // reset isi → gandakan secukupnya agar scroll mulus tanpa jeda
    track.innerHTML = originalHTML;
    while (track.scrollWidth < vw * 2.2){
      track.innerHTML += originalHTML;
    }
    // set durasi berdasar panjang konten
    const distance = track.scrollWidth + vw; // mulai kanan → keluar kiri
    const pps = parseFloat(getComputedStyle(el).getPropertyValue('--ticker-speed-pps')) || 95;
    el.style.setProperty('--ticker-duration', (distance/pps) + 's');
    el.style.setProperty('--ticker-translate', (-distance) + 'px');
  }

  rebuild();
  let t;
  window.addEventListener('resize', ()=>{ clearTimeout(t); t = setTimeout(rebuild, 120); }, { passive:true });
})();
</script>

<script>
(function(){
  const HOUR  = 60 * 60 * 1000; // 1 jam
  const CHECK = 60 * 1000;      // cek tiap 1 menit saat tab tersembunyi

  function reloadOrWait(){
    if (!document.hidden) {
      location.reload();       // reload biasa; server headers kamu sudah no-cache utk data
      return;
    }
    // kalau tab tidak sedang aktif, tunggu & cek lagi
    setTimeout(reloadOrWait, CHECK);
  }

  function scheduleReload(){
    setTimeout(reloadOrWait, HOUR);
  }

  scheduleReload();
})();
</script>

