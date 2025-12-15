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


                <?php if (!$isKB): ?>
                  <button type="button"
                          onclick="openGmailInbox()"
                          class="btn btn-danger btn-sm waves-effect waves-light mb-2 mr-2">
                    <span class="btn-label"><i class="mdi mdi-gmail"></i></span>
                    Gmail/ Cek transaksi
                  </button>

                  <?php endif; ?>

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

  <style type="text/css">
    /* ===== GMAIL MODAL THEME ===== */
.gmail-modal{
  border: 0;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 18px 50px rgba(0,0,0,.22);
}

.gmail-modal__header{
  border: 0;
  padding: 14px 16px;
  color: #fff;
  background: linear-gradient(135deg, #EA4335, #FBBC05, #34A853, #4285F4);
  background-size: 300% 300%;
  animation: gmailGradient 8s ease infinite;
}

@keyframes gmailGradient{
  0%{background-position:0% 50%}
  50%{background-position:100% 50%}
  100%{background-position:0% 50%}
}

.gmail-icon{
  width: 40px; height: 40px;
  border-radius: 12px;
  background: rgba(255,255,255,.18);
  display:flex; align-items:center; justify-content:center;
  backdrop-filter: blur(6px);
}
.gmail-icon i{ font-size: 22px; }

.gmail-title{ font-weight: 800; letter-spacing:.2px; }
.gmail-subtitle{ font-size: 12px; opacity: .9; }

.gmail-toolbar{
  display:flex;
  align-items:center;
  gap: .6rem;
  margin-bottom: 12px;
}

.gmail-search{
  position: relative;
  flex: 1;
  display:flex;
  align-items:center;
  background: #f6f7fb;
  border: 1px solid #eef0f6;
  border-radius: 12px;
  padding: 0 10px;
}
.gmail-search i{
  color: #6c757d;
  font-size: 18px;
}
.gmail-search input{
  border: 0 !important;
  background: transparent !important;
  box-shadow: none !important;
  padding-left: 8px;
  padding-right: 34px;
  height: 38px;
}
.gmail-clear{
  position:absolute;
  right: 6px;
  top: 50%;
  transform: translateY(-50%);
  border-radius: 10px;
  padding: 4px 8px;
}

.gmail-sync{
  border-radius: 12px;
  padding: 9px 12px;
  box-shadow: 0 8px 18px rgba(66,133,244,.22);
}

/* ===== LIST ===== */
.gmail-list .list-group-item{
  border: 0;
  border-radius: 12px;
  margin-bottom: 10px;
  background: #fff;
  box-shadow: 0 8px 22px rgba(17,24,39,.06);
  transition: transform .08s ease, box-shadow .2s ease;
}
.gmail-list .list-group-item:hover{
  transform: translateY(-1px);
  box-shadow: 0 12px 28px rgba(17,24,39,.10);
}
.gmail-item__top{
  display:flex;
  justify-content:space-between;
  gap: 12px;
  margin-bottom: 6px;
}
.gmail-from{
  font-weight: 700;
  color:#111827;
  max-width: 68%;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.gmail-date{
  font-size: 12px;
  color:#6b7280;
  white-space: nowrap;
}
.gmail-subject{
  font-weight: 700;
  color:#1f2937;
  margin-bottom: 4px;
}
.gmail-snippet{
  font-size: 13px;
  color:#6b7280;
  line-height: 1.35;
}
.gmail-chip{
  display:inline-flex;
  align-items:center;
  gap:.35rem;
  font-size: 12px;
  padding: 4px 9px;
  border-radius: 999px;
  background: #f3f4f6;
  border: 1px solid #e5e7eb;
  color:#374151;
}

/* ===== LOADING SHIMMER ===== */
.gmail-loading{
  padding: 8px 2px 14px;
}
.gmail-skel{
  display:flex;
  gap: 12px;
  padding: 10px;
  background: #fff;
  border: 1px solid #eef0f6;
  border-radius: 12px;
  margin-bottom: 10px;
}
.skel-avatar{
  width: 44px; height: 44px;
  border-radius: 14px;
  background: #f0f2f7;
  overflow:hidden;
  position:relative;
}
.skel-lines{ flex:1; }
.skel-line{
  height: 10px;
  border-radius: 999px;
  background: #f0f2f7;
  margin-bottom: 10px;
  overflow:hidden;
  position:relative;
}
.skel-line::after, .skel-avatar::after{
  content:"";
  position:absolute;
  top:0; left:-60%;
  width: 60%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,.8), transparent);
  animation: shimmer 1.1s infinite;
}
@keyframes shimmer{
  100%{ left: 120%; }
}
.w35{ width:35%; } .w40{ width:40%; } .w45{ width:45%; }
.w50{ width:50%; } .w60{ width:60%; } .w70{ width:70%; }
.w80{ width:80%; } .w90{ width:90%; } .w95{ width:95%; }

