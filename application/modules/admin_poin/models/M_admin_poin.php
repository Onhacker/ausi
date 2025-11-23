<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_admin_poin extends CI_Model {

    private $table         = 'voucher_cafe';
    // no | nama | no_hp | points | transaksi | total_rupiah | minggu_ke | expired_at | aksi
    private $column_order  = [
        null,                 // 0 no
        'customer_name',      // 1
        'customer_phone',     // 2
        'points',             // 3
        'transaksi_count',    // 4
        'total_rupiah',       // 5
        null,                 // 6 minggu_ke (expr)
        'expired_at',         // 7
        null                  // 8 aksi
    ];
    private $column_search = ['customer_name','customer_phone'];
    private $order         = ['points' => 'DESC']; // default: poin tertinggi

    private $filter_tahun   = null;
    private $filter_bulan   = null;
    private $filter_minggu  = null; // di-set otomatis dari DB (minggu terakhir)
    private $periode_label  = '';

    public function __construct(){
        parent::__construct();
    }

    /**
     * Set filter tahun & bulan.
     * Minggu terakhir di bulan tsb dihitung otomatis dari expired_at.
     */
    public function set_filter($tahun = 0, $bulan = 0){
        $this->filter_tahun  = $tahun > 0 ? (int)$tahun  : null;
        $this->filter_bulan  = $bulan > 0 ? (int)$bulan  : null;
        $this->filter_minggu = null;
        $this->periode_label = 'Periode: Semua data';

        // Kalau tahun & bulan diisi, cari minggu terakhir yang punya data
        if ($this->filter_tahun !== null && $this->filter_bulan !== null) {
            $sql = "
                SELECT expired_at
                FROM {$this->table}
                WHERE expired_at IS NOT NULL
                  AND expired_at <> '0000-00-00'
                  AND YEAR(expired_at) = ?
                  AND MONTH(expired_at) = ?
                ORDER BY expired_at DESC
                LIMIT 1
            ";
            $row = $this->db->query($sql, [$this->filter_tahun, $this->filter_bulan])->row();
            if ($row && !empty($row->expired_at)) {
                $ts   = strtotime($row->expired_at);
                $day  = (int)date('j', $ts);
                $week = (int)ceil($day / 7);

                $this->filter_minggu = $week;

                // Hitung range tanggal minggu tsb
                $startDay = ($week - 1) * 7 + 1;
                // gunakan date('t') biar tidak perlu ekstensi calendar
                $daysInMonth = (int)date('t', strtotime($this->filter_tahun.'-'.$this->filter_bulan.'-01'));
                if ($startDay > $daysInMonth) {
                    $startDay = $daysInMonth;
                }
                $endDay   = min($week * 7, $daysInMonth);

                $bulanLabel = $this->_indo_bulan($this->filter_bulan);
                $this->periode_label = "Periode: Minggu ke-{$week} (tgl {$startDay}-{$endDay}) {$bulanLabel} {$this->filter_tahun}";
            } else {
                $bulanLabel = $this->_indo_bulan($this->filter_bulan);
                $this->periode_label = "Periode: Tidak ada data untuk {$bulanLabel} {$this->filter_tahun}";
            }
        }
    }

    public function get_periode_label(){
        return $this->periode_label;
    }

    private function _indo_bulan($m){
        $m = (int)$m;
        $bulan = [
          1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
          5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
          9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
        ];
        return isset($bulan[$m]) ? $bulan[$m] : $m;
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

        if ($this->filter_tahun !== null) {
            $this->db->where('YEAR(expired_at)', $this->filter_tahun);
        }
        if ($this->filter_bulan !== null) {
            $this->db->where('MONTH(expired_at)', $this->filter_bulan);
        }
        if ($this->filter_minggu !== null) {
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
