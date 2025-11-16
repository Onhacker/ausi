<?php $this->load->view("front_end/front_notif") ?>
<?php if ($this->uri->segment(1) != 'on_login'): ?>
  <script>
  const thisUri = "<?= site_url($this->uri->uri_string()); ?>";
  const shareText = <?= json_encode($rec->nama_website.' '.$rec->kabupaten.'. '.$title) ?>;

  function shareTo(platform){
    const url  = encodeURIComponent(thisUri);
    const text = encodeURIComponent(shareText);
    let shareUrl = "";
    switch(platform){
      case "whatsapp": shareUrl = `https://wa.me/?text=${text}%20${url}`; break;
      case "facebook": shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`; break;
      case "twitter":  shareUrl = `https://twitter.com/intent/tweet?text=${text}&url=${url}`; break;
      case "telegram": shareUrl = `https://t.me/share/url?url=${url}&text=${text}`; break;
      default: alert("Platform tidak didukung"); return;
    }
    window.open(shareUrl, "_blank", "noopener");
  }
  </script>

  <div class="container-fluid mb-5">
    <div class="row">
      <div class="col-12">
        <div class="card-box-carbul">
          <h3 class=" text-center">
            <strong><?= $rec->nama_website ?></strong>
          </h3>

        <!--   <p class="boxed-text-l text-center mb-1">
            <?= $rec->meta_deskripsi ?>
          </p>
 -->
          <div class="text-center">
            Bagikan:
            <div class="share-buttons">
              <button class="btn btn-whatsapp btn-xs" onclick="shareTo('whatsapp')" aria-label="Bagikan via WhatsApp">
                <svg viewBox="0 0 32 32"><path d="M16.003 2.002a14 14 0 00-12.081 20.9l-1.586 5.8 5.954-1.558A14 14 0 1016.003 2zM8.463 24.43l-.35.093.093-.338.618-2.25-.446-.65a11.798 11.798 0 112.007 2.043l-.648-.43-2.28.58.006.003zM23.4 19.7c-.33.93-1.62 1.722-2.215 1.837-.573.11-1.285.16-2.068-.127-.477-.17-1.09-.352-1.894-.692-3.326-1.436-5.514-4.84-5.685-5.07-.17-.23-1.36-1.813-1.36-3.455 0-1.642.86-2.45 1.168-2.788.307-.34.668-.42.89-.42.223 0 .445.002.64.01.206.01.483-.078.756.576.29.682.985 2.353 1.07 2.526.085.17.142.36.028.577-.11.217-.165.35-.33.54-.165.19-.35.43-.5.577-.17.17-.345.357-.15.707.2.352.893 1.47 1.915 2.38 1.317 1.17 2.426 1.537 2.776 1.708.35.17.552.15.755-.092.197-.23.855-.997 1.084-1.34.223-.34.447-.287.76-.17.312.118 1.98.935 2.317 1.102.337.17.56.24.642.37.086.124.086.716-.24 1.647z"/></svg>
              </button>

              <button class="btn btn-facebook btn-xs" onclick="shareTo('facebook')" aria-label="Bagikan ke Facebook">
                <svg viewBox="0 0 24 24"><path d="M22 12.073C22 6.505 17.523 2 12 2S2 6.505 2 12.073C2 17.096 5.656 21.158 10.438 22v-7.01h-3.14v-2.917h3.14V9.845c0-3.1 1.894-4.788 4.659-4.788 1.325 0 2.464.099 2.797.143v3.24l-1.92.001c-1.504 0-1.796.716-1.796 1.767v2.316h3.588l-.467 2.917h-3.12V22C18.344 21.158 22 17.096 22 12.073z"/></svg>
              </button>

              <button class="btn btn-twitter btn-xs" onclick="shareTo('twitter')" aria-label="Bagikan ke Twitter / X">
                <svg viewBox="0 0 24 24"><path d="M23 3a10.9 10.9 0 01-3.14 1.53A4.48 4.48 0 0022.4 1.64a9.03 9.03 0 01-2.88 1.1 4.52 4.52 0 00-7.71 4.12A12.84 12.84 0 013 2.24a4.51 4.51 0 001.39 6.02 4.41 4.41 0 01-2.05-.56v.06a4.52 4.52 0 003.63 4.42 4.52 4.52 0 01-2.04.08 4.53 4.53 0 004.23 3.14A9.05 9.05 0 012 19.54a12.76 12.76 0 006.92 2.03c8.3 0 12.84-6.87 12.84-12.84 0-.2-.01-.39-.02-.58A9.22 9.22 0 0023 3z"/></svg>
              </button>

              <button class="btn btn-telegram btn-xs" onclick="shareTo('telegram')" aria-label="Bagikan ke Telegram">
                <svg viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.372 0 12c0 5.103 3.194 9.426 7.675 11.185.561.104.766-.243.766-.54 0-.266-.01-1.142-.015-2.072-3.124.681-3.787-1.507-3.787-1.507-.511-1.295-1.248-1.64-1.248-1.64-1.02-.698.077-.684.077-.684 1.127.079 1.72 1.158 1.72 1.158 1.003 1.718 2.63 1.222 3.272.934.103-.726.392-1.222.714-1.503-2.494-.284-5.115-1.247-5.115-5.548 0-1.225.438-2.228 1.157-3.014-.116-.285-.5-1.431.108-2.984 0 0 .94-.302 3.08 1.151A10.74 10.74 0 0112 6.845a10.77 10.77 0 012.808.377c2.14-1.453 3.08-1.151 3.08-1.151.609 1.553.225 2.699.11 2.984.72.786 1.156 1.789 1.156 3.014 0 4.31-2.625 5.26-5.126 5.538.403.345.763 1.023.763 2.06 0 1.488-.014 2.688-.014 3.053 0 .299.202.648.772.538A12.005 12.005 0 0024 12c0-6.628-5.373-12-12-12z"/></svg>
              </button>
            </div>

            <?php
              $playPackage = 'id.co.ausi.twa'; // isi dengan package id-mu kalau sudah pasti
              $playUrl     = 'https://play.google.com/store/apps/details?id=' . $playPackage;
            ?>

