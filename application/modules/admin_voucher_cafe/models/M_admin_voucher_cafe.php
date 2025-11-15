<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_admin_voucher_cafe extends CI_Model {

    private $table         = 'voucher_cafe_manual v';
    private $column_order  = [
        null,               // checkbox
        null,               // no
        'v.kode_voucher',   // kode
        'v.nama',           // nama
        'v.no_hp',          // no hp
        'v.tipe',           // tipe
        'v.nilai',          // nilai
        'v.tgl_mulai',      // periode
        'v.klaim_terpakai', // klaim
        'v.status',         // status
        null                // aksi
    ];
    private $column_search = ['v.kode_voucher','v.nama','v.no_hp'];
    private $order         = ['v.id' => 'DESC'];

    public function __construct(){
        parent::__construct();
    }

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

    /**
     * Generate kode voucher unik
     * Format: CAFyymmddXXX (CAF250115001, CAF250115002, dst.)
     */
   public function generate_kode_voucher()
{
    $length = 8;
    // Hindari karakter yang mirip (O/0, I/1)
    $chars  = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    do {
        $kode = '';
        for ($i = 0; $i < $length; $i++) {
            $kode .= $chars[random_int(0, strlen($chars) - 1)];
        }

        // Cek apakah kode sudah ada di DB
        $exists = $this->db
            ->where('kode_voucher', $kode)
            ->count_all_results('voucher_cafe_manual') > 0;

    } while ($exists);

    return $kode;
}

}
