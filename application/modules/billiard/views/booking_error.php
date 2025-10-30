<?php $this->load->view("front_end/head.php") ?>

<div class="container-fluid">
  <div class="row mt-3">
    <div class="col-lg-12">
      <div class="search-result-box card-box">

        <div class="text-center py-5">
          <div class="display-3 mb-3" aria-hidden="true">⏰</div>
          <h1 class="h3 mb-1">Yah, telat nih 😅</h1>

          <p class="text-muted mb-3">
            <?php echo html_escape($deskripsi ?? 'Batas waktu pembayaran sudah lewat. Booking kamu auto-dicancel biar slotnya kebuka lagi.'); ?>
          </p>

          <div class="small text-muted mb-4">
            Masih pengin lanjut? Bikin booking baru aja yaa~
          </div>

          <a href="<?= site_url('billiard') ?>" class="btn btn-primary me-2">
            <i class="mdi mdi-clipboard-list-outline"></i> Coba Booking Lagi
          </a>
          <a href="<?= site_url() ?>" class="btn btn-outline-secondary ml-1">
            <i class="mdi mdi-home-outline"></i> Ke Beranda
          </a>
        </div>

      </div>
    </div>
  </div>
</div>

<?php $this->load->view("front_end/footer.php") ?>
