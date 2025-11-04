<?php $this->load->view("front_end/head.php"); ?>
<div class="container-fluid">
  <div class="hero-title" role="banner" aria-label="Judul situs">
    <h1 class="text"><?= htmlspecialchars($title ?? 'Review') ?></h1>
    <span class="accent" aria-hidden="true"></span>
  </div>

  <style>
  :root{
    --star-yellow:#FFC107; /* kuning */
    --star-muted:#C9CED6;  /* abu-abu untuk bintang kosong */
    --sep:#e6e8ec;         /* pembatas review */
  }

  /* Input rating (radio) */
  .star-wrap{ display:inline-flex; flex-direction:row-reverse; gap:.25rem; }
  .star-wrap input[type="radio"]{ display:none; }
  .star-wrap label{
    font-size:1.8rem; cursor:pointer; user-select:none;
    color:var(--star-yellow);
    opacity:.45; transition:transform .08s, opacity .12s; line-height:1;
  }
  .star-wrap label:hover, .star-wrap label:focus{ transform:scale(1.08); }
  .star-wrap input:checked ~ label,
  .star-wrap label:hover, .star-wrap label:hover ~ label{ opacity:1; }

  /* Tampilan rating (aggregate & stars) */
  .stars{ display:inline-flex; gap:2px; line-height:1; }
  .stars .star{ color:var(--star-muted); font-size:1.1rem; }
  .stars-lg .star{ font-size:1.3rem; }
  .stars-sm .star{ font-size:1rem; }
  .stars .star.filled{ color:var(--star-yellow); }

  /* Review list (dengan pembatas) */
  #reviewsWrap{ position:relative; }
  .review-list{ display:block; }
  .review-item{
    display:flex; gap:.75rem; padding:.9rem 0;
    border-top:1px dashed var(--sep);
  }
  .review-item:first-child{ border-top:none; padding-top:0; }
  .review-head{ display:flex; align-items:center; justify-content:space-between; gap:.5rem; }
  .review-meta{ color:#6c757d; font-size:.85rem; }
  .review-text{ white-space:pre-line; margin-top:.4rem; }

  /* Avatar inisial */
  .avatar{
    flex:0 0 auto; width:42px; height:42px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-weight:700; color:#fff; letter-spacing:.3px;
    user-select:none;
  }

  /* Loader overlay untuk hasil list */
  .reviews-loader{
    position:absolute; inset:0; display:none;
    align-items:center; justify-content:center;
    background:rgba(255,255,255,.6); backdrop-filter:blur(1px); z-index:5;
  }

  /* Flash highlight pada form saat auto-scroll */
  @keyframes flashRing{
    0%{ box-shadow:0 0 0 0 rgba(13,110,253,.35); }
    50%{ box-shadow:0 0 0 8px rgba(13,110,253,.18); }
    100%{ box-shadow:0 0 0 0 rgba(13,110,253,0); }
  }
  .flash{ animation:flashRing 1.1s ease; border-radius:12px; }
  </style>
 <style>
        /* Aktif = biru */
        .pagination .page-item.active .page-link,
        .pagination .page-link[aria-current="page"]{
          background-color: #4a81d4; /* blue */
          border-color: #4a81d4;
          color: #fff;
        }

        /* State hover/focus saat aktif */
        .pagination .page-item.active .page-link:hover,
        .pagination .page-item.active .page-link:focus,
        .pagination .page-link[aria-current="page"]:hover,
        .pagination .page-link[aria-current="page"]:focus{
          background-color: #0b5ed7;
          border-color: #0b5ed7;
          color: #fff;
        }
      </style>
  <!-- RINGKASAN + FORM -->
  <div class="row g-3">
    <!-- RINGKASAN -->
    <!-- Awal: form hidden → summary melebar penuh -->
    <div id="colSummary" class="col-lg-12">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between gap-3">
            <div>
              <div class="fs-2 fw-bold"><span id="aggAvg"><?= number_format($review_avg,1) ?></span>/5</div>
              <div id="aggStars" class="stars stars-lg" aria-label="Rata-rata rating"></div>
              <div class="text-muted small">Berdasarkan <span id="aggCount"><?= (int)$review_count ?></span> ulasan.</div>
            </div>
            <div class="text-end">
              <img src="<?= html_escape($prev) ?>" alt="Preview" style="height:56px;border-radius:8px;object-fit:cover;" class="mb-2">
              <div>
                <button
                  type="button"
                  id="btnJumpToForm"
                  class="btn btn-sm btn-blue"
                  aria-expanded="false"
                  aria-controls="writeReview"
                >
                  ✍️ Tulis Review
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- FORM REVIEW (awal: disembunyikan dan dilebarkan saat tampil) -->
    <div id="colForm" class="col-lg-6 d-none">
      <div class="card shadow-sm" id="writeReview">
        <div class="card-body" id="reviewFormCard">
          <h4 class="card-title">Kirim Review, Kritik & Saran
            <small class="d-block mt-1">Masukan Anda sangat berarti dan kami gunakan untuk meningkatkan kualitas layanan.</small>
          </h4>

          <form id="reviewForm" autocomplete="off" novalidate>
            <input type="hidden" id="csrf_name" value="<?= html_escape($csrf_name) ?>">
            <input type="hidden" id="csrf_hash" name="<?= html_escape($csrf_name) ?>" value="<?= html_escape($csrf_hash) ?>">

            <div class="mb-2">
              <label class="form-label">Nama</label>
              <input type="text" name="nama" class="form-control" placeholder="Nama Anda" required>
              <small>Boleh pakai nama panggilan jika lebih nyaman</small>
            </div>

            <div class="mb-2">
              <label class="form-label d-block">Beri Bintang</label>
              <div class="star-wrap" role="radiogroup" aria-label="Rating">
                <?php for($i=5;$i>=1;$i--): ?>
                  <input type="radio" id="star<?= $i ?>" name="bintang" value="<?= $i ?>">
                  <label for="star<?= $i ?>" title="<?= $i ?> bintang">★</label>
                <?php endfor; ?>
              </div>
            </div>

            <div class="mb-2">
              <label class="form-label">Ulasan / Kritik & Saran</label>
              <textarea name="ulasan" class="form-control" rows="4" placeholder="Tulis ulasan Anda..." required></textarea>
            </div>

            <div class="mb-2">
              <label class="form-label">Captcha</label>
              <div class="d-flex align-items-center flex-wrap gap-2">
                <div id="captchaWords" class="badge bg-dark-subtle text-dark" style="font-size:1rem;">
                  <code><?= html_escape($captcha_words) ?></code>
                </div>
                <button type="button" id="btnRefreshCaptcha" class="btn btn-sm btn-outline-secondary">Ganti</button>
              </div>
              <small class="text-dark d-block mt-1">Masukkan Angka</small>
              <input type="text" name="captcha" class="form-control mt-2" maxlength="4" inputmode="numeric" pattern="\d{4}" placeholder="####" required>
            </div>

            <button type="submit" class="btn btn-blue" id="btnSubmit">Kirim</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- LIST REVIEW (DIBERI PEMBATAS) -->
  <div class="card shadow-sm">
    <div class="card-body" id="reviewsWrap">
      <h4 class="card-title mb-2">Semua Review</h4>

      <div id="reviewsGrid" class="review-list" aria-live="polite">
        <!-- item by JS -->
      </div>

      <!-- LOADER OVERLAY -->
      <div id="reviewsLoader" class="reviews-loader">
        <div class="spinner-border" role="status" aria-label="Memuat..."></div>
      </div>

      <nav class="mt-3" aria-label="Navigasi halaman review">
        <ul class="pagination  justify-content-center gap-1" id="reviewsPager"></ul>
      </nav>
    </div>
  </div>
</div>

<script>
/* ===== Helpers bintang (kuning/abu) ===== */
function setStarsEl(el, n){
  n = Math.max(0, Math.min(5, parseInt(n||0)));
  el.innerHTML = '';
  for (let i = 1; i <= 5; i++){
    const s = document.createElement('span');
    s.className = 'star' + (i <= n ? ' filled' : '');
    s.textContent = '★';
    el.appendChild(s);
  }
}

/* ===== Avatar inisial ===== */
const AVA_COLORS = ['#64748b','#0ea5e9','#22c55e','#8b5cf6','#ef4444','#f97316','#14b8a6','#eab308'];
function nameInitials(name=''){
  const clean = String(name).trim().replace(/\s+/g,' ').split(' ').slice(0,2);
  return clean.map(w=>w[0] ? w[0].toUpperCase() : '').join('');
}
function colorFromName(name=''){
  let h=0; for(let i=0;i<name.length;i++){ h = (h*31 + name.charCodeAt(i)) >>> 0; }
  return AVA_COLORS[h % AVA_COLORS.length];
}

(function(){
  const submitUrl  = "<?= $review_submit_url ?>";
  const listUrl    = "<?= $review_list_url ?>";
  const captchaUrl = "<?= $review_captcha_url ?>";

  let csrfName = document.getElementById('csrf_name').value;
  let csrfHash = document.getElementById('csrf_hash').value;

  const form = document.getElementById('reviewForm');
  const formCard = document.getElementById('reviewFormCard');
  const btnSubmit = document.getElementById('btnSubmit');
  const btnJump = document.getElementById('btnJumpToForm');

  const colSummary = document.getElementById('colSummary');
  const colForm    = document.getElementById('colForm');

  let curPage = 1, perPage = 10, totalPages = 1;

  /* Ringkasan awal dari server (biar langsung kelihatan) */
  setStarsEl(document.getElementById('aggStars'), <?= (int)round($review_avg) ?>);

  function updateAgg(agg){
    if(!agg) return;
    const avg = (agg.avg ?? 0).toFixed(1);
    document.getElementById('aggAvg').textContent = avg;
    document.getElementById('aggCount').textContent = agg.count ?? 0;
    setStarsEl(document.getElementById('aggStars'), Math.round(agg.avg ?? 0));
  }

  /* Toggle tampil/sembunyi form + atur lebar kolom */
  function toggleForm(show){
    const willShow = (typeof show === 'boolean') ? show : colForm.classList.contains('d-none');
    if (willShow){
      // Tampilkan form, kecilkan summary jadi 6 kolom
      colForm.classList.remove('d-none');
      colSummary.classList.remove('col-lg-12');
      if (!colSummary.classList.contains('col-lg-6')) colSummary.classList.add('col-lg-6');
      btnJump.setAttribute('aria-expanded', 'true');
      btnJump.textContent = '⬇️ Tutup Form';
      // Scroll & highlight
      document.getElementById('writeReview').scrollIntoView({ behavior:'smooth', block:'start' });
      setTimeout(()=>{
        formCard.classList.add('flash');
        const nameInput = form.querySelector('input[name="nama"]');
        if(nameInput) nameInput.focus();
        setTimeout(()=>formCard.classList.remove('flash'), 1200);
      }, 400);
    }else{
      // Sembunyikan form, lebarkan summary jadi 12 kolom
      colForm.classList.add('d-none');
      colSummary.classList.remove('col-lg-6');
      if (!colSummary.classList.contains('col-lg-12')) colSummary.classList.add('col-lg-12');
      btnJump.setAttribute('aria-expanded', 'false');
      btnJump.textContent = '✍️ Tulis Review';
      // Kembali ke ringkasan
      colSummary.scrollIntoView({ behavior:'smooth', block:'start' });
    }
  }

  /* Render item list dengan avatar inisial + pembatas */
  function renderCards(items){
    const grid = document.getElementById('reviewsGrid');
    if(!items || items.length===0){
      grid.innerHTML = '<div class="text-center text-muted py-4">Belum ada review.</div>';
      return;
    }
    grid.innerHTML = '';
    items.forEach(it=>{
      const name = it.nama || 'Anonim';
      const initials = nameInitials(name);
      const color = colorFromName(name);

      const wrap = document.createElement('div');
      wrap.className = 'review-item';

      wrap.innerHTML = `
        <div class="avatar" style="background:${color}">${initials || 'U'}</div>
        <div class="flex-grow-1">
          <div class="review-head">
            <div class="fw-semibold">${name}</div>
            <div class="stars stars-sm" aria-label="Rating"></div>
          </div>
          <div class="review-meta">${it.created_at || ''}</div>
          <p class="review-text mb-0"></p>
        </div>
      `;
      wrap.querySelector('.review-text').textContent = it.ulasan || '';
      setStarsEl(wrap.querySelector('.stars'), it.bintang || 0);
      grid.appendChild(wrap);
    });
  }

  function renderPager(){
    const pg = document.getElementById('reviewsPager');
    pg.innerHTML = '';
    const mk = (label, pageNum, disabled=false, active=false)=>{
      const li = document.createElement('li');
      li.className = 'page-item'+(disabled?' disabled':'')+(active?' active':'');
      const a = document.createElement('a'); a.className='page-link'; a.href='#'; a.textContent=label;
      if(!disabled){
        a.addEventListener('click', e=>{
          e.preventDefault();
          showLoader(true);
          load(curPage = pageNum);
        });
      }
      li.appendChild(a); return li;
    };
    pg.appendChild(mk('«', Math.max(1, curPage-1), curPage===1));
    const win=2, start=Math.max(1, curPage-win), end=Math.min(totalPages, curPage+win);
    for(let p=start; p<=end; p++) pg.appendChild(mk(String(p), p, false, p===curPage));
    pg.appendChild(mk('»', Math.min(totalPages, curPage+1), curPage===totalPages));
  }

  function showLoader(show){
    const el = document.getElementById('reviewsLoader');
    el.style.display = show ? 'flex' : 'none';
  }

  async function load(page=1){
    const url = new URL(listUrl, window.location.origin);
    url.searchParams.set('page', page);
    url.searchParams.set('per_page', perPage);

    // skeleton cepat
    const grid = document.getElementById('reviewsGrid');
    grid.innerHTML =
      '<div class="placeholder-glow"><span class="placeholder col-7"></span><span class="placeholder col-4"></span><span class="placeholder col-12"></span></div>';

    showLoader(true);
    try{
      const r = await fetch(url, { headers: { 'Accept':'application/json' }});
      const j = await r.json();
      if(!j.ok) throw new Error('Gagal memuat');

      totalPages = j.total_pages || 1;
      renderCards(j.items || []);
      renderPager();
      updateAgg(j.agg);
    }catch(e){
      grid.innerHTML = '<div class="text-center text-danger py-4">Error memuat data.</div>';
    }finally{
      showLoader(false);
    }
  }

  // Klik tombol → toggle form
  btnJump.addEventListener('click', (e)=>{
    e.preventDefault();
    toggleForm(); // auto toggle
  });

  // Submit form
  form.addEventListener('submit', async (ev)=>{
    ev.preventDefault();
    btnSubmit.disabled = true;

    const fd = new FormData(form);
    fd.append(csrfName, csrfHash);

    try{
      const r = await fetch(submitUrl, { method:'POST', body: fd });
      const j = await r.json();

      if(j.csrf){
        csrfHash = j.csrf;
        document.getElementById('csrf_hash').value = csrfHash;
      }
      if(j.captcha_words){
        const cw = document.querySelector('#captchaWords code');
        if(cw) cw.textContent = j.captcha_words; else document.getElementById('captchaWords').textContent = j.captcha_words;
      }

      if(!j.ok){
        const msg = (j.errors && Object.values(j.errors)[0]) || 'Gagal menyimpan. Periksa isian.';
        if (window.Swal) Swal.fire({icon:'error', title:'Gagal', text: msg});
        else alert(msg);
      }else{
        form.reset();
        if (window.Swal) Swal.fire({icon:'success', title:'Terima kasih!', text: j.msg || 'Review tersimpan.'});
        await load(1); // refresh list & agregat
        document.getElementById('reviewsWrap').scrollIntoView({behavior:'smooth', block:'start'});
        // (opsional) otomatis sembunyikan form setelah submit sukses:
        // toggleForm(false);
      }
    }catch(e){
      if (window.Swal) Swal.fire({icon:'error', title:'Error', text:'Terjadi kesalahan jaringan.'});
      else alert('Terjadi kesalahan jaringan.');
    }finally{
      btnSubmit.disabled = false;
    }
  });

  // Refresh captcha
  document.getElementById('btnRefreshCaptcha').addEventListener('click', async ()=>{
    try{
      const r = await fetch(captchaUrl, { headers: { 'Accept':'application/json' } });
      const j = await r.json();
      if(j.csrf){
        csrfHash = j.csrf;
        document.getElementById('csrf_hash').value = csrfHash;
      }
      if(j.captcha_words){
        const cw = document.querySelector('#captchaWords code');
        if(cw) cw.textContent = j.captcha_words; else document.getElementById('captchaWords').textContent = j.captcha_words;
      }
    }catch(e){}
  });

  // initial load
  load(1);
})();
</script>

<?php
// ===== JSON-LD (Google-friendly) =====
$prodName = $rec->nama_website ?? 'Ausi Billiard & Café';
$agg = [
  "@type" => "AggregateRating",
  "ratingValue" => (float)$review_avg,
  "reviewCount" => (int)$review_count
];
$reviews_ld = [];
foreach ($jsonld_reviews as $r) {
  $reviews_ld[] = [
    "@type" => "Review",
    "author" => ["@type"=>"Person","name"=>$r['nama']],
    "reviewRating" => ["@type"=>"Rating","ratingValue"=>(int)$r['bintang'],"bestRating"=>5,"worstRating"=>1],
    "reviewBody" => $r['ulasan'],
    "datePublished" => date('c', strtotime($r['created_at'])),
  ];
}
$ld = [
  "@context" => "https://schema.org",
  "@type" => "Product",
  "name" => $prodName,
  "image" => $prev,
  "aggregateRating" => $agg,
  "review" => $reviews_ld,
];
?>
<script type="application/ld+json">
<?= json_encode($ld, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) ?>
</script>

<?php $this->load->view("front_end/footer.php"); ?>
