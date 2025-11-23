<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rpp_gemini extends Onhacker_Controller
{
    // ============= SETTING GEMINI API =============
    // Ganti dengan API key milikmu dari Google AI Studio
    private $gemini_api_key = 'AIzaSyAc59vtjMm7efqYS6-VDRVTKUMsoOduGzA';
    private $gemini_model   = 'gemini-2.5-flash';

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['url', 'form']);
        // Supaya tidak ter-index mesin pencari
        $this->output->set_header('X-Robots-Tag: noindex, nofollow', true);
    }

    public function index()
    {
        $data = [
            'rpm_html'  => '',
            'error_msg' => '',
        ];

        if ($this->input->method(TRUE) === 'POST') {
            // ===== Ambil input form =====
            $nama_sekolah     = 'SMKN 1 WAJO'; // fixed sesuai permintaan
            $nama_guru        = trim((string)$this->input->post('nama_guru', TRUE));
            $mata_pelajaran   = trim((string)$this->input->post('mata_pelajaran', TRUE));
            $materi           = trim((string)$this->input->post('materi', TRUE));
            $kelas            = trim((string)$this->input->post('kelas', TRUE));
            $semester         = trim((string)$this->input->post('semester', TRUE));
            $fase             = trim((string)$this->input->post('fase', TRUE));
            $tahun_pelajaran  = trim((string)$this->input->post('tahun_pelajaran', TRUE));
            $pertemuan        = (int)$this->input->post('pertemuan');
            $total_waktu      = trim((string)$this->input->post('total_waktu', TRUE));

            if ($pertemuan <= 0) $pertemuan = 1;
            if ($mata_pelajaran === '') $mata_pelajaran = 'Bahasa Inggris';

            // ===== Susun prompt ke Gemini (minta JSON RPM) =====
            $prompt = <<<PROMPT
Sebagai seorang ahli kurikulum dan perencana pembelajaran di Indonesia, buatlah konten untuk Rencana Pembelajaran Mendalam (RPM) berdasarkan data berikut. Rencana ini harus mencakup {$pertemuan} pertemuan yang berbeda. Berikan hasil dalam format JSON saja (tanpa penjelasan di luar JSON). Setiap nilai yang berupa daftar (misalnya beberapa tujuan atau beberapa aktivitas) ditulis sebagai satu STRING dengan banyak baris, dipisahkan dengan line break (\n).

Data input:
- nama sekolah: {$nama_sekolah}
- nama guru: {$nama_guru}
- mata pelajaran: {$mata_pelajaran}
- materi pokok: {$materi}
- kelas: {$kelas}
- semester: {$semester}
- fase: {$fase}
- tahun ajaran: {$tahun_pelajaran}
- deskripsi total waktu: {$total_waktu}

Struktur JSON yang diminta:

{
  "tujuan_pembelajaran": "STRING berisi beberapa tujuan, dipisahkan baris baru",
  "kesiapan_murid": "STRING",
  "karakteristik_mapel": "STRING",
  "dimensi_profil_lulusan": "STRING berisi beberapa poin Dimensi profil lulusan yang terkait, dipisahkan baris baru",
  "pertemuan": [
    {
      "judul_pertemuan": "STRING",
      "alokasi_waktu_pertemuan": "STRING, misalnya: 3 x 45 menit",
      "kerangka_pembelajaran_rinci": "STRING berisi langkah pendahuluan, kegiatan inti, dan penutup, dipisahkan baris baru",
      "aktivitas_memahami": "STRING berisi beberapa aktivitas, dipisahkan baris baru",
      "aktivitas_mengaplikasikan": "STRING berisi beberapa aktivitas, dipisahkan baris baru",
      "aktivitas_merefleksikan": "STRING berisi beberapa aktivitas, dipisahkan baris baru"
    }
    ... (jumlah elemen array harus {$pertemuan})
  ],
  "asesmen_awal": "STRING berisi beberapa poin, dipisahkan baris baru",
  "asesmen_proses": "STRING berisi beberapa poin, dipisahkan baris baru",
  "asesmen_akhir": "STRING berisi beberapa poin, dipisahkan baris baru"
}

Pastikan JSON valid dan dapat di-parse tanpa error.
PROMPT;

            $error = '';
            $json  = $this->_call_gemini_rpm_json($prompt, $error);

            if ($json === null) {
                $data['error_msg'] = ($error !== '') ? $error : 'Terjadi kesalahan saat memanggil Gemini.';
            } else {
                // Susun HTML tabel rapi berdasarkan JSON
                $data['rpm_html'] = $this->_build_rpm_html($json, [
                    'nama_sekolah'    => $nama_sekolah,
                    'nama_guru'       => $nama_guru,
                    'mata_pelajaran'  => $mata_pelajaran,
                    'materi'          => $materi,
                    'kelas'           => $kelas,
                    'semester'        => $semester,
                    'fase'            => $fase,
                    'tahun_pelajaran' => $tahun_pelajaran,
                    'pertemuan'       => $pertemuan,
                    'total_waktu'     => $total_waktu,
                ]);
            }
        }

        $this->load->view('rpp_gemini_rpm', $data);
    }

    /**
     * Download dokumen sebagai .doc (Word)
     */
    public function download()
    {
        $contentEscaped = $this->input->post('rpm_content', FALSE);
        if ($contentEscaped === null || $contentEscaped === '') {
            show_404();
            return;
        }

        $content  = htmlspecialchars_decode($contentEscaped, ENT_NOQUOTES);
        $filename = 'RPP_' . date('Ymd_His') . '.doc';

        header("Content-Type: application/msword; charset=UTF-8");
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header("Cache-Control: no-store, no-cache, must-revalidate");

        echo "<html><head><meta charset=\"UTF-8\"><title>Rencana Pembelajaran</title>
        <style>
          body { font-family: 'Times New Roman', serif; font-size: 11pt; }
          table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
          th, td { border: 1px solid #000; padding: 6px; vertical-align: top; }
          th { background-color: #f2f2f2; font-weight: bold; }
          th.meeting-header { background-color: #e0f2fe; }
          .numbered-list { padding-left: 0; margin: 0; }
          .numbered-list > div { margin-bottom: 2px; }
        </style>
        </head><body>";
        echo $content;
        echo "</body></html>";
        exit;
    }

    // ================== HELPER: panggil Gemini & parse JSON ==================

    private function _call_gemini_rpm_json(string $prompt, string &$error = '')
    {
        if (empty($this->gemini_api_key)) {
            $error = 'API key Gemini belum diset di controller.';
            return null;
        }

        $url = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
            $this->gemini_model,
            $this->gemini_api_key
        );

        $payload = [
            'contents' => [[
                'role'  => 'user',
                'parts' => [['text' => $prompt]],
            ]],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
            ],
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 60,
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            $error = 'cURL error: ' . curl_error($ch);
            curl_close($ch);
            return null;
        }
        curl_close($ch);

        $data = json_decode($response, true);
        if (isset($data['error']['message'])) {
            $error = 'ERROR dari Gemini: ' . $data['error']['message'];
            return null;
        }

        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            $error = 'Format respon Gemini tidak dikenali.';
            return null;
        }

        $raw = $data['candidates'][0]['content']['parts'][0]['text'];
        $json = json_decode($raw, true);
        if ($json === null) {
            $error = 'Gagal decode JSON dari Gemini.';
            return null;
        }

        return $json;
    }

    // ================== HELPER: format teks multi-baris ke HTML ==================

    private function _fmt_multiline(?string $text): string
    {
        $text = trim((string)$text);
        if ($text === '') return '-';

        $lines = preg_split('/\r\n|\r|\n/', $text);
        $clean = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== '') $clean[] = $line;
        }

        if (count($clean) === 0) return '-';

        if (count($clean) === 1) {
            return '<div class="numbered-list">' .
                   htmlspecialchars($clean[0], ENT_QUOTES, 'UTF-8') .
                   '</div>';
        }

        $html = '<div class="numbered-list">';
        $i = 1;
        foreach ($clean as $line) {
            $display = $line;
            if (!preg_match('/^(\d+\.|-|\*)\s*/', $line)) {
                $display = $i . '. ' . $line;
            }
            $html .= '<div>' . htmlspecialchars($display, ENT_QUOTES, 'UTF-8') . '</div>';
            $i++;
        }
        $html .= '</div>';

        return $html;
    }

    // ================== HELPER: susun HTML tabel RPM ==================

    private function _build_rpm_html(array $g, array $meta): string
    {
        $s = function($key, $default = '-') use ($meta) {
            $v = $meta[$key] ?? '';
            return $v !== '' ? htmlspecialchars($v, ENT_QUOTES, 'UTF-8') : $default;
        };

        $tujuan   = $this->_fmt_multiline($g['tujuan_pembelajaran']   ?? '');
        $kesiapan = $this->_fmt_multiline($g['kesiapan_murid']        ?? '');
        $kar_mapel= $this->_fmt_multiline($g['karakteristik_mapel']   ?? '');
        $profil   = $this->_fmt_multiline($g['dimensi_profil_lulusan']?? '');
        $ases_awal   = $this->_fmt_multiline($g['asesmen_awal']   ?? '');
        $ases_proses = $this->_fmt_multiline($g['asesmen_proses'] ?? '');
        $ases_akhir  = $this->_fmt_multiline($g['asesmen_akhir']  ?? '');

        // Per pertemuan (D, E, F, ...)
        $pertemuanHtml = '';
        $meetings = isset($g['pertemuan']) && is_array($g['pertemuan']) ? $g['pertemuan'] : [];
        $startCode = ord('D');

        foreach ($meetings as $idx => $meet) {
            $code          = chr($startCode + $idx);
            $judul         = htmlspecialchars((string)($meet['judul_pertemuan'] ?? 'Tanpa Judul'), ENT_QUOTES, 'UTF-8');
            $alok          = htmlspecialchars((string)($meet['alokasi_waktu_pertemuan'] ?? '-'), ENT_QUOTES, 'UTF-8');
            $kerangka      = $this->_fmt_multiline($meet['kerangka_pembelajaran_rinci'] ?? '');
            $memahami      = $this->_fmt_multiline($meet['aktivitas_memahami']         ?? '');
            $mengaplikasi  = $this->_fmt_multiline($meet['aktivitas_mengaplikasikan']  ?? '');
            $merefleksikan = $this->_fmt_multiline($meet['aktivitas_merefleksikan']    ?? '');

            $pertemuanHtml .= "
            <thead>
              <tr>
                <th colspan=\"2\" class=\"meeting-header\">{$code}. PERTEMUAN " . ($idx + 1) . ": {$judul}</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class=\"w-1/3 font-bold bg-gray-50\">Alokasi Waktu Pertemuan</td>
                <td>{$alok}</td>
              </tr>
              <tr>
                <td class=\"w-1/3 font-bold bg-gray-50\">Kerangka Pembelajaran Rinci</td>
                <td>{$kerangka}</td>
              </tr>
              <tr>
                <td class=\"font-bold\">Aktivitas: Memahami</td>
                <td>{$memahami}</td>
              </tr>
              <tr>
                <td class=\"font-bold\">Aktivitas: Mengaplikasikan</td>
                <td>{$mengaplikasi}</td>
              </tr>
              <tr>
                <td class=\"font-bold\">Aktivitas: Merefleksikan</td>
                <td>{$merefleksikan}</td>
              </tr>
            </tbody>";
        }

        $ases_code = chr($startCode + max(0, count($meetings)));

        $nama_guru  = $s('nama_guru');
        $nama_sklh  = $s('nama_sekolah');
        $mapel      = $s('mata_pelajaran');
        $materi     = $s('materi');
        $tahun      = $s('tahun_pelajaran');
        $fase_kls_sem = $s('fase') . '/' . $s('kelas') . '/' . $s('semester');
        $total      = $s('total_waktu') . ' (' . $s('pertemuan') . ' Pertemuan)';

        $html = "
<div class=\"prose prose-sm max-w-none text-gray-800 document-container\">
  <h3 class=\"font-bold text-center text-lg mb-4\">RENCANA PEMBELAJARAN MENDALAM</h3>
  <hr class=\"my-4\" />
  <table class=\"document-table\">
    <thead>
      <tr><th colspan=\"2\" class=\"bg-gray-200\">A. IDENTITAS</th></tr>
    </thead>
    <tbody>
      <tr><td class=\"w-1/3 font-bold\">Nama Penyusun</td><td class=\"w-2/3\">{$nama_guru}</td></tr>
      <tr><td class=\"font-bold\">Nama Sekolah</td><td>{$nama_sklh}</td></tr>
      <tr><td class=\"font-bold\">Mata Pelajaran</td><td>{$mapel}</td></tr>
      <tr><td class=\"font-bold\">Materi Pokok</td><td>{$materi}</td></tr>
      <tr><td class=\"font-bold\">Tahun Ajaran</td><td>{$tahun}</td></tr>
      <tr><td class=\"font-bold\">Fase/Kelas/Semester</td><td>{$fase_kls_sem}</td></tr>
      <tr><td class=\"font-bold\">Total Alokasi Waktu</td><td>{$total}</td></tr>
    </tbody>

    <thead>
      <tr><th colspan=\"2\" class=\"bg-gray-200 mt-4\">B. IDENTIFIKASI PESERTA DIDIK & MAPEL</th></tr>
    </thead>
    <tbody>
      <tr><td class=\"w-1/3 font-bold\">Kesiapan Murid</td><td class=\"w-2/3\">{$kesiapan}</td></tr>
      <tr><td class=\"font-bold\">Karakteristik Mata Pelajaran</td><td>{$kar_mapel}</td></tr>
      <tr><td class=\"font-bold\">Dimensi Profil Lulusan</td><td>{$profil}</td></tr>
    </tbody>

    <thead>
      <tr><th colspan=\"2\" class=\"bg-gray-200 mt-4\">C. TUJUAN PEMBELAJARAN</th></tr>
    </thead>
    <tbody>
      <tr><td class=\"w-1/3 font-bold\">Tujuan Pembelajaran</td><td class=\"w-2/3\">{$tujuan}</td></tr>
    </tbody>

    {$pertemuanHtml}

    <thead>
      <tr><th colspan=\"2\" class=\"bg-gray-200 mt-4\">{$ases_code}. ASESMEN</th></tr>
    </thead>
    <tbody>
      <tr><td class=\"w-1/3 font-bold\">Assessment of Learning (Awal)</td><td>{$ases_awal}</td></tr>
      <tr><td class=\"font-bold\">Assessment as Learning (Proses)</td><td>{$ases_proses}</td></tr>
      <tr><td class=\"font-bold\">Assessment for Learning (Akhir)</td><td>{$ases_akhir}</td></tr>
    </tbody>
  </table>
</div>";

        return $html;
    }
}
