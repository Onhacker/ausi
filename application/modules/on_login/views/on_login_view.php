<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <title>Login <?php echo $rec->nama_website ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover,user-scalable=no">
    <meta name="robots" content="noindex">
    <meta content="Onhacker.net" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="google" content="notranslate">
    <link rel="shortcut icon" href="<?php echo base_url('assets/images/logo.png') ?>">

    <link href="<?php echo base_url('assets/admin/libs/sweetalert2/sweetalert2.min.css') ?>" rel="stylesheet" />
    <link href="<?php echo base_url('assets/admin/css/bootstrap.min.css') ?>" rel="stylesheet" />
    <link href="<?php echo base_url('assets/admin/css/icons.min.css') ?>" rel="stylesheet" />
    <link href="<?php echo base_url('assets/admin/css/app.min.css') ?>" rel="stylesheet" />
    <link href="<?php echo base_url() ?>assets/admin/libs/animate/animate.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url() ?>assets/admin/SliderCaptcha-master/src/disk/slidercaptcha.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/admin/css/aos.min.css') ?>" rel="stylesheet">
    
    <style>
        .slidercaptcha {
            margin: 0 auto;
            height: 286px;
            border-radius: 4px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.125);
        }

        .slidercaptcha .card-body {
            padding: 1rem;
        }

        .slidercaptcha canvas:first-child {
            border-radius: 4px;
            border: 1px solid #e6e8eb;
        }

        .slidercaptcha.card .card-header {
            background-image: none;
            background-color: rgba(0, 0, 0, 0.03);
        }

        .refreshIcon {
            position: absolute;
            top: -20px;
            color: black;
            font-size: 1rem;
        }

        @keyframes fadeZoom {
            0% {
                opacity: 0;
                transform: scale(0.9);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        .captcha-animate {
          animation: fadeZoom 0.4s ease-in-out;
      }


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
    .teks-kiri {
        padding-left: 3rem;
        padding-right: 3rem;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.4);
        color: white;
    }

    .teks-kiri h1 {
        font-size: 3rem;

    }

    .teks-kiri p {
        font-size: 1.2rem;
    }

    html, body {
        /*height: 100%;*/
        margin: 0;
        /*overflow: auto; */
        padding-bottom: 0px;
    }

    .account-pages {
        height: auto; 
        overflow: visible; 
    }

    .container, .row {
        height: auto; 
    }

    .card {
        max-height: auto; 
        overflow-y: auto; 
    }

    .teks-kiri-overlay {
        background: linear-gradient(to right, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0)); 
        padding: 2rem 3rem;
        border-radius: 0.75rem;
    }


    input {
        outline: none;
        border: none;
    }

    input:focus::-webkit-input-placeholder { color:transparent; }
    input:focus:-moz-placeholder { color:transparent; }
    input:focus::-moz-placeholder { color:transparent; }
    input:focus:-ms-input-placeholder { color:transparent; }

    .wrap-input100 {
      width: 100%;
      position: relative;
      border-bottom: 2px solid #28a745;
      margin-bottom: 30px;
  }

  .input100 {
      font-size: 16px;
      color: #fff;
      line-height: 1.2;
      display: block;
      width: 100%;
      height: 45px;
      background: transparent;
      padding: 0 5px 0 38px;
  }

  .focus-input100 {
      position: absolute;
      display: block;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      pointer-events: none;
  }

  .focus-input100::before {
      content: "";
      display: block;
      position: absolute;
      bottom: -2px;
      left: 0;
      width: 0;
      height: 2px;

      -webkit-transition: all 0.4s;
      -o-transition: all 0.4s;
      -moz-transition: all 0.4s;
      transition: all 0.4s;

      background: red;
  }

  .focus-input100::after {
      font-size: 22px;
      content: attr(data-placeholder);
      display: block;
      width: 100%;
      position: absolute;
      top: 6px;
      left: 0px;
      padding-left: 5px;

      -webkit-transition: all 0.4s;
      -o-transition: all 0.4s;
      -moz-transition: all 0.4s;
      transition: all 0.4s;
  }

  .input100:focus {
      padding-left: 5px;
  }

  .input100:focus + .focus-input100::after {
      top: -22px;
      font-size: 18px;
  }

  .input100:focus + .focus-input100::before {
      width: 100%;
  }

  .has-val.input100 + .focus-input100::after {
      top: -22px;
      font-size: 18px;
  }
  #particles-js {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-size: cover;
      background-position: 50% 50%;
  }
</style>
</head>

