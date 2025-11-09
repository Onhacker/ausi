<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Produk extends MX_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('front_model','fm');   // profil situs
        $this->load->model('M_produk','pm');     // model produk publik
        $this->load->model('M_cart_meja','cm');  // cart shared per-meja
        $this->load->library('session');
        $this->output->set_header('X-Module: produk');
    }

    /* ================== Helpers Umum ================== */
    /** Respon SANGAT sensitif / real-time / user-session */
        private function _nocache_headers(){
            $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
            $this->output->set_header('Pragma: no-cache');
        }

        /** Boleh cache SEBENTAR di sisi browser user sendiri (bukan publik/CDN) */
        private function _private_cache_headers($seconds = 60){
            $this->output->set_header('Cache-Control: private, max-age='.$seconds);
            $this->output->set_header('Pragma: private');
        }

        /** Boleh cache PUBLIK karena isinya statis / umum */
        private function _public_cache_headers($seconds = 3600){
            $this->output->set_header('Cache-Control: public, max-age='.$seconds);
            $this->output->set_header('Pragma: public');
        }


    /** Token unik per-perangkat/session (hex 32 chars) */
private function _client_token(){
    $tok = $this->session->userdata('client_token');
    if (!$tok) {
        try { $tok = bin2hex(random_bytes(16)); }
        catch (\Throwable $e) { $tok = md5(uniqid('', true)); }
        $this->session->set_userdata('client_token', $tok);
    }
    return $tok;
}

public function katalog(){
    $rec = $this->fm->web_me();

    $data["rec"]       = $rec;
    $data["title"]     = "Menu & Produk - ".$rec->nama_website;
    $data["deskripsi"] = "Daftar menu ".$rec->nama_website." ".$rec->kabupaten;
    $data["prev"]      = base_url("assets/images/produk.webp");

    $data["kategoris"] = $this->pm->get_categories();

    // tidak usah session-mode
    $data["q"]         = '';
    $data["kategori"]  = '';
    $data["sort"]      = 'bestseller';

    $data["meja_info"] = null;
    $data["mode"]      = 'delivery'; // default public aja, bukan real session

    // penting: JANGAN panggil _maybe_expire_meja_session()
    // penting: JANGAN panggil _ensure_default_delivery_mode()

    // halaman ini murni katalog publik
    $this->load->view('produk_view', $data);
}

public function scan_qr(){
    $this->_nocache_headers();
	$this->output->set_header('Permissions-Policy: camera=(self)');
    $this->output->set_header("Feature-Policy: camera 'self'");

    // data dasar buat view
    $rec = $this->fm->web_me();
    $data = [
        'rec'        => $rec,
        'title'      => 'Dine - In. Scan Barcode Meja',
        'deskripsi'  => 'Scan QR atau barcode di meja menggunakan kamera di bawah untuk mulai pesan makanan & minuman dine-in.',

        'prev'       => base_url('assets/images/nongki.webp'),
        // base URL untuk redirect kalau hasil bukan URL (anggap kode meja)
        'tag_base'   => site_url('produk/tag/'),
    ];
    $meja_kode         = $this->session->userdata('guest_meja_kode');
    $meja_nama         = $this->session->userdata('guest_meja_nama');
    $data['meja_info'] = $meja_kode ? ($meja_nama ?: $meja_kode) : null;

    // kirim mode ke view agar header “Delivery/Takeaway/Dine-in” tampil tepat
    $explicitMode      = $this->session->userdata('cart__mode');
    $data['mode']      = $meja_kode ? 'dinein' : (($explicitMode==='delivery') ? 'delivery' : 'walkin');
    $this->load->view('scan_qr_view', $data);
}


    private function _hard_reset_guest_context(){
        $this->session->unset_userdata([
            'guest_meja_id','guest_meja_kode','guest_meja_nama',
            'cart_meja_id','cart__walkin','cart_meta','scan_ts',
            'cart__mode' // NOTE: reset juga mode walkin/delivery
        ]);
    }

    private function _begin_customer_flow($meja_row){
    // mulai sesi baru per scan
    $this->session->sess_regenerate(TRUE);
    $this->_hard_reset_guest_context();

    // set identitas meja
    $this->session->set_userdata([
        'guest_meja_id'   => (int)$meja_row->id,
        'guest_meja_kode' => $meja_row->kode,
        'guest_meja_nama' => $meja_row->nama,
        'scan_ts'         => time(),
    ]);

    // token klien (per perangkat/browser)
    $token = $this->_client_token();

    // ==> pastikan open cart KHUSUS session ini
    $cart = $this->cm->ensure_open_cart_by_session($meja_row->kode, $token);
    if ($cart) $this->session->set_userdata('cart_meja_id', (int)$cart->id);
}


    private function _end_customer_flow_and_go_receipt($order_id){
        // hapus semua konteks customer
        $this->_hard_reset_guest_context();
        $this->session->sess_destroy(); // paksa sesi benar2 berakhir
        redirect('produk/receipt/'.(int)$order_id);
        exit;
    }
public function rate(){
    $this->_nocache_headers();
    if (strtoupper($this->input->method(true)) !== 'POST') {
        return $this->_json(['success'=>false,'pesan'=>'Method not allowed'], 405);
    }

    $id     = (int)$this->input->post('id');
    $stars  = (int)$this->input->post('stars');
    $review = trim((string)($this->input->post('review', true) ?? '')); // xss_clean
    $nama   = trim((string)($this->input->post('nama',   true) ?? '')); // xss_clean

    if ($id <= 0 || $stars < 1 || $stars > 5) {
        return $this->_json(['success'=>false,'pesan'=>'Input tidak valid'], 400);
    }

    // batasin panjang biar aman
    if (mb_strlen($review) > 1000) $review = mb_substr($review, 0, 1000);
    if (mb_strlen($nama)   >   60) $nama   = mb_substr($nama,   0,   60);

    // opsi: kosongkan nama yang hanya berisi spasi/emoji tak terlihat
    if ($nama !== '') {
        // rapikan spasi ganda
        $nama = preg_replace('/\s{2,}/u', ' ', $nama);
    }

    // Pastikan produk aktif
    $prod = $this->db->select('id,is_active')->get_where('produk', ['id'=>$id])->row();
    if (!$prod || (int)$prod->is_active !== 1) {
        return $this->_json(['success'=>false,'pesan'=>'Produk tidak ditemukan/aktif'], 404);
    }

    $token = $this->_client_token();
    $now   = date('Y-m-d H:i:s');

    $this->db->trans_begin();

    // Cek rating sebelumnya dari perangkat yang sama
    $prev = $this->db->select('id,stars,review,nama')
        ->get_where('produk_rating', ['produk_id'=>$id, 'client_token'=>$token])
        ->row();

    if ($prev) {
        $diff = $stars - (int)$prev->stars;

        $upd = [
            'stars'      => $stars,
            'updated_at' => $now
        ];
        if ($review !== '') { $upd['review'] = $review; $upd['review_at'] = $now; }
        if ($nama   !== '') { $upd['nama']   = $nama; } // update nama hanya bila ada input

        $this->db->where('id', (int)$prev->id)->update('produk_rating', $upd);

        if ($diff !== 0) {
            $this->db->set('rating_sum', 'rating_sum + '.(int)$diff, false)
                     ->where('id', $id)->update('produk');
        }
    } else {
        $this->db->insert('produk_rating', [
            'produk_id'    => $id,
            'client_token' => $token,
            'stars'        => $stars,
            'review'       => ($review !== '') ? $review : null,
            'review_at'    => ($review !== '') ? $now : null,
            'nama'         => ($nama   !== '') ? $nama   : null,
            'created_at'   => $now,
            'updated_at'   => $now
        ]);

        $this->db->set('rating_sum',   'rating_sum + '.(int)$stars, false)
                 ->set('rating_count', 'rating_count + 1',          false)
                 ->where('id', $id)->update('produk');
    }

    // Recalc avg
    $agg = $this->db->select('rating_sum, rating_count')
                    ->get_where('produk', ['id'=>$id])->row();
    $avg = ($agg && (int)$agg->rating_count > 0)
        ? round(((int)$agg->rating_sum) / (int)$agg->rating_count, 2)
        : 0.00;

    $this->db->where('id', $id)->update('produk', ['rating_avg'=>$avg]);

    if ($this->db->trans_status() === false) {
        $this->db->trans_rollback();
        return $this->_json(['success'=>false,'pesan'=>'Gagal menyimpan rating'], 500);
    }
    $this->db->trans_commit();

    return $this->_json([
        'success' => true,
        'pesan'   => 'Terima kasih! Rating tersimpan.',
        'avg'     => (float)$avg,
        'count'   => (int)($agg->rating_count ?? 0),
        'stars'   => $stars
        // opsional: bisa juga return 'nama' bila mau dipakai di UI
    ], 200);
}



  public function index(){
    $this->_nocache_headers();
    $rec = $this->fm->web_me();

    // >>> default-kan delivery bila belum ada mode & bukan dine-in
    $this->_maybe_expire_meja_session(120);   // 2 jam, silakan ubah
    $this->_ensure_default_delivery_mode();   // pastikan cart__mode = 'delivery' kalau belum ada

    $data["rec"]       = $rec;
    $data["title"]     = "Produk Terbaik dari " . $rec->nama_website;
    $data["deskripsi"] = "Temukan berbagai produk unggulan dari " . $rec->nama_website . " dengan kualitas terbaik dan harga bersahabat.";

    $data["prev"]      = base_url("assets/images/produk.webp");

    $data["kategoris"] = $this->pm->get_categories();
    $data["q"]         = $this->input->get('q', true) ?: '';
    $data["kategori"]  = $this->input->get('kategori', true) ?: '';
    // $data['kategori_produk_sub'] = $this->pm->get_subcategories_for_placeholder();
    // Kirim sub-kategori + nama kategori induk ke view
    $data['sub_kat_for_placeholder'] = $this->db->select('s.nama AS sub_nama, c.nama AS kategori_nama')
        ->from('kategori_produk_sub s')
        ->join('kategori_produk c', 'c.id = s.kategori_id', 'left')
        ->where('s.is_active', 1) // sesuaikan kalau kolomnya beda
        ->order_by('c.nama', 'ASC')
        ->order_by('s.nama', 'ASC')
        ->get()->result();

    $data["sort"]      = $this->input->get('sort', true) ?: 'random';

    // info meja (kalau dine-in)
    $meja_kode         = $this->session->userdata('guest_meja_kode');
    $meja_nama         = $this->session->userdata('guest_meja_nama');
    $data['meja_info'] = $meja_kode ? ($meja_nama ?: $meja_kode) : null;

    // mode aktif
    $explicitMode      = $this->session->userdata('cart__mode'); // bisa 'delivery', 'walkin', dsb

    if ($meja_kode) {
        // lagi duduk di meja -> selalu dinein
        $data['mode'] = 'dinein';
    } else {
        // tidak dine-in
        if ($explicitMode) {
            // kalau session udah punya mode, pakai itu
            $data['mode'] = $explicitMode;
        } else {
            // fallback terakhir = DELIVERY (bukan walkin lagi)
            $data['mode'] = 'delivery';
        }
    }

    $this->load->view('produk_view', $data);
}


    /* ----------------- Helper Meja/Cart ----------------- */
   private function _ensure_active_cart_id(){
    $meja_kode = $this->session->userdata('guest_meja_kode');
    if (!$meja_kode) return null;

    $token = $this->_client_token();
    $cart  = $this->cm->ensure_open_cart_by_session($meja_kode, $token);
    if ($cart) {
        $this->session->set_userdata('cart_meja_id', (int)$cart->id);
        return (int)$cart->id;
    }
    return null;
}


    private function _get_active_cart_id(){
        $id = (int)$this->session->userdata('cart_meja_id');
        if ($id > 0) return $id;
        return $this->_ensure_active_cart_id();
    }

    /* ----------------- Listing & Detail ------------------ */

    public function struk(){
        $this->_nocache_headers();
        $rec = $this->fm->web_me();

        $data["rec"]       = $rec;
        $data["title"]     = "Struk";
        $data["deskripsi"] = "Daftar produk di ".$rec->nama_website." ".$rec->kabupaten.".";
        $data["prev"]      = base_url("assets/images/icon_app.png");
        

        $data["kategoris"] = $this->pm->get_categories();
        $data["q"]         = $this->input->get('q', true) ?: '';
        $data["kategori"]  = $this->input->get('kategori', true) ?: '';
        $data["sort"]      = $this->input->get('sort', true) ?: 'new';

        $meja_kode         = $this->session->userdata('guest_meja_kode');
        $meja_nama         = $this->session->userdata('guest_meja_nama');
        $data['meja_info'] = $meja_kode ? ($meja_nama ?: $meja_kode) : null;

        $this->load->view('produk_view', $data);
    }

