<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Billiard extends MX_Controller {

  public function __construct(){
    parent::__construct();
    date_default_timezone_set('Asia/Makassar');

    // pastikan library & helper tersedia
    $this->load->library('form_validation');
    // $this->load->helper(['url','form']);
    $this->load->helper('front');


    // models
    $this->load->model('front_model','fm');    // untuk web_me()
    $this->load->model('M_billiard','mbi');
  }

  /* ========= Halaman Form ========= */
  public function index(){
    $pre = (int)$this->input->get('meja_id');
    $data = [
      "controller" => get_class($this),
      "title"      => "Booking Billiard",
      "deskripsi"  => "Pilih meja, tanggal & jam, lalu konfirmasi Bookingan.",
      "prev"       => base_url("assets/images/billiard.webp"),
      "mejas"      => $this->mbi->get_mejas_aktif(),
      "rec"        => $this->fm->web_me(),
      "selected_meja_id" => $pre,
    ];
    $this->load->view('billiard/form_billiard', $data);
  }




  /* ========= Pricing & Slot Validation (Weekday/Weekend configurable) ========= */

/** Build DateTime dari tanggal & jam (terima H:i atau H:i:s) */
private function _mk_dt2(string $d, string $t): ?DateTime {
  $tz = new DateTimeZone(date_default_timezone_get());
  $dt = DateTime::createFromFormat('Y-m-d H:i:s', $d.' '.$t, $tz);
  if ($dt !== false) return $dt;
  $dt2 = DateTime::createFromFormat('Y-m-d H:i', $d.' '.substr($t,0,5), $tz);
  return $dt2 !== false ? $dt2 : null;
}

/**
 * Hitung tarif & validasi slot terhadap jendela harga yang tersimpan di meja_billiard.
 * Aturan:
 * - Senin–Jumat (N=1..5): pakai kolom wk_* (day / night)
 * - Sabtu–Minggu (N=6..7): pakai kolom we_* (day / night)
 * - Slot harus sepenuhnya berada di salah satu jendela (day atau night). Tidak boleh nyebrang dua jendela.
 * - Menghormati overnight (mis. 18:00 → 02:00 H+1).
 *
 * Return:
 *   ['ok'=>bool, 'rate'=>int, 'band'=>'day|night', 'msg'=>string]
 */
private function _rate_for_slot_cfg(string $date, string $jam_mulai, string $jam_selesai, $meja): array {
  $start = $this->_mk_dt2($date, $jam_mulai);
  $end   = $this->_mk_dt2($date, $jam_selesai);
  if (!$start || !$end) {
    return ['ok'=>false,'rate'=>0,'band'=>'','msg'=>'Waktu tidak valid.'];
  }
  if ($end <= $start) $end->modify('+1 day');

  // Tentukan hari: 1..7 (Mon..Sun)
  $N = (int)DateTime::createFromFormat('Y-m-d', $date)->format('N');

  if ($N >= 1 && $N <= 5) {
    // WEEKDAY windows
    $dS = $meja->wk_day_start   ?? '09:00:00';
    $dE = $meja->wk_day_end     ?? '18:00:00';
    $dR = (int)($meja->wk_day_rate ?? 35000);

    $nS = $meja->wk_night_start ?? '18:00:00';
    $nE = $meja->wk_night_end   ?? '02:00:00';
    $nR = (int)($meja->wk_night_rate ?? 40000);

  } else {
    // WEEKEND windows
    $dS = $meja->we_day_start   ?? '10:00:00';
    $dE = $meja->we_day_end     ?? '18:00:00';
    $dR = (int)($meja->we_day_rate ?? 40000);

    $nS = $meja->we_night_start ?? '18:00:00';
    $nE = $meja->we_night_end   ?? '02:00:00';
    $nR = (int)($meja->we_night_rate ?? 50000);
  }

  // Build window Day/Night utk tanggal anchor $date
$w1s = $this->_mk_dt2($date, $dS);
$w1e = $this->_mk_dt2($date, $dE);
$w1Over = ($w1e <= $w1s);
if ($w1Over) $w1e->modify('+1 day');

$w2s = $this->_mk_dt2($date, $nS);
$w2e = $this->_mk_dt2($date, $nE);
$w2Over = ($w2e <= $w2s);
if ($w2Over) $w2e->modify('+1 day');

// Cek langsung pada anchor tanggal
$inW1 = ($start >= $w1s && $end <= $w1e);
$inW2 = ($start >= $w2s && $end <= $w2e);

// Tambahan: jika overnight dan start dinihari (sebelum start window),
// geser start/end +1 hari agar ikut bagian "H+1" window
if (!$inW1 && $w1Over && $start < $w1s){
  $start2 = (clone $start)->modify('+1 day');
  $end2   = (clone $end)->modify('+1 day');
  $inW1 = ($start2 >= $w1s && $end2 <= $w1e);
}
if (!$inW2 && $w2Over && $start < $w2s){
  $start2 = (clone $start)->modify('+1 day');
  $end2   = (clone $end)->modify('+1 day');
  $inW2 = ($start2 >= $w2s && $end2 <= $w2e);
}

if ($inW1) return ['ok'=>true,'rate'=>$dR,'band'=>'day','msg'=>'Tarif Day window'];
if ($inW2) return ['ok'=>true,'rate'=>$nR,'band'=>'night','msg'=>'Tarif Night window'];

// fallback pesan jika tetap tidak masuk
$msg = sprintf(
  'Slot harus di dalam salah satu jendela: %s–%s atau %s–%s.',
  substr($dS,0,5), substr($dE,0,5), substr($nS,0,5), substr($nE,0,5)
);
return ['ok'=>false,'rate'=>0,'band'=>'','msg'=>$msg];

}


  /* ========= Helper: normalisasi HH:MM ========= */
  // Contoh input di-handle: "21.00", "21 00", "21：00", "21-00", "2100", "9:5"
  private function _normalize_hhmm($val) {
    preg_match_all('/\d+/', (string)$val, $m);
    if (empty($m[0])) return null;

    $digits = implode('', $m[0]);

    if (count($m[0]) >= 2) {
      $H = (int)$m[0][0];
      $I = (int)$m[0][1];
    } else {
      if (strlen($digits) >= 3) {
        $H = (int)substr($digits, 0, -2);
        $I = (int)substr($digits, -2);
      } else {
        $H = (int)$digits;
        $I = 0;
      }
    }

    if ($H < 0 || $H > 23) return null;
    if ($I < 0 || $I > 59) return null;

    return sprintf('%02d:%02d', $H, $I);
  }

  private function _valid_ymd($s){
    $dt = DateTime::createFromFormat('Y-m-d', (string)$s);
    return $dt && $dt->format('Y-m-d') === $s;
  }

// Di dalam class Billiard (controller yang sama)
public function daftar_booking(){
  $this->_nocache_headers();

  $tz  = new DateTimeZone(date_default_timezone_get());
  $now = new DateTime('now', $tz);

  $web      = $this->fm->web_me();
  $maxDays  = (int)($web->maks_hari_booking ?? 30);
  if ($maxDays < 0) $maxDays = 0;

  $today     = (clone $now)->setTime(0,0,0);
  $yesterday = (clone $today)->modify('-1 day')->format('Y-m-d');             // cover overnight
  $upperDate = (clone $today)->modify('+'.$maxDays.' day')->format('Y-m-d');  // batas atas

  // Ambil booking terkonfirmasi
  $rows = $this->db->select('id_pesanan, meja_id, nama_meja,no_hp ,tanggal, jam_mulai, jam_selesai, durasi_jam, nama')
    ->from('pesanan_billiard')
    ->where('status','terkonfirmasi')
    ->where('tanggal >=', $yesterday)
    ->where('tanggal <=', $upperDate)
    ->order_by('meja_id','ASC')
    ->order_by('tanggal','ASC')
    ->order_by('jam_mulai','ASC')
    ->get()->result();

  // helper DateTime
  $mk_dt = function(string $d, string $t) {
    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $d.' '.$t);
    if ($dt !== false) return $dt;
    $dt2 = DateTime::createFromFormat('Y-m-d H:i', $d.' '.substr($t,0,5));
    return $dt2 !== false ? $dt2 : null;
  };

  // Group: meja -> days -> bookings (skip yang sudah selesai)
  $cards_by_meja = []; // [meja_id => ['nama_meja'=>..., 'days'=>[Y-m-d=>['date'=>..., 'bookings'=>[]]], 'booking_count'=>int, 'next_ts'=>int]]

  foreach ($rows as $r){
    $start = $mk_dt($r->tanggal, $r->jam_mulai);
    $end   = $mk_dt($r->tanggal, $r->jam_selesai);
    if (!$start || !$end) continue;
    if ($end <= $start) { $end->modify('+1 day'); } // overnight
    if ($end <= $now) continue; // sudah lewat

    $mejaId = (int)$r->meja_id;
    if (!isset($cards_by_meja[$mejaId])){
      $cards_by_meja[$mejaId] = [
        'meja_id'       => $mejaId,
        'nama_meja'     => ($r->nama_meja ?: 'MEJA #'.$mejaId),
        'days'          => [],
        'all_bookings'  => [],
      ];
    }

    $cards_by_meja[$mejaId]['all_bookings'][] = $start->getTimestamp()*1000;

    $dkey = $r->tanggal;
    if (!isset($cards_by_meja[$mejaId]['days'][$dkey])){
      // label tanggal
      $epoch = strtotime($dkey);
      $monMap = ['Jan'=>'JAN','Feb'=>'FEB','Mar'=>'MAR','Apr'=>'APR','May'=>'MEI','Jun'=>'JUN','Jul'=>'JUL','Aug'=>'AGU','Sep'=>'SEP','Oct'=>'OKT','Nov'=>'NOV','Dec'=>'DES'];
      $monEn  = date('M',$epoch);
      $hariMap= ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];

      $cards_by_meja[$mejaId]['days'][$dkey] = [
        'date'        => $dkey,
        'tanggal_fmt' => date('d M Y',$epoch),
        'mon'         => $monMap[$monEn] ?? strtoupper($monEn),
        'daynum'      => date('j',$epoch),
        'weekday'     => $hariMap[(int)date('w',$epoch)],
        'bookings'    => [],
      ];
    }

    $cards_by_meja[$mejaId]['days'][$dkey]['bookings'][] = [
      'id'          => (int)$r->id_pesanan,
      'nama'        => (string)($r->nama ?? 'Booking'),
      'tanggal'     => $r->tanggal,
      'no_hp'     => $r->no_hp,
      'jam_mulai'   => substr($r->jam_mulai,0,5),
      'jam_selesai' => substr($r->jam_selesai,0,5),
      'durasi_jam'  => (int)$r->durasi_jam,
      'start_ts'    => $start->getTimestamp()*1000,
      'end_ts'      => $end->getTimestamp()*1000,
    ];
  }

  // Bentuk $cards final: sort bookings per day, sort day, sort card
  $cards = [];
  foreach ($cards_by_meja as $meja){
    // sort hari dan bookings
    ksort($meja['days']);
    foreach ($meja['days'] as &$day){
      usort($day['bookings'], fn($a,$b)=> $a['start_ts'] <=> $b['start_ts']);
    }
    unset($day);

    $booking_count = 0;
    foreach ($meja['days'] as $d){ $booking_count += count($d['bookings']); }

    $next_ts = !empty($meja['all_bookings']) ? min($meja['all_bookings']) : PHP_INT_MAX;

    $cards[] = [
      'meja_id'       => $meja['meja_id'],
      'nama_meja'     => $meja['nama_meja'],
      'days'          => array_values($meja['days']),
      'booking_count' => $booking_count,
      'next_ts'       => $next_ts,
    ];
  }

  usort($cards, fn($a,$b)=> $a['next_ts'] <=> $b['next_ts']);

  $data = [
    "controller" => get_class($this),
    "title"      => "Daftar Bookingan Billiard",
    "deskripsi"  => "Booking mendatang per-meja, dikelompok per tanggal.",
    "prev"       => base_url("assets/images/billiard.webp"),
    "rec"        => $web,
    "cards"      => $cards,
  ];

  $this->load->view('billiard/daftar_booking', $data);
}


  public function add(){
  // ambil input & normalisasi
  $in = $this->input->post(NULL, TRUE) ?: [];

  // ==== ambil voucher dari form (nama field fleksibel: voucher / voucher_code) ====
  $voucher_code_raw = strtoupper(trim((string)($in['voucher'] ?? $in['voucher_code'] ?? '')));
  // sanitasi ringan: hanya A-Z, 0-9, dash & underscore
  $voucher_code = preg_replace('/[^A-Z0-9\-\_]/', '', $voucher_code_raw);

  $norm = $this->_normalize_hhmm($in['jam_mulai'] ?? '');
  if ($norm !== null) $in['jam_mulai'] = $norm;

  // injeksikan ke form_validation untuk cek dasar
  $this->form_validation->set_data($in);
  $this->_rules();
  if ($this->form_validation->run() === FALSE){
    return $this->_json(["success"=>false,"title"=>"Validasi Gagal","pesan"=>validation_errors()]);
  }

  // validasi manual tanggal + batas hari booking
  $tanggal = trim((string)($in['tanggal'] ?? ''));
  if (!$this->_valid_ymd($tanggal)){
    return $this->_json(["success"=>false,"title"=>"Validasi Gagal","pesan"=>"<br> Tanggal harus format YYYY-MM-DD. "]);
  }
  $web     = $this->fm->web_me();
  $maxDays = (int)($web->maks_hari_booking ?? 30);
  if ($maxDays < 0) $maxDays = 0;
  $tzId = $web->timezone ?? 'Asia/Makassar';
  try { $tz = new DateTimeZone($tzId); } catch (\Throwable $e) { $tz = new DateTimeZone('Asia/Makassar'); $tzId='Asia/Makassar'; }
  $today   = new DateTime('today', $tz);
  $maxDate = (clone $today)->modify("+{$maxDays} days")->setTime(23,59,59);
  $reqDate = DateTime::createFromFormat('Y-m-d', $tanggal, $tz);
  if (!$reqDate) {
    return $this->_json(["success"=>false,"title"=>"Validasi Gagal","pesan"=>"Tanggal tidak valid."]);
  }
  $reqDate->setTime(0,0,0);
  if ($reqDate < $today){
    return $this->_json(["success"=>false,"title"=>"Batas Waktu Booking","pesan"=>"Tanggal booking tidak boleh sebelum hari ini (".$today->format('Y-m-d')." ".$tzId.")."]);
  }
  if ($reqDate > $maxDate){
    $msg = "Tanggal booking maksimal {$maxDays} hari dari sekarang (s/d ".$maxDate->format('Y-m-d')." ".$tzId.").";
    return $this->_json(["success"=>false,"title"=>"Batas Waktu Booking","pesan"=>$msg]);
  }

  // validasi no_hp & jam
  $no_hp = preg_replace('/\D+/', '', (string)($in['no_hp'] ?? ''));
  if (!preg_match('/^\d{10,13}$/', $no_hp)){
    return $this->_json(["success"=>false,"title"=>"Validasi Gagal","pesan"=>"<br> Nomor HP harus 10–13 digit. "]);
  }
  $jam_hm = (string)($in['jam_mulai'] ?? '');
  if (!preg_match('/^(?:[01]?\d|2[0-3]):[0-5]\d$/', $jam_hm)){
    return $this->_json(["success"=>false,"title"=>"Validasi Gagal","pesan"=>"<br> Jam Mulai harus format HH:MM (24 jam). "]);
  }

  // siap pakai
  $meja_id    = (int)$in['meja_id'];
  $nama       = trim((string)$in['nama']);
  $durasi     = max(1, min(12, (int)$in['durasi_jam'])); // default dari user
  $jam_mulai  = $jam_hm . ':00';
  $jam_selesai= $this->_add_hours($jam_mulai, $durasi);

  $meja = $this->mbi->get_meja($meja_id);
  if (!$meja) return $this->_json(["success"=>false,"title"=>"Tidak Valid","pesan"=>"Meja tidak ditemukan."]);

  // cek operasional (support overnight) — pakai tanggal booking sebagai anchor
  if (!$this->_within_open_hours($jam_mulai, $jam_selesai, $meja->jam_buka, $meja->jam_tutup, $tanggal)){
    return $this->_json(["success"=>false,"title"=>"Di Luar Operasional","pesan"=>"Jam operasional meja: ".substr($meja->jam_buka,0,5)."–".substr($meja->jam_tutup,0,5)."." ]);
  }
  // === VALIDASI & TARIF MENGIKUTI KONFIG MEJA (weekday/weekend) ===
$rateInfo = $this->_rate_for_slot_cfg($tanggal, $jam_mulai, $jam_selesai, $meja);
if (!$rateInfo['ok']) {
  return $this->_json([
    "success"=>false,
    "title"=>"Di Luar Aturan Jam",
    "pesan"=>$rateInfo['msg']
  ]);
}
$harga = (int)$rateInfo['rate']; // tarif efektif untuk subtotal & penyimpanan


  // --- cek overlap dengan transaksi + auto-expire draft non-cash ---
  $date = $tanggal;
  $prev_date = (new DateTime($date))->modify('-1 day')->format('Y-m-d');
  $next_date = (new DateTime($date))->modify('+1 day')->format('Y-m-d');

  $mk = function(string $d, string $t){
    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $d.' '.$t);
    if ($dt !== false) return $dt;
    return DateTime::createFromFormat('Y-m-d H:i', $d.' '.substr($t,0,5));
  };
  $aStart = $mk($date, $jam_mulai);
  $aEnd   = $mk($date, $jam_selesai);
  if (!$aStart || !$aEnd) return $this->_json(["success"=>false,"title"=>"Error","pesan"=>"Waktu tidak valid."]);
  if ($aEnd <= $aStart) $aEnd->modify('+1 day');

  // === TRANSACTION START ===
  $this->db->trans_begin();

  // lock slot aktif
  $active = ['draft','verifikasi','terkonfirmasi','free'];
  $sql = "SELECT id_pesanan, tanggal, jam_mulai, jam_selesai, nama, status, updated_at, metode_bayar
          FROM pesanan_billiard
          WHERE meja_id = ?
            AND (tanggal IN (?, ?, ?))
            AND status IN (".str_repeat('?,', count($active)-1)."?)
          FOR UPDATE";
  $params = array_merge([$meja_id, $prev_date, $date, $next_date], $active);
  $rows = $this->db->query($sql, $params)->result();

  // bersihkan draft non-cash yang expired
  $late_min = $this->_late_min();
  $now      = time();
  $others   = [];
  foreach ($rows as $o) {
    $st = strtolower((string)$o->status);
    $mb = strtolower((string)($o->metode_bayar ?? ''));
    if (in_array($st, ['draft'], true) && $mb !== 'cash') {
      $upd = is_numeric($o->updated_at) ? (int)$o->updated_at : (@strtotime((string)$o->updated_at) ?: $now);
      $deadline = $upd + $late_min * 60;
      if ($deadline <= $now) {
        $this->db->where('id_pesanan', (int)$o->id_pesanan)->update('pesanan_billiard', [
          'status'     => 'batal',
          'updated_at' => date('Y-m-d H:i:s'),
        ]);
        continue;
      }
    }
    $others[] = $o;
  }

  // cek konflik (untuk durasi awal dari user)
  foreach($others as $o){
    $bStart = $mk($o->tanggal, $o->jam_mulai);
    $bEnd   = $mk($o->tanggal, $o->jam_selesai);
    if (!$bStart || !$bEnd) continue;
    if ($bEnd <= $bStart) $bEnd->modify('+1 day');
    $overlapStart = ($aStart > $bStart) ? $aStart : $bStart;
    $overlapEnd   = ($aEnd < $bEnd) ? $aEnd : $bEnd;
    if ($overlapEnd > $overlapStart){
      $this->db->trans_rollback();
      $jam = substr($o->jam_mulai,0,5).'–'.substr($o->jam_selesai,0,5);
      return $this->_json([
        "success"=>false,
        "title"=>"Slot Bentrok",
        "pesan"=>"Waktunya udah kebooking jam {$jam} sama orang — pilih slot lain dong 😅"
      ]);
    }
  }

  // ====== HARGA PER JAM ======
  // $harga = (int)$meja->harga_per_jam;

  // ====== VOUCHER BLOCK (lock dengan FOR UPDATE agar anti-race) ======
  $use_voucher  = false;
  $voucher_row  = null;
  $hp_norm62    = $this->normalize_phone_for_wa($no_hp); // 628xx…

  if ($voucher_code !== '') {
      $vsql = "SELECT * FROM voucher_billiard
               WHERE kode_voucher = ?
                 AND jenis = 'FREE_MAIN'
                 AND status = 'baru'
                 AND is_claimed = 0
                 AND no_hp_norm = ?
               LIMIT 1 FOR UPDATE";
      $voucher_row = $this->db->query($vsql, [$voucher_code, $hp_norm62])->row();
      if (!$voucher_row) {
        $this->db->trans_rollback();
        return $this->_json([
          "success"=>false,
          "title"=>"Voucher Invalid",
          "pesan"=>"Kode voucher nggak ketemu / udah dipakai / bukan milik nomor ini."
        ]);
      }
      $use_voucher = true;

      // ====== FORCE DURASI MENGIKUTI VOUCHER ======
      $voucher_hours = (int)($voucher_row->jam_voucher ?? 1);
      if ($voucher_hours < 1) { $voucher_hours = 1; }
      $durasi        = $voucher_hours;

      $jam_selesai   = $this->_add_hours($jam_mulai, $durasi);

      // re-check tarif pake slot baru:
      $rateInfo = $this->_rate_for_slot_cfg($tanggal, $jam_mulai, $jam_selesai, $meja);
      if (!$rateInfo['ok']) {
        $this->db->trans_rollback();
        return $this->_json([
          "success"=>false,
          "title"=>"Di Luar Aturan Jam",
          "pesan"=>$rateInfo['msg']
        ]);
      }
      $harga = (int)$rateInfo['rate'];

      // Cek jam operasional untuk durasi voucher
      if (!$this->_within_open_hours($jam_mulai, $jam_selesai, $meja->jam_buka, $meja->jam_tutup, $tanggal)){
        $this->db->trans_rollback();
        return $this->_json([
          "success"=>false,
          "title"=>"Di Luar Operasional",
          "pesan"=>"Dengan durasi voucher {$voucher_hours} jam, slot melewati jam tutup. Coba majuin jam mulai ya."
        ]);
      }

    // Re-check overlap dgn durasi voucher
    $aEnd = $mk($date, $jam_selesai);
    if ($aEnd <= $aStart) $aEnd->modify('+1 day');
    foreach($others as $o){
      $bStart = $mk($o->tanggal, $o->jam_mulai);
      $bEnd   = $mk($o->tanggal, $o->jam_selesai);
      if (!$bStart || !$bEnd) continue;
      if ($bEnd <= $bStart) $bEnd->modify('+1 day');
      $overlapStart = ($aStart > $bStart) ? $aStart : $bStart;
      $overlapEnd   = ($aEnd < $bEnd) ? $aEnd : $bEnd;
      if ($overlapEnd > $overlapStart){
        $this->db->trans_rollback();
        $jam = substr($o->jam_mulai,0,5).'–'.substr($o->jam_selesai,0,5);
        return $this->_json([
          "success"=>false,
          "title"=>"Slot Bentrok",
          "pesan"=>"Durasi voucher {$voucher_hours} jam mentok ke slot lain ({$jam}). Majukan jam mulai atau pilih slot lainnya ya."
        ]);
      }
    }
  }

  // subtotal pakai durasi final (kalau voucher, pakai durasi voucher)
  $subtotal = $harga * $durasi;

  // ====== PERSIAPAN INSERT ======
  // kode unik hanya untuk transfer/QRIS; untuk voucher → 0
  if ($use_voucher) {
    $kode_unik   = 0;
    $grand_total = 0; // total bayar nol
  } else {
    if ($this->session->userdata("admin_username") == 'kasir' ) {
      $kode_unik = 0;
    } else {
      $kode_unik = random_int(1, 499);
    }
    $grand_total = $subtotal + $kode_unik;
  }

  $kode  = $this->_make_kode(8);
  $token = bin2hex(random_bytes(24));

  // status pesanan (TIDAK menyetel metode_bayar sama sekali)
  $status_pesanan = $use_voucher ? 'free' : 'draft';

  $insert = [
    'kode_booking'  => $kode,
    'access_token'  => $token,
    'status'        => $status_pesanan,
    'nama'          => $nama,
    'no_hp'         => $no_hp,
    'meja_id'       => $meja_id,
    'nama_meja'     => isset($meja->nama_meja) ? $meja->nama_meja : ('MEJA #'.$meja_id),
    'tanggal'       => $tanggal,
    'jam_mulai'     => $jam_mulai,
    'jam_selesai'   => $jam_selesai,   // sudah menyesuaikan voucher kalau ada
    'durasi_jam'    => $durasi,        // sudah menyesuaikan voucher kalau ada
    'harga_per_jam' => $harga,
    'subtotal'      => $subtotal,       // harga asli utk laporan, ikut durasi final
    'kode_unik'     => $kode_unik,
    'grand_total'   => $grand_total,
    'created_at'    => date('Y-m-d H:i:s'),
    'updated_at'    => date('Y-m-d H:i:s')
  ];

  $ok = $this->db->insert('pesanan_billiard', $insert);
  if (!$ok){
    $this->db->trans_rollback();
    return $this->_json(["success"=>false,"title"=>"Gagal","pesan"=>"Tidak dapat menyimpan pesanan."]);
  }
  $new_id = (int)$this->db->insert_id();

  // Jika voucher dipakai → tandai accept + claimed (masih 1 transaksi)
  if ($use_voucher) {
    $this->db->where('id_voucher', (int)$voucher_row->id_voucher)
             ->update('voucher_billiard', [
               'status'     => 'accept',
               'is_claimed' => 1,
               'claimed_at' => date('Y-m-d H:i:s'),
               'notes'      => 'Dipakai untuk booking ID '.$new_id,
             ]);
  }

  $this->db->trans_commit();
  // === TRANSACTION END ===

  // WA ringkasan (param kedua hanya label; DB tidak dipakai)
  $newRec = $this->mbi->get_by_token($token);
  if ($newRec) {
    $this->_wa_ringkasan($newRec, $use_voucher ? 'FREE' : 'DRAFT', $status_pesanan);
  }

  // redirect: jika voucher → ke halaman free; jika bukan → ke cart seperti biasa
  return $this->_json([
    "success"      => true,
    "title"        => $use_voucher ? "Booking Gratis" : "Berhasil",
    "pesan"        => $use_voucher
                        ? "Voucher diterima. Mainnya gratis yaa 🎉"
                        : "Booking dibuat. Kode: <b>{$kode}</b>",
    "redirect_url" => $use_voucher
                      ? site_url('billiard/free').'?t='.urlencode($token)
                      : site_url('billiard/cart').'?t='.urlencode($token)
  ]);
}


