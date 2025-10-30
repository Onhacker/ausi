<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_paket_a extends CI_Model {
	
	var $column_order_user = array('', '', 'nama', 'nik', 'no_kk', 'nama_pemohon', 'id_dusun','create_date','status');
	var $column_order_admin = array('', '', 'nama', 'nik', 'no_kk', 'nama_pemohon','id_dusun', 'id_desa','create_date','status');
	var $column_search = array('nama_pemohon', 'no_wa', 'nama'); 
	var $order = array('create_date' => 'DESC', 'create_time' => 'DESC');

	public function __construct() {
		parent::__construct();
	}

	private function get_data_query($table, $post, $with_search = true, $with_order = true) {
		$this->db->from($table);

	// Filter
		if (!empty($post['tahun'])) {
			$this->db->where('YEAR(create_date)', $post['tahun']);
		}
		if (!empty($post['id_dusun'])) {
			$this->db->where('id_dusun', $post['id_dusun']);
		}
		if (!empty($post['id_desa'])) {
			$this->db->where('id_desa', $post['id_desa']);
		}
		// khusus untuk status, biar "0" tetap terdeteksi
		if (isset($post['status']) && $post['status'] !== "") {
			$this->db->where('status', $post['status']);
		}
		if (!empty($post['nama'])) {
			$this->db->group_start();
			$this->db->like('nama', $post['nama']);
			$this->db->or_like('nama_pemohon', $post['nama']);
			$this->db->or_like('nik', $post['nama']);
			$this->db->or_like('no_kk', $post['nama']);
			$this->db->group_end();
		}

	// Filter by admin level
		$admin_level = $this->session->userdata("admin_level");
		$id_desa = $this->session->userdata("id_desa");

		if ($admin_level === "admin") {
			if (!empty($post['id_kecamatan'])) {
			// Ambil prefix id_kecamatan dari post
				$parts = explode('_', $post['id_kecamatan']);
				if (count($parts) >= 3) {
					$prefix = $parts[0] . '_' . $parts[1] . '_' . $parts[2];
					$this->db->like("id_desa", $prefix . '_', 'after');
				}
			}

			$this->db->group_start();
			$this->db->where("status", 2);
			$this->db->or_where("status", 3);
			$this->db->or_where("status", 4);
			$this->db->group_end();
		} else {
			$this->db->where("id_desa", $id_desa);
		}

	// Search
		if ($with_search && !empty($post['search']['value'])) {
			$search_value = $post['search']['value'];
			$this->db->group_start();
			foreach ($this->column_search as $item) {
				$this->db->or_like($item, $search_value);
			}
			$this->db->group_end();
		}

	// Order
		if ($with_order && isset($post['order'])) {
			$columns = ($admin_level === "admin") ? $this->column_order_admin : $this->column_order_user;
			$order_column_index = $post['order'][0]['column'];
			$order_dir = $post['order'][0]['dir'];

			if (isset($columns[$order_column_index]) && !empty($columns[$order_column_index])) {
				$this->db->order_by($columns[$order_column_index], $order_dir);
			}
		} else if ($with_order) {
			foreach ($this->order as $key => $val) {
				$this->db->order_by($key, $val);
			}
		}
	}


	public function get_data($table, $post) {
		$this->get_data_query($table, $post, true, true);

		if ($post['length'] != -1) {
			$this->db->limit($post['length'], $post['start']);
		}

		return $this->db->get()->result();
	}

	public function count_filtered($table, $post) {
		$this->get_data_query($table, $post, true, false);
		return $this->db->count_all_results();
	}

	public function count_all($table, $post) {
		$this->get_data_query($table, $post, false, false);
		return $this->db->count_all_results();
	}

	public function arr_desa2($id_kecamatan = "") {
		if ($id_kecamatan != "") {
			$this->db->where("id_kecamatan", $id_kecamatan);
		}
		$res = $this->db->order_by("desa")->get("tiger_desa")->result();
		$arr[''] = '== Semua Desa ==';
		foreach ($res as $row) {
			$arr[$row->id] = $row->desa;
		}
		return $arr;
	}

	public function arr_dusun($id_desa = "") {
		if ($id_desa != "") {
			$this->db->where("id_desa", $id_desa);
		}
		$res = $this->db->order_by("nama_dusun")->get("master_dusun")->result();
		$arr[''] = '== Semua Dusun ==';
		foreach ($res as $row) {
			$arr[$row->id_dusun] = $row->nama_dusun;
		}
		return $arr;
	}



	function arr_tahun()
	{
		$query = "
		SELECT DISTINCT YEAR(update_time) AS tahun FROM paket_a
		UNION
		SELECT DISTINCT YEAR(update_time) AS tahun FROM paket_b
		UNION
		SELECT DISTINCT YEAR(update_time) AS tahun FROM paket_c
		UNION
		SELECT DISTINCT YEAR(update_time) AS tahun FROM paket_d
		UNION
		SELECT DISTINCT YEAR(update_time) AS tahun FROM paket_e
		UNION
		SELECT DISTINCT YEAR(update_time) AS tahun FROM paket_f
		UNION
		SELECT DISTINCT YEAR(update_time) AS tahun FROM paket_g
		UNION
		SELECT DISTINCT YEAR(update_time) AS tahun FROM paket_h
		UNION
		SELECT DISTINCT YEAR(update_time) AS tahun FROM paket_i
		UNION
		SELECT DISTINCT YEAR(update_time) AS tahun FROM paket_j
		UNION
		SELECT DISTINCT YEAR(update_time) AS tahun FROM paket_k
		UNION
		SELECT DISTINCT YEAR(update_time) AS tahun FROM paket_l
		UNION
		SELECT DISTINCT YEAR(update_time) AS tahun FROM paket_m
		UNION
		SELECT DISTINCT YEAR(update_time) AS tahun FROM paket_n
		UNION
		SELECT DISTINCT YEAR(update_time) AS tahun FROM paket_o
		ORDER BY tahun DESC
		";

		$res = $this->db->query($query);
		$arr[""] = "== Semua Tahun ==";
		foreach ($res->result() as $row) {
			$arr[$row->tahun] = $row->tahun;
		}

		return $arr;
	}

	public function arr_kecamatan() {
		$res = $this->db->order_by("kecamatan")->get("tiger_kecamatan")->result();
		$arr[''] = '== Semua Kecamatan ==';
		foreach ($res as $row) {
			$arr[$row->id] = $row->kecamatan;
		}
		return $arr;
	}
	
	public function get_desa_by_kecamatan($id_kecamatan) {
		return $this->db
		->order_by('desa','asc')
		->where('id_kecamatan', $id_kecamatan)
		->get('tiger_desa')

		->result();
	}
	public function get_dusun_by_desa($id_desa) {
		return $this->db->where('id_desa', $id_desa)->get('master_dusun')->result();
	}

	



	

}
