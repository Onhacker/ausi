<script type="text/javascript">

  $(document).ready(function(){
    $('#tanggal').datepicker({
      format: 'dd-mm-yyyy'
    });
    $('#tgl_posting').datepicker({
      format: 'dd-mm-yyyy'
    });

    $('#summernote').summernote({
      toolbar: [
      ['style', ['style']],
      ['font', ['bold','italic', 'underline', 'clear','strikethrough', 'superscript', 'subscript']],
      ['fontname', ['fontname']],
      ['fontsize', ['fontsize']],
      ['color', ['color']],
      ['para', ['ul', 'ol', 'paragraph','height']],
      ['table', ['table']],
      ['insert', ['link', 'picture', 'video','hr']],
      ['view', ['undo','redo','fullscreen', 'codeview', 'help']],
      ],
      dialogsInBody: true,
      lang: 'id-ID',
      
      height: "300px",
      callbacks: {
          onImageUpload: function(image) {
              upload_summernote(image[0]);
          },
          onMediaDelete : function(target) {
              hapus_summernote(target[0].src);
          }
      }
    });


});


  function upload_summernote(image) {
    Swal.fire({
      title: "Uploading...",
      html: "Jangan tutup halaman ini",
      allowOutsideClick: false,
      onBeforeOpen: function() {
        Swal.showLoading()
      },
      onClose: function() {
        clearInterval(t)
      }
    })
    var data = new FormData();
    data.append("image", image);
    $.ajax({
        url: "<?php echo site_url('general/upload_summernote')?>",
        cache: false,
        contentType: false,
        processData: false,
        data: data,
        dataType: "json",
        type: "POST",
        success: function(result) {
          Swal.close();
          if(result.success == false){
            Swal.fire(result.title,result.pesan, "error");
            return false;
          } else {
            $('#summernote').summernote("insertImage", result.img);
          }
          
        },
        error: function (jqXHR, textStatus, errorThrown) {
          alert('Error uploading data');
        }
    });
  }

  function hapus_summernote(src) {
    Swal.fire({
      title: "Yakin ingin menghapus ?",
      text: "Anda tidak dapat mengembalikan data terhapus",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#3085d6",
      confirmButtonText: "Ya Hapus",
      cancelButtonText: "Batal",
      allowOutsideClick: false,
      }).then((result) => {
        if (result.value) {
            $.ajax({
                type: "POST",
                url: "<?php echo site_url('general/delete_summernote')?>",
                data: {src : src},
                cache : false,
                dataType: "json",
                success: function(result) {
                    Swal.close();
                    if(result.success == false){
                        Swal.fire(result.title,result.pesan, "success");
                        return false;
                    } else {
                        Swal.fire(result.title,result.pesan, "success");
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                alert('Error deleting data');
                }
            });
        } else {
          $('#summernote').summernote("insertImage", src);
        }
    })
  }


</script>