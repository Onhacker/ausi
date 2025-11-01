<?php $this->load->view("front_end/head.php"); ?>

<?php
// ====== Ambil & siapkan data dari $rec ======
$rec    = isset($rec) ? $rec : (object)[];
$tzName = !empty($rec->waktu) ? (string)$rec->waktu : 'Asia/Makassar';

try {
  $tz = new DateTimeZone($tzName);
} catch(Exception $e) {
  $tz = new DateTimeZone('Asia/Makassar');
  $tzName = 'Asia/Makassar';
}

$now = new DateTime('now', $tz);

// Helpers kecil
$hariMap  = [
  0=>'Minggu',1=>'Senin',2=>'Selasa',3=>'Rabu',
  4=>'Kamis',5=>'Jumat',6=>'Sabtu'
];

$abbrTZ   = (
  $tzName==='Asia/Jakarta'  ? 'WIB' :
  ($tzName==='Asia/Makassar'? 'WITA' :
  ($tzName==='Asia/Jayapura'? 'WIT'  : ''))
);

// normalisasi "08.00" / "08:00" → "08:00"
$norm = function($s){
  $s = trim((string)$s);
  if ($s === '') return null;
  $s = str_replace('.', ':', $s);
  if (!preg_match('/^(\d{1,2}):([0-5]\d)$/', $s, $m)) return null;
  $h = max(0, min(23, (int)$m[1]));
  $i = (int)$m[2];
  return sprintf('%02d:%02d', $h, $i);
};

$toMin = function($hhmm){
  if ($hhmm===null) return null;
  [$h,$i] = array_map('intval', explode(':', $hhmm));
  return $h*60 + $i;
};
$dot = fn($s)=> $s ? str_replace(':', '.', $s) : '';

// Hari -> key konfigurasi (disesuaikan dgn field op_sun_open dll)
$daysKey = ['sun','mon','tue','wed','thu','fri','sat'];

// Ambil konfigurasi per hari dari $rec
$cfg = [];
foreach ($daysKey as $k) {
  $cfg[$k] = [
    'open'        => $norm($rec->{"op_{$k}_open"}),
    'break_start' => $norm($rec->{"op_{$k}_break_start"}),
    'break_end'   => $norm($rec->{"op_{$k}_break_end"}),
    'close'       => $norm($rec->{"op_{$k}_close"}),
    'closed'      => (int)($rec->{"op_{$k}_closed"}) ? 1 : 0,
  ];
}

// Info hari ini
$w = (int)$now->format('w');               // 0..6
$k = $daysKey[$w];                         // sun..sat
$nowMin = (int)$now->format('H')*60 + (int)$now->format('i');

$open  = $cfg[$k]['open'];
$close = $cfg[$k]['close'];
$bs    = $cfg[$k]['break_start'];
$be    = $cfg[$k]['break_end'];
$isClosedDay = $cfg[$k]['closed'] || !$open || !$close;

// Status (internal / optional)
$statusToday = 'Tutup';
if (!$isClosedDay) {
  $o = $toMin($open); $c = $toMin($close);
  $inOpen  = ($o!==null && $c!==null && $nowMin >= $o && $nowMin <= $c);
  $inBreak = ($bs && $be) ? ($nowMin >= $toMin($bs) && $nowMin < $toMin($be)) : false;
  $statusToday = $inOpen ? ($inBreak ? 'Istirahat' : 'Buka') : 'Tutup';
}

// ===== Helper jam =====

// Ubah "HH:MM:SS" → "HH:MM" lalu normalisasi
$hm = function($s) use ($norm){
  return $norm(substr((string)$s,0,5));
};

// Apakah menit t berada di span s–e (dukung overnight)
$inSpan = function(int $s, int $e, int $t): bool {
  if ($e <= $s) { // overnight
    $e += 24*60;
    if ($t < $s) $t += 24*60;
  }
  return ($t >= $s && $t <= $e);
};

