<?php
declare(strict_types=1);

$book = $book ?? [];
$cover = $cover ?? null;
$backCover = $backCover ?? null;
$sheets = $sheets ?? [];

$bookId = (string)($book['id'] ?? '');

$renderHalf = function (?array $p, string $label, ?int $number = null): void {
    if (!is_array($p)) {
        echo '<div class="h-full border border-slate-200 bg-white"></div>';
        return;
    }
    $imagePath = (string)($p['image_path'] ?? '');
    $text = (string)($p['text'] ?? '');
    $title = $number === null ? $label : ($label . ' ' . $number);
?>
    <div class="h-full p-8">
      <div class="text-sm font-semibold text-slate-500 mb-3"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></div>
      <?php if ($imagePath !== ''): ?>
        <div class="mb-5">
          <img src="/u/<?= htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') ?>" alt="Imagen" class="w-full max-h-[55%] object-contain">
        </div>
      <?php endif; ?>
      <div class="whitespace-pre-wrap text-slate-900 leading-relaxed">
        <?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?>
      </div>
    </div>
<?php
};

?>
<style>
  @media print {
    .no-print { display: none !important; }
    body { margin: 0; }
    .print-page { page-break-after: always; }
  }
</style>

<div class="no-print flex items-center justify-between px-4 py-4 border-b border-slate-200 bg-white">
  <div class="font-extrabold text-slate-900"><?= htmlspecialchars((string)($book['title'] ?? 'Papelitos'), ENT_QUOTES, 'UTF-8') ?></div>
  <div class="flex gap-2">
    <a href="/edit?book_id=<?= urlencode($bookId) ?>" class="rounded-xl px-4 py-2 bg-slate-900 text-white">Volver</a>
    <a href="/pdf?book_id=<?= urlencode($bookId) ?>" class="rounded-xl px-4 py-2 bg-sky-600 text-white">Exportar PDF</a>
    <button type="button" onclick="window.print()" class="rounded-xl px-4 py-2 bg-orange-500 text-white">Imprimir</button>
  </div>
</div>

<div class="px-4 py-6 bg-white">
  <div class="print-page mx-auto w-[210mm] h-[297mm] border border-slate-300 bg-white">
    <div class="grid grid-cols-2 h-full divide-x divide-slate-300">
      <div class="h-full">
        <?php $renderHalf(is_array($cover) ? $cover : null, 'Tapa'); ?>
      </div>
      <div class="h-full">
        <?php $renderHalf(is_array($backCover) ? $backCover : null, 'Contratapa'); ?>
      </div>
    </div>
  </div>

  <?php if (is_array($sheets) && count($sheets) > 0): ?>
    <?php foreach ($sheets as $s): ?>
      <?php
        $sheetIndex = (int)($s['sheet_index'] ?? 0);
        $left = is_array($s['left'] ?? null) ? $s['left'] : null;
        $right = is_array($s['right'] ?? null) ? $s['right'] : null;
        $nLeft = $sheetIndex * 2 + 1;
        $nRight = $sheetIndex * 2 + 2;
      ?>
      <div class="print-page mx-auto w-[210mm] h-[297mm] border border-slate-300 bg-white mt-6">
        <div class="grid grid-cols-2 h-full divide-x divide-slate-300">
          <div class="h-full">
            <?php $renderHalf($left, 'Página', $nLeft); ?>
          </div>
          <div class="h-full">
            <?php $renderHalf($right, 'Página', $nRight); ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
