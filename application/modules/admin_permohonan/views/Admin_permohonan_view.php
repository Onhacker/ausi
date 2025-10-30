<link href="<?php echo base_url('assets/admin/datatables/css/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">

<style>
  .badge { font-size: 0.75rem; }
  .card-elev { border-radius: .5rem; box-shadow: 0 4px 18px rgba(0,0,0,.06); }

  /* Select2 rapi sejajar form-control BS4 */
  .select2-container { width: 100% !important; }
  .select2-container .select2-selection--single { height: 38px; padding: 6px 8px; }
  .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 24px; }
  .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; right: 6px; }

  /* Checkbox kolom */
  th.select-col, td.select-col { width: 36px; text-align: center; vertical-align: middle; }
  #sel-counter { min-height: 1.25rem; }
</style>

<div class="container-fluid">
  <div class="row mt-3">
    <div class="col-12">
      <div class="card card-elev">
        <div class="card-body">
          <h4 class="header-title mb-2"><?php echo $title; ?></h4>
          <p class="text-muted mb-3"><?php echo $deskripsi; ?></p>

          <form id="form-filter" onsubmit="return false;" autocomplete="off">
            <div class="form-row">
              <div class="col-md-2 mb-2">
                <label class="small mb-1" for="tanggal_mulai">Tanggal Mulai</label>
                <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="form-control">
              </div>
              <div class="col-md-2 mb-2">
                <label class="small mb-1" for="tanggal_selesai">Tanggal Selesai</label>
                <input type="date" id="tanggal_selesai" name="tanggal_selesai" class="form-control">
              </div>
              <div class="col-md-2 mb-2">
                <label class="small mb-1" for="unit_tujuan">Unit Tujuan</label>
                <?php echo form_dropdown('unit_tujuan', $arr_units, '', 'id="unit_tujuan" class="form-control select2"'); ?>
              </div>
              <div class="col-md-4 mb-2">
                <label class="small mb-1" for="form_asal">Form Asal</label>
                <input type="text" id="form_asal" name="form_asal" class="form-control" placeholder="Nama tamu / Instansi">
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label class="small mb-1" for="filter_status">Status</label>
                  <select id="filter_status" name="status" class="form-control">
                    <option value="">== Semua Status ==</option>
                    <option value="approved">Approved</option>
                    <option value="checked_in">Checked-in</option>
                    <option value="checked_out">Checked-out</option>
                    <option value="expired">Expired</option>
                    <option value="rejected">Rejected</option> 
                  </select>
                </div>
              </div>
              </form>
              <!-- Toolbar di atas tabel -->
              <div class="col-12">
                <div class="d-flex flex-wrap justify-content-between align-items-center mt-2 mb-2">

                  <!-- Kiri: Detail + Hapus + counter -->
                  <div class="d-flex align-items-center mb-2 mb-md-0">
                    <div class="btn-group btn-group-sm mr-2" role="group" aria-label="Aksi terpilih">
                      <button type="button" id="btn-detail" class="mr-1 btn btn-primary" disabled>
                        <i class="fa fa-eye"></i> Detail
                      </button>
                      <?php if ($this->session->userdata('admin_username') === 'admin'): ?>
                        <button type="button" id="btn-hapus" class="mr-1 btn btn-danger" disabled>
                          <i class="fa fa-trash"></i> Hapus
                        </button>
                      <?php endif; ?>

                    </div>
                    <span class="badge badge-blue small" id="sel-counter" aria-live="polite"></span>
                  </div>

                  <!-- Kanan: Tampilkan + Reset + Cetak -->
                  <div class="btn-group btn-group-sm">
                    <button type="button" id="btn-filter" class="mr-1 btn btn-primary">
                      <i class="fa fa-search"></i> Tampilkan
                    </button>
                    <button type="button" id="btn-reset" class="mr-1 btn btn-warning">
                      <i class="fa fa-undo"></i> Reset
                    </button>
                    <button type="button" id="btn-cetak" class="mr-1 btn btn-danger">
                      <i class="fa fa-file-pdf"></i> Cetak PDF
                    </button>
                    <button type="button" id="btn-excel" class="mr-1 btn btn-success">
                      <i class="fa fa-file-excel"></i> Export Excel
                    </button>
                  </div>

                </div>
              </div>


          <div class="table-responsive mt-1">
            
            <table id="tbl_booking" class="table table-striped table-bordered table-sm w-100 align-middle">
              <thead>
                <tr>
                  <th width="5%" class="select-col">
                    <div class="checkbox checkbox-danger checkbox-single">
                      <input id="check-all" type="checkbox"><label></label>
                    </div>
                  </th>

                <!--   <th class="select-col">
                    <input type="checkbox" id="check-all" aria-label="Pilih semua">
                  </th> -->
                  <th width="4%" class="text-center">No</th>
                  <th>Kode Booking</th>
                  <th>Tanggal/Jam</th>
                  <th>Nama Tamu / Jabatan</th>
                  <th>Asal</th>
                  <th>Unit Tujuan</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<script>
