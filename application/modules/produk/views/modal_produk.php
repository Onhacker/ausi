<style type="text/css">
#modalProduk.modal{z-index:200200!important;overflow-y:auto}.modal-backdrop{z-index:200190!important;backdrop-filter:none!important;-webkit-backdrop-filter:none!important}.backdrop-blur,.blur,.is-blurred,.modal-open *{filter:none!important;-webkit-filter:none!important}#modalProduk .modal-body{max-height:none!important}.product-info a.rate-link,.product-info a.rate-link:active,.product-info a.rate-link:focus,.product-info a.rate-link:hover,.product-info a.rate-link:visited{font-weight:500!important}#modalProduk .modal-dialog{margin:.75rem auto;max-height:calc(100vh - 1.5rem)}#modalProduk .modal-content{height:auto;display:flex;flex-direction:column;max-height:100%}#modalProduk .modal-footer,#modalProduk .modal-header{flex:0 0 auto}#modalProduk .modal-body{flex:1 1 auto;overflow:auto!important;-webkit-overflow-scrolling:touch}#modalProduk .modal-body img{max-width:100%;height:auto}.spinner-border{display:inline-block;width:.9rem;height:.9rem;border:.15rem solid currentColor;border-right-color:transparent;border-radius:50%;animation:.6s linear infinite spin;vertical-align:-.2em;margin-right:.4rem}@keyframes spin{to{transform:rotate(360deg)}}#btnLihatSelengkapnya.is-loading{pointer-events:none;opacity:.8}
</style>

<!-- ===== Modal Detail Produk ===== -->
<div class="modal fade" id="modalProduk" tabindex="-1" aria-hidden="true" aria-labelledby="modalProdukTitle">
	<!-- <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"> -->
		<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"  id="modalProdukDialog">

			<div class="modal-content">
				<div class="modal-header">
					<h5 id="modalProdukTitle" class="modal-title">Detail Produk</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body" id="modalProdukBody">
					<div class="text-center py-5 text-muted">Memuat…</div>
				</div>
				<!-- Pastikan footer sudah kiri–kanan -->
				<div class="modal-footer d-flex justify-content-between align-items-center w-100">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
					<?php if ($this->uri->segment(2)<> "cart") {?>
						<a id="btnLihatSelengkapnya" href="#" class="btn btn-blue"
					rel="noopener" data-loading-label="Membuka…">
					<span class="spinner-border d-none" aria-hidden="true"></span>
					<span class="btn-text">selengkapnya</span>
				</a>
					<?php } ?>
					

			</div>


		</div>
	</div>
</div>
<script type="text/javascript">
	function modalSkeleton(){
		return `
		<div class="px-2">
		<div class="skel-thumb skel-shimmer" style="aspect-ratio:4/3;border-radius:12px;"></div>
		<div class="mt-3 skel-line w80 skel-shimmer"></div>
		<div class="skel-line w60 skel-shimmer"></div>
		<div class="mt-2 skel-line w80 skel-shimmer"></div>
		<div class="skel-line w40 skel-shimmer"></div>
		</div>`;
	}
	/* Pastikan modal ditempel ke body & z-index aman */
	$('#modalProduk').on('show.bs.modal', function(){
		if (this.parentElement !== document.body) document.body.appendChild(this);
	});
	const SITE_PRODUK_DETAIL = "<?= rtrim(site_url('produk/detail'), '/') ?>";

	function slugifyNamaProduk(nama){
		return (nama || '')
		.toString()
		.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
		.toLowerCase()
		.replace(/[^a-z0-9\s-]/g, '')
		.trim()
		.replace(/\s+/g, '-')
		.replace(/-+/g, '-');
	}

	function bindDetailModal(){
		$('#grid-products')
		.off('click', '.btn-detail')
		.on('click', '.btn-detail', function(e){
			e.preventDefault();

      // slug awal dari tombol
      let slug = ($(this).data('slug') || '').toString().trim();
      const detailHref = SITE_PRODUK_DETAIL + '/' + encodeURIComponent(slug || '');

      // set judul/skeleton + buka modal dulu
      $('#modalProdukTitle').text('Detail Produk');
      $('#modalProdukBody').html( modalSkeleton() );
      $('#btnLihatSelengkapnya').attr('href', detailHref);
      $('#modalProduk').modal('handleUpdate').modal('show');

      // fetch konten modal
      $.getJSON("<?= site_url('produk/detail_modal'); ?>", { slug })
      .done(function(r){
      	if (!r || !r.success){
      		$('#modalProdukBody').html('<div class="text-danger p-3">Gagal memuat detail.</div>');
      		return;
      	}

        // update judul & isi
        if (r.title) $('#modalProdukTitle').text(r.title);
        $('#modalProdukBody').html(r.html);
        $('#modalProduk').modal('handleUpdate');

        // perbarui link 'lihat selengkapnya' jika server kasih info lebih akurat
        // prioritas: r.detail_url > r.slug || r.link_seo > r.nama (slugify)
        let finalHref = null;

        if (r.detail_url){
          finalHref = r.detail_url; // diasumsikan sudah absolute/relative siap pakai
      } else {
      	const respSlug = (r.slug || r.link_seo || '').toString().trim();
      	if (respSlug){
      		finalHref = SITE_PRODUK_DETAIL + '/' + encodeURIComponent(respSlug);
      	} else if (r.nama || r.nama_produk || r.title){
      		const s = slugifyNamaProduk(r.nama || r.nama_produk || r.title);
      		finalHref = SITE_PRODUK_DETAIL + '/' + encodeURIComponent(s);
      	}
      }

      if (finalHref){
      	$('#btnLihatSelengkapnya').attr('href', finalHref);
      }
  })
      .fail(function(){
      	$('#modalProdukBody').html('<div class="text-danger p-3">Koneksi bermasalah.</div>');
      });
  });
	}
	function safeQty(val){
		const n = Number(val);
		return Number.isFinite(n) && n > 0 ? n : 1;
	}
</script>

<script>
	(function(){
		var btn = document.getElementById('btnLihatSelengkapnya');
		if(!btn) return;

		function setBtnLoading(on){
			var sp  = btn.querySelector('.spinner-border');
			var txt = btn.querySelector('.btn-text');
			if(on){
				btn.classList.add('is-loading','disabled');
				btn.setAttribute('aria-disabled','true');
				sp.classList.remove('d-none');
				if(!btn.dataset.originalLabel){ btn.dataset.originalLabel = (txt.textContent || '').trim(); }
				txt.textContent = btn.dataset.loadingLabel || 'Memuat…';
			}else{
				btn.classList.remove('is-loading','disabled');
				btn.removeAttribute('aria-disabled');
				sp.classList.add('d-none');
				if(btn.dataset.originalLabel){ txt.textContent = btn.dataset.originalLabel; }
			}
		}

  // Klik: tampilkan loading. Jika target _blank, kembalikan setelah sebentar.
  btn.addEventListener('click', function(){
  	setBtnLoading(true);
    // Jika buka tab baru, jangan “nyangkut” loading-nya
    if (btn.target === '_blank'){
    	setTimeout(function(){ setBtnLoading(false); }, 1500);
    }
});

  // Opsional: bila modal ditutup, reset loading
  if (window.jQuery){
  	$('#modalProduk').on('hidden.bs.modal', function(){ setBtnLoading(false); });
  }
})();
</script>
<?php $this->load->view("partials/form_rating") ?>