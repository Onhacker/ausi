<?php $this->load->view("front_end/head.php") ?>
<div class="container-fluid">

  <div class="row mt-3">
    <div class="col-lg-12">
      <div class="search-result-box card-box">

        <div class="text-center py-5">
          <h4 class="mb-2"><?php echo $deskripsi ?></h4>
          <!-- <p class="text-muted"><?php echo $deskripsi ?></p> -->
          <a href="<?= site_url('booking') ?>" class="btn btn-primary">Kembali ke Form Booking</a>
        </div>

      </div>
    </div>
  </div>
</div>



<?php $this->load->view("front_end/footer.php") ?>
