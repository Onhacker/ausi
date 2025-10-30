<script src="<?php echo base_url('assets/admin') ?>/js/sw.min.js"></script>
<script src="<?php echo base_url('assets/admin') ?>/js/jspdf.umd.min.js"></script>
<script src="<?php echo base_url('assets/admin') ?>/js/jspdf.plugin.autotable.min.js"></script>
<script type="text/javascript">
    function loadSyarat(id_permohonan) {
        
        $('#judul_modal').text("Memuat...");
        $('#modal_syarat .modal-body').html(`
            <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
            
            </div>
            <p class="mt-2 mb-0">Memuat data syarat permohonan...</p>
            </div>
            `);
        $('#modal_syarat').modal('show');

        $.ajax({
            url: "<?= site_url('home/loadSyarat') ?>",
            type: "POST",
            data: { id_permohonan: id_permohonan },
            dataType: "json",
            success: function(response) {
                if (response.status === 'ok') {
                    $('#judul_modal').text("Syarat Permohonan: " + response.judul);
                    let html = '';

                    if (response.syarat && response.syarat.length > 0) {
                        if (response.penjelasan && response.penjelasan.trim() !== "") {
                            html += 
                            `<div style="text-align: justify; padding-right: 1.25rem;" class="alert alert-success alert-dismissible text-dark" role="alert">
                            ${response.penjelasan}
                            </div>`;
                        }
                        html += `
                        <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                        <thead class="table-light">
                        <tr>
                        <th style="width: 5%;">No</th>
                        <th>Nama Syarat</th>
                        <th style="width: 20%;">Download</th>
                        </tr>
                        </thead>
                        <tbody>
                        `;
                        let penjelasan = '';
                        response.syarat.forEach((item, index) => {
                            html += `
                            <tr>
                            <td class="text-center">${index + 1}</td>
                            ${item.download ? `
                                <td>
                                ${item.peringatan == 1 ? '<i class="fas fa-exclamation-circle text-warning" title="Wajib!"></i> ' : ''} 
                                <strong>${item.nama}.</strong> <small class="text-dark d-block mt-1">${item.penjelasan || ''}</small>

                                </td>
                                <td>
                                <a href="${item.download}" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-download"></i> Unduh
                                </a>
                                </td>
                                ` : `
                                <td colspan="2">
                                ${item.peringatan == 1 ? '<i class="fas fa-exclamation-circle text-warning" title="Wajib!"></i> ' : ''} 
                                <strong>${item.nama}.</strong> <small class="text-dark d-block mt-1">${item.penjelasan || ''}</small>

                                </td>
                                `}
                                </tr>
                                `;
                            });

                        html += `
                        </tbody>
                        </table>
                        `;
                        html += `
                        <div class="text-dark">
                        <small><i class="fas fa-exclamation-circle text-warning"></i> Syarat yang memiliki ikon ini adalah <strong>Wajib</strong> untuk dipenuhi.</small>
                        </div>
                        `;
                    } else {
                        html = '<p class="text-muted">Belum ada syarat untuk permohonan ini.</p>';
                    }

                    $('#modal_syarat .modal-body').html(html);
                }
            },
            error: function() {
                $('#judul_modal').text("Terjadi Kesalahan");
                $('#modal_syarat .modal-body').html('<p class="text-danger">Terjadi kesalahan saat memuat data.</p>');
            }
        });
    }


function exportSyaratPDF() {
    // Menampilkan SweetAlert2 loading
    Swal.fire({
        title: 'Membuat PDF...',
        text: 'Harap tunggu sebentar.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading(); // Menampilkan loading
        }
    });

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    let rows = [];
    let no = 1;

    // Ambil data dari tabel
    $('#modal_syarat tbody tr').each(function () {
        const namaSyarat = $(this).find('td:nth-child(2)').text().trim();
        rows.push([no++, namaSyarat]);
    });

    // Jika tidak ada data, beri pesan dan keluar
    if (rows.length === 0) {
        Swal.close(); // Menutup SweetAlert
        Swal.fire('Oops!', 'Tidak ada data untuk diexport.', 'warning');
        return;
    }

    let rawTitle = $('#judul_modal').text().replace('Syarat Permohonan: ', '').trim();
    let filename = rawTitle.toLowerCase()
        .replace(/[^a-z0-9]+/gi, '_')
        .replace(/^_+|_+$/g, '') + '_syarat_permohonan.pdf';

    let penjelasan = $('.modal-body .alert-success').text().trim();
    doc.setFontSize(14);
    doc.text("Syarat Permohonan: " + rawTitle, 14, 15);

    let tableStartY = 22;

    if (penjelasan !== '') {
        doc.setFontSize(11);
        let splitText = doc.splitTextToSize(penjelasan, 180);
        doc.text(splitText, 14, 25);
        tableStartY = 25 + doc.getTextDimensions(splitText).h + 5;
    }

    doc.autoTable({
        startY: tableStartY,
        head: [['No', 'Nama Syarat']],
        body: rows,
        styles: { fontSize: 10 },
        didDrawPage: function (data) {
            const pageWidth = doc.internal.pageSize.getWidth();
            const pageHeight = doc.internal.pageSize.getHeight();

            doc.setTextColor(200); // abu-abu muda untuk watermark
            doc.setFontSize(40);
            doc.setFont("helvetica", "bold");
            doc.text("SIDIA Morowali Utara", pageWidth / 2, pageHeight / 2, {
                angle: 45,
                align: 'center'
            });

            // Judul halaman pertama
            if (data.pageNumber === 1) {
                doc.setTextColor(0);
                doc.setFontSize(14);
                doc.setFont("helvetica", "normal");
                doc.text("Syarat Permohonan: " + rawTitle, 14, 15);

                if (penjelasan !== '') {
                    doc.setFontSize(11);
                    let splitText = doc.splitTextToSize(penjelasan, 180);
                    doc.text(splitText, 14, 25);
                }
            }
        }
    });

    // Menutup SweetAlert dan menyimpan file PDF setelah proses selesai
    doc.save(filename);
    Swal.close(); // Pastikan SweetAlert ditutup setelah file diunduh
}



</script>