<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rpp_gemini extends Onhacker_Controller
{
    // === SETTING GEMINI API ===
    // TODO: ganti dengan API key milikmu dari Google AI Studio
    private $gemini_api_key = 'AIzaSyAc59vtjMm7efqYS6-VDRVTKUMsoOduGzA';
    private $gemini_model   = 'gemini-2.5-flash'; // bisa diganti model lain jika perlu

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['url', 'form']);
    }

    // Halaman utama: form + hasil RPP
    public function index()
    {
        $data['rpp_result'] = '';

        if ($this->input->method(TRUE) === 'POST') {
            // Ambil input dari form
            $namaGuru       = trim((string)$this->input->post('nama_guru', TRUE));
            $sekolah        = trim((string)$this->input->post('sekolah', TRUE));
            $kelas          = trim((string)$this->input->post('kelas', TRUE));
            $semester       = trim((string)$this->input->post('semester', TRUE));
            $tahunPelajaran = trim((string)$this->input->post('tahun_pelajaran', TRUE));
            $materi         = trim((string)$this->input->post('materi', TRUE));
            $alokasiWaktu   = trim((string)$this->input->post('alokasi_waktu', TRUE));
            $pertemuan      = trim((string)$this->input->post('pertemuan', TRUE));
            $karakterSiswa  = trim((string)$this->input->post('karakter_siswa', TRUE));

            // Susun prompt RPP
            $prompt = <<<PROMPT
Kamu adalah pengembang perangkat ajar untuk guru SMK di Indonesia.

Tolong buatkan saya RPP 1 lembar Kurikulum Merdeka dengan data berikut:

- Nama guru      : {$namaGuru}
- Sekolah        : {$sekolah}
- Mata pelajaran : Bahasa Inggris
- Kelas/Fase     : {$kelas}
- Semester       : {$semester}
- Tahun pelajaran: {$tahunPelajaran}
- Topik/Materi   : {$materi}
- Alokasi waktu  : {$alokasiWaktu}
- Jumlah pertemuan: {$pertemuan}
- Karakteristik siswa: {$karakterSiswa}

Susun RPP 1 lembar Kurikulum Merdeka dengan struktur:

1. Identitas Mata Pelajaran
2. Capaian Pembelajaran yang relevan (ringkas)
3. Tujuan Pembelajaran (dirumuskan dalam bentuk perilaku yang dapat diamati)
4. Profil Pelajar Pancasila yang diintegrasikan
5. Sarana dan Prasarana
6. Langkah-langkah Pembelajaran:
   - Pendahuluan
   - Kegiatan Inti (sesuai prinsip pembelajaran aktif di Kurikulum Merdeka)
   - Penutup
7. Asesmen:
   - Teknik dan bentuk asesmen
   - Contoh instrumen singkat
   - Kriteria penilaian
8. Pengayaan dan Remedial (singkat)
9. Refleksi Guru dan Peserta Didik (singkat)

Syarat penulisan:
- Gunakan bahasa Indonesia formal, rapi, seperti dokumen resmi sekolah.
- Sesuaikan konteks dengan SMK (contoh, aktivitas bisa terkait dunia kerja atau jurusan).
- Tampilkan dengan heading yang jelas dan poin-poin yang mudah dibaca.
PROMPT;

            // Panggil Gemini
            $result = $this->_call_gemini_api($prompt);
            $data['rpp_result'] = $result ?: 'Tidak ada respon dari Gemini / terjadi kesalahan.';
        }

        $this->load->view('rpp_gemini_form', $data);
    }

    /**
     * Endpoint untuk download RPP sebagai file .doc
     */
    public function download()
    {
        // FALSE di parameter kedua = jangan XSS filter, karena ini teks yang kita kirim sendiri
        $content = $this->input->post('rpp_content', FALSE);
        if ($content === null || $content === '') {
            show_404();
            return;
        }

        $filename = 'RPP_1_Lembar_' . date('Ymd_His') . '.doc';

        // Header supaya browser download sebagai dokumen Word
        header("Content-Type: application/msword; charset=UTF-8");
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header("Cache-Control: no-store, no-cache, must-revalidate");

        echo "<html><head><meta charset=\"UTF-8\"></head><body>";
        // Escape HTML lalu ubah newline jadi <br> agar tetap rapi di Word
        echo nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));
        echo "</body></html>";
        exit;
    }

    /**
     * Panggil Gemini API (REST) pakai cURL.
     */
    private function _call_gemini_api(string $prompt): ?string
    {
        if (empty($this->gemini_api_key)) {
            return 'API key Gemini belum diset di controller.';
        }

        $url = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
            $this->gemini_model,
            $this->gemini_api_key
        );

        $payload = [
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [
                        ['text' => $prompt]
                    ],
                ],
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
            $err = curl_error($ch);
            curl_close($ch);
            return 'cURL error: ' . $err;
        }
        curl_close($ch);

        $data = json_decode($response, true);

        // Ambil teks utama dari respon
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return $data['candidates'][0]['content']['parts'][0]['text'];
        }

        if (isset($data['error']['message'])) {
            return 'ERROR dari Gemini: ' . $data['error']['message'];
        }

        return 'Format respon Gemini tidak dikenali: ' . $response;
    }
}
