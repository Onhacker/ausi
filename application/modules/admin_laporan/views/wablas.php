<link href="<?= base_url('assets/admin/datatables/css/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet" type="text/css"/>

<style>
.badge-status{
  font-size: .75rem;
  font-weight: 600;
  padding: .35rem .5rem;
  border-radius: .5rem;
}
.badge-sent     { background:#e0f2fe; color:#0369a1; }   /* biru muda */
.badge-pending  { background:#fff7ed; color:#9a3412; }   /* oranye */
.badge-failed,
.badge-error    { background:#fee2e2; color:#991b1b; }   /* merah */
.badge-delivered{ background:#ecfdf5; color:#065f46; }   /* hijau tua */
.badge-read     { background:#dcfce7; color:#065f46; }   /* hijau muda */
.badge-unknown  { background:#f4f4f5; color:#3f3f46; }   /* abu */
</style>

<div class="container-fluid">
  <div class="row"><div class="col-12">
    <div class="page-title-box">
      <div class="page-title-right">
        <ol class="breadcrumb m-0">
          <li class="breadcrumb-item active"><?= $subtitle; ?></li>
        </ol>
      </div>
      <h4 class="page-title"><?= $title; ?></h4>
    </div>
  </div></div>

  <?php
  // ============ CALL API WABLAS ============

  $curl = curl_init();
  $token      = $web->wa_api_token;
  $secret_key = $web->wa_api_secret;

  // optional filter, kalau kosong pakai default hari ini dari wablas
  $page       = "";   // misal "1"
  $limit      = "";   // misal "100"
  $message_id = "";   // kalau mau fetch spesifik id

  curl_setopt($curl, CURLOPT_HTTPHEADER, array(
      "Authorization: $token.$secret_key",
  ));
  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt(
    $curl,
    CURLOPT_URL,
    "https://deu.wablas.com/api/report-realtime?page=$page&message_id=$message_id&limit=$limit"
  );
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

  $result = curl_exec($curl);
  $curlErr = curl_error($curl);
  curl_close($curl);

  // decode
  $data = json_decode($result, true);

  // fallback aman
  $apiOk   = false;
  $apiMsg  = '';
  $rows    = [];

  if (is_array($data)) {
    $apiOk  = !empty($data['status']);
    $apiMsg = isset($data['message']) && !is_array($data['message'])
                ? $data['message']
                : '';

    // kalau 'message' berisi array daftar chat (sesuai contoh kamu)
    if (isset($data['message']) && is_array($data['message'])) {
      $rows = $data['message'];
    }

    // beberapa implementasi wablas pakai key 'data' bukan 'message'
    if (empty($rows) && isset($data['data']) && is_array($data['data'])) {
      $rows = $data['data'];
    }
  }

  // helper badge status
  function render_status_badge($statusRaw){
      $s = strtolower(trim((string)$statusRaw));
      $label = strtoupper($s);

      $cls = 'badge-unknown';
      if ($s === 'sent')        $cls = 'badge-sent';
      elseif ($s === 'pending') $cls = 'badge-pending';
      elseif ($s === 'delivered') $cls = 'badge-delivered';
      elseif ($s === 'read')      $cls = 'badge-read';
      elseif ($s === 'failed' || $s === 'error') $cls = 'badge-failed';

      return '<span class="badge-status '.$cls.'">'.$label.'</span>';
  }

  // helper aman text
  function esc($str){
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
  }

  // kadang file di response adalah array { url: "...", ... }
  function render_file_col($file){
    if (!$file) return '-';
    // kalau string langsung
    if (is_string($file)) {
      return '<a href="'.esc($file).'" target="_blank" rel="noopener">lihat file</a>';
    }
    // kalau array
    if (is_array($file) && isset($file['url'])) {
      return '<a href="'.esc($file['url']).'" target="_blank" rel="noopener">lihat file</a>';
    }
    return '<span class="text-muted">ada lampiran</span>';
  }

  ?>

  <!-- Alert status API di atas tabel -->
  <div class="row mb-2">
    <div class="col-12">
      <?php if (!$apiOk): ?>
        <div class="alert alert-danger" role="alert" style="margin-bottom:1rem;">
          <strong>Gagal ambil data Wablas.</strong><br>
          <?= $curlErr ? esc($curlErr) : 'Response tidak valid'; ?>
        </div>
      <?php else: ?>
        <div class="alert alert-info" role="alert" style="margin-bottom:1rem;">
          <div><strong>Realtime WA Log</strong> (<?= esc($apiMsg ?: 'today only'); ?>)</div>
          <small class="text-muted">
            Device: <?= esc($data['device_id'] ?? '-') ?> /
            Page: <?= esc($data['page'] ?? '-') ?> /
            Total data: <?= esc($data['totalData'] ?? count($rows)) ?>
          </small>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- TABLE -->
  <div class="row">
    <div class="col-12">

<div class="card mb-3">
  <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover nowrap" id="tbl-wa-log" style="width:100%;">
          <thead class="thead-light">
            <tr>
              <th>#</th>
              <th>Waktu</th>
              <th>Dari</th>
              <th>Ke</th>
              <th>Pesan / Caption</th>
              <th>Lampiran</th>
              <th>Status</th>
              <th>Kategori</th>
              <th>Tipe</th>
              <th>ID Msg</th>
            </tr>
          </thead>
          <tbody>
          <?php
          $no = 1;
          if (!empty($rows) && is_array($rows)):
            foreach($rows as $row):

              // struktur sample:
              // $row['id']
              // $row['phone']['from']
              // $row['phone']['to']
              // $row['message']
              // $row['file']
              // $row['status']   (pending/sent/etc)
              // $row['category'] (text/image/file/etc)
              // $row['type']     ("agent", dsb)
              // $row['date']['created_at']
              // $row['date']['updated_at']

              $created = $row['date']['created_at'] ?? '-';
              $from    = $row['phone']['from'] ?? '-';
              $to      = $row['phone']['to']   ?? '-';
              $msg     = $row['message'] ?? '';
              $cat     = $row['category'] ?? '';
              $typ     = $row['type'] ?? '';
              $st      = $row['status'] ?? '';
              $file    = $row['file'] ?? null;

              // kecilkan teks panjang biar tabel nggak jebol
              $msg_short = mb_strlen($msg) > 120
                  ? mb_substr($msg,0,120).'…'
                  : $msg;
              ?>
              <tr>
                <td><?= $no++; ?></td>
                <td>
                  <div><?= esc($created); ?></div>
                  <div class="text-muted" style="font-size:.75rem;">
                    <?= esc($row['date']['updated_at'] ?? ''); ?>
                  </div>
                </td>
                <td><code><?= esc($from); ?></code></td>
                <td><code><?= esc($to); ?></code></td>
                <td style="max-width:260px;white-space:normal;word-break:break-word;">
                  <?= nl2br(esc($msg_short)); ?>
                </td>
                <td><?= render_file_col($file); ?></td>
                <td><?= render_status_badge($st); ?></td>
                <td><?= esc($cat); ?></td>
                <td><?= esc($typ); ?></td>
                <td style="font-size:.75rem;color:#6c757d;"><?= esc($row['id'] ?? ''); ?></td>
              </tr>
              <?php
            endforeach;
          endif;
          ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</div>

</div><!-- /.container-fluid -->

<!-- JS DataTables -->
<script src="<?= base_url('assets/admin/datatables/js/jquery.dataTables.min.js'); ?>"></script>
<script src="<?= base_url('assets/admin/datatables/js/dataTables.bootstrap4.min.js'); ?>"></script>

<script>
(function(){
  // inisialisasi DataTable
  $('#tbl-wa-log').DataTable({
    pageLength: 25,
    order: [[1,'desc']], // sort waktu terbaru di atas
    columnDefs: [
      { targets: [0], orderable:false, searchable:false }, // kolom nomor urut
      { targets: [5], orderable:false }, // lampiran
    ]
  });
})();
</script>
