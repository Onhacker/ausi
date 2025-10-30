<style>
/* ================== VARIABEL GLOBAL ================== */
:root{
  /* Section & card */
  --sec-bg:#ffffff;
  --card-bg:#ffffff;
  --card-shadow:0 12px 28px rgba(2,6,23,.08);
  --title:#0b1323;
  --muted:#64748b;

  /* Accent */
  --active:#0ea5e9;

  /* Slider fitur */
  --fs-gap:16px;
  --fs-card-w: clamp(260px, 78vw, 340px);

  /* Ukuran font fitur */
  --fs-title-min:16px;
  --fs-title-vw:3.4vw;
  --fs-title-max:20px;
  --fs-body:13.5px;

  /* Judul section */
  --sec-title:22px;
  --sec-link:13px;
}

/* ================== HERO TITLE ================== */
.hero-title{ padding:14px 0 6px; text-align:center; }
.hero-title .text{
  display:inline-block; margin:0;
  font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;
  font-weight:900; letter-spacing:.04em; text-transform:uppercase;
  line-height:1.1; font-size:clamp(18px,5vw,28px);
}
.hero-title .accent{
  display:block; height:3px; margin:8px auto 0; width:min(420px,80%);
  border-radius:999px; background:linear-gradient(90deg,#dd7634,#ffffff);
}

/* ================== HERO SLIDESHOW ================== */
.pwa-hero{position:relative;border-radius:16px;overflow:hidden;box-shadow:0 14px 34px rgba(0,0,0,.12);background:#000;margin:8px 0 14px}
.pwa-hero__track{display:flex;overflow-x:auto;overflow-y:hidden;scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch;scrollbar-width:none}
.pwa-hero__track::-webkit-scrollbar{display:none}
.pwa-hero__slide{flex:0 0 100%;position:relative;scroll-snap-align:center;height:clamp(200px,32vw,420px);background:#111}
.pwa-hero__link{display:block;height:100%;color:inherit;text-decoration:none}
.pwa-hero__img{width:100%;height:100%;object-fit:cover;display:block;filter:saturate(1.05) contrast(1.02)}
.pwa-hero__cap{position:absolute;inset:auto 0 0 0;padding:18px 18px 16px;color:#fff;background:linear-gradient(180deg,rgba(0,0,0,0) 0%,rgba(0,0,0,.55) 48%,rgba(0,0,0,.75) 100%)}
.pwa-hero__title{margin:0 0 4px;font-weight:800;color:#fff;font-size:clamp(16px,1.8vw,22px);letter-spacing:.2px}
.pwa-hero__text{margin:0;font-size:clamp(13px,1.4vw,16px);opacity:.95}
.pwa-hero__nav{position:absolute;top:50%;transform:translateY(-50%);width:42px;height:42px;border-radius:50%;border:0;cursor:pointer;background:rgba(255,255,255,.95);color:#111;font-size:22px;line-height:1;display:flex;align-items:center;justify-content:center;z-index:3;box-shadow:0 10px 28px rgba(0,0,0,.18)}
.pwa-hero__nav.prev{left:10px}.pwa-hero__nav.next{right:10px}
.pwa-hero__nav[disabled]{opacity:.35;cursor:default}
.pwa-hero__dots{position:absolute;left:0;right:0;bottom:8px;display:flex;gap:8px;justify-content:center;z-index:2}
.pwa-hero__dots button{width:8px;height:8px;border-radius:50%;border:0;background:rgba(255,255,255,.45);padding:0;cursor:pointer}
.pwa-hero__dots button[aria-current="true"]{background:#fff;transform:scale(1.15)}
@media (prefers-reduced-motion:reduce){.pwa-hero__track{scroll-behavior:auto}}

/* ================== QUICK MENU ================== */
.menu-circle{width:100px;height:100px;font-size:28px}
.menu-label{font-size:14px}
@media(max-width:576px){.menu-circle{width:60px;height:60px;font-size:18px}.menu-label{font-size:9px}}
.emoji-icon{font-size:40px}
@media(min-width:576px){.emoji-icon{font-size:60px}}
@media(min-width:768px){.emoji-icon{font-size:60px}}

.quickmenu-wrap{margin:8px 0 6px;--btn:48px}
.quickmenu-scroll{gap:12px;padding:6px 8px 10px;overflow-x:auto;overflow-y:hidden;scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch;scrollbar-width:none}
.quickmenu-scroll::-webkit-scrollbar{display:none}
.quickmenu-item{flex:0 0 auto;width:clamp(120px,25vw,180px);scroll-snap-align:start}
.qcard{padding:10px 8px;border-radius:14px;background:#fff;box-shadow:0 6px 18px rgba(0,0,0,.06);border:1px solid #eef2f7;transition:transform .15s ease,box-shadow .2s ease;color:#111}
.qcard:hover{transform:translateY(-2px);box-shadow:0 10px 24px rgba(0,0,0,.08)}
.menu-circle{width:72px;height:72px;border-radius:50%;margin:0 auto;display:flex;align-items:center;justify-content:center;color:#fff;box-shadow:0 8px 18px rgba(0,0,0,.12)}
.emoji-icon{font-size:30px;line-height:1}
.menu-label{display:block;margin-top:8px;font-weight:700;color:#1f2937;letter-spacing:.2px}
.quickmenu-btn{position:absolute;top:50%;transform:translateY(-50%);width:var(--btn);height:var(--btn);border-radius:50%;border:none;background:rgba(255,255,255,.9);box-shadow:0 6px 18px rgba(0,0,0,.12);cursor:pointer;z-index:3;font-size:20px;line-height:1;display:flex;align-items:center;justify-content:center;transition:opacity .2s ease,transform .2s ease}
.quickmenu-btn.left{left:4px}.quickmenu-btn.right{right:4px}
.quickmenu-btn[disabled]{opacity:.35;cursor:default}
.quickmenu-fade{pointer-events:none;position:absolute;top:0;bottom:0;width:32px;z-index:2}
.quickmenu-fade.left{left:0;background:linear-gradient(90deg,#fff,rgba(255,255,255,0))}
.quickmenu-fade.right{right:0;background:linear-gradient(270deg,#fff,rgba(255,255,255,0))}
@media (min-width: 992px){ .quickmenu-btn,.quickmenu-fade{ display:none !important; } }
@media (max-width: 576px){ .quickmenu-wrap{ --btn:25px; } .quickmenu-btn{ font-size:14px; } }

/* ================== FITUR (SLIDER KARTU) ================== */
.features-sec{ /* latar/spacing bila perlu, dibiarkan minimal */ }
.features-head h3{
  margin:0; color:var(--title); font-weight:800; letter-spacing:.02em;
  font-size:clamp(18px,3.8vw,var(--sec-title));
}
.features-all{ font-weight:700; text-decoration:none; font-size:var(--sec-link); color:var(--active); }

.feature-slider{ position:relative; z-index:0; /* padding bisa ditambah jika mau ruang untuk tombol */ }
.feature-slider .fs-nav{ z-index:5; pointer-events:auto; }

.fs-track{
  display:grid;
  grid-auto-flow:column; grid-auto-columns: var(--fs-card-w);
  gap: var(--fs-gap);
  overflow-x:auto; scroll-snap-type:x mandatory; scroll-behavior:smooth;
  -webkit-overflow-scrolling:touch;
  padding:4px; margin:0; list-style:none;
  -webkit-mask-image: linear-gradient(90deg, rgba(0,0,0,.08), #000 12%, #000 88%, rgba(0,0,0,.08));
          mask-image: linear-gradient(90deg, rgba(0,0,0,.08), #000 12%, #000 88%, rgba(0,0,0,.08));
}
.fs-track::-webkit-scrollbar{ display:none; }

.fs-track > li.media{
  scroll-snap-align:center;
  background:var(--card-bg);
  border-radius:28px;
  box-shadow:var(--card-shadow);
  padding:20px 18px;
  margin:0;
  display:flex; flex-direction:column;
  min-height:170px;
}
.fs-track > li.media .mr-3{ margin-right:0 !important; margin-bottom:20px; }
.fs-track > li.media .avatar-sm{
  width:56px; height:56px; min-width:56px;
  border-radius:16px; display:flex; align-items:center; justify-content:center;
  box-shadow:0 6px 14px rgba(2,6,23,.10); font-size:22px;
}
.fs-track > li.media h4{
  margin:4px 0 6px;
  text-align: center;
  font-size: 20px;
  font-weight:800; color:var(--title);
}
.fs-track > li.media p{ margin:0; line-height:1.5; color:black; /*font-size:var(--fs-body*/ text-align: center;); }

.avatar-sm{
  width:56px; height:56px;
  display:grid;                 /* atau flex juga boleh */
  place-items:center;           /* center hor+ver */
  line-height:0;                /* hilangkan offset line-height */
}
.avatar-sm i{ line-height:1; display:block; }
.avatar-sm.rounded-circle{ border-radius:50% !important; }

/* Nav & Dots fitur */
.fs-nav{
  position:absolute; top:50%; transform:translateY(-50%);
  width:36px; height:36px; border:0; border-radius:999px; cursor:pointer;
  display:grid; place-items:center; background:#fff;
  box-shadow:0 10px 24px rgba(2,6,23,.16); font-size:22px; line-height:1; z-index:2;
}
.fs-nav.prev{ left:6px; } .fs-nav.next{ right:6px; }
/*.fs-dots{ display:flex; justify-content:center; gap:8px; margin-top:10px; }
.fs-dot{ width:8px; height:8px; border-radius:999px; background:#cbd5e1; transition:width .25s, background .25s; }
.fs-dot.is-active{ width:22px; background:var(--active); }*/

/* ================== CHART ================== */
#visit-line-chart{ min-height:300px; width:100%; }

/* ================== DARK MODE ================== */
/*@media (prefers-color-scheme:dark){
  :root{ --card-bg:#0f172a; --title:#e5e7eb; --muted:#9aa4b2; }
  .pwa-hero__nav{ background:rgba(15,23,42,.92); color:#fff; }
  .qcard{ background:#0f172a; color:#e5e7eb; border-color:#182032; }
  .quickmenu-btn{ background:rgba(15,23,42,.9); color:#fff; }
}*/
</style>
<style type="text/css">
  /* ===== Testimonial Card ===== */
.t-card{
  --grad-1:#2563eb;  /* biru */
  --grad-2:#0ea5e9;  /* cyan */
  --grad-3:#22d3ee;  /* light cyan */
  position:relative;
  border-radius:26px;
  padding:36px 22px 28px;
  color:#fff;
  text-align:center;
  min-height:420px;
  display:flex;
  flex-direction:column;
  justify-content:center;
  align-items:center;
  gap:12px;
  /* gradasi + highlight lembut */
  background:
    radial-gradient(120% 100% at 100% 0, rgba(255,255,255,.16), transparent 50%),
    linear-gradient(160deg, var(--grad-1) 0%, var(--grad-2) 60%, var(--grad-3) 100%);
  box-shadow:
    0 20px 50px rgba(2,6,23,.15),
    inset 0 1px 0 rgba(255,255,255,.15);
  overflow:hidden;
}

/* overlay foto halus (opsional): set via style="--bg:url(...)" */
.t-card::before{
  content:"";
  position:absolute; inset:0;
  background:var(--bg, none) center/cover no-repeat;
  opacity:.18;                      /* lembut */
  mix-blend-mode:soft-light;
  pointer-events:none;
}

/* teks utama */
.t-quote{
  margin:0;
  font-weight:800;
  letter-spacing:.015em;
  line-height:1.6;
  font-size:clamp(18px,2.6vw,28px);
}

/* sumber/author */
.t-author{
  opacity:.85;
  font-weight:600;
  letter-spacing:.02em;
  color:rgba(255,255,255,.88);
  margin-bottom:8px;
}

/* tombol */
.t-btn{
  display:inline-flex;
  align-items:center; justify-content:center;
  padding:12px 22px;
  border-radius:14px;
  border:2px solid rgba(255,255,255,.9);
  color:#fff; text-decoration:none;
  font-weight:800; letter-spacing:.06em;
  background:rgba(255,255,255,.09);
  backdrop-filter:saturate(120%) blur(6px);
  box-shadow:0 8px 22px rgba(2,6,23,.25);
  transition:transform .2s ease, box-shadow .2s ease, background .2s ease;
}
.t-btn:hover{
  transform:translateY(-2px);
  background:rgba(255,255,255,.18);
  box-shadow:0 14px 36px rgba(2,6,23,.35);
}

/* sudut lembut pada container terang */
@media (prefers-color-scheme:light){
  .t-card{ outline:1px solid rgba(255,255,255,.35); }
}

/* responsif kecil */
@media (max-width:480px){
  .t-card{ min-height:360px; padding:28px 18px; }
}

</style>