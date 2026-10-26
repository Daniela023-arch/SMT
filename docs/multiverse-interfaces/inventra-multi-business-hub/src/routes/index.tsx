import { createFileRoute, Link } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { ArrowRight, Check, ChevronLeft, ChevronRight, ShieldCheck, Sparkles, Zap, Building2, BarChart3, Boxes, Bell, Megaphone, Star } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from "@/components/ui/dialog";
import { INV_TYPES, MODULES, type InvType } from "@/lib/data";
import { Logo } from "@/components/inventra/Shell";
import { cn } from "@/lib/utils";

export const Route = createFileRoute("/")({
  head: () => ({
    meta: [
      { title: "Inventra — Controla tu inventario. Haz crecer tu negocio." },
      { name: "description", content: "Administra productos, existencias, ventas, préstamos y operaciones desde un solo lugar. Para bibliotecas, abarrotes, papelerías, farmacias y equipos electrónicos." },
      { property: "og:title", content: "Inventra — Inventarios inteligentes" },
      { property: "og:description", content: "Plataforma SaaS adaptable para administrar inventarios de cualquier negocio." },
    ],
  }),
  component: Landing,
});

export function PublicHeader() {
  const links = [["Inicio", "/#inicio"], ["Funcionalidades", "/#funcionalidades"], ["Tipos de inventario", "/#tipos"], ["Planes", "/#planes"]];
  return (
    <header className="sticky top-0 z-40 border-b border-border bg-surface/90 backdrop-blur">
      <div className="mx-auto flex h-[72px] max-w-7xl items-center justify-between px-4 md:px-8">
        <Logo />
        <nav className="hidden items-center gap-1 lg:flex">
          {links.map(([l, h]) => <a key={l} href={h} className="rounded-full px-4 py-2 text-[15px] font-medium text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">{l}</a>)}
          <Link to="/solicitar" className="rounded-full px-4 py-2 text-[15px] font-medium text-muted-foreground hover:bg-muted hover:text-foreground">Solicitar sistema</Link>
        </nav>
        <Button asChild className="rounded-full px-5 shadow-soft"><Link to="/login">Iniciar sesión</Link></Button>
      </div>
    </header>
  );
}

const ADS = [
  { tag: "Nuevo", title: "Módulo de caducidades con alertas automáticas", text: "Recibe avisos 30, 15 y 7 días antes del vencimiento de cada lote.", icon: Bell },
  { tag: "Promoción", title: "Migra tu inventario sin costo de implementación", text: "Te ayudamos a importar tu catálogo desde Excel o CSV durante tu primer mes.", icon: Sparkles },
  { tag: "Webinar", title: "Buenas prácticas para bibliotecas escolares", text: "Jueves 12 de noviembre · 18:00 h · Cupo limitado.", icon: Megaphone },
];

