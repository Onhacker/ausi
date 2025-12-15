<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH.'../vendor/autoload.php';

use Google\Client;
use Google\Service\Gmail;

class Gmail_oauth {

  public static function client(bool $forceConsent = false, ?string $state = null): Client
  {
    $CI = &get_instance();
    $CI->load->config('google_gmail');
    $cfg = $CI->config->item('google_gmail');

    if (!$cfg || empty($cfg['client_id']) || empty($cfg['client_secret']) || empty($cfg['redirect_uri'])) {
      throw new Exception('Config google_gmail kosong. Cek env CLIENT_ID/SECRET & redirect_uri.');
    }

    $client = new Client();
    $client->setClientId($cfg['client_id']);
    $client->setClientSecret($cfg['client_secret']);
    $client->setRedirectUri($cfg['redirect_uri']);

    // scope minimal untuk baca email
    $client->addScope(Gmail::GMAIL_READONLY);

    // penting untuk refresh_token
    $client->setAccessType('offline');
    $client->setIncludeGrantedScopes(true);

    // state anti-CSRF (opsional)
    if ($state) $client->setState($state);

    // consent hanya saat perlu refresh_token
    if ($forceConsent) $client->setPrompt('consent select_account');
    else              $client->setPrompt(''); // boleh juga dikosongkan

    return $client;
  }
}