<style>
  /* Container (biar center dan rapi) */
  .store-badges{display:flex;justify-content:center;align-items:center;gap:10px;flex-wrap:wrap}

  /* === PUNYAMU: tetap === */
  .install-badge{
    display:none; /* disembunyikan, nanti JS yang tentukan */
    align-items:center;background:#000;color:#fff;font-size:16px;line-height:1.2;font-weight:600;
    font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;
    border-radius:999px;padding:10px 14px;text-decoration:none;border:1px solid #000;cursor:pointer
  }
  .install-badge:hover,.install-badge:focus{ text-decoration:none;background:#111;color:#fff }
  .install-badge .install-icon{ display:block;flex-shrink:0;width:24px;height:24px;margin-right:10px }
  .install-badge .install-icon svg{ width:100%;height:100%;fill:#fff;display:block }

  /* Tambahan minimal: style untuk tombol Play (mirip Install, tapi tanpa fill putih) */
  .play-badge{
    display:none; /* disembunyikan, nanti JS yang tentukan */
    align-items:center;background:#000;color:#fff;font-size:16px;line-height:1.2;font-weight:600;
    font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;
    border-radius:999px;padding:10px 14px;text-decoration:none;border:1px solid #000;cursor:pointer
  }
  .play-badge:hover,.play-badge:focus{ text-decoration:none;background:#111;color:#fff }
  .play-badge .play-icon{ display:block;flex-shrink:0;width:24px;height:24px;margin-right:10px }
  .play-badge .play-icon svg{ width:100%;height:100%;display:block } /* biarkan warnanya */

  @media (max-width:380px){
    .install-badge,.play-badge{ font-size:14px;padding:8px 12px }
    .install-badge .install-icon,.play-badge .play-icon{ width:20px;height:20px;margin-right:8px }
  }
</style>

<div class="text-center store-badges mt-2">
  <!-- Google Play (Android only / default non-iOS) -->
  <a id="playStoreButton"
     href="<?= htmlspecialchars($playUrl, ENT_QUOTES, 'UTF-8'); ?>"
     data-play-url="<?= htmlspecialchars($playUrl, ENT_QUOTES, 'UTF-8'); ?>"
     class="play-badge"
     aria-label="Get it on Google Play"
     target="_blank" rel="noopener">
    <span class="play-icon" aria-hidden="true"></span>
    <span class="play-text">Google Play</span>
  </a>

  <!-- Tombol PWA (iOS only atau bila sudah installed → berubah “Ngopi Yuk”) -->
  <a id="installButton"
     href="#"
     class="install-badge"
     aria-label="Install App">
    <span class="install-icon" aria-hidden="true"></span>
    <span class="install-text">Install App</span>
  </a>
</div>

<noscript>
  <div class="text-center" style="margin-top:8px">
    <a href="<?= htmlspecialchars($playUrl, ENT_QUOTES, 'UTF-8'); ?>">Unduh dari Google Play</a>
  </div>
</noscript>



          <!-- </div> -->

          <div class="divider mb-3"></div>

          <div class="row text-center mb-3">
            <a class="col-5 text-nowrap text-blue" href="<?= site_url('hal/privacy_policy') ?>">
              Kebijakan Privasi
            </a>

            <a class="col-2" href="#topnav" onclick="scrollToTop()" aria-label="Kembali ke atas">
              <i class="fas fa-arrow-up" style="color:#4a81d4"></i>
            </a>

            <a class="col-5 text-nowrap text-blue"
               href="<?= site_url('hal') ?>"
               aria-label="Syarat &amp; Ketentuan">
              <span class="d-inline">
                <abbr title="Syarat &amp; Ketentuan">S&amp;K</abbr>
              </span>
              <span class="d-none d-sm-inline text-blue">Syarat &amp; Ketentuan</span>
            </a>
          </div>

        </div><!-- /.card-box-carbul -->
      </div><!-- /.col-12 -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid mb-5 -->
<?php endif; ?>
</div>
</div>
<!-- Close wrapper (#app-scroll dari head.php) -->

<?php $uri = $this->uri->uri_string(); ?>

<!-- NAVBAR BAWAH (mobile only) -->
<nav class="navbar fixed-bottom navbar-light bg-white shadow-sm d-lg-none navbar-bottom px-0">
  <div class="w-100 d-flex justify-content-between text-center position-relative mx-0 px-0">
    
    <!-- BERANDA -->
    <div class="nav-item">
      <a href="<?= base_url() ?>"
         class="<?= ($uri == '' || $uri == 'home') ? 'text-active' : 'text-dark' ?>"
         data-navloading="1">
        <i class="fas fa-home d-block mb-1"></i>
        <span class="small">Beranda</span>
      </a>
    </div>

    <!-- BILLIARD -->
    <div class="nav-item">
      <a
        href="<?= base_url('billiard') ?>"
        class="<?= ($uri == 'billiard' || $uri == 'billiard/daftar_booking' || $uri == 'meja_billiard' || $uri == 'billiard/daftar_voucher') ? 'text-active' : 'text-dark' ?>"
        data-swaltarget="billiard-menu"
        data-booking="<?= base_url('billiard') ?>"
        data-list="<?= base_url('billiard/daftar_booking') ?>"       
        data-free="<?= base_url('billiard/daftar_voucher') ?>"
        data-history="<?= base_url('meja_billiard') ?>"
      >
        <i class="fas fa-golf-ball d-block mb-1"></i>
        <span class="small">Billiard</span>
      </a>
    </div>

    <!-- TOMBOL TENGAH / PRODUK -->
    <div class="space-left"></div>
    <div>
      <a href="<?= base_url('produk') ?>"
         class="center-button <?= ($uri == 'produk') ? 'text-white' : '' ?>"
         style="text-align:center; <?= ($uri == 'produk') ? 'background-color:#28a745;' : '' ?>"
         data-navloading="1">
        <img
          src="<?= base_url('assets/images/logo.png') . '?v=' . filemtime(FCPATH . 'assets/images/logo.png'); ?>"
          alt="Logo"
          class="nav-center-logo"
          style="width:40px; height:40px; object-fit:contain; margin-top:0px;">
        <span class="sr-only d-none">Produk</span>
      </a>
    </div>
    <div class="space-right"></div>

 <!-- NONGKI / CAFE -->
<div class="nav-item">
  <a
    href="<?= base_url('cafe') ?>"
    class="<?= (
        $uri == 'cafe'
        || $uri == 'scan'
        || $uri == 'produk/delivery'
        || $uri == 'produk/walkin'
        || $uri == 'produk/riwayat_pesanan'
        || $uri == 'hal/jadwal'
        || $uri == 'produk/reward'
      ) ? 'text-active' : 'text-dark' ?>"
    data-swaltarget="cafe-menu"
    data-cafe="<?= base_url('cafe') ?>"
    data-dinein="<?= base_url('scan') ?>"
    data-delivery="<?= base_url('produk/delivery') ?>"
    data-walkin="<?= base_url('produk/walkin') ?>"
    data-history="<?= base_url('produk/riwayat_pesanan') ?>"
    data-reward="<?= base_url('produk/reward') ?>"
    
  >
    <i class="fas fa-mug-hot d-block mb-1"></i>
    <span class="small">Cafe</span>
  </a>
</div>



    <!-- MENU -->
    <div class="nav-item">
      <a href="#kontakModalfront"
         class="<?= ($uri == 'hal/kontak' || $uri == 'hal/semua_menu' || $uri == 'hal/pengumuman' || $uri == 'hal/privacy_policy' || $uri == 'hal') ? 'text-active' : 'text-dark' ?>"
         id="btnOpenMenu">
        <i class="fe-grid d-block mb-1"></i>
        <span class="small">Menu</span>
      </a>
    </div>

  </div>
</nav>

<!-- Ripple layer -->
<div id="navRippleLayer"></div>

<style>
  .nav-mini-spinner.spinner-border {
    width: 1rem;
    height: 1rem;
    border-width: .15em;
    display: block;
    margin: 0 auto .25rem auto;
  }

  .nav-loading-center-fix {
    width:40px;
    height:40px;
    display:flex;
    align-items:center;
    justify-content:center;
    margin-top:0px;
  }

  .swal-mini-spinner.spinner-border,
  .modal-mini-spinner.spinner-border {
    width:1rem;
    height:1rem;
    border-width:.15em;
    margin-right:.5rem;
  }

  #navRippleLayer {
    position: fixed;
    left: 0;
    top: 0;
    width: 100vw;
    height: 100vh;
    pointer-events: none;
    z-index: 9999;
  }

  .nav-ripple-burst {
    position: absolute;
    width: var(--size);
    height: var(--size);
    left: calc(var(--x) - var(--size) / 2);
    top:  calc(var(--y) - var(--size) / 2);
    background: rgba(0,0,0,0.22);
    border-radius: 50%;
    pointer-events: none;
    opacity: 1;
    transform: scale(0);
    animation: nav-ripple-anim 400ms ease-out forwards;
    z-index:9999;
  }

  #quickmobilem .qcardfoot {
    width: 100%;
    display: flex;
    flex-direction: column;
    /* align-items: center; */
    /* text-align: center; */
    gap: 8px;
    /* padding: 12px 8px; */
    /* border-radius: 14px; */
    /* background: #f8f9fa; */
    /* border: 1px solid #eee; */
    /* transition: transform .2s 
ease, box-shadow .2s 
ease; */
    }


  @keyframes nav-ripple-anim {
    to {
      transform: scale(4);
      opacity: 0;
    }
  }

  .nav-center-rotating {
    animation: navCenterSpin .8s linear infinite;
    transform-origin: center center;
  }
  @keyframes navCenterSpin {
    to { transform: rotate(360deg); }
  }
</style>

<script>
(function(){

  /* ==========================================================
   * 1. RIPPLE SIDIK JARI NAVBAR
   * ========================================================== */
  (function initRipple(){
    const layer = document.getElementById('navRippleLayer');

    function spawnRipple(e){
      const clientX = (e.clientX !== undefined) ? e.clientX
                    : (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
      const clientY = (e.clientY !== undefined) ? e.clientY
                    : (e.touches && e.touches[0] ? e.touches[0].clientY : 0);
      if (!clientX && !clientY) return;

      const rect = e.currentTarget.getBoundingClientRect();
      const maxSide = Math.max(rect.width, rect.height) * 2;

      const burst = document.createElement('span');
      burst.className = 'nav-ripple-burst';
      burst.style.setProperty('--x', clientX + 'px');
      burst.style.setProperty('--y', clientY + 'px');
      burst.style.setProperty('--size', maxSide + 'px');

      layer.appendChild(burst);
      setTimeout(() => { burst.remove(); }, 450);
    }

    document.querySelectorAll('.navbar-bottom a').forEach(a => {
      a.addEventListener('pointerdown', spawnRipple, {passive:true});
    });
  })();


  /* ==========================================================
   * 2. GANTI ICON NAVBAR -> LOADING
   * ========================================================== */
  function swapIconToSpinner(anchor){
    if (anchor.__navLoadingApplied) return;
    anchor.__navLoadingApplied = true;

    if (anchor.classList.contains('center-button')) {
      const logoImg = anchor.querySelector('.nav-center-logo');
      if (logoImg){
        logoImg.classList.add('nav-center-rotating');
      }
      return;
    }

    let iconEl = anchor.querySelector('i');

    if (!iconEl){
      const imgEl = anchor.querySelector('img');
      if (imgEl){
        const wrap = document.createElement('div');
        wrap.className = 'nav-loading-center-fix';
        wrap.innerHTML = '<div class="spinner-border spinner-border-sm nav-mini-spinner" role="status" aria-hidden="true"></div>';
        imgEl.replaceWith(wrap);
      }
      return;
    }

    iconEl.setAttribute('data-icon-backup-class', iconEl.className);
    iconEl.setAttribute('data-icon-backup-html', iconEl.innerHTML);

    iconEl.className = 'spinner-border spinner-border-sm nav-mini-spinner';
    iconEl.innerHTML = '';
  }

  document.querySelectorAll('.navbar-bottom a[data-navloading]').forEach(a=>{
    a.addEventListener('click', function(e){
      if (e.metaKey || e.ctrlKey || e.shiftKey || e.which === 2) return;
      swapIconToSpinner(this);
      // biarkan lanjut ke href biasa
    });
  });
  /* ==========================================================
   * 3b. SWEETALERT MENU CAFE
   * ========================================================== */
  document.addEventListener('click', function(e){
    const link = e.target.closest('a[data-swaltarget="cafe-menu"]');
    if (!link) return;

    // allow open in new tab
    if (e.metaKey || e.ctrlKey || e.shiftKey || e.which === 2) return;

    e.preventDefault();

    if (!window.Swal || !Swal.fire) {
      // fallback: kalau SweetAlert belum loaded, langsung ke href biasa
      window.location.href = link.getAttribute('href');
      return;
    }

    Swal.fire({
  title: 'Mau apa di Café?',
  icon: 'info',
  iconHtml: '☕',
  html: `
    <div class="container-fluid px-0">
      <div class="row no-gutters">
        <div class="col-12 mb-2">
          <button type="button" id="swalCafeHistory"
            class="btn btn-blue btn-rounded btn-block d-flex align-items-center justify-content-center">
            <span class="me-2 mr-2" aria-hidden="true">🧾</span>
            <span>Riwayat Orderku</span>
          </button>
        </div>

        <div class="col-12 mb-2">
          <button type="button" id="swalReward"
            class="btn btn-blue btn-rounded btn-block d-flex align-items-center justify-content-center">
            <span class="me-2 mr-2" aria-hidden="true">🎁</span>
            <span>Reward Mingguan</span>
          </button>
        </div>

        <div class="col-12 mb-2">
          <button type="button" id="swalCafeDelivery"
            class="btn btn-blue btn-rounded btn-block d-flex align-items-center justify-content-center">
            <span class="me-2 mr-2" aria-hidden="true">🛵</span>
            <span>Pesan Antar (Delivery)</span>
          </button>
        </div>

        <div class="col-12 mb-2">
          <button type="button" id="swalCafeWalkin"
            class="btn btn-blue btn-rounded btn-block d-flex align-items-center justify-content-center">
            <span class="me-2 mr-2" aria-hidden="true">🛍️</span>
            <span>Bungkus (Takeaway)</span>
          </button>
        </div>

        <div class="col-12 mb-2">
          <button type="button" id="swalCafeDineIn"
            class="btn btn-blue btn-rounded btn-block d-flex align-items-center justify-content-center">
            <span class="me-2 mr-2" aria-hidden="true">🍽️</span>
            <span>Makan di Sini (Scan QR)</span>
          </button>
        </div>

        <div class="col-12 mb-2">
          <button type="button" id="swalCafeInfo"
            class="btn btn-blue btn-rounded btn-block d-flex align-items-center justify-content-center">
            <span class="me-2 mr-2" aria-hidden="true">🏠</span>
            <span>Info Café</span>
          </button>
        </div>
      </div>
    </div>
      `,
      showConfirmButton: false,
      showDenyButton: false,
      showCancelButton: false,
      buttonsStyling: false,
      showCloseButton: true,
      allowOutsideClick: true,
      allowEscapeKey: true,
      focusConfirm: false,
      didOpen: () => {
        const go = (u)=>{ if(u) window.location.href = u; };

        const l = Swal.getPopup().closest('body')
          .querySelector('a[data-swaltarget="cafe-menu"]');

        function makeBtnLoading(btn){
          if (!btn || btn.__loadingApplied) return;
          btn.__loadingApplied = true;
          btn.disabled = true;
          btn.innerHTML = `
            <div class="spinner-border spinner-border-sm swal-mini-spinner" role="status" aria-hidden="true"></div>
            <span>Loading...</span>
          `;
        }

        const btnInfo     = document.getElementById('swalCafeInfo');
        const btnDineIn   = document.getElementById('swalCafeDineIn');
        const btnDelivery = document.getElementById('swalCafeDelivery');
        const btnWalkin   = document.getElementById('swalCafeWalkin');
        const btnHistory  = document.getElementById('swalCafeHistory');
        const btnreward  = document.getElementById('swalReward');
        const btnSchedule = document.getElementById('swalCafeSchedule');

        btnInfo?.addEventListener('click', () => {
          makeBtnLoading(btnInfo);
          go(l?.dataset.cafe || l?.getAttribute('href'));
        });

        btnDineIn?.addEventListener('click', () => {
          makeBtnLoading(btnDineIn);
          go(l?.dataset.dinein || l?.getAttribute('href'));
        });

        btnDelivery?.addEventListener('click', () => {
          makeBtnLoading(btnDelivery);
          go(l?.dataset.delivery || l?.getAttribute('href'));
        });

        btnWalkin?.addEventListener('click', () => {
          makeBtnLoading(btnWalkin);
          go(l?.dataset.walkin || l?.getAttribute('href'));
        });

        btnHistory?.addEventListener('click', () => {
          makeBtnLoading(btnHistory);
          go(l?.dataset.history || l?.getAttribute('href'));
        });

        btnreward?.addEventListener('click', () => {
          makeBtnLoading(btnreward);
          go(l?.dataset.reward || l?.getAttribute('href'));
        });

        btnSchedule?.addEventListener('click', () => {
          makeBtnLoading(btnSchedule);
          go(l?.dataset.schedule || l?.getAttribute('href'));
        });
      }
    });
  });


  /* ==========================================================
   * 3. SWEETALERT MENU BILLIARD
   * ========================================================== */
  document.addEventListener('click', function(e){
    const link = e.target.closest('a[data-swaltarget="billiard-menu"]');
    if (!link) return;

    if (e.metaKey || e.ctrlKey || e.shiftKey || e.which === 2) return;

    e.preventDefault();

    if (!window.Swal || !Swal.fire) {
      window.location.href = link.getAttribute('href');
      return;
    }

    Swal.fire({
      title: 'Mau Ngapain ??',
      icon: 'info',
      iconHtml: '🎱',
      html: `
  <div class="container-fluid px-0">
    <div class="row no-gutters">
      <div class="col-12 mb-2">
        <button type="button" id="swalBtnBooking"
          class="btn btn-blue btn-rounded btn-block d-flex align-items-center justify-content-center">
          <span class="me-2 mr-2" aria-hidden="true">📅</span>
          <span>Booking Main Billiard</span>
        </button>
      </div>
      <div class="col-12 mb-2">
        <button type="button" id="swalBtnList"
          class="btn btn-blue btn-rounded btn-block d-flex align-items-center justify-content-center">
          <span class="me-2 mr-2" aria-hidden="true">📋</span>
          <span>Jadwal Main Billiard</span>
        </button>
      </div>
      <div class="col-12 mb-2">
        <button type="button" id="swalBtnHistory"
          class="btn btn-blue btn-rounded btn-block d-flex align-items-center justify-content-center">
          <span class="me-2 mr-2" aria-hidden="true">🎱</span>
          <span>Tarif Meja Billiard</span>
        </button>
      </div>
      
      <div class="col-12">
        <button type="button" id="swalBtnGratis"
          class="btn btn-blue btn-rounded btn-block d-flex align-items-center justify-content-center">
          <span class="me-2 mr-2" aria-hidden="true">🎟️</span>
          <span>Cek Voucher Gratis Main</span>
        </button>
      </div>
    </div>
  </div>
`,
      showConfirmButton: false,
      showDenyButton: false,
      showCancelButton: false,
      buttonsStyling: false,
      showCloseButton: true,
      allowOutsideClick: true,
      allowEscapeKey: true,
      focusConfirm: false,
      didOpen: () => {
        const go = (u)=>{ if(u) window.location.href = u; };

        const l = Swal.getPopup().closest('body')
          .querySelector('a[data-swaltarget="billiard-menu"]');

        function makeBtnLoading(btn){
          if (!btn || btn.__loadingApplied) return;
          btn.__loadingApplied = true;
          btn.disabled = true;
          btn.innerHTML = `
            <div class="spinner-border spinner-border-sm swal-mini-spinner" role="status" aria-hidden="true"></div>
            <span>Loading...</span>
          `;
        }

        const btnBooking = document.getElementById('swalBtnBooking');
        const btnList    = document.getElementById('swalBtnList');
        const btnGratis  = document.getElementById('swalBtnGratis');
        const btnHistory = document.getElementById('swalBtnHistory');

        btnBooking?.addEventListener('click', () => {
          makeBtnLoading(btnBooking);
          go(l?.dataset.booking || l?.getAttribute('href'));
        });

        btnList?.addEventListener('click', () => {
          makeBtnLoading(btnList);
          go(l?.dataset.list || l?.getAttribute('href'));
        });

        btnGratis?.addEventListener('click', () => {
          makeBtnLoading(btnGratis);
          go(l?.dataset.free || l?.getAttribute('href'));
        });

        btnHistory?.addEventListener('click', () => {
          makeBtnLoading(btnHistory);
          go(l?.dataset.history || l?.dataset.list || l?.getAttribute('href'));
        });

       
      }
    });
  });


  /* ==========================================================
   * 4. QUICK MENU di modal bawah (kontakModalfront)
   *    kasih spinner kecil di bulatan emoji
   * ========================================================== */
  if (!window.__AUSI_SPINNER_GLOBAL__) {
    window.__AUSI_SPINNER_GLOBAL__ = true;

    // inject CSS spinner kalau belum ada
    if (!document.getElementById('ausiSpinnerStyle')){
      var st = document.createElement('style');
      st.id = 'ausiSpinnerStyle';
      st.textContent = `
        .menu-circle.loading .emoji-icon{ opacity:0; }
        .menu-circle.loading{
          position:relative;
        }
        .menu-circle.loading::after{
          content:"";
          position:absolute;
          inset:0;
          margin:auto;
          width:28px;
          height:28px;
          border-radius:50%;
          border:3px solid rgba(255,255,255,.6);
          border-right-color:transparent;
          animation:ausiQuickSpin .6s linear infinite;
        }
        @keyframes ausiQuickSpin{
          from { transform:rotate(0deg); }
          to   { transform:rotate(360deg); }
        }
      `;
      document.head.appendChild(st);
    }

    // helper kasih class .loading
    function activateCircleSpinnerFrom(anchor){
      if (!anchor) return;
      var circle = anchor.querySelector('.menu-circle');
      if (!circle) return;
      if (!circle.classList.contains('loading')){
        circle.classList.add('loading');
      }
    }

    // klik di menu modal bawah
    document.addEventListener('click', function(e){
      var modalItem = e.target.closest('#kontakModalfront a[data-menuloading]');
      if (!modalItem) return;

      // allow open in new tab
      if (e.metaKey || e.ctrlKey || e.shiftKey || e.which === 2) return;

      e.preventDefault();
      activateCircleSpinnerFrom(modalItem);

      var href = modalItem.getAttribute('href');
      if (href){
        window.location.href = href;
      }
    }, {passive:false});

    // klik di quickmenu atas (opsional, kalau ada id="quickmenu")
    document.addEventListener('click', function(e){
      var card = e.target.closest('#quickmenu .qcardfoot');
      if (!card) return;
      if (document.getElementById('grandong')) { return; }
      activateCircleSpinnerFrom(card);
      // no preventDefault -> biarkan lanjut
    }, {passive:true});
  }

})();
</script>

<!-- MODAL MENU DEPAN -->
<style type="text/css">
  /* OVERRIDE: paksa 4 kolom di semua ukuran */
#quickmobilem{
  grid-template-columns:repeat(4,1fr) !important;
  gap:8px !important;
}

/* item jadi rapet rapi */
#quickmobilem .quickmobilem-item{
  display:flex;
}

