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
        $this->db->reset_query();   
        $this->_base_filters($filters);
        return $this->db->count_all_results();
    }

    /** Ambil produk dengan filter + sort + limit */
    public function get_products($filters = [], $limit = 12, $offset = 0, $sort = 'random'){
        $this->db->reset_query();   
        $this->_base_select();
        $this->_apply_filters($filters);
        $this->_apply_sort($sort, $filters); // <- kirim $filters untuk seed
        $this->db->limit((int)$limit, (int)$offset);
        return $this->db->get()->result();
    }

    /** Detail by slug */
    public function get_by_slug($slug){
        $this->db->reset_query();   
        $this->_base_select();
        $this->db->where('p.link_seo', $slug);
        $this->db->where('p.is_active', 1);
        return $this->db->get()->row();
    }
public function get_reviews($produk_id, $limit = 3, $offset = 0){
    return $this->db->select('pr.stars, pr.nama, pr.review, COALESCE(pr.review_at, pr.created_at) AS ts', false)
        ->from('produk_rating pr')
        ->where('pr.produk_id', (int)$produk_id)
        ->where("pr.review IS NOT NULL AND TRIM(pr.review) <> ''", null, false)
        ->order_by('COALESCE(pr.review_at, pr.created_at)', 'DESC', false)
        ->limit((int)$limit, (int)$offset)
        ->get()->result();
}

