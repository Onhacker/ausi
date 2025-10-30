<?php $this->load->view("front_end/head.php") ?>
<script src="<?php echo base_url('assets/admin/js/jquery-3.1.1.min.js'); ?>"></script>
<?php $this->load->view("template_permohonan.php") ?>
<?php $this->load->view("global_js.php") ?>
<style type="text/css">
    #modal_syarat .modal-body {
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 10px; 
    }

    .table th {
        background-color: #343a40;
        color: white;
        font-weight: bold;
    }

    .table td {
        font-size: 14px;
    }

    .table-hover tbody tr:hover {
        background-color: #f1f1f1;
    }

    .modal-content {
        border-radius: 8px; 
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); 
    }
    .alert {
        margin: 0rem 0rem;
        margin-bottom: 1rem;
    }

</style>
<div class="modal fade" id="modal_syarat" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-soft-primary">
                <h5 class="modal-title" id="judul_modal">Syarat Permohonan</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Memuat data...</p>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="exportSyaratPDF()">
                    <i class="fa fa-file-pdf-o mr-1"></i> Export PDF
                </button>

                <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">
                    <i class="fa fa-times mr-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view("front_end/footer.php") ?>
<?php $this->load->view("hal_js"); ?>