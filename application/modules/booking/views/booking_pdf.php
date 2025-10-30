<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Booking <?= htmlspecialchars($booking->kode_booking) ?></title>
  <style>
    /* ---- Tipografi & dasar ---- */
    body{font-family: DejaVu Sans, Arial, sans-serif; font-size:12px; color:#111; margin:0;}
    .wrap{padding:14px;}

    /* ---- Header ---- */
    .hdr-table{width:100%; border-collapse:collapse; margin-bottom:12px; border-bottom:1px solid #e5e7eb;}
    .hdr-td{vertical-align:middle; padding:6px 0;}
    .logo{height:42px}
    .org-title{font-size:16px; font-weight:700; margin:0; text-transform:uppercase; letter-spacing:.3px;}
    .org-sub{font-size:11px; color:#555; margin-top:2px}
    .badge{display:inline-block; padding:3px 8px; border-radius:4px; color:#fff; font-weight:700; font-size:11px}
    .b-approved{background:#2563eb}
    .b-checked_in{background:#0ea5e9}
    .b-checked_out{background:#16a34a}
    .b-pending{background:#f59e0b}
    .b-rejected{background:#ef4444}
    .b-expired{background:#94a3b8}
    .b-default{background:#6b7280}

    /* ---- Grid konten (pakai tabel agar aman di PDF engines) ---- */
    .grid{width:100%; border-collapse:separate; border-spacing:0 0;}
    .grid td{vertical-align:top}

    /* ---- Kartu detail ---- */
    .card{border:1px solid #e5e7eb; border-radius:8px;}
    .card-h{padding:8px 10px; border-bottom:1px solid #eef0f3; font-weight:700}
    .card-b{padding:10px}

    /* ---- Tabel detail ---- */
    .tbl{width:100%; border-collapse:collapse;}
    .tbl td{padding:6px 8px; border-bottom:1px dashed #e5e7eb; vertical-align:top}
    .tbl td.k{width:36%; color:#6b7280}
    .tbl tr:last-child td{border-bottom:none}
    .zebra tr:nth-child(odd) td{background:#fafafa}

    /* ---- QR box ---- */
    .qrbox{border:1px solid #e5e7eb; border-radius:8px; padding:10px; text-align:center}
    .qr-title{font-weight:700; margin-bottom:6px}
    .qr-img{width:160px; max-width:100%; height:auto; border:6px solid #fff; outline:1px solid #e5e7eb; border-radius:8px}
    .qr-sub{font-size:11px; color:#6b7280; margin-top:6px}

    /* ---- Footer ---- */
    .note{font-size:11px; color:#444; margin-top:10px}
    .note ul{margin:6px 0 0 18px; padding:0}
    .foot{margin-top:12px; padding-top:8px; border-top:1px dashed #cbd5e1; text-align:center; color:#64748b; font-size:11px}
        /* Paksa semua garis di tabel pendamping terlihat */
    table.tbl.pendamping {
      border-collapse: collapse !important;
      border: 1px solid #333 !important;
    }
    table.tbl.pendamping th,
    table.tbl.pendamping td {
      border: 1px solid #333 !important;
      padding: 6px 8px !important;
      font-size: 12px;
    }
    table.tbl.pendamping th { background: #f2f2f2; font-weight: 700; }

  </style>
</head>
<body>
  <?php
  // helper aman
  $e = function($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };

  // status badge class
  $st = strtolower((string)($booking->status ?? ''));
  $badgeClass = 'b-default';
  if     ($st==='approved')    $badgeClass = 'b-approved';
  elseif ($st==='checked_in')  $badgeClass = 'b-checked_in';
  elseif ($st==='checked_out') $badgeClass = 'b-checked_out';
  elseif ($st==='pending')     $badgeClass = 'b-pending';
  elseif ($st==='rejected')    $badgeClass = 'b-rejected';
  elseif ($st==='expired')     $badgeClass = 'b-expired';

  // unit tujuan
  $ci =& get_instance();
  $unit_nama = $ci->db->select('nama_unit')->get_where('unit_tujuan', ['id'=>$booking->unit_tujuan])->row('nama_unit');
  $unit_nama = $unit_nama ?: '-';

  // instansi asal & pejabat unit
  $instansi = ($booking->target_instansi_nama ?? '') ?: ($booking->instansi ?? '-');
  $nama_petugas_instansi = !empty($booking->nama_petugas_instansi) ? $booking->nama_petugas_instansi : '-';

  // ====== AMBIL DAFTAR PENDAMPING ======
  $pendamping_rows = $ci->db
  ->order_by('id_pendamping','ASC')
  ->get_where('booking_pendamping', ['kode_booking' => $booking->kode_booking])
  ->result();

  // path logo & QR (pakai path lokal agar aman di PDF)
  $logoPath = FCPATH.'assets/images/logo.png';
  $hasLogo  = is_file($logoPath);
  $qrPath   = FCPATH.'uploads/qr/qr_'.$booking->kode_booking.'.png';
  $hasQR    = is_file($qrPath);

  // tanggal & jam
  $tgl = !empty($booking->tanggal) ? date('d-m-Y', strtotime($booking->tanggal)) : '-';
  $jam = !empty($booking->jam)     ? $e($booking->jam) : '-';

  // durasi (opsional jika mau ditampilkan)
  $fmtChkIn  = !empty($booking->checkin_at)  ? date('d-m-Y H:i', strtotime($booking->checkin_at))  : '-';
  $fmtChkOut = !empty($booking->checkout_at) ? date('d-m-Y H:i', strtotime($booking->checkout_at)) : '-';
  ?>
  <div class="wrap">

   <table>
    <tr>
      <td align="center" width="25%">
       <?php if ($hasLogo): ?>
        <img style="width: 30px" src="<?= $logoPath ?>" alt="Logo">
      <?php endif; ?>
    </td>
    <td align="center">
     <strong><?php echo strtoupper($rec->type) ?></strong>
     <div style="font-size: 9px">Alamat : <?php echo $rec->alamat ?></div>
   </td>
 </tr>
</table>
<hr>

<!-- Grid konten: kiri detail, kanan QR -->
<table class="grid">
  <tr>
    <!-- Detail -->
    <td style="width:65%; padding-right:10px;">
      <div class="card">
        <div class="card-h" style="text-align: center;">Detail Booking</div>
        <div class="card-b">
          <table class="tbl zebra">
            <tr><td class="k">Kode Booking</td>   <td class="v"><?= $e($booking->kode_booking) ?></td></tr>
            <tr><td class="k">Nama Tamu</td>      <td class="v"><?= $e($booking->nama_tamu) ?></td></tr>
            <tr><td class="k">Jabatan</td>        <td class="v"><?= $e($booking->jabatan) ?></td></tr>
            <tr><td class="k">NIK</td>            <td class="v"><?= $e($booking->nik) ?></td></tr>
            <tr><td class="k">No. HP</td>         <td class="v"><?= $e($booking->no_hp) ?></td></tr>
            <tr><td class="k">Instansi Asal</td>  <td class="v"><?= $e($instansi) ?></td></tr>
            <tr><td class="k">Unit Tujuan</td>    <td class="v"><?= $e($unit_nama) ?></td></tr>
            <tr><td class="k">Nama <?= $e($unit_nama) ?></td> <td class="v"><?= $e($nama_petugas_instansi) ?></td></tr>
            <tr><td class="k">Keperluan</td>      <td class="v"><?= $e($booking->keperluan) ?></td></tr>
            <tr><td class="k">Tanggal & Jam</td>  <td class="v"><?= $tgl ?> &nbsp;<?= $jam ?></td></tr>
            <!-- <tr><td class="k">Check-in</td>    <td class="v"><?= $fmtChkIn ?></td></tr> -->
            <!-- <tr><td class="k">Checkout</td>    <td class="v"><?= $fmtChkOut ?></td></tr> -->
            <?php
            $jumlah_pend = (int)$booking->jumlah_pendamping;
            $ada_list    = !empty($pendamping_rows);
            ?>
            <tr>
              <td class="k">Pendamping</td>
              <td class="v"><?= $jumlah_pend ?> orang</td>
            </tr>

            <?php if ($jumlah_pend > 0): ?>
              <tr>
                <td colspan="2" class="v" style="padding-top:4px;">
                  <div style="font-weight:700; margin-bottom:6px;">Daftar Pendamping</div>

                  <?php if ($ada_list): ?>
                    <table class="tbl pendamping" border="1" cellspacing="0" cellpadding="4">
                      <tr>
                        <th style="width:42px;">No</th>
                        <th style="width:170px;">NIK</th>
                        <th>Nama</th>
                      </tr>
                      <?php foreach ($pendamping_rows as $i => $p): ?>
                        <tr>
                          <td style="border:1px solid #333; padding:6px 8px;"><?= $i+1 ?></td>
                          <td style="border:1px solid #333; padding:6px 8px;"><code><?= $e($p->nik) ?></code></td>
                          <td style="border:1px solid #333; padding:6px 8px;"><?= $e($p->nama) ?></td>
                        </tr>

                      <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                      <div style="color:#6b7280; font-style:italic;">Belum ada data pendamping yang diinput.</div>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endif; ?>

            </table>
          </div>
        </div>

        <div class="note">
          <ul>
            <li>Unduh file ini untuk disimpan.</li>
            <li>Bawa Kode Booking saat proses check-in.</li>
            <li>Tunjukkan Kode Booking beserta QR Code kepada petugas saat check-in.</li>
          </ul>
        </div>
      </td>

      <!-- QR -->
      <td style="width:35%; padding-left:10px;">
        <div class="qrbox">
          <div class="qr-title">QR CODE</div>
          <?php if ($hasQR): ?>
            <img src="<?= $qrPath ?>" class="qr-img" alt="QR Code">
            <div class="qr-sub">Scan di Pintu Masuk</div>
            <?php else: ?>
              <div class="qr-sub" style="color:#9ca3af">QR belum tersedia.</div>
            <?php endif; ?>
          </div>
        </td>
      </tr>
    </table>
    <div class="foot">
     <?php echo $rec->nama_website." • ". $rec->meta_deskripsi ?>
     • <?php echo site_url() ?>
   </div>
 </div>
</body>
</html>
