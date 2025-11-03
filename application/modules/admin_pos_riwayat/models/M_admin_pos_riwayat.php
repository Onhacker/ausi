<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_admin_pos_riwayat extends CI_Model {

    private $table = 'pesanan_paid pp';

    private $column_order = [
        null,               // no
        'pp.nomor',         // nomor
        'pp.mode',          // mode
        'pp.meja_nama',     // meja
        'pp.archived_at',   // pembayaran diterima
        'pp.total',         // subtotal
        'pp.grand_total',   // grand
        'pp.paid_method',   // metode
        null                // aksi
    ];

    private $column_search = ['pp.nomor','pp.nama','pp.customer_phone','pp.meja_nama','pp.meja_kode','pp.mode','pp.paid_method'];
    private $order         = ['pp.archived_at'=>'DESC','pp.id'=>'DESC'];

    private $max_rows = 500;
    private $paid_method_filter = 'all'; // all | cash | qris | transfer | dll
    private $mode_filter = 'all';        // all | walkin | dinein | delivery

    // === baru: filter periode ===
    private $from_dt = null; // 'Y-m-d H:i:s'
    private $to_dt   = null; // 'Y-m-d H:i:s'

    public function __construct(){ parent::__construct(); }

    public function set_max_rows($n=500){ $this->max_rows = max(0,(int)$n); }
    public function set_paid_method_filter($m='all'){ $this->paid_method_filter = $m ?: 'all'; }
    public function set_mode_filter($m='all'){ $this->mode_filter = $m ?: 'all'; }
    public function set_period_filter($from=null, $to=null){
        $this->from_dt = $from ?: null;
        $this->to_dt   = $to   ?: null;
    }

    private function _base_q(){
        $this->db->from($this->table);
        $this->db->select('
            pp.id, pp.src_id, pp.nomor, pp.mode,
            pp.meja_kode, pp.meja_nama,
            pp.nama, pp.customer_phone,
            pp.total, pp.kode_unik, pp.grand_total, pp.delivery_fee,
            pp.status, pp.paid_method,
            pp.paid_at, pp.created_at, pp.updated_at, pp.archived_at
        ');

        // filter metode
        if ($this->paid_method_filter !== 'all'){
            $this->db->where('pp.paid_method', $this->paid_method_filter);
        }
        // filter mode
        if ($this->mode_filter !== 'all'){
            $this->db->where('pp.mode', $this->mode_filter);
        }
        // filter periode (pakai archived_at, fallback paid_at)
        if ($this->from_dt && $this->to_dt){
            $this->db->group_start();
            // NB: COALESCE() dipakai aman di Query Builder sebagai raw field
            $this->db->where("COALESCE(pp.archived_at, pp.paid_at) >=", $this->from_dt);
            $this->db->where("COALESCE(pp.archived_at, pp.paid_at) <=", $this->to_dt);
            $this->db->group_end();
        }
    }

    private function _build_q(){
        $this->_base_q();

        // Search
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
            if ($col){ $this->db->order_by($col,$dir); }
        } else {
            $this->db->order_by('pp.archived_at','DESC'); // default → Pembayaran Diterima terbaru
            $this->db->order_by('pp.id','DESC');
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

        return $this->db->get()->result();
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

    /** Detail header + items dari tabel *_paid */
    public function get_paid_with_items(int $paid_id){
        $order = $this->db->get_where('pesanan_paid', ['id'=>$paid_id])->row();
        if (!$order) return null;

        $items = $this->db->select('*')->from('pesanan_item_paid')
                          ->where('pesanan_paid_id', (int)$paid_id)
                          ->order_by('id','ASC')->get()->result();
        return ['order'=>$order, 'items'=>$items];
    }
}
