<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->timezone(); // set zona waktu dari DB (fallback Asia/Makassar)
        // $this->load->helper(['front', 'download']);
        $this->load->model('front_model', 'fm');
    }

    /**
     * Halaman utama
     */
    public function index()
    {
        $data['rec']       = $this->fm->web_me();
        $data['title']     = 'Home';
        $data['deskripsi'] = $data["rec"]->nama_website." adalah aplikasi pemesanan makanan & minuman untuk dine-in (scan barcode meja), delivery, take away, serta booking meja billiard — dilengkapi pembayaran digital, notifikasi status, dan riwayat transaksi.';";
        $data['prev']      = base_url('assets/images/home.webp');
        $this->load->view('home_view', $data);
    }

    private function timezone()
    {
        $tz = 'Asia/Makassar';
        $row = $this->db->where('id_identitas', '1')->get('identitas')->row();
        if ($row && !empty($row->waktu)) {
            $tz = $row->waktu;
        }
        date_default_timezone_set($tz);
    }

    /**
     * Reload ke dashboard admin
     */
    public function reload()
    {
        redirect(site_url('admin_dashboard'));
    }
}
