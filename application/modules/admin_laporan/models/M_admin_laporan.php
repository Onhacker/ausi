<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_admin_laporan extends CI_Model
{
    // Tabel yang dipakai:
    // POS:            pesanan_paid      (kolom kunci: paid_at, grand_total, paid_method, mode, meja_nama/kode, nama, nomor)
    // BILLIARD:       billiard_paid     (kolom kunci: paid_at, grand_total, metode_bayar, nama_meja, nama, durasi_jam, kode_booking)
    // PENGELUARAN:    pengeluaran       (kolom: tanggal, kategori, metode_bayar, jumlah, nomor_ket/keterangan)

    public function __construct(){ parent::__construct(); }

    /* ================= POS ================= */
 public function sum_pos(array $f): array {
    $pmExpr  = "LOWER(TRIM(COALESCE(paid_method,'')))";
    $SUM_NET = "COALESCE(SUM(
      grand_total - CASE WHEN COALESCE(delivery_fee,0)=1 AND {$pmExpr}='cash' THEN 1 ELSE 0 END
    ),0)";

    // total & count
    $this->db->select("COUNT(*) AS cnt, {$SUM_NET} AS total", false)
             ->from('pesanan_paid');
    $this->_where_date('paid_at', $f);
    if (!empty($f['metode']) && $f['metode'] !== 'all') $this->db->where('LOWER(paid_method)', $f['metode']);
    if (!empty($f['mode'])   && $f['mode']   !== 'all') $this->db->where('LOWER(mode)', $f['mode']);
    if (!empty($f['status']) && $f['status'] !== 'all') $this->db->where('LOWER(status)', $f['status']);
    $row   = $this->db->get()->row();
    $count = (int)($row->cnt ?? 0);
    $total = (int)($row->total ?? 0);

    // by method (pakai SUM_NET yang sama)
    $by_method = [];
    $this->db->select("LOWER(paid_method) AS m, {$SUM_NET} AS t", false)
             ->from('pesanan_paid');
    $this->_where_date('paid_at', $f);
    if (!empty($f['mode'])   && $f['mode']   !== 'all') $this->db->where('LOWER(mode)', $f['mode']);
    if (!empty($f['status']) && $f['status'] !== 'all') $this->db->where('LOWER(status)', $f['status']);
    $this->db->group_by('LOWER(paid_method)');
    foreach ($this->db->get()->result() as $r){
        $by_method[$r->m ?: '-'] = (int)$r->t;
    }

    return ['count'=>$count,'total'=>$total,'by_method'=>$by_method];
}



