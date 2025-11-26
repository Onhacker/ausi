<?php
/** @var string $title */
/** @var string $period */
/** @var array  $rows */
/** @var array  $sum */
/** @var callable $idr */
$logo_file   = FCPATH.'assets/images/logo_admin.png';
$web = $this->om->web_me();
?>
<table>
    <tr>
      <td align="center" width="25%">
       <?php if ($logo_file): ?>
        <img style="width: 30px" src="<?= $logo_file ?>" alt="Logo">
      <?php endif; ?>
    </td>
    <td align="center">
     <strong><?php echo strtoupper($web->nama_website) ?></strong>
     <div style="font-size: 9px">Alamat : <?php echo $web->alamat."<br>Telp. ".$web->no_telp ?></div>
   </td>
 </tr>
</table>
<hr>
<h3 style="margin:0; text-align: center;"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>  <?= htmlspecialchars($filter_text, ENT_QUOTES, 'UTF-8'); ?></h3>
<div style="font-size:10px; text-align: center;"><?= htmlspecialchars($periode_text, ENT_QUOTES, 'UTF-8') ?>
  <br>
</div>
<!-- Info periode & filter -->
<table cellspacing="0" cellpadding="3" border="0" width="100%">
    <tr>
        <td style="font-size:10px;">
            <b>Periode:</b>
            <?= htmlspecialchars($periode_text, ENT_QUOTES, 'UTF-8'); ?>
        </td>
    </tr>
    <tr>
        <td style="font-size:10px;">
            <b>Filter:</b>
            <?= htmlspecialchars($filter_text, ENT_QUOTES, 'UTF-8'); ?>
        </td>
    </tr>
</table>

<br>

<table border="1" cellspacing="0" cellpadding="4" width="100%">
    <thead>
        <tr style="background-color:#efefef;font-weight:bold">
            <th align="center" width="7%">No</th>
            <th align="center" width="16%">Tanggal</th>
            <th align="center" width="14%">Kategori</th>
            <th align="center" width="35%">Keterangan</th>
            <th align="center" width="12%">Metode</th>
            <th align="center" width="16%">Jumlah</th>
        </tr>
    </thead>
    <tbody>
    <?php if (!empty($rows)): $i = 1; ?>
        <?php foreach($rows as $r): ?>
            <tr>
                <td width="7%"  align="center"><?= $i++; ?></td>
                <td width="16%" align="center">
                    <?= htmlspecialchars($tgl_indo($r->tanggal, true), ENT_QUOTES, 'UTF-8'); ?>
                </td>
                <td width="14%">
                    <?= htmlspecialchars($r->kategori, ENT_QUOTES, 'UTF-8'); ?>
                </td>
               <td width="35%" align="left" style="padding:2px;">
                    <?php
                    // ambil keterangan mentah
                    $ket = (string)($r->keterangan ?? '');

                    // trim kiri-kanan dulu
                    $ket = trim($ket);

                    if ($ket === '') {
                        $ket = '-';
                    } else {
                        // ubah semua newline jadi spasi
                        $ket = str_replace(["\r\n","\r","\n"], ' ', $ket);
                        // jadikan semua whitespace berurutan (spasi, tab, dsb) jadi 1 spasi
                        $ket = preg_replace('/\s+/', ' ', $ket);
                    }

                    echo htmlspecialchars($ket, ENT_QUOTES, 'UTF-8');
                    ?>
                </td>


                <td width="12%" align="center">
                    <?= htmlspecialchars($r->metode_bayar, ENT_QUOTES, 'UTF-8'); ?>
                </td>
                <td width="16%" align="right">
                    <?= $idr($r->jumlah); ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="6" align="center">Tidak ada data</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

<br>

<?php
// Rincian total per kategori
$kat = '';
if (!empty($sum['by_kategori'])){
    $kat .= '<ul style="margin:4px 0;padding-left:15px">';
    foreach($sum['by_kategori'] as $k => $v){
        $kat .= '<li>'.htmlspecialchars($k, ENT_QUOTES, 'UTF-8').': '.$idr($v).'</li>';
    }
    $kat .= '</ul>';
}
?>

<table cellspacing="0" cellpadding="3" border="0" width="100%">
    <tr>
        <td>
            <b>Jumlah Transaksi:</b>
            <?= (int)$sum['count']; ?> trx
        </td>
    </tr>
    <tr>
        <td>
            <b>Total Pengeluaran:</b>
            <?= $idr($sum['total']); ?>
        </td>
    </tr>
    <tr>
        <td>
            <b>Rincian Kategori:</b>
            <?= $kat ?: '-'; ?>
        </td>
    </tr>
</table>
