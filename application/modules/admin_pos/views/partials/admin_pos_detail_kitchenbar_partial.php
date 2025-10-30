<?php
/** @var object $order */
/** @var array  $items */
/** @var int|null $active_cat */
$id      = (int)($order->id ?? 0);
$nomor   = $order->nomor ?? $id;
$modeRaw = strtolower(trim((string)($order->mode ?? '-')));
$modeLabel = ($modeRaw==='dinein' || $modeRaw==='dine-in') ? 'Dine-in'
           : ($modeRaw==='delivery' ? 'Delivery' : 'Walk-in');

$meja    = $order->meja_nama ?: ($order->meja_kode ?: '—');
$nama    = trim((string)($order->nama ?? ''));
$cat     = ($active_cat === 1 || $active_cat === 2) ? (int)$active_cat : null;
$label   = $cat===1 ? 'Kitchen (Makanan)' : ($cat===2 ? 'Bar (Minuman)' : 'Semua Item');
$catatan = trim((string)($order->catatan ?? ''));

$showMeja    = ($modeRaw==='dinein' || $modeRaw==='dine-in');
$isDelivery  = ($modeRaw === 'delivery');
$customer_phone = trim((string)($order->customer_phone ?? ''));
$alamat_kirim   = trim((string)($order->alamat_kirim ?? ''));

function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>

