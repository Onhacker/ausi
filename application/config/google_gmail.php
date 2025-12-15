<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['google_gmail'] = [
  'client_id'     => getenv('GOOGLE_GMAIL_CLIENT_ID'),
  'client_secret' => getenv('GOOGLE_GMAIL_CLIENT_SECRET'),
  'redirect_uri'  => 'https://ausi.co.id/auth/gmail_callback', // pakai yang sudah Anda daftarkan
];