/* kecilkan sedikit biar muat 4 per baris di layar kecil */


#quickmobilem .menu-circle{
  width:50px;
  height:50px;
  border-radius:14px;
}

#quickmobilem .emoji-icon{
  font-size:22px;
  line-height:1;
}

#quickmobilem .menu-label{
  font-size:11px;
  font-weight:600;
  line-height:1.2;
  margin-top:8px;
}

</style>
<div class="modal fade"
     id="kontakModalfront"
     tabindex="-1"
     aria-labelledby="menumoLabel"
     aria-hidden="true"
     data-backdrop="false">
  <div class="modal-dialog modal-dialog-scrollable modal-bottom modal-dialog-full" style="animation-duration:.5s;">
    <div class="modal-content">

      <div class="modal-header bg-blue text-white">
        <h5 class="modal-title d-flex align-items-center text-white" id="menumoLabel">
          <i class="fas fa-concierge-bell mr-2"></i> Menu
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body p-0">
        <div class="menu-list">
          <div id="quickmobilem" class="quickmobilem-scroll d-flex text-center" tabindex="0" aria-label="Menu cepat geser">

            <!-- ===== QUICK MENU (4 KOLOM, DENGAN SECTION) ===== -->
<style>
  #quickmobilem{ display:block !important; width:100%; }

  /* Section wrapper & title */
  #quickmobilem .quicksection{ display:block; width:100%; margin:-8px 0 18px; }
  #quickmobilem .quicksection + .quicksection{
    border-top:1px dashed rgba(0,0,0,.08);
    padding-top:8px;
  }

  #quickmobilem .section-icon{ font-size:1.1rem; }

  /* Grid 4 kolom (selalu 4) */
  #quickmobilem .quicksection-items{
    display:flex; flex-wrap:wrap; margin:-8px;  
  }
  #quickmobilem .quicksection-items .quickmobilem-item{
    padding:8px; box-sizing:border-box;
    flex:0 0 25% !important;
    max-width:25% !important;
    width:25% !important;
    min-width:0 !important;   /* netralisir rule lama */
  }
  #quickmobilem .quickmobilem-item .qcardfoot{
    display:block; width:100%; height:100%;
  }
  /* Judul di tengah */
