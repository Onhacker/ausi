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
                <h4 class="page-title"><?php echo $subtitle ?></h4>
            </div>
        </div>
    </div>     

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                   <!--  <h4 class="header-title">Input Types</h4>
                    <p class="sub-header">
                        Most common form control, text-based input fields. Includes support for all HTML5 types: <code>text</code>, <code>password</code>, <code>datetime</code>, <code>datetime-local</code>, <code>date</code>, <code>month</code>, <code>time</code>, <code>week</code>, <code>number</code>, <code>email</code>, <code>url</code>, <code>search</code>, <code>tel</code>, and <code>color</code>.
                    </p> -->

                    <div class="row">
                        <div class="col-lg-12">
                            <form id="form_app" method="post"  enctype="multipart/form-data" >
                                    <div class="col-12">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group mb-3">
                                                    <label class="text-primary" for="example-email">Logo</label>
                                                    <input type="file" name="gambar" id="gambar" onchange="return validasiFile()" class="dropify" data-default-file="<?php echo base_url() ?>assets/images/<?php echo(isset($record->gambar))?$record->gambar:""; ?>" />
                                                    <!-- <small class="text-info">Maksimal 1 MB</small> -->
                                                </div>
                                            </div> <!-- end col -->

                                        </div> <!-- end row -->
                                    </div> <!-- end col -->
                                <!-- </div> -->

                                <div class="row">
                                  <div class="col-6">
                                    <a href="javascript:;" onclick="update()" id="btn-login" class="btn btn-primary btn-block">Update</a>
                                </div>
                                <div class="col-6">
                                    <button type="reset" onclick="home()" class="btn btn-block  btn-secondary waves-effect m-l-5">
                                        Batal
                                    </button>
                                </div>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $this->load->view($controller."_js"); ?>



   


    
</div>

