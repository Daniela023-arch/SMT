# Middleware de Inventra

Este documento define cómo aplicar los middleware del backend Laravel a los flujos descritos por el proyecto Inventra. El prototipo de interfaz describe pantallas y perfiles; las rutas Laravel de momento solo implementan inicio, dashboard y ajustes. Por eso, los ejemplos de paneles se aplican al crear sus controladores y rutas.

## Middleware disponibles

| Alias | Clase | Regla del proyecto |
|---|---|---|
| `active` | `EnsureActiveUser` | Rechaza cuentas cuyo campo `users.activo` sea falso. Se usa después de `auth`. |
| `tenant` | `EnsureTenant` | Requiere un usuario con `tenant_id`, resuelve el tenant de la ruta si existe y fija `tenant_id` en los atributos de la petición. No se aplica al superadministrador global. |
| `tenant.active` | `EnsureTenantActive` | Solo permite operaciones normales si el tenant existe, no está eliminado y `tenants.estado` es `activo`. |
| `tenant.resource:{param}` | `EnsureTenantResource` | Comprueba que el modelo enlazado por ruta tenga `tenant_id` igual al tenant activo. Devuelve 404 para no revelar recursos ajenos. |
| `subscription.active` | `EnsureSubscriptionActive` | Requiere una suscripción `activa`, `activo` o `prueba`, dentro de sus fechas, y un plan activo. |
| `module:{clave}` | `EnsureModuleEnabled` | Requiere que el módulo exista, esté activo globalmente y esté habilitado para el tenant en `modulos_tenant`. |
| `module.compatible:{clave}` | `EnsureModuleCompatibleWithInventory` | Aplica la matriz de compatibilidad entre módulo y tipo de inventario definida para Inventra. Úsalo junto con `module:{clave}`. |
| `inventory.type:{tipo,...}` | `EnsureInventoryType` | Limita una ruta a uno o más tipos concretos, por ejemplo `inventory.type:biblioteca,electronica`. |
| `role:{slug,...}` | `CheckRole` | Requiere uno de los roles indicados, respetando tenant y rol global. Slugs del prototipo: `super`, `admin`, `ventas`, `prestamos`, `almacen`. |
| `permission:{clave,...}` | `CheckPermission` | Requiere uno de los permisos unidos al rol del usuario, dentro de su tenant. |
| `plan.limit:{recurso}` | `EnsurePlanLimit` | Revisa el cupo de `usuarios` o `productos` en endpoints de creación. El servicio que escribe debe repetir la comprobación dentro de la transacción para evitar exceder el límite con solicitudes simultáneas. |

## Matriz de compatibilidad de módulos

La matriz coincide con `src/lib/data.ts` del prototipo y reconoce los tipos `biblioteca`, `abarrotes`, `papeleria`, `farmacia` y `electronica` (incluyendo el nombre visible «Equipos electrónicos»).

| Módulos | Tipos permitidos |
|---|---|
| `stock`, `productos`, `categorias`, `ubicaciones`, `csv`, `reportes` | Todos |
| `proveedores` | Abarrotes, papelería, farmacia, electrónica |
| `lotes`, `caducidades` | Abarrotes, farmacia |
| `ventas` | Abarrotes, papelería, farmacia |
| `prestamos`, `devoluciones` | Biblioteca, electrónica |
| `biblio` | Biblioteca |
| `mantenimientos`, `bajas` | Electrónica |

## Aplicación por flujo

Orden recomendado: autenticación primero; después estado de cuenta y rol; luego contexto/estado del tenant, suscripción, módulo y finalmente el recurso de ruta. El superadministrador no lleva `tenant` ni controles de suscripción del tenant.

