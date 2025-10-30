<?php

function flipdate($tanggal){
    if(empty($tanggal)) {
        return "";
    }
    $tanggal = substr($tanggal, 0,10);
    $x = explode("-", $tanggal);
    $x[0] = isset($x[0])?$x[0]:"0";
    $x[1] = isset($x[1])?$x[1]:"0";
    $x[2] = isset($x[2])?$x[2]:"0";
    return $x[2]."-".$x[1]."-".$x[0];
}

function aslidate($tanggal){
    if(empty($tanggal)) {
        return "";
    }
    $tanggal = substr($tanggal, 0,10);
    $x = explode("-", $tanggal);
    $x[0] = isset($x[0])?$x[0]:"0";
    $x[1] = isset($x[1])?$x[1]:"0";
    $x[2] = isset($x[2])?$x[2]:"0";
    return $x[0]."-".$x[1]."-".$x[2];
}


function jam($tanggal){
    if(empty($tanggal)) {
        return "";
    }
    // $tanggal = substr($tanggal, 0,10);
    $x = explode(" ", $tanggal);
    // $x[0] = isset($x[0])?$x[0]:"0";
    // $x[1] = isset($x[1])?$x[1]:"0";
    // $x[2] = isset($x[2])?$x[2]:"0";
    return "Pukul ".$x[1]. " WITA";
}



function tahun($tanggal){
    if(empty($tanggal)) {
        return "";
    }

    $x = explode("-", $tanggal);
    $x[0] = isset($x[0])?$x[0]:"0";
    $x[1] = isset($x[1])?$x[1]:"0";
    $x[2] = isset($x[2])?$x[2]:"0";
    return $x[0];
}

function rupiah($x)
{
    return "Rp ".number_format($x,0,',','.');
}

function uang($x)
{
    return "".number_format($x,0,',','.');
}

function numb($x){
    return number_format($x, 2,",",".");
}
function numbx($x){
    return number_format($x, 4,",",".");
}
function numby($x){
    return number_format($x, 0,",",".");
}
function numb_koe($x){
    return number_format($x, 3,",",".");
}

function qwe($x){
    return number_format($x, 2,",",".");
} 
function tgl_indo($tgl){
    $tmp = explode("-", $tgl);
    $bln = intval($tmp[1]);

    $arr_bln = array(1=>"Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober",
        "November","Desember");

    $ret = $tmp[0]." ".ucwords($arr_bln[$bln])." ".$tmp[2];
    return $ret;

}

function tgl_indox($tgl){
    $tmp = explode("-", $tgl);
    $bln = intval($tmp[1]);

    $arr_bln = array(1=>"Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober",
        "November","Desember");

    $ret = $tmp[0]." ".$arr_bln[$bln]." ".$tmp[2];
    return $ret;

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


function bulatkan($angka)  
{  
    $exp = explode('.',$angka);  
    if(count($exp) == 2) {  
        $p = "0.".$exp[1];  
        if($p < 0.00) {    
            $t=0;
        } else if (($p > 0.01) AND ($p < 0.71)) {
            $t= 0.5;
        } else $t=1;  
        $r=$exp[0] + $t;  
        return $r;  
    } else return $angka;  
}

function bulatin($angka)  
{  
    $exp = explode('.',$angka);  
    if(count($exp) == 2) {  
        $p = "0.".$exp[1];  
        if($p < 0.00) {    
            $t=0;
        } else if (($p > 0.01) AND ($p < 0.99)) {
            $t= 0.5;
        } else $t=1;  
        $r=$exp[0] + $t;  
        return $r;  
    } else return $angka;  
} 


// function pembulatan($uang){
//  $ratusan = substr($uang, -2);
//  $akhir = $uang + (100-$ratusan);
//  return $akhir;
// }

function pembulatan($uangx)
{
   $uang =  round($uangx);
    $ratusan = substr($uang, -3);
    if($ratusan<500)
       $akhir = $uang - $ratusan;
   else
       $akhir = $uang + (1000-$ratusan);
 // return $akhir;
   echo number_format($akhir, 0, ',', '.');;
}

function pembulatanc($uangx)
{
   $uang =  round($uangx);
    $ratusan = substr($uang, -2);
    if($ratusan<50)
       $akhir = $uang - $ratusan;
   else
       $akhir = $uang + (100-$ratusan);
 return $akhir;
   // echo number_format($akhir, 0, ',', '.');;
}


function pembulatanx($uangx)
{
   $uang =  round($uangx);
    $ratusan = substr($uang, -3);
    if($ratusan<500)
       $akhir = $uang - $ratusan;
   else
       $akhir = $uang + (1000-$ratusan);
 return $akhir;
   // echo number_format($akhir, 0, ',', '.');;
}

function no_bulat($x){
    return $x;
} 

function show_array($arr) {
    echo "<pre>";
    print_r($arr);
    echo "</pre>";
}

function duit($x){
    $ratusan = substr($x, -3);

    if($ratusan<500)
        $akhir = $x - $ratusan;
    else
        $akhir = $x + (1000-$ratusan);
    return $akhir;
}

function duit_a($x){
    $ratusan = substr($x, -3);
    if ($ratusan==0) 
        $akhir = $x;
    else
        $akhir = $x + (1000-$ratusan);
    return $akhir;
}

function duit_b($x){
    $ratusan = substr($x, -3);
    $akhir = $x - $ratusan;
    return $akhir;
}

function duit_n($x){
    return $x;
}

function nama_desa($str) {
    $tmp  = explode(" ", $str);

    $arr = array("I","II","III","IV","V","VI","VII","VIII");

    $max_array = count($tmp) - 1;


    if(in_array($tmp[$max_array],$arr))
    {
        $suffix = $tmp[$max_array]; 
        $nama_desa = "";

        for($i=0; $i<$max_array; $i++) { 

            $nama_desa .=$tmp[$i]." ";
        }   

        $nama_desa = ucwords(strtolower($nama_desa))." ".$tmp[count($tmp)-1]; 
        //return $nama_desa; 

    }
    else {
        $nama_desa = ucwords(strtolower($str));
    }

    return $nama_desa;


    
}



function copyr($source, $dest) {
    // Check for symlinks
    if (is_link($source)) {
        return symlink(readlink($source), $dest);
    }
    
    // Simple copy for a file
    if (is_file($source)) {
        return copy($source, $dest);
    }

    // Make destination directory
    if (!is_dir($dest)) {
        mkdir($dest);
    }

    // Loop through the folder
    $dir = dir($source);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Deep copy directories
        copyr("$source/$entry", "$dest/$entry");
    }

    // Clean up
    $dir->close();
    return true;
}
function cek_session_akses($link,$staff){
    $ci = & get_instance();
    $session = $ci->db->query("SELECT * FROM modul,operator_modul WHERE modul.id_modul=operator_modul.id_modul AND operator_modul.staff='$staff' AND modul.link='$link'")->num_rows();
    if ($session == '0' AND $ci->session->userdata('operator_level') != 'admin'){
            // redirect(site_url().'home');
        echo "<script type='text/javascript' language='javascript'>
        alert('Maaf anda tidak dapat mengakses ini');
        </script>
        <meta http-equiv='refresh' content='0; url=".site_url()."home'>";
    }
}

function cek_session_admin(){
    $ci = & get_instance();
    $session = $ci->session->userdata('operator_level');
    if ($session != 'admin'){
            // redirect(base_url());
        echo "<script type='text/javascript' language='javascript'>
        alert('Maaf anda tidak dapat mengakses ini');
        </script>
        <meta http-equiv='refresh' content='0; url=".site_url()."home'>";
    }
}


?>