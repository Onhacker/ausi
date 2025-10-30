<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cli_probe extends CI_Controller {
    public function ping($p='ok'){
        // izinkan CLI & web, biar gampang tes
        $msg = "CLI_PROBE OK at ".date('c')." p=$p FILE=".__FILE__." APPPATH=".APPPATH." FCPATH=".FCPATH."\n";
        echo $msg;
        @file_put_contents('/tmp/cli_probe.txt',$msg,FILE_APPEND);
        exit(0);
    }
}

