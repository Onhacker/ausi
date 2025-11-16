<?php $this->load->view("front_end/head.php"); ?>

<?php
  // ===== PHP helpers yg sdh ada di file kamu =====
  $subtotal_now = (int)($booking->subtotal ?? 0);
  $kode_unik    = (int)($booking->kode_unik ?? 0);
  $grand_total  = (int)($booking->grand_total ?? ($subtotal_now + $kode_unik));

  $st_raw   = strtolower((string)($booking->status ?? ''));
  $is_draft = ($st_raw === 'draft');
  $is_terkonfirmasi = ($st_raw === 'terkonfirmasi');

  $status_map = [
    'draft'         => ['Menunggu Pembayaran', 'warning',  'mdi-timer-sand'],
    'verifikasi'    => ['Pembayaran sedang diverifikasi', 'info',     'mdi-information-outline'],
    'terkonfirmasi' => ['Lunas', 'success', 'mdi-check-circle-outline'],
    'selesai'       => ['Selesai', 'success', 'mdi-check-circle-outline'],
    'canceled'      => ['Dibatalkan', 'danger','mdi-close-circle-outline'],
    'batal'         => ['Dibatalkan', 'danger','mdi-close-circle-outline'],
  ];
  [$status_text, $status_variant, $status_icon] = $status_map[$st_raw] ?? ['-', 'secondary','mdi-information-outline'];

  $pay_raw = strtolower((string)($booking->metode_bayar ?? $booking->payment_method ?? $booking->metode ?? $booking->pay_method ?? ''));
  $pay_map = [
    'qris'          => ['QRIS', 'mdi-qrcode-scan'],
    'transfer'      => ['Transfer Bank', 'mdi-bank-transfer'],
    'bank_transfer' => ['Transfer Bank', 'mdi-bank-transfer'],
    'cash'          => ['Tunai', 'mdi-cash'],
    'tunai'         => ['Tunai', 'mdi-cash'],
    'debit'         => ['Kartu Debit', 'mdi-credit-card-outline'],
    'kartu'         => ['Kartu', 'mdi-credit-card-outline'],
  ];
  $admin_user = strtolower((string)($this->session->userdata('admin_username') ?? ''));
  $is_kasir   = ($admin_user === 'kasir');

  // tombol pay muncul bila status draft & ada token (untuk kasir maupun non-session)
  $allow_statuses = ['draft'];
  $show_pay_buttons = isset($booking)
  && !empty($booking->access_token)
  && isset($booking->status)
  && in_array(strtolower((string)$booking->status), $allow_statuses, true);

  [$pay_text, $pay_icon] = $pay_map[$pay_raw] ?? ['-', 'mdi-credit-card-outline'];

  $tgl_disp = '';
  if (!empty($booking->tanggal)) {
    $ts = @strtotime($booking->tanggal);
    if ($ts !== false && $ts !== -1) $tgl_disp = date('d/m/Y', $ts);
  }

  // buat tampilan meja, jam, dll
  $nama_meja = $booking->nama_meja
      ?? ($meja->nama_meja ?? ('MEJA #'.($booking->meja_id ?? $meja->id_meja ?? '')));
  $id_meja_disp = (int)($booking->meja_id ?? $meja->id_meja ?? 0);

  $jam_mulai   = $booking->jam_mulai   ?? '00:00:00';
  $jam_selesai = $booking->jam_selesai ?? '00:00:00';
  $durasi_jam  = (int)($booking->durasi_jam ?? 0);

  $tarif_per_jam = (int)($booking->harga_per_jam ?? $meja->harga_per_jam ?? 0);

  // untuk tombol batal
  $hide_cancel = in_array($st_raw, ['terkonfirmasi','verifikasi'], true);

  // tombol pay?

  // deadline bayar
  if (isset($rec) && isset($rec->late_min)) {
    $late_min = (int)($rec->late_min ?? 15);
  } else {
    $late_min = 15;
  }
  $updated_raw = $booking->updated_at ?? 'now';
  $updated_ts  = is_numeric($updated_raw) ? (int)$updated_raw : (strtotime((string)$updated_raw) ?: time());
  $deadline_ts = $updated_ts + ($late_min * 60);

  // data utk edit section
  $edit_allowed = $edit_allowed ?? null;

  // tanggal asli utk datetime attr
  $tanggal_val = $booking->tanggal ?? '';
  $tanggal_display = $tgl_disp ?: ($tanggal_val ? date('d/m/Y', strtotime($tanggal_val)) : '-');
