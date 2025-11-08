<?php
/** @var object $order */
/** @var array  $items */
/** @var int|null $active_cat */

function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function msisdn($p){
  $d = preg_replace('/\D+/', '', (string)$p);
  if ($d === '') return '';
  if (strpos($d,'62')===0) return $d;
  if ($d[0]==='0') return '62'.substr($d,1);
  if ($d[0]==='8') return '62'.$d;
  return $d;
}

$id        = (int)($order->id ?? 0);
$nomor     = ($order->nomor ?? '') !== '' ? $order->nomor : $id;
$modeRaw   = strtolower(trim((string)($order->mode ?? '-')));
$modeLabel = ($modeRaw==='dinein' || $modeRaw==='dine-in') ? 'Dine-in' : ($modeRaw==='delivery' ? 'Delivery' : 'Walk-in');

$meja   = $order->meja_nama ?: ($order->meja_kode ?: '—');
$nama   = trim((string)($order->nama ?? ''));
$cat    = ($active_cat === 1 || $active_cat === 2) ? (int)$active_cat : null;
$label  = $cat===1 ? 'Kitchen (Makanan)' : ($cat===2 ? 'Bar (Minuman)' : 'Semua Item');
$catatan= trim((string)($order->catatan ?? ''));

$showMeja     = ($modeRaw==='dinein' || $modeRaw==='dine-in');
$isDelivery   = ($modeRaw === 'delivery');
$phoneRaw     = trim((string)($order->customer_phone ?? ''));
$waNumber     = $phoneRaw !== '' ? msisdn($phoneRaw) : '';
$alamat_kirim = trim((string)($order->alamat_kirim ?? ''));

// (opsional) tampilkan paid method ringkas jika ada
$paidRaw   = trim((string)($order->paid_method ?? ''));
$paidLabel = $paidRaw !== '' ? $paidRaw : '—';

// siapkan href WA yang aman
$waHref = $waNumber !== '' ? ('https://wa.me/'.$waNumber) : '';
?>

