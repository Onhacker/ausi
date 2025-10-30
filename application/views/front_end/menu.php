<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php if (($mode ?? 'front') === 'admin'): ?>

  <div class="menu-list">
    <!-- ====== ADMIN QUICK LIST ====== -->
    <a href="<?= site_url('admin_profil/detail_profil') ?>" class="menu-item">
      <i class="fas fa-user-circle"></i><span>Profil</span>
    </a>

    <a href="<?= site_url('booking') ?>" class="menu-item">
      <i class="fas fa-calendar-check"></i><span>Booking</span>
    </a>

    <a href="<?= site_url('hal/alur') ?>" class="menu-item">
      <i class="fas fa-project-diagram"></i><span>Alur Kunjungan</span>
    </a>

    <?php if (function_exists('user_can_mod') && user_can_mod(['admin_dashboard'])): ?>
      <a href="<?= site_url('admin_dashboard') ?>" class="menu-item">
        <i class="fas fa-chart-line"></i><span>Statistik</span>
      </a>
    <?php endif; ?>

    <?php if (function_exists('user_can_mod') && user_can_mod(['admin_dashboard/monitor'])): ?>
      <a href="<?= site_url('admin_dashboard/monitor') ?>" class="menu-item">
        <i class="fas fa-clipboard-list"></i><span>Monitoring</span>
      </a>
    <?php endif; ?>

    <?php if (function_exists('user_can_mod') && user_can_mod(['admin_scan'])): ?>
      <a href="<?= site_url('admin_scan') ?>" class="menu-item">
        <i class="fas fa-qrcode"></i><span>Checkin/Checkout</span>
      </a>
    <?php endif; ?>

    <?php if (function_exists('user_can_mod') && user_can_mod(['admin_permohonan'])): ?>
      <a href="<?= site_url('admin_permohonan') ?>" class="menu-item">
        <i class="fas fa-database"></i><span>Data</span>
      </a>
    <?php endif; ?>

    <?php if (function_exists('user_can_mod') && user_can_mod(['admin_user'])): ?>
      <a href="<?= site_url('admin_user') ?>" class="menu-item">
        <i class="fas fa-users-cog"></i><span>Manajemen User</span>
      </a>
    <?php endif; ?>

    <?php if (function_exists('user_can_mod') && user_can_mod(['admin_setting_web'])): ?>
      <a href="<?= site_url('admin_setting_web') ?>" class="menu-item">
        <i class="fas fa-cogs"></i><span>Pengaturan Sistem</span>
      </a>
    <?php endif; ?>

    <?php if (function_exists('user_can_mod') && user_can_mod(['admin_unit_tujuan'])): ?>
      <a href="<?= site_url('admin_unit_tujuan') ?>" class="menu-item">
        <i class="fas fa-building"></i><span>Unit Tujuan</span>
      </a>
    <?php endif; ?>

    <?php if (function_exists('user_can_mod') && user_can_mod(['Admin_unit_lain'])): ?>
      <a href="<?= site_url('admin_unit_lain') ?>" class="menu-item">
        <i class="fas fa-briefcase"></i><span>Unit Lain</span>
      </a>
    <?php endif; ?>

    <?php if (function_exists('user_can_mod') && user_can_mod(['Admin_instansi_ref'])): ?>
      <a href="<?= site_url('admin_instansi_ref') ?>" class="menu-item">
        <i class="fe-briefcase"></i><span>Instansi Asal</span>
      </a>
    <?php endif; ?>

    <?php if (function_exists('user_can_mod') && user_can_mod(['Admin_pengumuman'])): ?>
      <a href="<?= site_url('admin_pengumuman') ?>" class="menu-item">
        <i class="fe-bell"></i><span>Pengumuman</span>
      </a>
    <?php endif; ?>

    <a href="<?= site_url('hal/kontak') ?>" class="menu-item">
      <i class="fas fa-address-book"></i><span>Kontak</span>
    </a>
  </div>

<?php else: ?>

  <!-- ====== FRONT (PENGUNJUNG) QUICK STRIP ====== -->
  <div class="quickmobilem quickmobilem-scroll d-flex text-center" tabindex="0" aria-label="Menu cepat geser">
    <div class="quickmobilem-item">
      <a href="<?= site_url('booking') ?>" class="qcard d-block text-decoration-none">
        <div class="menu-circle" style="background:#17a2b8;"><span class="emoji-icon">📅</span></div>
        <small class="menu-label">Booking</small>
      </a>
    </div>

    <div class="quickmobilem-item">
      <a href="<?= site_url('hal/jadwal') ?>" class="qcard d-block text-decoration-none">
        <div class="menu-circle" style="background:#dc7633;"><span class="emoji-icon">⏰</span></div>
        <small class="menu-label">Jadwal</small>
      </a>
    </div>

    <div class="quickmobilem-item">
      <a href="<?= site_url('hal/pengumuman') ?>" class="qcard d-block text-decoration-none">
        <div class="menu-circle" style="background:#e74c3c;"><span class="emoji-icon">📣</span></div>
        <small class="menu-label">Pengumuman</small>
      </a>
    </div>

    <div class="quickmobilem-item">
      <a href="<?= site_url('hal/struktur') ?>" class="qcard d-block text-decoration-none">
        <div class="menu-circle" style="background:#8e44ad;"><span class="emoji-icon">🏛️</span></div>
        <small class="menu-label">Struktur Organisasi</small>
      </a>
    </div>

    <div class="quickmobilem-item">
      <a href="<?= site_url('hal/alur') ?>" class="qcard d-block text-decoration-none">
        <div class="menu-circle" style="background:#007bff;"><span class="emoji-icon">🧭</span></div>
        <small class="menu-label">Tahapan Kunjungan</small>
      </a>
    </div>

    <div class="quickmobilem-item">
      <a href="<?= site_url('hal/panduan') ?>" class="qcard d-block text-decoration-none">
        <div class="menu-circle" style="background:#3498db;"><span class="emoji-icon">📘</span></div>
        <small class="menu-label">Panduan Silaturahmi</small>
      </a>
    </div>

    <div class="quickmobilem-item">
      <a href="<?= site_url('hal/kontak') ?>" class="qcard d-block text-decoration-none">
        <div class="menu-circle" style="background:#25D366;"><span class="emoji-icon">📞</span></div>
        <small class="menu-label">Kontak</small>
      </a>
    </div>

    <div class="quickmobilem-item">
      <a href="<?= site_url('hal/privacy_policy') ?>" class="qcard d-block text-decoration-none">
        <div class="menu-circle" style="background:#16a085;"><span class="emoji-icon">🔒</span></div>
        <small class="menu-label">Kebijakan Privasi</small>
      </a>
    </div>

    <div class="quickmobilem-item">
      <a href="<?= site_url('hal') ?>" class="qcard d-block text-decoration-none">
        <div class="menu-circle" style="background:#6c757d;"><span class="emoji-icon">📄</span></div>
        <small class="menu-label">S&K</small>
      </a>
    </div>
  </div>

<?php endif; ?>
