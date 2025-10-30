<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_dashboard extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("M_admin_dashboard","ma");
        // date_default_timezone_set('Asia/Makassar');
        // cek_session_akses(get_class($this), $this->session->userdata('admin_session'));
        
    }

    /* =========================================================
     *               Helper JSON (aman & ringkas)
     * ========================================================= */
    private function json_exit($payload, int $status = 200, array $headers = [])
    {
        if (!is_string($payload)) {
            $payload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        foreach ($headers as $k => $v) {
            $this->output->set_header($k . ': ' . $v);
        }
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output($payload)
            ->_display();
        exit; // pastikan eksekusi berhenti
    }

    private function json_flush($payload, int $status = 200, array $headers = [])
    {
        if (!is_string($payload)) {
            $payload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        foreach ($headers as $k => $v) {
            $this->output->set_header($k . ': ' . $v);
        }
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output($payload)
            ->_display();

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request(); // PHP-FPM
        } else {
            @ob_end_flush(); @flush();
        }
        // tidak exit; proses bisa lanjut bila diperlukan
    }

    /* =========================================================
     *                     ROUTE UTAMA
     * ========================================================= */
    public function index()
    {   
        cek_session_akses(get_class($this), $this->session->userdata('admin_session'));
        // Langsung arahkan ke dashboard (default bulanan)
        return $this->dashboard();
    }

   

   // di dalam class controller yang sama
    private function _has_access(string $link): bool
    {
        $id    = $this->session->userdata('admin_session');
        $level = $this->session->userdata('admin_level');
        if (in_array($level, ['admin','su'], true)) return true;

    // exact
        $q = $this->db->select('1', false)
        ->from('modul')
        ->join('users_modul', 'modul.id_modul = users_modul.id_modul')
        ->where('id_session', $id)
        ->group_start()
        ->where('link', $link)
           ->or_where('link', strtok($link,'/')) // fallback: segmen pertama
           ->group_end()
           ->limit(1)->get();
           return $q->num_rows() > 0;
       }



    /* =========================================================
     *                    MONITOR (LAYAR UTAMA)
     * ========================================================= */
    public function monitor()
    {   
        cek_session_akses(get_class($this)."/monitor", $this->session->userdata('admin_session'));
        // $data["record"]    = $this->om->profil("users")->row();
        $data["controller"]= get_class($this);
        $data["title"]     = "Layar Utama";
        $data["subtitle"]  = "Daftar Booking & Sedang Berkunjung";
        $data["content"]   = $this->load->view('Admin_dashboard_monitor_view', $data, true);
        $this->render($data);
    }

 public function wall()
    {
        cek_session_akses(get_class($this)."/wall", $this->session->userdata('admin_session'));
        $data["controller"]  = get_class($this);
        $data["title"]       = "Wallboard";
        $data["subtitle"]    = "Monitor & Dashboard";
        $data["cycle"]       = 20;           // detik rotasi
        $data["default_tab"] = "monitor";    // monitor | dashboard
        $data["content"]     = $this->load->view('Admin_wall_tabs_view', $data, true);
        $this->render($data);
    }


    /**
     * API data layar utama
     * GET:
     *   q        = keyword (opsional)
     *   page     = halaman (default 1)
     *   per_page = jumlah baris per halaman (default 15, max 100)
     * Return JSON:
     *   - booked       : daftar semua booking (pending/approved) yang BELUM check-in (tanpa filter tanggal)
     *   - in_visit     : daftar yang sudah check-in & belum checkout
     *   - server_time  : waktu server ISO8601 (untuk jam live di UI)
     */
    private function _require_access_or_json(string $link): void {
    if (! $this->_has_access($link)) {
        $this->json_exit(['ok'=>false,'err'=>'forbidden'], 403);
    }
}

public function monitor_data()
{
    $this->_require_access_or_json(get_class($this)."/monitor");

    $q        = trim((string)$this->input->get('q', true));
    $page     = max(1, (int)$this->input->get('page'));
    $per_page = (int)$this->input->get('per_page');
    if ($per_page <= 0 || $per_page > 100) $per_page = 15;
    $offset   = ($page - 1) * $per_page;
    $like     = ($q !== '') ? $this->db->escape_like_str($q) : null;

    // ===== helper filter "Sudah Booking" (pending/approved, belum check-in, TIDAK expired)
    $apply_filters = function() use ($like) {
        $this->db->where('b.checkin_at IS NULL', null, false);
        $this->db->where_in('b.status', ['approved','pending']); // cukup pakai kolom langsung (collation sudah _ci)
        $this->db->where('b.status <>', 'expired');              // pastikan bukan expired
        if ($like !== null) {
            $this->db->group_start()
                ->like('b.kode_booking',            $like, 'both', false)
                ->or_like('b.nama_tamu',            $like, 'both', false)
                ->or_like('b.nik',                  $like, 'both', false)
                ->or_like('b.no_hp',                $like, 'both', false)
                ->or_like('b.keperluan',            $like, 'both', false)
                ->or_like('b.target_instansi_nama', $like, 'both', false)
                ->or_like('b.instansi',             $like, 'both', false)
                ->or_like('u.nama_unit',            $like, 'both', false)
                ->or_like('b.nama_petugas_instansi',$like, 'both', false)
            ->group_end();
        }
    };

    // ===== total booked (untuk pagination)
    $this->db->reset_query();
    $this->db->select('b.id_booking');
    $this->db->from('booking_tamu b');
    $this->db->join('unit_tujuan u','u.id=b.unit_tujuan','left');
    $apply_filters();
    $booked_total = (int)$this->db->count_all_results();

    // ===== data booked (halaman berjalan)
    $this->db->reset_query();
    $this->db->select('b.*, u.nama_unit');
    $this->db->from('booking_tamu b');
    $this->db->join('unit_tujuan u','u.id=b.unit_tujuan','left');
    $apply_filters();
    $this->db->order_by('b.tanggal','ASC');
    $this->db->order_by('b.jam','ASC');
    $this->db->limit($per_page, $offset);
    $booked_rows = $this->db->get()->result_array();

    // ===== Sedang berkunjung (sudah check-in & belum checkout), TIDAK expired
    $this->db->reset_query();
    $this->db->select('b.*, u.nama_unit');
    $this->db->from('booking_tamu b');
    $this->db->join('unit_tujuan u','u.id=b.unit_tujuan','left');
    $this->db->where('b.checkin_at IS NOT NULL', null, false);
    $this->db->where('b.checkout_at IS NULL', null, false);
    $this->db->where('b.status <>', 'expired');          // exclude expired
    // (opsional lebih ketat) kalau mau hanya yang status 'checked_in', aktifkan baris di bawah:
    // $this->db->where('b.status', 'checked_in');
    $this->db->order_by('b.checkin_at','DESC');
    $invisit = $this->db->get()->result_array();

    // ===== mapper baris ke format UI (pakai nama_petugas_instansi dari booking_tamu)
    $can_permohonan = $this->_has_access('admin_permohonan/detail');
    $can_scan       = $this->_has_access('admin_scan/detail');

    $map_booked = function($r) use ($can_permohonan) {
        $petugas = trim((string)($r['nama_petugas_instansi'] ?? ''));
        $pend    = isset($r['jumlah_pendamping']) ? (int)$r['jumlah_pendamping'] : 0;
        return [
            'kode'                  => (string)$r['kode_booking'],
            'nama'                  => (string)$r['nama_tamu'],
            'jabatan'                  => (string)$r['jabatan'],
            'unit'                  => (string)($r['nama_unit'] ?: '-'),
            'instansi'              => (string)($r['target_instansi_nama'] ?: ($r['instansi'] ?: '-')),
            'jam'                   => (string)($r['jam'] ?: ''),
            'tanggal'               => (string)($r['tanggal'] ?: ''),
            'status'                => strtolower((string)$r['status']),
            'checkin_at'            => (string)($r['checkin_at'] ?: ''),
            'checkout_at'           => (string)($r['checkout_at'] ?: ''),
            'jumlah_pendamping'     => $pend,
            'logo_ins'              => kategori_logo_url($r['target_kategori'] ?: ''),
            'nama_petugas_instansi' => $petugas,
            // === diarahkan ke admin_permohonan/detail & boleh klik jika punya akses
            'detail_url'            => $can_permohonan ? site_url('admin_permohonan/detail/'.rawurlencode($r['kode_booking'])) : '',
            'can_open'              => (bool)$can_permohonan,
        ];
    };
    $map_visit = function($r) use ($can_scan) {
        $petugas = trim((string)($r['nama_petugas_instansi'] ?? ''));
        $pend    = isset($r['jumlah_pendamping']) ? (int)$r['jumlah_pendamping'] : 0;
        return [
            'kode'                  => (string)$r['kode_booking'],
            'nama'                  => (string)$r['nama_tamu'],
            'unit'                  => (string)($r['nama_unit'] ?: '-'),
            'instansi'              => (string)($r['target_instansi_nama'] ?: ($r['instansi'] ?: '-')),
            'jam'                   => (string)($r['jam'] ?: ''),
            'tanggal'               => (string)($r['tanggal'] ?: ''),
            'status'                => strtolower((string)$r['status']),
            'checkin_at'            => (string)($r['checkin_at'] ?: ''),
            'checkout_at'           => (string)($r['checkout_at'] ?: ''),
            'jumlah_pendamping'     => $pend,
            'nama_petugas_instansi' => $petugas,
            // === tetap admin_scan/detail, tapi bisa dinonaktifkan kalau tak punya akses
            'detail_url'            => $can_scan ? site_url('admin_scan/detail/'.rawurlencode($r['kode_booking'])) : '',
            'can_open'              => (bool)$can_scan,
        ];
    };

    $total_pages = ($booked_total > 0) ? (int)ceil($booked_total / $per_page) : 0;
          $web    = $this->om->web_me();
        $tzName = $web->waktu ?: 'Asia/Makassar';
        $tz     = new DateTimeZone($tzName);
        $nowTz  = new DateTimeImmutable('now', $tz);

        $this->json_exit([
          'ok'           => true,
          'scope'        => 'all',
          'q'            => $q,
          'page'         => $page,
          'per_page'     => $per_page,
          'booked_total' => $booked_total,
          'booked_pages' => $total_pages,
          'booked'       => array_map($map_booked, $booked_rows),
          'in_visit'     => array_map($map_visit,  $invisit),
          'count_booked' => $booked_total,
          'count_visit'  => count($invisit),

          // waktu server berdasar TZ dari DB
          'server_tz'    => $tzName,                         // contoh: "Asia/Makassar"
          'server_time'  => $nowTz->format(DateTime::ATOM),  // contoh: "2025-09-12T14:05:00+08:00"
          'server_ms'    => (int) round(microtime(true) * 1000) // epoch ms
        ]);
    }




    /* =========================================================
     *                        DASHBOARD
     * ========================================================= */
    public function dashboard()
    {
        cek_session_akses(get_class($this), $this->session->userdata('admin_session'));
        $data["controller"]     = get_class($this);
        $data["title"]          = "Dashboard Kunjungan";
        $data["subtitle"]       = "Harian • Mingguan • Bulanan";
        $data["default_period"] = "month"; // default tampilan = Bulanan

        // ganti sesuai nama file view dashboard Anda
        $view_file = 'Admin_dashboard_dash';

        $data["content"] = $this->load->view($view_file, $data, true);
        $this->render($data);
    }

    /**
     * API data dashboard
     * GET:
     *   period = day | week | month   (default: month)
     *   date   = YYYY-MM-DD           (opsional; default: hari ini)
     *
     * Output:
     *   totals: { visitors, unique_instansi }
     *   top_instansi: [{label, qty}...]
     *   top_units   : [{label, qty}...]
     *   trend: { labels:[], data:[] }  // day=per jam, week=per hari (Sen-Min), month=per tanggal
     */
    public function dashboard_data()
    {
        cek_session_akses(get_class($this), $this->session->userdata('admin_session'));
        $period = strtolower((string)$this->input->get('period', true));
        if (!in_array($period, ['day','week','month'])) {
            $period = 'month'; // default bulanan
        }

        $dateParam = $this->input->get('date', true);
        $baseTs    = $dateParam ? strtotime($dateParam) : time();

        if ($period === 'day') {
            $start     = date('Y-m-d 00:00:00', $baseTs);
            $end       = date('Y-m-d 23:59:59', $baseTs);
            $trendBins = range(0, 23); // per jam
        } elseif ($period === 'week') {
            $monday    = strtotime('monday this week', $baseTs);
            $start     = date('Y-m-d 00:00:00', $monday);
            $end       = date('Y-m-d 23:59:59', strtotime('sunday this week', $baseTs));
            $trendBins = range(0, 6); // 0..6 (Sen..Min)
        } else { // 'month'
            $first     = strtotime(date('Y-m-01', $baseTs));
            $last      = strtotime(date('Y-m-t',  $baseTs));
            $start     = date('Y-m-d 00:00:00', $first);
            $end       = date('Y-m-d 23:59:59', $last);
            $trendBins = range(1, (int)date('t', $first)); // 1..t
        }

        $db      = $this->db;
        $between = "checkin_at BETWEEN ".$db->escape($start)." AND ".$db->escape($end);

        // Total pengunjung (berdasar yang check-in pada periode)
        $rowTotal = $db->query("SELECT COUNT(*) c FROM booking_tamu WHERE $between")->row_array();
        $totalVisitors = (int)($rowTotal['c'] ?? 0);

        // Jumlah instansi unik (asal kunjungan) selama periode
        $rowInstUniq = $db->query("
            SELECT COUNT(DISTINCT NULLIF(COALESCE(NULLIF(target_instansi_nama,''), NULLIF(instansi,'')), '')) c
            FROM booking_tamu WHERE $between
        ")->row_array();
        $uniqueInstansi = (int)($rowInstUniq['c'] ?? 0);

        // Top Instansi (asal)
        $topInst = $db->query("
            SELECT 
                COALESCE(NULLIF(target_instansi_nama,''), NULLIF(instansi,''), 'Tidak diketahui') AS label,
                COUNT(*) AS qty
            FROM booking_tamu
            WHERE $between
            GROUP BY label
            ORDER BY qty DESC
            LIMIT 5
        ")->result_array();

        // Top Unit (teramai)
        $topUnit = $db->query("
            SELECT 
                COALESCE(u.nama_unit, CONCAT('Unit#', bt.unit_tujuan)) AS label,
                COUNT(*) AS qty
            FROM booking_tamu bt
            LEFT JOIN unit_tujuan u ON u.id = bt.unit_tujuan
            WHERE $between
            GROUP BY label
            ORDER BY qty DESC
            LIMIT 5
        ")->result_array();

        // Tren (sesuai period)
        if ($period === 'day') {
            $rows = $db->query("
                SELECT HOUR(checkin_at) h, COUNT(*) c
                FROM booking_tamu
                WHERE $between
                GROUP BY HOUR(checkin_at)
            ")->result_array();
            $map    = [];
            foreach ($rows as $r) $map[(int)$r['h']] = (int)$r['c'];
            $labels = array_map(function($h){ return sprintf('%02d:00', $h); }, $trendBins);
            $data   = array_map(function($h) use ($map){ return $map[$h] ?? 0; }, $trendBins);
        } elseif ($period === 'week') {
            $rows = $db->query("
                SELECT WEEKDAY(DATE(checkin_at)) d, COUNT(*) c
                FROM booking_tamu
                WHERE $between
                GROUP BY WEEKDAY(DATE(checkin_at))
            ")->result_array();
            $map    = [];
            foreach ($rows as $r) $map[(int)$r['d']] = (int)$r['c'];
            $hari   = ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];
            $labels = array_map(function($i) use ($hari){ return $hari[$i]; }, $trendBins);
            $data   = array_map(function($i) use ($map){ return $map[$i] ?? 0; }, $trendBins);
        } else {
            $rows = $db->query("
                SELECT DAY(checkin_at) d, COUNT(*) c
                FROM booking_tamu
                WHERE $between
                GROUP BY DAY(checkin_at)
            ")->result_array();
            $map    = [];
            foreach ($rows as $r) $map[(int)$r['d']] = (int)$r['c'];
            $labels = array_map(function($d){ return (string)$d; }, $trendBins);
            $data   = array_map(function($d) use ($map){ return $map[$d] ?? 0; }, $trendBins);
        }

        $this->json_exit([
            'ok'          => true,
            'period'      => $period,      // day|week|month
            'start'       => $start,
            'end'         => $end,
            'server_time' => date('c'),

            'totals' => [
                'visitors'        => $totalVisitors,
                'unique_instansi' => $uniqueInstansi,
            ],
            'top_instansi' => $topInst,
            'top_units'    => $topUnit,

            'trend' => [
                'labels' => $labels,
                'data'   => $data,
            ],
        ]);
    }


}
