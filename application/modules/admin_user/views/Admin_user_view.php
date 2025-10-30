<link href="<?php echo base_url(); ?>assets/admin/datatables/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url("assets/admin") ?>/libs/jquery-toast/jquery.toast.min.css" rel="stylesheet" type="text/css" />

<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <!-- <li class="breadcrumb-item"><a href="javascript: void(0);"><?php echo $title ?></a></li> -->
                        <li class="breadcrumb-item active"><?php echo $subtitle ?></li>
                    </ol>
                </div>
                <h4 class="page-title"><?php echo $subtitle ?></h4>
            </div>
        </div>
    </div>     

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                     <div class="button-list">
                        <button type="button" onclick="add()" class="btn btn-success btn-rounded btn-sm waves-effect waves-light">
                            <span class="btn-label"><i class="fe-user-plus"></i></span>Invite User
                        </button>
                         <button type="button" onclick="refresh()" class="btn btn-info btn-rounded btn-sm waves-effect waves-light">
                            <span class="btn-label"><i class="fe-refresh-ccw"></i></span>Refresh
                        </button>
                        <button type="button" onclick="hapus_data()" class="btn btn-danger btn-rounded btn-sm waves-effect waves-light">
                            <span class="btn-label"><i class="fa fa-trash"></i></span>Hapus
                        </button>
                    </div>
                    <hr>
                    <table id="datable_1" class="table table-striped table-bordered" style="width:100%">
                      <thead>
                        <tr>
                          <th width="5%" class="text-center">
                            <div class="checkbox checkbox-primary checkbox-single">
                              <input id="check-all" type="checkbox">
                              <label></label>
                            </div>
                          </th>
                          <th width="5%"><strong>No.</strong></th>
                          <th>Foto</th>
                          <th>Username</th>
                          <th>Nama Lengkap</th>
                          <th>Unit</th>
                          <th>No. Whatsapp</th>
                        </tr>
                      </thead>
                    </table>
                </div> <!-- end card body-->
            </div> <!-- end card -->
        </div><!-- end col-->
    </div>
    <!-- end row-->



    <div id="full-width-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true" style="display: none;">
        <div class="modal-dialog  modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="mymodal-title" id="full-width-modalLabel">Modal Heading</h4>
                    <button type="button" class="close" onclick="close_modal()" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <form id="form_app" method="post"  enctype="multipart/form-data" >
                        <!-- <input type="hidden" name="id_dusun" id="id_dusun"> -->
                         
                        <div class="form-group mb-3">
                          <label class="text-primary">Unit</label>
                          <?= form_dropdown("id_unit", $arr_unit, $id_unit, 'id="id_unit" class="form-control"'); ?>
                        </div>

                        <div class="form-group mb-3">
                          <label class="text-primary">Nama Lengkap</label>
                          <input class="form-control" name="nama_lengkap" type="text" id="nama_lengkap"
                                 value="<?= htmlspecialchars($nama_lengkap, ENT_QUOTES, 'UTF-8') ?>" autocomplete="off" required>
                        </div>

                        <div class="form-group mb-3">
                          <label class="text-primary">Nomor Whatsapp</label>
                          <input class="form-control" name="no_telp" type="text" id="no_telp"
                                 value="<?= htmlspecialchars($no_telp, ENT_QUOTES, 'UTF-8') ?>" autocomplete="off"
                                 placeholder="08xxxxxxxxxx" pattern="^\+?\d{9,16}$" title="Isi angka 9–16 digit, boleh diawali +">
                        </div>

                        <div class="form-group mb-3">
                          <label class="text-primary">Username</label>
                          <input class="form-control" name="username" type="text" id="username"
                                 value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>" autocomplete="off" required>
                        </div>

                       <!--  <div class="form-group mb-3">
                          <label class="text-primary">Password Baru</label>
                          <div class="input-group">
                            <input type="password" class="form-control" id="password_baru" name="password_baru" minlength="6" autocomplete="new-password">
                            <div class="input-group-append">
                              <button class="btn btn-outline-secondary" type="button" id="togglePwd1"><i class="mdi mdi-eye"></i></button>
                            </div>
                          </div>
                          <small class="text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                        </div>

                        <div class="form-group mb-3">
                          <label class="text-primary">Konfirmasi Password Baru</label>
                          <div class="input-group">
                            <input type="password" class="form-control" id="password_baru_lagi" name="password_baru_lagi" minlength="6" autocomplete="new-password">
                            <div class="input-group-append">
                              <button class="btn btn-outline-secondary" type="button" id="togglePwd2"><i class="mdi mdi-eye"></i></button>
                            </div>
                          </div>
                          <small id="pwdHelp" class="form-text"></small>
                        </div> -->

                      <div class="alert alert-info">
  Password akan dibuat otomatis dan dikirim ke WhatsApp yang diisi.
</div>
                </div>
                <div class="modal-footer">
                   
                        <button type="button" class="btn btn-secondary waves-effect" onclick="close_modal()">Batal</button>
                     
                        <button type="button" onclick="simpan()" class="btn btn-primary waves-effect waves-light">Invite</button>
                  
                </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->



    <?php
    $this->load->view("backend/global_css");
    $this->load->view($controller."_js");
    ?>
</div>