#quickmobilem .quicksection-title{
  display:flex; align-items:center; gap:.5rem;
  font-weight:800; font-size:1.05rem; margin:0px 2px 12px;
  justify-content:left;     /* ⬅️ judul ke tengah */
  text-align:center;
}
#quickmobilem .section-icon{ font-size:1.1rem; }

</style>

<div id="quickmobilem">

  <!-- ========== CAFÉ & PESAN ========== -->
  <div class="quicksection" aria-labelledby="sec-cafe">
  <div class="quicksection-title" id="sec-cafe">
    <span class="section-icon">☕</span><span class="section-text">Café & Pesan</span>
  </div>
  <div class="quicksection-items">
    <!-- Riwayat Order Saya -->
    <div class="quickmobilem-item">
      <a href="<?= site_url('produk/riwayat_pesanan') ?>" class="qcardfoot d-block text-decoration-none" aria-label="Riwayat Order Saya" data-menuloading="1">
        <div class="menu-circle" style="background:#16a085;"><span class="emoji-icon" aria-hidden="true">🧾</span></div>
        <small class="menu-label">Riwayat Orderku</small>
      </a>
    </div>

    <!-- Reward Mingguan -->
    <div class="quickmobilem-item">
      <a href="<?= site_url('produk/reward') ?>" class="qcardfoot d-block text-decoration-none" aria-label="Reward Mingguan" data-menuloading="1">
        <div class="menu-circle" style="background:#f39c12;"><span class="emoji-icon" aria-hidden="true">🎁</span></div>
        <small class="menu-label">Reward Mingguan</small>
      </a>
    </div>
    
    <div class="quickmobilem-item">
      <a href="<?= site_url('scan') ?>" class="qcardfoot d-block text-decoration-none" aria-label="Makan di Sini (Scan QR)" data-menuloading="1">
        <div class="menu-circle" style="background:#2ecc71;"><span class="emoji-icon" aria-hidden="true">🍽️</span></div>
        <small class="menu-label">Makan di Sini</small>
      </a>
    </div>

    <div class="quickmobilem-item">
      <a href="<?= site_url('produk/delivery') ?>" class="qcardfoot d-block text-decoration-none" aria-label="Antar / Delivery" data-menuloading="1">
        <div class="menu-circle" style="background:#3498db;"><span class="emoji-icon" aria-hidden="true">🚚</span></div>
        <small class="menu-label">Antar / Delivery</small>
      </a>
    </div>

    <div class="quickmobilem-item">
      <a href="<?= site_url('produk/walkin') ?>" class="qcardfoot d-block text-decoration-none" aria-label="Bungkus (Walk-in)" data-menuloading="1">
        <div class="menu-circle" style="background:#9b59b6;"><span class="emoji-icon" aria-hidden="true">🛍️</span></div>
        <small class="menu-label">Bungkus</small>
      </a>
    </div>

    <div class="quickmobilem-item">
      <a href="<?= site_url('cafe') ?>" class="qcardfoot d-block text-decoration-none" aria-label="Info Café / Jadwal" data-menuloading="1">
        <div class="menu-circle" style="background:#dc7633;"><span class="emoji-icon" aria-hidden="true">🏪</span></div>
        <small class="menu-label">Café</small>
      </a>
    </div>

    

  </div>
