<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_meja_billiard extends Admin_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('M_admin_meja_billiard','dm');
        cek_session_akses(get_class($this), $this->session->userdata('admin_session')); // jika dipakai
    }

    public function index(){
        $data["controller"] = get_class($this);
        $data["title"]      = "Master";
        $data["subtitle"]   = $this->om->engine_nama_menu(get_class($this));
        $data["content"]    = $this->load->view($data["controller"]."_view",$data,true);
        $this->render($data);
    }

    /** DataTables server-side */
    public function get_dataa(){
        $list = $this->dm->get_data();
        $data = [];

        foreach($list as $r){
            $btns  = '<button type="button" class="btn btn-sm btn-warning mr-1" onclick="edit('.(int)$r->id_meja.')"><i class="fe-edit"></i> Edit</button>';

            $aktif_badge = ((int)$r->aktif === 1)
                ? '<span class="badge badge-success">Aktif</span>'
                : '<span class="badge badge-danger">Nonaktif</span>';

            $kat_badge = '';
            $kat_lower = strtolower((string)$r->kategori);
            if ($kat_lower === 'vip'){
                $kat_badge = '<span class="badge badge-dark">VIP</span>';
            } else {
                $kat_badge = '<span class="badge badge-primary">Reguler</span>';
            }

            $row = [];
            $row['cek']            = '<div class="checkbox checkbox-primary checkbox-single"><input type="checkbox" class="data-check" value="'.$r->id_meja.'"><label></label></div>';
            $row['no']             = '';
            $row['nama_meja']      = htmlspecialchars($r->nama_meja, ENT_QUOTES, 'UTF-8');
            $row['kategori']       = $kat_badge;
            $row['harga_per_jam']  = 'Rp '.number_format((int)$r->harga_per_jam,0,',','.').'/jam';
            $row['aktif']          = $aktif_badge;
            $row['updated_at']     = $r->updated_at ? htmlspecialchars($r->updated_at, ENT_QUOTES, 'UTF-8') : '-';
            $row['aksi']           = $btns;
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

    /** Ambil satu baris */
    public function get_one($id_meja){
        $id_meja = (int)$id_meja;
        $row = $this->db->get_where('meja_billiard',['id_meja'=>$id_meja])->row();
        if (!$row) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]);
            return;
        }
        echo json_encode(["success"=>true,"data"=>$row]);
    }

    /** Create */
    public function add(){
        $this->load->library('form_validation');

        $this->form_validation->set_rules('nama_meja','Nama Meja','trim|required|min_length[1]|max_length[100]');
        $this->form_validation->set_rules('kategori','Kategori','trim|required|in_list[reguler,vip]');
        $this->form_validation->set_rules('harga_per_jam','Harga/Jam Default','trim|integer');

        $this->form_validation->set_rules('jam_buka','Jam Buka','trim|required');
        $this->form_validation->set_rules('jam_tutup','Jam Tutup','trim|required');
        $this->form_validation->set_rules('jam_tutup_voucer','Jam Tutup Voucher','trim|required');

        $this->form_validation->set_rules('wk_day_start','Weekday Day Start','trim|required');
        $this->form_validation->set_rules('wk_day_end','Weekday Day End','trim|required');
        $this->form_validation->set_rules('wk_day_rate','Weekday Day Rate','trim|integer');

        $this->form_validation->set_rules('wk_night_start','Weekday Night Start','trim|required');
        $this->form_validation->set_rules('wk_night_end','Weekday Night End','trim|required');
        $this->form_validation->set_rules('wk_night_rate','Weekday Night Rate','trim|integer');

        $this->form_validation->set_rules('we_day_start','Weekend Day Start','trim|required');
        $this->form_validation->set_rules('we_day_end','Weekend Day End','trim|required');
        $this->form_validation->set_rules('we_day_rate','Weekend Day Rate','trim|integer');

        $this->form_validation->set_rules('we_night_start','Weekend Night Start','trim|required');
        $this->form_validation->set_rules('we_night_end','Weekend Night End','trim|required');
        $this->form_validation->set_rules('we_night_rate','Weekend Night Rate','trim|integer');

        $this->form_validation->set_rules('aktif','Status Aktif','trim|required|in_list[0,1]');
        // catatan boleh kosong

        if ($this->form_validation->run() !== TRUE) {
            echo json_encode([
                "success"=>false,
                "title"=>"Validasi Gagal",
                "pesan"=>validation_errors()
            ]);
            return;
        }

        $data_ins = [
            'nama_meja'          => $this->input->post('nama_meja', true),
            'kategori'           => $this->input->post('kategori', true),
            'harga_per_jam'      => (int)$this->input->post('harga_per_jam', true),

            'wk_day_start'       => $this->input->post('wk_day_start', true),
            'wk_day_end'         => $this->input->post('wk_day_end', true),
            'wk_day_rate'        => (int)$this->input->post('wk_day_rate', true),

            'wk_night_start'     => $this->input->post('wk_night_start', true),
            'wk_night_end'       => $this->input->post('wk_night_end', true),
            'wk_night_rate'      => (int)$this->input->post('wk_night_rate', true),

            'we_day_start'       => $this->input->post('we_day_start', true),
            'we_day_end'         => $this->input->post('we_day_end', true),
            'we_day_rate'        => (int)$this->input->post('we_day_rate', true),

            'we_night_start'     => $this->input->post('we_night_start', true),
            'we_night_end'       => $this->input->post('we_night_end', true),
            'we_night_rate'      => (int)$this->input->post('we_night_rate', true),

            'jam_buka'           => $this->input->post('jam_buka', true),
            'jam_tutup'          => $this->input->post('jam_tutup', true),
            'jam_tutup_voucer'   => $this->input->post('jam_tutup_voucer', true),

            'aktif'              => (int)$this->input->post('aktif', true),
            'catatan'            => $this->input->post('catatan', true),
        ];

        $ok = $this->db->insert('meja_billiard', $data_ins);

        echo json_encode([
            "success"=>$ok,
            "title"=>$ok?"Berhasil":"Gagal",
            "pesan"=>$ok?"Data berhasil disimpan":"Data gagal disimpan"
        ]);
    }

    /** Update */
    public function update(){
        $this->load->library('form_validation');

        $this->form_validation->set_rules('id_meja','ID','required|integer');

        $this->form_validation->set_rules('nama_meja','Nama Meja','trim|required|min_length[1]|max_length[100]');
        $this->form_validation->set_rules('kategori','Kategori','trim|required|in_list[reguler,vip]');
        $this->form_validation->set_rules('harga_per_jam','Harga/Jam Default','trim|integer');

        $this->form_validation->set_rules('jam_buka','Jam Buka','trim|required');
        $this->form_validation->set_rules('jam_tutup','Jam Tutup','trim|required');
        $this->form_validation->set_rules('jam_tutup_voucer','Jam Tutup Voucher','trim|required');

        $this->form_validation->set_rules('wk_day_start','Weekday Day Start','trim|required');
        $this->form_validation->set_rules('wk_day_end','Weekday Day End','trim|required');
        $this->form_validation->set_rules('wk_day_rate','Weekday Day Rate','trim|integer');

        $this->form_validation->set_rules('wk_night_start','Weekday Night Start','trim|required');
        $this->form_validation->set_rules('wk_night_end','Weekday Night End','trim|required');
        $this->form_validation->set_rules('wk_night_rate','Weekday Night Rate','trim|integer');

        $this->form_validation->set_rules('we_day_start','Weekend Day Start','trim|required');
        $this->form_validation->set_rules('we_day_end','Weekend Day End','trim|required');
        $this->form_validation->set_rules('we_day_rate','Weekend Day Rate','trim|integer');

        $this->form_validation->set_rules('we_night_start','Weekend Night Start','trim|required');
        $this->form_validation->set_rules('we_night_end','Weekend Night End','trim|required');
        $this->form_validation->set_rules('we_night_rate','Weekend Night Rate','trim|integer');

        $this->form_validation->set_rules('aktif','Status Aktif','trim|required|in_list[0,1]');

        if ($this->form_validation->run() !== TRUE) {
            echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>validation_errors()]);
            return;
        }

        $id_meja = (int)$this->input->post('id_meja', true);
        $row     = $this->db->get_where('meja_billiard',['id_meja'=>$id_meja])->row();
        if (!$row) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]);
            return;
        }

        $data_upd = [
            'nama_meja'          => $this->input->post('nama_meja', true),
            'kategori'           => $this->input->post('kategori', true),
            'harga_per_jam'      => (int)$this->input->post('harga_per_jam', true),

            'wk_day_start'       => $this->input->post('wk_day_start', true),
            'wk_day_end'         => $this->input->post('wk_day_end', true),
            'wk_day_rate'        => (int)$this->input->post('wk_day_rate', true),

            'wk_night_start'     => $this->input->post('wk_night_start', true),
            'wk_night_end'       => $this->input->post('wk_night_end', true),
            'wk_night_rate'      => (int)$this->input->post('wk_night_rate', true),

            'we_day_start'       => $this->input->post('we_day_start', true),
            'we_day_end'         => $this->input->post('we_day_end', true),
            'we_day_rate'        => (int)$this->input->post('we_day_rate', true),

            'we_night_start'     => $this->input->post('we_night_start', true),
            'we_night_end'       => $this->input->post('we_night_end', true),
            'we_night_rate'      => (int)$this->input->post('we_night_rate', true),

            'jam_buka'           => $this->input->post('jam_buka', true),
            'jam_tutup'          => $this->input->post('jam_tutup', true),
            'jam_tutup_voucer'   => $this->input->post('jam_tutup_voucer', true),

            'aktif'              => (int)$this->input->post('aktif', true),
            'catatan'            => $this->input->post('catatan', true),
        ];

        $ok = $this->db->where('id_meja',$id_meja)->update('meja_billiard',$data_upd);

        echo json_encode([
            "success"=>$ok,
            "title"=>$ok?"Berhasil":"Gagal",
            "pesan"=>$ok?"Data berhasil diupdate":"Data gagal diupdate"
        ]);
    }

    /** Delete (bulk) */
    public function hapus_data(){
        $ids = $this->input->post('id');
        if (!is_array($ids) || count($ids) === 0) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Tidak ada data"]);
            return;
        }

        $ok = true;
        foreach ($ids as $id_meja) {
            $id_meja = (int)$id_meja;
            if ($id_meja <= 0) continue;
            $ok = $ok && $this->db->delete('meja_billiard', ['id_meja'=>$id_meja]);
        }

        echo json_encode([
            "success"=>$ok,
            "title"=>$ok?"Berhasil":"Gagal",
            "pesan"=>$ok?"Data berhasil dihapus":"Sebagian data gagal dihapus"
        ]);
    }
}
