<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_voucher_billiard extends Admin_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('M_admin_voucher_billiard','dm');
        $this->load->helper(['url','text']);
        cek_session_akses(get_class($this), $this->session->userdata('admin_session'));
    }

    private function purge_public_caches()
    {
        $this->load->driver('cache', ['adapter' => 'file']);
        $this->cache->save('voucher_billiard_ver', time(), 365*24*3600);
        $this->output->set_header('X-Cache-Purged: voucher_billiard');
    }

    private function _hp_digits($s){
        return preg_replace('/\D+/', '', (string)$s);
    }

    // hasil: 62xxxxxxxxxxx (tanpa +)
    private function _hp_norm62($hp){
        $d = $this->_hp_digits($hp);
        if ($d === '') return '';
        if (strpos($d, '0') === 0)  $d = '62'.substr($d, 1);
        if (strpos($d, '62') !== 0) $d = '62'.$d;
        return $d;
    }

    public function index(){
        $data["controller"] = get_class($this);
        $data["title"]      = "Voucher Billiard";
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

            $id = (int)$r->id_voucher;

            $row['cek'] = '<div class="checkbox checkbox-primary checkbox-single">
                             <input type="checkbox" class="data-check" value="'.$id.'"><label></label>
                           </div>';
            $row['no']  = '';

            $kodeSafe = htmlspecialchars((string)$r->kode_voucher, ENT_QUOTES, 'UTF-8');

            // badge jenis
            $jenis = strtoupper((string)($r->jenis ?? 'FREE_MAIN'));
            $jenisLabel = ($jenis === 'FREE_MAIN') ? 'FREE MAIN' : $jenis;
            $jenisClass = ($jenis === 'FREE_MAIN') ? 'badge badge-primary'
                        : (($jenis === 'NOMINAL') ? 'badge badge-info' : 'badge badge-warning');

            $row['kode_voucher'] =
                '<div class="text-nowrap">'
              .   '<div><code>'.$kodeSafe.'</code></div>'
              .   '<div class="mt-1"><span class="'.$jenisClass.'">'.$jenisLabel.'</span></div>'
              . '</div>';

            $row['nama']  = htmlspecialchars((string)$r->nama, ENT_QUOTES, 'UTF-8');
            $row['no_hp'] = htmlspecialchars((string)$r->no_hp, ENT_QUOTES, 'UTF-8');

            // benefit
            $jam  = (int)($r->jam_voucher ?? 0);
            $nilai= (int)($r->nilai ?? 0);
            $maxP = (int)($r->max_potongan ?? 0);

            if ($jenis === 'FREE_MAIN') {
                $benefit = '<span class="badge badge-success">Gratis '.$jam.' Jam</span>';
            } elseif ($jenis === 'NOMINAL') {
                $benefit = '<span class="badge badge-info">Diskon Rp '.number_format($nilai,0,',','.').'</span>';
            } else { // PERSEN
                $txt = (int)$nilai.'%';
                if ($maxP > 0) $txt .= ' (max Rp '.number_format($maxP,0,',','.').')';
                $benefit = '<span class="badge badge-warning">Diskon '.$txt.'</span>';
            }
            $row['benefit'] = $benefit;

            // dibuat / dipakai
            $row['dibuat']  = !empty($r->created_at) ? htmlspecialchars((string)$r->created_at, ENT_QUOTES, 'UTF-8') : '-';
            $row['dipakai'] = !empty($r->claimed_at) ? htmlspecialchars((string)$r->claimed_at, ENT_QUOTES, 'UTF-8') : '-';

            // status
            $isClaimed = ((int)($r->is_claimed ?? 0) === 1) || (strtolower((string)$r->status) === 'accept');
            $isBatal   = (strtolower((string)$r->status) === 'batal');
            $isExpired = false;

            // expired by periode (kalau kolom ada & belum dipakai)
            $tglSelesai = (string)($r->tgl_selesai ?? '');
            if (!$isClaimed && $tglSelesai !== '' && $tglSelesai < $today) {
                $isExpired = true;
            }

            if ($isClaimed){
                $row['status'] = '<span class="badge badge-success">Dipakai</span>';
            } elseif ($isBatal){
                $row['status'] = '<span class="badge badge-danger">Batal</span>';
            } elseif ($isExpired){
                $row['status'] = '<span class="badge badge-warning">Expired</span>';
            } else {
                $row['status'] = '<span class="badge badge-primary">Baru</span>';
            }

            // tombol
            $btnDetail = '<button type="button" class="btn btn-sm btn-blue mr-1" onclick="detailVoucher('.$id.')">
                            <i class="fe-info"></i> Detail
                          </button>';

            $btnEdit = '<button type="button" class="btn btn-sm btn-warning mr-1" onclick="edit('.$id.')">
                          <i class="fe-edit"></i> Edit
                        </button>';

            // WA link (wa.me)
            $hpNorm = $this->_hp_norm62($r->no_hp_norm ?: $r->no_hp);
            $btnWA  = '';
            if ($hpNorm !== '') {
                $waUrl = 'https://wa.me/'.htmlspecialchars($hpNorm, ENT_QUOTES, 'UTF-8');
                $btnWA = '<a href="'.$waUrl.'" target="_blank" rel="noopener" class="btn btn-sm btn-success mr-1" title="WhatsApp">
                            <i class="mdi mdi-whatsapp"></i> WA
                          </a>';
            } else {
                $btnWA = '<button type="button" class="btn btn-sm btn-secondary mr-1" disabled>
                            <i class="fe-alert-circle"></i> WA
                          </button>';
            }

            // print rawbt
            $rawbtUrl = site_url(
                'admin_voucher_billiard/print_voucher_termal/'.$id
                . '?paper=58&rawbt=1&autoprint=1&autoclose=1&embed=1'
            );
            $btnRawbt = '<a href="'.$rawbtUrl.'" target="_blank" rel="noopener" class="btn btn-sm btn-primary" title="Kirim ke RawBT (HP)">
                           <i class="fa fa-mobile"></i> Print
                         </a>';

            // sembunyikan edit/wa jika claimed/batal/expired
            if ($isClaimed || $isBatal || $isExpired) {
                $btnEdit = '';
                $btnWA   = '';
            }

            $row['aksi'] =
                '<div class="btn-group btn-group-sm" role="group" aria-label="Aksi voucher">'
              .   $btnDetail
              .   $btnEdit
              .   $btnWA
              .   $btnRawbt
              . '</div>';

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

    public function print_voucher_termal($id = null)
    {
        $id = (int)$id;
        if ($id <= 0) show_error('ID voucher tidak valid', 400);

        $voucher = $this->db->from('voucher_billiard')
                            ->where('id_voucher', $id)
                            ->get()->row();
        if (!$voucher) show_error('Data voucher tidak ditemukan', 404);

        $paper = $this->input->get('paper', true);
        $paper = ($paper === '80') ? '80' : '58';

        $web = $this->om->web_me();

        $store = [
            'nama'   => $web->nama_website ?? 'AUSI Billiard',
            'alamat' => $web->alamat ?? '',
            'kota'   => $web->kabupaten ?? '',
            'telp'   => $web->no_telp ?? '',
            'footer' => 'Terima kasih 🙏',
            // 'logo_url' => base_url('assets/images/logo.png'), // optional
        ];

        $data = [
            'paper'      => $paper,
            'voucher'    => $voucher,
            'store'      => (object)$store,
            'printed_at' => date('Y-m-d H:i:s'),
        ];

        $html = $this->load->view('voucher_billiard_struk_termal', $data, true);

        $this->output
             ->set_content_type('text/html; charset=UTF-8')
             ->set_output($html);
    }

    public function get_one($id)
    {
        $id  = (int)$id;
        $row = $this->db->get_where('voucher_billiard',['id_voucher'=>$id])->row();

        if (!$row) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]);
            return;
        }
        echo json_encode(["success"=>true,"data"=>$row]);
    }

    public function add()
    {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('nama','Nama','trim|required|min_length[3]|max_length[100]');
        $this->form_validation->set_rules('no_hp','No. HP','trim|required|min_length[6]|max_length[32]');
        $this->form_validation->set_rules('jenis','Jenis','required|in_list[FREE_MAIN,NOMINAL,PERSEN]');
        $this->form_validation->set_rules('jam_voucher','Jam Voucher','trim|integer');
        $this->form_validation->set_rules('nilai','Nilai','trim|integer');
        $this->form_validation->set_rules('max_potongan','Max Potongan','trim|integer');
        $this->form_validation->set_rules('minimal_subtotal','Minimal Subtotal','trim|integer');
        $this->form_validation->set_rules('tgl_mulai','Tanggal Mulai','trim');
        $this->form_validation->set_rules('tgl_selesai','Tanggal Selesai','trim');
        $this->form_validation->set_rules('notes','Catatan','trim|max_length[255]');

        if ($this->form_validation->run() !== TRUE) {
            echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>validation_errors()]);
            return;
        }

        $nama  = $this->input->post('nama', true);
        $no_hp = $this->input->post('no_hp', true);
        $jenis = strtoupper((string)$this->input->post('jenis', true));

        $jam   = (int)$this->input->post('jam_voucher', true);
        $nilai = (int)$this->input->post('nilai', true);
        $maxP  = (int)$this->input->post('max_potongan', true);
        $minS  = (int)$this->input->post('minimal_subtotal', true);

        $tgl_mulai   = $this->input->post('tgl_mulai', true);
        $tgl_selesai = $this->input->post('tgl_selesai', true);

        $notes = $this->input->post('notes', true);

        // aturan per jenis
        if ($jenis === 'FREE_MAIN') {
            if ($jam < 1) {
                echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>"Jam voucher minimal 1"]);
                return;
            }
            $nilai = 0; $maxP = 0; $minS = 0;
        } elseif ($jenis === 'NOMINAL') {
            if ($nilai < 1) {
                echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>"Nilai diskon nominal minimal Rp 1"]);
                return;
            }
            $jam = 0; $maxP = 0;
        } else { // PERSEN
            if ($nilai < 1 || $nilai > 100) {
                echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>"Nilai persen harus 1 - 100"]);
                return;
            }
            $jam = 0;
        }

        // validasi periode jika diisi
        if ($tgl_mulai !== '' && $tgl_selesai !== '' && strtotime($tgl_selesai) < strtotime($tgl_mulai)) {
            echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>"Tanggal selesai tidak boleh sebelum tanggal mulai"]);
            return;
        }

        $hp_digits = $this->_hp_digits($no_hp);
        if ($hp_digits === '') {
            echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>"No. HP tidak valid"]);
            return;
        }

        $hp_norm = $this->_hp_norm62($hp_digits);

        $kode = $this->dm->generate_kode_voucher();
        $now  = date('Y-m-d H:i:s');

        $data_insert = [
            'no_hp'            => $hp_digits,
            'no_hp_norm'       => $hp_norm,
            'nama'             => $nama,
            'kode_voucher'     => $kode,
            'jam_voucher'      => $jam,
            'jenis'            => $jenis,
            'nilai'            => $nilai,
            'max_potongan'     => max(0, $maxP),
            'minimal_subtotal' => max(0, $minS),
            'tgl_mulai'        => ($tgl_mulai !== '' ? $tgl_mulai : NULL),
            'tgl_selesai'      => ($tgl_selesai !== '' ? $tgl_selesai : NULL),
            'status'           => 'baru',
            'is_claimed'       => 0,
            'issued_from_count'=> 0,
            'created_at'       => $now,
            'claimed_at'       => NULL,
            'notes'            => $notes,
        ];

        $ok = $this->db->insert('voucher_billiard', $data_insert);

        if ($ok) { $this->purge_public_caches(); }

        echo json_encode([
            "success"=>$ok,
            "title"=>$ok?"Berhasil":"Gagal",
            "pesan"=>$ok
                ? "Voucher berhasil disimpan. Kode: <strong>".$kode."</strong>"
                : "Data gagal disimpan"
        ]);
    }

    public function update()
    {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('id_voucher','ID','required|integer');
        $this->form_validation->set_rules('nama','Nama','trim|required|min_length[3]|max_length[100]');
        $this->form_validation->set_rules('no_hp','No. HP','trim|required|min_length[6]|max_length[32]');
        $this->form_validation->set_rules('jenis','Jenis','required|in_list[FREE_MAIN,NOMINAL,PERSEN]');
        $this->form_validation->set_rules('jam_voucher','Jam Voucher','trim|integer');
        $this->form_validation->set_rules('nilai','Nilai','trim|integer');
        $this->form_validation->set_rules('max_potongan','Max Potongan','trim|integer');
        $this->form_validation->set_rules('minimal_subtotal','Minimal Subtotal','trim|integer');
        $this->form_validation->set_rules('tgl_mulai','Tanggal Mulai','trim');
        $this->form_validation->set_rules('tgl_selesai','Tanggal Selesai','trim');
        $this->form_validation->set_rules('notes','Catatan','trim|max_length[255]');

        if ($this->form_validation->run() !== TRUE) {
            echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>validation_errors()]);
            return;
        }

        $id    = (int)$this->input->post('id_voucher', true);
        $row   = $this->db->get_where('voucher_billiard',['id_voucher'=>$id])->row();
        if (!$row) {
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]);
            return;
        }

        $nama  = $this->input->post('nama', true);
        $no_hp = $this->input->post('no_hp', true);
        $jenis = strtoupper((string)$this->input->post('jenis', true));

        $jam   = (int)$this->input->post('jam_voucher', true);
        $nilai = (int)$this->input->post('nilai', true);
        $maxP  = (int)$this->input->post('max_potongan', true);
        $minS  = (int)$this->input->post('minimal_subtotal', true);

        $tgl_mulai   = $this->input->post('tgl_mulai', true);
        $tgl_selesai = $this->input->post('tgl_selesai', true);
        $notes       = $this->input->post('notes', true);

        if ($jenis === 'FREE_MAIN') {
            if ($jam < 1) {
                echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>"Jam voucher minimal 1"]);
                return;
            }
            $nilai = 0; $maxP = 0; $minS = 0;
        } elseif ($jenis === 'NOMINAL') {
            if ($nilai < 1) {
                echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>"Nilai diskon nominal minimal Rp 1"]);
                return;
            }
            $jam = 0; $maxP = 0;
        } else { // PERSEN
            if ($nilai < 1 || $nilai > 100) {
                echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>"Nilai persen harus 1 - 100"]);
                return;
            }
            $jam = 0;
        }

        if ($tgl_mulai !== '' && $tgl_selesai !== '' && strtotime($tgl_selesai) < strtotime($tgl_mulai)) {
            echo json_encode(["success"=>false,"title"=>"Validasi Gagal","pesan"=>"Tanggal selesai tidak boleh sebelum tanggal mulai"]);
            return;
        }

        $hp_digits = $this->_hp_digits($no_hp);
        $hp_norm   = $this->_hp_norm62($hp_digits);

        $data_update = [
            'no_hp'            => $hp_digits,
            'no_hp_norm'       => $hp_norm,
            'nama'             => $nama,
            'jenis'            => $jenis,
            'jam_voucher'      => $jam,
            'nilai'            => $nilai,
            'max_potongan'     => max(0, $maxP),
            'minimal_subtotal' => max(0, $minS),
            'tgl_mulai'        => ($tgl_mulai !== '' ? $tgl_mulai : NULL),
            'tgl_selesai'      => ($tgl_selesai !== '' ? $tgl_selesai : NULL),
            'notes'            => $notes,
        ];

        $ok = $this->db->where('id_voucher',$id)->update('voucher_billiard', $data_update);

        if ($ok) { $this->purge_public_caches(); }

        echo json_encode([
            "success"=>$ok,
            "title"=>$ok?"Berhasil":"Gagal",
            "pesan"=>$ok?"Voucher berhasil diupdate":"Data gagal diupdate"
        ]);
    }

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
            $ok = $ok && $this->db->delete('voucher_billiard', ['id_voucher'=>$id]);
        }

        if ($ok) { $this->purge_public_caches(); }

        echo json_encode([
            "success"=>$ok,
            "title"=>$ok?"Berhasil":"Gagal",
            "pesan"=>$ok?"Data berhasil dihapus":"Sebagian data gagal dihapus"
        ]);
    }
}
