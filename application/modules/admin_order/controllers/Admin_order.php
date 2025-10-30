<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_order extends Admin_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('admin_order/M_admin_order','ao');
        $this->load->helper(['url','text']);
        $this->load->library('session');

        cek_session_akses(get_class($this), $this->session->userdata('admin_session'));
        date_default_timezone_set('Asia/Makassar');
    }

    public function index(){
        $data["controller"] = get_class($this);
        $data["title"]      = "Kasir - Order";
        $data["subtitle"]   = property_exists($this, 'om') && method_exists($this->om,'engine_nama_menu')
                              ? $this->om->engine_nama_menu(get_class($this)) : 'Order Management';
        $data["content"]    = $this->load->view('admin_order/index_view', $data, TRUE);
        $this->render($data);
    }

    /* ----------- LIST MEJA (full) ----------- */
    public function tables_json(){
        $tables = $this->ao->tables_with_current_name(true);
        return $this->_json_ok(['tables'=>$tables]);
    }

    /* ----------- ORDER AKTIF PER KODE ----------- */
    public function active_order_json(){
        $kode = trim($this->input->get('kode') ?? '');
        if ($kode === '') return $this->_json_err('Kode meja tidak valid');
        $o = $this->ao->current_pending_order_by_kode($kode);
        if (!$o){
            return $this->_json_ok(['order'=>null,'items'=>[],'kode'=>$kode]);
        }
        return $this->order_detail_json($o->id);
    }

    /* ----------- BUAT ORDER BARU ----------- */
    public function create_order(){
        $kode = trim($this->input->post('kode') ?? '');
        if ($kode === '') return $this->_json_err('Kode meja tidak valid');

        $nama = trim($this->input->post('nama') ?? '');
        $order_id = $this->ao->create_order_for_kode($kode, $nama);
        if (!$order_id) return $this->_json_err('Gagal membuat order baru');

        return $this->order_detail_json($order_id);
    }

    /* ----------- DETAIL ORDER ----------- */
    public function order_detail_json($order_id = null){
        $id = (int)($order_id ?: $this->input->get('order_id'));
        if ($id <= 0) return $this->_json_err('Order tidak valid');

        $order = $this->ao->order($id);
        if (!$order) return $this->_json_err('Order tidak ditemukan');

        $items = $this->ao->order_items($id);
        $total = 0; foreach($items as $it) $total += (int)$it->harga * (int)$it->qty;

        return $this->_json_ok([
            'order' => [
                'id'        => (int)$order->id,
                'nomor'     => $order->nomor ?? ($order->kode ?? ('ORD-'.$order->id)),
                'nama'      => $order->nama ?? '',
                'catatan'   => $order->catatan ?? '',
                'status'    => $order->status,
                'mode'      => $order->mode,
                'meja_kode' => $order->meja_kode,
                'meja_nama' => $order->meja_nama,
                'total'     => (int)$total,
                'total_fmt' => 'Rp '.number_format((int)$total,0,',','.'),
                'waktu'     => $order->created_at,
                'waktu_fmt' => $order->waktu_fmt ?? date('d/m/Y H:i', strtotime($order->created_at)),
            ],
            'items' => array_map(function($x){
                $sub = (int)$x->harga * (int)$x->qty;
                return [
                    'item_id'  => (int)$x->id,
                    'produk_id'=> (int)$x->produk_id,
                    'nama'     => $x->nama,
                    'harga'    => (int)$x->harga,
                    'qty'      => (int)$x->qty,
                    'subtotal' => $sub,
                    'harga_fmt'=> 'Rp '.number_format((int)$x->harga,0,',','.'),
                    'sub_fmt'  => 'Rp '.number_format($sub,0,',','.'),
                    'added_by' => $x->added_by ?? (isset($x->flags_added_by) ? $x->flags_added_by : null),
                ];
            }, $items),
        ]);
    }

    /* ----------- KATEGORI & PRODUK ----------- */
    public function categories_json(){
        $cats = $this->ao->get_categories();
        return $this->_json_ok(['categories'=>$cats]);
    }
    public function products_json(){
        $q   = trim($this->input->get('q') ?? '');
        $cid = $this->input->get('category_id');
        $cid = ($cid === '' || $cid === null) ? null : (int)$cid;

        $rows = $this->ao->search_products($q, $cid, 30);
        foreach($rows as &$r){
            $r->harga     = (int)$r->harga;
            $r->harga_fmt = 'Rp '.number_format($r->harga,0,',','.');
        }
        return $this->_json_ok(['products'=>$rows]);
    }

    /* ----------- ADD/REMOVE ITEM ----------- */
    public function add_item(){
        $order_id  = (int)$this->input->post('order_id');
        $produk_id = (int)$this->input->post('produk_id');
        $qty       = (int)($this->input->post('qty') ?? 1);
        if ($order_id<=0 || $produk_id<=0 || $qty<=0){
            return $this->_json_err('Data tidak valid');
        }
        $isAdmin = $this->session->userdata('admin_username') ? true : false;

        [$ok, $msg] = $this->ao->add_item_to_order($order_id, $produk_id, $qty, $isAdmin);
        if (!$ok) return $this->_json_err($msg ?: 'Gagal menambah item');

        return $this->order_detail_json($order_id);
    }

    public function remove_item(){
        $order_id = (int)$this->input->post('order_id');
        $item_id  = (int)$this->input->post('order_item_id');
        if ($order_id<=0 || $item_id<=0) return $this->_json_err('Data tidak valid');

        $isAdmin = $this->session->userdata('admin_username') ? true : false;
        [$ok,$msg] = $this->ao->remove_item_from_order($order_id, $item_id, $isAdmin);
        if (!$ok) return $this->_json_err($msg ?: 'Tidak bisa menghapus item');

        return $this->order_detail_json($order_id);
    }

    /* ----------- PAYMENT ----------- */
    public function pay_cash(){
        try {
            $order_id = (int)$this->input->post('order_id');
            if ($order_id<=0) return $this->_json_err('Order tidak valid');

            $res = $this->ao->mark_paid($order_id, 'cash');
            if (is_array($res)){
                if (!empty($res['db_error'])) return $this->_json_err('Gagal menyimpan pembayaran', ['detail'=>$res['db_error']]);
                if (empty($res['ok'])) return $this->_json_err($res['msg'] ?? 'Gagal menyimpan pembayaran');
                return $this->_json_ok(['message'=>$res['msg'] ?? 'Pembayaran tunai berhasil disimpan','order_paid'=>true]);
            }
            if (!$res){
                $err = $this->db->error();
                $msg = !empty($err['message']) ? ('DB: '.$err['message']) : 'Gagal menyimpan pembayaran';
                return $this->_json_err($msg);
            }
            return $this->_json_ok(['message'=>'Pembayaran tunai berhasil disimpan','order_paid'=>true]);
        } catch (Throwable $e){
            return $this->_json_err('Exception: '.$e->getMessage());
        }
    }

    public function qris_create(){
        $order_id = (int)$this->input->post('order_id');
        if ($order_id<=0) return $this->_json_err('Order tidak valid');

        $order = $this->ao->order($order_id);
        if (!$order) return $this->_json_err('Order tidak ditemukan');

        $amount = $this->ao->sum_order($order_id);
        $payload = sprintf('QRPAY|ORDER:%d|AMT:%d|TS:%s', $order_id, $amount, date('YmdHis'));

        $dir = FCPATH.'uploads/qris';
        if (!is_dir($dir)) @mkdir($dir,0775,true);
        $png = $dir."/order_{$order_id}.png";

        $qr_ok = false;
        if (file_exists(APPPATH.'libraries/Ciqrcode.php') || file_exists(APPPATH.'libraries/ciqrcode.php')) {
            $this->load->library('ciqrcode');
            $params = ['data'=>$payload,'level'=>'M','size'=>6,'savename'=>$png];
            $qr_ok = $this->ciqrcode->generate($params);
        }
        if (!$qr_ok && !file_exists($png)) { $im=imagecreatetruecolor(240,240); imagepng($im,$png); imagedestroy($im); }

        $url_png = base_url('uploads/qris/order_'.$order_id.'.png');
        return $this->_json_ok([
            'order_id'=>$order_id, 'amount'=>(int)$amount,
            'amount_fmt'=>'Rp '.number_format($amount,0,',','.'),
            'payload'=>$payload, 'qr_url'=>$url_png
        ]);
    }

    public function qris_confirm(){
        try {
            $order_id = (int)$this->input->post('order_id');
            if ($order_id<=0) return $this->_json_err('Order tidak valid');

            $res = $this->ao->mark_paid($order_id, 'qris');
            if (is_array($res)){
                if (!empty($res['db_error'])) return $this->_json_err('Gagal menyimpan', ['detail'=>$res['db_error']]);
                if (empty($res['ok'])) return $this->_json_err($res['msg'] ?? 'Gagal menyimpan');
                return $this->_json_ok(['message'=>$res['msg'] ?? 'Pembayaran QRIS ditandai lunas','order_paid'=>true]);
            }
            if (!$res){
                $err = $this->db->error();
                $msg = !empty($err['message']) ? ('DB: '.$err['message']) : 'Gagal menyimpan';
                return $this->_json_err($msg);
            }
            return $this->_json_ok(['message'=>'Pembayaran QRIS ditandai lunas','order_paid'=>true]);
        } catch (Throwable $e){
            return $this->_json_err('Exception: '.$e->getMessage());
        }
    }

    /* ----------- PRINT ----------- */
    public function print_receipt($id = null){
        $id = (int)$id; if ($id<=0) show_404();
        list($order,$items) = $this->ao->order_with_items($id);
        if (!$order) show_404();
        $rec  = (object)['nama_website'=>$this->config->item('site_name') ?: 'Toko','kabupaten'=>'','alamat'=>'','telp'=>''];
        $size = $this->input->get('size',true); $size = in_array($size,['58','80']) ? $size : '58';
        $data = compact('order','items','rec','size');
        $this->load->view('admin_order/print_receipt_view', $data);
    }
 
    public function print_table($id = null){
        $id = (int)$id; if ($id<=0) show_404();
        list($order,$items) = $this->ao->order_with_items($id);
        if (!$order) show_404();
        $rec  = (object)['nama_website'=>''];
        $size = $this->input->get('size',true); $size = in_array($size,['58','80']) ? $size : '80';
        $data = compact('order','items','rec','size');
        $this->load->view('admin_order/print_table_view', $data);
    }

    /* ----------- JSON UTIL ----------- */
    private function _json_ok($extra=[]){
        if ($this->security->get_csrf_token_name()){
            $extra['csrf_hash'] = $this->security->get_csrf_hash();
        }
        $extra['success'] = true;
        return $this->output->set_content_type('application/json')->set_output(json_encode($extra));
    }
    private function _json_err($msg='Error', $extra=[]){
        if ($this->security->get_csrf_token_name()){
            $extra['csrf_hash'] = $this->security->get_csrf_hash();
        }
        $extra['success'] = false; $extra['title'] = 'Gagal'; $extra['pesan'] = $msg;
        return $this->output->set_content_type('application/json')->set_output(json_encode($extra));
    }

 // --- CETAK KITCHEN: hanya kategori "makanan" ---
