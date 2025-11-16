<?php
/** application/views/points_view.php */
defined('BASEPATH') OR exit('No direct script access allowed');

$CI =& get_instance();
$CI->load->helper('url');

/** ===== Helpers kecil di view ===== */
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function idr($n){ return 'Rp '.number_format((int)$n, 0, ',', '.'); }
function norm_phone($s){
  $s = preg_replace('/\D+/', '', (string)$s);
  if ($s === '') return '';
  if (strpos($s, '62') === 0) return $s;
  if (strpos($s, '0') === 0)  return '62'.substr($s,1);
  return $s;
}
function mask_msisdn($s){
  $digits = preg_replace('/\D+/', '', (string)$s);
  if ($digits === '') return '—';
  $len = strlen($digits);
  $keepStart = (strpos($digits,'62')===0) ? 2 : 1;
  $keepStart = min($keepStart, $len);
  $keepEnd = min(4, max(2, (int)floor($len/3)));
  $keepEnd = min($keepEnd, max(0, $len - $keepStart));
  $prefix  = substr($digits, 0, $keepStart);
  $suffix  = substr($digits, -$keepEnd);
  $midLen  = max(0, $len - $keepStart - $keepEnd);
  return $prefix . str_repeat('•', $midLen) . $suffix;
}

/** ===== Ambil token/phone dari URL ===== */
$token   = trim((string)($CI->uri->segment(3) ?: $CI->input->get('token', true) ?: ''));
$phoneQ  = trim((string)($CI->input->get('phone', true) ?: ''));
$msisdn  = norm_phone($phoneQ);
$vc      = null;

if ($token !== ''){
  $vc = $CI->db->get_where('voucher_cafe', ['token'=>$token])->row();
} elseif ($msisdn !== ''){
  $vc = $CI->db->get_where('voucher_cafe', ['customer_phone'=>$msisdn])->row();
}

/** ===== Derivatif tampilan (siklus mingguan, reset Minggu 00:00 WITA) ===== */
$hasData     = (bool)$vc;
$shareLink   = $hasData && !empty($vc->token) ? site_url('produk/points/'.rawurlencode($vc->token)) : '';
$shareLinkQ  = $hasData && !empty($vc->customer_phone) ? site_url('produk/points?phone='.rawurlencode($vc->customer_phone)) : '';

$tz   = new DateTimeZone('Asia/Makassar'); // WITA
$now  = new DateTime('now', $tz);

/**
 * Pekan: Minggu 00:00 – Sabtu 23:59:59
 * Reset poin: Minggu 00:00
 * Pengumuman reward: Minggu 08:00
 */
$w0        = (int)$now->format('w'); // 0=Sun..6=Sat
$weekStart = (clone $now)->modify('-'.$w0.' days')->setTime(0,0,0);      // Minggu 00:00 pekan ini
$nextSun00 = (clone $weekStart)->modify('+7 days')->setTime(0,0,0);      // Minggu depan 00:00

// resetAt: dari DB (expired_at = tanggal reset Minggu), fallback ke Minggu depan 00:00
if ($hasData && !empty($vc->expired_at)) {
  $resetAt = (new DateTime($vc->expired_at, $tz))->setTime(0,0,0);       // Minggu 00:00
} else {
  $resetAt = $nextSun00;
}

// announcementAt: hari yang sama, jam 08:00 WITA
$announceAt    = (clone $resetAt)->setTime(8, 0, 0);                      // Minggu 08:00
$announceAtIso = $announceAt->format('c');

$isExpired = ($now >= $announceAt);

// Hitung sisa waktu sampai pengumuman Minggu 08:00 (teks awal / fallback)
if ($isExpired) {
  $daysLeftStr = 'Sudah kedaluwarsa';
} else {
  $diff  = $now->diff($announceAt);
  $parts = [];

  if ($diff->d > 0) {
    $parts[] = $diff->d.' hari';
  }
  if ($diff->h > 0 || $diff->d > 0) {
    $parts[] = $diff->h.' jam';
  }
  if ($diff->i > 0 || (!$diff->d && !$diff->h)) {
    $parts[] = $diff->i.' menit';
  }
  if (empty($parts)) {
    $parts[] = $diff->s.' detik';
  }

  $daysLeftStr = implode(' ', $parts).' lagi';
}

// Label-label tampilan
$next1Label     = $resetAt->format('d/m/Y'); // ini tetap tanggal reset pekan
$weekRangeLabel = $weekStart->format('d/m/Y').' – '.(clone $weekStart)->modify('+6 days')->format('d/m/Y');

// Aman untuk judul
$custName = $hasData ? ($vc->customer_name ?: '—') : '—';
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
  .no-gutters>.col, .no-gutters>[class*="col-"] {
    padding-right: 0;
    padding-left: 16px !important;
  }

  /* Countdown di badge */
  .points-countdown-pill{
    display:inline-flex;
    align-items:center;
    gap:4px;
  }
  .points-countdown-label{
    opacity:.9;
  }

  /* Baris identitas + pengundian */
  .points-hero-bottom{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:12px;
    margin-top:12px;
    flex-wrap:wrap;
  }
  .points-identitas{
    min-width:180px;
    text-align:left;
  }
  .points-reward-row{
    flex:1 1 200px;
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:.5rem;
    flex-wrap:wrap;
    text-align:left;
  }
  .points-reward-text{
    flex:1 1 160px;
    font-size:.8rem;
    color:#e5e7eb;
  }
  .points-reward-btn{
    flex:0 0 auto;
    white-space:nowrap;
  }
  @media (max-width:575.98px){
    .points-reward-row{
      flex-direction:row;
      align-items:flex-start;
    }
    .points-reward-text{
      flex:1 1 100%;
    }
    .points-reward-btn{
      margin-left:auto;
      margin-top:4px;
    }
  }
