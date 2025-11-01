<?php $this->load->view("front_end/head.php") ?>
<link href="<?php echo base_url('assets/min/home.min.css'); ?>" rel="stylesheet" type="text/css" />
<?php
$slides = [
  [
    'src'   => base_url("assets/images/slide/booking.webp"),
    'alt'   => 'Area Cafe',
    'title' => 'Booking Online',
    'text'  => 'Langsung booking meja billiard lewat HP, gampang banget!',
    'href'  => site_url('billiard'),

  ],
  
  [
    'src'   => base_url("assets/images/slide/laptop.webp"),
    'alt'   => 'Download Aplikasi Di Playstore atau Gunakan Browser',
    'title' => 'Ramah Akses',
    'text'  => 'Order dengan Scan Barcode di Meja',
    'href'  => site_url(),
  ],
  [
    'src'   => base_url("assets/images/slide/unit.webp"),
    'alt'   => 'Siap Menyambut Anda',
    'title' => 'Siap Menyambut Anda',
    'text'  => 'Layanan prima',
    'href'  => site_url('produk'),
  ],
];
?>


<div class="container-fluid">

  <!-- ===== Pretty Title ===== -->
 <!--  <div class="hero-title" role="banner" aria-label="Judul situs">
    <h1 class="text">Chill Corner</h1>
      <div class="text-muted">Tempat nongkrong & main billiard hits buat ngumpul bareng</div>
    <span class="accent" aria-hidden="true"></span>
  </div> -->
  <!-- ===== /Pretty Title ===== -->

  <div class="row">
    <!-- LEFT: HERO -->
    <div class="col-xl-4">
      <section class="pwa-hero" role="region" aria-label="Slideshow sorotan">
        <button class="pwa-hero__nav prev" type="button" aria-label="Sebelumnya">‹</button>
        <button class="pwa-hero__nav next" type="button" aria-label="Berikutnya">›</button>

        <div id="heroTrack" class="pwa-hero__track" tabindex="0" aria-live="polite">
          <?php foreach ($slides as $i => $s): ?>
            <article class="pwa-hero__slide" aria-roledescription="slide" aria-label="<?= ($i+1).' dari '.count($slides) ?>">
              <?php if (!empty($s['href'])): ?><a href="<?= htmlspecialchars($s['href'], ENT_QUOTES) ?>" class="pwa-hero__link"><?php endif; ?>
                <img class="pwa-hero__img"
                     src="<?= htmlspecialchars($s['src'], ENT_QUOTES) ?>"
                     alt="<?= htmlspecialchars($s['alt'], ENT_QUOTES) ?>"
                     loading="<?= $i === 0 ? 'eager' : 'lazy' ?>"
                     decoding="async" />
                <div class="pwa-hero__cap">
                  <h3 class="pwa-hero__title"><?= htmlspecialchars($s['title']) ?></h3>
                  <p class="pwa-hero__text"><?= htmlspecialchars($s['text']) ?></p>
                </div>
              <?php if (!empty($s['href'])): ?></a><?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>

        <div id="heroDots" class="pwa-hero__dots" aria-hidden="false"></div>
      </section>
    </div>

    <!-- RIGHT: RIBBON + QUICK MENU -->
    <div class="col-xl-8">
      <div class="quickmenu-wrap position-relative">
        <button class="quickmenu-btn left" type="button" aria-label="Geser kiri">&#10094;</button>
        <button class="quickmenu-btn right" type="button" aria-label="Geser kanan">&#10095;</button>
        <div class="quickmenu-fade left"></div>
        <div class="quickmenu-fade right"></div>

      <div id="quickmenu" class="quickmenu-scroll d-flex text-center" tabindex="0" aria-label="Menu cepat geser">
   <div class="quickmenu-item">
    <a href="<?= site_url('produk/scan_qr') ?>" class="qcard d-block text-decoration-none">
      <div class="menu-circle" style="background:#007bff;"><span class="emoji-icon">🍽️</span></div>
      <small class="menu-label">Makan Dicafe</small>
    </a>
  </div>
  <div class="quickmenu-item">
    <a href="<?= site_url('produk/delivery') ?>" class="qcard d-block text-decoration-none">
      <div class="menu-circle" style="background:#17a2b8;"><span class="emoji-icon">🚚</span></div>
      <small class="menu-label">Pesan Antar</small>
    </a>
  </div>

  <div class="quickmenu-item">
    <a href="<?= site_url('produk/walkin') ?>" class="qcard d-block text-decoration-none">
      <div class="menu-circle" style="background:#e74c3c;"><span class="emoji-icon">🍱</span></div>
      <small class="menu-label">Bungkus</small>
    </a>
  </div>

 

  <div class="quickmenu-item">
    <a href="<?= site_url('billiard') ?>" class="qcard d-block text-decoration-none">
      <div class="menu-circle" style="background:#25D366;"><span class="emoji-icon">🎱</span></div>
      <small class="menu-label">Book Meja</small>
    </a>
  </div>

  <div class="quickmenu-item">
    <a href="#" data-toggle="modal" data-target="#kontakModalfront" class="qcard d-block text-decoration-none">
      <div class="menu-circle" style="background:#6f42c1;"><span class="emoji-icon">🗂️</span></div>
      <small class="menu-label">Liat Semua</small>
    </a>
  </div>
