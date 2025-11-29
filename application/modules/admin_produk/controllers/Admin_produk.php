<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_produk extends Admin_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('M_admin_produk','dm');
        $this->load->helper(['url','text']);
        cek_session_akses(get_class($this), $this->session->userdata('admin_session'));
    }

    private function purge_public_caches(){
        $this->load->driver('cache', ['adapter' => 'file']);

        // versi produk (kalau masih dipakai di tempat lain)
        $this->cache->save('produk_ver', time(), 365*24*3600);

        // sekalian bersihkan semua cache list_ajax produk
        $this->_clear_product_list_cache();

        $this->output->set_header('X-Cache-Purged: produk');
    }


    public function index(){
        $data["controller"] = get_class($this);
        $data["title"]      = "Master";
        $data["subtitle"]   = $this->om->engine_nama_menu(get_class($this));
        $data["content"]    = $this->load->view($data["controller"]."_view",$data,true);
        $this->render($data);
    }

        /**
     * Helper: toggle semua produk di 1 kategori (berdasarkan nama kategori).
     * - Kalau ada yang is_active=1 -> set semua jadi 0 (nonaktif)
     * - Kalau semua 0 -> set semua jadi 1 (aktif)
     * Return JSON: success, pesan, aktif (0/1)
     */
    private function _toggle_kategori_produk($namaKategori)
    {
        $namaLower = strtolower(trim($namaKategori));

        // Cari kategori_produk berdasarkan nama (case-insensitive)
        $rowKat = $this->db->select('id, nama')
            ->from('kategori_produk')
            ->where('LOWER(nama)', $namaLower)
            ->get()->row();

        if ( ! $rowKat){
            $this->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'title'   => 'Kategori tidak ditemukan',
                    'pesan'   => 'Kategori "'.$namaKategori.'" belum ada di master kategori_produk.'
                ]));
            return null;
        }

        $katId = (int)$rowKat->id;

        // cek apakah ada produk aktif di kategori ini
        $hasActive = $this->db
            ->where('kategori_id', $katId)
            ->where('is_active', 1)
            ->count_all_results('produk') > 0;

        // kalau sekarang masih ada yang aktif -> matikan semua
        // kalau tidak ada yang aktif -> hidupkan semua
        $newActive = $hasActive ? 0 : 1;

        $this->db->where('kategori_id', $katId)
                 ->update('produk', ['is_active' => $newActive]);

        // bersihkan cache publik
        $this->purge_public_caches();

        $label = ucfirst($namaLower);
        $msg   = $newActive
            ? "Semua produk $label diaktifkan."
            : "Semua produk $label dinonaktifkan.";

        $this->output->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'title'   => 'Berhasil',
                'pesan'   => $msg,
                'aktif'   => $newActive
            ]));

        return $newActive;
    }

    // Toggle khusus MINUMAN
    public function toggle_minuman()
    {
        $this->_toggle_kategori_produk('Minuman');
    }

    // Toggle khusus MAKANAN
    public function toggle_makanan()
    {
        $this->_toggle_kategori_produk('Makanan');
    }

    /**
     * Dipakai di awal load untuk mengetahui:
     * - apakah ada produk Minuman yang aktif?
     * - apakah ada produk Makanan yang aktif?
     */
    public function get_toggle_states()
    {
        $result = [
            'success' => true,
            'minuman' => null,
            'makanan' => null,
        ];

        foreach (['minuman','makanan'] as $namaLower) {
            $rowKat = $this->db->select('id')
                ->from('kategori_produk')
                ->where('LOWER(nama)', $namaLower)
                ->get()->row();

            if ($rowKat){
                $katId = (int)$rowKat->id;
                $hasActive = $this->db
                    ->where('kategori_id', $katId)
                    ->where('is_active', 1)
                    ->count_all_results('produk') > 0;

                $result[$namaLower] = $hasActive ? 1 : 0;
            }
        }

        return $this->output->set_content_type('application/json')
            ->set_output(json_encode($result));
    }


    public function get_dataa(){
        $list = $this->dm->get_data();
        $data = [];
        foreach($list as $r){
            $row = [];
            $row['cek']      = '<div class="checkbox checkbox-primary checkbox-single"><input type="checkbox" class="data-check" value="'.(int)$r->id.'"><label></label></div>';
            $row['no']       = '';
            $thumb = !empty($r->gambar) ? '<img src="'.htmlspecialchars(base_url($r->gambar),ENT_QUOTES,'UTF-8').'" class="rounded mr-2" style="width:40px;height:40px;object-fit:cover">' : '<div class="bg-light" style="width:40px;height:40px;border-radius:6px"></div>';
            $unit = isset($r->satuan) ? $r->satuan : '';
            $row['produk']   = '<div class="d-flex align-items-center gap-2">'.$thumb.'<div><div class="fw-semibold">'.htmlspecialchars($r->nama,ENT_QUOTES,'UTF-8').'</div><div class="text-muted small">'.htmlspecialchars($unit,ENT_QUOTES,'UTF-8').'</div></div></div>';
            // $row['kategori'] = htmlspecialchars(isset($r->kategori_nama)?$r->kategori_nama:'—', ENT_QUOTES, 'UTF-8');
            $row['sku']      = '<code>'.htmlspecialchars($r->sku, ENT_QUOTES, 'UTF-8').'</code>';
            $row['harga']    = 'Rp '.number_format((float)$r->harga,0,',','.');
            $row['stok']     = (int)$r->stok;
            $row['aktif']    = $r->is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>';
            $btnEdit         = '<button type="button" class="btn btn-sm btn-warning" onclick="edit('.(int)$r->id.')"><i class="fe-edit"></i> Edit</button>';
            $row['aksi']     = $btnEdit;
            $row['kategori'] = htmlspecialchars(
                (isset($r->kategori_nama)?$r->kategori_nama:'—') . 
                (isset($r->sub_nama) && $r->sub_nama ? ' › '.$r->sub_nama : ''),
                ENT_QUOTES, 'UTF-8'
            );
            $row['sub_kategori'] = htmlspecialchars(isset($r->sub_nama)?$r->sub_nama:'—', ENT_QUOTES, 'UTF-8');


            $data[] = $row;
        }
        $out = [
            "draw" => (int)$this->input->post('draw'),
            "recordsTotal" => $this->dm->count_all(),
            "recordsFiltered" => $this->dm->count_filtered(),
            "data" => $data,
        ];
        $this->output->set_content_type('application/json')->set_output(json_encode($out));
    }

    public function get_one($id){
        $id  = (int)$id;
        $row = $this->db->get_where('produk',['id'=>$id])->row();
        if (!$row){ echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]); return; }
        echo json_encode(["success"=>true,"data"=>$row]);
    }

    public function add(){
        $this->load->library('form_validation');
        $this->form_validation->set_rules('kategori_id','Kategori','required|integer');
        $this->form_validation->set_rules('nama','Nama','trim|required|min_length[2]|max_length[150]');
        $this->form_validation->set_rules('sub_kategori_id','Sub Kategori','integer');
        $this->form_validation->set_rules('recomended','Andalan','in_list[0,1]');

        $this->form_validation->set_rules('kata_kunci','Kata Kunci Pencarian','trim|required|min_length[2]|max_length[150]');
        $this->form_validation->set_rules('harga','Harga','required|numeric');
        $this->form_validation->set_rules('hpp','HPP','numeric');
        $this->form_validation->set_rules('stok','Stok','integer');
        $this->form_validation->set_rules('satuan','Satuan','max_length[20]');

        if ($this->form_validation->run() !== TRUE){
            echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>validation_errors()]); return;
        }
        $sub_kategori_id = (int)$this->input->post('sub_kategori_id'); // ⬅️ NEW
        if ($sub_kategori_id <= 0) $sub_kategori_id = null;

        $kategori_id = (int)$this->input->post('kategori_id');
        $nama        = $this->input->post('nama', true);
        $kata_kunci        = $this->input->post('kata_kunci', true);
        $harga       = (float)$this->input->post('harga');
        $hpp         = strlen($this->input->post('hpp'))? (float)$this->input->post('hpp') : null;
        $stok        = (int)$this->input->post('stok');
        $satuan      = $this->input->post('satuan', true);
        $deskripsi   = $this->input->post('deskripsi', false);
        $gambar      = $this->input->post('gambar', true);
        $is_active   = (int) !!$this->input->post('is_active');
        $recomended = (int) !!$this->input->post('recomended');
        // Upload gambar (opsional)
        // Upload gambar (opsional)
        if (!empty($_FILES['gambar_file']['name'])) {
            $upload_path = FCPATH.'uploads/produk/';
            if (!is_dir($upload_path)) { @mkdir($upload_path, 0775, true); }
            $config = [
                'upload_path'   => $upload_path,
                'allowed_types' => 'gif|jpg|jpeg|png|webp|JPG|JPEG|PNG|WEBP|GIF',
                'max_size'      => 4096, // beri ruang sebelum dikompres
                'encrypt_name'  => TRUE,
            ];
            $this->load->library('upload', $config);

            if (!$this->upload->do_upload('gambar_file')) {
                echo json_encode(["success"=>false,"title"=>'Upload Gagal',"pesan"=>$this->upload->display_errors('', '')]); return;
            }

            $up      = $this->upload->data();
            $srcFull = $up['full_path'];                        // file asli
            $srcRel  = 'uploads/produk/'.$up['file_name'];      // relatif (fallback)

            // Tentukan nama file hasil kompres
            $pi       = pathinfo($up['file_name']);
            $basename = $pi['filename'];
            $destDir  = FCPATH.'uploads/produk/';

            // JPEG -> simpan jpg, lainnya -> coba webp (lebih kecil)
            $isJpeg  = in_array(strtolower($up['file_ext']), ['.jpg','.jpeg'], true);
            $extOut  = $isJpeg ? 'jpg' : (function_exists('imagewebp') ? 'webp' : 'jpg');
            $destRel = 'uploads/produk/'.$basename.'.'.$extOut;
            $destFull= $destDir.$basename.'.'.$extOut;

            // Kompres ke ≤ 500 KB, maksimal dimensi 1600px
            $res = $this->compress_to_target($srcFull, $destFull, 500, 1600, 1600);

            if (!empty($res['ok'])) {
                // hapus file asli jika beda path
                if (realpath($srcFull) !== realpath($destFull)) @unlink($srcFull);
                $gambar = $destRel;                 // <-- SIMPAN YANG INI KE DB
            } else {
                // kompres gagal -> simpan file asli
                $gambar = $srcRel;
            }
        }


        // SKU otomatis
        $sku  = $this->dm->generate_unique_sku($kategori_id, $nama);
        // slug link_seo otomatis
        $slug = $this->dm->generate_unique_slug($nama);

        $ok = $this->db->insert('produk',[
            'kategori_id'=>$kategori_id,
            'sub_kategori_id'=>$sub_kategori_id, // ⬅️ NEW
            'nama'=>$nama,
            'kata_kunci'=>$kata_kunci,
            'link_seo'=>$slug,
            'sku'=>$sku,
            'harga'=>$harga,
            'hpp'=>$hpp,
            'stok'=>$stok,
            'satuan'=>$satuan,
            'deskripsi'=>$deskripsi,
            'gambar'=>$gambar,
            'is_active'=>$is_active,
            'recomended'      => $recomended
        ]);

        if ($ok){ $this->purge_public_caches(); }

        echo json_encode(["success"=>$ok,"title"=>$ok?"Berhasil":"Gagal","pesan"=>$ok?"Data disimpan":"Gagal simpan"]);
    }

  
