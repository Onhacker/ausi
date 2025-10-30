
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <title><?php echo $title ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
        <meta name="robots" content="noindex">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="<?php echo base_url('assets') ?>/images/logo.png">

		<!-- App css -->
		<link href="<?php echo base_url('assets/admin') ?>/css/bootstrap.min.css" rel="stylesheet" type="text/css" id="bs-default-stylesheet" />
		<link href="<?php echo base_url('assets/admin') ?>/css/app.min.css" rel="stylesheet" type="text/css" id="app-default-stylesheet" />

		<!-- icons -->
		<link href="<?php echo base_url('assets/admin') ?>/css/icons.min.css" rel="stylesheet" type="text/css" />
        <style>
        #bg-video {
            position: fixed;
            top: 0;
            left: 0;
            min-width: 100%;
            min-height: 100%;
            z-index: -1;
            object-fit: cover;
            opacity: 0.85;
        }
        .card.bg-pattern {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
        }
        
    </style>
    </head>

    <body class="loading authentication-bg authentication-bg-pattern">
       <video autoplay muted loop id="bg-video">
        <source src="<?php echo base_url('assets/admin/images/bg-login.mp4') ?>" type="video/mp4">
            Your browser does not support HTML5 video.
        </video>
        <div class="account-pages mt-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-5">
                        <div class="card">

                            <div class="card-body">

                                <div class="error-text-box">
                                    <svg viewBox="0 0 400 200">
                                        <!-- Symbol-->
                                        <symbol id="s-text">
                                            <text text-anchor="middle" x="50%" y="50%" dy=".35em">404!</text>
                                        </symbol>
                                        <!-- Duplicate symbols-->
                                        <use class="text" xlink:href="#s-text">sdds</use>
                                        <use class="text" xlink:href="#s-text">sdds</use>
                                        <use class="text" xlink:href="#s-text">sdds</use>
                                        <use class="text" xlink:href="#s-text">sdds</use>
                                        <use class="text" xlink:href="#s-text">sdds</use>
                                    </svg>
                                </div>

                                <div class="text-center">
                                    <h3 class="mt-4"><?php echo $pesan; ?></h3>

                                    <a href="<?php echo site_url(); ?>" class="btn btn-primary mt-3">
                                        Kembali ke Beranda
                                    </a>
                                </div>


                            </div> <!-- end card-body -->
                        </div>
                        <!-- end card -->

                       
                        <!-- end row -->

                    </div> <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end page -->
        <!-- Vendor js -->
        <script src="<?php echo base_url('assets/admin') ?>/js/vendor.min.js"></script>

        <!-- App js -->
        <script src="<?php echo base_url('assets/admin') ?>/js/app.min.js"></script>

    </body>

</html>