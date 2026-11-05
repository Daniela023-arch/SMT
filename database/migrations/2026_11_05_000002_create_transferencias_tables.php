<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transferencias', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();
            $table->foreignId('establecimiento_origen_id')->constrained('establecimientos')->restrictOnDelete();
            $table->foreignId('establecimiento_destino_id')->constrained('establecimientos')->restrictOnDelete();
            $table->foreignId('almacen_origen_id')->constrained('almacenes')->restrictOnDelete();
            $table->foreignId('almacen_destino_id')->constrained('almacenes')->restrictOnDelete();
            $table->foreignId('solicitante_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('autorizador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('folio', 50);
            $table->string('estado', 30)->default('solicitada');
            $table->text('motivo')->nullable();
            $table->text('motivo_rechazo')->nullable();
            $table->timestamp('solicitada_at')->useCurrent();
            $table->timestamp('autorizada_at')->nullable();
            $table->timestamp('en_transito_at')->nullable();
            $table->timestamp('recibida_at')->nullable();
            $table->timestamp('cancelada_at')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'folio']);
            $table->index(['tenant_id', 'estado', 'created_at']);
        });

        Schema::create('transferencia_detalles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();
            $table->foreignId('transferencia_id')->constrained('transferencias')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $table->foreignId('lote_id')->nullable()->constrained('lotes')->restrictOnDelete();
            $table->foreignId('ubicacion_origen_id')->nullable()->constrained('ubicaciones')->nullOnDelete();
            $table->foreignId('ubicacion_destino_id')->nullable()->constrained('ubicaciones')->nullOnDelete();
            $table->decimal('cantidad_solicitada', 18, 4);
            $table->decimal('cantidad_enviada', 18, 4)->default(0);
            $table->decimal('cantidad_recibida', 18, 4)->default(0);
            $table->timestamps();
            $table->index(['transferencia_id', 'producto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transferencia_detalles');
        Schema::dropIfExists('transferencias');
    }
};
