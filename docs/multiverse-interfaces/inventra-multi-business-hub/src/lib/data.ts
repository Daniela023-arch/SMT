import {
  BookOpen, ShoppingBasket, PenTool, Pill, Cpu, Boxes, Package, Tags, MapPin, Truck, Layers,
  CalendarClock, ShoppingCart, BookMarked, Undo2, Library, Wrench, Trash2, FileUp, BarChart3,
  type LucideIcon,
} from "lucide-react";

export type InvType = "biblioteca" | "abarrotes" | "papeleria" | "farmacia" | "electronica";

export const INV_TYPES: Record<InvType, { label: string; icon: LucideIcon; item: string; items: string; features: string[]; blurb: string }> = {
  biblioteca: { label: "Biblioteca", icon: BookOpen, item: "Libro", items: "Libros", blurb: "Acervo, ejemplares y circulación.", features: ["Libros", "Autores y datos bibliográficos", "Ejemplares", "Préstamos", "Devoluciones"] },
  abarrotes: { label: "Abarrotes", icon: ShoppingBasket, item: "Producto", items: "Productos", blurb: "Venta diaria y control de existencias.", features: ["Productos", "Categorías", "Existencias", "Ventas", "Caducidades y lotes (opcional)"] },
  papeleria: { label: "Papelería", icon: PenTool, item: "Artículo", items: "Artículos", blurb: "Artículos escolares y de oficina.", features: ["Artículos escolares", "Productos", "Existencias", "Ventas", "Proveedores"] },
  farmacia: { label: "Farmacia", icon: Pill, item: "Medicamento", items: "Medicamentos", blurb: "Lotes, caducidades y alertas.", features: ["Medicamentos", "Lotes", "Caducidades", "Existencias", "Ventas", "Alertas de vencimiento"] },
  electronica: { label: "Equipos electrónicos", icon: Cpu, item: "Equipo", items: "Equipos", blurb: "Activos con número de serie.", features: ["Equipos", "Números de serie", "Ubicaciones", "Asignaciones", "Mantenimientos", "Bajas"] },
};

export type ModuleId = "stock" | "productos" | "categorias" | "ubicaciones" | "proveedores" | "lotes" | "caducidades" | "ventas" | "prestamos" | "devoluciones" | "biblio" | "mantenimientos" | "bajas" | "csv" | "reportes";

export const MODULES: Record<ModuleId, { name: string; desc: string; icon: LucideIcon; compat: InvType[]; essential?: boolean }> = {
  stock: { name: "Inventario y stock", desc: "Existencias, mínimos y alertas.", icon: Boxes, compat: ["biblioteca", "abarrotes", "papeleria", "farmacia", "electronica"], essential: true },
  productos: { name: "Productos", desc: "Catálogo maestro de registros.", icon: Package, compat: ["biblioteca", "abarrotes", "papeleria", "farmacia", "electronica"], essential: true },
  categorias: { name: "Categorías", desc: "Clasificación del catálogo.", icon: Tags, compat: ["biblioteca", "abarrotes", "papeleria", "farmacia", "electronica"] },
  ubicaciones: { name: "Ubicaciones", desc: "Pasillos, estantes y sedes.", icon: MapPin, compat: ["biblioteca", "abarrotes", "papeleria", "farmacia", "electronica"] },
  proveedores: { name: "Proveedores", desc: "Contactos y compras.", icon: Truck, compat: ["abarrotes", "papeleria", "farmacia", "electronica"] },
  lotes: { name: "Lotes", desc: "Trazabilidad por lote.", icon: Layers, compat: ["abarrotes", "farmacia"] },
  caducidades: { name: "Caducidades", desc: "Fechas de vencimiento y alertas.", icon: CalendarClock, compat: ["abarrotes", "farmacia"] },
  ventas: { name: "Ventas", desc: "Punto de venta y tickets.", icon: ShoppingCart, compat: ["abarrotes", "papeleria", "farmacia"] },
  prestamos: { name: "Préstamos", desc: "Salida temporal de ejemplares.", icon: BookMarked, compat: ["biblioteca", "electronica"] },
  devoluciones: { name: "Devoluciones", desc: "Recepción y estado.", icon: Undo2, compat: ["biblioteca", "electronica"] },
  biblio: { name: "Datos bibliográficos", desc: "ISBN, autores, editoriales.", icon: Library, compat: ["biblioteca"] },
  mantenimientos: { name: "Mantenimientos", desc: "Servicios preventivos y correctivos.", icon: Wrench, compat: ["electronica"] },
  bajas: { name: "Bajas", desc: "Retiro de activos.", icon: Trash2, compat: ["electronica"] },
  csv: { name: "Importaciones CSV", desc: "Carga masiva de registros.", icon: FileUp, compat: ["biblioteca", "abarrotes", "papeleria", "farmacia", "electronica"] },
  reportes: { name: "Reportes", desc: "Indicadores y exportaciones.", icon: BarChart3, compat: ["biblioteca", "abarrotes", "papeleria", "farmacia", "electronica"] },
};

