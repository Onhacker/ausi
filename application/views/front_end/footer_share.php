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
    window.open(shareUrl, "_blank", "noopener"); // aman
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
        <p class="boxed-text-l text-center mb-1">
          <?php echo strtoupper($rec->type) ?>
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
  // Ganti package kalau beda
          $playPackage = 'org.silaturahmi.twa';
          $playUrl     = 'https://play.google.com/store/apps/details?id=' . $playPackage;
          ?>
          <!-- Tambah di <head> atau CSS-mu -->
           
            <div class="text-center store-badges">
              <!-- Badge resmi Google Play -->
              <a id="playstoreBadge"
              href="<?= $playUrl ?>"
              onclick="return openPlayStore(event)"
              class="d-inline-block my-2"
              aria-label="Download di Google Play">
              <img alt="Download di Google Play"
              src="<?php echo base_url('assets/images/gp.webp') ?>"
              style="height:56px;width:auto;">
            </a>

            <!-- Badge iOS / PWA -->
            <a id="installButton"
            href="#"
            class="d-inline-block my-2 ms-2 ml-2"
            aria-label="Instal ke iOS (PWA)">
            <img alt="Instal ke iOS (Tambahkan ke Layar Utama)"
            src="<?= base_url('assets/images/ios.webp') ?>"
            style="height:56px;width:auto;">
          </a>
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
        <div class="row text-center  mb-3">
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