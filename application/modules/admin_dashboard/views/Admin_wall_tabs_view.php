<?php
$base_title = htmlspecialchars($title ?? 'Wallboard', ENT_QUOTES, 'UTF-8');
$base_sub   = htmlspecialchars($subtitle ?? 'Monitor & Dashboard', ENT_QUOTES, 'UTF-8');
?>
<style>
  /* Fullscreen gelap untuk halaman induk */
  html.fs-dark, html.fs-dark body{ background:#0b1220 !important; }
  html.fs-dark .content-page, html.fs-dark .content,
  html.fs-dark .container-fluid, html.fs-dark .page-title-box{ background:transparent !important; }
  html.fs-dark .card{ box-shadow:0 10px 30px rgba(0,0,0,.35); }

  /* Sembunyikan header/breadcrumb di dalam child agar tidak dobel */
  #pane-monitor .page-title-box, #pane-dash .page-title-box { display:none!important; }
</style>

<div class="container-fluid" id="fsContainer">
  <div class="row">
    <div class="col-12">
      <div class="page-title-box">
        <div class="page-title-right">
          <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><?= $base_title ?></li>
            <li class="breadcrumb-item active"><?= $base_sub ?></li>
          </ol>
        </div>
        <h4 class="page-title d-flex align-items-center" style="gap:.5rem;">
          <?= $base_sub ?>
        </h4>
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-between align-items-center mb-2">
    <ul class="nav nav-tabs" id="wallNav" role="tablist">
      <li class="nav-item"><a class="nav-link active" href="#pane-monitor" role="tab">Monitor</a></li>
      <li class="nav-item"><a class="nav-link" href="#pane-dash" role="tab">Dashboard</a></li>
    </ul>
    <button id="btnFSGlobal" class="btn btn-sm btn-outline-dark" type="button" title="Fullscreen">
      <i class="mdi mdi-fullscreen"></i> Fullscreen
    </button>
  </div>

  <div class="tab-content" id="wallContent">
    <div class="tab-pane fade show active" id="pane-monitor" role="tabpanel">
      <?php $this->load->view('_monitor'); ?>
    </div>
    <div class="tab-pane fade" id="pane-dash" role="tabpanel">
      <?php $this->load->view('_dash'); ?>
    </div>
  </div>
</div>

<script>
(function(){
  // Tabs sederhana
  const tabs  = Array.from(document.querySelectorAll('#wallNav .nav-link'));
  const panes = Array.from(document.querySelectorAll('#wallContent .tab-pane'));
  function activate(i){
    tabs.forEach((t,idx)=> t.classList.toggle('active', idx===i));
    panes.forEach((p,idx)=>{
      p.classList.toggle('show', idx===i);
      p.classList.toggle('active', idx===i);
    });
    // Reflow Highcharts setelah tab berubah
    setTimeout(()=>{ if (window.Highcharts) Highcharts.charts.filter(Boolean).forEach(c=>c.reflow && c.reflow()); }, 120);
  }
  tabs.forEach((a,i)=> a.addEventListener('click', e=>{ e.preventDefault(); activate(i); }));

  // Fullscreen seluruh container
  const btnFS = document.getElementById('btnFSGlobal');
  const root  = document.getElementById('fsContainer');
  btnFS.addEventListener('click', async ()=>{
    try{
      if (!document.fullscreenElement){
        await (root.requestFullscreen ? root.requestFullscreen({navigationUI:'hide'}) : document.documentElement.requestFullscreen());
        btnFS.innerHTML = '<i class="mdi mdi-fullscreen-exit"></i> Exit';
      } else {
        await document.exitFullscreen();
        btnFS.innerHTML = '<i class="mdi mdi-fullscreen"></i> Fullscreen';
      }
    }catch(e){}
  });
  document.addEventListener('fullscreenchange', ()=>{
    document.documentElement.classList.toggle('fs-dark', !!document.fullscreenElement);
    setTimeout(()=>{ if (window.Highcharts) Highcharts.charts.filter(Boolean).forEach(c=>c.reflow && c.reflow()); }, 120);
  });

  // Buka tab dari hash (opsional)
  if (location.hash === '#pane-dash') activate(1); else activate(0);
})();
</script>
