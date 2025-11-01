<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_admin_meja_billiard extends CI_Model {

    private $table         = 'meja_billiard b';
    // index kolom harus sinkron dengan DataTables columns[] di view
    // 0 cek, 1 no, 2 nama_meja, 3 kategori, 4 harga_per_jam, 5 aktif, 6 updated_at, 7 aksi
    private $column_order  = [
        null,
        null,
        'b.nama_meja',
        'b.kategori',
        'b.harga_per_jam',
        'b.aktif',
        'b.updated_at',
        null
    ];
    private $column_search = ['b.nama_meja','b.catatan','b.kategori'];
    private $order         = ['b.id_meja' => 'DESC'];

    public function __construct(){
        parent::__construct();
    }

    private function _base_q(){
        $this->db->from($this->table);
    }

    private function _build_q(){
        $this->_base_q();

        $search = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
        if ($search !== '') {
            $this->db->group_start();
            foreach ($this->column_search as $i => $col) {
                if ($i === 0) $this->db->like($col, $search);
                else          $this->db->or_like($col, $search);
            }
            $this->db->group_end();
        }

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
