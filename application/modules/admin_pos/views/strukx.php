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
$kodeUnik = (int)($order->kode_unik ?? 0);
$catatan  = trim((string)($order->catatan ?? ''));

// Hitung total jika belum ada
if (!isset($total)) {
  $total = 0;
  foreach ($items as $it) { $total += (int)($it->subtotal ?? 0); }
}
$total      = (int)$total;
$grandTotal = (isset($order->grand_total) && $order->grand_total !== null)
              ? (int)$order->grand_total
              : ($total + $kodeUnik);

// Support logo opsional, isi $store->logo_url jika ada
$logoUrl = isset($store->logo_url) && $store->logo_url ? (string)$store->logo_url : null;
$paidLabel = isset($paid_label) && trim($paid_label) !== ''
  ? $paid_label
  : (trim((string)($order->paid_method ?? '')) ?: '—');

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
      <div class="value"><?= esc(ucfirst($mode)) ?></div>
    </div>
    <div class="row">
      <div class="label">Metode</div>
      <div class="value"><?= esc($paidLabel) ?></div>
    </div>
    <div class="row">
      <div class="label">Meja</div>
      <div class="value"><?= esc($meja) ?></div>
    </div>
    <?php if ($nama !== ''): ?>
    <div class="row">
      <div class="label">Pelanggan</div>
      <div class="value"><?= esc($nama) ?></div>
    </div>
    <?php endif; ?>
    <?php if ($catatan !== ''): ?>
    <div class="mt4 small"><span class="label" style="font-weight:700">Catatan:</span> <?= nl2br(esc($catatan)) ?></div>
    <?php endif; ?>
  </div>

  <div class="hr-strong"></div>

  <!-- ===== Items ===== -->
  <table>
    <thead>
      <tr>
        <th class="left produk">Item</th>
        <th class="qty">Qty</th>
        <!-- <th class="harga">Harga</th> -->
        <!-- <th class="sub">Subtotal</th> -->
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td class="produk"><?= esc($it->nama ?? '-') ?></td>
          <td class="qty"><?= (int)$it->qty ?></td>
          <!-- <td class="harga"><?= rupiah((int)$it->harga) ?></td> -->
          <!-- <td class="sub"><?= rupiah((int)$it->subtotal) ?></td> -->
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

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
</div>

