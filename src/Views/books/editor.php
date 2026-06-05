<?php
declare(strict_types=1);

$book = $book ?? [];
$cover = $cover ?? null;
$backCover = $backCover ?? null;
$sheets = $sheets ?? [];

$bookId = (string)($book['id'] ?? '');

$sheetParam = $_GET['sheet'] ?? null;
$sheetIdx = $sheetParam === null ? -1 : filter_var($sheetParam, FILTER_VALIDATE_INT);
$sheetIdx = $sheetIdx === false ? -1 : (int)$sheetIdx;
$maxSheet = is_array($sheets) ? (count($sheets) - 1) : -1;
if ($sheetIdx < -1) {
    $sheetIdx = -1;
}
if ($sheetIdx > $maxSheet) {
    $sheetIdx = $maxSheet;
}

$isCover = $sheetIdx === -1;
$currentSheet = (!$isCover && is_array($sheets[$sheetIdx] ?? null)) ? $sheets[$sheetIdx] : null;

$leftPage = null;
$rightPage = null;
$leftLabel = '';
$rightLabel = '';
$leftNumber = null;
$rightNumber = null;
$sheetIndexForActions = null;

if ($isCover) {
    $leftPage = is_array($cover) ? $cover : null;
    $rightPage = is_array($backCover) ? $backCover : null;
    $leftLabel = 'Tapa';
    $rightLabel = 'Contratapa';
} else {
    $sheetIndexForActions = (int)($currentSheet['sheet_index'] ?? $sheetIdx);
    $leftPage = is_array($currentSheet['left'] ?? null) ? $currentSheet['left'] : null;
    $rightPage = is_array($currentSheet['right'] ?? null) ? $currentSheet['right'] : null;
    $leftNumber = $sheetIndexForActions * 2 + 1;
    $rightNumber = $sheetIndexForActions * 2 + 2;
    $leftLabel = 'Página';
    $rightLabel = 'Página';
}

$prevSheetIdx = $sheetIdx - 1;
$nextSheetIdx = $sheetIdx + 1;
$prevHref = $prevSheetIdx < -1 ? '/library' : ('/edit?book_id=' . urlencode($bookId) . '&sheet=' . urlencode((string)$prevSheetIdx));
if ($sheetIdx === -1) {
    $nextHref = $maxSheet >= 0 ? ('/edit?book_id=' . urlencode($bookId) . '&sheet=0') : '/library';
} elseif ($nextSheetIdx > $maxSheet) {
    $nextHref = '/print?book_id=' . urlencode($bookId);
} else {
    $nextHref = '/edit?book_id=' . urlencode($bookId) . '&sheet=' . urlencode((string)$nextSheetIdx);
}

$leftPageId = is_array($leftPage) ? (string)($leftPage['id'] ?? '') : '';
$rightPageId = is_array($rightPage) ? (string)($rightPage['id'] ?? '') : '';

$leftImagePath = is_array($leftPage) ? (string)($leftPage['image_path'] ?? '') : '';
$rightImagePath = is_array($rightPage) ? (string)($rightPage['image_path'] ?? '') : '';
$leftText = is_array($leftPage) ? (string)($leftPage['text'] ?? '') : '';
$rightText = is_array($rightPage) ? (string)($rightPage['text'] ?? '') : '';

$leftCanDelete = (!$isCover && is_array($leftPage) && (int)($leftPage['page_index'] ?? -1) >= 0);
$rightCanDelete = (!$isCover && is_array($rightPage) && (int)($rightPage['page_index'] ?? -1) >= 0);

