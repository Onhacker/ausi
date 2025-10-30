<?php
$foto = !empty($record->foto) ? $record->foto : 'onhacker_221a3f5e.jpg';
$img  = base_url('upload/users/'.$foto);
$tg   = $record->tanggal_reg ? $record->tanggal_reg : date('Y-m-d');
$tgl  = function_exists('tgl_indo') ? tgl_indo($tg) : date('d-m-Y', strtotime($tg));
?>
<div class="container-fluid">
  <!-- start page title -->
  <div class="row">
    <div class="col-lg-12 col-xl-12"> 
      <div class="page-title-box">
        <div class="page-title-right">
          <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><?php echo $title ?></li>
            <li class="breadcrumb-item active"><?php echo $subtitle ?></li>
          </ol>
        </div>
        <h4 class="page-title"><?php echo $subtitle ?> </h4>
      </div>
    </div>
  </div>     

  <div class="row">
    <div class="col-lg-12 col-xl-12"> 

      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col-md-4 text-center">
              <img src="<?= $img ?>" alt="foto" class="rounded-circle img-thumbnail" style="width:140px;height:140px;object-fit:cover">
              <h5 class="mt-3 mb-0"><?= htmlspecialchars($record->nama_lengkap) ?></h5>
              <small class="text-muted">@<?= htmlspecialchars($record->username) ?></small>
            </div>
            <div class="col-md-8">
              <table class="table table-sm table-borderless mb-0">
                <tr>
                  <th style="width:180px">Level</th>
                  <td><?= htmlspecialchars(ucfirst($record->level)) ?></td>
                </tr>
                <tr>
                  <th style="width:180px">Username</th>
                  <td><?= htmlspecialchars(ucfirst($record->username)) ?></td>
                </tr>
                <tr>
                  <th>Unit</th>
                  <td><?= htmlspecialchars($record->nama_unit ?: '—') ?></td>
                </tr>
                <tr>
                  <th>No. Whatsapp</th>
                  <td><?= htmlspecialchars($record->no_telp ?: '—') ?></td>
                </tr>
                <tr>
                  <th>Email</th>
                  <td><?= htmlspecialchars($record->email ?: '—') ?></td>
                </tr>
                <tr>
                  <th>Tanggal Registrasi</th>
                  <td><?= $tgl ?></td>
                </tr>
                <tr>
                  <th>Status</th>
                  <td>
                    <?php if ($record->blokir === 'Y'): ?>
                      <span class="badge badge-danger">Diblokir</span>
                      <?php elseif ($record->blokir === 'P'): ?>
                        <span class="badge badge-warning">Pending</span>
                        <?php else: ?>
                          <span class="badge badge-success">Aktif</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                    <tr>
                      <th>ID Session</th>
                      <td><code><?= htmlspecialchars((string)$record->id_session) ?></code></td>
                    </tr>
                  </table>

                  <div class="mt-3">
                   <!--  <a href="<?= site_url('admin_profil') ?>" class="btn btn-secondary">
                      <i class="fe-arrow-left"></i> Kembali
                    </a> -->
                    <?php if (strtolower($this->session->userdata('admin_username')) === strtolower($record->username)): ?>
                    <a href="<?= site_url('admin_profil') ?>" class="btn btn-primary ml-2">
                      <i class="fe-edit-2"></i> Edit Profil
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
