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

  <!-- ========== CSS KUSTOM ========== -->
  <style>
    :root{
      --safe-bottom: 16px;
      --a1:#dd7634; --a2:#8c2424; --a3:#cddc39;
      --c1:#4e77be; --c2:#1e3c72;
      --ticker-h: 46px;
      --clock-h: 88px;
      --hero-h: 120px; /* diisi via JS */
    }

    /* ===== EMPTY STATE (2 kolom kiri teks, kanan video) ===== */
    .empty-wrap{
      width: clamp(320px, 94vw, 1280px);
      margin: clamp(8px,2vw,16px) auto 0;
      padding: clamp(8px,2vw,16px) clamp(8px,2vw,18px);
      min-height: calc(100svh - var(--ticker-h) - var(--clock-h) - 120px);
      display: block;
    }
    .empty-grid{
      display:grid;
      grid-template-columns: minmax(260px,1fr) minmax(300px,1.4fr);
      align-items:center;
      gap: clamp(12px,3vw,28px);
    }
    @media (max-width: 992px){
      .empty-grid{ grid-template-columns: 1fr; }
      .empty-left{ text-align:center; }
    }

    .empty-left .empty-hero{ display:flex; align-items:center; gap:10px; margin:6px 0 6px; }
    .empty-left .emoji-8ball{ font-size:clamp(20px,3.2vw,36px); filter:drop-shadow(0 2px 6px rgba(0,0,0,.15)); }
    .empty-title{
      margin:0; font-weight:900; letter-spacing:.02em; line-height:1.1;
      font-size:clamp(18px, 4vw, 34px);
      background:linear-gradient(90deg,var(--a1),var(--a2),var(--a3));
      -webkit-background-clip:text; background-clip:text; color:transparent;
    }
    .empty-sub{ margin:6px 0 10px; color:#64748b; font-weight:600; font-size:clamp(12px,2vw,16px) }
    .empty-divider{
      width:min(520px,80%); height:3px; margin:8px 0 12px; border-radius:999px;
      background:linear-gradient(90deg,#22c55e,#06b6d4,#f59e0b); opacity:.9;
      box-shadow:0 0 12px rgba(34,197,94,.25), 0 0 16px rgba(6,182,212,.2);
    }

    .empty-right .empty-video{
      border-radius:12px; overflow:hidden; box-shadow:0 6px 16px rgba(0,0,0,.15);
      width:100%;
      margin:0;
      margin-bottom: calc(var(--clock-h) + 8px);
    }
    .embed-16x9{ position:relative; width:100%; aspect-ratio:16/9; max-height: calc(100svh - var(--hero-h) - var(--clock-h) - var(--ticker-h) - 42px); }
    .embed-16x9 iframe{ position:absolute; inset:0; width:100%; height:100%; border:0; }
    @supports not (aspect-ratio: 16/9){
      .embed-16x9{ height:0; padding-bottom:56.25%; max-height:none; }
    }
    body.has-fixed-ticker .empty-right .empty-video{ max-height: none !important; }

    body { padding-bottom: 0 !important; }

    /* ===== HERO ===== */
    .hero-title{ padding:24px 0 10px; text-align:center; }
    .hero-title .text{
      color:#fff; display:inline-block; margin:0; font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;
      font-weight:900; letter-spacing:.025em; text-transform:uppercase; line-height:1.1;
      font-size:clamp(18px,4.2vw,32px); filter:drop-shadow(0 2px 10px rgba(139,92,246,.15));
      animation:popIn .7s ease-out both;
    }
    .hero-title .accent{
      display:block; height:4px; width:0; margin:10px auto 0; border-radius:999px;
      background:linear-gradient(90deg,var(--a1),var(--a2),var(--a3));
      box-shadow:0 0 18px rgba(34,197,94,.35),0 0 24px rgba(6,182,212,.25);
      animation:grow .9s .7s ease-out forwards;
    }
    @keyframes popIn{from{opacity:0;transform:translateY(6px) scale(.98)} to{opacity:1;transform:none}}
    @keyframes grow{from{width:0} to{width:min(520px,80%)}}
    @media (prefers-reduced-motion:reduce){
      .hero-title .text,.hero-title .accent{animation:none}
      .hero-title .accent{width:min(520px,80%)}
    }

    /* ===== WRAPPER ===== */
    .wrapper{ position:relative; isolation:isolate; box-shadow:0 16px 36px rgba(0,0,0,.08); overflow:hidden; margin-bottom:clamp(16px,3vw,32px); }
    .wrapper.curved{ --curve-h:320px; }
    .wrapper.curved::before{
      content:""; position:absolute; inset:0 0 auto 0; height:var(--curve-h);
      background:linear-gradient(360deg,var(--c1),var(--c2));
      border-bottom-left-radius:50% 16%; border-bottom-right-radius:50% 16%;
      z-index:-1; filter:drop-shadow(0 18px 36px rgba(16,24,40,.18));
    }
    .wrapper{ padding-top:0px; }

    /* ===== LIVE DOT ===== */
    #liveDot{ position:relative; width:10px; height:10px; border-radius:50%; display:inline-block; margin-right:6px;
      background:var(--dot-color,#a3a3a3)!important; color:var(--dot-color,#a3a3a3) }
    #liveDot{ --radar-ring:2.2s; --radar-sweep:2.4s; }
    #liveDot.radar::before,#liveDot.radar::after{
      content:""; position:absolute; left:50%; top:50%; width:100%; height:100%;
      border:2px solid currentColor; border-radius:50%; transform:translate(-50%,-50%) scale(1); opacity:0; pointer-events:none
    }
    #liveDot.radar::before{ animation:radarRing var(--radar-ring) cubic-bezier(.2,.7,.2,1) infinite }
    #liveDot.radar::after{ animation:radarRing var(--radar-ring) cubic-bezier(.2,.7,.2,1) calc(var(--radar-ring)/2) infinite }
    @keyframes radarRing{ 0%{opacity:.65; transform:translate(-50%,-50%) scale(1)} 100%{opacity:0; transform:translate(-50%,-50%) scale(3.5)} }
    #liveDot .sweep{
      position:absolute; left:50%; top:50%; width:240%; height:240%; transform:translate(-50%,-50%);
      border-radius:50%; opacity:.35; filter:blur(.2px); pointer-events:none;
      background:conic-gradient(from 0deg, currentColor 0 28deg, transparent 30deg 360deg);
      -webkit-mask:radial-gradient(circle at center, transparent 0 38%, black 40% 100%);
      mask:radial-gradient(circle at center, transparent 0 38%, black 40% 100%);
      animation:radarSweep var(--radar-sweep) linear infinite;
    }
    @keyframes radarSweep{ from{transform:translate(-50%,-50%) rotate(0)} to{transform:translate(-50%,-50%) rotate(360deg)} }
    #liveDot.is-ok{--radar-ring:1.8s; --radar-sweep:2.0s}
    #liveDot.is-idle{--radar-ring:3.2s; --radar-sweep:3.6s}
    #liveDot.is-err{--radar-ring:1.8s; --radar-sweep:2.0s}
    @media (prefers-reduced-motion:reduce){ #liveDot.radar::before,#liveDot.radar::after,#liveDot .sweep{ animation:none!important; opacity:0!important } }

    /* ===== CARD & KALENDER MINI (tidak diubah) ===== */
    .meja-ribbon{position:absolute;top:-12px;left:50%;transform:translateX(-50%);z-index:2}
    .meja-ribbon span{background:#d30048;color:#fff;font-weight:700;font-size:14px;padding:6px 14px;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,.15)}
    .book-count{position:absolute;top:10px;right:12px;z-index:2;background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;border-radius:999px;padding:4px 10px;font-size:12px;font-weight:600}
    .cal-ava{width:46px;display:flex;flex-direction:column;align-items:center;gap:2px;flex:0 0 46px}
    .cal-tile{width:40px;height:40px;border:1px solid #eef2f7;border-radius:8px;background:#fff;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 1px 4px rgba(15,23,42,.06)}
    .cal-tile .cal-head{height:12px;line-height:12px;text-align:center;background:#0d6efd;color:#fff;font-size:9px;font-weight:700;letter-spacing:.4px;text-transform:uppercase}
    .cal-tile .cal-day{flex:1;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:15px;color:#111827}
    .cal-cap{font-size:10px;font-weight:700;line-height:1;color:#6b7280;text-transform:uppercase;letter-spacing:.15px}
    @media(prefers-color-scheme:dark){.cal-tile{border-color:#334155;background:#0b1220}.cal-tile .cal-day{color:#e5e7eb}.cal-cap{color:#9ca3af}}
    .day-row{display:flex;align-items:flex-start;gap:8px;margin:10px 0}
    .day-content{flex:1 1 auto;min-width:0}
    .conversation-list{margin:0;padding-left:0;list-style:none}
    .conversation-list li{display:flex;align-items:flex-start;margin:0 0 8px 0}
    .conversation-list .conversation-text{flex:1 1 auto;display:flex;margin:0!important}
    .conversation-list .ctext-wrap{width:100%;padding:.55rem .7rem;border:1px solid #eef2f7;border-radius:12px;background:#f8fafc}
    .ct-head{display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap}
    .ct-left{font-weight:700;font-size:14px}
    .tz{font-weight:600;font-size:11px;margin-left:6px;color:#64748b}
    .ct-right{display:flex;align-items:center;gap:8px}
    .status-pill{display:inline-flex;align-items:center;gap:6px;background:#1f2937;color:#fff;border-radius:999px;padding:3px 10px;font-size:11px;font-weight:700}
    .status-pill.success{background:#16a34a}
    .status-pill.muted{background:#6b7280}
    .cd{font-weight:700}
    .ct-meta{margin-top:2px;font-size:12px;color:#6b7280}
    .verify-pill{display:inline-flex;align-items:center;gap:6px;background:#f59e0b;color:#111827;border-radius:999px;padding:3px 10px;font-size:11px;font-weight:700;border:1px solid rgba(0,0,0,.05)}
    @keyframes pillBlink{50%{opacity:.35}}
    .booking-item.soon .status-pill{background:#fff7ed;color:#7c2d12; box-shadow:0 0 0 0 rgba(251,146,60,.55); animation:pillBlink 1s steps(1,end) infinite}
    .booking-item.critical .status-pill{background:#fef2f2;color:#991b1b; animation:pillBlink .5s steps(1,end) infinite}
    @media (prefers-reduced-motion:reduce){ .booking-item.soon .status-pill,.booking-item.critical .status-pill{animation:none!important} }
    .booking-item.vanish{opacity:0;height:0;margin:0;padding:0;overflow:hidden;transition:opacity .25s,height .25s,margin .25s,padding .25s}

    /* ===== SR-ONLY ===== */
    .sr-only{ position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0 }

    /* ===== TICKER ===== */
    .notice-ticker{
      --ticker-pad-y: 8px; --ticker-speed-pps: 95;
      position:relative; color:#f8fafc; background:rgba(2,6,23,.85);
      border-top:1px solid rgba(255,255,255,.12); box-shadow:0 -10px 24px rgba(0,0,0,.25);
      backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); white-space:nowrap;
      height:var(--ticker-h);
    }
    .notice-ticker--fixed{
      position:fixed; left:0; right:0; bottom:0; z-index:9980;
      display:flex; align-items:center; padding:0 0;
    }
    .notice-ticker--fixed::before{
      content:""; position:absolute; left:0; right:0; top:0; height:2px;
      background:linear-gradient(90deg,#22c55e,#06b6d4,#f59e0b); opacity:.8;
    }
    .ticker-track{ display:inline-block; will-change:transform; padding-left:100%; animation:tickerScroll var(--ticker-duration,30s) linear infinite; }
    .notice-ticker:hover .ticker-track{ animation-play-state:paused; }
    .t-item{ display:inline-block; font-weight:800; letter-spacing:.02em; padding:var(--ticker-pad-y) 0; margin-right:36px; }
    .t-sep{ opacity:.5; margin:0 16px }
    .notice-ticker,.notice-ticker .t-item{ font-size:clamp(12px,1.9vw,15px) }
    @media (prefers-color-scheme:light){ .notice-ticker{ background:rgba(15,23,42,.78); color:#fff } }
    @media (prefers-reduced-motion:reduce){ .ticker-track{ animation:none!important; padding-left:0!important } }
    @keyframes tickerScroll{ from{transform:translateX(0)} to{transform:translateX(var(--ticker-translate,-100%))} }

    /* ===== COMPACT MODE (tidak diubah) ===== */
    body.compact .wrapper.curved{ --curve-h:160px }
    body.compact .hero-title{ padding:10px 0 6px }
    body.compact .hero-title .text{ letter-spacing:.02em; font-size:clamp(14px,2.4vw,22px) }
    body.compact .hero-title .accent{ margin-top:6px }
    body.compact .card-box{ padding-top:38px!important; padding-bottom:46px!important }
    body.compact .meja-ribbon span{ font-size:24px; padding:4px 10px }
    body.compact .book-count{ top:8px; right:8px; padding:2px 8px; font-size:13px }
    body.compact .day-row{ gap:6px; margin:8px 0 }
    body.compact .cal-ava{ width:40px; flex:0 0 40px }
    body.compact .cal-tile{ width:34px; height:34px; border-radius:8px }
    body.compact .cal-tile .cal-head{ height:10px; line-height:10px; font-size:8px }
    body.compact .cal-tile .cal-day{ font-size:13px }
    body.compact .cal-cap{ font-size:9px }
    body.compact .conversation-list .ctext-wrap{ padding:.45rem .55rem; border-radius:10px }
    body.compact .ct-head{ gap:6px }
    body.compact .ct-left{ font-size:16px }
    body.compact .tz{ font-size:10px; margin-left:4px }
    body.compact .status-pill{ padding:2px 8px; font-size:14px }
    body.compact .verify-pill{ padding:2px 8px; font-size:10px }
    body.compact .ct-meta{ font-size:11px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis }
    body.compact hr{ margin:8px 0!important }
    body.compact .live-clock{ min-width:160px; padding:10px 12px }
    body.compact .live-clock .lc-time{ font-size:clamp(18px,3.6vw,30px) }
    body.compact .live-clock .lc-date{ font-size:11px }

    /* ===== LIVE CLOCK (tidak diubah) ===== */
    .live-clock{
      position:fixed; right:16px;
      z-index:10060; background:rgba(2,6,23,.72); color:#fff; padding:12px 14px;
      border-radius:14px; border:1px solid rgba(255,255,255,.08);
      box-shadow:0 10px 24px rgba(0,0,0,.25); backdrop-filter:blur(6px); -webkit-backdrop-filter:blur(6px);
      min-width:180px;
    }
    @media (prefers-color-scheme:light){ .live-clock{ background:rgba(15,23,42,.62) } }
    .live-clock .lc-time{ font-weight:900; letter-spacing:.03em; line-height:1; font-size:clamp(22px,4.5vw,40px) }
    .live-clock .lc-date{ margin-top:4px; font-size:12px; font-weight:600; opacity:.9; white-space:nowrap }
    .live-clock .lc-badge{ position:absolute; top:-8px; left:8px; background:#16a34a; color:#fff; padding:2px 8px; border-radius:999px; font-size:11px; font-weight:800; letter-spacing:.04em; border:1px solid rgba(255,255,255,.15) }

    /* ===== FS CTA & BUTTON (tidak diubah) ===== */
    .fs-cta{ position:fixed; inset:0; background:rgba(0,0,0,.6); display:flex; align-items:center; justify-content:center; z-index:10060 }
    .fs-cta__inner{ text-align:center; background:rgba(17,24,39,.88); border:1px solid rgba(255,255,255,.08); padding:20px 18px; border-radius:12px; color:#e5e7eb; max-width:92% }
    .fs-cta__inner .emoji{ font-size:36px; margin-bottom:8px }
    .fs-cta__inner h3{ font-size:18px; margin:6px 0; color:#fff }
    .fs-cta__inner p{ font-size:13px; margin:0 0 12px; color:#cbd5e1 }
    .fs-cta__inner .hint{ font-size:12px; opacity:.8; margin-top:8px }
    .fs-cta__inner .btn-cta{ padding:8px 14px; border-radius:8px; background:#10b981; color:#fff; border:0; font-weight:600; cursor:pointer }
    .fs-cta__inner .btn-cta:active{ transform:translateY(1px) }

    .fs-btn{
      position:fixed; right:12px;
      z-index:10060; width:38px; height:38px; border-radius:10px; border:1px solid rgba(0,0,0,.12);
      background:#111827; color:#e5e7eb; cursor:pointer; display:grid; place-items:center; box-shadow:0 4px 16px rgba(0,0,0,.25);
    }
    .fs-btn .icon-exit{ display:none }
    .fs-btn.active .icon-enter{ display:none }
    .fs-btn.active .icon-exit{ display:inline }
    .fs-btn:hover{ filter:brightness(1.05) }

    /* ===== PIN JAM + TOMBOL DI ATAS TICKER (tidak diubah) ===== */
    body.has-fixed-ticker #liveClock{
      top:auto !important; left:auto !important; right:16px !important;
      bottom: calc(var(--ticker-h) + var(--safe-bottom) + env(safe-area-inset-bottom, 0px)) !important;
    }
    body:not(.has-fixed-ticker) #liveClock{
      top:auto !important; left:auto !important; right:16px !important;
      bottom: calc(var(--safe-bottom) + env(safe-area-inset-bottom, 0px)) !important;
    }
    body.has-fixed-ticker .fs-btn{
      top:auto !important; left:auto !important; right:12px !important;
      bottom: calc(var(--ticker-h) + var(--safe-bottom) + 56px + env(safe-area-inset-bottom, 0px)) !important;
    }
    body:not(.has-fixed-ticker) .fs-btn{
      top:auto !important; left:auto !important; right:12px !important;
      bottom: calc(var(--safe-bottom) + 56px + env(safe-area-inset-bottom, 0px)) !important;
    }

    /* ===== LAYOUT AMAN DENGAN TICKER (tidak diubah) ===== */
    body.has-fixed-ticker .wrapper.curved{
      padding-bottom: calc(var(--ticker-h) + var(--safe-bottom) + var(--clock-h) + 16px);
      min-height: 100svh;
      box-sizing: border-box;
    }
  </style>
<style>
  /* ===== FORCE LANDSCAPE OVERLAY ===== */
  .rotate-guard{
    position:fixed; inset:0; z-index:10090; display:none;
    align-items:center; justify-content:center; text-align:center;
    background:#0b1220; color:#fff; padding:24px;
    border-top:1px solid rgba(255,255,255,.08);
  }
  .rotate-guard .ico{ font-size:64px; line-height:1; margin-bottom:10px; display:block; }
  .rotate-guard h3{ margin:6px 0 8px; font-weight:800; }
  .rotate-guard p{ margin:0; opacity:.9 }

  /* Saat portrait, sembunyikan konten dan tampilkan overlay */
  @media (orientation:portrait){
    body.force-landscape #app-scroll{ display:none !important; }
    body.force-landscape .rotate-guard{ display:flex; }
  }
</style>

  <!-- Tambahan CSS final video (biar prioritas paling bawah stylesheet) -->
  <style>
    body.has-fixed-ticker .empty-right .empty-video{ max-height: none !important; }
  </style>
</head>

<body class="menubar-gradient gradient-topbar topbar-dark compact">

  <!-- Jam live (dipin ke kanan bawah di atas ticker) -->
  <div class="live-clock" id="liveClock" role="timer" aria-live="polite" aria-label="Jam lokal WITA">
    <div class="lc-badge">WITA</div>
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

  <!-- Vendor JS -->
  

  <!-- Jam live WITA -->
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

    function renderCards(cards){
      cards = (cards||[]).slice().sort((a,b)=> (a.meja_id - b.meja_id)); // 1 di kiri, 2 di kanan

  // Hitung total booking dari seluruh cards/days
 function countBookings(list){
  const now = Date.now();
  let n = 0;
  (list||[]).forEach(c=>{
    (c.days||[]).forEach(d=>{
      (d.bookings||[]).forEach(b=>{
        const end = Number(b.end_ts||0);
        if (!end || end > now) n++; // hitung hanya yg belum selesai
      });
    });
  });
  return n;
}


  var trulyEmpty = !Array.isArray(cards) || cards.length===0 || countBookings(cards)===0;

  if (trulyEmpty){
    // ====== EMPTY STATE: kiri teks, kanan video ======
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

  // ====== ADA data booking (kode asli dipertahankan) ======
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
          html +=       '<div class="ct-left">'+esc(b.jam_mulai)+' – '+esc(b.jam_selesai)+' </div> · '+parseInt(b.durasi_jam||0,10)+' jam <span class="tz">WITA</span>';
          html +=       '<div class="ct-right mb-1">';
          html +=         '<span class="status-pill"><span class="status-label">Mulai dalam</span> · <span class="cd">00:00:00</span></span>';
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
  refreshCounters();
}

    // ================== Countdown (format kata Indonesia) ==================
(function(){
  // (Opsional) sisakan pad & fmt lama jika masih dipakai di tempat lain
  function pad(n){return (n<10?'0':'')+n;}

  // Ubah ms -> "X hari Y jam Z menit W dtk"
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

    // Biar ringkas, maksimal 3 unit terbesar
    return parts.slice(0, 3).join(' ');
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
      var cd    = item.querySelector('.cd');
      if(!pill||!label||!cd) return;

      pill.classList.remove('success','muted');
      item.classList.remove('soon','critical');

      if (start && now < start){
        // ===== Belum mulai
        label.textContent = 'Mulai dalam';
        cd.textContent    = fmtWords(start - now); // contoh: "1 jam 2 menit 3 dtk"
      }
      else if (end && now <= end){
        // ===== Sedang bermain
        label.textContent = 'Sedang bermain';
        cd.textContent    = 'Sisa Waktu ' + fmtWords(end - now) + '.'; // contoh: "Sisa Waktu 1 jam 2 menit 3 dtk."
        pill.classList.add('success');

        var left = end - now;
        if (left > 0 && left <= FIVE_MIN_MS){
          item.classList.add('soon');
          if (left <= ONE_MIN_MS) item.classList.add('critical');
          if (!item.dataset.beep5){
            try{ playSound(); }catch(e){}
            item.dataset.beep5 = '1';
          }
        } else {
          delete item.dataset.beep5;
        }
      }
      else{
        // ===== Selesai
        label.textContent = 'Selesai';
        cd.textContent    = '00:00:00';
        pill.classList.add('muted');
        delete item.dataset.beep5;

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

  // jalan tiap detik
  setInterval(runCountdown, 1000);
})();
    // ================== Doorbell beep (tidak diubah) ==================
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

    // ================== Ping loop (tidak diubah) ==================
    const last = { bil: { total:null, max_id:null, last_ts:null } };
    const BASE_INTERVAL = 10000, HIDDEN_INTERVAL = 20000;
    let errorStreak = 0, ticking = false;

    async function safeFetch(url){
      const r = await fetch(url, { cache:'no-store', credentials:'same-origin' });
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

    // kickoff
    loop();
    document.addEventListener('visibilitychange', function(){
      if (!document.hidden) setTimeout(loop, 200);
    });

    // sweep elemen untuk efek radar
    (function(){
      if (liveDot && !liveDot.querySelector('.sweep')){
        var s = document.createElement('i'); s.className = 'sweep'; liveDot.appendChild(s);
      }
    })();
  })();
  </script>

  <!-- Fullscreen toggle + WakeLock (tidak diubah) -->
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

    // CTA
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

    // Tombol mengambang
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

    /* Wake Lock layar */
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

  <!-- Ticker rebuild + flag has-fixed-ticker (tidak diubah) -->
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

  <!-- Auto reload berkala (tidak diubah) -->
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

  <!-- Hitung tinggi hero untuk batas video -->
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

  // === Hitung ulang jumlah booking aktif & fallback per-hari ===
function refreshCounters(){
  const now = Date.now();

  document.querySelectorAll('#cardsRow .card-box').forEach(function(card){
    // --- total booking aktif pada card (upcoming / sedang bermain) ---
    let active = 0;
    card.querySelectorAll('.booking-item').forEach(function(li){
      const end = parseInt(li.getAttribute('data-end-ts'),10)||0;
      if (!end || now <= end) active++; // hitung yg belum selesai
    });
    const bc = card.querySelector('.book-count');
    if (bc) bc.textContent = active + ' booking';

    // --- fallback "Belum ada booking..." per hari ---
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
  <script>
(function(){
  // ====== Coba kunci orientasi ke landscape (saat fullscreen) ======
  async function tryLockLandscape(){
    try{
      if (screen.orientation && screen.orientation.lock){
        await screen.orientation.lock('landscape');
      }
    }catch(e){
      // Diamkan; beberapa browser/iOS memang tidak mendukung
    }
  }

  // Kunci lagi setiap kali status fullscreen berubah (masuk/keluar)
  ['fullscreenchange','webkitfullscreenchange','msfullscreenchange'].forEach(function(evt){
    document.addEventListener(evt, tryLockLandscape);
  });

  // Prime sekali pada interaksi pertama (biar tetap dianggap user-gesture)
  document.addEventListener('pointerdown', function once(){
    tryLockLandscape();
    document.removeEventListener('pointerdown', once, true);
  }, { once:true, capture:true });

  // ====== Overlay: tampilkan jika device sedang portrait ======
  function isLandscape(){ return window.matchMedia('(orientation: landscape)').matches; }
  function applyGuard(){
    // Bila tidak landscape -> paksa tampilkan overlay & sembunyikan konten
    document.body.classList.toggle('force-landscape', !isLandscape());
  }
  applyGuard();
  window.addEventListener('orientationchange', applyGuard);
  window.addEventListener('resize', applyGuard);

  // Jika kamu ingin extra-strong: kunci ulang setelah 300ms usai fullscreen
  document.addEventListener('fullscreenchange', function(){
    setTimeout(tryLockLandscape, 300);
  });
})();
</script>

</body>
</html>
