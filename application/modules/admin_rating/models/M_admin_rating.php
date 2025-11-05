<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_admin_rating extends CI_Model {

    private $table = 'produk_rating pr';

    private $column_order = [
        null,           // no
        'p.nama',       // produk -> urut berdasarkan nama produk
        'pr.stars',     // bintang
        'pr.nama',      // nama pengisi
        'pr.review_at', // ditulis
        'pr.created_at',// dibuat
        null            // aksi
    ];

    private $column_search = [
        'pr.nama', 'pr.client_token', 'pr.review',
        'p.nama'   // <-- hanya kolom yang benar-benar ada
    ];

    private $order = ['pr.created_at'=>'DESC','pr.id'=>'DESC'];

    private $max_rows = 500;
    private $stars_filter = 'all';   // all|1..5
    private $has_review   = 'all';   // all|with|without
    private $from_dt = null; // 'Y-m-d H:i:s'
    private $to_dt   = null; // 'Y-m-d H:i:s'

    public function __construct(){ parent::__construct(); }

    public function set_max_rows($n=500){ $this->max_rows = max(0,(int)$n); }
    public function set_stars_filter($s='all'){ $this->stars_filter = $s ?: 'all'; }
    public function set_has_review_filter($s='all'){ $this->has_review = $s ?: 'all'; }
    public function set_period_filter($from=null, $to=null){
        $this->from_dt = $from ?: null;
        $this->to_dt   = $to   ?: null;
    }

    private function _base_q(){
        $this->db->from($this->table);
        $this->db->select("
            pr.id, pr.produk_id, pr.client_token, pr.nama, pr.stars, pr.review,
            pr.review_at, pr.created_at, pr.updated_at,
            IFNULL(p.nama, CONCAT('#', pr.produk_id)) AS produk_nama
        ", false);
        $this->db->join('produk p', 'p.id = pr.produk_id', 'left');

        // filter bintang
        if ($this->stars_filter !== 'all'){
            $this->db->where('pr.stars', (int)$this->stars_filter);
        }

        // filter ada/tidak ulasan (pakai '' bukan "")
        if ($this->has_review === 'with'){
            $this->db->where("pr.review IS NOT NULL AND pr.review <> ''", null, false);
        } elseif ($this->has_review === 'without'){
            $this->db->group_start();
            $this->db->where("pr.review IS NULL", null, false);
            $this->db->or_where("pr.review = ''", null, false);
            $this->db->group_end();
        }

        // filter periode (review_at fallback created_at)
        if ($this->from_dt && $this->to_dt){
            $this->db->where("COALESCE(pr.review_at, pr.created_at) >=", $this->from_dt);
            $this->db->where("COALESCE(pr.review_at, pr.created_at) <=", $this->to_dt);
        }
    }

    private function _build_q(){
        $this->_base_q();

        // Pencarian
        $search = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
        if ($search !== ''){
            $this->db->group_start();
            foreach($this->column_search as $i=>$col){
                if ($i===0) $this->db->like($col,$search);
                else        $this->db->or_like($col,$search);
            }
            $this->db->group_end();
        }

        // Ordering
        if (isset($_POST['order'])){
            $idx = (int)$_POST['order'][0]['column'];
            $dir = $_POST['order'][0]['dir']==='desc'?'DESC':'ASC';
            $col = $this->column_order[$idx] ?? null;
            if ($col){
                $this->db->order_by($col, $dir);
            }
        } else {
            foreach($this->order as $k=>$v){ $this->db->order_by($k,$v); }
        }
    }

    public function get_data(){
        $this->_build_q();

        $start  = isset($_POST['start'])  ? (int)$_POST['start']  : 0;
        $length = isset($_POST['length']) ? (int)$_POST['length'] : 10;
        if ($length === -1) $length = ($this->max_rows > 0 ? $this->max_rows : 1000000);

        if ($this->max_rows > 0){
            if ($start >= $this->max_rows){ $start = max(0, $this->max_rows - 1); }
            $remain = max(0, $this->max_rows - $start);
            $limit  = max(0, min($length, $remain));
            $this->db->limit($limit, $start);
        } else {
            $this->db->limit($length, $start);
        }

        $q = $this->db->get();
        if ($q === false){
            $err = $this->db->error();
            log_message('error', 'Admin_rating get_data failed: '.($this->db->last_query() ?: 'n/a').' | '.json_encode($err));
            throw new \RuntimeException('DB error ('.$err['code'].'): '.$err['message']);
        }
        return $q->result();
    }

    public function count_filtered(){
        $this->_build_q();
        $filtered = $this->db->count_all_results();
        return ($this->max_rows > 0) ? min($filtered, $this->max_rows) : $filtered;
    }

    public function count_all(){
        $this->_base_q();
        $total = $this->db->count_all_results();
        return ($this->max_rows > 0) ? min($total, $this->max_rows) : $total;
    }

    public function get_one(int $id){
        $q = $this->db->select('pr.*, IFNULL(p.nama, CONCAT("#", pr.produk_id)) AS produk_nama', false)
                      ->from('produk_rating pr')
                      ->join('produk p','p.id=pr.produk_id','left')
                      ->where('pr.id', $id)
                      ->get();
        if ($q === false){
            $err = $this->db->error();
            log_message('error', 'Admin_rating get_one failed: '.json_encode($err));
            return null;
        }
        return $q->row();
    }

    public function update_rating(int $id, array $data){
        return $this->db->where('id',$id)->update('produk_rating', $data);
    }

    public function delete_rating(int $id){
        return $this->db->delete('produk_rating', ['id'=>$id]);
    }
}
