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
    <li class="has-submenu <?= ($uri == 'admin_dashboard') ? 'active-menu' : '' ?>">
        <a href="<?= site_url("admin_dashboard") ?>"><i class="fe-activity"></i> Statistik</a>
    </li>
    <li class="has-submenu <?= ($uri == 'admin_permohonan') ? 'active-menu' : '' ?>">
        <a href="<?= site_url("admin_permohonan") ?>"><i class="fe-book-open"></i> Permohonan</a>
    </li>
    <li class="has-submenu <?= ($uri == 'admin_permohonan/all') ? 'active-menu' : '' ?>">
        <a href="<?= site_url("admin_permohonan/all") ?>"><i class="fe-eye"></i> Monitoring</a>
    </li>

    <li class="has-submenu <?= ($uri == 'admin_dusun') ? 'active-menu' : '' ?>">
        <a href="#"><i class="fe-git-commit"></i> Master <div class="arrow-down"></div></a>
        <ul class="submenu">
            <li><a href="<?= site_url("admin_dusun") ?>">Data Dusun</a></li>
        </ul>
    </li>

<?php endif; ?>
