<?php $this->load->view("front_end/head.php"); ?>
<div class="container-fluid">
  <div class="hero-title">
    <h1 class="text">Pembayaran via QRIS</h1>
    <div class="text-muted">Silakan scan QRIS dan bayar sesuai total di bawah ini, lalu tunggu verifikasi kasir.</div>
    <span class="accent" aria-hidden="true"></span>
  </div>

<?php
  // ===== Konteks & total tampilan =====
  $order_id      = (int)($order->id ?? 0);
  $is_delivery   = strtolower((string)($order->mode ?? '')) === 'delivery';
  $delivery_fee  = (int)($order->delivery_fee ?? 0);
  $subtotal_view = (int)($total ?? 0);

  // voucher
  $voucher_disc = (int)($order->voucher_disc ?? 0);
  $voucher_code = trim((string)($order->voucher_code ?? ''));
  $has_voucher  = ($voucher_disc > 0);

  // Ambil dari DB langsung agar pasti konsisten
  $kode_unik = isset($order->kode_unik)   ? (int)$order->kode_unik   : 0;
  $grand_db  = isset($order->grand_total) ? (int)$order->grand_total : null;

  // Fallback jika grand_total belum ada di DB
  $subtotal_after_voucher = $subtotal_view - $voucher_disc;
  if ($subtotal_after_voucher < 0) $subtotal_after_voucher = 0;

  $grand_fallback = $subtotal_after_voucher + ($is_delivery ? $delivery_fee : 0) + $kode_unik;
  $grand_display  = $grand_db ?? (int)($grand_total ?? $grand_fallback);

