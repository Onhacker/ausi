<?php
// Normalisasi & fallback mode
$mode_raw = isset($mode) ? strtolower(trim((string)$mode)) : '';
if ($mode_raw === '') {
  $mode_raw = strtolower((string)($this->session->userdata('cart__mode') ?? ''));
}
$mode_norm = str_replace([' ', '_', '-'], '', $mode_raw);
if ($mode_norm === 'dine' || $mode_norm === 'dinein') $mode_norm = 'dinein';
elseif ($mode_norm === 'delivery' || $mode_norm === 'deliv') $mode_norm = 'delivery';
elseif ($mode_norm === 'walkin' || $mode_norm === 'takeaway' || $mode_norm === 'takeout') $mode_norm = 'walkin';
else $mode_norm = '';

// Fallback: kalau ada meja → dinein, kalau tidak → walkin
if ($mode_norm === '') $mode_norm = (!empty($meja_info) ? 'dinein' : 'walkin');

$is_dinein   = ($mode_norm === 'dinein');
$is_delivery = ($mode_norm === 'delivery');
?>

<style>
  /* ===== Mode Card (compact) ===== */
  .mode-card{
    display:flex; align-items:center; justify-content:space-between;
    padding:.55rem .75rem; border:1px solid rgba(0,0,0,.06);
    border-radius:14px; background:#fff;
    box-shadow:0 2px 10px rgba(0,0,0,.04);
  }
  .mode-left{ display:flex; align-items:center; gap:.6rem; min-width:0; }
  .mode-icon{
    width:42px; height:42px; border-radius:999px; color:#fff;
    display:flex; align-items:center; justify-content:center; font-size:20px;
    box-shadow: inset 0 8px 18px rgba(0,0,0,.12);
    flex: 0 0 42px;
  }
  .mode-info{ min-width:0; }
  .mode-title{
    margin:0; font-weight:800; font-size:1rem; line-height:1.1; color:#111827;
    letter-spacing:.2px;
  }
  .mode-sub{
    margin:0; color:#6b7280; font-size:.85rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
  }
  .mode-right{ display:flex; align-items:center; gap:.5rem; flex: 0 0 auto; }

  /* Warna tema */
  .bg-dine{ background:linear-gradient(135deg,#ef4444,#dc2626); }       /* merah */
  .bg-delivery{ background:linear-gradient(135deg,#0ea5e9,#0284c7);}    /* biru */
  .bg-take{ background:linear-gradient(135deg,#6b7280,#4b5563);}        /* abu */

  /* Tombol kecil */
  .btn-chip{
    padding:.35rem .55rem; border-radius:10px; font-weight:700; font-size:.8rem;
  }
  .btn-outline-danger.btn-chip{ border-width:1px; }
  @media (max-width: 480px){
    .mode-title{ font-size:.95rem; }
    .mode-sub{ font-size:.8rem; }
  }
</style>

<?php if ($is_dinein): ?>
  <!-- DINE-IN / MEJA (compact) -->
  <div class="mode-card mb-2">
    <div class="mode-left">
      <div class="mode-icon bg-dine">
        <i class="dripicons-basket" aria-hidden="true"></i>
      </div>
      <div class="mode-info">
        <h3 class="mode-title">
          <?= !empty($meja_info) ? ' '.html_escape($meja_info) : 'Dine-in' ?>
        </h3>
        <p class="mode-sub">
          Pesanan Area <strong><?= !empty($meja_info) ? html_escape($meja_info) : 'ditempat' ?></strong>
        </p>
      </div>
    </div>
    <div class="mode-right">
      <!-- Tombol Keluar dari Meja: pakai class untuk binding SweetAlert -->
      <a href="<?= site_url('produk/leave_table') ?>"
         class="btn btn-sm btn-outline-danger btn-chip js-leave-table"
         data-meja="<?= !empty($meja_info) ? ' '.html_escape($meja_info) : '' ?>">
        Keluar dari Meja
      </a>

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
      text: meja ? ('Kamu akan keluar dari ' + meja + '. Keranjang meja akan dilepas.') 
                 : 'Kamu akan keluar dari mode Dine-in. Keranjang meja akan dilepas.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, keluar',
      cancelButtonText: 'Batal',
      reverseButtons: true,
      focusCancel: true
    }).then((result) => {
      if (result.isConfirmed) {
        // opsi: tampilan loading sebentar
        Swal.fire({title:'Memproses...', allowOutsideClick:false, didOpen:()=>Swal.showLoading()});
        window.location.href = href;
      }
    });
  }, false);
})();
</script>


<?php elseif ($is_delivery): ?>
  <!-- DELIVERY (tanpa nama/alamat) -->
  <div class="mode-card mb-2">
    <div class="mode-left">
      <div class="mode-icon bg-delivery">
        <i class="dripicons-rocket" aria-hidden="true"></i>
      </div>
      <div class="mode-info">
        <h3 class="mode-title">Delivery</h3>
        <p class="mode-sub">Pesanan <strong>Dikirim</strong></p>
      </div>
    </div>
  </div>

<?php else: ?>
  <!-- TAKEAWAY / WALK-IN -->
  <div class="mode-card mb-2">
    <div class="mode-left">
      <div class="mode-icon bg-take">
        <i class="dripicons-shopping-bag" aria-hidden="true"></i>
      </div>
      <div class="mode-info">
        <h3 class="mode-title">Takeaway</h3>
        <p class="mode-sub">Pesanan <strong>Dibungkus</strong></p>
      </div>
    </div>
  </div>
<?php endif; ?>