private function _ensure_db(): 
{
    $conn = $this->db->conn_id ?? null; // mysqli object
    if (!$conn || (method_exists($conn, 'ping') && !$conn->ping())) {
        log_message('error', 'DB connection lost, reconnecting...');
        $this->db->reconnect();
    }
}
private function _db_fail_response_if_any(): 
{
    $err = $this->db->error();
    if (!empty($err['code'])) {
        log_message('error', 'DB ERROR '.$err['code'].': '.$err['message']);
        $this->output->set_content_type('application/json')
            ->set_status_header(500)
            ->set_output(json_encode([
                'success' => false,
                'error'   => 'Database error',
                'code'    => $err['code'],
            ]));
        exit; // penting: hentikan eksekusi
    }
}
   public function list_ajax(){
    $this->_nocache_headers();
    $this->_ensure_db();

    // ===== input dasar =====
    $q        = trim($this->input->get('q', true) ?: '');
    $sub      = $this->input->get('sub', true) ?: $this->input->get('sub_kategori', true) ?: '';
    $kategori = $this->input->get('kategori', true) ?: '';
    $sort_in  = strtolower((string)($this->input->get('sort', true) ?: 'random'));
    $page     = max(1, (int)($this->input->get('page') ?: 1));
    $per_page = max(1, min(50, (int)($this->input->get('per_page') ?: 12)));

    // recommended (?rec=1 atau ?recommended=1)
    $rec_get = $this->input->get('recommended', true);
    $rec_alt = $this->input->get('rec', true);
    $recommended = ((string)$rec_get === '1' || (string)$rec_alt === '1') ? 1 : 0;

    // alias sort ID -> EN
    $aliasMap = [
        'terlaris' => 'bestseller',
        'populer'  => 'bestseller',
        'hot'      => 'trending',
        'naik'     => 'trending',
    ];
    $sort_in = $aliasMap[$sort_in] ?? $sort_in;

    // normalisasi sort
    $allowedSort = ['random','new','price_low','price_high','sold_out','bestseller','trending'];
    $sort = in_array($sort_in, $allowedSort, true) ? $sort_in : 'random';

    // ====== TRENDING FILTER ======
    $trend_param    = strtolower((string)($this->input->get('trend', true) ?: ''));
    $trend_days_in  = (int)($this->input->get('trend_days', true) ?: 0);
    $trend_min_in   = (float)($this->input->get('trend_min',  true) ?: 0);

    $trend_flag = 0;      // nonaktif default
    $trend_days = 14;     // window default
    $trend_min  = 1.0;    // skor minimal default

    if (
        $trend_param === '1' || $trend_param === 'true' || $trend_param === 'yes' ||
        in_array($trend_param, ['today','week','month'], true) ||
        $trend_days_in > 0 || $trend_min_in > 0
    ){
        $trend_flag = 1;
    }
    switch ($trend_param) {
        case 'today': $trend_days = 1;  break;
        case 'week':  $trend_days = 7;  break;
        case 'month': $trend_days = 30; break;
    }
    if ($trend_days_in > 0) $trend_days = min(90, max(1, $trend_days_in));
    if ($trend_min_in  > 0) $trend_min  = max(0.01, $trend_min_in);

    // Kalau RECOMMENDED aktif → abaikan kategori & trending
    if ($recommended) {
        $kategori   = '';
        $sub        = '';
        $trend_flag = 0;
    }

    // seed untuk random
    $seed = '';
    if ($sort === 'random') {
        // $seed = (string)($this->input->get('seed', true) ?? '');
        $tmp_seed = $this->input->get('seed', true);
$seed = (string)($tmp_seed !== null ? $tmp_seed : '');
        if ($seed === '') $seed = date('Ymd'); // deterministik harian
    }

    // ===== filters utk model =====
    $filters = [
        'q'            => $q,
        'kategori'     => $kategori,
        'sub_kategori' => $sub,
        'sold_out'     => ($sort==='sold_out') ? 1 : 0,
        'rand_seed'    => $seed,
        'recomended'   => $recommended, // sengaja 1 'm', sesuai kolom lama
        'trending'     => $trend_flag ? 1 : 0,
        'trend_days'   => $trend_days,
        'trend_min'    => $trend_min,
    ];

    // ===== hitung total dulu =====
    $total = $this->pm->count_products($filters);
    $this->_db_fail_response_if_any();

    // baru hitung paging
    $total_pages = max(1, (int)ceil($total / $per_page));
    $page        = min($page, $total_pages);
    $offset      = ($page - 1) * $per_page;

    // ===== ambil data =====
    $products = $this->pm->get_products($filters, $per_page, $offset, $sort);
    $this->_db_fail_response_if_any();

    // ===== render partials =====
    $items_html = $this->load->view('partials/produk_items_partial', [
        'products' => $products
    ], true);
    $pagi_html  = $this->load->view('partials/produk_pagination_partial', [
        'page'        => $page,
        'total_pages' => $total_pages
    ], true);

    // ===== output =====
    $this->output->set_content_type('application/json')->set_output(json_encode([
        'success'         => true,
        'items_html'      => $items_html,
        'pagination_html' => $pagi_html,
        'page'            => $page,
        'per_page'        => $per_page,
        'total'           => $total,
        'total_pages'     => $total_pages,
        'sort'            => $sort,
        'seed'            => $seed,
        'recommended'     => $recommended,
        'trend'           => $trend_flag,
        'trend_days'      => $trend_days,
        'trend_min'       => $trend_min,
    ]));
}




public function subkategori($kategori_id = null){
    $this->_public_cache_headers(3600);
    $kategori_id = (int)$kategori_id;
    if ($kategori_id <= 0){
        return $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success'=>false,'data'=>[]]));
    }
    $rows = $this->db->select('id, nama, slug')
        ->from('kategori_produk_sub')
        ->where(['kategori_id'=>$kategori_id, 'is_active'=>1])
        ->order_by('nama','ASC')->get()->result();
    return $this->output->set_content_type('application/json')
        ->set_output(json_encode(['success'=>true,'data'=>$rows]));
}


public function detail($slug = null){
    $this->_private_cache_headers(60);
    if (!$slug) show_404();

    $rec  = $this->fm->web_me();
    $prod = $this->pm->get_by_slug($slug);
    if (!$prod) show_404();

    // Fallback: jika kolom agregat tidak tersedia di select model, hitung cepat
    if (!isset($prod->rating_avg) || !isset($prod->rating_count)) {
        $agg = $this->db->select('AVG(stars) AS avg_val, COUNT(*) AS cnt', false)
        ->from('produk_rating')
        ->where('produk_id', (int)$prod->id)
        ->get()->row();
        $prod->rating_avg   = $agg ? (float)$agg->avg_val : 0.0;
        $prod->rating_count = $agg ? (int)$agg->cnt      : 0;
    }

    // Ambil ulasan untuk halaman detail (terbaru dulu; batasi 50 agar ringan)
    $reviews = $this->db->select('stars,nama, review, COALESCE(review_at, created_at) AS ts', false)
    ->from('produk_rating')
    ->where('produk_id', (int)$prod->id)
    ->where("review IS NOT NULL AND TRIM(review) <> ''", null, false)
    ->order_by('COALESCE(review_at, created_at)', 'DESC', false)
    ->limit(50)
    ->get()->result();

    $data = [
        "rec"       => $rec,
        "title"     => $prod->nama,
        "deskripsi" => (($prod->deskripsi ?: $prod->nama)),
        "prev"      => base_url($prod->gambar ?: "assets/images/icon_app.png"),
        "product"   => $prod,
        "reviews"   => $reviews,
    ];

    $this->load->view('produk_detail_view', $data);
}


    public function detail_modal(){
        $this->_nocache_headers();
    	// sleep(3);
        $slug = $this->input->get('slug', true);
        if (!$slug){
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success'=>false,'html'=>'<div class="p-3 text-danger">Slug tidak diberikan</div>']));
        }
        $prod = $this->pm->get_by_slug($slug);
        if (!$prod){
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success'=>false,'html'=>'<div class="p-3 text-danger">Produk tidak ditemukan</div>']));
        }
        $html = $this->load->view('partials/produk_detail_modal_partial', ['product'=>$prod], true);
        return $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success'=>true,'html'=>$html,'title'=>$prod->nama]));
    }

    /* ----------------- Cart Actions ------------------ */

public function review_list(){
    // optional: amankan akses non-ajax
    // if (!$this->input->is_ajax_request()) { show_404(); return; }

    // $id     = (int)($this->input->post('id') ?? $this->input->get('id'));
    $id = (int)(
        $this->input->post('produk_id')
        ?? $this->input->post('id')
        ?? $this->input->get('produk_id')
        ?? $this->input->get('id')
        ?? 0
    );
    $offset = max(0, (int)($this->input->post('offset') ?? $this->input->get('offset')));
    $limit  = min(20, max(1, (int)($this->input->post('limit') ?? $this->input->get('limit'))));

    if (!$id) {
        $payload = ['success'=>false,'pesan'=>'Produk tidak valid'];
        if ($this->config->item('csrf_protection')) $payload['csrf'] = $this->_csrf();
        return $this->output->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }

    // pastikan model sudah diload: $this->load->model('Produk_model','pm'); di __construct
    $rows  = $this->pm->get_reviews($id, $limit, $offset);
    $total = $this->pm->count_reviews($id);

    $out = [];
    foreach ($rows as $r){
        $tscol = $r->ts ?? null;
        $ts = $tscol ? (is_numeric($tscol) ? (int)$tscol : strtotime($tscol)) : time();
        $out[] = [
            'stars'  => (int)$r->stars,
            'nama' => (string)($r->nama ?? ''),
            'review' => (string)($r->review ?? ''),
            'ts_fmt' => date('d M Y', $ts),
        ];
    }

    $payload = ['success'=>true,'rows'=>$out,'total'=>(int)$total];
    if ($this->config->item('csrf_protection')) $payload['csrf'] = $this->_csrf();

    return $this->output->set_content_type('application/json')
        ->set_output(json_encode($payload));
}

