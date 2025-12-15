<link href="<?= base_url('assets/admin/datatables/css/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet" type="text/css"/>

<div class="container-fluid">
  <?php
    $uname = strtolower((string)$this->session->userdata('admin_username'));
    $isKB  = in_array($uname, ['kitchen','bar'], true); // kitchen / bar
  ?>

  <style>
    /* Badge tabel */
    .table td .badge.border{
      border-color: rgba(0,0,0,.12) !important;
      background:#f9fafb !important;
      color:#111827 !important;
      font-weight:600;
    }

    /* Kartu meja */
    .meja-card{
      border:1px solid rgba(0,0,0,.08);
      border-radius:.75rem;
      padding:.75rem;
      margin:.25rem;
      width:100%;
    }
    .meja-kode{
      font-weight:700;
      font-size:1rem;
    }
    .meja-meta{
      color:#6b7280;
      font-size:.85rem;
    }
    .meja-actions .btn{
      margin-right:.3rem;
      margin-top:.35rem;
    }

    /* ===== Idle countdown ===== */
    .idle-countdown{
      border:1px solid #e2e8f0;
      background:#f8fafc;
      color:#0f172a;
      padding:.4rem .75rem;
      border-radius:.5rem;
      font-weight:700;
      letter-spacing:.3px;
    }
    @media (max-width: 575.98px){
      .idle-countdown .d-none.d-sm-inline{
        display:none !important;
      }
    }

    /* ===== Running Ticker ===== */
    .ticker-wrap{
      position:relative;
      overflow:hidden;
      border-radius:.5rem;
      border:1px solid #ffeeba;
      background:#fff3cd;   /* alert-warning vibes */
      color:#856404;
    }
    .ticker-track{
      display:inline-block;
      white-space:nowrap;
      padding:.5rem 0;
      animation: ticker-move 25s linear infinite; /* kecilkan untuk lebih cepat */
    }
    .ticker-item{
      display:inline-block;
      margin:0 2rem;
      font-weight:600;
    }
    @keyframes ticker-move{
      0%   { transform: translateX(100%); }
      100% { transform: translateX(-100%); }
    }
    /* ==== CHIP KHUSUS VOUCHER ==== */
.voucher-chip{
  background: #fff7ed;                       /* krem lembut */
  color: #9a3412;                            /* coklat orange */
  border-radius: 999px;
  border: 1px dashed #fb923c;                /* garis putus-putus ala kupon */
  display: inline-flex;
  align-items: center;
  padding: .15rem .6rem .15rem .4rem;
  font-size: .75rem;
  font-weight: 500;
  letter-spacing: .01em;
}

.voucher-chip-icon{
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 18px;
  height: 18px;
  border-radius: 999px;
  background: #fed7aa;                       /* lingkaran oranye muda */
  margin-right: .25rem;
}

.voucher-chip-icon i{
  font-size: .9rem;
  line-height: 1;
}

.voucher-chip-text{
  white-space: nowrap;
}
 /* Ratakan isi sel di tengah untuk Mode / Status / Metode */
table.dataTable tbody td.col-mode,
table.dataTable tbody td.col-status,
table.dataTable tbody td.col-metode{
  vertical-align: middle !important;
}

/* Metode: wrapper d-flex tetap center */
table.dataTable tbody td.col-metode > div.d-flex {
  align-items: center;
  justify-content: center;
}

/* Semua badge di 3 kolom itu sejajar enak */
table.dataTable tbody td.col-mode   .badge.badge-pill,
table.dataTable tbody td.col-status .badge.badge-pill,
table.dataTable tbody td.col-metode .badge.badge-pill{
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
@keyframes kurirBlink {
  0%, 100% { opacity: 1; }
  50%      { opacity: 0.1; }
}

.kurir-belum {
  display: flex;          /* atau boleh 'block' kalau mau lebih simpel */
  align-items: center;
  font-size: 11px;
  font-weight: 600;
  padding: 2px 10px;
  border-radius: 999px;
  background: #fff5f5;
  color: #c53030;
  margin-top: 4px;        /* kasih jarak kecil dari badge 'Antar/Kirim' */
}

.kurir-belum-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  margin-right: 6px;
  background: #e53e3e;
  position: relative;
}

