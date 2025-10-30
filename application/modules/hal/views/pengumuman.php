<?php $this->load->view("front_end/head.php"); ?>
<div class="container-fluid">
  <div class="hero-title" role="banner" aria-label="Judul situs">
    <h1 class="text"><?= htmlspecialchars($title) ?></h1>
    <span class="accent" aria-hidden="true"></span>
  </div>

  <div class="row mt-3">
    <div class="col-lg-12">
      <div class="card-box p-3">
        <!-- Search bar -->
        <form id="frmSearch" class="mb-3">
          <div class="input-group">
            <input type="search" class="form-control" id="q" name="q" placeholder="Cari pengumuman..." aria-label="Cari pengumuman">
            <div class="input-group-append">
              <button class="btn btn-blue" type="submit"><i class="fe-search"></i> Cari</button>
            </div>
          </div>
        </form>

        <!-- List -->
        <div id="listWrap"></div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
          <button id="btnPrev" class="btn btn-outline-secondary btn-sm"><i class="fe-chevron-left"></i> </button>
          <div><span id="pagestat" class="text-muted small">Hal. 1/1</span></div>
          <button id="btnNext" class="btn btn-outline-secondary btn-sm"> <i class="fe-chevron-right"></i></button>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.pgm-card{ border:1px solid #e5e7eb; border-radius:16px; padding:16px; margin-bottom:12px; background:#fff; transition:box-shadow .2s }
.pgm-card:hover{ box-shadow: 0 8px 20px rgba(0,0,0,.06); }
.pgm-title{ font-weight:700; font-size:1.05rem; margin-bottom:.25rem }
.pgm-date{ font-size:.85rem; color:#6b7280; margin-bottom:.5rem }
.pgm-excerpt{ color:#374151; }
.pgm-read{ margin-top:.5rem }
.skel{ border-radius:12px; background:linear-gradient(90deg,#f3f4f6 25%,#e5e7eb 37%,#f3f4f6 63%); background-size:400% 100%; animation:shimmer 1.4s ease infinite; height:84px; margin-bottom:10px; }
@keyframes shimmer { 0%{background-position:100% 0}100%{background-position:0 0} }
</style>

<script>
(function(){
  const LIST_URL = "<?= site_url('hal/pengumuman_data') ?>";
  const SEO_BASE = "<?= site_url('hal/detail_pengumuman/') ?>";
  let state = { q: "", page: 1, per_page: 5, pages: 1, loading: false };

  const frm  = document.getElementById('frmSearch');
  const qIn  = document.getElementById('q');
  const list = document.getElementById('listWrap');
  const prev = document.getElementById('btnPrev');
  const next = document.getElementById('btnNext');
  const stat = document.getElementById('pagestat');

  frm.addEventListener('submit', function(e){
    e.preventDefault();
    state.q = qIn.value.trim();
    state.page = 1;
    fetchList();
  });

  prev.addEventListener('click', function(){
    if (state.page > 1){ state.page--; fetchList(); }
  });
  next.addEventListener('click', function(){
    if (state.page < state.pages){ state.page++; fetchList(); }
  });

  function skel(n=5){
    let h = '';
    for (let i=0;i<n;i++) h += '<div class="skel"></div>';
    list.innerHTML = h;
  }

  function slugify(s){
    return (s||'')
      .toString()
      .normalize('NFKD').replace(/[\u0300-\u036f]/g,'')
      .replace(/[^a-zA-Z0-9\s-]/g,'')
      .trim().replace(/\s+/g,'-').replace(/-+/g,'-')
      .toLowerCase();
  }
  function detailHref(item){
    if (item.link_seo) return SEO_BASE + encodeURIComponent(item.link_seo);
    const s = slugify(item.judul);
    return SEO_BASE + item.id + (s ? '-' + s : '');
  }

  function card(item){
    const href = detailHref(item);
    const tgl  = item.tanggal_view || item.tanggal || '';
    return `
      <article class="pgm-card" aria-labelledby="t${item.id}">
        <header>
          <h2 class="pgm-title" id="t${item.id}">
            <a href="${href}" class="text-dark">${escapeHtml(item.judul)}</a>
          </h2>
          <div class="pgm-date"><i class="fe-calendar"></i> ${escapeHtml(tgl)}</div>
        </header>
        <p class="pgm-excerpt">${escapeHtml(item.excerpt)}</p>
        <div class="pgm-read">
          <a class="btn btn-sm btn-outline-blue" href="${href}" aria-label="Baca selengkapnya tentang ${escapeHtml(item.judul)}">Baca selengkapnya</a>
        </div>
      </article>`;
  }

  function escapeHtml(s){ return (s||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

  async function fetchList(){
    if (state.loading) return;
    state.loading = true;
    skel(5);

    const url = new URL(LIST_URL, location.origin);
    url.searchParams.set('q', state.q);
    url.searchParams.set('page', state.page);
    url.searchParams.set('per_page', state.per_page);

    try{
      const res = await fetch(url.toString(), {
        headers: { 'Accept':'application/json' },
        credentials: 'same-origin'
      });
      if (!res.ok) throw new Error('HTTP '+res.status);
      const j = await res.json();
      if (!j.success) throw new Error('Gagal memuat');

      state.pages = j.pages || 1;
      state.page  = j.page  || 1;

      list.innerHTML = (j.items && j.items.length)
        ? j.items.map(card).join('')
        : '<div class="alert alert-info mb-0">Belum ada pengumuman yang cocok.</div>';

      prev.disabled = (state.page <= 1);
      next.disabled = (state.page >= state.pages);
      stat.textContent = `Hal. ${state.page} / ${state.pages} • Total ${j.total||0} data`;
    } catch(err){
      console.warn(err);
      list.innerHTML = '<div class="alert alert-danger">Gagal memuat data. Coba lagi.</div>';
    } finally {
      state.loading = false;
    }
  }

  fetchList();
})();
</script>
<?php $this->load->view("front_end/footer.php"); ?>
