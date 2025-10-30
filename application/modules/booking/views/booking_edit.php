<?php
/** booking_edit.php — View Ubah Booking
 *  Pastikan controller mengirimkan:
 *  - $controller, $title, $rec, $units_tree
 *  - $booking: object record booking_tamu
 */
$this->load->view("front_end/head.php");
?>

<!-- Flatpickr -->
<link rel="stylesheet" href="<?= base_url("assets/admin/libs/flatpickr/flatpickr.min.css") ?>">
<script src="<?= base_url("assets/admin/libs/flatpickr/flatpickr.min.js") ?>"></script>

<!-- Locale Indonesian (inline) -->
<script>
(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports) :
  typeof define === 'function' && define.amd ? define(['exports'], factory) :
  (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.id = {}));
}(this, (function (exports) { 'use strict';
  var fp = typeof window !== "undefined" && window.flatpickr !== undefined ? window.flatpickr : { l10ns: {} };
  var Indonesian = {
    weekdays: { shorthand: ["Min","Sen","Sel","Rab","Kam","Jum","Sab"], longhand: ["Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu"] },
    months:   { shorthand: ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"], longhand: ["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"] },
    firstDayOfWeek: 1, ordinal: function(){ return ""; }, time_24hr: true, rangeSeparator: " - ",
  };
  fp.l10ns.id = Indonesian; var id = fp.l10ns;
  exports.Indonesian = Indonesian; exports.default = id;
  Object.defineProperty(exports, '__esModule', { value: true });
})));
</script>