.kurir-belum-dot::after {
  content: '';
  position: absolute;
  inset: -4px;
  border-radius: inherit;
  border: 2px solid rgba(229, 62, 62, 0.6);
  animation: kurirPing 1.2s ease-out infinite;
}

@keyframes kurirPing {
  0%   { transform: scale(0.6); opacity: 1; }
  70%  { transform: scale(1.5); opacity: 0; }
  100% { transform: scale(1.5); opacity: 0; }
}

  </style>
 
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <h4 class="header-title">POS Cafe</h4>

          <!-- Toolbar -->
          <div class="row mb-2">
            <div class="col-12">
              <form class="form-inline align-items-center flex-wrap w-100">
                <!-- Refresh -->
                <button type="button"
                        onclick="reload_table('user')"
                        class="btn btn-blue btn-sm waves-effect waves-light mb-2 mr-2">
                  <span class="btn-label"><i class="fe-refresh-ccw"></i></span>
                  Refresh
                </button>
             <!--    <?php if (!$isKB): ?>
                  <button type="button"
                          onclick="openGmailInbox()"
                          class="btn btn-danger btn-sm waves-effect waves-light mb-2 mr-2">
                    <span class="btn-label"><i class="mdi mdi-gmail"></i></span>
                    Baca Gmail
                  </button>
                  <?php endif; ?> -->

                <?php if (!$isKB): ?>
                  <!-- Filter status -->
                  <div class="form-group mb-2 mr-2">
                    <label for="filter-status" class="sr-only">Status</label>
                    <select id="filter-status" class="custom-select custom-select-sm">
                      <option value="all" selected>Semua status</option>
                      <option value="paid">Lunas</option>
                      <option value="pending">Menunggu Pembayaran</option>
                      <option value="verifikasi">Verifikasi</option>
                      <option value="canceled">Canceled</option>
                    </select>
                  </div>
                <?php endif; ?>

                <!-- Idle countdown (auto-reload 20 menit) di sisi kanan -->
                <div id="idle-countdown"
                     class="idle-countdown d-inline-flex align-items-center ml-auto mb-2"
                     aria-live="polite"
                     style="display:none">
                  <i class="mdi mdi-timer-outline mr-1" aria-hidden="true"></i>
                  <span class="d-none d-sm-inline mr-1">Segarkan dalam:</span>
                  <span id="idle-countdown-text" class="font-weight-bold">20:00</span>
                </div>
              </form>
            </div>
          </div>
          <!-- /Toolbar -->

          <?php
            // (OPSIONAL) kalau kamu sudah punya perhitungan jam tutup aktif,
            // set epoch detik di $closing_deadline_ts. Kalau belum ada, biarkan 0.
            $closing_deadline_ts = isset($closing_deadline_ts) ? (int)$closing_deadline_ts : 0;
          ?>
          <?php if (!empty($closing_ticker)): ?>
            <div class="ticker-wrap mb-2" data-close-ts="<?= (int)$closing_deadline_ts ?>">
              <div class="ticker-track">
                <span class="ticker-item">
                  <i class="mdi mdi-clock-outline mr-1"></i>
                  <?= htmlspecialchars($closing_ticker, ENT_QUOTES, 'UTF-8'); ?>
                </span>
                <span class="ticker-item d-none d-md-inline">
                  <i class="mdi mdi-clock-outline mr-1"></i>
                  <?= htmlspecialchars($closing_ticker, ENT_QUOTES, 'UTF-8'); ?>
                </span>
              </div>
            </div>
          <?php endif; ?>

          <!-- Script: auto-reload 20 menit + kontrol ticker -->
          <script>
          (function(){
            // cegah inisialisasi ganda
            if (window.__AUTO_RELOAD_20M__) return;
            window.__AUTO_RELOAD_20M__ = true;

            const RELOAD_MS = 20 * 60 * 1000; // 20 menit
            const elBox  = document.getElementById('idle-countdown');
            const elText = document.getElementById('idle-countdown-text');

            function pad(n){ return (n<10 ? '0' : '') + n; }

            function updateCountdown(remainMs){
              if (!elBox || !elText) return;
              const s = Math.max(0, Math.ceil(remainMs/1000));
              elText.textContent = pad(Math.floor(s/60)) + ':' + pad(s % 60);
              if (elBox.style.display === 'none') elBox.style.display = '';
            }

            function startTimer(){
              const startAt = Date.now();

              function tick(){
                const now    = Date.now();
                const remain = RELOAD_MS - (now - startAt);

                if (remain <= 0){
                  // reload penuh halaman
                  location.reload();
                  return;
                }
                updateCountdown(remain);
                setTimeout(tick, 1000); // cukup 1x/detik
              }

              tick();
            }

            // ====== Ticker jam tutup (opsional) ======
            const tickerWrap = document.querySelector('.ticker-wrap');
            function evalTicker(){
              if (!tickerWrap) return;

              let closeTs = parseInt(tickerWrap.getAttribute('data-close-ts') || '', 10);
              if (!closeTs && window.WINDOW_CLOSE_TS) {
                closeTs = parseInt(window.WINDOW_CLOSE_TS, 10) || 0;
              }

              if (!closeTs){
                tickerWrap.style.display = 'none';
                return;
              }

              const nowSec = Math.floor(Date.now()/1000);
              const diff   = closeTs - nowSec; // detik ke tutup

              // tampilkan hanya bila 0 < diff ≤ 3600 (≤ 1 jam)
              tickerWrap.style.display = (diff > 0 && diff <= 3600) ? '' : 'none';
            }

            // start semuanya
            startTimer();
            evalTicker();
            setInterval(evalTicker, 30000);
          })();
          </script>

          <!-- Tabel -->
          <table id="datable_pos" class="table table-sm table-striped table-bordered w-100">
            <thead>
              <tr>
                <th width="6%">No.</th>
                <th>Mode</th>
                <th>Meja / Nama</th>

                <?php if ($isKB): ?>
                  <th>Pesanan</th>
                <?php endif; ?>

                <th>Waktu</th>
                <th>Durasi</th>
                <th class="th-price">Jumlah</th>
                <th>Status</th>
                <th class="th-method">Metode</th>

                <?php if (!$isKB): ?>
                  <th width="14%">Aksi</th>
                <?php endif; ?>
              </tr>
            </thead>
          </table>
          <!-- /Tabel -->

        </div>
      </div>
    </div>
  </div>

  <!-- ===== MODAL: LIST MEJA ===== -->
  <div id="meja-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-light">
          <h4 class="mymodal-title">Pilih Meja</h4>
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        </div>
        <div class="modal-body">
          <div class="form-group mb-2">
            <input type="search" id="meja-q" class="form-control" placeholder="Cari meja (kode/nama/area)…">
          </div>

          <div id="meja-list" class="row no-gutters"></div>
          <div id="meja-empty" class="text-muted small py-3" style="display:none">Tidak ada data.</div>
        </div>
        <div class="modal-footer">
          <small class="text-muted mr-auto" id="meja-count"></small>
          <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ===== MODAL: DETAIL ORDER ===== -->
  <div id="pos-detail-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable  modal-full">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="mymodal-title">Detail Order</h4>
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        </div>
        <div class="modal-body" id="detail-body">
          <div class="text-center text-muted py-5">Memuat…</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ===== MODAL: BUAT ORDER (POS) ===== -->
  <div id="pos-create-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="mymodal-title">Buat Order (Kasir)</h4>
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        </div>
        <div class="modal-body">
          <form id="form-pos" onsubmit="return false">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group mb-2">
                  <label>Mode</label>
                  <select class="form-control" name="mode" id="pos-mode">
                    <option value="walkin">Walk-in</option>
                    <option value="dinein">Dine-in</option>
                    <option value="delivery">Delivery</option>
                  </select>
                </div>

                <div class="form-group mb-2 dinein-only" style="display:none;">
                  <label>Meja (kode/nama)</label>
                  <input type="text" class="form-control" name="meja_kode" id="pos-meja-kode" placeholder="K-01">
                  <small class="text-muted">Opsional: isi kode atau nama meja</small>
                  <input type="text" class="form-control mt-1" name="meja_nama" id="pos-meja-nama" placeholder="Meja Tamu 1">
                </div>

                <div class="form-group mb-2">
                  <label>Nama Pelanggan</label>
                  <input type="text" class="form-control" name="nama" id="pos-nama" placeholder="Customer">
                </div>

                <div class="form-group mb-2">
                  <label>Catatan</label>
                  <textarea class="form-control" name="catatan" id="pos-catatan" rows="2" maxlength="120" placeholder="Tanpa gula, pedas, dll."></textarea>
                </div>

                <div class="form-group mb-2">
                  <label>Metode Pembayaran</label>
                  <div class="btn-group d-flex" role="group">
                    <button type="button" class="btn btn-outline-secondary w-100 pay-btn" data-pay="cash">Cash</button>
                    <button type="button" class="btn btn-outline-secondary w-100 pay-btn" data-pay="qris">QRIS</button>
                    <button type="button" class="btn btn-outline-secondary w-100 pay-btn" data-pay="transfer">Transfer</button>
                  </div>
                  <input type="hidden" name="pay_method" id="pos-pay" value="cash">
                </div>
              </div>

              <div class="col-md-8">
                <div class="form-group mb-2">
                  <label>Cari Produk</label>
                  <input type="search" class="form-control" id="pos-search" placeholder="Ketik nama / SKU…">
                </div>

                <div id="pos-search-result" class="border rounded p-2" style="max-height:210px; overflow:auto;">
                  <div class="text-muted small">Mulai ketik untuk mencari…</div>
                </div>

                <div class="table-responsive mt-2">
                  <table class="table table-sm table-bordered mb-0" id="pos-items">
                    <thead>
                      <tr>
                        <th>Produk</th>
                        <th style="width:120px">Harga</th>
                        <th style="width:120px">Qty</th>
                        <th style="width:140px">Subtotal</th>
                        <th style="width:60px">#</th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                      <tr>
                        <th colspan="3" class="text-right">Total</th>
                        <th id="pos-total" class="text-right">Rp 0</th>
                        <th></th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>

            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button class="btn btn-primary" id="pos-submit">Buat Order</button>
        </div>
      </div>
    </div>
  </div>