// Mapping rentang jam → label
// Dini hari: 00.00 – 04.59
// Pagi:      05.00 – 09.59
// Siang:     10.00 – 14.59
// Sore:      15.00 – 17.59
// Malam:     18.00 – 23.59
$daypartName = function(int $minute){
  if ($minute < 300) {          // <05:00
    return 'dini hari';
  } elseif ($minute < 600) {    // <10:00
    return 'pagi';
  } elseif ($minute < 900) {    // <15:00
    return 'siang';
  } elseif ($minute < 1080) {   // <18:00
    return 'sore';
  } else {
    return 'malam';
  }
};

// Deskripsi manusia utk 1 shift AKTIF SEKARANG
// "Hari ini siang - sore, 10.00 – 18.00 WITA"
// atau "Hari ini malam - besok dini hari, 18.00 – 02.00 WITA"
$humanBandDesc = function($startHHMM, $endHHMM, $abbrTZ) use ($toMin, $dot, $daypartName){
  if (!$startHHMM || !$endHHMM) return '';
  $sMin = $toMin($startHHMM);
  $eMin = $toMin($endHHMM);
  if ($sMin === null || $eMin === null) return '';

  $overnight = ($eMin <= $sMin);

  $labelStart = $daypartName($sMin);

  // pakai menit akhir -1 biar 10-18 jadi "siang - sore" bukan "siang - malam"
  $eMinLabel = $eMin - 1;
  if ($eMinLabel < 0) { $eMinLabel = 1439; }
  $labelEnd = $daypartName($eMinLabel % (24*60));

  if ($overnight){
    return "Hari ini $labelStart - besok $labelEnd, ".$dot($startHHMM)." – ".$dot($endHHMM)." $abbrTZ";
  } else {
    return "Hari ini $labelStart - $labelEnd, ".$dot($startHHMM)." – ".$dot($endHHMM)." $abbrTZ";
  }
};

// Deskripsi manusia utk shift dgn rate → buat "Nanti malam ..."
$shiftLineDesc = function($startHHMM,$endHHMM,$rate,$abbrTZ) use ($humanBandDesc){
  if (!$startHHMM || !$endHHMM) return '';
  $base = $humanBandDesc($startHHMM,$endHHMM,$abbrTZ);
  if ($base === '') return '';
  if ((int)$rate > 0){
    $base .= " · Rp".number_format((int)$rate,0,',','.')."/jam";
  }
  return $base;
};

// ====== Deskripsi TANPA "Hari ini"/"besok", buat tabel Tarif Senin-Jumat & Sabtu-Minggu
// Output contoh: "Siang - sore, 10.00 – 18.00 WITA"
// atau "Malam - dini hari, 18.00 – 02.00 WITA"
$bandDescGeneric = function($startHHMM,$endHHMM,$abbrTZ) use ($toMin,$dot,$daypartName){
  if (!$startHHMM || !$endHHMM) return '';
  $sMin = $toMin($startHHMM);
  $eMin = $toMin($endHHMM);
  if ($sMin === null || $eMin === null) return '';

  $overnight = ($eMin <= $sMin);

  $labelStart = $daypartName($sMin);

  $eMinLabel = $eMin - 1;
  if ($eMinLabel < 0) { $eMinLabel = 1439; }
  $labelEnd = $daypartName($eMinLabel % (24*60));

  // "Siang - sore, 10.00 – 18.00 WITA"
  // atau "Malam - dini hari, 18.00 – 02.00 WITA"
  $labelStartUc = ucfirst($labelStart);
  $labelEndTxt  = $labelEnd;

  return $labelStartUc." - ".$labelEndTxt.", ".$dot($startHHMM)." – ".$dot($endHHMM)." $abbrTZ";
};

// sama tapi plus rate
$bandDescGenericWithRate = function($startHHMM,$endHHMM,$rate,$abbrTZ) use ($bandDescGeneric){
  if (!$startHHMM || !$endHHMM) return '';
  $base = $bandDescGeneric($startHHMM,$endHHMM,$abbrTZ);
  if ($base === '') return '';
  if ((int)$rate > 0){
    $base .= " · Rp".number_format((int)$rate,0,',','.')."/jam";
  }
  return $base;
};
?>

