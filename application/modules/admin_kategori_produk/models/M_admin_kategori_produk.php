<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_admin_kategori_produk extends CI_Model {

    private $table         = 'kategori_produk kp';
    private $column_order  = [null, null, 'kp.nama', 'kp.slug', 'kp.is_active', null];
    private $column_search = ['kp.nama','kp.slug'];
    private $order         = ['kp.nama' => 'ASC'];

    public function __construct(){ parent::__construct(); }

    private function _base_q(){ $this->db->from($this->table); }

    private function _build_q(){
        $this->_base_q();
        $search = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
        if ($search !== ''){
            $this->db->group_start();
            foreach($this->column_search as $i=>$col){
                if ($i===0) $this->db->like($col,$search);
                else        $this->db->or_like($col,$search);
            }
            $this->db->group_end();
        }
        if (isset($_POST['order'])){
            $idx = (int)$_POST['order'][0]['column'];
            $dir = $_POST['order'][0]['dir']==='desc'?'DESC':'ASC';
            $col = $this->column_order[$idx] ?? key($this->order);
            if ($col) $this->db->order_by($col,$dir);
        } else {
            foreach($this->order as $col=>$dir){ $this->db->order_by($col,$dir); }
        }
    }

    public function get_data(){
        $this->_build_q();
        if (isset($_POST['length']) && $_POST['length'] != -1){
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

    /** Generate slug unik */
    public function generate_unique_slug($nama, $ignore_id=null){
        $this->load->helper('url');
        $base = url_title(convert_accented_characters($nama), '-', true);
        if ($base==='') $base = 'kategori';
        $base = substr($base, 0, 120);
        $slug = $base; $i=1;
        while(true){
            $this->db->from('kategori_produk')->where('slug',$slug);
            if ($ignore_id) $this->db->where('id !=', (int)$ignore_id);
            $exists = (int)$this->db->count_all_results() > 0;
            if (!$exists) break;
            $slug = $base.'-'.(++$i);
        }
        return $slug;
    }
    /* =============== SUBKATEGORI (server-side DataTables) =============== */
private $sub_table         = 'kategori_produk_sub ks';
private $sub_column_order  = [null, null, 'kp.nama', 'ks.nama', 'ks.slug', 'ks.is_active', null];
private $sub_column_search = ['kp.nama','ks.nama','ks.slug'];
private $sub_order         = ['ks.id' => 'DESC'];

private function _sub_base_q($kategori_id = null){
    $this->db->from($this->sub_table)
             ->join('kategori_produk kp','kp.id=ks.kategori_id','left');
    if ($kategori_id){ $this->db->where('ks.kategori_id',(int)$kategori_id); }
}

private function _sub_build_q($kategori_id = null){
    $this->_sub_base_q($kategori_id);
    $search = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
    if ($search !== ''){
        $this->db->group_start();
        foreach($this->sub_column_search as $i=>$col){
            if ($i===0) $this->db->like($col,$search);
            else        $this->db->or_like($col,$search);
        }
        $this->db->group_end();
    }
    if (isset($_POST['order'])){
        $idx = (int)$_POST['order'][0]['column'];
        $dir = $_POST['order'][0]['dir']==='desc'?'DESC':'ASC';
        $col = $this->sub_column_order[$idx] ?? key($this->sub_order);
        if ($col) $this->db->order_by($col,$dir);
    } else {
        foreach($this->sub_order as $col=>$dir){ $this->db->order_by($col,$dir); }
    }

    // select fields
    $this->db->select('ks.*, kp.nama AS kategori_nama');
}

public function get_sub_data($kategori_id = null){
    $this->_sub_build_q($kategori_id);
    if (isset($_POST['length']) && $_POST['length'] != -1){
        $this->db->limit((int)$_POST['length'], (int)$_POST['start']);
    }
    return $this->db->get()->result();
}

public function sub_count_filtered($kategori_id = null){
    $this->_sub_build_q($kategori_id);
    return $this->db->get()->num_rows();
}

public function sub_count_all($kategori_id = null){
    $this->_sub_base_q($kategori_id);
    return $this->db->count_all_results();
}

/** Slug unik utk subkategori */
public function generate_unique_slug_sub($nama, $ignore_id=null){
    $this->load->helper('url');
    $base = url_title(convert_accented_characters($nama), '-', true);
    if ($base==='') $base = 'subkategori';
    $base = substr($base, 0, 160);
    $slug = $base; $i=1;
    while(true){
        $this->db->from('kategori_produk_sub')->where('slug',$slug);
        if ($ignore_id) $this->db->where('id !=', (int)$ignore_id);
        $exists = (int)$this->db->count_all_results() > 0;
        if (!$exists) break;
        $slug = $base.'-'.(++$i);
    }
    return $slug;
}

}
