<?php $this->load->view("front_end/head.php"); ?>
<div class="container-fluid">
  <div class="hero-title" role="banner" aria-label="Judul pengumuman">
    <h1 class="text"><?= htmlspecialchars($title) ?></h1>
    <span class="accent" aria-hidden="true"></span>
  </div>

  <div class="row mt-3">
    <div class="col-lg-12">
      <div class="card-box p-3">
        <div class="mb-2 text-muted">
          <i class="fe-calendar"></i>
          <?= date('d M Y', strtotime($item->tanggal)) ?>
          &nbsp;•&nbsp;
          <i class="fe-user"></i> <?= htmlspecialchars($item->username) ?>
        </div>

        <article class="pgm-content">
          <?= $item->isi // HTML dari admin (dipercaya). Jika perlu, sanitasi sesuai kebijakan Anda. ?>
        </article>

        <hr>
        <a class="btn btn-light" href="<?= site_url('hal/pengumuman') ?>"><i class="fe-arrow-left"></i> Ke Semua Pengumuman</a>
      </div>
    </div>
  </div>
</div>

<style>
  .pgm-content img{max-width:100%;height:auto}
  .pgm-content table{width:100%;border-collapse:collapse}
  .pgm-content table, .pgm-content th, .pgm-content td{border:1px solid #e5e7eb}
  .pgm-content th, .pgm-content td{padding:8px}

  /* — line-height 0 sesuai permintaan — */
  .pgm-content p,
  .pgm-content li,
  .pgm-content td,
  .pgm-content th,
  .pgm-content div {
    line-height: 20px !important;
  }
</style><?php $this->load->view("front_end/footer.php"); ?>
