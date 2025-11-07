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
$total       = (int)$total;
$kodeUnik    = (int)($order->kode_unik ?? 0);
$isDelivery  = ($modeRaw === 'delivery');
$deliveryFee = (int)($order->delivery_fee ?? 0);

$grandFallback = $total + ($isDelivery ? $deliveryFee : 0) + $kodeUnik;
$grandTotal    = isset($order->grand_total) && $order->grand_total !== null
                 ? (int)$order->grand_total
                 : $grandFallback;

// Support logo opsional
$logoUrl     = base_url('assets/images/logo_admin.png');
$paidMethod  = strtolower($order->paid_method ?? '');
$statusRaw   = strtolower($order->status ?? '');
$statusLabel = ($statusRaw==='paid') ? 'Lunas'
             : (($statusRaw==='verifikasi') ? 'Verifikasi' : 'Menunggu pembayaran');

// Kode unik hanya relevan untuk non-cash
$showKodeUnik = ($paidMethod !== 'cash' && $kodeUnik > 0);
$signature = 'Dev By Onhacker'; // boleh ambil dari config kalau mau

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

  .wrap { max-width: 100%; }
  .center { text-align: center; }
  .right  { text-align: right; }
  .left   { text-align: left; }
  .small { font-size: 10px; }
  .muted { color:#333; }

  .brand { display:flex; flex-direction:column; align-items:center; justify-content:center; }
  .brand img.logo { display:block; width: 28mm; max-width: 80%; height:auto; margin-bottom:3px; }
  .brand .name   { font-weight: 800; font-size: 12px; letter-spacing: .3px; }
  .brand .addr   { line-height: 1.25; }

  .hr { border-top: 1px dashed #000; margin: 6px 0; }
  .hr-strong { border-top: 2px dashed #000; margin: 6px 0; }

  .row { display:flex; justify-content:space-between; align-items:flex-start; }
  .label { color:#111; }
  .value { color:#000; font-weight:600; }

  table { width:100%; border-collapse: collapse; }
  th, td { padding: 2px 0; vertical-align: top; }
  thead th { font-weight: 700; border-bottom: 1px dashed #000; padding-bottom: 3px; }
  .produk { width: 50%; word-break: break-word; }
  .qty    { width: 10%; text-align:center; white-space: nowrap; }
  .harga  { width: 20%; text-align:right;  white-space: nowrap; }
  .sub    { width: 20%; text-align:right;  white-space: nowrap; }
  tbody tr:not(:last-child) td { border-bottom: 1px dotted #000; }

  .totbox { border: 1px dashed #000; border-radius: 3px; padding: 6px; margin-top: 6px; }
  .totrow { display:flex; justify-content:space-between; align-items:center; }
  .totrow + .totrow { margin-top: 4px; }
  .totrow .tlabel { font-weight: 700; }
  .totrow .tval   { font-weight: 800; }

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
    <?php if ($logoUrl): ?><img class="logo" src="<?= esc($logoUrl) ?>" alt="Logo"><?php endif; ?>
    <div class="name"><?= esc($store->nama) ?></div>
    <div class="addr small">
      <?= esc($store->alamat) ?>
      <?php if (!empty($store->telp)): ?><br>HP/WA: <?= esc($store->telp) ?><?php endif; ?>
    </div>
  </div>

  <div class="hr"></div>

  <!-- Meta -->
  <div class="mb4">
    <div class="row"><div class="label">No</div><div class="value"><?= esc($nomor) ?></div></div>
    <div class="row"><div class="label">Waktu</div><div class="value"><?= esc($waktu) ?></div></div>
    <div class="row"><div class="label">Mode</div><div class="value"><?= esc($modeLabel) ?></div></div>
    <?php if ($showMeja): ?>
      <div class="row"><div class="label">Meja</div><div class="value"><?= esc($meja) ?></div></div>
    <?php endif; ?>
    <?php if ($nama !== ''): ?>
      <div class="row"><div class="label">Pelanggan</div><div class="value"><?= esc($nama) ?></div></div>
    <?php endif; ?>
  </div>

  <div class="hr-strong"></div>

  <!-- Items -->
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

  <!-- Totals -->
  <div class="totbox">
    <div class="totrow"><div class="tlabel">Status Pembayaran</div><div class="tval"><?= esc($statusLabel) ?></div></div>
    <?php if ($showKodeUnik): ?>
      <div class="totrow"><div class="tlabel">Kode Unik</div><div class="tval"><?= rupiah($kodeUnik) ?></div></div>
    <?php endif; ?>
    <div class="totrow"><div class="tlabel">Total</div><div class="tval"><?= rupiah($total) ?></div></div>
    <?php if ($isDelivery && $deliveryFee > 0): ?>
      <div class="totrow"><div class="tlabel">Ongkir</div><div class="tval"><?= rupiah($deliveryFee) ?></div></div>
    <?php endif; ?>
    <div class="totrow"><div class="tlabel">Total Pembayaran</div><div class="tval"><?= rupiah($grandTotal) ?></div></div>
  </div>

  <div class="hr"></div>

  <!-- Footer -->
  <div class="center">
    <div class="small"><?= esc($store->footer) ?></div>
    <div class="small muted">Dicetak: <?= esc($printed_at) ?></div>
    <div class="noprint" style="margin-top:8px"><button onclick="window.print()">🖨️ Cetak</button></div>
  </div>

  <div class="hr" style="margin-top:10px;"></div>
  <div class="center small muted" style="margin-top:6px;">Dev By Onhacker</div>
</div>
<?php
// === Siapkan data URL logo (base64) agar aman untuk RawBT ===
$logoData = '';
$logoPath = base_url('assets/images/logo_admin.png'); // ganti sesuai lokasi logomu
if (is_file($logoPath)) {
  $mime = function_exists('mime_content_type') ? mime_content_type($logoPath) : 'image/png';
  if (!$mime) $mime = 'image/png';
  $logoData = 'data:'.$mime.';base64,'.base64_encode(file_get_contents($logoPath));
}
?>
<script>
(async function(){
  // ===== Query Params =====
  const qs = new URLSearchParams(location.search);
  const AUTO_PRINT = qs.get('autoprint') === '1';
  const AUTO_CLOSE = qs.get('autoclose') === '1';
  const USE_RAWBT  = qs.get('rawbt') === '1';

  // ===== Lebar kolom =====
  const COLS = <?= ($paperWidthMM === 80) ? 48 : 32 ?>;

  // ===== Data untuk ESC/POS =====
  const ORDER = {
    toko       : <?= json_encode((string)($store->nama ?? '')) ?>,
    alamat     : <?= json_encode((string)($store->alamat ?? '')) ?>,
    telp       : <?= json_encode((string)($store->telp ?? '')) ?>,
    nomor      : <?= json_encode($nomor) ?>,
    // logo: pakai base64 (jika tersedia), fallback ke $logoUrl
    logo_url   : <?= json_encode($logoData ?: ($logoUrl ?? '')) ?>,

    waktu      : <?= json_encode($waktu) ?>,
    mode_label : <?= json_encode($modeLabel) ?>,
    show_meja  : <?= $showMeja ? 'true' : 'false' ?>,
    meja       : <?= json_encode($meja) ?>,
    nama       : <?= json_encode($nama) ?>,
    status     : <?= json_encode($statusLabel) ?>,
    paid_method: <?= json_encode($paidMethod) ?>,
    kode_unik  : <?= (int)$kodeUnik ?>,
    show_kode  : <?= $showKodeUnik ? 'true' : 'false' ?>,
    is_delivery: <?= $isDelivery ? 'true' : 'false' ?>,
    delivery   : <?= (int)$deliveryFee ?>,
    total      : <?= (int)$total ?>,
    grand_total: <?= (int)$grandTotal ?>,
    footer     : <?= json_encode((string)($store->footer ?? '')) ?>,
    printed_at : <?= json_encode($printed_at) ?>,
    sign       : <?= json_encode(isset($signature)? $signature : 'Dev By Onhacker') ?>,
    items      : <?= json_encode(array_map(function($it){
                      return [
                        'nama'     => (string)($it->nama ?? '-'),
                        'qty'      => (int)$it->qty,
                        'harga'    => (int)$it->harga,
                        'subtotal' => (int)$it->subtotal,
                      ];
                    }, $items)) ?>
  };

  // ===== ESC/POS Helpers =====
  const ESC = '\x1B', GS = '\x1D';
  const INIT= ESC+'@';
  const LEFT= ESC+'a'+'\x00', CTR= ESC+'a'+'\x01';
  const SIZE1X = GS+'!'+'\x00';

  const HR1 = '-'.repeat(COLS) + '\n';
  const HR2 = '='.repeat(COLS) + '\n';

  // ==== CUT commands + kalibrasi ====
  const CUT_FULL_FEED_N = (n) => GS + 'V' + '\x42' + String.fromCharCode(n & 0xFF); // full+feed
  const CUT_PART_FEED_N = (n) => GS + 'V' + '\x41' + String.fromCharCode(n & 0xFF); // partial+feed
  let CUT_FEED_N  = Number(qs.get('cutn') || 7);   // default 7
  let TRAIL_LINES = Number(qs.get('trail') || 3);  // default 3
  const MODE_QS   = (qs.get('cutmode')||'partial').toLowerCase();
  if (!(CUT_FEED_N>0)) CUT_FEED_N = 7;
  if (!(TRAIL_LINES>=0)) TRAIL_LINES = 3;
  const CUT_COMMAND = (MODE_QS==='full') ? CUT_FULL_FEED_N(CUT_FEED_N)
                                         : CUT_PART_FEED_N(CUT_FEED_N);

  // ==== Layout kolom (80mm/58mm) ====
  // 80mm => 48 kolom: nama 22 | qty 5 | harga 10 | sub 11
  // 58mm => 32 kolom: nama 16 | qty 4 | harga 6  | sub 6
  const W_NAME  = (COLS===48)?22:16;
  const W_QTY   = (COLS===48)?5:4;
  const W_PRICE = (COLS===48)?10:6;
  const W_SUB   = COLS - W_NAME - W_QTY - W_PRICE;

  function feed(n){ return '\n'.repeat(Math.max(0, n|0)); }
  function clamp(s,n){ s = String(s||''); return s.length>n ? s.slice(0,n) : s; }
  function wrapText(s, width=COLS){ s=String(s||''); const out=[]; while(s.length>width){ out.push(s.slice(0,width)); s=s.slice(width); } if(s) out.push(s); return out; }
  function padR(s,n){ s=String(s); return s.length>=n ? s.slice(0,n) : s + ' '.repeat(n-s.length); }
  function padL(s,n){ s=String(s); return s.length>=n ? s.slice(-n) : ' '.repeat(n-s.length) + s; }
  function lineLR(left, right){
    left = String(left||''); right = String(right||'');
    const space = Math.max(1, COLS - left.length - right.length);
    return left + ' '.repeat(space) + right;
  }
  function formatRp(n){ return 'Rp ' + Number(n||0).toLocaleString('id-ID'); }

  // === GAMBAR / LOGO ===
  const IMG_MODE = (qs.get('imgmode') || 'raster').toLowerCase(); // 'raster' | 'bit'
  const DOT_WIDTH = (()=>{
    const qsW = Number(qs.get('logow') || '');
    return (qsW>0 ? qsW : (<?= ($paperWidthMM === 80) ? 576 : 384 ?>));
  })();

  async function loadImageToCanvas(url, maxDots){
    if (!url) return null;
    let src = url, imgSrc = url;
    const isDataURL = url.startsWith('data:');
    try{
      const abs = new URL(url, location.href);
      const sameOrigin = abs.origin === location.origin;
      if (location.protocol==='https:' && abs.protocol==='http:') {
        try { await fetch('https://'+abs.host+abs.pathname+abs.search+abs.hash, {method:'HEAD'}); src = 'https://'+abs.host+abs.pathname+abs.search+abs.hash; } catch(e){}
      }
      if (!isDataURL && sameOrigin) {
        const r = await fetch(src, {credentials:'include'});
        if (!r.ok) throw new Error('fetch '+r.status);
        const b = await r.blob();
        imgSrc = URL.createObjectURL(b);
      }
      const img = new Image();
      if (!isDataURL && imgSrc.startsWith('http')) img.crossOrigin = 'anonymous';
      img.src = imgSrc;
      await new Promise((res, rej)=>{ img.onload=res; img.onerror=rej; });

      const scale = Math.min(1, maxDots / (img.naturalWidth||maxDots));
      const w = Math.max(8, Math.min(maxDots, Math.floor((img.naturalWidth||maxDots)*scale)));
      const h = Math.max(1, Math.floor((img.naturalHeight||maxDots)*scale));

      const c = document.createElement('canvas');
      c.width = w; c.height = h;
      const ctx = c.getContext('2d');
      ctx.drawImage(img, 0, 0, w, h);

      if (!isDataURL && imgSrc.startsWith('blob:')) try{ URL.revokeObjectURL(imgSrc); }catch(e){}
      return c;
    }catch(e){
      console.warn('loadImageToCanvas fail', e);
      return null;
    }
  }

  // MODE 1: GS v 0 Raster
  async function escposImageRaster(url, maxDots, center=true){
    const c = await loadImageToCanvas(url, maxDots);
    if (!c) return '';
    const w = c.width, h = c.height;
    const ctx = c.getContext('2d');
    const id = ctx.getImageData(0,0,w,h).data;
    const bytesPerRow = Math.ceil(w/8);
    const buf = new Uint8Array(bytesPerRow*h);

    for (let y=0;y<h;y++){
      for (let x=0;x<w;x++){
        const i=(y*w+x)*4;
        const lum = 0.299*id[i] + 0.587*id[i+1] + 0.114*id[i+2];
        if (lum < 160) buf[y*bytesPerRow + (x>>3)] |= (0x80 >> (x & 7));
      }
    }

    const xL = bytesPerRow & 0xFF, xH = (bytesPerRow>>8) & 0xFF;
    const yL = h & 0xFF,          yH = (h>>8) & 0xFF;

    let out = center ? CTR : LEFT;
    out += GS+'v'+'0'+'\x00' + String.fromCharCode(xL,xH,yL,yH);
    for (let i=0;i<buf.length;i++) out += String.fromCharCode(buf[i]);
    out += '\n' + LEFT;
    return out;
  }

  // MODE 2: ESC * 24-dot Bit Image (paling kompatibel)
  async function escposImageBit(url, maxDots, center=true){
    const c = await loadImageToCanvas(url, maxDots);
    if (!c) return '';
    const w8 = (c.width >> 3) << 3; // width multiple of 8
    const h  = c.height;
    const ctx = c.getContext('2d');
    const id = ctx.getImageData(0,0,c.width,c.height).data;

    let out = center ? CTR : LEFT;
    // kirim per band 24 dot
    for (let y=0; y<h; y+=24){
      const sliceH = Math.min(24, h - y);
      // ESC * m nL nH  (m=33 -> 24-dot, double-density), n = width (pixels)
      const n = w8;
      const nL = n & 0xFF, nH = (n >> 8) & 0xFF;
      out += ESC + '*' + String.fromCharCode(33, nL, nH);
      // untuk tiap kolom pixel (x), kirim 3 byte vertikal (24 dot)
      for (let x=0; x<w8; x++){
        for (let k=0; k<3; k++){
          let byte = 0;
          for (let b=0; b<8; b++){
            const yy = y + k*8 + b;
            let bit = 0;
            if (yy < y + sliceH){
              const i = (yy*c.width + x) * 4;
              const lum = 0.299*id[i] + 0.587*id[i+1] + 0.114*id[i+2];
              bit = (lum < 160) ? 1 : 0;
            }
            byte |= (bit << (7 - b));
          }
          out += String.fromCharCode(byte);
        }
      }
      out += '\n'; // line feed setelah satu band
    }
    out += LEFT;
    return out;
  }

  async function escposImageFromUrl(url, maxDots, center=true){
    if (!url) return '';
    const mode = (qs.get('imgmode') || 'raster').toLowerCase();
    if (mode === 'bit') return escposImageBit(url, maxDots, center);
    return escposImageRaster(url, maxDots, center);
  }

  // ==== Header Row Helpers ====
  function rowHeader(){
    return padR('Item', W_NAME) + padR('Qty', W_QTY) + padL('Harga', W_PRICE) + padL('Subtotal', W_SUB) + '\n';
  }
  function rowItem(name, qty, harga, sub){
    return padR(name, W_NAME) + padL(qty, W_QTY) + padL(harga, W_PRICE) + padL(sub, W_SUB) + '\n';
  }

  // === Builder async ===
  async function buildEscposFromOrder(o){
    let out = INIT + ESC + '2' + SIZE1X;

    // LOGO (lebih kompatibel: pakai &imgmode=bit di URL)
    out += await escposImageFromUrl(o.logo_url, DOT_WIDTH, true);

    // Header teks
    out += CTR + clamp(o.toko, COLS) + '\n';
    if (o.alamat) wrapText(o.alamat).forEach(l=> out += CTR + clamp(l, COLS) + '\n');
    if (o.telp)   out += CTR + 'HP/WA: ' + clamp(o.telp, COLS-7) + '\n';
    out += LEFT;

    // Meta
    out += 'No: ' + o.nomor + '\n';
    if (o.waktu) out += 'Waktu: ' + o.waktu + '\n';
    out += 'Mode: ' + (o.mode_label||'-') + '\n';
    if (o.show_meja && o.meja) out += 'Meja: ' + o.meja + '\n';
    if (o.nama) out += 'Pelanggan: ' + o.nama + '\n';

    // Items
    out += HR2 + rowHeader() + HR1;
    (o.items||[]).forEach((it, idx, arr)=>{
      const nameLines = wrapText(it.nama||'', W_NAME);
      const qty = Number(it.qty||0);
      const harga = formatRp(it.harga||0);
      const sub   = formatRp(it.subtotal ?? qty*(it.harga||0));
      out += rowItem(nameLines.shift()||'', qty, harga, sub);
      nameLines.forEach(l => { out += rowItem(l, '', '', ''); });
      if (idx < arr.length - 1) out += HR1;
    });

    // Totals
    out += HR1;
    out += lineLR('Status Pembayaran', o.status||'-') + '\n';
    if (o.show_kode && Number(o.kode_unik||0)) out += lineLR('Kode Unik', formatRp(o.kode_unik)) + '\n';
    out += lineLR('Total', formatRp(o.total||0)) + '\n';
    if (o.is_delivery && Number(o.delivery||0)>0) out += lineLR('Ongkir', formatRp(o.delivery||0)) + '\n';
    out += lineLR('Total Pembayaran', formatRp(o.grand_total || o.total || 0)) + '\n';

    // Footer + signature
    if (o.footer) out += '\n' + CTR + clamp(o.footer, COLS) + '\n';
    if (o.printed_at) out += CTR + 'Dicetak: ' + clamp(o.printed_at, COLS-9) + '\n';
    if (o.sign) out += CTR + clamp(o.sign, COLS) + '\n';
    out += LEFT;

    // Potong
    out += feed(TRAIL_LINES) + CUT_COMMAND;
    return out;
  }

  // ===== Eksekusi =====
  if (USE_RAWBT){
    const escpos = await buildEscposFromOrder(ORDER);
    const payload = encodeURIComponent(escpos);
    const intent  = `intent:${payload}#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;end;`;
    try { location.href = intent; } catch(e){ location.href = 'market://details?id=ru.a402d.rawbtprinter'; }
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
