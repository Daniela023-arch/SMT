# MULTIVERSE — interfaz para ferreterías (Laravel + Livewire)

Esta versión parte de SMT(3).zip y adapta el panel de **tenants** a la referencia visual de `multiverse-ferreterias.zip`. No convierte React en una segunda aplicación ni modifica las operaciones Livewire.

## Cambios aplicados
- `resources/views/layouts/app/sidebar.blade.php`: navegación basada en los roles reales `admin`, `ventas` y `almacen`. Se usan exclusivamente rutas que existen en `routes/web.php`. Se conserva el selector de sucursal/almacén para entrar en ventas e inventario.
- `resources/css/app.css`: estilos para `multiverse-workspace`, navegación, tarjetas, animación sutil, estados de foco y modo oscuro.
- `resources/views/{dashboard.blade.php,livewire/establecimientos/index.blade.php,livewire/productos/index.blade.php,livewire/inventario/show.blade.php,livewire/ventas/index.blade.php,livewire/transferencias/index.blade.php}`: alineación de acentos visuales al verde MULTIVERSE, sin reemplazar métodos `wire:*`.
- Tipografía Outfit aplicada exclusivamente en el layout del tenant.

## Superadministrador
No se modificó ningún archivo bajo `app/Livewire/Superadministrador`, `resources/views/livewire/superadministrador` ni `resources/views/layouts/superadministrador.blade.php`.

## Límites de esta entrega
La referencia es un prototipo React que contiene secciones sin implementación equivalente en Laravel (por ejemplo: usuarios del tenant, reportes del tenant y onboarding de pago). No se han inventado rutas o botones sin respaldo de base de datos. Para esas pantallas será necesario implementar Livewire, autorización, rutas y servicios en una fase posterior.

## Instalación
Este ZIP contiene el código fuente, NO `vendor/`, `node_modules/`, `.git/`, el `.env` privado ni una base SQLite local. Para una instalación nueva:

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
# Ajustar DB_* y demás valores en .env
php artisan migrate
npm run build
php artisan serve
```

En Windows PowerShell, reemplaza `cp` por `Copy-Item` si lo necesitas. Si sustituyes el código de tu proyecto existente, **conserva tu propio .env y base de datos**; no vuelvas a generar la APP_KEY de un sistema en producción.

## Verificación recomendada
`php artisan route:list`, `php artisan view:cache`, `php artisan test`, comprobar login de admin/ventas/almacen y permisos de módulos.
