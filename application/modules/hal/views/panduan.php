<?php $this->load->view("front_end/head.php") ?>
<div class="container-fluid">
    <div class="hero-title" role="banner" aria-label="Judul situs">
        <h1 class="text"><?php echo $title ?></h1>

        <span class="accent" aria-hidden="true"></span>
    </div>
    <div class="row mt-3">
        <div class="col-lg-12">
         <div class="card-box">
               <!-- ===========================
     Tata Cara Permohonan Kunjungan
     Letakkan di view mana saja (sudah pakai Bootstrap & MDI)
     =========================== -->
     <section id="panduan-booking">
  <style>
    /* Scoped beautify */
    #panduan-booking { font-size: 1.08rem; line-height: 1.6; }
    #panduan-booking h1.h3 { font-size: 1.75rem; font-weight: 700; }
    #panduan-booking .card { border: 1px solid #e9eef5; border-radius: .9rem; box-shadow: 0 6px 20px rgba(0,0,0,.04); }
    #panduan-booking .card-header { background: #f8fafc; border-bottom: 1px solid #eef2f7; font-size: 1.06rem; }
    #panduan-booking ul li { margin-bottom: .35rem; }
    #panduan-booking ol li { margin-bottom: .55rem; }
    #panduan-booking .btn-lg { font-size: 1.06rem; border-radius: .7rem; padding: .8rem 1.25rem; }

    /* FAQ */
    #panduan-booking .faq .faq-item {
      border: 1px solid #e8eef6; border-left: 4px solid #2f6fed; border-radius: .8rem; margin-bottom: .75rem; overflow: hidden; background: #fff;
    }
    #panduan-booking .faq .faq-q {
      width: 100%; display: flex; align-items: center; justify-content: space-between;
      padding: .95rem 1.1rem; background: #f8fbff; border: 0; text-align: left; font-weight: 600;
    }
    #panduan-booking .faq .faq-q .lead-wrap { display: inline-flex; align-items: center; gap: .5rem; }
    #panduan-booking .faq .faq-q .mdi { font-size: 1.25rem; opacity: .9; }
    #panduan-booking .faq .faq-q .chev { transition: transform .2s ease; }
    #panduan-booking .faq .faq-q[aria-expanded="true"] .chev { transform: rotate(180deg); }
    #panduan-booking .faq .collapse { border-top: 1px dashed #e2e8f0; }
    #panduan-booking .faq .faq-a { padding: 1rem 1.1rem; color: #334155; }
  </style>

  <div class="row justify-content-center">
    <div class="col-lg-9">
      <header class="mb-3">
        <h1 class="h4 d-flex align-items-center">
          <i class="mdi mdi-information-outline mr-2"></i>
          Ikuti langkah berikut untuk mendaftar kunjungan, mengunggah berkas, dan memantau status.
        </h1>
        <!-- <p class="text-dark mb-0">Ikuti langkah berikut untuk mendaftar kunjungan, mengunggah berkas, dan memantau status.</p> -->
      </header>

      <!-- PRASYARAT -->
      <div class="card mb-3">
        <div class="card-header bg-light py-2">
          <strong><i class="mdi mdi-clipboard-text-outline mr-1"></i>Prasyarat</strong>
        </div>
        <div class="card-body">
          <ul class="mb-0">
            <li>Data diri: <b>Nama</b>, <b>Jabatan</b>, <b>NIK/NIP/NRP</b>, <b>No. HP (10–13 digit)</b>, <b>Alamat</b>, <b>Tempat & Tanggal Lahir</b>.</li>
            <li><b>Unit Tujuan</b> dan <b>Instansi Asal</b> (pilih kategori; jika “Lainnya”, tulis nama instansi).</li>
            <li><b>Keperluan</b> kunjungan.</li>
            <li><b>Surat Tugas</b> (opsional saat daftar): PDF/JPG/PNG.<br>
              <small class="text-dark">Saat daftar disarankan ≤ <b>2MB</b>. Dari halaman detail bisa unggah hingga <b>10MB</b>.</small>
            </li>
          </ul>
        </div>
      </div>

      <!-- LANGKAH-LANGKAH -->
      <div class="card mb-3">
        <div class="card-header bg-light py-2">
          <strong><i class="mdi mdi-format-list-numbered mr-1"></i>Langkah Pendaftaran</strong>
        </div>
        <div class="card-body">
          <ol class="pl-3 mb-0">
            <li class="mb-2"><b>Buka Form “Booking Kunjungan”.</b><div class="text-dark">Isi identitas tamu & no. HP aktif WhatsApp.</div></li>
            <li class="mb-2"><b>Pilih Unit Tujuan & Instansi Asal.</b><div class="text-dark">Jika memilih kategori “Lainnya”, isi nama instansi (min. 3 karakter).</div></li>
            <li class="mb-2"><b>Tentukan Tanggal & Jam Kunjungan.</b><div class="text-dark">Sistem hanya menerima waktu dalam jam operasional (otomatis menolak jam istirahat/hari libur & menerapkan lead time minimal).</div></li>
            <li class="mb-2"><b>Isi Pendamping (jika ada).</b><div class="text-dark">Unit bisa membatasi jumlah pendamping. ID: NIK 16 / NIP 18/9 / NRP 8–9 digit; wajib nama, tidak duplikat, & tidak sama dengan ID tamu.</div></li>
            <li class="mb-2"><b>Unggah Surat Tugas (opsional).</b><div class="text-dark">PDF/JPG/PNG. Bisa dilewati dan diunggah via halaman detail setelahnya.</div></li>
            <li class="mb-2"><b>Kirim Permohonan.</b><div class="text-dark">Jika valid & kuota tersedia, sistem membuat <b>Kode Booking</b>, <b>QR Code</b>, dan mengirim link detail + PDF via WhatsApp.</div></li>
          </ol>
        </div>
      </div>

      <!-- SETELAH TERDAFTAR -->
      <div class="card mb-3">
        <div class="card-header bg-light py-2">
          <strong><i class="mdi mdi-qrcode mr-1"></i>Setelah Permohonan Terdaftar</strong>
        </div>
        <div class="card-body">
          <ul class="mb-0">
            <li>Akses <b>Halaman Detail Booking</b> dari link WhatsApp (bersifat privat/bertoken).</li>
            <li><b>Lihat/Unduh</b> QR & <b>Unduh PDF</b> tiket.</li>
            <li><b>Unggah/Lihat/Unduh</b> Surat Tugas & Foto dokumentasi.</li>
            <li><b>Edit Permohonan</b> dan <b>Hapus Permohonan</b> (tergantung batas H-n & jumlah kesempatan edit).</li>
          </ul>
        </div>
      </div>

      <!-- EDIT / HAPUS -->
      <div class="card mb-3">
        <div class="card-header bg-light py-2">
          <strong><i class="mdi mdi-pencil-outline mr-1"></i>Edit & Hapus</strong>
        </div>
        <div class="card-body">
          <ul class="mb-2">
            <li><b>Edit</b> dibatasi: maksimal jumlah perubahan & hanya sampai <b>H-n</b> sebelum hari kunjungan.</li>
            <li><b>Hapus</b> hanya sebelum status kunjungan berjalan (bukan <i>checked_in/checked_out</i>). Menghapus akan ikut menghapus pendamping, QR, dan lampiran terkait.</li>
          </ul>
          <div class="alert alert-info mb-0">
            Perubahan penting (tanggal/jam/unit/pendamping/dst.) akan memicu pengiriman ulang WhatsApp ke tamu & unit terkait.
          </div>
        </div>
      </div>

      <!-- HARI H KUNJUNGAN -->
      <div class="card mb-3">
        <div class="card-header bg-light py-2">
          <strong><i class="mdi mdi-calendar-check mr-1"></i>Hari H Kunjungan</strong>
        </div>
        <div class="card-body">
          <ul class="mb-0">
            <li>Datang ± <b>early_min</b> menit sebelum jadwal (lihat catatan pada halaman detail).</li>
            <li>Bawa <b>KTP asli</b> & identitas instansi.</li>
            <li>Tunjukkan <b>QR Code/PDF</b> saat <b>check-in</b>.</li>
          </ul>
        </div>
      </div>

      <!-- FAQ (baru, lebih rapi) -->
      <div class="card mb-3">
        <div class="card-header bg-light py-2">
          <strong><i class="mdi mdi-help-circle-outline mr-1"></i>FAQ (Pertanyaan Umum)</strong>
        </div>
        <div class="card-body">
          <div id="faq" class="faq">
            <!-- 1 -->
            <div class="faq-item">
              <button class="faq-q" type="button" data-toggle="collapse" data-target="#faq1c" aria-expanded="false">
                <span class="lead-wrap"><i class="mdi mdi-file-download-outline"></i>PDF tidak langsung terunduh?</span>
                <i class="mdi mdi-chevron-down chev"></i>
              </button>
              <div id="faq1c" class="collapse" data-parent="#faq">
                <div class="faq-a">Sistem menyiapkan file lebih dulu. Biarkan proses hingga unduhan berjalan otomatis.</div>
              </div>
            </div>
            <!-- 2 -->
            <div class="faq-item">
              <button class="faq-q" type="button" data-toggle="collapse" data-target="#faq2c" aria-expanded="false">
                <span class="lead-wrap"><i class="mdi mdi-clock-outline"></i>Tidak bisa memilih jam?</span>
                <i class="mdi mdi-chevron-down chev"></i>
              </button>
              <div id="faq2c" class="collapse" data-parent="#faq">
                <div class="faq-a">Kemungkinan di luar jam operasional, berada di jam istirahat, atau belum memenuhi <i>lead time</i> minimal untuk hari ini.</div>
              </div>
            </div>
            <!-- 3 -->
            <div class="faq-item">
              <button class="faq-q" type="button" data-toggle="collapse" data-target="#faq3c" aria-expanded="false">
                <span class="lead-wrap"><i class="mdi mdi-account-multiple-outline"></i>Kuota penuh?</span>
                <i class="mdi mdi-chevron-down chev"></i>
              </button>
              <div id="faq3c" class="collapse" data-parent="#faq">
                <div class="faq-a">Pilih tanggal lain atau unit lain (jika relevan). Sistem menolak otomatis saat kuota harian tercapai.</div>
              </div>
            </div>
            <!-- 4 -->
            <div class="faq-item">
              <button class="faq-q" type="button" data-toggle="collapse" data-target="#faq4c" aria-expanded="false">
                <span class="lead-wrap"><i class="mdi mdi-file-upload-outline"></i>Gagal unggah surat/foto?</span>
                <i class="mdi mdi-chevron-down chev"></i>
              </button>
              <div id="faq4c" class="collapse" data-parent="#faq">
                <div class="faq-a">Pastikan format (PDF/JPG/PNG), ukuran sesuai (saat daftar ≤ 2MB; dari detail ≤ 10MB), dan coba kompres ulang (maks. sisi ± 1600px).</div>
              </div>
            </div>
            <!-- 5 -->
            <div class="faq-item mb-0">
              <button class="faq-q" type="button" data-toggle="collapse" data-target="#faq5c" aria-expanded="false">
                <span class="lead-wrap"><i class="mdi mdi-link-variant"></i>Link detail bisa dibagikan?</span>
                <i class="mdi mdi-chevron-down chev"></i>
              </button>
              <div id="faq5c" class="collapse" data-parent="#faq">
                <div class="faq-a">Link detail bersifat privat (menggunakan token). Jangan dibagikan ke pihak yang tidak berkepentingan.</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- CTA -->
      <div class="text-center">
        <a href="<?= site_url('booking'); ?>" class="btn btn-blue btn-lg">
          <i class="mdi mdi-clipboard-check-outline mr-1"></i>Ajukan Permohonan Kunjungan
        </a>
      </div>
    </div>
  </div>
</section>


</div>
</div>
</div>
</div>

<?php $this->load->view("front_end/footer.php") ?>
