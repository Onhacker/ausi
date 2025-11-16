<?php
/** @var string $paper */
/** @var object $voucher */
/** @var object $store */
/** @var string $printed_at */

$embed = isset($_GET['embed']) && $_GET['embed'] == '1';

function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$paperWidthMM = ($paper === '80') ? 80 : 58;
$w = $paperWidthMM.'mm';

// ==== Siapkan data voucher ====
$kode   = $voucher->kode_voucher ?? '-';
$nama   = trim((string)($voucher->nama ?? ''));
$tipe   = ($voucher->tipe === 'persen') ? 'Diskon (%)' : 'Diskon (Rp)';

if ($voucher->tipe === 'persen') {
    $nilaiLabel = (int)$voucher->nilai.' %';
} else {
    $nilaiLabel = 'Rp '.number_format((int)$voucher->nilai, 0, ',', '.');
}

if (function_exists('tgl_view')) {
    $periode = tgl_view($voucher->tgl_mulai).' s/d '.tgl_view($voucher->tgl_selesai);
} else {
    $mulai   = $voucher->tgl_mulai ?? '';
    $selesai = $voucher->tgl_selesai ?? '';
    $periode = trim($mulai.' s/d '.$selesai);
}

$jenisLabel = 'Voucher';
if (isset($voucher->jenis_voucher) && $voucher->jenis_voucher === 'mingguan') {
    $jenisLabel = 'Voucher Mingguan';
}

$logoUrl = isset($store->logo_url) && $store->logo_url ? (string)$store->logo_url : null;

