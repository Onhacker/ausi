<?php
// Normalisasi
$order_code = $order->kode ?? ($order->nomor ?? $order->id ?? '-');
$customer   = $order->customer_name ?? $order->nama_pelanggan ?? 'Pelanggan';
$note       = $order->note ?? $order->catatan ?? '';
$waktu      = $order->created_at ?? $order->tanggal ?? date('Y-m-d H:i:s');
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Struk <?= htmlspecialchars($order_code) ?></title>
<style>
  /* ==== Ukuran thermal */
  @page { size: 58mm auto; margin: 0; }
  @media print { body { margin:0; } }

  body{
    width:58mm; margin:0 auto; padding:8px 6px;
    font: 12px/1.35 "Courier New", monospace;
    color:#000;
  }
  .center { text-align:center; }
  .right  { text-align:right; }
  .bold   { font-weight:bold; }
  .line   { border-top:1px dashed #000; margin:6px 0; }
  .big    { font-size:14px; font-weight:bold; }
  .row { display:flex; justify-content:space-between; }
  .muted{ color:#444; }
  .wrap  { word-break:break-word; white-space:normal; }
  .footer{ margin-top:8px; text-align:center; }

  /* Tabel item */
  .item { margin-bottom:4px; }
  .item .name { font-weight:bold; }
  .item small{ color:#333; }

  /* Hide tombol saat print */
  @media print { .noprint { display:none !important; } }
</style>
</head>
<body>
  <div class="center bold big">STRUK PESANAN</div>
  <div class="center">Kode: <?= htmlspecialchars($order_code) ?></div>
  <div class="line"></div>

  <div class="row"><div>Waktu</div><div><?= htmlspecialchars($waktu) ?></div></div>
  <div class="row"><div>Pelanggan</div><div><?= htmlspecialchars($customer) ?></div></div>
  <?php if (!empty($meja_info)): ?>
    <div class="row"><div>Tabel</div><div><?= htmlspecialchars($meja_info) ?></div></div>
  <?php endif; ?>
  <?php if (trim($note)!==''): ?>
    <div class="row"><div>Catatan</div><div class="wrap"><?= nl2br(htmlspecialchars($note)) ?></div></div>
  <?php endif; ?>

  <div class="line"></div>

  <?php
    $grand = 0;
    foreach ($items as $it):
      $nama  = $it->nama ?? $it->produk_nama ?? '-';
      $harga = (int)($it->harga ?? 0);
      $qty   = (int)($it->qty ?? 0);
      $sub   = $harga * $qty; $grand += $sub;
  ?>
    <div class="item">
      <div class="name wrap"><?= htmlspecialchars($nama) ?></div>
      <div class="row">
        <div class="muted"><?= $qty ?> x <?= number_format($harga,0,',','.') ?></div>
        <div class="bold"><?= number_format($sub,0,',','.') ?></div>
      </div>
    </div>
  <?php endforeach; ?>

  <div class="line"></div>
  <div class="row bold"><div>Total</div><div><?= number_format((int)($total ?? $grand),0,',','.') ?></div></div>

  <div class="footer">
    <div class="muted">Terima kasih 🙏</div>
    <div class="muted">harap tunggu pesanan Anda</div>
  </div>

  <div class="noprint" style="margin-top:10px;text-align:center">
    <button onclick="window.print()">Print</button>
  </div>

  <script>
    // Auto-print saat dibuka, tutup jika diizinkan
    window.addEventListener('load', function(){
      window.print();
      // setTimeout(()=> window.close(), 300); // aktifkan kalau mau otomatis menutup
    });
  </script>
</body>
</html>
