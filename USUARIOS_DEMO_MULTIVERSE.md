# Usuarios de demostración MULTIVERSE

**Solo desarrollo local (`APP_ENV=local`)**. El seeder `UsuariosDemoSeeder` completa los tres perfiles de usuario (admin, ventas y almacén) en cada uno de los tenants `ferreteria-alfa`, `ferreteria-beta` y `grupo-ferretero-central`. El superadministrador sigue siendo creado por `SuperadminDemoSeeder` y no se ha modificado.

## Ejecución

En la carpeta del proyecto, tras configurar `.env`, conexión de base de datos y dependencias:

```bash
php artisan migrate
php artisan db:seed
```

También es posible ejecutar solo el seeder nuevo **si ya existen los tenants, permisos y sucursales**:

```bash
php artisan db:seed --class=UsuariosDemoSeeder
```

## Accesos de prueba

| Tenant | Rol | Email |
|---|---|---|
| Global | Superadministrador | super@multiverse.mx |
| Ferretería Alfa | Administrador | admin@ferreteriaalfa.mx |
| Ferretería Alfa | Ventas | ventas@ferreteriaalfa.mx |
| Ferretería Alfa | Almacén | almacen@ferreteriaalfa.mx |
| Ferretería Beta | Administrador | admin@ferreteriabeta.mx |
| Ferretería Beta | Ventas | ventas@ferreteriabeta.mx |
| Ferretería Beta | Almacén | almacen@ferreteriabeta.mx |
| Grupo Ferretero Central | Administrador | admin@grupoferreterocentral.mx |
| Grupo Ferretero Central | Ventas | ventas@grupoferreterocentral.mx |
| Grupo Ferretero Central | Almacén | almacen@grupoferreterocentral.mx |

Contraseña **solo para cuentas nuevas**: `demo1234`, salvo que hayas definido `DEMO_TENANT_PASSWORD` en el `.env` (y `SUPERADMIN_PASSWORD` para superadmin).

**Importante:** los seeders no restablecen contraseñas de cuentas existentes. Los usuarios de ejemplo están restringidos a entorno local. El seeder crea los roles y permisos del tenant si faltan, y asigna cada usuario a las sucursales existentes del tenant; no modifica la interfaz del superadministrador.

Este paquete no incluye dependencias (`vendor` / `node_modules`) ni secretos (`.env`). La validación realizada fue sintáctica (`php -l`) sobre los dos archivos PHP modificados; no se ejecutaron pruebas con una base de datos real.
