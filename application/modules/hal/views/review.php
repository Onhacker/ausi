<?php $this->load->view("front_end/head.php"); ?>
<div class="container-fluid">
  <div class="hero-title" role="banner" aria-label="Judul situs">
    <h1 class="text"><?= htmlspecialchars($title) ?></h1>
    <span class="accent" aria-hidden="true"></span>
  </div>

  <div class="row">
    <div class="col-lg-12">
      <!-- <div class="card-box p-3"> -->
   <style>
  .ausi-review-section{
    max-width:900px;
    margin:20px auto;
    font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;
  }

  /* box utama rating + tombol */
  .google-review-box{
    border:1px solid rgba(0,0,0,.08);
    border-radius:16px;
    padding:16px;
    background:#fff;
    box-shadow:0 12px 24px rgba(0,0,0,.07);
    margin-bottom:16px;
  }
  .google-head{
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:12px;
  }
  .google-icon-badge{
    width:44px;
    height:44px;
    border-radius:10px;
    background:#4285F4;
    color:#fff;
    font-weight:600;
    font-size:18px;
    display:flex;
    align-items:center;
    justify-content:center;
  }
  .google-head-txt .title{
    font-size:15px;
    font-weight:600;
    color:#000;
    line-height:1.3;
  }
  .google-head-txt .ratingline{
    font-size:13px;
    color:#555;
    line-height:1.3;
  }

  .google-desc{
    font-size:13px;
    color:#333;
    line-height:1.4;
    margin-bottom:16px;
  }

  .google-cta-wrap{
    display:flex;
    flex-wrap:wrap;
    gap:8px;
  }
  .google-btn-primary,
  .google-btn-outline{
    flex:1;
    min-width:140px;
    text-align:center;
    text-decoration:none;
    border-radius:10px;
    padding:10px 12px;
    font-size:14px;
    font-weight:600;
    line-height:1.2;
    display:inline-block;
  }
  .google-btn-primary{
    background:#000;
    color:#fff;
    border:1px solid #000;
  }
  .google-btn-outline{
    background:#fff;
    color:#000;
    border:1px solid #000;
  }

  .google-footnote{
    font-size:11px;
    color:#999;
    text-align:right;
    margin-top:10px;
  }

  /* grid testimoni */
  .testi-wrap{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:12px;
  }
  .testi-card{
    border:1px solid rgba(0,0,0,.06);
    border-radius:16px;
    background:#fff;
    padding:12px 14px;
    box-shadow:0 8px 20px rgba(0,0,0,.05);
  }
  .testi-head{
    display:flex;
    justify-content:space-between;
    align-items:center;
  }
  .testi-name{
    font-size:14px;
    font-weight:600;
    color:#000;
    line-height:1.2;
  }
  .testi-stars{
    font-size:12px;
    color:#ffb400;
    line-height:1;
    white-space:nowrap;
  }
  .testi-time{
    font-size:12px;
    color:#777;
    line-height:1.2;
    margin-top:4px;
  }
  .testi-text{
    font-size:12px;
    color:#555;
    line-height:1.4;
    margin-top:6px;
  }
  .testi-source{
    font-size:11px;
    color:#999;
    margin-top:8px;
    text-align:right;
  }
</style>

 <!-- SECTION REVIEW -->
  <section class="ausi-review-section" id="ulasan">

    <!-- BOX RATING + CTA GOOGLE -->
    <div class="google-review-box">

      <div class="google-head">
        <div class="google-icon-badge">G</div>
        <div class="google-head-txt">
          <div class="title">Rating di Google</div>

          <div class="ratingline">
            ⭐ <?= htmlspecialchars(number_format($rating_avg, 1), ENT_QUOTES, 'UTF-8'); ?>
            / 5 •
            <?= (int)$rating_total; ?>+ ulasan
          </div>
        </div>
      </div>

      <div class="google-desc">
        Udah pernah nongki, main billiard, atau coba kursi pijat di
        <?= htmlspecialchars($rec->nama_website ?? 'Ausi Billiard & Cafe', ENT_QUOTES, 'UTF-8'); ?>?
        Kasih review biar orang lain tau tempatnya enak beneran 😎
      </div>

      <div class="google-cta-wrap">
        <a class="google-btn-primary"
           href="<?= htmlspecialchars($gmaps_url, ENT_QUOTES, 'UTF-8'); ?>"
           target="_blank" rel="noopener">
          ⭐ Lihat Review
        </a>

        <a class="google-btn-outline"
           href="<?= htmlspecialchars($gmaps_url, ENT_QUOTES, 'UTF-8'); ?>"
           target="_blank" rel="noopener">
          ✍️ Tulis Review
        </a>
      </div>

      <div class="google-footnote">
        via Google Reviews
      </div>
    </div>

    <!-- GRID TESTIMONI PILIHAN (STATIC / ISI SENDIRI) -->
    <div class="testi-wrap">
      <div class="testi-card">
        <div class="testi-head">
          <div class="testi-name">Rifki A.</div>
          <div class="testi-stars">★★★★★</div>
        </div>
        <div class="testi-time">2 hari lalu</div>
        <div class="testi-text">
          “Tempat billiard ternyaman di kota. Meja bersih,
          minuman dingin, musik enak. Wajib balik lagi 🤟”
        </div>
        <div class="testi-source">(Google Review)</div>
      </div>

      <div class="testi-card">
        <div class="testi-head">
          <div class="testi-name">Novi P.</div>
          <div class="testi-stars">★★★★★</div>
        </div>
        <div class="testi-time">1 minggu lalu</div>
        <div class="testi-text">
          “Kursi pijatnya mantep, 10 menit pegel hilang 🤤
          enak habis main billiard langsung relaks, harga ramah.”
        </div>
        <div class="testi-source">(Google Review)</div>
      </div>

      <div class="testi-card">
        <div class="testi-head">
          <div class="testi-name">Andi S.</div>
          <div class="testi-stars">★★★★☆</div>
        </div>
        <div class="testi-time">3 minggu lalu</div>
        <div class="testi-text">
          “Jadwalnya enak buat nongki malam, kopi lumayan, staff ramah.
          Recommended buat santai abis futsal.”
        </div>
        <div class="testi-source">(Customer real)</div>
      </div>
    </div>

    <div style="font-size:11px;color:#999;text-align:center;margin-top:20px;">
      *Bagian testimoni ini ditampilkan ulang secara manual / ringkas.
    </div>

  </section>

      <!-- </div> -->
    </div>
  </div>
</div>

<?php $this->load->view("front_end/footer.php"); ?>