public function print_kitchen($id = null){
    $id = (int)$id; if ($id<=0) show_404();

    list($order, $_all) = $this->ao->order_with_items($id);
    if (!$order) show_404();

    $hasKPTable    = $this->db->table_exists('kategori_produk');     // skema A
    $hasPKTable    = $this->db->table_exists('produk_kategori');     // skema B
    $hasProdCatId  = $this->db->field_exists('kategori_id', 'produk');
    $hasProdCatStr = $this->db->field_exists('kategori', 'produk');  // string kategori

    $this->db->select('pi.id, pi.qty, pi.harga, (pi.qty*pi.harga) AS subtotal, p.nama')
             ->from('pesanan_item pi')
             ->join('produk p','p.id=pi.produk_id','left')
             ->where('pi.pesanan_id', $id);

    if ($hasProdCatId && $hasKPTable){
        // produk.kategori_id -> kategori_produk.id
        $this->db->join('kategori_produk kp','kp.id=p.kategori_id','left')
                 ->group_start()
                    ->where('kp.slug', 'makanan')
                    ->or_where('kp.nama', 'Makanan')
                 ->group_end();
    } elseif ($hasProdCatId && $hasPKTable){
        // produk.kategori_id -> produk_kategori.id
        $this->db->join('produk_kategori pk','pk.id=p.kategori_id','left')
                 ->group_start()
                    ->where('pk.slug', 'makanan')
                    ->or_where('pk.nama', 'Makanan')
                 ->group_end();
    } elseif ($hasProdCatId){
        // Tidak ada tabel kategori, pakai fallback ID 1
        $this->db->where('p.kategori_id', 1);
    } elseif ($hasProdCatStr){
        // Kolom kategori berupa string
        $this->db->group_start()
                 ->like('LOWER(p.kategori)', 'makan', 'after', false) // "makanan*"
                 ->or_like('LOWER(p.kategori)', 'makanan', 'none', false)
                 ->group_end();
    }

    $items = $this->db->order_by('pi.id','ASC')->get()->result();

    $rec  = (object)['nama_website'=>'Kitchen'];
    $size = $this->input->get('size',true); $size = in_array($size,['58','80']) ? $size : '80';
    $data = compact('order','items','rec','size');
    $this->load->view('admin_order/print_kitchen_view', $data);
}

