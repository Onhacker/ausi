<?php $EMBED = isset($_GET['embed']) && $_GET['embed']=='1'; ?>

<?php defined('BASEPATH') OR exit('No direct script access allowed');
/** @var object $order @var array $items @var object $rec @var string $size */
$size = in_array($size ?? '', ['58','80']) ? $size : '80';
$W = ($size === '58') ? '58mm' : '80mm';

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rp($n){ $n = (int)$n; return 'Rp '.number_format($n,0,',','.'); }
function tgl_indone($ts){
    if(!$ts) return '';
    $m = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $t = is_numeric($ts)? (int)$ts : strtotime($ts);
    return date('d',$t).' '.$m[(int)date('n',$t)].' '.date('Y',$t).' '.date('H:i',$t);
}
$nomor = $order->nomor ?? ('ORD-'.$order->id);
$meja  = $order->meja_nama ?: $order->meja_kode ?: 'Takeaway';
$waktu = $order->waktu_fmt ?? tgl_indone($order->created_at ?? time());
$total = 0; foreach($items as $it){ $total += (int)$it->subtotal ?: ((int)$it->qty*(int)$it->harga); }
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Tempel Meja - <?= e($nomor) ?></title>
<style>
  @page { margin:0; }
  body{ width:<?= $W ?>; margin:0 auto; font:14px/1.25 "Courier New",monospace; color:#000; }
  .brand{text-align:center; font-weight:bold; font-size:16px; margin:6px 0 2px;}
  .muted{color:#000; opacity:.9; font-size:12px; text-align:center;}
  .hr{border-top:1px dashed #000; margin:6px 0;}
  .row{display:flex; justify-content:space-between; gap:6px;}
  .item{display:grid; grid-template-columns: 1fr auto; gap:6px; margin:4px 0;}
  .nm{font-weight:700;}
  .sub{text-align:right; min-width:80px;}
  .line2{display:flex; justify-content:space-between; font-size:12px; opacity:.9;}
  .tot{display:flex; justify-content:space-between; font-weight:bold; font-size:15px;}
  .note{font-size:12px; margin-top:6px; white-space:pre-wrap;}
  .no-print{margin-top:8px; text-align:center;}
  @media print { .no-print{display:none!important;} }
</style>
</head>
<body>
  <div class="brand"><?= e($rec->nama_website ?? '') ?></div>
  <div class="muted">SLIP MEJA • <?= e($waktu) ?></div>
  <div class="hr"></div>
  <div class="row">
    <div>No: <b><?= e($nomor) ?></b></div>
    <div>Meja: <b><?= e($meja) ?></b></div>
  </div>
  <?php if (!empty($order->nama)) : ?>
    <div>Pelanggan: <b><?= e($order->nama) ?></b></div>
  <?php endif; ?>
  <div class="hr"></div>

  <?php foreach($items as $it):
        $qty   = (int)($it->qty ?? 0);
        $harga = (int)($it->harga ?? 0);
        $sub   = (int)($it->subtotal ?? ($qty*$harga));
  ?>
    <div class="item">
      <div>
        <div class="nm"><?= e($it->nama ?? '') ?></div>
        <div class="line2">
          <span><?= $qty ?> x <?= rp($harga) ?></span>
          <span></span>
        </div>
      </div>
      <div class="sub"><?= rp($sub) ?></div>
    </div>
  <?php endforeach; ?>

  <div class="hr"></div>
  <div class="tot"><span>Total</span><span><?= rp($total) ?></span></div>

  <?php if (!empty($order->catatan)): ?>
    <div class="hr"></div>
    <div class="note"><b>Catatan:</b> <?= e($order->catatan) ?></div>
  <?php endif; ?>

  <div class="hr"></div>
  <div class="muted">Terbit: <?= date('d/m/Y H:i') ?></div>

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
