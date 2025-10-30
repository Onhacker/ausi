<?php
/** @var object $row */
/** @var string $title */
/** @var string $deskripsi */
?>
<link href="<?php echo base_url('assets/admin/datatables/css/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">

<?php
  $r = $row;

  // ========= Helper format tanggal Indonesia =========
  if (!function_exists('format_tanggal_id')) {
    function format_tanggal_id($dateStr, $with_day = true) {
      $dateStr = trim((string)$dateStr);
      if ($dateStr === '' || $dateStr === '-' || stripos($dateStr, '0000-00-00') !== false) return '-';
      try {
        $dt = new DateTime($dateStr);
      } catch (Throwable $e) {
        return '-';
      }
      $tgl = $dt->format('d-m-Y');
      if (!$with_day) return $tgl;

      // Map hari Indonesia
      static $hariMap = [
        'Sun' => 'Minggu', 'Mon' => 'Senin', 'Tue' => 'Selasa', 'Wed' => 'Rabu',
        'Thu' => 'Kamis',  'Fri' => 'Jumat', 'Sat' => 'Sabtu'
      ];
      $hari = $hariMap[$dt->format('D')] ?? '';
      return $hari ? ($hari . ', ' . $tgl) : $tgl;
    }
  }
  if (!function_exists('format_datetime_id')) {
    function format_datetime_id($dateTimeStr, $with_day = true) {
      $dateTimeStr = trim((string)$dateTimeStr);
      if ($dateTimeStr === '' || $dateTimeStr === '-' || stripos($dateTimeStr, '0000-00-00') !== false) return '-';
      try {
        $dt = new DateTime($dateTimeStr);
      } catch (Throwable $e) {
        return '-';
      }
      // Hari + dd-mm-yyyy
      $prefix = format_tanggal_id($dateTimeStr, $with_day);
      // Jam (pakai HH:ii, detik diabaikan agar rapi)
      $jam = $dt->format('H:i');
      return $prefix . ' ' . $jam;
    }
  }

  // ===== Badge status =====
  $badge = '<span class="badge badge-secondary">Draft</span>';
  if ($r->status === 'approved')     $badge = '<span class="badge badge-info text-dark">Approved</span>';
  if ($r->status === 'checked_in')   $badge = '<span class="badge badge-success">Checked-in</span>';
  if ($r->status === 'checked_out')  $badge = '<span class="badge badge-warning text-dark">Checked-out</span>';

  // ===== Stream URL via controller (aman) =====
  $surat_stream_url = !empty($r->surat_tugas) ? site_url('admin_permohonan/lampiran/surat/'.rawurlencode($r->kode_booking)) : null;
  $foto_stream_url  = !empty($r->foto)        ? site_url('admin_permohonan/lampiran/foto/'.rawurlencode($r->kode_booking))  : null;

  // Ekstensi dari DB (untuk pilih viewer modal)
  $surat_ext = !empty($r->surat_tugas) ? strtolower(pathinfo($r->surat_tugas, PATHINFO_EXTENSION)) : '';
  $foto_ext  = !empty($r->foto)        ? strtolower(pathinfo($r->foto, PATHINFO_EXTENSION))        : '';

  // ===== QR dari folder uploads/qr/ (pakai pola: uploads/qr/qr_{kode_booking}.png) =====
  $qr_img_url = null;
  $qr_dir_abs = FCPATH.'uploads/qr/';
  $qr_dir_web = base_url('uploads/qr/');

  // sanitize dulu kode booking biar aman untuk nama file
  $qr_base = preg_replace('/[^A-Za-z0-9_\-]/', '', (string)$r->kode_booking);
  $qr_filename = 'qr_'.$qr_base.'.png';

  $qr_abs = $qr_dir_abs.$qr_filename;
  if (is_file($qr_abs)) {
      $qr_img_url = $qr_dir_web.rawurlencode($qr_filename);
  }

  // ===== Durasi (checkin -> checkout) =====
  $durasiText = '-';
  if (!empty($r->checkin_at) && !empty($r->checkout_at)) {
      try {
          $ci = new DateTime($r->checkin_at);
          $co = new DateTime($r->checkout_at);
          if ($co >= $ci) {
              $diff = $ci->diff($co);
              $parts = [];
              if ($diff->d) $parts[] = $diff->d.' hari';
              if ($diff->h) $parts[] = $diff->h.' jam';
              if ($diff->i) $parts[] = $diff->i.' menit';
              if ($diff->s && !$diff->d && !$diff->h) $parts[] = $diff->s.' detik';
              $durasiText = $parts ? implode(' ', $parts) : '0 menit';
          }
      } catch (\Throwable $e) { /* biarkan "-" */ }
  }

  // ===== Pre-format tanggal yang dipakai di view =====
  $tgl_lahir_fmt   = format_tanggal_id($r->tanggal_lahir ?? '', false); // TANPA hari
  $tgl_booking_fmt = format_tanggal_id($r->tanggal ?? '', true);        // DENGAN hari
  $checkin_fmt     = format_datetime_id($r->checkin_at ?? '', true);    // DENGAN hari + jam
  $checkout_fmt    = format_datetime_id($r->checkout_at ?? '', true);   // DENGAN hari + jam