?>

<div class="container-fluid">

  <!-- HERO -->
  <div class="hero-title" role="banner" aria-label="Judul situs">
    <?php $this->load->view("front_end/back") ?>
    <h1 class="text">Konfirmasi Booking</h1>
    <span class="accent" aria-hidden="true"></span>
  </div>

  <!-- ALERT STATUS (atas, mirip alert hijau di template pertama) -->
  <?php if ($st_raw === 'terkonfirmasi'): ?>
    <div class="alert alert-success mb-3">
      <strong>Lunas!</strong>
      Pembayaran kamu sudah diterima.
      Tunjukkan <b>kode booking</b> ke kasir saat datang ya 🙌
    </div>
  <?php elseif ($st_raw === 'draft'): ?>
    <div class="alert alert-warning mb-3">
      <strong>Menunggu Pembayaran.</strong>
      Segera lakukan pembayaran sebelum <b><?= date('H:i', $deadline_ts) ?> WITA</b>.
    </div>
  <?php elseif (in_array($st_raw,['verifikasi'],true)): ?>
    <div class="alert alert-info mb-3">
      <strong>Verifikasi Pembayaran.</strong>
      Kami sedang cek bukti transfer kamu.
    </div>
  <?php elseif (in_array($st_raw,['canceled','batal'],true)): ?>
    <div class="alert alert-danger mb-3">
      <strong>Dibatalkan.</strong>
      Slot meja kamu sudah dilepas.
    </div>
  <?php else: ?>
    <div class="alert alert-secondary mb-3">
      <strong>Status:</strong> <?= html_escape($status_text) ?>
    </div>
  <?php endif; ?>

 <!-- =================== BLOK AKSI BAYAR / COUNTDOWN =================== -->
 <?php if ($show_pay_buttons): ?>
  <style>
    /* Tata letak tombol biar rapi & responsif */
    .pay-actions { gap: 12px 14px; }
    .pay-actions .btn-pay {
      flex: 1 1 160px;           /* lebar fleksibel */
      min-width: 140px;          /* batas minimum */
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      letter-spacing: .2px;
    }
    .pay-summary {
      border: 1px dashed rgba(0,0,0,.12);
      border-radius: .5rem;
      padding: .75rem 1rem;
      background: #fcfcfd;
    }
    @media (min-width: 992px){
      .pay-head { display:flex; align-items:center; justify-content:space-between; }
    }

  </style>

  <div class="row">
    <div class="col-12">
      <div class="card-box">
        <div class="card-body">

          <div class="pay-head mb-3">
            <div class="pay-summary mr-lg-3 mb-2 mb-lg-0">
              <div class="text-muted small mb-1">Total Bayar</div>
              <div class="h3 m-0">
                <strong class="totalBayarVal">Rp <?= number_format($grand_total,0,',','.') ?></strong>
              </div>
            </div>

            <div id="pay-deadline"
                 class="text-dark alert alert-info mb-0"
                 data-deadline-ms="<?= $deadline_ts * 1000 ?>">
              Pilih salah satu metode di bawah.
              <span class="d-block d-lg-inline">
                Bayar sebelum <strong><?= date('H:i', $deadline_ts) ?> WITA</strong> —
                sisa waktu
                <strong><span id="countdown" aria-live="polite">--:--</span></strong>.
              </span>
            </div>
          </div>

         <div class="pay-actions">
            <?php if ($is_kasir): ?>
              <!-- Baris 1: Kasir - TUNAI full width di atas -->
              <div class="d-flex mb-2">
                <a class="btn btn-success btn-sm btn-pay js-pay btn-block"
                   href="<?= site_url('billiard/pay_cash/'.rawurlencode($booking->access_token)) ?>"
                   data-method="cash" aria-label="Bayar Tunai (kasir)">
                  <i class="mdi mdi-cash mr-1"></i> TUNAI
                </a>
              </div>

              <!-- Baris 2: QRIS dan TRANSFER kiri-kanan -->
              <div class="d-flex">
                <a class="btn btn-primary btn-sm btn-pay js-pay flex-fill mr-1"
                   href="<?= site_url('billiard/pay_qris/'.rawurlencode($booking->access_token)) ?>"
                   data-method="qris" aria-label="Bayar via QRIS">
                  <i class="mdi mdi-qrcode-scan mr-1"></i> QRIS
                </a>

                <a class="btn btn-info btn-sm btn-pay js-pay flex-fill ml-1"
                   href="<?= site_url('billiard/pay_transfer/'.rawurlencode($booking->access_token)) ?>"
                   data-method="transfer" aria-label="Bayar via Transfer Bank">
                  <i class="mdi mdi-bank-transfer mr-1"></i> TRANSFER BNI
                </a>
              </div>

            <?php else: ?>
              <!-- Bukan kasir: hanya QRIS & TRANSFER kiri-kanan -->
              <div class="d-flex">
                <a class="btn btn-blue btn-sm btn-pay js-pay flex-fill mr-1"
                   href="<?= site_url('billiard/pay_qris/'.rawurlencode($booking->access_token)) ?>"
                   data-method="qris" aria-label="Bayar via QRIS">
                  <i class="mdi mdi-qrcode-scan mr-1"></i> QRIS
                </a>

                <a class="btn btn-info btn-sm btn-pay js-pay flex-fill ml-1"
                   href="<?= site_url('billiard/pay_transfer/'.rawurlencode($booking->access_token)) ?>"
                   data-method="transfer" aria-label="Bayar via Transfer Bank">
                  <i class="mdi mdi-bank-transfer mr-1"></i> TRANSFER BNI
                </a>
              </div>
            <?php endif; ?>
          </div>


          <div class="text-muted small mt-2">
            Nominal sudah termasuk <em>kode unik</em> bila ada.
          </div>

        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

  <!-- =============== CARD RINGKASAN (TICKET STYLE) =============== -->
  <div class="row">
    <div class="col-12">
      <div class="card-box">
        <div class="card-body" id="ticketAreac">

          <!-- >>>> AREA SCREENSHOT HANYA INI <<<< -->
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
                            style="font-size:1rem; letter-spacing:.5px; cursor:pointer;"
                            title="Klik untuk salin">
                        <?= html_escape($booking->kode_booking ?? '-') ?>
                      </span>
                    </td>
                  </tr>

                  <tr>
                    <th>Nama</th>
                    <td><?= html_escape($booking->nama ?? '-') ?></td>
                  </tr>

                  <tr>
                    <th>Nomor HP</th>
                    <td><?= html_escape($booking->no_hp ?? '-') ?></td>
                  </tr>

                  <tr>
                    <th>Meja</th>
                    <td>
                      <?= html_escape($nama_meja) ?>
                      <span class="badge badge-light border ml-1">ID <?= $id_meja_disp ?></span>
                    </td>
                  </tr>

                  <tr>
                    <th>Tanggal</th>
                    <td>
                      <time datetime="<?= html_escape($tanggal_val) ?>">
                        <?= html_escape($tanggal_display) ?>
                      </time>
                    </td>
                  </tr>

                  <tr>
                    <th>Jam</th>
                    <td>
                      <?php
                        $mulai5   = substr($jam_mulai,0,5);
                        $selesai5 = substr($jam_selesai,0,5);
                      ?>
                      <span id="jamRange"><?= html_escape($mulai5.' – '.$selesai5) ?></span>
                      (<?= $durasi_jam ?> jam)
                    </td>
                  </tr>

                  <tr>
                    <th>Tarif/Jam (informasi)</th>
                    <td>Rp <?= number_format($tarif_per_jam,0,',','.') ?></td>
                  </tr>

                  <tr>
                    <th>Subtotal</th>
                    <td>Rp <?= number_format($subtotal_now,0,',','.') ?></td>
                  </tr>

                  <?php if ($kode_unik > 0): ?>
                  <tr>
                    <th>Kode Unik</th>
                    <td>+ Rp <?= number_format($kode_unik,0,',','.') ?></td>
                  </tr>
                  <?php endif; ?>

                  <tr class="table-success">
                    <th>Total Bayar</th>
                    <td>
                      <strong>Rp <?= number_format($grand_total,0,',','.') ?></strong>
                      <?php if ($st_raw === 'terkonfirmasi'): ?>
                        <span class="badge badge-success ml-1">lunas</span>
                      <?php elseif ($st_raw === 'draft'): ?>
                        <span class="badge badge-warning ml-1">belum bayar</span>
                      <?php else: ?>
                        <span class="badge badge-secondary ml-1"><?= html_escape($st_raw) ?></span>
                      <?php endif; ?>
                    </td>
                  </tr>

                  <tr>
                    <th>Status</th>
                    <td>
                      <span class="badge badge-<?= $status_variant ?>">
                        <i class="mdi <?= $status_icon ?>"></i>
                        <?= html_escape($status_text) ?>
                      </span>
                    </td>
                  </tr>

                 <?php if (in_array($st_raw, ['verifikasi','terkonfirmasi'], true)): ?>

                  <tr>
                    <th>Metode Pembayaran</th>
                    <td>
                      <span class="badge badge-light border text-dark">
                        <i class="mdi <?= $pay_icon ?>"></i>
                        <?= html_escape($pay_text) ?>
                      </span>
                    </td>
                  </tr>
                  <?php endif; ?>

                </tbody>
              </table>
            </div>

            <hr>

          </div>
          <!-- /ticketArea -->

          <!-- >>> tombol Screenshot: hanya saat LUNAS <<< -->
          <?php if ($is_terkonfirmasi): ?>
            <div class="mt-1 no-shot">
              <button type="button" class="btn btn-blue" id="btnScreenshot">
                <i class="mdi mdi-camera"></i> Screenshot
              </button>
              <div class="text-muted small mt-1">
                Simpan ringkasan ini sebagai bukti booking.
              </div>
            </div>
          <?php endif; ?>



        </div><!-- /card-body -->
      </div><!-- /card-box -->
    </div><!-- /col -->
  </div><!-- /row -->
  <!-- ============= /CARD RINGKASAN ============= -->


 
  <!-- ================= /BLOK AKSI BAYAR ================= -->

  <!-- Tombol Batalkan Booking -->
  <?php if (!$hide_cancel): ?>
  <div class="row">
    <div class="col-12">
      <button class="btn btn-outline-secondary w-100 mt-2" id="btnBatal" type="button">
        <i class="mdi mdi-close"></i> Batalkan Booking
      </button>
    </div>
  </div>
  <?php endif; ?>

  <!-- ======================= FORM EDIT JIKA DRAFT ======================= -->
  <?php if ($is_draft && !empty($edit_allowed)): ?>
  <div class="row mt-3">
    <div class="col-12">
      <div id="editCard" class="card-box">
        <div class="card-body">
          <div class="row g-3">
            <div class="col-12 col-md-4">
              <label class="form-label">Tanggal</label>
              <div class="border rounded p-2">
                <?= $tgl_disp ?: html_escape($booking->tanggal ?? '') ?>
              </div>
            </div>

            <div class="col-12 col-md-8">
              <form id="frmUpd" method="post" action="<?= site_url('billiard/update_cart') ?>" novalidate>
                <input type="hidden" name="t" value="<?= html_escape($booking->access_token ?? '') ?>">

                <div class="row g-3">
                  <div class="col-6">
                    <label class="form-label">Jam Mulai</label>
                    <input type="time"
                           name="jam_mulai"
                           id="jam_mulai"
                           value="<?= substr($booking->jam_mulai ?? '00:00:00',0,5) ?>"
                           class="form-control"
                           step="300"
                           required
                           aria-describedby="jam-info">
                    <small id="jam-info" class="text-danger"></small>
                  </div>

                  <div class="col-6">
                    <label class="form-label">Lama (jam)</label>
                    <input type="number"
                           name="durasi_jam"
                           id="durasi"
                           value="<?= (int)($booking->durasi_jam ?? 1) ?>"
                           min="1" max="12"
                           class="form-control"
                           required>
                  </div>

                  <div class="col-12">
                    <small id="infoHitung" class="text-muted d-block"></small>
                  </div>

                  <!-- hidden utk JS -->
                  <input type="hidden" id="open"  value="<?= html_escape(substr($meja->jam_buka ?? '00:00:00',0,5)) ?>">
                  <input type="hidden" id="close" value="<?= html_escape(substr($meja->jam_tutup ?? '23:59:00',0,5)) ?>">
                  <input type="hidden" id="price" value="<?= (int)($tarif_per_jam) ?>">
                  <input type="hidden" id="booking_start" value="<?= html_escape(substr($booking->jam_mulai ?? ($meja->jam_buka ?? '00:00:00'),0,5)) ?>">
                  <input type="hidden" id="tolerance" value="<?= (int)($this->fm->web_me()->tolerance_minutes ?? 15) ?>">

                  <div class="col-12">
                    <div id="smallCollisionNotice" class="alert alert-warning py-2" style="display:none;">
                      Perhatian: ada geser kecil <strong><span id="collisionMinutes">0</span> menit</strong> terhadap booking lain (masih dalam toleransi).
                    </div>
                  </div>

                  <div class="col-12">
                    <button id="btnUbah" class="btn btn-primary w-100" type="submit">Ubah Jadwal</button>
                  </div>
                </div>
              </form>

            </div><!-- /col-md-8 -->
          </div><!-- /row -->
        </div><!-- /card-body -->
      </div><!-- /card-box -->
    </div><!-- /col-12 -->
  </div><!-- /row -->
  <?php endif; ?>
  <!-- ===================== /FORM EDIT ===================== -->

