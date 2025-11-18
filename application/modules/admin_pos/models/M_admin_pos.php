<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_admin_pos extends CI_Model {

    /* =========================
     * Konfigurasi DataTables
     * ========================= */
    
    private $table = 'pesanan o';

    private $column_order  = [
        null,             // no (rownum)
        'o.mode',         // mode
        'o.meja_nama',    // meja
        'o.created_at',   // waktu
        null,             // lama (ELAPSED)
        'o.grand_total',  // jumlah
        'o.status',       // status
        'o.paid_method',  // metode
        null              // aksi (non-orderable)
    ];

    private $column_search = ['o.mode','o.meja_nama','o.meja_kode','o.paid_method','o.status','o.nomor'];
    private $order         = ['o.created_at'=>'DESC','o.id'=>'DESC'];

    private $kasir_scope_enabled = false;
    private $kasir_days = 1;
    private $max_rows   = 100;
    
    /* ===== NEW: status filter ===== */
    // null = default (exclude paid); 'all' = tanpa filter; array = where_in
    private $status_filter = null;

    /* ===== Filter Kitchen/Bar: hanya order yang punya item kategori ini (1=food, 2=drink) ===== */
    private $item_category_filter = null;

    public function __construct(){
        parent::__construct();
    }

    /* =========================
     * Setters / Filters
     * ========================= */
    public function set_max_rows($n = 100){
        $this->max_rows = max(0,(int)$n);
    }

    public function set_kasir_scope($enabled = true, $days = 1){
        $this->kasir_scope_enabled = (bool)$enabled;
        $this->kasir_days = max(1, (int)$days);
    }

    public function set_item_category_filter($cat = null){
        $cat = is_null($cat) ? null : (int)$cat;
        $this->item_category_filter = in_array($cat, [1,2], true) ? $cat : null;
    }

    /** @param null|string|array $status  null=default(exclude paid), 'all'=no filter, array=['pending','verifikasi'] */
    public function set_status_filter($status){
        if ($status === null){
            $this->status_filter = null; // default behavior (exclude paid)
        } elseif ($status === 'all' || $status === []){
            $this->status_filter = 'all'; // no filter by status
        } elseif (is_array($status)){
            $status = array_values(array_filter(array_map('strtolower',$status)));
            $this->status_filter = $status ?: 'all';
        } else {
            $this->status_filter = [strtolower((string)$status)];
        }
    }

    /* =========================
     * Query Builders
     * ========================= */
    private function _base_q(){
    $this->db->from($this->table);
    $this->db->select('
        o.id, o.nomor, o.nama,
        o.meja_nama, o.meja_kode,
        o.mode, o.paid_method,
        o.created_at, o.updated_at, o.paid_at,
        o.kasir_start_at, o.kasir_end_at, o.kasir_duration_sec,
        o.status,o.courier_id,o.courier_name,
        o.status_pesanan_kitchen, o.kitchen_done_at, o.kitchen_duration_s,
        o.status_pesanan_bar,     o.bar_done_at,     o.bar_duration_s,
        o.grand_total, o.tutup_transaksi, o.kode_unik, o.catatan ,o.voucher_code
    ,o.voucher_disc
    ');

    // ... (filter status & kategori yang sudah ada)
    if ($this->status_filter === null){
        $this->db->where('o.status <>', 'paid');
    } elseif ($this->status_filter !== 'all' && is_array($this->status_filter)){
        $this->db->where_in('o.status', $this->status_filter);
    }
    if ($this->item_category_filter !== null){
        $cat = (int)$this->item_category_filter;
        $this->db->where("EXISTS (SELECT 1 FROM pesanan_item pi WHERE pi.pesanan_id = o.id AND pi.id_kategori = {$cat})", null, false);
    }

    /* >>>> TAMBAHAN: batasi ke jendela operasional hari-ini dari tabel identitas <<<< */
    $this->_apply_today_window();
}


    private function _build_q(){
        $this->_base_q();

        // ===== Pencarian =====
        $search = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
        if ($search !== ''){
            $this->db->group_start();
            foreach($this->column_search as $i=>$col){
                if ($i===0) $this->db->like($col,$search);
                else        $this->db->or_like($col,$search);
            }
            $this->db->group_end();
        }

        // Deteksi role dari session
        $uname = strtolower((string)($this->session->userdata('admin_username') ?? ''));
        $isKitchenBar = in_array($uname, ['kitchen','bar'], true);

        // ===== Ordering dari DataTables (jika user klik kolom) =====
        if (isset($_POST['order'])){
            $idx = (int)$_POST['order'][0]['column'];
            $dir = $_POST['order'][0]['dir']==='desc'?'DESC':'ASC';
            $col = $this->column_order[$idx] ?? null;

            if ($col === 'o.status') {
                if ($isKitchenBar) {
                    // Kitchen/Bar: gunakan kolom status_pesanan_x
                    if ($uname === 'kitchen'){
                        $this->db->order_by('o.status_pesanan_kitchen', $dir);
                    } else {
                        $this->db->order_by('o.status_pesanan_bar', $dir);
                    }
                } else {
                    // Kasir/Admin → prioritas status pembayaran
                    $clause = "FIELD(LOWER(o.status), 'pending','verifikasi','sent','canceled','failed','paid')";
                    $this->db->order_by($clause.' '.$dir, '', false);
                }
            } elseif ($col) {
                $this->db->order_by($col,$dir);
            }
        } else {
            // ===== Default order (tanpa klik kolom) =====
            // if ($isKitchenBar) {
            //     // Kitchen/Bar → pesanan "proses (1)" tampil duluan
            //     if ($uname === 'kitchen'){
            //         $this->db->order_by('o.status_pesanan_kitchen', 'ASC');
            //     } else {
            //         $this->db->order_by('o.status_pesanan_bar', 'ASC');
            //     }
            // } else {
            //     // Kasir/Admin → prioritas status pembayaran
            //     $statusPriority = "FIELD(LOWER(o.status), 'pending','verifikasi','sent','canceled','failed','paid')";
            //     $this->db->order_by($statusPriority, '', false);
            // }
            // $this->db->order_by('o.created_at','DESC');
            // $this->db->order_by('o.id','DESC');
            // ===== Default order (tanpa klik kolom): berdasarkan waktu saja =====
            $this->db->order_by('o.created_at', 'DESC');
            $this->db->order_by('o.id', 'DESC'); // tie-break

        }
    }

    /* =========================
     * DataTables main
     * ========================= */
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

    /* =========================
     * POS helpers
     * ========================= */
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

    public function get_order_with_items_by_cat($id, $cat_id = null){
        $order = $this->db->get_where('pesanan', ['id' => (int)$id])->row();
        if (!$order) return null;

        $this->db->select('pi.*, p.gambar, p.link_seo as slug')
                 ->from('pesanan_item pi')
                 ->join('produk p','p.id=pi.produk_id','left')
                 ->where('pi.pesanan_id', (int)$id)
                 ->order_by('pi.id','ASC');

        if ($cat_id === 1 || $cat_id === 2){
            $this->db->where('pi.id_kategori', (int)$cat_id);
        }

        $items = $this->db->get()->result();
        $total = 0; foreach($items as $it){ $total += (int)$it->subtotal; }

        return ['order'=>$order,'items'=>$items,'total'=>$total];
    }

    public function search_products($q='', $limit=30){
        $this->db->select('id,nama,harga,stok');
        if ($q!==''){
            $this->db->group_start()
                     ->like('nama',$q)->or_like('sku',$q)
                     ->group_end();
        }
        $this->db->where('is_active',1)->order_by('nama','ASC')->limit($limit);
        return $this->db->get('produk')->result();
    }

    /** create order pending dari kasir */
    public function tx_create_order_pending($mode, $meja_kode, $meja_nama, $nama, $catatan, $pay_method, array $items){
        $this->db->trans_begin();

        $total = 0;
        foreach($items as $it){
            $q = (int)($it['qty'] ?? 0);
            $h = (int)($it['harga'] ?? 0);
            $total += ($q * $h);
        }

        $nomor = date('YmdHis').'-'.mt_rand(100,999);
        $this->db->insert('pesanan', [
            'nomor'        => $nomor,
            'mode'         => $mode,
            'meja_kode'    => $meja_kode ?: null,
            'meja_nama'    => $meja_nama ?: null,
            'nama'         => $nama ?: 'Customer',
            'catatan'      => $catatan ?: null,
            'total'        => (int)$total,
            'kode_unik'    => 0,
            'grand_total'  => (int)$total,
            'status'       => 'pending',
            'paid_method'  => null,
            'paid_at'      => null,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
        $order_id = (int)$this->db->insert_id();

        // map produk nama (aman jika kasir tidak kirim nama)
        $need_ids = [];
        foreach($items as $it){ $need_ids[(int)$it['produk_id']] = true; }
        $map_nama = [];
        if ($need_ids){
            $ids = array_keys($need_ids);
            $prods = $this->db->select('id,nama')->from('produk')->where_in('id',$ids)->get()->result();
            foreach($prods as $p){ $map_nama[(int)$p->id] = $p->nama; }
        }

        foreach($items as $it){
            $pid = (int)$it['produk_id'];
            $qty = (int)$it['qty'];
            $hrg = (int)$it['harga'];
            if ($pid<=0 || $qty<=0) continue;
            // deteksi kategori: dari payload / dari produk
            $kat = isset($it['id_kategori']) ? (int)$it['id_kategori'] : null;
            if ($kat === null) {
                $rowp = $this->db->select('kategori_id')->get_where('produk',['id'=>$pid])->row();
                if ($rowp) { $kat = (int)$rowp->kategori_id; }  // 1=makanan, 2=minuman
            }

            $this->db->insert('pesanan_item', [
                'pesanan_id'  => $order_id,
                'produk_id'   => $pid,
                'id_kategori' => $kat,                 // <— penting utk kitchen/bar
                'nama'        => $it['nama'] ?? ($map_nama[$pid] ?? null),
                'qty'         => $qty,
                'harga'       => $hrg,
                'subtotal'    => $qty * $hrg,
                'added_by'    => 'kasir',
                'tambahan'    => 0,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);

        }

        if ($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            return ["success"=>false,"title"=>"Gagal","pesan"=>"Gagal membuat order"];
        }
        $this->db->trans_commit();
        return ["success"=>true,"order_id"=>$order_id];
    }

    /* =========================
     * QRIS cleanup helpers
     * ========================= */
    /** Bersihkan file QRIS (jika ada) */
    private function _cleanup_qris($id){
        $path = FCPATH.'uploads/qris/order_'.((int)$id).'.png';
        if (is_file($path)) @unlink($path);
    }

    /** Hapus file QRIS untuk order */
    private function _delete_qris_file($order_id){
        $path = FCPATH.'uploads/qris/order_'.(int)$order_id.'.png';
        if (is_file($path)) { @unlink($path); }
    }

    /* =========================
     * Dashboard helpers
     * ========================= */
    public function get_stats(){
        // gunakan kolom updated_at kalau ada; fallback ke created_at
        $tsCol = $this->db->field_exists('updated_at','pesanan') ? 'updated_at' : 'created_at';
        $row = $this->db->select("COUNT(*) AS total, MAX(id) AS max_id, MAX($tsCol) AS last_ts")
                        ->get('pesanan')->row();
        // jaga null
        $row = $row ?: (object)['total'=>0,'max_id'=>0,'last_ts'=>null];
        return $row;
    }

    /* =========================
     * Bulk actions
     * ========================= */
    /** Bulk tandai paid (set tutup_transaksi=1, status=paid, segel durasi kasir, hapus qris) */
    /** Bulk tandai paid (set tutup_transaksi=1, status=paid, segel durasi kasir, hapus qris) */
/** Bulk tandai paid (set tutup_transaksi=1, status=paid, segel durasi kasir) */
public function bulk_mark_paid(array $ids){
    $ids = array_values(array_unique(array_map('intval', $ids)));
    if (!$ids) {
        return [
            "ok_count"     => 0,
            "blocked_ids"  => [],
            "already_ids"  => [],
            "notfound_ids" => [],
            "errors"       => [],
        ];
    }

    $ok_count = 0;
    $blocked  = []; // paid_method kosong
    $already  = []; // sudah paid/canceled
    $notfound = [];
    $errors   = [];

    // 👉 Simpan payload order yang BERHASIL di-update.
    // Setelah COMMIT baru kita jalankan efek samping (arsip, trending, voucher).
    $payloads = [];

    $this->db->trans_begin();

    foreach ($ids as $id){
        $row = $this->db->get_where('pesanan', ['id'=>$id])->row();

        if (!$row){
            $notfound[] = $id;
            continue;
        }

        $st = strtolower((string)$row->status);
        if (in_array($st, ['paid','canceled'], true)){
            $already[] = $id;
            continue;
        }

        // ❌ stop kalau belum ada metode pembayaran
        $method = trim((string)$row->paid_method);
        if ($method === ''){
            $blocked[] = $id;
            continue;
        }

        // ✔️ tandai paid + tutup_transaksi = 1 + segel durasi kasir
        $now = date('Y-m-d H:i:s');

        // hitung durasi kasir (start → now). Prioritas: kasir_start_at, fallback ke created_at
        if (!empty($row->kasir_start_at)) {
            $startTs = strtotime($row->kasir_start_at);
        } else {
            $startTs = strtotime($row->created_at);
        }
        $endTs  = strtotime($now);
        $durSec = ($startTs && $endTs && $endTs >= $startTs) ? ($endTs - $startTs) : null;

        $upd = [
            'status'          => 'paid',
            'tutup_transaksi' => 1,
            'paid_at'         => $now,
            'updated_at'      => $now,
            // kalau mau sekalian pastikan tersimpan:
            'paid_method'     => $method,
        ];
        if (empty($row->kasir_end_at)) {
            $upd['kasir_end_at'] = $now;
        }
        if (empty($row->kasir_duration_sec) && $durSec !== null) {
            $upd['kasir_duration_sec'] = (int)$durSec;
        }

        $ok = $this->db->where('id', $id)->update('pesanan', $upd);
        if ($ok){
            $ok_count++;

            // Sinkronkan objek $row dengan nilai setelah update
            $row->status          = 'paid';
            $row->tutup_transaksi = 1;
            $row->paid_at         = $now;
            $row->updated_at      = $now;
            $row->paid_method     = $method;

            if (empty($row->kasir_end_at)) {
                $row->kasir_end_at = $now;
            }
            if (empty($row->kasir_duration_sec) && $durSec !== null) {
                $row->kasir_duration_sec = (int) $durSec;
            }

            // Simpan payload utk efek samping setelah COMMIT
            $payloads[] = (object)[
                'id'      => $id,
                'order'   => $row,   // sekarang status-nya sudah 'paid'
                'paid_at' => $now,
            ];
        } else {
            $errors[] = $id;
        }

    }

    if ($this->db->trans_status() === FALSE){
        $this->db->trans_rollback();
        // kalau rollback, kosongkan payload supaya tidak ada efek samping
        $payloads = [];
    } else {
        $this->db->trans_commit();
    }

    // ====== EFek samping (tidak mempengaruhi transaksi utama) ======
    foreach ($payloads as $p){
        $oid = (int)$p->id;

        // 1) Trending "terlaris"
        try {
            $this->_bump_terlaris_for_order($oid);
        } catch (\Throwable $e){
            log_message('error', 'bump_terlaris error for '.$oid.': '.$e->getMessage());
        }

        // 2) Arsip ke pesanan_paid & pesanan_item_paid
        try {
            $this->_archive_paid($oid);
        } catch (\Throwable $e){
            log_message('error', 'archive_paid error for '.$oid.': '.$e->getMessage());
        }

        // 3) Hapus file QRIS bila ada
        try {
            $this->_delete_qris_file($oid);
        } catch (\Throwable $e){
            log_message('error', 'delete_qris_file error for '.$oid.': '.$e->getMessage());
        }

        // 4) Loyalty / voucher (pakai helper yang sudah kamu buat)
        try {
            $this->_voucher_cafe_upsert_from_order($p->order, $p->paid_at);
        } catch (\Throwable $e){
            log_message('error', 'voucher_cafe_upsert error for '.$oid.': '.$e->getMessage());
        }
    }

    return [
        "ok_count"     => $ok_count,
        "blocked_ids"  => $blocked,
        "already_ids"  => $already,
        "notfound_ids" => $notfound,
        "errors"       => $errors,
    ];
}


    /** Batalkan (dipanggil controller) */
   public function bulk_mark_canceled(array $ids){
    $ids = array_values(array_unique(array_map('intval', $ids)));
    if (!$ids) return true;

    $this->db->trans_begin();

    foreach ($ids as $id){
        $ok = $this->db->where('id', $id)->update('pesanan', [
            'status'          => 'canceled',
            'tutup_transaksi' => 1,
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);
        if ($ok){ $this->_delete_qris_file($id); }
    }

    // >>> bersihkan juga arsip paid untuk ID-ID ini
    $this->_delete_archives_for_orders($ids);

    if ($this->db->trans_status() === FALSE){
        $this->db->trans_rollback(); return false;
    }
    $this->db->trans_commit(); return true;
}


    /** Bulk delete */
   public function bulk_delete(array $ids){
    $ids = array_values(array_unique(array_map('intval',$ids)));
    if (!$ids) return ["ok_count"=>0,"paid_ids"=>[],"notfound_ids"=>[],"errors"=>[]];

    $rows = $this->db->select('id,status')->from('pesanan')->where_in('id',$ids)->get()->result();
    $existMap = [];
    foreach($rows as $r){ $existMap[(int)$r->id] = strtolower((string)$r->status); }

    $paid_ids   = [];
    $notfound   = [];
    $ok_count   = 0;
    $errors     = [];
    $willPurgeArchives = []; // kumpulkan yang benar-benar dihapus

    $this->db->trans_begin();
    foreach($ids as $id){
        if (!isset($existMap[$id])){ $notfound[]=$id; continue; }
        if ($existMap[$id] === 'paid'){ $paid_ids[]=$id; continue; }

        // hapus items dulu
        $this->db->delete('pesanan_item',['pesanan_id'=>$id]);
        $ok = $this->db->delete('pesanan',['id'=>$id]);
        if ($ok){
            $ok_count++;
            $willPurgeArchives[] = $id;
            $this->_cleanup_qris($id);
        }else{
            $errors[]=$id;
        }
    }

    // >>> bersihkan arsip untuk yang memang terhapus
    if (!empty($willPurgeArchives)){
        $this->_delete_archives_for_orders($willPurgeArchives);
    }

    if ($this->db->trans_status() === FALSE){
        $this->db->trans_rollback();
    } else {
        $this->db->trans_commit();
    }

    return [
        "ok_count"=>$ok_count,
        "paid_ids"=>$paid_ids,
        "notfound_ids"=>$notfound,
        "errors"=>$errors,
    ];
}

// === tambahkan di dalam class M_admin_pos ===
/** Hapus arsip untuk order tertentu (header & items) */
private function _delete_archives_for_orders(array $order_ids): void
{
    $ids = array_values(array_unique(array_map('intval', $order_ids)));
    if (!$ids) return;

    // Hapus item dulu supaya aman dari FK
    $this->db->where_in('pesanan_src_id', $ids)->delete('pesanan_item_paid');
    // Hapus header arsipnya
    $this->db->where_in('src_id', $ids)->delete('pesanan_paid');
}

    /* =========================
     * Arsip Paid
     * ========================= */
    /** Arsipkan order + items ke *_paid saat status berubah ke paid */
    private function _archive_paid(int $order_id): bool
        {
            // --- Ambil header sumber ---
            $order = $this->db->get_where('pesanan', ['id' => $order_id])->row();
            if (!$order) return false;

            // --- Upsert header ke pesanan_paid berdasarkan src_id ---
            $exist = $this->db->get_where('pesanan_paid', ['src_id' => $order_id])->row();
            if ($exist) {
                $pesanan_paid_id = (int)$exist->id;
            } else {
                // Mapping kolom yang ADA di pesanan_paid (sesuai skema kamu)
                $mapOrder = [
                    'src_id'      => (int)$order->id,
                    'nomor'       => (string)($order->nomor ?? null),
                    'mode'        => (string)($order->mode ?? null),
                    'meja_kode'   => (string)($order->meja_kode ?? null),
                    'meja_nama'   => (string)($order->meja_nama ?? null),
                    'nama'        => (string)($order->nama ?? null),
                    'customer_phone'        => (string)($order->customer_phone ?? null),
                    'alamat_kirim'        => (string)($order->alamat_kirim ?? null),
                    'catatan'     => (string)($order->catatan ?? null),
                    'total'       => (int)($order->total ?? 0),
                    'kode_unik'   => (int)($order->kode_unik ?? 0),
                    'grand_total' => (int)($order->grand_total ?? 0),
                    'delivery_fee' => (int)($order->delivery_fee ?? 0),
                    'status'      => (string)($order->status ?? 'paid'), // seharusnya 'paid'
                    'paid_method' => (string)($order->paid_method ?? null),
                    'paid_at'     => !empty($order->paid_at) ? $order->paid_at : null,
                    'created_at'  => !empty($order->created_at) ? $order->created_at : null,
                    'updated_at'  => !empty($order->updated_at) ? $order->updated_at : null,
                    'archived_at' => date('Y-m-d H:i:s'),
                ];
                $this->db->insert('pesanan_paid', $mapOrder);
                $pesanan_paid_id = (int)$this->db->insert_id();
                if ($pesanan_paid_id <= 0) return false;
            }

            // --- Ambil items sumber ---
            $items = $this->db->get_where('pesanan_item', ['pesanan_id' => $order_id])->result();

            // Hindari duplikat berdasarkan src_id (id item sumber)
            $existingSrcIds = [];
            if ($items) {
                $srcIds = array_values(array_filter(array_map(function($r){
                    return (int)($r->id ?? 0);
                }, $items)));
                if ($srcIds) {
                    $in = implode(',', array_map('intval', $srcIds));
                    $q  = $this->db->query("SELECT src_id FROM pesanan_item_paid WHERE src_id IN ($in)");
                    foreach($q->result() as $r){ $existingSrcIds[(int)$r->src_id] = true; }
                }
            }

            // --- Insert item yang belum ada ---
            foreach ($items as $it) {
                $src_item_id = (int)($it->id ?? 0);
                if ($src_item_id <= 0) {
                    // Karena pesanan_item_paid.src_id NOT NULL → skip item tanpa id valid
                    continue;
                }
                if (isset($existingSrcIds[$src_item_id])) {
                    continue; // sudah diarsip
                }

                // Batasi panjang added_by agar aman ke varchar(32) di tabel _paid
                $addedBy = isset($it->added_by) ? (string)$it->added_by : null;
                if ($addedBy !== null && strlen($addedBy) > 32) {
                    $addedBy = substr($addedBy, 0, 32);
                }

                $mapItem = [
                    'src_id'          => $src_item_id,                    // NOT NULL (WAJIB)
                    'pesanan_src_id'  => (int)$order->id,
                    'pesanan_paid_id' => (int)$pesanan_paid_id,
                    'produk_id'       => isset($it->produk_id) ? (int)$it->produk_id : null,
                    'nama'            => isset($it->nama) ? (string)$it->nama : null,
                    'qty'             => (int)($it->qty ?? 0),
                    'harga'           => (int)($it->harga ?? 0),
                    'subtotal'        => (int)($it->subtotal ?? 0),
                    'added_by'        => $addedBy,                        // varchar(32) di _paid
                    'tambahan'        => (int)($it->tambahan ?? 0),
                    'created_at'      => !empty($it->created_at) ? $it->created_at : null,
                    'archived_at'     => date('Y-m-d H:i:s'),
                ];
                $this->db->insert('pesanan_item_paid', $mapItem);
            }

            return true;
        }


    /* =========================
     * Stopwatch Kasir (opsional)
     * ========================= */
    /** Pastikan kasir_start_at terisi sekali (panggil saat kasir membuka detail) */
    public function ensure_kasir_start(int $id){
        $row = $this->db->select('kasir_start_at')->get_where('pesanan', ['id'=>$id])->row();
        if ($row && empty($row->kasir_start_at)) {
            $this->db->where('id',$id)->update('pesanan', [
                'kasir_start_at' => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
        }
    }
/**
 * Filter hanya pesanan yang jatuh dalam jendela operasional "aktif" saat ini.
 * Mendukung jam nyebrang hari (overnight), contoh Sabtu 08:00 → 01:00 (Minggu).
 * - Mengambil jam dari tabel `identitas` (kolom op_*_open / op_*_close / *_closed)
 * - Timezone dari `identitas.waktu` (fallback Asia/Makassar)
 * - Bila "kemarin wrap & hari ini normal & sekarang dini hari" → dipakai window kemarin.
 * - Opsi batas tutup eksklusif (tutup tepat di menit close).
 */
/**
 * Filter pesanan hanya dalam jendela operasional yang SEDANG AKTIF.
 *
 * - Baca jam buka/tutup per hari dari tabel identitas (op_mon_open/close, dst).
 * - Support jam nyebrang hari, contoh Sabtu 10.00 → 01.00 (Minggu).
 * - Kalau sekarang tidak berada di jam operasional (tidak dalam window), hasil = KOSONG.
 * - Kalau sekarang berada di "ekor" Sabtu (Minggu dini hari < 01.00), window = Sabtu 10.00 → Minggu 01.00.
 */
// private function _apply_today_window(): void
// {
//     // ===== 1) Ambil konfigurasi dari identitas =====
//     $row = $this->db->query("
//         SELECT waktu,
//                op_mon_open, op_mon_close, op_mon_closed,
//                op_tue_open, op_tue_close, op_tue_closed,
//                op_wed_open, op_wed_close, op_wed_closed,
//                op_thu_open, op_thu_close, op_thu_closed,
//                op_fri_open, op_fri_close, op_fri_closed,
//                op_sat_open, op_sat_close, op_sat_closed,
//                op_sun_open, op_sun_close, op_sun_closed
//         FROM identitas
//         LIMIT 1
//     ")->row();

//     // Kalau tidak ada baris identitas, batasi minimal ke HARI INI saja (anti "semua pesanan").
//     if (!$row) {
//         $today = date('Y-m-d');
//         $this->db->where('DATE(o.created_at) =', $today);
//         return;
//     }

//     // ===== 2) Timezone dan waktu sekarang =====
//     $tzStr = trim((string)($row->waktu ?? ''));
//     if ($tzStr === '') $tzStr = 'Asia/Makassar';
//     try {
//         $tz = new DateTimeZone($tzStr);
//     } catch (\Throwable $e) {
//         $tz = new DateTimeZone('Asia/Makassar');
//     }

//     $now      = new DateTime('now', $tz);
//     $today    = $now->format('Y-m-d');
//     $yestDate = (clone $now)->modify('-1 day')->format('Y-m-d');
//     $dowToday = strtolower($now->format('D'));                       // mon..sun
//     $dowYest  = strtolower((clone $now)->modify('-1 day')->format('D'));

//     // ===== 3) Helper normalisasi jam "10.00" → "10:00" =====
//     $norm = function($s) {
//         $s = trim((string)$s);
//         if ($s === '') return null;
//         $s = str_replace('.', ':', $s);
//         if (!preg_match('/^(\d{1,2}):([0-5]\d)$/', $s, $m)) return null;
//         $h = max(0, min(23, (int)$m[1]));
//         $i = (int)$m[2];
//         return sprintf('%02d:%02d', $h, $i);
//     };
//     $toMin = function($hhmm) {
//         if ($hhmm === null) return null;
//         [$h,$i] = array_map('intval', explode(':', $hhmm));
//         return $h * 60 + $i;
//     };

//     // ===== 4) Pemetaan kolom per hari =====
//     $map = [
//         'mon' => ['open'=>'op_mon_open','close'=>'op_mon_close','closed'=>'op_mon_closed'],
//         'tue' => ['open'=>'op_tue_open','close'=>'op_tue_close','closed'=>'op_tue_closed'],
//         'wed' => ['open'=>'op_wed_open','close'=>'op_wed_close','closed'=>'op_wed_closed'],
//         'thu' => ['open'=>'op_thu_open','close'=>'op_thu_close','closed'=>'op_thu_closed'],
//         'fri' => ['open'=>'op_fri_open','close'=>'op_fri_close','closed'=>'op_fri_closed'],
//         'sat' => ['open'=>'op_sat_open','close'=>'op_sat_close','closed'=>'op_sat_closed'],
//         'sun' => ['open'=>'op_sun_open','close'=>'op_sun_close','closed'=>'op_sun_closed'],
//     ];

//     $defOpen  = '08:00';
//     $defClose = '23:59';

//     $getCfg = function($dayKey) use ($row, $map, $norm, $defOpen, $defClose) {
//         if (!$row || !isset($map[$dayKey])) {
//             return ['open'=>$defOpen,'close'=>$defClose,'closed'=>0];
//         }
//         $f = $map[$dayKey];
//         $open  = $norm($row->{$f['open']}  ?? null) ?: $defOpen;
//         $close = $norm($row->{$f['close']} ?? null) ?: $defClose;
//         $closed = !empty($row->{$f['closed']}) ? 1 : 0;

//         return ['open'=>$open,'close'=>$close,'closed'=>$closed];
//     };

//     $cfgToday = $getCfg($dowToday);
//     $cfgYest  = $getCfg($dowYest);

//     // ===== 5) Hitung window per hari (start/end DateTime string) =====
//     $buildWindow = function($dateYmd, array $cfg) use ($toMin) {
//         if (!empty($cfg['closed'])) {
//             return [null, null, false]; // closed
//         }

//         $open  = $cfg['open'];
//         $close = $cfg['close'];

//         $oMin = $toMin($open);
//         $cMin = $toMin($close);

//         if ($oMin === null || $cMin === null) {
//             return [null, null, false];
//         }

//         // 24 jam (open == close)
//         if ($oMin === $cMin) {
//             $start = $dateYmd.' 00:00:00';
//             $end   = (new DateTime($dateYmd.' 00:00:00'))->modify('+1 day')->format('Y-m-d H:i:s');
//             return [$start, $end, false];
//         }

//         // Normal (tidak wrap)
//         if ($cMin > $oMin) {
//             $start = $dateYmd.' '.$open.':00';
//             $end   = $dateYmd.' '.$close.':00';
//             return [$start, $end, false];
//         }

//         // Wrap (nyebrang hari) contoh 10:00 → 01:00
//         $start = $dateYmd.' '.$open.':00';
//         $end   = (new DateTime($dateYmd.' 00:00:00'))->modify('+1 day')->format('Y-m-d').' '.$close.':00';
//         return [$start, $end, true];
//     };

//     [$startToday, $endToday, $wrapToday] = $buildWindow($today, $cfgToday);
//     [$startYest, $endYest, $wrapYest]    = $buildWindow($yestDate, $cfgYest);

//     // ===== 6) Tentukan window aktif berdasarkan "now" =====
//     $start = null;
//     $end   = null;

//     $nowTs = $now->getTimestamp();

//     $inWindow = function($startStr, $endStr, $nowTs, DateTimeZone $tz) {
//         if (!$startStr || !$endStr) return false;
//         $s = new DateTime($startStr, $tz);
//         $e = new DateTime($endStr, $tz);
//         $sTs = $s->getTimestamp();
//         $eTs = $e->getTimestamp();
//         return ($nowTs >= $sTs && $nowTs < $eTs);
//     };

//     // Prioritas: window hari ini dulu
//     if ($inWindow($startToday, $endToday, $nowTs, $tz)) {
//         $start = $startToday;
//         $end   = $endToday;
//     }
//     // Kalau tidak sedang dalam window hari ini, cek apakah dia ekor window kemarin (wrap)
//     elseif ($wrapYest && $inWindow($startYest, $endYest, $nowTs, $tz)) {
//         $start = $startYest;
//         $end   = $endYest;
//     }

//     // ===== 7) Terapkan ke query utama =====
//     if (!$start || !$end) {
//         // Tidak sedang jam operasional → kosongkan hasil
//         $this->db->where('1 = 0', null, false);
//         log_message('debug', sprintf(
//             '[POS WINDOW] CLOSED now=%s tz=%s',
//             $now->format('Y-m-d H:i:s'),
//             $tz->getName()
//         ));
//         return;
//     }

//     $this->db->where('o.created_at >=', $start);
//     $this->db->where('o.created_at <',  $end);

//     log_message('debug', sprintf(
//         '[POS WINDOW] now=%s tz=%s start=%s end=%s',
//         $now->format('Y-m-d H:i:s'),
//         $tz->getName(),
//         $start,
//         $end
//     ));
// }

/**
 * Filter pesanan hanya dalam jendela operasional yang SEDANG AKTIF (+grace 10 menit).
 *
 * - Baca jam buka/tutup per hari dari tabel identitas (op_mon_open/close, dst).
 * - Support jam nyebrang hari, contoh Sabtu 10.00 → 01.00 (Minggu).
 * - Kalau sekarang tidak berada di jam operasional (+grace), hasil = KOSONG.
 * - Kalau sekarang berada di "ekor" Sabtu (Minggu dini hari < 01.00 + 10 menit), window = Sabtu 10.00 → Minggu 01.00.
 * - Order yang dibuat sampai 10 menit setelah jam tutup tetap ikut tampil.
 */
private function _apply_today_window(): void
{
    // ===== 1) Ambil konfigurasi dari identitas =====
    $row = $this->db->query("
        SELECT waktu,
               op_mon_open, op_mon_close, op_mon_closed,
               op_tue_open, op_tue_close, op_tue_closed,
               op_wed_open, op_wed_close, op_wed_closed,
               op_thu_open, op_thu_close, op_thu_closed,
               op_fri_open, op_fri_close, op_fri_closed,
               op_sat_open, op_sat_close, op_sat_closed,
               op_sun_open, op_sun_close, op_sun_closed
        FROM identitas
        LIMIT 1
    ")->row();

    // Kalau tidak ada baris identitas, batasi minimal ke HARI INI saja (anti "semua pesanan").
    if (!$row) {
        $today = date('Y-m-d');
        $this->db->where('DATE(o.created_at) =', $today);
        return;
    }

    // ===== 2) Timezone dan waktu sekarang =====
    $tzStr = trim((string)($row->waktu ?? ''));
    if ($tzStr === '') $tzStr = 'Asia/Makassar';
    try {
        $tz = new DateTimeZone($tzStr);
    } catch (\Throwable $e) {
        $tz = new DateTimeZone('Asia/Makassar');
    }

    $now      = new DateTime('now', $tz);
    $today    = $now->format('Y-m-d');
    $yestDate = (clone $now)->modify('-1 day')->format('Y-m-d');
    $dowToday = strtolower($now->format('D'));                       // mon..sun
    $dowYest  = strtolower((clone $now)->modify('-1 day')->format('D'));

    // ===== 3) Helper normalisasi jam "10.00" → "10:00" =====
    $norm = function($s) {
        $s = trim((string)$s);
        if ($s === '') return null;
        $s = str_replace('.', ':', $s);
        if (!preg_match('/^(\d{1,2}):([0-5]\d)$/', $s, $m)) return null;
        $h = max(0, min(23, (int)$m[1]));
        $i = (int)$m[2];
        return sprintf('%02d:%02d', $h, $i);
    };
    $toMin = function($hhmm) {
        if ($hhmm === null) return null;
        [$h,$i] = array_map('intval', explode(':', $hhmm));
        return $h * 60 + $i;
    };

    // ===== 4) Pemetaan kolom per hari =====
    $map = [
        'mon' => ['open'=>'op_mon_open','close'=>'op_mon_close','closed'=>'op_mon_closed'],
        'tue' => ['open'=>'op_tue_open','close'=>'op_tue_close','closed'=>'op_tue_closed'],
        'wed' => ['open'=>'op_wed_open','close'=>'op_wed_close','closed'=>'op_wed_closed'],
        'thu' => ['open'=>'op_thu_open','close'=>'op_thu_close','closed'=>'op_thu_closed'],
        'fri' => ['open'=>'op_fri_open','close'=>'op_fri_close','closed'=>'op_fri_closed'],
        'sat' => ['open'=>'op_sat_open','close'=>'op_sat_close','closed'=>'op_sat_closed'],
        'sun' => ['open'=>'op_sun_open','close'=>'op_sun_close','closed'=>'op_sun_closed'],
    ];

    $defOpen  = '08:00';
    $defClose = '23:59';

    $getCfg = function($dayKey) use ($row, $map, $norm, $defOpen, $defClose) {
        if (!$row || !isset($map[$dayKey])) {
            return ['open'=>$defOpen,'close'=>$defClose,'closed'=>0];
        }
        $f = $map[$dayKey];
        $open   = $norm($row->{$f['open']}  ?? null) ?: $defOpen;
        $close  = $norm($row->{$f['close']} ?? null) ?: $defClose;
        $closed = !empty($row->{$f['closed']}) ? 1 : 0;

        return ['open'=>$open,'close'=>$close,'closed'=>$closed];
    };

    $cfgToday = $getCfg($dowToday);
    $cfgYest  = $getCfg($dowYest);

    // ===== 5) Hitung window per hari (start/end DateTime string) =====
    $buildWindow = function($dateYmd, array $cfg) use ($toMin) {
        if (!empty($cfg['closed'])) {
            return [null, null, false]; // closed
        }

        $open  = $cfg['open'];
        $close = $cfg['close'];

        $oMin = $toMin($open);
        $cMin = $toMin($close);

        if ($oMin === null || $cMin === null) {
            return [null, null, false];
        }

        // 24 jam (open == close)
        if ($oMin === $cMin) {
            $start = $dateYmd.' 00:00:00';
            $end   = (new DateTime($dateYmd.' 00:00:00'))
                        ->modify('+1 day')->format('Y-m-d H:i:s');
            return [$start, $end, false];
        }

        // Normal (tidak wrap)
        if ($cMin > $oMin) {
            $start = $dateYmd.' '.$open.':00';
            $end   = $dateYmd.' '.$close.':00';
            return [$start, $end, false];
        }

        // Wrap (nyebrang hari) contoh 10:00 → 01:00
        $start = $dateYmd.' '.$open.':00';
        $end   = (new DateTime($dateYmd.' 00:00:00'))
                    ->modify('+1 day')->format('Y-m-d').' '.$close.':00';
        return [$start, $end, true];
    };

    [$startToday, $endToday, $wrapToday] = $buildWindow($today, $cfgToday);
    [$startYest,  $endYest,  $wrapYest]  = $buildWindow($yestDate, $cfgYest);

    // ===== 6) Tentukan window aktif berdasarkan "now" (+ grace 10 menit) =====
    $start = null;
    $end   = null;

    $nowTs        = $now->getTimestamp();
    $graceSeconds = 20 * 60; // 10 menit setelah jam tutup

    $inWindow = function($startStr, $endStr, $nowTs, DateTimeZone $tz) use ($graceSeconds) {
        if (!$startStr || !$endStr) return false;

        $s   = new DateTime($startStr, $tz);
        $e   = new DateTime($endStr,   $tz);
        $sTs = $s->getTimestamp();
        $eTs = $e->getTimestamp();

        // Window aktif: dari start s/d end + 10 menit
        return ($nowTs >= $sTs && $nowTs < ($eTs + $graceSeconds));
    };

    // Prioritas: window hari ini dulu
    if ($inWindow($startToday, $endToday, $nowTs, $tz)) {
        $start = $startToday;
        $end   = $endToday;
    }
    // Kalau tidak sedang dalam window hari ini, cek apakah dia ekor window kemarin (wrap)
    elseif ($wrapYest && $inWindow($startYest, $endYest, $nowTs, $tz)) {
        $start = $startYest;
        $end   = $endYest;
    }

    // ===== 7) Terapkan ke query utama =====
    if (!$start || !$end) {
        // Tidak sedang jam operasional (di luar +10 menit) → kosongkan hasil
        $this->db->where('1 = 0', null, false);
        log_message('debug', sprintf(
            '[POS WINDOW] CLOSED now=%s tz=%s',
            $now->format('Y-m-d H:i:s'),
            $tz->getName()
        ));
        return;
    }

    // Untuk data, perpanjang batas atas ke end + 10 menit supaya order baru
    // setelah tutup (maks 10 menit) tetap ikut tampil
    $endFilter = (new DateTime($end, $tz))
                    ->modify('+'.$graceSeconds.' seconds')
                    ->format('Y-m-d H:i:s');

    $this->db->where('o.created_at >=', $start);
    $this->db->where('o.created_at <',  $endFilter);

    log_message('debug', sprintf(
        '[POS WINDOW] now=%s tz=%s start=%s end=%s endFilter=%s',
        $now->format('Y-m-d H:i:s'),
        $tz->getName(),
        $start,
        $end,
        $endFilter
    ));
}


/** Ringkas item per order (opsional filter kategori 1/2) */
public function compact_items_for_order(int $order_id, ?int $cat_id = null): array
{
    $order_id = (int)$order_id;

    $this->db->from('pesanan_item pi')
             ->join('produk p', 'p.id = pi.produk_id', 'left')
             ->select('COALESCE(pi.nama, p.nama) AS nama', false)
             ->select('SUM(pi.qty) AS qty', false)
             ->where('pi.pesanan_id', $order_id);

    if ($cat_id === 1 || $cat_id === 2) {
        // pakai pi.id_kategori bila ada, jika null pakai p.kategori_id
        $this->db->group_start()
                 ->where('pi.id_kategori', (int)$cat_id)
                 ->or_group_start()
                    ->where('pi.id_kategori', null)
                    ->where('p.kategori_id', (int)$cat_id)
                 ->group_end()
                 ->group_end();
    }

    $this->db->group_by('COALESCE(pi.nama, p.nama)', false)
             ->order_by('COALESCE(pi.nama, p.nama)', 'ASC', false);

    $rows = $this->db->get()->result();
    foreach ($rows as $r) { $r->nama = (string)($r->nama ?? '-'); $r->qty = (int)$r->qty; }
    return $rows;
}
/** Ringkas item utk banyak order sekaligus → [order_id => [{nama, qty}, ...]] */
public function compact_items_for_orders(array $order_ids, ?int $cat_id = null): array
{
    $order_ids = array_values(array_unique(array_filter(array_map('intval',$order_ids))));
    if (!$order_ids) return [];

    $this->db->from('pesanan_item pi')
             ->join('produk p', 'p.id = pi.produk_id', 'left')
             ->select('pi.pesanan_id')
             ->select('COALESCE(pi.nama, p.nama) AS nama', false)
             ->select('SUM(pi.qty) AS qty', false)
             ->where_in('pi.pesanan_id', $order_ids);

    if ($cat_id === 1 || $cat_id === 2) {
        // pakai pi.id_kategori jika ada; fallback p.kategori_id bila null
        $this->db->group_start()
                 ->where('pi.id_kategori', (int)$cat_id)
                 ->or_group_start()
                    ->where('pi.id_kategori', null)
                    ->where('p.kategori_id', (int)$cat_id)
                 ->group_end()
                 ->group_end();
    }

    $this->db->group_by(['pi.pesanan_id','COALESCE(pi.nama, p.nama)'], false)
             ->order_by('pi.pesanan_id','ASC')
             ->order_by('COALESCE(pi.nama, p.nama)','ASC', false);

    $rows = $this->db->get()->result();

    $out = [];
    foreach ($rows as $r) {
        $out[(int)$r->pesanan_id][] = (object)[
            'nama' => (string)($r->nama ?? '-'),
            'qty'  => (int)$r->qty
        ];
    }
    return $out;
}


    // /** Ringkas item per order untuk kitchen/bar → [nama, qty] (opsional filter kategori 1/2) */
    // public function compact_items_for_order(int $order_id, ?int $cat_id = null): array
    // {
    //     $order_id = (int)$order_id;

    //     $this->db->from('pesanan_item pi');
    //     $this->db->join('produk p', 'p.id = pi.produk_id', 'left');

    //     // nama ditentukan: prioritas pi.nama, fallback p.nama
    //     $this->db->select('COALESCE(pi.nama, p.nama) AS nama', false);
    //     $this->db->select('SUM(pi.qty) AS qty', false);

    //     $this->db->where('pi.pesanan_id', $order_id);

    //     if ($cat_id === 1 || $cat_id === 2) {
    //         $this->db->where('pi.id_kategori', (int)$cat_id);
    //     }

    //     // gabung per nama, urutkan alfabet biar rapi
    //     $this->db->group_by('COALESCE(pi.nama, p.nama)', false);
    //     $this->db->order_by('COALESCE(pi.nama, p.nama)', 'ASC', false);

    //     $rows = $this->db->get()->result();
    //     // normalisasi tipe
    //     foreach ($rows as $r) {
    //         $r->nama = (string)($r->nama ?? '-');
    //         $r->qty  = (int)$r->qty;
    //     }
    //     return $rows;
    // }

    /**
 * Data minimal untuk broadcast WA setelah pesanan ditandai paid.
 * @param array $ids daftar ID pesanan yang sukses jadi paid
 * @return array of stdClass
 */
public function get_orders_for_wa(array $ids)
{
    $ids = array_values(array_unique(array_map('intval', $ids)));
    if (!$ids) return [];

    return $this->db->select('
            id,
            nomor,
            nama,
            total,
            kode_unik,
            delivery_fee,
            grand_total,
            paid_method,
            created_at,
            paid_at,
            customer_phone
        ')
        ->from('pesanan')
        ->where_in('id', $ids)
        ->get()
        ->result();
}


// di model:
public function get_order_any($id){
  $live = $this->get_order_with_items($id);
  if ($live) return $live;
  return $this->get_order_with_items_archived($id); // bikin versi baca dari *_paid
}


private function _bump_terlaris_for_order($pesanan_id){
    $pesanan_id = (int)$pesanan_id;

    // bisa kamu pindah ke config
    $decay   = 0.97; // ~3% turun per hari
    $capDays = 90;   // batasi dampak decay max 90 hari (opsional)

    $sql = "
        UPDATE produk p
        JOIN (
          SELECT produk_id, SUM(qty) AS s
          FROM pesanan_item
          WHERE pesanan_id = ?
          GROUP BY produk_id
          HAVING s > 0
        ) x ON x.produk_id = p.id
        SET
          -- lifetime counter (aman terhadap NULL)
          p.terlaris = COALESCE(p.terlaris, 0) + x.s,

          -- trending score dengan exponential decay harian
          p.terlaris_score = (
            COALESCE(p.terlaris_score, 0) *
            POW(?, GREATEST(0,
                LEAST(?, TIMESTAMPDIFF(DAY, COALESCE(p.terlaris_score_updated_at, NOW()), NOW()))
            ))
          ) + x.s,
          p.terlaris_score_updated_at = NOW()
    ";
    $this->db->query($sql, [$pesanan_id, $decay, $capDays]);
}

/** ================== LOYALTY / VOUCHER CAFE ================== */

/** Normalisasi nomor HP ke msisdn (62...) sederhana */
private function _norm_phone($s){
    $s = preg_replace('/\D+/', '', (string)$s); // keep digits only
    if ($s === '') return '';
    if (strpos($s, '62') === 0) return $s;
    if (strpos($s, '0') === 0)  return '62'.substr($s, 1);
    return $s;
}

/** Token hex 32 uniq (hindari "token sama semua") */
private function _rand_token_32(){
    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes(16));
    }
    return md5(uniqid('', true).mt_rand());
}

/** Tanggal habis masa berlaku: 31-12-(tahun berjalan) */
private function _end_of_year_date(){
    return date('Y').'-12-31';
}
// ==== helper baru: first day of next month ====
private function _first_of_next_month_date($refTs = null){
    $t = $refTs ? strtotime($refTs) : time();
    // contoh: 2025-11-13 -> 2025-12-01
    return date('Y-m-01', strtotime('first day of +1 month', $t));
}

/**
 * Upsert baris voucher_cafe untuk 1 order PAID.
 * - points += (kode_unik + floor(total/1000))
 * - transaksi_count += 1
 * - total_rupiah += total
 * - first_paid_at di-set saat pertama kali
 * - last_paid_at selalu di-update
 * - expired_at di-set ke akhir tahun berjalan
 */
/**
 * Reset mingguan: periode = Minggu 00:00 s.d. Sabtu 23:59:59 (WITA)
 * expired_at = TANGGAL Minggu berikutnya (DATE, jam 00:00 WITA secara konseptual)
 * - Jika pada transaksi baru paid_at >= expired_at (Minggu 00:00), maka RESET (mulai agregat baru).
 * - Jika belum lewat, tetap AKUMULASI di pekan berjalan.
 */
/**
 * Reset mingguan: periode = Minggu 00:00 s.d. Sabtu 23:59:59 (WITA)
 * expired_at = TANGGAL Minggu berikutnya (DATE)
 * - Jika pada transaksi baru paid_at >= expired_at (Minggu 00:00), maka RESET (mulai agregat baru).
 * - Jika belum lewat, tetap AKUMULASI di pekan berjalan.
 *
 * CATATAN:
 * - Di sini kita matikan $db_debug sementara supaya error voucher TIDAK mematikan proses mark_paid.
 * - Kalau ada error, cuma di-log.
 */



 private function _calc_loyalty_points_order($o): int
    {
        if (!$o) return 0;

        // total belanja menu (bukan grand_total)
        $total_menu = (int)($o->total ?? 0);
        if ($total_menu < 0) $total_menu = 0;

        // bonus poin dari kode unik
        $kode_unik = (int)($o->kode_unik ?? 0);
        if ($kode_unik < 0) $kode_unik = 0;

        // 1 poin per Rp 1.000 belanja
        $base = intdiv($total_menu, 1000);

        $poin = $kode_unik + $base;
        if ($poin < 0) $poin = 0;

        return $poin;
    }



private function _voucher_cafe_upsert_from_order($order_row, $paid_at)
{
    // ===== 0) Kalau tabel nggak ada, langsung keluar =====
    if (!$this->db->table_exists('voucher_cafe')) {
        log_message('debug', '[LOYALTY] voucher_cafe tidak ada, skip.');
        return;
    }

    // ===== 1) Matikan db_debug sementara supaya query error tidak fatal =====
    $oldDebug = $this->db->db_debug;
    $this->db->db_debug = false;

    try {

        // ===== 2) Normalisasi HP =====
        $hp_raw  = isset($order_row->customer_phone) ? $order_row->customer_phone
                 : (isset($order_row->no_hp) ? $order_row->no_hp : '');
        $hp      = $this->_norm_phone($hp_raw);
        if ($hp === '') {
            log_message('debug', '[LOYALTY] order ID '.($order_row->id ?? '??').' tanpa HP, skip voucher.');
            return;
        }

        $nama    = isset($order_row->nama) ? trim((string)$order_row->nama) : null;
        $kode    = (int)($order_row->kode_unik ?? 0);

                // ===== 3+4) Hitung poin pakai helper resmi (tanpa ongkir) =====
        // supaya ANGKA poin di DB = ANGKA poin yang ditulis di WA
        $poin_add = $this->_calc_loyalty_points_order($order_row);

        // Untuk statistik rupiah pekanan, kita masih boleh pakai grand_total/total
        if (isset($order_row->grand_total)) {
            $total_rp = (int)$order_row->grand_total;
        } elseif (isset($order_row->total)) {
            $total_rp = (int)$order_row->total;
        } else {
            $subtotal     = (int)($order_row->subtotal ?? 0);
            $delivery_fee = (int)($order_row->delivery_fee ?? 0);
            $kode         = (int)($order_row->kode_unik ?? 0);
            $total_rp     = $subtotal + $delivery_fee + $kode;
        }
        if ($total_rp < 0) $total_rp = 0;

        log_message('debug', sprintf(
            '[LOYALTY] order ID %s, hp=%s, total_rp=%d, poin_add=%d',
            $order_row->id ?? '??', $hp, $total_rp, $poin_add
        ));


        // ===== 5) Waktu & batas reset pekan (WITA) =====
        $tz = new DateTimeZone('Asia/Makassar'); // WITA
        try {
            $paidDt = new DateTime($paid_at, $tz);
        } catch (\Throwable $e) {
            $paidDt = new DateTime('now', $tz);
        }

        // 'w' => 0=Sunday..6=Saturday
        $w          = (int)$paidDt->format('w'); // 0=Min
        $weekStart  = (clone $paidDt)->modify('-'.$w.' days')->setTime(0,0,0); // Minggu 00:00 pekan ini
        $nextReset  = (clone $weekStart)->modify('+7 days');                   // Minggu depan 00:00
        $expiredYmd = $nextReset->format('Y-m-d'); // *** DATE ***

        // ===== 6) Ambil data existing per phone =====
        $exist = $this->db->get_where('voucher_cafe', ['customer_phone' => $hp])->row();
        $err   = $this->db->error();
        if (!empty($err['code'])) {
            log_message('error', 'voucher_cafe SELECT error: '.$err['code'].' - '.$err['message']);
            return;
        }

        // Helper baca expired_at → DateTime WITA
        $parseExpired = function($ymd) use ($tz){
            $ymd = trim((string)$ymd);
            if ($ymd === '' || $ymd === '0000-00-00') return null;
            try{
                $dt = new DateTime($ymd, $tz);
                $dt->setTime(0,0,0);
                return $dt;
            }catch(\Throwable $e){
                return null;
            }
        };

        if ($exist) {
            $expiredPrev = $parseExpired($exist->expired_at);
            // Jika belum pernah diset, anggap periode baru
            $isNewWeek = !$expiredPrev ? true : ($paidDt >= $expiredPrev);

            if ($isNewWeek) {
                log_message('debug', '[LOYALTY] reset pekan baru untuk '.$hp);
                $upd = [
                    'points'          => $poin_add,
                    'transaksi_count' => 1,
                    'total_rupiah'    => $total_rp,
                    'first_paid_at'   => $paidDt->format('Y-m-d H:i:s'),
                    'last_paid_at'    => $paidDt->format('Y-m-d H:i:s'),
                    'expired_at'      => $expiredYmd, // DATE: Minggu berikutnya
                    'customer_name'   => ($nama !== null && $nama !== '') ? $nama : $exist->customer_name,
                    'updated_at'      => $paidDt->format('Y-m-d H:i:s'),
                ];
            } else {
                log_message('debug', '[LOYALTY] akumulasi pekan berjalan untuk '.$hp);
                $upd = [
                    'points'          => (int)$exist->points + $poin_add,
                    'transaksi_count' => (int)$exist->transaksi_count + 1,
                    'total_rupiah'    => (int)$exist->total_rupiah + $total_rp,
                    'last_paid_at'    => $paidDt->format('Y-m-d H:i:s'),
                    'expired_at'      => $expiredYmd,
                    'customer_name'   => ($nama !== null && $nama !== '') ? $nama : $exist->customer_name,
                    'updated_at'      => $paidDt->format('Y-m-d H:i:s'),
                ];
            }

            $this->db->where('id', $exist->id)->update('voucher_cafe', $upd);
            $err = $this->db->error();
            if (!empty($err['code'])) {
                log_message('error', 'voucher_cafe UPDATE error: '.$err['code'].' - '.$err['message']);
            } else {
                log_message('debug', '[LOYALTY] voucher UPDATE OK untuk '.$hp);
            }

        } else {
            // ===== INSERT baru (pekan berjalan) =====
            $token = $this->_rand_token_32_unique(); // pastikan unik

            $ins = [
                'customer_phone'  => $hp,
                'customer_name'   => ($nama !== null && $nama !== '') ? $nama : null,
                'points'          => $poin_add,
                'transaksi_count' => 1,
                'total_rupiah'    => $total_rp,
                'first_paid_at'   => $paidDt->format('Y-m-d H:i:s'),
                'last_paid_at'    => $paidDt->format('Y-m-d H:i:s'),
                'expired_at'      => $expiredYmd,      // DATE
                'token'           => $token,
                'created_at'      => $paidDt->format('Y-m-d H:i:s'),
                'updated_at'      => $paidDt->format('Y-m-d H:i:s'),
            ];
            $this->db->insert('voucher_cafe', $ins);
            $err = $this->db->error();
            if (!empty($err['code'])) {
                log_message('error', 'voucher_cafe INSERT error: '.$err['code'].' - '.$err['message']);
            } else {
                log_message('debug', '[LOYALTY] voucher INSERT OK untuk '.$hp.' token='.$token);
            }
        }

    } finally {
        // PENTING: selalu kembalikan db_debug ke nilai awal
        $this->db->db_debug = $oldDebug;
    }
}


/**
 * Generate token 32 char dan pastikan unik (hindari token sama).
 * Menggunakan OPENSSL/Random_bytes bila ada.
 */
private function _rand_token_32_unique(){
    $try = 0;
    do{
        $try++;
        // token 32 hex chars
        if (function_exists('random_bytes')) {
            $token = bin2hex(random_bytes(16));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $token = bin2hex(openssl_random_pseudo_bytes(16));
        } else {
            $token = md5(uniqid((string)mt_rand(), true));
        }
        $exist = $this->db->get_where('voucher_cafe', ['token' => $token])->row();
        if (!$exist) return $token;
    } while ($try < 5);
    // fallback ekstrem (sangat kecil kemungkinannya)
    return md5(uniqid((string)mt_rand(), true).microtime(true));
}



}
