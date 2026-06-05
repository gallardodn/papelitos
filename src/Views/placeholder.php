<?php
declare(strict_types=1);

$heading = $heading ?? 'Próximamente';
$message = $message ?? 'Esta sección está en construcción.';
$backTo = $backTo ?? '/menu';

?>
<div class="bg-white/90 backdrop-blur rounded-3xl shadow-xl shadow-orange-100 border border-orange-100 p-8">
  <h2 class="text-2xl font-bold mb-2"><?= htmlspecialchars((string)$heading, ENT_QUOTES, 'UTF-8') ?></h2>
  <p class="text-slate-600 mb-6"><?= htmlspecialchars((string)$message, ENT_QUOTES, 'UTF-8') ?></p>
  <a href="<?= htmlspecialchars((string)$backTo, ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center rounded-2xl px-4 py-2 bg-slate-900 text-white hover:bg-slate-800 active:bg-slate-950 transition">
    Volver
  </a>
</div>

