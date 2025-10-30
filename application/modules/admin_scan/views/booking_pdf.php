<?php
/** @var object $booking */
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$CI =& get_instance();

/* ===== Helper Hari/Tanggal Indonesia ===== */
if (!function_exists('hari_indo')) {
  function hari_indo($ts){
    $map=[1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu',7=>'Minggu'];
    $n=(int)date('N',$ts?:time());
    return $map[$n]??'';
  }
}
if (!function_exists('fmt_hari_tanggal')) {
  // $with_time=true -> tampilkan jam juga
  function fmt_hari_tanggal($datestr,$with_time=false){
    if(empty($datestr)) return '-';
    $ts = is_numeric($datestr) ? (int)$datestr : strtotime($datestr);
    if(!$ts) return '-';
    $hari = hari_indo($ts);
    $tgl  = date('d-m-Y',$ts);
    if($with_time){
      $jam = date('H:i',$ts);
      return "{$hari}, {$tgl} {$jam}";
    }
    return "{$hari}, {$tgl}";
  }
}

/* Unit tujuan */
$unit_nama = '-';
if (!empty($booking->unit_tujuan)) {
  $unit_nama = (string)$CI->db->select('nama_unit')
    ->get_where('unit_tujuan', ['id' => $booking->unit_tujuan])
    ->row('nama_unit');
  if ($unit_nama === '') $unit_nama = '-';
}

/* Instansi asal */
$instansi_asal = $booking->target_instansi_nama ?: ($booking->instansi ?: '-');
$nama_petugas_instansi = $booking->nama_petugas_instansi ?: ($booking->nama_petugas_instansi ?: '-');

/* Status → badge */
$status    = strtolower((string)$booking->status);
$badgeText = strtoupper($status ?: '-');
$badgeBg   = '#6b7280';
if ($status === 'approved')    $badgeBg = '#2563eb';
if ($status === 'checked_in')  $badgeBg = '#0ea5e9';
if ($status === 'checked_out') $badgeBg = '#16a34a';
if ($status === 'pending')     $badgeBg = '#f59e0b';
if ($status === 'rejected')    $badgeBg = '#ef4444';
if ($status === 'expired')     $badgeBg = '#94a3b8';

/* Logo & QR (pakai path lokal agar aman di TCPDF) */
$logo_file   = FCPATH.'assets/images/logo.png';
$logo_exists = is_file($logo_file);
$qr_file     = FCPATH.'uploads/qr/qr_'.$booking->kode_booking.'.png';
$qr_exists   = is_file($qr_file);

/* Waktu (dengan hari) */
$fmt_jadwal = '-';
if (!empty($booking->tanggal) || !empty($booking->jam)) {
  // gabungkan tanggal + jam untuk diformat sekaligus
  $base = trim(($booking->tanggal ?? '').' '.($booking->jam ?? ''));
  $fmt_jadwal = fmt_hari_tanggal($base, true);
}
$fmt_chk_hari = !empty($booking->checkin_at)  ? fmt_hari_tanggal($booking->checkin_at,  true) : '-';
$fmt_chx_hari = !empty($booking->checkout_at) ? fmt_hari_tanggal($booking->checkout_at, true) : '-';

/* Durasi (kalau sudah checkout) */
$durasi_text = '';
if (!empty($booking->checkin_at) && !empty($booking->checkout_at)) {
  $a = new DateTime($booking->checkin_at);
  $b = new DateTime($booking->checkout_at);
  $diff = $a->diff($b);
  $menitTotal = $diff->days*24*60 + $diff->h*60 + $diff->i;
  $jam = floor($menitTotal/60); $mnt = $menitTotal%60;
  $durasi_text = ($jam>0? $jam.' jam ' : '').$mnt.' menit';
}

/* Waktu cetak (dengan hari) */
$printed_at = fmt_hari_tanggal(time(), true);
?>
<?php $web = $this->om->web_me(); ?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Tiket <?= e($booking->kode_booking) ?></title>
<style>
  /* ===== Compact layout untuk A5 ===== */
  body{font-family:helvetica,arial,sans-serif;font-size:9.6pt;color:#111;margin:0}

  .badge{display:inline-block;color:#fff;padding:2px 5px;border-radius:4px;font-weight:700;font-size:8.6pt}
  .print-at{font-size:8.2pt;color:#666}

  table.meta{width:100%;border-collapse:collapse}
  table.meta td{padding:1.4mm 0;vertical-align:top}
  table.meta td.k{width:40%;color:#555}
  table.meta td.v{font-weight:700}

  .two-col{width:100%;border-collapse:collapse}
  .two-col td{vertical-align:top}
  .two-col .left{width:65%}
  .two-col .right{width:35%;text-align:center}

  .qr-title{font-weight:700;margin-bottom:1.5mm}
  .qr-img{width:40mm;max-width:100%;border:1px solid #eee;border-radius:5px;padding:1.5mm;background:#fff}
  .qr-sub{font-size:8pt;color:#666;margin-top:1.2mm}

  .note{font-size:8.6pt;color:#444;margin-top:2.5mm}
  .note ul{margin:1.5mm 0 0 4mm;padding:0}

  .foot{border-top:1px dashed #aaa;margin-top:3.5mm;padding-top:2mm;text-align:center;color:#666;font-size:8.6pt}
</style>
</head>
<body>

  <!-- ===== HEADER: logo kiri + teks kanan ===== -->
  
   <table>
    <tr>
      <td align="center" width="25%">
       <?php if ($logo_exists): ?>
        <img style="width: 30px" src="<?= $logo_file ?>" alt="Logo">
      <?php endif; ?>
    </td>
    <td align="center">
     <strong><?php echo strtoupper($web->type) ?></strong>
     <div style="font-size: 9px">Alamat : <?php echo $web->alamat ?></div>
   </td>
 </tr>
</table>

  <hr style="border:0;border-top:1px solid #e5e7eb;margin:2mm 0 3mm;">

  <!-- Kode booking -->
  <div align="center" style="font-weight:700;margin-bottom:3mm;"><strong>Kode: <?= e($booking->kode_booking) ?></strong></div>

  <!-- ===== Konten dua kolom ===== -->
  <table class="two-col">
    <tr>
      <!-- Kiri: detail -->
      <td class="left">
        <table class="meta">
          <tr><td class="k">Nama Tamu</td>     <td class="v"><?= e($booking->nama_tamu) ?></td></tr>
          <tr><td class="k">Jabatan</td>       <td class="v"><?= e($booking->jabatan) ?></td></tr>
          <tr><td class="k">NIK/NIP/NRP</td>           <td class="v"><?= e($booking->nik) ?></td></tr>
          <tr><td class="k">No. HP</td>        <td class="v"><?= e($booking->no_hp) ?></td></tr>
          <tr><td class="k">Instansi Asal</td> <td class="v"><?= e($instansi_asal) ?></td></tr>
          <tr><td class="k">Unit Tujuan</td>   <td class="v"><?= e($unit_nama) ?></td></tr>
          <tr><td class="k">Pejabat Unit</td>  <td class="v"><?= e($nama_petugas_instansi) ?></td></tr>
          <tr><td class="k">Keperluan</td>     <td class="v"><?= e($booking->keperluan) ?></td></tr>
          <tr><td class="k">Tanggal & Jam</td> <td class="v"><?= e($fmt_jadwal) ?></td></tr>
          <tr><td class="k">Pendamping</td>    <td class="v"><?= (int)$booking->jumlah_pendamping ?> orang</td></tr>
          <tr><td class="k">Check-in</td>      <td class="v"><?= e($fmt_chk_hari) ?></td></tr>
          <?php if (!empty($booking->checkout_at)): ?>
            <tr><td class="k">Checkout</td>    <td class="v"><?= e($fmt_chx_hari) ?></td></tr>
            <tr><td class="k">Durasi</td>      <td class="v"><?= e($durasi_text) ?></td></tr>
          <?php endif; ?>
        </table>
      </td>

      <!-- Kanan: QR -->
      <td class="right">
        <div class="qr-title">QR Code</div>
        <?php if ($qr_exists): ?>
          <img class="qr-img" src="<?= $qr_file ?>" alt="QR">
          <div class="qr-sub">Scan di pos keluar</div>
        <?php else: ?>
          <div style="font-size:9.2pt;color:#888;">QR belum tersedia.</div>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <!-- Status & waktu cetak -->
  <div style="margin-top:3mm;">
    <span class="badge" style="background:<?= $badgeBg ?>;margin-right:4px;"><?= e($badgeText) ?></span>
    <span class="print-at">Dicetak: <?= e($printed_at) ?></span>
  </div>
  <!-- Catatan -->
  <div class="note">
    <ul>
      <li>Bawa tiket ini selama kunjungan.</li>
      <li>Tunjukkan tiket & QR saat proses keluar.</li>
    </ul>
  </div>

  <div class="foot">
    <?php echo $web->nama_website." • ". $web->meta_deskripsi ?>
     • <?= e(base_url()) ?>
  </div>

</body>
</html>
