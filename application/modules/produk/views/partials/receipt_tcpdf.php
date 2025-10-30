<?php
// $order, $items, $total, $paper (mm), $meja_info tersedia dari controller
$no   = html_escape($order->nomor ?? $order->id);
$wkt  = html_escape($order->created_at ?? $order->tanggal ?? date('Y-m-d H:i'));
$meja = html_escape($order->meja_nama ?: ($order->meja_kode ?? $meja_info ?? ''));
$nama = html_escape($order->nama ?? '');
$met  = !empty($order->paid_method) ? strtoupper(html_escape($order->paid_method)) : '';
$bkt  = !empty($order->bukti_bayar) ? base_url($order->bukti_bayar) : '';
?>

<style>
  /* Lebar halaman mengikuti ukuran PDF. Gunakan font monospace. */
  body{
    font-family: dejavusansmono, monospace;
    font-size: 9pt;
    color:#000;
  }
  .center{ text-align:center; }
  .right { text-align:right; }
  .hr { border-top: 1px dashed #000; height:0; margin:4px 0 4px 0; }
  .muted{ color:#333; }
  table { width:100%; border-collapse:collapse; }
  th, td { padding: 2px 0; vertical-align: top; }
  thead th { border-bottom: 1px dashed #000; }
  tfoot td { border-top: 1px dashed #000; font-weight: bold; padding-top:3px; }
  .nm  { width: 60%; }
  .qty { width: 10%; text-align:right; }
  .sub { width: 30%; text-align:right; }
</style>

<div class="center"><strong>STRUK PEMBELIAN</strong></div>
<div class="center muted">#<?= $no ?></div>
<div class="hr"></div>

<div>Waktu : <?= $wkt ?></div>
<?php if ($meja !== ''): ?><div>Meja  : <?= $meja ?></div><?php endif; ?>
<?php if ($nama !== ''): ?><div>Nama  : <?= $nama ?></div><?php endif; ?>
<?php if ($met  !== ''): ?><div>Metode: <?= $met ?></div><?php endif; ?>
<?php if ($bkt  !== ''): ?><div>Bukti : <?= $bkt ?></div><?php endif; ?>

<div class="hr"></div>

<table>
  <thead>
    <tr>
      <th class="nm">Item</th>
      <th class="qty">Qty</th>
      <th class="sub">Subtotal</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($items as $it):
      $nm = html_escape($it->nama ?? '-');
      $qty = (int)($it->qty ?? 0);
      $harga = (int)($it->harga ?? 0);
      $sub = $qty * $harga;
    ?>
      <tr>
        <td class="nm">
          <?= $nm ?>
          <?php if (!empty($it->tambahan) && (int)$it->tambahan===1): ?>
            <span class="muted"> (Tambahan)</span>
          <?php endif; ?>
        </td>
        <td class="qty"><?= $qty ?></td>
        <td class="sub">Rp <?= number_format($sub,0,',','.') ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
  <tfoot>
    <tr>
      <td colspan="2" class="right">Total</td>
      <td class="sub">Rp <?= number_format((int)$total,0,',','.') ?></td>
    </tr>
  </tfoot>
</table>

<div class="center" style="margin-top:6px;">Terima kasih 🙏</div>
