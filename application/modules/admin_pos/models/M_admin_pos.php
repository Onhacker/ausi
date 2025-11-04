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
        o.grand_total, o.tutup_transaksi, o.kode_unik, o.catatan
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
    public function bulk_mark_paid(array $ids){
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (!$ids) return ["ok_count"=>0];

        $ok_count = 0;
        $blocked  = []; // paid_method kosong
        $already  = []; // sudah paid/canceled
        $notfound = [];
        $errors   = [];

        $this->db->trans_begin();
        foreach ($ids as $id){
            $row = $this->db->get_where('pesanan', ['id'=>$id])->row();

            if (!$row){ $notfound[] = $id; continue; }

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
            $startTs = null;
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

                // Arsipkan ke *_paid
                $this->_archive_paid($id);

                // Hapus file QRIS bila ada
                $this->_delete_qris_file($id);
            } else {
                $errors[] = $id;
            }

        }

        if ($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
        } else {
            $this->db->trans_commit();
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

    /** Batasi data ke jendela waktu operasional "hari ini"
 *  Sumber jam: tabel identitas (kolom op_*_open / op_*_close dan *_closed)
 *  Timezone: identitas.waktu (fallback Asia/Jakarta)
 */
/**
 * Filter hanya pesanan dalam jendela operasional hari ini.
 * Support jam nyebrang hari, misal 10:00 → 03:00.
 * PANGGIL di akhir _base_q(): $this->_apply_today_window();
 */
private function _apply_today_window(): void
{
    // Ambil konfigurasi tanpa mereset QB utama
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

    // Default TZ & jam buka-tutup
    $tzStr = $row && trim((string)$row->waktu) !== '' ? trim((string)$row->waktu) : 'Asia/Jakarta';
    try { $tz = new DateTimeZone($tzStr); } catch (\Throwable $e) { $tz = new DateTimeZone('Asia/Jakarta'); }

    $now = new DateTime('now', $tz);
    $dow = strtolower($now->format('D')); // mon..sun

    $map = [
        'mon' => ['open'=>'op_mon_open','close'=>'op_mon_close','closed'=>'op_mon_closed'],
        'tue' => ['open'=>'op_tue_open','close'=>'op_tue_close','closed'=>'op_tue_closed'],
        'wed' => ['open'=>'op_wed_open','close'=>'op_wed_close','closed'=>'op_wed_closed'],
        'thu' => ['open'=>'op_thu_open','close'=>'op_thu_close','closed'=>'op_thu_closed'],
        'fri' => ['open'=>'op_fri_open','close'=>'op_fri_close','closed'=>'op_fri_closed'],
        'sat' => ['open'=>'op_sat_open','close'=>'op_sat_close','closed'=>'op_sat_closed'],
        'sun' => ['open'=>'op_sun_open','close'=>'op_sun_close','closed'=>'op_sun_closed'],
    ];

    // Ambil jam dari identitas, fallback ke 10:00–03:00 jika kosong
    $open  = '10:00';
    $close = '03:00';
    $closedFlag = false;

    if (isset($map[$dow]) && $row) {
        $f = $map[$dow];
        $open  = trim((string)($row->{$f['open']}  ?? '')) ?: $open;
        $close = trim((string)($row->{$f['close']} ?? '')) ?: $close;
        $closedFlag = !empty($row->{$f['closed']});
    }

    // Jika hari ini ditandai tutup → kosongkan hasil
    if ($closedFlag) {
        $this->db->where('1=', '0', false);
        return;
    }

    // Hitung window start-end (support cross-midnight)
    $today      = $now->format('Y-m-d');
    $yesterday  = (clone $now)->modify('-1 day')->format('Y-m-d');
    $tomorrow   = (clone $now)->modify('+1 day')->format('Y-m-d');

    // bandingkan waktu HH:ii
    $nowHm = $now->format('H:i');

    // apakah nyebrang hari? (contoh 10:00 → 03:00)
    $wrap = ($close <= $open);

    if ($wrap) {
        // Jika sekarang sebelum/tepat jam tutup (00:00–close), berarti window mulai kemarin open → hari ini close
        if ($nowHm <= $close) {
            $start = $yesterday.' '.$open.':00';
            $end   = $today   .' '.$close.':00';
        } else {
            // Selain itu: window mulai hari ini open → besok close
            $start = $today  .' '.$open.':00';
            $end   = $tomorrow.' '.$close.':00';
        }
    } else {
        // Tidak nyebrang (misal 08:00–22:00)
        $start = $today.' '.$open.':00';
        $end   = $today.' '.$close.':00';
    }

    // Terapkan filter ke query utama
    $this->db->where('o.created_at >=', $start);
    $this->db->where('o.created_at <=', $end);
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
public function get_orders_for_wa(array $ids){
    $ids = array_values(array_unique(array_map('intval',$ids)));
    if (!$ids) return [];

    return $this->db->select('
            id,
            nomor,
            nama,
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


}
