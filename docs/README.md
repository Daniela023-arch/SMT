# MULTIVERSE

MULTIVERSE es una plataforma SaaS para la gestión de inventarios de empresas ferreteras con varias sucursales y centros de distribución. Cada empresa funciona como un *tenant* independiente, con un catálogo y precios propios, y existencias separadas por almacén.

Este repositorio contiene dos partes relacionadas, pero todavía independientes:

- **Maqueta de interfaz:** prototipo navegable en React y TanStack Start, ubicado en [`docs/multiverse-ferreterias`](./multiverse-ferreterias/README.md).
- **Aplicación Laravel:** base backend del proyecto en la raíz del repositorio. Incluye autenticación, modelos, migraciones, servicios y pruebas para las partes implementadas. No hay integración entre la maqueta y Laravel todavía.

## Maqueta actual

La interfaz usa una navegación horizontal adaptable a escritorio y móvil. Presenta el sitio público, acceso de demostración y espacios de trabajo que cambian según el rol. La personalización visual se aplica al espacio de cada tenant, mientras que la identidad general de MULTIVERSE se mantiene fija.

### Pantallas

| Ruta | Contenido |
| --- | --- |
| `/` | Sitio público, funcionalidades, planes y acceso al formulario de solicitud. |
| `/solicitar` | Formulario de demostración para solicitar el servicio. |
| `/login` | Acceso simulado con cuentas de demostración. |
| `/onboarding` | Asistente inicial del tenant: personalización, contraseña, módulos, carga de catálogo y paso de pago simulado. |
| `/superadmin` | Panel global con tenants, solicitudes, suscripciones, módulos y reportes ficticios. |
| `/admin` | Espacio empresarial para catálogo, usuarios, sucursales, almacenes, transferencias, compras y reportes. |
| `/ventas` | Flujo de punto de venta de demostración. |
| `/almacen` | Inventario, ubicaciones y operaciones de almacén de demostración. |
| `/prestamos` | Ruta conservada en la maqueta; no forma parte del alcance ferretero definitivo. |

La maqueta utiliza datos locales de demostración. Acciones, roles, suscripciones y permisos no representan operaciones persistidas en el backend.

### Roles definidos

1. **Superadministrador:** administra la plataforma SaaS, los tenants, planes, suscripciones y módulos globales.
2. **Administrador:** gestiona su empresa, catálogo, usuarios, establecimientos, precios y autorizaciones.
3. **Encargado de ventas:** trabaja en las sucursales asignadas y registra operaciones comerciales.
4. **Encargado de almacén:** realiza operaciones de inventario en los establecimientos asignados y solicita transferencias.

Las cuentas y contraseñas de la maqueta son públicas y solo sirven para recorrer la interfaz. No usar credenciales de demostración como cuentas reales.

## Lógica del negocio

La jerarquía física prevista es:

```text
Tenant
└── Establecimiento (sucursal o centro de distribución)
    └── Almacén
        └── Ubicación (pasillo, rack, estante o nivel)
```

- El catálogo de productos y los precios son generales dentro de cada tenant.
- Las existencias pertenecen a un almacén y se pueden detallar por ubicación.
- El administrador puede consultar la operación consolidada de su tenant.
- Las ventas descuentan el inventario del almacén elegido y deben confirmarse en una transacción.
- Las transferencias entre almacenes pasan por solicitud, autorización, despacho y recepción. El destino recibe el stock cuando confirma las cantidades, que pueden ser parciales.
- Los usuarios operativos deben tener acceso asignado al establecimiento correspondiente.

## Ejecutar la maqueta

Requisitos: Node.js compatible con Vite 8 y npm.

```bash
cd docs/multiverse-ferreterias
npm install
npm run dev
```

Para compilar y revisar las pruebas de la interfaz:

```bash
npm run build
npm test
```

La maqueta incluye cuentas ficticias en `src/lib/data.ts`. La contraseña de demostración es `demo1234`; el inicio de sesión se simula localmente.

## Aplicación Laravel

La raíz del repositorio contiene la aplicación Laravel con PHP `^8.3`, Laravel `^13`, Livewire y Flux. Para preparar una instalación local:

```bash
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Configura primero la conexión de base de datos en `.env`. El archivo de ejemplo usa SQLite. Esta aplicación es la base backend del proyecto; varias áreas descritas por la maqueta, como compras, devoluciones, conteos y suscripciones operativas, aún requieren integración e implementación.

Las pruebas del backend se ejecutan con:

```bash
php artisan test
```

## Estado y límites

- La maqueta está pensada para presentar y explorar la experiencia de MULTIVERSE; conserva datos y estados en el navegador y usa contenido ficticio.
- La interfaz no autentica usuarios de forma segura, no procesa pagos reales y no debe usarse para controlar inventario de producción.
- Laravel y la maqueta todavía no comparten API ni almacenamiento.
- El backend ya tiene una base multi-tenant y servicios para algunas operaciones. La implementación no cubre aún todas las tablas, reglas y flujos del diseño integral.
- Antes de producción se deben completar la integración, autorización del lado servidor, persistencia, validaciones, auditoría y pruebas de los procesos pendientes.

## Estructura relevante

```text
app/                    Aplicación Laravel: modelos, servicios y middleware
database/migrations/    Esquema de base de datos
routes/                 Rutas Laravel
tests/                  Pruebas Pest
docs/README.md           Guía general de MULTIVERSE
docs/multiverse-ferreterias/  Maqueta React/TanStack Start
```

Consulta [`docs/multiverse-ferreterias/CAMBIOS_MULTIVERSE.md`](./multiverse-ferreterias/CAMBIOS_MULTIVERSE.md) para las notas de adaptación de la interfaz y sus limitaciones actuales.
