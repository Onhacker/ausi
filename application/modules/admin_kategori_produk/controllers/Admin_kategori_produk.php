<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_kategori_produk extends Admin_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('M_admin_kategori_produk','dm');
        $this->load->helper(['url','text']);
        cek_session_akses(get_class($this), $this->session->userdata('admin_session'));
    }

    private function purge_public_caches(){
        $this->load->driver('cache', ['adapter' => 'file']);
        $this->cache->save('kategori_produk_ver', time(), 365*24*3600);
        $this->output->set_header('X-Cache-Purged: kategori_produk');
    }

    public function index(){
        $data["controller"] = get_class($this);
        $data["title"]      = "Master";
        $data["subtitle"]   = $this->om->engine_nama_menu(get_class($this));
        $data["content"]    = $this->load->view($data["controller"]."_view",$data,true);
        $this->render($data);
    }

    public function get_dataa(){
        $list = $this->dm->get_data();
        $data = [];
        foreach($list as $r){
            $row = [];
            $row['cek']   = '<div class="checkbox checkbox-primary checkbox-single"><input type="checkbox" class="data-check" value="'.(int)$r->id.'"><label></label></div>';
            $row['no']    = '';
            $row['nama']  = htmlspecialchars($r->nama, ENT_QUOTES, 'UTF-8');
            $row['slug']  = htmlspecialchars($r->slug, ENT_QUOTES, 'UTF-8');
            $row['aktif'] = $r->is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>';
            $btnEdit = '<button type="button" class="btn btn-sm btn-warning" onclick="edit('.(int)$r->id.')"><i class="fe-edit"></i> Edit</button>';
            $row['aksi'] = $btnEdit;
            $data[] = $row;
        }

        $out = [
            "draw" => (int)$this->input->post('draw'),
            "recordsTotal" => $this->dm->count_all(),
            "recordsFiltered" => $this->dm->count_filtered(),
            "data" => $data,
        ];
        $this->output->set_content_type('application/json')->set_output(json_encode($out));
    }

    public function get_one($id){
        $id  = (int)$id;
        $row = $this->db->get_where('kategori_produk',['id'=>$id])->row();
        if (!$row){ echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]); return; }
        echo json_encode(["success"=>true,"data"=>$row]);
    }

    public function add(){
        $this->load->library('form_validation');
        $this->form_validation->set_rules('nama','Nama','trim|required|min_length[2]|max_length[120]');
        if ($this->form_validation->run() !== TRUE){
            echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>validation_errors()]); return;
        }
        $nama      = $this->input->post('nama', true);
        $deskripsi = $this->input->post('deskripsi', true);
        $is_active = (int) !!$this->input->post('is_active');
        $slug = $this->dm->generate_unique_slug($nama);

        $ok = $this->db->insert('kategori_produk',[
            'nama'=>$nama,'slug'=>$slug,'deskripsi'=>$deskripsi,'is_active'=>$is_active
        ]);

        if ($ok) { $this->purge_public_caches(); }

        echo json_encode(["success"=>$ok,"title"=>$ok?"Berhasil":"Gagal","pesan"=>$ok?"Data disimpan":"Gagal simpan"]);
    }

    public function update(){
        $this->load->library('form_validation');
        $this->form_validation->set_rules('id','ID','required|integer');
        $this->form_validation->set_rules('nama','Nama','trim|required|min_length[2]|max_length[120]');
        if ($this->form_validation->run() !== TRUE){
            echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>validation_errors()]); return;
        }

        $id        = (int)$this->input->post('id', true);
        $nama      = $this->input->post('nama', true);
        $deskripsi = $this->input->post('deskripsi', true);
        $is_active = (int) !!$this->input->post('is_active');

        $row = $this->db->get_where('kategori_produk',['id'=>$id])->row();
        if (!$row){ echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]); return; }

        $needSlug = (empty($row->slug) || trim($row->nama) !== trim($nama));
        $upd = ['nama'=>$nama,'deskripsi'=>$deskripsi,'is_active'=>$is_active];
        if ($needSlug){ $upd['slug'] = $this->dm->generate_unique_slug($nama, $id); }

        $ok = $this->db->where('id',$id)->update('kategori_produk', $upd);

        if ($ok) { $this->purge_public_caches(); }

        echo json_encode(["success"=>$ok,"title"=>$ok?"Berhasil":"Gagal","pesan"=>$ok?"Data diupdate":"Gagal update"]);
    }

    public function hapus_data(){
        $ids = $this->input->post('id');
        if (!is_array($ids) || count($ids)==0){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Tidak ada data"]); return;
        }
        $ok = true;
        foreach($ids as $id){
            $id = (int)$id; if ($id<=0) continue;
            // cegah jika dipakai produk
            $inuse = $this->db->where('kategori_id',$id)->count_all_results('produk');
            if ($inuse > 0){ $ok = false; continue; }
            $ok = $ok && $this->db->delete('kategori_produk',['id'=>$id]);
        }

        if ($ok) { $this->purge_public_caches(); }

        echo json_encode(["success"=>$ok,"title"=>$ok?"Berhasil":"Sebagian Gagal","pesan"=>$ok?"Data dihapus":"Sebagian tidak bisa dihapus (masih dipakai produk)"]);
    }

    /* ===================== SUBKATEGORI ===================== */

public function list_kategori(){
    // untuk dropdown parent kategori (hanya yg aktif biar rapi)
    $rows = $this->db->select('id, nama')
                     ->from('kategori_produk')->where('is_active',1)
                     ->order_by('nama','ASC')->get()->result();
    $this->output->set_content_type('application/json')->set_output(json_encode($rows));
}

