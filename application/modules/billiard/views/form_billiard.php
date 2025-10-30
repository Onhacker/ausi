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
    <div class="text-white">Pilih Meja Billiard</div>
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

                <?php if ($m->catatan) : ?>
                  <div class="alert alert-success mb-2" role="alert">
                    <i class="mdi mdi-check-all mr-2"></i> <?= html_escape($m->catatan) ?>
                  </div>
                <?php endif; ?> -->
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
                <input type="text" id="nama" name="nama" class="form-control" placeholder="Nama lengkap" required>
              </div>

              <div class="col-md-6 mb-2">
                <label class="form-label" for="no_hp">Nomor HP (WA)</label>
                <input
                  type="text"
                  id="no_hp"
                  name="no_hp"
                  class="form-control"
                  placeholder="08xxxxxxxxxx"
                  inputmode="numeric"
                  minlength="10"
                  maxlength="13"
                  pattern="0\d{9,12}"
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
                <input type="number" name="durasi_jam" id="durasi" value="" min="1" max="12" class="form-control" required>
              </div>

              <div class="col-12 mb-2">
                <div id="infoHitung" class="small text-danger"></div>
              </div>

              <div class="col-md-4 mb-3">
                <label class="form-label" for="voucher">Voucher Main Gratis</label>
                <input type="text" name="voucher" id="voucher" class="form-control">
              </div>

              <div class="col-12 text-center">
                <button class="btn btn-blue px-4" id="btnSubmit" type="submit">Booking</button>
              </div>
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
  LATE_MIN: <?= (int)($rec->late_min ?? 60) ?> // dalam menit
};



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
  const d   = getSelectedDate() || window.__BOOKING_DATES?.today || new Date();

  jamMulai.setCustomValidity('');
  if (jamInfo)    jamInfo.textContent = '';
  if (infoHitung) infoHitung.textContent = '';

  // Netralisir constraint native agar tidak muncul "maksimal XX:XX"
  jamMulai.removeAttribute('max');
