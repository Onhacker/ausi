<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_admin_produk extends CI_Model {

    private $table         = 'produk p';
    private $column_order  = [null, null, 'p.nama', 'kp.nama', 'kps.nama', 'p.sku', 'p.harga', 'p.stok', 'p.is_active', null];
    private $column_search = ['p.nama','kp.nama','kps.nama','p.sku','p.kata_kunci']; // ⬅️ tambahkan p.kata_kunci

    private $order         = ['p.created_at'=>'DESC','p.id'=>'DESC'];

    public function __construct(){ parent::__construct(); }

    private function _base_q(){
        $this->db->from($this->table);
        $this->db->select('p.*, kp.nama as kategori_nama, kps.nama as sub_nama'); // ⬅️ tambah sub_nama
        $this->db->join('kategori_produk kp','kp.id = p.kategori_id','left');
        $this->db->join('kategori_produk_sub kps','kps.id = p.sub_kategori_id','left'); // ⬅️ join sub
    }


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
        $kat = $this->input->post('kategori_id', true);
        if ($kat !== null && $kat !== '' && ctype_digit((string)$kat) && (int)$kat > 0){
            $this->db->where('p.kategori_id', (int)$kat);
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

    /** Slug unik untuk produk (link_seo) */
    public function generate_unique_slug($nama, $ignore_id=null){
        $this->load->helper('url');
        $base = url_title(convert_accented_characters($nama), '-', true);
        if ($base==='') $base='produk';
        $base = substr($base, 0, 170);
        $slug = $base; $i=1;
        while(true){
            $this->db->from('produk')->where('link_seo',$slug);
            if ($ignore_id) $this->db->where('id !=',(int)$ignore_id);
            $exists = (int)$this->db->count_all_results() > 0;
            if (!$exists) break;
            $slug = $base.'-'.(++$i);
        }
        return $slug;
    }

    /** Generate SKU unik berbasis kategori + nama.
     * Pola: CAT-ITEM-### (contoh: KOP-LAT-001)
     * $ignore_id: abaikan id ini saat mencari konflik (untuk update)
     */
    public function generate_unique_sku($kategori_id, $nama, $ignore_id = null){
        // Ambil nama kategori
        $kat = $this->db->select('nama')
        ->get_where('kategori_produk', ['id'=>(int)$kategori_id])
        ->row();
        $kat_name = $kat ? $kat->nama : 'CAT';

        // 3 huruf kategori (A-Z), 3 huruf produk (A-Z0-9)
        $cat = strtoupper(substr(preg_replace('/[^A-Za-z]/','', $kat_name), 0, 3));
        if ($cat === '') $cat = 'CAT';
        $it  = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/','', $nama), 0, 3));
        if ($it === '') $it = 'PRD';

        $base = $cat . '-' . $it;

        // Cari sequence terakhir
        $this->db->select('sku')->from('produk')
        ->like('sku', $base.'-', 'after');
        if ($ignore_id) $this->db->where('id !=', (int)$ignore_id);
        $this->db->order_by('sku', 'DESC')->limit(1);
        $row = $this->db->get()->row();

        $seq = 0;
        if ($row && preg_match('/-(\d{3,})$/', $row->sku, $m)) {
            $seq = (int)$m[1];
        }

        // Bentuk SKU baru & pastikan unik
        do {
            $seq++;
            $sku = $base . '-' . str_pad($seq, 3, '0', STR_PAD_LEFT);
            $this->db->from('produk')->where('sku', $sku);
            if ($ignore_id) $this->db->where('id !=', (int)$ignore_id);
            $exists = $this->db->count_all_results() > 0;
        } while ($exists);

        return $sku;
    }

}
