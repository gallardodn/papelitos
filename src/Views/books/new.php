<?php
declare(strict_types=1);

$titleValue = $titleValue ?? '';
$authorValue = $authorValue ?? '';

?>
<div class="bg-white/90 backdrop-blur rounded-3xl shadow-xl shadow-orange-100 border border-orange-100 p-8">
  <div class="flex items-start justify-between gap-4">
    <div>
      <h2 class="text-2xl font-bold mb-2">Nuevo libro</h2>
      <p class="text-slate-600 mb-6">Poné un título y el autor.</p>
    </div>
    <a href="/menu" class="rounded-2xl px-4 py-2 bg-slate-900 text-white hover:bg-slate-800 active:bg-slate-950 transition">
      Volver
    </a>
  </div>

  <form method="post" action="/new-book" class="space-y-4">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

    <label class="block">
      <span class="text-sm font-semibold text-slate-700">Título</span>
      <input name="title" value="<?= htmlspecialchars((string)$titleValue, ENT_QUOTES, 'UTF-8') ?>" required
        class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 focus:outline-none focus:ring-4 focus:ring-orange-100">
    </label>

    <label class="block">
      <span class="text-sm font-semibold text-slate-700">Autor</span>
      <input name="author" value="<?= htmlspecialchars((string)$authorValue, ENT_QUOTES, 'UTF-8') ?>" required
        class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 focus:outline-none focus:ring-4 focus:ring-orange-100">
    </label>

    <button type="submit"
      class="w-full rounded-2xl px-4 py-3 bg-orange-500 text-white font-semibold hover:bg-orange-600 active:bg-orange-700 transition">
      Crear libro
    </button>
  </form>
</div>