?>

  <div class="row">
    <!-- Kolom kiri: QRIS + nominal -->
    <div class="col-md-6">
      <div class="card card-body mb-3">
        <!-- <h5 class="mb-3">Scan QRIS</h5> -->
         <div class="d-flex justify-content-between mt-1 pt-2">
          <strong>Total</strong>
          <strong>Rp <?= number_format($grand_display,0,',','.') ?></strong>
        </div>

        <div class="text-center">
          <!-- Area yang akan di-screenshot -->
          <div id="qris-shot" style="display:inline-block; background:#fff; padding:8px; border-radius:8px;">
            <img
              id="qris-img"
              src="<?= site_url('produk/qris_png/'.$order_id) ?>"
              alt="QRIS"
              style="max-width: 320px; width:100%; height:auto; image-rendering: -webkit-optimize-contrast; background:#fff;"
            >
          </div>

          <div class="mt-2 d-flex justify-content-center gap-2">
            <button
              id="btn-screenshot-qris"
              type="button"
              class="btn btn-sm btn-outline-secondary"
              aria-label="Screenshot & Simpan"
            >
              Screenshot & Simpan
            </button>
            <!-- (opsional)
            <button id="btn-download-qris" type="button" class="btn btn-sm btn-outline-secondary" aria-label="Unduh PNG">
              Unduh PNG
            </button> -->
          </div>

          <small class="text-muted d-block mt-2">
            Di Android/iOS, tombol <em>Screenshot &amp; Simpan</em> membuka Share Sheet — pilih “Simpan ke Foto/Galeri”.
          </small>

          <div class="alert alert-info mt-2 mb-0 text-left" role="alert" style="max-width:420px; margin:10px auto 0;">
            <div class="font-weight-bold mb-1">Tips bila memesan & membayar dari perangkat yang sama:</div>
            <ol class="mb-0 pl-3">
              <li>Tekan <strong>Screenshot</strong> untuk menyimpan QR ke Galeri.</li>
              <li>Buka aplikasi pembayaran Anda (mis. bank/e-wallet).</li>
              <li>Pilih menu <strong>Scan</strong> lalu gunakan opsi <strong>Ambil dari Galeri</strong></li>
              <li>Pilih gambar QR yang barusan tersimpan, lalu <strong>bayar sesuai nominal</strong>.</li>
            </ol>
            <small class="d-block mt-1 text-muted">Catatan: beberapa aplikasi menggunakan istilah “Galeri”, “Foto”, atau “Upload QR”.</small>
          </div>
        </div>

        <hr class="my-3">

        <h5 class="mb-3">Nominal Pembayaran</h5>

        <div class="d-flex justify-content-between">
          <div class="text-dark">Subtotal</div>
          <div>Rp <?= number_format($subtotal_view,0,',','.') ?></div>
        </div>
        <?php if ($has_voucher): ?>
        <div class="d-flex justify-content-between">
          <div class="text-dark">
            Voucher<?php if ($voucher_code): ?> (<?= html_escape($voucher_code) ?>)<?php endif; ?>
          </div>
          <div class="text-danger">- <?= number_format($voucher_disc,0,',','.') ?></div>
        </div>
        <?php endif; ?>
        <?php if ($is_delivery && $delivery_fee > 0): ?>
        <div class="d-flex justify-content-between">
          <div class="text-dark">Ongkir</div>
          <div>+ <?= number_format($delivery_fee,0,',','.') ?></div>
        </div>
        <?php endif; ?>

        <?php if ($kode_unik > 0): ?>
        <div class="d-flex justify-content-between">
          <div class="text-dark">Kode Unik</div>
          <div>+ <?= number_format($kode_unik,0,',','.') ?></div>
        </div>
        <?php endif; ?>

        <hr class="my-2">

        <div class="d-flex justify-content-between align-items-center">
          <div class="h5 mb-0">Total Bayar</div>
          <div class="text-right">
            <div class="display-4" style="font-size:1.8rem;">Rp <?= number_format($grand_display,0,',','.') ?></div>
            <button
              type="button"
              class="btn btn-sm btn-outline-secondary mt-1 js-copy"
              data-copy="<?= (int)$grand_display ?>"
              aria-label="Salin total bayar"
            >
              Salin Total
            </button>
          </div>
        </div>

        <div class="small text-danger mt-2">
          * Pastikan membayar <strong>sesuai nominal</strong> agar kasir mudah memverifikasi.
        </div>
      </div>

      <div class="card card-body">
        <div class="alert alert-warning mb-3">
          Setelah pembayaran berhasil, kasir akan memverifikasi dan menandai pesanan Anda sebagai <strong>lunas</strong>.
        </div>

        <!-- <div class="d-flex flex-wrap gap-2">
          <a href="<?= site_url('produk/order_success/'.$order_id) ?>" class="btn btn-primary">
            <i class="mdi mdi-swap-horizontal mr-1"></i> Kembali
          </a>
        </div> -->
      </div>
    </div>

    <!-- Kolom kanan: Ringkasan pesanan -->
    <div class="col-md-6">
      <div class="card card-body mb-3">
        <h5 class="mb-3">Ringkasan Pesanan</h5>
        <ul class="list-unstyled mb-2">
          <?php foreach($items as $it): ?>
            <li class="d-flex justify-content-between border-bottom py-1">
              <span>
                <?= html_escape($it->nama ?? '-') ?>
                <?php if (!empty($it->tambahan) && (int)$it->tambahan===1): ?>
                  <span class="badge badge-warning">Tambahan</span>
                <?php endif; ?>
              </span>
              <span>x<?= (int)$it->qty ?> — Rp <?= number_format((int)$it->harga*(int)$it->qty,0,',','.') ?></span>
            </li>
          <?php endforeach; ?>
        </ul>

        <div class="d-flex justify-content-between pt-2 border-top">
          <strong>Subtotal</strong>
          <strong>Rp <?= number_format($subtotal_view,0,',','.') ?></strong>
        </div>
        <?php if ($has_voucher): ?>
          <div class="d-flex justify-content-between">
            <span>
              Voucher<?php if ($voucher_code): ?> (<?= html_escape($voucher_code) ?>)<?php endif; ?>
            </span>
            <span class="text-danger">- <?= number_format($voucher_disc,0,',','.') ?></span>
          </div>
        <?php endif; ?>

        <?php if ($is_delivery && $delivery_fee > 0): ?>
        <div class="d-flex justify-content-between">
          <span>Ongkir</span>
          <span>+ <?= number_format($delivery_fee,0,',','.') ?></span>
        </div>
        <?php endif; ?>

        <?php if ($kode_unik > 0): ?>
        <div class="d-flex justify-content-between">
          <span>Kode Unik</span>
          <span>+ <?= number_format($kode_unik,0,',','.') ?></span>
        </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between border-top mt-1 pt-2">
          <strong>Total Bayar</strong>
          <strong>Rp <?= number_format($grand_display,0,',','.') ?></strong>
        </div>
      </div>

      <?php if (!empty($meja_info)): ?>
      <div class="card card-body">
        <div class="small text-muted">Meja</div>
        <div class="h6 mb-0"><?= html_escape($meja_info) ?></div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php $this->load->view("front_end/footer.php"); ?>

<!-- html2canvas CDN -->
<script src="<?php echo base_url("assets/js/canva.js") ?>"></script>

