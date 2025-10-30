<?php
// ====== Ambil & siapkan data dari $rec ======
$rec    = isset($rec) ? $rec : (object)[];
$tzName = !empty($rec->waktu) ? (string)$rec->waktu : 'Asia/Makassar';

try { $tz = new DateTimeZone($tzName); } catch(Exception $e) { $tz = new DateTimeZone('Asia/Makassar'); $tzName = 'Asia/Makassar'; }
$now = new DateTime('now', $tz);

// Helpers kecil
$hariMap  = [0=>'Minggu',1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu'];
$bulanMap = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
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

// Default bila DB kosong
// $def = [
//   'mon'=>['open'=>'08:00','break_start'=>'12:00','break_end'=>'13:00','close'=>'15:00','closed'=>0],
//   'tue'=>['open'=>'08:00','break_start'=>'12:00','break_end'=>'13:00','close'=>'15:00','closed'=>0],
//   'wed'=>['open'=>'08:00','break_start'=>'12:00','break_end'=>'13:00','close'=>'15:00','closed'=>0],
//   'thu'=>['open'=>'08:00','break_start'=>'12:00','break_end'=>'13:00','close'=>'15:00','closed'=>0],
//   'fri'=>['open'=>'08:00','break_start'=>'11:30','break_end'=>'13:00','close'=>'14:00','closed'=>0],
//   'sat'=>['open'=>'08:00','break_start'=>null,'break_end'=>null,'close'=>'11:30','closed'=>0],
//   'sun'=>['open'=>null,'break_start'=>null,'break_end'=>null,'close'=>null,'closed'=>1],
// ];
$daysKey = ['sun','mon','tue','wed','thu','fri','sat'];

// Ambil konfigurasi per hari dari $rec
$cfg = [];
foreach ($daysKey as $k) {
  $cfg[$k] = [
    'open'        => $norm($rec->{"op_{$k}_open"}       ),
    'break_start' => $norm($rec->{"op_{$k}_break_start"}),
    'break_end'   => $norm($rec->{"op_{$k}_break_end"}  ),
    'close'       => $norm($rec->{"op_{$k}_close"}      ),
    'closed'      => (int)($rec->{"op_{$k}_closed"}     ) ? 1 : 0,
  ];
}

// ===== Hitung status hari ini (informatif) =====
// ===== Hitung status hari ini (informatif) — SUPPORT NYEBRANG HARI =====
$w       = (int)$now->format('w');           // 0..6 (0=Sun)
$daysKey = ['sun','mon','tue','wed','thu','fri','sat'];
$kToday  = $daysKey[$w];                     // key hari ini
$kYest   = $daysKey[($w+6)%7];               // key kemarin

$toMinSafe = function($s) use ($toMin){ return $s ? $toMin($s) : null; };
$nowMin  = (int)$now->format('H')*60 + (int)$now->format('i');

// ambil cfg hari ini & kemarin
$cfgToday = $cfg[$kToday];
$cfgYest  = $cfg[$kYest];

// normalisasi ke menit
$oT = $toMinSafe($cfgToday['open']);
$cT = $toMinSafe($cfgToday['close']);
$bsT = $toMinSafe($cfgToday['break_start']);
$beT = $toMinSafe($cfgToday['break_end']);

$oY = $toMinSafe($cfgYest['open']);
$cY = $toMinSafe($cfgYest['close']);
$bsY = $toMinSafe($cfgYest['break_start']);
$beY = $toMinSafe($cfgYest['break_end']);

// helper: cek window nyebrang
$isWrapT = ($oT !== null && $cT !== null && $cT <= $oT);
$isWrapY = ($oY !== null && $cY !== null && $cY <= $oY);

// Tentukan “window aktif” sekarang:
// - Jika sekarang antara 00:00 dan jam tutup HARI INI DAN hari ini wrap => window-nya mulai KEMARIN open → HARI INI close
// - Jika sekarang antara 00:00 dan jam tutup HARI INI tapi hari ini TIDAK wrap => itu bukan window aktif (sudah lewat); cek biasa
// - Jika tidak di rentang itu, pakai window HARI INI (bisa wrap ke besok)
$useYesterdayWindow = false;
if ($isWrapT && $cT !== null && $nowMin <= $cT) {
    // 00:00..close_today → window kemarin  open → today close
    $useYesterdayWindow = true;
}

$active = $useYesterdayWindow ? $cfgYest : $cfgToday;
$oA = $useYesterdayWindow ? $oY : $oT;
$cA = $useYesterdayWindow ? $cY : $cT;
$bsA= $useYesterdayWindow ? $bsY: $bsT;
$beA= $useYesterdayWindow ? $beY: $beT;
$isWrapA = ($oA !== null && $cA !== null && $cA <= $oA);
$activeClosedFlag = (int)($active['closed'] ?? 0);

$active = $useYesterdayWindow ? $cfgYest : $cfgToday;
$oA = $useYesterdayWindow ? $oY : $oT;
$cA = $useYesterdayWindow ? $cY : $cT;
$bsA= $useYesterdayWindow ? $bsY: $bsT;
$beA= $useYesterdayWindow ? $beY: $beT;
$isWrapA = ($oA !== null && $cA !== null && $cA <= $oA);
$activeClosedFlag = (int)($active['closed'] ?? 0);

/* >>> FIX: definisikan variabel yang dipakai di view <<< */
$isClosedDay = ($activeClosedFlag === 1) || ($oA === null) || ($cA === null);

/* Untuk display di UI */
$open   = $active['open'];   // "HH:MM" | null
$close  = $active['close'];  // "HH:MM" | null
$bs     = $active['break_start'];
$be     = $active['break_end'];
$hasBreak = ($bs !== null && $be !== null && $toMin($bs) < $toMin($be));

// Untuk display di UI (jam buka–tutup)
$open   = $active['open'];   // string "HH:MM" (bisa null)
$close  = $active['close'];  // string "HH:MM"
$bs     = $active['break_start'];
$be     = $active['break_end'];
$hasBreak = ($bs !== null && $be !== null && $toMin($bs) < $toMin($be));

// Hitung status
$statusKey   = 'off';        // ok | rest | off
$statusLabel = 'Tutup';
$statusNote  = '';

if ($activeClosedFlag || $oA===null || $cA===null) {
    $statusKey   = 'off';
    $statusLabel = 'Libur';
    $statusNote  = 'Tidak ada layanan kunjungan pada hari ini.';
} else {
    // cek apakah sekarang berada di dalam window aktif
    $inWindow = false;

    if ($isWrapA) {
        // contoh 10:00 → 03:00
        // window: [open..23:59] ∪ [00:00..close]
        if ($useYesterdayWindow) {
            // window kemarin→hari ini: kita sekarang pasti di segmen [00:00..close] (karena syarat di atas)
            $inWindow = ($nowMin <= $cA);
        } else {
            // window hari ini→besok
            $inWindow = ($nowMin >= $oA); // segmen [open..23:59], besoknya dihandle besok
        }
    } else {
        // tidak wrap: [open..close] pada hari yang sama
        $inWindow = ($nowMin >= $oA && $nowMin <= $cA);
    }

   if (!$inWindow) {
    // di luar window aktif → tentukan seTutup / sesudah tutup
    if ($isWrapA) {
        // untuk window wrap hari ini, bila belum >= open berarti "Tutup"
        if (!$useYesterdayWindow && $nowMin < $oA) {
            $statusKey   = 'off';
            $statusLabel = 'Tutup nih';
            $statusNote  = 'Buka mulai: '.$dot($open).'–'.$dot($close).' '.$abbrTZ.'. Datang aja mulai pukul '.$dot($open).' ya!';
        } else {
            // sisanya dianggap “Sudah tutup”
            $statusKey   = 'off';
            $statusLabel = 'Udah tutup';
            $statusNote  = 'Wah, udah tutup — hari ini tutup jam '.$dot($close).' '.$abbrTZ.'. Cek lagi besok ya!';
        }
    } else {
        if ($nowMin < $oA) {
            $statusKey   = 'off';
            $statusLabel = 'Tutup';
            $statusNote  = 'Buka mulai: '.$dot($open).'–'.$dot($close).' '.$abbrTZ.'. Datang aja mulai pukul '.$dot($open).' ya!';
        } else {
            $statusKey   = 'off';
            $statusLabel = 'Udah tutup';
            $statusNote  = 'Wah, udah tutup — hari ini tutup jam '.$dot($close).' '.$abbrTZ.'. Cek lagi besok ya!';
        }
    }
}
 else {
        // di dalam window → cek break (pakai break dari hari aktif)
        $inBreak = false;
        if ($hasBreak) {
            $bsMin = $toMin($bs);
            $beMin = $toMin($be);
            // break dianggap tidak wrap (umumnya jam siang)
            $inBreak = ($nowMin >= $bsMin && $nowMin < $beMin);
        }

        if ($inBreak) {
            $statusKey   = 'rest';
            $statusLabel = 'Istirahat';
            $statusNote  = 'Cafe dibuka kembali pukul '.$dot($be).' '.$abbrTZ.' dan berakhir pukul '.$dot($close).' '.$abbrTZ.'.';
        } else {
            $statusKey   = 'ok';
            $statusLabel = 'Buka';
            if ($hasBreak && $nowMin < $toMin($bs)) {
                $statusNote = 'Istirahat: '.$dot($bs).'–'.$dot($be).' '.$abbrTZ.'. Tutup pukul '.$dot($close).' '.$abbrTZ.'.';
            } else {
                $statusNote = 'Tutup pukul '.$dot($close).' '.$abbrTZ.'.';
            }
        }
    }
}


// Lokasi (opsional)
$kab = isset($rec->kabupaten) ? trim((string)$rec->kabupaten) : '';
$provRaw = isset($rec->provinsi) ? trim((string)$rec->provinsi) : '';
$prov = $provRaw !== '' ? ucwords(mb_strtolower($provRaw, 'UTF-8')) : '';
// Tanggal: "Hari, dd NamaBulan yyyy"
$hariNama   = $hariMap[$w];
$bulanNama  = $bulanMap[(int)$now->format('n')];
$tanggalIndo = "{$hariNama}, ".$now->format('d')." {$bulanNama} ".$now->format('Y');

// ----------------------------
// Delivery (menggunakan delivery_cutoff_enabled + delivery_cutoff)
// ----------------------------
// ----------------------------
// Delivery (menggunakan delivery_cutoff_enabled + delivery_cutoff)
// ----------------------------
$delivery_enabled = (int)($rec->delivery_cutoff_enabled ?? 0) === 1;

// Normalize delivery_cutoff (harus "HH:MM" atau null)
$raw_dc = isset($rec->delivery_cutoff) ? trim((string)$rec->delivery_cutoff) : '';
$delivery_cutoff = $norm($raw_dc); // hasil "HH:MM" atau null
$delivery_cutoff_min = ($delivery_cutoff !== null) ? $toMin($delivery_cutoff) : null;

// ============ HITUNG DELIVERY OPEN NOW ============
// Syarat: delivery_enabled, bukan hari libur, punya cutoff, & berada di rentang [openA .. min(cutoff, closeA)], tidak sedang break.
$delivery_open_now = false;

if ($delivery_enabled && !$isClosedDay && $delivery_cutoff_min !== null && $oA !== null && $cA !== null) {
    // Anchor start = jam buka; end = cutoff (dibatasi oleh jam tutup jika perlu)
    $start = $oA;                    // menit dari 00:00 (hari anchor)
    $end   = $delivery_cutoff_min;   // menit dari 00:00 (hari anchor)

    // Jika layanan overnight (open > close), angkat end & close ke timeline yang sama
    $endAdj = $end;
    $cAdj   = $cA;

    if ($isWrapA) {
        // contoh: open 18:00 (1080), close 03:00 (180) → close harus +1440 (1620)
        if ($cAdj <= $start) $cAdj += 1440;
        // jika cutoff "tampak" lebih kecil dari open (mis. 02:00), artinya cutoff hari berikutnya → +1440
        if ($endAdj <= $start) $endAdj += 1440;
    }

    // End tidak boleh melewati jam tutup global hari itu
    if ($cAdj !== null && $cAdj < $endAdj) {
        $endAdj = $cAdj;
    }

    // Samakan timeline now
    $nowAdj = $nowMin;
    if ($isWrapA) {
        // Jika saat ini berada di segmen after-midnight (00:00..close), dan kita menggunakan window kemarin
        // angkat now agar berada pada timeline yang sama dengan start/end
        if (($useYesterdayWindow ?? false) && $nowMin <= $cA) {
            $nowAdj += 1440;
        }
    }

    // Cek break (angkat ke timeline sama)
    $inBreakLocal = false;
    if ($hasBreak) {
        $bsMin = $toMin($bs); // bisa null
        $beMin = $toMin($be); // bisa null

        if ($bsMin !== null) {
            if ($isWrapA && $bsMin < $start) $bsMin += 1440;
        }
        if ($beMin !== null) {
            if ($isWrapA && $beMin < $start) $beMin += 1440;
            if ($bsMin !== null && $beMin <= $bsMin) $beMin += 1440; // jaga konsistensi urutan
        }

        if ($bsMin !== null && $beMin !== null) {
            $inBreakLocal = ($nowAdj >= $bsMin && $nowAdj < $beMin);
        }
    }

    // FINAL: harus setelah/tepat jam buka DAN sebelum/tepat cutoff (yang sudah dibatasi tutup) DAN bukan saat break
    $delivery_open_now = ($nowAdj >= $start && $nowAdj <= $endAdj && !$inBreakLocal);
}

// Label ramah
if (!$delivery_enabled) {
    $delivery_label = 'Delivery: Nonaktif';
} elseif ($delivery_cutoff === null) {
    $delivery_label = 'Delivery: Tidak ada cutoff';
} else {
    // Tampilkan dari jam buka → cutoff (bukan jam tutup umum)
    $delivery_label = 'Delivery: ' . $dot($open) . ' – ' . $dot($delivery_cutoff) . ' ' . $abbrTZ;
}

?>
<style type="text/css">
/* ====== Card gaya “event” ====== */
.op-card{position:relative;border-radius:22px;padding:22px;overflow:hidden;color:#fff;
  background:linear-gradient(135deg, #4a81d4 0%, #005f6c 100%);box-shadow:0 8px 28px rgba(0,0,0,.12)}
  
.op-card:before{content:"";position:absolute;inset:0;
  background:radial-gradient(1200px 420px at -10% 0%,rgba(255,255,255,.08),transparent 60%),
             radial-gradient(1200px 420px at 110% 100%,rgba(255,255,255,.08),transparent 60%)}
.op-title{font-weight:800;font-size:1.5rem;margin:0 0 .35rem}
.op-row{display:flex;align-items:center;gap:.5rem;margin:.15rem 0}
.op-row .ico{display:inline-flex;width:26px;height:26px;border-radius:8px;align-items:center;justify-content:center;
  background:rgba(255,255,255,.15)}
.op-row .txt{font-weight:600}
.op-row .sub{opacity:.95}
.op-badge{padding:.0rem .7rem;border-radius:999px;font-weight:800;letter-spacing:.2px;background:rgba(255,255,255,.18)}
.op-badge.ok{background:rgba(16,185,129,.9)}     /* hijau */
.op-badge.rest{background:rgba(245,158,11,.95)}  /* oranye */
.op-badge.off{background:rgba(239,68,68,.95)}    /* merah */

/* CTA: mobile = inline, desktop = pojok kanan-bawah (seperti sebelumnya) */
.op-cta{display:inline-block;background:rgba(255,255,255,.18);
  padding:.55rem 1rem;border-radius:999px;color:#fff;font-weight:800;letter-spacing:.3px;text-decoration:none;
  backdrop-filter: blur(6px);transition:all .2s}
.op-cta:hover{background:rgba(255,255,255,.25);transform:scale(1.03)}
@media(min-width: 768px){
  .op-cta{position:absolute;right:20px;bottom:23px}
}

/* Keterangan di dalam card */
.op-note{margin-top:10px;border-radius:14px;padding:5px 7px;color:#fff;background:rgba(255,255,255,.12);
  border:1px dashed rgba(255,255,255,.35);line-height:1.4}
.hero-title .text{font-weight:800}
</style>

<div class="op-card mb-2">
  <div class="op-title">Order (Hari Ini)</div>

  <div class="op-row">
    <span class="ico" aria-hidden="true">
      <!-- calendar -->
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M7 2v3M17 2v3M3 10h18M4 5h16a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/></svg>
    </span>
    <span class="txt"><?= htmlspecialchars($tanggalIndo, ENT_QUOTES, 'UTF-8') ?></span>
  </div>

  <div class="op-row">
    <span class="ico" aria-hidden="true">
      <!-- timer -->
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M10 2h4M12 8v4l3 2M5.5 5.5l2 2" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/><circle cx="12" cy="14" r="7" stroke="#fff" stroke-width="1.6"/></svg>
    </span>
    <span class="txt">
      <?php if($isClosedDay): ?>
        LIBUR
      <?php else: ?>
        <?= $dot($open) ?> – <?= $dot($close) ?> <?= $abbrTZ ?>
        <?php if($hasBreak): ?>
          <span class="sub">(Istirahat <?= $dot($bs) ?>–<?= $dot($be) ?>)</span>
        <?php endif; ?>
      <?php endif; ?>
    </span>
  </div>

   

  <div class="op-row">
    <span class="ico" aria-hidden="true">
      <!-- clock -->
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 6v6l4 2" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="9" stroke="#fff" stroke-width="1.6"/></svg>
    </span>
    <span class="txt" id="liveTime" data-tz="<?= htmlspecialchars($tzName,ENT_QUOTES,'UTF-8'); ?>">
      <?= $now->format('H:i:s') . ' ' . $abbrTZ /* fallback SSR; akan ditimpa JS */ ?>
    </span>
    <span class="op-badge <?= $statusKey ?>" aria-label="Status hari ini"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></span>
  </div>
  <div class="op-row">
    <span class="ico" aria-hidden="true">
      <!-- delivery icon -->
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M3 7h13v10H3z" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/><path d="M16 8h3l2 3v4" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/><circle cx="7.5" cy="16.5" r="1.5" stroke="#fff" stroke-width="1.6"/><circle cx="18.5" cy="18.5" r="1.5" stroke="#fff" stroke-width="1.6"/></svg>
    </span>

    <span class="txt">
      <?php if (!$delivery_enabled): ?>
        Delivery: <strong>Nonaktif</strong>
      <?php elseif ($delivery_cutoff === null): ?>
        Delivery: <strong>Tidak tersedia</strong>
      <?php else: ?>
        <?= htmlspecialchars($delivery_label, ENT_QUOTES, 'UTF-8') ?>
        <!-- <span class="sub"><?= htmlspecialchars($delivery_status_note, ENT_QUOTES, 'UTF-8') ?></span> -->
      <?php endif; ?>
    </span>

    <span class="op-badge <?= ($delivery_open_now ? 'ok' : 'off') ?>" aria-label="Delivery status">
      <?= $delivery_open_now ? 'Buka' : 'Tutup' ?>
    </span>
  </div>

  <?php if ($statusNote): ?>
    <div class="op-row text-center mt-1">
     <!--  <span class="ico" aria-hidden="true">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="9" stroke="#fff" stroke-width="1.6"/><path d="M12 8.2v.01M12 11v5" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/>
        </svg>
      </span> -->
      <div class="op-note" role="note">
      <span class="sub"><?= htmlspecialchars($statusNote, ENT_QUOTES, 'UTF-8') ?></span>
      </div>
      
    </div>
  <?php endif; ?>

 
  <!-- Keterangan DI DALAM CARD -->
 

    <a class="op-cta mt-2" href="<?= site_url('produk'); ?>">Order</a>

</div>

<script>
// Jam live sesuai timezone dari server-config ($rec->waktu).
(function(){
  const el = document.getElementById('liveTime');
  if (!el) return;
  const tz = el.getAttribute('data-tz') || 'Asia/Makassar';
  const fmtTime = new Intl.DateTimeFormat('id-ID', {
    timeZone: tz, hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:false
  });
  function tick(){ el.textContent = fmtTime.format(new Date()) + ' <?= $abbrTZ ?>'; }
  tick();
  setInterval(tick, 1000);
})();
</script>
