<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_laporan extends Admin_Controller
{
    public function __construct(){
        parent::__construct();
        $this->load->model('M_admin_laporan', 'lm');
        // kalau perlu batasi akses:
        cek_session_akses(get_class($this), $this->session->userdata('admin_session'));
    }

    public function index(){
        $data["controller"] = get_class($this);
        $data["title"]      = "Laporan";
        $data["subtitle"]   = "Ringkasan & Cetak";
        $data["content"]    = $this->load->view('Admin_laporan_view',$data,true);
        $this->render($data);
    }

    private function _sanitize_enum($v, array $allowed, $fallback){
        $v = strtolower(trim((string)$v));
        return in_array($v, $allowed, true) ? $v : $fallback;
    }


    /** ====== AJAX: ringkasan angka (POS / Billiard / Pengeluaran / Laba) ====== */
  // === RINGKASAN DASHBOARD (tetap: Laba = POS + Billiard - Pengeluaran) ===
public function summary_json(){
    $f = $this->_parse_filter();

    $pos  = $this->lm->sum_pos($f)        ?: ['count'=>0,'total'=>0,'by_method'=>[]];
    $bil  = $this->lm->sum_billiard($f)   ?: ['count'=>0,'total'=>0,'by_method'=>[]];
    $peng = $this->lm->sum_pengeluaran($f)?: ['count'=>0,'total'=>0,'by_kategori'=>[]];
    $kur  = $this->lm->sum_kurir($f)      ?: ['count'=>0,'total_fee'=>0,'by_method'=>[]];
    $kp   = $this->lm->sum_kursi_pijat($f)?: ['count'=>0,'total'=>0];

    $pos  = ['count'=>(int)($pos['count']??0),'total'=>(int)($pos['total']??0),'by_method'=>(array)($pos['by_method']??[])];
    $bil  = ['count'=>(int)($bil['count']??0),'total'=>(int)($bil['total']??0),'by_method'=>(array)($bil['by_method']??[])];
    $peng = ['count'=>(int)($peng['count']??0),'total'=>(int)($peng['total']??0),'by_kategori'=>(array)($peng['by_kategori']??[])];
    $kur  = ['count'=>(int)($kur['count']??0),'total_fee'=>(int)($kur['total_fee']??0),'by_method'=>(array)($kur['by_method']??[])];
    $kp   = ['count'=>(int)($kp['count']??0),'total'=>(int)($kp['total']??0)];

    $out = [
        'success'     => true,
        'filter'      => $f,
        'pos'         => $pos,
        'billiard'    => $bil,
        'pengeluaran' => $peng,
        'kurir'       => $kur,
        'kursi_pijat' => $kp,
        'meta'        => [
            'kurir_subset_of_pos' => true,
            'laba_formula'        => 'pos+billiard+kursi_pijat-pengeluaran',
        ],
        'laba'        => ['total' => $pos['total'] + $bil['total'] + $kp['total'] - $peng['total']],
    ];
    return $this->output->set_content_type('application/json','utf-8')->set_output(json_encode($out));
}


public function print_kursi_pijat(){
    $f = $this->_parse_filter();

    $rows = $this->lm->fetch_kursi_pijat($f);
    $sum  = $this->lm->sum_kursi_pijat($f);

    $data = [
        'title'  => 'Laporan Kursi Pijat',
        'period' => $this->_period_label($f),
        'rows'   => $rows,
        'sum'    => $sum,
        'f'      => $f,
        'idr'    => function($x){ return $this->_idr($x); },
    ];

    $safePeriod = preg_replace('/[^0-9A-Za-z_-]+/', '_', (string)$data['period']);
    $safePeriod = trim($safePeriod, '_');
    if ($safePeriod === '') $safePeriod = date('Ymd');
    $filename = 'laporan_kursi_pijat_' . $safePeriod . '.pdf';

    $html = $this->load->view('admin_laporan/pdf_kursi_pijat', $data, true);
    $this->_pdf($data['title'], $html, $filename);
}


// === CETAK POS (pakai grand_total_net) ===
public function print_pos(){
    $f = $this->_parse_filter();

    // rakit data buat view
    $data = [
        'title'  => 'Laporan Cafe (Transaksi Lunas)',
        'period' => $this->_period_label($f),
        'rows'   => $this->lm->fetch_pos($f),    // sudah ada grand_total_net
        'sum'    => $this->lm->sum_pos($f),      // sudah net ongkir=1
        'f'      => $f,
        'idr'    => function($x){ return $this->_idr($x); },
    ];

    // bikin nama file PDF pakai periode
    // contoh period: "01 Okt 2025 s.d 30 Okt 2025"
    // kita sanitize biar aman jadi: "01_Okt_2025_s_d_30_Okt_2025"
    $safePeriod = preg_replace('/[^0-9A-Za-z_-]+/', '_', $data['period']);
    $safePeriod = trim($safePeriod, '_'); // buang _ di awal/akhir kalau ada

    $filename = 'laporan_cafe_' . $safePeriod . '.pdf';

    // render HTML view jadi string
    $html = $this->load->view('admin_laporan/pdf_pos', $data, true);

    // kirim ke generator PDF
    $this->_pdf($data['title'], $html, $filename);
}


// === CETAK KURIR (tampilkan semua; ongkir=1 dianggap 0; skip kurir invalid) ===
public function print_kurir(){
    $f = $this->_parse_filter();
    $f['mode'] = 'delivery';

    $rows = $this->lm->fetch_pos($f); // ada delivery_fee_net

    $rows_out   = [];
    $total_fee  = 0;
    $by_method  = [];

    if (!empty($rows)){
        foreach ($rows as $r){
            // Ambil metode bayar (paid_method / paid_methode)
            $pmKeyRaw = $r->paid_method ?? $r->paid_methode ?? '';
            $k = strtolower(trim((string)$pmKeyRaw));

            // Hitung feeNet (support ongkir = 1 => gratis)
            $feeRaw = (int)($r->delivery_fee ?? 0);
            $feeNet = (int)($r->delivery_fee_net ?? (($feeRaw===1 && ($k==='transfer'||$k==='qris')) ? 0 : $feeRaw));

            $cid   = (int)($r->courier_id   ?? 0);
            $cname = trim((string)($r->courier_name ?? ''));
            if ($cid <= 0 && $cname === '') continue; // kurir invalid → skip

            // pastikan feeNet final
            $feeNet = (int)($r->delivery_fee_net ?? (((int)($r->delivery_fee ?? 0) === 1) ? 0 : (int)($r->delivery_fee ?? 0)));

            $pmKeyRaw = $r->paid_method ?? $r->paid_methode ?? '';
            $k = strtolower(trim((string)$pmKeyRaw)) ?: '-';
            if (!isset($by_method[$k])) $by_method[$k] = 0;
            $by_method[$k] += $feeNet;

            $rows_out[] = $r;
            $total_fee  += $feeNet;
        }
    }

    $data = [
        'title'  => 'Laporan Kurir (Delivery)',
        'period' => $this->_period_label($f),
        'rows'   => $rows_out,
        'sum'    => [
            'count'     => count($rows_out),
            'total_fee' => $total_fee,
            'by_method' => $by_method
        ],
        'f'      => $f,
        'idr'    => function($x){ return $this->_idr($x); },
    ];

    // build nama file pakai period
    $safePeriod = preg_replace('/[^0-9A-Za-z_-]+/', '_', (string)$data['period']);
    $safePeriod = trim($safePeriod, '_');
    if ($safePeriod === '') {
        $safePeriod = date('Ymd');
    }
    $filename = 'laporan_kurir_' . $safePeriod . '.pdf';

    $html = $this->load->view('admin_laporan/pdf_kurir', $data, true);
    $this->_pdf($data['title'], $html, $filename);
}


public function print_billiard(){
    $f = $this->_parse_filter();
    $data = [
        'title'  => 'Laporan Billiard (Terkonfirmasi)',
        'period' => $this->_period_label($f),
        'rows'   => $this->lm->fetch_billiard($f),
        'sum'    => $this->lm->sum_billiard($f),
        'f'      => $f,
        'idr'    => function($x){ return $this->_idr($x); },
    ];

    // nama file dinamis
    $safePeriod = preg_replace('/[^0-9A-Za-z_-]+/', '_', (string)$data['period']);
    $safePeriod = trim($safePeriod, '_');
    if ($safePeriod === '') {
        $safePeriod = date('Ymd');
    }
    $filename = 'laporan_billiard_' . $safePeriod . '.pdf';

    $html = $this->load->view('admin_laporan/pdf_billiard', $data, true);
    $this->_pdf($data['title'], $html, $filename);
}


public function print_pengeluaran(){
    $f = $this->_parse_filter();
    $data = [
        'title'  => 'Laporan Pengeluaran',
        'period' => $this->_period_label($f),
        'rows'   => $this->lm->fetch_pengeluaran($f),
        'sum'    => $this->lm->sum_pengeluaran($f),
        'f'      => $f,
        'idr'    => function($x){ return $this->_idr($x); },
    ];

    // nama file dinamis
    $safePeriod = preg_replace('/[^0-9A-Za-z_-]+/', '_', (string)$data['period']);
    $safePeriod = trim($safePeriod, '_');
    if ($safePeriod === '') {
        $safePeriod = date('Ymd');
    }
    $filename = 'laporan_pengeluaran_' . $safePeriod . '.pdf';

    $html = $this->load->view('admin_laporan/pdf_pengeluaran', $data, true);
    $this->_pdf($data['title'], $html, $filename);
}


public function print_laba(){
    $f = $this->_parse_filter();

    $sumPos = $this->lm->sum_pos($f);
    $sumBil = $this->lm->sum_billiard($f);
    $sumPen = $this->lm->sum_pengeluaran($f);
    $sumKP  = $this->lm->sum_kursi_pijat($f);

    // Laba final: Cafe + Billiard + Kursi Pijat - Pengeluaran
    $laba = (int)$sumPos['total'] + (int)$sumBil['total'] + (int)$sumKP['total']  - (int)$sumPen['total'];

    $data = [
        'title'  => 'Laporan Laba',
        'period' => $this->_period_label($f),
        'sumPos' => $sumPos,
        'sumBil' => $sumBil,
        'sumPen' => $sumPen,
        'sumKP'  => $sumKP,
        'laba'   => $laba,
        'f'      => $f,
        'idr'    => function($x){ return $this->_idr($x); },
    ];

    $safePeriod = preg_replace('/[^0-9A-Za-z_-]+/', '_', (string)$data['period']);
    $safePeriod = trim($safePeriod, '_');
    if ($safePeriod === '') $safePeriod = date('Ymd');
    $filename = 'laporan_laba_' . $safePeriod . '.pdf';

    $html = $this->load->view('admin_laporan/pdf_laba', $data, true);
    $this->_pdf($data['title'], $html, $filename);
}



    /* ===================== Helpers ===================== */

    private function _parse_filter(): array {
        $date_from = trim((string)$this->input->get_post('date_from'));
        $date_to   = trim((string)$this->input->get_post('date_to'));
         $preset = $this->_sanitize_enum($this->input->get_post('preset'),
        ['today','yesterday','this_week','this_month','range'],'today');
        $metode = $this->_sanitize_enum($this->input->get_post('metode'),
            ['all','cash','qris','transfer'],'all');
        $mode   = $this->_sanitize_enum($this->input->get_post('mode'),
            ['all','walkin','dinein','delivery'],'all');
        $status = $this->_sanitize_enum($this->input->get_post('status'),
            ['all','paid','unpaid','void','cancel','done'],'all');

        // ====== NEW: ambil jam dari request ======
        $time_from = trim((string)$this->input->get_post('time_from')) ?: '00:00';
        $time_to   = trim((string)$this->input->get_post('time_to'))   ?: '23:59';
        // validasi format HH:MM
        $reHHMM = '/^(2[0-3]|[01]\d):([0-5]\d)$/';
        if (!preg_match($reHHMM, $time_from)) $time_from = '00:00';
        if (!preg_match($reHHMM, $time_to))   $time_to   = '23:59';

        // hitung tanggal (Asia/Makassar)
        $tz  = new DateTimeZone('Asia/Makassar');
        $now = new DateTime('now', $tz);

        switch ($preset){
            case 'yesterday':
                $start = (clone $now)->modify('-1 day')->setTime(0,0,0);
                $end   = (clone $now)->modify('-1 day')->setTime(23,59,59);
                break;

            case 'this_week':
                $dow   = (int)$now->format('N'); // 1..7
                $start = (clone $now)->modify('-'.($dow-1).' days')->setTime(0,0,0);
                $end   = (clone $start)->modify('+6 days')->setTime(23,59,59);
                break;

            case 'this_month':
                $start = new DateTime($now->format('Y-m-01 00:00:00'), $tz);
                $end   = (clone $start)->modify('last day of this month')->setTime(23,59,59);
                break;

            case 'range':
                $start = $date_from ? DateTime::createFromFormat('Y-m-d', $date_from, $tz) : (clone $now)->setTime(0,0,0);
                $end   = $date_to   ? DateTime::createFromFormat('Y-m-d', $date_to,   $tz) : (clone $now)->setTime(23,59,59);
                if ($start){ $start->setTime(0,0,0); } else { $start = (clone $now)->setTime(0,0,0); }
                if ($end){   $end->setTime(23,59,59); } else { $end   = (clone $now)->setTime(23,59,59); }
                break;

            case 'today':
            default:
                $start = (clone $now)->setTime(0,0,0);
                $end   = (clone $now)->setTime(23,59,59);
        }

        // ====== NEW: terapkan jam spesifik ======
        // time_from → detik=00, time_to → detik=59 agar 1 menit utuh
        list($h1,$m1) = array_map('intval', explode(':',$time_from));
        list($h2,$m2) = array_map('intval', explode(':',$time_to));

        $start->setTime($h1, $m1, 0);
        $end->setTime($h2, $m2, 59);

        // ====== NEW: jika lintas tengah malam (end < start) → geser end +1 day ======
        if ($end < $start){
            $end->modify('+1 day');
        }

        return [
            'preset'     => $preset,
            'date_from'  => $start->format('Y-m-d H:i:s'),
            'date_to'    => $end->format('Y-m-d H:i:s'),
            'time_from'  => $time_from,   // opsional, berguna untuk debug/log
            'time_to'    => $time_to,     // opsional
            'metode'     => $metode,
            'mode'       => $mode,
            'status'     => $status,
        ];
    }


    private function _idr($n){ return 'Rp '.number_format((int)$n,0,',','.'); }

    private function _period_label(array $f){
    $f  = $this->_enforce_period_acl($f); // ⬅️ penting
    $tz = new DateTimeZone('Asia/Makassar');

    // parsing aman (fallback jika format bukan 'Y-m-d H:i:s')
    $df = DateTime::createFromFormat('Y-m-d H:i:s', $f['date_from'], $tz) ?: new DateTime($f['date_from'], $tz);
    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $f['date_to'],   $tz) ?: new DateTime($f['date_to'],   $tz);

    $pmap = [
        'today'      => 'Hari ini',
        'yesterday'  => 'Kemarin',
        'this_week'  => 'Minggu ini',
        'this_month' => 'Bulan ini',
        'range'      => 'Periode khusus'
    ];
    return ($pmap[$f['preset']] ?? 'Periode') . ' (' . $df->format('d/m/Y H:i') . ' — ' . $dt->format('d/m/Y H:i') . ')';
}
/** 🔒 Paksa batas periode sesuai role.
 *  Admin: bebas.
 *  Non-admin: hanya 'today' & 'yesterday'. Selain itu akan dipaksa 'today'.
 */
