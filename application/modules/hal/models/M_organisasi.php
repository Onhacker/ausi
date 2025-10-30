<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_organisasi extends CI_Model
{
    // Pakai tabel unit_tujuan sesuai skema kamu
    private $table = 'unit_tujuan';

  public function get_all() {
        return $this->db->order_by('id','ASC')->get('unit_tujuan')->result();
    }
    public function get_all_light() {
        return $this->db->select('id, parent_id, nama_unit, nama_pejabat, no_hp, kuota_harian, jumlah_pendamping, updated_at, created_at')
                        ->from('unit_tujuan')
                        ->order_by('id ASC')   // stabil untuk O(n)
                        ->get()->result();
    }

    /** Timestamp terakhir berubah (untuk ETag/cache key) */
    public function last_changed(): int {
        $row = $this->db->select('UNIX_TIMESTAMP(MAX(COALESCE(updated_at, created_at))) AS ts', false)
                        ->from('unit_tujuan')
                        ->get()->row();
        return (int)($row->ts ?? 0);
    }
}
