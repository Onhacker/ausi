<?php
/** @var string $paper */
/** @var object $order */
/** @var array  $items */
/** @var int    $total */
/** @var object $store */
/** @var string $printed_at */
/** @var int|null $cat  // 1=kitchen, 2=bar, null/else=kasir/admin */

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

// Logo opsional (abaikan jika tak ada)
$logoUrl = isset($store->logo_url) && $store->logo_url ? (string)$store->logo_url : null;

// Judul khusus Kitchen/Bar (sesuai permintaan)
$cat = isset($cat) ? (int)$cat : null;
$titleLine = ($cat === 1) ? 'Struk Order Kitchen' : (($cat === 2) ? 'Struk Order Bar' : '');
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
  .brand img.logo { display:block; width: 28mm; max-width: 80%; height:auto; margin-bottom:3px; }
  .brand .name   { font-weight: 800; font-size: 12px; letter-spacing: .3px; }
  .brand .title  { font-weight: 700; font-size: 11px; margin-top:2px; }

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

  /* Row item dengan garis halus antar baris */
  tbody tr:not(:last-child) td { border-bottom: 1px dotted #000; }

  /* ===== Blok totals (tidak ditampilkan di template ini) ===== */
  tfoot td, tfoot th { padding-top: 4px; }

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
    <?php if ($titleLine !== ''): ?>
      <div class="title"><?= esc($titleLine) ?></div>
    <?php endif; ?>
    <!-- Sesuai permintaan: alamat & no. WA toko DIHAPUS -->
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
        <!-- harga & subtotal sengaja disembunyikan -->
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td class="produk"><?= esc($it->nama ?? '-') ?></td>
          <td class="qty"><?= (int)$it->qty ?></td>
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

  // ======== Serialize data PHP -> JS ========
  const ORDER = {
    toko       : <?= json_encode((string)($store->nama ?? '')) ?>,
    title      : <?= json_encode($titleLine) ?>,
    nomor      : <?= json_encode($nomor) ?>,
    waktu      : <?= json_encode($waktu) ?>,
    mode       : <?= json_encode($mode) ?>,
    meja       : <?= json_encode($meja) ?>,
    nama       : <?= json_encode($nama) ?>,
    catatan    : <?= json_encode($catatan) ?>,
    printed_at : <?= json_encode($printed_at) ?>,
    items      : <?= json_encode(array_map(function($it){
                      return [
                        'nama' => (string)($it->nama ?? '-'),
                        'qty'  => (int)$it->qty
                      ];
                    }, $items)) ?>
  };

  // ======== ESC/POS Helpers ========
  const ESC = '\x1B', GS = '\x1D';
  const INIT= ESC+'@';
  const LEFT= ESC+'a'+'\x00', CTR= ESC+'a'+'\x01';
  const SIZE1X = GS+'!'+'\x00';

  const HR1 = '-'.repeat(COLS) + '\n'; // .hr
  const HR2 = '='.repeat(COLS) + '\n'; // .hr-strong

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

  // === CUT commands (umum + XS-80BT) ===
  const CUT_FULL_FEED_N = (n) => GS + 'V' + '\x42' + String.fromCharCode(n & 0xFF);
  const CUT_PART_FEED_N = (n) => GS + 'V' + '\x41' + String.fromCharCode(n & 0xFF);
  const CUT_FEED_N   = 7;
  const TRAIL_LINES  = 3;
  const CUT_COMMAND  = CUT_PART_FEED_N(CUT_FEED_N);

  // ======== Builder Kitchen/Bar (tanpa alamat/WA & tanpa metode bayar) ========
  function buildEscposFromOrder(o){
    const LS_DEFAULT = ESC + '2';
    let out = '';

    // Init, font normal
    out += INIT + LS_DEFAULT + SIZE1X;

    // Header: Nama toko + judul (tanpa alamat/no WA)
    out += CTR + clamp(o.toko, COLS) + '\n';
    if (o.title) out += CTR + clamp(o.title, COLS) + '\n';

    // Meta
    out += LEFT;
    out += 'No: ' + o.nomor + '\n';
    if (o.waktu) out += 'Waktu: ' + o.waktu + '\n';
    if (o.mode)  out += 'Mode: ' + (String(o.mode).charAt(0).toUpperCase()+String(o.mode).slice(1)) + '\n';
    if (o.meja)  out += 'Meja: ' + o.meja + '\n';
    if (o.nama)  out += 'Pelanggan: ' + o.nama + '\n';
    if (o.catatan) wrapText('Catatan: '+o.catatan).forEach(l=> out += l + '\n');

    // Table header
    out += HR2;
    out += lineLR('Item', 'Qty') + '\n';
    out += HR1;

    // Items
    (o.items||[]).forEach((it, idx, arr)=>{
      const nameLines = wrapText(it.nama||'');
      const qty = String(Number(it.qty||0));
      out += lineLR(clamp(nameLines.shift()||'', COLS-4), qty) + '\n';
      nameLines.forEach(l => out += clamp(l, COLS) + '\n');
      if (idx < arr.length - 1) out += HR1;
    });

    out += HR1;

    // Footer
    <?php $footerText = (string)($store->footer ?? ''); ?>
    <?php if ($footerText !== ''): ?>
    out += CTR + clamp(<?= json_encode($footerText) ?>, COLS) + '\n';
    <?php endif; ?>
    if (o.printed_at) out += CTR + 'Dicetak: ' + clamp(o.printed_at, COLS - 9) + '\n';
    out += LEFT;

    // Feed + cut
    out += feed(TRAIL_LINES) + CUT_COMMAND;
    return out;
  }

  // ======== Eksekusi ========
  if (USE_RAWBT){
    const escpos = buildEscposFromOrder(ORDER);
    sendToRawBT(escpos);
    if (AUTO_CLOSE){ setTimeout(()=>window.close(), 800); }
    return;
  }

  if (AUTO_PRINT){
    window.print();
    if (AUTO_CLOSE){ setTimeout(()=>window.close(), 200); }
  }
})();
</script>

</body>
</html>
