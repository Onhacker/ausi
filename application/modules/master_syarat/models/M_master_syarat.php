<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_master_syarat extends CI_Model {
	var $table = 'master_syarat';
	var $column_order = array('', '', 'label', 'nama_permohonan');
	var $order = array('id' => 'DESC');

	public function __construct(){
		parent::__construct();
	}

	private function get_data_query(){
		// $this->db->from($this->table);
		$this->db->select('*');
		$this->db->from('master_syarat');
		$this->db->join('master_permohonan', 'master_permohonan.id_permohonan = master_syarat.id_permohonan');
		$this->db->order_by('master_syarat.id_permohonan', 'DESC'); // lebih spesifik
		// $this->db->get();
		// $result = $query->result(); // atau result_array() jika ingin dalam bentuk array


		if ($this->session->userdata("admin_level") == "admin") {
			$column_search = array('label', 'nama_permohonan','syarat');
		} else {
			$column_search = array('label');
		}

		$search_value = $_POST['search']['value'] ?? '';
		if (!empty($search_value)) {
		$this->db->group_start(); // Open bracket for WHERE grouping
		foreach ($column_search as $i => $item) {
			if ($i === 0) {
				$this->db->like($item, $search_value);
			} else {
				$this->db->or_like($item, $search_value);
			}
		}
		$this->db->group_end(); // Close bracket
	}

	if (isset($_POST['order'])) {
		$col_index = $_POST['order']['0']['column'];
		$col_dir = $_POST['order']['0']['dir'];
		if (isset($this->column_order[$col_index]) && !empty($this->column_order[$col_index])) {
			$this->db->order_by($this->column_order[$col_index], $col_dir);
		}
	} else {
		// Default order
		$this->db->order_by(key($this->order), $this->order[key($this->order)]);
	}
}

public function get_data(){
	$this->get_data_query();
	if ($_POST['length'] != -1) {
		$this->db->limit($_POST['length'], $_POST['start']);
	}
	$query = $this->db->get();
	return $query->result();
}

public function count_filtered(){
	$this->get_data_query();
	return $this->db->get()->num_rows();
}

public function count_all(){
	return $this->db->count_all($this->table);
}



function get_by_id($id){
	if ($this->session->userdata("admin_level") == "admin") {
		$this->db->from($this->table);
		$this->db->where('id',$id);
		$query = $this->db->get();
	} 
	return $query;
}

function arr_permohonan(){
	$this->db->order_by("id_permohonan", "ASC");
	$res = $this->db->get("master_permohonan");
	$arr[""]  = "== Pilih Permohonan == ";
	foreach($res->result() as $row) :
		$arr[$row->id_permohonan]  = $row->nama_permohonan;
	endforeach;
	return $arr;
}

}
