<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_admin_meja extends CI_Model {

    private $table         = 'meja m';
    private $column_order  = [null, null, 'm.nama','m.kode','m.kapasitas','m.area','m.status', null, null];
    private $column_search = ['m.nama','m.kode','m.area','m.status'];
    private $order         = ['m.id' => 'DESC'];

    public function __construct(){ parent::__construct(); }

    private function _base_q(){ $this->db->from($this->table); }

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

    public function count_filtered(){ $this->_build_q(); return $this->db->get()->num_rows(); }
    public function count_all(){ $this->_base_q(); return $this->db->count_all_results(); }

    /** Kode unik: M001, M002, ... atau MEJA001 dst dari nama */
    public function generate_unique_kode($nama, $ignore_id = null){
        $base = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string)$nama));
        if ($base === '') $base = 'M';
        $base = substr($base, 0, 6); // pendek
        if (strlen($base) < 1) $base = 'M';

        // coba urutan 001..999
        for($i=1; $i<=9999; $i++){
            $kode = $base . str_pad((string)$i, max(3, 4 - (int)(strlen($base)/3)), '0', STR_PAD_LEFT);
            $this->db->from('meja')->where('kode', $kode);
            if ($ignore_id) $this->db->where('id !=', (int)$ignore_id);
            if ((int)$this->db->count_all_results() === 0) return $kode;
        }

        // fallback random
        do {
            $kode = $base . strtoupper(substr(md5(uniqid('',true)),0,4));
            $this->db->from('meja')->where('kode', $kode);
            if ($ignore_id) $this->db->where('id !=', (int)$ignore_id);
        } while ((int)$this->db->count_all_results() > 0);

        return $kode;
    }
}
