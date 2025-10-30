<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_admin_order extends CI_Model
{
    public function __construct(){ parent::__construct(); }

    private function fmt_rp($n){ return 'Rp '.number_format((int)$n,0,',','.'); }
    private function fmt_dt($dt){
        if (!$dt) return '';
        $ts = is_numeric($dt) ? (int)$dt : strtotime($dt);
        if (!$ts) return (string)$dt;
        $bulan = [1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        return sprintf('%s %s %s %s:%s', date('d',$ts), $bulan[(int)date('n',$ts)] ?? date('M',$ts), date('Y',$ts), date('H',$ts), date('i',$ts));
    }
    private function _log_db_err($tag){
        $e = $this->db->error();
        if (!empty($e['code'])) log_message('error', sprintf('[%s] %s => %s - %s', __CLASS__, $tag, $e['code'], $e['message']));
        return $e;
    }

    /**
     * Buat nomor unik untuk order.
     * Contoh: DN-20251016-005527-AB12 (dine-in) atau TK-... (takeaway)
     */
    private function gen_nomor($mode='dinein', $kode=''){
        $prefix = (strtoupper($mode)==='WALKIN' || $mode==='walkin') ? 'TK' : 'DN';
        for ($i=0; $i<6; $i++){
            $cand = sprintf('%s-%s-%s',
                $prefix,
                date('Ymd-His'),
                strtoupper(substr(dechex(mt_rand()),0,4))
            );
            $q = $this->db->select('id')->from('pesanan')->where('nomor',$cand)->limit(1)->get();
            if ($q && !$q->row()) return $cand;
            usleep(50000); // 50ms, kecilkan peluang tabrakan
        }
        // fallback super unik
        return $prefix.'-'.date('Ymd-His').'-'.strtoupper(substr(sha1(uniqid('', true)),0,6));
    }

    /* ---------- MEJA + NAMA AKTIF ---------- */
    public function tables_with_current_name($only_active = true){
    // 1) Ambil daftar meja aktif
    $this->db->select('id, kode, nama, status')->from('meja');
    if ($only_active) $this->db->where('status','aktif');
    $this->db->order_by('kode','asc');
    $qt = $this->db->get();
    $tables = $qt ? $qt->result() : [];

    // 2) Order pending per meja (ambil ID order terbaru)
    $q = $this->db->select('meja_kode, MAX(id) AS oid')
                  ->from('pesanan')->where('mode','dinein')
                  ->where('status','pending')->group_by('meja_kode')->get();
    $mapOid = [];
    if ($q) foreach($q->result() as $r){ $mapOid[(string)$r->meja_kode] = (int)$r->oid; }

    // 3) Ambil nama pelanggan untuk order-order pending tersebut
    $mapName = [];
    if ($mapOid){
        $ids = array_values($mapOid);
        $qq = $this->db->select('id, nama, meja_kode')->from('pesanan')->where_in('id',$ids)->get();
        if ($qq) foreach($qq->result() as $r){ $mapName[(string)$r->meja_kode] = (object)['order_id'=>(int)$r->id,'nama'=>$r->nama ?: '-']; }
    }

    // 4) WALKIN: order pending terbaru
    $walk = null;
    $walkRow = $this->db->select('id,nama')->from('pesanan')
                        ->where('mode','walkin')->where('status','pending')
                        ->order_by('id','desc')->limit(1)->get();
    if ($walkRow && ($rw=$walkRow->row())) $walk = (object)['order_id'=>(int)$rw->id,'nama'=>$rw->nama ?: '-'];

    // 5) Kumpulkan semua order_id untuk hitung last_ver
    $allOrderIds = [];
    if ($walk && !empty($walk->order_id)) $allOrderIds[] = (int)$walk->order_id;
    foreach($mapOid as $k=>$oid){ if ($oid>0) $allOrderIds[] = (int)$oid; }

    // 6) Hitung last_ver = MAX(pesanan_item.id) per order
    $verByOrder = [];
    if ($allOrderIds){
        $vv = $this->db->select('pesanan_id, MAX(id) AS ver')
                       ->from('pesanan_item')
                       ->where_in('pesanan_id', $allOrderIds)
                       ->group_by('pesanan_id')->get();
        if ($vv) foreach($vv->result() as $r){ $verByOrder[(int)$r->pesanan_id] = (int)$r->ver; }
    }

    // 7) Build output (+ last_ver)
    $out = [];
    // WALKIN card
    $out[] = (object)[
        'id'=>0,'kode'=>'WALKIN','nama'=>'Takeaway','status'=>'aktif',
        'current_name'=> $walk ? $walk->nama : 'Kosong',
        'current_order_id'=> $walk ? $walk->order_id : null,
        'is_empty'=> $walk ? false : true,
        'last_ver'=> $walk && isset($verByOrder[$walk->order_id]) ? (int)$verByOrder[$walk->order_id] : 0,
    ];
    foreach($tables as $t){
        $info = $mapName[(string)$t->kode] ?? null;
        $oid  = $info ? (int)$info->order_id : 0;
        $out[] = (object)[
            'id'=>$t->id, 'kode'=>$t->kode, 'nama'=>$t->nama, 'status'=>$t->status,
            'current_name'=> $info ? $info->nama : 'Kosong',
            'current_order_id'=> $info ? $info->order_id : null,
            'is_empty'=> $info ? false : true,
            'last_ver'=> ($oid && isset($verByOrder[$oid])) ? (int)$verByOrder[$oid] : 0,
        ];
    }
    return $out;
}


    /* ---------- ORDER AKTIF PER KODE ---------- */
    public function current_pending_order_by_kode($kode){
        if (strtoupper($kode)==='WALKIN'){
            $q = $this->db->from('pesanan')->where('mode','walkin')->where('status','pending')
                          ->order_by('id','desc')->limit(1)->get();
        } else {
            $q = $this->db->from('pesanan')->where('mode','dinein')->where('meja_kode',$kode)
                          ->where('status','pending')->order_by('id','desc')->limit(1)->get();
        }
        if (!$q) return null;
        $row = $q->row();
        if ($row){ $row->total=(int)$row->total; $row->total_fmt=$this->fmt_rp($row->total); $row->waktu_fmt=$this->fmt_dt($row->created_at); }
        return $row;
    }

    public function create_order_for_kode($kode, $nama=''){
        $mode = (strtoupper($kode)==='WALKIN') ? 'walkin' : 'dinein';

        $data = [
            'status'     => 'pending',
            'nama'       => $nama,
            'created_at' => date('Y-m-d H:i:s'),
            'mode'       => $mode,
            // >>> FIX: set nomor unik agar tidak bentrok uniq_nomor
            'nomor'      => $this->gen_nomor($mode, $kode),
        ];
        if ($mode === 'walkin'){
            $data['meja_kode']=null; $data['meja_nama']=null;
        } else {
            $data['meja_kode']=$kode;
            $mn = $this->db->select('nama')->get_where('meja',['kode'=>$kode])->row();
            $data['meja_nama'] = $mn ? $mn->nama : $kode;
        }

        $ok = $this->db->insert('pesanan', $data);
        if (!$ok){ $this->_log_db_err('create_order'); return 0; }
        return (int)$this->db->insert_id();
    }

    /* ---------- DETAIL + ITEMS ---------- */
    public function order($order_id){
        $q = $this->db->get_where('pesanan', ['id'=>(int)$order_id]);
        if (!$q) return null;
        $row = $q->row();
        if ($row){ $row->total=(int)$row->total; $row->total_fmt=$this->fmt_rp($row->total); $row->waktu_fmt=$this->fmt_dt($row->created_at); }
        return $row;
    }
    public function order_items($order_id){
        $has_added_by   = $this->db->field_exists('added_by','pesanan_item');
        $has_flags_json = $this->db->field_exists('flags_json','pesanan_item');

        $select = 'pi.id, pi.produk_id, p.nama AS nama, pi.qty, pi.harga, pi.subtotal, p.gambar';
        if ($has_added_by)   $select .= ', pi.added_by';
        if ($has_flags_json) $select .= ', pi.flags_json';

        $q = $this->db->select($select)->from('pesanan_item pi')
                      ->join('produk p','p.id=pi.produk_id','left')
                      ->where('pi.pesanan_id',(int)$order_id)
                      ->order_by('pi.id','ASC')->get();
        if (!$q) return [];
        $items = $q->result();
        foreach($items as $it){
            $it->qty       = (int)$it->qty;
            $it->harga     = (int)$it->harga;
            $it->subtotal  = (int)$it->subtotal;
            $it->harga_fmt = $this->fmt_rp($it->harga);
            $it->sub_fmt   = $this->fmt_rp($it->subtotal);
            if (!$has_added_by && $has_flags_json && !empty($it->flags_json)){
                $tmp = json_decode($it->flags_json, true);
                if (json_last_error()===JSON_ERROR_NONE && isset($tmp['added_by'])) $it->flags_added_by = $tmp['added_by'];
            }
        }
        return $items;
    }
    public function order_with_items($order_id){
        $o = $this->order($order_id);
        if (!$o) return [null,[]];
        $items = $this->order_items($order_id);
        return [$o,$items];
    }
    public function sum_order($order_id){
        $q = $this->db->select('SUM(qty*harga) AS n')->from('pesanan_item')->where('pesanan_id',(int)$order_id)->get();
        if (!$q) return 0;
        $row = $q->row(); return (int)($row->n ?? 0);
    }

    /* ---------- ADD/REMOVE ITEM ---------- */
    public function add_item_to_order($order_id, $produk_id, $qty=1, $isAdmin=false){
    // Ambil juga nama produk
    $p = $this->db->select('id,nama,harga,is_active')
                  ->get_where('produk',['id'=>$produk_id])
                  ->row();
    if (!$p || (isset($p->is_active) && (int)$p->is_active!==1)) {
        return [false,'Produk tidak ditemukan / non-aktif'];
    }

    $harga = (int)round((float)$p->harga);

    // Cek ketersediaan kolom opsional di pesanan_item
    $has_added_by    = $this->db->field_exists('added_by','pesanan_item');
    $has_flags_json  = $this->db->field_exists('flags_json','pesanan_item');
    $has_pi_nama     = $this->db->field_exists('nama','pesanan_item'); // << kolom target

    $data = [
        'pesanan_id' => (int)$order_id,
        'produk_id'  => (int)$produk_id,
        'qty'        => (int)$qty,
        'harga'      => $harga,
        'subtotal'   => $harga * (int)$qty,
    ];

    // Isi kolom nama pesanan_item jika ada
    if ($has_pi_nama) {
        $data['nama'] = (string)($p->nama ?? '');
    }

    if ($has_added_by){
        $data['added_by'] = $isAdmin ? 'admin' : 'customer';
    } elseif ($has_flags_json){
        // Bisa sekaligus simpan nama produk sebagai snapshot cadangan
        $data['flags_json'] = json_encode([
            'added_by'   => $isAdmin ? 'admin' : 'customer',
            'produk_nama'=> (string)($p->nama ?? null),
        ]);
    }

    $ok = $this->db->insert('pesanan_item',$data);
    if (!$ok){
        $this->_log_db_err('add_item');
        return [false,'DB error insert item'];
    }

    return [true,'OK'];
}


    public function remove_item_from_order($order_id, $item_id, $isAdmin=false){
        if (!$isAdmin) return [false,'Tidak diizinkan'];

        $has_added_by   = $this->db->field_exists('added_by','pesanan_item');
        $has_flags_json = $this->db->field_exists('flags_json','pesanan_item');

        $this->db->from('pesanan_item')->where('id',(int)$item_id)->where('pesanan_id',(int)$order_id);
        if ($has_added_by){
            $this->db->where('added_by','admin');
        } elseif ($has_flags_json){
            $this->db->where("JSON_EXTRACT(flags_json, '$.added_by') = 'admin'", null, false);
        } else {
            return [false,'Tidak boleh hapus item pengunjung'];
        }
        $q = $this->db->get(); if (!$q) return [false,'DB error cek'];
        if (!$q->row()) return [false,'Item bukan dari admin / tidak ditemukan'];

        $this->db->where('id',(int)$item_id)->delete('pesanan_item');
        if ($this->db->affected_rows()<=0){ $this->_log_db_err('remove_item'); return [false,'Gagal hapus']; }
        return [true,'OK'];
    }

    /* ---------- PAYMENT ---------- */
    public function mark_paid($order_id, $method = 'cash'){
        $order_id = (int)$order_id;
        $q = $this->db->select('id,status,paid_method')->get_where('pesanan',['id'=>$order_id]);
        if (!$q) return ['ok'=>false,'msg'=>'DB error (cek log)'];
        $row = $q->row(); if (!$row) return ['ok'=>false,'msg'=>'Order tidak ditemukan'];

        $fields = $this->db->list_fields('pesanan');
        if ($row->status === 'paid'){
            $upd = ['status'=>'paid'];
            if (in_array('paid_method',$fields) && $row->paid_method !== $method) $upd['paid_method'] = $method;
            if (in_array('paid_at',$fields)) $upd['paid_at'] = date('Y-m-d H:i:s');
            if (count($upd) > 1){ $this->db->where('id',$order_id)->update('pesanan',$upd); }
            return ['ok'=>true,'msg'=>'Order sudah lunas'];
        }

        $data = ['status'=>'paid'];
        if (in_array('paid_method',$fields)) $data['paid_method'] = $method;
        if (in_array('paid_at',$fields))     $data['paid_at']     = date('Y-m-d H:i:s');

        $this->db->where('id',$order_id)->update('pesanan',$data);
        $err = $this->db->error();
        if (!empty($err['code'])) return ['ok'=>false,'msg'=>'DB error update','db_error'=>$err['code'].': '.$err['message']];
        if ($this->db->affected_rows()>0) return ['ok'=>true,'msg'=>'Pembayaran tersimpan'];

        $qq=$this->db->select('status')->get_where('pesanan',['id'=>$order_id]);
        if ($qq && ($cur=$qq->row()) && $cur->status==='paid') return ['ok'=>true,'msg'=>'Order sudah lunas'];
        return ['ok'=>false,'msg'=>'Tidak ada perubahan'];
    }

    /* ---------- KATEGORI & PRODUK ---------- */
    public function get_categories(){
        $out = [];
        if ($this->db->table_exists('kategori_produk')){
            $out = $this->db->select('id, nama')->from('kategori_produk')->where('is_active',1)->order_by('nama','asc')->get()->result();
        } elseif ($this->db->table_exists('produk_kategori')){
            $out = $this->db->select('id, nama')->from('produk_kategori')->order_by('nama','asc')->get()->result();
        } else {
            if ($this->db->field_exists('kategori','produk')){
                $rows = $this->db->distinct()->select('kategori AS nama')->from('produk')
                             ->where('kategori IS NOT NULL', null, false)->order_by('kategori','asc')->get()->result();
                $i=1; foreach($rows as $r){ $out[] = (object)['id'=>$i++, 'nama'=>$r->nama]; }
            }
        }
        return $out;
    }

    public function search_products($q='', $category_id=null, $limit=30){
        $this->db->select('p.id, p.nama, p.harga')->from('produk p')->where('p.is_active',1);
        if ($category_id !== null){
            if ($this->db->field_exists('kategori_id','produk')) $this->db->where('p.kategori_id', $category_id);
        }
        if ($q!=='') $this->db->like('p.nama',$q);
        $this->db->order_by('p.nama','asc')->limit($limit);
        $rows = $this->db->get()->result();
        foreach($rows as $r){ $r->harga = (int)round((float)($r->harga ?? 0)); }
        return $rows;
    }

//     public function tables_with_current_name($only_active = true){
//     // 1) Ambil daftar meja aktif
//     $this->db->select('id, kode, nama, status')->from('meja');
//     if ($only_active) $this->db->where('status','aktif');
//     $this->db->order_by('kode','asc');
//     $qt = $this->db->get();
//     $tables = $qt ? $qt->result() : [];

//     // 2) Order pending per meja (ambil ID order terbaru)
//     $q = $this->db->select('meja_kode, MAX(id) AS oid')
//                   ->from('pesanan')->where('mode','dinein')
//                   ->where('status','pending')->group_by('meja_kode')->get();
//     $mapOid = [];
//     if ($q) foreach($q->result() as $r){ $mapOid[(string)$r->meja_kode] = (int)$r->oid; }

//     // 3) Ambil nama pelanggan untuk order-order pending tersebut
//     $mapName = [];
//     if ($mapOid){
//         $ids = array_values($mapOid);
//         $qq = $this->db->select('id, nama, meja_kode')->from('pesanan')->where_in('id',$ids)->get();
//         if ($qq) foreach($qq->result() as $r){ $mapName[(string)$r->meja_kode] = (object)['order_id'=>(int)$r->id,'nama'=>$r->nama ?: '-']; }
//     }

//     // 4) WALKIN: order pending terbaru
//     $walk = null;
//     $walkRow = $this->db->select('id,nama')->from('pesanan')
//                         ->where('mode','walkin')->where('status','pending')
//                         ->order_by('id','desc')->limit(1)->get();
//     if ($walkRow && ($rw=$walkRow->row())) $walk = (object)['order_id'=>(int)$rw->id,'nama'=>$rw->nama ?: '-'];

//     // 5) Kumpulkan semua order_id untuk hitung last_ver
//     $allOrderIds = [];
//     if ($walk && !empty($walk->order_id)) $allOrderIds[] = (int)$walk->order_id;
//     foreach($mapOid as $k=>$oid){ if ($oid>0) $allOrderIds[] = (int)$oid; }

//     // 6) Hitung last_ver = MAX(pesanan_item.id) per order
//     $verByOrder = [];
//     if ($allOrderIds){
//         $vv = $this->db->select('pesanan_id, MAX(id) AS ver')
//                        ->from('pesanan_item')
//                        ->where_in('pesanan_id', $allOrderIds)
//                        ->group_by('pesanan_id')->get();
//         if ($vv) foreach($vv->result() as $r){ $verByOrder[(int)$r->pesanan_id] = (int)$r->ver; }
//     }

//     // 7) Build output (+ last_ver)
//     $out = [];
//     // WALKIN card
//     $out[] = (object)[
//         'id'=>0,'kode'=>'WALKIN','nama'=>'Takeaway','status'=>'aktif',
//         'current_name'=> $walk ? $walk->nama : 'Kosong',
//         'current_order_id'=> $walk ? $walk->order_id : null,
//         'is_empty'=> $walk ? false : true,
//         'last_ver'=> $walk && isset($verByOrder[$walk->order_id]) ? (int)$verByOrder[$walk->order_id] : 0,
//     ];
//     foreach($tables as $t){
//         $info = $mapName[(string)$t->kode] ?? null;
//         $oid  = $info ? (int)$info->order_id : 0;
//         $out[] = (object)[
//             'id'=>$t->id, 'kode'=>$t->kode, 'nama'=>$t->nama, 'status'=>$t->status,
//             'current_name'=> $info ? $info->nama : 'Kosong',
//             'current_order_id'=> $info ? $info->order_id : null,
//             'is_empty'=> $info ? false : true,
//             'last_ver'=> ($oid && isset($verByOrder[$oid])) ? (int)$verByOrder[$oid] : 0,
//         ];
//     }
//     return $out;
// }

}
