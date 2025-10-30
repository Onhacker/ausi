<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_admin_kurir extends CI_Model {

    private $table         = 'kurir k';
    // harus sinkron dengan urutan columns[] di view
    // 0 cek, 1 no, 2 nama, 3 kontak, 4 kendaraan, 5 status, 6 on_trip_count, 7 aksi
    private $column_order  = [null, null, 'k.nama','k.phone','k.vehicle','k.status','k.on_trip_count', null];
    private $column_search = ['k.nama','k.phone','k.vehicle','k.plate','k.status'];
    private $order         = ['k.id' => 'DESC'];

    public function __construct(){
        parent::__construct();
    }

    private function _base_q(){
        $this->db->from($this->table);
    }

    private function _build_q(){
        $this->_base_q();

        // search global
        $search = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
        if ($search !== '') {
            $this->db->group_start();
            foreach ($this->column_search as $i => $col) {
                if ($i === 0) $this->db->like($col, $search);
                else          $this->db->or_like($col, $search);
            }
            $this->db->group_end();
        }

        // order kolom
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
            $this->db->limit(
                (int)$_POST['length'],
                (int)$_POST['start']
            );
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
