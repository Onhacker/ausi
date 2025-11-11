<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_admin_billiard extends CI_Model {

    private $table = 'pesanan_billiard pb';
    private $table_paid = 'billiard_paid';

    private $column_order  = [
        null,               // no
        'pb.kode_booking',  // kode
        'pb.nama_meja',     // meja
        
        'pb.created_at',    // durasi
        'pb.tanggal',       // waktu
        'pb.harga_per_jam', // harga/jam
        'pb.grand_total',   // grand
        'pb.status',        // status
        'pb.metode_bayar',  // metode
        null                // aksi
    ];

    private $column_search = ['pb.kode_booking','pb.nama','pb.no_hp','pb.nama_meja','pb.status','pb.metode_bayar'];
    private $order         = ['pb.created_at'=>'DESC','pb.id_pesanan'=>'DESC'];

    private $max_rows   = 200;
    // null = default (exclude 'terkonfirmasi'); 'all' = tanpa filter; array = where_in
    private $status_filter = null;

    public function __construct(){
        parent::__construct();
    }

    public function set_max_rows($n = 200){ $this->max_rows = max(0,(int)$n); }

    /** @param null|string|array $status  null=default(exclude 'terkonfirmasi'), 'all'=no filter, array=['menunggu_bayar','verifikasi'] */
    public function set_status_filter($status){
        if ($status === null){
            $this->status_filter = null;
        } elseif ($status === 'all' || $status === []){
            $this->status_filter = 'all';
        } elseif (is_array($status)){
            $status = array_values(array_filter(array_map('strtolower',$status)));
            $this->status_filter = $status ?: 'all';
        } else {
            $this->status_filter = [strtolower((string)$status)];
        }
    }

    private function _base_q(){
    $this->db->from($this->table);
    $this->db->select('
        pb.id_pesanan, pb.kode_booking, pb.access_token,
        pb.status, pb.nama, pb.no_hp,
        pb.meja_id, pb.nama_meja,
        pb.tanggal, pb.jam_mulai, pb.jam_selesai, pb.durasi_jam,
        pb.harga_per_jam, pb.subtotal, pb.kode_unik, pb.grand_total,
        pb.metode_bayar, pb.created_at, pb.updated_at, pb.edit_count
    ');

    // ===== tambahkan ini untuk batasi 2 hari terakhir =====
    // $this->db->where('pb.tanggal >=', date('Y-m-d', strtotime('-2 days')));
    // $this->db->where('pb.tanggal <=', date('Y-m-d'));

    // ===== filter status (seperti semula) =====
    if ($this->status_filter === null){
        $this->db->where('pb.status <>', 'terkonfirmasi');
    } elseif ($this->status_filter !== 'all' && is_array($this->status_filter)){
        $this->db->where_in('pb.status', $this->status_filter);
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
            if ($col) $this->db->order_by($col,$dir);
        } else {
            $this->db->order_by('pb.created_at','DESC');
            $this->db->order_by('pb.id_pesanan','DESC');
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

    public function get_order(int $id){
        return $this->db->get_where('pesanan_billiard', ['id_pesanan'=>$id])->row();
    }

    public function get_stats(){
        $tsCol = $this->db->field_exists('updated_at','pesanan_billiard') ? 'updated_at' : 'created_at';
        $row = $this->db->select("COUNT(*) AS total, MAX(id_pesanan) AS max_id, MAX($tsCol) AS last_ts")
                        ->get('pesanan_billiard')->row();
        return $row ?: (object)['total'=>0,'max_id'=>0,'last_ts'=>null];
    }

    /* =========================
     * Bulk actions
     * ========================= */
  public function bulk_mark_confirmed(array $ids){
    $ids = array_values(array_unique(array_map('intval', $ids)));
    if (!$ids) return ["ok_count"=>0];

    $ok_count = 0;
    $blocked  = [];
    $ok_ids   = []; 
    $already  = [];
    $notfound = [];
    $errors   = [];

    $copied_count   = 0;   // baris yang tersalin ke billiard_paid
    $copied_skipped = [];  // id yang dilewati karena sudah ada di billiard_paid

    $this->db->trans_begin();
    foreach ($ids as $id){
        $row = $this->db->get_where('pesanan_billiard', ['id_pesanan'=>$id])->row();
        if (!$row){
            $notfound[] = $id;
            continue;
        }

        $st = strtolower((string)$row->status);
        if (in_array($st, ['terkonfirmasi','batal'], true)){
            $already[] = $id;

            // walau sudah terkonfirmasi, tetap coba salin snapshot bila belum ada
            if ($st === 'terkonfirmasi' && !$this->_paid_exists($id)){
                $okIns = $this->db->insert($this->table_paid, $this->_paid_payload($row,'admin'));
                if ($okIns){
                    $copied_count++;
                } else {
                    $errors[] = $id;
                }
            } else {
                $copied_skipped[] = $id;
            }

            // penting: JANGAN push ke $ok_ids di sini,
            // karena statusnya bukan baru dikonfirmasi sekarang
            continue;
        }

        $method = trim((string)$row->metode_bayar);
        if ($method === '' || $method === null){
            $blocked[] = $id;
            continue;
        }

        // Update status → terkonfirmasi
        $now = date('Y-m-d H:i:s');
        $ok = $this->db->where('id_pesanan', $id)->update('pesanan_billiard', [
            'status'     => 'terkonfirmasi',
            'updated_at' => $now,
        ]);

        if ($ok){
            $ok_count++;
            $ok_ids[] = $id; // <=== INI YANG WAJIB, SUPAYA WA KE-TRIGGER

            // Salin snapshot ke tabel paid (idempotent)
            if (!$this->_paid_exists($id)){
                $okIns = $this->db->insert(
                    $this->table_paid,
                    $this->_paid_payload($row,'admin')
                );
                if ($okIns){
                    $copied_count++;
                } else {
                    $errors[] = $id;
                }
            } else {
                $copied_skipped[] = $id;
            }
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
        "ok_count"       => $ok_count,
        "blocked_ids"    => $blocked,
        "ok_ids"         => $ok_ids,     // <-- sekarang terisi
        "already_ids"    => $already,
        "notfound_ids"   => $notfound,
        "errors"         => $errors,
        "copied_count"   => $copied_count,
        "copied_skipped" => $copied_skipped,
    ];
}



    public function bulk_mark_canceled(array $ids){
    $ids = array_values(array_unique(array_map('intval', $ids)));
    if (!$ids) return ["ok_count"=>0, "paid_deleted"=>0, "notfound_ids"=>[], "errors"=>[]];

    $ok_count = 0;
    $paid_deleted = 0;
    $notfound = [];
    $errors = [];

    $now = date('Y-m-d H:i:s');

    $this->db->trans_begin();
    foreach ($ids as $id){
        $row = $this->db->select('id_pesanan')->from('pesanan_billiard')->where('id_pesanan',$id)->get()->row();
        if (!$row){
            // kalau ada orphan di billiard_paid, bereskan sekalian
            $this->db->delete($this->table_paid, ['id_pesanan'=>$id]);
            $paid_deleted += (int)$this->db->affected_rows();
            $notfound[] = $id;
            continue;
        }

        $ok = $this->db->where('id_pesanan', $id)->update('pesanan_billiard', [
            'status'     => 'batal',
            'updated_at' => $now,
        ]);
        if (!$ok){ $errors[] = $id; continue; }

        $ok_count++;

        // apapun status sebelumnya, begitu dibatalkan → hapus snapshot paid
        $this->db->delete($this->table_paid, ['id_pesanan'=>$id]);
        $paid_deleted += (int)$this->db->affected_rows();
    }

    if ($this->db->trans_status() === FALSE){
        $this->db->trans_rollback();
        return ["ok_count"=>0, "paid_deleted"=>0, "notfound_ids"=>$notfound, "errors"=>['tx_rollback']];
    }

    $this->db->trans_commit();
    return [
        "ok_count"      => $ok_count,
        "paid_deleted"  => $paid_deleted,
        "notfound_ids"  => $notfound,
        "errors"        => $errors,
    ];
}


    public function bulk_delete(array $ids){
    $ids = array_values(array_unique(array_map('intval',$ids)));
    if (!$ids) return ["ok_count"=>0,"confirmed_ids"=>[],"notfound_ids"=>[],"errors"=>[],"paid_deleted"=>0];

    // cek eksistensi & status
    $rows = $this->db->select('id_pesanan,status')
        ->from('pesanan_billiard')->where_in('id_pesanan',$ids)->get()->result();

    $existMap = [];
    foreach($rows as $r){ $existMap[(int)$r->id_pesanan] = strtolower((string)$r->status); }

    $confirmed = [];
    $notfound  = [];
    $ok_count  = 0;
    $errors    = [];
    $reallyDeleted = [];

    $this->db->trans_begin();

    foreach($ids as $id){
        if (!isset($existMap[$id])){ 
            $notfound[] = $id; 
            continue; 
        }
        if ($existMap[$id] === 'terkonfirmasi'){ 
            $confirmed[] = $id; 
            continue; 
        }

        $ok = $this->db->delete('pesanan_billiard',['id_pesanan'=>$id]);
        if ($ok){ 
            $ok_count++; 
            $reallyDeleted[] = $id; 
        } else { 
            $errors[] = $id; 
        }
    }

    // hapus juga snapshot di billiard_paid untuk yang benar-benar terhapus,
    // plus bereskan orphan jika user menghapus id yg ternyata notfound.
    $paid_deleted = 0;
    if ($this->db->trans_status() !== FALSE){
        $candidate = array_values(array_unique(array_merge($reallyDeleted, $notfound)));
        if (!empty($candidate)){
            $this->db->where_in('id_pesanan', $candidate)->delete($this->table_paid);
            $paid_deleted = (int)$this->db->affected_rows();
        }
        $this->db->trans_commit();
    } else {
        $this->db->trans_rollback();
        return [
            "ok_count"=>0,
            "confirmed_ids"=>$confirmed,
            "notfound_ids"=>$notfound,
            "errors"=>array_merge($errors, ['tx_rollback']),
            "paid_deleted"=>0,
        ];
    }

    return [
        "ok_count"      => $ok_count,
        "confirmed_ids" => $confirmed,
        "notfound_ids"  => $notfound,
        "errors"        => $errors,
        "paid_deleted"  => $paid_deleted,
    ];
}
private function _paid_exists(int $id_pesanan): bool{
    $r = $this->db->select('id_paid')->from($this->table_paid)->where('id_pesanan',$id_pesanan)->get()->row();
    return !empty($r);
}

private function _paid_payload(object $row, string $source='admin'): array{
    return [
        'id_pesanan'   => (int)$row->id_pesanan,
        'kode_booking' => (string)$row->kode_booking,
        'nama'         => (string)($row->nama ?? ''),
        'no_hp'        => (string)($row->no_hp ?? ''),
        'meja_id'      => (int)$row->meja_id,
        'nama_meja'    => (string)($row->nama_meja ?? null),
        'tanggal'      => (string)$row->tanggal,
        'jam_mulai'    => (string)$row->jam_mulai,
        'jam_selesai'  => (string)$row->jam_selesai,
        'durasi_jam'   => (int)$row->durasi_jam,
        'harga_per_jam'=> (int)$row->harga_per_jam,
        'subtotal'     => (int)$row->subtotal,
        'kode_unik'    => (int)$row->kode_unik,
        'grand_total'  => (int)$row->grand_total,
        'metode_bayar' => (string)($row->metode_bayar ?? null),
        'access_token' => (string)($row->access_token ?? null),
        'paid_at'      => date('Y-m-d H:i:s'),
        'source'       => $source,
    ];
}
private function _delete_paid_by_ids(array $ids): int{
    $ids = array_values(array_unique(array_map('intval',$ids)));
    if (!$ids) return 0;
    $this->db->where_in('id_pesanan', $ids)->delete($this->table_paid);
    return (int)$this->db->affected_rows();
}
/** Cek bentrok slot di meja yang sama (periksa lintas hari juga) */
public function check_slot_conflict(int $meja_id, string $tanggal, string $jam_mulai, int $durasi_jam, ?int $exclude_id=null): array{
    // Hitung rentang baru
    if (strlen($jam_mulai) === 5) $jam_mulai .= ':00';
    $startNew = strtotime($tanggal.' '.$jam_mulai);
    $endNew   = strtotime('+'.$durasi_jam.' hours', $startNew);

    // Cari booking lain +/-1 hari dari tanggal terkait (status selain 'batal')
    $fromDate = date('Y-m-d', strtotime($tanggal.' -1 day'));
    $toDate   = date('Y-m-d', strtotime($tanggal.' +1 day'));

    $this->db->from('pesanan_billiard');
    $this->db->where('meja_id', $meja_id);
    $this->db->where('status <>', 'batal');
    $this->db->where('tanggal >=', $fromDate);
    $this->db->where('tanggal <=', $toDate);
    if ($exclude_id){ $this->db->where('id_pesanan <>', $exclude_id); }
    $rows = $this->db->get()->result();

    $conflict_ids = [];
    foreach($rows as $r){
        $jm = strlen($r->jam_mulai)===5 ? $r->jam_mulai.':00' : $r->jam_mulai;
        $js = strlen($r->jam_selesai)===5 ? $r->jam_selesai.':00' : $r->jam_selesai;

        $s = strtotime($r->tanggal.' '.$jm);
        $e = strtotime($r->tanggal.' '.$js);
        if ($e <= $s){ $e = strtotime('+1 day', $e); } // handle lintas tengah malam

        // Overlap jika: s < endNew && e > startNew
        if ($s < $endNew && $e > $startNew){
            $conflict_ids[] = (int)$r->id_pesanan;
        }
    }

    return [
        'conflict' => !empty($conflict_ids),
        'ids'      => $conflict_ids
    ];
}

/** Simpan perubahan jadwal + updated_at + naikkan edit_count (jika ada) */
public function update_schedule(int $id, string $tanggal, string $jam_mulai, string $jam_selesai): bool{
    $data = [
        'tanggal'     => $tanggal,
        'jam_mulai'   => $jam_mulai,
        'jam_selesai' => $jam_selesai,
        'updated_at'  => date('Y-m-d H:i:s'),
    ];

    // auto increment edit_count jika kolomnya ada
    if ($this->db->field_exists('edit_count','pesanan_billiard')){
        $this->db->set('edit_count', 'COALESCE(edit_count,0)+1', false);
    }
    $this->db->where('id_pesanan',$id)->update('pesanan_billiard',$data);
    return $this->db->affected_rows() > 0;
}

/** Update jadwal di snapshot paid (jika ada barisnya) */
public function update_paid_schedule(int $id_pesanan, string $tanggal, string $jam_mulai, string $jam_selesai): int{
    $this->db->where('id_pesanan', $id_pesanan)
             ->update($this->table_paid, [
                 'tanggal'     => $tanggal,
                 'jam_mulai'   => $jam_mulai,
                 'jam_selesai' => $jam_selesai
             ]);
    return (int)$this->db->affected_rows(); // 0 jika tidak ada barisnya
}


    

}
