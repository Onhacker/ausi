<?php $this->load->view("header_pdf") ?>
<?php
/** @var string   $title */
/** @var string   $period */
/** @var array    $sumPos */
/** @var array    $sumBil */
/** @var array    $sumPen */
/** @var array    $sumKP */
/** @var array    $sumPS */
/** @var int      $laba */          // (POS + BIL + KP + PS - PENGELUARAN)
/** @var callable $idr */
?>
<table width="100%" cellspacing="0" cellpadding="0" border="0">
  <tr>
    <td>
      <table width="100%" cellspacing="0" cellpadding="6" border="1">
        <tr style="background-color:#f2f3f4; font-weight:bold">
          <td width="60%">Ringkasan (<?= htmlspecialchars($period ?? '-', ENT_QUOTES, 'UTF-8') ?>)</td>
          <td width="40%" align="right">Jumlah</td>
        </tr>
        <tr>
          <td>Cafe</td>
          <td align="right"><?= $idr($sumPos['total'] ?? 0) ?></td>
        </tr>
        <tr>
          <td>Billiard</td>
          <td align="right"><?= $idr($sumBil['total'] ?? 0) ?></td>
        </tr>
        <tr>
          <td>Kursi Pijat</td>
          <td align="right"><?= $idr($sumKP['total'] ?? 0) ?></td>
        </tr>
        <tr>
          <td>PlayStation (PS)</td>
          <td align="right"><?= $idr($sumPS['total'] ?? 0) ?></td>
        </tr>

        <?php
          // TOTAL PEMASUKAN = semua omzet unit (Cafe + Billiard + KP + PS)
          $totalIn = (int)($sumPos['total'] ?? 0)
                   + (int)($sumBil['total'] ?? 0)
                   + (int)($sumKP['total'] ?? 0)
                   + (int)($sumPS['total'] ?? 0);
        ?>
        <tr style="background-color:#f9fafb">
          <td><b>Total Pemasukan</b></td>
          <td align="right"><b><?= $idr($totalIn) ?></b></td>
        </tr>

        <tr>
          <td>Pengeluaran</td>
          <td align="right" style="color:#c62828">-<?= $idr($sumPen['total'] ?? 0) ?></td>
        </tr>
        <tr style="background-color:#f9fafb">
          <td>
            <b>LABA BERSIH (Total Pemasukan − Pengeluaran)</b>
          </td>
          <td align="right"><b><?= $idr($laba ?? 0) ?></b></td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<div style="height:10px"></div>

<?php
$hasPos = !empty($sumPos['by_method']);
$hasBil = !empty($sumBil['by_method']);
?>

<?php if ($hasPos || $hasBil): ?>
  <h4 style="margin:0 0 6px 0">Rincian Metode</h4>
  <table width="100%" cellspacing="0" cellpadding="0" border="0">
    <tr>
      <td width="49%" valign="top">
        <?php if ($hasPos): ?>
          <table width="100%" cellspacing="0" cellpadding="5" border="1">
            <tr style="background-color:#f2f3f4; font-weight:bold"><td colspan="2">Cafe</td></tr>
            <?php foreach ($sumPos['by_method'] as $k => $v): ?>
              <tr>
                <td width="60%">- <?= htmlspecialchars(strtoupper($k), ENT_QUOTES, 'UTF-8') ?></td>
                <td width="40%" align="right"><?= $idr($v) ?></td>
              </tr>
            <?php endforeach; ?>
          </table>
        <?php endif; ?>
      </td>
      <td width="2%"></td>
      <td width="49%" valign="top">
        <?php if ($hasBil): ?>
          <table width="100%" cellspacing="0" cellpadding="5" border="1">
            <tr style="background-color:#f2f3f4; font-weight:bold"><td colspan="2">Billiard</td></tr>
            <?php foreach ($sumBil['by_method'] as $k => $v): ?>
              <tr>
                <td width="60%">- <?= htmlspecialchars(strtoupper($k), ENT_QUOTES, 'UTF-8') ?></td>
                <td width="40%" align="right"><?= $idr($v) ?></td>
              </tr>
            <?php endforeach; ?>
          </table>
        <?php endif; ?>
      </td>
    </tr>
  </table>
<?php endif; ?>

<br>

<div style="margin-top:6px;color:#666;font-size:10pt;">
  <em>Catatan:</em>
  <ul style="margin:6px 0 0 18px; padding:0;">
    <li><b>Total Pemasukan</b> = Cafe + Billiard + Kursi Pijat + PS.</li>
    <li><b>LABA BERSIH</b> = Total Pemasukan − Pengeluaran.</li>
    <li>Ongkir Delivery sudah termasuk dalam Omzet Cafe (POS). Laporan Kurir bersifat informasi (subset) dan tidak menambah laba bersih.</li>
  </ul>
</div>