| Flujo | Cadena de middleware sugerida |
|---|---|
| Dashboard general de usuario autenticado | `auth`, `active`, `verified` |
| Panel y operaciones de superadministración (tenants, solicitudes, inventarios, usuarios globales, módulos, suscripciones, promociones, webhooks y reportes globales) | `auth`, `active`, `verified`, `role:super` |
| Finalizar onboarding del administrador del tenant | `auth`, `active`, `tenant`, `role:admin` |
| Administración normal del tenant (usuarios, catálogo, configuración y reportes) | `auth`, `active`, `verified`, `tenant`, `role:admin`, `tenant.active`, `subscription.active` |
| Catálogo y recursos del tenant | Cadena de administración normal + `module:productos`, `module.compatible:productos`, permiso de la operación y `tenant.resource:{producto}` cuando la ruta tenga un modelo `{producto}` |
| Ventas / punto de venta | `auth`, `active`, `verified`, `tenant`, `role:admin,ventas`, `tenant.active`, `subscription.active`, `module:ventas`, `module.compatible:ventas` |
| Préstamos y devoluciones | `auth`, `active`, `verified`, `tenant`, `role:admin,prestamos`, `tenant.active`, `subscription.active`, `module:prestamos` o `module:devoluciones`, `module.compatible:prestamos` o `module.compatible:devoluciones` |
| Almacén / entradas, salidas y movimientos | `auth`, `active`, `verified`, `tenant`, `role:admin,almacen`, `tenant.active`, `subscription.active`, `module:stock`, `module.compatible:stock` |
| Funciones de lotes y caducidades | Cadena de tenant normal + rol/permisos correspondientes, `module:lotes` o `module:caducidades` y su `module.compatible:*` |
| Biblioteca / datos bibliográficos | Cadena de tenant normal + rol/permisos correspondientes, `module:biblio`, `module.compatible:biblio` |
| Equipos / mantenimientos / bajas | Cadena de tenant normal + rol/permisos correspondientes, módulo y compatibilidad para `mantenimientos` o `bajas` |
| Crear usuarios o productos | Cadena de tenant normal + `plan.limit:usuarios` o `plan.limit:productos` en la ruta `POST` de creación |
| Solicitud pública de alta | Pública; aplicar el middleware integrado `throttle` en el endpoint de escritura |
| Recepción de webhooks de pagos | Endpoint separado de sesión web; verificar firma del proveedor y aplicar `throttle`. No debe usar `auth` de usuario. |

Ejemplo al crear rutas para ventas:

```php
Route::prefix('ventas')
    ->middleware([
        'auth', 'active', 'verified', 'tenant', 'role:admin,ventas',
        'tenant.active', 'subscription.active', 'module:ventas',
        'module.compatible:ventas',
    ])
    ->group(function () {
        // Definir aquí las rutas del punto de venta.
    });
```

Para un recurso enlazado por ruta, coloca `tenant.resource:producto` después de `tenant`; `producto` debe ser el nombre exacto del parámetro enlazado a un modelo Eloquent que tenga `tenant_id`.

## Permisos sugeridos

`CheckPermission` usa las claves guardadas en `permisos.clave`. Para cubrir las capacidades del prototipo, los datos iniciales deben incluir claves consistentes como:

- `usuarios.ver`, `usuarios.crear`, `usuarios.editar`, `usuarios.desactivar`, `usuarios.asignar_roles`
- `productos.ver`, `productos.crear`, `productos.editar`, `productos.eliminar`
- `categorias.gestionar`, `ubicaciones.gestionar`, `proveedores.gestionar`
- `inventario.ver`, `inventario.entrada`, `inventario.salida`, `inventario.transferir`
- `ventas.crear`, `ventas.confirmar`, `ventas.cancelar`, `ventas.ver`
- `prestamos.crear`, `prestamos.devolver`, `prestamos.ver`
- `mantenimientos.gestionar`, `bajas.crear`, `bajas.aprobar`
- `importaciones.crear`, `reportes.ver`, `reportes.exportar`
- `tenants.gestionar`, `modulos.gestionar`, `suscripciones.gestionar`, `solicitudes.gestionar`

Los middleware no crean los roles, permisos, módulos ni tipos de inventario. Deben existir sus registros en la base de datos y estar asignados mediante las tablas pivote correspondientes.

## Límites del esquema actual

- No hay campos que indiquen que el onboarding terminó, ni si una contraseña temporal debe cambiarse; el esquema actual no permite middleware confiable para esos pasos.
- La relación de compatibilidad módulo/tipo no existe en la base de datos. `EnsureModuleCompatibleWithInventory` usa la matriz documentada del prototipo como regla explícita; si el superadministrador debe editarla desde el panel, habrá que modelarla en tablas.
- El límite de plan del middleware sirve como rechazo temprano en rutas de creación. El servicio de dominio debe hacer la verificación final dentro de la transacción.
- En `bootstrap/app.php` están registrados los alias. Las rutas de paneles del prototipo aún no existen en Laravel; aplicar cada cadena al crear sus rutas evita proteger una funcionalidad con reglas incompletas.
