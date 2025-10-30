<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter\Column;
use PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter\Column\Rule as FilterRule;


class Admin_permohonan extends Admin_Controller {
	function __construct(){
		parent::__construct();

        // $this->output->set_header('Cache-Control: no-cache, no-store, must-revalidate');
        // $this->output->set_header('Pragma: no-cache');
        // $this->output->set_header('Expires: 0');
        // $this->output->set_header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT');

        $this->load->model("M_admin_permohonan", "mbt");

        // $this->load->model("M_paket_a", "pa");
    }


    public function index()
    {
        $level = $this->session->userdata("admin_level");
        if ($level !== "admin" && $level !== "user") {
            show_error("Anda tidak memiliki akses.", 403, "Akses Ditolak");
            return;
        }

        $data['controller'] = get_class($this);
        $data['title']      = "Data Booking Tamu";
        $data['subtitle']   = "Semua Booking";
        $data['deskripsi']  = "Data seluruh booking kunjungan tamu";

        // dropdown unit
        $data['arr_units']  = $this->mbt->arr_units();

        $data['content'] = $this->load->view('Admin_permohonan_view', $data, true);
        $this->render($data);
    }

    public function get_data()
    {
        $post = $this->input->post(NULL, TRUE);

        $list  = $this->mbt->get_datatables($post);
        $data  = [];
        $start = intval($post['start'] ?? 0);

        foreach ($list as $row) {
            $start++;

        // Status badge lengkap
            switch ($row->status) {
                case 'approved':
                $badge = '<span class="badge bg-info text-dark">✅ Approved</span>'; break;
                case 'checked_in':
                $badge = '<span class="badge bg-success">🟢 Checked-in</span>'; break;
                case 'checked_out':
                $badge = '<span class="badge bg-warning text-dark">🟡 Checked-out</span>'; break;
                case 'expired':
                $badge = '<span class="badge bg-secondary">⛔ Tidak Datang</span>'; break;
                case 'rejected':
                $badge = '<span class="badge bg-danger">❌ Rejected</span>'; break;
                default:
                $badge = '<span class="badge bg-light text-dark">Draft</span>';
            }

        $asal = $row->asal ?: '-'; // dari COALESCE(target_instansi_nama, instansi)
        $unit = $row->unit_tujuan_nama ?: '-';

        $kode    = (string)$row->kode_booking;
        $kodeEsc = htmlspecialchars($kode, ENT_QUOTES, 'UTF-8');

        $kodeHtml =
        '<span class="badge badge-secondary" title="Lihat detail">'.
        $kodeEsc.'</span>'.
        ' <button type="button" class="btn text-muted btn-copy-kode" '.
        'data-kode="'.$kodeEsc.'" title="Salin kode" aria-label="Salin kode">'.
        '<i class="fe-copy"></i></button>';


        $data[] = [
          'cek'     => '<div class="checkbox checkbox-danger checkbox-single"><input type="checkbox" class="row-check" value="'.$kodeEsc.'" aria-label="Pilih baris"><label></label></div>', // <-- kolom baru
          'no'      => $start,
          'kode'    => $kodeHtml,
          'tgljam'  => hari_ini($row->tanggal).", ".htmlspecialchars(tgl_view($row->tanggal)).' '.$row->jam,
          'tamu'    => '<b>'.htmlspecialchars($row->nama_tamu).'</b>'.
          '<div class="text-muted small">'.htmlspecialchars($row->jabatan ?: '-').'</div>',
          'asal'    => htmlspecialchars($asal),
          'instansi'=> '<b>'.htmlspecialchars($unit).'</b>'.
          (!empty($row->nama_petugas_instansi)
             ? '<div class="small text-muted">'.htmlspecialchars($row->nama_petugas_instansi).'</div>'
             : ''),
          'status'  => $badge
      ];
  }

  $output = [
    "draw"            => intval($post['draw'] ?? 1),
    "recordsTotal"    => $this->mbt->count_all($post),
    "recordsFiltered" => $this->mbt->count_filtered($post),
    "data"            => $data,
];
echo json_encode($output);
}



public function cetak_pdf()
{
    // 1) Ambil filter
    $filters = [
        'tanggal_mulai'   => $this->input->get('tanggal_mulai', TRUE),
        'tanggal_selesai' => $this->input->get('tanggal_selesai', TRUE),
        'unit_tujuan'     => $this->input->get('unit_tujuan', TRUE),
        'form_asal'       => $this->input->get('form_asal', TRUE),
        'status'          => $this->input->get('status', TRUE),
    ];

    // Lengkapi nama unit untuk filter
    $filters['unit_tujuan_nama'] = '';
    if (!empty($filters['unit_tujuan'])) {
        $u = $this->db->select('nama_unit')
                      ->where('id', (int)$filters['unit_tujuan'])
                      ->get('unit_tujuan')->row();
        if ($u) $filters['unit_tujuan_nama'] = $u->nama_unit;
    }

    // 2) Data
    $rows = $this->mbt->get_for_export($filters);

 

    // 4) Render HTML
    $html = $this->load->view('laporan_booking', [
        'rows'      => $rows,
        'filters'   => $filters,
        'title'     => 'Laporan Booking Tamu',
        'logo_file' => $logo_file, // ← kirim ke view
    ], true);

    // 5) TCPDF
    if (!class_exists('TCPDF')) {
        require_once(APPPATH.'third_party/tcpdf/tcpdf.php');
    }

    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('SILATURAHMI');
    $pdf->SetAuthor('SILATURAHMI');
    $pdf->SetTitle('Laporan Booking Tamu');
    $pdf->SetSubject('Laporan Booking Tamu');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(10, 12, 10);
    $pdf->SetAutoPageBreak(TRUE, 12);
    $pdf->SetFont('dejavusans', '', 10);
    $pdf->setImageScale(1.25);

    // 6) Tulis HTML
    $pdf->AddPage();
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->lastPage();

    // 7) Pastikan tidak ada output lain
    if (ob_get_length()) { @ob_end_clean(); }

    // 8) Output
    $filename = 'booking_tamu_'.date('Ymd_His').'.pdf';
    $pdf->Output($filename, 'I');
}


public function detail($kode_booking = null)
{
    if (empty($kode_booking)) { show_404(); }

    $row = $this->mbt->get_detail_by_kode($kode_booking);
    if (!$row) { show_404(); }

    // ⬇️ Tambahan: ambil pendamping
    $pendamping_rows = $this->mbt->get_pendamping_by_kode($kode_booking);

    $data['controller'] = get_class($this);
    $data['title']      = 'Detail Booking: '.$kode_booking;
    $data['deskripsi']  = 'Rincian lengkap data booking tamu';
    $data['subtitle']   = 'Detail Booking: '.$kode_booking;

    $data['row']             = $row;
    $data['pendamping_rows'] = $pendamping_rows; // ⬅️ kirim ke view

    $data['content'] = $this->load->view('Admin_permohonan_detail', $data, true);
    $this->render($data);
}


public function lampiran($jenis = '', $kode = '')
{
    // cek login/otorisasi sesuai kebutuhanmu...
    $row = $this->db->get_where('booking_tamu', ['kode_booking' => $kode])->row();
    if (!$row) show_404();

    if ($jenis === 'surat') {
        $file_name = $row->surat_tugas;
        $base_path = FCPATH.'uploads/surat_tugas/';
    } elseif ($jenis === 'foto') {
        $file_name = $row->foto;
        $base_path = FCPATH.'uploads/foto/';
    } else {
        show_404();
    }

    if (empty($file_name)) show_404();

    $path = $base_path . $file_name;
    if (!is_file($path)) show_404();

    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $mime = 'application/octet-stream';
    if ($ext === 'pdf') $mime = 'application/pdf';
    if (in_array($ext, ['jpg','jpeg'])) $mime = 'image/jpeg';
    if ($ext === 'png') $mime = 'image/png';
    if ($ext === 'gif') $mime = 'image/gif';
    if ($ext === 'webp') $mime = 'image/webp';

    // dl=1 -> force download
    $force = $this->input->get('dl', true) == '1';
    $disp  = $force ? 'attachment' : 'inline';

    // bersihkan output buffer
    if (ob_get_length()) ob_end_clean();

    header('Content-Type: '.$mime);
    header('X-Content-Type-Options: nosniff');
    header('Content-Length: '.filesize($path));
    header('Content-Disposition: '.$disp.'; filename="'.basename($path).'"');

    // kirim file
    readfile($path);
    exit;
}

public function hapus_batch()
{
    // Hanya izinkan username admin (sesuai permintaan)
    if ($this->session->userdata('admin_username') !== 'admin') {
        return $this->output
        ->set_status_header(403)
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'success' => false,
            'message' => 'Tidak diizinkan.'
        ]));
    }

    // Ambil array kode[] dari POST
    $list = $this->input->post('kode');
    if (!is_array($list) || empty($list)) {
        return $this->output
        ->set_status_header(400)
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'success' => false,
            'message' => 'Tidak ada data yang dipilih.'
        ]));
    }

    // Normalisasi & validasi nilai kode_booking
    $kodes = [];
    foreach ($list as $k) {
        $k = trim((string)$k);
        // Sesuaikan pola jika format kode_booking kamu berbeda
        if ($k !== '' && preg_match('/^[A-Za-z0-9._-]{3,64}$/', $k)) {
            $kodes[$k] = true; // set unique
        }
    }
    $kodes = array_keys($kodes);

    if (empty($kodes)) {
        return $this->output
        ->set_status_header(400)
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'success' => false,
            'message' => 'Format kode tidak valid.'
        ]));
    }

    // Eksekusi hapus batch dalam transaksi
    $this->db->trans_begin();

    $this->db->where_in('kode_booking', $kodes);
    $this->db->delete('booking_tamu');

    $affected = $this->db->affected_rows();

    if ($this->db->trans_status() === false) {
        $this->db->trans_rollback();
        return $this->output
        ->set_status_header(500)
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'success' => false,
            'message' => 'Gagal menghapus data (transaksi dibatalkan).'
        ]));
    }

    $this->db->trans_commit();

    // Sukses — balas JSON agar cocok dengan handler JS kamu
    return $this->output
    ->set_content_type('application/json')
    ->set_output(json_encode([
        'success' => true,
        'message' => $affected.' data terhapus.'
    ]));
}


