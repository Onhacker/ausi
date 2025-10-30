<?php $this->load->view("front_end/head.php"); ?>
<div class="container-fluid">
  <div class="hero-title">
    <h1 class="text">Pembayaran via QRIS</h1>
    <!-- <div class="text-muted">Silakan scan QRIS dan bayar sesuai total di bawah ini, lalu tunggu verifikasi kasir.</div> -->
    <span class="accent" aria-hidden="true"></span>
  </div>

<?php
  // ===== Konteks & total tampilan =====
  $order_id      = (int)($order->id_pesanan ?? 0);
  $kode_unik     = (int)($kode_unik ?? ($order->kode_unik ?? 0));
  $subtotal_view = (int)($total ?? ($order->subtotal ?? 0));
  $grand_display = (int)($grand_total ?? ($order->grand_total ?? ($subtotal_view + $kode_unik)));
  $token         = (string)($order->access_token ?? '');
  $status_now    = strtolower((string)($order->status ?? ''));
?>

  <div class="row">
    <!-- Kolom kiri: QRIS + nominal -->
    <div class="col-md-12">
      <div class="card card-body ">
        <!-- <h5 class="mb-1">Scan QRIS</h5> -->
        <div class="text-center">
        <div id="pay-deadline"
     class="text-dark alert alert-info"
     data-deadline-ms="<?= (int)$deadline_ts * 1000 ?>">
  Waktu pembayaran sisa (<span id="countdown">--:--</span>)
</div>


<script>
(function(){
  const root = document.getElementById('pay-deadline');
  const cd   = document.getElementById('countdown');
  if (!root || !cd) return;

  // URL cart (embed dari PHP)
  const CART_URL = "<?= site_url('billiard/cart?t=') . urlencode($token) ?>";
  let didRedirect = false;

  const pad = n => n<10 ? '0'+n : n;
  const fmt = ms => {
    let s = Math.max(0, Math.floor(ms/1000));
    const h = Math.floor(s/3600); s%=3600;
    const m = Math.floor(s/60);   s%=60;
    return h>0 ? `${pad(h)}:${pad(m)}:${pad(s)}` : `${pad(m)}:${pad(s)}`;
  };

  function redirectCart(){
    if (didRedirect) return;
    didRedirect = true;
    window.location.href = CART_URL;
  }

  function tick(){
    // AMBIL deadline TERBARU dari dataset (bisa diperbarui oleh polling)
    const dl = Number(root.dataset.deadlineMs || 0);
    const diff = dl - Date.now();
    cd.textContent = fmt(diff);
    if (diff <= 0) redirectCart();
  }

  const t = setInterval(tick, 1000);
  tick();
})();
</script>




          <!-- Area yang akan di-screenshot -->
          <div id="qris-shot" style="display:inline-block; background:#fff; padding:8px; border-radius:8px;">
            <img
              id="qris-img"
              src="<?= site_url('billiard/qris_png/'.$order->access_token) ?>"
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
          </div>

          <small class="text-dark d-block mt-2">
            Kamu lagi bayar pakai QRIS. Mohon tetap di halaman ini sampai transaksi selesai. <strong>Kasir akan memvalidasi pembayaran anda di jam kerja.</strong>
          </small>

          <div class="alert alert-info mt-2 mb-0 text-left" role="alert" style="max-width:420px; margin:10px auto 0;">
            <div class="font-weight-bold mb-1">Tips bila booking & membayar dari perangkat yang sama:</div>
            <ol class="mb-0 pl-3">
              <li>Tekan <strong>Screenshot</strong> untuk menyimpan QR ke Galeri.</li>
              <li>Buka aplikasi pembayaran Anda (bank/e-wallet).</li>
              <li>Pilih menu <strong>Scan</strong> lalu opsi <strong>Ambil dari Galeri</strong>.</li>
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

      <?php if (!empty($meja_info)): ?>
      <div class="card card-body">
        <div class="small text-dark">Meja Billiard</div>
        <div class="h4 mb-0"><?= html_escape($meja_info) ?></div>
      </div>
      <?php endif; ?>
    </div>

    <!-- KOLom kanan DIHAPUS sesuai permintaan -->
  </div>
</div>
<?php $this->load->view("front_end/footer.php"); ?>

<!-- html2canvas CDN -->
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

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
</script>

<script>
// ===== Screenshot QRIS (share / download) =====
(function(){
  const btnShare  = document.getElementById('btn-screenshot-qris');
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

  btnShare.addEventListener('click', handleShare);
})();
</script>

<?php if (!empty($token) && !in_array($status_now, ['terkonfirmasi','batal'], true)): ?>
<script>
(function(){
  var TOKEN   = "<?= htmlspecialchars($token, ENT_QUOTES) ?>";
  var POLL_MS = 7000;          // cek tiap 7 detik
  var MAX_MS  = 10*60*1000;    // hentikan polling setelah 10 menit
  var startTs = Date.now();
  var timer   = null;
  var didRedirect = false;

  const CART_URL   = "<?= site_url('billiard/cart?t=') . urlencode($token) ?>";
  const EXPIRED_URL= "<?= site_url('billiard/expired') ?>";

  function stop(){ if (timer){ clearTimeout(timer); timer = null; } }
  function redirectOnce(url){
    if (didRedirect) return;
    didRedirect = true;
    window.location.href = url;
  }

  async function check(){
    if (Date.now() - startTs > MAX_MS) { stop(); return; }

    try{
      const url = "<?= site_url('billiard/status') ?>?t=" + encodeURIComponent(TOKEN) + "&_=" + Date.now();
      const res = await fetch(url, { cache:"no-store", headers:{ "Accept":"application/json" }});
      if (!res.ok) throw new Error("HTTP " + res.status);
      const js = await res.json();
      if (!js || !js.success) throw new Error(js && js.error ? js.error : "unknown");

      // Sinkronisasi countdown (visual only)
      const root = document.getElementById('pay-deadline');
      const cd   = document.getElementById('countdown');
      if (root && js.deadline_ts) {
        // server kirim detik → konversi ke ms
        root.dataset.deadlineMs = String(Number(js.deadline_ts)); // ✅ sudah ms dari server

      }
      if (cd && typeof js.remaining_ms === 'number') {
        const pad=n=>n<10?'0'+n:n;
        const fmt=ms=>{let s=Math.max(0,ms/1000|0),h=s/3600|0,m=(s%3600)/60|0,x=s%60;
          return h>0?`${pad(h)}:${pad(m)}:${pad(x)}`:`${pad(m)}:${pad(x)}`};
        cd.textContent = fmt(js.remaining_ms);
      }

      // Status → navigasi
      var st = (js.status || "").toLowerCase();

      if (st === "terkonfirmasi"){
        stop();
        return redirectOnce(CART_URL);
      }

      if (st === "batal"){
        stop();
        return redirectOnce(EXPIRED_URL);
      }

      // Jika masih draft/verifikasi tapi countdown server sudah <= 0 → redirect ke cart
      // (countdown di UI bersifat tampilan; ini hanya untuk alur UX)
      if (typeof js.remaining_ms === 'number' && js.remaining_ms <= 0){
        stop();
        return redirectOnce(CART_URL);
      }

      // lanjut polling
      timer = setTimeout(check, POLL_MS);
    }catch(err){
      console.warn("billiard/status poll error:", err);
      timer = setTimeout(check, POLL_MS * 2);
    }
  }

  function onVis(){ if (document.visibilityState === "visible"){ if (!timer) check(); } else { stop(); } }
  document.addEventListener("visibilitychange", onVis);

  if (document.visibilityState === "visible") check();
})();
</script>
<?php endif; ?>


