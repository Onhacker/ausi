
<div class="mt-2">
  <?php if ($total_pages > 1): ?>
    
    <ul class="pagination justify-content-center mb-3 ">
      <li class="page-item <?= ($page<=1?'disabled':''); ?>">
        <?php if ($page<=1): ?>
          <span class="page-link" aria-label="Previous" aria-disabled="true">«</span>
          <?php else: ?>
            <a href="#" class="page-link" data-page="<?= $page-1; ?>" aria-label="Previous">«</a>
          <?php endif; ?>
        </li>

        <?php for ($i=1; $i<=$total_pages; $i++): ?>
          <li class="page-item <?= ($i==$page?'active':''); ?>">
            <?php if ($i==$page): ?>
              <span class="page-link" aria-current="page"><?= $i; ?></span>
              <?php else: ?>
                <a href="#" class="page-link" data-page="<?= $i; ?>"><?= $i; ?></a>
              <?php endif; ?>
            </li>
          <?php endfor; ?>

          <li class="page-item <?= ($page>=$total_pages?'disabled':''); ?>">
            <?php if ($page>=$total_pages): ?>
              <span class="page-link" aria-label="Next" aria-disabled="true">»</span>
              <?php else: ?>
                <a href="#" class="page-link" data-page="<?= $page+1; ?>" aria-label="Next">»</a>
              <?php endif; ?>
            </li>
          </ul>
          
        <?php endif; ?>
      </div>
      <style>
        /* Aktif = biru */
        .pagination .page-item.active .page-link,
        .pagination .page-link[aria-current="page"]{
          background-color: #4a81d4; /* blue */
          border-color: #4a81d4;
          color: #fff;
        }

        /* State hover/focus saat aktif */
        .pagination .page-item.active .page-link:hover,
        .pagination .page-item.active .page-link:focus,
        .pagination .page-link[aria-current="page"]:hover,
        .pagination .page-link[aria-current="page"]:focus{
          background-color: #0b5ed7;
          border-color: #0b5ed7;
          color: #fff;
        }
      </style>
