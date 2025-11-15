<?php $this->load->view("front_end/head.php") ?>

<style>
  /* ====== Riwayat Pesanan (Perangkat Ini) ====== */
  .order-history-card{
    padding: 1.25rem 1.25rem 1rem;
  }
  .order-history-header{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:.75rem;
  }
  .order-history-title{
    margin-bottom:.15rem;
  }
  .order-history-summary{
    font-size:.8rem;
    color:#6c757d;
  }

  .order-history-empty{
    border-style:dashed !important;
    font-size:.85rem;
    display:flex;
    align-items:flex-start;
    gap:.5rem;
  }
  .order-history-empty-icon{
    font-size:1.1rem;
    line-height:1;
    margin-top:.1rem;
  }

  #riwayat-list .list-group-item{
    border:0;
    padding:.6rem .4rem .2rem 0;
    opacity:0;
    transform:translateY(4px);
    background:transparent;
  }
  #riwayat-list .list-group-item.is-in{
    animation:ohFadeUp .25s ease forwards;
  }

  /* Divider di bawah setiap item */
  .order-divider{
    margin-top:.6rem;
    height:3px;
    border-radius:999px;
    background:linear-gradient(90deg,
      rgba(148,163,184,.2),
      rgba(148,163,184,.9),
      rgba(148,163,184,.2)
    );
  }
  #riwayat-list .list-group-item:last-child .order-divider{
    display:none;
  }

  .order-badges .badge{
    font-size:.7rem;
    letter-spacing:.04em;
    text-transform:uppercase;
  }
  .order-amount{
    font-weight:600;
    font-size:.85rem;
  }
  .order-meta{
    font-size:.78rem;
    color:#6b7280;
  }
  .btn-jump-order{
    white-space:nowrap;
  }

  @keyframes ohFadeUp{
    from{opacity:0;transform:translateY(4px);}
    to{opacity:1;transform:translateY(0);}
  }

  @media (max-width: 575.98px){
    #riwayat-list .list-group-item{
      padding-right:0;
    }
  }

  /* Overlay loading saat klik "Lihat order" */
  .riwayat-loading{
    position:fixed;
    inset:0;
    background:rgba(15,23,42,.35);
    display:flex;
    align-items:center;
    justify-content:center;
    z-index:1050;
  }
  .riwayat-loading-box{
    background:#0f172a;
    color:#e5e7eb;
    padding:1rem 1.25rem;
    border-radius:.75rem;
    box-shadow:0 12px 30px rgba(15,23,42,.45);
    display:flex;
    align-items:center;
    gap:.75rem;
    font-size:.9rem;
  }
  .badge-primary {
    color: #fff;
    background-color: #FF5722;
}
a {
    color: #795548;
    text-decoration: none;
    background-color: #fff0;
}

  #riwayat-pagination{
    font-size:.78rem;
  }
  #riwayat-pagination .page-link{
    padding:.15rem .5rem;
  }

</style>

