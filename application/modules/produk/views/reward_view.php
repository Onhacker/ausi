<?php $this->load->view("front_end/head.php"); ?>

<?php
// Helper kecil untuk sensor nomor HP di tampilan saja
if (!function_exists('mask_phone')) {
    function mask_phone($phone)
    {
        $phone  = trim((string)$phone);
        if ($phone === '') return '-';

        // ambil hanya digit
        $digits = preg_replace('/\D+/', '', $phone);
        $len    = strlen($digits);

        if ($len <= 4) {
            return $digits;
        }

        $showStart = min(4, $len - 2);
        $showEnd   = 2;

        $masked = substr($digits, 0, $showStart)
                . str_repeat('•', max(0, $len - $showStart - $showEnd))
                . substr($digits, -$showEnd);

        // kalau aslinya pakai + di depan, kembalikan
        if (isset($phone[0]) && $phone[0] === '+') {
            return '+' . $masked;
        }

        return $masked;
    }
}
?>

<div class="container-fluid">

  <!-- HERO TITLE -->
  <div class="hero-title ausi-hero-center" role="banner" aria-label="Judul halaman">
    <h1 class="text mb-0"><?php echo $title ?></h1>
    <span class="accent ausi-accent" aria-hidden="true"></span>
  </div>

  <div class="row">
    <div class="col-12 col-lg-10 col-xl-8 mx-auto">

      <!-- PENGUMUMAN REWARD -->
      <div class="card mb-3 shadow-sm border-0">
        <div class="card-body text-center">

          <span class="badge rounded-pill bg-warning text-dark mb-2">
            🎁 Program Reward AUSI Café
          </span>

          <?php if (!empty($is_announcement_time) && $is_announcement_time && !empty($winner_top)): ?>

            <!-- ========== MODE SUDAH PENGUMUMAN ========== -->
            <h4 class="mb-2">Selamat, Pemenang Reward Minggu Ini! 🎉</h4>
            <p class="mb-3 text-dark small">
              Berikut adalah penerima <strong>reward voucher order</strong> untuk periode minggu ini.
              Terima kasih sudah setia berbelanja di AUSI Café.
            </p>

            <div class="row g-3 justify-content-center">

              <!-- Poin tertinggi -->
              <div class="col-md-6">
                <div class="h-100 p-3 p-md-4 rounded border-0"
                     style="background:linear-gradient(135deg,#fff7d6,#ffe5a1);box-shadow:0 6px 16px rgba(0,0,0,.06);">
                  <div class="d-flex align-items-center mb-2 text-start">
                    <div class="me-3 fs-3">🏆</div>
                    <div>
                      <div class="small text-uppercase text-muted">Peraih Poin Tertinggi</div>
                      <div class="fw-bold fs-5 mb-0">
                        <?= html_escape($winner_top->customer_name ?: 'Tanpa Nama'); ?>
                      </div>
                    </div>
                  </div>

                  <div class="text-start small mb-1">
                    No. WA:
                    <span class="fw-semibold">
                      <?= html_escape(mask_phone($winner_top->customer_phone)); ?>
                    </span>
                  </div>
                  <div class="text-start small mb-2">
                    Total poin:
                    <span class="badge bg-success">
                      <?= (int)$winner_top->points; ?> poin
                    </span>
                  </div>
                  <div class="text-start small text-muted fst-italic">
                    Berhak atas voucher order senilai <strong>Rp 50.000*</strong>.
                  </div>
                </div>
              </div>

              <!-- Pemenang acak -->
              <?php if (!empty($winner_random)): ?>
                <div class="col-md-6">
                  <div class="h-100 p-3 p-md-4 rounded border-0"
                       style="background:linear-gradient(135deg,#e6f3ff,#d4e8ff);box-shadow:0 6px 16px rgba(0,0,0,.06);">
                    <div class="d-flex align-items-center mb-2 text-start">
                      <div class="me-3 fs-3">🎲</div>
                      <div>
                        <div class="small text-uppercase text-muted">Pemenang Acak</div>
                        <div class="fw-bold fs-5 mb-0">
                          <?= html_escape($winner_random->customer_name ?: 'Tanpa Nama'); ?>
                        </div>
                      </div>
                    </div>

                    <div class="text-start small mb-1">
                      No. WA:
                      <span class="fw-semibold">
                        <?= html_escape(mask_phone($winner_random->customer_phone)); ?>
                      </span>
                    </div>
                    <div class="text-start small mb-2">
                      Status:
                      <span class="badge bg-info">
                        Dipilih secara acak
                      </span>
                    </div>
                    <div class="text-start small text-muted fst-italic">
                      Semua pelanggan yang bertransaksi di periode ini memiliki peluang yang sama.
                    </div>
                  </div>
                </div>
              <?php endif; ?>

            </div>

            <!-- Countdown menuju pengumuman minggu berikutnya -->
            <div class="mt-3">
              <p class="mb-1 text-dark small">
                Hitung mundur menuju pengumuman minggu berikutnya:
              </p>
              <div id="reward-countdown"
                   class="reward-countdown-wrap"
                   aria-live="polite">
                <div class="reward-countdown-item">
                  <div class="reward-countdown-value" data-unit="days">0</div>
                  <div class="reward-countdown-label">Hari</div>
                </div>
                <div class="reward-countdown-item">
                  <div class="reward-countdown-value" data-unit="hours">00</div>
                  <div class="reward-countdown-label">Jam</div>
                </div>
                <div class="reward-countdown-item">
                  <div class="reward-countdown-value" data-unit="minutes">00</div>
                  <div class="reward-countdown-label">Menit</div>
                </div>
                <div class="reward-countdown-item">
                  <div class="reward-countdown-value" data-unit="seconds">00</div>
                  <div class="reward-countdown-label">Detik</div>
                </div>
              </div>
              <p class="mb-0 mt-2 text-dark small">
                Waktu mengikuti zona <strong>SIWA</strong>.
              </p>
            </div>

            <p class="mt-3 mb-0 small text-muted">
              No. WhatsApp disensor demi menjaga privasi pelanggan.  
              Hadiah dan konfirmasi akan dikirim langsung via WhatsApp oleh admin.
            </p>

          <?php else: ?>

            <!-- ========== MODE BELUM PENGUMUMAN ========== -->
            <div class="mb-2">
              <span class="d-inline-flex align-items-center px-3 py-1 rounded-pill"
                    style="background:rgba(37,99,235,.08);color:#1d4ed8;font-size:.78rem;font-weight:600;">
                <span class="me-2">⏳</span>
                <span>Pengumuman belum dimulai</span>
              </span>
            </div>

            <h4 class="mb-2">Nantikan Pengumuman Reward</h4>
            <p class="mb-2 text-dark">
              Pengumuman <strong>poin tertinggi</strong> dan <strong>1 pemenang acak</strong>
              akan tampil di halaman ini setiap <strong>Minggu, pukul 08.00 WITA</strong>.
            </p>

            <p class="mb-3 text-dark small">
              Pastikan Anda selalu menggunakan nomor WhatsApp yang sama saat order,
              agar sistem dapat mencatat dan mengakumulasi poin secara otomatis.
            </p>

            <!-- Countdown tepat di bawah "Nantikan Pengumuman Reward" -->
            <div class="mb-3">
              <p class="mb-1 text-dark small">
                Hitung mundur menuju pengumuman berikutnya:
              </p>
              <div id="reward-countdown"
                   class="reward-countdown-wrap"
                   aria-live="polite">
                <div class="reward-countdown-item">
                  <div class="reward-countdown-value" data-unit="days">0</div>
                  <div class="reward-countdown-label">Hari</div>
                </div>
                <div class="reward-countdown-item">
                  <div class="reward-countdown-value" data-unit="hours">00</div>
                  <div class="reward-countdown-label">Jam</div>
                </div>
                <div class="reward-countdown-item">
                  <div class="reward-countdown-value" data-unit="minutes">00</div>
                  <div class="reward-countdown-label">Menit</div>
                </div>
                <div class="reward-countdown-item">
                  <div class="reward-countdown-value" data-unit="seconds">00</div>
                  <div class="reward-countdown-label">Detik</div>
                </div>
              </div>
              <p class="mb-0 mt-2 text-dark small">
                Waktu mengikuti zona <strong>WITA (Asia/Makassar)</strong>.
              </p>
            </div>

            <!-- Mini timeline periode mingguan -->
            <div class="row g-2 justify-content-center text-start text-md-center">
              <div class="col-12 col-md-4">
                <div class="p-2 rounded"
                     style="background:rgba(15,23,42,.03);border:1px dashed rgba(148,163,184,.7);">
                  <div class="small fw-bold text-dark mb-1">Kumpulkan Poin</div>
                  <div class="small text-muted">
                    Poin dihitung dari setiap transaksi yang <strong>berhasil dibayar</strong>.
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="p-2 rounded"
                     style="background:rgba(15,23,42,.03);border:1px dashed rgba(148,163,184,.7);">
                  <div class="small fw-bold text-dark mb-1">Periode Mingguan</div>
                  <div class="small text-muted">
                    Rekap poin: <br>
                    <strong>Minggu 00:00 – Sabtu 23:59 WITA</strong>.
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="p-2 rounded"
                     style="background:rgba(15,23,42,.03);border:1px dashed rgba(148,163,184,.7);">
                  <div class="small fw-bold text-dark mb-1">Pengumuman</div>
                  <div class="small text-muted">
                    Hasil diumumkan <strong>Minggu 08:00 WITA</strong> langsung di halaman ini.
                  </div>
                </div>
              </div>
            </div>

          <?php endif; ?>

        </div>
      </div>

      <!-- INFO CARD -->
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h4 class="mb-2">Tingkatkan Poin &amp; Raih Voucher Order Senilai Rp 50.000</h4>
          <p class="mb-2">
            Setiap transaksi <strong>berhasil</strong> langsung menambah poin Anda.
            <strong>Makin sering order, makin cepat poin terkumpul</strong> — ayo lanjutkan belanja di AUSI!
          </p>
          <p class="mb-2">
            Rekap poin dan pengumuman <strong>voucher order</strong> dilakukan
            <strong>setiap hari Minggu pukul 08:00 WITA</strong> untuk periode <strong>pekan sebelumnya</strong>.
            Pastikan nomor WhatsApp aktif agar tidak ketinggalan info.
          </p>
          <p class="mb-0 text-dark small">
            Poin dihitung otomatis dari total belanja &amp; komponen kode unik transaksi; periode mengikuti
            <strong>siklus mingguan</strong> (Minggu 00:00 – Sabtu 23:59 WITA, reset otomatis Minggu 00:00).
            <br>
            <a href="<?php echo site_url('hal/#voucher-order'); ?>" class="text-decoration-underline">
              Syarat &amp; Ketentuan berlaku
            </a>
          </p>
        </div>
      </div>

    </div>
  </div>
