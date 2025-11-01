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
          <h3 class=" text-center"><strong><?php echo $rec->nama_website." ".strtoupper($rec->kabupaten) ?></strong></h3>
          <p class="boxed-text-l text-center mb-1">
            <?php echo $rec->meta_deskripsi ?>
          </p>

          <div class="text-center">
            Bagikan:
            <div class="share-buttons">
              <button class=" btn btn-whatsapp btn-xs" onclick="shareTo('whatsapp')">
                <svg viewBox="0 0 32 32"><path d="M16.003 2.002a14 14 0 00-12.081 20.9l-1.586 5.8 5.954-1.558A14 14 0 1016.003 2zM8.463 24.43l-.35.093.093-.338.618-2.25-.446-.65a11.798 11.798 0 112.007 2.043l-.648-.43-2.28.58.006.003zM23.4 19.7c-.33.93-1.62 1.722-2.215 1.837-.573.11-1.285.16-2.068-.127-.477-.17-1.09-.352-1.894-.692-3.326-1.436-5.514-4.84-5.685-5.07-.17-.23-1.36-1.813-1.36-3.455 0-1.642.86-2.45 1.168-2.788.307-.34.668-.42.89-.42.223 0 .445.002.64.01.206.01.483-.078.756.576.29.682.985 2.353 1.07 2.526.085.17.142.36.028.577-.11.217-.165.35-.33.54-.165.19-.35.43-.5.577-.17.17-.345.357-.15.707.2.352.893 1.47 1.915 2.38 1.317 1.17 2.426 1.537 2.776 1.708.35.17.552.15.755-.092.197-.23.855-.997 1.084-1.34.223-.34.447-.287.76-.17.312.118 1.98.935 2.317 1.102.337.17.56.24.642.37.086.124.086.716-.24 1.647z"/></svg>
              </button>

              <button class=" btn btn-facebook btn-xs" onclick="shareTo('facebook')">
                <svg viewBox="0 0 24 24"><path d="M22 12.073C22 6.505 17.523 2 12 2S2 6.505 2 12.073C2 17.096 5.656 21.158 10.438 22v-7.01h-3.14v-2.917h3.14V9.845c0-3.1 1.894-4.788 4.659-4.788 1.325 0 2.464.099 2.797.143v3.24l-1.92.001c-1.504 0-1.796.716-1.796 1.767v2.316h3.588l-.467 2.917h-3.12V22C18.344 21.158 22 17.096 22 12.073z"/></svg>
              </button>

              <button class="btn btn-twitter btn-xs" onclick="shareTo('twitter')">
                <svg viewBox="0 0 24 24"><path d="M23 3a10.9 10.9 0 01-3.14 1.53A4.48 4.48 0 0022.4 1.64a9.03 9.03 0 01-2.88 1.1 4.52 4.52 0 00-7.71 4.12A12.84 12.84 0 013 2.24a4.51 4.51 0 001.39 6.02 4.41 4.41 0 01-2.05-.56v.06a4.52 4.52 0 003.63 4.42 4.52 4.52 0 01-2.04.08 4.53 4.53 0 004.23 3.14A9.05 9.05 0 012 19.54a12.76 12.76 0 006.92 2.03c8.3 0 12.84-6.87 12.84-12.84 0-.2-.01-.39-.02-.58A9.22 9.22 0 0023 3z"/></svg>
              </button>

              <button class=" btn btn-telegram btn-xs" onclick="shareTo('telegram')">
                <svg viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.372 0 12c0 5.103 3.194 9.426 7.675 11.185.561.104.766-.243.766-.54 0-.266-.01-1.142-.015-2.072-3.124.681-3.787-1.507-3.787-1.507-.511-1.295-1.248-1.64-1.248-1.64-1.02-.698.077-.684.077-.684 1.127.079 1.72 1.158 1.72 1.158 1.003 1.718 2.63 1.222 3.272.934.103-.726.392-1.222.714-1.503-2.494-.284-5.115-1.247-5.115-5.548 0-1.225.438-2.228 1.157-3.014-.116-.285-.5-1.431.108-2.984 0 0 .94-.302 3.08 1.151A10.74 10.74 0 0112 6.845a10.77 10.77 0 012.808.377c2.14-1.453 3.08-1.151 3.08-1.151.609 1.553.225 2.699.11 2.984.72.786 1.156 1.789 1.156 3.014 0 4.31-2.625 5.26-5.126 5.538.403.345.763 1.023.763 2.06 0 1.488-.014 2.688-.014 3.053 0 .299.202.648.772.538A12.005 12.005 0 0024 12c0-6.628-5.373-12-12-12z"/></svg>
              </button>
            </div>

            <?php
              $playPackage = '#';
              $playUrl     = 'https://play.google.com/store/apps/details?id=' . $playPackage;
            ?>

            <style type="text/css">
              .install-badge{
                display:inline-flex;
                align-items:center;
                background:#000;
                color:#fff;
                font-size:16px;
                line-height:1.2;
                font-weight:600;
                font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;
                border-radius:999px;
                padding:10px 14px;
                text-decoration:none;
                border:1px solid #000;
                cursor:pointer;
              }
              .install-badge:hover,
              .install-badge:focus{
                text-decoration:none;
                background:#111;
                color:#fff;
              }

              .install-badge .install-icon{
                display:block;
                flex-shrink:0;
                width:24px;
                height:24px;
                margin-right:10px;
              }
              .install-badge .install-icon svg{
                width:100%;
                height:100%;
                fill:#fff;
                display:block;
              }

              @media (max-width:380px){
                .install-badge{
                  font-size:14px;
                  padding:8px 12px;
                }
                .install-badge .install-icon{
                  width:20px;
                  height:20px;
                  margin-right:8px;
                }
              }
            </style>

            <div class="text-center store-badges">
              <a id="installButton"
                 href="#"
                 class="install-badge d-inline-flex my-2 ms-2 ml-2"
                 aria-label="Install App">
                <span class="install-icon" aria-hidden="true"></span>
                <span class="install-text">Install App</span>
              </a>
            </div>
          </div>

          <style type="text/css">
            .divider {
              border: 0;
              height: 1px;
              background-color: rgba(0, 0, 0, 0.05);
              margin: 20px 0;
              border-radius: 1px;
            }
            .text-nowrap { white-space: nowrap; }
          </style>

          <div class="divider mb-3"></div>
          <div class="row text-center mb-3">
            <a class="col-5 text-nowrap text-blue" href="<?php echo site_url('hal/privacy_policy') ?>">
              Kebijakan Privasi
            </a>

            <a class="col-2" href="#topnav" onclick="scrollToTop()" aria-label="Kembali ke atas">
              <i class="fas fa-arrow-up" style="color:#4a81d4"></i>
            </a>

            <a class="col-5 text-nowrap text-blue" href="<?php echo site_url('hal') ?>" aria-label="Syarat & Ketentuan">
              <span class="d-inline d-sm-none">
                <abbr title="Syarat & Ketentuan">S&K</abbr>
              </span>
              <span class="d-none d-sm-inline text-blue">Syarat & Ketentuan</span>
            </a>
          </div>
        </div> 
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