private function _csrf(){
    return [
        'name' => $this->security->get_csrf_token_name(),
        'hash' => $this->security->get_csrf_hash(),
    ];
}


    public function add_to_cart(){
        $this->_nocache_headers();
        $id  = (int)$this->input->post('id');
        $qty = max(1, (int)$this->input->post('qty'));

        $row = $this->db->select('id,nama,sku,harga,stok,gambar,link_seo,is_active')
                        ->get_where('produk', ['id'=>$id, 'is_active'=>1])->row();

        if (!$row) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'title'   => 'Gagal',
                    'pesan'   => 'Produknya gak ketemu nih 😅',
                ]));
        }

        // normalisasi nama produk untuk respon
        $nama       = ucwords(trim((string)$row->nama));
        $qty_label  = ($qty > 1) ? " (x{$qty})" : '';
        $msg_ok     = "Masuk keranjang {$qty_label} 🎉";
        $msg_fail   = "Belum bisa nambahin ‘{$nama}’, coba lagi ya 🙏";

        // ====== Mode dengan cart_id (punya meja / session terhubung) ======
        $cart_id = $this->_get_active_cart_id();
        if ($cart_id) {
            $ok    = $this->cm->add_item($cart_id, (int)$row->id, $qty, (int)$row->harga);
            if ($ok) { $this->_touch_meja_session(); }
            $count = (int)$this->cm->count_items($cart_id);

            // meta meja
            $meta = $this->session->userdata('cart_meta') ?: [];
            $meta['meja'] = [
                'id'   => (int)$this->session->userdata('guest_meja_id'),
                'kode' => $this->session->userdata('guest_meja_kode'),
                'nama' => $this->session->userdata('guest_meja_nama'),
            ];
            $this->session->set_userdata('cart_meta', $meta);

            return $this->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => (bool)$ok,
                    'count'   => $count,
                    'title'   => $ok ? 'Mantap!' : 'Oops!',
                    'pesan'   => $ok ? $msg_ok : $msg_fail,
                    'produk'  => $nama,
                    'qty'     => $qty,
                ]));
        }

        // ====== Mode Walk-in/Delivery (keranjang di session) ======
        $cart = $this->session->userdata('cart__walkin') ?: [];

        if (isset($cart[$id])) {
            // tambah qty pada item yang sudah ada
            $cart[$id]['qty'] += $qty;
        } else {
            $cart[$id] = [
                'id'    => (int)$row->id,
                'nama'  => $nama,
                'harga' => (int)$row->harga,
                'qty'   => $qty,
                'slug'  => $row->link_seo,
                'gambar'=> $row->gambar
            ];
        }
        $this->session->set_userdata('cart__walkin', $cart);

        // hitung total item di keranjang (akumulasi qty)
        $count = 0; foreach($cart as $c){ $count += (int)$c['qty']; }

        return $this->output->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'count'   => (int)$count,
                'title'   => 'Mantap!',
                'pesan'   => $msg_ok,
                'produk'  => $nama,
                'qty'     => $qty,
            ]));
    }

    public function cart_count(){
        $this->_nocache_headers();
        $cart_id = $this->_get_active_cart_id();
        if ($cart_id) {
            $count = $this->cm->count_items($cart_id);
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success'=>true, 'count'=>(int)$count]));
        }
        $cart = $this->session->userdata('cart__walkin') ?: [];
        $cnt=0; foreach($cart as $c){ $cnt+=(int)$c['qty']; }
        return $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success'=>true, 'count'=>(int)$cnt]));
    }

    /** Halaman Cart */
    public function cart(){
        $this->_nocache_headers();
    $rec = $this->fm->web_me();
    $this->_maybe_expire_meja_session(120);

    // >>> default-kan delivery bila belum ada mode & bukan dine-in
    $this->_ensure_default_delivery_mode();

    $cart_id   = $this->_get_active_cart_id(); // null kalau walk-in/delivery
    $items     = [];
    $total     = 0;
    $mode      = 'walkin';
    $meja_info = null;

    if ($cart_id) {
        $mode  = 'dinein';
        $items = $this->cm->get_items($cart_id);
        $total = (int)$this->cm->sum_total($cart_id);

        $meja_kode = $this->session->userdata('guest_meja_kode');
        $meja_nama = $this->session->userdata('guest_meja_nama');
        $meja_info = $meja_kode ? ($meja_nama ?: $meja_kode) : null;

    } else {
        $explicitMode = $this->session->userdata('cart__mode');
        $mode = ($explicitMode === 'delivery') ? 'delivery' : 'walkin';

        $sess = $this->session->userdata('cart__walkin') ?: [];
        foreach ($sess as $p) {
            $p = (object)$p;
            $p->produk_id = (int)($p->id ?? 0);
            $p->qty       = (int)($p->qty ?? 0);
            $p->harga     = (int)($p->harga ?? 0);
            $p->subtotal  = $p->qty * $p->harga;
            $items[] = $p;
            $total  += $p->subtotal;
        }
    }

    $data = [
        'rec'        => $rec,
        'title'      => 'Keranjang',
        'deskripsi'  => 'Daftar produk di '.$rec->nama_website.' '.$rec->kabupaten.'.',
        'prev'       => base_url('assets/images/produk.webp'),
        'mode'       => $mode,
        'items'      => $items,
        'total'      => $total,
        'meja_info'  => $meja_info,
    ];

    $this->load->view('cart_view', $data);
}


    /** API set qty (plus/minus/input) */
    public function cart_update(){
         $this->_nocache_headers();
        $produk_id = (int)$this->input->post('produk_id');
        $qty       = (int)$this->input->post('qty');

        if ($produk_id <= 0) return $this->_json_err('Produk tidak valid');

        $cart_id = $this->_get_active_cart_id();
        if ($cart_id){
            $p = $this->db->select('harga')->get_where('produk',['id'=>$produk_id])->row();
            $harga = $p ? (int)$p->harga : null;
            $ok = $this->cm->set_qty($cart_id, $produk_id, $qty, $harga);
            $count = $this->cm->count_items($cart_id);
            $total = $this->cm->sum_total($cart_id);
            return $this->_json_ok(['count'=>$count, 'total'=>$total, 'ok'=>(bool)$ok]);
        }

        // walkin/delivery
        $cart = $this->session->userdata('cart__walkin') ?: [];
        if (!isset($cart[$produk_id])) {
            return $this->_json_err('Item tidak ada di keranjang');
        }
        if ($qty <= 0) unset($cart[$produk_id]);
        else $cart[$produk_id]['qty'] = $qty;
        $this->session->set_userdata('cart__walkin', $cart);

        $count=0; $total=0;
        foreach($cart as $c){ $count += (int)$c['qty']; $total += (int)$c['qty'] * (int)$c['harga']; }
        return $this->_json_ok(['count'=>$count,'total'=>$total]);
    }

    /** API remove baris */
    public function cart_remove(){
         $this->_nocache_headers();
        $produk_id = (int)$this->input->post('produk_id');
        if ($produk_id <= 0) return $this->_json_err('Produk tidak valid');

        $cart_id = $this->_get_active_cart_id();
        if ($cart_id){
            $ok = $this->cm->remove_item($cart_id, $produk_id);
            $count = $this->cm->count_items($cart_id);
            $total = $this->cm->sum_total($cart_id);
            return $this->_json_ok(['count'=>$count,'total'=>$total,'ok'=>(bool)$ok]);
        }

        $cart = $this->session->userdata('cart__walkin') ?: [];
        if (isset($cart[$produk_id])) unset($cart[$produk_id]);
        $this->session->set_userdata('cart__walkin', $cart);

        $count=0; $total=0;
        foreach($cart as $c){ $count += (int)$c['qty']; $total += (int)$c['qty'] * (int)$c['harga']; }
        return $this->_json_ok(['count'=>$count,'total'=>$total]);
    }


    /* ----------------- Order Pages ------------------ */

    /** Halaman Order (konfirmasi) */
    public function order(){
        $this->_nocache_headers();
    $rec = $this->fm->web_me();

    // >>> default-kan delivery bila belum ada mode & bukan dine-in
    $this->_maybe_expire_meja_session(120);
    $this->_ensure_default_delivery_mode();

    $cart_id = $this->_get_active_cart_id();
    $items   = [];
    $total   = 0;
    $mode    = 'walkin';
    $meja_info = null;

    if ($cart_id){
        $mode  = 'dinein';
        $items = $this->cm->get_items($cart_id);
        $total = $this->cm->sum_total($cart_id);
        $meja_kode = $this->session->userdata('guest_meja_kode');
        $meja_nama = $this->session->userdata('guest_meja_nama');
        $meja_info = $meja_kode ? ($meja_nama ?: $meja_kode) : null;
    } else {
        $explicitMode = $this->session->userdata('cart__mode');
        $mode = ($explicitMode === 'delivery') ? 'delivery' : 'walkin';

        $sess = $this->session->userdata('cart__walkin') ?: [];
        foreach($sess as $p){
            $p = (object)$p;
            $p->produk_id = $p->id;
            $p->qty = (int)$p->qty;
            $p->harga = (int)$p->harga;
            $items[] = $p;
            $total += $p->qty * $p->harga;
        }
    }

    $data = [
        'rec'        => $rec,
        'title'      => 'Konfirmasi Pesanan',
        'deskripsi'  => 'Konfirmasi produk di '.$rec->nama_website.' '.$rec->kabupaten.'.',
        'prev'       => base_url('assets/images/icon_app.png'),
        'mode'       => $mode,
        'items'      => $items,
        'total'      => $total,
        'meja_info'  => $meja_info,
    ];

    $this->load->view('order_view', $data);
}

