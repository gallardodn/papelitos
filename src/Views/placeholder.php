<?php
declare(strict_types=1);

$heading = $heading ?? 'Próximamente';
$message = $message ?? 'Esta sección está en construcción.';
$backTo = $backTo ?? '/menu';

?>
<div class="magic-card h-full w-full flex flex-col justify-center p-6 sm:p-10">
  <div class="max-w-lg mx-auto w-full text-center">
    <h2 class="text-3xl font-extrabold mb-3 text-white"><?= htmlspecialchars((string)$heading, ENT_QUOTES, 'UTF-8') ?></h2>
    <p class="text-slate-200 mb-8"><?= htmlspecialchars((string)$message, ENT_QUOTES, 'UTF-8') ?></p>
    <a href="<?= htmlspecialchars((string)$backTo, ENT_QUOTES, 'UTF-8') ?>" class="magic-btn inline-flex items-center rounded-2xl px-6 py-3 bg-white/10 text-white magic-glass font-extrabold transition">
      Volver
    </a>
  </div>
</div>
