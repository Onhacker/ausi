<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_meja extends Admin_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('M_admin_meja','dm');
        cek_session_akses(get_class($this), $this->session->userdata('admin_session')); // jika dipakai
    }

    public function index(){
        $data["controller"] = get_class($this);
        $data["title"]      = "Master";
        $data["subtitle"]   = $this->om->engine_nama_menu(get_class($this));
        $data["content"]    = $this->load->view($data["controller"]."_view",$data,true);
        $this->render($data);
    }

    /** DataTables server-side */
    public function get_dataa(){
        $list = $this->dm->get_data();
        $data = [];
        foreach($list as $r){
            $qr = $r->qrcode
                ? '<a class="badge badge-info" target="_blank" href="'.base_url($r->qrcode).'">QR</a>'
                : '<span class="badge badge-secondary">-</span>';

            $btns  = '<button type="button" class="btn btn-sm btn-warning mr-1" onclick="edit('.(int)$r->id.')"><i class="fe-edit"></i> Edit</button>';
            $btns .= '<button type="button" class="btn btn-sm btn-primary mr-1" onclick="print_qr('.(int)$r->id.')"><i class="fe-printer"></i> Print QR</button>';

            $row               = [];
            $row['cek']        = '<div class="checkbox checkbox-primary checkbox-single"><input type="checkbox" class="data-check" value="'.$r->id.'"><label></label></div>';
            $row['no']         = '';
            $row['nama']       = htmlspecialchars($r->nama, ENT_QUOTES, 'UTF-8');
            $row['kode']       = '<code>'.htmlspecialchars($r->kode, ENT_QUOTES, 'UTF-8').'</code>';
            $row['kapasitas']  = (int)$r->kapasitas;
            $row['area']       = htmlspecialchars($r->area, ENT_QUOTES, 'UTF-8');
            $row['status']     = $r->status === 'aktif'
                                 ? '<span class="badge badge-success">Aktif</span>'
                                 : '<span class="badge badge-danger">Nonaktif</span>';
            $row['qr']         = $qr;
            $row['aksi']       = $btns;
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

    /** Ambil satu baris */
    public function get_one($id){
        $id = (int)$id;
        $row = $this->db->get_where('meja',['id'=>$id])->row();
        if (!$row) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]);
            return;
        }
        echo json_encode(["success"=>true,"data"=>$row]);
    }

    /** Create */
    public function add(){
        $this->load->library('form_validation');
        $this->form_validation->set_rules('nama','Nama','trim|required|min_length[1]|max_length[100]');
        $this->form_validation->set_rules('kapasitas','Kapasitas','trim|integer');
        $this->form_validation->set_rules('area','Area','trim|max_length[100]');
        $this->form_validation->set_rules('status','Status','trim|required|in_list[aktif,nonaktif]');

        if ($this->form_validation->run() !== TRUE) {
            echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>validation_errors()]);
            return;
        }

        $nama      = $this->input->post('nama', true);
        $kapasitas = (int)$this->input->post('kapasitas', true);
        $area      = $this->input->post('area', true);
        $status    = $this->input->post('status', true);

        // kode meja human-readable, misal M10001
        $kode = $this->dm->generate_unique_kode($nama);

        // token rahasia untuk keamanan QR
        $qr_token = $this->_gen_token();

        $ok = $this->db->insert('meja', [
            'nama'       => $nama,
            'kapasitas'  => $kapasitas ?: null,
            'area'       => $area ?: null,
            'status'     => $status,
            'kode'       => $kode,
            'qr_token'   => $qr_token,
        ]);

        if ($ok){
            $id = $this->db->insert_id();
            // generate file QR untuk meja ini
            $qrPath = $this->_generate_qr($id); // simpan file QR & path
            if ($qrPath) {
                $this->db->where('id',$id)->update('meja',['qrcode'=>$qrPath]);
            }
        }

        echo json_encode([
            "success"=>$ok,
            "title"=>$ok?"Berhasil":"Gagal",
            "pesan"=>$ok?"Data berhasil disimpan":"Data gagal disimpan"
        ]);
    }

    /** Update */
    public function update(){
        $this->load->library('form_validation');
        $this->form_validation->set_rules('id','ID','required|integer');
        $this->form_validation->set_rules('nama','Nama','trim|required|min_length[1]|max_length[100]');
        $this->form_validation->set_rules('kapasitas','Kapasitas','trim|integer');
        $this->form_validation->set_rules('area','Area','trim|max_length[100]');
        $this->form_validation->set_rules('status','Status','trim|required|in_list[aktif,nonaktif]');

        if ($this->form_validation->run() !== TRUE) {
            echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>validation_errors()]);
            return;
        }

        $id   = (int)$this->input->post('id', true);
        $row  = $this->db->get_where('meja',['id'=>$id])->row();
        if (!$row) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]);
            return;
        }

        $nama      = $this->input->post('nama', true);
        $kapasitas = (int)$this->input->post('kapasitas', true);
        $area      = $this->input->post('area', true);
        $status    = $this->input->post('status', true);

        $data = [
            'nama'       => $nama,
            'kapasitas'  => $kapasitas ?: null,
            'area'       => $area ?: null,
            'status'     => $status,
        ];

        // pastikan kode tetap ada (unik)
        if (empty($row->kode)) {
            $data['kode'] = $this->dm->generate_unique_kode($nama, $id);
        }

        // pastikan qr_token juga tetap ada
        $qr_token = trim((string)$row->qr_token);
        if ($qr_token === '') {
            $qr_token = $this->_gen_token();
            $data['qr_token'] = $qr_token;
        }

        $ok = $this->db->where('id',$id)->update('meja',$data);

        if ($ok){
            // regenerate QR jika perlu / jika file hilang / force
            $qrPath = $this->_generate_qr($id, true);
            if ($qrPath) {
                $this->db->where('id',$id)->update('meja',['qrcode'=>$qrPath]);
            }
        }

        echo json_encode([
            "success"=>$ok,
            "title"=>$ok?"Berhasil":"Gagal",
            "pesan"=>$ok?"Data berhasil diupdate":"Data gagal diupdate"
        ]);
    }

    /** Delete (bulk) */
    public function hapus_data(){
        $ids = $this->input->post('id');
        if (!is_array($ids) || count($ids) === 0) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Tidak ada data"]);
            return;
        }

        $ok = true;
        foreach ($ids as $id) {
            $id = (int)$id;
            if ($id <= 0) continue;

            // hapus file QR jika ada
            $row = $this->db->get_where('meja',['id'=>$id])->row();
            if ($row && $row->qrcode && file_exists(FCPATH.$row->qrcode)) {
                @unlink(FCPATH.$row->qrcode);
            }

            $ok = $ok && $this->db->delete('meja', ['id'=>$id]);
        }

        echo json_encode([
            "success"=>$ok,
            "title"=>$ok?"Berhasil":"Gagal",
            "pesan"=>$ok?"Data berhasil dihapus":"Sebagian data gagal dihapus"
        ]);
    }

    /** Halaman cetak QR */
    public function print_qr($id){
        $id  = (int)$id;
        $row = $this->db->get_where('meja',['id'=>$id])->row();
        if (!$row) show_404();

        // pastikan file QR ada & up-to-date
        if (!$row->qrcode || !file_exists(FCPATH.$row->qrcode)) {
            $qrPath = $this->_generate_qr($id, true);
            if ($qrPath) {
                $row->qrcode = $qrPath;
                $this->db->where('id',$id)->update('meja',['qrcode'=>$qrPath]);
            }
        }

        $data = [
            'row'    => $row,
            'qr_url' => base_url($row->qrcode),
        ];
        $this->load->view('admin_meja_print_qr', $data);
    }

    /** QR generator */
    private function _generate_qr($id, $force=false){
        $row = $this->db->get_where('meja',['id'=>$id])->row();
        if (!$row) return null;

        $this->_ensure_upload_dir();

        // pastikan kode ada
        $kode = trim((string)$row->kode);
        if ($kode === '') {
            $kode = $this->dm->generate_unique_kode($row->nama, $id);
            $this->db->where('id',$id)->update('meja',['kode'=>$kode]);
        }

        // pastikan qr_token ada
        $qr_token = trim((string)$row->qr_token);
        if ($qr_token === '') {
            $qr_token = $this->_gen_token();
            $this->db->where('id',$id)->update('meja',['qr_token'=>$qr_token]);
        }

        // nama file QR (pakai kode meja, biar gampang cari)
        $qrRel = 'uploads/qrcodes/meja/'.$kode.'.png';
        $qrAbs = FCPATH.$qrRel;

        if (file_exists($qrAbs) && !$force) {
            return $qrRel;
        }

        // URL yang akan di-scan
        // sekarang format: /t/{kode}/{qr_token}
        $dataUrl = site_url('t/'.$kode.'/'.$qr_token);

        // Generate QR base
        $this->load->library('ciqrcode');
        $params = [
            'data'     => $dataUrl,
            'level'    => 'H',   // High error correction
            'size'     => 12,    // resolusi bagus
            'savename' => $qrAbs
        ];
        $ok = $this->ciqrcode->generate($params);
        if (!$ok) return null;

        // tempel logo tengah biar branding
        $this->_overlay_logo($qrAbs, 'assets/images/logo_admin.png');

        return $qrRel;
    }

    /** Logo overlay di tengah QR */
    private function _overlay_logo(string $qrAbs, string $logoRel): bool {
    if (!function_exists('imagecreatetruecolor')) return false;

    $logoAbs = FCPATH.$logoRel;
    if (!file_exists($qrAbs) || !file_exists($logoAbs)) return false;

    // buka QR
    $qr = @imagecreatefrompng($qrAbs);
    if (!$qr) return false;
    imagesavealpha($qr, true);

    $qrW = imagesx($qr);
    $qrH = imagesy($qr);

    // buka logo
    $ext = strtolower(pathinfo($logoAbs, PATHINFO_EXTENSION));
    if     ($ext==='png')  { $logoSrc = @imagecreatefrompng($logoAbs); }
    elseif ($ext==='jpg' || $ext==='jpeg') { $logoSrc = @imagecreatefromjpeg($logoAbs); }
    elseif ($ext==='gif')  { $logoSrc = @imagecreatefromgif($logoAbs); }
    else {
        imagedestroy($qr);
        return false;
    }
    if (!$logoSrc){
        imagedestroy($qr);
        return false;
    }
    imagesavealpha($logoSrc, true);

    $logoW = imagesx($logoSrc);
    $logoH = imagesy($logoSrc);

    // ukuran kartu/logo relatif ke QR
    $ratio      = 0.22; // boleh turunin ke 0.18 kalau mau lebih kecil
    $targetW    = (int) round($qrW * $ratio);
    $targetH    = (int) round($logoH * ($targetW / max(1,$logoW)));

    // padding putih di sekitar logo
    $pad        = max(6, (int) round($targetW * 0.18));
    $bgW        = $targetW + 2*$pad;
    $bgH        = $targetH + 2*$pad;

    // radius sudut kartu
    $radius     = (int) round(min($bgW,$bgH) * 0.20);

    // tebal border
    $border     = 4; // pixel outline

    // canvas kartu (punya alpha)
    $bg = imagecreatetruecolor($bgW, $bgH);
    imagesavealpha($bg, true);
    $trans = imagecolorallocatealpha($bg, 0,0,0,127); // full transparent
    imagefill($bg, 0,0, $trans);

    // warna border (hitam semi-transparan)
    $strokeCol = imagecolorallocatealpha($bg, 0,0,0,0); // hitam solid

    // warna isi kartu (putih solid)
    $fillCol   = imagecolorallocatealpha($bg, 255,255,255,0);

    // helper kecil buat gambar rounded rect
    $drawRounded = function($im,$x,$y,$w,$h,$r,$col){
        // badan
        imagefilledrectangle($im, $x+$r, $y,       $x+$w-$r, $y+$h,     $col);
        imagefilledrectangle($im, $x,     $y+$r,   $x+$w,    $y+$h-$r,  $col);
        // 4 sudut
        imagefilledellipse($im, $x+$r,         $y+$r,         2*$r, 2*$r, $col);
        imagefilledellipse($im, $x+$w-$r,      $y+$r,         2*$r, 2*$r, $col);
        imagefilledellipse($im, $x+$r,         $y+$h-$r,      2*$r, 2*$r, $col);
        imagefilledellipse($im, $x+$w-$r,      $y+$h-$r,      2*$r, 2*$r, $col);
    };

    // 1. gambar border dulu (agak lebih besar radiusnya biar lembut)
    $drawRounded($bg, 0, 0, $bgW, $bgH, $radius+2, $strokeCol);

    // 2. gambar isi putih sedikit ke dalam, supaya border kelihatan
    $innerX = $border;
    $innerY = $border;
    $innerW = $bgW - 2*$border;
    $innerH = $bgH - 2*$border;
    $drawRounded($bg, $innerX, $innerY, $innerW, $innerH, max(1,$radius-2), $fillCol);

    // 3. resize logo ke tengah kartu putih
    $logoResized = imagecreatetruecolor($targetW, $targetH);
    imagesavealpha($logoResized, true);
    $transparent = imagecolorallocatealpha($logoResized, 0,0,0,127);
    imagefill($logoResized, 0,0, $transparent);

    imagecopyresampled(
        $logoResized,
        $logoSrc,
        0, 0,
        0, 0,
        $targetW, $targetH,
        $logoW, $logoH
    );

    // tempel logo di tengah kartu putih
    $logoDstX = $innerX + (int)(($innerW - $targetW)/2);
    $logoDstY = $innerY + (int)(($innerH - $targetH)/2);
    imagecopy(
        $bg,
        $logoResized,
        $logoDstX, $logoDstY,
        0,0,
        $targetW, $targetH
    );

    // 4. tempel kartu (bg) ke tengah QR
    $dstX = (int) (($qrW - $bgW) / 2);
    $dstY = (int) (($qrH - $bgH) / 2);
    imagecopy(
        $qr,    // dst img (QR)
        $bg,    // src img (kartu dengan border + logo)
        $dstX, $dstY,
        0,0,
        $bgW, $bgH
    );

    // simpan final
    imagepng($qr, $qrAbs, 6);

    // cleanup
    imagedestroy($logoSrc);
    imagedestroy($logoResized);
    imagedestroy($bg);
    imagedestroy($qr);

    return true;
}


    private function _ensure_upload_dir(){
        $dir = FCPATH.'uploads/qrcodes/meja';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        if (!file_exists($dir.'/index.html')) @file_put_contents($dir.'/index.html','');
    }

    /** generator token acak untuk qr_token */
    private function _gen_token($lenBytes = 16){
        // 16 byte => 32 hex chars
        return bin2hex(random_bytes($lenBytes));
    }
}
