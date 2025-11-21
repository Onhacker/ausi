<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_voucher_kursi_pijat extends Admin_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('M_admin_voucher_kursi_pijat','dm');
        $this->load->database();
        cek_session_akses(get_class($this), $this->session->userdata('admin_session'));
    }

    private function _get_setting(){
        $row = $this->db->get_where('kursi_pijat_setting',['id'=>1])->row();
        if(!$row){
            return (object)[
                'harga_satuan'        => 20000,
                'durasi_unit'         => 15,
                'free_main_threshold' => 10,
            ];
        }
        if (!isset($row->free_main_threshold)) {
            $row->free_main_threshold = 10;
        }
        return $row;
    }

    private function _normalize_hp($hp){
        $hp = preg_replace('/\D+/', '', (string)$hp);
        return $hp === '' ? null : $hp;
    }

    public function index(){
        $data["controller"] = get_class($this);
        $data["title"]      = "Voucher Kursi Pijat";
        $data["subtitle"]   = $this->om->engine_nama_menu(get_class($this));
        $setting            = $this->_get_setting();
        $data["threshold"]  = (int)$setting->free_main_threshold;
        $data["content"]    = $this->load->view($data["controller"]."_view",$data,true);
        $this->render($data);
    }

    public function get_data(){
        $setting   = $this->_get_setting();
        $threshold = (int)$setting->free_main_threshold;
        if($threshold <= 0){ $threshold = 1; }

        $list = $this->dm->get_data();
        $data = [];

        foreach($list as $r){
            $totalMain   = (int)$r->total_main;
            $belumClaim  = (int)$r->belum_claime;
            $sudahClaim  = (int)$r->sudah_claime;
            $freeSiap    = (int)floor($belumClaim / $threshold);
            $sisaUntuk1  = $threshold - ($belumClaim % $threshold);
            if($belumClaim === 0){ $sisaUntuk1 = $threshold; }

            $hpShow = $r->no_hp !== '' ? $r->no_hp : '-';

            if($freeSiap > 0){
                $btn = '<button type="button" class="btn btn-sm btn-success" onclick="klaim_voucher(\''.htmlspecialchars($r->no_hp,ENT_QUOTES,'UTF-8').'\')">'
                     . '<i class="fe-star me-1"></i>Klaim 1x Free</button>';
            }else{
                $btn = '<button type="button" class="btn btn-sm btn-secondary" disabled>'
                     . '<i class="fe-star me-1"></i>Belum Cukup</button>';
            }

            $row = [
               'no'           => '',
               'nama'         => htmlspecialchars((string)$r->nama, ENT_QUOTES, 'UTF-8'),
               'no_hp'        => htmlspecialchars($hpShow, ENT_QUOTES, 'UTF-8'),
               'total_main'   => $totalMain,
               'belum_claime' => $belumClaim,
               'sudah_claime' => $sudahClaim,
               'free_siap'    => $freeSiap,
               'progress'     => $belumClaim.' / '.$threshold.' (butuh '.$sisaUntuk1.'x lagi untuk 1 free)',
               'aksi'         => $btn,
            ];
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

    /** Klaim 1x free untuk nomor HP tertentu */
    public function klaim(){
        $hp = $this->_normalize_hp($this->input->post('no_hp', true));
        if($hp === null){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Nomor HP tidak valid"]); return;
        }

        $setting   = $this->_get_setting();
        $threshold = (int)$setting->free_main_threshold;
        if($threshold <= 0){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Konfigurasi jumlah main untuk free belum benar"]); return;
        }

        // ambil poin aktif (claime = 0) sebanyak threshold
        $q = $this->db->select('id')
                      ->from('voucher_kursi_pijat')
                      ->where('no_hp',$hp)
                      ->where('claime',0)
                      ->order_by('id','ASC')
                      ->limit($threshold)
                      ->get();

        $rows = $q->result();
        if(count($rows) < $threshold){
            echo json_encode([
                "success"=>false,
                "title"  =>"Belum Cukup",
                "pesan"  =>"Jumlah main aktif belum mencapai batas free."
            ]);
            return;
        }

        $ids = array_map(function($r){ return (int)$r->id; }, $rows);
        if(empty($ids)){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data voucher tidak ditemukan"]); return;
        }

        $this->db->where_in('id',$ids);
        $ok = $this->db->update('voucher_kursi_pijat',['claime'=>1]);
        if($ok){
            echo json_encode(["success"=>true,"title"=>"Berhasil","pesan"=>"1 Voucher free berhasil diklaim"]);
        }else{
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Gagal mengupdate data voucher"]);
        }
    }
}
