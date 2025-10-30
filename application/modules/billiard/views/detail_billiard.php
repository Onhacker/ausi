<div class="container">
  <h3 class="mb-3"><?= html_escape($title) ?></h3>
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <div class="d-flex justify-content-between">
        <div>
          <div class="fw-bold">Kode: <span class="badge bg-dark"><?= $rec->kode_booking ?></span></div>
          <div><?= html_escape($rec->nama) ?> • <?= html_escape($rec->no_hp) ?></div>
          <div>Meja: <?= html_escape($meja->nama_meja) ?></div>
          <div>Tanggal/Jam: <?= $rec->tanggal ?>, <?= substr($rec->jam_mulai,0,5) ?>–<?= substr($rec->jam_selesai,0,5) ?> (<?= (int)$rec->durasi_jam ?> jam)</div>
          <div>Status: <b><?= strtoupper(str_replace('_',' ',$rec->status)) ?></b></div>
        </div>
        <div class="text-end">
          <div class="fw-bold fs-5">Total</div>
          <div class="fs-3">Rp<?= number_format($rec->subtotal,0,',','.') ?></div>
        </div>
      </div>
      <hr>
      <div class="d-flex gap-2">
        <a href="<?= site_url('billiard/cart') ?>?t=<?= urlencode($rec->access_token) ?>" class="btn btn-outline-secondary">Edit</a>
        <a href="<?= site_url('billiard/metode') ?>?t=<?= urlencode($rec->access_token) ?>" class="btn btn-primary">Metode Bayar</a>
      </div>
    </div>
  </div>
</div>
