<?php $this->load->view("header_pdf") ?>


<table border="1" cellspacing="0" cellpadding="4">
  <thead>
    <tr style="background-color:#efefef;font-weight:bold">
      <th align="center" width="6%">No</th>
      <th align="center" width="16%">Nama Kurir</th>
      <th align="center" width="12%">Telp Kurir</th>
      <th align="center" width="14%">Customer</th>
      <th align="center" width="26%">Alamat Kirim</th>
      <th align="center" width="12%">Paid At</th>
      <th align="center" width="6%">Metode</th>
      <th align="center" width="8%">Ongkir</th>
    </tr>
  </thead>
  <tbody>
 <?php if (!empty($rows)): $i=1; foreach($rows as $r):
  $cid   = (int)($r->courier_id   ?? 0);
  $cname = trim((string)($r->courier_name ?? ''));
  if ($cid <= 0 && $cname === '') continue;

  $custName   = trim((string)($r->nama ?? ''));
  $alamat     = trim((string)($r->alamat_kirim ?? ''));
  $paidAt     = !empty($r->paid_at) ? date('d-m-Y H:i', strtotime($r->paid_at)) : '-';

  $pmRaw = $r->paid_method ?? $r->paid_methode ?? '';
  $k = strtolower(trim((string)$pmRaw));
  $paidMethod = strtoupper(['transfer'=>'TF','qris'=>'QR','cash'=>'Cash'][$k] ?? ($k ?: '-'));

  $feeRaw = (int)($r->delivery_fee ?? 0);
  $feeNet = (int)($r->delivery_fee_net ?? (($feeRaw===1 && ($k==='transfer'||$k==='qris')) ? 0 : $feeRaw));
?>
  <tr>
    <td width="6%" align="center"><?= $i++ ?></td>
    <td width="16%"><?= htmlspecialchars($r->courier_name ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
    <td width="12%"><?= htmlspecialchars($r->courier_phone ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
    <td width="14%"><?= htmlspecialchars($custName ?: '-', ENT_QUOTES, 'UTF-8') ?></td>
    <td width="26%"><?= nl2br(htmlspecialchars($alamat ?: '-', ENT_QUOTES, 'UTF-8')) ?></td>
    <td width="12%" align="center"><?= htmlspecialchars($paidAt, ENT_QUOTES, 'UTF-8') ?></td>
    <td width="6%" align="center"><?= htmlspecialchars($paidMethod, ENT_QUOTES, 'UTF-8') ?></td>
    <td width="8%" align="right"><?= $idr($feeNet) ?></td>
  </tr>
<?php endforeach; else: ?>

    <tr><td colspan="8" align="center">Tidak ada data</td></tr>
  <?php endif; ?>
  </tbody>
</table>

<?php
$met = '';
if (!empty($sum['by_method'])){
  $met .= '<ul style="margin:4px 0;padding-left:15px">';
  foreach($sum['by_method'] as $k=>$v){
    $label = strtoupper($k);
    $met  .= '<li>'.htmlspecialchars($label, ENT_QUOTES, 'UTF-8').': '.$idr($v).'</li>';
  }
  $met .= '</ul>';
}
?>
<br>
<table cellspacing="0" cellpadding="3" border="0">
  <tr><td><b>Total Delivery:</b> <?= (int)($sum['count'] ?? 0) ?> trx</td></tr>
  <tr><td><b>Total Ongkir:</b> <?= $idr($sum['total_fee'] ?? 0) ?></td></tr>
  <tr><td><b>Rincian Metode:</b> <?= $met ?: '-' ?></td></tr>
</table>
