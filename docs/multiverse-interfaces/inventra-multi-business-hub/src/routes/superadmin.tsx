import { createFileRoute } from "@tanstack/react-router";
import { useMemo, useState } from "react";
import { Area, AreaChart, Bar, BarChart, CartesianGrid, Cell, Pie, PieChart, ResponsiveContainer, Tooltip, XAxis, YAxis } from "recharts";
import {
  LayoutDashboard, PlusCircle, Boxes, Users, Puzzle, BarChart3, CreditCard, Repeat, Megaphone, Settings, Building2, Clock,
  CheckCircle2, AlertTriangle, DollarSign, UserCheck, Search, Check, X, Eye, Lock, KeyRound, Mail,
} from "lucide-react";
import { Shell, PageHeader, Panel, Kpi, Status, useGuard, Empty } from "@/components/inventra/Shell";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Switch } from "@/components/ui/switch";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from "@/components/ui/dialog";
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from "@/components/ui/alert-dialog";
import { GROWTH, INV_TYPES, MODULES, REQUESTS, type InvType, type ModuleId } from "@/lib/data";
import { useSession } from "@/lib/session";
import { cn } from "@/lib/utils";
import { toast } from "sonner";

export const Route = createFileRoute("/superadmin")({
  head: () => ({ meta: [
    { title: "Panel global — Inventra" },
    { name: "description", content: "Dashboard del Superadministrador: tenants, solicitudes, módulos y suscripciones." },
    { property: "og:title", content: "Panel global — Inventra" },
    { property: "og:description", content: "Administración global de la plataforma Inventra." },
  ] }),
  component: Super,
});

const GROUPS = {
  plataforma: { label: "Plataforma", cats: [
    { id: "dashboard", label: "Dashboard", icon: LayoutDashboard }, { id: "nuevo", label: "Nuevo tenant", icon: PlusCircle },
    { id: "inventarios", label: "Inventarios", icon: Boxes }, { id: "usuarios", label: "Usuarios", icon: Users },
    { id: "modulos", label: "Módulos", icon: Puzzle }, { id: "reportes", label: "Reportes", icon: BarChart3 }] },
  comercial: { label: "Comercial", cats: [
    { id: "planes", label: "Planes", icon: CreditCard }, { id: "suscripciones", label: "Suscripciones", icon: Repeat }, { id: "promociones", label: "Promociones", icon: Megaphone }] },
  ajustes: { label: "Configuración", cats: [{ id: "config", label: "Plataforma", icon: Settings }] },
};
type G = keyof typeof GROUPS;
const PIE = ["var(--chart-1)", "var(--chart-2)", "var(--chart-3)", "var(--chart-4)", "var(--chart-5)"];

function Super() {
  const s = useGuard(["super"]);
  const [g, setG] = useState<G>("plataforma");
  const [cat, setCat] = useState("dashboard");
  if (!s) return null;
  return (
    <Shell main={(Object.keys(GROUPS) as G[]).map((k) => ({ id: k, label: GROUPS[k].label }))} mainActive={g}
      onMain={(id) => { setG(id as G); setCat(GROUPS[id as G].cats[0]!.id); }} cats={GROUPS[g].cats} catActive={cat} onCat={setCat} context="Inventra · Global">
      {cat === "dashboard" && <Dashboard go={setCat} />}
      {cat === "nuevo" && <NuevoTenant />}
      {cat === "inventarios" && <Inventarios />}
      {cat === "usuarios" && <Usuarios />}
      {cat === "modulos" && <Modulos />}
      {cat === "reportes" && <Reportes />}
      {cat === "planes" && <Planes />}
      {cat === "suscripciones" && <Suscripciones />}
      {cat === "promociones" && <Promos />}
      {cat === "config" && <Panel title="Configuración de la plataforma"><div className="grid gap-4 md:grid-cols-2">{["Nombre comercial", "Correo de soporte", "Zona horaria"].map((l, i) => <div key={l} className="space-y-1.5"><Label>{l}</Label><Input defaultValue={["Inventra", "soporte@inventra.mx", "America/Mexico_City"][i]} /></div>)}</div><Button className="mt-4" onClick={() => toast.success("Configuración guardada")}>Guardar</Button></Panel>}
    </Shell>
  );
}

