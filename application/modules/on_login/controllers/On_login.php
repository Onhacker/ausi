<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class On_login extends MX_Controller {
	function __construct(){
		parent::__construct();
		$this->output->set_header("X-Robots-Tag: noindex, nofollow", true);
		$this->timezone();
		$this->load->helper("front");
		$this->load->model("front_model",'fm');
	}

	function index(){
		if ($this->session->userdata("admin_login") == true) {
			// redirect(site_url("admin_dashboard"));
			redirect('admin_profil/detail_profil?r='.time());
		} else {
			$data["rec"] = $this->fm->web_me();
			$data["kode"] = $this->reload_captcha(true)["kode"];
			$this->load->view('on_login_view',$data);

		}

	}

	function timezone(){
		$this->db->where("id_identitas", "1");
		$s = $this->db->get("identitas")->row();
		return date_default_timezone_set($s->waktu);
	}

	function reload(){

			$url = site_url("admin_profil/detail_profil") . '?t=' . time();
		
		
		redirect($url);
	}

	function write(){
		unlink($this->session->userdata("file"));
		$this->session->unset_userdata("file");
		$t = microtime(true);
		$micro = sprintf("%06d",($t - floor($t)) * 1000000);
		$d = new DateTime( date('Y-m-d H:i:s.'.$micro, $t) );
		$acak = $this->get_client_ip().$d->format("Y-m-d H:i:s.u");
		$acak = substr(md5($acak), 0,15);
		$new_image = $acak.".png";
		$config['image_library']='gd2';
		$config['source_image'] = 'assets/images/qr/cap.png';
		$config['wm_text'] = $this->reload_captcha();
		$config['wm_type'] = 'text';
		$config['wm_font_path'] = 'system/fonts/texb.ttf';
		$config['wm_font_size'] = '14';
		$config['wm_font_color'] = 'F3FF00';
		$config['wm_vrt_alignment'] = 'bottom';
		$config['wm_hor_alignment'] = 'center';
		$config['quality'] = '100%';
		$config['new_image']= 'assets/images/qr/'.$new_image;
		$this->load->library('image_lib',$config);
		$this->image_lib->watermark();
		$path = 'assets/images/qr/';
		$filename =  $path.$new_image;
		$this->session->set_userdata("file",$filename);
		return $new_image;            
	}


	public function api_reload_captcha()
    {
        $captcha = $this->reload_captcha();
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($captcha));
        // exit;
    }

    public function reload_captcha()
    {
    // Contoh generate kode captcha baru (6 karakter random huruf kapital + angka)
    	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    	$length = 6;
    	$new_code = '';
    	for ($i = 0; $i < $length; $i++) {
    		$new_code .= $chars[rand(0, strlen($chars) - 1)];
    	}

    // Simpan kode captcha baru ke session
    	$this->session->set_userdata('captcha', $new_code);

    // Kembalikan kode captcha untuk ditampilkan di frontend
    	return ['kode' => $new_code];
    }


    private function reload_captchax()
    {
        $acak = $this->get_client_ip() . date("yy-m-d H:i:s");
        $acak = md5($acak);
        $serial = substr(preg_replace("/[^0-9]/", '', $acak), 0, 4);
        $this->session->set_userdata("captcha", $serial);
        $terbilang = Terbilang($serial);

        return [
            'status' => true,
            'kode' => $terbilang
        ];
    }

    public function verifikasi_captcha()
    {	
    	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    	$length = 6;
    	$new_code = '';
    	for ($i = 0; $i < $length; $i++) {
    		$new_code .= $chars[rand(0, strlen($chars) - 1)];
    	}
    	$this->session->set_userdata('captcha_verified', $new_code);
    	// manipulasi
    	echo json_encode([
    		"status" => "success",
    		"captcha" => substr(preg_replace("/[^0-9]/", '', md5($new_code . time())), 0, 4),
    	]);
    }

    public function ceklogin()
	{
		$max_attempts = 5;
		$lockout_time = 5 * 60; // 5 menit dalam detik

		$ip_address = $this->input->ip_address();
		$now = time();

		// Ambil data percobaan dari session
		$attempt = $this->session->userdata('login_attempt');
		if (!$attempt) {
			$attempt = [
				'count' => 0,
				'last_time' => $now
			];
		}
		// Reset jika waktu lockout sudah lewat
		if (($now - $attempt['last_time']) > $lockout_time) {
			$attempt['count'] = 0;
			$attempt['last_time'] = $now;
			$this->session->set_userdata('login_attempt', $attempt);
		}

		// Jika sudah melebihi batas
		if ($attempt['count'] >= $max_attempts && ($now - $attempt['last_time']) < $lockout_time) {
			$ret = [
				"success" => false,
				"title" => "Diblokir Sementara",
				"type" => "error",
				"pesan" => "Terlalu banyak percobaan login gagal. Coba lagi dalam 5 menit."
			];
			$this->output->set_content_type('application/json');
			echo json_encode($ret);
			return;
		}


	    $data = $this->input->post();
	    unset($data["password2"]);

	    if (empty($data['member']) || empty($data['kode'])) {
	            $ret = [
		            "success" => false,
		            "title" => "Gagal",
		            "type" => "error",
		            "pesan" => "Harap lengkapi semua kolom login"
			        ];
		        $this->output->set_content_type('application/json');
		        echo json_encode($ret);
		        return;
	    }

	    if (!$this->session->userdata('captcha_verified')) {
	        $ret = [
		            "success" => false,
		            "title" => "Gagal",
		            "type" => "error",
		            "pesan" => "Verifikasi Captcha belum selesai"
			        ];
		        $this->output->set_content_type('application/json');
		        echo json_encode($ret);
		        return;
	    }

	    if ($this->session->userdata('captcha_verified')) {
	    	$this->db->select('*')->from('users');
	    	$this->db->where("blokir", "N");
	    	$this->db->where("deleted", "N");
	    	$this->db->group_start()
	    	->where("username", $data['member'])
	    	->or_where("no_telp", $data['member'])
	    	->group_end();
	    	$res = $this->db->get();
	    	$rows = $res->row();

	    	if ($rows && password_verify($data['kode'], $rows->password)) {
	    		$data_session = [
	    			"admin_login"     => true,
	    			"admin_username"  => $rows->username,
	    			"admin_level"     => $rows->level,
	    			"admin_attack"    => $rows->attack,
	    			"admin_permisson" => $rows->permission_publish,
	    			"id_unit" => $rows->id_unit,
	    			"admin_session"   => $rows->id_session,
					  "admin_nama"      => $rows->nama_lengkap,         // <— tambahan
					  "admin_foto"      => $rows->foto ?? null          // <— tambahan (jika ada)
					];

	        	$this->session->set_userdata($data_session);
	        	$token    = bin2hex(random_bytes(32));
	        	$selector = bin2hex(random_bytes(9));
	        	$hash     = password_hash($token, PASSWORD_DEFAULT);
				$expiry   = time() + 60*60*24*365; // 1 tahun
	            
				$this->db->insert('auth_tokens', [
					'selector'       => $selector,
					'validator_hash' => $hash,
					'username'       => $rows->username,
					'expires'        => date('Y-m-d H:i:s', $expiry),
				]);
				// set cookie: selector:token
				$this->load->helper('cookie');
				set_cookie([
					'name'   => 'remember',
					'value'  => $selector.':'.$token,
					  'expire' => $expiry - time(),   // detik
					  'secure' => !empty($_SERVER['HTTPS']),
					  'httponly' => true,
					  'samesite' => 'Lax',
					]);

	            $this->session->unset_userdata('captcha_verified');
	            $this->session->unset_userdata('login_attempt');
	            $ret = [
	                "success" => true,
	                "pesan" => "Login Berhasil",
	                "operation" => "insert"
	            ];
	        } else {
	        	$attempt['count']++;
	        	$attempt['last_time'] = $now;
	        	$this->session->set_userdata('login_attempt', $attempt);
	        	$this->log_failed_attempt($data['member']);
	        	$this->check_failed_attempts_and_notify($data['member']);
	            $ret = [
	                "success" => false,
	                "title" => "Gagal",
	                "type" => "error",
	                "pesan" => "Login Gagal. Username dan/atau password salah"
	            ];
	        }
	    } else {
	    	$this->log_failed_attempt($data['member']);
	    	$this->check_failed_attempts_and_notify($data['member']);
	        $ret = [
	            "success" => false,
	            "title" => "Gagal",
	            "type" => "error",
	            "pesan" => "Verifikasi Captcha belum selesai"
	        ];
	    }

	    $this->output->set_content_type('application/json');
	    echo json_encode($ret);
	}


	private function log_failed_attempt($username = null) {
		$data = [
			'username' => $username,
			'ip_address' => $this->input->ip_address(),
			'attempt_time' => date('Y-m-d H:i:s')
		];
		$this->db->insert('login_failures', $data);
	}

	private function check_failed_attempts_and_notify($username = null) {
	    $threshold = 5;
	    $interval_minutes = 5;
	    $notification_cooldown = 10 * 60; // 10 menit
	    $time_limit = date('Y-m-d H:i:s', strtotime("-$interval_minutes minutes"));
	    $ip = $this->input->ip_address();

	    // Hitung gagal login dari IP ini
	    $this->db->from('login_failures');
	    $this->db->where('ip_address', $ip);
	    $this->db->where('attempt_time >=', $time_limit);
	    $count = $this->db->count_all_results();

	    if ($count >= $threshold) {
	        $last_notify = $this->session->userdata('last_login_fail_notify_' . $ip);
	        if (!$last_notify || (time() - $last_notify) > $notification_cooldown) {
	            // Ambil waktu terakhir gagal
	            $this->db->select_max('attempt_time');
	            $this->db->where('ip_address', $ip);
	            $this->db->where('attempt_time >=', $time_limit);
	            $query = $this->db->get('login_failures');
	            $last_attempt = $query->row()->attempt_time ?? 'Tidak diketahui';
	            $last_attempt_formatted = date('d-m-Y H:i:s', strtotime($last_attempt));

	            // Lokasi IP via ip-api.com
	            $location_data = @file_get_contents("http://ip-api.com/json/$ip");
	            $location = json_decode($location_data, true);

	            $lat = $location['lat'] ?? null;
	            $lon = $location['lon'] ?? null;
	            $city = $location['city'] ?? 'Tidak diketahui';
	            $region = $location['regionName'] ?? '';
	            $country = $location['country'] ?? '';
	            $lokasi = "$city, $region, $country";

	            // Altitude via open-elevation
	            $altitude = 'Tidak diketahui';
	            if ($lat && $lon) {
	                $elevation_url = "https://api.open-elevation.com/api/v1/lookup?locations=$lat,$lon";
	                $elevation_response = @file_get_contents($elevation_url);
	                $elevation_data = json_decode($elevation_response, true);
	                $altitude = $elevation_data['results'][0]['elevation'] ?? 'Tidak diketahui';
	            }

	            // Susun pesan
	            $subject = "Peringatan: Banyak percobaan login gagal di System Silaturahmi";
	            $message = "🔐 $subject\n\n"
	                . "📌 IP: $ip\n"
	                . "📍 Lokasi: $lokasi\n"
	                . "📈 Koordinat: $lat, $lon\n"
	                . "🗻 Altitude (estimasi): $altitude meter\n"
	                . "📆 Waktu terakhir: $last_attempt_formatted\n"
	                . "❌ Jumlah gagal: $count kali (dalam $interval_minutes menit)\n";

	            if ($username) {
	                $message .= "👤 Username: $username\n";
	            }

	            // Kirim ke WhatsApp
	            send_wa_single('6282333265888', $message);

	            // Simpan waktu notifikasi
	            $this->session->set_userdata('last_login_fail_notify_' . $ip, time());
	        }
	    }
	}



	function get_client_ip() {
		$ipaddress = '';
		if (getenv('HTTP_CLIENT_IP'))
			$ipaddress = getenv('HTTP_CLIENT_IP');
		else if(getenv('HTTP_X_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
		else if(getenv('HTTP_X_FORWARDED'))
			$ipaddress = getenv('HTTP_X_FORWARDED');
		else if(getenv('HTTP_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_FORWARDED_FOR');
		else if(getenv('HTTP_FORWARDED'))
			$ipaddress = getenv('HTTP_FORWARDED');
		else if(getenv('REMOTE_ADDR'))
			$ipaddress = getenv('REMOTE_ADDR');
		else
			$ipaddress = 'IP tidak dikenali';
		return $ipaddress;
	}

	function get_client_ip_2() {
		$ipaddress = '';
		if (isset($_SERVER['HTTP_CLIENT_IP']))
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_X_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		else if(isset($_SERVER['REMOTE_ADDR']))
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		else
			$ipaddress = 'IP tidak dikenali';
		return $ipaddress;
	}


	function get_client_browser() {
		$browser = '';
		if(strpos($_SERVER['HTTP_USER_AGENT'], 'Netscape'))
			$browser = 'Netscape';
		else if (strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox'))
			$browser = 'Firefox';
		else if (strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome'))
			$browser = 'Chrome';
		else if (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera'))
			$browser = 'Opera';
		else if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE'))
			$browser = 'Internet Explorer';
		else if (strpos($_SERVER['HTTP_USER_AGENT'], 'Safari'))
			$browser = 'Safari';
		else
			$browser = 'Other';
		return $browser;
	}

	public function logout()
{
    // ambil username dulu sebelum session dihancurkan
    $username = $this->session->userdata('admin_username');

    // 1) Hapus token remember & cookie
    $this->load->helper('cookie');
    $raw = get_cookie('remember', true); // format: selector:token
    if ($raw && strpos($raw, ':') !== false) {
        list($selector,) = explode(':', $raw, 2);
        if (!empty($selector)) {
            // hapus hanya token yang sedang dipakai
            $this->db->delete('auth_tokens', ['selector' => $selector]);
        }
    }
    // (opsional tapi disarankan): hapus SEMUA token user ini
    if (!empty($username)) {
        $this->db->delete('auth_tokens', ['username' => $username]);
    }
    // hapus cookie remember
    delete_cookie('remember');

    // 2) Bersihkan semua data session
    $this->session->unset_userdata([
        'admin_login',
        'admin_username',
        'admin_permisson',
        'admin_level',
        'admin_session',
        'admin_attack',
        'id_unit',
        'admin_nama',
        'admin_foto',
    ]);

    // 3) Destroy session + hapus cookie sesi (anti sisa-sisa cookie)
    $this->session->sess_regenerate(TRUE);
    $this->session->sess_destroy();

    // pastikan cookie CI dihapus (kadang perlu di beberapa env)
    $sess_cookie = $this->config->item('sess_cookie_name');
    if ($sess_cookie && isset($_COOKIE[$sess_cookie])) {
        setcookie($sess_cookie, '', time() - 3600, '/', '', !empty($_SERVER['HTTPS']), true);
    }

    // 4) Redirect ke halaman login
    redirect(site_url('on_login') . '?t=' . time());
}

	
}