</div>
<!-- Close wrapper -->

<?php $uri = $this->uri->uri_string(); ?>

<!-- NAVBAR BAWAH -->
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

    <!-- BILLIARD (SweetAlert pilih aksi) -->
    <div class="nav-item">
      <a
        href="<?= base_url('billiard') ?>"
        class="<?= ($uri == 'billiard' || $uri == 'billiard/daftar_booking' || $uri == 'hal/jadwal_billiard' || $uri == 'billiard/daftar_voucher') ? 'text-active' : 'text-dark' ?>"
        data-swaltarget="billiard-menu"
        data-booking="<?= base_url('billiard') ?>"
        data-list="<?= base_url('billiard/daftar_booking') ?>"       
        data-free="<?= base_url('billiard/daftar_voucher') ?>"
        data-history="<?= base_url('hal/jadwal_billiard') ?>"
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
         <img src="<?= base_url('assets/images/logo.png') . '?v=' . filemtime(FCPATH . 'assets/images/logo.png'); ?>" alt="Logo" 
         class="nav-center-logo"
             style="width:40px; height:40px; object-fit:contain; margin-top:0px;">
        <span class="sr-only d-none">Produk</span>
      </a>
    </div>
    <div class="space-right"></div>

    <!-- NONGKI -->
    <div class="nav-item">
      <a href="<?= base_url('hal/jadwal') ?>"
         class="<?= ($uri == 'hal/jadwal') ? 'text-active' : 'text-dark' ?>"
         data-navloading="1">
        <i class="fas fa-mug-hot d-block mb-1"></i>
        <span class="small">Nongki</span>
      </a>
    </div>

    <!-- MENU (BUKA MODAL, bukan pindah halaman) -->
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