</div><!-- /container-fluid -->


<!-- Dependensi JS & Footer -->
<script src="<?php echo base_url('assets/admin') ?>/js/vendor.min.js"></script>
<script src="<?php echo base_url('assets/admin') ?>/js/app.min.js"></script>
<script src="<?php echo base_url('assets/admin') ?>/js/sw.min.js"></script>

<!-- html2canvas (cukup sekali) -->
<?php if ($is_terkonfirmasi): ?>
  <script src="<?php echo base_url("assets/js/canva.js") ?>"></script>
<?php endif; ?>

<?php $this->load->view("front_end/footer.php"); ?>

<script>
// ================== COPY KODE BOOKING (klik badge) ==================
(function(){
  const b = document.getElementById('kodeBooking');
  if(!b) return;
  b.addEventListener('click', async ()=>{
    const text = b.textContent.trim();
    try {
      await navigator.clipboard.writeText(text);
      if(window.Swal){
        Swal.fire({
          title:'Kode disalin!',
          text:text,
          timer:1200,
          icon:'success',
          showConfirmButton:false
        });
      }
    } catch(e){}
  });
})();

// ================== COUNTDOWN BATAS BAYAR ==================
(function(){
  const root = document.getElementById('pay-deadline');
  const cd   = document.getElementById('countdown');
  if (!root || !cd) return;

  const pad = n => n<10 ? '0'+n : ''+n;
  const fmt = ms => {
    let s = Math.max(0, Math.floor(ms/1000));
    const h = Math.floor(s/3600); s%=3600;
    const m = Math.floor(s/60);   s%=60;
    return h>0 ? `${pad(h)}:${pad(m)}:${pad(s)}` : `${pad(m)}:${pad(s)}`;
  };

  function tick(){
    const dl = Number(root.dataset.deadlineMs || 0);
    const diff = dl - Date.now();
    cd.textContent = fmt(diff);
    if (diff <= 0) {
  clearInterval(t);
  cd.textContent = '00:00';
  root.classList.remove('alert-info');
  root.classList.add('alert-danger');
  root.innerHTML = 'Batas waktu pembayaran habis. Silakan buat ulang booking.';
  document.querySelectorAll('.js-pay').forEach(btn => {
    btn.setAttribute('disabled','disabled');
    btn.classList.add('disabled');
  });
}

  }

  const t = setInterval(tick, 1000);
  tick();
})();