public function fetch_pos(array $f): array {
    // Metode bayar yang aman (null/empty → '')
    $pmExpr = "LOWER(TRIM(COALESCE(paid_method,'')))";

    $this->db->select("
        pesanan_paid.*,
        (
          grand_total
          - CASE 
              WHEN COALESCE(delivery_fee,0)=1 AND {$pmExpr}='cash'
              THEN 1 ELSE 0
            END
        ) AS grand_total_net,
        (
          CASE 
            WHEN COALESCE(delivery_fee,0)=1 AND {$pmExpr}='cash'
            THEN 0 ELSE COALESCE(delivery_fee,0)
          END
        ) AS delivery_fee_net
    ", false)->from('pesanan_paid');

    $this->_where_date('paid_at', $f);
    if (!empty($f['metode']) && $f['metode'] !== 'all') $this->db->where('LOWER(paid_method)', $f['metode']);
    if (!empty($f['mode'])   && $f['mode']   !== 'all') $this->db->where('LOWER(mode)', $f['mode']);
    if (!empty($f['status']) && $f['status'] !== 'all') $this->db->where('LOWER(status)', $f['status']);

    $this->db->order_by('paid_at','ASC')->order_by('id','ASC');
    return $this->db->get()->result();
}





    /* ================= BILLIARD ================= */
    public function fetch_billiard(array $f): array {
        $this->db->from('billiard_paid');
        $this->_where_date('paid_at', $f);
        if (!empty($f['metode']) && $f['metode'] !== 'all'){
            $this->db->where('LOWER(metode_bayar)', $f['metode']);
        }
        $this->db->order_by('paid_at','ASC')->order_by('id_paid','ASC');
        return $this->db->get()->result();
    }

    public function sum_billiard(array $f): array {
        $this->db->select('COUNT(*) AS cnt, COALESCE(SUM(grand_total),0) AS total', false)
                 ->from('billiard_paid');
        $this->_where_date('paid_at', $f);
        if (!empty($f['metode']) && $f['metode'] !== 'all'){
            $this->db->where('LOWER(metode_bayar)', $f['metode']);
        }
        $row = $this->db->get()->row();
        $count = (int)($row->cnt ?? 0);
        $total = (int)($row->total ?? 0);

        $by_method = [];
        $this->db->select('LOWER(metode_bayar) AS m, COALESCE(SUM(grand_total),0) AS t', false)
                 ->from('billiard_paid');
        $this->_where_date('paid_at', $f)->group_by('LOWER(metode_bayar)');
        if (!empty($f['metode']) && $f['metode'] !== 'all'){
            // tidak usah tambahkan lagi; sudah di group_by semua
        }
        foreach($this->db->get()->result() as $r){
            $by_method[$r->m ?: '-'] = (int)$r->t;
        }

        return ['count'=>$count,'total'=>$total,'by_method'=>$by_method];
    }

    /* ================= PENGELUARAN ================= */
    public function fetch_pengeluaran(array $f): array {
        $this->db->select('id, tanggal, kategori, metode_bayar, jumlah, COALESCE(nomor,"") AS nomor, COALESCE(keterangan,"") AS keterangan', false)
                 ->from('pengeluaran');
        $this->_where_date('tanggal', $f);
        if (!empty($f['metode']) && $f['metode'] !== 'all'){
            $this->db->where('LOWER(metode_bayar)', $f['metode']);
        }
        if (!empty($f['kategori']) && $f['kategori'] !== 'all'){
            $this->db->where('kategori', $f['kategori']);
        }
        $this->db->order_by('tanggal','ASC')->order_by('id','ASC');
        $rows = $this->db->get()->result();

        // gabungkan nomor + keterangan (untuk PDF kolom "Nomor / Ket")
        foreach($rows as $r){
            $r->nomor_ket = trim(($r->nomor ? ('#'.$r->nomor.' ') : '').$r->keterangan);
        }
        return $rows;
    }

    public function sum_pengeluaran(array $f): array {
        $this->db->select('COUNT(*) AS cnt, COALESCE(SUM(jumlah),0) AS total', false)
                 ->from('pengeluaran');
        $this->_where_date('tanggal', $f);
        if (!empty($f['metode']) && $f['metode'] !== 'all'){
            $this->db->where('LOWER(metode_bayar)', $f['metode']);
        }
        if (!empty($f['kategori']) && $f['kategori'] !== 'all'){
            $this->db->where('kategori', $f['kategori']);
        }
        $row = $this->db->get()->row();
        $count = (int)($row->cnt ?? 0);
        $total = (int)($row->total ?? 0);

        $by_kategori = [];
        $this->db->select('kategori, COALESCE(SUM(jumlah),0) AS t', false)
                 ->from('pengeluaran');
        $this->_where_date('tanggal', $f)->group_by('kategori');
        foreach($this->db->get()->result() as $r){
            $by_kategori[(string)$r->kategori] = (int)$r->t;
        }

        return ['count'=>$count,'total'=>$total,'by_kategori'=>$by_kategori];
    }

    public function sum_kurir(array $f): array {
    $ff = $f; $ff['mode'] = 'delivery';
    $rows = $this->fetch_pos($ff);

    $out = ['count'=>0, 'total_fee'=>0, 'by_method'=>[]];
    if (!empty($rows)){
        foreach($rows as $r){
            $cid   = (int)($r->courier_id   ?? 0);
            $cname = trim((string)($r->courier_name ?? ''));
            if ($cid <= 0 && $cname === '') continue;

            $pm    = strtolower(trim((string)($r->paid_method ?? $r->paid_methode ?? '')));
            $feeR  = (int)($r->delivery_fee ?? 0);
            $feeNet= (int)($r->delivery_fee_net ?? (($feeR===1 && ($pm==='transfer'||$pm==='qris')) ? 0 : $feeR));

            $out['count']++;
            $out['total_fee'] += $feeNet;

            $key = $pm ?: '-';
            if (!isset($out['by_method'][$key])) $out['by_method'][$key] = 0;
            $out['by_method'][$key] += $feeNet;
        }
    }
    return $out;
}



    /* ================== common date where ================== */
    private function _where_date(string $field, array $f){
        $from = $f['date_from'] ?? null;
        $to   = $f['date_to']   ?? null;
        if ($from) $this->db->where("$field >=", $from);
        if ($to)   $this->db->where("$field <=", $to);
        return $this->db;
    }

        /**
     * Pendapatan Cafe (POS) per hari
     * grand_total_net:
     *    grand_total - (case ongkir=1 + cash => 1 rupiah)
     */
    public function agg_daily_pos(array $f): array {
        $pmExpr = "LOWER(TRIM(COALESCE(paid_method,'')))";

        $this->db->select("
            DATE(paid_at) AS d,
            COALESCE(SUM(
                grand_total
                - CASE 
                    WHEN COALESCE(delivery_fee,0)=1 AND {$pmExpr}='cash'
                    THEN 1 ELSE 0
                  END
            ),0) AS total
        ", false)->from('pesanan_paid');

        $this->_where_date('paid_at', $f);

        if (!empty($f['metode']) && $f['metode'] !== 'all'){
            $this->db->where('LOWER(paid_method)', $f['metode']);
        }
        if (!empty($f['mode']) && $f['mode'] !== 'all'){
            $this->db->where('LOWER(mode)', $f['mode']);
        }
        if (!empty($f['status']) && $f['status'] !== 'all'){
            $this->db->where('LOWER(status)', $f['status']);
        }

        $this->db->group_by('DATE(paid_at)');
        $rows = $this->db->get()->result();

        $out = [];
        foreach($rows as $r){
            $out[$r->d] = (int)$r->total;
        }
        return $out;
    }

    /**
     * Pendapatan Billiard per hari
     */
    public function agg_daily_billiard(array $f): array {
        $this->db->select("
            DATE(paid_at) AS d,
            COALESCE(SUM(grand_total),0) AS total
        ", false)->from('billiard_paid');

        $this->_where_date('paid_at', $f);

        if (!empty($f['metode']) && $f['metode'] !== 'all'){
            $this->db->where('LOWER(metode_bayar)', $f['metode']);
        }

        $this->db->group_by('DATE(paid_at)');
        $rows = $this->db->get()->result();

        $out = [];
        foreach($rows as $r){
            $out[$r->d] = (int)$r->total;
        }
        return $out;
    }

    /**
     * Pengeluaran per hari
     */
    public function agg_daily_pengeluaran(array $f): array {
        $this->db->select("
            DATE(tanggal) AS d,
            COALESCE(SUM(jumlah),0) AS total
        ", false)->from('pengeluaran');

        $this->_where_date('tanggal', $f);

        if (!empty($f['metode']) && $f['metode'] !== 'all'){
            $this->db->where('LOWER(metode_bayar)', $f['metode']);
        }
        if (!empty($f['kategori']) && $f['kategori'] !== 'all'){
            // kategori mungkin tidak selalu ada di $f, tapi kalau ada bolehlah
            $this->db->where('kategori', $f['kategori']);
        }

        $this->db->group_by('DATE(tanggal)');
        $rows = $this->db->get()->result();

        $out = [];
        foreach($rows as $r){
            $out[$r->d] = (int)$r->total;
        }
        return $out;
    }

}