.gmail-loading-text{
  display:flex;
  align-items:center;
  justify-content:center;
  gap:.5rem;
  color:#6b7280;
  font-size: 13px;
  padding-top: 6px;
}

/* ===== EMPTY ===== */
.gmail-empty{
  text-align:center;
  padding: 24px 10px 18px;
  background: #fff;
  border: 1px dashed #e5e7eb;
  border-radius: 14px;
}
.gmail-empty__icon{
  width: 60px; height: 60px;
  border-radius: 18px;
  margin: 0 auto 10px;
  background: #f3f4f6;
  display:flex; align-items:center; justify-content:center;
  color:#6b7280;
}
.gmail-empty__icon i{ font-size: 30px; }
.gmail-empty__title{ font-weight: 800; color:#111827; }
.gmail-empty__desc{ font-size: 13px; color:#6b7280; }

.gmail-modal__footer{
  border-top: 1px solid #f0f2f7;
  background: #fafbff;
}

  </style>
  <style>
  .gmail-toast{
  position: fixed;
  top: 16px;
  left: 50%;
  transform: translateX(-50%) translateY(-8px);
  z-index: 99999;
  display: none;
  min-width: 260px;
  max-width: 520px;
  padding: 10px 12px;
  border-radius: 12px;
  background: #111827;
  color: #fff;
  box-shadow: 0 14px 40px rgba(0,0,0,.28);
  opacity: 0;
  transition: all .18s ease;
}

.gmail-toast.show{
  display:block;
  transform: translateX(-50%) translateY(0);
  opacity: 1;
}

  .gmail-toast .trow{ display:flex; gap:10px; align-items:flex-start; }
  .gmail-toast .ticon{
    width: 28px; height: 28px;
    border-radius: 10px;
    display:flex; align-items:center; justify-content:center;
    background: rgba(255,255,255,.14);
    flex: 0 0 28px;
  }
  .gmail-toast .tmsg{ font-size: 13px; line-height: 1.35; }
  .gmail-toast .tsub{ font-size: 12px; opacity:.85; margin-top:2px; }
  .gmail-toast .tclose{
    margin-left:auto;
    color: rgba(255,255,255,.8);
    cursor: pointer;
    padding: 0 4px;
    line-height: 1;
    font-size: 18px;
  }

  /* variants */
  .gmail-toast.success{ background:#065f46; }
  .gmail-toast.info{ background:#1f2937; }
  .gmail-toast.warn{ background:#92400e; }
  .gmail-toast.error{ background:#991b1b; }
</style>

<!-- ===== MODAL: INBOX GMAIL (PRETTY) ===== -->
<div id="gmail-inbox-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content gmail-modal">
      <div class="modal-header gmail-modal__header">
        <div class="d-flex align-items-center" style="gap:.6rem;">
          <div class="gmail-icon">
            <i class="mdi mdi-email-outline"></i>
          </div>
          <div>
            <div class="gmail-title">Inbox Gmail</div>
            <div class="gmail-subtitle" id="gmail-subtitle">Tarik email terbaru & cari cepat</div>
          </div>
        </div>

        <button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">×</button>
      </div>

      <div class="modal-body">
        <!-- Toolbar -->
        <div class="gmail-toolbar">
          <div class="gmail-search">
            <i class="mdi mdi-magnify"></i>
            <input type="search" id="gmail-q" class="form-control" placeholder="Cari subject / from / snippet…">
            <button type="button" class="btn btn-light btn-sm gmail-clear" id="gmail-clear" title="Bersihkan">
              <i class="mdi mdi-close"></i>
            </button>
          </div>

         <!--  <button type="button" class="btn btn-primary btn-sm gmail-sync" id="gmail-sync-btn">
            <i class="fe-refresh-ccw mr-1"></i> Refresh
          </button> -->
          <button type="button" class="btn btn-primary btn-sm gmail-sync" id="gmail-sync-btn">
            <i class="fe-refresh-ccw mr-1"></i><span class="btn-text"> Refresh</span>
          </button>

        </div>

        <!-- Loading (shimmer) -->
        <div id="gmail-loading" class="gmail-loading" style="display:none">
          <div class="gmail-skel">
            <div class="skel-avatar"></div>
            <div class="skel-lines">
              <div class="skel-line w60"></div>
              <div class="skel-line w90"></div>
              <div class="skel-line w40"></div>
            </div>
          </div>
          <div class="gmail-skel">
            <div class="skel-avatar"></div>
            <div class="skel-lines">
              <div class="skel-line w50"></div>
              <div class="skel-line w80"></div>
              <div class="skel-line w35"></div>
            </div>
          </div>
          <div class="gmail-skel">
            <div class="skel-avatar"></div>
            <div class="skel-lines">
              <div class="skel-line w70"></div>
              <div class="skel-line w95"></div>
              <div class="skel-line w45"></div>
            </div>
          </div>
          <div class="gmail-loading-text">
            <span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
            Memuat email…
          </div>
        </div>

        <!-- Empty State -->
        <div id="gmail-empty" class="gmail-empty" style="display:none">
          <div class="gmail-empty__icon">
            <i class="mdi mdi-inbox-arrow-down-outline"></i>
          </div>
          <div class="gmail-empty__title">Belum ada email</div>
          <div class="gmail-empty__desc">Klik <b>Refresh</b> untuk mengambil email terbaru.</div>
        </div>

        <!-- List -->
        <div id="gmail-list" class="list-group gmail-list"></div>
      </div>

      <div class="modal-footer gmail-modal__footer">
        <div class="d-flex align-items-center" style="gap:.5rem;">
          <span class="badge badge-pill badge-light border" id="gmail-count">0 email</span>
          <small class="text-muted" id="gmail-hint">Klik item untuk detail.</small>
        </div>
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
$(document).on('click', '#gmail-clear', function(){
  $('#gmail-q').val('');
  loadInbox({silent:false, limit:20});
  try{ document.getElementById('gmail-q').focus(); }catch(e){}
});

</script>

<script>
(function(){
  if (window.__GMAIL_UI__) return; window.__GMAIL_UI__ = true;

  const URL_LIST   = "<?= site_url('admin_pos/gmail_inbox') ?>";
  const URL_SYNC   = "<?= site_url('admin_pos/gmail_sync') ?>";
  const URL_DETAIL = "<?= site_url('admin_pos/gmail_detail') ?>/";

  let autoSyncTimer = null;
  let isSyncing = false;

  function esc(s){ return (s||'').toString().replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' }[m])); }


    let toastTimer = null;

  function showToast(msg, sub, type){
    type = type || 'info';

    let el = document.getElementById('gmail-toast');
    if (!el){
      el = document.createElement('div');
      el.id = 'gmail-toast';
      el.className = 'gmail-toast';
      el.innerHTML = `
        <div class="trow">
          <div class="ticon"><i class="mdi mdi-bell-outline"></i></div>
          <div style="min-width:0">
            <div class="tmsg" id="gmail-toast-msg"></div>
            <div class="tsub" id="gmail-toast-sub" style="display:none"></div>
          </div>
          <div class="tclose" title="Tutup">&times;</div>
        </div>
      `;
      document.body.appendChild(el);

      el.querySelector('.tclose').addEventListener('click', function(){
        hideToast();
      });
    }

    // set variant + icon
    el.classList.remove('success','info','warn','error');
    el.classList.add(type);

    const icon = el.querySelector('.ticon i');
    if (icon){
      icon.className = 'mdi ' + (
        type === 'success' ? 'mdi-check-circle-outline' :
        type === 'error'   ? 'mdi-alert-circle-outline' :
        type === 'warn'    ? 'mdi-alert-outline' :
                             'mdi-bell-outline'
      );
    }

    const msgEl = document.getElementById('gmail-toast-msg');
    const subEl = document.getElementById('gmail-toast-sub');
    if (msgEl) msgEl.textContent = msg || '';
    if (sub){
      subEl.style.display = '';
      subEl.textContent = sub;
    }else{
      subEl.style.display = 'none';
      subEl.textContent = '';
    }

    clearTimeout(toastTimer);
    el.style.display = 'block';
    requestAnimationFrame(()=> el.classList.add('show'));

    toastTimer = setTimeout(hideToast, 2800);
  }

  function hideToast(){
    const el = document.getElementById('gmail-toast');
    if (!el) return;
    el.classList.remove('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(()=>{ try{ el.style.display='none'; }catch(e){} }, 200);
  }


  function uiState(state){ // 'loading' | 'list' | 'empty'
    const $loading = $('#gmail-loading');
    const $list    = $('#gmail-list');
    const $empty   = $('#gmail-empty');

    if (state === 'loading'){
      $loading.show();
      $list.hide();
      $empty.hide();
      return;
    }
    if (state === 'empty'){
      $loading.hide();
      $list.hide();
      $empty.show();
      return;
    }
    // list
    $loading.hide();
    $empty.hide();
    $list.show();
  }

  function setSubtitle(text){
    const el = document.getElementById('gmail-subtitle');
    if (el) el.textContent = text || 'Tarik email terbaru & cari cepat';
  }

  function gmailSyncing(on){
    isSyncing = !!on;
    const $btn = $('#gmail-sync-btn');
    if ($btn.length){
      $btn.prop('disabled', on);
      $btn.find('i').toggleClass('spin', on);
      $btn.find('.btn-text').text(on ? ' Sync…' : ' Refresh'); // pastikan tombol punya span.btn-text
    }
    setSubtitle(on ? 'Sinkronisasi Gmail… (background)' : 'Tarik email terbaru & cari cepat');
  }

  function renderInbox(rows){
    const $list  = $('#gmail-list');
    const $count = $('#gmail-count');

    rows = rows || [];
    $count.text(rows.length + ' email');

    if (!rows.length){
      uiState('empty');
      return;
    }

    $list.empty();

    rows.forEach(r=>{
      const badge = (r.status === 'diproses')
        ? '<span class="badge badge-success">Diproses</span>'
        : '<span class="badge badge-warning">Baru</span>';

      const html = `
        <button type="button" class="list-group-item list-group-item-action" onclick="gmailOpenDetail(${parseInt(r.id,10)})">
          <div class="gmail-item__top">
            <div class="gmail-from">${esc(r.from_email||'-')}</div>
            <div class="gmail-date">${esc(r.received_at||'')}</div>
          </div>
          <div class="gmail-subject text-truncate">${esc(r.subject||'(tanpa subject)')}</div>
          <div class="d-flex align-items-center justify-content-between" style="gap:.5rem">
            <div class="gmail-snippet text-truncate" style="min-width:0">${esc(r.snippet||'')}</div>
            <div>${badge}</div>
          </div>
        </button>`;
      $list.append(html);
    });

    uiState('list');
  }

  // ===== READ TABLE ONLY =====
  function loadInbox(opts){
    opts = opts || {};
    const q = ($('#gmail-q').val() || '').trim();

    if (!opts.silent) uiState('loading');

    return $.getJSON(URL_LIST, { limit: opts.limit || 20, q })
      .done(function(res){
        const ok = (res && (res.ok === true || res.success === true));
        if (!ok){
          renderInbox([]);
          if (window.Swal) Swal.fire(res?.title||'Gagal', res?.msg||res?.pesan||'Tidak bisa memuat inbox', 'error');
          return;
        }
        renderInbox(res.data || []);
      })
      .fail(function(xhr){
        renderInbox([]);
        if (window.Swal) Swal.fire('Error','Koneksi bermasalah saat memuat Gmail','error');
        console.error(xhr?.responseText);
      });
  }

  // ===== SYNC BACKGROUND =====
  function syncInbox(opts){
    opts = opts || {};
    if (isSyncing) return;

    gmailSyncing(true);

    $.getJSON(URL_SYNC, { limit: opts.limit || 20 })
      .done(function(res){
      if (!res || res.ok !== true) {
        showToast('Sync gagal', 'Response tidak valid', 'error');
        console.warn('Sync gagal', res);
        return;
      }
      const sync = res.sync || null;
      if (!sync || sync.ok !== true) {
        showToast('Sync error', sync?.msg || 'Tidak bisa ambil email', 'error');
        console.warn('Sync error', sync);
        return;
      }

      const inserted = sync.inserted ? parseInt(sync.inserted,10) : 0;

      if (inserted > 0){
        showToast(`✅ ${inserted} email baru masuk`, sync.last_sync_new ? ('Last sync: '+sync.last_sync_new) : '', 'success');
        loadInbox({silent:true, limit: opts.limit || 20});
      } else {
        showToast('Tidak ada email baru', sync.last_sync_new ? ('Last sync: '+sync.last_sync_new) : '', 'info');
      }
    })
    .fail(function(xhr){
      showToast('Sync gagal', 'Koneksi/Server bermasalah', 'error');
      console.error(xhr?.responseText);
    })

      .fail(function(xhr){
        console.error(xhr?.responseText);
      })
      .always(function(){
        gmailSyncing(false);
      });
  }

  // ===== OPEN MODAL: table dulu, lalu autosync =====
  window.openGmailInbox = function(){
    $('#gmail-inbox-modal').modal('show');

    loadInbox({silent:false, limit:20}).always(function(){
      clearTimeout(autoSyncTimer);
      autoSyncTimer = setTimeout(function(){
        syncInbox({limit:20});
      }, 500);
    });

    setTimeout(()=>{ try{ document.getElementById('gmail-q').focus(); }catch(e){} }, 150);
  };

  $('#gmail-inbox-modal').on('hidden.bs.modal', function(){
    clearTimeout(autoSyncTimer);
  });

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

  // tombol refresh = paksa sync
  $(document).on('click', '#gmail-sync-btn', function(){
    syncInbox({limit:20});
  });

  // clear search
  $(document).on('click', '#gmail-clear', function(){
    $('#gmail-q').val('');
    loadInbox({silent:false, limit:20});
    try{ document.getElementById('gmail-q').focus(); }catch(e){}
  });

  // search debounce: hanya baca table
  let t=null;
  $(document).on('input', '#gmail-q', function(){
    clearTimeout(t);
    t = setTimeout(()=>loadInbox({silent:false, limit:20}), 250);
  });

})();
</script>




<style>
/* biar icon refresh muter saat sync */
.spin{ animation: spin 1s linear infinite; display:inline-block; }
@keyframes spin{ from{ transform:rotate(0deg);} to{ transform:rotate(360deg);} }
</style>

  <script>
    const IS_KB = <?= $isKB ? 'true' : 'false' ?>;
  </script>

  <?php
    $this->load->view("backend/global_css");
    $this->load->view($controller."_js");
  ?>
</div>
