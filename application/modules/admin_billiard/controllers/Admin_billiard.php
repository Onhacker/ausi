<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_billiard extends Admin_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('M_admin_billiard','dm');
    }

    private function purge_public_caches(){
        $this->load->driver('cache', ['adapter' => 'file']);
        $this->cache->save('billiard_ver', time(), 365*24*3600);
        $this->output->set_header('X-Cache-Purged: billiard');
    }

    public function index(){
        $data["controller"] = get_class($this);
        $data["title"]      = "Booking Billiard";
        $data["subtitle"]   = "Daftar Pesanan";
        $data["content"]    = $this->load->view('admin_billiard_view',$data,true);
        $this->render($data);
    }

    /** DataTables server-side */
    public function get_data(){
        try{
            $status = $this->input->post('status', true);
            if ($status === '' || $status === null) { $status = 'all'; }

            $this->dm->set_max_rows(200);
            $this->dm->set_status_filter($status);

            $list = $this->dm->get_data();
            $data = [];

            foreach($list as $r){
            // ===== Kode Booking (atau "Selesai Main")
                $kode = htmlspecialchars($r->kode_booking ?: '-', ENT_QUOTES, 'UTF-8');

            // Hitung apakah sudah selesai main
                $endTs = null;
                if (!empty($r->tanggal) && !empty($r->jam_selesai)) {
                // asumsi $r->tanggal = YYYY-mm-dd, $r->jam_selesai = HH:ii(:ss)?
                    $endTs = strtotime(trim($r->tanggal.' '.$r->jam_selesai));
                }
                $isFinished = $endTs && time() >= $endTs;

            // Tampilkan di kolom "Kode Booking":
            // - Jika selesai → "Selesai Main"
            // - Jika belum → kode booking seperti biasa
                $kode_html = $isFinished
                ? '<span class="badge badge-dark">'.$kode.'</span><br><span class="badge badge-success">Selesai Main 🎱</span>'
                : '<span class="badge badge-dark">'.$kode.'</span>';

            // ===== Meja / Nama
                $meja = $r->nama_meja ?: ('Meja #'.$r->meja_id);
                $meja_html = htmlspecialchars($meja, ENT_QUOTES, 'UTF-8');
                $nama = trim((string)$r->nama);
                if ($nama !== ''){
                    $meja_html .= '<div class="text-muted small">'.htmlspecialchars($nama, ENT_QUOTES, 'UTF-8').'</div>';
                }

            // ===== Durasi
                $durasi = (int)$r->durasi_jam;
                $durasi_html = '<b>'.$durasi.' jam</b>';

            // ===== Waktu dibuat booking
                $tgl_book = $r->created_at ? date('d-m-Y', strtotime($r->created_at)) : '-';
                $tgl_book_html = $tgl_book.'<div class="text-dark small"> Jam '.htmlspecialchars(date('H:i:s', strtotime($r->created_at)),ENT_QUOTES,'UTF-8').'</div>';

            // ===== Waktu main
                $tgl = $r->tanggal ? date('d-m-Y', strtotime($r->tanggal)) : '-';
                $jam = "Jam ".trim(($r->jam_mulai ?: '').' - '.($r->jam_selesai ?: ''));
                $waktu_html = $tgl.' ('.$durasi_html.')' .'<div class="text-blue small">'.htmlspecialchars($jam,ENT_QUOTES,'UTF-8').'</div>';

            // ===== Harga / Grand
                $harga_jam = (int)$r->harga_per_jam;
                $grand     = (int)$r->grand_total;
                $harga_html = 'Rp '.number_format($harga_jam,0,',','.');
                $grand_html = 'Rp '.number_format($grand,0,',','.');

            // ===== Status bayar (tetap seperti semula)
                $sraw = strtolower((string)$r->status);
                $badge = 'secondary'; 
                $label = $sraw;
                switch ($sraw){
                    case 'draft':            $badge='warning'; $label='menunggu pembayaran'; break;
                    case 'menunggu_bayar':   $badge='warning'; $label='menunggu pembayaran'; break;
                    case 'verifikasi':       $badge='info';    $label='menunggu verifikasi'; break;
                    case 'terkonfirmasi':    $badge='success'; $label='lunas'; break;
                    case 'batal':            $badge='dark';    $label='batal'; break;
                    case 'free':             $badge='primary'; $label='free'; break;
                }
                $status_html = '<span class="badge badge-pill badge-'.$badge.'">'.htmlspecialchars($label,ENT_QUOTES,'UTF-8').'</span>';

            // ===== Metode
                $metode_html = htmlspecialchars($r->metode_bayar ?: '-', ENT_QUOTES, 'UTF-8');

                          // ... di dalam foreach($list as $r) { setelah $metode_html ... 
                $idInt     = (int)$r->id_pesanan;
                $namaPlain = trim((string)$r->nama) ?: '-';
                $namaAttr  = htmlspecialchars($namaPlain, ENT_QUOTES, 'UTF-8');
                $mejaName  = $r->nama_meja ?: ('Meja #'.$r->meja_id);
                $mejaAttr  = htmlspecialchars($mejaName, ENT_QUOTES, 'UTF-8');

                
                    $canReschedule = (!$isFinished && strtolower((string)$r->status) !== 'batal');

                    if ($canReschedule) {
                        $btnResch = '<button type="button" class="btn btn-sm btn-success mr-1 btn-reschedule"
                                         data-id="'.$idInt.'"
                                         data-nama="'.$namaAttr.'"
                                         data-meja="'.$mejaAttr.'"
                                         data-tanggal="'.htmlspecialchars($r->tanggal ?: '', ENT_QUOTES, 'UTF-8').'"
                                         data-jam_mulai="'.htmlspecialchars(substr((string)$r->jam_mulai,0,5), ENT_QUOTES, 'UTF-8').'"
                                         title="Reschedule">
                                       <i class="fe-clock"></i>
                                     </button>';
                    } else {
                        // tampilkan tombol non-aktif (tooltip menjelaskan alasannya)
                        $btnResch = '<button type="button" class="btn btn-sm btn-outline-secondary mr-1" disabled
                                         title="Tidak bisa reschedule: booking batal / sudah selesai main">
                                       <i class="fe-clock"></i>
                                     </button>';
                    }

                // Tombol lain: kirim element (this) supaya bisa baca data-nama & data-meja
                $btnPaid   = '<button type="button" class="btn btn-sm btn-primary mr-1"
                                 data-id="'.$idInt.'" data-nama="'.$namaAttr.'" data-meja="'.$mejaAttr.'"
                                 onclick="mark_paid_one(this)" title="Konfirmasi Bayar"><i class="fe-check-circle"></i></button>';

                $btnCancel = '<button type="button" class="btn btn-sm btn-secondary mr-1"
                                 data-id="'.$idInt.'" data-nama="'.$namaAttr.'" data-meja="'.$mejaAttr.'"
                                 onclick="mark_canceled_one(this)" title="Batalkan"><i class="fe-x-circle"></i></button>';

                $unameLower = strtolower((string)$this->session->userdata('admin_username'));
                $btnDelete  = ($unameLower === 'admin')
                  ? '<button type="button" class="btn btn-sm btn-danger"
                        data-id="'.$idInt.'" data-nama="'.$namaAttr.'" data-meja="'.$mejaAttr.'"
                        onclick="hapus_data_one(this)" title="Hapus"><i class="fa fa-trash"></i></button>'
                  : '';

                $actionsHtml = '<div class="btn-group btn-group-sm" role="group">'.$btnPaid.$btnResch.$btnCancel.$btnDelete.'</div>';



            // ===== Build row
                $row = [];
                $row['id']        = $idInt;
                $row['no']        = '';
            $row['kode']      = $kode_html;     // <-- sekarang bisa "Selesai Main"
            $row['meja']      = $meja_html;
            $row['waktu']     = $waktu_html;
            $row['durasi']    = $tgl_book_html;
            $row['harga']     = $harga_html;
            $row['grand']     = $grand_html;
            $row['status']    = $status_html;
            $row['metode']    = $metode_html;
            $row['aksi']      = $actionsHtml;

            $data[] = $row;
        }

        $out = [
            "draw"            => (int)$this->input->post('draw'),
            "recordsTotal"    => $this->dm->count_all(),
            "recordsFiltered" => $this->dm->count_filtered(),
            "data"            => $data,
        ];
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

    /** Detail sederhana (HTML partial via JSON) */
    public function detail($id=null){
        $id = (int)$id;
        $row = $this->dm->get_order($id);
        if (!$row){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]); return;
        }

        $html = '<div class="table-responsive"><table class="table table-sm table-striped mb-0">';
        $add = function($k,$v) use (&$html){
            $html .= '<tr><th style="width:180px">'.$k.'</th><td>'.$v.'</td></tr>';
        };
        $add('Kode Booking', htmlspecialchars($row->kode_booking,ENT_QUOTES,'UTF-8'));
        $add('Nama', htmlspecialchars($row->nama,ENT_QUOTES,'UTF-8'));
        $add('No. HP', htmlspecialchars($row->no_hp,ENT_QUOTES,'UTF-8'));
        $add('Meja', htmlspecialchars($row->nama_meja ?: ('Meja #'.$row->meja_id),ENT_QUOTES,'UTF-8'));
        $add('Tanggal', date('d-m-Y', strtotime($row->tanggal)));
        $add('Jam', htmlspecialchars(($row->jam_mulai.' - '.$row->jam_selesai),ENT_QUOTES,'UTF-8'));
        $add('Durasi', (int)$row->durasi_jam.' jam');
        $add('Harga/Jam', 'Rp '.number_format((int)$row->harga_per_jam,0,',','.'));
        $add('Subtotal', 'Rp '.number_format((int)$row->subtotal,0,',','.'));
        if ((int)$row->kode_unik > 0) $add('Kode Unik', number_format((int)$row->kode_unik,0,',','.'));
        $add('Grand Total', '<b>Rp '.number_format((int)$row->grand_total,0,',','.').'</b>');
        $add('Metode Bayar', htmlspecialchars($row->metode_bayar ?: '-',ENT_QUOTES,'UTF-8'));
        $add('Status', htmlspecialchars($row->status,ENT_QUOTES,'UTF-8'));
        $add('Dibuat', htmlspecialchars($row->created_at,ENT_QUOTES,'UTF-8'));
        if (!empty($row->updated_at)) $add('Diperbarui', htmlspecialchars($row->updated_at,ENT_QUOTES,'UTF-8'));
        $html .= '</table></div>';

        echo json_encode(["success"=>true, "html"=>$html, "title"=>'Detail #'.$row->kode_booking]);
    }

    /** Tandai KONFIRM (paid) */
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

    // proses konfirmasi bayar di model
    $res = $this->dm->bulk_mark_confirmed($ids);
        // Pastikan bulk_mark_confirmed() di model kamu nge-return minimal:
        // [
        //   'ok_count'      => 2,
        //   'ok_ids'        => [15,18],
        //   'blocked_ids'   => [...],
        //   'already_ids'   => [...],
        //   'notfound_ids'  => [...],
        //   'copied_count'  => 2,
        //   'copied_skipped'=> [...],
        //   'errors'        => []
        // ]

        if (!empty($res['ok_count'])) {
            $this->purge_public_caches();
        }

        // Kirim WA hanya utk booking yang bener-bener sukses dikonfirmasi barusan
        $wa_logs = [];
        if (!empty($res['ok_ids']) && is_array($res['ok_ids'])) {
            foreach ($res['ok_ids'] as $bid) {
                $wa_logs[] = "#{$bid}: ".$this->_notify_paid_whatsapp($bid);
            }
        }

        // susun pesan response buat frontend
        $msgs = [];
        if (!empty($res['ok_count']))       $msgs[] = $res['ok_count']." booking dikonfirmasi.";
        if (!empty($res['blocked_ids']))    $msgs[] = "Ditolak (metode bayar belum di-set): #".implode(', #', $res['blocked_ids']);
        if (!empty($res['already_ids']))    $msgs[] = "Diabaikan (sudah terkonfirmasi/batal): #".implode(', #', $res['already_ids']);
        if (!empty($res['notfound_ids']))   $msgs[] = "Tidak ditemukan: #".implode(', #', $res['notfound_ids']);
        if (!empty($res['copied_count']))   $msgs[] = "Disalin ke tabel paid: ".$res['copied_count']." baris.";
        if (!empty($res['copied_skipped'])) $msgs[] = "Lewati salin (sudah ada): #".implode(', #', $res['copied_skipped']);

        if (!empty($wa_logs)) {
            $msgs[] = "WA: ".implode(' ; ', $wa_logs);
        }

        $ok = !empty($res['ok_count']) && empty($res['errors']);

        echo json_encode([
            "success"=>$ok,
            "title"=>$ok?"Berhasil":"Sebagian/Gagal",
            "pesan"=>implode(' ', $msgs) ?: 'Tidak ada yang diproses.'
        ]);
    }
