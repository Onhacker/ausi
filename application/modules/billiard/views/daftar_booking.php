<?php $this->load->view("front_end/head.php") ?>

<div class="container-fluid">
  <div class="hero-title" role="banner" aria-label="Judul situs">
    <h1 class="text">Bookingan Billiard</h1>
    <span class="accent" aria-hidden="true"></span>
  </div>

  <div class="row">
    <?php if (empty($cards)): ?>
      <div class="col-12">
        <div class="card-box"><p class="mb-0">Belum ada bookingan mendatang.</p></div>
      </div>
    <?php else: ?>
      <?php foreach ($cards as $c): ?>
        <div class="col-xl-6 col-lg-12">
          <!-- padding-bottom ditambah supaya tombol ribbon bawah tidak menutupi konten -->
          <div class="card-box mt-3" style="position:relative; padding-top:46px; padding-bottom:64px;">

            <!-- Ribbon nama meja (TENGAH) + pill jumlah booking -->
            <div class="meja-ribbon"><span><?= html_escape($c['nama_meja']) ?></span></div>
            <div class="book-count"><?= (int)$c['booking_count'] ?> booking</div>

            <!-- ====== STYLE ====== -->
            <style type="text/css">
              /* Ribbon nama meja (center) */
              .meja-ribbon{position:absolute;top:-12px;left:50%;transform:translateX(-50%);z-index:2}
              .meja-ribbon span{
                background:#d30048;color:#fff;font-weight:700;font-size:14px;
                padding:6px 14px;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,.15)
              }
              .book-count{
                position:absolute;top:10px;right:12px;z-index:2;
                background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;
                border-radius:999px;padding:4px 10px;font-size:12px;font-weight:600
              }

              /* CTA Ribbon (bawah kanan) */
              .ribbon-cta{position:absolute;right:10px;bottom:10px;z-index:2}
              .ribbon-cta .cta-btn{
                position:relative;display:inline-flex;align-items:center;gap:1px;
                background:#3f51b5;color:#fff;text-decoration:none;
                padding:7px 12px;border-radius:8px;font-weight:800;font-size:12px;
                box-shadow:0 2px 6px rgba(0,0,0,.2);
                transform:rotate(-5deg); border:1px solid rgba(255,255,255,.15)
              }
              .ribbon-cta .cta-btn:hover{filter:brightness(1.05)}
              .ribbon-cta .cta-btn i{font-style:normal;opacity:.95}
              .ribbon-cta .cta-btn:after{
                content:"→"; font-weight:900; line-height:1; transform:translateY(-.5px)
              }

              /* Kalender mini */
              .cal-ava{width:46px;display:flex;flex-direction:column;align-items:center;gap:2px;flex:0 0 46px}
              .cal-tile{width:40px;height:40px;border:1px solid #eef2f7;border-radius:8px;background:#fff;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 1px 4px rgba(15,23,42,.06)}
              .cal-tile .cal-head{height:12px;line-height:12px;text-align:center;background:#0d6efd;color:#fff;font-size:9px;font-weight:700;letter-spacing:.4px;text-transform:uppercase}
              .cal-tile .cal-day{flex:1;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:15px;color:#111827}
              .cal-cap{font-size:10px;font-weight:700;line-height:1;color:#6b7280;text-transform:uppercase;letter-spacing:.15px}
              @media(prefers-color-scheme:dark){.cal-tile{border-color:#334155;background:#0b1220}.cal-tile .cal-day{color:#e5e7eb}.cal-cap{color:#9ca3af}}

              /* Layout per tanggal */
              .day-row{display:flex;align-items:flex-start;gap:8px;margin:10px 0}
              .day-content{flex:1 1 auto;min-width:0}

              /* Bubble */
              .conversation-list{margin:0;padding-left:0;list-style:none}
              .conversation-list li{display:flex;align-items:flex-start;margin:0 0 8px 0}
              .conversation-list .conversation-text{flex:1 1 auto;display:flex;margin:0 !important}
              .conversation-list .ctext-wrap{
                width:100%;padding:.55rem .7rem;border:1px solid #eef2f7;border-radius:12px;background:#f8fafc
              }
              .conversation-list .ctext-wrap:before,
              .conversation-list .ctext-wrap:after{display:none!important}

              /* Header bubble: kiri jam+durasi+WITA, kanan status+countdown (sejajar) */
              .ct-head{display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap}
              .ct-left{font-weight:700;font-size:14px}
              .tz{font-weight:600;font-size:11px;margin-left:6px;color:#64748b}
              .ct-right{display:flex;align-items:center;gap:8px}
              .status-pill{
                display:inline-flex;align-items:center;gap:6px;
                background:#1f2937;color:#fff;border-radius:999px;padding:3px 10px;font-size:11px;font-weight:700
              }
              .status-pill.success{background:#16a34a}
              .status-pill.muted{background:#6b7280}
              .cd{font-weight:700}
              .ct-meta{margin-top:2px;font-size:12px;color:#6b7280}

              /* ➕ Tambahan: pill untuk Verifikasi + Cash */
              .verify-pill{
                display:inline-flex;align-items:center;gap:6px;
                background:#f59e0b;color:#111827;border-radius:999px;
                padding:3px 10px;font-size:11px;font-weight:700;
                border:1px solid rgba(0,0,0,.05)
              }
            </style>

            <?php
              // masker no hp simple: 4 awal + bullet + 3 akhir
              if (!function_exists('mask_hp')) {
                function mask_hp($s){
                  $d = preg_replace('/\D+/', '', (string)$s);
                  if ($d==='') return '';
                  $len = strlen($d);
                  if ($len <= 4) return str_repeat('•', $len);
                  $head = substr($d, 0, min(4, $len-3));
                  $tail = substr($d, -3);
                  $mid  = max(0, $len - strlen($head) - strlen($tail));
                  return $head . str_repeat('•', $mid) . $tail;
                }
              }
            ?>

            <!-- ====== PER-TANGGAL ====== -->
            <?php foreach ($c['days'] as $d): ?>
              <div class="day-row">
                <!-- Kalender -->
                <div class="cal-ava" title="<?= html_escape($d['tanggal_fmt']) ?>">
                  <div class="cal-tile" aria-label="<?= html_escape($d['tanggal_fmt']) ?>">
                    <div class="cal-head"><?= html_escape($d['mon']) ?></div>
                    <div class="cal-day"><?= html_escape($d['daynum']) ?></div>
                  </div>
                  <div class="cal-cap mt-1"><?= html_escape(strtoupper($d['weekday'])) ?></div>
                </div>

                <!-- Daftar bubble tanggal ini -->
                <div class="day-content">
                  <?php if (!empty($d['bookings'])): ?>
                    <ul class="conversation-list" style="height:auto;max-height:none;overflow:visible;width:auto;">
                      <?php foreach ($d['bookings'] as $b): ?>
                        <?php
                          $start_ts   = (int)$b['start_ts'];
                          $end_ts     = (int)$b['end_ts'];
                          $jam_mulai  = html_escape($b['jam_mulai']);
                          $jam_selesai= html_escape($b['jam_selesai']);
                          $durasi_jam = (int)$b['durasi_jam'];
                          $pemesan    = html_escape($b['nama'] ?? 'Booking');

                          $hp_raw     = $b['no_hp'] ?? ($b['hp'] ?? ($b['no_hp_normalized'] ?? ''));
                          $hp_masked  = mask_hp($hp_raw);

                          // ➕ Tampilkan indikator hanya jika status=verifikasi & metode_bayar=cash
                          $status_raw   = strtolower((string)($b['status'] ?? ''));
                          $method_raw   = strtolower((string)($b['metode_bayar'] ?? ($b['payment_method'] ?? '')));
                          $show_veri_cs = ($status_raw === 'verifikasi' && $method_raw === 'cash');
                        ?>
                        <li class="booking-item" data-start-ts="<?= $start_ts ?>" data-end-ts="<?= $end_ts ?>">
                          <div class="conversation-text">
                            <div class="ctext-wrap">
                              <div class="ct-head">
                                <div class="ct-left">
                                  <?= $jam_mulai ?> – <?= $jam_selesai ?> · <?= $durasi_jam ?> jam
                                  <span class="tz">WITA</span>
                                </div>
                                <div class="ct-right mb-1">
                                  <span class="status-pill">
                                    <span class="status-label">Mulai dalam</span> · <span class="cd">00:00:00</span>
                                  </span>
                                  <?php if ($show_veri_cs): ?>
                                    <span class="verify-pill" title="Status: verifikasi, metode bayar cash" aria-label="Verifikasi (Cash)">
                                      Verifikasi Cash
                                    </span>
                                  <?php endif; ?>
                                </div>
                              </div>
                              <div class="ct-meta">
                                <?= $pemesan ?>
                                <?php if ($hp_masked !== ''): ?> · <?= $hp_masked ?><?php endif; ?>
                              </div>
                            </div>
                          </div>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  <?php else: ?>
                    <div class="text-muted small">Belum ada booking pada tanggal ini.</div>
                  <?php endif; ?>
                </div>
              </div>

              <hr style="margin:10px 0">
            <?php endforeach; ?>

            <!-- CTA ribbon: Booking Baru -->
            <div class="ribbon-cta">
              <a class="cta-btn" href="<?= site_url('billiard?meja_id='.(int)$c['meja_id']) ?>"  title="Buka halaman booking billiard">
                <i>Booking Yuk !!</i>
              </a>
            </div>

          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<?php $this->load->view("front_end/footer.php") ?>

<script>
(function(){
  function pad(n){return(n<10?'0':'')+n;}
  function fmt(ms){
    if(ms<=0)return'00:00:00';
    var s=Math.floor(ms/1000);
    var d=Math.floor(s/86400); s%=86400;
    var h=Math.floor(s/3600);  s%=3600;
    var m=Math.floor(s/60);    var sec=s%60;
    var day=d>0?(d+' hari '):'';
    return day+pad(h)+':'+pad(m)+':'+pad(sec);
  }
  function tick(){
    var now=Date.now();
    document.querySelectorAll('.booking-item').forEach(function(item){
      var start=parseInt(item.getAttribute('data-start-ts'),10)||0;
      var end  =parseInt(item.getAttribute('data-end-ts'),10)||0;

      var pill=item.querySelector('.status-pill');
      var label=item.querySelector('.status-label');
      var cd=item.querySelector('.cd');
      if(!pill||!label||!cd)return;

      pill.classList.remove('success','muted');

      if(start && now<start){
        label.textContent='Mulai dalam';
        cd.textContent=fmt(start-now);
      }else if(end && now<=end){
        label.textContent='Sedang bermain';
        cd.textContent=fmt(end-now);
        pill.classList.add('success');
      }else{
        label.textContent='Selesai';
        cd.textContent='00:00:00';
        pill.classList.add('muted');
      }
    });
  }
  tick(); setInterval(tick,1000);
})();
</script>
