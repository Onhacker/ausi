<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends MX_Controller {
	function __construct(){
        parent::__construct();

        // Mencegah halaman ini diindeks oleh mesin pencari
        $this->output->set_header("X-Robots-Tag: noindex, nofollow", true);
    }


    public function get_menu_desktop()
{
    $this->load->library('user_agent');
    $this->load->helper('menu');

    $is_logged = (bool)$this->session->userdata("admin_login");
    $level     = (string)$this->session->userdata('admin_level');
    $username  = (string)$this->session->userdata('admin_username');

    // ====== Definisi menu (rapi) ======
$MENU_DEF = [
    [
        'label'   => 'Statistik',
        'url'     => site_url('admin_laporan/chart'),
        'icon'    => 'mdi mdi-chart-areaspline',
        'require' => ['Statistik','dashboard','admin_laporan/chart'],
    ],
    // [
    //     'label'   => 'Monitoring',
    //     'url'     => site_url('admin_dashboard/monitor'),
    //     'icon'    => 'mdi mdi-monitor-eye',
    //     'require' => ['Monitoring','admin_dashboard/monitor','admin_monitor'],
    // ],
    [
        'label'   => 'Produk',
        'url'     => site_url('admin_produk'),
        'icon'    => 'mdi mdi-package-variant-closed',
        'require' => ['Produk','admin_produk'],
    ],
];

// ====== POS ======
$MENU_DEF[] = [
    'label'   => 'POS',
    'icon'    => 'mdi mdi-cash-register',
    'children'=> [
        [
            'label'   => 'POS Caffe',
            'url'     => site_url('admin_pos'),
            'icon'    => 'mdi mdi-coffee-outline',
            'require' => ['POS Caffe','admin_pos','user'],
        ],
        [
            'label'   => 'POS Billiard',
            'url'     => site_url('admin_billiard'),
            'icon'    => 'mdi mdi-billiards',
            'require' => ['POS Billiard','admin_billiard','user'],
        ],
        [
            'label'   => 'Pengeluaran',
            'url'     => site_url('admin_pengeluaran'),
            'icon'    => 'mdi mdi-cash-minus',
            'require' => ['Pengeluaran','admin_pengeluaran','user'],
        ],
    ],
];

// ====== Riwayat Transaksi ======
$MENU_DEF[] = [
    'label'   => 'Riwayat Transaksi',
    'icon'    => 'mdi mdi-history',
    'children'=> [
        [
            'label'   => 'Caffe',
            'url'     => site_url('admin_pos_riwayat'),
            'icon'    => 'mdi mdi-coffee',
            'require' => ['Caffe','admin_pos_riwayat','user'],
        ],
        [
            'label'   => 'Billiard',
            'url'     => site_url('admin_riwayat_billiard'),
            'icon'    => 'mdi mdi-billiards',
            'require' => ['Billiard','admin_riwayat_billiard','user'],
        ],
    ],
];

// ====== Laporan (disisipkan di sini) ======
// ====== Laporan (single item) ======
$MENU_DEF[] = [
    'label'   => 'Laporan',
    'url'     => site_url('admin_laporan'),
    'icon'    => 'mdi mdi-file-chart',
    'require' => ['Laporan','admin_laporan','admin_laporan/index'],
];


// ====== Master (hanya muncul jika user punya akses child-nya) ======
$MENU_DEF[] = [
    'label'   => 'Master',
    'icon'    => 'mdi mdi-cog-outline',
    'children'=> [
        [
            'label'   => 'Setting System',
            'url'     => site_url('admin_setting_web'),
            'icon'    => 'mdi mdi-cog',
            'require' => ['Setting System','admin_setting_web','user'],
        ],
        [
            'label'   => 'Meja Cafe',
            'url'     => site_url('admin_meja'),
            'icon'    => 'mdi mdi-table-chair',
            'require' => ['Meja','admin_meja'],
        ],
        [
            'label'   => 'Meja Billiard',
            'url'     => site_url('admin_meja_billiard'),
            'icon'    => 'mdi mdi-table-chair',
            'require' => ['Meja Billiard','admin_meja_billiard','user'],
        ],
        [
            'label'   => 'Kurir',
            'url'     => site_url('admin_kurir'),
            'icon'    => 'mdi mdi-table-chair',
            'require' => ['Kurir','admin_kurir','user'],
        ],
        [
            'label'   => 'Kategori Produk',
            'url'     => site_url('admin_kategori_produk'),
            'icon'    => 'mdi mdi-tag-multiple-outline',
            'require' => ['Kategori','admin_kategori_produk','user'],
        ],
        [
            'label'   => 'Unit Lain',
            'url'     => site_url('admin_unit_lain'),
            'icon'    => 'mdi mdi-domain-plus',
            'require' => ['Unit Lain','admin_unit_lain','user'],
        ],
        
        [
            'label'   => 'Pengumuman',
            'url'     => site_url('admin_pengumuman'),
            'icon'    => 'mdi mdi-bullhorn-outline',
            'require' => ['Pengumuman','admin_pengumuman','user'],
        ],
        
        [
            'label'   => 'Manajemen User',
            'url'     => site_url('admin_user'),
            'icon'    => 'mdi mdi-account-cog-outline',
            'require' => ['Manajemen User','admin_user','user'],
        ],
    ],
];

   
    // ====== Header dasar ======
    $this->output->set_content_type('application/json');

    // ====== Belum login → no cache & empty ======
    if (!$is_logged) {
        $this->output
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private')
            ->set_header('Pragma: no-cache')
            ->set_header('Expires: 0')
            ->set_header('Vary: Cookie');

        echo json_encode(["success" => false, "menu" => ""]);
        return;
    }

    // ====== Build menu (otomatis terfilter oleh akses di build_menu) ======
    $html = build_menu($MENU_DEF, [
        'li_has_child_class' => 'has-submenu',
        'li_active_class'    => 'active-menu',
        'child_ul_class'     => 'submenu',
    ]);

    // ====== ETag sensitif user & akses ======
    // Hash-kan juga "allowed set" agar ETag berubah saat hak akses berubah
    $allowed = allowed_module_slugs();
    $allowed_sig = is_array($allowed) ? md5(json_encode(array_keys($allowed))) : (string)$allowed;

    $signature = $username.'|'.$level.'|'.md5(json_encode($MENU_DEF)).'|'.$allowed_sig.'|'.md5($html);
    $etag = 'W/"menu-'.substr(sha1($signature), 0, 20).'"';

    // 304 handling
    $ifNoneMatch = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : '';
    if ($ifNoneMatch === $etag) {
        $this->output
            ->set_status_header(304)
            ->set_header('ETag: '.$etag)
            ->set_header('Cache-Control: private, max-age=900, stale-while-revalidate=600')
            ->set_header('Vary: Cookie');
        return;
    }

    // Header cache untuk respons 200
    $this->output
        ->set_header('ETag: '.$etag)
        ->set_header('Cache-Control: private, max-age=900, stale-while-revalidate=600') // 15 menit, SWR 10 menit
        ->set_header('Vary: Cookie')
        ->set_header('X-Menu-Version: '.$etag);

    echo json_encode([
        "success" => true,
        "menu"    => $html
    ]);
}
public function get_menu_mobile()
{
    $this->load->helper('menu'); // butuh user_can_mod(), allowed_module_slugs()
    $is_logged = (bool)$this->session->userdata("admin_login");
    $level     = (string)$this->session->userdata('admin_level');
    $username  = (string)$this->session->userdata('admin_username');

    $this->output->set_content_type('application/json');

    if (!$is_logged) {
        $this->output
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private')
            ->set_header('Pragma: no-cache')
            ->set_header('Expires: 0')
            ->set_header('Vary: Cookie');
        echo json_encode(["success"=>false, "actions"=>[]]);
        return;
    }

    // Definisi quick actions (mobile)
    $ACTIONS_DEF = [
    // ===== Dashboard =====
    [
        'id'      => 'admin_dashboard',
        'label'   => 'Statistik',
        'url'     => site_url('admin_dashboard'),
        'icon'    => 'mdi mdi-chart-areaspline',
        'require' => ['admin_dashboard'],
    ],
    [
        'id'      => 'admin_dashboard/monitor',
        'label'   => 'Monitoring',
        'url'     => site_url('admin_dashboard/monitor'),
        'icon'    => 'mdi mdi-monitor-eye',
        'require' => ['admin_dashboard/monitor'],
    ],

    // ===== Produk =====
    [
        'id'      => 'admin_produk',
        'label'   => 'Produk',
        'url'     => site_url('admin_produk'),
        'icon'    => 'mdi mdi-package-variant-closed',
        'require' => ['admin_produk'],
    ],

    // ===== POS =====
    [
        'id'      => 'admin_pos',
        'label'   => 'POS Caffe',
        'url'     => site_url('admin_pos'),
        'icon'    => 'mdi mdi-coffee-outline',
        'require' => ['admin_pos'],
    ],
    [
        'id'      => 'admin_billiard',
        'label'   => 'POS Billiard',
        'url'     => site_url('admin_billiard'),
        'icon'    => 'mdi mdi-billiards',
        'require' => ['admin_billiard'],
    ],
    [
        'id'      => 'admin_pengeluaran',
        'label'   => 'Pengeluaran',
        'url'     => site_url('admin_pengeluaran'),
        'icon'    => 'mdi mdi-cash-minus',
        'require' => ['admin_pengeluaran'],
    ],

    // ===== Riwayat =====
    [
        'id'      => 'admin_pos_riwayat',
        'label'   => 'Riwayat Caffe',
        'url'     => site_url('admin_pos_riwayat'),
        'icon'    => 'mdi mdi-history',
        'require' => ['admin_pos_riwayat'],
    ],
    [
        'id'      => 'admin_riwayat_billiard',
        'label'   => 'Riwayat Billiard',
        'url'     => site_url('admin_riwayat_billiard'),
        'icon'    => 'mdi mdi-history',
        'require' => ['admin_riwayat_billiard'],
    ],

    // ===== Laporan =====
    [
        'id'      => 'admin_laporan',
        'label'   => 'Laporan',
        'url'     => site_url('admin_laporan'),
        'icon'    => 'mdi mdi-file-chart',
        'require' => ['admin_laporan','admin_laporan/index'],
    ],

    // ===== Master / Pengaturan =====
    [
        'id'      => 'admin_user',
        'label'   => 'Manajemen User',
        'url'     => site_url('admin_user'),
        'icon'    => 'mdi mdi-account-cog',
        'require' => ['admin_user'],
    ],
    [
        'id'      => 'admin_setting_web',
        'label'   => 'Pengaturan Sistem',
        'url'     => site_url('admin_setting_web'),
        'icon'    => 'mdi mdi-cog-outline',
        'require' => ['admin_setting_web'],
    ],
    [
        'id'      => 'admin_kategori_produk',
        'label'   => 'Kategori',
        'url'     => site_url('admin_kategori_produk'),
        'icon'    => 'mdi mdi-tag-multiple-outline',
        'require' => ['admin_kategori_produk'],
    ],
    [
        'id'      => 'admin_unit_tujuan',
        'label'   => 'Unit Tujuan',
        'url'     => site_url('admin_unit_tujuan'),
        'icon'    => 'mdi mdi-domain',
        'require' => ['admin_unit_tujuan'],
    ],
    [
        'id'      => 'admin_unit_lain',
        'label'   => 'Unit Lain',
        'url'     => site_url('admin_unit_lain'),
        'icon'    => 'mdi mdi-domain-plus',
        'require' => ['admin_unit_lain'],
    ],
    [
        'id'      => 'admin_instansi_ref',
        'label'   => 'Instansi Asal',
        'url'     => site_url('admin_instansi_ref'),
        'icon'    => 'mdi mdi-domain',
        'require' => ['admin_instansi_ref'],
    ],
    [
        'id'      => 'admin_pengumuman',
        'label'   => 'Pengumuman',
        'url'     => site_url('admin_pengumuman'),
        'icon'    => 'mdi mdi-bullhorn-outline',
        'require' => ['admin_pengumuman'],
    ],
    [
        'id'      => 'admin_meja',
        'label'   => 'Meja',
        'url'     => site_url('admin_meja'),
        'icon'    => 'mdi mdi-table-chair',
        'require' => ['admin_meja'],
    ],

    // ===== Modul tambahan yang sudah ada di actions awal =====
    [
        'id'      => 'admin_scan',
        'label'   => 'Checkin/Checkout',
        'url'     => site_url('admin_scan'),
        'icon'    => 'mdi mdi-qrcode-scan',
        'require' => ['admin_scan'],
    ],
    [
        'id'      => 'admin_permohonan',
        'label'   => 'Data',
        'url'     => site_url('admin_permohonan'),
        'icon'    => 'mdi mdi-file-document-outline',
        'require' => ['admin_permohonan'],
    ],
];



    // Filter berdasarkan hak akses
    $allowed_actions = [];
    foreach ($ACTIONS_DEF as $a) {
        if (!isset($a['require']) || user_can_mod($a['require'])) {
            $allowed_actions[] = [
                'id'    => $a['id'],
                'label' => $a['label'],
                'url'   => $a['url'],
                'icon'  => $a['icon']
            ];
        }
    }

    // ETag sensitif user & izin
    $allowed = allowed_module_slugs();
    $allowed_sig = is_array($allowed) ? md5(json_encode(array_keys($allowed))) : (string)$allowed;
    $payload_sig = md5(json_encode($allowed_actions));
    $signature   = $username.'|'.$level.'|'.$allowed_sig.'|'.$payload_sig;
    $etag = 'W/"mobile-'.substr(sha1($signature), 0, 20).'"';

    $ifNoneMatch = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : '';
    if ($ifNoneMatch === $etag) {
        $this->output
            ->set_status_header(304)
            ->set_header('ETag: '.$etag)
            ->set_header('Cache-Control: private, max-age=900, stale-while-revalidate=600')
            ->set_header('Vary: Cookie');
        return;
    }

    $this->output
        ->set_header('ETag: '.$etag)
        ->set_header('Cache-Control: private, max-age=900, stale-while-revalidate=600')
        ->set_header('Vary: Cookie');

    echo json_encode([
        "success" => true,
        "actions" => $allowed_actions
    ]);
}



	public function check_login()
    {
        $is_admin = false;
        if ($this->session->userdata('admin_login') === true) {
            $is_admin = true;
        }

        session_write_close(); // tutup session agar tidak blocking

        // sleep(5);

        $response = ['is_admin' => $is_admin];
        echo json_encode($response);
    }

    public function get_link_permohonan()
    {
        $is_admin = false;
        if ($this->session->userdata('admin_login') === true) {
            $is_admin = true;
        }

        session_write_close(); 
        $this->load->helper('url');
        $rec = (object)[
        'gambar' => 'permohonan.png' // bisa juga ambil dari DB atau parameter
    ];

    $uri = $this->uri->segment(1);

    ob_start();
    // if ($this->session->userdata("admin_login") == true) { 
        ?>
        
     <?php ?>
        <a href="<?= base_url('booking') ?>"
         class="center-button <?= ($uri == 'booking') ? 'text-white' : '' ?>"
         style="text-align: center; <?= ($uri == 'booking') ? 'background-color: #28a745;' : '' ?>">
         <img src="<?= base_url('assets/images/logo.png') ?>" alt="Permohonan"
         style="width: 45px; height: 45px; object-fit: contain; margin-top: 0px;">
     </a>
 <!-- } -->

    <?php

    $html = ob_get_clean();
    echo $html;
    }

    public function ajax_status_user()
{
    // Ambil session (lakukan sebelum session_write_close)
    $is_login  = ($this->session->userdata('admin_login') === true);
    $username  = (string)$this->session->userdata('admin_username');
    $nama_ses  = (string)$this->session->userdata('admin_nama');   // dari $data_session baru
    $foto_ses  = (string)$this->session->userdata('admin_foto');   // dari $data_session baru

    // Hindari deadlock saat render view
    session_write_close();

    if (!$is_login) {
        // Item login (LI saja, tanpa <ul>)
        $login_html = '
        <li class="dropdown notification-list">
          <a class="nav-link nav-user mr-0 waves-effect"
             href="'.site_url("on_login").'">
            <span class="d-flex align-items-center">
              <i class="fas fa-user-circle mr-1" style="font-size:28px;color:green;"></i>
              <span class="pro-user-name">Login</span>
            </span>
          </a>
        </li>';

        echo json_encode([
            'logged_in' => false,
            'html'      => $login_html
        ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        return;
    }

    // Nama & foto dari session (fallback aman)
    $display_name = $nama_ses !== '' ? $nama_ses : $username;
    $foto_url     = base_url('upload/users/onhacker_221a3f5e.jpg');
    if (!empty($foto_ses)) {
        // Jika sudah URL penuh, pakai langsung; kalau cuma filename, prepend base_url
        if (filter_var($foto_ses, FILTER_VALIDATE_URL)) {
            $foto_url = $foto_ses;
        } else {
            $foto_url = base_url('upload/users/'.$foto_ses);
        }
    }

    // Notifikasi (pastikan view mengembalikan <li>...</li> TANPA <ul>)
    $notif_html = $this->load->view('backend/notif', [], true);

    // Dropdown user (LI saja)
    $user_html = '
    <li class="dropdown notification-list">
      <a class="nav-link dropdown-toggle nav-user mr-0 waves-effect"
         href="#" role="button"
         data-toggle="dropdown" data-bs-toggle="dropdown"
         aria-haspopup="true" aria-expanded="false">
        <span class="d-flex align-items-center">
          <img src="'.htmlspecialchars($foto_url, ENT_QUOTES, 'UTF-8').'"
               alt="user" class="rounded-circle mr-1" height="28">
          <span class="pro-user-name">'.htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8').'</span>
        </span>
      </a>
      <div class="dropdown-menu dropdown-menu-right profile-dropdown">
        <a href="'.site_url('admin_dashboard').'" class="dropdown-item">
          <i class="fe-activity"></i> Dashboard
        </a>
        <a href="'.site_url('admin_user').'" class="dropdown-item">
          <i class="fe-user"></i> Profil
        </a>
        <div class="dropdown-divider"></div>
        <a href="'.site_url('on_login/logout').'" class="dropdown-item text-danger">
          <i class="fe-log-out"></i> Keluar
        </a>
      </div>
    </li>';

    echo json_encode([
        'logged_in' => true,
        // gabungkan notif (<li>...</li>) + user dropdown
        'html'      => (string)$notif_html . $user_html
    ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}

public function status()
  {
    $logged = (bool)$this->session->userdata('admin_login');
    $resp = [
      'logged_in' => $logged,
      'name'      => $logged ? ($this->session->userdata('admin_nama') ?: 'Admin') : null,
      'dashboard' => $logged ? site_url('admin_profil/detail_profil') : null,
    ];

    // JANGAN dicache
    $this->output
      ->set_content_type('application/json')
      ->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0')
      ->set_header('Pragma: no-cache')
      ->set_header('Expires: 0')
      ->set_output(json_encode(['success'=>true,'data'=>$resp]));
  }

}