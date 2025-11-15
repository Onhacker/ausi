<?php
/**
 * application/modules/produk/views/order_success_view.php
 */
?>
<?php $this->load->view("front_end/head.php"); ?>

<style>
  /* ====== Cosmetics umum ====== */
  .card              { border-radius:14px; border:1px solid #eef2f7; }
  .section-title     { font-weight:700; font-size:1.05rem; }
  .dark              { color:#6c757d; }
  .soft              { background:#f8fafc; border-radius:12px; padding:.6rem .75rem; }
  .badge-pill        { border-radius: 999px; padding:.4rem .65rem; font-size:.8rem; }
  .money             { font-weight:700; letter-spacing:.3px; }
  .money-lg          { font-weight:800; font-size:1.8rem; }
  .kv .k             { color:#6c757d; }
  .kv .v             { font-weight:600; }
  .divider-dot       { height:6px; width:6px; border-radius:999px; background:#e5e7eb; display:inline-block; margin:0 .35rem; vertical-align:middle; }

  /* tabel */
  .table th, .table td { vertical-align:middle; }
  .table thead th      { background:#f9fbfd; border-top:0; }

  /* mobile tweaks */
  @media (max-width: 767.98px){
    .actions-wrap { flex-direction: column !important; align-items: stretch !important; gap:.5rem; }
    .actions-wrap .btn { width:100%; }
  }

  .badge-mode{ font-weight:700; }

  /* Info card */
  .info-box{
    border:1px dashed #cbd5e1; background:#f8fafc; border-radius:12px; padding:.75rem .9rem;
  }

  /* tombol pembayaran */
  .pay-actions{
    display:flex; flex-wrap:wrap; align-items:center; gap:.5rem; margin-top:.6rem;
  }
  .pay-actions .btn{
    min-height:40px; padding:.45rem .75rem; border-radius:12px;
    display:inline-flex; align-items:center; gap:.4rem;
  }
  .pay-actions .btn i{ line-height:1; font-size:1rem; }
  @media (max-width: 767.98px){
    .pay-actions .btn{ flex:1 1 calc(33.33% - .4rem); }
  }

  /* ===== Status chips ===== */
  .status-chip{
    display:inline-flex; align-items:center; gap:.4rem;
    border-radius:999px; padding:.22rem .6rem; font-size:.8rem; font-weight:700;
    border:1px solid transparent;
  }
  .status-paid{ background:#ecfdf5; color:#065f46; border-color:#a7f3d0; }      /* hijau */
  .status-verif{ background:#fffbeb; color:#92400e; border-color:#fde68a; }     /* kuning */
  .status-pending{ background:#f3f4f6; color:#374151; border-color:#e5e7eb; }   /* abu */
  .status-canceled{ background:#fee2e2; color:#7f1d1d; border-color:#fecaca; }  /* merah */

  /* Opsional: Ribbon LUNAS di pojok kartu */
  .ribbon-paid{
    position:absolute; top:10px; right:-40px; transform:rotate(45deg);
    background:#10b981; color:#fff; padding:.3rem 2.2rem; font-weight:800;
    box-shadow:0 6px 14px rgba(16,185,129,.25); letter-spacing:.5px; font-size:.75rem;
  }
</style>

<div class="container-fluid">
  <!-- Judul -->
  <div class="hero-title" role="banner" aria-label="Judul halaman">
    <h1 class="text">Terima kasih! 🎉</h1>
    <span class="accent" aria-hidden="true"></span>
  </div>

<?php
// ===== Helper tanggal Indonesia =====
if (!function_exists('tanggal_indo')) {
  function tanggal_indo($datetime, $show_time = true, $tz = 'Asia/Jakarta') {
    if (!$datetime) return '';
    try {
      if (is_numeric($datetime)) $dt = (new DateTime('@'.$datetime))->setTimezone(new DateTimeZone($tz));
      else $dt = new DateTime($datetime, new DateTimeZone($tz));
    } catch (Exception $e) { return $datetime; }
    $hari  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    $bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    return $hari[(int)$dt->format('w')].', '.(int)$dt->format('j').' '.$bulan[(int)$dt->format('n')].' '.$dt->format('Y').($show_time ? ' '.$dt->format('H:i') : '');
  }
}

// ===== Normalisasi variabel dari controller =====
$order         = isset($order) ? $order : null;
$items         = isset($items) && is_array($items) ? $items : [];
$meja_info     = isset($meja_info) ? $meja_info : null;
$status        = strtolower($order->status ?? 'pending');
$order_code    = $order->kode ?? ($order->order_code ?? ($order->invoice ?? null));
$created_at    = $order->created_at ?? ($order->tanggal ?? date('Y-m-d H:i:s'));
$created_at_id = tanggal_indo($created_at, true, 'Asia/Makassar');
$customer      = $order->nama ?? ($order->nama_pelanggan ?? ($customer_name ?? 'Pelanggan'));
$note_text     = isset($order->note) ? $order->note : (isset($order->catatan) ? $order->catatan : '');

$mode          = strtolower(trim((string)($order->mode ?? 'walkin')));
$is_dinein     = ($mode === 'dinein');
$is_delivery   = ($mode === 'delivery');

$delivery_fee    = (int)($order->delivery_fee ?? 0);
$customer_phone  = trim((string)($order->customer_phone ?? ''));
$alamat_kirim    = trim((string)($order->alamat_kirim ?? ''));
// Voucher
$voucher_disc = (int)($order->voucher_disc ?? 0);
$voucher_code = trim((string)($order->voucher_code ?? ''));
$has_voucher  = ($voucher_disc > 0);

if (!isset($total)) {
  $total = 0;
  foreach ($items as $it) { $total += (int)($it->harga ?? 0) * (int)($it->qty ?? 0); }
}

$kode_unik   = (int)($order->kode_unik ?? 0);

// Subtotal setelah voucher (fallback, kalau kolom grand_total kosong)
$subtotal_after_voucher = $total - $voucher_disc;
if ($subtotal_after_voucher < 0) $subtotal_after_voucher = 0;

/* Total bayar:
   - dinein/walkin: total - voucher + kode unik
   - delivery     : total - voucher + ongkir + kode unik
   (kalau kolom grand_total di DB sudah ada, kita pakai itu) */
$grand_total = (int)($order->grand_total ??
              ($subtotal_after_voucher + ($is_delivery ? $delivery_fee : 0) + $kode_unik));


/* Metode terkunci? (kalau sudah dipilih sebelumnya) */
$locked_method = strtolower(trim((string)(
    $order->paid_method
    ?? ($this->input->get('m') ?? '')
)));

/* Tampilkan tombol bayar:
   - Delivery: muncul jika ongkir > 0
   - Dine-in/Walk-in: langsung boleh muncul
   - Umum: status bukan paid/canceled & belum locked method
*/
$show_pay_buttons = (
  !in_array($status, ['paid','canceled'], true)
  && $locked_method === ''
  && ( ($is_delivery && $delivery_fee > 0) || (!$is_delivery) )
);

// Label mode
$mode_badge = 'badge-danger';
$mode_label = 'Bungkus';
if ($is_dinein){ $mode_badge = 'badge-danger';  $mode_label = !empty($meja_info) ? strtoupper($meja_info) : 'Dine-in'; }
elseif ($is_delivery){ $mode_badge = 'badge-danger'; $mode_label = 'Antar/ Kirim'; }

$paid_method   = strtolower(trim((string)($order->paid_method ?? '')));
$paid_at_raw   = $order->paid_at ?? null;
$paid_at_id    = $paid_at_raw ? tanggal_indo($paid_at_raw, true, 'Asia/Makassar') : null;
$is_paid       = ($status === 'paid');

/* Helper label & class status */
function status_badge($st){
  $st = strtolower((string)$st);
  if ($st === 'paid')       return ['LUNAS','status-paid'];
  if ($st === 'verifikasi') return ['Menunggu Verifikasi','status-verif'];
  if ($st === 'canceled')   return ['Dibatalkan','status-canceled'];
  return ['Menunggu Pembayaran','status-pending']; // pending / default
}
list($status_label, $status_class) = status_badge($status);
?>

  <!-- Info Meja / Mode -->
<?php $this->load->view("judul_mode") ?>
  <!-- Ringkasan + Kanan -->
  <div class="card card-body mb-3 position-relative">
    <?php if ($is_paid): ?>
      <div class="ribbon-paid">LUNAS</div>
    <?php endif; ?>
    <div class="row">
      <!-- Kolom kiri: Ringkasan order -->
      <div class="col-md-7">
        <div class="section-title mb-2">RINGKASAN PESANAN</div>

        <div class="row">
          <?php if ($order_code): ?>
          <div class="col-sm-6 mb-1 kv">
            <div class="k">Kode Pesanan</div>
            <div class="v"><code><?= html_escape($order_code) ?></code></div>
          </div>
          <?php endif; ?>

          <div class="col-sm-6 mb-1 kv">
            <div class="k">Waktu</div>
            <div class="v"><?= html_escape($created_at_id) ?></div>
          </div>

          <div class="col-sm-6 mb-1 kv">
            <div class="k">Pelanggan</div>
            <div class="v"><?= html_escape($customer) ?></div>
          </div>

          <?php if ($is_dinein && !empty($meja_info)): ?>
          <div class="col-sm-6 mb-1 kv">
            <div class="k">Meja</div>
            <div class="v"><?= html_escape($meja_info) ?></div>
          </div>
          <?php endif; ?>

          <?php if ($is_delivery): ?>
          <div class="col-sm-6 mb-1 kv">
            <div class="k">Telepon</div>
            <div class="v"><?= html_escape($customer_phone ?: '-') ?></div>
          </div>
          <div class="col-sm-12 mb-1 kv">
            <div class="k">Alamat Pengantaran</div>
            <div class="v"><?= nl2br(html_escape($alamat_kirim ?: '-')) ?></div>
          </div>
          <?php endif; ?>

          <?php if (trim((string)$note_text) !== ''): ?>
          <div class="col-sm-12 mb-1 kv">
            <div class="k">Catatan</div>
            <div class="v"><?= nl2br(html_escape($note_text)) ?></div>
          </div>
          <?php endif; ?>
          <?php if ($has_voucher): ?>
            <div class="col-sm-12 mb-1 kv">
              <div class="k">Voucher</div>
              <div class="v">
                <?php if ($voucher_code): ?>
                  <code><?= html_escape($voucher_code) ?></code>
                  <?php else: ?>
                    <code>VOUCHER</code>
                  <?php endif; ?>
                  <span class="ml-1">
                    &mdash; Potongan Rp <?= number_format($voucher_disc, 0, ',', '.') ?>
                  </span>
                </div>
              </div>
            <?php endif; ?>

        </div>
      </div>

      <!-- Kolom kanan: Status + Total + Struk + Pay -->
      <div class="col-md-5 mt-2 mt-md-0">
        <!-- Status chip selalu tampil -->
        <div class="mb-2">
          <span class="status-chip <?= $status_class ?>">
            <i class="mdi <?= $is_paid ? 'mdi-check-circle' : ($status==='verifikasi' ? 'mdi-timer-sand' : ($status==='canceled'?'mdi-close-circle':'mdi-dots-horizontal')) ?>"></i>
            <?= $status_label ?>
          </span>
          <?php if ($is_paid && $paid_method): ?>
            <small class="ml-2 text-muted">
              via <strong><?= strtoupper(html_escape($paid_method)) ?></strong>
              <?= $paid_at_id ? ' • '.$paid_at_id : '' ?>
            </small>
          <?php endif; ?>
        </div>

        <?php if ($is_delivery && $delivery_fee <= 0): ?>
          <style type="text/css">
            /* ===== Status Delivery Enhanced (responsive) ===== */
            .status-box{ position:relative; overflow:hidden; }
            .status-accent{
              position:absolute; inset:auto 0 0 0; height:3px;
              background: linear-gradient(90deg,#ef4444, #f59e0b, #ef4444);
              background-size:200% 100%;
              animation: slideAccent 2.8s linear infinite;
              opacity:.9;
            }
            @keyframes slideAccent{ 0%{background-position:0% 0} 100%{background-position:200% 0} }

            .status-layout{ display:flex; align-items:center; }
            .status-media .status-video{
              width:96px; height:96px; border-radius:12px; object-fit:cover; display:block;
              box-shadow: 0 4px 12px rgba(0,0,0,.08);
            }

            /* badge lembut berdenyut */
            .badge-soft{
              background:#fff5f5; color:#dc2626; border:1px solid #fee2e2; border-radius:999px; padding:.18rem .55rem; font-size:.76rem;
            }
            .badge-pulse{ position:relative; }
            .badge-pulse::after{
              content:""; position:absolute; inset:-4px; border-radius:999px;
              border:2px solid rgba(220,38,38,.35); animation:pulse 1.8s ease-out infinite;
            }
            @keyframes pulse{ 0%{transform:scale(.9); opacity:1} 100%{transform:scale(1.25); opacity:0} }

            /* progress shimmer */
            .status-progress .bar{
              position:relative; height:8px; border-radius:999px; background:#fdecec; overflow:hidden;
            }
            .status-progress .bar .shine{
              position:absolute; inset:0 60% 0 0; border-radius:999px; background:#ef4444;
              animation: loadSweep 1.6s ease-in-out infinite;
            }
            @keyframes loadSweep{
              0%{ left:0; right:60%; opacity:.75 }
              50%{ left:20%; right:20%; opacity:.95 }
              100%{ left:60%; right:0; opacity:.75 }
            }

            /* ===== Mobile tweaks ===== */
            @media (max-width: 575.98px){
              .status-layout{ flex-direction:column; align-items:flex-start; gap:.5rem; }
              .status-media{ margin-right:0 !important; }
              .status-media .status-video{ width:100px; height:100px;  }
              .badge-soft{ font-size:.7rem; padding:.14rem .48rem; }
              .status-progress .bar{ height:6px; }
              .status-box .text-danger{ font-size:.9rem; }
              #ongkir-count{ display:block; margin-top:.2rem; }
            }

            /* Dark mode tweak (opsional) */
            @media (prefers-color-scheme: dark){
              .badge-soft{ background:#2b1f20; color:#fda4af; border-color:#3b2325; }
              .status-progress .bar{ background:#2b1f20; }
              .status-progress .bar .shine{ background:#fb7185; }
            }

            /* Kurangi gerak untuk users yang prefer reduce motion */
            @media (prefers-reduced-motion: reduce){
              .status-accent, .badge-pulse::after, .status-progress .bar .shine{ animation:none; }
            }
          </style>

          <div class="section-title mb-2">STATUS</div>
          <div class="info-box mb-2 status-box">
            <div class="status-accent"></div>

            <div class="status-layout">
              <!-- Animasi delivery -->
              <div class="mr-3 status-media">
                <video autoplay loop muted playsinline class="status-video">
                  <source src="<?= base_url('assets/images/delivery.webm') ?>" type="video/webm">
                </video>
              </div>

              <div class="flex-grow-1">
                <div class="d-flex align-items-center flex-wrap">
                  <span class="badge badge-soft badge-pulse ml-1">Menghitung ongkir</span>
                </div>

                <div class="text-dark mt-1">
                  Tenang, lagi dihitung ongkir dari alamat kamu. Sebentar lagi hasilnya
                  <em>(beserta opsi metode pembayaran)</em> bakal tampil langsung di halaman ini.
                  System juga bakal kabarin via <strong>WhatsApp</strong>.
                  Sabar dulu ya 🙌<br>
                  <strong>Sambil nunggu, orderan kamu sementara kami proses 😉</strong>
                </div>

                <!-- Loading row -->
                <div id="ongkir-loading" class="d-flex align-items-center mt-2" aria-live="polite">
                  <div class="spinner-border spinner-border-sm text-danger mr-2" role="status" aria-hidden="true"></div>
                  <span class="text-danger font-weight-600 mr-2">Sedang mengkalkulasi ongkir…</span>
                  <span class="divider-dot"></span>
                  <!-- <small id="ongkir-count" class="text-blue ml-2"></small> -->
                </div>

                <!-- Progress bar animasi -->
                <div class="status-progress mt-2" aria-hidden="true">
                  <div class="bar"><span class="shine"></span></div>
                </div>
              </div>
            </div>
          </div>

          
        <?php endif; ?>

        <div class="soft">
          <div class="d-flex justify-content-between">
            <span class="dark">Subtotal</span>
            <span class="money">Rp <?= number_format((int)$total,0,',','.') ?></span>
          </div>
          <?php if ($has_voucher): ?>
            <div class="d-flex justify-content-between">
              <span class="dark">
                Voucher<?php if ($voucher_code): ?> (<?= html_escape($voucher_code) ?>)<?php endif; ?>
              </span>
              <span class="money text-danger">- <?= number_format($voucher_disc,0,',','.') ?></span>
            </div>
          <?php endif; ?>

          <?php if ($is_delivery && $delivery_fee > 0): ?>
          <div class="d-flex justify-content-between">
            <span class="dark">Ongkir</span>
            <span class="money">+ <?= number_format((int)$delivery_fee,0,',','.') ?></span>
          </div>
          <?php endif; ?>

          <?php if ((int)$kode_unik > 0): ?>
          <div class="d-flex justify-content-between">
            <span class="dark">Kode Unik</span>
            <span class="money">+ <?= number_format((int)$kode_unik,0,',','.') ?></span>
          </div>
          <?php endif; ?>

          <hr class="my-2">
          <div class="d-flex justify-content-between align-items-center">
            <div class="dark">Total Bayar</div>
            <div class="money money-lg">Rp <?= number_format((int)$grand_total,0,',','.') ?></div>
          </div>

          <?php if ($show_pay_buttons): ?>
          <div class="pay-actions">
            <a class="btn btn-success btn-sm js-pay"
               href="<?= site_url('produk/pay_cash/'.rawurlencode($order->nomor)) ?>"
               data-method="cash">
              <i class="mdi mdi-cash"></i><span>TUNAI</span>
            </a>
            <a class="btn btn-primary btn-sm js-pay"
               href="<?= site_url('produk/pay_qris/'.rawurlencode($order->nomor)) ?>"
               data-method="qris">
              <i class="mdi mdi-qrcode-scan"></i><span>QRIS</span>
            </a>
            <a class="btn btn-info btn-sm js-pay"
               href="<?= site_url('produk/pay_transfer/'.rawurlencode($order->nomor)) ?>"
               data-method="transfer">
              <i class="mdi mdi-bank-transfer"></i><span>TRANSFER</span>
            </a>
          </div>
          <?php endif; ?>
        </div>

        <?php
        // TAMPILKAN INSTRUKSI KHUSUS JIKA METODE = CASH
        $method_now = strtolower(trim((string)(
          $order->paid_method ?? $locked_method ?? ''
        )));
        if (!in_array($status, ['paid','canceled'], true) && $method_now === 'cash'): ?>
          <div class="info-box mt-2">
            <div class="d-flex">
              <div style="font-size:1.25rem;line-height:1">💵</div>
              <div class="ml-2">
                <div class="font-weight-700 mb-1">Pembayaran Tunai</div>
                <?php if ($is_delivery): ?>
                  <div>Silakan beri <strong>uang tunai</strong> kepada staff kami saat <strong>mengantar</strong> orderan Anda.</div>
                <?php elseif ($is_dinein): ?>
                  <div>Silakan langsung lakukan <strong>pembayaran tunai di KASIR sebelum orderan di antar</strong></div>
                <?php else: /* walkin / take-away */ ?>
                  <div>Silakan lakukan <strong>pembayaran tunai</strong> kepada <strong>KASIR</strong> saat <strong>pengambilan</strong> orderan.</div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($is_paid && !empty($order->id)): ?>
          <div class="mt-2 d-flex flex-wrap align-items-center" style="gap:.5rem">
            <a class="btn btn-danger btn-sm"
               href="<?= site_url('produk/receipt/'.rawurlencode($order->nomor)) ?>"
               rel="noopener">
              <i class="mdi mdi-printer"></i> Struk
            </a>
          </div>
        <?php endif; ?>


      </div><!-- /col kanan -->
    </div><!-- /row -->
  </div><!-- /card -->

  <!-- Items -->
  <div class="card card-body">
    <div class="section-title mb-3">Daftar Item</div>

    <?php if (empty($items)): ?>
      <div class="text-dark">Tidak ada item.</div>
    <?php else: ?>
      <!-- Tabel desktop -->
      <div class="table-responsive d-none d-md-block">
        <table class="table table-centered table-striped mb-0">
          <thead class="thead-light">
            <tr>
              <th style="width:64px">Gambar</th>
              <th>Produk</th>
              <th class="text-center" style="width:120px">Qty</th>
              <th class="text-right"  style="width:140px">Harga</th>
              <th class="text-right"  style="width:160px">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($items as $it):
              $img   = !empty($it->gambar) ? (strpos($it->gambar,'http')===0 ? $it->gambar : base_url($it->gambar)) : base_url('assets/images/icon_app.png');
              $nama  = $it->nama ?? $it->produk_nama ?? '-';
              $harga = (int)($it->harga ?? 0);
              $qty   = (int)($it->qty ?? 0);
              $sub   = $harga * $qty;
            ?>
            <tr>
              <td><img src="<?= $img ?>" class="img-fluid rounded" style="width:48px;height:48px;object-fit:cover" alt=""></td>
              <td>
                <?= html_escape($nama) ?>
                <?php if (!empty($it->tambahan) && (int)$it->tambahan === 1): ?>
                  <span class="badge badge-warning ml-1">Tambahan</span>
                <?php endif; ?>
              </td>
              <td class="text-center"><?= $qty ?></td>
              <td class="text-right">Rp <?= number_format($harga,0,',','.') ?></td>
              <td class="text-right">Rp <?= number_format($sub,0,',','.') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4" class="text-right">Total</td>
              <td class="text-right">Rp <?= number_format((int)$total,0,',','.') ?></td>
            </tr>
            <?php if ($has_voucher): ?>
              <tr>
                <td colspan="4" class="text-right">
                  Voucher<?php if ($voucher_code): ?> (<?= html_escape($voucher_code) ?>)<?php endif; ?>
                </td>
                <td class="text-right">- <?= number_format($voucher_disc,0,',','.') ?></td>
              </tr>
            <?php endif; ?>
            <?php if ($is_delivery && $delivery_fee > 0): ?>
            <tr>
              <td colspan="4" class="text-right">Ongkir</td>
              <td class="text-right">+ <?= number_format((int)$delivery_fee,0,',','.') ?></td>
            </tr>
            <?php endif; ?>

            <?php if ((int)$kode_unik > 0): ?>
            <tr>
              <td colspan="4" class="text-right">Kode Unik</td>
              <td class="text-right">+ <?= number_format((int)$kode_unik,0,',','.') ?></td>
            </tr>
            <?php endif; ?>

            <tr>
              <th colspan="4" class="text-right">Total Bayar</th>
              <th class="text-right">Rp <?= number_format((int)$grand_total,0,',','.') ?></th>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Kartu mobile -->
      <div class="d-md-none">
        <?php foreach($items as $it):
          $img   = !empty($it->gambar) ? (strpos($it->gambar,'http')===0 ? $it->gambar : base_url($it->gambar)) : base_url('assets/images/icon_app.png');
          $nama  = $it->nama ?? $it->produk_nama ?? '-';
          $harga = (int)($it->harga ?? 0);
          $qty   = (int)($it->qty ?? 0);
          $sub   = $harga * $qty;
        ?>
        <div class="media p-2 border rounded mb-2">
          <img src="<?= $img ?>" class="mr-3 rounded" style="width:56px;height:56px;object-fit:cover" alt="">
          <div class="media-body">
            <div class="font-weight-600">
              <?= html_escape($nama) ?>
              <?php if (!empty($it->tambahan) && (int)$it->tambahan === 1): ?>
                <span class="badge badge-warning align-middle ml-1">Tambahan</span>
              <?php endif; ?>
            </div>
            <div class="small text-dark mb-1">Harga: Rp <?= number_format($harga,0,',','.') ?></div>
            <div class="d-flex justify-content-between align-items-center">
              <div class="small">Qty: <strong><?= $qty ?></strong></div>
              <div class="small">Subtotal: <strong>Rp <?= number_format($sub,0,',','.') ?></strong></div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>

        <div class="d-flex justify-content-between align-items-center border-top pt-2">
          <div class="font-weight-600">Total</div>
          <div class="font-weight-700">Rp <?= number_format((int)$total,0,',','.') ?></div>
        </div>
        <?php if ($has_voucher): ?>
          <div class="d-flex justify-content-between align-items-center border-top pt-2">
            <div class="font-weight-600">
              Voucher<?php if ($voucher_code): ?> (<?= html_escape($voucher_code) ?>)<?php endif; ?>
            </div>
            <div class="font-weight-700">- <?= number_format($voucher_disc,0,',','.') ?></div>
          </div>
        <?php endif; ?>
        <?php if ($is_delivery && $delivery_fee > 0): ?>
        <div class="d-flex justify-content-between align-items-center border-top pt-2">
          <div class="font-weight-600">Ongkir</div>
          <div class="font-weight-700">+ <?= number_format((int)$delivery_fee,0,',','.') ?></div>
        </div>
        <?php endif; ?>

        <?php if ((int)$kode_unik > 0): ?>
        <div class="d-flex justify-content-between align-items-center border-top pt-2">
          <div class="font-weight-600">Kode Unik</div>
          <div class="font-weight-700">+ <?= number_format((int)$kode_unik,0,',','.') ?></div>
        </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center border-top pt-2">
          <div class="font-weight-600">Total Bayar</div>
          <div class="font-weight-700">Rp <?= number_format((int)$grand_total,0,',','.') ?></div>
        </div>
      </div>
    <?php endif; ?>
  </div>

</div>

<script src="<?php echo base_url('assets/admin') ?>/js/vendor.min.js"></script>
<script src="<?php echo base_url('assets/admin') ?>/js/app.min.js"></script>

<script>
/* ===== Konfirmasi pilih metode pembayaran (gaul) =====
   - Intersep klik anchor .js-pay
   - Tampilkan Swal konfirmasi
   - Kalau setuju -> redirect ke href asal
*/
(function(){
  function methodLabel(m){
    m = (m||'').toLowerCase();
    if (m === 'cash') return 'Tunai di Kasir ?';
    if (m === 'qris') return 'QRIS';
    if (m === 'transfer') return 'Transfer Bank';
    return m.toUpperCase();
  }
  function methodEmoji(m){
    m = (m||'').toLowerCase();
    if (m === 'cash') return '💵';
    if (m === 'qris') return '🧾';
    if (m === 'transfer') return '🏦';
    return '💳';
  }

  $(document).on('click', 'a.js-pay', function(e){
    e.preventDefault();
    var href = this.getAttribute('href');
    var m    = this.getAttribute('data-method') || '';
    var label = methodLabel(m);
    var emo   = methodEmoji(m);

    if (typeof Swal === 'undefined'){
      // fallback tanpa Swal
      if (confirm('Yakin pilih metode: ' + label + ' ?')) {
        window.location.href = href;
      }
      return;
    }

    Swal.fire({
      title: emo + ' Lanjut pakai ' + label + '?',
      html: `
        <div style="text-align:left;line-height:1.5">
          Pilihan pembayaran kamu akan dikunci. <br>
          <small class="text-muted">Kalau salah pilih, bilang admin/kasir ya ✋</small>
        </div>
      `,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Gaskeun 💨',
      cancelButtonText: 'Nanti dulu…',
      reverseButtons: true,
      allowOutsideClick: false
    }).then(function(res){
      if (res.isConfirmed) {
        Swal.fire({
          title: 'Sip, lagi disiapin…',
          didOpen: () => Swal.showLoading(),
          allowOutsideClick: false,
          showConfirmButton: false
        });
        window.location.href = href;
      }
    });
  });
})();
</script>

<?php if (!in_array($status, ['paid','canceled'], true)): ?>
<script>
(function(){
  // Gunakan nomor pesanan agar konsisten dengan endpoint lain
  var ORDER_REF = "<?= isset($order->nomor) ? rawurlencode($order->nomor) : (string)($order->kode ?? $order->id ?? '') ?>";
  if (!ORDER_REF) return;

  // Hanya relevan untuk delivery + simpan fee awal dari server
  const IS_DELIVERY = <?= $is_delivery ? 'true' : 'false' ?>;
  let lastFee = <?= (int)($delivery_fee ?? 0) ?>;

  var POLL_MS  = 7000;            // cek tiap 7 detik
  var MAX_MS   = 30*60*1000;      // safety stop 30 menit
  var startTs  = Date.now();
  var t        = null;

  function stop(){ if (t) { clearTimeout(t); t = null; } }

  async function check(){
    if (Date.now() - startTs > MAX_MS) { stop(); return; }

    try {
      const url = "<?= site_url('produk/order_status/') ?>" + ORDER_REF + "?t=" + Date.now();
      const res = await fetch(url, { cache:"no-store", headers:{ "Accept":"application/json" }});
      if (!res.ok) throw new Error("HTTP " + res.status);
      const js = await res.json();
      if (!js || !js.success) throw new Error(js && js.error ? js.error : "unknown");

      // (A) Jika delivery fee baru muncul (0 -> >0), reload halaman agar tombol/metode bayar tampil
      if (IS_DELIVERY) {
        const feeNow = Number(js.delivery_fee || 0); // ganti key ini kalau endpoint pakai nama lain
        if (lastFee === 0 && feeNow > 0) {
          location.reload();
          return;
        }
      }

      // (B) Jika status berubah, redirect instan
      var st = (js.status||"").toLowerCase();
      if (st === "paid") {
        stop();
        // 👉 langsung ke halaman struk (lebih mulus)
        window.location.href = "<?= site_url('produk/receipt/') ?>" + encodeURIComponent(js.nomor || "<?= (string)($order->nomor ?? $order->id) ?>");
        return;
      }
      if (st === "canceled") {
        stop();
        // 👉 tampilkan halaman sukses (render status canceled)
        window.location.href = "<?= site_url('produk/order_success/') ?>" + encodeURIComponent(js.nomor || "<?= (string)($order->nomor ?? $order->id) ?>");
        return;
      }

      // jika mode delivery dan ongkir sudah muncul, cukup reload halaman (UI akan menampilkan tombol bayar)

      // lanjut polling
      t = setTimeout(check, POLL_MS);
    } catch (err) {
      console.warn("order_status poll error:", err);
      // lambatkan sedikit kalau error jaringan
      t = setTimeout(check, POLL_MS * 2);
    }
  }

  // Poll hanya saat tab aktif supaya hemat
  function onVis(){ if (document.visibilityState === "visible") { if (!t) check(); } else { stop(); } }
  document.addEventListener("visibilitychange", onVis);

  if (document.visibilityState === "visible") check();
})();
</script>
<?php endif; ?>


<?php $this->load->view("front_end/footer.php"); ?>
