<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_pos extends Admin_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('M_admin_pos','dm');
        // $this->load->helper(['url','text','security']);
        cek_session_akses(get_class($this), $this->session->userdata('admin_session'));
    }

    private function purge_public_caches(){
        $this->load->driver('cache', ['adapter' => 'file']);
        $this->cache->save('pos_ver', time(), 365*24*3600);
        $this->output->set_header('X-Cache-Purged: pos');
    }

    public function index(){
        $data["controller"] = get_class($this);
        $data["title"]      = "Transaksi";
        $data["subtitle"]   = $this->om->engine_nama_menu(get_class($this));
        $data["content"]    = $this->load->view($data["controller"]."_view",$data,true);
        $this->render($data);
    }

    public function set_ongkir(){
    $id  = (int)$this->input->post('id');
    $fee = (string)$this->input->post('fee'); // "20.000" / "20000" / "20,000"

    if ($id <= 0) {
        return $this->output->set_content_type('application/json')
            ->set_output(json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"ID tidak valid"]));
    }

    // Normalisasi fee -> integer
    $fee = preg_replace('/[^\d]/', '', $fee);
    $fee = (int)$fee; if ($fee < 0) $fee = 0;

    $row = $this->db->get_where('pesanan', ['id'=>$id])->row();
    if (!$row) {
        return $this->output->set_content_type('application/json')
            ->set_output(json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Order tidak ditemukan"]));
    }

    // Hanya delivery
    $isDelivery = (strtolower($row->mode ?? '') === 'delivery');
    if (!$isDelivery){
        return $this->output->set_content_type('application/json')
            ->set_output(json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Order ini bukan delivery"]));
    }

    // ===== Hitung ulang grand_total (kode unik untuk semua metode ≠ cash) =====
    $subtotal    = (int)($row->total ?? 0);
    $deliveryFee = (int)$fee;
    $baseTotal   = $subtotal + $deliveryFee;

    $method = strtolower(trim((string)($row->paid_method ?? '')));
    $isCash = ($method === 'cash');

    if ($isCash){
        $kodeUnik = 0;
        $grand    = $baseTotal;
    } else {
        $kodeUnik = (int)($row->kode_unik ?? 0);
        if ($kodeUnik < 1 || $kodeUnik > 499){
            try { $kodeUnik = random_int(1, 499); } catch (\Throwable $e) { $kodeUnik = mt_rand(1, 499); }
        }
        $grand = $baseTotal + $kodeUnik;
    }

    $ok = $this->db->where('id', $id)->update('pesanan', [
        'delivery_fee' => $deliveryFee,
        'kode_unik'    => $kodeUnik,
        'grand_total'  => $grand,
        'updated_at'   => date('Y-m-d H:i:s'),
    ]);

    if (!$ok){
        return $this->output->set_content_type('application/json')
            ->set_output(json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Tidak bisa memperbarui ongkir"]));
    }

    $this->purge_public_caches();

    // Ambil ulang order + ITEMS setelah update
    $ord = $this->db->get_where('pesanan', ['id'=>$id])->row();

    $rows = $this->db->select('
                pi.id,
                pi.produk_id,
                COALESCE(pi.nama, p.nama) AS nama,
                pi.qty,
                pi.harga,
                (pi.qty * pi.harga) AS subtotal,
                pi.tambahan,
                p.kategori_id
            ')
            ->from('pesanan_item pi')
            ->join('produk p', 'p.id = pi.produk_id', 'left')
            ->where('pi.pesanan_id', $id)
            ->order_by('pi.id','ASC')->get()->result();

    $items_out = [];
    foreach ($rows as $r){
        $qty  = (int)$r->qty;
        $hrg  = (int)$r->harga;
        $sub  = (int)$r->subtotal;
        $items_out[] = [
            'id'              => (int)$r->id,
            'produk_id'       => (int)$r->produk_id,
            'nama'            => (string)($r->nama ?? '-'),
            'qty'             => $qty,
            'harga'           => $hrg,
            'subtotal'        => $sub,
            'tambahan'        => (int)($r->tambahan ?? 0),
            'kategori_id'     => isset($r->kategori_id) ? (int)$r->kategori_id : null,
            'display_harga'   => 'Rp '.number_format($hrg,0,',','.'),
            'display_subtotal'=> 'Rp '.number_format($sub,0,',','.')
        ];
    }

    // ========= Link struk & halaman terkait =========
    $links = [
        'customer_success' => site_url('produk/order_success/'.$ord->nomor),
        'customer_receipt' => site_url('produk/receipt/'.$id),
        'receipt_pdf_58'   => site_url('produk/receipt_pdf/'.$id.'?w=58'),
        'receipt_pdf_80'   => site_url('produk/receipt_pdf/'.$id.'?w=80'),
        'admin_print_58'   => site_url('admin_pos/print_struk_termal/'.$id.'?paper=58'),
        'admin_print_80'   => site_url('admin_pos/print_struk_termal/'.$id.'?paper=80'),
    ];
    $pm = strtolower((string)($ord->paid_method ?? ''));
    if ($pm === 'qris'){
        $links['pay_page'] = site_url('produk/order_success/'.$id);
    } elseif ($pm === 'transfer'){
        $links['pay_page'] = site_url('produk/order_success/'.$id);
    } else {
        $links['pay_page'] = null;
    }

    // ========= Compose pesan WA LENGKAP (dengan list item) =========
    $hp    = trim((string)($ord->customer_phone ?? ''));
    $namaC = trim((string)($ord->nama ?? ''));
    $nomor = $ord->nomor ?: $ord->id;

    // Baris item: "- Nama xQty = Rp 10.000"
    $lines = [];
    foreach ($items_out as $it){
        $lines[] = '- '.$it['nama'].' x'.$it['qty'].' = '.$it['display_subtotal'];
    }
    $items_block = implode("\n", $lines);
    $this->db->select("*")->from("identitas");
    $this->db->where("id_identitas", "1");
    $ident = $this->db->get()->row();

    $msg  = "Halo".($namaC ? " {$namaC}" : "").",\n";
    $msg .= "Ongkir pesanan #{$nomor} dari ".$ident->nama_website." telah diperbarui.\n\n";

    $msg .= "Rincian pesanan:\n";
    $msg .= $items_block . "\n\n";

    $msg .= "Subtotal : Rp ".number_format((int)$ord->total,0,',','.')."\n";
    $msg .= "Ongkir   : Rp ".number_format((int)$ord->delivery_fee,0,',','.')."\n";
    if ((int)$ord->kode_unik > 0){
        $msg .= "Kode Unik: Rp ".number_format((int)$ord->kode_unik,0,',','.')."\n";
    }
    $msg .= "TOTAL BAYAR: *Rp ".number_format((int)$ord->grand_total,0,',','.')."* \n\n";

    // $msg .= "Cek struk: ".$links['customer_receipt']."\n";

    /* ——— CATATAN TUNAI: letakkan persis di atas "Metode Pembayaran" ——— */
    $msg .= "_Catatan: Jika memilih pembayaran *tunai*, kode unik *tidak* ditagihkan._\n";

    /* Metode Pembayaran */
    $msg .= "Metode Pembayaran: ".$links['customer_success']."\n";

    /* ——— Catatan penutup ——— */
    $msg .= "\nSimpan nomor ini agar link bisa diklik.\n";
    $msg .= "Pesan ini dikirim otomatis, mohon *jangan balas* pesan ini.\n";


    $wa_sent = false;
    if ($hp !== ''){
        // fungsi kirim WA milikmu
        $wa_sent = send_wa_single($hp, $msg);
    }

    // ========= Payload JSON lengkap untuk UI =========
    $subtotal_out  = (int)($ord->total ?? 0);
    $ongkir_out    = (int)($ord->delivery_fee ?? 0);
    $kode_unik_out = (int)($ord->kode_unik ?? 0);
    $grand_out     = (int)($ord->grand_total ?? ($subtotal_out + $ongkir_out + $kode_unik_out));

    $detail = [
        'id'            => (int)$ord->id,
        'nomor'         => (string)($ord->nomor ?: $ord->id),
        'mode'          => (string)($ord->mode ?? ''),
        'status'        => (string)($ord->status ?? ''),
        'paid_method'   => (string)($ord->paid_method ?? ''),
        'created_at'    => (string)($ord->created_at ?? ''),
        'nama'          => (string)($ord->nama ?? ''),
        'customer_phone'=> (string)($ord->customer_phone ?? ''),
        'alamat_kirim'  => (string)($ord->alamat_kirim ?? ''),
        'subtotal'      => $subtotal_out,
        'delivery_fee'  => $ongkir_out,
        'kode_unik'     => $kode_unik_out,
        'grand_total'   => $grand_out,
        'display'       => [
            'subtotal'     => 'Rp '.number_format($subtotal_out,0,',','.'),
            'delivery_fee' => 'Rp '.number_format($ongkir_out,0,',','.'),
            'kode_unik'    => 'Rp '.number_format($kode_unik_out,0,',','.'),
            'grand_total'  => 'Rp '.number_format($grand_out,0,',','.'),
        ],
        'items'         => $items_out,
        'links'         => $links,
        'wa_preview'    => $msg, // opsional: untuk debug/preview di UI
    ];

    return $this->output->set_content_type('application/json')
        ->set_output(json_encode([
            "success"  => true,
            "title"    => "Berhasil",
            "pesan"    => "Ongkir diperbarui".($wa_sent ? " & WA terkirim." : "."),
            "wa_sent"  => (bool)$wa_sent,
            "order"    => $detail
        ]));
}




    /** DataTables server-side */
    // application/controllers/Admin_pos.php
public function get_dataa(){
    try{
        // ===== filter status dari POST =====
        $status = $this->input->post('status', true);
        if ($status === '' || $status === null) { $status = 'all'; }

        $this->dm->set_max_rows(100);
        $this->dm->set_kasir_scope(false);
        $this->dm->set_status_filter($status);

        // ===== filter kitchen/bar hanya order yg punya item kategori tertentu
        $uname = strtolower((string)$this->session->userdata('admin_username'));
        if ($uname === 'kitchen'){
            $this->dm->set_item_category_filter(1); // makanan
        } elseif ($uname === 'bar'){
            $this->dm->set_item_category_filter(2); // minuman
        } else {
            $this->dm->set_item_category_filter(null); // kasir/admin: semua
        }

        // flag role
        $isKitchen = ($uname === 'kitchen');
        $isBar     = ($uname === 'bar');


        // === Formatter METODE (hanya: Cash, QRIS, Transfer) ===
        $fmt_method = function($raw) {
            $rawStr = (string)$raw;
            $s = strtolower(trim($rawStr));
            if ($s === '' || $s === '-' || $s === 'unknown') {
                return '<span class="text-muted">—</span>';
            }

            // pecah token (dukung json array / string gabungan)
            $tokens = [];
            if ($s !== '' && ($s[0] === '[' || $s[0] === '{')) {
                $tmp = json_decode($rawStr, true);
                if (is_array($tmp)) {
                    foreach ($tmp as $v) { $tokens[] = strtolower(trim((string)$v)); }
                }
            }
            if (!$tokens) {
                $tokens = preg_split('/[\s,\/\+\|]+/', $s, -1, PREG_SPLIT_NO_EMPTY);
            }

            // normalisasi -> flag 3 jenis saja
            $has = ['cash'=>false, 'qris'=>false, 'transfer'=>false];
            foreach ($tokens as $t) {
                if (preg_match('/^(cash|tunai)$/', $t))                           $has['cash'] = true;
                elseif (preg_match('/^(qris|qr|scan)$/', $t))                      $has['qris'] = true;
                elseif (preg_match('/^(transfer|tf|bank|bca|bri|bni|mandiri)$/', $t)) $has['transfer'] = true;
            }

            // builder chip
            $chip = function($icon,$label,$cls){
                return '<span class="badge badge-pill '.$cls.' mr-1 mb-1">'
                     .   '<i class="mdi '.$icon.' mr-1"></i>'.$label
                     . '</span>';
            };

            $out = '';
            if ($has['cash'])     $out .= $chip('mdi-cash',           'Tunai',    'badge-success');
            if ($has['qris'])     $out .= $chip('mdi-qrcode-scan',    'QRIS',     'badge-info');
            if ($has['transfer']) $out .= $chip('mdi-bank-transfer',  'Transfer', 'badge-secondary');

            if ($out === '') return '<span class="text-muted">—</span>'; // selain 3, sembunyikan
            return '<div class="d-flex flex-wrap" style="gap:.25rem .25rem" title="'.htmlspecialchars($rawStr, ENT_QUOTES, 'UTF-8').'">'.$out.'</div>';
        };


        $list = $this->dm->get_data();
        $data = [];

        foreach ($list as $r) {
            // === Mode badge ===
            $mode_raw = strtolower(trim($r->mode ?: 'walking'));
            switch ($mode_raw) {
                case 'dinein':
                case 'dine-in':  $mode_label='Makan di Tempat';  $mode_badge='badge-info';    break;
                case 'delivery': $mode_label='Antar/ Kirim'; $mode_badge='badge-warning'; break;
                case 'walking':
                case 'walkin':
                case 'walk-in':
                default:         $mode_label='Bungkus';  $mode_badge='badge-primary'; break;
            }
            // === Info kurir (hanya untuk delivery) ===
            $kurirInfoHtml = '';
            if ($mode_raw === 'delivery') {
                $courier_id   = (int)($r->courier_id ?? 0);
                $courier_name = trim((string)($r->courier_name ?? ''));

                if ($courier_id > 0 && $courier_name !== '') {
                    $kurirInfoHtml = '<div class="small text-success mt-1">'
                                   . '<i class="mdi mdi-account-check-outline mr-1"></i>'
                                   . 'Kurir: ' . htmlspecialchars($courier_name, ENT_QUOTES, 'UTF-8')
                                   . '</div>';
                } else {
                    $kurirInfoHtml = '<div class="small text-danger mt-1">'
                                   . '<i class="mdi mdi-account-alert-outline mr-1"></i>'
                                   . 'Kurir belum ditugaskan'
                                   . '</div>';
                }
            }

            // Meja + nama
            $meja   = $r->meja_nama ?: ($r->meja_kode ?: '—');
            $nama   = trim((string)($r->nama ?? ''));
            $meja_html = htmlspecialchars($meja, ENT_QUOTES, 'UTF-8');
            if ($nama !== ''){
                $meja_html .= '<div class="text-muted small">'.htmlspecialchars($nama,ENT_QUOTES,'UTF-8').'</div>';
            }

            // Waktu
            $waktu_dt  = $r->created_at ?: date('Y-m-d H:i:s');
            $createdTs = strtotime($waktu_dt) ?: time();

            // Jumlah
            $jumlah = (int)($r->grand_total ?? $r->total ?? 0);

            // ======== STATUS (label & badge) sesuai role ========
            if ($isKitchen) {
                // Kitchen → pakai status_pesanan_kitchen (1/2)
                $sp = (int)($r->status_pesanan_kitchen ?? 1);
                if ($sp === 2) { $status_label = 'Selesai';  $badge = 'success'; }
                else           { $status_label = 'Proses'; $badge = 'warning'; }
            } elseif ($isBar) {
                // Bar → pakai status_pesanan_bar (1/2)
                $sp = (int)($r->status_pesanan_bar ?? 1);
                if ($sp === 2) { $status_label = 'Selesai';  $badge = 'success'; }
                else           { $status_label = 'Proses'; $badge = 'warning'; }
            } else {
                // Kasir / Admin → pakai status pembayaran
                $status_raw = strtolower($r->status ?: 'pending');
                $badge = 'secondary';
                if     ($status_raw === 'paid')       $badge = 'success';
                elseif ($status_raw === 'canceled')   $badge = 'dark';
                elseif ($status_raw === 'verifikasi') $badge = 'warning';
                elseif ($status_raw === 'failed')     $badge = 'danger';
                elseif ($status_raw === 'sent')       $badge = 'info';

                $status_label = $status_raw;
                if     ($status_raw === 'pending')    $status_label = 'menunggu pembayaran';
                elseif ($status_raw === 'verifikasi') $status_label = 'verifikasi kasir';
                elseif ($status_raw === 'paid')       $status_label = 'lunas';
                elseif ($status_raw === 'sent')       $status_label = 'terkirim';
            }

                $method = $r->paid_method ?: '-';
                $createdTs = strtotime($r->created_at ?: 'now') ?: time();

                $isClosed = false;
                if ($isKitchen) {
                    $isClosed = ((int)$r->status_pesanan_kitchen === 2);
                } elseif ($isBar) {
                    $isClosed = ((int)$r->status_pesanan_bar === 2);
                } else {
                    $status_raw = strtolower((string)($r->status ?? ''));
                    $isClosed = ((int)($r->tutup_transaksi ?? 0) === 1)
                             || in_array($status_raw, ['paid','canceled'], true);
                }

                if ($isClosed) {
                    // Titik selesai terbaik
                    $endTs = null;
                    if ($isKitchen && !empty($r->kitchen_done_at))      $endTs = strtotime($r->kitchen_done_at);
                    elseif ($isBar   && !empty($r->bar_done_at))        $endTs = strtotime($r->bar_done_at);
                    elseif (!empty($r->paid_at))                        $endTs = strtotime($r->paid_at);
                    elseif (!empty($r->updated_at))                     $endTs = strtotime($r->updated_at);
                    else                                                $endTs = $createdTs;

                    $dur = max(0, (int)$endTs - (int)$createdTs);
                    $lamaHtml = '<span class="elapsed stopped text-muted" data-dur="'.$dur.'">—</span>';
                } else {
                    // Masih berjalan → kirim data-start (detik)
                    $lamaHtml = '<span class="elapsed live text-primary" data-start="'.$createdTs.'">—</span>';
                }

                $row['lama'] = $lamaHtml;


            // ===== Kolom "Pesanan" (khusus kitchen/bar) =====
            $pesananHtml = '';
            if ($isKitchen || $isBar) {
                $catId = $isKitchen ? 1 : 2; // 1 = makanan (kitchen), 2 = minuman (bar)
                $list  = $this->dm->compact_items_for_order((int)$r->id, $catId);

                if ($list) {
                    $chips = [];
                    foreach ($list as $it) {
                        $qty  = (int)$it->qty;
                        $name = htmlspecialchars($it->nama, ENT_QUOTES, 'UTF-8');
                        // badge chip: "2× Nasi"
                        $chips[] = '<span class="badge badge-light border font-weight-bold mr-1 mb-1">'
                                 . $qty . '× ' . $name
                                 . '</span>';
                    }
                    // wrap agar rapi dan bisa multi-baris
                    $pesananHtml = '<div class="d-flex flex-wrap" style="gap:.25rem .25rem;">'
                                 . implode('', $chips)
                                 . '</div>';
                } else {
                    $pesananHtml = '<span class="text-muted">—</span>';
                }

                // tambahkan ke kolom row
                
            }
            $actionsHtml = '';
            if (!$isKitchen && !$isBar) {
                $idInt = (int)$r->id;
                $btnPaid   = '<button type="button" class="btn btn-sm btn-primary mr-1" onclick="mark_paid_one('.$idInt.')"><i class="fe-check-circle"></i></button>';
                $btnCancel = '<button type="button" class="btn btn-sm btn-secondary mr-1" onclick="mark_canceled_one('.$idInt.')"><i class="fe-x-circle"></i></button>';

                // tombol Hapus hanya untuk admin_username = admin
                $unameLower = strtolower((string)$this->session->userdata('admin_username'));
                $btnDelete  = ($unameLower === 'admin')
                    ? '<button type="button" class="btn btn-sm btn-danger" onclick="hapus_data_one('.$idInt.')"><i class="fa fa-trash"></i></button>'
                    : '';

                $actionsHtml = '<div class="btn-group btn-group-sm" role="group">'.$btnPaid.$btnCancel.$btnDelete.'</div>';
            }

            $row = [];

            // WAJIB: simpan id untuk click-row
            $row['id'] = (int)$r->id;  // <-- ini penting, jangan dihapus

            // 1. no
            $row['no']    = '';

            // 2. mode
            $row['mode']  =
    '<span class="d-none meta-rowid" data-rowid="'.(int)$r->id.'"></span>'.



    // lalu konten mode yg lama:
            '<div class="d-inline-block text-left">'
            .   '<span class="badge badge-pill '.$mode_badge.'">'
            .     htmlspecialchars($mode_label, ENT_QUOTES, 'UTF-8')
            .   '</span>'
            .   $kurirInfoHtml
            . '</div>';

            // 3. meja
            $row['meja']  = $meja_html;

            // 4. pesanan (kalau kitchen/bar). kalau bukan kitchen/bar tetap boleh kirim '' biar key-nya konsisten aman.
            if ($isKitchen || $isBar) {
                $row['pesanan'] = $pesananHtml;
            } else {
                // supaya DataTables gak error pas kolom "pesanan" ga ada di kasir? 
                // nggak wajib kalau kolom "pesanan" memang nggak diminta di mode kasir.
            }

            // 5. waktu
            $row['waktu'] = htmlspecialchars(date('d-m-Y H:i', $createdTs), ENT_QUOTES, 'UTF-8');

            // 6. lama
            $row['lama']  = $lamaHtml;

            // 7. jumlah
            $row['jumlah'] = ($isKitchen || $isBar) ? '' : ('Rp '.number_format($jumlah,0,',','.'));

            // 8. status
            $row['status'] =
                '<span class="badge badge-pill badge-'.$badge.'">'
              . htmlspecialchars($status_label,ENT_QUOTES,'UTF-8')
              . '</span>';

            // 9. metode
            // $row['metode'] = ($isKitchen || $isBar) ? '' : htmlspecialchars($method, ENT_QUOTES, 'UTF-8');
            // 9. metode
            if ($isKitchen || $isBar) {
                $row['metode'] = '';
            } else {
                $row['metode'] = $fmt_method($r->paid_method ?? '');
            }


            // 10. aksi (hanya kasir/admin)
            if (!$isKitchen && !$isBar) {
                $row['aksi'] = $actionsHtml;
            }

            $data[] = $row;

        }

        $out = [
            "draw"            => (int)$this->input->post('draw'),
            "recordsTotal"    => $this->dm->count_all(),
            "recordsFiltered" => $this->dm->count_filtered(),
            "data"            => $data,
        ];
        $out['hide_price_payment'] = (bool)($isKitchen || $isBar);
        $out['server_now'] = time(); // <— TAMBAH BARIS INI

        return $this->output->set_content_type('application/json')->set_output(json_encode($out));
    } catch(\Throwable $e){
        return $this->output->set_content_type('application/json')
            ->set_output(json_encode([
                "draw" => (int)$this->input->post('draw'),
                "recordsTotal"=>0,"recordsFiltered"=>0,"data"=>[],
                "error"=>"Server error: ".$e->getMessage()
            ]));
    }
}



public function set_status_pesanan(){
    $id  = (int)$this->input->post('id');
    $val = (int)$this->input->post('status_pesanan'); // 1|2
    if ($id<=0 || !in_array($val,[1,2],true)){
        echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Parameter tidak valid"]); return;
    }
    $ok = $this->db->where('id',$id)->update('pesanan', [
        'status_pesanan' => $val,
        'updated_at'     => date('Y-m-d H:i:s'),
    ]);
    echo json_encode([
        "success"=>$ok,
        "title"=>$ok?"Berhasil":"Gagal",
        "pesan"=>$ok?"Status pesanan diperbarui":"Tidak bisa memperbarui status"
    ]);
}

/** Kembalikan 1 jika kitchen, 2 jika bar, null selain itu */
/** 1 jika kitchen, 2 jika bar, null untuk kasir/yang lain */
private function _active_category_for_user(): ?int {
    $uname = strtolower(trim((string)$this->session->userdata('admin_username')));
    if ($uname === 'kitchen') return 1;   // makanan
    if ($uname === 'bar')     return 2;   // minuman
    return null;                          // kasir / default
}


    /** Detail order → modal (HTML partial) */
  public function detail($id = null){
    $id  = (int)$id;
    $cat = $this->_active_category_for_user(); // 1 kitchen, 2 bar, null kasir

    if ($cat === null) {
        // === KASIR: tampil penuh + daftar kurir untuk tombol "Tugaskan Kurir"
        $bundle = $this->dm->get_order_with_items($id);
        if (!$bundle){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]);
            return;
        }

        // siapkan daftar kurir (boleh filter only available)
        $order = $bundle['order'] ?? null;
        $is_delivery = (strtolower($order->mode ?? '-') === 'delivery');

        // hanya masukkan kurir jika mode delivery; aman juga kalau mau selalu dikirim
        $bundle['kurirs'] = $is_delivery ? $this->_list_kurirs(/* only_available */ false) : [];

        $html  = $this->load->view('partials/admin_pos_detail_partial', $bundle, true);
        $title = "Detail Order #".($order->nomor ?? $id);

    } else {
        // === KITCHEN/BAR: filter kategori & partial tanpa harga (tanpa kurir)
        $bundle = $this->dm->get_order_with_items_by_cat($id, $cat);
        if (!$bundle){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]);
            return;
        }
        $bundle['active_cat'] = $cat;

        // tidak perlu tabel kurir di kitchen/bar
        $bundle['kurirs'] = [];

        $html  = $this->load->view('partials/admin_pos_detail_kitchenbar_partial', $bundle, true);
        $title = "Detail ".($cat===1 ? 'Kitchen' : 'Bar')." #".($bundle['order']->nomor ?? $id);
    }

    echo json_encode(["success"=>true, "html"=>$html, "title"=>$title]);
}

