<?php $this->load->view("header_pdf") ?>
<table border="1" cellspacing="0" cellpadding="4">
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
<?php if (!empty($rows)): $i=1; foreach($rows as $r): ?>
<tr>
  <td width="7%" align="center"><?= $i++ ?></td>
  <td width="16%" align="center"><?= htmlspecialchars(date('d-m-Y H:i', strtotime($r->tanggal)), ENT_QUOTES, 'UTF-8') ?></td>
  <td width="14%"><?= htmlspecialchars($r->kategori, ENT_QUOTES, 'UTF-8') ?></td>
  <td width="35%"><?= htmlspecialchars($r->nomor_ket, ENT_QUOTES, 'UTF-8') ?></td>
  <td width="12%"><?= htmlspecialchars($r->metode_bayar, ENT_QUOTES, 'UTF-8') ?></td>
  <td width="16%" align="right"><?= $idr($r->jumlah) ?></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="6" align="center">Tidak ada data</td></tr>
<?php endif; ?>
</tbody>
</table>
<br>
<?php
$kat = '';
if (!empty($sum['by_kategori'])){
  $kat .= '<ul style="margin:4px 0;padding-left:15px">';
  foreach($sum['by_kategori'] as $k=>$v){
    $kat .= '<li>'.htmlspecialchars($k, ENT_QUOTES, 'UTF-8').': '.$idr($v).'</li>';
  }
  $kat .= '</ul>';
}
?>
<br>
<table cellspacing="0" cellpadding="3" border="0">
  <tr><td><b>Jumlah Transaksi:</b> <?= (int)$sum['count'] ?> trx</td></tr>
  <tr><td><b>Total Pengeluaran:</b> <?= $idr($sum['total']) ?></td></tr>
  <tr><td><b>Rincian Kategori:</b> <?= $kat ?: '-' ?></td></tr>
</table>
