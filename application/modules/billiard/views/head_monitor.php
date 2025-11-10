<!DOCTYPE html>
<html lang="id">
<head>
  <!-- ========== META DASAR ========== -->
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, viewport-fit=cover">

  <meta name="robots" content="index, follow">
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
  <link href="<?= base_url('assets/admin/css/icons.min.css'); ?>" rel="stylesheet" />
  <link href="<?= base_url('assets/admin/css/app.min.css'); ?>" rel="stylesheet" />
  <link href="<?= base_url('assets/admin/libs/animate/animate.min.css'); ?>" rel="stylesheet" />
  <link href="<?= base_url('assets/admin/libs/sweetalert2/sweetalert2.min.css'); ?>" rel="stylesheet" />

  <!-- ========== CSS KUSTOM (DIKELOMPOKKAN) ========== -->

</head>
<style>
  /* ====== COMPACT MODE UNTUK MONITORING ====== */
  body.compact .wrapper.curved{ --curve-h: 160px; }
  body.compact .hero-title{ padding: 10px 0 6px; }
  body.compact .hero-title .text{
    letter-spacing:.02em;
    font-size: clamp(14px, 2.4vw, 22px);
  }
  body.compact .hero-title .accent{ margin-top: 6px; }

  body.compact .card-box{ padding-top:38px !important; padding-bottom:46px !important; }
  body.compact .meja-ribbon span{ font-size:24px; padding:4px 10px; }
  body.compact .book-count{ top:8px; right:8px; padding:2px 8px; font-size:13px; }

  /* Kalender mini diperkecil */
  body.compact .day-row{ gap:6px; margin:8px 0; }
  body.compact .cal-ava{ width:40px; flex:0 0 40px; }
  body.compact .cal-tile{ width:34px; height:34px; border-radius:8px; }
  body.compact .cal-tile .cal-head{ height:10px; line-height:10px; font-size:8px; }
  body.compact .cal-tile .cal-day{ font-size:13px; }
  body.compact .cal-cap{ font-size:9px; }

  /* Bubble dirapikan & dipadatkan */
  body.compact .conversation-list .ctext-wrap{ padding:.45rem .55rem; border-radius:10px; }
  body.compact .ct-head{ gap:6px; }
  body.compact .ct-left{ font-size:16px; }
  body.compact .tz{ font-size:10px; margin-left:4px; }
  body.compact .status-pill{ padding:2px 8px; font-size:14px; }
  body.compact .verify-pill{ padding:2px 8px; font-size:10px; }
  body.compact .ct-meta{
    font-size:11px;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
  }
  body.compact hr{ margin:8px 0 !important; }

  /* Jam live sedikit dipadatkan juga */
  body.compact .live-clock{ min-width:160px; padding:10px 12px; }
  body.compact .live-clock .lc-time{ font-size:clamp(18px, 3.6vw, 30px); }
  body.compact .live-clock .lc-date{ font-size:11px; }
