<?php $this->load->view("front_end/head.php"); ?>
<div class="container-fluid">
  <div class="hero-title" role="banner" aria-label="Judul situs">
    <h1 class="text"><?php echo $title ?></h1>
    <span class="accent" aria-hidden="true"></span>
  </div>

  <?php
  // ======== PENGATURAN KERTAS ========
  $paper = isset($paper) ? (string)$paper : (isset($_GET['w']) ? $_GET['w'] : '58');
  $paper = ($paper === '80') ? '80' : '58'; // default 58mm

  // Lebar kontainer pratinjau (px) — tidak memengaruhi print.
  $previewWidth = ($paper === '80') ? 600 : 420;
  // Margin @page untuk print (mm)
  $pageMarginMM = ($paper === '80') ? 3 : 2;

  $status = strtolower($order->status ?? 'pending');

  // Flag & angka delivery
  $is_delivery  = strtolower($order->mode ?? '') === 'delivery';
  $delivery_fee = (int)($order->delivery_fee ?? 0);

  // ======== FORMAT TANGGAL INDONESIA ========
  if (!function_exists('indo_datetime')) {
    function indo_datetime($dt, $with_day = false){
      if (!$dt) $dt = date('Y-m-d H:i:s');
      $ts = is_numeric($dt) ? (int)$dt : strtotime($dt);
      if (!$ts) return (string)$dt;

      $bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
      $hari  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];

      $d  = date('j', $ts);
      $m  = $bulan[(int)date('n', $ts)] ?? date('m', $ts);
      $y  = date('Y', $ts);
      $hi = date('H:i', $ts);

      $str = "$d $m $y $hi";
      if ($with_day){
        $str = $hari[(int)date('w', $ts)] . ', ' . $str;
      }
      return $str;
    }
  }

  // ======== HELPER & NORMALISASI (dari “isi” kamu) ========
  function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
  function rupiah($n){ return 'Rp '.number_format((int)$n,0,',','.'); }

  // Mapping $store jika belum dikirim (agar tidak undefined)
  if (!isset($store)) {
    $store = (object)[
      'nama'     => $rec->nama_website ?? '',
      'alamat'   => $rec->alamat ?? '',
      'telp'     => $rec->no_telp ?? '',
      'logo_url' => base_url("assets/images/logo_admin.png"),
      'footer'   => $rec->nama_website ?? '',
    ];
  }
  if (empty($printed_at)) $printed_at = date('d/m/Y H:i');

  // Variabel “isi”
  $nomor    = $order->nomor ?? ('#'.$order->id);
  $waktu    = $order->created_at ? date('d/m/Y H:i', strtotime($order->created_at)) : '-';
  $mode     = $order->mode ?? '-';
  $meja     = $order->meja_nama ?: ($order->meja_kode ?: '-');
  $nama     = trim((string)($order->nama ?? ''));
  $catatan  = trim((string)($order->catatan ?? ''));

  // Mode label + tampil meja
  $modeRaw = strtolower($mode);
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

  // ==== Voucher (dari kolom pesanan) ====
  $voucherCode = isset($order->voucher_code) ? trim((string)$order->voucher_code) : '';
  $voucherDisc = (int)($order->voucher_disc ?? 0);
  $hasVoucher  = ($voucherDisc > 0);

  // grand total fallback: subtotal - voucher + ongkir + kode unik
  $baseAfterVoucher = $total - $voucherDisc;
  if ($baseAfterVoucher < 0) $baseAfterVoucher = 0;

  $grandFallback = $baseAfterVoucher + ($isDelivery ? $deliveryFee : 0) + $kodeUnik;

  $grandTotal = isset($order->grand_total) && $order->grand_total !== null
      ? (int)$order->grand_total
      : $grandFallback;


  $logoUrl     = isset($store->logo_url) && $store->logo_url ? (string)$store->logo_url : null;
  $paidMethod  = strtolower($order->paid_method ?? '');
  $statusRaw   = strtolower($order->status ?? '');
  $statusLabel = ($statusRaw==='paid') ? 'Lunas'
  : (($statusRaw==='verifikasi') ? 'Verifikasi' : 'Menunggu pembayaran');

  // Kode unik hanya relevan untuk non-cash
  $showKodeUnik = ($paidMethod !== 'cash' && $kodeUnik > 0);
  ?>
