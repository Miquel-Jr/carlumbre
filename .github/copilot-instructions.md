# Copilot Instructions for Carlumbre

## Objetivo
Este proyecto es un sistema de gestion para taller automotriz (PHP MVC ligero + MySQL + Bootstrap).
Cualquier cambio debe mantener compatibilidad con la arquitectura actual y evitar refactors grandes no solicitados.

## Stack y arquitectura
- Backend: PHP 8.x, arquitectura MVC ligera con router propio.
- DB: MySQL.
- Frontend: vistas PHP en `resources/views` con Bootstrap 5 y JS vanilla.
- Rutas: `routes/web.php`.
- Controladores: `app/Controllers`.
- Modelos: `app/Models`.
- Core y helpers: `app/Core`.
- Middleware: `app/Middleware`.

## Comandos utiles
- Instalar dependencias PHP: `composer install`
- Instalar frontend: `npm install`
- Levantar app: `composer start`
- Assets en desarrollo: `npm run dev`
- Build frontend: `npm run build`

## Convenciones de cambios
- Mantener cambios pequenos y localizados.
- No cambiar nombres de rutas, controladores ni vistas salvo requerimiento explicito.
- Reutilizar helpers/parciales existentes antes de agregar nuevas utilidades.
- Preservar mensajes y etiquetas en espanol en UI.
- Si agregas endpoint nuevo, registrar ruta en `routes/web.php`.
- Si agregas logica de negocio, priorizarla en Controller/Model, no en la vista.

## Seguridad y validaciones
- Validar permisos con middleware en endpoints nuevos o modificados.
- Validar entrada de `$_GET` y `$_POST` antes de usarla.
- Escapar salida en vistas con `htmlspecialchars`.
- No confiar solo en validaciones de frontend; reforzar siempre en backend.

## Reglas de UI
- Mantener estilo Bootstrap existente.
- Usar SweetAlert para confirmaciones y errores interactivos.
- Mantener consistencia visual y textos con el modulo intervenido.

## Regla importante: notificaciones WhatsApp
- Estados validos en practica: `pending`, `sent`, `failed`.
- Puede existir `opened` como legado; tratarlo como equivalente a `sent` en listados/filtros/estadisticas.
- Si una notificacion esta enviada (`sent` o legado `opened`), no debe permitir edicion de mensaje.
- Flujo esperado:
  - Antes de abrir WhatsApp, registrar o mantener notificacion en `pending`.
  - Al volver a la web, confirmar envio para marcar `sent` o dejar `pending`.

## Datos y SQL
- Evitar migraciones destructivas sin solicitud explicita.
- Si propones cambios de datos (por ejemplo normalizar `opened` -> `sent`), sugerir script reversible.

## Que evitar
- No introducir frameworks nuevos.
- No mover carpetas o reestructurar el proyecto.
- No mezclar cambios de estilo/format en archivos no relacionados.
- No romper compatibilidad de flujos existentes de usuarios, roles, quotes, work orders, billing o warranties.

## Como responder cambios grandes
Cuando la solicitud implique cambios amplios:
1. Enumerar archivos afectados.
2. Explicar impacto funcional.
3. Proponer plan por etapas.
4. Implementar primero una version minima funcional y segura.
