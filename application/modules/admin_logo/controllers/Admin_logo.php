<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_logo extends Admin_Controller {
	function __construct(){
		parent::__construct();
		cek_session_akses(get_class($this),$this->session->userdata('admin_session'));
	}

	function index(){
		$data["controller"] = get_class($this);
		$data["record"] = $this->om->edit('logo', array('id_logo' => 1))->row();
		$data["title"] = "Pengaturan Web";
		$data["subtitle"] = $this->om->engine_nama_menu(get_class($this)) ;
		$data["content"] = $this->load->view($data["controller"]."_view",$data,true); 
		$this->render($data);
	}

	function update(){
			$data = $this->input->post();
			$data["id_logo"] = "1";
			$new_name = "logo";
			$config['upload_path'] = 'assets/images/';
			$config['allowed_types'] = 'jpg|jpeg|png|JPG|PNG|ICO|JPEG';
            $config['max_size'] = '3000'; // kb
            $config['overwrite'] = TRUE;
            $config['file_name'] = $new_name;
            $this->load->library('upload', $config);

            if (empty($_FILES['gambar']["name"])){
				$this->db->where("id_logo","1");
				$res  = $this->om->update("logo",$data);
				rec();		
			} 

			if (! $this->upload->do_upload('gambar')) {
				$rules = "<hr>Tipe file (".str_replace("|", ", ", $config['allowed_types']).")<br>Max file (".($config['max_size'])." Kb)";

			} else {
				$fdata =  $this->upload->data();
				//Compress Image
                $config['image_library']='gd2';
                $config['source_image']='assets/images/'.$fdata['file_name'];
                $config['create_thumb']= FALSE;
                $config['maintain_ratio']= TRUE;
                $config['quality']= '100%';
                // $config['width']= 400;
                $config['height']= 100;
                $config['overwrite'] = TRUE;
                $config['new_image']= 'assets/images/'.$fdata['file_name'];
               
                $this->load->library('image_lib', $config);
                $res =  $this->image_lib->resize();

				$data['gambar'] = $fdata['file_name'];	
				$this->db->where("id_logo","1");
				$res  = $this->db->update("logo",$data);
				rec();			
			}
            
			if($res) {    
				$ret = array("success" => true,
					"title" => "Berhasil",
					"pesan" => "Data berhasil diupdate");
			} else {
				$ret = array("success" => false,
					"title" => "Gagal",
					"pesan" => "Data Gagal diupdate ".$this->upload->display_errors("<br>",$rules));
			}
		echo json_encode($ret);
	}


	
	
}
