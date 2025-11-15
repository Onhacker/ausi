<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_voucher_cafe extends Admin_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('M_admin_voucher_cafe','dm');
        $this->load->helper(['url','text']);
        cek_session_akses(get_class($this), $this->session->userdata('admin_session'));
    }

    /** (opsional) bump cache untuk front-end jika nanti ada list voucher publik */
    private function purge_public_caches()
    {
        $this->load->driver('cache', ['adapter' => 'file']);
        $this->cache->save('voucher_cafe_ver', time(), 365*24*3600);
        $this->output->set_header('X-Cache-Purged: voucher_cafe');
    }

    public function index(){
        $data["controller"] = get_class($this);
        $data["title"]      = "Voucher Cafe";
        $data["subtitle"]   = $this->om->engine_nama_menu(get_class($this));
        $data["content"]    = $this->load->view($data["controller"]."_view",$data,true);
        $this->render($data);
    }

    /** DataTables server-side */
  public function get_dataa()
{
    $list  = $this->dm->get_data();
    $data  = [];
    $today = date('Y-m-d');

    foreach ($list as $r) {
        $row = [];

        $row['cek'] = '<div class="checkbox checkbox-primary checkbox-single">
                         <input type="checkbox" class="data-check" value="'.(int)$r->id.'"><label></label>
                       </div>';
        $row['no']  = '';

        // ====== KODE VOUCHER + BADGE JENIS ======
        $kodeSafe = htmlspecialchars($r->kode_voucher, ENT_QUOTES, 'UTF-8');

        // label jenis voucher (default: Mingguan)
        $jenis      = isset($r->jenis_voucher) ? (string)$r->jenis_voucher : '';
        if ($jenis === 'mingguan') {
            $jenisLabel = 'Voucher Mingguan';
            $jenisClass = 'badge badge-primary';
        } else {
            // fallback kalau nanti ada jenis lain
            $jenisLabel = $jenis ? ucwords(str_replace('_',' ', $jenis)) : '';
            $jenisClass = 'badge badge-secondary';
        }

        $badgeJenis = $jenisLabel
            ? '<span class="'.$jenisClass.'">'.$jenisLabel.'</span>'
            : '';

        $row['kode_voucher'] =
            '<div class="text-nowrap">'
          .   '<div>'.$kodeSafe.'</div>'
          .   ($badgeJenis ? '<div class="mt-1">'.$badgeJenis.'</div>' : '')
          . '</div>';
        // ========================================

        $row['nama']  = htmlspecialchars($r->nama, ENT_QUOTES, 'UTF-8');
        $row['no_hp'] = htmlspecialchars($r->no_hp, ENT_QUOTES, 'UTF-8');

        $labelTipe = $r->tipe === 'persen' ? 'Persen (%)' : 'Nominal (Rp)';
        $row['tipe'] = '<span class="badge badge-info">'.$labelTipe.'</span>';

        if ($r->tipe === 'persen') {
            $nilaiLabel = (int)$r->nilai.' %';
        } else {
            $nilaiLabel = 'Rp '.number_format((int)$r->nilai,0,',','.');
        }
        $row['nilai'] = $nilaiLabel;

        if (function_exists('tgl_view')) {
            $periode = tgl_view($r->tgl_mulai).' s/d '.tgl_view($r->tgl_selesai);
        } else {
            $periode = htmlspecialchars($r->tgl_mulai.' s/d '.$r->tgl_selesai, ENT_QUOTES, 'UTF-8');
        }
        $row['periode'] = $periode;

        $row['klaim'] = (int)$r->klaim_terpakai.' / '.(int)$r->kuota_klaim;

        // Status: Aktif, Nonaktif, Kadaluarsa
        if ((int)$r->status === 0) {
            $row['status'] = '<span class="badge badge-secondary">Nonaktif</span>';
        } else {
            if ($r->tgl_selesai < $today) {
                $row['status'] = '<span class="badge badge-warning">Kadaluarsa</span>';
            } else {
                $row['status'] = '<span class="badge badge-success">Aktif</span>';
            }
        }

        // Tombol edit
        $btnEdit = '<button type="button" class="btn btn-sm btn-warning" onclick="edit('.(int)$r->id.')">
                      <i class="fe-edit"></i> Edit
                    </button>';

        // ====== TOMBOL KIRIM WHATSAPP KE CUSTOMER LANGSUNG ======
        // Susun teks yang akan dikirim ke WA
       $shareText = "Halo kak {$r->nama},\n"
           . "Selamat, kakak mendapatkan *Voucher Order AUSI Cafe*!\n\n"
           . "Detail Voucher:\n"
           . "- Kode Voucher : *{$r->kode_voucher}*\n"
           . "- Nama : {$r->nama}\n"
           . "- Tipe : {$labelTipe}\n"
           . "- Nilai : {$nilaiLabel}\n"
           . "- Periode : {$periode}\n\n"
           . "Cara Pakai:\n"
           . "Masukkan kode voucher ini saat melakukan order, sebelum periode berakhir.\n\n"
           . "Terima kasih sudah menjadi pelanggan setia AUSI Cafe.";


        // Normalisasi nomor HP ke format 62xxxxxxxxxxx
        $phoneRaw = preg_replace('/\D+/', '', (string)$r->no_hp); // buang selain digit
        $waUrl    = '';
        $btnWa    = '';

        if (!empty($phoneRaw)) {
            if (strpos($phoneRaw, '0') === 0) {
                // 08xxxx -> 628xxxx
                $phone = '62'.substr($phoneRaw, 1);
            } elseif (strpos($phoneRaw, '62') === 0) {
                // sudah 62xxxx
                $phone = $phoneRaw;
            } else {
                // asumsi nomor lokal tanpa 0 / 62, tambahkan 62 di depan
                $phone = '62'.$phoneRaw;
            }

            $waUrl = 'https://wa.me/'.$phone.'?text='.rawurlencode($shareText);
            $waUrl = htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8');

            $btnWa = '<a href="'.$waUrl.'" target="_blank" rel="noopener" '
                   . 'class="btn btn-sm btn-success mt-1" title="Kirim ke WhatsApp customer">'
                   . '<i class="fe-send"></i> WA</a>';
        } else {
            // Jika tidak ada nomor HP, tombol dinonaktifkan
            $btnWa = '<button type="button" class="btn btn-sm btn-secondary mt-1" '
                   . 'title="Nomor HP tidak tersedia" disabled>'
                   . '<i class="fe-alert-circle"></i> WA</button>';
        }
        // ========================================================

        // Gabungkan tombol
        $row['aksi'] = $btnEdit.' '.$btnWa;

        $data[] = $row;
    }

    $out = [
        "draw"            => (int)$this->input->post('draw'),
        "recordsTotal"    => $this->dm->count_all(),
        "recordsFiltered" => $this->dm->count_filtered(),
        "data"            => $data,
    ];

    $this->output
         ->set_content_type('application/json')
         ->set_output(json_encode($out));
}


    /** Ambil satu baris untuk form edit */
    public function get_one($id)
    {
        $id  = (int)$id;
        $row = $this->db->get_where('voucher_cafe_manual',['id'=>$id])->row();

        if (!$row) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]);
            return;
        }
        echo json_encode(["success"=>true,"data"=>$row]);
    }

    /** Create */
    public function add()
    {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('nama','Nama','trim|required|min_length[3]|max_length[100]');
        $this->form_validation->set_rules('no_hp','No. HP','trim|required|min_length[6]|max_length[20]');
        $this->form_validation->set_rules('tipe','Tipe Voucher','required|in_list[persen,nominal]');
        $this->form_validation->set_rules('nilai','Nilai','required|numeric');
        $this->form_validation->set_rules('tgl_mulai','Tanggal Mulai','required');
        $this->form_validation->set_rules('tgl_selesai','Tanggal Selesai','required');
        $this->form_validation->set_rules('kuota_klaim','Kuota Klaim','required|integer');

        if ($this->form_validation->run() !== TRUE) {
            echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>validation_errors()]);
            return;
        }

        $nama            = $this->input->post('nama', true);
        $no_hp           = $this->input->post('no_hp', true);
        $tipe            = $this->input->post('tipe', true);
        $nilai           = (int)$this->input->post('nilai', true);
        $minimal_belanja = (int)$this->input->post('minimal_belanja', true);
        $max_potongan    = (int)$this->input->post('max_potongan', true);
        $tgl_mulai       = $this->input->post('tgl_mulai', true);
        $tgl_selesai     = $this->input->post('tgl_selesai', true);
        $kuota_klaim     = (int)$this->input->post('kuota_klaim', true);
        $status          = (int)$this->input->post('status', true);
        $keterangan      = $this->input->post('keterangan', true);

        // Validasi tambahan
        if ($tipe === 'persen' && ($nilai <= 0 || $nilai > 100)) {
            echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>"Nilai persen harus antara 1 - 100"]);
            return;
        }
        if (strtotime($tgl_selesai) < strtotime($tgl_mulai)) {
            echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>"Tanggal selesai tidak boleh sebelum tanggal mulai"]);
            return;
        }
        if ($kuota_klaim <= 0) {
            echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>"Kuota klaim minimal 1"]);
            return;
        }

        $username = $this->session->userdata('admin_username') ?: 'admin';
        $kode     = $this->dm->generate_kode_voucher();

        $data_insert = [
            'kode_voucher'    => $kode,
            'nama'            => $nama,
            'no_hp'           => $no_hp,
            'tipe'            => $tipe,
            'nilai'           => $nilai,
            'minimal_belanja' => max(0, $minimal_belanja),
            'max_potongan'    => max(0, $max_potongan),
            'tgl_mulai'       => $tgl_mulai,
            'tgl_selesai'     => $tgl_selesai,
            'kuota_klaim'     => $kuota_klaim,
            'klaim_terpakai'  => 0,
            'status'          => $status ? 1 : 0,
            'keterangan'      => $keterangan,
            'created_at'      => date('Y-m-d H:i:s'),
            'created_by'      => $username
        ];

        $ok = $this->db->insert('voucher_cafe_manual', $data_insert);


        if ($ok) { $this->purge_public_caches(); }

        echo json_encode([
            "success"=>$ok,
            "title"=>$ok?"Berhasil":"Gagal",
            "pesan"=>$ok
                ? "Voucher berhasil disimpan. Kode: <strong>".$kode."</strong>"
                : "Data gagal disimpan"
        ]);
    }

    /** Update */
    public function update()
    {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('id','ID','required|integer');
        $this->form_validation->set_rules('nama','Nama','trim|required|min_length[3]|max_length[100]');
        $this->form_validation->set_rules('no_hp','No. HP','trim|required|min_length[6]|max_length[20]');
        $this->form_validation->set_rules('tipe','Tipe Voucher','required|in_list[persen,nominal]');
        $this->form_validation->set_rules('nilai','Nilai','required|numeric');
        $this->form_validation->set_rules('tgl_mulai','Tanggal Mulai','required');
        $this->form_validation->set_rules('tgl_selesai','Tanggal Selesai','required');
        $this->form_validation->set_rules('kuota_klaim','Kuota Klaim','required|integer');

        if ($this->form_validation->run() !== TRUE) {
            echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>validation_errors()]);
            return;
        }

        $id              = (int)$this->input->post('id', true);
        $nama            = $this->input->post('nama', true);
        $no_hp           = $this->input->post('no_hp', true);
        $tipe            = $this->input->post('tipe', true);
        $nilai           = (int)$this->input->post('nilai', true);
        $minimal_belanja = (int)$this->input->post('minimal_belanja', true);
        $max_potongan    = (int)$this->input->post('max_potongan', true);
        $tgl_mulai       = $this->input->post('tgl_mulai', true);
        $tgl_selesai     = $this->input->post('tgl_selesai', true);
        $kuota_klaim     = (int)$this->input->post('kuota_klaim', true);
        $status          = (int)$this->input->post('status', true);
        $keterangan      = $this->input->post('keterangan', true);

        $row = $this->db->get_where('voucher_cafe_manual',['id'=>$id])->row();
        if (!$row) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]);
            return;
        }

        if ($tipe === 'persen' && ($nilai <= 0 || $nilai > 100)) {
            echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>"Nilai persen harus antara 1 - 100"]);
            return;
        }
        if (strtotime($tgl_selesai) < strtotime($tgl_mulai)) {
            echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>"Tanggal selesai tidak boleh sebelum tanggal mulai"]);
            return;
        }
        if ($kuota_klaim <= 0) {
            echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>"Kuota klaim minimal 1"]);
            return;
        }

        $username = $this->session->userdata('admin_username') ?: 'admin';

        $data_update = [
            'nama'            => $nama,
            'no_hp'           => $no_hp,
            'tipe'            => $tipe,
            'nilai'           => $nilai,
            'minimal_belanja' => max(0, $minimal_belanja),
            'max_potongan'    => max(0, $max_potongan),
            'tgl_mulai'       => $tgl_mulai,
            'tgl_selesai'     => $tgl_selesai,
            'kuota_klaim'     => $kuota_klaim,
            'status'          => $status ? 1 : 0,
            'keterangan'      => $keterangan,
            'updated_at'      => date('Y-m-d H:i:s'),
            'updated_by'      => $username
        ];

        $ok = $this->db->where('id',$id)->update('voucher_cafe_manual', $data_update);

        if ($ok) { $this->purge_public_caches(); }

        echo json_encode([
            "success"=>$ok,
            "title"=>$ok?"Berhasil":"Gagal",
            "pesan"=>$ok?"Voucher berhasil diupdate":"Data gagal diupdate"
        ]);
    }

    /** Delete (bulk) */
    public function hapus_data()
    {
        $ids = $this->input->post('id');
        if (!is_array($ids) || count($ids) === 0) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Tidak ada data"]);
            return;
        }

        $ok = true;
        foreach ($ids as $id) {
            $id = (int)$id;
            if ($id <= 0) continue;
            $ok = $ok && $this->db->delete('voucher_cafe_manual', ['id'=>$id]);

        }

        if ($ok) { $this->purge_public_caches(); }

        echo json_encode([
            "success"=>$ok,
            "title"=>$ok?"Berhasil":"Gagal",
            "pesan"=>$ok?"Data berhasil dihapus":"Sebagian data gagal dihapus"
        ]);
    }
}
