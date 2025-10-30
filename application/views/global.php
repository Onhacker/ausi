
<style type="text/css">
    #status .image-container img {
        width: 45px;  
        height: auto;  
    }
/*.toast-chat {
  position: fixed;
  top: 5%;
  left: 50%;
  transform: translateX(-50%);
  background-color: #f1556c;
  color: white;
  padding: 12px 20px;
  border-radius: 6px;
  font-size: 14px;
  display: none;
  z-index: 9999;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
  animation: fadeIn 3s ease-in-out;
  display: flex;
  align-items: center;
  gap: 10px;
}

@media screen and (max-width: 767.98px) {
  .toast-chat {
    top: 10%;
  }
}
*/

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>


<script type="text/javascript">
  function logout(){
    Swal.fire({
        title: "Yakin ingin Keluar ?",
        text: "",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Keluar",
        cancelButtonText: "Batal",
        allowOutsideClick: false,
    }).then((result) => {
        if (result.value) {
            sessionStorage.removeItem("admin_dynamic_menu");
            window.location.href = "<?php echo site_url("on_login/logout") ?>";                    
        } 
    })
}
function toggleTopbarDark() {
    const body = document.body;
    if (window.innerWidth <= 767.98) {
        if (!body.classList.contains('topbar-dark')) {
            body.classList.add('topbar-dark');
        }
    } else {
        body.classList.remove('topbar-dark');
    }
}
window.addEventListener('DOMContentLoaded', toggleTopbarDark);
window.addEventListener('resize', toggleTopbarDark);

 // function updateNetworkStatus() {
 //    const toast = document.getElementById("offline-toast");

 //    if (!navigator.onLine) {
 //      toast.textContent = '⚠️ Tidak ada koneksi internet atau jaringan kurang stabil.';
 //      toast.style.display = "flex";
 //      return;
 //    }

 //    const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
 //    if (connection) {
 //      const slowTypes = ['2g', '3g', 'slow-2g'];
 //      if (slowTypes.includes(connection.effectiveType) || connection.rtt > 300) {
 //        toast.textContent = '⚠️ Jaringanmu tergolong lambat atau tidak stabil.';
 //        toast.style.display = "flex";
 //        return;
 //      }
 //    }

 //    toast.style.display = "none";
 //  }

 //  window.addEventListener('load', updateNetworkStatus);
 //  window.addEventListener('online', updateNetworkStatus);
 //  window.addEventListener('offline', updateNetworkStatus);

 //  const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
 //  if (connection) {
 //    connection.addEventListener('change', updateNetworkStatus);
 //  }
  

</script>
<!-- <div id="offline-toast" class="toast-chat">
  ⚠️ Tidak ada koneksi internet atau jaringan kurang stabil.
</div> -->

