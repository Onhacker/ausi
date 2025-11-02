<?php $this->load->view("front_end/head.php"); ?>

<?php
// safety biar gak notice undefined
$nama_web   = $rec->nama_website ?? 'Ausi Cafe & Billiard';
$kabupaten  = isset($rec->kabupaten) && $rec->kabupaten ? ' '.$rec->kabupaten : '';
$tagline    = isset($rec->meta_deskripsi) && $rec->meta_deskripsi ? ' ('.$rec->meta_deskripsi.')' : '';
$brand_full = $nama_web . $kabupaten . $tagline;

$admin_email = isset($rec->email) && $rec->email ? $rec->email : 'admin@ausi.co.id';
$admin_wa    = isset($rec->no_telp) && $rec->no_telp ? $rec->no_telp : '0822-xxxx-xxxx';
$alamat_usaha= isset($rec->alamat) && $rec->alamat ? htmlspecialchars($rec->alamat, ENT_QUOTES, 'UTF-8') : 'Alamat usaha';
?>

<div class="container-fluid">
  <div class="hero-title" role="banner" aria-label="Judul situs">
    <h1 class="text">Kebijakan Privasi <?= $nama_web . $kabupaten; ?></h1>
    <span class="accent" aria-hidden="true"></span>
  </div>

  <div class="row mt-3">
    <div class="col-lg-12">
      <div class="card-box">
        <section style="margin:auto; padding:2rem; font-family:sans-serif; line-height:1.7;">

          <p><em>Terakhir diperbarui: 3 November 2025</em></p>

          <p>
            Selamat datang di <strong><?= $brand_full; ?></strong>.
            Kami menghargai privasi Anda dan berkomitmen melindungi informasi pribadi
            saat Anda menggunakan layanan
            <strong>dine-in via scan barcode meja</strong>,
            <strong>delivery</strong>,
            <strong>take away</strong>,
            dan <strong>booking billiard</strong>.
            Kebijakan ini menjelaskan cara kami mengumpulkan, menggunakan, menyimpan,
            membagikan, dan melindungi data Anda, termasuk data lokasi.
          </p>

          <hr style="border:0; height:2px; background:#ccc; margin:2rem 0;">

          <h4><strong>1. Informasi yang Kami Kumpulkan</strong></h4>
          <ul>
            <li>
              <strong>Data identitas</strong>:
              nama, nomor telepon / WhatsApp, (opsional) email.
            </li>

            <li>
              <strong>Data pemesanan</strong>:
              nomor meja (untuk dine-in),
              item pesanan,
              catatan khusus/alergen,
              waktu pesanan,
              metode ambil (dine-in / delivery / take away),
              serta riwayat transaksi dan status booking billiard.
            </li>

            <li>
              <strong>Data pengantaran (delivery)</strong>:
              alamat lengkap yang Anda tulis sendiri,
              penanggung jawab penerima,
              patokan lokasi,
              instruksi akses (misal: "lewat gang samping masjid").
            </li>

            <li>
              <strong>Data lokasi (koordinat GPS)</strong>:
              titik koordinat perkiraan perangkat Anda (lintang/bujur).
              Kami hanya meminta ini <em>jika Anda menekan tombol seperti
              "Posisi Saya" / "Gunakan Lokasi Saya"</em> di fitur delivery / ongkir
              pada situs atau aplikasi kami.
              Akses lokasi ini memerlukan izin dari perangkat Anda.
              Anda bebas menolak; jika ditolak, Anda dapat memasukkan alamat manual.
            </li>

            <li>
              <strong>Data jarak & ongkir</strong>:
              hasil perhitungan jarak antara lokasi toko dan titik tujuan Anda
              (misal untuk menghitung ongkir, memeriksa apakah masih dalam radius layanan,
              dan estimasi waktu tempuh).
            </li>

            <li>
              <strong>Data pembayaran</strong>:
              metode bayar, status pembayaran, nomor referensi transaksi
              dari penyedia pembayaran.
              <em>Kami tidak menyimpan data kartu pembayaran Anda secara penuh.</em>
            </li>

            <li>
              <strong>Data perangkat & teknis</strong>:
              alamat IP, jenis peramban, sistem operasi,
              pengidentifikasi perangkat terbatas,
              <em>cookies</em>,
              dan data log seperti waktu akses / error teknis.
            </li>

            <li>
              <strong>Komunikasi</strong>:
              pesan ke admin / CS,
              keluhan,
              masukan,
              ulasan dan rating layanan/produk.
            </li>
          </ul>

          <h4><strong>2. Dasar & Tujuan Pemrosesan Data</strong></h4>
          <p>
            Kami memproses data berdasarkan:
            persetujuan Anda,
            pelaksanaan layanan yang Anda minta (transaksi / booking),
            dan kepentingan sah kami untuk menjaga kualitas & keamanan layanan.
            Tujuan utama kami mengolah data Anda termasuk:
          </p>
          <ul>
            <li>
              <strong>Melayani pesanan & booking</strong>:
              menyiapkan makanan/minuman,
              menyiapkan meja billiard,
              mengatur antrean,
              mengelola status pesanan.
            </li>

            <li>
              <strong>Pengantaran & ongkir</strong>:
              menggunakan lokasi Anda (jika Anda setujui)
              untuk menghitung ongkir,
              mengecek apakah alamat Anda masih dalam radius layanan,
              menentukan rute antar,
              dan menyimpan titik tujuan untuk kurir.
            </li>

            <li>
              <strong>Verifikasi & notifikasi</strong>:
              menghubungi Anda via WhatsApp / telepon
              untuk konfirmasi pesanan, kendala alamat, atau antrian billiard.
            </li>

            <li>
              <strong>Pembayaran & keamanan</strong>:
              penagihan,
              rekonsiliasi pembayaran,
              audit transaksi,
              pencegahan pemesanan fiktif / penipuan.
            </li>

            <li>
              <strong>Peningkatan layanan</strong>:
              menganalisis menu yang laris,
              jam ramai billiard,
              estimasi waktu kirim,
              performa kurir,
              dan stabilitas teknis aplikasi.
            </li>

            <li>
              <strong>Komunikasi promosi opsional</strong>:
              mengirim promo, voucher loyalti, atau info event,
              <em>hanya jika Anda memilih untuk ikut (opt-in)</em>.
              Anda bisa berhenti kapan saja.
            </li>
          </ul>

          <h4><strong>3. Cookies & Teknologi Serupa</strong></h4>
          <p>
            Kami menggunakan cookies / storage lokal di browser
            untuk menjaga sesi pemesanan,
            mengingat meja/barcode yang Anda scan,
            menyimpan mode pesanan (dine-in / delivery / take away),
            dan melakukan analitik kunjungan.
            Anda bisa membatasi cookies dari pengaturan browser/perangkat.
            Tanpa cookies, beberapa fitur mungkin tidak berfungsi normal
            (misalnya keranjang pesanan bisa hilang).
          </p>

          <h4><strong>4. Berbagi Informasi dengan Pihak Ketiga</strong></h4>
          <p>
            Kami <strong>tidak menjual atau menyewakan</strong> data pribadi Anda.
            Namun, kami dapat membagikan data secara terbatas kepada:
          </p>
          <ul>
            <li>
              <strong>Kurir / petugas antar</strong>:
              nama penerima,
              nomor kontak,
              alamat tujuan,
              dan (jika Anda setujui pakai "Posisi Saya")
              titik koordinat tujuan agar kurir tidak tersesat.
            </li>

            <li>
              <strong>Penyedia pembayaran</strong> (payment gateway / bank):
              agar pembayaran dapat diproses dan diverifikasi.
            </li>

            <li>
              <strong>Penyedia infrastruktur TI</strong>:
              hosting server,
              layanan SMS / WhatsApp gateway,
              sistem notifikasi,
              analitik performa.
              Akses mereka dibatasi sesuai fungsi teknis.
            </li>

            <li>
              <strong>Otoritas pemerintah / penegak hukum</strong>:
              jika diwajibkan oleh hukum, untuk kepatuhan pajak, investigasi, atau penyelesaian sengketa.
            </li>
          </ul>
          <p>
            Kami tidak memberikan data lokasi Anda secara publik.
            Koordinat hanya dipakai internal untuk layanan antar
            dan perhitungan ongkir.
          </p>

          <h4><strong>5. Retensi (Masa Simpan) Data</strong></h4>
          <ul>
            <li>
              <strong>Data transaksi & bukti pembayaran</strong>:
              disimpan sesuai ketentuan akuntansi/perpajakan dan kebutuhan audit.
            </li>
            <li>
              <strong>Data profil & preferensi</strong>:
              disimpan selama Anda masih aktif menggunakan layanan kami
              (misal sering pesan / booking).
            </li>
            <li>
              <strong>Data lokasi untuk ongkir</strong>:
              koordinat tujuan pengantaran dapat ikut tersimpan
              sebagai bagian dari catatan pesanan delivery
              (supaya kurir tahu titik antar dan untuk bukti pengantaran).
            </li>
            <li>
              <strong>Data log teknis</strong>:
              disimpan dalam periode wajar untuk keamanan, debugging, dan analitik penggunaan.
            </li>
          </ul>
          <p>
            Setelah masa retensi berakhir,
            data akan dihapus atau dianonimkan secara aman.
          </p>

          <h4><strong>6. Keamanan Data</strong></h4>
          <p>
            Kami menerapkan langkah teknis & organisasi yang wajar,
            antara lain pembatasan akses berbasis peran,
            enkripsi saat data dikirim (in transit),
            autentikasi internal,
            pencatatan akses (audit log),
            dan <em>backup</em> rutin.
            Tidak ada sistem digital yang 100% bebas risiko,
            namun kami berupaya maksimal menangani insiden keamanan sesuai prosedur.
          </p>

          <h4><strong>7. Hak Anda atas Data Pribadi</strong></h4>
          <p>Anda berhak untuk:</p>
          <ul>
            <li>
              Meminta salinan data pribadi yang kami simpan tentang Anda
              (termasuk riwayat pesanan).
            </li>
            <li>
              Memperbaiki data yang tidak akurat / sudah tidak relevan.
            </li>
            <li>
              Meminta penghapusan data tertentu,
              sepanjang tidak bertentangan dengan kewajiban hukum/pencatatan transaksi kami.
            </li>
            <li>
              Menolak / mencabut persetujuan terhadap penggunaan data
              untuk tujuan promosi.
            </li>
            <li>
              Mengajukan keberatan atas pemrosesan data tertentu.
            </li>
          </ul>
          <p>
            Kami dapat melakukan verifikasi identitas sebelum memproses permintaan
            (misal untuk mencegah pengambilan data orang lain).
          </p>

          <h4><strong>8. Pengguna di Bawah Umur</strong></h4>
          <p>
            Layanan kami ditujukan bagi pelanggan yang cakap hukum.
            Jika Anda orang tua / wali dan mengetahui anak di bawah umur
            memberikan data pribadi tanpa izin,
            silakan hubungi kami untuk penanganan.
          </p>

          <h4><strong>9. Transfer & Pemrosesan Lintas Sistem</strong></h4>
          <p>
            Dalam beberapa kasus,
            data dapat diproses atau disimpan pada infrastruktur teknis
            milik pihak ketiga (misalnya server atau layanan pesan)
            yang mungkin berada di wilayah berbeda.
            Kami mewajibkan pihak tersebut menjaga kerahasiaan
            dan keamanan data sesuai perjanjian.
          </p>

          <h4><strong>10. Perubahan Kebijakan Privasi</strong></h4>
          <p>
            Kebijakan ini dapat diperbarui sewaktu-waktu.
            Tanggal “Terakhir diperbarui” di bagian atas akan berubah jika ada revisi.
            Versi terbaru berlaku sejak dipublikasikan di situs / aplikasi resmi kami.
          </p>

          <h4><strong>11. Kontak Kami</strong></h4>
          <p>
            Untuk pertanyaan privasi,
            permintaan akses/penghapusan data,
            atau keluhan:
          </p>
          <address>
            <strong>Admin <?= $nama_web . $kabupaten; ?></strong><br>
            Email: <a href="mailto:<?= htmlspecialchars($admin_email, ENT_QUOTES, 'UTF-8'); ?>">
              <?= htmlspecialchars($admin_email, ENT_QUOTES, 'UTF-8'); ?>
            </a><br>
            WhatsApp: <?= htmlspecialchars($admin_wa, ENT_QUOTES, 'UTF-8'); ?><br>
            Alamat: <?= $alamat_usaha; ?>
          </address>

          <p style="font-size:.9rem; color:#666; margin-top:1rem;">
            Catatan lokasi:
            Aplikasi / situs kami mungkin meminta izin lokasi perangkat Anda
            untuk memudahkan estimasi ongkir dan memastikan alamat antar.
            Anda selalu bisa menolak.
            Jika Anda menolak, Anda tetap bisa lanjut dengan mengetik alamat manual.
          </p>

        </section>
      </div>
    </div>
  </div>
</div>

<?php $this->load->view("front_end/footer.php"); ?>
