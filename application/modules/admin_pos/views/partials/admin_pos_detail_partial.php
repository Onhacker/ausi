<?php
$order = $order ?? null;
$items = $items ?? [];
$total = (int)($total ?? 0);

$kode_unik = (int)($order->kode_unik ?? 0);
$grand     = isset($order->grand_total) ? (int)$order->grand_total : ($total + $kode_unik);

// flags & delivery info
$mode            = strtolower($order->mode ?? '-');
$is_delivery     = ($mode === 'delivery');
$is_dinein       = ($mode === 'dinein' || $mode === 'dine-in');
$delivery_fee    = (int)($order->delivery_fee ?? 0);
$customer_name   = trim((string)($order->nama ?? ''));
$alamat_kirim    = trim((string)($order->alamat_kirim ?? ''));
$meja_label      = ($order->meja_nama ?? ($order->meja_kode ?? '—'));

$paid_method     = trim((string)($order->paid_method ?? ''));
$canUpdateOngkir = ($is_delivery && $paid_method === '' && $delivery_fee <= 0);

// NEW: ambil catatan (fallback ke note jika field catatan kosong)
$catatan         = trim((string)($order->catatan ?? $order->note ?? ''));

// ==== Lokasi delivery (lat/lng + jarak) ====
$dest_lat  = isset($order->dest_lat) ? (float)$order->dest_lat : null;
$dest_lng  = isset($order->dest_lng) ? (float)$order->dest_lng : null;
$has_coord = ($dest_lat !== null && $dest_lat != 0 && $dest_lng !== null && $dest_lng != 0);

$maps_url = $has_coord
    ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($dest_lat . ',' . $dest_lng)
    : '';

$distance_m  = (isset($order->distance_m) && is_numeric($order->distance_m))
    ? (float)$order->distance_m
    : null;
$distance_km = $distance_m !== null ? ($distance_m / 1000) : null;

// QR untuk Maps (barcode yang bisa discan kurir)
$maps_qr_url = $maps_url !== ''
    ? 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . rawurlencode($maps_url)
    : '';

// ==== STATUS (sekali saja) ====
$status = strtolower($order->status ?? '-');
$statusBadge = ($status==='paid'?'success':($status==='verifikasi'?'warning':($status==='canceled'?'dark':'secondary')));
$is_paid_like = in_array($status, ['paid','lunas','selesai','completed','success'], true);

// label status biar tidak dobel "paid + Sudah Lunas"
$status_text_show  = $is_paid_like ? 'Sudah Lunas' : ($order->status ?? '-');
$statusBadge_show  = $is_paid_like ? 'success' : $statusBadge;

// hitung grand total tampil
$grand_calc = $total + ($is_delivery ? $delivery_fee : 0) + $kode_unik;
$grand_show = isset($order->grand_total) ? (int)$order->grand_total : $grand_calc;

$idForPrint = (int)($order->id ?? 0);

// badge mode
$modeBadgeClass = $is_delivery ? 'badge-warning' : ($is_dinein ? 'badge-info' : 'badge-primary');

// ==== KURIR (assigned) ====
$kurir_id   = (int)($order->courier_id ?? 0);
$kurir_nm   = trim((string)($order->courier_name ?? ''));
$kurir_telp = trim((string)($order->courier_phone ?? ''));
$hasKurir   = ($kurir_id > 0 && $kurir_nm !== '');

// ==== HP robust: ambil dari beberapa kemungkinan field ====
$customer_phone = '';
foreach (['customer_phone','phone','telp','hp','whatsapp','wa'] as $k) {
  if (isset($order->$k) && trim((string)$order->$k) !== '') {
    $customer_phone = trim((string)$order->$k);
    break;
  }
}
$phone_plain = preg_replace('/\D+/', '', $customer_phone);
$placeholders = ['-','0','000000','n/a','na'];
if ($phone_plain === '' || in_array(strtolower($customer_phone), $placeholders, true)) {
  $customer_phone = '';
  $phone_plain = '';
}
$has_phone = (strlen($phone_plain) >= 6);

// ==== deteksi metode bayar ====
$pm_raw = trim((string)($order->paid_method ?? ''));
$s = strtolower($pm_raw);
$tokens = [];


