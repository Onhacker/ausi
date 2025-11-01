<?php
  $showPijatImage = ($this->uri->segment(1) === 'pijat');
?>

<style>
.massage-wrap{
  position:relative;
  overflow:hidden;
  background:linear-gradient(135deg,#f8f5f2 0%,#fff 60%);
  border-radius:16px;
  border:1px solid rgba(0,0,0,.05);
  padding:20px;
  box-shadow:0 10px 30px rgba(0,0,0,.07);
}

/* overlay bg kursi pijat transparan (hanya kalau BUKAN /pijat) */
.massage-bg{
  position:absolute;
  right:-10px;
  width:60%;
  max-width:300px;
  opacity:.15;
  pointer-events:none;
  z-index:0;
}
.massage-bg img{
  width:100%;
  height:auto;
  border-radius:12px;
  display:block;
}

/* GRID UTAMA */
.massage-grid{
  position:relative;
  z-index:1;
  display:grid;
  grid-template-columns:1fr;
  grid-template-areas:
    "left"
    "img"
    "detail";
  row-gap:1rem;
  column-gap:1rem;
}

/* Desktop: kiri+kanan lalu detail full lebar bawah */
@media (min-width:768px){
  .massage-wrap{padding:28px 32px;}
  .massage-grid{
    grid-template-columns:1fr 260px;
    grid-template-areas:
      "left img"
      "detail detail";
    column-gap:1.5rem;
    row-gap:1.5rem;
    align-items:flex-start;
  }
}

/* BLOK KIRI (judul, harga, manfaat) */
.left-block{
  grid-area:left;
  color:#000;
  max-width:520px;
  font-size:.95rem;
  line-height:1.5;
  text-align:justify;
}
.massage-heading{
  font-size:2rem;
  font-weight:700;
  line-height:1.15;
  color:#000;
  text-transform:uppercase;
  margin:0 0 .5rem 0;
  text-align:left;
}
.massage-heading span{
  color:#9a6a38;
  display:block;
}
@media (min-width:768px){
  .massage-heading{font-size:2.3rem;}
}

.massage-desc{
  font-size:.95rem;
  line-height:1.5;
  color:#000;
  margin-bottom:1rem;
  text-align:justify;
  max-width:360px;
}

.massage-price{
  display:inline-block;
  background:#4a2f14;
  color:#fff;
  border-radius:4px;
  padding:.75rem 1rem;
  font-size:1rem;
  font-weight:600;
  line-height:1.4;
  margin-bottom:1.25rem;
  box-shadow:0 4px 10px rgba(0,0,0,.2);
}
.massage-price small{
  display:block;
  font-size:.7rem;
  font-weight:700;
  letter-spacing:.03em;
}
.massage-price .harga{
  font-size:1.4rem;
  font-weight:700;
}

.massage-benefit-title{
  font-size:1.1rem;
  font-weight:700;
  color:#4a2f14;
  margin-bottom:.75rem;
  text-align:left;
}
.massage-benefit-list{
  margin:0;
  padding:0;
  list-style:none;
  max-width:420px;
}
.massage-benefit-list li{
  background:#9a6a38;
  color:#fff;
  font-size:.9rem;
  font-weight:600;
  line-height:1.4;
  border-radius:6px;
  padding:.6rem .75rem;
  margin-bottom:.5rem;
  box-shadow:0 4px 10px rgba(0,0,0,.08);
  border:1px solid rgba(0,0,0,.15);
  text-align:left;
}

/* CTA button "Detail" (hanya kalau BUKAN /pijat) */
.massage-cta{
  margin-top:1rem;
}
.massage-btn{
  appearance:none;
  border:0;
  background:#4a2f14;
  color:#fff;
  font-weight:600;
  border-radius:6px;
  padding:.6rem 1rem;
  font-size:.9rem;
  line-height:1.2;
  display:inline-flex;
  align-items:center;
  gap:.5rem;
  box-shadow:0 4px 10px rgba(0,0,0,.2);
  cursor:pointer;
}
.massage-btn[disabled]{
  opacity:.6;
  cursor:not-allowed;
}

/* spinner kecil */
.spinner{
  width:16px;
  height:16px;
  border:2px solid rgba(255,255,255,.4);
  border-top-color:#fff;
  border-radius:50%;
  animation:spin .6s linear infinite;
  display:none;
}
.massage-btn.loading .spinner{display:inline-block;}
.massage-btn.loading .btn-text{display:none;}
@keyframes spin{to{transform:rotate(360deg);}}

/* BLOK GAMBAR (tampil hanya di /pijat) */
.img-block{
  grid-area:img;
  text-align:center;
}
.img-block img{
  max-width:260px;
  width:100%;
  height:auto;
  border-radius:12px;
  display:inline-block;
}

/* DESKRIPSI LENGKAP (full width bawah, hanya di /pijat) */
.detail-block{
  grid-area:detail;
  font-size:.9rem;
  line-height:1.5;
  color:#000;
  text-align:justify;
}
.detail-block p{
  margin-bottom:.8rem;
  text-align:justify;
}
.detail-block .bonus-title{
  margin-top:1rem;
  font-weight:600;
  color:#4a2f14;
  text-align:left;
}
.detail-block ul{
  padding-left:1rem;
  margin:0;
  margin-top:.5rem;
  font-size:.9rem;
  line-height:1.4;
  text-align:justify;
}
.detail-block li{
  margin-bottom:.4rem;
  text-align:justify;
}
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
          20 RIBU
          <span style="font-size:.8rem;font-weight:500;">/ 15 menit</span>
        </div>
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
          Cukup duduk manis 15 menit, biarkan kursinya kerja: pijat punggung buat
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
          <li>Cuma 20 ribu, tidak perlu booking</li>
        </ul>

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
