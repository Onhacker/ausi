<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title><?php echo "Link Expired ". $rec->nama_website ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta content="Onhacker.net" name="author" />
	<meta name="robots" content="noindex">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<!-- App favicon -->
	<link rel="shortcut icon" href="<?php echo base_url('assets/admin') ?>/images/logo.png">
	<!-- Sweet Alert-->
	<link href="<?php echo base_url('assets/admin') ?>/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css" />
	<!-- App css -->
	<link href="<?php echo base_url('assets/admin') ?>/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo base_url('assets/admin') ?>/css/icons.min.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo base_url('assets/admin') ?>/css/app.min.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo base_url(); ?>assets/admin/libs/magnific-popup/magnific-popup.css" rel="stylesheet" type="text/css" />

</head>

<body class="authentication1-bg authentication1-bg-pattern">

	<div class="account-pages mt-2 mb-2">
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-md-8 col-lg-6 col-xl-5">
					<div class="text-center w-100 mt-3">
						
							<span><img src="<?php echo base_url('assets/images/').$rec->gambar ?>" alt="<?php echo base_url('assets/images/').$rec->gambar ?>" height="50"></span>
						
						<h1 class="mb-2 mt-3" style="font-weight: bold;">LINK KADALUARSA</h1>
					</div>


				</div> 
			</div>
		</div>
		<!-- end container -->
	</div>
	<!-- end page -->

	<footer class="footer footer-alt">
		<div class="row mt-3 mb-2">
			<div class="col-12 text-center">
				<button type="button" onclick="home()" class="btn btn-success btn-rounded waves-effect waves-light mb-2 mt-4">Kembali ke Home</button>
			</div> <!-- end col -->
		</div>

		<?php echo date("Y") ?> &copy;  <a href="<?php echo $rec->url ?>" class="text-black-50"><?php echo $rec->nama_website ?></a> 
	</footer> 
	<script src="<?php echo base_url("assets/admin") ?>/js/jquery-3.1.1.min.js"></script>

	<!-- Vendor js -->
	<script src="<?php echo base_url('assets/admin') ?>/js/vendor.min.js"></script>
	<!-- Sweet Alerts js -->
	<script src="<?php echo base_url('assets/admin') ?>/libs/sweetalert2/sweetalert2.min.js"></script>

	<!-- App js -->
	<script src="<?php echo base_url('assets/admin') ?>/js/app.min.js"></script>
	<!-- jqyery form-->
	<script type="text/javascript" src="<?php echo base_url(); ?>assets/admin/js/jquery.easyui.min.js"></script>
	<script src="<?php echo base_url('assets/admin') ?>/js/jquery.form.js"></script>
	<script src="<?php echo base_url("assets/admin") ?>/libs/magnific-popup/jquery.magnific-popup.min.js"></script>
	<script src="<?php echo base_url("assets/admin") ?>/js/pages/gallery.init.js"></script>
</body>
<script type="text/javascript">
	function home(){
		window.location.href = "<?php echo site_url() ?>";
	}
</script>

</html>