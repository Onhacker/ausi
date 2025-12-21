<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Gmail_sync_service {

  /** @var CI_Controller */
  private $CI;

  public function __construct(){
    $this->CI =& get_instance();
    date_default_timezone_set('Asia/Makassar');
  }

  public function sync(int $limit = 20): array
  {
    $limit = max(1, min(200, (int)$limit));

    // ✅ cegah dobel sync (cron + klik user)
    if (!$this->_acquire_lock()) {
      return ['ok'=>true, 'msg'=>'Sync sedang berjalan, skip.'];
    }

    try {
      return $this->_gmail_sync($limit);
    } finally {
      $this->_release_lock();
    }
  }

  private function _acquire_lock(): bool
  {
    $row = $this->CI->db->query("SELECT GET_LOCK('gmail_sync_lock', 0) AS l")->row();
    return (int)($row->l ?? 0) === 1;
  }

  private function _release_lock(): void
  {
    $this->CI->db->query("SELECT RELEASE_LOCK('gmail_sync_lock')");
  }

  private function _gmail_load_token(): array
  {
    $row = $this->CI->db->select('gmail_token')->get_where('settings', ['id'=>1])->row();
    if (!$row || !$row->gmail_token) return [];
    $tok = json_decode((string)$row->gmail_token, true);
    return is_array($tok) ? $tok : [];
  }

  private function _gmail_save_token(array $tok): void
  {
    if (empty($tok['created'])) $tok['created'] = time();
    $this->CI->db->where('id', 1)->update('settings', [
      'gmail_token'      => json_encode($tok),
      'token_updated_at' => date('Y-m-d H:i:s'),
    ]);
  }

  private function _gmail_service_ready(): \Google\Service\Gmail
  {
    $this->CI->load->library('Gmail_oauth');

    $tok = $this->_gmail_load_token();
    if (empty($tok['access_token'])) {
      throw new Exception('Token Gmail belum tersimpan. Silakan Connect Gmail dulu.');
    }

    $client = Gmail_oauth::client(false);
    $client->setAccessToken($tok);

    if ($client->isAccessTokenExpired()) {
      $refresh = $tok['refresh_token'] ?? '';
      if ($refresh === '') {
        throw new Exception('Access token expired tapi refresh_token tidak ada. Reconnect OAuth (offline+consent).');
      }

      $newTok = $client->fetchAccessTokenWithRefreshToken($refresh);
      if (isset($newTok['error'])) {
        throw new Exception('Refresh token gagal: '.$newTok['error']);
      }

      // merge aman (refresh_token jangan ketimpa kosong)
      $merged = $tok;
      foreach ($newTok as $k => $v) {
        if ($k === 'refresh_token' && empty($v)) continue;
        $merged[$k] = $v;
      }
      $merged['refresh_token'] = $merged['refresh_token'] ?? $refresh;

      $this->_gmail_save_token($merged);
      $client->setAccessToken($merged);
    }

    return new \Google\Service\Gmail($client);
  }

  private function _gmail_api_retry(callable $fn, int $maxAttempts = 5)
  {
    $attempt = 0;
    $delayUs = 250000; // 250ms

    while (true) {
      try {
        return $fn();
      } catch (\Google\Service\Exception $e) {
        $code = (int)$e->getCode();
        $msg  = (string)$e->getMessage();

        $retryable =
          in_array($code, [429, 500, 503], true) ||
          ($code === 403 && preg_match('/rateLimitExceeded|userRateLimitExceeded|quotaExceeded|backendError|internalError/i', $msg));

        if (!$retryable || $attempt >= ($maxAttempts - 1)) throw $e;

        usleep($delayUs);
        $delayUs = min($delayUs * 2, 3000000); // cap 3 detik
        $attempt++;
      }
    }
  }

  private function _gmail_sync(int $limit = 20): array
  {
    try {
      $svc = $this->_gmail_service_ready();

      $profile = $this->_gmail_api_retry(fn() => $svc->users->getProfile('me'));
      $emailMe = (string)$profile->getEmailAddress();

      $settings = $this->CI->db->get_where('settings',['id'=>1])->row();
      $lastSyncOld = $settings->gmail_last_sync_at ?? null;

      // query incremental
      if (!empty($lastSyncOld)) {
        $dt = new DateTime($lastSyncOld);
        $dt->modify('-1 day');
        $q = 'after:'.$dt->format('Y/m/d');
      } else {
        // DB kosong pertama kali -> default 7 hari
        $q = 'newer_than:7d';
        // kalau mau lebih banyak saat awal, ganti mis: newer_than:30d
      }

      $params = [
        'maxResults' => $limit,
        'labelIds'   => ['INBOX'],
        'q'          => $q,
      ];

      $list = $this->_gmail_api_retry(fn() => $svc->users_messages->listUsersMessages('me', $params));
      $msgs = $list->getMessages() ?: [];
      $fetched = count($msgs);

      if ($fetched === 0) {
        return [
          'ok' => true,
          'me' => $emailMe,
          'gmail_total' => (int)$profile->getMessagesTotal(),
          'fetched' => 0,
          'inserted' => 0,
          'db_fail' => 0,
          'q' => $q,
          'last_sync_old' => $lastSyncOld,
          'last_sync_new' => $lastSyncOld,
        ];
      }

      // prefetch gmail_id existing
      $ids = array_map(fn($m) => $m->getId(), $msgs);
      $existsSet = [];
      $existsRows = $this->CI->db->select('gmail_id')
                                 ->from('gmail_inbox')
                                 ->where_in('gmail_id', $ids)
                                 ->get()->result();
      foreach ($existsRows as $er) $existsSet[(string)$er->gmail_id] = true;

      $inserted = 0;
      $dbFail   = 0;
      $maxInternalMs = 0;

      foreach ($msgs as $m) {
        $gid = (string)$m->getId();
        if (isset($existsSet[$gid])) continue;

        $msg = $this->_gmail_api_retry(fn() =>
          $svc->users_messages->get('me', $gid, [
            'format' => 'metadata',
            'metadataHeaders' => ['From','Subject','Date'],
          ])
        );

        $from = $subject = '';
        foreach (($msg->getPayload()->getHeaders() ?: []) as $h){
          if ($h->getName()==='From')    $from = $h->getValue();
          if ($h->getName()==='Subject') $subject = $h->getValue();
        }

        $internalMs = (int)$msg->getInternalDate();
        if ($internalMs > $maxInternalMs) $maxInternalMs = $internalMs;

        $receivedAt = $internalMs
          ? date('Y-m-d H:i:s', (int)($internalMs/1000))
          : date('Y-m-d H:i:s');

        $snippet = (string)$msg->getSnippet();

        $trxRef = null;
        if (preg_match('/No\s*Referensi:\s*([A-Z0-9]+)/i', $snippet, $mm)) {
          $trxRef = strtoupper($mm[1]);
        }

        $ok = $this->CI->db->insert('gmail_inbox', [
          'gmail_id'    => $gid,
          'from_email'  => $from,
          'subject'     => $subject,
          'snippet'     => $snippet,
          'raw'         => json_encode($msg),
          'status'      => 'baru',
          'received_at' => $receivedAt,
          'created_at'  => date('Y-m-d H:i:s'),
          // 'trx_ref'   => $trxRef, // aktifkan kalau kolom trx_ref ada
        ]);

        if ($ok) {
          $inserted++;
        } else {
          $err = $this->CI->db->error();
          if ((int)($err['code'] ?? 0) === 1062) continue; // uq_gmail_id
          $dbFail++;
        }
      }

      // update last sync
      $lastSyncNew = $lastSyncOld;
      if ($maxInternalMs > 0) {
        $lastSyncNew = date('Y-m-d H:i:s', (int)($maxInternalMs/1000));
      } elseif ($fetched > 0 && $fetched < $limit) {
        $lastSyncNew = date('Y-m-d H:i:s');
      }

      if (!empty($lastSyncNew) && $lastSyncNew !== $lastSyncOld) {
        $this->CI->db->where('id',1)->update('settings', ['gmail_last_sync_at' => $lastSyncNew]);
      }

      return [
        'ok' => true,
        'me' => $emailMe,
        'gmail_total' => (int)$profile->getMessagesTotal(),
        'fetched' => $fetched,
        'inserted' => $inserted,
        'db_fail' => $dbFail,
        'q' => $q,
        'last_sync_old' => $lastSyncOld,
        'last_sync_new' => $lastSyncNew,
      ];

    } catch (\Google\Service\Exception $e) {
      return ['ok'=>false,'msg'=>'Gmail API error: '.$e->getMessage()];
    } catch (\Throwable $e) {
      return ['ok'=>false,'msg'=>'Server error: '.$e->getMessage()];
    }
  }
}
