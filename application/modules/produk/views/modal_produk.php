<style type="text/css">
	/* Modal paling depan & tanpa blur */
	#modalProduk.modal{ z-index: 200200 !important; }
	.modal-backdrop{
		z-index: 200190 !important;
		backdrop-filter: none !important; -webkit-backdrop-filter: none !important;
	}
	.modal-open *{ filter: none !important; -webkit-filter: none !important; }
	.blur, .backdrop-blur, .is-blurred{ filter:none !important; -webkit-filter:none !important; }
	/* Auto height: modal mengikuti tinggi konten (maksimal tinggi viewport - margin) */
	#modalProduk .modal-dialog {
		max-height: calc(100vh - 2rem);   /* jaga supaya tidak lewat layar */
	}

	#modalProduk .modal-content {
		height: auto;                      /* biar tumbuh sesuai isi */
		max-height: 100%;                  /* tapi tetap patuh batas dialog */
	}

	#modalProduk .modal-body {
		overflow: visible !important;      /* hilangkan scroll internal */
		max-height: none !important;       /* jangan batasi isi body */
	}

	/* Kalau konten sangat panjang, biarkan page yang scroll */
	#modalProduk.modal {
		overflow-y: auto;
	}
/* Kunci tampilan "Review" agar tidak ikut bold */
.product-info a.rate-link{
  font-weight: 500 !important;       /* atau 400 sesuai selera */
}
.product-info a.rate-link:hover,
.product-info a.rate-link:focus,
.product-info a.rate-link:active,
.product-info a.rate-link:visited{
  font-weight: 500 !important;
}

</style>

<style>
	/* === Scroll di dalam isi modal (body), aman di semua ukuran layar === */
	#modalProduk .modal-dialog{
		margin: .75rem auto;                 /* biar ada napas */
		max-height: calc(100vh - 1.5rem);    /* jangan lebih tinggi dari viewport */
	}
	#modalProduk .modal-content{
		display: flex;
		flex-direction: column;
		max-height: 100%;
	}
	#modalProduk .modal-header,
	#modalProduk .modal-footer{
		flex: 0 0 auto;                      /* tinggi natural */
	}
	#modalProduk .modal-body{
		flex: 1 1 auto;                      /* sisa ruang untuk body */
		overflow: auto !important;           /* INI yang bikin isi modal scroll */
		-webkit-overflow-scrolling: touch;   /* smooth di iOS */
	}

	/* Kalau ada gambar/elemen lebar, jangan melebarin modal */
	#modalProduk .modal-body img{
		max-width: 100%;
		height: auto;
	}
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
					<a id="btnLihatSelengkapnya" href="#" class="btn btn-blue" rel="noopener">
						selengkapnya
					</a>
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