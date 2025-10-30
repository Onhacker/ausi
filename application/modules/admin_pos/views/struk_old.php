<?php
/** @var string $paper */
/** @var object $order */
/** @var array  $items */
/** @var int    $total */
/** @var object $store */
/** @var string $printed_at */

// Flag embed (untuk hide tombol cetak saat dicetak via iframe)
$embed = isset($_GET['embed']) && $_GET['embed'] == '1';

function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rupiah($n){ return 'Rp '.number_format((int)$n,0,',','.'); }

$paperWidthMM = ($paper === '80') ? 80 : 58; // default 58
$w = $paperWidthMM.'mm';

// Siapkan data order
$nomor    = $order->nomor ?? ('#'.$order->id);
$waktu    = $order->created_at ? date('d/m/Y H:i', strtotime($order->created_at)) : '-';
$mode     = $order->mode ?? '-';
$meja     = $order->meja_nama ?: ($order->meja_kode ?: '-');
$nama     = trim((string)($order->nama ?? ''));
// $kodeUnik = (int)($order->kode_unik ?? 0);
$catatan  = trim((string)($order->catatan ?? ''));

// Normalisasi & label mode + flag tampil meja
$modeRaw   = strtolower($mode);
if ($modeRaw==='dinein' || $modeRaw==='dine-in') {
  $modeLabel = 'Makan di tempat / Dine in';
  $showMeja  = true;
} elseif ($modeRaw==='delivery') {
  $modeLabel = 'Antar / Kirim / Delivery';
  $showMeja  = false;
} else {
  $modeLabel = 'Bungkus / Take Away';
  $showMeja  = false;
}


// Hitung total jika belum ada
if (!isset($total)) {
  $total = 0;
  foreach ($items as $it) { $total += (int)($it->subtotal ?? 0); }
}
$total      = (int)$total;
$kodeUnik    = (int)($order->kode_unik ?? 0);
$isDelivery  = ($modeRaw === 'delivery');
$deliveryFee = (int)($order->delivery_fee ?? 0);

// (double guard) hitung total lagi jika belum ada
if (!isset($total)) {
  $total = 0;
  foreach ($items as $it) { $total += (int)($it->subtotal ?? 0); }
}
$total = (int)$total;

$grandFallback = $total + ($isDelivery ? $deliveryFee : 0) + $kodeUnik;
$grandTotal    = isset($order->grand_total) && $order->grand_total !== null
                 ? (int)$order->grand_total
                 : $grandFallback;

// Support logo opsional, isi $store->logo_url jika ada
$logoUrl = isset($store->logo_url) && $store->logo_url ? (string)$store->logo_url : null;
$paidMethod  = strtolower($order->paid_method ?? '');
$statusRaw   = strtolower($order->status ?? '');
$statusLabel = ($statusRaw==='paid') ? 'Lunas'
             : (($statusRaw==='verifikasi') ? 'Verifikasi' : 'Menunggu pembayaran');

