<?php $uri = $this->uri->uri_string(); ?>
<style>
  .navbar-bottom {
    height: 65px;
    border-top: 1px solid #dee2e6;
    background-color: #fff;
    box-shadow: 0 -1px 5px rgba(0,0,0,0.05);
    z-index: 1030;
  }

  .navbar-bottom a {
    font-size: 12px;
    color: #666;
    text-decoration: none;
  }

  .navbar-bottom i {
    font-size: 18px;
  }

  .center-button {
    position: absolute;
    top: -30px;
    left: 50%;
    transform: translateX(-50%);
    background-color: #fff; /* hijau */
    width: 64px;
    height: 64px;
    border-radius: 50%;
    border: 1px solid #dee2e6;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    animation: footerAni 1s infinite;
    z-index: 10;
    background-image: linear-gradient(to right,#fbf003 0%,#009819 100%)!important;
  }

  .center-button .icon-center {
    width: 30px;
    height: 30px;
    object-fit: contain;
  }

  .nav-item {
    flex: 1;
    text-align: center;
  }

  .space-left, .space-right {
    flex: 0.5;
  }

  @keyframes footerAni {
    0% {
        transform: scale(1,1) translateX(-50%)
    }

    50% {
        transform: scale(1.05,1.05) translateX(-48%)
    }
  }
  .navbar-bottom .nav-item a:hover {
    color: #008000 !important; 
  }

  .navbar-bottom .nav-item a:hover i,
  .navbar-bottom .nav-item a:hover span {
    color: #008000 !important;
  }
</style>

<nav class="navbar fixed-bottom navbar-light bg-white shadow-sm d-lg-none navbar-bottom px-0">
  <div class="w-100 d-flex justify-content-between text-center position-relative mx-0 px-0">

    <div class="nav-item">
      <a href="<?= base_url() ?>" class="<?= ($uri == '' || $uri == 'home') ? 'text-primary' : 'text-dark' ?>">
        <i class="fas fa-home d-block mb-1"></i>
        <span class="small">Beranda</span>
      </a>
    </div>

    <div class="nav-item">
      <a href="<?= base_url('tracking') ?>" class="<?= ($uri == 'tracking') ? 'text-primary' : 'text-dark' ?>">
        <i class="fas fa-chart-line d-block mb-1"></i>
        <span class="small">Tracking</span>
      </a>
    </div>

    <div class="space-left"></div>

    <?php $web = $this->om->web_me();
      if ($this->session->userdata("admin_level") == "faskes") {
        $hak = "admin_permohonan/detail_paket/paket_u";
      } else {
        $hak = "hal/permohonan";
      }
    ?>

    <a href="<?= base_url($hak) ?>"
       class="center-button <?= ($uri == $hak) ? 'text-white' : '' ?>"
       style="text-align: center; <?= ($uri == $hak) ? 'background-color: #28a745;' : '' ?>">
       <div>
        <img src="<?php echo base_url('assets/images/') . $web->gambar ?>" alt="Permohonan" style="width: 50px; height: 50px; object-fit: contain; margin-top: 0px;">
      </div>
    </a>


  <div class="space-right"></div>

  <div class="nav-item">
    <a href="<?= base_url('admin_permohonan/detail_paket/paket_u') ?>" class="<?= ($uri == 'admin_permohonan/detail_paket/paket_u') ? 'text-primary' : 'text-dark' ?>">
      <i class="fas fa-clipboard-list d-block mb-1"></i>
      <span class="small">Sabit</span>
    </a>
  </div>

  <div class="nav-item">
    <a class="<?= ($uri == 'hal/kontak' || $uri == 'hal/kontak_capil' || $uri == 'tracking' || $uri == 'hal/permohonan' || $uri == 'hal/alur') ? 'text-primary' : 'text-dark' ?>" data-toggle="modal" data-target="#kontakModal">
      <i class="fas fa-bars d-block mb-1"></i>
      <span class="small">Menu</span>
    </a>
  </div>

</div>
</nav>
<style type="text/css">

  .modal-dialog.modal-bottom {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    width: 100%;
    transform: translateY(100%); 
    transition: transform 0.3s ease-out; 
    margin: 0;
  }

  .modal.fade.show .modal-dialog.modal-bottom {
    transform: translateY(0); 
  }
  .modal-dialog-full {
    max-width: 100%;
  }

  .modal-content-full {
    height: 100%;
    border-radius: 0;
  }


  .modal-content {
    border-radius: 0; 
    width: 100%; 
    margin: 0;
  }

</style>
<div class="modal " id="kontakModal" tabindex="-1" aria-labelledby="menumoLabel" aria-hidden="true">
  <div class="modal-dialog modal-bottom fadeInUp animated modal-dialog-full" style="animation-duration: 0.5s;">
    <div class="modal-content">
     <div class="modal-header bg-primary text-white">
      <h5 class="modal-title d-flex align-items-center text-white" id="menumoLabel">
        <i class="fas fa-concierge-bell mr-2"></i> Menu
      </h5>
      <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>

    <div class="modal-body">
      <a href="<?php echo base_url('hal/permohonan') ?>" style="text-decoration: none;">
        <div class="external-event text-dark ui-draggable ui-draggable-handle" data-class="bg-success" style="position: relative; z-index: auto; font-size: 16px; background: #c7d5ff; font-weight: 600; width: 100%;">
          <i class="fe-file-text mr-1"></i>Syarat Permohonan
        </div>
      </a>

      <a href="<?php echo base_url('hal/alur') ?>" style="text-decoration: none;">
        <div class="external-event text-dark ui-draggable ui-draggable-handle" data-class="bg-success" style="position: relative; z-index: auto; font-size: 16px; background: #c7d5ff; font-weight: 600; width: 100%;">
          <i class="mdi mdi-puzzle mr-1"></i>Alur Permohonan
        </div>
      </a>

      <a href="<?php echo base_url('hal/kontak_capil') ?>" style="text-decoration: none;">
        <div class="external-event text-dark ui-draggable ui-draggable-handle" data-class="bg-success" style="position: relative; z-index: auto; font-size: 16px; background: #c7d5ff; font-weight: 600; width: 100%;">
          <i class="fas fa-phone mr-1"></i>Kontak Dukcapil
        </div>
      </a>
      <a href="<?php echo base_url('hal/kontak') ?>" style="text-decoration: none;">
        <div class="external-event text-dark ui-draggable ui-draggable-handle" data-class="bg-success" style="position: relative; z-index: auto; font-size: 16px; background: #c7d5ff; font-weight: 600; width: 100%;">
          <i class="fas fa-mobile-alt mr-1"></i>Kontak Desa & Dusun
        </div>
      </a>
    </div>
  </div>
</div>
</div>