<div class="container-fluid">
  <div class="hero-title" role="banner" aria-label="Judul situs">
    <h1 class="text"><?php echo $title ?></h1>
    <span class="accent" aria-hidden="true"></span>
  </div>


  <div class="row">
    <div class="col-lg-12">
      <!-- <div class="card-box"> -->
        <div class="card card-body order-history-card">
          <div class="order-history-header mb-2">
            <div>
              <h4 class="order-history-title">Riwayat Pesanan di Perangkat Ini</h4>
              <p class="text-dark small">
                Riwayat hanya disimpan di perangkat &amp; browser ini. 
                Jika Anda menghapus data browser atau memakai perangkat lain, riwayat tidak ikut terbawa.
              </p>
            </div>
          </div>
          <div id="riwayat-count" class="order-history-summary text-dark small mb-2 d-none">
            <!-- diisi via JS -->
          </div>
          <div id="riwayat-empty" class="alert alert-light border order-history-empty mb-3 d-none">
            <span class="order-history-empty-icon">🗂️</span>
            <span>
              Belum ada riwayat pesanan di perangkat ini.<br>
              Silakan lakukan pemesanan, nanti akan tampil di sini.
            </span>
          </div>

          <ul id="riwayat-list" class="list-group list-group-flush mb-1"></ul>

          <!-- PAGINASI RIWAYAT -->
          <div id="riwayat-pagination"
               class="d-flex justify-content-between align-items-center mt-2 d-none">
            <small id="riwayat-range" class="text-muted"></small>
            <nav aria-label="Navigasi riwayat pesanan">
              <ul class="pagination pagination-sm mb-0">
                <li class="page-item disabled" id="riwayat-prev-wrap">
                  <button class="page-link" type="button" id="riwayat-prev" aria-label="Sebelumnya">
                    &laquo;
                  </button>
                </li>
                <li class="page-item disabled" id="riwayat-next-wrap">
                  <button class="page-link" type="button" id="riwayat-next" aria-label="Berikutnya">
                    &raquo;
                  </button>
                </li>
              </ul>
            </nav>
          </div>


          <!-- JUMLAH PESANAN DI BAWAH LIST -->
          
        </div>
      </div><!-- ./card-box -->
    <!-- </div> -->
  </div>
</div>

<!-- Overlay loading -->
<div id="riwayat-loading" class="riwayat-loading d-none">
  <div class="riwayat-loading-box">
    <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
    <div>Memuat detail pesanan...</div>
  </div>
</div>

<?php $this->load->view("front_end/footer.php") ?>