?>
<div class="magic-card h-full w-full p-5 sm:p-8 flex flex-col">
  <div class="flex items-start justify-between gap-4 shrink-0">
    <div class="min-w-0">
      <h2 class="text-2xl sm:text-3xl font-extrabold text-white truncate"><?= htmlspecialchars((string)($book['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
      <div class="text-slate-200 truncate"><?= htmlspecialchars((string)($book['author'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <div class="flex gap-2 shrink-0">
      <a href="/print?book_id=<?= urlencode($bookId) ?>" class="magic-btn rounded-2xl px-4 py-2 bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500 text-white font-extrabold transition">
        Imprimir
      </a>
      <a href="/library" class="magic-btn rounded-2xl px-4 py-2 bg-white/10 text-white magic-glass transition">
        Biblioteca
      </a>
      <a href="/menu" class="magic-btn rounded-2xl px-4 py-2 bg-white/10 text-white magic-glass transition">
        Menú
      </a>
    </div>
  </div>

  <div class="mt-4 flex items-center justify-between shrink-0">
    <div class="text-sm text-slate-200">
      <?= $isCover ? 'Hoja 0 · Tapa / Contratapa' : ('Hoja ' . ($sheetIdx + 1) . ' · Páginas ' . (string)$leftNumber . ' y ' . (string)$rightNumber) ?>
    </div>
    <div class="flex items-center gap-2">
      <a href="<?= htmlspecialchars($prevHref, ENT_QUOTES, 'UTF-8') ?>" class="magic-arrow magic-btn bg-white/10 text-white magic-glass" aria-label="Anterior">←</a>
      <a href="<?= htmlspecialchars($nextHref, ENT_QUOTES, 'UTF-8') ?>" class="magic-arrow magic-btn bg-white/10 text-white magic-glass" aria-label="Siguiente">→</a>
    </div>
  </div>

  <div class="mt-4 flex-1 min-h-0 grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-4">
    <div class="magic-glass rounded-3xl border border-white/10 p-4 flex items-center justify-center overflow-hidden">
      <div class="h-full w-full flex items-center justify-center">
        <div class="h-full max-h-full aspect-[210/297] w-auto rounded-2xl overflow-hidden border border-white/10 bg-white/5">
          <div class="grid grid-cols-2 h-full divide-x divide-white/10">
            <div class="p-4 flex flex-col gap-3">
              <div class="text-xs font-extrabold tracking-widest text-slate-200/80">
                <?= htmlspecialchars($leftNumber === null ? $leftLabel : ($leftLabel . ' ' . (string)$leftNumber), ENT_QUOTES, 'UTF-8') ?>
              </div>
              <?php if ($leftImagePath !== ''): ?>
                <div class="rounded-2xl border border-white/10 bg-white/5 overflow-hidden">
                  <img src="/u/<?= htmlspecialchars($leftImagePath, ENT_QUOTES, 'UTF-8') ?>" alt="Imagen" class="w-full object-contain max-h-48">
                </div>
              <?php else: ?>
                <div class="rounded-2xl border border-dashed border-white/15 bg-white/5 h-28"></div>
              <?php endif; ?>
              <div class="text-sm text-slate-100 whitespace-pre-wrap leading-relaxed magic-clamp-8">
                <?= htmlspecialchars($leftText === '' ? '...' : $leftText, ENT_QUOTES, 'UTF-8') ?>
              </div>
            </div>
            <div class="p-4 flex flex-col gap-3">
              <div class="text-xs font-extrabold tracking-widest text-slate-200/80">
                <?= htmlspecialchars($rightNumber === null ? $rightLabel : ($rightLabel . ' ' . (string)$rightNumber), ENT_QUOTES, 'UTF-8') ?>
              </div>
              <?php if ($rightImagePath !== ''): ?>
                <div class="rounded-2xl border border-white/10 bg-white/5 overflow-hidden">
                  <img src="/u/<?= htmlspecialchars($rightImagePath, ENT_QUOTES, 'UTF-8') ?>" alt="Imagen" class="w-full object-contain max-h-48">
                </div>
              <?php else: ?>
                <div class="rounded-2xl border border-dashed border-white/15 bg-white/5 h-28"></div>
              <?php endif; ?>
              <div class="text-sm text-slate-100 whitespace-pre-wrap leading-relaxed magic-clamp-8">
                <?= htmlspecialchars($rightText === '' ? '...' : $rightText, ENT_QUOTES, 'UTF-8') ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="magic-glass rounded-3xl border border-white/10 p-4 overflow-hidden" x-data="{ side: 'left', tool: 'image', dragging: false, submitIfFile(e){ const files = e?.dataTransfer?.files; if(!files || files.length === 0) return; this.$refs.input.files = files; this.$refs.form.submit(); } }">
      <div class="flex items-center justify-between gap-2">
        <div class="text-sm font-extrabold text-white tracking-wide">Hechizos</div>
        <form method="post" action="/sheet/add" class="shrink-0">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="book_id" value="<?= htmlspecialchars($bookId, ENT_QUOTES, 'UTF-8') ?>">
          <button type="submit" class="magic-btn rounded-2xl px-3 py-2 bg-white/10 text-white magic-glass transition">
            + Hoja
          </button>
        </form>
      </div>

      <div class="mt-4 grid grid-cols-2 gap-2">
        <button type="button" class="magic-btn rounded-2xl px-3 py-2 text-sm font-extrabold transition" x-on:click="side = 'left'" x-bind:class="side === 'left' ? 'bg-sky-500/30 text-white border border-white/10' : 'bg-white/5 text-slate-200 border border-white/10'">
          Izquierda
        </button>
        <button type="button" class="magic-btn rounded-2xl px-3 py-2 text-sm font-extrabold transition" x-on:click="side = 'right'" x-bind:class="side === 'right' ? 'bg-fuchsia-500/30 text-white border border-white/10' : 'bg-white/5 text-slate-200 border border-white/10'">
          Derecha
        </button>
      </div>

      <div class="mt-3 grid grid-cols-2 gap-2">
        <button type="button" class="magic-btn rounded-2xl px-3 py-2 text-sm font-extrabold transition" x-on:click="tool = 'image'" x-bind:class="tool === 'image' ? 'bg-amber-400/30 text-white border border-white/10' : 'bg-white/5 text-slate-200 border border-white/10'">
          Imagen
        </button>
        <button type="button" class="magic-btn rounded-2xl px-3 py-2 text-sm font-extrabold transition" x-on:click="tool = 'text'" x-bind:class="tool === 'text' ? 'bg-lime-400/25 text-white border border-white/10' : 'bg-white/5 text-slate-200 border border-white/10'">
          Texto
        </button>
      </div>

      <div class="mt-4" x-show="tool === 'image'">
        <form method="post" action="/page/image" enctype="multipart/form-data" class="space-y-3" x-ref="form" x-on:dragover.prevent="dragging = true" x-on:dragleave.prevent="dragging = false" x-on:drop.prevent="dragging = false; submitIfFile($event)">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="book_id" value="<?= htmlspecialchars($bookId, ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="page_id" x-bind:value="side === 'left' ? '<?= htmlspecialchars($leftPageId, ENT_QUOTES, 'UTF-8') ?>' : '<?= htmlspecialchars($rightPageId, ENT_QUOTES, 'UTF-8') ?>'">

          <label class="block cursor-pointer">
            <span class="text-sm font-extrabold text-white">Soltar imagen</span>
            <div class="mt-2 rounded-3xl border border-dashed px-4 py-4 text-sm text-slate-100 transition" x-bind:class="dragging ? 'border-sky-400 bg-sky-400/10' : 'border-white/15 bg-white/5'">
              <div class="font-extrabold">Arrastrá acá o tocá</div>
              <div class="text-xs text-slate-200/80 mt-1">JPG, PNG, WEBP o GIF</div>
            </div>
            <input type="file" name="image" accept="image/*" required x-ref="input" class="sr-only">
          </label>

          <button type="submit" class="magic-btn w-full rounded-2xl px-4 py-3 bg-gradient-to-r from-sky-500 via-violet-600 to-fuchsia-600 text-white font-extrabold">
            Guardar imagen
          </button>
        </form>
      </div>

      <div class="mt-4" x-show="tool === 'text'">
        <form method="post" action="/page/update" class="space-y-3" x-show="side === 'left'">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="book_id" value="<?= htmlspecialchars($bookId, ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="page_id" value="<?= htmlspecialchars($leftPageId, ENT_QUOTES, 'UTF-8') ?>">

          <label class="block">
            <span class="text-sm font-extrabold text-white">Texto</span>
            <textarea name="text" rows="7"
              class="mt-1 w-full rounded-2xl border border-white/10 bg-white/10 text-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-violet-400/20"><?= htmlspecialchars($leftText, ENT_QUOTES, 'UTF-8') ?></textarea>
          </label>

          <button type="submit" class="magic-btn w-full rounded-2xl px-4 py-3 bg-gradient-to-r from-lime-400 via-emerald-500 to-sky-500 text-slate-950 font-extrabold">
            Guardar texto
          </button>
        </form>

        <form method="post" action="/page/update" class="space-y-3" x-show="side === 'right'">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="book_id" value="<?= htmlspecialchars($bookId, ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="page_id" value="<?= htmlspecialchars($rightPageId, ENT_QUOTES, 'UTF-8') ?>">

          <label class="block">
            <span class="text-sm font-extrabold text-white">Texto</span>
            <textarea name="text" rows="7"
              class="mt-1 w-full rounded-2xl border border-white/10 bg-white/10 text-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-violet-400/20"><?= htmlspecialchars($rightText, ENT_QUOTES, 'UTF-8') ?></textarea>
          </label>

          <button type="submit" class="magic-btn w-full rounded-2xl px-4 py-3 bg-gradient-to-r from-lime-400 via-emerald-500 to-sky-500 text-slate-950 font-extrabold">
            Guardar texto
          </button>
        </form>
      </div>

      <?php if ($sheetIndexForActions !== null): ?>
        <?php
          $isFirst = $sheetIndexForActions === 0;
          $isLast = $sheetIndexForActions === $maxSheet;
          $canSwap = $leftPage !== null && $rightPage !== null;
        ?>
        <div class="mt-4 grid grid-cols-2 gap-2">
          <form method="post" action="/sheet/move">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="book_id" value="<?= htmlspecialchars($bookId, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="sheet_index" value="<?= htmlspecialchars((string)$sheetIndexForActions, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="direction" value="up">
            <button type="submit" <?= $isFirst ? 'disabled' : '' ?> class="magic-btn w-full rounded-2xl px-3 py-2 bg-white/10 text-white font-extrabold border border-white/10 <?= $isFirst ? 'opacity-30 cursor-not-allowed' : '' ?>">
              Subir
            </button>
          </form>
          <form method="post" action="/sheet/move">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="book_id" value="<?= htmlspecialchars($bookId, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="sheet_index" value="<?= htmlspecialchars((string)$sheetIndexForActions, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="direction" value="down">
            <button type="submit" <?= $isLast ? 'disabled' : '' ?> class="magic-btn w-full rounded-2xl px-3 py-2 bg-white/10 text-white font-extrabold border border-white/10 <?= $isLast ? 'opacity-30 cursor-not-allowed' : '' ?>">
              Bajar
            </button>
          </form>
        </div>

        <div class="mt-2 grid grid-cols-2 gap-2">
          <form method="post" action="/sheet/swap">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="book_id" value="<?= htmlspecialchars($bookId, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="sheet_index" value="<?= htmlspecialchars((string)$sheetIndexForActions, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" <?= $canSwap ? '' : 'disabled' ?> class="magic-btn w-full rounded-2xl px-3 py-2 bg-sky-500/30 text-white font-extrabold border border-white/10 <?= $canSwap ? '' : 'opacity-30 cursor-not-allowed' ?>">
              Intercambiar
            </button>
          </form>
          <form method="post" action="/sheet/delete" onsubmit="return confirm('¿Eliminar esta hoja (2 páginas)?');">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="book_id" value="<?= htmlspecialchars($bookId, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="sheet_index" value="<?= htmlspecialchars((string)$sheetIndexForActions, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="magic-btn w-full rounded-2xl px-3 py-2 bg-red-500/30 text-white font-extrabold border border-white/10">
              Eliminar hoja
            </button>
          </form>
        </div>
      <?php endif; ?>

      <div class="mt-2 grid grid-cols-2 gap-2">
        <form method="post" action="/page/delete" onsubmit="return confirm('¿Eliminar esta página?');" x-show="side === 'left' && <?= $leftCanDelete ? 'true' : 'false' ?>">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="book_id" value="<?= htmlspecialchars($bookId, ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="page_id" value="<?= htmlspecialchars($leftPageId, ENT_QUOTES, 'UTF-8') ?>">
          <button type="submit" class="magic-btn w-full rounded-2xl px-3 py-2 bg-red-500/30 text-white font-extrabold border border-white/10">
            Eliminar izq
          </button>
        </form>
        <form method="post" action="/page/delete" onsubmit="return confirm('¿Eliminar esta página?');" x-show="side === 'right' && <?= $rightCanDelete ? 'true' : 'false' ?>">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="book_id" value="<?= htmlspecialchars($bookId, ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="page_id" value="<?= htmlspecialchars($rightPageId, ENT_QUOTES, 'UTF-8') ?>">
          <button type="submit" class="magic-btn w-full rounded-2xl px-3 py-2 bg-red-500/30 text-white font-extrabold border border-white/10">
            Eliminar der
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
