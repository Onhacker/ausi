/* Ping + Notif + Auto Reload (POS & Billiard) — v3.3 (external) */
(function () {
  if (window.__POS_BILL_PINGER_V33__) return;
  window.__POS_BILL_PINGER_V33__ = true;

  const CFG = window.PING_POS_BIL_CFG || {};
  const PING_POS_URL = CFG.pingPosUrl || '';
  const PING_BIL_URL = CFG.pingBilUrl || '';
  const LS_KEY = CFG.lsKey || 'admin_sound_enabled';

  const SOUND_POS_URL = CFG.soundPosUrl || '';
  const SOUND_BIL_URL = CFG.soundBilUrl || '';

  const POS_SOURCES = [
    SOUND_POS_URL,
    SOUND_POS_URL ? SOUND_POS_URL.replace(/\.wav(\?.*)?$/i, '.mp3$1') : ''
  ].filter(Boolean);

  const BIL_SOURCES = [
    SOUND_BIL_URL,
    SOUND_BIL_URL ? SOUND_BIL_URL.replace(/\.wav(\?.*)?$/i, '.mp3$1') : ''
  ].filter(Boolean);

  const LOG = (msg, ...rest) => (window.PING_DEBUG ? console.log('[PING]', msg, ...rest) : void 0);
  const now = () => Date.now();

  function createAudioWithFallback(urls) {
    const a = new Audio();
    a.preload = 'auto';
    a.muted = false;
    a.volume = 1.0;
    a.crossOrigin = 'anonymous';
    let i = 0;

    function setSrc(idx) { a.src = urls[idx]; try { a.load(); } catch (_) {} }
    a.addEventListener('error', function () {
      if (i + 1 < urls.length) { i++; setSrc(i); }
    });
    if (urls.length) setSrc(i);
    return a;
  }

  const audioPos = window.__audioPos__ || createAudioWithFallback(POS_SOURCES);
  const audioBil = window.__audioBil__ || createAudioWithFallback(BIL_SOURCES);
  window.__audioPos__ = audioPos;
  window.__audioBil__ = audioBil;

  let audioCtx = null;
  function ensureAudioCtx() {
    if (audioCtx) return audioCtx;
    const Ctx = window.AudioContext || window.webkitAudioContext;
    if (!Ctx) return null;
    audioCtx = new Ctx();
    return audioCtx;
  }
  async function webAudioBeep(durationMs = 250, freq = 880) {
    const ctx = ensureAudioCtx();
    if (!ctx) return;
    if (ctx.state === 'suspended') { try { await ctx.resume(); } catch (_) {} }
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.type = 'sine';
    osc.frequency.setValueAtTime(freq, ctx.currentTime);
    gain.gain.setValueAtTime(0.001, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.2, ctx.currentTime + 0.02);
    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + durationMs / 1000);
    osc.connect(gain); gain.connect(ctx.destination);
    osc.start();
    osc.stop(ctx.currentTime + durationMs / 1000 + 0.02);
  }

  let soundEnabled = (localStorage.getItem(LS_KEY) !== '0'); // default ON
  let soundUnlocked = !!window.__soundUnlocked__;

  function isAudioActive() { return !!soundEnabled && !!soundUnlocked; }

  function updateSoundButtonInline() {
    const btn = document.getElementById('btn-enable-sound-inline');
    if (!btn) return;
    btn.style.display = isAudioActive() ? 'none' : '';
  }

  async function tryUnlockAudio() {
    if (soundUnlocked) return true;
    try {
      audioPos.muted = true; await audioPos.play().catch(() => {}); audioPos.pause(); audioPos.muted = false;
      audioBil.muted = true; await audioBil.play().catch(() => {}); audioBil.pause(); audioBil.muted = false;
    } catch (_) {}
    const ctx = ensureAudioCtx();
    if (ctx && ctx.state !== 'running') { try { await ctx.resume(); } catch (_) {} }
    soundUnlocked = true;
    window.__soundUnlocked__ = true;
    LOG('Audio unlocked');
    return true;
  }

  function bindInlineSoundButton() {
    const btn = document.getElementById('btn-enable-sound-inline');
    if (!btn) return;
    btn.addEventListener('click', async () => {
      await tryUnlockAudio();
      soundEnabled = true;
      localStorage.setItem(LS_KEY, '1');
      const ok = await playDirect('pos');
      updateSoundButtonInline();
      if (!ok) updateSoundButtonInline();
    });
  }

  function bindSoundButton() {
    const btn = document.getElementById('btn-enable-sound');
    if (!btn) return;

    const mark = () => {
      if (soundEnabled) {
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-success');
        btn.innerHTML = `<span class="btn-label"><i class="fe-volume-2"></i></span>Suara ON`;
      } else {
        btn.classList.add('btn-outline-secondary');
        btn.classList.remove('btn-success');
        btn.innerHTML = `<span class="btn-label"><i class="fe-volume-x"></i></span>Suara OFF`;
      }
    };

    btn.addEventListener('click', async () => {
      await tryUnlockAudio();
      soundEnabled = !soundEnabled;
      localStorage.setItem(LS_KEY, soundEnabled ? '1' : '0');
      if (soundEnabled) { playDirect('pos').catch(() => {}); }
      mark();
      updateSoundButtonInline();
    });

    mark();
  }

  function autoUnlockOnFirstGesture() {
    if (window.__autoUnlockBound__) return;
    window.__autoUnlockBound__ = true;
    const events = ['pointerdown', 'click', 'touchstart', 'keydown'];
    const handler = async () => {
      await tryUnlockAudio();
      if (soundEnabled) localStorage.setItem(LS_KEY, '1');
      updateSoundButtonInline();
      events.forEach(ev => window.removeEventListener(ev, handler, true));
    };
    events.forEach(ev => window.addEventListener(ev, handler, { once: true, capture: true, passive: true }));
  }

  async function playDirect(kind) {
    const el = (kind === 'pos') ? audioPos : audioBil;
    try {
      el.currentTime = 0;
      await el.play();
      return true;
    } catch (err) {
      LOG('HTMLAudio play blocked:', err && err.name);
      await webAudioBeep(250, kind === 'pos' ? 880 : 660);
      updateSoundButtonInline();
      return false;
    }
  }

  const SOUND_COOLDOWN = 5000;
  const lastSoundAt = { pos: 0, bil: 0 };

  async function playSound(kind) {
    if (!soundEnabled) return;
    const t = now();
    if (t - lastSoundAt[kind] < SOUND_COOLDOWN) return;
    lastSoundAt[kind] = t;

    updateSoundButtonInline();
    await tryUnlockAudio();
    const ok = await playDirect(kind);

    if (ok && isAudioActive()) updateSoundButtonInline();

    const label = (kind === 'pos') ? 'POS' : 'Billiard';
    showToast(`${label}: ada yang baru tuh`, 'success', { sticky: true });
  }

  const last = {
    pos: { total: null, max_id: null, last_ts: null },
    bil: { total: null, max_id: null, last_ts: null }
  };

  const BASE_INTERVAL = 10000, HIDDEN_INTERVAL = 20000;
  let errorStreak = 0, ticking = false;

  function isAdded(oldSnap, snap) {
    const tOld = Number(oldSnap.total), tNew = Number(snap.total);
    const idOld = Number(oldSnap.max_id), idNew = Number(snap.max_id);
    const totalUp = Number.isFinite(tOld) && Number.isFinite(tNew) && tNew > tOld;
    const idUp = Number.isFinite(idOld) && Number.isFinite(idNew) && idNew > idOld;
    return totalUp || idUp;
  }

  function isChanged(oldSnap, snap) {
    const tOld = Number(oldSnap.total), tNew = Number(snap.total);
    const idOld = Number(oldSnap.max_id), idNew = Number(snap.max_id);
    if (Number.isFinite(tOld) && Number.isFinite(tNew) && tNew !== tOld) return true;
    if (Number.isFinite(idOld) && Number.isFinite(idNew) && idNew !== idOld) return true;
    if (oldSnap.last_ts && snap.last_ts && String(snap.last_ts) !== String(oldSnap.last_ts)) return true;
    return false;
  }

  async function safeFetch(url) {
    const r = await fetch(url, { cache: 'no-store', credentials: 'same-origin' });
    if (!r.ok) throw new Error('HTTP ' + r.status);
    return r.json();
  }

  function triggerReload(kind) {
    if (kind === 'pos') {
      if (typeof window.reload_table === 'function') window.reload_table(kind + '-ping');
    } else {
      if (typeof window.reload_billiard_table === 'function') window.reload_billiard_table(kind + '-ping');
    }
  }

  async function handleChannel(kind, url, key) {
    if (!url) return;
    const j = await safeFetch(url);
    if (j && j.success) {
      const snap = { total: Number(j.total || 0), max_id: Number(j.max_id || 0), last_ts: j.last_ts ? String(j.last_ts) : null };
      if (last[key].total === null) {
        last[key] = snap; LOG(key.toUpperCase(), 'baseline', snap);
      } else {
        const added = isAdded(last[key], snap);
        const changed = isChanged(last[key], snap);
        LOG(key.toUpperCase(), { added, changed, prev: last[key], next: snap });
        last[key] = snap;
        if (changed) triggerReload(key);
        if (added) playSound(key);
      }
    }
  }

  async function tick() {
    if (ticking) return;
    ticking = true;

    const visible = !document.hidden;
    const interval = (visible ? BASE_INTERVAL : HIDDEN_INTERVAL) * Math.min(4, (1 + errorStreak * 0.5));

    try {
      await Promise.all([
        handleChannel('pos', PING_POS_URL, 'pos'),
        handleChannel('bil', PING_BIL_URL, 'bil')
      ]);
      errorStreak = 0;
    } catch (_) {
      errorStreak = Math.min(6, errorStreak + 1);
    } finally {
      ticking = false;
      setTimeout(tick, interval);
    }
  }

  if (document.readyState === 'complete' || document.readyState === 'interactive') {
    bindSoundButton();
    bindInlineSoundButton();
    autoUnlockOnFirstGesture();
    updateSoundButtonInline();
  } else {
    document.addEventListener('DOMContentLoaded', () => {
      bindSoundButton();
      bindInlineSoundButton();
      autoUnlockOnFirstGesture();
      updateSoundButtonInline();
    }, { once: true });
  }

  tick();
  document.addEventListener('visibilitychange', function () {
    if (!document.hidden) setTimeout(tick, 200);
  });

  window.test_pos_sound = () => playDirect('pos');
  window.test_bil_sound = () => playDirect('bil');
  window.PING_DEBUG = window.PING_DEBUG || false;
})();

