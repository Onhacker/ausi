<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kmzwa8awaa extends MX_Controller {
	function __construct(){
		parent::__construct();
        $this->output->set_header("X-Robots-Tag: noindex, nofollow", true);
        $this->timezone();
        $this->load->helper("front");
        $this->load->model("Front_model", "fm");
        // $this->load->library("email");
        error_reporting(0);
    }

    public function offline() {
        $this->load->view('offline_view');
    }

    function webhook(){
        $this->load->view('webhook');
    }

    public function download($token)
    {
        // $this->load->model('File_model');
        // $file = $this->File_model->getFileByToken($token);
        $this->db->where('token', $token);
            $query = $this->db->get('file_tokens');
            $file = $query->row();

        if (!$file) {
         $data['pesan'] = 'Token tidak valid atau tidak ditemukan.';
         return $this->load->view('er', $data);
        }

        if (strtotime($file->expires_at) < time()) {
            $data['pesan'] = 'Link sudah kadaluarsa.';
            return $this->load->view('er', $data);
        }

        $path = FCPATH . 'assets/file/' . $file->file_name;
        if (!file_exists($path)) {
            $data['pesan'] = 'File tidak ditemukan.';
            return $this->load->view('er', $data);
        }
// Ambil isi file dulu
        $this->load->helper('download');
        $file_content = file_get_contents($path);

    // Hapus token agar tidak bisa dipakai ulang
        $this->db->where('token', $token)->delete('file_tokens');

        // $new_filename = $nama_file. "_".$nama. "_" . date('Ymd_His') . '.' . pathinfo($file->file_name, PATHINFO_EXTENSION);

    // // Kirim file ke user dengan nama baru
        // force_download($new_filename, $file_content);
    // Kirim file ke user
        force_download($file->file_name, $file_content);
    }

    function tes_wa(){
       send_wa_single("082333265888", "Tes kirim pesan"); 
    }



    function laporan(){
        $curl = curl_init();
        $token = "e1L2SpNjYE6R7oaXb8eyqFNkepj4Yqq1sMuFANwy02o6XXvpcjtcI2i";
        $secret_key = "G4g42vHO";
        $page = "";
        $limit = "";
        $message_id = "";
        curl_setopt($curl, CURLOPT_HTTPHEADER,
            array(
                "Authorization: $token.$secret_key",
            )
        );
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL,  "https://deu.wablas.com/api/report-realtime?page=$page&message_id=$message_id&limit=$limit");
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($curl);
        curl_close($curl);
        echo "<pre>";
        print_r($result);
    }

    function timezone(){
        $this->db->where("id_identitas", "1");
        $s = $this->db->get("identitas")->row();
        return date_default_timezone_set($s->waktu);
    }

	function index(){
        echo "string";
	       exit();
        $query = $this->db->select('username')->get('users');
        $users = $query->result_array(); // Menggunakan result_array agar bisa diproses lebih fleksibel

        if (empty($users)) {
            echo "Tidak ada pengguna ditemukan.";
            return;
        }

        // Pastikan jumlah angka acak sesuai dengan jumlah user
        $totalUsers = count($users);
        if ($totalUsers > 9000000000) { // Batasan maksimal angka unik
            echo "Jumlah pengguna terlalu banyak untuk menghasilkan angka unik!";
            return;
        }

        // Buat angka unik acak (10 digit)
        $randomNumbers = array_map(
            function() { return rand(1000000000, 9999999999); }, 
            array_fill(0, $totalUsers, null)
        );

        // Pastikan angka acak tidak ada yang duplikat
        while (count(array_unique($randomNumbers)) < $totalUsers) {
            $randomNumbers = array_map(
                function() { return rand(1000000000, 9999999999); }, 
                array_fill(0, $totalUsers, null)
            );
        }

        // Update database dengan angka unik
        foreach ($users as $index => $user) {
            $this->db->where('username', $user['username'])
                     ->update('users', ['id_session' => $randomNumbers[$index]]);
        }

        echo "id_session berhasil diperbarui!";
        // echo $this->db->last_query();
    
	}

    // function feed(){
    //     $this->load->view(onhacker_view("rss")); 
    // }

    function verifikasi_email_users_web($id_reset = ""){
        $id_reset = explode("-", $id_reset);
        $id_reset = $id_reset[1];

        $this->db->where("id_reset", $id_reset);
        $this->db->select("id_session, attack, valid_reset,email")->from("users_web");
        $res = $this->db->get();
        $rec = $res->row();
        $today = date("Y-m-d");
        $until = $rec->valid_reset;

        $data["rec"] = $this->fm->web_me();

        if ($until < $today) {
                $z["id_reset"] = md5(date("YmdHis"));
                $this->db->where("id_reset", $id_reset);
                $this->db->update("users_web", $z);
                $this->load->view("password/link_expired", $data);
        } elseif ($res->num_rows() == 1) {
            $data["success"] = "Email ".$rec->email." berhasil diverifikasi. Silahkan Lengkapi form dibawah untuk melengkapi data login";
            $this->load->view("password/form_verifikasi_email_user_web", $data);
        }  else {
            $this->load->view("password/link_expired", $data);
        }
       
    }


    function new_user_web($id_reset){
        $data = $this->input->post();
        $this->load->library('form_validation');
        $this->form_validation->set_rules('password_baru','Password Baru','trim|required|min_length[8]'); 
        $this->form_validation->set_rules('password_baru_lagi','Konfirmasi Password ','trim|required|min_length[8]'); 

        $this->form_validation->set_message('required', '* %s Harus diisi ');
        $this->form_validation->set_message('numeric', '* %s Harus angka ');
        $this->form_validation->set_message('valid_email', '* %s Tidak Valid ');
        $this->form_validation->set_message('min_length', '* %s Minimal 8  Digit ');
        $this->form_validation->set_message('max_length', '* %s Maksimal 12 Karakter ');
        $this->form_validation->set_error_delimiters('<br> ', ' ');
        if($this->form_validation->run() == TRUE ) { 
            unset($data["password2"]);
            unset($data["id_session"]);
            unset($data["level"]);
            unset($data["blokir"]);
            unset($data["permission_publish"]);
            unset($data["foto"]);
            unset($data["attack"]);
        

            if ($data["password_baru"] <> $data["password_baru_lagi"]) {
                $rules = "Password Baru dan Konfirmasi Password Tidak Sama<br>";
            }  else {

                $data["password"] = $data["password_baru_lagi"];
                unset($data["password_baru"]);
                unset($data["password_baru_lagi"]);
                unset($data["member"]);
                $data["blokir"] = "N";
                $data["attack"] = md5(date("Ymdhis"));
                $data["valid_reset"] = "0000-00-00";
                $data["tanggal_reg"] = date("Y-m-d");
                $data["password"] = hash("sha512", md5($data["password"]));
                $id_resett = explode("-", $id_reset);
                $data["id_reset"] = hash("sha512", md5(date("Ymdhis")));
                $this->db->where("blokir", "P");
                $this->db->where("deleted","N");
                $this->db->where("id_reset",$id_resett[1]);
                $res  = $this->db->update("users_web",$data);    
          
            }
            
            if($res) {  
                $ret = array("success" => true,
                    "title" => "Berhasil",
                    "pesan" => "Data berhasil disimpan. Silahkan login menggunakan email dan password anda ");
            } else {
                $ret = array("success" => false,
                    "title" => "Gagal",
                    "pesan" => "Data Gagal disimpan<br>".$rules);
            }

        } else {
            $ret = array("success" => false,
                    "title" => "Gagal",
                    "pesan" => validation_errors());
        }
        echo json_encode($ret);
    }

    function reset_password_user_web(){
        $data = $this->db->escape_str($this->input->post());
        $this->load->library('form_validation');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
        $this->form_validation->set_message('required', '* %s Harus diisi ');
        $this->form_validation->set_message('valid_email', '* %s Tidak Valid ');
        $this->form_validation->set_error_delimiters('<br> ', ' ');
        if($this->form_validation->run() == TRUE ) { 
            $this->db->where("blokir", "N");
            $this->db->where("email", $data["email"]);
            $this->db->select("id_session, attack, tanggal_reg, email, nama_lengkap")->from("users_web");
            $cek_user = $this->db->get();
            $em = $cek_user->row();

            if ($cek_user->num_rows() == 0) {
                $rules = "Email ini tidak terdaftar. Masukkan email yang sudah terdaftar";
            } else {
                // buat kode reset dlu bro
                $kode = hash("sha512", md5(date("YmdHis")));
                $x["id_reset"] = hash("sha512", $kode);
                $datetime = new DateTime('today');
                $datetime->modify('+3 day');
                $link_valid = $datetime->format('Y-m-d');
                $x["valid_reset"] = $link_valid;
                $kode_reset = site_url("kmzwa8awaa/reset_password_web/".$kode."-".$x["id_reset"]);
                // insert tgl expired dan id_reset ke database untuk validasi
                $this->db->where("email", $data["email"]);
                $this->db->update("users_web", $x);

                // set pengirim
                $this->db->where("id_identitas", "1");
                $web = $this->db->get("identitas")->row();
                
                // isi body pesan 
                $data["title"] = "Reset Password";
                $data["p1"] = "Hai ".$em->nama_lengkap.". Jika permintaan ini adalah anda, Silahkan reset password anda";
                $data["p2"] = "Email reset password ini berlaku hingga ". tgl_view($link_valid)." Klik Reset Password ";
                $data["btn"] = "Reset Password";
                $data["link_reset"] = $kode_reset;
                $data["web"] = "<a href=".$web->url.">".$web->nama_website."</a>";
                // end of isi body

                $email                  = $em->email;
                $subject                = "Reset Password ".$web->nama_website;
                $this->email->from($web->email, $web->nama_website);
                $this->email->to($email);
                $this->email->cc('');
                $this->email->bcc('');
                $this->email->subject($subject);
                $body = $this->load->view('password/reset_password_mail_template',$data,TRUE);
                $this->email->message($body);  
                $this->email->set_mailtype("html");
                $this->email->send();

                $config['protocol'] = 'sendmail';
                $config['mailpath'] = '/usr/sbin/sendmail';
                $config['charset'] = 'utf-8';
                $config['wordwrap'] = TRUE;
                $config['mailtype'] = 'html';
                $res = $this->email->initialize($config);

                $rules = "Link Reset Password telah dikirim ke Email ". $data["email"]." Silahkan cek inbox atau spam";
            }
            if($res) {    
                $ret = array("success" => true,
                    "title" => "Berhasil",
                    "type" => "success",
                    "pesan" => $rules);
            } else {
                $ret = array("success" => false,
                    "title" => "Gagal",
                    "type" => "error",
                    "pesan" => "Reset Password Gagal ".$rules);
            }

        } else {
            $ret = array("success"=>false,
                    "title" => "Gagal",
                    "type" => "error",
                   "pesan" => validation_errors());

        }
        echo json_encode($ret);
    }


    function reset_password_web($id_reset = ""){
        $id_reset = explode("-", $id_reset);
        $id_reset = $id_reset[1];

        $this->db->where("id_reset", $id_reset);
        $this->db->select("id_session, attack, valid_reset")->from("users_web");
        $res = $this->db->get();
        $rec = $res->row();
        $today = date("Y-m-d");
        $until = $rec->valid_reset;

        $data["rec"] = $this->fm->web_me();

        if ($until < $today) {
                $z["id_reset"] = md5(date("YmdHis"));
                $this->db->where("id_reset", $id_reset);
                $this->db->update("users_web", $z);
                // // rec(get_class($this));
                $this->load->view("password/link_expired", $data);
        } elseif ($res->num_rows() == 1) {
            $this->load->view("password/form_reset_user_web", $data);
        }  else {
            $this->load->view("password/link_expired", $data);
        }
       
    }

    function verifikasi_email($id_reset = ""){
        $id_reset = explode("-", $id_reset);
        $id_reset = $id_reset[1];

        $this->db->where("id_reset", $id_reset);
        $this->db->select("id_session, attack, valid_reset,email")->from("users");
        $res = $this->db->get();
        $rec = $res->row();
        $today = date("Y-m-d");
        $until = $rec->valid_reset;

        $data["rec"] = $this->fm->web_me();

        if ($until < $today) {
                $z["id_reset"] = md5(date("YmdHis"));
                $this->db->where("id_reset", $id_reset);
                $this->db->update("users", $z);
                // // rec(get_class($this));
                $this->load->view("password/link_expired", $data);
        } elseif ($res->num_rows() == 1) {
            $data["success"] = "Email ".$rec->email." berhasil diverifikasi. Silahkan Lengkapi form dibawah untuk melengkapi data login";
            $this->load->view("password/form_verifikas_email", $data);
        }  else {
            $this->load->view("password/link_expired", $data);
        }
       
    }

    function reset_password($id_reset = ""){
        $id_reset = explode("-", $id_reset);
        $id_reset = $id_reset[1];

        $this->db->where("id_reset", $id_reset);
        $this->db->select("id_session, attack, valid_reset")->from("users");
        $res = $this->db->get();
        $rec = $res->row();
        $today = date("Y-m-d");
        $until = $rec->valid_reset;

        $data["rec"] = $this->fm->web_me();

        if ($until < $today) {
                $z["id_reset"] = md5(date("YmdHis"));
                $this->db->where("id_reset", $id_reset);
                $this->db->update("users", $z);
                // // rec(get_class($this));
                $this->load->view("password/link_expired", $data);
        } elseif ($res->num_rows() == 1) {
            $this->load->view("password/form_reset", $data);
        }  else {
            $this->load->view("password/link_expired", $data);
        }
       
    }

    function reset_password_user(){
        $data = $this->db->escape_str($this->input->post());
        $this->load->library('form_validation');
        $this->form_validation->set_rules(
            'no_telp',
            'Nomor Telepon',
            'required|regex_match[/^(08[1-9][0-9]{7,10})$/]',
            [
                'required'     => 'Nomor telepon wajib diisi.',
                'regex_match'  => 'Nomor telepon harus berupa angka, diawali "08", dan panjang 10-13 digit.'
            ]
        );
        $this->form_validation->set_message('required', '* %s Harus diisi ');
        $this->form_validation->set_error_delimiters('<br> ', ' ');
        if($this->form_validation->run() == TRUE ) { 
            $this->db->where("no_telp", $data["no_telp"]);
            $this->db->where("blokir", "N");
            $this->db->select("id_session, attack, tanggal_reg, no_telp, nama_lengkap")->from("users");
            $cek_user = $this->db->get();
            $em = $cek_user->row();


         // buat kode reset dlu bro
            $kode = hash("sha512", md5(date("YmdHis")));
            $x["id_reset"] = hash("sha512", $kode);
            $datetime = new DateTime('today');
            $datetime->modify('+3 day');
            $link_valid = $datetime->format('Y-m-d');
            $x["valid_reset"] = $link_valid;
            $kode_reset = site_url("kmzwa8awaa/reset_password/".$kode."-".$x["id_reset"]);
                    // insert tgl expired dan id_reset ke database untuk validasi
            $this->db->where("no_telp", $data["no_telp"]);
            $this->db->update("users", $x);
        // echo $this->db->last_query();
        // isi body pesan 
            $data["title"] = "Reset Password";
            $data["p1"] = "Hai ".$em->nama_lengkap.". Pengguna Aplikasi ".$this->fm->web_me()->nama_website.". Jika permintaan ini adalah anda, Silahkan reset password anda";
            $data["p2"] = "Reset password ini berlaku hingga ". tgl_view($link_valid).". Klik Link Reset Password ";
            $data["btn"] = "Reset Password";
            $data["link_reset"] = $kode_reset;
        // $data["web"] = "<a href=".$web->url.">".$web->nama_website."</a>";
            $pesan = $data["title"] . "\n" . $data["p1"] . ".\n" . $data["p2"] . ".\n" . $data["link_reset"];
            $noti = "Link Reset Password telah dikirim ke Whatsapp ". $em->no_telp."";
            send_wa_single($em->no_telp,$pesan); 
            if($noti) {    
                $ret = array("success" => true,
                    "title" => "Berhasil",
                    "type" => "success",
                    "pesan" => $noti);
            } else {
                $ret = array("success" => false,
                    "title" => "Gagal",
                    "type" => "error",
                    "pesan" => "Reset Password Gagal ");
            }
        } else {
            $ret = array("success"=>false,
                "title" => "Gagal",
                "type" => "error",
                "pesan" => validation_errors());

        }
        echo json_encode($ret);

    }

    function reset_password_userxxxxx(){
        $data = $this->db->escape_str($this->input->post());
        $this->load->library('form_validation');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
        $this->form_validation->set_message('required', '* %s Harus diisi ');
        $this->form_validation->set_message('valid_email', '* %s Tidak Valid ');
        $this->form_validation->set_error_delimiters('<br> ', ' ');
        if($this->form_validation->run() == TRUE ) { 
            $this->db->where("blokir", "N");
            $this->db->where("email", $data["email"]);
            $this->db->select("id_session, attack, tanggal_reg, email, nama_lengkap")->from("users");
            $cek_user = $this->db->get();
            $em = $cek_user->row();

            if ($cek_user->num_rows() == 0) {
                $rules = "Email ini tidak terdaftar. Masukkan email yang sudah terdaftar";
            } else {
                // buat kode reset dlu bro
                $kode = hash("sha512", md5(date("YmdHis")));
                $x["id_reset"] = hash("sha512", $kode);
                $datetime = new DateTime('today');
                $datetime->modify('+3 day');
                $link_valid = $datetime->format('Y-m-d');
                $x["valid_reset"] = $link_valid;
                $kode_reset = site_url("kmzwa8awaa/reset_password/".$kode."-".$x["id_reset"]);
                // insert tgl expired dan id_reset ke database untuk validasi
                $this->db->where("email", $data["email"]);
                $this->db->update("users", $x);
                // // rec(get_class($this));

                // set pengirim
                $this->db->where("id_identitas", "1");
                $web = $this->db->get("identitas")->row();
                
                // isi body pesan 
                $data["title"] = "Reset Password";
                $data["p1"] = "Hai ".$em->nama_lengkap.". Jika permintaan ini adalah anda, Silahkan reset password anda";
                $data["p2"] = "Email reset password ini berlaku hingga ". tgl_view($link_valid)." Klik Reset Password ";
                $data["btn"] = "Reset Password";
                $data["link_reset"] = $kode_reset;
                $data["web"] = "<a href=".$web->url.">".$web->nama_website."</a>";
                // end of isi body

                $email                  = $em->email;
                $subject                = "Reset Password ".$web->nama_website;
                $this->email->from($web->email, $web->nama_website);
                $this->email->to($email);
                $this->email->cc('');
                $this->email->bcc('');
                $this->email->subject($subject);
                $body = $this->load->view('password/reset_password_mail_template',$data,TRUE);
                $this->email->message($body);  
                $this->email->set_mailtype("html");
                $this->email->send();

                $config['protocol'] = 'sendmail';
                $config['mailpath'] = '/usr/sbin/sendmail';
                $config['charset'] = 'utf-8';
                $config['wordwrap'] = TRUE;
                $config['mailtype'] = 'html';
                $res = $this->email->initialize($config);

                $rules = "Link Reset Password telah dikirim ke Email ". $data["email"]." Silahkan cek inbox atau spam";
            }
            if($res) {    
                $ret = array("success" => true,
                    "title" => "Berhasil",
                    "type" => "success",
                    "pesan" => $rules);
            } else {
                $ret = array("success" => false,
                    "title" => "Gagal",
                    "type" => "error",
                    "pesan" => "Reset Password Gagal ".$rules);
            }

        } else {
            $ret = array("success"=>false,
                    "title" => "Gagal",
                    "type" => "error",
                   "pesan" => validation_errors());

        }
        echo json_encode($ret);
    }


    function new_pass_user_web($id_reset){
        $data = $this->input->post();
        $pass = $data["password_baru_lagi"];
        $this->load->library('form_validation');
        $this->form_validation->set_rules('password_baru','Password Baru','trim|required|min_length[8]'); 
        $this->form_validation->set_rules('password_baru_lagi','Konfirmas Password ','trim|required|min_length[8]'); 
        $this->form_validation->set_message('required', '* %s Harus diisi ');
        $this->form_validation->set_message('min_length', '* %s Minimal 8 karakter ');
        $this->form_validation->set_error_delimiters('<br> ', ' ');
        if($this->form_validation->run() == TRUE ) { 
            unset($data["password"]);
            unset($data["id_session"]);
            unset($data["level"]);
            unset($data["blokir"]);
            unset($data["permission_publish"]);
            unset($data["nama_lengkap"]);
            unset($data["foto"]);
            unset($data["attack"]);
           
            if ($data["password_baru"] <> $data["password_baru_lagi"]) {
                $rules = "Password Baru dan Konfirmasi Password Tidak Sama<br>";
            }  else {
                unset($data["password_baru"]);
                unset($data["password_baru_lagi"]);
               
                $data["attack"] = md5(date("Ymdhis"));
                $data["valid_reset"] = "0000-00-00";

                $data["password"] = hash("sha512", md5($pass));
                $id_resett = explode("-", $id_reset);
                $data["id_reset"] = hash("sha512", md5(date("Ymdhis")));
                $this->db->where("blokir", "N");
                $this->db->where("deleted","N");
                $this->db->where("id_reset",$id_resett[1]);
                $res  = $this->db->update("users_web",$data); 
                // // rec(get_class($this));                 
            }
            
            if($res) {  
                $ret = array("success" => true,
                    "title" => "Berhasil",
                    "pesan" => "Password berhasil diupdate ");
            } else {
                $ret = array("success" => false,
                    "title" => "Gagal",
                    "pesan" => "Password Gagal diupdate<br>");
            }

        } else {
            $ret = array("success" => false,
                    "title" => "Gagal",
                    "pesan" => validation_errors());
        }
        echo json_encode($ret);
    }

    function new_pass($id_reset){
        $data = $this->input->post();
        $pass = $data["password_baru_lagi"];
        $this->load->library('form_validation');
        $this->form_validation->set_rules('password_baru','Password Baru','trim|required|min_length[8]'); 
        $this->form_validation->set_rules('password_baru_lagi','Konfirmas Password ','trim|required|min_length[8]'); 
        $this->form_validation->set_message('required', '* %s Harus diisi ');
        $this->form_validation->set_message('min_length', '* %s Minimal 8 karakter ');
        $this->form_validation->set_error_delimiters('<br> ', ' ');
        if($this->form_validation->run() == TRUE ) { 
            unset($data["password"]);
            unset($data["id_session"]);
            unset($data["level"]);
            unset($data["blokir"]);
            unset($data["permission_publish"]);
            unset($data["nama_lengkap"]);
            unset($data["foto"]);
            unset($data["attack"]);
           
            if ($data["password_baru"] <> $data["password_baru_lagi"]) {
                $rules = "Password Baru dan Konfirmasi Password Tidak Sama<br>";
            }  else {
                unset($data["password_baru"]);
                unset($data["password_baru_lagi"]);
               
                $data["attack"] = md5(date("Ymdhis"));
                $data["valid_reset"] = "0000-00-00";

                $data["password"] = password_hash($pass, PASSWORD_ARGON2ID);
                $id_resett = explode("-", $id_reset);
                $data["id_reset"] = hash("sha512", md5(date("Ymdhis")));
                $this->db->where("blokir", "N");
                $this->db->where("deleted","N");
                $this->db->where("id_reset",$id_resett[1]);
                $res  = $this->db->update("users",$data); 
                // // rec(get_class($this));                 
            }
            
            if($res) {  
                $ret = array("success" => true,
                    "title" => "Berhasil",
                    "pesan" => "Password berhasil diupdate ");
            } else {
                $ret = array("success" => false,
                    "title" => "Gagal",
                    "pesan" => "Password Gagal diupdate<br>");
            }

        } else {
            $ret = array("success" => false,
                    "title" => "Gagal",
                    "pesan" => validation_errors());
        }
        echo json_encode($ret);
    }


    function new_user($id_reset){
        $data = $this->input->post();
        $this->load->library('form_validation');
        $this->form_validation->set_rules('member','Username','trim|required|max_length[12]'); 
        $this->form_validation->set_rules('nama_lengkap','Nama Lengkap','required'); 
        $this->form_validation->set_rules('password_baru','Password Baru','trim|required|min_length[8]'); 
        $this->form_validation->set_rules('password_baru_lagi','Konfirmas Password ','trim|required|min_length[8]'); 

        $this->form_validation->set_message('required', '* %s Harus diisi ');
        $this->form_validation->set_message('numeric', '* %s Harus angka ');
        $this->form_validation->set_message('valid_email', '* %s Tidak Valid ');
        $this->form_validation->set_message('min_length', '* %s Minimal 8  Digit ');
        $this->form_validation->set_message('max_length', '* %s Maksimal 12 Karakter ');
        $this->form_validation->set_error_delimiters('<br> ', ' ');
        if($this->form_validation->run() == TRUE ) { 
            unset($data["password2"]);
            unset($data["id_session"]);
            unset($data["level"]);
            unset($data["blokir"]);
            unset($data["permission_publish"]);
            unset($data["foto"]);
            unset($data["attack"]);
                
            if (strlen($data["member"]) < 6) {
                $ret = array("success" => false,
                    "title" => "Gagal",
                    "pesan" => "Username harus Minimal 5 karakter");
                echo json_encode($ret);
                return false;
            }

            $this->db->where("username", $data["member"]);
            $cek_user = $this->db->get("users");
            if ($cek_user->num_rows() > 0) {
                $ret = array("success" => false,
                    "title" => "Gagal",
                    "pesan" => "Username sudah ada");
                echo json_encode($ret);
                return false;
            }

            if ($data["password_baru"] <> $data["password_baru_lagi"]) {
                $rules = "Password Baru dan Konfirmasi Password Tidak Sama<br>";
            }  else {
                $data["username"] = $data["member"];
                $data["password"] = $data["password_baru_lagi"];
                unset($data["password_baru"]);
                unset($data["password_baru_lagi"]);
                unset($data["member"]);
                $data["blokir"] = "N";
                $data["attack"] = md5(date("Ymdhis"));
                $data["valid_reset"] = "0000-00-00";
                $data["tanggal_reg"] = date("Y-m-d");
                $data["password"] = hash("sha512", md5($data["password"]));
                $id_resett = explode("-", $id_reset);
                $data["id_reset"] = hash("sha512", md5(date("Ymdhis")));
                $this->db->where("blokir", "P");
                $this->db->where("deleted","N");
                $this->db->where("id_reset",$id_resett[1]);
                $res  = $this->db->update("users",$data);    
                // // rec(get_class($this));              
            }
            
            if($res) {  
                $ret = array("success" => true,
                    "title" => "Berhasil",
                    "pesan" => "Data berhasil disimpan ");
            } else {
                $ret = array("success" => false,
                    "title" => "Gagal",
                    "pesan" => "Data Gagal disimpan<br>".$rules);
            }

        } else {
            $ret = array("success" => false,
                    "title" => "Gagal",
                    "pesan" => validation_errors());
        }
        echo json_encode($ret);
    }


   
}
