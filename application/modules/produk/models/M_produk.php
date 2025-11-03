<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_produk extends CI_Model {

    public function __construct(){ parent::__construct(); }

    /** Ambil daftar kategori aktif untuk filter */
    public function get_categories(){
        return $this->db->order_by('nama','ASC')
            ->get_where('kategori_produk', ['is_active'=>1])->result();
    }

    /** Hitung total produk utk pagination */
    public function count_products($filters = []){
        $this->_base_filters($filters);
        return $this->db->count_all_results();
    }

    /** Ambil produk dengan filter + sort + limit */
    public function get_products($filters = [], $limit = 12, $offset = 0, $sort = 'random'){
        $this->_base_select();
        $this->_apply_filters($filters);
        $this->_apply_sort($sort, $filters); // <- kirim $filters untuk seed
        $this->db->limit((int)$limit, (int)$offset);
        return $this->db->get()->result();
    }

    /** Detail by slug */
    public function get_by_slug($slug){
        $this->_base_select();
        $this->db->where('p.link_seo', $slug);
        $this->db->where('p.is_active', 1);
        return $this->db->get()->row();
    }

    // ================== helpers ==================
    private function _base_select(){
        $this->db->from('produk p');
        $this->db->select('p.*, kp.nama as kategori_nama, kp.slug as kategori_slug');
        $this->db->join('kategori_produk kp', 'kp.id = p.kategori_id', 'left');

        // JOIN subkategori (asumsi p.sub_kategori_id ADA di tabel produk)
        // 🔧 FIX: Hapus koma di akhir select agar tidak jadi "..., sub_slug, FROM ..."
        $this->db->select('kps.nama as sub_nama, kps.slug as sub_slug');
        $this->db->join('kategori_produk_sub kps', 'kps.id = p.sub_kategori_id', 'left');
    }

    private function _base_filters($filters){
        $this->db->from('produk p');
        $this->db->join('kategori_produk kp', 'kp.id = p.kategori_id', 'left');
        $this->db->join('kategori_produk_sub kps', 'kps.id = p.sub_kategori_id', 'left');

        $this->db->where('p.is_active', 1);
        $this->db->where('(kp.is_active IS NULL OR kp.is_active = 1)');
        $this->db->where('(kps.is_active IS NULL OR kps.is_active = 1)');

        if (!empty($filters['q'])){
            $q = trim($filters['q']);
            $this->db->group_start()
                ->like('p.nama', $q)
                ->or_like('kp.nama', $q)
                ->or_like('p.kata_kunci', $q)
            ->group_end();
        }

        if (!empty($filters['kategori'])){
            $k = $filters['kategori'];
            if (ctype_digit((string)$k)) $this->db->where('p.kategori_id', (int)$k);
            else $this->db->where('kp.slug', $k);
        }

        if (!empty($filters['sub_kategori'])){
            $s = $filters['sub_kategori'];
            if (ctype_digit((string)$s)) $this->db->where('p.sub_kategori_id', (int)$s);
            else $this->db->where('kps.slug', $s);
        }

        if (!empty($filters['sold_out'])){
            $this->db->where('p.stok', 0);
        }

        // NEW: filter Recomended (prefix 'p.' biar nggak ambigu)
        if (!empty($filters['recomended'])) {
            $this->db->where('p.recomended', 1);
        }
    }


    private function _apply_filters($filters){
        $this->db->where('p.is_active', 1);
        $this->db->where('(kp.is_active IS NULL OR kp.is_active = 1)');
        $this->db->where('(kps.is_active IS NULL OR kps.is_active = 1)');

        if (!empty($filters['q'])){
            $q = trim($filters['q']);
            $this->db->group_start()
                ->like('p.nama', $q)
                ->or_like('kp.nama', $q)
                ->or_like('p.kata_kunci', $q)
            ->group_end();
        }

        if (!empty($filters['kategori'])){
            $k = $filters['kategori'];
            if (ctype_digit((string)$k)) $this->db->where('p.kategori_id', (int)$k);
            else $this->db->where('kp.slug', $k);
        }

        if (!empty($filters['sub_kategori'])){
            $s = $filters['sub_kategori'];
            if (ctype_digit((string)$s)) $this->db->where('p.sub_kategori_id', (int)$s);
            else $this->db->where('kps.slug', $s);
        }

        if (!empty($filters['sold_out'])){
            $this->db->where('p.stok', 0);
        }

        // NEW: filter Recomended (pakai alias tabel)
        if (!empty($filters['recomended'])) {
            $this->db->where('p.recomended', 1);
        }
    }


    private function _apply_sort($sort, $filters = []){
        // Subquery penjualan SELAMANYA (tanpa batas waktu)
        $subAll = "
            SELECT pi.produk_id, SUM(pi.qty) AS sold_all
            FROM pesanan_item pi
            JOIN pesanan pe ON pe.id = pi.pesanan_id
            WHERE pe.status IN ('paid','verifikasi')
            GROUP BY pi.produk_id
        ";

        switch ($sort) {
            case 'random':
                $seed = isset($filters['rand_seed']) && $filters['rand_seed'] !== ''
                    ? (string)$filters['rand_seed'] : date('Ymd');
                $seed_sql = $this->db->escape_str($seed);

                // Urutan acak deterministik + fallback id desc
                $this->db->order_by("CRC32(CONCAT(p.id,'-{$seed_sql}'))", 'ASC', false);
                $this->db->order_by('p.id','DESC');

                // Bawa total penjualan selamanya
                $this->db->select('COALESCE(a.sold_all,0) AS sold_all', false);
                $this->db->select('COALESCE(a.sold_all,0) AS sold', false);
                $this->db->select('COALESCE(a.sold_all,0) AS sold_month', false);
                $this->db->join("($subAll) a", 'a.produk_id = p.id', 'left', false);
                break;

            case 'bestseller':
                $this->db->select('COALESCE(a.sold_all,0) AS sold_all', false);
                $this->db->select('COALESCE(a.sold_all,0) AS sold', false);
                $this->db->select('COALESCE(a.sold_all,0) AS sold_month', false);
                $this->db->join("($subAll) a", 'a.produk_id = p.id', 'left', false);

                $this->db->order_by('sold_all','DESC');
                $this->db->order_by('p.created_at','DESC');
                $this->db->order_by('p.id','DESC');
                break;

            case 'price_low':
                $this->db->order_by('p.harga','ASC');  break;

            case 'price_high':
                $this->db->order_by('p.harga','DESC'); break;

            case 'sold_out':
                $this->db->order_by('p.nama','ASC');   break;

            case 'new':
            default:
                $this->db->order_by('p.created_at','DESC');
                $this->db->order_by('p.id','DESC');
        }
    }

    public function get_order_with_items($id){
        $order = $this->db->get_where('pesanan',['id'=>(int)$id])->row();
        if (!$order) return null;
        $items = $this->db->select('pi.*, p.gambar, p.link_seo as slug')
                          ->from('pesanan_item pi')
                          ->join('produk p','p.id=pi.produk_id','left')
                          ->where('pi.pesanan_id',(int)$id)
                          ->order_by('pi.id','ASC')->get()->result();
        $total = 0;
        foreach($items as $it){ $total += (int)$it->subtotal; }
        return ['order'=>$order,'items'=>$items,'total'=>$total];
    }

    // public function get_subcategories_for_placeholder($limit = 100){
    //     return $this->db->select('id, nama')
    //     ->from('kategori_produk_sub')
    //     ->where('is_active', 1)    // sesuaikan jika berbeda
    //     ->order_by('nama', 'ASC')
    //     ->limit((int)$limit)
    //     ->get()->result();
    // }

}
