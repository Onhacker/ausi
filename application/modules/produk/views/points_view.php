<?php
/** application/views/points_view.php */

$CI =& get_instance();
$CI->load->helper('url');

/** Helpers kecil di view */
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function idr($n){ return 'Rp '.number_format((int)$n, 0, ',', '.'); }
function norm_phone($s){
  $s = preg_replace('/\D+/', '', (string)$s);
  if ($s === '') return '';
  if (strpos($s, '62') === 0) return $s;
  if (strpos($s, '0') === 0)  return '62'.substr($s,1);
  return $s;
}

/** Ambil token dari segment-3 / ?token= / ?phone= */
$token   = trim((string)($CI->uri->segment(3) ?: $CI->input->get('token', true) ?: ''));
$phoneQ  = trim((string)($CI->input->get('phone', true) ?: ''));
$msisdn  = norm_phone($phoneQ);
$vc      = null;

if ($token !== ''){
  $vc = $CI->db->get_where('voucher_cafe', ['token'=>$token])->row();
} elseif ($msisdn !== ''){
  $vc = $CI->db->get_where('voucher_cafe', ['customer_phone'=>$msisdn])->row();
}

/** Derivatif tampilan */
/** Derivatif tampilan (siklus mingguan, reset Senin 00:00 WITA) */
/** Derivatif tampilan (siklus mingguan, reset Minggu 00:00 WITA) */
$hasData     = (bool)$vc;
$shareLink   = $hasData && !empty($vc->token) ? site_url('produk/points/'.rawurlencode($vc->token)) : '';
$shareLinkQ  = $hasData && !empty($vc->customer_phone) ? site_url('produk/points?phone='.rawurlencode($vc->customer_phone)) : '';

$tz   = new DateTimeZone('Asia/Makassar'); // WITA
$now  = new DateTime('now', $tz);

/**
 * Pekan: Minggu 00:00 – Sabtu 23:59:59
 * Reset: Minggu 00:00 pekan berikutnya
 */
$w0        = (int)$now->format('w'); // 0=Sun..6=Sat
$weekStart = (clone $now)->modify('-'.$w0.' days')->setTime(0,0,0); // Minggu 00:00 pekan ini
$nextSun   = (clone $weekStart)->modify('+7 days');                 // Minggu depan 00:00

// expiredAt prefer dari DB (diset backend ke Minggu 00:00), fallback ke $nextSun
$expiredAt   = $hasData && !empty($vc->expired_at) ? (string)$vc->expired_at : $nextSun->format('Y-m-d H:i:s');
$isExpired   = false;
$daysLeftStr = '';
$next1Label  = '';
$weekRangeLabel = $weekStart->format('d/m/Y').' – '.(clone $weekStart)->modify('+6 days')->format('d/m/Y');

try{
  $exp = new DateTime($expiredAt, $tz);
  $next1Label = $exp->format('d/m/Y');
  if ($now >= $exp){
    $isExpired   = true;
    $daysLeftStr = 'Sudah kedaluwarsa';
  } else {
    $daysLeftStr = $now->diff($exp)->days.' hari lagi';
  }
}catch(\Throwable $e){
  // fallback aman
  $isExpired   = false;
  $daysLeftStr = '';
  $next1Label  = $nextSun->format('d/m/Y');
}

// Simpan juga rentang pekan untuk UI (opsional)
$weekRangeLabel = $start->format('d/m/Y').' – '.(clone $start)->modify('+6 days')->format('d/m/Y');

$next1Label  = ''; // mis. "01/12/2025"