</div>

      </div>
       <?php $this->load->view("front_end/banner_jadwal.php"); ?>
    </div>
  </div>
      <div class="row mt-2">
        <?php
          $minBelanja   = (int)($rec->batas_free_ongkir ?? 0);
          $radiusKm     = (float)(($rec->max_radius_m ?? 0) / 1000);
          $fmtBelanja   = 'Rp'.number_format($minBelanja, 0, ',', '.');
          // Tampil 0 desimal kalau bulat, 1 desimal kalau perlu
          $fmtRadiusKm  = fmod($radiusKm, 1.0) == 0.0 ? number_format($radiusKm, 0, ',', '.') : number_format($radiusKm, 1, ',', '.');
        ?>
        <div class="col-lg-12">
          <div class="card-box text-center p-3 free-ongkir-card">
            <div class="free-ongkir-icon mb-1" aria-hidden="true">
              <i class="mdi mdi-truck-fast h1 m-0 text-warning"></i>
            </div>

            <h3 class="mb-2">Gratis Ongkir!</h3>

            <div class="mb-2 chip-row">
              <span class="badge-chip mr-2">
                <i class="mdi mdi-cash-multiple mr-1"></i> Min. order <b><?= $fmtBelanja ?></b>
              </span>
              <span class="badge-chip">
                <i class="mdi mdi-radar mr-1"></i> Radius <b><?= $fmtRadiusKm ?> km</b>
              </span>
            </div>


            <p class="text-dark mb-1">
              Dapetin <b>free ongkir</b> kalo order delivery tembus minimal <?= $fmtBelanja ?>, dan alamat kamu masih
              di area <b>±<?= $fmtRadiusKm ?> km</b> dari <?php echo $rec->nama_website ?>. Gas checkout, ya! 😉
            </p>

            <a href="<?= site_url('produk/delivery') ?>" class="btn btn-blue waves-effect waves-light mt-2">
              <i class="mdi mdi-truck-delivery"></i> Pesan Antar Sekarang
            </a>

           
          </div>
        </div>

