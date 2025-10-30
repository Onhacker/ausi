<?php $this->load->view("front_end/head.php"); ?>

<?php
// ====== Ambil & siapkan data dari $rec ======
$rec    = isset($rec) ? $rec : (object)[];
$tzName = !empty($rec->waktu) ? (string)$rec->waktu : 'Asia/Makassar';

try { $tz = new DateTimeZone($tzName); } catch(Exception $e) { $tz = new DateTimeZone('Asia/Makassar'); $tzName = 'Asia/Makassar'; }
$now = new DateTime('now', $tz);

// Helpers kecil
$hariMap  = [0=>'Minggu',1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu'];
$abbrTZ   = ($tzName==='Asia/Jakarta'?'WIB':($tzName==='Asia/Makassar'?'WITA':($tzName==='Asia/Jayapura'?'WIT':'')));

$norm = function($s){ // "8.00" / "08:00" -> "08:00" (atau null)
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

// Hari -> key konfigurasi
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

// Hitung info hari ini (untuk highlight dan label "(hari ini)")
$w = (int)$now->format('w');               // 0..6
$k = $daysKey[$w];                         // sun..sat
$nowMin = (int)$now->format('H')*60 + (int)$now->format('i');

$open  = $cfg[$k]['open'];
$close = $cfg[$k]['close'];
$bs    = $cfg[$k]['break_start'];
$be    = $cfg[$k]['break_end'];
$isClosedDay = $cfg[$k]['closed'] || !$open || !$close;

// (Status detail—kalau nanti perlu dipakai)
$statusToday = 'Tutup';
if (!$isClosedDay) {
  $o = $toMin($open); $c = $toMin($close);
  $inOpen  = ($o!==null && $c!==null && $nowMin >= $o && $nowMin <= $c);
  $inBreak = ($bs && $be) ? ($nowMin >= $toMin($bs) && $nowMin < $toMin($be)) : false;
  $statusToday = $inOpen ? ($inBreak ? 'Istirahat' : 'Buka') : 'Tutup';
}

// ===== Tambahan helper untuk windows & rate =====

// Ubah "HH:MM:SS" → "HH:MM" lalu normalisasi (tanpa fallback)
$hm = function($s) use ($norm){ return $norm(substr((string)$s,0,5)); };

// Cek apakah waktu "t" (menit) berada dalam span s–e (menit); dukung overnight
$inSpan = function(int $s, int $e, int $t): bool {
  if ($e <= $s) { // overnight
    $e += 24*60;
    if ($t < $s) $t += 24*60;
  }
  return ($t >= $s && $t <= $e);
};

// Formatter rentang waktu dengan redaksi "hari ini/besoknya" jika lintas hari
$fmtRange = function(?string $start, ?string $end) use ($toMin, $dot){
  if (!$start || !$end) return '—';
  $s = $toMin($start); $e = $toMin($end);
  if ($e !== null && $s !== null && $e <= $s) {
    return $dot($start).' hari ini – '.$dot($end).' besoknya';
  }
  return $dot($start).' – '.$dot($end);
};

// Formatter + TZ (untuk baris operasional utama)
$fmtRangeTZ = function(?string $start, ?string $end, string $abbr) use ($fmtRange){
  $txt = $fmtRange($start, $end);
  return trim($txt.' '.$abbr);
};
?>

<style>
  /* ====== Card tabel jadwal ====== */
  .op-tablecard{
    border-radius:16px;
    background:#fff;
    box-shadow:0 8px 28px rgba(0,0,0,.08);
    overflow:hidden;
    border:1px solid #e5e7eb;
  }
  .op-tablecard__head{
    display:flex;align-items:center;gap:.5rem;
    padding:12px 16px;
    background:linear-gradient(135deg,#133b79 0%, #06b6d4 100%);
    color:#fff;
  }
  .op-tablecard__head .hint{
    margin-left:auto;opacity:.95;font-weight:600
  }

  .pill{display:inline-block;padding:.22rem .55rem;border-radius:999px;font-weight:700;font-size:.72rem}
  .pill.today{background:#e0e7ff;color:#3730a3}
  .pill.off{background:#fee2e2;color:#991b1b}
  .pill.tz{background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.35)}
  .time-dash{font-variant-numeric:tabular-nums}
  .subnote{font-size:.9rem;color:#64748b;margin-top:4px}
  .kv{display:grid;gap:8px 12px}
  .kv .k{color:#64748b}
  .kv .v{font-weight:600}
  .divider{height:1px;background:#eef2f7;margin:10px 0}
  .op-body{padding:12px 16px}
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

      // ===== Operasional dasar meja (tanpa fallback) =====
      $open_raw  = $hm($m->jam_buka ?? null);
      $close_raw = $hm($m->jam_tutup ?? null);

      $open_min  = $open_raw  !== null ? $toMin($open_raw)  : null;
      $close_min = $close_raw !== null ? $toMin($close_raw) : null;

      $is_overnight = ($open_min !== null && $close_min !== null) ? ($close_min <= $open_min) : false;
      $is_active    = (int)($m->aktif ?? 0) === 1;
      $nowHM        = (int)$now->format('H')*60 + (int)$now->format('i');

      // ===== Windows & rate per meja (tanpa fallback) =====
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

      // Tentukan band “aktif sekarang” berdasar hari & jam saat ini
      $isWeekendNow = ($w === 0 || $w === 6); // 0=Min,6=Sab

      $activeBandLabel = '';
      $activeRate = 0; $activeStart = null; $activeEnd = null;

      if ($isWeekendNow) {
        $dS = $we_day_start   ? $toMin($we_day_start)   : null;
        $dE = $we_day_end     ? $toMin($we_day_end)     : null;
        $nS = $we_night_start ? $toMin($we_night_start) : null;
        $nE = $we_night_end   ? $toMin($we_night_end)   : null;

        $inDayNow   = ($dS!==null && $dE!==null) ? $inSpan($dS, $dE, $nowHM) : false;
        $inNightNow = ($nS!==null && $nE!==null) ? $inSpan($nS, $nE, $nowHM) : false;

        if ($inDayNow)  { $activeBandLabel='Weekend Siang';  $activeRate=$we_day_rate;  $activeStart=$we_day_start;  $activeEnd=$we_day_end; }
        if ($inNightNow){ $activeBandLabel='Weekend Malam';  $activeRate=$we_night_rate;$activeStart=$we_night_start;$activeEnd=$we_night_end; }
      } else {
        $dS = $wk_day_start   ? $toMin($wk_day_start)   : null;
        $dE = $wk_day_end     ? $toMin($wk_day_end)     : null;
        $nS = $wk_night_start ? $toMin($wk_night_start) : null;
        $nE = $wk_night_end   ? $toMin($wk_night_end)   : null;

        $inDayNow   = ($dS!==null && $dE!==null) ? $inSpan($dS, $dE, $nowHM) : false;
        $inNightNow = ($nS!==null && $nE!==null) ? $inSpan($nS, $nE, $nowHM) : false;

        if ($inDayNow)  { $activeBandLabel='Siang'; $activeRate=$wk_day_rate;  $activeStart=$wk_day_start;  $activeEnd=$wk_day_end; }
        if ($inNightNow){ $activeBandLabel='Malam'; $activeRate=$wk_night_rate;$activeStart=$wk_night_start;$activeEnd=$wk_night_end; }
      }

      // Apakah sekarang dalam jendela buka operasional
      $in_window = false;
      if ($open_min!==null && $close_min!==null) {
        if ($is_overnight) {
          $in_window = ($nowHM >= $open_min) || ($nowHM <= $close_min);
        } else {
          $in_window = ($nowHM >= $open_min && $nowHM <= $close_min);
        }
      }

      // Deteksi "belum buka" vs "sesudah tutup"
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
    ?>
      <div class="col-xl-6 col-lg-12">
        <div class="op-tablecard mb-3" aria-label="Jam Buka Meja">
          <div class="op-tablecard__head">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M7 2v3M17 2v3M3 10h18M4 5h16a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
            <strong><?= htmlspecialchars($mejaName, ENT_QUOTES, 'UTF-8') ?></strong>
            <span class="hint pill tz"><?= htmlspecialchars($abbrTZ,ENT_QUOTES,'UTF-8') ?></span>
          </div>

          <div class="op-body">
            <div class="kv">
              <div class="k">Tarif Sekarang</div>
              <div class="v">
                <?php if ($activeRate > 0 && $activeStart && $activeEnd): ?>
                  Rp<?= number_format($activeRate,0,',','.') ?>/jam
                  (<?= htmlspecialchars($activeBandLabel, ENT_QUOTES, 'UTF-8') ?>,
                   <?= $fmtRange($activeStart,$activeEnd) ?>)
                <?php else: ?>
                  —
                <?php endif; ?>
              </div>

              <div class="k">Buka - Tutup</div>
              <div class="v">
                <?php if ($open_raw && $close_raw): ?>
                  <span class="time-dash"><?= $fmtRangeTZ($open_raw,$close_raw,$abbrTZ) ?></span>
                  <?php if (!$in_window): ?>
                    <div class="subnote">
                      <?php if ($before_open): ?>
                        Belum buka · Buka jam <?= $dot($open_raw) ?> 
                      <?php elseif ($after_close): ?>
                        Tutup · Buka lagi <?= $is_overnight ? 'malam ini' : 'besoknya' ?> jam <?= $dot($open_raw) ?> 
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>
                <?php else: ?>
                  —
                <?php endif; ?>
              </div>

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

            <div class="kv mb-3">
              <div class="k">Weekday (Senin - Jumat)</div>
              <div class="v">
                <?php if ($wk_day_start && $wk_day_end): ?>
                  <?= $fmtRange($wk_day_start,$wk_day_end) ?>
                  (Rp<?= number_format($wk_day_rate,0,',','.') ?>/jam)
                <?php else: ?>—<?php endif; ?>
                <br>
                <?php if ($wk_night_start && $wk_night_end): ?>
                  <?= $fmtRange($wk_night_start,$wk_night_end) ?>
                  (Rp<?= number_format($wk_night_rate,0,',','.') ?>/jam)
                <?php else: ?>—<?php endif; ?>
              </div>

              <div class="k">Weekend (Sabtu - Minggu)</div>
              <div class="v">
                <?php if ($we_day_start && $we_day_end): ?>
                  <?= $fmtRange($we_day_start,$we_day_end) ?>
                  (Rp<?= number_format($we_day_rate,0,',','.') ?>/jam)
                <?php else: ?>—<?php endif; ?>
                <br>
                <?php if ($we_night_start && $we_night_end): ?>
                  <?= $fmtRange($we_night_start,$we_night_end) ?>
                  (Rp<?= number_format($we_night_rate,0,',','.') ?>/jam)
                <?php else: ?>—<?php endif; ?>
              </div>
            </div>
            <?php if ($m->catatan) {?>
              <div class="alert alert-success" role="alert">
              <i class="mdi mdi-check-all mr-2"></i> <?php echo $m->catatan ?>
            </div>
            <?php } ?>
            
          </div>

          <div class="ribbon-cta mt-3">
            <a class="cta-btn" href="<?= site_url('billiard?meja_id='.(int)$m->id_meja) ?>"  title="Buka halaman booking billiard">
              <i>Booking Yuk !!</i>
            </a>
          </div>
        </div>
      </div>
      
    <?php endforeach; ?>
  </div>


  <style type="text/css">

              /* CTA Ribbon (bawah kanan) */
              .ribbon-cta{position:absolute;right:10px;bottom:20px;z-index:2}
              .ribbon-cta .cta-btn{
                position:relative;display:inline-flex;align-items:center;gap:1px;
                background:#FF5722;color:#fff;text-decoration:none;
                padding:7px 12px;border-radius:8px;font-weight:800;font-size:12px;
                box-shadow:0 2px 6px rgba(0,0,0,.2);
                transform:rotate(-5deg); border:1px solid rgba(255,255,255,.15)
              }
              .ribbon-cta .cta-btn:hover{filter:brightness(1.05)}
              .ribbon-cta .cta-btn i{font-style:normal;opacity:.95}
              .ribbon-cta .cta-btn:after{
                content:"→"; font-weight:900; line-height:1; transform:translateY(-.5px)
              }

    .op-notex{margin-top:10px;border-radius:14px;padding:10px 12px;color:#000;background:rgba(255,255,255,.12);
      border:1px dashed rgba(255,255,255,.35);line-height:1.4}
    .op-ctax{display:inline-block;background: rgb(27 68 136);
      padding:.55rem 1rem;border-radius:999px;color:#fff;font-weight:800;letter-spacing:.3px;text-decoration:none;
      backdrop-filter: blur(6px);transition:all .2s}
    .op-ctax:hover{background:rgba(255,255,255,.25);transform:scale(1.03)}
  </style>

  <!-- <div class="op-notex" role="note">
    Mau main? Booking aja kapan pun — gampang. Tinggal pilih meja, tanggal & jam di halaman booking, terus konfirmasi. Santuy!
  </div> -->

  <!-- <div style="padding:6px 0 16px; text-align:right;">
    <a href="<?= site_url('billiard') ?>" class="op-ctax" role="button" aria-label="Booking Sekarang">Booking Sekarang</a> -->
  <!-- </div> -->

</div>

<?php $this->load->view("front_end/footer.php"); ?>
