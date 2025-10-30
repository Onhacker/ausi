
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title><?php echo $title ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Error" name="Error" />
    <meta content="Silaturahmi" name="PT.MVIN" />
    <meta name="robots" content="noindex">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="shortcut icon" href="<?php echo base_url('assets') ?>/images/logo.png">
    <link href="<?php echo base_url('assets/admin') ?>/css/bootstrap.min.css" rel="stylesheet" type="text/css" id="bs-default-stylesheet" />
    <link href="<?php echo base_url('assets/admin') ?>/css/app.min.css" rel="stylesheet" type="text/css" id="app-default-stylesheet" />
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
            pointer-events: none;
        }
        .card.bg-pattern {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
        }
        
    </style>
</head>

<body class="loading authentication-bg authentication-bg-pattern">
   <video autoplay muted playsinline loop id="bg-video">
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

                                    <symbol id="s-text">
                                        <text text-anchor="middle" x="50%" y="50%" dy=".35em">404!</text>
                                    </symbol>

                                    <use class="text" xlink:href="#s-text">sdds</use>
                                    <use class="text" xlink:href="#s-text">sdds</use>
                                    <use class="text" xlink:href="#s-text">sdds</use>
                                    <use class="text" xlink:href="#s-text">sdds</use>
                                    <use class="text" xlink:href="#s-text">sdds</use>
                                </svg>
                            </div>

                            <div class="text-center">
                                <h3 class="mt-4">Page not found </h3>
                                <p class="text-muted mb-0"><?php echo $title; ?></p>
                            </div>

                        </div> 
                    </div>
                </div> 
            </div>
        </div>
    </div>
   
</body>
</html>