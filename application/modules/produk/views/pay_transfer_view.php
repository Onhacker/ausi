<?php $this->load->view("front_end/head.php"); ?>
<div class="container-fluid">
  <div class="hero-title">
    <h1 class="text">Pembayaran via Transfer</h1>
    <!-- <div class="text-white">Silakan transfer sesuai nominal total bayar di bawah ini.</div> -->
    <span class="accent" aria-hidden="true"></span>
  </div>

<?php
  // ===== hitung konteks & total tampilan =====
  $is_delivery   = strtolower($order->mode ?? '') === 'delivery';
$delivery_fee  = (int)($order->delivery_fee ?? 0);
$subtotal_view = (int)($total ?? 0);
// pastikan ambil kode_unik yang sudah diset di _set_verifikasi('transfer')
$kode_unik     = (int)($kode_unik ?? ($order->kode_unik ?? 0));

// voucher
$voucher_disc = (int)($order->voucher_disc ?? 0);
$voucher_code = trim((string)($order->voucher_code ?? ''));
$has_voucher  = ($voucher_disc > 0);

// fallback kalau grand_total belum ikut terset di DB
$subtotal_after_voucher = $subtotal_view - $voucher_disc;
if ($subtotal_after_voucher < 0) $subtotal_after_voucher = 0;

$grand_fallback = $subtotal_after_voucher + ($is_delivery ? $delivery_fee : 0) + $kode_unik;
$grand_display  = (int)($grand_total ?? $order->grand_total ?? $grand_fallback);


  // status & ref (untuk polling)
  $status_now = strtolower($order->status ?? 'pending');
  $order_ref  = isset($order->nomor) ? rawurlencode($order->nomor) : (string)($order->kode ?? $order->id ?? '');
  $order_nomor = trim((string)($order->nomor ?? ''));
$show_change_method_btn = ($order_nomor !== '' && !in_array($status_now, ['paid','canceled'], true));

?>

