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
                <h4 class="page-title"><?php echo $subtitle ?></h4>
            </div>
        </div>
    </div>     

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-primary alert-dismissible bg-primary text-white border-0 fade show" role="alert">
                        Pastikan anda menginstal modul yang resmi diperoleh dari Onhacker. Instalasi modul bajakan dapat menyebabkan sistem dalam web menjadi rusak.
                    </div>
                     <div class="button-list">
                        <button type="button" onclick="add()" class="btn btn-success btn-rounded waves-effect waves-light">
                            <span class="btn-label"><i class="fa fa-plus"></i></span>Install
                        </button>
                        <button type="button" onclick="edit()" class="btn btn-info btn-rounded waves-effect waves-light">
                            <span class="btn-label"><i class="fa fa-edit"></i></span>Edit
                        </button>
                        <button type="button" onclick="hapus_data()" class="btn btn-danger btn-rounded waves-effect waves-light">
                            <span class="btn-label"><i class="fa fa-trash"></i></span>Hapus
                        </button>
                      <!--    <button type="button" onclick="get_modul()" class="btn btn-primary btn-rounded waves-effect waves-light float-right">
                            <span class="btn-label"><i class="fe-terminal"></i></span>Make a modul backup
                            <small>For Developer</small>
                        </button> -->

                    </div>
                  
                    <hr>
                    <table id="datable_1" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th width="5%" class="float-center">
                                    <div class="checkbox checkbox-primary checkbox-single">
                                        <input id="check-all" type="checkbox">
                                        <label></label>
                                    </div>
                                </th>
                                <th width="5%" ><strong>No.</strong>    </th>
                                <th>Modul</th>
                                <th>Link</th>
                                <th>Akses</th>
                                <!-- <th>Aktif</th> -->
                               
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
                        <input type="hidden" name="id_modul" id="id_modul">
                        <div class="form-group mb-3">
                            <label class="text-primary">Nama Modul</label>
                            <input class='form-control' name="nama_modul" type="text" id="nama_modul" placeholder="Nama Modul">
                        </div>
                         <div class="form-group mb-3">
                            <label class="text-primary">Link Seo</label>
                            <input class='form-control' name="static_content" type="text" id="static_content" placeholder="linkseo">
                            <small id="small">contoh : <?php echo site_url() ?><code>linkseoanda</code></small>
                        </div>
                         <div class="row">
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="text-primary">Aktif</label>
                                            <select name="aktif" id="aktif" class="form-control">
                                                <option value="Y">Ya</option>
                                                <option value="N">Tidak</option>
                                                
                                            </select>
                                        </div>
                                    </div> <!-- end col -->
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="text-primary">Hak Aksess</label>
                                             <select name="dada" id="dada" class="form-control">
                                                <option value="user">User</option>
                                                <option value="admin">Admin</option>
                                                
                                            </select>
                                        </div>
                                    </div> <!-- end col -->
                                </div> <!-- end row -->
                            </div> <!-- end col -->
                        </div>
                        <div class="form-group mb-3" id="balabala">
                            <label class="text-primary" id="edit_text">Modul (.zip) : </label>
                            <input type="file" name="link" id="link"  />
                        </div>
                       
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" onclick="close_modal()">Batal</button>
                    <button type="button" onclick="simpan()" class="btn btn-primary waves-effect waves-light">Simpan</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->



    <?php
    $this->load->view("backend/global_css");
    $this->load->view("backend/global_js");
    $this->load->view($controller."_js");
    ?>
</div>

