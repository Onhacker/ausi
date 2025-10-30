<script type="text/javascript">
	$(document).ready(function() {
		const total = <?= count($statistik_permohonan) ?>;
		if (total <= 10) {
			$('#loadMoreBtn').hide();
		}
	});
let offset = 10;
const limit = 10;
$('#loadMoreBtn').on('click', function () {
	const btn = $(this);
	const spinner = $('#spinner');

	btn.prop('disabled', true);
	spinner.removeClass('d-none');
	<?php if ($this->uri->segment(1)== "admin_permohonan") {
		$con = "admin_permohonan";
	} else {
		$con = "home";
	} ?>
	$.get("<?= base_url($con.'/get_permohonan_batch/') ?>" + offset, function (res) {
		let data = JSON.parse(res);

		if (data.length === 0) {
			btn.hide();
			return;
		}

		let firstNewElement = null;
		data.forEach((per, index) => {
			let lg = (['1', '2', '3'].includes(per.id_permohonan)) ? '4' : '3';
			let rib = (['1', '2', '3'].includes(per.id_permohonan)) ? 'ribbon-box' : '';
			let ribb = (['1', '2', '3'].includes(per.id_permohonan)) ? '<div class="ribbon-two ribbon-two-primary"><span>Package</span></div>' : '';
			let icon = per.icon ? per.icon : 'default.png';

			let statusHTML = '';
			if ("<?= $this->uri->segment(1) ?>" === "admin_permohonan") {
				let statusList = {
					status_0: {icon: 'fa-hourglass-start', color: 'secondary', label: 'Belum Diproses'},
					status_1: {icon: 'fa-sync-alt', color: 'warning', label: 'Sementara Diproses'},
					status_2: {icon: 'fa-building', color: 'primary', label: 'Menunggu Verifikasi Capil'},
					status_3: {icon: 'fa-check-circle', color: 'success', label: 'Disetujui'},
					status_4: {icon: 'fa-times-circle', color: 'danger', label: 'Ditolak'}
				};

				Object.entries(statusList).forEach(([key, val]) => {
					statusHTML += `
					<div class="col-6 col-md-2 position-relative d-none d-md-block mb-2">
					<i class="fas ${val.icon} fa-2x text-${val.color} mb-1"
					title="${per.jumlah[key]} Permohonan ${val.label}"
					onmouseover="showToast(this)"
					onmouseout="hideToast(this)">
					</i>
					<div class="toast-hover bg-${val.color} text-white px-2 py-1 rounded shadow">
					${per.jumlah[key]} ${val.label}
					</div>
					<h6 class="mb-0">
					<span data-plugin="counterup">${per.jumlah[key]}</span>
					</h6>
					</div>`;
				});

				statusHTML = `<div class="row justify-content-center text-center">${statusHTML}</div>`;
			}

			let buttonHTML = ("<?= $this->uri->segment(1) ?>" != "admin_permohonan")
			? `<div class="mt-0 mb-1"><a href="javascript:void(0);" onclick="loadSyarat(${per.id_permohonan})" class="btn btn-sm btn-primary">Lihat Syarat</a></div>`
			: '';

			let html = `
			<div class="col-lg-${lg} d-flex align-items-stretch fade-in">

			<?php if ($this->uri->segment(1) == "admin_permohonan") {?>
				<div class="card-box ${rib} bg-pattern w-100 ${"<?= $this->uri->segment(1) ?>" === "admin_permohonan" ? 'card-clickable' : ''}"
				onclick="handleCardClick(${per.id_permohonan})">
			<?php } else { ?>
				<div class="card-box ${rib} bg-pattern w-100">
			<?php } ?>
			
			${ribb}
			<div class="d-flex flex-column justify-content-between h-100 rounded shadow-sm bg-white text-center">
			<div>
			<img src="<?= base_url('assets/images/web/') ?>${icon}"
			alt="logo"
			class="avatar-xl img-thumbnail mb-1 border border-light shadow-sm"
			style="object-fit: cover; width: 100px; height: 100px;">
			<h4 class="mb-1 font-weight-bold text-dark">${per.nama_permohonan}</h4>
			<p class="text-dark font-14">
			<code class="text-dark"><b>Permohonan</b> :</code> ${per.deskripsi || '<i>Tidak ada deskripsi</i>'}
			</p>
			</div>
			${statusHTML}
			${buttonHTML}
			</div>
			</div>
			</div>`;

			// let $el = $(html).appendTo('#defaultContainer');
			// setTimeout(() => {
			// 	$el.addClass('show');
   //          }, 10); // kecilkan delay agar transisi muncul
		   let $el = $(html).appendTo('#defaultContainer');

			// Simpan elemen pertama yang ditambahkan
			if (index === 0) {
				firstNewElement = $el;
			}

			setTimeout(() => {
				$el.addClass('show');
			}, 10);

		});

		offset += limit;
		btn.prop('disabled', false);
		spinner.addClass('d-none');

		if (data.length < limit) {
            btn.hide(); // Sembunyikan tombol jika data batch < limit
        }

        // Scroll ke bawah setelah data ditambahkan
        // $('html, body').animate({
        // 	scrollTop: $('#defaultContainer').offset().top + $('#defaultContainer').height()
        // }, 500); // 500ms untuk smooth scroll
        if (firstNewElement) {
			$('html, body').animate({
				scrollTop: firstNewElement.offset().top - 50 // -20 agar ada sedikit jarak atas
			}, 500);
		}
    });
});
</script>