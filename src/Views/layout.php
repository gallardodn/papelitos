<?php
declare(strict_types=1);

$title = $title ?? 'Papelitos';
$layoutMode = $layoutMode ?? 'app';
$flashError = flash_get('error');
$flashSuccess = flash_get('success');
$isAuthed = current_user_id() !== null;

$isPrint = $layoutMode === 'print';
$bodyClass = $isPrint
    ? 'bg-white text-slate-800'
    : 'h-[100dvh] w-[100vw] overflow-hidden bg-gradient-to-br from-slate-950 via-indigo-950 to-slate-950 text-slate-100';
$containerClass = $isPrint ? 'mx-auto px-0 py-0' : 'h-full w-full flex flex-col p-4 sm:p-6';

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars((string)$title, ENT_QUOTES, 'UTF-8') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <?php if (!$isPrint): ?>
    <style>
      @keyframes magicSpin { to { transform: rotate(1turn); } }
      @keyframes magicIn { from { opacity: 0; transform: translateY(10px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
      @keyframes magicOut { from { opacity: 1; transform: translateY(0) scale(1); } to { opacity: 0; transform: translateY(-6px) scale(0.99); } }

      .magic-overlay {
        position: fixed;
        inset: 0;
        background: radial-gradient(600px 500px at 50% 30%, rgba(124, 58, 237, 0.22), rgba(0, 0, 0, 0.0) 60%),
          radial-gradient(700px 520px at 20% 70%, rgba(34, 211, 238, 0.16), rgba(0, 0, 0, 0.0) 60%),
          rgba(2, 6, 23, 0.0);
        opacity: 0;
        pointer-events: none;
        z-index: 40;
        transition: opacity 220ms ease;
      }
      body.magic-leave .magic-overlay { opacity: 1; }

      .magic-card {
        position: relative;
        border-radius: 28px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.14);
        box-shadow:
          0 25px 80px rgba(0, 0, 0, 0.55),
          0 0 0 1px rgba(255, 255, 255, 0.06) inset;
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        animation: magicIn 260ms ease both;
        overflow: hidden;
      }

      .magic-card::before {
        content: "";
        position: absolute;
        inset: -2px;
        border-radius: inherit;
        background: conic-gradient(from 0deg, #22d3ee, #a78bfa, #22c55e, #fb7185, #fbbf24, #22d3ee);
        filter: blur(14px);
        opacity: 0.65;
        z-index: -1;
        animation: magicSpin 3.2s linear infinite;
      }

      body.magic-leave .magic-card { animation: magicOut 200ms ease both; }

      .magic-btn {
        position: relative;
        border-radius: 18px;
        transition: transform 160ms ease, box-shadow 160ms ease, filter 160ms ease;
        box-shadow: 0 10px 30px rgba(0,0,0,0.35);
      }
      .magic-btn:hover {
        transform: translateY(-1px) scale(1.03);
        box-shadow: 0 16px 44px rgba(0,0,0,0.45), 0 0 0 1px rgba(255,255,255,0.10) inset, 0 0 26px rgba(168, 85, 247, 0.28);
        filter: saturate(1.15);
      }
      .magic-btn:active { transform: translateY(0) scale(0.99); }

      .magic-glass {
        background: rgba(2, 6, 23, 0.35);
        border: 1px solid rgba(255, 255, 255, 0.14);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        box-shadow: 0 14px 40px rgba(0,0,0,0.50);
      }

      .magic-arrow {
        width: 68px;
        height: 68px;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 28px;
        line-height: 1;
        box-shadow: 0 18px 50px rgba(0,0,0,0.50), 0 0 28px rgba(34, 211, 238, 0.22);
        transition: transform 160ms ease, box-shadow 160ms ease;
      }
      .magic-arrow:hover {
        transform: scale(1.06);
        box-shadow: 0 22px 64px rgba(0,0,0,0.60), 0 0 34px rgba(168, 85, 247, 0.26);
      }

      .magic-clamp-8 {
        display: -webkit-box;
        -webkit-line-clamp: 8;
        -webkit-box-orient: vertical;
        overflow: hidden;
      }
    </style>
  <?php endif; ?>
</head>
<body class="<?= htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8') ?>">
  <?php if (!$isPrint): ?>
    <canvas id="magicParticles" aria-hidden="true" style="position:fixed;inset:0;z-index:0;pointer-events:none"></canvas>
    <div class="magic-overlay" aria-hidden="true"></div>
  <?php endif; ?>
  <div class="<?= htmlspecialchars($containerClass, ENT_QUOTES, 'UTF-8') ?>">
    <?php if (!$isPrint): ?>
      <header class="flex items-center justify-between gap-4 shrink-0 magic-glass rounded-3xl px-5 py-4">
        <a href="<?= $isAuthed ? '/menu' : '/' ?>" class="select-none">
          <h1 class="text-4xl font-extrabold tracking-wide">
            <span class="text-sky-600">P</span><span class="text-yellow-500">a</span><span class="text-orange-500">p</span><span class="text-green-600">e</span><span class="text-amber-900">l</span><span class="text-violet-600">i</span><span class="text-red-600">t</span><span class="text-slate-900">o</span><span class="text-sky-600">s</span>
          </h1>
        </a>
        <?php if ($isAuthed): ?>
          <form method="post" action="/logout" class="shrink-0">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="magic-btn rounded-xl px-4 py-2 bg-slate-900 text-white hover:bg-slate-800 active:bg-slate-950 transition">
              Salir
            </button>
          </form>
        <?php endif; ?>
      </header>

      <?php if (is_string($flashError) && $flashError !== ''): ?>
        <div class="mt-4 rounded-2xl border border-red-400/30 bg-red-500/10 px-5 py-4 text-red-100 magic-glass">
          <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>

      <?php if (is_string($flashSuccess) && $flashSuccess !== ''): ?>
        <div class="mt-4 rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-5 py-4 text-emerald-100 magic-glass">
          <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <main class="<?= $isPrint ? '' : 'flex-1 min-h-0 overflow-hidden mt-4' ?>">
      <?= $content ?>
    </main>
  </div>
  <?php if (!$isPrint): ?>
    <script>
      (function () {
        const prefersReduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const overlay = document.querySelector('.magic-overlay');

        function isSafeLink(a) {
          if (!a) return false;
          if (a.target && a.target !== '') return false;
          if (a.hasAttribute('download')) return false;
          const href = a.getAttribute('href') || '';
          if (href === '' || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) return false;
          if (href.startsWith('http://') || href.startsWith('https://')) {
            try {
              const u = new URL(href);
              return u.origin === window.location.origin;
            } catch (_) {
              return false;
            }
          }
          return href.startsWith('/');
        }

        document.addEventListener('click', function (e) {
          const a = e.target && e.target.closest ? e.target.closest('a') : null;
          if (!isSafeLink(a)) return;
          if (e.defaultPrevented) return;
          if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
          const href = a.getAttribute('href');
          if (!href) return;
          if (prefersReduce) return;
          e.preventDefault();

          const open = a.getAttribute('data-magic-open') || '';
          if (open === 'expand') {
            const src = a.closest('.magic-card') || a.closest('.magic-glass') || a;
            try {
              const r = src.getBoundingClientRect();
              const cs = window.getComputedStyle(src);
              const ghost = document.createElement('div');
              ghost.style.position = 'fixed';
              ghost.style.left = r.left + 'px';
              ghost.style.top = r.top + 'px';
              ghost.style.width = r.width + 'px';
              ghost.style.height = r.height + 'px';
              ghost.style.borderRadius = cs.borderRadius || '28px';
              ghost.style.background = cs.backgroundColor && cs.backgroundColor !== 'rgba(0, 0, 0, 0)' ? cs.backgroundColor : 'rgba(255,255,255,0.08)';
              ghost.style.border = '1px solid rgba(255, 255, 255, 0.14)';
              ghost.style.boxShadow = '0 25px 80px rgba(0, 0, 0, 0.55)';
              ghost.style.backdropFilter = 'blur(14px)';
              ghost.style.webkitBackdropFilter = 'blur(14px)';
              ghost.style.zIndex = '60';
              ghost.style.pointerEvents = 'none';
              document.body.appendChild(ghost);

              const vw = Math.max(1, window.innerWidth || 1);
              const vh = Math.max(1, window.innerHeight || 1);
              const sx = vw / Math.max(1, r.width);
              const sy = vh / Math.max(1, r.height);
              ghost.animate(
                [
                  { transform: 'translate(0px, 0px) scale(1)', opacity: 1 },
                  { transform: 'translate(' + (-r.left) + 'px,' + (-r.top) + 'px) scale(' + sx + ',' + sy + ')', opacity: 1 }
                ],
                { duration: 240, easing: 'cubic-bezier(0.2, 0.9, 0.2, 1)', fill: 'forwards' }
              );
              window.setTimeout(function () {
                ghost.remove();
              }, 280);
            } catch (_) {}
          }

          document.body.classList.add('magic-leave');
          window.setTimeout(function () {
            window.location.href = href;
          }, 240);
        }, { capture: true });

        const canvas = document.getElementById('magicParticles');
        if (!(canvas instanceof HTMLCanvasElement)) return;
        const ctx = canvas.getContext('2d', { alpha: true });
        if (!ctx) return;

        const state = {
          w: 0,
          h: 0,
          dpr: 1,
          mx: 0,
          my: 0,
          particles: [],
          bursts: []
        };

        function resize() {
          state.dpr = Math.max(1, Math.min(2, window.devicePixelRatio || 1));
          state.w = Math.max(1, window.innerWidth || 1);
          state.h = Math.max(1, window.innerHeight || 1);
          canvas.width = Math.floor(state.w * state.dpr);
          canvas.height = Math.floor(state.h * state.dpr);
          canvas.style.width = state.w + 'px';
          canvas.style.height = state.h + 'px';
          ctx.setTransform(state.dpr, 0, 0, state.dpr, 0, 0);
        }

        function seed() {
          if (prefersReduce) {
            state.particles = [];
            return;
          }
          const count = Math.min(160, Math.floor((state.w * state.h) / 16000));
          const arr = [];
          for (let i = 0; i < count; i++) {
            arr.push({
              x: Math.random() * state.w,
              y: Math.random() * state.h,
              vx: (Math.random() - 0.5) * 0.22,
              vy: (Math.random() - 0.5) * 0.22,
              r: 0.8 + Math.random() * 1.9,
              a: 0.05 + Math.random() * 0.18
            });
          }
          state.particles = arr;
        }

        function burst(x, y, strength) {
          if (prefersReduce) return;
          const n = 18 + Math.floor(Math.random() * 10);
          const s = strength || 1;
          for (let i = 0; i < n; i++) {
            const ang = (i / n) * Math.PI * 2 + (Math.random() - 0.5) * 0.35;
            const sp = (2.0 + Math.random() * 2.8) * s;
            state.bursts.push({
              x: x,
              y: y,
              vx: Math.cos(ang) * sp,
              vy: Math.sin(ang) * sp,
              r: 1.1 + Math.random() * 2.4,
              a: 1
            });
          }
        }

        function tick() {
          ctx.clearRect(0, 0, state.w, state.h);

          const gx = (state.mx - state.w / 2) / state.w;
          const gy = (state.my - state.h / 2) / state.h;

          ctx.globalCompositeOperation = 'lighter';

          for (const p of state.particles) {
            p.vx += gx * 0.0007;
            p.vy += gy * 0.0007;
            p.x += p.vx;
            p.y += p.vy;
            p.vx *= 0.995;
            p.vy *= 0.995;

            if (p.x < -10) p.x = state.w + 10;
            if (p.x > state.w + 10) p.x = -10;
            if (p.y < -10) p.y = state.h + 10;
            if (p.y > state.h + 10) p.y = -10;

            ctx.globalAlpha = p.a;
            ctx.fillStyle = 'rgba(255,255,255,1)';
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fill();
          }

          for (let i = state.bursts.length - 1; i >= 0; i--) {
            const b = state.bursts[i];
            b.x += b.vx;
            b.y += b.vy;
            b.vx *= 0.985;
            b.vy *= 0.985;
            b.a *= 0.90;
            b.r *= 0.992;
            if (b.a < 0.02) {
              state.bursts.splice(i, 1);
              continue;
            }
            ctx.globalAlpha = Math.min(0.65, b.a);
            ctx.fillStyle = 'rgba(34,211,238,1)';
            ctx.beginPath();
            ctx.arc(b.x, b.y, Math.max(0.7, b.r), 0, Math.PI * 2);
            ctx.fill();
          }

          ctx.globalAlpha = 1;
          ctx.globalCompositeOperation = 'source-over';
          window.requestAnimationFrame(tick);
        }

        window.addEventListener('mousemove', function (e) {
          state.mx = e.clientX;
          state.my = e.clientY;
        }, { passive: true });

        window.addEventListener('click', function (e) {
          const btn = e.target && e.target.closest ? e.target.closest('.magic-btn') : null;
          if (!btn) return;
          burst(e.clientX, e.clientY, 1.0);
        }, { capture: true });

        window.addEventListener('resize', function () {
          resize();
          seed();
        }, { passive: true });

        resize();
        seed();
        tick();
      })();
    </script>
  <?php endif; ?>
</body>
</html>
