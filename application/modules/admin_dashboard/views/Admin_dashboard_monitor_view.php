<?php
$base_title = htmlspecialchars($title ?? 'Layar Utama', ENT_QUOTES, 'UTF-8');
$base_sub   = htmlspecialchars($subtitle ?? 'Daftar Booking & Sedang Berkunjung', ENT_QUOTES, 'UTF-8');
?>


<div class="container-fluid" id="fsContainer">
  <!-- Header -->
  <div class="row">
    <div class="col-12">
      <div class="page-title-box">
        <div class="page-title-right">
          <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><?= $base_title ?></li>
            <li class="breadcrumb-item active"><?= $base_sub ?></li>
          </ol>
        </div>
        <h4 class="page-title d-flex align-items-center" style="gap:.5rem;">
          <?= $base_sub ?>
          <button id="btnFullscreen" class="btn btn-sm btn-outline-dark ml-2" type="button" title="Fullscreen">
            <i class="mdi mdi-fullscreen"></i> Fullscreen
          </button>
        </h4>
      </div>
    </div>
  </div>
<?php $this->load->view("_monitor") ?>
