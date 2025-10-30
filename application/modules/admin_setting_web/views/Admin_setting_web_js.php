<script type="text/javascript">
   
    function modal(){
        $("#md").modal("show");
    }

    function validasiFile(){
            var inputFile = document.getElementById('favicon');
            var pathFile = inputFile.value;
            var ekstensiOk = /(\.jpg|\.jpeg|\.png|\.gif|\.ico)$/i;
            // var ekstensiOk = /(\.on)$/i;
            if(!ekstensiOk.exec(pathFile)){
                Swal.fire({
                    type: "error",
                    title: "File tidak diterima",
                    text: "Silakan upload file gambar yang memiliki ekstensi .jpg/.jpeg/.png/.gif/.ico",
                    confirmButtonClass: "btn btn-confirm mt-2",
                    // footer: '<a href="">Why do I have this issue?</a>'
                })
                inputFile.value = '';
                return false;
            } else {
                return true;
            }
    }   
    
    function update(){
        $('#form_app').form('submit',{
            url: '<?php echo site_url($this->uri->segment(1)."/update") ?>',
            onSubmit: function(){
                Swal.fire({
                    title: "Updating...",
                    html: "Jangan tutup halaman ini",
                    allowOutsideClick: false,
                    onBeforeOpen: function() {
                        Swal.showLoading()
                    },
                    onClose: function() {
                        clearInterval(t)
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
                        type: "error", 
                        html: obj.pesan,
                        allowOutsideClick: false,
                        confirmButtonClass: "btn btn-confirm mt-2"   
                    });
                    return false;
                } else {
                    Swal.fire({
                        title: obj.title,  
                        html: obj.pesan,   
                        type: "success",
                        allowOutsideClick: false,
                        confirmButtonClass: "btn btn-confirm mt-2"
                    })
                }
            }
        });
    }

    function home(){
        window.location.href="<?php echo site_url("admin_dashboard") ?>";
    }
    
</script>
