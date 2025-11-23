<?php
// Normalisasi & fallback mode
$mode_raw = isset($mode) ? strtolower(trim((string)$mode)) : '';
if ($mode_raw === '') {
  $mode_raw = strtolower((string)($this->session->userdata('cart__mode') ?? ''));
}
$mode_norm = str_replace([' ', '_', '-'], '', $mode_raw);
if ($mode_norm === 'dine' || $mode_norm === 'dinein') {
  $mode_norm = 'dinein';
} elseif ($mode_norm === 'delivery' || $mode_norm === 'deliv') {
  $mode_norm = 'delivery';
} elseif ($mode_norm === 'walkin' || $mode_norm === 'takeaway' || $mode_norm === 'takeout') {
  $mode_norm = 'walkin';
} else {
  $mode_norm = '';
}

// Fallback: kalau ada meja → dinein, kalau tidak → walkin
if ($mode_norm === '') {
  $mode_norm = (!empty($meja_info) ? 'dinein' : 'walkin');
}

$is_dinein   = ($mode_norm === 'dinein');
$is_delivery = ($mode_norm === 'delivery');
?>

<style>
 .mode-info,.mode-left{min-width:0}.mode-card{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.45rem;padding:.38rem .65rem;border:1px solid rgba(15,23,42,.06);border-radius:12px;background:#fff;box-shadow:0 1px 5px rgba(15,23,42,.04);position:relative;overflow:hidden;transition:box-shadow .18s,transform .18s}.mode-icon,.mode-left{align-items:center;display:flex}.mode-card:hover{box-shadow:0 4px 12px rgba(15,23,42,.1);transform:translateY(-1px)}.mode-card::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;opacity:.85}.mode-card--dinein::before{background:linear-gradient(180deg,#f97373,#b91c1c)}.mode-card--delivery::before{background:linear-gradient(180deg,#38bdf8,#0369a1)}.mode-card--takeaway::before{background:linear-gradient(180deg,#9ca3af,#4b5563)}.mode-left{gap:.55rem;flex:1 1 auto}.mode-icon{width:32px;height:32px;border-radius:999px;color:#fff;justify-content:center;font-size:17px;box-shadow:inset 0 5px 12px rgba(0,0,0,.16);flex:0 0 32px}.mode-title{margin:0;font-weight:700;font-size:.9rem;line-height:1.2;color:#111827;letter-spacing:.05px}.mode-sub{margin:.05rem 0 0;color:#6b7280;font-size:.78rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.mode-right{display:flex;align-items:center;gap:.35rem;flex:0 0 auto;margin-left:auto}.mode-badge{padding:.1rem .55rem;border-radius:999px;font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;background:rgba(15,23,42,.02);border:1px solid rgba(148,163,184,.7);color:#4b5563;white-space:nowrap}.bg-dine{background:linear-gradient(135deg,#ef4444,#dc2626)}.bg-delivery{background:linear-gradient(135deg,#0ea5e9,#0284c7)}.bg-take{background:linear-gradient(135deg,#6b7280,#4b5563)}.btn-chip{padding:.25rem .55rem;border-radius:999px;font-weight:600;font-size:.75rem;line-height:1.1}.btn-outline-danger.btn-chip{border-width:1px}@media (max-width:480px){.mode-card{padding:.35rem .55rem;gap:.35rem}.mode-title{font-size:.86rem}.mode-sub{font-size:.76rem}}
</style>

<?php if ($is_dinein): ?>
  <!-- DINE-IN / MEJA (compact) -->
  <div class="mode-card mb-2 mode-card--dinein">
    <div class="mode-left">
      <div class="mode-icon bg-dine">
        <i class="dripicons-basket" aria-hidden="true"></i>
      </div>
      <div class="mode-info">
        <h3 class="mode-title">
          <?= !empty($meja_info) ? html_escape($meja_info) : 'Mode Dine-in' ?>
        </h3>
        <p class="mode-sub">
          Order Area <strong><?= !empty($meja_info) ? html_escape($meja_info) : 'ditempat' ?></strong>
        </p>
      </div>
    </div>

    <div class="mode-right">
      <!-- Tombol Keluar dari Meja: pakai class untuk binding SweetAlert -->
     <!--  <a href="<?= site_url('produk/leave_table') ?>"
         class="btn btn-sm btn-outline-danger btn-chip js-leave-table"
         data-meja="<?= !empty($meja_info) ? ' '.html_escape($meja_info) : '' ?>">
        Keluar Meja
      </a> -->
      <span class="mode-badge">DINE-IN</span>
    </div>
  </div>

  <script>
  (function(){
    // delegasi: aman kalau tombol dirender dinamis
    document.addEventListener('click', function(e){
      const a = e.target.closest('.js-leave-table');
      if (!a) return;

      e.preventDefault();

      const href = a.getAttribute('href') || '#';
      const meja = a.getAttribute('data-meja') || '';

      // fallback kalau Swal tidak tersedia
      if (typeof Swal === 'undefined'){
        if (confirm('Keluar dari mode Dine-in? Keranjang meja akan dilepas.')) {
          window.location.href = href;
        }
        return;
      }

      Swal.fire({
        title: 'Keluar dari Meja?',
        text: meja
          ? ('Kamu akan keluar dari ' + meja + '. Keranjang meja akan dilepas.')
          : 'Kamu akan keluar dari mode Dine-in. Keranjang meja akan dilepas.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, keluar',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        focusCancel: true
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire({
            title:'Memproses...',
            allowOutsideClick:false,
            didOpen:()=>Swal.showLoading()
          });
          window.location.href = href;
        }
      });
    }, false);
  })();
  </script>

<?php elseif ($is_delivery): ?>
  <!-- DELIVERY -->
  <div class="mode-card mb-2 mode-card--delivery">
    <div class="mode-left">
      <div class="mode-icon bg-delivery">
        <i class="dripicons-rocket" aria-hidden="true"></i>
      </div>
      <div class="mode-info">
        <h3 class="mode-title">
          Mode Pesan Antar
        </h3>
        <p class="mode-sub">
         <strong>Diantar ke alamat Anda</strong>
        </p>
      </div>
    </div>
    <div class="mode-right">
      <span class="mode-badge">DELIVERY</span>
    </div>
  </div>

<?php else: ?>
  <!-- TAKEAWAY / WALK-IN -->
  <div class="mode-card mb-2 mode-card--takeaway">
    <div class="mode-left">
      <div class="mode-icon bg-take">
        <i class="dripicons-shopping-bag" aria-hidden="true"></i>
      </div>
      <div class="mode-info">
        <h3 class="mode-title">
          Mode Bungkus
        </h3>
        <p class="mode-sub">
          Pesanan <strong>dibungkus</strong>
        </p>
      </div>
    </div>
    <div class="mode-right">
      <span class="mode-badge">TAKEAWAY</span>
    </div>
  </div>
<?php endif; ?>