if ($expiredAt !== ''){
  try{
    $exp       = new DateTime($expiredAt);       // ini adalah tgl 1 bulan depan dari DB
    $today     = new DateTime('today');
    $next1Label= $exp->format('d/m/Y');          // seharusnya selalu "01/mm/YYYY"

    if ($exp <= $today){
      $isExpired   = true;
      $daysLeftStr = 'Sudah kedaluwarsa';
    } else {
      $daysLeftStr = $today->diff($exp)->days.' hari lagi';
    }
  } catch (\Throwable $e){}
} else {
  // fallback jika kolom kosong: hitung manual dari hari ini
  $exp   = new DateTime('first day of next month');
  $today = new DateTime('today');
  $next1Label = $exp->format('d/m/Y');
  $isExpired  = ($exp <= $today);
  $daysLeftStr= $isExpired ? 'Sudah kedaluwarsa' : $today->diff($exp)->days.' hari lagi';
}
function mask_msisdn($s){
  $digits = preg_replace('/\D+/', '', (string)$s);
  if ($digits === '') return '—';
  $len = strlen($digits);

  // kalau mulai "62", simpan 2 digit awal; selain itu simpan 1 digit awal
  $keepStart = (strpos($digits,'62')===0) ? 2 : 1;
  $keepStart = min($keepStart, $len);

  // simpan 3–4 digit akhir (minimal 2, maksimal 4, proporsional panjangnya)
  $keepEnd = min(4, max(2, (int)floor($len/3)));
  $keepEnd = min($keepEnd, max(0, $len - $keepStart));

  $prefix  = substr($digits, 0, $keepStart);
  $suffix  = substr($digits, -$keepEnd);
  $midLen  = max(0, $len - $keepStart - $keepEnd);
  $masked  = $prefix . str_repeat('•', $midLen) . $suffix;

  return $masked;
}

?>
<?php $this->load->view("front_end/head.php"); ?>

