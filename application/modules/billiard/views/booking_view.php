<?php $this->load->view("front_end/head.php") ?>

<!-- Flatpickr -->
<link rel="stylesheet" href="<?php echo base_url("assets/admin/libs/flatpickr/flatpickr.min.css") ?>">
<script src="<?php echo base_url("assets/admin/libs/flatpickr/flatpickr.min.js") ?>"></script>

<!-- Locale Indonesian (inline) — cegah 'invalid locale undefined' -->
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
  fp.l10ns.id = Indonesian;
  var id = fp.l10ns;
  exports.Indonesian = Indonesian;
  exports.default = id;
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
  .divider-soft{ height:1px; background:linear-gradient(to right,transparent,#e9ecef,transparent); margin: 1rem 0 1.25rem; }

  /* ===== Tabel pendamping “manis” ===== */
  .table-modern {
    --tbl-bg:#ffffff;
    --tbl-head:#f8f9fc;
    --tbl-border:#eef2f7;
    background: var(--tbl-bg);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 8px 28px rgba(0,0,0,.06);
  }
  .table-modern thead th {
    background: var(--tbl-head);
    border-bottom: 1px solid var(--tbl-border);
    font-weight: 700;
    font-size: .9rem;
    position: sticky; top: 0; z-index: 1;
  }
  .table-modern tbody td { vertical-align: middle; border-color: var(--tbl-border); }
  .table-modern tbody tr:hover { background: #f9fbff; }
  .table-modern .btn-action {
    border: 1px solid #e3e8f0;
    padding: .25rem .5rem;
    border-radius: 10px;
  }
  .badge-soft {
    background: #eef2ff;
    color: #374151;
    border: 1px solid #e5e7eb;
    font-weight: 600;
    border-radius: 999px;
    padding: .25rem .5rem;
  }
  form[novalidate] :invalid { box-shadow: none; outline: 0; }


  /* ===== Modal pendamping: z-index fix biar bisa diklik ===== */
  .modal { z-index: 2000 !important; }
  .modal-backdrop { z-index: 1990 !important; }
  .modal-header { border-bottom: 0; }
  .modal-content { border: 0; border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,.18); }
  .modal-footer { border-top: 0; }
</style>
<style id="modal-zforce">
  /* angka tinggi agar menang atas easyui/window-mask/mm-blocker dkk */
  #modalPendamping.modal            { z-index: 200000 !important; pointer-events: auto !important; }
  .modal-backdrop                   { z-index: 199990 !important; pointer-events: none !important; }
  .flatpickr-calendar               { z-index: 200010 !important; }  /* datepicker di dalam modal */
</style>

<div class="container-fluid">
  <div class="hero-title" role="banner" aria-label="Judul situs">
    <h1 class="text"><?= $title ?></h1>
    <div class="text-muted"><?php echo $rec->type ?></div>
    <span class="accent" aria-hidden="true"></span>
  </div>

  <div class="row">
    <div class="col-lg-12">
      <div class="card card-elev">
        <div class="card-body">
          <form id="form_app" method="post" enctype="multipart/form-data" novalidate autocomplete="off">

            <!-- ====== Asal Instansi ====== -->
            <div class="header-title">Asal Instansi</div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group mb-2">
                  <label for="kategori" class="form-label label-required">Kategori Instansi</label>
                  <select id="kategori" name="kategori" class="form-control" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option value="opd">Dinas Pemprov Sulsel</option>
                    <option value="pn">Pengadilan Negeri</option>
                    <option value="pa">Pengadilan Agama</option>
                    <option value="ptun">PTUN Makassar</option>
                    <option value="kejati">Kejaksaan Tinggi</option>
                    <option value="kejari">Kejaksaan Negeri</option>
                    <option value="cabjari">Cabang Kejaksaan Negeri</option>
                    <option value="bnn">BNN</option>
                    <option value="kodim">Kodim Wil. Kodam XIV/Hasanuddin</option>
                    <option value="kepolisian">Kepolisian</option>
                    <option value="lainnya">Lainnya</option>
                  </select>
                  <small class="help-hint">Jika tidak ada di daftar, pilih <b>Lainnya</b>.</small>
                </div>
              </div>

              <div class="col-md-6">
                <!-- MODE SELECT -->
                <div class="form-group mb-2" id="instansi_select_wrap">
                  <label for="instansi" class="form-label label-required">Instansi</label>
                  <select id="instansi" name="instansi_id" class="form-control" required disabled>
                    <option value="">-- Pilih Instansi --</option>
                  </select>
                  <small class="help-hint">Pilih kategori terlebih dahulu untuk menampilkan daftar instansi.</small>
                </div>

                <!-- MODE MANUAL -->
                <div class="form-group mb-2 d-none" id="instansi_manual_wrap">
                  <label for="instansi_manual" class="form-label label-required">Nama Instansi</label>
                  <input type="text" id="instansi_manual" name="target_instansi_nama" class="form-control" placeholder="Tulis nama instansi">
                  <small class="help-hint">Contoh: KPP Pratama Makassar Utara</small>
                </div>
              </div>
            </div>

            <div class="divider-soft"></div>

            <!-- ====== Unit Tujuan ====== -->
            <div class="header-title">Unit Tujuan Lapas</div>
            <div class="form-group mb-2">
              <label for="unit_tujuan" class="form-label label-required">Unit Tujuan</label>
              <select id="unit_tujuan" name="unit_tujuan" class="form-control" title="-- Pilih Unit --" required>
                <option value="">-- Pilih Unit --</option>
                <?php 
                function render_options($tree, $level = 0) {
                  $no = 1;
                  foreach ($tree as $node) {
                    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
                    $prefix = ($level > 0) ? $no . '. ' : '';
                    $label  = htmlspecialchars($node->nama_unit, ENT_QUOTES, 'UTF-8');
                    $content = $indent . $prefix . $label;
                    echo '<option value="' . (int)$node->id . '" data-content="' . $content . '">' . $content . '</option>';
                    if (!empty($node->children)) render_options($node->children, $level + 1);
                    $no++;
                  }
                }
                render_options($units_tree);
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
                  <input type="text" id="nama_tamu" name="nama_tamu" class="form-control" placeholder="Nama lengkap" required>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group mb-2">
                  <label for="id_number" class="form-label label-required">NIK/NIP/NRP</label>
                  <input type="text" id="id_number" name="nik" class="form-control"
                         placeholder="NIK 16 / NIP 18 atau 9 / NRP 8–9"
                         inputmode="numeric" pattern="(?:\d{8,9}|\d{16}|\d{18})" maxlength="18" required>
                  <small id="id_help" class="help-hint">
                    Boleh: <b>NIK</b> 16 digit • <b>NIP</b> 18 atau 9 digit • <b>NRP</b> 8–9 digit.
                  </small>
                </div>
                <script>
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
              </div>
               <div class="col-md-6">
                <div class="form-group mb-2">
                  <label for="jabatan" class="form-label label-required">Jabatan</label>
                  <input type="text" id="jabatan" name="jabatan" class="form-control" placeholder="Contoh: Staf / Kepala Seksi" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group mb-2">
                  <label for="alamat" class="form-label label-required">Alamat Tamu</label>
                  <input type="text" id="alamat" name="alamat" class="form-control" placeholder="Alamat lengkap" required>
                  <small class="help-hint">Alamat sesuai KTP.</small>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group mb-2">
                  <label class="form-label label-required mb-1">Tempat / Tanggal Lahir</label>
                  <div class="row">
                    <div class="col-6">
                      <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir"
                             placeholder="Tempat lahir (mis. Makassar)" required autocomplete="off">
                      <div class="invalid-feedback">Tempat lahir wajib diisi.</div>
                    </div>
                    <div class="col-6">
                      <input type="text" class="form-control" id="tanggal_lahir_view" placeholder="dd/mm/yyyy" autocomplete="off" required>
                      <input type="hidden" id="tanggal_lahir" name="tanggal_lahir">
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
                  minlength="10" maxlength="13" pattern="0\d{9,12}" title="Mulai 0, total 10–13 digit" required>

                  <small class="help-hint">Gunakan nomor aktif untuk menerima WhatsApp konfirmasi.</small>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group mb-2">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" id="email" name="email" class="form-control"
                  placeholder="nama@instansi.go.id">
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

                <!-- Table wrap: disembunyikan sampai ada data -->
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

                <!-- Hidden untuk backend -->
                <input type="hidden" name="pendamping_json" id="pendamping_json" value="[]">
                <input type="hidden" name="jumlah_pendamping" id="jumlah_pendamping" value="0">

                <!-- Modal Pendamping -->
                <div class="modal fade" id="modalPendamping" tabindex="-1" aria-labelledby="modalPendampingLabel" aria-hidden="true">
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
                          <input
                            type="text"
                            id="pd_nik"
                            class="form-control"
                            placeholder="NIK 16 / NIP 18 atau 9 / NRP 8–9"
                            maxlength="18"
                            inputmode="numeric"
                            autocomplete="off">
                          <small class="text-muted">
                            Boleh: <b>NIK</b> 16 digit • <b>NIP</b> 18 atau 9 digit • <b>NRP</b> 8–9 digit.
                          </small>
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
                <!-- /Modal -->

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
                  <input type="hidden" id="tanggal" name="tanggal">
                  <small id="tanggal-info" class="form-text text-muted"></small>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group mb-2">
                  <label for="jam" class="form-label label-required">Jam Kunjungan</label>
                  <!-- <input type="time" id="jam" name="jam" class="form-control" disabled required> -->
                  <input type="time" id="jam" name="jam" class="form-control" step="300" disabled required>

                  <small id="jam-info" class="form-text text-danger"></small>
                </div>
              </div>
            </div>

            <div class="divider-soft"></div>

            <!-- ====== Keperluan & Lampiran ====== -->
            <div class="header-title">Keperluan & Lampiran</div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group mb-2">
                  <label for="keperluan" class="form-label">Keperluan Kunjungan</label>
                  <textarea id="keperluan" name="keperluan" class="form-control" rows="3" placeholder="Tuliskan keperluan kunjungan"></textarea>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group mb-2">
                  <label class="form-label">Surat Tugas (Opsional)</label>
                  <div class="custom-file">
                    <input type="file" class="custom-file-input" name="surat_tugas" id="surat_tugas">
                    <label class="custom-file-label" for="surat_tugas">Pilih file...</label>
                  </div>
                  <small class="help-hint">PDF/JPG/PNG ≤2 MB. Unggah atau tunjukkan saat check-in.</small>
                </div>
              </div>
            </div>

            <div class="text-center">
              <button type="button" class="btn btn-blue px-4" id="btnBooking" onclick="simpan()">Booking Sekarang</button>

              <!-- <button id="btnBooking" class="btn btn-blue" type="button" onclick="return simpan(this)">Booking Sekarang</button> -->
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Dependensi -->
<script src="<?php echo base_url('assets/admin') ?>/js/vendor.min.js"></script>
<script src="<?php echo base_url('assets/admin') ?>/js/app.min.js"></script>
<script src="<?php echo base_url('assets/admin') ?>/js/sw.min.js"></script>
<script src="<?php echo base_url(); ?>assets/admin/js/jquery.easyui.min.js"></script>
<?php $this->load->view("front_end/footer.php") ?>

<script>
  $(function () {
    const $f = $('#form_app');
    $f.on('submit', e => e.preventDefault()); // cegah submit native
    $f.on('keydown', 'input, select', function(e){
      if (e.key === 'Enter' && this.tagName !== 'TEXTAREA') e.preventDefault();
    });
  });

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
  btn  = btn  || document.getElementById('btnBooking');
  opts = opts || {};
  const steps = opts.steps || ['Memvalidasi data…','Cek hari & jam…','Cek kuota pendamping…','Cek slot jadwal…','Menyimpan…','Generate QR…','Kirim WhatsApp…'];
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
    btn.innerHTML = btn.dataset.originalHtml || 'Booking Sekarang';
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
   TANGGAL KUNJUNGAN + JAM
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

  function todayYmd(){
    const base = Date.now() + (window.serverOffsetMs || 0);
    const d = new Date(base);
    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
  }
  function buildInfoLine(dayIdx, conf, pushedMin=null){
    if (!conf || conf.closed) return `Hari ${HARI_ID[dayIdx]}: Libur`;
    let line = `Hari ${HARI_ID[dayIdx]}: ${dot(conf.open)}–${dot(conf.close)} ${tzAbbr}`;
    if (conf.break_start && conf.break_end) line += ` (Istirahat ${dot(conf.break_start)}–${dot(conf.break_end)} ${tzAbbr})`;
    if (pushedMin && toMin(pushedMin) > toMin(conf.open||'00:00')) line += ` • Minimal hari ini: ${dot(pushedMin)}`;
    return line;
  }
  function sameYmd(a,b){ return a&&b && a.getFullYear()===b.getFullYear() && a.getMonth()===b.getMonth() && a.getDate()===b.getDate(); }

  function applyForDate(d){
    jamInput.value = ''; jamInput.disabled = true;
    jamInput.removeAttribute('min'); jamInput.removeAttribute('max');
    if (infoTgl) infoTgl.textContent = ''; if (infoJam) infoJam.textContent = '';
    if (!(d instanceof Date) || isNaN(d)) return;

    const dayIdx = d.getDay();
    const conf   = OP_HOURS?.days?.[String(dayIdx)];
    if (!conf || conf.closed){
      if (infoTgl) infoTgl.textContent = `Hari ${HARI_ID[dayIdx]} libur, silakan pilih hari lain.`;
      if (window.Swal) Swal.fire({title:'Info', html:`Hari ${HARI_ID[dayIdx]} libur, silakan pilih hari lain.`, icon:'info'});
      if (elISO) elISO.value = ''; if (elView) elView.value = '';
      return;
    }

    // --- tentukan batas awal/akhir hari itu
    let minStr = conf.open || '00:00';
    let maxStr = conf.close || '23:59';

    // --- jika hari ini, dorong min ke "sekarang + lead", skip jam istirahat
    const nowMsServer = Date.now() + (window.serverOffsetMs || 0);
    const nowServer   = new Date(nowMsServer);
    if (sameYmd(d, nowServer)){
      const lead = Math.max(0, Math.min(1440, +(OP_HOURS?.lead ?? 0)));
      let earliest = nowServer.getHours()*60 + nowServer.getMinutes() + lead;
      const bs = toMin(conf.break_start), be = toMin(conf.break_end);
      if (bs!==null && be!==null && bs<be && earliest>=bs && earliest<be) earliest = be;
      const openMin = toMin(minStr) ?? 0;
      minStr = fromMin(Math.max(openMin, earliest));
    }

    // --- tidak ada slot?
    if (toMin(minStr) >= toMin(maxStr)) {
      jamInput.disabled = true;
      if (infoJam) infoJam.textContent = 'Tidak ada slot tersedia di hari ini.';
      return;
    }

    // --- set batas & info
    jamInput.disabled = false;
    jamInput.min = minStr; 
    jamInput.max = maxStr;
    if (infoJam) infoJam.textContent = buildInfoLine(dayIdx, conf, minStr);
  }


  const minToday = todayYmd();

  if (window.flatpickr && elView) {
    flatpickr(elView, withIdLocale({
      dateFormat: 'd/m/Y',
      allowInput: false,
      clickOpens: true,      // klik input buka kalender
      disableMobile: true,
      minDate: minToday,
    onChange(selectedDates, _, inst){
      const d = selectedDates && selectedDates[0] ? selectedDates[0] : null;
      if (elISO) elISO.value = d ? inst.formatDate(d,'Y-m-d') : '';
      applyForDate(d);
    },
    onClose(_, __, inst){
      // dukung input ketik manual
      const typed = elView.value && inst.parseDate(elView.value, 'd/m/Y');
      const minD  = inst.parseDate(minToday, 'Y-m-d');

      if (!typed) { elISO.value = ''; return; }

      // validasi minimal hari ini
      if (typed < minD) {
        elView.value = ''; elISO.value = '';
        if (window.Swal) Swal.fire({title:'Tanggal tidak valid', text:'Tidak bisa memilih tanggal sebelum hari ini.', icon:'warning'});
        return;
      }

      elISO.value = inst.formatDate(typed,'Y-m-d');
      applyForDate(typed);
    }
  }));

  } else if (elView) {
    elView.type = 'date'; elView.setAttribute('min', minToday);
    elView.addEventListener('change', function(){
      if (this.value && this.value < minToday) {
        this.value = '';
        if (infoTgl) infoTgl.textContent = 'Tidak bisa memilih tanggal sebelum hari ini.';
        if (window.Swal) Swal.fire({title:'Tanggal tidak valid', text:'Tidak bisa memilih tanggal sebelum hari ini.', icon:'warning'});
        return;
      }
      const d = this.value ? new Date(this.value) : null;
      elISO.value = this.value || '';
      applyForDate(d);
    });
  }

  if (elISO && elISO.value && elISO.value >= minToday) applyForDate(new Date(elISO.value));
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
  function todayYmd(){
    const base = Date.now() + (window.serverOffsetMs || 0);
    const d = new Date(base);
    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
  }

  if (window.flatpickr) {
    flatpickr(view, withIdLocale({
      dateFormat: 'd/m/Y',
      allowInput: false,
      clickOpens: true,      // klik input buka kalender
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
    view.addEventListener('change', function(){ iso.value = this.value || ''; });
  }
})();
</script>

<script>
/* =========================
   KATEGORI → INSTANSI (AJAX)
   ========================= */
$(function(){
  const URL_OPTIONS = '<?= site_url("booking/options_by_kategori"); ?>';

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


  $('#kategori').on('change', function(){
    const jenis = this.value;
    if (jenis === 'lainnya'){ toggleManual(true); return; }
    toggleManual(false);
    if(!jenis){ resetInstansi(); return; }
    setInstansiLoading();
    $.getJSON(URL_OPTIONS, { jenis })
    .done(function(resp){
      const list = Array.isArray(resp) ? resp : (resp.results || []);
      const $i = $('#instansi'); $i.empty().append('<option value="">-- Pilih Instansi --</option>');
      list.forEach(r => $i.append('<option value="'+r.id+'">'+r.text+'</option>'));
      $i.prop('disabled', list.length === 0);
    })
    .fail(function(){ resetInstansi(); alert('Gagal memuat data instansi.'); });
  });
});
</script>

<script>
/* ==============================================
   KILLER MASKER GLOBAL (biar modal gak ketutup)
   ============================================== */
window.killMasks = function () {
  $('.window-mask, .messager-mask, .datagrid-mask, .easyui-mask, .mm-wrapper__blocker')
    .css('pointer-events','none').hide();
};
</script>

<script>
/* =========================
   PENDAMPING (MODAL + tabel muncul otomatis saat ada data)
   ========================= */
(function(){
  window.pendamping = Array.isArray(window.pendamping) ? window.pendamping : [];
  let pendamping = window.pendamping;
  let editIndex  = -1;
  window.PD_MAX_LIMIT = null;

  // Elemen
  const btnOpen  = document.getElementById('btnOpenPendamping');
  const pdTableWrap = document.getElementById('pdTableWrap');
  const tblBody  = document.querySelector('#tblPendampingLocal tbody');
  const hidJson  = document.getElementById('pendamping_json');
  const hidJumlah= document.getElementById('jumlah_pendamping');
  const pdCountWrap = document.getElementById('pdCountBadgeWrap');
  const pdFilledBadge = document.getElementById('pdFilledBadge');
  const pdInfoText = document.getElementById('pdInfoText');
  const pdLimitInfo = document.getElementById('pdLimitInfo');

  // Modal
  const $modal = $('#modalPendamping');
  const elNik  = document.getElementById('pd_nik');
  const elNama = document.getElementById('pd_nama');
  const btnSubmit = document.getElementById('btnPdSubmit');
  const lblModal  = document.getElementById('modalPendampingLabel');
  const pdWarn    = document.getElementById('pdWarn');
  const inNikPemohon = document.getElementById('id_number');

  // Utils
  const onlyDigits = s => String(s||'').replace(/\D+/g,'');
  const esc = s => String(s||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]));
  const rxId = /^(?:\d{8,9}|\d{16}|\d{18})$/; // NRP 8–9, NIK 16, NIP 18/9
  const isValidId = s => rxId.test(String(s||''));

  // Open modal (mode add/edit)
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

    window.killMasks && window.killMasks();
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

  // Render: toggle table bila ada data
  function render(){
    hidJson.value   = JSON.stringify(pendamping);
    hidJumlah.value = String(pendamping.length);

    // Badge jumlah
    if (pendamping.length > 0){
      pdCountWrap.classList.remove('d-none');
      pdFilledBadge.textContent = pendamping.length;
    } else {
      pdCountWrap.classList.add('d-none');
    }

    // Tabel
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

  // Global actions untuk tombol di row
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

  // Event: input ID hanya angka, max 18 (duplikat handler dihapus)
  document.getElementById('pd_nik').addEventListener('input', function(){
    this.value = (this.value||'').replace(/\D+/g,'').slice(0,18);
  });
  // Enter untuk simpan
  document.getElementById('pd_nama').addEventListener('keydown', (e)=>{ if(e.key==='Enter'){ e.preventDefault(); submitModal(); }});
  document.getElementById('btnPdSubmit').addEventListener('click', submitModal);

  // Tombol buka modal
  document.getElementById('btnOpenPendamping').addEventListener('click', ()=>{
    if (window.PD_MAX_LIMIT !== null && pendamping.length >= window.PD_MAX_LIMIT){
      Swal.fire({title:'Batas tercapai', text:'Jumlah pendamping sudah mencapai batas maksimum unit ini.', icon:'info'});
      return;
    }
    window.killMasks && window.killMasks();
    openModal('add', -1);
  });

  // Batas pendamping per-unit
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

  // Hook submit dipanggil dari simpan()
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

  // Inisialisasi
  refreshLimitInfo();
  render();
})();
</script>

<script>
/* =========================
   SUBMIT / BOOKING
   ========================= */
   function _inBreak(conf, hhmm){
    const toMin = s => ((+s.split(':')[0])*60 + (+s.split(':')[1]));
    if(!conf || !conf.break_start || !conf.break_end) return false;
    const v = toMin(hhmm), bs = toMin(conf.break_start), be = toMin(conf.break_end);
    return bs < be && v >= bs && v < be;
  }
  function _confForDateStr(ymd){
    const d = new Date(ymd+'T00:00:00'); const i = d.getDay();
    return (window.OP_HOURS && OP_HOURS.days && OP_HOURS.days[String(i)]) || null;
  }

(function(){
  const form = document.getElementById('form_app');
  if (!form) return;
  $('#form_app').on('submit', function(e){ e.preventDefault(); return false; });
  // Ambil teks label untuk field
  const getLabel = (el) => {
    if (el.id){
      const lbl = form.querySelector(`label[for="${el.id}"]`);
      if (lbl) return lbl.textContent.replace('*','').trim();
    }
    return (el.getAttribute('placeholder') || el.name || 'Field').trim();
  };

  // Ubah reason -> pesan Indonesia
  const idMessage = (el) => {
    const v = el.validity;
    if (v.valueMissing)   return 'Wajib diisi';
    // if (v.typeMismatch)   return (el.type==='email') ? 'Format email tidak valid'
    //                        : (el.type==='url') ? 'URL tidak valid'
    //                        : 'Format tidak valid';
    if (v.patternMismatch)return el.title || 'Format tidak sesuai';
    if (v.tooShort)       return `Minimal ${el.minLength} karakter`;
    if (v.tooLong)        return `Maksimal ${el.maxLength} karakter`;
    if (v.rangeUnderflow) return `Minimal ${el.min}`;
    if (v.rangeOverflow)  return `Maksimal ${el.max}`;
    if (v.stepMismatch)   return 'Nilai tidak sesuai kelipatan. Gunakan kelipatan 5 (contoh 10:05)';
    if (v.badInput)       return 'Nilai tidak valid';
    if (v.customError)    return el.validationMessage || 'Tidak valid';
    return 'Tidak valid';
  };

  // Biar tooltip native juga Indonesia
  const wire = (el) => {
    const clear = () => el.setCustomValidity('');
    const set   = () => { if (!el.validity.valid) el.setCustomValidity(idMessage(el)); };
    el.addEventListener('invalid', set);
    el.addEventListener('input',  clear);
    el.addEventListener('change', clear);
  };
  form.querySelectorAll('input,select,textarea').forEach(wire);

  // Expose helper ringkasan untuk Swal
  window.buildInvalidListHTML = function(form){
    const invalids = Array.from(form.querySelectorAll(':invalid'));
    return '<ul style="text-align:left;margin:0;padding-left:1.1rem;">'
      + invalids.slice(0, 8).map(el => `<li>${getLabel(el)}: ${idMessage(el)}</li>`).join('')
      + (invalids.length>8 ? `<li>dst… (${invalids.length} field)</li>` : '')
      + '</ul>';
  };
})();

// Hard-block semua submit pada #form_app (termasuk yang dipicu plugin)
  (function(){
    const f = document.getElementById('form_app');
    if (!f) return;

    // Blokir event submit di fase capture (lebih dulu dari plugin lain)
    window.addEventListener('submit', function(e){
      if (e.target === f) {
        e.preventDefault();
        e.stopPropagation();
        if (e.stopImmediatePropagation) e.stopImmediatePropagation();
        return false;
      }
    }, true);

    // Jika ada lib yang memanggil f.submit() langsung, patch instance-nya
    const orig = f.submit?.bind(f);
    f.submit = function(){
      console.warn('Native form.submit() diblok.');
      return false;
    };

    // Cegah Enter memicu submit
    f.addEventListener('keydown', function(e){
      const tag = (e.target.tagName || '').toUpperCase();
      if (e.key === 'Enter' && tag !== 'TEXTAREA') {
        e.preventDefault();
      }
    });
  })();

async function simpan(btn){
  btn = btn || document.getElementById('btnBooking');
  const url  = "<?= site_url(strtolower($controller).'/add')?>/";
  const form = document.getElementById('form_app');

  // ---- 1) FE validation
  if (!form.checkValidity()){
    Swal.fire({ title:'Lengkapi data dulu', html: window.buildInvalidListHTML(form), icon:'warning' });
    return false;
  }

  // ---- 2) Konfirmasi
  const ok = await Swal.fire({
    title: 'Kirim Booking?',
    html: `Saya setuju <a href="<?= site_url('hal'); ?>" target="_blank" rel="noopener">S&K</a> dan <a href="<?= site_url('hal/privacy_policy'); ?>" target="_blank" rel="noopener">Kebijakan Privasi</a>.`,
    icon: "question",
    showCancelButton: true,
    reverseButtons: true,
    input: "checkbox",
    inputValue: 0,
    inputPlaceholder: "Ceklis: Ya, saya setuju !!!",
    confirmButtonText: "Ya, kirim sekarang",
    cancelButtonText:  "Batal",
    inputValidator: (r) => !r ? "Silakan ceklis persetujuan terlebih dahulu." : undefined
  }).then(r => r.isConfirmed);
  if (!ok) return false;

  // ---- 3) Cek tanggal & jam
  const ymd = document.getElementById('tanggal')?.value || '';
  const jam = document.getElementById('jam')?.value || '';
  if (!ymd) { Swal.fire({title:'Tanggal belum valid', text:'Silakan pilih tanggal dari datepicker.', icon:'warning'}); return false; }
  if (!jam || document.getElementById('jam').disabled) { Swal.fire({title:'Jam belum dipilih', text:'Silakan pilih jam kunjungan.', icon:'warning'}); return false; }
  const conf = _confForDateStr(ymd);
  if (conf && _inBreak(conf, jam)) { Swal.fire({title:'Jam istirahat', text:'Silakan pilih di luar jam istirahat.', icon:'info'}); return false; }

  // ---- 4) Validasi pendamping
  try { if (typeof window._ensurePendampingBeforeSubmit === 'function') window._ensurePendampingBeforeSubmit(); }
  catch (e) { Swal.fire({ title:'Validasi Gagal', text: e.message || 'Cek kembali data pendamping.', icon:'error' }); return false; }

  // ---- 5) Loader step (timing) + AJAX paralel
  const formData = new FormData(form);
  setLoading(true, btn, { interval: 900 });

  // a) buka loader-step
  const steps = [
    'Memvalidasi data…',
    'Cek hari & jam…',
    'Cek kuota pendamping…',
    'Cek slot jadwal…',
    'Generate QR Code…',
    'Menyimpan…',
    'Persiapan Kirim Notifikasi…'

  ];
  const perStep = [500, 600, 500, 600,700, 800, 700]; // ms per step (silakan ubah)
  const sleep = (ms)=> new Promise(r=>setTimeout(r, ms));
  const loader = loaderSteps({ title:'Memproses permohonan…', steps });

  // b) jalankan step berdasarkan timing (independen dari AJAX)
  const loaderPromise = (async () => {
    for (let i=0; i<steps.length; i++){
      loader.setStep(i);
      await sleep(perStep[i] || 600);
      loader.next();
    }
    // jangan close di sini—biar bagian sukses/fail yang nutup
  })();

  // c) siapkan helper swal redirect countdown
  function swalAutoRedirect({ ms=2500, to="#", title="Berhasil", html="Mengarahkan dalam <b></b> detik…" } = {}) {
    let timerInterval;
    Swal.fire({
      title, html, timer: ms, timerProgressBar: true,
      allowOutsideClick: false, allowEscapeKey: false, reverseButtons: true,
      showConfirmButton: false, showCancelButton: false,
      confirmButtonText: "Pergi sekarang", cancelButtonText: "Batal",
      didOpen: () => {
        Swal.showLoading();
        const b = Swal.getHtmlContainer().querySelector("b");
        const tick = () => { const left = Swal.getTimerLeft(); if (left != null && b) b.textContent = Math.ceil(left/1000); };
        tick(); timerInterval = setInterval(tick, 100);
      },
      willClose: () => { clearInterval(timerInterval); }
    }).then((res) => {
      if (res.isConfirmed || res.dismiss === Swal.DismissReason.timer) window.location.assign(to);
      else Swal.fire({ title: "Dibatalkan", icon: "info", timer: 1200, showConfirmButton: false });
    });
  }

  // d) AJAX paralel
  $.ajax({
    url, type: "POST", data: formData, processData: false, contentType: false, dataType: "json"
  })
  .done(async function(obj){
    if(!obj || obj.success === false){
      // gagal → tandai fail, tutup loader, tampilkan error (tanpa auto-close)
      loader.fail('Gagal menyimpan');
      loader.close();
      Swal.fire({
        title: obj?.title || "Validasi Gagal",
        icon: "error",
        html: obj?.pesan || "Terjadi kesalahan.",
        allowOutsideClick: false,
        confirmButtonText: "OK"
      });
      return;
    }

    // sukses → tunggu step selesai dulu baru redirect countdown
    await loaderPromise;                  // pastikan semua step tampil
    loader.success('Selesai');            // mark selesai
    setTimeout(() => {                    // beri jeda biar step terakhir terlihat
      loader.close();
      const to = obj.redirect_url;
      const pesan = 'Tunggu Sebentar yaa...';
      swalAutoRedirect({
        ms: 2500,
        to,
        title: obj.title || "Booking Berhasil",
        html: pesan + 'Mengarahkan dalam <b></b> detik…'
      });
      // jika perlu QR cepat, tambahkan di html di atas:
      // + (obj.qr_url ? "<br><img src='"+obj.qr_url+"' alt='QR' width='150' height='150'>" : "")
    }, 350);
  })
  .fail(function(xhr, status, error){
    loader.fail('Gagal menyimpan');
    loader.close();
    Swal.fire({
      title: "Error",
      text: "Terjadi kesalahan pada server: " + (error || status),
      icon: "error",
      confirmButtonText: "OK"
    });
  })
  .always(function(){
    setLoading(false, btn);
  });

  return false;
}

</script>

<script>
/* =======================================================
   PERBAIKAN POSISI MODAL & MASK saat show/shown/hide
   (tanpa handler klik & tanpa filter input dobel)
   ======================================================= */
$(function () {
  var $m = $('#modalPendamping');
  if ($m.length) $m.appendTo('body'); // hindari parent yang punya transform/z-index

  $('#modalPendamping')
    .on('show.bs.modal', function(){
      window.killMasks && window.killMasks();
    })
    .on('shown.bs.modal', function(){
      window.killMasks && window.killMasks();
      // tutup swal kalau ada
      if ($('.swal2-container:visible').length) { try { Swal.close(); } catch(e){} }
      // enable & fokus input
      $('#pd_nik').prop({ readonly:false, disabled:false });
      $('#pd_nama').prop({ readonly:false, disabled:false });
      setTimeout(function(){ $('#pd_nik').trigger('focus'); }, 30);

      // beberapa lib suka spawn masker lagi; bersihkan sebentar
      var tries = 0, iv = setInterval(function(){
        window.killMasks && window.killMasks();
        if (++tries > 15) clearInterval(iv); // ~1.8 detik
      }, 120);
      $(this).data('maskIv', iv);
    })
    .on('hide.bs.modal', function(){
      var iv = $(this).data('maskIv');
      if (iv) clearInterval(iv);
    });
});



</script>
<script>
  $('#no_hp').on('input', function(){
    this.value = (this.value || '').replace(/\D+/g,'').slice(0,13);
  });
</script>

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
    'Persiapan Kirim Notifikasi…'
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

  // Swal.fire({ title:"Proses...", html:"Jangan tutup halaman ini", allowOutsideClick:false, didOpen:() => Swal.showLoading() });


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