</style>
<style>
  /* ---- DOT UTAMA ---- */
  #liveDot{
    position:relative;
    width:10px; height:10px; border-radius:50%;
    display:inline-block; margin-right:6px;
    background: var(--dot-color,#a3a3a3) !important; /* override inline */
    color: var(--dot-color,#a3a3a3);                 /* untuk currentColor */
  }

  /* Variabel durasi default (bisa diubah via class status) */
  #liveDot{ --radar-ring: 2.2s; --radar-sweep: 2.4s; }

  /* ---- RING BERGELOMBANG (dua lingkaran bergantian) ---- */
  #liveDot.radar::before,
  #liveDot.radar::after{
    content:"";
    position:absolute; left:50%; top:50%;
    width:100%; height:100%;
    border:2px solid currentColor; border-radius:50%;
    transform: translate(-50%,-50%) scale(1);
    opacity:0; pointer-events:none;
  }
  #liveDot.radar::before{
    animation: radarRing var(--radar-ring) cubic-bezier(.2,.7,.2,1) infinite;
  }
  #liveDot.radar::after{
    animation: radarRing var(--radar-ring) cubic-bezier(.2,.7,.2,1) calc(var(--radar-ring)/2) infinite;
  }

  @keyframes radarRing{
    0%   { opacity:.65; transform:translate(-50%,-50%) scale(1); }
    100% { opacity:0;   transform:translate(-50%,-50%) scale(3.5); }
  }

  /* ---- SWEEP RADAR BERPUTAR ---- */
  #liveDot .sweep{
    position:absolute; left:50%; top:50%;
    width:240%; height:240%;
    transform:translate(-50%,-50%);
    border-radius:50%;
    opacity:.35; pointer-events:none; filter: blur(.2px);
    background: conic-gradient(from 0deg,
                  currentColor 0 28deg,
                  transparent 30deg 360deg);
    /* tampil hanya pada area cincin, tengah transparan */
    -webkit-mask: radial-gradient(circle at center,
                      transparent 0 38%,
                      black 40% 100%);
            mask: radial-gradient(circle at center,
                      transparent 0 38%,
                      black 40% 100%);
    animation: radarSweep var(--radar-sweep) linear infinite;
  }
  @keyframes radarSweep{
    from { transform: translate(-50%,-50%) rotate(0deg); }
    to   { transform: translate(-50%,-50%) rotate(360deg); }
  }

  /* ---- STATUS: atur warna + kecepatan ---- */
  #liveDot.is-ok   { --radar-ring: 1.8s; --radar-sweep: 2.0s; }
  #liveDot.is-idle { --radar-ring: 3.2s; --radar-sweep: 3.6s; }
  #liveDot.is-err  { --radar-ring: 1.8s; --radar-sweep: 2.0s; }

  /* Hormati preferensi reduce-motion */
  @media (prefers-reduced-motion: reduce){
    #liveDot.radar::before, #liveDot.radar::after, #liveDot .sweep{
      animation:none !important; opacity:0 !important;
    }
  }
</style>

<style>
  .live-clock{
    position: fixed;
    bottom: 16px; right: 16px;
    z-index: 9999;
    background: rgba(2, 6, 23, .72);
    color: #fff;
    padding: 12px 14px;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,.08);
    box-shadow: 0 10px 24px rgba(0,0,0,.25);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    min-width: 180px;
  }
  @media (prefers-color-scheme: light){
    .live-clock{ background: rgba(15, 23, 42, .62); }
  }
  .live-clock .lc-time{
    font-weight: 900;
    letter-spacing: .03em;
    line-height: 1;
    font-size: clamp(22px, 4.5vw, 40px);
  }
  .live-clock .lc-date{
    margin-top: 4px;
    font-size: 12px;
    font-weight: 600;
    opacity: .9;
    white-space: nowrap;
  }
  .live-clock .lc-badge{
    position: absolute;
    top: -8px; left: 8px;
    background: #16a34a;
    color: #fff;
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .04em;
    border: 1px solid rgba(255,255,255,.15);
  }
