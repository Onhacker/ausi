<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rpm extends Onhacker_Controller
{
    // ============= SETTING GEMINI API =============
    // Ganti dengan API key milikmu dari Google AI Studio
    private $gemini_api_key;
    private $gemini_model;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['url', 'form']);

        // load config gemini
        $this->load->config('gemini');
        $this->gemini_api_key = (string) $this->config->item('gemini_api_key');
        $this->gemini_model   = (string) ($this->config->item('gemini_model') ?: 'gemini-2.5-flash');

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
            $perintah_tambahan = trim((string)$this->input->post('perintah_tambahan', TRUE));

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
  "dimensi_profil_lulusan": "STRING berisi beberapa poin dan penjelasan Dimensi profil lulusan yang terkait, dipisahkan baris baru",
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
  "asesmen_akhir": "STRING berisi beberapa poin, dipisahkan baris baru",

  "kisi_kisi": [
    {
      "indikator": "STRING indikator soal yang jelas dan terukur, diturunkan dari tujuan_pembelajaran dan asesmen",
      "bentuk_soal": "Pilihan Ganda / Uraian Singkat / Uraian Panjang",
      "level_kognitif": "Misalnya: C1, C2, C3, atau sebutan lain yang relevan",
      "nomor_soal": "Nomor soal yang terkait, misalnya: 1 atau 1-2",
      "aspek_dinilai": "Aspek kemampuan yang dinilai (misalnya: memahami teks, menyusun kalimat, dsb.)",
      "skor_maksimal": 1
    }
    ... (minimal 5 entri)
  ],

  "soal_pilgan": [
    {
      "nomor": 1,
      "indikator": "STRING, harus konsisten dengan salah satu indikator di kisi_kisi",
      "pertanyaan": "Teks soal pilihan ganda",
      "opsi_a": "Pilihan A",
      "opsi_b": "Pilihan B",
      "opsi_c": "Pilihan C",
      "opsi_d": "Pilihan D",
      "kunci": "A/B/C/D"
    }
    ... (minimal 5 soal)
  ],

  "soal_uraian": [
    {
      "nomor": 11,
      "indikator": "STRING, harus konsisten dengan salah satu indikator di kisi_kisi",
      "pertanyaan": "Teks soal uraian",
      "pedoman_skor": "STRING berisi pedoman penskoran per poin jawaban, setiap poin dipisahkan baris baru (\\n)"
    }
    ... (1–3 soal uraian)
  ]
}


Pastikan JSON valid dan dapat di-parse tanpa error.
PROMPT;
// Jika ada perintah tambahan dari guru, sisipkan di akhir prompt
if ($perintah_tambahan !== '') {
    $prompt .= "\n\nPerintah tambahan dari guru terkait format/isi output (WAJIB diikuti):\n"
             . $perintah_tambahan . "\n";
}


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

             echo "<html><head><meta charset=\"UTF-8\"><title>Rencana Pembelajaran Mendalam SMKN 1 WAJO</title>
