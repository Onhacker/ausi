<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron_exp_biliard extends Onhacker_Controller
{
    public function __construct(){
        parent::__construct();

        // CLI-only
        if ( ! $this->input->is_cli_request()) { show_404(); }

        date_default_timezone_set('Asia/Makassar');
        $this->load->model('front_model','fm');
        $this->load->database();
    }

    /**
     * Auto-cancel booking yang lewat deadline bayar, lalu arsipkan & hapus.
     * Sentuh hanya status: draft | menunggu_bayar (belum sah). 'terkonfirmasi' tidak disentuh.
     */
    public function cancel_expired()
    {
        // Samakan zona waktu MySQL agar TIMESTAMPDIFF konsisten
        $this->db->query("SET time_zone = '+08:00'");

        // Wajib: tabel arsip harus ada
        if ( ! $this->db->table_exists('billiard_expired')) {
            log_message('error', '[CRON] cancel_expired: table billiard_expired tidak ada');
            echo "ERR: table billiard_expired tidak ada\n";
            return;
        }

        // Cek ketersediaan kolom opsional
        $hasLateMinCol = $this->db->field_exists('late_min',    'pesanan_billiard');
        $hasCanceledAt = $this->db->field_exists('canceled_at', 'pesanan_billiard');

        // Grace period (menit) dari konfigurasi global (fallback 15)
        $lateMinGlobal = $this->_late_min();

        // Ambil kandidat ID yang expired
        $ids = $this->_get_cancelable_ids($lateMinGlobal, $hasLateMinCol);
        if (empty($ids)) {
            echo "OK 0\n";
            return;
        }

        // Placeholder untuk IN (...)
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // Mulai transaksi
        $this->db->trans_start();

        // 1) Update status jadi 'batal' + (optional) canceled_at
        $updateSql = "
            UPDATE pesanan_billiard
               SET status = 'batal',
                   updated_at = NOW()"
                   . ($hasCanceledAt ? ", canceled_at = NOW()" : "") . "
             WHERE id_pesanan IN ($placeholders)
        ";
        $this->db->query($updateSql, $ids);

        // 2) Insert ke arsip (pakai SUBSTRING/CAST agar aman beda lebar kolom)
        $insertSql = "
            INSERT INTO billiard_expired (
                archived_at,
                id_pesanan,
                kode_booking,
                access_token,
                status,
                nama,
                no_hp,
                meja_id,
                nama_meja,
                tanggal,
                jam_mulai,
                jam_selesai,
                durasi_jam,
                harga_per_jam,
                subtotal,
                kode_unik,
                grand_total,
                metode_bayar,
                created_at,
                updated_at,
                edit_count,
                late_min,
                canceled_at
            )
            SELECT
                NOW()                                            AS archived_at,
                CAST(b.id_pesanan AS UNSIGNED)                   AS id_pesanan,
                SUBSTRING(b.kode_booking, 1, 32)                 AS kode_booking,
                SUBSTRING(b.access_token, 1, 100)                AS access_token,
                b.status                                         AS status,         -- sudah 'batal' dari step update
                SUBSTRING(b.nama, 1, 100)                        AS nama,
                SUBSTRING(b.no_hp, 1, 50)                        AS no_hp,
                b.meja_id                                        AS meja_id,
                SUBSTRING(b.nama_meja, 1, 100)                   AS nama_meja,
                b.tanggal                                        AS tanggal,
                b.jam_mulai                                      AS jam_mulai,
                b.jam_selesai                                     AS jam_selesai,
                b.durasi_jam                                     AS durasi_jam,
                b.harga_per_jam                                  AS harga_per_jam,
                b.subtotal                                       AS subtotal,
                b.kode_unik                                      AS kode_unik,
                b.grand_total                                    AS grand_total,
                CASE
                    WHEN b.metode_bayar IS NULL THEN NULL
                    ELSE SUBSTRING(b.metode_bayar, 1, 50)
                END                                              AS metode_bayar,
                b.created_at                                      AS created_at,
                b.updated_at                                      AS updated_at,
                b.edit_count                                      AS edit_count,
                " . ($hasLateMinCol ? "b.late_min" : "NULL") . "  AS late_min,
                " . ($hasCanceledAt ? "b.canceled_at" : "NULL") . " AS canceled_at
            FROM pesanan_billiard b
            WHERE b.id_pesanan IN ($placeholders)
        ";
        $this->db->query($insertSql, $ids);

        // 3) Hapus dari tabel utama
        $deleteSql = "
            DELETE FROM pesanan_billiard
             WHERE id_pesanan IN ($placeholders)
        ";
        $this->db->query($deleteSql, $ids);

        // Selesaikan transaksi
        $this->db->trans_complete();

        if ( ! $this->db->trans_status()) {
            $err = $this->db->error(); // ['code'], ['message']
            log_message('error', '[CRON] cancel_expired ROLLBACK: '.$err['code'].' '.$err['message']);
            echo "ERR: ".$err['message']."\n";
            return;
        }

        $affected = count($ids);
        log_message('info', '[CRON] cancel_expired archived+deleted='.$affected.' ok=1');
        echo "OK {$affected}\n";
    }

    /**
     * Ambil daftar id_pesanan yang boleh dan harus di-cancel.
     * Pakai COALESCE(updated_at, created_at) supaya yang updated_at NULL tetap ke-detect.
     */
    private function _get_cancelable_ids(int $lateMinGlobal, bool $hasLateMinCol): array
    {
        $cancelables = "('draft','menunggu_bayar')";

        if ($hasLateMinCol) {
            $sql = "
                SELECT id_pesanan
                  FROM pesanan_billiard
                 WHERE status IN {$cancelables}
                   AND (metode_bayar IS NULL OR metode_bayar <> 'cash')
                   AND TIMESTAMPDIFF(
                         MINUTE,
                         COALESCE(updated_at, created_at),
                         NOW()
                       ) >= COALESCE(late_min, ?)
            ";
            $q = $this->db->query($sql, [$lateMinGlobal]);
        } else {
            $sql = "
                SELECT id_pesanan
                  FROM pesanan_billiard
                 WHERE status IN {$cancelables}
                   AND (metode_bayar IS NULL OR metode_bayar <> 'cash')
                   AND TIMESTAMPDIFF(
                         MINUTE,
                         COALESCE(updated_at, created_at),
                         NOW()
                       ) >= ?
            ";
            $q = $this->db->query($sql, [$lateMinGlobal]);
        }

        $rows = $q->result_array();
        if (!$rows) { return []; }

        // Map ke integer
        return array_map(function($r){ return (int)$r['id_pesanan']; }, $rows);
    }

    /**
     * Ambil toleransi telat bayar (menit) dari konfigurasi global.
     * Fallback aman minimal 15 menit kalau nilai kosong/0.
     */
    private function _late_min(): int
    {
        $web = $this->fm->web_me();
        $m = (int)($web->late_min ?? 15);
        return $m > 0 ? $m : 15;
    }
}
