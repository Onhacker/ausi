<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_notify extends Admin_Controller {
    public function __construct(){
        parent::__construct();
        try { $this->load->model('M_admin_pos','mp'); } catch (\Throwable $e) {}
        try { $this->load->model('M_admin_billiard','mb'); } catch (\Throwable $e) {}
    }

    public function ping(){
        $this->output
         ->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0')
         ->set_header('Pragma: no-cache');

    $this->load->driver('cache', ['adapter' => 'file']);

    // Konsistenkan penamaan key: pos_ver dan billiard_ver
    $pos_ver = (int) ($this->cache->get('pos_ver') ?: 0);
    $bil_ver = (int) ($this->cache->get('billiard_ver') ?: 0);

        $pos = ['total'=>0,'max_id'=>0,'last_ts'=>null];
        $bil = ['total'=>0,'max_id'=>0,'last_ts'=>null];

        try {
            if ($this->mp && method_exists($this->mp,'get_stats')){
                $s = $this->mp->get_stats();
                $pos = [
                    'total'   => (int)($s->total ?? 0),
                    'max_id'  => (int)($s->max_id ?? 0),
                    'last_ts' => !empty($s->last_ts) ? date('c', strtotime($s->last_ts)) : null,
                ];
            }
        } catch(\Throwable $e){}

        try {
            if ($this->mb && method_exists($this->mb,'get_stats')){
                $s = $this->mb->get_stats();
                $bil = [
                    'total'   => (int)($s->total ?? 0),
                    'max_id'  => (int)($s->max_id ?? 0),
                    'last_ts' => !empty($s->last_ts) ? date('c', strtotime($s->last_ts)) : null,
                ];
            }
        } catch(\Throwable $e){}

        return $this->output->set_content_type('application/json')->set_output(json_encode([
            'success' => true,
            'pos' => [
                'ver'     => $pos_ver,
                'total'   => $pos['total'],
                'max_id'  => $pos['max_id'],
                'last_ts' => $pos['last_ts'],
            ],
            'billiard' => [
                'ver'     => $bil_ver,
                'total'   => $bil['total'],
                'max_id'  => $bil['max_id'],
                'last_ts' => $bil['last_ts'],
            ],
        ]));

    }
}
