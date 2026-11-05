# MULTIVERSE — adaptación a ferreterías multisucursal

Se conserva el diseño React/TanStack Start, navegación horizontal y personalización por tenant. La plataforma general usa la paleta original.

## Cambios
- Identidad MULTIVERSE en interfaz y metadatos.
- Catálogo de productos ferreteros y tenants de ferretería.
- Cuatro roles: superadministrador, administrador, encargado de ventas y encargado de almacén.
- Pantalla de sucursales, centros de distribución y almacenes con datos de demostración.
- Solicitudes de transferencias con aprobación exclusiva del administrador y seguimiento de envío/recepción.
- Módulos de sucursales, almacenes, transferencias, compras, cotizaciones y conteos.
- Navegación horizontal centrada existente, sin introducir un sidebar.

## Limitaciones importantes
El proyecto continúa siendo un prototipo frontend. Los datos y autorizaciones se simulan en el navegador (localStorage). Las existencias actuales son agregadas por tenant; no hay todavía persistencia ni saldos reales por almacén. Los traspasos cambian de estado, pero no mueven inventario real. No debe utilizarse para ventas o inventario de producción. La base de datos MySQL de 55 tablas y las reglas transaccionales requieren implementación posterior en Laravel.

## Ejecutar
`npm install` y `npm run dev`.
