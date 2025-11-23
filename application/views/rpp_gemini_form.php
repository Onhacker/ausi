<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Generator RPP 1 Lembar - Gemini API</title>
  <style>
    body{
      font-family: "Times New Roman", "Calibri", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      max-width: 960px;
      margin: 20px auto;
      padding: 0 15px;
      background: #f5f5f5;
    }
    h1{
      font-size: 1.4rem;
      margin-bottom: .5rem;
    }
    .card{
      background:#fff;
      padding:15px 20px;
      border-radius:10px;
      box-shadow:0 2px 6px rgba(0,0,0,.06);
      margin-bottom:15px;
    }
    .form-row{
      display:flex;
      flex-wrap:wrap;
      gap:10px;
    }
    .form-group{
      flex:1 1 260px;
      margin-bottom:10px;
    }
    label{
      display:block;
      font-size:.85rem;
      margin-bottom:3px;
      font-weight:600;
    }
    input, select, textarea{
      width:100%;
      padding:7px 8px;
      border-radius:6px;
      border:1px solid #ccc;
      font-size:.9rem;
      box-sizing:border-box;
    }
    textarea{
      resize:vertical;
    }
    button{
      border:none;
      border-radius:999px;
      padding:8px 18px;
      font-size:.9rem;
      cursor:pointer;
    }
    .btn-primary{
      background:#0d6efd;
      color:#fff;
    }
    .btn-secondary{
      background:#6c757d;
      color:#fff;
    }
    .hint{
      font-size:.78rem;
      color:#777;
    }
    .rpp-output{
      line-height: 1.6;
      font-size: .9rem;
    }
    /* Styling dasar biar mirip RPP ketika dilihat di browser */
    .rpp-output h2{
      text-align:center;
      text-transform:uppercase;
      font-size:1.1rem;
      margin-bottom:.5rem;
    }
    .rpp-output h3{
      font-size:.98rem;
      margin-top:1rem;
      margin-bottom:.3rem;
    }
    .rpp-output p{
      margin: .1rem 0 .3rem;
    }
    .rpp-output ul,
    .rpp-output ol{
      margin-top:0;
      margin-bottom:.5rem;
      padding-left:1.2rem;
    }
    .rpp-output table{
      border-collapse: collapse;
      width:100%;
      margin-bottom:.8rem;
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
      margin-top: 15px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      flex-wrap: wrap;
      gap: 10px;
    }
  </style>
</head>
<body>

<h1>Generator RPP 1 Lembar (Terhubung Gemini API)</h1>
<p>Isi form lalu klik <b>Generate RPP</b>. Server akan langsung memanggil Gemini dan menampilkan hasil RPP dengan susunan seperti dokumen resmi.</p>

<div class="card">
  <form method="post" action="<?= current_url(); ?>">
    <div class="form-row">
      <div class="form-group">
        <label for="nama_guru">Nama Guru</label>
        <input type="text" id="nama_guru" name="nama_guru" required>
      </div>
      <div class="form-group">
        <label for="sekolah">Sekolah</label>
        <input type="text" id="sekolah" name="sekolah" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="kelas">Kelas / Fase</label>
        <input type="text" id="kelas" name="kelas" placeholder="Misal: X / Fase E" required>
      </div>
      <div class="form-group">
        <label for="semester">Semester</label>
        <select id="semester" name="semester" required>
          <option value="">-- Pilih --</option>
          <option value="Ganjil">Ganjil</option>
          <option value="Genap">Genap</option>
        </select>
      </div>
      <div class="form-group">
        <label for="tahun_pelajaran">Tahun Pelajaran</label>
        <input type="text" id="tahun_pelajaran" name="tahun_pelajaran" placeholder="2025/2026" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="materi">Topik / Materi</label>
        <input type="text" id="materi" name="materi" placeholder="Asking and Giving Opinion" required>
      </div>
      <div class="form-group">
        <label for="alokasi_waktu">Alokasi Waktu</label>
        <input type="text" id="alokasi_waktu" name="alokasi_waktu" placeholder="2 x 45 menit" required>
      </div>
      <div class="form-group">
        <label for="pertemuan">Jumlah Pertemuan</label>
        <input type="text" id="pertemuan" name="pertemuan" placeholder="1 pertemuan" required>
      </div>
    </div>

    <div class="form-group">
      <label for="karakter_siswa">Karakteristik Siswa</label>
      <textarea id="karakter_siswa" name="karakter_siswa" rows="2"
        placeholder="Misal: Siswa SMK jurusan TBSM, kemampuan bahasa Inggris beragam, suka contoh kontekstual dunia kerja."></textarea>
    </div>

    <button type="submit" class="btn-primary">Generate RPP</button>
    <p class="hint">Catatan: butuh koneksi internet & API key Gemini yang aktif.</p>
  </form>
</div>

<?php if (!empty($rpp_result)): ?>
  <div class="card">
    <div class="rpp-output">
      <!-- TAMPILKAN HTML DARI GEMINI APA ADANYA -->
      <?= $rpp_result; ?>
    </div>

    <div class="download-bar">
      <!-- Form download: kirim HTML yang sudah di-escape -->
      <form method="post" action="<?= site_url('rpp_gemini/download'); ?>">
        <textarea name="rpp_content" style="display:none;"><?= htmlspecialchars($rpp_result, ENT_NOQUOTES, 'UTF-8'); ?></textarea>
        <button type="submit" class="btn-secondary">Download RPP (.doc)</button>
      </form>
      <span class="hint">Klik untuk mengunduh sebagai file Word. Susunan heading, tabel, dan daftar akan ikut terbawa.</span>
    </div>
  </div>
<?php endif; ?>

</body>
</html>