<?php
  // === Tambahan: mapping label metode pembayaran + info pendukung ===
  $paidMethodRaw = strtolower($order->paid_method ?? '');
  $paymentChannel = trim((string)($order->payment_channel ?? $order->bank ?? ''));
  $paymentRef     = trim((string)($order->payment_ref ?? $order->trx_id ?? ''));
  $paidAt         = !empty($order->paid_at) ? indo_datetime($order->paid_at, true) : null;

  switch ($paidMethodRaw) {
    case 'cash': case 'tunai':         $paidMethodLabel = 'Tunai'; break;
    case 'qris':                       $paidMethodLabel = 'QRIS'; break;
    case 'transfer':                   $paidMethodLabel = 'Transfer Bank'; break;
    case 'ewallet': case 'e-wallet':   $paidMethodLabel = 'E-Wallet'; break;
    case 'va': case 'virtual_account': $paidMethodLabel = 'Virtual Account'; break;
    default:
      $paidMethodLabel = $paidMethodRaw !== '' ? ucwords($paidMethodRaw) : '—';
  }

  // Gabung channel ke label kalau ada, misal: "Transfer Bank (BCA)"
  if ($paymentChannel !== '') {
    $paidMethodWithChannel = $paidMethodLabel . ' (' . esc($paymentChannel) . ')';
  } else {
    $paidMethodWithChannel = $paidMethodLabel;
  }
