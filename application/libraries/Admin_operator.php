<?php
class Admin_operator extends MX_Controller {
	function __construct() {
		parent::__construct();
		$this->timezone();
		
		$this->load->helper("cpu");
		cek_session_op_login();	
		$this->load->model("Ram_model","om");
		$this->load->helper("on");
		error_reporting(1);
	}

	function render($data){
		$this->om->validasiOpLogin();     // keluarkan jika perintah keluarkan dari prangkat lain
		$data["jumlah_pesan"] = $this->om->view_where('hubungi', array('dibaca'=>'N'))->num_rows(); 
		$this->db->where("status", "0");
		$data["pesan"] = $this->om->view_ordering_limit('hubungi','id_hubungi','DESC',0,5);
		$this->load->view("backend/op_template",$data);
	}

	function timezone(){
        $this->db->where("id_identitas", "1");
        $s = $this->db->get("identitas")->row();
        return date_default_timezone_set($s->waktu);
    }

}
?>