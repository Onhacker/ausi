<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
// Ambil nilai POST (kalau belum submit, hasilnya null)
$old_nama_guru          = $this->input->post('nama_guru', true);
$old_mapel              = $this->input->post('mata_pelajaran', true);
$old_materi             = $this->input->post('materi', true);
$old_kelas              = $this->input->post('kelas', true);
$old_semester           = $this->input->post('semester', true);
$old_fase               = $this->input->post('fase', true);
$old_tahun_pelajaran    = $this->input->post('tahun_pelajaran', true);
$old_pertemuan          = $this->input->post('pertemuan', true);
$old_total_waktu        = $this->input->post('total_waktu', true);
$old_perintah_tambahan  = $this->input->post('perintah_tambahan', true);

// Default jika pertama kali dibuka (belum POST)
if ($old_mapel === null || $old_mapel === '') {
    $old_mapel = 'Bahasa Inggris';
}
if ($old_tahun_pelajaran === null || $old_tahun_pelajaran === '') {
    $old_tahun_pelajaran = '2025/2026';
}
if ($old_pertemuan === null || $old_pertemuan === '') {
    $old_pertemuan = 3;
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex, nofollow">
    <title>Generator SMKN 1 WAJO</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- SweetAlert2 CSS -->
    <link href="<?php echo base_url('assets/admin') ?>/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css" />

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            color: #374151;
        }
        .panel {
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        /* === FORM CONTROL SERAGAM & LEBIH LEGA === */
        input[type="text"],
        input[type="number"],
        select,
        textarea {
            border: 1px solid #d1d5db;
            padding: 8px !important;       /* placeholder tidak mepet garis */
            border-radius: 0.375rem;
            width: 100%;
            font-size: 0.875rem;
            background-color: #f9fafb;
            color: #1f2937;
            line-height: 1.5rem;         /* tinggi baris */
            min-height: 2.9rem;          /* tinggi minimum */
            box-sizing: border-box;
        }

        /* textarea sedikit lebih tinggi & turun dari atas */
        textarea {
            padding-top: 0.9rem;
            min-height: 4rem;
        }

        input[type="text"]::placeholder,
        input[type="number"]::placeholder,
        textarea::placeholder {
            color: #9ca3af;
        }

        input[readonly]{
            background-color:#e5e7eb;
            cursor:not-allowed;
        }

        .button-primary {
            background-color: #2563eb;
            color: #ffffff;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: background-color 0.2s, opacity .15s;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:.5rem;
        }
        .button-primary:hover {
            background-color: #1d4ed8;
        }
        .button-primary[disabled]{
            opacity:.8;
            cursor:wait;
        }

        .text-label {
            font-size: 0.875rem;
            color: #4b5563;
        }
        .placeholder-text {
            color: #9ca3af;
            font-style: italic;
        }

        /* Doc output */
        .document-container h4, .document-container h5 {
            font-weight: 600;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }
        .document-container p, .document-container ul {
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 0.5rem;
        }
        .document-container .numbered-list > div {
            margin-bottom: 0.25rem;
            padding-left: 0;
        }
        .document-container .numbered-list {
            padding-left: 0;
        }

        .document-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        .document-table th, .document-table td {
            border: 1px solid #e5e7eb;
            padding: 0.5rem;
            vertical-align: top;
            text-align: left;
        }
        .document-table th {
            background-color: #f9fafb;
            font-weight: 600;
        }
        .document-table th.meeting-header {
            background-color: #e0f2fe;
            font-size: 1rem;
            color: #0c4a6e;
            text-align: center;
        }

        .button-download {
            background-color: #10b981;
            color: #ffffff;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: background-color 0.2s;
            margin-top: 1rem;
            width:100%;
            text-align:center;
        }
        .button-download:hover {
            background-color: #059669;
        }

        .btn-spinner{
            width:16px;
            height:16px;
        }
    </style>
<style>
  /* Kolom "Pedoman Skor" (supaya tidak melebar-lebar seperti di screenshot) */
  .document-table td.rubric-score-cell{
    text-align: left;       /* override justify */
  }

  /* Numbering pedoman skor uraian */
  .document-container ol.rubric-score-list{
    list-style-type: decimal;   /* pakai angka 1,2,3,... */
    margin: 0 0 .25rem 1.5rem;
    padding-left: 1.5rem;
  }

  .document-container ol.rubric-score-list > li{
    margin-bottom: 2px;
  }
</style>

    <style>
      .rpm-title-wrap{
        display:flex;
        align-items:center;
        justify-content:center;
        gap:.5rem;
      }
      .rpm-title-emoji{
        font-size:1.6rem;
      }
    </style>

    <style>
      /* Biar isi tabel rapi */
      .document-table td {
        vertical-align: top;
        font-size: 0.9rem;
        text-align: justify;
      }

      /* === PENTING: munculkan angka di preview === */
      .document-container ol.numbered-list {
        list-style-type: decimal !important;  /* tampilkan 1,2,3 */
        margin: 0 0 .25rem 1.5rem;
        padding-left: 1.5rem;
      }

      .document-container ol.numbered-list > li {
        margin-bottom: 2px;
        text-align: justify;
      }

      /* Bullet untuk aktivitas di bawah Pendahuluan / Kegiatan Inti / Penutup */
      .document-container ol.numbered-list ul {
        list-style-type: disc !important;
        margin: 2px 0 4px 1.5rem;
        padding-left: 0;
        text-align: justify;
      }

      .document-container ol.numbered-list ul > li {
        margin-bottom: 2px;
      }

      .rpm-loader-wrap{
        display:flex;
        align-items:center;
        gap:.5rem;
        font-size:0.9rem;
        margin-top:.75rem;
      }

      .rpm-loader-icon{
        width:16px;
        height:16px;
        border-radius:999px;
        border:2px solid rgba(148,163,184,.5); /* abu-abu soft */
        border-top-color:#2563eb;              /* biru primary */
        animation:rpm-spin 0.8s linear infinite;
      }

      @keyframes rpm-spin{
        to { transform: rotate(360deg); }
      }
    </style>

</head>
<body class="p-8">

<div class="max-w-4xl mx-auto flex flex-col gap-8 items-stretch">

    <!-- PANEL FORM -->
    <div class="panel p-6">
        <h2 class="text-2xl font-bold mb-1">Rencana Pembelajaran Mendalam SMKN 1 WAJO</h2>
        <p class="text-sm text-gray-600 mb-6">
            Isi data di bawah ini untuk membuat Rencana Pembelajaran Mendalam (RPM) secara otomatis.
        </p>
        
        <p style="text-align: right;"><strong>RPM Generator By Veeya</strong></p>
        <br>
        <form method="post" action="<?= current_url(); ?>" id="rpmForm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="nama_sekolah" class="block text-label">Nama Sekolah</label>
                    <input type="text" id="nama_sekolah" name="sekolah" value="SMKN 1 WAJO" readonly>
                </div>
                <div>
                    <label for="nama_guru" class="block text-label">Nama Guru</label>
                    <input
                        type="text"
                        id="nama_guru"
                        name="nama_guru"
                        placeholder="Contoh: Nurhikmah, S.Pd"
                        value="<?= htmlspecialchars((string)($old_nama_guru ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                        required>
                </div>

                <div>
                    <label for="mata_pelajaran" class="block text-label">Mata Pelajaran</label>
                    <select id="mata_pelajaran" name="mata_pelajaran" required>
                        <option value="">Pilih mata pelajaran</option>

                        <!-- UMUM / MUATAN NASIONAL -->
                        <optgroup label="Muatan Nasional (Umum)">
                            <option value="Pendidikan Agama dan Budi Pekerti" <?= $old_mapel === 'Pendidikan Agama dan Budi Pekerti' ? 'selected' : ''; ?>>Pendidikan Agama dan Budi Pekerti</option>
                            <option value="Pendidikan Pancasila dan Kewarganegaraan" <?= $old_mapel === 'Pendidikan Pancasila dan Kewarganegaraan' ? 'selected' : ''; ?>>Pendidikan Pancasila dan Kewarganegaraan (PPKN)</option>
                            <option value="Bahasa Indonesia" <?= $old_mapel === 'Bahasa Indonesia' ? 'selected' : ''; ?>>Bahasa Indonesia</option>
                            <option value="Matematika" <?= $old_mapel === 'Matematika' ? 'selected' : ''; ?>>Matematika</option>
                            <option value="Sejarah Indonesia" <?= $old_mapel === 'Sejarah Indonesia' ? 'selected' : ''; ?>>Sejarah Indonesia</option>
                            <option value="Bahasa Inggris" <?= $old_mapel === 'Bahasa Inggris' ? 'selected' : ''; ?>>Bahasa Inggris</option>
                            <option value="Seni Budaya" <?= $old_mapel === 'Seni Budaya' ? 'selected' : ''; ?>>Seni Budaya</option>
                            <option value="Pendidikan Jasmani, Olahraga, dan Kesehatan" <?= $old_mapel === 'Pendidikan Jasmani, Olahraga, dan Kesehatan' ? 'selected' : ''; ?>>Pendidikan Jasmani, Olahraga, dan Kesehatan (PJOK)</option>
                            <option value="Informatika" <?= $old_mapel === 'Informatika' ? 'selected' : ''; ?>>Informatika</option>
                            <option value="Bimbingan dan Konseling" <?= $old_mapel === 'Bimbingan dan Konseling' ? 'selected' : ''; ?>>Bimbingan dan Konseling</option>
                            <option value="Projek Penguatan Profil Pelajar Pancasila" <?= $old_mapel === 'Projek Penguatan Profil Pelajar Pancasila' ? 'selected' : ''; ?>>Projek Penguatan Profil Pelajar Pancasila (P5)</option>
                        </optgroup>

                        <!-- MUATAN KEWILAYAHAN -->
                        <optgroup label="Muatan Kewilayahan">
                            <option value="Bahasa Daerah" <?= $old_mapel === 'Bahasa Daerah' ? 'selected' : ''; ?>>Bahasa Daerah</option>
                            <option value="Mulok Kewilayahan" <?= $old_mapel === 'Mulok Kewilayahan' ? 'selected' : ''; ?>>Mulok Kewilayahan</option>
                        </optgroup>

                        <!-- DASAR KEJURUAN / C1 -->
                        <optgroup label="C1. Dasar-dasar Kejuruan (Lintas Program)">
                            <option value="Dasar-dasar Kejuruan" <?= $old_mapel === 'Dasar-dasar Kejuruan' ? 'selected' : ''; ?>>Dasar-dasar Kejuruan</option>
                            <option value="Simulasi dan Komunikasi Digital" <?= $old_mapel === 'Simulasi dan Komunikasi Digital' ? 'selected' : ''; ?>>Simulasi dan Komunikasi Digital</option>
                            <option value="Projek Kreatif dan Kewirausahaan" <?= $old_mapel === 'Projek Kreatif dan Kewirausahaan' ? 'selected' : ''; ?>>Projek Kreatif dan Kewirausahaan</option>
                            <option value="Otomatisasi Tata Kelola Perkantoran Dasar" <?= $old_mapel === 'Otomatisasi Tata Kelola Perkantoran Dasar' ? 'selected' : ''; ?>>Otomatisasi Tata Kelola Perkantoran Dasar</option>
                            <option value="Dasar-dasar Akuntansi dan Keuangan" <?= $old_mapel === 'Dasar-dasar Akuntansi dan Keuangan' ? 'selected' : ''; ?>>Dasar-dasar Akuntansi dan Keuangan</option>
                            <option value="Dasar-dasar Bisnis dan Pemasaran" <?= $old_mapel === 'Dasar-dasar Bisnis dan Pemasaran' ? 'selected' : ''; ?>>Dasar-dasar Bisnis dan Pemasaran</option>
                            <option value="Dasar-dasar Teknik Mesin" <?= $old_mapel === 'Dasar-dasar Teknik Mesin' ? 'selected' : ''; ?>>Dasar-dasar Teknik Mesin</option>
                            <option value="Dasar-dasar Teknik Otomotif" <?= $old_mapel === 'Dasar-dasar Teknik Otomotif' ? 'selected' : ''; ?>>Dasar-dasar Teknik Otomotif</option>
                            <option value="Dasar-dasar Teknik Komputer dan Jaringan" <?= $old_mapel === 'Dasar-dasar Teknik Komputer dan Jaringan' ? 'selected' : ''; ?>>Dasar-dasar Teknik Komputer dan Jaringan</option>
                            <option value="Dasar-dasar Rekayasa Perangkat Lunak" <?= $old_mapel === 'Dasar-dasar Rekayasa Perangkat Lunak' ? 'selected' : ''; ?>>Dasar-dasar Rekayasa Perangkat Lunak</option>
                            <option value="Dasar-dasar Desain Komunikasi Visual" <?= $old_mapel === 'Dasar-dasar Desain Komunikasi Visual' ? 'selected' : ''; ?>>Dasar-dasar Desain Komunikasi Visual</option>
                        </optgroup>

                        <!-- CONTOH C2/C3 BEBERAPA KOMPETENSI KEAHLIAN UMUM -->
                        <optgroup label="C2/C3. Teknik Komputer dan Informatika">
                            <option value="Jaringan Komputer dan Dasar-dasar WAN" <?= $old_mapel === 'Jaringan Komputer dan Dasar-dasar WAN' ? 'selected' : ''; ?>>Jaringan Komputer dan Dasar-dasar WAN</option>
                            <option value="Administrasi Sistem Jaringan" <?= $old_mapel === 'Administrasi Sistem Jaringan' ? 'selected' : ''; ?>>Administrasi Sistem Jaringan</option>
                            <option value="Pemrograman Dasar" <?= $old_mapel === 'Pemrograman Dasar' ? 'selected' : ''; ?>>Pemrograman Dasar</option>
                            <option value="Pemrograman Berorientasi Objek" <?= $old_mapel === 'Pemrograman Berorientasi Objek' ? 'selected' : ''; ?>>Pemrograman Berorientasi Objek</option>
                            <option value="Pemrograman Web dan Perangkat Bergerak" <?= $old_mapel === 'Pemrograman Web dan Perangkat Bergerak' ? 'selected' : ''; ?>>Pemrograman Web dan Perangkat Bergerak</option>
                            <option value="Basis Data" <?= $old_mapel === 'Basis Data' ? 'selected' : ''; ?>>Basis Data</option>
                            <option value="Desain Grafis Percetakan" <?= $old_mapel === 'Desain Grafis Percetakan' ? 'selected' : ''; ?>>Desain Grafis Percetakan</option>
                        </optgroup>

                        <optgroup label="C2/C3. Bisnis dan Manajemen">
                            <option value="Akuntansi Dasar" <?= $old_mapel === 'Akuntansi Dasar' ? 'selected' : ''; ?>>Akuntansi Dasar</option>
                            <option value="Praktikum Akuntansi Perusahaan Jasa dan Dagang" <?= $old_mapel === 'Praktikum Akuntansi Perusahaan Jasa dan Dagang' ? 'selected' : ''; ?>>Praktikum Akuntansi Perusahaan Jasa dan Dagang</option>
                            <option value="Perbankan Dasar" <?= $old_mapel === 'Perbankan Dasar' ? 'selected' : ''; ?>>Perbankan Dasar</option>
                            <option value="Bisnis Online dan Pemasaran Digital" <?= $old_mapel === 'Bisnis Online dan Pemasaran Digital' ? 'selected' : ''; ?>>Bisnis Online dan Pemasaran Digital</option>
                            <option value="Administrasi Umum" <?= $old_mapel === 'Administrasi Umum' ? 'selected' : ''; ?>>Administrasi Umum</option>
                            <option value="Otomatisasi Tata Kelola Humas dan Keprotokolan" <?= $old_mapel === 'Otomatisasi Tata Kelola Humas dan Keprotokolan' ? 'selected' : ''; ?>>Otomatisasi Tata Kelola Humas dan Keprotokolan</option>
                        </optgroup>

                        <optgroup label="C2/C3. Teknik Otomotif dan Mesin">
                            <option value="Teknik Pemeliharaan Mesin Kendaraan Ringan" <?= $old_mapel === 'Teknik Pemeliharaan Mesin Kendaraan Ringan' ? 'selected' : ''; ?>>Teknik Pemeliharaan Mesin Kendaraan Ringan</option>
                            <option value="Teknik Chasis dan Pemindah Tenaga" <?= $old_mapel === 'Teknik Chasis dan Pemindah Tenaga' ? 'selected' : ''; ?>>Teknik Chasis dan Pemindah Tenaga</option>
                            <option value="Teknik Kelistrikan Otomotif" <?= $old_mapel === 'Teknik Kelistrikan Otomotif' ? 'selected' : ''; ?>>Teknik Kelistrikan Otomotif</option>
                            <option value="Teknik Pemesinan Bubut" <?= $old_mapel === 'Teknik Pemesinan Bubut' ? 'selected' : ''; ?>>Teknik Pemesinan Bubut</option>
                            <option value="Teknik Pengelasan" <?= $old_mapel === 'Teknik Pengelasan' ? 'selected' : ''; ?>>Teknik Pengelasan</option>
                        </optgroup>

                        <optgroup label="Lain-lain (Sesuaikan SMK)">
                            <option value="Kepariwisataan dan Perhotelan" <?= $old_mapel === 'Kepariwisataan dan Perhotelan' ? 'selected' : ''; ?>>Kepariwisataan dan Perhotelan</option>
                            <option value="Tata Boga" <?= $old_mapel === 'Tata Boga' ? 'selected' : ''; ?>>Tata Boga</option>
                            <option value="Kecantikan Kulit dan Rambut" <?= $old_mapel === 'Kecantikan Kulit dan Rambut' ? 'selected' : ''; ?>>Kecantikan Kulit dan Rambut</option>
                            <option value="Keperawatan" <?= $old_mapel === 'Keperawatan' ? 'selected' : ''; ?>>Keperawatan</option>
                            <option value="Farmasi Klinis dan Komunitas" <?= $old_mapel === 'Farmasi Klinis dan Komunitas' ? 'selected' : ''; ?>>Farmasi Klinis dan Komunitas</option>
                            <option value="Agribisnis Tanaman Pangan dan Hortikultura" <?= $old_mapel === 'Agribisnis Tanaman Pangan dan Hortikultura' ? 'selected' : ''; ?>>Agribisnis Tanaman Pangan dan Hortikultura</option>
                            <option value="Agribisnis Ternak" <?= $old_mapel === 'Agribisnis Ternak' ? 'selected' : ''; ?>>Agribisnis Ternak</option>
                        </optgroup>

                    </select>
                </div>

                <div>
                    <label for="materi" class="block text-label">Materi Pokok</label>
                    <input
                        type="text"
                        id="materi"
                        name="materi"
                        placeholder="Contoh: Asking and Giving Opinion"
                        value="<?= htmlspecialchars((string)($old_materi ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                        required>
                </div>

                <div>
                    <label for="kelas" class="block text-label">Kelas</label>
                    <input
                        type="text"
                        id="kelas"
                        name="kelas"
                        placeholder="Contoh: X"
                        value="<?= htmlspecialchars((string)($old_kelas ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                        required>
                </div>
                <div>
                    <label for="semester" class="block text-label">Semester</label>
                    <select id="semester" name="semester" required>
                        <option value="">Pilih semester</option>
                        <option value="Ganjil" <?= $old_semester === 'Ganjil' ? 'selected' : ''; ?>>Ganjil</option>
                        <option value="Genap"  <?= $old_semester === 'Genap'  ? 'selected' : ''; ?>>Genap</option>
                    </select>
                </div>

                <div>
                    <label for="fase" class="block text-label">Fase</label>
                    <input
                        type="text"
                        id="fase"
                        name="fase"
                        placeholder="Contoh: E"
                        value="<?= htmlspecialchars((string)($old_fase ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div>
                    <label for="tahun_pelajaran" class="block text-label">Tahun Ajaran</label>
                    <select id="tahun_pelajaran" name="tahun_pelajaran" required>
                        <option value="">Pilih tahun ajaran</option>
                        <option value="2023/2024" <?= $old_tahun_pelajaran === '2023/2024' ? 'selected' : ''; ?>>2023/2024</option>
                        <option value="2024/2025" <?= $old_tahun_pelajaran === '2024/2025' ? 'selected' : ''; ?>>2024/2025</option>
                        <option value="2025/2026" <?= $old_tahun_pelajaran === '2025/2026' ? 'selected' : ''; ?>>2025/2026</option>
                        <option value="2026/2027" <?= $old_tahun_pelajaran === '2026/2027' ? 'selected' : ''; ?>>2026/2027</option>
                        <option value="2027/2028" <?= $old_tahun_pelajaran === '2027/2028' ? 'selected' : ''; ?>>2027/2028</option>
                    </select>
                </div>

                <div>
                    <label for="pertemuan" class="block text-label">Jumlah Pertemuan Dibuat</label>
                    <input
                        type="number"
                        id="pertemuan"
                        name="pertemuan"
                        min="1"
                        value="<?= htmlspecialchars((string)$old_pertemuan, ENT_QUOTES, 'UTF-8'); ?>"
                        required>
                </div>
                <div>
                    <label for="total_waktu" class="block text-label">Deskripsi Total Waktu (Opsional)</label>
                    <input
                        type="text"
                        id="total_waktu"
                        name="total_waktu"
                        placeholder="Contoh: 9 × 45 Menit"
                        value="<?= htmlspecialchars((string)($old_total_waktu ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="md:col-span-2">
                    <label for="perintah_tambahan" class="block text-label">
                        Perintah Tambahan (Opsional)
                    </label>
                    <textarea
                        id="perintah_tambahan"
                        name="perintah_tambahan"
                        rows="3"
                        placeholder="Beri Perintah. Contoh : sesuai dengan jurusan teknik komputer dan jaringan Fase F. Gunakan perintah tambahan untuk merubah hasil. Contoh: Hapus karakter * di awal setiap baris, gunakan kalimat efektif, dll."><?= htmlspecialchars((string)($old_perintah_tambahan ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        Contoh: <em>&quot;Hapus semua karakter * di awal baris&quot;</em>,
                        <em>&quot;Gunakan kalimat singkat dan padat&quot;</em>, dsb.
                    </p>
                </div>

            </div>

            <button type="submit" id="btn-generate" class="w-full mt-6 button-primary">
                <svg class="btn-spinner hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="btn-label">Generate RPM</span>
            </button>
        </form>
    </div>

    <!-- PANEL HASIL DOKUMEN -->
    <div class="panel p-6 flex flex-col h-full">
        <h2 class="text-2xl font-bold mb-4">Hasil Dokumen</h2>
        <div id="documentOutput"
             class="flex-grow bg-gray-50 border border-dashed border-gray-300 rounded-md p-4 text-sm text-gray-700 overflow-y-auto min-h-[300px]">
            <?php if (!empty($error_msg)): ?>
                <span class="text-red-500 text-sm">
                    <?= htmlspecialchars($error_msg, ENT_QUOTES, 'UTF-8'); ?>
                </span>
            <?php elseif (!empty($rpm_html)): ?>
                <?= $rpm_html; ?>
            <?php else: ?>
                <span class="placeholder-text">Hasil RPM Anda akan ditampilkan di sini setelah form diisi dan dikirim.</span>
            <?php endif; ?>
        </div>

        <?php if (!empty($rpm_html) && empty($error_msg)): ?>
            <form method="post" action="<?= site_url('rpm/download'); ?>" class="mt-4">
                <textarea name="rpm_content" style="display:none;"><?= htmlspecialchars($rpm_html, ENT_NOQUOTES, 'UTF-8'); ?></textarea>
                <button type="submit" class="button-download">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-2" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 9.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.293 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                    Unduh Dokumen (Word)
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- SweetAlert2 JS -->
<script src="<?php echo base_url('assets/admin') ?>/js/sw.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('rpmForm');
    const btn  = document.getElementById('btn-generate');
    if (!form || !btn) return;

    // Matikan native HTML5 validation biar kita pakai Swal sendiri
    form.setAttribute('novalidate', 'novalidate');

    const spinner = btn.querySelector('.btn-spinner');
    const label   = btn.querySelector('.btn-label');

    const STORAGE_KEY_FORM = 'rpm_form_data';
    const STORAGE_KEY_PIN  = 'rpm_pin_ok';
    const RPM_PIN          = '2025'; // UBAH PIN DI SINI

    const fieldMessages = {
        nama_guru: 'Nama guru masih kosong nih. Isi dulu ya, biar aku tahu siapa yang bikin RPM kece ini 😄',
        mata_pelajaran: 'Mata pelajaran belum dipilih. Pilih dulu dong, kamu ngajar mapel apa? 😉',
        materi: 'Materi pokok belum diisi. Tulis dulu materi yang mau diajarin biar aku bisa nyusun RPM-nya ✍️',
        kelas: 'Kelas belum diisi. Contoh: X, XI TKJ 1, atau XII AKL 2. Isi dulu yaa 🙌',
        semester: 'Semester belum dipilih. Ganjil atau Genap nih? Pilih salah satu dulu ya 😁',
        tahun_pelajaran: 'Tahun ajaran belum dipilih. Biar administrasinya rapi, pilih dulu tahun ajarannya ✨',
        pertemuan: 'Jumlah pertemuan masih kosong atau nol. Isi berapa kali pertemuan yang mau kamu buat ya 😊'
    };

    function getValue(id) {
        var el = document.getElementById(id);
        if (!el) return '';
        return (el.value || '').toString().trim();
    }

    function showRequiredAlert(fieldId) {
        var msg = fieldMessages[fieldId] || 'Masih ada data yang kosong nih. Lengkapi dulu yaa 😉';
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Eits, bentar dulu...',
                text: msg,
                confirmButtonText: 'Siap, aku isi dulu'
            }).then(function () {
                var el = document.getElementById(fieldId);
                if (el) el.focus();
            });
        } else {
            alert(msg);
            var el = document.getElementById(fieldId);
            if (el) el.focus();
        }
    }

    // === LocalStorage: simpan & restore isian form ===
    function restoreFormFromStorage() {
        if (typeof localStorage === 'undefined') return;
        try {
            var json = localStorage.getItem(STORAGE_KEY_FORM);
            if (!json) return;
            var data = JSON.parse(json);
            if (!data || typeof data !== 'object') return;

            var fields = [
                'nama_guru',
                'mata_pelajaran',
                'materi',
                'kelas',
                'semester',
                'fase',
                'tahun_pelajaran',
                'pertemuan',
                'total_waktu',
                'perintah_tambahan'
            ];

            fields.forEach(function (id) {
                var el = document.getElementById(id);
                if (!el) return;

                var current = (el.value || '').toString().trim();
                if (current === '' && data[id] !== undefined && data[id] !== null) {
                    el.value = data[id];
                }
            });
        } catch (e) {
            // ignore
        }
    }

    function saveFormToStorage() {
        if (typeof localStorage === 'undefined') return;
        try {
            var data = {};
            var fields = [
                'nama_guru',
                'mata_pelajaran',
                'materi',
                'kelas',
                'semester',
                'fase',
                'tahun_pelajaran',
                'pertemuan',
                'total_waktu',
                'perintah_tambahan'
            ];
            fields.forEach(function (id) {
                var el = document.getElementById(id);
                if (!el) return;
                data[id] = (el.value || '').toString();
            });
            localStorage.setItem(STORAGE_KEY_FORM, JSON.stringify(data));
        } catch (e) {
            // ignore
        }
    }

    function setFormEnabled(enabled) {
        var elems = form.querySelectorAll('input, select, textarea, button');
        elems.forEach(function (el) {
            el.disabled = !enabled;
        });
    }

    function showPinGate() {
        if (typeof Swal === 'undefined') return;

        setFormEnabled(false);

        var waText = encodeURIComponent('Assalamualaikum, Ibu Nurhikmah, saya butuh PIN utk Akses RPM Generator, Saya dari ..........');
        var waLink = 'https://wa.me/6285255541755?text=' + waText;

        Swal.fire({
            icon: 'info',
            title: 'PIN Akses RPM',
            html:
                '<p style="font-size:0.9rem;margin-bottom:0.75rem;">' +
                    'Halaman ini dikunci dengan PIN. Kalau belum punya, silakan minta dulu ke Bu Nurhikmah lewat WhatsApp.' +
                '</p>' +
                '<p style="margin-bottom:0.75rem;">' +
                    '<a href="'+ waLink +'" target="_blank" style="color:#2563eb;text-decoration:underline;font-weight:500;">' +
                        'Klik di sini untuk chat Bu Nurhikmah via WA' +
                    '</a>' +
                '</p>' +
                '<p style="font-size:0.85rem;color:#6b7280;">' +
                    'Setelah dapat PIN, masukkan di bawah ini ya 👇' +
                '</p>',
            input: 'password',
            inputPlaceholder: 'Masukkan PIN akses di sini',
            inputAttributes: {
                autocapitalize: 'off',
                autocomplete: 'off'
            },
            confirmButtonText: 'Masuk',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: false,
            preConfirm: function (value) {
                if (!value) {
                    Swal.showValidationMessage('PIN belum diisi, cobain diisi dulu ya 😊');
                    return false;
                }
                if (value !== RPM_PIN) {
                    Swal.showValidationMessage('PIN-nya masih salah nih. Coba dicek lagi, atau chat Bu Nurhikmah kalau lupa 🔐');
                    return false;
                }
                return true;
            }
        }).then(function (result) {
            if (result.isConfirmed) {
                try {
                    if (typeof localStorage !== 'undefined') {
                        localStorage.setItem(STORAGE_KEY_PIN, '1');
                    }
                } catch (e) {}
                pinOK = true;
                setFormEnabled(true);
                Swal.fire({
                    icon: 'success',
                    title: 'Akses dibuka 🎉',
                    text: 'Silakan isi form RPM, semoga bermanfaat 😊',
                    timer: 1600,
                    showConfirmButton: false
                });
            }
        });
    }

    // Cek apakah PIN sudah pernah diisi di device ini
    let pinOK = false;
    try {
        if (typeof localStorage !== 'undefined') {
            pinOK = (localStorage.getItem(STORAGE_KEY_PIN) === '1');
        }
    } catch (e) {
        pinOK = false;
    }

    if (!pinOK) {
        setFormEnabled(false);
        showPinGate();
    } else {
        setFormEnabled(true);
    }

    // Restore form dari localStorage
    restoreFormFromStorage();
    form.addEventListener('input', saveFormToStorage);
    form.addEventListener('change', saveFormToStorage);

    function showLoaderSteps() {
        if (typeof Swal === 'undefined') {
            return;
        }

        var steps = [
            'Lagi ngecek dulu semua data yang kamu isi...',
            'Merapiin dulu nama guru, mapel, kelas, dan semesternya...',
            'Sedang ngobrol bareng mesin pintar buat nyusun ide 😎',
            'Nyocokin materi dengan capaian pembelajaran yang pas...',
            'Menentukan alokasi waktu tiap pertemuan biar pas dan realistis ⏱️',
            'Meracik aktivitas pembelajaran biar nggak ngebosenin tapi tetap on track 🎯',
            'Menyusun kalimat-kalimat pembelajaran biar jelas dan gampang dipahami siswa ✍️',
            'Menyusun kalimat asesmen dan refleksi biar terasa lebih manusiawi dan friendly 😊',
            'Merapikan struktur dan format RPM biar enak dibaca di HP maupun laptop 📄',
            'Sedang poles lagi kalimat-kalimatnya biar makin kece dan rapi 😁',
            'Double check biar nggak ada bagian penting yang ketinggalan ✅',
            'Menyiapkan tampilan preview RPM langsung di layar kamu...',
            'Menyiapkan dokumen dan tombol download biar bisa kamu simpan dan cetak dengan manis 👍'
        ];

        var idx = 0;

        Swal.fire({
            // Title pakai HTML, ada emoji "berpikir"
            title:
                '<div class="rpm-title-wrap">' +
                    '<span class="rpm-title-emoji">🤔</span>' +
                    '<span>Generate RPM dulu ya...</span>' +
                '</div>',

            // Isi: spinner kecil + teks step
            html:
                '<div class="rpm-loader-wrap">' +
                    '<span class="rpm-loader-icon"></span>' +
                    '<span id="rpm-loader-text">' + steps[0] + '</span>' +
                '</div>',

            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,

            didOpen: function (popup) {
                // Icon loading bawaan SweetAlert2
                Swal.showLoading();

                var textEl = popup.querySelector('#rpm-loader-text');
                var interval = setInterval(function () {
                    idx++;
                    if (!textEl || idx >= steps.length) {
                        clearInterval(interval);
                        return;
                    }
                    textEl.textContent = steps[idx];
                }, 1200);

                // Simpan interval ke popup biar bisa dibersihkan saat willClose
                popup.dataset.rpmIntervalId = interval;
            },

            willClose: function (popup) {
                var id = popup && popup.dataset ? popup.dataset.rpmIntervalId : null;
                if (id) {
                    clearInterval(id);
                }
            }
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Kalau belum punya PIN yang valid, paksa buka gate dulu
        if (!pinOK) {
            showPinGate();
            return;
        }

        // Cegah double submit
        if (btn.dataset.submitting === '1') {
            return;
        }

        var namaGuru     = getValue('nama_guru');
        var mapel        = getValue('mata_pelajaran');
        var materi       = getValue('materi');
        var kelas        = getValue('kelas');
        var semester     = getValue('semester');
        var tahunPel     = getValue('tahun_pelajaran');
        var pertemuanVal = getValue('pertemuan');
        var pertemuanNum = parseInt(pertemuanVal, 10);

        if (!namaGuru) {
            showRequiredAlert('nama_guru');
            return;
        }
        if (!mapel) {
            showRequiredAlert('mata_pelajaran');
            return;
        }
        if (!materi) {
            showRequiredAlert('materi');
            return;
        }
        if (!kelas) {
            showRequiredAlert('kelas');
            return;
        }
        if (!semester) {
            showRequiredAlert('semester');
            return;
        }
        if (!tahunPel) {
            showRequiredAlert('tahun_pelajaran');
            return;
        }
        if (!pertemuanVal || isNaN(pertemuanNum) || pertemuanNum <= 0) {
            showRequiredAlert('pertemuan');
            return;
        }

        // Kalau lolos semua validasi
        btn.dataset.submitting = '1';
        btn.disabled = true;
        if (spinner) spinner.classList.remove('hidden');
        if (label)   label.textContent = 'Lagi berpikir keras...';

        // Tampilkan loader step SweetAlert
        showLoaderSteps();

        // Kasih jeda dikit supaya Swal sempat muncul sebelum halaman reload
        setTimeout(function () {
            form.submit();
        }, 400);
    });
});
</script>
</body>
</html>