// ================== PILIH METODE BAYAR (SweetAlert konfirmasi) ==================
(function(){
  document.addEventListener('click',function(e){
    const el=e.target.closest('.js-pay'); if(!el) return;
    e.preventDefault();
    const method=(el.dataset.method||'').toLowerCase();
    const href=el.getAttribute('href')||'#';
    const labelMap={cash:'TUNAI',qris:'QRIS',transfer:'TRANSFER'};
    const noteMap={
      cash:'Bayarnya langsung di kasir ya. Gas?',
      qris:'Setelah lanjut, jangan tutup halaman sampai transaksi selesai.',
      transfer:'Nanti dapat nomor rekening untuk transfer. Oke?'
    };
    const title=`Pilih ${labelMap[method]||'metode ini'}?`;

    if(typeof Swal==='undefined'){
      if(confirm(title+'\n\n'+(noteMap[method]||''))){
        window.location.href=href;
      }
      return;
    }

    Swal.fire({
      title,
      html:`<div class="text-start">${noteMap[method]||''}</div>`,
      icon:'question',
      showCancelButton:true,
      confirmButtonText:'Lanjut',
      cancelButtonText:'Batal',
      reverseButtons:true
    }).then(res=>{
      if(res.isConfirmed){
        Swal.fire({
          title:'Mengalihkan...',
          allowOutsideClick:false,
          didOpen:()=>Swal.showLoading()
        });
        window.location.href=href;
      }
    });
  });
})();

