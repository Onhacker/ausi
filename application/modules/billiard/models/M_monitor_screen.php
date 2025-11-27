<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_monitor_screen extends CI_Model
{
    protected $table = 'monitor_screen';

    public function touch($monitor_id, $tipe = 'billiard', $nama_default = null)
    {
        if (!$monitor_id) return;

        $now = date('Y-m-d H:i:s');
        $ip  = $this->input->ip_address();
        $ua  = $this->input->user_agent();

        $row = $this->db
            ->get_where($this->table, ['monitor_id' => $monitor_id])
            ->row();

        $ipLocation = null;
        if ($row) {
            $ipLocation = $row->ip_location;
        }

        // Kalau IP berubah atau lokasi kosong → geolokasi ulang
        if ($ip && (!$ipLocation || ($row && $row->last_ip !== $ip))) {
            $ipLocation = $this->lookup_ip_location($ip);
        }

        $data = [
            'last_seen'   => $now,
            'last_ip'     => $ip,
            'user_agent'  => $ua,
            'tipe'        => $tipe,
            'ip_location' => $ipLocation,
        ];

        if ($row) {
            $this->db->where('monitor_id', $monitor_id)
                     ->update($this->table, $data);
        } else {
            $data['monitor_id'] = $monitor_id;
            $data['nama']       = $nama_default ?: 'Monitor '.$tipe;
            $this->db->insert($this->table, $data);
        }
    }

    public function list_status($tipe = 'billiard', $max_idle_sec = 60)
    {
        if ($tipe === 'all') {
            $this->db->order_by('last_seen', 'DESC');
            $rows = $this->db->get($this->table)->result();
        } else {
            $this->db->where('tipe', $tipe)
                     ->order_by('last_seen', 'DESC');
            $rows = $this->db->get($this->table)->result();
        }

        $now = time();
        foreach ($rows as &$r) {
            $ts   = strtotime($r->last_seen ?: '1970-01-01 00:00:00');
            $diff = max(0, $now - $ts);
            $r->idle_sec  = $diff;
            $r->is_online = ($diff <= $max_idle_sec);
        }
        return $rows;
    }

    /**
     * Geolokasi IP (sederhana).
     * - IP private / lokal → "Jaringan lokal (private IP)".
     * - IP publik → coba panggil ipapi.co (gratis tapi ada limit).
     */
    private function lookup_ip_location($ip)
    {
        if (!$ip) return null;

        // Kalau private / reserved → langsung tandai sebagai jaringan lokal
        if ($this->is_private_ip($ip)) {
            return 'Jaringan lokal (private IP)';
        }

        // Panggil API geolokasi (contoh: ipapi.co)
        $url = 'https://ipapi.co/' . urlencode($ip) . '/json/';

        $ctx = stream_context_create([
            'http' => [
                'timeout' => 3, // 3 detik
            ]
        ]);

        $json = @file_get_contents($url, false, $ctx);
        if (!$json) return null;

        $data = json_decode($json, true);
        if (!is_array($data)) return null;

        $parts = [];
        if (!empty($data['city']))         $parts[] = $data['city'];
        if (!empty($data['region']))       $parts[] = $data['region'];
        if (!empty($data['country_name'])) $parts[] = $data['country_name'];
        if (!$parts && !empty($data['country_code'])) $parts[] = $data['country_code'];

        return $parts ? implode(', ', $parts) : null;
    }

    private function is_private_ip($ip)
    {
        // Kalau invalid / private / reserved → dianggap lokal
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return true;
        }
        return false;
    }
}
