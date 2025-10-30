<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_admin_unit_tujuan extends CI_Model {

    private $table = 'unit_tujuan';
    private $order = ['id'=>'DESC'];
    private $col_order = ['', '', 'id', 'nama_unit', 'parent_nama', 'nama_pejabat', 'no_hp', 'kuota_harian', 'jumlah_pendamping', 'updated_at'];

    public function __construct(){ parent::__construct(); }

    private function _base_q(){
        $this->db->select('u.*, p.nama_unit AS parent_nama');
        $this->db->from('unit_tujuan u');
        $this->db->join('unit_tujuan p', 'p.id = u.parent_id', 'left');
    }

    private function _build_datatable_q(){
        $this->_base_q();

        // searching
        $search = $_POST['search']['value'] ?? '';
        if ($search !== '') {
            $this->db->group_start()
                ->like('u.nama_unit', $search)
                ->or_like('u.nama_pejabat', $search)
                ->or_like('u.no_hp', $search)
                ->or_like('p.nama_unit', $search)
            ->group_end();
        }

        // ordering
        if (isset($_POST['order'][0]['column'])) {
            $idx = (int)$_POST['order'][0]['column'];
            $dir = $_POST['order'][0]['dir'] ?? 'desc';
            $col = $this->col_order[$idx] ?? 'id';
            if ($col === 'parent_nama') $col = 'p.nama_unit';
            $this->db->order_by($col, $dir);
        } else {
            $this->db->order_by(key($this->order), current($this->order));
        }
    }

    public function get_data(){
        $this->_build_datatable_q();
        if (($_POST['length'] ?? -1) != -1) {
            $this->db->limit((int)$_POST['length'], (int)$_POST['start']);
        }
        return $this->db->get()->result();
    }

    public function count_filtered(){
        $this->_build_datatable_q();
        return $this->db->get()->num_rows();
    }

    public function count_all(){
        return $this->db->count_all($this->table);
    }

    public function get_by_id($id){
        $this->_base_q();
        $this->db->where('u.id',(int)$id);
        $row = $this->db->get()->row_array();
        return $row;
    }

    /* dropdown parent (server search select2) */
    public function search_parent($q=''){
        $this->db->select('id, nama_unit');
        $this->db->from('unit_tujuan');
        if ($q !== '') $this->db->like('nama_unit', $q);
        $this->db->order_by('nama_unit','ASC');
        $this->db->limit(50);
        return $this->db->get()->result();
    }

    /* dropdown statis (jika ingin render php) */
    public function arr_parent(){
        $this->db->order_by('nama_unit','ASC');
        $res = $this->db->get('unit_tujuan')->result();
        $arr = [""=>"— Root (tanpa parent) —"];
        foreach($res as $r){
            $arr[$r->id] = "[#{$r->id}] ".$r->nama_unit;
        }
        return $arr;
    }
}