public function count_reviews($produk_id){
    return (int)$this->db->from('produk_rating pr')
        ->where('pr.produk_id', (int)$produk_id)
        ->where("pr.review IS NOT NULL AND TRIM(pr.review) <> ''", null, false)
        ->count_all_results();
}


    // ================== helpers ==================
   private function _base_select(){
        $this->db->from('produk p');
        $this->db->select('p.*, kp.nama as kategori_nama, kp.slug as kategori_slug');
        $this->db->join('kategori_produk kp', 'kp.id = p.kategori_id', 'left');

        // Sub-kategori
        $this->db->select('kps.nama as sub_nama, kps.slug as sub_slug');
        $this->db->join('kategori_produk_sub kps', 'kps.id = p.sub_kategori_id', 'left');

        // Map p.terlaris --> alias lama agar view tetap bekerja tanpa ubahan
        $this->db->select('COALESCE(p.terlaris,0) AS sold_all',  false);
        $this->db->select('COALESCE(p.terlaris,0) AS sold',      false);
        $this->db->select('COALESCE(p.terlaris,0) AS sold_month',false);
    }

    private function _base_filters($filters){
    $this->db->from('produk p');
    $this->db->join('kategori_produk kp', 'kp.id = p.kategori_id', 'left');
    $this->db->join('kategori_produk_sub kps', 'kps.id = p.sub_kategori_id', 'left');

    $this->db->where('p.is_active', 1);
    $this->db->where('(kp.is_active IS NULL OR kp.is_active = 1)');
    $this->db->where('(kps.is_active IS NULL OR kps.is_active = 1)');

    // Keyword
    if (!empty($filters['q'])){
        $q = trim($filters['q']);
        $this->db->group_start()
            ->like('p.nama', $q)
            ->or_like('kp.nama', $q)
            ->or_like('kps.nama', $q)
            ->or_like('p.kata_kunci', $q)
        ->group_end();
    }

    // Recommended
   // ===== Recommended =====
$isRec = !empty($filters['recomended']); // param bernama 'recomended' (1 'm')
if ($isRec) {
    // fallback aman: pilih kolom yang ada di DB
    if ($this->db->field_exists('recomended', 'produk')) {
        $this->db->where('p.recomended', 1);
    } elseif ($this->db->field_exists('recommended', 'produk')) {
        $this->db->where('p.recommended', 1);
    } else {
        // kalau dua-duanya tidak ada, paksa 0 hasil (hindari error)
        $this->db->where('1=0', null, false);
    }
}


    // Kategori/Sub (skip saat recommended)
    if (!$isRec && !empty($filters['kategori'])){
        $k = $filters['kategori'];
        if (ctype_digit((string)$k)) $this->db->where('p.kategori_id', (int)$k);
        else $this->db->where('kp.slug', $k);
    }
    if (!$isRec && !empty($filters['sub_kategori'])){
        $s = $filters['sub_kategori'];
        if (ctype_digit((string)$s)) $this->db->where('p.sub_kategori_id', (int)$s);
        else $this->db->where('kps.slug', $s);
    }

    if (!empty($filters['sold_out'])){
        $this->db->where('p.stok', 0);
    }

    // 🔥 Trending window (tambahkan di base_filters juga!)
    if (!empty($filters['trending'])) {
        $min  = isset($filters['trend_min'])  ? (float)$filters['trend_min']  : 1.0;
        $days = isset($filters['trend_days']) ? (int)$filters['trend_days']   : 14;

        $this->db->where('COALESCE(p.terlaris_score,0) >= '.(float)$min, null, false);
        $this->db->where(
            '(TIMESTAMPDIFF(DAY, COALESCE(p.terlaris_score_updated_at, p.created_at), NOW()) <= '.(int)$days.')',
            null,
            false
        );
    }
}



    private function _apply_filters($filters){
    $this->db->where('p.is_active', 1);
    $this->db->where('(kp.is_active IS NULL OR kp.is_active = 1)');
    $this->db->where('(kps.is_active IS NULL OR kps.is_active = 1)');

    // ===== Keyword =====
    if (!empty($filters['q'])){
        $q = trim($filters['q']);
        $this->db->group_start()
            ->like('p.nama', $q)
            ->or_like('kp.nama', $q)
            ->or_like('kps.nama', $q)      // + subkategori (opsional, tapi membantu)
            ->or_like('p.kata_kunci', $q)
        ->group_end();
    }

    // ===== Recommended =====
    // ===== Recommended =====
$isRec = !empty($filters['recomended']); // param bernama 'recomended' (1 'm')
if ($isRec) {
    // fallback aman: pilih kolom yang ada di DB
    if ($this->db->field_exists('recomended', 'produk')) {
        $this->db->where('p.recomended', 1);
    } elseif ($this->db->field_exists('recommended', 'produk')) {
        $this->db->where('p.recommended', 1);
    } else {
        // kalau dua-duanya tidak ada, paksa 0 hasil (hindari error)
        $this->db->where('1=0', null, false);
    }
}


    // ===== Kategori/Sub (di-skip bila recommended aktif) =====
    if (!$isRec && !empty($filters['kategori'])){
        $k = $filters['kategori'];
        if (ctype_digit((string)$k)) $this->db->where('p.kategori_id', (int)$k);
        else $this->db->where('kp.slug', $k);
    }

    if (!$isRec && !empty($filters['sub_kategori'])){
        $s = $filters['sub_kategori'];
        if (ctype_digit((string)$s)) $this->db->where('p.sub_kategori_id', (int)$s);
        else $this->db->where('kps.slug', $s);
    }

    // ===== Sold out =====
    if (!empty($filters['sold_out'])){
        $this->db->where('p.stok', 0);
    }

    // ===== Trending window (opsional, aktif jika filters['trending']) =====
    if (!empty($filters['trending'])) {
        $min  = isset($filters['trend_min'])  ? (float)$filters['trend_min']  : 1.0;  // skor min
        $days = isset($filters['trend_days']) ? (int)$filters['trend_days']   : 14;   // jendela hari

        // skor minimal (pakai raw supaya fungsi COALESCE tidak di-backtick)
        $this->db->where('COALESCE(p.terlaris_score,0) >= '.(float)$min, null, false);

        // hanya yang skornya diupdate dalam <= $days hari terakhir
        $this->db->where(
            '(TIMESTAMPDIFF(DAY, COALESCE(p.terlaris_score_updated_at, p.created_at), NOW()) <= '.(int)$days.')',
            null,
            false
        );
    }
}



    private function _apply_sort($sort, $filters = []){
    switch ($sort) {
        case 'random':
            // random deterministik berbasis seed
            $seed = isset($filters['rand_seed']) && $filters['rand_seed'] !== ''
                ? (string)$filters['rand_seed'] : date('Ymd');
            $seed_sql = $this->db->escape_str($seed);

            $this->db->order_by("CRC32(CONCAT(p.id,'-{$seed_sql}'))", 'ASC', false);
            $this->db->order_by('p.id','DESC'); // fallback stabil
            break;

            case 'bestseller':
    // Terlaris sepanjang waktu (stabil)
            $this->db->order_by('p.terlaris','DESC');
            $this->db->order_by('p.created_at','DESC');
            $this->db->order_by('p.id','DESC');
            break;

            case 'trending':
    // Sedang naik daun (dinamis). Fallback ke terlaris bila score 0/null.
                    $this->db->order_by('COALESCE(p.terlaris_score,0)','DESC', false);
            $this->db->order_by('p.terlaris','DESC');      // tie-breaker
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
