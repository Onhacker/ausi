<?php $this->load->view("front_end/head.php") ?>
<div class="container-fluid">
  <div class="hero-title" role="banner" aria-label="Judul situs">
    <h1 class="text">Alur Pembooking(an) Kunjungan Tamu</h1>
    <span class="accent" aria-hidden="true"></span>
  </div>
  <div class="row">
    <div class="col-lg-12">
      <div class="search-result-box card-box">

        <style>
          .timeline-sm-item{padding-left:40px;position:relative}
          /*.timeline-sm-item:before{
            content:''; position:absolute; left:-20px; top:2px; width:18px; height:18px; border-radius:50%;
            background-color:#00008B; border:2px solid #fff; box-shadow:0 0 5px rgba(0,0,0,.15)
            }*/
            .timeline-sm-date{font-weight:bold}
            .timeline-sm-item h4{font-weight:600;color:#333}
            .timeline-sm-item p{font-size:1rem;line-height:1.6;text-align:justify}
            .text-muted{color:#6c757d!important}
            .timeline-sm-item:hover h4{color:#00008B}
            .timeline-sm-item:hover .timeline-sm-date{color:#007bff}
            .gal-box img{border-radius:10px;box-shadow:0 6px 20px rgba(0,0,0,.08)}
            .badge-step{display:inline-block;min-width:28px;height:28px;line-height:24px;border-radius:50%;
              text-align:center;border:2px solid #00008B;color:#00008B;font-weight:700;margin-right:.35rem}
              .tip{background:#f8fbff;border:1px solid #e6f0ff;border-radius:8px;padding:.6rem .75rem}
            </style>

            <div class="container">
              <link href="<?= base_url('assets/admin/libs/magnific-popup/magnific-popup.css'); ?>" rel="stylesheet" type="text/css" />


         <!--  <h3 class="text-center mb-3">
            <i class="mdi mdi-qrcode-scan mr-1"></i> Alur Pembooking(an) Kunjungan Tamu
          </h3> -->

          <!-- Flowchart (opsional) -->
          <!-- <div class="text-center text-muted">Form → Approved & Ticket (QR) → Check-in (Scan) → Layanan → Checkout → Statistik</div> -->
          <a href="<?php echo base_url("assets/images/flowcart.webp") ?>" class="image-popup" title="Alur">
            <img src="<?php echo base_url("assets/images/flowcart.webp") ?>" class="img-fluid" alt="work-thumbnail">
          </a>
          <ul class="list-unstyled timeline-sm mt-4">
            <!-- Step 1 -->
            <li class="timeline-sm-item">

              <h4 class="mt-0">Buat Booking</h4>
              <p>
                Pengunjung membuka halaman <a href="<?= site_url('booking') ?>">Form Booking</a>,
                memilih <strong>tanggal &amp; jam</strong> kunjungan, mengisi <strong>data tamu</strong> (nama, NIK/NIP/NRP, alamat, no. HP),
                memilih <strong>kategori &amp; instansi asal</strong>, menentukan <strong>unit tujuan</strong>,
                serta menuliskan <strong>keperluan</strong>. Opsional: unggah <strong>Surat Tugas (PDF/JPG/PNG ≤ 2MB)</strong>.
                Setelah disubmit, sistem membuat <strong>kode booking</strong> dan menampilkan ringkasan permohonan.
              </p>
            </li>

            <!-- Step 2 -->
            <li class="timeline-sm-item">

              <h4 class="mt-0">Verifikasi Admin</h4>
              <p>
                Sistem memeriksa ketersediaan slot dan kelengkapan data. Jika sesuai, status menjadi <strong>Approved</strong>.
                Sistem menerbitkan <strong>QR Code</strong> &amp; <strong>Ticket (PDF)</strong> yang dapat diunduh.
                (Opsional) Notifikasi dikirim via WhatsApp/SMS berisi kode booking &amp; tautan ticket.
              </p>
              <p class="text-dark mb-0">
                <em><strong>Auto-approve:</strong> bila diaktifkan, booking yang valid dan tidak bentrok
                otomatis disetujui dan ticket/QR langsung tersedia.</em>
              </p>
            </li>

            <!-- Step 3 -->
            <li class="timeline-sm-item">

              <h4 class="mt-0">Persiapan Pengunjung</h4>
              <p>
                Pengunjung menyimpan/ mencetak <strong>ticket (PDF)</strong> yang memuat QR.
                Datang sesuai jadwal. Siapkan <strong>KTP/identitas instansi</strong> dan (bila ada) <strong>Surat Tugas</strong>.
              </p>
              <div class="tip"><i class="mdi mdi-lightbulb-on-outline"></i> Tips: simpan QR di galeri ponsel &amp; unduh PDF agar tetap bisa ditunjukkan meski offline.</div>
            </li>

            <!-- Step 4 -->
            <li class="timeline-sm-item">

              <h4 class="mt-0">Check-in (Hari H)</h4>
              <p>
                Di pos petugas, QR pada ticket di-<strong>scan</strong>.
                Petugas memilih mode <strong>Check-in</strong>, mengarahkan kamera ke QR (atau memakai <em>barcode gun</em> /
                input <strong>kode booking</strong> sebagai fallback). Sistem menandai <strong>Checked-in</strong> dan merekam <strong>waktu &amp; petugas</strong>.
              </p>
            </li>

            <!-- Step 5 -->
            <li class="timeline-sm-item">

              <h4 class="mt-0">Proses Kunjungan & Dokumentasi</h4>
              <p>
                Tamu dilayani oleh <strong>Unit Tujuan</strong> sesuai keperluan. Dari halaman <strong>Detail Booking</strong>,
                petugas dapat menyalakan kamera untuk <strong>ambil/unggah foto dokumentasi</strong> lalu <strong>Simpan Foto</strong>.
                Foto terhubung ke data booking sebagai bukti kehadiran.
              </p>
            </li>

            <!-- Step 6 -->
            <li class="timeline-sm-item">

              <h4 class="mt-0">Checkout</h4>
              <p>
                Saat tamu selesai, lakukan pemindaian ulang pada mode <strong>Checkout</strong>.
                Sistem mencatat <strong>waktu checkout</strong> dan menghitung <strong>durasi kunjungan</strong>.
                Status berubah menjadi <strong>Checked-out</strong>.
              </p>
            </li>

            <!-- Step 7 -->
            <li class="timeline-sm-item">

              <h4 class="mt-0">Selesai &amp; Arsip</h4>
              <p>
                Semua data (identitas, unit tujuan, waktu check-in/out, petugas, foto, dokumen) tersimpan sebagai arsip.
                Ticket/QR &amp; Surat Pernyataan (PDF) tetap bisa dilihat/diunduh dari halaman detail booking.
              </p>
            </li>

            <!-- Step 8 -->
            <li class="timeline-sm-item">

              <h4 class="mt-0">Statistik &amp; Evaluasi</h4>
              <p>
                Dashboard menampilkan <strong>Tren Kunjungan</strong> (harian/mingguan/bulanan), <strong>Asal Instansi Top 5</strong>,
                dan <strong>Unit Teramai</strong>. Angka berbasis <strong>check-in</strong> (real hadir).
                Gunakan untuk penjadwalan, alokasi petugas, dan evaluasi layanan.
              </p>
            </li>
          </ul>

          <div class="alert alert-info mt-3 mb-0">
            <i class="mdi mdi-information-outline mr-1"></i>
            <strong>Tips:</strong> Datang 10 menit lebih awal, pastikan ponsel punya baterai &amp; koneksi,
            simpan kode booking untuk verifikasi cepat, dan ikuti arahan petugas.
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<?php $this->load->view("front_end/footer.php") ?>
<script src="<?= base_url('assets/admin/libs/magnific-popup/jquery.magnific-popup.min.js') ?>"></script>
<script>
  $(function(){
    $(".image-popup").magnificPopup({
      type: "image",
      closeOnContentClick: false,
      closeBtnInside: false,
      mainClass: "mfp-with-zoom mfp-img-mobile",
      image: { verticalFit: true, titleSrc: e => e.el.attr("title") },
      gallery: { enabled: true },
      zoom: { enabled: true, duration: 300, opener: e => e.find("img") }
    });
  });
</script>
