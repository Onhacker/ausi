<!DOCTYPE html>
<html lang="en"> 
<?php 
$web = $this->om->web_me();
$us = $this->om->user();
if ($this->session->userdata("admin_level") == "admin") {
    $fr = "Admin";
} else {
    $fr = "User";
}
?>
<head>
    <meta charset="utf-8" />
    <title>>_ <?php echo $subtitle." - ".$title." | ".$fr." ".$us->nama_lengkap ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Onhacker CMS" name="description" />
    <meta content="Onhacker" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    
    <link rel="shortcut icon" href="<?php echo base_url('upload/gambar/favicon.ico') ?>">
    
    <link href="<?php echo base_url('assets/admin') ?>/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/admin') ?>/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/admin') ?>/css/app.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/admin') ?>/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css" />
    <?php if ($this->uri->segment(1) == "admin_w_dua" or strtolower($controller) == "admin_pengumuman" or strtolower($controller) == "admin_stp_klb"  or strtolower($controller) == "admin_kalender"   or $this->uri->segment(1) == "admin_modul") { ?>
        <link href="<?php echo base_url(); ?>assets/admin/libs/clockpicker/bootstrap-clockpicker.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url(); ?>assets/admin/libs/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

        <link href="<?php echo base_url(); ?>assets/admin/libs/flatpickr/flatpickr.min.css" rel="stylesheet" type="text/css" />
    <?php } ?>
    <link href="<?php echo base_url(); ?>assets/admin/libs/select2/select2.min.css" rel="stylesheet" type="text/css" />

    <script src="<?php echo base_url("assets/admin") ?>/js/jquery-3.1.1.min.js"></script>
    
    <style type="text/css">
        html {
          scroll-behavior: smooth;
      }

  </style>
</head>
<?php if (strtolower($controller) == "admin_stp" or strtolower($controller) == "admin_w_dua" or strtolower($controller) == "admin_stp_klb") {
    $un = "unsticky-header";
} ?>
<body class="menubar-gradient gradient-topbar <?php echo $un ?>">

 <div id="preloader">
    <div id="status">
        <div class="spinner">Loading...</div>
    </div>
</div>