// Kode unik hanya relevan untuk non-cash
$showKodeUnik = ($paidMethod !== 'cash' && $kodeUnik > 0);

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Struk #<?= esc($nomor) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  @page { size: <?= $w ?> auto; margin: 2mm; }
  html, body { width: <?= $w ?>; }
  body {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-size: 11px; line-height: 1.35; color:#000;
    -webkit-print-color-adjust: exact; print-color-adjust: exact;
  }

  /* ===== Layout util ===== */
  .wrap { max-width: 100%; }
  .center { text-align: center; }
  .right  { text-align: right; }
  .left   { text-align: left; }
  .mt2{margin-top:2px} .mb2{margin-bottom:2px}
  .mt4{margin-top:4px} .mb4{margin-bottom:4px}
  .mt6{margin-top:6px} .mb6{margin-bottom:6px}
  .mt8{margin-top:8px} .mb8{margin-bottom:8px}
  .small { font-size: 10px; }
  .muted { color:#333; }

  /* ===== Header toko ===== */
  .brand {
    display:flex; flex-direction:column; align-items:center; justify-content:center;
  }
  .brand img.logo {
    display:block; width: 28mm; max-width: 80%; height:auto; margin-bottom:3px;
  }
  .brand .name   { font-weight: 800; font-size: 12px; letter-spacing: .3px; }
  .brand .addr   { line-height: 1.25; }

  /* ===== Garis pemisah ===== */
  .hr { border-top: 1px dashed #000; margin: 6px 0; }
  .hr-strong { border-top: 2px dashed #000; margin: 6px 0; }

  /* ===== Meta order ===== */
  .row { display:flex; justify-content:space-between; align-items:flex-start; }
  .label { color:#111; }
  .value { color:#000; font-weight:600; }

  /* ===== Tabel item ===== */
  table { width:100%; border-collapse: collapse; }
  th, td { padding: 2px 0; vertical-align: top; }
  thead th { font-weight: 700; border-bottom: 1px dashed #000; padding-bottom: 3px; }
  .produk { width: 50%; word-break: break-word; }
  .qty    { width: 10%; text-align:center; white-space: nowrap; }
  .harga  { width: 20%; text-align:right;  white-space: nowrap; }
  .sub    { width: 20%; text-align:right;  white-space: nowrap; }

  /* Row item dengan garis halus antar baris (tanpa makan tinta) */
  tbody tr:not(:last-child) td { border-bottom: 1px dotted #000; }

  /* ===== Blok totals ===== */
  tfoot td, tfoot th { padding-top: 4px; }
  .totline td { border-top: 1px dashed #000; padding-top:5px; }
  .totbox {
    border: 1px dashed #000; border-radius: 3px; padding: 6px; margin-top: 4px;
  }
  .totrow { display:flex; justify-content:space-between; align-items:center; }
  .totrow + .totrow { margin-top: 4px; }
  .totrow .tlabel { font-weight: 700; }
  .totrow .tval   { font-weight: 800; }

  /* Tombol cetak hide saat print / embed */
  <?php if ($embed): ?>
  .noprint { display:none !important; }
  <?php endif; ?>
  @media print { .noprint { display:none !important; } }
</style>
</head>
<body>
<div class="wrap">

  <!-- ===== Brand / Header toko ===== -->
  <div class="brand center">
    <?php if ($logoUrl): ?>
      <img class="logo" src="<?= esc($logoUrl) ?>" alt="Logo">
    <?php endif; ?>
    <div class="name"><?= esc($store->nama) ?></div>
    <div class="addr small">
      <?= esc($store->alamat) ?>
      <?php if (!empty($store->telp)): ?>
        <br>HP/WA: <?= esc($store->telp) ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="hr"></div>

  <!-- ===== Meta Order ===== -->
  <div class="mb4">
    <div class="row">
      <div class="label">No</div>
      <div class="value"><?= esc($nomor) ?></div>
    </div>
    <div class="row">
      <div class="label">Waktu</div>
      <div class="value"><?= esc($waktu) ?></div>
    </div>
    <div class="row">
      <div class="label">Mode</div>
      <div class="value"><?= esc($modeLabel) ?></div>
    </div>

    <?php if ($showMeja): ?>
    <div class="row">
      <div class="label">Meja</div>
      <div class="value"><?= esc($meja) ?></div>
    </div>
    <?php endif; ?>

    <?php if ($nama !== ''): ?>
    <div class="row">
      <div class="label">Pelanggan</div>
      <div class="value"><?= esc($nama) ?></div>
    </div>
    <?php endif; ?>
    <!--
    <?php if ($catatan !== ''): ?>
    <div class="mt4 small"><span class="label" style="font-weight:700">Catatan:</span> <?= nl2br(esc($catatan)) ?></div>
    <?php endif; ?>
    -->
  </div>

  <div class="hr-strong"></div>

  <!-- ===== Items ===== -->
  <table>
    <thead>
      <tr>
        <th class="left produk">Item</th>
        <th class="qty">Qty</th>
        <th class="harga">Harga</th>
        <th class="sub">Subtotal</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td class="produk"><?= esc($it->nama ?? '-') ?></td>
          <td class="qty"><?= (int)$it->qty ?></td>
          <td class="harga"><?= number_format((int)$it->harga, 0, ',', '.') ?></td>
          <td class="sub"><?= number_format((int)$it->subtotal, 0, ',', '.') ?></td>

        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- ===== Totals (rapi & tebal) ===== -->
  <div class="totbox">
  <div class="totrow">
    <div class="tlabel">Status Pembayaran</div>
    <div class="tval"><?= esc($statusLabel) ?></div>
  </div>

  <?php if ($showKodeUnik): ?>
  <div class="totrow">
    <div class="tlabel">Kode Unik</div>
    <div class="tval"><?= rupiah($kodeUnik) ?></div>
  </div>
  <?php endif; ?>

  <div class="totrow">
    <div class="tlabel">Total</div>
    <div class="tval"><?= rupiah($total) ?></div>
  </div>

  <?php if ($isDelivery && $deliveryFee > 0): ?>
  <div class="totrow">
    <div class="tlabel">Ongkir</div>
    <div class="tval"><?= rupiah($deliveryFee) ?></div>
  </div>
  <?php endif; ?>

  <div class="totrow">
    <div class="tlabel">Total Pembayaran</div>
    <div class="tval"><?= rupiah($grandTotal) ?></div>
  </div>
</div>


  <div class="hr"></div>

  <!-- ===== Footer ===== -->
  <div class="center">
    <div class="small"><?= esc($store->footer) ?></div>
    <div class="small muted">Dicetak: <?= esc($printed_at) ?></div>

    <!-- Tombol cetak (hilang saat print/embed) -->
    <div class="noprint mt8">
      <button onclick="window.print()">🖨️ Cetak</button>
    </div>
  </div>

  <!-- garis putus-putus “tear line” kecil (opsional) -->
  <div class="hr" style="margin-top:10px;"></div>
  <div class="center small muted" style="margin-top:6px;">Dev By Onhacker</div>

</div>
</body>
</html>
