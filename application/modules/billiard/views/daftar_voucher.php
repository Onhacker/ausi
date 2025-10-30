<?php $this->load->view("front_end/head.php") ?>

<?php
// Helper: sensor no HP
$mask_hp = function($hp){
  $d = preg_replace('/\D+/', '', (string)$hp);
  if ($d === '') return '';
  if (strpos($d, '62') === 0) $d = '0'.substr($d, 2);
  $len = strlen($d);
  if ($len <= 6) return substr($d, 0, 2) . str_repeat('•', max(0, $len - 2));
  return substr($d, 0, 4) . ' ' . str_repeat('•', max(4, $len - 6)) . ' ' . substr($d, -2);
};

// Helper: sensor kode voucher (3 awal, 2 akhir; pertahankan delimiter)
$mask_code = function($code){
  $s = (string)$code;
  preg_match_all('/[A-Za-z0-9]/', $s, $m);
  $total = count($m[0]);
  $out = ''; $seen = 0; $keep_head = 3; $keep_tail = 2;
  $chars = preg_split('//u', $s, -1, PREG_SPLIT_NO_EMPTY);
  foreach ($chars as $ch){
    if (preg_match('/[A-Za-z0-9]/', $ch)){
      $out .= ($seen < $keep_head || $seen >= ($total - $keep_tail)) ? $ch : '•';
      $seen++;
    } else {
      $out .= $ch;
    }
  }
  return $out;
};

// Ambil angka promo dari controller/web_me
$threshold  = (int)($rec->batas_edit ?? 10);   // berapa kali main → 1 voucher
$valid_days = (int)($batas_hari ?? 30);        // masa berlaku voucher dari created_at
?>

<div class="container-fluid">
  <div class="hero-title" role="banner" aria-label="Judul situs">
    <h1 class="text"><?php echo $title ?></h1>
    <span class="accent" aria-hidden="true"></span>
  </div>
  <!-- ALERT PROMO (bahasa gaul) -->
      <div class="alert alert-warning shadow-sm border-0" role="alert">
        <div class="d-flex align-items-start">
          <div class="mr-3 d-none d-sm-flex align-items-center justify-content-center rounded-circle bg-white" style="width:42px;height:42px;">
            <!-- ikon tiket mini -->
          
          </div>
          <div>

            <div class="font-weight-bold mb-1">Gift Voucher Free Main 🎟️</div>
            <div class="mb-0">
              Main <b><?= (int)$threshold ?></b>x  langsung dapet <b>1 voucher Free Main Billiard selama <?php echo $rec->late_min." menit" ?></b>. 
              Voucher berlaku <b><?= (int)$valid_days ?></b> hari sejak dibuat. Gaskeun rajin main, kumpulin poinnya! 🔥
            </div>
          </div>
        </div>
      </div>
      <!-- END ALERT PROMO -->
  <div class="row">
    <div class="col-lg-12">
      <!-- <div class="search-result-box card-box"> -->

        <?php if (empty($vouchers)): ?>
          <!-- Empty state yang lebih cantik -->
          <div class="card-box">
          <div class="text-center ">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light mb-3" style="width:92px;height:92px;">
              <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-label="Voucher" role="img">
                <!-- Bentuk tiket -->
                <path d="M3 5h18a2 2 0 0 1 2 2v2a3 3 0 0 0 0 6v2a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-2a3 3 0 0 0 0-6V7a2 2 0 0 1 2-2z"></path>
                <!-- Simbol persen -->
                <path d="M9 9h.01"></path>
                <path d="M15 15h.01"></path>
                <path d="M15 9l-6 6"></path>
              </svg>
            </div>

            <h4 class="mb-1">Belum ada voucher aktif</h4>
            <p class="text-dark mb-3">
              Main terus & kumpulkan transaksi <em>terkonfirmasi</em> buat dapetin voucher <strong>Free Main</strong> ✨
            </p>
            <div class="mb-2">
              <a href="<?= site_url('billiard') ?>" class="btn btn-blue mr-2">Booking Sekarang</a>
              <a href="<?= site_url('billiard/daftar_booking') ?>" class="btn btn-outline-secondary">Lihat List Booking</a>
            </div>
            <div class="small text-dark">
              Voucher baru bakal dikirim via WhatsApp & muncul di halaman ini.
            </div>
          </div>
        </div>
        <?php else: ?>
          <div class="row">
            <?php foreach ($vouchers as $v): ?>
              <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="card-box bg-pattern" style="padding:16px;">
                  <div class="d-flex align-items-start justify-content-between mb-1">
                    <h4 class="mb-0 font-18 text-truncate" title="<?= html_escape($v->nama) ?>">
                      <?= html_escape($v->nama) ?>
                    </h4>
                    <?php if ($v->is_expired): ?>
                      <span class="badge badge-danger">Expired</span>
                    <?php else: ?>
                      <span class="badge badge-success">Aktif</span>
                    <?php endif; ?>
                  </div>

                  <div class="text-muted small mb-2">
                    <?= html_escape($mask_hp($v->no_hp)) ?>
                  </div>

                  <div class="text-center mb-2">
                    <span class="badge badge-dark px-3 py-2" style="font-size:.9rem;letter-spacing:.4px;">
                      <?= html_escape($mask_code($v->kode)) ?>
                    </span>
                  </div>

                  <div class="d-flex text-center mt-2">
                    <div class="flex-fill px-2">
                      <div class="text-muted small mb-1">Dibuat</div>
                      <div class="font-weight-bold" style="font-size:.95rem;">
                        <?= html_escape($v->created_at) ?>
                      </div>
                    </div>
                    <div class="flex-fill px-2">
                      <div class="text-muted small mb-1">Expired</div>
                      <div class="font-weight-bold <?= $v->is_expired ? 'text-danger' : '' ?>" style="font-size:.95rem;">
                        <?= html_escape($v->expired_at) ?>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      <!-- </div> -->
    </div>
  </div>
</div>

<?php $this->load->view("front_end/footer.php") ?>