public function export_excel()
{
    // Akses
    $level = $this->session->userdata("admin_level");
    if ($level !== "admin" && $level !== "user") {
        show_error("Anda tidak memiliki akses.", 403, "Akses Ditolak");
        return;
    }

    // Filter
    $filters = [
        'tanggal_mulai'   => $this->input->get('tanggal_mulai', TRUE),
        'tanggal_selesai' => $this->input->get('tanggal_selesai', TRUE),
        'unit_tujuan'     => $this->input->get('unit_tujuan', TRUE),
        'form_asal'       => $this->input->get('form_asal', TRUE),
        'status'          => $this->input->get('status', TRUE),
    ];
    $filters['unit_tujuan_nama'] = '';
    if (!empty($filters['unit_tujuan'])) {
        $u = $this->db->select('nama_unit')->where('id', (int)$filters['unit_tujuan'])->get('unit_tujuan')->row();
        if ($u) $filters['unit_tujuan_nama'] = $u->nama_unit;
    }

    // Data
    $rows = $this->mbt->get_for_export($filters);

    // Spreadsheet
    $ss    = new Spreadsheet();
    $sheet = $ss->getActiveSheet();
    $sheet->setTitle('Booking Tamu');

    $headers = ['No','Kode Booking','Tanggal','Jam','Nama Tamu','Jabatan','Asal','Unit Tujuan','Pejabat','Status'];
    $lastCol = Coordinate::stringFromColumnIndex(count($headers));

    // Baris 1 khusus LOGO + tinggi
    $sheet->mergeCells("A1:{$lastCol}1");
    $sheet->getRowDimension(1)->setRowHeight(56);

    // KOP (baris 2-5)
    $orgName   = 'LAPAS KELAS I MAKASSAR';
    $orgAddr   = 'Jl. Sultan Alauddin, Gn. Sari, Kec. Rappocini, Kota Makassar, Sulawesi Selatan 90221';
    $titleText = 'LAPORAN BOOKING TAMU';

    // filter line (tampil di baris 5)
    $fp = [];
    if (!empty($filters['tanggal_mulai']) || !empty($filters['tanggal_selesai'])) {
        $mulai   = !empty($filters['tanggal_mulai'])   ? date('d-m-Y', strtotime($filters['tanggal_mulai']))   : '-';
        $selesai = !empty($filters['tanggal_selesai']) ? date('d-m-Y', strtotime($filters['tanggal_selesai'])) : '-';
        $fp[] = "Periode: {$mulai} s/d {$selesai}";
    }
    if (!empty($filters['unit_tujuan_nama'])) $fp[] = 'Unit: '.$filters['unit_tujuan_nama'];
    if (!empty($filters['form_asal']))        $fp[] = 'Kata kunci: "'.$filters['form_asal'].'"';
    $fp[] = 'Status: '.($filters['status'] !== '' ? $filters['status'] : 'Semua');
    $filterLine = implode('  |  ', $fp);

    foreach ([2,3,4,5] as $r) $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
    $sheet->setCellValue('A2', $orgName);
    $sheet->setCellValue('A3', $orgAddr);
    $sheet->setCellValue('A4', $titleText);
    $sheet->setCellValue('A5', $filterLine);

    $sheet->getStyle("A2:A5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A3')->getFont()->setSize(10)->getColor()->setARGB('FF555555');
    $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('A5')->getFont()->setSize(10)->getColor()->setARGB('FF444444');
    $sheet->getStyle("A5:{$lastCol}5")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);

    // HEADER TABEL (baris 7) — warna + border
    $headerRow = 7;
    foreach ($headers as $i => $h) {
        $col = Coordinate::stringFromColumnIndex($i + 1);
        $sheet->setCellValue($col.$headerRow, $h);
    }
    $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
        'font'      => ['bold' => true, 'color' => ['argb' => 'FF000000']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        'fill'      => ['type' => Fill::FILL_SOLID, 'color' => ['argb' => 'FFCCE5FF']], // biru muda
        'borders'   => ['allBorders' => ['style' => Border::BORDER_THIN]],
    ]);
    $sheet->getRowDimension($headerRow)->setRowHeight(22);

    // ==== Data & hitung lebar konten (auto-fit manual yang stabil) ====
    $hariMap = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    $maxLen  = array_map('mb_strlen', $headers); // panjang awal dari header

    $r  = $headerRow + 1;
    $no = 1;

    $mapStatus = static function($s){
        // mapping baru sesuai permintaan
        switch ($s) {
            case 'approved':    return 'Belum Datang';
            case 'checked_in':  return 'Datang';
            case 'checked_out': return 'Datang';
            case 'expired':     return 'Tidak Datang';
            case 'rejected':    return 'Ditolak';
            default:            return 'Draft';
        }
    };

    foreach ($rows as $row) {
        $asal = $row->asal ?? '';
        if ($asal === '' || $asal === null) {
            $asal = !empty($row->target_instansi_nama) ? $row->target_instansi_nama : ($row->instansi ?? '-');
        }
        $ts     = strtotime($row->tanggal);
        $hari   = $hariMap[(int)date('w', $ts)] ?? date('D', $ts);
        $tglTxt = $hari.', '.date('d-m-Y', $ts);

        $vals = [
            (string)$no,
            (string)$row->kode_booking,
            $tglTxt,
            (string)$row->jam,
            (string)$row->nama_tamu,
            (string)($row->jabatan ?: '-'),
            (string)($asal ?: '-'),
            (string)($row->unit_tujuan_nama ?: '-'),
            (string)($row->nama_petugas_instansi ?: '-'),
            $mapStatus($row->status),
        ];

        // tulis ke sheet
        $sheet->setCellValue("A{$r}", $vals[0]);
        $sheet->setCellValueExplicit("B{$r}", $vals[1], DataType::TYPE_STRING);
        $sheet->setCellValue("C{$r}", $vals[2]);
        $sheet->setCellValue("D{$r}", $vals[3]);
        $sheet->setCellValue("E{$r}", $vals[4]);
        $sheet->setCellValue("F{$r}", $vals[5]);
        $sheet->setCellValue("G{$r}", $vals[6]);
        $sheet->setCellValue("H{$r}", $vals[7]);
        $sheet->setCellValue("I{$r}", $vals[8]);
        $sheet->setCellValue("J{$r}", $vals[9]);

        // update maxLen per kolom
        foreach ($vals as $i => $v) {
            $L = mb_strlen($v);
            if ($L > $maxLen[$i]) $maxLen[$i] = $L;
        }

        $no++; $r++;
    }

    $lastDataRow = max($headerRow, $r - 1);

    // BORDER all data
    $sheet->getStyle("A{$headerRow}:{$lastCol}{$lastDataRow}")
          ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // === Set width stabil (auto-fit manual) ===
    // Rumus excel kira-kira: px ≈ width*7 + 5, kita set width by chars (maxLen + padding)
    $padding = [2, 2, 4, 2, 4, 3, 4, 4, 4, 2]; // padding per kolom
    for ($i = 0; $i < count($headers); $i++) {
        $wChars = max(6, min(60, $maxLen[$i] + $padding[$i])); // clamp 6..60
        $sheet->getColumnDimensionByColumn($i+1)->setAutoSize(false);
        $sheet->getColumnDimensionByColumn($i+1)->setWidth($wChars);
    }

    // === Center LOGO tepat di tengah merge A..J (menggunakan width yg sudah fix) ===
    $logoPath = FCPATH.'assets/images/logo.png';
    if (is_file($logoPath)) {
        // px dari width (chars) -> approx
        $toPx = static function($wChars) {
            $w = ($wChars > 0) ? $wChars : 8.43; // default excel
            return (int)floor($w * 7 + 5);
        };
        // total px A..J
        $totalPx = 0; $colPx = [];
        for ($c = 1; $c <= count($headers); $c++) {
            $w = $sheet->getColumnDimensionByColumn($c)->getWidth();
            $px = $toPx($w);
            $colPx[$c] = $px;
            $totalPx  += $px;
        }

        // width logo
        $probe = new Drawing();
        $probe->setPath($logoPath);
        $probe->setHeight(56);
        $imgW = $probe->getWidth(); // px
        unset($probe);

        // target start X supaya center terhadap A..J
        $startX = max(0, (int)floor(($totalPx - $imgW) / 2));

        // cari kolom anchor & offsetX
        $acc = 0; $anchorCol = 1; $offsetX = 0;
        for ($c = 1; $c <= count($headers); $c++) {
            $next = $acc + $colPx[$c];
            if ($startX < $next) {
                $anchorCol = $c;
                $offsetX   = $startX - $acc;
                break;
            }
            $acc = $next;
        }
        $offsetX = max(0, min($offsetX, max(0, $colPx[$anchorCol] - $imgW)));

        $anchorCell = Coordinate::stringFromColumnIndex($anchorCol).'1';
        $drawing = new Drawing();
        $drawing->setPath($logoPath);
        $drawing->setHeight(56);
        $drawing->setCoordinates($anchorCell);
        $drawing->setOffsetX($offsetX);
        $drawing->setOffsetY(4);
        $drawing->setWorksheet($sheet);
    }

    // Freeze
    $sheet->freezePane('A'.($headerRow+1));

    // Footer
    $webName = ($this->om && method_exists($this->om, 'web_me') && $this->om->web_me())
               ? ($this->om->web_me()->nama_website ?? 'Aplikasi')
               : 'Aplikasi';
    $footerRow = $lastDataRow + 2;
    $sheet->mergeCells("A{$footerRow}:{$lastCol}{$footerRow}");
    $sheet->setCellValue("A{$footerRow}", 'Generated oleh '.$webName.' — '.date('d-m-Y'));
    $sheet->getStyle("A{$footerRow}")->getFont()->setItalic(true)->setSize(9)->getColor()->setARGB('FF777777');
    $sheet->getStyle("A{$footerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    // === AUTO FILTER ===
    // Range filter khusus kolom Asal (G), Unit Tujuan (H), Pejabat (I), Status (J)
    $filterRange = "G{$headerRow}:J{$lastDataRow}";
    $sheet->setAutoFilter($filterRange);

    // (Opsional) jika mau langsung menerapkan filter sesuai request di URL:
    // - Status
    if (!empty($filters['status'])) {
        $statusLabel = $mapStatus($filters['status']); // pakai mapper yang sudah ada di atas
        $af   = $sheet->getAutoFilter();
        $colJ = $af->getColumn('J');
        $colJ->setFilterType(Column::AUTOFILTER_FILTERTYPE_FILTER);
        $colJ->setJoin(Column::AUTOFILTER_COLUMN_JOIN_OR);
        $ruleJ = $colJ->createRule();
        $ruleJ->setRule(FilterRule::AUTOFILTER_COLUMN_RULE_EQUAL, $statusLabel);
        $colJ->addRule($ruleJ);
    }

    // - Unit Tujuan
    if (!empty($filters['unit_tujuan_nama'])) {
        $af   = $sheet->getAutoFilter();
        $colH = $af->getColumn('H');
        $colH->setFilterType(Column::AUTOFILTER_FILTERTYPE_FILTER);
        $ruleH = $colH->createRule();
        $ruleH->setRule(FilterRule::AUTOFILTER_COLUMN_RULE_EQUAL, $filters['unit_tujuan_nama']);
        $colH->addRule($ruleH);
    }

    // - Asal (kalau kamu ingin prefilter persis sama stringnya)
    //   Catatan: kalau "form_asal" dipakai sebagai pencarian bebas, biasanya
    //   tidak kita prefilter persis equal di Excel. Kalau mau, aktifkan blok ini.
    if (!empty($filters['form_asal'])) {
        $af   = $sheet->getAutoFilter();
        $colG = $af->getColumn('G');
        $colG->setFilterType(Column::AUTOFILTER_FILTERTYPE_CUSTOMFILTER);
        $ruleG = $colG->createRule();
        // triknya pakai wildcard agar mirip "contains"
        $ruleG->setRule(FilterRule::AUTOFILTER_COLUMN_RULE_EQUAL, '*'.$filters['form_asal'].'*');
        $colG->addRule($ruleG);
        $colG->setJoin(Column::AUTOFILTER_COLUMN_JOIN_OR);
    }

    // Terapkan hide/show baris sesuai rule (opsional, supaya file terbuka sudah terfilter)
    if (!empty($filters['status']) || !empty($filters['unit_tujuan_nama']) || !empty($filters['form_asal'])) {
        $sheet->getAutoFilter()->showHideRows();
    }

    // Output
    if (ob_get_length()) { ob_end_clean(); }
    $filename = 'booking_tamu_'.date('Ymd_His');
    try {
        if (!class_exists('ZipArchive')) {
            throw new \RuntimeException('ext-zip tidak tersedia');
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = IOFactory::createWriter($ss, 'Xlsx');
        $writer->save('php://output');
    } catch (\Throwable $e) {
        log_message('error', 'export_excel XLSX gagal, fallback ke XLS: '.$e->getMessage());
        if (ob_get_length()) { ob_end_clean(); }
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="'.$filename.'.xls"');
        header('Cache-Control: max-age=0');
        $writer = IOFactory::createWriter($ss, 'Xls');
        $writer->save('php://output');
    }

    $ss->disconnectWorksheets();
    unset($ss);
    exit;
}



}