<style>
  body {
    font-family: 'Times New Roman', serif;
    font-size: 12pt;
    line-height: 150%;              /* SPASI 1,5 UMUM */
    mso-line-height-alt: 18pt;      /* BANTU WORD BACA 1,5 */
  }

  /* pastikan paragraf & list ikut 1,5 juga */
  p, li {
    margin-top: 0;
    margin-bottom: 4px;
    line-height: 150%;
    mso-line-height-alt: 18pt;
  }

  td, th {
    line-height: 150%;
    mso-line-height-alt: 18pt;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
  }
  th, td {
    border: 1px solid #000;
    padding: 6px;
    vertical-align: top;
  }
  th {
    background-color: #f2f2f2;
    font-weight: bold;
  }
  th.meeting-header {
    background-color: #e0f2fe;
  }

  .numbered-list {
    margin: 0;
    padding-left: 1.0em;  /* agak dekat */
  }
  .numbered-list li {
    margin-bottom: 2px;
    text-align: justify;
  }

 
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
    /**
     * Bangun HTML "Lampiran: Rubrik Penilaian"
     * Rubrik diambil dari isi asesmen_awal, asesmen_proses, asesmen_akhir.
     */
    private function _build_rubrik_penilaian_html(array $g): string
    {
        $awal   = $this->_extract_criteria_lines($g['asesmen_awal']   ?? '');
        $proses = $this->_extract_criteria_lines($g['asesmen_proses'] ?? '');
        $akhir  = $this->_extract_criteria_lines($g['asesmen_akhir']  ?? '');

        // Kalau tidak ada sama sekali, tidak usah tampilkan lampiran
        if (empty($awal) && empty($proses) && empty($akhir)) {
            return '';
        }

        $html = '
<div class="mt-8">
  <h4 class="font-bold text-lg mb-2">Lampiran: Rubrik Penilaian</h4>
  <p class="text-sm text-gray-700 mb-3">
    Rubrik ini disusun berdasarkan rumusan asesmen pada bagian sebelumnya dan digunakan sebagai acuan penilaian kinerja peserta didik.
  </p>
';

        $sections = [
            'Assessment of Learning (Awal)'   => $awal,
            'Assessment as Learning (Proses)' => $proses,
            'Assessment for Learning (Akhir)' => $akhir,
        ];

        $secNo = 1;
        foreach ($sections as $label => $criteria) {
            if (empty($criteria)) {
                $secNo++;
                continue;
            }

            $labelEsc = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');

            $html .= '
  <h4 class="font-semibold mt-4 mb-2">'.$secNo.'. '.$labelEsc.'</h4>
  <table class="document-table">
    <thead>
      <tr>
        <th style="width:32px;">No</th>
        <th>Kriteria</th>
        <th>Sangat Baik (4)</th>
        <th>Baik (3)</th>
        <th>Cukup (2)</th>
        <th>Perlu Bimbingan (1)</th>
      </tr>
    </thead>
    <tbody>
';
            $no = 1;
            foreach ($criteria as $critRaw) {
                $critEsc = htmlspecialchars($critRaw, ENT_QUOTES, 'UTF-8');

                $html .= '
      <tr>
        <td>'.$no.'</td>
        <td>'.$critEsc.'</td>
        <td>Menunjukkan penguasaan sangat baik terhadap aspek <em>'.$critEsc.'</em>, mandiri dan konsisten.</td>
        <td>Menunjukkan penguasaan baik terhadap aspek <em>'.$critEsc.'</em>, terdapat sedikit kekeliruan namun tidak mengganggu pencapaian tujuan.</td>
        <td>Menunjukkan penguasaan cukup terhadap aspek <em>'.$critEsc.'</em>, masih memerlukan bimbingan pada beberapa bagian.</td>
        <td>Belum menunjukkan penguasaan yang memadai terhadap aspek <em>'.$critEsc.'</em> dan memerlukan bimbingan intensif.</td>
      </tr>
';
                $no++;
            }

            $html .= '
    </tbody>
  </table>
';
            $secNo++;
        }

        $html .= '</div>';

        return $html;
    }

    // ================== HELPER: format teks multi-baris ke HTML ==================

    // ================== HELPER: format teks multi-baris ke HTML ==================

