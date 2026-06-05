<?php
declare(strict_types=1);

$email = $email ?? '';

?>
<div class="bg-white/90 backdrop-blur rounded-3xl shadow-xl shadow-orange-100 border border-orange-100 p-8">
  <h2 class="text-2xl font-bold mb-2">Ingresar</h2>
  <p class="text-slate-600 mb-6">Entrá con tu correo y contraseña.</p>

  <form method="post" action="/login" class="space-y-4">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

    <label class="block">
      <span class="text-sm font-semibold text-slate-700">Correo electrónico</span>
      <input type="email" name="email" value="<?= htmlspecialchars((string)$email, ENT_QUOTES, 'UTF-8') ?>" autocomplete="email" required
        class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 focus:outline-none focus:ring-4 focus:ring-orange-100">
    </label>

    <label class="block">
      <span class="text-sm font-semibold text-slate-700">Contraseña</span>
      <input type="password" name="password" autocomplete="current-password" required
        class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 focus:outline-none focus:ring-4 focus:ring-orange-100">
    </label>

    <button type="submit"
      class="w-full rounded-2xl px-4 py-3 bg-orange-500 text-white font-semibold hover:bg-orange-600 active:bg-orange-700 transition">
      Entrar
    </button>
  </form>

  <div class="mt-6 text-sm text-slate-600">
    ¿No tenés cuenta?
    <a class="font-semibold text-orange-700 hover:text-orange-800" href="/register">Crear cuenta</a>
  </div>
</div>
