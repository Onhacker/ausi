<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Map extends MX_Controller {
	function __construct(){
		parent::__construct();
	}

	function index(){
       $this->load->view("map_view");
    }


	
}