function Dashboard({ go }: { go: (c: string) => void }) {
  const { tenants } = useSession();
  const dist = (Object.keys(INV_TYPES) as InvType[]).map((k, i) => ({ name: INV_TYPES[k].label, value: [16, 21, 12, 15, 10][i] }));
  return (<>
    <PageHeader title="Dashboard global" subtitle="Lunes 26 de octubre de 2026 · Resumen de la plataforma" actions={<Button onClick={() => go("nuevo")}><PlusCircle className="mr-2 h-4 w-4" />Crear tenant</Button>} />
    <div className="grid grid-cols-2 gap-4 lg:grid-cols-6">
      <Kpi label="Tenants activos" value="74" hint="+8 este mes" icon={Building2} />
      <Kpi label="Solicitudes pendientes" value="3" hint="2 nuevas hoy" icon={Clock} tone="warning" />
      <Kpi label="Suscripciones activas" value="71" icon={CheckCircle2} tone="success" />
      <Kpi label="Próximas a vencer" value="6" hint="en 15 días" icon={AlertTriangle} tone="danger" />
      <Kpi label="Usuarios registrados" value="612" icon={UserCheck} />
      <Kpi label="Ingresos (oct)" value="$118 k" hint="MXN, ficticio" icon={DollarSign} tone="success" />
    </div>
    <div className="mt-6 grid gap-6 lg:grid-cols-3">
      <Panel title="Crecimiento de tenants" className="lg:col-span-2"><div className="h-64"><ResponsiveContainer><AreaChart data={GROWTH}><defs><linearGradient id="g1" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stopColor="var(--chart-1)" stopOpacity={0.25} /><stop offset="1" stopColor="var(--chart-1)" stopOpacity={0} /></linearGradient></defs><CartesianGrid strokeDasharray="3 3" stroke="var(--border)" vertical={false} /><XAxis dataKey="m" tickLine={false} axisLine={false} fontSize={12} /><YAxis tickLine={false} axisLine={false} fontSize={12} width={30} /><Tooltip /><Area dataKey="tenants" stroke="var(--chart-1)" strokeWidth={2.5} fill="url(#g1)" /></AreaChart></ResponsiveContainer></div></Panel>
      <Panel title="Tenants por tipo"><div className="h-48"><ResponsiveContainer><PieChart><Pie data={dist} dataKey="value" innerRadius={50} outerRadius={80} paddingAngle={3}>{dist.map((_, i) => <Cell key={i} fill={PIE[i]} />)}</Pie><Tooltip /></PieChart></ResponsiveContainer></div>
        <div className="mt-2 grid grid-cols-2 gap-1 text-xs">{dist.map((d, i) => <span key={d.name} className="flex items-center gap-1.5"><span className="h-2 w-2 rounded-full" style={{ background: PIE[i] }} />{d.name} · {d.value}</span>)}</div></Panel>
      <Panel title="Ingresos por suscripciones (miles MXN)" className="lg:col-span-2"><div className="h-56"><ResponsiveContainer><BarChart data={GROWTH}><CartesianGrid strokeDasharray="3 3" stroke="var(--border)" vertical={false} /><XAxis dataKey="m" tickLine={false} axisLine={false} fontSize={12} /><YAxis tickLine={false} axisLine={false} fontSize={12} width={30} /><Tooltip /><Bar dataKey="ingresos" fill="var(--chart-2)" radius={[6, 6, 0, 0]} /></BarChart></ResponsiveContainer></div></Panel>
      <Panel title="Alertas administrativas"><ul className="space-y-3 text-sm">
        {tenants.filter((t) => t.status !== "Activa").map((t) => <li key={t.id} className="flex gap-3 rounded-xl bg-warning/10 p-3"><AlertTriangle className="h-4 w-4 shrink-0 text-warning-foreground" /><span><b>{t.name}</b> vence el {t.expires}</span></li>)}
        <li className="flex gap-3 rounded-xl bg-destructive/5 p-3"><X className="h-4 w-4 shrink-0 text-destructive" />2 pagos rechazados por el banco</li>
        <li className="flex gap-3 rounded-xl bg-accent p-3"><Mail className="h-4 w-4 shrink-0 text-primary" />4 invitaciones sin aceptar</li></ul></Panel>
      <Panel title="Solicitudes pendientes" className="lg:col-span-2" action={<Button variant="ghost" size="sm" onClick={() => go("nuevo")}>Ver todas</Button>}>
        <div className="divide-y divide-border">{REQUESTS.filter((r) => r.status === "Pendiente" || r.status === "En revisión").map((r) => { const T = INV_TYPES[r.type]; return <div key={r.folio} className="flex items-center gap-3 py-3"><span className="grid h-9 w-9 place-items-center rounded-xl bg-accent text-primary"><T.icon className="h-4 w-4" /></span><div className="flex-1"><div className="text-sm font-semibold">{r.biz}</div><div className="text-xs text-muted-foreground">{r.folio} · {r.plan}</div></div><Status s={r.status} /></div>; })}</div></Panel>
      <Panel title="Actividad reciente"><ul className="space-y-3 text-sm">{[["Tenant creado", "Mini Súper La Esquina", "hace 2 h"], ["Módulo habilitado", "Lotes · Farmacia San Rafael", "hace 5 h"], ["Plan renovado", "Biblioteca Juan Rulfo", "ayer"], ["Solicitud rechazada", "Soporte TI Hidalgo", "hace 2 días"]].map(([a, b, c]) => <li key={b} className="flex gap-3"><span className="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-primary" /><div><div className="font-medium">{a}</div><div className="text-xs text-muted-foreground">{b} · {c}</div></div></li>)}</ul></Panel>
    </div>
  </>);
}