// dukung JSON string/array
if ($s !== '' && ($s[0] === '[' || $s[0] === '{')) {
  $tmp = json_decode($pm_raw, true);
  if (is_array($tmp)) {
    foreach ($tmp as $v) {
      if (is_array($v)) { $v = implode(' ', $v); }
      $tokens[] = strtolower(trim((string)$v));
    }
  }
}
if (!$tokens) {
  $tokens = preg_split('/[\s,\/\+\|\-]+/', $s, -1, PREG_SPLIT_NO_EMPTY);
}
// ==== QRIS BARCODE (tampil hanya jika qris + verifikasi) ====
$is_verifikasi = ($status === 'verifikasi');

// deteksi qris (setelah tokens terbentuk)
$is_qris = in_array('qris', $tokens, true) || (strpos($s, 'qris') !== false);

// cari file barcode (utama: assets/uploads/qris/order_1675.png)
$qris_rel = '';
$qris_abs = '';

if ($is_qris && $is_verifikasi) {
  $cands = [
    'uploads/qris/order_' . $idForPrint . '.png', // sesuai permintaan
    'uploads/qris/' . $idForPrint . '.png',       // fallback
  ];

  foreach ($cands as $rel) {
    $abs = FCPATH . ltrim($rel, '/');
    if (is_file($abs)) { $qris_rel = $rel; $qris_abs = $abs; break; }
  }
}

$show_qris_barcode = ($qris_rel !== '');
$qris_src = $show_qris_barcode
  ? base_url($qris_rel) . '?v=' . @filemtime($qris_abs)
  : '';
$qris_download_url = $show_qris_barcode ? base_url($qris_rel) : '';
$qris_download_name = 'qris_order_' . $idForPrint . '.png';

$cashSyn  = ['cash','tunai','cod','bayar_ditempat','bayarditempat'];
$digSyn   = ['transfer','tf','bank','qris','qr','qr-code','gopay','ovo','dana','shopeepay','mbanking','va','virtualaccount'];

$hasCash    = (bool)array_intersect($tokens, $cashSyn);
$hasDigital = (bool)array_intersect($tokens, $digSyn);

// kondisi tombol assign kurir
$canAssignKurir = ($is_delivery && !$hasKurir);

// Link order_success
$order_code  = trim((string)($order->nomor ?? $order->kode ?? $order->order_code ?? $order->order_key ?? $order->id ?? ''));
$success_url = $order_code !== '' ? site_url('produk/order_success/'.$order_code) : site_url('produk/order_success');
$pm_label    = 'Lihat Order';

// DETAIL kanan hanya jika ada info customer/delivery/catatan (biar tidak dobel2 kosong)
$show_detail = ($customer_name !== '' || $has_phone || $is_delivery || $catatan !== '');
?>

