<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_riwayat_billiard extends CI_Model {

    private $table = 'billiard_paid bp';

    private $column_order  = [
        null,               // no
        'bp.kode_booking',  // kode
        'bp.nama_meja',     // meja
        'bp.paid_at',       // dibayar
        'bp.tanggal',       // waktu main (tgl)
        'bp.harga_per_jam', // harga/jam
        'bp.grand_total',   // grand
        'bp.metode_bayar',  // metode
        null                // aksi
    ];

    private $column_search = ['bp.kode_booking','bp.nama','bp.no_hp','bp.nama_meja','bp.metode_bayar'];
    private $order         = ['bp.paid_at'=>'DESC','bp.id_paid'=>'DESC'];

    private $max_rows = 500;
    private $paid_method_filter = 'all'; // all | qris | cash | transfer | ...

    public function __construct(){ parent::__construct(); }

    public function set_max_rows($n = 500){ $this->max_rows = max(0,(int)$n); }
    public function set_paid_method_filter($m='all'){ $this->paid_method_filter = $m ?: 'all'; }

    private function _base_q(){
        $this->db->from($this->table);
        $this->db->select('
            bp.id_paid, bp.id_pesanan, bp.kode_booking,
            bp.nama, bp.no_hp,
            bp.meja_id, bp.nama_meja,
            bp.tanggal, bp.jam_mulai, bp.jam_selesai, bp.durasi_jam,
            bp.harga_per_jam, bp.subtotal, bp.kode_unik, bp.grand_total,
            bp.metode_bayar, bp.access_token, bp.paid_at, bp.source
        ');

        if ($this->paid_method_filter !== 'all'){
            $this->db->where('bp.metode_bayar', $this->paid_method_filter);
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
            $this->db->order_by('bp.paid_at','DESC');
            $this->db->order_by('bp.id_paid','DESC');
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

    public function get_paid_row(int $id_paid){
        return $this->db->get_where('billiard_paid', ['id_paid'=>$id_paid])->row();
    }
}
