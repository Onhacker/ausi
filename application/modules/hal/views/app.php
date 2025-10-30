<?php $this->load->view("front_end/head.php") ?>
<style>
/* ====== Overlay Instal iOS PWA ====== */
.ios-a2hs-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.58);
  -webkit-backdrop-filter: blur(4px);
  backdrop-filter: blur(4px);
  display: none; /* default hidden */
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  z-index: 99999;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  color: #1f2937;
}

.ios-a2hs-card {
  position: relative;
  background: #ffffff;
  border-radius: 1rem;
  width: 100%;
  max-width: 340px;
  box-shadow: 0 24px 48px rgba(0,0,0,0.32);
  animation: ios-pop 0.28s cubic-bezier(.2,1.15,.4,1.2);
  padding: 1.25rem 1.25rem 1rem;
  text-align: center;
}

/* Tombol close (X) */
.ios-a2hs-close {
  position: absolute;
  top: .5rem;
  right: .5rem;
  background: rgba(0,0,0,0.06);
  border: 0;
  border-radius: .5rem;
  width: 32px;
  height: 32px;
  font-size: 1.1rem;
  line-height: 32px;
  cursor: pointer;
  color: #374151;
  font-weight: 600;
}

.ios-a2hs-title {
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: .25rem;
}
.ios-a2hs-desc {
  font-size: .8rem;
  color: #6b7280;
  line-height: 1.4;
  margin-bottom: 1rem;
}

/* Mockup Safari iOS */
.ios-phone-frame {
  background: #f9fafb;
  border-radius: .75rem;
  border: 2px solid #e5e7eb;
  box-shadow: inset 0 0 0 2px #fff;
  padding: .75rem .75rem 1rem;
  text-align: left;
  position: relative;
  margin-bottom: 1rem;
}

/* Bar atas Safari */
.ios-safari-bar {
  background: #ffffff;
  border: 1px solid #d1d5db;
  border-radius: .5rem;
  padding: .5rem .75rem;
  font-size: .7rem;
  line-height: 1.2;
  color: #374151;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
}

.ios-url-pill {
  background: #f3f4f6;
  border-radius: .4rem;
  padding: .25rem .5rem;
  font-size: .65rem;
  color: #4b5563;
  font-weight: 500;
  flex-grow: 1;
  margin-right: .5rem;
  text-overflow: ellipsis;
  overflow: hidden;
  white-space: nowrap;
}
.ios-share-btn {
  position: relative;
  width: 28px;
  height: 28px;
  border-radius: .5rem;
  background: #2563eb;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 8px 16px rgba(37,99,235,0.45);
}
/* ikon share (kotak + panah) */
.ios-share-icon {
  width: 16px;
  height: 16px;
  color: #fff;
  display: block;
  position: relative;
}
.ios-share-icon .box {
  position: absolute;
  left: 2px;
  right: 2px;
  bottom: 2px;
  top: 6px;
  border: 2px solid currentColor;
  border-radius: 3px;
}
.ios-share-icon .arrow {
  position: absolute;
  left: 50%;
  top: 0;
  width: 0;
  height: 0;
  border-left: 4px solid transparent;
  border-right: 4px solid transparent;
  border-bottom: 6px solid currentColor;
  transform: translateX(-50%);
}
.ios-share-icon .stem {
  position: absolute;
  top: 4px;
  left: 50%;
  width: 2px;
  height: 6px;
  background: currentColor;
  transform: translateX(-50%);
  border-radius: 1px;
}

/* Panah animasi langkah 1 ("Tap sini") */
.ios-step-arrow {
  position: absolute;
  right: .25rem;
  top: calc(100% + .25rem);
  font-size: .7rem;
  background: #111827;
  color: #fff;
  padding: .3rem .5rem .3rem .5rem;
  border-radius: .5rem;
  line-height: 1.2;
  box-shadow: 0 10px 20px rgba(0,0,0,.4);
  animation: bounceArrow 1.4s infinite;
}
.ios-step-arrow::after {
  content: '';
  position: absolute;
  top: -6px;
  right: 10px;
  border-width: 0 6px 6px 6px;
  border-style: solid;
  border-color: transparent transparent #111827 transparent;
  filter: drop-shadow(0 -1px 0 rgba(0,0,0,.4));
}