public function sub_get_data(){
    // server-side datatable utk subkategori
    $cat = (int)$this->input->post('kategori_id'); // optional filter
    $list = $this->dm->get_sub_data($cat);
    $data = [];
    foreach ($list as $r){
        $row = [];
        $row['cek']        = '<div class="checkbox checkbox-primary checkbox-single"><input type="checkbox" class="sub-check" value="'.(int)$r->id.'"><label></label></div>';
        $row['no']         = '';
        $row['kategori']   = htmlspecialchars($r->kategori_nama, ENT_QUOTES, 'UTF-8');
        $row['nama']       = htmlspecialchars($r->nama, ENT_QUOTES, 'UTF-8');
        $row['slug']       = htmlspecialchars($r->slug, ENT_QUOTES, 'UTF-8');
        $row['aktif']      = $r->is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>';
        $btnEdit = '<button type="button" class="btn btn-sm btn-warning" onclick="sub_edit('.(int)$r->id.')"><i class="fe-edit"></i> Edit</button>';
        $row['aksi']       = $btnEdit;
        $data[] = $row;
    }
    $out = [
        "draw" => (int)$this->input->post('draw'),
        "recordsTotal" => $this->dm->sub_count_all($cat),
        "recordsFiltered" => $this->dm->sub_count_filtered($cat),
        "data" => $data,
    ];
    $this->output->set_content_type('application/json')->set_output(json_encode($out));
}

public function sub_get_one($id){
    $id  = (int)$id;
    $row = $this->db->select('ks.*, kp.nama AS kategori_nama')
                    ->from('kategori_produk_sub ks')
                    ->join('kategori_produk kp','kp.id=ks.kategori_id','left')
                    ->where('ks.id',$id)->get()->row();
    if (!$row){ echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]); return; }
    echo json_encode(["success"=>true,"data"=>$row]);
}

public function sub_add(){
    $this->load->library('form_validation');
    $this->form_validation->set_rules('kategori_id','Kategori','required|integer');
    $this->form_validation->set_rules('nama','Nama','trim|required|min_length[2]|max_length[120]');
    if ($this->form_validation->run() !== TRUE){
        echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>validation_errors()]); return;
    }
    $kategori_id = (int)$this->input->post('kategori_id',true);
    $nama        = $this->input->post('nama', true);
    $deskripsi   = $this->input->post('deskripsi', true);
    $is_active   = (int) !!$this->input->post('is_active');
    $slug        = $this->dm->generate_unique_slug_sub($nama);

    $ok = $this->db->insert('kategori_produk_sub',[
        'kategori_id'=>$kategori_id,'nama'=>$nama,'slug'=>$slug,'deskripsi'=>$deskripsi,'is_active'=>$is_active
    ]);

    if ($ok) { $this->purge_public_caches(); }
    echo json_encode(["success"=>$ok,"title"=>$ok?"Berhasil":"Gagal","pesan"=>$ok?"Data disimpan":"Gagal simpan"]);
}

public function sub_update(){
    $this->load->library('form_validation');
    $this->form_validation->set_rules('id','ID','required|integer');
    $this->form_validation->set_rules('kategori_id','Kategori','required|integer');
    $this->form_validation->set_rules('nama','Nama','trim|required|min_length[2]|max_length[120]');
    if ($this->form_validation->run() !== TRUE){
        echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>validation_errors()]); return;
    }

    $id          = (int)$this->input->post('id', true);
    $kategori_id = (int)$this->input->post('kategori_id', true);
    $nama        = $this->input->post('nama', true);
    $deskripsi   = $this->input->post('deskripsi', true);
    $is_active   = (int) !!$this->input->post('is_active');

    $row = $this->db->get_where('kategori_produk_sub',['id'=>$id])->row();
    if (!$row){ echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]); return; }

    $needSlug = (empty($row->slug) || trim($row->nama) !== trim($nama));
    $upd = ['kategori_id'=>$kategori_id,'nama'=>$nama,'deskripsi'=>$deskripsi,'is_active'=>$is_active];
    if ($needSlug){ $upd['slug'] = $this->dm->generate_unique_slug_sub($nama, $id); }

    $ok = $this->db->where('id',$id)->update('kategori_produk_sub', $upd);
    if ($ok) { $this->purge_public_caches(); }
    echo json_encode(["success"=>$ok,"title"=>$ok?"Berhasil":"Gagal","pesan"=>$ok?"Data diupdate":"Gagal update"]);
}

public function sub_hapus(){
    $ids = $this->input->post('id');
    if (!is_array($ids) || count($ids)==0){
        echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Tidak ada data"]); return;
    }
    $ok = true;
    foreach($ids as $id){
        $id = (int)$id; if ($id<=0) continue;
        // contoh: kalau subkategori dipakai produk, cegah hapus (opsional)
        $inuse = $this->db->where('subkategori_id',$id)->count_all_results('produk');
        if ($inuse > 0){ $ok = false; continue; }
        $ok = $ok && $this->db->delete('kategori_produk_sub',['id'=>$id]);
    }
    if ($ok) { $this->purge_public_caches(); }
    echo json_encode(["success"=>$ok,"title"=>$ok?"Berhasil":"Sebagian Gagal","pesan"=>$ok?"Data dihapus":"Sebagian tidak bisa dihapus (masih dipakai produk)"]);
}

}
