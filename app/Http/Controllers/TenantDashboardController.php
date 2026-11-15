<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Almacen;
use App\Models\Establecimiento;
use App\Models\StockMovimiento;
use App\Models\Tenant;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenantDashboardController
{
    public function __invoke(Request $request)
    {
        $usuario = $request->user();
        $esSuperadmin = $usuario->roles()
            ->where('roles.slug', 'superadmin')
            ->whereNull('roles.tenant_id')
            ->wherePivot('tenant_id', null)
            ->exists();

        if ($esSuperadmin) {
            return redirect()->route('superadmin.dashboard');
        }

        abort_if($usuario->tenant_id === null, 403, 'Tu cuenta no está asociada a un negocio.');

        $tenantId = (int) $usuario->tenant_id;
        $tenant = Tenant::query()->findOrFail($tenantId);
        $rolesTenant = $usuario->roles()->where('usuario_rol.tenant_id', $tenantId)->pluck('roles.slug')->all();
        $esAdmin = in_array('admin', $rolesTenant, true);
        $esGerente = in_array('gerente', $rolesTenant, true);
        $esAlmacen = in_array('almacen', $rolesTenant, true);
        if (in_array('ventas', $rolesTenant, true)) {
            $puntoVenta = DB::table('usuario_almacen')->join('almacenes', 'almacenes.id', '=', 'usuario_almacen.almacen_id')
                ->join('establecimientos', 'establecimientos.id', '=', 'almacenes.establecimiento_id')
                ->where('usuario_almacen.tenant_id', $usuario->tenant_id)->where('usuario_almacen.usuario_id', $usuario->getKey())
                ->where('almacenes.tenant_id', $usuario->tenant_id)->where('almacenes.activo', true)->where('establecimientos.activo', true)
                ->first(['almacenes.id', 'almacenes.establecimiento_id']);
            if ($puntoVenta === null) {
                return view('tenant.punto-venta-sin-asignar', compact('tenant'));
            }

            return redirect()->route('ventas.index', ['establecimiento' => $puntoVenta->establecimiento_id, 'almacen' => $puntoVenta->id]);
        }
        $establecimientoIds = DB::table('usuario_establecimiento')->where('tenant_id', $tenantId)->where('usuario_id', $usuario->getKey())->select('establecimiento_id');
        $almacenIds = DB::table('usuario_almacen')->where('tenant_id', $tenantId)->where('usuario_id', $usuario->getKey())->select('almacen_id');
        $almacenes = Almacen::query()->with('establecimiento:id,nombre')
            ->where('tenant_id', $tenantId)->where('activo', true)
            ->whereHas('establecimiento', fn ($query) => $query->where('activo', true))
            ->when($esGerente, fn ($query) => $query->whereIn('establecimiento_id', $establecimientoIds))
            ->when($esAlmacen && ! $esAdmin, fn ($query) => $query->whereIn('id', $almacenIds))
            ->orderBy('establecimiento_id')->orderBy('nombre')->get();
        $sucursalesEnAlcance = $esAdmin
            ? Establecimiento::query()->where('tenant_id', $tenantId)->where('activo', true)->count()
            : ($esGerente
                ? Establecimiento::query()->where('tenant_id', $tenantId)->where('activo', true)->whereIn('id', $establecimientoIds)->count()
                : $almacenes->pluck('establecimiento_id')->unique()->count());
        $personalActivo = DB::table('usuario_rol as ur')
            ->join('roles as r', 'r.id', '=', 'ur.rol_id')
            ->join('users', 'users.id', '=', 'ur.usuario_id')
            ->where('ur.tenant_id', $tenantId)->where('r.tenant_id', $tenantId)
            ->whereIn('r.slug', ['gerente', 'ventas', 'almacen'])->where('users.activo', true)
            ->distinct('users.id')->count('users.id');
        $equipoGerente = $esGerente
            ? app(\App\Services\UsuarioTenantService::class)->usuarios($usuario)
                ->filter(fn ($persona) => (bool) $persona->activo)
                ->sortBy('name')->values()
            : collect();
        $personalGerenteTotal = $equipoGerente->count();
        $equipoGerente = $equipoGerente->take(8)->values();
        $productos = Producto::query()->where('tenant_id', $tenantId)->where('activo', true);
        $ventasHoy = Venta::query()->where('tenant_id', $tenantId)
            ->where('estado', 'confirmada')->whereDate('created_at', today());
        if (! $esAdmin) {
            if ($esAlmacen) {
                $ventasHoy->whereIn('almacen_id', $almacenIds);
            } else {
                $ventasHoy->whereIn('establecimiento_id', $establecimientoIds);
            }
        }

        $productosBajoStock = Producto::query()
            ->leftJoin('existencias', function ($join) use ($tenantId, $esAdmin, $esAlmacen, $almacenIds, $establecimientoIds): void {
                $join->on('existencias.producto_id', '=', 'productos.id')
                    ->where('existencias.tenant_id', '=', $tenantId);
                if (! $esAdmin && $esAlmacen) $join->whereIn('existencias.almacen_id', $almacenIds);
                elseif (! $esAdmin) $join->whereIn('existencias.almacen_id', DB::table('almacenes')->select('id')->where('tenant_id', $tenantId)->whereIn('establecimiento_id', $establecimientoIds));
            })
            ->where('productos.tenant_id', $tenantId)
            ->where('productos.activo', true)
            ->select(['productos.id', 'productos.nombre', 'productos.sku', 'productos.stock_minimo'])
            ->selectRaw('COALESCE(SUM(existencias.cantidad), 0) as existencia_actual')
            ->groupBy('productos.id', 'productos.nombre', 'productos.sku', 'productos.stock_minimo')
            ->havingRaw('COALESCE(SUM(existencias.cantidad), 0) <= productos.stock_minimo')
            ->orderBy('productos.nombre')
            ->limit(6)
            ->get();

        $movimientos = StockMovimiento::query()
            ->with(['producto:id,nombre,sku', 'usuario:id,name'])
            ->where('tenant_id', $tenantId)
            ->where(function ($query) use ($esAdmin, $esAlmacen, $almacenIds, $establecimientoIds, $tenantId): void {
                if ($esAdmin) return;
                $warehouseIds = $esAlmacen
                    ? $almacenIds
                    : DB::table('almacenes')->select('id')->where('tenant_id', $tenantId)->whereIn('establecimiento_id', $establecimientoIds);
                $query->whereIn('almacen_origen_id', $warehouseIds)->orWhereIn('almacen_destino_id', $warehouseIds);
            })
            ->latest()
            ->limit(6)
            ->get();

        return view($esGerente ? 'dashboard-gerente' : 'dashboard', [
            'tenant' => $tenant,
            'esAdmin' => $esAdmin,
            'esGerente' => $esGerente,
            'esAlmacen' => $esAlmacen,
            'personalActivo' => $personalActivo,
            'equipoGerente' => $equipoGerente,
            'personalGerenteTotal' => $personalGerenteTotal,
            'sucursalesEnAlcance' => $sucursalesEnAlcance,
            'almacenesOperativos' => $almacenes,
            'totalProductos' => $productos->count(),
            'ventasHoyCantidad' => (clone $ventasHoy)->count(),
            'ventasHoyTotal' => (float) (clone $ventasHoy)->sum('total'),
            'productosBajoStock' => $productosBajoStock,
            'movimientos' => $movimientos,
        ]);
    }
}