const STEPS = ["Datos del negocio", "Tipo de inventario", "Plan y vigencia", "Módulos", "Administrador", "Resumen"];

function NuevoTenant() {
  const [reqs, setReqs] = useState(REQUESTS);
  const [filter, setFilter] = useState("Todas");
  const [q, setQ] = useState("");
  const [wizard, setWizard] = useState<null | { folio?: string; biz: string; owner: string; type: InvType; plan: string }>(null);
  const [reject, setReject] = useState<string | null>(null);
  const rows = reqs.filter((r) => (filter === "Todas" || r.status === filter) && (r.biz + r.folio + r.owner).toLowerCase().includes(q.toLowerCase()));
  return (<>
    <PageHeader title="Nuevo tenant" subtitle="Gestiona solicitudes y crea espacios de trabajo." actions={<Button onClick={() => setWizard({ biz: "", owner: "", type: "abarrotes", plan: "Mensual" })}><PlusCircle className="mr-2 h-4 w-4" />Crear sin solicitud</Button>} />
    <Panel>
      <div className="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div className="flex flex-wrap gap-2">{["Todas", "Pendiente", "En revisión", "Aprobada", "Rechazada"].map((f) => <button key={f} onClick={() => setFilter(f)} className={cn("rounded-full border px-3 py-1.5 text-sm font-medium transition-colors", filter === f ? "border-foreground bg-foreground text-background" : "border-border hover:border-foreground/40")}>{f}</button>)}</div>
        <div className="relative md:w-72"><Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" /><Input value={q} onChange={(e) => setQ(e.target.value)} placeholder="Buscar folio o negocio" className="pl-9" /></div>
      </div>
      <div className="overflow-x-auto"><table className="w-full min-w-[800px] text-sm">
        <thead><tr className="border-b border-border text-left text-xs uppercase tracking-wide text-muted-foreground">{["Folio", "Negocio", "Responsable", "Tipo", "Plan", "Fecha", "Estado", "Acciones"].map((h) => <th key={h} className="px-3 py-3 font-semibold">{h}</th>)}</tr></thead>
        <tbody>{rows.map((r) => (
          <tr key={r.folio} className="border-b border-border last:border-0 hover:bg-muted/50">
            <td className="px-3 py-3 font-mono text-xs">{r.folio}</td><td className="px-3 py-3 font-semibold">{r.biz}</td><td className="px-3 py-3">{r.owner}</td>
            <td className="px-3 py-3">{INV_TYPES[r.type].label}</td><td className="px-3 py-3">{r.plan}</td><td className="px-3 py-3 text-muted-foreground">{r.date}</td><td className="px-3 py-3"><Status s={r.status} /></td>
            <td className="px-3 py-3"><div className="flex gap-1">
              {(r.status === "Pendiente" || r.status === "En revisión") ? (<>
                <Button size="sm" variant="ghost" onClick={() => setReqs(reqs.map((x) => x.folio === r.folio ? { ...x, status: "En revisión" } : x))}><Eye className="h-4 w-4" /></Button>
                <Button size="sm" onClick={() => setWizard({ folio: r.folio, biz: r.biz, owner: r.owner, type: r.type, plan: r.plan })}><Check className="mr-1 h-4 w-4" />Aprobar</Button>
                <Button size="sm" variant="ghost" className="text-destructive" onClick={() => setReject(r.folio)}><X className="h-4 w-4" /></Button></>) : <span className="text-xs text-muted-foreground">—</span>}
            </div></td>
          </tr>))}</tbody>
      </table>{rows.length === 0 && <Empty icon={Search} title="Sin resultados" text="No hay solicitudes con ese filtro." />}</div>
    </Panel>
    <AlertDialog open={!!reject} onOpenChange={(o) => !o && setReject(null)}><AlertDialogContent><AlertDialogHeader><AlertDialogTitle>¿Rechazar solicitud {reject}?</AlertDialogTitle><AlertDialogDescription>Se notificará al solicitante. Esta acción no se puede deshacer.</AlertDialogDescription></AlertDialogHeader>
      <AlertDialogFooter><AlertDialogCancel>Cancelar</AlertDialogCancel><AlertDialogAction className="bg-destructive text-destructive-foreground hover:bg-destructive/90" onClick={() => { setReqs(reqs.map((x) => x.folio === reject ? { ...x, status: "Rechazada" } : x)); toast.error("Solicitud rechazada"); }}>Rechazar</AlertDialogAction></AlertDialogFooter></AlertDialogContent></AlertDialog>
    {wizard && <Wizard init={wizard} onClose={() => setWizard(null)} onDone={() => { if (wizard.folio) setReqs(reqs.map((x) => x.folio === wizard.folio ? { ...x, status: "Aprobada" } : x)); setWizard(null); }} />}
  </>);
}

