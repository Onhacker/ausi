<script src="<?php echo base_url(); ?>/assets/admin/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url(); ?>/assets/admin/libs/datatables/dataTables.bootstrap4.js"></script>
<script type="text/javascript">
  $(document).ready(function () {
    const table = $('#datable_1').DataTable({
      order: [],
      processing: true,
      serverSide: true,
      scrollX: true,
      responsive: true,
      ajax: {
        url: "<?php echo site_url('hal/get_data_desa') ?>",
        type: "POST"
      },
      columns: [
      {
        data: null,
        orderable: false,
        className: "text-center d-md-none",
        render: () => '<button class="btn btn-sm btn-primary btn-expand"><i class="fas fa-eye"></i></button>'
      },
      {
        data: null,
        orderable: false,
        className: "text-center d-none d-md-table-cell",
        render: function (data, type, row, meta) {
          return meta.row + meta.settings._iDisplayStart + 1;
        }
      },
      { data: "desa" },
      { data: "kecamatan" },
      { data: "nama_lengkap", className: "d-none d-md-table-cell" },
      { data: "no_telp", className: "text-center d-none d-md-table-cell" },
      {
        data: null,
        orderable: false,
        className: "text-center d-none d-md-table-cell",
        render: function (data) {
          return `
          <button class="btn btn-sm btn-info btn-dusun"
          data-dusun='${JSON.stringify(data.dusun || [])}'>
          <i class="fas fa-address-book"></i> Lihat Dusun
          </button>
          `;
        }
      }
      ],
      rowCallback: function (row, data, iDisplayIndex) {
        const info = table.page.info();
        const index = info.page * info.length + (iDisplayIndex + 1);
        $('td:eq(1)', row).html(index);
      },
      language: {
        sProcessing: "<i class='fas fa-spinner fa-spin text-primary'></i> Memuat...",
        sLengthMenu: "<i class='fas fa-list'></i> _MENU_",
        sZeroRecords: "<i class='fas fa-exclamation-circle text-danger'></i> Tidak ada data",
        sInfo: "<i class='fas fa-info-circle'></i> _START_ - _END_ dari _TOTAL_",
        sInfoEmpty: "<i class='fas fa-info-circle'></i> 0 - 0 dari 0",
        sInfoFiltered: "<i class='fas fa-filter'></i> dari _MAX_",
        sSearch: "<i class='fas fa-search'></i>",
        oPaginate: {
          sFirst: "<i class='fas fa-angle-double-left'></i>",
          sPrevious: "<i class='fas fa-angle-left'></i>",
          sNext: "<i class='fas fa-angle-right'></i>",
          sLast: "<i class='fas fa-angle-double-right'></i>"
        }
      }

    });


    function format(data) {
      const waLink = `https://wa.me/${data.no_telp.replace(/^0/, '62').replace(/[^0-9]/g, '')}?text=Halo%20Operator%20Sidia%20Desa%20${data.desa || '...'}`;

      return `
      <div class="card-box">
      <div class="row align-items-center">
      <div class="col-sm-4">
      <p class="mb-1  mt-sm-0"><i class="mdi mdi-account mr-1"></i>Operator Desa : ${data.nama_lengkap || '-'}</p>
      <p class="mb-0"><i class="mdi mdi-phone-classic mr-1"></i>Whatsapp Desa :
      ${data.no_telp ? `
        <a href="${waLink}" class="text-dark" target="_blank">${data.no_telp}</a>
        </div>` : ''}</p>
        </div>
        <div class="col-sm-2">
        <div class="text-center mt-1 mt-sm-0">
        <button class="btn btn-sm btn-info btn-dusun w-100 mt-2" 
        data-dusun='${JSON.stringify(data.dusun || [])}'>
        <i class="fas fa-address-book"></i> Kontak Dusun
        </button>
        </div>
        </div>
        </div>
        `;
      }

      $('#datable_1 tbody').on('click', '.btn-expand', function () {
        const tr = $(this).closest('tr');
        const row = table.row(tr);
        const icon = $(this).find('i');

        if (row.child.isShown()) {
          row.child.hide();
          tr.removeClass('shown');
          icon.removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
          row.child(format(row.data())).show();
          tr.addClass('shown');
          icon.removeClass('fa-eye').addClass('fa-eye-slash');
        }
      });

      $('#datable_1 tbody').on('click', '.btn-dusun', function () {
        const dusunList = $(this).data('dusun');

        if (!Array.isArray(dusunList) || dusunList.length === 0) {
          $('#isiModalDusun').html('<div class="text-center">Tidak ada data dusun</div>');
        } else {
          let isi = dusunList.map(d => {
            const formattedNoHp = d.no_hp.replace(/[^0-9]/g, '');
            const waLink = `https://wa.me/${formattedNoHp.startsWith('0') ? '62' + formattedNoHp.slice(1) : '62' + formattedNoHp}?text=Halo%20Bapak/Ibu%20${d.nama_petugas_dusun}%20Dusun%20${d.nama_dusun}.%20%0ASaya%20ingin%20komunikasi%20tentang%20Permohonan%20Dokumen%20Kependudukan`;

            return `
            <div class="border-bottom mb-3">
            <div class="d-flex justify-content-between">
            <h4 class="mb-1 text-dark">Dusun : ${d.nama_dusun || '-'}</h4>
            ${d.no_hp ? `
              <button class="btn btn-xs btn-success" onclick="window.open('${waLink}', '_blank')">
              <i class="fab fa-whatsapp"></i> Hubungi Petugas
              </button>
              ` : ''}
              </div>
              <div class="text-dark">
              <strong>Petugas Dusun:</strong> ${d.nama_petugas_dusun || '-'}<br>
              <strong>No. HP:</strong> ${d.no_hp ? `<a href="${waLink}" target="_blank" class="text-success">${d.no_hp}</a>` : '-'}
              </div>
              </div>
              `;

            }).join('');
          $('#isiModalDusun').html(isi);
        }

        $('#modalDusun').modal('show');
      });


    });


  </script>