// ===== UTIL: Kompres ke target ukuran (±500KB) =====
function compress_to_target($srcPath, $destPath, $targetKB = 500, $maxW = 1600, $maxH = 1600)
{
    if (!file_exists($srcPath)) return ['ok'=>false,'msg'=>'Source not found'];

    $info = getimagesize($srcPath);
    if (!$info) return ['ok'=>false,'msg'=>'Invalid image'];
    $mime = $info['mime'];

    // Loader GD berdasar MIME
    switch ($mime) {
        case 'image/jpeg': $img = imagecreatefromjpeg($srcPath); break;
        case 'image/png':  $img = imagecreatefrompng($srcPath);  break;
        case 'image/webp': 
            if (!function_exists('imagecreatefromwebp')) return ['ok'=>false,'msg'=>'WEBP not supported'];
            $img = imagecreatefromwebp($srcPath); 
            break;
        case 'image/gif':  $img = imagecreatefromgif($srcPath);  break;
        default: return ['ok'=>false,'msg'=>'Unsupported mime: '.$mime];
    }
    if (!$img) return ['ok'=>false,'msg'=>'Failed to load image'];

    // --- 1) Resize jika > maxW/maxH (aspek ratio terjaga)
    $w = imagesx($img); $h = imagesy($img);
    $scale = min(1.0, $maxW / $w, $maxH / $h);
    if ($scale < 1.0) {
        $nw = max(1, (int)floor($w * $scale));
        $nh = max(1, (int)floor($h * $scale));
        $tmp = imagecreatetruecolor($nw, $nh);

        // handle alpha utk PNG/WEBP
        imagealphablending($tmp, false);
        imagesavealpha($tmp, true);
        imagecopyresampled($tmp, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($img);
        $img = $tmp; $w = $nw; $h = $nh;
    }

    // Helper tulis ke file (jpg/webp/png). Return path & size
    $writeTmp = function($image, $ext, $quality) {
        $tmpPath = sys_get_temp_dir().'/imgcmp_'.uniqid().'.'.$ext;
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                imageinterlace($image, true); // progressive
                imagejpeg($image, $tmpPath, $quality);
                break;
            case 'webp':
                if (!function_exists('imagewebp')) return [null, 0];
                // quality 0-100
                imagewebp($image, $tmpPath, $quality);
                break;
            case 'png':
                // quality PNG di GD = 0-9 (kecil = lebih baik). Kita map 0..9
                $pngQ = max(0, min(9, (int)round((100 - $quality) / 11))); // approx
                imagesavealpha($image, true);
                imagepng($image, $tmpPath, $pngQ);
                break;
            default: return [null, 0];
        }
        clearstatcache(true, $tmpPath);
        return [$tmpPath, file_exists($tmpPath) ? filesize($tmpPath) : 0];
    };

    $targetBytes = $targetKB * 1024;

    // --- 2) Tentukan format keluaran:
    // - JPEG: tetap JPEG
    // - PNG/GIF: coba ke WEBP (lebih kecil). Jika server tak support WEBP, fallback ke JPEG.
    // - WEBP: tetap WEBP
    $outFormat = 'jpg';
    if ($mime === 'image/jpeg') $outFormat = 'jpg';
    elseif ($mime === 'image/webp') $outFormat = 'webp';
    else { // png/gif
        $outFormat = function_exists('imagewebp') ? 'webp' : 'jpg';
    }

    // --- 3) Binary search quality
    // Untuk JPG/WEBP pakai 40..90; untuk PNG, kita map quality 100..40 -> png level 0..9
    $lo = 40; $hi = 90; $best = null; $bestSize = PHP_INT_MAX; $bestTmp = null;
    while ($lo <= $hi) {
        $mid = (int)floor(($lo + $hi) / 2);
        [$tmpPath, $size] = $writeTmp($img, $outFormat, $mid);
        if (!$tmpPath) break;

        if ($size <= $targetBytes) {
            // simpan kandidat terbaik (terkecil tapi ≤ target)
            if ($size < $bestSize) { $best = $mid; $bestSize = $size;
                if ($bestTmp && is_file($bestTmp) && $bestTmp !== $tmpPath) @unlink($bestTmp);
                $bestTmp = $tmpPath;
            } else {
                @unlink($tmpPath);
            }
            // coba turunkan quality lagi
            $hi = $mid - 1;
        } else {
            // masih kebesaran -> naikkan kompresi (turunkan quality)
            @unlink($tmpPath);
            $lo = $mid + 1;
        }
    }

    // Jika masih belum ≤ target, pakai hasil terkecil dari loop terakhir
    if (!$bestTmp) {
        // coba sekali lagi quality minimal
        [$tmpPath, $size] = $writeTmp($img, $outFormat, 40);
        if ($tmpPath) { $bestTmp = $tmpPath; $bestSize = $size; }
    }

    if (!$bestTmp) return ['ok'=>false,'msg'=>'Failed to write compressed file'];

    // Pindahkan ke destPath (pastikan foldernya ada)
    $destDir = dirname($destPath);
    if (!is_dir($destDir)) @mkdir($destDir, 0775, true);
    @rename($bestTmp, $destPath);

    // Bersih-bersih
    imagedestroy($img);

    return ['ok'=>true, 'path'=>$destPath, 'bytes'=>$bestSize, 'kb'=>round($bestSize/1024,1), 'format'=>$outFormat];
}

    
    /** 
 * Bersihkan cache katalog produk (list_ajax).
 * Dipanggil setiap ada perubahan data produk (stok, harga, nama, dll).
 */