private function _pretty_hp(string $hp): string {
  $d = preg_replace('/\D+/', '', $hp);
  if ($d === '') return '';
  if (strpos($d, '62') === 0) return '0' . substr($d, 2);
  return (strpos($d, '0') === 0) ? $d : $d;
}

    private function _notify_paid_whatsapp($booking_id)
{
    try {
        // 1. Ambil data pesanan setelah mark_paid
        $rec = $this->dm->get_order((int)$booking_id);
        if (!$rec) {
            return "data tidak ditemukan";
        }

        // 2. Ambil nomor hp asli dari pesanan
        $hp_tujuan = $rec->no_hp ?? '';
        if ($hp_tujuan === '') {
            return "no hp kosong";
        }

        // 3. Nama meja (pakai snapshot kalau ada, kalau gak fallback ke tabel master)
        $meja_nama = isset($rec->nama_meja) && $rec->nama_meja !== ''
            ? $rec->nama_meja
            : ($this->db->select('nama_meja')
                        ->get_where('meja_billiard', ['id_meja' => $rec->meja_id])
                        ->row('nama_meja')
               ?: ('MEJA #'.($rec->meja_id ?? '')));

        // 4. Info brand toko
        $web  = $this->om->web_me();
        $site = $web->nama_website ?? 'Sistem';

        // 5. Angka uang
        $subtotal   = (int)($rec->subtotal ?? 0);
        $kode_unik  = (int)($rec->kode_unik ?? 0);
        $grand      = (int)($rec->grand_total ?? ($subtotal + $kode_unik));
        $isFree     = ($grand === 0);

        // 6. Link tiket/detail booking
        $link = $isFree
            ? (site_url('billiard/free') . '?t=' . urlencode($rec->access_token ?? ''))
            : (site_url('billiard/cart') . '?t=' . urlencode($rec->access_token ?? ''));

        // 7. Format tanggal & jam
        $tgl_label = (function($tgl){
            if (function_exists('hari') && function_exists('tgl_view')) {
                return hari($tgl).", ".tgl_view($tgl);
            }
            return $tgl ?: '-';
        })($rec->tanggal ?? '');

        $jamMulai   = substr($rec->jam_mulai   ?? '00:00:00',0,5);
        $jamSelesai = substr($rec->jam_selesai ?? '00:00:00',0,5);

        // 8. Judul header WA setelah dibayar
        $judul_header = $isFree
            ? 'Booking Gratis Dikonfirmasi'
            : 'Pembayaran Diterima';

        // 9. Susun isi pesan WA (gaya yang sudah kita pakai)
        $lines = [];

        // HEADER
        $lines[] = "🎱 *{$judul_header} — {$site}*";
        $lines[] = "--------------------------------";
        $lines[] = "Terima kasih, booking Anda sudah aktif. 🙌";
        $lines[] = "";

        // DETAIL BOOKING
        // $lines[] = "📄 *Kode Booking:* " . ($rec->kode_booking ?? '-');
        // $lines[] = "🙍 *Nama:* " . ($rec->nama ?? '-');
        // $lines[] = "📞 *HP:* "   . ($this->_pretty_hp($rec->no_hp ?? ''));
        // $lines[] = "🪑 *Meja:* " . $meja_nama;
        // $lines[] = "📅 *Tanggal:* " . $tgl_label;
        // $lines[] = "⏰ *Jam:* " . $jamMulai . "–" . $jamSelesai;
        // $lines[] = "⏳ *Durasi:* " . ($rec->durasi_jam ?? '-') . " Jam";
        // $lines[] = "";

        // // TARIF & BIAYA
        // $lines[] = "💸 *Tarif / Jam:* Rp" . number_format((int)($rec->harga_per_jam ?? 0),0,',','.');
        // $lines[] = "🔢 *Kode Unik:* Rp" . number_format($kode_unik,0,',','.');
        // $lines[] = "🧮 *Subtotal:* Rp"  . number_format($subtotal,0,',','.');

        // if ($isFree) {
        //     $lines[] = "✅ *Total Bayar:* Rp0";
        //     $lines[] = "_(Voucher / free play)_";
        // } else {
        //     $lines[] = "💳 *Total Bayar:* Rp" . number_format($grand,0,',','.');
        // }
        // $lines[] = "";

        // LINK TIKET / DETAIL BOOKING
        $lines[] = "🎟 *Tiket / Detail Booking:*";
        $lines[] = $link;
        $lines[] = "Saat datang, tunjukkan Tiket / Detail Booking ke kasir sebelum mulai main.";
        $lines[] = "";
        $lines[] = "💾 Simpan kontak ini supaya link bisa diklik.";
        $lines[] = "";

        // INSTRUKSI KASIR
        // FOOTER
        $lines[] = "📣 _Pesan ini dikirim otomatis oleh sistem {$site}. Mohon jangan dibalas._";

        $pesan = implode("\n", $lines);

        // 10. Kirim WA via function kamu
        // send_wa_single() di tempatmu SUDAH handle normalisasi tujuan,
        // jadi kita kirim raw $rec->no_hp saja.
        $resSend = send_wa_single($hp_tujuan, $pesan);

        // logging buat debugging, tapi tidak ganggu flow
        if (is_string($resSend)) {
            log_message('debug', 'WA konfirmasi paid -> '.$hp_tujuan.' hasil: '.$resSend);
        } else {
            log_message('debug', 'WA konfirmasi paid -> '.$hp_tujuan.' hasil: '.json_encode($resSend));
        }

        return "WA ok ".$hp_tujuan;

    } catch (Throwable $e) {
        log_message('error', 'WA konfirmasi paid error: '.$e->getMessage().' trace: '.$e->getTraceAsString());
        return "WA gagal (".$e->getMessage().")";
    }
}

    /** Batalkan */
    public function mark_canceled(){
    $ids = $this->input->post('id');
    if (!is_array($ids) || !count($ids)){
        echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Tidak ada data dipilih"]); return;
    }

    $res = $this->dm->bulk_mark_canceled($ids);

    $msgs = [];
    if (!empty($res['ok_count']))     $msgs[] = $res['ok_count']." booking dibatalkan.";
    if (!empty($res['paid_deleted'])) $msgs[] = "Snapshot paid dihapus: ".$res['paid_deleted']." baris.";
    if (!empty($res['notfound_ids'])) $msgs[] = "Tidak ditemukan: #".implode(', #', $res['notfound_ids']);
    if (!empty($res['errors']))       $msgs[] = "Error: #".implode(', #', $res['errors']);

    $ok = !empty($res['ok_count']) && empty($res['errors']);
    if ($ok) $this->purge_public_caches();

    echo json_encode([
        "success"=>$ok,
        "title"=>$ok?"Berhasil":"Sebagian/Gagal",
        "pesan"=> $msgs ? implode(' ', $msgs) : 'Tidak ada yang diproses.'
    ]);
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
        if (!empty($res['paid_deleted']))    $msgs[] = "Snapshot paid dihapus: ".$res['paid_deleted']." baris.";
        if (!empty($res['confirmed_ids']))   $msgs[] = "Ditolak (status terkonfirmasi): #".implode(', #', $res['confirmed_ids']);
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

    /** Reschedule (ubah tanggal main & jam mulai) + tolak jika bentrok */
public function reschedule(){
    $this->output->set_content_type('application/json');
    try{
        $id        = (int)$this->input->post('id');
        $tanggal   = trim((string)$this->input->post('tanggal'));
        $jam_mulai = trim((string)$this->input->post('jam_mulai'));

        if ($id <= 0 || $tanggal === '' || $jam_mulai === ''){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"ID, tanggal, dan jam mulai wajib diisi."]); return;
        }

        // Ambil data booking saat ini
        $row = $this->dm->get_order($id);
        if (!$row){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Booking #$id tidak ditemukan."]); return;
        }

        // Durasi & normalisasi jam
        $durasi = (int)$row->durasi_jam; if ($durasi <= 0) $durasi = 1;
        if (strlen($jam_mulai) === 5) $jam_mulai .= ':00';

        $startTs = strtotime($tanggal.' '.$jam_mulai);
        if (!$startTs){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Format tanggal/jam tidak valid."]); return;
        }
        $endTs = strtotime('+'.$durasi.' hours', $startTs);
        $jam_selesai = date('H:i:s', $endTs);

        // Tolak jika status batal
        $st = strtolower((string)$row->status);
        if ($st === 'batal'){
            echo json_encode(["success"=>false,"title"=>"Ditolak","pesan"=>"Tidak bisa reschedule: status sudah dibatalkan."]); return;
        }

        // Cek bentrok slot
        $cek = $this->dm->check_slot_conflict((int)$row->meja_id, $tanggal, $jam_mulai, $durasi, $id);
        if (!empty($cek['conflict'])){
            $ids = !empty($cek['ids']) ? (' #'.implode(', #',$cek['ids'])) : '';
            echo json_encode([
                "success"=>false,
                "title"=>"Slot Bentrok",
                "pesan"=>"Jadwal berbenturan dengan booking lain".$ids.". Silakan pilih waktu lain."
            ]);
            return;
        }

        // Update pesanan_billiard
        $ok = $this->dm->update_schedule($id, $tanggal, $jam_mulai, $jam_selesai);
        if (!$ok){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Tidak bisa menyimpan reschedule."]); return;
        }

        // Update snapshot di billiard_paid (jika ada)
        $paid_aff = $this->dm->update_paid_schedule($id, $tanggal, $jam_mulai, $jam_selesai);

        // bersihkan cache publik
        $this->purge_public_caches();

        // Info untuk pesan
        $nm        = trim((string)($row->nama ?? '')) ?: '-';
        $meja_nama = isset($row->nama_meja) && $row->nama_meja !== ''
            ? $row->nama_meja
            : ($this->db->select('nama_meja')->get_where('meja_billiard', ['id_meja' => $row->meja_id])->row('nama_meja')
               ?: ('MEJA #'.($row->meja_id ?? '')));

        echo json_encode([
            "success"=>true,
            "title"=>"Berhasil",
            "pesan"=>"{$nm} — {$meja_nama} dijadwalkan ke {$tanggal}, {$jam_mulai}–{$jam_selesai} (durasi {$durasi} jam)."
                    .($paid_aff ? " Snapshot paid ikut diperbarui." : "")
        ]);
    }catch(\Throwable $e){
        echo json_encode(["success"=>false,"title"=>"Error","pesan"=>$e->getMessage()]);
    }
}

    
    /** Ping untuk notifikasi ringan */
    public function ping(){
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
}
