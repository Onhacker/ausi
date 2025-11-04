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
$customer_phone  = trim((string)($order->customer_phone ?? ''));
$alamat_kirim    = trim((string)($order->alamat_kirim ?? ''));
$meja_label      = $order->meja_nama ?: ($order->meja_kode ?: '—');
$paid_method     = trim((string)($order->paid_method ?? ''));
// $canUpdateOngkir = ($is_delivery && $paid_method === '');
$canUpdateOngkir = ($is_delivery && $paid_method === '' && $delivery_fee <= 0);

// NEW: ambil catatan (fallback ke note jika field catatan kosong)
$catatan         = trim((string)($order->catatan ?? $order->note ?? ''));

// normalisasi telp utk link tel: / copy
$phone_plain = preg_replace('/\s+/', '', $customer_phone);

// hitung grand total tampil
$grand_calc = $total + ($is_delivery ? $delivery_fee : 0) + $kode_unik;
$grand_show = isset($order->grand_total) ? (int)$order->grand_total : $grand_calc;

$idForPrint = (int)($order->id ?? 0);

// badge mode
$modeBadgeClass = $is_delivery ? 'badge-warning' : ($is_dinein ? 'badge-info' : 'badge-primary');
// badge status
$status = strtolower($order->status ?? '-');
$statusBadge = ($status==='paid'?'success':($status==='verifikasi'?'warning':($status==='canceled'?'dark':'secondary')));

// ==== KURIR (assigned) ====
$kurir_id    = (int)($order->courier_id ?? 0);
$kurir_nama  = trim((string)($order->courier_name ?? ''));
$kurir_telp  = trim((string)($order->courier_phone ?? ''));
$hasKurir    = ($kurir_id > 0 && $kurir_nama !== '');
// $canAssignKurir = ($is_delivery && !in_array($status, ['canceled']) && !$hasKurir);
// $canAssignKurir = ($is_delivery && $status === 'paid' && !$hasKurir);

// ganti baris paid_method lama
$pm_raw = trim((string)($order->paid_method ?? ''));
$pm     = strtolower($pm_raw);

// ...

// ganti baris canAssignKurir
$canAssignKurir = ($is_delivery && $status === 'pending' && $pm === 'cash' && !$hasKurir);

