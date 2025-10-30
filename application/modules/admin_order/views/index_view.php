<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container-fluid">
  <div class="page-title-box">
    <h4 class="page-title"><?= html_escape($title ?? 'Kasir - Order'); ?></h4>
  </div>

  <style>
    #tables-wrap{
      display:grid;
      grid-template-columns:repeat(8, minmax(150px,1fr));
      gap:12px;
    }
    @media (max-width:1600px){ #tables-wrap{ grid-template-columns:repeat(6, minmax(150px,1fr)); } }
    @media (max-width:1200px){ #tables-wrap{ grid-template-columns:repeat(4, minmax(140px,1fr)); } }
    @media (max-width:768px) { #tables-wrap{ grid-template-columns:repeat(2, minmax(140px,1fr)); } }

    .table-card{ border:1px solid rgba(0,0,0,.06); border-radius:12px; padding:14px; background:#fff; cursor:pointer; transition:box-shadow .15s, transform .05s, border-color .2s; }
    .table-card:hover{ box-shadow:0 8px 26px rgba(0,0,0,.08); }
    .table-card:active{ transform:scale(.99); }
    .table-name{ font-weight:700; margin:0; font-size:1rem; display:flex; align-items:center; gap:8px; }
    .badge-pill{ border-radius:999px; padding:2px 8px; font-size:.72rem; font-weight:600; }
    .badge-terisi{ background:#eaf6ff; color:#1062a6; border:1px solid #b5ddff; }
    .badge-kosong{ background:#fff3f3; color:#c62828; border:1px solid #ffc9c9; }
    .count{ font-size:.9rem; color:#555; }
    .empty-note{ grid-column:1/-1; text-align:center; color:#777; padding:16px; }

    .controls{ display:flex; align-items:center; gap:8px; margin-bottom:12px; flex-wrap:wrap; }
    .controls .btn{ padding:.35rem .7rem; }

    #quick-search-wrap .form-control { height: calc(1.5em + .5rem + 2px); }
    #qs-result .item{ display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #eee; padding:6px 4px; }
    #qs-result .item .name{ font-weight:600; }
    #qs-result .item .meta{ color:#666; font-size:.85rem; }
  </style>

  <div class="controls">
    <div class="btn-group" role="group" aria-label="Filter">
      <button class="btn btn-sm btn-outline-secondary active" data-filter="all">Semua</button>
      <button class="btn btn-sm btn-outline-secondary" data-filter="terisi">Terisi</button>
      <button class="btn btn-sm btn-outline-secondary" data-filter="kosong">Kosong</button>
    </div>
    <button id="btnRefresh" class="btn btn-sm btn-outline-primary" title="Muat ulang (auto 20s)">⟳ Refresh</button>
    <div class="text-muted small ml-2" id="lastUpdateTxt">—</div>
  </div>

  <div id="tables-wrap">
    <div class="empty-note">Memuat meja…</div>
  </div>
</div>

<!-- MODAL DETAIL ORDER -->
<div class="modal fade" id="modalOrder" tabindex="-1" aria-hidden="true" aria-labelledby="modalOrderTitle">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="modalOrderTitle" class="modal-title">Detail Pesanan</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body" id="modalOrderBody">
        <div class="py-4 text-center text-muted">Memuat…</div>
      </div>
      <div class="modal-footer">
        <a id="btnCetakKitchen" class="btn btn-outline-info" target="_blank" rel="noopener"><i class="mdi mdi-silverware"></i> Cetak Kitchen</a>
        <a id="btnCetakBar" class="btn btn-outline-secondary" target="_blank" rel="noopener"><i class="mdi mdi-glass-cocktail"></i> Cetak Bar</a>
        <a id="btnCetakMeja" class="btn btn-outline-secondary" target="_blank" rel="noopener"><i class="mdi mdi-table-chair"></i> Cetak Meja</a>
        <a id="btnCetakStruk" class="btn btn-outline-success" target="_blank" rel="noopener"><i class="mdi mdi-receipt"></i> Cetak Struk</a>
        <button class="btn btn-light" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- Assets -->
<script src="<?php echo base_url('assets/admin/js/vendor.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/admin/js/app.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/admin/js/sw.min.js'); ?>"></script>
<script>if(typeof Swal==='undefined'){window.Swal={fire:(o)=>alert((o.title?o.title+'\n':'')+(o.text||o.html||''))};}</script>

<script>
(function(){
  const api = {
    tables       : "<?= site_url('admin_order/tables_json'); ?>",
    activeOrder  : "<?= site_url('admin_order/active_order_json'); ?>",
    detail       : "<?= site_url('admin_order/order_detail_json'); ?>",
    categories   : "<?= site_url('admin_order/categories_json'); ?>",
    products     : "<?= site_url('admin_order/products_json'); ?>",
    addItem      : "<?= site_url('admin_order/add_item'); ?>",
    removeItem   : "<?= site_url('admin_order/remove_item'); ?>",
    payCash      : "<?= site_url('admin_order/pay_cash'); ?>",
    qrisCreate   : "<?= site_url('admin_order/qris_create'); ?>",
    qrisConfirm  : "<?= site_url('admin_order/qris_confirm'); ?>",
    printReceipt : "<?= site_url('admin_order/print_receipt'); ?>",
    printKitchen : "<?= site_url('admin_order/print_kitchen'); ?>",
    printBar     : "<?= site_url('admin_order/print_bar'); ?>",
    printTable   : "<?= site_url('admin_order/print_table'); ?>",
    createOrder  : "<?= site_url('admin_order/create_order'); ?>",
  };

  const wrap = document.getElementById('tables-wrap');
  const lastTxt = document.getElementById('lastUpdateTxt');
  const btnRefresh = document.getElementById('btnRefresh');

  let filter = 'all';
  let pollTimer = null;
  let lastNow = 0;

  // hanya untuk popup "order baru" (kosong -> terisi)
  const lastEmptyMap = {}; // { kode: is_empty }
  window.openKode = null;

  // Filter tombol
  document.querySelectorAll('[data-filter]').forEach(btn=>{
    btn.addEventListener('click', function(){
      document.querySelectorAll('[data-filter]').forEach(b=>b.classList.remove('active'));
      this.classList.add('active');
      filter = this.getAttribute('data-filter');
      applyFilter();
    });
  });

  // Refresh manual
  btnRefresh.addEventListener('click', ()=> loadTables(true));

  // Start
  loadTables(true);
  pollTimer = setInterval(loadTables, 20000);

  // ====== AJAX meja
  function loadTables(force=false){
    const url = api.tables + (lastNow ? ('?since='+encodeURIComponent(lastNow)) : '');
    fetch(url, {cache:'no-store'})
      .then(r=>r.json())
      .then(j=>{
        if(!j || !j.success){ renderError('Gagal memuat meja.'); return; }
        lastNow = Math.floor(Date.now()/1000);
        lastTxt.textContent = 'Update: ' + new Date().toLocaleTimeString();

        const list = j.tables || [];

        // Notif kosong -> terisi (order baru)
        list.forEach(t=>{
          const kode = String(t.kode||'');
          const curEmpty = !!t.is_empty;
          const prevEmpty = (kode in lastEmptyMap) ? lastEmptyMap[kode] : true;
          if (prevEmpty === true && curEmpty === false){
            Swal.fire({
              title: 'Order baru!',
              html: `Meja <b>${escapeHtml(kode==='WALKIN'?'Takeaway':(t.nama||t.kode))}</b> mendapat order baru.`,
              icon: 'info',
              showCancelButton: true,
              confirmButtonText: 'Buka Meja',
              cancelButtonText: 'Tutup'
            }).then(res=>{ if(res.isConfirmed){ openOrderModalForKode(kode, false); }});
          }
          lastEmptyMap[kode] = curEmpty;
        });

        renderTables(list);
      })
      .catch(()=> renderError('Koneksi bermasalah.'));
  }

  function renderError(msg){
    wrap.innerHTML = `<div class="empty-note">${escapeHtml(msg)}</div>`;
  }

  // ====== Render grid meja (Nama pelanggan saja)
  function renderTables(list){
    wrap.innerHTML = '';
    if(!list || !list.length){
      wrap.innerHTML = '<div class="empty-note">Tidak ada meja.</div>';
      return;
    }
    list.forEach(t=>{
      const kode   = t.kode;
      const isWalk = (kode === 'WALKIN');
      const title  = isWalk ? 'Takeaway' : (t.nama || t.kode);
      const empty  = !!t.is_empty;
      const currentName = t.current_name || (empty ? 'Kosong' : '');

      const card = document.createElement('div');
      card.className = 'table-card';
      card.setAttribute('data-kode', kode);
      card.setAttribute('data-status', empty ? 'kosong' : 'terisi');

      card.innerHTML = `
        <div class="d-flex justify-content-between align-items-start">
          <h6 class="table-name mb-2">
            <span>${escapeHtml(title)}</span>
          </h6>
          <span class="badge-pill ${empty?'badge-kosong':'badge-terisi'}">${empty?'Kosong':'Terisi'}</span>
        </div>
        <div class="count">Nama: <b>${escapeHtml(currentName)}</b></div>
      `;

      card.addEventListener('click', ()=> openOrderModalForKode(kode, false));
      wrap.appendChild(card);
    });
    applyFilter();
  }

  function applyFilter(){
    const showAll = (filter==='all');
    const wantTerisi = (filter==='terisi');
    const wantKosong = (filter==='kosong');
    const cards = wrap.querySelectorAll('.table-card');
    let visible = 0;
    cards.forEach(c=>{
      const st = c.getAttribute('data-status');
      const show = showAll || (wantTerisi && st==='terisi') || (wantKosong && st==='kosong');
      c.style.display = show ? '' : 'none';
      if (show) visible++;
    });
    if (visible===0){
      if (!wrap.querySelector('.empty-note')){
        const div = document.createElement('div');
        div.className='empty-note';
        div.textContent='Tidak ada meja sesuai filter.';
        wrap.appendChild(div);
      }
    } else {
      const note = wrap.querySelector('.empty-note');
      if (note) note.remove();
    }
  }

  // ===== Modal detail (empty & filled both support add produk)
  function openOrderModalForKode(kode, focusAdd=false){
    window.openKode = kode;
    const $modal = $('#modalOrder');
    const bodyEl = document.getElementById('modalOrderBody');
    const titleEl= document.getElementById('modalOrderTitle');
    const btnKitchen = document.getElementById('btnCetakKitchen');
    const btnBar     = document.getElementById('btnCetakBar');
    const btnMeja    = document.getElementById('btnCetakMeja');
    const btnStruk   = document.getElementById('btnCetakStruk');

    titleEl.textContent = 'Detail Pesanan - '+(kode==='WALKIN'?'Takeaway':kode);
    bodyEl.innerHTML = '<div class="text-center text-muted py-5">Memuat…</div>';
    $modal.modal('show');

    fetch(api.activeOrder + '?kode=' + encodeURIComponent(kode), {cache:'no-store'})
      .then(r=>r.json()).then(j=>{
        if(!j || !j.success){ bodyEl.innerHTML = '<div class="text-danger p-3">Gagal memuat.</div>'; return; }

        let order = j.order || null;
        const items = j.items || [];

        if (!order){
          // Meja kosong: tampilkan area tambah produk (auto-create order saat Add)
          [btnKitchen,btnBar,btnMeja,btnStruk].forEach(a=>a.setAttribute('href','javascript:void(0)'));

          bodyEl.innerHTML = `
            <div class="mb-2 d-flex justify-content-between align-items-start w-100">
              <div>
                <div><b>Order:</b> —</div>
                <div class="form-inline mt-1">
                  <label class="mr-2 mb-1">Pelanggan</label>
                  <input id="custNameEmpty" class="form-control form-control-sm" placeholder="Nama (opsional)">
                </div>
                <div class="mt-1"><b>Meja:</b> ${escapeHtml(kode==='WALKIN'?'Takeaway':kode)}</div>
              </div>
              <div class="text-right">
                <div class="mb-2"><span class="badge badge-pill badge-light">Total: <b>Rp 0</b></span></div>
                <div class="small text-muted">Order dibuat otomatis saat menambah item.</div>
              </div>
            </div>

            ${renderQuickSearch()}
            <div class="alert alert-info mb-0">Belum ada item.</div>
          `;

          loadCategoriesInto(bodyEl.querySelector('#qs-kategori'));
          initQuickSearch(null, bodyEl);

          if (focusAdd){
            const inp = bodyEl.querySelector('#qs-input');
            if (inp){ inp.focus(); }
          }
          return;
        }

        // Meja ada order
        btnKitchen.setAttribute('href', api.printKitchen + '/' + encodeURIComponent(order.id));
        btnBar.setAttribute('href', api.printBar + '/' + encodeURIComponent(order.id));
        btnMeja.setAttribute('href', api.printTable + '/' + encodeURIComponent(order.id));
        btnStruk.setAttribute('href', api.printReceipt + '/' + encodeURIComponent(order.id));

        const head = `
          <div class="mb-2 d-flex justify-content-between align-items-start w-100">
            <div>
              <div><b>Order:</b> ${escapeHtml(order.nomor||('ORD-'+order.id))}</div>
              <div><b>Pelanggan:</b> ${escapeHtml(order.nama||'-')}</div>
              ${(order.meja_nama || order.meja_kode) ? `<div><b>Meja:</b> ${escapeHtml(order.meja_nama||order.meja_kode)}</div>` : ''}
              <div><b>Waktu:</b> ${escapeHtml(order.waktu_fmt||order.waktu||'')}</div>
            </div>
            <div class="text-right">
              <div class="mb-2"><span class="badge badge-pill badge-light">Total: <b>${escapeHtml(order.total_fmt||rupiah(order.total||0))}</b></span></div>
              <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-success" id="btnPayCash">Bayar Cash</button>
                <button class="btn btn-outline-primary" id="btnQRIS">QRIS</button>
                <button class="btn btn-outline-secondary" id="btnQRISConfirm" title="Tandai lunas QRIS">Confirm</button>
              </div>
            </div>
          </div>
          <div id="qrisBox" class="mb-2" style="display:none;">
            <div class="border rounded p-2">
              <div class="small text-muted mb-1">QRIS untuk pembayaran:</div>
              <img id="qrisImg" src="" alt="QRIS" style="max-width:180px; height:auto;">
              <div class="small mt-1"><b>Nominal:</b> <span id="qrisAmt">-</span></div>
            </div>
          </div>
        `;

        const rows = items.map(it=>{
          const qty = it.qty||0, harga=it.harga||0, sub = (it.subtotal!=null?it.subtotal:qty*harga);
          const canDel = String(it.added_by||'').toLowerCase()==='admin';
          return `
            <tr>
              <td>${escapeHtml(it.nama||'')}</td>
              <td class="text-center">${qty}</td>
              <td class="text-right">${escapeHtml(it.harga_fmt||rupiah(harga))}</td>
              <td class="text-right">${escapeHtml(it.sub_fmt||rupiah(sub))}</td>
              <td class="text-right">${canDel ? `<button class="btn btn-sm btn-outline-danger" data-del="${it.item_id}"><i class="mdi mdi-trash-can-outline"></i></button>` : ''}</td>
            </tr>`;
        }).join('');
        const total = items.reduce((a,b)=>a + (b.subtotal!=null?b.subtotal:((b.harga||0)*(b.qty||0))), 0);

        bodyEl.innerHTML = `
          ${head}
          ${renderQuickSearch()}
          <div class="table-responsive">
            <table class="table table-sm mb-2">
              <thead class="thead-light">
                <tr>
                  <th>Produk</th><th class="text-center" style="width:70px">Qty</th>
                  <th class="text-right" style="width:120px">Harga</th>
                  <th class="text-right" style="width:120px">Subtotal</th>
                  <th class="text-right" style="width:60px">Aksi</th>
                </tr>
              </thead>
              <tbody>${rows || `<tr><td colspan="5" class="text-center text-muted py-3">Belum ada item.</td></tr>`}</tbody>
              <tfoot><tr><th colspan="3" class="text-right">Total</th><th class="text-right">${rupiah(total)}</th><th></th></tr></tfoot>
            </table>
          </div>
        `;

        // Bind
        bindDeleteButtons(order.id, bodyEl);
        bodyEl.querySelector('#btnPayCash')?.addEventListener('click', ()=> payCash(order.id));
        bodyEl.querySelector('#btnQRIS')?.addEventListener('click', ()=> qrisCreate(order.id));
        bodyEl.querySelector('#btnQRISConfirm')?.addEventListener('click', ()=> qrisConfirm(order.id));

        loadCategoriesInto(bodyEl.querySelector('#qs-kategori'));
        initQuickSearch(order.id, bodyEl);

        if (focusAdd){
          const inp = bodyEl.querySelector('#qs-input');
          if (inp){ inp.focus(); }
        }
      })
      .catch(()=> bodyEl.innerHTML = '<div class="text-danger p-3">Koneksi bermasalah.</div>');
  }

  // ===== Quick Search block (HTML)
  function renderQuickSearch(){
    return `
      <div id="quick-search-wrap" class="card mb-2">
        <div class="card-body p-2">
          <div class="form-row align-items-center">
            <div class="col-md-5 mb-2">
              <input type="text" id="qs-input" class="form-control form-control-sm" placeholder="Cari nama produk…">
            </div>
            <div class="col-md-4 mb-2">
              <select id="qs-kategori" class="form-control form-control-sm">
                <option value="">Semua Kategori</option>
              </select>
            </div>
            <div class="col-md-3 mb-2 text-right">
              <button id="qs-refresh" class="btn btn-sm btn-outline-secondary">Refresh</button>
            </div>
          </div>
          <div id="qs-result" class="mt-2" style="max-height:240px; overflow:auto;">
            <div class="text-muted small py-2 px-1">Ketik untuk mencari produk…</div>
          </div>
        </div>
      </div>
    `;
  }

  // ===== Helpers
  function bindDeleteButtons(orderId, scope){
    scope.querySelectorAll('[data-del]').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const itemId = parseInt(btn.getAttribute('data-del')||'0',10);
        if (!itemId) return;
        Swal.fire({ title:'Hapus item?', icon:'warning', showCancelButton:true, confirmButtonText:'Hapus', cancelButtonText:'Batal' })
          .then(res=>{
            if (!res.isConfirmed) return;
            const form = new FormData();
            form.append('order_id', orderId);
            form.append('order_item_id', itemId);
            fetch(api.removeItem, { method:'POST', body:form })
              .then(r=>r.json())
              .then(j=>{
                if (!j || !j.success){ Swal.fire('Gagal', (j && (j.pesan||j.message)) || 'Tidak bisa menghapus item', 'error'); return; }
                openOrderModalForKode(window.openKode, false);
              })
              .catch(()=> Swal.fire('Gagal', 'Koneksi bermasalah', 'error'));
          });
      });
    });
  }

  function loadCategoriesInto(selectEl){
    if (!selectEl) return;
    fetch(api.categories, {cache:'no-store'})
      .then(r=>r.json())
      .then(j=>{
        if (!j || !j.success) return;
        const list = j.categories || j.data || [];
        list.forEach(k=>{
          const opt = document.createElement('option');
          opt.value = k.id; opt.textContent = k.nama;
          selectEl.appendChild(opt);
        });
      })
      .catch(()=>{});
  }

  function initQuickSearch(orderIdOrNull, scope){
    let currentOrderId = orderIdOrNull || null; // jika null, create saat add pertama
    const $q = scope.querySelector('#qs-input');
    const $k = scope.querySelector('#qs-kategori');
    const $r = scope.querySelector('#qs-result');
    const $ref = scope.querySelector('#qs-refresh');

    let typingTimer=null;
    function debounced(){ if (typingTimer) clearTimeout(typingTimer); typingTimer=setTimeout(runSearch,280); }
    $q.addEventListener('input', debounced);
    $k.addEventListener('change', runSearch);

    // Refresh di modal: kosongkan input & hasil
    $ref.addEventListener('click', ()=>{
      if ($q) $q.value = '';
      if ($k) $k.value = '';
      if ($r) $r.innerHTML = '<div class="text-muted small py-2 px-1">Ketik untuk mencari produk…</div>';
      $q && $q.focus();
    });

    function runSearch(){
      const name = ($q.value||'').trim();
      const cat  = ($k.value||'').trim();
      if (!name && !cat){
        $r.innerHTML = '<div class="text-muted small py-2 px-1">Ketik nama produk atau pilih kategori.</div>';
        return;
      }
      const url = api.products + `?q=${encodeURIComponent(name)}&category_id=${encodeURIComponent(cat)}`;
      $r.innerHTML = '<div class="text-muted small py-2 px-1">Mencari…</div>';
      fetch(url, {cache:'no-store'})
        .then(r=>r.json())
        .then(j=>{
          if (!j || !j.success){ $r.innerHTML = '<div class="text-danger small py-2 px-1">Gagal memuat hasil.</div>'; return; }
          const list = j.products || j.rows || [];
          if (!list.length){ $r.innerHTML = '<div class="text-muted small py-2 px-1">Tidak ada hasil.</div>'; return; }
          const html = list.map(p=>{
            const id   = p.id;
            const nama = p.nama;
            const harga= p.harga_fmt || rupiah(p.harga||0);
            return `
              <div class="item">
                <div>
                  <div class="name">${escapeHtml(nama)}</div>
                  <div class="meta">${escapeHtml(harga)}</div>
                </div>
                <div>
                  <div class="input-group input-group-sm">
                    <input type="number" min="1" value="1" class="form-control form-control-sm" style="width:70px" data-qty-for="${id}">
                    <div class="input-group-append">
                      <button class="btn btn-outline-primary" data-add="${id}">Tambah</button>
                    </div>
                  </div>
                </div>
              </div>
            `;
          }).join('');
          $r.innerHTML = html;

          // bind add
          $r.querySelectorAll('[data-add]').forEach(btn=>{
            btn.addEventListener('click', async ()=>{
              const pid = btn.getAttribute('data-add');
              const qtyEl = $r.querySelector(`[data-qty-for="${pid}"]`);
              const qty = Math.max(1, parseInt(qtyEl && qtyEl.value || '1',10));
              btn.disabled = true;
              try{
                // kalau belum ada order, buat dulu
                if (!currentOrderId){
                  const custName = (scope.querySelector('#custNameEmpty')?.value || '').trim();
                  const fd = new FormData();
                  fd.append('kode', window.openKode || '');
                  fd.append('nama', custName);
                  const cr = await fetch(api.createOrder, { method:'POST', body: fd });
                  const cj = await cr.json();
                  if (!cj || !cj.success || !cj.order || !cj.order.id){
                    Swal.fire('Gagal', (cj && (cj.pesan||cj.message)) || 'Gagal membuat order', 'error');
                    btn.disabled = false; return;
                  }
                  currentOrderId = cj.order.id;
                  // update tombol cetak
                  document.getElementById('btnCetakKitchen').href = api.printKitchen + '/' + encodeURIComponent(currentOrderId);
                  document.getElementById('btnCetakBar').href     = api.printBar + '/' + encodeURIComponent(currentOrderId);
                  document.getElementById('btnCetakMeja').href    = api.printTable + '/' + encodeURIComponent(currentOrderId);
                  document.getElementById('btnCetakStruk').href   = api.printReceipt + '/' + encodeURIComponent(currentOrderId);
                }
                await quickAdd(currentOrderId, pid, qty, btn);
              } finally{
                btn.disabled = false;
              }
            });
          });
        })
        .catch(()=> $r.innerHTML = '<div class="text-danger small py-2 px-1">Koneksi bermasalah.</div>');
    }
  }

  async function quickAdd(orderId, produkId, qty, btn){
    const form = new FormData();
    form.append('order_id', orderId);
    form.append('produk_id', produkId);
    form.append('qty', qty||1);
    const r = await fetch(api.addItem, { method:'POST', body:form });
    const j = await r.json();
    if (!j || !j.success){
      Swal.fire('Gagal', (j && (j.pesan||j.message)) || 'Gagal menambah item', 'error');
      return;
    }
    toastMini('Ditambahkan');
    openOrderModalForKode(window.openKode, false);
  }

  function payCash(orderId){
    const form = new FormData();
    form.append('order_id', orderId);
    fetch(api.payCash, { method:'POST', body:form })
      .then(r=>r.json())
      .then(j=>{
        if (!j || !j.success){
          Swal.fire('Gagal', (j && (j.pesan||j.message)) || 'Gagal menyimpan pembayaran', 'error'); 
          return;
        }
        $('#modalOrder').modal('hide');
        loadTables(true);
        window.open(api.printReceipt + '/' + encodeURIComponent(orderId), '_blank');
        toastMini('Pembayaran tunai tersimpan');
      })
      .catch(()=> Swal.fire('Gagal', 'Koneksi bermasalah', 'error'));
  }

  function qrisCreate(orderId){
    const form = new FormData();
    form.append('order_id', orderId);
    fetch(api.qrisCreate, { method:'POST', body:form })
      .then(r=>r.json())
      .then(j=>{
        if (!j || !j.success){ Swal.fire('Gagal', (j && (j.pesan||j.message)) || 'Gagal buat QRIS', 'error'); return; }
        const box = document.getElementById('qrisBox');
        const img = document.getElementById('qrisImg');
        const amt = document.getElementById('qrisAmt');
        img.src = j.qr_url; amt.textContent = j.amount_fmt || rupiah(j.amount||0);
        box.style.display = 'block';
      })
      .catch(()=> Swal.fire('Gagal', 'Koneksi bermasalah', 'error'));
  }

  function qrisConfirm(orderId){
    const form = new FormData();
    form.append('order_id', orderId);
    fetch(api.qrisConfirm, { method:'POST', body:form })
      .then(r=>r.json())
      .then(j=>{
        if (!j || !j.success){
          Swal.fire('Gagal', (j && (j.pesan||j.message)) || 'Konfirmasi gagal', 'error'); 
          return;
        }
        $('#modalOrder').modal('hide');
        loadTables(true);
        window.open(api.printReceipt + '/' + encodeURIComponent(orderId), '_blank');
        toastMini('Pembayaran QRIS ditandai lunas');
      })
      .catch(()=> Swal.fire('Gagal', 'Koneksi bermasalah', 'error'));
  }

  // ===== Utils
  function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m])); }
  const fmtRp = n => 'Rp ' + (parseInt(n||0)).toString().replace(/\B(?=(\d{3})+(?!\d))/g,'.');
  function rupiah(n){ return fmtRp(n); }

  function toastMini(msg){
    if (window.Swal && Swal.fire){
      Swal.fire({toast:true, position:'top-end', showConfirmButton:false, timer:1100, timerProgressBar:true, icon:'success', title: msg});
    } else { alert(msg); }
  }

  // reload meja setiap kali modal ditutup
  $('#modalOrder').on('hidden.bs.modal', function () {
    window.openKode = null;
    loadTables(true);
  });
})();
</script>