<style>
  /* ====== Card ====== */
  .op-tablecard{
    border-radius:16px;
    background:#fff;
    box-shadow:0 8px 28px rgba(0,0,0,.08);
    overflow:hidden;
    border:1px solid #e5e7eb;
    position:relative;
  }
  .op-tablecard__head{
    display:flex;
    align-items:center;
    padding:12px 16px;
    background:linear-gradient(135deg,#133b79 0%, #06b6d4 100%);
    color:#fff;
  }
  .op-tablecard__head svg{
    margin-right:6px;
    flex-shrink:0;
  }
  .op-tablecard__head strong{
    font-weight:700;
    color:#fff;
  }
  .op-tablecard__head .hint{
    margin-left:auto;
    opacity:.95;
    font-weight:600
  }

  .pill{
    display:inline-block;
    padding:.22rem .55rem;
    border-radius:999px;
    font-weight:700;
    font-size:.72rem
  }
  .pill.today{background:#e0e7ff;color:#3730a3}
  .pill.off{background:#fee2e2;color:#991b1b}
  .pill.tz{
    background:rgba(255,255,255,.2);
    color:#fff;
    border:1px solid rgba(255,255,255,.35)
  }

  .live-time-pill{
    display:inline-block;
    background:#ffedd5; /* oranye muda */
    color:#9a3412;
    font-weight:700;
    font-size:.8rem;
    line-height:1.2;
    border-radius:8px;
    padding:4px 8px;
    border:1px solid rgba(0,0,0,.08);
    box-shadow:0 2px 4px rgba(0,0,0,.08);
  }

  .tarif-line{
    display:flex;
    flex-wrap:wrap;
    align-items:center;
    margin-bottom:4px;
  }
  .tarif-line .now-rate{
    font-weight:700;
    color:#111;
    font-size:.9rem;
    line-height:1.2;
    margin-left:6px;
  }

  .subnote {
    font-size: .9rem;
    color: #005a08;
    line-height: 1.4;
}
  .subnote + .subnote {
    margin-top:4px;
  }

  .kv{
    display:grid;
    gap:8px 12px
  }
  .kv .k{
    color:#000;
    font-weight:700; /* k bold */
  }
  .kv .v{
    font-weight:400; /* v normal */
    color:#111;
  }

  .divider{
    height:1px;
    background:#eef2f7;
    margin:12px 0
  }
  .op-body{
    padding:12px 16px
  }

  /* box tarif weekday / weekend */
  .tarif-box{
    border-radius:12px;
    padding:10px 12px;
    margin-bottom:10px;
    font-size:.9rem;
    line-height:1.4;
    border:1px solid transparent;
  }
  /* Weekday: biru muda */
  .tarif-weekday{
    background:#f0f9ff;
    border-color:#bae6fd;
    color:#1e293b;
  }
  /* Weekend: ungu muda */
  .tarif-weekend{
    background:#f5f3ff;
    border-color:#ddd6fe;
    color:#1e293b;
  }

  .tarif-box-title{
    font-weight:700;
    font-size:.8rem;
    line-height:1.3;
    text-transform:uppercase;
    margin-bottom:4px;
    letter-spacing:.2px;
    color:#1e293b;
  }
  .tarif-item-line{
    margin-bottom:4px;
  }

  /* Voucher dashed box */
  .voucher-box{
    border-radius:12px;
    border:2px dashed #eab308;           /* kuning amber */
    background:#fffbeb;                  /* kuning muda */
    color:#444;
    padding:12px 14px;
    font-size:.85rem;
    line-height:1.45;
    font-weight:500;
    margin-top:12px;
    margin-bottom:12px;
  }
  .voucher-box-title{
    font-weight:700;
    font-size:.9rem;
    line-height:1.3;
    color:#854d0e;                        /* amber-800 */
    display:flex;
    align-items:center;
    gap:.4rem;
    margin-bottom:4px;
  }
  .voucher-box-title .emoji{
    font-size:1rem;
    line-height:1;
  }
  .voucher-box a{
    color:#854d0e;
    text-decoration:underline;
  }

  /* CTA ribbon btn */
  .ribbon-cta{
    position:absolute;
    right:10px;
    bottom:20px;
    z-index:2
  }
  .ribbon-cta .cta-btn{
    position:relative;
    display:inline-flex;
    align-items:center;
    background:#FF5722;
    color:#fff;
    text-decoration:none;
    padding:7px 12px;
    border-radius:8px;
    font-weight:800;
    font-size:12px;
    box-shadow:0 2px 6px rgba(0,0,0,.2);
    transform:rotate(-5deg);
    border:1px solid rgba(255,255,255,.15)
  }
  .ribbon-cta .cta-btn:hover{filter:brightness(1.05)}
  .ribbon-cta .cta-btn i{font-style:normal;opacity:.95}
  .ribbon-cta .cta-btn:after{
    content:"→";
    font-weight:900;
    line-height:1;
    transform:translateY(-.5px)
  }
</style>

<div class="container-fluid">

  <div class="hero-title ausi-hero-center" role="banner" aria-label="Judul halaman">
    <?php $this->load->view("front_end/back") ?>
    <h1 class="text"><?= htmlspecialchars($rec->title ?? ($title ?? 'Jadwal Kunjungan'), ENT_QUOTES, 'UTF-8') ?></h1>
    <?php if (!empty($deskripsi)): ?>
      <div class="text-white"><?= htmlspecialchars($deskripsi, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <span class="accent" aria-hidden="true"></span>
  </div>

  <div class="row">
    <?php foreach($mejas as $m):

      // ===== Operasional dasar meja =====
      $open_raw  = $hm($m->jam_buka ?? null);
      $close_raw = $hm($m->jam_tutup ?? null);

      $open_min  = $open_raw  !== null ? $toMin($open_raw)  : null;
      $close_min = $close_raw !== null ? $toMin($close_raw) : null;

      $is_overnight = ($open_min !== null && $close_min !== null) ? ($close_min <= $open_min) : false;
      $is_active    = (int)($m->aktif ?? 0) === 1;
      $nowHM        = (int)$now->format('H')*60 + (int)$now->format('i');

      // ===== Windows & rate per meja =====
      $wk_day_start   = $hm($m->wk_day_start   ?? null);
      $wk_day_end     = $hm($m->wk_day_end     ?? null);
      $wk_day_rate    = (int)($m->wk_day_rate  ?? 0);

      $wk_night_start = $hm($m->wk_night_start ?? null);
      $wk_night_end   = $hm($m->wk_night_end   ?? null);
      $wk_night_rate  = (int)($m->wk_night_rate?? 0);

      $we_day_start   = $hm($m->we_day_start   ?? null);
      $we_day_end     = $hm($m->we_day_end     ?? null);
      $we_day_rate    = (int)($m->we_day_rate  ?? 0);

      $we_night_start = $hm($m->we_night_start ?? null);
      $we_night_end   = $hm($m->we_night_end   ?? null);
      $we_night_rate  = (int)($m->we_night_rate?? 0);

      // Weekend?
      $isWeekendNow = ($w === 0 || $w === 6); // 0=Min,6=Sab

      // Siang/malam status sekarang
      $inDayNow   = false;
      $inNightNow = false;

      // Band aktif sekarang
      $activeRate   = 0;
      $activeStart  = null;
      $activeEnd    = null;

      if ($isWeekendNow) {
        $dS = $we_day_start   ? $toMin($we_day_start)   : null;
        $dE = $we_day_end     ? $toMin($we_day_end)     : null;
        $nS = $we_night_start ? $toMin($we_night_start) : null;
        $nE = $we_night_end   ? $toMin($we_night_end)   : null;

        $inDayNow   = ($dS!==null && $dE!==null) ? $inSpan($dS, $dE, $nowHM) : false;
        $inNightNow = ($nS!==null && $nE!==null) ? $inSpan($nS, $nE, $nowHM) : false;

        if ($inDayNow)  { $activeRate=$we_day_rate;   $activeStart=$we_day_start;   $activeEnd=$we_day_end; }
        if ($inNightNow){ $activeRate=$we_night_rate; $activeStart=$we_night_start; $activeEnd=$we_night_end; }

      } else {
        $dS = $wk_day_start   ? $toMin($wk_day_start)   : null;
        $dE = $wk_day_end     ? $toMin($wk_day_end)     : null;
        $nS = $wk_night_start ? $toMin($wk_night_start) : null;
        $nE = $wk_night_end   ? $toMin($wk_night_end)   : null;

        $inDayNow   = ($dS!==null && $dE!==null) ? $inSpan($dS, $dE, $nowHM) : false;
        $inNightNow = ($nS!==null && $nE!==null) ? $inSpan($nS, $nE, $nowHM) : false;

        if ($inDayNow)  { $activeRate=$wk_day_rate;   $activeStart=$wk_day_start;   $activeEnd=$wk_day_end; }
        if ($inNightNow){ $activeRate=$wk_night_rate; $activeStart=$wk_night_start; $activeEnd=$wk_night_end; }
      }

      // Status buka sekarang
      $in_window = false;
      if ($open_min!==null && $close_min!==null) {
        if ($is_overnight) {
          $in_window = ($nowHM >= $open_min) || ($nowHM <= $close_min);
        } else {
          $in_window = ($nowHM >= $open_min && $nowHM <= $close_min);
        }
      }

      $before_open = false; $after_close = false;
      if ($open_min!==null && $close_min!==null) {
        if ($is_overnight) {
          $before_open = ($nowHM > $close_min) && ($nowHM < $open_min);
          $after_close = ($nowHM <= $close_min);
        } else {
          $before_open = ($nowHM < $open_min);
          $after_close = ($nowHM > $close_min);
        }
      }

      $mejaName = trim((string)($m->nama_meja ?? ''));
      if ($mejaName === '') $mejaName = 'Meja '.(int)$m->id_meja;

      // Voucher context
      $isRegMeja        = (strtolower((string)($m->kategori ?? '')) === 'reguler'); // reguler / vip
      $voucher_end_raw  = $hm($m->jam_tutup_voucer ?? null); // batas voucher meja ini
      $voucher_duration = (int)($rec->jam_voucher_default ?? 1); // jam gratis/voucher
      $batas_main       = (int)($rec->batas_edit ?? 0); // berapa kali main normal utk dapat voucher

      // Kalimat band aktif (shift SEKARANG)
      $bandText = '';
      if ($activeRate > 0 && $activeStart && $activeEnd){
        $bandText = $humanBandDesc($activeStart, $activeEnd, $abbrTZ);
      }

      // SHIFT BERIKUTNYA ("Nanti malam ...") → hanya kalau sekarang masih shift siang/sore
      $nextShiftLine = '';
      if ($inDayNow) {
        if ($isWeekendNow) {
          if ($we_night_start && $we_night_end){
            $tmp = $shiftLineDesc($we_night_start,$we_night_end,$we_night_rate,$abbrTZ);
            $nextShiftLine = preg_replace('/^Hari ini malam\b/u', 'Nanti malam', $tmp);
          }
        } else {
          if ($wk_night_start && $wk_night_end){
            $tmp = $shiftLineDesc($wk_night_start,$wk_night_end,$wk_night_rate,$abbrTZ);
            $nextShiftLine = preg_replace('/^Hari ini malam\b/u', 'Nanti malam', $tmp);
          }
        }
      }

      // BOX TARIF UNTUK DISPLAY (tanpa "Hari ini")
      $wk_day_desc   = '';
      $wk_night_desc = '';
      if ($wk_day_start && $wk_day_end){
        $wk_day_desc = $bandDescGenericWithRate($wk_day_start,$wk_day_end,$wk_day_rate,$abbrTZ);
      }
      if ($wk_night_start && $wk_night_end){
        $wk_night_desc = $bandDescGenericWithRate($wk_night_start,$wk_night_end,$wk_night_rate,$abbrTZ);
      }

      $we_day_desc   = '';
      $we_night_desc = '';
      if ($we_day_start && $we_day_end){
        $we_day_desc = $bandDescGenericWithRate($we_day_start,$we_day_end,$we_day_rate,$abbrTZ);
      }
      if ($we_night_start && $we_night_end){
        $we_night_desc = $bandDescGenericWithRate($we_night_start,$we_night_end,$we_night_rate,$abbrTZ);
      }
    ?>

      <div class="col-xl-6 col-lg-12">
        <div class="op-tablecard mb-3" aria-label="Info Meja Billiard">
          <div class="op-tablecard__head">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M7 2v3M17 2v3M3 10h18M4 5h16a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"
                    stroke="#fff" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
            <strong><?= htmlspecialchars($mejaName, ENT_QUOTES, 'UTF-8') ?><?= ($m->kategori?' - '.htmlspecialchars($m->kategori,ENT_QUOTES,'UTF-8'):'') ?></strong>
            <span class="hint pill tz"><?= htmlspecialchars($abbrTZ,ENT_QUOTES,'UTF-8') ?></span>
          </div>

          <div class="op-body">
            <div class="kv">
              <!-- TARIF SEKARANG -->
              <div class="k">Tarif Sekarang</div>
              <div class="v">
                <div class="tarif-line mb-2">
                  <span class="live-time-pill" data-live-time></span>
                  <?php if ($activeRate > 0 && $activeStart && $activeEnd): ?>
                    <span class="now-rate">
                      Rp<?= number_format($activeRate,0,',','.') ?>/jam
                    </span>
                  <?php else: ?>
                    <span class="now-rate">—</span>
                  <?php endif; ?>
                </div>

                <?php if ($bandText !== ''): ?>
                  <div class="subnote">
                    <?= htmlspecialchars($bandText, ENT_QUOTES, 'UTF-8') ?>
                  </div>
                <?php else: ?>
                  <div class="subnote">Tarif aktif saat ini tidak terdeteksi.</div>
                <?php endif; ?>

                <?php if ($nextShiftLine !== ''): ?>
                  <div class="subnote">
                    <?= htmlspecialchars($nextShiftLine, ENT_QUOTES, 'UTF-8') ?>
                  </div>
                <?php endif; ?>
              </div>

              <!-- STATUS -->
              <div class="k">Status</div>
              <div class="v">
                <?php if ($is_active): ?>
                  <span class="pill" style="background:#ecfdf5;color:#065f46;border-radius:8px">Aktif</span>
                <?php else: ?>
                  <span class="pill off" style="border-radius:8px">Nonaktif</span>
                <?php endif; ?>

                <?php if ($in_window): ?>
                  <span class="pill today" style="border-radius:8px;margin-left:6px">Buka</span>
                <?php elseif ($before_open): ?>
                  <span class="pill" style="background:#fef3c7;color:#9a3412;border-radius:8px;margin-left:6px">Belum buka</span>
                <?php elseif ($after_close): ?>
                  <span class="pill off" style="border-radius:8px;margin-left:6px">Tutup</span>
                <?php endif; ?>
              </div>
            </div>

            <div class="divider"></div>

            <!-- ===== TARIF SENIN - JUMAT ===== -->
            <div class="tarif-box tarif-weekday">
              <div class="tarif-box-title">Tarif Senin - Jumat</div>

              <?php if ($wk_day_desc !== ''): ?>
                <div class="tarif-item-line">
                  <?= htmlspecialchars($wk_day_desc, ENT_QUOTES, 'UTF-8') ?>
                </div>
              <?php endif; ?>

              <?php if ($wk_night_desc !== ''): ?>
                <div class="tarif-item-line">
                  <?= htmlspecialchars($wk_night_desc, ENT_QUOTES, 'UTF-8') ?>
                </div>
              <?php endif; ?>

              <?php if ($wk_day_desc === '' && $wk_night_desc === ''): ?>
                <div class="tarif-item-line">—</div>
              <?php endif; ?>
            </div>

            <!-- ===== TARIF SABTU & MINGGU ===== -->
            <div class="tarif-box tarif-weekend">
              <div class="tarif-box-title">Tarif Sabtu &amp; Minggu</div>

              <?php if ($we_day_desc !== ''): ?>
                <div class="tarif-item-line">
                  <?= htmlspecialchars($we_day_desc, ENT_QUOTES, 'UTF-8') ?>
                </div>
              <?php endif; ?>

              <?php if ($we_night_desc !== ''): ?>
                <div class="tarif-item-line">
                  <?= htmlspecialchars($we_night_desc, ENT_QUOTES, 'UTF-8') ?>
                </div>
              <?php endif; ?>

              <?php if ($we_day_desc === '' && $we_night_desc === ''): ?>
                <div class="tarif-item-line">—</div>
              <?php endif; ?>
            </div>

            <!-- ================== VOUCHER INFO ================== -->
            <?php if ($isRegMeja): ?>
              <div class="voucher-box">
                <div class="voucher-box-title">
                  <span class="emoji">🎟️</span>
                  <span>Voucher Main Gratis</span>
                </div>

                <div>
                  Main normal <b><?= $batas_main ?></b>x ⇒ dapat
                  <b>1 Voucher Main Gratis <?= $voucher_duration ?> jam</b>.
                </div>
                <div>
                  Voucher berlaku di 
                  <b><?php echo $mejaName ?></b> ini.
                </div>
                <div>
                  Jadwal pakai voucher:
                  bisa dipakai mulai
                  <b>jam <?= $dot($open_raw) ?></b>
                  sampai
                  <b>
                  <?php if ($voucher_end_raw): ?>
                    jam <?= $dot($voucher_end_raw) ?> <?= htmlspecialchars($abbrTZ,ENT_QUOTES,'UTF-8') ?>
                  <?php else: ?>
                    batas promo meja ini
                  <?php endif; ?>
                  </b>.
                </div>
                <div style="font-size:.8rem;margin-top:4px;">
                  <a href="<?= site_url('hal#voucher') ?>">
                    Syarat &amp; Ketentuan berlaku
                  </a>
                </div>
              </div>
            <?php endif; ?>
            <!-- ================== /VOUCHER INFO ================== -->

            <?php if ($m->catatan) { ?>
              <div class="alert alert-success" role="alert" style="font-size:.85rem; line-height:1.4;">
                <i class="mdi mdi-check-all mr-2"></i> <?= $m->catatan ?>
              </div>
            <?php } ?>

          </div><!-- /.op-body -->

          <div class="ribbon-cta mt-3">
            <a class="cta-btn"
               href="<?= site_url('billiard?meja_id='.(int)$m->id_meja) ?>"
               title="Buka halaman booking billiard">
              <i>Booking Yuk !!</i>
            </a>
          </div>

        </div><!-- /.op-tablecard -->
      </div><!-- /.col -->

    <?php endforeach; ?>
  </div><!-- /.row -->

</div><!-- /.container-fluid -->

<!-- ====== LIVE CLOCK SCRIPT ======
     Update semua <span data-live-time> setiap detik
     Format: "Sabtu · 16.43.12 WITA"
-->
<script>
(function(){
  function two(n){ return (n<10?'0':'')+n; }
  function hariNama(idx){
    const map=['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    return map[idx] || '';
  }
  function tick(){
    const now   = new Date();
    const hari  = hariNama(now.getDay());
    const jam   = two(now.getHours());
    const menit = two(now.getMinutes());
    const detik = two(now.getSeconds());
    const txt   = hari + ' · ' + jam + '.' + menit + '.' + detik + ' <?= $abbrTZ ?>';
    document.querySelectorAll('[data-live-time]').forEach(function(el){
      el.textContent = txt;
    });
  }
  tick();
  setInterval(tick,1000);
})();
</script>

<?php $this->load->view("front_end/footer.php"); ?>