<style>
  .points-hero{
    border-radius: 14px;
    padding: 16px;
    background: linear-gradient(180deg, #0ea5e9 0%, #0369a1 100%);
    color:#fff;
    box-shadow: 0 10px 24px rgba(0,0,0,.08);
  }
  .points-value{
    font-size: 44px;
    font-weight: 800;
    line-height: 1;
    letter-spacing: .5px;
  }
  .points-label{
    opacity: .95;
    font-weight: 600;
    letter-spacing: .3px;
  }
  .stats-card{
    border:1px solid #e7eef7;
    border-radius: 12px;
    background:#fff;
  }
  .stat-item{ padding:14px; }
  .stat-k{ font-size:12px; color:#64748b; margin:0; }
  .stat-v{ font-size:18px; font-weight:700; margin:2px 0 0; }
  .badge-soft{
    display:inline-block; padding:6px 10px; border-radius: 999px; font-weight:600; font-size:12px;
    background:#f1f5f9; color:#0f172a; border:1px solid #e2e8f0;
  }
  .badge-danger-soft{ background:#fef2f2; color:#991b1b; border-color:#fecaca; }
  .badge-ok-soft{ background:#ecfdf5; color:#065f46; border-color:#a7f3d0; }
  .copy-btn{ white-space:nowrap; }
  .empty-state{
    border: 1px dashed #b9c3d6; background:#f8fbff; border-radius: 12px;
  }
  .empty-state .inner{ padding:16px; }
  .empty-state .emoji{ font-size:44px; line-height:1; margin-bottom:8px; }
  .mono{ font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
  .no-gutters {
    margin-right: 0;
    margin-left: 20px;
    }
</style>

<div class="container-fluid">
   <div class="hero-title ausi-hero-center" role="banner" aria-label="Judul halaman">
    <h1 class="text mb-0">POIN <?php echo e($vc->customer_name ?: '—'); ?></h1>
        <span class="accent ausi-accent" aria-hidden="true"></span>
  </div>

  <div class="row">
    <div class="col-12 col-lg-10 col-xl-8 mx-auto">

      <!-- HERO -->
      <div class="points-hero mt-2">
        <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:12px">
          <div>
            <div class="points-label">Poin Loyalty</div>
            <div class="points-value">
              <?php echo $hasData ? number_format((int)$vc->points,0,',','.') : '0'; ?>
            </div>
            <?php if ($hasData): ?>
  <div class="mt-2">
    <?php if ($isExpired): ?>
      <span class="badge-soft badge-danger-soft">Reset pekan: <?php echo e($next1Label); ?> (00:00 WITA)</span>
    <?php else: ?>
      <span class="badge-soft badge-ok-soft mr-2">Reset pekan: <?php echo e($next1Label); ?> (00:00 WITA)</span>
      <span class="badge-soft"><?php echo e($daysLeftStr); ?></span>
    <?php endif; ?>

    <!-- Rentang pekan -->
    <div class="mt-1 small text-light">
      Periode pekan ini: <?php echo e($weekRangeLabel); ?> (WITA)
    </div>
  </div>
<?php else: ?>
  <div class="mt-2"><span class="badge-soft">Belum ada data poin</span></div>
<?php endif; ?>

          </div>

          <div class="text-right">
            <?php if ($hasData): ?>
              <div class="small mb-1">Atas nama :</div>
              <div class="h5 mb-2 text-white" style="font-weight:800">
                <?php echo e($vc->customer_name ?: '—'); ?>
              </div>
              <div class="small text-light">HP: <?php echo e(mask_msisdn($vc->customer_phone ?? '')); ?></div>

            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- ACTIONS -->
  <!--     <div class="d-flex align-items-center flex-wrap mt-2" style="gap:8px">
        <?php if ($hasData): ?>
          <button class="btn btn-primary btn-sm copy-btn"
                  data-copy="<?php echo e($shareLink ?: $shareLinkQ); ?>">
            Salin Link Poin
          </button>
          <button class="btn btn-outline-secondary btn-sm copy-btn mono"
                  data-copy="<?php echo e($vc->token); ?>">
            Salin Token
          </button>
          <a class="btn btn-outline-primary btn-sm" href="<?php echo e(current_url()); ?><?php echo ($_SERVER['QUERY_STRING']?'?'.e($_SERVER['QUERY_STRING']):''); ?>">
            Refresh
          </a>
        <?php endif; ?>
      </div> -->

      <!-- STATS -->
      <?php if ($hasData): ?>
        <div class="stats-card mt-3">
          <div class="row no-gutters">
            <div class="col-6 col-md-3 stat-item">
              <p class="stat-k">Transaksi</p>
              <p class="stat-v"><?php echo number_format((int)$vc->transaksi_count,0,',','.'); ?> Kali</p>
            </div>
            <div class="col-6 col-md-3 stat-item">
              <p class="stat-k">Total Order</p>
              <p class="stat-v"><?php echo idr($vc->total_rupiah); ?></p>
            </div>
            <div class="col-6 col-md-3 stat-item">
              <p class="stat-k">Pertama Kali</p>
              <p class="stat-v"><?php echo $vc->first_paid_at ? e(date('d/m/Y', strtotime($vc->first_paid_at))) : '—'; ?></p>
            </div>
            <div class="col-6 col-md-3 stat-item">
              <p class="stat-k">Terakhir</p>
              <p class="stat-v"><?php echo $vc->last_paid_at ? e(date('d/m/Y', strtotime($vc->last_paid_at))) : '—'; ?></p>
            </div>
          </div>
        </div>

      <?php endif; ?>

      <div class="card mt-3 ">
        <div class="card-body">
         <h4 class="mb-2">Tingkatkan Poin & Raih Voucher Order Senilai Rp 50.000</h4>
         <p class="mb-2">
          Setiap transaksi <strong>berhasil</strong> langsung menambah poin Anda.
          <strong>Makin sering order, makin cepat poin terkumpul</strong>—ayo lanjutkan belanja di AUSI!
        </p>
        <p class="mb-2">
          Pengumuman <strong>voucher order</strong> dan rekap poin dilakukan
          <strong>setiap hari Minggu</strong> untuk periode <strong>pekan sebelumnya</strong>. Pastikan nomor WhatsApp aktif agar tidak ketinggalan info.
        </p>
        <p class="mb-0 text-muted small">
          Poin dihitung otomatis dari total belanja & komponen kode unik transaksi; periode mengikuti
          <strong>siklus mingguan</strong> (Minggu 00:00 – Sabtu 23:59 WITA, reset Minggu 00:00).
          <br>
          <a href="<?php echo site_url('hal/#voucher-order'); ?>" class="text-decoration-underline">
            Syarat &amp; Ketentuan berlaku
          </a>
        </p>

        </div>
      </div>



    </div>
  </div>
</div>

<script>
(function(){
  function copyText(t){
    if (!t) return false;
    if (navigator.clipboard && window.isSecureContext){
      return navigator.clipboard.writeText(t);
    }
    // fallback
    const ta = document.createElement('textarea');
    ta.value = t;
    ta.style.position = 'fixed';
    ta.style.left = '-9999px';
    document.body.appendChild(ta);
    ta.focus(); ta.select();
    try{ document.execCommand('copy'); }catch(e){}
    document.body.removeChild(ta);
    return Promise.resolve();
  }
  document.addEventListener('click', function(ev){
    const btn = ev.target.closest('.copy-btn');
    if (!btn) return;
    const txt = btn.getAttribute('data-copy') || '';
    copyText(txt).then(function(){
      btn.innerHTML = 'Tersalin!';
      setTimeout(()=>{ btn.innerHTML = btn.classList.contains('mono') ? 'Salin Token' : 'Salin Link Poin'; }, 1200);
    });
  });
})();
</script>

<?php $this->load->view("front_end/footer.php") ?>