<style>
  .free-ongkir-card{
    border-radius: 16px;
    position: relative;
    overflow: hidden;
    background: #fff;
  }
  .free-ongkir-card::before{
    /* aksen lembut di pojok */
    content:"";
    position:absolute; right:-60px; top:-60px;
    width:180px; height:180px;
    background: radial-gradient(closest-side, rgba(40,167,69,.15), transparent 70%);
    transform: rotate(15deg);
  }
  .free-ongkir-icon {
    width: 72px;
    height: 72px;
    border-radius: 999px;
    display: inline-flex
;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    background: linear-gradient(135deg, #277295, #20c997);
    box-shadow: 0 8px 18px rgba(32, 201, 151, .25);
}
  .badge-chip {
    display: inline-block;
    padding: .2rem .4rem;
    border-radius: 999px;
    font-weight: 600;
    background: #f4fff8;
    border: 1px dashed #28a745;
    color: #1b5e20;
}
  .badge-chip i{ vertical-align: -2px; }
  /* kiri–kanan + responsif wrap */
.chip-row{
  display:flex;
  justify-content:space-between;  /* kiri–kanan */
  align-items:center;
  flex-wrap:wrap;                 /* biar rapih di layar kecil */
}
.chip-row .badge-chip{ margin-bottom:.25rem; }

</style>

</div>




    <div class="feature-slider" id="featureSlider">
      <button class="fs-nav prev" type="button" aria-label="Sebelumnya">‹</button>

      <ul class="sortable-list taskList list-unstyled ui-sortable fs-track" id="upcoming" tabindex="0" aria-live="polite">
        <li class="media mb-2 align-items-center">
  <div class="avatar-sm rounded-circle bg-soft-info text-info mr-3 d-flex align-items-center justify-content-center">
    <i class="fas fa-qrcode font-20"></i>
  </div>
  <div class="media-body">
    <h4 class="mt-0 mb-1"><strong>QR Meja</strong></h4>
    <p>Duduk langsung Dine-in tinggal scan QR meja. Pesanan otomatis masuk tanpa harus antri.</p>
  </div>
</li>
        <li class="media mb-2 align-items-center">
  <div class="avatar-sm rounded-circle bg-soft-primary text-primary mr-3 d-flex align-items-center justify-content-center">
    <i class="fas fa-bolt font-20"></i>
  </div>
  <div class="media-body">
    <h4 class="mt-0 mb-1"><strong>Booking Billiard Gampang</strong></h4>
    <p>Pesan meja atau jam main tinggal klik—langsung dari HP atau laptop, tanpa ribet.</p>
  </div>
</li>

<li class="media mb-2 align-items-center">
  <div class="avatar-sm rounded-circle bg-soft-success text-success mr-3 d-flex align-items-center justify-content-center">
    <i class="fas fa-calendar-check font-20"></i>
  </div>
  <div class="media-body">
    <h4 class="mt-0 mb-1"><strong>Pilih Waktu Sendiri</strong></h4>
    <p>Mau nongki siang atau malam? Tentuin sendiri jam main atau ngopi santai, gampang diatur ulang kalau berubah rencana.</p>
  </div>
</li>



<li class="media mb-2 align-items-center">
  <div class="avatar-sm rounded-circle bg-soft-warning text-warning mr-3 d-flex align-items-center justify-content-center">
    <i class="fas fa-bell font-20"></i>
  </div>
  <div class="media-body">
    <h4 class="mt-0 mb-1"><strong>Pengingat Otomatis</strong></h4>
    <p>Dapet notifikasi WA, Email, atau SMS biar nggak lupa jam main. Langsung diingatkan otomatis.</p>
  </div>
</li>

<li class="media mb-2 align-items-center">
  <div class="avatar-sm rounded-circle bg-soft-danger text-danger mr-3 d-flex align-items-center justify-content-center">
    <i class="fas fa-stopwatch font-20"></i>
  </div>
  <div class="media-body">
    <h4 class="mt-0 mb-1"><strong>Anti Antri Panjang</strong></h4>
    <p>Dengan jadwal terencana, datang tinggal duduk. Main atau ngopi langsung—waktu tunggu minim!</p>
  </div>
</li>

<li class="media mb-2 align-items-center">
  <div class="avatar-sm rounded-circle bg-soft-dark text-dark mr-3 d-flex align-items-center justify-content-center">
    <i class="fas fa-mobile-alt font-20"></i>
  </div>
  <div class="media-body">
    <h4 class="mt-0 mb-1"><strong>HP & Browser Ready</strong></h4>
    <p>Pakai aplikasi atau browser favorit, semua bisa. Order makanan/minuman, booking meja, sampai bayar tinggal klik aja.</p>
  </div>
</li>

<li class="media mb-2 align-items-center">
  <div class="avatar-sm rounded-circle bg-soft-secondary text-secondary mr-3 d-flex align-items-center justify-content-center">
    <i class="fas fa-shield-alt font-20"></i>
  </div>
  <div class="media-body">
    <h4 class="mt-0 mb-1"><strong>Data Aman</strong></h4>
    <p>Info pribadimu dijaga aman. QR unik biar nggak ada yang nyolong slot meja atau tiketmu.</p>
  </div>
</li>

<li class="media mb-2 align-items-center">
  <div class="avatar-sm rounded-circle bg-soft-primary text-primary mr-3 d-flex align-items-center justify-content-center">
    <i class="fas fa-headset font-20"></i>
  </div>
  <div class="media-body">
    <h4 class="mt-0 mb-1"><strong>Bantuan Cepat</strong></h4>
    <p>Kendala booking atau soal meja? Tim kami siap bantu, tinggal chat atau telpon aja.</p>
  </div>
</li>

      </ul>

      <button class="fs-nav next" type="button" aria-label="Berikutnya">›</button>
    </div>
  </section>



<?php $this->load->view("front_end/banner_billiard") ?>



</div>

<script src="<?= base_url('assets/admin/js/sw.min.js') ?>"></script>
<script src="<?= base_url('assets/min/home.min.js') ?>"></script>
<?php $basePath = parse_url(site_url(), PHP_URL_PATH); if (!$basePath) $basePath = '/'; ?>

<?php $this->load->view("front_end/footer.php") ?>

