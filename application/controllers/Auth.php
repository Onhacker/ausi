<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends Onhacker_Controller {
	function __construct(){
		parent::__construct();
        $this->output->set_header("X-Robots-Tag: noindex, nofollow", true);

	}

	public function gmail_connect()
		{
		  $client = Gmail_oauth::client();
		  redirect($client->createAuthUrl());
		}

		public function gmail_callback()
		{
		  $client = Gmail_oauth::client();
		  $code = (string)$this->input->get('code', true);
		  if ($code === '') show_error('OAuth code kosong', 400);

		  $token = $client->fetchAccessTokenWithAuthCode($code);
		  if (isset($token['error'])) show_error('OAuth error: '.$token['error'], 400);

		  // simpan token (access + refresh) ke DB (contoh di table settings id=1)
		  $this->db->update('settings', ['gmail_token' => json_encode($token)], ['id'=>1]);

		  redirect('admin_pos'); // atau ke halaman admin gmail
		}


    
}