</div>


  <!-- ========== BILLIARD ========== -->
  <div class="quicksection" aria-labelledby="sec-billiard">
    <div class="quicksection-title" id="sec-billiard">
      <span class="section-icon">🎱</span><span class="section-text">Billiard</span>
    </div>
    <div class="quicksection-items">
      <div class="quickmobilem-item">
        <a href="<?= site_url('billiard') ?>" class="qcardfoot d-block text-decoration-none" aria-label="Booking Billiard" data-menuloading="1">
          <div class="menu-circle" style="background:#17a2b8;"><span class="emoji-icon" aria-hidden="true">📝</span></div>
          <small class="menu-label">Booking Billiard</small>
        </a>
      </div>

      <div class="quickmobilem-item">
        <a href="<?= site_url('meja_billiard') ?>" class="qcardfoot d-block text-decoration-none" aria-label="Tarif Meja Billiard" data-menuloading="1">
          <div class="menu-circle" style="background:#1abc9c;"><span class="emoji-icon" aria-hidden="true">💵</span></div>
          <small class="menu-label">Tarif Meja Billiard</small>
        </a>
      </div>

      <div class="quickmobilem-item">
        <a href="<?= site_url('billiard/daftar_booking') ?>" class="qcardfoot d-block text-decoration-none" aria-label="Jadwal Main Billiard" data-menuloading="1">
          <div class="menu-circle" style="background:#e67e22;"><span class="emoji-icon" aria-hidden="true">🗓️</span></div>
          <small class="menu-label">Jadwal Main Billiard</small>
        </a>
      </div>

      <div class="quickmobilem-item">
        <a href="<?= site_url('billiard/daftar_voucher') ?>" class="qcardfoot d-block text-decoration-none" aria-label="Gratis Main Billiard" data-menuloading="1">
          <div class="menu-circle" style="background:#d81b60;"><span class="emoji-icon" aria-hidden="true">🎁</span></div>
          <small class="menu-label">Gratis Main Billiard</small>
        </a>
      </div>
    </div>
  </div>

  <!-- ========== FASILITAS ========== -->
  <div class="quicksection" aria-labelledby="sec-fasilitas">
    <div class="quicksection-title" id="sec-fasilitas">
      <span class="section-icon">🏷️</span><span class="section-text">Fasilitas</span>
    </div>
    <div class="quicksection-items">
      <div class="quickmobilem-item">
        <a href="<?= site_url('pijat') ?>" class="qcardfoot d-block text-decoration-none" aria-label="Kursi Pijat" data-menuloading="1">
          <div class="menu-circle" style="background:#9a6a38;"><span class="emoji-icon" aria-hidden="true">💆‍♂️</span></div>
          <small class="menu-label">Kursi Pijat</small>
        </a>
      </div>
      <div class="quickmobilem-item">
        <a href="<?= site_url('review') ?>" class="qcardfoot d-block text-decoration-none" aria-label="Rating Review" data-menuloading="1">
          <div class="menu-circle" style="background:#FFC107;"><span class="emoji-icon" aria-hidden="true">📝</span></div>
          <small class="menu-label">Ratings &amp; Review</small>
        </a>
      </div>
    </div>
  </div>

  <!-- ========== INFORMASI ========== -->
  <div class="quicksection" aria-labelledby="sec-info">
    <div class="quicksection-title" id="sec-info">
      <span class="section-icon">ℹ️</span><span class="section-text">Informasi</span>
    </div>
    <div class="quicksection-items">
      <div class="quickmobilem-item">
        <a href="<?= site_url('hal/kontak') ?>" class="qcardfoot d-block text-decoration-none" aria-label="Kontak" data-menuloading="1">
          <div class="menu-circle" style="background:#25D366;"><span class="emoji-icon" aria-hidden="true">☎️</span></div>
          <small class="menu-label">Kontak</small>
        </a>
      </div>

      <div class="quickmobilem-item">
        <a href="<?= site_url('hal/pengumuman') ?>" class="qcardfoot d-block text-decoration-none" aria-label="Pengumuman" data-menuloading="1">
          <div class="menu-circle" style="background:#e74c3c;"><span class="emoji-icon" aria-hidden="true">📣</span></div>
          <small class="menu-label">Pengumuman</small>
        </a>
      </div>
