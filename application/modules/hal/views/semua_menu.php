<?php $this->load->view("front_end/head.php"); ?>
<div class="container-fluid">
  <div class="hero-title" role="banner" aria-label="Judul pengumuman">
    <h1 class="text"><?= htmlspecialchars($title) ?></h1>
    <span class="accent" aria-hidden="true"></span>
  </div>
<style>
  /* MOBILE-FIRST: 2 kolom */
  #quickmenu{
    display: grid !important;            /* override d-flex */
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    overflow: visible;
  }

  /* Kartu & konten rata tengah */
  #quickmenu .quickmenu-item{ display:flex; }
  #quickmenu .qcard{
    width:100%;
    display:flex; flex-direction:column; align-items:center;
    text-align:center; gap:8px;
    padding:12px 8px; border-radius:14px;
    background:#f8f9fa; border:1px solid #eee;
    transition: transform .2s ease, box-shadow .2s ease;
  }
  #quickmenu .qcard:hover{ transform:translateY(-2px); box-shadow:0 6px 14px rgba(0,0,0,.08); }

  /* Ikon bulat */
  #quickmenu .menu-circle{
    width:56px; height:56px; margin:0 auto;
    border-radius:50%; display:flex; align-items:center; justify-content:center;
    color:#fff; box-shadow:0 4px 10px rgba(0,0,0,.12);
  }
  #quickmenu .emoji-icon{ font-size:24px; line-height:1; }

  /* Label */
  #quickmenu .menu-label{
    display:block; width:100%; text-align:center;
    margin-top:2px; font-weight:600; font-size:12px; color:#34495e;
  }

  /* SUPER KECIL (opsional) */
  @media (max-width: 360px){
    #quickmenu .menu-circle{ width:50px; height:50px; }
    #quickmenu .emoji-icon{ font-size:22px; }
    #quickmenu .menu-label{ font-size:11px; }
  }

  /* LAPTOP/desktop: 4 kolom (Bootstrap lg ≥ 992px) */
  @media (min-width: 992px){
    #quickmenu{ grid-template-columns: repeat(4, 1fr); gap: 16px; }
  }
</style>



  <div class="row mt-1">
    <div class="col-lg-12">
      <div class="card-box p-3">
        <div id="quickmenu" class="quickmenu-scroll d-flex text-center" tabindex="0" aria-label="Menu cepat geser">
          <div class="quickmenu-item">
            <a href="<?= site_url('booking') ?>" class="qcard d-block text-decoration-none">
              <div class="menu-circle" style="background:#17a2b8;"><span class="emoji-icon">📅</span></div>
              <small class="menu-label">Booking</small>
            </a>
          </div>

          <div class="quickmenu-item">
            <a href="<?= site_url('hal/jadwal') ?>" class="qcard d-block text-decoration-none">
              <div class="menu-circle" style="background:#dc7633;"><span class="emoji-icon">⏰</span></div>
              <small class="menu-label">Jadwal</small>
            </a>
          </div>

          <div class="quickmenu-item">
            <a href="<?= site_url('hal/pengumuman') ?>" class="qcard d-block text-decoration-none">
              <div class="menu-circle" style="background:#e74c3c;"><span class="emoji-icon">📣</span></div>
              <small class="menu-label">Pengumuman</small>
            </a>
          </div>

          <div class="quickmenu-item">
            <a href="<?= site_url('hal/struktur') ?>" class="qcard d-block text-decoration-none">
              <div class="menu-circle" style="background:#8e44ad;"><span class="emoji-icon">🏛️</span></div>
              <small class="menu-label">Struktur</small>
            </a>
          </div>

          <div class="quickmenu-item">
            <a href="<?= site_url('hal/alur') ?>" class="qcard d-block text-decoration-none">
              <div class="menu-circle" style="background:#007bff;"><span class="emoji-icon">🧭</span></div>
              <small class="menu-label">Tahapan</small>
            </a>
          </div>

          <div class="quickmenu-item">
            <a href="<?= site_url('hal/panduan') ?>" class="qcard d-block text-decoration-none">
              <div class="menu-circle" style="background:#3498db;"><span class="emoji-icon">📘</span></div>
              <small class="menu-label">Panduan</small>
            </a>
          </div>

          <div class="quickmenu-item">
            <a href="<?= site_url('hal/kontak') ?>" class="qcard d-block text-decoration-none">
              <div class="menu-circle" style="background:#25D366;"><span class="emoji-icon">📞</span></div>
              <small class="menu-label">Kontak</small>
            </a>
          </div>

          <div class="quickmenu-item">
            <a href="<?= site_url('hal/privacy_policy') ?>" class="qcard d-block text-decoration-none">
              <div class="menu-circle" style="background:#16a085;"><span class="emoji-icon">🔒</span></div>
              <small class="menu-label">Kebijakan Privasi</small>
            </a>
          </div>

          <div class="quickmenu-item">
            <a href="<?= site_url('hal') ?>" class="qcard d-block text-decoration-none">
              <div class="menu-circle" style="background:#6c757d;"><span class="emoji-icon">📄</span></div>
              <small class="menu-label">S&K</small>
            </a>
          </div>

         
        </div>

      </div>
    </div>
  </div>
</div>

<?php $this->load->view("front_end/footer.php"); ?>
