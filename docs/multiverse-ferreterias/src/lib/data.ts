import { Boxes, Package, Tags, MapPin, Truck, Layers, ShoppingCart, Undo2, FileUp, BarChart3, Store, Warehouse, ArrowLeftRight, ClipboardCheck, ShoppingBag, Users, type LucideIcon } from "lucide-react";
export type InvType = "ferreteria";
export const INV_TYPES: Record<InvType,{label:string;icon:LucideIcon;item:string;items:string;features:string[];blurb:string}> = {
 ferreteria:{label:"Ferretería multisucursal",icon:Store,item:"Producto",items:"Productos ferreteros",blurb:"Catálogo empresarial, sucursales, centros de distribución y existencias por almacén.",features:["Sucursales y centros de distribución","Inventario por almacén","Precios generales","Ventas y cotizaciones","Transferencias autorizadas","Compras y recepción"]}
};
export type ModuleId = "stock"|"productos"|"categorias"|"ubicaciones"|"proveedores"|"lotes"|"ventas"|"devoluciones"|"csv"|"reportes"|"sucursales"|"almacenes"|"transferencias"|"compras"|"cotizaciones"|"conteos"|"usuarios";
const compat: InvType[]=["ferreteria"];
export const MODULES:Record<ModuleId,{name:string;desc:string;icon:LucideIcon;compat:InvType[];essential?:boolean}>={
 stock:{name:"Inventario y stock",desc:"Existencias por almacén y alertas.",icon:Boxes,compat,essential:true},productos:{name:"Productos",desc:"Catálogo central y precios generales.",icon:Package,compat,essential:true},categorias:{name:"Categorías y marcas",desc:"Clasificación ferretera.",icon:Tags,compat},ubicaciones:{name:"Ubicaciones",desc:"Pasillos, racks y niveles.",icon:MapPin,compat},proveedores:{name:"Proveedores",desc:"Proveedores empresariales.",icon:Truck,compat},lotes:{name:"Lotes y series",desc:"Trazabilidad cuando aplique.",icon:Layers,compat},ventas:{name:"Ventas",desc:"Punto de venta por sucursal.",icon:ShoppingCart,compat},devoluciones:{name:"Devoluciones",desc:"Devoluciones comerciales.",icon:Undo2,compat},csv:{name:"Importaciones CSV",desc:"Carga inicial de catálogo.",icon:FileUp,compat},reportes:{name:"Reportes",desc:"Reportes consolidados.",icon:BarChart3,compat},sucursales:{name:"Sucursales",desc:"Sucursales y centros de distribución.",icon:Store,compat,essential:true},almacenes:{name:"Almacenes",desc:"Almacenes por establecimiento.",icon:Warehouse,compat,essential:true},transferencias:{name:"Transferencias",desc:"Solicitud y autorización administrativa.",icon:ArrowLeftRight,compat},compras:{name:"Compras",desc:"Órdenes y recepciones.",icon:ShoppingBag,compat},cotizaciones:{name:"Cotizaciones",desc:"Presupuestos para clientes.",icon:ClipboardCheck,compat},conteos:{name:"Conteos",desc:"Conteos y ajustes controlados.",icon:ClipboardCheck,compat},usuarios:{name:"Usuarios",desc:"Asignaciones por establecimiento.",icon:Users,compat}
};
export type Tenant={id:string;name:string;logo?:string;type:InvType;plan:"Mensual"|"Trimestral"|"Anual";status:"Activa"|"Por vencer"|"Suspendida";expires:string;color:string;authorized:ModuleId[];active:ModuleId[];users:number;city:string};
const mods=Object.keys(MODULES) as ModuleId[];
export const TENANTS:Tenant[]=[
{id:"t-alfa",name:"Ferretería Alfa",type:"ferreteria",plan:"Anual",status:"Activa",expires:"12 ago 2027",color:"#04724d",authorized:mods,active:mods,users:28,city:"Ciudad de México"},
{id:"t-beta",name:"Ferretería Beta",type:"ferreteria",plan:"Trimestral",status:"Activa",expires:"15 ene 2027",color:"#315c73",authorized:mods,active:mods,users:16,city:"Puebla"},
{id:"t-gamma",name:"Grupo Ferretero Central",type:"ferreteria",plan:"Mensual",status:"Por vencer",expires:"30 nov 2026",color:"#704f3e",authorized:mods,active:mods.filter(x=>x!=="lotes"),users:34,city:"Querétaro"}
];
export type Item={sku:string;name:string;cat:string;stock:number;min:number;price:number;loc:string;extra:string};
export const ITEMS:Record<InvType,Item[]>={ferreteria:[
{sku:"FER-001",name:"Taladro inalámbrico 20 V",cat:"Herramientas eléctricas",stock:32,min:8,price:1899,loc:"Rack A-02",extra:"Marca: Truper"},
{sku:"FER-002",name:"Martillo de uña 16 oz",cat:"Herramientas manuales",stock:145,min:25,price:189,loc:"Pasillo 3",extra:"Unidad: pieza"},
{sku:"FER-003",name:"Cemento gris 50 kg",cat:"Materiales de construcción",stock:420,min:80,price:238,loc:"Patio de materiales",extra:"Unidad: saco"},
{sku:"FER-004",name:"Tornillo hexagonal 1/4",cat:"Tornillería",stock:3600,min:500,price:3.5,loc:"Gaveta T-12",extra:"Venta por pieza o caja"},
{sku:"FER-005",name:"Pintura vinílica blanca 19 L",cat:"Pinturas",stock:14,min:20,price:1190,loc:"Pasillo 8",extra:"Unidad: cubeta"},
{sku:"FER-006",name:"Cable THW calibre 12 (metro)",cat:"Material eléctrico",stock:980,min:150,price:22,loc:"Rack E-04",extra:"Unidad: metro"}
]};
export const REQUESTS=[{folio:"SOL-2026-0148",biz:"Ferretería Los Pinos",owner:"Andrea Ríos",type:"ferreteria" as InvType,plan:"Trimestral",date:"05 nov 2026",status:"Pendiente"},{folio:"SOL-2026-0147",biz:"Grupo Ferretero del Norte",owner:"Luis Márquez",type:"ferreteria" as InvType,plan:"Anual",date:"04 nov 2026",status:"En revisión"},{folio:"SOL-2026-0146",biz:"Ferretería Industrial Oriente",owner:"Sofía León",type:"ferreteria" as InvType,plan:"Anual",date:"03 nov 2026",status:"Pendiente"}];
export const GROWTH=[{m:"May",tenants:38,ingresos:61},{m:"Jun",tenants:44,ingresos:70},{m:"Jul",tenants:51,ingresos:82},{m:"Ago",tenants:57,ingresos:90},{m:"Sep",tenants:66,ingresos:104},{m:"Oct",tenants:74,ingresos:118}];
export const DEMO_ACCOUNTS=[{email:"super@multiverse.mx",label:"Superadministrador",role:"super" as const},{email:"admin@ferreteriaalfa.mx",label:"Admin · Ferretería Alfa (primer acceso)",role:"admin" as const,tenant:"t-alfa",firstLogin:true},{email:"admin@ferreteriabeta.mx",label:"Admin · Ferretería Beta",role:"admin" as const,tenant:"t-beta"},{email:"ventas@ferreteriaalfa.mx",label:"Encargado de ventas",role:"ventas" as const,tenant:"t-alfa"},{email:"almacen@ferreteriaalfa.mx",label:"Encargado de almacén",role:"almacen" as const,tenant:"t-alfa"},{email:"suspendida@ejemplo.mx",label:"Cuenta suspendida",role:"suspended" as const}];
export const DEMO_PASSWORD="demo1234";
export const money=(n:number)=>n.toLocaleString("es-MX",{style:"currency",currency:"MXN"});