</div>

<style>
  .reward-countdown-wrap{
    display:flex;
    justify-content:center;
    flex-wrap:wrap;
    gap:8px;
  }

  .reward-countdown-item{
    min-width:72px;
    padding:6px 12px;
    border-radius:999px;
    background:#111827;              /* pill gelap */
    border:1px solid #111827;
    display:inline-flex;             /* supaya angka & teks 1 baris */
    align-items:baseline;
    justify-content:center;
    gap:4px;                         /* ⬅️ spasi antara angka & JAM/MENIT/DETIK */
  }

  .reward-countdown-value{
    font-size:1.1rem;
    font-weight:700;
    line-height:1;
    color:#ffffff;                   /* ⬅️ angka putih */
  }

  .reward-countdown-label{
    font-size:.78rem;
    text-transform:uppercase;
    letter-spacing:.08em;
    color:#e5e7eb;                   /* teks unit abu-abu muda */
  }

  @media (prefers-color-scheme: dark){
    .reward-countdown-item{
      background:#020617;
      border-color:#020617;
    }
  }
</style>


<script>
(function () {
  const wrap = document.getElementById('reward-countdown');
  if (!wrap) return;

  const targetStr = '<?= isset($next_announcement_iso) ? $next_announcement_iso : ''; ?>';
  if (!targetStr) {
    wrap.innerHTML = '<span class="small text-muted">-</span>';
    return;
  }

  const target = new Date(targetStr);

  const elDays    = wrap.querySelector('[data-unit="days"]');
  const elHours   = wrap.querySelector('[data-unit="hours"]');
  const elMinutes = wrap.querySelector('[data-unit="minutes"]');
  const elSeconds = wrap.querySelector('[data-unit="seconds"]');

  const boxDays    = elDays    ? elDays.closest('.reward-countdown-item') : null;
  const boxHours   = elHours   ? elHours.closest('.reward-countdown-item') : null;
  const boxMinutes = elMinutes ? elMinutes.closest('.reward-countdown-item') : null;
  const boxSeconds = elSeconds ? elSeconds.closest('.reward-countdown-item') : null;

  function pad2(n){ return n.toString().padStart(2,'0'); }

  function tick() {
    const now  = new Date();
    const diff = target - now;

    if (diff <= 0) {
      wrap.innerHTML = '<span class="small fw-semibold text-dark">Sedang proses pengumuman...</span>';
      // reload supaya otomatis menampilkan pemenang
      setTimeout(function () {
        location.reload();
      }, 5000);
      return;
    }

    const totalSec = Math.floor(diff / 1000);
    const days     = Math.floor(totalSec / 86400);
    const hours    = Math.floor((totalSec % 86400) / 3600);
    const minutes  = Math.floor((totalSec % 3600) / 60);
    const seconds  = totalSec % 60;

    // Hari: sembunyikan jika 0
    if (elDays && boxDays) {
      if (days <= 0) {
        boxDays.style.display = 'none';
      } else {
        boxDays.style.display = 'flex';
        elDays.textContent = days;
      }
    }

    // Jam: sembunyikan jika 0
    if (elHours && boxHours) {
      if (hours <= 0) {
        boxHours.style.display = 'none';
      } else {
        boxHours.style.display = 'flex';
        elHours.textContent = pad2(hours);
      }
    }

    // Menit: sembunyikan jika 0
    if (elMinutes && boxMinutes) {
      if (minutes <= 0) {
        boxMinutes.style.display = 'none';
      } else {
        boxMinutes.style.display = 'flex';
        elMinutes.textContent = pad2(minutes);
      }
    }

    // Detik: tetap tampil, meskipun 0 (biar countdown tidak hilang total)
    if (elSeconds && boxSeconds) {
      boxSeconds.style.display = 'flex';
      elSeconds.textContent = pad2(seconds);
    }
  }

  tick();
  setInterval(tick, 1000);
})();
</script>

<?php $this->load->view("front_end/footer.php"); ?>
