<div style="max-width:600px;margin:2rem auto;font-family:sans-serif;line-height:1.6;padding:1rem;">
  <h1>Permintaan Penghapusan Data Pengguna</h1>
  <p>
    Aplikasi <strong>AUSI BILLIARD & CAFE</strong> mengumpulkan data yang Anda berikan saat
    melakukan pemesanan/booking, seperti:
  </p>
  <ul>
    <li>Nama</li>
    <li>Nomor WhatsApp / nomor telepon</li>
    <li>Alamat pengantaran dan patokan lokasi</li>
    <li>Titik koordinat (jika Anda menekan tombol "Posisi Saya")</li>
    <li>Catatan pesanan / instruksi antar</li>
  </ul>

  <p>
    Anda dapat meminta kami untuk menghapus data-data tersebut dari sistem aktif kami.
    Untuk meminta penghapusan data, silakan hubungi:
  </p>

  <p>
    Email: <a href="mailto:<?= htmlspecialchars($rec->email ?? 'admin@ausi.co.id', ENT_QUOTES, 'UTF-8'); ?>">
      <?= htmlspecialchars($rec->email ?? 'admin@ausi.co.id', ENT_QUOTES, 'UTF-8'); ?>
    </a><br>
    WhatsApp: <?= htmlspecialchars($rec->no_telp ?? '0822-xxxx-xxxx', ENT_QUOTES, 'UTF-8'); ?>
  </p>

  <p>
    Saat menghubungi kami, mohon sertakan:
  </p>
  <ul>
    <li>Nama pemesan</li>
    <li>Nomor WhatsApp yang dipakai saat memesan</li>
    <li>Tanggal pesanan (perkiraan)</li>
    <li>Jika delivery: alamat/titik antar yang ingin dihapus</li>
  </ul>

  <p>
    Kami akan memproses permintaan penghapusan data pribadi operasional Anda
    (kontak, alamat antar, titik lokasi, catatan pesanan personal) paling lambat 30 hari kerja.
  </p>

  <p>
    Catatan penting:<br>
    Kami mungkin tetap menyimpan sebagian informasi transaksi (misalnya total belanja,
    metode pembayaran, bukti pembayaran) sesuai kewajiban pembukuan dan perpajakan.
    Data tersebut tidak akan digunakan untuk marketing setelah Anda meminta penghapusan.
  </p>

  <p>
    Jika Anda memiliki pertanyaan tambahan mengenai privasi atau penggunaan data,
    silakan lihat juga Kebijakan Privasi kami di
    <a href="https://ausi.co.id/kebijakan-privasi">https://ausi.co.id/kebijakan-privasi</a>.
  </p>
</div>