// --- CETAK BAR: hanya kategori "minuman" ---
public function print_bar($id = null){
    $id = (int)$id; if ($id<=0) show_404();

    list($order, $_all) = $this->ao->order_with_items($id);
    if (!$order) show_404();

    $hasKPTable    = $this->db->table_exists('kategori_produk');     // skema A
    $hasPKTable    = $this->db->table_exists('produk_kategori');     // skema B
    $hasProdCatId  = $this->db->field_exists('kategori_id', 'produk');
    $hasProdCatStr = $this->db->field_exists('kategori', 'produk');  // string kategori

    $this->db->select('pi.id, pi.qty, pi.harga, (pi.qty*pi.harga) AS subtotal, p.nama')
             ->from('pesanan_item pi')
             ->join('produk p','p.id=pi.produk_id','left')
             ->where('pi.pesanan_id', $id);

    if ($hasProdCatId && $hasKPTable){
        $this->db->join('kategori_produk kp','kp.id=p.kategori_id','left')
                 ->group_start()
                    ->where('kp.slug', 'minuman')
                    ->or_where('kp.nama', 'Minuman')
                 ->group_end();
    } elseif ($hasProdCatId && $hasPKTable){
        $this->db->join('produk_kategori pk','pk.id=p.kategori_id','left')
                 ->group_start()
                    ->where('pk.slug', 'minuman')
                    ->or_where('pk.nama', 'Minuman')
                 ->group_end();
    } elseif ($hasProdCatId){
        // Fallback ID 2
        $this->db->where('p.kategori_id', 2);
    } elseif ($hasProdCatStr){
        $this->db->group_start()
                 ->like('LOWER(p.kategori)', 'minum', 'after', false)   // "minuman*"
                 ->or_like('LOWER(p.kategori)', 'minuman', 'none', false)
                 ->group_end();
    }

    $items = $this->db->order_by('pi.id','ASC')->get()->result();

    $rec  = (object)['nama_website'=>'Bar'];
    $size = $this->input->get('size',true); $size = in_array($size,['58','80']) ? $size : '80';
    $data = compact('order','items','rec','size');
    $this->load->view('admin_order/print_kitchen_view', $data); // reuse layout
}





}
