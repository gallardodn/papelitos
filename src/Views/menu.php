<?php
declare(strict_types=1);

?>
<div class="magic-card h-full w-full p-6 sm:p-10 flex flex-col">
  <div class="shrink-0">
    <h2 class="text-3xl font-extrabold mb-2 text-white">Menú</h2>
    <p class="text-slate-200 mb-6">Elegí qué querés hacer.</p>
  </div>

  <div class="flex-1 min-h-0 grid grid-cols-1 sm:grid-cols-2 gap-4 content-start">
    <a href="/new-book" class="magic-btn rounded-3xl border border-white/10 bg-gradient-to-br from-orange-500/20 via-white/5 to-white/5 p-6">
      <div class="text-xl font-extrabold text-white">Nuevo libro</div>
      <div class="text-sm text-slate-200 mt-1">Crear un libro desde cero.</div>
    </a>
    <a href="/library" class="magic-btn rounded-3xl border border-white/10 bg-gradient-to-br from-sky-500/20 via-white/5 to-white/5 p-6">
      <div class="text-xl font-extrabold text-white">Biblioteca</div>
      <div class="text-sm text-slate-200 mt-1">Ver los libros guardados.</div>
    </a>
    <a href="/edit" class="magic-btn rounded-3xl border border-white/10 bg-gradient-to-br from-lime-500/20 via-white/5 to-white/5 p-6">
      <div class="text-xl font-extrabold text-white">Editar</div>
      <div class="text-sm text-slate-200 mt-1">Modificar un libro existente.</div>
    </a>
    <a href="/help" class="magic-btn rounded-3xl border border-white/10 bg-gradient-to-br from-violet-500/20 via-white/5 to-white/5 p-6">
      <div class="text-xl font-extrabold text-white">Ayuda</div>
      <div class="text-sm text-slate-200 mt-1">Cómo usar Papelitos.</div>
    </a>
  </div>

  <div class="mt-6 flex items-center justify-between shrink-0">
    <a href="/new-book" class="magic-arrow magic-btn bg-white/10 text-white magic-glass" aria-label="Anterior">
      ←
    </a>
    <a href="/library" class="magic-arrow magic-btn bg-white/10 text-white magic-glass" aria-label="Siguiente">
      →
    </a>
  </div>
</div>
