<!DOCTYPE html>
<html lang="id"> 
<?php 
$web = $this->om->web_me();
$us = $this->om->user();

?>
<head>
    <meta charset="utf-8" />
    <title><?php echo $subtitle ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover,user-scalable=no">
    <meta name="robots" content="noindex">
    <meta content="Onhacker CMS" name="description" />
    <meta content="Onhacker" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="google" content="notranslate">
    <link rel="icon" href="<?php echo base_url('assets/images/favicon.ico') ?>" type="image/x-icon" />
    <link rel="shortcut icon" href="<?php echo base_url('assets/images/favicon.ico') ?>" type="image/x-icon" />
    <link href="<?php echo base_url('assets/admin') ?>/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/admin') ?>/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/admin') ?>/css/app.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url() ?>assets/admin/libs/animate/animate.min.css" rel="stylesheet" type="text/css" />
    <?php if ($this->uri->segment(1) == "admin_permohonan") { ?>
       <link href="<?php echo base_url("assets/admin") ?>/libs/dropify/dropify.min.css" rel="stylesheet" type="text/css" />
       <style>
        .dropify-wrapper .dropify-clear {
            display: none !important;
            pointer-events: none !important;
            visibility: hidden !important;
        }
    </style>
<?php } ?>
<link href="<?php echo base_url('assets/admin') ?>/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>assets/admin/libs/select2/select2.min.css" rel="stylesheet" type="text/css" />
<script src="<?php echo base_url("assets/admin") ?>/js/jquery-3.1.1.min.js"></script>

<style type="text/css">
    html {
      scroll-behavior: smooth;
  }

  .white-shadow-text {
      color: #fff !important; 
      text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
  }
  .berubah.gradient-topbar #topnav {
      background: linear-gradient(80deg, #0d47a1 0%, #42a5f5 100%);
  }

</style>
</head>
<?php $this->load->view("global") ?>
<body class="menubar-gradient gradient-topbar topbar-dark <?= $this->session->userdata('admin_login') ? 'berubah' : '' ?>">

    <div id="preloader">
        <div id="status">
           <div class="image-container flip animated infinite">
              <img src="<?php echo base_url('assets/images/loader.png') ?>" alt="Foto" style="display: none;" onload="this.style.display='block';" />
          </div>
      </div>
    </div>

<header id="topnav">
  <div class="navbar-custom">
    <div class="container-fluid">
      <?php $this->load->view("backend/notif.php") ?>

      <!-- DESKTOP HEADER (sembunyikan di mobile) -->
      <div class="logo-desktop d-none d-lg-flex align-items-center mb-3">
        <div class="me-3">
          <img src="<?php echo base_url('assets/images/logo_admin.png') ?>" 
               alt="Logo <?php echo htmlspecialchars($web->nama_website, ENT_QUOTES) ?>" height="50">
        </div>
        <div class="kepala">
          <h4 class="mb-1 ml-2">
            <span class="header-title2">
              <?php echo htmlspecialchars($web->nama_website." ".strtoupper($web->kabupaten), ENT_QUOTES) ?>
            </span>
          </h4>
          <div class="font-13 text-success mb-2 ml-2 text-truncate">
            <code><?php echo strtoupper($web->meta_deskripsi." ".$us->kota) ?></code>
          </div>
        </div>
      </div>

      <!-- MOBILE HEADER (hanya tampil di mobile) -->
      <div class="logo-box d-lg-none">
        <a class="logo text-center" href="javascript:void(0)">
          <span class="logo-sm d-inline-flex align-items-center">
            <img src="<?php echo base_url('assets/images/logo.png') ?>" 
                 alt="Logo <?php echo htmlspecialchars($web->nama_website, ENT_QUOTES) ?>" height="40" class="mr-1">
            <span class="header-title ml-1 white-shadow-text">
              <?php echo htmlspecialchars($web->nama_website, ENT_QUOTES) ?>
            </span>
          </span>
        </a>
      </div>
      <!-- /MOBILE HEADER -->

    </div><!-- /.container-fluid -->
  </div><!-- /.navbar-custom -->

  <div class="topbar-menu">
    <div class="container-fluid">
      <div id="navigation">
       <ul class="navigation-menu">
        <ul id="menu-dynamic" class="navigation-menu"></ul>

