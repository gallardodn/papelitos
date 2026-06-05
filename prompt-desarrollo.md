# 🧿 Papelitos — Creador de libros infantiles imprimibles

> Prompt para agente de desarrollo — idea concebida por Diego Nicolás

---

## 📋 Resumen

Aplicación web local para crear, editar e imprimir libros de cuentos infantiles personalizados. Los niños pueden dibujar sus propias historias hoja por hoja usando un modal de dibujo a pantalla completa, y los padres pueden agregar textos narrativos, importar imágenes y generar el libro en formato imprimible A4 o PDF. Incluye biblioteca visual con estantería animada, login de usuarios y plantillas predeterminadas.

---

## 👥 Audiencia

- **Padres** (crean y editan libros para sus hijos)
- **Niños** (dibujan sus historias directamente en la app, leen los libros terminados)
- Potencialmente cualquier persona que quiera crear libros infantiles personalizados

---

## 🎨 Identidad visual

- **Título de la app:** letras de colores formando un arcoíris
- **Estilo general:** animado, colorido, enfocado a niños
- **Todas las pantallas** deben tener animaciones y estética infantil
- **Tipografía:** amigable, redondeada, legible para niños

---

## 🗺️ Flujo de navegación

```
1. Login / Registro
       ↓
2. Menú principal
   ├── 📕 Nuevo libro
   ├── 📚 Biblioteca
   ├── ✏️ Editar
   └── ❓ Ayuda
       ↓
3. Editor de libro (página por página)
   ├── Modal de dibujo a pantalla completa
   ├── Importar imágenes
   ├── Agregar texto narrativo
   └── Vista previa de página A4
```

---

## 🧱 Funcionalidades detalladas

### 1. Login / Registro

- Pantalla inicial de la aplicación
- Crear cuenta con nombre de usuario y contraseña
- Inicio de sesión con usuario y contraseña
- Almacenamiento local de credenciales (SQLite vía PHP)
- Diseño infantil: animado, colorido, con el título arcoíris

### 2. Menú principal

Cuatro opciones grandes y visibles (adaptadas para clics táctiles):

| Opción | Función |
|---|---|
| 📕 **Nuevo libro** | Crear un libro desde cero (pedir título y autor) |
| 📚 **Biblioteca** | Estantería visual con tapitas de libros guardados |
| ✏️ **Editar** | Seleccionar un libro existente para modificarlo |
| ❓ **Ayuda** | Guía de uso / introducción a la aplicación |

### 3. Biblioteca (estantería visual)

- Representación visual tipo **estantería de libros** con tapitas
- Cada libro se muestra con su **portada, título y autor**
- **Animación al desplegar:** al hacer clic en un libro, transición suave para abrirlo
- **Resaltado hover:** al pasar el mouse sobre un libro, efecto visual (agrandar, brillo, etc.)
- **Plantillas predeterminadas:** libros de ejemplo incluidos con el proyecto para mostrar el formato y servir como punto de partida
- Separación por volúmenes / colecciones (opcional)

### 4. Editor de libro (página por página)

- Interfaz tipo "página en blanco" con formato A4
- Agregar, eliminar y reordenar páginas
- Cada página puede contener:
  - Un **dibujo infantil** (creado en el modal de dibujo)
  - Una **imagen importada**
  - **Texto narrativo** debajo del dibujo/imagen

### 5. Modal de dibujo a pantalla completa

- Se despliega al hacer clic en "Dibujar" o "Editar dibujo" de una página
- **Ocupa toda la pantalla** para evitar clics accidentales que rompan el flujo (accesibilidad infantil)
- Herramientas de dibujo:
  - 🖊️ **Pincel** (tamaño ajustable)
  - 🎨 **Selector de color**
  - 🪣 **Rellenar** (bucket fill)
  - 🖌️ **Pintar** (paintbrush)
- **Botón grande de guardado** adaptado para niños (texto grande, color llamativo)
- **Dibujar sobre imágenes importadas:** permite cargar una imagen de fondo y dibujar encima (tipo colorear o agregar elementos sobre una base)
- Al guardar, el dibujo se integra en la página del libro

### 6. Importación de imágenes

- Arrastrar y soltar imágenes desde el explorador de archivos
- Seleccionar archivo mediante botón de carga
- Previsualización en miniatura al cargar
- Colocar la imagen en la página (arrastrar a posición)
- Redimensionar manteniendo proporción

### 7. Texto narrativo

- Área de texto debajo del dibujo/imagen en cada página
- Elegir **ubicación del texto** (debajo, al costado, etc.)
- Elegir **tipo de letra** (tipografía infantil, redondeada, legible)
- Tamaño de letra ajustable
- Color del texto

### 8. Exportación

- 📄 **Exportar a formato imprimible A4** (optimizado para impresión en casa)
- 📑 **Exportar a PDF** (para compartir digitalmente o llevar a imprimir)
- Vista previa de cómo queda cada página antes de exportar

### 9. Consideraciones de recursos

| Funcionalidad | Tipo |
|---|---|
| Importación local de imágenes | ✅ Gratuito |
| Edición de texto | ✅ Gratuito |
| Dibujo infantil | ✅ Gratuito |
| Biblioteca y estantería | ✅ Gratuito |
| Exportación PDF / impresión | ✅ Gratuito |
| Generación de imágenes con IA | 💰 Pago (API externa) |
| Generación de cuentos con IA | 💰 Pago (API externa) |
| Voces TTS premium | 💰 Pago (API externa) |

