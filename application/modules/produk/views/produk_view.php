<?php $this->load->view("front_end/head.php"); ?>

<!-- custom style untuk halaman menu -->
<link rel="stylesheet"
href="<?= base_url('assets/front/produk.min.css') ?>?v=<?= filemtime(FCPATH.'assets/front/produk.min.css'); ?>">
<style>
  /* ===== Tulisan Berjalan: Batas Last Order ===== */
  .lastorder-ticker{
    position: relative;
    overflow: hidden;
    background: linear-gradient(90deg, #dc3545, #ff6b6b);
    color: #fff;
    border-radius: 999px;
    padding: 4px 0;
    margin-top: .5rem;
    margin-bottom: .75rem;
    box-shadow: 0 6px 16px rgba(220,53,69,.35);
    display: none;              /* default: sembunyikan */
  }
  .lastorder-ticker.is-active{
    display: block;             /* muncul kalau sudah 1 jam sebelumnya */
  }
  .lastorder-track{
    display: inline-block;
    white-space: nowrap;
    will-change: transform;
    animation: lastorder-scroll 22s linear infinite;
    padding-left: 100%; /* mulai dari luar kanan */
  }
  .lastorder-ticker:hover .lastorder-track{
    animation-play-state: paused; /* bisa dihentikan kalau di-hover */
  }
  .lastorder-label{
    display: inline-flex;
    align-items: center;
    font-weight: 600;
    text-transform: uppercase;
    font-size: .75rem;
    padding: .15rem .75rem;
    background: rgba(0,0,0,.25);
    border-radius: 999px;
    margin-right: .75rem;
    letter-spacing: .04em;
  }
  .lastorder-label i{
    font-size: .9rem;
    margin-right: .25rem;
  }
  .lastorder-text{
    font-size: .8rem;
  }
  .lastorder-text strong{
    font-weight: 700;
  }
  @media (min-width: 768px){
    .lastorder-text{ font-size: .9rem; }
  }
  @keyframes lastorder-scroll{
    0%   { transform: translateX(0); }
    100% { transform: translateX(-100%); }
  }
</style>


<?php
// Cari ID kategori "Makanan" & "Minuman"
$kat_makanan_id = '';
$kat_minuman_id = '';
if (!empty($kategoris)) {
  foreach ($kategoris as $k) {
    $nm = strtolower(trim($k->nama));
    if ($kat_makanan_id === '' && strpos($nm, 'makanan') !== false) {
      $kat_makanan_id = (string)$k->id;
    }
    if ($kat_minuman_id === '' && strpos($nm, 'minuman') !== false) {
      $kat_minuman_id = (string)$k->id;
    }
  }
}


/**
 * Ticker LAST ORDER & BUKA KEMBALI
 * - Basis: jam operasional di tabel identitas (op_*_open / op_*_close / op_*_closed)
 * - Support nyebrang hari (mis. 10:00–01:00)
 * - State:
 *   - last_hour    : 0–60 menit sebelum jam tutup (tampilkan "batas JAM TUTUP ORDER...")
 *   - after_close  : setelah jam tutup s/d jam buka berikutnya (tampilkan "order sudah tutup, buka kembali jam ...")
 *   - none         : selain itu (disembunyikan)
 */
$last_order_label = '';
$next_open_label  = '';
$ticker_state     = 'none';

// Ambil row identitas lengkap
$ident = (isset($rec) && is_object($rec)) ? $rec : null;

if ($ident && property_exists($ident, 'op_mon_open')) {

    // === Timezone toko ===
    $tzId = isset($ident->waktu) && trim((string)$ident->waktu) !== ''
        ? trim((string)$ident->waktu)
        : 'Asia/Makassar';

    try {
        $tz  = new DateTimeZone($tzId);
    } catch (Exception $e) {
        $tz  = new DateTimeZone('Asia/Makassar');
    }

    $now = new DateTime('now', $tz);

    // Helper parse "08.00" / "8:00" -> [h,i] atau null
    $parseTime = function($s) {
        $s = trim((string)$s);
        if ($s === '') return null;
        $s = str_replace('.', ':', $s);
        if (!preg_match('/^(\d{1,2}):([0-5]\d)$/', $s, $m)) return null;
        $h = max(0, min(23, (int)$m[1]));
        $i = (int)$m[2];
        return [$h, $i];
    };

    $dayKeys = [
        1 => 'mon',
        2 => 'tue',
        3 => 'wed',
        4 => 'thu',
        5 => 'fri',
        6 => 'sat',
        7 => 'sun',
    ];

    $todayNum     = (int)$now->format('N'); // 1=Mon..7=Sun
    $todayKey     = $dayKeys[$todayNum];
    $yesterdayNum = ($todayNum === 1) ? 7 : $todayNum - 1;
    $tomorrowNum  = ($todayNum === 7) ? 1 : $todayNum + 1;

    $yesterdayKey = $dayKeys[$yesterdayNum];
    $tomorrowKey  = $dayKeys[$tomorrowNum];

    // Base date (00:00) untuk hari ini / kemarin / besok
    $todayDate     = (clone $now)->setTime(0, 0, 0);
    $yesterdayDate = (clone $todayDate)->modify('-1 day');
    $tomorrowDate  = (clone $todayDate)->modify('+1 day');

    // Bangun 1 "session" buka-tutup untuk 1 hari:
    // - openDT: tanggal-buka
    // - closeDT: tanggal-tutup (kalau wrap, bisa +1 hari)
    $buildSession = function($baseDate, $dayKey) use ($ident, $parseTime) {
        $openField   = "op_{$dayKey}_open";
        $closeField  = "op_{$dayKey}_close";
        $closedField = "op_{$dayKey}_closed";

        $isClosed = property_exists($ident, $closedField)
            ? ((int)$ident->{$closedField} === 1)
            : false;

        if ($isClosed) {
            return null;
        }

        $openStr = property_exists($ident, $openField)
            ? trim((string)$ident->{$openField})
            : '';
        $closeStr = property_exists($ident, $closeField)
            ? trim((string)$ident->{$closeField})
            : '';

        $openArr  = $parseTime($openStr);
        $closeArr = $parseTime($closeStr);

        if ($openArr === null || $closeArr === null) {
            return null;
        }

        list($oh, $om) = $openArr;
        list($ch, $cm) = $closeArr;

        $openDT = clone $baseDate;
        $openDT->setTime($oh, $om, 0);

        $closeDT = clone $baseDate;

        $openMin  = $oh * 60 + $om;
        $closeMin = $ch * 60 + $cm;

        if ($closeMin >= $openMin) {
            // Tutup di hari yang sama
            $closeDT->setTime($ch, $cm, 0);
        } else {
            // Tutup lewat tengah malam (wrap) → geser ke hari berikutnya
            $closeDT->modify('+1 day')->setTime($ch, $cm, 0);
        }

        return [
            'openDT'  => $openDT,
            'closeDT' => $closeDT,
            'openStr' => sprintf('%02d:%02d', $oh, $om),
            'closeStr'=> sprintf('%02d:%02d', $ch, $cm),
        ];
    };

    $sYesterday = $buildSession($yesterdayDate, $yesterdayKey);
    $sToday     = $buildSession($todayDate, $todayKey);
    $sTomorrow  = $buildSession($tomorrowDate, $tomorrowKey);

    // === Session aktif (kalau sekarang sedang jam buka) ===
    $activeSession = null;
    if ($sYesterday && $now >= $sYesterday['openDT'] && $now < $sYesterday['closeDT']) {
        $activeSession = $sYesterday;
    } elseif ($sToday && $now >= $sToday['openDT'] && $now < $sToday['closeDT']) {
        $activeSession = $sToday;
    }

    // === Last close (< now) dari kemarin / hari ini ===
    $lastCloseDT = null;
    if ($sYesterday && $sYesterday['closeDT'] <= $now) {
        $lastCloseDT = $sYesterday['closeDT'];
    }
    if ($sToday && $sToday['closeDT'] <= $now) {
        if ($lastCloseDT === null || $sToday['closeDT'] > $lastCloseDT) {
            $lastCloseDT = $sToday['closeDT'];
        }
    }

    // === Next open (> now) dari hari ini / besok ===
    $nextOpenDT = null;
    if ($sToday && $now < $sToday['openDT']) {
        $nextOpenDT = $sToday['openDT'];
    }
    if ($sTomorrow && $now < $sTomorrow['openDT']) {
        if ($nextOpenDT === null || $sTomorrow['openDT'] < $nextOpenDT) {
            $nextOpenDT = $sTomorrow['openDT'];
        }
    }

    // === Tentukan state ticker ===
    if ($activeSession) {
        // Lagi jam buka → cek apakah sudah masuk 0–60 menit terakhir
        $last_order_label = $activeSession['closeStr'];

        $diffMinutes = (int) round(
            ($activeSession['closeDT']->getTimestamp() - $now->getTimestamp()) / 60
        );

        if ($diffMinutes <= 60 && $diffMinutes >= 0) {
            $ticker_state = 'last_hour';
        } else {
            $ticker_state = 'none';
        }

    } else {
        // Lagi tutup → kalau di antara lastClose & nextOpen → after_close
        if ($lastCloseDT && $nextOpenDT && $now > $lastCloseDT && $now < $nextOpenDT) {
            $ticker_state     = 'after_close';
            $last_order_label = $lastCloseDT->format('H:i');
            $next_open_label  = $nextOpenDT->format('H:i');
        }
    }
}
?>

<div class="container-fluid">
  <div class="mt-2">
     <?php if ($ticker_state !== 'none' && $last_order_label !== ''): ?>
  <!-- Tulisan berjalan batas jam tutup / buka kembali -->
  <div class="lastorder-ticker is-active"
       role="status"
       aria-label="Peringatan batas jam tutup"
       data-state="<?= html_escape($ticker_state); ?>">
    <div class="lastorder-track">
      <span class="lastorder-label">
        <i class="mdi mdi-alert" aria-hidden="true"></i> INFO
      </span>

      <!-- 0–60 menit sebelum jam tutup -->
      <span class="lastorder-text lastorder-before"
      style="<?= $ticker_state === 'last_hour' ? '' : 'display:none;'; ?>">
      Batas <strong>jam tutup order</strong> makanan &amp; minuman itu sampai pukul
      <strong><?= html_escape($last_order_label); ?> Waktu Siwa hari ini</strong>.
      Yuk selesaikan pesanan dan tuntaskan pembayaran sebelum jam itu ya. Makasih banyak atas pengertiannya. 💛
    </span>


      <!-- Setelah lewat jam tutup -->
      <?php if ($next_open_label !== ''): ?>
        <span class="lastorder-text lastorder-after"
        style="<?= $ticker_state === 'after_close' ? '' : 'display:none;'; ?>">
        <!-- Untuk sementara <strong>order online lagi tutup dulu</strong>, ya.   -->
        Kami bakal <strong>buka lagi</strong> pukul
        <strong><?= html_escape($next_open_label); ?> Waktu Siwa</strong>.  
        Ditunggu kehadirannya di jam operasional berikutnya. 💛
      </span>
    <?php endif; ?>

    </div>
  </div>
<?php endif; ?>

    <?php $this->load->view("judul_mode") ?>

    <div id="mode-info"
    data-mode="<?= html_escape($mode ?? '') ?>"
    data-meja="<?= html_escape($meja_info ?? '') ?>">
  </div>
</div>

<form id="filter-form" class="mb-0">
  <input type="hidden" id="kategori" name="kategori" value="<?= html_escape($kategori); ?>">
  <input type="hidden" id="sort" name="sort" value="<?= html_escape($sort ?: 'random'); ?>">
  <input type="hidden" id="recommended" name="recommended" value="0">

  <div class="filter-toolbar">
    <?php $this->load->view("form_cari") ?>

    <div class="filter-sort">
      <div class="dropdown">
        <button class="btn btn-danger dropdown-toggle d-flex align-items-center"
        type="button"
        id="dropdownSortBtn"
        data-toggle="dropdown"
        aria-haspopup="true"
        aria-expanded="false">
        <span id="sortBtnLabel">Urutkan</span>&nbsp;<i class="mdi mdi-chevron-down"></i>
      </button>

      <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownSortBtn">
        <a class="dropdown-item sort-opt" data-sort="random" href="javascript:void(0)">
          <i class="fas fa-thumbs-up me-2"></i> Untukmu
        </a>
        <a class="dropdown-item sort-opt" data-sort="new" href="javascript:void(0)">
          <i class="fas fa-clock me-2"></i> Terbaru
        </a>

        <!-- ⬇️ Tambahan: Trending -->
        <div class="dropdown-divider"></div>
        <a class="dropdown-item sort-opt" data-sort="trending" data-trend="today" href="javascript:void(0)">
          <i class="fas fa-bolt me-2"></i> Favorit • Hari ini
        </a>
        <a class="dropdown-item sort-opt" data-sort="trending" data-trend="week" href="javascript:void(0)">
          <i class="fas fa-bolt me-2"></i> Favorit • 7 hari
        </a>
        <a class="dropdown-item sort-opt" data-sort="trending" data-trend="month" href="javascript:void(0)">
          <i class="fas fa-bolt me-2"></i> Favorit • 30 hari
        </a>
        <div class="dropdown-divider"></div>

        <a class="dropdown-item sort-opt" data-sort="bestseller" href="javascript:void(0)">
          <i class="fas fa-star me-2"></i> Terlaris
        </a>
        <a class="dropdown-item sort-opt" data-sort="price_low" href="javascript:void(0)">
          <i class="fas fa-arrow-down me-2"></i> Harga Rendah
        </a>
        <a class="dropdown-item sort-opt" data-sort="price_high" href="javascript:void(0)">
          <i class="fas fa-arrow-up me-2"></i> Harga Tinggi
        </a>
        <a class="dropdown-item sort-opt" data-sort="sold_out" href="javascript:void(0)">
          <i class="fas fa-ban me-2"></i> Habis
        </a>
      </div>

    </div>
  </div>
</div>

<!-- QUICKMENU -->
<div class="quickmenu-wrap position-relative mb-1" id="grandong">
  <div id="quickmenu" class="quickmenu-scroll d-flex text-center" tabindex="0" aria-label="Kategori">

    <div class="quickmenu-item" data-kategori="">
      <div class="qcard">
        <div class="menu-circle" >
          <span class="emoji-icon" data-anim="all">🍽️</span>
        </div>
        <small class="menu-label">Semua</small>
      </div>
    </div>

    <div class="quickmenu-item" data-kategori="<?= html_escape($kat_makanan_id); ?>">
      <div class="qcard">
        <div class="menu-circle" >
          <span class="emoji-icon" data-anim="food">🍝</span>
        </div>
        <small class="menu-label">Makanan</small>
      </div>
    </div>

    <div class="quickmenu-item" data-kategori="<?= html_escape($kat_minuman_id); ?>">
      <div class="qcard">
        <div class="menu-circle" >
          <span class="emoji-icon" data-anim="drink">☕</span>
        </div>
        <small class="menu-label">Minuman</small>
      </div>
    </div>
    <div class="quickmenu-item" data-recommended="1">
      <div class="qcard">
        <div class="menu-circle">
          <span class="emoji-icon" data-anim="all">🔥</span>
        </div>
        <small class="menu-label">Andalang</small>
      </div>
    </div>
    <div class="quickmenu-item" data-tipe="paket">
      <div class="qcard">
        <div class="menu-circle">
          <span class="emoji-icon" data-anim="all">🍱</span>
        </div>
        <small class="menu-label">Hemat</small>
      </div>
    </div>



       <!--  <div class="quickmenu-item" data-action="cart">
          <a class="qcard d-block text-decoration-none"
             href="<?= site_url('produk/cart') ?>"
             aria-label="Buka keranjang">
            <div class="menu-circle" >
              🍱<span class="q-badge" data-anim="cart" id="cart-count">0</span>
            </div>
            <small class="menu-label">Keranjang</small>
          </a>
        </div>
      -->
    </div>
  </div>
</form>

<div class="row mt-1" id="grid-products"></div>
<div class="row"><div class="col-12" id="pagination-wrap"></div></div>
</div>

<!-- FAB Cart -->
<a href="<?= site_url('produk/cart') ?>"
 id="fab-cart"
 class="fab-cart"
 aria-label="Buka keranjang">
 <span class="spinner-border d-none" aria-hidden="true"></span>
 <i class="mdi mdi-cart-outline icon-default" aria-hidden="true"></i>

 <?php if (!empty($meja_info)): ?>
  <span class="fab-label d-none d-sm-inline">Meja <?= html_escape($meja_info) ?></span>
<?php endif; ?>

<span class="fab-badge" id="fab-count">0</span>
</a>

<!-- TOUR TOOLTIP KERANJANG -->
<div id="fab-cart-tooltip" class="cart-tour-tooltip" role="dialog" aria-live="polite">
  <div class="cart-tour-card">
    <!-- Badge bulat di pojok -->
    <div class="cart-tour-step">
      <i class="mdi mdi-cart-outline" aria-hidden="true"></i>
    </div>

    <div class="cart-tour-content">
      <div class="cart-tour-title">Ini keranjang Anda</div>
      <div class="cart-tour-text">
        Di sini tersimpan pesanan Anda. Klik untuk melihat &amp; menyelesaikan orderan.
      </div>

      <div class="cart-tour-actions">
        <button type="button"
        id="fab-cart-tooltip-ok"
        class="cart-tour-btn-primary">
        OK, saya paham
      </button>
    </div>
  </div>
</div>
</div>



<?php
// Asumsi timezone sudah di-set di config (Asia/Makassar)
// 1 = Senin, 2 = Selasa, ..., 5 = Jumat, 7 = Minggu
$today = (int) date('N');

if ($today === 5) {
    // Hari Jumat
  $this->load->view("promo_jumat_berkah");
} else {
    // Selain Jumat
  $this->load->view("promo_mingguan");
}
?>

<?php $this->load->view("front_end/footer.php") ?>
<script>
  window.AUSI_CFG = {
    sub_api     : "<?= site_url('produk/subkategori/'); ?>",
    list_ajax   : "<?= site_url('produk/list_ajax'); ?>",
    add_to_cart : "<?= site_url('produk/add_to_cart'); ?>",
    cart_count  : "<?= site_url('produk/cart_count'); ?>"
  };
</script>

<!-- Custom logic kita -->
<script src="<?= base_url('assets/front/produk.min.js') ?>?v=<?= filemtime(FCPATH.'assets/front/produk.min.js'); ?>"></script>


<?php $this->load->view("modal_produk") ?>