<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario_almacen', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('almacen_id')->constrained('almacenes')->cascadeOnDelete();
            $table->foreignId('asignado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['usuario_id', 'almacen_id']);
            $table->index(['tenant_id', 'almacen_id']);
        });

        // Conserva los accesos que existían: antes el acceso a cualquier almacén
        // de una sucursal se heredaba automáticamente de usuario_establecimiento.
        $usuariosAlmacen = DB::table('usuario_rol as ur')
            ->join('roles as r', 'r.id', '=', 'ur.rol_id')
            ->join('usuario_establecimiento as ue', function ($join): void {
                $join->on('ue.usuario_id', '=', 'ur.usuario_id')->on('ue.tenant_id', '=', 'ur.tenant_id');
            })
            ->join('almacenes as a', function ($join): void {
                $join->on('a.establecimiento_id', '=', 'ue.establecimiento_id')->on('a.tenant_id', '=', 'ue.tenant_id');
            })
            ->where('r.slug', 'almacen')
            ->whereColumn('r.tenant_id', 'ur.tenant_id')
            ->select(['ur.tenant_id', 'ur.usuario_id', 'a.id as almacen_id', 'ue.asignado_por'])
            ->get();

        foreach ($usuariosAlmacen->chunk(500) as $chunk) {
            DB::table('usuario_almacen')->insertOrIgnore($chunk->map(fn ($row): array => [
                'tenant_id' => $row->tenant_id,
                'usuario_id' => $row->usuario_id,
                'almacen_id' => $row->almacen_id,
                'asignado_por' => $row->asignado_por,
                'created_at' => now(),
                'updated_at' => now(),
            ])->all());
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_almacen');
    }
};
