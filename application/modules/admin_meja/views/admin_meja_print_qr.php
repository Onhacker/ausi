<!doctype html><html><head><meta charset="utf-8">
<title>Cetak QR - <?= html_escape($row->nama) ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  :root{
    --poster:#bd1000; --pad:#f2b233; --ink:#111;
    --size:500px;
    /* compact look → lebih ringan & rapat */
    --radius-outer:20px; --radius-inner:14px;
    --stroke:6px; --marker:56px; --qr-pad:10px;
  }
  *{box-sizing:border-box} html,body{height:100%}
  body{margin:0;font-family:Arial,Helvetica,sans-serif;background:#fff;color:#000;
       -webkit-print-color-adjust:exact;print-color-adjust:exact}

  .poster{
    width:var(--size); aspect-ratio:1/1; margin:12px auto; background:var(--poster);
    border-radius:12px; position:relative; overflow:hidden;
    display:flex; flex-direction:column; align-items:center;
  }

  /* header dipadatkan */
  .title{margin-top:12px;text-align:center;color:#fff;line-height:1.05}
  .title h1{margin:0;font-weight:900;font-size:clamp(28px,7vw,44px);letter-spacing:.3px}
  .title p{margin:6px 0 2px;font-weight:800;font-size:clamp(14px,3.6vw,20px)}

  /* konten utama lebih lebar, QR dominan */
  .stack{position:relative;width:94%;flex:1;display:flex;align-items:center;justify-content:center}
  .pad{
    position:absolute;left:0;right:0;margin:auto;width:100%;height:50%;
    background:var(--pad);border:var(--stroke) solid var(--ink);
    border-radius:calc(var(--radius-outer) + 4px);box-shadow:0 2px 0 rgba(0,0,0,.15) inset;
    transform:translateY(2px);
  }
  .qr-card{
    position:relative;width:80%;height:78%;
    background:#fff;border:var(--stroke) solid var(--ink);border-radius:var(--radius-outer);
    display:flex;align-items:center;justify-content:center;box-shadow:0 6px 0 rgba(0,0,0,.06);
  }
  .qr-inner{position:relative;width:98%;height:94%;border-radius:var(--radius-inner);background:#fff}

  /* marker lebih kecil agar area QR lega */
  .corner{position:absolute;width:var(--marker);height:var(--marker);border-radius:10px}
  .tl{top:14px;left:14px;border-top:6px solid var(--ink);border-left:6px solid var(--ink)}
  .tr{top:14px;right:14px;border-top:6px solid var(--ink);border-right:6px solid var(--ink)}
  .br{bottom:14px;right:14px;border-bottom:6px solid var(--ink);border-right:6px solid var(--ink)}
  .bl{bottom:14px;left:14px;border-bottom:6px solid var(--ink);border-left:6px solid var(--ink)}

  /* QR pas di dalam tanpa melebar */
  .qr-fit{
    position:absolute; inset:var(--qr-pad);
    display:flex;align-items:center;justify-content:center;overflow:hidden;
  }
  .qr-fit img{
    max-width:100%; max-height:100%;
    width:auto; height:auto; aspect-ratio:1/1; display:block; image-rendering:crisp-edges;
  }

  @media print{.no-print{display:none}body{margin:0}.poster{margin:0}@page{margin:0}}
  @media (max-width:760px){
    :root{--size:92vw;--marker:clamp(34px,10vw,56px);--qr-pad:clamp(8px,2.4vw,10px)}
  }
</style>
</head>
<body>
<div class="poster" role="img" aria-label="Order Meja - Scan untuk order">
  <div class="title">
    <h1><?= html_escape(strtoupper($row->nama)) ?></h1>
    <p>SCAN UNTUK ORDER</p>
  </div>

  <div class="stack">
    <div class="pad" aria-hidden="true"></div>
    <div class="qr-card">
      <div class="qr-inner">
        <span class="corner tl" aria-hidden="true"></span>
        <span class="corner tr" aria-hidden="true"></span>
        <span class="corner br" aria-hidden="true"></span>
        <span class="corner bl" aria-hidden="true"></span>
        <div class="qr-fit">
          <img src="<?= $qr_url ?>" alt="QR Order Meja <?= html_escape($row->nama) ?>">
        </div>
      </div>
    </div>
  </div>
</div>

<div class="no-print" style="text-align:center;margin:10px 0 18px">
  <button onclick="window.print()">Print</button>
</div>
</body></html>
