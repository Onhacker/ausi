<?php $this->load->view("front_end/head.php") ?>

<?php
  // ===== PHP helpers =====
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
[$pay_text, $pay_icon] = $pay_map[$pay_raw] ?? ['-', 'mdi-credit-card-outline'];

$tgl_disp = '';
if (!empty($booking->tanggal)) {
  $ts = @strtotime($booking->tanggal);
  if ($ts !== false && $ts !== -1) $tgl_disp = date('d/m/Y', $ts);
}

  // untuk tombol batal di summary-card
$hide_cancel = in_array($st_raw, ['terkonfirmasi','verifikasi'], true);
?>

<style>
  /* ================== SUMMARY CARD (scoped) ================== */
  .summary-card{
    /* theme tokens */
    --sc-bg:#fff; --sc-ink:#0f172a; --sc-muted:#64748b; --sc-border:#e5e7eb;
    background:var(--sc-bg); color:var(--sc-ink);
    border:1px solid var(--sc-border); border-radius:16px;
    box-shadow:0 8px 24px rgba(2,6,23,.06); position:relative; overflow:hidden;
  }
  [data-bs-theme="dark"] .summary-card{
    --sc-bg:#0b1220; --sc-ink:#e5e7eb; --sc-muted:#94a3b8; --sc-border:#1e293b;
  }
  .summary-card .card-body{padding:1.25rem 1.25rem}
  @media(min-width:992px){.summary-card .card-body{padding:1.5rem 1.75rem}}
  .summary-card::before{content:"";position:absolute;inset:0 0 auto 0;height:4px;
  background:linear-gradient(90deg,#0b61ff,#8b5cf6,#06b6d4)
}
.summary-card hr{border-top:1px dashed var(--sc-border);opacity:1}

/* helpers */
.summary-card .minw-0{min-width:0}
.summary-card .label-muted{color:var(--sc-muted)}
.summary-card .money-lg{font-weight:800;font-size:1.5rem}
.summary-card .money-sm{font-weight:700;font-size:1.05rem} /* Subtotal kecil */
@media(max-width:575.98px){.summary-card .money-lg{font-size:1.35rem}}

/* GRAND TOTAL hitam */
.summary-card #grandtotal{color:var(--sc-ink); background:none}

/* buttons pay spacing */
.summary-card .pay-actions{display:flex;flex-wrap:wrap;column-gap:16px;row-gap:14px;margin:.25rem 0 1.1rem}
.summary-card .pay-actions .btn.js-pay{padding:.56rem 1rem;border-radius:999px;min-width:150px}
@media(min-width:992px){.summary-card .pay-actions{column-gap:18px;row-gap:14px}
.summary-card .pay-actions .btn.js-pay{min-width:170px}}
@media(max-width:575.98px){.summary-card .pay-actions .btn.js-pay{flex:1 1 160px}}

/* ===== LEFT: identitas ===== */
.summary-card .ident-line{display:flex;align-items:flex-start;gap:16px}
.summary-card .avatar-bubble{
  width:44px;height:44px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;
  background:linear-gradient(180deg, rgba(11,97,255,.12), rgba(11,97,255,.05));
  color:#0b61ff; box-shadow:inset 0 6px 20px rgba(2,6,23,.06)
}
.summary-card .avatar-bubble .mdi{font-size:22px;line-height:1}
.summary-card .id-stack{display:flex;flex-direction:column;gap:.7rem}
.summary-card .field-label{color:var(--sc-muted);font-size:.85rem;line-height:1;margin:0 0 .25rem 0}
.summary-card .field-value.name{font-weight:700}

/* Kode booking chip */
.summary-card #copyBooking.code-chip{
  display:inline-flex;align-items:center;gap:.5rem;padding:.46rem .75rem;border-radius:14px;
  cursor:pointer;user-select:none;background:#1f2937!important;border:0!important;
  color:#fff;font-weight:800;letter-spacing:.6px;font-variant-numeric:tabular-nums;
  font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;
  box-shadow:0 6px 14px rgba(2,6,23,.16)
}
.summary-card #copyBooking.code-chip .mdi{font-size:1rem;opacity:.9}
.summary-card #copyBooking.code-chip:hover{transform:translateY(-1px);box-shadow:0 10px 22px rgba(2,6,23,.20)}

