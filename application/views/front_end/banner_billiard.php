<div class="t-card mb-3" style="--bg:url('<?= base_url('assets/images/billiard.webp') ?>')">
  <blockquote class="t-quote">
    <div class="font-weight-bold mb-1">Gratis Main Billiard 🎟️</div>
    Main <b><?= (int)$rec->batas_edit ?></b>x dapet
    <b>1 voucher Free Main Billiard selama <?= $rec->jam_voucher_default . " jam" ?></b>.
    Gaskeun — rajin main, kumpulin poinnya! 🔥 Sistem bakal ngitung otomatis jumlah main kamu dan langsung ngasih tau.

    <small class="d-block mt-2 text-dark">
      <a href="<?= site_url('hal#voucher') ?>" class="text-dark" style="text-decoration:underline;">
        *Syarat &amp; Ketentuan berlaku
      </a>
    </small>
  </blockquote>

  <!-- <div class="t-author"><?= htmlspecialchars($rec->type ?? '', ENT_QUOTES, 'UTF-8') ?></div> -->
  <a class="t-btn" href="<?= site_url('billiard') ?>">Gas Booking</a>
</div>
<style type="text/css">
	.t-card {
    --grad-1: #2563eb;
    --grad-2: #0ea5e9;
    --grad-3: #22d3ee;
    position: relative;
    border-radius: 26px;
    padding: 36px 22px 28px;
    color: #fff;
    text-align: center;
    min-height: 420px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 12px;
    background: radial-gradient(120% 100% at 100% 0, rgb(255 255 255 / 0.16), transparent 50%),
        linear-gradient(160deg, var(--grad-1) 0%, var(--grad-2) 60%, var(--grad-3) 100%);
    box-shadow:
        0 20px 50px rgb(2 6 23 / 0.15),
        inset 0 1px 0 rgb(255 255 255 / 0.15);
    overflow: hidden;
}
.t-card::before {
    content: "";
    position: absolute;
    inset: 0;
    background: var(--bg, none) center/cover no-repeat;
    opacity: 0.18;
    mix-blend-mode: soft-light;
    pointer-events: none;
}
.t-quote {
    margin: 0;
    font-weight: 800;
    letter-spacing: 0.015em;
    line-height: 1.6;
    font-size: clamp(18px, 2.6vw, 28px);
}
.t-author {
    opacity: 0.85;
    font-weight: 600;
    letter-spacing: 0.02em;
    color: rgb(255 255 255 / 0.88);
    margin-bottom: 8px;
}
.t-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 22px;
    border-radius: 14px;
    border: 2px solid rgb(255 255 255 / 0.9);
    color: #fff;
    text-decoration: none;
    font-weight: 800;
    letter-spacing: 0.06em;
    background: rgb(255 255 255 / 0.09);
    backdrop-filter: saturate(120%) blur(6px);
    box-shadow: 0 8px 22px rgb(2 6 23 / 0.25);
    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        background 0.2s ease;
}
.t-btn:hover {
    transform: translateY(-2px);
    background: rgb(255 255 255 / 0.18);
    box-shadow: 0 14px 36px rgb(2 6 23 / 0.35);
}
@media (prefers-color-scheme: light) {
    .t-card {
        outline: 1px solid rgb(255 255 255 / 0.35);
    }
}
@media (max-width: 480px) {
    .t-card {
        min-height: 360px;
        padding: 28px 18px;
    }
}

</style>