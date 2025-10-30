<style>
  .printable-receipt{font-size:12px}
  .printable-receipt .center{text-align:center}
  .printable-receipt table{width:100%;border-collapse:collapse}
  .printable-receipt th,.printable-receipt td{padding:4px 0}
  .printable-receipt tfoot td{border-top:1px dashed #999;font-weight:700}
</style>
<div class="printable-receipt">
  <div class="center"><strong><?= html_escape($rec->nama_website ?? 'Toko') ?></strong></div>
  <div class="center">Struk #<?= html_escape($order->nomor ?? $order->id) ?></div>
  <div>Tanggal: <?= html_escape($order->created_at ?? date('Y-m-d H:i')) ?></div>
  <?php if (!empty($order->meja_nama) || !empty($order->meja_kode)): ?>
    <div>Meja: <?= html_escape($order->meja_nama ?: $order->meja_kode) ?></div>
  <?php endif; ?>
  <?php if (!empty($order->paid_method)): ?>
    <div>Metode: <?= strtoupper(html_escape($order->paid_method)) ?></div>
  <?php endif; ?>
  <hr>
  <table>
    <thead>
      <tr><th>Item</th><th class="right" style="text-align:right">Qty</th><th class="right" style="text-align:right">Subtotal</th></tr>
    </thead>
    <tbody>
      <?php $total=0; foreach($items as $it):
        $sub = (int)$it->harga * (int)$it->qty; $total += $sub; ?>
        <tr>
          <td><?= html_escape($it->nama ?? '-') ?><?= (!empty($it->tambahan) && (int)$it->tambahan===1)?' <small>(Tambahan)</small>':''; ?></td>
          <td class="right" style="text-align:right"><?= (int)$it->qty ?></td>
          <td class="right" style="text-align:right">Rp <?= number_format($sub,0,',','.') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr><td colspan="2" class="right" style="text-align:right">Total</td><td class="right" style="text-align:right">Rp <?= number_format((int)$total,0,',','.') ?></td></tr>
    </tfoot>
  </table>
  <div class="center">Terima kasih 🙏</div>
</div>
