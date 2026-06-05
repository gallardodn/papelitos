<?php
declare(strict_types=1);

?>
<div class="bg-white/90 backdrop-blur rounded-3xl shadow-xl shadow-orange-100 border border-orange-100 p-8">
  <h2 class="text-2xl font-bold mb-2">Menú</h2>
  <p class="text-slate-600 mb-6">Elegí qué querés hacer.</p>

  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <a href="/new-book" class="rounded-3xl border border-slate-100 bg-gradient-to-br from-orange-50 to-white p-6 hover:shadow-md transition">
      <div class="text-lg font-extrabold">Nuevo libro</div>
      <div class="text-sm text-slate-600 mt-1">Crear un libro desde cero.</div>
    </a>
    <a href="/library" class="rounded-3xl border border-slate-100 bg-gradient-to-br from-sky-50 to-white p-6 hover:shadow-md transition">
      <div class="text-lg font-extrabold">Biblioteca</div>
      <div class="text-sm text-slate-600 mt-1">Ver los libros guardados.</div>
    </a>
    <a href="/edit" class="rounded-3xl border border-slate-100 bg-gradient-to-br from-lime-50 to-white p-6 hover:shadow-md transition">
      <div class="text-lg font-extrabold">Editar</div>
      <div class="text-sm text-slate-600 mt-1">Modificar un libro existente.</div>
    </a>
    <a href="/help" class="rounded-3xl border border-slate-100 bg-gradient-to-br from-violet-50 to-white p-6 hover:shadow-md transition">
      <div class="text-lg font-extrabold">Ayuda</div>
      <div class="text-sm text-slate-600 mt-1">Cómo usar Papelitos.</div>
    </a>
  </div>
</div>

