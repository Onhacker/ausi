<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_profil extends Admin_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->library('email');
    }

    /** Helper: ambil 1 baris profil + nama unit (dukung 2 sumber) */
    private function _profil_row()
{
    $username = $this->session->userdata('admin_username');
    if (!$username) return null;

    $this->db->from('users u');

    // Cek keberadaan skema dinamis
    $has_unit_tujuan = $this->db->table_exists('unit_tujuan');
    $has_unit_lain   = $this->db->field_exists('id_unit_lain', 'users');
    $has_source      = $this->db->field_exists('unit_source',  'users');

    // Join yang tersedia saja
    if ($has_unit_tujuan) {
        $this->db->join('unit_tujuan ut', 'ut.id = u.id_unit', 'left');
    }
    if ($has_unit_lain) {
        $this->db->join('unit_lain ul', 'ul.id_unit_lain = u.id_unit_lain', 'left');
    }

    // Ekspresi nama_unit tanpa ketergantungan wajib ke unit_tujuan
    if ($has_unit_lain && $has_source) {
        $expr_nama_unit = $has_unit_tujuan
            ? "CASE WHEN u.unit_source='unit_lain' THEN ul.tugas ELSE ut.nama_unit END"
            : "CASE WHEN u.unit_source='unit_lain' THEN ul.tugas ELSE '' END";
    } else {
        // Skema lama: id_unit=0 dianggap 'Unit Lain' (tanpa ut)
        $expr_nama_unit = $has_unit_tujuan
            ? "CASE WHEN COALESCE(u.id_unit,0)=0 THEN 'Unit Lain' ELSE ut.nama_unit END"
            : "CASE WHEN COALESCE(u.id_unit,0)=0 THEN 'Unit Lain' ELSE '' END";
    }

    $this->db->select("
        u.username,
        u.nama_lengkap,
        u.no_telp,
        u.foto,
        u.tanggal_reg,
        u.level,
        ({$expr_nama_unit}) AS nama_unit
    ", false);
    $this->db->where('u.username', $username);

    return $this->db->get()->row();
}


    public function index(){
        $data["controller"] = get_class($this);

        $row = $this->_profil_row();
        if (!$row) {
            $row = (object)[
                'username'      => $this->session->userdata('admin_username'),
                'nama_lengkap'  => $this->session->userdata('admin_nama') ?: $this->session->userdata('admin_username'),
                'no_telp'       => '',
                'foto'          => '',
                'tanggal_reg'   => date('Y-m-d'),
                'level'         => $this->session->userdata('admin_level'),
                'nama_unit'     => '',
            ];
        }

        $data["record"]   = $row;
        $data["title"]    = "Pengaturan Profil";
        $data["subtitle"] = ucfirst($this->session->userdata("admin_level"));
        $data["content"]  = $this->load->view($data["controller"]."_view",$data,true);
        $this->render($data);
    }

    /** JSON untuk view (AJAX) */
    public function load_profil(){
        $row = $this->_profil_row();
        if (!$row) {
            $row = (object)[
                'username'      => $this->session->userdata('admin_username'),
                'nama_lengkap'  => $this->session->userdata('admin_nama') ?: $this->session->userdata('admin_username'),
                'no_telp'       => '',
                'foto'          => '',
                'tanggal_reg'   => date('Y-m-d'),
                'level'         => $this->session->userdata('admin_level'),
                'nama_unit'     => '',
            ];
        }

        // fallback foto
        $foto = !empty($row->foto) ? $row->foto : 'onhacker_221a3f5e.jpg';

        // format tanggal untuk tampilan
        $tgl = $row->tanggal_reg ? $row->tanggal_reg : date('Y-m-d');
        $tgl_fmt = function_exists('tgl_indo') ? tgl_indo($tgl) : date('d-m-Y', strtotime($tgl));

        $out = [
            'username'     => $row->username,
            'nama_lengkap' => $row->nama_lengkap,
            'no_telp'      => $row->no_telp,
            'foto'         => $foto,
            'tanggal_reg'  => $tgl_fmt,
            'level'        => $row->level,
            'nama_unit'    => (string)$row->nama_unit,
        ];
        $this->output->set_content_type('application/json')->set_output(json_encode($out));
    }

    public function detail_profil($key = null)
{
    $data["controller"] = get_class($this);

    // Tentukan kriteria pencarian
    $where = [];
    if ($key === null || $key === '') {
        $where['u.username'] = $this->session->userdata('admin_username');
    } else {
        if (ctype_digit((string)$key)) $where['u.id_session'] = (int)$key;
        else                           $where['u.username']   = $key;
    }

    $this->db->from('users u');

    // Cek keberadaan skema dinamis
    $has_unit_tujuan = $this->db->table_exists('unit_tujuan');
    $has_unit_lain   = $this->db->field_exists('id_unit_lain', 'users');
    $has_source      = $this->db->field_exists('unit_source',  'users');

    // Join yang tersedia saja
    if ($has_unit_tujuan) {
        $this->db->join('unit_tujuan ut', 'ut.id = u.id_unit', 'left');
    }
    if ($has_unit_lain) {
        $this->db->join('unit_lain ul', 'ul.id_unit_lain = u.id_unit_lain', 'left');
    }

    // Ekspresi nama_unit aman tanpa ut
    if ($has_unit_lain && $has_source) {
        $expr_nama_unit = $has_unit_tujuan
            ? "CASE WHEN u.unit_source='unit_lain' THEN ul.tugas ELSE ut.nama_unit END"
            : "CASE WHEN u.unit_source='unit_lain' THEN ul.tugas ELSE '' END";
    } else {
        $expr_nama_unit = $has_unit_tujuan
            ? "CASE WHEN COALESCE(u.id_unit,0)=0 THEN 'Unit Lain' ELSE ut.nama_unit END"
            : "CASE WHEN COALESCE(u.id_unit,0)=0 THEN 'Unit Lain' ELSE '' END";
    }

    $this->db->select("
        u.id_session,
        u.username,
        u.nama_lengkap,
        u.no_telp,
        u.email,
        u.level,
        u.foto,
        u.tanggal_reg,
        u.blokir,
        ({$expr_nama_unit}) AS nama_unit
    ", false);

    foreach ($where as $k=>$v) $this->db->where($k, $v);

    $q = $this->db->get();
    if ($q->num_rows() === 0) {
        show_error("Profil tidak ditemukan.", 404, "Not Found");
        return;
    }

    $row = $q->row();

    $data["record"]   = $row;
    $data["title"]    = "Detail Profil";
    $data["subtitle"] = htmlspecialchars($row->nama_lengkap.' ('.$row->username.')', ENT_QUOTES, 'UTF-8');
    $data["content"]  = $this->load->view(strtolower($data["controller"])."_detail_view", $data, true);
    $this->render($data);
}

    /** Update profil + upload foto */
    public function update(){
        // Ambil POST aman (XSS Filtering)
        $data     = $this->input->post(NULL, TRUE);
        $username = $this->session->userdata("admin_username");

        $this->load->library('form_validation');
        $this->form_validation->set_rules('nama_lengkap','Nama Lengkap','required');

        // No WA: boleh + di depan, total 9-15 digit
        $this->form_validation->set_rules(
            'no_telp',
            'No Whatsapp',
            'trim|required|regex_match[/^\+?\d{9,15}$/]'
        );

        $this->form_validation->set_message('required', '* %s Harus diisi ');
        $this->form_validation->set_message('regex_match', '* %s tidak valid (hanya angka dan opsional + di depan, 9-15 digit)');
        $this->form_validation->set_error_delimiters('<br> ', ' ');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>validation_errors()]);
            return;
        }

        // Cek unik no_telp selain user ini
        $cek = $this->db->where('no_telp', $data['no_telp'])
                        ->where('username !=', $username)
                        ->get('users')->row();
        if ($cek) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"No. Whatsapp ".$data["no_telp"]." sudah digunakan"]);
            return;
        }

        // Ambil user saat ini
        $user = $this->db->where('username',$username)->get('users')->row();
        if (!$user) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"User tidak ditemukan"]);
            return;
        }

        // Siapkan data update dasar
        $upd = [
            'nama_lengkap'       => $data['nama_lengkap'],
            'no_telp'            => $data['no_telp'],
            'permission_publish' => $user->permission_publish,
            'blokir'             => $user->blokir,
            'level'              => $user->level,
        ];

        // ==== Upload Foto (opsional) ====
        if (!empty($_FILES['foto']['name'])) {
            $this->load->library('upload');
            $base_name = ($this->session->userdata('id_desa') ?: $username) . "_" .
                         (function_exists('buat_name') ? buat_name($data['nama_lengkap'], "0")
                                                       : preg_replace('~\s+~','-', strtolower($data['nama_lengkap']))) .
                         "_" . substr(md5(date("Ymdhis")), 0, 8);
            $cfg = [
                'upload_path'   => FCPATH.'upload/users/',
                'allowed_types' => 'jpg|jpeg|png|JPG|PNG|JPEG',
                'max_size'      => 2048,
                'overwrite'     => TRUE,
                'file_name'     => $base_name,
            ];
            $this->upload->initialize($cfg);

            if (!$this->upload->do_upload('foto')) {
                $err = $this->upload->display_errors('', '');
                echo json_encode([
                    "success"=>false,
                    "title"=>"Gagal",
                    "pesan"=>"Upload gagal: ".$err."<br>Tipe diizinkan: jpg, jpeg, png. Maks 2MB."
                ]);
                return;
            }

            // Hapus foto lama (jika ada & file exist)
            if (!empty($user->foto)) {
                $old = FCPATH.'upload/users/'.$user->foto;
                if (is_file($old)) @unlink($old);
            }

            // Resize ringan
            $fdata = $this->upload->data();
            $upd['foto'] = $fdata['file_name'];

            $this->load->library('image_lib');
            $this->image_lib->clear();
            $rz = [
                'image_library'  => 'gd2',
                'source_image'   => $fdata['full_path'],
                'maintain_ratio' => TRUE,
                'width'          => 300,
                'height'         => 300,
            ];
            $this->image_lib->initialize($rz);
            @$this->image_lib->resize();
        }

        // Simpan
        $this->db->where('username', $username);
        $res = $this->db->update('users', $upd);

        if ($res) {
            // Sinkronkan session agar header menampilkan nama/foto terbaru
            if (!empty($upd['nama_lengkap'])) $this->session->set_userdata('admin_nama', $upd['nama_lengkap']);
            if (!empty($upd['foto']))         $this->session->set_userdata('admin_foto', $upd['foto']);

            echo json_encode(["success"=>true,"title"=>"Berhasil","pesan"=>"Data berhasil diupdate"]);
        } else {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data gagal diupdate"]);
        }
    }

    /** Ganti password */
    public function update_pass(){
        $data     = $this->input->post(NULL, TRUE);
        $username = $this->session->userdata("admin_username");

        $this->load->library('form_validation');
        $this->form_validation->set_rules('password_lama', 'Password Lama', 'required');
        $this->form_validation->set_rules('password_baru', 'Password Baru', 'trim|required|min_length[8]');
        $this->form_validation->set_rules('password_baru_lagi', 'Konfirmasi Password', 'trim|required|min_length[8]');
        $this->form_validation->set_message('required', '* %s Harus diisi');
        $this->form_validation->set_message('min_length', '* %s Minimal 8 karakter');
        $this->form_validation->set_error_delimiters('<br> ', ' ');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>validation_errors()]);
            return;
        }

        $user = $this->db->where('username',$username)->get('users')->row();
        if (!$user) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"User tidak ditemukan"]);
            return;
        }

        if (!password_verify($data['password_lama'], $user->password)) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Password Lama salah"]);
            return;
        }

        if ($data['password_baru'] !== $data['password_baru_lagi']) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Password Baru dan Konfirmasi Password tidak sama"]);
            return;
        }

        $upd = ['password' => password_hash($data['password_baru'], PASSWORD_ARGON2ID)];
        if (!empty($data['out']) && $data['out'] === 'out') {
            $upd['attack'] = md5(date("YmdHis"));
            $this->session->set_userdata("admin_attack", $upd['attack']);
        }

        $this->db->where('username',$username);
        $ok = $this->db->update('users', $upd);

        echo json_encode($ok
            ? ["success"=>true,"title"=>"Berhasil","pesan"=>"Password berhasil diperbarui"]
            : ["success"=>false,"title"=>"Gagal","pesan"=>"Gagal memperbarui password"]
        );
    }

    /* ====== (Opsional) util ====== */
    public function convert_password_to_password_hash(){ /* …biarkan seperti sebelumnya… */ }
    public function convert_password_to_password_hashx(){ /* …biarkan seperti sebelumnya… */ }
    public function detek(){ /* …biarkan seperti sebelumnya… */ }
}