<script>
// Halaman riwayat pesanan (perangkat ini) dengan pagination (5 per halaman)
(function(){
  const KEY       = 'ausi_orders';
  const PAGE_SIZE = 5;

  const listEl         = document.getElementById('riwayat-list');
  const emptyEl        = document.getElementById('riwayat-empty');
  const countEl        = document.getElementById('riwayat-count');
  const btnClear       = document.getElementById('btn-clear-riwayat');
  const btnRefresh     = document.getElementById('btn-refresh-riwayat');
  const loadingOverlay = document.getElementById('riwayat-loading');

  // elemen pagination
  const paginationWrap = document.getElementById('riwayat-pagination');
  const rangeEl        = document.getElementById('riwayat-range');
  const prevItem       = document.getElementById('riwayat-prev-wrap');
  const nextItem       = document.getElementById('riwayat-next-wrap');
  const prevBtn        = document.getElementById('riwayat-prev');
  const nextBtn        = document.getElementById('riwayat-next');

  if (!listEl) return;

  let ordersCache = []; // semua data (sudah di-reverse)
  let currentPage = 1;

  function loadOrdersFromStorage(){
    let orders = [];
    try {
      const raw = localStorage.getItem(KEY);
      if (raw){
        orders = JSON.parse(raw);
        if (!Array.isArray(orders)) orders = [];
      }
    } catch(e){
      console.warn('Gagal parse ausi_orders dari localStorage', e);
      orders = [];
    }
    return orders;
  }

  function formatWaktu(iso){
    if (!iso) return '';
    try {
      const d = new Date(iso);
      if (isNaN(d.getTime())) return '';

      const hari    = d.toLocaleDateString('id-ID', { weekday: 'long' });
      const tanggal = d.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
      });
      const jam     = d.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
      });

      // Contoh: "Senin, 14 Nov 2025 19.03"
      return `${hari}, ${tanggal} ${jam}`;
    } catch(e){
      return '';
    }
  }

  function idr(n){
    const num = Number(n || 0);
    if (!num) return '';
    return 'Rp ' + num.toLocaleString('id-ID');
  }

  function mapMode(mode){
    if (!mode) return '';
    const m = String(mode).toLowerCase();
    if (m === 'dinein' || m === 'dine-in' || m === 'dine_in') return 'Makan di tempat';
    if (m === 'delivery') return 'Delivery';
    if (m === 'takeaway' || m === 'take-away' || m === 'pickup') return 'Ambil sendiri';
    return '';
  }

  function mapStatus(status){
    if (!status) return '';
    const s = String(status).toLowerCase();
    if (s === 'paid' || s === 'lunas') return 'Lunas';
    if (s === 'menunggu_bayar' || s === 'pending' || s === 'unpaid') return 'Belum bayar';
    if (s === 'canceled' || s === 'batal' || s === 'void') return 'Batal';
    return '';
  }

  function mapStatusColor(status){
    if (!status) return 'secondary';
    const s = String(status).toLowerCase();
    if (s === 'paid' || s === 'lunas') return 'success';
    if (s === 'menunggu_bayar' || s === 'pending' || s === 'unpaid') return 'warning';
    if (s === 'canceled' || s === 'batal' || s === 'void') return 'secondary';
    return 'secondary';
  }

  // Update tampilan pagination (info range + tombol prev/next)
  function updatePagination(total){
    if (!paginationWrap) return;

    // kalau tidak ada data atau total <= page size, sembunyikan pagination
    if (!total || total <= PAGE_SIZE){
      paginationWrap.classList.add('d-none');
      return;
    }

    const totalPages = Math.ceil(total / PAGE_SIZE);
    if (currentPage > totalPages) currentPage = totalPages;

    paginationWrap.classList.remove('d-none');

    const start = (currentPage - 1) * PAGE_SIZE + 1;
    let end     = currentPage * PAGE_SIZE;
    if (end > total) end = total;

    if (rangeEl){
      rangeEl.textContent = `Menampilkan ${start}–${end} dari ${total} pesanan`;
    }

    if (prevItem){
      if (currentPage <= 1) prevItem.classList.add('disabled');
      else prevItem.classList.remove('disabled');
    }

    if (nextItem){
      if (currentPage >= totalPages) nextItem.classList.add('disabled');
      else nextItem.classList.remove('disabled');
    }
  }

  function renderRiwayat(page){
    const orders = loadOrdersFromStorage();
    ordersCache  = orders.slice().reverse(); // terbaru di atas
    const total  = ordersCache.length;

    if (typeof page === 'number') currentPage = page;
    else currentPage = 1;

    listEl.innerHTML = '';

    // Tidak ada data
    if (!total){
      if (emptyEl) emptyEl.classList.remove('d-none');
      if (countEl){
        countEl.classList.remove('d-none');
        countEl.textContent = 'Belum ada pesanan yang tersimpan.';
      }
      updatePagination(0);
      return;
    }

    if (emptyEl) emptyEl.classList.add('d-none');
    if (countEl){
      countEl.classList.remove('d-none');
      countEl.textContent = total + ' pesanan tersimpan di perangkat ini.';
    }

    const totalPages = Math.ceil(total / PAGE_SIZE);
    if (currentPage > totalPages) currentPage = totalPages;

    const startIndex = (currentPage - 1) * PAGE_SIZE;
    const endIndex   = startIndex + PAGE_SIZE;
    const slice      = ordersCache.slice(startIndex, endIndex);

    slice.forEach(function(o, idx){
      const nomor       = o.nomor || '(tanpa nomor)';
      const url         = o.redirect || '#';
      const waktu       = formatWaktu(o.created_at || o.createdAt);
      const modeLabel   = mapMode(o.mode);
      const statusLabel = mapStatus(o.status);
      const statusColor = mapStatusColor(o.status);
      const amount      = o.grand_total || o.total || 0;
      const paidMethod  = (o.paid_method || '').toUpperCase();
      const deviceBrand = o.device_brand || o.deviceLabel || '';

      const li = document.createElement('li');
      li.className = 'list-group-item d-flex flex-column flex-wrap';
      li.style.animationDelay = (idx * 40) + 'ms';

      li.innerHTML = `
        <div class="d-flex justify-content-between align-items-center flex-wrap">
          <div class="d-flex flex-column flex-grow-1 mr-2">
            <div class="d-flex align-items-center justify-content-between mb-1">
              <div class="order-badges">
                <span class="badge badge-primary mr-1">Pesanan</span>
                <a href="${url}"><strong>${nomor}</strong></a>
                ${modeLabel ? `<span class="badge badge-light border text-dark ml-1">${modeLabel}</span>` : ''}
                ${statusLabel ? `<span class="badge badge-${statusColor} ml-1">${statusLabel}</span>` : ''}
              </div>
            </div>
            <div class="order-meta">
              ${waktu ? `<span class="mr-2">🕒 ${waktu}</span>` : ''}
              ${deviceBrand ? `<span class="mr-2">📱 ${deviceBrand}</span>` : ''}
              ${paidMethod ? `<span class="text-uppercase text-dark">Metode: ${paidMethod}</span>` : ''}
            </div>
          </div>
          <div class="mt-2 mt-sm-0">
            ${amount ? `<div class="order-amount text-right mb-1">${idr(amount)}</div>` : ''}
            <button type="button" class="btn btn-xs btn-blue btn-jump-order" data-url="${url}">
              Lihat order
            </button>
          </div>
        </div>
        <div class="order-divider"></div>
      `;

      listEl.appendChild(li);

      // animasi kecil setelah ter-mount
      requestAnimationFrame(function(){
        li.classList.add('is-in');
      });
    });

    // event click untuk tombol "Lihat order"
    listEl.querySelectorAll('.btn-jump-order').forEach(function(btn){
      btn.addEventListener('click', function(){
        const url = this.getAttribute('data-url');
        if (!url || url === '#' || this.disabled) return;

        // Tampilkan overlay loading
        if (loadingOverlay){
          loadingOverlay.classList.remove('d-none');
        }

        // Disable tombol & ubah isi jadi spinner
        this.disabled = true;
        this.innerHTML = `
          <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
          Membuka...
        `;

        window.location.href = url;
      });
    });

    updatePagination(total);
  }

  // Tombol refresh
  if (btnRefresh){
    btnRefresh.addEventListener('click', function(){
      renderRiwayat(currentPage || 1);
    });
  }

  // Tombol hapus riwayat
  if (btnClear){
    btnClear.addEventListener('click', function(){
      if (typeof Swal !== 'undefined'){
        Swal.fire({
          title: 'Hapus riwayat?',
          text: 'Riwayat pesanan di perangkat ini akan dihapus.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Ya, hapus',
          cancelButtonText: 'Batal'
        }).then(function(res){
          if (res.isConfirmed){
            localStorage.removeItem(KEY);
            currentPage = 1;
            renderRiwayat(1);
          }
        });
      } else {
        if (confirm('Hapus semua riwayat pesanan di perangkat ini?')){
          localStorage.removeItem(KEY);
          currentPage = 1;
          renderRiwayat(1);
        }
      }
    });
  }

  // Navigasi prev/next
  if (prevBtn){
    prevBtn.addEventListener('click', function(){
      if (prevItem && prevItem.classList.contains('disabled')) return;
      if (currentPage > 1){
        currentPage--;
        renderRiwayat(currentPage);
        // Scroll ke atas list (opsional)
        listEl.scrollIntoView({ behavior:'smooth', block:'start' });
      }
    });
  }

  if (nextBtn){
    nextBtn.addEventListener('click', function(){
      if (nextItem && nextItem.classList.contains('disabled')) return;
      const total      = ordersCache.length;
      const totalPages = Math.ceil(total / PAGE_SIZE);
      if (currentPage < totalPages){
        currentPage++;
        renderRiwayat(currentPage);
        listEl.scrollIntoView({ behavior:'smooth', block:'start' });
      }
    });
  }

  // Render awal
  renderRiwayat(1);
})();
</script>