const pickedDate = getSelectedDate();
jamMulai.min = (pickedDate && sameYMD(pickedDate, new Date())) ? hmNow() : '00:00';


  if (!sel) return;

  const open  = sel.getAttribute('data-open');   // "HH:MM"
  const close = sel.getAttribute('data-close');  // "HH:MM"
  const overnight = (toMin(close) <= toMin(open));

  // durasi (jam)
  let dHours = parseInt((durasi.value || '').trim(), 10);
  dHours = isNaN(dHours) ? null : Math.max(1, Math.min(12, dHours));

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
const band = slotBandFor(sel, jamMulai.value, dHours, d);
if (!band){
  const err = slotErrorFor(sel, jamMulai.value||'', dHours, d);
  if (jamMulai) jamMulai.setCustomValidity(err||'');
  if (jamInfo)  jamInfo.textContent = err||'';
  if (infoHitung) infoHitung.textContent = err||'';
  return;
}


  // Estimasi harga
  const subtotal = (band.rate * dHours) || 0;
  if (infoHitung) {
    infoHitung.innerHTML =
      `<b>${band.label}</b> ${band.wStart}–${band.wEnd} ` +
      `(Rp${band.rate.toLocaleString('id-ID')}/jam) • ` +
      `Durasi ${dHours} jam → Estimasi: <b>Rp${subtotal.toLocaleString('id-ID')}</b>`;
  }
}



  document.querySelectorAll('input[name="meja_id"]').forEach(r=>r.addEventListener('change',refreshUI));
  ['input','change','blur'].forEach(ev=>{
    durasi.addEventListener(ev,refreshUI);
    jamMulai.addEventListener(ev,refreshUI);
  });
  tanggalView?.addEventListener('change',refreshUI);

  document.addEventListener('DOMContentLoaded',refreshUI);
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
  const cfg = getCfgFor(r, dateObj); if(!cfg || !startHM) return null;
  const dur = Math.max(1, Math.min(12, parseInt(hours||'1',10))) * 60;
  const start = toMin(startHM);
  const end   = start + dur;

  const dwin = windowToSpan(cfg.day);
  const nwin = windowToSpan(cfg.night);

  // cek span, termasuk bagian "lewat 24:00" utk overnight
  function inSpan(win){
    // non-overnight: e <= 1440
    if (win.e <= 24*60){
      return (start >= win.s && end <= win.e);
    }
    // overnight:
    // A) start di bagian sore (mis. 23:00)
    if (start >= win.s && end <= win.e) return true;
    // B) start di bagian dinihari (mis. 01:00) → geser +24h
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
    const minNow = hmNow();
    jamMulai.min = minNow;
    if (jamMulai.value && toMin(jamMulai.value) < toMin(minNow)){
      const msg = `Jam mulai tidak boleh di masa lalu (≥ ${minNow}).`;
      jamMulai.setCustomValidity(msg);
      if (jamInfo) jamInfo.textContent = msg;
      return false;
    }
  } else {
    jamMulai.min = '00:00';
  }

  if (!jamMulai.value) return true;

  const dHours = Math.max(1, Math.min(12, parseInt(durasi.value||'1', 10)));
  const errMsg = slotErrorFor(sel, jamMulai.value, dHours, dObj);

  if (errMsg){
    jamMulai.setCustomValidity(errMsg);
    if (jamInfo) jamInfo.textContent = errMsg;
    return false;
  }
  return true;
}



  ['input','change'].forEach(ev=>{
    if (jamMulai) jamMulai.addEventListener(ev, validateTimeRange);
    if (durasi)   durasi.addEventListener(ev, validateTimeRange);
  });
  document.querySelectorAll('input[name="meja_id"]').forEach(r=>{
    r.addEventListener('change', validateTimeRange);
  });

  // helper UI
  function htmlEscape(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"'`=\/]/g, function (s) {
      return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#x60;','=':'&#x3D;'})[s];
    });
  }
  function setLoading(state){
    if (!btn) return;
    if (state){
      btn.disabled = true;
      btn.__original = btn.__original || btn.innerHTML;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses…';
    } else {
      btn.disabled = false;
      if (btn.__original) btn.innerHTML = btn.__original;
    }
  }

  async function doPost(){
    setLoading(true);
    if (window.Swal) Swal.fire({ title: 'Nge-proses…', text: 'Sedang membuat booking, tunggu sebentar ya.', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
      const fd = new FormData(frm);
      const r  = await fetch(frm.action, { method:'POST', body: fd });
      const j  = await r.json();
      if (window.Swal) Swal.close();

      if (j.success) {
        const isFree = j.redirect_url && j.redirect_url.indexOf('/billiard/free') !== -1;
        if (window.Swal) {
          await Swal.fire({
            title: isFree ? 'Voucher diterima 🎉' : 'Berhasil!',
            html: isFree
            ? `Booking gratis aktif.<br>Durasi ditentukan sistem: <b>${getFreeHours()}</b> jam.`
            : (j.pesan || 'Booking berhasil dibuat.'),
            icon: 'success',
            confirmButtonText: 'Sip'
          });
        }
        if (j.redirect_url) setTimeout(() => { window.location.href = j.redirect_url; }, 250);
        return;
      }

      // === Tangani khusus error voucher ===
      const title = (j.title||'').toLowerCase();
      const pesan = (j.pesan||'');
      const voucherFilled = !!(voucherInp && voucherInp.value.trim() !== '');

      if (voucherFilled && (title.includes('voucher') || /voucher/i.test(pesan))) {
        const res = await Swal.fire({
          title: 'Kode vouchernya belum match 😅',
          html: pesan + '<br><br>Mau lanjut tanpa voucher aja?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Lanjut tanpa voucher',
          cancelButtonText: 'Cek/ubah kode'
        });

        if (res.isConfirmed) {
          // kosongkan voucher & submit ulang
          voucherInp.value = '';
          return doPost();
        } else {
          // biar user bisa koreksi
          voucherInp.focus();
          return;
        }
      }

      // default error (bukan voucher)
      if (window.Swal) await Swal.fire({ title: j.title || 'Gagal', html: j.pesan || 'Gagal membuat booking.', icon: 'error', confirmButtonText: 'Oke' });
      else alert(j.title || 'Gagal membuat booking');
    } catch (err) {
      if (window.Swal) { Swal.close(); await Swal.fire({ title: 'Waduh', text: 'Koneksi error. Coba lagi yaa.', icon: 'error', confirmButtonText: 'Oke' }); }
      else alert('Koneksi error. Coba lagi.');
      console.error(err);
    } finally {
      setLoading(false);
    }
  }

  frm.addEventListener('submit', async (e) => {
    e.preventDefault();

    // Validasi tanggal (termasuk batas MAX_DAYS)
    const { today, maxDateObj } = window.__BOOKING_DATES;

    if (!tanggalIso.value) {
      if (window.Swal) await Swal.fire({ title: 'Tanggal kosong', text: 'Pilih tanggal mainnya dulu ya.', icon: 'warning' });
      else alert('Tanggal kosong. Pilih tanggal dulu.');
      return;
    } else {
      const picked = new Date(tanggalIso.value + 'T00:00:00');
      if (picked < today) {
        if (window.Swal) await Swal.fire({ title: 'Tanggal lewat', text: 'Pilih tanggal yang belum lewat dong.', icon: 'warning' });
        else alert('Tanggal sudah lewat.');
        return;
      }
      if (picked > maxDateObj) {
        const ymdMax = window.__BOOKING_DATES.ymdMax;
        if (window.Swal) await Swal.fire({ title: 'Tanggal terlalu jauh', text: `Tanggal booking maksimal sampai ${ymdMax}.`, icon: 'warning' });
        else alert(`Tanggal maksimal: ${ymdMax}`);
        return;
      }
    }

    const timeOk = validateTimeRange();
    const allOk  = frm.checkValidity();
    if (!allOk || !timeOk) {
      const listHtml = buildInvalidList() || '<p>Periksa kembali isian kamu ya.</p>';
      if (window.Swal) {
        await Swal.fire({ title: 'Cek dulu', html: listHtml, icon: 'warning', confirmButtonText: 'Oke' });
      } else {
        alert('Periksa kembali isian kamu.');
      }
      const firstInvalid = frm.querySelector(':invalid') || (!timeOk ? jamMulai : null);
      if (firstInvalid) firstInvalid.focus();
      return;
    }

    // Ringkasan
    const sel       = getSelectedMeja();
    const mejaName = getMejaName(sel);
    const namaVal   = (frm.querySelector('#nama')  || {}).value || '';
    const noHpVal   = (frm.querySelector('#no_hp') || {}).value || '';
    const mejaLabel = getMejaLabel(sel);
    const tglLabel  = (document.getElementById('tanggal_view')||{}).value || tanggalIso.value || '—';
    const durVal    = (durasi.value || '1');
    const jamLabel  = (jamMulai.value || '00:00') + ' / Durasi: ' + durVal + ' jam';
    const bandObj   = slotBandFor(sel, (jamMulai.value||'00:00'), parseInt(durVal,10), (getSelectedDate()||new Date()));
const subtotal  = bandObj ? (bandObj.rate * parseInt(durVal,10)) : 0;

    const voucherCode = (voucherInp && voucherInp.value.trim()) ? voucherInp.value.trim().toUpperCase() : '';
    // const estimasi = voucherCode ? 'Rp 0 (kalau kode valid)' : ('Rp ' + subtotal.toLocaleString('id-ID'));
    const estimasi = voucherCode
  ? 'Rp 0 (kalau kode valid)'
  : (bandObj
      ? `Rp ${subtotal.toLocaleString('id-ID')} (${bandObj.label})`
      : '— (pilih jam agar masuk window)');


    const html = `
      <div style="text-align:left;">
        <p><b>Nama</b>: ${htmlEscape(namaVal)}</p>
        <p><b>HP (WA)</b>: ${htmlEscape(noHpVal)}</p>
        <p><b>${htmlEscape(mejaName)}</b></p>
        <p><b>Tanggal</b>: ${htmlEscape(tglLabel)}</p>
        ${voucherCode ? `<p><b>Voucher</b>: <span class="badge badge-success">${htmlEscape(voucherCode)}</span></p>` : ''}
        ${voucherCode ? `<p><b>Durasi voucher</b>: Ditentukan sistem: <b>${getFreeHours()}</b> jam</p>` : ''}

        <p><b>Estimasi bayar</b>: <span style="font-weight:800">${estimasi}</span></p>
      </div>
    `;

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
      if (!conf.isConfirmed) {
        await Swal.fire({ title: 'Santuy 😎', text: 'Booking nggak jadi — kamu bisa ubah dulu.', icon: 'info', timer: 1200, showConfirmButton: false });
        return;
      }
    } else if (!confirm('Yakin booking?')) {
      return;
    }

    // Submit
    await doPost();
  });
})();
</script>