// private function _enforce_period_acl(array $f): array
// {
//     $isAdmin = ($this->session->userdata('admin_username') === 'admin');
//     $preset  = $f['preset'] ?? 'today';

//     // Admin: tak perlu ubah apa pun
//     if ($isAdmin) {
//         return $f;
//     }

//     // Non-admin: hanya boleh today / yesterday
//     if ($preset !== 'today' && $preset !== 'yesterday') {
//         $preset = 'today';
//     }

//     $tz  = new DateTimeZone('Asia/Makassar');
//     $now = new DateTime('now', $tz);

//     if ($preset === 'yesterday') {
//         $start = (clone $now)->setTime(0,0,0)->modify('-1 day');
//         $end   = (clone $start)->setTime(23,59,59);
//     } else { // today
//         $start = (clone $now)->setTime(0,0,0);
//         $end   = (clone $now)->setTime(23,59,59);
//     }

//     $f['preset']    = $preset;
//     $f['date_from'] = $start->format('Y-m-d H:i:s');
//     $f['date_to']   = $end->format('Y-m-d H:i:s');
//     return $f;
// }

private function _enforce_period_acl(array $f): array
{
    // Tidak ada pembatasan berdasarkan session/role lagi.
    // Semua preset diterima. Jika 'custom' dikirim dengan date_from & date_to,
    // kita pakai apa adanya (dinormalisasi). Default: today.
    $tz  = new DateTimeZone('Asia/Makassar');
    $now = new DateTime('now', $tz);

    $preset = strtolower(trim((string)($f['preset'] ?? 'today')));

    // Jika custom & ada date_from/date_to → pakai itu
    if ($preset === 'custom' && !empty($f['date_from']) && !empty($f['date_to'])) {
        try { $start = new DateTime((string)$f['date_from'], $tz); }
        catch (\Exception $e) { $start = (clone $now)->setTime(0,0,0); }

        try { $end = new DateTime((string)$f['date_to'], $tz); }
        catch (\Exception $e) { $end = (clone $start)->setTime(23,59,59); }

        // Jaga urutan start <= end
        if ($end < $start) { [$start, $end] = [$end, $start]; }

        // Clamp ke detik penuh
        $start = (clone $start)->setTime((int)$start->format('H'), (int)$start->format('i'), (int)$start->format('s'));
        $end   = (clone $end)->setTime((int)$end->format('H'), (int)$end->format('i'), (int)$end->format('s'));
    } else {
        // Preset umum (tambahkan/kurangi sesuai kebutuhanmu)
        switch ($preset) {
            case 'yesterday':
                $start = (clone $now)->setTime(0,0,0)->modify('-1 day');
                $end   = (clone $start)->setTime(23,59,59);
                break;

            case 'this_week': { // Senin..Minggu minggu ini
                $dow   = (int)$now->format('N'); // 1..7 (Mon..Sun)
                $start = (clone $now)->modify('-'.($dow-1).' days')->setTime(0,0,0);
                $end   = (clone $start)->modify('+6 days')->setTime(23,59,59);
                break;
            }

            case 'last_week': { // Senin..Minggu minggu lalu
                $dow   = (int)$now->format('N');
                $end   = (clone $now)->modify('-'.$dow.' days')->setTime(23,59,59); // Minggu lalu
                $start = (clone $end)->modify('-6 days')->setTime(0,0,0);
                break;
            }

            case 'this_month':
                $start = (clone $now)->modify('first day of this month')->setTime(0,0,0);
                $end   = (clone $now)->modify('last day of this month')->setTime(23,59,59);
                break;

            case 'last_month':
                $start = (clone $now)->modify('first day of last month')->setTime(0,0,0);
                $end   = (clone $now)->modify('last day of last month')->setTime(23,59,59);
                break;

            case 'this_year':
                $start = (clone $now)->setDate((int)$now->format('Y'), 1, 1)->setTime(0,0,0);
                $end   = (clone $now)->setDate((int)$now->format('Y'),12,31)->setTime(23,59,59);
                break;

            case 'last_year': {
                $y     = (int)$now->format('Y') - 1;
                $start = (clone $now)->setDate($y, 1, 1)->setTime(0,0,0);
                $end   = (clone $now)->setDate($y,12,31)->setTime(23,59,59);
                break;
            }

            case 'today':
            default:
                $preset = 'today';
                $start  = (clone $now)->setTime(0,0,0);
                $end    = (clone $now)->setTime(23,59,59);
        }
    }

    $f['preset']    = $preset;
    $f['date_from'] = $start->format('Y-m-d H:i:s');
    $f['date_to']   = $end->format('Y-m-d H:i:s');
    return $f;
}


    private function _pdf($title, $html, $filename='laporan.pdf'){
        $this->load->library('pdf'); // TCPDF wrapper
        
        $pdf = new Pdf('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        $pdf->SetCreator('AusiApp');
        $pdf->SetAuthor('AusiApp');
        $pdf->SetTitle($title);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 10);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // PAKSA landscape DI SINI
        $pdf->AddPage('L', 'A4');

        $pdf->SetFont('dejavusans','',9);
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output($filename, 'I');
        exit;
    }


    private function _pdfx($title, $html, $filename='laporan.pdf'){
        $this->load->library('pdf'); // TCPDF wrapper
        
        $pdf = new Pdf('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // $pdf = new pdf('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('AusiApp');
        $pdf->SetAuthor('AusiApp');
        $pdf->SetTitle($title);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 10);
        $pdf->setPrintHeader(false);   // <— matikan header (menghilangkan garis atas)
        $pdf->setPrintFooter(false);   // opsional: matikan footer

        $pdf->AddPage();
        $pdf->SetFont('dejavusans','',9);
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output($filename, 'I'); // inline
        exit;
    }
    /** ==============================
     * HALAMAN STATISTIK GRAFIK
     * ============================== */
    public function chart(){
        // halaman HTML yg berisi filter + container chart
        $data["controller"] = get_class($this);
        $data["title"]      = "Statistik";
        $data["subtitle"]   = "Statistik Grafik";
        $data["content"]    = $this->load->view('Admin_laporan_chart',$data,true);
        $this->render($data);
    }

    public function wablas(){
        // halaman HTML yg berisi filter + container chart
        $data["web"] = $this->om->web_me();
        $data["controller"] = get_class($this);
        $data["title"]      = "Wa";
        $data["subtitle"]   = "Wa";
        $data["content"]    = $this->load->view('wablas',$data,true);
        $this->render($data);
    }

    /**
     * AJAX DATA UNTUK HIGHCHARTS
     * return json:
     * {
     *   success: true,
     *   categories: ["2025-10-28", "2025-10-29", ...],
     *   cafe: [120000, 90000, ...],
     *   billiard: [...],
     *   pengeluaran: [...],
     *   laba: [...],
     *   total_rekap: {cafe:..., billiard:..., pengeluaran:..., laba:...},
     *   filter: {...}
     * }
     */
    public function chart_data(){
    $f = $this->_parse_filter();

    $cafeMap        = $this->lm->agg_daily_pos($f);
    $billiardMap    = $this->lm->agg_daily_billiard($f);
    $pengeluaranMap = $this->lm->agg_daily_pengeluaran($f);
    $kpMap          = $this->lm->agg_daily_kursi_pijat($f); // KP wajib

    $tz = new DateTimeZone('Asia/Makassar');
    $startDay = DateTime::createFromFormat('Y-m-d H:i:s', $f['date_from'], $tz) ?: new DateTime($f['date_from'], $tz);
    $endDay   = DateTime::createFromFormat('Y-m-d H:i:s', $f['date_to'],   $tz) ?: new DateTime($f['date_to'],   $tz);

    $loopStart = clone $startDay; $loopStart->setTime(0,0,0);
    $loopEnd   = clone $endDay;   $loopEnd->setTime(23,59,59);

    $categories=[]; $cafeArr=[]; $bilArr=[]; $pengArr=[]; $kpArr=[]; $labaArr=[];
    $sumCafe=0; $sumBil=0; $sumPeng=0; $sumKP=0; $sumLaba=0;

    $cur = clone $loopStart;
    while ($cur <= $loopEnd){
        $key = $cur->format('Y-m-d');

        $c  = (int)($cafeMap[$key]        ?? 0);
        $b  = (int)($billiardMap[$key]    ?? 0);
        $kp = (int)($kpMap[$key]          ?? 0);
        $pe = (int)($pengeluaranMap[$key] ?? 0);

        $l  = $c + $b + $kp - $pe; // ← LABA BARU

        $categories[] = $key;
        $cafeArr[] = $c; $bilArr[] = $b; $kpArr[] = $kp; $pengArr[] = $pe; $labaArr[] = $l;

        $sumCafe += $c; $sumBil += $b; $sumKP += $kp; $sumPeng += $pe; $sumLaba += $l;

        $cur->modify('+1 day');
    }

    $out = [
        'success'       => true,
        'filter'        => $f,
        'categories'    => $categories,
        'cafe'          => $cafeArr,
        'billiard'      => $bilArr,
        'kursi_pijat'   => $kpArr,
        'pengeluaran'   => $pengArr,
        'laba'          => $labaArr, // sudah +KP
        'total_rekap'   => [
            'cafe'        => $sumCafe,
            'billiard'    => $sumBil,
            'kursi_pijat' => $sumKP,
            'pengeluaran' => $sumPeng,
            'laba'        => $sumLaba, // sudah +KP
        ],
    ];

    return $this->output
        ->set_content_type('application/json','utf-8')
        ->set_output(json_encode($out));
}



    
}
