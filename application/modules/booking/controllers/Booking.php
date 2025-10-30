<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Booking extends MX_Controller {

    private $last_upload_error = null;

    public function __construct(){
        parent::__construct();
        $this->load->helper('front');
        $this->load->model('front_model','fm');
        $this->load->model('M_booking','mb');
    }

    /* ===== Small guard for dev/debug endpoints ===== */
    private function _ensure_dev_or_key(): void {
        $okEnv = (defined('ENVIRONMENT') && ENVIRONMENT === 'development');
        $key   = trim((string)$this->input->get('key', true));
        $cfg   = getenv('ADMIN_TASK_KEY') ?: 'changeme';
        $okKey = ($key !== '' && hash_equals($cfg, $key));
        if (!$okEnv && !$okKey) {
            show_404();
            exit;
        }
    }

    function ppt(){
        $this->load->view('ppt_view');
    }

    public function index()
    {
        $data["controller"] = get_class($this);
        $data["title"]      = "Booking Kunjungan Tamu";
        $data["deskripsi"]  = "Pemohon dapat memantau status permohonan secara real-time mulai dari pengajuan hingga selesai diproses. Fitur ini memudahkan pemantauan dan memastikan transparansi pelayanan";
        $data["prev"]       = base_url("assets/images/booking.png");

        // === ambil tree dari cache (auto bypass jika ?nocache=1) ===
        $data['units_tree'] = $this->mb->get_tree_cached();

        // kurangi I/O
        $data["rec"] = $this->fm->web_me();

        $this->load->view('booking_view', $data);
    }


    public function download_gate()
    {
        $t = trim((string)$this->input->get('t', true));
        $k = trim((string)$this->input->get('k', true));

        // Validasi minimal
        if ($t === '' || $k === '') return $this->_gate_error('Link tidak valid.');

        $row = $this->db->get_where('booking_tamu', [
            'kode_booking' => $k,
            'access_token' => $t
        ])->row();

        if (!$row)                         return $this->_gate_error('Link tidak valid.');
        if ((int)$row->token_revoked === 1)return $this->_gate_error('Token dicabut.');
        if (!empty($row->checkout_at))     return $this->_gate_error('Sudah checkout.');
        if (isset($row->status) && $row->status === 'expired') {
            return $this->_gate_error('Status sudah tidak berlaku (expired).');
        }

        $dl = site_url('booking/print_pdf/'.rawurlencode($k)).'?t='.urlencode($t).'&dl=1';
        $to = site_url('booking/booked?t='.urlencode($t)); 

        $data = [
            'controller' => get_class($this),
            'title'      => 'Mengunduh tiket…',
            'deskripsi'  => 'Menyiapkan unduhan…',
            'prev'       => base_url("assets/images/booking.png"),
            'dl'         => $dl,
            'to'         => $to,
            'rec'        => $this->fm->web_me(),
        ];

        // 200 OK tapi tetap noindex + no-cache
        $this->output
            ->set_header('X-Robots-Tag: noindex, nofollow, noarchive')
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0')
            ->set_header('Pragma: no-cache');

        $this->load->view('download_gate', $data);
    }

    private function _gate_error($msg)
    {
        $this->output
            ->set_status_header(410, 'Gone') // di halaman ERROR boleh 410
            ->set_header('X-Robots-Tag: noindex, nofollow, noarchive')
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0')
            ->set_header('Pragma: no-cache');

        $data = [
            'controller' => get_class($this),
            'title'      => 'Link Tidak Berlaku',
            'deskripsi'  => $msg,
            'prev'       => base_url("assets/images/booking.png"),
            'rec'        => $this->fm->web_me(),
        ];
        $this->load->view('booking_error', $data);
    }

    public function booked()
    {
        $token = $this->input->get('t', TRUE);
        if (!$token) { return $this->_booked_error("Link tidak valid."); }

        $booking = $this->db->get_where('booking_tamu', ['access_token' => $token])->row();
        if (!$booking) {
            return $this->_booked_error("Link tidak valid.");
        }
        if ((int)$booking->token_revoked === 1) {
            return $this->_booked_error("Link sudah tidak berlaku (token dicabut).");
        }
        if (!empty($booking->checkout_at)) {
            $wkt = date('d-m-Y H:i', strtotime($booking->checkout_at));
            return $this->_booked_error("Link tidak berlaku. Anda telah checkout pada {$wkt}.");
        }
        if (isset($booking->status) && $booking->status === 'expired') {
            $wkt = !empty($booking->expired_at)
                ? date('d-m-Y H:i', strtotime($booking->expired_at))
                : ($booking->tanggal.' '.$booking->jam);
            return $this->_booked_error("Link tidak berlaku. Status: Tidak Hadir (Expired) pada {$wkt}.");
        }

        // Noindex + no-cache untuk halaman detail berbasis token
        $this->output
            ->set_header('X-Robots-Tag: noindex, nofollow, noarchive')
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0')
            ->set_header('Pragma: no-cache');

        $data = [
            "controller" => get_class($this),
            "title"      => "Detail Booking",
            "deskripsi"  => "Detail",
            "booking"    => $booking,
            "rec"        => $this->fm->web_me(),
            "prev"       => base_url("assets/images/booking.png"),
        ];
        list($batas_edit, $batas_hari) = $this->_get_edit_limits();
        $data['batas_edit'] = $batas_edit;
        $data['batas_hari'] = $batas_hari;
        // $web = $this->db->select('batas_edit,batas_hari')->get('identitas')->row();
        // $data['batas_edit'] = isset($web->batas_edit) ? (int)$web->batas_edit : 1;
        // $data['batas_hari'] = isset($web->batas_hari) ? (int)$web->batas_hari : 2;


        $this->load->view('booking_detail', $data);
    }

    private function _booked_error($msg)
    {
        $this->output
            ->set_status_header(410, 'Gone') // atau 404
            ->set_header('X-Robots-Tag: noindex, nofollow, noarchive')
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0')
            ->set_header('Pragma: no-cache');

        $data = [
            "controller" => get_class($this),
            "rec"        => $this->fm->web_me(),
            "title"      => "Link Tidak Berlaku",
            "prev"       => base_url("assets/images/booking.png"),
            "deskripsi"  => $msg
        ];
        $this->load->view('booking_error', $data);
    }

    /** =========================
     *  SUBMIT BOOKING (AJAX)
     *  ========================= */
    public function add()
    {
        $data = $this->input->post(NULL, TRUE);
        $this->load->library('form_validation');

        // -- inisialisasi supaya selalu ada
        $surat_tugas = null;
        $foto        = null;

        $this->_set_rules();
        if ($this->form_validation->run() === FALSE) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode([
                    "success"=>false, "title"=>"Validasi Gagal", "pesan"=>validation_errors()
                ]));
        }
        $email = strtolower(trim($data['email'] ?? ''));

        // 1) Validasi tanggal & jam
        [$ok, $tanggal, $jam, $err] = $this->_validate_jadwal($data['tanggal'], $data['jam']);
        if (!$ok) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>$err]));
        }

        // 2) Cek batas pendamping per unit
        $unit_id    = (int)$data['unit_tujuan'];
        $dimintaRaw = $this->input->post('jumlah_pendamping', true);
        $diminta    = ($dimintaRaw === '' || $dimintaRaw === null) ? 0 : (int)$dimintaRaw;

        [$ok, $unit_nama, $err] = $this->_validate_pendamping($unit_id, $diminta);
        if (!$ok) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(["success"=>false,"title"=>"Melebihi Batas Pendamping","pesan"=>$err]));
        }
        // 2.b) Cek kuota harian (pre-check cepat)
        [$okQuota, $sisa, $terpakai, $kuota, $errQ] = $this->_validate_kuota_harian($unit_id, $tanggal, false);
        if (!$okQuota) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(["success"=>false,"title"=>"Kuota Penuh","pesan"=>$errQ]));
        }

        // 3) Instansi asal
        $kategori   = strtolower((string)$this->input->post('kategori', true));
        $instansiId = (int)$this->input->post('instansi_id', true);
        if ($kategori === 'lainnya') {
            $instansiNama = trim((string)$this->input->post('target_instansi_nama', true));
            if ($instansiNama === '' || mb_strlen($instansiNama) < 3) {
                return $this->output->set_content_type('application/json')
                    ->set_output(json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>"Nama instansi (Lainnya) minimal 3 karakter."]));
            }
            $instansiId = null;
        } else {
            [$ok, $instansiNama, $err] = $this->_resolve_instansi_asal($kategori, $instansiId);
            if (!$ok) {
                return $this->output->set_content_type('application/json')
                    ->set_output(json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>$err]));
            }
        }

        // 4) Token akses
        $access_token = bin2hex(random_bytes(24));

        // 5) Upload opsional
        $no_file = function(string $field){
            return empty($_FILES[$field]) ||
                   (isset($_FILES[$field]['error']) && $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) ||
                   (isset($_FILES[$field]['name']) && $_FILES[$field]['name'] === '');
        };

        if (!$no_file('surat_tugas')) {
            $tmp = $this->_upload('surat_tugas', './uploads/surat_tugas/', 'pdf|jpg|jpeg|png', 2048, 'surat_tugas');
            if ($tmp === null) {
                $msg = $this->get_last_upload_error();
                if ($msg && stripos($msg, 'did not select a file') === false) {
                    return $this->output->set_content_type('application/json')
                        ->set_output(json_encode(["success"=>false,"title"=>"Upload Gagal","pesan"=>$msg ?: "Surat tugas melebihi 2MB."]));
                }
            } else { $surat_tugas = $tmp; }
        }

        if (!$no_file('foto')) {
            $tmp = $this->_upload('foto', './uploads/foto/', 'jpg|jpeg|png', 1536, 'foto');
            if ($tmp === null) {
                $msg = $this->get_last_upload_error();
                if ($msg && stripos($msg, 'did not select a file') === false) {
                    return $this->output->set_content_type('application/json')
                        ->set_output(json_encode(["success"=>false,"title"=>"Upload Gagal","pesan"=>$msg ?: "Foto melebihi 1.5MB."]));
                }
            } else { $foto = $tmp; }
        }

        // 5.x) default nama petugas instansi dari unit_tujuan
        $nama_petugas_instansi = null;
        $unit = $this->db->select('nama_pejabat, nama_unit')
            ->from('unit_tujuan')->where('id', $unit_id)->limit(1)->get()->row();
        if ($unit && !empty($unit->nama_pejabat)) $nama_petugas_instansi = trim($unit->nama_pejabat);

        // ------- DATA PENDAMPING (validasi JSON) -------
        $pendampingRows  = [];
        $pendamping_json = $this->input->post('pendamping_json', true);
        $nik_tamu        = preg_replace('/\D+/', '', (string)$this->input->post('nik', true));

        if ($pendamping_json !== null && $pendamping_json !== '') {
            $arr = json_decode($pendamping_json, true);
            if (!is_array($arr)) {
                return $this->output->set_content_type('application/json')
                    ->set_output(json_encode(["success"=>false,"title"=>"Validasi Pendamping","pesan"=>"Format data pendamping tidak valid."]));
            }
            $seen = [];
            foreach ($arr as $i => $p) {
                $rawId = (string)($p['nik'] ?? '');              // FE kirim di key 'nik' (isi bisa NIK/NIP/NRP)
                $id    = preg_replace('/\D+/', '', $rawId);      // ambil hanya digit
                $nama  = trim((string)($p['nama'] ?? ''));

                if ($id === '' && $nama === '') continue;

                if ($nama === '') {
                    return $this->output->set_content_type('application/json')
                        ->set_output(json_encode([
                            "success"=>false,
                            "title"  =>"Validasi Pendamping",
                            "pesan"  =>"Nama pendamping #".($i+1)." wajib diisi."
                        ]));
                }

                // NRP 8–9 / NIK 16 / NIP 18 atau 9
                if ($id === '' || !preg_match('/^(?:\d{8,9}|\d{16}|\d{18})$/', $id)) {
                    return $this->output->set_content_type('application/json')
                        ->set_output(json_encode([
                            "success"=>false,
                            "title"  =>"Validasi Pendamping",
                            "pesan"  =>"ID pendamping #".($i+1)." tidak valid. Isi salah satu: NIK 16 digit, NIP 18/9 digit, atau NRP 8–9 digit."
                        ]));
                }

                $nik_tamu_digits = preg_replace('/\D+/', '', (string)$nik_tamu);
                if ($nik_tamu_digits && $id === $nik_tamu_digits) {
                    return $this->output->set_content_type('application/json')
                        ->set_output(json_encode([
                            "success"=>false,
                            "title"  =>"Validasi Pendamping",
                            "pesan"  =>"ID pendamping #".($i+1)." tidak boleh sama dengan ID tamu."
                        ]));
                }

                if (isset($seen[$id])) {
                    return $this->output->set_content_type('application/json')
                        ->set_output(json_encode([
                            "success"=>false,
                            "title"  =>"Validasi Pendamping",
                            "pesan"  =>"ID pendamping #".($i+1)." duplikat."
                        ]));
                }
                $seen[$id] = true;

                $pendampingRows[] = [
                    'nik'  => $id,
                    'nama' => $nama,
                ];
            }
        }
        if ($diminta > 0 && count($pendampingRows) !== $diminta) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode([
                    "success"=>false,"title"=>"Validasi Pendamping",
                    "pesan"=>"Jumlah pendamping yang diisi (".count($pendampingRows).") tidak sama dengan jumlah pendamping ($diminta)."
                ]));
        }
        // ------- END PENDAMPING -------

        // ===== INSERT dengan proteksi duplikasi kode_booking =====
        $insert_base = [
            "nama_tamu"             => $data['nama_tamu'],
            "jabatan"               => $data['jabatan'],
            "nik"                   => $data['nik'],
            "no_hp"                 => $data['no_hp'],
            "alamat"                => $data['alamat'],
            "instansi"              => $data['instansi'] ?? null,
            "unit_tujuan"           => $unit_id,
            "target_kategori"       => $kategori,
            "target_instansi_id"    => $instansiId,
            "target_instansi_nama"  => $instansiNama,
            "nama_petugas_instansi" => $nama_petugas_instansi,
            "keperluan"             => $data['keperluan'] ?? null,
            "surat_tugas"           => $surat_tugas,
            "jumlah_pendamping"     => $diminta,
            "tanggal"               => $tanggal,
            "jam"                   => $jam,
            "foto"                  => $foto,
            "tanggal_lahir"         => $this->normalize_date_mysql($data['tanggal_lahir'] ?? ''),
            "tempat_lahir"          => $data["tempat_lahir"] ?? null,
            "status"                => "approved", // atur sesuai kebijakanmu
            "access_token"          => $access_token,
            "token_issued_at"       => date('Y-m-d H:i:s'),
            "token_revoked"         => 0
        ];
        if ($this->db->field_exists('email','booking_tamu')) {
            $insert_base['email'] = $email ?: null;
        }


        $MAX_TRY = 7;
        $lastErr = null;
        for ($try=1; $try<=$MAX_TRY; $try++) {

            $kode_booking = $this->_make_kode_booking(); // pastikan ada UNIQUE index di kolom ini
            $insert = $insert_base;
            $insert["kode_booking"] = $kode_booking;

            $this->db->trans_begin();
            // Re-check kuota di dalam transaksi + lock baris unit (FOR UPDATE)
            [$okQuota2, $sisa2, $terpakai2, $kuota2, $errQ2] = $this->_validate_kuota_harian($unit_id, $tanggal, true);
            if (!$okQuota2) {
                $this->db->trans_rollback();
                return $this->output->set_content_type('application/json')
                    ->set_output(json_encode(["success"=>false,"title"=>"Kuota Penuh","pesan"=>$errQ2]));
            }

            $okMain = $this->db->insert("booking_tamu", $insert);
            if (!$okMain) {
                $err = $this->db->error();
                $this->db->trans_rollback();

                if ((int)$err['code'] === 1062 && stripos($err['message'], 'kode_booking') !== false) {
                    $lastErr = $err;
                    continue;
                }

                return $this->output->set_content_type('application/json')
                    ->set_output(json_encode([
                        "success"=>false,"title"=>"Gagal Menyimpan",
                        "pesan"=>"Terjadi kendala saat menyimpan data booking. [DB-{$err['code']}]"
                    ]));
            }

            // Insert pendamping (jika ada)
            if (!empty($pendampingRows)) {
                $values = [];
                foreach ($pendampingRows as $r) {
                    $values[] = $this->db->escape($kode_booking).','.$this->db->escape($r['nik']).','.$this->db->escape($r['nama']);
                }
                $sql = "INSERT INTO booking_pendamping (kode_booking, nik, nama) VALUES ".
                       '('.implode('),(', $values).') '.
                       "ON DUPLICATE KEY UPDATE nama = VALUES(nama)";
                $okPend = $this->db->query($sql);
                if (!$okPend) {
                    $err = $this->db->error();
                    $this->db->trans_rollback();
                    return $this->output->set_content_type('application/json')
                        ->set_output(json_encode([
                            "success"=>false,"title"=>"Gagal Menyimpan Pendamping",
                            "pesan"=>"Silakan ulangi pengisian pendamping. [DB-{$err['code']}]"
                        ]));
                }
            }

            $this->db->trans_commit();

            // Berhasil!
            $qr_url       = $this->_make_qr($kode_booking);
            $redirect_url = site_url('booking/booked?t='.urlencode($access_token));

            return $this->output->set_content_type('application/json')
                ->set_output(json_encode([
                    "success"      => true,
                    "title"        => "Booking Berhasil",
                    "pesan"        => "Kunjungan berhasil didaftarkan.<br>Kode Booking: <b>".$kode_booking."</b>",
                    "kode_booking" => $kode_booking,
                    "qr_url"       => $qr_url,
                    "redirect_url" => $redirect_url
                ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
        }

        // Sampai sini artinya 7x percobaan masih bentrok (hampir mustahil)
        return $this->output->set_content_type('application/json')
            ->set_output(json_encode([
                "success"=>false,"title"=>"Gagal",
                "pesan"=>"Gagal membuat kode booking unik. Silakan coba lagi."
            ]));
    }

    /** =========================
     *  API Pendamping (opsional)
     *  ========================= */

    // GET /booking/pendamping_list/{kode}
    public function pendamping_list($kode_booking){
        $rows = $this->db->order_by('id_pendamping','ASC')
            ->get_where('booking_pendamping', ['kode_booking' => $kode_booking])->result_array();
        return $this->output->set_content_type('application/json')
            ->set_output(json_encode(["ok"=>true,"data"=>$rows]));
    }

    // POST: kode_booking, nik(8-9/16/18), nama, t(access_token) [wajib]
    public function pendamping_add(){
        $kode  = trim((string)$this->input->post('kode_booking', true));
        $nik   = preg_replace('/\D/','', (string)$this->input->post('nik', true));
        $nama  = trim((string)$this->input->post('nama', true));
        $token = trim((string)$this->input->post('t', true));

        if ($kode==='' || $nama===''
            || !preg_match('/^(?:\d{8,9}|\d{16}|\d{18})$/', $nik)
            || $token==='') {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(["ok"=>false,"msg"=>"Data tidak lengkap/valid."]));
        }

        $exists = $this->db->select('kode_booking')->limit(1)
            ->get_where('booking_tamu', ['kode_booking'=>$kode,'access_token'=>$token,'token_revoked'=>0])->row();
        if (!$exists) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(["ok"=>false,"msg"=>"Unauthorized atau booking tidak ditemukan."]));
        }

        $sql = "INSERT INTO booking_pendamping (kode_booking, nik, nama)
                VALUES (?,?,?)
                ON DUPLICATE KEY UPDATE nama=VALUES(nama)";
        $this->db->query($sql, [$kode,$nik,$nama]);

        return $this->output->set_content_type('application/json')
            ->set_output(json_encode(["ok"=>true,"msg"=>"Pendamping disimpan."]));
    }

    // POST: id_pendamping, t(access_token) [wajib]
    public function pendamping_delete(){
        $id    = (int)$this->input->post('id_pendamping', true);
        $token = trim((string)$this->input->post('t', true));
        if ($id<=0 || $token==='') {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(["ok"=>false,"msg"=>"Data tidak lengkap."]));
        }

        // Pastikan id milik booking dengan token ini
        $kode = $this->db->select('b.kode_booking')
            ->from('booking_tamu b')
            ->join('booking_pendamping p','p.kode_booking=b.kode_booking','inner')
            ->where('p.id_pendamping', $id)
            ->where('b.access_token', $token)
            ->where('b.token_revoked', 0)
            ->limit(1)->get()->row('kode_booking');

        if (!$kode) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(["ok"=>false,"msg"=>"Unauthorized."]));
        }

        $this->db->delete('booking_pendamping', ['id_pendamping'=>$id]);
        return $this->output->set_content_type('application/json')
            ->set_output(json_encode(["ok"=>true,"deleted"=>$this->db->affected_rows()>0]));
    }

    /** =========================
     *  WA & Util
     *  ========================= */

    private function normalize_date_mysql(?string $s): ?string {
        if (!$s) return null;
        $s = trim($s);
        $s = str_replace(['/', '.', ' '], '-', $s);

        $formats = ['Y-m-d','d-m-Y','m-d-Y','d-m-y'];

        foreach ($formats as $fmt) {
            $dt = DateTime::createFromFormat($fmt, $s);
            $errors = DateTime::getLastErrors();
            if ($dt && empty($errors['warning_count']) && empty($errors['error_count'])) {
                if ($dt->format($fmt) === $s) {
                    return $dt->format('Y-m-d');
                }
            }
        }
        return null;
    }

    public function dev_quick_booking()
    {
        $this->_ensure_dev_or_key();

        // ambil unit_tujuan valid
        $unit = (int)($this->db->select('id')->order_by('id','ASC')->limit(1)->get('unit_tujuan')->row('id') ?? 1);

        $kode  = 'BK'.date('ymd').strtoupper(bin2hex(random_bytes(3)));
        $token = bin2hex(random_bytes(16));

        $data = [
            'kode_booking'         => $kode,
            'access_token'         => $token,
            'status'               => 'approved',
            'nama_tamu'            => 'Tester Dev',
            'no_hp'                => '082333265888',
            'keperluan'            => 'Uji fitur',
            'unit_tujuan'          => $unit,
            'target_instansi_nama' => 'Instansi Dev',
            'tanggal'              => date('Y-m-d'),
            'jam'                  => date('H:i', time()+3600),
            'token_revoked'        => 0,
            'create_date'          => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('booking_tamu', $data);

        $urls = [
            'detail' => site_url('booking/booked?t='.urlencode($token)),
            'pdf'    => site_url('booking/print_pdf/'.rawurlencode($kode)).'?t='.urlencode($token).'&dl=1',
            'wa'     => site_url('booking/wa_notify?t='.urlencode($token).'&debug=1&key='.urlencode(getenv('ADMIN_TASK_KEY') ?: 'changeme').'&force=1'),
        ];

        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['ok'=>true,'booking'=>$data,'urls'=>$urls], JSON_UNESCAPED_SLASHES));
    }

    public function wa_notify()
    {
        $token = trim((string)($this->input->post('t', TRUE) ?: $this->input->get('t', TRUE)));
        $kode  = trim((string)$this->input->get('k', TRUE));   // dukung kode_booking
        $debug = (int)$this->input->get('debug', TRUE) === 1;
        $force = (int)$this->input->get('force', TRUE) === 1;  // paksa kirim (abaikan stamp)
        $log   = [];

        if ($debug) { $this->_ensure_dev_or_key(); }

        // Ambil booking by token atau kode
        if ($token !== '') {
            $b = $this->db->get_where('booking_tamu', ['access_token' => $token])->row();
        } elseif ($kode !== '') {
            $b = $this->db->get_where('booking_tamu', ['kode_booking' => $kode])->row();
        } else {
            return $this->json_exit(['ok'=>false,'err'=>'missing token or kode (pakai ?t=token atau ?k=kode_booking)'], 422);
        }

        if (!$b) {
            if ($debug) {
                $last = $this->db->select('kode_booking, tanggal, jam', false)
                    ->order_by('id','DESC')->limit(5)->get('booking_tamu')->result_array();
                return $this->json_exit([
                    'ok'        => false,
                    'err'       => 'not found',
                    'hint'      => 'pakai ?t=access_token atau ?k=kode_booking',
                    'peek_last' => $last
                ], 404);
            }
            return $this->json_exit(['ok'=>false,'err'=>'not found'], 404);
        }
        if ((int)$b->token_revoked === 1)    return $this->json_exit(['ok'=>true,'skip'=>'token revoked']);
        if (!empty($b->checkout_at))         return $this->json_exit(['ok'=>true,'skip'=>'already checkout']);

        // ===== Data bantu: ambil unit tujuan (beserta no_hp) =====
        $unit_row = $this->db
            ->select('id, nama_unit, nama_pejabat, parent_id, no_hp', false)
            ->get_where('unit_tujuan', ['id' => (int)$b->unit_tujuan])
            ->row();

        $unit_nama_db     = $unit_row->nama_unit    ?? '-';
        $unit_pejabat_db  = $unit_row->nama_pejabat ?? '';
        $parent_id        = $unit_row->parent_id    ?? null;

        $hp_unit = $this->_extract_phone_from_row($unit_row);
        if (!$hp_unit) {
            list($hp_unit_fb, $nm_fb, $pej_fb) = $this->_get_unit_contact((int)$b->unit_tujuan);
            if ($hp_unit_fb) {
                $hp_unit        = $hp_unit_fb;
                $unit_nama_db   = $unit_nama_db ?: ($nm_fb ?: '-');
                $unit_pejabat_db= $unit_pejabat_db ?: ($pej_fb ?: '');
            }
        }

        $qr_url   = base_url('uploads/qr/qr_'.$b->kode_booking.'.png');
        $redir    = site_url('booking/booked?t='.urlencode($b->access_token));
        $pdf_url  = site_url('booking/download_gate').'?k='.rawurlencode($b->kode_booking).'&t='.urlencode($b->access_token);
        $instansi = $b->target_instansi_nama ?: ($b->instansi ?: '-');

        // ===== 1) Kirim ke TAMU =====
        $can_stamp_user = $this->db->field_exists('wa_sent_at', 'booking_tamu');
        $already_user   = $can_stamp_user && !empty($b->wa_sent_at);
        if ($force || !$already_user) {
            $ok_user = $this->_send_wa_konfirmasi($b->no_hp, [
                'access_token'           => $b->access_token,
                'nama'                   => $b->nama_tamu,
                'nama_petugas_instansi'  => $b->nama_petugas_instansi,
                'kode'                   => $b->kode_booking,
                'instansi_asal'          => $instansi,
                'unit_tujuan'            => $unit_nama_db ?: '-',
                'tanggal'                => $b->tanggal,
                'jam'                    => $b->jam,
                'qr_url'                 => $qr_url,
                'redirect_url'           => $redir,
                'pdf_url'                => $pdf_url,
                'keperluan'              => $b->keperluan ?: '-',
            ]);
            $log[] = ['send_user'=>['ok'=>$ok_user]];
            if ($ok_user && $can_stamp_user) {
                if ($token !== '') { $this->db->where('access_token', $token); }
                else               { $this->db->where('kode_booking', $b->kode_booking); }
                $this->db->where('wa_sent_at IS NULL', NULL, FALSE)
                    ->update('booking_tamu', ['wa_sent_at' => date('Y-m-d H:i:s')]);
            }
        } else {
            $log[] = ['send_user'=>'skipped: already stamped'];
        }

        // ===== Kirim EMAIL ke TAMU (jika ada)
        // ===== Kirim EMAIL ke TAMU (idempotent + race-safe) =====
        if (!empty($b->email) && filter_var($b->email, FILTER_VALIDATE_EMAIL)) {
            $hasSentCol = $this->db->field_exists('email_sent_at','booking_tamu');

            // 1) Atomic claim: hanya satu proses yang dapat '1'
            $claimed = 0;
            if ($hasSentCol && !$force) {
                $this->db->query(
                    "UPDATE booking_tamu
                     SET email_sent_at = NOW()
                     WHERE kode_booking=? AND access_token=? AND email_sent_at IS NULL",
                    [$b->kode_booking, $b->access_token]
                );
                $claimed = $this->db->affected_rows(); // 1 = kita pemilik kirim; 0 = sudah diklaim/terkirim
            }

            if ($force || $claimed > 0) {
                $ok_mail = $this->_send_email_konfirmasi($b->email, [
                    'access_token'          => $b->access_token,
                    'is_update'             => false,
                    'nama'                  => $b->nama_tamu,
                    'nama_petugas_instansi' => $b->nama_petugas_instansi,
                    'kode'                  => $b->kode_booking,
                    'instansi_asal'         => $instansi,
                    'unit_tujuan'           => $unit_nama_db ?: '-',
                    'tanggal'               => $b->tanggal,
                    'jam'                   => $b->jam,
                    'qr_url'                => $qr_url,
                    'redirect_url'          => $redir,
                    'pdf_url'               => $pdf_url,
                    'keperluan'             => $b->keperluan ?: '-',
                ]);
                $log[] = ['send_email_user'=>['ok'=>$ok_mail, 'forced'=>$force, 'claimed'=>$claimed]];

                // 2) Jika gagal dan ini hasil klaim kita (bukan force), kembalikan ke NULL agar bisa retry biasa
                if (!$ok_mail && !$force && $claimed > 0 && $hasSentCol) {
                    $this->db->query(
                        "UPDATE booking_tamu
                         SET email_sent_at = NULL
                         WHERE kode_booking=? AND access_token=?",
                        [$b->kode_booking, $b->access_token]
                    );
                }

                // 3) Jika force, pastikan tetap distamp agar tidak resend tanpa sengaja
                if ($force && $hasSentCol) {
                    $this->db->query(
                        "UPDATE booking_tamu
                         SET email_sent_at = COALESCE(email_sent_at, NOW())
                         WHERE kode_booking=? AND access_token=?",
                        [$b->kode_booking, $b->access_token]
                    );
                }
            } else {
                $log[] = ['send_email_user'=>'skipped: already stamped'];
            }
        } else {
            $log[] = ['send_email_user'=>'skipped: no email'];
        }




        // ===== 2) Kirim ke UNIT TUJUAN =====
        if (!empty($hp_unit)) {
            $can_stamp_unit = $this->db->field_exists('wa_unit_sent_at', 'booking_tamu');
            $already_unit   = $can_stamp_unit && !empty($b->wa_unit_sent_at);
            if ($force || !$already_unit) {
                $ok_unit = $this->_send_wa_info_unit($hp_unit, [
                    'kode'            => $b->kode_booking,
                    'nama'            => $b->nama_tamu,
                    'instansi_asal'   => $instansi,
                    'hp_tamu'         => $b->no_hp,
                    'unit_nama'       => $unit_nama_db,
                    'unit_pejabat'    => $unit_pejabat_db,
                    'child_unit_nama' => $unit_nama_db,
                    'tanggal'         => $b->tanggal,
                    'jam'             => $b->jam,
                    'pendamping'      => (int)$b->jumlah_pendamping,
                    'keperluan'       => $b->keperluan ?: '-',
                    'redirect_url'    => $redir,
                    'qr_url'          => $qr_url,
                    'is_cc'           => false,
                ]);
                $log[] = ['send_unit'=>['to'=>$hp_unit,'ok'=>$ok_unit]];
                if ($ok_unit && $can_stamp_unit) {
                    if ($token !== '') { $this->db->where('access_token', $token); }
                    else               { $this->db->where('kode_booking', $b->kode_booking); }
                    $this->db->where('wa_unit_sent_at IS NULL', NULL, FALSE)
                        ->update('booking_tamu', ['wa_unit_sent_at' => date('Y-m-d H:i:s')]);
                }
            } else {
                $log[] = ['send_unit'=>'skipped: already stamped'];
            }
        } else {
            $log[] = ['send_unit'=>'skipped: no phone','dbg_from'=>$unit_row->no_hp ?? null];
        }

        // ===== 3) TEMBUSAN ke UNIT INDUK (parent_id) =====
        if (!empty($parent_id)) {
            $parent_row = $this->db
                ->select('id, nama_unit, nama_pejabat, no_hp', false)
                ->get_where('unit_tujuan', ['id' => (int)$parent_id])->row();

            $hp_parent = $this->_extract_phone_from_row($parent_row);
            if (!$hp_parent && $parent_row) {
                list($hp_parent_fb, $nm_parent_fb, $pej_parent_fb) = $this->_get_unit_contact((int)$parent_row->id);
                if ($hp_parent_fb) {
                    $hp_parent               = $hp_parent_fb;
                    $parent_row->nama_unit   = $parent_row->nama_unit   ?: $nm_parent_fb;
                    $parent_row->nama_pejabat= $parent_row->nama_pejabat?: $pej_parent_fb;
                }
            }

            if (!empty($hp_parent)) {
                $can_stamp_parent = $this->db->field_exists('wa_parent_sent_at', 'booking_tamu');
                $already_parent   = $can_stamp_parent && !empty($b->wa_parent_sent_at);

                $allow_duplicate_cc = true;
                $same_number = isset($hp_unit) &&
                    (preg_replace('/\D+/','',$hp_unit) === preg_replace('/\D+/','',$hp_parent));

                if ($force || (($allow_duplicate_cc || !$same_number) && !$already_parent)) {
                    $ok_cc = $this->_send_wa_info_unit($hp_parent, [
                        'kode'            => $b->kode_booking,
                        'nama'            => $b->nama_tamu,
                        'hp_tamu'         => $b->no_hp,
                        'instansi_asal'   => $instansi,
                        'unit_nama'       => $parent_row->nama_unit ?? '-',
                        'unit_pejabat'    => $parent_row->nama_pejabat ?? '',
                        'child_unit_nama' => $unit_nama_db,
                        'tanggal'         => $b->tanggal,
                        'jam'             => $b->jam,
                        'pendamping'      => (int)$b->jumlah_pendamping,
                        'keperluan'       => $b->keperluan ?: '-',
                        'redirect_url'    => $redir,
                        'qr_url'          => $qr_url,
                        'is_cc'           => true,
                    ]);
                    $log[] = ['send_parent'=>['to'=>$hp_parent,'ok'=>$ok_cc]];

                    if ($ok_cc && $can_stamp_parent) {
                        if ($token !== '') { $this->db->where('access_token', $token); }
                        else               { $this->db->where('kode_booking', $b->kode_booking); }
                        $this->db->where('wa_parent_sent_at IS NULL', NULL, FALSE)
                            ->update('booking_tamu', ['wa_parent_sent_at' => date('Y-m-d H:i:s')]);
                    }
                } else {
                    $log[] = ['send_parent'=>'skipped: already stamped or same-number'];
                }
            } else {
                $log[] = ['send_parent'=>'skipped: no phone'];
            }
        } else {
            $log[] = ['send_parent'=>'skipped: no parent'];
        }

        if ($debug) return $this->json_exit(['ok'=>true,'debug'=>$log], 200);
        return $this->json_exit(['ok'=>true]);
    }

    /**
     * Ambil nomor WA & info unit.
     * Return: [hp, nama_unit, nama_pejabat]
     */
    private function _get_unit_contact(int $unit_id): array
    {
        $u = $this->db->get_where('unit_tujuan', ['id'=>$unit_id])->row();
        if (!$u) return [null,null,null];

        $hp = null;
        foreach (['no_hp','wa','whatsapp','wa_number','no_wa','no_whatsapp','hp','telepon','telp','phone'] as $col) {
            if (isset($u->$col) && trim($u->$col) !== '') { $hp = $u->$col; break; }
        }
        if ($hp && method_exists($this, '_normalize_msisdn_id')) {
            $hp = $this->_normalize_msisdn_id($hp);
        }

        return [$hp, ($u->nama_unit ?? null), ($u->nama_pejabat ?? null)];
    }

    /** Normalisasi MSISDN Indonesia → 628xxxxxxxx */
    private function _normalize_msisdn_id(string $msisdn): string
    {
        $d = preg_replace('/\D+/', '', $msisdn);
        if ($d === '') return $d;
        if (strpos($d, '62') === 0) return $d;
        if ($d[0] === '0') return '62'.substr($d,1);
        if ($d[0] === '8') return '62'.$d;
        return $d;
    }

    private function _extract_phone_from_row($row): ?string
    {
        if (!$row) return null;
        foreach (['no_hp','wa','whatsapp','wa_number','no_wa','no_whatsapp','hp','telepon','telp','phone'] as $col) {
            if (isset($row->$col) && trim((string)$row->$col) !== '') {
                $num = trim((string)$row->$col);
                if (method_exists($this, '_normalize_msisdn_id')) {
                    $num = $this->_normalize_msisdn_id($num);
                }
                return $num;
            }
        }
        return null;
    }

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

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            @ob_end_flush(); @flush();
        }
    }

    private function _json(array $payload, int $status = 200)
    {
        return $this->output
            ->set_status_header($status)
            ->set_content_type('application/json; charset=utf-8')
            ->set_output(json_encode($payload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    }

    /** =========================
     *  RULES & VALIDATORS
     *  ========================= */

    private function _set_rules(){
        $kat = strtolower((string)$this->input->post('kategori', true));

        // Identitas tamu
        $this->form_validation->set_rules('nama_tamu','Nama Tamu','required|trim|min_length[3]');
        $this->form_validation->set_rules('jabatan','Jabatan','required|trim');
        $this->form_validation->set_rules('tempat_lahir','Tempat Lahir','required|trim');
        $this->form_validation->set_rules('alamat','Alamat','required|trim');
        $this->form_validation->set_rules('tanggal_lahir','Tanggal Lahir','required|trim');
        // $this->form_validation->set_rules('email','Email','required|trim|valid_email');


        // NIK 16 | NIP 18/9 | NRP 8–9 digit
        $this->form_validation->set_rules(
            'nik',
            'NIK/NIP/NRP',
            'required|trim|regex_match[/^(?:\d{16}|\d{18}|\d{8,9})$/]',
            ['regex_match' => '* %s harus: NIK 16 digit, NIP 18/9 digit, atau NRP 8–9 digit (angka saja)']
        );

        // No HP 10–13 digit
        $this->form_validation->set_rules(
            'no_hp','No HP',
            'required|trim|regex_match[/^[0-9]{10,13}$/]',
            ['regex_match' => '* %s harus 10–13 digit angka']
        );

        // Tujuan internal Lapas
        $this->form_validation->set_rules('unit_tujuan','Unit Tujuan','required');

        // Kategori asal + instansi
        $this->form_validation->set_rules(
            'kategori','Kategori Asal',
            'required|in_list[opd,pn,pa,ptun,kejati,kejari,cabjari,bnn,kodim,kepolisian,lainnya]'
        );

        if ($kat === 'lainnya') {
            $this->form_validation->set_rules(
                'target_instansi_nama','Nama Instansi',
                'required|trim|min_length[3]|max_length[255]'
            );
            $this->form_validation->set_rules('instansi_id','Instansi Asal','integer');
        } else {
            $this->form_validation->set_rules('instansi_id','Instansi Asal','required|integer');
            $this->form_validation->set_rules('target_instansi_nama','Nama Instansi','trim|max_length[255]');
        }

        // Jadwal & pendamping
        $this->form_validation->set_rules('tanggal','Tanggal','required');
        $this->form_validation->set_rules('jam','Jam','required');
        $this->form_validation->set_rules(
            'jumlah_pendamping','Jumlah Pendamping',
            'trim|regex_match[/^\d*$/]',
            ['regex_match' => '* %s hanya boleh berisi angka']
        );

        // Pesan error
        $this->form_validation->set_message('required',     '* %s Harus diisi');
        $this->form_validation->set_message('integer',      '* %s harus berupa angka bulat');
        $this->form_validation->set_message('is_natural',   '* %s harus berupa angka >= 0');
        $this->form_validation->set_message('min_length',   '* %s minimal %s karakter');
        $this->form_validation->set_message('max_length',   '* %s maksimal %s karakter');
        $this->form_validation->set_message('in_list',      '* %s tidak valid');

        $this->form_validation->set_error_delimiters('<br> ', ' ');
    }

    private function _validate_jadwal($tanggal, $jam_raw)
    {
        $errors  = [];
        $tanggal = trim((string)$tanggal);
        $jam_raw = trim((string)$jam_raw);
        $jam     = null;

        $web = $this->fm->web_me();

        // Zona waktu dari DB (fallback ke default PHP bila tidak valid)
        $tzName = !empty($web->waktu) ? (string)$web->waktu : date_default_timezone_get();
        try {
            $tz = new DateTimeZone($tzName);
        } catch (\Exception $e) {
            $tz = new DateTimeZone(date_default_timezone_get());
        }

        // Lead time (0..1440, sama dengan front-end)
        $min_lead_minutes = (int)($web->min_lead_minutes ?? 0);
        $min_lead_minutes = max(0, min(1440, $min_lead_minutes));

        // --- Validasi tanggal (format & tidak boleh mundur) ---
        $dt = DateTime::createFromFormat('Y-m-d', $tanggal, $tz);
        if (!$dt || $dt->format('Y-m-d') !== $tanggal) {
            $errors[] = '* Tanggal tidak valid (format harus YYYY-MM-DD).';
        } else {
            $nowTz = new DateTimeImmutable('now', $tz);
            $today = $nowTz->format('Y-m-d');
            if ($tanggal < $today) {
                $errors[] = '* Tanggal tidak boleh mundur (minimal hari ini).';
            }

            $hari = (int)$dt->format('w'); // 0=Min..6=Sab

            // --- Validasi jam (HH:MM atau HH:MM:SS) ---
            if ($jam_raw === '') {
                $errors[] = '* Jam harus diisi.';
            } else {
                $jam_norm = str_replace('.', ':', $jam_raw);
                $jam_norm = preg_replace('/\s+/', '', $jam_norm);

                if (preg_match('/^\d{1,2}:[0-5]\d:[0-5]\d$/', $jam_norm)) {
                    $jam_norm = substr($jam_norm, 0, 5);
                }

                if (!preg_match('/^(?:[01]?\d|2[0-3]):[0-5]\d$/', $jam_norm)) {
                    $errors[] = '* Format jam tidak valid (pakai HH:MM, contoh 16:55).';
                } else {
                    list($hh, $mm) = explode(':', $jam_norm);
                    $hh    = (int)$hh;
                    $mm    = (int)$mm;
                    $jam   = sprintf('%02d:%02d', $hh, $mm);
                    $menit = $hh * 60 + $mm;

                    // === Jam operasional dinamis (per-hari + istirahat) ===
                    $normHHMM = function ($v) {
                        $v = trim((string)$v);
                        if ($v === '') return null;
                        $v = str_replace('.', ':', $v);
                        if (!preg_match('/^(\d{1,2}):([0-5]\d)$/', $v, $m)) return null;
                        $h = max(0, min(23, (int)$m[1]));
                        $i = (int)$m[2];
                        return sprintf('%02d:%02d', $h, $i);
                    };
                    $toMin = function ($hhmm) {
                        if ($hhmm === null) return null;
                        [$h, $i] = array_map('intval', explode(':', $hhmm));
                        return $h * 60 + $i;
                    };

                    $kmap = ['0' => 'sun', '1' => 'mon', '2' => 'tue', '3' => 'wed', '4' => 'thu', '5' => 'fri', '6' => 'sat'];
                    $k    = $kmap[(string)$hari];

                    $cfg = [
                        'open'        => $normHHMM($web->{"op_{$k}_open"}),
                        'break_start' => $normHHMM($web->{"op_{$k}_break_start"}),
                        'break_end'   => $normHHMM($web->{"op_{$k}_break_end"}),
                        'close'       => $normHHMM($web->{"op_{$k}_close"}),
                        'closed'      => (int)$web->{"op_{$k}_closed"},
                    ];

                    if ($cfg['closed'] || !$cfg['open'] || !$cfg['close']) {
                        $errors[] = '* Hari ' . $this->_nama_hari_id($hari) . ' libur, tidak bisa booking.';
                    } else {
                        $min = $toMin($cfg['open']);
                        $max = $toMin($cfg['close']);
                        $bs  = $toMin($cfg['break_start']);
                        $be  = $toMin($cfg['break_end']);

                        if ($min === null || $max === null || $min >= $max) {
                            $errors[] = '* Hari ' . $this->_nama_hari_id($hari) . ' libur, tidak bisa booking.';
                        } else {
                            $hasBreak = ($bs !== null && $be !== null && $bs < $be);
                            $inBreak  = ($hasBreak && $menit >= $bs && $menit < $be);

                            if ($tanggal === $today) {
                                $nowMin = ((int)$nowTz->format('H')) * 60 + ((int)$nowTz->format('i'));
                                $earliest = max($min, $nowMin + $min_lead_minutes);

                                if ($hasBreak && $earliest >= $bs && $earliest < $be) {
                                    $earliest = $be;
                                }

                                if ($earliest > $max) {
                                    $errors[] = '* Jadwal operasional hari ini sudah berakhir.';
                                } else {
                                    if ($menit < $earliest) {
                                        $minLabel = sprintf('%02d:%02d', intdiv($earliest, 60), $earliest % 60);
                                        $msg = '* Untuk hari ini, jam minimal adalah ' . $minLabel;
                                        if ($min_lead_minutes) {
                                            $msg .= ' (termasuk jeda ' . $min_lead_minutes . ' menit).';
                                        }
                                        $errors[] = $msg;
                                    }
                                    if ($menit > $max) {
                                        $maxLabel = sprintf('%02d:%02d', intdiv($max, 60), $max % 60);
                                        $errors[] = '* Jam melewati jam operasional (maksimal ' . $maxLabel . ').';
                                    }
                                    if ($inBreak) {
                                        $bsLabel = sprintf('%02d:%02d', intdiv($bs, 60), $bs % 60);
                                        $beLabel = sprintf('%02d:%02d', intdiv($be, 60), $be % 60);
                                        $errors[] = '* Jam yang dipilih berada pada waktu istirahat (' . $bsLabel . '–' . $beLabel . ').';
                                    }
                                }
                            } else {
                                if ($menit < $min || $menit > $max) {
                                    $minLabel = sprintf('%02d:%02d', intdiv($min, 60), $min % 60);
                                    $maxLabel = sprintf('%02d:%02d', intdiv($max, 60), $max % 60);
                                    $errors[] = '* Jam kunjungan tidak sesuai operasional (' . $minLabel . '–' . $maxLabel . ').';
                                } elseif ($inBreak) {
                                    $bsLabel = sprintf('%02d:%02d', intdiv($bs, 60), $bs % 60);
                                    $beLabel = sprintf('%02d:%02d', intdiv($be, 60), $be % 60);
                                    $errors[] = '* Jam yang dipilih berada pada waktu istirahat (' . $bsLabel . '–' . $beLabel . ').';
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($errors) {
            return [false, null, null, '<br> ' . implode('<br> ', $errors)];
        }
        return [true, $tanggal, $jam, ''];
    }

    private function _nama_hari_id($w){
        $m=['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        return $m[(int)$w] ?? '';
    }

    private function _validate_pendamping($unit_id, $diminta){
        $row = $this->db->select('nama_unit, jumlah_pendamping')
            ->get_where('unit_tujuan', ['id' => (int)$unit_id])
            ->row();
        $unit_nama = $row ? $row->nama_unit : '-';
        $batas     = $row ? $row->jumlah_pendamping : null; // null = tidak dibatasi

        if ($batas !== null && (int)$diminta > (int)$batas) {
            $err = sprintf('Unit tujuan <b>%s</b> membatasi pendamping maksimal <b>%d</b>. Anda mengajukan <b>%d</b>.',
                $unit_nama, (int)$batas, (int)$diminta);
            return [false, $unit_nama, $err];
        }
        return [true, $unit_nama, ''];
    }

    /**
     * Validasi kuota harian per unit.
     * Return: [ok(bool), sisa(int|null), terpakai(int), kuota(int|null), err(string)]
     * - kuota NULL/<=0 dianggap tak dibatasi.
     * - $lock=true → kunci baris unit untuk mencegah race (dipakai di dalam transaksi).
     */
    private function _validate_kuota_harian(int $unit_id, string $tanggal, bool $lock = false): array
    {
        if ($lock) {
            $unit = $this->db->query(
                'SELECT kuota_harian, nama_unit FROM unit_tujuan WHERE id = ? FOR UPDATE',
                [$unit_id]
            )->row();
        } else {
            $unit = $this->db->select('kuota_harian, nama_unit')
                ->get_where('unit_tujuan', ['id'=>$unit_id])->row();
        }

        if (!$unit) {
            return [false, null, 0, null, 'Unit tujuan tidak ditemukan.'];
        }

        $kuota = isset($unit->kuota_harian) ? (int)$unit->kuota_harian : 0;
        $kuota = $kuota > 0 ? $kuota : null; // null = tak dibatasi

        $this->db->select('COUNT(1) AS jml', false)
            ->from('booking_tamu')
            ->where('unit_tujuan', $unit_id)
            ->where('tanggal', $tanggal);

        if ($this->db->field_exists('status', 'booking_tamu')) {
            $this->db->where_in('status', ['pending','approved','checked_in']);
        }
        if ($this->db->field_exists('token_revoked', 'booking_tamu')) {
            $this->db->where('token_revoked', 0);
        }
        if ($this->db->field_exists('checkout_at', 'booking_tamu')) {
            $this->db->group_start()
                ->where('checkout_at IS NULL', NULL, FALSE)
                ->or_where('checkout_at', '')
                ->group_end();
        }

        $terpakai = (int)($this->db->get()->row('jml') ?? 0);

        if ($kuota === null) {
            return [true, null, $terpakai, null, '']; // tak dibatasi
        }

        if ($terpakai >= $kuota) {
            $tgl_view = date('d-m-Y', strtotime($tanggal));
            $err = sprintf(
                'Kuota harian untuk unit <b>%s</b> pada tanggal <b>%s</b> sudah penuh (kuota %d, terpakai %d).',
                $unit->nama_unit ?? '-', $tgl_view, $kuota, $terpakai
            );
            return [false, 0, $terpakai, $kuota, $err];
        }

        $sisa = max(0, $kuota - $terpakai);
        return [true, $sisa, $terpakai, $kuota, ''];
    }

    private function _resolve_instansi_asal($kategori, $instansiId){
        $map = $this->_kategori_map();
        if (!isset($map[$kategori])) {
            return [false, '', 'Kategori asal tidak dikenal.'];
        }
        $cfg = $map[$kategori];

        $this->db->select($cfg['text'].' AS nama', false)
            ->from($cfg['table'])
            ->where($cfg['id'], (int)$instansiId);
        if ($this->db->field_exists('aktif', $cfg['table'])) {
            $this->db->where('aktif', 1);
        }
        $row = $this->db->get()->row();
        if (!$row) return [false, '', 'Instansi asal tidak ditemukan / tidak aktif pada kategori terpilih.'];
        return [true, $row->nama, ''];
    }

    private function _kategori_map(){
        return [
            'opd'    => ['table'=>'opd_sulsel',               'id'=>'id_opd',    'text'=>'nama_opd'],
            'pn'     => ['table'=>'pengadilan_negeri_sulsel', 'id'=>'id_pn',     'text'=>'nama_pn'],
            'pa'     => ['table'=>'pengadilan_agama_sulsel',  'id'=>'id_pa',     'text'=>'nama_pa'],
            'ptun'   => ['table'=>'pengadilan_tun_sulsel',    'id'=>'id_ptun',   'text'=>'nama_ptun'],
            'kejari' => ['table'=>'kejaksaan_negeri_sulsel',  'id'=>'id_kejari', 'text'=>'nama_kejari'],
            'cabjari'=> ['table'=>'kejari_cabang_sulsel',     'id'=>'id_cabjari','text'=>"CONCAT(nama_cabang,' – ',lokasi)"],
            'bnn'    => ['table'=>'bnn_sulsel',               'id'=>'id_bnn',    'text'=>'nama_unit'],
            'kodim'  => ['table'=>'kodim_sulawesi',           'id'=>'id_kodim',  'text'=>'label'],
            'kejati' => ['table'=>'kejaksaan_tinggi_sulsel',  'id'=>'id_kejati', 'text'=>'nama_kejati'],
            'kepolisian' => ['table'=>'kepolisian',  'id'=>'id_kepolisian', 'text'=>'nama_kepolisian'],
        ];
    }

    private function _make_kode_booking(int $len = 6): string
    {
        // Hindari karakter membingungkan: I, O, 0, 1
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $alphabetLen = strlen($alphabet);

        $gen = function(int $n) use ($alphabet, $alphabetLen) {
            $s = '';
            for ($i = 0; $i < $n; $i++) {
                $s .= $alphabet[random_int(0, $alphabetLen - 1)];
            }
            return $s;
        };

        for ($try = 0; $try < 20; $try++) {
            $code = $gen($len);
            $exists = $this->db->select('1', false)
                ->from('booking_tamu')
                ->where('kode_booking', $code)
                ->limit(1)->get()->num_rows() > 0;

            if (!$exists) return $code;
        }

        return $gen($len + 2);
    }

    public function get_last_upload_error() {
        return $this->last_upload_error;
    }

    private function _upload($field, $path, $types, $max_kb = 2048, $prefix = null)
    {
        $this->last_upload_error = null;

        if (empty($_FILES[$field]['name'])) return NULL;
        if (!is_dir($path)) { @mkdir($path, 0755, true); }

        if (isset($_FILES[$field]['size'])) {
            $sizeKB = (int)ceil($_FILES[$field]['size'] / 1024);
            if ($sizeKB > (int)$max_kb) {
                $this->last_upload_error = sprintf(
                    'Ukuran file %s %dKB melebihi batas %dKB.',
                    htmlspecialchars($_FILES[$field]['name'], ENT_QUOTES, 'UTF-8'),
                    $sizeKB, (int)$max_kb
                );
                log_message('error', '[UPLOAD] Ditolak (terlalu besar): '.$this->last_upload_error);
                return NULL;
            }
        }

        if ($prefix === null) {
            $prefix = preg_replace('/[^a-z0-9_]+/i', '_', strtolower($field));
        }

        $origName = $_FILES[$field]['name'];
        $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $rand6    = function_exists('random_bytes') ? bin2hex(random_bytes(3)) : substr(md5(uniqid('', true)), 0, 6);
        $fname    = sprintf('%s_%s_%s.%s', $prefix, date('Ymd_His'), $rand6, $ext);

        if (!isset($this->upload)) { $this->load->library('upload'); }
        $config = [
            'upload_path'      => $path,
            'allowed_types'    => $types,
            'max_size'         => $max_kb,
            'file_name'        => $fname,
            'overwrite'        => false,
            'remove_spaces'    => true,
            'file_ext_tolower' => true,
            'encrypt_name'     => false,
        ];
        $this->upload->initialize($config);

        if ($this->upload->do_upload($field)) {
            return $fname;
        }

        $err = $this->upload->display_errors('', '');
        $this->last_upload_error = $err ?: 'Gagal mengunggah berkas.';
        log_message('error', '[UPLOAD] Gagal '.$field.': '.$this->last_upload_error);
        return NULL;
    }

    public function upload_surat_tugas()
{
    $this->output->set_content_type('application/json');

    $kode = trim((string)$this->input->post('kode_booking', true));
    if ($kode === '' || empty($_FILES['surat_tugas']['name'])) {
        return $this->_jsonx(false, 'Data tidak lengkap', 400);
    }

    // pastikan booking ada
    $row = $this->db->get_where('booking_tamu', ['kode_booking'=>$kode])->row_array();
    if (!$row) return $this->_jsonx(false, 'Booking tidak ditemukan', 404);

    $config = [
        'upload_path'      => FCPATH.'uploads/surat_tugas/',
        'allowed_types'    => 'pdf|jpg|jpeg|png|webp',
        'max_size'         => 10240,             // KB = 10MB
        'file_ext_tolower' => true,
        'remove_spaces'    => true,
        'detect_mime'      => true,
        'overwrite'        => false,
        'file_name'        => 'surat_'.$kode.'_'.date('Ymd_His'),
    ];
    if (!is_dir($config['upload_path'])) @mkdir($config['upload_path'], 0755, true);

    $this->load->library('upload', $config);
    if (!$this->upload->do_upload('surat_tugas')) {
        return $this->_jsonx(false, $this->upload->display_errors('', ''), 400);
    }

    $up      = $this->upload->data();                       // file_name, file_type, etc.
    $relPath = 'uploads/surat_tugas/'.$up['file_name'];     // simpan RELATIF, lebih fleksibel
    $url     = $up['file_name'];

    // (opsional) hapus file lama agar tidak numpuk
    if (!empty($row['surat_tugas'])) {
        $oldPath = parse_url($row['surat_tugas'], PHP_URL_PATH) ?: $row['surat_tugas'];
        $oldAbs  = FCPATH.ltrim($oldPath, '/');
        if (is_file($oldAbs) && strpos($oldAbs, FCPATH.'uploads/surat_tugas/') === 0) @unlink($oldAbs);
    }

    // simpan ke DB supaya view bisa render setelah reload
    if ($this->db->field_exists('surat_tugas', 'booking_tamu')) {
        $this->db->where('kode_booking', $kode)->update('booking_tamu', ['surat_tugas' => $url]);
    }

    return $this->_jsonx(true, 'OK', 200, [
        'url'       => $url,
        'path'      => $relPath,
        'name'      => $up['file_name'],
        'mime'      => $up['file_type'],
        // jika csrf_regenerate = TRUE, FE bisa pakai hash baru ini
        'csrf_hash' => $this->security->get_csrf_hash(),
    ]);
}

private function _jsonx($ok, $msg, $status=200, $extra=[])
{
    $this->output->set_status_header($status);
    echo json_encode(array_merge(['ok'=>$ok,'msg'=>$msg], $extra));
    return;
}


    private function _make_qr($kode_booking, $with_logo = true)
    {
        $this->load->library('ciqrcode');
        $dir = './uploads/qr/';
        if (!is_dir($dir)) { @mkdir($dir, 0755, true); }

        $file = 'qr_'.$kode_booking.'.png';
        $this->ciqrcode->generate([
            'data'     => $kode_booking,
            'level'    => 'H',
            'size'     => 10,
            'savename' => $dir.$file
        ]);

        if ($with_logo) {
            $logo_path = FCPATH.'assets/images/logo.png';
            if (is_file($logo_path)) {
                $this->_qr_overlay_logo($dir.$file, $logo_path, 0.22, false);
            }
        }

        return base_url('uploads/qr/'.$file);
    }

    private function _qr_overlay_logo($qr_path, $logo_path, $scale = 0.22, $save_as_new = false, $suffix = '_logo')
    {
        try {
            if (!is_file($qr_path) || !is_file($logo_path)) {
                log_message('error', 'QR/Logo path tidak ditemukan.');
                return false;
            }

            $makeImg = function($path) {
                $info = getimagesize($path);
                if (!$info) return [null, null];
                switch ($info[2]) {
                    case IMAGETYPE_PNG:  $img = imagecreatefrompng($path);  $ext = 'png';  break;
                    case IMAGETYPE_JPEG: $img = imagecreatefromjpeg($path); $ext = 'jpg';  break;
                    default: return [null, null];
                }
                return [$img, $ext];
            };

            list($qr,   $qrExt)   = $makeImg($qr_path);
            list($logo, $logoExt) = $makeImg($logo_path);
            if (!$qr || !$logo) {
                log_message('error', 'Gagal membaca gambar (format harus PNG/JPG).');
                return false;
            }

            imagealphablending($qr, true);
            imagesavealpha($qr, true);
            imagealphablending($logo, true);
            imagesavealpha($logo, true);

            $qr_w = imagesx($qr);  $qr_h = imagesy($qr);
            $lg_w = imagesx($logo);$lg_h = imagesy($logo);

            $scale = max(0.10, min(0.35, (float)$scale));

            $target_w = (int) round($qr_w * $scale);
            $target_h = (int) round($lg_h * ($target_w / max(1, $lg_w)));

            $dst_x = (int) round(($qr_w - $target_w) / 2);
            $dst_y = (int) round(($qr_h - $target_h) / 2);

            $logo_resized = imagecreatetruecolor($target_w, $target_h);
            imagealphablending($logo_resized, false);
            imagesavealpha($logo_resized, true);
            $transparent = imagecolorallocatealpha($logo_resized, 0, 0, 0, 127);
            imagefilledrectangle($logo_resized, 0, 0, $target_w, $target_h, $transparent);

            imagecopyresampled($logo_resized, $logo, 0, 0, 0, 0, $target_w, $target_h, $lg_w, $lg_h);

            imagecopy($qr, $logo_resized, $dst_x, $dst_y, 0, 0, $target_w, $target_h);

            if ($save_as_new) {
                $pi = pathinfo($qr_path);
                $out = $pi['dirname'].'/'.$pi['filename'].$suffix.'.png';
            } else {
                $out = $qr_path;
            }

            $ok = imagepng($qr, $out, 6);
            imagedestroy($qr);
            imagedestroy($logo);
            imagedestroy($logo_resized);

            if (!$ok) {
                log_message('error', 'Gagal menyimpan QR hasil overlay.');
                return false;
            }
            return $out;
        } catch (Throwable $e) {
            log_message('error', 'QR overlay error: '.$e->getMessage());
            return false;
        }
    }

    public function contact_vcf()
    {
        $path = FCPATH.'uploads/contact-silaturahmi.vcf';
        if ( ! is_file($path)) { show_404(); return; }

        $this->load->helper('download');
        $filename = 'SilaturahmiMakassar.vcf';

        force_download($filename, file_get_contents($path));
        exit;
    }

    private function _send_wa_konfirmasi($no_hp, array $d)
    {
        // Normalisasi nomor tujuan (user)
        if (method_exists($this,'_normalize_msisdn_id')) {
            $no_hp = $this->_normalize_msisdn_id((string)$no_hp);
        }

        // escape ringan untuk WA markdown
        $wa_plain = function($s){
            $s = (string)$s;
            return str_replace(['*','_','~','`'], ['\*','\_','\~','\`'], $s);
        };

        // (A) ambil token lebih dulu
        $token = !empty($d['access_token']) ? $d['access_token'] : null;

        // (B) redirect_url aman
        $redirect_url = !empty($d['redirect_url'])
            ? $d['redirect_url']
            : ($token ? site_url('booking/booked').'?t='.urlencode($token) : site_url('booking'));

        // (C) data utama
        $nama          = $wa_plain($d['nama'] ?? '-');
        $keperluan     = $wa_plain($d['keperluan'] ?? '-');
        $kode          = trim((string)($d['kode'] ?? ''));
        $instansi_asal = $wa_plain($d['instansi_asal'] ?? '-');
        $nama_petugas_instansi = $wa_plain($d['nama_petugas_instansi'] ?? '-');
        $unit_tujuan   = $wa_plain($d['unit_tujuan'] ?? '-');
        $tanggal_disp  = !empty($d['tanggal']) ? date('d-m-Y', strtotime($d['tanggal'])) : '-';
        $jam_disp      = isset($d['jam']) ? $d['jam'] : '-';

        $pdf_url = $d['pdf_url'] ?? '';
        if ($pdf_url === '' && $kode !== '' && $token) {
            $pdf_url = site_url('booking/download_gate').'?k='.rawurlencode($kode).'&t='.urlencode($token);
        }

        $web = $this->fm->web_me();
        $is_update = !empty($d['is_update']);

        $pesan  = $is_update ? "*[Perubahan Booking Kunjungan]*\n\n" : "*[Konfirmasi Booking Kunjungan]*\n\n";
        $pesan .= "Halo *{$nama}*,\n\n";
        $pesan .= $is_update
            ? "Anda telah melakukan *perubahan* booking kunjungan dengan detail berikut:\n\n"
            : "Pengajuan kunjungan Anda telah *BERHASIL* didaftarkan dengan detail berikut:\n\n";

        $pesan .= "🆔 Kode Booking : *{$wa_plain($kode)}*\n";
        $pesan .= "👤 Nama Tamu : {$nama}\n";
        $pesan .= "🏢 Instansi Asal : {$instansi_asal}\n";
        $pesan .= "🏛️ Unit Tujuan : {$unit_tujuan}\n";
        $pesan .= "👔 Pejabat Unit : {$nama_petugas_instansi}\n";
        $pesan .= "📅 Tanggal : {$tanggal_disp}\n";
        $pesan .= "🕒 Jam : {$jam_disp}\n";
        $pesan .= "📝 Keperluan : {$keperluan}\n\n";
        if ($pdf_url !== '') {
            $pesan .= "🔳 Download kode booking (PDF):\n{$pdf_url}\n\n";
        }
        $pesan .= "🔗 Detail booking:\n{$redirect_url}\n\n";
        $pesan .= "Tunjukkan kode booking (PDF)/Barcode pada petugas pintu masuk saat kunjungan\n\n";
        $pesan .= "📇 Simpan kontak kami agar link bisa diklik langsung\n\n";
        $pesan .= "_Pesan ini dikirim otomatis oleh Aplikasi ".($web->nama_website ?? 'Aplikasi')."._";

        if (function_exists('send_wa_single')) {
            try {
                send_wa_single($no_hp, $pesan);
                return true;
            } catch (Throwable $e) {
                log_message('error', 'send_wa_single error: '.$e->getMessage());
                return false;
            }
        } else {
            log_message('error', 'Fungsi send_wa_single tidak ditemukan.');
            return false;
        }
    }

    public function get_limit_pendamping(){
        $id = (int)$this->input->get('id');
        $row = $this->db->select('jumlah_pendamping')->get_where('unit_tujuan',['id'=>$id])->row();
        $max = $row ? $row->jumlah_pendamping : null; // NULL berarti tak dibatasi
        echo json_encode(["max" => $max === null ? null : (int)$max]);
    }

    public function options_by_kategori()
    {
        $jenis   = $this->input->get('jenis', true);
        $nocache = ($this->input->get('nocache') === '1');

        // === CACHE DRIVER: nocache=1 -> adapter dummy (tidak simpan apa pun) ===
        $this->load->driver('cache', ['adapter' => $nocache ? 'dummy' : 'file']);

        $map = [
            'opd'    => ['table'=>'opd_sulsel',               'id'=>'id_opd',    'text'=>'nama_opd',                         'search'=>['nama_opd']],
            'pn'     => ['table'=>'pengadilan_negeri_sulsel', 'id'=>'id_pn',     'text'=>'nama_pn',                          'search'=>['nama_pn','kabkota']],
            'pa'     => ['table'=>'pengadilan_agama_sulsel',  'id'=>'id_pa',     'text'=>'nama_pa',                          'search'=>['nama_pa','kabkota']],
            'ptun'   => ['table'=>'pengadilan_tun_sulsel',    'id'=>'id_ptun',   'text'=>'nama_ptun',                        'search'=>['nama_ptun','kabkota']],
            'kejari' => ['table'=>'kejaksaan_negeri_sulsel',  'id'=>'id_kejari', 'text'=>'nama_kejari',                      'search'=>['nama_kejari','kabkota']],
            'cabjari'=> ['table'=>'kejari_cabang_sulsel',     'id'=>'id_cabjari','text'=>"CONCAT(nama_cabang,' – ',lokasi)", 'search'=>['nama_cabang','lokasi','kabkota']],
            'bnn'    => ['table'=>'bnn_sulsel',               'id'=>'id_bnn',    'text'=>'nama_unit',                        'search'=>['nama_unit','kabkota']],
            'kodim'  => ['table'=>'kodim_sulawesi',           'id'=>'id_kodim',  'text'=>'label',                            'search'=>['label','wilayah','provinsi']],
            'kejati' => ['table'=>'kejaksaan_tinggi_sulsel',  'id'=>'id_kejati', 'text'=>'nama_kejati',                      'search'=>['nama_kejati']],
            'kepolisian' => ['table'=>'kepolisian',  'id'=>'id_kepolisian', 'text'=>'nama_kepolisian',                      'search'=>['nama_kepolisian']],
        ];

        if (!$jenis || !isset($map[$jenis])) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode([]));
        }

        $cacheKey = 'opts_kat:' . $jenis;

        // === Ambil dari cache (kecuali nocache=1) ===
        $rows = $nocache ? false : $this->cache->get($cacheKey);

        if ($rows === false) {
            $cfg = $map[$jenis];

            $this->db->select($cfg['id'].' AS id, '.$cfg['text'].' AS text', false)
                     ->from($cfg['table']);

            if ($this->db->field_exists('aktif', $cfg['table'])) {
                $this->db->where('aktif', 1);
            }

            if ($jenis === 'kodim') {
                $this->db->order_by('nomor_kodim', 'ASC');
            } else {
                // MySQL mengizinkan ORDER BY alias 'text'
                $this->db->order_by('text', 'ASC');
            }

            $rows = $this->db->get()->result_array();

            // === Simpan cache “selamanya” (sampai dihapus manual / invalidasi admin) ===
            if (!$nocache) {
                $this->cache->save($cacheKey, $rows, 0); // TTL=0 => permanen
            }
        }

        return $this->output->set_content_type('application/json')
            ->set_output(json_encode($rows));
    }


    public function print_pdf($kode_booking)
    {
        $token = $this->input->get('t', TRUE);
        if (!$token) show_error('Token wajib.', 403);

        $row = $this->db->get_where('booking_tamu', [
            'kode_booking' => $kode_booking,
            'access_token' => $token
        ])->row();

        if (!$row) show_error('Token/booking tidak valid.', 403);
        if ((int)$row->token_revoked === 1) show_error('Token dicabut.', 403);
        if (!empty($row->checkout_at)) show_error('Token sudah tidak berlaku (checkout).', 403);
        $data["rec"]        = $this->fm->web_me();

        $data['booking'] = $row;
        $html = $this->load->view('booking_pdf', $data, TRUE);

        $dl = (int)$this->input->get('dl');
        $filename = 'Booking_'.$kode_booking.'.pdf';

        $this->load->library('pdf'); // TCPDF wrapper
        $pdf = new Pdf('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
        if (method_exists($pdf, 'setPrintHeader')) $pdf->setPrintHeader(false);
        if (method_exists($pdf, 'setPrintFooter')) $pdf->setPrintFooter(false);
        $pdf->SetTitle($filename);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output($filename, $dl ? 'D' : 'I');
    }

    // contoh minimal; lindungi dengan auth petugas!
    public function do_checkin($kode_booking)
    {
        $this->db->where('kode_booking', $kode_booking)
            ->update('booking_tamu', ['checkin_at' => date('Y-m-d H:i:s')]);
    }

    public function do_checkout($kode_booking)
    {
        $this->db->where('kode_booking', $kode_booking)
            ->update('booking_tamu', [
                'checkout_at'   => date('Y-m-d H:i:s'),
                'token_revoked' => 1
            ]);
    }

    private function _send_wa_info_unit(string $hp_unit, array $d): bool
    {
        if (!function_exists('send_wa_single')) {
            log_message('error', 'send_wa_single tidak tersedia.');
            return false;
        }

        if (method_exists($this, '_normalize_msisdn_id')) {
            $hp_unit = $this->_normalize_msisdn_id($hp_unit);
        } else {
            $hp_unit = preg_replace('/\D+/', '', $hp_unit ?? '');
            if ($hp_unit !== '' && $hp_unit[0] === '0') $hp_unit = '62'.substr($hp_unit,1);
        }
        if ($hp_unit === '') {
            log_message('error', 'WA unit kosong setelah normalisasi.');
            return false;
        }

        $web = null;
        if (isset($this->fm) && method_exists($this->fm, 'web_me'))       $web = $this->fm->web_me();
        elseif (isset($this->om) && method_exists($this->om, 'web_me'))   $web = $this->om->web_me();
        $app_name = $web->nama_website ?? 'Aplikasi';

        $wa_plain = function($s){
            $s = (string)$s;
            return str_replace(['*','_','~','`'], ['\*','\_','\~','\`'], $s);
        };

        $kode        = trim((string)($d['kode'] ?? ''));
        $nama        = $wa_plain(trim((string)($d['nama'] ?? '-')));
        $no_hp_tamu  = trim((string)($d['hp_tamu'] ?? ''));
        $instansi    = $wa_plain(trim((string)($d['instansi_asal'] ?? '-')));
        $child_unit  = $wa_plain(trim((string)($d['child_unit_nama'] ?? ($d['unit_nama'] ?? '-'))));
        $unit_nama   = $wa_plain(trim((string)($d['unit_nama'] ?? '-')));
        $unit_pej    = $wa_plain(trim((string)($d['unit_pejabat'] ?? '')));
        $pendamping  = (int)($d['pendamping'] ?? 0);
        $keperluan   = $wa_plain(trim((string)(($d['keperluan'] ?? '-') ?: '-')));
        $redir       = trim((string)($d['redirect_url'] ?? ''));
        $is_cc       = !empty($d['is_cc']);

        $tanggal_disp = '-';
        if (!empty($d['tanggal'])) {
            $dt = DateTime::createFromFormat('Y-m-d', (string)$d['tanggal']);
            if ($dt && $dt->format('Y-m-d') === (string)$d['tanggal']) {
                $tanggal_disp = $dt->format('d-m-Y');
            }
        }
        $jam_disp = isset($d['jam']) ? $d['jam'] : '-';

        $wa_link = '';
        if ($no_hp_tamu !== '') {
            $wa_digits = preg_replace('/\D+/', '', $no_hp_tamu);
            if ($wa_digits !== '') {
                if ($wa_digits[0] === '0') $wa_digits = '62'.substr($wa_digits,1);
                if (strlen($wa_digits) >= 10) $wa_link = 'https://wa.me/'.$wa_digits;
            }
        }

        $is_update = !empty($d['is_update']);

        if ($is_cc) {
            $header = $is_update ? '🔁 *TEMBUSAN PERUBAHAN KUNJUNGAN*' : '🔁 *TEMBUSAN PEMBERITAHUAN KUNJUNGAN*';
        } else {
            $header = $is_update ? '📣 *PEMBERITAHUAN PERUBAHAN KUNJUNGAN*' : '📣 *PEMBERITAHUAN KUNJUNGAN*';
        }

        $kepada = "Kepada Yth.\n";
        $kepada .= ($unit_pej !== '') ? ('*'.$unit_pej.'* — *'.$unit_nama.'*') : ('*'.$unit_nama.'*');

        $lines = [];
        $lines[] = $header;
        $lines[] = '';
        $lines[] = $kepada;
        $lines[] = '━━━━━━━━━━━━━━━━━━━━';
        if ($is_update) {
            $lines[] = 'Terdapat *perubahan* data booking kunjungan. Berikut detail terbaru:';
        }
        $lines[] = '🆔 Kode Booking : *'.($kode !== '' ? $wa_plain($kode) : '-').'*';
        $lines[] = '👤 Tamu : '.$nama;
        if ($wa_link) $lines[] = '🟢 WhatsApp : '.$wa_link;
        $lines[] = '🏢 Instansi : '.$instansi;
        $lines[] = ($is_cc ? '🔎 Tembusan utk : *'.$child_unit.'*' : '🎯 Unit Tujuan : '.$child_unit);
        $lines[] = '📅 Tanggal : '.$tanggal_disp;
        $lines[] = '⏰ Jam : '.$jam_disp;
        $lines[] = '👥 Pendamping : '.$pendamping.' orang';
        $lines[] = '📝 Keperluan : '.$keperluan;
        if ($redir  !== '') {
            $lines[] = '';
            $lines[] = '🔗 Detail kunjungan:';
            $lines[] = $redir;
        }
        $lines[] = '';
        $lines[] = 'Simpan nomor ini agar link dapat diklik';
        $lines[] = '_Pesan otomatis '.$app_name.'._';

        try {
            send_wa_single($hp_unit, implode("\n", $lines));
            return true;
        } catch (Throwable $e) {
            log_message('error', 'WA unit gagal: '.$e->getMessage());
            return false;
        }
    }

    // ==== DOKUMENTASI: list & upload (opsional) ====

    public function dokumentasi_list()
    {
        $token = $this->input->post('t', TRUE) ?: $this->input->get('t', TRUE);
        if (!$token) return $this->_json(['ok'=>false,'err'=>'missing token'], 422);

        $b = $this->db->get_where('booking_tamu', ['access_token' => $token])->row();
        if (!$b) return $this->_json(['ok'=>false,'err'=>'not found'], 404);

        $dir = FCPATH.'uploads/dokumentasi/';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        $list = [];

        if ($this->db->table_exists('booking_dokumentasi')) {
            $rows = $this->db->select('id, filename, uploaded_at')
                ->from('booking_dokumentasi')
                ->where('kode_booking', $b->kode_booking)
                ->order_by('uploaded_at','DESC')
                ->get()->result_array();
            foreach ($rows as $r) {
                $list[] = [
                    'filename' => $r['filename'],
                    'url'      => base_url('uploads/dokumentasi/'.rawurlencode($r['filename'])),
                    'time'     => $r['uploaded_at']
                ];
            }
        } else {
            foreach (glob($dir.'doc_'.$b->kode_booking.'_*.*') ?: [] as $f) {
                $list[] = [
                    'filename' => basename($f),
                    'url'      => base_url('uploads/dokumentasi/'.basename($f)),
                    'time'     => date('Y-m-d H:i:s', @filemtime($f))
                ];
            }
            usort($list, function($a,$b){ return strcmp($b['time'] ?? '', $a['time'] ?? ''); });
        }

        return $this->_json(['ok'=>true,'data'=>$list]);
    }

    public function upload_dokumentasi()
    {
        // --- Ambil data dari POST form-urlencoded ---
        $kode = trim((string)$this->input->post('kode', true));
        $b64  = (string)$this->input->post('image', false);

        // --- Fallback: coba body JSON ---
        if ($kode === '' || $b64 === '') {
            $rawIn = $this->input->raw_input_stream;
            if ($rawIn) {
                $j = json_decode($rawIn, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($j)) {
                    if ($kode === '') $kode = trim((string)($j['kode'] ?? ''));
                    if ($b64  === '') $b64  = (string)($j['image'] ?? '');
                }
            }
        }

        // --- Fallback: multipart file ---
        if ($b64 === '' && !empty($_FILES)) {
            $f = $_FILES['image'] ?? $_FILES['doc_photo'] ?? null;
            if ($f && isset($f['error']) && $f['error'] === UPLOAD_ERR_OK) {
                $tmp  = $f['tmp_name'];
                $mime = function_exists('finfo_open')
                    ? (function($p){ $fi = finfo_open(FILEINFO_MIME_TYPE); $m = finfo_file($fi,$p); finfo_close($fi); return $m; })($tmp)
                    : mime_content_type($tmp);
                if (!in_array($mime, ['image/jpeg','image/png'], true)) {
                    return $this->json_exit(["ok"=>false, "msg"=>"Hanya JPG/PNG"], 400);
                }
                $data = file_get_contents($tmp);
                if ($data === false) return $this->json_exit(["ok"=>false,"msg"=>"Gagal membaca file"],500);
                $b64 = 'data:'.$mime.';base64,'.base64_encode($data);
            }
        }

        // --- Terima token 't' sebagai alternatif 'kode' ---
        $tok = trim((string)$this->input->post('t', true));
        if ($kode === '' && $tok === '') {
            $rawIn = $this->input->raw_input_stream;
            if ($rawIn) {
                $j = json_decode($rawIn, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($j)) {
                    $tok = trim((string)($j['t'] ?? ''));
                }
            }
        }
        if ($kode === '' && $tok !== '') {
            $kode_from_tok = $this->db
                ->select('kode_booking')
                ->get_where('booking_tamu', ['access_token' => $tok])
                ->row('kode_booking');
            if ($kode_from_tok) $kode = $kode_from_tok;
        }

        // --- Validasi minimal ---
        if ($kode === '' || $b64 === '') {
            return $this->json_exit(["ok"=>false, "msg"=>"Data tidak lengkap (kode/image)"], 400);
        }

        // --- Validasi booking ---
        $row = $this->db->get_where('booking_tamu', ['kode_booking'=>$kode])->row_array();
        if (!$row) return $this->json_exit(["ok"=>false, "msg"=>"Booking tidak ditemukan"], 404);

        // --- Parse dataURL (jpg/png) ---
        $b64 = preg_replace('/\s+/', ' ', $b64);
        $b64 = preg_replace('#^data:\s*image/([^;]+);base64,#i', 'data:image/$1;base64,', $b64);
        $payload = substr($b64, strpos($b64, ',')+1);
        $payload = strtr($payload, ' ', '+');
        if (!preg_match('#^data:image/(png|jpe?g);base64,#i', $b64, $m)) {
            return $this->json_exit(["ok"=>false, "msg"=>"Format gambar tidak valid"], 400);
        }
        $ext = strtolower($m[1]) === 'jpeg' ? 'jpg' : strtolower($m[1]);

        $bin = base64_decode($payload, true);
        if ($bin === false) return $this->json_exit(["ok"=>false,"msg"=>"Base64 rusak"],400);

        if (strlen($bin) > 5 * 1024 * 1024) {
            return $this->json_exit(["ok"=>false, "msg"=>"Ukuran gambar melebihi 5MB"], 413);
        }

        $info = @getimagesizefromstring($bin);
        if ($info === false) return $this->json_exit(["ok"=>false,"msg"=>"Berkas bukan gambar"],400);
        list($w, $h) = $info;

        $src = @imagecreatefromstring($bin);
        if (!$src) return $this->json_exit(["ok"=>false, "msg"=>"Gagal memproses gambar"], 500);
        $maxSide = 1600;
        $scale = min(1, $maxSide / max($w, $h));
        $nw = max(1, (int)floor($w * $scale));
        $nh = max(1, (int)floor($h * $scale));
        $dst = imagecreatetruecolor($nw, $nh);
        if ($ext === 'png') { imagealphablending($dst, false); imagesavealpha($dst, true); }
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

        $dir = FCPATH.'uploads/foto/'; // konsisten dengan dokumentasi_list
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            imagedestroy($src); imagedestroy($dst);
            return $this->json_exit(["ok"=>false, "msg"=>"Gagal membuat folder upload"], 500);
        }
        $safeKode = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $kode);
        $fname   = 'doc_'.$safeKode.'_'.date('Ymd_His').'_'.substr(md5(uniqid('',true)),0,6).'.'.$ext;
        $fullTmp = $dir.'.tmp_'.$fname;
        $full    = $dir.$fname;

        $ok = ($ext === 'jpg') ? imagejpeg($dst, $fullTmp, 85) : imagepng($dst, $fullTmp, 6);
        imagedestroy($src); imagedestroy($dst);
        if (!$ok) return $this->json_exit(["ok"=>false, "msg"=>"Gagal menyimpan berkas"], 500);
        @chmod($fullTmp, 0644);
        if (!@rename($fullTmp, $full)) { @unlink($fullTmp); return $this->json_exit(["ok"=>false,"msg"=>"Gagal memindahkan berkas"],500); }

        // Jika tabel dokumentasi ada, simpan metadata
        // if ($this->db->table_exists('booking_dokumentasi')) {
        //     $this->db->insert('booking_dokumentasi', [
        //         'kode_booking' => $kode,
        //         'filename'     => $fname,
        //         'uploaded_at'  => date('Y-m-d H:i:s'),
        //     ]);
        // }
        if ($this->db->field_exists('foto', 'booking_tamu')) {
            $this->db->where('kode_booking', $kode)->update('booking_tamu', ['foto' => $fname]);
        }
        return $this->json_exit([
            "ok"  => true,
            "msg" => "Foto tersimpan",
            "url" => base_url('uploads/foto/'.$fname),
            "file"=> $fname,
            "meta"=> ["w"=>$nw, "h"=>$nh]
        ]);
    }

    /* =========================
     *  EDIT (form ubah)
     *  ========================= */
    public function edit($kode_booking = null)
    {
        $t = trim((string)$this->input->get('t', TRUE)); // access_token (WAJIB)
        $k = $kode_booking ?: trim((string)$this->input->get('k', TRUE));

        if ($t === '') { return $this->_booked_error("Link tidak valid."); }

        // Cari berdasarkan token; jika ada kode_booking, pastikan cocok
        $this->db->from('booking_tamu')->where('access_token', $t);
        if ($k !== '') $this->db->where('kode_booking', $k);
        $booking = $this->db->limit(1)->get()->row();

        // pastikan booking ada dahulu sebelum _check_edit_lock()
        if (!$booking) return $this->_booked_error("Link tidak valid.");

        list($okEdit, $msgEdit) = $this->_check_edit_lock($booking);
        if (!$okEdit) {
            return $this->_booked_error($msgEdit);
        }

        if ((int)$booking->token_revoked === 1) return $this->_booked_error("Link sudah tidak berlaku (token dicabut).");
        if (!empty($booking->checkout_at)) {
            $wkt = date('d-m-Y H:i', strtotime($booking->checkout_at));
            return $this->_booked_error("Link tidak berlaku. Anda telah checkout pada {$wkt}.");
        }
        if (isset($booking->status) && $booking->status === 'expired') {
            $wkt = !empty($booking->expired_at)
                ? date('d-m-Y H:i', strtotime($booking->expired_at))
                : ($booking->tanggal.' '.$booking->jam);
            return $this->_booked_error("Link tidak berlaku. Status: Tidak Hadir (Expired) pada {$wkt}.");
        }

        $data = [
            "controller" => get_class($this),
            "title"      => "Ubah Booking",
            "deskripsi"  => "Perbarui data booking Anda.",
            "booking"    => $booking,
            "rec"        => $this->fm->web_me(),
            "units_tree" => $this->mb->get_tree(),
            "prev"       => base_url("assets/images/booking.png"),
        ];

        // Noindex + no-cache
        $this->output
            ->set_header('X-Robots-Tag: noindex, nofollow, noarchive')
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0')
            ->set_header('Pragma: no-cache');
            list($batas_edit, $batas_hari) = $this->_get_edit_limits();
            $data['batas_edit'] = $batas_edit;
            $data['batas_hari'] = $batas_hari;

        $this->load->view('booking_edit', $data);
    }

    /* =========================
     *  UPDATE (proses ubah via AJAX POST)
     *  ========================= */
    public function update()
    {
        $kode  = trim((string)$this->input->post('kode_booking', true));
        $token = trim((string)$this->input->post('access_token', true));

        if ($kode === '' || $token === '') {
            return $this->_json(["success"=>false,"title"=>"Tidak Valid","pesan"=>"Data kunci tidak lengkap."], 422);
        }

        $booking = $this->db->get_where('booking_tamu', [
            'kode_booking' => $kode,
            'access_token' => $token
        ])->row();

        if (!$booking) {
            return $this->_json(["success"=>false,"title"=>"Tidak Ditemukan","pesan"=>"Link tidak valid."], 404);
        }

        list($okEdit, $msgEdit) = $this->_check_edit_lock($booking);
        if (!$okEdit) {
            return $this->_json(["success"=>false,"title"=>"Tidak Diizinkan","pesan"=>$msgEdit], 403);
        }

        if ((int)$booking->token_revoked === 1) {
            return $this->_json(["success"=>false,"title"=>"Tidak Berlaku","pesan"=>"Token dicabut."], 410);
        }
        if (!empty($booking->checkout_at)) {
            $wkt = date('d-m-Y H:i', strtotime($booking->checkout_at));
            return $this->_json(["success"=>false,"title"=>"Tidak Berlaku","pesan"=>"Anda telah checkout pada {$wkt}."], 410);
        }
        if (isset($booking->status) && $booking->status === 'expired') {
            $wkt = !empty($booking->expired_at)
                ? date('d-m-Y H:i', strtotime($booking->expired_at))
                : ($booking->tanggal.' '.$booking->jam);
            return $this->_json(["success"=>false,"title"=>"Tidak Berlaku","pesan"=>"Status expired pada {$wkt}."], 410);
        }

        $data = $this->input->post(NULL, TRUE);
        $this->load->library('form_validation');
        $this->_set_rules();

        if ($this->form_validation->run() === FALSE) {
            return $this->_json([
                "success"=>false, "title"=>"Validasi Gagal",
                "pesan"=>validation_errors()
            ]);
        }

        // 1) Validasi tanggal & jam
        [$okJ, $tanggal, $jam, $errJ] = $this->_validate_jadwal($data['tanggal'], $data['jam']);
        if (!$okJ) {
            return $this->_json(["success"=>false,"title"=>"Validasi Jadwal","pesan"=>$errJ]);
        }

        // 2) Batas pendamping per unit
        $unit_id    = (int)$data['unit_tujuan'];
        $dimintaRaw = $this->input->post('jumlah_pendamping', true);
        $diminta    = ($dimintaRaw === '' || $dimintaRaw === null) ? 0 : (int)$dimintaRaw;
        [$okP, $unit_nama, $errP] = $this->_validate_pendamping($unit_id, $diminta);
        if (!$okP) {
            return $this->_json(["success"=>false,"title"=>"Melebihi Batas Pendamping","pesan"=>$errP]);
        }

        // 3) Instansi asal
        $kategori   = strtolower((string)$this->input->post('kategori', true));
        $instansiId = (int)$this->input->post('instansi_id', true);
        if ($kategori === 'lainnya') {
            $instansiNama = trim((string)$this->input->post('target_instansi_nama', true));
            if ($instansiNama === '' || mb_strlen($instansiNama) < 3) {
                return $this->_json(["success"=>false,"title"=>"Validasi Gagal","pesan"=>"Nama instansi (Lainnya) minimal 3 karakter."]);
            }
            $instansiId = null;
        } else {
            [$okI, $instansiNama, $errI] = $this->_resolve_instansi_asal($kategori, $instansiId);
            if (!$okI) {
                return $this->_json(["success"=>false,"title"=>"Validasi Gagal","pesan"=>$errI]);
            }
        }

        // 4) Upload opsional
        $surat_tugas = $booking->surat_tugas ?: null;
        $foto        = $booking->foto ?: null;

        $no_file = function(string $field){
            return empty($_FILES[$field]) ||
                   (isset($_FILES[$field]['error']) && $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) ||
                   (isset($_FILES[$field]['name']) && $_FILES[$field]['name'] === '');
        };

        if (!$no_file('surat_tugas')) {
            $tmp = $this->_upload('surat_tugas', './uploads/surat_tugas/', 'pdf|jpg|jpeg|png', 2048, 'surat_tugas');
            if ($tmp === null) {
                $msg = $this->get_last_upload_error();
                if ($msg && stripos($msg, 'did not select a file') === false) {
                    return $this->_json(["success"=>false,"title"=>"Upload Gagal","pesan"=>$msg ?: "Surat tugas melebihi 2MB."]);
                }
            } else {
                if ($surat_tugas && is_file(FCPATH.'uploads/surat_tugas/'.$surat_tugas)) {
                    @unlink(FCPATH.'uploads/surat_tugas/'.$surat_tugas);
                }
                $surat_tugas = $tmp;
            }
        }

        if (!$no_file('foto')) {
            $tmp = $this->_upload('foto', './uploads/foto/', 'jpg|jpeg|png', 1536, 'foto');
            if ($tmp === null) {
                $msg = $this->get_last_upload_error();
                if ($msg && stripos($msg, 'did not select a file') === false) {
                    return $this->_json(["success"=>false,"title"=>"Upload Gagal","pesan"=>$msg ?: "Foto melebihi 1.5MB."]);
                }
            } else {
                if ($foto && is_file(FCPATH.'uploads/foto/'.$foto)) {
                    @unlink(FCPATH.'uploads/foto/'.$foto);
                }
                $foto = $tmp;
            }
        }

        // 5) Nama petugas instansi default dari unit_tujuan
        $nama_petugas_instansi = null;
        $unit = $this->db->select('nama_pejabat, nama_unit')
            ->from('unit_tujuan')->where('id', $unit_id)->limit(1)->get()->row();
        if ($unit && !empty($unit->nama_pejabat)) $nama_petugas_instansi = trim($unit->nama_pejabat);

        // 6) PENDAMPING (JSON)
        $pendampingRows  = [];
        $pendamping_json = $this->input->post('pendamping_json', true);
        $nik_tamu        = preg_replace('/\D+/', '', (string)$this->input->post('nik', true));

        if ($pendamping_json !== null && $pendamping_json !== '') {
            $arr = json_decode($pendamping_json, true);
            if (!is_array($arr)) {
                return $this->_json(["success"=>false,"title"=>"Validasi Pendamping","pesan"=>"Format data pendamping tidak valid."]);
            }
            $seen = [];
            foreach ($arr as $i => $p) {
                $rawId = (string)($p['nik'] ?? '');
                $id    = preg_replace('/\D+/', '', $rawId);
                $nama  = trim((string)($p['nama'] ?? ''));

                if ($id === '' && $nama === '') continue;

                if ($nama === '') {
                    return $this->_json(["success"=>false,"title"=>"Validasi Pendamping","pesan"=>"Nama pendamping #".($i+1)." wajib diisi."]);
                }
                if ($id === '' || !preg_match('/^(?:\d{8,9}|\d{16}|\d{18})$/', $id)) {
                    return $this->_json(["success"=>false,"title"=>"Validasi Pendamping","pesan"=>"ID pendamping #".($i+1)." tidak valid. Isi salah satu: NIK 16 digit, NIP 18/9 digit, atau NRP 8–9 digit."]);
                }
                $nik_tamu_digits = preg_replace('/\D+/', '', (string)$nik_tamu);
                if ($nik_tamu_digits && $id === $nik_tamu_digits) {
                    return $this->_json(["success"=>false,"title"=>"Validasi Pendamping","pesan"=>"ID pendamping #".($i+1)." tidak boleh sama dengan ID tamu."]);
                }
                if (isset($seen[$id])) {
                    return $this->_json(["success"=>false,"title"=>"Validasi Pendamping","pesan"=>"ID pendamping #".($i+1)." duplikat."]);
                }
                $seen[$id] = true;
                $pendampingRows[] = ['nik' => $id, 'nama' => $nama];
            }
        }
        if ($diminta > 0 && count($pendampingRows) !== $diminta) {
            return $this->_json([
                "success"=>false,"title"=>"Validasi Pendamping",
                "pesan"=>"Jumlah pendamping yang diisi (".count($pendampingRows).") tidak sama dengan jumlah pendamping ($diminta)."
            ]);
        }
        $email = strtolower(trim($data['email'] ?? ''));
        // 7) Siapkan UPDATE
        $update = [
            "nama_tamu"             => $data['nama_tamu'],
            "jabatan"               => $data['jabatan'],
            "nik"                   => $data['nik'],
            "alamat"                => $data["alamat"] ?? null,
            "no_hp"                 => $data['no_hp'],
            "instansi"              => $data['instansi'] ?? null,
            "unit_tujuan"           => $unit_id,
            "target_kategori"       => $kategori,
            "target_instansi_id"    => $instansiId,
            "target_instansi_nama"  => $instansiNama,
            "nama_petugas_instansi" => $nama_petugas_instansi,
            "keperluan"             => $data['keperluan'] ?? null,
            "surat_tugas"           => $surat_tugas,
            "jumlah_pendamping"     => $diminta,
            "tanggal"               => $tanggal,
            "jam"                   => $jam,
            "foto"                  => $foto,
            "tanggal_lahir"         => $this->normalize_date_mysql($data['tanggal_lahir'] ?? ''),
            "tempat_lahir"          => $data["tempat_lahir"] ?? null,
            "update_date"           => date('Y-m-d H:i:s'),
        ];
        if ($this->db->field_exists('edit_count', 'booking_tamu')) {
            $update['edit_count'] = (int)($booking->edit_count ?? 0) + 1;
        }
        if ($this->db->field_exists('email','booking_tamu')) {
            $update['email'] = $email ?: null;
        }

        // 8) Transaksi + cek kuota (exclude record sendiri bila pindah tanggal/unit)
        $this->db->trans_begin();

        $pindahTanggalAtauUnit = ((string)$booking->tanggal !== (string)$tanggal) || ((int)$booking->unit_tujuan !== (int)$unit_id);

        if ($pindahTanggalAtauUnit) {
            $unitRow = $this->db->query('SELECT kuota_harian, nama_unit FROM unit_tujuan WHERE id=? FOR UPDATE', [$unit_id])->row();
            if (!$unitRow) {
                $this->db->trans_rollback();
                return $this->_json(["success"=>false,"title"=>"Unit Tidak Ditemukan","pesan"=>"Unit tujuan tidak ditemukan."], 404);
            }
            $kuota = isset($unitRow->kuota_harian) ? (int)$unitRow->kuota_harian : 0;
            $kuota = $kuota > 0 ? $kuota : null;

            $this->db->select('COUNT(1) AS jml', false)
                ->from('booking_tamu')
                ->where('unit_tujuan', $unit_id)
                ->where('tanggal', $tanggal)
                ->where('kode_booking !=', $booking->kode_booking);
            if ($this->db->field_exists('status', 'booking_tamu')) {
                $this->db->where_in('status', ['pending','approved','checked_in']);
            }
            if ($this->db->field_exists('token_revoked', 'booking_tamu')) {
                $this->db->where('token_revoked', 0);
            }
            if ($this->db->field_exists('checkout_at', 'booking_tamu')) {
                $this->db->group_start()
                    ->where('checkout_at IS NULL', NULL, FALSE)
                    ->or_where('checkout_at', '')
                    ->group_end();
            }
            $terpakai = (int)($this->db->get()->row('jml') ?? 0);

            if ($kuota !== null && $terpakai >= $kuota) {
                $this->db->trans_rollback();
                $tgl_view = date('d-m-Y', strtotime($tanggal));
                return $this->_json([
                    "success"=>false,"title"=>"Kuota Penuh",
                    "pesan"=>"Kuota harian untuk unit <b>".htmlspecialchars($unitRow->nama_unit,ENT_QUOTES,'UTF-8')."</b> pada tanggal <b>{$tgl_view}</b> sudah penuh."
                ]);
            }
        }

        $this->db->where('kode_booking', $booking->kode_booking)
                 ->where('access_token', $booking->access_token)
                 ->update('booking_tamu', $update);

        if ($this->db->affected_rows() < 0) {
            $err = $this->db->error();
            $this->db->trans_rollback();
            return $this->_json(["success"=>false,"title"=>"Gagal Menyimpan","pesan"=>"Terjadi kendala saat menyimpan. [DB-{$err['code']}]"]);
        }

        $this->db->delete('booking_pendamping', ['kode_booking' => $booking->kode_booking]);
        if (!empty($pendampingRows)) {
            $values = [];
            foreach ($pendampingRows as $r) {
                $values[] = $this->db->escape($booking->kode_booking).','.$this->db->escape($r['nik']).','.$this->db->escape($r['nama']);
            }
            $sql = "INSERT INTO booking_pendamping (kode_booking, nik, nama) VALUES (".implode('),(', $values).")";
            $okPend = $this->db->query($sql);
            if (!$okPend) {
                $err = $this->db->error();
                $this->db->trans_rollback();
                return $this->_json(["success"=>false,"title"=>"Gagal Menyimpan Pendamping","pesan"=>"Silakan ulangi. [DB-{$err['code']}]"]);
            }
        }

        $this->db->trans_commit();

        $fresh = $this->db->get_where('booking_tamu', [
            'kode_booking' => $booking->kode_booking,
            'access_token' => $booking->access_token
        ])->row();

        $critical_changed = $pindahTanggalAtauUnit
            || ((string)$booking->jam !== (string)$jam)
            || ((string)$booking->no_hp !== (string)$update['no_hp'])
            || ((string)$booking->nama_tamu !== (string)$update['nama_tamu'])
            || ((int)$booking->jumlah_pendamping !== (int)$diminta)
            || ((string)($booking->keperluan ?? '') !== (string)($update['keperluan'] ?? ''));

        $force = ((int)$this->input->post('resend_wa', true) === 1)
              || ((int)$this->input->post('force_wa',  true) === 1);

        if ($fresh && ($force || $critical_changed)) {
            $this->_resend_wa_after_update($fresh, $force);
        }

        $redirect_url = site_url('booking/booked?t='.urlencode($booking->access_token));
        return $this->_json([
            "success"      => true,
            "title"        => "Tersimpan",
            "pesan"        => "Perubahan telah disimpan."
                               .(!$fresh ? "" : ($force ? " (WhatsApp dikirim ulang - paksa)"
                                               : ($critical_changed ? " (WhatsApp dikirim ulang)" : ""))),
            "redirect_url" => $redirect_url
        ]);
    }

    private function _resend_wa_after_update($b, bool $force = false): void
    {
        if (!$b) return;
        if ((int)$b->token_revoked === 1) return;
        if (!empty($b->checkout_at))     return;

        $qr_rel = 'uploads/qr/qr_'.$b->kode_booking.'.png';
        $qr_abs = FCPATH.$qr_rel;
        if (!is_file($qr_abs)) { $this->_make_qr($b->kode_booking); }
        $qr_url = base_url($qr_rel);

        $redir   = site_url('booking/booked?t='.urlencode($b->access_token));
        $pdf_url = site_url('booking/download_gate').'?k='.rawurlencode($b->kode_booking).'&t='.urlencode($b->access_token);

        $instansi = $b->target_instansi_nama ?: ($b->instansi ?: '-');

        // TAMU
        $ok_user = $this->_send_wa_konfirmasi($b->no_hp, [
            'is_update'             => true,
            'access_token'          => $b->access_token,
            'nama'                  => $b->nama_tamu,
            'nama_petugas_instansi' => $b->nama_petugas_instansi,
            'kode'                  => $b->kode_booking,
            'instansi_asal'         => $instansi,
            'unit_tujuan'           => $this->_safe_unit_name((int)$b->unit_tujuan) ?: '-',
            'tanggal'               => $b->tanggal,
            'jam'                   => $b->jam,
            'qr_url'                => $qr_url,
            'redirect_url'          => $redir,
            'pdf_url'               => $pdf_url,
            'keperluan'             => $b->keperluan ?: '-',
        ]);
        if ($ok_user && $this->db->field_exists('wa_sent_at','booking_tamu')) {
            $this->db->where('kode_booking', $b->kode_booking)
                     ->where('access_token', $b->access_token)
                     ->where('wa_sent_at IS NULL', NULL, FALSE)
                     ->update('booking_tamu', ['wa_sent_at' => date('Y-m-d H:i:s')]);
        }

        // EMAIL TAMU saat update
        if (!empty($b->email) && filter_var($b->email, FILTER_VALIDATE_EMAIL)) {
            $ok_mail = $this->_send_email_konfirmasi($b->email, [
                'is_update'             => true,
                'access_token'          => $b->access_token,
                'nama'                  => $b->nama_tamu,
                'nama_petugas_instansi' => $b->nama_petugas_instansi,
                'kode'                  => $b->kode_booking,
                'instansi_asal'         => $instansi,
                'unit_tujuan'           => $this->_safe_unit_name((int)$b->unit_tujuan) ?: '-',
                'tanggal'               => $b->tanggal,
                'jam'                   => $b->jam,
                'qr_url'                => $qr_url,
                'redirect_url'          => $redir,
                'pdf_url'               => $pdf_url,
                'keperluan'             => $b->keperluan ?: '-',
            ]);
            if ($ok_mail && $this->db->field_exists('email_sent_at','booking_tamu')) {
                $this->db->where('kode_booking', $b->kode_booking)
                         ->where('access_token', $b->access_token)
                         ->where('email_sent_at IS NULL', NULL, FALSE)
                         ->update('booking_tamu', ['email_sent_at' => date('Y-m-d H:i:s')]);
            }
        }


        // UNIT TUJUAN
        $unit_row = $this->db->select('id, nama_unit, nama_pejabat, parent_id, no_hp', false)
                             ->get_where('unit_tujuan', ['id' => (int)$b->unit_tujuan])->row();
        $unit_nama_db    = $unit_row->nama_unit    ?? '-';
        $unit_pejabat_db = $unit_row->nama_pejabat ?? '';
        $parent_id       = $unit_row->parent_id    ?? null;

        $hp_unit = $this->_extract_phone_from_row($unit_row);
        if (!$hp_unit) {
            list($hp_unit_fb, $nm_fb, $pej_fb) = $this->_get_unit_contact((int)$b->unit_tujuan);
            if ($hp_unit_fb) {
                $hp_unit         = $hp_unit_fb;
                $unit_nama_db    = $unit_nama_db ?: ($nm_fb ?: '-');
                $unit_pejabat_db = $unit_pejabat_db ?: ($pej_fb ?: '');
            }
        }

        if (!empty($hp_unit)) {
            $ok_unit = $this->_send_wa_info_unit($hp_unit, [
                'is_update'        => true,
                'kode'             => $b->kode_booking,
                'nama'             => $b->nama_tamu,
                'hp_tamu'          => $b->no_hp,
                'instansi_asal'    => $instansi,
                'unit_nama'        => $unit_nama_db,
                'unit_pejabat'     => $unit_pejabat_db,
                'child_unit_nama'  => $unit_nama_db,
                'tanggal'          => $b->tanggal,
                'jam'              => $b->jam,
                'pendamping'       => (int)$b->jumlah_pendamping,
                'keperluan'        => $b->keperluan ?: '-',
                'redirect_url'     => $redir,
                'qr_url'           => $qr_url,
                'is_cc'            => false,
            ]);
            if ($ok_unit && $this->db->field_exists('wa_unit_sent_at','booking_tamu')) {
                $this->db->where('kode_booking', $b->kode_booking)
                         ->where('access_token', $b->access_token)
                         ->where('wa_unit_sent_at IS NULL', NULL, FALSE)
                         ->update('booking_tamu', ['wa_unit_sent_at' => date('Y-m-d H:i:s')]);
            }
        }

        // TEMBUSAN PARENT
        if (!empty($parent_id)) {
            $parent_row = $this->db->select('id, nama_unit, nama_pejabat, no_hp', false)
                                   ->get_where('unit_tujuan', ['id' => (int)$parent_id])->row();

            $hp_parent = $this->_extract_phone_from_row($parent_row);
            if (!$hp_parent && $parent_row) {
                list($hp_parent_fb, $nm_parent_fb, $pej_parent_fb) = $this->_get_unit_contact((int)$parent_row->id);
                if ($hp_parent_fb) {
                    $hp_parent               = $hp_parent_fb;
                    $parent_row->nama_unit   = $parent_row->nama_unit   ?: $nm_parent_fb;
                    $parent_row->nama_pejabat= $parent_row->nama_pejabat?: $pej_parent_fb;
                }
            }

            if (!empty($hp_parent)) {
                $ok_cc = $this->_send_wa_info_unit($hp_parent, [
                    'is_update'        => true,
                    'kode'             => $b->kode_booking,
                    'nama'             => $b->nama_tamu,
                    'hp_tamu'          => $b->no_hp,
                    'instansi_asal'    => $instansi,
                    'unit_nama'        => $parent_row->nama_unit ?? '-',
                    'unit_pejabat'     => $parent_row->nama_pejabat ?? '',
                    'child_unit_nama'  => $unit_nama_db,
                    'tanggal'          => $b->tanggal,
                    'jam'              => $b->jam,
                    'pendamping'       => (int)$b->jumlah_pendamping,
                    'keperluan'        => $b->keperluan ?: '-',
                    'redirect_url'     => $redir,
                    'qr_url'           => $qr_url,
                    'is_cc'            => true,
                ]);
                if ($ok_cc && $this->db->field_exists('wa_parent_sent_at','booking_tamu')) {
                    $this->db->where('kode_booking', $b->kode_booking)
                             ->where('access_token', $b->access_token)
                             ->where('wa_parent_sent_at IS NULL', NULL, FALSE)
                             ->update('booking_tamu', ['wa_parent_sent_at' => date('Y-m-d H:i:s')]);
                }
            }
        }
    }

    private function _safe_unit_name(int $unit_id): ?string
    {
        return $this->db->select('nama_unit')->get_where('unit_tujuan', ['id'=>$unit_id])->row('nama_unit');
    }

    private function _app_tz(): DateTimeZone {
        $web = (isset($this->fm) && method_exists($this->fm,'web_me')) ? $this->fm->web_me() : null;
        $tzName = !empty($web->waktu) ? (string)$web->waktu : date_default_timezone_get();
        try { return new DateTimeZone($tzName); } catch (\Throwable $e) { return new DateTimeZone(date_default_timezone_get()); }
    }

    /** Validasi kunci: boleh edit?
     *  - Maksimal edit = $web->batas_edit (0 = nonaktif)
     *  - H-n = $web->batas_hari (edit hanya bila selisih >= n)
     */
    private function _check_edit_lock($booking): array {
        list($batas_edit, $batas_hari) = $this->_get_edit_limits();

        // 0 = fitur edit dimatikan
        if ($batas_edit === 0) {
            return [false, 'Fitur ubah data saat ini dinonaktifkan.'];
        }

        $cnt = (int)($booking->edit_count ?? 0);
        if ($cnt >= $batas_edit) {
            return [false, 'Anda sudah mencapai batas maksimal '.$batas_edit.' kali perubahan.'];
        }

        try {
            $tz    = $this->_app_tz();
            $today = new DateTime('today', $tz);
            $visit = new DateTime((string)$booking->tanggal, $tz); $visit->setTime(0,0,0);
            $days  = (int)$today->diff($visit)->format('%r%a');

            if ($days < $batas_hari) {
                return [false, 'Perubahan hanya dapat dilakukan maksimal H-'.$batas_hari.' dari tanggal kunjungan.'];
            }
        } catch (\Throwable $e) {
            return [false, 'Tanggal kunjungan tidak valid untuk pembatasan H-'.$batas_hari.'.'];
        }

        return [true, ''];
    }


    /** Ambil limit edit dari konfigurasi web_me()
     *  - batas_edit: maksimal jumlah perubahan (0 = nonaktif, default 1)
     *  - batas_hari: minimal jarak hari (H-n) agar masih boleh edit (default 2)
     */
    private function _get_edit_limits(): array {
        $web = $this->fm->web_me();

        $batas_edit = (int)($web->batas_edit ?? 1);
        // clamp biar aman
        $batas_edit = max(0, min(10, $batas_edit));

        $batas_hari = (int)($web->batas_hari ?? 2);
        $batas_hari = max(0, min(30, $batas_hari));

        return [$batas_edit, $batas_hari];
    }

    public function hapus()
    {
        // Wajib POST
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            return $this->_json_bad('Metode tidak diizinkan.', 405);
        }

        $token = $this->input->post('t', true);
        $kode  = $this->input->post('kode', true);

        if (!$token || !$kode) {
            return $this->_json_bad('Parameter tidak lengkap.', 400);
        }

        // Ambil booking by token + kode
        $booking = $this->db->get_where('booking_tamu', [
            'access_token'  => $token,
            'kode_booking'  => $kode
        ])->row();

        if (!$booking) {
            return $this->_json_bad('Data tidak ditemukan atau token tidak valid.', 404);
        }

        // Boleh hapus? (lock jika sudah check-in/checked-out)
        $st = strtolower((string)$booking->status);
        $locked = in_array($st, ['checked_in','checked_out'], true);
        if ($locked) {
            return $this->_json_bad('Permohonan tidak dapat dihapus karena sudah dalam status kunjungan.', 400);
        }

        $this->db->trans_begin();

        try {
            // Hapus pendamping
            $this->db->delete('booking_pendamping', ['kode_booking' => $booking->kode_booking]);

            // Simpan path untuk dihapus setelah commit
            $paths = [];
            if (!empty($booking->foto)) {
                $paths[] = FCPATH.'uploads/foto/'.basename($booking->foto);
            }
            if (!empty($booking->surat_tugas)) {
                $paths[] = FCPATH.'uploads/surat_tugas/'.basename($booking->surat_tugas);
            }
            // QR
            $paths[] = FCPATH.'uploads/qr/qr_'.$booking->kode_booking.'.png';

            // Hapus booking
            $this->db->delete('booking_tamu', ['id_booking' => $booking->id_booking]);

            if ($this->db->trans_status() === false) {
                throw new Exception('Gagal menghapus data.');
            }
            $this->db->trans_commit();

            // Hapus file-file (di luar transaksi DB)
            foreach ($paths as $p) {
                if ($p && is_file($p)) { @unlink($p); }
            }

            return $this->_json_ok(['ok'=>true]);
        } catch (Throwable $e) {
            $this->db->trans_rollback();
            return $this->_json_bad($e->getMessage() ?: 'Terjadi galat saat menghapus.');
        }
    }

    // Helper JSON singkat
    private function _json_ok($data = [], $code = 200) {
        $this->output->set_status_header($code)
                     ->set_content_type('application/json','utf-8')
                     ->set_output(json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    }
    private function _json_bad($msg = 'Gagal', $code = 400) {
        $this->output->set_status_header($code)
                     ->set_content_type('application/json','utf-8')
                     ->set_output(json_encode(['ok'=>false,'msg'=>$msg], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    }

    private function _send_email_konfirmasi(string $to, array $d): bool
    {
        $to = trim($to);
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) return false;

        // init + dapatkan FROM
        list($from, $from_name) = $this->_init_email_and_from();

        // app/web info
        $web      = $this->fm->web_me();
        $app_name = $web->nama_website ?? 'Aplikasi';

        // payload utk template (sama seperti sebelumnya) ...
        $payload = [
            'is_update'             => !empty($d['is_update']),
            'kode'                  => (string)($d['kode'] ?? ''),
            'nama'                  => (string)($d['nama'] ?? '-'),
            'instansi_asal'         => (string)($d['instansi_asal'] ?? '-'),
            'unit_tujuan'           => (string)($d['unit_tujuan'] ?? ($d['child_unit_nama'] ?? ($d['unit_nama'] ?? '-'))),
            'nama_petugas_instansi' => (string)($d['nama_petugas_instansi'] ?? ($d['unit_pejabat'] ?? '')),
            'tanggal'               => !empty($d['tanggal']) ? date('d-m-Y', strtotime($d['tanggal'])) : '-',
            'jam'                   => (string)($d['jam'] ?? '-'),
            'keperluan'             => (string)($d['keperluan'] ?? '-'),
            'redirect_url'          => (string)($d['redirect_url'] ?? site_url('booking')),
            'pdf_url'               => (string)($d['pdf_url'] ?? ''),
            'qr_url'                => (string)($d['qr_url'] ?? ''),
            'app_name'              => $app_name,
        ];

        $html = $this->load->view('front_end/mail_notif', $payload, true);

        $this->load->library('email');
        $this->email->clear(true);
        $this->email->from($from, $from_name ?: $app_name);
        $this->email->to($to);
        $subj = ($payload['is_update'] ? '[Perubahan] ' : '[Konfirmasi] ')."Booking {$payload['kode']} – {$app_name}";
        $this->email->subject($subj);
        $this->email->set_mailtype('html');
        $this->email->set_newline("\r\n");
        $this->email->message($html);

        try {
            $ok = (bool)$this->email->send();
            if (!$ok) {
                // dump error ke log supaya gampang debug
                $dbg = $this->email->print_debugger(['headers']);
                log_message('error', 'email send failed: '.$dbg);
            }
            return $ok;
        } catch (Throwable $e) {
            log_message('error', 'email send exception: '.$e->getMessage());
            return false;
        }
    }


    /** Init Email library tanpa application/config/email.php
     *  Ambil dari ENV jika ada; kalau tidak, fallback ke nilai default.
     *  Return: [from_email, from_name]
     */

    private function _init_email_and_from(): array
    {
        $this->load->library('email');

        // 1) Coba ambil dari DB 'identitas'
        $rec = $this->fm->web_me();

        if ($rec && !empty($rec->smtp_active)) {
            $app_name   = ($this->fm->web_me()->nama_website ?? 'Aplikasi');
            $smtp_host  = $rec->smtp_host ?: '';
            $smtp_user  = $rec->smtp_user ?: '';
            $smtp_pass  = $rec->smtp_pass ?: '';
            $smtp_port  = (int)($rec->smtp_port ?: 0);
            $smtp_crypto= $rec->smtp_crypto ?: ''; // '', 'ssl', 'tls'
            $from_email = $rec->smtp_from ?: ($smtp_user ?: 'noreply@localhost.localdomain');
            $from_name  = $rec->smtp_from_name ?: $app_name;

            if ($smtp_host !== '' && $smtp_user !== '' && $smtp_pass !== '' && $smtp_port > 0) {
                $cfg = [
                    'protocol'    => 'smtp',
                    'smtp_host'   => $smtp_host,
                    'smtp_user'   => $smtp_user,
                    'smtp_pass'   => $smtp_pass,
                    'smtp_port'   => $smtp_port,
                    'smtp_crypto' => ($smtp_crypto?:null),
                    'mailtype'    => 'html',
                    'charset'     => 'utf-8',
                    'newline'     => "\r\n",
                    'crlf'        => "\r\n",
                    'wordwrap'    => true,
                    'validate'    => true,
                ];
                $this->email->initialize($cfg);
                return [$from_email, $from_name];
            }
            // kalau aktif tapi belum lengkap → lanjut ke fallback ENV
        }

        // 2) Fallback ENV (lama)
        $smtp_host   = getenv('SMTP_HOST')   ?: 'smtp.hostinger.com';
        $smtp_user   = getenv('SMTP_USER')   ?: 'admin@silaturahmi.org';
        $smtp_pass   = getenv('SMTP_PASS')   ?: '100Ribxdurupiah@'; // GMAIL: pakai App Password!
        $smtp_port   = (int)(getenv('SMTP_PORT') ?: 465);
        $smtp_crypto = getenv('SMTP_CRYPTO') ?: 'ssl';            // 'ssl' (465) atau 'tls' (587)
        $from_email  = getenv('SMTP_FROM')  ?: $smtp_user;
        $from_name   = getenv('SMTP_FROM_NAME') ?: (($rec->nama_website ?? 'Aplikasi'));

        $cfg = [
            'protocol'    => 'smtp',
            'smtp_host'   => $smtp_host,
            'smtp_user'   => $smtp_user,
            'smtp_pass'   => $smtp_pass,
            'smtp_port'   => $smtp_port,
            'smtp_crypto' => $smtp_crypto,
            'mailtype'    => 'html',
            'charset'     => 'utf-8',
            'newline'     => "\r\n",
            'crlf'        => "\r\n",
            'wordwrap'    => true,
            'validate'    => true,
        ];
        $this->email->initialize($cfg);
        return [$from_email, $from_name];
    }




}