// daftar kurir dari controller (boleh kosong)
$kurirs = $kurirs ?? [];
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

  /* ===== Modal Assign Kurir: tema beda & overlay ===== */
  #modalAssignKurir { z-index: 1062; }
  .modal-backdrop.backdrop-assign-kurir { /*z-index:1061 !important*/; background: rgba(22,163,74,.25); }
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

  /* tabel kurir */
  #tblKurir td, #tblKurir th { vertical-align: middle; }
  #tblKurir tbody tr:hover{ background:#ecfdf5; }
  .btn-assign-row{ min-width:92px; }

  /* overlay loading */
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
        <!-- <span class="pill">
          <i class="fe-credit-card"></i>
          <?= htmlspecialchars($order->paid_method ?: '-', ENT_QUOTES,'UTF-8'); ?>
        </span> -->
        <span class="pill">
          <i class="fe-credit-card"></i>
          <?= htmlspecialchars($pm_raw ?: '-', ENT_QUOTES,'UTF-8'); ?>
        </span>

        <span class="pill">
          <i class="fe-flag"></i>
          <span class="badge badge-<?= $statusBadge ?> mb-0" style="font-size:.78rem;"><?= htmlspecialchars($order->status ?? '-', ENT_QUOTES,'UTF-8'); ?></span>
        </span>

        <?php if ($is_dinein && $meja_label !== '—'): ?>
          <span class="pill">
            <i class="fe-layout"></i> Meja: <strong><?= htmlspecialchars($meja_label, ENT_QUOTES,'UTF-8'); ?></strong>
          </span>
        <?php endif; ?>

        <!-- KURIR (badge ketika sudah ditugaskan) -->
        <span class="pill" id="kurirMeta" <?= $hasKurir ? '' : 'style="display:none"' ?>>
          <i class="fe-user-check"></i>
          Kurir:
          <strong>
            <?php if ($kurir_telp !== ''): ?>
              <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $kurir_telp), ENT_QUOTES,'UTF-8'); ?>">
                <?= htmlspecialchars($kurir_nama, ENT_QUOTES,'UTF-8'); ?>
              </a>
            <?php else: ?>
              <?= htmlspecialchars($kurir_nama, ENT_QUOTES,'UTF-8'); ?>
            <?php endif; ?>
          </strong>
        </span>
      </div>
    </div>

    <div class="text-right">
      <?php if ($is_delivery): ?>
        <?php if ($canAssignKurir): ?>
          <button type="button" class="btn btn-sm btn-success mb-2" id="btnAssignKurirHeader"
            onclick="openKurirModal(<?= $idForPrint ?>)">
            <i class="fe-send"></i> Tugaskan Kurir
          </button><br>
        <?php endif; ?>
      <?php endif; ?>

      <button type="button" class="btn btn-sm btn-primary mb-1"
        onclick="printStrukInline(<?= $idForPrint ?>, '58')">
        <i class="fe-printer"></i> Cetak 58mm
      </button><br>
      <button type="button" class="btn btn-sm btn-secondary"
        onclick="printStrukInline(<?= $idForPrint ?>, '80')">
        <i class="fe-printer"></i> Cetak 80mm
      </button>
    </div>
  </div>

  <!-- DETAIL PELANGGAN -->
  <?php if ($customer_name !== '' || $customer_phone !== '' || $is_delivery || $catatan !== ''): ?>
    <div class="mb-3">
      <div class="section-title">Detail Pelanggan</div>
      <div class="row">
        <div class="col-md-6">
          <?php if ($customer_name !== ''): ?>
            <div class="kv mb-1">
              <div class="k">Nama</div><div class="v"><?= htmlspecialchars($customer_name, ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
          <?php endif; ?>

          <?php if ($customer_phone !== ''): ?>
            <div class="kv mb-1 align-items-center">
              <div class="k">HP</div>
              <div class="v d-flex align-items-center">
                <a class="mr-2" href="tel:<?= htmlspecialchars($phone_plain, ENT_QUOTES,'UTF-8'); ?>">
                  <?= htmlspecialchars($customer_phone, ENT_QUOTES, 'UTF-8'); ?>
                </a>
                <button type="button" class="btn btn-xxs btn-outline-secondary js-copy"
                        data-copy="<?= htmlspecialchars($phone_plain, ENT_QUOTES,'UTF-8'); ?>">
                  Salin
                </button>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <div class="col-md-6">
          <?php if ($is_delivery): ?>
            <div class="kv mb-1">
              <div class="k">Alamat Kirim</div>
              <div class="v" style="font-weight:500;">
                <?= nl2br(htmlspecialchars($alamat_kirim !== '' ? $alamat_kirim : '-', ENT_QUOTES, 'UTF-8')); ?>
              </div>
            </div>
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

<!-- =========================
     MODAL: PILIH / TUGASKAN KURIR
     ========================= -->
<div class="modal fade" id="modalAssignKurir" tabindex="-1" role="dialog" aria-labelledby="assignKurirLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content position-relative">
      <!-- overlay loading -->
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
        <!-- Tabel Kurir -->
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
  // CSRF (sesuaikan bila perlu)
  var CSRF_NAME = "<?= isset($this->security) ? $this->security->get_csrf_token_name() : 'csrf_test_name' ?>";
  var CSRF_HASH = "<?= isset($this->security) ? $this->security->get_csrf_hash() : '' ?>";

  // backdrop warna khusus + layering di atas modal parent
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

  // klik baris (non-aksi) – tidak melakukan apa pun selain highlight
  $(document).on('click', '#tblKurir .row-kurir', function(e){
    if ($(e.target).closest('button,a').length) return;
    $('#tblKurir .row-kurir').removeClass('table-success');
    $(this).addClass('table-success');
  });

  // kept for compatibility (tidak dipakai)
  window.assignKurirSelected = function(orderId){
    alert('Pilih kurir dari tombol "Tugaskan" pada tabel.');
  };

  // assign dengan loading overlay & disable tombol
  window.assignKurirNow = function(orderId, courierId, btn){
    var $modal   = $('#modalAssignKurir');
    var $overlay = $modal.find('.assign-loading');
    var $btn     = $(btn || null);

    // UI lock
    $overlay.removeClass('d-none');
    var oldHtml=null;
    if ($btn.length){ oldHtml = $btn.html(); $btn.prop('disabled', true).html('<span class="spin" style="width:16px;height:16px;border-width:2px;margin-right:6px;"></span>Memproses'); }

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
          // update badge kurir di header
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

          // hilangkan tombol header agar tidak bisa assign lagi
          $('#btnAssignKurirHeader').remove();

          // tutup hanya modal kurir
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
        if ($btn.length){ $btn.prop('disabled', false).html(oldHtml || 'Tugaskan'); }
      }
    });
  };

  // salin tombol mini (HP customer)
  $(document).on('click', '.js-copy', function(){
    var val = $(this).data('copy') || '';
    if (!val) return;
    var ta = document.createElement('textarea');
    ta.value = val;
    document.body.appendChild(ta);
    ta.select(); document.execCommand('copy');
    document.body.removeChild(ta);
    $(this).text('Tersalin').prop('disabled', true);
    setTimeout(()=>{ $(this).text('Salin').prop('disabled', false); }, 1500);
  });
})();
</script>
