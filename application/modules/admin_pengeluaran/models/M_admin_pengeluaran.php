<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_admin_pengeluaran extends CI_Model {

    private $table = 'pengeluaran p';

    private $column_order  = [
        null,            // no
        'p.tanggal',     // tanggal
        'p.kategori',    // kategori
        'p.nomor',       // uraian/nomor
        'p.jumlah',      // jumlah
        'p.metode_bayar',// metode
        'p.created_at',  // dibuat
        null             // aksi
    ];

    private $column_search = ['p.nomor','p.kategori','p.keterangan','p.metode_bayar','p.created_by'];
    private $order         = ['p.tanggal'=>'DESC','p.id'=>'DESC'];

    private $max_rows = 1000;
    private $f_kategori = 'all';
    private $f_metode   = 'all';
    private $f_dfrom    = '';
    private $f_dto      = '';

    public function __construct(){ parent::__construct(); }

    public function set_max_rows($n = 1000){ $this->max_rows = max(0,(int)$n); }

    public function set_filters($kategori='all', $metode='all', $dfrom='', $dto=''){
        $this->f_kategori = $kategori ?: 'all';
        $this->f_metode   = $metode   ?: 'all';
        $this->f_dfrom    = $dfrom;
        $this->f_dto      = $dto;
    }

    private function _base_q(){
        $this->db->from($this->table);
        $this->db->select('
            p.id, p.nomor, p.tanggal, p.kategori, p.keterangan, p.jumlah,
            p.metode_bayar, p.created_by, p.updated_by, p.created_at, p.updated_at
        ');

        if ($this->f_kategori !== 'all'){
            $this->db->where('p.kategori', $this->f_kategori);
        }
        if ($this->f_metode !== 'all'){
            $this->db->where('p.metode_bayar', $this->f_metode);
        }
        if ($this->f_dfrom !== ''){
            $this->db->where('p.tanggal >=', $this->f_dfrom.' 00:00:00');
        }
        if ($this->f_dto !== ''){
            $this->db->where('p.tanggal <=', $this->f_dto.' 23:59:59');
        }
    }

    private function _build_q(){
        $this->_base_q();

        // Search
        $search = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
        if ($search !== ''){
            $this->db->group_start();
            foreach($this->column_search as $i=>$col){
                if ($i===0) $this->db->like($col,$search);
                else        $this->db->or_like($col,$search);
            }
            $this->db->group_end();
        }

        // Ordering
        if (isset($_POST['order'])){
            $idx = (int)$_POST['order'][0]['column'];
            $dir = $_POST['order'][0]['dir']==='desc'?'DESC':'ASC';
            $col = $this->column_order[$idx] ?? null;
            if ($col){ $this->db->order_by($col,$dir); }
        } else {
            $this->db->order_by('p.tanggal','DESC');
            $this->db->order_by('p.id','DESC');
        }
    }

    public function get_data(){
        $this->_build_q();

        $start  = isset($_POST['start'])  ? (int)$_POST['start']  : 0;
        $length = isset($_POST['length']) ? (int)$_POST['length'] : 10;
        if ($length === -1) $length = ($this->max_rows > 0 ? $this->max_rows : 1000000);

        if ($this->max_rows > 0){
            if ($start >= $this->max_rows){ $start = max(0, $this->max_rows - 1); }
            $remain = max(0, $this->max_rows - $start);
            $limit  = max(0, min($length, $remain));
            $this->db->limit($limit, $start);
        } else {
            $this->db->limit($length, $start);
        }

        return $this->db->get()->result();
    }

    public function count_filtered(){
        $this->_build_q();
        $filtered = $this->db->count_all_results();
        return ($this->max_rows > 0) ? min($filtered, $this->max_rows) : $filtered;
    }

    public function count_all(){
        $this->_base_q();
        $total = $this->db->count_all_results();
        return ($this->max_rows > 0) ? min($total, $this->max_rows) : $total;
    }

    public function get_row(int $id){
        return $this->db->get_where('pengeluaran', ['id'=>$id])->row();
    }

    public function insert(array $data){
        return $this->db->insert('pengeluaran', $data);
    }

    public function update(int $id, array $data){
        return $this->db->where('id',$id)->update('pengeluaran',$data);
    }

    public function delete(int $id){
        return $this->db->delete('pengeluaran',['id'=>$id]);
    }
}