public function free(){
  $this->_nocache_headers();

  $token = trim((string)$this->input->get('t', TRUE));
  if ($token === '') return $this->_token_gone();

  $rec = $this->mbi->get_by_token($token);
  if (!$rec) return $this->_token_gone();

  // Kalau bukan booking gratis, lempar ke halaman detail standar
  if (strtolower((string)$rec->status) !== 'free') {
    return redirect('billiard/booked?t='.rawurlencode($token));
  }

  // ambil info meja master (opsional)
  $meja_master = $this->mbi->get_meja($rec->meja_id);
  $meja = (object) [
    'id_meja'       => $rec->meja_id,
    'nama_meja'     => $rec->nama_meja ?? ($meja_master->nama_meja ?? 'MEJA #'.$rec->meja_id),
    'harga_per_jam' => $rec->harga_per_jam ?? ($meja_master->harga_per_jam ?? 0),
    'jam_buka'      => $meja_master->jam_buka ?? '00:00:00',
    'jam_tutup'     => $meja_master->jam_tutup ?? '23:59:00',
  ];

  $data = [
    "controller" => get_class($this),
    "title"      => "Booking Gratis",
    "deskripsi"  => "Main gratis pake voucher — selamat menikmati! 😎",
    "booking"    => $rec,
    "meja"       => $meja,
    "prev"       => base_url("assets/images/billiard.webp"),
    "rec"        => $this->fm->web_me(),
  ];

  $this->_token_headers();
  $this->load->view('billiard/booking_free_view', $data);
}


