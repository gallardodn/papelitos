<?php
declare(strict_types=1);

$titleValue = $titleValue ?? '';
$authorValue = $authorValue ?? '';

?>
<div class="magic-card h-full w-full p-6 sm:p-10 flex flex-col">
  <div class="flex items-start justify-between gap-4 shrink-0">
    <div>
      <h2 class="text-3xl font-extrabold mb-2 text-white">Nuevo libro</h2>
      <p class="text-slate-200 mb-6">Poné un título y el autor.</p>
    </div>
    <a href="/menu" class="magic-btn rounded-2xl px-4 py-2 bg-white/10 text-white magic-glass transition">
      Menú
    </a>
  </div>

  <form method="post" action="/new-book" class="space-y-4 flex-1 min-h-0">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

    <label class="block">
      <span class="text-sm font-semibold text-slate-100">Título</span>
      <input name="title" value="<?= htmlspecialchars((string)$titleValue, ENT_QUOTES, 'UTF-8') ?>" required
        class="mt-1 w-full rounded-2xl border border-white/10 bg-white/10 text-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-violet-400/20">
    </label>

    <label class="block">
      <span class="text-sm font-semibold text-slate-100">Autor</span>
      <input name="author" value="<?= htmlspecialchars((string)$authorValue, ENT_QUOTES, 'UTF-8') ?>" required
        class="mt-1 w-full rounded-2xl border border-white/10 bg-white/10 text-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-violet-400/20">
    </label>

    <button type="submit"
      class="magic-btn w-full rounded-2xl px-4 py-3 bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500 text-white font-extrabold tracking-wide transition">
      Crear libro
    </button>
  </form>

  <div class="mt-6 flex items-center justify-between shrink-0">
    <a href="/menu" class="magic-arrow magic-btn bg-white/10 text-white magic-glass" aria-label="Anterior">
      ←
    </a>
    <a href="/library" class="magic-arrow magic-btn bg-white/10 text-white magic-glass" aria-label="Siguiente">
      →
    </a>
  </div>
</div>