/* Area bawah: Step list */
.ios-steps {
  font-size: .8rem;
  line-height: 1.4;
  color: #374151;
}
.ios-step {
  display: flex;
  text-align: left;
  margin-bottom: .75rem;
  align-items: flex-start;
}
.ios-step:last-child { margin-bottom: 0; }
.ios-step-num {
  background: #2563eb;
  color: #fff;
  font-size: .7rem;
  font-weight: 600;
  line-height: 1.5rem;
  width: 1.5rem;
  height: 1.5rem;
  text-align: center;
  border-radius: .5rem;
  margin-right: .5rem;
  flex-shrink: 0;
  box-shadow: 0 6px 14px rgba(37,99,235,0.45);
}
.ios-step-text {
  font-size: .8rem;
  line-height: 1.4;
  color: #1f2937;
}
.ios-step-hint {
  font-size: .7rem;
  color: #6b7280;
  margin-top: .2rem;
}

/* animasi: card pop in */
@keyframes ios-pop {
  0%   { transform: scale(.8) translateY(20px); opacity:0; }
  60%  { transform: scale(1.03) translateY(-2px); opacity:1; }
  100% { transform: scale(1) translateY(0); opacity:1; }
}

/* animasi panah/label "Tap sini" nge-bounce */
@keyframes bounceArrow {
  0%,100% { transform: translateY(0); }
  50%     { transform: translateY(-4px); }
}

/* Utility to show/hide overlay */
.ios-a2hs-overlay.show {
  display: flex;
}
</style>

<?php
  // Ganti package kalau beda
          $playPackage = '#';
          $playUrl     = 'https://play.google.com/store/apps/details?id=' . $playPackage;
          ?>
<div class="container-fluid">
	<div class="hero-title" role="banner" aria-label="Judul situs">
		<h1 class="text"><?php echo $title ?></h1>

		<span class="accent" aria-hidden="true"></span>
	</div>
	<div class="row mt-3">
		<div class="col-lg-12">
			<div class="card-box">
					<div class="text-center store-badges">
              <!-- Badge resmi Google Play -->
              <a id="playstoreBadge"
              href="<?= $playUrl ?>"
              onclick="return openPlayStore(event)"
              class="d-inline-block my-2"
              aria-label="Download di Google Play">
              <img alt="Download di Google Play"
              src="<?php echo base_url('assets/images/gp.webp') ?>"
              style="height:56px;width:auto;">
            </a>

            <!-- Badge iOS / PWA -->
            <a id="installButton"
            href="#"
            class="d-inline-block my-2 ms-2 ml-2"
            aria-label="Instal ke iOS (PWA)">
            <img alt="Instal ke iOS (Tambahkan ke Layar Utama)"
            src="<?= base_url('assets/images/ios.webp') ?>"
            style="height:56px;width:auto;">
          </a>
        </div>

			</div>
		</div>
	</div>
</div>


