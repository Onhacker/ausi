<?php
  // Tampilkan layout detail penuh kalau lagi di halaman /ps
  $showPsImage = ($this->uri->segment(1) === 'ps4');

  // Ambil pengaturan PS dari tabel ps_setting
  $ps_setting = $this->db->get_where('ps_setting', ['id' => 1])->row();

  $ps_harga  = $ps_setting && isset($ps_setting->harga_satuan)        ? (int)$ps_setting->harga_satuan        : 15000;
  $ps_durasi = $ps_setting && isset($ps_setting->durasi_unit)         ? (int)$ps_setting->durasi_unit         : 60;
  $ps_free   = $ps_setting && isset($ps_setting->free_main_threshold) ? (int)$ps_setting->free_main_threshold : 0;

  $psHargaLabel = number_format($ps_harga, 0, ',', '.'); // contoh: 15.000
?>

<style>
.ps-wrap{
  position:relative;
  overflow:hidden;
  background: radial-gradient(circle at top, #447ad2 0, #041664 55%, #000 100%);
  border-radius:16px;
  border:1px solid rgba(148,163,184,.35);
  padding:20px;
  box-shadow:0 14px 40px rgba(15,23,42,.7);
  color:#e5e7eb;
}
.ps-bg{
  position:absolute;
  right:-30px;
  bottom:-40px;
  width:60%;
  max-width:340px;
  opacity:.18;
  pointer-events:none;
  z-index:0;
}
.ps-bg img{
  width:100%;
  height:auto;
  display:block;
  filter:drop-shadow(0 18px 30px rgba(15,23,42,.9));
}
.ps-grid{
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
.ps-left-block{
  grid-area:left;
  max-width:520px;
  font-size:.95rem;
  line-height:1.5;
  text-align:justify;
}
.ps-heading{
  font-size:2rem;
  font-weight:800;
  line-height:1.05;
  text-transform:uppercase;
  margin:0 0 .6rem;
  letter-spacing:.08em;
}
.ps-heading span{
  display:block;
  font-size:1.45rem;
  letter-spacing:.25em;
  color:#38bdf8;
}
.ps-desc{
  font-size:.95rem;
  line-height:1.6;
  color:#cbd5f5;
  margin-bottom:1.1rem;
  max-width:420px;
}
.ps-price{
  position:relative;
  display:inline-block;
  background:linear-gradient(135deg,#0f172a,#1e293b 45%,#020617 100%);
  border-radius:10px;
  padding:1.3rem 1.1rem .9rem;
  font-size:1rem;
  font-weight:600;
  line-height:1.4;
  margin-bottom:1.4rem;
  box-shadow:0 14px 30px rgba(15,23,42,.9);
  border:1px solid rgba(56,189,248,.45);
}
.ps-price small{
  display:block;
  font-size:.7rem;
  font-weight:700;
  letter-spacing:.14em;
  color:#94a3b8;
  text-transform:uppercase;
}
.ps-price .harga{
  font-size:1.5rem;
  font-weight:800;
  color:#e5e7eb;
}
.ps-price .harga span{
  font-size:.8rem;
  font-weight:500;
  color:#9ca3af;
}
.ps-price .free-info{
  position:absolute;
  top:-12px;
  right:-10px;
  background:linear-gradient(135deg,#22c55e,#16a34a);
  color:#022c22;
  font-size:.7rem;
  font-weight:800;
  padding:.3rem .85rem;
  border-radius:999px;
  text-transform:uppercase;
  letter-spacing:.08em;
  box-shadow:0 8px 18px rgba(22,163,74,.7);
  display:inline-flex;
  align-items:center;
  gap:.25rem;
  white-space:nowrap;
}
.ps-price .free-info::before{
  content:'★';
  font-size:.8rem;
}
.ps-benefit-title{
  font-size:1.05rem;
  font-weight:700;
  color:#38bdf8;
  margin-bottom:.75rem;
  text-align:left;
}
.ps-benefit-list{
  margin:0;
  padding:0;
  list-style:none;
  max-width:440px;
}
.ps-benefit-list li{
  background:rgba(15,23,42,.9);
  color:#e5e7eb;
  line-height:1.4;
  border-radius:8px;
  padding:.6rem .75rem;
  margin-bottom:.45rem;
  box-shadow:0 10px 20px rgba(15,23,42,.9);
  border:1px solid rgba(148,163,184,.4);
  font-size:.9rem;
  display:flex;
  align-items:center;
  gap:.35rem;
}
.ps-benefit-list li::before{
  content:'🎮';
  font-size:.95rem;
}
.ps-cta{
  margin-top:1rem;
}
.ps-btn{
  appearance:none;
  border:0;
  border-radius:999px;
  padding:.65rem 1.3rem;
  line-height:1.2;
  display:inline-flex;
  align-items:center;
  gap:.55rem;
  cursor:pointer;
  background:linear-gradient(135deg,#0ea5e9,#6366f1);
  color:#0b1120;
  font-weight:700;
  font-size:.9rem;
  box-shadow:0 12px 26px rgba(37,99,235,.65);
}
.ps-btn[disabled]{opacity:.6;cursor:not-allowed;}
.ps-btn .btn-text{
  text-transform:uppercase;
  letter-spacing:.12em;
  font-size:.78rem;
}
.ps-btn .btn-icon{
  font-size:1rem;
}
.ps-spinner{
  width:16px;
  height:16px;
  border:2px solid rgba(15,23,42,.2);
  border-top-color:#0b1120;
  border-radius:50%;
  animation:.6s linear infinite ps-spin;
  display:none;
}
.ps-btn.loading .btn-text,
.ps-btn.loading .btn-icon{display:none;}
.ps-btn.loading .ps-spinner{display:inline-block;}

@keyframes ps-spin{to{transform:rotate(360deg);}}

.ps-img-block{
  grid-area:img;
  text-align:center;
}
.ps-img-block img{
  max-width:260px;
  width:100%;
  height:auto;
  border-radius:18px;
  box-shadow:0 18px 40px rgba(15,23,42,.9);
  border:1px solid rgba(56,189,248,.55);
}
.ps-detail-block{
  grid-area:detail;
  font-size:.9rem;
  line-height:1.6;
  color:#cbd5f5;
  text-align:justify;
}
.ps-detail-block p{
  margin-bottom:.8rem;
}
.ps-detail-block .bonus-title{
  margin-top:1rem;
  font-weight:600;
  color:#38bdf8;
  text-align:left;
}
.ps-detail-block ul{
  padding-left:1rem;
  margin:.5rem 0 0;
  font-size:.9rem;
  line-height:1.4;
}
.ps-detail-block li{
  margin-bottom:.4rem;
}

@media (min-width:768px){
  .ps-wrap{padding:26px 32px;}
  .ps-grid{
    grid-template-columns:1fr 260px;
    grid-template-areas:
      "left img"
      "detail detail";
    column-gap:1.7rem;
    row-gap:1.5rem;
    align-items:flex-start;
  }
  .ps-heading{font-size:2.25rem;}
}
</style>

<div class="ps-wrap mb-2">

  <?php if (!$showPsImage): ?>
    <!-- overlay background PS (BUKAN /ps) -->
    <div class="ps-bg">
      <img src="<?= base_url('assets/images/ps4_ico.webp'); ?>" alt="">
    </div>
  <?php endif; ?>

  <div class="ps-grid">

    <!-- BLOK KIRI -->
    <div class="ps-left-block">
       <?php if (!$showPsImage): ?>
      <h2 class="ps-heading">
        <!-- AUSI -->
        <span>PLAYSTATION</span>
      </h2>
      <?php endif; ?>
      <div class="ps-img-block mb-2">
        <img src="<?= base_url('assets/images/ps4.webp'); ?>" alt="Area sewa PlayStation">
      </div>
      <div class="ps-desc">
        Biar nongkrong makin seru, main bareng di console PlayStation dengan layar gede dan kursi nyaman.
        Cocok buat duel 1vs1, mabar, sampai mini turnamen rame-rame.
      </div>

      <div class="ps-price">
        <small>MULAI DARI</small>
        <div class="harga">
          Rp <?= $psHargaLabel; ?>
          <span>/ <?= $ps_durasi; ?> menit</span>
        </div>

        <?php if ($ps_free > 0): ?>
          <div class="free-info">
            GRATIS 1 sesi setiap <?= $ps_free; ?>× main*
          </div>
        <?php endif; ?>
      </div>

      <div class="ps-benefit-title">Kenapa main di sini?</div>
      <ul class="ps-benefit-list">
        <li>Console &amp; stik dirawat rutin, siap main tanpa drama</li>
        <li>Layar besar, nyaman buat split-screen rame-rame</li>
        <li>Pilihan game populer: bola, fighting, racing, party, dan lainnya</li>
        <li>Bisa sambil pesan makanan &amp; minuman dari cafe langsung</li>
      </ul>

      <?php if (!$showPsImage): ?>
        <!-- tombol "Detail" hanya di halaman selain /ps -->
        <div class="ps-cta">
          <button
            class="ps-btn"
            type="button"
            data-go-ps="1">
            <span class="btn-text">LIHAT DETAIL</span>
            <span class="btn-icon">▶</span>
            <span class="ps-spinner"></span>
          </button>
        </div>
      <?php endif; ?>
    </div>

    <?php if ($showPsImage): ?>
   
      <!-- BLOK DETAIL FULL -->
      <div class="ps-detail-block">
        <p>
          Di
          <strong><?= htmlspecialchars($rec->nama_website ?? 'tempat kami', ENT_QUOTES, 'UTF-8'); ?></strong>
          kamu bisa sewa PlayStation buat seru-seruan bareng teman. Tinggal pilih game,
          duduk nyaman, dan langsung gas main — cocok buat nunggu giliran billiard,
          isi waktu luang, atau sekalian bikin mini turnamen.
        </p>

        <p>
          Setiap sesi berdurasi kira-kira <strong><?= $ps_durasi; ?> menit</strong>.
          Waktunya cukup buat beberapa match bola, balapan, atau game party sambil ngobrol &
          pesan menu dari cafe. Nggak perlu ribet setting, crew kami siap bantu kalau ada kendala.
        </p>

        <p style="font-weight:600;">
          Mau mabar serius atau cuma fun match? Semua bisa — yang penting suasananya santai,
          temenan nyaman, dan dompet tetap aman.
        </p>

        <?php if ($ps_free > 0): ?>
          <div class="bonus-title">
            Program GRATIS PlayStation:
          </div>
          <ul>
            <li>
              Main reguler pakai nomor HP yang sama akan otomatis tercatat poinnya.
              Setiap <strong><?= $ps_free; ?>× main</strong>, kamu berhak
              <strong>1× sesi sewa PlayStation GRATIS</strong> yang bisa diklaim lewat kasir.
            </li>
          </ul>
        <?php endif; ?>

        <p style="margin-top:1rem;">
          Tinggal bilang ke kasir mau sewa PS, pilih game, dan mulai. Waktunya level up
          malam nongkrongmu! 🎮
        </p>
      </div>
    <?php endif; ?>

  </div><!-- /.ps-grid -->
</div><!-- /.ps-wrap -->

<script>
(function(){
  var btn = document.querySelector('.ps-btn[data-go-ps]');
  if(!btn) return;

  btn.addEventListener('click', function(){
    btn.classList.add('loading');
    btn.setAttribute('disabled','disabled');
    window.location.href = "<?= site_url('ps4'); ?>";
  });
})();
</script>
