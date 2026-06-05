<?php
declare(strict_types=1);

$books = $books ?? [];
$selectedBookId = $selectedBookId ?? null;

?>
<div class="bg-white/90 backdrop-blur rounded-3xl shadow-xl shadow-orange-100 border border-orange-100 p-8">
  <div class="flex items-start justify-between gap-4 mb-6">
    <div>
      <h2 class="text-2xl font-bold mb-2">Editar</h2>
      <p class="text-slate-600">Elegí un libro para editar (el editor viene en el siguiente paso).</p>
    </div>
    <div class="flex gap-2">
      <a href="/library" class="rounded-2xl px-4 py-2 bg-slate-900 text-white hover:bg-slate-800 active:bg-slate-950 transition">
        Biblioteca
      </a>
      <a href="/menu" class="rounded-2xl px-4 py-2 bg-slate-900 text-white hover:bg-slate-800 active:bg-slate-950 transition">
        Menú
      </a>
    </div>
  </div>

  <?php if (!is_array($books) || count($books) === 0): ?>
    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-slate-700">
      No hay libros para editar. Creá uno desde “Nuevo libro”.
    </div>
  <?php else: ?>
    <div class="space-y-3">
      <?php foreach ($books as $b): ?>
        <?php $id = (string)($b['id'] ?? ''); ?>
        <a href="/edit?book_id=<?= urlencode($id) ?>" class="block rounded-3xl border border-slate-100 bg-gradient-to-br from-lime-50 to-white p-5 hover:shadow-md transition">
          <div class="flex items-center justify-between gap-4">
            <div>
              <div class="text-lg font-extrabold"><?= htmlspecialchars((string)($b['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
              <div class="text-sm text-slate-600 mt-1"><?= htmlspecialchars((string)($b['author'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <?php if ($selectedBookId !== null && (string)$selectedBookId === $id): ?>
              <div class="text-sm font-semibold text-lime-700">Seleccionado</div>
            <?php endif; ?>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