<!-- ===== Overlay Instal PWA iOS ===== -->
<div id="ios-a2hs-overlay" class="ios-a2hs-overlay" role="dialog" aria-modal="true" aria-labelledby="ios-a2hs-title" aria-describedby="ios-a2hs-desc">
  <div class="ios-a2hs-card">
    <button class="ios-a2hs-close" type="button" aria-label="Tutup panduan">&times;</button>

    <div class="ios-a2hs-title" id="ios-a2hs-title">
      Tambahkan ke Layar Utama
    </div>
    <div class="ios-a2hs-desc" id="ios-a2hs-desc">
      Instal aplikasi ini di iPhone / iPad supaya bisa dibuka seperti aplikasi biasa.
    </div>

    <div class="ios-phone-frame">
      <div class="ios-safari-bar">
        <div class="ios-url-pill">
          <?= htmlspecialchars(base_url(), ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div class="ios-share-btn" aria-hidden="true">
          <div class="ios-share-icon">
            <div class="box"></div>
            <div class="arrow"></div>
            <div class="stem"></div>
          </div>
        </div>

        <!-- gelembung step (Tap sini) -->
        <div class="ios-step-arrow">
          Ketuk tombol <b>Bagikan</b><br>(ikon panah ke atas)
        </div>
      </div>
    </div>

    <div class="ios-steps">
      <div class="ios-step">
        <div class="ios-step-num">1</div>
        <div class="ios-step-text">
          Tekan ikon <b>Bagikan</b> di Safari
          <div class="ios-step-hint">Ikon kotak dengan panah ke atas (lihat di atas)</div>
        </div>
      </div>
      <div class="ios-step">
        <div class="ios-step-num">2</div>
        <div class="ios-step-text">
          Pilih <b>Tambahkan ke Layar Utama</b>
          <div class="ios-step-hint">Scroll kalau belum kelihatan</div>
        </div>
      </div>
      <div class="ios-step">
        <div class="ios-step-num">3</div>
        <div class="ios-step-text">
          Tekan <b>Tambahkan</b>
          <div class="ios-step-hint">Selesai! Ikon aplikasi akan muncul di Home Screen</div>
        </div>
      </div>
    </div>

  </div>
</div>
<!-- ===== /Overlay Instal PWA iOS ===== -->

<script>
// helper deteksi iOS & Safari seperti sebelumnya
function isIOSUA() {
  const ua = navigator.userAgent || navigator.vendor || '';
  return /iPad|iPhone|iPod/i.test(ua) || (ua.includes('Macintosh') && 'ontouchend' in document);
}
function isSafariIOS() {
  const ua = navigator.userAgent || navigator.vendor || '';
  const isSafari = /^((?!chrome|android|crios|fxios).)*safari/i.test(ua);
  return isIOSUA() && isSafari;
}

/* === Overlay controller === */
(function initIOSOverlayInstallGuide(){
  // ambil elemen overlay + tombol close
  const overlayEl = document.getElementById('ios-a2hs-overlay');

  // simpan ref global biar bisa dipanggil dari installButton click
  window.__iosA2HS = {
    open: function(){
      if (!overlayEl) return;
      overlayEl.classList.add('show');
    },
    close: function(){
      if (!overlayEl) return;
      overlayEl.classList.remove('show');
    }
  };

  // tombol close (X)
  if (overlayEl){
    const closeBtn = overlayEl.querySelector('.ios-a2hs-close');
    if (closeBtn){
      closeBtn.addEventListener('click', function(){
        window.__iosA2HS.close();
      });
    }

    // klik backdrop di luar kartu -> tutup juga
    overlayEl.addEventListener('click', function(e){
      if (e.target === overlayEl){
        window.__iosA2HS.close();
      }
    });
  }

  // inject fallback global lama: showIOSInstallGuide()
  window.showIOSInstallGuide = function(e){
    if (e) e.preventDefault();

    // jika bukan Safari iOS, jangan bohongi user
    if (!isSafariIOS()){
      // fallback behaviour
      if (window.Swal?.fire){
        Swal.fire({
          title:'Buka di Safari',
          html:'Untuk instal di iPhone/iPad:<br>1. Buka pakai <b>Safari</b>.<br>2. Tap ikon Bagikan → <b>Tambahkan ke Layar Utama</b>.',
          icon:'info'
        });
      } else {
        alert('Buka halaman ini di Safari.\nLalu: Bagikan → Tambahkan ke Layar Utama.');
      }
      return false;
    }

    // tampilkan overlay animasi khusus iOS
    window.__iosA2HS.open();
    return false;
  };
})();
</script>

<?php $this->load->view("front_end/footer.php") ?>
