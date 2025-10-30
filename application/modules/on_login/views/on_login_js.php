<script type="text/javascript">
	$(document).ready(function() {
		const slider = document.querySelector('.slider');

		slider.addEventListener('mousedown', function () {
			slider.classList.add('dragging');
		});

		document.addEventListener('mouseup', function () {
			slider.classList.remove('dragging');
		});

		slider.addEventListener('touchstart', function () {
			slider.classList.add('dragging');
		});
		document.addEventListener('touchend', function () {
			slider.classList.remove('dragging');
		});

		const isLoggedIn = <?= json_encode($this->session->userdata("admin_login") == true) ?>;
		if (isLoggedIn) {
			window.location.href = "<?= base_url('admin_profil/detail_profil') ?>?r=" + Date.now();
		}
		$("#btn-loader").hide();
		$("#btn-loader-reset").hide();
		$('form').bind("keypress", function(e) {
			if (e.keyCode == 13) {               
				e.preventDefault();
				return false;
			}
		});
	});
	
	window.addEventListener('load', () => {
		document.getElementById("bg-video").play();
	});


	function togglePassword() {
		const input = document.getElementById("kode");
		const icon = document.getElementById("eyeIcon");

		if (input.type === "password") {
		    input.type = "text";
		    icon.innerText = "🙈"; 
		  } else {
		    input.type = "password";
		    icon.innerText = "👁️"; 
		  }
	}

	function resetSliderCaptcha() {
		if (sliderInstance && typeof sliderInstance.reset === 'function') {
			sliderInstance.reset();
		}
		document.getElementById('captcha').value = 'false';
		document.getElementById('btn-login').disabled = true;
	}


	function reloadCaptchaSlider() {
        sliderInstance.reset(); 
        document.getElementById('captcha').value = 'false';
    }

    function go_login() {
        var isCaptchaVerified = document.getElementById('captcha').value === 'true';
        if (!isCaptchaVerified) {
            Swal.fire({
                title: 'Captcha belum terverifikasi',
                text: 'Silakan selesaikan captcha terlebih dahulu.',
                icon: 'warning',
                confirmButtonText: 'OK',
                customClass: { confirmButton: 'btn btn-danger mt-2' },
                buttonsStyling: false
            });
            reloadCaptchaSlider();
            return false;
        }

        const sliderIcon = document.querySelector(".slider .sliderIcon");
        if (sliderIcon) {
        	sliderIcon.className = "spinner-border spinner-border-sm text-white";
        }

        $.ajax({
            url: '<?= site_url("on_login/ceklogin"); ?>',
            method: 'POST',
            data: $('#frm').serialize(),
         	dataType: 'json',
            success: function(obj) {
			    if (obj.success === false) {
			    	restoreSliderIcon();
			        Swal.fire({
			            title: obj.title,
			            text: obj.pesan,
			            icon: obj.type || 'error',
			            confirmButtonText: 'OK',
			            customClass: { confirmButton: 'btn btn-danger mt-2' },
			            buttonsStyling: false
			        });
			        reloadCaptchaSlider();
			    } else {
			        location.href = '<?= site_url("on_login/reload"); ?>';
			    }
			},
            error: function() {
            	restoreSliderIcon();
                Swal.fire({
                    title: 'Error',
                    text: 'Gagal terhubung ke server',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    customClass: { confirmButton: 'btn btn-danger mt-2' },
                    buttonsStyling: false
                });
                reloadCaptchaSlider();

            }
        });

        return false;
    }

function restoreSliderIcon() {
    const sliderIcon = document.querySelector(".slider .spinner-border");
    if (sliderIcon) {
        sliderIcon.className = "fa fa-arrow-right sliderIcon";
    }
}


function reloadCaptcha() {
	fetch('<?= base_url("on_login/api_reload_captcha") ?>')
	.then(res => res.json())
	.then(data => {
		if (data.status && data.kode) {
			document.getElementById('Capctha').textContent = data.kode;
			document.getElementById('captcha').value = '';
		} else {
			alert('Gagal memuat captcha baru.');
		}
	})
	.catch(() => alert('Gagal terhubung ke server.'));
}

function reset_password(){
	$("#btn-login-reset").hide();
	$("#btn-loader-reset").show();
	$('#reset').form('submit',{
		url: '<?php echo site_url("kmzwa8awaa/reset_password_user"); ?>',				 
		success: function(result){
			console.log(result);
			result = result.replace(/\s+/g, " ");
			obj = $.parseJSON(result);
			console.log(obj.pesan);
			if (obj.success == false ){
				Swal.fire({
					title: obj.title,   
					html: obj.pesan,
					icon: obj.type,   
					confirmButtonClass: "btn btn-confirm mt-2",

				})
				$("#btn-login-reset").show();
				$("#btn-loader-reset").hide();

			} else {
				$("#con-close-modal").modal("hide");
				$("#btn-login-reset").show();
				$("#btn-loader-reset").hide();
				$("#email").val("");
				Swal.fire({
					title: obj.title,   
					html: obj.pesan,
					icon: obj.type,   
					confirmButtonClass: "btn btn-confirm mt-2",

				})
			}
		}
	});
	return false;
}

</script>