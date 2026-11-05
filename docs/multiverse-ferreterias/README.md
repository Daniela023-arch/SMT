# Inventra Multi Business Hub

Diseña una aplicación web SaaS profesional, moderna, responsiva y de alta fidelidad llamada provisionalmente Inventra, enfocada en la administración de inventarios para diferentes tipos de negocios.

El sistema debe tener una identidad visual propia, elegante, minimalista y empresarial.

REFERENCIA VISUAL OBLIGATORIA

Utiliza la imagen adjunta de la barra de navegación de Airbnb como referencia principal de diseño.

NO debes copiar los colores ni la identidad corporativa de Airbnb. Solamente utiliza su concepto de navegación:

Barra superior blanca, limpia y espaciosa.

Logotipo ubicado a la izquierda.

Navegación principal centrada.

Acciones de usuario a la derecha.

Segunda barra horizontal con iconos lineales y nombres de categorías.

Categoría activa identificada mediante una línea inferior.

Animaciones suaves al cambiar de categoría.

Flechas laterales para desplazarse entre módulos.

Diseño adaptable a dispositivos móviles.

La navegación debe cambiar dinámicamente dependiendo del rol del usuario y los módulos autorizados.

No diseñes un sistema con sidebar tradicional como navegación principal. La navegación horizontal estilo Airbnb es obligatoria.

CONTEXTO GENERAL

El sistema permite administrar múltiples empresas o negocios independientes llamados TENANTS.

Los cinco tipos de inventario disponibles son:

Biblioteca.

Abarrotes.

Papelería.

Farmacia.

Equipos electrónicos.

Cada tenant tiene sus propios usuarios, productos, configuraciones, inventarios y operaciones.

Existe un SUPERADMINISTRADOR global que controla la plataforma, las solicitudes de tenants, los módulos disponibles y las suscripciones.

Cada tenant tiene un ADMINISTRADOR que gestiona exclusivamente su negocio.

También existen ENCARGADOS con permisos limitados, por ejemplo encargados de ventas, préstamos o almacén.

El sistema debe reflejar claramente estas diferencias de acceso.

PANTALLA 1 — SITIO WEB PÚBLICO

Diseña una landing page profesional para presentar y comercializar la plataforma.

Header

Logotipo Inventra a la izquierda.

Navegación principal:

Inicio

Funcionalidades

Tipos de inventario

Planes

Solicitar sistema

Iniciar sesión

El botón "Iniciar sesión" debe destacarse visualmente.

Hero principal

Título:

"Controla tu inventario. Haz crecer tu negocio."

Subtítulo:

"Una plataforma inteligente y adaptable para administrar productos, existencias, ventas, préstamos y operaciones desde un solo lugar."

Botones:

Conocer planes

Solicitar mi sistema

A la derecha, mostrar una vista previa realista del dashboard administrativo.

Sección publicitaria

Crear un apartado para promociones y publicidad administrada por el Superadministrador.

Incluir:

Banner promocional principal.

Carrusel de anuncios.

Tarjetas de funcionalidades destacadas.

Promociones especiales.

Llamadas a la acción.

Utilizar contenido ficticio realista y claramente editable.

Tipos de inventario

Crear cinco tarjetas interactivas con iconos e ilustraciones:

BIBLIOTECA

Libros.

Autores y datos bibliográficos.

Ejemplares.

Préstamos.

Devoluciones.

ABARROTES

Productos.

Categorías.

Existencias.

Ventas.

Caducidades y lotes cuando estén habilitados.

PAPELERÍA

Artículos escolares.

Productos.

Existencias.

Ventas.

Proveedores.

FARMACIA

Medicamentos.

Lotes.

Caducidades.

Existencias.

Ventas.

Alertas de vencimiento.

EQUIPOS ELECTRÓNICOS

Equipos.

Números de serie.

Ubicaciones.

Asignaciones.

Mantenimientos.

Bajas.

Cada tarjeta debe tener un botón "Conocer funcionalidades".

Planes de suscripción

Crear una sección de precios con selector:

MENSUAL | TRIMESTRAL | ANUAL

Mostrar tres modalidades de contratación por periodicidad, sin inventar precios definitivos.

Cada opción debe mostrar:

Precio configurable.

Periodicidad.

Beneficios incluidos.

Módulos disponibles según tipo de inventario.

Condiciones de contratación.

Botón "Solicitar plan".

