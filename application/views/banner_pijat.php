<?php
  $showPijatImage = ($this->uri->segment(1) === 'pijat');

  // Ambil pengaturan kursi pijat dari tabel kursi_pijat_setting
  $kp_setting = $this->db->get_where('kursi_pijat_setting', ['id' => 1])->row();

  $kp_harga  = $kp_setting && isset($kp_setting->harga_satuan)        ? (int)$kp_setting->harga_satuan        : 20000;
  $kp_durasi = $kp_setting && isset($kp_setting->durasi_unit)         ? (int)$kp_setting->durasi_unit         : 15;
  $kp_free   = $kp_setting && isset($kp_setting->free_main_threshold) ? (int)$kp_setting->free_main_threshold : 0;

  $hargaLabel = number_format($kp_harga, 0, ',', '.'); // contoh: 20.000
?>

<style>
.massage-wrap{position:relative;overflow:hidden;background:linear-gradient(135deg,#f8f5f2 0,#fff 60%);border-radius:16px;border:1px solid rgba(0,0,0,.05);padding:20px;box-shadow:0 10px 30px rgba(0,0,0,.07)}.massage-btn,.massage-price{background:#4a2f14;box-shadow:0 4px 10px rgba(0,0,0,.2)}.massage-bg{position:absolute;right:-10px;width:60%;max-width:300px;opacity:.15;pointer-events:none;z-index:0}.massage-bg img{width:100%;height:auto;border-radius:12px;display:block}.massage-grid{position:relative;z-index:1;display:grid;grid-template-columns:1fr;grid-template-areas:"left" "img" "detail";row-gap:1rem;column-gap:1rem}.left-block{grid-area:left;color:#000;max-width:520px;font-size:.95rem;line-height:1.5;text-align:justify}.massage-heading{font-size:2rem;font-weight:700;line-height:1.15;color:#000;text-transform:uppercase;margin:0 0 .5rem;text-align:left}.massage-heading span{color:#9a6a38;display:block}@media (min-width:768px){.massage-wrap{padding:28px 32px}.massage-grid{grid-template-columns:1fr 260px;grid-template-areas:"left img" "detail detail";column-gap:1.5rem;row-gap:1.5rem;align-items:flex-start}.massage-heading{font-size:2.3rem}}.massage-desc{font-size:.95rem;line-height:1.5;color:#000;margin-bottom:1rem;text-align:justify;max-width:360px}.massage-price{position:relative;display:inline-block;color:#fff;border-radius:8px;padding:1.5rem 1.1rem .75rem;font-size:1rem;font-weight:600;line-height:1.4;margin-bottom:1.4rem;overflow:visible}.massage-price small{display:block;font-size:.7rem;font-weight:700;letter-spacing:.03em}.img-block img,.massage-btn.loading .spinner{display:inline-block}.massage-price .harga{font-size:1.4rem;font-weight:700}.massage-price .harga span{font-size:.8rem;font-weight:500}.massage-price .free-info{position:absolute;top:-12px;right:-10px;background:linear-gradient(135deg,#ffd54f,#ffb300);color:#4a2f14;font-size:.7rem;font-weight:800;padding:.3rem .8rem;border-radius:999px;text-transform:uppercase;letter-spacing:.08em;box-shadow:0 4px 10px rgba(0,0,0,.35);display:inline-flex;align-items:center;gap:.25rem;white-space:nowrap}.massage-price .free-info::before{content:'★';font-size:.8rem}.massage-price .free-info::after{content:'';position:absolute;bottom:-4px;right:14px;border-width:4px 4px 0;border-style:solid;border-color:#e0a800 transparent transparent;opacity:.9}/.massage-benefit-title{font-size:1.1rem;font-weight:700;color:#4a2f14;margin-bottom:.75rem;text-align:left}.massage-benefit-list li,.massage-btn{color:#fff;font-weight:600;font-size:.9rem}.massage-benefit-list{margin:0;padding:0;list-style:none;max-width:420px}.massage-benefit-list li{background:#9a6a38;line-height:1.4;border-radius:6px;padding:.6rem .75rem;margin-bottom:.5rem;box-shadow:0 4px 10px rgba(0,0,0,.08);border:1px solid rgba(0,0,0,.15);text-align:left}.massage-cta{margin-top:1rem}.massage-btn{appearance:none;border:0;border-radius:6px;padding:.6rem 1rem;line-height:1.2;display:inline-flex;align-items:center;gap:.5rem;cursor:pointer}.massage-btn[disabled]{opacity:.6;cursor:not-allowed}.spinner{width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:.6s linear infinite spin;display:none}.massage-btn.loading .btn-text{display:none}@keyframes spin{to{transform:rotate(360deg)}}.img-block{grid-area:img;text-align:center}.img-block img{max-width:260px;width:100%;height:auto;border-radius:12px}.detail-block{grid-area:detail;font-size:.9rem;line-height:1.5;color:#000;text-align:justify}.detail-block p{margin-bottom:.8rem;text-align:justify}.detail-block .bonus-title{margin-top:1rem;font-weight:600;color:#4a2f14;text-align:left}.detail-block ul{padding-left:1rem;margin:.5rem 0 0;font-size:.9rem;line-height:1.4;text-align:justify}.detail-block li{margin-bottom:.4rem;text-align:justify}
</style>


<div class="massage-wrap mb-2">

  <?php if (!$showPijatImage): ?>
    <!-- overlay background kursi pijat transparan (BUKAN /pijat) -->
    <div class="massage-bg">
      <img src="<?= base_url('assets/images/pijat.webp'); ?>" alt="">
    </div>
  <?php endif; ?>

  <div class="massage-grid">

    <!-- BLOK KIRI -->
    <div class="left-block">
      <h2 class="massage-heading">
        KURSI PIJAT
        <span>ELEKTRIK</span>
      </h2>

      <div class="massage-desc">
        Capek kerja, begadang, atau kelamaan main? Recharge badanmu langsung di AUSI Billiard &amp; Cafe dengan kursi pijat elektrik full-body — rileks dari leher sampai kaki.
      </div>

      <div class="massage-price">
        <small>HANYA</small>
        <div class="harga">
          Rp <?= $hargaLabel; ?>
          <span>/ <?= $kp_durasi; ?> menit</span>
        </div>

        <?php if ($kp_free > 0): ?>
          <div class="free-info">
            GRATIS 1 sesi setiap <?= $kp_free; ?>× main*
          </div>
        <?php endif; ?>
      </div>

      <div class="massage-benefit-title">Manfaat</div>
      <ul class="massage-benefit-list">
        <li>Bikin badan rileks dan nyaman</li>
        <li>Menghilangkan pegal dan capek</li>
        <li>Membuat badan fresh</li>
        <li>Serasa di pijatan spa</li>
      </ul>

      <?php if (!$showPijatImage): ?>
        <!-- tombol "Detail" hanya di halaman selain /pijat -->
        <div class="massage-cta">
          <button
            class="massage-btn"
            type="button"
            data-go-pijat="1">
            <span class="btn-text">Detail</span>
            <span class="spinner"></span>
          </button>
        </div>
      <?php endif; ?>
    </div>

    <?php if ($showPijatImage): ?>
      <!-- BLOK KANAN (gambar kursi) -->
      <div class="img-block">
        <img src="<?= base_url('assets/images/pijat.webp'); ?>" alt="Kursi pijat elektrik">
      </div>

      <!-- BLOK DETAIL FULL WIDTH DI BAWAH (span kiri+kanan) -->
      <div class="detail-block">
        <p>
          Capek habis kerja, begadang, atau kelamaan main? Sekarang kamu bisa langsung
          recharge badan tanpa perlu keluar jauh. Di
          <strong><?= htmlspecialchars($rec->nama_website ?? 'tempat kami', ENT_QUOTES, 'UTF-8'); ?></strong>
          sudah tersedia kursi pijat elektrik full-body dengan teknologi roller, airbag,
          dan pijat getar yang menyasar leher, bahu, punggung, pinggang, sampai kaki.
        </p>

        <p>
          Cukup duduk manis <?= $kp_durasi; ?> menit, biarkan kursinya kerja: pijat punggung buat
          ngurangin pegal, kompres tekanan lembut di kaki buat ngilangin capek berdiri lama,
          plus gerakan relaks buat nurunin tegang di bahu &amp; leher. Rasanya nyaman banget —
          mirip treatment spa cepat versi santai.
        </p>

        <p style="font-weight:600;">
          Nikmati dan rasakan sendiri efeknya: badan lebih enteng, kepala lebih ringan,
          dan mood balik enak. Cocok banget sebelum nongkrong lagi, sebelum main lagi,
          atau sambil nunggu giliran meja billiard.
        </p>

        <div class="bonus-title">
          Bonus kelebihan:
        </div>
        <ul>
          <li>Pijat otomatis dari leher sampai telapak kaki</li>
          <li>Bisa bantu melancarkan aliran darah &amp; merilekskan otot tegang</li>
          <li>Bikin tidur nanti malam lebih nyenyak</li>
          <li>Cuma Rp <?= $hargaLabel; ?>, tidak perlu booking</li>
        </ul>

        <?php if ($kp_free > 0): ?>
          <div class="bonus-title">
            Program GRATIS:
          </div>
          <ul>
            <li>
              Setiap <strong><?= $kp_free; ?>× main</strong> dengan nomor HP yang sama,
              kamu dapat <strong>1× sesi kursi pijat GRATIS</strong>. Tinggal klaim ke kasir saat mau dipakai.
            </li>
          </ul>
        <?php endif; ?>

        <p style="margin-top:1rem;">
          Tinggal bilang ke kasir / kru kami, dan duduk. Sisanya biar kursinya yang kerja 😌
        </p>
      </div>
    <?php endif; ?>

  </div><!-- /.massage-grid -->
</div><!-- /.massage-wrap -->

<script>
(function(){
  var btn = document.querySelector('.massage-btn[data-go-pijat]');
  if(!btn) return;

  btn.addEventListener('click', function(){
    // tampilkan loader
    btn.classList.add('loading');
    btn.setAttribute('disabled','disabled');

    // redirect ke halaman pijat
    window.location.href = "<?= site_url('pijat'); ?>";
  });
})();
</script>
