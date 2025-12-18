<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_billiard extends Admin_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('M_admin_billiard','dm');
    }

    private function purge_public_caches(){
        $this->load->driver('cache', ['adapter' => 'file']);
        $this->cache->save('billiard_ver', time(), 365*24*3600);
        $this->output->set_header('X-Cache-Purged: billiard');
    }

    public function index(){
        $data["controller"] = get_class($this);
        $data["title"]      = "Booking Billiard";
        $data["subtitle"]   = "Daftar Pesanan";
        $data["content"]    = $this->load->view('admin_billiard_view',$data,true);
        $this->render($data);
    }

    /** DataTables server-side */
    public function get_data(){
        try{
            $status = $this->input->post('status', true);
            if ($status === '' || $status === null) { $status = 'all'; }

            $this->dm->set_max_rows(200);
            $this->dm->set_status_filter($status);

            $list = $this->dm->get_data();
            $data = [];

            foreach($list as $r){
            // ===== Kode Booking (atau "Selesai Main")
                $kode = htmlspecialchars($r->kode_booking ?: '-', ENT_QUOTES, 'UTF-8');

            // Hitung apakah sudah selesai main
                            // === Hitung apakah sudah selesai main (handle nyebrang tengah malam) ===
            $startTs = null;
            $endTs   = null;
            $crossMidnight = false;

            if (!empty($r->tanggal) && !empty($r->jam_mulai)) {
                $startTs = strtotime(trim($r->tanggal.' '.$r->jam_mulai));
            }
            if (!empty($r->tanggal) && !empty($r->jam_selesai)) {
                $endTs = strtotime(trim($r->tanggal.' '.$r->jam_selesai));
            }

            // Jika jam_selesai <= jam_mulai, berarti selesai di H+1
            if ($startTs && $endTs && $endTs <= $startTs) {
                $endTs += 86400; // +1 hari
                $crossMidnight = true;
            }

            $isFinished = ($endTs && time() >= $endTs);


            // Tampilkan di kolom "Kode Booking":
            // - Jika selesai → "Selesai Main"
            // - Jika belum → kode booking seperti biasa
                // Tampilkan di kolom "Kode Booking":
                // Tampilkan di kolom "Kode Booking":
                // - Jika selesai & status != batal → "Selesai Main" (hijau)
                // - Jika selesai & status = batal  → "Batal Main" (merah)
                // - Jika belum selesai → hanya kode booking
                $kode_html = '<span class="badge badge-dark">'.$kode.'</span>';

                if ($isFinished) {
                    $isBatal    = (strtolower((string)$r->status) === 'batal');
                    $statusMain = $isBatal ? 'Batal Main ❌' : 'Selesai Main 🎱';
                    $badgeClass = $isBatal ? 'badge-danger' : 'badge-success';

                    $kode_html .= '<br><span class="badge '.$badgeClass.'">'.$statusMain.'</span>';
                }


            // ===== Meja / Nama
                $meja = $r->nama_meja ?: ('Meja #'.$r->meja_id);
                $meja_html = htmlspecialchars($meja, ENT_QUOTES, 'UTF-8');
                $nama = trim((string)$r->nama);
                if ($nama !== ''){
                    $meja_html .= '<div class="text-muted small">'.htmlspecialchars($nama, ENT_QUOTES, 'UTF-8').'</div>';
                }

            // ===== Durasi
                $durasi = (int)$r->durasi_jam;
                $durasi_html = '<b>'.$durasi.' jam</b>';

            // ===== Waktu dibuat booking
                $tgl_book = $r->created_at ? date('d-m-Y', strtotime($r->created_at)) : '-';
                $tgl_book_html = $tgl_book.'<div class="text-dark small"> Jam '.htmlspecialchars(date('H:i:s', strtotime($r->created_at)),ENT_QUOTES,'UTF-8').'</div>';

            // ===== Waktu main
                $tgl = $r->tanggal ? date('d-m-Y', strtotime($r->tanggal)) : '-';

                $jamMulai   = $r->jam_mulai   ? substr($r->jam_mulai, 0, 5)   : '';
                $jamSelesai = $r->jam_selesai ? substr($r->jam_selesai, 0, 5) : '';
                $jamLabel   = 'Jam '.$jamMulai.' - '.$jamSelesai.($crossMidnight ? ' (besok)' : '');

                $waktu_html = $tgl.' ('.$durasi_html.')'
                            . '<div class="text-blue small">'.htmlspecialchars($jamLabel, ENT_QUOTES, 'UTF-8').'</div>';


            // ===== Harga / Grand
                $harga_jam = (int)$r->harga_per_jam;
                $grand     = (int)$r->grand_total;
                $harga_html = 'Rp '.number_format($harga_jam,0,',','.');
                $grand_html = 'Rp '.number_format($grand,0,',','.');

            // ===== Status bayar (tetap seperti semula)
                $sraw = strtolower((string)$r->status);
                $badge = 'secondary'; 
                $label = $sraw;
                switch ($sraw){
                    case 'draft':            $badge='warning'; $label='menunggu pembayaran'; break;
                    case 'menunggu_bayar':   $badge='warning'; $label='menunggu pembayaran'; break;
                    case 'verifikasi':       $badge='danger';    $label='menunggu verifikasi'; break;
                    case 'terkonfirmasi':    $badge='success'; $label='lunas'; break;
                    case 'batal':            $badge='dark';    $label='batal'; break;
                    case 'free':             $badge='primary'; $label='free'; break;
                }
                $status_html = '<span class="badge badge-pill badge-'.$badge.'">'.htmlspecialchars($label,ENT_QUOTES,'UTF-8').'</span>';
                // ===== Status bayar (tetap seperti semula)
                $sraw = strtolower((string)$r->status);
                $badge = 'secondary'; 
                $label = $sraw;
                switch ($sraw){
                  case 'draft':            $badge='warning'; $label='menunggu pembayaran'; break;
                  case 'menunggu_bayar':   $badge='warning'; $label='menunggu pembayaran'; break;
                  case 'verifikasi':       $badge='info';    $label='menunggu verifikasi'; break;
                  case 'terkonfirmasi':    $badge='success'; $label='lunas'; break;
                  case 'batal':            $badge='dark';    $label='batal'; break;
                  case 'free':             $badge='primary'; $label='free'; break;
                }
                $status_html = '<span class="badge badge-pill badge-'.$badge.'">'.htmlspecialchars($label,ENT_QUOTES,'UTF-8').'</span>';

                // ===== TAMBAHAN: tampilkan voucher di bawah status (jika ada)
                $vc = trim((string)($r->voucher_code ?? ''));
                if ($vc !== '') {
                  $vcEsc = htmlspecialchars($vc, ENT_QUOTES, 'UTF-8');
                  $status_html .= '<div class="mt-1"><span class="badge badge-info">Voucher: '.$vcEsc.'</span></div>';
                }

            // ===== Metode
                $metode_html = htmlspecialchars($r->metode_bayar ?: '-', ENT_QUOTES, 'UTF-8');

                          // ... di dalam foreach($list as $r) { setelah $metode_html ... 
                $idInt     = (int)$r->id_pesanan;
                $namaPlain = trim((string)$r->nama) ?: '-';
                $namaAttr  = htmlspecialchars($namaPlain, ENT_QUOTES, 'UTF-8');
                $mejaName  = $r->nama_meja ?: ('Meja #'.$r->meja_id);
                $mejaAttr  = htmlspecialchars($mejaName, ENT_QUOTES, 'UTF-8');

                
                    $canReschedule = (!$isFinished && strtolower((string)$r->status) !== 'batal');

                    if ($canReschedule) {
                        $btnResch = '<button type="button" class="btn btn-sm btn-success mr-1 btn-reschedule"
                                         data-id="'.$idInt.'"
                                         data-nama="'.$namaAttr.'"
                                         data-meja="'.$mejaAttr.'"
                                         data-tanggal="'.htmlspecialchars($r->tanggal ?: '', ENT_QUOTES, 'UTF-8').'"
                                         data-jam_mulai="'.htmlspecialchars(substr((string)$r->jam_mulai,0,5), ENT_QUOTES, 'UTF-8').'"
                                         title="Reschedule">
                                       <i class="fe-clock"></i>
                                     </button>';
                    } else {
                        // tampilkan tombol non-aktif (tooltip menjelaskan alasannya)
                        $btnResch = '<button type="button" class="btn btn-sm btn-outline-secondary mr-1" disabled
                                         title="Tidak bisa reschedule: booking batal / sudah selesai main">
                                       <i class="fe-clock"></i>
                                     </button>';
                    }

                // Tombol lain: kirim element (this) supaya bisa baca data-nama & data-meja
                $btnPaid   = '<button type="button" class="btn btn-sm btn-primary mr-1"
                                 data-id="'.$idInt.'" data-nama="'.$namaAttr.'" data-meja="'.$mejaAttr.'"
                                 onclick="mark_paid_one(this)" title="Konfirmasi Bayar"><i class="fe-check-circle"></i></button>';

                $btnCancel = '<button type="button" class="btn btn-sm btn-secondary mr-1"
                                 data-id="'.$idInt.'" data-nama="'.$namaAttr.'" data-meja="'.$mejaAttr.'"
                                 onclick="mark_canceled_one(this)" title="Batalkan"><i class="fe-x-circle"></i></button>';

                $unameLower = strtolower((string)$this->session->userdata('admin_username'));
                $btnDelete  = ($unameLower === 'admin')
                  ? '<button type="button" class="btn btn-sm btn-danger"
                        data-id="'.$idInt.'" data-nama="'.$namaAttr.'" data-meja="'.$mejaAttr.'"
                        onclick="hapus_data_one(this)" title="Hapus"><i class="fa fa-trash"></i></button>'
                  : '';

                $actionsHtml = '<div class="btn-group btn-group-sm" role="group">'.$btnPaid.$btnResch.$btnCancel.$btnDelete.'</div>';



            // ===== Build row
                $row = [];
                $row['id']        = $idInt;
                $row['no']        = '';
            $row['kode']      = $kode_html;     // <-- sekarang bisa "Selesai Main"
            $row['meja']      = $meja_html;
            $row['waktu']     = $waktu_html;
            $row['durasi']    = $tgl_book_html;
            $row['harga']     = $harga_html;
            $row['grand']     = $grand_html;
            $row['status']    = $status_html;
            $row['metode']    = $metode_html;
            $row['aksi']      = $actionsHtml;

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

public function detail($id=null){
    $id = (int)$id;
    $row = $this->dm->get_order($id);

    if (!$row){
        return $this->output->set_content_type('application/json')
            ->set_output(json_encode([
                "success"=>false,
                "title"=>"Gagal",
                "pesan"=>"Data tidak ditemukan"
            ]));
    }

    $esc = fn($s)=>htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    $rp  = fn($n)=>'Rp '.number_format((int)$n,0,',','.');

    // ===== voucher fields (aman walau kolom belum ada) =====
    $voucher_code   = isset($row->voucher_code) ? trim((string)$row->voucher_code) : '';
    $voucher_jenis  = isset($row->voucher_jenis) ? trim((string)$row->voucher_jenis) : '';
    $voucher_disc   = isset($row->voucher_discount) ? (int)$row->voucher_discount : 0;
    $after_disc     = isset($row->subtotal_after_disc) ? (int)$row->subtotal_after_disc : null;

    // ===== RULE UI: disable kalau voucher sudah terpasang =====
    $hasVoucher  = ($voucher_code !== '');
    $disAttr     = $hasVoucher ? ' disabled' : '';
    $btnClass    = $hasVoucher ? 'btn btn-sm btn-secondary' : 'btn btn-sm btn-primary';
    $hintVoucher = $hasVoucher
        ? '<small class="text-muted d-block mt-2">Voucher sudah terpasang, tidak bisa diganti dari modal ini.</small>'
        : '<small class="text-muted d-block mt-2">Masukkan voucher lalu klik Terapkan. Validasi dilakukan oleh sistem.</small>';

    /* =========================
     * TABLE DETAIL
     * ========================= */
    $html = '<div class="table-responsive"><table class="table table-sm table-striped mb-0">';
    $add = function($k,$v) use (&$html){
        $html .= '<tr><th style="width:180px">'.$k.'</th><td>'.$v.'</td></tr>';
    };

    $add('Kode Booking', $esc($row->kode_booking ?: '-'));
    $add('Nama', $esc($row->nama ?: '-'));
    $add('No. HP', $esc($row->no_hp ?: '-'));
    $add('Meja', $esc($row->nama_meja ?: ('Meja #'.$row->meja_id)));

    $tgl = !empty($row->tanggal) ? date('d-m-Y', strtotime($row->tanggal)) : '-';
    $add('Tanggal', $esc($tgl));
    $add('Jam', $esc(($row->jam_mulai.' - '.$row->jam_selesai)));
    $add('Durasi', (int)$row->durasi_jam.' jam');
    $add('Harga/Jam', $rp($row->harga_per_jam));
    $add('Subtotal', $rp($row->subtotal));

    if ((int)$row->kode_unik > 0){
        $add('Kode Unik', number_format((int)$row->kode_unik,0,',','.'));
    }

    // ===== tampilkan info voucher jika ada =====
    if ($hasVoucher) {
        $add('Voucher', '<span class="badge badge-info">'.$esc($voucher_code).'</span>');
        if ($voucher_jenis !== '') $add('Jenis Voucher', $esc($voucher_jenis));
        if ($voucher_disc > 0)     $add('Potongan', '<span class="text-success"><b>'.$rp($voucher_disc).'</b></span>');
        if ($after_disc !== null)  $add('Subtotal Setelah Diskon', '<b>'.$rp($after_disc).'</b>');
    }

    $add('Grand Total', '<b>'.$rp($row->grand_total).'</b>');
    $add('Metode Bayar', $esc($row->metode_bayar ?: '-'));
    $add('Status', $esc($row->status ?: '-'));
    $add('Dibuat', $esc($row->created_at ?: '-'));
    if (!empty($row->updated_at)) $add('Diperbarui', $esc($row->updated_at));

    $html .= '</table></div>';

    /* =========================
     * FORM VOUCHER (SELALU TAMPIL, DISABLE JIKA SUDAH ADA)
     * ========================= */
    $html .= '<div class="mt-3">';
    $html .= '  <div class="card"><div class="card-body py-3">';
    $html .= '    <div class="d-flex justify-content-between align-items-center mb-2">';
    $html .= '      <div><b>Voucher</b><div class="text-muted small">NOMINAL / PERSEN</div></div>';
    $html .= '      '.($hasVoucher
                    ? '<span class="badge badge-info">Terpasang: '.$esc($voucher_code).'</span>'
                    : '<span class="badge badge-secondary">Belum ada</span>').'
               </div>';

    $html .= '    <div class="form-inline">';
    $html .= '      <input type="text"
                        class="form-control form-control-sm mr-2 dv-voucher-code"
                        placeholder="Masukkan kode voucher"
                        style="width:220px;text-transform:uppercase"
                        value="'.$esc($voucher_code).'"'.$disAttr.'>';
    $html .= '      <button type="button"
                        class="'.$btnClass.' btn-apply-voucher"
                        data-id="'.$id.'"'.$disAttr.'>Terapkan</button>';
    $html .= '    </div>';
    $html .=        $hintVoucher;
    $html .= '  </div></div>';
    $html .= '</div>';

    return $this->output->set_content_type('application/json')
        ->set_output(json_encode([
            "success"=>true,
            "title"=>'Detail #'.$row->kode_booking,
            "html"=>$html
        ]));
}

public function apply_voucher(){
  $id = (int)$this->input->post('id', true);
  $codeRaw = strtoupper(trim((string)$this->input->post('voucher', true)));
  $voucher_code = preg_replace('/[^A-Z0-9\-_]/', '', $codeRaw);

  if ($id < 1 || $voucher_code === ''){
    return $this->_out_json(["success"=>false,"title"=>"Validasi","pesan"=>"ID / kode voucher tidak valid."]);
  }

  $oldDebug = $this->db->db_debug;
  $this->db->db_debug = false;

  $this->db->trans_begin();

  // lock booking
  $qRow = $this->db->query("SELECT * FROM pesanan_billiard WHERE id_pesanan=? LIMIT 1 FOR UPDATE", [$id]);
  if (!$qRow){
    $err = $this->db->error();
    $this->db->trans_rollback();
    $this->db->db_debug = $oldDebug;
    return $this->_out_json(["success"=>false,"title"=>"DB Error","pesan"=>"Query booking gagal: ".$err['message']]);
  }

  $row = $qRow->row();
  if (!$row){
    $this->db->trans_rollback();
    $this->db->db_debug = $oldDebug;
    return $this->_out_json(["success"=>false,"title"=>"Tidak ditemukan","pesan"=>"Booking tidak ditemukan."]);
  }

  $st = strtolower((string)$row->status);

  // hanya tolak jika benar-benar tidak logis
  if (in_array($st, ['batal','free'], true)){
    $this->db->trans_rollback();
    $this->db->db_debug = $oldDebug;
    return $this->_out_json([
      "success"=>false,
      "title"=>"Ditolak",
      "pesan"=>"Voucher tidak bisa diterapkan pada booking dengan status ini."
    ]);
  }

  // jangan overwrite voucher yang sudah terpasang
  if ($this->db->field_exists('voucher_code','pesanan_billiard')){
    $existing = trim((string)($row->voucher_code ?? ''));
    if ($existing !== ''){
      $this->db->trans_rollback();
      $this->db->db_debug = $oldDebug;
      return $this->_out_json(["success"=>false,"title"=>"Ditolak","pesan"=>"Booking ini sudah memakai voucher: {$existing}."]);
    }
  }

  // normalisasi HP
  $no_hp = (string)($row->no_hp ?? '');
  $hp_norm62 = $this->_hp62($no_hp);
  if ($hp_norm62 === ''){
    $this->db->trans_rollback();
    $this->db->db_debug = $oldDebug;
    return $this->_out_json(["success"=>false,"title"=>"Validasi","pesan"=>"No HP booking kosong / tidak valid."]);
  }

  // lock voucher
  $qV = $this->db->query(
    "SELECT * FROM voucher_billiard
     WHERE kode_voucher = ?
       AND status = 'baru'
       AND is_claimed = 0
       AND no_hp_norm = ?
     LIMIT 1 FOR UPDATE",
    [$voucher_code, $hp_norm62]
  );

  if (!$qV){
    $err = $this->db->error();
    $this->db->trans_rollback();
    $this->db->db_debug = $oldDebug;
    return $this->_out_json(["success"=>false,"title"=>"DB Error","pesan"=>"Query voucher gagal: ".$err['message']]);
  }

  $v = $qV->row();
  if (!$v){
    $this->db->trans_rollback();
    $this->db->db_debug = $oldDebug;
    return $this->_out_json(["success"=>false,"title"=>"Voucher Invalid","pesan"=>"Kode voucher tidak ditemukan / sudah dipakai / bukan milik nomor ini."]);
  }

  // validasi periode berdasarkan tanggal booking
  $tanggal  = (string)($row->tanggal ?? '');
  $vMulai   = (string)($v->tgl_mulai ?? '');
  $vSelesai = (string)($v->tgl_selesai ?? '');
  if ($vMulai !== '' && $tanggal < $vMulai){
    $this->db->trans_rollback();
    $this->db->db_debug = $oldDebug;
    return $this->_out_json(["success"=>false,"title"=>"Voucher Belum Berlaku","pesan"=>"Voucher berlaku mulai {$vMulai}."]);
  }
  if ($vSelesai !== '' && $tanggal > $vSelesai){
    $this->db->trans_rollback();
    $this->db->db_debug = $oldDebug;
    return $this->_out_json(["success"=>false,"title"=>"Voucher Expired","pesan"=>"Voucher berlaku sampai {$vSelesai}."]);
  }

  $jenis = strtoupper((string)($v->jenis ?? ''));
  if (!in_array($jenis, ['NOMINAL','PERSEN'], true)){
    $this->db->trans_rollback();
    $this->db->db_debug = $oldDebug;
    return $this->_out_json([
      "success"=>false,
      "title"=>"Voucher Tidak Didukung",
      "pesan"=>"Voucher {$jenis} hanya bisa dipakai saat booking dibuat."
    ]);
  }

  // subtotal base
  $subtotal = (int)($row->subtotal ?? 0);
  if ($subtotal <= 0){
    $subtotal = ((int)($row->harga_per_jam ?? 0)) * ((int)($row->durasi_jam ?? 0));
  }
  if ($subtotal <= 0){
    $this->db->trans_rollback();
    $this->db->db_debug = $oldDebug;
    return $this->_out_json(["success"=>false,"title"=>"Error","pesan"=>"Subtotal 0, tidak bisa hitung diskon."]);
  }

  // minimal subtotal
  $minSub = (int)($v->minimal_subtotal ?? 0);
  if ($minSub > 0 && $subtotal < $minSub){
    $this->db->trans_rollback();
    $this->db->db_debug = $oldDebug;
    return $this->_out_json([
      "success"=>false,"title"=>"Minimal Belanja Belum Cukup",
      "pesan"=>"Minimal subtotal Rp ".number_format($minSub,0,',','.')."."
    ]);
  }

  // hitung diskon
  $discount = 0;
  if ($jenis === 'NOMINAL'){
    $nom = (int)($v->nilai ?? 0);
    if ($nom < 1){
      $this->db->trans_rollback();
      $this->db->db_debug = $oldDebug;
      return $this->_out_json(["success"=>false,"title"=>"Voucher Invalid","pesan"=>"Nilai voucher tidak valid."]);
    }
    $discount = min($subtotal, $nom);
  } else { // PERSEN
    $pct = (int)($v->nilai ?? 0);
    if ($pct < 1 || $pct > 100){
      $this->db->trans_rollback();
      $this->db->db_debug = $oldDebug;
      return $this->_out_json(["success"=>false,"title"=>"Voucher Invalid","pesan"=>"Persen voucher harus 1-100."]);
    }
    $discount = (int) floor(($subtotal * $pct) / 100);
    $maxP = (int)($v->max_potongan ?? 0);
    if ($maxP > 0) $discount = min($discount, $maxP);
    $discount = min($discount, $subtotal);
  }

  if ($discount < 1){
    $this->db->trans_rollback();
    $this->db->db_debug = $oldDebug;
    return $this->_out_json(["success"=>false,"title"=>"Voucher Tidak Efektif","pesan"=>"Diskon voucher menghasilkan 0."]);
  }

  $after = max(0, $subtotal - $discount);

  // === grand total & kode unik
  $kode_unik = (int)($row->kode_unik ?? 0);

  // kalau sudah lunas, kode_unik biasanya tidak relevan → nolkan
  if ($st === 'terkonfirmasi') $kode_unik = 0;

  if ($after <= 0){
    $kode_unik = 0;
    $grand = 0;
    $newStatus = 'free';
  } else {
    $grand = $after + $kode_unik;
    $newStatus = (string)$row->status;
  }

  $upd = [
    'grand_total' => $grand,
    'updated_at'  => date('Y-m-d H:i:s'),
  ];

  if ($after <= 0){
    $upd['status']    = $newStatus;
    $upd['kode_unik'] = 0;
  }

  if ($st === 'terkonfirmasi'){
    $upd['kode_unik'] = 0; // pastikan tersimpan nol
  }

  if ($this->db->field_exists('voucher_code','pesanan_billiard'))        $upd['voucher_code'] = $voucher_code;
  if ($this->db->field_exists('voucher_jenis','pesanan_billiard'))       $upd['voucher_jenis'] = $jenis;
  if ($this->db->field_exists('voucher_discount','pesanan_billiard'))    $upd['voucher_discount'] = $discount;
  if ($this->db->field_exists('subtotal_after_disc','pesanan_billiard')) $upd['subtotal_after_disc'] = $after;

  $okU = $this->db->where('id_pesanan', $id)->update('pesanan_billiard', $upd);
  if (!$okU){
    $err = $this->db->error();
    $this->db->trans_rollback();
    $this->db->db_debug = $oldDebug;
    return $this->_out_json(["success"=>false,"title"=>"DB Error","pesan"=>"Update booking gagal: ".$err['message']]);
  }

  // kalau sudah lunas → update snapshot billiard_paid juga (biar laporan sama)
  if ($st === 'terkonfirmasi'){
    $this->db->where('id_pesanan', $id)->update('billiard_paid', [
      'subtotal'    => $subtotal,
      'kode_unik'   => 0,
      'grand_total' => $grand,
      'paid_at'     => date('Y-m-d H:i:s'),
    ]);
    // kalau tabel/kolom tidak ada → db_debug=false jadi tidak fatal
  }

  // klaim voucher
  $note = 'Dipakai untuk booking ID '.$id.' (diskon Rp '.number_format($discount,0,',','.').')';
  $okV = $this->db->where('id_voucher', (int)$v->id_voucher)->update('voucher_billiard', [
    'status'     => 'accept',
    'is_claimed' => 1,
    'claimed_at' => date('Y-m-d H:i:s'),
    'notes'      => $note,
  ]);
  if (!$okV){
    $err = $this->db->error();
    $this->db->trans_rollback();
    $this->db->db_debug = $oldDebug;
    return $this->_out_json(["success"=>false,"title"=>"DB Error","pesan"=>"Update voucher gagal: ".$err['message']]);
  }

  if ($this->db->trans_status() === FALSE){
    $this->db->trans_rollback();
    $this->db->db_debug = $oldDebug;
    return $this->_out_json(["success"=>false,"title"=>"Gagal","pesan"=>"Transaksi gagal."]);
  }

  $this->db->trans_commit();
  $this->db->db_debug = $oldDebug;

  return $this->_out_json([
    "success"=>true,
    "title"=>"Voucher Diterapkan",
    "pesan"=>"Diskon Rp ".number_format($discount,0,',','.')." berhasil diterapkan.",
    "grand_total"=>$grand,
    "after"=>$after
  ]);
}


private function _out_json(array $arr){
  return $this->output
    ->set_content_type('application/json')
    ->set_output(json_encode($arr));
}

private function _hp62(string $hp): string {
  $hp = preg_replace('/\D+/', '', $hp);
  if ($hp === '') return '';
  if (strpos($hp, '62') === 0) return $hp;
  if (strpos($hp, '0') === 0)  return '62'.substr($hp, 1);
  // fallback: anggap sudah benar
  return $hp;
}

    /** Tandai KONFIRM (paid) */
    public function mark_paid(){
    $ids = $this->input->post('id');
    if (!is_array($ids) || !count($ids)){
        echo json_encode([
            "success"=>false,
            "title"=>"Gagal",
            "pesan"=>"Tidak ada data dipilih"
        ]);
        return;
    }

    // proses konfirmasi bayar di model
    $res = $this->dm->bulk_mark_confirmed($ids);
        // Pastikan bulk_mark_confirmed() di model kamu nge-return minimal:
        // [
        //   'ok_count'      => 2,
        //   'ok_ids'        => [15,18],
        //   'blocked_ids'   => [...],
        //   'already_ids'   => [...],
        //   'notfound_ids'  => [...],
        //   'copied_count'  => 2,
        //   'copied_skipped'=> [...],
        //   'errors'        => []
        // ]

        if (!empty($res['ok_count'])) {
            $this->purge_public_caches();
        }

        // Kirim WA hanya utk booking yang bener-bener sukses dikonfirmasi barusan
        $wa_logs = [];
        if (!empty($res['ok_ids']) && is_array($res['ok_ids'])) {
            foreach ($res['ok_ids'] as $bid) {
                $wa_logs[] = "#{$bid}: ".$this->_notify_paid_whatsapp($bid);
            }
        }

        // susun pesan response buat frontend
        $msgs = [];
        if (!empty($res['ok_count']))       $msgs[] = $res['ok_count']." booking dikonfirmasi.";
        if (!empty($res['blocked_ids']))    $msgs[] = "Ditolak (metode bayar belum di-set): #".implode(', #', $res['blocked_ids']);
        if (!empty($res['already_ids']))    $msgs[] = "Diabaikan (sudah terkonfirmasi/batal): #".implode(', #', $res['already_ids']);
        if (!empty($res['notfound_ids']))   $msgs[] = "Tidak ditemukan: #".implode(', #', $res['notfound_ids']);
        if (!empty($res['copied_count']))   $msgs[] = "Disalin ke tabel paid: ".$res['copied_count']." baris.";
        if (!empty($res['copied_skipped'])) $msgs[] = "Lewati salin (sudah ada): #".implode(', #', $res['copied_skipped']);

        if (!empty($wa_logs)) {
            $msgs[] = "WA: ".implode(' ; ', $wa_logs);
        }

        $ok = !empty($res['ok_count']) && empty($res['errors']);

        echo json_encode([
            "success"=>$ok,
            "title"=>$ok?"Berhasil":"Sebagian/Gagal",
            "pesan"=>implode(' ', $msgs) ?: 'Tidak ada yang diproses.'
        ]);
    }
private function _pretty_hp(string $hp): string {
  $d = preg_replace('/\D+/', '', $hp);
  if ($d === '') return '';
  if (strpos($d, '62') === 0) return '0' . substr($d, 2);
  return (strpos($d, '0') === 0) ? $d : $d;
}

    private function _notify_paid_whatsapp($booking_id)
{
    try {
        // 1. Ambil data pesanan setelah mark_paid
        $rec = $this->dm->get_order((int)$booking_id);
        if (!$rec) {
            return "data tidak ditemukan";
        }

        // 2. Ambil nomor hp asli dari pesanan
        $hp_tujuan = $rec->no_hp ?? '';
        if ($hp_tujuan === '') {
            return "no hp kosong";
        }

        // 3. Nama meja (pakai snapshot kalau ada, kalau gak fallback ke tabel master)
        $meja_nama = isset($rec->nama_meja) && $rec->nama_meja !== ''
            ? $rec->nama_meja
            : ($this->db->select('nama_meja')
                        ->get_where('meja_billiard', ['id_meja' => $rec->meja_id])
                        ->row('nama_meja')
               ?: ('MEJA #'.($rec->meja_id ?? '')));

        // 4. Info brand toko
        $web  = $this->om->web_me();
        $site = $web->nama_website ?? 'Sistem';

        // 5. Angka uang
        $subtotal   = (int)($rec->subtotal ?? 0);
        $kode_unik  = (int)($rec->kode_unik ?? 0);
        $grand      = (int)($rec->grand_total ?? ($subtotal + $kode_unik));
        $isFree     = ($grand === 0);

        // 6. Link tiket/detail booking
        $link = $isFree
            ? (site_url('billiard/free') . '?t=' . urlencode($rec->access_token ?? ''))
            : (site_url('billiard/cart') . '?t=' . urlencode($rec->access_token ?? ''));

        // 7. Format tanggal & jam
        $tgl_label = (function($tgl){
            if (function_exists('hari') && function_exists('tgl_view')) {
                return hari($tgl).", ".tgl_view($tgl);
            }
            return $tgl ?: '-';
        })($rec->tanggal ?? '');

        $jamMulai   = substr($rec->jam_mulai   ?? '00:00:00',0,5);
        $jamSelesai = substr($rec->jam_selesai ?? '00:00:00',0,5);

        // 8. Judul header WA setelah dibayar
        $judul_header = $isFree
            ? 'Booking Gratis Dikonfirmasi'
            : 'Pembayaran Diterima';

        // 9. Susun isi pesan WA (gaya yang sudah kita pakai)
        $lines = [];

        // HEADER
        $lines[] = "🎱 *{$judul_header} — {$site}*";
        $lines[] = "--------------------------------";
        $lines[] = "Terima kasih, booking Anda sudah aktif. 🙌";
        $lines[] = "";

        // DETAIL BOOKING
        // $lines[] = "📄 *Kode Booking:* " . ($rec->kode_booking ?? '-');
        $lines[] = "🙍 *Nama:* " . ($rec->nama ?? '-');
        $lines[] = "📞 *HP:* "   . ($this->_pretty_hp($rec->no_hp ?? ''));
        $lines[] = "🪑 *Meja:* " . $meja_nama;
        $lines[] = "📅 *Tanggal:* " . $tgl_label;
        $lines[] = "⏰ *Jam:* " . $jamMulai . "–" . $jamSelesai;
        $lines[] = "⏳ *Durasi:* " . ($rec->durasi_jam ?? '-') . " Jam";
        $lines[] = "";

        // // TARIF & BIAYA
        // $lines[] = "💸 *Tarif / Jam:* Rp" . number_format((int)($rec->harga_per_jam ?? 0),0,',','.');
        // $lines[] = "🔢 *Kode Unik:* Rp" . number_format($kode_unik,0,',','.');
        // $lines[] = "🧮 *Subtotal:* Rp"  . number_format($subtotal,0,',','.');

        // if ($isFree) {
        //     $lines[] = "✅ *Total Bayar:* Rp0";
        //     $lines[] = "_(Voucher / free play)_";
        // } else {
        //     $lines[] = "💳 *Total Bayar:* Rp" . number_format($grand,0,',','.');
        // }
        // $lines[] = "";

        // LINK TIKET / DETAIL BOOKING
        $lines[] = "🎟 *Tiket / Detail Booking:*";
        $lines[] = $link;
        $lines[] = "Saat datang, tunjukkan Tiket / Detail Booking ke kasir sebelum mulai main.";
        $lines[] = "";
        $lines[] = "💾 Simpan kontak ini supaya link bisa diklik.";
        $lines[] = "";

        // INSTRUKSI KASIR
        // FOOTER
        $lines[] = "📣 _Pesan ini dikirim otomatis oleh sistem {$site}._";

        $pesan = implode("\n", $lines);

        // 10. Kirim WA via function kamu
        // send_wa_single() di tempatmu SUDAH handle normalisasi tujuan,
        // jadi kita kirim raw $rec->no_hp saja.
        $resSend = send_wa_single($hp_tujuan, $pesan);

        // logging buat debugging, tapi tidak ganggu flow
        if (is_string($resSend)) {
            log_message('debug', 'WA konfirmasi paid -> '.$hp_tujuan.' hasil: '.$resSend);
        } else {
            log_message('debug', 'WA konfirmasi paid -> '.$hp_tujuan.' hasil: '.json_encode($resSend));
        }

        return "WA ok ".$hp_tujuan;

    } catch (Throwable $e) {
        log_message('error', 'WA konfirmasi paid error: '.$e->getMessage().' trace: '.$e->getTraceAsString());
        return "WA gagal (".$e->getMessage().")";
    }
}

    /** Batalkan */
    public function mark_canceled(){
    $ids = $this->input->post('id');
    if (!is_array($ids) || !count($ids)){
        echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Tidak ada data dipilih"]); return;
    }

    $res = $this->dm->bulk_mark_canceled($ids);

    $msgs = [];
    if (!empty($res['ok_count']))          $msgs[] = $res['ok_count']." booking dibatalkan.";
    if (!empty($res['paid_deleted']))      $msgs[] = "Snapshot paid dihapus: ".$res['paid_deleted']." baris.";
    if (!empty($res['voucher_unclaimed'])) $msgs[] = "Voucher dibalikin: ".$res['voucher_unclaimed']." item.";
    if (!empty($res['notfound_ids']))      $msgs[] = "Tidak ditemukan: #".implode(', #', $res['notfound_ids']);
    if (!empty($res['errors']))            $msgs[] = "Error: #".implode(', #', $res['errors']);

    $ok = !empty($res['ok_count']) && empty($res['errors']);
    if ($ok) $this->purge_public_caches();

    echo json_encode([
        "success"=>$ok,
        "title"=>$ok?"Berhasil":"Sebagian/Gagal",
        "pesan"=> $msgs ? implode(' ', $msgs) : 'Tidak ada yang diproses.'
    ]);
}



    /** Hapus */
    public function hapus_data(){
        $ids = $this->input->post('id');
        if (!is_array($ids) || !count($ids)){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Tidak ada data"]); return;
        }
        $res = $this->dm->bulk_delete($ids);

        $msgs = [];
        if (!empty($res['ok_count']))        $msgs[] = $res['ok_count']." data dihapus.";
        if (!empty($res['paid_deleted']))    $msgs[] = "Snapshot paid dihapus: ".$res['paid_deleted']." baris.";
        if (!empty($res['confirmed_ids']))   $msgs[] = "Ditolak (status terkonfirmasi): #".implode(', #', $res['confirmed_ids']);
        if (!empty($res['notfound_ids']))    $msgs[] = "Tidak ditemukan: #".implode(', #', $res['notfound_ids']);
        if (!empty($res['errors']))          $msgs[] = "Gagal: #".implode(', #', $res['errors']);


        $ok = !empty($res['ok_count']) && empty($res['errors']);
        if ($ok) $this->purge_public_caches();

        echo json_encode([
            "success"=>$ok,
            "title"=>$ok?"Berhasil":"Sebagian/Gagal",
            "pesan"=>implode(' ', $msgs) ?: 'Tidak ada yang diproses.'
        ]);
    }

    /** Reschedule (ubah tanggal main & jam mulai) + tolak jika bentrok */
public function reschedule(){
    $this->output->set_content_type('application/json');
    try{
        $id        = (int)$this->input->post('id');
        $tanggal   = trim((string)$this->input->post('tanggal'));
        $jam_mulai = trim((string)$this->input->post('jam_mulai'));

        if ($id <= 0 || $tanggal === '' || $jam_mulai === ''){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"ID, tanggal, dan jam mulai wajib diisi."]); return;
        }

        // Ambil data booking saat ini
        $row = $this->dm->get_order($id);
        if (!$row){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Booking #$id tidak ditemukan."]); return;
        }

        // Durasi & normalisasi jam
        $durasi = (int)$row->durasi_jam; if ($durasi <= 0) $durasi = 1;
        if (strlen($jam_mulai) === 5) $jam_mulai .= ':00';

        $startTs = strtotime($tanggal.' '.$jam_mulai);
        if (!$startTs){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Format tanggal/jam tidak valid."]); return;
        }
        $endTs = strtotime('+'.$durasi.' hours', $startTs);
        $jam_selesai = date('H:i:s', $endTs);

        // Tolak jika status batal
        $st = strtolower((string)$row->status);
        if ($st === 'batal'){
            echo json_encode(["success"=>false,"title"=>"Ditolak","pesan"=>"Tidak bisa reschedule: status sudah dibatalkan."]); return;
        }

        // Cek bentrok slot
        $cek = $this->dm->check_slot_conflict((int)$row->meja_id, $tanggal, $jam_mulai, $durasi, $id);
        if (!empty($cek['conflict'])){
            $ids = !empty($cek['ids']) ? (' #'.implode(', #',$cek['ids'])) : '';
            echo json_encode([
                "success"=>false,
                "title"=>"Slot Bentrok",
                "pesan"=>"Jadwal berbenturan dengan booking lain".$ids.". Silakan pilih waktu lain."
            ]);
            return;
        }

        // Update pesanan_billiard
        $ok = $this->dm->update_schedule($id, $tanggal, $jam_mulai, $jam_selesai);
        if (!$ok){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Tidak bisa menyimpan reschedule."]); return;
        }

        // Update snapshot di billiard_paid (jika ada)
        $paid_aff = $this->dm->update_paid_schedule($id, $tanggal, $jam_mulai, $jam_selesai);

        // bersihkan cache publik
        $this->purge_public_caches();

        // Info untuk pesan
        $nm        = trim((string)($row->nama ?? '')) ?: '-';
        $meja_nama = isset($row->nama_meja) && $row->nama_meja !== ''
            ? $row->nama_meja
            : ($this->db->select('nama_meja')->get_where('meja_billiard', ['id_meja' => $row->meja_id])->row('nama_meja')
               ?: ('MEJA #'.($row->meja_id ?? '')));

        echo json_encode([
            "success"=>true,
            "title"=>"Berhasil",
            "pesan"=>"{$nm} — {$meja_nama} dijadwalkan ke {$tanggal}, {$jam_mulai}–{$jam_selesai} (durasi {$durasi} jam)."
                    .($paid_aff ? " Snapshot paid ikut diperbarui." : "")
        ]);
    }catch(\Throwable $e){
        echo json_encode(["success"=>false,"title"=>"Error","pesan"=>$e->getMessage()]);
    }
}

    
    /** Ping untuk notifikasi ringan */
    public function ping(){
        $this->output
             ->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0')
             ->set_header('Pragma: no-cache');

        try{
            $s = $this->dm->get_stats();
            return $this->output->set_content_type('application/json')->set_output(json_encode([
                'success' => true,
                'total'   => (int)$s->total,
                'max_id'  => (int)$s->max_id,
                'last_ts' => $s->last_ts ? date('c', strtotime($s->last_ts)) : null,
            ]));
        }catch(\Throwable $e){
            return $this->output->set_content_type('application/json')->set_output(json_encode([
                'success'=>false,'error'=>$e->getMessage()
            ]));
        }
    }
}
