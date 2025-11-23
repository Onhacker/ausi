<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Generator RPP 1 Lembar - Gemini API</title>
  <style>
    body{
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
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
    .hint{
      font-size:.78rem;
      color:#777;
    }
    pre{
      white-space:pre-wrap;
      font-family: "JetBrains Mono","Fira Code",monospace;
      font-size:.85rem;
    }
  </style>
</head>
<body>

<h1>Generator RPP 1 Lembar (Terhubung Gemini API)</h1>
<p>Isi form lalu klik <b>Generate RPP</b>. Server akan langsung memanggil Gemini dan menampilkan hasil RPP di bawah.</p>

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
    <h3>Hasil RPP dari Gemini</h3>
    <pre><?= htmlspecialchars($rpp_result, ENT_QUOTES, 'UTF-8'); ?></pre>
  </div>
<?php endif; ?>

</body>
</html>
