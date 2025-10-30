<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_unit_lain extends Admin_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('M_admin_unit_lain','dm');
        cek_session_akses(get_class($this), $this->session->userdata('admin_session'));
    }

    public function index(){
        $data["controller"] = get_class($this);
        $data["title"]      = "Master Unit Lain";
        $data["subtitle"]   = $this->om->engine_nama_menu(get_class($this));
        $data["content"]    = $this->load->view($data["controller"]."_view",$data,true);
        $this->render($data);
    }

    /** DataTables server-side */
    public function get_data()
    {
        $list = $this->dm->get_data();
        $data = [];
        foreach ($list as $r) {
            $row = [];
            $row['cek']   = '<div class="checkbox checkbox-primary checkbox-single">'
                          . '<input type="checkbox" class="data-check" value="'.(int)$r->id_unit_lain.'"><label></label>'
                          . '</div>';
            $row['no']    = ''; // akan diisi di rowCallback
            $row['tugas'] = htmlspecialchars($r->tugas, ENT_QUOTES, 'UTF-8');

            $btnEdit  = '<button type="button" class="btn btn-sm btn-warning" onclick="edit('.(int)$r->id_unit_lain.')"><i class="fe-edit"></i> Edit</button>';
            $row['aksi']  = $btnEdit;

            $data[] = $row;
        }

        $out = [
            "draw"            => (int)$this->input->post('draw'),
            "recordsTotal"    => $this->dm->count_all(),
            "recordsFiltered" => $this->dm->count_filtered(),
            "data"            => $data,
        ];
        $this->output->set_content_type('application/json')->set_output(json_encode($out));
    }

    /** Ambil satu baris untuk form edit */
    public function get_one($id)
    {
        $id  = (int)$id;
        $row = $this->db->get_where('unit_lain',['id_unit_lain'=>$id])->row();
        if (!$row) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]);
            return;
        }
        echo json_encode(["success"=>true,"data"=>$row]);
    }

    /** Create */
    public function add()
    {
        $data = $this->input->post(NULL, TRUE);
        $this->load->library('form_validation');
        $this->form_validation->set_rules('tugas','Tugas','trim|required|min_length[3]|max_length[255]');
        $this->form_validation->set_message('required','* %s harus diisi');
        $this->form_validation->set_message('min_length','* %s minimal %s karakter');
        $this->form_validation->set_message('max_length','* %s maksimal %s karakter');
        $this->form_validation->set_error_delimiters('<br> ',' ');

        if ($this->form_validation->run() !== TRUE) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>validation_errors()]);
            return;
        }

        // Cek duplikasi (case-insensitive)
        $dupe = $this->db
            ->where('LOWER(tugas)=', strtolower($data['tugas']))
            ->count_all_results('unit_lain');
        if ($dupe > 0) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Tugas sudah ada"]);
            return;
        }

        $ins = [
            'tugas' => $data['tugas'],
        ];
        $res = $this->db->insert('unit_lain',$ins);
        if ($res) {
            echo json_encode(["success"=>true,"title"=>"Berhasil","pesan"=>"Data berhasil disimpan"]);
        } else {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data gagal disimpan"]);
        }
    }

    /** Update */
    public function update()
    {
        $data = $this->input->post(NULL, TRUE);
        $id   = (int)($data['id_unit_lain'] ?? 0);

        $this->load->library('form_validation');
        $this->form_validation->set_rules('id_unit_lain','ID','required|integer');
        $this->form_validation->set_rules('tugas','Tugas','trim|required|min_length[3]|max_length[255]');
        $this->form_validation->set_message('required','* %s harus diisi');
        $this->form_validation->set_error_delimiters('<br> ',' ');

        if ($this->form_validation->run() !== TRUE) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>validation_errors()]);
            return;
        }

        // Pastikan ada
        $exists = $this->db->where('id_unit_lain',$id)->count_all_results('unit_lain');
        if ($exists == 0) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]);
            return;
        }

        // Cek duplikasi selain dirinya
        $dupe = $this->db->where('LOWER(tugas)=', strtolower($data['tugas']))
                         ->where('id_unit_lain <>', $id)
                         ->count_all_results('unit_lain');
        if ($dupe > 0) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Tugas sudah ada"]);
            return;
        }

        $upd = ['tugas' => $data['tugas']];
        $res = $this->db->where('id_unit_lain',$id)->update('unit_lain',$upd);

        if ($res) {
            echo json_encode(["success"=>true,"title"=>"Berhasil","pesan"=>"Data berhasil diupdate"]);
        } else {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data gagal diupdate"]);
        }
    }

    /** Delete (bulk) */
    public function hapus_data()
    {
        $ids = $this->input->post('id');
        if (!is_array($ids) || count($ids) === 0) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Tidak ada data"]);
            return;
        }

        $ok = true;
        foreach ($ids as $id) {
            $id = (int)$id;
            if ($id <= 0) continue;
            $this->db->where('id_unit_lain',$id);
            $ok = $ok && $this->db->delete('unit_lain');
        }

        if ($ok) echo json_encode(["success"=>true,"title"=>"Berhasil","pesan"=>"Data berhasil dihapus"]);
        else     echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Sebagian data gagal dihapus"]);
    }
}
