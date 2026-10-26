# 📦 Documentación — Prototipo de Interfaz: Inventra Multi Business Hub

> **Fuente:** `docs/multiverse-interfaces.zip` → carpeta `inventra-multi-business-hub`  
> **Construido con:** [Lovable](https://lovable.dev) · [Editor del proyecto](https://lovable.dev/projects/8dbd4360-51ef-4174-b3b9-fe17a55e3057)

---

## 🧭 Descripción general

**Inventra** es el prototipo frontend de una plataforma SaaS de administración de inventarios multi-tenant. Permite gestionar múltiples empresas o negocios independientes (**tenants**), cada uno con su propio catálogo, usuarios, módulos y personalización visual.

> ⚠️ **Prototipo frontend únicamente.** La autenticación, permisos y aislamiento multi-tenant son simulados. No hay conexión con Laravel ni MySQL. No usar en producción.

---

## 🏗️ Estructura del proyecto

```
inventra-multi-business-hub/
├── src/
│   ├── routes/              # Páginas de la aplicación
│   │   ├── index.tsx        # Landing pública
│   │   ├── login.tsx        # Inicio de sesión
│   │   ├── onboarding.tsx   # Asistente de primer acceso
│   │   ├── admin.tsx        # Panel del administrador tenant
│   │   ├── superadmin.tsx   # Dashboard del superadministrador
│   │   ├── solicitar.tsx    # Formulario de solicitud pública
│   │   ├── ventas.tsx       # Panel encargado de ventas
│   │   ├── prestamos.tsx    # Panel encargado de préstamos
│   │   └── almacen.tsx      # Panel encargado de almacén
│   ├── components/
│   │   ├── inventra/
│   │   │   ├── Shell.tsx    # Contenedor principal + navegación horizontal
│   │   │   └── Workspace.tsx # Área de trabajo dinámica por módulo
│   │   └── ui/              # Componentes Radix UI / shadcn
│   ├── hooks/               # Custom hooks React
│   ├── lib/                 # Utilidades
│   ├── styles.css           # Sistema de diseño (tokens CSS + Tailwind)
│   └── router.tsx           # Configuración TanStack Router
├── public/
├── package.json
├── vite.config.ts
└── tsconfig.json
```

---

## 🚀 Instalación y ejecución

**Requisitos:** Node.js 20 o superior.

```bash
# 1. Instalar dependencias
npm install

# 2. Levantar servidor de desarrollo
npm run dev

# 3. Abrir la URL que muestra la terminal en el navegador
```

**Credenciales de demo** (contraseña: `demo1234`):

| Rol | Acceso |
|---|---|
| Superadministrador | Dashboard global de la plataforma |
| Administrador tenant | Dashboard del negocio (onboarding en primer acceso) |
| Encargado de ventas | Punto de venta |
| Encargado de préstamos | Gestión de préstamos/devoluciones |
| Encargado de almacén | Control de existencias y movimientos |

---

## 🖥️ Pantallas implementadas

### 1. Sitio web público (`/`)
Landing page comercial con:
- Hero: *"Controla tu inventario. Haz crecer tu negocio."*
- Sección publicitaria con banner, carrusel y tarjetas de funcionalidades
- **5 tarjetas interactivas** de tipos de inventario con botón "Conocer funcionalidades"
- Sección de **planes de suscripción** (Mensual / Trimestral / Anual)
- Navegación: Inicio · Funcionalidades · Tipos de inventario · Planes · Solicitar sistema · Iniciar sesión

### 2. Inicio de sesión (`/login`)
- Correo + contraseña (mostrar/ocultar)
- Recordarme · Recuperar contraseña
- Redirección automática por rol al panel correspondiente
- Estados de error: cuenta suspendida, contraseña incorrecta, cargando

### 3. Superadministrador (`/superadmin`)
Dashboard global con navegación: **Dashboard · Nuevo tenant · Inventarios · Usuarios · Módulos · Reportes**

| Sección | Contenido |
|---|---|
| Dashboard | KPIs: tenants activos, solicitudes, suscripciones, ingresos; gráficas de crecimiento |
| Nuevo tenant | Tabla de solicitudes (Pendiente / En revisión / Aprobada / Rechazada) + asistente de creación |
| Inventarios | Vista de los 5 tipos, estadísticas y configuración general |
| Usuarios | Gestión global de cuentas, roles, bloqueos e invitaciones |
| Módulos | Catálogo global de módulos con compatibilidad por tipo de inventario |
| Reportes | Crecimiento, ingresos, renovaciones y actividad; filtros por fecha/estado/tipo |

### 4. Asistente de primer acceso (`/onboarding`)
Barra de progreso de **5 pasos** que el administrador completa la primera vez:

1. **Bienvenida** — Nombre del negocio y tipo de inventario (bloqueados)
2. **Personalización** — Logo, color principal/secundario, vista previa en tiempo real
3. **Seguridad** — Cambio obligatorio de contraseña temporal con medidor de fortaleza
4. **Módulos** — Activar/desactivar módulos dentro de los autorizados por el superadmin
5. **Carga inicial** — Importación CSV, registro manual o configurar más tarde

### 5. Administrador del tenant (`/admin`)
Navegación: **Mi tenant · Usuarios · Catálogo · Reportes**

- **Mi tenant:** dashboard con existencias, movimientos recientes, estado de suscripción y alertas
- **Usuarios:** crear, editar, activar/desactivar, asignar roles y permisos
- **Catálogo:** tablas con CRUD, búsqueda, filtros, paginación y ordenamiento
- **Reportes:** existencias, entradas/salidas, bajo stock, ventas, caducidades; exportación CSV/PDF

### 6. Encargado de ventas (`/ventas`)
- Punto de venta: buscador, lector de código de barras, carrito, totales, método de pago
- Validación de existencias antes de confirmar
- Estados: venta exitosa, error, stock insuficiente

### 7. Encargado de préstamos (`/prestamos`)
- Navegación: Inicio · Préstamos · Devoluciones · Historial
- Buscador de ejemplares, disponibilidad, datos del solicitante, fechas y estado
- Listado de préstamos activos y vencidos; no permite prestar ítems no disponibles

### 8. Encargado de almacén (`/almacen`)
- Navegación: Inicio · Inventario · Entradas · Salidas · Movimientos
- Stock actual vs. mínimo, alertas, ubicaciones, historial de movimientos
- Validaciones contra existencias negativas y registros duplicados

---

## 📦 Tipos de inventario soportados

| Tipo | Módulos principales |
|---|---|
| 📚 Biblioteca | Libros, autores, ejemplares, préstamos, devoluciones |
| 🛒 Abarrotes | Productos, categorías, existencias, ventas, lotes/caducidades |
| ✏️ Papelería | Artículos escolares, productos, existencias, ventas, proveedores |
| 💊 Farmacia | Medicamentos, lotes, caducidades, ventas, alertas de vencimiento |
| 💻 Equipos electrónicos | Equipos, nros. de serie, ubicaciones, asignaciones, mantenimientos, bajas |

---

## 🧩 Módulos disponibles

Los módulos son configurados por el superadministrador por tenant y el administrador puede activar/desactivar los opcionales:

- Inventario y stock · Productos · Categorías · Ubicaciones · Proveedores
- Lotes · Caducidades · Ventas · Préstamos · Devoluciones
- Datos bibliográficos · Mantenimientos · Bajas · Importaciones CSV · Reportes

---

## 🎨 Sistema de diseño

### Identidad visual
- **Tipografía:** `Plus Jakarta Sans` (fallback: `ui-sans-serif`, `system-ui`)
- **Paleta base:** Azul profundo como identidad principal
- **Superficies:** Blanco y gris muy claro
- **Estados semánticos:** Verde (éxito) · Ámbar (advertencia) · Rojo (error)

### Tokens de color (modo claro)

| Token | Valor OKLCH | Uso |
|---|---|---|
| `--primary` | `oklch(0.36 0.13 262)` | Botones, activos, gráficas |
| `--background` | `oklch(0.985 0.003 255)` | Fondo general |
| `--foreground` | `oklch(0.2 0.03 262)` | Texto principal |
| `--success` | `oklch(0.6 0.15 155)` | Confirmaciones |
| `--warning` | `oklch(0.78 0.15 75)` | Alertas |
| `--destructive` | `oklch(0.58 0.22 25)` | Errores y eliminaciones |

### Principios de UI
- Navegación **horizontal estilo Airbnb** (no sidebar); categoría activa con línea inferior
- Tarjetas con bordes sutiles, esquinas redondeadas (`--radius: 0.875rem`)
- Sombras discretas: `shadow-soft` / `shadow-lift`
- Animaciones suaves: `animate-fade-up` (0.45 s cubic-bezier)
- Modales para acciones importantes; toasts para notificaciones
- Confirmación explícita para operaciones destructivas
- Responsive: escritorio, tablet y móvil (navegación con scroll horizontal en móvil)
- Soporte de modo oscuro (`.dark`) con tokens invertidos

### Personalización por tenant
Cada tenant puede definir su propio color principal/secundario desde el onboarding. Los colores del tenant reemplazan los de marca dentro de su espacio.

---

## 🛠️ Stack tecnológico

| Categoría | Tecnología |
|---|---|
| Framework UI | React 19 + TypeScript 5 |
| Routing | TanStack Router 1.170 |
| Data fetching | TanStack Query 5 |
| Build tool | Vite 8 + Bun |
| Estilos | Tailwind CSS 4 + `tw-animate-css` |
| Componentes | Radix UI (suite completa) + shadcn/ui |
| Formularios | React Hook Form + Zod |
| Gráficas | Recharts |
| Iconos | Lucide React |
| Fechas | date-fns |
| Toasts | Sonner |
| Tests | Vitest + Testing Library |
| Linting/Format | ESLint 9 + Prettier |
| Generado con | [Lovable](https://lovable.dev) |

---

## ⚠️ Alcance y limitaciones del prototipo

- **Sin backend:** toda la información se persiste en `localStorage` del navegador
- **Sin autenticación real:** roles y permisos son simulados
- **Sin integración Laravel/MySQL:** el proyecto SMT (Laravel) es independiente
- **Importación CSV básica:** sin importador robusto ni transacciones de base de datos
- **Subida de logo limitada:** solo imágenes pequeñas (límite de `localStorage`)
- **Multi-tenancy simulada:** no hay aislamiento real de datos entre tenants

---

## 🔗 Referencias

- [Editor Lovable del proyecto](https://lovable.dev/projects/8dbd4360-51ef-4174-b3b9-fe17a55e3057)
- [Instalación de NVM](https://github.com/nvm-sh/nvm#installing-and-updating)
- Archivo fuente: `docs/multiverse-interfaces.zip`
