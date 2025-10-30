<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title><?php echo "Reset Password ".$rec->nama_website ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Onhacker.net" name="author" />
    <meta name="robots" content="noindex">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="<?php echo base_url('assets/admin') ?>/images/logo.png">
    <!-- Sweet Alert-->
    <link href="<?php echo base_url('assets/admin') ?>/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css" />
    <!-- App css -->
    <link href="<?php echo base_url('assets/admin') ?>/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/admin') ?>/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/admin') ?>/css/app.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url(); ?>assets/admin/libs/magnific-popup/magnific-popup.css" rel="stylesheet" type="text/css" />

</head>

<body class="authentication-bg authentication-bg-pattern">

    <div class="account-pages mt-5 mb-3">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card bg-pattern">
                      <div class="card" style="-webkit-box-shadow : none;">
                        <div class="card-body" id="btn_pass">
                            <div class="text-center w-75 m-auto">
                                <span><img src="<?php echo base_url('assets/images/').$rec->gambar ?>" alt="<?php echo base_url('assets/images/').$rec->gambar ?>" height="40"></span>
                                
                                <h4 class="mb-4 mt-3">RESET PASSWORD</h4>
                            </div>

                            <form id="form_pass" method="post" >

                                <div class="form-group mb-3">
                                    <input class="form-control" type="password" id="password_baru" name="password_baru" placeholder="Masukkan Password Baru">
                                </div>

                                 <div class="form-group mb-3">
                                    <input class="form-control" type="password" id="password_baru_lagi" name="password_baru_lagi"  placeholder="Konfirmasi Password Baru">
                                </div>

                                <div class="form-group mb-0 text-center">
                                    <a href="javascript:vpid(0)" onclick="reset_password()" style="background-color: #00a192; border: none;" class="btn btn-info btn-block">RESET PASSWORD</a>
                                </div>

                            </form>

                        </div> <!-- end card-body -->


                        <div class="card-body" id="btn-login" style="display: none;">
                            <h4 class="mb-4 mt-3">Silahkan login kembali</h4>
                            <div class="form-group mb-0 text-center">
                                <a href="<?php echo site_url("on_login") ?>"  style="background-color: #00a192; border: none;" class="btn btn-info btn-block">Login</a>      
                        </div> <!-- end card-body -->


                    </div>
                    <!-- end row -->

                </div> <!-- end col -->
            </div>
            <!-- end row -->
        </div>
        <!-- end container -->
    </div>
    <!-- end page -->

    <footer class="footer footer-alt">
        <?php echo date("Y") ?> &copy;  <a href="<?php echo $rec->url ?>" class="text-black-50"><?php echo $rec->nama_website ?></a> 
    </footer> 
    <script src="<?php echo base_url("assets/admin") ?>/js/jquery-3.1.1.min.js"></script>

    <!-- Vendor js -->
    <script src="<?php echo base_url('assets/admin') ?>/js/vendor.min.js"></script>
    <!-- Sweet Alerts js -->
    <script src="<?php echo base_url('assets/admin') ?>/libs/sweetalert2/sweetalert2.min.js"></script>

    <!-- App js -->
    <script src="<?php echo base_url('assets/admin') ?>/js/app.min.js"></script>
    <!-- jqyery form-->
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/admin/js/jquery.easyui.min.js"></script>
    <script src="<?php echo base_url('assets/admin') ?>/js/jquery.form.js"></script>
    <script src="<?php echo base_url("assets/admin") ?>/libs/magnific-popup/jquery.magnific-popup.min.js"></script>
    <script src="<?php echo base_url("assets/admin") ?>/js/pages/gallery.init.js"></script>
</body>


</html>
<script type="text/javascript">
    $(document).ready(function(){
        $("#btn-login").hide();
    });


    function reset_password(){
        $('#form_pass').form('submit',{
            url: '<?php echo site_url($this->uri->segment(1)."/new_pass/").$this->uri->segment(3) ?>',
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
                    $("#btn-login").show();
                    $("#btn_pass").hide();
                    $("#password_baru").val("");
                    $("#password_baru_lagi").val("");
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