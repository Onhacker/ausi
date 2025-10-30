<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_meja extends Admin_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('M_admin_meja','dm');
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
            $qr = $r->qrcode ? '<a class="badge badge-info" target="_blank" href="'.base_url($r->qrcode).'">QR</a>' : '<span class="badge badge-secondary">-</span>';
            $btns  = '<button type="button" class="btn btn-sm btn-warning mr-1" onclick="edit('.(int)$r->id.')"><i class="fe-edit"></i> Edit</button>';
            $btns .= '<button type="button" class="btn btn-sm btn-primary mr-1" onclick="print_qr('.(int)$r->id.')"><i class="fe-printer"></i> Print QR</button>';

            $row = [];
            $row['cek']       = '<div class="checkbox checkbox-primary checkbox-single"><input type="checkbox" class="data-check" value="'.$r->id.'"><label></label></div>';
            $row['no']        = '';
            $row['nama']      = htmlspecialchars($r->nama, ENT_QUOTES, 'UTF-8');
            $row['kode']      = '<code>'.htmlspecialchars($r->kode, ENT_QUOTES, 'UTF-8').'</code>';
            $row['kapasitas'] = (int)$r->kapasitas;
            $row['area']      = htmlspecialchars($r->area, ENT_QUOTES, 'UTF-8');
            $row['status']    = $r->status === 'aktif'
                                ? '<span class="badge badge-success">Aktif</span>'
                                : '<span class="badge badge-danger">Nonaktif</span>';
            $row['qr']        = $qr;
            $row['aksi']      = $btns;
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
        if (!$row) { echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]); return; }
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
            echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>validation_errors()]); return;
        }

        $nama      = $this->input->post('nama', true);
        $kapasitas = (int)$this->input->post('kapasitas', true);
        $area      = $this->input->post('area', true);
        $status    = $this->input->post('status', true);

        $kode = $this->dm->generate_unique_kode($nama);

        $ok = $this->db->insert('meja', [
            'nama'      => $nama,
            'kapasitas' => $kapasitas ?: null,
            'area'      => $area ?: null,
            'status'    => $status,
            'kode'      => $kode,
        ]);

        if ($ok){
            $id = $this->db->insert_id();
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
            echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>validation_errors()]); return;
        }

        $id        = (int)$this->input->post('id', true);
        $row       = $this->db->get_where('meja',['id'=>$id])->row();
        if (!$row) { echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]); return; }

        $nama      = $this->input->post('nama', true);
        $kapasitas = (int)$this->input->post('kapasitas', true);
        $area      = $this->input->post('area', true);
        $status    = $this->input->post('status', true);

        $data = [
            'nama'      => $nama,
            'kapasitas' => $kapasitas ?: null,
            'area'      => $area ?: null,
            'status'    => $status,
        ];

        // regenerate kode bila kosong (harus tetap unik)
        if (empty($row->kode)) {
            $data['kode'] = $this->dm->generate_unique_kode($nama, $id);
        }

        $ok = $this->db->where('id',$id)->update('meja',$data);

        if ($ok){
            // jg pastikan QR ada (atau regenerate jika kode berubah)
            $qrPath = $this->_generate_qr($id, true);
            if ($qrPath) { $this->db->where('id',$id)->update('meja',['qrcode'=>$qrPath]); }
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
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Tidak ada data"]); return;
        }

        $ok = true;
        foreach ($ids as $id) {
            $id = (int)$id;
            if ($id <= 0) continue;
            // hapus file QR jika ada
            $row = $this->db->get_where('meja',['id'=>$id])->row();
            if ($row && $row->qrcode && file_exists(FCPATH.$row->qrcode)) @unlink(FCPATH.$row->qrcode);
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

        if (!$row->qrcode || !file_exists(FCPATH.$row->qrcode)) {
            $qrPath = $this->_generate_qr($id, true);
            if ($qrPath) { $row->qrcode = $qrPath; $this->db->where('id',$id)->update('meja',['qrcode'=>$qrPath]); }
        }

        $data = ['row'=>$row, 'qr_url'=> base_url($row->qrcode)];
        $this->load->view('admin_meja_print_qr', $data);
    }

    /** QR generator */
    /** QR generator */
