<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_admin_dashboard extends CI_Model
{
    /**
     * Tandai otomatis booking yang sudah lewat jadwal sebagai 'expired'.
     *
     * Kriteria:
     *  - Belum check-in (checkin_at IS NULL)
     *  - Status masih 'pending' atau 'approved'
     *  - TIMESTAMP(tanggal, jam) < (now - grace_minutes)
     *
     * @param int $grace_minutes  Waktu toleransi (menit) setelah jadwal. Default 30 menit.
     * @param string $tz          Timezone yang dipakai untuk cutoff (default Asia/Makassar).
     * @return int                Jumlah baris yang di-update.
     */
    public function expire_past_bookings(int $grace_minutes = 30, string $tz = 'Asia/Makassar'): int
    {
        if ($grace_minutes < 0 || $grace_minutes > 1440) {
            $grace_minutes = 30;
        }

        // Hitung cutoff berdasarkan timezone lokal yang diinginkan
        $dt = new DateTime('now', new DateTimeZone($tz));
        if ($grace_minutes > 0) {
            $dt->modify("-{$grace_minutes} minutes");
        }
        $cutoff = $dt->format('Y-m-d H:i:s');

        // Jalankan UPDATE langsung (paling rapi untuk ekspresi TIMESTAMP(tanggal, jam))
        $sql = "
        UPDATE booking_tamu
        SET status = 'expired'
        WHERE checkin_at IS NULL
        AND status IN ('pending','approved')
        AND schedule_dt < ?
        AND schedule_dt IS NOT NULL
        ";

        $this->db->query($sql, [$cutoff]);

        return $this->db->affected_rows();
    }

    /**
     * Lihat berapa yang "akan di-expire" tanpa mengubah data (dry-run).
     */
    public function count_will_expire(int $grace_minutes = 30, string $tz = 'Asia/Makassar'): int
    {
        $dt = new DateTime('now', new DateTimeZone($tz));
        if ($grace_minutes > 0) {
            $dt->modify("-{$grace_minutes} minutes");
        }
        $cutoff = $dt->format('Y-m-d H:i:s');

        $sql = "
            SELECT COUNT(*) AS c
              FROM booking_tamu
             WHERE checkin_at IS NULL
               AND status IN ('pending','approved')
               AND TIMESTAMP(tanggal, jam) < ?
        ";
        $row = $this->db->query($sql, [$cutoff])->row_array();
        return (int)($row['c'] ?? 0);
    }
}
