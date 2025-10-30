<script>
    $(document).ready(function() {
    $('#btn_cari').on('click', function() {
        var noTracking = $('#no_tracking').val().trim();

        if (noTracking === '') {
            $('#hasil_tracking').html(`<div class="alert alert-danger text-center col-md-6">Masukkan No. Tracking terlebih dahulu.</div>`);
            return;
        }

        // Menampilkan spinner loading saat permintaan AJAX dimulai
        $.ajax({
            url: '<?= site_url("tracking/cek") ?>',
            type: 'POST',
            data: { no_registrasi: noTracking },
            dataType: 'json',
            beforeSend: function() {
                $('#hasil_tracking').empty(); // Kosongkan hasil sebelumnya
                $('#loading-spinner').show(); // Tampilkan spinner loading
            },
            success: function(res) {
                $('#loading-spinner').hide(); // Sembunyikan spinner setelah respons diterima
                $('#icl').hide(); // Sembunyikan spinner setelah respons diterima
                if (res.status === true) {
                    const statusInfo = getStatusIcon(res.data.status);
                    const durasiLabel = res.data.status === 3 ? 'Durasi Permohonan' : 'Proses Berlangsung';

                    // Menambahkan pesan berkas telah dikirim via WhatsApp atau bisa diambil di kantor desa
                    let berkasInfo = '';
                    if (res.data.status === 3) {
                        berkasInfo = '<li><strong>Berkas:</strong> Berkas digital telah dikirim melalui WhatsApp. Anda juga dapat mengambil berkas fisik langsung di kantor ' + res.data.kantor + '.</li>';

                    }
                    let fisik = "";
                    if (res.data.ket && res.data.ket.trim() !== "") {
                        fisik = '<li><strong>' + res.data.ket + '</strong></li>';
                    }



                    $('#hasil_tracking').html(`
                        <div class="col-md-8">
                            <div class="card card-pricing">
                                <div class="card-body text-center">
                                    <p class="card-pricing-plan-name font-weight-bold text-uppercase">${res.data.nama_permohonan}</p>
                                    <p class="card-pricing-plan-name font-weight-bold">${res.data.deskripsi}</p>
                                    <span class="card-pricing-icon ${statusInfo.color}">
                                        <i class="${statusInfo.icon}"></i>
                                    </span>
                                    <h4 class="card-pricing-price text-dark font-weight-bold">🧑‍ ${res.data.nama}</h4>
                                    <ul class="card-pricing-features text-center text-dark mt-2">
                                        <li><strong>NIK:</strong><br> ${res.data.nik}</li>
                                        <li><strong>No. KK:</strong><br> ${res.data.no_kk}</li>
                                        <li><strong>Alamat:</strong><br> ${res.data.alamat}</li>
                                        <li><strong>Tanggal Permohonan:</strong><br> ${res.data.tgl_permohonan}</li>
                                        <li><strong>Update Terakhir:</strong><br> ${res.data.update_time}</li>
                                        <li><strong>${durasiLabel}:</strong><br> ${res.data.durasi}</li>
                                        <li><strong>Status:</strong><br> <span class="badge ${statusInfo.badgeClass}">${statusInfo.label}</span></li>
                                        ${res.data.status == 4 ? `<li><strong>Alasan Penolakan:</strong> ${res.data.alasan_penolakan}</li>` : ''}
                                        ${berkasInfo}
                                        ${fisik}
                                    </ul>
                                    <button class="btn btn-info waves-effect mt-3 mb-2 text-dark" disabled>No. Tracking: ${$('#no_tracking').val()}</button>
                                </div>
                            </div>
                        </div>
                    `);
                }
                else {
                    $('#hasil_tracking').html(`<div class="alert alert-warning text-center col-md-6">❗ Data tidak ditemukan.</div>`);
                }
            },
            error: function() {
                $('#loading-spinner').hide(); // Sembunyikan spinner setelah error
                $('#hasil_tracking').html(`<div class="alert alert-danger text-center col-md-6">Terjadi kesalahan. Coba lagi.</div>`);
            }
        });
    });
});


    function getStatusIcon(status) {
       switch(parseInt(status)) {
        case 1:
        return {
            icon: "fe-clock", 
            color: "text-secondary", 
            label: "Sementara proses Desa", 
            badgeClass: "badge-secondary"
        };
        case 2:
        return {
            icon: "fe-refresh-cw", 
            color: "text-info", 
            label: "Sedang Diproses Dukcapil", 
            badgeClass: "badge-info"
        };
        case 3:
        return {
            icon: "fe-check-circle", 
            color: "text-success", 
            label: "Disetujui", 
            badgeClass: "badge-success"
        };
        case 4:
        return {
            icon: "fe-x-circle", 
            color: "text-danger", 
            label: "Ditolak", 
            badgeClass: "badge-danger"
        };
        default:
        return {
            icon: "fe-help-circle", 
            color: "text-muted", 
            label: "Tidak Diketahui", 
            badgeClass: "badge-light"
        };
    }
}
</script>
