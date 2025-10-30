<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_admin_pengumuman extends CI_Model {

    private $table          = 'pengumuman p';
    private $column_order   = [null, null, 'p.judul', 'p.tanggal', 'p.username', null];
    private $column_search  = ['p.judul', 'p.username', 'p.tanggal'];
    private $order          = ['p.tanggal' => 'DESC', 'p.id' => 'DESC'];

    public function __construct(){ parent::__construct(); }

    private function _base_q(){
        $this->db->from($this->table);
    }

    private function _build_q(){
        $this->_base_q();

        // searching
        $search = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
        if ($search !== '') {
            $this->db->group_start();
            foreach ($this->column_search as $i => $col) {
                if ($i === 0) $this->db->like($col, $search);
                else          $this->db->or_like($col, $search);
            }
            $this->db->group_end();
        }

        // ordering
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

    /** Generate slug unik; jika $ignore_id diisi, exclude id tsb (update) */
    public function generate_unique_slug($judul, $ignore_id = null)
    {
        $this->load->helper('url');

        $base = url_title(convert_accented_characters($judul), '-', true);
        if ($base === '') $base = 'pengumuman';
        $base = substr($base, 0, 170);

        $slug = $base;
        $i = 1;

        while (true) {
            $this->db->from('pengumuman')->where('link_seo', $slug);
            if ($ignore_id) $this->db->where('id !=', (int)$ignore_id);

            $exists = (int)$this->db->count_all_results() > 0;
            if (!$exists) break;

            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }
}