<!-- ===== MODAL: INBOX GMAIL ===== -->
<div id="gmail-inbox-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h4 class="mymodal-title mb-0">Inbox Gmail</h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      </div>

      <div class="modal-body">
        <div class="d-flex align-items-center mb-2" style="gap:.5rem">
          <input type="search" id="gmail-q" class="form-control form-control-sm" placeholder="Cari subject / from / snippet…">
          <button type="button" class="btn btn-sm btn-primary" id="gmail-sync-btn">
            <i class="fe-refresh-ccw"></i> Sync
          </button>
        </div>

        <div id="gmail-loading" class="text-center text-muted py-3" style="display:none">Memuat…</div>
        <div id="gmail-empty" class="text-muted small py-3" style="display:none">Tidak ada email.</div>

        <div id="gmail-list" class="list-group"></div>
      </div>

      <div class="modal-footer">
        <small class="text-muted mr-auto" id="gmail-count">0 email</small>
        <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- ===== MODAL: DETAIL EMAIL ===== -->
<div id="gmail-detail-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="mymodal-title mb-0" id="gmail-detail-title">Detail Email</h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      </div>
      <div class="modal-body" id="gmail-detail-body">
        <div class="text-center text-muted py-5">Memuat…</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
<script>
(function(){
  if (window.__GMAIL_UI__) return; window.__GMAIL_UI__ = true;

  const URL_LIST   = "<?= site_url('admin_pos/gmail_inbox') ?>";
  const URL_DETAIL = "<?= site_url('admin_pos/gmail_detail') ?>/"; // +id

  function esc(s){ return (s||'').toString().replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' }[m])); }

  function gmailLoading(on){
    document.getElementById('gmail-loading').style.display = on ? '' : 'none';
  }

  function renderInbox(rows){
    const $list  = $('#gmail-list');
    const $empty = $('#gmail-empty');
    const $count = $('#gmail-count');

    $list.empty();
    rows = rows || [];
    $count.text(rows.length + ' email');

    if (!rows.length){
      $empty.show();
      return;
    }
    $empty.hide();

    rows.forEach(r=>{
      const badge = (r.status === 'diproses')
        ? '<span class="badge badge-success">Diproses</span>'
        : '<span class="badge badge-warning">Baru</span>';

      const html = `
        <button type="button" class="list-group-item list-group-item-action" onclick="gmailOpenDetail(${parseInt(r.id,10)})">
          <div class="d-flex justify-content-between align-items-start">
            <div style="min-width:0">
              <div class="font-weight-bold text-truncate">${esc(r.subject||'(tanpa subject)')}</div>
              <div class="text-muted small text-truncate">${esc(r.from_email||'-')}</div>
            </div>
            <div class="text-right" style="margin-left:.5rem">
              ${badge}
              <div class="text-muted small">${esc(r.received_at||'')}</div>
            </div>
          </div>
          <div class="text-muted small mt-1 text-truncate">${esc(r.snippet||'')}</div>
        </button>`;
      $list.append(html);
    });
  }

  function loadInbox(opts){
    opts = opts || {};
    const q = ($('#gmail-q').val() || '').trim();

    gmailLoading(true);
    return $.getJSON(URL_LIST, {
      sync: opts.sync ? 1 : 0,
      limit: opts.limit || 20,
      q: q
    }).done(function(res){
      gmailLoading(false);
      if (!res || !res.success){
        renderInbox([]);
        if (window.Swal) Swal.fire(res?.title||'Gagal', res?.pesan||'Tidak bisa memuat inbox', 'error');
        return;
      }
      renderInbox(res.data || []);
    }).fail(function(xhr){
      gmailLoading(false);
      renderInbox([]);
      if (window.Swal) Swal.fire('Error','Koneksi bermasalah saat memuat Gmail','error');
      console.error(xhr?.responseText);
    });
  }

  window.openGmailInbox = function(){
    $('#gmail-inbox-modal').modal('show');
    loadInbox({sync:true, limit:20});
    setTimeout(()=>{ try{ document.getElementById('gmail-q').focus(); }catch(e){} }, 150);
  };

  window.gmailOpenDetail = function(id){
    $('#gmail-detail-modal').modal('show');
    $('#gmail-detail-body').html('<div class="text-center text-muted py-5">Memuat…</div>');

    $.get(URL_DETAIL + id, function(html){
      $('#gmail-detail-body').html(html);
    }).fail(function(xhr){
      $('#gmail-detail-body').html('<div class="text-danger">Gagal memuat detail.</div>');
      console.error(xhr?.responseText);
    });
  };

  // tombol sync
  $('#gmail-sync-btn').on('click', function(){ loadInbox({sync:true, limit:20}); });

  // search debounce
  let t=null;
  $('#gmail-q').on('input', function(){
    clearTimeout(t);
    t = setTimeout(()=>loadInbox({sync:false, limit:20}), 250);
  });

})();
</script>

  <script>
    const IS_KB = <?= $isKB ? 'true' : 'false' ?>;
  </script>

  <?php
    $this->load->view("backend/global_css");
    $this->load->view($controller."_js");
  ?>
</div>
