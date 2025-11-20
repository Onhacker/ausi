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

?>
<style>
  .card-order{ overflow:hidden; }
  .card-order .header{
    display:flex; gap:12px; align-items:flex-start; justify-content:space-between;
    border-bottom:1px dashed rgba(0,0,0,.08); padding-bottom:.5rem; margin-bottom:.75rem;
  }
  .card-order .meta{
    display:flex; flex-wrap:wrap; gap:8px 16px; align-items:center;
  }
  .card-order .meta .pill{
    display:inline-flex; align-items:center; gap:6px;
    background:#f7f7f9; border:1px solid rgba(0,0,0,.06);
    padding:4px 8px; border-radius:999px; font-size:.85rem;
  }
  .card-order .section-title{
    font-weight:600; margin:.25rem 0 .35rem; color:#111827; font-size:.95rem;
  }
  .kv{ display:flex; gap:8px; flex-wrap:wrap; }
  .kv .k{ min-width:92px; color:#6b7280; }
  .kv .v{ font-weight:600; color:#111827; }
  @media (max-width: 575.98px){
    .card-order .header{ flex-direction:column; align-items:flex-start; }
  }
  .table-items thead th{ background:#f8fafc; border-top:none; }
  .total-row th{ border-top:2px solid #e5e7eb !important; }

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
</style>

<div class="card card-body card-order">
  <!-- HEADER RINGKAS -->
  <div class="header">
    <div>
      <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
        <span class="badge badge-pill <?= $modeBadgeClass ?> mr-2">
          <?= htmlspecialchars(ucfirst($order->mode ?? '-'), ENT_QUOTES, 'UTF-8'); ?>
        </span>
        <h5 class="mb-0">Order #<?= htmlspecialchars($order->nomor ?? $order->id, ENT_QUOTES,'UTF-8'); ?></h5>
      </div>

      <div class="meta">
        <span class="pill">
          <i class="fe-clock"></i>
          <?= htmlspecialchars(date('d-m-Y H:i', strtotime($order->created_at)), ENT_QUOTES,'UTF-8'); ?>
        </span>
        <span class="pill">
          <i class="fe-credit-card"></i>
          <?= htmlspecialchars($pm_raw ?: '-', ENT_QUOTES,'UTF-8'); ?>
        </span>

        <span class="pill">
          <i class="fe-flag"></i>
          <span class="badge badge-<?= $statusBadge ?> mb-0" style="font-size:.78rem;">
            <?= htmlspecialchars($order->status ?? '-', ENT_QUOTES,'UTF-8'); ?>
          </span>
        </span>

        <?php if ($is_dinein && $meja_label !== '—'): ?>
          <span class="pill">
            <i class="fe-layout"></i> Meja: <strong><?= htmlspecialchars($meja_label, ENT_QUOTES,'UTF-8'); ?></strong>
          </span>
        <?php endif; ?>

        <!-- KURIR -->
        <span class="pill" id="kurirMeta" <?= $hasKurir ? '' : 'style="display:none"' ?>>
          <i class="fe-user-check"></i>
          Kurir:
          <strong>
            <?php if ($kurir_telp !== ''): ?>
              <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $kurir_telp), ENT_QUOTES,'UTF-8'); ?>">
                <?= htmlspecialchars($kurir_nm, ENT_QUOTES,'UTF-8'); ?>
              </a>
            <?php else: ?>
              <?= htmlspecialchars($kurir_nm, ENT_QUOTES,'UTF-8'); ?>
            <?php endif; ?>
          </strong>
        </span>

      </div>
    </div>

    <?php if ($has_phone && !$is_paid_like): ?>
      <div class="dropdown d-inline-block">
        <button type="button" class="btn btn-sm btn-success dropdown-toggle mb-2"
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
    <?php endif; ?>

    <div class="text-right">
      <?php if ($is_delivery && $canAssignKurir): ?>
        <button type="button" class="btn btn-sm btn-success mb-2" id="btnAssignKurirHeader"
          onclick="openKurirModal(<?= $idForPrint ?>)">
          <i class="fe-send"></i> Tugaskan Kurir
        </button><br>
      <?php endif; ?>

      <button type="button" class="btn btn-sm btn-outline-secondary"
        onclick="printStrukInline(<?= $idForPrint ?>, '80', true, true)">
        <i class="fe-printer"></i> Cetak
      </button>
    </div>
  </div>

  <!-- DETAIL PELANGGAN -->
  <?php 
    $show_detail = ($customer_name !== '' || $has_phone || $is_delivery || $catatan !== '' || (!$has_phone && $pm_raw !== ''));
    if ($show_detail):
  ?>
    <div class="mb-3">
      <div class="section-title">Detail Pelanggan</div>
      <div class="row">
        <div class="col-md-6">
          <?php if ($customer_name !== ''): ?>
            <div class="kv mb-1">
              <div class="k">Nama</div>
              <div class="v"><?= htmlspecialchars($customer_name, ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
          <?php endif; ?>

          <?php if ($has_phone): ?>
            <div class="kv mb-1 align-items-center">
              <div class="k">HP</div>
              <div class="v d-flex align-items-center">
                <a class="mr-2" href="tel:<?= htmlspecialchars($phone_plain, ENT_QUOTES,'UTF-8'); ?>">
                  <?= htmlspecialchars($customer_phone, ENT_QUOTES, 'UTF-8'); ?>
                </a>
              </div>
            </div>
          <?php endif; ?>

          <div class="kv mb-1 align-items-center">
            <div class="k">Pembayaran</div>
            <div class="v d-flex align-items-center flex-wrap" style="gap:6px;">
              <a class="btn btn-xs btn-outline-primary"
                 href="<?= htmlspecialchars($success_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
                <i class="fe-credit-card"></i>
                <?= htmlspecialchars($pm_label, ENT_QUOTES, 'UTF-8'); ?>
              </a>

              <?php if ($is_paid_like): ?>
                <span class="badge badge-success">Sudah Lunas</span>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <?php if ($is_delivery): ?>
            <div class="kv mb-1">
              <div class="k">Alamat Kirim</div>
              <div class="v" style="font-weight:500;">
                <?= nl2br(htmlspecialchars($alamat_kirim !== '' ? $alamat_kirim : '-', ENT_QUOTES, 'UTF-8')); ?>
              </div>
            </div>

            <?php if ($has_coord || $distance_km !== null): ?>
              <div class="kv mb-1">
                <div class="k">Lokasi</div>
                <div class="v">
                  <?php if ($distance_km !== null): ?>
                    <div class="mb-1">
                      Jarak estimasi:
                      <strong><?= number_format($distance_km, 1, ',', '.'); ?> km</strong>
                      <small class="text-muted">
                        (± <?= number_format($distance_m, 0, ',', '.'); ?> m)
                      </small>
                    </div>
                  <?php endif; ?>

                  <?php if ($has_coord && $maps_url): ?>
                    <div class="mb-1 small">
                      Koordinat:
                      <a href="<?= htmlspecialchars($maps_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
                        <?= htmlspecialchars($dest_lat . ', ' . $dest_lng, ENT_QUOTES, 'UTF-8'); ?>
                        <i class="fe-external-link"></i>
                      </a>
                    </div>

                    <?php if ($maps_qr_url): ?>
                      <div class="mt-1">
                        <div class="small text-muted mb-1">
                          Scan QR untuk buka Google Maps:
                        </div>
                        <img
                          src="<?= htmlspecialchars($maps_qr_url, ENT_QUOTES, 'UTF-8'); ?>"
                          alt="QR Google Maps"
                          class="qr-maps-img"
                        >
                      </div>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>
              </div>
            <?php endif; ?>
          <?php endif; ?>

          <?php if ($catatan !== ''): ?>
            <div class="kv mb-0">
              <div class="k">Catatan</div>
              <div class="v"><?= nl2br(htmlspecialchars($catatan, ENT_QUOTES, 'UTF-8')); ?></div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- ITEM + TOTAL -->
  <div class="table-responsive">
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
          var html = ' <i class="fe-user-check"></i> Kurir: <strong>';
          if (telp){
            html += '<a href="tel:'+telpPlain+'">'+$('<div>').text(nama).html()+'</a>';
          } else {
            html += $('<div>').text(nama).html();
          }
          html += '</strong>';
          $('#kurirMeta').html(html).show();

          $('#btnAssignKurirHeader').remove();
          $('#modalAssignKurir').modal('hide');
        } else {
          alert(res && res.msg ? res.msg : 'Gagal menugaskan kurir.');
        }
      },
      error: function(){
        alert('Terjadi kesalahan jaringan/server.');
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
</script>
