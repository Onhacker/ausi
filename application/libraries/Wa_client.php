<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Wa_client {
    protected $CI;
    protected $api_url;
    protected $token;
    protected $secret;

    public function __construct(array $params = [])
    {
        $this->CI =& get_instance();
        $this->CI->load->database();

        // Ambil token/secret dari DB (identitas.id_identitas = 1)
        $row = $this->CI->db->select('wa_api_token, wa_api_secret')
            ->from('identitas')->where('id_identitas', 1)->get()->row();

        $this->token  = $params['token']  ?? ($row->wa_api_token  ?? null);
        $this->secret = $params['secret'] ?? ($row->wa_api_secret ?? null);

        // Jika pakai domain lain, bisa override lewat $params['api_url']
        $this->api_url = $params['api_url'] ?? 'https://deu.wablas.com/api/send-message';
    }

    /** Normalisasi nomor ke format Indonesia: 62xxxxxxxxxxx */
   public function normalize_msisdn($wa)
        {
            // 1. Ambil hanya digit
            $n = preg_replace('/\D+/', '', (string)$wa);
            if ($n === '') {
                return [
                    'msisdn'  => '',
                    'country' => null,
                ];
            }

            $country = null;

            // =========================
            // 1) HANDLE AUSTRALIA (+61...)
            // =========================
            //
            // Pola WA Australia biasanya: +61 4xxxxxxxx
            // Setelah preg_replace, "+61412..." jadi "61412..."
            //
            // Kita anggap:
            // - Kalau mulai "61", itu Australia. Biarkan.
            // - Ada kasus kadang orang ketik "0614xxxx". Kita rapikan jadi "614xxxx".
            //
            if (strpos($n, '61') === 0) {
                $country = 'AU';

                // "6104xxxx" -> "614xxxx"
                if (strpos($n, '610') === 0) {
                    // buang '0' setelah 61
                    // "6104xxxx" => "61" . "4xxxx"
                    $n = '61' . substr($n, 3);
                }

                // untuk AU kita gak ubah lagi
                return [
                    'msisdn'  => $n,
                    'country' => $country,
                ];
            }

            // =========================
            // 2) HANDLE INDONESIA (+62...)
            // =========================
            //
            // Kasus umum:
            //   6208123456   -> 62 + 8123456
            //   08123456789  -> 62 + 8123456789
            //   8123456789   -> 62 + 8123456789
            //   628123456789 -> sudah benar
            //
            if (strpos($n, '620') === 0) {
                // "6208xxxx" -> "62" . "8xxxx"
                $n = '62' . substr($n, 3);
                $country = 'ID';

            } elseif ($n[0] === '0') {
                // "08xxxx" -> "62" . "8xxxx"
                $n = '62' . substr($n, 1);
                $country = 'ID';

            } elseif ($n[0] === '8') {
                // "8xxxx" -> "62" . "8xxxx"
                $n = '62' . $n;
                $country = 'ID';

            } elseif (strpos($n, '62') === 0) {
                // sudah format internasional indo
                $country = 'ID';
            }

            // =========================
            // 3) DEFAULT (NEGARA LAIN)
            // =========================
            if ($country === null) {
                $country = 'INTL'; // kita gak kenal, biarkan asli
            }

            return [
                'msisdn'  => $n,
                'country' => $country,
            ];
        }


    /** Kirim 1 pesan WA */
    public function send_single($wa, $pesan): array
    {
        $phone = $this->normalize_msisdn($wa);

        if (empty($this->token)) {
            return [
                'success'   => false,
                'http_code' => 0,
                'error'     => 'Token WA API belum diisi. Set di menu Pengaturan/Identitas.',
            ];
        }

        $headers = [
            // Jika vendor butuh "token.secret" → pertahankan pola ini.
            'Authorization: ' . ($this->secret ? ($this->token.'.'.$this->secret) : $this->token),
            'Content-Type: application/x-www-form-urlencoded',
        ];

        $payload = http_build_query([
            'phone'   => $phone,
            'message' => (string)$pesan,
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $this->api_url,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 20,
            // PRODUKSI: sebaiknya TRUE. Saat dev lokal bisa nonaktif.
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $body      = curl_exec($ch);
        $curl_err  = curl_error($ch);
        $http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curl_err) {
            log_message('error', 'WA send cURL error: '.$curl_err);
            return ['success'=>false, 'http_code'=>0, 'error'=>$curl_err, 'body'=>$body];
        }

        // Sesuaikan dengan struktur respons vendor (contoh umum JSON)
        $json = json_decode($body, true);
        $ok   = ($http_code >= 200 && $http_code < 300);

        return [
            'success'   => $ok,
            'http_code' => $http_code,
            'body'      => $body,
            'json'      => $json,
        ];
    }
}
