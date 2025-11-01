<?php $this->load->view("front_end/head.php"); ?>

<div class="container-fluid">
  <div class="hero-title" role="banner" aria-label="Judul situs">
    <?php $this->load->view("front_end/back") ?>
    <h1 class="text">Booking Gratis Berhasil</h1>
    <span class="accent" aria-hidden="true"></span>
  </div>

  <div class="alert alert-success mb-3">
    <strong>Asyik!</strong> Voucher kamu diterima. Tagihan <b>Rp 0</b> (gratis).
    Tunjukkan <b>kode booking</b> ke kasir ya.
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card-box">
        <div class="card-body" id="ticketAreac">
          <?php
            $b = $booking ?? (object)[];
            $kode  = htmlspecialchars($b->kode_booking ?? '-', ENT_QUOTES, 'UTF-8');
            $nama  = htmlspecialchars($b->nama ?? '-', ENT_QUOTES, 'UTF-8');
            $hp    = htmlspecialchars($b->no_hp ?? '-', ENT_QUOTES, 'UTF-8');
            $mejaN = htmlspecialchars(($meja->nama_meja ?? 'MEJA #'.($b->meja_id ?? '')), ENT_QUOTES, 'UTF-8');
            $tgl   = htmlspecialchars($b->tanggal ?? '-', ENT_QUOTES, 'UTF-8');
            $mulai = htmlspecialchars(substr($b->jam_mulai ?? '00:00:00',0,5), ENT_QUOTES, 'UTF-8');
            $seles = htmlspecialchars(substr($b->jam_selesai ?? '00:00:00',0,5), ENT_QUOTES, 'UTF-8');
            $dur   = (int)($b->durasi_jam ?? 0);
            $harga = (int)($b->harga_per_jam ?? 0);
            $sub   = (int)($b->subtotal ?? 0);
          ?>

          <!-- === AREA UTAMA (akan ikut screenshot) === -->
          <div id="ticketArea">
            <h4 class="mb-3">Ringkasan Booking</h4>

            <div class="table-responsive">
              <table class="table table-sm mb-0">
                <tbody>
                  <tr>
                    <th style="width:220px;">Kode Booking</th>
                    <td>
                      <span id="kodeBooking"
                            class="badge badge-primary font-weight-bold"
                            style="font-size:1rem; letter-spacing:.5px;">
                        <?php echo $kode; ?>
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <th>Nama</th>
                    <td><?php echo $nama; ?></td>
                  </tr>
                  <tr>
                    <th>Nomor HP</th>
                    <td><?php echo $hp; ?></td>
                  </tr>
                  <tr>
                    <th>Meja</th>
                    <td><?php echo $mejaN; ?></td>
                  </tr>
                  <tr>
                    <th>Tanggal</th>
                    <td><?php echo $tgl; ?></td>
                  </tr>
                  <tr>
                    <th>Jam</th>
                    <td>
                      <?php echo $mulai.' – '.$seles; ?>
                      (<?php echo $dur; ?> jam)
                    </td>
                  </tr>
                  <tr>
                    <th>Tarif/Jam (informasi)</th>
                    <td>Rp <?php echo number_format($harga,0,',','.'); ?></td>
                  </tr>
                  <tr>
                    <th>Subtotal Asli (informasi)</th>
                    <td>Rp <?php echo number_format($sub,0,',','.'); ?></td>
                  </tr>
                  <tr class="table-success">
                    <th>Total Bayar</th>
                    <td>
                      <strong>Rp 0</strong> (pakai voucher)
                    </td>
                  </tr>
                  <tr>
                    <th>Status</th>
                    <td>
                      <span class="badge badge-success">free</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <hr>
          </div>
          <!-- === /AREA UTAMA === -->

          <!-- Tombol + note kita tandai .no-shot supaya TIDAK ikut di screenshot -->
          <div class="mt-3 no-shot">
            <button type="button" class="btn btn-blue" id="btnScreenshot">
              <i class="mdi mdi-camera"></i> Screenshot
            </button>
            <div class="text-muted small mt-1">
              Simpan ringkasan ini sebagai bukti booking.
            </div>
          </div>

        </div><!-- /card-body -->
      </div><!-- /card-box -->
    </div><!-- /col -->
  </div><!-- /row -->
</div><!-- /container-fluid -->

<!-- html2canvas CDN (cukup sekali) -->
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

<script>
(function(){
  var btn = document.getElementById('btnScreenshot');
  if (!btn) return;

  // Tambahan safety, walau kita juga pakai ignoreElements
  btn.setAttribute('data-html2canvas-ignore', 'true');

  btn.addEventListener('click', function(){
    // TARGET: ticketAreac → supaya dapet padding card-body (lebih lega)
    var area = document.getElementById('ticketAreac');
    if (!area){
      alert('Ringkasan tidak ditemukan.');
      return;
    }

    html2canvas(area, {
      scale: Math.min(2, window.devicePixelRatio || 2),
      useCORS: true,
      backgroundColor: '#ffffff',

      // KUNCI: elemen yg punya class .no-shot jangan dirender
      ignoreElements: function(node){
        return node.classList && node.classList.contains('no-shot');
      }
    }).then(function(canvas){
      var kodeEl = document.getElementById('kodeBooking');
      var kode = (kodeEl?.textContent || 'booking').trim() || 'booking';

      if (canvas.toBlob) {
        canvas.toBlob(function(blob){
          var a = document.createElement('a');
          a.href = URL.createObjectURL(blob);
          a.download = 'booking_free_' + kode + '.png';
          document.body.appendChild(a);
          a.click();
          URL.revokeObjectURL(a.href);
          a.remove();
        });
      } else {
        var a = document.createElement('a');
        a.href = canvas.toDataURL('image/png');
        a.download = 'booking_free_' + kode + '.png';
        document.body.appendChild(a);
        a.click();
        a.remove();
      }
    });
  });
})();
</script>

<?php $this->load->view("front_end/footer.php"); ?>