<script>
  document.addEventListener("DOMContentLoaded", function () {
  const container = document.getElementById("menu-dynamic");
  const quickDataLink = document.getElementById("quick-data-link");
  const quickDataCard = document.getElementById("quick-data-card");

  function isMobile() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
  }

  const isAdmin = <?= $this->session->userdata("admin_login") ? 'true' : 'false' ?>;

  // helper: update Quick Link "Data" berdasarkan HTML menu server
  function applyQuickLinkData(menuHTML) {
    try {
      // Bungkus sebagai <ul> agar DOMParser mudah cari <li>/<a>
      const parser = new DOMParser();
      const doc = parser.parseFromString(`<ul>${menuHTML}</ul>`, "text/html");

      // Cari item menu "admin_permohonan"
      const link = doc.querySelector('a[href*="/admin_permohonan"]');
      if (!link) {
        // Tidak punya akses -> sembunyikan kartu
        quickDataLink.style.display = "none";
        return;
      }

      // Sinkronkan href (jaga-jaga jika base_url berubah)
      quickDataLink.href = link.getAttribute("href");

      // Tandai aktif (opsional, sesuai class di build_menu)
      const li = link.closest("li");
      const isActive = li && li.classList.contains("active-menu");
      quickDataCard.classList.toggle("active", !!isActive);
    } catch (e) {
      console.warn("Gagal mem-parsing menu untuk quick link:", e);
    }
  }

  if (isAdmin && !isMobile()) {
    fetch("<?= site_url("api/get_menu_desktop?uri=" . $uri) ?>&t=" + Date.now())
      .then(res => res.json())
      .then(data => {
        container.innerHTML = data.menu;                 // render menu di header/sidebar
        applyQuickLinkData(data.menu);                   // sinkronkan kartu Data
        console.log("✅ Menu dinamis dimuat & quick link sinkron");
      })
      .catch(() => {
        container.innerHTML = "<li><em>Gagal memuat menu dinamis</em></li>";
        // fallback: tampilkan kartu Data default
        quickDataLink.style.display = "";
        console.log("⚠️ Gagal fetch menu; pakai quick link default");
      });
  } else {
    container.innerHTML = "";
    // untuk mobile / non-admin: bebas pilih
    // quickDataLink.style.display = "none"; // kalau mau sembunyikan di mobile
    console.log("ℹ️ Mobile atau non-admin, tidak memuat menu dinamis");
  }
});
</script>
    </div>
  </div>
</header>





<div class="wrapper">
    <?php echo $content ?>
</div>


<?php $this->load->view("backend/footer_admin.php"); ?>


<footer class="footer d-none d-md-block">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                Coder by <a href="#"><?php echo $web->credits ?></a> 
            </div>
            <div class="col-md-6">
                <div class="text-md-right footer-links d-none d-sm-block">
                  <button type="button" id="btn-enable-sound-inline" class="btn btn-warning ml" style="display:none">
                  Aktifkan Suara Notifikasi
                </button>

                    <!-- <a href="javascript:void(0);">Version 2.0.0</a> -->
              </div>

            </div>
        </div>
    </div>
</footer>


<script src="<?php echo base_url('assets/admin') ?>/js/vendor.min.js"></script>
<script src="<?php echo base_url('assets/admin') ?>/js/app.min.js"></script>
<script src="<?php echo base_url('assets/admin') ?>/js/sw.min.js"></script>

<script type="text/javascript" src="<?php echo base_url(); ?>assets/admin/js/jquery.easyui.min.js"></script>
<?php if ($this->uri->segment(1) == "admin_profil" or $this->uri->segment(1) == "admin_permohonan") {?>
    <script src="<?php echo base_url("assets/admin") ?>/libs/dropify/dropify_peng.js"></script>

<?php } ?>


<?php if ($this->uri->segment(1) == "admin_reg" or $this->uri->segment(1) == "admin_permohonan") {?>
    <script src="<?php echo base_url(); ?>assets/admin/datatables/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/admin/datatables/js/dataTables.bootstrap4.min.js"></script>
<?php } ?>


<?php if (strtolower($controller) == "admin_modul" or strtolower($controller) == "admin_user" ) { ?>
    <script src="<?php echo base_url("assets/admin") ?>/libs/tippy-js/tippy.all.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/admin/datatables/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/admin/datatables/js/dataTables.bootstrap4.min.js"></script>
    <script src="<?php echo base_url("assets/admin") ?>/libs/jquery-toast/jquery.toast.min.js"></script>

<?php } ?>


<?php if (strtolower($controller) == "admin_billiard" or strtolower($controller) == "admin_unit_lain"  or strtolower($controller) == "admin_produk" or strtolower($controller) == "admin_meja" or strtolower($controller) == "admin_pos" or strtolower($controller) == "admin_kategori_produk" or strtolower($controller) == "admin_pos_riwayat" or strtolower($controller) == "admin_riwayat_billiard" or strtolower($controller) == "admin_pengeluaran" or strtolower($controller) == "admin_meja_billiard" or strtolower($controller) == "admin_pengumuman" or strtolower($controller) == "admin_kurir" or strtolower($controller) == "admin_kursi_pijat") {?>
   <script src="<?php echo base_url(); ?>assets/admin/datatables/js/jquery.dataTables.min.js"></script>
   <script src="<?php echo base_url(); ?>assets/admin/datatables/js/dataTables.bootstrap4.min.js"></script>
   <script src="<?php echo base_url("assets/admin") ?>/libs/jquery-toast/jquery.toast.min.js"></script>
   <script src="<?php echo base_url("assets/admin/") ?>libs/select2/select2.min.js"></script> 
<?php } ?>

<?php if (strtolower($controller) == "admin_dashboard" or strtolower($controller) == "admin_permohonan") {?>
   <script src="<?php echo base_url("assets/admin/") ?>libs/select2/select2.min.js"></script>
   <script src="<?php echo base_url("assets/admin") ?>/libs/tippy-js/tippy.all.min.js"></script>
<?php } ?>
<?php if (strtolower($controller) == "admin_produk"  or strtolower($controller) == "admin_pengumuman") {?>
  <script src="<?= base_url('assets/admin/summernote/summernote-bs4.min.js'); ?>"></script>

<?php } ?>
<script>
    var base_url = "<?= base_url() ?>";
</script>



</body>

</html>