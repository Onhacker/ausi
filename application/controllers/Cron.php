<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends MX_Controller {

  public function __construct(){
    parent::__construct();
    date_default_timezone_set('Asia/Makassar');
    $this->load->library('Gmail_sync_service');
  }

  // CLI: php index.php cron gmail_sync 50
  public function gmail_sync($limit = 50)
  {
    // ✅ paling aman: CLI only
    if (!$this->input->is_cli_request()) {
      show_error('Forbidden', 403);
    }

    $res = $this->gmail_sync_service->sync((int)$limit);

    $this->output
      ->set_content_type('application/json')
      ->set_output(json_encode(['ok'=>!empty($res['ok']), 'sync'=>$res], JSON_UNESCAPED_SLASHES));
  }
}
