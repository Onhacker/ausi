<?php $uri = $this->uri->uri_string(); ?>
<style>
    .active-menu > a {
        text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.6);
        font-weight: bold;
    }
 
    .has-submenu a {
        transition: text-shadow 0.3s ease-in-out;
    }

    .has-submenu a:hover {
        text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.6);
        font-weight: bold;
    }
</style>
<?php if ($this->session->userdata("admin_login") == true): ?>
    <?php if ($this->session->userdata("admin_level")) {?>
        <li class="has-submenu <?= (strpos($uri, 'admin_permohonan/detail_paket/paket_u') === 0) ? 'active-menu' : '' ?>">
            <a href="<?= site_url("admin_permohonan/detail_paket/paket_u") ?>"><i class="fe-book-open"></i> Permohonan</a>
        </li>
    <?php } else { ?>
        <li class="has-submenu <?= ($uri == 'admin_dashboard') ? 'active-menu' : '' ?>">
            <a href="<?= site_url("admin_dashboard") ?>"><i class="fe-activity"></i> Statistik</a>
        </li>
        <li class="has-submenu <?= ($uri == 'admin_dashboard/monitor') ? 'active-menu' : '' ?>">
            <a href="<?= site_url("admin_dashboard/monitor") ?>"><i class="fe-book-open"></i> Permohonan</a>
        </li>
        <li class="has-submenu <?= ($uri == 'admin_dashboard/monitor') ? 'active-menu' : '' ?>">
            <a href="<?= site_url("admin_dashboard/monitor") ?>"><i class="fe-eye"></i> asdMonitoring</a>
        </li>
        <?php if ($this->session->userdata("admin_username") == "admin" && $this->session->userdata("admin_level") == "admin"): ?>
        <li class="has-submenu <?= in_array($uri, ['master_permohonan', 'master_syarat', 'admin_user/capil', 'admin_user']) ? 'active-menu' : '' ?>">
            <a href="#"><i class="fe-git-commit"></i> Master <div class="arrow-down"></div></a>
            <ul class="submenu">
                <li><a href="<?= site_url("master_permohonan") ?>">Master Permohonan</a></li>
                <li><a href="<?= site_url("master_syarat") ?>">Master Syarat</a></li>
                <li><a href="<?= site_url("master_inputan") ?>">Master Inputan</a></li>
                <li><a href="<?= site_url("admin_user/capil") ?>">Manajemen User Capil</a></li>
                <li><a href="<?= site_url("admin_user") ?>">Manajemen User Desa</a></li>
            </ul>
        </li>
        <?php else: ?>
            <li class="has-submenu <?= ($uri == 'admin_dusun') ? 'active-menu' : '' ?>">
                <a href="#"><i class="fe-git-commit"></i> Master <div class="arrow-down"></div></a>
                <ul class="submenu">
                    <li><a href="<?= site_url("admin_dusun") ?>">Data Dusun</a></li>
                </ul>
            </li>
        <?php endif; ?>
    <?php } ?>
<?php endif; ?>