public function pay_transfer($token = null){
  $this->_nocache_headers();
  if (!$token) show_404();

  // 1) Ambil & auto-cancel kalau masih draft dan deadline lewat (kasir/admin/cash dikecualikan di helper)
  $row = $this->mbi->get_by_token($token);
  if (!$row) show_404();
  $row = $this->_auto_cancel_if_expired($row);
  if (strtolower((string)$row->status) === 'batal') {
    return redirect('billiard/expired');
  }

  // 2) Jika sudah lunas → balik ke ringkasan
  $status_now = strtolower((string)($row->status ?? ''));
  if ($status_now === 'terkonfirmasi') {
    return redirect('billiard/cart?t=' . rawurlencode($row->access_token));
  }

  // 3) Pastikan status ke verifikasi untuk transfer (sinkron metode/kode_unik/grand_total)
  if ($status_now === 'draft' || strtolower((string)($row->metode_bayar ?? '')) !== 'transfer') {
    $this->_set_verifikasi((int)$row->id_pesanan, 'transfer');
    $row = $this->mbi->get_by_token($token); // re-fetch agar field (kode_unik, grand_total, metode) terbarui
    if (!$row) show_404();
  }

  // 4) Payload untuk view
  $deadline_ts = $this->_deadline_ts($row); // UI countdown saja (server tidak auto-cancel status 'verifikasi')
  $subtotal    = (int)($row->subtotal ?? 0);
  $kode_unik   = (int)($row->kode_unik ?? 0);
  $grand_total = (int)($row->grand_total ?? ($subtotal + $kode_unik));

  $rec       = $this->fm->web_me();
  $meja_info = $row->nama_meja ?? ('MEJA #'.($row->meja_id ?? ''));

  $data = [
    'title'        => 'Verifikasi Pembayaran (Transfer)',
    'deskripsi'    => 'Silakan transfer sesuai nominal total bayar berikut, kemudian tunggu verifikasi kasir.',
    'prev'         => base_url('assets/images/icon_app.png'),
    'rec'          => $rec,
    'order'        => $row,
    'items'        => [],
    'total'        => $subtotal,
    'kode_unik'    => $kode_unik,
    'grand_total'  => $grand_total,
    'meja_info'    => $meja_info,
    'deadline_ts'  => $deadline_ts, // tetap kirim: countdown tampilan
    'bank_list'    => [
      ['bank'=>'BNI','atas_nama'=>'Afrisal','no_rek'=>'1980870276'],
    ],
  ];

  $this->load->view('pay_transfer_view', $data);
}


public function status(){
    $this->_nocache_headers();

    $token = trim((string)$this->input->get('t', TRUE));
    if ($token === '') {
        return $this->_json(['success'=>false,'error'=>'Token kosong'], 400);
    }

    $row = $this->mbi->get_by_token($token);
    if (!$row) {
      return $this->_json(['success'=>false,'error'=>'Data tidak ditemukan'], 404);
    }

    $row = $this->_auto_cancel_if_expired($row);
    $status_now = strtolower((string)($row->status ?? ''));

    $deadline_ts = $this->_deadline_ts($row);
    $now_ts      = time();
    $remaining   = max(0, $deadline_ts - $now_ts) * 1000; // ms

    $payload = [
      'success'        => true,
      'status'         => $status_now,
      'kode_booking'   => (string)($row->kode_booking ?? ''),
      'subtotal'       => (int)($row->subtotal ?? 0),
      'kode_unik'      => (int)($row->kode_unik ?? 0),
      'grand_total'    => (int)($row->grand_total ?? 0),
      'redirect_cart'  => site_url('billiard/cart').'?t='.urlencode($row->access_token ?? ''),
      'redirect_booked'=> site_url('billiard/booked').'?t='.urlencode($row->access_token ?? ''),
      'updated_at'     => (string)($row->updated_at ?? ''),
      'deadline_ts'    => $deadline_ts * 1000,
      'remaining_ms'   => $remaining,
    ];

    return $this->_json($payload);

}


  /* ========= Cart ========= */
  public function cart(){
    $token   = trim((string)$this->input->get('t', TRUE));
    $booking = $this->mbi->get_by_token($token);
    if (!$booking) return $this->_token_gone();
      $booking = $this->_auto_cancel_if_expired($booking);
        if (!$booking || strtolower((string)$booking->status) === 'batal') {
          $this->session->set_flashdata('flash_err', 'Batas waktu pembayaran habis. Booking dibatalkan.');
          return redirect('billiard/expired');
        }

    // === AUTO CONFIRM utk kasir ===
    // pastikan library session aktif (biasanya sudah autoload)
    $role = strtolower((string)$this->session->userdata('admin_username'));
    if ($role === 'kasir') {
        $curStatus = strtolower((string)($booking->status ?? ''));
        if ($curStatus !== 'verifikasi') {
            // update lewat model (by token biar pasti)
            $this->mbi->set_status_by_token($token, 'verifikasi', [
                'updated_at' => date('Y-m-d H:i:s'),
                'metode_bayar' => 'cash',
            ]);

            // sinkronkan objek $booking utk view saat ini (tanpa query ulang)
            $booking->status       = 'verifikasi';
            $booking->updated_at = date('Y-m-d H:i:s');
            $booking->metode_bayar = 'cash';
        }
    }
    // === END AUTO CONFIRM ===
    $deadline_ts = $this->_deadline_ts($booking);
    // ambil info meja master (untuk data lain seperti price/jam buka/tutup)
    $meja_master = $this->mbi->get_meja($booking->meja_id);

    // buat objek meja utk view
    $meja = (object) [
      'id_meja'       => $booking->meja_id,
      'nama_meja'     => $booking->nama_meja ?? ($meja_master->nama_meja ?? 'MEJA #'.$booking->meja_id),
      'harga_per_jam' => $booking->harga_per_jam ?? ($meja_master->harga_per_jam ?? 0),
      'jam_buka'      => $meja_master->jam_buka ?? '00:00:00',
      'jam_tutup'     => $meja_master->jam_tutup ?? '23:59:00',
    ];

    $rec  = $this->fm->web_me();
    $lock = $this->_edit_lock_info($booking);

    $data = [
      "controller"   => get_class($this),
      "title"        => "Informasi Booking",
      "deskripsi"    => "Cek & ubah durasi/jam jika perlu.",
      "booking"      => $booking,
      "meja"         => $meja,
      "prev"         => base_url("assets/images/billiard.webp"),
      "rec"          => $rec,
      "max_edit"      => $lock['max_edit'],
      "sisa_edit"     => $lock['sisa_edit'],
      "limit_minutes" => $lock['limit_minutes'],
      "cutoff_dt"     => $lock['cutoff']->format('Y-m-d H:i'),
      "cutoff_hm"     => $lock['cutoff']->format('H:i'),
      "time_left_min" => $lock['time_left_min'],
      "edit_allowed"  => $lock['allowed'],
      "edit_reason"   => $lock['reason'],
      "deadline_ts"   => $deadline_ts,
    ];

    $this->_token_headers();
    $this->load->view('billiard/cart_billiard', $data);
}

 private function _nocache_headers(){
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
        $this->output->set_header('Pragma: no-cache');
    }
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

    /** Minutes allowed to pay (fallback 15) */
