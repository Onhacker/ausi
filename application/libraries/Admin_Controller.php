<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_Controller extends MX_Controller {

    public function __construct() {
        parent::__construct();
        $this->timezone();

        // Helper & model existing
        $this->load->helper(['cpu','on','cookie']);
        $this->load->model('Ram_model','om');

        // 1) Auto-login dari cookie (jika belum login)
        $this->autologin_from_cookie();

        // 2) Wajib sudah login (fungsi Anda)
        cek_session_on_login();

        // 3) Paksa single-session → cek attack di DB
        $this->enforce_single_session();

        // (opsional) bersihkan token kadaluwarsa (sekali-sekali saja)
        if (mt_rand(1, 20) === 1) { // ~5% request
            $this->cleanup_expired_tokens();
        }
        if ($this->session->userdata('admin_login')) {
        	$this->output
        	->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private')
        	->set_header('Pragma: no-cache')
        	->set_header('Expires: 0')
      ->set_header('X-Auth-Logged-In: 1'); // hint untuk SW/diagnostik

  }


        // Matikan notice di produksi (sesuai style Anda)
        error_reporting(0);
        
    }

    public function render($data){
        // Tetap panggil validasi internal Anda (boleh jadi redundant, aman)
        $this->om->validasiOnLogin();
        $this->load->view('backend/admin_template', $data);
    }

    private function timezone(){
        $s = $this->db->where('id_identitas', '1')->get('identitas')->row();
        return date_default_timezone_set($s ? $s->waktu : 'Asia/Jakarta');
    }

    /**
     * Auto-login dari cookie remember (selector:token)
     * - Verifikasi token
     * - Rotasi token + set cookie baru
     * - (PENTING) Rotasi juga kolom users.attack agar single-session terjamin
     */
    private function autologin_from_cookie(){
        // Sudah login? lewatkan
        if ($this->session->userdata('admin_login')) return;

        $raw = get_cookie('remember', true);
        if (!$raw || strpos($raw, ':') === false) return;

        list($selector, $token) = explode(':', $raw, 2);
        if (!$selector || !$token) return;

        // Ambil token
        $row = $this->db->where('selector', $selector)
                        ->where('expires >=', date('Y-m-d H:i:s'))
                        ->get('auth_tokens')->row();
        if (!$row) {
            delete_cookie('remember');
            return;
        }

        // Verifikasi token
        if (!password_verify($token, $row->validator_hash)) {
            // Token invalid → hapus token & cookie (antisipasi theft/invalid)
            $this->db->delete('auth_tokens', ['selector' => $selector]);
            delete_cookie('remember');
            return;
        }

        // Ambil user
        $u = $this->db->where('username', $row->username)
                      ->where('blokir','N')->where('deleted','N')
                      ->get('users')->row();
        if (!$u) {
            // User tidak valid → bersihkan
            $this->db->delete('auth_tokens', ['selector' => $selector]);
            delete_cookie('remember');
            return;
        }

        // === ROTASI ATTACK (menjamin single-session) ===
        $newAttack = bin2hex(random_bytes(16));
        $this->db->where('username', $u->username)->update('users', ['attack' => $newAttack]);

        // Set session login
        $this->session->sess_regenerate(TRUE); // anti fixation
        $this->session->set_userdata([
            'admin_login'     => true,
            'admin_username'  => $u->username,
            'admin_level'     => $u->level,
            'admin_attack'    => $newAttack,
            'admin_permisson' => $u->permission_publish,
            'id_unit'         => $u->id_unit,
            'admin_session'   => $u->id_session,
            'admin_nama'      => $u->nama_lengkap,
            'admin_foto'      => $u->foto ?? null,
        ]);

        // Rotasi token remember (best practice)
        $newToken  = bin2hex(random_bytes(32));
        $newHash   = password_hash($newToken, PASSWORD_DEFAULT);
        $newExpiry = time() + 60*60*24*365; // 1 tahun

        $this->db->where('selector', $selector)->update('auth_tokens', [
            'validator_hash' => $newHash,
            'expires'        => date('Y-m-d H:i:s', $newExpiry),
        ]);

        set_cookie([
            'name'     => 'remember',
            'value'    => $selector.':'.$newToken,
            'expire'   => $newExpiry - time(),
            'secure'   => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    /**
     * Bandingkan session admin_attack dengan nilai di DB.
     * Jika beda → sesi ini dikeluarkan (sudah ada login baru di perangkat lain).
     */
    private function enforce_single_session(){
        $u = $this->session->userdata('admin_username');
        $a = $this->session->userdata('admin_attack');
        if (!$u || !$a) return;

        $row = $this->db->select('attack')->where('username',$u)->get('users')->row();
        if (!$row || $row->attack !== $a) {
            // Hapus cookie remember + hancurkan session
            $this->logout_silent();
            redirect('on_login/logout?reason=revoked');
            exit;
        }
    }

    private function cleanup_expired_tokens(){
        $this->db->where('expires <', date('Y-m-d H:i:s'))->delete('auth_tokens');
    }

    private function logout_silent(){
        $raw = get_cookie('remember', true);
        if ($raw && strpos($raw, ':') !== false) {
            list($selector,) = explode(':', $raw, 2);
            if ($selector) $this->db->delete('auth_tokens', ['selector' => $selector]);
        }
        delete_cookie('remember');
        $this->session->sess_regenerate(TRUE);
        $this->session->sess_destroy();
    }
}
