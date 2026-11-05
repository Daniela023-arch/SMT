<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuraciones_tenant', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained('tenants')->cascadeOnDelete();
            $table->string('logotipo')->nullable();
            $table->string('color_primario', 20)->nullable();
            $table->string('color_secundario', 20)->nullable();
            $table->json('preferencias')->nullable();
            $table->timestamps();
        });

        Schema::create('establecimientos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('codigo', 40);
            $table->string('nombre');
            $table->string('tipo', 30); // sucursal | centro_distribucion
            $table->string('email')->nullable();
            $table->string('telefono', 30)->nullable();
            $table->text('direccion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tenant_id', 'codigo']);
            $table->unique(['id', 'tenant_id']);
        });

        Schema::create('almacenes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('establecimiento_id')->constrained('establecimientos')->cascadeOnDelete();
            $table->string('codigo', 40);
            $table->string('nombre');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['establecimiento_id', 'codigo']);
            $table->unique(['id', 'tenant_id']);
            $table->index(['tenant_id', 'establecimiento_id']);
        });

        Schema::table('ubicaciones', function (Blueprint $table): void {
            $table->foreignId('almacen_id')->nullable()->after('tenant_id')->constrained('almacenes')->nullOnDelete();
        });

        Schema::table('existencias', function (Blueprint $table): void {
            $table->foreignId('almacen_id')->nullable()->after('tenant_id')->constrained('almacenes')->nullOnDelete();
            $table->index(['tenant_id', 'almacen_id', 'producto_id'], 'existencias_por_almacen');
        });

        Schema::table('stock_movimientos', function (Blueprint $table): void {
            $table->foreignId('almacen_origen_id')->nullable()->constrained('almacenes')->nullOnDelete();
            $table->foreignId('almacen_destino_id')->nullable()->constrained('almacenes')->nullOnDelete();
        });

        Schema::table('ventas', function (Blueprint $table): void {
            $table->foreignId('establecimiento_id')->nullable()->constrained('establecimientos')->nullOnDelete();
            $table->foreignId('almacen_id')->nullable()->constrained('almacenes')->nullOnDelete();
        });

        Schema::create('usuario_establecimiento', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('establecimiento_id')->constrained('establecimientos')->cascadeOnDelete();
            $table->foreignId('asignado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['usuario_id', 'establecimiento_id']);
            $table->index(['tenant_id', 'establecimiento_id']);
        });

        Schema::create('auditoria', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('establecimiento_id')->nullable()->constrained('establecimientos')->nullOnDelete();
            $table->string('accion', 100);
            $table->string('entidad', 100)->nullable();
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('agente_usuario')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['tenant_id', 'created_at']);
            $table->index(['entidad', 'entidad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria');
        Schema::dropIfExists('usuario_establecimiento');

        Schema::table('ventas', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('establecimiento_id');
            $table->dropConstrainedForeignId('almacen_id');
        });

        Schema::table('stock_movimientos', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('almacen_origen_id');
            $table->dropConstrainedForeignId('almacen_destino_id');
        });

        Schema::table('existencias', function (Blueprint $table): void {
            $table->dropIndex('existencias_por_almacen');
            $table->dropConstrainedForeignId('almacen_id');
        });

        Schema::table('ubicaciones', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('almacen_id');
        });

        Schema::dropIfExists('almacenes');
        Schema::dropIfExists('establecimientos');
        Schema::dropIfExists('configuraciones_tenant');
    }
};