</style>

<div class="container-fluid">
  <div class="hero-title ausi-hero-center" role="banner" aria-label="Judul halaman">
    <?php $this->load->view("front_end/back") ?>
    <h1 class="text mb-0">POIN <?php echo e($custName); ?></h1>
    <span class="accent ausi-accent" aria-hidden="true"></span>
  </div>

  <div class="row">
    <div class="col-12 col-lg-10 col-xl-8 mx-auto">

      <!-- HERO -->
      <div class="points-hero mt-2">
        <!-- Baris atas: poin + countdown -->
        <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:12px">
          <div>
            <div class="points-label">Poin Loyalty</div>
            <div class="points-value">
              <?php echo $hasData ? number_format((int)$vc->points,0,',','.') : '0'; ?>
            </div>
            <?php if ($hasData): ?>
              <div class="mt-2">
                <?php if ($isExpired): ?>
                  <span class="badge-soft badge-danger-soft d-block mb-1">
                    Periode pekan ini telah berakhir. Menunggu pengundian berikutnya.
                  </span>
                <?php else: ?>
                  <span class="badge-soft badge-ok-soft mr-2 d-block mb-1">
                    Reset pekan: <?php echo e($next1Label); ?> (00:00 WITA)
                  </span>
                  <span class="badge-soft mt-1 d-inline-block">
                    <span class="points-countdown-pill">
                      <span class="points-countdown-label">Pengundian poin dalam</span>
                      <span id="points-countdown"
                            data-target="<?php echo e($announceAtIso); ?>">
                        <?php echo e($daysLeftStr); ?>
                      </span>
                    </span>
                  </span>
                <?php endif; ?>
                <div class="mt-1 small text-light">
                  Periode pekan ini: <?php echo e($weekRangeLabel); ?> (WITA)
                </div>
              </div>
            <?php else: ?>
              <div class="mt-2"><span class="badge-soft">Belum ada data poin</span></div>
            <?php endif; ?>
          </div>
        </div>

        <?php if ($hasData): ?>
        <!-- Baris bawah: Atas nama + HP di kiri, teks & tombol di kanan -->
        <div class="points-hero-bottom">
          <div class="points-identitas">
            <div class="small mb-1">Atas nama :</div>
            <div class="h5 mb-1 text-white" style="font-weight:800">
              <?php echo e($custName); ?>
            </div>
            <div class="small text-light">
              HP: <?php echo e(mask_msisdn($vc->customer_phone ?? '')); ?>
            </div>
          </div>

          <div class="points-reward-row">
            <div class="points-reward-text">
              Pengundian poin akan dilaksanakan setiap
              <strong>Minggu, pukul 08:00 WITA</strong>
              berdasarkan rekap poin pekan ini.
            </div>
            <a href="<?php echo site_url('produk/reward'); ?>"
               class="btn btn-sm btn-outline-light points-reward-btn">
              Cek selengkapnya di sini
            </a>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- STATS -->
      <?php if ($hasData): ?>
        <div class="stats-card mt-3 mb-3">
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
              <p class="stat-k">TRX Awal Pekan ini</p>
              <p class="stat-v"><?php echo $vc->first_paid_at ? e(date('d/m/Y', strtotime($vc->first_paid_at))) : '—'; ?></p>
            </div>
            <div class="col-6 col-md-3 stat-item">
              <p class="stat-k">TRX Terakhir Pekan ini</p>
              <p class="stat-v"><?php echo $vc->last_paid_at ? e(date('d/m/Y', strtotime($vc->last_paid_at))) : '—'; ?></p>
            </div>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<script>
/* ===== Live countdown ke pengundian poin (Minggu 08:00 WITA) ===== */
(function(){
  const el = document.getElementById('points-countdown');
  if (!el) return;

  const targetStr = el.getAttribute('data-target') || '';
  if (!targetStr) return;

  const target = new Date(targetStr);
  if (isNaN(target.getTime())) return;

  function tick(){
    const now  = new Date();
    let diff   = target - now;

    if (diff <= 0){
      el.textContent = 'sebentar lagi…';
      return;
    }

    let totalSec = Math.floor(diff / 1000);
    const days   = Math.floor(totalSec / 86400);
    totalSec    -= days * 86400;
    const hours  = Math.floor(totalSec / 3600);
    totalSec    -= hours * 3600;
    const mins   = Math.floor(totalSec / 60);
    const secs   = totalSec - mins * 60;

    const parts = [];
    if (days > 0) {
      parts.push(days + ' hari');
    }
    if (hours > 0 || days > 0) {
      parts.push(hours + ' jam');
    }
    if (mins > 0 || days > 0 || hours > 0) {
      parts.push(mins + ' menit');
    }
    // selalu tampilkan detik supaya kelihatan live
    parts.push(secs + ' detik');

    el.textContent = parts.join(' ');
  }

  tick();
  setInterval(tick, 1000);
})();

/* ===== Copy helper (token / link poin) ===== */
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

<?php $this->load->view("front_end/footer.php"); ?>
