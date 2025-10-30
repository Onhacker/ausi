<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_pengumuman extends Admin_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('M_admin_pengumuman','dm');
        $this->load->helper(['url','text']);
        cek_session_akses(get_class($this), $this->session->userdata('admin_session'));
    }

    /** Purge cache publik (dipanggil setiap CRUD berhasil) */
    private function purge_public_caches()
    {
        $this->load->driver('cache', ['adapter' => 'file']);
        // ❌ JANGAN $this->cache->clean(); supaya cache lain tidak ikut terhapus.
        // ✅ Cukup bump versi pengumuman (ETag/versi list publik akan berubah)
        $this->cache->save('pengumuman_ver', time(), 365*24*3600);
        $this->output->set_header('X-Cache-Purged: pengumuman');
    }

    public function index(){
        $data["controller"] = get_class($this);
        $data["title"]      = "Master";
        $data["subtitle"]   = $this->om->engine_nama_menu(get_class($this));
        $data["content"]    = $this->load->view($data["controller"]."_view",$data,true);
        $this->render($data);
    }

    /** DataTables server-side */
    public function get_dataa()
    {
        $list = $this->dm->get_data();
        $data = [];
        foreach ($list as $r) {
            $row = [];
            $row['cek']      = '<div class="checkbox checkbox-primary checkbox-single">'
                             . '<input type="checkbox" class="data-check" value="'.(int)$r->id.'"><label></label>'
                             . '</div>';
            $row['no']       = ''; // diisi rowCallback di DataTable (index)
            $row['judul']    = htmlspecialchars($r->judul, ENT_QUOTES, 'UTF-8');
            $row['tanggal']  = htmlspecialchars(tgl_view($r->tanggal), ENT_QUOTES, 'UTF-8');
            $row['username'] = htmlspecialchars($r->username, ENT_QUOTES, 'UTF-8');

            $btnEdit  = '<button type="button" class="btn btn-sm btn-warning" onclick="edit('.(int)$r->id.')"><i class="fe-edit"></i> Edit</button>';
            $row['aksi'] = $btnEdit;

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
        $row = $this->db->get_where('pengumuman',['id'=>$id])->row();
        if (!$row) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]); return;
        }
        echo json_encode(["success"=>true,"data"=>$row]);
    }

    /** Helper slug ringkas (kalau butuh lokal) */
    private function _slugify($s){
        $base = url_title(convert_accented_characters($s), '-', true);
        if ($base === '') $base = 'pengumuman';
        return substr($base, 0, 170); // sisakan space untuk suffix jika perlu
    }

    /** Create */
    public function add()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('judul','Judul','trim|required|min_length[3]|max_length[255]');
        $this->form_validation->set_rules('tanggal','Tanggal','required');

        if ($this->form_validation->run() !== TRUE) {
            echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>validation_errors()]); return;
        }

        $judul    = $this->input->post('judul', true);
        $tanggal  = $this->input->post('tanggal', true);
        $isi      = $this->input->post('isi', false); // HTML OK
        $username = $this->session->userdata('admin_username') ?: 'admin';

        // slug unik dari model
        $slug = $this->dm->generate_unique_slug($judul);

        $ok = $this->db->insert('pengumuman', [
            'judul'     => $judul,
            'isi'       => $isi,
            'tanggal'   => $tanggal,
            'username'  => $username,
            'link_seo'  => $slug,
        ]);

        if ($ok) { $this->purge_public_caches(); }

        echo json_encode([
            "success"=>$ok,
            "title"=>$ok?"Berhasil":"Gagal",
            "pesan"=>$ok?"Data berhasil disimpan":"Data gagal disimpan"
        ]);
    }

    /** Update */
    public function update()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('id','ID','required|integer');
        $this->form_validation->set_rules('judul','Judul','trim|required|min_length[3]|max_length[255]');
        $this->form_validation->set_rules('tanggal','Tanggal','required');

        if ($this->form_validation->run() !== TRUE) {
            echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>validation_errors()]); return;
        }

        $id      = (int)$this->input->post('id', true);
        $judul   = $this->input->post('judul', true);
        $tanggal = $this->input->post('tanggal', true);
        $isi     = $this->input->post('isi', false);

        $row = $this->db->get_where('pengumuman',['id'=>$id])->row();
        if (!$row) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]); return;
        }

        // Apakah perlu regen slug?
        $needSlug = (empty($row->link_seo) || trim($row->judul) !== trim($judul));

        $data_update = [
            'judul'   => $judul,
            'isi'     => $isi,
            'tanggal' => $tanggal,
        ];

        if ($needSlug) {
            $data_update['link_seo'] = $this->dm->generate_unique_slug($judul, $id);
        }

        $ok = $this->db->where('id',$id)->update('pengumuman', $data_update);

        if ($ok) { $this->purge_public_caches(); }

        echo json_encode([
            "success"=>$ok,
            "title"=>$ok?"Berhasil":"Gagal",
            "pesan"=>$ok?"Data berhasil diupdate":"Data gagal diupdate"
        ]);
    }

    /** Delete (bulk) */
    public function hapus_data()
    {
        $ids = $this->input->post('id');
        if (!is_array($ids) || count($ids) === 0) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Tidak ada data"]); return;
        }

        $ok = true;
        foreach ($ids as $id) {
            $id = (int)$id;
            if ($id <= 0) continue;
            $ok = $ok && $this->db->delete('pengumuman', ['id'=>$id]);
        }

        if ($ok) { $this->purge_public_caches(); }

        echo json_encode([
            "success"=>$ok,
            "title"=>$ok?"Berhasil":"Gagal",
            "pesan"=>$ok?"Data berhasil dihapus":"Sebagian data gagal dihapus"
        ]);
    }
}
