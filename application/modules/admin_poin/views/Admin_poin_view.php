<link href="<?= base_url('assets/admin/datatables/css/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet" type="text/css"/>

<style>
  .filter-bar .form-group{
    margin-right: .5rem;
    margin-bottom: .5rem;
  }
  .filter-bar .form-control{
    min-width: 120px;
  }
</style>

<div class="container-fluid">
  <div class="row"></div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <h4 class="header-title mb-1"><?= $title; ?></h4>
          <p class="text-dark mb-1">
            Rekap <b>poin loyalty café</b> berdasarkan nomor HP pelanggan.
            Urutan default: <b>Poin tertinggi</b>.
          </p>
          <!-- Tulisan periode minggu terakhir (di-update via AJAX) -->
          <p id="periode_label" class="text-dark small mb-3">
            Periode: memuat minggu terakhir berdasarkan data (expired_at).
          </p>

          <!-- FILTER BAR (Tahun & Bulan saja) -->
          <div class="d-flex flex-wrap align-items-end justify-content-between mb-2">
            <div class="filter-bar d-flex flex-wrap align-items-end">
              <?php
                $defaultYear  = isset($defaultYear)  ? (int)$defaultYear  : (int)date('Y');
                $defaultMonth = isset($defaultMonth) ? (int)$defaultMonth : (int)date('n');

                $bulanNama = [
                  1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
                  5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
                  9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
                ];
              ?>

              <div class="form-group">
                <label for="filter_tahun" class="mb-0 small">Tahun</label>
                <select id="filter_tahun" class="form-control form-control-sm">
                  <option value="">Semua Tahun</option>
                  <?php if (!empty($years)): ?>
                    <?php foreach($years as $y): ?>
                      <option value="<?= $y; ?>" <?= ($defaultYear === (int)$y ? 'selected' : ''); ?>>
                        <?= $y; ?>
                      </option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>

              <div class="form-group">
                <label for="filter_bulan" class="mb-0 small">Bulan</label>
                <select id="filter_bulan" class="form-control form-control-sm">
                  <option value="">Semua Bulan</option>
                  <?php foreach($bulanNama as $num=>$label): ?>
                    <option value="<?= $num; ?>" <?= ($defaultMonth === $num ? 'selected' : ''); ?>>
                      <?= $label; ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="mb-2">
              <button type="button" onclick="refreshPoin()" class="btn btn-info btn-rounded btn-sm waves-effect waves-light">
                <span class="btn-label"><i class="fe-refresh-ccw"></i></span>Refresh
              </button>
            </div>
          </div>

          <!-- TABLE -->
          <div class="table-responsive">
            <table id="tabel_poin" class="table table-sm table-striped table-bordered w-100">
              <thead>
                <tr>
                  <th width="5%">No.</th>
                  <th>Nama Pelanggan</th>
                  <th>No. HP</th>
                  <th>Poin</th>
                  <th>Jumlah Transaksi</th>
                  <th>Total Belanja (Rp)</th>
                  <th>Minggu ke-</th>
                  <th>Expired</th>
                  <th width="12%">Aksi</th>
                </tr>
              </thead>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>

  <?php $this->load->view("backend/global_css"); ?>
</div>

<script type="text/javascript">
let poinTable;

/* ==== SweetAlert helpers ==== */
function swalToast(icon='success', title='Berhasil', text='') {
  return Swal.fire({
    icon,
    title,
    html: text,
    toast:true,
    position:'center',
    showConfirmButton:false,
    timer:2500,
    timerProgressBar:true
  });
}

function swalErr(msg, title='Gagal'){
  return Swal.fire({icon:'error', title, html: msg});
}

function refreshPoin(){
  if (poinTable) {
    poinTable.ajax.reload(null, false);
  }
}

$(document).ready(function(){
  poinTable = $('#tabel_poin').DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    ajax: {
      url: "<?= site_url(strtolower($controller).'/get_data'); ?>",
      type: "POST",
      data: function(d){
        d.tahun  = $('#filter_tahun').val()  || '';
        d.bulan  = $('#filter_bulan').val()  || '';
        // minggu tidak dikirim lagi, ditentukan otomatis di server (minggu terakhir)
      }
    },
    columns: [
      { data: 'no', orderable:false, searchable:false },
      { data: 'nama' },
      { data: 'no_hp' },
      { data: 'points',       className: 'text-center' },
      { data: 'transaksi',    className: 'text-center' },
      { data: 'total_rupiah', className: 'text-right' },
      { data: 'minggu_ke',    className: 'text-center' },
      { data: 'expired_at',   className: 'text-center' },
      { data: 'aksi', orderable:false, searchable:false, className:'text-center' },
    ],
    order: [[3,'desc']], // default: poin tertinggi
    rowCallback: function(row, data, displayIndex){
      const info = this.api().page.info();
      $('td:eq(0)', row).html(info.start + displayIndex + 1);
    }
  });

  // setiap kali data AJAX selesai di-load, update tulisan periode
  $('#tabel_poin').on('xhr.dt', function(e, settings, json, xhr){
    if(json && typeof json.periode_label !== 'undefined'){
      $('#periode_label').text(json.periode_label);
    }
  });

  // reload tabel saat filter berubah
  $('#filter_tahun, #filter_bulan').on('change', function(){
    refreshPoin();
  });
});

/**
 * Detail 1 pelanggan (SweetAlert)
 */
function show_detail(id){
  if(!id){ return; }

  $.ajax({
    url: "<?= site_url(strtolower($controller).'/detail'); ?>",
    type: "POST",
    dataType: "json",
    data: { id: id },
    success: function(res){
      if(res.success){
        Swal.fire({
          title: res.title || 'Detail Poin Pelanggan',
          html:  res.html  || '-',
          width: 600,
          confirmButtonText: 'Tutup'
        });
      } else {
        swalErr(res.pesan || 'Gagal mendapatkan detail');
      }
    },
    error: function(){
      swalErr('Terjadi kesalahan koneksi');
    }
  });
}
</script>
