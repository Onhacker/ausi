<style>
/* SVG Promo Banner — mobile: square-ish, desktop: wide */
.svg-promo{
  position:fixed;
  left:50%;
  bottom:calc(168px + env(safe-area-inset-bottom)); /* naik biar aman dari nav & FAB */
  transform:translate(-50%, 16px) scale(.98);
  width:min(92vw, 380px);          /* HAMPIR PERSEGI di mobile */
  z-index:1050;
  opacity:0;
  transition:transform .35s cubic-bezier(.2,.8,.2,1), opacity .35s;
  pointer-events:auto;
  will-change: transform, opacity;
  touch-action: manipulation;
  -webkit-backface-visibility: hidden; /* iOS compositor hint */
}
.svg-promo.show{ opacity:1; transform:translate(-50%, 0) scale(1); }
.svg-btn:hover{ filter:brightness(1.05); }
.svg-link { text-decoration: underline; cursor: pointer; }
.svg-hit  { cursor:pointer; }

/* Tampilkan hanya 1 SVG sesuai viewport */
#ptsSvgBanner .is-mobile  { display:block; }
#ptsSvgBanner .is-desktop { display:none; }

/* Desktop/tablet: pakai banner lebar seperti biasa */
@media (min-width:576px){
  .svg-promo{
    bottom:calc(32px + env(safe-area-inset-bottom));
    width:min(820px, calc(100vw - 48px));
  }
  #ptsSvgBanner .is-mobile  { display:none; }
  #ptsSvgBanner .is-desktop { display:block; }
}
</style>