// di class Produk
public function leave_table(){
    $this->_nocache_headers();
    $had = (bool)$this->session->userdata('guest_meja_kode');
    $this->_hard_reset_guest_context();   // sudah bersihkan guest_meja_*, cart_meja_id, cart__mode, dll
    $this->session->sess_regenerate(TRUE);

    // alert buat user
    $this->session->set_flashdata('cart_reset_title', 'Keluar dari Meja');
    $this->session->set_flashdata('cart_reset_msg', $had
        ? 'Kamu sudah keluar dari mode Dine-in. Sekarang lanjut sebagai Delivery/Takeaway ya. 🙌'
        : 'Kamu saat ini tidak terhubung ke meja mana pun.');
    redirect('produk');
}

    private function _touch_meja_session(){
    if ($this->session->userdata('guest_meja_kode')) {
        $this->session->set_userdata('scan_ts', time());
    }
}


    /** Submit order → buat pesanan & item dari cart */
  public function submit_order(){
    $this->_nocache_headers();
    $this->_maybe_expire_meja_session(120);
    $nama    = trim($this->input->post('nama', true) ?: '');
    $catatan = trim($this->input->post('catatan', true) ?: '');
    $email = trim($this->input->post('email', true) ?: ''); // NEW
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $this->_json_err('Format email tidak valid.');
    }

    // === Validasi jam layanan (buka/tutup harian; support nyebrang hari) ===
    // Ambil konfigurasi dari tabel identitas (JANGAN diubah: tetap $rec)
    $rec = $this->db->get('identitas')->row(); // pastikan tabel identitas ada
    $tzName = (!empty($rec->waktu) ? (string)$rec->waktu : 'Asia/Makassar');
    $abbrTZ = ($tzName==='Asia/Jakarta' ? 'WIB'
            : ($tzName==='Asia/Makassar' ? 'WITA'
            : ($tzName==='Asia/Jayapura' ? 'WIT' : '')));
    try { $tz = new DateTimeZone($tzName); } catch (\Throwable $e) { $tz = new DateTimeZone('Asia/Makassar'); $tzName='Asia/Makassar'; }
    $now = new DateTime('now', $tz);

    // Helper normalisasi "08.00" / "8:00" => "08:00"
    $norm = function($s){
        $s = trim((string)$s);
        if ($s === '') return null;
        $s = str_replace('.', ':', $s);
        if (!preg_match('/^(\d{1,2}):([0-5]\d)$/', $s, $m)) return null;
        $h = max(0, min(23, (int)$m[1]));
        $i = (int)$m[2];
        return sprintf('%02d:%02d', $h, $i);
    };
    // konversi "HH:MM" -> menit
    $toMin = function($hhmm){
        if ($hhmm===null) return null;
        [$h,$i] = array_map('intval', explode(':', $hhmm));
        return $h*60 + $i;
    };
    $dot = fn($s)=> $s ? str_replace(':','.', $s) : '';

    $daysKey = ['sun','mon','tue','wed','thu','fri','sat'];
    // ambil cfg per-hari dari identitas
    $cfg = [];
    foreach ($daysKey as $k){
        $cfg[$k] = [
            'open'        => $norm($rec->{"op_{$k}_open"}        ?? null),
            'break_start' => $norm($rec->{"op_{$k}_break_start"} ?? null),
            'break_end'   => $norm($rec->{"op_{$k}_break_end"}   ?? null),
            'close'       => $norm($rec->{"op_{$k}_close"}       ?? null),
            'closed'      => (int)($rec->{"op_{$k}_closed"}      ?? 0) ? 1 : 0,
        ];
    }

    // Hitung window aktif “hari ini” dgn dukungan nyebrang hari
    $w         = (int)$now->format('w');          // 0=Sun..6=Sat
    $kToday    = $daysKey[$w];
    $kYest     = $daysKey[($w+6)%7];
    $nowMin    = (int)$now->format('H')*60 + (int)$now->format('i');

    $toMinSafe = function($s) use($toMin){ return $s ? $toMin($s) : null; };

    $oT = $toMinSafe($cfg[$kToday]['open']);
    $cT = $toMinSafe($cfg[$kToday]['close']);
    $bsT= $toMinSafe($cfg[$kToday]['break_start']);
    $beT= $toMinSafe($cfg[$kToday]['break_end']);
    $isWrapT = ($oT !== null && $cT !== null && $cT <= $oT); // contoh 10:00–03:00

    $oY = $toMinSafe($cfg[$kYest]['open']);
    $cY = $toMinSafe($cfg[$kYest]['close']);
    $bsY= $toMinSafe($cfg[$kYest]['break_start']);
    $beY= $toMinSafe($cfg[$kYest]['break_end']);
    $isWrapY = ($oY !== null && $cY !== null && $cY <= $oY);

    // Apakah segmen aktif yang berlaku saat ini adalah window kemarin→hari ini?
    $useYesterdayWindow = false;

    if ($isWrapT && $cT !== null && $nowMin < $cT) {
        // Hari-ini wrap & masih dini hari -> segmen 00:00..close hari-ini
        $useYesterdayWindow = true;
    } elseif ($isWrapY && $cY !== null && $nowMin < $cY && !$isWrapT) {
        // KEMARIN wrap, HARI INI normal & masih dini hari -> pakai window KEMARIN
        $useYesterdayWindow = true;
    }


    $active = $useYesterdayWindow ? $cfg[$kYest] : $cfg[$kToday];
    $oA = $useYesterdayWindow ? $oY : $oT;
    $cA = $useYesterdayWindow ? $cY : $cT;
    $bsA= $useYesterdayWindow ? $bsY: $bsT;
    $beA= $useYesterdayWindow ? $beY: $beT;
    $isWrapA = ($oA !== null && $cA !== null && $cA <= $oA);

    $openStr  = $active['open'];
    $closeStr = $active['close'];
    $hasBreak = ($active['break_start'] && $active['break_end'] && $toMin($active['break_start']) < $toMin($active['break_end']));
    $inWindow = false;

    // Tentukan apakah sekarang di dalam window buka
    if ($active['closed'] || $oA===null || $cA===null){
        $inWindow = false;
    } else if ($isWrapA){
        // window wrap (contoh 10:00–03:00)
        if ($useYesterdayWindow){
            // segmen [00:00..close]
            $inWindow = ($nowMin <= $cA);
        } else {
            // segmen [open..23:59]
            $inWindow = ($nowMin >= $oA);
        }
    } else {
        // window normal (tidak wrap)
        $inWindow = ($nowMin >= $oA && $nowMin <= $cA);
    }

    // Jika di dalam window, pastikan bukan jam istirahat
    if ($inWindow && $hasBreak){
        $bs = $toMin($active['break_start']);
        $be = $toMin($active['break_end']);
        if ($nowMin >= $bs && $nowMin < $be){
            $inWindow = false; // istirahat dianggap tidak melayani
            $this->_json_err('Sedang istirahat: '.$dot($active['break_start']).'–'.$dot($active['break_end']).'. Silakan pesan setelah istirahat berakhir.');
            return;
        }
    }

    // Tolak order jika di luar jam layanan
    if (!$inWindow){
        $msg = 'Pesanan hanya dapat dibuat pada jam layanan: '
            . ($openStr ? $dot($openStr) : '-')
            . '–'
            . ($closeStr ? $dot($closeStr) : '-')
            . ($abbrTZ ? ' '.$abbrTZ : '');
        return $this->_json_err($msg);
    }
    // === END validasi jam layanan ===

    // Untuk delivery:
    $customer_phone = trim($this->input->post('phone', true) ?: '');
    $alamat_kirim   = trim($this->input->post('alamat', true) ?: '');

    // Nama wajib
    if ($nama === '') {
        return $this->_json_err('Nama wajib diisi');
    }

    $cart_id = $this->_get_active_cart_id(); // null kalau walk-in/delivery
    $forced  = $this->session->userdata('cart__mode'); // walkin/delivery/null

    if     ($cart_id)             $mode = 'dinein';
    elseif ($forced==='delivery') $mode = 'delivery';
    else                          $mode = 'walkin';

    // === Ambil item dari sumber (DIPINDAH KE ATAS sebelum cek ongkir) ===
    $items = []; $total = 0;
    if ($cart_id){
        $items = $this->cm->get_items($cart_id);
        foreach($items as $it){ $total += ((int)$it->qty * (int)$it->harga); }
        if (!$items){ return $this->_json_err('Keranjang kosong'); }
    } else {
        $sess = $this->session->userdata('cart__walkin') ?: [];
        if (!$sess){ return $this->_json_err('Keranjang kosong'); }
        foreach($sess as $it){
            $it = (object)$it;
            $items[] = (object)[
                'produk_id'=>(int)$it->id,
                'qty'      =>(int)$it->qty,
                'harga'    =>(int)$it->harga,
                'nama'     =>$it->nama ?? null,
                'slug'     =>$it->slug ?? null,
                'gambar'   =>$it->gambar ?? null,
            ];
            $total += (int)$it->qty * (int)$it->harga;
        }
    }

    // ==== Variabel default delivery ====
    $dest_lat    = 0.0;
    $dest_lng    = 0.0;
    $distance_m  = 0;
    $delivery_fee= 0;

    if ($mode === 'delivery') {
        // Konfigurasi ongkir toko (PAKAI variabel BARU agar $rec tidak berubah)
        $shop = $this->fm->web_me(); // store_lat, store_lng, base_km, base_fee, per_km, max_radius_m, batas_free_ongkir

        // Ambil koordinat dari form (gratis ongkir cukup titik)
        $post_lat  = (float)$this->input->post('dest_lat', true);
        $post_lng  = (float)$this->input->post('dest_lng', true);

        // Penentuan gratis ongkir berdasar subtotal
        $batas_free = (int)($shop->batas_free_ongkir ?? 0);
        $is_free    = ($total >= $batas_free);
        $this->load->helper('ongkir');
        if ($is_free) {
            
            // === GRATIS ONGKIR: tidak wajib token; tetap validasi koordinat & radius di server
            if ($post_lat < -90 || $post_lat > 90 || $post_lng < -180 || $post_lng > 180) {
                return $this->_json_err('Koordinat tidak valid.');
            }

            // Hitung jarak/radius server-side
            $calc = hitung_ongkir_server(
                (float)$shop->store_lat, (float)$shop->store_lng,
                $post_lat, $post_lng,
                (float)$shop->base_km, (int)$shop->base_fee, (int)$shop->per_km,
                (int)$shop->max_radius_m
            );
            if (!$calc['allowed']) {
                return $this->_json_err('Di luar radius '.number_format(((int)$shop->max_radius_m)/1000,1,'.','').' km');
            }

            $dest_lat     = $post_lat;
            $dest_lng     = $post_lng;
            $distance_m   = (int)$calc['distance_m'];
            $delivery_fee = 1; // sentinel "gratis"
        } else {
            // === NON-GRATIS: wajib token & ambil dari sesi lock_ongkir
            $token = $this->input->post('ongkir_token', true);
            if (!$token) {
                return $this->_json(['success'=>false,'title'=>'Belum Tau Ongkirnya','pesan'=>'Yuk pilih dulu lokasinya biar bisa dihitung~']);
            }
            $lock = $this->session->userdata('ongkir_lock_'.$token);

            if (($lock['ts'] ?? 0) < time()-600){
                return $this->_json(['success'=>false,'title'=>'Kedaluwarsa','pesan'=>'Silakan pilih ulang lokasi.']);
            }

            // Ambil nilai dari sesi
            $dest_lat     = (float)$lock['lat'];
            $dest_lng     = (float)$lock['lng'];
            $delivery_fee = (int)$lock['fee'];
            $distance_m   = (int)$lock['distance_m'];

            // OPTIONAL HARDENING:
            // hitung ulang cepat buat pastikan masih allowed
            $calcCheck = hitung_ongkir_server(
                (float)$shop->store_lat, (float)$shop->store_lng,
                $dest_lat, $dest_lng,
                (float)$shop->base_km, (int)$shop->base_fee, (int)$shop->per_km,
                (int)$shop->max_radius_m
            );
            if (!$calcCheck['allowed']) {
                return $this->_json_err('Alamat sudah di luar radius layanan 😔');
            }
        }

    }

    // ===== Cutoff DELIVERY dari tabel identitas (tetap pakai $rec) =====
    // ===== Cutoff DELIVERY dari tabel identitas (window-aware; dukung overnight) =====
    if ($mode === 'delivery') {
        $enabled = isset($rec->delivery_cutoff_enabled) ? (int)$rec->delivery_cutoff_enabled : 1;
        $rawCut  = isset($rec->delivery_cutoff) ? (string)$rec->delivery_cutoff : '';
        $cutHHMM = $norm($rawCut); // "HH:MM" atau null

        if ($enabled && $cutHHMM && $oA !== null && $cA !== null) {
            // Anchor mulai = jam buka aktif (today/yesterday segment yang sedang berlaku)
            $start = $oA;
            $endAdj = $toMin($cutHHMM);   // cutoff menit (00:00 basis)
            $cAdj   = $cA;                // tutup menit (00:00 basis)

            // Jika window wrap (mis. 10:00–03:00), naikkan angka ke timeline yang sama
            if ($isWrapA) {
                if ($cAdj <= $start) $cAdj += 1440;       // close ke hari+1
                // Jika cutoff terlihat < open, artinya cutoff ada di hari yang sama dengan open,
                // tapi saat ini kita bisa berada pada segmen after-midnight. Jangan ubah
                // asumsi bisnis: cutoff berada di hari "open" (bukan next-day).
                // Jadi endAdj TIDAK otomatis +1440 kecuali memang Anda ingin cutoff next-day.
            }

            // Batasi end oleh jam tutup operasional
            if ($cAdj < $endAdj) $endAdj = $cAdj;

            // Samakan timeline now
            $nowAdj = $nowMin;
            if ($isWrapA && ($useYesterdayWindow ?? false) && $nowMin <= $cA) {
                // Sedang di segmen after-midnight (00:00..close) milik window kemarin → angkat now
                $nowAdj += 1440;
            }

            // Jika ingin tegas: delivery hanya diterima di rentang [open .. cutoff]
            // Dengan logika ini, kasus 00:30 vs cutoff 17:00 (open 10:00) akan tertolak.
            if (!($nowAdj >= $start && $nowAdj <= $endAdj)) {
                return $this->_json_err(
                    'Untuk pengantaran, kami menerima order pada '.
                    $dot($active['open']).'–'.$dot($cutHHMM).' '.($abbrTZ ?: '').'. Silakan coba lagi nanti.'
                );
            }
        }
    }


    // Validasi delivery minimal
    if ($mode==='delivery' && ($alamat_kirim === '' || $customer_phone === '')){
        return $this->_json_err('Untuk pengantaran, mohon isi telepon dan alamat pengantaran.');
    }

    $meja_kode = $this->session->userdata('guest_meja_kode');
    $meja_nama = $this->session->userdata('guest_meja_nama');

    // === Hitung grand total TAHAP SUBMIT (tanpa kode unik) ===
    // base_total = subtotal + ongkir (jika delivery)
    $base_total  = (int)$total + ($mode === 'delivery' ? (int)$delivery_fee : 0);
    $kode = random_int(1, 499);
    $kode_unik   = $kode;                    // <-- kode unik BELUM dipakai di sini
    $grand_total = $base_total+$kode_unik;          // <-- kunci awal; akan di-update di _set_verifikasi()

    $this->db->trans_begin();

    // Selalu BUAT ORDER BARU (tanpa append)
    $nomor = date('YmdHis').'-'.mt_rand(100,999);
    $order_data = [
        'nomor'        => $nomor,
        'mode'         => $mode,
        'meja_kode'    => $meja_kode ?: null,
        'meja_nama'    => $meja_nama ?: null,
        'cart_id'      => $cart_id ?: null,
        'nama'         => $nama,
        'email'        => ($email !== '' ? $email : null),  // NEW

        'catatan'      => ($catatan !== '' ? $catatan : null),

        // delivery fields
        'customer_phone' => (($mode === 'delivery' || $mode === 'dinein') && $customer_phone !== '')
                        ? $customer_phone : null,
        'alamat_kirim'   => ($mode==='delivery') ? $alamat_kirim   : null,
        'delivery_fee'   => ($mode==='delivery') ? (int)$delivery_fee : 0,
        'delivery_status'=> ($mode==='delivery') ? 'waiting' : null,
        'dest_lat'       => ($mode==='delivery') ? $dest_lat : 0,
        'dest_lng'       => ($mode==='delivery') ? $dest_lng : 0,
        'distance_m'     => ($mode==='delivery') ? $distance_m : 0,

        // totals (tanpa kode unik dulu)
        'total'        => (int)$total,          // subtotal barang
        'kode_unik'    => $kode_unik,                    // akan diisi saat pilih QRIS/transfer
        'grand_total'  => (int)$grand_total,    // base_total
        'status'       => 'pending',            // menunggu verifikasi / pilih metode
        'paid_method'  => null,
        'paid_at'      => null,
        'created_at'   => date('Y-m-d H:i:s'),
    ];
    $this->db->insert('pesanan', $order_data);
    $order_id = (int)$this->db->insert_id();

    // ========= Tambahan: map nama & kategori produk (untuk isi pi.nama & pi.id_kategori) =========
    $id_set = [];
    foreach($items as $it){ $id_set[(int)$it->produk_id] = true; }

    $map_nama = [];
    $map_kat  = [];
    if ($id_set){
        $ids = array_keys($id_set);
        // ambil sekalian kategori_id dari tabel produk
        $prods = $this->db->select('id, nama, kategori_id')->from('produk')->where_in('id', $ids)->get()->result();
        foreach($prods as $p){
            $pid = (int)$p->id;
            $map_nama[$pid] = $p->nama;
            $map_kat[$pid]  = isset($p->kategori_id) ? (int)$p->kategori_id : null;
        }
    }

    // Insert detail
    $actor = ($mode === 'dinein')
        ? ($nama ?: ($meja_nama ?: ($meja_kode ?: 'customer')))
        : $mode; // tandai 'walkin' atau 'delivery' sebagai added_by

    foreach($items as $it){
        $pid   = (int)$it->produk_id;
        $qty   = (int)$it->qty;
        $harga = (int)$it->harga;
        $nm    = $it->nama ?? ($map_nama[$pid] ?? null);
        $katId = $map_kat[$pid] ?? null; // kategori produk

        $rowIns = [
            'pesanan_id'  => $order_id,
            'produk_id'   => $pid,
            'nama'        => $nm,
            'id_kategori' => $katId,
            'qty'         => $qty,
            'harga'       => $harga,
            'subtotal'    => $qty * $harga,
            'added_by'    => $actor,
            'tambahan'    => 0,
            'created_at'  => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('pesanan_item', $rowIns);
    }

    // Tutup cart & bereskan session keranjang (biar order tidak nempel)
    if ($cart_id){
        $this->db->where('id', $cart_id)
         ->where('status', 'open') // guard opsional
         ->update('cart_meja', [
             'status'     => 'submitted',
             'sesi_key'   => null,                 // <— penting
             'updated_at' => date('Y-m-d H:i:s'),
         ]);

        $this->session->unset_userdata('cart_meja_id');
    } else {
        $this->session->unset_userdata('cart__walkin');
    }

    if ($this->db->trans_status() === FALSE){
        $this->db->trans_rollback();
        return $this->_json_err('Gagal membuat pesanan');
    }
    $this->db->trans_commit();
    if ($mode === 'delivery') {
    $ord = (object)[
        'id'            => $order_id,
        'nomor'         => $nomor,
        'nama'          => $nama,
        'customer_phone'=> $customer_phone,
        'total'         => $total,
        'delivery_fee'  => $delivery_fee,
        'grand_total'   => $grand_total,
        'kode_unik'   => $kode_unik,
        'catatan'   => $catatan,
        'alamat_kirim'  => $alamat_kirim,
        'dest_lat'      => $dest_lat,
        'dest_lng'      => $dest_lng,
    ];
    // $this->_wa_notify_delivery_submit($ord, $items);
   

}

    return $this->_json_ok([
        'order_id' => (int)$order_id,
        'nomor'    => $nomor ?: $order_id,
        'message'  => 'Pesanan dibuat (status pending)',
        // 'redirect' => site_url('produk/order_success/'.$order_id)
        'redirect' => site_url('produk/order_success/'.$nomor) // <— pakai nomor
    ]);
}

