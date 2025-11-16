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
              Berikut adalah penerima <strong>reward voucher order Rp 50.000</strong> untuk periode pekan ini
              (rekap poin <strong>Minggu 00:00 – Sabtu 23:59 WITA</strong>).
              Terima kasih sudah setia berbelanja di AUSI Café.
            </p>

            <div class="row g-3 justify-content-center">

              <!-- Poin tertinggi -->
              <div class="col-md-6">
                <div class="reward-card reward-card-top mb-2">
                  <div class="reward-card-header">
                    <span class="reward-emoji">🏆</span>
                    <span class="reward-label">Peraih Poin Tertinggi</span>
                  </div>

                  <div class="reward-name">
                    <?= html_escape($winner_top->customer_name ?: 'Tanpa Nama'); ?>
                  </div>

                  <div class="reward-row">
                    <span class="reward-row-label">No. WA</span>
                    <span class="reward-row-value">
                      <?= html_escape(mask_phone($winner_top->customer_phone)); ?>
                    </span>
                  </div>

                  <div class="reward-row">
                    <span class="reward-row-label">Total poin</span>
                    <span class="reward-pill pill-green">
                      <?= (int)$winner_top->points; ?> poin
                    </span>
                  </div>

                  <div class="reward-note">
                    Berhak atas voucher order senilai <strong>Rp&nbsp;50.000*</strong>.
                  </div>
                  <div class="reward-note">
                    Jika terjadi poin yang sama, <strong>Robot Ausi</strong> menentukan pemenang berdasarkan:
                    <br>
                    <span class="small">
                      1) <strong>Total belanja</strong> lebih besar,
                      2) <strong>waktu transaksi terakhir</strong> yang lebih awal,
                      3) <strong>jumlah transaksi</strong> yang lebih banyak.
                    </span>
                  </div>
                </div>
              </div>

              <!-- Pemenang acak -->
              <?php if (!empty($winner_random)): ?>
                <div class="col-md-6">
                  <div class="reward-card reward-card-random">
                    <div class="reward-card-header">
                      <span class="reward-emoji">🎲</span>
                      <span class="reward-label">Pemenang Acak</span>
                    </div>

                    <div class="reward-name">
                      <?= html_escape($winner_random->customer_name ?: 'Tanpa Nama'); ?>
                    </div>

                    <div class="reward-row">
                      <span class="reward-row-label">No. WA</span>
                      <span class="reward-row-value">
                        <?= html_escape(mask_phone($winner_random->customer_phone)); ?>
                      </span>
                    </div>

                    <div class="reward-row">
                      <span class="reward-row-label">Status</span>
                      <span class="reward-pill pill-blue">
                        Dipilih secara acak oleh Sistem
                      </span>
                    </div>

                    <div class="reward-note">
                      Pemenang acak dipilih oleh <strong>Robot Ausi</strong> dari seluruh pelanggan
                      yang memiliki <strong>poin &gt; 0</strong> pada pekan tersebut
                      (kecuali peraih poin tertinggi), dan berhak atas voucher order
                      senilai <strong>Rp&nbsp;50.000*</strong>.
                    </div>
                  </div>
                </div>
              <?php endif; ?>

            </div>

            <!-- Countdown menuju pengumuman minggu berikutnya -->
            <div class="mt-3">
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

            <p class="mt-3 mb-0 small text-dark">
              No. WhatsApp disensor demi menjaga privasi pelanggan.
              Voucher dan konfirmasi akan dikirim langsung via WhatsApp oleh admin.
            </p>
            <p class="mt-2 mb-0 small text-dark">
              Peraih voucher ditentukan sepenuhnya oleh <strong>Robot Ausi</strong> sesuai
              <em>Syarat &amp; Ketentuan Program Reward</em>: poin dihitung dari transaksi
              berstatus <strong>paid</strong> dalam satu siklus pekan, dengan kombinasi
              <strong>poin tertinggi</strong> dan <strong>undian acak</strong> tanpa campur tangan manusia.
            </p>
            <p class="mt-2 mb-0 small text-dark">
              <strong>*</strong>Voucher bersifat <strong>non-tunai</strong>, tidak dapat diuangkan,
              tidak dapat dipindahtangankan, dan tidak dapat digabung dengan promo lain
              kecuali dinyatakan sebaliknya. Masa berlaku voucher adalah
              <strong>7 (tujuh) hari kalender</strong> sejak tanggal penerbitan.
              Detail lengkap dapat dilihat pada
              <a href="<?php echo site_url('hal/'); ?>#voucher-order"
                 class="text-decoration-underline">
                Syarat &amp; Ketentuan
              </a>.
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
            <!-- Countdown tepat di bawah "Nantikan Pengumuman Reward" -->
            <div class="mb-1">
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
            <p class="mb-2 text-dark">
              Pengumuman <strong>poin tertinggi</strong> dan <strong>1 pemenang acak</strong>
              akan tampil di halaman ini setiap <strong>Minggu, pukul 08:00 WITA</strong>,
              untuk periode <strong>Minggu 00:00 – Sabtu 23:59 WITA</strong> pekan sebelumnya.
            </p>

            <p class="mb-3 text-dark small">
              Pastikan Anda selalu menggunakan nomor WhatsApp yang sama saat order
              dan transaksi berstatus <strong>paid</strong>, agar sistem dapat mencatat
              dan mengakumulasi poin secara otomatis.
            </p>

            <!-- Mini timeline periode mingguan -->
            <div class="row g-2 justify-content-center text-start text-md-center">
              <div class="col-12 col-md-4">
                <div class="p-2 rounded"
                     style="background:rgba(15,23,42,.03);border:1px dashed rgba(148,163,184,.7);">
                  <div class="small fw-bold text-dark mb-1">Kumpulkan Poin</div>
                  <div class="small text-dark">
                    Poin dihitung dari setiap transaksi yang <strong>berhasil dibayar (status paid)</strong>.
                    Transaksi void/refund/batal tidak menambah poin.
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="p-2 rounded"
                     style="background:rgba(15,23,42,.03);border:1px dashed rgba(148,163,184,.7);">
                  <div class="small fw-bold text-dark mb-1">Periode Mingguan</div>
                  <div class="small text-dark">
                    Rekap poin: <br>
                    <strong>Minggu 00:00 – Sabtu 23:59 WITA</strong>. <br>
                    Setiap <strong>Minggu 00:00</strong> dimulai pekan baru dan
                    perhitungan poin kembali dari nol.
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="p-2 rounded"
                     style="background:rgba(15,23,42,.03);border:1px dashed rgba(148,163,184,.7);">
                  <div class="small fw-bold text-dark mb-1">Pengumuman</div>
                  <div class="small text-dark">
                    Hasil diumumkan <strong>Minggu 08:00 WITA</strong>
                    di halaman ini untuk periode pekan sebelumnya.
                  </div>
                </div>
              </div>

              <div class="col-12 col-md-4">
                <div class="p-2 rounded"
                     style="background:rgba(15,23,42,.03);border:1px dashed rgba(148,163,184,.7);">
                  <div class="small fw-bold text-dark mb-1">Metode</div>
                  <div class="small text-dark">
                    Peraih voucher ditentukan sepenuhnya oleh <strong>Robot Ausi</strong>:
                    <br>
                    – <strong>Pemenang 1:</strong> poin tertinggi (dengan tie-breaker total belanja, waktu transaksi
                    terakhir yang lebih awal, lalu jumlah transaksi).<br>
                    – <strong>Pemenang 2:</strong> undian acak oleh <strong>Robot Ausi</strong>
                    dari semua pelanggan yang memiliki <strong>poin &gt; 0</strong> pada periode tersebut,
                    tanpa campur tangan manusia.
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
            Setiap transaksi <strong>berhasil (status paid)</strong> langsung menambah poin Anda.
            <strong>Makin sering order, makin cepat poin terkumpul</strong> — ayo lanjutkan belanja di AUSI!
          </p>
          <p class="mb-2">
            Rekap poin dan pengumuman <strong>voucher order</strong> dilakukan
            <strong>setiap hari Minggu pukul 08:00 WITA</strong> untuk periode <strong>pekan sebelumnya</strong>.
            Pastikan nomor WhatsApp aktif agar tidak ketinggalan info.
          </p>
          <p class="mb-0 text-dark small">
            Poin dihitung otomatis dari total belanja &amp; komponen kode unik transaksi;
            periode mengikuti <strong>siklus mingguan</strong> (Minggu 00:00 – Sabtu 23:59 WITA,
            reset otomatis setiap Minggu 00:00).
            <br>
            <a href="<?php echo site_url('hal/'); ?>#voucher-order"
               class="text-decoration-underline">
              Syarat &amp; Ketentuan program reward berlaku
            </a>
          </p>
        </div>
      </div>

    </div>
  </div>
