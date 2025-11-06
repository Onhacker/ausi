<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_rating extends Admin_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('M_admin_rating','dm');
        cek_session_akses(get_class($this), $this->session->userdata('admin_session'));
        $this->output->set_header('X-Module: admin_rating');
    }

    public function index(){
        $data["controller"] = get_class($this);
        $data["title"]      = "Riwayat Rating Produk";
        $data["subtitle"]   = "Ulasan & Rating Pelanggan";
        $data["content"]    = $this->load->view('Admin_rating_view',$data,true);
        $this->render($data);
    }

    /** DataTables server-side */
    public function get_data(){
        try{
            // === filters dari form ===
            $stars  = $this->input->post('stars', true) ?: 'all';     // all|1|2|3|4|5
            $stat   = $this->input->post('has_review', true) ?: 'all';// all|with|without

            $from_raw = trim((string)$this->input->post('dt_from', true));
            $to_raw   = trim((string)$this->input->post('dt_to', true));
            $from_dt  = $from_raw !== '' ? date('Y-m-d H:i:s', strtotime($from_raw)) : null;
            $to_dt    = $to_raw   !== '' ? date('Y-m-d H:i:s', strtotime($to_raw))   : null;

            $this->dm->set_max_rows(500);
            $this->dm->set_stars_filter($stars);
            $this->dm->set_has_review_filter($stat);
            $this->dm->set_period_filter($from_dt, $to_dt);

            $list = $this->dm->get_data();
            $data = [];

            foreach($list as $r){
                // produk
                $produk = $r->produk_nama ?: ('#'.$r->produk_id);
                $produk_html = '<div><b>'.htmlspecialchars($produk,ENT_QUOTES,'UTF-8').'</b>'
                             . '<div class="text-muted small">ID: '.(int)$r->produk_id.'</div></div>';

                // stars -> ikon
                $s = (int)$r->stars; if ($s<0) $s=0; if($s>5) $s=5;
                $stars_html = '<div>';
                for($i=1;$i<=5;$i++){
                    $cls = $i <= $s ? 'text-warning' : 'text-muted';
                    $stars_html .= '<i class="mdi mdi-star '.$cls.'"></i>';
                }
                $stars_html .= '<span class="ml-1 badge badge-light">'.$s.'/5</span></div>';

                // nama + token
                $nm = trim((string)$r->nama);
                $token = $r->client_token ? substr($r->client_token,0,8).'…' : '-';
                $nama_html = htmlspecialchars($nm !== '' ? $nm : '—',ENT_QUOTES,'UTF-8')
                           . '<div class="text-muted small"><code>'.$token.'</code></div>';

                // review snippet
                $rev = trim((string)$r->review);
                $rev_short = $rev === '' ? '<span class="badge badge-secondary">Tanpa ulasan</span>'
                    : nl2br(htmlspecialchars(mb_strimwidth($rev,0,140,'…','UTF-8'),ENT_QUOTES,'UTF-8'));

                // tanggal
                $reviewed  = $r->review_at ? date('d-m-Y H:i', strtotime($r->review_at)) : '-';
                $created   = $r->created_at ? date('d-m-Y H:i', strtotime($r->created_at)) : '-';

                // actions
                                $unameLower = strtolower((string)$this->session->userdata('admin_username'));
                $isAdmin    = ($unameLower === 'admin');

                $btnEdit = $isAdmin
                  ? '<button type="button" class="btn btn-sm btn-primary mr-1" onclick="open_edit('.$idInt.')"><i class="fe-edit"></i></button>'
                  : '<button type="button" class="btn btn-sm btn-outline-secondary mr-1" disabled title="Hanya admin dapat mengubah"><i class="fe-lock"></i></button>';

                $btnDelete = $isAdmin
                  ? '<button type="button" class="btn btn-sm btn-danger" onclick="do_delete('.$idInt.')"><i class="fe-trash-2"></i></button>'
                  : '<button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Hanya admin dapat menghapus"><i class="fe-lock"></i></button>';

                $aksi = '<div class="btn-group btn-group-sm" role="group">'.$btnEdit.$btnDelete.'</div>';


                $row = [];
                $row['id']         = $idInt;
                $row['no']         = '';
                $row['produk']     = $produk_html;
                $row['stars']      = $stars_html;
                $row['nama']       = $nama_html;
                $row['review']     = $rev_short;
                $row['review_at']  = htmlspecialchars($reviewed,ENT_QUOTES,'UTF-8');
                $row['created_at'] = htmlspecialchars($created,ENT_QUOTES,'UTF-8');
                $row['aksi']       = $aksi;

                $data[] = $row;
            }

            $out = [
                "draw"            => (int)$this->input->post('draw'),
                "recordsTotal"    => $this->dm->count_all(),
                "recordsFiltered" => $this->dm->count_filtered(),
                "data"            => $data,
            ];
            
            return $this->output->set_content_type('application/json')->set_output(json_encode($out));
        } catch(\Throwable $e){
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode([
                    "draw" => (int)$this->input->post('draw'),
                    "recordsTotal"=>0,"recordsFiltered"=>0,"data"=>[],
                    "error"=>"Server error: ".$e->getMessage()
                ]));
        }
    }

    /** Ambil satu baris untuk modal edit */
    public function get_one($id=null){
        $id = (int)$id;
        $row = $this->dm->get_one($id);
        if (!$row){
            return $this->output->set_content_type('application/json')->set_output(json_encode([
                "success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"
            ]));
        }
        return $this->output->set_content_type('application/json')->set_output(json_encode([
            "success"=>true, "data"=>$row
        ]));
    }

    /** Simpan (update) rating */
    public function save(){
        try{
            $id   = (int)$this->input->post('id');
            $nama = trim((string)$this->input->post('nama', true));
            $stars = (int)$this->input->post('stars');
            $review = trim((string)$this->input->post('review', false));
            $review_at_raw = trim((string)$this->input->post('review_at', true)); // "Y-m-d H:i:s" atau kosong

            if ($stars < 0) $stars = 0;
            if ($stars > 5) $stars = 5;

            $data = [
                'nama'       => ($nama !== '' ? mb_substr($nama,0,60) : null),
                'stars'      => $stars,
                'review'     => ($review !== '' ? $review : null),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($review_at_raw !== ''){
                $ts = strtotime($review_at_raw);
                $data['review_at'] = $ts ? date('Y-m-d H:i:s',$ts) : null;
            } else {
                // boleh null-kan
                $data['review_at'] = null;
            }

            $ok = $this->dm->update_rating($id, $data);
            if (!$ok){
                return $this->output->set_content_type('application/json')->set_output(json_encode([
                    "success"=>false,"title"=>"Gagal","pesan"=>"Tidak dapat menyimpan data"
                ]));
            }
            return $this->output->set_content_type('application/json')->set_output(json_encode([
                "success"=>true,"title"=>"Berhasil","pesan"=>"Perubahan disimpan"
            ]));
        } catch(\Throwable $e){
            return $this->output->set_content_type('application/json')->set_output(json_encode([
                "success"=>false,"title"=>"Error","pesan"=>$e->getMessage()
            ]));
        }
    }

    /** Hapus rating (admin only) */
    public function delete($id=null){
        $unameLower = strtolower((string)$this->session->userdata('admin_username'));
        if ($unameLower !== 'admin'){
            return $this->output->set_content_type('application/json')->set_output(json_encode([
                "success"=>false,"title"=>"Ditolak","pesan"=>"Hanya admin yang dapat menghapus"
            ]));
        }
        $id = (int)$id;
        $ok = $this->dm->delete_rating($id);
        return $this->output->set_content_type('application/json')->set_output(json_encode(
            $ok ? ["success"=>true,"title"=>"Terhapus","pesan"=>"Data berhasil dihapus"]
                : ["success"=>false,"title"=>"Gagal","pesan"=>"Tidak dapat menghapus data"]
        ));
    }
}