private function _clear_product_list_cache()
{

    $registryKey = 'prod_list_registry';
    $reg = $this->cache->get($registryKey);

    if (is_array($reg)) {
        foreach ($reg as $key) {
            $this->cache->delete($key); // hapus setiap cache list produk
        }
    }

    // hapus juga registry-nya supaya bersih
    $this->cache->delete($registryKey);
}


    public function update(){
        $this->load->library('form_validation');
        $this->form_validation->set_rules('id','ID','required|integer');
        $this->form_validation->set_rules('kategori_id','Kategori','required|integer');
        $this->form_validation->set_rules('nama','Nama','trim|required|min_length[2]|max_length[150]');
        $this->form_validation->set_rules('kata_kunci','Kata Kunci Pencarian','trim|required|min_length[2]|max_length[150]');
        $this->form_validation->set_rules('harga','Harga','required|numeric');
        $this->form_validation->set_rules('hpp','HPP','numeric');
        $this->form_validation->set_rules('stok','Stok','integer');
        $this->form_validation->set_rules('satuan','Satuan','max_length[20]');

        if ($this->form_validation->run() !== TRUE){
            echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>validation_errors()]); return;
        }

        $id          = (int)$this->input->post('id', true);
        $row         = $this->db->get_where('produk',['id'=>$id])->row();
        if (!$row){ echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]); return; }
        $sub_kategori_id = (int)$this->input->post('sub_kategori_id'); // ⬅️ NEW
        if ($sub_kategori_id <= 0) $sub_kategori_id = null;
        $kategori_id = (int)$this->input->post('kategori_id');
        $nama        = $this->input->post('nama', true);
        $kata_kunci        = $this->input->post('kata_kunci', true);
        $harga       = (float)$this->input->post('harga');
        $hpp         = strlen($this->input->post('hpp'))? (float)$this->input->post('hpp') : null;
        $stok        = (int)$this->input->post('stok');
        $satuan      = $this->input->post('satuan', true);
        $deskripsi   = $this->input->post('deskripsi', false);
        $gambar      = $this->input->post('gambar', true);
        $is_active   = (int) !!$this->input->post('is_active');

        // Upload baru jika ada
        // Upload baru jika ada
        if (!empty($_FILES['gambar_file']['name'])) {
            $upload_path = FCPATH.'uploads/produk/';
            if (!is_dir($upload_path)) { @mkdir($upload_path, 0775, true); }
            $config = [
                'upload_path'   => $upload_path,
                'allowed_types' => 'gif|jpg|jpeg|png|webp|JPG|JPEG|PNG|WEBP|GIF',
                'max_size'      => 4096,
                'encrypt_name'  => TRUE,
            ];
            $this->load->library('upload', $config);

            if (!$this->upload->do_upload('gambar_file')) {
                echo json_encode(["success"=>false,"title"=>'Upload Gagal',"pesan"=>$this->upload->display_errors('', '')]); return;
            }

            $up      = $this->upload->data();
            $srcFull = $up['full_path'];
            $srcRel  = 'uploads/produk/'.$up['file_name'];

            $pi       = pathinfo($up['file_name']);
            $basename = $pi['filename'];
            $destDir  = FCPATH.'uploads/produk/';

            $isJpeg  = in_array(strtolower($up['file_ext']), ['.jpg','.jpeg'], true);
            $extOut  = $isJpeg ? 'jpg' : (function_exists('imagewebp') ? 'webp' : 'jpg');
            $destRel = 'uploads/produk/'.$basename.'.'.$extOut;
            $destFull= $destDir.$basename.'.'.$extOut;

            $res = $this->compress_to_target($srcFull, $destFull, 500, 1600, 1600);

            if (!empty($res['ok'])) {
                if (realpath($srcFull) !== realpath($destFull)) @unlink($srcFull);
                // hapus lama jika ada
                if (!empty($row->gambar) && strpos($row->gambar, 'uploads/produk/') === 0) {
                    @unlink(FCPATH.$row->gambar);
                }
                $gambar = $destRel;                  // <-- simpan hasil kompres
            } else {
                if (!empty($row->gambar) && strpos($row->gambar, 'uploads/produk/') === 0) {
                    @unlink(FCPATH.$row->gambar);
                }
                $gambar = $srcRel;                   // fallback simpan file asli
            }
        }


        // SKU otomatis (update) — akan berubah mengikuti nama/kategori terkini
        $sku = $this->dm->generate_unique_sku($kategori_id, $nama, $id);

        $needSlug = (empty($row->link_seo) || trim($row->nama) !== trim($nama));
        $upd = [
            'kategori_id'=>$kategori_id,
             'sub_kategori_id'=>$sub_kategori_id, // ⬅️ NEW
            'nama'=>$nama,
            'kata_kunci'=>$kata_kunci,
            'sku'=>$sku,
            'harga'=>$harga,
            'hpp'=>$hpp,
            'stok'=>$stok,
            'satuan'=>$satuan,
            'deskripsi'=>$deskripsi,
            'gambar'=>$gambar,
            'is_active'=>$is_active
        ];
        if ($needSlug){ $upd['link_seo'] = $this->dm->generate_unique_slug($nama, $id); }

        $ok = $this->db->where('id',$id)->update('produk', $upd);

        if ($ok){ $this->purge_public_caches(); }
         

        echo json_encode(["success"=>$ok,"title"=>$ok?"Berhasil":"Gagal","pesan"=>$ok?"Data diupdate":"Gagal update"]);
    }


    public function hapus_data(){
        $ids = $this->input->post('id');
        if (!is_array($ids) || count($ids)===0){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Tidak ada data"]); return;
        }
        $ok = true;
        foreach($ids as $id){
            $id = (int)$id; if ($id<=0) continue;
            $ok = $ok && $this->db->delete('produk',['id'=>$id]);
        }

        if ($ok){ $this->purge_public_caches(); }

        echo json_encode(["success"=>$ok,"title"=>$ok?"Berhasil":"Gagal","pesan"=>$ok?"Data dihapus":"Sebagian data gagal dihapus"]);
    }

    public function get_subkategori($kategori_id = null){
    $kategori_id = (int)($kategori_id ?? $this->input->get('kategori_id'));
    if ($kategori_id <= 0){
        return $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success'=>false,'data'=>[]]));
    }
    $rows = $this->db->order_by('nama','asc')
            ->get_where('kategori_produk_sub', ['kategori_id'=>$kategori_id, 'is_active'=>1])
            ->result();
    $list = [];
    foreach($rows as $r){
        $list[] = ['id'=>(int)$r->id, 'nama'=>$r->nama, 'slug'=>$r->slug];
    }
    return $this->output->set_content_type('application/json')
        ->set_output(json_encode(['success'=>true,'data'=>$list]));
}