La app debe **distinguir visualmente** qué funcionalidades son gratuitas y cuáles requieren recursos externos con costo (íconos, labels, etc.)

---

## 💻 Especificaciones técnicas

| Aspecto | Detalle |
|---|---|
| **Entorno** | Local (PC Windows) — 100% local, sin nube |
| **Backend** | PHP (preferencia del creador) |
| **Frontend** | JavaScript + CSS + framework a elección del desarrollador (se recomienda Bootstrap para facilidad visual o Tailwind + Alpine.js para algo moderno y liviano) |
| **Persistencia (fase local)** | SQLite vía PDO (local, sin servidor externo, zero config) |
| **Migración futura** | Todo el acceso a base de datos debe usar **PDO** para poder migrar a MySQL/MariaDB en el futuro sin reescribir código. El día que se requiera versión comercial, se cambia la cadena de conexión y listo. |
| **Separación datos / código** | Los datos de usuario (SQLite, imágenes, dibujos) deben persistir en una carpeta separada del código, ignorada por git mediante `.gitignore`. El repositorio solo contiene código fuente. |
| **Lienzo de dibujo** | Canvas HTML5 + librería de dibujo (Fabric.js recomendado por su robustez) |
| **PDF** | Librería PHP para generación de PDF (Dompdf, TCPDF o similar) |
| **Video (futuro, Fase 3)** | FFmpeg + TTS (no implementar en esta fase) |
| **Sin vendor lock-in** | Stack 100% open source. PDO permite cambiar de motor de base de datos sin reescribir código. |

---

## 🧪 Requisitos funcionales (checklist)

### Login y registro
- [ ] Pantalla de login con usuario y contraseña
- [ ] Registro de nuevo usuario
- [ ] Persistencia local de credenciales (SQLite)
- [ ] Diseño infantil animado

### Menú principal
- [ ] 4 opciones grandes: Nuevo libro, Biblioteca, Editar, Ayuda
- [ ] Título arcoíris animado
- [ ] Diseño responsive y touch-friendly

### Biblioteca
- [ ] Estantería visual con tapitas de libros
- [ ] Animación al desplegar un libro
- [ ] Resaltado hover sobre cada libro
- [ ] Plantillas predeterminadas incluidas
- [ ] Separación por volúmenes / colecciones

### Editor de libro
- [ ] Crear nuevo libro con título y autor
- [ ] Paginación (agregar, eliminar, reordenar páginas)
- [ ] Cada página: dibujo + texto
- [ ] Formato A4

### Modal de dibujo
- [ ] Pantalla completa
- [ ] Herramientas: pincel, color, rellenar, pintar
- [ ] Botón grande de guardado
- [ ] Dibujar sobre imágenes importadas
- [ ] Sin clics accidentales que rompan el flujo

### Importación de imágenes
- [ ] Drag & drop
- [ ] Botón de selección de archivo
- [ ] Previsualización
- [ ] Colocar y redimensionar en página

### Texto narrativo
- [ ] Área de texto debajo del dibujo
- [ ] Selección de ubicación del texto
- [ ] Selección de tipo de letra (tipografía infantil)
- [ ] Ajuste de tamaño y color

### Exportación
- [ ] Vista previa de página
- [ ] Exportar a formato imprimible A4
- [ ] Exportar a PDF

### General
- [ ] Diseño animado y colorido (enfocado a niños)
- [ ] Diferenciación visual entre funciones gratuitas y pagas
- [ ] Persistencia local de todos los datos

### Estructura del proyecto y despliegue
- [ ] Datos de usuario (SQLite, imágenes subidas, dibujos) guardados en carpeta separada del código
- [ ] `.gitignore` configurado para excluir datos personales
- [ ] La aplicación debe generar archivos de datos por defecto al ejecutarse si no existen
- [ ] El repositorio debe poder clonarse y ejecutarse sin datos preexistentes

---

## 🧠 Notas de contexto

- Esta app nace del deseo de Diego de crear cuentos personalizados para sus hijos, combinando dibujos hechos por los niños con textos narrativos.
- Los niños deben poder usar la app de forma autónoma (interfaz simple, botones grandes, modal full screen para evitar salidas accidentales).
- "Que los chicos dibujen y los padres armen el libro" es el espíritu.
- Nombre del proyecto: **Papelitos**.
- Este prompt es para un agente de desarrollo externo. El creador no codea directamente, solo concibe y documenta las ideas.
- Fase 3 (video narrado con TTS) es a largo plazo. No implementar ahora.
- **IMPORTANTE:** Separar el área de desarrollo (código que va a git) del área de producción local (datos personales que no deben subirse al repositorio). Los datos de usuario se generan al ejecutar la app y no deben estar trackeados en git. Especificar con `.gitignore` y estructura de carpetas.

---

## 📁 Archivos relacionados

- Idea original documentada en: `C:\Proyectos\Papelitos\prompt-desarrollo.md` (este archivo)
- Este prompt está listo para ser entregado a un agente de desarrollo externo.