<!-- Tambahkan sekali saja (opsional) agar SVG pas di lingkaran -->
<style>
  .menu-circle svg{ width:24px; height:24px; display:block; }
</style>



<div class="quickmobilem-item">
  <a href="<?= site_url('hal/review') ?>" class="qcardfoot d-block text-decoration-none" aria-label="Google Review" data-menuloading="1">
    <div class="menu-circle" style="background:#fff; border:2px solid #e5e7eb;">
      <!-- Logo Google (inline SVG) -->
      <svg viewBox="0 0 256 262" aria-hidden="true" focusable="false">
        <path fill="#4285F4" d="M255.68 131.09c0-10.22-.84-17.66-2.66-25.39H130.55v45.99h71.93c-1.45 11.61-9.3 29.16-26.77 40.98l-.24 1.59 38.87 30.14 2.69.27c24.67-22.78 38.65-56.33 38.65-93.58"/>
        <path fill="#34A853" d="M130.55 261.1c35.2 0 64.77-11.62 86.36-31.64l-41.12-31.88c-11.03 7.7-25.82 13.09-45.24 13.09-34.57 0-63.92-22.64-74.43-53.98l-1.54.13-40.23 31.06-.53 1.42C34.2 231.6 79.46 261.1 130.55 261.1"/>
        <path fill="#FBBC05" d="M56.12 156.69c-2.77-8.22-4.36-16.97-4.36-26.02s1.59-17.8 4.36-26.02l-.07-1.74-40.72-31.49-1.33.63C3.78 89.96 0 109.63 0 130.67c0 21.04 3.78 40.71 13.99 59.62l42.13-33.6"/>
        <path fill="#EA4335" d="M130.55 51.53c24.45 0 40.88 10.54 50.26 19.37l36.69-35.87C195.23 12.11 165.75 0 130.55 0 79.46 0 34.2 29.5 13.99 71.05l42.13 33.6c10.51-31.34 39.86-53.98 74.43-53.98"/>
      </svg>
    </div>
    <small class="menu-label">Google Review</small>
  </a>
