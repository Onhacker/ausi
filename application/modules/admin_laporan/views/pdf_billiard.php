<?php $this->load->view("header_pdf") ?>

<table border="1" cellspacing="0" cellpadding="4">
<thead>
<tr style="background-color:#efefef;font-weight:bold">
  <th align="center" width="7%">No</th>
  <th align="center" width="15%">Kode</th>
  <th align="center" width="22%">Meja / Nama</th>
  <th align="center" width="10%">Durasi</th>
  <th align="center" width="14%">Metode</th>
  <th align="center" width="16%">Waktu Bayar</th>
  <th align="center" width="16%">Grand Total</th>
</tr>
</thead>
<tbody>
<?php if (!empty($rows)): $i=1; foreach($rows as $r): 
  $mn  = trim(($r->nama_meja ?: ('Meja #'.$r->meja_id)).' / '.($r->nama ?: '-'));
  $dur = (int)$r->durasi_jam.' jam';
?>
<tr>
  <td width="7%" align="center"><?= $i++ ?></td>
  <td width="15%"><?= htmlspecialchars($r->kode_booking, ENT_QUOTES, 'UTF-8') ?></td>
  <td width="22%"><?= htmlspecialchars($mn, ENT_QUOTES, 'UTF-8') ?></td>
  <td width="10%" align="center"><?= htmlspecialchars($dur, ENT_QUOTES, 'UTF-8') ?></td>
  <td width="14%"><?= htmlspecialchars($r->metode_bayar ?: '-', ENT_QUOTES, 'UTF-8') ?></td>
  <td width="16%" align="center"><?= htmlspecialchars(date('d-m-Y H:i', strtotime($r->paid_at)), ENT_QUOTES, 'UTF-8') ?></td>
  <td width="16%" align="right"><?= $idr($r->grand_total) ?></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="7" align="center">Tidak ada data</td></tr>
<?php endif; ?>
</tbody>
</table>
<br>
<?php
$met = '';
if (!empty($sum['by_method'])){
  $met .= '<ul style="margin:4px 0;padding-left:15px">';
  foreach($sum['by_method'] as $k=>$v){
    $met .= '<li>'.htmlspecialchars(strtoupper($k), ENT_QUOTES, 'UTF-8').': '.$idr($v).'</li>';
  }
  $met .= '</ul>';
}
?>
<br>
<table cellspacing="0" cellpadding="3" border="0">
  <tr><td><b>Total Booking:</b> <?= (int)$sum['count'] ?> trx</td></tr>
  <tr><td><b>Total Omzet:</b> <?= $idr($sum['total']) ?></td></tr>
  <tr><td><b>Rincian Metode:</b> <?= $met ?: '-' ?></td></tr>
</table>
