<div class="container">
  <h3 class="mb-3"><?= html_escape($title) ?></h3>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <div class="mb-3">
        <div class="fw-bold">Ringkasan</div>
        <div><?= html_escape($rec->nama) ?> • <span class="text-muted"><?= html_escape($rec->no_hp) ?></span></div>
        <div>Meja: <?= html_escape($meja->nama_meja) ?> (Rp<?= number_format($rec->harga_per_jam,0,',','.') ?>/jam)</div>
        <div>Waktu: <?= $rec->tanggal ?>, <?= substr($rec->jam_mulai,0,5) ?>–<?= substr($rec->jam_selesai,0,5) ?> (<?= (int)$rec->durasi_jam ?> jam)</div>
        <div class="fs-4 mt-1">Total: <b>Rp<?= number_format($rec->subtotal,0,',','.') ?></b></div>
      </div>

      <form id="frmMetode" method="post" action="<?= site_url('billiard/konfirmasi') ?>">
        <input type="hidden" name="t" value="<?= html_escape($rec->access_token) ?>">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label fw-bold">Metode</label>
            <select name="metode_bayar" class="form-select">
              <option value="cash">Cash di Kasir</option>
              <option value="qris">QRIS</option>
              <option value="transfer">Transfer</option>
            </select>
            <small class="text-muted">QRIS/Transfer → status “menunggu bayar”.</small>
          </div>
          <div class="col-md-8 d-flex align-items-end justify-content-end">
            <a class="btn btn-outline-secondary me-2" href="<?= site_url('billiard/cart') ?>?t=<?= urlencode($rec->access_token) ?>">Edit</a>
            <button class="btn btn-primary px-4">Konfirmasi</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('frmMetode').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const fd = new FormData(e.target);
  const r = await fetch(e.target.action, {method:'POST', body:fd});
  const j = await r.json();
  alert((j.title? j.title+'\n':'')+(j.pesan? j.pesan:''));
  if (j.success && j.redirect_url) location.href = j.redirect_url;
});
</script>
