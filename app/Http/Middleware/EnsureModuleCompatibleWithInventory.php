<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/** Enforces the Inventra module/type matrix documented by the frontend prototype. */
class EnsureModuleCompatibleWithInventory
{
    private const MODULE_TYPES = [
        'stock' => ['biblioteca', 'abarrotes', 'papeleria', 'farmacia', 'electronica'],
        'productos' => ['biblioteca', 'abarrotes', 'papeleria', 'farmacia', 'electronica'],
        'categorias' => ['biblioteca', 'abarrotes', 'papeleria', 'farmacia', 'electronica'],
        'ubicaciones' => ['biblioteca', 'abarrotes', 'papeleria', 'farmacia', 'electronica'],
        'proveedores' => ['abarrotes', 'papeleria', 'farmacia', 'electronica'],
        'lotes' => ['abarrotes', 'farmacia'],
        'caducidades' => ['abarrotes', 'farmacia'],
        'ventas' => ['abarrotes', 'papeleria', 'farmacia'],
        'prestamos' => ['biblioteca', 'electronica'],
        'devoluciones' => ['biblioteca', 'electronica'],
        'biblio' => ['biblioteca'],
        'mantenimientos' => ['electronica'],
        'bajas' => ['electronica'],
        'csv' => ['biblioteca', 'abarrotes', 'papeleria', 'farmacia', 'electronica'],
        'reportes' => ['biblioteca', 'abarrotes', 'papeleria', 'farmacia', 'electronica'],
    ];

    public function handle(Request $request, Closure $next, string $module): Response
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->user()?->tenant_id;
        $tenant = $tenantId === null
            ? null
            : Tenant::query()->with('tipoInventario')->find($tenantId);
        $inventoryType = $this->canonicalInventoryType($tenant?->tipoInventario?->nombre);
        $allowedTypes = self::MODULE_TYPES[$module] ?? null;

        if ($allowedTypes === null || $inventoryType === null || ! in_array($inventoryType, $allowedTypes, true)) {
            abort(Response::HTTP_FORBIDDEN, 'Este módulo no es compatible con el tipo de inventario del tenant.');
        }

        return $next($request);
    }

    private function canonicalInventoryType(?string $type): ?string
    {
        return match (Str::slug($type ?? '')) {
            'biblioteca' => 'biblioteca',
            'abarrotes' => 'abarrotes',
            'papeleria' => 'papeleria',
            'farmacia' => 'farmacia',
            'electronica', 'equipo-electronico', 'equipos-electronicos' => 'electronica',
            default => null,
        };
    }
}
