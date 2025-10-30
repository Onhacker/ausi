<script type="text/javascript">
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

        table = $('#datable_2').DataTable({
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
                sSearch         :   "<i class='ti-search'></i> Cari Modul :",
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
            ajax: {"url": "<?php echo site_url(strtolower($controller)."/get_data_modul/".$this->uri->segment(3) )?>", "type": "POST"},
            columns: [
                {"data": "id_modul","orderable": false},
                {"data": "nama_modul"}, 
                {"data": "aksi","orderable": false},
              
            ],

            "order": [],
            rowCallback: function(row, data, iDisplayIndex) {
                var info = this.fnPagingInfo();
                var page = info.iPage;
                var length = info.iLength;
                var index = page * length + (iDisplayIndex + 1);
                $('td:eq(0)', row).html(index); // masukkan index untuk menampilkan no urut
            }
        });
    });

    function reload_table() {
        table.ajax.reload(null,false); 
    }

    $("#check-all").click(function () {
        $(".data-check").prop('checked', $(this).prop('checked'));
    });


    function pub(id,ses){
        loader();
        $.ajax({
            type: "POST",
            url : "<?php echo site_url(strtolower($controller).'/pub')?>/" + id + "/" + ses,
            cache : false,
            dataType: "json",
            success: function(result) {
                Swal.close();
                reload_table();
                if(result.success == false){
                    Swal.fire(result.title,result.pesan, "error");
                    return false;
                } else {
                    // Swal.fire(result.title,result.pesan, "success");
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {

            }
        });
    }

    function loader() {
        Swal.fire({
            title: "Prosess...",
            html: "Jangan tutup halaman ini",
            allowOutsideClick: false,
            didOpen: function() {
                Swal.showLoading()
            }
        })
    }

    function reset_password() {
        var id = <?php echo $this->uri->segment(3) ?>;
        Swal.fire({
            text: "Kirim kode reset ke Whatsapp <?php echo $record->no_telp ?>",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Ya reset",
            cancelButtonText: "Batal",
            allowOutsideClick: false,
        }).then((result) => {
            if (result.value) {
                loader();
                $.ajax({
                    type: "POST",
                    url : "<?php echo site_url(strtolower($controller).'/reset_password_user/')?>" + id,
                    cache : false,
                    dataType: "json",
                    success: function(result) {
                        // Swal.close();
                        if(result.success == false){
                            Swal.fire({
                                title: result.title,   
                                html: result.pesan,
                                type: result.type,   
                                customClass: {
                                    confirmButton: 'btn btn-danger mt-2'
                                } ,
                                // footer: '<a href="">Why do I have this issue?</a>'
                            })
                            return false;
                        } else {
                            Swal.fire({
                            title: result.title,   
                            html: result.pesan,
                            type: result.type,   
                            customClass: {
                                    confirmButton: 'btn btn-danger mt-2'
                                } ,
                            // footer: '<a href="">Why do I have this issue?</a>'
                            })
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert("error");
                    }
                });
            } else {
                // alert("fuck");
                // $('#summernote').summernote("insertImage", src);
            }
        })
    }


    function kirim_ulang() {
        var email = $("#mail_ver").text();
        // alert(email);
        Swal.fire({
            title: "Yakin ingin mengirim ulang verifikasi ?",
            text: "Kirim Verifikasi ke email " + email,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Ya Kirim Ulang",
            cancelButtonText: "Batal",
            allowOutsideClick: false,
        }).then((result) => {
            if (result.value) {
                loader();
                $.ajax({
                    type: "POST",
                    url : "<?php echo site_url(strtolower($controller).'/resend')?>/",
                    cache : false,
                    data : {email : email},
                    dataType: "json",
                    success: function(result) {
                        // Swal.close();
                        if(result.success == false){
                            Swal.fire({
                                title: result.title,   
                                html: result.pesan,
                                type: "error",   
                                customClass: {
                                    confirmButton: 'btn btn-danger mt-2'
                                } ,
                                // footer: '<a href="">Why do I have this issue?</a>'
                            })
                            return false;
                        } else {
                            Swal.fire({
                            title: result.title,   
                            html: result.pesan,
                            type: "success",   
                            customClass: {
                                    confirmButton: 'btn btn-danger mt-2'
                                } ,
                            // footer: '<a href="">Why do I have this issue?</a>'
                            })
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert("error");
                    }
                });
            } else {
                // alert("fuck");
                // $('#summernote').summernote("insertImage", src);
            }
        })
    }

    function update_setting_profil(){
        var id = <?php echo $this->uri->segment(3) ?>;
        $('#form_app').form('submit',{
            url: '<?php echo site_url($this->uri->segment(1)."/update_setting_profil/") ?>' + id,
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




</script>