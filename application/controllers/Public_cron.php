<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Public_cron extends Onhacker_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->output->set_content_type('text/plain'); // respons jelas di browser/CLI
    }

    /** Izinkan CLI tanpa key; HTTP wajib pakai key */
    private function _require_key(): void
    {
        if ($this->input->is_cli_request()) return;
        $key    = (string)$this->input->get('key', true);
        $expect = (string)$this->config->item('cron_secret');
        if (!$expect || !hash_equals($expect, $key)) { show_404(); exit; }
    }

    /** Now (lokal Makassar) */
    private function _nowLocal(string $tz = 'Asia/Makassar'): string
    {
        $dt = new DateTime('now', new DateTimeZone($tz));
        return $dt->format('Y-m-d H:i:s');
    }

    /** Hitung cutoff (lokal Makassar) dari menit grace */
    private function _cutoff(int $grace, string $tz = 'Asia/Makassar'): string
    {
        if ($grace < 0 || $grace > 1440) $grace = 30;
        $dt = new DateTime('now', new DateTimeZone($tz));
        if ($grace > 0) $dt->modify("-{$grace} minutes");
        return $dt->format('Y-m-d H:i:s');
    }

    /** Ping cepat */
    public function ping(): void
    {
        $this->_require_key();
        $msg = "[PING] ".date('c')." tz=Asia/Makassar\n";
        echo $msg;
        @file_put_contents('/tmp/cron_ping.log', $msg, FILE_APPEND);
        exit(0);
    }

    /** Dry-run: hitung yang akan di-expire (pakai jadwal_at + waktu Makassar) */
    public function korban($grace_minutes = 30): void
    {
        $this->_require_key();

        $grace  = (int)$grace_minutes;
        $cutoff = $this->_cutoff($grace); // Asia/Makassar

        $q = $this->db->query("
            SELECT COUNT(*) AS n
              FROM booking_tamu
             WHERE checkin_at IS NULL
               AND status IN ('pending','approved')
               AND jadwal_at < ?
        ", [$cutoff])->row();

        $will = (int)($q->n ?? 0);
        echo "[CHECK] will_expire={$will}, grace={$grace}m, cutoff={$cutoff}, tz=Asia/Makassar\n";
        exit(0);
    }

    /** Eksekusi expire (expired_at diset ke waktu Makassar) */
    public function expire_bookings($grace_minutes = 30): void
    {
        $this->_require_key();

        $grace     = (int)$grace_minutes;
        $cutoff    = $this->_cutoff($grace);   // batas jadwal yang lewat (WITA)
        $expiredAt = $this->_nowLocal();       // cap waktu expired (WITA)

        // Hitung calon (untuk log)
        $will = (int)$this->db->query("
            SELECT COUNT(*) AS n
              FROM booking_tamu
             WHERE checkin_at IS NULL
               AND status IN ('pending','approved')
               AND jadwal_at < ?
        ", [$cutoff])->row()->n;

        // Update: pakai expired_at = waktu Makassar (dibind dari PHP)
        $this->db->query("
            UPDATE booking_tamu
               SET status    = 'expired',
                   expired_at = ?
             WHERE checkin_at IS NULL
               AND status IN ('pending','approved')
               AND jadwal_at < ?
        ", [$expiredAt, $cutoff]);

        $err      = $this->db->error();
        $affected = $this->db->affected_rows();

        if (!empty($err['code'])) {
            $msg = "[SQLERR] code={$err['code']} msg={$err['message']} cutoff={$cutoff} expired_at={$expiredAt}\n";
            echo $msg;
            log_message('error', "[CRON] ".$msg);
            @file_put_contents('/tmp/cron_expire.log', $msg, FILE_APPEND);
            exit(1);
        }

        $msg = "[EXPIRE] will={$will} affected={$affected} grace={$grace}m cutoff={$cutoff} expired_at={$expiredAt} tz=Asia/Makassar\n";
        echo $msg;
        log_message('error', "[CRON] ".trim($msg));
        @file_put_contents('/tmp/cron_expire.log', $msg, FILE_APPEND);
        exit(0);
    }
}
