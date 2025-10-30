<script type="text/javascript">
  var table;
  $(document).ready(function(){
    // helper paging info
    $.fn.dataTableExt.oApi.fnPagingInfo = function(oSettings){
      return {
        "iStart": oSettings._iDisplayStart,
        "iEnd": oSettings.fnDisplayEnd(),
        "iLength": oSettings._iDisplayLength,
        "iTotal": oSettings.fnRecordsTotal(),
        "iFilteredTotal": oSettings.fnRecordsDisplay(),
        "iPage": Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength),
        "iTotalPages": Math.ceil(oSettings.fnRecordsDisplay() / oSettings._iDisplayLength)
      };
    };

    var table = $('#datable_1').DataTable({
      lengthMenu: [ [10,25,50,100,-1], [10,25,50,100,"All"] ],
      oLanguage: {
        sProcessing   : "Memuat Data...",
        sSearch       : "<i class='ti-search'></i> Cari :",
        sZeroRecords  : "Maaf, data tidak ditemukan",
        sLengthMenu   : "Tampil _MENU_ data",
        sEmptyTable   : "Data tidak ada",
        sInfo         : "Menampilkan _START_ - _END_ dari _TOTAL_ data",
        sInfoEmpty    : "Tidak ada data ditampilkan",
        sInfoFiltered : "(difilter dari _MAX_ total data)",
        oPaginate     : { sNext:"<i class='fe-chevrons-right'></i>", sPrevious:"<i class='fe-chevrons-left'></i>" }
      },
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url : "<?= site_url(strtolower($controller).'/get_data') ?>",
        type: "POST"
      },
      columns: [
        { data:"cek", orderable:false, searchable:false, className:'text-center align-middle' },
        { data:null, orderable:false, searchable:false, className:'align-middle' }, // No. auto di rowCallback
        { data:"foto", orderable:false, searchable:false, className:'align-middle' },
        { data:"username", className:'align-middle' },
        { data:"nama_lengkap", className:'align-middle' },
        { data:"unit", className:'align-middle' },
        { data:"no_telp", className:'align-middle' }
      ],
      order: [],
      rowCallback: function(row, data, iDisplayIndex){
        var info   = this.fnPagingInfo();
        var page   = info.iPage;
        var length = info.iLength;
        var index  = page * length + (iDisplayIndex + 1);
        $('td:eq(1)', row).html(index);

        // row clickable → detail
        if (data.detail_url){
          $(row).attr('data-href', data.detail_url).css('cursor','pointer');
        }
      }
    });

    // search input bawaan datatable
    $('#datable_1_filter input')
      .off('.DT')
      .on('input.DT', function(){ table.search(this.value).draw(); });

    // klik baris → buka detail (kecuali klik element interaktif)
    $('#datable_1 tbody').on('click','tr', function(e){
      if ($(e.target).is('input, label, a, img, button, .data-check')) return;
      var href = $(this).data('href');
      if (href) window.location.href = href;
    });

    // check all
    $(document).on('change', '#check-all', function(){
      var checked = this.checked;
      $('#datable_1 .data-check').prop('checked', checked);
    });

    // saat redraw, “check-all” kembali off
    table.on('draw', function(){
      $('#check-all').prop('checked', false);
    });

    function reload_table() {
      table.ajax.reload(null,false); 
    }

    $("#check-all").click(function () {
      $(".data-check").prop('checked', $(this).prop('checked'));
    });

    function refresh(){ reload_table(); }

    function loader() {
      Swal.fire({
        title: "Prosess...",
        html: "Jangan tutup halaman ini",
        allowOutsideClick: false,
        didOpen: function() { Swal.showLoading(); }
      });
    }

    function pub(id){
      loader();
      $.ajax({
        type: "POST",
        url : "<?= site_url(strtolower($controller).'/pub') ?>/" + id,
        cache : false,
        dataType: "json",
        success: function(result) {
          Swal.close();
          reload_table();
        }
      });
    }

    // Global flag add/update
    var save_method = 'add';
    var CTRL = "<?= strtolower($controller ?? 'admin_users') ?>";

    function safeLoader(on = true){
      try { if (typeof loader === 'function' && on) loader(); } catch(e){}
    }

    function add(){
      var f = $('#form_app')[0];
      if (f) f.reset();
      save_method = 'add';
      $("#add_member").show();
      $('.mymodal-title').text('Tambah Pengguna');
      // $('#password_baru, #password_baru_lagi').prop('disabled', false).val('');
      $('#full-width-modal').modal('show');
    }

    function parseJSONSafe(res){
      if (res == null) return null;
      if (typeof res === 'object') return res;
      try { return JSON.parse(res); } catch(e){ return null; }
    }

    function validateClient(){
      var username   = ($.trim($('#username').val() || ''));
      var nama       = ($.trim($('#nama_lengkap').val() || ''));
      var id_unit    = ($.trim($('#id_unit').val() || ''));
      var no_telp    = ($.trim($('#no_telp').val() || ''));
      // var pass1      = $('#password_baru').val() || '';
      // var pass2      = $('#password_baru_lagi').val() || '';

      if (!nama)     return "Nama Lengkap harus diisi.";
      if (!id_unit)  return "Unit harus dipilih.";
      if (!username) return "Username harus diisi.";
      if (username.length < 6 || username.length > 12) return "Username harus 6–12 karakter.";
      if (!/^[a-zA-Z0-9_-]+$/.test(username)) return "Username hanya boleh huruf/angka/underscore/strip.";

      // if (save_method === 'add'){
      //   if (!pass1 || !pass2) return "Password & Konfirmasi Password harus diisi.";
      //   if (pass1.length < 8 || pass2.length < 8) return "Password minimal 8 karakter.";
      //   if (pass1 !== pass2) return "Password dan Konfirmasi Password tidak sama.";
      // }
      if (no_telp && !/^[0-9+]+$/.test(no_telp)) return "Nomor WhatsApp hanya boleh angka dan tanda +.";
      return null;
    }

    function simpan(){
      var url = (save_method === 'add')
        ? "<?= site_url(strtolower($controller ?? 'admin_users').'/add') ?>/"
        : "<?= site_url(strtolower($controller ?? 'admin_users').'/update') ?>/";

      var err = validateClient();
      if (err){
        Swal.fire({ title:"Validasi", icon:"error", html:err, allowOutsideClick:false });
        return;
      }

      $('#form_app').form('submit',{
        url: url,
        onSubmit: function(){
          safeLoader(true);
          var ok = $(this).form('validate');
          if (!ok) Swal.close();
          return ok;
        },
        success: function(result){
          Swal.close();
          var obj = parseJSONSafe(result);
          if (!obj){
            Swal.fire({ title:"Gagal", icon:"error", html:"Respon tidak valid dari server.", allowOutsideClick:false });
            return;
          }
          if (obj.success === false){
            Swal.fire({ title: obj.title || "Gagal", icon:"error", html: obj.pesan || "Terjadi kesalahan.", allowOutsideClick:false });
            return;
          }
          Swal.fire({
            title: obj.title || "Berhasil",
            html:  obj.pesan || "Data berhasil disimpan.",
            icon:  "success",
            allowOutsideClick: false,
            showCancelButton: false,
            confirmButtonColor: "#d33",
            confirmButtonText: "Kelola Hak Akses"
          }).then((resSwal)=>{
            if (resSwal.value) {
              window.location.href = "<?= site_url(strtolower($controller ?? 'admin_users').'/detail_profil/') ?>" + (obj.id || '');
            }
          });
          $("#full-width-modal").modal("hide");
          if (typeof reload_table === 'function') reload_table();
        },
        onLoadError: function(){
          Swal.close();
          Swal.fire({ title:"Gagal", icon:"error", html:"Tidak dapat terhubung ke server.", allowOutsideClick:false });
        }
      });
    }

    function hapus_data() {
      var list_id = [];
      $(".data-check:checked").each(function(){ list_id.push(this.value); });
      if(list_id.length < 1){
        Swal.fire("Info","Pilih Satu Data", "warning");
        return;
      }
      Swal.fire({
        title: "Yakin ingin menghapus "+list_id.length+" data ?",
        text: "Anda tidak dapat mengembalikan data terhapus",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Ya Hapus",
        cancelButtonText: "Batal",
        allowOutsideClick: false,
      }).then((result)=>{
        if (!result.value) return;
        loader();
        $.ajax({
          type: "POST",
          url : "<?= site_url(strtolower($controller).'/hapus_data') ?>/",
          data: { id:list_id },
          cache : false,
          dataType: "json",
          success: function(result) {
            Swal.close();
            reload_table();
            Swal.fire(result.title || (result.success?'Berhasil':'Gagal'),
                     result.pesan || (result.success?'Data dihapus':'Gagal menghapus'),
                     result.success ? "success" : "error");
          }
        });
      });
    }

    function close_modal(){
      Swal.fire({
        title: "Yakin ingin menutup ?",
        text: "Data yang belum tersimpan akan hilang",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Tutup",
        cancelButtonText: "Batal",
        allowOutsideClick: false,
      }).then((result)=>{ if (result.value) $("#full-width-modal").modal("hide"); });
    }

    function det(id){
      document.location.href = "<?= site_url(strtolower($controller).'/detail_profil/') ?>" + id;
    }

    // Tampilkan/sembunyikan password
    function toggle(id, btnId){
      const inp = document.getElementById(id);
      const btn = document.getElementById(btnId);
      if (!inp || !btn) return;
      btn.addEventListener('click', ()=>{
        const type = inp.getAttribute('type') === 'password' ? 'text' : 'password';
        inp.setAttribute('type', type);
        const ic = btn.querySelector('i');
        if (ic){ ic.classList.toggle('mdi-eye'); ic.classList.toggle('mdi-eye-off'); }
      });
    }
    toggle('password_baru','togglePwd1');
    toggle('password_baru_lagi','togglePwd2');

    // Validasi kecocokan password
    const p1 = document.getElementById('password_baru');
    const p2 = document.getElementById('password_baru_lagi');
    const h  = document.getElementById('pwdHelp');
    function checkMatch(){
      const a = (p1?.value || '').trim(), b = (p2?.value || '').trim();
      if (!h) return;
      if (!a && !b){ h.textContent=''; h.className='form-text'; return; }
      if (a.length && a.length < 8){ h.textContent='Minimal 8 karakter.'; h.className='form-text text-danger'; return; }
      if (a !== b){ h.textContent='Password tidak sama.'; h.className='form-text text-danger'; }
      else { h.textContent='Password cocok.'; h.className='form-text text-success'; }
    }
    p1 && p1.addEventListener('input', checkMatch);
    p2 && p2.addEventListener('input', checkMatch);

    // Normalisasi nomor WA → angka saja (boleh + di depan)
    const tel = document.getElementById('no_telp');
    if (tel){
      tel.addEventListener('input', ()=>{
        let v = tel.value.replace(/[^\d+]/g,'');
        v = v.replace(/\+/g,'');
        tel.value = (tel.value[0]==='+' ? '+' : '') + v;
      });
    }
// === Ekspos ke global agar bisa dipanggil dari onclick HTML ===
window.add = add;
window.refresh = refresh;
window.hapus_data = hapus_data;
window.close_modal = close_modal;
window.simpan = simpan;
window.pub = pub;
window.det = det;

  }); // ←←← INI YANG KURANG: tutup $(document).ready
</script>