<script>
(function(){
  // ======== Query Params ========
  const qs = new URLSearchParams(location.search);
  const IS_EMBED   = qs.get('embed') === '1';
  const AUTO_PRINT = qs.get('autoprint') === '1';
  const AUTO_CLOSE = qs.get('autoclose') === '1';
  const USE_RAWBT  = qs.get('rawbt') === '1';

  // Lebar kolom sesuai paper (58=>32, 80=>48)
  const COLS = <?= ($paperWidthMM === 80) ? 48 : 32 ?>;

  // ======== Serialize data PHP -> JS (aman via json_encode) ========
  const ORDER = {
    toko       : <?= json_encode((string)($store->nama ?? '')) ?>,
    alamat     : <?= json_encode((string)($store->alamat ?? '')) ?>,
    telp       : <?= json_encode((string)($store->telp ?? '')) ?>,
    nomor      : <?= json_encode($nomor) ?>,
    waktu      : <?= json_encode($waktu) ?>,
    mode       : <?= json_encode($mode) ?>,
    meja       : <?= json_encode($meja) ?>,
    nama       : <?= json_encode($nama) ?>,
    catatan    : <?= json_encode($catatan) ?>,
    printed_at : <?= json_encode($printed_at) ?>,
    items      : <?= json_encode(array_map(function($it){
                      return [
                        'nama'     => (string)($it->nama ?? '-'),
                        'qty'      => (int)$it->qty
                      ];
                    }, $items)) ?>
  };

  // ======== ESC/POS Helpers ========
  const ESC = '\x1B', GS = '\x1D';
  const INIT= ESC+'@';
  const LEFT= ESC+'a'+'\x00', CTR= ESC+'a'+'\x01';
  const BOLD_ON= ESC+'E'+'\x01', BOLD_OFF= ESC+'E'+'\x00';
  const SIZE1X = GS+'!'+'\x00';
  const CUT = GS+'V'+'\x00'; // di sebagian 58mm yang tanpa cutter: diabaikan

  const HR1 = '-'.repeat(COLS) + '\n'; // setara .hr
  const HR2 = '='.repeat(COLS) + '\n'; // setara .hr-strong

  function feed(n){ return '\n'.repeat(Math.max(0, n|0)); }
  function clamp(s,n){ s = String(s||''); return s.length>n ? s.slice(0,n) : s; }
  function wrapText(s, width=COLS){
    s = String(s||''); const out=[]; while(s.length>width){ out.push(s.slice(0,width)); s = s.slice(width); }
    if(s) out.push(s); return out;
  }
  function lineLR(left, right){
    left = String(left||''); right = String(right||'');
    const space = Math.max(1, COLS - left.length - right.length);
    return left + ' '.repeat(space) + right;
  }

  function sendToRawBT(escpos){
    const payload = encodeURIComponent(escpos);
    const intent  = `intent:${payload}#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;end;`;
    try { location.href = intent; }
    catch(e){ location.href = 'market://details?id=ru.a402d.rawbtprinter'; }
  }

  function buildEscposFromOrder(o){
    // === KITCHEN SLIP persis HTML (font normal; nama & qty; garis) ===
    let out = '';
    out += INIT + SIZE1X;

    // Header (brand)
    out += CTR + '\n' + clamp(o.toko, COLS) + '\n';
    if (o.alamat) wrapText(o.alamat).forEach(l=> out += clamp(l, COLS) + '\n');
    if (o.telp)   out += 'HP/WA: ' + clamp(o.telp, COLS-7) + '\n';
    out += LEFT + '\n';

    // Meta
    out += 'No: ' + o.nomor + '\n';
    if (o.waktu) out += 'Waktu: ' + o.waktu + '\n';
    if (o.mode)  out += 'Mode: ' + (String(o.mode).charAt(0).toUpperCase() + String(o.mode).slice(1)) + '\n';
    out += 'Metode: <?= esc($paidLabel) ?>' + '\n';
    if (o.meja)  out += 'Meja: ' + o.meja + '\n';
    if (o.nama)  out += 'Pelanggan: ' + o.nama + '\n';
    if (o.catatan) wrapText('Catatan: '+o.catatan).forEach(l=> out += l + '\n');

    // Garis tebal (hr-strong)
    out += HR2;

    // Header tabel (Item | Qty) + garis bawah
    out += lineLR('Item', 'Qty') + '\n';
    out += HR1;

    // Items (tiap baris nama kiri, qty kanan) + garis antar-item
    (o.items||[]).forEach((it, idx, arr)=>{
      const nameLines = wrapText(it.nama||'');
      const qty = String(Number(it.qty||0));
      // baris pertama nama & qty
      out += lineLR(clamp(nameLines.shift()||'', COLS-4), qty) + '\n';
      // sisa nama (jika panjang)
      nameLines.forEach(l => out += clamp(l, COLS) + '\n');
      // garis antar item (kecuali terakhir)
      if (idx < arr.length - 1) out += HR1;
    });

    // Garis penutup tipis
    out += HR1;

    // Footer (seperti HTML)
    if (<?= json_encode((string)($store->footer ?? '')) ?>){
      out += CTR + clamp(<?= json_encode((string)($store->footer ?? '')) ?>, COLS) + '\n';
    }
    if (o.printed_at){
      out += CTR + 'Dicetak: ' + clamp(o.printed_at, COLS - 9) + '\n';
    }
    out += LEFT;

    // Sedikit feed di akhir (tidak mempengaruhi margin atas)
    out += feed(2) + CUT;
    return out;
  }

  // ======== Eksekusi sesuai mode ========
  if (USE_RAWBT){
    const escpos = buildEscposFromOrder(ORDER);
    sendToRawBT(escpos);
    if (AUTO_CLOSE){ setTimeout(()=>window.close(), 800); }
    return; // jangan autoprint HTML
  }

  if (AUTO_PRINT){
    window.print();
    if (AUTO_CLOSE){ setTimeout(()=>window.close(), 200); }
  }
})();
</script>

</body>
</html>