private function _late_min(): int {
  $web = $this->fm->web_me();
  $m = (int)($web->late_min ?? 15);
  return $m > 0 ? $m : 15;
}

/** Parse updated_at to unix ts (fallback now) */
private function _ts_from_updated($updated_at): int {
  if (is_numeric($updated_at)) return (int)$updated_at;
  $ts = @strtotime((string)$updated_at);
  return $ts ?: time();
}

/** Deadline = updated_at + late_min minutes */
private function _deadline_ts($row): int {
  return $this->_ts_from_updated($row->updated_at ?? null) + ($this->_late_min() * 60);
}

/** If passed deadline & still unpaid-status => cancel in DB, then refetch row */
/** Jika sudah lewat deadline & masih unpaid-status => cancel.
 *  KECUALI: sesi kasir ATAU metode_bayar = cash (tidak pernah auto-cancel).
 */
private function _auto_cancel_if_expired($row): ?object {
  if (!$row) return $row;

  // hanya status draft yang bisa di-auto-cancel
  $st = strtolower((string)($row->status ?? ''));
  if ($st !== 'draft') {
    return $row;
  }

  // pengecualian: sesi kasir/admin atau metode cash => jangan auto-cancel
  $role   = strtolower((string)$this->session->userdata('admin_username'));
  $metode = strtolower((string)($row->metode_bayar ?? ''));
  if ($role === 'kasir' || $role === 'admin' || $metode === 'cash') {
    return $row;
  }

  if (time() >= $this->_deadline_ts($row)) {
    $this->db->where('id_pesanan', (int)$row->id_pesanan)->update('pesanan_billiard', [
      'status'     => 'batal',
      'updated_at' => date('Y-m-d H:i:s'),
    ]);
    $row = $this->mbi->get_by_token($row->access_token);
  }
  return $row;
}



/** Halaman info kedaluwarsa sederhana */
public function expired(){
  $this->_nocache_headers();
  $data = [
    "controller"=>get_class($this),
    "title"=>"Waktu Habis",
    "deskripsi"=>"Batas waktu pembayaran sudah lewat. Booking dibatalkan otomatis.",
    "prev"=>base_url("assets/images/booking.png"),
    "rec"=>$this->fm->web_me(),
  ];
  $this->load->view('booking_error', $data); // buat view simpel, atau ganti ke booking_error
}

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

