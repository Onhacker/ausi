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
    <li class="has-submenu <?= (strpos($uri, 'admin_permohonan/detail_paket/paket_u') === 0) ? 'active-menu' : '' ?>">
        <a href="<?= site_url("admin_permohonan/detail_paket/paket_u") ?>"><i class="fe-book-open"></i> Permohonan</a>
    </li>
<?php endif; ?>
