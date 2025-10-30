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
                     <div class="button-list">
                        <button type="button" onclick="add()" class="btn btn-success btn-rounded waves-effect waves-light">
                            <span class="btn-label"><i class="fa fa-plus"></i></span>Tambah
                        </button>
                        <button type="button" onclick="edit()" class="btn btn-info btn-rounded waves-effect waves-light">
                            <span class="btn-label"><i class="fa fa-edit"></i></span>Edit
                        </button>
                        <button type="button" onclick="hapus_data()" class="btn btn-danger btn-rounded waves-effect waves-light">
                            <span class="btn-label"><i class="fa fa-trash"></i></span>Hapus
                        </button>
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
                                <th>Persyaratan</th>
                                <th>Jenis Permohonan</th>
                                              
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
                    <form id="form_app" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="id">
                            <div class="form-group mb-3">
                               <label class="text-primary">Jenis Permohonan</label>
                               <?php 
                               $permohonan = isset($permohonan)?$permohonan:"";
                               echo form_dropdown("id_permohonan",$this->dm->arr_permohonan(),$permohonan,'id="id_permohonan"  class="form-control" data-toggle="select2"') 
                               ?>
                           </div>

                        <div class="form-group mb-3">
                            <label for="label" class="text-primary">Label</label>
                            <input class="form-control" name="label" type="text" id="label" autocomplete="off">
                        </div>
                        <div class="form-group mb-3">
                            <label for="syarat" class="text-primary">Syarat</label>
                            <input class="form-control" name="syarat" type="text" id="syarat" autocomplete="off">
                        </div>
                        <div class="form-group mb-3">
                            <label for="penjelasan" class="text-primary">Penjelasan</label>
                            <textarea class="form-control" name="penjelasan" id="penjelasan" rows="3"></textarea>
                        </div>
                      <!--   <div class="form-group mb-3">
                            <label for="kode_file" class="text-primary">Kode File</label>
                            <input class="form-control" name="kode_file" type="text" id="kode_file" autocomplete="off">
                            <small>contoh : kk</small>

                        </div> -->
                        <div class="form-group mb-3">
                            <label for="peringatan" class="text-primary">Syarat Wajib/Tidak</label>
                            <select name="peringatan" id="peringatan" class="form-control" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="1">Wajib</option>
                                <option value="0">Tidak Wajib</option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="download" class="text-primary">File Download</label>
                            <input class="form-control" type="file" name="download" id="download">
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
    $this->load->view($controller."_js");
    ?>
  
</div>