/**
 * Kirim WA ke customer ketika order delivery BERHASIL dibuat.
 * - Gunakan setelah DB commit di submit_order()
 * - Butuh helper: send_wa_single($hp, $msg)
 *
 * @param object $ord  Objek pesanan minimal punya: id, nomor, nama, customer_phone,
 *                     total (subtotal), delivery_fee, grand_total, alamat_kirim,
 *                     dest_lat, dest_lng
 * @param array|null $items  Array item (object {nama, qty, harga}); jika null akan diambil dari DB.
 * @return bool  true jika dieksekusi (tidak menjamin sukses kirim WA), false jika gagal awal (mis. no phone).
 */
private function _wa_notify_delivery_submit($ord, ?array $items = null): bool
{
    // Validasi minimal
    if (!$ord || empty($ord->customer_phone)) return false;

    // Helper WA
    // if (!function_exists('send_wa_single')) {
    //     // ganti 'wa' jika nama helper berbeda
    //     $this->load->helper('wa');
    // }
        $this->load->helper('front');

    // Info brand toko
    $shop  = $this->fm->web_me(); // pastikan model fm sudah diload di __construct
    $brand = $shop->nama_toko ?? ($shop->web_title ?? 'Ausi Billiard & Café');

    // Ambil items dari DB jika tidak disuplai
    if ($items === null) {
        $items = $this->db->select('nama, qty, harga')
                          ->from('pesanan_item')
                          ->where('pesanan_id', (int)$ord->id)
                          ->order_by('id', 'asc')
                          ->get()->result();
    }

    // Formatter rupiah
    $idr = function($n){ return 'Rp'.number_format((int)$n, 0, ',', '.'); };

    // Ringkas daftar item (maks 6 baris)
    $lines = [];
    $maxLines = 6;
    $i = 0;
    foreach ($items as $it) {
        $nm  = trim((string)($it->nama ?? 'Item'));
        $qty = (int)($it->qty ?? 0);
        $hrg = (int)($it->harga ?? 0);
        $sub = $qty * $hrg;
        $lines[] = '• '.$nm.' ×'.$qty.' — '.$idr($sub);
        if (++$i >= $maxLines) { $lines[] = '• …'; break; }
    }
    $itemsList = $lines ? implode("\n", $lines) : '• (kosong)';

    // Ongkir: sentinel 1 = gratis
    $ongkirText = ((int)$ord->delivery_fee === 1) ? 'Gratis ongkir' : $idr($ord->delivery_fee);

    // Link pembayaran/sukses (sesuai permintaan)
    $payUrl = site_url('produk/order_success/'.$ord->nomor);

    // (opsional) pin tujuan
    $pinLink = '';
    if (!empty($ord->dest_lat) && !empty($ord->dest_lng)) {
        $pinLink = "https://www.google.com/maps/?q={$ord->dest_lat},{$ord->dest_lng}";
    }

    // Susun pesan
    $msg =
        "Halo {$ord->nama}, pesanan *{$ord->nomor}* di *{$brand}* sudah kami terima ✅\n\n".
        "Rincian:\n{$itemsList}\n\n".
        "Subtotal: ".$idr($ord->total)."\n".
        "Ongkir: {$ongkirText}\n".
        "Kode Unik: ".$idr($ord->kode_unik)."\n".
        "Grand Total: ".$idr($ord->grand_total)."\n".
        (!empty($ord->alamat_kirim) ? "Alamat: {$ord->alamat_kirim}\n" : '').
        ($pinLink ? "Pin lokasi: {$pinLink}\n" : '').
        "Catatan: ".$ord->catatan."\n".

        "\nSilahkan lanjutkan proses *pembayaran* di tautan berikut:\n{$payUrl}\n\n".
        "Terima kasih 🙏";

    // Kirim WA; bungkus try agar aman
    try {
        @send_wa_single($ord->customer_phone, $msg);
        return true;
    } catch (\Throwable $e) {
        log_message('error', 'WA delivery submit error: '.$e->getMessage());
        return false;
    }
}
    


    private function _clear_guest_session(){
        $this->session->unset_userdata([
            'guest_meja_id','guest_meja_kode','guest_meja_nama','cart_meja_id',
            'cart__walkin','cart_meta','cart__mode'
        ]);
    }

    /** Ambil order + items + total dari ID/KODE/nomor. */
    private function _order_bundle($ref){
        $order = null; $items = []; $total = 0; $meja_info = null;

        // header
        if (ctype_digit((string)$ref)) {
            $order = $this->db->get_where('pesanan', ['id' => (int)$ref])->row();
        } else {
            $order = $this->db->get_where('pesanan', ['nomor' => $ref])->row();
            if (!$order && $this->db->field_exists('kode','pesanan')) {
                $order = $this->db->get_where('pesanan', ['kode' => $ref])->row();
            }
        }
        if (!$order) return [null, [], 0, null];

        // items
        $q = $this->db->select('
                pi.produk_id,
                COALESCE(pi.nama, p.nama) AS nama,
                pi.harga, pi.qty,
                (pi.harga * pi.qty) AS subtotal,
                pi.tambahan,
                p.gambar, p.link_seo AS slug, p.stok
            ')
            ->from('pesanan_item pi')
            ->join('produk p', 'p.id = pi.produk_id', 'left')
            ->where('pi.pesanan_id', (int)$order->id)
            ->order_by('pi.id','ASC');
        $items = $q->get()->result();

        $total = 0;
        foreach ($items as $it){
            $it->harga    = (int)round((float)$it->harga);
            $it->qty      = (int)$it->qty;
            $it->subtotal = (int)$it->harga * (int)$it->qty;
            $total += $it->subtotal;
            $it->tambahan = isset($it->tambahan) ? (int)$it->tambahan : 0;
        }

        $kode_unik   = (int)($order->kode_unik ?? 0);
        $grand_total = isset($order->grand_total) && (int)$order->grand_total > 0
                     ? (int)$order->grand_total
                     : ((int)$total + (int)$kode_unik);

        // simpan ke object agar view gampang pakai
        $order->kode_unik   = $kode_unik;
        $order->grand_total = $grand_total;

        // --- Info meja
        if (!empty($order->meja_nama))      $meja_info = $order->meja_nama;
        elseif (!empty($order->meja_kode))  $meja_info = $order->meja_kode;

        return [$order, $items, $total, $meja_info];
    }

    public function order_success($ref = null){
    $this->_nocache_headers();
    if (!$ref) show_404();

    [$order, $items, $total, $meja_info] = $this->_order_bundle($ref);
    if (!$order) show_404();

    // guard beda meja (untuk dinein)
    $sess_kode = $this->session->userdata('guest_meja_kode');
    if ($order->mode === 'dinein' && $sess_kode && $order->meja_kode && $order->meja_kode !== $sess_kode){
        return redirect('produk');
    }

    $paid_method = isset($order->paid_method) ? $order->paid_method : null;
    $method = $this->input->get('method', true) ?: $paid_method;

    $rec = $this->fm->web_me();
    $data = [
        'rec'       => $rec,
        'title'     => 'Pesanan Diterima',
        'deskripsi' => 'Konfirmasi produk di '.$rec->nama_website.' '.$rec->kabupaten.'.',
        'prev'      => base_url('assets/images/produk.webp'),
        'order'     => $order,
        'items'     => $items,
        'total'     => $total,
        'meja_info' => $meja_info,
        'method'    => $method,
    ];
    $this->load->view('order_success_view', $data);
}

// Tambah helper ini (mis. taruh di bawah _nocache_headers)
private function _ensure_default_delivery_mode(){
    // Jangan ganggu kalau sudah dine-in (punya meja/cart aktif)
    $has_meja = (bool)$this->session->userdata('guest_meja_kode')
             || (bool)$this->session->userdata('cart_meja_id');
    if ($has_meja) return;

    // Kalau belum ada pilihan mode sama sekali → set default delivery
    if (!$this->session->userdata('cart__mode')) {
        $this->session->set_userdata('cart__mode', 'delivery');
    }
}
private function _email_order_confirmation($order_id){
    // Ambil data order + item
    $order = $this->db->get_where('pesanan', ['id'=>$order_id])->row();
    if (!$order || empty($order->email)) return;

    $items = $this->db->select('nama, qty, harga, subtotal')
                      ->from('pesanan_item')
                      ->where('pesanan_id', $order_id)
                      ->order_by('id','asc')->get()->result();

    // Identitas/app name
    $web = $this->fm->web_me();
    $app = !empty($web->namaweb) ? $web->namaweb : 'Ausi Billiard & Café';

    // Payload utk template email (mode ORDER)
    $payload = [
        'mail_mode'    => 'order',          // <--- penting
        'is_update'    => false,
        'app_name'     => $app,
        'order'        => $order,
        'items'        => $items,
        'redirect_url' => site_url('produk/order_success/'.$order->nomor),
        'pdf_url'      => null,             // kalau nanti ada struk PDF, isi di sini
        'qr_url'       => null,             // opsional: link QR order kalau ada
    ];

    // Render HTML dari view
    $html = $this->load->view('front_end/mail_notif', $payload, true);

    // Kirim via CI Email
    $this->load->library('email');

    // Tentukan alamat FROM
    $fromEmail = !empty($web->email) ? $web->email : ('no-reply@'.parse_url(base_url(), PHP_URL_HOST));
    $fromName  = $app;

    $this->email->from($fromEmail, $fromName);
    $this->email->to($order->email);
    $this->email->subject('Konfirmasi Pesanan #'.$order->nomor.' - '.$app);
    $this->email->message($html);

    // Jangan bikin fatal kalau gagal
    try { $this->email->send(); } catch (\Throwable $e) { log_message('error', 'Email order gagal: '.$e->getMessage()); }
}

   public function set_mode_walkin(){
    // Hapus jejak meja agar pasti walkin
    $this->_hard_reset_guest_context();
    $this->session->set_userdata('cart__mode', 'walkin');

    // ⬇️ alert seperti dine-in
    $this->session->set_flashdata('cart_reset_title', 'Mode: Bawa Pulang');
    $this->session->set_flashdata('cart_reset_msg', 'Kamu berada di mode bawa pulang. Pesanan akan dibungkus. 🚶‍♂️🛍️');

    redirect('produk');
}

public function set_mode_delivery(){
    $this->_hard_reset_guest_context();
    $this->session->set_userdata('cart__mode', 'delivery');

    // ⬇️ alert seperti dine-in
    $this->session->set_flashdata('cart_reset_title', 'Mode: Kirim');
    $this->session->set_flashdata('cart_reset_msg', 'Kamu berada di mode Antar. Nanti alamat & ongkir diminta saat checkout. 🚚📦');

    redirect('produk');
}
// di class Produk
private function _maybe_expire_meja_session($minutes = 120){
    $kode = $this->session->userdata('guest_meja_kode');
    $ts   = (int)$this->session->userdata('scan_ts');
    if (!$kode || !$ts) return;

    if (time() - $ts > ($minutes * 60)) {
        // lepaskan konteks meja
        $this->_hard_reset_guest_context();
        // beri alert ke user
        $this->session->set_flashdata('cart_reset_title', 'Sesi Berakhir');
        $this->session->set_flashdata('cart_reset_msg', 'Sesi meja kamu otomatis berakhir karena tidak ada aktivitas. 👍');
    }
}


    /** Cetak struk thermal */
    public function order_print($id = null){
        $this->_nocache_headers();
        if (!$id) show_404();
        [$order, $items, $total, $meja_info] = $this->_order_bundle($id);
        if (!$order) show_404();
        $data = compact('order','items','total','meja_info');
        $this->load->view('order_receipt_view', $data);
    }

    /** Halaman struk full page (untuk cetak) */
    public function receipt($ref = null){
	    $this->_nocache_headers();
	    if (!$ref) show_404();

	    [$order, $items, $total, $meja_info] = $this->_order_bundle($ref);
	    if (!$order) show_404();

	    $rec = $this->fm->web_me();
	    $data = compact('order','items','total','meja_info','rec');
	    $data["title"]     = "Struk";
	    $data["deskripsi"] = "Struk Pembayaran";
	    $data["prev"]      = base_url("assets/images/icon_app.png");

	    $this->load->view('order_receipt_page', $data);

	}


    /* ----------------- Tag Meja (QR) ------------------ */
    // public function tag($kode = null){
    //     $kode = trim((string)$kode);
    //     if ($kode === '') return redirect('produk');

    //     $row = $this->db->get_where('meja', ['kode'=>$kode, 'status'=>'aktif'])->row();

    //     if ($row) {
    //         $this->_begin_customer_flow($row);
    //         if ($this->session->userdata('cart')) $this->session->unset_userdata('cart');
    //         $this->session->set_flashdata('cart_reset_title', $row->nama);
    //         $this->session->set_flashdata('cart_reset_msg', 'Kamu akan order di '.$row->nama.'. Tolong pastikan kamu duduk di '.$row->nama.', jangan pindah-pindah ya. 👍');

    //     } else {
    //         $this->_hard_reset_guest_context();
    //         $this->session->sess_regenerate(TRUE);
    //     }
    //     return redirect('produk');
    // }

    public function tag($kode = null, $token = null){
        $kode  = trim((string)$kode);
        $token = trim((string)$token);

        if ($kode === '' || $token === '') {
            return redirect('produk');
        }

        // validasi pola kode meja (opsional, biar spam ke DB berkurang)
        if (!preg_match('/^[A-Z0-9]{3,10}$/', $kode)) {
            $this->_hard_reset_guest_context();
            $this->session->sess_regenerate(TRUE);
            return redirect('produk');
        }


        $row = $this->db->get_where('meja', [
            'kode'     => $kode,
            'qr_token' => $token,
            'status'   => 'aktif'
        ])->row();

        if ($row) {
            $this->_begin_customer_flow($row);

            if ($this->session->userdata('cart')) {
                $this->session->unset_userdata('cart');
            }

            $this->session->set_flashdata('cart_reset_title', $row->nama);
            $this->session->set_flashdata(
                'cart_reset_msg',
                'Kamu akan order di '.$row->nama.'. Tolong pastikan kamu duduk di '.$row->nama.', jangan pindah-pindah ya. 👍'
            );

            // penting: regenerasi session id saat "claim" meja
            $this->session->sess_regenerate(TRUE);

        } else {
            $this->_hard_reset_guest_context();
            $this->session->sess_regenerate(TRUE);
        }

        return redirect('produk');
    }


    /* ----------------- Pembayaran (tanpa API) ------------------ */
    private function _mark_paid_and_end($id, $method){
        $id = (int)$id;
        $row = $this->db->get_where('pesanan',['id'=>$id])->row();
        if (!$row) show_404();
        if (!in_array($row->status, ['paid','canceled'], true)){
            $this->db->where('id',$id)->update('pesanan', [
                'status'      => 'paid',
                'paid_method' => $method,      // cash | qris | transfer
                'paid_at'     => date('Y-m-d H:i:s'),
            ]);
        }
        $this->_end_customer_flow_and_go_receipt($id);
    }

    private function _set_verifikasi($id, $method){
    $id  = (int)$id;
    $row = $this->db->get_where('pesanan', ['id'=>$id])->row();
    if (!$row) show_404();

    $subtotal    = (int)($row->total ?? 0);
    $deliveryFee = (int)($row->delivery_fee ?? 0);
    $isDelivery  = (strtolower($row->mode ?? '') === 'delivery');

    // base total = subtotal + ongkir (kalau delivery)
    $baseTotal = $subtotal + ($isDelivery ? $deliveryFee : 0);

    $needUnique = in_array($method, ['qris','transfer'], true);

    $kode = 0;
    if ($needUnique){
        // jaga kode unik 1..499 (re-use kalau sudah ada yang valid)
        $kode = (int)($row->kode_unik ?? 0);
        if ($kode < 1 || $kode > 499) {
            $kode = random_int(1, 499);
        }
    }

    $upd = [
        'status'      => 'verifikasi',
        'paid_method' => $method,                 // cash | qris | transfer
        'kode_unik'   => $kode,                   // 0 utk cash
        'grand_total' => $baseTotal + $kode,      // tambah unik kalau perlu
        'updated_at'  => date('Y-m-d H:i:s'),
    ];

    $this->db->where('id', $id)->update('pesanan', $upd);
}



    /* ====== TUNAI: langsung lunas (boleh tetap) ====== */
    public function pay_cash($ref = null){
	    $this->_nocache_headers();
	    if (!$ref) show_404();

	    $row = $this->_get_order_by_ref($ref);
	    if (!$row) show_404();

	    if (!in_array(strtolower($row->status ?? ''), ['paid','canceled'], true)){
	        // set ke verifikasi (tanpa kode unik) → grand_total = base total
	        $this->_set_verifikasi((int)$row->id, 'cash');
	    }
	    return redirect('produk/order_success/'.rawurlencode($row->nomor));
	}


    // --- Helper CRC-16/CCITT-FALSE untuk Tag 63
    private function _crc16_ccitt_false(string $s): int {
        $poly = 0x1021; $crc = 0xFFFF;
        $len = strlen($s);
        for ($i=0; $i<$len; $i++) {
            $crc ^= (ord($s[$i]) << 8);
            for ($b=0; $b<8; $b++) {
                $crc = ($crc & 0x8000) ? (($crc << 1) ^ $poly) : ($crc << 1);
                $crc &= 0xFFFF;
            }
        }
        return $crc;
    }

    // Set/replace Tag 54 (amount) lalu hitung ulang CRC (Tag 63)
    private function _qris_set_amount(string $payload, $amount): string {
	    // === 1) Parse TLV top-level sampai Tag 63 ===
	    $tags = []; $i = 0; $n = strlen($payload);
	    while ($i + 4 <= $n) {
	        $tag = substr($payload, $i, 2);
	        $len = intval(substr($payload, $i + 2, 2), 10);
	        $i += 4;
	        if ($len < 0 || $i + $len > $n) break;
	        $val = substr($payload, $i, $len);
	        $i += $len;
	        $tags[] = [$tag, $len, $val];
	        if ($tag === '63') break; // CRC – berhenti
	    }

	    // === 2) Filter: buang Tag 54 (Amount) & Tag 63 (CRC lama) ===
	    $filtered = [];
	    foreach ($tags as [$t, $l, $v]) {
	        if ($t === '54' || $t === '63') continue;
	        $filtered[] = [$t, $l, $v];
	    }

	    // === 3) Normalisasi nominal (tanpa pemisah ribuan) ===
	    $amt = (float)$amount;
	    if (!is_finite($amt) || $amt < 0) { $amt = 0.0; }

	    // QRIS/EMV pakai titik desimal; trimming nol di belakang
	    if (fmod($amt, 1.0) == 0.0) {
	        $amtStr = (string)intval($amt);
	    } else {
	        $amtStr = number_format($amt, 2, '.', '');
	        $amtStr = rtrim(rtrim($amtStr, '0'), '.');
	        if ($amtStr === '') $amtStr = '0';
	    }
	    $len54 = strlen($amtStr);
	    if ($len54 > 99) {
	        // sangat tidak mungkin, tapi jaga-jaga
	        $amtStr = substr($amtStr, 0, 99);
	        $len54  = strlen($amtStr);
	    }
	    $len54_2d = str_pad((string)$len54, 2, '0', STR_PAD_LEFT);

	    // === 4) Rakit ulang: pertahankan urutan tag, sisipkan 54 di posisi yang benar (sebelum tag > 54) ===
	    $body = '';
	    $inserted54 = false;
	    foreach ($filtered as [$t, $l, $v]) {
	        // Saat menemukan tag yang lebih besar dari 54 dan 54 belum disisipkan → selipkan 54 dulu
	        if (!$inserted54 && ctype_digit($t) && intval($t, 10) > 54) {
	            $body .= '54' . $len54_2d . $amtStr;
	            $inserted54 = true;
	        }
	        $body .= $t . str_pad((string)$l, 2, '0', STR_PAD_LEFT) . $v;
	    }
	    // Jika semua tag <= 54, 54 belum tersisip → taruh 54 sebelum CRC
	    if (!$inserted54) {
	        $body .= '54' . $len54_2d . $amtStr;
	    }

	    // === 5) Hitung ulang CRC (Tag 63) ===
	    $toCrc = $body . '6304';
	    $crc = strtoupper(dechex($this->_crc16_ccitt_false($toCrc)));
	    $crc = str_pad($crc, 4, '0', STR_PAD_LEFT);

	    return $toCrc . $crc;
	}


    public function pay_qris($ref = null){
	    $this->_nocache_headers();
	    if (!$ref) show_404();

	    $row = $this->_get_order_by_ref($ref);
	    if (!$row) show_404();
	    $orderId = (int)$row->id;
    $orderNo = (string)$row->nomor;
	    if (in_array(strtolower($row->status ?? ''), ['paid','canceled'], true)){
	        return redirect('produk/order_success/'.rawurlencode($row->nomor));
	    }

	    // Tandai metode verifikasi + kode unik + grand_total
	    $this->_set_verifikasi((int)$row->id, 'qris');

        // Re-fetch order + bundle item/total
        $order = $this->db->get_where('pesanan', ['id'=>(int)$row->id])->row();
	    [$order2, $items, $total, $meja_info] = $this->_order_bundle($row->id);

	    $rec         = $this->fm->web_me();
	    $kode_unik   = (int)($order->kode_unik ?? 0);
	    $grand_total = (int)($order->grand_total ?? ($total + $kode_unik));


        // 1) QRIS BASE (punya kamu). PASTIKAN tanpa spasi/linebreak.
        $BASE_QRIS = '00020101021126590013ID.CO.BNI.WWW011893600009150432388702096072939380303UMI51440014ID.CO.QRIS.WWW0215ID10254388495450303UMI5204793253033605802ID5922AUSI BILLIARD DAN CAFE6004WAJO61059099262070703A0163048CA7';

        // 2) Sisipkan Tag 54 (amount) + CRC baru
        $payload = $this->_qris_set_amount($BASE_QRIS, $grand_total);

        // 3) Generate PNG QR (uploads/qris/order_{id}.png)
        $dir = FCPATH.'uploads/qris';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $png = $dir."/order_{$orderId}.png";

        $qr_ok = false;
        if (file_exists(APPPATH.'libraries/Ciqrcode.php') || file_exists(APPPATH.'libraries/ciqrcode.php')) {
            $this->load->library('ciqrcode');
            $params = [
                'data'     => $payload,
                'level'    => 'H',
                'size'     => 8,
                'savename' => $png
            ];
            $qr_ok = $this->ciqrcode->generate($params);
        }
        if (!$qr_ok && !file_exists($png)) {
            // fallback kosong agar tidak error jika library tidak ada
            $im = imagecreatetruecolor(360,360); imagepng($im,$png); imagedestroy($im);
        }

        // 4) Overlay logo di tengah (jika ada)
        $logoPath = FCPATH.'assets/images/logo_admin.png';
        if (file_exists($logoPath) && file_exists($png)) {
            $this->_overlay_logo_on_png($png, $logoPath, 0.22); // 22% lebar QR
        }

        // URL gambar QR yang dipakai di view
         $qris_img = base_url('uploads/qris/order_'.$orderId.'.png');

    $data = [
        'title'       => 'Pembayaran via QRIS',
        'deskripsi'   => 'Silakan scan QRIS dan bayar sesuai total, lalu tunggu verifikasi kasir.',
        'prev'        => base_url('assets/images/icon_app.png'),
        'rec'         => $rec,
        'order'       => $order2 ?: $order,
        'items'       => $items,
        'total'       => (int)$total,
        'kode_unik'   => $kode_unik,
        'grand_total' => $grand_total,
        'meja_info'   => $meja_info,
        'qris_img'    => $qris_img,
        'bank_list'   => [],
        'qris_payload'=> $payload, // dari prosesmu
    ];
    $this->load->view('pay_qris_view', $data);
    }

    /**
     * Tumpuk logo transparan di tengah file PNG QR.
     *
     * @param string $qrPath   Path PNG QR
     * @param string $logoPath Path logo (PNG/JPG)
     * @param float  $scale    0.18–0.26 direkomendasikan (proporsi lebar logo terhadap QR)
     */
    private function _overlay_logo_on_png($qrPath, $logoPath, $scale = 0.22){
        // Buka QR
        $qr = @imagecreatefrompng($qrPath);
        if (!$qr) return;

        // Coba buka logo sebagai PNG, fallback ke JPEG
        $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
        if ($ext === 'png') {
            $logoSrc = @imagecreatefrompng($logoPath);
        } else {
            $logoSrc = @imagecreatefromjpeg($logoPath);
        }
        if (!$logoSrc){
            imagedestroy($qr);
            return;
        }

        // Dimensi
        $qrW = imagesx($qr);
        $qrH = imagesy($qr);
        $lgW = imagesx($logoSrc);
        $lgH = imagesy($logoSrc);

        // Hitung ukuran logo baru (jaga aspek rasio)
        $targetW = max(30, (int)round($qrW * $scale));
        $ratio   = $lgH ? ($lgW / $lgH) : 1;
        $targetH = (int)round($targetW / $ratio);

        // Buat kanvas logo ber-alpha
        $logo = imagecreatetruecolor($targetW, $targetH);
        imagealphablending($logo, false);
        imagesavealpha($logo, true);
        $trans = imagecolorallocatealpha($logo, 0, 0, 0, 127);
        imagefilledrectangle($logo, 0, 0, $targetW, $targetH, $trans);

        // Resize logo sumber ke kanvas
        imagecopyresampled($logo, $logoSrc, 0, 0, 0, 0, $targetW, $targetH, $lgW, $lgH);

        // Posisi tengah
        $dstX = (int)round(($qrW - $targetW) / 2);
        $dstY = (int)round(($qrH - $targetH) / 2);

        // Pastikan alpha QR terjaga
        imagealphablending($qr, true);
        imagesavealpha($qr, true);

        // Copy logo ke QR (logo sudah ber-alpha)
        imagecopy($qr, $logo, $dstX, $dstY, 0, 0, $targetW, $targetH);

        // Simpan kembali PNG QR
        imagepng($qr, $qrPath);

        // Bersih-bersih
        imagedestroy($logoSrc);
        imagedestroy($logo);
        imagedestroy($qr);
    }

    public function qris_png($ref = null){
        if (!$ref) show_404();

    $this->_nocache_headers(); // tambah ini

    $row = $this->_get_order_by_ref($ref);
    if (!$row) show_404();
    $orderId = (int)$row->id;
    $status = strtolower($row->status ?? '');
    if (in_array($status, ['paid','canceled'], true)) show_404();

    $path = FCPATH.'uploads/qris/order_'.$orderId.'.png';
    if (!is_file($path)) show_404();

    header('Content-Type: image/png');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Content-Length: '.filesize($path));
    readfile($path);
    exit;
}



    /* ====== TRANSFER: verifikasi + kode unik + halaman instruksi ====== */
    public function pay_transfer($ref = null){
    $this->_nocache_headers();
    if (!$ref) show_404();

    $row = $this->_get_order_by_ref($ref);
    if (!$row) show_404();
    if (in_array(strtolower($row->status ?? ''), ['paid','canceled'], true)){
        return redirect('produk/order_success/'.rawurlencode($row->nomor));
    }

    $this->_set_verifikasi((int)$row->id, 'transfer');

    [$order2, $items, $total, $meja_info] = $this->_order_bundle($row->id);
    $rec   = $this->fm->web_me();
    $order = $this->db->get_where('pesanan', ['id'=>(int)$row->id])->row();

    $data = [
        'title'       => 'Verifikasi Pembayaran (Transfer)',
        'deskripsi'   => 'Silakan transfer sesuai nominal total bayar berikut, kemudian tunggu verifikasi kasir.',
        'prev'        => base_url('assets/images/icon_app.png'),
        'rec'         => $rec,
        'order'       => $order2 ?: $order,
        'items'       => $items,
        'total'       => (int)($order->total ?? $total ?? 0),
        'kode_unik'   => (int)($order->kode_unik ?? 0),
        'grand_total' => (int)($order->grand_total ?? (($order->total ?? 0) + ($order->kode_unik ?? 0))),
        'meja_info'   => $meja_info,
        'bank_list'   => [
            ['bank'=>'BNI','atas_nama'=>'Afrisal','no_rek'=>'1980870276'],
        ],
    ];
    $this->load->view('pay_transfer_view', $data);
}


    private function _get_order_by_ref($ref){
	    $ref = (string)$ref;
	    if ($ref === '') return null;
	    if (ctype_digit($ref)) {
	        return $this->db->get_where('pesanan', ['id'=>(int)$ref])->row();
	    }
	    return $this->db->get_where('pesanan', ['nomor'=>$ref])->row();
	}


    /** Upload bukti (transfer) → selesai flow + ke struk */
    public function upload_bukti($id = null){
        $this->_nocache_headers();
        $id = (int)$id;
        if ($id <= 0) show_404();

        $order = $this->db->get_where('pesanan', ['id'=>$id])->row();
        if (!$order) show_404();

        $config = [
            'upload_path'   => FCPATH.'uploads/bukti/',
            'allowed_types' => 'jpg|jpeg|png|webp|pdf',
            'max_size'      => 4096,
            'encrypt_name'  => true,
        ];
        if (!is_dir($config['upload_path'])) @mkdir($config['upload_path'], 0775, true);
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('bukti')){
            $this->session->set_flashdata('upload_err', $this->upload->display_errors('', ''));
            return redirect('produk/pay_transfer/'.$id);
        }

        $up  = $this->upload->data();
        $rel = 'uploads/bukti/'.$up['file_name'];

        $this->db->where('id', $id)->update('pesanan', [
            'bukti_bayar' => $rel,
            'paid_method' => 'transfer',
            'status'      => 'paid',            // ⬅️ tandai LUNAS setelah upload
            'paid_at'     => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        $this->session->set_flashdata('upload_ok', 'Bukti transfer berhasil diunggah.');
        // selesai flow → ke halaman struk & hapus session
        $this->_end_customer_flow_and_go_receipt($id);
    }

    /* Opsional: kasir tandai lunas */
    public function mark_transfer_paid($id = null, $secret = null){
        $this->_nocache_headers();
        if ($secret !== 'YOUR_SECRET_TOKEN') show_404();

        $id = (int)$id;
        if (!$id) show_404();

        $row = $this->db->get_where('pesanan',['id'=>$id])->row();
        if (!$row) show_404();

        if (!in_array($row->status, ['paid'], true)) {
            $this->db->where('id',$id)->update('pesanan', [
                'status'      => 'paid',
                'paid_method' => 'transfer',
                'paid_at'     => date('Y-m-d H:i:s')
            ]);
        }
        $this->session->set_flashdata('msg_ok','Pesanan ditandai lunas (transfer).');
        return redirect('produk/order_success/'.$id);
    }

    /* ----------------- JSON & Modal struk ------------------ */
    private function _json_ok($extra=[]){
        $extra['success'] = true;
        return $this->output->set_content_type('application/json')->set_output(json_encode($extra));
    }
    private function _json_err($msg='Error', $extra=[]){
        $extra['success'] = false; $extra['title'] = 'Gagal'; $extra['pesan'] = $msg;
        return $this->output->set_content_type('application/json')->set_output(json_encode($extra));
    }

    public function order_receipt_modal($id = null){
        $this->_nocache_headers();
        if (!$id) show_404();

        [$order, $items, $total, $meja_info] = $this->_order_bundle($id);
        if (!$order) show_404();

        $rec  = $this->fm->web_me();
        $html = $this->load->view(
            'partials/order_receipt_partial',
            compact('order','items','total','meja_info','rec'),
            true
        );

        $title = 'Struk #'.($order->nomor ?? $order->kode ?? $order->id);
        return $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success'=>true,'html'=>$html,'title'=>$title]));
    }

    // === Cetak PDF Thermal via TCPDF ===
    // URL: produk/receipt_pdf/{id}?w=58|80  (default 58)
    public function receipt_pdf($id = null){
         $this->_nocache_headers();
        if (!$id) show_404();

        // Ambil order & items
        [$order, $items, $total, $meja_info] = $this->_order_bundle($id);
        if (!$order) show_404();

        // Lebar kertas: 58mm atau 80mm
        $w = $this->input->get('w', true);
        $paper = ($w == '80') ? 80 : 58; // mm

        // Estimasi tinggi halaman (mm) biar 1 halaman:
        // header + info ~ 36mm, setiap item ~ 6mm, footer ~ 18mm
        $item_count = count($items);
        $height_mm  = 36 + ($item_count * 6) + 18;
        if ($height_mm < 80) $height_mm = 80; // minimal 80mm agar tidak terlalu pendek

        // Siapkan HTML via view (biar rapi)
        $data = [
            'order'     => $order,
            'items'     => $items,
            'total'     => (int)$total,
            'paper'     => $paper,  // mm
            'meja_info' => $meja_info,
        ];
        $html = $this->load->view('partials/receipt_tcpdf', $data, true);

        // ====== Inisialisasi TCPDF ======
        if (!class_exists('TCPDF')) {
            $this->load->library('tcpdf'); // pastikan ada application/libraries/Tcpdf.php wrapper CI
        }

        // 'P' potrait, 'mm' unit, ukuran custom (lebar x tinggi)
        $pdf = new TCPDF('P', 'mm', [$paper, $height_mm], true, 'UTF-8', false);

        $pdf->SetCreator('POS');
        $pdf->SetAuthor('POS');
        $pdf->SetTitle('Struk #'.($order->nomor ?? $order->id));
        $pdf->SetSubject('Receipt');
        $pdf->SetKeywords('receipt, thermal');

        // Tanpa header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Margin tipis untuk thermal
        $pdf->SetMargins(2, 2, 2, true);
        $pdf->SetAutoPageBreak(false, 2);

        // Font monospace
        $pdf->SetFont('dejavusansmono', '', 9);
        $pdf->AddPage();

        // Tulis HTML
        $pdf->writeHTML($html, true, false, true, false, '');

        // Keluarkan PDF ke browser
        $filename = 'struk-'.($order->nomor ?? $order->id).'.pdf';
        $pdf->Output($filename, 'I'); // I = inline, D = download
    }

    // Cek status order (JSON)
public function order_status($ref = null){
    $this->_nocache_headers();
    if (!$ref) {
        return $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success'=>false,'error'=>'ref kosong']));
    }

    $row = $this->_get_order_by_ref($ref);
    if (!$row) {
        return $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success'=>false,'error'=>'order tidak ditemukan']));
    }

    // Normalisasi status & pembayaran
    $status      = strtolower((string)$row->status);
    $paid_method = $row->paid_method ?? null;
    $paid_at     = $row->paid_at ?? null;

    // >>> Tambahan untuk deteksi ongkir via polling <<<
    $mode         = strtolower((string)($row->mode ?? ''));
    $is_delivery  = ($mode === 'delivery');
    $delivery_fee = (int)($row->delivery_fee ?? 0);

    return $this->output->set_content_type('application/json')
        ->set_output(json_encode([
            'success'       => true,
            'status'        => $status,                          // pending | verifikasi | paid | canceled | ...
            'paid_method'   => $paid_method,                     // cash | qris | transfer | null
            'paid_at'       => $paid_at,
            'updated_at'    => $row->updated_at ?? null,
            'grand_total'   => (int)($row->grand_total ?? 0),
            'order_id'      => (int)$row->id,
            'nomor'         => (string)$row->nomor,

            // >>> Kunci buat frontend <<<
            'is_delivery'   => $is_delivery,                     // bool
            'delivery_fee'  => $delivery_fee,                    // int
        ]));
}
// application/modules/produk/controllers/Produk.php
public function load_map()
{
    $this->_private_cache_headers(60);
    // Ambil param dari query (fallback ke default bila kosong)
    // $store_lat = (float)$this->input->get_post('store_lat', true);
    // $store_lng = (float)$this->input->get_post('store_lng', true);
    // $base_km   = (float)$this->input->get_post('base_km', true);
    // $base_fee  = (int)$this->input->get_post('base_fee', true);
    // $per_km    = (int)$this->input->get_post('per_km', true);
    $rec = $this->fm->web_me();
    $store_lat = $rec->store_lat;
    $store_lng = $rec->store_lng;
    $base_km=$rec->base_km;
    $base_fee=$rec->base_fee;
    $per_km=$rec->per_km;

    // if (!$store_lat) $store_lat = -3.7156933057722576;
    // if (!$store_lng) $store_lng = 120.40755839999998;
    // if ($base_km   <= 0) $base_km  = $rec->base_km;
    // if ($base_fee  <= 0) $base_fee = $rec->base_fee;
    // if ($per_km    <= 0) $per_km   = $rec->per_km;

    // Kembalikan FRAGMEN HTML (bukan modal baru)
    $html = '
<div id="ongkirMapWrap"
     data-store-lat="'.html_escape($store_lat).'"
     data-store-lng="'.html_escape($store_lng).'"
     data-base-km="'.html_escape($base_km).'"
     data-base-fee="'.html_escape($base_fee).'"
     data-per-km="'.html_escape($per_km).'">

  <div id="mapInModal" style="height:380px;"></div>

  <div id="mapInfo" class="border-top small"></div>

 <style>.map-footer-bar{display:flex;justify-content:space-between;align-items:center;padding:.75rem .75rem;border-top:1px solid rgb(0 0 0 / .08);background:rgb(255 255 255 / .6);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);gap:.5rem}.map-footer-btn{flex:1 1 calc(50% - .5rem);min-width:calc(50% - .5rem);display:flex;align-items:center;justify-content:center;border:0;border-radius:.7rem;font-size:.9rem;font-weight:600;padding:.6rem .75rem;line-height:1.2;white-space:nowrap;box-shadow:0 .5rem 1rem rgb(0 0 0 / .15);text-shadow:0 1px 2px rgb(0 0 0 / .35)}.btn-loc{background-image:linear-gradient(135deg,#6b7280 0%,#4b5563 50%,#1f2937 100%);color:#fff}.btn-loc .icon{margin-right:.5rem;font-size:1rem;line-height:0}.btn-apply{background-image:linear-gradient(135deg,#2563eb 0%,#4f46e5 40%,#312e81 100%);color:#fff}.btn-apply .icon{margin-right:.5rem;font-size:1rem;line-height:0}.btn-apply[disabled]{opacity:.45;cursor:not-allowed;box-shadow:0 .5rem 1rem rgb(0 0 0 / .08);text-shadow:none}@media(max-width:360px){.map-footer-btn{flex:1 1 100%;min-width:100%}.map-footer-bar{flex-wrap:wrap}}.map-footer-btn.btn-loc[aria-busy="true"] {
  opacity: .7;
  pointer-events: none;
  cursor: wait;
}

.map-footer-btn.btn-loc .spin {
  margin-right: .4rem;
  vertical-align: -0.125em;
}

.map-footer-btn.btn-loc .icon {
  margin-right: .4rem;
  font-size: 1rem;
  line-height: 1;
}
</style>

<div class="map-footer-bar">

 <button
  type="button"
  class="map-footer-btn btn-loc"
  id="btnUseMyLoc"
  aria-busy="false"
>
  <span class="spinner-border spinner-border-sm spin d-none" role="status" aria-hidden="true"></span>
  <span class="icon" aria-hidden="true">📍</span>
  <span class="txt">Posisi Saya</span>
</button>
<button
    type="button"
    class="map-footer-btn btn-apply"
    id="btnUseOngkir"
    disabled
  >
    <span class="icon" aria-hidden="true">✅</span>
    <span>Set Posisi</span>
  </button>

  
</div>


  <div class="px-2 pb-2 text-dark small">
  <i class="mdi mdi-information-outline" aria-hidden="true"></i>
  Pengantaran ulang karena alamat tidak jelas atau penerima tidak tersedia dapat dikenakan biaya tambahan sesuai 
  <a href=' .site_url('hal#delivery').' class="text-decoration-underline">Syarat &amp; Ketentuan</a> yang berlaku.
</div>

</div>';

    $this->output
        ->set_content_type('text/html; charset=UTF-8')
        ->set_output($html);
}

 public function load_mapx(){
    // Ambil koordinat toko dari DB / konfigurasi
    // Contoh hardcode; ganti ke identitas_model Anda:
    // $iden = $this->identitas_model->get();
    // $store_lat = (float)$iden->lat;
    // $store_lng = (float)$iden->lng;

    $store_lat = -3.7156933057722576;
    $store_lng = 120.40755839999998;

    // Skema tarif (bisa juga tarik dari DB)
    $data = [
      'store_lat' => $store_lat,
      'store_lng' => $store_lng,
      'base_km'   => 2,
      'base_fee'  => 5000,
      'per_km'    => 1000,
    ];

    // Return hanya fragmen HTML (tanpa layout)
    $this->load->view('ongkir', $data);
  }

  public function reverse_geocode()
{
    $this->_nocache_headers();
    // ===== CORS preflight (OPTIONS) =====
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        $this->output
            ->set_header('Access-Control-Allow-Origin: *')
            ->set_header('Access-Control-Allow-Methods: GET, OPTIONS')
            ->set_header('Access-Control-Allow-Headers: Content-Type')
            ->set_status_header(204)
            ->_display();
        exit;
    }

    // ===== Ambil input =====
    $lat = (float)$this->input->get('lat', true);
    $lon = (float)$this->input->get('lon', true); // pakai "lon" (bukan "lng") untuk Nominatim

    if (!$lat && !$lon) {
        return $this->output
            ->set_header('Access-Control-Allow-Origin: *')
            ->set_content_type('application/json')
            ->set_status_header(400)
            ->set_output(json_encode(['ok'=>false,'error'=>'lat/lon required']));
    }

    // ===== Call Nominatim dari server =====
    $url = 'https://nominatim.openstreetmap.org/reverse'
         . '?format=jsonv2'
         . '&lat=' . rawurlencode($lat)
         . '&lon=' . rawurlencode($lon)
         . '&addressdetails=1'
         . '&accept-language=id';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_HTTPHEADER     => [
            // Penting: identitas sesuai kebijakan Nominatim
            'User-Agent: MVIN-Ongkir/1.0 (+mailto:support@contoh-domainmu.id)'
        ],
    ]);
    $resp   = curl_exec($ch);
    $errno  = curl_errno($ch);
    $errstr = curl_error($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($errno || $status < 200 || $status >= 300 || !$resp) {
        return $this->output
            ->set_header('Access-Control-Allow-Origin: *')
            ->set_content_type('application/json')
            ->set_status_header(502)
            ->set_output(json_encode([
                'ok'    => false,
                'error' => 'Nominatim error',
                'detail'=> $errstr,
                'code'  => $status
            ]));
    }

    // (Opsional) bisa tambahkan caching di sini bila perlu

    // Pass-through JSON + set CORS
    $this->output
        ->set_header('Access-Control-Allow-Origin: *')
        ->set_header('Access-Control-Allow-Methods: GET, OPTIONS')
        // ->set_header('Cache-Control: public, max-age=3600') // cache 1 jam di client
        ->set_content_type('application/json; charset=UTF-8')
        ->set_output($resp);
}
public function lock_ongkir()
{
    $this->_nocache_headers();
    $this->output->set_content_type('application/json', 'utf-8');
    $this->load->helper('ongkir');

    $lat = (float)$this->input->post('lat', true);
    $lng = (float)$this->input->post('lng', true);
    if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
        return $this->_json(['ok'=>false,'msg'=>'Koordinat tidak valid'], 400);
    }

    $rec = $this->fm->web_me();

    // Cast + fallback radius
    $store_lat    = (float)$rec->store_lat;
    $store_lng    = (float)$rec->store_lng;
    $base_km      = (float)$rec->base_km;
    $base_fee     = (int)$rec->base_fee;
    $per_km       = (int)$rec->per_km;
    $max_radius_m = (int)$rec->max_radius_m;
    if ($max_radius_m <= 0) $max_radius_m = 3000;

    // WAJIB via jalan: prefer_road=true, road_only=true
    $calc = hitung_ongkir_server(
      $store_lat,$store_lng,$lat,$lng,
      $base_km,$base_fee,$per_km,$max_radius_m,
      true,   // prefer_road
      true    // road_only
    );

    // Gagal mendapatkan rute jalan (jangan fallback ke haversine)
    if (empty($calc['ok'])) {
      return $this->_json([
        'ok'  => false,
        'msg' => 'Rute jalan tidak ditemukan. Geser pin sedikit atau pilih titik di tepi jalan lalu coba lagi.',
      ], 502);
    }

    if (!$calc['allowed']) {
      return $this->_json([
        'ok'=>false,
        'msg'=>'Di luar radius '.number_format($max_radius_m/1000,1,'.','').' km',
        'distance_m' => (int)$calc['distance_m'],
      ], 400);
    }

    // Token + simpan session
    $bytes = function_exists('random_bytes') ? random_bytes(16) : openssl_random_pseudo_bytes(16);
    $token = bin2hex($bytes);

    $this->session->set_userdata('ongkir_lock_'.$token, [
      'lat'        => $lat,
      'lng'        => $lng,
      'distance_m' => (int)$calc['distance_m'],
      'fee'        => (int)$calc['fee_rounded'],
      'ts'         => time(),
    ]);

    $csrf = null;
    if ($this->config->item('csrf_protection')) {
      $csrf = [
        'name' => $this->security->get_csrf_token_name(),
        'hash' => $this->security->get_csrf_hash(),
      ];
    }

    return $this->_json([
      'ok'         => true,
      'lat'        => $lat,
      'lng'        => $lng,
      'token'      => $token,
      'fee'        => (int)$calc['fee_rounded'],
      'distance_m' => (int)$calc['distance_m'],
      'csrf'       => $csrf,
      // opsional: info untuk debug/telemetri
      'provider'   => $calc['provider'] ?? null,
      'kind'       => $calc['distance_kind'] ?? null,
    ]);
}


