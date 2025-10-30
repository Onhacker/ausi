<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Master_syarat extends Admin_Controller {
	function __construct(){
		parent::__construct();
		cek_session_akses(get_class($this),$this->session->userdata('admin_session'));
		$this->load->model("M_master_syarat", "dm");
	}

	function index(){
		$data["controller"] = get_class($this);		
		$data["title"] = "Permohonan";
		$data["subtitle"] = $this->om->engine_nama_menu(get_class($this)) ;
		$data["content"] = $this->load->view($data["controller"]."_view",$data,true); 
		$this->render($data);
	}


	function get_data(){   
        $list = $this->dm->get_data();
        $data = array();
        $no = $_POST['start'];
        $tes = "'#p1'";
        foreach ($list as $res) {
            $no++;
            $row = array();
            $row["id"] = $res->id;
            $row["syarat"] = $res->syarat;
            $row["nama_permohonan"] = $res->nama_permohonan;
            $row['cek'] = '<div class="checkbox checkbox-primary checkbox-single"> <input type="checkbox" class="data-check" value="'.$res->id.'"><label></label></div>';

            $data[] = $row;
        }

        $output = array(
        	"draw" => $_POST['draw'],
        	"recordsTotal" => $this->dm->count_all(),
        	"recordsFiltered" => $this->dm->count_filtered(),
        	"data" => $data,
        );
        // echo $this->db->last_query();
        echo json_encode($output);
    }

    function edit($id){
        $data = array();
        $res = $this->dm->get_by_id($id);
        if($res->num_rows() > 0 ){
            $data = $res->row_array();
        } else {
            $data = array();
        }
        echo json_encode($data);
    }


    
    function update() {
        cek_session_akses("master_syarat", $this->session->userdata('admin_session'));

        $this->load->library('form_validation');
        $this->form_validation->set_rules('id_permohonan', 'Pilih Permohonan', 'required');
        $this->form_validation->set_rules('label', 'Label', 'required');
        $this->form_validation->set_rules('syarat', 'Syarat', 'required');
        $this->form_validation->set_rules('peringatan', 'Info Wajib/tidak wajib', 'required');
        $this->form_validation->set_message('required', '* %s Harus diisi ');
        $this->form_validation->set_error_delimiters('<br> ', ' ');

        if ($this->form_validation->run() !== TRUE) {
            echo json_encode([
                "success" => false,
                "title"   => "Gagal",
                "pesan"   => validation_errors()
            ]);
            return;
        }

    // Ambil input
        $id              = $this->input->post('id', true);
        $id_permohonan   = $this->input->post('id_permohonan', true);
        $label           = $this->input->post('label', true);
        $syarat          = $this->input->post('syarat', true);
        $peringatan      = $this->input->post('peringatan', true);
        $penjelasan      = $this->input->post('penjelasan', true);

    // Ambil data lama
        $data_lama = $this->db->get_where('master_syarat', ['id' => $id])->row();
        if (!$data_lama) {
            echo json_encode([
                "success" => false,
                "title"   => "Gagal",
                "pesan"   => "Data tidak ditemukan."
            ]);
            return;
        }

    // Penanganan Upload File
        if (!empty($_FILES['download']['name'])) {
            $config['upload_path']   = 'assets/formulir';
            $config['allowed_types'] = 'pdf';
        $config['max_size']      = 4000;  // Maksimal 4MB
        $config['encrypt_name']  = TRUE;

        // Memuat library upload
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('download')) {
            echo json_encode([
                "success" => false,
                "title"   => "Gagal Upload",
                "pesan"   =>  $config['max_size']
            ]);
            return;
        }

        $upload_data = $this->upload->data();
        $data_update['download'] = $upload_data['file_name'];  // Menyimpan nama file yang diupload

        // Hapus file lama jika ada
        if (!empty($data_lama->download) && file_exists('assets/formulir/' . $data_lama->download)) {
            unlink('assets/formulir/' . $data_lama->download);
        }
    } else {
        // Jika tidak ada file yang diupload, gunakan data lama
        $data_update['download'] = $data_lama->download;
    }

    // Siapkan data update
    $data_update['id_permohonan'] = $id_permohonan;
    $data_update['label']         = $label;
    $data_update['syarat']        = $syarat;
    $data_update['peringatan']    = $peringatan;
    $data_update['penjelasan']    = $penjelasan;

    // Eksekusi update
    $this->db->where('id', $id);
    $res = $this->db->update('master_syarat', $data_update);

    echo json_encode([
        "success" => $res,
        "title"   => $res ? "Berhasil" : "Gagal",
        "pesan"   => $res ? "Data berhasil diupdate" : "Data gagal diupdate"
    ]);
}




    function generate_kode_file($label) {
    // Ubah ke huruf kecil
        $label = strtolower($label);

    // Hapus semua karakter selain huruf dan angka
        $kode = preg_replace('/[^a-z0-9]/', '', $label);

    // Jika kosong, fallback dengan timestamp
        if (empty($kode)) {
        $kode = 'file' . time(); // contoh: file1714450123
    }

    return $kode;
}

