<?php $this->load->view('admin/_partials/header'); // atau sesuai template-mu ?>
<div class="row">
  <div class="col-12">
    <div class="card-box">
      <div class="d-flex align-items-center mb-3">
        <h4 class="header-title mb-0">POS Kasir</h4>
        <div class="ml-auto d-flex gap-2">
          <input id="q" type="search" class="form-control form-control-sm" placeholder="Cari nomor/nama/meja…">
          <select id="fstatus" class="form-control form-control-sm ml-2">
            <option value="">Semua Status</option>
            <option value="pending">Pending</option>
            <option value="verifikasi">Verifikasi</option>
            <option value="paid">Paid</option>
            <option value="canceled">Canceled</option>
          </select>
          <button id="btn-add" class="btn btn-sm btn-primary ml-2">
            <i class="mdi mdi-plus"></i> Tambah Order
          </button>
        </div>
      </div>

      <!-- TABEL sesuai template -->
      <div class="table-responsive">
        <table class="table table-centered table-hover mb-0" id="tbl-orders">
          <thead>
            <tr>
              <th class="border-top-0">Name</th>
              <th class="border-top-0">Mode</th>
              <th class="border-top-0">Date</th>
              <th class="border-top-0">Amount</th>
              <th class="border-top-0">Status</th>
              <th class="border-top-0" style="width:180px">Actions</th>
            </tr>
          </thead>
          <tbody><!-- rows via JS --></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Tambah Order (kasir) -->
<div class="modal fade" id="mdlCreate" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Buat Order (Kasir)</h5>
        <button class="close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <!-- Step 1: Data umum -->
        <div class="form-row">
          <div class="form-group col-md-3">
            <label>Mode</label>
            <select id="ord-mode" class="form-control">
              <option value="walkin">Walk-in</option>
              <option value="dinein">Dine-in</option>
              <option value="delivery">Delivery</option>
            </select>
          </div>
          <div class="form-group col-md-3 d-none" id="wrap-meja-kode">
            <label>Kode Meja</label>
            <input type="text" id="ord-meja-kode" class="form-control" placeholder="M01">
          </div>
          <div class="form-group col-md-3 d-none" id="wrap-meja-nama">
            <label>Nama Meja</label>
            <input type="text" id="ord-meja-nama" class="form-control" placeholder="Meja A">
          </div>
          <div class="form-group col-md-3">
            <label>Atas Nama</label>
            <input type="text" id="ord-nama" class="form-control" placeholder="Nama pelanggan" required>
          </div>
          <div class="form-group col-md-12">
            <label>Catatan</label>
            <input type="text" id="ord-catatan" class="form-control" placeholder="Opsional">
          </div>
        </div>

        <hr>

        <!-- Step 2: Cari Produk & keranjang kecil -->
        <div class="form-row">
          <div class="form-group col-md-6">
            <label>Cari Produk</label>
            <div class="input-group">
              <input type="search" id="prod-q" class="form-control" placeholder="Ketik nama produk…">
              <div class="input-group-append">
                <button id="btn-prod-search" class="btn btn-light" type="button"><i class="mdi mdi-magnify"></i></button>
              </div>
            </div>
            <div id="prod-results" class="mt-2" style="max-height:300px; overflow:auto;">
              <!-- list hasil -->
            </div>
          </div>
          <div class="form-group col-md-6">
            <label>Keranjang</label>
            <div class="table-responsive" style="max-height:300px; overflow:auto;">
              <table class="table table-sm table-bordered mb-2" id="tbl-cart">
                <thead>
                  <tr><th>Produk</th><th style="width:80px" class="text-center">Qty</th><th class="text-right" style="width:120px">Harga</th><th class="text-right" style="width:120px">Subtotal</th><th style="width:40px"></th></tr>
                </thead>
                <tbody><!-- items --></tbody>
                <tfoot>
                  <tr>
                    <th colspan="3" class="text-right">Total</th>
                    <th class="text-right" id="cart-total">Rp 0</th>
                    <th></th>
                  </tr>
                </tfoot>
              </table>
            </div>

            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Metode Bayar</label>
                <select id="ord-pay" class="form-control">
                  <option value="">(Pilih saat closing)</option>
                  <option value="cash">Cash</option>
                  <option value="qris">QRIS</option>
                  <option value="transfer">Transfer</option>
                </select>
              </div>
              <div class="form-group col-md-6 d-flex align-items-end justify-content-end">
                <button id="btn-create-submit" class="btn btn-primary">
                  <i class="mdi mdi-check-bold"></i> Buat Order
                </button>
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Modal: Detail Order -->
<div class="modal fade" id="mdlDetail" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="mdlDetailTitle" class="modal-title">Detail Pesanan</h5>
        <button class="close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button>
      </div>
      <div id="mdlDetailBody" class="modal-body">
        <div class="py-5 text-center text-muted">Memuat…</div>
      </div>
    </div>
  </div>
