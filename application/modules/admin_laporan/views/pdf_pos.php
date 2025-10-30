<?php $this->load->view("header_pdf") ?>

<table border="1" cellspacing="0" cellpadding="4">
<thead>
<tr style="background-color:#efefef;font-weight:bold">
  <th align="center" width="7%">No</th>
  <th align="center" width="16%">Nomor</th>
  <th align="center" width="12%">Mode</th>
  <th align="center" width="18%">Meja/Nama</th>
  <th align="center" width="15%">Metode</th>
  <th align="center" width="16%">Waktu Bayar</th>
  <th align="center" width="16%">Grand Total</th>
</tr>
</thead>
<tbody>
<?php if (!empty($rows)): $i=1; foreach($rows as $r): 
  $meja = $r->meja_nama ?: $r->meja_kode;
  $nama = trim((string)$r->nama);
  $mn   = ($r->mode == "dinein") ? trim($meja.' '.($nama ? ' / '.$nama : '')) : trim($nama);
?>
<tr>
  <td width="7%" align="center"><?= $i++ ?></td>
  <td width="16%"><?= htmlspecialchars($r->nomor ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
  <td width="12%"><?= htmlspecialchars($r->mode ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
  <td width="18%"><?= htmlspecialchars($mn ?: '-', ENT_QUOTES, 'UTF-8') ?></td>
  <td width="15%"><?= htmlspecialchars($r->paid_method ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
  <td width="16%" align="center"><?= htmlspecialchars($r->paid_at ? date('d-m-Y H:i', strtotime($r->paid_at)) : '-', ENT_QUOTES, 'UTF-8') ?></td>
  <td width="16%" align="right"><?= $idr((int)($r->grand_total_net ?? $r->grand_total ?? 0)) ?></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="7" align="center">Tidak ada data</td></tr>
<?php endif; ?>
</tbody>
</table>

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
  <tr><td><b>Total Transaksi:</b> <?= (int)$sum['count'] ?> trx</td></tr>
  <tr><td><b>Total Omzet:</b> <?= $idr($sum['total']) ?></td></tr>
  <tr><td><b>Rincian Metode:</b> <?= $met ?: '-' ?></td></tr>
</table>
