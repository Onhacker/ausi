<?php 
function cek_session_on_login(){
    $ci = & get_instance();
    // cukup cek flag inti
    if (!$ci->session->userdata('admin_login') || !$ci->session->userdata('admin_username')) {
        redirect('on_login/logout');
        exit;
    }
}

function url($link = "",$modul = "", $copy = "") {
    $ci = & get_instance();
    $ci->db->where("link", $modul);
    $ci->db->where("aktif", "Y");
    $ci->db->where("publish", "Y");
    $res = $ci->db->get("modul")->row();
    if ($copy == "copy") {
        return $res->static_content."/".$link;
    } else {
        return site_url($res->static_content."/").$link;
    }
}

function rec(){
    $d = 0;
    if ($d != 0) {
     $ci = & get_instance();
     $ak["tanggal"] = date("Y-m-d H:i:s");
     $ak["username"] = $ci->session->userdata("admin_username");
     $ak["query"] = $ci->db->last_query();
     $ci->db->insert("aktifitas",$ak);
 }
 
}

function cek_session_akses($link,$id){
    $ci = & get_instance();
    $ci->db->select('*');
    $ci->db->from("modul");
    $ci->db->join('users_modul', 'modul.id_modul = users_modul.id_modul');
    $ci->db->where("link", $link);
    $ci->db->where("id_session", $id);
    $session = $ci->db->get()->num_rows();
    if ($session == '0' AND $ci->session->userdata('admin_level') != 'admin' AND $ci->session->userdata('admin_level') != 'su'){
      redirect(base_url().'on_login/logout');
  }
}

function nama_file($linker,$class){
    $ci = & get_instance();
    $ci->db->where("id_identitas", "1");
    $web = $ci->db->get("identitas")->row();
    $path = str_replace("Admin_", "", $class);
    return $path."-on-".$linker."-".substr(md5(date("Ymdhis")), 0,10);
}

function link_post($link){
    $c = array (' ');
    $d = array ('-','/','\\',',','.','#',':',';','\'','"','[',']','{','}',')','(','|','`','~','!','@','%','$','^','&','*','=','?','+','–');
    $link = str_replace($d, '', $link); 
    $ci = & get_instance();
    $ci->db->where("judul", $link);
    $cek = $ci->db->get("berita");
    if ($cek->num_rows() >= 1) {
     $link = strtolower(str_replace($c, '-', $link))."-".($cek->num_rows() + 1);
 } else {
     $link = strtolower(str_replace($c, '-', $link));
     
 }
 return $link;
}


function valid($query){
    if ($this->session->userdata("admin_level")=='admin'){
        return $query;
    } else {
        $this->db->where("username", $this->session->userdata("admin_username"));
        return $query;
    }
}


function template(){
    $ci = & get_instance();
    $query = $ci->db->query("SELECT folder FROM templates where aktif='Y'");
    $tmp = $query->row_array();
    if ($query->num_rows()>=1){
        return $tmp['folder'];
    }else{
        return 'errors';
    }
}

function background(){
    $ci = & get_instance();
    $bg = $ci->db->query("SELECT gambar FROM background ORDER BY id_background DESC LIMIT 1")->row_array();
    return $bg['gambar'];
}

function cetak($str){
    return strip_tags(htmlentities($str, ENT_QUOTES, 'UTF-8'));
}

function title(){
    $ci = & get_instance();
    $title = $ci->db->query("SELECT nama_website FROM identitas ORDER BY id_identitas DESC LIMIT 1")->row_array();
    return $title['nama_website'];
}

function description(){
    $ci = & get_instance();
    $title = $ci->db->query("SELECT meta_deskripsi FROM identitas ORDER BY id_identitas DESC LIMIT 1")->row_array();
    return $title['meta_deskripsi'];
}

function keywords(){
    $ci = & get_instance();
    $title = $ci->db->query("SELECT meta_keyword FROM identitas ORDER BY id_identitas DESC LIMIT 1")->row_array();
    return $title['meta_keyword'];
}

function favicon(){
    $ci = & get_instance();
    $fav = $ci->db->query("SELECT favicon FROM identitas ORDER BY id_identitas DESC LIMIT 1")->row_array();
    return $fav['favicon'];
}

function last_login(){
    $ci = & get_instance();
    $ses = $ci->session->userdata("admin_username");
    $ci->db->order_by("waktu", "DESC");
    $ci->db->where("username", $ses);
    $ci->db->limit("1");
    $log = $ci->db->get("user_akses")->row();
    $w = explode(" ", $log->waktu);
    $hari = hari_ini($w[0]);
    $jam = $w[1]. " WITA";
    return "Last Login <i class = 'fe-terminal'></i><br><i class = 'fe-map-pin'></i> ".$log->ip."<br><i class = 'fe-globe'></i> ".$log->browser."<br><i class = 'fe-clock'></i> ".$hari.", ".tgl_indo($w[0])." Pukul ".$jam."<br><i class = 'fe-airplay'></i> ".$log->os."<br>";
}

function cek_session_admin(){
    $ci = & get_instance();
    $session = $ci->session->userdata('admin_level');
    if ($session != 'admin'){
        redirect(site_url("on_login/logout"));
    }
}

function cek_permission(){
    $ci = & get_instance();
    $session_per = $ci->session->userdata('admin_permisson');
    if ($session_per != 'Y'){
        redirect(site_url("on_login/logout"));
    }
    
}
