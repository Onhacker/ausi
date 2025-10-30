<?php $uri = trim($this->uri->uri_string(), '/'); ?>
<ul class="navigation-menu">
    <li class="has-submenu <?= ($uri === 'booking') ? 'active-menu' : '' ?>">
        <a href="<?= site_url('booking'); ?>"><i class="fe-calendar"></i> Booking</a>
    </li>
    <li class="has-submenu <?= ($uri === 'hal/jadwal') ? 'active-menu' : '' ?>">
        <a href="<?= site_url('hal/jadwal'); ?>"><i class="fe-calendar"></i> Jadwal Kunjungan</a>
    </li>
    <li class="has-submenu <?= ($uri === 'hal/struktur') ? 'active-menu' : '' ?>">
        <a href="<?= site_url('hal/struktur'); ?>"><i class="fe-users"></i> Struktur Organisasi</a>
    </li>
    <li class="has-submenu <?= ($uri === 'hal/alur') ? 'active-menu' : '' ?>">
        <a href="<?= site_url('hal/alur'); ?>"><i class="fe-git-branch"></i> Alur Kunjungan</a>
    </li>
    <li class="has-submenu <?= ($uri === 'hal/kontak') ? 'active-menu' : '' ?>">
        <a href="<?= site_url('hal/kontak'); ?>"><i class="fe-phone-call"></i> Kontak</a>
    </li>
</ul>