function Wizard({ init, onClose, onDone }: { init: { biz: string; owner: string; type: InvType; plan: string }; onClose: () => void; onDone: () => void }) {
  const [step, setStep] = useState(0);
  const [d, setD] = useState({ ...init, start: "2026-10-26", email: "", mods: [] as ModuleId[] });
  const compat = useMemo(() => (Object.keys(MODULES) as ModuleId[]).filter((m) => MODULES[m].compat.includes(d.type)), [d.type]);
  const mods = d.mods.length ? d.mods : compat;
  const valid = [d.biz.length > 1 && d.owner.length > 1, true, true, true, /\S+@\S+\.\S+/.test(d.email), true][step];
  return (
    <Dialog open onOpenChange={(o) => !o && onClose()}>
      <DialogContent className="max-w-2xl rounded-2xl">
        <DialogHeader><DialogTitle>Asistente de creación de tenant</DialogTitle><DialogDescription>Paso {step + 1} de {STEPS.length} · {STEPS[step]}</DialogDescription></DialogHeader>
        <div className="flex gap-1">{STEPS.map((_, i) => <div key={i} className={cn("h-1 flex-1 rounded-full transition-colors", i <= step ? "bg-primary" : "bg-border")} />)}</div>
        <div key={step} className="min-h-[240px] animate-fade-up py-2">
          {step === 0 && <div className="grid gap-4 md:grid-cols-2"><div className="space-y-1.5"><Label>Nombre del negocio</Label><Input value={d.biz} onChange={(e) => setD({ ...d, biz: e.target.value })} /></div><div className="space-y-1.5"><Label>Responsable</Label><Input value={d.owner} onChange={(e) => setD({ ...d, owner: e.target.value })} /></div></div>}
          {step === 1 && <div className="grid grid-cols-2 gap-3 md:grid-cols-3">{(Object.keys(INV_TYPES) as InvType[]).map((k) => { const T = INV_TYPES[k]; return <button key={k} onClick={() => setD({ ...d, type: k, mods: [] })} className={cn("flex flex-col items-center gap-2 rounded-xl border p-4 text-sm font-semibold", d.type === k ? "border-primary bg-accent text-primary" : "border-border")}><T.icon className="h-6 w-6" strokeWidth={1.5} />{T.label}</button>; })}</div>}
          {step === 2 && <div className="space-y-4"><div className="grid grid-cols-3 gap-2">{["Mensual", "Trimestral", "Anual"].map((p) => <button key={p} onClick={() => setD({ ...d, plan: p })} className={cn("rounded-xl border p-4 font-semibold", d.plan === p ? "border-primary bg-accent text-primary" : "border-border")}>{p}</button>)}</div><div className="space-y-1.5"><Label>Inicio de vigencia</Label><Input type="date" value={d.start} onChange={(e) => setD({ ...d, start: e.target.value })} /></div></div>}
          {step === 3 && <div className="grid gap-2 md:grid-cols-2">{compat.map((m) => { const M = MODULES[m]; const on = mods.includes(m); return <label key={m} className="flex items-center gap-3 rounded-xl border border-border p-3"><M.icon className="h-4 w-4 text-primary" /><span className="flex-1 text-sm font-medium">{M.name}{M.essential && <span className="ml-1 text-xs text-muted-foreground">(esencial)</span>}</span><Switch checked={on} disabled={M.essential} onCheckedChange={(v) => setD({ ...d, mods: v ? [...mods, m] : mods.filter((x) => x !== m) })} /></label>; })}</div>}
          {step === 4 && <div className="grid gap-4 md:grid-cols-2"><div className="space-y-1.5"><Label>Nombre del administrador</Label><Input defaultValue={d.owner} /></div><div className="space-y-1.5"><Label>Correo del administrador</Label><Input type="email" value={d.email} onChange={(e) => setD({ ...d, email: e.target.value })} placeholder="admin@negocio.mx" /></div><p className="text-xs text-muted-foreground md:col-span-2">Se generará una contraseña temporal que deberá cambiar en su primer acceso.</p></div>}
          {step === 5 && <dl className="grid grid-cols-2 gap-3 rounded-xl bg-muted p-4 text-sm">{[["Negocio", d.biz], ["Tipo", INV_TYPES[d.type].label], ["Plan", `${d.plan} desde ${d.start}`], ["Administrador", d.email], ["Módulos", mods.map((m) => MODULES[m].name).join(", ")]].map(([k, v]) => <div key={k} className={k === "Módulos" ? "col-span-2" : ""}><dt className="text-xs text-muted-foreground">{k}</dt><dd className="font-semibold">{v}</dd></div>)}</dl>}
        </div>
        <div className="flex justify-between"><Button variant="ghost" onClick={() => (step ? setStep(step - 1) : onClose())}>{step ? "Atrás" : "Cancelar"}</Button>
          {step < STEPS.length - 1 ? <Button disabled={!valid} onClick={() => setStep(step + 1)}>Continuar</Button> : <Button onClick={() => { toast.success(`Tenant "${d.biz}" creado`, { description: `Invitación enviada a ${d.email}` }); onDone(); }}>Crear tenant y enviar invitación</Button>}</div>
      </DialogContent>
    </Dialog>
  );
}

