<?php
/**
 * Template email KHUSUS ORDER (tanpa booking).
 *
 * Kebutuhan variabel:
 *  - $app_name   : string nama aplikasi/toko
 *  - $order      : object pesanan (kolom: nomor, nama, email, customer_phone, mode, alamat_kirim,
 *                  total, delivery_fee, kode_unik, grand_total, created_at, meja_nama, meja_kode)
 *  - $items      : array objek item (nama, qty, harga, subtotal)
 *  - $redirect_url (opsional): url detail/status pesanan
 *  - $pdf_url      (opsional): url struk/pdf
 *  - $qr_url       (opsional): url QR (jika ada)
 */
$esc    = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$app    = $app_name ?? 'Ausi Billiard & Café';
$o      = $order ?? (object)[];
$mode   = strtolower((string)($o->mode ?? ''));
$isDel  = ($mode === 'delivery');
$isDi   = ($mode === 'dinein');
$modeTx = $isDel ? 'Delivery' : ($isDi ? 'Dine-in' : 'Walk-in');

$telp   = $o->customer_phone ?? '';
$email  = $o->email ?? '';
$alamat = $isDel ? ($o->alamat_kirim ?? '') : '';

$createdAt = !empty($o->created_at) ? strtotime($o->created_at) : time();
$tgl = date('d-m-Y', $createdAt);
$jam = date('H:i',    $createdAt);

$subtotal    = (int)($o->total ?? 0);
$ongkir      = (int)($o->delivery_fee ?? 0);
$kode_unik   = (int)($o->kode_unik ?? 0);
$grand_total = (int)($o->grand_total ?? ($subtotal + max(0,$ongkir) + $kode_unik));