Destacar visualmente el plan anual como recomendado, sin afirmar descuentos inexistentes.

Solicitar nuevo tenant

Crear un formulario público para solicitar la contratación del sistema.

Campos:

Nombre del negocio.

Nombre del responsable.

Correo electrónico.

Teléfono.

Tipo de inventario.

Plan solicitado: mensual, trimestral o anual.

Descripción del negocio.

Comentarios adicionales.

Aceptación de aviso de privacidad.

Al enviar, mostrar una pantalla de confirmación con número de solicitud y estado "Pendiente de revisión".

No crear automáticamente un tenant activo desde el formulario público.

PANTALLA 2 — INICIO DE SESIÓN

Diseñar una pantalla elegante, segura y minimalista.

Elementos:

Logotipo.

Correo electrónico.

Contraseña.

Mostrar/ocultar contraseña.

Recordarme.

Recuperar contraseña.

Botón "Iniciar sesión".

Enlace para regresar al sitio público.

La aplicación debe identificar el rol y redirigir al panel correspondiente.

SUPERADMINISTRADOR → Dashboard global.

ADMINISTRADOR → Configuración inicial si es su primer acceso; en caso contrario, dashboard del tenant.

ENCARGADO → Panel limitado según sus permisos.

Incluir estados de error, carga, contraseña incorrecta y cuenta suspendida.

PANTALLA 3 — SUPERADMINISTRADOR

Diseñar un dashboard global que administre toda la plataforma SaaS.

Navegación horizontal principal con iconos:

Dashboard | Nuevo tenant | Inventarios | Usuarios | Módulos | Reportes

También incluir acceso secundario a planes, suscripciones, promociones y configuración de la plataforma, sin saturar la navegación principal.

Dashboard global

Mostrar tarjetas KPI:

Tenants activos.

Solicitudes pendientes.

Suscripciones activas.

Suscripciones próximas a vencer.

Usuarios registrados.

Ingresos por suscripciones.

Incluir:

Gráfica de crecimiento de tenants.

Gráfica de ingresos.

Distribución de tenants por tipo de inventario.

Actividad reciente.

Alertas administrativas.

Solicitudes pendientes.

Nuevo tenant

Diseñar un módulo para gestionar solicitudes y crear tenants.

Tabla con:

Folio.

Negocio.

Responsable.

Tipo de inventario.

Plan.

Fecha.

Estado.

Acciones.

Estados:

Pendiente | En revisión | Aprobada | Rechazada

Al aprobar una solicitud, abrir un asistente administrativo:

Revisar datos del negocio.

Seleccionar tipo de inventario.

Asignar plan y vigencia.

Activar módulos permitidos.

Registrar administrador inicial.

Revisar resumen.

Crear tenant y enviar invitación de acceso.

El Superadministrador debe poder crear tenants directamente sin solicitud previa.

Inventarios

Mostrar los cinco tipos de inventario disponibles.

Permitir visualizar:

Tenants por tipo.

Módulos compatibles.

Configuración general de cada tipo.

Estadísticas agregadas.

No mezclar información operativa privada de tenants sin autorización.

Usuarios

Gestionar:

Usuarios globales.

Administradores de tenants.

Estados de cuentas.

Roles.

Bloqueos.

Invitaciones.

Restablecimiento de acceso.

Módulos

Mostrar un catálogo global de módulos:

Inventario y stock.

Productos.

Categorías.

Ubicaciones.

Proveedores.

Lotes.

Caducidades.

Ventas.

Préstamos.

Devoluciones.

Datos bibliográficos.

Mantenimientos.

Bajas.

Importaciones CSV.

Reportes.

Cada módulo debe indicar con qué tipos de inventario es compatible.

El Superadministrador controla qué módulos se habilitan para cada tenant.

Reportes

Mostrar:

Crecimiento de tenants.

Suscripciones.

Ingresos.

Renovaciones.

Tipos de inventario más utilizados.

Actividad administrativa.

Agregar filtros por fecha, estado y tipo de inventario.

PANTALLA 4 — PRIMER INICIO DEL ADMINISTRADOR DEL TENANT

Diseñar un asistente de configuración inicial obligatorio.

Debe aparecer únicamente cuando el administrador ingresa por primera vez o cuando tiene una configuración inicial pendiente.

Mostrar barra de progreso con cinco pasos.

PASO 1 — Bienvenida

Mostrar:

