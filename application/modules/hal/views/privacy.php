<?php $this->load->view("front_end/head.php") ?>
<div class="container-fluid">
  <div class="hero-title" role="banner" aria-label="Judul situs">
    <h1 class="text">Kebijakan Privasi <?php echo ($rec->nama_website ?? 'Ausi Cafe & Billiard') . (isset($rec->kabupaten) ? ' '.$rec->kabupaten : '') ?></h1>
    <span class="accent" aria-hidden="true"></span>
  </div>

  <div class="row mt-3">
    <div class="col-lg-12">
      <div class="card-box">
        <section style="margin:auto; padding:2rem; font-family:sans-serif; line-height:1.7;">

          <p><em>Terakhir diperbarui: 23 Oktober 2025</em></p>

          <p>Selamat datang di <strong><?php 
            echo ($rec->nama_website ?? 'Ausi Cafe & Billiard')
              .(isset($rec->kabupaten) ? ' '.$rec->kabupaten : '')
              .(isset($rec->meta_deskripsi) && $rec->meta_deskripsi ? ' ('.$rec->meta_deskripsi.')' : '');
          ?></strong>. Kami menghargai privasi Anda dan berkomitmen melindungi informasi pribadi saat Anda menggunakan layanan
          <strong>dine-in via scan barcode meja</strong>, <strong>delivery</strong>, <strong>take away</strong>, dan <strong>booking billiard</strong>.
          Kebijakan ini menjelaskan cara kami mengumpulkan, menggunakan, menyimpan, membagikan, dan melindungi data Anda.</p>

          <hr style="border:0; height:2px; background:#ccc; margin:2rem 0;">

          <h4><strong>1. Informasi yang Kami Kumpulkan</strong></h4>
          <ul>
            <li>Data identitas: nama, nomor telepon/WhatsApp, (opsional) email.</li>
            <li>Data pemesanan: nomor meja (untuk dine-in), item pesanan, catatan khusus/alergen, waktu pesanan, metode ambil (dine-in/delivery/take away), dan riwayat transaksi.</li>
            <li>Data pengantaran (delivery): alamat lengkap, penanggung jawab penerima, patokan lokasi.</li>
            <li>Data pembayaran: metode pembayaran, status/nomor referensi transaksi dari penyedia pembayaran <em>(kami tidak menyimpan data kartu secara penuh)</em>.</li>
            <li>Data perangkat & teknis: alamat IP, jenis peramban, sistem operasi, pengidentifikasi perangkat, <em>cookies</em>, dan data log.</li>
            <li>Komunikasi: pesan ke layanan pelanggan, keluhan, ulasan/penilaian.</li>
          </ul>

          <h4><strong>2. Dasar & Tujuan Pemrosesan Data</strong></h4>
          <p>Kami memproses data berdasarkan persetujuan Anda, pemenuhan kontrak layanan (pemesanan/booking), dan kepentingan sah untuk peningkatan layanan. Tujuan utama:</p>
          <ul>
            <li>Memproses pemesanan/booking, menyiapkan pesanan, dan mengatur pengantaran.</li>
            <li>Verifikasi identitas, notifikasi status pesanan, dan pelayanan pelanggan.</li>
            <li>Penagihan dan rekonsiliasi pembayaran, pencegahan kecurangan.</li>
            <li>Peningkatan kualitas menu & layanan, analitik penggunaan, dan pengembangan fitur.</li>
            <li>Komunikasi promosi (hanya dengan persetujuan/opsi <em>opt-in</em> yang dapat dicabut kapan saja).</li>
          </ul>

          <h4><strong>3. Cookies & Teknologi Serupa</strong></h4>
          <p>Kami menggunakan cookies untuk menjaga sesi pemesanan, menyimpan preferensi, dan melakukan analitik kunjungan. Anda dapat mengatur/menolak cookies lewat pengaturan peramban; beberapa fitur mungkin tidak optimal tanpa cookies.</p>

          <h4><strong>4. Berbagi Informasi dengan Pihak Ketiga</strong></h4>
          <p>Kami <strong>tidak menjual/menyewakan</strong> data pribadi Anda. Data hanya dibagikan kepada pihak berikut sesuai kebutuhan layanan dan perjanjian kerahasiaan:</p>
          <ul>
            <li>Penyedia pembayaran (payment gateway) untuk memproses transaksi.</li>
            <li>Kurir/mitra pengantaran untuk layanan delivery.</li>
            <li>Penyedia infrastruktur TI (hosting, SMS/WhatsApp gateway, sistem email) dan dukungan teknis.</li>
            <li>Otoritas pemerintah/penegak hukum jika diwajibkan peraturan.</li>
          </ul>

          <h4><strong>5. Retensi (Masa Simpan) Data</strong></h4>
          <ul>
            <li>Data transaksi & bukti pembayaran: disimpan sesuai ketentuan akuntansi/perpajakan yang berlaku.</li>
            <li>Data profil & preferensi: disimpan selama akun aktif/Anda masih menggunakan layanan.</li>
            <li>Data log & teknis: disimpan dalam periode wajar untuk keamanan dan analitik.</li>
          </ul>
          <p>Setelah masa retensi berakhir, data akan dihapus/dianonimkan secara aman.</p>

          <h4><strong>6. Keamanan Data</strong></h4>
          <p>Kami menerapkan langkah teknis & organisasi yang wajar, antara lain pembatasan akses berbasis peran, enkripsi dalam transit, otentikasi, pencatatan akses (audit log), dan <em>backup</em> berkala. Meski demikian, tidak ada sistem yang sepenuhnya bebas risiko; kami berupaya maksimal menangani insiden keamanan sesuai prosedur.</p>

          <h4><strong>7. Hak Anda (Subjek Data)</strong></h4>
          <p>Sesuai peraturan perundang-undangan (termasuk <strong>UU PDP No. 27/2022</strong>), Anda berhak untuk:</p>
          <ul>
            <li>Mengakses dan/atau memperoleh salinan data pribadi Anda.</li>
            <li>Memperbaiki data yang tidak akurat/tidak lengkap.</li>
            <li>Menghapus atau menunda pemrosesan data tertentu sesuai ketentuan.</li>
            <li>Mencabut persetujuan (mis. komunikasi promosi) kapan saja.</li>
            <li>Menyampaikan keberatan terkait pemrosesan untuk tujuan tertentu.</li>
          </ul>
          <p>Permohonan hak dapat diajukan melalui kontak pada bagian “Kontak Kami”. Kami dapat melakukan verifikasi identitas sebelum memproses permintaan.</p>

          <h4><strong>8. Anak di Bawah Umur</strong></h4>
          <p>Layanan kami ditujukan bagi pelanggan yang cakap hukum. Jika Anda orang tua/wali dan mengetahui anak Anda memberikan data tanpa izin, hubungi kami untuk penanganan sesuai ketentuan.</p>

          <h4><strong>9. Transfer Data</strong></h4>
          <p>Jika diperlukan, data dapat diproses/ditransfer ke sistem pihak ketiga yang berlokasi di dalam/luar negeri dengan perlindungan dan perjanjian yang sesuai peraturan.</p>

          <h4><strong>10. Perubahan Kebijakan</strong></h4>
          <p>Kebijakan ini dapat diperbarui sewaktu-waktu. Tanggal “Terakhir diperbarui” akan disesuaikan, dan versi terbaru berlaku sejak dipublikasikan di situs/aplikasi.</p>

          <h4><strong>11. Kontak Kami</strong></h4>
          <p>Untuk pertanyaan, permintaan hak subjek data, atau keluhan privasi, silakan hubungi:</p>
          <address>
            <strong>Admin <?php echo ($rec->nama_website ?? 'Ausi Cafe & Billiard') . (isset($rec->kabupaten) ? ' '.$rec->kabupaten : '') ?></strong><br>
            Email: <a href="mailto:<?php echo $rec->email ?>"><?php echo $rec->email ?></a><br>
            WhatsApp: <?php echo $rec->no_telp ?><br>
            Alamat: <?php echo isset($rec->alamat) ? htmlspecialchars($rec->alamat) : 'Alamat usaha'; ?>
          </address>

        </section>
      </div>
    </div>
  </div>
</div>
<?php $this->load->view("front_end/footer.php") ?>
