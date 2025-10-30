<?php $this->load->view("front_end/head.php") ?>
<div class="container-fluid">
    <div class="row mt-3">
        <div class="col-lg-12">
            <div class="search-result-box m-t-30 card-box">
                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <div class="row">
                            <div class="col-12">
                                <div class="text-center">
                                    <h3 class="mb-0">Tracking <b>Permohonan</b></h3>
                                </div>
                            </div>
                        </div>
                       <style type="text/css">.search-icon{font-size:80px;background-image:linear-gradient(to right,#fbf003 0%,#009819 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;color:transparent;position:absolute;top:60px;left:60%;transform:translateX(-50%);animation:waveZoom 1.5s ease-in-out infinite;transform-origin:center center;z-index:10}@keyframes waveZoom{0%{transform:translateX(-50%) rotateZ(0deg) scale(1)}25%{transform:translateX(-50%) rotateZ(10deg) scale(1.2)}50%{transform:translateX(-50%) rotateZ(0deg) scale(1)}75%{transform:translateX(-50%) rotateZ(-10deg) scale(1.2)}100%{transform:translateX(-50%) rotateZ(0deg) scale(1)}}</style>
                        <div class="pt-3 pb-4">
                            <div class="input-group m-t-10">
                                <input type="text" id="no_tracking" class="form-control" placeholder="No. Tracking">
                                <span class="input-group-append">
                                    <button type="button" id="btn_cari" class="btn waves-effect waves-light btn-primary">
                                        <i class="fa fa-search mr-1"></i> Cari
                                    </button>
                                </span>
                            </div>
                            <div id="loading-spinner" style="display:none; text-align:center;" class="col-md-12 mb-3 mt-2">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                                <p class="mt-2">Memproses permintaan...</p>
                            </div>
                            <div id="hasil_tracking" class="row justify-content-center mt-4 text-dark">
                                <div id="icl" class="row justify-content-center text-dark position-relative">
                                    <i class="fas fa-search search-icon"></i>
                                    <img src="<?php echo base_url("assets/images/track.png") ?>" alt="image" class="img-fluid img-thumbnail" width="200">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view("front_end/footer.php") ?>
<?php $this->load->view("tracking_js"); ?>