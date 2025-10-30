<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_user extends Admin_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model("M_admin_user", "dm");
        $this->load->model("M_admin_user_profil", "cm");
        $this->load->library("email");
        cek_session_akses(get_class($this), $this->session->userdata('admin_session'));
    }

    public function index(){
        $data["controller"] = get_class($this);
        $data["title"]      = "Manajemen User";
        $data["subtitle"]   = $this->om->engine_nama_menu(get_class($this));
        // arr_unit() dari model kini hanya mengembalikan optgroup "Unit Lain"
        $data['arr_unit']   = $this->dm->arr_unit();
        $data["content"]    = $this->load->view($data["controller"]."_view",$data,true);
        $this->render($data);
    }

    // Parser value dropdown -> hanya 'unit_lain'
    private function parse_unit($raw)
    {
        $val = trim(html_entity_decode((string)$raw, ENT_QUOTES, 'UTF-8'));
        if (strpos($val, 'L:') === 0) {
            return ['source' => 'unit_lain', 'id' => (int)substr($val, 2)];
        }
        // jika bukan pola L:<id> anggap invalid
        return ['source' => 'invalid', 'id' => 0];
    }

    public function detail_profil($id){
        $data["controller"] = get_class($this);

        // cek skema agar query aman
        $has_unit_lain = $this->db->field_exists('id_unit_lain', 'users');
        $has_unit_src  = $this->db->field_exists('unit_source',  'users');

        if ($has_unit_lain && $has_unit_src) {
            $this->db->select("
                users.username,
                users.nama_lengkap,
                users.foto,
                users.no_telp,
                users.permission_publish,
                users.email,
                users.tanggal_reg,
                users.level,
                users.blokir,
                CASE 
                  WHEN users.unit_source = 'unit_lain' THEN COALESCE(ul.tugas,'')
                  ELSE '' 
                END AS nama_unit
            ", false);
            $this->db->from("users");
            $this->db->join("unit_lain ul", "ul.id_unit_lain = users.id_unit_lain", "left");
        } elseif ($has_unit_lain) {
            // tanpa unit_source: tetap ambil nama dari unit_lain jika ada
            $this->db->select("
                users.username,
                users.nama_lengkap,
                users.foto,
                users.no_telp,
                users.permission_publish,
                users.email,
                users.tanggal_reg,
                users.level,
                users.blokir,
                COALESCE(ul.tugas,'') AS nama_unit
            ", false);
            $this->db->from("users");
            $this->db->join("unit_lain ul", "ul.id_unit_lain = users.id_unit_lain", "left");
        } else {
            // skema lama tanpa kolom unit_lain
            $this->db->select("
                users.username,
                users.nama_lengkap,
                users.foto,
                users.no_telp,
                users.permission_publish,
                users.email,
                users.tanggal_reg,
                users.level,
                users.blokir,
                '' AS nama_unit
            ", false);
            $this->db->from("users");
        }

        $this->db->where("users.id_session", $id);

        $q = $this->db->get();
        if ($q->num_rows() === 0) {
            show_error("Pengguna tidak ditemukan.", 404, "Not Found");
            return;
        }

        $ret = $q->row();
        $data["record"]   = $ret;
        $data["title"]    = "Manajemen User";
        $data["subtitle"] = ucfirst($ret->level)." ".$ret->nama_lengkap;

        $data["content"] = $this->load->view($data["controller"]."_profil_view", $data, true);
        $this->render($data);
    }

    public function get_data()
    {
        $this->load->model('M_admin_user','m_admin_user');

        $list = $this->m_admin_user->get_data();
        $data = [];
        foreach($list as $res){
            // foto
            if (empty($res->foto)) {
                $foto = '<img src="'.base_url('upload/users/onhacker_221a3f5e.jpg').'" class="rounded-circle avatar-sm" width="50" alt="foto">';
            } else {
                $foto = '<img src="'.base_url('upload/users/'.$res->foto).'" class="rounded-circle avatar-sm" width="50" alt="foto">';
            }

            $row = [];
            $row['cek']          = '<div class="checkbox checkbox-primary checkbox-single"><input type="checkbox" class="data-check" value="'.htmlspecialchars($res->id_session).'"><label></label></div>';
            $row['foto']         = $foto;
            $row['username']     = htmlspecialchars($res->username);
            $row['nama_lengkap'] = htmlspecialchars($res->nama_lengkap ?: '—');
            $row['unit']         = htmlspecialchars($res->nama_unit ?: '—');
            $row['no_telp']      = htmlspecialchars($res->no_telp ?: '—');
            $row['detail_url']   = site_url(strtolower($this->router->class)."/detail_profil/".$res->id_session);

            $data[] = $row;
        }

        $output = [
            "draw"            => (int)$this->input->post('draw'),
            "recordsTotal"    => $this->m_admin_user->count_all(),
            "recordsFiltered" => $this->m_admin_user->count_filtered(),
            "data"            => $data,
        ];
        $this->output->set_content_type('application/json')->set_output(json_encode($output));
    }

    public function add()
    {
        $data = $this->input->post(NULL, TRUE);
        $this->load->library('form_validation');

        // === VALIDASI INPUT (tanpa password) ===
        $this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[6]|max_length[12]|alpha_dash');
        $this->form_validation->set_rules('nama_lengkap', 'Nama Lengkap', 'trim|required');
        $this->form_validation->set_rules('no_telp', 'Nama HP/WA', 'trim|required');

        // id_unit kini wajib format L:<id> (unit_lain)
        $this->form_validation->set_rules('id_unit', 'Unit Lain', [
            'required',
            [
                'valid_unit',
                function ($str) {
                    $CI  =& get_instance();
                    $raw = trim(html_entity_decode((string)$str, ENT_QUOTES, 'UTF-8'));

                    if ($raw === '' || $raw === null) {
                        $CI->form_validation->set_message('valid_unit', '* %s harus dipilih');
                        return FALSE;
                    }

                    // Wajib pola L:<angka>
                    if (strpos($raw, 'L:') === 0) {
                        $id = substr($raw, 2);
                        $ok = (ctype_digit($id) && (int)$id > 0);
                        if (!$ok) $CI->form_validation->set_message('valid_unit', '* %s tidak valid');
                        return $ok;
                    }

                    // selain itu dianggap invalid
                    $CI->form_validation->set_message('valid_unit', '* %s tidak valid');
                    return FALSE;
                }
            ]
        ]);

        $this->form_validation->set_message('required', '* %s harus diisi');
        $this->form_validation->set_message('alpha_dash', '* %s hanya boleh huruf, angka, underscore (_), atau strip (-)');
        $this->form_validation->set_message('min_length', '* %s minimal %s karakter');
        $this->form_validation->set_message('max_length', '* %s maksimal %s karakter');
        $this->form_validation->set_message('matches', '* %s tidak sama');
        $this->form_validation->set_error_delimiters('<br> ', ' ');

        if ($this->form_validation->run() !== TRUE) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>validation_errors()]);
            return;
        }

        if (strlen($data["username"]) < 6) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Username minimal 6 karakter"]);
            return;
        }

        // === id_session unik 10 digit ===
        do {
            $id_session = substr(str_shuffle(str_repeat("0123456789", 10)), 0, 10);
            $exists = $this->db->where('id_session', $id_session)->count_all_results('users');
        } while ($exists > 0);

        // === Cek username duplikat ===
        $dupeUser = $this->db->where("username", $data["username"])->limit(1)->get("users");
        if ($dupeUser->num_rows() > 0) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Username sudah ada"]);
            return;
        }

        // ===== Parse & pastikan unit_lain
        $unitSel = $this->parse_unit($data['id_unit']); // ['source'=>..., 'id'=>int]
        if ($unitSel['source'] !== 'unit_lain' || $unitSel['id'] <= 0) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Unit Lain tidak valid"]);
            return;
        }

        // ===== Siapkan data insert
        $row = [];
        $row["id_session"]   = $id_session;
        $row["nama_lengkap"] = $data["nama_lengkap"];
        $row["no_telp"]      = $data["no_telp"];
        $row["username"]     = $data["username"];
        $row["blokir"]       = "N";
        if ($this->db->field_exists('deleted','users')) {
            $row["deleted"]  = "N"; // agar lolos filter saat login
        }
        $row["attack"]       = md5(date("Ymdhis"));
        $row["valid_reset"]  = "0000-00-00";
        $row["tanggal_reg"]  = date("Y-m-d");
        $row["id_reset"]     = hash("sha512", md5(date("Ymdhis")));

        // ===== Generate password kuat & hash
        $plainPassword   = $this->generate_password(12);
        $algo            = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
        $row["password"] = password_hash($plainPassword, $algo);

        // ===== Mapping kolom unit_lain
        if ($this->db->field_exists('id_unit_lain', 'users')) {
            $row['id_unit_lain'] = $unitSel['id'];
        }
        if ($this->db->field_exists('id_unit', 'users')) {
            $row['id_unit'] = 0; // bersihkan jejak unit_tujuan
        }
        if ($this->db->field_exists('unit_source', 'users')) {
            $row['unit_source'] = 'unit_lain';
        }

        // Ambil nama unit_lain
        $u = $this->db->select('tugas AS nama')
                      ->get_where('unit_lain', ['id_unit_lain' => $unitSel['id']])->row();
        $nama_unit  = $u ? $u->nama : 'Unit Lain';
        $unit_label = 'Unit Lain';

        log_message('debug', 'POST id_unit = '.$this->input->post('id_unit'));

        // ===== Simpan
        $res = $this->db->insert("users", $row);

        if ($res) {
            $web  = $this->om->web_me();
            $site = site_url("on_login");

            $unitInfo = ($unit_label ?: 'Unit Lain').' '.$nama_unit;
            $appName  = $web->nama_website ?: 'Aplikasi';

            // ===== Kirim WhatsApp kredensial
            $pesan =
                "Halo *{$row['nama_lengkap']}*\n\n".
                "Akun *{$appName}* untuk {$unitInfo} berhasil dibuat.\n\n".
                "Login: {$site}\n".
                "Username: *{$row['username']}*\n".
                "Password: *{$plainPassword}*\n\n".
                "_Demi keamanan, segera ganti password setelah login._";

            if (function_exists('send_wa_single') && !empty($row["no_telp"])) {
                @send_wa_single($row["no_telp"], $pesan);
            }

            echo json_encode([
                "success" => true,
                "title"   => "Berhasil",
                "id"      => $row["id_session"],
                "pesan"   => "Akun berhasil dibuat"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "title"   => "Gagal",
                "pesan"   => "Data gagal disimpan"
            ]);
        }
    }

    function generate_password($length = 12) {
        // Hindari karakter yang mudah tertukar (O/0, I/1, l)
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%*?';
        $alphaLen = strlen($alphabet);

        $length = max(8, (int)$length);
        $bytes = random_bytes($length);
        $pwd = '';
        for ($i = 0; $i < $length; $i++) {
            $pwd .= $alphabet[ord($bytes[$i]) % $alphaLen];
        }
        return $pwd;
    }

    public function get_data_modul($id_session)
    {
        $this->load->model('M_admin_user_profil', 'mprof');

        $list = $this->mprof->get_data($id_session);
        $data = [];
        $no   = (int)$this->input->post('start');

        foreach ($list as $r) {
            $no++;

            $isOn = ((int)$r->granted === 1);
            $btn  = $isOn
                ? '<button class="btn btn-sm btn-success" onclick="pub('.(int)$r->id_modul.', \''.$id_session.'\')"><i class="fe-check"></i> Aktif</button>'
                : '<button class="btn btn-sm btn-outline-secondary" onclick="pub('.(int)$r->id_modul.', \''.$id_session.'\')"><i class="fe-plus"></i> Aktifkan</button>';

            $row = [
                'id_modul'   => (int)$r->id_modul,
                'nama_modul' => $r->nama_modul,
                'aksi'       => $btn,
            ];
            $data[] = $row;
        }

        $out = [
            "draw"            => (int)$this->input->post('draw'),
            "recordsTotal"    => $this->mprof->count_all(),
            "recordsFiltered" => $this->mprof->count_filtered($id_session),
            "data"            => $data,
        ];

        $this->output->set_content_type('application/json')->set_output(json_encode($out));
    }

    function resend(){
        $data = $this->db->escape_str($this->input->post());
        $this->load->library('form_validation');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
        $this->form_validation->set_message('required', '* %s Harus diisi ');
        $this->form_validation->set_message('valid_email', '* %s Tidak Valid ');
        $this->form_validation->set_error_delimiters('<br> ', ' ');
        if($this->form_validation->run() == TRUE ) {

            $kode             = hash("sha512", md5(date("YmdHis")));
            $data["id_reset"] = hash("sha512", $kode);
            $datetime         = new DateTime('today');
            $datetime->modify('+3 day');
            $link_valid       = $datetime->format('Y-m-d');
            $data["valid_reset"] = $link_valid;
            $kode_reset       = site_url("kmzwa8awaa/verifikasi_email/".$kode."-".$data["id_reset"]);
            $generate_pass    = md5("YmdHis");
            $data["blokir"]   = "P";
            $data["password"] = hash("sha512", md5($generate_pass));
            $this->db->where("email", $data["email"]);
            $this->db->update("users", $data);

            // penerima
            $this->db->where("email", $data["email"]);
            $this->db->select("id_session, attack, tanggal_reg, email, nama_lengkap")->from("users");
            $cek_user = $this->db->get();
            $em = $cek_user->row();

            // pengirim
            $this->db->where("id_identitas", "1");
            $web = $this->db->get("identitas")->row();

            // body
            $data["title"]      = "Verifikasi Email";
            $data["p1"]         = $web->nama_website ." mengirim verifikasi email untuk turut mengelola website. ";
            $data["p2"]         = "Email verifikasi ini berlaku hingga ". tgl_view($link_valid)." Klik Verifikasi Email ";
            $data["btn"]        = "Verifikasi Email";
            $data["link_reset"] = $kode_reset;
            $data["web"]        = "<a href=".$web->url.">".$web->nama_website."</a>";

            $email   = $em->email;
            $subject = "Verifikasi Email";
            $this->email->from($web->email, $web->nama_website);
            $this->email->to($email);
            $this->email->subject($subject);
            $body = $this->load->view('password/reset_password_mail_template',$data,TRUE);
            $this->email->message($body);
            $this->email->set_mailtype("html");

            $config['protocol'] = 'sendmail';
            $config['mailpath'] = '/usr/sbin/sendmail';
            $config['charset']  = 'utf-8';
            $config['wordwrap'] = TRUE;
            $config['mailtype'] = 'html';
            $res = $this->email->initialize($config) && $this->email->send();

            if($res) {
                $ret = ["success" => true, "title" => "Berhasil", "pesan" => "Email verifikasi akun telah dikirim ke email<br>". $data["email"]];
            } else {
                $ret = ["success" => false, "title" => "Gagal", "pesan" => "Data Gagal disimpan "];
            }

        } else {
            $ret = ["success" => false, "title" => "Gagal", "pesan" => validation_errors()];
        }
        echo json_encode($ret);
    }

    function pub($id,$ses){
        $this->db->where("id_session", $ses);
        $this->db->where("id_modul", $id);
        $cek = $this->db->get("users_modul");

        $this->db->where("id_modul", $id);
        $rul = $this->db->get("modul")->row();

        if ($cek->num_rows() == 0) {
            $data["id_modul"]   = $id;
            $data["id_session"] = $ses;
            $rec = $this->db->insert("users_modul", $data);
            if ($rec) {
                $ret = ["success" => true, "title" => "Akses Dibuka", "pesan" => "Akses ".$rul->nama_modul." <br>Diizinkan"];
            } else {
                $ret = ["success" => false, "title" => "Gagal", "pesan" => "Gagal proses"];
            }
        } else {
            $data["id_modul"]   = $id;
            $data["id_session"] = $ses;
            $res = $this->db->delete("users_modul", $data);
            if ($res) {
                $ret = ["success" => true, "title" => "Akses Ditutup", "pesan" => "Akses ".$rul->nama_modul." <br>tidak diizinkan"];
            } else {
                $ret = ["success" => false, "title" => "Gagal", "pesan" => "Gagal proses"];
            }
        }
        echo json_encode($ret);
    }

    function hapus_data(){
        $list_id = $this->input->post('id'); // berisi id_session[]
        $ret = ["success" => false, "title" => "Gagal", "pesan" => "Tidak ada data"];

        if (is_array($list_id) && count($list_id) > 0) {
            foreach ($list_id as $id) {
                $this->db->where("id_session", $id);
                $res = $this->db->delete("users");
                $ret = $res
                    ? ["success" => true,  "title" => "Berhasil", "pesan" => "Data berhasil dihapus"]
                    : ["success" => false, "title" => "Gagal",    "pesan" => "Data gagal dihapus"];
            }
        }

        echo json_encode($ret);
    }

    function update_setting_profil($id){
        $data  = $this->db->escape_str($this->input->post());
        $this->load->library('form_validation');
        $this->form_validation->set_rules('nama_website','Nama Website','required');
        $this->form_validation->set_rules('url','Domain','required');
        $this->form_validation->set_message('required', '* %s Harus diisi ');
        $this->form_validation->set_error_delimiters('<br> ', ' ');
        if($this->form_validation->run() == false ) {

            $this->db->where("id_session",$id);
            $res  = $this->db->update("users",$data);

            if($res) {
                $ret = ["success" => true, "title" => "Berhasil", "pesan" => "Data berhasil diupdate"];
            } else {
                $ret = ["success" => false, "title" => "Gagal", "pesan" => "Data gagal diupdate"];
            }

        } else {
            $ret = ["success" => false, "title" => "Gagal", "pesan" => validation_errors()];
        }
        echo json_encode($ret);
    }

    function reset_password_user($id){
        $this->db->where("blokir", "N");
        $this->db->where("id_session", $id);
        $this->db->select("id_session, attack, tanggal_reg, no_telp, nama_lengkap")->from("users");
        $cek_user = $this->db->get();
        $em = $cek_user->row();

        // buat kode reset
        $kode          = hash("sha512", md5(date("YmdHis")));
        $x["id_reset"] = hash("sha512", $kode);
        $datetime      = new DateTime('today');
        $datetime->modify('+3 day');
        $link_valid    = $datetime->format('Y-m-d');
        $x["valid_reset"] = $link_valid;
        $kode_reset    = site_url("kmzwa8awaa/reset_password/".$kode."-".$x["id_reset"]);

        // simpan expired & id_reset
        $this->db->where("id_session", $id);
        $this->db->update("users", $x);

        // body pesan WA
        $data["title"]      = "Reset Password";
        $data["p1"]         = "Hai ".$em->nama_lengkap.". Pengguna Aplikasi ".$this->om->web_me()->nama_website.". Jika permintaan ini adalah anda, Silahkan reset password anda";
        $data["p2"]         = "Reset password ini berlaku hingga ". tgl_view($link_valid)." Klik Link Reset Password ";
        $data["btn"]        = "Reset Password";
        $data["link_reset"] = $kode_reset;

        $pesan = $data["title"]."<br>".$data["p1"].".<br>".$data["p2"].".<br>".$data["link_reset"];
        $noti  = "Link Reset Password telah dikirim ke Whatsapp ". $em->no_telp;

        @send_wa_single($em->no_telp,$pesan);

        if($noti) {
            $ret = ["success" => true, "title" => "Berhasil", "type" => "success", "pesan" => $noti];
        } else {
            $ret = ["success" => false, "title" => "Gagal", "type" => "error", "pesan" => "Reset Password Gagal"];
        }
        echo json_encode($ret);
    }

    function reset_password_userx(){
        $data = $this->db->escape_str($this->input->post());
        $this->load->library('form_validation');
        $this->form_validation->set_message('required', '* %s Harus diisi ');
        $this->form_validation->set_error_delimiters('<br> ', ' ');

        if($this->form_validation->run() == TRUE ) {
            $this->db->where("blokir", "N");
            $this->db->where("email", $data["email"]);
            $this->db->select("id_session, attack, tanggal_reg, email, nama_lengkap")->from("users");
            $cek_user = $this->db->get();
            $em = $cek_user->row();

            if ($cek_user->num_rows() == 0) {
                $rules = "Email ini tidak terdaftar. Masukkan email yang sudah terdaftar";
                $res   = false;
            } else {
                $kode          = hash("sha512", md5(date("YmdHis")));
                $x["id_reset"] = hash("sha512", $kode);
                $datetime      = new DateTime('today');
                $datetime->modify('+3 day');
                $link_valid    = $datetime->format('Y-m-d');
                $x["valid_reset"] = $link_valid;
                $kode_reset    = site_url("kmzwa8awaa/reset_password/".$kode."-".$x["id_reset"]);

                $this->db->where("email", $data["email"]);
                $this->db->update("users", $x);

                $this->db->where("id_identitas", "1");
                $web = $this->db->get("identitas")->row();

                $data["title"]      = "Reset Password";
                $data["p1"]         = "Hai ".$em->nama_lengkap.". Jika permintaan ini adalah anda, Silahkan reset password anda";
                $data["p2"]         = "Email reset password ini berlaku hingga ". tgl_view($link_valid)." Klik Reset Password ";
                $data["btn"]        = "Reset Password";
                $data["link_reset"] = $kode_reset;
                $data["web"]        = "<a href=".$web->url.">".$web->nama_website."</a>";

                $email   = $em->email;
                $subject = "Reset Password ".$web->nama_website;
                $this->email->from($web->email, $web->nama_website);
                $this->email->to($email);
                $this->email->subject($subject);
                $body = $this->load->view('password/reset_password_mail_template',$data,TRUE);
                $this->email->message($body);
                $this->email->set_mailtype("html");

                $config['protocol'] = 'sendmail';
                $config['mailpath'] = '/usr/sbin/sendmail';
                $config['charset']  = 'utf-8';
                $config['wordwrap'] = TRUE;
                $config['mailtype'] = 'html';
                $res = $this->email->initialize($config) && $this->email->send();

                $rules = "Link Reset Password telah dikirim ke Email ". $data["email"]." Silahkan cek inbox atau spam";
            }

            if($res) {
                $ret = ["success" => true, "title" => "Berhasil", "type" => "success", "pesan" => $rules];
            } else {
                $ret = ["success" => false, "title" => "Gagal", "type" => "error", "pesan" => "Reset Password Gagal ".$rules];
            }

        } else {
            $ret = ["success"=>false, "title" => "Gagal", "type" => "error", "pesan" => validation_errors()];
        }
        echo json_encode($ret);
    }
}
