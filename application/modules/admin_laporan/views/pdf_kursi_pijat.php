<?php $this->load->view("header_pdf"); ?>

<table border="1" cellspacing="0" cellpadding="4">
  <thead>
    <tr style="background-color:#efefef;font-weight:bold">
      <th align="center" width="6%">No</th>
      <th align="center" width="16%">Nama</th>
      <th align="center" width="12%">Durasi</th>
      <th align="center" width="10%">Sesi</th>
      <th align="center" width="14%">Mulai</th>
      <th align="center" width="14%">Selesai</th>
      <th align="center" width="10%">Status</th>
      <th align="center" width="18%">Total</th>
    </tr>
  </thead>
  <tbody>
  <?php if (!empty($rows)): $i=1; foreach($rows as $r): 
    $durText = (int)$r->durasi_menit.' menit';
    $mulai   = $r->mulai   ? date('d-m-Y H:i', strtotime($r->mulai))   : '-';
    $selesai = $r->selesai ? date('d-m-Y H:i', strtotime($r->selesai)) : '-';
    $sts     = strtoupper((string)$r->status);
  ?>
    <tr>
      <td align="center" width="6%"><?= $i++; ?></td>
      <td width="16%"><?= htmlspecialchars($r->nama ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
      <td align="center" width="12%"><?= htmlspecialchars($durText, ENT_QUOTES, 'UTF-8') ?></td>
      <td align="center" width="10%"><?= (int)($r->sesi ?? 0) ?></td>
      <td align="center" width="14%"><?= htmlspecialchars($mulai, ENT_QUOTES, 'UTF-8') ?></td>
      <td align="center" width="14%"><?= htmlspecialchars($selesai, ENT_QUOTES, 'UTF-8') ?></td>
      <td align="center" width="10%"><?= htmlspecialchars($sts, ENT_QUOTES, 'UTF-8') ?></td>
      <td align="right"  width="18%"><?= $idr($r->total_harga ?? 0) ?></td>
    </tr>
  <?php endforeach; else: ?>
    <tr><td colspan="8" align="center">Tidak ada data</td></tr>
  <?php endif; ?>
  </tbody>
</table>

<br>
<br>
<table cellspacing="0" cellpadding="3" border="0">
  <tr><td><b>Total Transaksi:</b> <?= (int)($sum['count'] ?? 0) ?> trx</td></tr>
  <tr><td><b>Total Omzet Kursi Pijat:</b> <?= $idr($sum['total'] ?? 0) ?></td></tr>
</table>

<?php
// Label status aktif (default: SELESAI ketika status=all)
function _kp_status_label($s){
  $s = strtolower((string)$s);
  if ($s==='done' || $s==='paid') return 'SELESAI';
  if ($s==='unpaid')             return 'BARU';
  if ($s==='cancel' || $s==='void') return 'BATAL';
  return 'SELESAI'; // default when "all"
}
$activeStatus = isset($f['status']) && strtolower($f['status'])!=='all'
  ? _kp_status_label($f['status'])
  : 'SELESAI';
?>

<!-- <div style="margin-top:8px;color:#666;font-size:10pt;">
  <em>Catatan:</em>
  <ul style="margin:6px 0 0 18px; padding:0;">
    <li>Perhitungan <b>hanya</b> mencakup transaksi Kursi Pijat berstatus <b><?= $activeStatus ?></b>.</li>
    <li>Secara default (saat status = ALL), yang dihitung adalah <b>SELESAI</b>.</li>
    <li>Rentang tanggal menggunakan <code>COALESCE(selesai, mulai)</code>.</li>
  </ul>
</div> -->
