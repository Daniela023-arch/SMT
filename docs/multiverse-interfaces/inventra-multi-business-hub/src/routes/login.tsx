import { createFileRoute, Link, useNavigate } from "@tanstack/react-router";
import { useState } from "react";
import { AlertCircle, ArrowLeft, Eye, EyeOff, Loader2, Lock } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Checkbox } from "@/components/ui/checkbox";
import { Logo } from "@/components/inventra/Shell";
import { DEMO_ACCOUNTS, DEMO_PASSWORD } from "@/lib/data";
import { homeFor, useSession } from "@/lib/session";
import { toast } from "sonner";

export const Route = createFileRoute("/login")({
  head: () => ({ meta: [
    { title: "Iniciar sesión — Inventra" },
    { name: "description", content: "Accede a tu espacio de trabajo en Inventra." },
    { property: "og:title", content: "Iniciar sesión — Inventra" },
    { property: "og:description", content: "Acceso seguro para administradores y encargados." },
  ] }),
  component: Login,
});

function Login() {
  const { signIn } = useSession();
  const nav = useNavigate();
  const [email, setEmail] = useState("");
  const [pw, setPw] = useState("");
  const [show, setShow] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<{ kind: "pw" | "suspended" | "empty"; msg: string } | null>(null);

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!email || !pw) return setError({ kind: "empty", msg: "Ingresa tu correo y contraseña." });
    setLoading(true); setError(null);
    setTimeout(() => {
      setLoading(false);
      const acc = DEMO_ACCOUNTS.find((a) => a.email === email.trim().toLowerCase());
      if (!acc || pw !== DEMO_PASSWORD) return setError({ kind: "pw", msg: "Correo o contraseña incorrectos. Verifica tus datos." });
      if (acc.role === "suspended") return setError({ kind: "suspended", msg: "Tu cuenta está suspendida. Contacta al administrador de tu negocio." });
      const s = { email: acc.email, name: acc.label.replace(/^Admin · /, "Admin "), role: acc.role, tenantId: "tenant" in acc ? acc.tenant : undefined, firstLogin: "firstLogin" in acc ? (acc.firstLogin && localStorage.getItem(`inventra-setup-${acc.email}`) !== "done") : undefined };
      signIn(s); toast.success("Bienvenido de nuevo");
      nav({ to: homeFor(s) as "/superadmin" });
    }, 800);
  };

  return (
    <div className="grid min-h-screen bg-background lg:grid-cols-2">
      <div className="flex flex-col px-6 py-8 md:px-16">
        <div className="flex items-center justify-between"><Logo /><Link to="/" className="flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft className="h-4 w-4" />Sitio público</Link></div>
        <form onSubmit={submit} className="mx-auto my-auto w-full max-w-sm py-10">
          <h1 className="text-3xl font-bold tracking-tight">Inicia sesión</h1>
          <p className="mt-1 text-muted-foreground">Accede a tu espacio de trabajo.</p>
          {error && (
            <div role="alert" className={`mt-6 flex gap-3 rounded-xl border p-3 text-sm ${error.kind === "suspended" ? "border-warning/50 bg-warning/10 text-warning-foreground" : "border-destructive/30 bg-destructive/5 text-destructive"}`}>
              {error.kind === "suspended" ? <Lock className="h-4 w-4 shrink-0" /> : <AlertCircle className="h-4 w-4 shrink-0" />}{error.msg}
            </div>
          )}
          <div className="mt-6 space-y-4">
            <div className="space-y-1.5"><Label htmlFor="email">Correo electrónico</Label><Input id="email" type="email" autoComplete="email" value={email} onChange={(e) => setEmail(e.target.value)} className="h-11" aria-invalid={error?.kind === "pw"} /></div>
            <div className="space-y-1.5"><Label htmlFor="pw">Contraseña</Label>
              <div className="relative"><Input id="pw" type={show ? "text" : "password"} autoComplete="current-password" value={pw} onChange={(e) => setPw(e.target.value)} className="h-11 pr-10" aria-invalid={error?.kind === "pw"} />
                <button type="button" aria-label={show ? "Ocultar contraseña" : "Mostrar contraseña"} onClick={() => setShow(!show)} className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground">{show ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}</button></div>
            </div>
            <div className="flex items-center justify-between text-sm"><label className="flex items-center gap-2"><Checkbox />Recordarme</label><button type="button" onClick={() => toast.info("Te enviamos un enlace de recuperación si el correo existe.")} className="font-semibold text-primary hover:underline">Recuperar contraseña</button></div>
            <Button type="submit" size="lg" disabled={loading} className="w-full rounded-xl">{loading ? <><Loader2 className="mr-2 h-4 w-4 animate-spin" />Verificando…</> : "Iniciar sesión"}</Button>
          </div>
        </form>
      </div>
      <div className="hidden border-l border-border bg-hero p-12 lg:flex lg:flex-col lg:justify-center">
        <div className="rounded-3xl border border-border bg-surface/80 p-6 shadow-lift backdrop-blur">
          <div className="text-sm font-semibold">Cuentas de demostración</div>
          <p className="text-xs text-muted-foreground">Contraseña para todas: <code className="rounded bg-muted px-1.5 py-0.5 font-semibold">{DEMO_PASSWORD}</code></p>
          <div className="mt-4 grid gap-2">
            {DEMO_ACCOUNTS.map((a) => (
              <button key={a.email} type="button" onClick={() => { setEmail(a.email); setPw(DEMO_PASSWORD); setError(null); }} className="flex items-center justify-between rounded-xl border border-border bg-surface px-3 py-2 text-left text-sm transition-all hover:border-primary hover:shadow-soft">
                <span className="font-medium">{a.label}</span><span className="text-xs text-muted-foreground">{a.email}</span>
              </button>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}
