<?php $this->load->view("front_end/head.php") ?>

<!-- Flatpickr -->
<link rel="stylesheet" href="<?php echo base_url("assets/admin/libs/flatpickr/flatpickr.min.css") ?>">
<script src="<?php echo base_url("assets/admin/libs/flatpickr/flatpickr.min.js") ?>"></script>

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
    firstDayOfWeek: 1,
    ordinal: function(){ return ""; },
    time_24hr: true,
    rangeSeparator: " - "
  };
  fp.l10ns.id = Indonesian;
  var id = fp.l10ns;
  exports.Indonesian = Indonesian;
  exports.default = id;
  Object.defineProperty(exports, '__esModule', { value: true });
})));
</script>

<style>
  .card-elev { border:0; border-radius:14px; box-shadow:0 6px 22px rgba(0,0,0,.06); }
  .form-label { font-weight:600; }
  .help-hint { color:#6c757d; font-size:.85rem; }
  /* .btn-blue{ background:linear-gradient(90deg,#2563eb,#1d4ed8); border:0; color:#fff; }
     .btn-blue:hover{ filter:brightness(1.06); } */
  .divider-soft { height:1px; background:linear-gradient(to right,transparent,#e9ecef,transparent); margin:1rem 0 1.25rem; }
  .text-danger { color:#795548 !important; }

  .card-box {
   /* background-color: #fff;
    padding: 0.5rem;
    -webkit-box-shadow: 0 1px 4px 0 rgb(0 0 0 / .1);
    box-shadow: 0 1px 4px 0 rgb(0 0 0 / .1);*/
    margin-bottom: 12px !important ;
    /*border-radius: .25rem;*/
}
</style>

<div class="container-fluid">
  <div class="hero-title ausi-hero-center" role="banner" aria-label="Judul halaman">
    <?php $this->load->view("front_end/back") ?>
    <h1 class="text mb-0"><?= html_escape($title) ?></h1>
    <!-- <div class="text-white">Pilih Meja Billiard</div> -->
    <span class="accent ausi-accent" aria-hidden="true"></span>
  </div>

  <div class="row">
    <div class="col-md-12 ">
      <!-- <label class="form-label d-block" for="meja">
        <h4 class="text-white"><b>Pilih Meja Billiard</b></h4>
      </label> -->

      <div id="meja" role="radiogroup" aria-label="Pilih Meja">
        <?php
          // pilihan awal dari controller / query string
          $selId = isset($selected_meja_id) ? (int)$selected_meja_id : (int)$this->input->get('meja_id');
        ?>

        <?php foreach ($mejas as $i => $m):
          $id = 'meja_'.$m->id_meja;

          // logika pilihan default (dipertahankan)
          $isChecked = ($selId > 0 && (int)$m->id_meja === (int)$m->id_meja)
            ? ((int)$m->id_meja === $selId) : ($i === 0);

          // Fallback default (kalau kolom null)
          $wk_day_start   = isset($m->wk_day_start)   ? substr($m->wk_day_start,0,5)   : '09:00';
          $wk_day_end     = isset($m->wk_day_end)     ? substr($m->wk_day_end,0,5)     : '18:00';
          $wk_day_rate    = isset($m->wk_day_rate)    ? (int)$m->wk_day_rate           : 35000;

          $wk_night_start = isset($m->wk_night_start) ? substr($m->wk_night_start,0,5) : '18:00';
          $wk_night_end   = isset($m->wk_night_end)   ? substr($m->wk_night_end,0,5)   : '02:00';
          $wk_night_rate  = isset($m->wk_night_rate)  ? (int)$m->wk_night_rate         : 40000;

          $we_day_start   = isset($m->we_day_start)   ? substr($m->we_day_start,0,5)   : '10:00';
          $we_day_end     = isset($m->we_day_end)     ? substr($m->we_day_end,0,5)     : '18:00';
          $we_day_rate    = isset($m->we_day_rate)    ? (int)$m->we_day_rate           : 40000;

          $we_night_start = isset($m->we_night_start) ? substr($m->we_night_start,0,5) : '18:00';
          $we_night_end   = isset($m->we_night_end)   ? substr($m->we_night_end,0,5)   : '02:00';
          $we_night_rate  = isset($m->we_night_rate)  ? (int)$m->we_night_rate         : 50000;

          $open  = substr($m->jam_buka,0,5);
          $close = substr($m->jam_tutup,0,5);
        ?>
          <div class="card-box project-box ">
            <div class="radio radio-danger mr-2 ml-3 ">
              <input
                type="radio"
                name="meja_id"
                id="<?= $id ?>"
                value="<?= (int)$m->id_meja ?>"
                form="frm"
                data-kategori="<?= html_escape(strtolower($m->kategori ?? 'reguler')) ?>"

                data-open="<?= $open ?>"
                data-close="<?= $close ?>"

                data-wk-day-start="<?= $wk_day_start ?>"
                data-wk-day-end="<?= $wk_day_end ?>"
                data-wk-day-rate="<?= $wk_day_rate ?>"

                data-wk-night-start="<?= $wk_night_start ?>"
                data-wk-night-end="<?= $wk_night_end ?>"
                data-wk-night-rate="<?= $wk_night_rate ?>"

                data-we-day-start="<?= $we_day_start ?>"
                data-we-day-end="<?= $we_day_end ?>"
                data-we-day-rate="<?= $we_day_rate ?>"

                data-we-night-start="<?= $we_night_start ?>"
                data-we-night-end="<?= $we_night_end ?>"
                data-we-night-rate="<?= $we_night_rate ?>"

                data-price="<?= $wk_day_rate ?>"
                data-nama="<?= html_escape($m->nama_meja) ?>"

                <?= $isChecked ? 'checked' : '' ?>
                required
              >

              <label for="<?= $id ?>" class="d-block w-100 m-0">
                <!-- Title -->
                <h4 class="mt-0"><b><?= html_escape($m->nama_meja) ?></b></h4>
              <?php
                $katLower = strtolower((string)($m->kategori ?? ''));
                $isVipMeja = (strpos($katLower, 'vip') !== false);
              ?>
              <?php if ($isVipMeja): ?>
                <div class="mt-1 mb-2 small text-danger">
                  <i class="mdi mdi-calendar-star mr-1"></i>
                  <b>Malam Tahun Baru</b> (31 Des 18:00 – 1 Jan 04:00): <b>Rp250.000/jam</b>
                </div>
              <?php endif; ?>

                <!-- Weekday -->
               <!--  <div class="mb-1">
                  <span class="pr-2 text-nowrap text-dark d-inline-block">
                    <i class="mdi mdi-circle text-info"></i> Weekday (Senin – Jumat)
                  </span>
                  <ul class="mb-2">
                    <li><?= $wk_day_start ?>–<?= $wk_day_end ?> (Rp<?= number_format($wk_day_rate,0,',','.') ?>/jam)</li>
                    <li><?= $wk_night_start ?>–<?= $wk_night_end ?> (Rp<?= number_format($wk_night_rate,0,',','.') ?>/jam)</li>
                  </ul>
                </div> -->

                <!-- Weekend -->
               <!--  <p class="mb-1">
                  <span class="pr-2 text-nowrap text-dark d-inline-block">
                    <i class="mdi mdi-circle text-warning"></i> Weekend (Sabtu – Minggu)
                  </span>
                </p>
                <ul class="mb-2">
                  <li><?= $we_day_start ?>–<?= $we_day_end ?> (Rp<?= number_format($we_day_rate,0,',','.') ?>/jam)</li>
                  <li><?= $we_night_start ?>–<?= $we_night_end ?> (Rp<?= number_format($we_night_rate,0,',','.') ?>/jam)</li>
                </ul>

                <p class="text-dark text-uppercase mb-2">
                  <i class="mdi mdi-calendar-clock"></i>
                  <small> Buka - Tutup <?= $open ?>–<?= $close ?> WITA</small>
                </p>
 -->
                <?php if ($m->catatan) : ?>
                  <style type="text/css">
                    .alert-catatan {
                     
                      margin: 0rem 0rem !important;
                      
                    }
                  </style>
                  <div class="alert alert-catatan alert-success mb-2" role="alert">
                    <i class="mdi mdi-check-all mr-2"></i> <?= html_escape($m->catatan) ?>
                  </div>
                <?php endif; ?>
              </label>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Form -->
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-elev">
        <div class="card-body">
          <?= form_open('billiard/add', ['id'=>'frm','autocomplete'=>'off','novalidate'=>true]) ?>
            <div class="row">
              <!-- <div class="col-12 mb-2">
                <small id="infoMeja" class="help-hint text-danger"></small>
              </div> -->

              <div class="col-md-6 mb-2">
                <label class="form-label" for="nama">Nama</label>
                <input type="text" id="nama" name="nama" class="form-control" placeholder="Nama lengkap" autocomplete="on" required>
              </div>

              <div class="col-md-6 mb-2">
                <label class="form-label" for="no_hp">Nomor HP (WA)</label>
                <input
                  type="text"
                  id="no_hp"
                  name="no_hp"
                  class="form-control"
                  placeholder="08xxxxxxxxxx / 628xxxxx"
                  inputmode="numeric"
                  minlength="10"
                  maxlength="13"
                  autocomplete="on"
                  title="Mulai 0, total 10–13 digit"
                  required
                >
                <small class="help-hint">Nomor aktif untuk WhatsApp konfirmasi.</small>
              </div>

              <div class="col-md-4 mb-2">
                <label class="form-label" for="tanggal_view">Tanggal Booking</label>
                <input type="text" id="tanggal_view" class="form-control" placeholder="dd/mm/yyyy" autocomplete="off" required>
                <input type="hidden" name="tanggal" id="tanggal">
                <?php
                  $maxDays = (int)($rec->maks_hari_booking ?? 3); // misal 3
                  $tz      = new DateTimeZone('Asia/Makassar');
                  $today   = new DateTime('today', $tz);
                  $maxDate = (clone $today)->modify("+{$maxDays} days"); // INKLUSIF: today .. today+maxDays
                  $maxDateIso  = $maxDate->format('Y-m-d');
                  $maxDateDisp = $maxDate->format('d/m/Y');
                ?>
                <small class="help-hint text-danger">
                  Booking dibuka sampai <?= html_escape($maxDateDisp) ?> (maks <?= $maxDays ?> hari).
                </small>
              </div>

              <div class="col-md-4 mb-2">
                <label class="form-label" for="jam_mulai">Jam Mulai</label>
                <input type="time" name="jam_mulai" id="jam_mulai" class="form-control" step="300" required>
                <small id="jam-info" class="help-hint text-danger"></small>
              </div>

              <div class="col-md-4 mb-2">
                <label class="form-label" for="durasi">Mau main berapa jam?</label>
                <select name="durasi_jam" id="durasi" class="form-control" required>
                  <option value="" selected disabled hidden>Pilih durasi…</option>
                  <option value="1">1 Jam</option>
                  <option value="2">2 Jam</option>
                  <option value="3">3 Jam</option>
                  <option value="4">4 Jam</option>
                  <option value="5">5 Jam</option>
                  <option value="6">6 Jam</option>
                  <option value="7">7 Jam</option>
                  <option value="8">8 Jam</option>
                  <option value="9">9 Jam</option>
                  <option value="10">10 Jam</option>
                </select>
              </div>


              <div class="col-12 mb-2">
                <div id="infoHitung" class="small text-danger"></div>
              </div>

              <div class="col-md-4 mb-3">
                <label class="form-label" for="voucher">Voucher / Kode Promo</label>
                <input
                  type="text"
                  name="voucher"
                  id="voucher"
                  class="form-control"
                  placeholder="Contoh: AUSI10 / FREE2JAM"
                  autocomplete="off"
                >
                <small class="help-hint">
                  Bisa berupa <b>FREE_MAIN</b> (khusus meja <b>REGULER</b>) atau diskon <b>NOMINAL/PERSEN</b> (semua meja).
                </small>
              </div>


              <div class="col-12 text-center">
                <button class="btn btn-blue px-4" id="btnSubmit" type="submit">
                  <span class="btn-label">Booking</span>
                  <span class="spinner-border spinner-border-sm align-middle d-none" role="status" aria-hidden="true"></span>
                </button>
              </div>
              <style type="text/css">
               /* tombol utama biru */
               .btn-blue{
                background:linear-gradient(90deg,#005bea,#00c6fb);
                color:#fff;
                border:0;
                font-weight:600;
                border-radius:10px;
              }
              .btn-blue:hover,
              .btn-blue:focus{
                color:#fff;
                filter:brightness(.9);
              }
              </style>


            </div>
          <?= form_close() ?>
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
<script>window.__VOUCHER_PREVIEW_URL = "<?= site_url('billiard/preview_voucher') ?>";</script>
<?php $this->load->view("front_end/footer.php") ?>
<!-- ====== RADIO MEJA: tampilkan hint jam & tarif (versi window-aware) ====== -->
<script>
  function getMejaName(selected){
  return selected?.dataset?.nama || '—';
}


document.addEventListener('DOMContentLoaded', function(){
  const radios = document.querySelectorAll('input[name="meja_id"]');
  const hint   = document.getElementById('infoMeja');

  function updateHint(){
    const r = document.querySelector('input[name="meja_id"]:checked');
    if(!r || !hint) return;

    // aman kalau getSelectedDate belum ada (fallback ke today)
    const d = (typeof getSelectedDate === 'function' && getSelectedDate()) 
              || window.__BOOKING_DATES?.today 
              || new Date();

    const cfg   = getCfgFor(r, d); 
    if(!cfg) return;

    const open  = r.getAttribute('data-open');
    const close = r.getAttribute('data-close');

    hint.innerHTML =
      `Buka - Tutup : ${open}–${close} WITA<br><b>${cfg.isWeekend ? 'Weekend' : 'Weekday'}</b>`+
      `<br>• Siang: ${cfg.day.start}–${cfg.day.end} (Rp${cfg.day.rate.toLocaleString('id-ID')}/jam) `+
      `<br>• Malam: ${cfg.night.start}–${cfg.night.end} (Rp${cfg.night.rate.toLocaleString('id-ID')}/jam)`;
  }

  updateHint();
  radios.forEach(r => r.addEventListener('change', updateHint));
  document.getElementById('tanggal_view')?.addEventListener('change', updateHint);
});
</script>


<!-- ====== GLOBAL UTILS & KONFIG (dipakai banyak blok) ====== -->
<script>
// window.AUSI_CFG = {
//   MAX_DAYS: <?= (int)($rec->maks_hari_booking ?? 3) ?>   // batas hari dari server
// };

window.AUSI_CFG = {
  MAX_DAYS: <?= (int)($rec->maks_hari_booking ?? 3) ?>,
  PAY_LIMIT_MIN: <?= (int)($rec->late_min ?? 60) ?>,
  MAX_HOURS: 10
};


function clampHours(v){
  const max = Number(window.AUSI_CFG?.MAX_HOURS || 5);
  const x = parseInt(v, 10);
  if (isNaN(x)) return null;
  return Math.max(1, Math.min(max, x));
}


// Utils umum
function pad(n){ return (n<10?'0':'')+n; }
function withIdLocale(opts){ try{ if(window.flatpickr?.l10ns?.id) opts.locale = flatpickr.l10ns.id; }catch(e){} return opts; }
function toMin(s){ if(!s) return null; const [h,m] = s.split(':').map(Number); return h*60+m; }
function fromMin(n){ return `${pad(Math.floor((n/60)%24))}:${pad(n%60)}`; }
function ymd(d){ return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`; }
function sameYMD(a,b){
  return a && b &&
    a.getFullYear()===b.getFullYear() &&
    a.getMonth()===b.getMonth() &&
    a.getDate()===b.getDate();
}

/* =========================================================
 * OVERRIDE TARIF: VIP Malam Tahun Baru = 250.000 / jam
 * Window: 31 Des 18:00 -> 1 Jan 06:00
 * ========================================================= */
const NY_VIP_RATE = 250000;
const NY_START_HM = '18:00';
const NY_END_HM   = '04:00';

function isVipMeja(r){
  const k = String(r?.dataset?.kategori || '').toLowerCase();
  return k.includes('vip');
}

// Cek overlap slot dengan window tahun baru (berdasarkan tanggal booking)
function isNewYearNight(dateObj, startHM, hours){
  if (!dateObj || !startHM) return false;

  const durH = Math.max(1, Math.min(12, parseInt(hours || '1', 10) || 1));

  const m = dateObj.getMonth() + 1;
  const d = dateObj.getDate();

  // basis timeline: 31 Des = 0..1439, 1 Jan = 1440..2879
  let dayOffset = 0;
  if (m === 12 && d === 31) dayOffset = 0;
  else if (m === 1 && d === 1) dayOffset = 1440;
  else return false;

  const slotStart = dayOffset + toMin(startHM);
  const slotEnd   = slotStart + (durH * 60);

  const winStart = toMin(NY_START_HM);        // 31 Des 18:00
  const winEnd   = 1440 + toMin(NY_END_HM);   // 1 Jan 06:00

  return Math.min(slotEnd, winEnd) > Math.max(slotStart, winStart);
}

function getRateOverrideNYVip(r, dateObj, startHM, hours){
  if (isVipMeja(r) && isNewYearNight(dateObj, startHM, hours)) {
    return { ok:true, rate: NY_VIP_RATE, label: 'Malam Tahun Baru (VIP)' };
  }
  return { ok:false };
}

function hmNow(){         // "HH:MM" waktu sekarang (lokal browser)
  const n = new Date();
  return `${pad(n.getHours())}:${pad(n.getMinutes())}`;
}
function fmtDur(min){min=Math.max(0,Math.floor(min));const h=Math.floor(min/60),m=min%60;return [h?`${h} jam`:null,m?`${m} mnt`:null].filter(Boolean).join(' ')||'0 mnt';}

// Tanggal acuan lokal (hindari isu timezone)
(function(){
  const today = new Date(); today.setHours(0,0,0,0);
  const maxDateObj = new Date(today);
  maxDateObj.setDate(maxDateObj.getDate() + window.AUSI_CFG.MAX_DAYS);
  maxDateObj.setHours(23,59,59,999); // inklusif sampai akhir hari

  window.__BOOKING_DATES = {
    today,
    maxDateObj,
    ymdToday: ymd(today),
    ymdMax:   ymd(maxDateObj)
  };
})();
</script>

<!-- ====== SINGLE INIT: FLATPICKR (HANYA INI) ====== -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const tanggalView = document.getElementById('tanggal_view');
  const tanggalIso  = document.getElementById('tanggal');
  if (!tanggalView || !tanggalIso) return;

  const { today, maxDateObj } = window.__BOOKING_DATES;

  if (window.flatpickr) {
    flatpickr(tanggalView, withIdLocale({
      dateFormat: 'd/m/Y',
      minDate: today,       // pakai Date object
      maxDate: maxDateObj,  // pakai Date object
      disableMobile: true,
      allowInput: false,
      defaultDate: tanggalIso.value || null,
      onChange(selectedDates, _, inst) {
        const d = selectedDates?.[0] || null;
        tanggalIso.value = d ? inst.formatDate(d, 'Y-m-d') : '';
      },
      onReady(_, __, inst) {
        if (tanggalIso.value) {
          const parsed = inst.parseDate(tanggalIso.value, 'Y-m-d');
          if (parsed) tanggalView.value = inst.formatDate(parsed, 'd/m/Y');
        }
      }
    }));
  } else {
    // Fallback: input[type="date"]
    const { ymdToday, ymdMax } = window.__BOOKING_DATES;
    tanggalView.type = 'date';
    tanggalView.min  = ymdToday;
    tanggalView.max  = ymdMax;
    tanggalView.addEventListener('change', function () {
      if (this.value && (this.value < ymdToday || this.value > ymdMax)) {
        this.value = '';
        tanggalIso.value = '';
        if (window.Swal) Swal.fire({
          title:'Tanggal tidak valid',
          text:`Pilih tanggal antara ${ymdToday} sampai ${ymdMax}.`,
          icon:'warning'
        });
        return;
      }
      tanggalIso.value = this.value || '';
    });
  }
});
</script>

<!-- ====== ATURAN JAM & DURASI (support overnight) ====== -->
<script>
  let __isSubmitting = false;

(function(){
  const jamMulai=document.getElementById('jam_mulai');
  const durasi=document.getElementById('durasi');
  const infoHitung=document.getElementById('infoHitung');
  const jamInfo=document.getElementById('jam-info');
  const tanggalView=document.getElementById('tanggal_view');

  if(!jamMulai||!durasi) return;

  function getSelectedMeja(){ return document.querySelector('input[name="meja_id"]:checked'); }

  // tetap jaga batas operasional (open/close) seperti versi lama
  function normalizeClose(openMin, closeMin){ return (closeMin<=openMin)? closeMin+24*60 : closeMin; }

  function refreshUI(){
  const sel = getSelectedMeja();
  // const d   = getSelectedDate() || window.__BOOKING_DATES?.today || new Date();
  const d = (typeof getSelectedDate === 'function' ? getSelectedDate() : null)
       || window.__BOOKING_DATES?.today
       || new Date();


  jamMulai.setCustomValidity('');
  if (jamInfo)    jamInfo.textContent = '';
  if (infoHitung) infoHitung.textContent = '';

  // Netralisir constraint native agar tidak muncul "maksimal XX:XX"
  jamMulai.removeAttribute('max');
// const pickedDate = getSelectedDate();
// jamMulai.min = (pickedDate && sameYMD(pickedDate, new Date())) ? hmNow() : '00:00';


  if (!sel) return;

  const open  = sel.getAttribute('data-open');   // "HH:MM"
  const close = sel.getAttribute('data-close');  // "HH:MM"
  const overnight = (toMin(close) <= toMin(open));

  // durasi (jam)
  let dHours = clampHours(durasi.value);


  // Info display saja
  const displayClose = overnight ? `${close} (next day)` : close;
  if (!dHours){
    if (infoHitung) {
      infoHitung.textContent =
        `Buka ${open}–${displayClose}. Isi durasi untuk estimasi & batas slot.`;
    }
    return;
  }

  // Estimasi & validasi window (Day/Night)
  // Estimasi & validasi window (Day/Night)
// Estimasi & validasi window (terima lintas Day→Night pakai tarif Day)
const band = slotBandFor(sel, jamMulai.value, dHours, d);
if (!band){
  // coba rule baru: Day→Night diterima & pakai tarif Day full
  const cx = slotDayCrossOK(sel, jamMulai.value, dHours, d);
  if (!cx.ok){
    const err = slotErrorFor(sel, jamMulai.value||'', dHours, d);
    if (jamMulai) jamMulai.setCustomValidity(err||'');
    if (jamInfo)  jamInfo.textContent = err||'';
    if (infoHitung) infoHitung.textContent = err||'';
    return;
  }

  // OK via rule Day→Night
  if (jamMulai) jamMulai.setCustomValidity('');
  if (jamInfo)  jamInfo.textContent = '';
 const overNY = getRateOverrideNYVip(sel, d, jamMulai.value, dHours);
  const rateUsed  = overNY.ok ? overNY.rate : cx.rate;
  const labelUsed = overNY.ok ? overNY.label : cx.label;

  const subtotal = rateUsed * dHours;

  if (infoHitung){
    infoHitung.innerHTML =
      `<b>${labelUsed}</b> ${cx.startHM}–${cx.endHM} `
      + `(${overNY.ok ? 'Tarif khusus Rp' + rateUsed.toLocaleString('id-ID') : 'pakai tarif Day Rp' + rateUsed.toLocaleString('id-ID')}/jam) • `
      + `Durasi ${dHours} jam → Estimasi: <b>Rp${subtotal.toLocaleString('id-ID')}</b>`
      + (overNY.ok ? `<br><span class="badge badge-danger mt-1">Tarif Malam Tahun Baru VIP</span>` : '');
  }

  return;
}

// Normal (tidak lintas window)
const overNY = getRateOverrideNYVip(sel, d, jamMulai.value, dHours);
const rateUsed  = overNY.ok ? overNY.rate : band.rate;
const labelUsed = overNY.ok ? overNY.label : band.label;

const subtotal = (rateUsed * dHours) || 0;

if (infoHitung) {
  infoHitung.innerHTML =
    `<b>${labelUsed}</b> ${band.wStart}–${band.wEnd} `
    + `(Rp${rateUsed.toLocaleString('id-ID')}/jam) • `
    + `Durasi ${dHours} jam → Estimasi: <b>Rp${subtotal.toLocaleString('id-ID')}</b>`
    + (overNY.ok ? `<br><span class="badge badge-danger mt-1">Tarif Malam Tahun Baru VIP</span>` : '');
}


}


  document.querySelectorAll('input[name="meja_id"]').forEach(r=>r.addEventListener('change',refreshUI));
  ['input','change','blur'].forEach(ev=>{
    durasi.addEventListener(ev,refreshUI);
    jamMulai.addEventListener(ev,refreshUI);
  });
  tanggalView?.addEventListener('change',refreshUI);

  if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', refreshUI);
} else {
  refreshUI();
}



})();
</script>

<script>
/* ==== PRICING HELPERS: weekday/weekend + day/night ==== */
function isWeekend(d){ const w=d.getDay(); return w===0 || w===6; } // Minggu=0, Sabtu=6
function getSelectedDate(){
  const h=document.getElementById('tanggal'); if(!h||!h.value) return null;
  const [y,m,dd]=h.value.split('-').map(Number); const d=new Date(y,m-1,dd); d.setHours(0,0,0,0); return d;
}
function getCfgFor(r, d){
  if(!r||!d) return null;
  const ds=r.dataset, we=isWeekend(d), p=we?'we':'wk';
  const day   ={ label: we?'Weekend Day':'Weekday Day',   start: ds[`${p}DayStart`],   end: ds[`${p}DayEnd`],   rate: Number(ds[`${p}DayRate`]||0) };
  const night ={ label: we?'Weekend Night':'Weekday Night',start: ds[`${p}NightStart`], end: ds[`${p}NightEnd`], rate: Number(ds[`${p}NightRate`]||0) };
  return { day, night, isWeekend: we };
}
function windowToSpan(w){
  const s=toMin(w.start), e0=toMin(w.end); const e=(e0<=s)? e0+24*60 : e0; // support overnight
  return {...w, s, e};
}
/** Kembalikan window aktif untuk slot (jamMulai, durasi) — atau null jika tidak masuk window mana pun */
function slotBandFor(r, startHM, hours, dateObj){
  const cfg = getCfgFor(r, dateObj); 
  if(!cfg || !startHM) return null;

  const h = clampHours(hours);
  if (!h) return null;

  const dur = h * 60;
  const start = toMin(startHM);
  const end   = start + dur;

  const dwin = windowToSpan(cfg.day);
  const nwin = windowToSpan(cfg.night);

  function inSpan(win){
    if (win.e <= 24*60){
      return (start >= win.s && end <= win.e);
    }
    if (start >= win.s && end <= win.e) return true;
    const start2 = start + 24*60, end2 = end + 24*60;
    return (start2 >= win.s && end2 <= win.e);
  }

  if (inSpan(dwin)) return { band:'day',   label:cfg.day.label,   rate:cfg.day.rate,   wStart:cfg.day.start,   wEnd:cfg.day.end };
  if (inSpan(nwin)) return { band:'night', label:cfg.night.label, rate:cfg.night.rate, wStart:cfg.night.start, wEnd:cfg.night.end };
  return null;
}

</script>

<!-- ====== VALIDASI + SUBMIT (SweetAlert) ====== -->
<script>
(function(){
  const frm         = document.getElementById('frm');
  const tanggalIso  = document.getElementById('tanggal');
  const tanggalView = document.getElementById('tanggal_view');
  const jamMulai    = document.getElementById('jam_mulai');
  const durasi      = document.getElementById('durasi');
  const btn         = document.getElementById('btnSubmit');
  const jamInfo     = document.getElementById('jam-info');
  const voucherInp  = document.getElementById('voucher');

  if (!frm) return;

  // kecilkan noise
  frm.querySelectorAll('input, select, textarea').forEach(el=>{
    el.addEventListener('invalid', e=> e.preventDefault());
    el.addEventListener('input',  ()=> el.setCustomValidity(''));
    el.addEventListener('change', ()=> el.setCustomValidity(''));
  });

  function getSelectedMeja(){ return document.querySelector('input[name="meja_id"]:checked'); }
  function getMejaLabel(selected){
    if (!selected) return '—';
    const lbl = document.querySelector(`label[for="${selected.id}"]`);
    return lbl ? lbl.innerText.trim() : (`MEJA #${selected.value}`);
  }
  function getLabel(el){
    if (el?.id) {
      const lbl = frm.querySelector(`label[for="${el.id}"]`);
      if (lbl) return lbl.textContent.trim().replace(/\*+$/, '');
    }
    return (el?.getAttribute('placeholder') || el?.name || 'Field').trim();
  }
  function buildInvalidList(){
    const invalids = Array.from(frm.querySelectorAll(':invalid'));
    if (!invalids.length) return '';
    const items = invalids.slice(0, 10).map(el => {
      let msg = el.validationMessage || 'Tidak valid';
      if (el.validity.valueMissing)   msg = 'Wajib diisi';
      else if (el.validity.patternMismatch && el.title) msg = el.title;
      else if (el.validity.tooShort)  msg = `Minimal ${el.minLength} karakter`;
      else if (el.validity.tooLong)   msg = `Maksimal ${el.maxLength} karakter`;
      else if (el.validity.rangeUnderflow) msg = `Minimal ${el.min}`;
      else if (el.validity.rangeOverflow)  msg = `Maksimal ${el.max}`;
      return `<li><b>${getLabel(el)}</b>: ${msg}</li>`;
    }).join('');
    return `<ul style="text-align:left; margin:0; padding-left:1.1rem;">${items}${invalids.length>10?'<li>dst…</li>':''}</ul>`;
  }

function roundUpTo5Min(hhmm){
  // input "14:02" -> output "14:05"
  const [H,M] = hhmm.split(':').map(Number);
  let total = H*60 + M;

  const mod = total % 5;
  if (mod !== 0) {
    total += (5 - mod); // naik ke kelipatan 5
  }

  const HH = String(Math.floor(total/60) % 24).padStart(2,'0');
  const MM = String(total % 60).padStart(2,'0');
  return `${HH}:${MM}`;
}

function validateTimeRange(){
  const sel      = document.querySelector('input[name="meja_id"]:checked');
  const jamMulai = document.getElementById('jam_mulai');
  const durasi   = document.getElementById('durasi');
  const jamInfo  = document.getElementById('jam-info');

  if (jamMulai){ jamMulai.setCustomValidity(''); }
  if (jamInfo)  { jamInfo.textContent=''; }
  if (!sel) return true;

  const dObj    = getSelectedDate() || new Date();
  const isToday = sameYMD(dObj, new Date());

  if (isToday){
    const nowRaw   = hmNow();            // mis. "13:58"
    const nowRound = roundUpTo5Min(nowRaw); // "14:00"

    jamMulai.min = nowRound; // pakai yg sudah dibuletin 5 menit

    if (jamMulai.value && toMin(jamMulai.value) < toMin(nowRound)){
      const msg = `Jam mulai tidak boleh di masa lalu (≥ ${nowRound}).`;
      jamMulai.setCustomValidity(msg);
      if (jamInfo) jamInfo.textContent = msg;
      return false;
    }
  } else {
    jamMulai.min = '00:00';
  }

  if (!jamMulai.value) return true;

  const dHours = clampHours(durasi.value || '1') || 1;


  const okBand = slotBandFor(sel, jamMulai.value, dHours, dObj);
  if (okBand){
    jamMulai.setCustomValidity('');
    if (jamInfo) jamInfo.textContent = '';
    return true;
  }

  // 2) Fallback: terima kasus lintas Day→Night dengan tarif Day penuh
  const cx = slotDayCrossOK(sel, jamMulai.value, dHours, dObj);
  if (cx.ok){
    jamMulai.setCustomValidity('');
    if (jamInfo) jamInfo.textContent = '';
    return true;
  }

  // 3) Masih tidak valid → error default
    // 3) Masih tidak valid → error default
  const errMsg = slotErrorFor(sel, jamMulai.value, dHours, dObj);
  jamMulai.setCustomValidity(errMsg);
  if (jamInfo) jamInfo.textContent = errMsg;
  return false;

}




  ['input','change'].forEach(ev=>{
    if (jamMulai) jamMulai.addEventListener(ev, validateTimeRange);
    if (durasi)   durasi.addEventListener(ev, validateTimeRange);
  });
  document.querySelectorAll('input[name="meja_id"]').forEach(r=>{
    r.addEventListener('change', validateTimeRange);
  });
  tanggalView?.addEventListener('change', validateTimeRange); // taruh di sini (sekali)

  // helper UI
  function htmlEscape(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"'`=\/]/g, function (s) {
      return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#x60;','=':'&#x3D;'})[s];
    });
  }
  // function setLoading(state){
  //   if (!btn) return;
  //   if (state){
  //     btn.disabled = true;
  //     btn.__original = btn.__original || btn.innerHTML;
  //     btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses…';
  //   } else {
  //     btn.disabled = false;
  //     if (btn.__original) btn.innerHTML = btn.__original;
  //   }
  // }

function setLoading(state){
  const btn = document.getElementById('btnSubmit');
  if (!btn) return;

  const labelEl = btn.querySelector('.btn-label');
  const spinEl  = btn.querySelector('.spinner-border');

  if (state) {
    btn.disabled = true;
    btn.setAttribute('aria-disabled','true');
    if (labelEl) labelEl.textContent = 'Memproses…';
    if (spinEl)  spinEl.classList.remove('d-none');
  } else {
    btn.disabled = false;
    btn.removeAttribute('aria-disabled');
    if (labelEl) labelEl.textContent = 'Booking';
    if (spinEl)  spinEl.classList.add('d-none');
  }
}



  // async function doPost(){
  //   setLoading(true);
  //   if (window.Swal) Swal.fire({ title: 'Nge-proses…', text: 'Sedang membuat booking, tunggu sebentar ya.', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

  //   try {
  //     const fd = new FormData(frm);
  //     const r  = await fetch(frm.action, { method:'POST', body: fd });
  //     const j  = await r.json();
  //     if (window.Swal) Swal.close();

  //     if (j.success) {
  //       const isFree = j.redirect_url && j.redirect_url.indexOf('/billiard/free') !== -1;
  //       if (window.Swal) {
  //         await Swal.fire({
  //           title: isFree ? 'Voucher diterima 🎉' : 'Berhasil!',
  //           html: isFree
  //           ? `Booking gratis aktif.<br>Durasi ditentukan sistem: <b>${getFreeHours()}</b> jam.`
  //           : (j.pesan || 'Booking berhasil dibuat.'),
  //           icon: 'success',
  //           confirmButtonText: 'Sip'
  //         });
  //       }
  //       if (j.redirect_url) setTimeout(() => { window.location.href = j.redirect_url; }, 250);
  //       return;
  //     }

  //     // === Tangani khusus error voucher ===
  //     const title = (j.title||'').toLowerCase();
  //     const pesan = (j.pesan||'');
  //     const voucherFilled = !!(voucherInp && voucherInp.value.trim() !== '');

  //     if (voucherFilled && (title.includes('voucher') || /voucher/i.test(pesan))) {
  //       const res = await Swal.fire({
  //         title: 'Kode vouchernya belum match 😅',
  //         html: pesan + '<br><br>Mau lanjut tanpa voucher aja?',
  //         icon: 'warning',
  //         showCancelButton: true,
  //         confirmButtonText: 'Lanjut tanpa voucher',
  //         cancelButtonText: 'Cek/ubah kode'
  //       });

  //       if (res.isConfirmed) {
  //         // kosongkan voucher & submit ulang
  //         voucherInp.value = '';
  //         return doPost();
  //       } else {
  //         // biar user bisa koreksi
  //         voucherInp.focus();
  //         return;
  //       }
  //     }

  //     // default error (bukan voucher)
  //     if (window.Swal) await Swal.fire({ title: j.title || 'Gagal', html: j.pesan || 'Gagal membuat booking.', icon: 'error', confirmButtonText: 'Oke' });
  //     else alert(j.title || 'Gagal membuat booking');
  //   } catch (err) {
  //     if (window.Swal) { Swal.close(); await Swal.fire({ title: 'Waduh', text: 'Koneksi error. Coba lagi yaa.', icon: 'error', confirmButtonText: 'Oke' }); }
  //     else alert('Koneksi error. Coba lagi.');
  //     console.error(err);
  //   } finally {
  //     setLoading(false);
  //   }
  // }
/* =====================================================
 * BOOKING LOADER STEP (SweetAlert versi bertingkat)
 * - Muncul list step vertikal
 * - Step aktif pakai spinner
 * - Step yang sudah lewat jadi centang hijau
 * - Step berikutnya abu-abu
 * ===================================================== */
let __bookTimer = null;

function startBookingLoader(steps, durMs){
  let idx = 0; // step aktif sekarang

  // builder HTML list step
  function makeListHtml(activeIdx){
    let lis = '';
    for (let i = 0; i < steps.length; i++){
      if (i < activeIdx){
        // sudah kelar -> centang
        lis += `
          <li class="mb-2" style="display:flex;align-items:flex-start;">
            <i class="mdi mdi-check-circle-outline text-success mr-2"
               style="font-size:1.1rem;line-height:1.1rem;"></i>
            <span>${steps[i]}</span>
          </li>`;
      } else if (i === activeIdx){
        // lagi jalan -> spinner
        lis += `
          <li class="mb-2" style="display:flex;align-items:flex-start;">
            <span class="spinner-border spinner-border-sm mr-2"
                  role="status" aria-hidden="true"></span>
            <span>${steps[i]}</span>
          </li>`;
      } else {
        // belum jalan -> abu2
        lis += `
          <li class="mb-2 text-muted"
              style="display:flex;align-items:flex-start;opacity:.5;">
            <i class="mdi mdi-checkbox-blank-circle-outline mr-2"
               style="font-size:.9rem;line-height:1.1rem;"></i>
            <span>${steps[i]}</span>
          </li>`;
      }
    }
    return `
      <ul style="list-style:none;margin:0;padding-left:0;text-align:left;">
        ${lis}
      </ul>`;
  }

  Swal.fire({
    title: 'Lagi dibuatin booking… 🙌',
    html: makeListHtml(idx),
    allowOutsideClick: false,
    allowEscapeKey: false,
    showConfirmButton: false,
    width: '480px',
    didOpen: () => {
      const box = Swal.getHtmlContainer();

      __bookTimer = setInterval(()=>{
        idx++;

        // kalau udah lewat jumlah step, stop interval dan render semua centang hijau
        if (idx >= steps.length){
          clearInterval(__bookTimer);
          __bookTimer = null;

          if (box){
            let doneLis = '';
            for (let i = 0; i < steps.length; i++){
              doneLis += `
                <li class="mb-2" style="display:flex;align-items:flex-start;">
                  <i class="mdi mdi-check-circle-outline text-success mr-2"
                     style="font-size:1.1rem;line-height:1.1rem;"></i>
                  <span>${steps[i]}</span>
                </li>`;
            }
            box.innerHTML = `
              <ul style="list-style:none;margin:0;padding-left:0;text-align:left;">
                ${doneLis}
              </ul>`;
          }
          return;
        }

        // update tampilan tiap "durMs"
        if (box){
          box.innerHTML = makeListHtml(idx);
        }

      }, durMs || 900);
    }
  });
}

// Tutup loader (panggil ini pas sudah dapat response server)
function stopBookingLoader(){
  if (__bookTimer){
    clearInterval(__bookTimer);
    __bookTimer = null;
  }
  Swal.close();
}

/* ====== SIMPAN RIWAYAT BOOKING KE LOCALSTORAGE ======
 * key: 'ausi_billiard_booking_history_v1'
 * nanti bisa dibaca di halaman "riwayat booking"
 * dan redirect ke detail pakai field redirect_url
 * =================================================== */
function saveBookingToLocal(j){
  try {
    if (typeof window === 'undefined' || !window.localStorage) return;

    const key = 'ausi_billiard_booking_history_v1';

    const sel        = getSelectedMeja && getSelectedMeja();
    const mejaName   = getMejaName ? getMejaName(sel) : (sel?.dataset?.nama || '—');
    const namaVal    = (frm.querySelector('#nama')  || {}).value || '';
    const noHpVal    = (frm.querySelector('#no_hp') || {}).value || '';
    const tglIso     = (typeof tanggalIso  !== 'undefined' && tanggalIso)  ? (tanggalIso.value  || '') : '';
    const tglView    = (typeof tanggalView !== 'undefined' && tanggalView) ? (tanggalView.value || tglIso) : tglIso;
    const jamMulaiEl = document.getElementById('jam_mulai');
    const durasiEl   = document.getElementById('durasi');
    const voucherEl  = document.getElementById('voucher');

    const jamVal      = (jamMulaiEl && jamMulaiEl.value) || '';
    const durVal      = (durasiEl && durasiEl.value) || '1';
    const durInt      = parseInt(durVal, 10) || 0;
    const voucherCode = (voucherEl && voucherEl.value.trim())
      ? voucherEl.value.trim().toUpperCase()
      : '';

    const item = {
      // meta waktu
      ts: Date.now(),
      created_at: new Date().toISOString(),

      // ===== dari server (prioritas) =====
      id:            j.id_pesanan || j.id || null,
      nomor:         j.nomor || null,
      kode_booking:  j.kode_booking || null,
      status:        j.status || 'draft',      // sekarang backend kirim status asli
      redirect_url:  j.redirect_url || null,

      // ringkasan dari form
      meja_name:     mejaName,
      nama:          namaVal,
      no_hp:         noHpVal,
      tanggal_iso:   tglIso,
      tanggal_view:  tglView,
      jam_mulai:     jamVal,
      durasi_jam:    durInt,
      voucher:       voucherCode || null
    };

    let arr = [];
    const raw = localStorage.getItem(key);
    if (raw) {
      try { arr = JSON.parse(raw) || []; } catch(e){ arr = []; }
    }

    // buang duplikat berdasarkan kode_booking / id_pesanan / redirect_url
    arr = arr.filter(old => {
      if (item.id && old.id && old.id === item.id) return false;
      if (item.kode_booking && old.kode_booking && old.kode_booking === item.kode_booking) return false;
      if (item.redirect_url && old.redirect_url && old.redirect_url === item.redirect_url) return false;
      return true;
    });

    arr.unshift(item);
    if (arr.length > 30) arr = arr.slice(0, 30);

    localStorage.setItem(key, JSON.stringify(arr));
  } catch (e) {
    console.warn('Gagal menyimpan riwayat booking di localStorage', e);
  }
}


/* ========== POST BOOKING KE SERVER ========== */
async function doPost(){
  // kunci tombol & tandai sedang submit
  __isSubmitting = true;
  setLoading(true);

  // tampilkan loader bertahap
  const steps = [
    'Cek data form kamu dulu… 😎',
    'Cek jam & durasi, bentrok nggak… ⏰',
    'Kunci slot mejanya… 🎱',
    'Bikin kode booking kamu… 🧾',
    'Simpan ke sistem kasir… 💾',
    'Ngabarin balik ke kamu… 📲'
  ];
  startBookingLoader(steps, 900);

  try {
    const fd = new FormData(frm);
    const r  = await fetch(frm.action, { method:'POST', body: fd });

    stopBookingLoader();

    let j = null;
    try { j = await r.json(); } catch(e){}

    // kalau server nggak jawab normal
    if (!r.ok || !j){
      // ERROR → boleh coba lagi
      if (window.Swal){
        await Swal.fire({
          title: 'Waduh',
          text: 'Server nggak jawab normal. Coba lagi ya 🙏',
          icon: 'error',
          confirmButtonText: 'Oke'
        });
      } else {
        alert('Server error / response bukan JSON');
      }

      // buka kunci lagi biar bisa retry
      __isSubmitting = false;
      setLoading(false);
      return;
    }

    // ======== SUCCESS CASE DARI SERVER ========
    if (j.success){
      // SIMPAN RIWAYAT DI LOCALSTORAGE DI SINI
      saveBookingToLocal(j);

      const isFree = j.redirect_url && j.redirect_url.indexOf('/billiard/free') !== -1;

      if (window.Swal){
        await Swal.fire({
          title: isFree ? 'Voucher diterima 🎉' : 'Berhasil!',
          html: isFree
            ? 'Booking gratis aktif 🎉<br>Durasi main sesuai voucher kamu sudah dikunci di sistem.'
            : (j.pesan || 'Booking berhasil dibuat.'),
          icon: 'success',
          confirmButtonText: 'Sip'
        });
      } else {
        alert('Booking berhasil dibuat.');
      }

      // >>> PENTING: jangan setLoading(false) lagi di sini <<<
      // >>> biarin tombol tetap disabled supaya gak bisa spam <<<

      if (j.redirect_url){
        setTimeout(()=>{
          window.location.href = j.redirect_url;
        }, 250);
      }
      return;
    }

    // ======== GAK SUCCESS, tapi masih respon normal ========
    const titleLow   = (j.title||'').toLowerCase();
    const pesan      = (j.pesan||'');
    const voucherInp = document.getElementById('voucher');
    const voucherFilled = !!(voucherInp && voucherInp.value.trim() !== '');

    // kasus voucher salah
    if (voucherFilled && (titleLow.includes('voucher') || /voucher/i.test(pesan))){
      const res = await Swal.fire({
        title: 'Kode vouchernya belum match 😅',
        html: pesan + '<br><br>Mau lanjut tanpa voucher aja?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Lanjut tanpa voucher',
        cancelButtonText: 'Cek/ubah kode'
      });

      if (res.isConfirmed){
        voucherInp.value = '';
        // coba submit ulang
        return doPost();
      } else {
        voucherInp.focus();
        // gagal tapi user mau koreksi → unlock tombol
        __isSubmitting = false;
        setLoading(false);
        return;
      }
    }

    // error umum (bukan voucher)
    if (window.Swal){
      await Swal.fire({
        title: j.title || 'Gagal',
        html: j.pesan || 'Gagal membuat booking.',
        icon: 'error',
        confirmButtonText: 'Oke'
      });
    } else {
      alert(j.title || 'Gagal membuat booking');
    }

    // gagal → boleh retry
    __isSubmitting = false;
    setLoading(false);

  } catch (err) {
    // fetch() error
    stopBookingLoader();

    if (window.Swal){
      await Swal.fire({
        title: 'Waduh',
        text: 'Koneksi error. Coba lagi yaa.',
        icon: 'error',
        confirmButtonText: 'Oke'
      });
    } else {
      alert('Koneksi error. Coba lagi.');
    }

    console.error('Booking fetch error:', err);

    // error jaringan → boleh retry
    __isSubmitting = false;
    setLoading(false);
  }
}


  function normalizeJamMulai() {
  const el = document.getElementById('jam_mulai');
  if (!el) return;

  // ganti titik ke colon
  if (el.value && el.value.indexOf('.') !== -1) {
    el.value = el.value.replace(/\./g, ':');
  }

  // cek pola HH:MM manual (00-23):(00-59)
  const v = el.value.trim();
  const ok = /^(?:[01]?\d|2[0-3]):[0-5]\d$/.test(v);

  // reset dulu
  el.setCustomValidity('');

  if (!ok) {
    // kalau kosong biarkan required yg bicara
    if (v !== '') {
      el.setCustomValidity('Format jam harus HH:MM, contoh 14:40 (pakai titik dua).');
    }
  }
}



// pas user ngetik / blur / ganti jam, kita rapikan & kasih message custom
document.addEventListener('DOMContentLoaded', function(){
  const jamMulai = document.getElementById('jam_mulai');
  if (!jamMulai) return;

  ['input','change','blur'].forEach(ev=>{
    jamMulai.addEventListener(ev, function(){
      normalizeJamMulai();
    });
  });

  // custom pesan default browser waktu field ini invalid
  jamMulai.addEventListener('invalid', function(e){
    // cegah pesan default "Enter a valid value"
    e.preventDefault();
    normalizeJamMulai(); // pastikan sudah bersih dulu
  });
});
// set dari PHP:
// 
const VOUCHER_PREVIEW_URL = window.__VOUCHER_PREVIEW_URL || '';

function sanitizeVoucherCode(s){
  return String(s || '')
    .toUpperCase()
    .replace(/[^A-Z0-9\-_]/g, '')
    .slice(0, 32);
}
function fmtRp(n){
  const x = parseInt(n, 10) || 0;
  return 'Rp ' + x.toLocaleString('id-ID');
}
async function previewVoucher(payload){
  if (!VOUCHER_PREVIEW_URL) throw new Error('VOUCHER_PREVIEW_URL belum di-set');
  const fd = new FormData();
  Object.keys(payload).forEach(k => fd.append(k, payload[k] ?? ''));
  const res = await fetch(VOUCHER_PREVIEW_URL, {
    method: 'POST',
    body: fd,
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  });
  return await res.json();
}



  frm.addEventListener('submit', async (e) => {
  e.preventDefault();

  // anti double click VERY EARLY
  if (__isSubmitting) {
    return; // sudah proses, abaikan spam
  }

  setLoading(true);
  __isSubmitting = true;

  // ===== VALIDASI TANGGAL =====
  const { today, maxDateObj } = window.__BOOKING_DATES;

  if (!tanggalIso.value) {
    if (window.Swal) {
      await Swal.fire({
        title: 'Tanggal kosong',
        text: 'Pilih tanggal mainnya dulu ya.',
        icon: 'warning'
      });
    } else {
      alert('Tanggal kosong. Pilih tanggal dulu.');
    }
    setLoading(false);
    __isSubmitting = false;
    return;
  } else {
    const picked = new Date(tanggalIso.value + 'T00:00:00');
    if (picked < today) {
      if (window.Swal) {
        await Swal.fire({
          title: 'Tanggal lewat',
          text: 'Pilih tanggal yang belum lewat dong.',
          icon: 'warning'
        });
      } else {
        alert('Tanggal sudah lewat.');
      }
      setLoading(false);
      __isSubmitting = false;
      return;
    }
    if (picked > maxDateObj) {
      const ymdMax = window.__BOOKING_DATES.ymdMax;
      if (window.Swal) {
        await Swal.fire({
          title: 'Tanggal terlalu jauh',
          text: `Tanggal booking maksimal sampai ${ymdMax}.`,
          icon: 'warning'
        });
      } else {
        alert(`Tanggal maksimal: ${ymdMax}`);
      }
      setLoading(false);
      __isSubmitting = false;
      return;
    }
  }

  const timeOk = validateTimeRange();
  // document.getElementById('tanggal_view')?.addEventListener('change', validateTimeRange);

  const allOk  = frm.checkValidity();
  if (!allOk || !timeOk) {
    const listHtml = buildInvalidList() || '<p>Periksa kembali isian kamu ya.</p>';
    if (window.Swal) {
      await Swal.fire({
        title: 'Cek dulu',
        html: listHtml,
        icon: 'warning',
        confirmButtonText: 'Oke'
      });
    } else {
      alert('Periksa kembali isian kamu.');
    }
    const firstInvalid = frm.querySelector(':invalid') || (!timeOk ? jamMulai : null);
    if (firstInvalid) firstInvalid.focus();

    setLoading(false);
    __isSubmitting = false;
    return;
  }

  // ===== KONFIRMASI RINGKAS =====
  const sel        = getSelectedMeja();
  const mejaName   = getMejaName(sel);
  const namaVal    = (frm.querySelector('#nama')  || {}).value || '';
  const noHpVal    = (frm.querySelector('#no_hp') || {}).value || '';
  const tglLabel   = (document.getElementById('tanggal_view')||{}).value || tanggalIso.value || '—';
  const durVal     = (durasi.value || '1');
  const jamLabel   = (jamMulai.value || '00:00') + ' / Durasi: ' + durVal + ' jam';
  // const voucherCode = (voucherInp && voucherInp.value.trim()) ? voucherInp.value.trim().toUpperCase() : '';
  const voucherCode = sanitizeVoucherCode(voucherInp && voucherInp.value ? voucherInp.value.trim() : '');

  const durInt = clampHours(durVal) || 1;

  const dObj   = (getSelectedDate() || new Date());
  const startHM = (jamMulai.value || '00:00');

  let labelTxt = '—';
  let subtotal = 0;

// 1) hitung base (normal window)
const bandObj = slotBandFor(sel, startHM, durInt, dObj);
if (bandObj){
  labelTxt = bandObj.label;
  subtotal = bandObj.rate * durInt;
} else {
  // 2) fallback lintas Day→Night pakai tarif Day
  const cx = slotDayCrossOK(sel, startHM, durInt, dObj);
  if (cx.ok){
    labelTxt = cx.label;
    subtotal = cx.rate * durInt;
  }
}

// 3) TERAKHIR: override Malam Tahun Baru VIP (menimpa base)
const overNY2 = getRateOverrideNYVip(sel, dObj, startHM, durInt);
if (overNY2.ok){
  labelTxt = overNY2.label;
  subtotal = overNY2.rate * durInt;
}

// =========================================================
// HARD CODE FRONTEND: VIP Malam Tahun Baru = 250.000 / jam
// Window: 31 Des 18:00 -> 1 Jan 04:00
// =========================================================
// const NY_VIP_RATE = 250000;
// const NY_START_HM = '18:00';
// const NY_END_HM   = '04:00';

// function isVipMeja(r){
//   const k = String(r?.dataset?.kategori || '').toLowerCase();
//   return k.includes('vip');
// }

// // Cek overlap slot dengan window tahun baru (pakai tanggal booking, bukan "hari ini")
// function isNewYearNight(dateObj, startHM, hours){
//   if (!dateObj || !startHM) return false;

//   const y = dateObj.getFullYear();
//   const m = dateObj.getMonth() + 1;
//   const d = dateObj.getDate();

//   // hanya relevan untuk 31 Des atau 1 Jan
//   let dayOffset = 0;
//   if (m === 12 && d === 31) {
//     dayOffset = 0;        // 31 Des
//   } else if (m === 1 && d === 1) {
//     dayOffset = 1440;     // 1 Jan = +1 hari dari basis 31 Des
//   } else {
//     return false;
//   }

//   const durH = Math.max(1, Math.min(12, parseInt(hours || '1', 10) || 1));
//   const slotStart = dayOffset + toMin(startHM);
//   const slotEnd   = slotStart + (durH * 60);

//   const winStart = toMin(NY_START_HM);           // 18:00 = 1080
//   const winEnd   = 1440 + toMin(NY_END_HM);      // next day 04:00 = 1800

//   const ovStart = Math.max(slotStart, winStart);
//   const ovEnd   = Math.min(slotEnd, winEnd);
//   return ovEnd > ovStart;
// }

// function getRateOverrideNYVip(r, dateObj, startHM, hours){
//   if (isVipMeja(r) && isNewYearNight(dateObj, startHM, hours)) {
//     return { ok:true, rate: NY_VIP_RATE, label: 'Malam Tahun Baru (VIP)' };
//   }
//   return { ok:false };
// }





// if (bandObj){
//   labelTxt = bandObj.label;
//   subtotal = bandObj.rate * durInt;
// } else {
//   // fallback: lintas Day→Night pakai tarif Day
//   const cx = slotDayCrossOK(sel, (jamMulai.value||'00:00'), durInt, (getSelectedDate()||new Date()));
//   if (cx.ok){
//     labelTxt = cx.label;
//     subtotal = cx.rate * durInt; // SELURUH DURASI tarif siang
//   }
// }

// const payMethodRaw =
//   (frm.querySelector('[name="metode_bayar"]')?.value ||
//    frm.querySelector('[name="payment_method"]')?.value ||
//    frm.querySelector('[name="pay_method"]')?.value || '').toLowerCase().trim();

// const isNonCash = (payMethodRaw && payMethodRaw !== 'cash'); // transfer/qris/dll

// let voucherPreview = null;
// let estimasi = (subtotal > 0)
//   ? `${fmtRp(subtotal)} (${labelTxt})`
//   : '— (pilih jam agar masuk window)';

// // === jika ada voucher: precheck ke server supaya tahu jenisnya ===
// if (voucherCode) {
//   try {
//     voucherPreview = await previewVoucher({
//       voucher: voucherCode,
//       no_hp: noHpVal,
//       tanggal: tanggalIso.value,
//       meja_id: sel,
//       jam_mulai: jamMulai.value,
//       durasi_jam: durInt,
//       subtotal: subtotal,
//       metode_bayar: payMethodRaw
//     });

//     if (!voucherPreview || !voucherPreview.success) {
//       const msg = (voucherPreview && voucherPreview.pesan) ? voucherPreview.pesan : 'Voucher tidak valid.';
//       if (window.Swal) {
//         await Swal.fire({ title: 'Voucher Invalid', html: msg, icon: 'error' });
//       } else {
//         alert(msg);
//       }
//       setLoading(false);
//       __isSubmitting = false;
//       return;
//     }
//   } catch (err) {
//     if (window.Swal) {
//       await Swal.fire({
//         title: 'Gagal cek voucher',
//         text: 'Tidak bisa verifikasi voucher. Coba lagi ya.',
//         icon: 'error'
//       });
//     } else {
//       alert('Gagal cek voucher.');
//     }
//     setLoading(false);
//     __isSubmitting = false;
//     return;
//   }


const payMethodRaw =
  (frm.querySelector('[name="metode_bayar"]')?.value ||
   frm.querySelector('[name="payment_method"]')?.value ||
   frm.querySelector('[name="pay_method"]')?.value || '').toLowerCase().trim();

const isNonCash = (payMethodRaw && payMethodRaw !== 'cash');

let voucherPreview = null;
let estimasi = (subtotal > 0)
  ? `${fmtRp(subtotal)} (${labelTxt})`
  : '— (pilih jam agar masuk window)';

// === jika ada voucher: precheck ke server supaya tahu jenisnya ===
if (voucherCode) {
  try {
    const mejaId = sel ? String(sel.value || '') : '';
    const mejaKategori = (sel?.dataset?.kategori || '').toLowerCase().trim();

    voucherPreview = await previewVoucher({
      voucher: voucherCode,
      no_hp: noHpVal,
      tanggal: tanggalIso.value,
      meja_id: mejaId,                // ✅ FIX: bukan element
      jam_mulai: (jamMulai.value || ''),
      durasi_jam: durInt,
      subtotal: String(subtotal || 0),
      metode_bayar: payMethodRaw
    });

    if (!voucherPreview || !voucherPreview.success) {
      const msg = (voucherPreview && voucherPreview.pesan)
        ? voucherPreview.pesan
        : 'Voucher tidak valid.';
      if (window.Swal) await Swal.fire({ title: 'Voucher Invalid', html: msg, icon: 'error' });
      else alert(msg);

      setLoading(false);
      __isSubmitting = false;
      return;
    }

    const v = voucherPreview.data || {};
    const jenis = String(v.jenis || '').toUpperCase();

    // ✅ FREE_MAIN: hanya REGULER (double safety, walau server juga cek)
    if (jenis === 'FREE_MAIN' && mejaKategori && mejaKategori !== 'reguler') {
      if (window.Swal) {
        await Swal.fire({
          title: 'Voucher Tidak Berlaku',
          text: 'Voucher FREE_MAIN hanya bisa dipakai di meja REGULER.',
          icon: 'warning'
        });
      } else {
        alert('Voucher FREE_MAIN hanya bisa dipakai di meja REGULER.');
      }
      setLoading(false);
      __isSubmitting = false;
      return;
    }

    if (jenis === 'FREE_MAIN') {
      estimasi = 'Rp 0 (FREE_MAIN valid)';
    } else {
      // NOMINAL / PERSEN
      const after = parseInt(v.subtotal_after ?? v.grand_total ?? subtotal, 10) || 0;
      const disc  = parseInt(v.discount ?? 0, 10) || 0;

      estimasi = isNonCash
        ? `${fmtRp(after)} (setelah diskon ${fmtRp(disc)}) + kode unik (transfer/QRIS)`
        : `${fmtRp(after)} (setelah diskon ${fmtRp(disc)})`;
    }

  } catch (err) {
    if (window.Swal) {
      await Swal.fire({
        title: 'Gagal cek voucher',
        text: 'Tidak bisa verifikasi voucher. Coba lagi ya.',
        icon: 'error'
      });
    } else {
      alert('Gagal cek voucher.');
    }
    setLoading(false);
    __isSubmitting = false;
    return;
  }
}

// ✅ kalau tidak ada voucher, biarkan estimasi default (subtotal)
// if (voucherCode && voucherPreview && voucherPreview.success) {
//   const v = voucherPreview.data || {};
//   const jenis = String(v.jenis || '').toUpperCase();

//   if (jenis === 'FREE_MAIN') {
//     estimasi = 'Rp 0 (FREE_MAIN valid)';
//   } else {
//     const after = parseInt(v.subtotal_after ?? v.grand_total ?? subtotal, 10) || 0;
//     const disc  = parseInt(v.discount ?? 0, 10) || 0;

//     estimasi = isNonCash
//       ? `${fmtRp(after)} (setelah diskon ${fmtRp(disc)}) + kode unik (transfer/QRIS)`
//       : `${fmtRp(after)} (setelah diskon ${fmtRp(disc)})`;
//   }
// }


  const html = `
    <div style="text-align:left;">
      <p><b>Nama</b>: ${htmlEscape(namaVal)}</p>
      <p><b>HP (WA)</b>: ${htmlEscape(noHpVal)}</p>
      <p><b>${htmlEscape(mejaName)}</b></p>
      <p><b>Tanggal</b>: ${htmlEscape(tglLabel)}</p>
      ${(overNY2 && overNY2.ok) ? `<p><span class="badge badge-danger">Tarif khusus Malam Tahun Baru<br>VIP: Rp250.000/jam</span></p>` : ''}

      ${voucherCode ? `<p><b>Voucher</b>: <span class="badge badge-success">${htmlEscape(voucherCode)}</span></p>` : ''}

${(voucherCode && voucherPreview && voucherPreview.success && String(voucherPreview.data?.jenis||'').toUpperCase()==='FREE_MAIN')
  ? `<p><b>Durasi voucher</b>: <b>${parseInt(voucherPreview.data?.jam_voucher||1,10)} jam</b> (durasi akan dikunci otomatis)</p>`
  : ''}

${(voucherCode && voucherPreview && voucherPreview.success && ['NOMINAL','PERSEN'].includes(String(voucherPreview.data?.jenis||'').toUpperCase()))
  ? `<p><b>Diskon</b>: <b>${fmtRp(voucherPreview.data?.discount||0)}</b></p>`
  : ''}

      <p><b>Estimasi bayar</b>: <span style="font-weight:800">${estimasi}</span></p>
    </div>
  `;

  let proceed = true;
  if (window.Swal) {
    const conf = await Swal.fire({
      title: voucherCode ? 'Pakai voucher, ya?' : 'Yakin booking nih?',
      html,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: voucherCode ? 'Gas, pakai voucher' : 'Yoi, Booking!',
      cancelButtonText: 'Nanti dulu',
      reverseButtons: true,
      focusCancel: true,
      width: '560px'
    });
    proceed = conf.isConfirmed;
  } else {
    proceed = confirm('Yakin booking?');
  }

  if (!proceed) {
    // user batal → lepas kunci
    setLoading(false);
    __isSubmitting = false;

    if (window.Swal) {
      await Swal.fire({
        title: 'Santuy 😎',
        text: 'Booking nggak jadi — kamu bisa ubah dulu.',
        icon: 'info',
        timer: 1200,
        showConfirmButton: false
      });
    }
    return;
  }

  // Lanjut benar-benar submit ke server
  // (JANGAN setLoading(false) di sini, biar tetap disable)
  await doPost();
});

})();
</script>


<!-- ====== INPUT TIME: ganti '.' jadi ':' ====== -->
<script>
document.addEventListener('DOMContentLoaded', function(){
  const jamMulai = document.getElementById('jam_mulai');
  if (!jamMulai) return;
  // jamMulai.addEventListener('input', function(){
  //   if (this.value && this.value.indexOf('.') !== -1) {
  //     this.value = this.value.replace(/\./g, ':');
  //   }
  // });
});
</script>

<!-- ====== HTML Escape helper ====== -->
<script>
// function htmlEscape(str) {
//   if (!str) return '';
//   return String(str).replace(/[&<>"'`=\/]/g, function (s) {
//     return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#x60;','=':'&#x3D;'})[s];
//   });
// }
</script>
<script>
document.addEventListener('DOMContentLoaded', function(){
  var radios = document.querySelectorAll('input[name="meja_id"]');
  if (radios.length && !Array.from(radios).some(r => r.checked)) {
    radios[0].checked = true;
  }
});
</script>
<script>
function getFreeHours(){
  var m = (window.AUSI_CFG && window.AUSI_CFG.PAY_LIMIT_MIN)
            ? Number(window.AUSI_CFG.PAY_LIMIT_MIN)
            : 60;
  if (isNaN(m) || m <= 0) m = 60;
  return Math.max(1, Math.ceil(m/60));
}

</script>

<script>
function slotErrorFor(r, startHM, hours, dateObj){
   const cfg = getCfgFor(r, dateObj);
  if(!cfg || !startHM) return null;

  const h = clampHours(hours);
  if (!h) return 'Pilih durasi dulu ya.';

  const durMin = h * 60;
  const s = toMin(startHM), e = s + durMin;
  const endHM = fromMin(e % (24*60));

  const dwin = windowToSpan(cfg.day), nwin = windowToSpan(cfg.night);

  // apakah slot full di salah satu window (sudah valid)?
  function inside(win){
    if (win.e <= 1440) return (s>=win.s && e<=win.e);
    // overnight: izinkan pemetaan ke hari+1
    if (s>=win.s && e<=win.e) return true;
    const s2=s+1440,e2=e+1440; return (s2>=win.s && e2<=win.e);
  }
  if (inside(dwin) || inside(nwin)) return null;

  // apakah mulai di dalam window tapi selesai melewati batas window?
  function startIn(win){
    if (win.e <= 1440){
      if (s>=win.s && s<win.e) return {sBase:s, endBound:win.e};
      return null;
    } else {
      if (s>=win.s && s<win.e) return {sBase:s, endBound:win.e};
      const s2=s+1440; if (s2>=win.s && s2<win.e) return {sBase:s2, endBound:win.e};
      return null;
    }
  }

  const inDay   = startIn(dwin);
  const inNight = startIn(nwin);

  if (inDay && inDay.sBase + durMin > dwin.e){
  const maxDur = Math.max(0, dwin.e - inDay.sBase);
  return `Anda ingin main ${hours} jam mulai ${startHM} (selesai ${endHM}). `
       + `Slot ini melewati batas ${cfg.day.label} ${cfg.day.start}–${cfg.day.end}. `
       + `Dari ${startHM} maksimal hanya ${fmtDur(maxDur)} (sampai ${cfg.day.end}). `
       + `Kurangi durasi atau pindah ke window ${cfg.night.start}–${cfg.night.end}.`;
}
if (inNight && inNight.sBase + durMin > nwin.e){
  const maxDur = Math.max(0, nwin.e - inNight.sBase);
  return `Anda ingin main ${hours} jam mulai ${startHM} (selesai ${endHM}). `
       + `Slot ini melewati batas ${cfg.night.label} ${cfg.night.start}–${cfg.night.end}. `
       + `Dari ${startHM} maksimal hanya ${fmtDur(maxDur)} (sampai ${cfg.night.end}). `
       + `Kurangi durasi atau pindah ke window ${cfg.day.start}–${cfg.day.end}.`;
}


  // fallback: benar-benar di luar kedua window
  return `Jam ${startHM} selama ${hours} jam berada di luar jendela: `
       + `${cfg.day.start}–${cfg.day.end} atau ${cfg.night.start}–${cfg.night.end}.`;
}
</script>
<script>
// ==== RULE TAMBAHAN: jika mulai di "Day" lalu melewati batas ke "Night",
// terima sebagai VALID dan seluruh durasi dihitung pakai tarif "Day".
// ==== RULE TAMBAHAN: jika mulai di "Day" lalu melewati batas ke "Night",
// terima sebagai VALID dan seluruh durasi dihitung pakai tarif "Day".
function slotDayCrossOK(r, startHM, hours, dateObj){
  const cfg = getCfgFor(r, dateObj);
  if (!cfg || !startHM) return { ok:false };

  const h = clampHours(hours);
  if (!h) return { ok:false };

  const durMin = h * 60;  const s = toMin(startHM);                 // menit dari 00:00
  const dwin = windowToSpan(cfg.day);       // {s,e} dengan e bisa >1440 (overnight)
  const nwin = windowToSpan(cfg.night);     // {s,e}

  // Map titik waktu t ke "garis waktu" yang sejajar dengan window win
  function mapToBase(t, win){
    // Jika window overnight (e>1440) dan t < win.s, geser +24h
    return (win.e > 1440 && t < win.s) ? t + 1440 : t;
  }

  // Start harus berada di window Day (dalam basis Day)
  const sBase = mapToBase(s, dwin);
  if (!(sBase >= dwin.s && sBase < dwin.e)) return { ok:false };

  const eBase = sBase + durMin;             // ujung slot pada basis Day

  // Benar-benar menyeberang batas akhir Day?
  if (eBase <= dwin.e) return { ok:false };

  // Ujung slot harus berada di Night window (pakai basis Night)
  function inNight(t){
    const t1 = mapToBase(t, nwin);
    // untuk jaga-jaga kalau masih bisa lewat +24h
    const t2 = t1 + 1440;
    // return (t1 > nwin.s && t1 <= nwin.e) || (t2 > nwin.s && t2 <= nwin.e);
    return (t1 >= nwin.s && t1 <= nwin.e) || (t2 >= nwin.s && t2 <= nwin.e);

  }
  if (!inNight(eBase)) return { ok:false };

  // Lolos: seluruh durasi dihitung dengan tarif Day
  return {
    ok: true,
    rate: cfg.day.rate,
    label: 'Lintas Day→Night (tarif Day)',
    startHM: startHM,
    endHM: fromMin((s + durMin) % (24*60))
  };
}

// document.addEventListener('DOMContentLoaded', function(){
//   const v = document.getElementById('voucher');
//   if (!v) return;
//   v.addEventListener('input', function(){
//     this.value = sanitizeVoucherCode(this.value);
//   });
// });

</script>
