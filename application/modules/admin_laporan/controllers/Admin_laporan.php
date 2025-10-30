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

    $pos  = ['count'=>(int)($pos['count']??0),'total'=>(int)($pos['total']??0),'by_method'=>(array)($pos['by_method']??[])];
    $bil  = ['count'=>(int)($bil['count']??0),'total'=>(int)($bil['total']??0),'by_method'=>(array)($bil['by_method']??[])];
    $peng = ['count'=>(int)($peng['count']??0),'total'=>(int)($peng['total']??0),'by_kategori'=>(array)($peng['by_kategori']??[])];
    $kur  = ['count'=>(int)($kur['count']??0),'total_fee'=>(int)($kur['total_fee']??0),'by_method'=>(array)($kur['by_method']??[])];

    $out = [
        'success'     => true,
        'filter'      => $f,
        'pos'         => $pos,
        'billiard'    => $bil,
        'pengeluaran' => $peng,
        'kurir'       => $kur,                     // info (subset POS)
        'meta'        => ['kurir_subset_of_pos'=>true],
        'laba'        => ['total' => $pos['total'] + $bil['total'] - $peng['total']],
    ];
    return $this->output->set_content_type('application/json','utf-8')->set_output(json_encode($out));
}

// === CETAK POS (pakai grand_total_net) ===
public function print_pos(){
    $f = $this->_parse_filter();
    $data = [
        'title'  => 'Laporan Cafe (Transaksi Lunas)',
        'period' => $this->_period_label($f),
        'rows'   => $this->lm->fetch_pos($f),    // sudah ada grand_total_net
        'sum'    => $this->lm->sum_pos($f),      // sudah net ongkir=1
        'f'      => $f,
        'idr'    => function($x){ return $this->_idr($x); },
    ];
    $html = $this->load->view('admin_laporan/pdf_pos', $data, true);
    $this->_pdf($data['title'], $html, 'laporan_cafe.pdf');
}

// === CETAK KURIR (tampilkan semua; ongkir=1 dianggap 0; skip kurir invalid) ===
public function print_kurir(){
    $f = $this->_parse_filter();
    $f['mode'] = 'delivery';

    $rows = $this->lm->fetch_pos($f); // ada delivery_fee_net

    $rows_out = [];
    $total_fee = 0;
    $by_method = [];

    if (!empty($rows)){
        foreach ($rows as $r){
            // di dalam foreach $rows as $r
$pmKeyRaw = $r->paid_method ?? $r->paid_methode ?? '';
$k = strtolower(trim((string)$pmKeyRaw));

$feeRaw = (int)($r->delivery_fee ?? 0);
$feeNet = (int)($r->delivery_fee_net ?? (($feeRaw===1 && ($k==='transfer'||$k==='qris')) ? 0 : $feeRaw));

            $cid   = (int)($r->courier_id   ?? 0);
            $cname = trim((string)($r->courier_name ?? ''));
            if ($cid <= 0 && $cname === '') continue; // kurir invalid → skip

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
        'sum'    => ['count'=>count($rows_out),'total_fee'=>$total_fee,'by_method'=>$by_method],
        'f'      => $f,
        'idr'    => function($x){ return $this->_idr($x); },
    ];
    $html = $this->load->view('admin_laporan/pdf_kurir', $data, true);
    $this->_pdf($data['title'], $html, 'laporan_kurir.pdf');
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
        $html = $this->load->view('admin_laporan/pdf_billiard', $data, true);
        $this->_pdf($data['title'], $html, 'laporan_billiard.pdf');
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
        $html = $this->load->view('admin_laporan/pdf_pengeluaran', $data, true);
        $this->_pdf($data['title'], $html, 'laporan_pengeluaran.pdf');
    }

    public function print_laba(){
        $f = $this->_parse_filter();
        $sumPos = $this->lm->sum_pos($f);
        $sumBil = $this->lm->sum_billiard($f);
        $sumPen = $this->lm->sum_pengeluaran($f);
        $data = [
            'title'  => 'Laporan Laba',
            'period' => $this->_period_label($f),
            'sumPos' => $sumPos,
            'sumBil' => $sumBil,
            'sumPen' => $sumPen,
            'laba'   => (int)$sumPos['total'] + (int)$sumBil['total'] - (int)$sumPen['total'],
            'f'      => $f,
            'idr'    => function($x){ return $this->_idr($x); },
        ];
        $html = $this->load->view('admin_laporan/pdf_laba', $data, true);
        $this->_pdf($data['title'], $html, 'laporan_laba.pdf');
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
        $tz = new DateTimeZone('Asia/Makassar');
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


    private function _pdf($title, $html, $filename='laporan.pdf'){
        $this->load->library('pdf'); // TCPDF wrapper
        
        $pdf = new Pdf('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

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

    
}
