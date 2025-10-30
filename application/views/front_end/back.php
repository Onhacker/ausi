  
    <i class="  ti-arrow-left ausi-btn-back" onclick="ausiBack()"></i>
  
<style type="text/css">
  .ausi-hero-center{
  position: relative;
  text-align: center !important;   /* pastikan title/subtitle center */
  padding: 24px 0 14px;
}
.ausi-btn-back{
  position: absolute;
  left: 0px;                            
  width: 30px; height: 30px;
  display: inline-flex; align-items: center; justify-content: center;
  color: #fff;
  font-weight: 700;
  font-size: 18px;
}
</style>
<script>
  function ausiBack(){
    if (document.referrer && history.length > 1) history.back();
    else window.location.href = "<?= site_url() ?>";
  }
</script>