?>

  <style>
    :root{
      --preview-width: <?= (int)$previewWidth ?>px;
      --font-size: 12px;
      --line-height: 1.35;
    }
    *{ box-sizing:border-box; }

    #receipt-area, #receipt-area *{
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono",
      "Courier New", monospace !important;
      font-variant-numeric: tabular-nums;
    }

    @media print{
      #receipt-area, #receipt-area *{
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono",
        "Courier New", monospace !important;
      }
    }

    .receipt{
      max-width: var(--preview-width);
      margin: 12px auto;
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 10px;
      padding: 12px;
    }
    .in-card .receipt{ max-width:100%; margin:0; background:transparent; border:0; border-radius:0; padding:0; }

    /* KELAS-KELAS DARI “ISI” */
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

    .brand { display:flex; flex-direction:column; align-items:center; justify-content:center; }
    .brand img.logo { display:block; width: 28mm; max-width: 80%; height:auto; margin-bottom:3px; }
    .brand .name { font-weight: 800; font-size: 12px; letter-spacing: .3px; }
    .brand .addr { line-height: 1.25; }

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
    .harga  { width: 20%; text-align:center;  white-space: nowrap; }
    .sub    { width: 20%; text-align:center;  white-space: nowrap; }
    tbody tr:not(:last-child) td { border-bottom: 1px dotted #000; }

    .totbox { border: 1px dashed #000; border-radius: 3px; padding: 6px; margin-top: 4px; }
    .totrow { display:flex; justify-content:space-between; align-items:center; }
    .totrow + .totrow { margin-top: 4px; }
    .totrow .tlabel { font-weight: 700; }
    .totrow .tval   { font-weight: 800; }
    
  </style>

  <div class="row">
    <div class="col-12 col-lg-8 mx-auto">
      <div class="card card-body in-card" id="receipt-area">

        <div class="receipt" >
          <!-- ======= ISI DISAMAKAN DENGAN SNIPPET KAMU ======= -->
          <div class="wrap">

            <!-- Brand -->
            <div class="brand center">
              <?php if ($logoUrl): ?>
                <img class="logo" style="width: 20%" src="<?= esc($logoUrl) ?>" alt="Logo" >
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

            <!-- Meta Order -->
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

              <!-- DIPINDAHKAN KE ATAS (dari kotak Total) -->
              <div class="row">
                <div class="label">Metode Pembayaran</div>
                <div class="value"><?= esc($paidMethodWithChannel) ?></div>
              </div>

              <div class="row">
                <div class="label">Status Pembayaran</div>
                <div class="value"><?= esc($statusLabel) ?></div>
              </div>
            </div>
                          <?php if ($hasVoucher): ?>
                <div class="row">
                  <div class="label">Voucher</div>
                  <div class="value">
                    <?php if ($voucherCode !== ''): ?>
                      <?= esc($voucherCode) ?> (<?= rupiah($voucherDisc) ?>)
                    <?php else: ?>
                      Potongan voucher <?= rupiah($voucherDisc) ?>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endif; ?>

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
              <?php if ($paymentRef !== ''): ?>
                <div class="totrow">
                  <div class="tlabel">Ref/Trx</div>
                  <div class="tval"><?= esc($paymentRef) ?></div>
                </div>
              <?php endif; ?>

              <div class="totrow">
                <div class="tlabel">Subtotal</div>
                <div class="tval"><?= rupiah($total) ?></div>
              </div>

              <?php if ($hasVoucher): ?>
                <div class="totrow">
                  <div class="tlabel">
                    Voucher<?php if ($voucherCode !== ''): ?> (<?= esc($voucherCode) ?>)<?php endif; ?>
                  </div>
                  <div class="tval">- <?= rupiah($voucherDisc) ?></div>
                </div>
              <?php endif; ?>

              <?php if ($isDelivery && $deliveryFee > 0): ?>
                <div class="totrow">
                  <div class="tlabel">Ongkir</div>
                  <div class="tval"><?= rupiah($deliveryFee) ?></div>
                </div>
              <?php endif; ?>

              <?php if ($showKodeUnik): ?>
                <div class="totrow">
                  <div class="tlabel">Kode Unik</div>
                  <div class="tval"><?= rupiah($kodeUnik) ?></div>
                </div>
              <?php endif; ?>

              <div class="totrow">
                <div class="tlabel">Total Pembayaran</div>
                <div class="tval"><?= rupiah($grandTotal) ?></div>
              </div>
            </div>


            <div class="hr"></div>

            <!-- Footer -->
            <div class="center">
              <div class="small"><?= esc($store->footer) ?></div>
              <div class="small muted">Dicetak: <?= esc($printed_at) ?></div>
            </div>

            <div class="hr" style="margin-top:10px;"></div>
            <div class="center small muted" style="margin-top:6px;">Dev By Onhacker</div>

          </div><!-- /.wrap -->
        </div>

        <?php
        // fallback url kembali ke halaman ringkasan pesanan
        $back_url = site_url('produk/order_success/'.$order->nomor);
        ?>
        <div class="no-print mt-3 d-flex flex-wrap justify-content-between gap-2"
             data-html2canvas-ignore="true">
          <a href="<?= $back_url ?>" class="btn btn-outline-secondary btn-sm">
            <i class="mdi mdi-arrow-left"></i> Kembali
          </a>
          <button id="btn-screenshot" type="button" class="btn btn-outline-primary btn-sm">
            <i class="mdi mdi-camera"></i> Screenshot
          </button>
        </div>
        <div class="center small" data-html2canvas-ignore="true" style="margin-top:8px;">
          Butuh struk fisik? Tinggal bilang ke kasir ya 😉
        </div>

      </div>
    </div>
  </div>

</div>
<!-- html2canvas untuk screenshot -->
<script src="<?php echo base_url("assets/js/canva.js") ?>"></script>
<script>
  (function(){
    function makeFilename(){
      var n = <?= json_encode($order->nomor ?? $order->id) ?>;
      return 'struk-' + String(n).replace(/\s+/g,'-') + '.png';
    }

    document.getElementById('btn-screenshot').addEventListener('click', function(){
      var btn = this;
      var target = document.getElementById('receipt-area');
      if (!window.html2canvas || !target){ return alert('Gagal memuat alat screenshot.'); }

      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span> Memproses…';

      var scale = Math.max(window.devicePixelRatio || 1, 2);
      html2canvas(target, {
        scale: scale,
        backgroundColor: '#fff',
        useCORS: true,
        windowWidth: target.scrollWidth,
        windowHeight: target.scrollHeight
      }).then(function(canvas){
        var margin = 24; // px
        var out = document.createElement('canvas');
        out.width  = canvas.width  + margin * 2;
        out.height = canvas.height + margin * 2;

        var ctx = out.getContext('2d');
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, out.width, out.height);
        ctx.drawImage(canvas, margin, margin);

        out.toBlob(function(blob){
          var a = document.createElement('a');
          a.href = URL.createObjectURL(blob);
          a.download = makeFilename();
          document.body.appendChild(a);
          a.click();
          URL.revokeObjectURL(a.href);
          a.remove();
          btn.disabled = false;
          btn.innerHTML = '<i class="mdi mdi-camera"></i> Screenshot';
        }, 'image/png', 1);
      }).catch(function(err){
        console.error(err);
        alert('Gagal membuat screenshot.');
        btn.disabled = false;
        btn.innerHTML = '<i class="mdi mdi-camera"></i> Screenshot';
      });
    });
  })();
</script>

<?php $this->load->view("front_end/footer.php"); ?>
