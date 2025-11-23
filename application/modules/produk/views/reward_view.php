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

// Helper untuk sensor nama (mis: "Nurhikmah Nurhikmah" -> "Nur•••••• Nur••••••")
if (!function_exists('mask_name')) {
    function mask_name($name)
    {
        $name = trim((string)$name);
        if ($name === '') {
            return 'Tanpa Nama';
        }

        // pecah per kata
        $parts = preg_split('/\s+/', $name);
        $maskedParts = [];

        foreach ($parts as $part) {
            $len = strlen($part);
            if ($len <= 3) {
                // nama sangat pendek, biarkan saja
                $maskedParts[] = $part;
            } else {
                // ambil 3 huruf pertama, sisanya diganti bullet
                $maskedParts[] = substr($part, 0, 3) . str_repeat('•', $len - 3);
            }
        }

        return implode(' ', $maskedParts);
    }
}
?>

<div class="container-fluid">

  <!-- HERO TITLE -->
  <div class="hero-title ausi-hero-center" role="banner" aria-label="Judul halaman">
    <?php $this->load->view("front_end/back") ?>
    <h1 class="text mb-0">🎁 <?php echo $title ?></h1>
    <span class="accent ausi-accent" aria-hidden="true"></span>
  </div>

  <div class="row">
    <div class="col-12 col-lg-10 col-xl-8 mx-auto">

      <!-- PENGUMUMAN REWARD -->
      <div class="card mb-3 shadow-sm border-0">
        <div class="card-body text-center">

          <?php
          // 7 = Minggu (PHP: date('N')), untuk ganti teks "minggu ini" / "minggu lalu"
          $isTodayAnnouncement = !empty($is_announcement_time);
          $hasRange = !empty($periode_mulai_str) && !empty($periode_selesai_str);

          // === LOGIKA: Sembunyikan countdown jika hari Minggu 08:00–23:59 WITA ===
          // Tujuannya: setelah pengumuman (hari Minggu), timer ke minggu berikutnya tidak tampil dulu.
          $hideCountdown = false;
          try {
              $nowWita = new DateTime('now', new DateTimeZone('Asia/Makassar'));
              $dow0    = (int)$nowWita->format('w');   // 0 = Minggu
              $hm      = (int)$nowWita->format('Hi');  // format 4 digit, mis. 0830

              // Sembunyikan hanya jika:
              // - Hari Minggu
              // - Waktu antara 08:00 s/d 23:59 WITA
              // - DAN sudah ada pemenang (supaya sebelum pengumuman, countdown tetap jalan)
              if (!empty($winner_top) && $dow0 === 0 && $hm >= 800 && $hm <= 2359) {
                  $hideCountdown = true;
              }
          } catch (Exception $e) {
              $hideCountdown = false;
          }
          ?>

          <?php if (!$hideCountdown): ?>
          <!-- ===== COUNTDOWN SELALU DI ATAS ===== -->
          <div class="mb-3">
            <p class="mb-1 text-dark small">
              Hitung mundur menuju pengumuman berikutnya:
            </p>

            <div class="reward-clock-container"
                 role="group"
                 aria-label="Timer hitung mundur pengumuman reward">
              <div class="reward-clock-col">
                <div id="reward_days"
                     class="reward-clock-timer"
                     aria-label="Sisa hari">&nbsp;</div>
                <div class="reward-clock-label reward-label-days">Hari</div>
              </div>
              <div class="reward-clock-col">
                <div id="reward_hours"
                     class="reward-clock-timer"
                     aria-label="Sisa jam">&nbsp;</div>
                <div class="reward-clock-label">Jam</div>
              </div>
              <div class="reward-clock-col">
                <div id="reward_minutes"
                     class="reward-clock-timer"
                     aria-label="Sisa menit">&nbsp;</div>
                <div class="reward-clock-label">Menit</div>
              </div>
              <div class="reward-clock-col">
                <div id="reward_seconds"
                     class="reward-clock-timer"
                     aria-label="Sisa detik">&nbsp;</div>
                <div class="reward-clock-label">Detik</div>
              </div>
            </div>

            <p class="mb-0 mt-2 text-dark small">
              Waktu mengikuti zona <strong>SIWA</strong>.
            </p>
          </div>
          <!-- ===== END COUNTDOWN ===== -->
          <?php endif; ?>

          <?php if (!empty($winner_top)): ?>

            <!-- ========== SELALU TAMPILKAN PEMENANG TERAKHIR ========== -->
            <h4 class="mb-2">
              <?= $isTodayAnnouncement ? 'Selamat, Pemenang Reward Minggu Ini! 🎉' : 'Pemenang Reward Minggu Lalu 🎉'; ?>
            </h4>

            <p class="mb-3 text-dark small">
              <?php if ($hasRange): ?>
                <?php if ($isSunday): ?>
                  Berikut adalah penerima <strong>reward voucher order Rp 50.000</strong> yang diumumkan hari ini,
                  untuk rekap poin periode
                  <strong>
                    <?= html_escape($periode_mulai_str); ?> – <?= html_escape($periode_selesai_str); ?>
                    (Minggu 00:00 – Sabtu 23:59 WITA)
                  </strong>.
                <?php else: ?>
                  Berikut adalah penerima <strong>reward voucher order Rp 50.000</strong> untuk periode
                  <strong>
                    <?= html_escape($periode_mulai_str); ?> – <?= html_escape($periode_selesai_str); ?>
                    (Minggu 00:00 – Sabtu 23:59 WITA)
                  </strong>.
                <?php endif; ?>
              <?php else: ?>
                <?php if ($isSunday): ?>
                  Berikut adalah penerima <strong>reward voucher order Rp 50.000</strong> yang diumumkan hari ini,
                  untuk rekap poin <strong>Minggu 00:00 – Sabtu 23:59 WITA</strong>.
                <?php else: ?>
                  Berikut adalah penerima <strong>reward voucher order Rp 50.000</strong> untuk periode
                  <strong>minggu lalu</strong>.
                <?php endif; ?>
              <?php endif; ?>
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
                    <?= html_escape(mask_name($winner_top->nama)); ?>
                  </div>

                  <div class="reward-row">
                    <span class="reward-row-label">No. WA</span>
                    <span class="reward-row-value">
                      <?= html_escape(mask_phone($winner_top->no_hp)); ?>
                    </span>
                  </div>

                  <div class="reward-row">
                    <span class="reward-row-label">Voucher</span>
                    <span class="reward-pill pill-green">
                      Voucher order Rp <?= number_format((int)$winner_top->nilai, 0, ',', '.'); ?>
                    </span>
                  </div>
                  <div class="reward-row">
                    <span class="reward-row-label">Total Poin</span>
                    <span class="reward-row-value">
                      <?= number_format((int)$winner_top->jumlah_poin, 0, ',', '.'); ?>
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
                      <?= html_escape(mask_name($winner_random->nama)); ?>
                    </div>

                    <div class="reward-row">
                      <span class="reward-row-label">No. WA</span>
                      <span class="reward-row-value">
                        <?= html_escape(mask_phone($winner_random->no_hp)); ?>
                      </span>
                    </div>

                    <div class="reward-row">
                      <span class="reward-row-label">Voucher</span>
                      <span class="reward-pill pill-blue">
                        Voucher order Rp <?= number_format((int)$winner_random->nilai, 0, ',', '.'); ?>
                      </span>
                    </div>
                    <div class="reward-row">
                      <span class="reward-row-label">Total Poin</span>
                      <span class="reward-row-value">
                        <?= number_format((int)$winner_random->jumlah_poin, 0, ',', '.'); ?>
                      </span>
                    </div>

                    <div class="reward-row">
                      <span class="reward-row-label">Status</span>
                      <span class="reward-row-value">
                        Diacak oleh Robot Ausi
                      </span>
                    </div>

                    <div class="reward-note">
                      Pemenang acak ditentukan oleh <strong>Robot Ausi</strong> menggunakan
                      generator angka acak terkomputerisasi dari seluruh pelanggan yang memiliki
                      <strong>poin &gt; 0</strong> pada pekan tersebut (kecuali peraih poin tertinggi),
                      sepenuhnya <strong>tanpa campur tangan manusia</strong>.
                      Pemenang berhak atas <strong>voucher order senilai Rp&nbsp;50.000*</strong>.
                    </div>

                  </div>
                </div>
              <?php endif; ?>

            </div>

            <p class="mt-3 mb-0 small text-dark">
              Voucher dan konfirmasi akan dikirim langsung via WhatsApp oleh admin.
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

            <!-- ========== BELUM ADA PEMENANG (MENUNGGU PENGUMUMAN PERTAMA) ========== -->
            <div class="mb-2">
              <span class="d-inline-flex align-items-center px-3 py-1 rounded-pill"
                    style="background:rgba(37,99,235,.08);color:#1d4ed8;font-size:.78rem;font-weight:600;">
                <span class="me-2"></span>
                <span>⏳ Nantikan Pengumuman Reward</span>
              </span>
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

  /* Countdown baru – model "clock" 4 kolom, selalu di atas */
  .reward-clock-container{
    border-radius:8px;
    box-shadow:0 10px 25px rgba(15,23,42,.08);
    width:100%;
    max-width:640px;
    margin:0 auto;
    display:flex;
    flex-wrap:wrap;
    overflow:hidden;
  }
  .reward-clock-col{
    flex:1 1 25%;
    min-width:25%;
    text-align:center;
    border-right:1px solid rgba(15,23,42,.4);
    background:linear-gradient(
      180deg,
      rgba(0,110,162,1) 0%,
      rgba(1,78,115,1) 100%
    );
  }
  .reward-clock-col:first-child{
    background:#23395b;
  }
  .reward-clock-col:last-child{
    border-right:none;
  }
  @media (max-width:576px){
    .reward-clock-col{
      flex:1 1 50%;
      min-width:50%;
    }
    .reward-clock-col:nth-child(2){
      border-right:none;
    }
  }
  .reward-clock-timer{
    color:#ffffff;
    font-size:1.8rem;
    padding:14px 0;
    box-shadow:0 -4px 6px -4px rgba(15,23,42,.7);
    font-weight:700;
    line-height:1.1;
  }
  .reward-clock-label{
    color:#ffffff;
    text-transform:uppercase;
    font-size:.8rem;
    padding:8px 0;
    margin:0;
    background:linear-gradient(
      180deg,
      rgba(2,73,107,1) 0%,
      rgba(0,110,162,1) 100%
    );
    border-top:1px solid #013e5b;
    letter-spacing:.08em;
  }
  .reward-label-days{
    background:#23395b;
    border-top:1px solid #161925;
  }
  .reward-expired-message{
    font-size:.95rem;
    font-weight:600;
    color:#f9fafb;
    padding:10px 0;
  }

  @media (min-width:768px){
    .reward-clock-timer{
      font-size:2.2rem;
      padding:18px 0;
    }
    .reward-clock-label{
      font-size:.85rem;
      padding:10px 0;
    }
  }

  /*@media (prefers-color-scheme: dark){*/
    .reward-card-top{
      background:linear-gradient(135deg,#3f3f26,#4f4925);
    }
    .reward-card-random{
      background:linear-gradient(135deg,#1f2937,#0f172a);
    }
    .reward-note{
      color:#e5e7eb;
    }
  /*}*/
</style>

<script>
(function () {
  const container = document.querySelector('.reward-clock-container');
  if (!container) return;

  const targetStr = '<?= isset($next_announcement_iso) ? $next_announcement_iso : ''; ?>';
  if (!targetStr) {
    container.innerHTML = '<div class="reward-expired-message">-</div>';
    return;
  }

  const countDownDate = new Date(targetStr).getTime();
  const elDays    = document.getElementById('reward_days');
  const elHours   = document.getElementById('reward_hours');
  const elMinutes = document.getElementById('reward_minutes');
  const elSeconds = document.getElementById('reward_seconds');

  if (!elDays || !elHours || !elMinutes || !elSeconds) {
    return;
  }

  function pad2(n){ return n.toString().padStart(2,'0'); }

  let timerId;

  function update() {
    const now = Date.now();
    const distance = countDownDate - now;

    if (distance <= 0) {
      clearInterval(timerId);
      container.innerHTML = '<div class="reward-expired-message">🎉 Sedang proses pengumuman...</div>';
      // reload supaya otomatis menampilkan pemenang
      setTimeout(function () {
        location.reload();
      }, 5000);
      return;
    }

    const totalSec = Math.floor(distance / 1000);
    const days     = Math.floor(totalSec / 86400);
    const hours    = Math.floor((totalSec % 86400) / 3600);
    const minutes  = Math.floor((totalSec % 3600) / 60);
    const seconds  = totalSec % 60;

    elDays.textContent    = days;
    elHours.textContent   = pad2(hours);
    elMinutes.textContent = pad2(minutes);
    elSeconds.textContent = pad2(seconds);
  }

  update();
  timerId = setInterval(update, 1000);
})();
</script>

<?php $this->load->view("front_end/footer.php"); ?>
