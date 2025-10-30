<link href="<?php echo base_url(); ?>assets/admin/datatables/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url("assets/admin") ?>/libs/jquery-toast/jquery.toast.min.css" rel="stylesheet" type="text/css" />

<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);"><?php echo $title ?></a></li>
                        <li class="breadcrumb-item active"><?php echo $subtitle ?></li>
                    </ol>
                </div>
                <h4 class="page-title"><?php echo $subtitle ?> </h4>
            </div>
        </div>
    </div>     

    <div class="row">
    	<div class="col-lg-4 col-xl-4">
    		<div class="card-box text-center">
               <!--  <?php if ($record->tanggal_reg == "0000-00-00"){ ?>
                    <img src="<?php echo base_url('upload/users/onhacker_221a3f5e.jpg') ?>" class="rounded-circle avatar-lg img-thumbnail" alt="profile-image">
                    <h4 class="mb-0">Akun ini menunggu verifikasi email agar bisa aktif melalui <?php echo $record->email ?></h4>
                    <p></p>
                    <p class="text-black mb-2 font-13"><strong>Email :</strong> <span class="ml-2 " id= "mail_ver"><?php echo $record->email ?></span></p>
                    <button type="button" class="btn btn-success btn-xs waves-effect mb-2 waves-light" onclick="kirim_ulang()">Kirim Ulang Email Verifikasi</button>
                <?php } else { ?> -->
                    <?php if (empty($record->foto)) {?>
                        <img src="<?php echo base_url('upload/users/onhacker_221a3f5e.jpg') ?>" class="rounded-circle avatar-lg img-thumbnail" alt="profile-image">
                    <?php } else { ?>
                        <img src="<?php echo base_url('upload/users/').$record->foto ?>" class="rounded-circle avatar-lg img-thumbnail"
                        alt="profile-image">
                    <?php } ?>
                    <h4 class="mb-0"><?php echo $record->nama_lengkap ?></h4>
                    <p class="text-black">Username : <?php echo $record->username ?></p>

                    <div class="text-left mt-3">
                        <p class="text-black mb-2 font-13">
                            <strong>Unit :</strong>
                            <span class="ml-2" id="unit">
                                <?php echo !empty($record->nama_unit) ? $record->nama_unit : '-' ?>
                            </span>
                        </p>

                        <p class="text-black mb-2 font-13">
                            <strong>No. Whatsapp :</strong>
                            <span class="ml-2"><?php echo $record->no_telp ?></span>
                        </p>

                        <p class="text-black mb-1 font-13">
                            <strong>Tanggal Reg :</strong>
                            <span class="ml-2">
                                <?php echo ($record->tanggal_reg && $record->tanggal_reg !== "0000-00-00")
                                ? tgl_indo($record->tanggal_reg)
                                : '<span class="badge badge-warning p-1">Pending/Belum verifikasi</span>'; ?>
                            </span>
                        </p>
                    </div>

                    
                <!-- <?php } ?> -->
            </div> <!-- end card-box -->

        </div>

        <div class="col-lg-8 col-xl-8">
        	<div class="card-box">
        		<ul class="nav nav-pills navtab-bg nav-justified">
        			<li class="nav-item">
        				<a href="#aboutme" data-toggle="tab" aria-expanded="false" class="nav-link active">
        					Pengaturan User
        				</a>
        			</li>

        			<!-- <li class="nav-item">
        				<a href="#settings" data-toggle="tab" aria-expanded="false" class="nav-link">
        					Kontrol User
        				</a>
        			</li> -->
        		</ul>
        		<div class="tab-content">
        			<div class="tab-pane show active" id="aboutme">

                        <?php if ($record->tanggal_reg != "0000-00-00"){ ?>
                            <h5 class="mb-3 text-uppercase bg-light p-2"><i class="fe-lock"></i> Password</h5>
                            <button type="button" class="btn btn-danger btn-xs waves-effect mb-2 waves-light" onclick="reset_password()">Reset Password</button><br>
                            <small>Reset Password akan mengirim pesan whatsapp ke pemilik akun untuk melakukan pengaturan ulang password</small>

                            <hr>
                        <?php } ?>

                        <div class="table-responsive">

                            <?php if($record->level == "admin"){?>
                                <h4 class="mb-0">Akun ini adalah level admin dan berhak mengakses semua module didalam system ini</h4>
                                <p></p>
                            <?php } else { ?>

                               <?php if ($record->tanggal_reg == "0000-00-00"){ ?>
                                <h4 class="mb-0">Anda boleh mengelola hak akses tanpa menunggu konfirmasi verifikasi email</h4>
                                <p></p>
                            <?php  } ?>
                            <h5 class="mb-3 text-uppercase bg-light p-2"><i class="fe-user-x"></i> Blokir/Permission</h5>
                            <form id="form_app" method="post"  enctype="multipart/form-data" >
                                <div class="row">
                                    <?php if ($record->tanggal_reg != "0000-00-00"){ ?>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                               <label class="text-primary" for="example-email">Blokir</label>
                                               <?php    
                                               $arr_blokir = array("N"=>"Tidak",
                                                  "Y" => "Ya",);
                                               $blokir = isset($record->blokir)?$record->blokir:"";
                                               echo form_dropdown("blokir",$arr_blokir,$blokir,'class="form-control"') ?>
                                               <small>Blokir user akan menutup akses login user <?php echo $record->nama_lengkap ?></small>
                                           </div>
                                       </div>
                                   <?php  } ?>

                                  <!--  <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="text-primary" for="example-email">Permission Publish</label>
                                        <?php    
                                        $arr_permission_publish = array("N"=>"Tidak",
                                          "Y" => "Ya",);
                                        $permission_publish = isset($record->permission_publish)?$record->permission_publish:"";
                                        echo form_dropdown("permission_publish",$arr_permission_publish,$permission_publish,'class="form-control"') ?>
                                        <small>Aktifkan permission untuk user <?php echo $record->nama_lengkap ?> jika diperbolehkan langsung menerbitkan postingan tanpa melalui verifikasi admin</small>
                                    </div>
                                    </div> -->
                            </div> <!-- end row -->
                            <div class="text-left">
                                <button type="button"  onclick="update_setting_profil()"  class="btn btn-success btn-xs waves-effect waves-light mt-2"><i class="mdi mdi-content-save"></i> Simpan</button>
                            </div>
                            <p></p>

                        </form>
                        <hr>
                        <h5 class="mb-3 text-uppercase bg-light p-2"><i class="fe-user-plus"></i> Hak Akses</h5>
                        <table id="datable_2" class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th width="5%" ><strong>No.</strong>    </th>
                                    <th>Modul</th>
                                    <th>Hak Akses</th>
                                </tr>
                            </thead>
                        </table>
                    <?php } ?>
                </div>
            </div> 

            <div class="tab-pane" id="settings">

                <!-- <h5 class="mb-4 text-uppercase"><i class="mdi mdi-account-circle mr-1"></i> Personal Info</h5> -->
                <div class="row">
                    <div class="col-md-12">
                    </div>

                </div>

            </div> <!-- end row -->

        </div>
        <!-- end settings content-->

    </div> <!-- end tab-content -->
</div> <!-- end card-box-->

</div> <!-- end col -->
</div>

</div>

<?php $this->load->view("Admin_user_profil_js") ?>