function Landing() {
  const [ad, setAd] = useState(0);
  const [period, setPeriod] = useState<"Mensual" | "Trimestral" | "Anual">("Anual");
  const [open, setOpen] = useState<InvType | null>(null);
  useEffect(() => { const t = setInterval(() => setAd((a) => (a + 1) % ADS.length), 5000); return () => clearInterval(t); }, []);

  return (
    <div className="min-h-screen bg-surface">
      <PublicHeader />
      {/* HERO */}
      <section id="inicio" className="bg-hero">
        <div className="mx-auto grid max-w-7xl items-center gap-12 px-4 py-16 md:px-8 lg:grid-cols-[1fr_1.15fr] lg:py-24">
          <div className="animate-fade-up">
            <span className="inline-flex items-center gap-2 rounded-full border border-border bg-surface px-3 py-1 text-xs font-semibold text-primary shadow-soft"><Zap className="h-3.5 w-3.5" />Multi-negocio · Multi-usuario · En la nube</span>
            <h1 className="mt-6 text-4xl font-extrabold leading-[1.05] tracking-tight md:text-6xl">Controla tu inventario. <span className="text-primary">Haz crecer tu negocio.</span></h1>
            <p className="mt-6 max-w-xl text-lg text-muted-foreground">Una plataforma inteligente y adaptable para administrar productos, existencias, ventas, préstamos y operaciones desde un solo lugar.</p>
            <div className="mt-8 flex flex-wrap gap-3">
              <Button asChild size="lg" variant="outline" className="rounded-full"><a href="#planes">Conocer planes</a></Button>
              <Button asChild size="lg" className="rounded-full"><Link to="/solicitar">Solicitar mi sistema <ArrowRight className="ml-1 h-4 w-4" /></Link></Button>
            </div>
            <div className="mt-10 flex items-center gap-6 text-sm text-muted-foreground">
              <span className="flex items-center gap-2"><ShieldCheck className="h-4 w-4 text-success" />Datos aislados por negocio</span>
              <span className="flex items-center gap-2"><Building2 className="h-4 w-4 text-primary" />5 tipos de inventario</span>
            </div>
          </div>
          <DashboardPreview />
        </div>
      </section>

      {/* PROMOS (administradas por Superadmin) */}
      <section id="funcionalidades" className="mx-auto max-w-7xl px-4 py-20 md:px-8">
        <div className="relative overflow-hidden rounded-3xl bg-brand p-8 text-primary-foreground md:p-12">
          <div className="max-w-2xl">
            <span className="rounded-full bg-primary-foreground/15 px-3 py-1 text-xs font-semibold">Temporada de regreso a clases</span>
            <h2 className="mt-4 text-3xl font-bold md:text-4xl">Papelerías: prepara tu inventario para la temporada alta</h2>
            <p className="mt-3 text-primary-foreground/80">Plantillas de catálogo escolar listas para importar y alertas de bajo stock por categoría.</p>
            <Button asChild variant="secondary" className="mt-6 rounded-full"><Link to="/solicitar">Quiero esta promoción</Link></Button>
          </div>
          <Boxes className="absolute -bottom-10 -right-6 h-64 w-64 opacity-10" strokeWidth={1} />
          <span className="absolute right-4 top-4 rounded-full bg-primary-foreground/15 px-2 py-0.5 text-[10px] uppercase tracking-wider">Contenido editable</span>
        </div>

        <div className="mt-6 grid gap-6 lg:grid-cols-[1.4fr_1fr]">
          <div className="relative overflow-hidden rounded-3xl border border-border bg-card p-8 shadow-soft">
            <div key={ad} className="animate-fade-up">
              {(() => { const A = ADS[ad]!; return (<>
                <div className="flex items-center gap-3"><span className="grid h-11 w-11 place-items-center rounded-xl bg-accent text-primary"><A.icon className="h-5 w-5" /></span><span className="rounded-full bg-warning/20 px-2.5 py-0.5 text-xs font-semibold text-warning-foreground">{A.tag}</span></div>
                <h3 className="mt-5 text-2xl font-bold">{A.title}</h3><p className="mt-2 text-muted-foreground">{A.text}</p></>); })()}
            </div>
            <div className="mt-8 flex items-center justify-between">
              <div className="flex gap-1.5">{ADS.map((_, i) => <button key={i} aria-label={`Anuncio ${i + 1}`} onClick={() => setAd(i)} className={cn("h-1.5 rounded-full transition-all", i === ad ? "w-8 bg-primary" : "w-3 bg-border")} />)}</div>
              <div className="flex gap-2">
                <button aria-label="Anterior" onClick={() => setAd((ad + ADS.length - 1) % ADS.length)} className="grid h-9 w-9 place-items-center rounded-full border border-border hover:shadow-soft"><ChevronLeft className="h-4 w-4" /></button>
                <button aria-label="Siguiente" onClick={() => setAd((ad + 1) % ADS.length)} className="grid h-9 w-9 place-items-center rounded-full border border-border hover:shadow-soft"><ChevronRight className="h-4 w-4" /></button>
              </div>
            </div>
          </div>
          <div className="flex flex-col justify-between rounded-3xl border border-warning/40 bg-warning/10 p-8">
            <div><Star className="h-6 w-6 text-warning-foreground" /><h3 className="mt-4 text-xl font-bold">Promoción especial para farmacias</h3><p className="mt-2 text-sm text-muted-foreground">Acompañamiento personalizado para configurar lotes y caducidades. Vigencia y condiciones definidas por Inventra.</p></div>
            <Button asChild variant="outline" className="mt-6 w-fit rounded-full bg-surface"><Link to="/solicitar">Solicitar información</Link></Button>
          </div>
        </div>

        <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          {[[Boxes, "Existencias en tiempo real", "Stock, mínimos y movimientos por ubicación."], [BarChart3, "Reportes accionables", "Exporta CSV y PDF según permisos."], [ShieldCheck, "Roles y permisos", "Cada encargado ve solo lo que necesita."], [Sparkles, "Tu marca, tu espacio", "Logotipo y colores propios por negocio."]].map(([I, t, d]) => {
            const Icon = I as typeof Boxes;
            return <div key={t as string} className="rounded-2xl border border-border bg-card p-6 shadow-soft transition-all hover:-translate-y-1 hover:shadow-lift"><Icon className="h-6 w-6 text-primary" strokeWidth={1.6} /><h4 className="mt-4 font-semibold">{t as string}</h4><p className="mt-1 text-sm text-muted-foreground">{d as string}</p></div>;
          })}
        </div>
      </section>

      {/* TIPOS */}
      <section id="tipos" className="border-y border-border bg-background py-20">
        <div className="mx-auto max-w-7xl px-4 md:px-8">
          <h2 className="text-3xl font-bold tracking-tight md:text-4xl">Un sistema, cinco tipos de inventario</h2>
          <p className="mt-2 text-muted-foreground">Cada negocio recibe solo los módulos que tienen sentido para su operación.</p>
          <div className="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-5">
            {(Object.keys(INV_TYPES) as InvType[]).map((k) => {
              const t = INV_TYPES[k];
              return (
                <div key={k} className="group flex flex-col rounded-2xl border border-border bg-card p-6 shadow-soft transition-all hover:-translate-y-1 hover:shadow-lift">
                  <span className="grid h-14 w-14 place-items-center rounded-2xl bg-accent text-primary transition-transform group-hover:scale-110"><t.icon className="h-7 w-7" strokeWidth={1.5} /></span>
                  <h3 className="mt-5 text-sm font-extrabold tracking-wider">{t.label.toUpperCase()}</h3>
                  <ul className="mt-3 flex-1 space-y-1.5 text-sm text-muted-foreground">{t.features.map((f) => <li key={f} className="flex gap-2"><Check className="mt-0.5 h-3.5 w-3.5 shrink-0 text-success" />{f}</li>)}</ul>
                  <Button variant="outline" size="sm" className="mt-5 rounded-full" onClick={() => setOpen(k)}>Conocer funcionalidades</Button>
                </div>
              );
            })}
          </div>
        </div>
      </section>
      <Dialog open={!!open} onOpenChange={(o) => !o && setOpen(null)}>
        <DialogContent className="max-w-lg rounded-2xl">
          {open && (<><DialogHeader><DialogTitle>{INV_TYPES[open].label}</DialogTitle><DialogDescription>{INV_TYPES[open].blurb} Módulos compatibles:</DialogDescription></DialogHeader>
            <div className="grid grid-cols-2 gap-2">{Object.values(MODULES).filter((m) => m.compat.includes(open)).map((m) => <div key={m.name} className="flex items-center gap-2 rounded-xl border border-border p-3 text-sm"><m.icon className="h-4 w-4 text-primary" />{m.name}</div>)}</div>
            <Button asChild className="rounded-full"><Link to="/solicitar">Solicitar para mi negocio</Link></Button></>)}
        </DialogContent>
      </Dialog>

      {/* PLANES */}
      <section id="planes" className="mx-auto max-w-7xl px-4 py-20 md:px-8">
        <div className="text-center">
          <h2 className="text-3xl font-bold tracking-tight md:text-4xl">Planes de suscripción</h2>
          <p className="mt-2 text-muted-foreground">Elige la periodicidad que mejor se adapte a tu negocio.</p>
          <div className="mt-8 inline-flex rounded-full border border-border bg-muted p-1">
            {(["Mensual", "Trimestral", "Anual"] as const).map((p) => <button key={p} onClick={() => setPeriod(p)} className={cn("rounded-full px-5 py-2 text-sm font-semibold uppercase tracking-wide transition-all", period === p ? "bg-surface text-foreground shadow-soft" : "text-muted-foreground")}>{p}</button>)}
          </div>
        </div>
        <div className="mt-10 grid gap-6 md:grid-cols-3">
          {(["Mensual", "Trimestral", "Anual"] as const).map((p) => {
            const rec = p === "Anual"; const sel = p === period;
            return (
              <div key={p} className={cn("relative flex flex-col rounded-3xl border bg-card p-8 transition-all", rec ? "border-primary shadow-lift" : "border-border shadow-soft", sel && "ring-2 ring-primary/30")}>
                {rec && <span className="absolute -top-3 left-8 rounded-full bg-primary px-3 py-1 text-xs font-bold text-primary-foreground">Recomendado</span>}
                <h3 className="text-lg font-bold">Plan {p}</h3>
                <div className="mt-4 flex items-baseline gap-2"><span className="text-4xl font-extrabold">$ —</span><span className="text-muted-foreground">MXN / {p === "Mensual" ? "mes" : p === "Trimestral" ? "trimestre" : "año"}</span></div>
                <p className="mt-1 text-xs text-muted-foreground">Precio configurable por el Superadministrador</p>
                <ul className="mt-6 space-y-2 text-sm">{["Usuarios y roles ilimitados por tenant", "Logotipo y colores propios", "Reportes y exportaciones", p === "Anual" ? "Renovación con un solo pago al año" : p === "Trimestral" ? "Renovación cada 3 meses" : "Renovación mes a mes"].map((b) => <li key={b} className="flex gap-2"><Check className="h-4 w-4 text-success" />{b}</li>)}</ul>
                <div className="mt-6 rounded-xl bg-muted p-4 text-xs text-muted-foreground"><b className="text-foreground">Módulos:</b> según el tipo de inventario contratado (biblioteca, abarrotes, papelería, farmacia o electrónicos).</div>
                <p className="mt-4 flex-1 text-xs text-muted-foreground">Condiciones: activación sujeta a revisión de la solicitud. Vigencia a partir de la creación del espacio.</p>
                <Button asChild className="mt-6 rounded-full" variant={rec ? "default" : "outline"}><Link to="/solicitar" search={{ plan: p }}>Solicitar plan</Link></Button>
              </div>
            );
          })}
        </div>
      </section>

      <section className="mx-auto max-w-7xl px-4 pb-20 md:px-8">
        <div className="flex flex-col items-center justify-between gap-6 rounded-3xl border border-border bg-background p-10 md:flex-row">
          <div><h2 className="text-2xl font-bold">¿Listo para ordenar tu inventario?</h2><p className="text-muted-foreground">Envía tu solicitud y nuestro equipo la revisará.</p></div>
          <Button asChild size="lg" className="rounded-full"><Link to="/solicitar">Solicitar mi sistema</Link></Button>
        </div>
      </section>
      <footer className="border-t border-border py-8 text-center text-sm text-muted-foreground">© 2026 Inventra · Aviso de privacidad · Términos</footer>
    </div>
  );
}

