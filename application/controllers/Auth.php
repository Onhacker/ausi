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
    $this->load->library('Gmail_oauth');
    // $this->load->library('session'); // kalau belum di-autoload

    // cek token lama (buat tentukan perlu consent atau tidak)
    $oldRow = $this->db->select('gmail_token')->get_where('settings', ['id'=>1])->row();
    $oldTok = ($oldRow && $oldRow->gmail_token) ? (json_decode($oldRow->gmail_token, true) ?: []) : [];

    $forceConsent = empty($oldTok['refresh_token']); // hanya kalau belum ada refresh_token

    // state anti-CSRF
    $state = bin2hex(random_bytes(16));
    $this->session->set_userdata('gmail_oauth_state', $state);

    $client = Gmail_oauth::client($forceConsent, $state);
    redirect($client->createAuthUrl());

  } catch (\Throwable $e) {
    show_error("GMAIL CONNECT ERROR: ".$e->getMessage(), 500);
  }
}

public function gmail_callback()
{
  $this->load->library('Gmail_oauth');
  // $this->load->library('session'); // kalau belum di-autoload

  // validasi state anti-CSRF
  $state     = (string)$this->input->get('state', true);
  $expected  = (string)$this->session->userdata('gmail_oauth_state');

  if ($expected === '' || $state === '' || !hash_equals($expected, $state)) {
    show_error('State OAuth tidak valid / kadaluarsa. Ulangi connect.', 400);
  }
  $this->session->unset_userdata('gmail_oauth_state');

  $code = (string)$this->input->get('code', true);
  if ($code === '') show_error('OAuth code kosong', 400);

  $client = Gmail_oauth::client(false, $state);
  $token  = $client->fetchAccessTokenWithAuthCode($code);

  if (isset($token['error'])) {
    show_error('OAuth error: '.$token['error'], 400);
  }

  // ambil token lama untuk merge aman
  $oldRow = $this->db->select('gmail_token')->get_where('settings', ['id'=>1])->row();
  $oldTok = ($oldRow && $oldRow->gmail_token) ? (json_decode($oldRow->gmail_token, true) ?: []) : [];

  // merge: token baru override, kecuali refresh_token kosong jangan timpa
  $merged = $oldTok;
  foreach ($token as $k => $v) {
    if ($k === 'refresh_token' && empty($v) && !empty($oldTok['refresh_token'])) continue;
    $merged[$k] = $v;
  }
  if (empty($merged['created'])) $merged['created'] = time();

  $this->db->where('id', 1)->update('settings', [
    'gmail_token'      => json_encode($merged),
    'token_updated_at' => date('Y-m-d H:i:s'),
  ]);

  redirect('admin_pos/gmail_inbox?sync=1');
}

}
