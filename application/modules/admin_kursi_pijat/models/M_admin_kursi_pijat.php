<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_admin_kursi_pijat extends CI_Model {

    private $table         = 'kursi_pijat_transaksi kp';
    //           cek   no     nama           tanggal         durasi            sesi          total              status
    private $column_order  = [null, null, 'kp.nama',       'kp.created_at', 'kp.durasi_menit', 'kp.sesi', 'kp.total_harga', 'kp.status'];
    private $column_search = ['kp.nama', 'kp.status', 'kp.catatan', 'kp.created_at']; // ⬅️ tambah created_at
    private $order         = ['kp.created_at' => 'DESC']; // sudah OK untuk default

    public function __construct(){ parent::__construct(); }

    private function _base_q(){ $this->db->from($this->table); }

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
            foreach ($this->order as $col => $dir) { $this->db->order_by($col,$dir); }
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

