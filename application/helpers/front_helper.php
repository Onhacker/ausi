<?php 
function cek_session_on_login_web(){
	$ci = & get_instance();
	if( $ci->session->userdata('web_login') == false 
		and !$ci->session->userdata("web_username")  
		and !$ci->session->userdata("web_level")
		and !$ci->session->userdata("web_attack")
		and !$ci->session->userdata("web_permisson")
		and !$ci->session->userdata("web_session") ) {
		redirect('forum/logout');
	}
}

function generate_qr_payload($kode_booking) {
    $secret_key = "SILAPAS_SECRET"; // ganti pakai key rahasia
    
    $timestamp = time();
    $checksum  = md5($kode_booking . $secret_key . $timestamp);

    // Format string payload
    $payload = "BOOK|{$kode_booking}|{$timestamp}|{$checksum}";

    return $payload;
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
            $path = $map[$kat] ?? 'assets/logo/default.png';
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

    if (!function_exists('send_wa_single')) {
    	function send_wa_single($wa, $pesan) {
    		$CI =& get_instance();
    		$CI->load->library('Wa_client');
    		return $CI->wa_client->send_single($wa, $pesan);
    	}
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


function tahun_view($tgl){
	$tahun = substr($tgl,6,4);
	return $tahun;       
}

function bulan_view_only($tgl){
	$tmp = explode("-", $tgl);
	$bln = intval($tmp[1]);
	$arr_bln = array(1=>"Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober",
		"November","Desember");

	$ret = ucwords($arr_bln[$bln]);
	return $ret;    
}

function bulan_view($tgl){
	$tmp = explode("-", $tgl);
	$bln = intval($tmp[1]);
	$arr_bln = array(1=>"Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober",
		"November","Desember");

	$ret = ucwords($arr_bln[$bln])." ".$tmp[0];
	return $ret;    
}


function Size($path){
	$bytes = sprintf('%u', filesize($path));
	if ($bytes > 0){
		$unit = intval(log($bytes, 1000));
		$units = array('B', 'KB', 'MB', 'GB');

		if (array_key_exists($unit, $units) === true){
			return sprintf('%d %s', $bytes / pow(1000, $unit), $units[$unit]);
		}
	}
	return $bytes;
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

function nama_file($linker,$class){
	$ci = & get_instance();
	$ci->db->where("id_identitas", "1");
	$web = $ci->db->get("identitas")->row();
	$path = str_replace("forum_", "", $class);
	return $path."-on-".$linker."-".substr(md5(date("Ymdhis")), 0,10);
}

function rec(){
	$ci = & get_instance();
	$ak["tanggal"] = date("Y-m-d H:i:s");
	$ak["username"] = $ci->session->userdata("admin_username");
	$ak["query"] = $ci->db->last_query();
	$ci->db->insert("aktifitas",$ak);
}

function onhacker_path($x){
	$ci = & get_instance();
	$ci->db->where("aktif", "Y");
	$res = $ci->db->get("templates")->row();
	return base_url("frontend/".$res->folder."/".$x);
}

function onhacker($x){
	$ci = & get_instance();
	$ci->db->where("aktif", "Y");
	$res = $ci->db->get("templates")->row();
	return $res->folder."/".$x."_view";
}

function onhacker_view($x){
	$ci = & get_instance();
	$ci->db->where("aktif", "Y");
	$res = $ci->db->get("templates")->row();
	return $res->folder."/".$x;
}

function uang($x) {
	return "".number_format($x,0,',','.');
}

function judul_split($judul,$split){
	$validasi = strip_tags(htmlentities($judul, ENT_QUOTES, 'UTF-8'));
	if (strlen($validasi) > $split) {
		$jdl = substr($judul, 0,$split)." ...";
	} else {
		$jdl = $validasi;
	}
	return $jdl;
}

function isi($isi){
	$validasi = html_entity_decode($isi, ENT_QUOTES, 'UTF-8');
	return $validasi;
	
}

function isi_split($isi,$split){
	$validasi = strip_tags(html_entity_decode($isi, ENT_QUOTES, 'UTF-8'));
	if (strlen($validasi) > $split) {
		$isi = substr($validasi, 0,$split)." ...";
	} else {
		$isi = $validasi;
	}
	return $isi;
	
}

function penulis($p){
	$ex = explode(" ", $p);
	if (strlen($ex[0]) > 6) {
		$name = substr($ex[0], 0,6)." ...";
	} else {
		$name = $ex[0];
	}
	return $name;
}

function url($link = "",$modul = "") {
	$ci = & get_instance();
	$ci->db->where("link", $modul);
	$ci->db->where("aktif", "Y");
	$ci->db->where("publish", "Y");
	$res = $ci->db->get("modul")->row();
	return site_url($res->static_content."/").$link;
}

function on_seo($modul = "") {
	$ci = & get_instance();
	$ci->db->where("link", $modul);
	$ci->db->where("aktif", "Y");
	$ci->db->where("publish", "Y");
	$res = $ci->db->get("modul")->row();
	return $res->static_content;
}

function link_foto_user_web($link = ""){
	if ($link == "") {
		$gbr = base_url("upload/users/onhacker_221a3f5e.jpg");
	} else {
		$gbr = base_url("upload/users/".$link);
	}
	return $gbr;
}


function link_gambar($link = ""){
	$ci = & get_instance();
	$no_image = $ci->fm->web_me()->gambar;
	if ($link == "") {
		$gbr = base_url("upload/gambar/onhacker_221a3f5e.jpg");
	} else {
		$gbr = base_url("upload/gambar/".$link);
	}
	return $gbr;
}

function link_gambar_logo($link = ""){
	$ci = & get_instance();
	$no_image = $ci->fm->web_me()->gambar;
	if ($link == "") {
		$gbr = base_url("assets/images/".$no_image);
	} else {
		$gbr = base_url("upload/gambar/".$link);
	}
	return $gbr;
}

function tgl_view($tgl){
	$tanggal = substr($tgl,8,2);
	$bulan = substr($tgl,5,2);
	$tahun = substr($tgl,0,4);
	return $tanggal.'-'.$bulan.'-'.$tahun;       
}

function tgl_df($tgl){
	$re = explode(" ", $tgl);
	$tanggal = substr($re[0],8,2);
	$bulan = substr($re[0],5,2);
	$tahun = substr($re[0],0,4);
	return $tanggal.'-'.$bulan.'-'.$tahun." Pukul ".$re[1];       
}

function tgl_simpan($tgl){
	$tanggal = substr($tgl,0,2);
	$bulan = substr($tgl,3,2);
	$tahun = substr($tgl,6,4);
	return $tahun.'-'.$bulan.'-'.$tanggal;       
}

function tgl_indo($tgl){
	$tmp = explode("-", $tgl);
	$bln = intval($tmp[1]);

	$arr_bln = array(1=>"Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober",
		"November","Desember");

	$ret = $tmp[2]." ".ucwords($arr_bln[$bln])." ".$tmp[0];
	return $ret;

}

function cetak($str){
	return strip_tags(htmlentities($str, ENT_QUOTES, 'UTF-8'));
}

function cetak_meta($str,$mulai,$selesai){
	return strip_tags(html_entity_decode(substr(str_replace('"','',$str),$mulai,$selesai), ENT_COMPAT, 'UTF-8'));
}

function sensor($teks){
	$ci = & get_instance();
	$query = $ci->db->query("SELECT * FROM katajelek");
	foreach ($query->result_array() as $r) {
		$teks = str_replace($r['kata'], $r['ganti'], $teks);       
	}
	return $teks;
}  

function Terbilang($nilai) {
	$huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
	if($nilai==0){
		return "";
	}elseif ($nilai < 12&$nilai!=0) {
		return "" . $huruf[$nilai];
	} elseif ($nilai < 20) {
		return Terbilang($nilai - 10) . " Belas ";
	} elseif ($nilai < 100) {
		return Terbilang($nilai / 10) . " Puluh " . Terbilang($nilai % 10);
	} elseif ($nilai < 200) {
		return " Seratus " . Terbilang($nilai - 100);
	} elseif ($nilai < 1000) {
		return Terbilang($nilai / 100) . " Ratus " . Terbilang($nilai % 100);
	} elseif ($nilai < 2000) {
		return " Seribu " . Terbilang($nilai - 1000);
	} elseif ($nilai < 1000000) {
		return Terbilang($nilai / 1000) . " Ribu " . Terbilang($nilai % 1000);
	} elseif ($nilai < 1000000000) {
		return Terbilang($nilai / 1000000) . " Juta " . Terbilang($nilai % 1000000);
	}elseif ($nilai < 1000000000000) {
		return Terbilang($nilai / 1000000000) . " Milyar " . Terbilang($nilai % 1000000000);
	}elseif ($nilai < 100000000000000) {
		return Terbilang($nilai / 1000000000000) . " Trilyun " . Terbilang($nilai % 1000000000000);
	}elseif ($nilai <= 100000000000000) {
		return "Maaf Tidak Dapat di Prose Karena Jumlah nilai Terlalu Besar ";
	}
}

function cvr($s) {
	$c = array (' ');
	$d = array ('&amp;','amp;','nbsp;','&nbsp;','-','/','\\',',','.','#',':',';','\'','"','[',']','{','}',')','(','|','`','~','!','@','%','$','^','&','*','=','?','+','–');
	    $s = str_replace($d, '', $s); // Hilangkan karakter yang telah disebutkan di array $d
	    $s = strtolower(str_replace($c, ' ', $s)); // Ganti spasi dengan tanda - dan ubah hurufnya menjadi kecil semua
	    return $s;
	}

	function cek_terakhir($datetime, $full = false) {
		$today = time();    
		$createdday= strtotime($datetime); 
		$datediff = abs($today - $createdday);  
		$difftext="";  
		$years = floor($datediff / (365*60*60*24));  
		$months = floor(($datediff - $years * 365*60*60*24) / (30*60*60*24));  
		$days = floor(($datediff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));  
		$hours= floor($datediff/3600);  
		$minutes= floor($datediff/60);  
		$seconds= floor($datediff);  
     //year checker  
		if($difftext=="")  
		{  
			if($years>1)  
				$difftext=$years." Tahun";  
			elseif($years==1)  
				$difftext=$years." Tahun";  
		}  
     //month checker  
		if($difftext=="")  
		{  
			if($months>1)  
				$difftext=$months." Bulan";  
			elseif($months==1)  
				$difftext=$months." Bulan";  
		}  
     //month checker  
		if($difftext=="")  
		{  
			if($days>1)  
				$difftext=$days." Hari";  
			elseif($days==1)  
				$difftext=$days." Hari";  
		}  
     //hour checker  
		if($difftext=="")  
		{  
			if($hours>1)  
				$difftext=$hours." Jam";  
			elseif($hours==1)  
				$difftext=$hours." Jam";  
		}  
     //minutes checker  
		if($difftext=="")  
		{  
			if($minutes>1)  
				$difftext=$minutes." Menit";  
			elseif($minutes==1)  
				$difftext=$minutes." Menit";  
		}  
     //seconds checker  
		if($difftext=="")  
		{  
			if($seconds>1)  
				$difftext=$seconds." Detik";  
			elseif($seconds==1)  
				$difftext=$seconds." Detik";  
		}  
		return $difftext;  
	}