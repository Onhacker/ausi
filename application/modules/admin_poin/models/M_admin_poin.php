<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_admin_poin extends CI_Model {

    private $table         = 'voucher_cafe';
    // no | nama | no_hp | points | transaksi | total_rupiah | minggu_ke | expired_at | aksi
    private $column_order  = [
        null,                 // no
        'customer_name',      // 1
        'customer_phone',     // 2
        'points',             // 3
        'transaksi_count',    // 4
        'total_rupiah',       // 5
        null,                 // 6 minggu_ke (pakai ekspresi, tidak diorder langsung)
        'expired_at',         // 7
        null                  // 8 aksi
    ];
    private $column_search = ['customer_name','customer_phone'];
    private $order         = ['points' => 'DESC']; // default: poin tertinggi

    private $filter_tahun  = null;
    private $filter_bulan  = null;
    private $filter_minggu = null;

    public function __construct(){
        parent::__construct();
    }

    /**
     * Set filter minggu & bulan (berdasarkan expired_at)
     * $minggu: 1–5 (1= tgl 1–7, 2= 8–14, dst)
     */
    public function set_filter($tahun = 0, $bulan = 0, $minggu = 0){
        $this->filter_tahun  = $tahun > 0 ? (int)$tahun  : null;
        $this->filter_bulan  = $bulan > 0 ? (int)$bulan  : null;
        $this->filter_minggu = $minggu > 0 ? (int)$minggu : null;
    }

    private function _base_q(){
        $this->db->from($this->table);
        $this->db->select("
            id,
            customer_phone,
            customer_name,
            points,
            transaksi_count,
            total_rupiah,
            first_paid_at,
            last_paid_at,
            expired_at,
            token,
            created_at,
            updated_at,
            CEIL(DAY(expired_at) / 7) AS minggu_ke
        ", false);

        // Filter tahun & bulan berdasarkan expired_at
        if ($this->filter_tahun !== null) {
            $this->db->where('YEAR(expired_at)', $this->filter_tahun);
        }
        if ($this->filter_bulan !== null) {
            $this->db->where('MONTH(expired_at)', $this->filter_bulan);
        }
        if ($this->filter_minggu !== null && $this->filter_minggu >= 1 && $this->filter_minggu <= 5) {
            // Minggu ke-n: 1–7, 8–14, 15–21, 22–28, 29–31
            $this->db->where('CEIL(DAY(expired_at) / 7) = '.$this->filter_minggu, null, false);
        }
    }

    private function _build_q(){
        $this->_base_q();

        // Searching
        $search = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
        if($search !== ''){
            $this->db->group_start();
            foreach($this->column_search as $i=>$col){
                if($i === 0){
                    $this->db->like($col, $search);
                } else {
                    $this->db->or_like($col, $search);
                }
            }
            $this->db->group_end();
        }

        // Ordering
        if (isset($_POST['order'])){
            $idx = (int)$_POST['order'][0]['column'];
            $dir = ($_POST['order'][0]['dir'] === 'desc') ? 'DESC' : 'ASC';
            $col = $this->column_order[$idx] ?? null;

            if ($col){
                $this->db->order_by($col, $dir);
            }
        } else {
            // Default: order by points desc
            foreach($this->order as $col => $dir){
                $this->db->order_by($col, $dir);
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
