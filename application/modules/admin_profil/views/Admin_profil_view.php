<link href="<?php echo base_url("assets/admin") ?>/libs/dropify/dropify.min.css" rel="stylesheet" type="text/css" />
<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
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
        <div class="col-lg-4 col-xl-4"> 
            
             <form id="form_app" method="post"  enctype="multipart/form-data" >
            <div class="card-box text-center">

               <input type="file" name="foto" id="foto" onchange="return validasiFile()" class="dropify"
               data-default-file="<?php echo base_url('upload/users/') . (!empty($record->foto) ? $record->foto : 'onhacker_221a3f5e.jpg'); ?>" />


                 <div class="d-flex justify-content-center">
                    <div class="spinner-border avatar-lg text-primary m-2" role="status" id="loader" style="display: none;"></div>
                </div>
                <div id="bg-pr">
                   
                         <h4 class="mb-0" id="nama"></h4>
                   
                    
                     <p class="text-black"> Username : <code id="lev"></code></p>

                    <div class="text-left mt-3">
                        <p class="text-black mb-2 font-13"><strong>Hp :</strong><span class="ml-2" id="telp"></span></p>
                       <p id="unit_row" class="text-black mb-2 font-13">
  <strong>Unit :</strong> <span class="ml-2" id="unit"></span>
</p>

                        <p class="text-black mb-2 font-13"><strong>Tanggal Reg :</strong> <span class="ml-2 " id="tanggal_reg"></span></p>
                    </div>
                </div>
            </div> 
        </div>
        <div class="col-lg-8 col-xl-8">
            <div class="card-box">
                <ul class="nav nav-pills navtab-bg nav-justified">


                    <li class="nav-item">
                        <a href="#settings" data-toggle="tab" aria-expanded="false" class="nav-link active">
                            Settings
                        </a>
                    </li>
                     <li class="nav-item">
                        <a href="#password" data-toggle="tab" aria-expanded="false" class="nav-link">
                            Ganti Password
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane show active" id="settings" >
                         <h5 class="mb-3 text-uppercase bg-light p-2"><i class="mdi mdi-lock"></i> Akun</h5>
                         <div class="row">
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="userpassword">No Whatsapp</label>
                                    <input type="text" class="form-control" name="no_telp" value="<?php echo (isset($record->no_telp))?$record->no_telp:"";  ?>">
                                </div>
                            </div>

                           

                        </div>
                        <h5 class="mb-3 text-uppercase bg-light p-2"><i class="mdi mdi-account-circle mr-1"></i> Pejabat</h5>
                        <div class="row">

                             <div class="col-md-12">
                                <div class="form-group">
                                    <label for="firstname">Nama Lengkap/Pengelola</label>
                                    <input type="text" class="form-control" id="firstname" name="nama_lengkap" value="<?php echo (isset($record->nama_lengkap))?$record->nama_lengkap:"";  ?>">
                                </div>
                            </div>

                        </div> 
                     

                        <div class="row">
                        <div class="col-12">
                            <a href="#load_profil" onclick="update()" id="btn-login" class="btn btn-success btn-block">Update</a>
                        </div>
                       <!--  <div class="col-6">
                            <button type="reset" onclick="home()" class="btn btn-block  btn-secondary waves-effect m-l-5">Batal
                            </button>
                        </div> -->
                    </div>
                </form>
            </div>


            <div class="tab-pane" id="password" >
               <form id="form_pass" method="post" >
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="firstname">Password Lama</label>
                            <input type="password" class="form-control" id="password_lama" name="password_lama" ?>
                        </div>
                    </div>
                     <div class="col-md-12">
                        <div class="form-group">
                            <label for="firstname">Password Baru</label>
                            <input type="password" class="form-control" id="password_baru" name="password_baru" ?>
                        </div>
                    </div>
                     <div class="col-md-12">
                        <div class="form-group">
                            <label for="firstname">Password Baru Lagi</label>
                            <input type="password" class="form-control" id="password_baru_lagi" name="password_baru_lagi" ?>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input form-checkbox" id="customCheck12">
                                <label class="custom-control-label text-danger" for="customCheck12">Tampilkan Password</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                            <div class="form-group">
                              <div class="custom-control custom-checkbox">
                                <input type="checkbox" value="out" name="out" class="custom-control-input" id="customCheck11">
                                <label class="custom-control-label" for="customCheck11">Keluarkan dari perangkat lain (Tindakan ini akan mengeluarkan akun anda dari perangkat lain yang terhubung)</label>
                            </div>
                        </div>
                    </div>

                  

                </div> <!-- end row -->
                <div class="row">
                    <div class="col-6">
                        <a href="javascript:;" onclick="update_pass()" id="btn-login" class="btn btn-success btn-block">Ganti Password</a>
                    </div>
                    <div class="col-6">
                        <button type="reset" onclick="home()" class="btn btn-block  btn-secondary waves-effect m-l-5">Batal
                        </button>
                    </div>
                </div>
            </form>
        </div>



        </div>
    </div> 
</div>
</div>


<?php $this->load->view($controller."_js"); ?>



   


    
</div>