"¡Bienvenido a Inventra! Vamos a configurar tu espacio de trabajo."

Mostrar nombre del negocio y tipo de inventario asignado por el Superadministrador.

Estos datos deben estar bloqueados para evitar cambiar de tipo de tenant.

PASO 2 — Personalización

Permitir:

Subir logotipo PNG, JPG o SVG validado.

Vista previa del logotipo.

Elegir color principal.

Elegir color secundario opcional.

Seleccionar entre colores predefinidos.

Vista previa en tiempo real del dashboard.

Restablecer colores.

El color seleccionado debe aplicarse a botones, elementos activos, gráficas y detalles visuales.

Mantener buena accesibilidad y contraste.

PASO 3 — Seguridad

Solicitar cambio obligatorio de contraseña temporal.

Campos:

Contraseña actual.

Nueva contraseña.

Confirmar contraseña.

Mostrar reglas de seguridad y medidor de fortaleza.

No permitir continuar hasta completar correctamente este paso.

PASO 4 — Módulos

Mostrar solamente los módulos previamente autorizados por el Superadministrador para ese tenant.

El administrador puede activar o desactivar módulos opcionales dentro de los autorizados.

Los módulos esenciales deben aparecer bloqueados y activados.

Los módulos no contratados o no autorizados no pueden activarse.

Mostrar:

Nombre del módulo.

Descripción.

Icono.

Estado.

Interruptor de activación.

Ejemplo:

Tenant farmacia:

Productos: obligatorio.

Existencias: obligatorio.

Lotes: autorizado.

Caducidades: autorizado.

Ventas: autorizado.

Préstamos bibliográficos: no disponible.

PASO 5 — Carga inicial de inventario

Preguntar:

"¿Cómo deseas comenzar a registrar tus productos?"

Mostrar tres opciones:

Carga masiva mediante archivo CSV.

Registro manual.

Configurar más tarde.

Si selecciona carga masiva:

Mostrar área drag & drop.

Descargar plantilla CSV según tipo de inventario.

Subir archivo.

Previsualizar registros.

Validar columnas.

Detectar errores.

Confirmar importación.

Si selecciona manual:

Mostrar formulario de creación del primer producto o registro.

Campos dinámicos según tipo de inventario.

Si selecciona configurar más tarde:

Permitir finalizar sin inventario inicial.

Mostrar resumen final y botón:

"Finalizar configuración e ir a mi dashboard".

PANTALLA 5 — ADMINISTRADOR DEL TENANT

Crear un panel de administración independiente para cada tenant.

Navegación principal:

Mi tenant | Usuarios | Catálogo | Reportes

La barra horizontal secundaria mostrará los módulos autorizados y activados.

Mi tenant

Dashboard con:

Nombre y logotipo del negocio.

Tipo de inventario.

Estado de suscripción.

Plan contratado.

Resumen de existencias.

Movimientos recientes.

Alertas.

Accesos rápidos.

Incluir configuración para editar colores, logotipo y módulos autorizados.

Usuarios

Permitir al administrador:

Crear usuarios de su tenant.

Editar usuarios.

Activar/desactivar usuarios.

Asignar roles.

Asignar permisos.

Restablecer acceso mediante flujo seguro.

Roles sugeridos:

Administrador.

Encargado de ventas.

Encargado de préstamos.

Encargado de almacén.

Catálogo

Administrar los registros operativos según el tenant:

Productos o libros.

Categorías.

Ubicaciones.

Proveedores.

Existencias.

Lotes.

Códigos de identificación.

Datos específicos por tipo de inventario.

Incluir tablas con búsqueda, filtros, paginación, ordenamiento y acciones CRUD.

Reportes

Mostrar reportes operativos de su propio tenant:

Existencias.

Entradas y salidas.

Productos con bajo stock.

Ventas.

Préstamos.

Caducidades.

Mantenimientos.

Bajas.

Solo mostrar reportes correspondientes a los módulos autorizados.

Permitir exportar CSV y PDF si el permiso está habilitado.

PANTALLA 6 — ENCARGADO DE VENTAS

Diseñar una interfaz sencilla, rápida y orientada a operaciones.

Navegación:

Inicio | Ventas | Productos | Historial

Mostrar solamente las opciones autorizadas.

Pantalla principal tipo punto de venta:

Buscador de productos.

Lector de código de barras.

Carrito de compra.

Cantidad.

Precio unitario.

