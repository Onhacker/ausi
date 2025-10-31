<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron_exp_biliard extends Onhacker_Controller
{
    public function __construct(){
        parent::__construct();

        // Masih CLI-only
        if (!$this->input->is_cli_request()) { show_404(); }

        date_default_timezone_set('Asia/Makassar');
        $this->load->model('front_model','fm');
        $this->load->database();
    }

    /**
     * Auto-cancel booking yg lewat deadline bayar, lalu arsipkan & hapus.
     * Hanya sentuh status: draft | menunggu_bayar  (booking yg belum sah)
     * Yang sudah 'terkonfirmasi' tidak disentuh.
     */
    public function cancel_expired(){
        // sinkronkan timezone MySQL biar TIMESTAMPDIFF() bandingkan apel vs apel
        $this->db->query("SET time_zone = '+08:00'");

        $hasLateMinCol = $this->db->field_exists('late_min',    'pesanan_billiard');
        $hasCanceledAt = $this->db->field_exists('canceled_at', 'pesanan_billiard');

        // status yang boleh di-autocancel
        $cancelables = "('draft','menunggu_bayar')";

        // ambil grace period (menit) dari config global
        $lateMinGlobal = $this->_late_min(); // misal 60

        // 1. TENTUKAN kandidat yg expired
        // --------------------------------
        if ($hasLateMinCol) {
            // versi dengan kolom late_min per row
            $sqlGetIds = "
                SELECT id_pesanan
                FROM pesanan_billiard
                WHERE status IN {$cancelables}
                  AND (metode_bayar IS NULL OR metode_bayar <> 'cash')
                  AND TIMESTAMPDIFF(MINUTE, updated_at, NOW()) >= COALESCE(late_min, ?)
            ";
            $q = $this->db->query($sqlGetIds, [$lateMinGlobal]);
        } else {
            $sqlGetIds = "
                SELECT id_pesanan
                FROM pesanan_billiard
                WHERE status IN {$cancelables}
                  AND (metode_bayar IS NULL OR metode_bayar <> 'cash')
                  AND TIMESTAMPDIFF(MINUTE, updated_at, NOW()) >= ?
            ";
            $q = $this->db->query($sqlGetIds, [$lateMinGlobal]);
        }

        $rows = $q->result_array();
        if (!$rows || count($rows) === 0){
            echo "OK 0\n";
            return;
        }

        // daftar ID yg akan kita proses
        $ids = array_map(function($r){
            return (int)$r['id_pesanan'];
        }, $rows);

        // bikin placeholder "?, ?, ?, ..." sesuai jumlah id
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // 2. MULAI TRANSAKSI
        // --------------------------------
        $this->db->trans_start();

        // 2a. UPDATE status jadi 'batal' + timestamp
        //      (kita update DULU, biar snapshot yg kita arsip itu sudah status final)
        $updateSql = "
            UPDATE pesanan_billiard
               SET status='batal',
                   updated_at=NOW()".
                   ($hasCanceledAt ? ", canceled_at=NOW()" : "") ."
             WHERE id_pesanan IN ($placeholders)
        ";
        $this->db->query($updateSql, $ids);

        // 2b. INSERT ke tabel arsip billiard_expired
        //      -> kita salin field penting dari pesanan_billiard
        //      NOTE: sesuaikan daftar kolom sesuai struktur billiard_expired kamu.
        //      Di bawah ini aku asumsikan billiard_expired punya kolom:
        //      archived_at, id_pesanan, kode_booking, access_token, status, nama,
        //      no_hp, meja_id, nama_meja, tanggal, jam_mulai, jam_selesai,
        //      durasi_jam, harga_per_jam, subtotal, kode_unik, grand_total,
        //      metode_bayar, created_at, updated_at, edit_count,
        //      late_min, canceled_at
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
                NOW()            AS archived_at,
                b.id_pesanan     AS id_pesanan,
                b.kode_booking   AS kode_booking,
                b.access_token   AS access_token,
                b.status         AS status,
                b.nama           AS nama,
                b.no_hp          AS no_hp,
                b.meja_id        AS meja_id,
                b.nama_meja      AS nama_meja,
                b.tanggal        AS tanggal,
                b.jam_mulai      AS jam_mulai,
                b.jam_selesai    AS jam_selesai,
                b.durasi_jam     AS durasi_jam,
                b.harga_per_jam  AS harga_per_jam,
                b.subtotal       AS subtotal,
                b.kode_unik      AS kode_unik,
                b.grand_total    AS grand_total,
                b.metode_bayar   AS metode_bayar,
                b.created_at     AS created_at,
                b.updated_at     AS updated_at,
                b.edit_count     AS edit_count,
                ".($hasLateMinCol ? "b.late_min" : "NULL")." AS late_min,
                ".($hasCanceledAt ? "b.canceled_at" : "NULL")." AS canceled_at
            FROM pesanan_billiard b
            WHERE b.id_pesanan IN ($placeholders)
        ";
        $this->db->query($insertSql, $ids);

        // 2c. DELETE dari pesanan_billiard
        //      hanya yg barusan kita proses (jadi batal manual admin TIDAK ikut kehapus)
        $deleteSql = "
            DELETE FROM pesanan_billiard
            WHERE id_pesanan IN ($placeholders)
        ";
        $this->db->query($deleteSql, $ids);

        // 3. SELESAIKAN TRANSAKSI
        // --------------------------------
        $this->db->trans_complete();

        $ok = $this->db->trans_status();
        $affectedCount = count($ids);

        log_message(
            'info',
            '[CRON] cancel_expired auto-archived & deleted='.$affectedCount.' status='.$ok
        );

        echo "OK {$affectedCount}\n";
    }

    /**
     * Ambil toleransi telat bayar (menit) dari konfigurasi global.
     * fallback aman minimal 15 menit kalau nilai kosong / 0.
     */
    private function _late_min(): int {
        $web = $this->fm->web_me();
        $m = (int)($web->late_min ?? 15);
        return $m > 0 ? $m : 15;
    }
}
