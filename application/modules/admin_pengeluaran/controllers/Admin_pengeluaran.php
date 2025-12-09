<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_pengeluaran extends Admin_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('M_admin_pengeluaran','dm');
        cek_session_akses(get_class($this), $this->session->userdata('admin_session')); // jika dipakai
    }

    private function purge_public_caches(){
        $this->load->driver('cache', ['adapter' => 'file']);
        $this->cache->save('pengeluaran_ver', time(), 365*24*3600);
        $this->output->set_header('X-Cache-Purged: pengeluaran');
    }

    public function index(){
        $data["controller"] = get_class($this);
        $data["title"]      = "Pengeluaran";
        $data["subtitle"]   = "Daftar Pengeluaran";
        $data["content"]    = $this->load->view('Admin_pengeluaran_view',$data,true);
        $this->render($data);
    }

    public function get_raw($id=null){
    $id = (int)$id;
    $row = $this->dm->get_row($id);
    if (!$row){ $this->output->set_content_type('application/json')->set_output(json_encode(null)); return; }
    $out = [
        'id'          => (int)$row->id,
        'nomor'       => (string)($row->nomor ?? ''),
        'tanggal'     => (string)($row->tanggal ?? ''),
        'kategori'    => (string)($row->kategori ?? 'Umum'),
        'keterangan'  => (string)($row->keterangan ?? ''),
        'jumlah'      => (string)((int)$row->jumlah),
        'metode_bayar'=> (string)($row->metode_bayar ?? 'cash'),
    ];
    return $this->output->set_content_type('application/json')->set_output(json_encode($out));
}

    /** DataTables server-side */
    public function get_data(){
        try{
            $kategori = $this->input->post('kategori', true) ?: 'all';
            $metode   = $this->input->post('metode', true) ?: 'all';
            $dfrom    = $this->input->post('date_from', true) ?: '';
            $dto      = $this->input->post('date_to', true)   ?: '';

            $this->dm->set_max_rows(1000);
            $this->dm->set_filters($kategori, $metode, $dfrom, $dto);

            $list = $this->dm->get_data();
            $data = [];
            $isAdmin = ($this->session->userdata('admin_username') === 'admin');
            foreach ($list as $r){
                $jumlah    = (int)$r->jumlah;
                $jumlah_html = 'Rp '.number_format($jumlah,0,',','.');
                $tanggal   = $r->tanggal ? date('d-m-Y H:i', strtotime($r->tanggal)) : '-';
                $created   = $r->created_at ? date('d-m-Y H:i', strtotime($r->created_at)) : '-';

                $ket = trim((string)($r->keterangan ?? ''));
               if ($ket !== '') {
                    // pecah berdasarkan enter / baris baru
                    $lines = preg_split("/\r\n|\r|\n/", $ket);

                    $items = [];
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if ($line === '') continue; // skip baris kosong

                        $items[] = '<li>'.htmlspecialchars($line, ENT_QUOTES, 'UTF-8').'</li>';
                    }

                    if (!empty($items)) {
                        // list bernomor
                        $ket = '<ol class="text-dark mb-0 pl-3">'
                             . implode('', $items)
                             . '</ol>';
                    } else {
                        $ket = '';
                    }
                }


                $row = [];
                $row['id']        = (int)$r->id;
                $row['no']        = '';
                $row['tanggal']   = htmlspecialchars($tanggal,ENT_QUOTES,'UTF-8');
                $row['kategori']  = htmlspecialchars($r->kategori ?: '-',ENT_QUOTES,'UTF-8');
                $row['uraian']    = $ket;
                $row['jumlah']    = $jumlah_html;
                $row['metode']    = htmlspecialchars($r->metode_bayar ?: '-',ENT_QUOTES,'UTF-8');
                $row['dibuat']    = htmlspecialchars(($r->created_by ?: '-').' · '.$created,ENT_QUOTES,'UTF-8');

                $id = (int)$r->id;
               $actionsHtml  = '<div class="btn-group btn-group-sm" role="group">';
                // tombol detail selalu ada
                $actionsHtml .= '<button type="button" class="btn btn-info" onclick="show_detail('.$id.')">
                                    <i class="fe-eye"></i>
                                 </button>';

                // tombol edit + hapus hanya untuk admin
                if ($isAdmin) {
                    $actionsHtml .= '<button type="button" class="btn btn-primary ml-1" onclick="edit_one('.$id.')">
                                        <i class="fe-edit"></i>
                                     </button>';
                    $actionsHtml .= '<button type="button" class="btn btn-danger ml-1" onclick="hapus_one('.$id.')">
                                        <i class="fe-trash-2"></i>
                                     </button>';
                }

                $actionsHtml .= '</div>';

                $row['aksi'] = $actionsHtml;

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

    /** Detail */
    public function detail($id=null){
        $id = (int)$id;
        $row = $this->dm->get_row($id);
        if (!$row){ echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]); return; }

        $html = '<div class="table-responsive"><table class="table table-sm table-striped mb-0">';
        $add = function($k,$v) use (&$html){ $html .= '<tr><th style="width:180px">'.$k.'</th><td>'.$v.'</td></tr>'; };
        $add('Nomor', htmlspecialchars($row->nomor ?? '-',ENT_QUOTES,'UTF-8'));
        $add('Tanggal', $row->tanggal ? date('d-m-Y H:i', strtotime($row->tanggal)) : '-');
        $add('Kategori', htmlspecialchars($row->kategori ?? '-',ENT_QUOTES,'UTF-8'));
        if (!empty($row->keterangan)) $add('Keterangan', nl2br(htmlspecialchars($row->keterangan,ENT_QUOTES,'UTF-8')));
        $add('Jumlah', '<b>Rp '.number_format((int)$row->jumlah,0,',','.').'</b>');
        $add('Metode', htmlspecialchars($row->metode_bayar ?? '-',ENT_QUOTES,'UTF-8'));
        $add('Dibuat Oleh', htmlspecialchars($row->created_by ?? '-',ENT_QUOTES,'UTF-8'));
        if (!empty($row->updated_at)){
            $add('Diperbarui', htmlspecialchars($row->updated_by ?? '-',ENT_QUOTES,'UTF-8').' · '.htmlspecialchars($row->updated_at,ENT_QUOTES,'UTF-8'));
        }
        $html .= '</table></div>';

        echo json_encode(["success"=>true, "html"=>$html, "title"=>'Detail Pengeluaran #'.($row->nomor ?: $row->id)]);
    }

    /** Tambah */
    public function create(){
        try{

             $rawTanggal = $this->input->post('tanggal', true);

            // kalau kosong → pakai sekarang
            if ($rawTanggal === null || $rawTanggal === '') {
                $tanggal = date('Y-m-d H:i:s');
            } else {
                $tanggal = $rawTanggal; // sudah dalam format Y-m-d H:i:s dari JS
            }

            // $tanggal   = $this->input->post('tanggal', true) ?: date('Y-m-d H:i:s');
            $kategori  = $this->input->post('kategori', true) ?: 'Umum';
            // $keterangan= $this->input->post('keterangan', true);
            $keterangan = trim((string)$this->input->post('keterangan', true));
                if ($keterangan === '') {
                    echo json_encode([
                        "success" => false,
                        "title"   => "Gagal",
                        "pesan"   => "Keterangan tidak boleh kosong"
                    ]);
                    return;
                }

            $jumlahStr = $this->input->post('jumlah', true);
            $metode    = $this->input->post('metode_bayar', true) ?: 'cash';

            $jumlah = (int)preg_replace('/[^\d]/','',$jumlahStr);
            if ($jumlah <= 0){
                echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Jumlah harus lebih dari 0"]); return;
            }

            $nomor = date('YmdHis').'-'.mt_rand(100,999);
            $this->db->where("username", $this->session->userdata('admin_username'));
            $us = $this->db->get('users')->row();
            $user  = $us->nama_lengkap;

            $ok = $this->dm->insert([
                'nomor'        => $nomor,
                'tanggal'      => $tanggal,
                'kategori'     => $kategori,
                'keterangan'   => $keterangan,
                'jumlah'       => $jumlah,
                'metode_bayar' => $metode,
                'created_by'   => $user,
                'created_at'   => date('Y-m-d H:i:s'),
            ]);

            if (!$ok){ echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Tidak bisa menyimpan data"]); return; }
            $this->purge_public_caches();
            echo json_encode(["success"=>true,"title"=>"Berhasil","pesan"=>"Pengeluaran ditambahkan"]);
        }catch(\Throwable $e){
            echo json_encode(["success"=>false,"title"=>"Error","pesan"=>$e->getMessage()]);
        }
    }

    /** Ubah */
    public function update(){
        try{
            $id        = (int)$this->input->post('id');
            if ($id<=0){ echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"ID tidak valid"]); return; }
             $rawTanggal = $this->input->post('tanggal', true);

                // kalau kosong → pakai sekarang
                if ($rawTanggal === null || $rawTanggal === '') {
                    $tanggal = date('Y-m-d H:i:s');
                } else {
                    $tanggal = $rawTanggal; // sudah dalam format Y-m-d H:i:s dari JS
                }

            // $tanggal   = $this->input->post('tanggal', true);
            $kategori  = $this->input->post('kategori', true);
            // $keterangan= $this->input->post('keterangan', true);
            $keterangan = trim((string)$this->input->post('keterangan', true));
            if ($keterangan === '') {
                echo json_encode([
                    "success" => false,
                    "title"   => "Gagal",
                    "pesan"   => "Keterangan tidak boleh kosong"
                ]);
                return;
            }

            $jumlahStr = $this->input->post('jumlah', true);
            $metode    = $this->input->post('metode_bayar', true);

            $jumlah = (int)preg_replace('/[^\d]/','',$jumlahStr);
            if ($jumlah <= 0){
                echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Jumlah harus lebih dari 0"]); return;
            }

            $this->db->where("username", $this->session->userdata('admin_username'));
            $us = $this->db->get('users')->row();
            $user  = $us->nama_lengkap;

            $ok = $this->dm->update($id, [
                'tanggal'      => $tanggal,
                'kategori'     => $kategori,
                'keterangan'   => $keterangan,
                'jumlah'       => $jumlah,
                'metode_bayar' => $metode,
                'updated_by'   => $user,
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);

            if (!$ok){ echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Tidak bisa memperbarui data"]); return; }
            $this->purge_public_caches();
            echo json_encode(["success"=>true,"title"=>"Berhasil","pesan"=>"Pengeluaran diperbarui"]);
        }catch(\Throwable $e){
            echo json_encode(["success"=>false,"title"=>"Error","pesan"=>$e->getMessage()]);
        }
    }

    /** Hapus (hard delete) */
    public function delete(){
        $id = (int)$this->input->post('id');
        if ($id<=0){ echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"ID tidak valid"]); return; }
        $ok = $this->dm->delete($id);
        if ($ok){ $this->purge_public_caches(); }
        echo json_encode(["success"=>$ok,"title"=>$ok?"Berhasil":"Gagal","pesan"=>$ok?"Data dihapus":"Tidak bisa menghapus data"]);
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

        // PAKSA landscape
        $pdf->AddPage('L', 'A4');

        $pdf->SetFont('dejavusans','',9);
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output($filename, 'I');
        exit;
    }

    public function cetak()
{
    // Ambil filter dari GET (sesuai JS tombol Cetak)
    $kategori = $this->input->get('kategori', true) ?: 'all';
    $metode   = $this->input->get('metode', true)   ?: 'all';
    $dfrom    = $this->input->get('date_from', true) ?: '';
    $dto      = $this->input->get('date_to', true)   ?: '';
    $search   = trim((string)$this->input->get('q', true) ?? '');

    // set filter ke model
    $this->dm->set_filters($kategori, $metode, $dfrom, $dto);

    // ambil semua baris sesuai filter (tanpa limit)
    $rows = $this->dm->get_all_for_print($search);

    // ==== hitung summary ====
    $sum = [
        'count'       => 0,
        'total'       => 0,
        'by_kategori' => []
    ];

    foreach ($rows as $r) {
        $sum['count']++;
        $jml = (int)$r->jumlah;
        $sum['total'] += $jml;

        $katKey = $r->kategori ?: 'Tanpa Kategori';
        if (!isset($sum['by_kategori'][$katKey])) {
            $sum['by_kategori'][$katKey] = 0;
        }
        $sum['by_kategori'][$katKey] += $jml;
    }

    // helper format Rupiah
    $idr = function($n){
        return 'Rp '.number_format((int)$n, 0, ',', '.');
    };

    // helper tanggal Indonesia (dengan jam)
    $tgl_indo = function($datetimeStr, $withTime = true){
        if (empty($datetimeStr)) return '-';
        $ts = strtotime($datetimeStr);
        if ($ts === false) return '-';
        $bulan = [
            1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                 'Jul', 'Agus', 'Sept', 'Okt', 'Nov', 'Des'
        ];
        $res = date('d', $ts).' '.$bulan[(int)date('n', $ts)].' '.date('Y', $ts);
        if ($withTime) {
            $res .= ' '.date('H:i', $ts);
        }
        return $res;
    };

    $title = 'Laporan Pengeluaran';

    // teks periode pakai tanggal Indo juga
    if ($dfrom || $dto) {
        $p1 = $dfrom ? $tgl_indo($dfrom.' 00:00:00', false) : '...';
        $p2 = $dto   ? $tgl_indo($dto.' 23:59:59', false)   : '...';
        $periode_text = $p1.' s.d '.$p2;
    } else {
        $periode_text = 'Semua tanggal';
    }

    $ketKategori = ($kategori === 'all') ? 'Semua Kategori' : $kategori;
    $ketMetode   = ($metode === 'all')   ? 'Semua Metode'   : strtoupper($metode);

    $filter_text = 'Kategori: '.$ketKategori.' | Metode: '.$ketMetode;
    if ($search !== '') {
        $filter_text .= ' | Pencarian: "'.$search.'"';
    }

    // siapkan data untuk view PDF
    $data = [
        'title'        => $title,
        'rows'         => $rows,
        'sum'          => $sum,
        'idr'          => $idr,
        'tgl_indo'     => $tgl_indo,
        'periode_text' => $periode_text,
        'filter_text'  => $filter_text,
    ];

    // render view jadi HTML string
    $html = $this->load->view('Admin_pengeluaran_pdf', $data, true);

    // Nama file PDF
    $fnFrom = $dfrom ?: 'all';
    $fnTo   = $dto   ?: 'all';
    $filename = 'pengeluaran-'.$fnFrom.'-'.$fnTo.'.pdf';

    $this->_pdf($title, $html, $filename);
}



}
