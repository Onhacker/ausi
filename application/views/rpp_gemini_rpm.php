<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex, nofollow">
    <title>Generator Rencana Pembelajaran</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
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
        input[type="text"], input[type="number"], select {
            border: 1px solid #d1d5db;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            width: 100%;
            font-size: 0.875rem;
            background-color: #f9fafb;
            color: #1f2937;
        }
        input[type="text"]::placeholder, input[type="number"]::placeholder {
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
</head>
<body class="p-8">

<div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-8 items-start">
    <!-- PANEL KIRI: FORM -->
    <div class="panel p-6">
        <h2 class="text-2xl font-bold mb-1">Rencana Pembelajaran Mendalam</h2>
        <p class="text-sm text-gray-600 mb-6">
            Isi data di bawah ini untuk membuat Rencana Pembelajaran Mendalam (RPM) secara otomatis.
        </p>

        <form method="post" action="<?= current_url(); ?>" id="rpmForm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="nama_sekolah" class="block text-label">Nama Sekolah</label>
                    <input type="text" id="nama_sekolah" name="sekolah" value="SMKN 1 WAJO" readonly>
                </div>
                <div>
                    <label for="nama_guru" class="block text-label">Nama Guru</label>
                    <input type="text" id="nama_guru" name="nama_guru" placeholder="Contoh: Nurhikmah, S.Pd" required>
                </div>

                <div>
                    <label for="mata_pelajaran" class="block text-label">Mata Pelajaran</label>
                    <select id="mata_pelajaran" name="mata_pelajaran" required>
                        <option value="">Pilih mata pelajaran</option>
                        <option value="Bahasa Inggris" selected>Bahasa Inggris</option>
                        <option value="Bahasa Indonesia">Bahasa Indonesia</option>
                        <option value="Matematika">Matematika</option>
                        <option value="PPKN">PPKN</option>
                        <option value="Informatika">Informatika</option>
                        <option value="Simulasi dan Komunikasi Digital">Simulasi dan Komunikasi Digital</option>
                        <option value="Dasar-dasar Kejuruan">Dasar-dasar Kejuruan</option>
                    </select>
                </div>
                <div>
                    <label for="materi" class="block text-label">Materi Pokok</label>
                    <input type="text" id="materi" name="materi" placeholder="Contoh: Asking and Giving Opinion" required>
                </div>

                <div>
                    <label for="kelas" class="block text-label">Kelas</label>
                    <input type="text" id="kelas" name="kelas" placeholder="Contoh: X" required>
                </div>
                <div>
                    <label for="semester" class="block text-label">Semester</label>
                    <select id="semester" name="semester" required>
                        <option value="">Pilih semester</option>
                        <option value="Ganjil">Ganjil</option>
                        <option value="Genap">Genap</option>
                    </select>
                </div>

                <div>
                    <label for="fase" class="block text-label">Fase</label>
                    <input type="text" id="fase" name="fase" placeholder="Contoh: E">
                </div>
                <div>
                    <label for="tahun_pelajaran" class="block text-label">Tahun Ajaran</label>
                    <select id="tahun_pelajaran" name="tahun_pelajaran" required>
                        <option value="">Pilih tahun ajaran</option>
                        <option value="2023/2024">2023/2024</option>
                        <option value="2024/2025">2024/2025</option>
                        <option value="2025/2026" selected>2025/2026</option>
                        <option value="2026/2027">2026/2027</option>
                        <option value="2027/2028">2027/2028</option>
                    </select>
                </div>

                <div>
                    <label for="pertemuan" class="block text-label">Jumlah Pertemuan Dibuat</label>
                    <input type="number" id="pertemuan" name="pertemuan" value="3" min="1" required>
                </div>
                <div>
                    <label for="total_waktu" class="block text-label">Deskripsi Total Waktu (Opsional)</label>
                    <input type="text" id="total_waktu" name="total_waktu" placeholder="Contoh: 9 × 45 Menit">
                </div>
            </div>

            <button type="submit" id="btn-generate" class="w-full mt-6 button-primary">
                <svg class="btn-spinner hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="btn-label">Buat Rencana Pembelajaran Mendalam</span>
            </button>
        </form>
    </div>

    <!-- PANEL KANAN: HASIL DOKUMEN -->
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
            <form method="post" action="<?= site_url('rpp_gemini/download'); ?>" class="mt-4">
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

<script>
(function(){
    const form   = document.getElementById('rpmForm');
    const btn    = document.getElementById('btn-generate');
    if (!form || !btn) return;

    const spinner = btn.querySelector('.btn-spinner');
    const label   = btn.querySelector('.btn-label');

    form.addEventListener('submit', function () {
        if (btn.disabled) return;
        btn.disabled = true;
        if (spinner) spinner.classList.remove('hidden');
        if (label)   label.textContent = 'Lagi berpikir keras...';
    });
})();
</script>
</body>
</html>