/**
 * Ambil daftar kurir untuk ditampilkan di modal/tabel.
 * - Urutkan: available dulu, beban tugas (on_trip_count) kecil dulu, lalu nama.
 * - Sesuaikan nama tabel/kolom & daftar status aktif sesuai skema kamu.
 */
private function _list_kurirs($only_available = false){
    // contoh join ringan untuk hitung tugas aktif dari tabel orders
    $this->db->select("
        k.id, k.nama, k.phone, k.vehicle, k.plate, k.status,
        COALESCE(SUM(CASE WHEN o.id IS NOT NULL THEN 1 ELSE 0 END), 0) AS on_trip_count
    ", false);
    $this->db->from('kurir k');
    // sesuaikan status order yang dianggap 'sedang jalan'
    $this->db->join(
        'pesanan o',
        "o.courier_id = k.id AND o.status_pesanan IN ('1','2')",
        'left'
    );

    if ($only_available) {
        // kalau mau hanya kurir siap/aktif
        $this->db->where_in('k.status', ['available','ontask']);
    }

    $this->db->group_by('k.id');
    // sort: available dulu, lalu yang on_trip_count kecil, lalu nama
    $this->db->order_by("CASE WHEN k.status='available' THEN 0 WHEN k.status='ontask' THEN 1 ELSE 2 END", "ASC", false);
    $this->db->order_by('on_trip_count', 'ASC');
    $this->db->order_by('k.nama', 'ASC');

    return $this->db->get()->result();
}



    /** Tambah order (kasir) → pending lalu redirect ke Produk::pay_* */
    public function create_order(){
        $mode       = $this->input->post('mode', true) ?: 'walkin';
        $meja_kode  = $this->input->post('meja_kode', true);
        $meja_nama  = $this->input->post('meja_nama', true);
        $nama       = $this->input->post('nama', true) ?: 'Customer';
        $catatan    = $this->input->post('catatan', true);
        $pay_method = $this->input->post('pay_method', true) ?: 'cash';
        $items_json = $this->input->post('items');
        $items      = json_decode($items_json, true);

        if (!is_array($items) || !count($items)){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Item belum dipilih"]); return;
        }

        $res = $this->dm->tx_create_order_pending($mode, $meja_kode, $meja_nama, $nama, $catatan, $pay_method, $items);
        if (!$res['success']){ echo json_encode($res); return; }

        $this->purge_public_caches();

        $order_id = (int)$res['order_id'];
        switch ($pay_method){
            case 'qris':     $redirect = site_url('produk/pay_qris/'.$order_id); break;
            case 'transfer': $redirect = site_url('produk/pay_transfer/'.$order_id); break;
            default:         $redirect = site_url('produk/pay_cash/'.$order_id);
        }

        echo json_encode([
            "success"=>true,
            "title"=>"Berhasil",
            "pesan"=>"Order berhasil dibuat",
            "order_id"=>$order_id,
            "redirect"=>$redirect
        ]);
    }

    /** Tandai paid (bulk) */
  /** Tandai paid (bulk) */
public function mark_paid(){
    $ids = $this->input->post('id');
    if (!is_array($ids) || !count($ids)){
        echo json_encode([
            "success"=>false,
            "title"=>"Gagal",
            "pesan"=>"Tidak ada data dipilih"
        ]);
        return;
    }

    $res = $this->dm->bulk_mark_paid($ids);

    if (!empty($res['ok_count'])) {
        $this->purge_public_caches();
    }

    // Kumpulkan kelompok ID dari hasil model (pastikan array)
    $blocked   = !empty($res['blocked_ids'])  ? (array)$res['blocked_ids']  : [];
    $already   = !empty($res['already_ids'])  ? (array)$res['already_ids']  : [];
    $notfound  = !empty($res['notfound_ids']) ? (array)$res['notfound_ids'] : [];
    $errors    = !empty($res['errors'])       ? (array)$res['errors']       : [];

    // Hitung ok_ids = input - (blocked ∪ already ∪ notfound ∪ errors)
    $bad    = array_unique(array_merge($blocked, $already, $notfound, $errors));
    $ok_ids = array_values(array_diff(array_map('intval',$ids), array_map('intval',$bad)));

    // >>> HENTIKAN DURASI DI KASIR untuk yang benar-benar paid
    if (!empty($ok_ids)) {
        $this->_stop_kasir_timer($ok_ids);
    }

    // >>> KIRIM WA KE CUSTOMER (pembayaran diterima)
    if (!empty($ok_ids)) {
        $this->_wa_paid_notice($ok_ids);
    }

    // Rangkai pesan yang jelas untuk alert di UI
    $msgs = [];
    if (!empty($res['ok_count']))     $msgs[] = $res['ok_count']." order ditandai lunas.";
    if (!empty($res['blocked_ids']))  $msgs[] = "Ditolak (metode bayar belum di-set): #".implode(', #', $res['blocked_ids']);
    if (!empty($res['already_ids']))  $msgs[] = "Diabaikan (sudah paid/canceled): #".implode(', #', $res['already_ids']);
    if (!empty($res['notfound_ids'])) $msgs[] = "Tidak ditemukan: #".implode(', #', $res['notfound_ids']);

    $ok = !empty($res['ok_count']) && empty($res['blocked_ids']) && empty($res['errors']);

    echo json_encode([
        "success" => $ok,
        "title"   => $ok ? "Berhasil" : "Sebagian/Gagal",
        "pesan"   => implode(' ', $msgs) ?: 'Tidak ada yang diproses.'
    ]);
}
/**
 * Kirim WA konfirmasi bahwa pembayaran sudah diterima.
 * Dipanggil hanya untuk ID order yang benar-benar berhasil jadi "paid".
 *
 * @param array $paid_ids
 */
private function _wa_paid_notice(array $paid_ids){
    if (empty($paid_ids)) return;

    // Ambil info toko sekali saja
    $ident = $this->db->get('identitas')->row();
    $toko  = trim((string)($ident->nama_website ?? $ident->nama ?? 'AUSI BILLIARD & CAFE'));
    if ($toko === '') { $toko = 'AUSI BILLIARD & CAFE'; }

    // Ambil data order yang baru dilunasi (id, nomor, total, metode bayar, dll)
    $orders = $this->dm->get_orders_for_wa($paid_ids);
    if (!$orders) return;

    foreach ($orders as $o){
        // nomor hp customer dari pesanan
        $hpRaw = trim((string)($o->customer_phone ?? ''));
        if ($hpRaw === '') continue; // ga ada nomor -> skip

        // normalisasi ke format internasional (62xxx)
        $msisdn = $this->_msisdn($hpRaw);
        if ($msisdn === '') continue;

        // data basic pesanan
        $kode    = ($o->nomor !== '' ? $o->nomor : $o->id);
        $total   = (int)($o->grand_total ?? 0);
        $metode  = (string)($o->paid_method ?? '-');
        $waktu   = !empty($o->created_at)
            ? date('d/m/Y H:i', strtotime($o->created_at))
            : date('d/m/Y H:i');

        // susun pesan WA
            $kodeTampil = ($o->nomor !== '' ? $o->nomor : $o->id);
            $linkStruk  = site_url('produk/receipt/'.$kodeTampil);
            $namaSapaan = trim($o->nama ?: "kak");

            $msg  = "Halo {$namaSapaan},\n\n";
            $msg .= "✨ *PEMBAYARAN DITERIMA* ✅\n";
            $msg .= "Pesanan #{$kodeTampil} pada {$waktu}\n";
            $msg .= "──────────────────\n";
            $msg .= "Total Bayar : *".$this->_idr($total)."*\n";
            $msg .= "Metode      : {$metode}\n";
            $msg .= "──────────────────\n";
            $msg .= "Pembayaran telah kami terima 👍\n\n";

            $msg .= "Struk / Detail:\n{$linkStruk}\n\n";

            $msg .= "klo mau struk fisik, langsung ke kasir kak.";
            $msg .= "Terima kasih sudah bertransaksi di {$toko} 🙌\n\n";

            $msg .= "Simpan kontak ini agar link bisa diklik 📲\n";
            $msg .= "Pesan ini dikirim otomatis oleh sistem {$toko}. Mohon jangan balas pesan ini.\n";


        // kirim via gateway WA kamu
        $this->_wa_try($msisdn, $msg);
    }
}




    /** Batalkan (bulk) */
    public function mark_canceled(){
        $ids = $this->input->post('id');
        if (!is_array($ids) || !count($ids)){ echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Tidak ada data dipilih"]); return; }
        $ok = $this->dm->bulk_mark_canceled($ids);
        if ($ok) $this->purge_public_caches();
        echo json_encode(["success"=>$ok,"title"=>$ok?"Berhasil":"Gagal","pesan"=>$ok?"Pesanan dibatalkan":"Sebagian gagal diproses"]);
    }

    /** Hapus */
   public function hapus_data(){
        $ids = $this->input->post('id');
        if (!is_array($ids) || !count($ids)){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Tidak ada data"]); return;
        }
        $res = $this->dm->bulk_delete($ids);

        $msgs = [];
        if (!empty($res['ok_count']))        $msgs[] = $res['ok_count']." data dihapus.";
        if (!empty($res['paid_ids']))        $msgs[] = "Ditolak (status paid): #".implode(', #', $res['paid_ids']);
        if (!empty($res['notfound_ids']))    $msgs[] = "Tidak ditemukan: #".implode(', #', $res['notfound_ids']);
        if (!empty($res['errors']))          $msgs[] = "Gagal: #".implode(', #', $res['errors']);

        $ok = !empty($res['ok_count']) && empty($res['errors']);
        if ($ok) $this->purge_public_caches();

        echo json_encode([
            "success"=>$ok,
            "title"=>$ok?"Berhasil":"Sebagian/Gagal",
            "pesan"=>implode(' ', $msgs) ?: 'Tidak ada yang diproses.'
        ]);
    }


public function print_struk_termalx($id = null)
{
    $id = (int)$id;
    if ($id <= 0) { show_error('ID tidak valid', 400); }

    $cat = $this->_active_category_for_user(); // null=kasir/admin, 1=kitchen, 2=bar
    $paper = $this->input->get('paper', true);
    $paper = ($paper === '80') ? '80' : '58';

    $bundle = ($cat === null)
        ? $this->dm->get_order_with_items($id)
        : $this->dm->get_order_with_items_by_cat($id, $cat);

    if (!$bundle) { show_error('Order tidak ditemukan', 404); }

    // ==== UPDATE status proses sesuai role ====
    if (!empty($bundle['items'])) {
    $now = date('Y-m-d H:i:s');

    if ($cat === 1) { // kitchen
        // jika belum segel, segel sekarang
        $row = $this->db->select('created_at,kitchen_done_at,kitchen_duration_s')
                        ->from('pesanan')->where('id',$id)->get()->row();
        if ($row && empty($row->kitchen_done_at)) {
            $dur = max(0, strtotime($now) - strtotime($row->created_at));
            $this->db->where('id',$id)->update('pesanan', [
                'status_pesanan_kitchen' => 2,
                'kitchen_done_at'        => $now,
                'kitchen_duration_s'     => $dur,
                'updated_at'             => $now,
            ]);
        } else {
            // tetap pastikan status=2
            $this->db->where('id',$id)->update('pesanan', [
                'status_pesanan_kitchen' => 2,
                'updated_at'             => $now,
            ]);
        }
    } elseif ($cat === 2) { // bar
        $row = $this->db->select('created_at,bar_done_at,bar_duration_s')
                        ->from('pesanan')->where('id',$id)->get()->row();
        if ($row && empty($row->bar_done_at)) {
            $dur = max(0, strtotime($now) - strtotime($row->created_at));
            $this->db->where('id',$id)->update('pesanan', [
                'status_pesanan_bar' => 2,
                'bar_done_at'        => $now,
                'bar_duration_s'     => $dur,
                'updated_at'         => $now,
            ]);
        } else {
            $this->db->where('id',$id)->update('pesanan', [
                'status_pesanan_bar' => 2,
                'updated_at'         => $now,
            ]);
        }
    }
}


    // Info toko
    $web = $this->om->web_me();
    $store = [
        'nama'   => $web->nama_website ?? 'Nama Toko',
        'alamat' => $web->alamat ?? 'Alamat',
        'kota'   => $web->kabupaten ?? '',
        'telp'   => $web->no_telp ?? '',
        'footer' => 'Terima kasih 🙏',
    ];

    $data = [
        'paper'      => $paper,
        'order'      => $bundle['order'],
        'items'      => $bundle['items'],
        'total'      => (int)$bundle['total'],
        'store'      => (object)$store,
        'printed_at' => date('Y-m-d H:i:s'),
    ];

    $html = $this->load->view('strukx', $data, true);
    $this->output->set_content_type('text/html; charset=UTF-8')->set_output($html);
}




public function print_struk_termal($id = null)
{
    $id = (int)$id;
    if ($id <= 0) { show_error('ID tidak valid', 400); }

    // pakai $this->dm (alias M_admin_pos)
    $bundle = $this->dm->get_order_with_items($id);
    if (!$bundle) { show_error('Order tidak ditemukan', 404); }

    $paper = $this->input->get('paper', true);
    $paper = ($paper === '80') ? '80' : '58';

    // info toko opsional
    // $store = [
    //     'nama'   => 'Nama Toko',
    //     'alamat' => 'Alamat Jalan Contoh No. 123',
    //     'kota'   => 'Kota',
    //     'telp'   => '0812-xxxx-xxxx',
    //     'footer' => 'Terima kasih 🙏',
    // ];
    // if (isset($this->om) && method_exists($this->om, 'web_me')) {
        $web = $this->om->web_me();
        if ($web) {
            $store['nama']   = trim($web->nama_website ?? $store['nama']);
            $store['alamat'] = trim($web->alamat ?? $store['alamat']);
            $store['kota']   = trim($web->kabupaten ?? $store['kota']);
            $store['telp']   = trim($web->no_telp ?? $store['telp']);
        }
    // }

    $data = [
        'paper'      => $paper,
        'order'      => $bundle['order'],
        'items'      => $bundle['items'],
        'total'      => (int)$bundle['total'],
        'store'      => (object)$store,
        'printed_at' => date('Y-m-d H:i:s'),
    ];

    $html = $this->load->view('front_end/struk', $data, true);
    $this->output->set_content_type('text/html; charset=UTF-8')->set_output($html);
}


    /** Search produk untuk picker */
    public function search_products(){
        $q   = $this->input->get('q', true) ?: '';
        $res = $this->dm->search_products($q, 30);
        $out = [];
        foreach($res as $p){
            $out[] = [
                'id'    => (int)$p->id,
                'nama'  => $p->nama,
                'harga' => (int)$p->harga,
                'stok'  => (int)$p->stok,
            ];
        }
        return $this->output->set_content_type('application/json')->set_output(json_encode([
            'success'=>true,'data'=>$out
        ]));
    }

    // application/controllers/Admin_pos.php
public function ping(){
    // supaya tidak di-cache
    $this->output
         ->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0')
         ->set_header('Pragma: no-cache');

    try{
        $s = $this->dm->get_stats();
        return $this->output->set_content_type('application/json')->set_output(json_encode([
            'success' => true,
            'total'   => (int)$s->total,
            'max_id'  => (int)$s->max_id,
            'last_ts' => $s->last_ts ? date('c', strtotime($s->last_ts)) : null,
        ]));
    }catch(\Throwable $e){
        return $this->output->set_content_type('application/json')->set_output(json_encode([
            'success'=>false,'error'=>$e->getMessage()
        ]));
    }
}

/** Stop timer kasir: set kasir_end_at & kasir_duration_sec (detik) kalau belum diisi */
private function _stop_kasir_timer($ids){
    if (empty($ids)) return;
    if (!is_array($ids)) $ids = [$ids];

    // Cek kolom agar aman di skema lama
    $hasStart = $this->db->field_exists('kasir_start_at','pesanan');
    $hasEnd   = $this->db->field_exists('kasir_end_at','pesanan');
    $hasDur   = $this->db->field_exists('kasir_duration_sec','pesanan');
    if (!$hasStart || !$hasEnd || !$hasDur) return;

    // Ambil rows yang end-nya masih null
    $rows = $this->db->select('id, created_at, kasir_start_at, kasir_end_at')
                     ->from('pesanan')
                     ->where_in('id', $ids)
                     ->get()->result();

    $now = date('Y-m-d H:i:s');
    foreach ($rows as $r){
        if (!empty($r->kasir_end_at)) continue; // sudah stop

        // Prioritaskan kasir_start_at, fallback ke created_at jika start belum ada
        $start = !empty($r->kasir_start_at) ? strtotime($r->kasir_start_at)
                                            : (!empty($r->created_at) ? strtotime($r->created_at) : null);
        $end   = strtotime($now);
        $dur   = ($start && $end && $end >= $start) ? ($end - $start) : 0;

        $this->db->where('id', (int)$r->id)->update('pesanan', [
            'kasir_end_at'       => $now,
            'kasir_duration_sec' => (int)$dur,
            'updated_at'         => $now,
        ]);
    }
}
public function assign_courier(){
    $order_id   = (int)$this->input->post('order_id', true);
    $courier_id = (int)$this->input->post('courier_id', true);
    if ($order_id <= 0 || $courier_id <= 0){
      return $this->_json(['ok'=>false,'msg'=>'Parameter tidak lengkap'], 400);
    }

    // ambil data order
    $order = $this->db->where('id',$order_id)->get('pesanan')->row();
    if (!$order) return $this->_json(['ok'=>false,'msg'=>'Order tidak ditemukan'], 404);
    if (strtolower($order->mode) !== 'delivery'){
      return $this->_json(['ok'=>false,'msg'=>'Order bukan delivery'], 422);
    }
    if (strtolower($order->status) === 'canceled'){
      return $this->_json(['ok'=>false,'msg'=>'Order sudah dibatalkan'], 422);
    }

    // ambil data kurir
    $kurir = $this->db->where('id',$courier_id)->get('kurir')->row();
    if (!$kurir) return $this->_json(['ok'=>false,'msg'=>'Kurir tidak ditemukan'], 404);
    $st = strtolower((string)($kurir->status ?? 'off'));
    if (!in_array($st, ['available','ontask'])){
      return $this->_json(['ok'=>false,'msg'=>'Kurir tidak tersedia'], 422);
    }

    $this->db->trans_start();

    // kalau sudah ditugaskan ke kurir yg sama, skip update agar idempotent
    $alreadyAssigned = (!empty($order->courier_id) && (int)$order->courier_id === (int)$kurir->id);

    if (!$alreadyAssigned){
      // update order
      $upd = [
        'courier_id'    => $kurir->id,
        'courier_name'  => $kurir->nama,
        'courier_phone' => $kurir->phone,
        'updated_at'    => date('Y-m-d H:i:s')
      ];
        $this->db->where('id',$order_id)->update('pesanan', $upd);
        $upda = [
        'courier_name'  => $kurir->nama,
        'courier_phone' => $kurir->phone,
        'courier_id'    => $kurir->id
        ];
        $this->db->where('src_id',$order_id)->update('pesanan_paid', $upda);

      // optional: tandai kurir on-task
      $this->db->where('id', $kurir->id)->update('kurir', ['status'=>'ontask']);

      // optional: log tracking
      $this->db->insert('delivery_log', [
        'order_id'   => $order_id,
        'courier_id' => $kurir->id,
        'event'      => 'assigned',
        'note'       => 'Kurir ditugaskan',
        'created_at' => date('Y-m-d H:i:s')
      ]);
    }

    $this->db->trans_complete();
    if (!$this->db->trans_status()){
      return $this->_json(['ok'=>false,'msg'=>'DB error'], 500);
    }

    /* =========================
     * KIRIM WHATSAPP (setelah commit)
     * ========================= */
    $store  = $this->db->get('identitas')->row();
    $toko   = trim((string)($store->nama_website ?? $store->nama ?? ''));
    if ($toko === '') $toko = 'Toko';

    $nomor  = (string)($order->nomor ?? $order_id);
    $custNm = trim((string)($order->nama ?? $order->customer_name ?? 'Pelanggan'));
    $custPh = trim((string)($order->customer_phone ?? $order->telp ?? $order->hp ?? ''));
    $alamat = trim((string)($order->alamat_kirim ?? $order->alamat ?? '-'));
    $ongkir = (int)($order->delivery_fee ?? 0);

    $lat = isset($order->dest_lat) ? (float)$order->dest_lat : null;
    $lng = isset($order->dest_lng) ? (float)$order->dest_lng : null;

    $nav = '';
        if ($lat !== null && $lng !== null) {
            // boleh pakai dir (navigasi) atau q (pin lokasi)
            $nav = 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($lat . ',' . $lng);
            // atau:
            // $nav = "https://www.google.com/maps/?q={$lat},{$lng}";
        }    // format pesan ke kurir
    $msgKurir = "Halo {$kurir->nama},\n".
                "Anda ditugaskan untuk delivery pesanan #{$nomor} dari {$toko}.\n".
                "Customer: {$custNm}".($custPh ? " ({$custPh})" : "")."\n".
                "Alamat: {$alamat}\n".
                // ($ongkir > 0 ? "Ongkir: ".$this->_idr($ongkir)."\n" : "").
                ($nav ? "Navigasi: {$nav}\n" : "").
                "Mohon konfirmasi di sistem setelah pickup. Terima kasih.";

    // format pesan ke customer
    $veh  = trim(((string)($kurir->vehicle ?? '')).' '.((string)($kurir->plate ?? '')));
    $veh  = trim($veh);
    $kurirTelp = trim((string)($kurir->phone ?? ''));
    $kurirWaCTC = $kurirTelp ? ('https://wa.me/'.$this->_msisdn($kurirTelp)) : '';

    $msgCust = "Halo {$custNm},\n".
               "Kurir telah ditugaskan.\n".
               "Pesanan Anda #{$nomor} dari {$toko} akan diantar. Pastikan Anda berada di lokasi pengantaran.\n".
               "Kurir: {$kurir->nama}".($kurirTelp ? " ({$kurirTelp})" : "").($veh ? "\nKendaraan: {$veh}" : "")."\n".
               // ($ongkir > 0 ? "Perkiraan ongkir: ".$this->_idr($ongkir)."\n" : "").
               // ($nav ? "Lokasi tujuan terdaftar.\n" : "").
               ($kurirWaCTC ? "Hubungi kurir: {$kurirWaCTC}\n" : "").
               "Terima kasih 🙏";

    // normalisasi nomor → 62xxxxx
    $custMsisdn  = $this->_msisdn($custPh);
    $kurirMsisdn = $this->_msisdn($kurirTelp);

    // kirim (aman: hanya jika fungsi helper ada & nomor valid)
    $okCust = $this->_wa_try($custMsisdn,  $msgCust);
    $okKur  = $this->_wa_try($kurirMsisdn, $msgKurir);

    // fallback Click-to-Chat
    $fallback = [
      'courier_ctc'  => $kurirMsisdn ? ('https://wa.me/'.$kurirMsisdn.'?text='.rawurlencode($msgKurir)) : null,
      'customer_ctc' => $custMsisdn  ? ('https://wa.me/'.$custMsisdn .'?text='.rawurlencode($msgCust))  : null,
    ];

    return $this->_json([
      'ok'=>true,
      'data'=>[
        'id'    => (int)$kurir->id,
        'nama'  => (string)$kurir->nama,
        'phone' => (string)$kurir->phone
      ],
      'wa'=>[
        'courier'=> ['ok'=>$okKur,  'to'=>$kurirMsisdn, 'fallback_ctc'=>$fallback['courier_ctc']],
        'customer'=>['ok'=>$okCust, 'to'=>$custMsisdn,  'fallback_ctc'=>$fallback['customer_ctc']],
      ]
    ]);
}

/* ===== Helper privat (kalau belum ada) ===== */
private function _idr($n){ return 'Rp '.number_format((int)$n,0,',','.'); }

private function _msisdn($p){
  $d = preg_replace('/\D+/', '', (string)$p);
  if ($d === '') return '';
  if (strpos($d,'62')===0) return $d;
  if ($d[0]==='0') return '62'.substr($d,1);
  if ($d[0]==='8') return '62'.$d;
  return $d;
}

/** Panggil helper send_wa_single() kalau ada, aman dari error */
private function _wa_try($to,$msg){
  if ($to==='') return false;
  if (function_exists('send_wa_single')){
    try { return (bool)send_wa_single($to,$msg); } catch (\Throwable $e) { return false; }
  }
  return false;
}


  private function _json($arr, $code=200){
    $this->output->set_status_header($code);
    $this->output->set_output(json_encode($arr));
    return;
  }


  // ====== TAMBAHKAN di class Admin_pos (controller) ======
public function list_meja(){
    // agar response JSON tidak di-cache
    $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

    $q = trim((string)$this->input->get('q', true));

    $this->db->from('meja')->where('status','aktif');
    if ($q !== ''){
        $this->db->group_start()
                 ->like('kode',$q)->or_like('nama',$q)->or_like('area',$q)
                 ->group_end();
    }
    $this->db->order_by('kode','ASC');

    $rows = $this->db->get()->result();
    $out  = [];

    foreach ($rows as $r){
        // link: produk/tag/{kode}/{qr_token}
        $url = site_url('produk/tag/'.$r->kode.'/'.$r->qr_token);

        $out[] = [
            'id'        => (int)$r->id,
            'kode'      => (string)$r->kode,
            'nama'      => (string)$r->nama,
            'area'      => (string)($r->area ?? ''),
            'kapasitas' => (int)($r->kapasitas ?? 0),
            'qrcode'    => $r->qrcode ? base_url($r->qrcode) : null,
            'link'      => $url, // <<— PENTING: URL absolut
        ];
    }

    return $this->output->set_content_type('application/json')
        ->set_output(json_encode(['success'=>true,'data'=>$out]));
}



}
