<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_monitor extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_monitor_screen', 'mon');
        cek_session_akses(get_class($this), $this->session->userdata('admin_session'));
    }

    /**
     * JSON untuk widget di dashboard:
     * status TV Billiard (online/offline + last_seen + idle_sec).
     */
    public function status_json()
    {
        $this->output
             ->set_content_type('application/json', 'utf-8')
             ->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

        // 60 detik: kalau >60 detik tidak ping, dianggap offline
        $rows = $this->mon->list_status('billiard', 60);

        $data = [
            'ok'          => true,
            'has_data'    => false,
            'is_online'   => false,
            'idle_sec'    => null,
            'last_seen'   => null,
            'nama'        => 'TV Billiard',
            'last_ip'     => null,
            'ua_raw'      => null,
            'ua_browser'  => null,
            'ua_platform' => null,
            'ip_location' => null,   // <<< TAMBAHAN
            // 'ip_location' => null, // opsional, kalau nanti mau geolokasi
        ];

        if (!empty($rows)) {
            // ambil record terbaru
            $r = $rows[0];

            $data['has_data']  = true;
            $data['is_online'] = (bool)$r->is_online;
            $data['idle_sec']  = (int)$r->idle_sec;
            $data['last_seen'] = $r->last_seen;
            $data['nama']      = $r->nama ?: 'TV Billiard';
            $data['last_ip']     = $r->last_ip ?: null;
            $data['ua_raw']      = $r->user_agent ?: null;
            $data['ip_location'] = $r->ip_location ?: null;


            // ====== parse user agent secara sederhana ======
            $ua = $r->user_agent ?: '';

            // Browser
            $browser = 'Tidak diketahui';
            if (stripos($ua, 'OPR/') !== false || stripos($ua, 'Opera') !== false) {
                $browser = 'Opera';
            } elseif (stripos($ua, 'Edg/') !== false) {
                $browser = 'Microsoft Edge';
            } elseif (stripos($ua, 'Chrome') !== false && stripos($ua, 'Chromium') === false) {
                $browser = 'Google Chrome';
            } elseif (stripos($ua, 'Safari') !== false && stripos($ua, 'Chrome') === false) {
                $browser = 'Safari';
            } elseif (stripos($ua, 'Firefox') !== false) {
                $browser = 'Mozilla Firefox';
            } elseif (stripos($ua, 'MSIE') !== false || stripos($ua, 'Trident/') !== false) {
                $browser = 'Internet Explorer';
            }

            // Platform / OS
            $platform = null;
            if (stripos($ua, 'Windows') !== false) {
                $platform = 'Windows';
            } elseif (stripos($ua, 'Mac OS X') !== false || stripos($ua, 'Macintosh') !== false) {
                $platform = 'macOS';
            } elseif (stripos($ua, 'Android') !== false) {
                $platform = 'Android';
            } elseif (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) {
                $platform = 'iOS';
            } elseif (stripos($ua, 'Linux') !== false) {
                $platform = 'Linux';
            }

            $data['ua_browser']  = $browser;
            $data['ua_platform'] = $platform;
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }


    // opsional: page tabel status monitor (yang sebelumnya kita bahas)
    public function index()
    {
        $rows = $this->mon->list_status('billiard', 60);

        $data['controller'] = get_class($this);
        $data['title']      = 'Status Monitor';
        $data['subtitle']   = 'TV / Screen Live Billiard';
        $data['rows']       = $rows;
        $data['content']    = $this->load->view('monitor_status_view', $data, true);
        $this->render($data);
    }
}
