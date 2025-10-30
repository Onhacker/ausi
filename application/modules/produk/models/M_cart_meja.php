<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_cart_meja extends CI_Model {

    public function __construct(){
        parent::__construct();
    }

    /** Cari atau buat cart 'open' untuk meja tertentu. Return row (id, dll). */
   public function ensure_open_cart($meja_kode){
    $meja_kode = (string)$meja_kode;
    if ($meja_kode === '') return null;

    $now = date('Y-m-d H:i:s');

    // Ambil sesuatu yg stabil dari sesi utk jejak
    $ci  = &get_instance();
    $sid = $ci->session->userdata('session_id') ?: (function_exists('session_id') ? session_id() : null);
    if (!$sid) { $sid = bin2hex(random_bytes(16)); }

    // sesi_key & session_token (opsional; tidak dipakai buat unique, hanya jejak)
    $sesi_key      = hash('sha256', $meja_kode.'|'.$sid.'|'.microtime(true).'|'.mt_rand());
    $session_token = substr(hash('sha256', $sid.'|'.$meja_kode), 0, 40);

    // KUNCI: insert atomik. Jika sudah ada baris OPEN untuk meja ini,
    // unique(uniq_open) akan trigger dan kita re-use id existing (LAST_INSERT_ID(id)).
    $sql = "INSERT INTO cart_meja (meja_kode, sesi_key, session_token, status, created_at, updated_at)
            VALUES (?, ?, ?, 'open', ?, ?)
            ON DUPLICATE KEY UPDATE
                id = LAST_INSERT_ID(id),
                updated_at = VALUES(updated_at)";

    // NOTE: Pastikan ada index unik: CREATE UNIQUE INDEX uniq_cart_open_per_meja ON cart_meja (uniq_open);
    $this->db->query($sql, [$meja_kode, $sesi_key, $session_token, $now, $now]);

    $id = (int)$this->db->insert_id();
    if ($id <= 0){
        // Fallback super-aman (harusnya tak terjadi): cari baris open terbaru
        return $this->db->order_by('updated_at','DESC')
                        ->get_where('cart_meja', ['meja_kode'=>$meja_kode, 'status'=>'open'])
                        ->row();
    }
    return $this->db->get_where('cart_meja', ['id'=>$id])->row();
}


    /** Tambahkan/increment item (ON DUPLICATE → qty += $qty). */
   public function add_item($cart_id, $produk_id, $qty, $harga){
        $sql = "INSERT INTO cart_meja_item (cart_id, produk_id, qty, harga)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE qty = qty + VALUES(qty), harga = VALUES(harga), updated_at = CURRENT_TIMESTAMP()";
        return $this->db->query($sql, [(int)$cart_id, (int)$produk_id, max(1,(int)$qty), (int)$harga]);
    }

    /** SET qty absolut (bukan increment). Jika qty<=0 → delete. */
    public function set_qty($cart_id, $produk_id, $qty, $harga_snapshot=null){
        $cart_id   = (int)$cart_id;
        $produk_id = (int)$produk_id;
        $qty       = (int)$qty;

        if ($qty <= 0) {
            return $this->db->delete('cart_meja_item', ['cart_id'=>$cart_id, 'produk_id'=>$produk_id]);
        }
        $harga = ($harga_snapshot !== null) ? (int)$harga_snapshot : 0;
        $sql = "INSERT INTO cart_meja_item (cart_id, produk_id, qty, harga)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE qty = VALUES(qty), harga = IF(VALUES(harga)>0,VALUES(harga),harga), updated_at = CURRENT_TIMESTAMP()";
        return $this->db->query($sql, [$cart_id, $produk_id, $qty, $harga]);
    }

    public function remove_item($cart_id, $produk_id){
        return $this->db->delete('cart_meja_item', ['cart_id'=>(int)$cart_id, 'produk_id'=>(int)$produk_id]);
    }

    public function count_items($cart_id){
        $q = $this->db->select('COALESCE(SUM(qty),0) AS n')->from('cart_meja_item')->where('cart_id',(int)$cart_id)->get()->row();
        return (int)($q->n ?? 0);
    }

    public function sum_total($cart_id){
        $q = $this->db->select('COALESCE(SUM(qty*harga),0) AS t')->from('cart_meja_item')->where('cart_id',(int)$cart_id)->get()->row();
        return (int)($q->t ?? 0);
    }

    public function get_items($cart_id){
        return $this->db->select('i.*, p.nama, p.link_seo AS slug, p.gambar, p.stok')
                        ->from('cart_meja_item i')
                        ->join('produk p', 'p.id = i.produk_id', 'left')
                        ->where('i.cart_id', (int)$cart_id)
                        ->order_by('i.created_at','DESC')
                        ->get()->result();
    }
    public function ensure_open_cart_by_session($meja_kode, $session_token){
    $meja_kode     = (string)$meja_kode;
    $session_token = (string)$session_token;
    if ($meja_kode === '' || $session_token === '') return null;

    // 1) Reuse kalau sudah ada
    $row = $this->db->get_where('cart_meja', [
        'meja_kode'     => $meja_kode,
        'session_token' => $session_token,
        'status'        => 'open'
    ])->row();
    if ($row) return $row;

    // 2) Insert aman (unik: meja_kode + session_token + uniq_open)
    $now = date('Y-m-d H:i:s');
    $sql = "INSERT INTO cart_meja (meja_kode, sesi_key, session_token, status, created_at, updated_at)
        VALUES (?, CONCAT(?, '|', ?, '|', UNIX_TIMESTAMP()), ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
          id = LAST_INSERT_ID(id),
          updated_at = VALUES(updated_at)";
$this->db->query($sql, [
    $meja_kode, $meja_kode, $session_token,
    'open',     $session_token,
    $now,       $now
]);


    $id = (int)$this->db->insert_id();
    if ($id > 0) return $this->db->get_where('cart_meja', ['id'=>$id])->row();

    // 3) Fallback super-aman
    return $this->db->order_by('updated_at','DESC')
                    ->get_where('cart_meja', [
                        'meja_kode'     => $meja_kode,
                        'session_token' => $session_token,
                        'status'        => 'open'
                    ])->row();
}


}
