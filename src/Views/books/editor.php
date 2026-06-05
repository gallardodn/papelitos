<?php
declare(strict_types=1);

$book = $book ?? [];
$cover = $cover ?? null;
$backCover = $backCover ?? null;
$sheets = $sheets ?? [];

$bookId = (string)($book['id'] ?? '');

?>
<div class="bg-white/90 backdrop-blur rounded-3xl shadow-xl shadow-orange-100 border border-orange-100 p-8">
  <div class="flex items-start justify-between gap-4 mb-6">
    <div>
      <h2 class="text-2xl font-bold mb-1"><?= htmlspecialchars((string)($book['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
      <div class="text-slate-600"><?= htmlspecialchars((string)($book['author'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <div class="flex gap-2">
      <a href="/print?book_id=<?= urlencode($bookId) ?>" class="rounded-2xl px-4 py-2 bg-orange-500 text-white hover:bg-orange-600 active:bg-orange-700 transition">
        Imprimir
      </a>
      <a href="/library" class="rounded-2xl px-4 py-2 bg-slate-900 text-white hover:bg-slate-800 active:bg-slate-950 transition">
        Biblioteca
      </a>
      <a href="/menu" class="rounded-2xl px-4 py-2 bg-slate-900 text-white hover:bg-slate-800 active:bg-slate-950 transition">
        Menú
      </a>
    </div>
  </div>

  <form method="post" action="/sheet/add" class="mb-8">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="book_id" value="<?= htmlspecialchars($bookId, ENT_QUOTES, 'UTF-8') ?>">
    <button type="submit" class="rounded-2xl px-5 py-3 bg-orange-500 text-white font-semibold hover:bg-orange-600 active:bg-orange-700 transition">
      Agregar hoja (2 páginas)
    </button>
  </form>

  <?php
    $renderHalf = function (?array $p, string $label, ?int $number = null) use ($bookId): void {
        if (!is_array($p)) {
            echo '<div class="h-full rounded-2xl border border-slate-200 bg-slate-50 p-5 text-slate-600">Vacío</div>';
            return;
        }
        $pageId = (string)($p['id'] ?? '');
        $imagePath = (string)($p['image_path'] ?? '');
        $text = (string)($p['text'] ?? '');
        $pageIndex = (int)($p['page_index'] ?? -9999);
        $title = $number === null ? $label : ($label . ' ' . $number);
        $canDelete = $number !== null && $pageIndex >= 0;
  ?>
        <div class="h-full rounded-2xl border border-slate-200 bg-white p-5">
          <div class="text-xs font-semibold text-slate-500 mb-2"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></div>
          <?php if ($imagePath !== ''): ?>
            <div class="mb-3">
              <img src="/u/<?= htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') ?>" alt="Imagen" class="w-full max-h-52 object-contain rounded-2xl border border-slate-100 bg-white">
            </div>
          <?php else: ?>
            <div class="mb-3 h-36 rounded-2xl border border-dashed border-slate-200 bg-slate-50"></div>
          <?php endif; ?>
          <div class="min-h-24 whitespace-pre-wrap text-slate-800 leading-relaxed text-sm">
            <?= htmlspecialchars($text === '' ? '...' : $text, ENT_QUOTES, 'UTF-8') ?>
          </div>
        </div>

        <div class="mt-4 space-y-4">
          <form method="post" action="/page/image" enctype="multipart/form-data" class="space-y-3" x-data="{ dragging: false, submitIfFile(e){ const files = e?.dataTransfer?.files; if(!files || files.length === 0) return; this.$refs.input.files = files; this.$refs.form.submit(); } }" x-ref="form" x-on:dragover.prevent="dragging = true" x-on:dragleave.prevent="dragging = false" x-on:drop.prevent="dragging = false; submitIfFile($event)">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="book_id" value="<?= htmlspecialchars($bookId, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="page_id" value="<?= htmlspecialchars($pageId, ENT_QUOTES, 'UTF-8') ?>">

            <label class="block cursor-pointer">
              <span class="text-sm font-semibold text-slate-700">Imagen</span>
              <div class="mt-2 rounded-3xl border border-dashed px-4 py-4 text-sm text-slate-700 transition"
                :class="dragging ? 'border-sky-400 bg-sky-50' : 'border-slate-200 bg-white'">
                <div class="font-semibold">Arrastrá acá o tocá</div>
                <div class="text-xs text-slate-500 mt-1">JPG, PNG, WEBP o GIF (máx 10MB)</div>
              </div>
              <input type="file" name="image" accept="image/*" required x-ref="input" class="sr-only">
            </label>

            <button type="submit" class="rounded-2xl px-4 py-2 bg-sky-600 text-white font-semibold hover:bg-sky-700 active:bg-sky-800 transition">
              <?= $imagePath !== '' ? 'Reemplazar imagen' : 'Subir imagen' ?>
            </button>
          </form>

          <form method="post" action="/page/update" class="space-y-3">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="book_id" value="<?= htmlspecialchars($bookId, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="page_id" value="<?= htmlspecialchars($pageId, ENT_QUOTES, 'UTF-8') ?>">

            <label class="block">
              <span class="text-sm font-semibold text-slate-700">Texto</span>
              <textarea name="text" rows="4"
                class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 focus:outline-none focus:ring-4 focus:ring-orange-100"><?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>

            <button type="submit" class="rounded-2xl px-4 py-2 bg-slate-900 text-white font-semibold hover:bg-slate-800 active:bg-slate-950 transition">
              Guardar texto
            </button>
          </form>

          <?php if ($canDelete): ?>
            <form method="post" action="/page/delete" onsubmit="return confirm('¿Eliminar esta página?');">
              <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="book_id" value="<?= htmlspecialchars($bookId, ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="page_id" value="<?= htmlspecialchars($pageId, ENT_QUOTES, 'UTF-8') ?>">
              <button type="submit" class="rounded-2xl px-4 py-2 bg-red-600 text-white font-semibold hover:bg-red-700 active:bg-red-800 transition">
                Eliminar página
              </button>
            </form>
          <?php endif; ?>
        </div>
  <?php
    };
  ?>

  <div class="space-y-10">
    <div>
      <div class="flex items-center justify-between gap-4 mb-3">
        <div class="text-lg font-extrabold">Hoja 0</div>
        <div class="text-sm text-slate-600">Tapa / Contratapa</div>
      </div>

      <div class="rounded-3xl border border-slate-200 bg-white p-4">
        <div class="aspect-[210/297] w-full border border-slate-200 rounded-2xl overflow-hidden bg-white">
          <div class="grid grid-cols-2 h-full divide-x divide-slate-200">
            <div class="p-4 overflow-auto">
              <?php $renderHalf(is_array($cover) ? $cover : null, 'Tapa'); ?>
            </div>
            <div class="p-4 overflow-auto">
              <?php $renderHalf(is_array($backCover) ? $backCover : null, 'Contratapa'); ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div>
      <div class="flex items-center justify-between gap-4 mb-3">
        <div class="text-lg font-extrabold">Hojas</div>
        <div class="text-sm text-slate-600">Cada hoja A4 está dividida a la mitad (dos páginas).</div>
      </div>

      <?php if (!is_array($sheets) || count($sheets) === 0): ?>
        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-slate-700">
          Todavía no hay hojas internas. Usá “Agregar hoja”.
        </div>
      <?php else: ?>
        <div class="space-y-8">
          <?php foreach ($sheets as $s): ?>
            <?php
              $sheetIndex = (int)($s['sheet_index'] ?? 0);
              $left = is_array($s['left'] ?? null) ? $s['left'] : null;
              $right = is_array($s['right'] ?? null) ? $s['right'] : null;
              $nLeft = $sheetIndex * 2 + 1;
              $nRight = $sheetIndex * 2 + 2;
              $isFirst = $sheetIndex === 0;
              $isLast = $sheetIndex === (count($sheets) - 1);
              $canSwap = $left !== null && $right !== null;
            ?>
            <div class="rounded-3xl border border-slate-100 bg-gradient-to-br from-orange-50 to-white p-6">
              <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div class="text-lg font-extrabold">Hoja <?= $sheetIndex + 1 ?> (Páginas <?= $nLeft ?> y <?= $nRight ?>)</div>
                <div class="flex gap-2">
                  <form method="post" action="/sheet/move">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="book_id" value="<?= htmlspecialchars($bookId, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="sheet_index" value="<?= htmlspecialchars((string)$sheetIndex, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="direction" value="up">
                    <button type="submit" <?= $isFirst ? 'disabled' : '' ?> class="rounded-2xl px-3 py-2 bg-slate-900 text-white hover:bg-slate-800 active:bg-slate-950 transition <?= $isFirst ? 'opacity-40 cursor-not-allowed' : '' ?>">
                      Subir
                    </button>
                  </form>
                  <form method="post" action="/sheet/move">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="book_id" value="<?= htmlspecialchars($bookId, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="sheet_index" value="<?= htmlspecialchars((string)$sheetIndex, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="direction" value="down">
                    <button type="submit" <?= $isLast ? 'disabled' : '' ?> class="rounded-2xl px-3 py-2 bg-slate-900 text-white hover:bg-slate-800 active:bg-slate-950 transition <?= $isLast ? 'opacity-40 cursor-not-allowed' : '' ?>">
                      Bajar
                    </button>
                  </form>
                  <form method="post" action="/sheet/swap">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="book_id" value="<?= htmlspecialchars($bookId, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="sheet_index" value="<?= htmlspecialchars((string)$sheetIndex, ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" <?= $canSwap ? '' : 'disabled' ?> class="rounded-2xl px-3 py-2 bg-sky-600 text-white hover:bg-sky-700 active:bg-sky-800 transition <?= $canSwap ? '' : 'opacity-40 cursor-not-allowed' ?>">
                      Intercambiar
                    </button>
                  </form>
                  <form method="post" action="/sheet/delete" onsubmit="return confirm('¿Eliminar esta hoja (2 páginas)?');">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="book_id" value="<?= htmlspecialchars($bookId, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="sheet_index" value="<?= htmlspecialchars((string)$sheetIndex, ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="rounded-2xl px-3 py-2 bg-red-600 text-white hover:bg-red-700 active:bg-red-800 transition">
                      Eliminar
                    </button>
                  </form>
                </div>
              </div>

              <div class="rounded-3xl border border-slate-200 bg-white p-4">
                <div class="aspect-[210/297] w-full border border-slate-200 rounded-2xl overflow-hidden bg-white">
                  <div class="grid grid-cols-2 h-full divide-x divide-slate-200">
                    <div class="p-4 overflow-auto">
                      <?php $renderHalf($left, 'Página', $nLeft); ?>
                    </div>
                    <div class="p-4 overflow-auto">
                      <?php $renderHalf($right, 'Página', $nRight); ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