<!-- Ripple layer untuk efek "cekrek" -->
<div id="navRippleLayer"></div>

<style>
  /* spinner kecil gantikan icon nav */
  .nav-mini-spinner.spinner-border {
    width: 1rem;
    height: 1rem;
    border-width: .15em;
    display: block;
    margin: 0 auto .25rem auto;
  }

  /* saat sebelumnya kita ganti <img> dengan spinner,
     wrapper ini jaga ukuran biar gak loncat layout */
  .nav-loading-center-fix {
    width:40px;
    height:40px;
    display:flex;
    align-items:center;
    justify-content:center;
    margin-top:0px;
  }

  /* spinner kecil untuk tombol swal & item modal */
  .swal-mini-spinner.spinner-border,
  .modal-mini-spinner.spinner-border {
    width:1rem;
    height:1rem;
    border-width:.15em;
    margin-right:.5rem;
  }

  /* Ripple layer global sidik jari */
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

  @keyframes nav-ripple-anim {
    to {
      transform: scale(4);
      opacity: 0;
    }
  }

  /* ===== animasi mutar untuk logo tombol tengah ===== */
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
   * 1. RIPPLE SIDIK JARI NAVBAR (efek "cekrek")
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

      setTimeout(() => {
        burst.remove();
      }, 450);
    }

    document.querySelectorAll('.navbar-bottom a').forEach(a => {
      a.addEventListener('pointerdown', spawnRipple, {passive:true});
    });
  })();


  /* ==========================================================
   * 2. GANTI ICON NAVBAR -> LOADING (data-navloading="1")
   *    - Home / Nongki: ganti <i> jadi spinner
   *    - Tombol tengah Produk: logo PNG mutar
   * ========================================================== */
  function swapIconToSpinner(anchor){
    if (anchor.__navLoadingApplied) return;
    anchor.__navLoadingApplied = true;

    // tombol tengah punya class center-button
    if (anchor.classList.contains('center-button')) {
      const logoImg = anchor.querySelector('.nav-center-logo');
      if (logoImg){
        // tambahkan class animasi rotate
        logoImg.classList.add('nav-center-rotating');
      }
      // tombol tengah tidak diganti spinner bootstrap, cukup mutar
      return;
    }

    // selain tombol tengah → cari icon <i>
    let iconEl = anchor.querySelector('i');

    // fallback kalau tidak ada <i> tapi ada <img> (kasus langka)
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

    // simpan class lama icon (optional)
    iconEl.setAttribute('data-icon-backup-class', iconEl.className);
    iconEl.setAttribute('data-icon-backup-html', iconEl.innerHTML);

    // ubah jadi spinner
    iconEl.className = 'spinner-border spinner-border-sm nav-mini-spinner';
    iconEl.innerHTML = '';
  }

  document.querySelectorAll('.navbar-bottom a[data-navloading]').forEach(a=>{
    a.addEventListener('click', function(e){
      // ctrl / cmd / shift / middle click -> biarkan buka tab baru
      if (e.metaKey || e.ctrlKey || e.shiftKey || e.which === 2) return;
      swapIconToSpinner(this);
      // tidak preventDefault -> langsung lanjut ke href normal
    });
  });


  /* ==========================================================
   * 3. SWEETALERT MENU BILLIARD
   *    - munculin popup
   *    - tombol di popup dapat spinner "Loading..." sebelum redirect
   * ========================================================== */
  document.addEventListener('click', function(e){
    const link = e.target.closest('a[data-swaltarget="billiard-menu"]');
    if (!link) return;

    // allow buka tab baru (ctrl/cmd/shift/middle)
    if (e.metaKey || e.ctrlKey || e.shiftKey || e.which === 2) return;

    e.preventDefault();

    // fallback kalau Swal belum ada
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
                <i class="fas fa-calendar-plus me-2 mr-2" aria-hidden="true"></i>
                <span>Booking Main</span>
              </button>
            </div>
            <div class="col-12 mb-2">
              <button type="button" id="swalBtnHistory"
                class="btn btn-blue btn-rounded btn-block d-flex align-items-center justify-content-center">
                <i class="mdi mdi-billiards me-2 mr-2" aria-hidden="true"></i>
                <span>Lihat Meja</span>
              </button>
            </div>
            <div class="col-12 mb-2">
              <button type="button" id="swalBtnList"
                class="btn btn-blue btn-rounded btn-block d-flex align-items-center justify-content-center">
                <i class="fas fa-clipboard-list me-2 mr-2" aria-hidden="true"></i>
                <span>List Bookingan</span>
              </button>
            </div>
            <div class="col-12">
              <button type="button" id="swalBtnGratis"
                class="btn btn-blue btn-rounded btn-block d-flex align-items-center justify-content-center">
                <i class="fas fa-ticket-alt me-2 mr-2" aria-hidden="true"></i>
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
          .querySelector('a[data-swaltarget="billiard-menu"]'); // anchor asal navbar billiard

        // helper kasih spinner ke tombol swal
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


})();
</script>

