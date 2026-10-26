import { useEffect, useRef, useState, type ReactNode } from "react";
import { Link, useNavigate } from "@tanstack/react-router";
import { Bell, ChevronLeft, ChevronRight, LogOut, Menu, Search, User, type LucideIcon } from "lucide-react";
import { cn } from "@/lib/utils";
import { useSession, type Role } from "@/lib/session";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";

export function Logo({ className, mark }: { className?: string; mark?: string | undefined }) {
  return (
    <Link to="/" className={cn("flex items-center gap-2 font-extrabold tracking-tight text-primary", className)}>
      {mark ? (
        <img src={mark} alt="" className="h-8 w-8 rounded-lg object-contain" />
      ) : (
        <span className="grid h-8 w-8 place-items-center rounded-lg bg-primary text-primary-foreground">
          <svg viewBox="0 0 24 24" className="h-4 w-4" fill="none" stroke="currentColor" strokeWidth="2.4"><path d="M4 8l8-4 8 4-8 4-8-4z" /><path d="M4 12l8 4 8-4" /><path d="M4 16l8 4 8-4" /></svg>
        </span>
      )}
      <span className="text-xl">Inventra</span>
    </Link>
  );
}

export type NavItem = { id: string; label: string; icon: LucideIcon };

const ROLE_LABEL: Record<Role, string> = { super: "Superadministrador", admin: "Administrador", ventas: "Encargado de ventas", prestamos: "Encargado de préstamos", almacen: "Encargado de almacén" };

/** Airbnb-style horizontal navigation: top bar (logo · main nav · actions) + icon category bar with underline. */
export function Shell({
  main, mainActive, onMain, cats, catActive, onCat, context, children, logo,
}: {
  main?: { id: string; label: string }[]; mainActive?: string; onMain?: (id: string) => void;
  cats: NavItem[]; catActive: string; onCat: (id: string) => void;
  context?: string | undefined; children: ReactNode; logo?: string | undefined;
}) {
  const { session, signOut } = useSession();
  const nav = useNavigate();
  const scroller = useRef<HTMLDivElement>(null);
  const [edges, setEdges] = useState({ l: false, r: false });
  const check = () => { const el = scroller.current; if (!el) return; setEdges({ l: el.scrollLeft > 4, r: el.scrollLeft + el.clientWidth < el.scrollWidth - 4 }); };
  useEffect(() => { check(); window.addEventListener("resize", check); return () => window.removeEventListener("resize", check); }, [cats]);
  const scroll = (d: number) => scroller.current?.scrollBy({ left: d * 280, behavior: "smooth" });
  const logout = () => { signOut(); nav({ to: "/login", replace: true }); };

  return (
    <div className="min-h-screen bg-background">
      <header className="sticky top-0 z-40 border-b border-border bg-surface/95 backdrop-blur">
        <div className="mx-auto flex h-[72px] max-w-[1440px] items-center justify-between gap-4 px-4 md:px-10">
          <Logo mark={logo} />
          {main && main.length > 1 ? (
            <nav className="hidden items-center gap-1 lg:flex">
              {main.map((m) => (
                <button key={m.id} onClick={() => onMain?.(m.id)}
                  className={cn("rounded-full px-4 py-2 text-[15px] font-medium transition-colors", mainActive === m.id ? "text-foreground" : "text-muted-foreground hover:bg-muted hover:text-foreground")}>
                  <span className={cn("pb-1", mainActive === m.id && "border-b-2 border-foreground")}>{m.label}</span>
                </button>
              ))}
            </nav>
          ) : (
            <div className="hidden w-[380px] items-center gap-3 rounded-full border border-border bg-surface py-2 pl-5 pr-2 shadow-soft lg:flex">
              <span className="flex-1 truncate text-sm font-medium">{context ?? "Inventra"}</span>
              <span className="grid h-8 w-8 place-items-center rounded-full bg-primary text-primary-foreground"><Search className="h-4 w-4" /></span>
            </div>
          )}
          <div className="flex items-center gap-2">
            {context && main && main.length > 1 && <span className="hidden max-w-[220px] truncate rounded-full px-3 py-2 text-sm font-medium text-muted-foreground xl:block">{context}</span>}
            <button aria-label="Notificaciones" className="relative grid h-10 w-10 place-items-center rounded-full hover:bg-muted">
              <Bell className="h-[18px] w-[18px]" /><span className="absolute right-2 top-2 h-2 w-2 rounded-full bg-destructive" />
            </button>
            <DropdownMenu>
              <DropdownMenuTrigger className="flex items-center gap-2 rounded-full border border-border py-1.5 pl-3 pr-1.5 transition-shadow hover:shadow-soft">
                <Menu className="h-4 w-4" />
                <span className="grid h-8 w-8 place-items-center rounded-full bg-primary text-xs font-bold text-primary-foreground">{session?.name.slice(0, 1) ?? <User className="h-4 w-4" />}</span>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end" className="w-64 rounded-xl">
                <DropdownMenuLabel>
                  <div className="font-semibold">{session?.name}</div>
                  <div className="text-xs font-normal text-muted-foreground">{session && ROLE_LABEL[session.role]}</div>
                </DropdownMenuLabel>
                {main && main.length > 1 && (<><DropdownMenuSeparator className="lg:hidden" />
                  {main.map((m) => <DropdownMenuItem key={m.id} className="lg:hidden" onClick={() => onMain?.(m.id)}>{m.label}</DropdownMenuItem>)}</>)}
                <DropdownMenuSeparator />
                <DropdownMenuItem onClick={logout}><LogOut className="mr-2 h-4 w-4" />Cerrar sesión</DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </div>
        <div className="relative mx-auto max-w-[1440px] px-4 md:px-10">
          {edges.l && <ArrowBtn side="l" onClick={() => scroll(-1)} />}
          <div ref={scroller} onScroll={check} className="no-scrollbar flex justify-start gap-8 overflow-x-auto md:justify-center md:gap-10">
            {cats.map((c) => {
              const on = c.id === catActive;
              return (
                <button key={c.id} onClick={() => onCat(c.id)}
                  className={cn("group relative flex shrink-0 flex-col items-center gap-1.5 pb-3 pt-2 text-xs font-semibold transition-colors", on ? "text-foreground" : "text-muted-foreground hover:text-foreground")}>
                  <c.icon className={cn("h-6 w-6 transition-transform duration-300", on ? "scale-110 text-primary" : "group-hover:-translate-y-0.5")} strokeWidth={1.6} />
                  <span className="whitespace-nowrap">{c.label}</span>
                  <span className={cn("absolute inset-x-0 bottom-0 h-0.5 origin-center rounded-full transition-all duration-300", on ? "scale-x-100 bg-foreground" : "scale-x-0 bg-border group-hover:scale-x-100")} />
                </button>
              );
            })}
          </div>
          {edges.r && <ArrowBtn side="r" onClick={() => scroll(1)} />}
        </div>
      </header>
      <main key={catActive} className="mx-auto max-w-[1440px] animate-fade-up px-4 py-8 md:px-10">{children}</main>
    </div>
  );
}