public function set_andalan(){
    $ids = $this->input->post('id');

    if (!is_array($ids) || count($ids) === 0){
        return $this->output->set_content_type('application/json')
            ->set_output(json_encode([
                "success" => false,
                "title"   => "Gagal",
                "pesan"   => "Pilih minimal satu data dulu."
            ]));
    }

    // Pastikan kolom 'recomended' ada
    if ( ! $this->db->field_exists('recomended', 'produk')){
        return $this->output->set_content_type('application/json')
            ->set_output(json_encode([
                "success" => false,
                "title"   => "Kolom Tidak Ditemukan",
                "pesan"   => "Kolom 'recomended' belum ada di tabel produk."
            ]));
    }

    // Sanitasi id
    $clean = [];
    foreach($ids as $id){
        $id = (int)$id;
        if ($id > 0) $clean[] = $id;
    }
    if (empty($clean)){
        return $this->output->set_content_type('application/json')
            ->set_output(json_encode([
                "success" => false,
                "title"   => "Gagal",
                "pesan"   => "ID tidak valid."
            ]));
    }

    $this->db->where_in('id', $clean);
    $ok = $this->db->update('produk', ['recomended' => 1]);

    if ($ok){ $this->purge_public_caches(); }

    return $this->output->set_content_type('application/json')
        ->set_output(json_encode([
            "success" => (bool)$ok,
            "title"   => $ok ? "Berhasil" : "Gagal",
            "pesan"   => $ok ? "Produk ditandai sebagai andalan." : "Gagal memperbarui data."
        ]));
}


}