export type Tenant = {
  id: string; name: string; logo?: string; type: InvType; plan: "Mensual" | "Trimestral" | "Anual"; status: "Activa" | "Por vencer" | "Suspendida";
  expires: string; color: string; authorized: ModuleId[]; active: ModuleId[]; users: number; city: string;
};

export const TENANTS: Tenant[] = [
  { id: "t-bib", name: "Biblioteca Municipal Juan Rulfo", type: "biblioteca", plan: "Anual", status: "Activa", expires: "12 ago 2027", color: "oklch(0.45 0.12 160)", authorized: ["stock", "productos", "categorias", "ubicaciones", "prestamos", "devoluciones", "biblio", "csv", "reportes"], active: ["stock", "productos", "categorias", "ubicaciones", "prestamos", "devoluciones", "biblio", "reportes"], users: 9, city: "Guadalajara" },
  { id: "t-aba", name: "Abarrotes Doña Lupita", type: "abarrotes", plan: "Mensual", status: "Por vencer", expires: "02 nov 2026", color: "oklch(0.58 0.16 45)", authorized: ["stock", "productos", "categorias", "proveedores", "ventas", "lotes", "caducidades", "reportes"], active: ["stock", "productos", "categorias", "proveedores", "ventas", "reportes"], users: 4, city: "Puebla" },
  { id: "t-pap", name: "Papelería El Lápiz Feliz", type: "papeleria", plan: "Trimestral", status: "Activa", expires: "15 ene 2027", color: "oklch(0.55 0.17 300)", authorized: ["stock", "productos", "categorias", "proveedores", "ventas", "csv", "reportes"], active: ["stock", "productos", "categorias", "proveedores", "ventas", "reportes"], users: 5, city: "Querétaro" },
  { id: "t-far", name: "Farmacia San Rafael", type: "farmacia", plan: "Anual", status: "Activa", expires: "30 sep 2027", color: "oklch(0.5 0.14 200)", authorized: ["stock", "productos", "categorias", "lotes", "caducidades", "ventas", "proveedores", "csv", "reportes"], active: ["stock", "productos", "lotes", "caducidades", "ventas", "reportes"], users: 7, city: "Monterrey" },
  { id: "t-ele", name: "TecnoActivos Universidad del Valle", type: "electronica", plan: "Anual", status: "Activa", expires: "01 mar 2027", color: "oklch(0.4 0.13 262)", authorized: ["stock", "productos", "ubicaciones", "prestamos", "devoluciones", "mantenimientos", "bajas", "reportes"], active: ["stock", "productos", "ubicaciones", "prestamos", "devoluciones", "mantenimientos", "bajas", "reportes"], users: 12, city: "CDMX" },
];

export type Item = { sku: string; name: string; cat: string; stock: number; min: number; price: number; loc: string; extra: string };

