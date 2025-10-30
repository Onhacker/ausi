<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_admin_user_profil extends CI_Model
{
    // Tabel master modul & relasi akses user
    protected $tbl_modul = 'modul';
    protected $tbl_rel   = 'users_modul';

    // Urutan kolom untuk DataTables (sesuaikan dengan header: No., Modul, Hak Akses)
    // DT mengirim index kolom; kita hanya izinkan order pada kolom "Modul"
    protected $column_order = [ null, 'modul.nama_modul', null ];
    protected $column_search = [ 'modul.nama_modul' ];
    protected $default_order = [ 'modul.nama_modul' => 'ASC' ];

    public function __construct()
    {
        parent::__construct();
    }

    /** Base WHERE modul aktif untuk level user */
    private function base_modul_query()
    {
        $this->db->from($this->tbl_modul.' modul');
        $this->db->where('modul.aktif',  'Y');
        $this->db->where('modul.status', 'user');
        $this->db->where('modul.publish','Y');
    }

    /**
     * Query builder untuk data (dengan LEFT JOIN ke users_modul per user)
     * @param string $id_session  id_session user yang sedang dikelola
     */
    private function get_data_query($id_session)
    {
        $this->db->select("
            modul.id_modul,
            modul.nama_modul,
            CASE WHEN um.id_modul IS NULL THEN 0 ELSE 1 END AS granted
        ", false);

        $this->base_modul_query();

        // Join relasi akses: jika ada, berarti granted=1
        $this->db->join($this->tbl_rel.' um',
            "um.id_modul = modul.id_modul AND um.id_session = ".$this->db->escape($id_session),
            'left'
        );

        // Search
        $search = $this->input->post('search[value]', true);
        if ($search !== null && $search !== '') {
            $this->db->group_start();
            foreach ($this->column_search as $i => $col) {
                if ($i === 0) $this->db->like($col, $search);
                else          $this->db->or_like($col, $search);
            }
            $this->db->group_end();
        }

        // Order
        $orderReq = $this->input->post('order');
        if (is_array($orderReq) && isset($orderReq[0]['column'], $orderReq[0]['dir'])) {
            $colIdx = (int)$orderReq[0]['column'];
            $dir    = strtolower($orderReq[0]['dir']) === 'desc' ? 'DESC' : 'ASC';
            $col    = $this->column_order[$colIdx] ?? null;
            if ($col) $this->db->order_by($col, $dir);
        } else {
            foreach ($this->default_order as $c=>$d) $this->db->order_by($c,$d);
        }
    }

    /** Data untuk DataTables */
    public function get_data($id_session)
    {
        $this->get_data_query($id_session);

        $length = (int)$this->input->post('length');
        $start  = (int)$this->input->post('start');
        if ($length !== -1) $this->db->limit($length, $start);

        return $this->db->get()->result();
    }

    /** Hitung setelah filter */
    public function count_filtered($id_session)
    {
        $this->get_data_query($id_session);
        return $this->db->get()->num_rows();
    }

    /** Total modul aktif (tanpa filter pencarian) */
    public function count_all()
    {
        $this->base_modul_query();
        return $this->db->count_all_results();
    }
}
