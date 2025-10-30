<!DOCTYPE html>
<html lang="en"> 
<?php 
$web = $this->om->web_me();
$us = $this->om->user();
if ($this->session->userdata("op_login") == true) {
    $fr = "Operator";
} else {
    $fr = "User";
}
?>
<head>
    <meta charset="utf-8" />
    <title>>_ <?php echo $fr." ".$this->session->op_username ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Onhacker CMS" name="description" />
    <meta content="Onhacker" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    
    <link rel="shortcut icon" href="<?php echo base_url('assets/images/').$web->favicon ?>">
    
    <link href="<?php echo base_url('assets/admin') ?>/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/admin') ?>/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/admin') ?>/css/app.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/admin') ?>/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css" />
    <?php if ($this->uri->segment(1) == "admin_hal" or strtolower($controller) == "admin_pengumuman" or strtolower($controller) == "admin_agenda"   or $this->uri->segment(1) == "admin_pesan" or $this->uri->segment(1) == "admin_modul" or $this->uri->segment(1) == "admin_kontak" or strtolower($controller) == "admin_post" or strtolower($controller) == "admin_foto") { ?>
        <link href="<?php echo base_url("assets/admin") ?>/summernote/summernote-bs4.min.css" rel="stylesheet">
        <link href="<?php echo base_url(); ?>assets/admin/libs/clockpicker/bootstrap-clockpicker.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url(); ?>assets/admin/libs/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <?php } ?>
    <?php if ($this->uri->segment(1) == "admin_pengurus") { ?>
        <link href="<?php echo base_url(); ?>assets/admin/libs/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <?php } ?>
    <script src="<?php echo base_url("assets/admin") ?>/js/jquery-3.1.1.min.js"></script>
    

</head>
<body class="menubar-gradient gradient-topbar  ">

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

                <?php $cek_pesan = $this->om->engine_akses_menu("Admin_pesan",$this->session->userdata("admin_session"));
                if ($cek_pesan == 1 OR $this->session->userdata("admin_level") == "admin") { ?>
                    <li class="dropdown notification-list">
                        <a class="nav-link dropdown-toggle waves-effect" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                            <i class="fe-mail noti-icon"></i>
                            <?php if ($jumlah_pesan > 0) {?>
                                <span class="badge badge-danger rounded-circle noti-icon-badge" id="jml"><?php echo $jumlah_pesan ?></span>
                                <?PHP } ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right dropdown-lg">

                                <div class="dropdown-item noti-title">
                                    <h5 class="m-0">
                                        <?php echo $this->om->engine_nama_menu("Admin_pesan") ?>
                                    </h5>
                                </div>

                                <div class="slimscroll noti-scroll">
                                    <?php 
                                    foreach ($pesan->result() as $row) {
                                        $isi_pesan = substr($row->pesan,0,20);
                                        $titik = strlen($row->pesan);
                                        if ($titik > 20) {
                                            $tt = " ...";
                                        } else {
                                            $tt = "";
                                        }
                                        $waktukirim = cek_terakhir($row->tanggal.' '.$row->jam);
                                        if ($row->dibaca=='N'){
                                            $color = 'warning'; 
                                            $baca = "belum dibaca"; 
                                            $ac = "active";
                                        } else { 
                                            $color = 'muted'; 
                                            $baca = ""; 
                                            $ac = "";
                                        }
                                        ?>


                                        <a href="<?php echo site_url("admin_pesan") ?>" class="dropdown-item notify-item <?php echo $ac ?>">
                                            <div class="notify-icon bg-primary ">
                                                <i class="fe-message-circle"></i> </div>
                                                <p class="notify-details"><?php echo $row->nama ?></p>
                                                <p class="text-muted mb-0 user-msg">
                                                    <small class="text-<?php echo $color ?>"><?php echo $isi_pesan.$tt ?> <span class="badge badge-info badge-pill"><?php echo $waktukirim ?> yang lalu</span> <span class="badge badge-danger badge-pill"><?php echo $baca ?></span></small>
                                                </p>
                                            </a>
                                        <?php } ?>

                                    </div>


                                    <a href="<?php echo site_url("admin_pesan") ?>" class="dropdown-item text-center text-primary notify-item notify-all">
                                        Lihat Semua
                                        <i class="fi-arrow-right"></i>
                                    </a>

                                </div>
                            </li>
                        <?php } ?>

                       


                    </ul>


                    <div class="logo-box">
                        <a  class="logo text-center">
                            <span class="logo-lg">
                                <img src="<?php echo base_url('assets/images/').$web->gambar ?>" alt="Logo <?php echo $web->nama_website ?>" alt="" height = "60px" >
                                <?php  
                                    $this->db->where("npsn", $this->session->userdata("op_username"));
                                    $sch = $this->db->get("sekolah")->row()
                                ?>
                                <span class="header-title"><?php echo strtoupper($this->session->userdata("op_username"). " ". $sch->nama_sekolah)  ?></span>

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
                                   <!--  <a href="<?php echo site_url('admin_log') ?>" class="dropdown-item">
                                        <i class="fe-terminal"></i>
                                        <span>Log</span>
                                    </a> -->
                                    <a href="<?php echo site_url('admin_modul') ?>" class="dropdown-item">
                                        <i class="fe-terminal"></i>
                                        <span>Manajemen Modul</span>
                                    </a>
                                </div>
                            </li>
                        <?php } ?>

                    <!-- <li class="dropdown dropdown-mega d-none d-lg-block">
                        <a class="nav-link dropdown-toggle waves-effect" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                            Mega Menu
                            <i class="mdi mdi-chevron-down"></i> 
                        </a>
                        <div class="dropdown-menu dropdown-megamenu">
                            <div class="row">
                                <div class="col-sm-8">

                                    <div class="row">
                                        <div class="col-md-4">
                                            <h5 class="text-dark mt-0">UI Components</h5>
                                            <ul class="list-unstyled megamenu-list">
                                                <li>
                                                    <a href="javascript:void(0);">Widgets</a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);">Nestable List</a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);">Range Sliders</a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);">Masonry Items</a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);">Sweet Alerts</a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);">Treeview Page</a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);">Tour Page</a>
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="col-md-4">
                                            <h5 class="text-dark mt-0">Applications</h5>
                                            <ul class="list-unstyled megamenu-list">
                                                <li>
                                                    <a href="javascript:void(0);">eCommerce Pages</a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);">CRM Pages</a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);">Email</a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);">Calendar</a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);">Team Contacts</a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);">Task Board</a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);">Email Templates</a>
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="col-md-4">
                                            <h5 class="text-dark mt-0">Extra Pages</h5>
                                            <ul class="list-unstyled megamenu-list">
                                                <li>
                                                    <a href="javascript:void(0);">Left Sidebar with User</a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);">Menu Collapsed</a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);">Small Left Sidebar</a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);">New Header Style</a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);">Search Result</a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);">Gallery Pages</a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);">Maintenance & Coming Soon</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="text-center mt-3">
                                        <h3 class="text-dark">Special Discount Sale!</h3>
                                        <h4>Save up to 70% off.</h4>
                                        <button class="btn btn-primary btn-rounded mt-3">Download Now</button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </li> -->
                    <li class="dropdown dropdown-mega d-none d-lg-block">
                        <a  href="<?php echo site_url("pengumuman") ?> " class="nav-link dropdown-toggle waves-effect" target = "_BLANK">
                        <i class="fe-globe noti-icon"></i> Lihat Hasil </a>
                        <!--   <a  href="javascript:void(0)" onclick="lihat_hasil()" class="nav-link dropdown-toggle waves-effect">
                        <i class="fe-globe noti-icon"></i> Lihat Hasil </a> -->
                    </li>
                    <script type="text/javascript">
                        function lihat_hasil(){

            Swal.fire({
                title: "Proses Data..",
                text: "Data sedang diolan untuk ditampilkan, pastikan data dibawah telah sesuai sebelum dipublish",
                type: "warning",
                showCancelButton: false,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Ok Fine",
                cancelButtonText: "Batal",
                allowOutsideClick: false,
            })
                        }
                    </script>
                </ul>
            </div> 
        </div>


        <div class="topbar-menu">
            <div class="container-fluid">
                <div id="navigation">

                    <ul class="navigation-menu">

                        <li class="has-submenu">
                            <a href="<?php echo site_url("admin_siswa") ?>"><i class="fe-monitor"></i>Data </a>
                        </li>
                       
                       
                        <li class="has-submenu">
                            <a href="javascript:void(0)" onclick="ganti_password()"><i class="fe-monitor"></i>Ganti Password </a>
                        </li>
                         <li class="has-submenu">
                            <a href="<?php echo site_url("op_login/logout") ?>"><i class="fe-monitor"></i>Logout </a>
                        </li>

                        <?php  
                        $cek_a = $this->om->engine_akses_menu("Admin_setting_web",$this->session->userdata("admin_session"));
                        $cek_b = $this->om->engine_akses_menu("Admin_menu",$this->session->userdata("admin_session"));
                        $cek_logo = $this->om->engine_akses_menu("Admin_logo",$this->session->userdata("admin_session"));
                        $cek_kontak = $this->om->engine_akses_menu("Admin_kontak",$this->session->userdata("admin_session"));
                        if($cek_a == 1 or $cek_b == 1 or $cek_logo == 1 OR $this->session->userdata("admin_level") == "admin"){ ?>
                            <li class="has-submenu">
                                <a href="#"><i class="fe-settings"></i>Pengaturan Web <div class="arrow-down"></div></a>
                                <ul class="submenu">
                                    <?php if ($cek_a == 1 OR $this->session->userdata("admin_level") == "admin") {?>
                                        <li>
                                            <a href="<?php echo site_url("admin_setting_web") ?>"><?php echo $this->om->engine_nama_menu("Admin_setting_web") ?></a>
                                        </li>
                                    <?php } ?>
                                    <?php if ($cek_kontak == 1 OR $this->session->userdata("admin_level") == "admin") {?>
                                        <li>
                                            <a href="<?php echo site_url("admin_kontak") ?>"><?php echo $this->om->engine_nama_menu("Admin_kontak") ?></a>
                                        </li>
                                    <?php } ?>
                                    <?php if ($cek_b == 1 OR $this->session->userdata("admin_level") == "admin") {?>
                                        <li>
                                            <a href="<?php echo site_url("admin_menu") ?>"><?php echo $this->om->engine_nama_menu("Admin_menu") ?></a>
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
                        $cek_c = $this->om->engine_akses_menu("Admin_hal",$this->session->userdata("admin_session"));
                        $cek_d = $this->om->engine_akses_menu("Admin_post",$this->session->userdata("admin_session"));
                        $cek_e = $this->om->engine_akses_menu("Admin_kategori",$this->session->userdata("admin_session"));
                        $cek_f = $this->om->engine_akses_menu("Admin_tag",$this->session->userdata("admin_session"));
                        $cek_g = $this->om->engine_akses_menu("Admin_foto",$this->session->userdata("admin_session"));
                        $cek_h = $this->om->engine_akses_menu("Admin_file",$this->session->userdata("admin_session"));
                        $cek_i = $this->om->engine_akses_menu("admin_pengumuman",$this->session->userdata("admin_session"));
                        $cek_agenda = $this->om->engine_akses_menu("admin_agenda",$this->session->userdata("admin_session"));
                        $cek_komen = $this->om->engine_akses_menu("admin_komentar",$this->session->userdata("admin_session"));
                        $cek_sensor = $this->om->engine_akses_menu("admin_sensor",$this->session->userdata("admin_session"));
                        $cek_banner = $this->om->engine_akses_menu("admin_banner",$this->session->userdata("admin_session"));
                        if($cek_c == 1 
                            or $cek_d == 1 
                            or $cek_sensor == 1 
                            or $cek_banner == 1  
                            or $cek_komen == 1  
                            or $cek_agenda == 1 
                            or $cek_h == 1 
                            or $cek_e == 1 
                            or $cek_g == 1  
                            OR $this->session->userdata("admin_level") == "admin") { ?>
                                <li class="has-submenu">
                                    <a href="#"><i class="fe-grid"></i>Posting <div class="arrow-down"></div></a>
                                    <ul class="submenu megamenu">
                                        <li>
                                            <ul>
                                                <?php if ($cek_c == 1 OR $this->session->userdata("admin_level") == "admin") {?>
                                                    <li>
                                                        <a href="<?php echo site_url("admin_hal") ?>"><?php echo $this->om->engine_nama_menu("Admin_hal") ?></a>
                                                    </li>
                                                <?php } ?> 
                                                <?php if ($cek_d == 1 OR $this->session->userdata("admin_level") == "admin") {?>
                                                    <li>
                                                        <a href="<?php echo site_url("admin_post") ?>"><?php echo $this->om->engine_nama_menu("Admin_post") ?></a>
                                                    </li>
                                                <?php } ?>    
                                                <?php if ($cek_e == 1 OR $this->session->userdata("admin_level") == "admin") {?>
                                                    <li>
                                                        <a href="<?php echo site_url("admin_kategori") ?>"><?php echo $this->om->engine_nama_menu("Admin_kategori") ?></a>
                                                    </li>
                                                <?php } ?>    
                                                <?php if ($cek_f == 1 OR $this->session->userdata("admin_level") == "admin") {?>
                                                    <li>
                                                        <a href="<?php echo site_url("admin_tag") ?>"><?php echo $this->om->engine_nama_menu("Admin_tag") ?></a>
                                                    </li>
                                                <?php } ?> 
                                                <?php if ($cek_f == 1 OR $this->session->userdata("admin_level") == "admin") {?>
                                                    <li>
                                                        <a href="<?php echo site_url("admin_komentar") ?>"><?php echo $this->om->engine_nama_menu("Admin_komentar") ?> <?php echo $this->om->engine_nama_menu("Admin_post") ?></a>
                                                    </li>
                                                <?php } ?> 
                                                 <?php if ($cek_sensor == 1 OR $this->session->userdata("admin_level") == "admin") {?>
                                                    <li>
                                                        <a href="<?php echo site_url("admin_sensor") ?>"><?php echo $this->om->engine_nama_menu("Admin_sensor") ?></a>
                                                    </li>
                                                <?php } ?> 

                                            </ul>
                                        </li>
                                        <li>
                                            <ul>
                                                <?php if ($cek_agenda == 1 OR $this->session->userdata("admin_level") == "admin") {?>
                                                    <li class="has-submenu">
                                                        <a href="<?php echo site_url("admin_agenda") ?>"><?php echo $this->om->engine_nama_menu("Admin_agenda") ?></a>
                                                    </li>
                                                <?php } ?> 
                                                <?php if ($cek_i == 1 OR $this->session->userdata("admin_level") == "admin") {?>
                                                    <li>
                                                        <a href="<?php echo site_url("admin_pengumuman") ?>"><?php echo $this->om->engine_nama_menu("Admin_pengumuman") ?></a>
                                                    </li>
                                                <?php } ?>   
                                                <?php if ($cek_g == 1 OR $this->session->userdata("admin_level") == "admin") {?>
                                                    <li class="has-submenu">
                                                        <a href="<?php echo site_url("admin_foto") ?>"><?php echo $this->om->engine_nama_menu("Admin_foto") ?></a>
                                                    </li>
                                                <?php } ?> 
                                                <?php if ($cek_h == 1 OR $this->session->userdata("admin_level") == "admin") {?>
                                                    <li class="has-submenu">
                                                        <a href="<?php echo site_url("admin_file") ?>"><?php echo $this->om->engine_nama_menu("Admin_file") ?></a>
                                                    </li>
                                                <?php } ?> 
                                                <?php if ($cek_banner == 1 OR $this->session->userdata("admin_level") == "admin") {?>
                                                    <li class="has-submenu">
                                                        <a href="<?php echo site_url("admin_banner") ?>"><?php echo $this->om->engine_nama_menu("Admin_banner") ?></a>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                            <?php } ?>



                            <?php 
                            $cek_jabatan = $this->om->engine_akses_menu("Admin_jabatan",$this->session->userdata("admin_session"));
                            $cek_pengurus = $this->om->engine_akses_menu("Admin_pengurus",$this->session->userdata("admin_session"));
                            $cek_anggaran = $this->om->engine_akses_menu("Admin_anggaran",$this->session->userdata("admin_session"));
                            $sekolah = $this->om->engine_akses_menu("admin_siswa",$this->session->userdata("admin_session"));
                            if($cek_jabatan == 1 or $cek_pengurus  or $cek_anggaran == 1 or $sekolah == 1 OR $this->session->userdata("admin_level") == "admin") { ?>

                                <li class="has-submenu">
                                    <a href="#"> <i class="fe-briefcase"></i><?php echo $this->om->web_me()->type ?> <div class="arrow-down"></div></a>
                                    <ul class="submenu megamenu">
                                        <li>
                                            <ul>
                                                <?php if ($cek_jabatan == 1 OR $this->session->userdata("admin_level") == "admin") {?>
                                                    <li>
                                                        <a href="<?php echo site_url("admin_jabatan") ?>"><?php echo $this->om->engine_nama_menu("Admin_jabatan") ?></a>
                                                    </li>
                                                <?php } ?> 
                                                <?php if ($cek_pengurus == 1 OR $this->session->userdata("admin_level") == "admin") {?>
                                                    <li>
                                                        <a href="<?php echo site_url("admin_pengurus") ?>"><?php echo $this->om->engine_nama_menu("Admin_pengurus") ?></a>
                                                    </li>
                                                <?php } ?> 
                                           
                                            </ul>
                                        </li>

                                    </ul>
                                </li>
                            <?php } ?>
                            <?php $cek_pesan_menu = $this->om->engine_akses_menu("Admin_pesan",$this->session->userdata("admin_session"));
                            if ($cek_pesan_menu == "1"  OR $this->session->userdata("admin_level") == "admin") { ?>
                                <li>
                                    <a href="<?php echo site_url("admin_pesan") ?>"><i class="fe-mail"></i> <?php echo $this->om->engine_nama_menu("Admin_pesan") ?></a>
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

        <?php if ($this->uri->segment(1) == "admin_menu") {?>
            <script src="<?php echo base_url("assets/admin") ?>/libs/nestable2/jquery.nestable.min.js"></script>
        <?php } ?>
        <?php if ($this->uri->segment(1) == "admin_hal" or strtolower($controller) == "admin_pengumuman"  or strtolower($controller) == "admin_agenda"  or strtolower($controller) == "admin_siswa" or strtolower($controller) == "admin_history" or strtolower($controller) == "admin_komentar"   or strtolower($controller) == "admin_post" or strtolower($controller) == "admin_modul" or strtolower($controller) == "admin_kontak" or strtolower($controller) == "admin_user"  or strtolower($controller) == "admin_foto" or strtolower($controller) == "admin_pesan") { ?>
            <script src="<?php echo base_url("assets/admin") ?>/libs/tippy-js/tippy.all.min.js"></script>
            <script src="<?php echo base_url(); ?>assets/admin/datatables/js/jquery.dataTables.min.js"></script>
            <script src="<?php echo base_url(); ?>assets/admin/datatables/js/dataTables.bootstrap4.min.js"></script>
            <script src="<?php echo base_url("assets/admin") ?>/libs/jquery-toast/jquery.toast.min.js"></script>

            <script src="<?php echo base_url("assets/admin") ?>/summernote/summernote-bs4.min.js"></script>
            <script src="<?php echo base_url("assets/admin") ?>/summernote/lang/summernote-id-ID.js"></script>
            <script src="<?php echo base_url("assets/admin/") ?>libs/clockpicker/bootstrap-clockpicker.min.js"></script>
            <script src="<?php echo base_url("assets/admin/") ?>libs/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>

        <?php } ?>
        <?php if (strtolower($controller) == "admin_pengurus" or strtolower($controller) == "admin_anggaran" ) {?>
            <script src="<?php echo base_url("assets/admin/") ?>libs/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
            <script src="<?php echo base_url(); ?>assets/admin/datatables/js/jquery.dataTables.min.js"></script>
            <script src="<?php echo base_url(); ?>assets/admin/datatables/js/dataTables.bootstrap4.min.js"></script>
            <script src="<?php echo base_url("assets/admin") ?>/libs/jquery-toast/jquery.toast.min.js"></script>
        <?php } ?>
        <?php if (strtolower($controller) == "admin_kategori" or strtolower($controller) == "admin_banner" or strtolower($controller) == "admin_jabatan" or strtolower($controller) == "admin_tag" or strtolower($controller) == "admin_sensor" or strtolower($controller) == "admin_pengurus"  or strtolower($controller) == "admin_file" or strtolower($controller) == "admin_log") {?>
            <script src="<?php echo base_url("assets/admin") ?>/libs/tippy-js/tippy.all.min.js"></script>
            <script src="<?php echo base_url(); ?>assets/admin/datatables/js/jquery.dataTables.min.js"></script>
            <script src="<?php echo base_url(); ?>assets/admin/datatables/js/dataTables.bootstrap4.min.js"></script>
            <script src="<?php echo base_url("assets/admin") ?>/libs/jquery-toast/jquery.toast.min.js"></script>
        <?php } ?>

        <script type="text/javascript">
            $(document).on('keypress', ':input:not(textarea):not([type=submit])', function (e) {
                if (e.which == 13) e.preventDefault();
            });

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