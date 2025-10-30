<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron_exp_biliard extends Onhacker_Controller
{
  public function __construct(){
    parent::__construct();
    if (!$this->input->is_cli_request()) { show_404(); } // CLI only
    date_default_timezone_set('Asia/Makassar');
    $this->load->model('front_model','fm');
    $this->load->database();
  }

  /**
   * Auto-cancel booking yang lewat deadline bayar.
   * Status yang DI-CANCEL: draft | menunggu_bayar | verifikasi
   * Status yang TIDAK DISENTUH: batal | terkonfirmasi
   */
  public function cancel_expired(){
      $hasExpiresAt  = $this->db->field_exists('expires_at',  'pesanan_billiard');
      $hasLateMinCol = $this->db->field_exists('late_min',    'pesanan_billiard'); // per-row
      $hasCanceledAt = $this->db->field_exists('canceled_at', 'pesanan_billiard');

      $cancelables = "('draft','menunggu_bayar')";
      $setCanceledAt = $hasCanceledAt ? ", canceled_at = NOW()" : "";

      // OPTIONAL (hindari mismatch zona waktu MySQL vs PHP)
      // $this->db->query("SET time_zone = '+08:00'");

      if ($hasExpiresAt) {
        $sql = "
          UPDATE pesanan_billiard
             SET status='batal', updated_at=NOW() {$setCanceledAt}
           WHERE status IN {$cancelables}
             AND (metode_bayar IS NULL OR metode_bayar <> 'cash')
             AND expires_at IS NOT NULL
             AND expires_at <= NOW()
        ";
        $this->db->query($sql);
      } else {
        $lateMinGlobal = (int)($this->fm->web_me()->late_min ?? 15);

        if ($hasLateMinCol) {
          $sql = "
            UPDATE pesanan_billiard
               SET status='batal', updated_at=NOW() {$setCanceledAt}
             WHERE status IN {$cancelables}
               AND (metode_bayar IS NULL OR metode_bayar <> 'cash')
               AND TIMESTAMPDIFF(MINUTE, updated_at, NOW()) >= COALESCE(late_min, ?)
          ";
          $this->db->query($sql, [$lateMinGlobal]);
        } else {
          $sql = "
            UPDATE pesanan_billiard
               SET status='batal', updated_at=NOW() {$setCanceledAt}
             WHERE status IN {$cancelables}
               AND (metode_bayar IS NULL OR metode_bayar <> 'cash')
               AND TIMESTAMPDIFF(MINUTE, updated_at, NOW()) >= ?
          ";
          $this->db->query($sql, [$lateMinGlobal]);
        }
      }

      $affected = $this->db->affected_rows();
      log_message('info', '[CRON] cancel_expired affected='.$affected);
      echo "OK {$affected}\n";
    }

}