// ================== FORM UBAH JADWAL (AJAX) ==================
(function(){
  const frm=document.getElementById('frmUpd'); if(!frm) return;
  frm.addEventListener('submit',async e=>{
    e.preventDefault();
    const btn=document.getElementById('btnUbah');
    if(!btn||btn.disabled) return;

    const original=btn.innerHTML;
    btn.disabled=true;
    btn.innerHTML='Memproses…';

    try{
      const fd=new FormData(e.target);
      const r=await fetch(e.target.action,{method:'POST',body:fd});
      const j=await r.json();

      // info toleransi bentrok kecil
      if(j.collision&&j.collision.allowed&&j.collision.minutes){
        const box=document.getElementById('smallCollisionNotice');
        const minsEl=document.getElementById('collisionMinutes');
        if(box&&minsEl){
          minsEl.textContent=j.collision.minutes;
          box.style.display='block';
          setTimeout(()=>box.style.display='none',8000);
        }
      }

      if(window.Swal){
        await Swal.fire({
          title:j.title||(j.success?'Berhasil':'Gagal'),
          html:j.pesan||'',
          icon:j.success?'success':'error'
        });
      } else {
        alert(
          (j.title?j.title+'\n':'')
          +(j.pesan?j.pesan.replace(/<br>/g,'\n'):'')
        );
      }

      if(j.success&&j.redirect_url){
        location.href=j.redirect_url;
      }

    }catch(err){
      if(window.Swal){
        Swal.fire({title:'Error',text:'Koneksi ke server gagal.',icon:'error'});
      }else{
        alert('Koneksi ke server gagal.');
      }
    }finally{
      if(btn){
        btn.disabled=false;
        btn.innerHTML=original;
      }
    }
  });
})();

