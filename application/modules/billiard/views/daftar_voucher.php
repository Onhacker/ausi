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

<style>
  /* kotak info voucher (garis putus-putus) */
  .voucher-hint{
    border:2px dashed rgba(0,0,0,.25);
    border-radius:12px;
    background:#fafafa;
    padding:12px 16px;
    font-size:.9rem;
    line-height:1.4;
  }
  .voucher-hint strong{
    font-weight:600;
  }
  .voucher-hint .badge-step{
    display:inline-block;
    min-width:22px;
    height:22px;
    line-height:22px;
    font-size:.7rem;
    font-weight:600;
    text-align:center;
    border-radius:999px;
    background:#000;
    color:#fff;
    margin-right:6px;
  }

  /* kartu voucher */
  .voucher-card{
    border-radius:16px;
    background:#fff;
    border:1px solid rgba(0,0,0,.07);
    box-shadow:0 8px 20px rgba(0,0,0,.05);
    position:relative;
    overflow:hidden;
  }

  .voucher-status-badge.badge-success{
    background:#28a745;
    color:#fff;
  }
  .voucher-status-badge.badge-danger{
    background:#dc3545;
    color:#fff;
  }

  .voucher-code-badge{
    display:inline-block;
    background:#000;
    color:#fff;
    border-radius:10px;
    font-size:.9rem;
    letter-spacing:.4px;
    padding:.5rem .75rem;
    font-weight:600;
  }

  /* dekorasi sobekan tiket kiri/kanan */
  .voucher-card:before,
  .voucher-card:after{
    content:"";
    position:absolute;
    top:50%;
    width:20px;
    height:20px;
    background:#f8f9fa;
    border:1px solid rgba(0,0,0,.07);
    border-radius:50%;
    transform:translateY(-50%);
  }
  .voucher-card:before{ left:-10px; }
  .voucher-card:after{ right:-10px; }

  /* tombol utama biru */
  .btn-blue{
    background:linear-gradient(90deg,#005bea,#00c6fb);
    color:#fff;
    border:0;
    font-weight:600;
    border-radius:10px;
  }
  .btn-blue:hover,
  .btn-blue:focus{
    color:#fff;
    filter:brightness(.9);
  }

  .text-dim{
    color:rgba(0,0,0,.6);
  }
</style>

<div class="container-fluid">
  <div class="hero-title" role="banner" aria-label="Judul situs">
    <h1 class="text"><?= html_escape($title) ?></h1>
    <span class="accent" aria-hidden="true"></span>
  </div>

  <div class="row">
    <div class="col-lg-12">

      <?php if (empty($vouchers)): ?>
        <!-- STATE: TIDAK ADA VOUCHER -->
        <div class="card-box text-center" style="border-radius:16px;border:1px solid rgba(0,0,0,.07);box-shadow:0 8px 20px rgba(0,0,0,.05);">
          <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light mb-3" style="width:92px;height:92px;">
            <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-label="Voucher" role="img">
              <path d="M3 5h18a2 2 0 0 1 2 2v2a3 3 0 0 0 0 6v2a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-2a3 3 0 0 0 0-6V7a2 2 0 0 1 2-2z"></path>
              <path d="M9 9h.01"></path>
              <path d="M15 15h.01"></path>
              <path d="M15 9l-6 6"></path>
            </svg>
          </div>

          <h4 class="mb-1 font-weight-bold">Belum ada voucher aktif</h4>
          <p class="text-dim mb-3" style="max-width:360px;margin:0 auto;">
            Main terus dan selesaikan transaksi (lunas / sudah selesai). Setelah memenuhi syarat, kamu akan dapat voucher <strong>Free Main</strong> 🤘
          </p>

          <!-- Cara klaim voucher (tetap ditampilkan di empty state) -->
          <div class="voucher-hint text-left mx-auto mb-3" style="max-width:420px;">
            <div class="mb-2 font-weight-bold" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;">
              Cara pakai voucher
            </div>

            <div class="d-flex mb-2">
              <div class="badge-step">1</div>
              <div>Masuk ke halaman <strong>Booking</strong>.</div>
            </div>

            <div class="d-flex mb-2">
              <div class="badge-step">2</div>
              <div>Masukkan <strong>kode voucher</strong> kamu.</div>
            </div>

            <div class="d-flex">
              <div class="badge-step">3</div>
              <div>Selesaikan booking sebelum masa voucher <strong>berakhir</strong>.</div>
            </div>

            <div class="mt-2 small text-dim">
              Silakan klaim dengan cara memasukkan kode voucher Anda pada bagian Booking sebelum voucher kedaluwarsa.
            </div>

            <!-- LINK SYARAT & KETENTUAN (EMPTY STATE) -->
            <div class="mt-2 small text-dim">
              Baca <a href="<?= site_url('hal') ?>#voucher">syarat &amp; ketentuan voucher</a>.
            </div>
          </div>

          <!-- <div class="mb-2">
            <a href="<?= site_url('billiard') ?>" class="btn btn-blue mr-2">Klaim Voucher</a>
            <a href="<?= site_url('billiard/daftar_booking') ?>" class="btn btn-outline-secondary" style="border-radius:10px;font-weight:600;">
              Riwayat Booking
            </a>
          </div> -->

          <div class="small text-dim">
            Voucher baru juga akan dikirim via WhatsApp dan otomatis muncul di halaman ini.
          </div>
        </div>

      <?php else: ?>
        <!-- STATE: ADA VOUCHER -->

        <!-- LIST VOUCHER -->
        <div class="row">
          <?php foreach ($vouchers as $v): ?>
            <div class="col-xl-3 col-lg-4 col-md-6">
              <div class="card-box voucher-card mb-3" style="padding:16px;">

                <!-- Header nama + status -->
                <div class="d-flex align-items-start justify-content-between mb-2">
                  <div class="pr-2" style="min-width:0;">
                    <h4 class="mb-0 font-18 text-truncate" title="<?= html_escape($v->nama) ?>">
                      <?= html_escape($v->nama) ?>
                    </h4>
                    <div class="text-muted small mt-1">
                      <?= html_escape($mask_hp($v->no_hp)) ?>
                    </div>
                  </div>

                  <?php if ($v->is_expired): ?>
                    <span class="badge voucher-status-badge badge-danger align-self-start">Expired</span>
                  <?php else: ?>
                    <span class="badge voucher-status-badge badge-success align-self-start">Aktif</span>
                  <?php endif; ?>
                </div>

                <!-- Kode voucher (masked) -->
                <div class="text-center mb-3">
                  <span class="voucher-code-badge">
                    <?= html_escape($mask_code($v->kode)) ?>
                  </span>
                </div>

                <!-- Info tanggal -->
                <div class="d-flex text-center mt-2 mb-3">
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

                <!-- CTA per voucher -->
                <div class="text-center">
                  <?php if (!$v->is_expired): ?>
                    <style>
                      /* State loading khusus tombol klaim */
                      .btn-claim.loading {
                        opacity: .7;
                        pointer-events: none; /* cegah spam klik */
                        cursor: wait;
                      }
                    </style>

                    <a href="<?= site_url('billiard') ?>"
                       class="btn btn-blue btn-block btn-claim"
                       style="width:100%;">
                      <span class="btn-text">Klaim Voucher</span>
                    </a>

                    <script>
                    (function(){
                      // Delegasi klik supaya jalan walau tombol dibuat dinamis
                      document.addEventListener('click', function(e){
                        var btn = e.target.closest('a.btn-claim');
                        if (!btn) return;

                        // kalau sudah pernah loading, jangan proses 2x
                        if (btn.classList.contains('loading')) return;

                        // Tambah class loading (efek visual & lock)
                        btn.classList.add('loading');

                        // Ganti isi tombol jadi spinner + teks
                        btn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> <span>Memproses...</span>';

                        // biarkan default jalan → browser tetap redirect ke href
                        // jadi tidak pakai preventDefault()
                      }, true); // capture=true biar kejadian sebelum nav lanjut
                    })();
                    </script>

                  <?php else: ?>
                    <button class="btn btn-secondary btn-block" style="width:100%;border-radius:10px;font-weight:600;" disabled>
                      Tidak dapat diklaim
                    </button>
                  <?php endif; ?>
                </div>

              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Cara klaim voucher (SEKARANG DI BAWAH LOOP) -->
        <div class="row">
          <div class="col-12">
            <div class="voucher-hint mx-auto mt-2 mb-3" style="max-width:480px;">
              <div class="mb-2 font-weight-bold" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;">
                Cara klaim voucher
              </div>

              <div class="d-flex mb-2">
                <div class="badge-step">1</div>
                <div>Buka halaman <strong>Booking</strong></div>
              </div>

              <div class="d-flex mb-2">
                <div class="badge-step">2</div>
                <div>Masukkan <strong>kode voucher</strong> yang tertera di atas</div>
              </div>

              <div class="d-flex mb-2">
                <div class="badge-step">3</div>
                <div>Selesaikan booking sebelum <strong>expired</strong></div>
              </div>

              <div class="small text-dim">
                Silakan klaim dengan cara memasukkan kode voucher Anda pada bagian Booking sebelum voucher kedaluwarsa.
              </div>

              <!-- LINK SYARAT & KETENTUAN (STATE ADA VOUCHER) -->
              <div class="small text-dim mt-2">
                Baca <a href="<?= site_url('hal') ?>#voucher">syarat &amp; ketentuan voucher</a>.
              </div>

              <!-- <div class="text-center mt-3">
                <a href="<?= site_url('billiard') ?>" class="btn btn-blue" style="min-width:160px;">
                  Klaim Voucher
                </a>
              </div> -->
            </div>
          </div>
        </div>

        <!-- Info penting -->
        <div class="row">
          <div class="col-12">
            <div class="voucher-hint mx-auto mt-2 mb-4" style="max-width:480px;">
              <div class="small font-weight-bold mb-1" style="text-transform:uppercase;letter-spacing:.05em;">
                Penting
              </div>
              <div class="small mb-1">
                Voucher hanya bisa dipakai selama masih aktif.
              </div>
              <div class="small mb-1">
                Klaim dilakukan di halaman Booking sebelum masa berlaku habis.
              </div>
              <div class="small">
                Setelah dipakai, voucher akan hilang dari daftar.
              </div>
              <!-- Tambahan link syarat & ketentuan -->
              <div class="small mt-2">
                Baca <a href="<?= site_url('hal') ?>#voucher">syarat &amp; ketentuan voucher</a>.
              </div>
            </div>
          </div>
        </div>

      <?php endif; ?>

    </div>
  </div>

  <?php $this->load->view("front_end/banner_billiard") ?>
</div>

<?php $this->load->view("front_end/footer.php") ?>
