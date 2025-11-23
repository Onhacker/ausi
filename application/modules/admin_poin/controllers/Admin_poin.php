<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_poin extends Admin_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('M_admin_poin','dm');
        $this->load->database();
        cek_session_akses(get_class($this), $this->session->userdata('admin_session'));
    }

    public function index(){
    $data["controller"] = get_class($this);
    $data["title"]      = "Poin Loyalty Café";
    $data["subtitle"]   = $this->om->engine_nama_menu(get_class($this));

    // Cari tanggal expired terakhir di voucher_cafe
    $last = $this->db
        ->select('expired_at')
        ->from('voucher_cafe')
        ->where('expired_at IS NOT NULL', null, false)
        ->where('expired_at <>', '0000-00-00')
        ->order_by('expired_at', 'DESC')
        ->limit(1)
        ->get()
        ->row();

    if ($last && !empty($last->expired_at)) {
        $dt          = new DateTime($last->expired_at);
        $lastYear    = (int)$dt->format('Y');
        $lastMonth   = (int)$dt->format('n'); // 1–12
        $lastDay     = (int)$dt->format('j'); // 1–31
        $lastWeek    = (int)ceil($lastDay / 7); // 1–5
    } else {
        // fallback kalau tabel kosong
        $dt          = new DateTime();
        $lastYear    = (int)$dt->format('Y');
        $lastMonth   = (int)$dt->format('n');
        $lastDay     = (int)$dt->format('j');
        $lastWeek    = (int)ceil($lastDay / 7);
    }

    // opsi tahun (misal: tahun sekarang -1 s/d +1)
    $years = [];
    for($y = $lastYear - 1; $y <= $lastYear + 1; $y++){
        $years[] = $y;
    }

    $data["years"]        = $years;
    $data["defaultYear"]  = $lastYear;
    $data["defaultMonth"] = $lastMonth;
    $data["defaultWeek"]  = $lastWeek;

    $data["content"]  = $this->load->view($data["controller"]."_view",$data,true);
    $this->render($data);
}


    /**
     * Data untuk DataTables
     * Filter: tahun, bulan, minggu_ke (berdasarkan expired_at)
     */
    public function get_data(){
        $tahun   = (int)$this->input->post('tahun');
        $bulan   = (int)$this->input->post('bulan');
        $minggu  = (int)$this->input->post('minggu');

        // set filter ke model
        $this->dm->set_filter($tahun, $bulan, $minggu);

        $list = $this->dm->get_data();
        $data = [];

        foreach($list as $r){
            $id            = (int)$r->id;
            $nama          = isset($r->customer_name) ? trim((string)$r->customer_name) : '';
            $hp            = isset($r->customer_phone) ? trim((string)$r->customer_phone) : '';
            $points        = (int)$r->points;
            $trxCount      = (int)$r->transaksi_count;
            $totalRupiah   = (int)$r->total_rupiah;
            $expired       = $r->expired_at;
            $minggu_ke     = (int)$r->minggu_ke;

            $namaShow = $nama !== '' ? $nama : '-';
            $hpShow   = $hp   !== '' ? $hp   : '-';

            $expiredShow = ($expired && $expired !== '0000-00-00')
                ? date('d-m-Y', strtotime($expired))
                : '-';

            $mingguShow = $minggu_ke > 0 ? ('Minggu ke-'.$minggu_ke) : '-';

            $btnDetail = '<button type="button" class="btn btn-sm btn-outline-primary"'
                       . ' onclick="show_detail('.$id.')">'
                       . '<i class="fe-eye me-1"></i>Detail</button>';

            $row = [
                'no'             => '',
                'nama'           => htmlspecialchars($namaShow, ENT_QUOTES, 'UTF-8'),
                'no_hp'          => htmlspecialchars($hpShow, ENT_QUOTES, 'UTF-8'),
                'points'         => $points,
                'transaksi'      => $trxCount,
                'total_rupiah'   => number_format($totalRupiah, 0, ',', '.'),
                'minggu_ke'      => $mingguShow,
                'expired_at'     => $expiredShow,
                'aksi'           => $btnDetail,
            ];
            $data[] = $row;
        }

        $out = [
            "draw"            => (int)$this->input->post('draw'),
            "recordsTotal"    => $this->dm->count_all(),
            "recordsFiltered" => $this->dm->count_filtered(),
            "data"            => $data,
        ];
        $this->output->set_content_type('application/json')->set_output(json_encode($out));
    }

    /**
     * Detail 1 row poin (by id voucher_cafe)
     */
    public function detail(){
        $id = (int)$this->input->post('id');
        if($id <= 0){
            echo json_encode([
                "success" => false,
                "title"   => "Gagal",
                "pesan"   => "ID tidak valid"
            ]);
            return;
        }

        $row = $this->db->get_where('voucher_cafe', ['id' => $id])->row();
        if(!$row){
            echo json_encode([
                "success" => false,
                "title"   => "Gagal",
                "pesan"   => "Data tidak ditemukan"
            ]);
            return;
        }

        $nama    = htmlspecialchars((string)$row->customer_name, ENT_QUOTES, 'UTF-8');
        $hp      = htmlspecialchars((string)$row->customer_phone, ENT_QUOTES, 'UTF-8');
        $points  = (int)$row->points;
        $trx     = (int)$row->transaksi_count;
        $total   = (int)$row->total_rupiah;
        $token   = htmlspecialchars((string)$row->token, ENT_QUOTES, 'UTF-8');

        $firstPaid  = $row->first_paid_at ? date('d-m-Y H:i', strtotime($row->first_paid_at)) : '-';
        $lastPaid   = $row->last_paid_at  ? date('d-m-Y H:i', strtotime($row->last_paid_at))  : '-';
        $expired    = $row->expired_at    ? date('d-m-Y',     strtotime($row->expired_at))    : '-';
        $createdAt  = $row->created_at    ? date('d-m-Y H:i', strtotime($row->created_at))    : '-';
        $updatedAt  = $row->updated_at    ? date('d-m-Y H:i', strtotime($row->updated_at))    : '-';

        // hitung minggu ke- berdasarkan expired_at (1–7, 8–14, dst)
        $mingguKe = '-';
        if ($row->expired_at && $row->expired_at !== '0000-00-00') {
            $day = (int)date('j', strtotime($row->expired_at));
            $mingguKe = (int)ceil($day / 7);
        }

        $html  = '<div class="table-responsive">';
        $html .= '<table class="table table-sm table-striped mb-0">';
        $html .= '<tr><th width="35%">Nama</th><td>'.$nama.'</td></tr>';
        $html .= '<tr><th>No. HP</th><td>'.$hp.'</td></tr>';
        $html .= '<tr><th>Poin</th><td>'.$points.'</td></tr>';
        $html .= '<tr><th>Jumlah Transaksi</th><td>'.$trx.'</td></tr>';
        $html .= '<tr><th>Total Belanja</th><td>Rp '.number_format($total,0,',','.').'</td></tr>';
        $html .= '<tr><th>Minggu ke-</th><td>'.$mingguKe.'</td></tr>';
        $html .= '<tr><th>Expired</th><td>'.$expired.'</td></tr>';
        $html .= '<tr><th>First Paid</th><td>'.$firstPaid.'</td></tr>';
        $html .= '<tr><th>Last Paid</th><td>'.$lastPaid.'</td></tr>';
        $html .= '<tr><th>Token</th><td><code>'.$token.'</code></td></tr>';
        $html .= '<tr><th>Dibuat</th><td>'.$createdAt.'</td></tr>';
        $html .= '<tr><th>Diupdate</th><td>'.$updatedAt.'</td></tr>';
        $html .= '</table>';
        $html .= '</div>';

        echo json_encode([
            "success" => true,
            "title"   => "Detail Poin Pelanggan",
            "html"    => $html
        ]);
    }

}
