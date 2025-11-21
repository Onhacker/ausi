<script type="text/javascript">
let voucherTable;

/* ==== SweetAlert helpers (reuse) ==== */
function swalToast(icon='success', title='Berhasil', text='') {
  return Swal.fire({ icon, title, text, toast:true, position:'center', showConfirmButton:false, timer:2500, timerProgressBar:true });
}
function swalErr(msg, title='Gagal'){ return Swal.fire({icon:'error',   title, html: msg}); }

function refreshVoucher(){ voucherTable.ajax.reload(null,false); }

$(document).ready(function(){
  voucherTable = $('#vouchertable').DataTable({
    processing: true, serverSide: true, responsive: true,
    ajax: { url: "<?= site_url(strtolower($controller).'/get_data'); ?>", type: "POST" },
    columns: [
      { data: 'no', orderable:false, searchable:false },
      { data: 'nama' },
      { data: 'no_hp' },
      { data: 'total_main',   className:'text-center' },
      { data: 'belum_claime', className:'text-center' },
      { data: 'free_siap',    className:'text-center' },
      { data: 'progress' },
      { data: 'aksi', orderable:false, searchable:false, className:'text-center' },
    ],
    order: [[2,'asc']], // by No. HP
    rowCallback: function(row, data, displayIndex){
      const info = this.api().page.info();
      $('td:eq(0)', row).html(info.start + displayIndex + 1);
    }
  });
});

function klaim_voucher(hp){
  Swal.fire({
    title: 'Klaim 1x FREE?',
    html: 'Satu paket FREE akan mengurangi jumlah main aktif sesuai pengaturan.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Ya, Klaim',
    cancelButtonText: 'Batal'
  }).then(function(r){
    if(!r.isConfirmed) return;
    $.ajax({
      url: "<?= site_url(strtolower($controller).'/klaim'); ?>",
      type: "POST",
      dataType:"json",
      data:{no_hp: hp},
      success:function(res){
        if(res.success){
          swalToast('success','Berhasil',res.pesan || 'Voucher berhasil diklaim');
          refreshVoucher();
        }else{
          swalErr(res.pesan || 'Gagal klaim voucher');
        }
      },
      error:function(){ swalErr('Terjadi kesalahan koneksi'); }
    });
  });
}
</script>

<link href="<?= base_url('assets/admin/datatables/css/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet" type="text/css"/>

<div class="container-fluid">
  <div class="row"></div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <h4 class="header-title"><?= $title; ?></h4>
          <p class="text-muted mb-2">
            Program loyalty kursi pijat:
            <br>Setiap <b><?= (int)$threshold; ?></b> kali main (berdasarkan nomor HP yang sama) mendapatkan <b>1x FREE</b>.
            <br>Gunakan tombol <span class="badge bg-success text-white">Klaim 1x Free</span> saat pelanggan menggunakan hak gratisnya.
          </p>

          <div class="d-flex justify-content-end mb-2">
            <button type="button" onclick="refreshVoucher()" class="btn btn-info btn-rounded btn-sm waves-effect waves-light">
              <span class="btn-label"><i class="fe-refresh-ccw"></i></span>Refresh
            </button>
          </div>

          <div class="table-responsive">
            <table id="vouchertable" class="table table-sm table-striped table-bordered w-100">
              <thead>
                <tr>
                  <th width="5%">No.</th>
                  <th>Nama Terakhir</th>
                  <th>No. HP</th>
                  <th>Total Main</th>
                  <th>Main Aktif (belum klaim)</th>
                  <th>Voucher Siap Klaim</th>
                  <th>Progress</th>
                  <th width="18%">Aksi</th>
                </tr>
              </thead>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>

  <?php
    $this->load->view("backend/global_css");  ?>
</div>
