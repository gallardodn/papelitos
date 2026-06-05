<?php
declare(strict_types=1);

$books = $books ?? [];
$selectedBookId = $selectedBookId ?? null;

?>
<div class="magic-card h-full w-full p-6 sm:p-10 flex flex-col">
  <div class="flex items-start justify-between gap-4 mb-6 shrink-0">
    <div>
      <h2 class="text-3xl font-extrabold mb-2 text-white">Editar</h2>
      <p class="text-slate-200">Elegí un libro para editar.</p>
    </div>
    <div class="flex gap-2">
      <a href="/library" class="magic-btn rounded-2xl px-4 py-2 bg-white/10 text-white magic-glass transition">
        Biblioteca
      </a>
      <a href="/menu" class="magic-btn rounded-2xl px-4 py-2 bg-white/10 text-white magic-glass transition">
        Menú
      </a>
    </div>
  </div>

  <?php if (!is_array($books) || count($books) === 0): ?>
    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-slate-700">
      No hay libros para editar. Creá uno desde “Nuevo libro”.
    </div>
  <?php else: ?>
    <div class="flex-1 min-h-0 grid grid-cols-1 gap-3 content-start">
      <?php foreach ($books as $b): ?>
        <?php $id = (string)($b['id'] ?? ''); ?>
        <a href="/edit?book_id=<?= urlencode($id) ?>" class="magic-btn block rounded-3xl border border-white/10 bg-gradient-to-br from-lime-500/20 via-white/5 to-white/5 p-5">
          <div class="flex items-center justify-between gap-4">
            <div>
              <div class="text-lg font-extrabold text-white"><?= htmlspecialchars((string)($b['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
              <div class="text-sm text-slate-200 mt-1"><?= htmlspecialchars((string)($b['author'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <?php if ($selectedBookId !== null && (string)$selectedBookId === $id): ?>
              <div class="text-sm font-extrabold text-lime-300">Seleccionado</div>
            <?php endif; ?>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="mt-6 flex items-center justify-between shrink-0">
    <a href="/library" class="magic-arrow magic-btn bg-white/10 text-white magic-glass" aria-label="Anterior">
      ←
    </a>
    <a href="/menu" class="magic-arrow magic-btn bg-white/10 text-white magic-glass" aria-label="Siguiente">
      →
    </a>
  </div>
</div>