?>

<style>
  .card-elev { border-radius: .75rem; box-shadow: 0 8px 28px rgba(0,0,0,.08); }
  .kv { border-collapse: collapse; width:100%; }
  .kv th, .kv td { padding: .6rem .85rem; border-bottom: 1px solid #eef1f5; vertical-align: top; }
  .kv th { width: 30%; color:#4b5563; font-weight: 600; background: #fafbfc; }
  .badge { font-size: .75rem; letter-spacing:.2px; }
  .chip { display:inline-block; padding:.35rem .6rem; border-radius:999px; background:#eef2ff; color:#4338ca; font-weight:600; }
  .viewer-wrap { position: relative; width: 100%; }
  .viewer-embed { width: 100%; height: 70vh; border: none; border-radius:.5rem; background:#f8fafc; }
  .viewer-img { max-width: 100%; max-height: 70vh; display: block; margin: 0 auto; border-radius:.5rem; background:#f8fafc; }
  .spinner{display:inline-block;width:22px;height:22px;border:3px solid #ddd;border-top-color:#4b8;border-radius:50%;animation:sp 1s linear infinite}
  @keyframes sp{to{transform:rotate(360deg)}}

  /* Keperluan (teks panjang) */
  .keperluan-card { background:#f9fafb; border:1px solid #eef1f5; border-radius:.75rem; padding: .9rem 1rem; position: relative; }
  .keperluan-body { font-size:.95rem; line-height:1.7; color:#374151; max-height: 8.5rem; overflow:hidden; transition:max-height .25s ease; }
  .keperluan-body.expanded { max-height: 999px; }
  .keperluan-fade { content:""; position:absolute; left:0; right:0; bottom:2.7rem; height:3rem; background:linear-gradient(to bottom, rgba(249,250,251,0), #f9fafb); pointer-events:none; display:block; }
  .keperluan-body.expanded + .keperluan-fade { display:none; }
  .keperluan-actions { margin-top:.5rem; text-align:right; }
  .btn-soft { padding:.25rem .6rem; border-radius:.5rem; background:#eef2ff; color:#4338ca; border:0; font-weight:600; }
  .btn-soft:hover { background:#e0e7ff; }

  /* Panel kanan */
  .side-card .card-header { background:#fafbff; border-bottom:1px solid #eef1f5; font-weight:600; }
  .img-fluid-rounded { width:100%; height:auto; border-radius:.75rem; background:#f8fafc; }
  .qr-box { display:flex; align-items:center; justify-content:center; background:#f8fafc; border:1px dashed #e5e7eb; border-radius:.75rem; min-height:260px; }
  .qr-img { max-width: 100%; height:auto; }
</style>

<div class="container-fluid">
  <div class="row mt-3">
    <div class="col-12">
      <div class="card card-elev">
        <div class="card-body">

          <!-- Header -->
          <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div class="mb-2">
              <h4 class="mb-1"><?php echo htmlspecialchars($title); ?></h4>
              <div class="text-muted"><?php echo htmlspecialchars($deskripsi); ?></div>
            </div>
            <div class="mb-2">
              <a href="<?php echo site_url('admin_permohonan'); ?>" class="btn btn-light btn-sm">
                &larr; Kembali
              </a>
            </div>
          </div>

          <!-- Chips -->
          <div class="mb-3 d-flex align-items-center flex-wrap gap-2">
            <span class="chip mr-2">🆔 Kode: <?php echo htmlspecialchars($r->kode_booking); ?></span>
            <span class="ml-1"><?php echo $badge; ?></span>
          </div>

          <div class="row">
            <!-- KIRI: detail -->
            <div class="col-lg-7">
              <table class="kv">
                
                <tr><th>👤 Nama Tamu</th><td><?php echo htmlspecialchars($r->nama_tamu ?? '-'); ?></td></tr>

                <!-- Tgl lahir: dd-mm-yyyy TANPA hari -->
                <tr>
                  <th>🎂 Tempat/Tanggal Lahir</th>
                  <td>
                    <?php
                      $tmp = $r->tempat_lahir ?? '-';
                      echo htmlspecialchars($tmp), ', ', htmlspecialchars($tgl_lahir_fmt);
                    ?>
                  </td>
                </tr>

                <tr><th>📍 Alamat</th><td><?php echo htmlspecialchars($r->alamat ?? '-'); ?></td></tr>
                
                <tr><th>🪪 NIK</th><td><?php echo htmlspecialchars($r->nik ?? '-'); ?></td></tr>
                <tr><th>📱 No. HP</th><td><?php echo htmlspecialchars($r->no_hp ?? '-'); ?></td></tr>

                <tr><th>🏢 Instansi Asal</th><td><?php echo htmlspecialchars($r->target_instansi_nama ?? '-'); ?></td></tr>
                <tr><th>🏷️ Jabatan</th><td><?php echo htmlspecialchars($r->jabatan ?? '-'); ?></td></tr>
                <tr><th>🏛️ Unit Tujuan</th><td><?php echo htmlspecialchars($r->unit_tujuan_nama ?? '-'); ?></td></tr>
                <tr><th>👔 Nama Pejabat Unit</th><td><?php echo htmlspecialchars($r->nama_petugas_instansi ?? '-'); ?></td></tr>

                <!-- Keperluan -->
                <tr>
                  <th>📝 Keperluan</th>
                  <td>
                    <div class="keperluan-card">
                      <div id="kepBody" class="keperluan-body"><?php echo nl2br(htmlspecialchars($r->keperluan ?? '-')); ?></div>
                      <div class="keperluan-fade"></div>
                      <div class="keperluan-actions">
                        <button type="button" id="kepToggle" class="btn-soft">Lihat selengkapnya</button>
                      </div>
                    </div>
                  </td>
                </tr>

                <tr><th>👥 Jumlah Pendamping</th><td><?php echo htmlspecialchars($r->jumlah_pendamping ?? '0'); ?></td></tr>
                <?php
                  $pendamping_rows = $pendamping_rows ?? [];
                ?>

                <?php if (!empty($pendamping_rows)): ?>
                <tr>
                  <th>📋 Daftar Pendamping</th>
                  <td>
                    <div class="table-responsive">
                      <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                          <tr>
                            <th style="width:60px;">No</th>
                            <th style="width:200px;">NIK</th>
                            <th>Nama</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($pendamping_rows as $i => $p): ?>
                            <tr>
                              <td class="text-center"><?= $i + 1 ?></td>
                              <td><code><?= htmlspecialchars($p->nik ?? '', ENT_QUOTES, 'UTF-8') ?></code></td>
                              <td><?= htmlspecialchars($p->nama ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  </td>
                </tr>
                <?php elseif ((int)($r->jumlah_pendamping ?? 0) > 0): ?>
                <tr>
                  <th>📋 Daftar Pendamping</th>
                  <td class="text-muted">Belum ada data pendamping.</td>
                </tr>
                <?php endif; ?>

                <!-- Lampiran -->
                <tr>
                  <th>📄 Surat Tugas</th>
                  <td>
                    <?php if ($surat_stream_url): ?>
                      <button type="button"
                              class="btn btn-outline-primary btn-sm"
                              data-open="surat"
                              data-src="<?php echo htmlspecialchars($surat_stream_url); ?>"
                              data-ext="<?php echo htmlspecialchars($surat_ext); ?>">
                        Buka Surat Tugas
                      </button>
                      <div class="text-muted small mt-1"><?php echo htmlspecialchars($r->surat_tugas); ?></div>
                    <?php else: ?>
                      -
                    <?php endif; ?>
                  </td>
                </tr>

                <!-- Tanggal booking: dd-mm-yyyy + hari, jam terpisah tetap apa adanya -->
                <tr>
                  <th>📅 Tanggal/Jam Booking</th>
                  <td>
                    <?php
                      $jam_raw = trim((string)($r->jam ?? ''));
                      echo htmlspecialchars($tgl_booking_fmt);
                      if ($jam_raw !== '' && $jam_raw !== '-') echo ' ' . htmlspecialchars($jam_raw);
                    ?>
                  </td>
                </tr>

                <tr><th>📌 Status</th><td><?php echo htmlspecialchars($r->status ?? '-'); ?></td></tr>

                <!-- Check-in/out: tampilkan hari + dd-mm-yyyy + HH:ii -->
                <tr><th>🟢 Waktu Check-in</th><td><?php echo htmlspecialchars($checkin_fmt); ?></td></tr>
                <tr><th>🟡 Waktu Check-out</th><td><?php echo htmlspecialchars($checkout_fmt); ?></td></tr>

                <tr><th>⏱️ Durasi</th><td><?php echo htmlspecialchars($durasiText); ?></td></tr>
              </table>
            </div>

            <!-- KANAN: Foto & QR -->
            <div class="col-lg-5">
              <!-- Foto besar -->
              <div class="card side-card mb-3">
                <div class="card-header py-2">🖼️ Foto</div>
                <div class="card-body">
                  <?php if ($foto_stream_url): ?>
                    <img src="<?php echo htmlspecialchars($foto_stream_url); ?>" alt="Foto Tamu" class="img-fluid-rounded mb-2" id="fotoPreview">
                    <div class="d-flex gap-2">
                      <button type="button"
                              class="btn btn-outline-secondary btn-sm mr-2"
                              data-open="foto"
                              data-src="<?php echo htmlspecialchars($foto_stream_url); ?>"
                              data-ext="<?php echo htmlspecialchars($foto_ext); ?>">
                        Lihat Lebih Besar
                      </button>
                      <a id="fotoSideDownload" class="btn btn-primary btn-sm" href="<?php echo htmlspecialchars($foto_stream_url . (strpos($foto_stream_url,'?')!==false?'&':'?') . 'dl=1'); ?>">Download</a>
                    </div>
                  <?php else: ?>
                    <div class="text-muted">Tidak ada foto.</div>
                  <?php endif; ?>
                </div>
              </div>

              <!-- QR / Barcode -->
              <div class="card side-card">
                <div class="card-header py-2">🔳 QR / Barcode</div>
                <div class="card-body">
                  <?php if ($qr_img_url): ?>
                    <div class="qr-box mb-2">
                      <img src="<?php echo htmlspecialchars($qr_img_url); ?>" alt="QR Code Booking" class="qr-img" id="qrImg">
                    </div>
                    <a id="qrDownload" class="btn btn-primary btn-sm" href="<?php echo htmlspecialchars($qr_img_url . (strpos($qr_img_url,'?')!==false?'&':'?') . 'dl=1'); ?>">
                      Download QR
                    </a>
                  <?php else: ?>
                    <div class="text-muted">QR tidak tersedia di folder <code>uploads/qr</code> untuk kode <b><?php echo htmlspecialchars($r->kode_booking); ?></b>.</div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div> <!-- /row -->

        </div>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: SURAT TUGAS -->
<div class="modal fade" id="modalSuratTugas" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title">Pratinjau Surat Tugas</h6>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <div id="spinSurat" class="spinner d-none"></div>
        <div class="viewer-wrap">
          <embed id="suratEmbed" class="viewer-embed d-none" type="application/pdf">
          <img   id="suratImg"   class="viewer-img d-none" alt="Surat Tugas">
          <div id="suratFallback" class="text-muted small d-none mt-2">
            Tidak dapat menampilkan pratinjau. Silakan gunakan tombol <b>Download</b>.
          </div>
        </div>
      </div>
      <div class="modal-footer py-2">
        <a id="suratDownload" href="#" class="btn btn-primary btn-sm" rel="noopener">Download</a>
        <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: FOTO -->
<div class="modal fade" id="modalFoto" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title">Pratinjau Foto</h6>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <div id="spinFoto" class="spinner d-none"></div>
        <div class="viewer-wrap text-center">
          <img id="fotoImg" class="viewer-img" alt="Foto Tamu">
        </div>
      </div>
      <div class="modal-footer py-2">
        <a id="fotoDownload" href="#" class="btn btn-primary btn-sm" rel="noopener">Download</a>
        <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
(function($){

  // ====== Util modal: support BS5/BS4/fallback ======
  function openModal(id){
    var el = document.getElementById(id);
    if (!el) return;

    if (window.bootstrap && typeof bootstrap.Modal === 'function') {
      var Inst = bootstrap.Modal;
      var inst = (typeof Inst.getInstance === 'function') ? Inst.getInstance(el) : null;
      if (!inst) {
        inst = (typeof Inst.getOrCreateInstance === 'function')
          ? Inst.getOrCreateInstance(el, {backdrop:true, keyboard:true})
          : new Inst(el, {backdrop:true, keyboard:true});
      }
      inst.show(); return;
    }

    if (window.jQuery && $.fn.modal) { $(el).modal('show'); return; }

    // Fallback manual
    el.classList.add('show'); el.style.display = 'block';
    document.body.classList.add('modal-open');
    if (!document.getElementById('__backdrop')) {
      var bd = document.createElement('div'); bd.id='__backdrop';
      bd.className='modal-backdrop fade show'; document.body.appendChild(bd);
    }
  }
  function closeModal(id){
    var el = document.getElementById(id);
    if (!el) return;

    if (window.bootstrap && typeof bootstrap.Modal === 'function') {
      var Inst = bootstrap.Modal;
      var inst = (typeof Inst.getInstance === 'function') ? Inst.getInstance(el) : null;
      if (!inst) inst = (typeof Inst.getOrCreateInstance === 'function') ? Inst.getOrCreateInstance(el) : new Inst(el);
      inst.hide(); return;
    }
    if (window.jQuery && $.fn.modal) { $(el).modal('hide'); return; }

    el.classList.remove('show'); el.style.display = 'none';
    document.body.classList.remove('modal-open');
    var bd = document.getElementById('__backdrop'); if (bd) bd.remove();

    if (id === 'modalSuratTugas') { $('#suratEmbed').attr('src',''); $('#suratImg').attr('src',''); }
    if (id === 'modalFoto') { $('#fotoImg').attr('src',''); }
  }

  // ====== Keperluan toggle ======
  $('#kepToggle').on('click', function(){
    var $b = $('#kepBody');
    var expanded = $b.hasClass('expanded');
    if (expanded) { $b.removeClass('expanded'); $(this).text('Lihat selengkapnya'); }
    else          { $b.addClass('expanded');   $(this).text('Sembunyikan'); }
  });

  // ====== Buka Surat Tugas ======
  $(document).on('click', '[data-open="surat"]', function(e){
    e.preventDefault(); e.stopPropagation();
    var src = $(this).data('src') || '';
    var ext = (($(this).data('ext')||'')+'').toLowerCase();

    var $embed=$('#suratEmbed'), $img=$('#suratImg'), $fb=$('#suratFallback');
    $embed.addClass('d-none').attr('src','');
    $img.addClass('d-none').attr('src','');
    $fb.addClass('d-none').text('Tidak dapat menampilkan pratinjau. Silakan gunakan tombol Download.');

    if (!src) { $fb.removeClass('d-none').text('File tidak tersedia.'); $('#suratDownload').attr('href', '#'); return false; }

    $('#spinSurat').removeClass('d-none'); // spinner on
    if (ext === 'pdf')       { $embed.removeClass('d-none').attr('src', src + '#zoom=page-fit'); }
    else if (['jpg','jpeg','png','gif','webp'].indexOf(ext) !== -1) { $img.removeClass('d-none').attr('src', src); }
    else                     { $embed.removeClass('d-none').attr('src', src); }

    // Download langsung
    var dl = src + (src.indexOf('?') > -1 ? '&' : '?') + 'dl=1';
    $('#suratDownload').attr({ href: dl, download: '' });

    openModal('modalSuratTugas');
    return false;
  });

  // ====== Buka Foto (modal) ======
  $(document).on('click', '[data-open="foto"]', function(e){
    e.preventDefault(); e.stopPropagation();
    var src = $(this).data('src') || '';

    $('#spinFoto').removeClass('d-none');
    $('#fotoImg').attr('src', src || '');

    var dl = src + (src.indexOf('?') > -1 ? '&' : '?') + 'dl=1';
    $('#fotoDownload').attr({ href: dl, download: '' });

    openModal('modalFoto');
    return false;
  });

  // ====== Spinner off setelah load ======
  $('#suratEmbed, #suratImg').on('load', function(){ $('#spinSurat').addClass('d-none'); });
  $('#fotoImg').on('load', function(){ $('#spinFoto').addClass('d-none'); });

  // ====== Download tanpa tab baru (iframe tersembunyi) ======
  function triggerDownload(url){
    if (!url) return;
    var ifr = document.getElementById('__dl_iframe');
    if (!ifr) {
      ifr = document.createElement('iframe');
      ifr.id='__dl_iframe'; ifr.style.display='none';
      document.body.appendChild(ifr);
    }
    ifr.src = url;
  }
  $(document).on('click', '#suratDownload, #fotoDownload, #fotoSideDownload, #qrDownload', function(e){
    e.preventDefault(); e.stopPropagation();
    var url = this.getAttribute('href');
    if (url) triggerDownload(url);
    return false;
  });

  // ====== ESC / backdrop (fallback) ======
  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape'){
      if($('#modalSuratTugas').is(':visible')) { closeModal('modalSuratTugas'); }
      if($('#modalFoto').is(':visible')) { closeModal('modalFoto'); }
    }
  });
  document.addEventListener('click', function(e){
    const bd = document.getElementById('__backdrop');
    if (bd && e.target === bd) {
      if($('#modalSuratTugas').is(':visible')) closeModal('modalSuratTugas');
      if($('#modalFoto').is(':visible')) closeModal('modalFoto');
    }
  });

})(jQuery);
</script>
