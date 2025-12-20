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

    public function statistik_produk(){
    $data["controller"] = get_class($this);
    $data["title"]      = "Statistik Produk";
    $data["subtitle"]   = $this->om->engine_nama_menu(get_class($this));

    // =======================
    // QUERY DATA (kolom terlaris)
    // =======================
    $topLimit  = 100;
    $gridLimit = 100;

    // total produk aktif
    // $total_aktif = (int)$this->db->where('is_active', 1)->count_all_results('produk');

    // total terjual (sum terlaris) untuk produk aktif
    $rowTotal = $this->db->select('COALESCE(SUM(terlaris),0) AS total', false)
        ->from('produk')
        // ->where('is_active', 1)
        ->get()->row();
    $total_terlaris = (int)($rowTotal->total ?? 0);

    // count produk yang pernah terjual (terlaris > 0)
    $rowCount = $this->db->select('COUNT(*) AS cnt', false)
        ->from('produk')
        // ->where('is_active', 1)
        ->where('terlaris >', 0)
        ->get()->row();
    $count_terjual = (int)($rowCount->cnt ?? 0);

    // Top list (untuk chart & grid)
    $rows = $this->db->select('id, nama, terlaris, stok, harga', false)
        ->from('produk')
        // ->where('is_active', 1)
        ->where('terlaris >', 0)
        ->order_by('terlaris', 'DESC')
        ->limit($gridLimit)
        ->get()->result_array();

    $top10 = array_slice($rows, 0, $topLimit);

    // KPI Top 1
    $top1_name = $top10[0]['nama'] ?? '—';
    $top1_val  = (int)($top10[0]['terlaris'] ?? 0);
    $top1_pct  = ($total_terlaris > 0) ? round(($top1_val / $total_terlaris) * 100, 1) : 0;

    // rata-rata terjual per produk (yang terjual)
    $avg_terjual = ($count_terjual > 0) ? round($total_terlaris / $count_terjual, 2) : 0;

    // Data Chart (kategori & nilai)
    $chart_categories = array_map(function($r){
        return mb_strimwidth((string)$r['nama'], 0, 22, '…', 'UTF-8'); // biar label ga kepanjangan
    }, $top10);
    $chart_values = array_map(function($r){
        return (int)$r['terlaris'];
    }, $top10);

    // Data Grid (firstRowAsNames = true)
    $gridData = [];
    $gridData[] = ['ID','Produk','Terlaris','Stok','Harga'];
    foreach ($rows as $r){
        $gridData[] = [
            (int)$r['id'],
            (string)$r['nama'],
            (int)$r['terlaris'],
            (int)$r['stok'],
            (float)$r['harga'],
        ];
    }

    // lempar ke view
    $data['kpi_total_terlaris'] = $total_terlaris;
    $data['kpi_total_aktif']    = $total_aktif;
    $data['kpi_count_terjual']  = $count_terjual;
    $data['kpi_avg_terjual']    = $avg_terjual;
    $data['kpi_top1_name']      = $top1_name;
    $data['kpi_top1_val']       = $top1_val;
    $data['kpi_top1_pct']       = $top1_pct;

    $data['chart_categories']   = $chart_categories;
    $data['chart_values']       = $chart_values;
    $data['gridData']           = $gridData;

    $data["content"] = $this->load->view("Statistik_view",$data,true);
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

        $thumb = !empty($r->gambar)
            ? '<img src="'.htmlspecialchars(base_url($r->gambar),ENT_QUOTES,'UTF-8').'" class="rounded mr-2" style="width:40px;height:40px;object-fit:cover">'
            : '<div class="bg-light" style="width:40px;height:40px;border-radius:6px"></div>';

        $unit = isset($r->satuan) ? $r->satuan : '';

        $row['produk']   = '<div class="d-flex align-items-center gap-2">'.$thumb.
                           '<div><div class="fw-semibold">'.htmlspecialchars($r->nama,ENT_QUOTES,'UTF-8').'</div>'.
                           '<div class="text-muted small">'.htmlspecialchars($unit,ENT_QUOTES,'UTF-8').'</div></div></div>';

        // SKU DIHILANGKAN DARI OUTPUT
        // $row['sku']      = '<code>'.htmlspecialchars($r->sku, ENT_QUOTES, 'UTF-8').'</code>';

        $row['harga']    = 'Rp '.number_format((float)$r->harga,0,',','.');
        $row['stok']     = (int)$r->stok;
        $row['aktif']    = $r->is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>';

       // tombol edit
            $btnEdit = '<button type="button" class="btn btn-sm btn-warning" onclick="edit('.(int)$r->id.')">
                          <i class="fe-edit"></i> Edit
                        </button>';

            // tombol stok (bergantian)
            if ((int)$r->stok > 0){
                // stok ada → tampil “Kosongkan Stok”
                $btnStok = '<button type="button" class="btn btn-sm btn-danger ml-1" onclick="kosongkanStok('.(int)$r->id.')">
                              <i class="fe-slash"></i> Kosongkan Stok
                            </button>';
            } else {
                // stok 0 → tampil “Readykan Stok”
                $btnStok = '<button type="button" class="btn btn-sm btn-success ml-1" onclick="readykanStok('.(int)$r->id.')">
                              <i class="fe-check-circle"></i> Readykan Stok
                            </button>';
            }

            // dibungkus supaya berdampingan
            $row['aksi'] = '<div class="d-inline-flex align-items-center">'.$btnEdit.$btnStok.'</div>';


        $row['kategori'] = htmlspecialchars(
            (isset($r->kategori_nama)?$r->kategori_nama:'—') .
            (isset($r->sub_nama) && $r->sub_nama ? ' › '.$r->sub_nama : ''),
            ENT_QUOTES, 'UTF-8'
        );

        $row['sub_kategori'] = htmlspecialchars(isset($r->sub_nama)?$r->sub_nama:'—', ENT_QUOTES, 'UTF-8');

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
                $this->form_validation->set_rules('tipe','Tipe Produk','in_list[single,paket]');

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
        $tipe = $this->input->post('tipe', true);
        if ($tipe !== 'paket') { $tipe = 'single'; }
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
            'recomended'      => $recomended,
            'tipe'            => $tipe,   // ⬅️ INI BARU
        ]);

        if ($ok){
            $paketId = (int)$this->db->insert_id();
            $this->_save_paket_items($paketId);   // simpan isi paket kalau tipe=paket
            $this->purge_public_caches();
        }

        echo json_encode([
            "success"=>$ok,
            "title"  =>$ok?"Berhasil":"Gagal",
            "pesan"  =>$ok?"Data disimpan":"Gagal simpan"
        ]);

    }