<header id="topnav">

    <div class="navbar-custom">
        <div class="container-fluid">
            <ul class="list-unstyled topnav-menu float-right mb-0">
                <li class="dropdown notification-list">

                    <a class="navbar-toggle nav-link">
                        <div class="lines">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </a>

                </li>

                   
                        <li class="dropdown notification-list">
                            <a class="nav-link dropdown-toggle nav-user mr-0 waves-effect" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                <?php if (empty($us->foto)) {?>
                                    <img src="<?php echo base_url('upload/users/user-1.jpg') ?>" alt="user-image" class="rounded-circle">
                                <?php } else {?>
                                    <img src="<?php echo base_url('upload/users/'.$us->foto) ?>" alt="user-image" class="rounded-circle" id="foto_profil">
                                <?php } ?>
                                <?php if ($this->session->userdata("admin_level") == "admin") {?>
                                    <span class="pro-user-name ml-1" >
                                        LUTFI JAYA, SKM <i class="mdi mdi-chevron-down"></i> 
                                    </span>
                                <?php } else {?>
                                    <span class="pro-user-name ml-1" id="nama_profil">
                                        <?php echo ($us->nama_lengkap) ?> <i class="mdi mdi-chevron-down"></i> 
                                    </span>
                                <?php } ?>
                                
                            </a>
                            <div class="dropdown-menu dropdown-menu-right profile-dropdown ">

                                <div class="dropdown-header noti-title">
                                    <h6 class="text-overflow m-0">Welcome !</h6>
                                </div>


                                <a href="<?php echo site_url("admin_profil") ?>" class="dropdown-item notify-item">
                                    <i class="fe-user"></i>
                                    <span>Akun</span>
                                </a>





                                <div class="dropdown-divider"></div>


                                <a href="javascript:void(0)" onclick="logout()" class="dropdown-item notify-item">
                                    <i class="fe-log-out"></i>
                                    <span>Logout</span>
                                </a>

                            </div>
                        </li>

                        <li class="dropdown notification-list">
                            <a href="javascript:void(0);" class="nav-link right-bar-toggle waves-effect">
                                <i class="fe-bell noti-icon"></i>
                            </a>
                        </li>

                    </ul>


                    <div class="logo-box">
                        <a  class="logo text-center">
                            <span class="logo-lg">
                                <img src="<?php echo base_url('assets/images/').$web->gambar ?>" alt="Logo <?php echo $web->nama_website ?>" alt="" height = "60px" >
                                <?php if ($this->session->userdata("admin_level") == "user") {
                                    $this->db->where("id_dusun", $this->session->userdata("admin_dusun"));
                                    $c = $this->db->get("master_dusun")->row(); ?>
                                    <span class="header-title">SISTEM INFORMASI STP <code> <?php echo strtoupper($this->om->bentuk_p($c->bentuk)." ".$c->nama_dusun) ?></code></span>
                                <?php } else { ?>
                                    <span class="header-title"><?php echo strtoupper($web->nama_website) ?></span>
                                <?php } ?>
                                

                            </span>
                            <span class="logo-sm">

                                <img src="<?php echo base_url('assets/images/').$web->gambar ?>" alt="Logo <?php echo $web->nama_website ?>" alt="" height="40" class="img-fluid avatar-sm rounded">
                            </span>
                        </a>
                    </div>

                    <ul class="list-unstyled topnav-menu topnav-menu-left m-0">
                        <?php if ($this->session->userdata("admin_level") == "admin") {?>
                            <li class="dropdown d-none d-lg-block">
                                <a class="nav-link dropdown-toggle waves-effect" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                    Admin Area<i class="mdi mdi-chevron-down"></i> 
                                </a>
                                <div class="dropdown-menu">
                                    <a href="<?php echo site_url('admin_user') ?>" class="dropdown-item">
                                        <i class="fe-user mr-1"></i>
                                        <span>Manajemen User</span>
                                    </a>
                                    <a href="<?php echo site_url("admin_dusun") ?>" class="dropdown-item">
                                        <i class="fe-git-merge mr-1"></i>
                                        <span><?php echo $this->om->engine_nama_menu("Admin_dusun") ?></span>
                                    </a>
                                     <a href="<?php echo site_url("admin_desa") ?>" class="dropdown-item">
                                        <i class="fe-globe mr-1"></i>
                                        <span><?php echo $this->om->engine_nama_menu("Admin_desa") ?></span>
                                    </a>
                                     <a href="<?php echo site_url("admin_penyakit") ?>" class="dropdown-item">
                                        <i class="fe-github mr-1"></i>
                                        <span><?php echo $this->om->engine_nama_menu("Admin_penyakit") ?></span>
                                    </a>
                               

                                </div>
                            </li>
                        <?php } ?>

                </ul>
            </div> 
        </div>


        <div class="topbar-menu">
            <div class="container-fluid">
                <div id="navigation">

                    <ul class="navigation-menu">

                        <li class="has-submenu">
                            <a href="<?php echo site_url("admin_dashboard") ?>"><i class="fe-monitor"></i>Dashboard </a>
                        </li>

                        <?php  
                        $cek_a = $this->om->engine_akses_menu("Admin_setting_web",$this->session->userdata("admin_session"));
                      
                        $cek_logo = $this->om->engine_akses_menu("Admin_logo",$this->session->userdata("admin_session"));
                        if($cek_a == 1 or $cek_b == 1 or $cek_logo == 1 OR $this->session->userdata("admin_level") == "admin"){ ?>
                            <li class="has-submenu">
                                <a href="#"><i class="fe-settings"></i><?php echo $this->om->engine_nama_menu("Admin_setting_web") ?> <div class="arrow-down"></div></a>
                                <ul class="submenu">
                                    <?php if ($cek_a == 1 OR $this->session->userdata("admin_level") == "admin") {?>
                                        <li>
                                            <a href="<?php echo site_url("admin_setting_web") ?>"><?php echo $this->om->engine_nama_menu("Admin_setting_web") ?></a>
                                        </li>
                                    <?php } ?>
                                    
                                   
                                    <?php if ($cek_logo == 1 OR $this->session->userdata("admin_level") == "admin") {?>
                                        <li>
                                            <a href="<?php echo site_url("admin_logo") ?>"><?php echo $this->om->engine_nama_menu("Admin_logo") ?></a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </li>
                        <?php } ?>

                         <?php  
                        $cek_w_dua = $this->om->engine_akses_menu("Admin_w_dua",$this->session->userdata("admin_session"));
                        if($cek_w_dua == 1 or $this->session->userdata("admin_level") == "admin"){ ?>
                            <li class="has-submenu">
                                <a href="#"><i class="fe-droplet"></i><?php echo $this->om->engine_nama_menu("Admin_w_dua") ?> <div class="arrow-down"></div></a>
                                <ul class="submenu">
                                   <?php if ($cek_w_dua == 1 OR $this->session->userdata("admin_level") == "admin") {?>
                                    <li>
                                        <a href="<?php echo site_url("admin_w_dua") ?>"><?php if ($this->session->userdata("admin_level") == "admin") {
                                            echo "Laporan W2 Mingguan dari RS/dusun";
                                        } else {
                                            echo "W2 Mingguan";
                                        } ?></a>
                                    </li>
                                     <?php if ($this->session->userdata("admin_level") == "admin") {?>
                                        <li>
                                            <a href="<?php echo site_url("admin_w_dua/bulanan_dinas") ?>">Laporan W2 Bulanan Dari RS/dusun</a>
                                        </li>
                                    <?php } ?> 

                                     <?php if ($this->session->userdata("admin_level") == "admin") {?>
                                        <li>
                                            <a href="<?php echo site_url("admin_w_dua/laporan_dinas") ?>">Laporan W2 Bulanan Ke Provinsi</a>
                                        </li>
                                    <?php } ?> 

                                     <?php if ($this->session->userdata("admin_level") != "admin") {?>
                                        <li>
                                            <a href="<?php echo site_url("admin_w_dua/bulanan") ?>">Laporan W2 Bulanan</a>
                                        </li>
                                    <?php } ?> 
                                    <?php } ?> 
                                    
                                  
                                    <?php if ($cek_w_dua == 1 OR $this->session->userdata("admin_level") == "admin") {?>
                                        <li>
                                            <a href="<?php echo site_url("admin_w_dua/statistik") ?>">Trend Mingguan Berdasarkan Desa</a>
                                        </li>
                                    <?php } ?> 
                                </ul>
                            </li>
                        <?php } ?>

                        <?php  
                        $cek_stp = $this->om->engine_akses_menu("Admin_stp",$this->session->userdata("admin_session"));
                        if($cek_stp == 1 or $this->session->userdata("admin_level") == "admin"){ ?>
                            <li class="has-submenu">
                                <a href="#"><i class="fe-activity"></i><?php echo $this->om->engine_nama_menu("Admin_stp") ?> <div class="arrow-down"></div></a>
                                <ul class="submenu">
                                   <?php if ($cek_stp == 1 OR $this->session->userdata("admin_level") == "admin") {?>
                                    <li>
                                        <a href="<?php echo site_url("admin_stp") ?>"><?php if ($this->session->userdata("admin_level") == "admin") {
                                            echo "Laporan dari RS/dusun";
                                        } else {
                                            echo "STP";
                                        } ?></a>
                                    </li>
                                     <?php if ($this->session->userdata("admin_level") == "admin") {?>
                                        <li>
                                            <a href="<?php echo site_url("admin_stp/laporan_dinas") ?>">Laporan Ke Provinsi</a>
                                        </li>
                                    <?php } ?> 
                                    <?php } ?> 
                                    <?php if ($cek_stp == 1 OR $this->session->userdata("admin_level") == "admin") {?>
                                        <li>
                                            <a href="<?php echo site_url("admin_stp/sebaran") ?>">Distribusi Kasus Menurut Penyakit</a>
                                        </li>
                                    <?php } ?> 
                                   
                                    <?php if ($cek_stp == 1 OR $this->session->userdata("admin_level") == "admin") {?>
                                        <li>
                                            <a href="<?php echo site_url("admin_stp/statistik") ?>">Grafik Trend Kasus Penyakit</a>
                                        </li>
                                    <?php } ?> 
                                     <?php if ($this->session->userdata("admin_level") == "admin") {?>
                                        <li>
                                            <a href="<?php echo site_url("admin_stp/sebaran_dusun") ?>">Grafik Penyakit Menurut Tempat</a>
                                        </li>
                                    <?php } ?> 
                                </ul>
                            </li>
                        <?php } ?>

                       
                       


                         <?php  
                        $cek_stp_klb = $this->om->engine_akses_menu("Admin_stp_klb",$this->session->userdata("admin_session"));
                        if($cek_stp_klb == 1 or $this->session->userdata("admin_level") == "admin"){ ?>
                            <li class="has-submenu">
                                <a href="#"><i class="fe-alert-triangle"></i><?php echo $this->om->engine_nama_menu("Admin_stp_klb") ?> <div class="arrow-down"></div></a>
                                <ul class="submenu">
                                   <?php if ($cek_stp_klb == 1 OR $this->session->userdata("admin_level") == "admin") {?>
                                    <li>
                                        <a href="<?php echo site_url("admin_stp_klb") ?>"><?php if ($this->session->userdata("admin_level") == "admin") {
                                            echo "Laporan dari RS/dusun";
                                        } else {
                                            echo "STP KLB";
                                        } ?></a>
                                    </li>
                                     <?php if ($this->session->userdata("admin_level") == "admin") {?>
                                        <li>
                                            <a href="<?php echo site_url("admin_stp_klb/laporan_dinas") ?>">Laporan Ke Provinsi</a>
                                        </li>
                                    <?php } ?> 
                                    <?php } ?> 
                                 
                                </ul>
                            </li>
                        <?php } ?>



                            <?php 
                            $cek_kalender = $this->om->engine_akses_menu("Admin_kalender",$this->session->userdata("admin_session"));
                            if($this->session->userdata("admin_level") == "admin") { ?>

                                <li class="has-submenu">
                                    <a href="#"> <i class="fe-gitlab"></i><?php echo $this->om->web_me()->type ?> <div class="arrow-down"></div></a>
                                    <ul class="submenu megamenu">
                                        <li>
                                            <ul>
                                                
                                                <?php if ($this->session->userdata("admin_level") == "admin") {?>
                                                    <li>
                                                        <a href="<?php echo site_url("admin_pengumuman") ?>"><?php echo $this->om->engine_nama_menu("Admin_pengumuman") ?></a>
                                                    </li>
                                                <?php } ?> 
                                                  <?php if ($this->session->userdata("admin_level") == "admin") {?>
                                                    <li>
                                                        <a href="<?php echo site_url("admin_kalender") ?>"><?php echo $this->om->engine_nama_menu("Admin_kalender") ?></a>
                                                    </li>
                                                <?php } ?> 
                                            </ul>
                                        </li>

                                    </ul>
                                </li>
                            <?php } ?>
                           
                
                        </ul>


                        <div class="clearfix"></div>
                    </div>

                </div>

            </div>


        </header>






        <div class="wrapper">
            <?php echo $content ?>
        </div>







        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        Coder by <a href="#">Onhacker</a> 
                    </div>
                    <div class="col-md-6">
                        <div class="text-md-right footer-links d-none d-sm-block">
                            <a href="javascript:void(0);">Version 1.0.0</a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>



        <div class="right-bar">
            <div class="rightbar-title">
                <a href="javascript:void(0);" class="right-bar-toggle float-right">
                    <i class="dripicons-cross noti-icon"></i>
                </a>
                <h5 class="m-0 text-white">Info</h5>
            </div>
            <div class="slimscroll-menu rightbar-content">

                <hr class="mt-0" />
                <h5 class="pl-3 pr-3">Anda login sebagai <span class="float-right badge badge-pill badge-danger"><?php echo $this->session->userdata("admin_level") ?></span></h5>
                <hr class="mb-0" />
                <?php if ($this->session->userdata("admin_level") !="admin") {?>
                     <h5 class="pl-3 pr-3">Jika ada pertanyaan, silahkan hubungi <?php echo $this->om->web_me()->no_telp ?></h5>
                <?php } ?>
               
            </div> 
        </div>


        <div class="rightbar-overlay"></div>
        <script src="<?php echo base_url("assets/admin") ?>/js/jquery-3.1.1.min.js"></script>

        <script src="<?php echo base_url('assets/admin') ?>/js/vendor.min.js"></script>

        <script src="<?php echo base_url('assets/admin') ?>/libs/sweetalert2/sweetalert2.min.js"></script>

        <script src="<?php echo base_url('assets/admin') ?>/js/app.min.js"></script>
        <?php if ($this->uri->segment(1) == "admin_setting_web" or $this->uri->segment(1) == "admin_profil" or $this->uri->segment(1) == "admin_logo") {?>
            <script src="<?php echo base_url("assets/admin") ?>/libs/dropify/dropify_peng.js"></script>
        <?php } ?>

        <script type="text/javascript" src="<?php echo base_url(); ?>assets/admin/js/jquery.easyui.min.js"></script>
        <script src="<?php echo base_url('assets/admin') ?>/js/jquery.form.js"></script>

        <?php if ($this->uri->segment(1) == "admin_w_dua" or strtolower($controller) == "admin_pengumuman" or strtolower($controller) == "admin_kalender"  or strtolower($controller) == "admin_modul" or strtolower($controller) == "admin_kontak" or strtolower($controller) == "admin_user" ) { ?>
            <script src="<?php echo base_url("assets/admin/") ?>libs/flatpickr/flatpickr.min.js"></script>

            <script src="<?php echo base_url("assets/admin") ?>/libs/tippy-js/tippy.all.min.js"></script>
            <script src="<?php echo base_url(); ?>assets/admin/datatables/js/jquery.dataTables.min.js"></script>
            <script src="<?php echo base_url(); ?>assets/admin/datatables/js/dataTables.bootstrap4.min.js"></script>
            <script src="<?php echo base_url("assets/admin") ?>/libs/jquery-toast/jquery.toast.min.js"></script>
            <script src="<?php echo base_url("assets/admin/") ?>libs/clockpicker/bootstrap-clockpicker.min.js"></script>
            <script src="<?php echo base_url("assets/admin/") ?>libs/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>

        <?php } ?>
        <?php if (strtolower($controller) == "admin_w_dua"  or strtolower($controller) == "admin_desa" or strtolower($controller) == "admin_dusun" or strtolower($controller) == "admin_penyakit" or strtolower($controller) == "admin_stp"  or strtolower($controller) == "admin_stp_klb") {?>
             <script src="<?php echo base_url("assets/admin/") ?>libs/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
            <script src="<?php echo base_url("assets/admin") ?>/libs/tippy-js/tippy.all.min.js"></script>
            <script src="<?php echo base_url(); ?>assets/admin/datatables/js/jquery.dataTables.min.js"></script>
            <script src="<?php echo base_url(); ?>assets/admin/datatables/js/dataTables.bootstrap4.min.js"></script>
            <script src="<?php echo base_url("assets/admin") ?>/libs/jquery-toast/jquery.toast.min.js"></script>
           <script type="text/javascript" src="<?php echo base_url(); ?>assets/admin/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
        <?php } ?>

         <script src="<?php echo base_url("assets/admin/") ?>libs/select2/select2.min.js"></script>

        <script type="text/javascript">
           
          

            function logout(){
                Swal.fire({
                    title: "Yakin ingin Keluar ?",
                    text: "",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Keluar",
                    cancelButtonText: "Batal",
                    allowOutsideClick: false,
                }).then((result) => {
                    if (result.value) {
                        window.location.href = "<?php echo site_url("on_login/logout") ?>";                    } 
                    })
            }

        </script>
    </body>

    </html>