function Inventarios() {
  const { tenants } = useSession();
  return (<>
    <PageHeader title="Tipos de inventario" subtitle="Datos agregados. La información operativa de cada tenant es privada." />
    <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">{(Object.keys(INV_TYPES) as InvType[]).map((k, i) => { const T = INV_TYPES[k]; const ts = tenants.filter((t) => t.type === k); return (
      <Panel key={k}><div className="flex items-center gap-3"><span className="grid h-12 w-12 place-items-center rounded-2xl bg-accent text-primary"><T.icon className="h-6 w-6" strokeWidth={1.5} /></span><div><h3 className="font-bold">{T.label}</h3><p className="text-xs text-muted-foreground">{[16, 21, 12, 15, 10][i]} tenants · {[140, 88, 61, 104, 219][i]} usuarios</p></div></div>
        <div className="mt-4 flex flex-wrap gap-1.5">{Object.values(MODULES).filter((m) => m.compat.includes(k)).map((m) => <span key={m.name} className="rounded-full bg-muted px-2.5 py-1 text-xs">{m.name}</span>)}</div>
        <div className="mt-4 border-t border-border pt-3 text-xs text-muted-foreground">Ejemplo: {ts.map((t) => t.name).join(", ")}</div>
        <Button variant="outline" size="sm" className="mt-3" onClick={() => toast.info(`Configuración general de ${T.label}`)}>Configuración general</Button></Panel>); })}</div>
  </>);
}

