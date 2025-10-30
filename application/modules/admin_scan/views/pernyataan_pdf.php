<?php
/** @var object $booking */
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$CI =& get_instance();

// helper: tanggal Indonesia "dd NamaBulan yyyy"
if (!function_exists('tanggal_id')) {
  function tanggal_id($datestr){
    if (empty($datestr)) return '-';
    $ts = is_numeric($datestr) ? (int)$datestr : strtotime($datestr);
    if (!$ts) return '-';
    $bln = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
            7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    return date('d',$ts).' '.$bln[(int)date('n',$ts)].' '.date('Y',$ts);
  }
}

/* Sumber data */
$instansi_asal = $booking->target_instansi_nama ?: ($booking->instansi ?: '-');
$posisi        = $booking->jabatan ?: '-';
$ttl        = $booking->tempat_lahir.",".tgl_view($booking->tanggal_lahir) ?: '-';
// $tanggalMasuk  = !empty($booking->checkin_at) ? date('d-m-Y', strtotime($booking->checkin_at)) : '-';
$tanggalMasuk = !empty($booking->checkin_at)
  ? tanggal_id($booking->checkin_at)
  : '-';

/* Branding lembaga (silakan sesuaikan) */
$lapas_nama   = 'LAPAS KELAS I MAKASSAR';
$kota_default = 'Makassar';

$ts_ref      = !empty($booking->tanggal) ? strtotime($booking->tanggal) : time();
$kota_tgl    = $kota_default . ", ".$tanggalMasuk;

/* Util untuk garis isian */
function line_value($val, $minWidth='70%'){
  $v = trim((string)$val);
  return '<span class="line"><span>'.($v !== '' ? e($v) : '&nbsp;').'</span></span>';
}
?>
<?php
if (!function_exists('line_value')) {
  /**
   * Garis/area kosong untuk isian (mis. tanda tangan).
   * @param string $text   Teks kecil di bawah garis (opsional)
   * @param string $width  Lebar (contoh: '85%', '120mm', '300px')
   * @param string $height Tinggi area kosong (contoh: '26mm', '180px')
   * @param bool   $as_line true=garis bawah; false=kotak berbingkai
   */
  function line_value(string $text = '', string $width = '60%', string $height = '8mm', bool $as_line = true){
    // validasi simpel
    $w = preg_match('/^\d+(\.\d+)?(mm|cm|px|%)$/', $width)  ? $width  : '60%';
    $h = preg_match('/^\d+(\.\d+)?(mm|cm|px)$/',   $height) ? $height : '8mm';
    $style = $as_line
      ? "width:$w;height:$h;border-bottom:1px solid #111;"
      : "width:$w;height:$h;border:1px solid #111;border-radius:3px;";
    $html  = '<div style="'.$style.'"></div>';
    if ($text !== '') $html .= '<div style="font-size:8pt;color:#666;margin-top:1.2mm;">'.$text.'</div>';
    return $html;
  }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Lembar Persetujuan Kerahasiaan — <?= e($booking->kode_booking) ?></title>
<style>
  body{font-family: "Times New Roman", Times, serif; color:#111; margin:0; padding:22mm 18mm; font-size:12pt;}
  h1,h2,h3,p{margin:0}
  .lampiran{display:inline-block;background:#e8f0ff;padding:2px 8px;border-radius:4px;font-weight:700;letter-spacing:.3px}
  .tengah{text-align:center}
  .mt-2{margin-top:8px}.mt-3{margin-top:12px}.mt-4{margin-top:18px}.mt-5{margin-top:26px}
  .para{line-height:1.7;text-align:justify}
  .line{display:inline-block;min-width:70%;border-bottom:1px solid #000;line-height:1.4}
  .line>span{visibility:visible}
  .tbl{width:100%;border-collapse:collapse}
  .tbl td{padding:5px 0;vertical-align:top}
  .tbl td.k{width:30%}
  .footer{margin-top:28px;text-align:center;font-weight:700}
  .small{font-size:10pt;color:#555}
</style>
</head>
<body>

  <div class="tengah">
    <div class="lampiran"><strong>LAMPIRAN 8</strong></div>
    <h2 class="mt-3" style="font-weight:800;letter-spacing:.4px;"><strong>LEMBAR PERSETUJUAN KERAHASIAAN</strong></h2>
    <div style="font-style:italic;font-weight:700;"><strong>(CONFIDENTIAL INFORM CONSENT)</strong></div>
    <h3 class="mt-3" style="font-weight:800;"><strong>TAMU DINAS</strong></h3>
  </div>

  <!-- <div class=""><strong>Nama</strong> : <?= line_value($booking->nama_tamu) ?></div> -->

  <p class="para"><strong>Nama</strong> : <?= line_value($booking->nama_tamu) ?><br>Saya berjanji tidak akan membocorkan data dan sistem keamanan dan rahasia negara
    yang berhubungan dengan tempat yang secara sengaja saya kunjungi di
    <strong><?= e($lapas_nama) ?></strong>, dan apabila saya melakukan pelanggaran,
    saya bersedia dihukum sesuai dengan ketentuan peraturan perundang-undangan yang
    berlaku.
  </p>

  <div class="mt-5" style="text-align:right;"><?= e($kota_tgl) ?></div>

  <table class="tbl mt-3">
    <tr>
      <td class="k">Nama</td>
      <td>: <?= line_value($booking->nama_tamu) ?></td>
    </tr>
    <tr>
      <td class="k">Alamat</td>
      <td>: <?= line_value($booking->alamat) /* tidak ada di data booking → kosong */ ?></td>
    </tr>
    <tr>
      <td class="k">Institusi</td>
      <td>: <?= line_value($instansi_asal) ?></td>
    </tr>
    <tr>
      <td class="k">Posisi</td>
      <td>: <?= line_value($posisi) ?></td>
    </tr>
    <tr>
      <td class="k">Tempat/tanggal lahir</td>
      <td>: <?= line_value($ttl) ?></td>
      
    </tr>
    <tr>
  <td class="k">Tanda tangan</td>
  <td>: <?= line_value('', '85%', '26mm', true) ?>
    &nbsp;<br><br>
    <hr style="width: 100px">
  </td>
</tr>

    <tr>
      <td class="k">Tanggal masuk</td>
      <td>: <?= line_value($tanggalMasuk, '40%') ?></td>
    </tr>
  </table>
<br>
  <div class="footer mt-5">
    Rutan/LPAS/Lapas/LPKA <?= e(strtoupper($lapas_nama)) ?>
    <!-- <div class="small mt-2">Kode Booking: <?= e($booking->kode_booking) ?></div> -->
  </div>

</body>
</html>
