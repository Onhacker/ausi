<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron_cart extends Onhacker_Controller
{
    public function __construct(){
        parent::__construct();
        if (!$this->input->is_cli_request()) { show_404(); }
        $this->load->database();
        $this->load->driver('cache', ['adapter' => 'file']); // lock sederhana
        // pastikan TZ (opsional, kalau belum di config)
        @date_default_timezone_set('Asia/Makassar');
    }

    /* ====== Lock agar tidak double-run ====== */
    private function _acquire_lock($name = 'cart_cleanup', $ttl = 900){ // 15 menit
        $key  = 'cron_lock_'.$name;
        $lock = $this->cache->get($key);
        $now  = time();
        if (is_array($lock) && ($now - ((int)$lock['ts'])) < $ttl) {
            echo "[SKIP] Another run in progress: {$name}\n";
            exit(0);
        }
        $this->cache->save($key, ['ts'=>$now, 'pid'=>getmypid()], $ttl);
        return $key;
    }
    private function _release_lock($key){ $this->cache->delete($key); }

    /* ====== Langkah-langkah core ====== */
    private function _purge_orphan_items(): int {
        $sql = "DELETE cmi
                  FROM cart_meja_item cmi
             LEFT JOIN cart_meja cm ON cm.id = cmi.cart_id
                 WHERE cm.id IS NULL";
        $this->db->query($sql);
        $n = $this->db->affected_rows();
        log_message('info', "cron_cart: orphan_items_deleted={$n}");
        echo "[OK] Orphan items deleted: {$n}\n";
        return (int)$n;
    }

    private function _expire_open(int $minutes = 120): int {
        $sql = "UPDATE cart_meja
                   SET status = 'expired', updated_at = NOW()
                 WHERE status = 'open'
                   AND updated_at < (NOW() - INTERVAL ? MINUTE)";
        $this->db->query($sql, [$minutes]);
        $n = $this->db->affected_rows();
        log_message('info', "cron_cart: open_expired={$n}, minutes={$minutes}");
        echo "[OK] Open carts expired (>{$minutes}m): {$n}\n";
        return (int)$n;
    }

    private function _purge_non_open(int $days = 1): array {
        $this->db->trans_begin();

        $sql1 = "DELETE cmi
                   FROM cart_meja_item cmi
                   JOIN cart_meja cm ON cm.id = cmi.cart_id
                  WHERE cm.status <> 'open'
                    AND cm.updated_at < (NOW() - INTERVAL ? DAY)";
        $this->db->query($sql1, [$days]);
        $del_items = $this->db->affected_rows();

        $sql2 = "DELETE cm
                   FROM cart_meja cm
                  WHERE cm.status <> 'open'
                    AND cm.updated_at < (NOW() - INTERVAL ? DAY)";
        $this->db->query($sql2, [$days]);
        $del_carts = $this->db->affected_rows();

        if (!$this->db->trans_status()){
            $this->db->trans_rollback();
            echo "[ERR] Purge non-open rollback\n";
            return [0,0];
        }
        $this->db->trans_commit();

        log_message('info', "cron_cart: non_open_purged_items={$del_items}, carts={$del_carts}, days={$days}");
        echo "[OK] Non-open purged: items={$del_items}, carts={$del_carts} (>{$days}d)\n";
        return [$del_items, $del_carts];
    }

    /* ====== Entrypoints ====== */

    // php index.php cron_cart run [expire_minutes] [purge_days]
    public function run($expire_minutes = 120, $purge_days = 1){
        $lock = $this->_acquire_lock('cart_cleanup', 900);
        echo "== Cart cleanup start ==\n";
        $this->_purge_orphan_items();
        $this->_expire_open((int)$expire_minutes);
        $this->_purge_non_open((int)$purge_days);
        $this->_release_lock($lock);
        echo "== Cart cleanup done ==\n";
    }

    // php index.php cron_cart expire [minutes]
    public function expire($minutes = 120){
        $lock = $this->_acquire_lock('cart_expire', 600);
        $this->_expire_open((int)$minutes);
        $this->_release_lock($lock);
    }

    // php index.php cron_cart purge [days]
    public function purge($days = 1){
        $lock = $this->_acquire_lock('cart_purge', 600);
        $this->_purge_orphan_items();
        $this->_purge_non_open((int)$days);
        $this->_release_lock($lock);
    }
}