/* Toast center (Swal kalau ada, fallback vanilla) */
function showToast(text, icon = 'success', opts = {}) {
  const sticky = !!opts.sticky;
  const duration = Number.isFinite(opts.duration) ? opts.duration : 2500;
  const iconMap = { danger: 'error', warn: 'warning' };
  const swalIcon = iconMap[icon] || icon;

  if (window.Swal) {
    Swal.fire({
      toast: false,
      icon: swalIcon,
      title: text,
      position: 'center',
      width: 360,
      showConfirmButton: false,
      showCloseButton: sticky,
      timer: sticky ? undefined : duration,
      timerProgressBar: !sticky,
      allowOutsideClick: sticky ? false : true,
      backdrop: false,
      customClass: { popup: 'g-center-toast' }
    });
    return;
  }

  const id = 'toast-center-overlay';
  let c = document.getElementById(id);
  if (!c) {
    c = document.createElement('div');
    c.id = id;
    c.style.cssText = 'position:fixed;inset:0;z-index:100000;display:flex;align-items:center;justify-content:center;pointer-events:none';
    document.body.appendChild(c);
  }

  const card = document.createElement('div');
  card.setAttribute('role', 'status');
  card.style.cssText = 'pointer-events:auto;position:relative;background:#111827;color:#fff;padding:12px 44px 12px 14px;border-radius:12px;box-shadow:0 8px 28px rgba(0,0,0,.35);opacity:0;transform:scale(.96);transition:.15s';
  card.textContent = text;

  const btn = document.createElement('button');
  btn.type = 'button';
  btn.setAttribute('aria-label', 'Close');
  btn.innerHTML = '&times;';
  btn.style.cssText = 'position:absolute;top:6px;right:10px;background:transparent;border:none;color:#fff;font-size:18px;line-height:1;cursor:pointer;opacity:.85';
  btn.addEventListener('click', () => card.remove());
  card.appendChild(btn);

  c.appendChild(card);
  requestAnimationFrame(() => {
    card.style.opacity = '1';
    card.style.transform = 'scale(1)';
  });

  if (!sticky) {
    setTimeout(() => {
      card.style.opacity = '0';
      card.style.transform = 'scale(.96)';
      setTimeout(() => card.remove(), 150);
    }, duration);
  }
}
