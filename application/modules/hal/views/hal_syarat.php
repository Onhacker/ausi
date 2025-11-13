<?php $this->load->view("front_end/head.php") ?>
<div class="container-fluid">

  <div class="hero-title" role="banner" aria-label="Judul situs">
    <h1 class="text">Syarat &amp; Ketentuan <?php echo $rec->nama_website ?? 'Ausi Cafe & Billiard'; ?> <?php echo isset($rec->kabupaten) ? $rec->kabupaten : ''; ?></h1>
    <span class="accent" aria-hidden="true"></span>
  </div>

  <div class="row justify-content-center py-3">
    <div class="col-12">
      <div class="card-box">

        <!-- Header -->
        <header class="mb-3 mt-2 text-center">
          <div class="d-flex justify-content-center gap-2">
            <span class="badge bg-light text-dark border">Versi 2.0</span>
            <span class="badge bg-light text-dark border">Terakhir diperbarui: 13 November 2025</span>

          </div>
        </header>

        <!-- Daftar Isi -->
        <section class="card mb-3">
          <div class="card-body">
            <h2 class="h4 mb-2">Daftar Isi</h2>
            <ol class="mb-0">
              <li><a href="#pendahuluan">Pendahuluan</a></li>
              <li><a href="#definisi">Definisi</a></li>
              <li><a href="#ruang-lingkup">Ruang Lingkup Layanan</a></li>
              <li><a href="#pemesan">Pemesanan &amp; Akurasi Data</a></li>
              <li><a href="#dinein">Dine-in (Scan Barcode Meja)</a></li>
              <li><a href="#delivery">Delivery</a></li>
              <li><a href="#takeaway">Take Away (Ambil Sendiri)</a></li>
              <li><a href="#billiard">Booking Billiard</a></li>
              <li><a href="#pembayaran">Pembayaran, Biaya &amp; Promo</a></li>
              <li><a href="#voucher">Voucher &amp; Promo Main Gratis</a></li>
              <li><a href="#voucher-order">Poin Loyalty &amp; Voucher Order Rp 50.000</a></li>

              <li><a href="#kebijakan-produk">Kebijakan Produk &amp; Kualitas</a></li>
              <li><a href="#larangan">Ketertiban, Keamanan &amp; Larangan</a></li>
              <li><a href="#privasi">Privasi &amp; Perlindungan Data</a></li>
              <li><a href="#ketersediaan">Ketersediaan &amp; Perubahan Layanan</a></li>
              <li><a href="#tanggung-jawab">Tanggung Jawab &amp; Batasan</a></li>
              <li><a href="#ki">Kekayaan Intelektual</a></li>
              <li><a href="#force-majeure">Force Majeure</a></li>
              <li><a href="#perubahan">Perubahan Syarat &amp; Ketentuan</a></li>
              <li><a href="#hukum">Hukum &amp; Penyelesaian Sengketa</a></li>
              <li><a href="#kontak">Kontak</a></li>
            </ol>
          </div>
        </section>

        <div class="alert alert-info mb-3" role="alert">
          Dengan melakukan pemesanan (dine-in, delivery, take away) atau membuat booking billiard,
          Anda menyatakan telah membaca, memahami, dan menyetujui Syarat &amp; Ketentuan ini.
        </div>

        <!-- Pendahuluan -->
        <section id="pendahuluan" class="card mb-3">
          <div class="card-body">
            <p class="mb-0">
              Dokumen ini mengatur penggunaan layanan <?php echo $rec->nama_website ?? 'Ausi Cafe & Billiard'; ?> (“Tempat/Layanan”),
              termasuk pemesanan melalui barcode meja, pemesanan antar (delivery), ambil sendiri (take away), dan booking meja billiard.
            </p>
          </div>
        </section>

        <!-- Definisi -->
        <section id="definisi" class="card mb-3">
          <div class="card-body">
            <h2 class="h4">1) Definisi</h2>
            <ol class="mb-0">
              <li><strong>Pengelola</strong>: pihak resmi pengelola <?php echo $rec->nama_website ?? 'Ausi Cafe & Billiard'; ?>.</li>
              <li><strong>Pengguna/Pelanggan</strong>: individu yang melakukan pemesanan/booking melalui sistem.</li>
              <li><strong>Barcode Meja</strong>: kode unik pada meja untuk mengakses menu dan memesan secara digital.</li>
              <li><strong>Booking Billiard</strong>: pemesanan slot waktu penggunaan meja billiard.</li>
              <li><strong>Order</strong>: transaksi pembelian menu/layanan melalui sistem.</li>
            </ol>
          </div>
        </section>

        <!-- Ruang Lingkup -->
        <section id="ruang-lingkup" class="card mb-3">
          <div class="card-body">
            <h2 class="h4">2) Ruang Lingkup Layanan</h2>
            <p class="mb-0">
              Layanan meliputi: pemesanan dine-in via barcode meja, delivery, take away, dan booking billiard,
              termasuk notifikasi status pesanan, tagihan digital, dan histori transaksi.
            </p>
          </div>
        </section>

        <!-- Pemesanan -->
        <section id="pemesan" class="card mb-3">
          <div class="card-body">
            <h2 class="h4">3) Pemesanan &amp; Akurasi Data</h2>
            <ol class="mb-0">
              <li>Pengguna wajib mengisi data secara benar (nama, kontak, alamat untuk delivery, dsb.).</li>
              <li>Pengelola berhak menolak/menunda/membatalkan order/booking bila ada indikasi penyalahgunaan/kecurangan.</li>
              <li>Perubahan atau pembatalan mengikuti kebijakan pada masing-masing layanan (lihat bagian terkait).</li>
            </ol>
          </div>
        </section>

        <!-- Dine-in -->
        <section id="dinein" class="card mb-3">
          <div class="card-body">
            <h2 class="h4">4) Dine-in (Scan Barcode Meja)</h2>
            <ol class="mb-0">
              <li>Pemesanan dilakukan dengan <strong>memindai barcode meja</strong> yang Anda tempati.</li>
              <li>Pastikan nomor meja sesuai saat melakukan pembayaran/tagihan.</li>
              <li><strong>Batas waktu duduk</strong> dapat diterapkan pada jam ramai (peak hour) untuk menunjang kelancaran layanan.</li>
              <li>Permintaan khusus (tanpa bahan tertentu/alergen, tingkat kematangan, dsb.) akan diupayakan sebatas ketersediaan.</li>
            </ol>
          </div>
        </section>

        <!-- Delivery -->
        <section id="delivery" class="card mb-3">
          <div class="card-body">
            <h2 class="h4">5) Delivery</h2>
            <ol class="mb-0">
              <li>Layanan antar mengikuti <strong>radius/ketersediaan kurir</strong> pada sistem saat pemesanan.</li>
              <li>Biaya antar dan estimasi waktu pengantaran ditampilkan sebelum konfirmasi.</li>
              <li>Tanggung jawab produk beralih ke pelanggan saat pesanan <strong>diserahterimakan</strong> kepada penerima yang terdaftar.</li>
              <li>Pengantaran ulang karena alamat tidak jelas/tidak ada penerima dapat dikenai biaya tambahan.</li>
            </ol>
          </div>
        </section>

        <!-- Take Away -->
        <section id="takeaway" class="card mb-3">
          <div class="card-body">
            <h2 class="h4">6) Take Away (Ambil Sendiri)</h2>
            <ol class="mb-0">
              <li>Pesanan diambil pada <strong>loket/konter</strong> yang ditentukan dengan menunjukkan kode order.</li>
              <li>Harap mengambil pesanan dalam <strong>jangka waktu yang disarankan</strong> agar kualitas tetap optimal.</li>
              <li>Pesanan yang tidak diambil dalam jangka waktu lama dapat mengalami penurunan kualitas; pengembalian dana tidak berlaku karena hal tersebut.</li>
            </ol>
          </div>
        </section>

        <!-- Booking Billiard -->
        <section id="billiard" class="card mb-3">
          <div class="card-body">
            <h2 class="h4">7) Booking Billiard</h2>
            <ol class="mb-0">
              <li>Booking dilakukan berdasarkan <strong>slot waktu</strong> yang tersedia pada sistem.</li>
              <li><strong>Kedatangan tepat waktu</strong> wajib; toleransi keterlambatan maksimum 10 menit sebelum slot dilepas untuk pelanggan lain.</li>
              <li>Maksimal pemain per meja mengikuti standar operasional; harap menjaga alat (cue/bola/meja) dari kerusakan.</li>
              <li>Kerusakan karena kelalaian dapat dikenakan <strong>biaya penggantian</strong> sesuai ketentuan Pengelola.</li>
              <li>Pengelola dapat menetapkan <strong>minimum order</strong> makanan/minuman per meja saat jam ramai.</li>
            </ol>
          </div>
        </section>

        <!-- Pembayaran -->
        <section id="pembayaran" class="card mb-3">
          <div class="card-body">
            <h2 class="h4">8) Pembayaran, Biaya &amp; Promo</h2>
            <ol class="mb-0">
              <li>Metode pembayaran: tunai, transfer, atau dompet digital yang tersedia pada sistem/kasir.</li>
              <li>Harga dapat berubah sewaktu-waktu; harga yang berlaku adalah yang tampil pada saat konfirmasi order.</li>
              <li>Promo/kupon mengikuti syarat masing-masing program (periode, minimum transaksi, dan ketersediaan).</li>
            </ol>
          </div>
        </section>

        <!-- Voucher -->
        <section id="voucher" class="card mb-3">
          <div class="card-body">
            <h2 class="h4">9) Voucher &amp; Promo Main Gratis</h2>
            <ol class="mb-0">
              <li>
                Voucher atau promo “main gratis” billiard hanya berlaku pada
                <strong>meja kategori reguler</strong>. Tidak berlaku untuk meja bertipe VIP
                kecuali dinyatakan tertulis oleh Pengelola.
              </li>
              <li>
                Setiap voucher memiliki <strong>durasi main tertentu</strong>
                (misal 1 jam). Saat melakukan booking dengan voucher,
                durasi booking akan mengikuti durasi voucher tersebut
                dan tidak dapat diperpanjang secara gratis.
              </li>
              <li>
                Voucher hanya berlaku dalam <strong>jam operasional promo</strong>
                yang ditetapkan Pengelola. Jika jadwal bermain melewati batas jam promo,
                booking dapat ditolak atau diminta digeser.
              </li>
              <li>
                Voucher terikat pada <strong>nomor HP yang terdaftar</strong>
                saat klaim, tidak dapat dipindahtangankan, dijual, atau dipakai
                oleh nomor lain tanpa persetujuan Pengelola.
              </li>
              <li>
                Satu voucher hanya berlaku untuk <strong>satu booking</strong>.
                Setelah sistem menerbitkan kode booking dengan status “free/gratis”,
                voucher dianggap digunakan dan tidak dapat dipakai ulang.
              </li>
              <li>
                Keterlambatan hadir melewati toleransi yang berlaku atau tidak hadir (“no show”)
                dapat menyebabkan voucher dianggap hangus.
              </li>
              <li>
                Voucher tidak memiliki nilai tunai, <strong>tidak dapat diuangkan</strong>,
                dan tidak dapat digabungkan dengan promo lain kecuali diizinkan Pengelola.
              </li>
              <li>
                Pengelola berhak menolak, membatalkan, atau menarik voucher
                jika terindikasi penyalahgunaan, manipulasi data, atau pelanggaran S&amp;K.
              </li>
              <li>
                Ketersediaan voucher bersifat terbatas. Program voucher dapat dihentikan,
                diubah jam berlakunya, atau dibatasi kuotanya kapan saja tanpa pemberitahuan sebelumnya.
              </li>
            </ol>
          </div>
        </section>

    <section id="voucher-order" class="card mb-3">
  <div class="card-body">
    <h2 class="h4">9.1) Poin Loyalty &amp; Voucher Order Rp 50.000 (Siklus Mingguan)</h2>

    <h3 class="h4 mt-3 mb-2">A. Mekanisme Poin (Siklus Mingguan)</h3>
    <ul class="mb-3">
      <li><strong>Status transaksi</strong> yang dihitung poin hanyalah <em>paid</em> (berhasil). Transaksi void/refund/batal tidak menambah poin.</li>
      <li><strong>Rumus poin:</strong> <span class="mono">poin = kode_unik + (total // 1000)</span> &mdash; contoh: 5.000 → 5; 50.000 → 50; 100.000 → 100.</li>
      <li><strong>Periode pekan:</strong> Minggu 00:00 &ndash; Sabtu 23:59 WITA.</li>
      <li><strong>Reset otomatis:</strong> setiap <u>Minggu 00:00 WITA</u> (awal pekan baru). Pada saat ini, akumulasi pekan sebelumnya ditutup, dan perhitungan dimulai dari nol.</li>
      <li><strong>Cek poin:</strong> total poin pekan berjalan bisa dilihat melalui tautan/token di halaman <em>Points</em>.</li>
    </ul>

    <h3 class="h4 mt-3 mb-2">B. Voucher Order Rp 50.000</h3>
    <ul class="mb-3">
      <li><strong>Pengumuman pemenang:</strong> setiap <u>Minggu pukul 08:00 WITA</u>, untuk periode <strong>pekan sebelumnya</strong>.</li>

      <li><strong>Jumlah pemenang per pekan:</strong> 2 (dua) orang.</li>

      <li>
        <strong>Pemenang 1 (poin tertinggi):</strong> pelanggan dengan poin tertinggi pada pekan tersebut.
        <br><em>Urutan tie-breaker (jika poin sama):</em>
        <ol class="mb-2 mt-1">
          <li><strong>Total belanja (total_rupiah)</strong> lebih besar menang.</li>
          <li><strong>Waktu transaksi terakhir (last_paid_at)</strong> lebih awal menang.</li>
          <li><strong>Jumlah transaksi (transaksi_count)</strong> lebih banyak menang.</li>
        </ol>
      </li>

      <li>
        <strong>Pemenang 2 (acak):</strong> 1 (satu) pelanggan dipilih secara acak dari seluruh peserta yang memiliki poin &gt; 0 pada pekan tersebut
        (tidak termasuk Pemenang 1).
      </li>

      <li><strong>Nilai voucher:</strong> Rp 50.000, bersifat non-tunai dan tidak dapat diuangkan.</li>
      <li><strong>Penggunaan promo:</strong> tidak dapat digabung dengan promo lain kecuali dinyatakan sebaliknya.</li>
      <li><strong>Kepemilikan:</strong> voucher terikat ke nomor WhatsApp/akun terdaftar dan <u>tidak dapat dipindahtangankan</u>.</li>
      <li><strong>Masa berlaku voucher:</strong> 7 (tujuh) hari kalender sejak tanggal penerbitan.</li>
      <li><strong>Cara klaim:</strong> ikuti tautan yang dikirim via WhatsApp atau buka halaman <em>Points</em> menggunakan token Anda.</li>
    </ul>

    <h3 class="h4 mt-3 mb-2">C. Contoh Linimasa</h3>
    <ul class="mb-3">
      <li><strong>Periode dihitung:</strong> Minggu, 09 Nov 2025 00:00 &ndash; Sabtu, 15 Nov 2025 23:59 WITA.</li>
      <li><strong>Reset otomatis:</strong> Minggu, 16 Nov 2025 00:00 WITA (mulai pekan baru).</li>
      <li><strong>Pengumuman pemenang:</strong> Minggu, 16 Nov 2025 pukul 08:00 WITA.</li>
    </ul>

    <h3 class="h4 mt-3 mb-2">D. Ketentuan Tambahan</h3>
    <ul class="mb-0">
      <li>Indikasi penyalahgunaan atau manipulasi data dapat menyebabkan pembatalan poin maupun voucher.</li>
      <li>Pengelola berhak mengubah kuota, nilai, atau ketentuan program kapan saja demi peningkatan layanan.</li>
      <li>Zona waktu yang digunakan: <strong>WITA (Asia/Makassar)</strong>.</li>
    </ul>
  </div>
</section>


        <!-- Kebijakan Produk -->
        <section id="kebijakan-produk" class="card mb-3">
          <div class="card-body">
            <h2 class="h4">10) Kebijakan Produk &amp; Kualitas</h2>
            <ul class="mb-0">
              <li>Kami menjaga standar kebersihan dan kualitas; perbedaan tampilan/penyajian dapat terjadi karena ketersediaan bahan musiman.</li>
              <li>Informasi <strong>alergen</strong> dapat diminta kepada staf; pelanggan bertanggung jawab memberi tahu pantangan/kondisi alergi.</li>
              <li>Keluhan terkait kesalahan item/kualitas <strong>harus disampaikan segera</strong> (maks. 30 menit setelah diterima untuk dine-in/delivery).</li>
            </ul>
          </div>
        </section>

        <!-- Ketertiban & Larangan -->
        <section id="larangan" class="card mb-3">
          <div class="card-body">
            <h2 class="h4">11) Ketertiban, Keamanan &amp; Larangan</h2>
            <ol class="mb-0">
              <li>Dilarang merokok di area non-smoking dan dilarang membawa minuman keras/obat terlarang ke area terlarang.</li>
              <li>Dilarang membawa makanan/minuman dari luar tanpa izin Pengelola.</li>
              <li>Jaga ketertiban, tidak melakukan tindakan yang mengganggu pengunjung lain atau merusak fasilitas.</li>
              <li>Anak-anak harus dalam pengawasan orang tua/wali.</li>
            </ol>
          </div>
        </section>

        <!-- Privasi -->
        <section id="privasi" class="card mb-3">
          <div class="card-body">
            <h2 class="h4">12) Privasi &amp; Perlindungan Data</h2>
            <p class="mb-2">
              Pemrosesan data pribadi mengikuti <a href="<?php echo site_url('hal/privacy_policy') ?>">Kebijakan Privasi</a>.
              Pengelola mematuhi ketentuan perundang-undangan, termasuk <strong>UU PDP No. 27/2022</strong>.
            </p>
            <ul class="mb-0">
              <li>Data digunakan untuk pemesanan, pembayaran, notifikasi, dan peningkatan layanan.</li>
              <li>Pengelola menerapkan langkah keamanan yang wajar untuk melindungi data.</li>
              <li>Permintaan akses/perbaikan/penghapusan data dapat diajukan melalui kontak Pengelola.</li>
            </ul>
          </div>
        </section>

        <!-- Ketersediaan -->
        <section id="ketersediaan" class="card mb-3">
          <div class="card-body">
            <h2 class="h4">13) Ketersediaan &amp; Perubahan Layanan</h2>
            <ol class="mb-0">
              <li>Layanan disediakan “sebagaimana adanya”; potensi gangguan dapat terjadi karena pemeliharaan/jaringan/pihak ketiga.</li>
              <li>Menu, harga, jam operasional, dan kebijakan dapat disesuaikan untuk peningkatan layanan.</li>
            </ol>
          </div>
        </section>

        <!-- Tanggung Jawab -->
        <section id="tanggung-jawab" class="card mb-3">
          <div class="card-body">
            <h2 class="h4">14) Tanggung Jawab &amp; Batasan</h2>
            <ol class="mb-0">
              <li>Pengelola tidak bertanggung jawab atas keterlambatan kurir, gangguan pihak ketiga, atau kesalahan alamat dari pelanggan.</li>
              <li>Kerugian akibat pelanggaran S&amp;K oleh pelanggan menjadi tanggung jawab pelanggan.</li>
              <li>Sejauh diizinkan hukum, tanggung jawab Pengelola dibatasi pada upaya wajar memperbaiki gangguan layanan.</li>
            </ol>
          </div>
        </section>

        <!-- KI -->
        <section id="ki" class="card mb-3">
          <div class="card-body">
            <h2 class="h4">15) Kekayaan Intelektual</h2>
            <p class="mb-0">
              Seluruh logo, nama, konten, foto, tampilan antarmuka, dan kode pada sistem dilindungi hukum.
              Dilarang menyalin, memodifikasi, atau mendistribusikan tanpa izin tertulis Pengelola.
            </p>
          </div>
        </section>

        <!-- Force Majeure -->
        <section id="force-majeure" class="card mb-3">
          <div class="card-body">
            <h2 class="h4">16) Force Majeure</h2>
            <p class="mb-0">
              Pengelola dibebaskan dari tuntutan atas kejadian di luar kendali (bencana, listrik/jaringan, kebijakan pemerintah, dsb.)
              yang mengakibatkan layanan terganggu.
            </p>
          </div>
        </section>

        <!-- Perubahan S&K -->
        <section id="perubahan" class="card mb-3">
          <div class="card-body">
            <h2 class="h4">17) Perubahan Syarat &amp; Ketentuan</h2>
            <p class="mb-0">
              Pengelola dapat memperbarui dokumen ini sewaktu-waktu. Versi terbaru akan ditampilkan pada situs/aplikasi.
              Penggunaan berkelanjutan setelah perubahan dianggap sebagai persetujuan pelanggan.
            </p>
          </div>
        </section>

        <!-- Hukum -->
        <section id="hukum" class="card mb-3">
          <div class="card-body">
            <h2 class="h4">18) Hukum &amp; Penyelesaian Sengketa</h2>
            <p class="mb-0">
              S&amp;K ini tunduk pada hukum Republik Indonesia. Sengketa diselesaikan terlebih dahulu melalui musyawarah;
              jika tidak tercapai, mengikuti mekanisme yang berlaku di wilayah <?php echo isset($rec->kabupaten) ? '<strong>'.$rec->kabupaten.'</strong>' : '<strong>Kota Makassar</strong>'; ?>.
            </p>
          </div>
        </section>

        <!-- Kontak -->
        <section id="kontak" class="card mb-3">
          <div class="card-body">
            <h2 class="h4">19) Kontak</h2>
            <p class="mb-2">Pertanyaan terkait S&amp;K dapat dikirim ke:</p>
            <address class="mb-0">
              <strong>Pengelola <?php echo $rec->nama_website ?? 'Ausi Cafe & Billiard'; ?></strong><br>
              Email: <a href="mailto:<?php echo $rec->email ?>"><?php echo $rec->email ?></a><br>
              Telepon: <?php echo $rec->no_telp ?><br>
              Alamat: <?php echo isset($rec->alamat) ? htmlspecialchars($rec->alamat) : 'Alamat usaha'; ?>
            </address>
          </div>
        </section>

        <div class="alert alert-secondary my-4" role="alert">
          <strong>Catatan:</strong> Dengan menekan “Pesan / Order”, “Bayar”, atau membuat “Booking Billiard” pada sistem,
          Anda menyetujui Syarat &amp; Ketentuan ini.
        </div>

      </div>
    </div>
  </div>

</div>
<?php $this->load->view("front_end/footer.php") ?>