<style>
  /* ========= Cosmetics tambahan (ringan & responsif) ========= */
  .card{ border-radius:14px; border:1px solid #eef2f7; }
  .section-title{ font-weight:700; font-size:1.05rem; }
  .soft{ background:#f8fafc; border-radius:12px; padding:.6rem .75rem; }
  .money-lg{ font-weight:800; font-size:1.9rem; letter-spacing:.2px; }
  .mini-hint{ font-size:.85rem; color:#6b7280; }

  /* Panel total & highlight */
  .total-panel{ background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:.9rem; }
  .total-panel .line{ display:flex; justify-content:space-between; align-items:center; margin:.25rem 0; }
  .total-highlight{
    border:1px dashed #fecaca; background:#fff1f2; border-radius:12px; padding:.75rem .9rem;
  }

  /* Bank list */
  .bank-item{ border:1px solid #eef2f7; border-radius:12px; padding:.75rem .9rem; display:flex; justify-content:space-between; align-items:flex-start; }
  .bank-item + .bank-item{ margin-top:.6rem; }
  .bank-title{ font-weight:700; }
  .bank-rek{ background:#f9fafb; border-radius:8px; padding:.15rem .5rem; display:inline-block; }

  /* Chips status sederhana (opsional kalau mau dipakai) */
  .chip{ display:inline-flex; align-items:center; gap:.4rem; padding:.22rem .6rem; border-radius:999px; font-size:.8rem; font-weight:700; }
  .chip-warn{ background:#fffbeb; color:#92400e; border:1px solid #fde68a; }

  /* Copy buttons */
  .btn-icon{ display:inline-flex; align-items:center; gap:.35rem; }

  /* Responsif kecil */
  @media (max-width: 575.98px){
    .money-lg{ font-size:1.6rem; }
  }
</style>

  <div class="row">
    <!-- Kolom kiri: Info pembayaran (TOTAL + REKENING disatukan) -->
<div class="col-md-6">
  <div class="card card-body mb-3">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <?php if (!in_array($status_now, ['paid','canceled'], true)): ?>
        <span class="chip chip-warn">
          <i class="mdi mdi-timer-sand"></i> Menunggu Transfer
        </span>
      <?php endif; ?>
    </div>

    <?php if ($show_change_method_btn): ?>
      <div class="mb-2">
        <button type="button"
                class="btn btn-outline-danger btn-rounded btn-sm"
                id="btn-ubah-metode"
                data-nomor="<?= html_escape($order_nomor) ?>">
          <i class="mdi mdi-refresh"></i> Ganti Metode Pembayaran
        </button>
        <small class="text-muted d-block mt-1">
          Jika salah pilih metode, klik ini untuk kembali ke pilihan pembayaran.
        </small>
      </div>
    <?php endif; ?>

    <!-- ===== Total Panel ===== -->
    <div class="total-panel">
      <div class="line">
        <span class="text-dark">Subtotal</span>
        <span>Rp <?= number_format($subtotal_view,0,',','.') ?></span>
      </div>

      <?php if ($has_voucher): ?>
        <div class="line">
          <span class="text-dark">
            Voucher<?php if ($voucher_code): ?> (<?= html_escape($voucher_code) ?>)<?php endif; ?>
          </span>
          <span class="text-danger">- <?= number_format($voucher_disc,0,',','.') ?></span>
        </div>
      <?php endif; ?>

      <?php if ($is_delivery && $delivery_fee > 0): ?>
        <div class="line">
          <span class="text-dark">Ongkir</span>
          <span>+ <?= number_format($delivery_fee,0,',','.') ?></span>
        </div>
      <?php endif; ?>

      <?php if ($kode_unik > 0): ?>
        <div class="line">
          <span class="text-dark">Kode Unik</span>
          <span>+ <?= number_format($kode_unik,0,',','.') ?></span>
        </div>
      <?php endif; ?>
    </div>

    <div class="total-highlight mt-2">
      <div class="d-flex justify-content-between align-items-center">
        <div class="h6 mb-0">Total Bayar</div>
        <div class="text-right">
          <div class="money-lg">Rp <?= number_format($grand_display,0,',','.') ?></div>
          <button
            type="button"
            class="btn btn-sm btn-outline-secondary mt-1 js-copy btn-icon"
            data-copy="<?= (int)$grand_display ?>"
            aria-label="Salin total bayar">
            <i class="mdi mdi-content-copy"></i> Salin Total
          </button>
        </div>
      </div>
     <!--  <div class="mini-hint mt-2 text-danger">
        * Transfer tepat sesuai <strong>angka unik</strong> agar kasir mudah mencocokkan.
      </div> -->
    </div>

    <!-- <hr class="my-1"> -->

    <!-- ===== Rekening Tujuan ===== -->
    <!-- <h4 class="mb-2"><strong>Rekening Tujuan</strong></h4> -->

    <?php if (!empty($bank_list)): ?>
      <?php foreach($bank_list as $b):
            $rek    = preg_replace('/\s+/', '', $b['no_rek']); // tanpa spasi
            $rekRaw = preg_replace('/[^0-9]/', '', $rek);      // hanya angka
      ?>
        <div class="bank-item mt-2">
          <div>
            <div class="bank-title">Rek. Tujuan<br><?= html_escape($b['bank']) ?></div>
            <div class="text-dark">a.n. <?= html_escape($b['atas_nama']) ?></div>
            <div class="mt-1">
              <code class="bank-rek"><?= html_escape($b['no_rek']) ?></code>
            </div>
          </div>
          <div class="ml-3">
            <button
              type="button"
              class="btn btn-sm btn-outline-secondary js-copy btn-icon"
              data-copy="<?= html_escape($rekRaw) ?>"
              aria-label="Salin nomor rekening <?= html_escape($b['bank']) ?>">
              <i class="mdi mdi-content-copy"></i> Salin Rek.
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="alert alert-warning mb-0">Data rekening tujuan belum tersedia.</div>
    <?php endif; ?>

    <div class="alert alert-danger mt-1 mb-0">
      * Transfer tepat sesuai <strong>Rp <?= number_format($grand_display,0,',','.') ?></strong> agar kasir mudah mencocokkan.
    </div>
  </div>
</div>

    <!-- Kolom kanan: Ringkasan pesanan -->
    <div class="col-md-6">
      <div class="card card-body mb-3">
        <h3 class="mb-3">Ringkasan Pesanan</h3>
        <?php if (!empty($items)): ?>
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
        <?php else: ?>
          <div class="text-dark">Tidak ada item.</div>
        <?php endif; ?>

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

        <div class="soft mt-3">
          <div class="mini-hint">
            Setelah transfer, kasir akan memverifikasi pembayaran kamu. Halaman ini akan otomatis
            diarahkan ke <em>struk</em> ketika status sudah <strong>LUNAS</strong>.
          </div>
        </div>
      </div>

      <?php if (!empty($meja_info)): ?>
      <div class="card card-body">
        <div class="text-dark">Meja</div>
        <div class="h3 mb-0"><?= html_escape($meja_info) ?></div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php $this->load->view("front_end/footer.php"); ?>

<script>
// Utility salin ke clipboard (Clipboard API jika ada, fallback textarea)
(function(){
  function copyText(text){
    if (navigator.clipboard && window.isSecureContext) {
      return navigator.clipboard.writeText(text);
    }
    // fallback
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
    btn.innerHTML = ok ? '<i class="mdi mdi-check"></i> Tersalin' : '<i class="mdi mdi-close"></i> Gagal';
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

<?php if ($order_ref && !in_array($status_now, ['paid','canceled'], true)): ?>
<script>
(function(){
  // ===== Polling status pembayaran (transfer) =====
  var ORDER_REF = "<?= $order_ref ?>";
  var POLL_MS   = 7000;         // cek tiap 7 detik
  var MAX_MS    = 10*60*1000;   // stop otomatis 10 menit
  var startTs   = Date.now();
  var timer     = null;

  function stop(){ if (timer){ clearTimeout(timer); timer = null; } }

  async function check(){
    if (Date.now() - startTs > MAX_MS) { stop(); return; }

    try{
      const url = "<?= site_url('produk/order_status/') ?>" + ORDER_REF + "?t=" + Date.now();
      const res = await fetch(url, { cache:"no-store", headers:{ "Accept":"application/json" }});
      if (!res.ok) throw new Error("HTTP " + res.status);
      const js = await res.json();
      if (!js || !js.success) throw new Error(js && js.error ? js.error : "unknown");

      var st = (js.status || "").toLowerCase();

      if (st === "paid"){
        stop();
        // Langsung ke struk biar user yakin transaksi sukses
        window.location.href = "<?= site_url('produk/receipt/') ?>" + encodeURIComponent(js.nomor || "<?= (string)($order->nomor ?? $order->id) ?>");
        return;
      }

      if (st === "canceled"){
        stop();
        // Kembali ke halaman ringkasan (akan render status canceled)
        window.location.href = "<?= site_url('produk/order_success/') ?>" + encodeURIComponent(js.nomor || "<?= (string)($order->nomor ?? $order->id) ?>");
        return;
      }

      // masih pending / verifikasi => lanjut polling
      timer = setTimeout(check, POLL_MS);
    }catch(err){
      console.warn("order_status poll error:", err);
      // kalau error jaringan, coba lagi dengan jeda sedikit lebih lama
      timer = setTimeout(check, POLL_MS * 2);
    }
  }

  // Poll hanya saat tab aktif (hemat resource)
  function onVis(){ if (document.visibilityState === "visible"){ if (!timer) check(); } else { stop(); } }
  document.addEventListener("visibilitychange", onVis);

  if (document.visibilityState === "visible") check();
})();
</script>
<?php endif; ?>
<script>
(function(){
  const btn = document.getElementById('btn-ubah-metode');
  if (!btn) return;

  let CSRF_NAME = "<?= $this->security->get_csrf_token_name(); ?>";
  let CSRF_HASH = "<?= $this->security->get_csrf_hash(); ?>";

  const nomor = btn.getAttribute('data-nomor') || '';
  if (!nomor) return;

  async function postReset(){
    const form = new URLSearchParams();
    form.append('nomor', nomor);
    form.append(CSRF_NAME, CSRF_HASH);

    const res = await fetch("<?= site_url('produk/change_payment_method'); ?>", {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        'Accept': 'application/json'
      },
      body: form.toString(),
      credentials: 'same-origin'
    });

    const js = await res.json().catch(() => null);
    if (js && js.csrf) { CSRF_NAME = js.csrf.name; CSRF_HASH = js.csrf.hash; }
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return js;
  }

  async function run(){
    btn.disabled = true;
    try{
      const r = await postReset();
      if (r && r.ok){
        window.location.href = r.redirect || ("<?= site_url('produk/order_success/') ?>" + encodeURIComponent(nomor));
        return;
      }
      alert((r && r.msg) ? r.msg : 'Gagal mengGanti Metode Pembayaran.');
    }catch(e){
      console.error(e);
      alert('Gagal menghubungi server.');
    }finally{
      btn.disabled = false;
    }
  }

  btn.addEventListener('click', function(){
    if (window.Swal){
      Swal.fire({
        icon: 'warning',
        title: 'Ganti Metode Pembayaran?',
        html: 'Status pesanan akan dikembalikan ke <b>PENDING</b> agar kamu bisa pilih metode bayar lagi.',
        showCancelButton: true,
        confirmButtonText: 'Ya, ubah',
        cancelButtonText: 'Batal',
        reverseButtons: true
      }).then(x => { if (x.isConfirmed) run(); });
    } else {
      if (confirm('Ganti Metode Pembayaran? Status akan kembali ke PENDING.')) run();
    }
  });
})();
</script>