public function kosongkan_stok()
{
    if ( ! $this->input->is_ajax_request()) {
        show_404();
    }

    $id = (int) $this->input->post('id');
    if ( ! $id) {
        return $this->_json_error('ID tidak valid');
    }

    $dataUpdate = [
        'stok'       => 0,
        'updated_at' => date('Y-m-d H:i:s'),
    ];

    $ok = $this->db->where('id', $id)->update('produk', $dataUpdate);

    if ( ! $ok) {
        return $this->_json_error('Gagal mengosongkan stok');
    }

    $this->_json_ok('Stok produk dikosongkan');
}

public function readykan_stok()
{
    if ( ! $this->input->is_ajax_request()) {
        show_404();
    }

    $id = (int) $this->input->post('id');
    if ( ! $id) {
        return $this->_json_error('ID tidak valid');
    }

    $dataUpdate = [
        'stok'       => 100,
        'updated_at' => date('Y-m-d H:i:s'),
    ];

    $ok = $this->db->where('id', $id)->update('produk', $dataUpdate);

    if ( ! $ok) {
        return $this->_json_error('Gagal mengubah stok menjadi 100');
    }

    $this->_json_ok('Stok produk diubah menjadi 100');
}

private function _json_ok($pesan = 'OK', $extra = [])
{
    $out = array_merge([
        'success' => true,
        'title'   => 'Berhasil',
        'pesan'   => $pesan,
    ], $extra);

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($out));
}

private function _json_error($pesan = 'Terjadi kesalahan', $extra = [])
{
    $out = array_merge([
        'success' => false,
        'title'   => 'Gagal',
        'pesan'   => $pesan,
    ], $extra);

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($out));
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
                $this->form_validation->set_rules('tipe','Tipe Produk','in_list[single,paket]');

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
                $tipe = $this->input->post('tipe', true);
        if ($tipe !== 'paket') { $tipe = 'single'; }

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
            'is_active'=>$is_active,
            'tipe'            => $tipe,
        ];
        if ($needSlug){ $upd['link_seo'] = $this->dm->generate_unique_slug($nama, $id); }

               $ok = $this->db->where('id',$id)->update('produk', $upd);

            if ($ok){
                $this->_save_paket_items($id);   // simpan ulang isi paket
                $this->purge_public_caches();
            }

            echo json_encode([
                "success"=>$ok,
                "title"  =>$ok?"Berhasil":"Gagal",
                "pesan"  =>$ok?"Data diupdate":"Gagal update"
            ]);

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

    /**
     * Simpan ulang isi paket ke tabel produk_paket_item
     * - Selalu hapus isi lama terlebih dahulu
     * - Kalau tipe != 'paket', langsung selesai (tidak insert apapun)
     */
    private function _save_paket_items($paketId)
    {
        $paketId = (int)$paketId;
        if ($paketId <= 0) return;

        $tipe = $this->input->post('tipe', true);
        if ($tipe !== 'paket') {
            // bukan paket -> hapus isi lama (jika ada) dan selesai
            $this->db->where('paket_id', $paketId)->delete('produk_paket_item');
            return;
        }

        // hapus isi lama dulu
        $this->db->where('paket_id', $paketId)->delete('produk_paket_item');

        $produkIds = $this->input->post('paket_produk_id');
        $qtys      = $this->input->post('paket_qty');

        if (!is_array($produkIds) || !is_array($qtys)) return;

        $batch = [];
        foreach ($produkIds as $i => $pid) {
            $pid = (int)$pid;
            $qty = isset($qtys[$i]) ? (int)$qtys[$i] : 0;
            if ($pid <= 0 || $qty <= 0) continue;

            $batch[] = [
                'paket_id'  => $paketId,
                'produk_id' => $pid,
                'qty'       => $qty,
            ];
        }

        if (!empty($batch)) {
            $this->db->insert_batch('produk_paket_item', $batch);
        }
    }

    /**
     * Ambil isi paket (dipakai saat EDIT di modal)
     * GET admin_produk/get_paket_items/{id}
     */
    public function get_paket_items($id)
    {
        $id = (int)$id;
        if ($id <= 0) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'pesan'   => 'ID paket tidak valid'
                ]));
        }

        $rows = $this->db->select('ppi.produk_id, ppi.qty, p.nama, p.harga, p.satuan')
            ->from('produk_paket_item ppi')
            ->join('produk p', 'p.id = ppi.produk_id', 'left')
            ->where('ppi.paket_id', $id)
            ->get()->result();

        return $this->output->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'data'    => $rows
            ]));
    }




}