<style>
  /* ===== Order Header (compact, modern) ===== */
  .order-card{
    position:relative;
    display:flex; gap:.9rem; align-items:flex-start;
    padding:.65rem .75rem; border:1px solid #e9ecef; border-radius:14px;
    background:#fff; box-shadow:0 2px 10px rgba(0,0,0,.04);
    margin-bottom:.6rem;
  }
  .order-left{ display:flex; gap:.6rem; align-items:flex-start; min-width:0; flex:1; }
  .order-icon{
    width:42px; height:42px; border-radius:999px; color:#fff; flex:0 0 42px;
    display:flex; align-items:center; justify-content:center; font-size:20px;
    box-shadow: inset 0 8px 18px rgba(0,0,0,.12);
  }
  .order-icon.dine{ background:linear-gradient(135deg,#ef4444,#dc2626); }
  .order-icon.delivery{ background:linear-gradient(135deg,#0ea5e9,#0284c7); }
  .order-icon.walkin{ background:linear-gradient(135deg,#6b7280,#4b5563); }
  .order-main{ min-width:0; }
  .order-title{
    margin:0 0 .1rem; font-weight:800; font-size:1rem; color:#111827; letter-spacing:.2px;
  }
  .order-meta{ margin:0; color:#6b7280; font-size:.85rem; }
  .order-meta b{ color:#374151; }
  .order-badges{ margin-top:.35rem; display:flex; gap:.35rem; flex-wrap:wrap; }
  .pill{ display:inline-block; padding:.2rem .5rem; border-radius:999px;
         font-size:.72rem; border:1px solid #e1e5ea; background:#f8f9fb; color:#374151; }
  .pill.mode{ border-color:transparent; color:#fff; }
  .pill.mode.dine{ background:#ef4444; }
  .pill.mode.delivery{ background:#0ea5e9; }
  .pill.mode.walkin{ background:#6b7280; }

  .order-actions{
    display:flex; gap:.4rem; flex-wrap:wrap; align-items:center;
  }
  .btn-xxs{ padding:.25rem .5rem; font-size:.78rem; border-radius:10px; }

  /* Detail pelanggan mini box */
  .cust-box{
    margin-top:.5rem; border:1px dashed #e5e7eb; background:#fcfcfd; border-radius:10px; padding:.5rem .6rem;
  }
  .cust-row{ font-size:.85rem; color:#374151; }
  .cust-muted{ color:#6b7280; }

  /* Table compact */
  .table-compact thead th{ background:#f9fbfd; border-top:0; font-size:.85rem; }
  .table-compact td, .table-compact th{ padding:.45rem .6rem; vertical-align:middle; }
</style>

<div class="order-card">
  <!-- kiri -->
  <div class="order-left">
    <div class="order-icon <?= $modeRaw==='dinein' || $modeRaw==='dine-in' ? 'dine' : ($modeRaw==='delivery' ? 'delivery' : 'walkin') ?>">
      <?php if ($modeRaw==='dinein' || $modeRaw==='dine-in'): ?>
        <i class="dripicons-basket"></i>
      <?php elseif ($modeRaw==='delivery'): ?>
        <i class="dripicons-rocket"></i>
      <?php else: ?>
        <i class="dripicons-shopping-bag"></i>
      <?php endif; ?>
    </div>

    <div class="order-main">
      <h3 class="order-title">Order #<?= esc($nomor) ?></h3>
      <p class="order-meta">
        Mode: <b><?= esc($modeLabel) ?></b>
        <?php if ($showMeja): ?>
          <span class="mx-1">•</span> Meja: <b><?= esc($meja) ?></b>
        <?php endif; ?>
        <?php if ($nama !== ''): ?>
          <span class="mx-1">•</span> Nama: <b><?= esc($nama) ?></b>
        <?php endif; ?>
      </p>

      <?php if ($catatan !== ''): ?>
        <p class="order-meta" style="margin-top:.25rem;">
          <span class="text-danger" style="font-weight:700">Catatan:</span> <?= nl2br(esc($catatan)) ?>
        </p>
      <?php endif; ?>

      <div class="order-badges">
        <span class="pill mode <?= $modeRaw==='dinein' || $modeRaw==='dine-in' ? 'dine' : ($modeRaw==='delivery' ? 'delivery' : 'walkin') ?>">
          <?= esc($modeLabel) ?>
        </span>
        <span class="pill"><?= esc($label) ?></span>
      </div>

      <?php if ($nama !== '' || $customer_phone !== '' || $isDelivery): ?>
        <div class="cust-box">
          <div class="cust-row"><b>Detail Pelanggan</b></div>
          <?php if ($nama !== ''): ?>
            <div class="cust-row"><span class="cust-muted">Nama:</span> <?= esc($nama) ?></div>
          <?php endif; ?>
          <?php if ($customer_phone !== ''):
                $phoneDigits = preg_replace('/\s+/', '', $customer_phone); ?>
            <div class="cust-row d-flex align-items-center flex-wrap" style="gap:.4rem;">
              <span class="cust-muted">HP/WA:</span>
              <a href="https://wa.me/<?= esc($phoneDigits) ?>" target="_blank" rel="noopener"><?= esc($customer_phone) ?></a>
              <button type="button" class="btn btn-outline-secondary btn-xxs js-copy" data-copy="<?= esc($phoneDigits) ?>">Salin</button>
            </div>
          <?php endif; ?>
          <?php if ($isDelivery): ?>
            <div class="cust-row" style="margin-top:.25rem;">
              <span class="cust-muted">Alamat Kirim:</span>
              <div><?= nl2br(esc($alamat_kirim !== '' ? $alamat_kirim : '-')) ?></div>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- kanan (aksi) -->
  <div class="order-actions">
    <button type="button" class="btn btn-primary btn-xxs"
            onclick="printStrukInlinex(<?= (int)$id ?>, '58')">
      <i class="fe-printer"></i> 58mm
    </button>
    <button type="button" class="btn btn-secondary btn-xxs"
            onclick="printStrukInlinex(<?= (int)$id ?>, '80')">
      <i class="fe-printer"></i> 80mm
    </button>
  </div>
</div>

<div class="table-responsive">
  <table class="table table-sm table-bordered table-compact mb-0">
    <thead>
      <tr>
        <th>Item</th>
        <th class="text-center" style="width:100px">Qty</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($items)): ?>
        <tr><td colspan="2" class="text-center text-dark">Tidak ada item.</td></tr>
      <?php else: foreach($items as $it): ?>
        <tr>
          <td><?= esc($it->nama ?? ('#'.$it->produk_id)) ?></td>
          <td class="text-center"><?= (int)($it->qty ?? 0) ?></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<script>
  // Copy nomor HP → clipboard (Swal kalau ada)
  document.addEventListener('click', function(e){
    const btn = e.target.closest('.js-copy');
    if(!btn) return;
    const text = btn.getAttribute('data-copy') || '';
    if(!text) return;
    navigator.clipboard.writeText(text).then(()=>{
      if(window.Swal){
        Swal.fire({icon:'success', title:'Disalin!', text:'Nomor sudah disalin ke clipboard.', timer:1200, showConfirmButton:false});
      }else{
        alert('Nomor disalin.');
      }
    });
  });
</script>
