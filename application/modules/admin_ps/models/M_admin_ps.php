<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_admin_ps extends CI_Model {

    private $table         = 'ps_transaksi ps';
    // cek | no | nama | no_hp | tanggal | durasi | sesi | total | status
    private $column_order  = [
        null,
        null,
        'ps.nama',
        'ps.no_hp',
        'ps.created_at',
        'ps.durasi_menit',
        'ps.sesi',
        'ps.total_harga',
        'ps.status'
    ];
    private $column_search = ['ps.nama','ps.no_hp','ps.status','ps.catatan','ps.created_at'];
    private $order         = ['ps.created_at' => 'DESC'];

    public function __construct(){ parent::__construct(); }

    private function _base_q(){
        $this->db->from($this->table);
    }

    private function _build_q(){
        $this->_base_q();

        // Searching
        $search = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
        if ($search !== '') {
            $this->db->group_start();
            foreach ($this->column_search as $i=>$col) {
                if ($i === 0) $this->db->like($col, $search);
                else          $this->db->or_like($col, $search);
            }
            $this->db->group_end();
        }

        // Ordering
        if (isset($_POST['order'])) {
            $idx = (int)$_POST['order'][0]['column'];
            $dir = $_POST['order'][0]['dir'] === 'desc' ? 'DESC' : 'ASC';
            $col = $this->column_order[$idx] ?? key($this->order);
            if ($col) $this->db->order_by($col, $dir);
        } else {
            foreach ($this->order as $col => $dir) {
                $this->db->order_by($col,$dir);
            }
        }
    }

    public function get_data(){
        $this->_build_q();
        if (isset($_POST['length']) && $_POST['length'] != -1) {
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
