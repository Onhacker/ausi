<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Developer extends MX_Controller {
	function __construct(){
		parent::__construct();
        $this->output->set_header("X-Robots-Tag: noindex, nofollow", true);
	}

	function index(){
        echo "
        * @Engine   CodeIgniter<br>
        * @Database Mysqli<br>
        * @author   Baso Irwan Sakti<br>
        * @license  Onhacker<br>
        * @since    Version 1.0.0<br>
        * @filesource<br>
        ";
    }

    function manifest(){
        header('Content-Type: application/json');
        $manifest = [
            "id" => "/",
            "name" => "AUSI BILLIARD & CAFE",
            "short_name" => "AUSI BILLIARD & CAFE",
            "start_url" => site_url("/?pwa=1"),
            "scope"      => "/",
            "display" => "standalone",
            "background_color"=> "#ffffff",   
            "theme_color"=> "#FF8C00",        
            "icons" => [
                [
                    "src" => site_url("/assets/images/maskable_icon_192.png"),
                    "sizes" => "192x192",
                    "type" => "image/png",
                    "purpose" => "any",
                    "label" => "Icon 192x192"
                ],
                [
                    "src" => site_url("/assets/images/maskable_icon_512.png"),
                    "sizes" => "512x512",
                    "type" => "image/png",
                    "purpose" => "any",
                    "label" => "Icon 512x512"
                ],
            ],
            "description" => " Ausi Billiard & Cafe adalah aplikasi pemesanan makanan & minuman untuk dine-in (scan barcode meja), delivery, take away, serta booking meja billiard — dilengkapi pembayaran digital, notifikasi status, dan riwayat transaksi",
            "developer" => [
                "name" => "PT. MVIN",
                "url" => "https://mediaverse.com"
            ],
            "permissions" => [
                "notifications"
            ],
            "splash_pages" => [
                [
                    "src" => site_url("/assets/images/logo_ausi.png"),
                    "sizes" => "640x1136",
                    "type" => "image/png"
                ]
            ],
            "screenshots" => [
                [
                    "src" => site_url("/assets/images/screenshot_desktop.png"),
                    "sizes" => "1280x720",
                    "type" => "image/png",
                    "form_factor" => "wide"
                ],
                [
                    "src" => site_url("/assets/images/screenshot_mobile.png"),
                    "sizes" => "360x640",
                    "type" => "image/png"
                ]
            ]
        ];

        echo json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    }

    public function service_worker()
    {
        header('Content-Type: application/javascript');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Service-Worker-Allowed: ' . rtrim(parse_url(site_url(), PHP_URL_PATH), '/') . '/');
        $this->load->view('sw_view'); // view yang berisi script sw.js
    }


}
