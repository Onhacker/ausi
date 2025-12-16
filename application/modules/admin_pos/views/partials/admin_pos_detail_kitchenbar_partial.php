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

$modeRaw    = strtolower(trim((string)($order->mode ?? '-')));
$isDine     = ($modeRaw==='dinein' || $modeRaw==='dine-in');
$isDelivery = ($modeRaw==='delivery');

$modeLabel  = $isDine ? 'Dine-in' : ($isDelivery ? 'Delivery' : 'Walk-in');
$showMeja   = $isDine;

$meja       = $order->meja_nama ?: ($order->meja_kode ?: '—');
$nama       = trim((string)($order->nama ?? ''));
$cat        = ($active_cat === 1 || $active_cat === 2) ? (int)$active_cat : null;
$label      = $cat===1 ? 'Kitchen (Makanan)' : ($cat===2 ? 'Bar (Minuman)' : 'Semua Item');
$catatan    = trim((string)($order->catatan ?? ''));

$phoneRaw   = trim((string)($order->customer_phone ?? ''));
$waNumber   = $phoneRaw !== '' ? msisdn($phoneRaw) : '';
$waHref     = $waNumber !== '' ? ('https://wa.me/'.$waNumber) : '';

$alamat_kirim = trim((string)($order->alamat_kirim ?? ''));

$paidRaw    = trim((string)($order->paid_method ?? ''));
$paidLabel  = $paidRaw !== '' ? $paidRaw : '—';

$showCustomerBox = ($nama !== '' || $phoneRaw !== '' || $isDelivery || $paidRaw !== '');

// background ikon via inline style (tanpa class dine/delivery/walkin)
$iconBg = $isDine
  ? 'linear-gradient(135deg,#ef4444,#dc2626)'
  : ($isDelivery ? 'linear-gradient(135deg,#0ea5e9,#0284c7)' : 'linear-gradient(135deg,#6b7280,#4b5563)');
?>

<style>
  /* ===== Header Card ===== */
  .order-card{
    padding:.75rem .85rem;
    border:1px solid #e5e7eb;
    border-radius:12px;
    background:#fff;
    box-shadow:0 1px 4px rgba(15,23,42,.04);
    margin-bottom:.75rem;
  }
  .order-head{
    display:flex;
    gap:.65rem;
    align-items:flex-start;
    min-width:0;
  }
  .order-icon{
    width:40px; height:40px; border-radius:999px;
    color:#fff; flex:0 0 40px;
    display:flex; align-items:center; justify-content:center;
    font-size:19px;
    box-shadow: inset 0 8px 18px rgba(0,0,0,.12);
  }
  .order-title{
    margin:0;
    font-weight:800;
    font-size:1rem;
    color:#111827;
    letter-spacing:.2px;
    line-height:1.2;
  }
  .order-sub{
    margin:.15rem 0 0;
    color:#6b7280;
    font-size:.85rem;
  }

  /* ===== Box + Table label-value ===== */
  .cust-box{
    border:1px solid #e5e7eb;
    background:#fff;
    border-radius:12px;
    padding:.55rem .75rem;
  }
  .id-table{
    width:100%;
    border-collapse:separate;
    border-spacing:0 6px;
  }
  .id-table th{
    width:128px;
    padding:.2rem .4rem;
    font-weight:600;
    color:#6b7280;
    text-align:left;
    vertical-align:top;
    white-space:nowrap;
  }
  .id-table td{ padding:.2rem .4rem; color:#111827; }

  .btn-xxs{ padding:.25rem .6rem; font-size:.78rem; border-radius:999px; }

  /* Table compact items */
  .table-compact thead th{
    background:#f9fbfd;
    border-top:0;
    font-size:.85rem;
  }
  .table-compact td,
  .table-compact th{
    padding:.45rem .6rem;
    vertical-align:middle;
  }
</style>

<div class="order-card">

  <!-- HEADER: ICON + TITLE -->
  <div class="order-head">
    <div class="order-icon" style="background:<?= esc($iconBg) ?>;">
      <?php if ($isDine): ?>
        <i class="dripicons-basket"></i>
      <?php elseif ($isDelivery): ?>
        <i class="dripicons-rocket"></i>
      <?php else: ?>
        <i class="dripicons-shopping-bag"></i>
      <?php endif; ?>
    </div>

    <div style="min-width:0;">
      <h3 class="order-title">Order #<?= esc($nomor) ?></h3>
      <p class="order-sub mb-0">
        <b><?= esc($modeLabel) ?></b>
        <?php if ($showMeja): ?>
          &nbsp;•&nbsp; Meja: <b><?= esc($meja) ?></b>
        <?php endif; ?>
      </p>
    </div>
  </div>

  <!-- 2 KOLOM: Ringkasan (kiri) + Detail Pelanggan (kanan) -->
  <div class="row mt-3">
    <!-- KIRI: RINGKASAN ORDER + TOMBOL CETAK (DI DALAM) -->
    <div class="col-md-6 mb-2 mb-md-0">
      <div class="cust-box">
        <table class="id-table mb-1">
          <tr>
            <th>Mode</th>
            <td><?= esc($modeLabel) ?></td>
          </tr>

          <?php if ($showMeja): ?>
            <tr>
              <th>Meja</th>
              <td><?= esc($meja) ?></td>
            </tr>
          <?php endif; ?>

          <tr>
            <th>Filter Item</th>
            <td><?= esc($label) ?></td>
          </tr>

          <?php if ($catatan !== ''): ?>
            <tr>
              <th>Catatan</th>
              <td>
                <span class="text-danger" style="font-weight:700;">
                  <?= nl2br(esc($catatan)) ?>
                </span>
              </td>
            </tr>
          <?php endif; ?>
        </table>

        <div class="text-right mt-2">
          <button type="button"
                  class="btn btn-pink btn-xxs"
                  onclick="printStrukInlinex(<?= (int)$id ?>, '80', true, true)"
                  aria-label="Cetak struk 80mm via RawBT">
            <i class="fe-printer"></i> Cetak
          </button>
        </div>
      </div>
    </div>

    <!-- KANAN: DETAIL PELANGGAN -->
    <div class="col-md-6">
      <div class="cust-box">
        <?php if ($showCustomerBox): ?>
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
        <?php else: ?>
          <div class="text-muted">—</div>
        <?php endif; ?>
      </div>
    </div>
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
        <tr>
          <td colspan="2" class="text-center text-dark">Tidak ada item.</td>
        </tr>
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

    var w = window.open(url, "print_"+id,
      "width=520,height=760,menubar=0,location=0,toolbar=0,status=0");
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
</script>
