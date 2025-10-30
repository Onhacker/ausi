<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($title ?? 'Laporan Booking Tamu'); ?></title>
<style>
  .title   { font-size: 13px; font-weight: bold; margin: 2px 0 4px; text-transform: uppercase; }
  .kop-org { font-size: 12pt; font-weight: 800; letter-spacing: .3px; }
  .kop-sub { font-size: 9pt;  color:#555; }
  .meta    { font-size: 10px; color:#444; margin: 6px 0 10px; text-align: center; }
  table    { width: 100%; border-collapse: collapse; }
  th, td   { border: 1px solid #777; padding: 5px 6px; font-size: 10px; }
  th       { background-color: #cce5ff; }
  .small   { font-size: 9px; color:#555; }
  .center  { text-align: center; }
  .no-border td { border: none !important; }
</style>
</head>
<body>
<?php
/* Helper hari + tanggal Indonesia */
if (!function_exists('hari_tanggal_id')) {
  function hari_tanggal_id($tgl, $jam = null){
    if (empty($tgl)) return '-';
    $ts = strtotime(trim($tgl.' '.($jam ?? '')));
    if (!$ts) return '-';
    $map = ['Sun'=>'Minggu','Mon'=>'Senin','Tue'=>'Selasa','Wed'=>'Rabu','Thu'=>'Kamis','Fri'=>'Jumat','Sat'=>'Sabtu'];
    $hari = $map[date('D',$ts)] ?? date('D',$ts);
    $tglf = date('d-m-Y', $ts);
    $jamf = $jam ? date('H:i', $ts) : '';
    return trim($hari.', '.$tglf.($jamf ? ' '.$jamf : ''));
  }
}

/* Mapping status (sesuai request) */
if (!function_exists('status_label_bt')) {
  function status_label_bt($s){
    switch ($s) {
      case 'approved':    return 'Belum Datang';
      case 'checked_in':  return 'Datang';
      case 'checked_out': return 'Datang';
      case 'expired':     return 'Tidak Datang';
      case 'rejected':    return 'Ditolak';
      default:            return 'Draft';
    }
  }
}

/* Bangun baris filter */
$f = $filters ?? [];
$bagian = [];
if (!empty($f['tanggal_mulai']) || !empty($f['tanggal_selesai'])) {
  $mulai   = !empty($f['tanggal_mulai'])   ? date('d-m-Y', strtotime($f['tanggal_mulai']))   : '-';
  $selesai = !empty($f['tanggal_selesai']) ? date('d-m-Y', strtotime($f['tanggal_selesai'])) : '-';
  $bagian[] = "Periode: {$mulai} s/d {$selesai}";
}
if (!empty($f['unit_tujuan_nama'])) { $bagian[] = 'Unit: '.$f['unit_tujuan_nama']; }
if (!empty($f['form_asal']))        { $bagian[] = 'Kata kunci: "'.$f['form_asal'].'"'; }
$bagian[] = 'Status: '.(isset($f['status']) && $f['status']!=='' ? status_label_bt($f['status']) : 'Semua');
$filter_line = implode('  |  ', $bagian);

// Nama aplikasi untuk footer
$webName = (isset($this->om) && method_exists($this->om,'web_me') && $this->om->web_me())
  ? ($this->om->web_me()->nama_website ?? 'Aplikasi')
  : 'Aplikasi';

  /* Logo & QR (pakai path lokal agar aman di TCPDF) */
$logo_file   = FCPATH.'assets/images/logo.png';
$logo_exists = is_file($logo_file);
?>

<!-- KOP (logo & teks benar-benar center) -->
<table class="no-border" style="margin-bottom:0px;">
  <tr>
    <td class="center" style="padding-bottom:0px;">
       <?php if ($logo_exists): ?>
        <img style="width: 30px" src="<?= $logo_file ?>" alt="Logo">
      <?php endif; ?>
      <div class="kop-org"><strong>LAPAS KELAS I MAKASSAR</strong></div>
      <div class="kop-sub">Jl. Sultan Alauddin, Gn. Sari, Kec. Rappocini, Kota Makassar, Sulawesi Selatan 90221</div>
      <div class="title"><?= htmlspecialchars($title ?? 'Laporan Booking Tamu'); ?></div>
    </td>
  </tr>
</table>

<!-- Garis pemisah dobel -->
<table class="no-border" style="margin:0 0 0px 0;">
  <!-- <tr><td style="border-top:1px solid #000;height:0;"></td></tr> -->
  <tr><td style="border-top:0.8px solid #000;height:0;"></td></tr>
</table>

<?php if (!empty($filter_line)): ?>
  <div class="meta"><strong><?= htmlspecialchars($filter_line); ?></strong></div>
<?php endif; ?>

<table>
  <thead >
    <tr style="background-color: #cce5ff">
      <th width="5%"  class="center">No</th>
      <th width="8%" class="center">Kode Booking</th>
      <th width="15%" class="center">Tanggal / Jam</th>
      <th width="20%" class="center">Nama Tamu / Jabatan</th>
      <th width="20%" class="center">Asal</th>
      <th width="22%" class="center">Unit Tujuan / Pejabat</th>
      <th width="10%"  class="center">Status</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($rows)): $no=1; foreach ($rows as $r):
      $asal = (isset($r->asal) && $r->asal !== '') ? $r->asal
            : (!empty($r->target_instansi_nama) ? $r->target_instansi_nama
            : (!empty($r->instansi) ? $r->instansi : '-'));
    ?>
    <tr>
      <td width="5%" class="center"><?= $no++; ?></td>
      <td width="8%"><?= htmlspecialchars($r->kode_booking ?? '-'); ?></td>
      <td width="15%"><?= htmlspecialchars(hari_tanggal_id($r->tanggal ?? null, $r->jam ?? null), ENT_QUOTES, 'UTF-8'); ?></td>
      <td width="20%">
        <b><?= htmlspecialchars($r->nama_tamu ?? '-'); ?></b><br>
        <span class="small"><?= htmlspecialchars($r->jabatan ?? '-'); ?></span>
      </td>
      <td width="20%"><?= htmlspecialchars($asal); ?></td>
      <td width="22%">
        <?= htmlspecialchars($r->unit_tujuan_nama ?? '-'); ?><br>
        <span class="small"><?= htmlspecialchars($r->nama_petugas_instansi ?? '-'); ?></span>
      </td>
      <td width="10%" class="center"><?= htmlspecialchars(status_label_bt($r->status ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
    </tr>
    <?php endforeach; else: ?>
    <tr><td colspan="7" class="center">Tidak ada data</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<!-- Footer -->
<div style="margin-top:8px; text-align:right; font-size:9px; color:#777;">
  Dibuat oleh <?= htmlspecialchars($webName); ?> — <?= date('d-m-Y'); ?>
</div>

</body>
</html>
