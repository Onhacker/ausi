<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_billiard extends CI_Model {

  public function get_mejas_aktif(){
    return $this->db->order_by('nama_meja','asc')->get_where('meja_billiard', ['aktif'=>1])->result();
  }

  public function get_meja($id){
    return $this->db->get_where('meja_billiard', ['id_meja'=>(int)$id, 'aktif'=>1])->row();
  }

  public function create_pesanan(array $data){
    $this->db->insert('pesanan_billiard', $data);
    return $this->db->affected_rows() > 0;
  }

  public function get_by_token(string $token){
    return $this->db->get_where('pesanan_billiard', ['access_token'=>$token])->row();
  }

  public function update_by_token(string $token, array $data){
    $this->db->where('access_token',$token)->update('pesanan_billiard',$data);
    return $this->db->affected_rows() >= 0;
  }
public function set_status_by_token(string $token, string $status, array $extra = []){
        if (empty($token)) return false;
        $data = array_merge(['status' => $status], $extra);
        return $this->db->where('access_token', $token)->update('pesanan_billiard', $data);
    }
  // overlap jika existing.start < new.end && existing.end > new.start
  public function has_overlap(int $meja_id, string $tanggal, string $mulai, string $selesai, $exclude_id = null){
    $this->db->from('pesanan_billiard')
      ->where('meja_id', $meja_id)
      ->where('tanggal', $tanggal)
      ->where_in('status', ['menunggu_bayar','terkonfirmasi'])
      ->where('jam_mulai <', $selesai)
      ->where('jam_selesai >', $mulai);
    if ($exclude_id){
      $this->db->where('id_pesanan <>', $exclude_id);
    }
    return $this->db->count_all_results() > 0;
  }
}