</div>

      <div class="quickmobilem-item">
        <a href="<?= site_url('hal/privacy_policy') ?>" class="qcardfoot d-block text-decoration-none" aria-label="Kebijakan Privasi" data-menuloading="1">
          <div class="menu-circle" style="background:#16a085;"><span class="emoji-icon" aria-hidden="true">🔒</span></div>
          <small class="menu-label">Kebijakan Privasi</small>
        </a>
      </div>

      <div class="quickmobilem-item">
        <a href="<?= site_url('hal') ?>" class="qcardfoot d-block text-decoration-none" aria-label="Syarat dan Ketentuan" data-menuloading="1">
          <div class="menu-circle" style="background:#6c757d;"><span class="emoji-icon" aria-hidden="true">📜</span></div>
          <small class="menu-label">S&amp;K</small>
        </a>
      </div>
    </div>
  </div>

</div>
<!-- ===== /QUICK MENU ===== -->


          <div class="sheet-footer">
  <div class="sheet-grab" aria-hidden="true"></div>

  <button type="button"
          id="btnSlideDownClose"
          class="sheet-close-btn"
          data-dismiss="modal"
          aria-label="Tutup">
    <i class="mdi mdi-chevron-down"></i><span>Tutup</span>
  </button>

  <div class="sheet-credit">Developed by <strong>Onhacker</strong></div>