<!-- MODAL MENU DEPAN -->
<div class="modal fade" id="kontakModalfront" tabindex="-1" aria-labelledby="menumoLabel" aria-hidden="true" data-backdrop="false">
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

            <div class="quickmobilem-item">
              <a href="<?= site_url('produk/scan_qr') ?>" class="qcard d-block text-decoration-none" aria-label="Makan di Sini (Scan QR)" data-menuloading="1">
                <div class="menu-circle" style="background:#2ecc71;"><span class="emoji-icon" aria-hidden="true">🍽️</span></div>
                <small class="menu-label">Makan di Sini</small>
              </a>
            </div>

            <div class="quickmobilem-item">
              <a href="<?= site_url('produk/delivery') ?>" class="qcard d-block text-decoration-none" aria-label="Antar / Delivery" data-menuloading="1">
                <div class="menu-circle" style="background:#3498db;"><span class="emoji-icon" aria-hidden="true">🚚</span></div>
                <small class="menu-label">Antar / Delivery</small>
              </a>
            </div>

            <div class="quickmobilem-item">
              <a href="<?= site_url('produk/walkin') ?>" class="qcard d-block text-decoration-none" aria-label="Bungkus (Walk-in)" data-menuloading="1">
                <div class="menu-circle" style="background:#9b59b6;"><span class="emoji-icon" aria-hidden="true">🛍️</span></div>
                <small class="menu-label">Bungkus</small>
              </a>
            </div>

            <div class="quickmobilem-item">
              <a href="<?= site_url('billiard') ?>" class="qcard d-block text-decoration-none" aria-label="Booking Billiard" data-menuloading="1">
                <div class="menu-circle" style="background:#17a2b8;"><span class="emoji-icon" aria-hidden="true">🎱</span></div>
                <small class="menu-label">Booking Billiard</small>
              </a>
            </div>

            <div class="quickmobilem-item">
              <a href="<?= site_url('hal/jadwal_billiard') ?>" class="qcard d-block text-decoration-none" aria-label="Lihat Meja Billiard" data-menuloading="1">
                <div class="menu-circle" style="background:#1abc9c;"><span class="emoji-icon" aria-hidden="true">👀</span></div>
                <small class="menu-label">Lihat Meja</small>
              </a>
            </div>

            <div class="quickmobilem-item">
              <a href="<?= site_url('billiard/daftar_booking') ?>" class="qcard d-block text-decoration-none" aria-label="List Bookingan Billiard" data-menuloading="1">
                <div class="menu-circle" style="background:#e67e22;"><span class="emoji-icon" aria-hidden="true">📋</span></div>
                <small class="menu-label">List Bookingan</small>
              </a>
            </div>

            <div class="quickmobilem-item">
              <a href="<?= site_url('billiard/daftar_voucher') ?>" class="qcard d-block text-decoration-none" aria-label="Gratis Main Billiard" data-menuloading="1">
                <div class="menu-circle" style="background:#d81b60;"><span class="emoji-icon" aria-hidden="true">🎁</span></div>
                <small class="menu-label">Gratis Main</small>
              </a>
            </div>

            <div class="quickmobilem-item">
              <a href="<?= site_url('hal/jadwal') ?>" class="qcard d-block text-decoration-none" aria-label="Jadwal / Nongki" data-menuloading="1">
                <div class="menu-circle" style="background:#dc7633;"><span class="emoji-icon" aria-hidden="true">📅</span></div>
                <small class="menu-label">Nongki</small>
              </a>
            </div>

            <div class="quickmobilem-item">
              <a href="<?= site_url('hal/kontak') ?>" class="qcard d-block text-decoration-none" aria-label="Kontak" data-menuloading="1">
                <div class="menu-circle" style="background:#25D366;"><span class="emoji-icon" aria-hidden="true">☎️</span></div>
                <small class="menu-label">Kontak</small>
              </a>
            </div>

            <div class="quickmobilem-item">
              <a href="<?= site_url('hal/pengumuman') ?>" class="qcard d-block text-decoration-none" aria-label="Pengumuman" data-menuloading="1">
                <div class="menu-circle" style="background:#e74c3c;"><span class="emoji-icon" aria-hidden="true">📣</span></div>
                <small class="menu-label">Pengumuman</small>
              </a>
            </div>

            <div class="quickmobilem-item">
              <a href="<?= site_url('hal/privacy_policy') ?>" class="qcard d-block text-decoration-none" aria-label="Kebijakan Privasi" data-menuloading="1">
                <div class="menu-circle" style="background:#16a085;"><span class="emoji-icon" aria-hidden="true">🔒</span></div>
                <small class="menu-label">Kebijakan Privasi</small>
              </a>
            </div>

            <div class="quickmobilem-item">
              <a href="<?= site_url('hal') ?>" class="qcard d-block text-decoration-none" aria-label="Syarat dan Ketentuan" data-menuloading="1">
                <div class="menu-circle" style="background:#6c757d;"><span class="emoji-icon" aria-hidden="true">📜</span></div>
                <small class="menu-label">S&amp;K</small>
              </a>
            </div>

          </div>

          <div class="sheet-close-wrap text-center">
            <button type="button"
                id="btnSlideDownClose"
                class="btn btn-sheet-close"
                data-dismiss="modal"
                aria-label="Tutup menu">
              <i class="fas fa-chevron-down" aria-hidden="true"></i>
            </button>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
