<?php
declare(strict_types=1);

$books = $books ?? [];

?>
<div class="magic-card h-full w-full p-6 sm:p-10 flex flex-col">
  <div class="flex items-start justify-between gap-4 mb-6 shrink-0">
    <div>
      <h2 class="text-3xl font-extrabold mb-2 text-white">Biblioteca</h2>
      <p class="text-slate-200">Tus libros guardados.</p>
    </div>
    <div class="flex gap-2">
      <a href="/new-book" class="magic-btn rounded-2xl px-4 py-2 bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500 text-white font-extrabold transition">
        Nuevo
      </a>
      <a href="/menu" class="magic-btn rounded-2xl px-4 py-2 bg-white/10 text-white magic-glass transition">
        Menú
      </a>
    </div>
  </div>

  <?php if (!is_array($books) || count($books) === 0): ?>
    <div class="magic-glass rounded-2xl border border-white/10 px-5 py-4 text-slate-100">
      Todavía no tenés libros. Creá el primero con “Nuevo”.
    </div>
  <?php else: ?>
    <?php $maxIndex = max(0, count($books) - 1); ?>
    <div class="flex-1 min-h-0 relative" x-data="{ i: 0, max: <?= (int)$maxIndex ?>, go(n){ this.i = Math.max(0, Math.min(this.max, n)); }, prev(){ this.go(this.i - 1); }, next(){ this.go(this.i + 1); } }" x-on:keydown.window.left.prevent="prev()" x-on:keydown.window.right.prevent="next()">
      <div class="absolute inset-y-0 left-0 flex items-center z-10">
        <button type="button" class="magic-arrow magic-btn bg-white/10 text-white magic-glass ml-2" x-on:click="prev()" x-bind:class="i === 0 ? 'opacity-30 pointer-events-none' : ''" aria-label="Anterior">
          ←
        </button>
      </div>
      <div class="absolute inset-y-0 right-0 flex items-center z-10">
        <button type="button" class="magic-arrow magic-btn bg-white/10 text-white magic-glass mr-2" x-on:click="next()" x-bind:class="i === max ? 'opacity-30 pointer-events-none' : ''" aria-label="Siguiente">
          →
        </button>
      </div>

      <div class="h-full w-full overflow-hidden">
        <div class="h-full flex transition-transform duration-500 ease-out" x-bind:style="'transform: translateX(' + (-i * 100) + 'vw)'">
          <?php foreach ($books as $b): ?>
            <?php $id = (string)($b['id'] ?? ''); ?>
            <div class="w-[100vw] h-full px-4 sm:px-10 flex items-center justify-center">
              <div class="magic-glass rounded-3xl border border-white/10 p-8 w-full max-w-xl">
                <div class="text-xs font-extrabold tracking-widest text-slate-200/80">LIBRO</div>
                <div class="mt-2 text-3xl sm:text-4xl font-extrabold text-white leading-tight">
                  <?= htmlspecialchars((string)($b['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="mt-2 text-slate-200 text-lg">
                  <?= htmlspecialchars((string)($b['author'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="mt-6 flex items-center justify-between gap-3">
                  <a data-magic-open="expand" href="/edit?book_id=<?= urlencode($id) ?>" class="magic-btn rounded-2xl px-6 py-3 bg-gradient-to-r from-sky-500 via-violet-600 to-fuchsia-600 text-white font-extrabold">
                    Abrir
                  </a>
                  <div class="text-xs text-slate-200/80">
                    <?= htmlspecialchars((string)($b['updated_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <div class="mt-6 flex items-center justify-between shrink-0">
    <a href="/menu" class="magic-arrow magic-btn bg-white/10 text-white magic-glass" aria-label="Anterior">
      ←
    </a>
    <a href="/new-book" class="magic-arrow magic-btn bg-white/10 text-white magic-glass" aria-label="Siguiente">
      →
    </a>
  </div>
</div>
