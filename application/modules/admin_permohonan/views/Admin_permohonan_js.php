<script type="text/javascript">
    function handleCardClick(id) {
        window.location.href = "<?php echo site_url('admin_permohonan/handle_card_click/') ?>" + id;
    }

    function showToast(el) {
        const toast = el.nextElementSibling;
        toast.style.display = "block";
    }

    function hideToast(el) {
        const toast = el.nextElementSibling;
        toast.style.display = "none";
    }

    
</script>