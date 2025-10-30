<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_kurir extends Admin_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('M_admin_kurir','dm');
        cek_session_akses(get_class($this), $this->session->userdata('admin_session')); // jika dipakai

    }

    public function index(){
        $data["controller"] = get_class($this);
        $data["title"]      = "Master";
        $data["subtitle"]   = $this->om->engine_nama_menu(get_class($this));
        $data["content"]    = $this->load->view("admin_kurir_view",$data,true);
        $this->render($data);
    }

    /** DataTables server-side */
    public function get_dataa(){
        $list = $this->dm->get_data();
        $data = [];

        foreach($list as $r){
            $btns  = '<button type="button" class="btn btn-sm btn-warning mr-1" onclick="edit('.(int)$r->id.')"><i class="fe-edit"></i> Edit</button>';

            // badge status
            $badgeClass = 'badge-secondary';
            if ($r->status === 'available') $badgeClass = 'badge-success';
            else if ($r->status === 'ontask') $badgeClass = 'badge-warning';
            else if ($r->status === 'off') $badgeClass = 'badge-dark';

            $status_badge = '<span class="badge '.$badgeClass.'">'.htmlspecialchars($r->status,ENT_QUOTES,'UTF-8').'</span>';

            $row = [];
            $row['cek']            = '<div class="checkbox checkbox-primary checkbox-single"><input type="checkbox" class="data-check" value="'.$r->id.'"><label></label></div>';
            $row['no']             = '';
            $row['nama']           = htmlspecialchars($r->nama, ENT_QUOTES, 'UTF-8');
            $row['kontak']         = htmlspecialchars($r->phone ?? '-', ENT_QUOTES, 'UTF-8');
            $row['kendaraan']      = htmlspecialchars(trim(($r->vehicle ?? '').' '.($r->plate ?? '')), ENT_QUOTES, 'UTF-8');
            $row['status']         = $status_badge;
            $row['on_trip_count']  = (int)$r->on_trip_count;
            $row['aksi']           = $btns;

            $data[] = $row;
        }

        $out = [
            "draw"            => (int)$this->input->post('draw'),
            "recordsTotal"    => $this->dm->count_all(),
            "recordsFiltered" => $this->dm->count_filtered(),
            "data"            => $data,
        ];

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($out));
    }

    /** Ambil satu kurir */
    public function get_one($id){
        $id = (int)$id;
        $row = $this->db->get_where('kurir',['id'=>$id])->row();
        if (!$row) {
            echo json_encode([
                "success"=>false,
                "title"=>"Gagal",
                "pesan"=>"Data tidak ditemukan"
            ]);
            return;
        }
        echo json_encode(["success"=>true,"data"=>$row]);
    }

    /** Create */
    public function add(){
        $this->load->library('form_validation');

        $this->form_validation->set_rules('nama','Nama','trim|required|min_length[1]|max_length[100]');
        $this->form_validation->set_rules('phone','Telepon','trim|max_length[30]');
        $this->form_validation->set_rules('vehicle','Kendaraan','trim|max_length[60]');
        $this->form_validation->set_rules('plate','Plat','trim|max_length[20]');
        $this->form_validation->set_rules('status','Status','trim|required|in_list[available,ontask,off]');
        $this->form_validation->set_rules('on_trip_count','On Trip Count','trim|integer');

        if ($this->form_validation->run() !== TRUE) {
            echo json_encode([
                "success"=>false,
                "title"=>"Validasi Gagal",
                "pesan"=>validation_errors()
            ]);
            return;
        }

        $data_ins = [
            'nama'           => $this->input->post('nama', true),
            'phone'          => $this->input->post('phone', true) ?: null,
            'vehicle'        => $this->input->post('vehicle', true) ?: null,
            'plate'          => $this->input->post('plate', true) ?: null,
            'status'         => $this->input->post('status', true),
            'on_trip_count'  => (int)$this->input->post('on_trip_count', true),
        ];

        $ok = $this->db->insert('kurir', $data_ins);

        echo json_encode([
            "success"=>$ok,
            "title"=>$ok?"Berhasil":"Gagal",
            "pesan"=>$ok?"Data berhasil disimpan":"Data gagal disimpan"
        ]);
    }

    /** Update */
    public function update(){
        $this->load->library('form_validation');

        $this->form_validation->set_rules('id','ID','required|integer');

        $this->form_validation->set_rules('nama','Nama','trim|required|min_length[1]|max_length[100]');
        $this->form_validation->set_rules('phone','Telepon','trim|max_length[30]');
        $this->form_validation->set_rules('vehicle','Kendaraan','trim|max_length[60]');
        $this->form_validation->set_rules('plate','Plat','trim|max_length[20]');
        $this->form_validation->set_rules('status','Status','trim|required|in_list[available,ontask,off]');
        $this->form_validation->set_rules('on_trip_count','On Trip Count','trim|integer');

        if ($this->form_validation->run() !== TRUE) {
            echo json_encode([
                "success"=>false,
                "title"=>"Validasi Gagal",
                "pesan"=>validation_errors()
            ]);
            return;
        }

        $id = (int)$this->input->post('id', true);
        $row = $this->db->get_where('kurir',['id'=>$id])->row();
        if (!$row) {
            echo json_encode([
                "success"=>false,
                "title"=>"Gagal",
                "pesan"=>"Data tidak ditemukan"
            ]);
            return;
        }

        $data_upd = [
            'nama'           => $this->input->post('nama', true),
            'phone'          => $this->input->post('phone', true) ?: null,
            'vehicle'        => $this->input->post('vehicle', true) ?: null,
            'plate'          => $this->input->post('plate', true) ?: null,
            'status'         => $this->input->post('status', true),
            'on_trip_count'  => (int)$this->input->post('on_trip_count', true),
        ];

        $ok = $this->db->where('id',$id)->update('kurir',$data_upd);

        echo json_encode([
            "success"=>$ok,
            "title"=>$ok?"Berhasil":"Gagal",
            "pesan"=>$ok?"Data berhasil diupdate":"Data gagal diupdate"
        ]);
    }

    /** Delete bulk */
    public function hapus_data(){
        $ids = $this->input->post('id');
        if (!is_array($ids) || count($ids) === 0) {
            echo json_encode([
                "success"=>false,
                "title"=>"Gagal",
                "pesan"=>"Tidak ada data"
            ]);
            return;
        }

        $ok = true;
        foreach ($ids as $id) {
            $id = (int)$id;
            if ($id <= 0) continue;
            $ok = $ok && $this->db->delete('kurir', ['id'=>$id]);
        }

        echo json_encode([
            "success"=>$ok,
            "title"=>$ok?"Berhasil":"Gagal",
            "pesan"=>$ok?"Data berhasil dihapus":"Sebagian data gagal dihapus"
        ]);
    }
}
