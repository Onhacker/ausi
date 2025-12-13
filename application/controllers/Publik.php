<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Publik extends Onhacker_Controller {
	function __construct(){
		parent::__construct();
        $this->output->set_header("X-Robots-Tag: noindex, nofollow", true);

	}

	function index(){
		echo "fuck";
	}

	function error(){
        $data['title'] = "Halaman Tidak Ditemukan - ".$this->fm->web_me()->nama_website;
        $this->load->view("stp/Error_view",$data); 
    }

    
}
