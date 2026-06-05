<?php
declare(strict_types=1);

$email = $email ?? '';

?>
<div class="magic-card h-full w-full flex flex-col justify-center p-6 sm:p-10">
  <div class="max-w-md mx-auto w-full">
    <h2 class="text-3xl font-extrabold mb-2 text-white">Ingresar</h2>
    <p class="text-slate-200 mb-6">Entrá con tu correo y contraseña.</p>

    <form method="post" action="/login" class="space-y-4">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

      <label class="block">
        <span class="text-sm font-semibold text-slate-100">Correo electrónico</span>
        <input type="email" name="email" value="<?= htmlspecialchars((string)$email, ENT_QUOTES, 'UTF-8') ?>" autocomplete="email" required
          class="mt-1 w-full rounded-2xl border border-white/10 bg-white/10 text-white placeholder:text-slate-300 px-4 py-3 focus:outline-none focus:ring-4 focus:ring-violet-400/20">
      </label>

      <label class="block">
        <span class="text-sm font-semibold text-slate-100">Contraseña</span>
        <input type="password" name="password" autocomplete="current-password" required
          class="mt-1 w-full rounded-2xl border border-white/10 bg-white/10 text-white placeholder:text-slate-300 px-4 py-3 focus:outline-none focus:ring-4 focus:ring-violet-400/20">
      </label>

      <button type="submit"
        class="magic-btn w-full rounded-2xl px-4 py-3 bg-gradient-to-r from-violet-600 via-fuchsia-600 to-sky-600 text-white font-extrabold tracking-wide transition">
        Entrar
      </button>
    </form>

    <div class="mt-6 text-sm text-slate-200">
      ¿No tenés cuenta?
      <a class="font-extrabold text-sky-300 hover:text-sky-200 magic-btn px-2 py-1 rounded-xl inline-flex items-center" href="/register">Crear cuenta</a>
    </div>

    <div class="mt-8 flex items-center justify-end">
      <a href="/register" class="magic-arrow magic-btn bg-white/10 text-white magic-glass" aria-label="Siguiente">
        →
      </a>
    </div>
  </div>
</div>