public function pay_qris($token = null){
    $this->_nocache_headers();
    if (!$token) show_404();

    // Ambil booking by access_token
    $row = $this->mbi->get_by_token($token);
    if (!$row) show_404();

    // Auto-cancel hanya untuk draft (fungsi kamu sudah handle)
    $row = $this->_auto_cancel_if_expired($row);
    if (strtolower((string)$row->status) === 'batal') {
        return redirect('billiard/expired');
    }

    // Jika sudah lunas → balik ke ringkasan
    $status_now = strtolower((string)($row->status ?? ''));
    if ($status_now === 'terkonfirmasi') {
        return redirect('billiard/cart?t=' . rawurlencode($row->access_token));
    }

    // Pastikan status menuju pembayaran (verifikasi) + sinkron metode/kode_unik/grand_total
    $desired        = 'qris';
    $needPromote    = ($status_now === 'draft');
    $needSyncMethod = (strtolower((string)($row->metode_bayar ?? '')) !== $desired);

    if ($needPromote || $needSyncMethod) {
        $this->_set_verifikasi((int)$row->id_pesanan, $desired);
        // re-fetch agar $row terbarui
        $row = $this->mbi->get_by_token($token);
        if (!$row) show_404();
    }

    // ===== Data pembayaran =====
    $deadline_ts = $this->_deadline_ts($row);
    $kode_unik   = (int)($row->kode_unik ?? 0);
    $subtotal    = (int)($row->subtotal ?? 0);
    $grand_total = (int)($row->grand_total ?? ($subtotal + $kode_unik));

    // ===== QRIS payload =====
    // 1) BASE QRIS (tanpa spasi/linebreak) – pastikan benar punyamu
    $BASE_QRIS = '00020101021126590013ID.CO.BNI.WWW011893600009150432388702096072939380303UMI51440014ID.CO.QRIS.WWW0215ID10254388495450303UMI5204793253033605802ID5922AUSI BILLIARD DAN CAFE6004WAJO61059099262070703A0163048CA7';

    // 2) Sisipkan Tag 54 (amount) + CRC baru
    $payload = $this->_qris_set_amount($BASE_QRIS, $grand_total);

    // 3) Generate PNG QR (uploads/qris/order_bill_{id}.png)
    $order_id = (int)($row->id_pesanan ?? 0);
    if ($order_id <= 0) show_404();

    $dir = FCPATH . 'uploads/qris';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    $png = $dir . "/order_bill_{$order_id}.png";

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
        // fallback kosong supaya view tidak error jika lib QR belum ada
        $im = imagecreatetruecolor(360,360); imagepng($im,$png); imagedestroy($im);
    }

    // 4) Overlay logo di tengah (opsional)
    $logoPath = FCPATH.'assets/images/logo_admin.png';
    if (file_exists($logoPath) && file_exists($png)) {
        $this->_overlay_logo_on_png($png, $logoPath, 0.22); // 22% lebar QR
    }

    // (opsional) URL langsung ke file PNG, tapi di view kamu pakai route qris_png (lebih aman)
    $qris_img  = base_url('uploads/qris/order_bill_'.$order_id.'.png');

    $rec       = $this->fm->web_me();
    $meja_info = $row->nama_meja ?? ('MEJA #'.($row->meja_id ?? ''));

    $data = [
        'title'        => 'Pembayaran via QRIS',
        'deskripsi'    => 'Silakan scan QRIS dan bayar sesuai total, lalu tunggu verifikasi kasir.',
        'prev'         => base_url('assets/images/icon_app.png'),
        'rec'          => $rec,

        'order'        => $row,              // record booking langsung
        'items'        => [],                // tidak ada item detail
        'total'        => $subtotal,
        'kode_unik'    => $kode_unik,
        'grand_total'  => $grand_total,
        'meja_info'    => $meja_info,

        'qris_img'     => $qris_img,         // fallback (view utamanya pakai qris_png/:token)
        'deadline_ts'  => $deadline_ts,      // untuk countdown (tampilan saja)
        'qris_payload' => $payload,
        'bank_list'    => [],
    ];

    $this->load->view('pay_qris_view', $data);
}



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

  public function qris_png($token = null){
  if (!$token) show_404();

  $row = $this->mbi->get_by_token($token);
  if (!$row) show_404();

  $status = strtolower((string)($row->status ?? ''));
  if ($status !== 'verifikasi') {
    show_404();
  }

  $orderId = (int)($row->id_pesanan ?? 0);
  if ($orderId <= 0) show_404();

  if (in_array($status, ['batal'], true)) show_404();

  $path = FCPATH.'uploads/qris/order_bill_'.$orderId.'.png';
  if (!is_file($path)) show_404();

  header('Content-Type: image/png');
  header('Content-Length: '.filesize($path));
  header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
  header('Pragma: no-cache');
  readfile($path);
  exit;
}



   private function _set_verifikasi($id_pesanan, $method){
  $id_pesanan = (int)$id_pesanan;
  $row = $this->db->get_where('pesanan_billiard', ['id_pesanan'=>$id_pesanan])->row();
  if (!$row) show_404();

  $status_now = strtolower((string)$row->status);
  if (in_array($status_now, ['terkonfirmasi','batal'], true)) return;

  $subtotal   = (int)($row->subtotal ?? 0);
  $needUnique = in_array($method, ['qris','transfer'], true);

  $kode = $needUnique
    ? (((int)$row->kode_unik >= 1 && (int)$row->kode_unik <= 499) ? (int)$row->kode_unik : random_int(1,499))
    : 0;

  $upd = [
    'metode_bayar' => $method,
    'kode_unik'    => $kode,
    'grand_total'  => $subtotal + $kode,
  ];

  // updated_at hanya saat promosi dari draft → verifikasi
  if ($status_now === 'draft') {
    $upd['status']     = 'verifikasi';
    // $upd['updated_at'] = date('Y-m-d H:i:s');
  }

  $this->db->where('id_pesanan', $id_pesanan)->update('pesanan_billiard', $upd);
}




  // Hitung status boleh edit berdasarkan kuota & waktu
  private function _edit_lock_info($booking) {
    $rec = $this->fm->web_me(); // identitas / web_me
    $max_edit = (int)($rec->maks_edit_booking ?? 2);
    $min_before = (int)($rec->edit_limit_minutes ?? 60);

    $edit_count = (int)($booking->edit_count ?? 0);

    // waktu sekarang
    $now = new DateTime('now', new DateTimeZone(date_default_timezone_get()));

    // start datetime (tanggal + jam_mulai H:i:s)
    $start = DateTime::createFromFormat('Y-m-d H:i:s', "{$booking->tanggal} {$booking->jam_mulai}");
    if (!$start) { // fallback kalau jam_mulai H:i
      $start = DateTime::createFromFormat('Y-m-d H:i', "{$booking->tanggal} ".substr($booking->jam_mulai,0,5));
    }

    // cutoff = X menit sebelum mulai
    $cutoff = clone $start;
    $cutoff->modify("-{$min_before} minutes");

    $sisa_edit = max(0, $max_edit - $edit_count);
    $time_left_min = max(0, (int) floor(($cutoff->getTimestamp() - $now->getTimestamp()) / 60));

    $allowed = true;
    $reason  = '';

    if ($sisa_edit <= 0) { $allowed = false; $reason = 'Kuota edit habis.'; }
    if ($now >= $cutoff) { $allowed = false; $reason = 'Sudah melewati batas waktu edit.'; }

    return [
      'max_edit'       => $max_edit,
      'edit_count'     => $edit_count,
      'sisa_edit'      => $sisa_edit,
      'limit_minutes'  => $min_before,
      'now'            => $now,
      'start'          => $start,
      'cutoff'         => $cutoff,
      'time_left_min'  => $time_left_min,
      'allowed'        => $allowed,
      'reason'         => $reason,
    ];
  }

  public function update_cart(){
    $token   = trim((string)$this->input->post('t', TRUE));
    $recBook = $this->mbi->get_by_token($token);
    if (!$recBook) return $this->_json(["success"=>false,"title"=>"Tidak Valid","pesan"=>"Link tidak ditemukan."]);

    // Cek kebijakan edit (kuota & waktu)
    $lock = $this->_edit_lock_info($recBook);
    if (!$lock['allowed']){
      $pesan = $lock['reason'];
      if ($lock['reason'] === 'Sudah melewati batas waktu edit.') {
        $pesan .= " (Batas: {$lock['limit_minutes']} menit sebelum mulai, s/d {$lock['cutoff']->format('H:i')})";
      } else {
        $pesan .= " (Maksimal {$lock['max_edit']}x).";
      }
      return $this->_json(["success"=>false,"title"=>"Tidak Bisa Diubah","pesan"=>$pesan]);
    }

    // Normalisasi input jam
    $raw_jam   = (string)$this->input->post('jam_mulai', TRUE);
    $norm_jam  = $this->_normalize_hhmm($raw_jam) ?? substr($recBook->jam_mulai,0,5);
    $jam_mulai = $norm_jam . ':00';

    $durasi = (int)$this->input->post('durasi_jam', TRUE);
    $durasi = max(1, min(12, $durasi));

    // Cek ada perubahan?
    $changed = ($jam_mulai !== $recBook->jam_mulai) || ($durasi !== (int)$recBook->durasi_jam);
    if (!$changed){
      return $this->_json(["success"=>false,"title"=>"Tidak Ada Perubahan","pesan"=>"Jam & durasi tidak berubah."]);
    }

    // Validasi operasional
    $meja = $this->mbi->get_meja($recBook->meja_id);
    $jam_selesai = $this->_add_hours($jam_mulai, $durasi);

    if (!$this->_within_open_hours($jam_mulai, $jam_selesai, $meja->jam_buka, $meja->jam_tutup, $recBook->tanggal)){
      return $this->_json(["success"=>false,"title"=>"Di Luar Operasional","pesan"=>"Jam operasional meja: ".substr($meja->jam_buka,0,5)."–".substr($meja->jam_tutup,0,5)."."]);
    }
    // === VALIDASI & TARIF MENGIKUTI KONFIG MEJA (weekday/weekend) ===
$rateInfo = $this->_rate_for_slot_cfg($recBook->tanggal, $jam_mulai, $jam_selesai, $meja);
if (!$rateInfo['ok']) {
  return $this->_json([
    "success"=>false,
    "title"=>"Di Luar Aturan Jam",
    "pesan"=>$rateInfo['msg']
  ]);
}
$effRate  = (int)$rateInfo['rate'];
$subtotal = $effRate * $durasi;

    // ---- Toleransi overlap (drift) ----
    // ambil toleransi menit dari konfigurasi web_me(), default 15 menit
    $tol = (int)($this->fm->web_me()->tolerance_minutes ?? 15);

    // ambil bookings lain untuk meja: tanggal yang sama, hari sebelumnya, dan hari berikutnya
    $date = $recBook->tanggal; // "Y-m-d"
    $prev_date = (new DateTime($date))->modify('-1 day')->format('Y-m-d');
    $next_date = (new DateTime($date))->modify('+1 day')->format('Y-m-d');

    $others = $this->db
      ->select('id_pesanan, tanggal, jam_mulai, jam_selesai, nama')
      ->from('pesanan_billiard')
      ->where('meja_id', $recBook->meja_id)
      ->group_start()
        ->where('tanggal', $prev_date)
        ->or_where('tanggal', $date)
        ->or_where('tanggal', $next_date)
      ->group_end()
      ->where('id_pesanan !=', $recBook->id_pesanan)
      ->get()
      ->result();

    // helper: buat DateTime dari date + time (terima H:i:s atau H:i)
    $mk_dt = function(string $d, string $t) {
      $dt = DateTime::createFromFormat('Y-m-d H:i:s', $d.' '.$t);
      if ($dt !== false) return $dt;
      $dt2 = DateTime::createFromFormat('Y-m-d H:i', $d.' '.substr($t,0,5));
      return $dt2 !== false ? $dt2 : null;
    };

    // create our new interval (may cross midnight)
    $aStart = $mk_dt($date, $jam_mulai);
    $aEnd   = $mk_dt($date, $jam_selesai);
    if (!$aStart || !$aEnd) {
      return $this->_json(["success"=>false,"title"=>"Invalid Time","pesan"=>"Waktu tidak valid."]);
    }
    if ($aEnd <= $aStart) $aEnd->modify('+1 day'); // overnight for the new interval

    foreach ($others as $o){
      $bStart = $mk_dt($o->tanggal, $o->jam_mulai);
      $bEnd   = $mk_dt($o->tanggal, $o->jam_selesai);
      if (!$bStart || !$bEnd) continue; // skip malformed rows (log jika perlu)
      if ($bEnd <= $bStart) $bEnd->modify('+1 day');

      // compute overlap
      $overlapStart = ($aStart > $bStart) ? $aStart : $bStart;
      $overlapEnd   = ($aEnd < $bEnd) ? $aEnd : $bEnd;

      if ($overlapEnd > $overlapStart) {
        $overlapMinutes = (int) ceil(($overlapEnd->getTimestamp() - $overlapStart->getTimestamp()) / 60);
        if ($overlapMinutes > $tol) {
          $firstName = $o->nama ?? ('#'.$o->id_pesanan);
          $pesan = "Waktu yang dipilih berbenturan dengan booking lain ({$firstName} — ".substr($o->jam_mulai,0,5)."–".substr($o->jam_selesai,0,5).") lebih dari toleransi ({$tol} menit). Silakan pilih jam lain.";
          return $this->_json(["success"=>false,"title"=>"Slot Bentrok","pesan"=>$pesan]);
        } else {
          // small overlap: allowed but you can record detail to show to user
        }
      }
    }

    // ---- Jika sampai sini: tidak ada overlap > tolerance => boleh update ----
    // $subtotal = (int)$recBook->harga_per_jam * $durasi;
    $ok = $this->mbi->update_by_token($token, [
  'jam_mulai'     => $jam_mulai,
  'jam_selesai'   => $jam_selesai,
  'durasi_jam'    => $durasi,
  'harga_per_jam' => $effRate,    // simpan tarif efektif untuk tampilan & WA
  'subtotal'      => $subtotal,
  'edit_count'    => ((int)$recBook->edit_count) + 1,
]);


    if (!$ok) return $this->_json(["success"=>false,"title"=>"Gagal","pesan"=>"Tidak dapat memperbarui keranjang."]);

    // Hitung sisa setelah increment
    $left = max(0, $lock['max_edit'] - (((int)$recBook->edit_count) + 1));
    $msg_coll = '';
    if (!empty($collisions)){
      // optional: beri tahu ada small overlap yg diizinkan
      $msg_coll = ' (Perhatian: ada geseran kecil dengan booking lain — drift ≤ '.$tol.' menit.)';
    }

    return $this->_json([
      "success"=>true,
      "title"=>"OK",
      "pesan"=>"Bookingan diperbarui. Sisa kuota perubahan: {$left}x. Batas waktu ubah: {$lock['cutoff']->format('H:i')} ({$lock['limit_minutes']} menit sebelum mulai).".$msg_coll,
      "redirect_url"=>site_url('billiard/cart').'?t='.urlencode($token),
    ]);
  }


  /**
   * Cek apakah slot (mulai, selesai) berada di dalam jam operasional meja.
   * Mendukung jam tutup keesokan hari (overnight).
   *
   * @param string $mulai  "HH:MM:SS" atau "HH:MM"
   * @param string $selesai "HH:MM:SS" atau "HH:MM"
   * @param string $buka   "HH:MM:SS" atau "HH:MM"
   * @param string $tutup  "HH:MM:SS" atau "HH:MM"
   * @param string|null $date  tanggal booking "Y-m-d" (jika null gunakan hari ini)
   * @return bool
   */
  private function _within_open_hours($mulai, $selesai, $buka, $tutup, $date = null): bool {
  // helper: HH:MM[:SS] / "21.00" → menit dari 00:00
  $hm2min = function($t){
    $t = str_replace('.', ':', (string)$t);
    $t = substr($t, 0, 5);
    if (!preg_match('/^(\d{1,2}):([0-5]\d)$/', $t, $m)) return null;
    $h = (int)$m[1]; $i = (int)$m[2];
    if ($h < 0 || $h > 23) return null;
    return $h*60 + $i;
  };

  $s  = $hm2min($mulai);
  $e  = $hm2min($selesai);
  $op = $hm2min($buka);
  $cl = $hm2min($tutup);
  if ($s === null || $e === null || $op === null || $cl === null) return false;

  // normalisasi selesai melewati start (00:00 setelah 23:00, dst)
  if ($e <= $s) $e += 1440;

  // operasional overnight jika tutup <= buka (mis. 18:00 → 02:00)
  $overnight = ($cl <= $op);
  if ($overnight) $cl += 1440;

  // jika overnight dan jam mulai < buka (jam 00:30, 01:00, dst),
  // geser ke “hari+1” supaya berada pada rentang 18:00..02:00 (H+1)
  if ($overnight && $s < $op) {
    $s += 1440;
    if ($e <= $s) $e += 1440; // jaga konsistensi
  }

  // bandingkan relatif terhadap jam buka
  $startRel = $s - $op;
  $endRel   = $e - $op;
  $closeRel = $cl - $op; // 0 .. close

  return ($startRel >= 0 && $endRel > $startRel && $endRel <= $closeRel);
}


  /* ========= Metode Bayar ========= */
  public function metode(){
    $token = trim((string)$this->input->get('t', TRUE));
    $rec   = $this->mbi->get_by_token($token);
    if (!$rec) return $this->_token_gone();

    $data = [
      "controller"=>get_class($this),
      "title"     =>"Metode Pembayaran",
      "deskripsi" =>"Pilih metode pembayaran.",
      "rec"       =>$rec,
      "meja"      =>$this->mbi->get_meja($rec->meja_id),
      "prev"      =>base_url("assets/images/booking.png"),
      "web"       =>$this->fm->web_me(),
    ];
    $this->_token_headers();
    $this->load->view('billiard/metode_billiard', $data);
  }

  public function konfirmasi(){
  $token  = trim((string)$this->input->post('t', TRUE));
  $metode = trim((string)$this->input->post('metode_bayar', TRUE));
  if (!in_array($metode, ['cash','qris','transfer'], true)) $metode = 'cash';

  $rec = $this->mbi->get_by_token($token);
  if (!$rec) return $this->_json(["success"=>false,"title"=>"Tidak Valid","pesan"=>"Link tidak ditemukan."]);

  $rec = $this->_auto_cancel_if_expired($rec);
  if (strtolower((string)$rec->status) === 'batal') {
    return $this->_json(["success"=>false,"title"=>"Waktu Habis","pesan"=>"Batas waktu pembayaran sudah habis. Booking dibatalkan."]);
  }

  // race check
  if ($this->mbi->has_overlap($rec->meja_id, $rec->tanggal, $rec->jam_mulai, $rec->jam_selesai, $rec->id_pesanan)){
    return $this->_json(["success"=>false,"title"=>"Slot Bentrok","pesan"=>"Slot diambil pengguna lain tepat saat konfirmasi. Silakan atur ulang."]);
  }

  $status = ($metode === 'cash') ? 'terkonfirmasi' : 'verifikasi';
  $this->mbi->update_by_token($token, ['metode_bayar'=>$metode,'status'=>$status]);

  // WA otomat (ringkas)
  $this->_wa_ringkasan($rec, $metode, $status);

  return $this->_json([
    "success"=>true,
    "title"=>"Terkonfirmasi",
    "pesan"=>"Status: ".str_replace('_',' ',$status).". Rincian dikirim via WhatsApp.",
    "redirect_url"=> site_url('billiard/booked').'?t='.urlencode($token)
  ]);
}


  /* ========= Resume / Detail ========= */
  public function booked(){
    $token = trim((string)$this->input->get('t', TRUE));
    $rec   = $this->mbi->get_by_token($token);
    if (!$rec) return $this->_token_gone();

    $data = [
      "controller"=>get_class($this),
      "title"     =>"Detail Booking",
      "deskripsi" =>"Ringkasan pesanan",
      "rec"       =>$rec,
      "meja"      =>$this->mbi->get_meja($rec->meja_id),
      "prev"      =>base_url("assets/images/booking.png"),
      "web"       =>$this->fm->web_me(),
    ];
    $this->_token_headers();
    $this->load->view('billiard/detail_billiard', $data);
  }

  /* ========= Edit ringan (jam/durasi) dari halaman detail ========= */
  public function edit(){
    return redirect('billiard/cart?t='.urlencode((string)$this->input->get('t', TRUE)));
  }

  /* ========= Batalkan ========= */
  public function batal(){
    $token = trim((string)$this->input->post('t', TRUE));
    $rec   = $this->mbi->get_by_token($token);
    if (!$rec) {
        return $this->_json(["success"=>false,"title"=>"Tidak Valid","pesan"=>"Link tidak ditemukan."]);
    }

    // Preferensi: gunakan method model jika ada
    $deleted = false;
    if (method_exists($this->mbi, 'delete_by_token')) {
        try {
            $deleted = (bool) $this->mbi->delete_by_token($token);
        } catch (Throwable $e) {
            log_message('error', 'delete_by_token error: '.$e->getMessage());
            $deleted = false;
        }
    } else {
        // Fallback langsung ke DB
        $this->db->where('access_token', $token)->delete('pesanan_billiard');
        $deleted = ($this->db->affected_rows() > 0);
    }

    if (!$deleted) {
        return $this->_json([
            "success"=>false,
            "title"=>"Gagal",
            "pesan"=>"Tidak dapat menghapus pesanan. Silakan coba lagi."
        ]);
    }

    return $this->_json([
      "success"=>true,
      "title"=>"Dihapus",
      "pesan"=>"Booking berhasil dihapus.",
      "redirect_url"=> site_url('billiard')
    ]);
}


  /* ========= Rules & Helpers ========= */
  private function _rules(){
    $this->form_validation->set_rules('meja_id','Meja','required|integer');
    $this->form_validation->set_rules('nama','Nama','required|trim|min_length[3]');

    // TANPA regex_match (kita cek manual)
    $this->form_validation->set_rules('no_hp','Nomor HP','required|trim|min_length[10]|max_length[13]');
    $this->form_validation->set_rules('tanggal','Tanggal','required|trim');
    $this->form_validation->set_rules('jam_mulai','Jam Mulai','required|trim');

    $this->form_validation->set_rules('durasi_jam','Durasi (jam)','required|integer|greater_than_equal_to[1]|less_than_equal_to[12]');

    $this->form_validation->set_message('required','* %s harus diisi');
    $this->form_validation->set_error_delimiters('<br> ',' ');
  }

  private function _add_hours(string $his, int $h): string {
    $dt = DateTime::createFromFormat('H:i:s', $his) ?: DateTime::createFromFormat('H:i', substr($his,0,5));
    $dt->modify('+'.$h.' hour');
    return $dt->format('H:i:s');
  }

  private function _make_kode($len=8): string {
    $alphabet='ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $n=strlen($alphabet); $s='';
    for($i=0;$i<$len;$i++){ $s.=$alphabet[random_int(0,$n-1)]; }
    // pastikan unik
    for($try=0;$try<10;$try++){
      $exists=$this->db->select('1',false)->from('pesanan_billiard')->where('kode_booking',$s)->limit(1)->get()->num_rows()>0;
      if(!$exists) return $s;
      $s.=$alphabet[random_int(0,$n-1)];
    }
    return $s;
  }