</div>

<?php $this->load->view('admin/_partials/footer'); // atau sesuai template-mu ?>

<script>
(function(){
  /* ===== utils fmt ===== */
  function fmtRupiah(n){ n = Number(n||0); return 'Rp ' + n.toLocaleString('id-ID'); }

  /* ===== LIST TABLE ===== */
  const $tblBody = document.querySelector('#tbl-orders tbody');
  const $q = document.getElementById('q');
  const $fstatus = document.getElementById('fstatus');

  function badgeStatus(s){
    const map = {
      'paid':'badge-success',
      'canceled':'badge-danger',
      'verifikasi':'badge-warning',
      'pending':'badge-secondary'
    };
    const cls = map[String(s||'').toLowerCase()] || 'badge-light';
    return `<span class="badge badge-pill ${cls}">${(s||'-')}</span>`;
  }

  function rowActions(r){
    const paidDisabled = (String(r.status).toLowerCase()==='paid') ? 'disabled' : '';
    const cancelDisabled = (String(r.status).toLowerCase()==='canceled') ? 'disabled' : '';
    return `
      <button class="btn btn-xs btn-info act-detail" data-id="${r.id}"><i class="mdi mdi-eye"></i></button>
      <button class="btn btn-xs btn-success act-paid ml-1" data-id="${r.id}" ${paidDisabled}><i class="mdi mdi-cash-check"></i> Paid</button>
      <button class="btn btn-xs btn-danger act-cancel ml-1" data-id="${r.id}" ${cancelDisabled}><i class="mdi mdi-close-circle"></i> Cancel</button>
    `;
  }

  function renderRows(rows){
    $tblBody.innerHTML = rows.map(r => `
      <tr>
        <td>
          <img src="${r.avatar||'<?php echo base_url('assets/images/users/user-1.jpg'); ?>'}" class="rounded-circle avatar-sm bx-shadow-lg" alt="user">
          <span class="ml-2">${r.customer}</span>
        </td>
        <td><span class="badge badge-info">${r.mode_badge}</span> ${r.meja_nama?('- '+r.meja_nama): (r.meja_kode?('- '+r.meja_kode):'')}</td>
        <td>${r.date_fmt}</td>
        <td>${r.amount_fmt}</td>
        <td>${badgeStatus(r.status)}</td>
        <td>${rowActions(r)}</td>
      </tr>
    `).join('');
  }

  function loadList(){
    const p = new URLSearchParams();
    if ($q.value.trim() !== '') p.set('q',$q.value.trim());
    if ($fstatus.value !== '') p.set('status',$fstatus.value);
    fetch('<?php echo site_url('admin_pos/list_json'); ?>?'+p.toString(), {credentials:'same-origin'})
      .then(r=>r.json())
      .then(j=>{ if(j && j.success){ renderRows(j.data || []);} });
  }

  $q.addEventListener('input', debounce(loadList, 300));
  $fstatus.addEventListener('change', loadList);

  function debounce(fn, t){ let iv=null; return function(){ clearTimeout(iv); iv=setTimeout(fn, t||300); }; }

  /* ===== DETAIL MODAL ===== */
  const $mdlDetail = $('#mdlDetail');
  $(document).on('click','.act-detail', function(){
    const id = this.getAttribute('data-id');
    $('#mdlDetailTitle').text('Detail Pesanan #'+id);
    $('#mdlDetailBody').html('<div class="py-5 text-center text-muted">Memuat…</div>');
    $mdlDetail.modal('show');
    fetch('<?php echo site_url('admin_pos/detail_modal'); ?>/'+id, {credentials:'same-origin'})
      .then(r=>r.json())
      .then(j=>{
        if (j && j.success){ $('#mdlDetailBody').html(j.html); }
        else { $('#mdlDetailBody').html('<div class="text-danger p-3">Gagal memuat</div>'); }
      });
  });

  /* ===== ACTION: PAID / CANCEL ===== */
  $(document).on('click','.act-paid', function(){
    const id = this.getAttribute('data-id');
    Swal.fire({title:'Tandai LUNAS?', icon:'question', showCancelButton:true, confirmButtonText:'Ya, Paid'}).then(s=>{
      if (!s.isConfirmed) return;
      fetch('<?php echo site_url('admin_pos/mark_paid'); ?>/'+id, {method:'POST', credentials:'same-origin'})
        .then(r=>r.json()).then(j=>{
          if (j && j.success){ Swal.fire('OK','Order ditandai LUNAS','success'); loadList(); }
          else { Swal.fire('Gagal', (j && j.pesan)||'Error', 'error'); }
        });
    });
  });

  $(document).on('click','.act-cancel', function(){
    const id = this.getAttribute('data-id');
    Swal.fire({title:'Batalkan order?', icon:'warning', showCancelButton:true, confirmButtonText:'Ya, Cancel'}).then(s=>{
      if (!s.isConfirmed) return;
      const fd = new FormData(); fd.append('status','canceled');
      fetch('<?php echo site_url('admin_pos/set_status'); ?>/'+id, {method:'POST', body:fd, credentials:'same-origin'})
        .then(r=>r.json()).then(j=>{
          if (j && j.success){ Swal.fire('OK','Order dibatalkan','success'); loadList(); }
          else { Swal.fire('Gagal', (j && j.pesan)||'Error', 'error'); }
        });
    });
  });

  /* ===== CREATE MODAL (kasir) ===== */
  const $mdlCreate = $('#mdlCreate');
  const $mode = $('#ord-mode'), $wrapKode=$('#wrap-meja-kode'), $wrapNama=$('#wrap-meja-nama');

  function toggleMeja(){
    const v = $mode.val();
    if (v === 'dinein'){ $wrapKode.removeClass('d-none'); $wrapNama.removeClass('d-none'); }
    else{ $wrapKode.addClass('d-none'); $wrapNama.addClass('d-none'); }
  }
  $mode.on('change', toggleMeja); toggleMeja();

  // cart state
  let cart = []; // {id, nama, qty, harga}

  function renderCart(){
    const $tb = $('#tbl-cart tbody');
    let tot = 0;
    $tb.html(cart.map((it,idx)=>{
      const sub = it.qty * it.harga; tot += sub;
      return `<tr>
        <td>${it.nama}</td>
        <td class="text-center">
          <input type="number" min="1" class="form-control form-control-sm cart-qty" data-idx="${idx}" value="${it.qty}">
        </td>
        <td class="text-right">${fmtRupiah(it.harga)}</td>
        <td class="text-right">${fmtRupiah(sub)}</td>
        <td class="text-center"><button class="btn btn-sm btn-outline-danger cart-del" data-idx="${idx}"><i class="mdi mdi-delete"></i></button></td>
      </tr>`;
    }).join(''));
    $('#cart-total').text(fmtRupiah(tot));
  }

  $(document).on('input','.cart-qty', function(){
    const idx = +this.getAttribute('data-idx');
    let v = parseInt(this.value || 1, 10);
    if (v < 1) v = 1;
    cart[idx].qty = v;
    renderCart();
  });
  $(document).on('click','.cart-del', function(){
    const idx = +this.getAttribute('data-idx');
    cart.splice(idx,1); renderCart();
  });

  // product search
  function renderProducts(list){
    const html = list.map(p=>`
      <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-1">
        <div class="d-flex align-items-center">
          <img src="${p.gambar ? '<?php echo base_url(); ?>'+p.gambar : '<?php echo base_url('assets/images/icon_app.png'); ?>'}" class="rounded mr-2" style="width:40px;height:40px;object-fit:cover">
          <div>
            <div class="font-weight-600">${p.nama}</div>
            <small class="text-muted">${fmtRupiah(p.harga)}</small>
          </div>
        </div>
        <button class="btn btn-sm btn-outline-primary btn-add-prod" data-id="${p.id}" data-nama="${$('<div>').text(p.nama).html()}" data-harga="${p.harga}"><i class="mdi mdi-plus"></i></button>
      </div>
    `).join('');
    $('#prod-results').html(html || '<div class="text-muted">Tidak ada hasil</div>');
  }

  function searchProducts(){
    const q = ($('#prod-q').val()||'').trim();
    const p = new URLSearchParams(); p.set('q', q);
    fetch('<?php echo site_url('api/products/search'); ?>?'+p.toString(), {credentials:'same-origin'})
      .then(r=>r.json()).then(j=>{
        if (j && j.success) renderProducts(j.data||[]);
        else $('#prod-results').html('<div class="text-danger">Gagal mencari</div>');
      });
  }
  // — NOTE: Buat endpoint GET api/products/search → balas {success:true,data:[{id,nama,harga,gambar}]}
  //   Jika belum ada, kamu bisa route ke method yang call $this->mp->search_products($q)

  $('#btn-prod-search').on('click', searchProducts);
  $('#prod-q').on('keydown', function(e){ if(e.key==='Enter'){ e.preventDefault(); searchProducts(); } });

  $(document).on('click','.btn-add-prod', function(){
    const id = +this.getAttribute('data-id');
    const nama = this.getAttribute('data-nama');
    const harga = +this.getAttribute('data-harga');
    const exist = cart.find(x=>x.id===id);
    if (exist){ exist.qty += 1; }
    else { cart.push({id, nama, qty:1, harga}); }
    renderCart();
  });

  // open modal create
  $('#btn-add').on('click', function(){
    cart = [];
    renderCart();
    $('#prod-q').val('');
    $('#prod-results').html('');
    $('#ord-nama').val('');
    $('#ord-catatan').val('');
    $('#ord-meja-kode').val('');
    $('#ord-meja-nama').val('');
    $('#ord-pay').val('');
    $mdlCreate.modal('show');
  });

  // submit create
  $('#btn-create-submit').on('click', function(){
    const payload = new FormData();
    const mode = $('#ord-mode').val();
    const nama = ($('#ord-nama').val()||'').trim();
    if (!nama){ Swal.fire('Nama wajib','Isi nama pelanggan dulu ya.','warning'); return; }

    payload.append('mode', mode);
    payload.append('nama', nama);
    payload.append('catatan', ($('#ord-catatan').val()||'').trim());
    if (mode==='dinein'){
      payload.append('meja_kode', ($('#ord-meja-kode').val()||'').trim());
      payload.append('meja_nama', ($('#ord-meja-nama').val()||'').trim());
    }
    payload.append('pay_method', $('#ord-pay').val()||'');

    cart.forEach((it,i)=>{
      payload.append(`items[${i}][produk_id]`, it.id);
      payload.append(`items[${i}][qty]`, it.qty);
      payload.append(`items[${i}][harga]`, it.harga);
    });

    Swal.fire({title:'Membuat order…', allowOutsideClick:false, didOpen:()=>Swal.showLoading()});
    fetch('<?php echo site_url('admin_pos/create'); ?>', {method:'POST', body: payload, credentials:'same-origin'})
      .then(r=>r.json()).then(j=>{
        Swal.close();
        if (j && j.success){
          Swal.fire('Sip!','Order dibuat.','success');
          $mdlCreate.modal('hide');
          loadList();
          // opsional: redirect ke ringkasan
          // if (j.redirect) window.open(j.redirect,'_blank');
        } else {
          Swal.fire('Gagal', (j && j.pesan)||'Error', 'error');
        }
      }).catch(()=>{ Swal.close(); Swal.fire('Error','Koneksi bermasalah.','error'); });
  });

  // init
  loadList();
})();
</script>