function Usuarios() {
  const [users, setUsers] = useState([
    { n: "Mariana Torres", e: "admin@farmaciasanrafael.mx", r: "Administrador", t: "Farmacia San Rafael", s: "Activo" },
    { n: "Ramón Aguilar", e: "admin@bibliotecarulfo.mx", r: "Administrador", t: "Biblioteca Juan Rulfo", s: "Activo" },
    { n: "Guadalupe Hernández", e: "admin@donalupita.mx", r: "Administrador", t: "Abarrotes Doña Lupita", s: "Activo" },
    { n: "Iván Salas", e: "ivan@inventra.mx", r: "Superadministrador", t: "Global", s: "Activo" },
    { n: "Karla Ortiz", e: "karla@soporteti.mx", r: "Administrador", t: "Soporte TI Hidalgo", s: "Invitado" },
    { n: "Héctor Méndez", e: "hector@ejemplo.mx", r: "Administrador", t: "Papelería Centro", s: "Bloqueado" },
  ]);
  const [conf, setConf] = useState<string | null>(null);
  return (<>
    <PageHeader title="Usuarios" subtitle="Usuarios globales y administradores de tenants." actions={<Button onClick={() => toast.success("Invitación enviada")}><Mail className="mr-2 h-4 w-4" />Invitar usuario</Button>} />
    <Panel><div className="overflow-x-auto"><table className="w-full min-w-[720px] text-sm"><thead><tr className="border-b border-border text-left text-xs uppercase text-muted-foreground">{["Usuario", "Rol", "Tenant", "Estado", ""].map((h) => <th key={h} className="px-3 py-3">{h}</th>)}</tr></thead>
      <tbody>{users.map((u) => <tr key={u.e} className="border-b border-border last:border-0"><td className="px-3 py-3"><div className="font-semibold">{u.n}</div><div className="text-xs text-muted-foreground">{u.e}</div></td><td className="px-3 py-3">{u.r}</td><td className="px-3 py-3">{u.t}</td><td className="px-3 py-3"><Status s={u.s} /></td>
        <td className="px-3 py-3 text-right"><Button size="sm" variant="ghost" onClick={() => toast.success(`Enlace de restablecimiento enviado a ${u.e}`)}><KeyRound className="h-4 w-4" /></Button><Button size="sm" variant="ghost" className={u.s === "Bloqueado" ? "text-success" : "text-destructive"} onClick={() => setConf(u.e)}><Lock className="h-4 w-4" /></Button></td></tr>)}</tbody></table></div></Panel>
    <AlertDialog open={!!conf} onOpenChange={(o) => !o && setConf(null)}><AlertDialogContent><AlertDialogHeader><AlertDialogTitle>¿Cambiar estado de bloqueo?</AlertDialogTitle><AlertDialogDescription>El usuario {conf} {users.find((u) => u.e === conf)?.s === "Bloqueado" ? "recuperará el acceso" : "no podrá iniciar sesión"}.</AlertDialogDescription></AlertDialogHeader>
      <AlertDialogFooter><AlertDialogCancel>Cancelar</AlertDialogCancel><AlertDialogAction onClick={() => { setUsers(users.map((u) => u.e === conf ? { ...u, s: u.s === "Bloqueado" ? "Activo" : "Bloqueado" } : u)); toast.success("Estado actualizado"); }}>Confirmar</AlertDialogAction></AlertDialogFooter></AlertDialogContent></AlertDialog>
  </>);
}