<style>
  .card-order{ overflow:hidden; }
  .table-items thead th{ background:#f8fafc; border-top:none; }
  .total-row th{ border-top:2px solid #e5e7eb !important; }

  /* Box + table */
  .info-box{
    border:1px solid #e5e7eb;
    background:#fff;
    border-radius:10px;
    padding:.5rem .75rem;
    margin-bottom:.75rem;
  }
  .info-table{
    width:100%;
    border-collapse:separate;
    border-spacing:0 4px;
  }
  .info-table th{
    width:120px;
    font-size:.82rem;
    font-weight:600;
    color:#6b7280;
    padding:.15rem .25rem;
    white-space:nowrap;
    vertical-align:top;
  }
  .info-table td{
    font-size:.9rem;
    padding:.15rem .25rem;
    color:#111827;
  }

  .section-title{
    font-weight:700; margin:.25rem 0 .5rem; color:#111827; font-size:.98rem;
  }

  /* ===== Ringkasan (tabel) ===== */
  .order-summary{
    width:100%;
    border-collapse:separate;
    border-spacing:0 6px;
  }
  .order-summary th{
    width:120px;
    font-size:.82rem;
    font-weight:700;
    color:#6b7280;
    padding:.2rem .25rem;
    white-space:nowrap;
    vertical-align:top;
  }
  .order-summary td{
    font-size:.92rem;
    padding:.2rem .25rem;
    color:#111827;
    font-weight:600;
  }

  /* Actions di ringkasan */
  .summary-actions{
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    justify-content:flex-end;
    margin-top:.5rem;
  }
  .summary-actions .btn,
  .summary-actions .dropdown,
  .summary-actions .dropdown-toggle{
    width:100%;
  }
  @media (min-width:576px){
    .summary-actions .btn,
    .summary-actions .dropdown,
    .summary-actions .dropdown-toggle{
      width:auto;
    }
  }

  /* QR Maps */
  .qr-maps-img{
    max-width: 140px;
    height:auto;
    background:#fff;
    border-radius:6px;
    box-shadow:0 0 0 1px rgba(0,0,0,.06);
    padding:4px;
  }

  /* ===== Modal Assign Kurir ===== */
  #modalAssignKurir { z-index: 1062; }
  .modal-backdrop.backdrop-assign-kurir { background: rgba(22,163,74,.25); }
  #modalAssignKurir .modal-content{
    border:2px solid #16a34a; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,.25);
  }
  #modalAssignKurir .modal-header{
    background:linear-gradient(90deg,#16a34a 0%, #22c55e 100%);
    color:#fff; border-bottom:none; border-top-left-radius:12px; border-top-right-radius:12px;
  }
  #modalAssignKurir .modal-title{ font-weight:700; letter-spacing:.2px; }
  #modalAssignKurir .modal-body{ background:#f0fff4; }
  #modalAssignKurir .modal-footer{ background:#f6fffb; border-top:none; }

  #tblKurir td, #tblKurir th { vertical-align: middle; }
  #tblKurir tbody tr:hover{ background:#ecfdf5; }
  .btn-assign-row{ min-width:92px; }

  #modalAssignKurir .assign-loading{
    position:absolute; inset:0; background:rgba(255,255,255,.7);
    display:flex; align-items:center; justify-content:center; flex-direction:column;
    gap:10px; z-index:5; border-radius:12px;
  }
  #modalAssignKurir .assign-loading.d-none{ display:none; }
  .spin {
    width:26px; height:26px; border-radius:50%;
    border:3px solid rgba(34,197,94,.35); border-top-color:#16a34a; animation:spin 0.8s linear infinite;
  }
  @keyframes spin { to { transform: rotate(360deg); } }
  .qris-img{
    max-width:220px;
    height:auto;
    background:#fff;
    border-radius:8px;
    box-shadow:0 0 0 1px rgba(0,0,0,.06);
    padding:6px;
  }

</style>

<div class="card-order">

  <div class="row">
    <!-- KIRI: RINGKASAN ORDER -->
    <div class="col-lg-6 mb-3 mb-lg-0">
      <div class="section-title">Ringkasan Order</div>

      <div class="info-box mb-0">
        <table class="order-summary">
          <tbody>
            <tr>
              <th>Mode</th>
              <td>
                <span class="badge badge-pill <?= $modeBadgeClass ?>">
                  <?= htmlspecialchars(ucfirst($order->mode ?? '-'), ENT_QUOTES, 'UTF-8'); ?>
                </span>
                <?php if ($is_dinein && $meja_label !== '—'): ?>
                  <span class="ml-2 text-dark" style="font-weight:600;">
                   <?= htmlspecialchars($meja_label, ENT_QUOTES,'UTF-8'); ?>
                  </span>
                <?php endif; ?>
              </td>
            </tr>

            <tr>
              <th>No Order</th>
              <td>#<?= htmlspecialchars($order->nomor ?? $order->id, ENT_QUOTES,'UTF-8'); ?></td>
            </tr>

            <tr>
              <th>Waktu</th>
              <td><?= htmlspecialchars(date('d-m-Y H:i:s', strtotime($order->created_at)), ENT_QUOTES,'UTF-8'); ?></td>
            </tr>

            <tr>
              <th>Status</th>
              <td>
                <span class="badge badge-<?= $statusBadge_show ?>" style="font-size:.82rem;" title="<?= htmlspecialchars($order->status ?? '-', ENT_QUOTES,'UTF-8'); ?>">
                  <?= htmlspecialchars($status_text_show, ENT_QUOTES,'UTF-8'); ?>
                </span>
              </td>
            </tr>

            <tr>
              <th>Pembayaran</th>
              <td>
                <div class="d-flex align-items-center flex-wrap" style="gap:6px;">
                  <span><?= htmlspecialchars($pm_raw ?: '-', ENT_QUOTES,'UTF-8'); ?></span>
                  <a class="btn btn-xs btn-outline-primary ml-1"
                     href="<?= htmlspecialchars($success_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
                    <i class="fe-credit-card"></i> <?= htmlspecialchars($pm_label, ENT_QUOTES, 'UTF-8'); ?>
                  </a>
                </div>
              </td>
            </tr>
           <?php if ($show_qris_barcode): ?>
            <?php 
            $order_id = (int)$idForPrint; // atau (int)$order->id