function ArrowBtn({ side, onClick }: { side: "l" | "r"; onClick: () => void }) {
  const I = side === "l" ? ChevronLeft : ChevronRight;
  return (
    <div className={cn("absolute top-0 z-10 flex h-full items-center", side === "l" ? "left-0 bg-surface pl-2 pr-6" : "right-0 bg-surface pl-6 pr-2")}>
      <button onClick={onClick} aria-label={side === "l" ? "Anterior" : "Siguiente"} className="grid h-8 w-8 place-items-center rounded-full border border-border bg-surface shadow-soft hover:scale-105"><I className="h-4 w-4" /></button>
    </div>
  );
}

export function PageHeader({ title, subtitle, actions }: { title: string; subtitle?: string; actions?: ReactNode }) {
  return (
    <div className="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-end">
      <div><h1 className="text-2xl font-bold tracking-tight md:text-3xl">{title}</h1>{subtitle && <p className="mt-1 text-muted-foreground">{subtitle}</p>}</div>
      {actions && <div className="flex flex-wrap gap-2">{actions}</div>}
    </div>
  );
}

export function Panel({ title, action, children, className }: { title?: string; action?: ReactNode; children: ReactNode; className?: string }) {
  return (
    <section className={cn("rounded-2xl border border-border bg-card p-5 shadow-soft", className)}>
      {title && <div className="mb-4 flex items-center justify-between"><h3 className="font-semibold">{title}</h3>{action}</div>}
      {children}
    </section>
  );
}

export function Kpi({ label, value, hint, icon: I, tone = "default" }: { label: string; value: string; hint?: string; icon: LucideIcon; tone?: "default" | "success" | "warning" | "danger" }) {
  const t = { default: "bg-accent text-primary", success: "bg-success/10 text-success", warning: "bg-warning/20 text-warning-foreground", danger: "bg-destructive/10 text-destructive" }[tone];
  return (
    <div className="rounded-2xl border border-border bg-card p-5 shadow-soft transition-shadow hover:shadow-lift">
      <div className="flex items-center justify-between"><span className="text-sm text-muted-foreground">{label}</span><span className={cn("grid h-9 w-9 place-items-center rounded-xl", t)}><I className="h-[18px] w-[18px]" /></span></div>
      <div className="mt-3 text-2xl font-bold tracking-tight">{value}</div>
      {hint && <div className="mt-1 text-xs text-muted-foreground">{hint}</div>}
    </div>
  );
}

export function Status({ s }: { s: string }) {
  const map: Record<string, string> = {
    Activa: "bg-success/10 text-success", Activo: "bg-success/10 text-success", Aprobada: "bg-success/10 text-success", Disponible: "bg-success/10 text-success", Devuelto: "bg-success/10 text-success",
    Pendiente: "bg-warning/20 text-warning-foreground", "Por vencer": "bg-warning/20 text-warning-foreground", "En revisión": "bg-accent text-primary", Invitado: "bg-accent text-primary", Prestado: "bg-accent text-primary", "Bajo stock": "bg-warning/20 text-warning-foreground",
    Rechazada: "bg-destructive/10 text-destructive", Suspendida: "bg-destructive/10 text-destructive", Bloqueado: "bg-destructive/10 text-destructive", Vencido: "bg-destructive/10 text-destructive", Agotado: "bg-destructive/10 text-destructive",
  };
  return <span className={cn("inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold", map[s] ?? "bg-muted text-muted-foreground")}>{s}</span>;
}

export function Empty({ icon: I, title, text }: { icon: LucideIcon; title: string; text: string }) {
  return <div className="flex flex-col items-center rounded-2xl border border-dashed border-border py-14 text-center"><I className="h-8 w-8 text-muted-foreground" strokeWidth={1.5} /><div className="mt-3 font-semibold">{title}</div><p className="mt-1 max-w-sm text-sm text-muted-foreground">{text}</p></div>;
}

/** Client guard for the prototype: redirects to /login when the role doesn't match. */
export function useGuard(roles: Role[]) {
  const { session, ready } = useSession();
  const nav = useNavigate();
  useEffect(() => { if (ready && (!session || !roles.includes(session.role))) nav({ to: "/login", replace: true }); }, [ready, session]); // eslint-disable-line
  return ready && session && roles.includes(session.role) ? session : null;
}
