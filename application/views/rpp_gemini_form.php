<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Rencana Pembelajaran – Gemini</title>
  <style>
    :root{
      --bg-page: #f3f4f6;
      --card-bg: #ffffff;
      --border-subtle: #e5e7eb;
      --accent: #2563eb;
      --text-main: #111827;
      --text-muted: #6b7280;
      --radius-card: 18px;
    }

    *{ box-sizing:border-box; }

    body{
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      margin:0;
      padding:24px 12px 32px;
      background: var(--bg-page);
      color: var(--text-main);
    }

    .page-container{
      max-width: 1200px;
      margin: 0 auto;
    }

    .panel{
      background: var(--card-bg);
      border-radius: var(--radius-card);
      box-shadow: 0 15px 30px rgba(15,23,42,.07);
      padding: 20px 22px 18px;
      margin-bottom: 18px;
    }

    .panel-header-title{
      font-size: 1.25rem;
      font-weight: 700;
      margin-bottom: 4px;
    }

    .panel-header-sub{
      font-size: .9rem;
      color: var(--text-muted);
      margin-bottom: 14px;
    }

    .form-row{
      display:flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .form-group{
      flex:1 1 220px;
      margin-bottom: 10px;
    }

    label{
      display:block;
      font-size:.8rem;
      font-weight:600;
      color:#4b5563;
      margin-bottom:4px;
    }

    input, select, textarea{
      width:100%;
      padding:9px 10px;
      border-radius: 10px;
      border:1px solid var(--border-subtle);
      font-size:.9rem;
      color:#111827;
      background:#f9fafb;
      outline:none;
      transition: border-color .15s, box-shadow .15s, background .15s;
    }

    input::placeholder,
    textarea::placeholder{
      color:#9ca3af;
      font-size:.85rem;
    }

    input:focus, select:focus, textarea:focus{
      border-color: var(--accent);
      box-shadow: 0 0 0 1px rgba(37,99,235,.25);
      background:#ffffff;
    }

    .btn-primary{
      width:100%;
      border:none;
      border-radius: 999px;
      padding:11px 18px;
      font-size:.95rem;
      font-weight:600;
      cursor:pointer;
      background: var(--accent);
      color:#ffffff;
      margin-top: 8px;
      transition: background .15s, transform .05s, opacity .15s;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap: 8px;
    }
    .btn-primary:hover{
      background:#1d4ed8;
    }
    .btn-primary:active{
      transform: translateY(1px);
    }
    .btn-primary[disabled]{
      opacity: .78;
      cursor: wait;
    }

    .btn-spinner{
      width:16px;
      height:16px;
      border-radius:999px;
      border:2px solid rgba(255,255,255,.6);
      border-top-color:#ffffff;
      animation: spin 0.7s linear infinite;
      display:none;
    }
    .btn-primary.is-loading .btn-spinner{
      display:inline-block;
    }

    @keyframes spin{
      to { transform: rotate(360deg); }
    }

    .btn-secondary{
      border:none;
      border-radius:999px;
      padding:8px 16px;
      font-size:.88rem;
      font-weight:500;
      cursor:pointer;
      background:#4b5563;
      color:#ffffff;
      transition: background .15s, transform .05s;
    }
    .btn-secondary:hover{
      background:#374151;
    }
    .btn-secondary:active{
      transform: translateY(1px);
    }

    .hint{
      font-size:.78rem;
      color:var(--text-muted);
      margin-top:6px;
    }

    .result-header{
      font-size:1.1rem;
      font-weight:700;
      margin-bottom:8px;
    }
    .result-box{
      border-radius: 14px;
      border: 1px dashed var(--border-subtle);
      min-height: 260px;
      padding: 16px 14px;
      background:#f9fafb;
      overflow:auto;
    }
    .result-placeholder{
      display:flex;
      align-items:center;
      justify-content:center;
      height:100%;
      color: var(--text-muted);
      font-size:.9rem;
      text-align:center;
    }

    .rpp-output{
      font-family: "Times New Roman", "Georgia", serif;
      font-size:.93rem;
      line-height:1.6;
      color:#111827;
    }
    .rpp-output h2{
      text-align:center;
      text-transform:uppercase;
      font-size:1.1rem;
      margin: 0 0 .4rem;
    }
    .rpp-output h3{
      font-size:.98rem;
      margin-top:.9rem;
      margin-bottom:.3rem;
    }
    .rpp-output p{
      margin:.1rem 0 .35rem;
    }
    .rpp-output ul,
    .rpp-output ol{
      margin-top:0;
      margin-bottom:.5rem;
      padding-left:1.25rem;
    }
    .rpp-output table{
      border-collapse:collapse;
      width:100%;
      margin:.4rem 0 .8rem;
      font-size:.9rem;
    }
    .rpp-output table,
    .rpp-output th,
    .rpp-output td{
      border:1px solid #000;
    }
    .rpp-output th,
    .rpp-output td{
      padding:4px 6px;
      vertical-align:top;
    }

    .download-bar{
      margin-top: 12px;
      display:flex;
      justify-content:space-between;
      align-items:center;
      flex-wrap:wrap;
      gap:10px;
    }
  </style>
</head>
<body>
<div class="page-container">

  <!-- PANEL: FORM -->
  <div class="panel">
    <div class="panel-header-title">Rencana Pembelajaran Mendalam</div>
    <div class="panel-header-sub">
      Isi data di bawah ini untuk membuat dokumen Rencana Pembelajaran (RPP 1 Lembar / RPM) secara otomatis.
    </div>

    <form method="post" action="<?= current_url(); ?>" id="rppForm">
      <div class="form-row">
  <div class="form-group">
    <label for="sekolah">Nama Sekolah</label>
    <input
      type="text"
      id="sekolah"
      name="sekolah"
      value="SMKN 1 WAJO"
      readonly
    >
  </div>
  <div class="form-group">
    <label for="nama_guru">Nama Guru</label>
    <input type="text" id="nama_guru" name="nama_guru" placeholder="Contoh: Nurhikmah, S.Pd" required>
  </div>
</div>


      <div class="form-row">
        <div class="form-group">
          <label for="mata_pelajaran">Mata Pelajaran</label>
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
        <div class="form-group">
          <label for="materi">Materi Pokok</label>
          <input type="text" id="materi" name="materi" placeholder="Contoh: Asking and Giving Opinion" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="kelas">Kelas</label>
          <input type="text" id="kelas" name="kelas" placeholder="Contoh: X" required>
        </div>
        <div class="form-group">
          <label for="semester">Semester</label>
          <select id="semester" name="semester" required>
            <option value="">Pilih semester</option>
            <option value="Ganjil">Ganjil</option>
            <option value="Genap">Genap</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="fase">Fase</label>
          <input type="text" id="fase" name="fase" placeholder="Contoh: E">
        </div>
        <div class="form-group">
          <label for="tahun_pelajaran">Tahun Ajaran</label>
          <select id="tahun_pelajaran" name="tahun_pelajaran" required>
            <option value="">Pilih tahun ajaran</option>
            <option value="2023/2024">2023/2024</option>
            <option value="2024/2025">2024/2025</option>
            <option value="2025/2026" selected>2025/2026</option>
            <option value="2026/2027">2026/2027</option>
            <option value="2027/2028">2027/2028</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="pertemuan">Jumlah Pertemuan Dibuat</label>
          <input type="number" min="1" id="pertemuan" name="pertemuan" value="1" required>
        </div>
        <div class="form-group">
          <label for="total_waktu">Deskripsi Total Waktu (Opsional)</label>
          <input type="text" id="total_waktu" name="total_waktu" placeholder="Contoh: 3 × 3 JP (9 × 45 menit)">
        </div>
      </div>

      <button type="submit" class="btn-primary" id="btn-generate">
        <span class="btn-spinner" aria-hidden="true"></span>
        <span class="btn-label">Buat Rencana Pembelajaran</span>
      </button>
      <!-- <div class="hint">Dokumen akan dihasilkan oleh Gemini berdasarkan data di atas.</div> -->
    </form>
  </div>

  <!-- PANEL: HASIL DOKUMEN DI BAWAH -->
  <div class="panel">
    <div class="result-header">Hasil Dokumen</div>

    <div class="result-box">
      <?php if (!empty($rpp_result) && stripos($rpp_result, 'ERROR') === false): ?>
        <div class="rpp-output">
          <?= $rpp_result; ?>
        </div>
      <?php elseif (!empty($rpp_result)): ?>
        <div class="result-placeholder">
          <?= htmlspecialchars($rpp_result, ENT_QUOTES, 'UTF-8'); ?>
        </div>
      <?php else: ?>
        <div class="result-placeholder">
          Hasil dokumen Anda akan ditampilkan di sini setelah form di atas dikirim.
        </div>
      <?php endif; ?>
    </div>

    <?php if (!empty($rpp_result) && stripos($rpp_result, 'ERROR') === false): ?>
      <div class="download-bar">
        <form method="post" action="<?= site_url('rpp_gemini/download'); ?>">
          <textarea name="rpp_content" style="display:none;"><?= htmlspecialchars($rpp_result, ENT_NOQUOTES, 'UTF-8'); ?></textarea>
          <button type="submit" class="btn-secondary">Download Dokumen (.doc)</button>
        </form>
        <div class="hint">Klik untuk mengunduh sebagai file Word. Struktur tabel dan heading akan ikut terbawa.</div>
      </div>
    <?php endif; ?>
  </div>

</div>

<script>
  (function(){
    const form  = document.getElementById('rppForm');
    const btn   = document.getElementById('btn-generate');
    const label = btn ? btn.querySelector('.btn-label') : null;

    if (form && btn) {
      form.addEventListener('submit', function () {
        if (btn.disabled) return;
        btn.disabled = true;
        btn.classList.add('is-loading');
        if (label) label.textContent = 'Lagi berpikir keras...';
      });
    }
  })();
</script>
</body>
</html>