<!-- SVG Promo Banner -->
<div id="ptsSvgBanner" class="svg-promo" hidden>
  <!-- ====== MOBILE SQUARE (±360x400) ====== -->
  <svg class="is-mobile" viewBox="0 0 360 400" width="100%" role="dialog" aria-label="Promo Jumat Berkah 10%">
    <defs>
      <!-- BACKGROUND HIJAU (mobile) -->
      <linearGradient id="bgGradM" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0%" stop-color="#064e3b"/>
        <stop offset="100%" stop-color="#16a34a"/>
      </linearGradient>
      <linearGradient id="goldM" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stop-color="#facc15"/><stop offset="100%" stop-color="#eab308"/>
      </linearGradient>
      <!-- HIJAU untuk tombol OK (mobile) -->
      <linearGradient id="btnBlueM" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0%" stop-color="#bbf7d0"/>
        <stop offset="100%" stop-color="#16a34a"/>
      </linearGradient>
      <!-- Light glass -->
      <linearGradient id="btnLiteM" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0%" stop-color="#ffffff" stop-opacity=".18"/>
        <stop offset="100%" stop-color="#ffffff" stop-opacity=".22"/>
      </linearGradient>
      <filter id="shadowM" x="-20%" y="-20%" width="140%" height="140%" color-interpolation-filters="sRGB">
        <feDropShadow dx="0" dy="8" stdDeviation="10" flood-color="#000000" flood-opacity=".25"/>
      </filter>
      <symbol id="coinM" viewBox="-12 -2 24 14">
        <ellipse cx="0" cy="-2" rx="12" ry="5" fill="url(#goldM)"/>
        <rect   x="-12" y="-2" width="24" height="10" rx="5" fill="url(#goldM)"/>
        <ellipse cx="0" cy="8" rx="12" ry="5" fill="url(#goldM)"/>
      </symbol>
    </defs>

    <!-- Card -->
    <g filter="url(#shadowM)">
      <rect x="4" y="4" rx="18" ry="18" width="352" height="392" fill="url(#bgGradM)"/>
    </g>

    <!-- Ornamen islami: bulan sabit + bintang -->
    <g transform="translate(300,66)" opacity=".65">
      <path d="M0,-16 A16,16 0 1,0 0,16 A11,16 0 1,1 0,-16" fill="#facc15"/>
      <path d="M0,-8 L1.8,-2.4 L7.2,-2.4 L2.8,0.8 L4.4,6 L0,-1 L-4.4,6 L-2.8,0.8 L-7.2,-2.4 L-1.8,-2.4 Z"
            fill="#fef9c3"/>
    </g>

    <!-- Siluet masjid tipis di bawah -->
    <g transform="translate(190,260)" opacity=".18">
      <rect x="0" y="28" width="40" height="32" rx="2" fill="#022c22"/>
      <path d="M20,0 Q6,10 6,24 H34 Q34,10 20,0 Z" fill="#022c22"/>
      <rect x="48" y="24" width="26" height="36" rx="2" fill="#022c22"/>
      <path d="M61,4 Q53,10 53,18 H69 Q69,10 61,4 Z" fill="#022c22"/>
      <rect x="96" y="30" width="32" height="30" rx="2" fill="#022c22"/>
      <path d="M112,10 Q102,16 102,26 H122 Q122,16 112,10 Z" fill="#022c22"/>
      <rect x="76" y="0" width="4" height="40" fill="#022c22"/>
      <circle cx="78" cy="-4" r="4" fill="#022c22"/>
    </g>

    <!-- Close -->
    <g id="btnClose" class="svg-hit" transform="translate(328,20)">
      <circle r="12" fill="#ffffff" fill-opacity=".18" stroke="#ffffff" stroke-opacity=".45" stroke-width="1.5"/>
      <path d="M -5,-5 L 5,5 M -5,5 L 5,-5" stroke="#fff" stroke-width="2.4" stroke-linecap="round"/>
    </g>

    <!-- Voucher badge -->
    <g transform="translate(20,28)">
      <path d="M0,20 a10,10 0 0 1 10,-10 h230 a10,10 0 0 1 10,10 v10 a10,10 0 0 0 0,22 v10 a10,10 0 0 1 -10,10 h-230 a10,10 0 0 1 -10,-10 z"
            fill="#ffffff" fill-opacity=".18" stroke="#ffffff" stroke-opacity=".25"/>
      <g transform="translate(208,38)">
        <ellipse cx="0" cy="0" rx="16" ry="7" fill="url(#goldM)" opacity=".95"/>
        <rect x="-16" y="0" width="32" height="10" rx="5" fill="url(#goldM)"/>
        <ellipse cx="0" cy="10" rx="16" ry="7" fill="url(#goldM)"/>
      </g>
      <!-- Coins animation (mobile) -->
      <g transform="translate(208,38) scale(.95)">
        <use href="#coinM">
          <animateTransform attributeName="transform" type="translate"
            values="0,0; -6,0; 0,0" dur="1.4s" repeatCount="indefinite"
            keyTimes="0;0.5;1" calcMode="spline"
            keySplines=".25,.1,.25,1;.25,.1,.25,1"/>
          <animateTransform attributeName="transform" type="scale" additive="sum"
            values="1;1.08;1" dur="1.4s" repeatCount="indefinite"
            keyTimes="0;0.5;1" calcMode="spline"
            keySplines=".25,.1,.25,1;.25,.1,.25,1"/>
        </use>
        <use href="#coinM" opacity=".92" transform="translate(12,0)">
          <animateTransform attributeName="transform" type="translate"
            values="12,0; 0,0; 12,0" dur="1.4s" begin=".18s" repeatCount="indefinite"
            keyTimes="0;0.5;1" calcMode="spline"
            keySplines=".25,.1,.25,1;.25,.1,.25,1"/>
          <animateTransform attributeName="transform" type="scale" additive="sum"
            values="1;1.08;1" dur="1.4s" begin=".18s" repeatCount="indefinite"
            keyTimes="0;0.5;1" calcMode="spline"
            keySplines=".25,.1,.25,1;.25,.1,.25,1"/>
        </use>
        <use href="#coinM" opacity=".85" transform="translate(24,0)">
          <animateTransform attributeName="transform" type="translate"
            values="24,0; 12,0; 24,0" dur="1.4s" begin=".36s" repeatCount="indefinite"
            keyTimes="0;0.5;1" calcMode="spline"
            keySplines=".25,.1,.25,1;.25,.1,.25,1"/>
          <animateTransform attributeName="transform" type="scale" additive="sum"
            values="1;1.08;1" dur="1.4s" begin=".36s" repeatCount="indefinite"
            keyTimes="0;0.5;1" calcMode="spline"
            keySplines=".25,.1,.25,1;.25,.1,.25,1"/>
        </use>
      </g>

      <g fill="#fff" opacity=".9" transform="translate(10,4)">
        <path d="M7 0 L9 5 L14 7 L9 9 L7 14 L5 9 L0 7 L5 5 Z"/>
      </g>
      <text x="16" y="30" fill="#fff" font-size="15" font-weight="700">Promo Jumat Berkah</text>
      <text x="16" y="62" fill="#fff" font-size="24" font-weight="800">Potongan 10%</text>
      <!-- <text x="16" y="90" fill="#fff" font-size="16" font-weight="600">Untuk 10 orang pertama</text> -->
    </g>

    <!-- Headline + desc (diturunkan sedikit) -->
    <g transform="translate(20,155)">
      <text x="0" y="0"  fill="#bbf7d0" font-size="15" font-weight="700">
        Promo spesial hari Jumat
      </text>
      <text x="0" y="26" fill="#ffffff" font-size="21" font-weight="800">
        Diskon 10% untuk
      </text>
      <text x="0" y="50" fill="#ffffff" font-size="21" font-weight="800">
        10 orang pertama
      </text>
      <text fill="#e2fbe8" font-size="13">
        <tspan x="0" y="80">Berlaku setiap hari Jumat,</tspan>
        <tspan x="0" y="96">khusus makan &amp; minum di tempat.</tspan>
      </text>
    </g>

    <!-- Footer (mobile) -->
    <g transform="translate(16,292)">
      <rect x="0" y="0" rx="12" ry="12" width="328" height="72" fill="#ffffff" fill-opacity=".10" stroke="#ffffff" stroke-opacity=".18"/>

      <!-- Pindah ke dalam footer supaya tidak tumpang tindih -->
      <g id="linkReward" class="svg-link" transform="translate(12,18)">
        <text x="0" y="0" fill="#ffffff" font-size="13">
          Lihat cara dapat voucher
          <tspan font-weight="700" text-decoration="underline"> di sini</tspan>
        </text>
      </g>

      <g id="toggleNoShow" class="svg-hit" transform="translate(12,38)">
        <rect x="0" y="-11" rx="9" ry="9" width="24" height="24" fill="url(#btnLiteM)" stroke="#ffffff" stroke-opacity=".4"/>
        <path id="chkMark" d="M4,0 L9,6 L19,-8" stroke="#fff" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round" opacity="0"/>
        <text x="32" y="6" fill="#ffffff" font-size="13">Jangan tampilkan lagi hari ini</text>
      </g>

      <g id="linkTerms" class="svg-link" transform="translate(12,58)">
        <text x="0" y="8" fill="#ffffff" font-size="12" text-decoration="underline">Baca Selengkapnya S&amp;K </text>
      </g>

      <g id="btnOK" class="svg-btn svg-hit" transform="translate(252,16)">
        <rect x="0" y="0" rx="12" ry="12" width="64" height="40" fill="url(#btnBlueM)" stroke="#ffffff" stroke-opacity=".4"/>
        <text x="32" y="25" text-anchor="middle" fill="#ffffff" font-size="14" font-weight="700">OK</text>
      </g>
    </g>
  </svg>

  <!-- ====== DESKTOP WIDE ====== -->
  <svg class="is-desktop" viewBox="0 0 820 260" width="100%" role="dialog" aria-label="Promo Jumat Berkah 10%">
    <defs>
      <!-- BACKGROUND HIJAU (desktop) -->
      <linearGradient id="bgGradD" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0%" stop-color="#064e3b"/>
        <stop offset="100%" stop-color="#16a34a"/>
      </linearGradient>
      <linearGradient id="goldD" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stop-color="#facc15"/><stop offset="100%" stop-color="#eab308"/>
      </linearGradient>
      <!-- HIJAU untuk tombol OK (desktop) -->
      <linearGradient id="btnBlueD" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0%" stop-color="#bbf7d0"/>
        <stop offset="100%" stop-color="#16a34a"/>
      </linearGradient>
      <linearGradient id="btnLiteD" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0%" stop-color="#ffffff" stop-opacity=".18"/>
        <stop offset="100%" stop-color="#ffffff" stop-opacity=".22"/>
      </linearGradient>
      <filter id="shadowD" x="-20%" y="-20%" width="140%" height="140%" color-interpolation-filters="sRGB">
        <feDropShadow dx="0" dy="8" stdDeviation="10" flood-color="#000000" flood-opacity=".25"/>
      </filter>
      <symbol id="coinD" viewBox="-12 -2 24 14">
        <ellipse cx="0" cy="-2" rx="12" ry="5" fill="url(#goldD)"/>
        <rect   x="-12" y="-2" width="24" height="10" rx="5" fill="url(#goldD)"/>
        <ellipse cx="0" cy="8" rx="12" ry="5" fill="url(#goldD)"/>
      </symbol>
    </defs>

    <g filter="url(#shadowD)">
      <rect x="4" y="4" rx="20" ry="20" width="812" height="252" fill="url(#bgGradD)"/>
    </g>

    <!-- Ornamen bulan sabit + bintang (desktop) -->
    <g transform="translate(720,70)" opacity=".7">
      <path d="M0,-20 A20,20 0 1,0 0,20 A14,20 0 1,1 0,-20" fill="#facc15"/>
      <path d="M0,-9 L2.2,-3 L8,-3 L3.2,0.9 L4.8,7 L0,-1.2 L-4.8,7 L-3.2,0.9 L-8,-3 L-2.2,-3 Z"
            fill="#fef9c3"/>
    </g>

    <g id="btnClose" class="svg-hit" transform="translate(774,18)">
      <circle r="13" fill="#ffffff" fill-opacity=".18" stroke="#ffffff" stroke-opacity=".45" stroke-width="1.5"/>
      <path d="M -5,-5 L 5,5 M -5,5 L 5,-5" stroke="#fff" stroke-width="2.4" stroke-linecap="round"/>
    </g>

    <g transform="translate(24,22)">
      <path d="M0,22 a12,12 0 0 1 12,-12 h260 a12,12 0 0 1 12,12 v10 a12,12 0 0 0 0,24 v10 a12,12 0 0 1 -12,12 h-260 a12,12 0 0 1 -12,-12 z"
            fill="#ffffff" fill-opacity=".18" stroke="#ffffff" stroke-opacity=".25"/>
      <!-- Coins animation (desktop) -->
      <g transform="translate(232,44)">
        <use href="#coinD">
          <animateTransform attributeName="transform" type="translate"
            values="0,0; -8,0; 0,0" dur="1.6s" repeatCount="indefinite"
            keyTimes="0;0.5;1" calcMode="spline"
            keySplines=".25,.1,.25,1;.25,.1,.25,1"/>
          <animateTransform attributeName="transform" type="scale" additive="sum"
            values="1;1.08;1" dur="1.6s" repeatCount="indefinite"
            keyTimes="0;0.5;1" calcMode="spline"
            keySplines=".25,.1,.25,1;.25,.1,.25,1"/>
        </use>
        <use href="#coinD" opacity=".92" transform="translate(14,0)">
          <animateTransform attributeName="transform" type="translate"
            values="14,0; 2,0; 14,0" dur="1.6s" begin=".2s" repeatCount="indefinite"
            keyTimes="0;0.5;1" calcMode="spline"
            keySplines=".25,.1,.25,1;.25,.1,.25,1"/>
          <animateTransform attributeName="transform" type="scale" additive="sum"
            values="1;1.08;1" dur="1.6s" begin=".2s" repeatCount="indefinite"
            keyTimes="0;0.5;1" calcMode="spline"
            keySplines=".25,.1,.25,1;.25,.1,.25,1"/>
        </use>
        <use href="#coinD" opacity=".85" transform="translate(28,0)">
          <animateTransform attributeName="transform" type="translate"
            values="28,0; 16,0; 28,0" dur="1.6s" begin=".4s" repeatCount="indefinite"
            keyTimes="0;0.5;1" calcMode="spline"
            keySplines=".25,.1,.25,1;.25,.1,.25,1"/>
          <animateTransform attributeName="transform" type="scale" additive="sum"
            values="1;1.08;1" dur="1.6s" begin=".4s" repeatCount="indefinite"
            keyTimes="0;0.5;1" calcMode="spline"
            keySplines=".25,.1,.25,1;.25,.1,.25,1"/>
        </use>
      </g>

      <text x="20" y="44" fill="#fff" font-size="18" font-weight="700">Promo Jumat Berkah</text>
      <text x="20" y="76" fill="#fff" font-size="24" font-weight="800">Diskon 10% - 10 orang pertama</text>
    </g>

    <!-- Headline + desc desktop (diturunkan sedikit) -->
    <g transform="translate(24,130)">
      <text x="0" y="0"  fill="#bbf7d0" font-size="15" font-weight="700">Promo spesial hari Jumat</text>
      <text x="0" y="30" fill="#ffffff" font-size="24" font-weight="800">Diskon 10% untuk 10 orang pertama</text>
      <text fill="#e2fbe8" font-size="13">
        <tspan x="0" y="60">Berlaku setiap hari Jumat,</tspan>
        <tspan x="0" y="76">khusus makan &amp; minum di tempat.</tspan>
      </text>
    </g>

    <!-- Footer (desktop) -->
    <g transform="translate(20,204)" id="footerDesktop">
      <rect x="0" y="0" rx="12" ry="12" width="780" height="52" fill="#ffffff" fill-opacity=".10" stroke="#ffffff" stroke-opacity=".18"/>
      <g id="linkTerms" class="svg-link" transform="translate(12,34)">
        <text x="0" y="0" fill="#ffffff" font-size="14" text-decoration="underline">Syarat &amp; Ketentuan berlaku</text>
      </g>
      <g id="toggleNoShow" class="svg-hit" transform="translate(356,30)">
        <rect x="0" y="-13" rx="10" ry="10" width="26" height="26" fill="url(#btnLiteD)" stroke="#ffffff" stroke-opacity=".4"/>
        <path id="chkMark" d="M5,1 L11,8 L21,-8" stroke="#fff" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round" opacity="0"/>
        <text x="36" y="6" fill="#ffffff" font-size="13">Jangan tampilkan lagi hari ini</text>
      </g>
      <g id="btnOK" class="svg-btn svg-hit" transform="translate(700,6)">
        <rect x="0" y="0" rx="14" ry="14" width="72" height="40" fill="url(#btnBlueD)" stroke="#ffffff" stroke-opacity=".4"/>
        <text x="36" y="26" text-anchor="middle" fill="#ffffff" font-size="14" font-weight="700">OK</text>
      </g>
    </g>
  </svg>
