<?php
/** @var array $this -> view payload dari controller:
 *  $is_update(bool), $kode,$nama,$instansi_asal,$unit_tujuan,$nama_petugas_instansi,
 *  $tanggal (d-m-Y), $jam, $keperluan, $redirect_url, $pdf_url, $qr_url, $app_name
 */
$esc = function($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
$is_update = !empty($is_update);
$app = $app_name ?? 'Aplikasi';
$badge = $is_update ? 'Perubahan Booking' : 'Konfirmasi Booking';
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title><?= $esc($badge) ?> – <?= $esc($app) ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  /* gaya inline-friendly utk email client */
  body{margin:0;background:#f5f7fb;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#111}
  .wrap{width:100%;padding:24px 0;}
  .container{max-width:620px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;
    box-shadow:0 4px 18px rgba(0,0,0,.06)}
  .header{padding:18px 24px;background:#111;color:#fff}
  .header h1{margin:0;font-size:18px;letter-spacing:.3px}
  .badge{display:inline-block;margin-top:8px;background:#0ea5e9;color:#fff;padding:4px 10px;border-radius:999px;font-size:12px}
  .content{padding:24px}
  .lead{font-size:16px;line-height:1.5;margin:0 0 16px}
  .kv{width:100%;border-collapse:collapse;margin:16px 0}
  .kv td{padding:8px 0;vertical-align:top;border-bottom:1px solid #eee;font-size:14px}
  .kv td.k{width:38%;color:#555}
  .kv td.v{width:62%;color:#111;font-weight:600}
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
        <span class="badge"><?= $esc($badge) ?></span>
      </div>

      <div class="content">
        <p class="lead">
          Halo <strong><?= $esc($nama ?? '-') ?></strong>,<br>
          <?= $is_update
              ? 'Anda telah melakukan <strong>perubahan</strong> data booking kunjungan. Berikut detail terbarunya:'
              : 'Pengajuan kunjungan Anda telah <strong>BERHASIL</strong> didaftarkan. Berikut detailnya:' ?>
        </p>

        <table class="kv" role="presentation" cellpadding="0" cellspacing="0">
          <tr><td class="k">Kode Booking</td><td class="v"><?= $esc($kode ?? '-') ?></td></tr>
          <tr><td class="k">Nama Tamu</td><td class="v"><?= $esc($nama ?? '-') ?></td></tr>
          <tr><td class="k">Instansi Asal</td><td class="v"><?= $esc($instansi_asal ?? '-') ?></td></tr>
          <tr><td class="k">Unit Tujuan</td><td class="v"><?= $esc($unit_tujuan ?? '-') ?></td></tr>
          <tr><td class="k">Pejabat Unit</td><td class="v"><?= $esc($nama_petugas_instansi ?? '-') ?></td></tr>
          <tr><td class="k">Tanggal</td><td class="v"><?= $esc($tanggal ?? '-') ?></td></tr>
          <tr><td class="k">Jam</td><td class="v"><?= $esc($jam ?? '-') ?></td></tr>
          <tr><td class="k">Keperluan</td><td class="v"><?= $esc($keperluan ?? '-') ?></td></tr>
        </table>

        <?php if (!empty($qr_url)): ?>
        <div class="qr">
          <img src="<?= $esc($qr_url) ?>" alt="QR Kode Booking">
          <div class="note">Tunjukkan kode/QR ini ke petugas saat kunjungan.</div>
        </div>
        <?php endif; ?>

        <div class="cta">
          <?php if (!empty($redirect_url)): ?>
            <a class="btn" href="<?= $esc($redirect_url) ?>" target="_blank" rel="noopener">Lihat Detail Booking</a>
          <?php endif; ?>
          <?php if (!empty($pdf_url)): ?>
            <a class="btn" href="<?= $esc($pdf_url) ?>" target="_blank" rel="noopener">Download Kode (PDF)</a>
          <?php endif; ?>
        </div>

        <p class="note">
          Simpan kontak kami agar tautan di email dapat diklik langsung. Email ini dikirim otomatis oleh sistem <?= $esc($app) ?>.
        </p>
      </div>

      <div class="footer">
        &copy; <?= date('Y') ?> <?= $esc($app) ?>. Mohon jangan membalas email ini.
      </div>
    </div>
  </div>
</body>
</html>
