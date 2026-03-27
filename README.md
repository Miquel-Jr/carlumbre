# Carlumbre App

Sistema web de gestión para taller automotriz con módulos de clientes, autos, presupuestos, órdenes de trabajo, facturación, garantías, notificaciones y usuarios con permisos por rol.

## 1) Stack tecnológico

- Backend: PHP (arquitectura MVC ligera, enrutador propio)
- Base de datos: MySQL
- Frontend: HTML + Bootstrap 5 + JavaScript
- Build de assets: Vite (Node.js)
- Librerías principales:
  - `vlucas/phpdotenv` (variables de entorno)
  - `dompdf/dompdf` (PDF de presupuestos)
  - `cloudinary/cloudinary_php` (almacenamiento de imágenes)
  - `sweetalert2` (alertas y confirmaciones)

## 2) Estructura del proyecto

```text
carlumbre/
├─ app/
│  ├─ Controllers/        # Controladores por módulo
│  ├─ Models/             # Acceso a datos y lógica de dominio
│  ├─ Core/               # Router, DB, helpers, integraciones core
│  └─ Middleware/         # Auth, Guest y permisos
├─ config/                # Configuración de DB, menú, permisos y servicios externos
├─ public/                # Front controller (index.php) y assets públicos
├─ resources/
│  ├─ views/              # Vistas por módulo
│  ├─ css/
│  └─ js/
├─ routes/
│  └─ web.php             # Definición de rutas GET/POST
├─ vendor/                # Dependencias Composer
├─ composer.json
├─ package.json
└─ vite.config.js
```

## 3) Arquitectura y flujo

- Punto de entrada: `public/index.php`
  - Carga autoload de Composer
  - Carga `.env` (si existe)
  - Inicia sesión
  - Registra rutas y resuelve request en `App\Core\Router`
- Enrutado: `routes/web.php`
- Middleware:
  - `AuthMiddleware`: exige sesión activa
  - `GuestMiddleware`: restringe acceso a login para usuarios autenticados
  - `PermissionMiddleware`: valida permisos por rol
- Helpers globales (`app/Core/Helpers.php`):
  - render de vistas
  - redirecciones
  - armado de menú por permisos
  - etiquetas legibles de permisos

## 4) Módulos funcionales

### 4.1 Autenticación y perfil
- Login/logout
- Perfil de usuario
- Cambio de contraseña

### 4.2 Gestión de usuarios y roles
- CRUD de usuarios
- Asignación de rol por usuario
- Gestión de permisos por rol
- Menú dinámico según permisos del usuario logueado

### 4.3 Clientes y autos
- CRUD de clientes
- CRUD de autos por cliente
- Gestión de fotos del auto

### 4.4 Servicios y productos
- Catálogo de servicios del taller
- Catálogo de productos/autopartes
- Soporte de garantía por servicio (`has_warranty`, `warranty_time_base`)

### 4.5 Presupuestos
- Creación/edición de presupuestos
- Ítems de servicio y producto
- Aprobación/rechazo de presupuesto
- Exportación PDF

### 4.6 Órdenes de trabajo (OT)
- Generación de OT desde presupuesto aprobado
- Actividades de trabajo con estado
- Sincronización de estado de OT

### 4.7 Facturación
- Generación de factura desde OT culminada
- Edición de número real de factura
- Estado de factura (`issued`, `paid`, `cancelled`)

### 4.8 Garantías
- Registro de vigencia de garantía al pasar factura a pagada
- Consulta por factura y tabla global de garantías (`/warranties`)
- Estados de garantía: vigente/vencida

### 4.9 Notificaciones WhatsApp
- Historial de notificaciones
- Reenvío/envío manual
- Edición de mensaje antes de enviar
- Recordatorios automáticos para garantías vencidas

## 5) Rutas principales (resumen)

- Auth: `/`, `/login`, `/logout`
- Dashboard: `/dashboard`
- Perfil: `/profile`
- Usuarios: `/users`, `/users/roles`
- Clientes y autos: `/clients`, `/clients/cars`
- Servicios: `/services`
- Productos: `/products`
- Presupuestos: `/quotes`
- OT: `/work-orders`
- Facturación: `/billing`
- Garantías: `/warranties`
- Notificaciones: `/notifications`

> Ver detalle completo en `routes/web.php`.

## 6) Servicios externos conectados

### 6.1 WhatsApp Cloud API (Meta)
- Uso: envío/reenvío de mensajes desde notificaciones
- Configuración: `config/whatsapp.php`
- Variables de entorno:
  - `WA_PHONE_NUMBER_ID`
  - `WA_ACCESS_TOKEN`
  - `WA_WEBHOOK_VERIFY`
  - `WA_API_VERSION` (en config, default `v21.0`)
- Implementación principal:
  - `app/Core/Whatsapp.php`
  - `app/Models/Whatsapp.php`

### 6.2 Cloudinary
- Uso: almacenamiento/borrado de imágenes
- Configuración: `config/cloudinary.php`
- Variables de entorno:
  - `CLOUDINARY_CLOUD_NAME`
  - `CLOUDINARY_API_KEY`
  - `CLOUDINARY_API_SECRET`
- Implementación: `app/Core/CloudinaryStorage.php`

### 6.3 Motor de base de datos MySQL
- Configuración: `config/database.php`
- Variables de entorno:
  - `DB_HOST`
  - `DB_NAME`
  - `DB_USER`
  - `DB_PASS`

## 7) Variables de entorno sugeridas

Crear archivo `.env` en raíz con al menos:

```env
DB_HOST=127.0.0.1
DB_NAME=carlumbre
DB_USER=root
DB_PASS=

WA_PHONE_NUMBER_ID=
WA_ACCESS_TOKEN=
WA_WEBHOOK_VERIFY=

CLOUDINARY_CLOUD_NAME=
CLOUDINARY_API_KEY=
CLOUDINARY_API_SECRET=
```

## 8) Instalación y ejecución

### Requisitos
- PHP 8.x
- Composer
- MySQL
- Node.js + npm
- Extensión PHP GD (`ext-gd`)

### Pasos

1. Instalar dependencias PHP:
   ```bash
   composer install
   ```

2. Instalar dependencias frontend:
   ```bash
   npm install
   ```

3. Configurar `.env` con credenciales de DB y servicios externos.

4. Ejecutar backend (servidor embebido):
   ```bash
   composer start
   ```

5. (Opcional) Ejecutar assets en desarrollo:
   ```bash
   npm run dev
   ```

## 9) Convenciones del proyecto

- Rutas definidas en `routes/web.php`
- Cada módulo sigue patrón Controller + Model + View
- Vistas reutilizan parciales (`resources/views/partials`)
- Alertas y confirmaciones con SweetAlert
- Seguridad basada en sesión + permisos por rol

## 10) Estado funcional actual (alto nivel)

- Usuarios/roles/permisos: operativo
- Presupuestos/OT/facturación: operativo
- Garantías y recordatorios: operativo
- Notificaciones WhatsApp con edición de mensaje: operativo

---

