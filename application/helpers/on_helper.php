<?php 
    function jam($tanggal){
    if(empty($tanggal)) {
            return "";
        }
        $x = explode(" ", $tanggal);
        return "Pukul ".$x[1]. " WITA";
    }

    function ucapan(){
        $jam = date("H");

        if ($jam >= 5 && $jam < 12) {
            $ucapan = "Selamat Pagi 🌅";
        } elseif ($jam >= 12 && $jam < 15) {
            $ucapan = "Selamat Siang ☀️";
        } elseif ($jam >= 15 && $jam < 18) {
            $ucapan = "Selamat Sore 🌇";
        } else {
            $ucapan = "Selamat Malam 🌙";
        }
        return $ucapan;
    }

    if (!function_exists('kategori_logo_url')) {
          function kategori_logo_url($kategori){
            $CI =& get_instance();
            $CI->load->helper('url');

            $kat = strtolower(trim((string)$kategori));
            $map = [
              'opd'    => 'assets/images/logo/opd_sulsel.svg',
              'pn'     => 'assets/images/logo/mahkamah_agung.svg',
              'pa'     => 'assets/images/logo/mahkamah_agung.svg',
              'ptun'   => 'assets/images/logo/mahkamah_agung.svg',
              'kejari' => 'assets/images/logo/kejaksaan_ri.svg',
              'cabjari'=> 'assets/images/logo/kejaksaan_ri.svg',
              'bnn'    => 'assets/images/logo/bnn.svg',
              'kodim'  => 'assets/images/logo/tni_ad.svg',
              'kejati' => 'assets/images/logo/kejaksaan_ri.svg',
              'kepolisian' => 'assets/images/logo/kepolisian.png',

            ];
            $path = $map[$kat] ?? 'assets/images/maskable_icon_new_192.png';
            return base_url($path);
          }
        }

        if (!function_exists('kategori_label')) {
          function kategori_label($kategori){
            $kat = strtolower(trim((string)$kategori));
            $label = [
              'opd'=>'OPD Sulawesi Selatan', 'pn'=>'Pengadilan Negeri', 'pa'=>'Pengadilan Agama',
              'ptun'=>'PTUN', 'kejari'=>'Kejaksaan Negeri', 'cabjari'=>'Cabang Kejaksaan Negeri',
              'bnn'=>'BNN', 'kodim'=>'Kodim', 'kejati'=>'Kejaksaan Tinggi', 'kepolisian'=>'Kepolisian'
            ];
            return $label[$kat] ?? strtoupper($kat ?: 'LOGO');
          }
        }

    function no_wa($wa){
        $wa = preg_replace('/[^0-9]/', '', $wa); // hapus semua karakter selain angka

        // Ganti awalan 0, 62, +62 jadi 62
        if (preg_match('/^0/', $wa)) {
            $wa = preg_replace('/^0/', '62', $wa);
        } elseif (preg_match('/^62/', $wa)) {
            // sudah benar, tidak perlu diubah
        } elseif (preg_match('/^620/', $wa)) {
            $wa = preg_replace('/^620/', '62', $wa);
        } elseif (preg_match('/^8/', $wa)) {
            $wa = '62' . $wa;
        }
        return $wa;
    }

    if (!function_exists('send_wa_single')) {
        function send_wa_single($wa, $pesan) {
            $CI =& get_instance();
            $CI->load->library('Wa_client');
            return $CI->wa_client->send_single($wa, $pesan);
        }
    }

   function generate_custom_id() {
        $ci = &get_instance(); // akses CI instance di helper
        $id_desa = preg_replace('/[^a-zA-Z0-9]/', '', $ci->session->userdata('id_desa')); // bersihkan simbol aneh

        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

        return strtolower($id_desa.'_'.date("YmdHis").'_'. $uuid);
    }
    
    function generate_kode_tracking_8() {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $length = 8;
        $kode_tracking = '';
        for ($i = 0; $i < $length; $i++) {
            $kode_tracking .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $kode_tracking;
    }

    function generate_kode_tracking_unique_8() {
        $CI =& get_instance();
        do {
            $kode = generate_kode_tracking_8();
            $cek = $CI->db->where("id_pemohon", $kode)->get("view_semua_paket")->num_rows();
        } while ($cek > 0); // Ulangi jika sudah ada yang pakai
        return $kode;
    }


    function generate_nomor_registrasi($table, $field = 'no_registrasi_pemohon', $prefix = 'REG') {
        $CI =& get_instance();
        $tahun = date('Y');
        $ex = explode("_", $table);
        $kode = strtoupper($ex[1]);

        $CI->db->trans_start();

        $CI->db->select($field);
        $CI->db->from($table);
        $CI->db->like($field, "$prefix-$kode-$tahun", 'after');
        $CI->db->order_by($field, 'DESC');
        $CI->db->limit(1);
        $query = $CI->db->get();

        if ($query->num_rows() > 0) {
            $last_code = trim($query->row()->$field);
            $last_parts = explode('-', $last_code);
            $last_number = isset($last_parts[3]) ? (int) $last_parts[3] : 0;
            $new_number = $last_number + 1;
        } else {
            $new_number = 1;
        }

        $no_reg = $prefix . '-' . $kode . '-' . $tahun . '-' . str_pad($new_number, 6, '0', STR_PAD_LEFT);

        $CI->db->trans_complete();

        return ($CI->db->trans_status() === FALSE) ? false : $no_reg;
    }


    function hari($tanggal = null)
    {
    // Jika tidak ada parameter, gunakan tanggal hari ini
        $tanggal = $tanggal ?? date('Y-m-d');

    // Ambil nama hari dalam bahasa Inggris
        $hariInggris = date('l', strtotime($tanggal));

    // Daftar konversi nama hari
        $daftarHari = [
            'Sunday'    => 'Minggu',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu'
        ];

    // Kembalikan nama hari dalam bahasa Indonesia
        return $daftarHari[$hariInggris] ?? 'Tidak diketahui';
    }


    function hitungLamaProses($create_date, $create_time, $update_time) {
    // Gabungkan tanggal & waktu awal
        $start = new DateTime($create_date . ' ' . $create_time);

    // Buat objek waktu selesai dari update_time (format: Y-m-d H:i:s)
        $end = new DateTime($update_time);

    // Hitung selisihnya
        $diff = $start->diff($end);

    // Format hasil: X hari X jam X menit X detik
        $lama = '';
        if ($diff->d > 0) $lama .= $diff->d . ' hari ';
        if ($diff->h > 0) $lama .= $diff->h . ' jam ';
        if ($diff->i > 0) $lama .= $diff->i . ' menit ';
        if ($diff->s > 0 || $lama == '') $lama .= $diff->s . ' detik';

        return trim($lama);
    }

    if (!function_exists('waktu_lalu')) {
        function waktu_lalu($datetime, $full = false) {
            $now = new DateTime;
            $ago = new DateTime($datetime);
            $diff = $now->diff($ago);

            $diff->w = floor($diff->d / 7);
            $diff->d -= $diff->w * 7;

            $string = [
                'y' => 'tahun',
                'm' => 'bulan',
                'w' => 'minggu',
                'd' => 'hari',
                'h' => 'jam',
                'i' => 'menit',
                's' => 'detik',
            ];
            foreach ($string as $k => &$v) {
                if ($diff->$k) {
                    $v = $diff->$k . ' ' . $v;
                } else {
                    unset($string[$k]);
                }
            }

            if (!$full) $string = array_slice($string, 0, 1);
            return $string ? implode(', ', $string) . ' yang lalu' : 'baru saja';
        }
    }


    function ye($a){
        if ($a == "0") {
            $b =  "<b class = 'text-danger'>0</b>";
        } else {
            $b =  "<b class = 'text-primary'>".uang($a)."</b>";
        }
        return $b;
    }

    function numb($x){
        
        if ($x == "100") {
            $y = "100";
        } else {
            $y = number_format($x, 1,",",".");
           
        }

        if ($y== "nan" or $y == "inf") {
            $h = " ";
        } else {
            $h = $y;
        }
       
        return $h;
    }

    function umur($tgl_lahir){

        $birthDt = new DateTime($tgl_lahir);
        $today = new DateTime('today');
        $y = $today->diff($birthDt)->y;
        $m = $today->diff($birthDt)->m;

        $d = $today->diff($birthDt)->d;
        if ($y == 0 and $m == 0 and $d == 0) {
             return "< 24 jam";
        } elseif ($y == 0 and $m == 0 ) {
             return $d . " hari";;
        } elseif ($y == 0) {
            return $m . " bulan " . $d . " hari";;
        } elseif ($m == 0) {
            return $y . " tahun " . $d . " hari";;
        }  elseif ($y > 5) {
           return $y. " tahun";
        } else {
            return $y . " tahun " . $m . " bulan " . $d . " hari";;
        }
        
    }

    function umur_bulan($tgl_lahir){
        $birthDt = new DateTime($tgl_lahir);
        $today = new DateTime('today');
        $y = $today->diff($birthDt)->y;
        $m = $today->diff($birthDt)->m;
        $d = $today->diff($birthDt)->d;
        return $m . " bulan " ;
    }

    

    
    function imun_berikut($id,$tgl_suntik){
        if ($id == "154" or $id == "137") {
            $ber = "<small class ='text-danger'>".bulan_view(date('Y-m-d', strtotime('+1 month', strtotime($tgl_suntik))))."</small><br><small class=' text-dark'>BCG dan Polio (1) </small>";
        } elseif ($id == "117" or $id == "127") {
            $ber = "<small class ='text-danger'>".bulan_view(date('Y-m-d', strtotime('+2 month', strtotime($tgl_suntik))))."</small><br><small class=' text-dark'>Polio (2) dan Pentavalen (1) </small>";
        } elseif ($id == "119" or $id == "126") {
            $ber = "<small class ='text-danger'>".bulan_view(date('Y-m-d', strtotime('+3 month', strtotime($tgl_suntik))))."</small><br><small class=' text-dark'>Polio (3) dan Pentavalen (2) </small>";
        } elseif ($id == "122" or $id == "128") {
            $ber = "<small class ='text-danger'>".bulan_view(date('Y-m-d', strtotime('+4 month', strtotime($tgl_suntik))))."</small><br><small class=' text-dark'>Polio (4), Pentavalen (3) dan IPV </small>";
        } elseif ($id == "130" or $id == "125" or $id == "121") {
            $ber = "<small class ='text-danger'>".bulan_view(date('Y-m-d', strtotime('+9 month', strtotime($tgl_suntik))))."</small><br><small class=' text-dark'>MR </small>";
        } elseif ($id == "124") {
            $ber = "<small class ='text-danger'>".bulan_view(date('Y-m-d', strtotime('+18 month', strtotime($tgl_suntik))))."</small><br><small class=' text-dark'>Pentavalen Lanjutan dan Campak Lanjutan </small>";
        } elseif ($id == "118" or $id == "129" or $id == "120") {
            $ber = "<small class ='text-danger'>".bulan_view(date('Y-m-d', strtotime('+18 month', strtotime($tgl_suntik))))."</small><br><small class=' text-dark'>MR Lanjutan/ Imunisasi BIAS </small>";
        } else {
            $ber = "-";
        }
        return $ber;
    }

    function drop_out($id,$tgl_suntik){
        $ber_hbo7 = date('Y-m-d', strtotime('+7 days', strtotime($tgl_suntik)));
        $bcg_p1 = date('Y-m-d', strtotime('+1 month', strtotime($tgl_suntik)));
        $p2_p1 = date('Y-m-d', strtotime('+2 month', strtotime($tgl_suntik)));
        if (date("Y-m-d") >= $ber_hbo7 and $id == "154") {
            $do = "<span class='badge bg-warning text-dark'>Drop Out HB0 7</span>";
        } elseif (date("Y-m-d") >= $bcg_p1 and  $id == "117" or $id == "127" ) {
            $do = "<span class='badge bg-warning text-dark'>Drop Out BCG dan Polio (1)</span>";
        } elseif (date("Y-m-d") >= $p2_p1 and $id == "119" or $id =="126") {
            $do = "<span class='badge bg-warning text-dark'>Drop Out Pentavalen (1) dan Polio (2)</span>";
        }
        return $do;
    }

    function arr_vaksin_ibu($tt){
        if ($tt == "tt1") {
            $re = "TT Bumil 1";
        } elseif ($tt == "tt2") {
            $re = "TT Bumil 2";
        } elseif ($tt == "tt3") {
            $re = "TT Bumil 3";
        } elseif ($tt == "tt4") {
            $re = "TT Bumil 4";
        } elseif ($tt == "tt5") {
            $re = "TT Bumil 5";
        } elseif ($tt == "ttll") {
            $re = "TT Bumil LL";
        } elseif ($tt == "ttw1") {
            $re = "TT WUS Tidak Hamil 1";
        } elseif ($tt == "ttw2") {
            $re = "TT WUS Tidak Hamil 2";
        } elseif ($tt == "ttw3") {
            $re = "TT WUS Tidak Hamil 3";
        } elseif ($tt == "ttw4") {
            $re = "TT WUS Tidak Hamil 4";
        } elseif ($tt == "ttw5") {
            $re = "TT WUS Tidak Hamil 5";
        } elseif ($tt == "ttwll") {
            $re = "TT WUS Tidak Hamil LL";
        } 
        return $re;
    }


    function arr_vaksin_ibu_p($tt){
        if ($tt == "tt1") {
            $re = "Tetanus Toxoid Ibu Hamil 1";
        } elseif ($tt == "tt2") {
            $re = "Tetanus Toxoid Ibu Hamil 2";
        } elseif ($tt == "tt3") {
            $re = "Tetanus Toxoid Ibu Hamil 3";
        } elseif ($tt == "tt4") {
            $re = "Tetanus Toxoid Ibu Hamil 4";
        } elseif ($tt == "tt5") {
            $re = "Tetanus Toxoid Ibu Hamil 5";
        } elseif ($tt == "ttll") {
            $re = "Tetanus Toxoid Ibu Hamil LL";
        } elseif ($tt == "ttw1") {
            $re = "Tetanus Toxoid Wanita Usia Subur Tidak Hamil 1";
        } elseif ($tt == "ttw2") {
            $re = "Tetanus Toxoid Wanita Usia Subur Tidak Hamil 2";
        } elseif ($tt == "ttw3") {
            $re = "Tetanus Toxoid Wanita Usia Subur Tidak Hamil 3";
        } elseif ($tt == "ttw4") {
            $re = "Tetanus Toxoid Wanita Usia Subur Tidak Hamil 4";
        } elseif ($tt == "ttw5") {
            $re = "Tetanus Toxoid Wanita Usia Subur Tidak Hamil 5";
        } elseif ($tt == "ttwll") {
            $re = "Tetanus Toxoid Wanita Usia Subur Tidak Hamil LL";
        } 
        return $re;
    }

    function umur_simpan($tgl_lahir,$tgl_suntik){

        $birthDt = new DateTime($tgl_lahir);
        $today = new DateTime($tgl_suntik);
        $y = $today->diff($birthDt)->y;
        $m = $today->diff($birthDt)->m;

        $d = $today->diff($birthDt)->d;
        if ($y == 0 and $m == 0 and $d == 0) {
             return "< 24 jam";
        } elseif ($y == 0 and $m == 0 ) {
             return $d . " hari";;
        } elseif ($y == 0) {
            return $m . " bulan " . $d . " hari";;
        } elseif ($m == 0) {
            return $y . " tahun " . $d . " hari";;
        }  elseif ($y > 5) {
           return $y. " tahun";
        } else {
            return $y . " tahun " . $m . " bulan " . $d . " hari";;
        }
        
    }


    function umur_cetak($tgl_lahir){

        $birthDt = new DateTime($tgl_lahir);
        $today = new DateTime('today');
        $y = $today->diff($birthDt)->y;
        $m = $today->diff($birthDt)->m;

        $d = $today->diff($birthDt)->d;
        if ($y == 0 and $m == 0 and $d == 0) {
             return "< 24 jam";
        } elseif ($y == 0 and $m == 0 ) {
             return $d . " hr";;
        } elseif ($y == 0) {
            return $m . " bln " . $d . " hr";;
        } elseif ($m == 0) {
            return $y . " th " . $d . " hr";;
        }  elseif ($y > 5) {
           return $y. " th";
        } else {
            return $y . " th " . $m . " bln " . $d . " hr";;
        }
        
    }

    function ve($a){
        if ($a == "0") {
            $b =  "";
        } else {
            $b =  uang($a);
        }
        return $b;
    }

    function getSearchTermToBold($text, $words){
        preg_match_all('~[A-Za-z0-9_äöüÄÖÜ]+~', $words, $m);
        if (!$m)
            return $text;
        $re = '~(' . implode('|', $m[0]) . ')~i';
        return preg_replace($re, '<b style="color:red">$0</b>', $text);
    }

    function tgl_indo($tgl){
        $tmp = explode("-", $tgl);
        $bln = intval($tmp[1]);

        $arr_bln = array(1=>"Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober",
            "November","Desember");

        $ret = $tmp[2]." ".ucwords($arr_bln[$bln])." ".$tmp[0];
        return $ret;

    }

    function rawat($t){
        if ($t == "J") {
            return "Rawat Jalan";
        } else {
            return "Rawat Inap";
        }
    }

    function isi_split($isi,$split){
        $validasi = strip_tags(html_entity_decode($isi, ENT_QUOTES, 'UTF-8'));
        if (strlen($isi) > $split) {
            $isi = substr($isi, 0,$split)." ...";
        } else {
            $isi = $isi;
        }
        return $isi;

    }
    function penulis($p){
        $ex = explode(" ", $p);
        if (strlen($ex[0]) > 6) {
            return $name = substr($ex[0], 0,6)."...";
        } else {
            return $name = $ex[0];
        }
    }

    function uang($x) {
        return "".number_format($x,0,',','.');
    }

    function tgl_simpan($tgl){
            $tanggal = substr($tgl,0,2);
            $bulan = substr($tgl,3,2);
            $tahun = substr($tgl,6,4);
            return $tahun.'-'.$bulan.'-'.$tanggal;       
    }


    function tahun_view($tgl){
            $tahun = substr($tgl,6,4);
            return $tahun;       
    }

    function tgl_view($tgl){
            $tanggal = substr($tgl,8,2);
            $bulan = substr($tgl,5,2);
            $tahun = substr($tgl,0,4);
            return $tanggal.'-'.$bulan.'-'.$tahun;       
    }

    function date_time_view($tgl) {
        if (!$tgl) return '-';

        // Pecah kalau ada jam-nya
        $tanggalOnly = explode(" ", $tgl)[0]; // Ambil bagian tanggal saja

        $tanggal = substr($tanggalOnly, 8, 2);
        $bulan   = substr($tanggalOnly, 5, 2);
        $tahun   = substr($tanggalOnly, 0, 4);

        return $tanggal . '/' . $bulan . '/' . $tahun . ' '. explode(" ", $tgl)[1];
    }


    function bulan_view($tgl){
            $tmp = explode("-", $tgl);
            $bln = intval($tmp[1]);
            $arr_bln = array(1=>"Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober",
                "November","Desember");

            $ret = ucwords($arr_bln[$bln])." ".$tmp[0];
            return $ret;    
    }

    function bulan_view_only($tgl){
            $tmp = explode("-", $tgl);
            $bln = intval($tmp[1]);
            $arr_bln = array(1=>"Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober",
                "November","Desember");

            $ret = ucwords($arr_bln[$bln]);
            return $ret;    
    }

    function generateRandomString($length = 10) {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    } 

    function linker($l,$int = "") {
        if ($int == "0") {
            $c = array (' ');
            $d = array ('-','/','\\',',','.','#',':',';','\'','"','[',']','{','}',')','(','|','`','~','!','@','%','$','^','&','*','=','?','+','–');
            $l = str_replace($d, '', $l); 
            $l = strtolower(str_replace($c, '-', $l));
            return $l;
        } else {
            $acak = date("yy-m-d H:i:s");
            $acak = md5($acak);
            $acak = substr(preg_replace("/[^0-9]/", '', $acak),0,10);
            $serial = hash("sha512", md5($acak));
            $serial = substr(preg_replace("/[^0-9]/", '', $serial),0,6);

            $waktu = date("Y/m/d/");
            $c = array (' ');
            $d = array ('-','/','\\',',','.','#',':',';','\'','"','[',']','{','}',')','(','|','`','~','!','@','%','$','^','&','*','=','?','+','–');
            $l = str_replace($d, '', $l); 
            $l = strtolower(str_replace($c, '-', $l));
            return $waktu.$serial."/".$l;
        }
        
    }

    function buat_name($l,$int = "") {
        if ($int == "0") {
            $c = array (' ');
            $d = array ('-','/','\\',',','.','#',':',';','\'','"','[',']','{','}',')','(','|','`','~','!','@','%','$','^','&','*','=','?','+','–');
            $l = str_replace($d, '', $l); 
            $l = strtolower(str_replace($c, '_', $l));
            return $l;
        } else {
            $acak = date("yy-m-d H:i:s");
            $acak = md5($acak);
            $acak = substr(preg_replace("/[^0-9]/", '', $acak),0,10);
            $serial = hash("sha512", md5($acak));
            $serial = substr(preg_replace("/[^0-9]/", '', $serial),0,6);

            $waktu = date("Y/m/d/");
            $c = array (' ');
            $d = array ('-','/','\\',',','.','#',':',';','\'','"','[',']','{','}',')','(','|','`','~','!','@','%','$','^','&','*','=','?','+','–');
            $l = str_replace($d, '', $l); 
            $l = strtolower(str_replace($c, '_', $l));
            return $waktu.$serial."/".$l;
        }
        
    }

    function translate_bln($bulan){
        if ($bulan == "January") {
           $bln = "1";
        } elseif ($bulan == "February") {
            $bln = "2";
        } elseif ($bulan == "March") {
            $bln = "3";
        } elseif ($bulan == "April") {
            $bln = "4";
        } elseif ($bulan == "May") {
            $bln = "5";
        } elseif ($bulan == "June") {
            $bln = "6";
        } elseif ($bulan == "July") {
            $bln = "7";
        } elseif ($bulan == "August") {
            $bln = "8";
        } elseif ($bulan == "September") {
            $bln = "9";
        } elseif ($bulan == "October") {
            $bln = "10";
        } elseif ($bulan == "November") {
            $bln = "11";
        } elseif ($bulan == "December") {
            $bln = "12";
        }
        return $bln;
    }

    function hari_ini($tanggal){
        if(empty($tanggal)) {
            return "";
        }

        $x = explode("-", $tanggal);
        $x[0] = isset($x[0])?$x[0]:"0";
        $x[1] = isset($x[1])?$x[1]:"0";
        $x[2] = isset($x[2])?$x[2]:"0";
    // return $x[2]."-".$x[1]."-".$x[0];
        $day = date('D', strtotime($tanggal));
        $dayList = array(
            'Sun' => 'Minggu',
            'Mon' => 'Senin',
            'Tue' => 'Selasa',
            'Wed' => 'Rabu',
            'Thu' => 'Kamis',
            'Fri' => 'Jumat',
            'Sat' => 'Sabtu'
        );
        return $dayList[$day];

    }

    function getBulan($bln){
                switch ($bln){
                    case 1: 
                        return "Januari";
                        break;
                    case 2:
                        return "Februari";
                        break;
                    case 3:
                        return "Maret";
                        break;
                    case 4:
                        return "April";
                        break;
                    case 5:
                        return "Mei";
                        break;
                    case 6:
                        return "Juni";
                        break;
                    case 7:
                        return "Juli";
                        break;
                    case 8:
                        return "Agustus";
                        break;
                    case 9:
                        return "September";
                        break;
                    case 10:
                        return "Oktober";
                        break;
                    case 11:
                        return "November";
                        break;
                    case 12:
                        return "Desember";
                        break;
                }
            } 

function cek_terakhir($datetime, $full = false) {
    $today = time();    
    $createdday = strtotime($datetime); 
    $datediff = abs($today - $createdday);  
    $difftext = "";  
    
    // Menghitung tahun, bulan, hari, jam, menit, detik
    $years = floor($datediff / (365 * 60 * 60 * 24));  
    $months = floor(($datediff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));  
    $days = floor(($datediff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));  
    $hours = floor(($datediff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24) / 3600);  
    $minutes = floor(($datediff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 3600) / 60);  
    $seconds = $datediff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 3600 - $minutes * 60;

    // Menambahkan tahun, bulan, hari jika ada
    if ($years > 0) {
        $difftext .= $years . " Tahun ";
    }
    if ($months > 0) {
        $difftext .= $months . " Bulan ";
    }
    if ($days > 0) {
        $difftext .= $days . " Hari ";
    }
    if ($hours > 0) {
        $difftext .= $hours . " Jam ";
    }
    if ($minutes > 0) {
        $difftext .= $minutes . " Menit ";
    }
    if ($seconds >= 0) {
        $difftext .= $seconds . " Detik";
    }
    
    return $difftext;
}