<script>
(function(){
  // cegah inisialisasi ulang
  if (window.__AUSI_SPINNER_GLOBAL__) return;
  window.__AUSI_SPINNER_GLOBAL__ = true;

  // sisipkan CSS spinner kalau belum ada
  if (!document.getElementById('ausiSpinnerStyle')){
    var st = document.createElement('style');
    st.id = 'ausiSpinnerStyle';
    st.textContent = `
      /* Sembunyikan emoji saat loading */
      .menu-circle.loading .emoji-icon{
        opacity:0;
      }

      /* Spinner putih muter di dalam bulatan */
      .menu-circle.loading::after{
        content:"";
        position:absolute;
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

  // helper kasih class .loading ke bulatan menu di dalam anchor yg diklik
  function activateCircleSpinnerFrom(anchor){
    if (!anchor) return;
    var circle = anchor.querySelector('.menu-circle');
    if (!circle) return;
    if (!circle.classList.contains('loading')){
      circle.classList.add('loading');
    }
  }

  /* ==========================================================
   * CLICK DI QUICKMENU ATAS (id="quickmenu")
   * ========================================================== */
  document.addEventListener('click', function(e){
    // cari <a class="qcard ..."> di dalam #quickmenu
    var card = e.target.closest('#quickmenu .qcard');
    if (!card) return;
    if (document.getElementById('grandong')) {
      return;
    }

    // kasih efek muter di bulatan
    activateCircleSpinnerFrom(card);
    // TIDAK preventDefault -> biar link tetap jalan normal
  }, {passive:true});

  /* ==========================================================
   * CLICK DI MENU MODAL (#kontakModalfront)
   * ========================================================== */
  document.addEventListener('click', function(e){
    // hanya item yg memang mau loading: data-menuloading
    var modalItem = e.target.closest('#kontakModalfront a[data-menuloading]');
    if (!modalItem) return;

    // kalau user ctrl/shift/command atau middle click, biarin buka tab baru
    if (e.metaKey || e.ctrlKey || e.shiftKey || e.which === 2) return;

    // normal click → kasih spinner, lalu redirect manual
    e.preventDefault();

    activateCircleSpinnerFrom(modalItem);

    var href = modalItem.getAttribute('href');
    if (href){
      window.location.href = href;
    }
  }, {passive:false});

})();
</script>

<?php $this->load->view("front_end/app") ?>
<script src="<?= base_url('assets/admin/js/vendor.min.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/app.min.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/sw.min.js') ?>"></script>
<script src="<?= base_url('assets/js/install.js') ?>"></script>
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