function DashboardPreview() {
  const bars = [42, 58, 50, 72, 66, 88, 80];
  return (
    <div className="relative animate-fade-up">
      <div className="rounded-3xl border border-border bg-surface p-3 shadow-lift">
        <div className="rounded-2xl border border-border bg-background">
          <div className="flex items-center justify-between border-b border-border bg-surface px-4 py-3 rounded-t-2xl">
            <div className="flex items-center gap-2 text-sm font-bold text-primary"><span className="h-5 w-5 rounded-md bg-primary" />Farmacia San Rafael</div>
            <div className="flex gap-4 text-xs text-muted-foreground"><span className="border-b-2 border-foreground pb-1 font-semibold text-foreground">Mi tenant</span><span>Catálogo</span><span>Reportes</span></div>
          </div>
          <div className="grid grid-cols-3 gap-3 p-4">
            {[["Existencias", "4,812"], ["Ventas hoy", "$18,240"], ["Por caducar", "23"]].map(([l, v]) => <div key={l} className="rounded-xl border border-border bg-card p-3"><div className="text-[11px] text-muted-foreground">{l}</div><div className="mt-1 text-lg font-bold">{v}</div></div>)}
            <div className="col-span-2 rounded-xl border border-border bg-card p-3">
              <div className="text-[11px] text-muted-foreground">Ventas de la semana</div>
              <div className="mt-3 flex h-28 items-end gap-2">{bars.map((b, i) => <div key={i} className="flex-1 rounded-t-md bg-primary/80" style={{ height: `${b}%` }} />)}</div>
            </div>
            <div className="rounded-xl border border-border bg-card p-3 text-[11px]">
              <div className="text-muted-foreground">Alertas</div>
              {["Omeprazol · 4 pzas", "Amoxicilina lote AM88", "Paracetamol cad. nov"].map((a) => <div key={a} className="mt-2 flex items-center gap-1.5"><span className="h-1.5 w-1.5 rounded-full bg-warning" />{a}</div>)}
            </div>
          </div>
        </div>
      </div>
      <div className="absolute -bottom-6 -left-6 hidden rounded-2xl border border-border bg-surface p-4 shadow-lift md:block">
        <div className="text-xs text-muted-foreground">Venta confirmada</div><div className="font-bold text-success">+ $1,240.00</div>
      </div>
    </div>
  );
}