// ================== BATAL BOOKING ==================
(function(){
  const btnBatal=document.getElementById('btnBatal'); if(!btnBatal) return;

  btnBatal.addEventListener('click',async ()=>{
    if(typeof Swal==='undefined'){
      const ok=confirm('Yakin mau batalin? Slot akan dibuka lagi.');
      if(!ok) return;

      const fd=new FormData();
      fd.append('t','<?= $booking->access_token ?>');

      try{
        const r=await fetch('<?= site_url('billiard/batal') ?>',{method:'POST',body:fd});
        const j=await r.json();
        alert(
          (j.title?j.title+'\n':'')
          +(j.pesan?j.pesan.replace(/<br>/g,'\n'):'')
        );
        if(j.redirect_url) location.href=j.redirect_url;
      }catch(err){
        alert('Koneksi error.');
      }
      return;
    }

    // SweetAlert versi
    const res=await Swal.fire({
      title:'Batal booking?',
      text:'Slot akan dibuka lagi. Lanjutkan?',
      icon:'warning',
      showCancelButton:true,
      confirmButtonText:'Iya, batalin',
      cancelButtonText:'Enggak',
      reverseButtons:true
    });
    if(!res.isConfirmed){return;}

    try{
      Swal.fire({
        title:'Proses...',
        allowOutsideClick:false,
        didOpen:()=>Swal.showLoading()
      });

      const fd=new FormData();
      fd.append('t','<?= $booking->access_token ?>');

      const r=await fetch('<?= site_url('billiard/batal') ?>',{method:'POST',body:fd});
      const j=await r.json();

      Swal.close();

      if(j.success){
        await Swal.fire({
          title:'Dibatalkan',
          html:(j.pesan||''),
          icon:'success'
        });
        if(j.redirect_url) location.href=j.redirect_url;
      }else{
        await Swal.fire({
          title:(j.title||'Gagal'),
          html:(j.pesan||''),
          icon:'error'
        });
      }
    }catch(err){
      Swal.close();
      Swal.fire({
        title:'Error',
        text:'Koneksi bermasalah',
        icon:'error'
      });
    }
  });
})();