</style>
<style>
  /* ===== Overlay CTA ===== */
  .fs-cta{ position:fixed; inset:0; background:rgba(0,0,0,.6);
    display:flex; align-items:center; justify-content:center; z-index:9999; }
  .fs-cta__inner{ text-align:center; background:rgba(17,24,39,.88);
    border:1px solid rgba(255,255,255,.08); padding:20px 18px; border-radius:12px;
    color:#e5e7eb; max-width:92%; }
  .fs-cta__inner .emoji{ font-size:36px; margin-bottom:8px; }
  .fs-cta__inner h3{ font-size:18px; margin:6px 0; color:#fff; }
  .fs-cta__inner p{ font-size:13px; margin:0 0 12px; color:#cbd5e1; }
  .fs-cta__inner .hint{ font-size:12px; opacity:.8; margin-top:8px; }
  .fs-cta__inner .btn-cta{ padding:8px 14px; border-radius:8px; background:#10b981;
    color:#fff; border:0; font-weight:600; cursor:pointer; }
  .fs-cta__inner .btn-cta:active{ transform:translateY(1px); }

  /* ===== Floating toggle button ===== */
  .fs-btn{ position:fixed; right:12px; bottom:100px; z-index:9999; width:38px; height:38px;
    border-radius:10px; border:1px solid rgba(0,0,0,.12); background:#111827; color:#e5e7eb;
    cursor:pointer; display:grid; place-items:center; box-shadow:0 4px 16px rgba(0,0,0,.25); }
  .fs-btn .icon-exit{ display:none; }
  .fs-btn.active .icon-enter{ display:none; }
  .fs-btn.active .icon-exit{ display:inline; }
  .fs-btn:hover{ filter:brightness(1.05); }

  /* Hormati preferensi reduce-motion (tak berpengaruh besar di sini) */
  @media (prefers-reduced-motion: reduce){
    .fs-cta, .fs-btn{ transition:none !important; animation:none !important; }
  }
  body {
        padding-bottom: 0px !important;
    }
</style>

<style>
  /* Blink & warna peringatan 5 menit terakhir */
  @keyframes pillBlink { 50% { opacity: .35; } }

  .status-pill { transition: background-color .2s, color .2s, box-shadow .2s; }

  /* Saat < 5 menit ke akhir */
  .booking-item.soon  .status-pill{
    background:#fff7ed; color:#7c2d12; /* oranye lembut */
    box-shadow:0 0 0 0 rgba(251,146,60,.55);
    animation: pillBlink 1s steps(1,end) infinite;
  }
  /* Saat < 1 menit ke akhir (lebih urgent, opsional) */
  .booking-item.critical .status-pill{
    background:#fef2f2; color:#991b1b; /* merah lembut */
    animation: pillBlink .5s steps(1,end) infinite;
  }

  /* Hormati preferensi reduce-motion */
  @media (prefers-reduced-motion: reduce){
    .booking-item.soon  .status-pill,
    .booking-item.critical .status-pill{ animation:none !important; }
  }
</style>


<style type="text/css">
 hr {
    margin-top: 1rem;
    margin-bottom: 1rem;
    border: 0;
    border-top: 1px solid #264a80;
}
:root {
    --a1: #dd7634;
    --a2: #ffffff;
    --a3: #cddc39;
}
.hero-title {
    padding: 24px 0 10px;
    text-align: center;
}
.hero-title .text {
    color: #fff;
    display: inline-block;
    margin: 0;
    font-family:
        system-ui,
        -apple-system,
        "Segoe UI",
        Roboto,
        Arial,
        sans-serif;
    font-weight: 900;
    letter-spacing: 0.025em;
    text-transform: uppercase;
    line-height: 1.1;
    font-size: clamp(18px, 4.2vw, 32px);
    filter: drop-shadow(0 2px 10px rgba(139, 92, 246, 0.15));
    animation: popIn 0.7s ease-out both;
}
.hero-title .accent {
    display: block;
    height: 4px;
    width: 0;
    margin: 10px auto 0;
    border-radius: 999px;
    background: linear-gradient(90deg, var(--a1), var(--a2), var(--a3));
    box-shadow:
        0 0 18px rgba(34, 197, 94, 0.35),
        0 0 24px rgba(6, 182, 212, 0.25);
    animation: grow 0.9s 0.7s ease-out forwards;
}
@keyframes popIn {
    from {
        opacity: 0;
        transform: translateY(6px) scale(0.98);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
@keyframes grow {
    from {
        width: 0;
    }
    to {
        width: min(520px, 80%);
    }
}
@media (prefers-reduced-motion: reduce) {
    .hero-title .text,
    .hero-title .accent {
        animation: none;
    }
    .hero-title .accent {
        width: min(520px, 80%);
    }
}
:root {
    --c1: #4e77be;
    --c2: #1e3c72;
}
.wrapper {
    position: relative;
    isolation: isolate;
    /*border-radius: 20px;*/
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    margin-bottom: clamp(16px, 3vw, 32px);
}
.wrapper.curved {
    --curve-h: 320px;
}
.wrapper.curved::before {
    content: "";
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    height: var(--curve-h);
    background: linear-gradient(360deg, var(--c1), var(--c2));
    border-bottom-left-radius: 50% 16%;
    border-bottom-right-radius: 50% 16%;
    z-index: -1;
    pointer-events: none;
    filter: drop-shadow(0 18px 36px rgba(16, 24, 40, 0.18));
}
.wrapper > * {
    position: relative;
    z-index: 1;
}
.wrapper.curved.curve-sm {
    --curve-h: 140px;
}
.wrapper.curved.curve-md {
    --curve-h: 220px;
}
.wrapper.curved.curve-lg {
    --curve-h: 320px;
}
.wrapper.curved.curve-xl {
    --curve-h: 420px;
}

</style>

<style type="text/css">
              /* Ribbon nama meja (center) */
              .meja-ribbon{position:absolute;top:-12px;left:50%;transform:translateX(-50%);z-index:2}
              .meja-ribbon span{
                background:#d30048;color:#fff;font-weight:700;font-size:14px;
                padding:6px 14px;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,.15)
              }
              .book-count{
                position:absolute;top:10px;right:12px;z-index:2;
                background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;
                border-radius:999px;padding:4px 10px;font-size:12px;font-weight:600
              }

              /* CTA Ribbon (bawah kanan) */
              .ribbon-cta{position:absolute;right:10px;bottom:10px;z-index:2}
              .ribbon-cta .cta-btn{
                position:relative;display:inline-flex;align-items:center;gap:1px;
                background:#3f51b5;color:#fff;text-decoration:none;
                padding:7px 12px;border-radius:8px;font-weight:800;font-size:12px;
                box-shadow:0 2px 6px rgba(0,0,0,.2);
                transform:rotate(-5deg); border:1px solid rgba(255,255,255,.15)
              }
              .ribbon-cta .cta-btn:hover{filter:brightness(1.05)}
              .ribbon-cta .cta-btn i{font-style:normal;opacity:.95}
              .ribbon-cta .cta-btn:after{
                content:"→"; font-weight:900; line-height:1; transform:translateY(-.5px)
              }

              /* Kalender mini */
              .cal-ava{width:46px;display:flex;flex-direction:column;align-items:center;gap:2px;flex:0 0 46px}
              .cal-tile{width:40px;height:40px;border:1px solid #eef2f7;border-radius:8px;background:#fff;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 1px 4px rgba(15,23,42,.06)}
              .cal-tile .cal-head{height:12px;line-height:12px;text-align:center;background:#0d6efd;color:#fff;font-size:9px;font-weight:700;letter-spacing:.4px;text-transform:uppercase}
              .cal-tile .cal-day{flex:1;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:15px;color:#111827}
              .cal-cap{font-size:10px;font-weight:700;line-height:1;color:#6b7280;text-transform:uppercase;letter-spacing:.15px}
              @media(prefers-color-scheme:dark){.cal-tile{border-color:#334155;background:#0b1220}.cal-tile .cal-day{color:#e5e7eb}.cal-cap{color:#9ca3af}}

              /* Layout per tanggal */
              .day-row{display:flex;align-items:flex-start;gap:8px;margin:10px 0}
              .day-content{flex:1 1 auto;min-width:0}

              /* Bubble */
              .conversation-list{margin:0;padding-left:0;list-style:none}
              .conversation-list li{display:flex;align-items:flex-start;margin:0 0 8px 0}
              .conversation-list .conversation-text{flex:1 1 auto;display:flex;margin:0 !important}
              .conversation-list .ctext-wrap{
                width:100%;padding:.55rem .7rem;border:1px solid #eef2f7;border-radius:12px;background:#f8fafc
              }
              .conversation-list .ctext-wrap:before,
              .conversation-list .ctext-wrap:after{display:none!important}

              /* Header bubble: kiri jam+durasi+WITA, kanan status+countdown (sejajar) */
              .ct-head{display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap}
              .ct-left{font-weight:700;font-size:14px}
              .tz{font-weight:600;font-size:11px;margin-left:6px;color:#64748b}
              .ct-right{display:flex;align-items:center;gap:8px}
              .status-pill{
                display:inline-flex;align-items:center;gap:6px;
                background:#1f2937;color:#fff;border-radius:999px;padding:3px 10px;font-size:11px;font-weight:700
              }
              .status-pill.success{background:#16a34a}
              .status-pill.muted{background:#6b7280}
              .cd{font-weight:700}
              .ct-meta{margin-top:2px;font-size:12px;color:#6b7280}

              /* ➕ Tambahan: pill untuk Verifikasi + Cash */
              .verify-pill{
                display:inline-flex;align-items:center;gap:6px;
                background:#f59e0b;color:#111827;border-radius:999px;
                padding:3px 10px;font-size:11px;font-weight:700;
                border:1px solid rgba(0,0,0,.05)
              }
            </style>


<style>
  /* ===== UTIL SR-ONLY (aksesibilitas) ===== */
  .sr-only{
    position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;
    clip:rect(0,0,0,0);white-space:nowrap;border:0
  }

  /* ===== FIXED TV TICKER ===== */
  .notice-ticker{
    --ticker-h: 46px;              /* tinggi bar */
    --ticker-pad-y: 8px;           /* padding vertikal konten */
    --ticker-speed-pps: 95;        /* kecepatan (pixel per detik), bisa diubah */
    position: relative;
    color: #f8fafc;
    background: rgba(2,6,23,.85);
    border-top: 1px solid rgba(255,255,255,.12);
    box-shadow: 0 -10px 24px rgba(0,0,0,.25);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    white-space: nowrap;
  }
  .notice-ticker--fixed{
    position: fixed; left: 0; right: 0; bottom: 0; z-index: 10050;
    height: var(--ticker-h);
    display: flex; align-items: center;
    padding: 0 0; /* sumbu Y diatur oleh inner */
  }
  .notice-ticker--fixed::before{
    /* garis gradasi tipis di atas ticker */
    content:""; position:absolute; left:0; right:0; top:0; height:2px;
    background: linear-gradient(90deg,#22c55e,#06b6d4,#f59e0b);
    opacity:.8
  }

  /* Track bergerak */
  .ticker-track{
    display: inline-block;
    will-change: transform;
    padding-left: 100%; /* mulai dari kanan layar */
    animation: tickerScroll var(--ticker-duration, 30s) linear infinite;
  }
  .notice-ticker:hover .ticker-track{ animation-play-state: paused; }

  .t-item{
    display: inline-block;
    font-weight: 800;
    letter-spacing: .02em;
    padding: var(--ticker-pad-y) 0;
    margin-right: 36px;
  }
  .t-sep{ opacity: .5; margin: 0 16px; }

  /* Teks ukuran nyaman & responsif */
  .notice-ticker,
  .notice-ticker .t-item{ font-size: clamp(12px, 1.9vw, 15px); }

  /* Compact mode (ikuti gaya kamu) */
  body.compact .notice-ticker{ --ticker-h: 40px; --ticker-pad-y: 6px; }

  /* Tema terang */
  @media (prefers-color-scheme: light){
    .notice-ticker{ background: rgba(15,23,42,.78); color:#fff; }
  }

  /* Hormati reduce-motion */
  @media (prefers-reduced-motion: reduce){
    .ticker-track{ animation: none !important; padding-left: 0 !important; }
  }

  @keyframes tickerScroll{
    from { transform: translateX(0); }
    to   { transform: translateX(var(--ticker-translate, -100%)); }
  }

  /* Beri ruang di bawah konten & geser jam agar tak ketimpa */
  .has-fixed-ticker .wrapper{ padding-bottom: calc(var(--ticker-h) + 10px) !important; }
  .has-fixed-ticker .live-clock{ bottom: calc(16px + var(--ticker-h)); }
</style>
<style>
  /* Ticker di bawah live-clock */
  .notice-ticker--fixed{ z-index: 9980 !important; }

  /* Jangan tambah ruang bawah & jangan geser jam */
  .has-fixed-ticker .wrapper{ padding-bottom: 0 !important; }
  .has-fixed-ticker .live-clock{ bottom: 16px !important; } /* posisi defaultmu */
</style>
<style>
  /* Animasi lenyap yang halus */
  .booking-item.vanish {
    opacity: 0;
    height: 0;
    margin: 0;
    padding: 0;
    overflow: hidden;
    transition: opacity .25s, height .25s, margin .25s, padding .25s;
  }
</style>


<body class="menubar-gradient gradient-topbar topbar-dark compact">

<div class="live-clock" id="liveClock" role="timer" aria-live="polite" aria-label="Jam lokal WITA">
  <div class="lc-badge">WITA</div>
  <div class="lc-time" id="lcTime">00:00:00</div>
  <div class="lc-date" id="lcDate">Senin, 01 Januari 1970</div>
</div>


    <style type="text/css">
      .wrapper {
        padding-top: 0px;
    }
    </style>


<!-- WRAPPER HALAMAN / AREA SCROLL -->
<div class="wrapper curved" style="--curve-h: 330px;" id="app-scroll">

  <!-- preloader lama -->
  <div id="preloader">
    <div id="status">
      <div class="image-container animated flip infinite">
        <img
          src="<?= base_url('assets/images/loader.png') ?>"
          alt="Foto"
          style="display: none;"
          onload="this.style.display='block';"
        />
      </div>
    </div>
  </div>




