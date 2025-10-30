<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_booking extends CI_Model {

    // Gunakan key statis agar mudah di-invalidate dari sisi admin
    private $cacheKey = 'unit_tree';

    public function __construct() {
        parent::__construct();
        // Pakai file cache; jika server mendukung APCu, bisa ganti: ['adapter'=>'apc','backup'=>'file']
        $this->load->driver('cache', ['adapter' => 'file']);
    }

    /**
     * Versi lama (tetap disediakan jika dipakai di tempat lain)
     * NOTE: Ini menyebabkan N+1 query. Sebaiknya gunakan get_tree_cached() di controller.
     */
    public function get_tree($parent_id = NULL)
    {
        $this->db->where('parent_id', $parent_id);
        $this->db->order_by('id', 'ASC');
        $result = $this->db->get('unit_tujuan')->result();

        $tree = [];
        foreach ($result as $row) {
            $row->children = $this->get_tree($row->id); // rekursif (N+1)
            $tree[] = $row;
        }
        return $tree;
    }

    /**
     * Pohon unit_tujuan dengan cache selamanya (TTL=0).
     * - Bypass cache jika ?nocache=1
     * - Build tree O(n) sekali query
     */
    public function get_tree_cached()
    {
        $nocache = ($this->input->get('nocache') === '1');

        // Ambil dari cache jika tidak bypass
        $tree = $nocache ? false : $this->cache->get($this->cacheKey);

        if ($tree === false) {
            // === 1x query ambil semua row ===
            $rows = $this->db
                ->select('id, parent_id, nama_unit, nama_pejabat, no_hp, kuota_harian, jumlah_pendamping, updated_at')
                ->order_by('id', 'ASC')
                ->get('unit_tujuan')
                ->result();

            // === Bangun tree O(n) ===
            $tree = $this->build_tree_fast($rows);

            // === Sort konsisten (alfabetis nama_unit) ===
            $this->sort_tree_by_name($tree);

            // Simpan selamanya sampai di-invalidate admin (TTL=0)
            if (!$nocache) {
                $this->cache->save($this->cacheKey, $tree, 0);
            }
        }

        return $tree;
    }

    /**
     * Build tree dari flat rows tanpa N+1 query (O(n))
     * @param stdClass[] $rows
     * @return stdClass[] root-level nodes (masing2 punya ->children = [])
     */
    private function build_tree_fast(array $rows)
    {
        // Siapkan children
        foreach ($rows as $r) {
            if (!isset($r->children)) {
                $r->children = [];
            }
        }

        // Index by id
        $byId = [];
        foreach ($rows as $r) {
            $byId[$r->id] = $r;
        }

        // Susun parent-child
        $roots = [];
        foreach ($rows as $r) {
            if ($r->parent_id === null || $r->parent_id === '' ) {
                $roots[] = $r;
            } else if (isset($byId[$r->parent_id])) {
                $byId[$r->parent_id]->children[] = $r;
            } else {
                // Orphan (parent tidak ditemukan) → anggap root agar tidak hilang
                $roots[] = $r;
            }
        }
        return $roots;
    }

    /**
     * Sort tree (in-place) berdasarkan nama_unit ascending
     */
    private function sort_tree_by_name(array &$nodes)
    {
        usort($nodes, function($a, $b){
            $an = isset($a->nama_unit) ? mb_strtolower($a->nama_unit, 'UTF-8') : '';
            $bn = isset($b->nama_unit) ? mb_strtolower($b->nama_unit, 'UTF-8') : '';
            return strcmp($an, $bn);
        });
        foreach ($nodes as &$n) {
            if (!empty($n->children)) {
                $this->sort_tree_by_name($n->children);
            }
        }
    }
}