// JSON data untuk JS (RawBT)
$voucherPayload = [
    'toko'       => (string)($store->nama ?? ''),
    'alamat'     => (string)($store->alamat ?? ''),
    'telp'       => (string)($store->telp ?? ''),
    'kode'       => (string)$kode,
    'nama'       => (string)$nama,
    'jenis'      => (string)$jenisLabel,
    'tipe'       => (string)$tipe,
    'nilai'      => (string)$nilaiLabel,
    'periode'    => (string)$periode,
    'printed_at' => (string)$printed_at,
    'footer'     => (string)($store->footer ?? 'Terima kasih 🙏'),
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Voucher <?= esc($kode) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  @page { size: <?= $w ?> auto; margin: 2mm; }
  html, body { width: <?= $w ?>; }
  body {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-size: 11px; line-height: 1.35; color:#000;
    -webkit-print-color-adjust: exact; print-color-adjust: exact;
  }

  .wrap { max-width: 100%; }
  .center { text-align: center; }
  .right  { text-align: right; }
  .left   { text-align: left; }
  .small { font-size: 10px; }
  .muted { color:#333; }
  .mt4{margin-top:4px} .mb4{margin-bottom:4px}
  .mt6{margin-top:6px} .mb6{margin-bottom:6px}
  .mt8{margin-top:8px} .mb8{margin-bottom:8px}

  .brand {
    display:flex; flex-direction:column; align-items:center; justify-content:center;
  }
  .brand img.logo {
    display:block; width: 28mm; max-width: 80%; height:auto; margin-bottom:3px;
  }
  .brand .name   { font-weight: 800; font-size: 12px; letter-spacing: .3px; }
  .brand .addr   { line-height: 1.25; }

  .hr { border-top: 1px dashed #000; margin: 6px 0; }
  .hr-strong { border-top: 2px dashed #000; margin: 6px 0; }

  .row { display:flex; justify-content:space-between; align-items:flex-start; }
  .label { color:#111; }
  .value { color:#000; font-weight:600; }

  <?php if ($embed): ?>
  .noprint { display:none !important; }
  <?php endif; ?>
  @media print { .noprint { display:none !important; } }
</style>
</head>
<body>
<div class="wrap">

  <!-- Header toko -->
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

  <!-- Judul voucher -->
  <div class="center mb4">
    <div style="font-weight:800;">VOUCHER ORDER</div>
    <div class="small"><?= esc($jenisLabel) ?></div>
  </div>

  <!-- Detail voucher -->
  <div class="mb4">
    <div class="row">
      <div class="label">Kode</div>
      <div class="value"><?= esc($kode) ?></div>
    </div>
    <?php if ($nama !== ''): ?>
    <div class="row">
      <div class="label">Nama</div>
      <div class="value"><?= esc($nama) ?></div>
    </div>
    <?php endif; ?>
    <div class="row">
      <div class="label">Tipe</div>
      <div class="value"><?= esc($tipe) ?></div>
    </div>
    <div class="row">
      <div class="label">Nilai</div>
      <div class="value"><?= esc($nilaiLabel) ?></div>
    </div>
    <div class="row">
      <div class="label">Periode</div>
      <div class="value"><?= esc($periode) ?></div>
    </div>
  </div>

  <div class="hr-strong"></div>

  <!-- Instruksi singkat -->
  <div class="small">
    Gunakan kode voucher ini saat melakukan order di AUSI Cafe sebelum periode berakhir.
    <br>
    S&amp;K berlaku. Voucher tidak dapat diuangkan.
  </div>

  <div class="hr"></div>

  <!-- Footer -->
  <div class="center" style="margin-top:8px;">
    <div class="small"><?= esc($store->footer ?? 'Terima kasih 🙏') ?></div>
    <div class="small muted">Dicetak: <?= esc($printed_at) ?></div>

    <div class="noprint mt8">
      <button onclick="window.print()">🖨️ Cetak</button>
    </div>
  </div>

  <div class="hr" style="margin-top:10px;"></div>
</div>

<script>
(function(){
  const qs = new URLSearchParams(location.search);
  const AUTO_PRINT = qs.get('autoprint') === '1';
  const AUTO_CLOSE = qs.get('autoclose') === '1';
  const USE_RAWBT  = qs.get('rawbt') === '1';

  const COLS = <?= ($paperWidthMM === 80) ? 48 : 32 ?>;

  // Data voucher dari PHP
  const V = <?= json_encode($voucherPayload, JSON_UNESCAPED_UNICODE) ?>;

  const ESC = '\x1B', GS = '\x1D';
  const INIT= ESC+'@';
  const LEFT= ESC+'a'+'\x00', CTR= ESC+'a'+'\x01';
  const SIZE1X = GS+'!'+'\x00';

  const HR1 = '-'.repeat(COLS) + '\n';
  const HR2 = '='.repeat(COLS) + '\n';

  function clamp(s,n){ s = String(s||''); return s.length>n ? s.slice(0,n) : s; }
  function wrapText(s, width=COLS){
    s = String(s||''); const out=[]; while(s.length>width){ out.push(s.slice(0,width)); s = s.slice(width); }
    if(s) out.push(s); return out;
  }

  function sendToRawBT(escpos){
    const payload = encodeURIComponent(escpos);
    const intent  = `intent:${payload}#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;end;`;
    try { location.href = intent; }
    catch(e){ location.href = 'market://details?id=ru.a402d.rawbtprinter'; }
  }

  const CUT_PART_FEED_N = (n) => GS + 'V' + '\x41' + String.fromCharCode(n & 0xFF);
  const CUT_FEED_N   = 7;
  const TRAIL_LINES  = 3;
  const CUT_COMMAND  = CUT_PART_FEED_N(CUT_FEED_N);

  function buildEscposFromVoucher(v){
    let out = '';
    out += INIT + SIZE1X;

    // Header toko
    out += CTR + clamp(v.toko, COLS) + '\n';
    if (v.alamat) wrapText(v.alamat).forEach(l => out += CTR + clamp(l, COLS) + '\n');
    if (v.telp)   out += CTR + 'HP/WA: ' + clamp(v.telp, COLS-7) + '\n';

    out += HR2;
    out += CTR + 'VOUCHER ORDER' + '\n';
    if (v.jenis) out += CTR + clamp(v.jenis, COLS) + '\n';
    out += HR1;

    out += LEFT;
    out += 'Kode   : ' + v.kode + '\n';
    if (v.nama) out += 'Nama   : ' + v.nama + '\n';
    out += 'Tipe   : ' + v.tipe + '\n';
    out += 'Nilai  : ' + v.nilai + '\n';
    if (v.periode) out += 'Periode: ' + v.periode + '\n';

    out += HR1;
    wrapText('Gunakan kode ini saat order sebelum periode berakhir. S&K berlaku. Voucher tidak dapat diuangkan.')
      .forEach(l => out += l + '\n');

    if (v.footer) out += '\n' + CTR + clamp(v.footer, COLS) + '\n';
    if (v.printed_at) out += CTR + 'Dicetak: ' + clamp(v.printed_at, COLS-9) + '\n';

    out += '\n'.repeat(TRAIL_LINES) + CUT_COMMAND;
    return out;
  }

  if (USE_RAWBT){
    const escpos = buildEscposFromVoucher(V);
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
