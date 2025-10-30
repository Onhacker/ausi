<?php
class Onhacker_Controller extends MX_Controller {
	function __construct() {
		parent::__construct();
		$this->timezone();
		$this->load->model("Front_model","fm");
		$this->load->helper("front");
		error_reporting(0);
		// $this->kunjungan();
		
	}

	function render($data){
		$this->db->where("aktif", "Y");
		$res = $this->db->get("templates")->row();
		$data["web"] = $this->fm->web_me();
		$data["terkini"] = $this->fm->view_where_ordering_limit('berita',array('status' => 'Y'),'id_berita','DESC',0,10);
		$data["menu_atas"] = $this->fm->view_where_ordering_limit('menu',array('position' => 'Top','aktif' => 'Ya'),'urutan','ASC',0,5);
		$this->db->where("posisi", "atas");
		$this->db->where("aktif", "Y");
		$data["banner"] = $this->db->get("banner");
		$data["uri"] = $this->uri->segment(1);

		//footer

		$data["nama_kategori"] =$this->fm->engine_nama_menu("Admin_kategori");
		$data["kategori"] = $this->fm->view_where_ordering_limit("kategori","aktif = 'Y'","id_kategori","DESC",0,8);
		$data["read_more_kategori_limit"] = $data["kategori"]->num_rows();
		$this->db->where("aktif", "Y");
		$jum_ka = $this->db->get("kategori");
		$data["read_more_kategori"] = $jum_ka->num_rows();

		$this->load->view($res->folder."/front_template",$data);
	}
	
	function timezone(){
		$this->db->where("id_identitas", "1");
		$s = $this->db->get("identitas")->row();
		return date_default_timezone_set($s->waktu);
	}

	function kunjungan(){
        $ip      = $_SERVER['REMOTE_ADDR'];
        $tanggal = date("Y-m-d");
        $waktu   = time(); 
        $cekk = $this->db->query("SELECT * FROM statistik WHERE ip='$ip' AND tanggal='$tanggal'");
        $rowh = $cekk->row_array();
        if($cekk->num_rows() == 0){
            $datadb = array('ip'=>$ip, 'tanggal'=>$tanggal, 'hits'=>'1', 'online'=>$waktu);
            $this->db->insert('statistik',$datadb);
        }else{
            $hitss = $rowh['hits'] + 1;
            $datadb = array('ip'=>$ip, 'tanggal'=>$tanggal, 'hits'=>$hitss, 'online'=>$waktu);
            $array = array('ip' => $ip, 'tanggal' => $tanggal);
            $this->db->where($array);
            $this->db->update('statistik',$datadb);
        }
    }

	
}
?>