// versi: pakai filemtime kalau ada, fallback time()
$v = 0;
if (!empty($qris_abs) && is_file($qris_abs)) $v = (int)@filemtime($qris_abs);
if ($v <= 0) $v = time();

// URL gambar via controller + bypass SW
$qris_src = site_url('produk/qris_png/'.$order_id).'?sw-bypass=1&v='.$v;

// untuk download / buka
$qris_download_url = $qris_src; // pakai url yang sama biar selalu fresh
 ?>
            <tr>
              <th>QRIS</th>
              <td>
                <div class="small text-muted mb-1">Scan untuk pembayaran:</div>

                <div class="d-flex flex-wrap align-items-start" style="gap:10px;">
                 <img
  id="qris-img"
  src="<?= htmlspecialchars($qris_src, ENT_QUOTES, 'UTF-8'); ?>"
  alt="QRIS"
  style="max-width: 320px; width:100%; height:auto; image-rendering: -webkit-optimize-contrast; background:#fff;"
>


                  <!-- tombol kiri-kanan -->
                  <div class="d-flex flex-wrap align-items-center" style="gap:8px;">
                    <a class="btn btn-xs btn-success"
   href="<?= htmlspecialchars($qris_download_url, ENT_QUOTES, 'UTF-8'); ?>"
   download="<?= htmlspecialchars($qris_download_name, ENT_QUOTES, 'UTF-8'); ?>">
  <i class="fe-download"></i> Download
</a>

<a class="btn btn-xs btn-outline-secondary"
   href="<?= htmlspecialchars($qris_download_url, ENT_QUOTES, 'UTF-8'); ?>"
   target="_blank" rel="noopener">
  <i class="fe-external-link"></i> Buka
