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

  // ambil token lama (buat jaga-jaga refresh_token)
  $oldRow = $this->db->select('gmail_token')->get_where('settings', ['id'=>1])->row();
  $oldTok = $oldRow && $oldRow->gmail_token ? json_decode($oldRow->gmail_token, true) : [];

  // kalau Google tidak kirim refresh_token, pakai yang lama
  if (empty($token['refresh_token']) && !empty($oldTok['refresh_token'])) {
    $token['refresh_token'] = $oldTok['refresh_token'];
  }

  $this->db->where('id', 1)->update('settings', [
    'gmail_token'       => json_encode($token),
    'token_updated_at'  => date('Y-m-d H:i:s'),
  ]);

  redirect('admin_pos/gmail_inbox'); // biar langsung lihat inbox
}



    
}