var tbl;

function reloadTable(){
  tbl.ajax.reload(null,false);
}

function getSelectedCodes(){
  const codes = [];
  $('#tbl_booking tbody input.row-check:checked').each(function(){
    codes.push(this.value);
  });
  return codes;
}

function updateToolbarState(){
  const sel = getSelectedCodes();
  const n   = sel.length;
  const btnDetail = document.getElementById('btn-detail');
  const btnHapus  = document.getElementById('btn-hapus');

  if (btnDetail) btnDetail.disabled = !(n === 1);
  if (btnHapus)  btnHapus.disabled  = !(n >= 1);

  const lab = n > 0 ? (n + ' item dipilih') : '';
  document.getElementById('sel-counter').textContent = lab;
}

$(function(){
  // Inisialisasi Select2 jika dipakai
  if ($.fn.select2) {
    $('#unit_tujuan').select2();
  }

  tbl = $('#tbl_booking').DataTable({
    processing: true,
    serverSide: true,
    ordering: true,
  order: [[3, 'desc'], [2, 'desc']], // default: Tanggal (desc), lalu Kode (desc)
  ajax: {
    url: "<?php echo site_url('admin_permohonan/get_data'); ?>",
    type: "POST",
    data: function(d){
      d.tanggal_mulai   = $('#tanggal_mulai').val();
      d.tanggal_selesai = $('#tanggal_selesai').val();
      d.unit_tujuan     = $('#unit_tujuan').val();
      d.form_asal       = $('#form_asal').val();
      d.status          = $('#filter_status').val();
    }
  },
  columns: [
    { data: 'cek', orderable:false, searchable:false },              // 0: checkbox → non-DB
    { data: 'no',  orderable:false, searchable:false },              // 1: nomor urut → non-DB
    { data: 'kode',    name:'bt.kode_booking', className:'text-center' }, // 2: kode_booking
    { data: 'tgljam',  name:'bt.tanggal',      className:'text-center' }, // 3: tanggal (server tambah jam)
    { data: 'tamu',    name:'bt.nama_tamu' },                            // 4: nama_tamu
    { data: 'asal',    name:'asal' },                                    // 5: alias COALESCE(...) AS asal
    { data: 'instansi',name:'unit_tujuan_nama' },                        // 6: alias u.nama_unit AS unit_tujuan_nama
    { data: 'status',  name:'bt.status', className:'text-center', searchable:false } // 7: status
    ],
    columnDefs: [
    { targets: [0,1], orderable: false } // hanya checkbox & no yang non-orderable
    ],
    drawCallback: function(){
      $('#check-all').prop('checked', false);
      updateToolbarState();
    }
  });


  // Filter
  $('#btn-filter').on('click', function(){ reloadTable(); });

  // Reset
  $('#btn-reset').on('click', function(){
    $('#form-filter')[0].reset();
    $('#unit_tujuan').val('').trigger('change');
    $('#filter_status').val('');
    reloadTable();
  });

  // Cetak PDF (pakai filter yang sama)
  $('#btn-cetak').on('click', function(){
    const qs = new URLSearchParams({
      tanggal_mulai:   $('#tanggal_mulai').val() || '',
      tanggal_selesai: $('#tanggal_selesai').val() || '',
      unit_tujuan:     $('#unit_tujuan').val() || '',
      form_asal:       $('#form_asal').val() || '',
      status:          $('#filter_status').val() || ''
    }).toString();

    window.open('<?php echo site_url('admin_permohonan/cetak_pdf'); ?>?'+qs, '_blank');
  });

  // Export Excel (pakai filter yang sama)
  $('#btn-excel').on('click', function(){
    const qs = new URLSearchParams({
      tanggal_mulai:   $('#tanggal_mulai').val() || '',
      tanggal_selesai: $('#tanggal_selesai').val() || '',
      unit_tujuan:     $('#unit_tujuan').val() || '',
      form_asal:       $('#form_asal').val() || '',
      status:          $('#filter_status').val() || ''
    }).toString();

    window.open('<?= site_url('admin_permohonan/export_excel'); ?>?'+qs, '_blank');
  });

  // Cegah submit bawaan (Enter) agar tidak reload page
  $('#form-filter').on('submit', function(e){ e.preventDefault(); });

  // Tekan Enter di input/select = klik "Tampilkan"
  $('#form-filter').on('keydown', 'input,select', function(e){
    if (e.key === 'Enter') {
      e.preventDefault();
      $('#btn-filter').trigger('click');
    }
  });

  // Jika pakai Select2: cegah Enter di kotak search Select2
  $(document).on('keydown', '.select2-search__field', function(e){
    if (e.key === 'Enter') e.preventDefault();
  });

  // Master checkbox
  $('#check-all').on('change', function(){
    const checked = this.checked;
    $('#tbl_booking tbody input.row-check').prop('checked', checked);
    updateToolbarState();
  });

  // Update toolbar saat user pilih/deselect
  $('#tbl_booking').on('change', 'input.row-check', updateToolbarState);

  // Tombol DETAIL (tepat 1 item)
  $('#btn-detail').on('click', function(){
    const sel = getSelectedCodes();
    if (sel.length !== 1) {
      if (window.Swal) Swal.fire('Pilih 1 data', 'Harap pilih tepat 1 data untuk melihat detail.', 'info');
      else alert('Harap pilih tepat 1 data.');
      return;
    }
    const kode = sel[0];
    // window.open('<?php echo site_url('admin_permohonan/detail/'); ?>' + encodeURIComponent(kode), '_blank');
    window.location.href = '<?= site_url('admin_permohonan/detail/'); ?>' + encodeURIComponent(kode);

  });

  // Tombol HAPUS (>= 1 item) — hanya render jika ada tombolnya
  $('#btn-hapus').on('click', function(){
    const sel = getSelectedCodes();
    if (!sel.length) {
      if (window.Swal) Swal.fire('Belum ada pilihan', 'Pilih minimal 1 data untuk dihapus.', 'info');
      else alert('Pilih minimal 1 data.');
      return;
    }

    const doDelete = ()=>{
      const $btn = $('#btn-hapus').prop('disabled', true);
      $.ajax({
        url: "<?php echo site_url('admin_permohonan/hapus_batch'); ?>",
        method: "POST",
        dataType: "json",
        data: { kode: sel }, // backend terima array kode[]
        success: function(resp){
          if (resp && resp.success) {
            if (window.Swal) Swal.fire('Berhasil', resp.message || 'Data terhapus.', 'success');
            else alert('Data terhapus.');
            reloadTable();
          } else {
            if (window.Swal) Swal.fire('Gagal', (resp && resp.message) || 'Gagal menghapus data.', 'error');
            else alert('Gagal menghapus data.');
          }
        },
        error: function(){
          if (window.Swal) Swal.fire('Error', 'Terjadi kendala jaringan/server.', 'error');
          else alert('Terjadi kendala jaringan/server.');
        },
        complete: function(){
          $btn.prop('disabled', false);
        }
      });
    };

    if (window.Swal) {
      Swal.fire({
        title: 'Hapus data terpilih?',
        text: sel.length + ' item akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
      }).then(r => { if (r.isConfirmed) doDelete(); });
    } else {
      if (confirm('Hapus '+sel.length+' item?')) doDelete();
    }
  });

});
</script>

<script>
/* copy kode tetap */
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.btn-copy-kode');
  if (!btn) return;

  const text = btn.getAttribute('data-kode') || '';
  if (!text) return;

  const ok = () => {
    if (window.Swal) {
      Swal.fire({toast:true, position:'center', icon:'success', title:'Kode disalin', timer:1500, showConfirmButton:false});
    }
  };

  function fallback(){
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.setAttribute('readonly','');
    ta.style.position = 'fixed';
    ta.style.left = '-9999px';
    document.body.appendChild(ta);
    ta.select();
    try { document.execCommand('copy'); } catch(e){}
    document.body.removeChild(ta);
    ok();
  }

  if (navigator.clipboard && window.isSecureContext) {
    navigator.clipboard.writeText(text).then(ok).catch(fallback);
  } else {
    fallback();
  }
});
</script>