</div>
<style type="text/css">
  .sheet-footer{
  position: sticky; bottom: 0; z-index: 1;
  /*padding: 10px 16px calc(12px + env(safe-area-inset-bottom));*/
  background: rgba(255,255,255,.75);
  backdrop-filter: saturate(160%) blur(8px);
  -webkit-backdrop-filter: saturate(160%) blur(8px);
  border-top: 1px solid rgba(2,6,23,.06);
  text-align: center;
}
.sheet-grab{
  width: 56px; height: 5px; border-radius: 999px;
  margin: 2px auto 10px;
  background: rgba(2,6,23,.15);
}
.sheet-close-btn{
  display: inline-flex; align-items: center; gap: 6px;
  padding: 8px 14px; border-radius: 999px; border: 1px solid rgba(2,6,23,.08);
  background: linear-gradient(180deg,#fff,#f6f7fb);
  color: #0f172a; font-weight: 700;
  box-shadow: 0 8px 20px rgba(2,6,23,.08), 0 1px 0 rgba(255,255,255,.7) inset;
  transition: transform .15s ease, box-shadow .2s ease;
}
.sheet-close-btn i{ font-size: 18px; line-height: 1; }
.sheet-close-btn:hover{ transform: translateY(1px); box-shadow: 0 6px 16px rgba(2,6,23,.14), 0 1px 0 rgba(255,255,255,.6) inset; }
.sheet-close-btn:active{ transform: translateY(2px) scale(.98); }
.sheet-close-btn:focus{ outline: none; box-shadow: 0 0 0 4px rgba(37,99,235,.18), 0 8px 20px rgba(2,6,23,.1); }

.sheet-credit{
  margin-top: 8px; font-size: .82rem; color: #64748b; letter-spacing: .01em;
}

/* Dark mode */
@media (prefers-color-scheme: dark){
  .sheet-footer{
    /*background: rgba(17,24,39,.75);*/
    border-top-color: rgba(255,255,255,.06);
  }
  .sheet-grab{ background: rgba(255,255,255,.18); }
  .sheet-close-btn{
    background: linear-gradient(180deg,#1f2937,#111827);
    color: #e5e7eb; border-color: rgba(255,255,255,.08);
    box-shadow: 0 10px 24px rgba(0,0,0,.45), 0 1px 0 rgba(255,255,255,.06) inset;
  }
  .sheet-close-btn:focus{ box-shadow: 0 0 0 4px rgba(96,165,250,.25), 0 10px 24px rgba(0,0,0,.45); }
  .sheet-credit{ color:#9ca3af; }
}

/* Reduce motion */
@media (prefers-reduced-motion: reduce){
  .sheet-close-btn{ transition: none; }
}

</style>
        </div><!-- /.menu-list -->
      </div><!-- /.modal-body -->
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal fade -->




<script src="<?= base_url('assets/admin/js/vendor.min.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/app.min.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/sw.min.js') ?>"></script>
<!-- load JS eksternal -->
<!-- petunjuk install ios -->
<?php $this->load->view("front_end/app"); ?>
<?php
$path = 'assets/js/install.js';
$ver  = file_exists(FCPATH.$path) ? filemtime(FCPATH.$path) : time(); // fallback
?>
<script defer src="<?= base_url($path) ?>?v=<?= $ver ?>"></script>

<script src="<?= base_url('assets/min/footer.min.js') ?>"></script>

<script>
  const base_url = "<?= base_url() ?>";
  const APP_PATH = "<?= rtrim(parse_url(base_url(), PHP_URL_PATH) ?? '/', '/') ?>/";
  const SW_FILE  = "service-worker.js";

  function scrollToTop(){
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  (function(){
    const PKG = "<?= $playPackage ?? '' ?>";
    const WEB = "<?= $playUrl ?? '' ?>";
    window.openPlayStore = function(e){
      if (e) e.preventDefault();
      const isAndroid = /Android/i.test(navigator.userAgent);
      if (isAndroid) {
        const intent = `intent://details?id=${PKG}#Intent;scheme=market;package=com.android.vending;S.browser_fallback_url=${encodeURIComponent(WEB)};end`;
        location.href = intent;
      } else {
        window.open(WEB, '_blank', 'noopener');
      }
      return false;
    };
  })();

  // PWA service worker
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register(APP_PATH + SW_FILE, { scope: APP_PATH })
      .then(registration => {
        console.log("✅ Service Worker registered.");
        registration.onupdatefound = () => {
          const newWorker = registration.installing;
          if (!newWorker) return;
          console.log("🔄 Update ditemukan.");
          newWorker.onstatechange = () => {
            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
              if (window.Swal) {
                Swal.fire({
                  title: 'Update Tersedia',
                  text: 'Versi baru tersedia. Ingin muat ulang aplikasi?',
                  icon: 'info',
                  showCancelButton: true,
                  confirmButtonText: 'Muat Ulang',
                  cancelButtonText: 'Nanti Saja'
                }).then((r) => { if (r.isConfirmed) newWorker.postMessage({ type:'SKIP_WAITING' }); });
              } else {
                if (confirm('Versi baru tersedia. Muat ulang aplikasi sekarang?')) {
                  newWorker.postMessage({ type:'SKIP_WAITING' });
                }
              }
            }
          };
        };
      })
      .catch(err => console.warn("❌ Gagal daftar Service Worker:", err));

    navigator.serviceWorker.addEventListener('controllerchange', () => {
      location.reload();
    });
  }
</script>

</body>
</html>
