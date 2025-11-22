<?php $this->load->view("front_end/head.php"); ?>
<div class="container-fluid">
  <div class="hero-title" role="banner" aria-label="Judul situs">
    <h1 class="text"><?= htmlspecialchars($title) ?></h1>
    <span class="accent" aria-hidden="true"></span>
  </div>

  <div class="row mt-2">
    <div class="col-lg-12">
      <!-- <div class="card-box p-3"> -->
       
        <?php $this->load->view("banner_ps") ?>

      <!-- </div> -->
    </div>
  </div>
</div>

<?php $this->load->view("front_end/footer.php"); ?>
