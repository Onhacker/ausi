<!-- =========================
     CABANG: GRATIS ONGKIR
     (pakai modal & script SAMA
      dengan cabang ongkir biasa)
     ========================= -->

<div class="form-group col-md-6">
  <label for="alamatKirim" class="mb-1">
    Alamat Antar <span class="text-muted">(lengkap ya!)</span>
  </label>

  <textarea
    id="alamatKirim"
    class="form-control shadow-sm"
    name="alamat"
    rows="3"
    required
    minlength="15"
    maxlength="300"
    aria-describedby="alamatTips alamatCount"></textarea>

  <small id="alamatTips" class="form-text text-muted mt-1 d-flex">
    <i class="mdi mdi-lightbulb-on-outline mr-1" aria-hidden="true"></i>
    Tulis: jalan/no, gang, patokan (masjid/sekolah), akses (lewat sungai), dan shareloc.
  </small>

  <!-- Banner info Gratis Ongkir -->
  <div class="mt-2">
    <div class="alert alert-success d-flex align-items-start mb-2" role="alert">
      <i class="mdi mdi-truck-fast mr-2" aria-hidden="true"></i>
      <div>
        <strong>Gratis Ongkir!</strong><br>
        Belanja Anda sudah mencapai minimal
        Rp<?= number_format($batas_free, 0, ',', '.') ?>.<br>
        Silakan pilih titik pengantaran.
      </div>
    </div>
  </div>

  <!-- Tombol pilih lokasi / GPS (SAMA ID-nya dengan cabang berbayar) -->
  <div class="d-flex flex-wrap align-items-center mt-2">
    <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center mr-1" id="btnMapOngkir">
      <i class="mdi mdi-map-marker-outline mr-1" aria-hidden="true"></i> Pilih Lokasi
    </button>

    <button type="button" class="btn btn-warning btn-sm d-inline-flex align-items-center" id="btnUseMyLocation">
      <i class="mdi mdi-crosshairs-gps mr-1" aria-hidden="true"></i> Gunakan Lokasi Saya
    </button>
  </div>
</div>

<!-- HINT STATUS / ALAMAT / JARAK (tanpa nominal ongkir) -->
<small id="ongkirHint" class="text-dark mb-2"></small>

<!-- Hidden input. Penting:
     - free_ongkir = 1 -> bikin __applyOngkirFromMap() masuk mode gratis
     - ongkirInput akan DIISI otomatis oleh __applyOngkirFromMap() sebagai sentinel (1)
       bukan angka rupiah -->
<input type="hidden" name="ongkir"        id="ongkirInput" value="">
<input type="hidden" name="free_ongkir"   value="1">

<input type="hidden" name="dest_lat"      id="dest_lat">
<input type="hidden" name="dest_lng"      id="dest_lng">
<input type="hidden" name="distance_m"    id="distance_m">
<input type="hidden" name="ongkir_token"  id="ongkir_token">
