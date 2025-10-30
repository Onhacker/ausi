<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_pengumuman extends CI_Model
{
    /**
     * Ambil list + total untuk pagination & pencarian.
     * Return: [$rowsArray, $totalInt]
     */
    public function list_with_total(string $q = '', int $page = 1, int $per_page = 5)
    {
        $off = ($page - 1) * $per_page;
        if ($off < 0) $off = 0;

        // ------- Hitung total -------
        $this->db->from('pengumuman');

        if ($q !== '') {
            $q_esc = $this->db->escape_like_str($q); // aman terhadap wildcard
            $this->db->group_start()
                     ->like('judul', $q_esc, 'both', false) // false: sudah di-escape manual
                     ->or_like('isi',   $q_esc, 'both', false)
                     ->group_end();
        }

        $total = (int) $this->db->count_all_results(); // <- TANPA parameter kedua

        // ------- Ambil rows -------
        $this->db->from('pengumuman');

        if ($q !== '') {
            $q_esc = $this->db->escape_like_str($q);
            $this->db->group_start()
                     ->like('judul', $q_esc, 'both', false)
                     ->or_like('isi',   $q_esc, 'both', false)
                     ->group_end();
        }

        $this->db->select('id, judul, isi, tanggal, link_seo');
        $this->db->order_by('tanggal', 'DESC');
        $this->db->limit($per_page, $off);
        $rows = $this->db->get()->result_array();

        return [$rows, $total];
    }


    /**
     * Fallback versi perubahan (ketika 'pengumuman_ver' belum ada).
     * Pakai MAX(tanggal) sebagai patokan.
     */
    public function last_changed_fallback(): int
    {
        $row = $this->db->select('UNIX_TIMESTAMP(MAX(tanggal)) AS ts', false)
                        ->get('pengumuman')->row();
        return ($row && $row->ts) ? (int)$row->ts : time();
    }
}
