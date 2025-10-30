<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Menu extends MX_Controller
{
    public function __construct(){
        parent::__construct();
        $this->load->helper('url');
    }

    public function ping(){ echo 'ok'; } // untuk test cepat

    private function is_admin_mode(){
        if (function_exists('user_can_mod')) {
            $mods = [
              'admin_dashboard','admin_dashboard/monitor','admin_scan','admin_permohonan',
              'admin_user','admin_setting_web','admin_unit_tujuan','admin_unit_lain',
              'admin_instansi_ref','admin_pengumuman'
            ];
            foreach ($mods as $m) if (user_can_mod([$m])) return TRUE;
        }
        return FALSE;
    }

    public function quick(){
        // DEBUG: izinkan dulu non-AJAX biar mudah test
        // kalau sudah stabil, boleh aktifkan lagi baris is_ajax_request()
        // if (!$this->input->is_ajax_request()) show_404();

        $mode = strtolower($this->input->get('mode') ?: 'auto');
        if (!in_array($mode, ['admin','front','auto'])) $mode = 'auto';
        if ($mode === 'auto') $mode = $this->is_admin_mode() ? 'admin' : 'front';

        $this->load->view('front_end/menu', ['mode' => $mode]);
    }
}