<body class="authentication-bg authentication-bg-pattern">
    <div id="preloader">
        <div id="status">
           <div class="image-container animated flip infinite">
              <img src="<?php echo base_url('assets/images/').$rec->gambar ?>" width="50px" alt="Foto" style="display: none;" onload="this.style.display='block';" />
          </div>
      </div>
  </div>
  <video autoplay muted playsinline loop id="bg-video">
    <source src="<?php echo base_url('assets/admin/images/bg-login.mp4') ?>" type="video/mp4">
        Your browser does not support HTML5 video.
    </video>

    <div id="particles-js"></div>
  
  <div class="account-pages mt-3 mb-3">
    <div class="container mb-2">
        <div class="row align-items-center" style="min-height: 100%">
          <div class="col-md-6 d-none d-md-flex flex-column justify-content-center" style="min-height:100%;">
            <div class="teks-kiri-overlay text-white" data-aos="fade-in">
                <h1 class="display-4 text-white font-weight-bold mb-4" data-aos="fade-right" data-aos-delay="200"><?php echo $rec->nama_website ?></h1>

                <p class="lead mb-3" data-aos="fade-right" data-aos-delay="500">
                    Selamat Datang
                </p>

                <p class="lead mb-3" data-aos="fade-right" data-aos-delay="800">
                    <strong><?php echo $rec->meta_deskripsi ?></strong>
                </p>

                <p class="lead" data-aos="fade-right" data-aos-delay="1100">
                    Kota <?php echo ucwords(strtolower($rec->kabupaten)) ?>
                </p>
            </div>
        </div>



        <div class="col-md-6 col-lg-4 ">
            <div class="card bg-pattern">
                <div class="card-body">
                    <div class="text-center">
                        <img src="<?php echo base_url('assets/images/' . $rec->gambar) ?>" alt="Logo" height="50">
                        <h3 class="mt-2"><?php echo $rec->nama_website ?> <?php echo (strtoupper($rec->kabupaten)) ?></h3>
                    </div>

                    <form id="frm" method="post" class="mt-3">
                     <div class="wrap-input100 validate-input">
                        <input class="input100" type="text" name="member" id="member" placeholder="Username" autocomplete="off" style="color: black">
                        <span class="focus-input100" data-placeholder="👤"></span>
                    </div>
                    <div class="wrap-input100 validate-input">
                        <input class="input100" type="password" name="kode" id="kode" placeholder="Password" autocomplete="off" style="color: black">
                        <span class="focus-input100" data-placeholder="🔐"></span>
                        <span onclick="togglePassword()" style="position: absolute; top: 50%; right: 15px; transform: translateY(-50%); cursor: pointer;">
                            <span id="eyeIcon">👁️</span>
                        </span>
                    </div>

                    <input type="hidden" name="password2" id="password2" />
                    <div class="form-group mb-2 position-relative">
                        <div class="slidercaptcha card" style="background-color: transparent;">
                            <div class="card-header" style="background-color: white">
                                <span class="text-primary">Captcha</span>
                                <div class="position-relative">
                                    <i class="fas fa-redo refreshIcon position-absolute text-muted" onclick="reloadCaptchaSlider()"></i>
                                </div>
                            </div>
                            <input type="hidden" name="captcha" id="captcha" value="false">
                            <div class="card-body">
                                <div id="sliderCaptchaContainer"></div>
                            </div>
                        </div>
                    </div>


                    <script>
                        var baseUrl = '<?= site_url("assets/images/caca/") ?>';
                    </script>
                    <script src="<?php echo site_url("assets/admin/SliderCaptcha-master/src/disk/longbow.slidercaptcha.js") ?>"></script>

                    <script>
                        let sliderInstance;

                        document.addEventListener("DOMContentLoaded", function () {
                            sliderInstance = sliderCaptcha({
                                id: 'sliderCaptchaContainer',
                                width: 250,
                                height: 155,
                                onSuccess: function () {
                                    document.getElementById('captcha').value = 'true';
                                    fetch('<?= site_url("on_login/verifikasi_captcha") ?>', { method: 'POST' })
                                    .then(res => {
                                        if (res.ok) {
                                            go_login();
                                        } else {
                                            Swal.fire({
                                                title: 'Gagal Verifikasi',
                                                text: 'Captcha tidak valid, coba lagi.',
                                                icon: 'error'
                                            });
                                            reloadCaptchaSlider();
                                        }
                                    });
                                },
                                onFail: function () {
                                    document.getElementById('captcha').value = 'false';

                                }
                            });
                        });
                    </script>


                </form>
                <div class="d-flex justify-content-between mt-2">
                    <a href="<?php echo site_url() ?>">
                        <button class="btn btn-primary btn-xs waves-effect waves-light">
                            <i class="fas fa-home mr-1"></i> Ke Home
                        </button>
                    </a>
                    <a href="#" data-toggle="modal" data-target="#con-close-modal" data-backdrop="static" data-keyboard="false">
                        <button class="btn btn-danger btn-xs waves-effect waves-light">
                            <i class="fas fa-key mr-1"></i> Lupa Password
                        </button>
                    </a>
                </div>

            </div>

        </div>
    </div>
</div>
</div>
</div>

<div id="con-close-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card" style="-webkit-box-shadow: none;">
            <div class="card-body">
                <div class="text-center w-75 m-auto">
                    <img src="<?php echo base_url('assets/images/' . $rec->gambar) ?>" alt="Logo" height="80">
                </div>

                <form id="reset" method="post" class="mt-4">
                    <div class="form-group">
                        <label>No. Whatsapp</label>
                        <input class="form-control" type="text" id="no_telp" name="no_telp" autocomplete="off" placeholder="Masukkan Nomor Whatsapp">
                    </div>

                    <div class="form-group text-center mb-0">
                        <a href="javascript:;" onclick="reset_password()" id="btn-login-reset" class="btn btn-block text-white" style="background-color: #00a192;">Reset Password</a>
                        <div class="spinner-border text-primary m-2" id="btn-loader-reset" role="status" style="display: none;"></div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>


<!-- Scripts -->
<script src="<?php echo base_url('assets/admin/js/jquery-3.1.1.min.js') ?>"></script>
<script src="<?php echo base_url('assets/admin/js/vendor.min.js') ?>"></script>
<script src="<?php echo base_url('assets/admin') ?>/js/sw.min.js"></script>
<script src="<?php echo base_url('assets/admin/js/app.min.js') ?>"></script>
<script src="<?php echo base_url('assets/admin/js/jquery.easyui.min.js') ?>"></script>
<?php $this->load->view("on_login_js") ?>
<script src="<?php echo base_url('assets/admin/js/aos.min.js') ?>"></script>
<script>
  AOS.init({
    once: true
});
</script>
<!-- scripts -->
<script src="<?php echo base_url('assets/partikel/') ?>particles.min.js"></script>
<script src="<?php echo base_url('assets/partikel/') ?>js/app.js"></script>


</body>
</html>
