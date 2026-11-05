# MULTIVERSE — Cambios de interfaz

- Se retiraron las pestañas superiores «Plataforma», «Comercial» y «Configuración» del encabezado del Superadministrador. La navegación por iconos se conserva centrada, sin cambiar la estructura de los módulos.
- Se agregó un botón de modo claro/oscuro en la cabecera de los paneles y en el asistente inicial. La elección se conserva en localStorage.
- El asistente del primer acceso ahora termina en un paso de suscripción después de guardar la personalización y la carga inicial.
- El paso de cobro es EXCLUSIVAMENTE una simulación: no hay cargos reales, no se solicitan tarjetas y el botón «Simular pago aprobado» habilita el acceso de demostración.
- Para producción, Laravel debe validar el pago mediante webhook firmado y registrar la suscripción antes de habilitar el tenant; nunca confiar en localStorage.
