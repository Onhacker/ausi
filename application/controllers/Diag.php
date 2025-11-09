<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Diag extends MX_Controller {
  public function env() {
    $hasJson = function_exists('json_encode') ? 'ON' : 'OFF';
    $hasMb   = extension_loaded('mbstring') ? 'ON' : 'OFF';
    $hasMysqli = extension_loaded('mysqli') ? 'ON' : 'OFF';
    $mysqlVer = null;
    $sqlMode  = null;
    try {
      $row = $this->db->query('SELECT VERSION() v, @@sql_mode m')->row();
      $mysqlVer = $row ? $row->v : null;
      $sqlMode  = $row ? $row->m : null;
    } catch (Exception $e) { /* ignore */ }

    header('Content-Type: text/plain; charset=utf-8');
    echo "php=".PHP_VERSION."\njson=".$hasJson."\nmbstring=".$hasMb.
         "\nmysqli=".$hasMysqli."\nmysql=".$mysqlVer."\nsql_mode=".$sqlMode."\n";
  }

  public function ping() {
    header('Content-Type: application/json');
    echo '{"ok":true}';
  }
}
