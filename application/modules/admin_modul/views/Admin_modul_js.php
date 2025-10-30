<script type="text/javascript">
    var table;
    $(document).ready(function(){
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

        table = $('#datable_1').DataTable({
            "lengthMenu": [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"] ],
            initComplete: function() {
                var api = this.api();
                $('#datatable-buttons_filter input')
                .off('.DT')
                .on('input.DT', function() {
                    api.search(this.value).draw();
                });
            },
            oLanguage: {
                sProcessing     :   "Memuat Data...",
                sSearch         :   "<i class='ti-search'></i> Cari <?php echo $subtitle ?> :",
                sZeroRecords    :   "Maaf Data Tidak Ditemukan",
                sLengthMenu     :   "Tampil _MENU_ Data",
                sEmptyTable     :   "Data Tidak Ada",
                sInfo           :   "Menampilkan _START_ -  _END_ dari _TOTAL_ Total Data",
                sInfoEmpty      :   "Tidak ada data ditampilkan",
                sInfoFiltered   :   "(Filter dari _MAX_ total Data)",
                "oPaginate"     :   {
                                    "sNext": "<i class='fe-chevrons-right'></i>",
                                    "sPrevious": "<i class='fe-chevrons-left'></i>"
                                    },
            },
            "scrollX": true,
            processing: true,
            serverSide: true,
            ajax: {"url": "<?php echo site_url(strtolower($controller)."/get_data")?>", "type": "POST"},
            columns: [
                {"data": "cek","orderable": false},
                {"data": "id","orderable": false},
                {"data": "nama_modul"}, 
                {"data": "link"},
                {"data": "status"}, 
                // {"data": "aktif"}, 
            ],

            "order": [],
            rowCallback: function(row, data, iDisplayIndex) {
                var info = this.fnPagingInfo();
                var page = info.iPage;
                var length = info.iLength;
                var index = page * length + (iDisplayIndex + 1);
                $('td:eq(1)', row).html(index); // masukkan index untuk menampilkan no urut
            }
        });
    });

    function reload_table() {
        table.ajax.reload(null,false); 
    }

    $("#check-all").click(function () {
        $(".data-check").prop('checked', $(this).prop('checked'));
    });


    function copy_link(element) {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($(element).text()).select();
        document.execCommand("copy");
        $temp.remove();
        $.toast({
            text: "Link tercopy", // Text that is to be shown in the toast
            heading: 'Copy', // Optional heading to be shown on the toast
            icon: 'info', // Type of toast icon
            showHideTransition: 'slide', // fade, slide or plain
            allowToastClose: true, // Boolean value true or false
            hideAfter: 1000, // false to make it sticky or number representing the miliseconds as time after which toast needs to be hidden
            stack: 5, // false if there should be only one toast at a time or a number representing the maximum number of toasts to be shown at a time
            position: 'mid-center', // bottom-left or bottom-right or bottom-center or top-left or top-right or top-center or mid-center or an object representing the left, right, top, bottom values
            textAlign: 'left',  // Text alignment i.e. left, right or center
            loader: true,  // Whether to show loader or not. True by default
            loaderBg: '#da8609',  // Background color of the toast loader
            
        });
    }


    function loader() {
        Swal.fire({
            title: "Prosess...",
            html: "Jangan tutup halaman ini",
            allowOutsideClick: false,
            onBeforeOpen: function() {
                Swal.showLoading()
            },
            onClose: function() {
                clearInterval(t)
            }
        })
    }

    function reset(){
        $('#summernote').summernote('reset');
    }

    function hapus_gambar_thumb() {
        list_id = $("#id_halaman").val();
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
                loader();
                $.ajax({
                    type: "POST",
                    url : "<?php echo site_url(strtolower($controller).'/hapus_gambar')?>/" + list_id,
                    cache : false,
                    dataType: "json",
                    success: function(result) {
                        $('#previewImage').attr('src', '<?php echo base_url("upload/gambar/upload-here.png") ?>');
                        $("#hapus_gambar").hide();
                        $("#text_upload").hide();
                        $("#gambar").val("");
                        Swal.close();
                        if(result.success == false){
                            Swal.fire(result.title,result.pesan, "success");
                            return false;
                        } else {
                            Swal.fire(result.title,result.pesan, "success");
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        $('#previewImage').attr('src', '<?php echo base_url("upload/gambar/upload-here.png") ?>');
                        $("#hapus_gambar").hide();
                        $("#text_upload").hide();
                        $("#gambar").val("");
                        Swal.fire("Berhasil","Gambar Berhasil dihapus", "success");
                    }
                });
            } else {
                // $('#summernote').summernote("insertImage", src);
            }
        })
    }

    function add(){ 
        save_method = 'add';
        $('#static_content').attr("readonly", false);
        $('#static_content').val("");
        $("#small").html("contoh : <?php echo site_url() ?><code>linkseoanda</code>");
        $('#form_app')[0].reset(); 
        reset();
        $('#id_modul').val("");
        $("#balabala").show();
        $('#full-width-modal').modal('show'); 
        $('.mymodal-title').text('Install Modul'); 
    }

    function edit() {
        loader();
        $('#static_content').attr("readonly", false);
        $('#static_content').val("");
        var list_id = [];
        $(".data-check:checked").each(function() {
            list_id.push(this.value);
        });
  
        if(list_id.length == 1) { 
            save_method = 'update';
            $.ajax({
                url : "<?php echo site_url(strtolower($controller).'/edit')?>/" + list_id,
                type: "GET",
                dataType: "JSON",
                success: function(data) {
                    Swal.close();
                    $('#id_modul').val(data.id_modul);
                    $('#nama_modul').val(data.nama_modul);
                    $('#aktif').val(data.aktif);
                    $('#dada').val(data.status);
                    
                    if (data.static_content == "") {
                        $('#static_content').attr("readonly", true);
                        $("#small").text("Link Seo Static. Tidak dapat dirubah");
                    } else {
                        $("#small").html("contoh : <?php echo site_url() ?><code>linkseoanda</code>");
                        $('#static_content').val(data.static_content);
                    }
                    $("#balabala").hide();
                    
                    $('#full-width-modal').modal('show'); 
                    $('.mymodal-title').html('Edit Modul <code>'+ data.nama_modul+'</code>'); 
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    alert('Error get data from ajax');
                }
            });
        } else if (list_id.length >= 2) {
            Swal.fire("Info","Tidak dapat mengedit "+list_id.length+" data sekaligus, Pilih satu data saja", "warning");
        } else {
            Swal.fire("Info","Pilih Satu Data", "warning");
        }
    }

    function simpan(){
        var url;
        if(save_method == 'add') {
            url = "<?php echo site_url(strtolower($controller).'/add')?>";
        } else {
            url = "<?php echo site_url(strtolower($controller).'/update')?>";
        }   

        $('#form_app').form('submit',{
            url: url,
            onSubmit: function(){
                loader();
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
                });

                $("#full-width-modal").modal("hide"); 
                reload_table();
            }   
        }
        });
    }

    function hapus_data() {
        var list_id = [];
        $(".data-check:checked").each(function() {
          list_id.push(this.value);
        });
        if(list_id.length > 0) { 
            Swal.fire({
                title: "Yakin ingin menghapus "+list_id.length+" data ?",
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
                    loader();
                    $.ajax({
                        type: "POST",
                        url : "<?php echo site_url(strtolower($controller).'/hapus_data')?>/",
                        data: {id:list_id},
                        cache : false,
                        dataType: "json",
                        success: function(result) {
                            Swal.close();
                            reload_table();
                            if(result.success == false){
                                Swal.fire(result.title,result.pesan, "error");
                                return false;
                            } else {
                                Swal.fire(result.title,result.pesan, "success");
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            alert("fucks");
                        }
                    });
                } else {
                    // $('#summernote').summernote("insertImage", src);
                }
            })
        } else {
            Swal.fire("Info","Pilih Satu Data", "warning");
        }
    }
    function get_modulx(){
        var list_id = [];
           $(".data-check:checked").each(function() {
            list_id.push(this.value);
        });
           window.location.href="<?php echo site_url(strtolower($controller).'/get_modul')?>/" + list_id;
    }
    function get_modul() {
        var list_id = [];
        $(".data-check:checked").each(function() {
            list_id.push(this.value);
        });
  
        if(list_id.length == 1) { 
            window.location.href="<?php echo site_url(strtolower($controller).'/get_modul')?>/" + list_id;
        } else if (list_id.length >= 2) {
            Swal.fire("Info","Tidak dapat membackup "+list_id.length+" data sekaligus, Pilih satu data saja", "warning");
        } else {
            Swal.fire("Info","Pilih Satu Data", "warning");
        }
    }

      function close_modal(){
        Swal.fire({
            title: "Yakin ingin menutup ?",
            text: "Anda tidak dapat mengembalikan data yang belum tersimpan",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Tutup",
            cancelButtonText: "Batal",
            allowOutsideClick: false,
        }).then((result) => {
            if (result.value) {
                $("#full-width-modal").modal("hide");
            } 
        })
    }
    
</script>
