<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MX_Controller {

  function __construct(){
    parent::__construct();
    $this->output->set_header("X-Robots-Tag: noindex, nofollow", true);
    $this->load->database();
  }

  public function gmail_connect()
	{
	  try {
	    $this->load->library('Gmail_oauth'); // PENTING (biar class dikenali)
	    $client = Gmail_oauth::client();
	    redirect($client->createAuthUrl());
	  } catch (\Throwable $e) {
	    show_error("GMAIL CONNECT ERROR: ".$e->getMessage(), 500);
	  }
	}


  public function gmail_callback()
{
    $client = Gmail_oauth::client();

    // 1) Kalau Google kirim error, tampilkan dulu (ini penyebab code kosong paling sering)
    $err  = $this->input->get('error'); // jangan TRUE
    $desc = $this->input->get('error_description');
    if ($err) {
        show_error('OAuth error: '.$err.($desc ? ' | '.$desc : ''), 400);
    }

    // 2) Ambil code RAW (hindari xss_clean)
    $code = $this->input->get('code'); // <-- PENTING: jangan pakai TRUE
    if (!$code) {
        // bantu debug: lihat query string yang masuk
        show_error('OAuth code kosong. Query: '.htmlspecialchars($_SERVER['QUERY_STRING'] ?? ''), 400);
    }

    $token = $client->fetchAccessTokenWithAuthCode($code);
    if (isset($token['error'])) {
        show_error('OAuth token error: '.$token['error'], 400);
    }

    // simpan token (jaga refresh_token lama)
    $oldRow = $this->db->select('gmail_token')->get_where('settings', ['id'=>1])->row();
    $oldTok = $oldRow && $oldRow->gmail_token ? json_decode($oldRow->gmail_token, true) : [];

    if (empty($token['refresh_token']) && !empty($oldTok['refresh_token'])) {
        $token['refresh_token'] = $oldTok['refresh_token'];
    }

    $this->db->where('id', 1)->update('settings', [
        'gmail_token'      => json_encode($token),
        'token_updated_at' => date('Y-m-d H:i:s'),
    ]);

    redirect('admin_pos/gmail_inbox');
}

}