/** Helper output JSON (letakkan di controller/base controller Anda) */
protected function _json($data, $code = 200)
{
    return $this->output
        ->set_status_header($code)
        ->set_output(json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
}
public function route_multi()
{
    $this->_nocache_headers();
  $this->output->set_content_type('application/json; charset=UTF-8');

  $lat1 = (float)$this->input->get('lat1', true);
  $lng1 = (float)$this->input->get('lng1', true);
  $lat2 = (float)$this->input->get('lat2', true);
  $lng2 = (float)$this->input->get('lng2', true);

  if (!$lat1 && !$lng1 && !$lat2 && !$lng2){
    $this->output->set_status_header(400);
    echo json_encode(['ok'=>false,'msg'=>'Bad request']);
    return;
  }

  $urls = [
    'osrm'  => "https://router.project-osrm.org/route/v1/driving/$lng1,$lat1;$lng2,$lat2?overview=false&alternatives=false&steps=false",
    'osmde' => "https://routing.openstreetmap.de/routed-car/route/v1/driving/$lng1,$lat1;$lng2,$lat2?overview=false&alternatives=false&steps=false",
  ];

  foreach ($urls as $name => $url) {
    $res = $this->_curl_json($url);
    if ($res && isset($res['code']) && $res['code']==='Ok' && !empty($res['routes'][0]['distance'])) {
      $distance = (int)$res['routes'][0]['distance'];
      echo json_encode(['ok'=>true, 'provider'=>$name, 'distance'=>$distance]);
      return;
    }
  }

  $this->output->set_status_header(502);
  echo json_encode(['ok'=>false,'msg'=>'OSRM upstream error / no route']);
}

private function _curl_json($url)
{
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 6,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_HTTPHEADER     => [
      'Accept: application/json',
      // Disarankan oleh OSM: identitas aplikasi + kontak
      'User-Agent: MVIN-DeliveryCalc/1.0 (+yourdomain.tld; contact: youremail@domain.tld)',
    ],
  ]);
  $raw  = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($raw === false || $code >= 400) return null;
  $json = json_decode($raw, true);
  return is_array($json) ? $json : null;
}


}
