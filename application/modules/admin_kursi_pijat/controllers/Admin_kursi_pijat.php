<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_kursi_pijat extends Admin_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('M_admin_kursi_pijat','dm');
        cek_session_akses(get_class($this), $this->session->userdata('admin_session'));
    }

    public function index(){
        $data["controller"] = get_class($this);
        $data["title"]      = "Transaksi Kursi Pijat";
        $data["subtitle"]   = $this->om->engine_nama_menu(get_class($this));
        $data["content"]    = $this->load->view($data["controller"]."_view",$data,true);
        $this->render($data);
    }

    /** ===== Helpers ===== */
    private function _get_setting(){
        $row = $this->db->get_where('kursi_pijat_setting', ['id'=>1])->row();
        if(!$row){
            // default kalau belum ada record
            return (object)[
                'harga_satuan'        => 20000,
                'durasi_unit'         => 15,
                'free_main_threshold' => 10,
            ];
        }
        if (!isset($row->free_main_threshold)) {
            $row->free_main_threshold = 10;
        }
        return $row;
    }

    private function _rupiah($n){ return 'Rp '.number_format((int)$n,0,',','.'); }

    private function _normalize_hp($hp){
        $hp = preg_replace('/\D+/', '', (string)$hp);
        return $hp === '' ? null : $hp;
    }

    /**
     * Tambah poin voucher: 1 baris di table voucher_kursi_pijat = 1x main.
     * $sesi = berapa kali main (dari kolom sesi)
     */
    private function _insert_voucher_rows($idTransaksi, $hp, $nama, $sesi, $status){
        $hp   = $this->_normalize_hp($hp);
        $sesi = (int)$sesi;

        if ($hp === null || $sesi <= 0) return;
        if ($status === 'batal') return; // kalau langsung batal, tidak dihitung

        $now = date('Y-m-d H:i:s');
        for ($i = 0; $i < $sesi; $i++) {
            $this->db->insert('voucher_kursi_pijat',[
                'id_transaksi' => $idTransaksi,
                'no_hp'        => $hp,
                'nama'         => $nama,
                'claime'       => 0,
                'created_at'   => $now,
            ]);
        }
    }

    /**
     * Hapus poin voucher yang sumbernya dari transaksi tertentu
     * (dipakai saat transaksi dibatalkan / dihapus)
     */
    private function _delete_voucher_for_trans($ids){
        if (!is_array($ids)) { $ids = [$ids]; }
        $ids = array_values(array_filter(array_map('intval', $ids), function($v){ return $v > 0; }));
        if (empty($ids)) return;

        $this->db->where_in('id_transaksi', $ids);
        $this->db->delete('voucher_kursi_pijat');
    }

    /** ===== Pengaturan (Master) ===== */
    public function get_setting(){
        $s = $this->_get_setting();
        $this->output->set_content_type('application/json')
            ->set_output(json_encode([
                "success"=>true,
                "data"=>[
                    "harga_satuan"        => (int)$s->harga_satuan,
                    "durasi_unit"         => (int)$s->durasi_unit,
                    "free_main_threshold" => (int)$s->free_main_threshold,
                ]
            ]));
    }

    public function save_setting(){
        $data    = $this->input->post(NULL, TRUE);
        $harga   = (int)($data['harga_satuan'] ?? 0);
        $durasi  = (int)($data['durasi_unit'] ?? 0);
        $freeMain= (int)($data['free_main_threshold'] ?? 0);

        if ($harga < 0 || $durasi <= 0 || $freeMain <= 0) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Input tidak valid"]); return;
        }

        $row = $this->db->get_where('kursi_pijat_setting',['id'=>1])->row();
        $dataUpdate = [
            'harga_satuan'        => $harga,
            'durasi_unit'         => $durasi,
            'free_main_threshold' => $freeMain,
        ];

        $ok  = $row
            ? $this->db->update('kursi_pijat_setting',$dataUpdate,['id'=>1])
            : $this->db->insert('kursi_pijat_setting',array_merge(['id'=>1],$dataUpdate));

        echo json_encode($ok ? ["success"=>true,"title"=>"Berhasil","pesan"=>"Pengaturan tersimpan"]
                             : ["success"=>false,"title"=>"Gagal","pesan"=>"Gagal menyimpan pengaturan"]);
    }

    /** ===== DataTables server-side ===== */
    public function get_data(){
        $list = $this->dm->get_data();
        $data = [];

        // cek role admin dari session
        $isAdmin = ($this->session->userdata('admin_username') === 'admin');

        foreach ($list as $r) {
            $label = ($r->status==='selesai'?'Lunas':($r->status==='batal'?'Batal':'Belum Bayar'));
            $badge = ($r->status==='selesai'?'success':($r->status==='batal'?'danger':'warning'));

            $btnEdit  = '<button type="button" class="btn btn-sm btn-warning" onclick="edit('.(int)$r->id_transaksi.')"><i class="fe-edit"></i></button>';
            $btnBayar = ($r->status==='baru')
                ? ' <button type="button" class="btn btn-sm btn-success ms-1" onclick="bayar('.(int)$r->id_transaksi.')"><i class="fe-check-circle"></i></button>'
                : '';
            $btnBatal = ($r->status==='baru')
                ? ' <button type="button" class="btn btn-sm btn-danger ms-1" onclick="batal('.(int)$r->id_transaksi.')"><i class="fe-x-circle"></i></button>'
                : '';

            $btnHapus = $isAdmin
                ? ' <button type="button" class="btn btn-sm btn-outline-danger ms-1" onclick="hapus('.(int)$r->id_transaksi.')"><i class="fe-trash-2"></i></button>'
                : '';

            // TANGGAL dari created_at
            $tgl_raw = isset($r->created_at) ? (string)$r->created_at : '';
            $tgl_fmt = ($tgl_raw !== '' ? date('d-m-Y H:i', strtotime($tgl_raw)) : '-');

            $hpRaw   = isset($r->no_hp) ? (string)$r->no_hp : '';
            $hpShow  = $hpRaw !== '' ? $hpRaw : '-';

            $row = [
                'cek'      => '<div class="checkbox checkbox-primary checkbox-single"><input type="checkbox" class="data-check" value="'.(int)$r->id_transaksi.'"><label></label></div>',
                'no'       => '',
                'nama'     => htmlspecialchars($r->nama, ENT_QUOTES, 'UTF-8'),
                'no_hp'    => htmlspecialchars($hpShow, ENT_QUOTES, 'UTF-8'),
                'tanggal'  => htmlspecialchars($tgl_fmt, ENT_QUOTES, 'UTF-8'),
                'durasi'   => (int)$r->durasi_menit.' menit',
                'sesi'     => (int)$r->sesi.'x',
                'total'    => $this->_rupiah($r->total_harga),
                'status'   => '<span class="badge badge-'.$badge.'">'.$label.'</span>',
                'aksi'     => $btnEdit.$btnBayar.$btnBatal, // btnHapus dipakai di bulk, biar rapih bisa dimatikan di sini
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

    /** Ambil satu baris */
    public function get_one($id){
        $id  = (int)$id;
        $row = $this->db->get_where('kursi_pijat_transaksi',['id_transaksi'=>$id])->row();
        if(!$row){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]); return;
        }
        echo json_encode(["success"=>true,"data"=>$row]);
    }

    public function set_batal(){
        $id = (int)$this->input->post('id');
        if($id<=0){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"ID tidak valid"]); return;
        }

        $row = $this->db->get_where('kursi_pijat_transaksi',['id_transaksi'=>$id])->row();
        if(!$row){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]); return;
        }

        if($row->status==='batal'){
            echo json_encode(["success"=>true,"title"=>"Info","pesan"=>"Transaksi sudah dibatalkan"]);
            return;
        }
        if($row->status==='selesai'){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Transaksi sudah Lunas dan tidak bisa dibatalkan"]);
            return;
        }

        // hanya boleh batal dari status 'baru'
        $ok = $this->db->update('kursi_pijat_transaksi', ['status'=>'batal'], ['id_transaksi'=>$id]);

        if ($ok) {
            // hapus poin voucher yang berasal dari transaksi ini
            $this->_delete_voucher_for_trans($id);
            echo json_encode(["success"=>true,"title"=>"Berhasil","pesan"=>"Transaksi dibatalkan"]);
        } else {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Gagal mengubah status"]);
        }
    }

    /** Create (mulai otomatis NOW) */
    public function add(){
        $p = $this->input->post(NULL, TRUE);
        $this->load->library('form_validation');

        $this->form_validation->set_rules('nama','Nama','trim|required|min_length[2]|max_length[100]');
        $this->form_validation->set_rules('no_hp','No. HP','trim|max_length[20]');
        $this->form_validation->set_rules('durasi_menit','Durasi (menit)','required|integer|greater_than[0]');
        $this->form_validation->set_rules('status','Status','trim|required|in_list[baru,selesai,batal]');
        $this->form_validation->set_error_delimiters('<br> ',' ');

        if($this->form_validation->run() !== TRUE){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>validation_errors()]); return;
        }

        $set      = $this->_get_setting();
        $dur      = (int)$p['durasi_menit']; // sudah menit (sesi*unit)
        $unit     = (int)$set->durasi_unit;
        $sesi     = (int)max(1, ceil($dur / $unit));
        $harga    = (int)$set->harga_satuan;
        $total    = $sesi * $harga;
        $mulaiDt  = date('Y-m-d H:i:s');
        $selesaiDt= date('Y-m-d H:i:s', strtotime($mulaiDt.' +'.$dur.' minutes'));

        $hpNorm   = $this->_normalize_hp($p['no_hp'] ?? '');

        $ins = [
            'nama'          => $p['nama'],
            'no_hp'         => $hpNorm,
            'durasi_menit'  => $dur,
            'sesi'          => $sesi,
            'harga_satuan'  => $harga,
            'total_harga'   => $total,
            'mulai'         => $mulaiDt,
            'selesai'       => $selesaiDt,
            'status'        => $p['status'],
            'catatan'       => $p['catatan'] ?? null,
        ];
        $ok = $this->db->insert('kursi_pijat_transaksi',$ins);

        if ($ok) {
            $idBaru = (int)$this->db->insert_id();
            // tambahkan poin voucher berdasarkan sesi
            $this->_insert_voucher_rows($idBaru, $hpNorm, $p['nama'], $sesi, $p['status']);

            echo json_encode(["success"=>true,"title"=>"Berhasil","pesan"=>"Data berhasil disimpan"]);
        } else {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data gagal disimpan"]);
        }
    }

    /** Update */
    public function update(){
        $p  = $this->input->post(NULL, TRUE);
        $id = (int)($p['id_transaksi'] ?? 0);

        $this->load->library('form_validation');
        $this->form_validation->set_rules('id_transaksi','ID','required|integer');
        $this->form_validation->set_rules('nama','Nama','trim|required|min_length[2]|max_length[100]');
        $this->form_validation->set_rules('no_hp','No. HP','trim|max_length[20]');
        $this->form_validation->set_rules('durasi_menit','Durasi (menit)','required|integer|greater_than[0]');
        $this->form_validation->set_rules('status','Status','trim|required|in_list[baru,selesai,batal]');
        $this->form_validation->set_error_delimiters('<br> ',' ');

        if($this->form_validation->run() !== TRUE){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>validation_errors()]); return;
        }

        $row = $this->db->get_where('kursi_pijat_transaksi',['id_transaksi'=>$id])->row();
        if(!$row){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]); return;
        }

        $set      = $this->_get_setting();
        $dur      = (int)$p['durasi_menit'];
        $unit     = (int)$set->durasi_unit;
        $sesi     = (int)max(1, ceil($dur / $unit));
        $harga    = (int)$set->harga_satuan;
        $total    = $sesi * $harga;

        $mulaiDt   = $row->mulai ?: null;
        $selesaiDt = $mulaiDt ? date('Y-m-d H:i:s', strtotime($mulaiDt.' +'.$dur.' minutes')) : null;

        $hpNorm    = $this->_normalize_hp($p['no_hp'] ?? '');

        $upd = [
            'nama'          => $p['nama'],
            'no_hp'         => $hpNorm,
            'durasi_menit'  => $dur,
            'sesi'          => $sesi,
            'harga_satuan'  => $harga,
            'total_harga'   => $total,
            'selesai'       => $selesaiDt,
            'status'        => $p['status'],
            'catatan'       => $p['catatan'] ?? null,
        ];
        $ok = $this->db->update('kursi_pijat_transaksi',$upd, ['id_transaksi'=>$id]);

        // NOTE: supaya simple, di sini kita tidak koreksi poin voucher lama.
        // Kalau mau strict, bisa dibuat sinkronisasi khusus.

        echo json_encode($ok ? ["success"=>true,"title"=>"Berhasil","pesan"=>"Data berhasil diupdate"]
                             : ["success"=>false,"title"=>"Gagal","pesan"=>"Data gagal diupdate"]);
    }

    /** Set Lunas (Bayar) */
    public function set_lunas(){
        $id = (int)$this->input->post('id');
        if($id<=0){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"ID tidak valid"]); return;
        }
        $row = $this->db->get_where('kursi_pijat_transaksi',['id_transaksi'=>$id])->row();
        if(!$row){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]); return;
        }
        if($row->status==='selesai'){
            echo json_encode(["success"=>true,"title"=>"Info","pesan"=>"Sudah Lunas"]); return;
        }

        $ok = $this->db->update('kursi_pijat_transaksi',['status'=>'selesai'],['id_transaksi'=>$id]);
        echo json_encode($ok ? ["success"=>true,"title"=>"Berhasil","pesan"=>"Transaksi ditandai Lunas"]
                             : ["success"=>false,"title"=>"Gagal","pesan"=>"Gagal mengubah status"]);
    }

    /** Delete bulk */
    public function hapus_data()
    {
        // Hanya admin yang boleh menghapus
        $user = strtolower(trim((string)$this->session->userdata('admin_username')));
        if ($user !== 'admin') {
            $this->output->set_status_header(403);
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    "success" => false,
                    "title"   => "Ditolak",
                    "pesan"   => "Hanya admin yang boleh menghapus data."
                ]));
        }

        $ids = $this->input->post('id');

        // izinkan single value maupun array
        if (!is_array($ids)) {
            $ids = $ids !== null ? [$ids] : [];
        }

        // Sanitasi ke integer > 0
        $ids = array_values(array_filter(array_map('intval', $ids), function($v){
            return $v > 0;
        }));

        if (empty($ids)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    "success"=>false,
                    "title"  =>"Gagal",
                    "pesan"  =>"ID tidak valid atau tidak ada data dipilih."
                ]));
        }

        // Eksekusi delete transaksi
        $this->db->where_in('id_transaksi', $ids);
        $this->db->delete('kursi_pijat_transaksi');
        $deleted = $this->db->affected_rows();

        if ($deleted > 0) {
            // hapus juga poin voucher yang terkait
            $this->_delete_voucher_for_trans($ids);

            $res = ["success"=>true, "title"=>"Berhasil", "pesan"=>"Data berhasil dihapus"];
        } else {
            $res = ["success"=>false, "title"=>"Gagal", "pesan"=>"Data tidak ditemukan atau sudah dihapus."];
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($res));
    }

}
