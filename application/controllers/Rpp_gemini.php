<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rpp_gemini extends Onhacker_Controller
{
    // === SETTING GEMINI API ===
    // Ganti dengan API key milikmu dari Google AI Studio
    private $gemini_api_key = 'AIzaSyAc59vtjMm7efqYS6-VDRVTKUMsoOduGzA';
    private $gemini_model   = 'gemini-2.5-flash'; // bisa diganti model lain jika perlu

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['url', 'form']);
    }

    // Halaman utama: form + hasil dokumen
    public function index()
    {
        $data['rpp_result'] = '';

        if ($this->input->method(TRUE) === 'POST') {
            // Ambil input dari form
            $namaGuru        = trim((string)$this->input->post('nama_guru', TRUE));
            $sekolah         = trim((string)$this->input->post('sekolah', TRUE));
            $mataPelajaran   = trim((string)$this->input->post('mata_pelajaran', TRUE));
            $materi          = trim((string)$this->input->post('materi', TRUE));
            $kelas           = trim((string)$this->input->post('kelas', TRUE));
            $semester        = trim((string)$this->input->post('semester', TRUE));
            $fase            = trim((string)$this->input->post('fase', TRUE));
            $tahunPelajaran  = trim((string)$this->input->post('tahun_pelajaran', TRUE));
            $pertemuan       = trim((string)$this->input->post('pertemuan', TRUE));
            $totalWaktu      = trim((string)$this->input->post('total_waktu', TRUE));

            if ($mataPelajaran === '') {
                $mataPelajaran = 'Bahasa Inggris'; // default kalau kosong
            }

            // Susun prompt: RPP / RPM dalam bentuk HTML rapi
            $prompt = <<<PROMPT
Kamu adalah pengembang perangkat ajar untuk guru SMK di Indonesia.

Buatkan dokumen Rencana Pembelajaran (bisa berupa RPP 1 Lembar / Rencana Pembelajaran Mendalam) berdasarkan data berikut:

- Satuan Pendidikan : {$sekolah}
- Nama Guru         : {$namaGuru}
- Mata Pelajaran    : {$mataPelajaran}
- Kelas             : {$kelas}
- Fase              : {$fase}
- Semester          : {$semester}
- Tahun Pelajaran   : {$tahunPelajaran}
- Materi Pokok      : {$materi}
- Jumlah Pertemuan  : {$pertemuan}
- Deskripsi Total Waktu : {$totalWaktu}

Ketentuan format:

1. Tampilkan hasil dalam bentuk HTML yang rapi untuk dicetak di Microsoft Word.
2. Jangan sertakan tag <html>, <head>, atau <body>. Hanya isi body saja.
3. Gunakan susunan dan heading seperti berikut (boleh disesuaikan seperlunya):
   - <h2 style="text-align:center; text-transform:uppercase;">RENCANA PEMBELAJARAN</h2>
   - <h3>IDENTITAS MATA PELAJARAN</h3>
     Gunakan <table> untuk memuat:
       • Satuan Pendidikan
       • Mata Pelajaran
       • Kelas
       • Fase
       • Semester
       • Tahun Pelajaran
       • Materi Pokok
       • Jumlah Pertemuan
       • Total Waktu
       • Nama Guru
   - <h3>A. Capaian / Tujuan Pembelajaran</h3>
   - <h3>B. Profil Pelajar Pancasila</h3>
   - <h3>C. Karakteristik Peserta Didik</h3>
   - <h3>D. Sarana dan Prasarana</h3>
   - <h3>E. Langkah-langkah Pembelajaran</h3>
       • Bagi menjadi: Pendahuluan, Kegiatan Inti, Penutup.
       • Gunakan <ol> dan <ul> untuk langkah-langkah yang runtut.
   - <h3>F. Asesmen</h3>
       • Jelaskan teknik, instrumen singkat, dan kriteria penilaian.
   - <h3>G. Pengayaan dan Remedial</h3>
   - <h3>H. Refleksi Guru dan Peserta Didik</h3>

4. Gunakan elemen HTML berikut untuk kerapian:
   - <h2>, <h3> untuk judul dan subjudul.
   - <p> untuk paragraf.
   - <ul> dan <ol> untuk daftar.
   - <table>, <tr>, <td> untuk identitas di bagian atas.
5. Jangan gunakan tanda strip "-" di awal baris untuk daftar; gunakan tag <ul>/<ol> saja.
6. Gunakan bahasa Indonesia formal, rapi, dan sesuai konteks SMK.

PROMPT;

            // Panggil Gemini
            $result = $this->_call_gemini_api($prompt);
            $data['rpp_result'] = $result ?: 'Tidak ada respon dari Gemini / terjadi kesalahan.';
        }

        $this->load->view('rpp_gemini_form', $data);
    }

    /**
     * Endpoint untuk download dokumen sebagai file .doc (berbasis HTML)
     */
    public function download()
    {
        $contentEscaped = $this->input->post('rpp_content', FALSE);
        if ($contentEscaped === null || $contentEscaped === '') {
            show_404();
            return;
        }

        $content = htmlspecialchars_decode($contentEscaped, ENT_NOQUOTES);
        $filename = 'Rencana_Pembelajaran_' . date('Ymd_His') . '.doc';

        header("Content-Type: application/msword; charset=UTF-8");
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header("Cache-Control: no-store, no-cache, must-revalidate");

        echo "<html><head><meta charset=\"UTF-8\"></head><body>";
        echo $content;
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

        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return $data['candidates'][0]['content']['parts'][0]['text'];
        }

        if (isset($data['error']['message'])) {
            return 'ERROR dari Gemini: ' . $data['error']['message'];
        }

        return 'Format respon Gemini tidak dikenali: ' . $response;
    }
}