// ================== DYNAMIC HITUNG ESTIMASI (OPEN/CLOSE, DURASI DLL) ==================
(function(){
  const jamMulai=document.getElementById('jam_mulai');
  const durasi=document.getElementById('durasi');
  const info=document.getElementById('infoHitung');
  const jamInfo=document.getElementById('jam-info');

  if(!jamMulai||!durasi) return;

  const open=(document.getElementById('open')?.value)||'00:00';
  const close=(document.getElementById('close')?.value)||'23:59';
  const price=parseInt((document.getElementById('price')?.value)||'0',10);
  const bookingStart=(document.getElementById('booking_start')?.value)||'00:00';
  const tolerance=parseInt((document.getElementById('tolerance')?.value)||'15',10);

  const pad = n => (n<10?'0':'')+n;
  const toMin = s => { if(!s) return null;
    const [h,m] = s.split(':').map(Number);
    return h*60+m;
  };
  const fromMin = n => `${pad(Math.floor((n/60)%24))}:${pad(n%60)}`;
  function normalizeClose(a,b){return (b<=a)?b+1440:b}
  const rpStr = n => (n||0).toLocaleString('id-ID');

  function refresh(){
    jamMulai.setCustomValidity('');
    if(jamInfo) jamInfo.textContent='';
    if(info) info.textContent='';

    let d=parseInt(durasi.value||'1',10);
    if(isNaN(d)||d<1) d=1;
    if(d>12) d=12;

    const openMin=toMin(open),
          closeMinRaw=toMin(close),
          closeMin=normalizeClose(openMin,closeMinRaw);

    let tolLatest=toMin(bookingStart)+tolerance;
    tolLatest=Math.max(openMin,tolLatest);

    const latestByClose=closeMin-d*60;
    const latestStartMin=Math.min(latestByClose,tolLatest);
    const latestStart=fromMin(latestStartMin);

    jamMulai.min=open;
    jamMulai.max=fromMin(latestStartMin);

    const displayClose=(closeMinRaw<=openMin)?(close+' (next day)'):close;
    if(info){
      info.textContent=
        `Buka ${open}–${displayClose}. `+
        `Durasi ${d} jam (mulai paling lambat: ${latestStart}). `+
        (price>0?`Estimasi: Rp${rpStr(price*d)}.`:'');
    }

    if(jamMulai.value){
      let chosen=toMin(jamMulai.value);
      if(chosen<openMin&&closeMinRaw<=openMin) chosen+=1440;
      const endMin=chosen+d*60;
      if(endMin>closeMin){
        const msg='Durasi melewati jam tutup. Majukan jam mulai atau kurangi durasi.';
        jamMulai.setCustomValidity(msg);
        if(jamInfo) jamInfo.textContent=msg;
      }
      // update display jamRange (di ticket) biar real-time
      const jamRangeEl = document.getElementById('jamRange');
      if(jamRangeEl){
        const endDisplay=fromMin(Math.min(endMin,closeMin));
        jamRangeEl.textContent = jamMulai.value+' – '+endDisplay;
      }
    }

    // update subtotal & grand total live
    const subtotalEl=document.getElementById('subtotalLive'); // kita bakal buat fallback?
    const kodeUnikElVal = <?= (int)$kode_unik ?>;
    const calcSubtotal = price*d;
    const calcGrand    = price*d + kodeUnikElVal;

    // Kalau kamu mau live update di tabel tiket juga, kita bisa update innerText Total Bayar:
    // (opsional, aman kalau kolomnya ada)
    const valEl = document.querySelector('.totalBayarVal');
if (valEl) { valEl.textContent = 'Rp '+rpStr(calcGrand); }

  }

  ['input','change'].forEach(ev=>{
    durasi.addEventListener(ev,refresh);
    jamMulai.addEventListener(ev,refresh);
  });
  document.addEventListener('DOMContentLoaded',refresh);
})();

// ================== SCREENSHOT RINGKASAN BOOKING ==================
// ================== SCREENSHOT RINGKASAN BOOKING ==================
(function(){
  var btn = document.getElementById('btnScreenshot');
  if (!btn) return;

  // optional aja: flag tombol sendiri juga
  btn.setAttribute('data-html2canvas-ignore', 'true');

  btn.addEventListener('click', function(){
    // PENTING: target tetap ticketAreac (biar ada padding cantik)
    var area = document.getElementById('ticketAreac');
    if (!area){
      alert('Ringkasan tidak ditemukan.');
      return;
    }

    html2canvas(area, {
      scale: Math.min(2, window.devicePixelRatio || 2),
      useCORS: true,
      backgroundColor: '#ffffff',

      // <-- inilah kuncinya: skip elemen dengan class .no-shot
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
          a.download = 'booking_' + kode + '.png';
          document.body.appendChild(a);
          a.click();
          URL.revokeObjectURL(a.href);
          a.remove();
        });
      } else {
        var a = document.createElement('a');
        a.href = canvas.toDataURL('image/png');
        a.download = 'booking_' + kode + '.png';
        document.body.appendChild(a);
        a.click();
        a.remove();
      }
    });
  });
})();

</script>
