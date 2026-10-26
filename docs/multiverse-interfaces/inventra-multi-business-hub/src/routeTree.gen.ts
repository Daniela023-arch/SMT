/* eslint-disable */
// @ts-nocheck
// Generated route manifest for the offline handoff; TanStack Router regenerates this file during development.
import { Route as rootRouteImport } from './routes/__root'
import { Route as IndexRouteImport } from './routes/index'
import { Route as LoginRouteImport } from './routes/login'
import { Route as SolicitarRouteImport } from './routes/solicitar'
import { Route as SuperadminRouteImport } from './routes/superadmin'
import { Route as AdminRouteImport } from './routes/admin'
import { Route as OnboardingRouteImport } from './routes/onboarding'
import { Route as VentasRouteImport } from './routes/ventas'
import { Route as PrestamosRouteImport } from './routes/prestamos'
import { Route as AlmacenRouteImport } from './routes/almacen'
const IndexRoute = IndexRouteImport.update({id:'/',path:'/',getParentRoute:()=>rootRouteImport} as any)
const LoginRoute = LoginRouteImport.update({id:'/login',path:'/login',getParentRoute:()=>rootRouteImport} as any)
const SolicitarRoute = SolicitarRouteImport.update({id:'/solicitar',path:'/solicitar',getParentRoute:()=>rootRouteImport} as any)
const SuperadminRoute = SuperadminRouteImport.update({id:'/superadmin',path:'/superadmin',getParentRoute:()=>rootRouteImport} as any)
const AdminRoute = AdminRouteImport.update({id:'/admin',path:'/admin',getParentRoute:()=>rootRouteImport} as any)
const OnboardingRoute = OnboardingRouteImport.update({id:'/onboarding',path:'/onboarding',getParentRoute:()=>rootRouteImport} as any)
const VentasRoute = VentasRouteImport.update({id:'/ventas',path:'/ventas',getParentRoute:()=>rootRouteImport} as any)
const PrestamosRoute = PrestamosRouteImport.update({id:'/prestamos',path:'/prestamos',getParentRoute:()=>rootRouteImport} as any)
const AlmacenRoute = AlmacenRouteImport.update({id:'/almacen',path:'/almacen',getParentRoute:()=>rootRouteImport} as any)
export interface FileRoutesByFullPath {
  '/': typeof IndexRoute
  '/login': typeof LoginRoute
  '/solicitar': typeof SolicitarRoute
  '/superadmin': typeof SuperadminRoute
  '/admin': typeof AdminRoute
  '/onboarding': typeof OnboardingRoute
  '/ventas': typeof VentasRoute
  '/prestamos': typeof PrestamosRoute
  '/almacen': typeof AlmacenRoute
}
export interface FileRoutesByTo extends FileRoutesByFullPath {}
export interface FileRoutesById {
  __root__: typeof rootRouteImport
  '/': typeof IndexRoute
  '/login': typeof LoginRoute
  '/solicitar': typeof SolicitarRoute
  '/superadmin': typeof SuperadminRoute
  '/admin': typeof AdminRoute
  '/onboarding': typeof OnboardingRoute
  '/ventas': typeof VentasRoute
  '/prestamos': typeof PrestamosRoute
  '/almacen': typeof AlmacenRoute
}
export interface FileRouteTypes {
  fileRoutesByFullPath: FileRoutesByFullPath
  fullPaths: '/' | '/login' | '/solicitar' | '/superadmin' | '/admin' | '/onboarding' | '/ventas' | '/prestamos' | '/almacen'
  fileRoutesByTo: FileRoutesByTo
  to: '/' | '/login' | '/solicitar' | '/superadmin' | '/admin' | '/onboarding' | '/ventas' | '/prestamos' | '/almacen'
  id: '__root__' | '/' | '/login' | '/solicitar' | '/superadmin' | '/admin' | '/onboarding' | '/ventas' | '/prestamos' | '/almacen'
  fileRoutesById: FileRoutesById
}
export interface RootRouteChildren {
  IndexRoute: typeof IndexRoute
  LoginRoute: typeof LoginRoute
  SolicitarRoute: typeof SolicitarRoute
  SuperadminRoute: typeof SuperadminRoute
  AdminRoute: typeof AdminRoute
  OnboardingRoute: typeof OnboardingRoute
  VentasRoute: typeof VentasRoute
  PrestamosRoute: typeof PrestamosRoute
  AlmacenRoute: typeof AlmacenRoute
}
declare module '@tanstack/react-router' {
  interface FileRoutesByPath {
    '/': {id:'/';path:'/';fullPath:'/';preLoaderRoute:typeof IndexRouteImport;parentRoute:typeof rootRouteImport}
    '/login': {id:'/login';path:'/login';fullPath:'/login';preLoaderRoute:typeof LoginRouteImport;parentRoute:typeof rootRouteImport}
    '/solicitar': {id:'/solicitar';path:'/solicitar';fullPath:'/solicitar';preLoaderRoute:typeof SolicitarRouteImport;parentRoute:typeof rootRouteImport}
    '/superadmin': {id:'/superadmin';path:'/superadmin';fullPath:'/superadmin';preLoaderRoute:typeof SuperadminRouteImport;parentRoute:typeof rootRouteImport}
    '/admin': {id:'/admin';path:'/admin';fullPath:'/admin';preLoaderRoute:typeof AdminRouteImport;parentRoute:typeof rootRouteImport}
    '/onboarding': {id:'/onboarding';path:'/onboarding';fullPath:'/onboarding';preLoaderRoute:typeof OnboardingRouteImport;parentRoute:typeof rootRouteImport}
    '/ventas': {id:'/ventas';path:'/ventas';fullPath:'/ventas';preLoaderRoute:typeof VentasRouteImport;parentRoute:typeof rootRouteImport}
    '/prestamos': {id:'/prestamos';path:'/prestamos';fullPath:'/prestamos';preLoaderRoute:typeof PrestamosRouteImport;parentRoute:typeof rootRouteImport}
    '/almacen': {id:'/almacen';path:'/almacen';fullPath:'/almacen';preLoaderRoute:typeof AlmacenRouteImport;parentRoute:typeof rootRouteImport}
  }
}
export const routeTree = rootRouteImport._addFileChildren({
  IndexRoute: IndexRoute,
  LoginRoute: LoginRoute,
  SolicitarRoute: SolicitarRoute,
  SuperadminRoute: SuperadminRoute,
  AdminRoute: AdminRoute,
  OnboardingRoute: OnboardingRoute,
  VentasRoute: VentasRoute,
  PrestamosRoute: PrestamosRoute,
  AlmacenRoute: AlmacenRoute
})