function add() {
    cek_session_akses("master_syarat", $this->session->userdata('admin_session'));

    $this->load->library('form_validation');
    $this->form_validation->set_rules('id_permohonan', 'Pilih Permohonan', 'required');
    $this->form_validation->set_rules('label', 'Label', 'required');
    $this->form_validation->set_rules('syarat', 'Syarat', 'required');
    $this->form_validation->set_rules('peringatan', 'Info Wajib/tidak wajib', 'required');
    $this->form_validation->set_message('required', '* %s Harus diisi');
    $this->form_validation->set_error_delimiters('<br> ', ' ');

    if ($this->form_validation->run() !== TRUE) {
        echo json_encode([
            "success" => false,
            "title"   => "Validasi Gagal",
            "pesan"   => validation_errors()
        ]);
        return;
    }

    $data = $this->db->escape_str($this->input->post());
    $data['kode_file'] = $this->generate_kode_file($data["label"]);

    // Generate ID baru
    $last_id = $this->db->select('id')->order_by('id', 'DESC')->limit(1)->get('master_syarat')->row();
    $data["id"] = $last_id ? $last_id->id + 1 : 1;

    // Ambil nama tabel dari master_permohonan
    $permohonan = $this->db->get_where("master_permohonan", ["id_permohonan" => $data["id_permohonan"]])->row();
    if (!$permohonan) {
        echo json_encode([
            "success" => false,
            "title"   => "Gagal",
            "pesan"   => "Permohonan tidak ditemukan"
        ]);
        return;
    }
    $alt_tabel = $permohonan->nama_tabel;

    // Cek & ALTER tabel jika kolom belum ada
    if (!empty($alt_tabel) && !empty($data['kode_file'])) {
        $kolom = 'file_' . $data['kode_file'];
        $field = $this->db->query("SHOW COLUMNS FROM `$alt_tabel` LIKE '$kolom'")->row();
        if (!$field) {
            $this->db->query("ALTER TABLE `$alt_tabel` ADD `$kolom` VARCHAR(255) DEFAULT NULL");
        }
    }

    // Upload file (pdf)
    if (!empty($_FILES['download']['name'])) {
        $config['upload_path']   = 'assets/formulir';
        $config['allowed_types'] = 'pdf';
        $config['max_size']      = 2048;
        $config['encrypt_name']  = TRUE;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('download')) {
            echo json_encode([
                "success" => false,
                "title"   => "Gagal Upload",
                "pesan"   => strip_tags($this->upload->display_errors())
            ]);
            return;
        }

        $upload_data = $this->upload->data();
        $data['download'] = $upload_data['file_name'];
    }

    // Simpan ke DB
    $res = $this->db->insert("master_syarat", $data);
        // echo $this->db->last_query();
    echo json_encode([
        "success" => $res,
        "title"   => $res ? "Berhasil" : "Gagal",
        "pesan"   => $res ? "Data berhasil disimpan" : "Data gagal disimpan"
    ]);
}





function hapus_data() {
    $list_id = $this->input->post('id');
    $res = false;

    foreach ($list_id as $id) {
        // Ambil data syarat
        $syarat = $this->db->get_where("master_syarat", ["id" => $id])->row();

        if ($syarat) {
            $kode_file = 'file_' . $syarat->kode_file;
            $file_path = 'assets/formulir/' . $syarat->download;

            // Hapus file jika ada
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Ambil nama tabel dari master_permohonan
            $permohonan = $this->db->get_where("master_permohonan", ["id_permohonan" => $syarat->id_permohonan])->row();
            if ($permohonan) {
                $tabel = $permohonan->nama_tabel;

                // Cek dan hapus kolom jika ada
                $field = $this->db->query("SHOW COLUMNS FROM $tabel LIKE '{$kode_file}'")->row();
                if ($field) {
                    $this->db->query("ALTER TABLE $tabel DROP COLUMN `{$kode_file}`");
                }
            }

            // Hapus data dari master_syarat
            $this->db->where("id", $id);
            $res = $this->db->delete("master_syarat");
        }
    }

    // Respon
    if ($res) {
        $ret = [
            "success" => true,
            "title"   => "Berhasil",
            "pesan"   => "Data berhasil dihapus"
        ];
    } else {
        $ret = [
            "success" => false,
            "title"   => "Gagal",
            "pesan"   => "Data gagal dihapus"
        ];
    }

    echo json_encode($ret);
}



}