function normalize_phone_for_wa($raw){
    // 1. bersihkan karakter selain angka dan plus
    $num = preg_replace('/[^0-9+]/', '', $raw ?? '');

    // 2. trim spasi di kiri/kanan
    $num = trim($num);

    // 3. Kalau kosong, balikin kosong aja
    if ($num === '') {
        return '';
    }

    // 4. Kalau ada plus di depan, hapus plusnya
    if (strpos($num, '+') === 0) {
        $num = substr($num, 1); // "+62812..." -> "62812..."
    }

    // 5. Cek pola Indonesia
    //    - "08xxxx"   -> "628xxxx"
    //    - "62xxxx"   -> sudah oke
    //
    //    NOTE:
    //    kita sengaja TARUH cek "62" sebelum cek "0"
    //    supaya nomor yg memang sudah 62 tidak diapa2in lagi.
    if (strpos($num, '62') === 0) {
        // Sudah format internasional Indonesia tanpa plus.
        return $num;
    }

    if (strpos($num, '0') === 0) {
        // diasumsikan nomor lokal Indonesia yg nulis 08...
        // contoh: "08123456789" -> "628123456789"
        return '62' . substr($num, 1);
    }

    // 6. BUKAN diawali 0, BUKAN diawali 62:
    //    berarti kemungkinan user sudah kasih kode negara lain
    //    contoh:
    //      "61424..."  -> nomor Australia (+61...)
    //      "6598..."   -> nomor Singapura (+65...)
    //      "1xxx..."   -> US/Canada (+1...) tapi kita ga sentuh
    //
    //    Jadi jangan diubah
    return $num;
}