<!-- ====== INPUT TIME: ganti '.' jadi ':' ====== -->
<script>
document.addEventListener('DOMContentLoaded', function(){
  const jamMulai = document.getElementById('jam_mulai');
  if (!jamMulai) return;
  jamMulai.addEventListener('input', function(){
    if (this.value && this.value.indexOf('.') !== -1) {
      this.value = this.value.replace(/\./g, ':');
    }
  });
});
</script>

<!-- ====== HTML Escape helper ====== -->
<script>
function htmlEscape(str) {
  if (!str) return '';
  return String(str).replace(/[&<>"'`=\/]/g, function (s) {
    return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#x60;','=':'&#x3D;'})[s];
  });
}
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
  var m = (window.AUSI_CFG && window.AUSI_CFG.LATE_MIN) ? Number(window.AUSI_CFG.LATE_MIN) : 60;
  if (isNaN(m) || m <= 0) m = 60;
  // dibulatkan ke atas biar nggak “kepotong” (misal 61 menit => 2 jam)
  return Math.max(1, Math.ceil(m/60));
}
</script>

<script>
function slotErrorFor(r, startHM, hours, dateObj){
  const cfg = getCfgFor(r, dateObj); if(!cfg || !startHM) return null;
  const durMin = Math.max(1, Math.min(12, parseInt(hours||'1',10))) * 60;
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