<style>
  /* ===== Tampilan umum ===== */
  .card-elev { border:0; border-radius:14px; box-shadow:0 6px 22px rgba(0,0,0,.06); }
  .header-title{ font-size:1.05rem; font-weight:700; margin:.5rem 0 1rem; }
  .form-label { font-weight: 600; }
  .label-required::after{ content:" *"; color:#dc3545; font-weight:700; }
  .help-hint,.small-muted{ color:#6c757d; font-size:.85rem; }
  .btn-blue{ background:linear-gradient(90deg,#2563eb,#1d4ed8); border:0; color:#fff; }
  .btn-blue:hover{ filter:brightness(1.06); }
  .btn-gray{ background:#e9ecef; border:0; color:#111827; }
  .divider-soft{ height:1px; background:linear-gradient(to right,transparent,#e9ecef,transparent); margin: 1rem 0 1.25rem; }
  .badge-soft { background:#eef2ff; color:#374151; border:1px solid #e5e7eb; font-weight:600; border-radius:999px; padding:.25rem .5rem; }

  /* Tabel pendamping */
  .table-modern { --tbl-bg:#fff; --tbl-head:#f8f9fc; --tbl-border:#eef2f7; background:var(--tbl-bg); border-radius:14px; overflow:hidden; box-shadow:0 8px 28px rgba(0,0,0,.06); }
  .table-modern thead th { background:var(--tbl-head); border-bottom:1px solid var(--tbl-border); font-weight:700; font-size:.9rem; position:sticky; top:0; z-index:1; }
  .table-modern tbody td { vertical-align:middle; border-color:var(--tbl-border); }
  .table-modern tbody tr:hover { background:#f9fbff; }
  .table-modern .btn-action { border:1px solid #e3e8f0; padding:.25rem .5rem; border-radius:10px; }

  /* ===== Modal core ===== */
  .modal { z-index: 2000 !important; }
  .modal-backdrop { z-index: 1990 !important; }
  .modal-header { border-bottom: 0; }
  .modal-content { border: 0; border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,.18); }
  .modal-footer { border-top: 0; }

  /* ===== Upload Card (Keperluan & Lampiran) ===== */
  .upload-card{border:1px dashed #e5e7eb;border-radius:14px;padding:12px 14px;background:#fafbff}
  .upload-head{display:flex;align-items:center;justify-content:space-between;gap:.5rem;margin-bottom:.35rem}
  .upload-title{font-weight:700;margin:0}
  .upload-actions{display:flex;flex-wrap:wrap;gap:.5rem}
  .file-badge{display:inline-flex;align-items:center;gap:.35rem;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:999px;padding:.2rem .6rem;font-size:.85rem}
  .btn-soft{background:#eef2ff;border:1px solid #e5e7eb}
  .hint{color:#6b7280;font-size:.85rem}

  /* ===== Matikan blur backdrop SAAT modal preview (surat/foto) ===== */
  body.noblur-backdrop .modal-backdrop {
    backdrop-filter: none !important; -webkit-backdrop-filter: none !important; filter:none !important;
    background: rgba(0,0,0,.55) !important; z-index: 2040 !important; position: fixed !important;
  }
  body.noblur-backdrop .modal { z-index: 2050 !important; }
  body.noblur-backdrop .content,
  body.noblur-backdrop .page-wrapper,
  body.noblur-backdrop .wrapper,
  body.noblur-backdrop main,
  body.noblur-backdrop #app { filter:none !important; -webkit-filter:none !important; transform:none !important; }

  /* ===== Modal Pendamping paling atas + non-blur ===== */
  #modalPendamping { z-index: 2055 !important; }
  #modalPendamping .modal-dialog, #modalPendamping .modal-content { pointer-events:auto !important; }
  .modal-backdrop.pendamping-backdrop {
    z-index:2050 !important; position:fixed !important; backdrop-filter:none !important; -webkit-backdrop-filter:none !important; filter:none !important; background:rgba(0,0,0,.55) !important;
  }

  /* ===== Pastikan modal preview selalu di atas ===== */
  #modalPreviewSurat, #modalPreviewFoto { z-index: 222222 !important; }

  /* ===== Sembunyikan SEMUA thumbnail di form (foto lama & preview cepat) ===== */
  .thumb-live, #fotoThumbLive { display: none !important; }
</style>

<?php
  // Helper kecil agar aman
  $B = $booking ?? (object)[];
  $kode     = htmlspecialchars((string)($B->kode_booking ?? ''), ENT_QUOTES, 'UTF-8');
  $token    = htmlspecialchars((string)($B->access_token ?? ''), ENT_QUOTES, 'UTF-8');
  $kat      = htmlspecialchars((string)($B->target_kategori ?? ''), ENT_QUOTES, 'UTF-8');
  $instId   = (int)($B->target_instansi_id ?? 0);
  $instNm   = htmlspecialchars((string)($B->target_instansi_nama ?? ($B->instansi ?? '')), ENT_QUOTES, 'UTF-8');
  $unitSel  = (int)($B->unit_tujuan ?? 0);
  $tglYmd   = htmlspecialchars((string)($B->tanggal ?? ''), ENT_QUOTES, 'UTF-8');
  $jamHHMM  = htmlspecialchars((string)($B->jam ?? ''), ENT_QUOTES, 'UTF-8');
  $lahirYmd = htmlspecialchars((string)($B->tanggal_lahir ?? ''), ENT_QUOTES, 'UTF-8');
  $fotoFile = htmlspecialchars((string)($B->foto ?? ''), ENT_QUOTES, 'UTF-8');
  $stFile   = htmlspecialchars((string)($B->surat_tugas ?? ''), ENT_QUOTES, 'UTF-8');
?>

<div class="container-fluid">
  <div class="hero-title" role="banner" aria-label="Judul halaman">
    <h1 class="text"><?= htmlspecialchars($title ?? 'Ubah Booking', ENT_QUOTES, 'UTF-8') ?></h1>
    <div class="text-muted mb-2">Perbarui data booking Anda. Kode:</div>
    <span class="badge-soft"><?= $kode ?: '-' ?></span>
    <span class="accent" aria-hidden="true"></span>
  </div>

  <div class="row">
    <div class="col-lg-12">
      <div class="card card-elev">
        <div class="card-body">
          <form id="form_edit" method="post" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="kode_booking" value="<?= $kode ?>">
            <input type="hidden" name="access_token" value="<?= $token ?>">

            <!-- ====== Asal Instansi ====== -->
            <div class="header-title">Asal Instansi</div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group mb-2">
                  <label for="kategori" class="form-label label-required">Kategori Instansi</label>
                  <select id="kategori" name="kategori" class="form-control" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option value="opd"    <?= $kat==='opd'?'selected':''; ?>>Dinas Pemprov Sulsel</option>
                    <option value="pn"     <?= $kat==='pn'?'selected':''; ?>>Pengadilan Negeri</option>
                    <option value="pa"     <?= $kat==='pa'?'selected':''; ?>>Pengadilan Agama</option>
                    <option value="ptun"   <?= $kat==='ptun'?'selected':''; ?>>PTUN Makassar</option>
                    <option value="kejati" <?= $kat==='kejati'?'selected':''; ?>>Kejaksaan Tinggi</option>
                    <option value="kejari" <?= $kat==='kejari'?'selected':''; ?>>Kejaksaan Negeri</option>
                    <option value="cabjari"<?= $kat==='cabjari'?'selected':''; ?>>Cabang Kejaksaan Negeri</option>
                    <option value="bnn"    <?= $kat==='bnn'?'selected':''; ?>>BNN</option>
                    <option value="kodim"  <?= $kat==='kodim'?'selected':''; ?>>Kodim Wil. Kodam XIV/Hasanuddin</option>
                    <option value="kepolisian"  <?= $kat==='kepolisian'?'selected':''; ?>>Kepolisian</option>
                    <option value="lainnya"<?= $kat==='lainnya'?'selected':''; ?>>Lainnya</option>
                  </select>
                  <small class="help-hint">Jika tidak ada di daftar, pilih <b>Lainnya</b>.</small>
                </div>
              </div>

              <div class="col-md-6">
                <!-- MODE SELECT -->
                <div class="form-group mb-2" id="instansi_select_wrap">
                  <label for="instansi" class="form-label <?= $kat!=='lainnya'?'label-required':''; ?>">Instansi</label>
                  <select id="instansi" name="instansi_id" class="form-control" <?= $kat==='lainnya'?'disabled':''; ?>>
                    <option value="">-- Pilih Instansi --</option>
                  </select>
                  <small class="help-hint">Pilih kategori terlebih dahulu untuk menampilkan daftar instansi.</small>
                </div>

                <!-- MODE MANUAL -->
                <div class="form-group mb-2 <?= $kat==='lainnya'?'':'d-none' ?>" id="instansi_manual_wrap">
                  <label for="instansi_manual" class="form-label label-required">Nama Instansi</label>
                  <input type="text" id="instansi_manual" name="target_instansi_nama" class="form-control" placeholder="Tulis nama instansi"
                         value="<?= $kat==='lainnya' ? $instNm : '' ?>">
                  <small class="help-hint">Contoh: KPP Pratama Makassar Utara</small>
                </div>
              </div>
            </div>

            <div class="divider-soft"></div>

            <!-- ====== Unit Tujuan ====== -->
            <div class="header-title">Unit Tujuan Lapas</div>
            <div class="form-group mb-2">
              <label for="unit_tujuan" class="form-label label-required">Unit Tujuan</label>
              <select id="unit_tujuan" name="unit_tujuan" class="form-control" required>
                <option value="">-- Pilih Unit --</option>
                <?php
                function render_options_edit($tree, $level = 0, $selected = 0) {
                  $no=1;
                  foreach ($tree as $node) {
                    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
                    $prefix = ($level > 0) ? $no.'. ' : '';
                    $label  = htmlspecialchars($node->nama_unit, ENT_QUOTES, 'UTF-8');
                    $content= $indent.$prefix.$label;
                    $sel    = ((int)$node->id === (int)$selected) ? 'selected' : '';
                    echo '<option value="'.(int)$node->id.'" '.$sel.' data-content="'.$content.'">'.$content.'</option>';
                    if (!empty($node->children)) render_options_edit($node->children, $level+1, $selected);
                    $no++;
                  }
                }
                render_options_edit($units_tree, 0, $unitSel);
                ?>
              </select>
            </div>

            <div class="divider-soft"></div>

            <!-- ====== Data Tamu ====== -->
            <div class="header-title">Data Tamu</div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group mb-2">
                  <label for="nama_tamu" class="form-label label-required">Nama Tamu</label>
                  <input type="text" id="nama_tamu" name="nama_tamu" class="form-control" required
                         value="<?= htmlspecialchars((string)($B->nama_tamu ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group mb-2">
                  <label for="id_number" class="form-label label-required">NIK/NIP/NRP</label>
                  <input type="text" id="id_number" name="nik" class="form-control"
                         placeholder="NIK 16 / NIP 18 atau 9 / NRP 8–9"
                         inputmode="numeric" pattern="(?:\d{8,9}|\d{16}|\d{18})" maxlength="18" required
                         value="<?= htmlspecialchars(preg_replace('/\D+/','',(string)($B->nik ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                  <small id="id_help" class="help-hint">
                    Boleh: <b>NIK</b> 16 digit • <b>NIP</b> 18 atau 9 digit • <b>NRP</b> 8–9 digit.
                  </small>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group mb-2">
                  <label for="jabatan" class="form-label label-required">Jabatan</label>
                  <input type="text" id="jabatan" name="jabatan" class="form-control" required
                         value="<?= htmlspecialchars((string)($B->jabatan ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group mb-2">
                  <label for="alamat" class="form-label label-required">Alamat Tamu</label>
                  <input type="text" id="alamat" name="alamat" class="form-control" required
                         value="<?= htmlspecialchars((string)($B->alamat ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                  <small class="help-hint">Alamat sesuai KTP.</small>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group mb-2">
                  <label class="form-label label-required mb-1">Tempat / Tanggal Lahir</label>
                  <div class="row">
                    <div class="col-6">
                      <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir"
                             placeholder="Tempat lahir (mis. Makassar)" required autocomplete="off"
                             value="<?= htmlspecialchars((string)($B->tempat_lahir ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                      <div class="invalid-feedback">Tempat lahir wajib diisi.</div>
                    </div>
                    <div class="col-6">
                      <input type="text" class="form-control" id="tanggal_lahir_view" placeholder="dd/mm/yyyy" autocomplete="off" required>
                      <input type="hidden" id="tanggal_lahir" name="tanggal_lahir" value="<?= $lahirYmd ?>">
                      <div class="invalid-feedback">Tanggal lahir wajib diisi.</div>
                    </div>
                  </div>
                  <small class="form-text text-muted">Contoh: Makassar — 21-02-1990</small>
                </div>
              </div>

              

              <div class="col-md-3">
                <div class="form-group mb-2">
                  <label for="no_hp" class="form-label label-required">No. HP</label>
                  <input type="text" id="no_hp" name="no_hp" class="form-control"
                         placeholder="08xxxxxxxxxx" inputmode="numeric"
                         minlength="10" maxlength="13" pattern="0\d{9,12}" title="Mulai 0, total 10–13 digit" required
                         value="<?= htmlspecialchars((string)($B->no_hp ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                  <small class="help-hint">Gunakan nomor aktif untuk menerima WhatsApp konfirmasi.</small>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group mb-2">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" id="email" name="email" class="form-control"
                  value="<?= htmlspecialchars((string)($B->email ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                  <small class="help-hint">Gunakan email aktif untuk menerima Email konfirmasi.</small>
                </div>
              </div>


              <!-- ====== Pendamping (opsional) ====== -->
              <div class="col-md-12">
                <div class="header-title d-flex justify-content-between align-items-center">
                  <span>Pendamping</span>
                  <button type="button" class="btn btn-sm btn-blue" id="btnOpenPendamping">
                    <i class="fas fa-user-plus mr-1"></i> Tambah Pendamping
                  </button>
                </div>

                <div id="pdTableWrap" class="table-responsive d-none">
                  <table class="table table-hover align-middle mb-0 table-modern" id="tblPendampingLocal">
                    <thead>
                      <tr>
                        <th style="width:64px" class="text-center">No</th>
                        <th style="width:220px">NIK/NIP/NRP</th>
                        <th>Nama</th>
                        <th style="width:160px" class="text-center">Aksi</th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                  </table>
                </div>

                <input type="hidden" name="pendamping_json" id="pendamping_json" value="[]">
                <input type="hidden" name="jumlah_pendamping" id="jumlah_pendamping" value="<?= (int)($B->jumlah_pendamping ?? 0) ?>">

                <!-- Modal Pendamping -->
                <div class="modal fade pendamping-modal" id="modalPendamping" tabindex="-1" aria-labelledby="modalPendampingLabel" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="modalPendampingLabel">Tambah Pendamping</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                        <div class="form-group">
                          <label class="form-label">NIK/NIP/NRP</label>
                          <input type="text" id="pd_nik" class="form-control"
                                 placeholder="NIK 16 / NIP 18 atau 9 / NRP 8–9"
                                 maxlength="18" inputmode="numeric" autocomplete="off">
                          <small class="text-muted">Boleh: <b>NIK</b> 16 digit • <b>NIP</b> 18 atau 9 digit • <b>NRP</b> 8–9 digit.</small>
                        </div>
                        <div class="form-group">
                          <label class="form-label">Nama Pendamping</label>
                          <input type="text" id="pd_nama" class="form-control" placeholder="Nama lengkap" autocomplete="off">
                        </div>
                        <div id="pdWarn" class="small text-danger d-none" role="alert" aria-live="assertive"></div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-blue" id="btnPdSubmit">Simpan</button>
                      </div>
                    </div>
                  </div>
                </div>

                <small id="pdInfoText" class="text-muted d-block mt-2">Jika tidak membawa pendamping, lewati bagian ini.</small>
                <div class="small text-muted mt-1 d-flex align-items-center" style="gap:.5rem;">
                  <span id="pdLimitInfo" class="badge-soft">Batas pendamping: tidak dibatasi.</span>
                  <span id="pdCountBadgeWrap" class="badge-soft d-none">Total pendamping: <span id="pdFilledBadge">0</span></span>
                </div>
              </div>
            </div>

            <div class="divider-soft"></div>

            <!-- ====== Jadwal Kunjungan ====== -->
            <div class="header-title">Jadwal Kunjungan</div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group mb-2">
                  <label for="tanggal_view" class="form-label label-required">Tanggal Kunjungan</label>
                  <input type="text" id="tanggal_view" class="form-control" placeholder="dd/mm/yyyy" inputmode="numeric" autocomplete="off" required>
                  <input type="hidden" id="tanggal" name="tanggal" value="<?= $tglYmd ?>">
                  <small id="tanggal-info" class="form-text text-muted"></small>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group mb-2">
                  <label for="jam" class="form-label label-required">Jam Kunjungan</label>
                  <input type="time" id="jam" name="jam" class="form-control" step="300" required
                  value="<?= preg_replace('/^(\d{2}):(\d{2}).*$/','$1:$2',$jamHHMM) ?>">
                  <small id="jam-info" class="form-text text-danger"></small>

                </div>
              </div>
            </div>

            <div class="divider-soft"></div>

            <!-- ====== Keperluan & Lampiran (cantik + preview, TANPA thumbnail di form) ====== -->
            <div class="header-title">Keperluan & Lampiran</div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group mb-2">
                  <label for="keperluan" class="form-label">Keperluan Kunjungan</label>
                  <textarea id="keperluan" name="keperluan" class="form-control" rows="4" placeholder="Tuliskan keperluan kunjungan"><?= htmlspecialchars((string)($B->keperluan ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
              </div>

              <div class="col-md-6">
                <!-- SURAT TUGAS -->
                <div class="upload-card mb-3">
                  <div class="upload-head">
                    <h6 class="upload-title"><i class="mdi mdi-file-document-outline mr-1"></i> Surat Tugas (Opsional)</h6>
                    <!-- <?php if ($stFile): ?>
                      <span class="file-badge" title="File saat ini"><i class="mdi mdi-file-eye-outline"></i><span><?= htmlspecialchars($stFile,ENT_QUOTES,'UTF-8') ?></span></span>
                    <?php endif; ?> -->
                  </div>

                  <?php if ($stFile):
                    $stUrl = base_url('uploads/surat_tugas/'.rawurlencode($stFile));
                  ?>
                    <div class="mb-2">
                      <div class="upload-actions">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalPreviewSurat" data-existing="1">
                          <i class="mdi mdi-eye"></i> Pratinjau
                        </button>
                        <!-- <a href="<?= $stUrl ?>" target="_blank" rel="noopener" class="btn btn-soft btn-sm">
                          <i class="mdi mdi-open-in-new"></i> Buka
                        </a> -->
                        <a href="<?= $stUrl ?>" download class="btn btn-outline-secondary btn-sm">
                          <i class="mdi mdi-download"></i> Unduh
                        </a>
                      </div>
                    </div>
                  <?php endif; ?>

                  <div class="custom-file mb-2">
                    <input type="file" class="custom-file-input" name="surat_tugas" id="surat_tugas" accept=".pdf,.jpg,.jpeg,.png">
                    <label class="custom-file-label" for="surat_tugas">Pilih PDF/JPG/PNG (≤ 2 MB)</label>
                  </div>

                  <div class="upload-actions">
                    <button type="button" id="btnPrevSurat" class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalPreviewSurat" disabled>
                      <i class="mdi mdi-eye-outline"></i> Pratinjau file baru
                    </button>
                    <button type="button" id="btnResetSurat" class="btn btn-light btn-sm" disabled>
                      <i class="mdi mdi-close-circle-outline"></i> Reset
                    </button>
                  </div>

                  <div class="hint mt-2">Format: PDF/JPG/PNG. Ukuran maksimal 2 MB.</div>
                </div>

                <!-- FOTO -->
                <div class="upload-card">
                  <div class="upload-head">
                    <h6 class="upload-title"><i class="mdi mdi-image-outline mr-1"></i> Foto (Opsional)</h6>
                   <!--  <?php if ($fotoFile): ?>
                      <span class="file-badge" title="File saat ini"><i class="mdi mdi-image"></i><span><?= htmlspecialchars($fotoFile,ENT_QUOTES,'UTF-8') ?></span></span>
                    <?php endif; ?> -->
                  </div>

                  <?php if ($fotoFile):
                    $fUrl = base_url('uploads/foto/'.rawurlencode($fotoFile));
                  ?>
                    <div class="mb-2">
                      <div class="upload-actions">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalPreviewFoto" data-existing="1">
                          <i class="mdi mdi-eye"></i> Pratinjau
                        </button>
                       <!--  <a href="<?= $fUrl ?>" target="_blank" rel="noopener" class="btn btn-soft btn-sm">
                          <i class="mdi mdi-open-in-new"></i> Buka
                        </a> -->
                        <a href="<?= $fUrl ?>" download class="btn btn-outline-secondary btn-sm">
                          <i class="mdi mdi-download"></i> Unduh
                        </a>
                      </div>
                    </div>
                  <?php endif; ?>

                  <div class="custom-file mb-2">
                    <input type="file" class="custom-file-input" name="foto" id="foto" accept="image/jpeg,image/png">
                    <label class="custom-file-label" for="foto">Pilih JPG/PNG (≤ 1.5 MB)</label>
                  </div>

                  <!-- Tidak ada thumbnail di form -->
                  <div class="upload-actions">
                    <button type="button" id="btnPrevFoto" class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalPreviewFoto" disabled>
                      <i class="mdi mdi-eye-outline"></i> Pratinjau foto baru
                    </button>
                    <button type="button" id="btnResetFoto" class="btn btn-light btn-sm" disabled>
                      <i class="mdi mdi-close-circle-outline"></i> Reset
                    </button>
                  </div>

                  <div class="hint mt-2">Format: JPG/PNG. Ukuran maksimal 1.5 MB. Foto dokumentasi dapat dilakukan saat checkin</div>
                </div>
              </div>
            </div>

            <!-- Modals Preview -->
            <div class="modal fade" id="modalPreviewSurat" tabindex="-1" role="dialog" aria-hidden="true">
              <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                <div class="modal-content">
                  <div class="modal-header py-2">
                    <h6 class="modal-title"><i class="mdi mdi-file-eye-outline"></i> Pratinjau Surat Tugas</h6>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                  </div>
                  <div class="modal-body p-0" id="previewSuratBody">
                    <div class="p-4 text-muted">Tidak ada file untuk dipratinjau.</div>
                  </div>
                  <div class="modal-footer py-2">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal fade" id="modalPreviewFoto" tabindex="-1" role="dialog" aria-hidden="true">
              <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                  <div class="modal-header py-2">
                    <h6 class="modal-title"><i class="mdi mdi-eye-outline"></i> Pratinjau Foto</h6>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                  </div>
                  <div class="modal-body text-center" id="previewFotoBody">
                    <div class="p-4 text-muted">Tidak ada foto untuk dipratinjau.</div>
                  </div>
                  <div class="modal-footer py-2">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
                  </div>
                </div>
              </div>
            </div>
            <!-- /Modals -->

            <div class="text-center mt-3">
              <a class="btn btn-gray px-4 mr-2" href="<?= site_url('booking/booked?t='.urlencode($token)) ?>">Batal</a>
              <button type="button" class="btn btn-blue px-4" id="btnUpdate" onclick="simpan_ubah()">Simpan Perubahan</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Dependensi -->
<script src="<?= base_url('assets/admin/js/vendor.min.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/app.min.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/sw.min.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/jquery.easyui.min.js') ?>"></script>
<?php $this->load->view("front_end/footer.php"); ?>

<script>
/* =========================
   CONFIG JAM OPERASIONAL
   ========================= */
window.OP_HOURS = <?= json_encode([
  'tz'    => $rec->waktu ?? 'Asia/Makassar',
  'lead'  => (int)($rec->min_lead_minutes ?? 10),
  'days'  => [
    '0' => ['open'=>$rec->op_sun_open ?? null, 'break_start'=>$rec->op_sun_break_start ?? null, 'break_end'=>$rec->op_sun_break_end ?? null, 'close'=>$rec->op_sun_close ?? null, 'closed'=>(int)($rec->op_sun_closed ?? 1)],
    '1' => ['open'=>$rec->op_mon_open ?? '08:00','break_start'=>$rec->op_mon_break_start ?? null,'break_end'=>$rec->op_mon_break_end ?? null,'close'=>$rec->op_mon_close ?? '15:00','closed'=>(int)($rec->op_mon_closed ?? 0)],
    '2' => ['open'=>$rec->op_tue_open ?? '08:00','break_start'=>$rec->op_tue_break_start ?? null,'break_end'=>$rec->op_tue_break_end ?? null,'close'=>$rec->op_tue_close ?? '15:00','closed'=>(int)($rec->op_tue_closed ?? 0)],
    '3' => ['open'=>$rec->op_wed_open ?? '08:00','break_start'=>$rec->op_wed_break_start ?? null,'break_end'=>$rec->op_wed_break_end ?? null,'close'=>$rec->op_wed_close ?? '15:00','closed'=>(int)($rec->op_wed_closed ?? 0)],
    '4' => ['open'=>$rec->op_thu_open ?? '08:00','break_start'=>$rec->op_thu_break_start ?? null,'break_end'=>$rec->op_thu_break_end ?? null,'close'=>$rec->op_thu_close ?? '15:00','closed'=>(int)($rec->op_thu_closed ?? 0)],
    '5' => ['open'=>$rec->op_fri_open ?? '08:00','break_start'=>$rec->op_fri_break_start ?? null,'break_end'=>$rec->op_fri_break_end ?? null,'close'=>$rec->op_fri_close ?? '14:00','closed'=>(int)($rec->op_fri_closed ?? 0)],
    '6' => ['open'=>$rec->op_sat_open ?? '08:00','break_start'=>$rec->op_sat_break_start ?? null,'break_end'=>$rec->op_sat_break_end ?? null,'close'=>$rec->op_sat_close ?? '11:30','closed'=>(int)($rec->op_sat_closed ?? 0)],
  ]
], JSON_UNESCAPED_SLASHES) ?>;

/* =========================
   UTIL + LOADER
   ========================= */
const MARGIN_CLASS = 'ml-2';
function loader(){
  Swal.fire({ title:"Proses...", html:"Jangan tutup halaman ini", allowOutsideClick:false, didOpen:() => Swal.showLoading() });
}
function setLoading(isLoading, btn, opts){
  btn  = btn  || document.getElementById('btnUpdate');
  opts = opts || {};
  const steps = opts.steps || ['Memvalidasi data…','Cek hari & jam…','Cek kuota pendamping…','Cek slot jadwal…','Menyimpan…','Kirim Notifikasi…'];
  const intervalMs = opts.interval || 900;
  if (isLoading){
    if (btn.dataset.loadingActive === '1') return;
    btn.dataset.originalHtml = btn.innerHTML;
    btn.disabled = true; btn.dataset.loadingActive = '1';
    let i = 0;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>' + `<span class="${MARGIN_CLASS}">${steps[i]}</span>`;
    const id = setInterval(() => {
      i = (i+1) % steps.length;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>' + `<span class="${MARGIN_CLASS}">${steps[i]}</span>`;
    }, intervalMs);
    btn.dataset.loadingInterval = id;
  } else {
    if (btn.dataset.loadingInterval) { clearInterval(+btn.dataset.loadingInterval); delete btn.dataset.loadingInterval; }
    btn.disabled = false;
    btn.innerHTML = btn.dataset.originalHtml || 'Simpan Perubahan';
    delete btn.dataset.loadingActive;
  }
}
function withIdLocale(opts){
  try { if (window.flatpickr?.l10ns?.id) opts.locale = flatpickr.l10ns.id; } catch(e){}
  return opts;
}
</script>

<script>
/* =========================
   TANGGAL KUNJUNGAN + JAM (prefill)
   ========================= */
(function(){
  const elView  = document.getElementById('tanggal_view');
  const elISO   = document.getElementById('tanggal');
  const jamInput = document.getElementById('jam');
  const infoJam  = document.getElementById('jam-info');
  const infoTgl  = document.getElementById('tanggal-info');

  const HARI_ID = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  const TZ_ABBR = {'Asia/Jakarta':'WIB','Asia/Makassar':'WITA','Asia/Jayapura':'WIT'};
  const tzAbbr  = TZ_ABBR[(window.OP_HOURS && OP_HOURS.tz) || 'Asia/Makassar'] || '';

  const pad = n => (n<10?'0':'')+n;
  const toMin = s => !s ? null : ((h=s.split(':')[0]|0, m=s.split(':')[1]|0), h*60+m);
  const fromMin = n => `${pad(Math.floor(n/60))}:${pad(n%60)}`;
  const dot = s => s ? s.replace(':','.') : '';
  function todayYmd(){ const d = new Date(); return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`; }
  function buildInfoLine(dayIdx, conf, pushedMin=null){
    if (!conf || conf.closed) return `Hari ${HARI_ID[dayIdx]}: Libur`;
    let line = `Hari ${HARI_ID[dayIdx]}: ${dot(conf.open)}–${dot(conf.close)} ${tzAbbr}`;
    if (conf.break_start && conf.break_end) line += ` (Istirahat ${dot(conf.break_start)}–${dot(conf.break_end)} ${tzAbbr})`;
    if (pushedMin && toMin(pushedMin) > toMin(conf.open||'00:00')) line += ` • Minimal hari ini: ${dot(pushedMin)}`;
    return line;
  }
  function sameYmd(a,b){ return a&&b && a.getFullYear()===b.getFullYear() && a.getMonth()===b.getMonth() && a.getDate()===b.getDate(); }

  function applyForDate(d){
    if (!(d instanceof Date) || isNaN(d)) return;
    const dayIdx = d.getDay();
    const conf   = OP_HOURS?.days?.[String(dayIdx)];
    if (!conf || conf.closed){
      infoTgl && (infoTgl.textContent = `Hari ${HARI_ID[dayIdx]} libur, silakan pilih hari lain.`);
      return;
    }
    let minStr = conf.open || '00:00';
    let maxStr = conf.close || '23:59';

    const now = new Date();
    if (sameYmd(d, now)){
      const lead = Math.max(0, Math.min(1440, +(OP_HOURS?.lead ?? 0)));
      let earliest = now.getHours()*60 + now.getMinutes() + lead;
      const bs = toMin(conf.break_start), be = toMin(conf.break_end);
      if (bs!==null && be!==null && bs<be && earliest>=bs && earliest<be) earliest = be;
      const openMin = toMin(minStr) ?? 0;
      minStr = fromMin(Math.max(openMin, earliest));
    }

    jamInput.min = minStr; jamInput.max = maxStr;
    infoJam && (infoJam.textContent = buildInfoLine(dayIdx, conf, minStr));
  }

  const minToday = todayYmd();
  if (window.flatpickr && elView) {
    flatpickr(elView, withIdLocale({
      dateFormat: 'd/m/Y',
      allowInput: false,
      clickOpens: true,
      disableMobile: true,
      minDate: minToday,
      defaultDate: elISO.value || null,
      onChange(selectedDates, _, inst){
        const d = selectedDates && selectedDates[0] ? selectedDates[0] : null;
        elISO.value = d ? inst.formatDate(d,'Y-m-d') : '';
        if (d) applyForDate(d);
      },
      onReady(_, __, inst){
        if (elISO.value) {
          const parsed = inst.parseDate(elISO.value, 'Y-m-d');
          if (parsed) { elView.value = inst.formatDate(parsed, 'd/m/Y'); applyForDate(parsed); }
        }
      }
    }));
  } else if (elView) {
    elView.type = 'date'; elView.setAttribute('min', minToday);
    if (elISO.value) elView.value = elISO.value;
  }
})();
</script>
<script>
(function(){
  const jamInput = document.getElementById('jam');
  if(!jamInput) return;

  // Ubah "HH:MM:SS" -> "HH:MM"
  function toHHMM(s){
    const m = String(s||'').match(/^(\d{1,2}):(\d{2})/);
    return m ? (m[1].padStart(2,'0') + ':' + m[2]) : '';
  }

  // Prefill: rapikan kalau datangnya "HH:MM:SS"
  jamInput.value = toHHMM(jamInput.value);

  // Saat user mengubah → pastikan tetap HH:MM
  jamInput.addEventListener('input', function(e){
    // beberapa browser ngetik bisa sisipin detik; normalisasi halus
    const v = toHHMM(e.target.value);
    if (v && e.target.value !== v) e.target.value = v;
  });

  // Opsional: enforce pattern supaya FE invalid kalau ada detik
  jamInput.setAttribute('pattern','^([01]\\d|2[0-3]):[0-5]\\d$');

  // Hook submit: sebelum kirim FormData, pastikan HH:MM
  const _oldSimpan = window.simpan_ubah;
  window.simpan_ubah = function(btn){
    if (jamInput) jamInput.value = toHHMM(jamInput.value);
    return _oldSimpan ? _oldSimpan(btn) : undefined;
  };
})();
</script>

<script>
/* =========================
   TANGGAL LAHIR (flatpickr)
   ========================= */
(function(){
  const view = document.getElementById('tanggal_lahir_view');
  const iso  = document.getElementById('tanggal_lahir');
  if (!view || !iso) return;

  const pad = n => (n<10?'0':'')+n;
  function todayYmd(){ const d = new Date(); return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`; }

  if (window.flatpickr) {
    flatpickr(view, withIdLocale({
      dateFormat: 'd/m/Y',
      allowInput: false,
      clickOpens: true,
      disableMobile: true,
      maxDate: todayYmd(),
      defaultDate: iso.value || null,
      onChange(selectedDates, _, inst){
        const d = selectedDates && selectedDates[0] ? selectedDates[0] : null;
        iso.value = d ? inst.formatDate(d,'Y-m-d') : '';
      },
      onReady(_, __, inst){
        if (iso.value) {
          const parsed = inst.parseDate(iso.value, 'Y-m-d');
          if (parsed) view.value = inst.formatDate(parsed, 'd/m/Y');
        }
      }
    }));
  } else {
    view.type = 'date'; view.setAttribute('max', todayYmd());
    view.value = iso.value || '';
    view.addEventListener('change', function(){ iso.value = this.value || ''; });
  }
})();
</script>

<script>
/* =========================
   KATEGORI → INSTANSI (AJAX, preselect)
   ========================= */
$(function(){
  const URL_OPTIONS = '<?= site_url("booking/options_by_kategori"); ?>';
  const initKat  = <?= json_encode($kat) ?>;
  const initId   = <?= (int)$instId ?>;
  const initNama = <?= json_encode($instNm) ?>;

  function setInstansiLoading(){
    $('#instansi').prop('disabled', true).html('<option value="">Memuat data...</option>');
  }
  function resetInstansi(){
    $('#instansi').prop('disabled', true).html('<option value="">-- Pilih Instansi --</option>');
  }
  function toggleManual(isManual){
    $('#instansi_select_wrap').toggleClass('d-none', isManual);
    $('#instansi_manual_wrap').toggleClass('d-none', !isManual);
    $('#instansi').prop('required', !isManual);
    $('#instansi_manual').prop('required', isManual);
    if (isManual){ $('#instansi').val(''); } else { $('#instansi_manual').val(''); }
  }

  function loadOptionsAndSelect(jenis, selectedId){
    if(!jenis){ resetInstansi(); return; }
    setInstansiLoading();
    $.getJSON(URL_OPTIONS, { jenis })
    .done(function(resp){
      const list = Array.isArray(resp) ? resp : (resp.results || []);
      const $i = $('#instansi'); $i.empty().append('<option value="">-- Pilih Instansi --</option>');
      list.forEach(r => $i.append('<option value="'+r.id+'">'+r.text+'</option>'));
      if (selectedId) $i.val(String(selectedId));
      $i.prop('disabled', list.length === 0);
    })
    .fail(function(){ resetInstansi(); alert('Gagal memuat data instansi.'); });
  }

  // On change
  $('#kategori').on('change', function(){
    const jenis = this.value;
    if (jenis === 'lainnya'){
      toggleManual(true);
      $('#instansi_manual').val(initNama || '');
    } else {
      toggleManual(false);
      loadOptionsAndSelect(jenis, null);
    }
  });

  // Init from existing
  if (initKat === 'lainnya'){
    toggleManual(true);
    $('#instansi_manual').val(initNama || '');
  } else if (initKat) {
    toggleManual(false);
    loadOptionsAndSelect(initKat, initId || null);
  }
});
</script>

<script>
/* =========================
   ID Number sanitization + pesan custom
   ========================= */
(function(){
  const input = document.getElementById('id_number');
  const rx = /^(?:\d{8,9}|\d{16}|\d{18})$/;
  function sanitize(){ const d=(input.value||'').replace(/\D/g,'').slice(0,18); if(d!==input.value) input.value=d; }
  function setValidity(){
    if(!input.value){ input.setCustomValidity('Wajib diisi'); return; }
    input.setCustomValidity(rx.test(input.value)?'':'Format tidak valid. Isi salah satu: NIK 16 digit, NIP 18/9 digit, atau NRP 8–9 digit.');
  }
  input.addEventListener('input', ()=>{ sanitize(); setValidity(); });
  input.addEventListener('invalid', setValidity);
  sanitize(); setValidity();
})();
</script>

<script>
/* =========================
   PENDAMPING
   ========================= */
(function(){
  window.pendamping = Array.isArray(window.pendamping) ? window.pendamping : [];
  let pendamping = window.pendamping;
  let editIndex  = -1;
  window.PD_MAX_LIMIT = null;

  const pdTableWrap = document.getElementById('pdTableWrap');
  const tblBody  = document.querySelector('#tblPendampingLocal tbody');
  const hidJson  = document.getElementById('pendamping_json');
  const hidJumlah= document.getElementById('jumlah_pendamping');
  const pdCountWrap = document.getElementById('pdCountBadgeWrap');
  const pdFilledBadge = document.getElementById('pdFilledBadge');
  const pdInfoText = document.getElementById('pdInfoText');
  const pdLimitInfo = document.getElementById('pdLimitInfo');

  const $modal = $('#modalPendamping');
  const elNik  = document.getElementById('pd_nik');
  const elNama = document.getElementById('pd_nama');
  const btnSubmit = document.getElementById('btnPdSubmit');
  const lblModal  = document.getElementById('modalPendampingLabel');
  const pdWarn    = document.getElementById('pdWarn');
  const inNikPemohon = document.getElementById('id_number');

  const onlyDigits = s => String(s||'').replace(/\D+/g,'');
  const esc = s => String(s||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]));
  const rxId = /^(?:\d{8,9}|\d{16}|\d{18})$/;
  const isValidId = s => rxId.test(String(s||''));

  function openModal(mode='add', idx=-1){
    editIndex = (mode==='edit') ? idx : -1;
    lblModal.textContent = (mode==='edit') ? 'Edit Pendamping' : 'Tambah Pendamping';
    btnSubmit.textContent = 'Simpan';
    pdWarn.classList.add('d-none'); pdWarn.textContent = '';

    if (mode==='edit' && pendamping[idx]){
      elNik.value  = pendamping[idx].nik;
      elNama.value = pendamping[idx].nama;
    } else {
      elNik.value = ''; elNama.value = '';
    }

    setTimeout(()=> elNik.focus(), 80);
    $modal.modal('show');
  }
  function showWarn(msg){ pdWarn.textContent = msg; pdWarn.classList.remove('d-none'); }

  function submitModal(){
    const nik  = onlyDigits(elNik.value);
    const nama = String(elNama.value||'').trim();
    const nikPemohon = onlyDigits(inNikPemohon?.value || '');

    pdWarn.classList.add('d-none'); pdWarn.textContent = '';
    if (!isValidId(nik))  return showWarn('ID tidak valid. Gunakan NIK 16 / NIP 18 atau 9 / NRP 8–9 digit.');
    if (!nama)            return showWarn('Nama pendamping wajib diisi.');
    if (nikPemohon && nikPemohon === nik) return showWarn('ID pendamping tidak boleh sama dengan ID tamu.');

    const exists = pendamping.findIndex((p, i) => p.nik === nik && i !== editIndex);
    if (exists !== -1) return showWarn('ID pendamping sudah ada di daftar.');

    if (editIndex === -1 && window.PD_MAX_LIMIT !== null && pendamping.length >= window.PD_MAX_LIMIT){
      return showWarn('Jumlah pendamping sudah mencapai batas maksimum unit ini.');
    }

    if (editIndex === -1) pendamping.push({nik, nama});
    else pendamping[editIndex] = {nik, nama};

    render();
    $modal.modal('hide');
  }

  function render(){
    hidJson.value   = JSON.stringify(pendamping);
    hidJumlah.value = String(pendamping.length);

    if (pendamping.length > 0){
      pdCountWrap.classList.remove('d-none');
      pdFilledBadge.textContent = pendamping.length;
    } else {
      pdCountWrap.classList.add('d-none');
    }

    if (pendamping.length === 0){
      pdTableWrap.classList.add('d-none');
      tblBody.innerHTML = '';
      pdInfoText.textContent = 'Jika tidak membawa pendamping, lewati bagian ini.';
      return;
    } else {
      pdTableWrap.classList.remove('d-none');
      pdInfoText.textContent = 'Gunakan tombol Edit/Hapus untuk mengubah daftar.';
    }

    tblBody.innerHTML = pendamping.map((p,i)=>`
      <tr>
        <td class="text-center">${i+1}</td>
        <td><code>${esc(p.nik)}</code></td>
        <td>${esc(p.nama)}</td>
        <td class="text-center" style="white-space:nowrap; gap:.25rem;">
          <button type="button" class="btn btn-sm btn-warning btn-action" onclick="pdEdit(${i})" title="Edit">
            <i class="fas fa-edit"></i>
          </button>
          <button type="button" class="btn btn-sm btn-danger btn-action" onclick="pdDel(${i})" title="Hapus">
            <i class="fas fa-trash-alt"></i>
          </button>
        </td>
      </tr>
    `).join('');
  }

  window.pdEdit = function(i){ if (i<0 || i>=pendamping.length) return; openModal('edit', i); };
  window.pdDel = async function(i){
    if (i < 0 || i >= pendamping.length) return;
    const nama = (pendamping[i] && (pendamping[i].nama || ''));
    let ok = true;
    if (typeof Swal !== 'undefined') {
      const r = await Swal.fire({
        title: 'Hapus pendamping?', text: nama ? `Nama: ${nama}` : 'Data ini akan dihapus.',
        icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal',
        reverseButtons: true, focusCancel: true
      });
      ok = r.isConfirmed;
    } else {
      ok = confirm(`Hapus pendamping ${nama ? '('+nama+')' : ''}?`);
    }
    if (!ok) return;
    pendamping.splice(i, 1);
    render();
  };

  document.getElementById('pd_nik').addEventListener('input', function(){
    this.value = (this.value||'').replace(/\D+/g,'').slice(0,18);
  });
  document.getElementById('pd_nama').addEventListener('keydown', (e)=>{ if(e.key==='Enter'){ e.preventDefault(); submitModal(); }});
  document.getElementById('btnPdSubmit').addEventListener('click', submitModal);

  document.getElementById('btnOpenPendamping').addEventListener('click', ()=>{
    if (window.PD_MAX_LIMIT !== null && pendamping.length >= window.PD_MAX_LIMIT){
      Swal.fire({title:'Batas tercapai', text:'Jumlah pendamping sudah mencapai batas maksimum unit ini.', icon:'info'});
      return;
    }
    openModal('add', -1);
  });

  // Limit pendamping per-unit
  $('#unit_tujuan').on('change', function(){
    const unitId = $(this).val();
    if(!unitId){ window.PD_MAX_LIMIT = null; refreshLimitInfo(); return; }
    $.getJSON('<?= site_url("booking/get_limit_pendamping"); ?>', { id: unitId }, function(res){
      window.PD_MAX_LIMIT = (typeof res.max === 'number') ? res.max : null;
      refreshLimitInfo();
      if (window.PD_MAX_LIMIT !== null && pendamping.length > window.PD_MAX_LIMIT){
        Swal.fire({title:'Melebihi Batas', text:`Jumlah pendamping (${pendamping.length}) melebihi batas (${window.PD_MAX_LIMIT}). Hapus sebagian.`, icon:'warning'});
      }
    }).fail(function(){ window.PD_MAX_LIMIT = null; refreshLimitInfo(); });
  });
  function refreshLimitInfo(){
    const txt = (window.PD_MAX_LIMIT === null) ? 'Batas pendamping: tidak dibatasi.' : `Batas pendamping: maks. ${window.PD_MAX_LIMIT} orang.`;
    if (pdLimitInfo) pdLimitInfo.textContent = txt;
  }

  // ==== Load awal dari server (API: /booking/pendamping_list/{kode}) ====
  const kode = <?= json_encode($kode) ?>;
  if (kode) {
    $.getJSON('<?= site_url("booking/pendamping_list/"); ?>'+encodeURIComponent(kode))
      .done(function(resp){
        const arr = (resp && resp.data) ? resp.data : [];
        if (Array.isArray(arr)) {
          pendamping = arr.map(r => ({ nik: String(r.nik||'').replace(/\D+/g,''), nama: String(r.nama||'').trim() }))
                          .filter(x => x.nik && x.nama);
          window.pendamping = pendamping;
          refreshLimitInfo();
          render();
        }
      });
  }

  refreshLimitInfo();
  render();

  window._ensurePendampingBeforeSubmit = function(){
    hidJson.value   = JSON.stringify(pendamping);
    hidJumlah.value = String(pendamping.length);
    if (window.PD_MAX_LIMIT !== null && pendamping.length > window.PD_MAX_LIMIT) {
      throw new Error(`Jumlah pendamping melebihi batas (${pendamping.length}/${window.PD_MAX_LIMIT}).`);
    }
    if (pendamping.some(p => !isValidId(p.nik) || !p.nama?.trim())) {
      throw new Error('Lengkapi ID (NIK 16 / NIP 18 atau 9 / NRP 8–9 digit) dan Nama semua pendamping.');
    }
  };
})();
</script>

<script>
/* =========================
   SUBMIT / UPDATE
   ========================= */
(function(){
  const form = document.getElementById('form_edit');
  if (!form) return;

  const getLabel = (el) => {
    if (el.id){
      const lbl = form.querySelector(`label[for="${el.id}"]`);
      if (lbl) return lbl.textContent.replace('*','').trim();
    }
    return (el.getAttribute('placeholder') || el.name || 'Field').trim();
  };
  const idMessage = (el) => {
    const v = el.validity;
    if (v.valueMissing)   return 'Wajib diisi';
    if (v.typeMismatch)   return (el.type==='email') ? 'Format email tidak valid'
                           : (el.type==='url') ? 'URL tidak valid'
                           : 'Format tidak valid';
    if (v.patternMismatch)return el.title || 'Format tidak sesuai';
    if (v.tooShort)       return `Minimal ${el.minLength} karakter`;
    if (v.tooLong)        return `Maksimal ${el.maxLength} karakter`;
    if (v.rangeUnderflow) return `Minimal ${el.min}`;
    if (v.rangeOverflow)  return `Maksimal ${el.max}`;
    if (v.stepMismatch)   return 'Nilai tidak sesuai kelipatan';
    if (v.badInput)       return 'Nilai tidak valid';
    if (v.customError)    return el.validationMessage || 'Tidak valid';
    return 'Tidak valid';
  };
  const wire = (el) => {
    const clear = () => el.setCustomValidity('');
    const set   = () => { if (!el.validity.valid) el.setCustomValidity(idMessage(el)); };
    el.addEventListener('invalid', set);
    el.addEventListener('input',  clear);
    el.addEventListener('change', clear);
  };
  form.querySelectorAll('input,select,textarea').forEach(wire);

  window.buildInvalidListHTML = function(form){
    const invalids = Array.from(form.querySelectorAll(':invalid'));
    return '<ul style="text-align:left;margin:0;padding-left:1.1rem;">'
      + invalids.slice(0, 8).map(el => `<li>${getLabel(el)}: ${idMessage(el)}</li>`).join('')
      + (invalids.length>8 ? `<li>dst… (${invalids.length} field)</li>` : '')
      + '</ul>';
  };
})();

function simpan_ubah(btn){
  btn = btn || document.getElementById('btnUpdate');
  const url  = "<?= site_url(strtolower($controller).'/update')?>/";
  const form = document.getElementById('form_edit');

  if (!form.checkValidity()){
    Swal.fire({ title: 'Lengkapi data dulu', html: window.buildInvalidListHTML(form), icon: 'warning' });
    return;
  }

  Swal.fire({
    title: 'Simpan Perubahan?',
    html: `Perubahan akan memperbarui data booking yang ada.`,
    icon: "question",
    showCancelButton: true,
    allowOutsideClick: false,
    reverseButtons: true,
    buttonsStyling: true,
    customClass: { confirmButton: "btn btn-primary", cancelButton: "btn btn-warning" },
    confirmButtonText: "Ya, simpan",
    cancelButtonText:  "Batal",
  }).then((res) => {
    if (!res.isConfirmed) return;

    const ymd = document.getElementById('tanggal')?.value || '';
    const jam = document.getElementById('jam')?.value || '';
    if (!ymd) { Swal.fire({title:'Tanggal belum valid', text:'Silakan pilih tanggal dari datepicker.', icon:'warning'}); return; }
    if (!jam) { Swal.fire({title:'Jam belum dipilih', text:'Silakan pilih jam kunjungan.', icon:'warning'}); return; }

    try {
      if (typeof window._ensurePendampingBeforeSubmit === 'function') window._ensurePendampingBeforeSubmit();
    } catch (e) {
      Swal.fire({ title:'Validasi Gagal', text: e.message || 'Cek kembali data pendamping.', icon:'error' });
      return;
    }

    const formData = new FormData(form);
    setLoading(true, btn, { interval: 900 });
    const loader = loaderSteps({
        title: 'Memproses permohonan…',
        run: [
      async () => { await new Promise(r=>setTimeout(r,500)); }, // Memvalidasi data…
      async () => { await new Promise(r=>setTimeout(r,600)); }, // Cek hari & jam…
      async ({set}) => { set('Cek kuota pendamping… (2/5)'); await new Promise(r=>setTimeout(r,500)); },
      async () => { await new Promise(r=>setTimeout(r,600)); }, // Cek slot jadwal…
      async () => { await new Promise(r=>setTimeout(r,800)); }, // Menyimpan…
      async () => { await new Promise(r=>setTimeout(r,700)); }, // Kirim WhatsApp…
      ]
    });

    $.ajax({
      url, type: "POST", data: formData,
      processData: false, contentType: false, dataType: "json"
    })
    .done(function(obj){
      if(!obj || obj.success === false){
        Swal.fire({
          title: obj?.title || "Gagal Menyimpan",
          icon: "error",
          html: obj?.pesan || "Terjadi kesalahan.",
          allowOutsideClick: false,
          buttonsStyling: false,
          customClass: { confirmButton: "btn btn-danger" },
          confirmButtonText: "OK"
        });
        return;
      }

      if (obj.redirect_url) { window.location.assign(obj.redirect_url); return; }
      const to = "<?= site_url('booking/booked?t='.urlencode($token)) ?>";
      Swal.fire({ title: obj.title || "Tersimpan", text: "Perubahan telah disimpan.", icon: "success" })
          .then(()=> window.location.assign(to));
    })
    .fail(function(xhr, status, error){
      Swal.fire({
        title: "Error",
        text: "Terjadi kesalahan pada server: " + (error || status),
        icon: "error",
        buttonsStyling: false,
        customClass: { confirmButton: "btn btn-danger" },
        confirmButtonText: "OK"
      });
    })
    .always(function(){ setLoading(false, btn); });
  });
}
</script>

<script>
 $(function () {
  var $mp = $('#modalPendamping');

  // Pastikan modal dipindah ke <body>
  $mp.appendTo('body');

  // Saat modal tampil → matikan efek blur global + styling backdrop
  $mp.on('shown.bs.modal', function () {
    $('body').addClass('noblur-backdrop');
    $('.modal-backdrop').last()
      .addClass('pendamping-backdrop')        // class khususmu
      .css({
        'backdrop-filter': 'none',
        '-webkit-backdrop-filter': 'none',
        'filter': 'none',
        'background': 'rgba(0,0,0,.55)',
        'z-index': 2050,
        'position': 'fixed'
      });
  });

  // Saat modal ditutup → kembalikan normal
  $mp.on('hidden.bs.modal', function () {
    $('body').removeClass('noblur-backdrop');
    $('.modal-backdrop.pendamping-backdrop')
      .removeClass('pendamping-backdrop')
      .attr('style',''); // reset inline style
  });
});

</script>

<!-- ====== Preview SURAT & FOTO (tanpa thumbnail di form) + anti-blur ====== -->
<script>
(function(){
  // Pastikan modal preview berada di <body> dan anti-blur aktif saat tampil
  var $surat = $('#modalPreviewSurat');
  var $foto  = $('#modalPreviewFoto');
  $surat.add($foto).appendTo('body');

  function onShow(){ document.body.classList.add('noblur-backdrop'); }
  function onHide(){ document.body.classList.remove('noblur-backdrop'); }
  $surat.on('show.bs.modal', onShow).on('hidden.bs.modal', onHide);
  $foto .on('show.bs.modal', onShow).on('hidden.bs.modal', onHide);

  // ====== SURAT ======
  var suratInput    = document.getElementById('surat_tugas');
  var lblSurat      = document.querySelector('label[for="surat_tugas"].custom-file-label');
  var btnPrevSurat  = document.getElementById('btnPrevSurat');
  var btnResetSurat = document.getElementById('btnResetSurat');
  var suratObjURL   = null;

  function bytesHuman(n){ return n>=1024*1024 ? (n/1024/1024).toFixed(2)+' MB' : (n/1024).toFixed(0)+' KB'; }
  function setLabel(el,labelEl){ if(labelEl){ labelEl.textContent = (el.files && el.files[0]) ? el.files[0].name : 'Pilih file'; } }
  function revoke(url){ try{ url && URL.revokeObjectURL(url); }catch(e){} }

  if (suratInput){
    suratInput.addEventListener('change', function(){
      revoke(suratObjURL); suratObjURL=null;
      setLabel(suratInput, lblSurat);
      btnPrevSurat.disabled = true;
      btnResetSurat.disabled= true;

      var f = this.files && this.files[0];
      if(!f) return;

      var okType = /^(application\/pdf|image\/jpeg|image\/png)$/i.test(f.type);
      var okSize = f.size <= 2*1024*1024;
      if(!okType){ Swal.fire('Format tidak didukung','Pilih PDF/JPG/PNG.','warning'); this.value=''; setLabel(this,lblSurat); return; }
      if(!okSize){ Swal.fire('Ukuran terlalu besar','Maksimal 2 MB. Ukuran sekarang: '+bytesHuman(f.size),'warning'); this.value=''; setLabel(this,lblSurat); return; }

      suratObjURL = URL.createObjectURL(f);
      btnPrevSurat.disabled = false;
      btnResetSurat.disabled= false;
    });

    btnResetSurat && btnResetSurat.addEventListener('click', function(){
      revoke(suratObjURL); suratObjURL=null; suratInput.value=''; setLabel(suratInput,lblSurat);
      btnPrevSurat.disabled=true; btnResetSurat.disabled=true;
    });

    var EXIST_SURAT_URL = <?= json_encode($stFile ? base_url('uploads/surat_tugas/'.rawurlencode($stFile)) : '') ?>;

    $('#modalPreviewSurat').on('show.bs.modal', function(e){
      var body = document.getElementById('previewSuratBody');
      if(!body) return;
      body.innerHTML = '<div class="p-4 text-muted">Tidak ada file untuk dipratinjau.</div>';

      var fromExisting = e.relatedTarget && e.relatedTarget.getAttribute('data-existing') === '1';
      var url = suratObjURL || (fromExisting ? EXIST_SURAT_URL : '');
      if(!url) return;

      if (url && url.match(/\.pdf($|\?)/i) || (suratInput.files[0]?.type === 'application/pdf')) {
        body.innerHTML =
          '<div class="embed-responsive embed-responsive-16by9">'+
          '<iframe class="embed-responsive-item" src="'+url+'#toolbar=1&navpanes=0&scrollbar=1" style="border:0" allowfullscreen></iframe>'+
          '</div>';
      } else {
        body.innerHTML = '<div class="text-center p-2"><img src="'+url+'" style="max-height:80vh;max-width:100%" class="img-fluid" alt="Preview Surat"></div>';
      }
    });
  }

  // ====== FOTO ======
  var fotoInput    = document.getElementById('foto');
  var lblFoto      = document.querySelector('label[for="foto"].custom-file-label');
  var btnPrevFoto  = document.getElementById('btnPrevFoto');
  var btnResetFoto = document.getElementById('btnResetFoto');
  var fotoObjURL   = null;

  if (fotoInput){
    fotoInput.addEventListener('change', function(){
      revoke(fotoObjURL); fotoObjURL=null;
      setLabel(fotoInput, lblFoto);
      btnPrevFoto.disabled = true;
      btnResetFoto.disabled= true;

      var f = this.files && this.files[0];
      if(!f) return;

      var okType = /^image\/(jpeg|png)$/i.test(f.type);
      var okSize = f.size <= 1.5*1024*1024;
      if(!okType){ Swal.fire('Format tidak didukung','Pilih JPG/PNG.','warning'); this.value=''; setLabel(this,lblFoto); return; }
      if(!okSize){ Swal.fire('Ukuran terlalu besar','Maksimal 1.5 MB. Ukuran sekarang: '+bytesHuman(f.size),'warning'); this.value=''; setLabel(this,lblFoto); return; }

      fotoObjURL = URL.createObjectURL(f);
      btnPrevFoto.disabled = false;
      btnResetFoto.disabled= false;
    });

    btnResetFoto && btnResetFoto.addEventListener('click', function(){
      revoke(fotoObjURL); fotoObjURL=null; fotoInput.value=''; setLabel(fotoInput,lblFoto);
      btnPrevFoto.disabled=true; btnResetFoto.disabled=true;
    });

    var EXIST_FOTO_URL  = <?= json_encode($fotoFile ? base_url('uploads/foto/'.rawurlencode($fotoFile)) : '') ?>;
    $('#modalPreviewFoto').on('show.bs.modal', function(e){
      var body = document.getElementById('previewFotoBody');
      if(!body) return;
      body.innerHTML = '<div class="p-4 text-muted">Tidak ada foto untuk dipratinjau.</div>';

      var fromExisting = e.relatedTarget && e.relatedTarget.getAttribute('data-existing') === '1';
      var url = fotoObjURL || (fromExisting ? EXIST_FOTO_URL : '');
      if(!url) return;

      body.innerHTML = '<img src="'+url+'" class="img-fluid" style="max-height:75vh" alt="Preview Foto">';
    });
  }
})();
</script>

<!-- butuh SweetAlert2 -->
<script>
/**
 * Loader ber-step pakai SweetAlert2
 * @param {Object} opts
 *  - title: judul modal
 *  - note: subteks kecil
 *  - steps: array label langkah
 *  - startIndex: index langkah awal (default 0)
 *  - allowClose: boleh ditutup manual? (default false)
 *  - run: (opsional) array async fn, dijalankan berurutan sesuai steps
 *         tiap fn boleh throw untuk menghentikan & menandai gagal
 */
function loaderSteps(opts = {}) {
  const steps = opts.steps || [
    'Memvalidasi data…',
    'Cek hari & jam…',
    'Cek kuota pendamping…',
    'Cek slot jadwal…',
    'Menyimpan…',
    'Kirim Notifikasi…'
  ];

  let idx = Math.max(0, Math.min(+(opts.startIndex || 0), steps.length));
  let failedAt = -1;

  const title = opts.title || 'Proses…';
  const note  = ('note' in opts) ? opts.note : 'Jangan tutup halaman ini';
  const allowClose = !!opts.allowClose;

  function html() {
    // progress: jumlah langkah selesai (idx) dari total
    const done = Math.max(0, Math.min(idx, steps.length));
    const percent = Math.round((done / steps.length) * 100);

    const items = steps.map((label, i) => {
      const isDone = i < idx && failedAt < 0;
      const isNow  = i === idx && failedAt < 0;
      const isFail = failedAt === i;

      let bullet = '';
      if (isDone) bullet = '<span class="ls-tick">✓</span>';
      else if (isFail) bullet = '<span class="ls-cross">✕</span>';
      else if (isNow) bullet = '<span class="ls-spin"></span>';
      else bullet = '';

      const cls =
        isFail ? 'ls-step fail' :
        isDone ? 'ls-step done' :
        isNow  ? 'ls-step now'  : 'ls-step todo';

      return `
        <li class="${cls}">
          <div class="ls-bullet">${bullet}</div>
          <div class="ls-label">${label}</div>
        </li>
      `;
    }).join('');

    return `
      <style>
        .ls-wrap{font-size:14px;text-align:left}
        .ls-note{color:#64748b;margin:.25rem 0 .75rem}
        .ls-progress{height:8px;background:#e5e7eb;border-radius:999px;overflow:hidden}
        .ls-bar{height:100%;width:${percent}%;background:#3b82f6;transition:width .35s ease;border-radius:999px}
        .ls-list{list-style:none;padding:0;margin:.75rem 0 0}
        .ls-step{display:flex;align-items:center;gap:.6rem;padding:.5rem 0;border-bottom:1px dashed #eef2f7}
        .ls-step:last-child{border-bottom:none}
        .ls-bullet{width:20px;height:20px;border-radius:999px;border:2px solid #cbd5e1;display:flex;align-items:center;justify-content:center;flex:0 0 20px}
        .ls-step.done .ls-bullet{background:#10b981;border-color:#10b981;color:#fff}
        .ls-step.fail .ls-bullet{background:#ef4444;border-color:#ef4444;color:#fff}
        .ls-step.now .ls-bullet{border-color:#60a5fa}
        .ls-label{color:#0f172a}
        .ls-tick{font-weight:700;font-size:12px;line-height:1}
        .ls-cross{font-weight:700;font-size:12px;line-height:1}
        .ls-spin{width:12px;height:12px;border-radius:999px;border:2px solid #93c5fd;border-top-color:transparent;animation:ls-rot .8s linear infinite;display:inline-block}
        @keyframes ls-rot{to{transform:rotate(360deg)}}
      </style>
      <div class="ls-wrap">
        ${note ? `<div class="ls-note">${note}</div>` : ''}
        <div class="ls-progress"><div class="ls-bar"></div></div>
        <ul class="ls-list">${items}</ul>
      </div>
    `;
  }

  // buka swal
  Swal.fire({
    title,
    html: html(),
    allowOutsideClick: false,
    allowEscapeKey: allowClose,
    showConfirmButton: false,
    didOpen: () => {
      // optional: tampilkan spinner kecil bawaan swal
      Swal.showLoading();
    }
  });

  function redraw() {
    // update HTML penuh agar simpel & robust
    Swal.update({ html: html() });
  }

  // --- API ---
  function setStep(i, newLabel) {
    if (typeof newLabel === 'string') steps[i] = newLabel;
    idx = Math.max(0, Math.min(i, steps.length));
    failedAt = -1;
    redraw();
  }

  function next(newLabel) {
    if (typeof newLabel === 'string' && idx < steps.length) steps[idx] = newLabel;
    idx = Math.min(idx + 1, steps.length);
    redraw();
  }

  function text(newLabel) {
    if (typeof newLabel === 'string' && idx < steps.length) {
      steps[idx] = newLabel;
      redraw();
    }
  }

  function fail(errorText) {
    if (idx >= steps.length) idx = steps.length - 1;
    if (idx < 0) idx = 0;
    failedAt = idx;
    if (typeof errorText === 'string' && errorText.trim()) {
      steps[idx] = errorText;
    }
    redraw();
  }

  function success(finalText) {
    if (typeof finalText === 'string' && finalText.trim()) {
      // set label terakhir bila ada
      if (idx < steps.length) steps[idx] = finalText;
    }
    idx = steps.length; // semua selesai
    redraw();
  }

  function close() {
    Swal.close();
  }

  // --- Mode otomatis (opsional) ---
  // opts.run: array async functions yang dijalankan berurutan
  async function run() {
    if (!Array.isArray(opts.run) || !opts.run.length) return;
    try {
      for (let i = 0; i < opts.run.length; i++) {
        setStep(i);               // fokus ke step i
        const label = steps[i];   // label saat ini
        // jalankan pekerjaan step i
        await opts.run[i]({ step:i, label, set:text });
        // selesai step → lanjut
        next();                   // tandai selesai & maju
      }
      success();                  // semua selesai
    } catch (err) {
      // tandai gagal di step saat ini
      const msg = (err && err.message) ? err.message : 'Gagal pada langkah ini.';
      fail(msg);
      // option: tampilkan alert kecil
      setTimeout(() => {
        Swal.fire({icon:'error', title:'Gagal', text:msg});
      }, 50);
      throw err;
    }
  }

  // auto-run jika disediakan
  if (Array.isArray(opts.run) && opts.run.length) {
    // tidak menunggu di sini; panggil manual jika mau await:
    run().catch(()=>{});
  }

  return { next, setStep, text, fail, success, close, run, steps };
}
</script>