</a>

                  </div>
                </div>
              </td>
            </tr>
          <?php endif; ?>



            <tr id="rowKurir" <?= $hasKurir ? '' : 'style="display:none"' ?>>
              <th>Kurir</th>
              <td id="kurirMeta">
                <?php if ($hasKurir): ?>
                  <i class="fe-user-check"></i>
                  <strong>
                    <?php if ($kurir_telp !== ''): ?>
                      <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $kurir_telp), ENT_QUOTES,'UTF-8'); ?>">
                        <?= htmlspecialchars($kurir_nm, ENT_QUOTES,'UTF-8'); ?>
                      </a>
                    <?php else: ?>
                      <?= htmlspecialchars($kurir_nm, ENT_QUOTES,'UTF-8'); ?>
                    <?php endif; ?>
                  </strong>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Tombol di Ringkasan -->
        <div class="summary-actions">
          <?php if (!$is_paid_like): ?>
            <button type="button" class="btn btn-sm btn-outline-danger"
            onclick="gantiMetodePembayaran(<?= $idForPrint ?>, this)">
            <i class="fe-refresh-cw"></i> Ganti Metode Pembayaran
          </button>

          <?php endif; ?>

         <!--  <?php if ($has_phone && !$is_paid_like): ?>
            <div class="dropdown d-inline-block">
              <button type="button" class="btn btn-sm btn-success dropdown-toggle"
                      data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="mdi mdi-whatsapp"></i> WA Pengingat
              </button>
              <div class="dropdown-menu dropdown-menu-right">
                <?php if (in_array($status, ['pending','verifikasi'], true)): ?>
                  <a class="dropdown-item" href="#"
                     onclick="waReminder(<?= $idForPrint ?>,'payment');return false;">
                    Pengingat Pembayaran
                  </a>
                <?php endif; ?>

                <?php if ($is_delivery): ?>
                  <a class="dropdown-item" href="#"
                     onclick="waReminder(<?= $idForPrint ?>,'delivery');return false;">
                    Pengingat Pengantaran
                  </a>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?> -->

          <?php if ($is_delivery && $canAssignKurir): ?>
            <button type="button" class="btn btn-sm btn-warning" id="btnAssignKurirHeader"
                    onclick="openKurirModal(<?= $idForPrint ?>)">
              <i class="mdi mdi-motorbike"></i> Tugaskan Kurir
            </button>
          <?php endif; ?>

          <button type="button" class="btn btn-sm btn-pink"
                  onclick="printStrukInline(<?= $idForPrint ?>, '80', true, true)">
            <i class="fe-printer"></i> Cetak Struk
          </button>
          <a class="btn btn-primary btn-sm"
          href="<?= site_url('produk/receipt/'.rawurlencode($idForPrint)) ?>"
          rel="noopener" target = "_blank">
          <i class="mdi mdi-file-document"></i> Lihat Struk
        </a>
        </div>
      </div>
    </div>

    <!-- KANAN: DETAIL PELANGGAN -->
    <div class="col-lg-6">
      <div class="section-title">Detail Pelanggan</div>

      <?php if ($show_detail): ?>

        <?php if ($customer_name !== '' || $has_phone): ?>
          <div class="info-box">
            <table class="info-table">
              <?php if ($customer_name !== ''): ?>
                <tr>
                  <th>Nama</th>
                  <td><?= htmlspecialchars($customer_name, ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
              <?php endif; ?>

              <?php if ($has_phone): ?>
                <tr>
                  <th>HP</th>
                  <td>
                    <a href="tel:<?= htmlspecialchars($phone_plain, ENT_QUOTES,'UTF-8'); ?>">
                      <?= htmlspecialchars($customer_phone, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                  </td>
                </tr>
              <?php endif; ?>
            </table>
          </div>
        <?php endif; ?>

        <?php if ($is_delivery): ?>
          <div class="info-box">
            <table class="info-table">
              <tr>
                <th>Alamat Kirim</th>
                <td style="font-weight:500;">
                  <?= nl2br(htmlspecialchars($alamat_kirim !== '' ? $alamat_kirim : '-', ENT_QUOTES, 'UTF-8')); ?>
                </td>
              </tr>

              <?php if ($has_coord || $distance_km !== null): ?>
                <?php if ($distance_km !== null): ?>
                  <tr>
                    <th>Jarak</th>
                    <td>
                      Jarak estimasi:
                      <strong><?= number_format($distance_km, 1, ',', '.'); ?> km</strong>
                      <small class="text-muted">(± <?= number_format($distance_m, 0, ',', '.'); ?> m)</small>
                    </td>
                  </tr>
                <?php endif; ?>

                <?php if ($has_coord && $maps_url): ?>
                  <tr>
                    <th>Lokasi</th>
                    <td>
                      <div class="mb-1 small">
                        Koordinat:
                        <a href="<?= htmlspecialchars($maps_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
                          <?= htmlspecialchars($dest_lat . ', ' . $dest_lng, ENT_QUOTES, 'UTF-8'); ?>
                          <i class="fe-external-link"></i>
                        </a>
                      </div>

                      <?php if ($maps_qr_url): ?>
                        <div class="mt-1">
                          <div class="small text-muted mb-1">Scan QR untuk buka Google Maps:</div>
                          <img
                            src="<?= htmlspecialchars($maps_qr_url, ENT_QUOTES, 'UTF-8'); ?>"
                            alt="QR Google Maps"
                            class="qr-maps-img"
                          >
                        </div>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endif; ?>
              <?php endif; ?>
            </table>
          </div>
        <?php endif; ?>

        <?php if ($catatan !== ''): ?>
          <div class="info-box mb-0">
            <table class="info-table">
              <tr>
                <th>Catatan</th>
                <td><?= nl2br(htmlspecialchars($catatan, ENT_QUOTES, 'UTF-8')); ?></td>
              </tr>
            </table>
          </div>
        <?php endif; ?>

      <?php else: ?>
        <div class="info-box mb-0">
          <div class="text-muted">—</div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ITEM + TOTAL -->
  <div class="table-responsive mt-3">
    <table class="table table-sm table-striped table-items mb-2">
      <thead>
        <tr>
          <th>Produk</th>
          <th class="text-right">Harga</th>
          <th class="text-center">Qty</th>
          <th class="text-right">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($items as $it): ?>
          <tr>
            <td><?= htmlspecialchars($it->nama ?: ('#'.$it->produk_id), ENT_QUOTES,'UTF-8'); ?></td>
            <td class="text-right">Rp <?= number_format((int)$it->harga,0,',','.'); ?></td>
            <td class="text-center"><?= (int)$it->qty; ?></td>
            <td class="text-right">Rp <?= number_format((int)$it->subtotal,0,',','.'); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <th colspan="3" class="text-right">Total</th>
          <th class="text-right">Rp <?= number_format($total,0,',','.') ?></th>
        </tr>

        <?php if ($is_delivery): ?>
          <tr>
            <th colspan="3" class="text-right">
              <div class="d-flex align-items-center justify-content-end">
                <span>Ongkir</span>
                <?php if ($canUpdateOngkir): ?>
                  <button type="button"
                          class="btn btn-xs btn-danger ml-2"
                          onclick="showSetOngkir(<?= (int)$order->id ?>, '<?= number_format($delivery_fee,0,',','.') ?>')">
                    <i class="fe-truck"></i> Update
                  </button>
                <?php endif; ?>
              </div>
            </th>
            <th class="text-right">Rp <?= number_format($delivery_fee,0,',','.') ?></th>
          </tr>
        <?php endif; ?>

        <tr>
          <th colspan="3" class="text-right">Kode Unik</th>
          <th class="text-right">Rp <?= number_format($kode_unik,0,',','.') ?></th>
        </tr>

        <tr class="total-row">
          <th colspan="3" class="text-right h3 mb-0">Total Pembayaran</th>
          <th class="text-right h3 mb-0">Rp <?= number_format($grand_show,0,',','.') ?></th>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<!-- MODAL: PILIH / TUGASKAN KURIR -->
<div class="modal fade" id="modalAssignKurir" tabindex="-1" role="dialog" aria-labelledby="assignKurirLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content position-relative">
      <div class="assign-loading d-none">
        <div class="spin"></div>
        <div class="text-success font-weight-bold">Menugaskan kurir...</div>
      </div>

      <div class="modal-header py-2">
        <h5 class="modal-title" id="assignKurirLabel">Tugaskan Kurir</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-sm table-hover" id="tblKurir">
            <thead class="thead-light">
              <tr>
                <th>Kurir</th>
                <th>Kontak</th>
                <th>Kendaraan</th>
                <th class="text-right">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($kurirs)): ?>
                <?php foreach($kurirs as $k): ?>
                  <tr data-id="<?= (int)$k->id ?>" class="row-kurir">
                    <td><strong><?= htmlspecialchars($k->nama ?? ('Kurir #'.$k->id), ENT_QUOTES, 'UTF-8') ?></strong></td>
                    <td>
                      <?php if (!empty($k->phone)): ?>
                        <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/','',$k->phone), ENT_QUOTES, 'UTF-8'); ?>">
                          <?= htmlspecialchars($k->phone, ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                      <?php else: ?>
                        <span class="text-muted">—</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?= htmlspecialchars(trim(($k->vehicle ?? '').' '.($k->plate ?? '')), ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                    <td class="text-right">
                      <button type="button" class="btn btn-xs btn-success btn-assign-row"
                        onclick="assignKurirNow(<?= $idForPrint ?>, <?= (int)$k->id ?>, this)">
                        Tugaskan
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="4" class="text-center text-muted">Belum ada data kurir.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="modal-footer py-2">
        <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  var CSRF_NAME = "<?= isset($this->security) ? $this->security->get_csrf_token_name() : 'csrf_test_name' ?>";
  var CSRF_HASH = "<?= isset($this->security) ? $this->security->get_csrf_hash() : '' ?>";

  $('#modalAssignKurir').on('show.bs.modal', function(){
    setTimeout(function(){
      $('.modal-backdrop').last().addClass('backdrop-assign-kurir');
    }, 10);
  }).on('hidden.bs.modal', function(){
    $('.modal-backdrop').removeClass('backdrop-assign-kurir');
  });

  window.openKurirModal = function(orderId){
    $('#modalAssignKurir').modal('show');
  };

  $(document).on('click', '#tblKurir .row-kurir', function(e){
    if ($(e.target).closest('button,a').length) return;
    $('#tblKurir .row-kurir').removeClass('table-success');
    $(this).addClass('table-success');
  });

  window.assignKurirSelected = function(orderId){
    alert('Pilih kurir dari tombol "Tugaskan" pada tabel.');
  };

  window.assignKurirNow = function(orderId, courierId, btn){
    var $modal   = $('#modalAssignKurir');
    var $overlay = $modal.find('.assign-loading');
    var $btn     = $(btn || null);

    $overlay.removeClass('d-none');
    var oldHtml=null;
    if ($btn.length){
      oldHtml = $btn.html();
      $btn.prop('disabled', true)
          .html('<span class="spin" style="width:16px;height:16px;border-width:2px;margin-right:6px;"></span>Memproses');
    }

    $.ajax({
      url: "<?= site_url('admin_pos/assign_courier'); ?>",
      method: "POST",
      dataType: "json",
      data: (function(){
        var d = { order_id: orderId, courier_id: courierId };
        d[CSRF_NAME] = CSRF_HASH;
        return d;
      })(),
      success: function(res){
        if (res && res.ok){
          var nama = res.data && res.data.nama ? res.data.nama : 'Kurir';
          var telp = res.data && res.data.phone ? String(res.data.phone) : '';
          var telpPlain = telp.replace(/\s+/g,'');
          var html = ' <i class="fe-user-check"></i> <strong>';
          if (telp){
            html += '<a href="tel:'+telpPlain+'">'+$('<div>').text(nama).html()+'</a>';
          } else {
            html += $('<div>').text(nama).html();
          }
          html += '</strong>';

          $('#kurirMeta').html(html).show();
          $('#rowKurir').show();

          $('#btnAssignKurirHeader').remove();
          $('#modalAssignKurir').modal('hide');

          if (typeof reload_table === 'function') {
            reload_table('assign-courier');
          }
        } else {
          alert(res && res.msg ? res.msg : 'Gagal menugaskan kurir.');
        }
      },
      error: function(xhr){
        var msg = 'Terjadi kesalahan jaringan/server.';
        if (xhr.responseJSON && xhr.responseJSON.msg){
          msg = xhr.responseJSON.msg;
        } else if (xhr.responseText) {
          try {
            var j = JSON.parse(xhr.responseText);
            if (j.msg) msg = j.msg;
          } catch(e){}
        }
        alert(msg);
      },

      complete: function(){
        $overlay.addClass('d-none');
        if ($btn.length){
          $btn.prop('disabled', false).html(oldHtml || 'Tugaskan');
        }
      }
    });
  };

  $(document).on('click', '.js-copy', function(){
    var val = $(this).data('copy') || '';
    if (!val) return;
    var ta = document.createElement('textarea');
    ta.value = val;
    document.body.appendChild(ta);
    ta.select(); document.execCommand('copy');
    document.body.removeChild(ta);
    Swal.fire({toast:true, position:'top', icon:'success', title:'Nomor tersalin', showConfirmButton:false, timer:1300});
  });
})();

window.CSRF_NAME = "<?= isset($this->security) ? $this->security->get_csrf_token_name() : 'csrf_test_name' ?>";
window.CSRF_HASH = "<?= isset($this->security) ? $this->security->get_csrf_hash() : '' ?>";

function swalLoading(title, html){
  Swal.fire({
    title: title || 'Memproses...',
    html:  html  || '',
    allowOutsideClick: false,
    allowEscapeKey:   false,
    didOpen: () => { Swal.showLoading(); }
  });
}

window.waReminder = function(orderId, type){
  var labels = { payment:'Pengingat Pembayaran', delivery:'Pengingat Pengantaran', thanks:'Ucapan Terima Kasih' };
  var label = labels[type] || 'Kirim Pesan';

  Swal.fire({
    icon: 'question', title: label, text: 'Kirim pesan WhatsApp ke customer sekarang?',
    showCancelButton: true, confirmButtonText: 'Kirim', cancelButtonText: 'Batal', reverseButtons: true
  }).then(function(res){
    if (!res.isConfirmed) return;

    swalLoading('Mengirim...', 'Mohon tunggu');
    $.ajax({
      url: "<?= site_url('admin_pos/wa_reminder'); ?>",
      method: "POST",
      dataType: "json",
      data: (function(){
        var d = { order_id: orderId, type: type };
        if (window.CSRF_NAME) d[window.CSRF_NAME] = window.CSRF_HASH;
        return d;
      })()
    })
    .done(function(r){
      if (r && r.csrf) { window.CSRF_NAME = r.csrf.name; window.CSRF_HASH = r.csrf.hash; }
      Swal.close();

      if (r && r.ok){
        Swal.fire({ icon:'success', title:'Terkirim', text:'Pesan WhatsApp berhasil dikirim.' });
        return;
      }

      if (r && r.preview_ctc){
        Swal.fire({
          icon:'info',
          title:'Kirim manual via WhatsApp',
          html: `
            <div class="text-left">
              <p>Gateway WhatsApp tidak aktif. Klik tombol di bawah untuk membuka WhatsApp dengan pesan siap kirim.</p>
              <label class="small text-muted d-block mb-1">Preview Pesan</label>
              <textarea class="form-control" rows="6" readonly>${(r.preview_text||'').replace(/</g,'&lt;')}</textarea>
            </div>
          `,
          showCancelButton:true, confirmButtonText:'Buka WhatsApp', cancelButtonText:'Tutup', width:600
        }).then(function(x){ if (x.isConfirmed) window.open(r.preview_ctc, '_blank'); });
        return;
      }

      Swal.fire({ icon:'error', title:'Gagal', text:(r && r.msg) ? r.msg : 'Gagal mengirim pesan.' });
    })
    .fail(function(){
      Swal.close();
      Swal.fire({ icon:'error', title:'Gagal', text:'Tidak dapat menghubungi server.' });
    });
  });
};
window.gantiMetodePembayaran = function(orderId, el){
  const $parentModal = el ? $(el).closest('.modal') : $();

  Swal.fire({
    icon: 'warning',
    title: 'Ganti metode pembayaran?',
    html: 'Status pesanan akan diubah menjadi <b>pending</b> dan halaman pembayaran akan dibuka kembali.',
    showCancelButton: true,
    confirmButtonText: 'Ya, ganti',
    cancelButtonText: 'Tidak',
    reverseButtons: true
  }).then(function(res){
    if (!res.isConfirmed) return;

    // ✅ pre-open tab (JANGAN pakai 'noopener' di sini)
    let win = window.open('', '_blank'); // <-- penting

    if (!win) {
      Swal.fire({ icon:'warning', title:'Popup diblokir', text:'Izinkan popup/new tab di browser, lalu coba lagi.' });
      return;
    }

    swalLoading('Memproses...', 'Mengubah metode pembayaran');

    $.ajax({
      url: "<?= site_url('admin_pos/change_payment_method'); ?>",
      method: "POST",
      dataType: "json",
      data: (function(){
        var d = { order_id: orderId };
        if (window.CSRF_NAME) d[window.CSRF_NAME] = window.CSRF_HASH;
        return d;
      })()
    })
    .done(function(r){
      if (r && r.csrf) { window.CSRF_NAME = r.csrf.name; window.CSRF_HASH = r.csrf.hash; }
      Swal.close();

      if (r && r.ok && r.redirect){
        // ✅ tutup modal
        if ($parentModal.length) $parentModal.modal('hide');
        else $('.modal.show').modal('hide');

        setTimeout(function(){
          $('.modal-backdrop').remove();
          $('body').removeClass('modal-open').css('padding-right','');
        }, 80);

        // ✅ amankan opener lalu arahkan tab baru
        try { win.opener = null; } catch(e){}
        win.location.href = r.redirect;
        win.focus();
        return;
      }

      // gagal -> tutup tab kosong
      try { win.close(); } catch(e){}
      Swal.fire({ icon:'error', title:'Gagal', text:(r && r.msg) ? r.msg : 'Gagal mengubah metode pembayaran.' });
    })
    .fail(function(){
      Swal.close();
      try { win.close(); } catch(e){}
      Swal.fire({ icon:'error', title:'Gagal', text:'Tidak dapat menghubungi server.' });
    });
  });
};

</script>