$rupiah = function($n){ $n=(int)$n; return 'Rp '.number_format($n,0,',','.'); };
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title><?= $esc('Konfirmasi Pesanan – '.$app) ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  body{margin:0;background:#f5f7fb;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#111}
  .wrap{width:100%;padding:24px 0}
  .container{max-width:620px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 18px rgba(0,0,0,.06)}
  .header{padding:18px 24px;background:#111;color:#fff}
  .header h1{margin:0;font-size:18px;letter-spacing:.3px}
  .badge{display:inline-block;margin-top:8px;background:#0ea5e9;color:#fff;padding:4px 10px;border-radius:999px;font-size:12px}
  .content{padding:24px}
  .lead{font-size:16px;line-height:1.5;margin:0 0 16px}
  .kv{width:100%;border-collapse:collapse;margin:16px 0}
  .kv td{padding:8px 0;vertical-align:top;border-bottom:1px solid #eee;font-size:14px}
  .kv td.k{width:38%;color:#555}
  .kv td.v{width:62%;color:#111;font-weight:600}
  .items{width:100%;border-collapse:collapse;margin-top:8px}
  .items th,.items td{font-size:13px;padding:8px;border-bottom:1px solid #eee;text-align:left}
  .items th{text-transform:uppercase;letter-spacing:.02em;background:#fafafa}
  .items td.r{text-align:right;font-weight:600}
  .cta{margin:24px 0 8px}
  .btn{display:inline-block;text-decoration:none;background:#111;color:#fff;padding:12px 18px;border-radius:10px;font-weight:600}
  .btn + .btn{margin-left:10px}
  .note{font-size:12px;color:#666;margin-top:16px}
  .qr{margin:24px auto 8px;text-align:center}
  .qr img{max-width:180px;height:auto;border:8px solid #f5f7fb;border-radius:12px}
  .footer{padding:14px 24px;color:#777;background:#fafafa;font-size:12px}
  @media (max-width:520px){ .btn{display:block;margin:10px 0} .kv td.k{width:45%} .kv td.v{width:55%} }
</style>
</head>
<body>
  <div class="wrap">
    <div class="container">
      <div class="header">
        <h1><?= $esc($app) ?></h1>
        <span class="badge">Konfirmasi Pesanan</span>
      </div>

      <div class="content">
        <p class="lead">
          Halo <strong><?= $esc($o->nama ?? '-') ?></strong>,<br>
          Pesanan Anda telah <strong>BERHASIL</strong> dibuat. Berikut ringkasannya:
        </p>

        <table class="kv" role="presentation" cellpadding="0" cellspacing="0">
          <tr><td class="k">Nomor Pesanan</td><td class="v"><?= $esc($o->nomor ?? '-') ?></td></tr>
          <tr><td class="k">Tanggal</td><td class="v"><?= $esc($tgl) ?> <?= $esc($jam) ?></td></tr>
          <tr><td class="k">Mode</td><td class="v"><?= $esc($modeTx) ?></td></tr>
          <?php if ($isDi): ?>
            <tr><td class="k">Tempat</td><td class="v">
              <?php
                $m = trim((string)($o->meja_nama ?? $o->meja_kode ?? '-'));
                echo $esc($m !== '' ? $m : '-');
              ?>
            </td></tr>
          <?php endif; ?>
          <tr><td class="k">Atas Nama</td><td class="v"><?= $esc($o->nama ?? '-') ?></td></tr>
          <?php if (!empty($telp)): ?>
            <tr><td class="k">Telepon</td><td class="v"><?= $esc($telp) ?></td></tr>
          <?php endif; ?>
          <?php if (!empty($email)): ?>
            <tr><td class="k">Email</td><td class="v"><?= $esc($email) ?></td></tr>
          <?php endif; ?>
          <?php if ($isDel && !empty($alamat)): ?>
            <tr><td class="k">Alamat</td><td class="v"><?= $esc($alamat) ?></td></tr>
          <?php endif; ?>
          <?php if (!empty($o->catatan)): ?>
            <tr><td class="k">Catatan</td><td class="v"><?= $esc($o->catatan) ?></td></tr>
          <?php endif; ?>
        </table>

        <?php if (!empty($items) && is_array($items)): ?>
          <table class="items" role="presentation" cellpadding="0" cellspacing="0">
            <thead>
              <tr><th>Produk</th><th>Qty</th><th class="r">Harga</th><th class="r">Subtotal</th></tr>
            </thead>
            <tbody>
              <?php foreach($items as $it): ?>
                <tr>
                  <td><?= $esc($it->nama ?? '-') ?></td>
                  <td><?= (int)($it->qty ?? 0) ?></td>
                  <td class="r"><?= $esc($rupiah($it->harga ?? 0)) ?></td>
                  <td class="r"><?= $esc($rupiah($it->subtotal ?? 0)) ?></td>
                </tr>
              <?php endforeach; ?>
              <tr><td colspan="3" class="r">Subtotal</td><td class="r"><?= $esc($rupiah($subtotal)) ?></td></tr>
              <?php if ($isDel): ?>
                <tr><td colspan="3" class="r">Ongkir</td><td class="r"><?= $esc($ongkir==1 ? 'GRATIS' : $rupiah($ongkir)) ?></td></tr>
              <?php endif; ?>
              <tr><td colspan="3" class="r">Kode Unik</td><td class="r"><?= $esc($rupiah($kode_unik)) ?></td></tr>
              <tr><td colspan="3" class="r" style="font-weight:800">Total Bayar</td><td class="r" style="font-weight:800"><?= $esc($rupiah($grand_total)) ?></td></tr>
            </tbody>
          </table>
        <?php endif; ?>

        <?php if (!empty($qr_url)): ?>
          <div class="qr">
            <img src="<?= $esc($qr_url) ?>" alt="QR Pesanan">
            <div class="note">Tunjukkan kode/QR ini saat pengambilan atau kepada kurir.</div>
          </div>
        <?php endif; ?>

        <div class="cta">
          <?php if (!empty($redirect_url)): ?>
            <a class="btn" href="<?= $esc($redirect_url) ?>" target="_blank" rel="noopener">Lihat Status Pesanan</a>
          <?php endif; ?>
          <?php if (!empty($pdf_url)): ?>
            <a class="btn" href="<?= $esc($pdf_url) ?>" target="_blank" rel="noopener">Unduh Struk (PDF)</a>
          <?php endif; ?>
        </div>

        <p class="note">Email ini dikirim otomatis oleh sistem <?= $esc($app) ?>. Mohon simpan email ini sebagai bukti pesanan Anda.</p>
      </div>

      <div class="footer">
        &copy; <?= date('Y') ?> <?= $esc($app) ?>. Mohon jangan membalas email ini.
      </div>
    </div>
  </div>
</body>
</html>
