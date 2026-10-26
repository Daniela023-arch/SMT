import { createContext, useContext, useEffect, useState, type ReactNode } from "react";
import { TENANTS, type Tenant } from "./data";

export type Role = "super" | "admin" | "ventas" | "prestamos" | "almacen";
export type Session = { email: string; name: string; role: Role; tenantId?: string | undefined; firstLogin?: boolean | undefined };

type Ctx = {
  session: Session | null; ready: boolean;
  signIn: (s: Session) => void; signOut: () => void; update: (p: Partial<Session>) => void;
  tenants: Tenant[]; updateTenant: (id: string, p: Partial<Tenant>) => void;
};
const C = createContext<Ctx | null>(null);
const KEY = "inventra-session";

export function SessionProvider({ children }: { children: ReactNode }) {
  const [session, setSession] = useState<Session | null>(null);
  const [ready, setReady] = useState(false);
  const [tenants, setTenants] = useState<Tenant[]>(TENANTS);
  useEffect(() => { try { const saved = localStorage.getItem("inventra-tenants"); if (saved) setTenants(JSON.parse(saved)); } catch {} }, []);
  useEffect(() => { if (ready) localStorage.setItem("inventra-tenants", JSON.stringify(tenants)); }, [tenants, ready]);
  useEffect(() => {
    try { const raw = localStorage.getItem(KEY); if (raw) setSession(JSON.parse(raw)); } catch { /* noop */ }
    setReady(true);
  }, []);
  const persist = (s: Session | null) => { setSession(s); if (s) localStorage.setItem(KEY, JSON.stringify(s)); else localStorage.removeItem(KEY); };
  return (
    <C.Provider value={{
      session, ready, tenants,
      signIn: persist, signOut: () => persist(null),
      update: (p) => session && persist({ ...session, ...p }),
      updateTenant: (id, p) => setTenants((ts) => ts.map((t) => (t.id === id ? { ...t, ...p } : t))),
    }}>{children}</C.Provider>
  );
}
export function useSession() { const c = useContext(C); if (!c) throw new Error("SessionProvider missing"); return c; }
export function homeFor(s: Session) {
  if (s.role === "super") return "/superadmin";
  if (s.role === "admin") return s.firstLogin ? "/onboarding" : "/admin";
  return ("/" + s.role) as "/ventas" | "/prestamos" | "/almacen";
}
