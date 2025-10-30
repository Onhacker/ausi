<?php $EMBED = isset($_GET['embed']) && $_GET['embed']=='1'; ?>

<?php defined('BASEPATH') OR exit('No direct script access allowed');
/** @var object $order @var array $items @var object $rec @var string $size */
$size = in_array($size ?? '', ['58','80']) ? $size : '80';
$W = ($size === '58') ? '58mm' : '80mm';

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function tgl_indone($ts){
    if(!$ts) return '';
    $m = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $t = is_numeric($ts)? (int)$ts : strtotime($ts);
    return date('d',$t).' '.$m[(int)date('n',$t)].' '.date('Y',$t).' '.date('H:i',$t);
}
$nomor = $order->nomor ?? ('ORD-'.$order->id);
$meja  = $order->meja_nama ?: $order->meja_kode ?: 'Takeaway';
$waktu = $order->waktu_fmt ?? tgl_indone($order->created_at ?? time());
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Kitchen - <?= e($nomor) ?></title>
<style>
  @page { margin:0; }
  body{ width:<?= $W ?>; margin:0 auto; font:14px/1.25 "Courier New",monospace; color:#000; }
  .brand{text-align:center; font-weight:bold; font-size:16px; margin:6px 0 2px;}
  .muted{color:#000; opacity:.9; font-size:12px; text-align:center;}
  .hr{border-top:1px dashed #000; margin:6px 0;}
  .row{display:flex; justify-content:space-between; gap:6px;}
  .big{font-size:16px; font-weight:bold;}
  .item{display:flex; justify-content:space-between; gap:6px; margin:4px 0;}
  .qty{min-width:36px; text-align:right; font-weight:bold;}
  .name{flex:1; font-weight:bold;}
  .note{font-size:12px; margin-top:6px; white-space:pre-wrap;}
  .actions{margin-top:8px; text-align:center;}
  .no-print{margin-top:8px; text-align:center;}
  @media print { .no-print{display:none!important;} }
</style>
</head>
<body>
  <div class="brand"><?= e($rec->nama_website ?? 'KITCHEN') ?></div>
  <div class="muted">SLIP KITCHEN • <?= e($waktu) ?></div>
  <div class="hr"></div>
  <div class="row">
    <div>No: <b><?= e($nomor) ?></b></div>
    <div>Meja: <b><?= e($meja) ?></b></div>
  </div>
  <?php if (!empty($order->nama)) : ?>
    <div>Pelanggan: <b><?= e($order->nama) ?></b></div>
  <?php endif; ?>
  <div class="hr"></div>

  <?php foreach($items as $it): ?>
    <div class="item">
      <div class="name"><?= e($it->nama ?? '') ?></div>
      <div class="qty">x <?= (int)($it->qty ?? 0) ?></div>
    </div>
  <?php endforeach; ?>

  <?php if (!empty($order->catatan)): ?>
    <div class="hr"></div>
    <div class="note"><b>Catatan:</b> <?= e($order->catatan) ?></div>
  <?php endif; ?>

  <div class="hr"></div>
  <div class="actions muted">Terbit: <?= date('d/m/Y H:i') ?></div>

  <div class="no-print">
    <button onclick="window.print()">Cetak</button>
  </div>
<!-- <script>window.print();</script> -->
<?php if (!$EMBED): ?>
<script>
  // mode normal (kalau user akses langsung): auto print + close window
  (function(){
    try{
      window.print();
      setTimeout(function(){ window.close && window.close(); }, 300);
    }catch(e){}
  })();
</script>
<?php endif; ?>

</body>

</html>