<script>
// ===== Utility salin ke clipboard =====
(function(){
  function copyText(text){
    if (navigator.clipboard && window.isSecureContext) {
      return navigator.clipboard.writeText(text);
    }
    return new Promise(function(resolve, reject){
      try{
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.focus(); ta.select();
        const ok = document.execCommand('copy');
        document.body.removeChild(ta);
        ok ? resolve() : reject(new Error('Copy failed'));
      }catch(e){ reject(e); }
    });
  }

  function flash(btn, ok){
    const old = btn.textContent;
    btn.disabled = true;
    btn.textContent = ok ? 'Tersalin ✓' : 'Gagal';
    setTimeout(function(){
      btn.disabled = false;
      btn.textContent = old;
    }, 1200);
  }

  document.addEventListener('click', function(e){
    const btn = e.target.closest('.js-copy');
    if (!btn) return;
    const text = btn.getAttribute('data-copy') || '';
    if (!text) return;

    copyText(text).then(function(){ flash(btn, true); })
                  .catch(function(){ flash(btn, false); });
  });
})();

// ===== Screenshot QRIS (share / download) =====
(function(){
  const btnShare  = document.getElementById('btn-screenshot-qris');
  const btnDl     = document.getElementById('btn-download-qris'); // opsional
  const shotNode  = document.getElementById('qris-shot');
  const img       = document.getElementById('qris-img');
  if (!shotNode || !img || !btnShare) return;

  function saveCanvasAsPng(canvas, filename){
    if (canvas.toBlob){
      canvas.toBlob(function(blob){
        if (!blob){ alert('Gagal membuat gambar.'); return; }
        const fileUrl = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = fileUrl; a.download = filename;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(fileUrl);
      }, 'image/png');
    } else {
      const a = document.createElement('a');
      a.href = canvas.toDataURL('image/png');
      a.download = filename;
      document.body.appendChild(a); a.click(); a.remove();
    }
  }

  function fileFromCanvas(canvas, filename){
    return new Promise((resolve, reject)=>{
      canvas.toBlob(function(blob){
        if (!blob) return reject(new Error('Blob gagal dibuat'));
        resolve(new File([blob], filename, {type:'image/png'}));
      }, 'image/png');
    });
  }

  async function renderCanvasPreferImgOnly(){
    if (!img.complete){
      await new Promise((res, rej)=>{ img.onload = res; img.onerror = () => rej(new Error('QR gagal dimuat')); });
    }
    const w = img.naturalWidth || img.width;
    const h = img.naturalHeight || img.height;
    if (!w || !h) throw new Error('Ukuran gambar tidak valid');

    const canvas = document.createElement('canvas');
    canvas.width = w; canvas.height = h;
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    ctx.imageSmoothingEnabled = false;
    ctx.fillStyle = '#fff'; ctx.fillRect(0,0,w,h);
    ctx.drawImage(img, 0, 0, w, h);
    return canvas;
  }

  async function renderCanvasByHtml2Canvas(){
    const scale = Math.max(2, Math.ceil(window.devicePixelRatio || 1));
    return html2canvas(shotNode, {
      backgroundColor: '#ffffff',
      allowTaint: true,
      scale
    });
  }

  async function renderSmart(){
    try{
      return await renderCanvasPreferImgOnly();
    }catch(e){
      console.warn('PreferImgOnly gagal, coba html2canvas:', e);
      return await renderCanvasByHtml2Canvas();
    }
  }

  async function shareOrDownload(canvas){
    const filename = 'qris-<?= $order_id ?>.png';
    try{
      const file = await fileFromCanvas(canvas, filename);
      if (navigator.canShare && navigator.canShare({ files:[file] })){
        await navigator.share({
          files: [file],
          title: 'QRIS Pembayaran',
          text: 'Simpan QR ini ke galeri, lalu bayar sesuai nominal.'
        });
        return;
      }
    }catch(err){
      console.warn('Share gagal / tidak didukung:', err);
    }
    saveCanvasAsPng(canvas, filename);
  }

  async function handleShare(){
    try{
      const canvas = await renderSmart();
      await shareOrDownload(canvas);
    }catch(err){
      console.error(err);
      alert('Gagal membuat screenshot. Pastikan gambar QR sudah termuat dan coba lagi.');
    }
  }

  async function handleDownload(){
    try{
      const canvas = await renderSmart();
      saveCanvasAsPng(canvas, 'qris-<?= $order_id ?>.png');
    }catch(err){
      console.error(err);
      alert('Gagal mengunduh gambar.');
    }
  }

  btnShare.addEventListener('click', handleShare);
  btnDl?.addEventListener('click', handleDownload);
})();
</script>
<script>
(function(){
  // Hanya aktif kalau belum paid/canceled dari server-side (optional guard)
  var ORDER_REF = "<?= isset($order->nomor) ? rawurlencode($order->nomor) : (int)$order_id ?>";
  if (!ORDER_REF) return;

  var POLL_MS   = 7000;           // interval polling 7 detik
  var MAX_WAIT  = 10 * 60 * 1000; // safety stop 10 menit (opsional)
  var startTs   = Date.now();
  var timer     = null;
  var stop      = function(){ if (timer) { clearTimeout(timer); timer=null; } };

  async function check(){
    // safety stop
    if (Date.now() - startTs > MAX_WAIT) { stop(); return; }

    try{
      // tambahkan cache buster query
      const url = "<?= site_url('produk/order_status/') ?>" + ORDER_REF + "?t=" + Date.now();
      const res = await fetch(url, { cache:'no-store', headers:{ 'Accept':'application/json' } });
      if (!res.ok) throw new Error('HTTP '+res.status);
      const js = await res.json();
      if (!js || !js.success) throw new Error(js && js.error ? js.error : 'unknown');

      var st = (js.status||'').toLowerCase();
      if (st === 'paid'){
        stop();
        // Redirect ke halaman sukses (pakai nomor biar konsisten)
        window.location.href = "<?= site_url('produk/order_success/') ?>" + encodeURIComponent(js.nomor || "<?= $order_id ?>");
        return;
      }
      if (st === 'canceled'){
        stop();
        // Bisa diarahkan balik ke order_success juga (nanti view menampilkan status canceled)
        window.location.href = "<?= site_url('produk/order_success/') ?>" + encodeURIComponent(js.nomor || "<?= $order_id ?>");
        return;
      }

      // selain itu (pending/verifikasi): lanjut polling
      timer = setTimeout(check, POLL_MS);
    }catch(err){
      // Jeda sedikit lebih lama jika error jaringan agar tidak spam
      timer = setTimeout(check, POLL_MS * 2);
      console.warn('poll error:', err);
    }
  }

  // Mulai polling hanya bila tab aktif, supaya hemat
  function onVisChange(){
    if (document.visibilityState === 'visible'){
      if (!timer) { check(); }
    } else {
      stop();
    }
  }
  document.addEventListener('visibilitychange', onVisChange);

  // Kick off pertama
  if (document.visibilityState === 'visible') check();
})();
</script>
<?php
// aktifkan polling HANYA untuk metode CASH & selama belum paid/canceled
$__method_now = strtolower(trim((string)($order->paid_method ?? $locked_method ?? '')));
if ($__method_now === 'cash' && !in_array(strtolower($order->status ?? 'pending'), ['paid','canceled'], true)):
?>
<script>
(function(){
  // Referensi order (nomor > kode > id)
  var ORDER_REF = "<?= isset($order->nomor) ? rawurlencode($order->nomor) : (string)($order->kode ?? $order->id ?? '') ?>";
  if (!ORDER_REF) return;

  var POLL_MS  = 7000;            // cek tiap 7 detik
  var MAX_WAIT = 10*60*1000;      // stop 10 menit
  var startTs  = Date.now();
  var t        = null;

  function stop(){ if (t){ clearTimeout(t); t=null; } }

  async function check(){
    if (Date.now() - startTs > MAX_WAIT) { stop(); return; }

    try{
      const url = "<?= site_url('produk/order_status/') ?>" + ORDER_REF + "?t=" + Date.now();
      const res = await fetch(url, { cache:"no-store", headers:{ "Accept":"application/json" }});
      if (!res.ok) throw new Error("HTTP " + res.status);
      const js = await res.json();
      if (!js || !js.success) throw new Error(js && js.error ? js.error : "unknown");

      var st = (js.status||"").toLowerCase();

      if (st === "paid" || st === "canceled"){
        stop();
        // KHUSUS CASH: cukup reload halaman supaya view menampilkan status terbaru
        location.reload();
        return;
      }

      // masih pending/verifikasi => lanjut polling
      t = setTimeout(check, POLL_MS);
    }catch(err){
      console.warn("order_status poll error:", err);
      t = setTimeout(check, POLL_MS * 2);
    }
  }

  // Poll hanya saat tab aktif
  function onVis(){ if (document.visibilityState === "visible"){ if (!t) check(); } else { stop(); } }
  document.addEventListener("visibilitychange", onVis);

  if (document.visibilityState === "visible") check();
})();
</script>
<?php endif; ?>