// ================== HELPER: format teks multi-baris ke HTML ==================
private function _fmt_multiline(?string $text): string
{
    $text = trim((string)$text);
    if ($text === '') return '-';

    // Pecah per baris & bersihkan
    $lines = preg_split('/\r\n|\r|\n/', $text);
    $clean = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line !== '') {
            $clean[] = $line;
        }
    }
    if (count($clean) === 0) {
        return '-';
    }

    // ========= DETEKSI: apakah ini "Kerangka Pembelajaran" (Pendahuluan / Kegiatan Inti / Penutup)? =========
    $hasKerangkaSection = false;
    foreach ($clean as $line) {
        // Buang nomor di depan kalau ada (mis. "1. ..." / "2) ...")
        $body = $line;
        if (preg_match('/^\d+[\.\)]\s*(.+)$/u', $line, $m)) {
            $body = trim($m[1]);
        }

        $lower = mb_strtolower($body, 'UTF-8');
        if (strpos($lower, 'pendahuluan') !== false ||
            strpos($lower, 'kegiatan inti') !== false ||
            strpos($lower, 'penutup') !== false) {
            $hasKerangkaSection = true;
            break;
        }
    }

    // ========= MODE KERANGKA: Pendahuluan / Kegiatan Inti / Penutup =========
    if ($hasKerangkaSection) {
        //   - Tiap bagian jadi <li> dari <ol> (nomor otomatis lurus)
        //   - Aktivitas di bawahnya jadi <ul><li>, semua rata kiri–kanan
        $sections = [];
        $current  = -1;

        foreach ($clean as $line) {
            $origLine = $line;
            $body     = $line;

            // Buang nomor di depan kalau ada
            if (preg_match('/^\d+[\.\)]\s*(.+)$/u', $line, $m)) {
                $body = trim($m[1]);
            }

            $lower = mb_strtolower($body, 'UTF-8');
            $isHeading = (strpos($lower, 'pendahuluan') !== false ||
                          strpos($lower, 'kegiatan inti') !== false ||
                          strpos($lower, 'penutup') !== false);

            if ($isHeading) {
                // === Baris ini judul bagian ===
                $title     = $body;
                $firstItem = '';

                // Kalau ada ":" → kiri = judul, kanan = isi pertama
                $posColon = mb_strpos($title, ':');
                if ($posColon !== false) {
                    $firstItem = trim(mb_substr($title, $posColon + 1));
                    $title     = trim(mb_substr($title, 0, $posColon));
                }

                $sections[] = [
                    'title' => $title,
                    'items' => [],
                ];
                $current = count($sections) - 1;

                if ($firstItem !== '') {
                    $sections[$current]['items'][] = $firstItem;
                }
            } else {
                // === Bukan heading → jadikan bullet di bawah heading terakhir ===
                if ($current === -1) {
                    // kalau belum ada heading, skip saja
                    continue;
                }

                // Buang nomor / bullet lama di depan
                $item = preg_replace('/^\s*(?:\d+[\.\)]|[-*•])\s*/u', '', $origLine);
                $item = trim($item);

                if ($item !== '') {
                    $sections[$current]['items'][] = $item;
                }
            }
        }

        if (empty($sections)) {
            return '-';
        }

        // Render ke HTML:
        // <ol class="numbered-list">
        //   <li><strong>Pendahuluan</strong><ul>...</ul></li>
        //   <li><strong>Kegiatan Inti</strong><ul>...</ul></li>
        //   ...
        $html = '<ol class="numbered-list" style="margin:0; padding-left:1.25em; text-align:justify;">';
        foreach ($sections as $sec) {
            $html .= '<li style="margin-bottom:4px;">'
                   . '<strong>'
                   . htmlspecialchars($sec['title'], ENT_QUOTES, 'UTF-8')
                   . '</strong>';

            if (!empty($sec['items'])) {
                $html .= '<ul style="margin:2px 0 2px 1em; padding-left:1em; text-align:justify;">';
                foreach ($sec['items'] as $item) {
                    $html .= '<li style="margin:0 0 2px 0;">'
                           . htmlspecialchars($item, ENT_QUOTES, 'UTF-8')
                           . '</li>';
                }
                $html .= '</ul>';
            }

            $html .= '</li>';
        }
        $html .= '</ol>';

        return $html;
    }

    // ========= MODE NORMAL (bukan kerangka): numbering lurus pakai <ol><li>, rata kiri–kanan =========
    $html = '<ol class="numbered-list" style="margin:0; padding-left:1.25em; text-align:justify;">';

    foreach ($clean as $line) {
        // buang nomor / bullet lama di depan
        $stripped = preg_replace('/^\s*(?:\d+[\.\)]|[-*•])\s*/u', '', $line);
        $stripped = trim($stripped);
        if ($stripped === '') continue;

        $html .= '<li style="margin-bottom:2px;">'
               . htmlspecialchars($stripped, ENT_QUOTES, 'UTF-8')
               . '</li>';
    }

    $html .= '</ol>';

    // Kalau ternyata kosong setelah dibersihkan, kembalikan '-'
    if ($html === '<ol class="numbered-list" style="margin:0; padding-left:1.25em; text-align:justify;"></ol>') {
        return '-';
    }

    return $html;
}

    /**
     * Ekstrak baris-baris kriteria dari teks asesmen (dipakai untuk rubrik).
     * - Pecah per baris
     * - Buang nomor / bullet di depan
     * - Hilangkan baris kosong
     */
    private function _extract_criteria_lines(?string $text): array
    {
        $text = trim((string)$text);
        if ($text === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $text);
        $out   = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            // buang nomor / bullet di depan: "1. ...", "2) ...", "- ...", "• ..."
            $line = preg_replace('/^\s*(?:\d+[\.\)]|[-*•])\s*/u', '', $line);
            $line = trim($line);

            if ($line !== '') {
                $out[] = $line;
            }
        }

        return $out;
    }
    /**
     * Bangun HTML "Lampiran: Rubrik Penilaian"
     * Rubrik diambil dari isi asesmen_awal, asesmen_proses, asesmen_akhir.
     */
        /**
     * Lampiran: Kisi-kisi Soal (berdasarkan field "kisi_kisi" dari JSON Gemini)
     */
    private function _build_kisi_kisi_html(array $g): string
    {
        if (!isset($g['kisi_kisi']) || !is_array($g['kisi_kisi']) || empty($g['kisi_kisi'])) {
            return '';
        }

        $rows = $g['kisi_kisi'];

        $html = '
<div class="mt-8">
  <h4 class="font-bold text-lg mb-2">Lampiran: Kisi-kisi Soal</h4>
  <table class="document-table">
    <thead>
      <tr>
        <th style="width:32px;">No</th>
        <th>Indikator</th>
        <th>Bentuk Soal</th>
        <th>Level Kognitif</th>
        <th>Nomor Soal</th>
        <th>Aspek Dinilai</th>
        <th>Skor Maks.</th>
      </tr>
    </thead>
    <tbody>
';

        $no = 1;
        foreach ($rows as $row) {
            $indikator      = htmlspecialchars((string)($row['indikator']      ?? ''), ENT_QUOTES, 'UTF-8');
            $bentuk         = htmlspecialchars((string)($row['bentuk_soal']    ?? ''), ENT_QUOTES, 'UTF-8');
            $level          = htmlspecialchars((string)($row['level_kognitif'] ?? ''), ENT_QUOTES, 'UTF-8');
            $nomor          = htmlspecialchars((string)($row['nomor_soal']     ?? ''), ENT_QUOTES, 'UTF-8');
            $aspek          = htmlspecialchars((string)($row['aspek_dinilai']  ?? ''), ENT_QUOTES, 'UTF-8');
            $skor_max       = htmlspecialchars((string)($row['skor_maksimal']  ?? ''), ENT_QUOTES, 'UTF-8');

            if ($indikator === '' && $aspek === '' && $nomor === '') {
                continue;
            }

            $html .= '
      <tr>
        <td>'.$no.'</td>
        <td>'.$indikator.'</td>
        <td>'.$bentuk.'</td>
        <td>'.$level.'</td>
        <td>'.$nomor.'</td>
        <td>'.$aspek.'</td>
        <td style="text-align:center;">'.$skor_max.'</td>
      </tr>
';
            $no++;
        }

        $html .= '
    </tbody>
  </table>
</div>';

        return $html;
    }

    /**
     * Lampiran: Bank Soal (Pilihan Ganda & Uraian)
     */
    private function _build_bank_soal_html(array $g): string
    {
        $pilgan = isset($g['soal_pilgan']) && is_array($g['soal_pilgan']) ? $g['soal_pilgan'] : [];
        $uraian = isset($g['soal_uraian']) && is_array($g['soal_uraian']) ? $g['soal_uraian'] : [];

        if (empty($pilgan) && empty($uraian)) {
            return '';
        }

        $html = '
<div class="mt-8">
  <h4 class="font-bold text-lg mb-2">Lampiran: Bank Soal</h4>
';

        // --- Soal Pilihan Ganda ---
        if (!empty($pilgan)) {
            $html .= '
  <h5 class="font-semibold mt-2 mb-1">1. Soal Pilihan Ganda</h5>
  <table class="document-table">
    <thead>
      <tr>
        <th style="width:32px;">No</th>
        <th>Soal</th>
        <th style="width:15%;">A</th>
        <th style="width:15%;">B</th>
        <th style="width:15%;">C</th>
        <th style="width:15%;">D</th>
        <th style="width:8%;">Kunci</th>
      </tr>
    </thead>
    <tbody>
';
            foreach ($pilgan as $row) {
                $no   = (int)($row['nomor'] ?? 0);
                if ($no <= 0) {
                    $no = 0;
                }

                $q    = htmlspecialchars((string)($row['pertanyaan'] ?? ''), ENT_QUOTES, 'UTF-8');
                $a    = htmlspecialchars((string)($row['opsi_a']     ?? ''), ENT_QUOTES, 'UTF-8');
                $b    = htmlspecialchars((string)($row['opsi_b']     ?? ''), ENT_QUOTES, 'UTF-8');
                $c    = htmlspecialchars((string)($row['opsi_c']     ?? ''), ENT_QUOTES, 'UTF-8');
                $d    = htmlspecialchars((string)($row['opsi_d']     ?? ''), ENT_QUOTES, 'UTF-8');
                $key  = htmlspecialchars((string)($row['kunci']      ?? ''), ENT_QUOTES, 'UTF-8');

                if ($q === '') {
                    continue;
                }

                $html .= '
      <tr>
        <td>'.($no ?: '&nbsp;').'</td>
        <td>'.$q.'</td>
        <td>'.$a.'</td>
        <td>'.$b.'</td>
        <td>'.$c.'</td>
        <td>'.$d.'</td>
        <td style="text-align:center;">'.$key.'</td>
      </tr>
';
            }

            $html .= '
    </tbody>
  </table>
';
        }

        // --- Soal Uraian ---
        if (!empty($uraian)) {
            $html .= '
  <h5 class="font-semibold mt-4 mb-1">2. Soal Uraian</h5>
  <table class="document-table">
    <thead>
      <tr>
        <th style="width:32px;">No</th>
        <th>Soal Uraian</th>
        <th>Pedoman Skor</th>
      </tr>
    </thead>
    <tbody>
';

            foreach ($uraian as $row) {
                $no   = (int)($row['nomor'] ?? 0);
                $q    = htmlspecialchars((string)($row['pertanyaan']   ?? ''), ENT_QUOTES, 'UTF-8');
                $rub  = trim((string)($row['pedoman_skor'] ?? ''));
                // pecah pedoman per baris -> <ul>
                $rubLines = [];
                if ($rub !== '') {
                    $tmp = preg_split('/\r\n|\r|\n/', $rub);
                    foreach ($tmp as $rl) {
                        $rl = trim($rl);
                        if ($rl !== '') {
                            $rubLines[] = htmlspecialchars($rl, ENT_QUOTES, 'UTF-8');
                        }
                    }
                }

                if ($q === '') {
                    continue;
                }

                $html .= '
      <tr>
        <td>'.($no ?: '&nbsp;').'</td>
        <td>'.$q.'</td>
        <td>';
                if (!empty($rubLines)) {
                    $html .= '<ul style="margin:0; padding-left:1.2em;">';
                    foreach ($rubLines as $rl) {
                        $html .= '<li>'.$rl.'</li>';
                    }
                    $html .= '</ul>';
                }
                $html .= '</td>
      </tr>
';
            }

            $html .= '
    </tbody>
  </table>
';
        }

        $html .= '
</div>';

        return $html;
    }

    /**
     * Lampiran: Rubrik Penilaian Soal (berdasarkan kisi_kisi / indikator soal)
     */
    private function _build_rubrik_soal_html(array $g): string
    {
        $criteria = [];

        // Utamakan pakai "kisi_kisi"
        if (isset($g['kisi_kisi']) && is_array($g['kisi_kisi'])) {
            foreach ($g['kisi_kisi'] as $row) {
                $aspek = trim((string)($row['aspek_dinilai'] ?? ''));
                $indik = trim((string)($row['indikator']     ?? ''));
                $key   = $aspek !== '' ? $aspek : $indik;
                if ($key !== '') {
                    $criteria[] = $key;
                }
            }
        }

        // Fallback: kalau kisi_kisi kosong, ambil indikator dari soal
        if (empty($criteria)) {
            foreach (['soal_pilgan', 'soal_uraian'] as $field) {
                if (!isset($g[$field]) || !is_array($g[$field])) continue;
                foreach ($g[$field] as $row) {
                    $indik = trim((string)($row['indikator'] ?? ''));
                    if ($indik !== '') {
                        $criteria[] = $indik;
                    }
                }
            }
        }

        // unik
        $criteria = array_values(array_unique($criteria));

        if (empty($criteria)) {
            return '';
        }

        $html = '
<div class="mt-8">
  <h4 class="font-bold text-lg mb-2">Lampiran: Rubrik Penilaian Soal</h4>
  <p class="text-sm text-gray-700 mb-3">
    Rubrik ini digunakan untuk menilai jawaban peserta didik terhadap butir-butir soal berdasarkan indikator yang telah dirumuskan dalam kisi-kisi.
  </p>
  <table class="document-table">
    <thead>
      <tr>
        <th style="width:32px;">No</th>
        <th>Indikator / Aspek yang Dinilai</th>
        <th>Sangat Baik (4)</th>
        <th>Baik (3)</th>
        <th>Cukup (2)</th>
        <th>Perlu Bimbingan (1)</th>
      </tr>
    </thead>
    <tbody>
';

        $no = 1;
        foreach ($criteria as $crit) {
            $critEsc = htmlspecialchars($crit, ENT_QUOTES, 'UTF-8');

            $html .= '
      <tr>
        <td>'.$no.'</td>
        <td>'.$critEsc.'</td>
        <td>Jawaban sangat lengkap, tepat, dan jelas sesuai indikator <em>'.$critEsc.'</em>; menunjukkan pemahaman mendalam dan penggunaan istilah yang benar.</td>
        <td>Jawaban sudah tepat dan cukup lengkap sesuai indikator <em>'.$critEsc.'</em>, hanya terdapat kekurangan kecil yang tidak mengganggu makna utama.</td>
        <td>Jawaban sebagian sudah sesuai indikator <em>'.$critEsc.'</em>, namun masih ada bagian penting yang hilang atau kurang jelas.</td>
        <td>Jawaban tidak sesuai atau jauh dari indikator <em>'.$critEsc.'</em>, menunjukkan pemahaman yang sangat terbatas dan membutuhkan bimbingan intensif.</td>
      </tr>
';
            $no++;
        }

        $html .= '
    </tbody>
  </table>
</div>';

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
  <h3 class=\"font-bold text-center text-lg mb-4\">RENCANA PEMBELAJARAN MENDALAM SMKN 1 WAJO</h3>
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
    // Lampiran-lampiran:
        // 1) Kisi-kisi soal (dibangun dari field "kisi_kisi")
        $html .= $this->_build_kisi_kisi_html($g);

        // 2) Bank soal (pilihan ganda & uraian)
        $html .= $this->_build_bank_soal_html($g);

        // 3) Rubrik penilaian (berbasis asesmen_awal/proses/akhir) – yang sudah ada
        $html .= $this->_build_rubrik_penilaian_html($g);

        // 4) Rubrik penilaian soal (berbasis indikator / kisi-kisi)
        $html .= $this->_build_rubrik_soal_html($g);
         return $html;
}
}