Subtotal.

Total.

Método de pago.

Confirmar venta.

Validar existencias antes de confirmar.

Incluir estados de venta exitosa, error y stock insuficiente.

El encargado no puede administrar tenants, suscripciones, módulos ni usuarios.

PANTALLA 7 — ENCARGADO DE PRÉSTAMOS

Orientado principalmente a bibliotecas y otros tenants que tengan autorizado el módulo de préstamos.

Navegación:

Inicio | Préstamos | Devoluciones | Historial

Mostrar:

Buscador de ejemplares.

Disponibilidad.

Datos del solicitante.

Fecha de préstamo.

Fecha prevista de devolución.

Estado.

Confirmar préstamo.

Incluir pantalla de devolución, préstamos activos y vencidos.

No permitir prestar una unidad que ya está prestada o no disponible.

PANTALLA 8 — ENCARGADO DE ALMACÉN

Diseñar una interfaz para controlar existencias.

Navegación:

Inicio | Inventario | Entradas | Salidas | Movimientos

Mostrar:

Productos disponibles.

Stock actual.

Stock mínimo.

Alertas.

Ubicaciones.

Historial de movimientos.

Permitir registrar entradas, salidas y movimientos autorizados.

Incluir validaciones para evitar existencias negativas y registros duplicados.

No mostrar módulos administrativos no autorizados.

COMPORTAMIENTO MULTI-TENANT

Todos los dashboards deben usar el mismo sistema de diseño, pero cambiar dinámicamente según el tenant.

Ejemplo biblioteca:

Libros.

Ejemplares.

Datos bibliográficos.

Préstamos.

Devoluciones.

Ejemplo abarrotes:

Productos.

Existencias.

Ventas.

Proveedores.

Lotes y caducidades si están habilitados.

Ejemplo papelería:

Artículos.

Categorías.

Ventas.

Existencias.

Proveedores.

Ejemplo farmacia:

Medicamentos.

Lotes.

Caducidades.

Ventas.

Alertas.

Ejemplo equipos electrónicos:

Equipos.

Identificadores.

Ubicaciones.

Mantenimientos.

Bajas.

No mostrar funcionalidades incompatibles con el tenant.

SISTEMA DE DISEÑO

Utilizar:

Tipografía Inter o similar.

Fondo blanco y gris muy claro.

Tarjetas con bordes sutiles.

Esquinas redondeadas.

Iconografía lineal consistente.

Sombras discretas.

Tablas modernas.

Gráficas legibles.

Espaciado amplio.

Animaciones suaves.

Diseño responsivo.

Estados hover, focus, loading, empty y error.

Ventanas modales para acciones importantes.

Notificaciones tipo toast.

Confirmación para operaciones destructivas.

Colores predeterminados:

Azul profundo para identidad principal.

Blanco para superficies.

Gris para texto secundario.

Verde para éxito.

Ámbar para advertencias.

Rojo para errores.

Los colores del tenant pueden reemplazar los colores de marca en su propio espacio.

REQUISITOS DE PROTOTIPADO

Genera pantallas independientes y conectadas mediante navegación funcional en el prototipo.

Incluye datos ficticios realistas en español.

Genera variantes de dashboard para los cinco tipos de tenant.

Incluye vistas de escritorio y móvil.

Mantén una navegación visual consistente en todo el proyecto.

No mezcles el dashboard global del Superadministrador con el dashboard operativo de un tenant.

No presentes módulos no autorizados como opciones funcionales.

Prioriza claridad, facilidad de uso, jerarquía visual y experiencia profesional.

El resultado debe parecer una plataforma SaaS lista para presentarse comercialmente, no un panel administrativo genérico.

This project was built with [Lovable](https://lovable.dev).

## Build with Lovable

Continue developing this project in the [Lovable editor](https://lovable.dev/projects/8dbd4360-51ef-4174-b3b9-fe17a55e3057).

- **Ship faster**: describe what you want to build and Lovable handles the code.
- **Stay in sync**: every change made in Lovable is committed straight to this repository.
- **Full ownership**: this code is yours. Push to `main` on GitHub and your changes sync back into Lovable, ready for your next prompt.

## Development

Prefer working locally? You need Node.js and npm — [install with nvm](https://github.com/nvm-sh/nvm#installing-and-updating).

```sh
git clone <this-repository-url>
cd <repository-name>
npm i
npm run dev
```