function Modulos() {
  const { tenants, updateTenant } = useSession();
  const [tid, setTid] = useState(tenants[0]!.id);
  const t = tenants.find((x) => x.id === tid)!;
  return (<>
    <PageHeader title="Catálogo de módulos" subtitle="Compatibilidad por tipo de inventario y autorización por tenant." />
    <div className="grid gap-6 lg:grid-cols-[1.3fr_1fr]">
      <Panel><div className="overflow-x-auto"><table className="w-full min-w-[640px] text-sm"><thead><tr className="border-b border-border text-left text-xs uppercase text-muted-foreground"><th className="px-3 py-3">Módulo</th>{(Object.keys(INV_TYPES) as InvType[]).map((k) => { const T = INV_TYPES[k]; return <th key={k} className="px-2 py-3 text-center" title={T.label}><T.icon className="mx-auto h-4 w-4" /></th>; })}</tr></thead>
        <tbody>{(Object.keys(MODULES) as ModuleId[]).map((m) => { const M = MODULES[m]; return <tr key={m} className="border-b border-border last:border-0"><td className="flex items-center gap-2 px-3 py-2.5 font-medium"><M.icon className="h-4 w-4 text-primary" />{M.name}</td>{(Object.keys(INV_TYPES) as InvType[]).map((k) => <td key={k} className="text-center">{M.compat.includes(k) ? <Check className="mx-auto h-4 w-4 text-success" /> : <span className="text-border">—</span>}</td>)}</tr>; })}</tbody></table></div></Panel>
      <Panel title="Autorizar módulos por tenant">
        <select value={tid} onChange={(e) => setTid(e.target.value)} className="mb-4 h-10 w-full rounded-md border border-input bg-surface px-3 text-sm">{tenants.map((x) => <option key={x.id} value={x.id}>{x.name}</option>)}</select>
        <div className="space-y-2">{(Object.keys(MODULES) as ModuleId[]).filter((m) => MODULES[m].compat.includes(t.type)).map((m) => { const M = MODULES[m]; const on = t.authorized.includes(m); return (
          <label key={m} className="flex items-center gap-3 rounded-xl border border-border p-3"><M.icon className="h-4 w-4 text-primary" /><span className="flex-1 text-sm">{M.name}</span><Switch checked={on} disabled={M.essential} onCheckedChange={(v) => { updateTenant(t.id, { authorized: v ? [...t.authorized, m] : t.authorized.filter((x) => x !== m), active: v ? t.active : t.active.filter((x) => x !== m) }); toast.success(`${M.name} ${v ? "autorizado" : "retirado"}`); }} /></label>); })}</div>
      </Panel>
    </div>
  </>);
}

function Reportes() {
  const [type, setType] = useState("Todos");
  return (<>
    <PageHeader title="Reportes de plataforma" actions={<>
      <Input type="date" defaultValue="2026-05-01" className="w-40" /><Input type="date" defaultValue="2026-10-31" className="w-40" />
      <select value={type} onChange={(e) => setType(e.target.value)} className="h-10 rounded-md border border-input bg-surface px-3 text-sm"><option>Todos</option>{Object.values(INV_TYPES).map((t) => <option key={t.label}>{t.label}</option>)}</select>
      <select className="h-10 rounded-md border border-input bg-surface px-3 text-sm"><option>Todos los estados</option><option>Activa</option><option>Por vencer</option><option>Suspendida</option></select></>} />
    <div className="grid grid-cols-2 gap-4 lg:grid-cols-4"><Kpi label="Renovaciones (6m)" value="58" icon={Repeat} tone="success" /><Kpi label="Tasa de renovación" value="91%" icon={CheckCircle2} /><Kpi label="Nuevas suscripciones" value="36" icon={CreditCard} /><Kpi label="Acciones administrativas" value="1,204" icon={Settings} /></div>
    <div className="mt-6 grid gap-6 lg:grid-cols-2">
      <Panel title="Crecimiento de tenants"><div className="h-60"><ResponsiveContainer><AreaChart data={GROWTH}><CartesianGrid strokeDasharray="3 3" stroke="var(--border)" vertical={false} /><XAxis dataKey="m" fontSize={12} /><YAxis fontSize={12} width={30} /><Tooltip /><Area dataKey="tenants" stroke="var(--chart-1)" fill="var(--chart-1)" fillOpacity={0.12} /></AreaChart></ResponsiveContainer></div></Panel>
      <Panel title="Ingresos (miles MXN)"><div className="h-60"><ResponsiveContainer><BarChart data={GROWTH}><CartesianGrid strokeDasharray="3 3" stroke="var(--border)" vertical={false} /><XAxis dataKey="m" fontSize={12} /><YAxis fontSize={12} width={30} /><Tooltip /><Bar dataKey="ingresos" fill="var(--chart-4)" radius={[6, 6, 0, 0]} /></BarChart></ResponsiveContainer></div></Panel>
      <Panel title="Tipos de inventario más utilizados" className="lg:col-span-2"><div className="space-y-3">{(Object.keys(INV_TYPES) as InvType[]).map((k, i) => { const v = [16, 21, 12, 15, 10][i] ?? 0; return <div key={k} className="flex items-center gap-3 text-sm"><span className="w-44">{INV_TYPES[k].label}</span><div className="h-2.5 flex-1 rounded-full bg-muted"><div className="h-full rounded-full bg-primary transition-all" style={{ width: `${(v / 21) * 100}%` }} /></div><span className="w-8 text-right font-semibold">{v}</span></div>; })}</div></Panel>
    </div>
  </>);
}