export const ITEMS: Record<InvType, Item[]> = {
  biblioteca: [
    { sku: "978-607-07-1", name: "Pedro Páramo — Juan Rulfo", cat: "Literatura mexicana", stock: 6, min: 2, price: 0, loc: "Estante A-3", extra: "RM Editores · 2019" },
    { sku: "978-84-376-0", name: "Cien años de soledad — G. García Márquez", cat: "Literatura latinoamericana", stock: 4, min: 2, price: 0, loc: "Estante A-5", extra: "Cátedra · 2017" },
    { sku: "978-968-16-1", name: "El laberinto de la soledad — Octavio Paz", cat: "Ensayo", stock: 1, min: 2, price: 0, loc: "Estante B-1", extra: "FCE · 2015" },
    { sku: "978-607-31-2", name: "Breve historia de México", cat: "Historia", stock: 3, min: 1, price: 0, loc: "Estante C-2", extra: "Debolsillo · 2020" },
    { sku: "978-607-11-3", name: "Aura — Carlos Fuentes", cat: "Literatura mexicana", stock: 0, min: 1, price: 0, loc: "Estante A-4", extra: "Era · 2016" },
  ],
  abarrotes: [
    { sku: "7501055300", name: "Refresco cola 600 ml", cat: "Bebidas", stock: 48, min: 24, price: 18, loc: "Refrigerador 1", extra: "Lote L2410 · cad. 15/03/27" },
    { sku: "7501000111", name: "Pan de caja blanco grande", cat: "Panadería", stock: 6, min: 10, price: 52, loc: "Pasillo 2", extra: "Lote B992 · cad. 30/10/26" },
    { sku: "7501020515", name: "Leche entera 1 L", cat: "Lácteos", stock: 22, min: 12, price: 28, loc: "Refrigerador 2", extra: "Lote LE77 · cad. 05/11/26" },
    { sku: "7501008042", name: "Frijol negro 1 kg", cat: "Abarrotes secos", stock: 35, min: 10, price: 39, loc: "Pasillo 1", extra: "Lote F102" },
    { sku: "7501031311", name: "Aceite vegetal 1 L", cat: "Abarrotes secos", stock: 2, min: 8, price: 46, loc: "Pasillo 1", extra: "Lote AC55" },
  ],
  papeleria: [
    { sku: "PAP-0001", name: "Cuaderno profesional raya 100 h", cat: "Cuadernos", stock: 120, min: 40, price: 45, loc: "Anaquel 1", extra: "Proveedor: Scribe" },
    { sku: "PAP-0002", name: "Lápiz grafito HB (caja 12)", cat: "Escritura", stock: 18, min: 20, price: 62, loc: "Anaquel 2", extra: "Proveedor: Dixon" },
    { sku: "PAP-0003", name: "Pegamento en barra 21 g", cat: "Adhesivos", stock: 64, min: 30, price: 28, loc: "Anaquel 3", extra: "Proveedor: Pritt" },
    { sku: "PAP-0004", name: "Hojas blancas carta (500)", cat: "Papel", stock: 9, min: 15, price: 115, loc: "Bodega", extra: "Proveedor: Copamex" },
    { sku: "PAP-0005", name: "Colores largos (24 pzas)", cat: "Arte", stock: 33, min: 10, price: 98, loc: "Anaquel 2", extra: "Proveedor: Prismacolor" },
  ],
  farmacia: [
    { sku: "MED-1001", name: "Paracetamol 500 mg (20 tabs)", cat: "Analgésicos", stock: 85, min: 30, price: 35, loc: "Vitrina A", extra: "Lote PX2301 · cad. 11/2026" },
    { sku: "MED-1002", name: "Amoxicilina 500 mg (12 caps)", cat: "Antibióticos", stock: 12, min: 15, price: 89, loc: "Vitrina B", extra: "Lote AM8820 · cad. 03/2027" },
    { sku: "MED-1003", name: "Loratadina 10 mg (10 tabs)", cat: "Antihistamínicos", stock: 40, min: 10, price: 54, loc: "Vitrina A", extra: "Lote LR119 · cad. 12/2026" },
    { sku: "MED-1004", name: "Omeprazol 20 mg (14 caps)", cat: "Gastro", stock: 4, min: 10, price: 72, loc: "Vitrina C", extra: "Lote OM552 · cad. 11/2026" },
    { sku: "MED-1005", name: "Suero oral 500 ml", cat: "Hidratación", stock: 26, min: 12, price: 24, loc: "Anaquel 4", extra: "Lote SU33 · cad. 08/2027" },
  ],
  electronica: [
    { sku: "SN-LNV-88213", name: "Laptop Lenovo ThinkPad E14", cat: "Cómputo", stock: 1, min: 0, price: 18900, loc: "Lab. Cómputo 2", extra: "Asignado: Dra. Ruiz" },
    { sku: "SN-EPS-11902", name: "Proyector Epson PowerLite", cat: "Audiovisual", stock: 1, min: 0, price: 12400, loc: "Almacén central", extra: "Disponible" },
    { sku: "SN-HP-55410", name: "Impresora HP LaserJet Pro", cat: "Impresión", stock: 1, min: 0, price: 6800, loc: "Rectoría", extra: "En mantenimiento" },
    { sku: "SN-DEL-77001", name: "Monitor Dell 24\" P2422H", cat: "Cómputo", stock: 1, min: 0, price: 4300, loc: "Lab. Cómputo 1", extra: "Disponible" },
    { sku: "SN-CIS-30019", name: "Switch Cisco 24 puertos", cat: "Redes", stock: 1, min: 0, price: 9900, loc: "Site", extra: "Instalado" },
  ],
};