</div>

<script>
(function(){
  var wrap = document.getElementById('ptsSvgBanner');
  if(!wrap) return;

  var now = new Date();

  // fungsi kecil untuk dapat key per-hari, misalnya: 20251120
  function getDayKey(d){
    var y  = d.getFullYear();
    var m  = String(d.getMonth()+1).padStart(2,'0');
    var da = String(d.getDate()).padStart(2,'0');
    return '' + y + m + da;
  }

  // prefix beda agar tidak tabrakan dengan promo mingguan
  var key = 'ausi_jumat_berkah_' + getDayKey(now);

  try { if(localStorage.getItem(key)==='hide'){ wrap.remove(); return; } } catch(e){}

  wrap.hidden = false;
  requestAnimationFrame(function(){ wrap.classList.add('show'); });

  // Ambil SVG yang sedang tampil (bukan display:none)
  var svgs = Array.from(wrap.querySelectorAll('svg'));
  var svg  = svgs.find(function(s){ return getComputedStyle(s).display !== 'none'; }) || svgs[0];

  var btnOK     = svg.getElementById('btnOK');
  var btnClose  = svg.getElementById('btnClose');
  var linkTerms = svg.getElementById('linkTerms');
  var toggle    = svg.getElementById('toggleNoShow');
  var chkMark   = svg.getElementById('chkMark');
  var linkReward = svg.getElementById('linkReward');

  var persist   = false;

  function closeBanner(save){
    wrap.classList.remove('show');
    setTimeout(function(){
      if(save){ try{ localStorage.setItem(key, 'hide'); }catch(e){} }
      wrap.remove();
    }, 260);
  }

  if (toggle && chkMark) {
    toggle.addEventListener('click', function(){
      persist = !persist;
      chkMark.setAttribute('opacity', persist ? '1' : '0');
    });
  }
  if (linkTerms) linkTerms.addEventListener('click', function(){
    window.location.href = "<?= site_url('hal/#voucher-order'); ?>";
  });
  if (linkReward) linkReward.addEventListener('click', function(){
    window.location.href = "<?= site_url('hal/detail_pengumuman/ausi-jumat-berkah'); ?>";
  });
  if (btnOK)    btnOK.addEventListener('click',   function(){ closeBanner(persist); });
  if (btnClose) btnClose.addEventListener('click',function(){ closeBanner(false); });

  document.addEventListener('keydown', function(e){
    if(e.key==='Escape'){ closeBanner(false); }
  });
})();
</script>