function Planes() {
  return (<><PageHeader title="Planes" subtitle="Precios configurables por periodicidad." />
    <div className="grid gap-5 md:grid-cols-3">{["Mensual", "Trimestral", "Anual"].map((p) => <Panel key={p} title={`Plan ${p}`}><div className="space-y-3"><div className="space-y-1.5"><Label>Precio (MXN)</Label><Input placeholder="Definir precio" /></div><label className="flex items-center justify-between text-sm">Mostrar en sitio público<Switch defaultChecked /></label><label className="flex items-center justify-between text-sm">Marcar como recomendado<Switch defaultChecked={p === "Anual"} /></label><Button className="w-full" onClick={() => toast.success(`Plan ${p} guardado`)}>Guardar</Button></div></Panel>)}</div></>);
}
function Suscripciones() {
  const { tenants } = useSession();
  return (<><PageHeader title="Suscripciones" /><Panel><div className="overflow-x-auto"><table className="w-full min-w-[600px] text-sm"><thead><tr className="border-b border-border text-left text-xs uppercase text-muted-foreground">{["Tenant", "Tipo", "Plan", "Vence", "Estado"].map((h) => <th key={h} className="px-3 py-3">{h}</th>)}</tr></thead><tbody>{tenants.map((t) => <tr key={t.id} className="border-b border-border last:border-0"><td className="px-3 py-3 font-semibold">{t.name}</td><td className="px-3 py-3">{INV_TYPES[t.type].label}</td><td className="px-3 py-3">{t.plan}</td><td className="px-3 py-3">{t.expires}</td><td className="px-3 py-3"><Status s={t.status} /></td></tr>)}</tbody></table></div></Panel></>);
}
function Promos() {
  const [items, setItems] = useState([{ t: "Temporada de regreso a clases", on: true }, { t: "Módulo de caducidades", on: true }, { t: "Migración sin costo", on: true }, { t: "Webinar bibliotecas", on: false }]);
  return (<><PageHeader title="Promociones del sitio público" subtitle="Banners y anuncios visibles en la landing." actions={<Button onClick={() => setItems([...items, { t: "Nueva promoción", on: false }])}><PlusCircle className="mr-2 h-4 w-4" />Nueva</Button>} />
    <div className="grid gap-4 md:grid-cols-2">{items.map((it, i) => <Panel key={i}><div className="flex items-center gap-3"><Megaphone className="h-5 w-5 text-primary" /><Input value={it.t} onChange={(e) => setItems(items.map((x, j) => j === i ? { ...x, t: e.target.value } : x))} /><Switch checked={it.on} onCheckedChange={(v) => setItems(items.map((x, j) => j === i ? { ...x, on: v } : x))} /></div></Panel>)}</div></>);
}
