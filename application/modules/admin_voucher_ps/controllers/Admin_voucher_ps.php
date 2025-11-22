<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_voucher_ps extends Admin_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('M_admin_voucher_ps','dm');
        $this->load->database();
        cek_session_akses(get_class($this), $this->session->userdata('admin_session'));
    }

    private function _get_setting(){
        $row = $this->db->get_where('ps_setting',['id'=>1])->row();
        if(!$row){
            return (object)[
                'harga_satuan'        => 15000,
                'durasi_unit'         => 60,
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
        $data["title"]      = "Voucher PlayStation";
        $data["subtitle"]   = $this->om->engine_nama_menu(get_class($this));
        $setting            = $this->_get_setting();
        $data["threshold"]  = (int)$setting->free_main_threshold;
        $data["content"]    = $this->load->view($data["controller"]."_view",$data,true);
        $this->render($data);
    }

    public function get_data(){
    $setting   = $this->_get_setting();
    $threshold = (int)$setting->free_main_threshold;
    // NOTE: kalau $threshold <= 0 => program voucher PS nonaktif

    $list = $this->dm->get_data();
    $data = [];

    foreach($list as $r){
        $totalMain   = (int)$r->total_main;
        $belumClaim  = (int)$r->belum_claime;
        $sudahClaim  = (int)$r->sudah_claime;

        if ($threshold > 0) {
            $freeSiap    = (int)floor($belumClaim / $threshold);
            $sisaUntuk1  = $threshold - ($belumClaim % $threshold);
            if($belumClaim === 0){ $sisaUntuk1 = $threshold; }
            $progress    = $belumClaim.' / '.$threshold.' (butuh '.$sisaUntuk1.'x lagi untuk 1 free)';
        } else {
            // Program nonaktif
            $freeSiap    = 0;
            $sisaUntuk1  = 0;
            $progress    = 'Program voucher PS nonaktif';
        }

        $hpShow = $r->no_hp !== '' ? $r->no_hp : '-';

        if ($threshold > 0 && $freeSiap > 0) {
            $btn = '<button type="button" class="btn btn-sm btn-success" onclick="klaim_voucher(\''.htmlspecialchars($r->no_hp,ENT_QUOTES,'UTF-8').'\')">'
                 . '<i class="fe-star me-1"></i>Klaim 1x Free</button>';
        } elseif ($threshold > 0) {
            $btn = '<button type="button" class="btn btn-sm btn-secondary" disabled>'
                 . '<i class="fe-star me-1"></i>Belum Cukup</button>';
        } else {
            $btn = '<button type="button" class="btn btn-sm btn-secondary" disabled>'
                 . '<i class="fe-star me-1"></i>Nonaktif</button>';
        }

        $row = [
           'no'           => '',
           'nama'         => htmlspecialchars((string)$r->nama, ENT_QUOTES, 'UTF-8'),
           'no_hp'        => htmlspecialchars($hpShow, ENT_QUOTES, 'UTF-8'),
           'total_main'   => $totalMain,
           'belum_claime' => $belumClaim,
           'sudah_claime' => $sudahClaim,
           'free_siap'    => $freeSiap,
           'progress'     => $progress,
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


    /** Klaim 1x free untuk nomor HP tertentu (voucher PS) */
    public function klaim(){
    $hp = $this->_normalize_hp($this->input->post('no_hp', true));
    if($hp === null){
        echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Nomor HP tidak valid"]); return;
    }

    $setting   = $this->_get_setting();
    $threshold = (int)$setting->free_main_threshold;

    // Kalau 0 atau kurang, artinya program voucher PS nonaktif
    if($threshold <= 0){
        echo json_encode([
            "success"=>false,
            "title"  =>"Gagal",
            "pesan"  =>"Program voucher PS sedang nonaktif (Jumlah main untuk dapat FREE = 0)."
        ]);
        return;
    }

    // --- lanjut logic lama klaim seperti sebelumnya ---
    // ambil poin aktif (claime = 0) sebanyak threshold
    $q = $this->db->select('id')
                  ->from('voucher_ps')
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
    $ok = $this->db->update('voucher_ps',['claime'=>1]);
    if($ok){
        echo json_encode(["success"=>true,"title"=>"Berhasil","pesan"=>"1 Voucher free PS berhasil diklaim"]);
    }else{
        echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Gagal mengupdate data voucher"]);
    }
}

}