private function _wa_ringkasan($rec, $metode, $status){
    // Pastikan fungsi pengirim WA tersedia
    if (!function_exists('send_wa_single')) {
        log_message('error', 'WA: send_wa_single tidak ditemukan. Aborting WA send.');
        return false;
    }

    // Ambil & sanitasi nomor HP dari record
    $raw = (string)($rec->no_hp ?? '');
    $no  = preg_replace('/\D+/', '', $raw); // hilangkan semua non-digit
    if ($no === '') {
        log_message('error', 'WA: nomor HP kosong pada booking id=' . ($rec->id_pesanan ?? '[unknown]') . ' kode=' . ($rec->kode_booking ?? '[unknown]'));
        return false;
    }

    // Normalisasi ke format internasional tanpa plus (contoh: 0812... -> 62812...)
    if (strpos($no, '0') === 0) {
        $no_norm = '62' . substr($no, 1);
    } elseif (strpos($no, '62') === 0) {
        $no_norm = $no;
    } elseif (strpos($no, '+62') === 0) {
        $no_norm = substr($no, 1);
    } else {
        $no_norm = $no;
    }

    // Jika tabel punya kolom no_hp_normalized, simpan agar link lebih mudah dipakai nanti
    try {
        if ($this->db->field_exists('no_hp_normalized', 'pesanan_billiard')) {
            $this->db->where('access_token', $rec->access_token)->update('pesanan_billiard', ['no_hp_normalized' => $no_norm]);
        }
    } catch(Throwable $e){
        log_message('debug', 'WA: gagal simpan no_hp_normalized: '.$e->getMessage());
    }

    // Nama meja snapshot / fallback master
    $meja_nama = $rec->nama_meja
        ?? ($this->db->select('nama_meja')->get_where('meja_billiard', ['id_meja' => $rec->meja_id])->row('nama_meja') ?: ('MEJA #'.($rec->meja_id ?? '')));

    $web   = $this->fm->web_me();
    $site  = $web->nama_website ?? 'Sistem';

    // Deteksi booking free
    $isFree   = (strtolower((string)$status) === 'free') || ((int)($rec->grand_total ?? 0) === 0);
    $judul    = $isFree ? 'Booking Gratis Billiard' : 'Booking Billiard';

    $subtotal = (int)($rec->subtotal ?? 0);
    $grand    = (int)($rec->grand_total ?? ($subtotal + (int)($rec->kode_unik ?? 0)));

    // Build pesan
   $lines = [];

    // HEADER
    $lines[] = "🎱 *{$judul} — {$site}*";
    $lines[] = "--------------------------------";
    $lines[] = "";

    // DETAIL BOOKING
    $lines[] = "📄 *Kode Booking:* " . ($rec->kode_booking ?? '-');
    $lines[] = "🙍 *Nama:* " . ($rec->nama ?? '-');
    $lines[] = "📞 *HP:* "   . ($this->_pretty_hp($rec->no_hp ?? ''));
    $lines[] = "🪑 *Meja:* " . $meja_nama;
    $lines[] = "📅 *Tanggal:* " . hari($rec->tanggal).", ".tgl_view($rec->tanggal);
    $lines[] = "⏰ *Jam:* " . (substr($rec->jam_mulai ?? '00:00:00',0,5)) . "–" . (substr($rec->jam_selesai ?? '00:00:00',0,5));
    $lines[] = "⏳ *Durasi:* " . ($rec->durasi_jam ?? '-') . " Jam";
    $lines[] = "";

    // TARIF & BIAYA
    $lines[] = "💸 *Tarif / Jam:* Rp" . number_format((int)($rec->harga_per_jam ?? 0),0,',','.');
    $lines[] = "🔢 *Kode Unik:* Rp" . number_format((int)($rec->kode_unik ?? 0),0,',','.');
    $lines[] = "🧮 *Subtotal:* Rp"  . number_format($subtotal,0,',','.');

    if ($isFree) {
        $lines[] = "✅ *Total Bayar:* Rp0";
        $lines[] = "_(Promo voucher / free play)_";
    } else {
        $lines[] = "💳 *Total Bayar:* Rp" . number_format($grand,0,',','.');
    }
    $lines[] = "";

    // LINK TIKET / PEMBAYARAN
    $link = $isFree
        ? (site_url('billiard/free') . '?t=' . urlencode($rec->access_token ?? ''))
        : (site_url('billiard/cart') . '?t=' . urlencode($rec->access_token ?? ''));

    if ($isFree) {
        $lines[] = "🎟 *Tiket Gratis Kamu:*";
        $lines[] = $link;
    } else {
        $lines[] = "🔗 *Lanjutkan Pembayaran disini:*";
        $lines[] = $link;
    }

    $lines[] = "💾 Simpan kontak ini supaya link bisa diklik.";
    $lines[] = "";

    // INSTRUKSI KASIR
    // $lines[] = "📣 Tunjukkan pesan ini ke kasir saat mulai main.";
    // $lines[] = "";

    // FOOTER OTOMATIS
    $lines[] = "📣 _Pesan ini dikirim otomatis oleh sistem {$site}. Mohon jangan dibalas._";


    $pesan = implode("\n", $lines);

    try {
        $res = send_wa_single($no_norm, $pesan);
        if (is_string($res)) {
            log_message('debug', 'WA: kirim ke '.$no_norm.' hasil: '.$res);
        } else {
            log_message('debug', 'WA: kirim ke '.$no_norm.' hasil: '.json_encode($res));
        }
        return true;
    } catch (Throwable $e) {
        log_message('error', 'WA error: '.$e->getMessage().' trace: '.$e->getTraceAsString());
        return false;
    }
}



  private function _token_headers(){
    $this->output
      ->set_header('X-Robots-Tag: noindex, nofollow, noarchive')
      ->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0')
      ->set_header('Pragma: no-cache');
  }
  private function _token_gone(){
    $this->_token_headers();
    $data = [
      "controller"=>get_class($this),
      "title"=>"Link Tidak Berlaku",
      "deskripsi"=>"Token tidak valid / data tidak ditemukan.",
      "prev"=>base_url("assets/images/booking.png"),
      "rec"=>$this->fm->web_me(),
    ];
    $this->load->view('booking_error', $data);
    return;
  }

  private function _json($payload, int $code=200){
    return $this->output->set_status_header($code)->set_content_type('application/json','utf-8')
      ->set_output(json_encode($payload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
  }

  /* ===================== VOUCHER: Free Main Billiard ===================== */

/** Normalisasi hanya digit */
private function _normalize_digits($s): string {
  return preg_replace('/\D+/', '', (string)$s);
}

/** Normalisasi ke MSISDN Indonesia (0812… → 62812…) tanpa tanda + */
private function _normalize_msisdn($s): string {
  $d = $this->_normalize_digits($s);
  if ($d === '') return '';
  if (strpos($d, '0') === 0) return '62' . substr($d, 1);
  if (strpos($d, '62') === 0) return $d;
  if (strpos($d, '8') === 0) return '62' . $d; // antisipasi input "812…"
  return $d;
}

/** Pastikan tabel voucher_billiard ada */
private function _ensure_voucher_table(): void {
  if ($this->db->table_exists('voucher_billiard')) return;

  $sql = "CREATE TABLE IF NOT EXISTS `voucher_billiard` (
    `id_voucher` INT AUTO_INCREMENT PRIMARY KEY,
    `no_hp` VARCHAR(32) NOT NULL,
    `no_hp_norm` VARCHAR(32) NOT NULL,
    `nama` VARCHAR(100) NULL,
    `kode_voucher` VARCHAR(32) NOT NULL UNIQUE,
    `jenis` VARCHAR(32) NOT NULL DEFAULT 'FREE_MAIN',
    `status` VARCHAR(20) NOT NULL DEFAULT 'baru',    -- baru | terpakai | batal
    `is_claimed` TINYINT(1) NOT NULL DEFAULT 0,
    `issued_from_count` INT NOT NULL DEFAULT 0,
    `jam_voucher` INT NOT NULL DEFAULT 1,                  -- jumlah konfirmasi saat voucher dikeluarkan
    `created_at` DATETIME NOT NULL,
    `claimed_at` DATETIME NULL,
    `notes` VARCHAR(255) NULL,
    INDEX `idx_hp_norm` (`no_hp_norm`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
  $this->db->query($sql);
}

/** Kode voucher unik */
private function _make_voucher_code($len = 8): string {
  $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
  $n = strlen($alphabet);
  for ($try = 0; $try < 30; $try++) {
    $s = 'VCH-';
    for ($i = 0; $i < $len; $i++) {
      $s .= $alphabet[random_int(0, $n - 1)];
    }
    $exists = $this->db->select('1', false)
      ->from('voucher_billiard')
      ->where('kode_voucher', $s)->limit(1)->get()->num_rows() > 0;
    if (!$exists) return $s;
  }
  // fallback (nyaris mustahil)
  return 'VCH-' . bin2hex(random_bytes(4));
}

/** Ambil batas_edit dari identitas → minimal 1, default 10 */
private function _voucher_threshold(): int {
  $rec = $this->fm->web_me();
  $batas = (int)($rec->batas_edit ?? 10);
  if ($batas < 1) $batas = 10;
  return $batas;
}

/** Hitung statistik voucher untuk 1 nomor */
private function _voucher_stats_by_hp(string $no_hp_raw): array {
  $hp_norm = $this->_normalize_msisdn($no_hp_raw);
  if ($hp_norm === '') {
    return ['ok' => false, 'reason' => 'Nomor HP kosong/invalid'];
  }

  // hitung total terkonfirmasi untuk nomor ini
  $use_norm_col = $this->db->field_exists('no_hp_normalized', 'pesanan_billiard');
  if ($use_norm_col) {
    $confirmed = (int)$this->db->select('COUNT(*) AS c', false)
      ->get_where('pesanan_billiard', ['status' => 'terkonfirmasi', 'no_hp_normalized' => $hp_norm])
      ->row('c');
  } else {
    // fallback: normalisasi di PHP (ambil semua no_hp yang mirip → filter)
    $rows = $this->db->select('no_hp')->from('pesanan_billiard')
      ->where('status', 'terkonfirmasi')->get()->result();
    $confirmed = 0;
    foreach ($rows as $r) {
      if ($this->_normalize_msisdn($r->no_hp) === $hp_norm) $confirmed++;
    }
  }

  // nama terakhir (opsional)
  $nama = $this->db->select('nama')->from('pesanan_billiard')
    ->where('status', 'terkonfirmasi')
    ->order_by('id_pesanan', 'DESC')
    ->limit(1)
    ->get()->row('nama') ?? '';

  $batas = $this->_voucher_threshold();
  $should = (int) floor($confirmed / $batas);

  $issued = (int)$this->db->select('COUNT(*) AS c', false)
    ->get_where('voucher_billiard', ['no_hp_norm' => $hp_norm, 'jenis' => 'FREE_MAIN'])
    ->row('c');

  $unused = (int)$this->db->select('COUNT(*) AS c', false)
    ->get_where('voucher_billiard', ['no_hp_norm' => $hp_norm, 'jenis' => 'FREE_MAIN', 'is_claimed' => 0])
    ->row('c');

  return [
    'ok'         => true,
    'hp_norm'    => $hp_norm,
    'nama'       => $nama,
    'batas'      => $batas,
    'confirmed'  => $confirmed,
    'should'     => $should,    // total voucher seharusnya
    'issued'     => $issued,    // total voucher yang sudah dibuat
    'unused'     => $unused,    // voucher yang belum diklaim
    'missing'    => max(0, $should - $issued),
  ];
}

/**
 * JOB: generate voucher berdasarkan akumulasi transaksi "terkonfirmasi".
 * - Bisa dipanggil via cron (GET ke URL ini)
 * - Bisa testing manual (tambahkan ?no_hp=08xxxxxxxxxx untuk proses 1 nomor)
 */
public function voucher_issue_job() {
  $this->_nocache_headers();
  $this->_ensure_voucher_table();

  $web        = $this->fm->web_me();
  $siteName   = (string)($web->nama_website ?? 'Billiard');
  $batasHariV = (int)($web->batas_hari ?? 30);
  if ($batasHariV < 1) $batasHariV = 30;

  $filter_hp = trim((string)$this->input->get('no_hp', TRUE));
  $batas = $this->_voucher_threshold();

  $targets = [];

  if ($filter_hp !== '') {
    // hanya 1 nomor
    $hp_norm = $this->_normalize_msisdn($filter_hp);
    if ($hp_norm !== '') $targets[$hp_norm] = ['hp_norm' => $hp_norm, 'nama' => ''];
  } else {
    // proses semua nomor yang pernah terkonfirmasi
    $use_norm_col = $this->db->field_exists('no_hp_normalized', 'pesanan_billiard');
    if ($use_norm_col) {
      $rs = $this->db->select('no_hp_normalized AS hp_norm, MAX(nama) AS nama, COUNT(*) AS c', false)
        ->from('pesanan_billiard')
        ->where('status', 'terkonfirmasi')
        ->group_by('no_hp_normalized')
        ->get()->result();
      foreach ($rs as $r) {
        $hp = $this->_normalize_msisdn($r->hp_norm);
        if ($hp) $targets[$hp] = ['hp_norm' => $hp, 'nama' => (string)$r->nama];
      }
    } else {
      // fallback: kumpulkan lalu normalisasi di PHP
      $rs = $this->db->select('no_hp, nama')->from('pesanan_billiard')
        ->where('status', 'terkonfirmasi')->get()->result();
      foreach ($rs as $r) {
        $hp = $this->_normalize_msisdn($r->no_hp);
        if (!$hp) continue;
        if (!isset($targets[$hp])) $targets[$hp] = ['hp_norm' => $hp, 'nama' => (string)($r->nama ?? '')];
      }
    }
  }

  $created = [];
  $tz      = new DateTimeZone('Asia/Makassar'); // WITA
  $now     = new DateTime('now', $tz);

  foreach ($targets as $hp_norm => $meta) {
    $stats = $this->_voucher_stats_by_hp($hp_norm);
    if (!$stats['ok']) continue;

    $to_make = (int)$stats['missing'];
    if ($to_make <= 0) {
      $created[] = [
        'no_hp_norm'  => $hp_norm,
        'made'        => 0,
        'reason'      => 'sudah cukup / belum memenuhi kelipatan',
        'confirmed'   => $stats['confirmed'],
        'batas'       => $stats['batas'],
        'should'      => $stats['should'],
        'issued'      => $stats['issued'],
        'unused'      => $stats['unused'],
        'wa'          => ['sent' => false, 'reason' => 'no new voucher'],
      ];
      continue;
    }

    // buat voucher baru & kumpulkan kodenya
    $codes = [];
    for ($i = 0; $i < $to_make; $i++) {
      $kode = $this->_make_voucher_code(8);
      $jamVoucherDefault = $web->jam_voucher_default;
      $this->db->insert('voucher_billiard', [
        'no_hp'             => $hp_norm,                // simpan normalized
        'no_hp_norm'        => $hp_norm,
        'nama'              => (string)($stats['nama'] ?? ''),
        'kode_voucher'      => $kode,
        'jenis'             => 'FREE_MAIN',
        'status'            => 'baru',
        'is_claimed'        => 0,
        'issued_from_count' => $stats['confirmed'],
        'jam_voucher'       => $jamVoucherDefault,
        'created_at'        => $now->format('Y-m-d H:i:s'),
        'claimed_at'        => null,
        'notes'             => 'Auto issue by job (batas_edit=' . $stats['batas'] . ')',
      ]);
      $codes[] = $kode;
    }

    // ===== Kirim WhatsApp (bahasa gaul) =====
    $waSent   = false;
    $waDetail = null;
    $errMsg   = null;

    // Hitung masa berlaku (semua kode dibuat di waktu yang sama → expiry sama)
    $expired = (clone $now)->modify('+' . $batasHariV . ' days');

    // Format tanggal WITA (pakai helper jika ada)
    if (method_exists($this, '_fmt_id_wita')) {
      $expired_str = $this->_fmt_id_wita($expired, true); // dengan hari
    } else {
      // fallback
      $bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
      $hari  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
      $expired_str = $hari[(int)$expired->format('w')] . ', ' . (int)$expired->format('j') . ' ' . $bulan[(int)$expired->format('n')] . ' ' . $expired->format('Y H.i') . ' WITA';
    }

    // Susun pesan
    $sapaan = trim((string)($stats['nama'] ?? ''));
    $sapaan = $sapaan !== '' ? "Halo Kak *{$sapaan}* 👋" : "Halo Kak 👋";
    $lines = [];
    $lines[] = $sapaan;
    $lines[] = "Makasih udah sering main di *{$siteName}* 🙏";
    $lines[] = "Kamu udah ngumpulin *{$stats['confirmed']}x* transaksi. Sesuai promo, tiap *{$stats['batas']}x* dapat *1 voucher Free Main*.";
    $lines[] = "";
    $lines[] = "Nih *voucher baru* buat kamu:";
    foreach ($codes as $c) {
      $lines[] = "• *{$c}*";
    }
    $lines[] = "";
    $lines[] = "Berlaku sampai: *{$expired_str}*";
    $lines[] = "Buat pakainya, tinggal booking dan jangan lupa masukkan vouchernya ya 😉";
    $lines[] = "";
    $lines[] = "Cek daftar voucher kamu di sini:";
    $lines[] = site_url('billiard/daftar_voucher');
    $lines[] = "Jika link tidak dapat diklik, simpan kontak ini.";
    $lines[] = "";
    $lines[] = "Pesan ini dikirim otomtis oleh sistem ".$web->nama_website;

    $pesan = implode("\n", $lines);

    try {
      if (function_exists('send_wa_single')) {
        // kirim ke nomor dalam bentuk 62xxxxxxxxx (tanpa tanda +)
        $res = send_wa_single($hp_norm, $pesan);
        $waSent   = true;
        $waDetail = is_string($res) ? $res : json_encode($res);
      } else {
        $errMsg = 'send_wa_single() tidak tersedia';
        log_message('error', 'WA voucher: function send_wa_single tidak ditemukan.');
      }
    } catch (Throwable $e) {
      $errMsg = $e->getMessage();
      log_message('error', 'WA voucher error: '.$e->getMessage());
    }

    $created[] = [
      'no_hp_norm'    => $hp_norm,
      'made'          => $to_make,
      'codes'         => $codes,
      'confirmed'     => $stats['confirmed'],
      'batas'         => $stats['batas'],
      'should'        => $stats['should'],
      'issued_before' => $stats['issued'],
      'issued_after'  => $stats['issued'] + $to_make,
      'expired_at'    => $expired->format('Y-m-d H:i:s').' WITA',
      'wa'            => [
        'sent'   => $waSent,
        'detail' => $waDetail,
        'error'  => $errMsg,
      ],
    ];
  }

  return $this->_json([
    'success'     => true,
    'message'     => 'Voucher issuance job executed',
    'batas_edit'  => $batas,
    'batas_hari'  => $batasHariV,
    'result'      => $created,
  ]);
}

/**
 * Tampilkan daftar voucher untuk nomor tertentu (JSON).
 * GET: no_hp=08xxxxxxxxxx
 */
public function voucher_list() {
  $this->_nocache_headers();
  $this->_ensure_voucher_table();

  $hp = trim((string)$this->input->get('no_hp', TRUE));
  $hp_norm = $this->_normalize_msisdn($hp);
  if ($hp_norm === '') {
    return $this->_json(['success'=>false,'title'=>'Nomor kosong','pesan'=>'Parameter ?no_hp wajib diisi (10–13 digit).'], 400);
  }

  $stats = $this->_voucher_stats_by_hp($hp_norm);
  if (!$stats['ok']) {
    return $this->_json(['success'=>false,'title'=>'Invalid','pesan'=>$stats['reason']], 400);
  }

  $rows = $this->db->select('kode_voucher, jenis, status, is_claimed, created_at, claimed_at, notes')
    ->from('voucher_billiard')
    ->where(['no_hp_norm'=>$hp_norm,'jenis'=>'FREE_MAIN'])
    ->order_by('created_at','DESC')->get()->result();

  return $this->_json([
    'success' => true,
    'hp_norm' => $hp_norm,
    'nama'    => $stats['nama'],
    'summary' => [
      'batas_edit' => $stats['batas'],
      'total_terkonfirmasi' => $stats['confirmed'],
      'voucher_seharusnya'  => $stats['should'],
      'voucher_terbit'      => $stats['issued'],
      'voucher_belum_klaim' => $stats['unused'],
      'kurang'              => $stats['missing'],
    ],
    'vouchers' => $rows,
  ]);
}

/**
 * Klaim voucher (testing): POST kode, no_hp
 */
public function voucher_claim_test() {
  $this->_nocache_headers();
  $kode = trim((string)$this->input->post('kode', TRUE));
  $hp   = trim((string)$this->input->post('no_hp', TRUE));
  $hp_norm = $this->_normalize_msisdn($hp);

  if ($kode === '' || $hp_norm === '') {
    return $this->_json(['success'=>false,'title'=>'Invalid','pesan'=>'kode & no_hp wajib.'], 400);
  }

  $row = $this->db->get_where('voucher_billiard', [
    'kode_voucher' => $kode,
    'no_hp_norm'   => $hp_norm,
    'jenis'        => 'FREE_MAIN',
    'is_claimed'   => 0,
    'status'       => 'baru'
  ])->row();

  if (!$row) {
    return $this->_json(['success'=>false,'title'=>'Tidak ditemukan','pesan'=>'Voucher sudah dipakai / tidak cocok dengan nomor ini.'], 404);
  }

  $this->db->where('id_voucher', (int)$row->id_voucher)->update('voucher_billiard', [
    'is_claimed' => 1,
    'status'     => 'terpakai',
    'claimed_at' => date('Y-m-d H:i:s'),
  ]);

  return $this->_json(['success'=>true,'title'=>'OK','pesan'=>'Voucher ditandai terpakai.']);
}

/** Pretty format no HP dari 62… → 0… */
private function _pretty_hp(string $hp): string {
  $d = preg_replace('/\D+/', '', $hp);
  if ($d === '') return '';
  if (strpos($d, '62') === 0) return '0' . substr($d, 2);
  return (strpos($d, '0') === 0) ? $d : $d;
}

/** Halaman daftar voucher (render ke view) */
/** Tampilkan daftar voucher dalam kartu (pakai template card-box) */
/** Daftar voucher versi kartu, tanggal Indonesia + WITA, tanpa filter */
/** Daftar voucher versi kartu, tanggal Indonesia + WITA, hanya yang belum diklaim */
public function daftar_voucher(){
  $this->_nocache_headers();
  if (method_exists($this, '_ensure_voucher_table')) $this->_ensure_voucher_table();

  $web       = $this->fm->web_me();
  $batasHari = (int)($web->batas_hari ?? 30);
  if ($batasHari < 1) $batasHari = 30;

  // HANYA voucher yang belum diklaim
  // sebelum ->get()->result();
$days   = $this->_voucher_expiry_days();
$tz     = new DateTimeZone('Asia/Makassar');
$cutoff = (new DateTime('now',$tz))->modify('-'.$days.' days')->format('Y-m-d H:i:s');

$rows = $this->db->select('id_voucher,nama,no_hp,no_hp_norm,kode_voucher,created_at,status,is_claimed')
                 ->from('voucher_billiard')
                 ->where('jenis','FREE_MAIN')
                 ->where('is_claimed', 0)
                 ->where('status', 'baru')           // hanya yang aktif
                 ->where('created_at >=', $cutoff)   // tidak lewat masa berlaku
                 ->order_by('created_at','DESC')
                 ->get()->result();


  // $tz  = new DateTimeZone('Asia/Makassar'); // WITA
  $now = new DateTime('now', $tz);

  // Helper tampilan no HP 62.. → 08..
  $prettyHp = function($hp){
    $d = preg_replace('/\D+/', '', (string)$hp);
    if ($d === '') return '';
    if (strpos($d,'62') === 0) return '0'.substr($d,2);
    return (strpos($d,'0') === 0) ? $d : $d;
  };

  $cards = [];
  foreach ($rows as $r){
    $created = DateTime::createFromFormat('Y-m-d H:i:s', (string)$r->created_at, $tz);
    if (!$created) { try { $created = new DateTime((string)$r->created_at, $tz); } catch(Throwable $e){ $created = clone $now; } }
    $expired = (clone $created)->modify('+'.$batasHari.' days');

    $cards[] = (object)[
      'nama'        => (string)($r->nama ?: 'Tanpa Nama'),
      'no_hp'       => $prettyHp($r->no_hp ?: $r->no_hp_norm),
      'kode'        => (string)$r->kode_voucher,
      'created_at'  => $this->_fmt_id_wita($created),
      'expired_at'  => $this->_fmt_id_wita($expired),
      'is_expired'  => (int)($now > $expired),
      'is_claimed'  => 0, // sudah difilter
      'status'      => (string)$r->status,
    ];
  }

  $data = [
    "controller" => get_class($this),
    "title"      => "Gratis Main Billiard",
    "deskripsi"  => "Voucher Free Main (belum diklaim)",
    "prev"       => base_url("assets/images/billiard.webp"),
    "rec"        => $web,
    "batas_hari" => $batasHari,
    "vouchers"   => $cards,
  ];

  $this->load->view('billiard/daftar_voucher', $data);
}

/** Format tanggal Indonesia + WITA. Contoh: 26 Oktober 2025 14.05 WITA */
private function _fmt_id_wita(DateTime $dt, bool $with_day = false): string {
  $dt = (clone $dt)->setTimezone(new DateTimeZone('Asia/Makassar')); // WITA
  $hari  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  $bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
  $d  = (int)$dt->format('j');
  $m  = (int)$dt->format('n');
  $y  = $dt->format('Y');
  $hm = $dt->format('H.i'); // gaya Indonesia pakai titik
  $dateStr = $d.' '.$bulan[$m].' '.$y.' '.$hm.' WITA';
  return $with_day ? ($hari[(int)$dt->format('w')].', '.$dateStr) : $dateStr;
}

/** Ambil jumlah hari berlaku voucher dari identitas (default 30) */
private function _voucher_expiry_days(): int {
  $web = $this->fm->web_me();
  $d   = (int)($web->batas_hari ?? 30);
  return $d > 0 ? $d : 30;
}

/**
 * CRON JOB: Auto-expire voucher
 * Menandai voucher yg: jenis=FREE_MAIN, status=baru, is_claimed=0,
 * dan created_at < (NOW - batas_hari) → status='batal' (expired).
 */
public function voucher_expire_job() {
  $this->_nocache_headers();
  $this->_ensure_voucher_table();

  $days = $this->_voucher_expiry_days();
  $tz   = new DateTimeZone('Asia/Makassar'); // WITA
  $now  = new DateTime('now', $tz);
  $cutoff = (clone $now)->modify('-'.$days.' days')->format('Y-m-d H:i:s');

  // Hitung kandidat yang akan di-expire
  $this->db->from('voucher_billiard')
           ->where('jenis','FREE_MAIN')
           ->where('status','baru')
           ->where('is_claimed', 0)
           ->where('created_at <', $cutoff);
  $candidates = (int)$this->db->count_all_results();

  // Siapkan update
  $upd = [
    'status' => 'batal', // tandai expired
    'notes'  => 'Auto expired (batas_hari='.$days.')',
  ];
  // Jika punya kolom expired_at, isi juga
  if ($this->db->field_exists('expired_at', 'voucher_billiard')) {
    $upd['expired_at'] = $now->format('Y-m-d H:i:s');
  }

  // Eksekusi update massal
  $this->db->where('jenis','FREE_MAIN')
           ->where('status','baru')
           ->where('is_claimed', 0)
           ->where('created_at <', $cutoff)
           ->update('voucher_billiard', $upd);

  $affected = $this->db->affected_rows();

  return $this->_json([
    'success'      => true,
    'message'      => 'Auto-expire executed',
    'batas_hari'   => $days,
    'cutoff_lt'    => $cutoff.' WITA',
    'candidates'   => $candidates,
    'expired_now'  => $affected,
  ]);
}


}
