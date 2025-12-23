<?php $this->load->view("front_end/head.php"); ?>
<style>
  .actions-wrap{ display:flex; align-items:center; justify-content:space-between; gap:.5rem; }
  @media (max-width: 767.98px){
    .actions-wrap{ flex-direction: row !important; }
    .actions-wrap .btn{ flex:1 1 0; }
  }

  .item-card{ border:1px solid #eef2f7; border-radius:12px; padding:.75rem; margin-bottom:.5rem; }
  .item-card .row{ align-items:center; }
  .price, .subtotal{ font-weight:600; }
  .muted{ color:#6c757d; font-size:.9rem; }

  .card.card-body{
    border:1px solid rgba(2,6,23,.06);
    border-radius:12px;
    box-shadow:0 8px 20px rgba(16,24,40,.04);
    background:#fff;
  }
  /*.btn-blue{ background:#2663eb; border-color:#2663eb; color:#fff; }*/
  /*.btn-blue:hover{ background:#1f54c8; border-color:#1f54c8; color:#fff; }*/

  .input-group .input-group-text{
    border-top-left-radius:10px; border-bottom-left-radius:10px;
    background:#ecf2ff; color:#1e293b; border-color:#c7d2fe;
  }
  .form-control{ border-radius:10px; border-color:#e5e7eb; }
  .form-control:focus{ border-color:#c7d2fe; box-shadow:0 0 0 .2rem rgba(37,99,235,.15); }

  #catatanHelp{ display:flex; justify-content:space-between; align-items:center; }
  #catatanHelp .hint{ color:#6b7280; }

  .table thead th{ border-top:0; background:#f1f5f9; color:#0f172a; font-weight:700; }
  .table tbody tr:hover{ background:#fcfdff; }

  .badge-danger, .badge-secondary, .badge-info{
    padding:.5rem .7rem; border-radius:999px; font-weight:700;
    box-shadow:0 6px 12px rgba(2,6,23,.06);
  }
  .badge-info{ background:#17a2b8; }
</style>

<div class="container-fluid">
  <!-- <div class="hero-title" role="banner" aria-label="Judul halaman">
    <h1 class="text">Konfirmasi Pesanan</h1>
    <span class="accent" aria-hidden="true"></span>
  </div> -->

    <div class="hero-title ausi-hero-center" role="banner" aria-label="Judul halaman">
    <i class="  ti-arrow-left ausi-btn-back" onclick="ausiBack()"></i>
    
    <style type="text/css">
      .ausi-hero-center{
        position: relative;
        text-align: center !important;   /* pastikan title/subtitle center */
        padding: 24px 0 14px;
      }
      .ausi-btn-back{
        position: absolute;
        left: 0px;                            
        width: 30px; height: 30px;
        display: inline-flex; align-items: center; justify-content: center;
        color: #fff;
        font-weight: 700;
        font-size: 18px;
      }
    </style>
    <script>
      function ausiBack(){
        window.location.href = "<?= site_url("produk/cart") ?>";
      }
    </script>
    <h1 class="text mb-1">Konfirmasi Pesanan</h1>
    <span class="accent" aria-hidden="true"></span>
  </div>


<?php $this->load->view("judul_mode") ?>
  <div class="card card-body">
    <?php if (empty($items)): ?>
      <div class="text-center py-5">
        <h5>Keranjang kosong</h5>
        <a href="<?= site_url('produk') ?>" class="btn btn-primary mt-3">
          <i class="mdi mdi-arrow-left"></i> Kembali ke Menu
        </a>
      </div>
    <?php else: ?>

      <!-- Tabel (desktop & tablet) -->
      <div class="table-responsive d-none d-md-block">
        <table class="table table-centered table-striped mb-0">
          <thead class="thead-light">
            <tr>
              <th>Produk</th>
              <th class="text-center" style="width:120px">Qty</th>
              <th class="text-right" style="width:140px">Harga</th>
              <th class="text-right" style="width:160px">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($items as $it): $harga=(int)$it->harga; $qty=(int)$it->qty; ?>
              <tr>
                <td><?= html_escape($it->nama ?? '') ?></td>
                <td class="text-center"><?= $qty ?></td>
                <td class="text-right">Rp <?= number_format($harga,0,',','.') ?></td>
                <td class="text-right">Rp <?= number_format($harga*$qty,0,',','.') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="3" class="text-right"><strong>Total</strong></th>
              <th class="text-right"><strong>Rp <?= number_format((int)$total,0,',','.') ?></strong></th>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Kartu (mobile) -->
      <div class="d-md-none">
        <?php foreach($items as $it):
          $nama  = $it->nama ?? '';
          $harga = (int)$it->harga; $qty = (int)$it->qty; $sub = $harga*$qty;
        ?>
          <div class="item-card">
            <div class="row">
              <div class="col-8">
                <div class="font-weight-600"><?= html_escape($nama) ?></div>
                <div class="muted">Harga: Rp <?= number_format($harga,0,',','.') ?></div>
                <div class="muted">Jumlah: <strong><?= $qty ?></strong></div>
              </div>
              <div class="col-4 text-right">
                <div class="subtotal">Rp <?= number_format($sub,0,',','.') ?></div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>

        <div class="d-flex justify-content-between border-top pt-2 mt-2">
          <div class="font-weight-600"><strong>Total</strong></div>
          <div class="font-weight-700"><strong>Rp <?= number_format((int)$total,0,',','.') ?></strong></div>
        </div>
      </div>

      <hr>

      <form id="form-order" onsubmit="return false;">
        <div class="form-row">
          <div class="form-group col-md-6">
            <label><strong>Nama</strong> </label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text" id="nama-addon">
                  <i class="mdi mdi-account" aria-hidden="true"></i>
                </span>
              </div>
              <input type="text" class="form-control" name="nama" placeholder="Nama Wajib" autocomplete="on" required aria-describedby="nama-addon">
            </div>
          </div>
          <div class="form-group col-md-6">
          
            <label class="d-flex align-items-center">
              <span><strong>No. WhatsApp</strong> <small class="text-muted ml-1">(opsional)</small></span>

           <!--    <span class="help-icon pretty"
              data-toggle="tooltip"
              data-placement="right"
              title="Masukkan No. WA agar berkesempatan mendapatkan voucher mingguan.">
              ?
            </span> -->
              </label>

              <input type="tel"
              class="form-control"
              name="phone"
              placeholder="08xxxxxxxxxx"
              autocomplete="tel"
              required>
              <small class="text-muted">
                Masukkan No. WA untuk undian voucher mingguan.
              </small>
            </div>

            <!-- EMAIL (opsional) -->
<!-- <div class="form-group col-md-6">
  <label class="d-flex align-items-center">
    <span>Email <small class="text-muted ml-1">(opsional)</small></span> -->
   <!--  <span class="help-icon pretty"
          data-toggle="tooltip"
          data-placement="right"
          title="Jika diisi, kami info promo menarik ke email ini.">?</span> -->
<!--   </label>
  <input type="email"
         class="form-control"
         name="email"
         placeholder="nama@contoh.com"
         autocomplete="email">
</div> -->




          <div class="form-group col-md-6">
            <label for="catatan"><strong>Catatan</strong></label>
            <textarea class="form-control" id="catatan" name="catatan" rows="2" placeholder="Tanpa gula / pedas / bungkus / dll" autocomplete="off" maxlength="255" aria-describedby="catatanHelp"></textarea>
            <small id="catatanHelp" class="form-text">
              <span class="hint">Maks. 255 karakter</span>
              <span><span id="catatan-count">0</span>/255</span>
            </small>
          </div>

<!-- KODE VOUCHER (opsional) -->
<div class="form-group col-md-6">
  <label class="d-flex align-items-center">
    <span><strong>Kode Voucher</strong> <small class="text-muted ml-1">(opsional)</small></span>
  <!--   <span class="help-icon pretty"
          data-toggle="tooltip"
          data-placement="right"
          title="Masukkan kode voucher promo yang kamu punya.">
      ?
    </span> -->
  </label>
  <div class="input-group">
    <input type="text"
           class="form-control"
           name="voucher_code"
           id="voucher_code"
           placeholder="Contoh: CAF250115001"
           autocomplete="off"
           style="text-transform:uppercase;">
    <div class="input-group-append">
      <button class="btn btn-outline-secondary" type="button" id="btn-clear-voucher">
        Hapus
      </button>
    </div>
  </div>
  <small class="text-muted">
    Biarkan kosong jika tidak punya voucher.
  </small>
</div>


          <style type="text/css">
            /* ===== Pay Methods (beautify) ===== */
.pay-methods .pay-grid{
  display:grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  /*gap:.6rem;*/
}

@media (max-width: 767.98px){
  .pay-methods .pay-grid{
    grid-template-columns: 1fr; /* mobile: 1 kolom biar enak */
  }
}

.pay-pill{
  position:relative;
  display:flex;
  align-items:center;
  gap:.65rem;
  padding:.75rem .85rem;
  border:1px solid rgba(2,6,23,.10);
  border-radius:14px;
  background:rgba(255,255,255,.92);
  box-shadow:0 10px 24px rgba(16,24,40,.05);
  cursor:pointer;
  user-select:none;
  transition:transform .08s ease, box-shadow .12s ease, border-color .12s ease, background .12s ease;
  min-height:60px;
}

.pay-pill:hover{
  border-color: rgba(37,99,235,.25);
  box-shadow:0 12px 28px rgba(37,99,235,.10);
}

.pay-pill:active{ transform:scale(.99); }

.pay-pill input[type="radio"]{
  position:absolute;
  opacity:0;
  pointer-events:none;
}

.pay-icon{
  width:40px; height:40px;
  border-radius:12px;
  display:flex; align-items:center; justify-content:center;
  background:rgba(37,99,235,.10);
  color:#1e40af;
  flex-shrink:0;
  box-shadow: inset 0 0 0 1px rgba(37,99,235,.12);
}
.pay-icon i{ font-size:20px; line-height:1; }

.pay-text{ line-height:1.15; min-width:0; }
.pay-title{
  display:block;
  font-weight:800;
  color:#0f172a;
  font-size:.98rem;
}
.pay-sub{
  display:block;
  font-size:.8rem;
  color:#6b7280;
  margin-top:.15rem;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}

.pay-check{
  margin-left:auto;
  width:22px; height:22px;
  border-radius:999px;
  display:flex; align-items:center; justify-content:center;
  background:rgba(15,23,42,.06);
  color:transparent;
  box-shadow: inset 0 0 0 1px rgba(2,6,23,.10);
  transition:all .12s ease;
}
.pay-check i{ font-size:14px; }

/* Selected state */
.pay-pill input:checked ~ .pay-icon{
  background:linear-gradient(135deg, rgba(37,99,235,.18), rgba(79,70,229,.14));
  color:#1d4ed8;
  box-shadow: inset 0 0 0 1px rgba(37,99,235,.25);
}

.pay-pill input:checked ~ .pay-text .pay-title{
  color:#0b2b8a;
}

.pay-pill input:checked ~ .pay-check{
  background:linear-gradient(135deg,#ff6306,#ff2424);
  color:#fff;
  box-shadow:0 10px 20px rgba(34,197,94,.22);
}

.pay-pill:has(input:checked){
  border-color: rgba(37,99,235,.35);
  background:rgba(239,246,255,.75);
  box-shadow:0 14px 30px rgba(37,99,235,.12);
}

          </style>
        <!-- METODE PEMBAYARAN -->
        <div class="form-group col-12 pay-methods">
          <div class="d-flex align-items-end justify-content-between mb-2">
            <label class="m-0 font-weight-700"><strong>Metode Pembayaran</strong></label>
            <small class="text-muted">Wajib dipilih</small>
          </div>

          <div class="pay-grid">
            <label class="pay-pill" for="pm_cash">
              <input type="radio" id="pm_cash" value="cash" name="paid_method" required>
              <span class="pay-icon" aria-hidden="true">
                <i class="mdi mdi-cash"></i>
              </span>
              <span class="pay-text">
                <span class="pay-title">Tunai</span>
                <span class="pay-sub">Bayar di kasir</span>
              </span>
              <span class="pay-check" aria-hidden="true">
                <i class="mdi mdi-check"></i>
              </span>
            </label>

            <label class="pay-pill" for="pm_qris">
              <input type="radio" id="pm_qris" value="qris" name="paid_method">
              <span class="pay-icon" aria-hidden="true">
                <i class="mdi mdi-qrcode-scan"></i>
              </span>
              <span class="pay-text">
                <span class="pay-title">QRIS</span>
                <span class="pay-sub">Scan & bayar</span>
              </span>
              <span class="pay-check" aria-hidden="true">
                <i class="mdi mdi-check"></i>
              </span>
            </label>

            <label class="pay-pill" for="pm_transfer">
              <input type="radio" id="pm_transfer" value="transfer" name="paid_method">
              <span class="pay-icon" aria-hidden="true">
                <i class="mdi mdi-bank-transfer"></i>
              </span>
              <span class="pay-text">
                <span class="pay-title">Transfer</span>
                <span class="pay-sub">BNI</span>
              </span>
              <span class="pay-check" aria-hidden="true">
                <i class="mdi mdi-check"></i>
              </span>
            </label>
          </div>

          <small class="text-muted d-block mt-2">
            Pilih salah satu metode di atas untuk lanjut.
          </small>
        </div>




            <?php if ($mode === 'delivery'): ?>
            <?php $this->load->view("ongkir") ?>
            <?php endif; ?>

        </div>
      </form>

    <style>.actions-wrap{background:rgb(15 23 42 / .04);border:1px solid rgb(0 0 0 / .06);border-radius:.9rem;padding:.75rem 1rem;box-shadow:0 .75rem 2rem rgb(0 0 0 / .08)}.btn-blue{display:flex;align-items:center;justify-content:center;width:100%;border:0;border-radius:.75rem;font-size:1rem;font-weight:600;line-height:1.2;padding:.75rem 1rem;color:#fff;background-image:linear-gradient(135deg,#2563eb 0%,#4f46e5 40%,#312e81 100%);box-shadow:0 .5rem 1.25rem rgb(37 99 235 / .45),0 0 20px rgb(99 102 241 / .45) inset;text-shadow:0 1px 2px rgb(0 0 0 / .4);transition:all .12s ease}.btn-blue i{margin-left:.5rem;font-size:1.1rem;line-height:0}.btn-blue:hover,.btn-blue:focus{filter:brightness(1.07);box-shadow:0 .75rem 1.5rem rgb(37 99 235 / .55),0 0 28px rgb(99 102 241 / .6) inset;outline:none}.btn-blue:active{transform:scale(.98);box-shadow:0 .4rem 1rem rgb(0 0 0 / .4),0 0 18px rgb(99 102 241 / .4) inset}@media (max-width:400px){.btn-blue{font-size:.95rem;padding:.7rem .9rem}.btn-blue i{font-size:1rem}}</style>

<div class="d-flex justify-content-between align-items-center actions-wrap mt-1">
  <button id="btn-order" class="btn btn-blue btn-block">
    Order <i class="mdi mdi-basket-fill" aria-hidden="true"></i>
  </button>
</div>


    <?php endif; ?>
  </div>
</div>

<?php $this->load->view("front_end/footer.php"); ?>
<script>
(function(){
  const MODE     = "<?= $mode ?>";
  const IS_KASIR = <?= strtolower((string)$this->session->userdata('admin_username'))==='kasir' ? 'true' : 'false' ?>;
  const mejaInfo = "<?= !empty($meja_info) ? addslashes($meja_info) : '' ?>";

  // ====== Draft persistence (localStorage) ======
  const DRAFT_KEY = `orderFormDraft:v1:${MODE}`; // pisahkan per mode (walkin/delivery/dinein)
  let saveTimer = null;

  function getFormElems() {
    const $form = $('#form-order');
    return {
      $form,
      $nama:    $form.find('input[name="nama"]'),
      $catatan: $form.find('textarea[name="catatan"]'),
      $phone:   $form.find('input[name="phone"]'),
      $email:   $form.find('input[name="email"]'),
      $voucher: $form.find('input[name="voucher_code"]'), // <-- NEW
      $alamat:  $form.find('textarea[name="alamat"]'),
      $ongkir:  $form.find('input[name="ongkir"]'),
      $payMethod: $form.find('input[name="paid_method"]')
    };
  }

  function loadDraft() {
  try{
    const raw = localStorage.getItem(DRAFT_KEY);
    if (!raw) return;
    const d = JSON.parse(raw)||{};
    // const { $form } = getFormElems();
    //   if (d.paid_method != null && d.paid_method !== '') {
    //     $form.find('input[name="paid_method"][value="'+d.paid_method+'"]').prop('checked', true);
    //   }

    const { $nama, $catatan, $phone, $alamat, $ongkir, $email, $voucher } = getFormElems();

    if (d.nama != null)     $nama.val(d.nama);
    if (d.phone != null)     $phone.val(d.phone);
    if (d.email != null)    $email.val(d.email);  // <-- NEW
    if (d.voucher_code != null)$voucher.val(d.voucher_code); // <-- NEW

    if (MODE==='delivery'){
      if (d.phone != null)  $phone.val(d.phone);
      if (d.alamat != null) $alamat.val(d.alamat);
      // if (IS_KASIR && d.ongkir != null) $ongkir.val(d.ongkir);
    }
    $catatan.trigger('input');
  }catch(e){}
}

function saveDraft() {
  const { $form, $nama, $phone, $alamat, $ongkir, $email, $voucher } = getFormElems();

  // const paid_method = ($form.find('input[name="paid_method"]:checked').val() || '').trim();

  const data = {
    nama:  ($nama.val()||'').trim(),
    email: ($email.val()||'').trim(),
    phone: ($phone.val()||'').trim(),
    // voucher_code: ($voucher.val()||'').trim().toUpperCase(),
    // paid_method: paid_method // ✅ NEW
  };

  if (MODE==='delivery'){
    data.phone  = ($phone.val()||'').trim();
    data.alamat = ($alamat.val()||'').trim();
    // if (IS_KASIR) data.ongkir = ($ongkir.val()||'').trim();
  }

  try{ localStorage.setItem(DRAFT_KEY, JSON.stringify(data)); }catch(e){}
}





  function scheduleSaveDraft(){
    if (saveTimer) clearTimeout(saveTimer);
    saveTimer = setTimeout(saveDraft, 250); // debounce 250ms
  }

  function clearDraft(){
    try{ localStorage.removeItem(DRAFT_KEY); }catch(e){}
  }

  // Restore saat load
  $(document).ready(loadDraft);

  $(document).on('click', '#btn-clear-voucher', function(){
  $('#voucher_code').val('');
  if (typeof scheduleSaveDraft === 'function') {
    scheduleSaveDraft();
  }
});
  // Listen perubahan input agar auto-save
  // ⟵ KECUALIKAN #catatan dari trigger penyimpanan
  $(document).on('input change', '#form-order input, #form-order textarea:not(#catatan)', scheduleSaveDraft);
function buildSteps(mode){
  // mode di sini pakai string yang kamu sudah punya:
  // 'dinein', 'delivery', atau 'walkin'

  // Step awal yang selalu relevan
  const list = [
    'Cek datamu bentar… 😎',
    'Lihat kita lagi buka apa nggak… ⏰', // cek jam layanan
  ];

  if (mode === 'delivery') {
    // Step khusus delivery
    list.push('Hitung jarak & ongkirnya… 🚚');
    list.push('Catat alamat & nomor HP kamu… 📍');
  } else if (mode === 'dinein') {
    // Step khusus dine-in (meja)
    list.push('Catat kamu lagi di meja ini… 🍽️');
  } else {
    // fallback = walkin / bungkus
    list.push('Catat pesanan kamu buat dibungkus… 🛍️');
  }
  if ($('input[name="voucher_code"]').val().trim() !== '') {
    list.push('Cek dan validasi kode vouchermu… 🎟️');
  }
  // Step akhir yang sama buat semua mode
  list.push('Bikin nomor order biar resmi… 🧾');
  list.push('Simpan ke sistem kasir… 💾');
  list.push('Kasih info balik ke kamu… 📲');

  return list;
}


const ORDER_KEY = 'ausi_orders';

function detectDeviceBrand(){
  const ua = (navigator.userAgent || '').toLowerCase();
  if (!ua) return 'Perangkat tidak dikenal';

  // Apple
  if (ua.includes('iphone'))   return 'Apple iPhone';
  if (ua.includes('ipad'))     return 'Apple iPad';

  // Brand Android populer di Indonesia
  if (ua.includes('samsung') || ua.includes('sm-')) return 'Samsung';
  if (ua.includes('redmi') || ua.includes('xiaomi') || ua.includes(' mi ')) return 'Xiaomi / Redmi';
  if (ua.includes('oppo'))     return 'OPPO';
  if (ua.includes('vivo'))     return 'vivo';
  if (ua.includes('realme'))   return 'realme';
  if (ua.includes('infinix'))  return 'Infinix';
  if (ua.includes('tecno'))    return 'TECNO';
  if (ua.includes('asus'))     return 'ASUS';
  if (ua.includes('huawei'))   return 'Huawei';
  if (ua.includes('lenovo'))   return 'Lenovo';

  // OS fallback
  if (ua.includes('android'))                      return 'Android (lainnya)';
  if (ua.includes('windows'))                      return 'Windows';
  if (ua.includes('mac os') || ua.includes('macintosh')) return 'macOS';

  return 'Perangkat lain';
}

function ausiSaveOrderToLocal(order){
  try {
    if (!window.localStorage) return;

    let orders = [];
    const raw = localStorage.getItem(ORDER_KEY);
    if (raw) {
      try{
        orders = JSON.parse(raw);
        if (!Array.isArray(orders)) orders = [];
      } catch(e){
        orders = [];
      }
    }

    const nowISO = new Date().toISOString();

    const newOrder = {
      nomor:       order.nomor       || '',
      redirect:    order.redirect    || '',
      created_at:  order.created_at  || nowISO,
      mode:        order.mode        || '',
      status:      order.status      || '',
      grand_total: Number(order.grand_total || 0),
      paid_method: order.paid_method || '',
      device_brand: detectDeviceBrand()
    };

    // kalau nomor sudah ada -> update; kalau belum -> push
    const idx = orders.findIndex(o => o.nomor === newOrder.nomor);
    if (idx >= 0) {
      orders[idx] = newOrder;
    } else {
      orders.push(newOrder);
    }

    // batasi 50 terakhir
    if (orders.length > 50) {
      orders = orders.slice(-50);
    }

    localStorage.setItem(ORDER_KEY, JSON.stringify(orders));
    console.log('Riwayat order tersimpan:', newOrder);
  } catch (e){
    console.warn('Gagal simpan riwayat pesanan ke localStorage', e);
  }
}


  // ====== Submit flow ======
  $('#btn-order').on('click', function(){

    const { $form, $nama, $catatan, $phone, $alamat, $ongkir, $email, $voucher } = getFormElems();
    const email = ($email.val() || '').trim();
    const voucherCode = ($voucher.val() || '').trim().toUpperCase(); // <-- NEW
    const fd    = new FormData($form[0]);
    const paidMethod = ($form.find('input[name="paid_method"]:checked').val() || '').trim();

    if (!paidMethod){
      Swal.fire({
        icon: 'warning',
        title: 'Pilih metode pembayaran dulu 🙏',
        text: 'Silakan pilih Tunai / QRIS / Transfer sebelum lanjut.',
        allowOutsideClick: false
      });
      return;
    }
    fd.set('paid_method', paidMethod); // ✅ kirim ke server

    const nama    = ($nama.val() || '').trim();
    const catatan = ($catatan.val() || '').trim(); // boleh dipakai, tapi TIDAK disimpan
    if (voucherCode) {
      fd.set('voucher_code', voucherCode);
    }
    if (!nama) {
      Swal.fire({
        icon: 'warning',
        title: 'Nama dulu dong 🙏',
        text: 'Biar kru kami tau harus manggil siapa pas order jadi.',
        allowOutsideClick: false
      }).then(()=> $nama.focus());
      return;
    }


    // Delivery fields
   let phone='', alamat='', ongkir=0, latVal='', lngVal='';
    if (MODE === 'delivery') {
      phone  = ($phone.val() || '').trim();
      alamat = ($alamat.val() || '').trim();

      // ambil koordinat dari hidden input di form
      const $lat = $('#form-order').find('input[name="dest_lat"]');
      const $lng = $('#form-order').find('input[name="dest_lng"]');

      latVal = ($lat.val() || '').trim();
      lngVal = ($lng.val() || '').trim();

      if (IS_KASIR) {
        const ong = ($ongkir.val() || '0').trim();
        ongkir = parseInt(ong, 10) || 0;
      }

      // VALIDASI WAJIB DELIVERY (telepon & alamat teks)
      if (!phone || !alamat) {
        Swal.fire({
          icon:'warning',
          title:'Lengkapi data delivery',
          text:'Telepon & alamat wajib untuk pengantaran.',
          allowOutsideClick:false
        });
        return;
      }

      // VALIDASI WAJIB PIN LOKASI 📍
      if (!latVal || !lngVal) {
        Swal.fire({
          icon:'warning',
          title:'Tentukan titik lokasi 📍',
          text:'Silakan pilih titik lokasi di map agar kurir tidak nyasar.',
          allowOutsideClick:false
        });
        return;
      }
    }



    // ===== SweetAlert RINGKASAN TANPA total/ongkir/total bayar =====
    const ringkasan = `
      <div style="text-align:left;line-height:1.5">
        ${mejaInfo
          ? `<div><b>Tempat</b>: <?= html_escape($meja_info) ?></div>`
          : `<div><b>Mode</b>: ${MODE==='delivery'?'Delivery':'Bungkus'}</div>`}
        ${MODE==='delivery' ? `
          <div><b>Telepon</b>: ${$('<div>').text(phone).html()}</div>
          <div><b>Alamat</b>: ${$('<div>').text(alamat).html()}</div>
        ` : ''}
         ${email ? `<div><b>Email</b>: ${$('<div>').text(email).html()}</div>` : ''} 
          ${voucherCode ? `<div><b>Voucher</b>: ${$('<div>').text(voucherCode).html()}</div>` : ''} 
          ${paidMethod ? `<div><b>Bayar</b>: ${$('<div>').text(paidMethod.toUpperCase()).html()}</div>` : ''}

        ${catatan ? `<div><b>Catatan</b>: ${$('<div>').text(catatan).html()}</div>` : ''}
        <div><b>Atas Nama</b>: ${$('<div>').text(nama).html()}</div>
      </div>
    `;

    Swal.fire({
      title: 'Fix nih, lanjut order? 😎',
      html: ringkasan,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, Gaskeun Orderr ! 🚀',
      cancelButtonText: 'Bentar, cek dulu…',
      reverseButtons: true,
      didOpen: () => {
        const swalEl = Swal.getPopup();
        if (swalEl) swalEl.style.zIndex = 200000;
      },
      allowOutsideClick: false,
      allowEscapeKey: true
    }).then((res)=>{
      if (!res.isConfirmed) return;

      // suntikkan ongkir 0 jika bukan kasir (supaya server konsisten)
      if (MODE === 'delivery' && !IS_KASIR) {
        fd.set('ongkir', '0');
      }

   
      const steps = buildSteps(MODE);
      if (email) steps.push('Siapkan email konfirmasi… ✉️');
        // buka loader progres bertahap
        startProgressLoader(steps, 900); // <-- NEW (ganti Swal.fire "Lagi diproses...")
      $.ajax({
        url: "<?= site_url('produk/submit_order'); ?>",
        type: "POST",
        data: fd,
        processData: false,
        contentType: false,
        dataType: 'json'
      }).done(function(r){
        stopProgressLoader(true); // <-- NEW
        // Swal.close();
        if (!r || !r.success) {
          Swal.fire('Gagal 😥', (r && r.pesan) ? r.pesan : 'Tidak bisa membuat pesanan', 'error');
          return;
        }
        // Hapus draft biar nggak ke-restore ke order berikutnya
        clearDraft();
        ausiSaveOrderToLocal(r);

        // Swal.fire({ title:'Mantap! Pesanan diterima ✔️', icon:'success', timer:1300, showConfirmButton:false });
        Swal.fire({
          title: 'Mantap! Pesanan diterima ✔️',
          html: '<small>Lanjut ke metode pembayaran…</small>',
          icon: 'success',
            timer: 1300,              // popup auto-close dalam 1,3 detik
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false
          }).then(() => {
            // ⬅️ BARIS PENTING: redirect setelah popup bener-bener tertutup
            window.location.href = r.redirect;
          });

        // setTimeout(()=> { window.location.href = r.redirect; }, 900);
      // }).fail(function(){
      //   stopProgressLoader(false); // <-- NEW
      //   Swal.fire('Error','Koneksi lagi ngambek, coba lagi ya.','error');
      // });
      }).fail(function(jqXHR, textStatus, errorThrown){
        stopProgressLoader(false);

        // simpan info buat debug (bisa kamu lihat lewat DevTools console)
        console.warn('AJAX submit_order FAIL:', {
          status: jqXHR.status,
          textStatus,
          errorThrown,
          responseText: jqXHR.responseText
        });

        let msgUser = 'Koneksi lagi ngambek, coba lagi ya.';
        // Deteksi beberapa kasus umum dan kasih pesan lebih pas
        if (jqXHR.status === 403){
          msgUser = 'Sesi kamu kadaluarsa / keamanan blokir (403). Coba refresh halaman dulu ya 🙏';
        } else if (jqXHR.status === 500){
          msgUser = 'Server lagi error (500). Kru kami lagi beresin kok 🙏';
        }

        Swal.fire('Error', msgUser, 'error');
      });

    });
  });
})();


// GLOBAL: handle loader step multi-tahap
// let __stepLoaderInterval = null;

let __stepLoaderInterval = null;
let __stepLoaderIndex = 0;
let __stepLoaderSteps = [];

/**
 * startProgressLoader([
 *   'Memvalidasi data…',
 *   'Cek hari & jam…',
 *   'Cek radius & ongkir…',
 *   'Bikin nomor order…',
 *   'Simpan ke kasir…',
 *   'Persiapan kirim notifikasi…'
 * ], 900)
 */
function startProgressLoader(stepsArray, intervalMs){
  __stepLoaderSteps = Array.isArray(stepsArray) && stepsArray.length ? stepsArray : ['Memproses…'];
  __stepLoaderIndex = 0;
  const dur = intervalMs || 900;

  // build daftar step awal (step 0 = active spinner, sisanya pending)
  let listHtml = '';
  for (let i=0; i<__stepLoaderSteps.length; i++){
    const stepText = __stepLoaderSteps[i];

    if (i === 0){
      // langkah aktif pertama → spinner
      listHtml += `
        <div class="prog-step-row" data-step="${i}" style="display:flex;align-items:flex-start;gap:.6rem;margin-bottom:.4rem;">
          <div class="prog-icon prog-icon-active" style="
              width:1.1rem;
              height:1.1rem;
              border:.18rem solid #3b82f6;
              border-right-color:transparent;
              border-radius:50%;
              animation:swalSpin .6s linear infinite;
              flex-shrink:0;
            "></div>
          <div class="prog-text prog-text-active" style="
              font-size:.9rem;
              line-height:1.4;
              color:#111;
              font-weight:600;
            ">${escapeHtml(stepText)}</div>
        </div>
      `;
    } else {
      // langkah berikutnya → pending (belum aktif, abu-abu, tanpa icon dulu)
      listHtml += `
        <div class="prog-step-row" data-step="${i}" style="display:flex;align-items:flex-start;gap:.6rem;margin-bottom:.4rem;opacity:.5;">
          <div class="prog-icon prog-icon-pending" style="
              width:1.1rem;
              height:1.1rem;
              border:.18rem solid #ccc;
              border-radius:50%;
              background-color:#f8f9fa;
              flex-shrink:0;
            "></div>
          <div class="prog-text prog-text-pending" style="
              font-size:.9rem;
              line-height:1.4;
              color:#666;
              font-weight:500;
            ">${escapeHtml(stepText)}</div>
        </div>
      `;
    }
  }

  // body html swal
  const bodyHtml = `
    <div style="text-align:left;line-height:1.5">
      <div id="prog-steps-wrap" style="margin-bottom:.75rem;">
        ${listHtml}
      </div>
      <div style="font-size:.8rem;line-height:1.4;color:#777;">
        Mohon tunggu sebentar ya 🙏
      </div>
    </div>

    <style>
      @keyframes swalSpin { to { transform: rotate(360deg); } }
      .checkmark-icon {
        width:1.1rem;
        height:1.1rem;
        border-radius:50%;
        background-color:#10b981; /* hijau */
        color:#fff;
        font-size:.7rem;
        line-height:1.1rem;
        text-align:center;
        font-weight:700;
        box-shadow:0 0.25rem 0.5rem rgb(16 185 129 / .35);
      }
    </style>
  `;

  Swal.fire({
    icon: 'warning',
    title: "Memproses Pesanan…",
    html: bodyHtml,
    allowOutsideClick: false,
    allowEscapeKey: false,
    showConfirmButton: false,
    didOpen: () => {
      const popup = Swal.getPopup();
      if (popup) popup.style.zIndex = 200000; // optional naikkan z-index

      // interval: tandai step lama -> selesai ✅, lalu aktifkan step berikutnya
      __stepLoaderInterval = setInterval(()=>{
        advanceProgressStep();
      }, dur);
    }
  });
}

// panggil saat selesai / error
function stopProgressLoader(finalize=true){
  if (__stepLoaderInterval){
    clearInterval(__stepLoaderInterval);
    __stepLoaderInterval = null;
  }

  if (finalize){
    // sebelum close, pastikan semua step ditandai selesai (biar keliatan rapi)
    try { finishAllProgressSteps(); } catch(e){}
  }

  Swal.close();
}


/* ===== helper internal ===== */

// Escape text biar aman kalau step ada karakter aneh
function escapeHtml(str){
  return String(str)
    .replace(/&/g,"&amp;")
    .replace(/</g,"&lt;")
    .replace(/>/g,"&gt;")
    .replace(/"/g,"&quot;")
    .replace(/'/g,"&#039;");
}

/**
 * advanceProgressStep():
 * - step index sekarang di-mark selesai (spinner -> checkmark hijau, text jadi abu agak redup)
 * - step berikutnya di-mark aktif (spinner biru, teks bold gelap)
 * - kalau sudah di step terakhir, kita tetap kasih centang dan stop interval
 */
function advanceProgressStep(){
  const wrap = Swal.getPopup()?.querySelector('#prog-steps-wrap');
  if (!wrap) return;

  const rows = wrap.querySelectorAll('.prog-step-row');
  if (!rows.length) return;

  const current = __stepLoaderIndex;
  const next    = current + 1;

  // 1. Tandai step current jadi selesai (✅)
  const rowCur = rows[current];
  if (rowCur){
    const iconCur = rowCur.querySelector('.prog-icon');
    const textCur = rowCur.querySelector('.prog-text');

    if (iconCur){
      // ganti isi iconCur jadi checkmark hijau circle
      iconCur.classList.remove('prog-icon-active','prog-icon-pending');
      iconCur.classList.add('prog-icon-done');
      iconCur.style.cssText = ''; // reset inline style
      iconCur.innerHTML = '<div class="checkmark-icon">✓</div>';
    }

    if (textCur){
      textCur.classList.remove('prog-text-active','prog-text-pending');
      textCur.classList.add('prog-text-done');
      textCur.style.cssText = `
        font-size:.9rem;
        line-height:1.4;
        color:#4b5563;
        font-weight:500;
      `;
    }

    // turunkan opacity rowCur sedikit
    rowCur.style.opacity = '.85';
  }

  // 2. Aktifkan step next (kalau ada)
  if (next < rows.length){
    const rowNext = rows[next];
    const iconNext = rowNext.querySelector('.prog-icon');
    const textNext = rowNext.querySelector('.prog-text');

    if (rowNext) {
      rowNext.style.opacity = '1';
    }

    if (iconNext){
      iconNext.classList.remove('prog-icon-pending','prog-icon-done');
      iconNext.classList.add('prog-icon-active');
      iconNext.innerHTML = ''; // kosongkan isi agar spinner jalan pakai inline style
      iconNext.style.cssText = `
        width:1.1rem;
        height:1.1rem;
        border:.18rem solid #3b82f6;
        border-right-color:transparent;
        border-radius:50%;
        animation:swalSpin .6s linear infinite;
        flex-shrink:0;
      `;
    }

    if (textNext){
      textNext.classList.remove('prog-text-pending','prog-text-done');
      textNext.classList.add('prog-text-active');
      textNext.style.cssText = `
        font-size:.9rem;
        line-height:1.4;
        color:#111;
        font-weight:600;
      `;
    }

    __stepLoaderIndex = next;
  } else {
    // Udah terakhir -> stop interval
    if (__stepLoaderInterval){
      clearInterval(__stepLoaderInterval);
      __stepLoaderInterval = null;
    }
  }
}

/**
 * finishAllProgressSteps():
 * dipanggil pas mau tutup loader.
 * Semua row jadi centang hijau (biar keliatan completed).
 */
function finishAllProgressSteps(){
  const wrap = Swal.getPopup()?.querySelector('#prog-steps-wrap');
  if (!wrap) return;
  const rows = wrap.querySelectorAll('.prog-step-row');
  rows.forEach(row => {
    row.style.opacity = '.85';
    const icon = row.querySelector('.prog-icon');
    const txt  = row.querySelector('.prog-text');
    if (icon){
      icon.classList.remove('prog-icon-active','prog-icon-pending');
      icon.classList.add('prog-icon-done');
      icon.style.cssText = '';
      icon.innerHTML = '<div class="checkmark-icon">✓</div>';
    }
    if (txt){
      txt.classList.remove('prog-text-active','prog-text-pending');
      txt.classList.add('prog-text-done');
      txt.style.cssText = `
        font-size:.9rem;
        line-height:1.4;
        color:#4b5563;
        font-weight:500;
      `;
    }
  });
}


</script>

<script>
  (function(){
    var $cat = $('#catatan');
    var $count = $('#catatan-count');
    var LIMIT = 255;
    function updateCount(){
      var val = $cat.val() || '';
      if (val.length > LIMIT) { $cat.val(val.slice(0, LIMIT)); }
      var len = $cat.val().length;
      $count.text(len);
      var help = document.getElementById('catatanHelp');
      if (len >= LIMIT) { help.classList.remove('text-muted'); help.classList.add('text-danger'); }
      else { help.classList.add('text-muted'); help.classList.remove('text-danger'); }
    }
    // Tetap update counter, tapi tdk disimpan di localStorage
    $(document).ready(updateCount);
    $cat.on('input', updateCount);
  })();
</script>




