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
    : 'min-h-screen bg-gradient-to-b from-orange-50 via-amber-50 to-white text-slate-800';
$containerClass = $isPrint ? 'mx-auto px-0 py-0' : 'max-w-3xl mx-auto px-4 py-10';

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars((string)$title, ENT_QUOTES, 'UTF-8') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="<?= htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8') ?>">
  <div class="<?= htmlspecialchars($containerClass, ENT_QUOTES, 'UTF-8') ?>">
    <?php if (!$isPrint): ?>
      <header class="flex items-center justify-between mb-8">
        <a href="<?= $isAuthed ? '/menu' : '/' ?>" class="select-none">
          <h1 class="text-4xl font-extrabold tracking-wide bg-gradient-to-r from-red-500 via-amber-500 via-lime-500 via-sky-500 to-violet-500 bg-clip-text text-transparent">
            Papelitos
          </h1>
        </a>
        <?php if ($isAuthed): ?>
          <form method="post" action="/logout" class="shrink-0">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="rounded-xl px-4 py-2 bg-slate-900 text-white hover:bg-slate-800 active:bg-slate-950 transition">
              Salir
            </button>
          </form>
        <?php endif; ?>
      </header>

      <?php if (is_string($flashError) && $flashError !== ''): ?>
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-800">
          <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>

      <?php if (is_string($flashSuccess) && $flashSuccess !== ''): ?>
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800">
          <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <?= $content ?>
  </div>
</body>
</html>