</div>

<style>
  /* Kartu pemenang */
  .reward-card{
    border-radius:16px;
    padding:16px 14px 14px;
    text-align:left;
    box-shadow:0 6px 16px rgba(15,23,42,.06);
  }
  @media (min-width:768px){
    .reward-card{ padding:18px 20px 16px; }
  }
  .reward-card-top{
    background:linear-gradient(135deg,#fff7d6,#ffe5a1);
  }
  .reward-card-random{
    background:linear-gradient(135deg,#e6f3ff,#d4e8ff);
  }
  .reward-card-header{
    display:flex;
    align-items:center;
    gap:8px;
    font-size:.8rem;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.08em;
    color:#ffff;
    margin-bottom:6px;
  }
  .reward-emoji{
    font-size:1rem;
  }
  .reward-label{
    white-space:nowrap;
  }
  .reward-name{
    font-weight:700;
    font-size:1.1rem;
    margin-bottom:6px;
    color:#ffff;
  }
  .reward-row{
    display:flex;
    justify-content:space-between;
    align-items:center;
    font-size:.9rem;
    margin-bottom:4px;
    gap:8px;
  }
  .reward-row-label{
    color:#ffff;
    font-weight:500;
  }
  .reward-row-value{
    font-weight:600;
    color:#ffff;
  }
  .reward-pill{
    display:inline-flex;
    align-items:center;
    padding:2px 8px;
    border-radius:999px;
    font-size:.75rem;
    font-weight:600;
    background:rgba(255,255,255,.9);
  }
  .pill-green{
    background:#16a34a;
    color:#f9fafb;
  }
  .pill-blue{
    background:#0ea5e9;
    color:#f9fafb;
  }
  .reward-note{
    margin-top:6px;
    font-size:.8rem;
    color:#6b7280;
    font-style:italic;
  }

  /* Countdown pill (angka putih, label ada spasi) */
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
    background:#111827;
    border:1px solid #111827;
    display:inline-flex;
    align-items:baseline;
    justify-content:center;
    gap:4px;
  }
  .reward-countdown-value{
    font-size:1.1rem;
    font-weight:700;
    line-height:1;
    color:#ffffff;
  }
  .reward-countdown-label{
    font-size:.78rem;
    text-transform:uppercase;
    letter-spacing:.08em;
    color:#e5e7eb;
  }

  @media (prefers-color-scheme: dark){
    .reward-card-top{
      background:linear-gradient(135deg,#3f3f26,#4f4925);
    }
    .reward-card-random{
      background:linear-gradient(135deg,#1f2937,#0f172a);
    }
    .reward-note{
      color:#e5e7eb;
    }
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
    wrap.innerHTML = '<span class="small text-dark">-</span>';
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
        boxDays.style.display = 'inline-flex';
        elDays.textContent = days;
      }
    }

    // Jam: sembunyikan jika 0
    if (elHours && boxHours) {
      if (hours <= 0) {
        boxHours.style.display = 'none';
      } else {
        boxHours.style.display = 'inline-flex';
        elHours.textContent = pad2(hours);
      }
    }

    // Menit: sembunyikan jika 0
    if (elMinutes && boxMinutes) {
      if (minutes <= 0) {
        boxMinutes.style.display = 'none';
      } else {
        boxMinutes.style.display = 'inline-flex';
        elMinutes.textContent = pad2(minutes);
      }
    }

    // Detik: tetap tampil
    if (elSeconds && boxSeconds) {
      boxSeconds.style.display = 'inline-flex';
      elSeconds.textContent = pad2(seconds);
    }
  }

  tick();
  setInterval(tick, 1000);
})();
</script>

<?php $this->load->view("front_end/footer.php"); ?>
