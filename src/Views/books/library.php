<?php
declare(strict_types=1);

$books = $books ?? [];

?>
<div class="bg-white/90 backdrop-blur rounded-3xl shadow-xl shadow-orange-100 border border-orange-100 p-8">
  <div class="flex items-start justify-between gap-4 mb-6">
    <div>
      <h2 class="text-2xl font-bold mb-2">Biblioteca</h2>
      <p class="text-slate-600">Tus libros guardados.</p>
    </div>
    <div class="flex gap-2">
      <a href="/new-book" class="rounded-2xl px-4 py-2 bg-orange-500 text-white hover:bg-orange-600 active:bg-orange-700 transition">
        Nuevo
      </a>
      <a href="/menu" class="rounded-2xl px-4 py-2 bg-slate-900 text-white hover:bg-slate-800 active:bg-slate-950 transition">
        Menú
      </a>
    </div>
  </div>

  <?php if (!is_array($books) || count($books) === 0): ?>
    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-slate-700">
      Todavía no tenés libros. Creá el primero con “Nuevo”.
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <?php foreach ($books as $b): ?>
        <div class="rounded-3xl border border-slate-100 bg-gradient-to-br from-amber-50 to-white p-6">
          <div class="text-lg font-extrabold"><?= htmlspecialchars((string)($b['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
          <div class="text-sm text-slate-600 mt-1">
            <?= htmlspecialchars((string)($b['author'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
          </div>
          <div class="mt-4 flex items-center justify-between">
            <a href="/edit?book_id=<?= urlencode((string)($b['id'] ?? '')) ?>" class="rounded-2xl px-4 py-2 bg-slate-900 text-white hover:bg-slate-800 active:bg-slate-950 transition">
              Editar
            </a>
            <div class="text-xs text-slate-500">
              <?= htmlspecialchars((string)($b['updated_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

