<?php $this->load->view("front_end/head.php") ?>

<div class="container-fluid">
  <div class="hero-title" role="banner" aria-label="Judul situs">
    <h1 class="text"><?= $title ?></h1>
            <div class="text-muted"><?php echo $rec->type ?></div>

    <span class="accent" aria-hidden="true"></span>
  </div>
  <div class="row">
    <div class="col-lg-12">
      <div class="card shadow-sm border-0">
        <div class="card-body">
         <!--  <div class="text-center mb-4">
            <h3 class="mb-1 fw-bold">🏢 <?= $title ?></h3>
            <div class="text-muted">Lapas Kelas I Makassar</div>
          </div> -->

          <style>
            ul.tree, ul.tree ul {
              list-style: none;
              margin: 0;
              padding: 0;
            }
            ul.tree ul {
              margin-left: 25px;
              border-left: 2px dashed #ccc;
              padding-left: 18px;
            }
            ul.tree li {
              margin: 6px 0;
              padding: 6px 8px;
              position: relative;
              font-size: 15px;
              transition: all 0.3s ease;
            }
            ul.tree li strong {
              color: black;
              font-weight: 600;
            }
            ul.tree li:hover {
              background: #f8f9fa;
              border-radius: 6px;
            }
            .pejabat {
              font-size: 14px;
              color: #6c757d;
              margin-left: 6px;
            }
            .icon {
              margin-right: 8px;
            }
          </style>
           <link href="<?= base_url('assets/admin/libs/magnific-popup/magnific-popup.css'); ?>" rel="stylesheet" type="text/css" />
            <div class="gal-box">
            <a href="<?php echo base_url("assets/images/struktur_organisasi.webp") ?>" class="image-popup" title="Struktur Organisasi">
              <img src="<?php echo base_url("assets/images/struktur_organisasi.webp") ?>" class="img-fluid" alt="work-thumbnail">
            </a>
            <div class="gall-info">
              <h4 class="font-16 mt-0">Struktur Organisasi</h4>
            </div> 
          </div>
         <?php
          // --- PETA IKON ---
          // Override spesifik per ID unit (opsional)
          $ICON_BY_ID = [
            1  => 'fa-crown text-warning',        // Kepala Lapas
            10 => 'fa-shield-alt text-danger',    // Regu Pengamanan (contoh)
          ];

          // Default ikon berdasarkan parent_id (level 2 dst)
          $ICON_BY_PARENT = [
            2 => 'fa-user-tie text-success',         // Bawahan Kabag TU
            3 => 'fa-shield-alt text-danger',        // Bawahan Ka. KPLP
            4 => 'fa-chalkboard-teacher text-primary', // Bawahan Kabid Pembinaan
            5 => 'fa-briefcase text-info',           // Bawahan Kabid Kegiatan Kerja
            6 => 'fa-shield-alt text-danger',        // Bawahan Kabid Adm. Kamtib
          ];

          // Helper: tentukan kelas ikon dari id/parent_id
          function icon_class_for_node($node, $ICON_BY_ID, $ICON_BY_PARENT){
            // 1) Prioritas: mapping per ID (jika ada)
            if (isset($ICON_BY_ID[$node->id])) return $ICON_BY_ID[$node->id];

            // 2) Root (parent_id null) → beda ikon untuk id=1 vs lainnya
            if (empty($node->parent_id)) {
              return ($node->id == 1) ? 'fa-crown text-warning' : 'fa-building text-primary';
            }

            // 3) Turunan: pakai mapping per parent_id kalau tersedia
            if (isset($ICON_BY_PARENT[$node->parent_id])) return $ICON_BY_PARENT[$node->parent_id];

            // 4) Fallback umum
            return !empty($node->children) ? 'fa-sitemap text-secondary' : 'fa-user-circle text-muted';
          }

          // Renderer pohon
          function render_tree($nodes, $ICON_BY_ID, $ICON_BY_PARENT) { ?>
            <ul class="tree">
              <?php foreach ($nodes as $node): 
                $iconClass = icon_class_for_node($node, $ICON_BY_ID, $ICON_BY_PARENT); ?>
                <li>
                  <i class="fas <?= $iconClass ?> icon"></i>
                  <strong><?= htmlspecialchars($node->nama_unit, ENT_QUOTES) ?></strong>
                  <?php if (!empty($node->nama_pejabat)) : ?>
                    <span class="pejabat">(<?= htmlspecialchars($node->nama_pejabat, ENT_QUOTES) ?>)</span>
                  <?php endif; ?>

                  <?php if (!empty($node->children)) :
                    render_tree($node->children, $ICON_BY_ID, $ICON_BY_PARENT);
                  endif; ?>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php } ?>


         <?php render_tree($tree, $ICON_BY_ID, $ICON_BY_PARENT); ?>


        </div>
      </div>
    </div>
  </div>
</div>

<?php $this->load->view("front_end/footer.php") ?>
<script src="<?= base_url('assets/admin/libs/magnific-popup/jquery.magnific-popup.min.js') ?>"></script>
<script>
  $(function(){
    $(".image-popup").magnificPopup({
      type: "image",
      closeOnContentClick: false,
      closeBtnInside: false,
      mainClass: "mfp-with-zoom mfp-img-mobile",
      image: { verticalFit: true, titleSrc: e => e.el.attr("title") },
      gallery: { enabled: true },
      zoom: { enabled: true, duration: 300, opener: e => e.find("img") }
    });
  });
</script>
