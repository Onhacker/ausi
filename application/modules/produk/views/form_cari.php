<?php
// ====== Prefix (biar gampang kalau mau ganti lagi) ======
$FOOD_PREFIX  = 'Mau mamam ';
$DRINK_PREFIX = "Mau mimmi' ";

// ====== Kumpulkan semua sub + kategorinya (tetap sama) ======
$__subs = [];

if (!empty($sub_kat_for_placeholder)) {
  foreach ($sub_kat_for_placeholder as $r) {
    $sub = trim((string)($r->sub_nama ?? ''));
    $kat = trim((string)($r->kategori_nama ?? ''));
    if ($sub !== '') $__subs[] = ['sub' => $sub, 'kat' => $kat];
  }
} elseif (!empty($kategori_produk_sub)) {
  foreach ($kategori_produk_sub as $r) {
    $sub = trim((string)($r->sub_nama ?? $r->nama ?? ''));
    $kat = trim((string)($r->kategori_nama ?? ''));
    if ($sub !== '') $__subs[] = ['sub' => $sub, 'kat' => $kat];
  }
}

$__lower = function($s){
  return function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s);
};

// Kelompokkan jadi makanan vs minuman
$makanan = []; $minuman = [];
foreach ($__subs as $it) {
  $sub = $it['sub'];
  $kat = $__lower($it['kat'] ?? '');
  $cls = null;

  if ($kat !== '') {
    if (strpos($kat, 'minum') !== false) $cls = 'minuman';
    elseif (strpos($kat, 'makan') !== false) $cls = 'makanan';
  }
  if ($cls === null) {
    $low = $__lower($sub);
    $drinkKw = ['minum','kopi','teh','jus','soda','susu','milk','drink',
                'espresso','latte','matcha','boba','shake','smoothie',
                'americano','lemon','lime','coklat panas','es '];
    foreach ($drinkKw as $kw) { if (strpos($low, $kw) !== false) { $cls = 'minuman'; break; } }
    if ($cls === null) $cls = 'makanan';
  }

  if ($cls === 'minuman') $minuman[$sub] = true; else $makanan[$sub] = true;
}

// Dedup & acak
$makanan = array_keys($makanan); shuffle($makanan);
$minuman = array_keys($minuman); shuffle($minuman);

// Potong nama agar tidak kepanjangan
$cap = function($s){
  $s = preg_replace('/\s+/', ' ', $s);
  return function_exists('mb_substr') ? mb_substr($s, 0, 26, 'UTF-8') : substr($s, 0, 26);
};

// Susun frasa (interleave minum/makan)
$phrases = [];
$imax = max(count($minuman), count($makanan));
for ($i = 0; $i < $imax; $i++) {
  if (isset($minuman[$i])) $phrases[] = $DRINK_PREFIX.$cap($minuman[$i]).'?';
  if (isset($makanan[$i])) $phrases[] = $FOOD_PREFIX.$cap($makanan[$i]).'?';
}

// Batasi agar animasi smooth
$MAX_PHRASES = 12;
if (count($phrases) > $MAX_PHRASES) $phrases = array_slice($phrases, 0, $MAX_PHRASES);

// Fallback kalau kosong
if (count($phrases) < 2) {
  $phrases = [$FOOD_PREFIX.'Makanan?', $DRINK_PREFIX.'Minuman?'];
}
?>
<script>
  // Dipakai JS animasi placeholder
  window.SEARCH_PHRASES = <?= json_encode(array_values($phrases), JSON_UNESCAPED_UNICODE); ?>;
</script>

<div class="filter-search">
  <div class="input-group search-inside">
    <input type="search"
           class="form-control filter-input"
           id="q"
           name="q"
           value="<?= html_escape($q ?? ''); ?>"
           placeholder="Mau mamam apa…"
           aria-label="Cari menu"
           autocomplete="off">

    <!-- Ikon di dalam input -->
    <span class="search-icon" aria-hidden="true">
      <i class="fa fa-search"></i>
    </span>

    <div class="input-group-append">
      <button type="button" id="btn-reset" class="btn btn-danger" aria-label="Bersihkan pencarian">
        <i class="fa fa-times" aria-hidden="true"></i>
      </button>
    </div>
  </div>
</div>


<script>
(function(){
  const input = document.getElementById('q');
  const btnReset = document.getElementById('btn-reset');

  const items = (Array.isArray(window.SEARCH_PHRASES) && window.SEARCH_PHRASES.length >= 2)
                ? window.SEARCH_PHRASES
                : ["Mau mamam Makanan?", "Mau minum Minuman?"];

  const typingSpeed = 60;
  const deletingSpeed = 30;
  const holdTime = 1500;

  let idx = 0, timer = null, running = false;

  function typeText(text, cb){
    let i = 0;
    clearInterval(timer);
    timer = setInterval(() => {
      input.setAttribute('placeholder', text.slice(0, ++i));
      if (i >= text.length) { clearInterval(timer); setTimeout(cb, holdTime); }
    }, typingSpeed);
  }
  function eraseText(cb){
    clearInterval(timer);
    timer = setInterval(() => {
      const cur = input.getAttribute('placeholder') || '';
      const next = cur.slice(0, -1);
      input.setAttribute('placeholder', next);
      if (next.length === 0) { clearInterval(timer); cb(); }
    }, deletingSpeed);
  }
  function cycle(){
    if (!running) return;
    const text = items[idx % items.length];
    typeText(text, () => eraseText(() => { idx++; cycle(); }));
  }
  function shouldRun(){ return document.activeElement !== input && input.value.trim() === ''; }
  function start(){ if (!running) { running = true; cycle(); } }
  function stop(){ running = false; clearInterval(timer); }

  if (shouldRun()) start();
  input.addEventListener('focus', stop, {passive:true});
  input.addEventListener('input', stop, {passive:true});
  input.addEventListener('blur', () => { if (shouldRun()) start(); }, {passive:true});
  if (btnReset) btnReset.addEventListener('click', () => { input.value=''; input.focus(); stop(); input.setAttribute('placeholder',''); }, {passive:true});
  document.addEventListener('visibilitychange', () => { if (document.hidden) stop(); else if (shouldRun()) start(); });
})();
</script>