<style>
  /* ===== Order Header (compact, modern) ===== */
  .order-card{
    position:relative; display:flex; gap:.9rem; align-items:flex-start;
    padding:.65rem .75rem; border:1px solid #e9ecef; border-radius:14px;
    background:#fff; box-shadow:0 2px 10px rgba(0,0,0,.04); margin-bottom:.6rem;
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

  .order-title{ margin:0 0 .1rem; font-weight:800; font-size:1rem; color:#111827; letter-spacing:.2px; }
  .order-meta{ margin:0; color:#6b7280; font-size:.85rem; }
  .order-meta b{ color:#374151; }

  .order-badges{ margin-top:.35rem; display:flex; gap:.35rem; flex-wrap:wrap; }
  .pill{ display:inline-block; padding:.2rem .5rem; border-radius:999px; font-size:.72rem; border:1px solid #e1e5ea; background:#f8f9fb; color:#374151; }
  .pill.mode{ border-color:transparent; color:#fff; }
  .pill.mode.dine{ background:#ef4444; }
  .pill.mode.delivery{ background:#0ea5e9; }
  .pill.mode.walkin{ background:#6b7280; }

  .order-actions{ display:flex; gap:.4rem; flex-wrap:wrap; align-items:center; }
  .btn-xxs{ padding:.25rem .5rem; font-size:.78rem; border-radius:10px; }

  /* ===== Detail Pelanggan (label–value table) ===== */
  .cust-box{
    margin-top:.5rem; border:1px solid #e5e7eb; background:#fff;
    border-radius:12px; padding:.6rem .75rem;
  }
  .id-table{ width:100%; border-collapse:separate; border-spacing:0 6px; }
  .id-table th{
    width:128px; padding:.25rem .5rem; font-weight:600; color:#6b7280;
    text-align:left; vertical-align:top; white-space:nowrap;
  }
  .id-table td{ padding:.25rem .5rem; color:#111827; }
  .id-actions{ display:inline-flex; gap:.4rem; margin-left:.5rem; flex-wrap:wrap; vertical-align:middle; }
  .id-actions .btn-xxs{ padding:.22rem .55rem; font-size:.78rem; border-radius:10px; }

  /* Table compact */
  .table-compact thead th{ background:#f9fbfd; border-top:0; font-size:.85rem; }
  .table-compact td, .table-compact th{ padding:.45rem .6rem; vertical-align:middle; }
</style>

<div class="order-card">
  <!-- kiri -->
  <div class="order-left">
    <div class="order-icon <?= ($modeRaw==='dinein'||$modeRaw==='dine-in') ? 'dine' : ($modeRaw==='delivery' ? 'delivery' : 'walkin') ?>">
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
        <?php if ($showMeja): ?><span class="mx-1">•</span> Meja: <b><?= esc($meja) ?></b><?php endif; ?>
        <?php if ($nama !== ''): ?><span class="mx-1">•</span> Nama: <b><?= esc($nama) ?></b><?php endif; ?>
        <?php if ($paidRaw !== ''): ?><span class="mx-1">•</span> Metode: <b><?= esc($paidLabel) ?></b><?php endif; ?>
      </p>

      <?php if ($catatan !== ''): ?>
        <p class="order-meta" style="margin-top:.25rem;">
          <span class="text-danger" style="font-weight:700">Catatan:</span> <?= nl2br(esc($catatan)) ?>
        </p>
      <?php endif; ?>

      <div class="order-badges">
        <span class="pill mode <?= ($modeRaw==='dinein'||$modeRaw==='dine-in') ? 'dine' : ($modeRaw==='delivery' ? 'delivery' : 'walkin') ?>">
          <?= esc($modeLabel) ?>
        </span>
        <span class="pill"><?= esc($label) ?></span>
      </div>

      <?php if ($nama !== '' || $phoneRaw !== '' || $isDelivery): ?>
        <div class="cust-box">
          <table class="id-table">
            <?php if ($nama !== ''): ?>
              <tr>
                <th>Nama</th>
                <td><?= esc($nama) ?></td>
              </tr>
            <?php endif; ?>

            <?php if ($phoneRaw !== ''): ?>
              <tr>
                <th>HP/WA</th>
                <td>
                  <?php if ($waHref !== ''): ?>
                    <a href="<?= esc($waHref) ?>" target="_blank" rel="noopener"><?= esc($phoneRaw) ?></a>
                  <?php else: ?>
                    <span><?= esc($phoneRaw) ?></span>
                  <?php endif; ?>
                  <span class="id-actions">
                    <button type="button"
                            class="btn btn-outline-secondary btn-xxs js-copy"
                            data-copy="<?= esc($waNumber ?: preg_replace('/\s+/', '', $phoneRaw)) ?>">
                      Salin
                    </button>
                    <a class="btn btn-outline-secondary btn-xxs"
                       href="<?= $waHref !== '' ? esc($waHref) : 'javascript:void(0)' ?>"
                       target="_blank" rel="noopener"
                       <?= $waHref !== '' ? '' : 'aria-disabled="true" tabindex="-1" style="pointer-events:none;opacity:.6;"' ?>>
                      WhatsApp
                    </a>
                  </span>
                </td>
              </tr>
            <?php endif; ?>

            <?php if ($isDelivery): ?>
              <tr>
                <th>Alamat Kirim</th>
                <td><div style="white-space:pre-wrap;"><?= esc($alamat_kirim !== '' ? $alamat_kirim : '-') ?></div></td>
              </tr>
            <?php endif; ?>

            <?php if ($paidRaw !== ''): ?>
              <tr>
                <th>Metode</th>
                <td><?= esc($paidLabel) ?></td>
              </tr>
            <?php endif; ?>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- kanan (aksi) -->
  <div class="order-actions">
   <!--  <button type="button" class="btn btn-secondary btn-xxs"
            onclick="printStrukInlinex(<?= (int)$id ?>, '80')"
            aria-label="Cetak struk 80mm">
      <i class="fe-printer"></i> 80mm
    </button> -->

    <button type="button" class="btn btn-outline-secondary btn-xxs"
            onclick="printStrukInlinex(<?= (int)$id ?>, '80', true, true)"
            aria-label="Cetak struk 80mm via RawBT">
      <i class="fe-printer"></i> Cetak
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
  // id, paper('58'|'80'), autoClose[opt], useRawBT[opt]
  function printStrukInlinex(id, paper, autoClose, useRawBT){
    // default agar panggilan lama (2 arg) tetap berfungsi = HTML print + autoClose
    if (typeof autoClose === 'undefined') autoClose = true;
    if (typeof useRawBT  === 'undefined') useRawBT  = false; // default: HTML

    var p   = (paper === '80') ? '80' : '58';
    var url = "<?= site_url('admin_pos/print_struk_termalx/') ?>" + id
            + "?paper=" + p + "&embed=1"
            + (useRawBT ? "&rawbt=1" : "&autoprint=1")
            + (autoClose ? "&autoclose=1" : "");

    var w = window.open(url, "print_"+id, "width=520,height=760,menubar=0,location=0,toolbar=0,status=0");
    if (!w) return; // popup diblok

    // Fallback auto-print cuma utk mode HTML
    if (!useRawBT){
      var tried = false;
      var iv = setInterval(function(){
        if (!w || w.closed) { clearInterval(iv); return; }
        try {
          if (w.document && w.document.readyState === 'complete' && !tried){
            tried = true; clearInterval(iv);
            try { w.focus(); w.print(); if (autoClose) w.close(); } catch(e){}
          }
        } catch(e){}
      }, 250);
    }
  }

  // Handler tombol "Salin" untuk nomor WA/HP
  document.addEventListener('click', function(e){
    var btn = e.target.closest('.js-copy');
    if(!btn) return;
    var t = btn.getAttribute('data-copy') || '';
    if(!t) return;
    navigator.clipboard.writeText(t).then(function(){
      var old = btn.textContent;
      btn.textContent = 'Tersalin';
      setTimeout(function(){ btn.textContent = old; }, 1200);
    }).catch(function(){});
  });
</script>
