<?php $this->load->view("header_pdf") ?>
<?php
/** @var string   $title */
/** @var string   $period */
/** @var array    $rows */
/** @var array    $sum */
/** @var array    $f */
/** @var callable $idr */
?>

<?php
function fmt_dt($dt){
    if (!$dt) return '-';
    $ts = strtotime($dt);
    if (!$ts) return $dt;
    return date('d/m/Y H:i', $ts);
}
?>

<!-- <h3 style="margin:0 0 6px 0;">
  <?= htmlspecialchars($title ?? 'Laporan PlayStation (PS)', ENT_QUOTES, 'UTF-8'); ?>
</h3>
<div style="margin-bottom:8px;">
  Periode: <b><?= htmlspecialchars($period ?? '-', ENT_QUOTES, 'UTF-8'); ?></b>
</div> -->

<table width="100%" cellspacing="0" cellpadding="6" border="1">
  <tr style="background-color:#f2f3f4; font-weight:bold;">
    <td width="5%"  align="center">No</td>
    <td width="18%">Mulai</td>
    <td width="18%">Selesai</td>
    <td width="20%">Nama</td>
    <td width="12%" align="right">Durasi (menit)</td>
    <td width="15%" align="right">Total</td>
    <td width="12%">Status</td>
  </tr>

  <?php
  $no = 1;
  $grandTotal = 0;
  $totalDurasi = 0;
  if (!empty($rows)):
    foreach($rows as $r):
      $grandTotal  += (int)($r->total_harga ?? 0);
      $totalDurasi += (int)($r->durasi_menit ?? 0);
  ?>
  <tr>
    <td align="center"><?= $no++; ?></td>
    <td><?= fmt_dt($r->mulai ?? null); ?></td>
    <td><?= fmt_dt($r->selesai ?? null); ?></td>
    <td><?= htmlspecialchars($r->nama ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
    <td align="right"><?= number_format((int)($r->durasi_menit ?? 0), 0, ',', '.'); ?></td>
    <td align="right"><?= $idr($r->total_harga ?? 0); ?></td>
    <td><?= htmlspecialchars($r->status ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
  </tr>
  <?php endforeach; else: ?>
  <tr>
    <td colspan="7" align="center">Tidak ada data untuk periode ini.</td>
  </tr>
  <?php endif; ?>

  <tr style="background-color:#f9fafb; font-weight:bold;">
    <td colspan="4" align="right">TOTAL</td>
    <td align="right"><?= number_format($totalDurasi, 0, ',', '.'); ?></td>
    <td align="right"><?= $idr($sum['total'] ?? $grandTotal); ?></td>
    <td>&nbsp;</td>
  </tr>
</table>

<div style="margin-top:8px; font-size:9pt; color:#555;">
  <em>Keterangan:</em>
  <ul style="margin:4px 0 0 18px; padding:0;">
    <li>Laporan ini secara default hanya menghitung transaksi dengan status <b>selesai</b>.</li>
    <li>Jika ingin mencakup status lain (baru / batal), atur filter status di halaman laporan sebelum mencetak.</li>
  </ul>
</div>
