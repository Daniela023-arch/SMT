import { createFileRoute, Link } from "@tanstack/react-router";
import { useState } from "react";
import { z } from "zod";
import { CheckCircle2, Clock } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";
import { Checkbox } from "@/components/ui/checkbox";
import { INV_TYPES, type InvType } from "@/lib/data";
import { PublicHeader } from "./index";
import { cn } from "@/lib/utils";

export const Route = createFileRoute("/solicitar")({
  validateSearch: (s: Record<string, unknown>): { plan?: string } => (typeof s["plan"] === "string" ? { plan: s["plan"] } : {}),
  head: () => ({ meta: [
    { title: "Solicitar sistema — Inventra" },
    { name: "description", content: "Solicita la contratación de Inventra para tu negocio. Revisaremos tu solicitud y te contactaremos." },
    { property: "og:title", content: "Solicitar sistema — Inventra" },
    { property: "og:description", content: "Formulario de solicitud para nuevos negocios en Inventra." },
  ] }),
  component: Solicitar,
});

const schema = z.object({
  negocio: z.string().trim().min(2, "Ingresa el nombre del negocio").max(120),
  responsable: z.string().trim().min(2, "Ingresa el nombre del responsable").max(100),
  email: z.string().trim().email("Correo no válido").max(255),
  telefono: z.string().trim().regex(/^[0-9 +()-]{8,20}$/, "Teléfono no válido"),
  tipo: z.string().min(1, "Selecciona un tipo de inventario"),
  plan: z.enum(["Mensual", "Trimestral", "Anual"], { message: "Selecciona un plan" }),
  descripcion: z.string().trim().min(10, "Describe brevemente tu negocio").max(1000),
  comentarios: z.string().max(1000).optional(),
  privacidad: z.literal(true, { message: "Debes aceptar el aviso de privacidad" }),
});

function Solicitar() {
  const { plan } = Route.useSearch();
  const [f, setF] = useState<Record<string, string | boolean>>({ plan: plan ?? "", tipo: "", privacidad: false });
  const [err, setErr] = useState<Record<string, string>>({});
  const [sending, setSending] = useState(false);
  const [folio, setFolio] = useState<string | null>(null);
  const set = (k: string, v: string | boolean) => setF((p) => ({ ...p, [k]: v }));

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    const r = schema.safeParse(f);
    if (!r.success) { setErr(Object.fromEntries(r.error.issues.map((i) => [i.path[0] as string, i.message]))); return; }
    setErr({}); setSending(true);
    setTimeout(() => { setSending(false); setFolio(`SOL-2026-0${149 + Math.floor(Math.random() * 40)}`); }, 900);
  };

  const field = (k: string, label: string, el: React.ReactNode) => (
    <div className="space-y-1.5"><Label htmlFor={k}>{label}</Label>{el}{err[k] && <p className="text-xs text-destructive">{err[k]}</p>}</div>
  );

  return (
    <div className="min-h-screen bg-background">
      <PublicHeader />
      <div className="mx-auto max-w-3xl px-4 py-12">
        {folio ? (
          <div className="animate-fade-up rounded-3xl border border-border bg-card p-10 text-center shadow-lift">
            <CheckCircle2 className="mx-auto h-14 w-14 text-success" strokeWidth={1.5} />
            <h1 className="mt-4 text-2xl font-bold">¡Solicitud enviada!</h1>
            <p className="mt-2 text-muted-foreground">Nuestro equipo revisará la información y te contactará por correo.</p>
            <div className="mx-auto mt-6 grid max-w-sm gap-3 rounded-2xl bg-muted p-5 text-left text-sm">
              <div className="flex justify-between"><span className="text-muted-foreground">Número de solicitud</span><b>{folio}</b></div>
              <div className="flex justify-between"><span className="text-muted-foreground">Estado</span><span className="flex items-center gap-1 font-semibold text-warning-foreground"><Clock className="h-4 w-4" />Pendiente de revisión</span></div>
            </div>
            <p className="mt-4 text-xs text-muted-foreground">Tu espacio no se activa automáticamente: se crea tras la aprobación del Superadministrador.</p>
            <Button asChild className="mt-6 rounded-full"><Link to="/">Volver al inicio</Link></Button>
          </div>
        ) : (
          <form onSubmit={submit} noValidate className="rounded-3xl border border-border bg-card p-6 shadow-soft md:p-10">
            <h1 className="text-3xl font-bold tracking-tight">Solicitar mi sistema</h1>
            <p className="mt-1 text-muted-foreground">Completa los datos y revisaremos tu solicitud.</p>
            <div className="mt-8 grid gap-5 md:grid-cols-2">
              {field("negocio", "Nombre del negocio", <Input id="negocio" onChange={(e) => set("negocio", e.target.value)} placeholder="Farmacia Vida Sana" />)}
              {field("responsable", "Nombre del responsable", <Input id="responsable" onChange={(e) => set("responsable", e.target.value)} placeholder="Luis Márquez" />)}
              {field("email", "Correo electrónico", <Input id="email" type="email" onChange={(e) => set("email", e.target.value)} placeholder="contacto@negocio.mx" />)}
              {field("telefono", "Teléfono", <Input id="telefono" onChange={(e) => set("telefono", e.target.value)} placeholder="55 1234 5678" />)}
            </div>
            <div className="mt-6">{field("tipo", "Tipo de inventario", (
              <div className="grid grid-cols-2 gap-2 sm:grid-cols-5">{(Object.keys(INV_TYPES) as InvType[]).map((k) => { const T = INV_TYPES[k]; return (
                <button type="button" key={k} onClick={() => set("tipo", k)} className={cn("flex flex-col items-center gap-2 rounded-xl border p-3 text-xs font-semibold transition-all", f["tipo"] === k ? "border-primary bg-accent text-primary" : "border-border hover:border-foreground/30")}><T.icon className="h-5 w-5" strokeWidth={1.6} />{T.label}</button>); })}</div>))}</div>
            <div className="mt-6">{field("plan", "Plan solicitado", (
              <div className="grid grid-cols-3 gap-2">{["Mensual", "Trimestral", "Anual"].map((p) => <button type="button" key={p} onClick={() => set("plan", p)} className={cn("rounded-xl border p-3 text-sm font-semibold", f["plan"] === p ? "border-primary bg-accent text-primary" : "border-border")}>{p}</button>)}</div>))}</div>
            <div className="mt-6 grid gap-5">
              {field("descripcion", "Descripción del negocio", <Textarea id="descripcion" onChange={(e) => set("descripcion", e.target.value)} placeholder="Giro, número de sucursales, volumen aproximado de productos..." />)}
              {field("comentarios", "Comentarios adicionales (opcional)", <Textarea id="comentarios" onChange={(e) => set("comentarios", e.target.value)} />)}
              <div><label className="flex items-start gap-3 text-sm"><Checkbox checked={!!f["privacidad"]} onCheckedChange={(v) => set("privacidad", v === true)} className="mt-0.5" />Acepto el aviso de privacidad y el tratamiento de mis datos para atender esta solicitud.</label>{err.privacidad && <p className="mt-1 text-xs text-destructive">{err.privacidad}</p>}</div>
            </div>
            <Button type="submit" size="lg" disabled={sending} className="mt-8 w-full rounded-full">{sending ? "Enviando…" : "Enviar solicitud"}</Button>
          </form>
        )}
      </div>
    </div>
  );
}
