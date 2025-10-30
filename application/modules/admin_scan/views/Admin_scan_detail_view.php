<?php
// Pastikan $booking terisi object dari DB
$booking   = $booking ?? null;
$title     = $title ?? 'Detail Booking';
$subtitle  = $subtitle ?? 'Informasi Pengunjung';

// Ambil instance CI untuk akses db & csrf
$CI =& get_instance();

// ===== Helper Hari/Tanggal Indonesia =====
if (!function_exists('hari_indo')) {
  function hari_indo($timestamp){
    $map = [1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu',7=>'Minggu'];
    $n = (int)date('N', $timestamp ?: time());
    return $map[$n] ?? '';
  }
}
if (!function_exists('fmt_hari_tanggal')) {
  function fmt_hari_tanggal($datetimeStr, $with_time=false){
    if (!$datetimeStr) return '-';
    $ts = is_numeric($datetimeStr) ? (int)$datetimeStr : strtotime($datetimeStr);
    if (!$ts) return '-';
    $hari = hari_indo($ts);
    $tgl  = date('d-m-Y', $ts);
    if ($with_time) {
      $jam = date('H:i', $ts);
      return "{$hari}, {$tgl} {$jam}";
    }
    return "{$hari}, {$tgl}";
  }
}

// Unit tujuan (fallback jika $unit_nama tidak dipassing)
if (!isset($unit_nama)) {
  $unit_nama = '-';
  if (!empty($booking->unit_tujuan)) {
    $u = $CI->db->select('nama_unit')->get_where('unit_tujuan', ['id'=>$booking->unit_tujuan])->row('nama_unit');
    if ($u) $unit_nama = $u;
  }
}

$surat_url = $surat_url ?? null;
$foto_url  = $foto_url  ?? null;

// Badge status
$badgeMap = [
  'pending'     => 'warning',
  'approved'    => 'primary',
  'checkin'     => 'info',
  'checked_in'  => 'info',
  'checkout'    => 'success',
  'checked_out' => 'success',
  'expired'     => 'secondary',
  'rejected'    => 'danger',
];
$st = strtolower((string)($booking->status ?? ''));
$badgeCls = $badgeMap[$st] ?? 'secondary';

// Boleh ambil dokumentasi hanya saat approved / checkin / checked_in
$can_capture  = in_array($st, ['approved','checkin','checked_in'], true);
$status_label = strtoupper(str_replace('_',' ', $st ?: '-'));

// Durasi kunjungan (hanya jika sudah check-in & check-out)
$durasi_text = null;
if (!empty($booking->checkin_at) && !empty($booking->checkout_at)) {
  $a = new DateTime($booking->checkin_at);
  $b = new DateTime($booking->checkout_at);
  $diff = $a->diff($b);
  $menitTotal = $diff->days*24*60 + $diff->h*60 + $diff->i;
  $jam = floor($menitTotal/60); $mnt = $menitTotal%60;
  $durasi_text = ($jam>0? $jam.' jam ' : '').$mnt.' menit';
}

// Link PDF
$pdf_url = site_url('admin_scan/print_pdf/'.rawurlencode($booking->kode_booking ?? '')).'?inline=1&ts='.time();
$pernyataan_pdf = $pernyataan_pdf
  ?? ($pdf_pernyataan ?? null)
  ?? site_url('admin_scan/pernyataan_pdf/'.rawurlencode($booking->kode_booking ?? '')).'?inline=1&ts='.time();

// Jadwal kunjungan (hari + tanggal + jam)
$jadwal_text = '-';
if (!empty($booking->tanggal) || !empty($booking->jam)) {
  $base = trim(($booking->tanggal ?? '').' '.($booking->jam ?? ''));
  $jadwal_text = fmt_hari_tanggal($base, true);
}

// Check-in / Checkout
$checkin_text  = !empty($booking->checkin_at)  ? fmt_hari_tanggal($booking->checkin_at,  true) : null;
$checkout_text = !empty($booking->checkout_at) ? fmt_hari_tanggal($booking->checkout_at, true) : null;

// Petugas (jika ada)
$petugas_checkin  = $booking->petugas_checkin  ?? null;
$petugas_checkout = $booking->petugas_checkout ?? null;

// Sanitizer ringkas
$e = fn($s)=>htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

// WA link
$wa_digits = preg_replace('/\D+/', '', (string)($booking->no_hp ?? ''));
?>
<style>
  .kv-card{border:1px solid #eef0f3;border-radius:14px}
  .kv-head{border-bottom:1px dashed #e5e7eb}
  .kv-label{color:#6b7280;font-size:.95rem}
  .kv-value{font-weight:600}
  .kv-row{padding:.6rem 0;border-bottom:1px dashed #f1f5f9}
  .kv-row:last-child{border-bottom:none}
  .badge.text-uppercase{letter-spacing:.02em}
  .btn-soft{background:#f8fafc;border-color:#e5e7eb}

  .doc-card .preview{max-width:50%;border-radius:12px;box-shadow:0 4px 18px rgba(0,0,0,.06)}
  .pdf-card .pdf-frame{border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;background:#111}
  .pdf-card object{width:100%;height:68vh;border:0}
  @media (max-width: 991px){ .pdf-card object{height:60vh} }

  .header-stat .h5{font-weight:700}

  /* Overlay kunci kamera */
  .cam-card{position:relative}
  .cam-card .overlay{
    position:absolute; inset:0;
    background:rgba(255,255,255,.88);
    display:flex; align-items:center; justify-content:center;
    border-radius:12px; text-align:center; padding:1rem;
  }
  .cam-card .overlay .msg{max-width:420px; color:#374151;}

  /* Keperluan panjang */
  .keperluan-box{background:#f8fafc;border:1px dashed #e5e7eb;border-radius:10px;padding:.75rem}

  /* ===== Kamera Dokumentasi (tanpa garis/guide) ===== */
  #docCamWrap{position:relative}
  .fs-exit-btn{
    position:absolute; top:10px; right:10px; z-index: 1070;
    width:42px; height:42px; border:0; border-radius:999px;
    display:flex; align-items:center; justify-content:center;
    background:rgba(0,0,0,.6); color:#fff;
  }
  .fs-shutter{
    position:absolute; left:50%; transform:translateX(-50%);
    bottom:18px; z-index:1070;
    width:68px; height:68px; border:0; border-radius:999px;
    display:flex; align-items:center; justify-content:center;
    background:rgba(255,255,255,.85); color:#111;
    box-shadow:0 8px 30px rgba(0,0,0,.35);
  }

  #docCamWrap:not(.is-fs):not(:fullscreen) video {
    aspect-ratio:16/9; width:100%; border-radius:12px; background:#000; object-fit:cover;
  }

  /* Fullscreen API */
  #docCamWrap:fullscreen,
  #docCamWrap:-webkit-full-screen { background:#000; }
  #docCamWrap:fullscreen video,
  #docCamWrap:-webkit-full-screen video,
  video:fullscreen, video:-webkit-full-screen {
    position:absolute; inset:0;
    width:100vw !important;
    height:100svh !important; height:100dvh !important; height:100vh !important;
    object-fit:cover !important; border-radius:0 !important; aspect-ratio:auto !important;
    background:#000;
  }

  /* Fallback CSS bila API ditolak */
  body.scan-lock { overflow: hidden; }
  #docCamWrap.fullscreen-scan{
    position: fixed !important; inset: 0 !important; z-index: 1060 !important;
    background:#000; margin:0 !important; border-radius:0 !important;
    padding: env(safe-area-inset-top) env(safe-area-inset-right) env(safe-area-inset-bottom) env(safe-area-inset-left);
  }
  #docCamWrap.fullscreen-scan video{
    position:absolute; inset:0;
    width:100vw !important;
    height:100svh !important; height:100dvh !important; height:100vh !important;
    object-fit:cover !important; border-radius:0 !important; aspect-ratio:auto !important;
    background:#000;
  }
  #docCamWrap.is-fs video{
    position:absolute; inset:0;
    width:100vw !important;
    height:100svh !important; height:100dvh !important; height:100vh !important;
    object-fit:cover !important; border-radius:0 !important; aspect-ratio:auto !important;
  }
  /* Tombol senter saat fullscreen (mengambang di kiri-bawah) */
.fs-torch-btn{
  position:absolute; bottom:10px; left:10px;
  z-index:1070; width:auto; min-width:42px; height:42px;
  border:0; border-radius:999px; padding:0 .9rem;
  display:flex; align-items:center; gap:.35rem;
  background:rgba(0,0,0,.6); color:#fff;
}
.fs-torch-btn.active{ background:rgba(255,200,0,.9); color:#111; }
.fs-torch-btn i{ font-size:18px; }
.btn-loading{position:relative;pointer-events:none;opacity:.85}
.btn-loading::after{
  content:""; position:absolute; right:10px; top:50%; transform:translateY(-50%);
  width:14px; height:14px; border:2px solid currentColor; border-top-color:transparent;
  border-radius:50%; animation:spin .8s linear infinite;
}
@keyframes spin{to{transform:translateY(-50%) rotate(360deg)}}
  .surat-uploader{display:block}
.su-dropzone{
  display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center;
  gap:.25rem; padding:1rem; border:1px dashed #cfd4dc; border-radius:12px; background:#fafbff;
  transition:all .15s ease;
}
.su-dropzone:hover{background:#f5f7ff; border-color:#9aa4b2}
.su-dropzone.is-drag{background:#eef2ff; border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.12) inset}
.su-ico{font-size:28px; color:#64748b}
.su-text{color:#334155}
.su-sub{font-size:.85rem; color:#94a3b8}

.su-filepill{
  display:flex; align-items:center; gap:.5rem; margin-top:.6rem;
  background:linear-gradient(180deg,#ffffff,#f8fafc); border:1px solid #e5e7eb; border-radius:999px;
  padding:.35rem .6rem .35rem .5rem; box-shadow:0 1px 2px rgba(0,0,0,.04);
}
.su-filepill i{color:#64748b}
.su-filepill .su-meta{font-size:.85rem; color:#64748b}
.su-clear{
  border:0; background:#f1f5f9; color:#0f172a; width:26px; height:26px; border-radius:50%;
  display:inline-flex; align-items:center; justify-content:center;
}
.su-clear:hover{background:#e2e8f0}

</style>

<div class="container-fluid">
  <!-- start page title -->
  <div class="row">
    <div class="col-12">
      <div class="page-title-box">
        <div class="page-title-right">
          <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><?= $e($title) ?></li>
            <li class="breadcrumb-item active"><?= $e($subtitle) ?></li>
          </ol>
        </div>
        <h4 class="page-title"><?= $e($subtitle) ?></h4>
      </div>
    </div>
  </div>

  <!-- Header ringkas -->
  <div class="card kv-card shadow-sm mb-3">
    <div class="card-body header-stat">
      <div class="d-flex justify-content-between align-items-center kv-head pb-2 mb-2">
        <div>
          <div class="kv-label">🔖 Kode Booking</div>
          <div class="h5 mb-0"><?= $e($booking->kode_booking ?? '-') ?></div>
        </div>
        <span class="badge badge-<?= $badgeCls ?> text-uppercase px-3 py-2">
          <?= $e($booking->status ?? '-') ?>
        </span>
      </div>
    </div>
  </div>

  <!-- ======================== BARIS ATAS ======================== -->
  <div class="row">
    <!-- Kiri: DETAIL BOOKING -->
    <div class="col-lg-7">
      <div class="card kv-card shadow-sm mb-3">
        <div class="card-body">
          <div class="kv-head pb-3 mb-3">
            <h6 class="mb-0"><i class="mdi mdi-information-outline"></i> Detail Booking</h6>
          </div>

          <div class="row">
            <div class="col-12">
              <dl class="mb-0">

                <div class="kv-row row no-gutters">
                  <dt class="col-sm-3 kv-label">👤 Nama Tamu</dt>
                  <dd class="col-sm-9 kv-value"><?= $e($booking->nama_tamu ?? '-') ?></dd>
                </div>
                <div class="kv-row row no-gutters">
                  <dt class="col-sm-3 kv-label">📍 Alamat</dt>
                  <dd class="col-sm-9 kv-value"><?= $e($booking->alamat ?? '-') ?></dd>
                </div>
                <div class="kv-row row no-gutters">
                  <dt class="col-sm-3 kv-label">🎂 Tempat/Tanggal Lahir</dt>
                  <dd class="col-sm-9 kv-value"><?= $e($booking->tempat_lahir ?? '-') ?>, <?= tgl_view($e($booking->tanggal_lahir ?? '-')) ?></dd>
                </div>

                <div class="kv-row row no-gutters">
                  <dt class="col-sm-3 kv-label">🧰 Jabatan</dt>
                  <dd class="col-sm-9 kv-value"><?= $e($booking->jabatan ?? '-') ?></dd>
                </div>

                <div class="kv-row row no-gutters">
                  <dt class="col-sm-3 kv-label">🪪 NIK/NIP/NRP</dt>
                  <dd class="col-sm-9 kv-value">
                    <?= $e($booking->nik ?? '-') ?>
                    <?php if (!empty($booking->nik)): ?>
                      <button type="button" class="btn btn-light btn-sm ml-1 btn-copy" data-clip="<?= $e($booking->nik) ?>">
                        <i class="mdi mdi-content-copy"></i> Salin
                      </button>
                    <?php endif; ?>
                  </dd>
                </div>

                <div class="kv-row row no-gutters">
                  <dt class="col-sm-3 kv-label">📞 No. HP</dt>
                  <dd class="col-sm-9 kv-value">
                    <?= $e($booking->no_hp ?? '-') ?>
                    <?php if ($wa_digits): ?>
                      <a class="btn btn-light btn-sm ml-1" target="_blank" rel="noopener" href="https://wa.me/<?= $wa_digits ?>">
                        <i class="mdi mdi-whatsapp"></i>
                      </a>
                    <?php endif; ?>
                  </dd>
                </div>

                <div class="kv-row row no-gutters">
                  <dt class="col-sm-3 kv-label">🏷️ Instansi Asal</dt>
                  <dd class="col-sm-9 kv-value">
                    <?= $e(($booking->target_instansi_nama ?? '') ?: (($booking->instansi ?? '') ?: '-')) ?>
                  </dd>
                </div>

                <div class="kv-row row no-gutters">
                  <dt class="col-sm-3 kv-label">🎯 Unit Tujuan</dt>
                  <dd class="col-sm-9 kv-value"><?= $e($unit_nama) ?></dd>
                </div>

                <div class="kv-row row no-gutters">
                  <dt class="col-sm-3 kv-label">👔 Nama <?= $e($unit_nama) ?></dt>
                  <dd class="col-sm-9 kv-value"><?= $e($booking->nama_petugas_instansi ?? '-') ?></dd>
                </div>

                <div class="kv-row row no-gutters">
                  <dt class="col-sm-3 kv-label">📝 Keperluan</dt>
                  <dd class="col-sm-9 kv-value">
                    <div class="keperluan-box"><?= nl2br($e($booking->keperluan ?? '-')) ?></div>
                  </dd>
                </div>

                <div class="kv-row row no-gutters">
                  <dt class="col-sm-3 kv-label">📅 Jadwal Kunjungan</dt>
                  <dd class="col-sm-9 kv-value"><?= $e($jadwal_text) ?></dd>
                </div>

                <div class="kv-row row no-gutters">
                  <dt class="col-sm-3 kv-label">👥 Pendamping</dt>
                  <dd class="col-sm-9 kv-value">
                    <span class="badge badge-pill badge-primary" style="font-size:.9rem;">
                      <?= (int)($booking->jumlah_pendamping ?? 0) ?> orang
                    </span>
                  </dd>
                </div>

                <?php if (!empty($pendamping_rows)): ?>
                <div class="kv-row row no-gutters">
                  <dt class="col-sm-3 kv-label">📋 Daftar Pendamping</dt>
                  <dd class="col-sm-9 kv-value">
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
                              <td><code><?= $e($p->nik ?? '') ?></code></td>
                              <td><?= $e($p->nama ?? '') ?></td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  </dd>
                </div>
                <?php elseif ((int)($booking->jumlah_pendamping ?? 0) > 0): ?>
                <div class="kv-row row no-gutters">
                  <dt class="col-sm-3 kv-label">📋 Daftar Pendamping</dt>
                  <dd class="col-sm-9 kv-value text-muted">Belum ada data pendamping.</dd>
                </div>
                <?php endif; ?>

                <?php if (!empty($checkin_text)): ?>
                <div class="kv-row row no-gutters">
                  <dt class="col-sm-3 kv-label">⏱️ Check-in</dt>
                  <dd class="col-sm-9 kv-value">
                    <?= $e($checkin_text) ?>
                    <?php if (!empty($petugas_checkin)): ?>
                      <div class="small text-muted">👮 Petugas: <?= $e($petugas_checkin) ?></div>
                    <?php endif; ?>
                  </dd>
                </div>
                <?php endif; ?>

                <?php if (!empty($checkout_text)): ?>
                <div class="kv-row row no-gutters">
                  <dt class="col-sm-3 kv-label">✅ Checkout</dt>
                  <dd class="col-sm-9 kv-value">
                    <?= $e($checkout_text) ?>
                    <?php if (!empty($petugas_checkout)): ?>
                      <div class="small text-muted">👮 Petugas: <?= $e($petugas_checkout) ?></div>
                    <?php endif; ?>
                  </dd>
                </div>
                <?php endif; ?>

                <?php if (!empty($durasi_text)): ?>
                <div class="kv-row row no-gutters">
                  <dt class="col-sm-3 kv-label">🕒 Durasi</dt>
                  <dd class="col-sm-9 kv-value"><?= $e($durasi_text) ?></dd>
                </div>
                <?php endif; ?>
                <!-- === SURAT TUGAS (preview & actions) === -->
                <div class="kv-row row no-gutters">
                  <dt class="col-sm-3 kv-label">📄 Surat Tugas</dt>
                  <dd class="col-sm-9 kv-value">
                    <div id="suratTugasBox">
                      <?php if (!empty($surat_url)): ?>
                        <div class="btn-group btn-group-sm" role="group">
                          <button type="button" class="btn btn-outline-primary"
                          data-toggle="modal" data-target="#modalSuratTugas">
                          <i class="mdi mdi-eye"></i> Lihat
                        </button>
                        &nbsp;
                        <a id="suratTugasUnduh" class="btn btn-outline-secondary"
                        href="<?= $surat_url ?>" download>
                        <i class="mdi mdi-download"></i> Unduh
                      </a>
                    </div>
                    <?php else: ?>
                      <span class="text-muted">Belum ada surat tugas.</span>
                    <?php endif; ?>
                  </div>
                </dd>
              </div>

                <div class="kv-row row no-gutters">
                  <dt class="col-sm-3 kv-label">🖼️ Foto</dt>
                  <dd class="col-sm-9 kv-value">
                    <div id="lampiranFotoBox">
                      <?php if (!empty($foto_url)): ?>
                        <img
                          src="<?= $foto_url ?>"
                          alt="Foto Lampiran"
                          class="img-thumbnail js-lampiran-foto"
                          style="max-height:120px;object-fit:cover;cursor:zoom-in;display:block"
                          data-full="<?= $foto_url ?>"
                        >
                        <a class="btn btn-sm btn-outline-secondary mt-2" href="<?= $foto_url ?>" download>
                          <i class="mdi mdi-download"></i> Unduh Foto
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2 ml-1"
                                data-toggle="modal" data-target="#modalFotoLampiran">
                          <i class="mdi mdi-magnify-plus"></i> Perbesar
                        </button>
                      <?php else: ?>
                        <div class="text-muted">Belum ada foto lampiran.</div>
                      <?php endif; ?>
                    </div>
                  </dd>
                </div>

              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Kanan: Kamera Dokumentasi -->
    <div class="col-lg-5">
      <div class="card doc-card shadow-sm mb-3 cam-card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="mdi mdi-camera-outline"></i> Dokumentasi</h6>
            <small class="text-muted">Kode: <?= $e($booking->kode_booking ?? '-') ?></small>
          </div>

          <div class="mt-2" id="docCamWrap">
            <video id="camPreview" autoplay muted playsinline></video>
            <button id="btnFsTorch" type="button" class="fs-torch-btn d-none" aria-label="Senter">
              <i class="mdi mdi-flashlight"></i><span class="d-none d-sm-inline"> Senter</span>
            </button>
            <button id="btnDocExitFs" type="button" class="fs-exit-btn d-none" aria-label="Tutup layar penuh">
              <i class="mdi mdi-close"></i>
            </button>
            <button id="btnFsShutter" type="button" class="fs-shutter d-none" aria-label="Ambil foto">
              <i class="mdi mdi-camera" style="font-size:28px;"></i>
            </button>
            

          </div>

          <canvas id="camCanvas" class="d-none"></canvas>

          <div class="d-flex flex-wrap mt-2" style="gap:.5rem;">
            <select id="camSelect" class="form-control" style="max-width:300px"></select>
            <button id="btnCamStart"  type="button" class="btn btn-outline-primary btn-sm" <?= $can_capture ? '' : 'disabled' ?>>
              <i class="mdi mdi-play"></i> Nyalakan Kamera
            </button>
            <button id="btnCamCapture" type="button" class="btn btn-primary btn-sm" disabled>
              <i class="mdi mdi-camera"></i> Ambil Foto
            </button>
            <button id="btnCamStop"    type="button" class="btn btn-outline-secondary btn-sm" disabled>
              <i class="mdi mdi-stop"></i> Matikan
            </button>
            <button id="btnCamTorch" type="button" class="btn btn-outline-warning btn-sm" disabled>
              <i class="mdi mdi-flashlight"></i> Senter
            </button>
            <button id="btnFsTorch" type="button" class="fs-torch-btn d-none" aria-label="Senter">
              <i class="mdi mdi-flashlight"></i><span class="d-none d-sm-inline"> Senter</span>
            </button>


            <button id="btnDocFull" class="btn btn-outline-dark btn-sm d-none">
              <i class="mdi mdi-arrow-expand-all"></i> Layar Penuh
            </button>
            <label class="btn btn-blue btn-sm mb-0 <?= $can_capture ? '' : 'disabled' ?>" style="<?= $can_capture?'':'pointer-events:none;opacity:.6;'?>">
              <input id="fileFallback" type="file" accept="image/*" capture="environment" hidden <?= $can_capture ? '' : 'disabled' ?>>
              <i class="mdi mdi-upload"></i> Unggah dari Laptop/HP (kamera penuh)
            </label>
          </div>

          <div class="mt-2">
            <img id="docPreview" class="preview d-none" alt="Pratinjau Dokumentasi">
            <div id="docMsg" class="small text-muted">Belum ada foto.</div>
          </div>

          <div class="mt-2">
            <button id="btnDocSave" type="button" class="btn btn-success btn-sm" disabled>
              <i class="mdi mdi-content-save"></i> Simpan Foto
            </button>
          </div>

          <!-- Upload Surat Tugas -->
          <div class="card shadow-sm mt-3">
            <div class="card-body">
              <h6 class="mb-2 d-flex align-items-center">
                <i class="mdi mdi-file-upload-outline mr-1"></i> Upload Surat Tugas
              </h6>

              <div class="d-flex flex-wrap align-items-center" style="gap:.5rem;">
               <div class="surat-uploader" id="suratUploader">
                <div class="su-dropzone" id="suDropZone">
                  <i class="mdi mdi-cloud-upload-outline su-ico"></i>
                  <div class="su-text"><b>Seret & lepas</b> PDF/JPG/PNG di sini</div>
                  <div class="su-sub">atau</div>
                  <label for="fileSuratTugas" class="btn btn-primary btn-sm">
                    <i class="mdi mdi-file-plus"></i> Pilih Berkas
                  </label>
                  <input id="fileSuratTugas" type="file" accept="application/pdf,image/*" hidden>
                </div>

                <div class="su-filepill d-none" id="suFilePill">
                  <i class="mdi mdi-file-outline"></i>
                  <span id="suFileName">—</span>
                  <span class="su-meta" id="suFileMeta"></span>
                  <button type="button" class="su-clear" id="suClear" aria-label="Hapus">
                    <i class="mdi mdi-close"></i>
                  </button>
                </div>
              </div>

              <div class="mt-2">
                <button id="btnUploadSurat" type="button" class="btn btn-success btn-sm">
                  <i class="mdi mdi-upload"></i> Upload
                </button>
              </div>

              </div>

              <div id="suratProg" class="progress mt-2 d-none" style="height:6px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%"></div>
              </div>

              <small id="suratHint" class="text-muted d-block mt-2">
                Format: PDF/JPG/PNG &middot; Maksimal 2&nbsp;MB.
              </small>
              <div id="suratMsg" class="small mt-1 text-muted"></div>
            </div>
          </div>

        </div>

        <?php if (!$can_capture): ?>
          <div class="overlay">
            <div class="msg">
              <div class="h6 mb-1"><i class="mdi mdi-lock-outline"></i> Kamera dinonaktifkan</div>
              <div>Status saat ini: <b><?= $e($status_label) ?></b>.<br>
                  Ambil dokumentasi hanya tersedia pada status <b>APPROVED</b> atau <b>CHECKED IN</b>.
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ======================== BARIS BAWAH ======================== -->
  <div class="row">
    <!-- Kiri: PDF Ticket -->
    <div class="col-lg-6">
      <div class="card shadow-sm pdf-card mb-3">
        <div class="card-body">
          <h6 class="mb-2 d-flex align-items-center">
            <i class="mdi mdi-file-pdf-box mr-1"></i> Ticket Pengunjung (PDF)
          </h6>
          <div class="pdf-frame">
            <object data="<?= $pdf_url ?>" type="application/pdf">
              <div class="p-3 bg-white">
                <div class="mb-2 font-weight-bold">Pratinjau PDF tidak didukung browser ini.</div>
                <a href="<?= $pdf_url ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                  Buka PDF di tab baru
                </a>
              </div>
            </object>
          </div>
          <small class="text-muted d-block mt-2">
            Catatan: Jika PDF tidak otomatis muncul, klik “Buka PDF di tab baru”.
          </small>
        </div>
      </div>
    </div>

    <!-- Kanan: SURAT PERNYATAAN (PDF) -->
    <div class="col-lg-6">
      <div class="card shadow-sm pdf-card mb-3">
        <div class="card-body">
          <h6 class="mb-2 d-flex align-items-center">
            <i class="mdi mdi-file-pdf-box mr-1"></i> Surat Pernyataan (PDF)
          </h6>
          <div class="pdf-frame">
            <object data="<?= $pernyataan_pdf ?>" type="application/pdf">
              <div class="p-3 bg-white">
                <div class="mb-2 font-weight-bold">Pratinjau PDF tidak didukung browser ini.</div>
                <a href="<?= $pernyataan_pdf ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                  Buka PDF di tab baru
                </a>
              </div>
            </object>
          </div>
          <small class="text-muted d-block mt-2">
            Catatan: Jika PDF tidak otomatis muncul, klik “Buka PDF di tab baru”.
          </small>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Perbesar Foto -->
<div class="modal fade" id="modalFotoLampiran" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title mb-0"><i class="mdi mdi-image"></i> Foto Lampiran</h6>
        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button>
      </div>
      <div class="modal-body p-2 text-center" style="background:#000;">
        <img id="lampiranFotoModalImg" src="<?= !empty($foto_url)?$foto_url:'' ?>"
             alt="Foto Lampiran" style="max-width:100%;max-height:80vh;object-fit:contain">
      </div>
      <div class="modal-footer py-2">
        <a id="lampiranFotoDownload" href="<?= !empty($foto_url)?$foto_url:'#' ?>"
           class="btn btn-outline-primary" download>
          <i class="mdi mdi-download"></i> Unduh
        </a>
        <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Preview Surat Tugas -->
<div class="modal fade" id="modalSuratTugas" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title mb-0"><i class="mdi mdi-file-eye-outline"></i> Surat Tugas</h6>
        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button>
      </div>
      <div class="modal-body p-0" style="background:#111;">
        <div id="suratTugasView" class="w-100" style="min-height:70vh;">
          <!-- akan diisi JS (object/pdf atau img) -->
        </div>
      </div>
      <div class="modal-footer py-2">
        <a id="suratTugasDownloadModal" href="<?= !empty($surat_url)?$surat_url:'#' ?>" class="btn btn-outline-primary" download>
          <i class="mdi mdi-download"></i> Unduh
        </a>
        <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
<script>(function(){function copyToClipboard(text){if(navigator.clipboard)return navigator.clipboard.writeText(text);const ta=document.createElement('textarea');ta.value=text;document.body.appendChild(ta);ta.select();document.execCommand('copy');document.body.removeChild(ta);return Promise.resolve()}
document.querySelectorAll('.btn-copy').forEach(btn=>{btn.addEventListener('click',function(){const txt=this.getAttribute('data-clip')||'';copyToClipboard(txt).then(()=>{this.innerHTML='<i class="mdi mdi-check"></i> Disalin';this.classList.add('btn-success');setTimeout(()=>{this.innerHTML='<i class="mdi mdi-content-copy"></i> Salin';this.classList.remove('btn-success')},1500)})})})})();function setLampiranModal(url){const img=document.getElementById('lampiranFotoModalImg');const dl=document.getElementById('lampiranFotoDownload');if(img)img.src=url||'';if(dl)dl.href=url||'#'}
function renderLampiranFoto(url){const box=document.getElementById('lampiranFotoBox');if(!box)return;if(!url){box.innerHTML='<div class="text-muted">Belum ada foto lampiran.</div>';setLampiranModal('');return}
box.innerHTML=`
      <img src="${url}" alt="Foto Lampiran"
           class="img-thumbnail js-lampiran-foto"
           style="max-height:120px;object-fit:cover;cursor:zoom-in;display:block"
           data-full="${url}">
      <a class="btn btn-sm btn-outline-secondary mt-2" href="${url}" download>
        <i class="mdi mdi-download"></i> Unduh Foto
      </a>
      <button type="button" class="btn btn-sm btn-outline-primary mt-2 ml-1"
              data-toggle="modal" data-target="#modalFotoLampiran">
        <i class="mdi mdi-magnify-plus"></i> Perbesar
      </button>
    `;setLampiranModal(url)}
document.addEventListener('click',function(e){const el=e.target.closest('.js-lampiran-foto');if(!el)return;const full=el.getAttribute('data-full')||el.getAttribute('src')||'';setLampiranModal(full);if(window.jQuery){$('#modalFotoLampiran').modal('show')}else{document.querySelector('[data-target="#modalFotoLampiran"]')?.click()}});</script><script>(function(){const CAN_CAPTURE=<?=$can_capture?'true':'false'?>;const STATUS_LABEL=<?=json_encode($status_label)?>;const KODE=<?=json_encode($booking->kode_booking??'')?>;const wrap=document.getElementById('docCamWrap');const video=document.getElementById('camPreview');const canvas=document.getElementById('camCanvas');const imgPrev=document.getElementById('docPreview');const msg=document.getElementById('docMsg');const ddlCam=document.getElementById('camSelect');const bStart=document.getElementById('btnCamStart');const bStop=document.getElementById('btnCamStop');const bShot=document.getElementById('btnCamCapture');const bSave=document.getElementById('btnDocSave');const bFull=document.getElementById('btnDocFull');const bExitFs=document.getElementById('btnDocExitFs');const bFsShut=document.getElementById('btnFsShutter');const bTorch=document.getElementById('btnCamTorch');const bFsTorch=document.getElementById('btnFsTorch');const fFile=document.getElementById('fileFallback');const safeRenderLampiran=(window.renderLampiranFoto||function(){}).bind(window);let stream=null;let currentDeviceId=null;let torchTrack=null;let torchOn=!1;let torchCap=!1;function setMsg(t,ok=!1){if(!msg)return;msg.textContent=t;msg.className=ok?'small text-success':'small text-muted'}
const isFsActive=()=>document.fullscreenElement===wrap||wrap?.classList.contains('fullscreen-scan')||wrap?.classList.contains('is-fs');async function ensureLabels(){try{const t=await navigator.mediaDevices.getUserMedia({video:!0,audio:!1});t.getTracks().forEach(x=>x.stop())}catch(e){}}
async function listCams(){const devs=await navigator.mediaDevices.enumerateDevices();return devs.filter(d=>d.kind==='videoinput')}
function pickDefault(cams){if(!cams.length)return null;const by=re=>cams.find(d=>re.test(d.label||''));return by(/usb|external|logitech|webcam|brio|hd pro/i)||by(/back|rear|environment/i)||cams[0]}
async function fillCamSelect(selectedId=null){if(!ddlCam)return[];const cams=await listCams();ddlCam.innerHTML='';cams.forEach((c,i)=>{const opt=document.createElement('option');opt.value=c.deviceId||'';opt.textContent=c.label||`Kamera ${i+1}`;ddlCam.appendChild(opt)});const def=selectedId&&cams.some(c=>c.deviceId===selectedId)?selectedId:(pickDefault(cams)?.deviceId||(cams[0]?.deviceId||''));if(def)ddlCam.value=def;return cams}
async function startWithDevice(deviceId){if(stream){stream.getTracks().forEach(t=>t.stop());stream=null}
const constraints=deviceId?{video:{deviceId:{exact:deviceId},width:{ideal:1280},height:{ideal:720}},audio:!1}:{video:{facingMode:{ideal:'environment'},width:{ideal:1280},height:{ideal:720}},audio:!1};stream=await navigator.mediaDevices.getUserMedia(constraints);video.srcObject=stream;await video.play().catch(()=>{});currentDeviceId=stream.getVideoTracks()[0]?.getSettings()?.deviceId||deviceId||null;if(bShot)bShot.disabled=!1;if(bStop)bStop.disabled=!1;if(bFull)bFull.classList?.remove('d-none');await setupTorch();setMsg('Kamera aktif.',!0)}
async function startCam(){if(!CAN_CAPTURE){setMsg('Kamera dinonaktifkan (status: '+STATUS_LABEL+').');return}
try{await ensureLabels();const cams=await fillCamSelect(currentDeviceId||localStorage.getItem('doc.camId'));if(!cams.length){setMsg('Tidak ada kamera terdeteksi.');return}
const chosenId=ddlCam?.value||pickDefault(cams)?.deviceId||null;await startWithDevice(chosenId);try{localStorage.setItem('doc.camId',chosenId||'')}catch(e){}}catch(e){setMsg('Tidak bisa mengakses kamera: '+(e?.message||e))}}
function stopCam(){turnOffTorch();if(stream){stream.getTracks().forEach(t=>t.stop());stream=null}
if(video)video.srcObject=null;if(bShot)bShot.disabled=!0;if(bStop)bStop.disabled=!0;if(bFull)bFull.classList?.add('d-none');disableTorchUI();hideFsUi();exitFullscreen();setMsg('Kamera dimatikan.')}
function capture(){if(!video||!video.videoWidth)return;const w=video.videoWidth,h=video.videoHeight;const maxW=1280,scale=Math.min(1,maxW/w);canvas.width=Math.round(w*scale);canvas.height=Math.round(h*scale);const ctx=canvas.getContext('2d');ctx.drawImage(video,0,0,canvas.width,canvas.height);const dataUrl=canvas.toDataURL('image/jpeg',0.9);imgPrev.src=dataUrl;imgPrev.classList.remove('d-none');if(bSave)bSave.disabled=!1;setMsg('Pratinjau siap. Klik "Simpan Foto".',!0)}
function captureAndExit(){capture();if(isFsActive()){setTimeout(()=>exitFullscreen(),80)}}
function readFile(file){const r=new FileReader();r.onload=()=>{imgPrev.src=r.result;imgPrev.classList.remove('d-none');if(bSave)bSave.disabled=!1;setMsg('Pratinjau siap (unggah). Klik "Simpan Foto".',!0)};r.readAsDataURL(file)}
async function saveDoc(){const dataUrl=imgPrev.src||'';if(!/^data:image\//.test(dataUrl))return;const params=new URLSearchParams();params.set('kode',KODE);params.set('image',dataUrl);<?php if(config_item('csrf_protection')):?>params.set('<?= $CI->security->get_csrf_token_name() ?>','<?= $CI->security->get_csrf_hash() ?>');<?php endif;?>if(bSave)bSave.disabled=!0;setMsg('Menyimpan...',!1);try{const r=await fetch('<?= site_url('admin_scan/upload_doc_photo') ?>',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'},body:params.toString(),credentials:'same-origin'});const j=await r.json();if(j.ok){setMsg('Tersimpan: '+(j.file||''),!0);if(j.url){imgPrev.src=j.url;safeRenderLampiran(j.url)}}else{setMsg('Gagal: '+(j.msg||''));if(bSave)bSave.disabled=!1}}catch(e){setMsg('Error: '+(e?.message||e));if(bSave)bSave.disabled=!1}}
function updateTorchUI(){if(bTorch){bTorch.disabled=!torchCap;bTorch.classList.toggle('btn-warning',torchOn);bTorch.classList.toggle('btn-outline-warning',!torchOn);bTorch.innerHTML=`<i class="mdi ${torchOn?'mdi-flashlight-off':'mdi-flashlight'}"></i> ${torchOn?'Matikan':'Senter'}`}
if(bFsTorch){if(isFsActive()&&torchCap)bFsTorch.classList.remove('d-none');else bFsTorch.classList.add('d-none');bFsTorch.classList.toggle('active',torchOn);bFsTorch.innerHTML=`<i class="mdi ${torchOn?'mdi-flashlight-off':'mdi-flashlight'}"></i><span class="d-none d-sm-inline"> ${torchOn?'Matikan':'Senter'}</span>`}}
function disableTorchUI(){torchTrack=null;torchOn=!1;torchCap=!1;updateTorchUI()}
async function setupTorch(){disableTorchUI();const stream=video.srcObject;if(!stream)return;const track=stream.getVideoTracks()[0];if(!track)return;const caps=(track.getCapabilities&&track.getCapabilities())||{};if(caps.torch){torchTrack=track;torchCap=!0;torchOn=(track.getSettings&&!!track.getSettings().torch)||!1}
updateTorchUI()}
async function toggleTorch(){if(!torchTrack)return;try{await torchTrack.applyConstraints({advanced:[{torch:!torchOn}]});torchOn=!torchOn;updateTorchUI()}catch(e){setMsg('Senter tidak didukung/ditolak di perangkat ini.',!1);disableTorchUI()}}
function turnOffTorch(){if(!torchTrack||!torchOn)return;try{torchTrack.applyConstraints({advanced:[{torch:!1}]})}catch(e){}
torchOn=!1;updateTorchUI()}
async function enterFullscreen(){try{if(wrap&&wrap.requestFullscreen){await wrap.requestFullscreen();wrap.classList.add('is-fs');showFsUi();return}
if(video&&video.webkitEnterFullscreen){video.webkitEnterFullscreen();wrap.classList.add('is-fs');showFsUi();return}}catch(e){}
wrap?.classList.add('fullscreen-scan','is-fs');document.body.classList.add('scan-lock');showFsUi()}
function exitFullscreen(){if(document.fullscreenElement&&document.exitFullscreen){document.exitFullscreen()}
wrap?.classList.remove('fullscreen-scan','is-fs');document.body.classList.remove('scan-lock');hideFsUi()}
function showFsUi(){bExitFs?.classList.remove('d-none');bFsShut?.classList.remove('d-none');updateTorchUI()}
function hideFsUi(){bExitFs?.classList.add('d-none');bFsShut?.classList.add('d-none');bFsTorch?.classList.add('d-none')}
document.addEventListener('fullscreenchange',()=>{if(!document.fullscreenElement){wrap?.classList.remove('fullscreen-scan','is-fs');document.body.classList.remove('scan-lock');hideFsUi()}else{wrap?.classList.add('is-fs');showFsUi()}});bStart&&bStart.addEventListener('click',startCam);bStop&&bStop.addEventListener('click',stopCam);bShot&&bShot.addEventListener('click',capture);bFsShut&&bFsShut?.addEventListener('click',captureAndExit);video&&video.addEventListener('click',()=>{if(isFsActive())captureAndExit();});bSave&&bSave.addEventListener('click',saveDoc);fFile&&fFile.addEventListener('change',(e)=>{if(e.target.files&&e.target.files[0])readFile(e.target.files[0]);});ddlCam&&ddlCam.addEventListener('change',async(e)=>{const id=e.target.value||null;try{await startWithDevice(id);try{localStorage.setItem('doc.camId',id||'')}catch(_){}}catch(err){setMsg('Gagal beralih kamera: '+(err?.message||err))}});bFull&&bFull.addEventListener('click',async()=>{if(stream){await enterFullscreen()}});bExitFs&&bExitFs?.addEventListener('click',exitFullscreen);bTorch&&bTorch.addEventListener('click',toggleTorch);bFsTorch&&bFsTorch.addEventListener('click',toggleTorch);document.addEventListener('keydown',(e)=>{if(e.key==='Escape')exitFullscreen();});navigator.mediaDevices?.addEventListener?.('devicechange',async()=>{const cams=await fillCamSelect(currentDeviceId);const stillThere=cams.some(c=>c.deviceId===currentDeviceId);if(!stillThere){const next=pickDefault(cams);if(next&&stream){setMsg('Perangkat berubah—beralih kamera…');await startWithDevice(next.deviceId);if(ddlCam)ddlCam.value=next.deviceId}else if(stream){stopCam();setMsg('Semua kamera tidak tersedia.')}}});(async()=>{if(!CAN_CAPTURE){bStart?.setAttribute('disabled','disabled');bShot?.setAttribute('disabled','disabled');bStop?.setAttribute('disabled','disabled');fFile?.setAttribute('disabled','disabled');ddlCam?.setAttribute('disabled','disabled');setMsg('Kamera dinonaktifkan (status: '+STATUS_LABEL+').');return}
await ensureLabels();await fillCamSelect(localStorage.getItem('doc.camId'))})();window.addEventListener('beforeunload',stopCam)})();</script><script>(function(){const KODE=<?=json_encode($booking->kode_booking??'')?>;const INIT_SURAT=<?=json_encode($surat_url??'')?>;const fileInput=document.getElementById('fileSuratTugas');const btnUpload=document.getElementById('btnUploadSurat');const progWrap=document.getElementById('suratProg');const progBar=progWrap?progWrap.querySelector('.progress-bar'):null;const msgEl=document.getElementById('suratMsg');const box=document.getElementById('suratTugasBox');const modalView=document.getElementById('suratTugasView');const modalDl=document.getElementById('suratTugasDownloadModal');function setMsgSurat(t,ok){if(!msgEl)return;msgEl.textContent=t;msgEl.className='small '+(ok?'text-success':'text-muted')}
function showProgress(show){if(!progWrap)return;progWrap.classList.toggle('d-none',!show);if(show&&progBar)progBar.style.width='0%'}
function setProgress(pct){if(progBar)progBar.style.width=Math.max(0,Math.min(100,pct))+'%'}
function isPdf(url){return/\.pdf(?:\?|#|$)/i.test(url||'')}
function renderSuratModal(url){if(!modalView)return;modalView.innerHTML='';if(!url){modalView.innerHTML='<div class="p-3 text-white-50">Tidak ada berkas.</div>';return}
if(isPdf(url)){const obj=document.createElement('object');obj.setAttribute('data',url);obj.setAttribute('type','application/pdf');obj.style.width='100%';obj.style.height='70vh';obj.style.border='0';modalView.appendChild(obj)}else{const img=document.createElement('img');img.src=url;img.alt='Surat Tugas';img.style.maxWidth='100%';img.style.maxHeight='80vh';img.style.objectFit='contain';img.style.display='block';img.style.margin='0 auto';modalView.appendChild(img)}
if(modalDl)modalDl.href=url}
function renderSuratBox(url){if(!box)return;if(!url){box.innerHTML='<span class="text-muted">Belum ada surat tugas.</span>';renderSuratModal('');return}
box.innerHTML=`
      <div class="btn-group btn-group-sm" role="group">
        <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#modalSuratTugas">
          <i class="mdi mdi-eye"></i> Lihat
        </button>
        <a id="suratTugasUnduh" class="btn btn-outline-secondary ml-1" href="${url}" download>
          <i class="mdi mdi-download"></i> Unduh
        </a>
      </div>
    `;renderSuratModal(url)}
renderSuratBox(INIT_SURAT);function uploadSurat(file){return new Promise((resolve,reject)=>{const fd=new FormData();fd.append('kode',KODE);fd.append('surat_tugas',file);<?php if(config_item('csrf_protection')):?>fd.append('<?= $CI->security->get_csrf_token_name() ?>','<?= $CI->security->get_csrf_hash() ?>');<?php endif;?>const xhr=new XMLHttpRequest();xhr.open('POST','<?= site_url('admin_scan/upload_surat_tugas') ?>',!0);xhr.responseType='json';xhr.upload.onprogress=(e)=>{if(e.lengthComputable){setProgress(Math.round((e.loaded/e.total)*100))}};xhr.onload=()=>{const j=xhr.response||{};if(xhr.status>=200&&xhr.status<300&&j.ok){resolve(j)}else{reject(j&&j.msg?j.msg:('Gagal upload ('+xhr.status+')'))}};xhr.onerror=()=>reject('Jaringan bermasalah.');xhr.send(fd)})}
function validateFile(f){if(!f)return'Pilih berkas terlebih dahulu.';const okType=/^(application\/pdf|image\/jpeg|image\/png)$/i.test(f.type)||/\.(pdf|jpe?g|png)$/i.test(f.name);if(!okType)return'Format harus PDF/JPG/PNG.';if(f.size>2*1024*1024)return'Ukuran melebihi 2MB.';return''}
btnUpload&&btnUpload.addEventListener('click',async()=>{const f=fileInput?.files?.[0];const err=validateFile(f);if(err){setMsgSurat(err,!1);return}
btnUpload.disabled=!0;showProgress(!0);setMsgSurat('Mengunggah...',!1);try{const res=await uploadSurat(f);const url=res.url||'';setMsgSurat('Surat tugas tersimpan.',!0);renderSuratBox(url);if(fileInput)fileInput.value=''}catch(e){setMsgSurat(String(e||'Upload gagal'),!1)}finally{setProgress(100);setTimeout(()=>showProgress(!1),600);btnUpload.disabled=!1}});document.addEventListener('click',function(e){const a=e.target.closest('#suratTugasUnduh, #lampiranFotoDownload');if(!a)return;a.classList.add('btn-loading');setTimeout(()=>a.classList.remove('btn-loading'),2000)})})();</script><script>(function(){const fileInput=document.getElementById('fileSuratTugas');const dropZone=document.getElementById('suDropZone');const pill=document.getElementById('suFilePill');const fName=document.getElementById('suFileName');const fMeta=document.getElementById('suFileMeta');const btnClear=document.getElementById('suClear');if(!fileInput||!dropZone)return;function fmtBytes(b){if(!b&&b!==0)return'';if(b<1024)return b+' B';if(b<1024*1024)return(b/1024).toFixed(1)+' KB';return(b/1024/1024).toFixed(2)+' MB'}
function extOf(name){const m=String(name||'').match(/\.([a-z0-9]+)$/i);return m?m[1].toUpperCase():''}
function showFileInfo(file){if(!file){pill.classList.add('d-none');return}
fName.textContent=file.name;const type=(file.type||extOf(file.name)||'').replace('image/','IMG ').replace('application/','');fMeta.textContent=(type?type+' • ':'')+fmtBytes(file.size);pill.classList.remove('d-none')}
function clearFile(){try{const dt=new DataTransfer();fileInput.files=dt.files}catch(_){fileInput.value=''}
pill.classList.add('d-none')}
fileInput.addEventListener('change',()=>showFileInfo(fileInput.files[0]));['dragenter','dragover'].forEach(ev=>{dropZone.addEventListener(ev,e=>{e.preventDefault();e.stopPropagation();dropZone.classList.add('is-drag')})});['dragleave','drop'].forEach(ev=>{dropZone.addEventListener(ev,e=>{e.preventDefault();e.stopPropagation();dropZone.classList.remove('is-drag')})});dropZone.addEventListener('drop',e=>{const file=(e.dataTransfer&&e.dataTransfer.files&&e.dataTransfer.files[0])||null;if(!file)return;try{const dt=new DataTransfer();dt.items.add(file);fileInput.files=dt.files}catch(_){}
showFileInfo(file)});btnClear&&btnClear.addEventListener('click',clearFile)})();</script>
