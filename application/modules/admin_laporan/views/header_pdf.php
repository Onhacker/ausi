<?php
/** @var string $title */
/** @var string $period */
/** @var array  $rows */
/** @var array  $sum */
/** @var callable $idr */
$logo_file   = FCPATH.'assets/images/logo_admin.png';
$web = $this->om->web_me();
?>
<table>
    <tr>
      <td align="center" width="25%">
       <?php if ($logo_file): ?>
        <img style="width: 30px" src="<?= $logo_file ?>" alt="Logo">
      <?php endif; ?>
    </td>
    <td align="center">
     <strong><?php echo strtoupper($web->nama_website) ?></strong>
     <div style="font-size: 9px">Alamat : <?php echo $web->alamat."<br>Telp. ".$web->no_telp ?></div>
   </td>
 </tr>
</table>
<hr>
<h3 style="margin:0; text-align: center;"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h3>
<div style="font-size:10px; text-align: center;"><?= htmlspecialchars($period, ENT_QUOTES, 'UTF-8') ?>
  <br>
</div>