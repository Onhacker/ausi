<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_scan extends Admin_Controller {

    public function __construct(){
        parent::__construct();
        // pastikan admin sudah login & punya akses modul ini
        cek_session_akses(get_class($this), $this->session->userdata('admin_session'));
    }

    // ===== JSON helpers =====
    private function json_exit($payload, int $status = 200, array $headers = [])
    {
        if (!is_string($payload)) {
            $payload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        foreach ($headers as $k => $v) {
            $this->output->set_header($k.': '.$v);
        }
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output($payload)
            ->_display();
        exit;
    }

    private function json_flush($payload, int $status = 200, array $headers = [])
    {
        if (!is_string($payload)) {
            $payload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        foreach ($headers as $k => $v) {
            $this->output->set_header($k.': '.$v);
        }
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output($payload)
            ->_display();

        if (function_exists('fastcgi_finish_request')) { fastcgi_finish_request(); }
        else { @ob_end_flush(); @flush(); }
    }
    // ========================

    public function index(){
        $data["controller"] = get_class($this);
        $data["title"]      = "Scan QR";
        $data["subtitle"]   = "Check-in / Checkout";
        $data["content"]    = $this->load->view($data["controller"]."_view", $data, true);
        $this->render($data);
    }

    /**
     * Halaman detail booking untuk ADMIN (tanpa token)
     * URL: admin_scan/detail/{kode_booking}
     */
    public function detail($kode_booking = '')
{
    $kode_booking = trim((string)$kode_booking);
    if ($kode_booking === '') show_404();

    $row = $this->db->get_where('booking_tamu', ['kode_booking' => $kode_booking])->row();
    if (!$row) show_404();

    // NEW: ambil daftar pendamping
    $pendamping_rows = $this->db
        ->order_by('id_pendamping','ASC')
        ->get_where('booking_pendamping', ['kode_booking' => $row->kode_booking])
        ->result();

    // data tambahan untuk tampilan
    $unit_nama = $this->db->select('nama_unit')->get_where('unit_tujuan', ['id'=>$row->unit_tujuan])->row('nama_unit');
    $qr_file   = FCPATH.'uploads/qr/qr_'.$row->kode_booking.'.png';
    $data = [
        "controller" => get_class($this),
        "title"      => "Detail Booking",
        "subtitle"   => "Detail",
        "booking"    => $row,
        "unit_nama"  => $unit_nama ?: '-',
        "qr_url"     => is_file($qr_file) ? base_url('uploads/qr/qr_'.$row->kode_booking.'.png') : null,
        "surat_url"  => !empty($row->surat_tugas) ? base_url('uploads/surat_tugas/'.$row->surat_tugas) : null,
        "foto_url"   => !empty($row->foto)        ? base_url('uploads/foto/'.$row->foto)              : null,

        // NEW: kirim ke view
        "pendamping_rows" => $pendamping_rows,
    ];

    $data["content"] = $this->load->view($data["controller"]."_detail_view", $data, true);
    $this->render($data);
}
public function upload_surat_tugas()
{
    $kode = $this->input->post('kode', true);
    if (!$kode) return $this->output->set_content_type('application/json')->set_output(json_encode(['ok'=>false,'msg'=>'Kode kosong']));

    // validasi booking
    $b = $this->db->get_where('booking_tamu', ['kode_booking'=>$kode])->row();
    if (!$b) return $this->output->set_content_type('application/json')->set_output(json_encode(['ok'=>false,'msg'=>'Booking tidak ditemukan'], JSON_UNESCAPED_UNICODE));

    // upload (PDF/JPG/PNG, 2MB)
    $config = [
        'upload_path'      => FCPATH.'uploads/surat_tugas/',
        'allowed_types'    => 'pdf|jpg|jpeg|png',
        'max_size'         => 2048,
        'file_ext_tolower' => true,
        'overwrite'        => false,
        'remove_spaces'    => true,
        'file_name'        => 'surat_'.$kode.'_'.date('Ymd_His'),
    ];
    if (!is_dir($config['upload_path'])) @mkdir($config['upload_path'], 0755, true);

    $this->load->library('upload', $config);
    if (!$this->upload->do_upload('surat_tugas')) {
        return $this->output->set_content_type('application/json')->set_output(json_encode(['ok'=>false,'msg'=>$this->upload->display_errors('', '')]));
    }
    $data = $this->upload->data();
    $filename = $data['file_name'];
    $url = base_url('uploads/surat_tugas/'.$filename);

    // simpan ke booking_tamu.surat_tugas (opsional hapus file lama)
    $old = $b->surat_tugas;
    $this->db->where('kode_booking', $kode)->update('booking_tamu', ['surat_tugas'=>$filename]);
    if ($old && $old !== $filename) {
        $oldPath = FCPATH.'uploads/surat_tugas/'.$old;
        if (is_file($oldPath)) @unlink($oldPath);
    }

    return $this->output->set_content_type('application/json')->set_output(json_encode(['ok'=>true,'url'=>$url,'file'=>$filename]));
}


    /**
     * POST: kode
     * Ubah status -> checked_in (idempotent)
     */
   public function checkin_api(){
        $kode = trim((string)$this->input->post('kode', true));
        if ($kode === '') {
            return $this->json_exit(["ok"=>false, "msg"=>"Kode kosong"], 400);
        }

        $row = $this->db->get_where('booking_tamu', ['kode_booking'=>$kode])->row_array();
        if (!$row) {
            return $this->json_exit(["ok"=>false, "msg"=>"Booking tidak ditemukan"], 404);
        }

        // Sudah checkout? stop
        if (!empty($row['checkout_at'])) {
            return $this->json_exit(["ok"=>false, "msg"=>"Sudah checked_out"], 409);
        }

        // Hanya boleh dari status ini
        $status = strtolower((string)$row['status']);
        if (!in_array($status, ['approved','checked_in'], true)) {
            return $this->json_exit(["ok"=>false, "msg"=>"Status sekarang '$status' tidak bisa di-check-in"], 409);
        }

        // ===== VALIDASI WAKTU (Asia/Makassar disarankan diset global) =====
        $todayLocal = date('Y-m-d');
        $nowTs      = time(); // epoch saat ini (Makassar)

        $tanggal = trim((string)$row['tanggal']);   // 'Y-m-d'
        $jam     = trim((string)$row['jam']);       // 'H:i' atau 'H:i:s'
        if ($tanggal === '' || $jam === '') {
            return $this->json_exit(["ok"=>false, "msg"=>"Check-in ditolak: jadwal belum lengkap."], 409);
        }

        // $scheduledStr = $tanggal.' '.$jam;          // "YYYY-mm-dd HH:ii[:ss]"
        $scheduledTs = !empty($row['jadwal_at'])
            ? strtotime($row['jadwal_at'])
            : strtotime($row['tanggal'].' '.$row['jam']); // fallback

        // $scheduledTs  = strtotime($scheduledStr);
        if ($scheduledTs === false) {
            return $this->json_exit(["ok"=>false, "msg"=>"Check-in ditolak: format jadwal tidak valid."], 409);
        }

        // Kebijakan: hanya hari H
        if ($tanggal !== $todayLocal) {
            return $this->json_exit([
                "ok"=>false,
                "msg"=>"Check-in ditolak: hanya bisa pada tanggal ".date('d-m-Y', strtotime($tanggal))."."
            ], 409);
        }

        // Ambil konfigurasi early/late/lead dari profil web
        $ceki      = $this->om->web_me(); // <- penting: titik koma
        $EARLY_MIN = isset($ceki->early_min)         ? (int)$ceki->early_min         : 10;
        $LATE_MIN  = isset($ceki->late_min)          ? (int)$ceki->late_min          : 60;
        $LEAD_MIN  = isset($ceki->min_lead_minutes)  ? (int)$ceki->min_lead_minutes  : 10;

        // Clamp 0..1440 menit
        $EARLY_MIN = max(0, min(1440, $EARLY_MIN));
        $LATE_MIN  = max(0, min(1440, $LATE_MIN));
        $LEAD_MIN  = max(0, min(1440, $LEAD_MIN));

        // Hitung window check-in
        $createdTs = (!empty($row['create_date']) && $row['create_date'] !== '0000-00-00 00:00:00')
        ? strtotime($row['create_date']) : null;

        $startByEarly = $scheduledTs - ($EARLY_MIN * 60);               // boleh X menit sebelum jadwal
        $startByLead  = $createdTs ? $createdTs + ($LEAD_MIN * 60) : null; // tidak boleh sebelum booking+lead
        $startTs      = $startByLead ? max($startByEarly, $startByLead) : $startByEarly;
        $endTs        = $scheduledTs + ($LATE_MIN  * 60);               // toleransi keterlambatan

        if ($nowTs < $startTs) {
            return $this->json_exit([
                "ok"=>false,
                "msg"=>"Check-in ditolak: terlalu awal. Boleh mulai ".date('H:i',$startTs)."–".date('H:i',$endTs)." WITA."

            ], 409);
        }
        if ($nowTs > $endTs) {
            return $this->json_exit([
                "ok"=>false,
                "msg"=>"Check-in ditolak: melewati batas toleransi sampai ".date('H:i', $endTs)." WITA."
            ], 409);
        }
        // ===== END VALIDASI WAKTU =====

        // Nama petugas dari session
        $petugas = trim((string)($this->session->userdata('admin_nama') ?: $this->session->userdata('admin_username') ?: ''));

        // DETAIL ADMIN (tanpa token)
        $detail_url = site_url('admin_scan/detail/'.$row['kode_booking']);

        // Idempotent: bila sudah checked_in sebelumnya, jangan ubah checkin_at
        if (!empty($row['checkin_at']) || $status === 'checked_in') {
            if ($petugas !== '' && empty($row['petugas_checkin'])) {
                $this->db->where('kode_booking', $kode)->update('booking_tamu', [
                    'petugas_checkin' => $petugas
                ]);
                $row['petugas_checkin'] = $petugas;
            }

            return $this->json_exit([
                "ok"=>true, "msg"=>"Sudah checked_in", "already"=>true,
                "detail_url"=>$detail_url,
                "data"=>[
                    "kode"             => $row['kode_booking'],
                    "nama"             => $row['nama_tamu'],
                    "checkin_at"       => $row['checkin_at'],
                    "status"           => "checked_in",
                    "petugas_checkin"  => $row['petugas_checkin'] ?? null,
                ]
            ], 200);
        }

        // Proses check-in baru
        $ts = time(); // timestamp sekarang

        $hari_id = [
            'Sun'=>'Minggu','Mon'=>'Senin','Tue'=>'Selasa',
            'Wed'=>'Rabu','Thu'=>'Kamis','Fri'=>'Jumat','Sat'=>'Sabtu'
        ];

        $now_db    = date('Y-m-d H:i:s', $ts); // format DB
        $now_label = date('H:i:s', $ts).' '.($hari_id[date('D',$ts)] ?? date('D',$ts)).', '.date('d-m-Y', $ts);

        $this->db->where('kode_booking', $kode)->update('booking_tamu', [
            'checkin_at'      => $now_db,
            'status'          => 'checked_in',
            'petugas_checkin' => $petugas ?: null,
        ]);

        return $this->json_exit([
            "ok"        => true,
            "msg"       => "Check-in berhasil",
            "detail_url"=> $detail_url,
            "data"      => [
                "kode"            => $row['kode_booking'],
                "nama"            => $row['nama_tamu'],
                "checkin_at"      => $now_label,     // label untuk tampilan (dengan detik)
                // "checkin_at_raw"=> $now_db,       // kalau mau kirim juga format DB
                "status"          => "checked_in",
                "petugas_checkin" => $petugas ?: null,
            ]
        ], 200);
    }



public function checkout_api(){
    $kode = trim((string)$this->input->post('kode', true));
    if ($kode === '') return $this->json_exit(["ok"=>false,"msg"=>"Kode kosong"], 400);

    $row = $this->db->get_where('booking_tamu', ['kode_booking'=>$kode])->row_array();
    if (!$row) return $this->json_exit(["ok"=>false,"msg"=>"Booking tidak ditemukan"], 404);

    if (empty($row['checkin_at']) && strtolower((string)$row['status']) !== 'checked_in') {
        return $this->json_exit(["ok"=>false,"msg"=>"Belum checked_in, tidak bisa checkout"], 409);
    }

    $detail_url = site_url('admin_scan/detail/'.$row['kode_booking']);

    // Nama petugas dari session
    $petugas = trim((string)($this->session->userdata('admin_nama') ?: $this->session->userdata('admin_username') ?: ''));

    // Idempotent: sudah checkout
    if (!empty($row['checkout_at']) || strtolower((string)$row['status']) === 'checked_out') {
        // Jika petugas_checkout kosong, isi sekarang (tanpa mengubah checkout_at/status)
        if ($petugas !== '' && empty($row['petugas_checkout'])) {
            $this->db->where('kode_booking', $kode)->update('booking_tamu', [
                'petugas_checkout' => $petugas
            ]);
            $row['petugas_checkout'] = $petugas;
        }

        return $this->json_exit([
            "ok"=>true, "msg"=>"Sudah checked_out", "already"=>true,
            "detail_url"=>$detail_url,
            "data"=>[
                "kode"              => $row['kode_booking'],
                "nama"              => $row['nama_tamu'],
                "checkout_at"       => $row['checkout_at'],
                "status"            => "checked_out",
                "petugas_checkout"  => $row['petugas_checkout'] ?? null,
            ]
        ], 200);
    }

    // Proses checkout baru
    $ts = time();

    $hari_id = [
      'Sun'=>'Minggu','Mon'=>'Senin','Tue'=>'Selasa',
      'Wed'=>'Rabu','Thu'=>'Kamis','Fri'=>'Jumat','Sat'=>'Sabtu'
  ];

    $now_db    = date('Y-m-d H:i:s', $ts); // simpan ke DB
    $now_label = date('H:i:s', $ts).' '.($hari_id[date('D',$ts)] ?? date('D',$ts)).', '.date('d-m-Y', $ts);

        // kalau mau dengan detik: ganti 'H:i' -> 'H:i:s'

    $this->db->where('kode_booking', $kode)->update('booking_tamu', [
      'checkout_at'      => $now_db,
      'status'           => 'checked_out',
      'token_revoked'    => 1,
      'petugas_checkout' => $petugas,
  ]);

    return $this->json_exit([
      "ok" => true,
      "msg" => "Checkout berhasil",
      "detail_url" => $detail_url,
      "data" => [
        "kode"             => $row['kode_booking'],
        "nama"             => $row['nama_tamu'],
            "checkout_at"      => $now_label,   // "HH:ii Hari, dd-mm-yyyy"
            "status"           => "checked_out",
            "petugas_checkout" => $petugas ?: null,
        ]
    ], 200);

}


    // Tambahkan ke class Admin_scan
   public function print_pdf($kode_booking)
{
    // Ambil data
    $row = $this->db->get_where('booking_tamu', ['kode_booking' => $kode_booking])->row();
    if (!$row) show_error('Booking tidak ditemukan.', 404);

    $data['booking'] = $row;
    // pastikan path view benar (admin_scan/booking_pdf)
    $html = $this->load->view('admin_scan/booking_pdf', $data, TRUE);

    $this->load->library('pdf'); // TCPDF wrapper

    // A5 Landscape = 210mm x 148mm => setengah A4 (tingginya setengah)
    $pdf = new Pdf('P', 'mm', 'F4', true, 'UTF-8', false);

    // Hilangkan header/footer default agar tidak makan ruang
    if (method_exists($pdf, 'setPrintHeader')) $pdf->setPrintHeader(false);
    if (method_exists($pdf, 'setPrintFooter')) $pdf->setPrintFooter(false);

    // Margin kecil dan tanpa auto page break agar tak “lompat” ke halaman 2
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(false, 6);

    $pdf->AddPage();
    $pdf->writeHTML($html, true, false, true, false, '');

    // tampilkan inline kalau dipanggil dengan ?inline=1
    $inline = (int)$this->input->get('inline');
    // $pdf->Output('Ticket_'.$kode_booking.'.pdf', $inline ? 'I' : 'D');
    $pdf->Output($filename, $dl ? 'D' : 'I');
}

public function pernyataan_pdf($kode_booking)
{
    // Ambil data
    $row = $this->db->get_where('booking_tamu', ['kode_booking' => $kode_booking])->row();
    if (!$row) show_error('Booking tidak ditemukan.', 404);

    $data['booking'] = $row;
    // pastikan path view benar (admin_scan/booking_pdf)
    $html = $this->load->view('admin_scan/pernyataan_pdf', $data, TRUE);

    $this->load->library('pdf'); // TCPDF wrapper

    // A5 Landscape = 210mm x 148mm => setengah A4 (tingginya setengah)
    $pdf = new Pdf('P', 'mm', 'F4', true, 'UTF-8', false);

    // Hilangkan header/footer default agar tidak makan ruang
    if (method_exists($pdf, 'setPrintHeader')) $pdf->setPrintHeader(false);
    if (method_exists($pdf, 'setPrintFooter')) $pdf->setPrintFooter(false);

    // Margin kecil dan tanpa auto page break agar tak “lompat” ke halaman 2
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(false, 6);

    $pdf->AddPage();
    $pdf->writeHTML($html, true, false, true, false, '');

    // tampilkan inline kalau dipanggil dengan ?inline=1
    $inline = (int)$this->input->get('inline');
    // $pdf->Output('Ticket_'.$kode_booking.'.pdf', $inline ? 'I' : 'D');
    $pdf->Output($filename, $dl ? 'D' : 'I');
}


	// Tambahkan ke class Admin_scan
public function upload_doc_photo()
{
    $kode = trim((string)$this->input->post('kode', true));
    $b64  = (string)$this->input->post('image', false); // base64 dataURL

    if ($kode === '' || $b64 === '') {
        return $this->json_exit(["ok"=>false, "msg"=>"Data tidak lengkap"], 400);
    }

    // validasi booking
    $row = $this->db->get_where('booking_tamu', ['kode_booking'=>$kode])->row_array();
    if (!$row) return $this->json_exit(["ok"=>false, "msg"=>"Booking tidak ditemukan"], 404);

    // decode dataURL
    if (preg_match('#^data:image/(png|jpe?g);base64,#i', $b64, $m)) {
        $ext = strtolower($m[1]) === 'jpeg' ? 'jpg' : strtolower($m[1]);
        $data = base64_decode(substr($b64, strpos($b64, ',')+1));
    } else {
        return $this->json_exit(["ok"=>false, "msg"=>"Format gambar tidak valid"], 400);
    }

    // simpan file
    $dir = FCPATH.'uploads/foto/';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $safeKode = preg_replace('/[^a-zA-Z0-9_\-]/','_', $kode);
    $fname = 'dok_'.$safeKode.'_'.date('Ymd_His').'_'.substr(md5(uniqid('', true)),0,6).'.'.$ext;
    $full  = $dir.$fname;
    if (file_put_contents($full, $data) === false) {
        return $this->json_exit(["ok"=>false, "msg"=>"Gagal menyimpan berkas"], 500);
    }

    // jika kolom foto ada, update (opsional)
    if ($this->db->field_exists('foto', 'booking_tamu')) {
        $this->db->where('kode_booking', $kode)->update('booking_tamu', ['foto'=>$fname]);
    }

    return $this->json_exit([
        "ok"=>true,
        "msg"=>"Foto tersimpan",
        "url"=> base_url('uploads/foto/'.$fname),
        "file"=>$fname
    ]);
}


}
