<?php
$uri_segment = $this->uri->segment(1);
$is_admin_uri = (strpos($uri_segment, 'admin_') === 0 || strpos($uri_segment, 'master_') === 0);

// ==== SESSION ====
$admin_login   = ($this->session->userdata('admin_login') === true);
$admin_user    = (string)$this->session->userdata('admin_username');
$admin_name    = trim((string)$this->session->userdata('admin_nama')); // bisa kosong utk level user lama
$admin_foto    = trim((string)$this->session->userdata('admin_foto'));
$admin_sid     = (string)$this->session->userdata('admin_session');

// ==== FALLBACK KE DB BILA NAMA/FOTO DI SESSION KOSONG ====
if ($admin_login && ($admin_name === '' || $admin_foto === '') && $admin_sid !== '') {
  $row = $this->db->select('nama_lengkap, foto')->from('users')
          ->where('id_session', $admin_sid)->limit(1)->get()->row_array();
  if ($row) {
    if ($admin_name === '' && !empty($row['nama_lengkap'])) $admin_name = $row['nama_lengkap'];
    if ($admin_foto === '' && !empty($row['foto']))        $admin_foto = $row['foto'];
  }
}

// ==== DISPLAY NAME & FOTO ====
$display_name = $admin_login ? ($admin_name !== '' ? $admin_name : $admin_user) : 'Login';

// foto: bila sudah URL penuh, pakai langsung; kalau filename → prepend base_url; default ke no-image
if ($admin_foto !== '') {
  $foto_url = filter_var($admin_foto, FILTER_VALIDATE_URL) ? $admin_foto : base_url('upload/users/'.$admin_foto);
} else {
  $foto_url = base_url('upload/users/onhacker_221a3f5e.jpg'); // fallback Anda bisa ganti ke onhacker_221a3f5e.jpg
}

// short name max 12 char
if (function_exists('mb_strlen')) {
  $short = mb_strlen($display_name) > 12 ? (mb_substr($display_name,0,12).'...') : $display_name;
} else {
  $short = strlen($display_name) > 12 ? (substr($display_name,0,12).'...') : $display_name;
}
?>

<div class="<?= !$is_admin_uri ? 'hide-on-mobile' : '' ?>">
  <ul class="list-unstyled topnav-menu float-right mb-0 ">

    <!-- Notifikasi -->
    <li class="dropdown notification-list hide-on-mobile">
      <a class="nav-link dropdown-toggle waves-effect" href="<?= site_url('home'); ?>">
        <i class="fe-external-link" ></i> Lihat Website
      </a>
    </li>
      

    <!-- User / Login -->
    <?php if ($admin_login): ?>
      <li class="dropdown notification-list">
        <a class="nav-link dropdown-toggle nav-user mr-0 waves-effect" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
          <img src="<?= htmlspecialchars($foto_url, ENT_QUOTES, 'UTF-8') ?>"
               onerror="this.onerror=null;this.src='<?= base_url('upload/users/onhacker_221a3f5e.jpg') ?>';"
               class="rounded-circle" id="foto_profil" height="28">
          <span class="pro-user-name ml-1" id="nama_profil">
            <?= htmlspecialchars($short, ENT_QUOTES, 'UTF-8') ?> <i class="mdi mdi-chevron-down"></i>
          </span>
        </a>
        <div class="dropdown-menu dropdown-menu-right profile-dropdown">
          <div class="dropdown-item noti-title mb-2 text-center">
            <h5 class="m-0 font-weight-bold text-danger">Welcome!</h5>
          </div>
          <a href="<?= site_url('admin_profil') ?>" class="dropdown-item notify-item">
            <i class="fe-user"></i><span>Pengaturan Akun</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="javascript:void(0)" onclick="logout()" class="dropdown-item notify-item">
            <i class="fe-log-out"></i><span>Logout</span>
          </a>
        </div>
      </li>
    <?php else: ?>
      <li class="dropdown notification-list">
        <a class="nav-link nav-user mr-0 waves-effect" href="<?= site_url('on_login') ?>">
          <span class="d-flex align-items-center">
            <i class="fas fa-user-circle mr-1" style="font-size:28px;color:green;"></i>
            <span class="pro-user-name">Login</span>
          </span>
        </a>
      </li>
    <?php endif; ?>
  </ul>

  <style>
    @media (max-width:767.98px){.hide-on-mobile{display:none}}
    .search-result-box .kepala{padding-bottom:20px;border-bottom:1px solid #f1f5f7;margin-bottom:20px}
    .header-title2{font-size:1.2rem;font-weight:600;color:#555;text-align:left;margin:10px 0;font-family:Arial,sans-serif;border-left:4px solid #ff5722;padding-left:10px;text-shadow:1px 1px 2px rgba(0,0,0,.15)}
    .d-flex.align-items-center.mb-3>.me-3 img{max-height:50px;width:auto;display:block;transition:all .3s ease-in-out;filter:drop-shadow(2px 2px 6px rgba(0,0,0,.3))}
    .d-flex.align-items-center.mb-3>.me-3 img:hover{filter:drop-shadow(0 0 10px rgba(255,87,34,.6));transform:scale(1.05)}
    .bord{border-left:4px solid #ff5722;padding-left:10px}
  </style>
</div>