private function _generate_qr($id, $force=false){
    $row = $this->db->get_where('meja',['id'=>$id])->row();
    if (!$row) return null;

    $this->_ensure_upload_dir();
    $kode = trim((string)$row->kode);
    if ($kode==='') $kode = $this->dm->generate_unique_kode($row->nama, $id);

    $qrRel = 'uploads/qrcodes/meja/'.$kode.'.png';
    $qrAbs = FCPATH.$qrRel;

    if (file_exists($qrAbs) && !$force) return $qrRel;

    // URL tujuan saat discan
    $dataUrl = site_url('t/'.$kode);

    // Buat QR dasar (ukurannya sedikit lebih besar agar aman dengan logo)
    $this->load->library('ciqrcode');
    $params = [
        'data'     => $dataUrl,
        'level'    => 'H',   // High error correction -> aman tertutup logo
        'size'     => 12,    // lebih besar dari 10 agar resolusi mantap
        'savename' => $qrAbs
    ];
    $ok = $this->ciqrcode->generate($params);
    if (!$ok) return null;

    // Overlay logo di tengah (abaikan jika GD tidak tersedia / logo tidak ada)
    $this->_overlay_logo($qrAbs, 'assets/images/logo_admin.png');

    return $qrRel;
}
/** Overlay logo di tengah QR.
 *  $logoRel: relative path dari FCPATH, contoh 'assets/images/logo_admin.png'
 */
private function _overlay_logo(string $qrAbs, string $logoRel): bool {
    // Pastikan GD tersedia
    if (!function_exists('imagecreatetruecolor')) return false;

    $logoAbs = FCPATH.$logoRel;
    if (!file_exists($qrAbs) || !file_exists($logoAbs)) return false;

    // Buka QR
    $qr = @imagecreatefrompng($qrAbs);
    if (!$qr) return false;
    imagesavealpha($qr, true);

    $qrW = imagesx($qr);
    $qrH = imagesy($qr);

    // Buka logo (png/jpg/gif)
    $ext = strtolower(pathinfo($logoAbs, PATHINFO_EXTENSION));
    if     ($ext==='png') $logo = @imagecreatefrompng($logoAbs);
    elseif ($ext==='jpg' || $ext==='jpeg') $logo = @imagecreatefromjpeg($logoAbs);
    elseif ($ext==='gif') $logo = @imagecreatefromgif($logoAbs);
    else { imagedestroy($qr); return false; }

    if (!$logo){ imagedestroy($qr); return false; }
    imagesavealpha($logo, true);

    $logoW = imagesx($logo);
    $logoH = imagesy($logo);

    // Target: sekitar 22% lebar QR, dengan padding putih & rounded agar scanner mudah baca
    $ratio      = 0.22;                            // besar logo relatif lebar QR
    $targetW    = (int) round($qrW * $ratio);
    $targetH    = (int) round($logoH * ($targetW / max(1,$logoW)));
    $pad        = max(6, (int) round($targetW * 0.18)); // padding putih di sekitar logo
    $bgW        = $targetW + 2*$pad;
    $bgH        = $targetH + 2*$pad;
    $radius     = (int) round(min($bgW,$bgH) * 0.20);    // sudut membulat

    // Kanvas background transparan
    $bg = imagecreatetruecolor($bgW, $bgH);
    imagesavealpha($bg, true);
    $trans = imagecolorallocatealpha($bg, 0,0,0,127);
    imagefill($bg, 0,0, $trans);

    // Kotak putih rounded (tebal, bantu kontras)
    $white = imagecolorallocate($bg, 255,255,255);

    // Rounded rectangle manual (2 rect + 4 ellipse)
    imagefilledrectangle($bg, $radius, 0, $bgW-$radius, $bgH, $white);
    imagefilledrectangle($bg, 0, $radius, $bgW, $bgH-$radius, $white);
    imagefilledellipse($bg, $radius, $radius, 2*$radius, 2*$radius, $white);
    imagefilledellipse($bg, $bgW-$radius, $radius, 2*$radius, 2*$radius, $white);
    imagefilledellipse($bg, $radius, $bgH-$radius, 2*$radius, 2*$radius, $white);
    imagefilledellipse($bg, $bgW-$radius, $bgH-$radius, 2*$radius, 2*$radius, $white);

    // Tempel logo ke background dengan resize halus
    imagecopyresampled($bg, $logo, $pad, $pad, 0, 0, $targetW, $targetH, $logoW, $logoH);

    // Hitung posisi center dan tempel ke QR
    $dstX = (int) (($qrW - $bgW) / 2);
    $dstY = (int) (($qrH - $bgH) / 2);
    imagecopy($qr, $bg, $dstX, $dstY, 0, 0, $bgW, $bgH);

    // Simpan kembali PNG (overwrite)
    imagepng($qr, $qrAbs, 6);

    // bersihkan
    imagedestroy($logo);
    imagedestroy($bg);
    imagedestroy($qr);
    return true;
}


    private function _ensure_upload_dir(){
        $dir = FCPATH.'uploads/qrcodes/meja';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        // tambahkan index.html supaya aman
        if (!file_exists($dir.'/index.html')) @file_put_contents($dir.'/index.html','');
    }
}
