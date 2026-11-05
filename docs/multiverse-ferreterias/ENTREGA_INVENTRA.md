# Inventra — entrega de interfaz corregida

## Cambios
- Se conserva la identidad azul marino del sistema general. Se eliminaron los fondos degradados y las transiciones de degradado.
- Navegación horizontal centrada en pantallas medianas y grandes; en móvil admite desplazamiento.
- Nuevas rutas `/admin`, `/onboarding`, `/ventas`, `/prestamos`, `/almacen`.
- Asistente de primer acceso con logotipo, color, contraseña de demostración, módulos y carga inicial.
- Panel de administrador con catálogo, usuarios, reportes, configuración y módulos autorizados.
- Encargados con operaciones de ventas, préstamos, devoluciones, entradas y salidas; actualizan existencias locales.
- Estado de inventario, usuarios, movimientos y personalización guardado localmente en el navegador.

## Ejecutar
1. Instala Node.js 20 o superior.
2. Abre la carpeta en VS Code.
3. Ejecuta `npm install` y después `npm run dev`.
4. Abre la URL local que muestra la terminal.
5. Accede con las cuentas de demostración del login (contraseña `demo1234`).

## Alcance y advertencias
Es un **prototipo frontend**, no un sistema productivo. La autenticación, las contraseñas, el aislamiento multi-tenant y los permisos son simulados y NO protegen datos reales. No hay conexión con Laravel ni MySQL. La carga CSV es básica, sin un importador robusto; los movimientos no tienen conciliación ni transacciones de base de datos. La subida del logo se limita a imágenes pequeñas para almacenamiento local. El proyecto requiere pruebas finales tras instalar dependencias; el entorno de entrega no pudo completar `npm install`.

El archivo `src/routeTree.gen.ts` contiene las rutas añadidas y TanStack Router puede regenerarlo al iniciar el proyecto.
