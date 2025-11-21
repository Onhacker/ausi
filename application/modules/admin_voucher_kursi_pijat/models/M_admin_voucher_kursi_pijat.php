<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_admin_voucher_kursi_pijat extends CI_Model {

    private $table         = 'voucher_kursi_pijat v';
    // no | nama | no_hp | total_main | belum_claime | free_siap | progress | aksi
    private $column_order  = [
        null,
        'v.nama',
        'v.no_hp',
        'total_main',
        'belum_claime',
        'sudah_claime',
        'free_siap',
        null
    ];
    private $column_search = ['v.nama','v.no_hp'];
    private $order         = ['v.no_hp' => 'ASC'];

    public function __construct(){ parent::__construct(); }

    private function _base_q(){
        $this->db->from($this->table);
        $this->db->select("
            v.no_hp,
            MAX(v.nama) AS nama,
            COUNT(*) AS total_main,
            SUM(CASE WHEN v.claime = 0 THEN 1 ELSE 0 END) AS belum_claime,
            SUM(CASE WHEN v.claime = 1 THEN 1 ELSE 0 END) AS sudah_claime
        ", false);
        $this->db->group_by('v.no_hp');
    }

    private function _build_q(){
        $this->_base_q();

        $search = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
        if($search !== ''){
            $this->db->group_start();
            foreach($this->column_search as $i=>$col){
                if($i===0) $this->db->like($col,$search);
                else       $this->db->or_like($col,$search);
            }
            $this->db->group_end();
        }

        if(isset($_POST['order'])){
            $idx = (int)$_POST['order'][0]['column'];
            $dir = $_POST['order'][0]['dir'] === 'desc' ? 'DESC' : 'ASC';
            $col = $this->column_order[$idx] ?? key($this->order);
            if($col){
                $this->db->order_by($col,$dir);
            }
        }else{
            foreach($this->order as $col=>$dir){
                $this->db->order_by($col,$dir);
            }
        }
    }

    public function get_data(){
        $this->_build_q();
        if(isset($_POST['length']) && $_POST['length'] != -1){
            $this->db->limit((int)$_POST['length'], (int)$_POST['start']);
        }
        return $this->db->get()->result();
    }

    public function count_filtered(){
        $this->_build_q();
        return $this->db->get()->num_rows();
    }

    public function count_all(){
        $this->_base_q();
        return $this->db->count_all_results();
    }
}