/* No HP chip */
.summary-card .phone-chip{
  display:inline-flex;align-items:center;gap:.25rem;padding:.32rem .6rem;border-radius:999px;
  border:1px solid var(--sc-border);background:#f8fafc;color:var(--sc-ink);max-width:100%
}
.summary-card .phone-chip .text-truncate{max-width:14rem}
[data-bs-theme="dark"] .summary-card .phone-chip{background:#0f172a;border-color:#1e293b;color:#e5e7eb}
@media(max-width:575.98px){
  .summary-card .ident-line{gap:18px}
  .summary-card .avatar-bubble{width:38px;height:38px;border-radius:10px}
  .summary-card #copyBooking.code-chip{padding:.42rem .68rem}
  .summary-card .phone-chip{font-size:.9rem;padding:.28rem .55rem}
  .summary-card .phone-chip .text-truncate{max-width:70vw}
}

/* ===== CENTER: meja & waktu ===== */
.summary-card .slot-id{
  font-size:.78rem;line-height:1;padding:.18rem .5rem;border-radius:999px;
  background:#f8fafc;color:var(--sc-ink);border:1px solid var(--sc-border);flex:0 0 auto
}
.summary-card .slot-chip{
  display:inline-flex;align-items:center;gap:.35rem;padding:.38rem .65rem;border-radius:999px;
  border:1px solid var(--sc-border);background:#fff;color:var(--sc-ink);font-size:.92rem;white-space:nowrap
}
.summary-card .slot-chip i{opacity:.85;line-height:1}
[data-bs-theme="dark"] .summary-card .slot-id{background:#0f172a;border-color:#1e293b;color:#e5e7eb}
[data-bs-theme="dark"] .summary-card .slot-chip{background:#0b1220;border-color:#1e293b;color:#e5e7eb}
@media(max-width:575.98px){.summary-card .slot-id{font-size:.75rem;padding:.16rem .45rem}
.summary-card .slot-chip{font-size:.86rem;padding:.32rem .55rem}
.summary-card .slot-chip time{font-variant-numeric:tabular-nums}}

/* badge light fix */
.summary-card .badge.bg-light{background:#f8fafc!important;color:inherit;border:1px solid var(--sc-border)!important}
[data-bs-theme="dark"] .summary-card .badge.bg-light{background:#0f172a!important}
</style>

<div class="container-fluid">
  <div class="hero-title">
    <h1 class="text">Konfirmasi Booking</h1>
    <div class="text-muted">Lakukan pembayaran .....</div>
    <span class="accent" aria-hidden="true"></span>
  </div>

  <!-- ===================== SUMMARY CARD ===================== -->
  <div class="row">
    <div class="col-12">
      <div class="card-box summary-card mb-3">
        <div class="card-body">
          <div class="row gx-4 gy-3 align-items-start">

            <!-- LEFT (mobile first): Identitas -->
            <div class="col-12 col-lg-4 order-1">
              <div class="ident-line">
                <span class="avatar-bubble" aria-hidden="true">
                  <i class="mdi mdi-billiards"></i>
                </span>

                <div class="id-stack w-100 minw-0">
                  <!-- Kode Booking -->
                  <div>
                    <div class="field-label">Kode Booking</div>
                    <span id="copyBooking" class="badge bg-dark code-chip" title="Klik untuk menyalin">
                      <?= html_escape($booking->kode_booking ?? '') ?>
                      <i class="mdi mdi-content-copy" aria-hidden="true"></i>
                    </span>
                  </div>

                  <!-- Nama -->
                  <div>
                    <div class="field-label">Nama</div>
                    <div class="field-value name text-truncate">
                      <?= html_escape($booking->nama ?? '') ?>
                    </div>
                  </div>

                  <!-- No HP -->
                  <div>
                    <div class="field-label">No HP</div>
                    <span class="phone-chip">
                      <i class="mdi mdi-phone-outline me-1" aria-hidden="true"></i>
                      <span class="text-truncate d-inline-block"><?= html_escape($booking->no_hp ?? '') ?></span>
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- CENTER: Meja & Waktu -->
            <div class="col-12 col-lg-4 order-2">
              <div class="d-flex align-items-start">
                <i class="mdi mdi-table-chair text-primary me-2 fs-5" aria-hidden="true"></i>
                <div class="w-100 minw-0">
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="fw-semibold text-truncate mt-2"
                    title="<?= html_escape($booking->nama_meja ?? $meja->nama_meja ?? 'MEJA #' . ($booking->meja_id ?? $meja->id_meja ?? '')) ?>">
                    <?= html_escape($booking->nama_meja ?? $meja->nama_meja ?? 'MEJA #' . ($booking->meja_id ?? $meja->id_meja ?? '')) ?>
                  </div>
                  <span class="slot-id mt-2 ml-1">ID <?= (int)($booking->meja_id ?? $meja->id_meja ?? 0) ?></span>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-2">
                  <span class="slot-chip">
                    <i class="mdi mdi-calendar-outline me-1" aria-hidden="true"></i>
                    <?php
                    $tanggal_val = $booking->tanggal ?? '';
                    $tanggal_display = $tgl_disp ?: ($tanggal_val ? date('d/m/Y', strtotime($tanggal_val)) : '-');
                    ?>
                    <time datetime="<?= html_escape($tanggal_val) ?>"><?= html_escape($tanggal_display) ?></time>
                  </span>

                  <span class="slot-chip mt-1">
                    <i class="mdi mdi-clock-outline me-1" aria-hidden="true"></i>
                    <?php
                    $jam_mulai   = $booking->jam_mulai   ?? '00:00:00';
                    $jam_selesai = $booking->jam_selesai ?? '00:00:00';
                    ?>
                    <strong id="rangetime" class="me-1">
                      <time datetime="<?= html_escape($jam_mulai) ?>"><?= html_escape(substr($jam_mulai,0,5)) ?></time>
                      &ndash;
                      <time datetime="<?= html_escape($jam_selesai) ?>"><?= html_escape(substr($jam_selesai,0,5)) ?></time>
                    </strong>
                    <span class="text-muted">· <span id="durtext"><?= (int)($booking->durasi_jam ?? 0) ?></span> jam</span>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- RIGHT: Total, status, metode bayar & batalkan -->
          <div class="col-12 col-lg-4 order-3 text-lg-end mt-3">
            <div class="label-dark">Subtotal</div>
            <div id="subtotal" class="money-sm">Rp <?= number_format((int)($booking->subtotal ?? 0),0,',','.') ?></div>

            <div class="mt-1">
              Tarif / jam:
              <span class="badge bg-light text-dark border">
                Rp<?= number_format((int)($booking->harga_per_jam ?? $meja->harga_per_jam ?? 0),0,',','.') ?>
              </span>
            </div>
            <div class="small mt-1">Durasi: <strong><?= (int)($booking->durasi_jam ?? 0) ?></strong> jam</div>

            <?php if ($kode_unik > 0):

             ?>
             <div class="mt-2">
              <span class="label-dark">Kode Unik</span>
              <div id="kodeunik" class="fw-semibold">+ Rp<?= number_format($kode_unik,0,',','.') ?></div>
            </div>

          <?php endif; ?>

          <div class="mt-2 label-muted small">Grand Total</div>
          <div id="grandtotal" class="money-lg">Rp<?= number_format($grand_total,0,',','.') ?></div>
          <!-- letakkan di kolom kanan (TOTAL), mis. tepat sebelum/sesudah Grand Total -->
          <input type="hidden" id="kode_unik_val" value="<?= (int)$kode_unik ?>">

          <div class="mt-2">
            <span class="badge bg-<?= $status_variant ?>">
              <i class="mdi <?= $status_icon ?>"></i> <?= $status_text ?>
            </span>
          </div>

          <?php if ($is_terkonfirmasi): ?>
            <div class="mt-3">
              <div class="label-muted small mb-1">Metode Pembayaran</div>
              <div class="d-inline-flex align-items-center gap-2 border rounded-pill px-3 py-1">
                <i class="mdi <?= $pay_icon ?>"></i>
                <span class="fw-semibold"><?= html_escape($pay_text) ?></span>
              </div>
            </div>

            <div class="mt-3 no-shot">
              <button id="btn-shot-booking" class="btn btn-blue btn-sm w-100" type="button">
                <i class="mdi mdi-camera"></i> Screenshot Bukti
              </button>
              <div class="text-muted small mt-1">Jangan di-close dulu ya—simpan bukti ini ✨</div>
            </div>
          <?php endif; ?>

          <?php
          $allow_statuses = ['draft'];
          $show_pay_buttons = isset($booking)
          && !empty($booking->access_token)
          && isset($booking->status)
          && in_array(strtolower((string)$booking->status), $allow_statuses, true);
          ?>
          <?php if ($show_pay_buttons && $this->session->userdata("admin_username") <> "kasir"): ?>
            <hr class="my-1">
            <?php
            $late_min = (int)($rec->late_min ?? 15);
            $updated_raw = $booking->updated_at ?? 'now';
            $updated_ts  = is_numeric($updated_raw) ? (int)$updated_raw : (strtotime((string)$updated_raw) ?: time());
            $deadline_ts = $updated_ts + ($late_min * 60);
            ?>
            <div id="pay-deadline"
            class="text-dark alert alert-info"
            data-deadline-ms="<?= $deadline_ts * 1000 ?>">
            Pilih salah satu metode di bawah ya.
            <span class="d-block d-lg-inline">
              Pembayaran harus selesai sebelum <strong><?= date('H:i', $deadline_ts) ?> WITA</strong><br>
              Waktu pembayaran sisa (<span id="countdown">--:--</span>).

            </span>
          </div>
          <script>
(function(){
  const root = document.getElementById('pay-deadline');
  const cd   = document.getElementById('countdown');
  if (!root || !cd) return;

  const pad = n => n<10 ? '0'+n : n;
  const fmt = ms => {
    let s = Math.max(0, Math.floor(ms/1000));
    const h = Math.floor(s/3600); s%=3600;
    const m = Math.floor(s/60);   s%=60;
    return h>0 ? `${pad(h)}:${pad(m)}:${pad(s)}` : `${pad(m)}:${pad(s)}`;
  };

  function tick(){
    // baca DEADLINE TERBARU dari dataset (ms)
    const dl = Number(root.dataset.deadlineMs || 0);
    const diff = dl - Date.now();
    cd.textContent = fmt(diff);
    if (diff <= 0) clearInterval(t);
  }

  const t = setInterval(tick, 1000);
  tick();
})();
</script>

<div class="pay-actions">
  <a class="btn btn-primary btn-sm js-pay"
  href="<?= site_url('billiard/pay_qris/'.rawurlencode($booking->access_token)) ?>"
  data-method="qris">
  <i class="mdi mdi-qrcode-scan"></i> QRIS
</a>
<a class="btn btn-info btn-sm js-pay"
href="<?= site_url('billiard/pay_transfer/'.rawurlencode($booking->access_token)) ?>"
data-method="transfer">
<i class="mdi mdi-bank-transfer"></i> TRANSFER
</a>
</div>
<?php endif; ?>

<?php if (!$hide_cancel): ?>
  <button class="btn btn-outline-secondary w-100 mt-2" id="btnBatal" type="button">
    <i class="mdi mdi-close"></i> Batalkan
  </button>
<?php endif; ?>
</div>

</div><!-- /row -->
</div>
</div>
</div>
</div>
<!-- =================== /SUMMARY CARD =================== -->

<!-- ======================= EDIT FORM ======================= -->
<div class="row">
  <div class="col-12">
    <?php if ($is_draft && !empty($edit_allowed)): ?>
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
                    <input type="time" name="jam_mulai" id="jam_mulai"
                    value="<?= substr($booking->jam_mulai ?? '00:00:00',0,5) ?>"
                    class="form-control" step="300" required aria-describedby="jam-info">
                    <small id="jam-info" class="text-danger"></small>
                  </div>
                  <div class="col-6">
                    <label class="form-label">Lama (jam)</label>
                    <input type="number" name="durasi_jam" id="durasi"
                    value="<?= (int)($booking->durasi_jam ?? 1) ?>"
                    min="1" max="12" class="form-control" required>
                  </div>

                  <div class="col-12">
                    <small id="infoHitung" class="text-muted d-block"></small>
                  </div>

                  <!-- hidden data untuk JS -->
                  <input type="hidden" id="open"  value="<?= html_escape(substr($meja->jam_buka ?? '00:00:00',0,5)) ?>">
                  <input type="hidden" id="close" value="<?= html_escape(substr($meja->jam_tutup ?? '23:59:00',0,5)) ?>">
                  <input type="hidden" id="price" value="<?= (int)($booking->harga_per_jam ?? $meja->harga_per_jam ?? 0) ?>">
                  <input type="hidden" id="booking_start" value="<?= html_escape(substr($booking->jam_mulai ?? ($meja->jam_buka ?? '00:00:00'),0,5)) ?>">
                  <input type="hidden" id="tolerance" value="<?= (int)($this->fm->web_me()->tolerance_minutes ?? 15) ?>">

                  <div class="col-12">
                    <div id="smallCollisionNotice" class="alert alert-warning py-2" style="display:none;">
                      Perhatian: ada geser kecil <strong><span id="collisionMinutes">0</span> menit</strong> terhadap booking lain (masih dalam toleransi).
                    </div>
                  </div>

                  <div class="col-12">
                    <button id="btnUbah" class="btn btn-primary w-100" type="submit">Ubah</button>
                  </div>
                </div>
              </form>

              <!-- Tombol Batalkan sudah dipindah ke summary-card -->
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
<!-- ===================== /EDIT FORM ===================== -->
</div>

<!-- Dependensi JS & Footer -->
<script src="<?php echo base_url('assets/admin') ?>/js/vendor.min.js"></script>
<script src="<?php echo base_url('assets/admin') ?>/js/app.min.js"></script>
<script src="<?php echo base_url('assets/admin') ?>/js/sw.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

<?php $this->load->view("front_end/footer.php") ?>

<script>
// ===== helper & kalkulasi =====
const safeGet = id => document.getElementById(id)||null;
const pad = n => (n<10?'0':'')+n;
const toMin = s => { if(!s) return null; const [h,m] = s.split(':').map(Number); return h*60+m; };
const fromMin = n => `${pad(Math.floor((n/60)%24))}:${pad(n%60)}`;
const rpStr = n => (n||0).toLocaleString('id-ID');

(function(){
  const jamMulai=safeGet('jam_mulai'); const durasi=document.getElementById('durasi');
  const info=document.getElementById('infoHitung'); const jamInfo=document.getElementById('jam-info');
  if(!jamMulai||!durasi) return;

  const open=(document.getElementById('open')?.value)||'00:00';
  const close=(document.getElementById('close')?.value)||'23:59';
  const price=parseInt((document.getElementById('price')?.value)||'0',10);
  const bookingStart=(document.getElementById('booking_start')?.value)||'00:00';
  const tolerance=parseInt((document.getElementById('tolerance')?.value)||'15',10);

  function normalizeClose(a,b){return (b<=a)?b+1440:b}
  function refresh(){
    jamMulai.setCustomValidity('');
    if(jamInfo) jamInfo.textContent='';
    if(info) info.textContent='';

    let d=parseInt(durasi.value||'1',10); if(isNaN(d)||d<1)d=1; if(d>12)d=12;
    const openMin=toMin(open), closeMinRaw=toMin(close), closeMin=normalizeClose(openMin,closeMinRaw);

    let tolLatest=toMin(bookingStart)+tolerance; tolLatest=Math.max(openMin,tolLatest);
    const latestByClose=closeMin-d*60; const latestStartMin=Math.min(latestByClose,tolLatest);
    const latestStart=fromMin(latestStartMin);

    jamMulai.min=open; jamMulai.max=fromMin(latestStartMin);
    const displayClose=(closeMinRaw<=openMin)?(close+' (next day)'):close;

    if(info) info.textContent=`Buka ${open}–${displayClose}. Durasi ${d} jam (mulai paling lambat: ${latestStart}).`+(price>0?` Estimasi: Rp${rpStr(price*d)}.`:'');

    if(jamMulai.value){
      let chosen=toMin(jamMulai.value); if(chosen<openMin&&closeMinRaw<=openMin) chosen+=1440;
      const endMin=chosen+d*60;
      if(endMin>closeMin){
        const msg='Durasi melewati jam tutup. Majukan jam mulai atau kurangi durasi.';
        jamMulai.setCustomValidity(msg); if(jamInfo) jamInfo.textContent=msg;
      }
      const endDisplay=fromMin(Math.min(endMin,closeMin)); const r=document.getElementById('rangetime'); if(r) r.textContent=`${jamMulai.value}–${endDisplay}`;
    }
    const durtext=document.getElementById('durtext'); if(durtext) durtext.textContent=d;

    const subtotalEl=document.getElementById('subtotal'); if(subtotalEl) subtotalEl.textContent=`Rp${rpStr(price*d)}`;
    const parseMoney = s => Number((s||'').toString().replace(/[^\d]/g,'')||0);
    const unikEl = document.getElementById('kode_unik_val');
    const unikVal = unikEl ? parseInt(unikEl.value||'0',10)
    : parseMoney(document.getElementById('kodeunik')?.textContent);

    const grandEl=document.getElementById('grandtotal'); if(grandEl) grandEl.textContent=`Rp${rpStr(price*d+unikVal)}`;
  }
  ['input','change'].forEach(ev=>{durasi.addEventListener(ev,refresh); jamMulai.addEventListener(ev,refresh)});
  document.addEventListener('DOMContentLoaded',refresh);
})();

// ===== Submit Ubah (AJAX + Swal) =====
(function(){
  const frm=document.getElementById('frmUpd'); if(!frm) return;
  frm.addEventListener('submit',async e=>{
    e.preventDefault();
    const btn=document.getElementById('btnUbah'); if(!btn||btn.disabled) return;
    const original=btn.innerHTML; btn.disabled=true; btn.innerHTML='Memproses…';
    try{
      const fd=new FormData(e.target);
      const r=await fetch(e.target.action,{method:'POST',body:fd});
      const j=await r.json();

      if(j.collision&&j.collision.allowed&&j.collision.minutes){
        const box=document.getElementById('smallCollisionNotice'); const minsEl=document.getElementById('collisionMinutes');
        if(box&&minsEl){minsEl.textContent=j.collision.minutes; box.style.display='block'; setTimeout(()=>box.style.display='none',8000);}
      }
      if(window.Swal){await Swal.fire({title:j.title||(j.success?'Berhasil':'Gagal'),html:j.pesan||'',icon:j.success?'success':'error'});}
      else{alert((j.title?j.title+'\n':'')+(j.pesan?j.pesan.replace(/<br>/g,'\n'):''));}
      if(j.success&&j.redirect_url) location.href=j.redirect_url;
    }catch(err){
      if(window.Swal) Swal.fire({title:'Error',text:'Koneksi ke server gagal.',icon:'error'});
      else alert('Koneksi ke server gagal.');
    }finally{
      const btn2=document.getElementById('btnUbah'); if(btn2){btn2.disabled=false; btn2.innerHTML=original;}
    }
  });
})();

// ===== Batal Booking (SweetAlert + fallback) =====
(function(){
  const btnBatal=document.getElementById('btnBatal'); if(!btnBatal) return;
  btnBatal.addEventListener('click',async ()=>{
    if(typeof Swal==='undefined'){
      const ok=confirm('Yakin mau batalin?'); if(!ok) return;
      const fd=new FormData(); fd.append('t','<?= $booking->access_token ?>');
      try{
        const r=await fetch('<?= site_url('billiard/batal') ?>',{method:'POST',body:fd});
        const j=await r.json(); alert((j.title?j.title+'\n':'')+(j.pesan?j.pesan.replace(/<br>/g,'\n'):'')); if(j.redirect_url) location.href=j.redirect_url;
      }catch(err){alert('Koneksi error.');}
      return;
    }
    const res=await Swal.fire({title:'Batal booking?',text:'Slot akan dibuka lagi. Lanjutkan?',icon:'warning',showCancelButton:true,confirmButtonText:'Iya, batalin',cancelButtonText:'Enggak',reverseButtons:true});
    if(!res.isConfirmed){return;}
    try{
      Swal.fire({title:'Proses...',allowOutsideClick:false,didOpen:()=>Swal.showLoading()});
      const fd=new FormData(); fd.append('t','<?= $booking->access_token ?>');
      const r=await fetch('<?= site_url('billiard/batal') ?>',{method:'POST',body:fd});
      const j=await r.json(); Swal.close();
      if(j.success){await Swal.fire({title:'Dibatalkan',html:(j.pesan||''),icon:'success'}); if(j.redirect_url) location.href=j.redirect_url;}
      else{await Swal.fire({title:(j.title||'Gagal'),html:(j.pesan||''),icon:'error'});}
    }catch(err){Swal.close(); Swal.fire({title:'Error',text:'Koneksi bermasalah',icon:'error'});}
  });
})();

// ===== Konfirmasi pilih metode bayar =====
(function(){
  document.addEventListener('click',function(e){
    const el=e.target.closest('.js-pay'); if(!el) return; e.preventDefault();
    const method=(el.dataset.method||'').toLowerCase(); const href=el.getAttribute('href')||'#';
    const labelMap={cash:'TUNAI',qris:'QRIS',transfer:'TRANSFER'};
    const noteMap={
      cash:'Bayarnya langsung di kasir ya. Gas?',
      qris:'Setelah lanjut, jangan tutup halaman sampai transaksi selesai.',
      transfer:'Nanti dapat nomor rekening untuk transfer. Oke?'
    };
    const title=`Pilih ${labelMap[method]||'metode ini'}?`;
    if(typeof Swal==='undefined'){ if(confirm(title+'\n\n'+(noteMap[method]||''))) window.location.href=href; return; }
    Swal.fire({title,html:`<div class="text-start">${noteMap[method]||''}</div>`,icon:'question',showCancelButton:true,confirmButtonText:'Lanjut',cancelButtonText:'Batal',reverseButtons:true})
    .then(res=>{ if(res.isConfirmed){Swal.fire({title:'Mengalihkan...',allowOutsideClick:false,didOpen:()=>Swal.showLoading()}); window.location.href=href; }});
  });
})();

// ===== Screenshot ringkasan =====
(function(){
  var btn=document.getElementById('btn-shot-booking'); if(!btn) return;
  btn.addEventListener('click',async function(){
    try{
      var node=document.querySelector('.summary-card'); if(!node){alert('Ringkasan tidak ketemu.'); return;}
      var scale=Math.max(2,Math.ceil(window.devicePixelRatio||1));
      var canvas=await html2canvas(node,{backgroundColor:'#ffffff',scale,ignoreElements:function(el){return el.classList&&el.classList.contains('no-shot')}});
      canvas.toBlob(function(blob){
        if(!blob){alert('Gagal bikin gambar.'); return;}
        var filename='bukti-booking-<?= (int)($booking->id_pesanan ?? 0) ?>.png';
        var file=new File([blob],filename,{type:'image/png'});
        if(navigator.canShare&&navigator.canShare({files:[file]})){navigator.share({files:[file],title:'Bukti Booking'}).catch(()=>{});return;}
        var url=URL.createObjectURL(blob); var a=document.createElement('a'); a.href=url; a.download=filename; document.body.appendChild(a); a.click(); a.remove(); setTimeout(()=>URL.revokeObjectURL(url),1200);
      },'image/png');
    }catch(err){alert('Ups, gagal screenshot.');}
  });
})();

// ===== Copy Kode Booking =====
(function(){
  const badge = document.getElementById('copyBooking');
  if(!badge) return;
  badge.addEventListener('click', async ()=>{
    try{
      await navigator.clipboard.writeText(badge.textContent.trim());
      if(window.Swal){Swal.fire({title:'Disalin!',timer:1100,icon:'success',showConfirmButton:false});}
    }catch(e){}
  });
})();
</script>
