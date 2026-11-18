<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNotNull('tenant_id')
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('tenants')
                    ->whereColumn('tenants.id', 'users.tenant_id')
                    ->where('tenants.estado', 'configuracion_pendiente');
            })
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('usuario_rol')
                    ->join('roles', 'roles.id', '=', 'usuario_rol.rol_id')
                    ->whereColumn('usuario_rol.usuario_id', 'users.id')
                    ->whereColumn('usuario_rol.tenant_id', 'users.tenant_id')
                    ->whereColumn('roles.tenant_id', 'users.tenant_id')
                    ->where('roles.slug', 'admin');
            })
            ->update(['debe_cambiar_password' => true]);
    }

    public function down(): void
    {
        // Keep the forced password change in place after rollback for account safety.
    }
};
