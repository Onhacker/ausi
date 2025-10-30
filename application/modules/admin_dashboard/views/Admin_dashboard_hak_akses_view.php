<script src="<?php echo base_url('assets/admin') ?>/libs/sweetalert2/sweetalert2.min.js"></script>

<?php if (empty($record->email)) {?>
   <script type="text/javascript">
        Swal.fire("Info","Silahkan lengkapi Email anda sebelum melanjutkan", "warning");
   </script>
<?php } elseif (empty($record->no_telp)) {?>
     <script type="text/javascript">
        Swal.fire("Info","Silahkan lengkapi no telepon anda sebelum melanjutkan", "warning");
   </script>
<?php } elseif (empty($record->nama_lengkap)) {?>
     <script type="text/javascript">
        Swal.fire("Info","Silahkan lengkapi nama anda sebelum melanjutkan", "warning");
   </script>
<?php } ?>

<div class="container-fluid">

    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item active"><?php echo $title ?></li>
                    </ol>
                </div>
                <h4 class="page-title"><?php echo $title ?></h4>
            </div>
        </div>
    </div>  
   
    <div class="row">
        <div class="col-lg-4 col-xl-4">
            <div class="card-box text-center">

                <?php if (empty($record->foto)) {?>
                    <img src="<?php echo base_url('upload/users/user-1.jpg') ?>" alt="user-image" cclass="rounded-circle avatar-lg img-thumbnail">
                <?php } else {?>
                    <img style="width: 100px" src="<?php echo base_url('upload/users/'.$record->foto) ?>" alt="user-image" cclass="rounded-circle avatar-lg img-thumbnail" id="foto_profil">
                <?php } ?>

                <h4 class="mb-0"><?php echo $record->nama_lengkap ?></h4>
                <p class="text-muted">username : <?php echo $record->username ?></p>

                <a href="<?php echo site_url("admin_profil") ?>"><button type="button" class="btn btn-success btn-xs waves-effect mb-2 waves-light">Edit Profil</button></a>

                <div class="text-left mt-3">
                 
                    <p class="text-muted mb-2 font-13"><strong>No. Telepon :</strong><span class="ml-2"><?php echo $record->no_telp ?></span></p>

                    <p class="text-muted mb-1 font-13"><strong>Level :</strong> <span class="ml-2"><?php echo $record->level ?></span></p>
                    <p class="text-muted mb-1 font-13"><strong>Tanggal Reg :</strong> <span class="ml-2"><?php echo tgl_indo($record->tanggal_reg) ?></span></p>
                    <?php if ($record->permission_publish == "Y") {
                        $per = "Anda bisa menerbitkan langsung postingan tanpa menunggu verifikas dari admin";
                    } else {
                        $per = "Anda bisa membuat tulisan berdasarkan hak akses namun perlu proses verifikasi dari admin untuk menerbitkan tulisan anda ke publik";
                    } ?>
                    <p class="text-muted mb-1 font-13"><strong>Pengelola <?php echo $this->om->bentuk_admin($this->session->userdata("admin_dusun"),"p")." ".$this->om->identitas()->nama_dusun ?> :</strong> <span class="ml-2"><?php echo $this->om->user()->nama_lengkap ?></span></p>
                </div>     
            </div> 
        </div>

        <div class="col-lg-8 col-xl-8">
            <div class="card-box">
                <ul class="nav nav-pills navtab-bg nav-justified">
                    <li class="nav-item">
                        <a href="#aboutme" data-toggle="tab" aria-expanded="false" class="nav-link active">
                            HAK AKSES
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane show active" id="aboutme">


                        <h5 class="mb-3 mt-4 text-uppercase"><i class="mdi mdi-cards-variant mr-1"></i>
                        Hak Akses</h5>
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>No.</th>
                                        <th>Nama Modul</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $i = 1;
                                    foreach ($mod->result() as $row) :  ?>
                                    <tr>
                                        <td width="5%"><?php echo $i++ ?>.</td>
                                        <td><a href="<?php echo site_url().strtolower($row->link) ?>"><?php echo $row->nama_modul ?></a></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> <!-- end tab-content -->
            </div> <!-- end card-box-->
     
        </div> <!-- end col -->
    </div>

</div>

