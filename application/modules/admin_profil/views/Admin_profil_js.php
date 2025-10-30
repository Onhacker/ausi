<script type="text/javascript">
    $(document).ready(function(){
        load_profil();
        $('.dropify').dropify();
        $('.form-checkbox').click(function(){
            if($(this).is(':checked')){
                $('#password_lama').attr('type','text');
                $('#password_baru').attr('type','text');
                $('#password_baru_lagi').attr('type','text');
            }else{
                $('#password_lama').attr('type','password');
                $('#password_baru').attr('type','password');
                $('#password_baru_lagi').attr('type','password');
            }
        });

    });

    function validasiFile(){
            var inputFile = document.getElementById('foto');
            var pathFile = inputFile.value;
            var ekstensiOk = /(\.jpg|\.jpeg|\.png|\.gif|\.ico)$/i;
            // var ekstensiOk = /(\.on)$/i;
            if(!ekstensiOk.exec(pathFile)){
                Swal.fire({
                    icon: "error",
                    title: "File tidak diterima",
                    text: "Silakan upload file gambar yang memiliki ekstensi .jpg/.jpeg/.png/.gif/.ico",
                    customClass: {
                        confirmButton: 'btn btn-danger mt-2'
                      },
                    // footer: '<a href="">Why do I have this issue?</a>'
                })
                inputFile.value = '';
                return false;
            } else {
                return true;
            }
    }   


    

   function load_profil(){
  $("#loader").show();
  $("#bg-pr").hide();
  $.ajax({
    url : "<?= site_url(strtolower($controller).'/load_profil/') ?>",
    cache:false,
    type : "GET",
    dataType : "json",
    success : function(result){
      $("#loader").hide();
      $("#bg-pr").show();

      // data dasar
      $("#nama").text(result.nama_lengkap || "");
      $("#nama_profil").text(result.nama_lengkap || "");
      $("#lev").text(result.username || "");
      $("#telp").text(result.no_telp || "");
      $("#tanggal_reg").text(result.tanggal_reg || "");

      // === Unit (ganti desa/kecamatan) ===
      var unitName = (result.unit || result.nama_unit || "").trim();
      var isAdmin  = String(result.level || "").toLowerCase() === "admin";

      // #unit: span tempat menaruh nama unit
      var unitEl   = document.getElementById("unit");
      // #unit_row (opsional): wrapper <p>/<div> untuk baris Unit, kalau ada
      var unitRow  = document.getElementById("unit_row");

      if (isAdmin){
        // sembunyikan baris unit untuk admin
        if (unitRow) unitRow.style.display = "none";
        else if (unitEl && unitEl.parentElement) unitEl.parentElement.style.display = "none";
      } else {
        if (unitEl) unitEl.textContent = unitName || "-";
        if (unitRow) unitRow.style.display = "";
        else if (unitEl && unitEl.parentElement) unitEl.parentElement.style.display = "";
      }

      // foto
      var foto = (result.foto && result.foto.trim() !== "") ? result.foto : "onhacker_221a3f5e.jpg";
      $('#foto_profil').attr('src', '<?= base_url("upload/users/") ?>' + foto);
    }
  });
}

    
    function update(){
        $('#form_app').form('submit',{
            url: '<?php echo site_url($this->uri->segment(1)."/update") ?>',
            onSubmit: function(){
                Swal.fire({
                    title: "Updating...",
                    html: "Jangan tutup halaman ini",
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading()
                    }
                })
                //loader
                return $(this).form('validate');
            },
            dataType:'json',
            success: function(result){
                console.log(result);
                obj = $.parseJSON(result);
                if (obj.success == false ){
                    swal.fire({   
                        title: obj.title,   
                        icon: "error", 
                        html: obj.pesan,
                        allowOutsideClick: false,
                        customClass: {
                        confirmButton: 'btn btn-danger mt-2'
                      }   
                    });
                    return false;
                } else {
                    load_profil();
                    Swal.fire({
                        title: obj.title,  
                        html: obj.pesan,   
                        icon: "success",
                        allowOutsideClick: false,
                        customClass: {
                        confirmButton: 'btn btn-danger mt-2'
                      }
                    })
                }
            }
        });
    }

    function update_pass(){
        $('#form_pass').form('submit',{
            url: '<?php echo site_url($this->uri->segment(1)."/update_pass") ?>',
            onSubmit: function(){
                Swal.fire({
                    title: "Updating...",
                    html: "Jangan tutup halaman ini",
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading()
                    }
                })
                //loader
                return $(this).form('validate');
            },
            dataType:'json',
            success: function(result){
                console.log(result);
                obj = $.parseJSON(result);
                if (obj.success == false ){
                    swal.fire({   
                        title: obj.title,   
                        icon: "error", 
                        html: obj.pesan,
                        allowOutsideClick: false,
                        customClass: {
                        confirmButton: 'btn btn-danger mt-2'
                      }   
                    });
                    return false;
                } else {
                    $("#password_lama").val("");
                    $("#password_baru").val("");
                    $("#password_baru_lagi").val("");
                    Swal.fire({
                        title: obj.title,  
                        html: obj.pesan,   
                        icon: "success",
                        allowOutsideClick: false,
                        customClass: {
                        confirmButton: 'btn btn-danger mt-2'
                      }
                    })
                }
            }
        });
    }

    function home(){
        window.location.href="<?php echo site_url("admin_dashboard") ?>";
    }

    // $(window).bind('beforeunload', function(){
    //   return 'Are you sure you want to leave?';
    // });
    
</script>
