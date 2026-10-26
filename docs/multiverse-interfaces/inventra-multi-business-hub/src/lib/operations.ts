import { ITEMS, type InvType, type Item } from './data';
export type Movement = { id: string; tenantId: string; kind: string; sku: string; quantity: number; date: string; detail?: string };
const read = <T,>(key: string, fallback: T): T => { if (typeof window === 'undefined') return fallback; try { return JSON.parse(localStorage.getItem(key) || '') as T; } catch { return fallback; } };
export function getItems(tenantId: string, type: InvType): Item[] { return read(`inventra-items-${tenantId}`, ITEMS[type]); }
export function saveItems(tenantId: string, items: Item[]) { localStorage.setItem(`inventra-items-${tenantId}`, JSON.stringify(items)); window.dispatchEvent(new Event('inventra-update')); }
export function getMovements(tenantId: string): Movement[] { return read(`inventra-movements-${tenantId}`, []); }
export function addMovement(m: Omit<Movement, 'id' | 'date'>) { const list = getMovements(m.tenantId); localStorage.setItem(`inventra-movements-${m.tenantId}`, JSON.stringify([{...m, id: crypto.randomUUID(), date: new Date().toLocaleString('es-MX')}, ...list])); window.dispatchEvent(new Event('inventra-update')); }
export type LocalUser = { id: string; name: string; email: string; role: string; active: boolean };
export function getUsers(id: string): LocalUser[] { return read(`inventra-users-${id}`, []); }
export function saveUsers(id: string, users: LocalUser[]) { localStorage.setItem(`inventra-users-${id}`, JSON.stringify(users)); window.dispatchEvent(new Event('inventra-update')); }