export const REQUESTS = [
  { folio: "SOL-2026-0148", biz: "Ferretería y Papelería Ríos", owner: "Andrea Ríos", type: "papeleria" as InvType, plan: "Trimestral", date: "25 oct 2026", status: "Pendiente" },
  { folio: "SOL-2026-0147", biz: "Farmacia Vida Sana", owner: "Luis Márquez", type: "farmacia" as InvType, plan: "Anual", date: "24 oct 2026", status: "En revisión" },
  { folio: "SOL-2026-0146", biz: "Colegio Benavente — Biblioteca", owner: "Mtra. Sofía León", type: "biblioteca" as InvType, plan: "Anual", date: "22 oct 2026", status: "Pendiente" },
  { folio: "SOL-2026-0145", biz: "Mini Súper La Esquina", owner: "Jorge Cantú", type: "abarrotes" as InvType, plan: "Mensual", date: "20 oct 2026", status: "Aprobada" },
  { folio: "SOL-2026-0144", biz: "Soporte TI Hidalgo", owner: "Karla Ortiz", type: "electronica" as InvType, plan: "Mensual", date: "18 oct 2026", status: "Rechazada" },
];

export const GROWTH = [
  { m: "May", tenants: 38, ingresos: 61 }, { m: "Jun", tenants: 44, ingresos: 70 }, { m: "Jul", tenants: 51, ingresos: 82 },
  { m: "Ago", tenants: 57, ingresos: 90 }, { m: "Sep", tenants: 66, ingresos: 104 }, { m: "Oct", tenants: 74, ingresos: 118 },
];

export const DEMO_ACCOUNTS = [
  { email: "super@inventra.mx", label: "Superadministrador", role: "super" as const },
  { email: "admin@farmaciasanrafael.mx", label: "Admin · Farmacia (primer acceso)", role: "admin" as const, tenant: "t-far", firstLogin: true },
  { email: "admin@bibliotecarulfo.mx", label: "Admin · Biblioteca", role: "admin" as const, tenant: "t-bib" },
  { email: "admin@donalupita.mx", label: "Admin · Abarrotes", role: "admin" as const, tenant: "t-aba" },
  { email: "admin@lapizfeliz.mx", label: "Admin · Papelería", role: "admin" as const, tenant: "t-pap" },
  { email: "admin@tecnoactivos.mx", label: "Admin · Electrónicos", role: "admin" as const, tenant: "t-ele" },
  { email: "ventas@donalupita.mx", label: "Encargado de ventas", role: "ventas" as const, tenant: "t-aba" },
  { email: "prestamos@bibliotecarulfo.mx", label: "Encargado de préstamos", role: "prestamos" as const, tenant: "t-bib" },
  { email: "almacen@tecnoactivos.mx", label: "Encargado de almacén", role: "almacen" as const, tenant: "t-ele" },
  { email: "suspendida@ejemplo.mx", label: "Cuenta suspendida", role: "suspended" as const },
];

export const DEMO_PASSWORD = "demo1234";
export const money = (n: number) => n.toLocaleString("es-MX", { style: "currency", currency: "MXN" });
