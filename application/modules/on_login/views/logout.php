<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title><?php echo $this->fm->web_me()->nama_website." ".$this->fm->web_me()->kabupaten ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Onhacker.net" name="author" />
    <meta name="robots" content="noindex">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="<?php echo base_url('assets/admin') ?>/images/logo.png">
    <!-- App css -->
    <link href="<?php echo base_url('assets/admin') ?>/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/admin') ?>/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/admin') ?>/css/app.min.css" rel="stylesheet" type="text/css" />

</head>

<body class="authentication-bg authentication-bg-pattern">
<video autoplay muted loop id="bg-video">
    <source src="<?php echo base_url("assets/admin/images") ?>/bg-login.mp4" type="video/mp4">
    Your browser does not support HTML5 video.
</video>
    <div class="account-pages mt-5 mb-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card bg-pattern">

                            <div class="card-body p-4">
                                
                                <div class="text-center w-75 m-auto">
                                    <a href="<?php echo site_url("on_login") ?>">
                                      <span><img src="<?php echo base_url('assets/images/').$this->fm->web_me()->gambar ?>" alt="<?php echo base_url('assets/images/').$this->fm->web_me()->gambar ?>" height="80"></span>
                                    </a>
                                </div>

                                <div class="text-center">
                                    <div class="mt-4">
                                        <div class="logout-checkmark">
                                            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 130.2 130.2">
                                                <circle class="path circle" fill="none" stroke="#4bd396" stroke-width="6" stroke-miterlimit="10" cx="65.1" cy="65.1" r="62.1"/>
                                                <polyline class="path check" fill="none" stroke="#4bd396" stroke-width="6" stroke-linecap="round" stroke-miterlimit="10" points="100.2,40.2 51.5,88.8 29.8,67.5 "/>
                                            </svg>
                                        </div>
                                    </div>

                                    <h3>Terima Kasih</h3>

                                    <p class="text-muted font-13"> Anda telah keluar. </p>
                                </div>

                            </div> <!-- end card-body -->
                        </div>
                        <!-- end card -->
                    <!-- end card -->

                    <div class="row mt-3">
                        <div class="col-12 text-center">
                            <!-- <p> <a href="<?php echo site_url("on_login") ?>" class="text-black-50 ml-1"><i class="fe-power"></i>  Login Kembali</a></p> -->
                            <p class="text-white-50"><a href="<?php echo site_url("on_login") ?>" class="text-white ml-1 btn btn-info"><i class="fe-globe"></i> Login Kembali</a></p>
                        </div> <!-- end col -->
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
        <?php echo date("Y") ?> &copy;  By <a href="#" class="text-white-50">PT. MVIN</a> 
    </footer>  
   
    <!-- Vendor js -->
    <script src="<?php echo base_url('assets/admin') ?>/js/vendor.min.js"></script>
    <!-- Sweet Alerts js -->
    
    <!-- App js -->
    <script src="<?php echo base_url('assets/admin') ?>/js/app.min.js"></script>
    <!-- jqyery form-->
  
</body>


</html>