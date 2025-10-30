<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_billiard extends CI_Model {
  public function __construct(){ parent::__construct(); }

  /**
   * Ambil daftar meja (aktif = 1) dengan semua kolom.
   * @return array objek
   */
  public function get_all_mejas(){
    return $this->db
      ->select('*')
      ->from('meja_billiard')
      ->where('aktif', 1)
      ->order_by('id_meja', 'ASC')
      ->get()
      ->result();
  }

  // Jika sudah ada fungsi get_mejas_aktif() yang cocok, kamu bisa pakai itu.
}
